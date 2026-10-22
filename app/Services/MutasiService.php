<?php

namespace App\Services;

use App\Models\Mutasi;
use App\Models\Pembayaran;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MutasiService
{
    /**
     * Memproses forward notifikasi dari HP Android (DANA / Bank)
     */
    public function processIncoming(array $payload): array
    {
        $rawTitle   = $payload['title'] ?? $payload['header'] ?? $payload['subject'] ?? '';
        $rawContent = $payload['content'] ?? $payload['text'] ?? $payload['message'] ?? $payload['body'] ?? $payload['description'] ?? '';
        $rawPackage = $payload['package_name'] ?? $payload['package'] ?? $payload['pkg'] ?? '';
        $rawApp     = $payload['app_name'] ?? $payload['app'] ?? '';

        $fullText = trim($rawTitle . ' ' . $rawContent);

        // 1. Parse detail notifikasi
        $parsed = $this->parseNotification($fullText, $rawTitle, $rawPackage, $rawApp);

        // Jika nominal di-pass eksplisit di payload (misal dari script filter MacroDroid)
        if (isset($payload['nominal']) && is_numeric($payload['nominal']) && (float)$payload['nominal'] > 0) {
            $parsed['nominal'] = (float) $payload['nominal'];
        } elseif (isset($payload['amount']) && is_numeric($payload['amount']) && (float)$payload['amount'] > 0) {
            $parsed['nominal'] = (float) $payload['amount'];
        }

        // 2. Simpan rekaman mutasi ke database
        $mutasi = Mutasi::create([
            'app_name'     => $parsed['app_name'],
            'package_name' => $rawPackage ?: null,
            'tipe'         => $parsed['tipe'],
            'nominal'      => $parsed['nominal'],
            'sender'       => $parsed['sender'],
            'title'        => $rawTitle ?: null,
            'content'      => $rawContent ?: $fullText,
            'status'       => 'UNMATCHED',
            'catatan'      => null,
            'raw_payload'  => $payload,
        ]);

        // 3. Filter jika transaksi keluar (Debit) atau nominal 0
        if ($parsed['tipe'] === 'DB' || $parsed['nominal'] <= 0) {
            $mutasi->update([
                'status'  => 'IGNORED',
                'catatan' => 'Diabaikan: Transaksi keluar/debit atau nominal tidak terdeteksi.',
            ]);

            return [
                'success'   => true,
                'matched'   => false,
                'status'    => 'IGNORED',
                'mutasi_id' => $mutasi->id,
                'message'   => 'Notifikasi dicatat tetapi diabaikan (bukan dana masuk).',
            ];
        }

        // 4. Cari pesanan yang cocok (Order Matching)
        $order = $this->findMatchingOrder($parsed['nominal']);

        if (!$order) {
            $mutasi->update([
                'status'  => 'UNMATCHED',
                'catatan' => 'Belum ada pesanan pending dengan nominal Rp ' . number_format($parsed['nominal'], 0, ',', '.'),
            ]);

            Log::info("[MutasiService] Mutasi tercatat tetapi belum cocok dengan order pending", [
                'mutasi_id' => $mutasi->id,
                'nominal'   => $parsed['nominal'],
                'app'       => $parsed['app_name'],
            ]);

            return [
                'success'   => true,
                'matched'   => false,
                'status'    => 'UNMATCHED',
                'mutasi_id' => $mutasi->id,
                'nominal'   => $parsed['nominal'],
                'message'   => 'Mutasi berhasil disimpan. Menunggu pesanan dengan nominal yang sesuai.',
            ];
        }

        // 5. Verifikasi dan Update Status Pesanan Otomatis!
        $this->confirmOrderPayment($order, $mutasi);

        return [
            'success'   => true,
            'matched'   => true,
            'status'    => 'MATCHED',
            'mutasi_id' => $mutasi->id,
            'order_id'  => $order->id,
            'nominal'   => $parsed['nominal'],
            'message'   => "🎉 Sukses! Pesanan #INV-{$order->id} berhasil diverifikasi otomatis lunas.",
        ];
    }

    /**
     * Parsing teks notifikasi Android dari berbagai E-Wallet & Bank di Indonesia
     */
    public function parseNotification(string $content, string $title = '', string $packageName = '', string $appName = ''): array
    {
        $normalized = strtolower($content . ' ' . $title . ' ' . $packageName . ' ' . $appName);

        // ── A. Deteksi Provider / Aplikasi ────────────────────────────────
        $app = 'DANA';
        if (str_contains($normalized, 'dana')) {
            $app = 'DANA';
        } elseif (str_contains($normalized, 'bca') || str_contains($normalized, 'mybca') || str_contains($normalized, 'm-transfer')) {
            $app = 'BCA';
        } elseif (str_contains($normalized, 'gopay') || str_contains($normalized, 'gojek')) {
            $app = 'GOPAY';
        } elseif (str_contains($normalized, 'shopee') || str_contains($normalized, 'seabank')) {
            $app = 'SHOPEEPAY';
        } elseif (str_contains($normalized, 'mandiri') || str_contains($normalized, 'livin')) {
            $app = 'MANDIRI';
        } elseif (str_contains($normalized, 'bri') || str_contains($normalized, 'brimo')) {
            $app = 'BRI';
        } elseif (str_contains($normalized, 'bni')) {
            $app = 'BNI';
        } elseif (str_contains($normalized, 'ovo')) {
            $app = 'OVO';
        } elseif (!empty($appName)) {
            $app = strtoupper($appName);
        }

        // ── B. Deteksi Tipe (Kredit Masuk vs Debit Keluar) ─────────────────
        $debitKeywords = [
            'melakukan pembayaran', 'pembayaran ke', 'transfer ke', 'bayar ke', 'pengeluaran', 'debet',
            'debit', 'pulsa berhasil', 'berhasil bayar', 'kamu telah bayar', 'telah bayar',
            'berhasil kirim', 'kirim uang', 'terpotong', 'tagihan berhasil dibayar'
        ];

        $tipe = 'CR';
        foreach ($debitKeywords as $dk) {
            if (str_contains($normalized, $dk)) {
                $tipe = 'DB';
                break;
            }
        }

        // ── C. Ekstraksi Nominal Rupiah ───────────────────────────────────
        $nominal = 0.0;

        // Pola 1: Mencari format "Rp 50.142" atau "Rp. 50.142" atau "IDR 50.142" atau "Rp 50.142,00"
        if (preg_match('/(?:rp\.?|idr)\s*([0-9]{1,3}(?:[.,][0-9]{3})*(?:[.,][0-9]{2})?|[0-9]+)/i', $content, $m)) {
            $rawNom = $m[1];
            // Hapus sen/desimal di ujung jika ada (,00 atau .00)
            $cleanNom = preg_replace('/[,.]00$/', '', $rawNom) ?? $rawNom;
            // Hilangkan semua titik atau koma pemisah ribuan
            $cleanNom = preg_replace('/[^0-9]/', '', (string) $cleanNom) ?? '0';
            $nominal = (float) $cleanNom;
        }

        // Pola 2 (Fallback): Jika tidak ada embel-embel Rp tapi ada angka ribuan setelah kata kunci
        if ($nominal <= 0 && preg_match('/(?:sebesar|sejumlah|nominal)\s*[:=]?\s*([0-9\.,]+)/i', $content, $m)) {
            $rawNom = preg_replace('/[,.]00$/', '', $m[1]) ?? $m[1];
            $cleanNom = preg_replace('/[^0-9]/', '', (string) $rawNom) ?? '0';
            $nominal = (float) $cleanNom;
        }

        // ── D. Ekstraksi Pengirim (Opsional) ──────────────────────────────
        $sender = null;
        if (preg_match('/(?:dari|pengirim)\s+([A-Za-z0-9\s\.\,\'\-]+?)(?:\s+via|\s+sebesar|\s+ke|\s+berhasil|\.|\;|$)/i', $content, $sm)) {
            $sender = trim($sm[1]);
        }

        return [
            'app_name' => $app,
            'tipe'     => $tipe,
            'nominal'  => $nominal,
            'sender'   => $sender,
        ];
    }

    /**
     * Menemukan pesanan yang pending berdasarkan kecocokan nominal persis
     */
    public function findMatchingOrder(float $nominal)
    {
        $windowMinutes = (int) config('mutasi.match_window_minutes', 1440);
        $since = Carbon::now()->subMinutes($windowMinutes);

        // Cari pesanan yang statusnya PENDING_PAYMENT / Menunggu Pembayaran / Menunggu Konfirmasi
        // dan belum ditandai Lunas di tabel pembayarans
        return DB::table('orders')
            ->where(function ($q) use ($nominal) {
                $q->where('total_biaya', $nominal)
                  ->orWhere('total_price', $nominal);
            })
            ->whereIn('status', ['PENDING_PAYMENT', 'Menunggu Pembayaran', 'Menunggu Konfirmasi'])
            ->where('created_at', '>=', $since)
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                      ->from('pembayarans')
                      ->whereColumn('pembayarans.order_id', 'orders.id')
                      ->where('pembayarans.status', 'Lunas');
            })
            ->orderBy('id', 'desc')
            ->first();
    }

    /**
     * Konfirmasi pembayaran pesanan menjadi Lunas secara otomatis
     */
    public function confirmOrderPayment($order, Mutasi $mutasi): void
    {
        DB::beginTransaction();
        try {
            // 1. Update status di tabel orders
            DB::table('orders')->where('id', $order->id)->update([
                'status'         => 'Menunggu Konfirmasi',
                'payment_method' => 'QRIS',
                'paid_at'        => now(),
                'updated_at'     => now(),
            ]);

            // 2. Upsert status di tabel pembayarans
            Pembayaran::updateOrCreate(
                ['order_id' => $order->id],
                [
                    'metode'  => 'QRIS',
                    'jumlah'  => $mutasi->nominal ?: ($order->total_biaya ?? $order->total_price),
                    'status'  => 'Lunas',
                    'paid_at' => now(),
                ]
            );

            // 3. Update relasi di mutasi
            $mutasi->update([
                'order_id' => $order->id,
                'status'   => 'MATCHED',
                'catatan'  => "Otomatis cocok dengan Pesanan #INV-{$order->id} ({$order->kode_booking}).",
            ]);

            DB::commit();

            Log::info("[MutasiService] ✅ Berhasil auto-confirm pesanan #INV-{$order->id} senilai Rp {$mutasi->nominal}");

            // 4. Kirim notifikasi WhatsApp otomatis
            if (config('mutasi.send_wa_notification', true)) {
                $this->sendNotifications($order, $mutasi);
            }

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("[MutasiService] Gagal confirmOrderPayment: " . $e->getMessage(), [
                'order_id'  => $order->id,
                'mutasi_id' => $mutasi->id,
            ]);
            throw $e;
        }
    }

    /**
     * Kirim notifikasi WhatsApp ke Pelanggan dan Vendor
     */
    protected function sendNotifications($order, Mutasi $mutasi): void
    {
        $nomorCust = $order->customer_whatsapp ?? null;
        $nominalFormatted = number_format($mutasi->nominal, 0, ',', '.');
        $strukUrl = route('customer.struk', ['id' => 'INV-' . $order->id]);

        // WhatsApp ke Pelanggan
        if (!empty($nomorCust)) {
            $pesanCust = "🎉 *PEMBAYARAN DIVERIFIKASI OTOMATIS!* 🎉\n\n"
                . "Halo *{$order->customer_name}*, terima kasih!\n"
                . "Pembayaran QRIS pesanan Anda telah berhasil diverifikasi oleh sistem Rentify secara otomatis 24/7:\n\n"
                . "📋 *No. Pesanan:* INV-{$order->id}\n"
                . "🔖 *Kode Booking:* {$order->kode_booking}\n"
                . "💰 *Total Terbayar:* Rp {$nominalFormatted}\n"
                . "🏦 *Channel:* QRIS ({$mutasi->app_name})\n"
                . "⏱️ *Waktu:* " . Carbon::now()->format('d M Y, H:i') . " WIB\n\n"
                . "Pesanan Anda sekarang sedang dipersiapkan oleh pihak rental. Lihat bukti dan struk digital Anda di sini:\n"
                . "👉 {$strukUrl}\n\n"
                . "_Rentify — Sewa Mudah, Cepat & Terpercaya._";

            WhatsAppService::send($nomorCust, $pesanCust);
        }

        // WhatsApp ke Vendor (jika nomor vendor ada)
        if (!empty($order->vendor_id)) {
            $vendor = DB::table('users')->where('id', $order->vendor_id)->first();
            $vendorPhone = $vendor->whatsapp_vendor ?? $vendor->no_hp ?? null;

            if (!empty($vendorPhone)) {
                $vendorPesananUrl = route('admin.dashboard'); // atau halaman pesanan vendor
                $pesanVendor = "🔔 *PESANAN BARU TERBAYAR LUNAS (QRIS)*\n\n"
                    . "Halo *{$vendor->name}*, ada pesanan baru yang telah terbayar lunas via QRIS otomatis:\n\n"
                    . "📋 *No. Pesanan:* INV-{$order->id}\n"
                    . "👤 *Penyewa:* {$order->customer_name}\n"
                    . "💰 *Total Pembayaran:* Rp {$nominalFormatted}\n\n"
                    . "Silakan periksa detail pesanan dan siapkan barang sewaan di panel toko Anda:\n"
                    . "👉 https://rentify21.my.id/vendor/pesanan\n\n"
                    . "_Sistem Rentify Auto-Settlement_";

                WhatsAppService::send($vendorPhone, $pesanVendor);
            }
        }
    }
}
