<?php

namespace App\Services;

use App\Models\Mutasi;
use App\Models\Pembayaran;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

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
            $catatan = 'Diabaikan: Transaksi keluar/debit atau nominal tidak terdeteksi.';
            if (str_contains($fullText, '[not_text]') || str_contains($fullText, '[not_title]')) {
                $catatan = 'Peringatan: Teks notifikasi berisi placeholder [not_text]. Pastikan izin Akses Notifikasi (Notification Access) untuk MacroDroid telah diaktifkan di Pengaturan HP Android.';
                Log::warning('[MutasiService] MacroDroid [not_text] terdeteksi. Izin Akses Notifikasi Android belum aktif.');
            }

            $mutasi->update([
                'status'  => 'IGNORED',
                'catatan' => $catatan,
            ]);

            return [
                'success'   => true,
                'matched'   => false,
                'status'    => 'IGNORED',
                'mutasi_id' => $mutasi->id,
                'message'   => $catatan,
            ];
        }

        // 4. Cari pesanan yang cocok (Mendukung Single Order maupun Multi-Vendor Combo Orders)
        $matchedOrders = $this->findMatchingOrders($parsed['nominal']);

        if ($matchedOrders->isEmpty()) {
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

        // 5. Verifikasi dan Update Status Seluruh Pesanan yang Cocok Secara Otomatis!
        $this->confirmOrdersPayment($matchedOrders, $mutasi);

        $orderIds = $matchedOrders->pluck('id')->all();
        $orderCodes = $matchedOrders->map(fn($o) => '#INV-' . $o->id)->implode(', ');

        return [
            'success'   => true,
            'matched'   => true,
            'status'    => 'MATCHED',
            'mutasi_id' => $mutasi->id,
            'order_id'  => $matchedOrders->first()->id,
            'order_ids' => $orderIds,
            'nominal'   => $parsed['nominal'],
            'message'   => "🎉 Sukses! Pesanan {$orderCodes} berhasil diverifikasi otomatis lunas.",
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
        if ($nominal <= 0 && preg_match('/(?:sebesar|sejumlah|nominal|terima|diterima|masuk)\s*[:=]?\s*(?:rp\.?)?\s*([0-9\.,]+)/i', $content, $m)) {
            $rawNom = preg_replace('/[,.]00$/', '', $m[1]) ?? $m[1];
            $cleanNom = preg_replace('/[^0-9]/', '', (string) $rawNom) ?? '0';
            $nominal = (float) $cleanNom;
        }

        // Pola 3: Format DANA Bisnis / Bank "Uang masuk Rp 10.500" atau "Menerima Rp 10.500" atau "Transfer masuk Rp 10.500"
        if ($nominal <= 0 && preg_match('/(?:uang masuk|menerima|terima uang|transfer masuk)\s*(?:sebesar)?\s*(?:rp\.?)?\s*([0-9\.,]+)/i', $normalized, $m)) {
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
     * Menemukan pesanan yang pending berdasarkan nominal (Single Order) untuk kompatibilitas ke belakang
     */
    public function findMatchingOrder(float $nominal)
    {
        $orders = $this->findMatchingOrders($nominal);
        return $orders->first();
    }

    /**
     * Menemukan kumpulan pesanan yang pending berdasarkan kecocokan nominal persis.
     * Mendukung:
     * 1. Single order matching (1 pesanan = nominal)
     * 2. Multi-vendor combo order matching via payment_group_id (SUM(total_biaya) = nominal)
     * 3. Multi-vendor combo order matching via User & Time Window (subsets order = nominal)
     */
    public function findMatchingOrders(float $nominal): Collection
    {
        $windowMinutes = (int) config('mutasi.match_window_minutes', 1440);
        $since = Carbon::now()->subMinutes($windowMinutes);

        // 1. Cek Single Order Matching
        $single = DB::table('orders')
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

        if ($single) {
            return collect([$single]);
        }

        // 2. Cek Multi-Vendor Combo Order Matching melalui payment_group_id
        if (Schema::hasColumn('orders', 'payment_group_id')) {
            $matchingGroups = DB::table('orders')
                ->select('payment_group_id', DB::raw('SUM(total_biaya) as total_group_biaya'), DB::raw('SUM(total_price) as total_group_price'), DB::raw('COUNT(*) as total_orders'))
                ->whereNotNull('payment_group_id')
                ->whereIn('status', ['PENDING_PAYMENT', 'Menunggu Pembayaran', 'Menunggu Konfirmasi'])
                ->where('created_at', '>=', $since)
                ->whereNotExists(function ($query) {
                    $query->select(DB::raw(1))
                          ->from('pembayarans')
                          ->whereColumn('pembayarans.order_id', 'orders.id')
                          ->where('pembayarans.status', 'Lunas');
                })
                ->groupBy('payment_group_id')
                ->havingRaw('ROUND(total_group_biaya, 2) = ? OR ROUND(total_group_price, 2) = ?', [$nominal, $nominal])
                ->get();

            foreach ($matchingGroups as $grp) {
                $groupOrders = DB::table('orders')
                    ->where('payment_group_id', $grp->payment_group_id)
                    ->whereIn('status', ['PENDING_PAYMENT', 'Menunggu Pembayaran', 'Menunggu Konfirmasi'])
                    ->get();

                if ($groupOrders->isNotEmpty()) {
                    return $groupOrders;
                }
            }
        }

        // 3. Cek Multi-Vendor Combo Order Matching via User & Time Window (Fallback untuk order tanpa payment_group_id)
        $pendingOrders = DB::table('orders')
            ->whereIn('status', ['PENDING_PAYMENT', 'Menunggu Pembayaran', 'Menunggu Konfirmasi'])
            ->where('created_at', '>=', $since)
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                      ->from('pembayarans')
                      ->whereColumn('pembayarans.order_id', 'orders.id')
                      ->where('pembayarans.status', 'Lunas');
            })
            ->orderBy('id', 'desc')
            ->get()
            ->groupBy('user_id');

        foreach ($pendingOrders as $userId => $userOrders) {
            if ($userOrders->count() < 2) {
                continue;
            }

            $ordersArr = $userOrders->values()->all();
            $matchedSubset = $this->findSubsetSum($ordersArr, $nominal);
            if (!empty($matchedSubset)) {
                return collect($matchedSubset);
            }
        }

        return collect([]);
    }

    /**
     * Algoritma mencari subset pesanan yang penjumlahannya sama dengan target nominal
     */
    protected function findSubsetSum(array $orders, float $targetSum): ?array
    {
        $n = count($orders);
        $maxItems = min($n, 10); // Pembatas eksponensial aman
        $totalCombinations = 1 << $maxItems;

        for ($i = 1; $i < $totalCombinations; $i++) {
            $subset = [];
            $sumBiaya = 0.0;
            $sumPrice = 0.0;

            for ($j = 0; $j < $maxItems; $j++) {
                if ($i & (1 << $j)) {
                    $subset[] = $orders[$j];
                    $sumBiaya += (float) ($orders[$j]->total_biaya ?? 0);
                    $sumPrice += (float) ($orders[$j]->total_price ?? 0);
                }
            }

            if (count($subset) >= 2) {
                if (abs($sumBiaya - $targetSum) < 0.01 || abs($sumPrice - $targetSum) < 0.01) {
                    return $subset;
                }
            }
        }

        return null;
    }

    /**
     * Konfirmasi pembayaran untuk satu pesanan (wrapper ke confirmOrdersPayment)
     */
    public function confirmOrderPayment($order, Mutasi $mutasi): void
    {
        $this->confirmOrdersPayment(collect([$order]), $mutasi);
    }

    /**
     * Konfirmasi pembayaran untuk kumpulan pesanan (Single maupun Multi-Vendor Combo)
     * secara atomik di dalam satu database transaction.
     */
    public function confirmOrdersPayment($orders, Mutasi $mutasi): void
    {
        $ordersList = $orders instanceof Collection ? $orders : collect($orders);

        if ($ordersList->isEmpty()) {
            return;
        }

        DB::beginTransaction();
        try {
            $orderIds = $ordersList->pluck('id')->all();
            $orderCodes = $ordersList->map(fn($o) => '#INV-' . $o->id . ' (' . ($o->kode_booking ?? '') . ')')->implode(', ');

            // 1. Update status di tabel orders untuk SEMUA pesanan yang cocok
            DB::table('orders')->whereIn('id', $orderIds)->update([
                'status'         => 'Menunggu Konfirmasi',
                'payment_method' => 'QRIS',
                'paid_at'        => now(),
                'updated_at'     => now(),
            ]);

            // 2. Upsert status di tabel pembayarans untuk setiap pesanan
            foreach ($ordersList as $ord) {
                $orderTotal = (float) ($ord->total_biaya ?: $ord->total_price);
                Pembayaran::updateOrCreate(
                    ['order_id' => $ord->id],
                    [
                        'payment_group_id' => $ord->payment_group_id ?? null,
                        'metode'           => 'QRIS',
                        'jumlah'           => $orderTotal,
                        'status'           => 'Lunas',
                        'paid_at'          => now(),
                    ]
                );
            }

            // 3. Update relasi di mutasi
            $firstOrder = $ordersList->first();
            $catatan = count($orderIds) > 1
                ? "Otomatis cocok dengan " . count($orderIds) . " Pesanan Combo: {$orderCodes}."
                : "Otomatis cocok dengan Pesanan #INV-{$firstOrder->id} ({$firstOrder->kode_booking}).";

            $mutasi->update([
                'order_id' => $firstOrder->id,
                'status'   => 'MATCHED',
                'catatan'  => $catatan,
            ]);

            DB::commit();

            Log::info("[MutasiService] ✅ Berhasil auto-confirm " . count($orderIds) . " pesanan [{$orderCodes}] senilai Rp {$mutasi->nominal}");

            // 4. Kirim notifikasi WhatsApp otomatis untuk masing-masing pesanan
            if (config('mutasi.send_wa_notification', true)) {
                foreach ($ordersList as $ord) {
                    $this->sendNotifications($ord, $mutasi);
                }
            }

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("[MutasiService] Gagal confirmOrdersPayment: " . $e->getMessage(), [
                'order_ids' => $orderIds ?? [],
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
        \App\Services\OrderNotificationService::notifyPaymentConfirmed(
            $order,
            'QRIS (' . ($mutasi->app_name ?? 'Mutasi Bank') . ')',
            (float) ($order->total_biaya ?: $order->total_price ?: $mutasi->nominal)
        );
    }
}
