<?php

namespace App\Services;

use App\Models\Voucher;
use Illuminate\Support\Facades\DB;

class DiscountCalculationEngine
{
    /**
     * Platform markup commission rate (5%).
     */
    public const PLATFORM_FEE_RATE = 0.05;

    /**
     * COD vendor active balance debt safety threshold (-Rp 50,000.00).
     */
    public const COD_DEBT_SAFETY_THRESHOLD = -50000.00;

    /**
     * Maximum percentage of post-voucher rental subtotal that can be offset by Rentify Coins (50%).
     */
    public const COIN_RENTAL_DISCOUNT_CAP_RATE = 0.50;

    /**
     * Hitung nilai diskon sewa dari voucher toko maupun voucher platform.
     *
     * @param Voucher|array $voucher
     * @param float $eligibleRentalSubtotal  Subtotal sewa barang yang memenuhi syarat
     * @param int   $totalQuantity           Jumlah kuantitas item dalam keranjang / pesanan
     * @return float Nilai diskon yang telah dibatasi dan dibulatkan
     */
    public static function calculateRentalDiscount(
        Voucher|array $voucher,
        float $eligibleRentalSubtotal,
        int $totalQuantity = 1
    ): float {
        $eligibleRentalSubtotal = max(0.0, (float) $eligibleRentalSubtotal);
        if ($eligibleRentalSubtotal <= 0.0) {
            return 0.0;
        }

        $minQuantity = (int) (is_array($voucher)
            ? ($voucher['min_quantity'] ?? 1)
            : ($voucher->min_quantity ?? 1));

        // Penegakan batas kuantitas minimum (BOGO Quantity Guard)
        if ($totalQuantity < $minQuantity) {
            return 0.0;
        }

        $minimalBelanja = (float) (is_array($voucher)
            ? ($voucher['minimal_belanja'] ?? $voucher['min_spend'] ?? 0.0)
            : ($voucher->minimal_belanja ?? 0.0));

        // Jika subtotal sewa belum memenuhi batas minimal belanja, diskon bernilai 0
        if ($eligibleRentalSubtotal < $minimalBelanja) {
            return 0.0;
        }

        $tipeDiskon = strtolower((string) (is_array($voucher)
            ? ($voucher['tipe_diskon'] ?? $voucher['discount_type'] ?? 'nominal')
            : ($voucher->tipe_diskon ?? 'nominal')));

        $nilaiDiskon = (float) (is_array($voucher)
            ? ($voucher['nilai_diskon'] ?? $voucher['discount_value'] ?? 0.0)
            : ($voucher->nilai_diskon ?? 0.0));

        $maksimalDiskon = (float) (is_array($voucher)
            ? ($voucher['maksimal_diskon'] ?? $voucher['max_discount'] ?? 0.0)
            : ($voucher->maksimal_diskon ?? 0.0));

        $kodeVoucher = strtoupper((string) (is_array($voucher)
            ? ($voucher['kode_voucher'] ?? $voucher['code'] ?? '')
            : ($voucher->kode_voucher ?? '')));

        // Evaluasi BOGO Sewa 3 Bayar 2 (Rasio Eksak Rasional 1/3: ceil(($eligibleSubtotal / 3.0) * 100) / 100)
        $isBogo3b2 = in_array($tipeDiskon, ['bogo_3_bayar_2', 'bogo_3b2', 'bogo_3_2', 'sewa_3_bayar_2', 'sewa_3_2'])
            || ($tipeDiskon === 'bogo' && $minQuantity === 3)
            || (str_contains($kodeVoucher, '3B2') || str_contains($kodeVoucher, 'BOGO3') || str_contains($kodeVoucher, 'SEWA3'))
            || ($minQuantity === 3 && abs($nilaiDiskon - (100.0 / 3.0)) < 0.5);

        // Evaluasi BOGO Sewa 2 Bayar 1 (Diskon 50%)
        $isBogo2b1 = in_array($tipeDiskon, ['bogo_2_bayar_1', 'bogo_2b1', 'bogo_2_1', 'sewa_2_bayar_1', 'sewa_2_1'])
            || ($tipeDiskon === 'bogo' && ($minQuantity === 2 || $minQuantity === 1))
            || (str_contains($kodeVoucher, '2B1') || str_contains($kodeVoucher, 'BOGO2') || str_contains($kodeVoucher, 'SEWA2'))
            || ($minQuantity === 2 && in_array($tipeDiskon, ['persen', 'percentage', 'percent']) && abs($nilaiDiskon - 50.0) < 0.01);

        if ($isBogo3b2) {
            $discount = ceil(($eligibleRentalSubtotal / 3.0) * 100.0) / 100.0;
            if ($maksimalDiskon > 0.0) {
                $discount = min($discount, $maksimalDiskon);
            }
        } elseif ($isBogo2b1) {
            $discount = ($eligibleRentalSubtotal * 50.0) / 100.0;
            if ($maksimalDiskon > 0.0) {
                $discount = min($discount, $maksimalDiskon);
            }
        } elseif (in_array($tipeDiskon, ['persen', 'percentage', 'percent'])) {
            $discount = ($eligibleRentalSubtotal * $nilaiDiskon) / 100.0;
            if ($maksimalDiskon > 0.0) {
                $discount = min($discount, $maksimalDiskon);
            }
        } else {
            $discount = $nilaiDiskon;
            if ($maksimalDiskon > 0.0) {
                $discount = min($discount, $maksimalDiskon);
            }
        }

        // Penegakan non-negative flooring
        $discount = max(0.0, $discount);

        // Diskon tidak boleh melebihi subtotal sewa barang murni (subtotal_sewa_asli)
        $discount = min($discount, $eligibleRentalSubtotal);

        return round($discount, 2);
    }

    /**
     * Hitung kekurangan belanja terhadap batas minimal voucher (shortfall).
     *
     * @param Voucher|array $voucher
     * @param float $eligibleRentalSubtotal
     * @return float Nominal kekurangan untuk memenuhi minimal_belanja (0 jika sudah cukup)
     */
    public static function getMinimumSpendShortfall(Voucher|array $voucher, float $eligibleRentalSubtotal): float
    {
        $minimalBelanja = (float) (is_array($voucher)
            ? ($voucher['minimal_belanja'] ?? $voucher['min_spend'] ?? 0.0)
            : ($voucher->minimal_belanja ?? 0.0));

        $eligibleRentalSubtotal = max(0.0, (float) $eligibleRentalSubtotal);

        return round(max(0.0, $minimalBelanja - $eligibleRentalSubtotal), 2);
    }

    /**
     * Periksa kualifikasi vendor untuk menerima order dengan metode pembayaran COD.
     * Mencegah vendor menumpuk tunggakan komisi platform melewati batas toleransi utang (-Rp 50.000).
     *
     * @param int $vendorId
     * @param float $projectedRentifyFee Komisi 5% yang diproyeksikan didebit saat COD
     * @return bool True jika saldo proyeksi >= -50.000, False jika di bawah batas aman
     */
    public static function isVendorEligibleForCod(int $vendorId, float $projectedRentifyFee = 0.0): bool
    {
        $saldoAktif = (float) (DB::table('saldos')
            ->where('vendor_id', $vendorId)
            ->value('saldo_aktif') ?? 0.0);

        $projectedRentifyFee = max(0.0, (float) $projectedRentifyFee);
        $projectedBalance = round($saldoAktif - $projectedRentifyFee, 2);

        return $projectedBalance >= self::COD_DEBT_SAFETY_THRESHOLD;
    }

    /**
     * Evaluasi alur pemotongan bertingkat (Waterfall Deduction Pipeline).
     *
     * Alur deduksi:
     * Step 1: Hitung markup sewa 5% (subtotal_sewa_markup = round($subtotal * 1.05, 2)) & fee Rentify.
     * Step 2: Kurangkan voucher toko (dibatasi maksimal sewa dasar, fee platform tetap dibayar customer).
     * Step 3: Kurangkan voucher platform dari sisa sewa yang memenuhi syarat.
     * Step 4: Kurangkan koin Rentify, dibatasi maksimal 50% dari sisa sewa setelah voucher.
     * Step 5: Tetapkan kode unik (0 untuk COD, preserved untuk transfer online). Tambahkan ongkir & deposit.
     * Step 6: Verifikasi non-overflow invariant (diskon dilarang memakan ongkir atau deposit).
     *
     * @param array $params
     * @return array Rincian menyeluruh breakdown keuangan pesanan
     */
    public static function evaluateWaterfall(array $params): array
    {
        // Parameter input dengan sanitasi non-negatif
        $subtotalSewaAsli = max(0.0, (float) ($params['subtotal_sewa_asli'] ?? $params['subtotal_sewa'] ?? $params['subtotal'] ?? 0.0));
        $shippingFee = max(0.0, (float) ($params['shipping_fee'] ?? $params['ongkir'] ?? 0.0));
        $totalDeposit = max(0.0, (float) ($params['total_deposit'] ?? $params['deposit'] ?? 0.0));

        $rawVoucherToko = max(0.0, (float) ($params['potongan_voucher_toko'] ?? $params['voucher_toko'] ?? $params['potongan_voucher'] ?? 0.0));
        $rawVoucherPlatform = max(0.0, (float) ($params['potongan_voucher_platform'] ?? $params['voucher_platform'] ?? 0.0));
        $rawKoin = max(0.0, (float) ($params['potongan_koin'] ?? $params['koin'] ?? 0.0));

        $paymentMethod = strtoupper(trim((string) ($params['payment_method'] ?? $params['metode_pembayaran'] ?? 'ONLINE')));
        $rawKodeUnik = max(0, (int) ($params['kode_unik'] ?? 0));

        // Step 1: Markup 5% & Fee Platform
        $subtotalSewaMarkup = round($subtotalSewaAsli * (1 + self::PLATFORM_FEE_RATE), 2);
        $rentifyFee = round($subtotalSewaMarkup - $subtotalSewaAsli, 2);

        // Step 2: Voucher Toko - dibatasi maksimal subtotal_sewa_asli
        $potonganVoucherToko = min($rawVoucherToko, $subtotalSewaAsli);
        $potonganVoucherToko = round($potonganVoucherToko, 2);
        $remainingAfterStore = round($subtotalSewaAsli - $potonganVoucherToko, 2);

        // Step 3: Voucher Platform - dibatasi sisa sewa dasar setelah voucher toko
        $potonganVoucherPlatform = min($rawVoucherPlatform, $remainingAfterStore);
        $potonganVoucherPlatform = round($potonganVoucherPlatform, 2);
        $remainingAfterPlatform = round($remainingAfterStore - $potonganVoucherPlatform, 2);

        // Step 4: Koin Rentify - dibatasi maksimal 50% dari sisa sewa setelah seluruh voucher
        $maxCoinDiscount = round($remainingAfterPlatform * self::COIN_RENTAL_DISCOUNT_CAP_RATE, 2);
        $potonganKoin = min($rawKoin, $maxCoinDiscount);
        $potonganKoin = round($potonganKoin, 2);
        $remainingRental = round($remainingAfterPlatform - $potonganKoin, 2);

        // Step 5: Kode unik & perhitungan tagihan akhir pelanggan
        // COD unconditionally kode_unik = 0
        $kodeUnik = ($paymentMethod === 'COD') ? 0 : $rawKodeUnik;

        // Tagihan sewa bersih pelanggan = sisa sewa murni + rentify_fee markup 5% yang utuh
        $netCustomerRental = round($remainingRental + $rentifyFee, 2);

        $totalBiaya = round($netCustomerRental + $shippingFee + $totalDeposit + $kodeUnik, 2);

        // Step 6: Verifikasi non-overflow invariant
        // Diskon tidak boleh memakan ongkir, deposit, atau fee platform
        $totalDiscount = round($potonganVoucherToko + $potonganVoucherPlatform + $potonganKoin, 2);
        $minRequiredTotal = round($rentifyFee + $shippingFee + $totalDeposit + $kodeUnik, 2);
        if ($totalBiaya < $minRequiredTotal) {
            $totalBiaya = $minRequiredTotal;
        }

        // Hak penghasilan vendor: Harga sewa dasar dikurangi diskon voucher toko + ongkir utuh
        $vendorEarning = round(max(0.0, $subtotalSewaAsli - $potonganVoucherToko) + $shippingFee, 2);

        return [
            'subtotal_sewa_asli'        => $subtotalSewaAsli,
            'subtotal_sewa_markup'      => $subtotalSewaMarkup,
            'rentify_fee'               => $rentifyFee,
            'potongan_voucher_toko'     => $potonganVoucherToko,
            'potongan_voucher_platform' => $potonganVoucherPlatform,
            'potongan_koin'             => $potonganKoin,
            'remaining_rental'          => $remainingRental,
            'shipping_fee'              => $shippingFee,
            'total_deposit'             => $totalDeposit,
            'kode_unik'                 => $kodeUnik,
            'payment_method'            => $paymentMethod,
            'vendor_earning'            => $vendorEarning,
            'total_diskon'              => $totalDiscount,
            'total_discount'            => $totalDiscount,
            'total_potongan'            => $totalDiscount,
            'potongan_voucher'          => round($potonganVoucherToko + $potonganVoucherPlatform, 2),
            'total_biaya'               => $totalBiaya,
            'total_price'               => $totalBiaya,
        ];
    }
}
