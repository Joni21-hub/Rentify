<?php

namespace App\Services;

use App\Models\Penyewaan;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderNotificationService
{
    /**
     * Format mata uang Rupiah
     */
    public static function formatRupiah($amount): string
    {
        return number_format((float) $amount, 0, ',', '.');
    }

    /**
     * Dapatkan nama-nama barang dari pesanan
     */
    public static function getOrderItemsSummary($order): string
    {
        try {
            $orderId = $order->id ?? null;
            if (!$orderId) {
                return 'Barang Rental';
            }

            // Ambil dari relasi atau tabel order_items
            $items = DB::table('order_items')
                ->where('order_id', $orderId)
                ->pluck('product_name')
                ->filter()
                ->toArray();

            if (empty($items)) {
                $items = DB::table('order_items')
                    ->leftJoin('barang', 'order_items.product_id', '=', 'barang.id')
                    ->where('order_items.order_id', $orderId)
                    ->pluck('barang.nama_barang')
                    ->filter()
                    ->toArray();
            }

            if (!empty($items)) {
                return implode(', ', $items);
            }

            if (!empty($order->nama_barang)) {
                return $order->nama_barang;
            }
        } catch (\Exception $e) {
            Log::warning('[OrderNotificationService] Gagal mengambil ringkasan barang: ' . $e->getMessage());
        }

        return 'Barang Rental Rentify';
    }

    /**
     * Dapatkan nomor WhatsApp Customer
     */
    public static function resolveCustomerPhone($order): ?string
    {
        if (!empty($order->customer_whatsapp)) {
            return $order->customer_whatsapp;
        }

        $userId = $order->user_id ?? $order->customer_id ?? null;
        if ($userId) {
            $user = DB::table('users')->where('id', $userId)->first();
            if ($user && !empty($user->whatsapp)) {
                return $user->whatsapp;
            }
        }

        return null;
    }

    /**
     * Dapatkan data Vendor (nama & nomor whatsapp)
     */
    public static function resolveVendor($order): ?object
    {
        $vendorId = $order->vendor_id ?? null;

        // Jika tidak ada di root order, cari dari barang di order_items
        if (!$vendorId && !empty($order->id)) {
            $vendorId = DB::table('order_items')
                ->leftJoin('barang', 'order_items.product_id', '=', 'barang.id')
                ->where('order_items.order_id', $order->id)
                ->value('barang.vendor_id');
        }

        if ($vendorId) {
            return DB::table('users')->where('id', $vendorId)->first();
        }

        return null;
    }

    /**
     * 1. Notifikasi saat Pesanan Baru Dibuat (QRIS / COD)
     */
    public static function notifyOrderCreated($order): void
    {
        try {
            $custPhone = self::resolveCustomerPhone($order);
            $vendor = self::resolveVendor($order);
            $items = self::getOrderItemsSummary($order);
            $total = self::formatRupiah($order->total_biaya ?? $order->total_price ?? 0);
            $orderId = $order->id;
            $customerName = $order->customer_name ?? 'Pelanggan';
            $kodeBooking = $order->kode_booking ?? ('RNT' . $orderId);
            $paymentMethod = strtoupper($order->payment_method ?? 'QRIS');

            $strukUrl = url("/customer/struk/INV-{$orderId}");
            $qrisUrl = url("/qris/INV-{$orderId}");
            $vendorPesananUrl = url('/vendor/pesanan');

            // --- Kirim ke Customer ---
            if (!empty($custPhone)) {
                if ($paymentMethod === 'COD') {
                    $pesanCust = "*RENTIFY*\n"
                        . "*PESANAN SEWA DITERIMA (COD)*\n\n"
                        . "Halo *{$customerName}*,\n\n"
                        . "Pesanan sewa Anda dengan metode Bayar di Tempat (COD) telah berhasil dibuat dan tercatat di sistem Rentify:\n\n"
                        . "• *No. Pesanan:* INV-{$orderId}\n"
                        . "• *Kode Booking:* {$kodeBooking}\n"
                        . "• *Barang:* {$items}\n"
                        . "• *Total Pembayaran:* Rp {$total}\n"
                        . "• *Metode:* Bayar di Tempat (COD)\n\n"
                        . "Pihak rental akan segera menyiapkan unit dan menghubungi Anda untuk konfirmasi jadwal serah terima. Cek bukti dan struk resmi pemesanan Anda melalui tautan berikut:\n"
                        . "{$strukUrl}\n\n"
                        . "Terima kasih telah menggunakan Rentify.\n\n"
                        . "---\n"
                        . "_Pesan otomatis dari Sistem Rentify (rentify21.my.id)_";
                } else {
                    $pesanCust = "*RENTIFY*\n"
                        . "*MENUNGGU PEMBAYARAN PESANAN*\n\n"
                        . "Halo *{$customerName}*,\n\n"
                        . "Pesanan sewa Anda telah berhasil dibuat. Silakan selesaikan pembayaran untuk memproses pesanan Anda:\n\n"
                        . "• *No. Pesanan:* INV-{$orderId}\n"
                        . "• *Kode Booking:* {$kodeBooking}\n"
                        . "• *Barang:* {$items}\n"
                        . "• *Total Tagihan:* Rp {$total}\n"
                        . "• *Metode Pembayaran:* QRIS Dinamis\n\n"
                        . "Selesaikan pembayaran melalui tautan resmi Rentify berikut sebelum masa berlaku habis:\n"
                        . "{$qrisUrl}\n\n"
                        . "Setelah transfer berhasil, status pesanan akan otomatis diverifikasi oleh sistem 24/7.\n\n"
                        . "---\n"
                        . "_Pesan otomatis dari Sistem Rentify (rentify21.my.id)_";
                }

                WhatsAppService::send($custPhone, $pesanCust);
            }

            // --- Kirim ke Vendor ---
            if ($vendor) {
                $vendorPhone = $vendor->whatsapp_vendor ?? $vendor->whatsapp ?? null;
                $vendorName = $vendor->vendor_name ?? $vendor->name ?? 'Vendor';

                if (!empty($vendorPhone)) {
                    $pesanVendor = "*RENTIFY PARTNER*\n"
                        . "*PESANAN SEWA BARU MASUK*\n\n"
                        . "Halo *{$vendorName}*,\n\n"
                        . "Toko Anda menerima pesanan sewa baru dengan rincian sebagai berikut:\n\n"
                        . "• *No. Pesanan:* INV-{$orderId}\n"
                        . "• *Penyewa:* {$customerName}\n"
                        . "• *Barang:* {$items}\n"
                        . "• *Total Sewa:* Rp {$total}\n"
                        . "• *Metode:* {$paymentMethod}\n\n"
                        . "Silakan periksa ketersediaan unit dan kelola pesanan melalui panel toko Anda:\n"
                        . "{$vendorPesananUrl}\n\n"
                        . "---\n"
                        . "_Rentify Partner Operations_";

                    WhatsAppService::send($vendorPhone, $pesanVendor);
                }
            }
        } catch (\Exception $e) {
            Log::error('[OrderNotificationService] Gagal notifyOrderCreated: ' . $e->getMessage());
        }
    }

    /**
     * 2. Notifikasi saat Pembayaran Berhasil Terverifikasi / Lunas (QRIS, Mutasi, Admin Verify)
     */
    public static function notifyPaymentConfirmed($order, string $channel = 'QRIS', ?float $nominal = null): void
    {
        try {
            $custPhone = self::resolveCustomerPhone($order);
            $vendor = self::resolveVendor($order);
            $items = self::getOrderItemsSummary($order);
            $orderId = $order->id;
            $customerName = $order->customer_name ?? 'Pelanggan';
            $kodeBooking = $order->kode_booking ?? ('RNT' . $orderId);

            $nominalFinal = $nominal ?: ($order->total_biaya ?? $order->total_price ?? 0);
            $nominalFormatted = self::formatRupiah($nominalFinal);

            $strukUrl = url("/customer/struk/INV-{$orderId}");
            $vendorPesananUrl = url('/vendor/pesanan');
            $waktu = Carbon::now('Asia/Jakarta')->format('d M Y, H:i');

            // --- Kirim Struk & Konfirmasi ke Customer ---
            if (!empty($custPhone)) {
                $pesanCust = "*RENTIFY*\n"
                    . "*PEMBAYARAN DIVERIFIKASI LUNAS*\n\n"
                    . "Halo *{$customerName}*,\n\n"
                    . "Pembayaran pesanan sewa Anda telah berhasil diverifikasi oleh sistem Rentify:\n\n"
                    . "• *No. Pesanan:* INV-{$orderId}\n"
                    . "• *Kode Booking:* {$kodeBooking}\n"
                    . "• *Barang:* {$items}\n"
                    . "• *Total Terbayar:* Rp {$nominalFormatted}\n"
                    . "• *Metode:* {$channel}\n"
                    . "• *Waktu Transaksi:* {$waktu} WIB\n\n"
                    . "Pesanan Anda sekarang sedang dipersiapkan oleh pihak rental. Simpan dan periksa struk digital resmi Anda melalui tautan berikut:\n"
                    . "{$strukUrl}\n\n"
                    . "Terima kasih telah menggunakan Rentify.\n\n"
                    . "---\n"
                    . "_Pesan otomatis dari Sistem Rentify (rentify21.my.id)_";

                WhatsAppService::send($custPhone, $pesanCust);
            }

            // --- Kirim Alert ke Vendor ---
            if ($vendor) {
                $vendorPhone = $vendor->whatsapp_vendor ?? $vendor->whatsapp ?? null;
                $vendorName = $vendor->vendor_name ?? $vendor->name ?? 'Vendor';

                if (!empty($vendorPhone)) {
                    $pesanVendor = "*RENTIFY PARTNER*\n"
                        . "*PESANAN SEWA TERBAYAR LUNAS*\n\n"
                        . "Halo *{$vendorName}*,\n\n"
                        . "Pesanan sewa berikut telah lunas diverifikasi:\n\n"
                        . "• *No. Pesanan:* INV-{$orderId}\n"
                        . "• *Penyewa:* {$customerName}\n"
                        . "• *Barang:* {$items}\n"
                        . "• *Total Pembayaran:* Rp {$nominalFormatted}\n"
                        . "• *Metode:* {$channel}\n\n"
                        . "Mohon segera siapkan unit rental Anda untuk pelanggan. Kelola pesanan melalui panel toko:\n"
                        . "{$vendorPesananUrl}\n\n"
                        . "---\n"
                        . "_Rentify Partner Operations_";

                    WhatsAppService::send($vendorPhone, $pesanVendor);
                }
            }
        } catch (\Exception $e) {
            Log::error('[OrderNotificationService] Gagal notifyPaymentConfirmed: ' . $e->getMessage());
        }
    }

    /**
     * 3. Notifikasi saat Status Pesanan Diperbarui oleh Vendor / Sistem
     */
    public static function notifyOrderStatusUpdated($order, string $newStatus, ?string $keterangan = null): void
    {
        try {
            $custPhone = self::resolveCustomerPhone($order);
            $vendor = self::resolveVendor($order);
            $items = self::getOrderItemsSummary($order);
            $orderId = $order->id;
            $customerName = $order->customer_name ?? 'Pelanggan';
            $strukUrl = url("/customer/struk/INV-{$orderId}");
            $vendorPesananUrl = url('/vendor/pesanan');
            $vendorName = $vendor ? ($vendor->vendor_name ?? $vendor->name ?? 'Vendor Rental') : 'Vendor Rental';

            switch ($newStatus) {
                case 'Disetujui':
                    if (!empty($custPhone)) {
                        $pesan = "*RENTIFY*\n"
                            . "*PESANAN SEWA DISETUJUI*\n\n"
                            . "Halo *{$customerName}*,\n\n"
                            . "Pesanan sewa Anda dengan nomor *INV-{$orderId}* ({$items}) telah disetujui oleh *{$vendorName}*.\n\n"
                            . "Unit rental sedang dipersiapkan dan dicek kondisinya sebelum diserahterimakan sesuai jadwal sewa Anda.\n\n"
                            . "Pantau status pesanan dan rincian transaksi melalui tautan berikut:\n"
                            . "{$strukUrl}\n\n"
                            . "---\n"
                            . "_Pesan otomatis dari Sistem Rentify (rentify21.my.id)_";

                        WhatsAppService::send($custPhone, $pesan);
                    }
                    break;

                case 'Sedang Disewa':
                    if (!empty($custPhone)) {
                        $tglMulai = !empty($order->tanggal_mulai) ? Carbon::parse($order->tanggal_mulai)->format('d M Y') : '-';
                        $tglSelesai = !empty($order->tanggal_selesai) ? Carbon::parse($order->tanggal_selesai)->format('d M Y') : '-';

                        $pesan = "*RENTIFY*\n"
                            . "*MASA SEWA AKTIF*\n\n"
                            . "Halo *{$customerName}*,\n\n"
                            . "Unit rental untuk pesanan *INV-{$orderId}* telah diserahterimakan dan masa sewa resmi dimulai:\n\n"
                            . "• *Barang:* {$items}\n"
                            . "• *Masa Sewa:* {$tglMulai} s/d {$tglSelesai}\n\n"
                            . "Harap menjaga kondisi barang dengan baik dan mengembalikan unit tepat waktu sebelum masa sewa berakhir untuk menghindari denda.\n\n"
                            . "Lihat rincian pesanan dan syarat sewa melalui tautan berikut:\n"
                            . "{$strukUrl}\n\n"
                            . "---\n"
                            . "_Pesan otomatis dari Sistem Rentify (rentify21.my.id)_";

                        WhatsAppService::send($custPhone, $pesan);
                    }
                    break;

                case 'Selesai':
                    // Pesan ke Customer
                    if (!empty($custPhone)) {
                        $pesanCust = "*RENTIFY*\n"
                            . "*SEWA SELESAI — TERIMA KASIH*\n\n"
                            . "Halo *{$customerName}*,\n\n"
                            . "Pesanan sewa Anda dengan nomor *INV-{$orderId}* ({$items}) telah selesai.\n\n"
                            . "Terima kasih banyak telah menyewa melalui platform Rentify. Luangkan waktu sejenak untuk memberikan ulasan dan penilaian melalui tautan berikut:\n"
                            . "{$strukUrl}\n\n"
                            . "---\n"
                            . "_Pesan otomatis dari Sistem Rentify (rentify21.my.id)_";

                        WhatsAppService::send($custPhone, $pesanCust);
                    }

                    // Pesan ke Vendor (Saldo Masuk)
                    if ($vendor) {
                        $vendorPhone = $vendor->whatsapp_vendor ?? $vendor->whatsapp ?? null;
                        if (!empty($vendorPhone)) {
                            $pesanVendor = "*RENTIFY PARTNER*\n"
                                . "*PESANAN SELESAI — PENDAPATAN DITERUSKAN*\n\n"
                                . "Halo *{$vendorName}*,\n\n"
                                . "Pesanan sewa *INV-{$orderId}* ({$items}) telah selesai.\n\n"
                                . "Dana sewa bersih toko Anda telah berhasil dibukukan ke saldo dompet toko Rentify. Periksa laporan saldo toko Anda di:\n"
                                . "{$vendorPesananUrl}\n\n"
                                . "---\n"
                                . "_Rentify Partner Operations_";

                            WhatsAppService::send($vendorPhone, $pesanVendor);
                        }
                    }
                    break;

                case 'Dibatalkan':
                    if (!empty($custPhone)) {
                        $pesanCust = "*RENTIFY*\n"
                            . "*PESANAN SEWA DIBATALKAN*\n\n"
                            . "Halo *{$customerName}*,\n\n"
                            . "Pesanan sewa *INV-{$orderId}* ({$items}) telah berstatus dibatalkan.\n\n"
                            . ($keterangan ? "• *Keterangan:* {$keterangan}\n\n" : "")
                            . "Apabila Anda membutuhkan informasi lebih lanjut mengenai pengembalian dana atau ingin memesan unit rental lain, hubungi tim dukungan Rentify melalui WhatsApp ini.\n\n"
                            . "---\n"
                            . "_Pesan otomatis dari Sistem Rentify (rentify21.my.id)_";

                        WhatsAppService::send($custPhone, $pesanCust);
                    }

                    if ($vendor) {
                        $vendorPhone = $vendor->whatsapp_vendor ?? $vendor->whatsapp ?? null;
                        if (!empty($vendorPhone)) {
                            $pesanVendor = "*RENTIFY PARTNER*\n"
                                . "*PESANAN SEWA DIBATALKAN*\n\n"
                                . "Halo *{$vendorName}*,\n\n"
                                . "Pesanan sewa *INV-{$orderId}* ({$items}) telah dibatalkan.\n\n"
                                . ($keterangan ? "• *Keterangan:* {$keterangan}\n\n" : "")
                                . "Periksa riwayat pesanan Anda di panel toko:\n"
                                . "{$vendorPesananUrl}\n\n"
                                . "---\n"
                                . "_Rentify Partner Operations_";

                            WhatsAppService::send($vendorPhone, $pesanVendor);
                        }
                    }
                    break;
            }
        } catch (\Exception $e) {
            Log::error('[OrderNotificationService] Gagal notifyOrderStatusUpdated: ' . $e->getMessage());
        }
    }
}
