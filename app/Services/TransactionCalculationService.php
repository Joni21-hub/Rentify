<?php

namespace App\Services;

use App\Models\Penyewaan;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TransactionCalculationService
{
    /**
     * Platform commission rate (5%).
     */
    public const PLATFORM_FEE_RATE = 0.05;

    /**
     * Hitung total rincian biaya pesanan sewa baru pada saat checkout.
     *
     * @param float $subtotalSewaAsli  Total harga sewa dasar dari vendor (harga_sewa_harian * qty * durasi)
     * @param float $shippingFee       Ongkos kirim (100% milik vendor / kurir pengantar)
     * @param float $voucherDiscount   Potongan diskon voucher dari toko vendor
     * @param int   $kodeUnik          Kode unik 3-digit verifikasi otomatis
     * @return array
     */
    public static function calculateOrderPricing(
        float $subtotalSewaAsli,
        float $shippingFee = 0.0,
        float $voucherDiscount = 0.0,
        int $kodeUnik = 0
    ): array {
        $subtotalSewaAsli = max(0, $subtotalSewaAsli);
        $shippingFee = max(0, $shippingFee);
        $voucherDiscount = max(0, $voucherDiscount);
        $kodeUnik = max(0, $kodeUnik);

        // Fee platform 5% dihitung dari harga sewa barang murni sebelum diskon voucher
        $subtotalSewaMarkup = round($subtotalSewaAsli * (1 + self::PLATFORM_FEE_RATE), 2);
        $rentifyFee = round($subtotalSewaMarkup - $subtotalSewaAsli, 2);

        // Hak penghasilan vendor: Harga sewa dasar dikurangi diskon voucher toko + ongkir utuh
        $vendorEarning = round(max(0, $subtotalSewaAsli - $voucherDiscount) + $shippingFee, 2);

        // Total tagihan akhir pelanggan: (Sewa Markup - Voucher) + Ongkir + Kode Unik
        $totalCustomer = round(max(0, $subtotalSewaMarkup - $voucherDiscount) + $shippingFee + $kodeUnik, 2);

        return [
            'subtotal_sewa_asli'   => $subtotalSewaAsli,
            'subtotal_sewa_markup' => $subtotalSewaMarkup,
            'rentify_fee'          => $rentifyFee,
            'potongan_voucher'     => $voucherDiscount,
            'shipping_fee'         => $shippingFee,
            'kode_unik'            => $kodeUnik,
            'vendor_earning'       => $vendorEarning,
            'total_biaya'          => $totalCustomer,
            'total_price'          => $totalCustomer,
        ];
    }

    /**
     * Ambil atau rekonstruksi rincian keuangan pesanan secara akurat dari data yang tersimpan di DB.
     *
     * @param object|array $order
     * @return array
     */
    public static function getBreakdownForOrder($order): array
    {
        $obj = is_array($order) ? (object) $order : $order;

        $totalTagihan    = (float) ($obj->total_price ?? $obj->total_biaya ?? 0);
        $shippingFee     = (float) ($obj->shipping_fee ?? 0);
        $voucherDiscount = (float) ($obj->potongan_voucher ?? 0);
        $kodeUnik        = (int) ($obj->kode_unik ?? 0);

        $storedFee       = (float) ($obj->rentify_fee ?? 0);
        $storedEarning   = (float) ($obj->vendor_earning ?? 0);

        if ($storedFee > 0 && $storedEarning > 0) {
            $rentifyFee    = $storedFee;
            $vendorEarning = $storedEarning;
            $sewaMarkup    = max(0, $totalTagihan - $shippingFee - $kodeUnik + $voucherDiscount);
            $sewaAsli      = max(0, $sewaMarkup - $rentifyFee);
        } else {
            // Rekonstruksi jika nilai belum pernah tersimpan di database
            // Sewa markup customer sebelum diskon voucher & tanpa ongkir & kode unik
            $sewaMarkup = max(0, $totalTagihan - $shippingFee - $kodeUnik + $voucherDiscount);
            $sewaAsli   = round($sewaMarkup / (1 + self::PLATFORM_FEE_RATE), 2);
            $rentifyFee = round($sewaMarkup - $sewaAsli, 2);

            // Hak vendor: sewa asli - voucher + ongkos kirim
            $vendorEarning = round(max(0, $sewaAsli - $voucherDiscount) + $shippingFee, 2);
        }

        return [
            'subtotal_sewa_asli'   => $sewaAsli,
            'subtotal_sewa_markup' => $sewaMarkup,
            'rentify_fee'          => $rentifyFee,
            'vendor_earning'       => $vendorEarning,
            'potongan_voucher'     => $voucherDiscount,
            'shipping_fee'         => $shippingFee,
            'kode_unik'            => $kodeUnik,
            'total_biaya'          => $totalTagihan,
            'total_price'          => $totalTagihan,
        ];
    }

    /**
     * Selesaikan pesanan sewa secara atomik (penyelesaian transaksi & saldo dompet vendor).
     * Dapat dipanggil dari alur Vendor maupun Customer tanpa duplikasi ataupun inkonsistensi.
     *
     * @param int|object $orderOrId
     * @return array
     */
    public static function settleOrderCompletion($orderOrId): array
    {
        return DB::transaction(function () use ($orderOrId) {
            $orderId = is_numeric($orderOrId) ? (int) $orderOrId : (int) $orderOrId->id;

            // Lock baris order untuk mencegah race condition atau double settlement
            $order = DB::table('orders')->where('id', $orderId)->lockForUpdate()->first();

            if (!$order) {
                throw new \InvalidArgumentException("Pesanan dengan ID #{$orderId} tidak ditemukan.");
            }

            $vendorId = $order->vendor_id;
            if (!$vendorId) {
                throw new \RuntimeException("Pesanan #{$orderId} tidak memiliki vendor_id yang valid.");
            }

            // Pastikan row saldo vendor ada dan terkunci
            $saldoVendor = DB::table('saldos')->where('vendor_id', $vendorId)->lockForUpdate()->first();
            if (!$saldoVendor) {
                DB::table('saldos')->insert([
                    'vendor_id'     => $vendorId,
                    'saldo_aktif'   => 0,
                    'saldo_ditahan' => 0,
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);
                $saldoVendor = DB::table('saldos')->where('vendor_id', $vendorId)->lockForUpdate()->first();
            }

            $breakdown = self::getBreakdownForOrder($order);
            $rentifyFee = $breakdown['rentify_fee'];
            $vendorEarning = $breakdown['vendor_earning'];
            $paymentMethod = strtoupper(trim($order->payment_method ?? 'ONLINE'));

            $isAlreadyCompleted = in_array(strtolower(trim($order->status ?? '')), ['selesai', 'menunggu ulasan']);
            $saldoAwal = (float) $saldoVendor->saldo_aktif;
            $saldoBaru = $saldoAwal;

            // Hanya mutasikan saldo jika status order belum pernah 'Selesai'
            if (!$isAlreadyCompleted) {
                if ($paymentMethod === 'COD') {
                    // Vendor sudah menerima tunai 100% dari customer.
                    // Sistem mendebit hak komisi platform 5% dari dompet vendor.
                    $saldoBaru = round($saldoAwal - $rentifyFee, 2);

                    // Peringatan batas minus saldo (komisi tertunggak)
                    if ($saldoBaru < -50000) {
                        Log::warning("[TransactionCalculationService] Vendor #{$vendorId} menunggak komisi COD melebihi batas (saldo: {$saldoBaru})");
                    }
                } else {
                    // Pembayaran Online (QRIS, E-Wallet, Transfer):
                    // Uang dipegang sistem. Pastikan pembayaran sudah terverifikasi lunas sebelum saldo dikreditkan ke vendor.
                    $pembayaran = DB::table('pembayarans')->where('order_id', $orderId)->first();
                    $isExplicitlyUnpaid = ($pembayaran && in_array(strtoupper(trim($pembayaran->status ?? '')), ['MENUNGGU KONFIRMASI', 'PENDING', 'FAILED', 'EXPIRED']) && is_null($order->paid_at) && is_null($pembayaran->paid_at));
                    $isPendingOrder = in_array(strtoupper(trim($order->status ?? '')), ['PENDING_PAYMENT', 'MENUNGGU PEMBAYARAN', 'MENUNGGU KONFIRMASI']);

                    if ($isExplicitlyUnpaid || ($isPendingOrder && is_null($order->paid_at))) {
                        throw new \RuntimeException("Pesanan #{$orderId} belum berstatus Lunas. Saldo vendor tidak dapat dicairkan sebelum pembayaran terverifikasi masuk.");
                    }

                    // Sistem mengkredit hak vendor_earning utuh ke saldo aktif vendor.
                    $saldoBaru = round($saldoAwal + $vendorEarning, 2);
                }

                DB::table('saldos')
                    ->where('vendor_id', $vendorId)
                    ->update([
                        'saldo_aktif' => $saldoBaru,
                        'updated_at'  => now(),
                    ]);
            }

            $completedAt = $order->completed_at ?? Carbon::now('Asia/Jakarta');

            DB::table('orders')
                ->where('id', $orderId)
                ->update([
                    'status'         => 'Selesai',
                    'vendor_earning' => $vendorEarning,
                    'rentify_fee'    => $rentifyFee,
                    'completed_at'   => $completedAt,
                    'updated_at'     => Carbon::now('Asia/Jakarta'),
                ]);

            Log::info("[Transaction Settlement] Order #{$orderId} diselesaikan", [
                'order_id'          => $orderId,
                'vendor_id'         => $vendorId,
                'payment_method'    => $paymentMethod,
                'vendor_earning'    => $vendorEarning,
                'rentify_fee'       => $rentifyFee,
                'saldo_sebelum'     => $saldoAwal,
                'saldo_sesudah'     => $saldoBaru,
                'was_already_done'  => $isAlreadyCompleted,
            ]);

            return [
                'order_id'         => $orderId,
                'vendor_id'        => $vendorId,
                'status'           => 'Selesai',
                'payment_method'   => $paymentMethod,
                'vendor_earning'   => $vendorEarning,
                'rentify_fee'      => $rentifyFee,
                'saldo_sebelum'    => $saldoAwal,
                'saldo_sesudah'    => $saldoBaru,
                'breakdown'        => $breakdown,
                'was_already_done' => $isAlreadyCompleted,
            ];
        });
    }

    /**
     * Batalkan penyelesaian pesanan sewa secara atomik (revert saldo dompet vendor & status).
     * Digunakan saat pesanan yang telah 'Selesai' dibatalkan/direfund oleh Admin.
     *
     * @param int|object $orderOrId
     * @param string $reason
     * @return array
     */
    public static function revertOrderSettlement($orderOrId, string $reason = ''): array
    {
        return DB::transaction(function () use ($orderOrId, $reason) {
            $orderId = is_numeric($orderOrId) ? (int) $orderOrId : (int) $orderOrId->id;

            $order = DB::table('orders')->where('id', $orderId)->lockForUpdate()->first();
            if (!$order) {
                throw new \InvalidArgumentException("Pesanan dengan ID #{$orderId} tidak ditemukan.");
            }

            $vendorId = $order->vendor_id;
            if (!$vendorId) {
                throw new \RuntimeException("Pesanan #{$orderId} tidak memiliki vendor_id yang valid.");
            }

            // Pastikan status saat ini adalah 'Selesai' sebelum di-revert
            if (strtolower($order->status ?? '') !== 'selesai') {
                return [
                    'order_id' => $orderId,
                    'status'   => $order->status,
                    'reverted' => false,
                    'message'  => 'Pesanan belum dalam status Selesai, mutasi saldo tidak perlu dibatalkan.'
                ];
            }

            $saldoVendor = DB::table('saldos')->where('vendor_id', $vendorId)->lockForUpdate()->first();
            if (!$saldoVendor) {
                DB::table('saldos')->insert([
                    'vendor_id'     => $vendorId,
                    'saldo_aktif'   => 0,
                    'saldo_ditahan' => 0,
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);
                $saldoVendor = DB::table('saldos')->where('vendor_id', $vendorId)->lockForUpdate()->first();
            }

            $breakdown = self::getBreakdownForOrder($order);
            $rentifyFee = $breakdown['rentify_fee'];
            $vendorEarning = $breakdown['vendor_earning'];
            $paymentMethod = strtoupper(trim($order->payment_method ?? 'ONLINE'));

            $saldoAwal = (float) $saldoVendor->saldo_aktif;

            if ($paymentMethod === 'COD') {
                // Pada COD, fee 5% sebelumnya didebit dari dompet vendor.
                // Saat revert/batal, kembalikan fee 5% tersebut ke saldo vendor.
                $saldoBaru = round($saldoAwal + $rentifyFee, 2);
            } else {
                // Pada Online, vendor sebelumnya dikredit vendor_earning.
                // Saat revert/batal, tarik kembali vendor_earning dari saldo vendor (izinkan minus jika saldo tidak cukup).
                $saldoBaru = round($saldoAwal - $vendorEarning, 2);
            }

            DB::table('saldos')
                ->where('vendor_id', $vendorId)
                ->update([
                    'saldo_aktif' => $saldoBaru,
                    'updated_at'  => now(),
                ]);

            DB::table('orders')
                ->where('id', $orderId)
                ->update([
                    'status'     => 'Dibatalkan',
                    'updated_at' => Carbon::now('Asia/Jakarta'),
                ]);

            Log::warning("[Transaction Settlement Reverted] Order #{$orderId} dibatalkan dari status Selesai", [
                'order_id'       => $orderId,
                'vendor_id'      => $vendorId,
                'payment_method' => $paymentMethod,
                'vendor_earning' => $vendorEarning,
                'rentify_fee'    => $rentifyFee,
                'saldo_sebelum'  => $saldoAwal,
                'saldo_sesudah'  => $saldoBaru,
                'reason'         => $reason,
            ]);

            return [
                'order_id'      => $orderId,
                'vendor_id'     => $vendorId,
                'status'        => 'Dibatalkan',
                'reverted'      => true,
                'saldo_sebelum' => $saldoAwal,
                'saldo_sesudah' => $saldoBaru,
            ];
        });
    }
}
