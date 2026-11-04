<?php

namespace Tests\Unit\Services;

use App\Models\User;
use App\Models\Voucher;
use App\Services\DiscountCalculationEngine;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DiscountCalculationEngineTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Test diskon nominal tanpa batas maksimal (maksimal_diskon = 0).
     */
    public function test_nominal_discount_without_cap(): void
    {
        $voucher = [
            'tipe_diskon'     => 'nominal',
            'nilai_diskon'    => 15000.00,
            'minimal_belanja' => 50000.00,
            'maksimal_diskon' => 0.0,
        ];

        $discount = DiscountCalculationEngine::calculateRentalDiscount($voucher, 100000.00);

        $this->assertSame(15000.00, $discount);
    }

    /**
     * Test diskon nominal dengan pembatasan maksimal_diskon.
     */
    public function test_nominal_discount_with_max_discount_cap(): void
    {
        $voucher = [
            'tipe_diskon'     => 'nominal',
            'nilai_diskon'    => 25000.00,
            'minimal_belanja' => 50000.00,
            'maksimal_diskon' => 20000.00,
        ];

        $discount = DiscountCalculationEngine::calculateRentalDiscount($voucher, 100000.00);

        $this->assertSame(20000.00, $discount);
    }

    /**
     * Test diskon persentase tanpa pembatasan cap.
     */
    public function test_percentage_discount_without_cap(): void
    {
        $voucher = [
            'tipe_diskon'     => 'persen',
            'nilai_diskon'    => 10.0, // 10%
            'minimal_belanja' => 50000.00,
            'maksimal_diskon' => 0.0,
        ];

        $discount = DiscountCalculationEngine::calculateRentalDiscount($voucher, 150000.00);

        $this->assertSame(15000.00, $discount);
    }

    /**
     * Test diskon persentase yang dibatasi oleh maksimal_diskon.
     */
    public function test_percentage_discount_enforces_max_discount_cap(): void
    {
        $voucher = [
            'tipe_diskon'     => 'persen',
            'nilai_diskon'    => 20.0, // 20% dari 100.000 = 20.000
            'minimal_belanja' => 50000.00,
            'maksimal_diskon' => 12500.00, // Capped di 12.500
        ];

        $discount = DiscountCalculationEngine::calculateRentalDiscount($voucher, 100000.00);

        $this->assertSame(12500.00, $discount);
    }

    /**
     * Test penegakan batas minimal belanja (minimal_belanja threshold enforcement).
     */
    public function test_minimum_spend_threshold_enforcement(): void
    {
        $voucher = [
            'tipe_diskon'     => 'nominal',
            'nilai_diskon'    => 10000.00,
            'minimal_belanja' => 50000.00,
            'maksimal_diskon' => 0.0,
        ];

        // Subtotal di bawah minimal_belanja -> diskon harus 0.0
        $discountBelow = DiscountCalculationEngine::calculateRentalDiscount($voucher, 49999.00);
        $this->assertSame(0.0, $discountBelow);

        // Subtotal pas di batas minimal_belanja -> diskon aktif
        $discountExact = DiscountCalculationEngine::calculateRentalDiscount($voucher, 50000.00);
        $this->assertSame(10000.00, $discountExact);

        // Subtotal melebihi batas minimal_belanja -> diskon aktif
        $discountAbove = DiscountCalculationEngine::calculateRentalDiscount($voucher, 75000.00);
        $this->assertSame(10000.00, $discountAbove);
    }

    /**
     * Test kalkulasi shortfall (kekurangan belanja untuk mencapai batas minimal voucher).
     */
    public function test_minimum_spend_shortfall_calculation(): void
    {
        $voucher = [
            'minimal_belanja' => 60000.00,
        ];

        // Shortfall saat subtotal di bawah minimal_belanja
        $shortfall = DiscountCalculationEngine::getMinimumSpendShortfall($voucher, 45000.00);
        $this->assertSame(15000.00, $shortfall);

        // Shortfall saat subtotal sudah memenuhi minimal_belanja
        $shortfallMet = DiscountCalculationEngine::getMinimumSpendShortfall($voucher, 60000.00);
        $this->assertSame(0.0, $shortfallMet);

        // Shortfall saat subtotal melebihi minimal_belanja
        $shortfallExceeded = DiscountCalculationEngine::getMinimumSpendShortfall($voucher, 80000.00);
        $this->assertSame(0.0, $shortfallExceeded);
    }

    /**
     * Test pembatasan non-overflow & pembatasan diskon toko tidak boleh melebihi subtotal sewa dasar.
     */
    public function test_store_discount_bounded_strictly_to_base_rental_subtotal(): void
    {
        $voucher = [
            'tipe_diskon'     => 'nominal',
            'nilai_diskon'    => 150000.00, // Nominal diskon lebih besar dari harga sewa
            'minimal_belanja' => 50000.00,
            'maksimal_diskon' => 0.0,
        ];

        // Sewa dasar 100.000, diskon harus dibatasi tepat di 100.000
        $discount = DiscountCalculationEngine::calculateRentalDiscount($voucher, 100000.00);
        $this->assertSame(100000.00, $discount);
    }

    /**
     * Test kalkulasi diskon menggunakan instance Eloquent model Voucher.
     */
    public function test_calculate_rental_discount_with_eloquent_voucher_model(): void
    {
        $vendor = User::create([
            'name'     => 'Vendor Test ' . uniqid(),
            'email'    => 'vendor_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role'     => 'vendor',
        ]);

        $voucher = Voucher::create([
            'vendor_id'       => $vendor->id,
            'kode_voucher'    => 'DISKONPERSEN' . rand(100, 999),
            'tipe_diskon'     => 'persen',
            'nilai_diskon'    => 15.0,
            'minimal_belanja' => 40000.00,
            'maksimal_diskon' => 10000.00,
            'kuota_total'     => 50,
            'kuota_terpakai'  => 0,
            'tanggal_mulai'   => now()->toDateString(),
            'tanggal_selesai' => now()->addMonth()->toDateString(),
            'is_active'       => true,
        ]);

        $discount = DiscountCalculationEngine::calculateRentalDiscount($voucher, 100000.00);

        // 15% dari 100.000 = 15.000, dibatasi maksimal_diskon = 10.000
        $this->assertSame(10000.00, $discount);
    }

    /**
     * Test vendor balance guard isVendorEligibleForCod pada batas aman (-Rp 49.999 dan -Rp 50.000).
     */
    public function test_is_vendor_eligible_for_cod_at_safe_thresholds(): void
    {
        $vendor1 = User::create([
            'name'     => 'Vendor 49k ' . uniqid(),
            'email'    => 'v49_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role'     => 'vendor',
        ]);

        DB::table('saldos')->insert([
            'vendor_id'     => $vendor1->id,
            'saldo_aktif'   => -49999.00,
            'saldo_ditahan' => 0.0,
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);

        // Saldo -49.999 tanpa fee proyeksi -> >= -50.000 -> True
        $this->assertTrue(DiscountCalculationEngine::isVendorEligibleForCod($vendor1->id, 0.0));

        $vendor2 = User::create([
            'name'     => 'Vendor 50k ' . uniqid(),
            'email'    => 'v50_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role'     => 'vendor',
        ]);

        DB::table('saldos')->insert([
            'vendor_id'     => $vendor2->id,
            'saldo_aktif'   => -50000.00,
            'saldo_ditahan' => 0.0,
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);

        // Saldo pas -50.000 tanpa fee proyeksi -> >= -50.000 -> True
        $this->assertTrue(DiscountCalculationEngine::isVendorEligibleForCod($vendor2->id, 0.0));
    }

    /**
     * Test isVendorEligibleForCod mendeteksi kegagalan saat projected platform fee menyebabkan saldo melewati batas -Rp 50.000.
     */
    public function test_is_vendor_eligible_for_cod_fails_when_projected_fee_breaches_threshold(): void
    {
        $vendor = User::create([
            'name'     => 'Vendor Border ' . uniqid(),
            'email'    => 'vborder_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role'     => 'vendor',
        ]);

        DB::table('saldos')->insert([
            'vendor_id'     => $vendor->id,
            'saldo_aktif'   => -49999.00,
            'saldo_ditahan' => 0.0,
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);

        // Saldo -49.999 dengan projected fee platform Rp 2.00 -> proyeksi -50.001 -> False
        $this->assertFalse(DiscountCalculationEngine::isVendorEligibleForCod($vendor->id, 2.00));

        // Saldo awal sudah -50.001 -> tanpa fee proyeksi tetap False
        DB::table('saldos')->where('vendor_id', $vendor->id)->update(['saldo_aktif' => -50001.00]);
        $this->assertFalse(DiscountCalculationEngine::isVendorEligibleForCod($vendor->id, 0.0));
    }

    /**
     * Test isVendorEligibleForCod untuk vendor baru yang belum memiliki row saldo (default saldo 0).
     */
    public function test_is_vendor_eligible_for_cod_defaults_zero_for_new_vendor(): void
    {
        $newVendor = User::create([
            'name'     => 'Vendor New ' . uniqid(),
            'email'    => 'vnew_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role'     => 'vendor',
        ]);

        // Belum ada row di saldos, saldo aktif = 0.0. Projected fee 500 -> projected balance -500.00 >= -50.000 -> True
        $this->assertTrue(DiscountCalculationEngine::isVendorEligibleForCod($newVendor->id, 500.00));
    }

    /**
     * Test isolasi komisi Rentify 5%: markup platform tetap utuh dan wajib dibayar customer meski ada voucher toko 100%.
     */
    public function test_evaluate_waterfall_rentify_fee_isolation_from_vendor_store_discounts(): void
    {
        $params = [
            'subtotal_sewa_asli'    => 10000.00,
            'potongan_voucher_toko' => 10000.00, // Diskon toko 100% dari harga dasar vendor
            'shipping_fee'          => 0.0,
            'total_deposit'         => 0.0,
            'payment_method'        => 'ONLINE',
            'kode_unik'             => 0,
        ];

        $breakdown = DiscountCalculationEngine::evaluateWaterfall($params);

        // Harga dasar vendor: 10.000
        // Markup 5%: round(10.000 * 1.05, 2) = 10.500
        // Fee Rentify: 500
        $this->assertSame(10000.00, $breakdown['subtotal_sewa_asli']);
        $this->assertSame(10500.00, $breakdown['subtotal_sewa_markup']);
        $this->assertSame(500.00, $breakdown['rentify_fee']);

        // Voucher toko memotong 10.000 sewa dasar, sisa sewa dasar = 0
        $this->assertSame(10000.00, $breakdown['potongan_voucher_toko']);
        $this->assertSame(0.0, $breakdown['remaining_rental']);

        // Hak penghasilan vendor jadi 0
        $this->assertSame(0.0, $breakdown['vendor_earning']);

        // Customer tetap wajib membayar komisi markup platform 5% (Rp 500)
        $this->assertSame(500.00, $breakdown['total_biaya']);
        $this->assertSame(500.00, $breakdown['total_price']);
    }

    /**
     * Test non-overflow assertion: diskon berlebih tidak boleh mengurangi ongkos kirim atau deposit jaminan.
     */
    public function test_evaluate_waterfall_non_overflow_into_shipping_or_deposit(): void
    {
        $params = [
            'subtotal_sewa_asli'        => 10000.00,
            'potongan_voucher_toko'     => 15000.00, // Mencoba diskon melebihi sewa asli
            'potongan_voucher_platform' => 5000.00,
            'potongan_koin'             => 5000.00,
            'shipping_fee'              => 12000.00,
            'total_deposit'             => 50000.00,
            'payment_method'            => 'ONLINE',
            'kode_unik'                 => 100,
        ];

        $breakdown = DiscountCalculationEngine::evaluateWaterfall($params);

        // Diskon toko dibatasi maksimal sewa asli (10.000)
        $this->assertSame(10000.00, $breakdown['potongan_voucher_toko']);
        // Setelah sewa dasar habis, voucher platform dan koin terpasung di 0
        $this->assertSame(0.0, $breakdown['potongan_voucher_platform']);
        $this->assertSame(0.0, $breakdown['potongan_koin']);

        // Ongkos kirim dan deposit tetap utuh 100%
        $this->assertSame(12000.00, $breakdown['shipping_fee']);
        $this->assertSame(50000.00, $breakdown['total_deposit']);
        $this->assertSame(100, $breakdown['kode_unik']);

        // Total tagihan = 500 (fee platform) + 12.000 (ongkir) + 50.000 (deposit) + 100 (kode unik) = 62.600
        $this->assertSame(62600.00, $breakdown['total_biaya']);
        $this->assertSame(62600.00, $breakdown['total_price']);

        // Hak vendor = 0 (sewa terpotong voucher toko) + 12.000 (ongkir utuh) = 12.000
        $this->assertSame(12000.00, $breakdown['vendor_earning']);
    }

    /**
     * Test pemotongan Koin Rentify dibatasi maksimal 50% dari sisa sewa setelah seluruh voucher.
     */
    public function test_evaluate_waterfall_coin_discount_capped_at_fifty_percent(): void
    {
        $params = [
            'subtotal_sewa_asli'        => 100000.00,
            'potongan_voucher_toko'     => 20000.00, // Sisa sewa dasar = 80.000
            'potongan_voucher_platform' => 20000.00, // Sisa sewa dasar = 60.000
            'potongan_koin'             => 40000.00, // Diminta 40.000 koin, tapi plafon 50% dari 60.000 = 30.000
            'shipping_fee'              => 10000.00,
            'total_deposit'             => 0.0,
            'payment_method'            => 'ONLINE',
            'kode_unik'                 => 0,
        ];

        $breakdown = DiscountCalculationEngine::evaluateWaterfall($params);

        // Koin harus dicap pada 30.000
        $this->assertSame(30000.00, $breakdown['potongan_koin']);
        // Sisa sewa setelah koin = 60.000 - 30.000 = 30.000
        $this->assertSame(30000.00, $breakdown['remaining_rental']);

        // Fee Rentify 5% dari 100.000 = 5.000
        $this->assertSame(5000.00, $breakdown['rentify_fee']);

        // Total customer = 30.000 (sisa sewa) + 5.000 (fee) + 10.000 (ongkir) = 45.000
        $this->assertSame(45000.00, $breakdown['total_biaya']);
    }

    /**
     * Test isolasi kode_unik: wajib bernilai 0 untuk COD dan dipertahankan untuk transfer online.
     */
    public function test_evaluate_waterfall_kode_unik_isolation(): void
    {
        // Kasus 1: Metode pembayaran COD -> kode_unik wajib dipaksa 0 tanpa syarat
        $codParams = [
            'subtotal_sewa_asli' => 50000.00,
            'payment_method'     => 'COD',
            'kode_unik'          => 482, // Nilai kode unik yang dicoba dimasukkan
            'shipping_fee'       => 5000.00,
            'total_deposit'      => 0.0,
        ];

        $codBreakdown = DiscountCalculationEngine::evaluateWaterfall($codParams);
        $this->assertSame(0, $codBreakdown['kode_unik'], 'COD orders must unconditionally set kode_unik to 0');
        // Total = 50.000 + 2.500 (fee 5%) + 5.000 (ongkir) + 0 (kode unik) = 57.500
        $this->assertSame(57500.00, $codBreakdown['total_biaya']);

        // Kasus 2: Metode pembayaran ONLINE -> kode unik dipertahankan untuk verifikasi transfer
        $onlineParams = [
            'subtotal_sewa_asli' => 50000.00,
            'payment_method'     => 'ONLINE',
            'kode_unik'          => 482,
            'shipping_fee'       => 5000.00,
            'total_deposit'      => 0.0,
        ];

        $onlineBreakdown = DiscountCalculationEngine::evaluateWaterfall($onlineParams);
        $this->assertSame(482, $onlineBreakdown['kode_unik'], 'Online orders must preserve 3-digit kode_unik');
        // Total = 50.000 + 2.500 + 5.000 + 482 = 57.982
        $this->assertSame(57982.00, $onlineBreakdown['total_biaya']);
    }

    /**
     * Test kesetaraan pemanggilan statis dan instance pada DiscountCalculationEngine.
     */
    public function test_static_and_instance_invocation_equivalence(): void
    {
        $engine = new DiscountCalculationEngine();

        $voucher = [
            'tipe_diskon'     => 'nominal',
            'nilai_diskon'    => 5000.00,
            'minimal_belanja' => 20000.00,
        ];

        $resStatic = DiscountCalculationEngine::calculateRentalDiscount($voucher, 30000.00);
        $resInstance = $engine->calculateRentalDiscount($voucher, 30000.00);

        $this->assertSame($resStatic, $resInstance);
    }

    /**
     * Test diskon 100% maksimal 3 barang yang di-CO (berfungsi untuk <= 3 barang, ditolak jika > 3 barang).
     */
    public function test_100_percent_discount_with_max_quantity_limit(): void
    {
        $voucher = [
            'tipe_diskon'  => 'persen',
            'nilai_diskon' => 100.0,
            'max_quantity' => 3,
        ];

        // 2 barang: lolos, diskon 100%
        $discount2 = DiscountCalculationEngine::calculateRentalDiscount($voucher, 150000.00, 2);
        $this->assertSame(150000.00, $discount2);

        // 3 barang: lolos, diskon 100%
        $discount3 = DiscountCalculationEngine::calculateRentalDiscount($voucher, 225000.00, 3);
        $this->assertSame(225000.00, $discount3);

        // 4 barang: melebihi maksimal 3 barang -> diskon 0 (voucher ditolak)
        $discount4 = DiscountCalculationEngine::calculateRentalDiscount($voucher, 300000.00, 4);
        $this->assertSame(0.0, $discount4);
    }

    /**
     * Test diskon 50% maksimal 5 barang (berfungsi jika <= 5 barang, ditolak jika > 5 barang).
     */
    public function test_50_percent_discount_with_max_quantity_5(): void
    {
        $voucher = [
            'tipe_diskon'  => 'persen',
            'nilai_diskon' => 50.0,
            'max_quantity' => 5,
        ];

        // 3 barang (< 5): tetap berfungsi diskon 50%
        $discount3 = DiscountCalculationEngine::calculateRentalDiscount($voucher, 300000.00, 3);
        $this->assertSame(150000.00, $discount3);

        // 5 barang (tepat 5): berfungsi diskon 50%
        $discount5 = DiscountCalculationEngine::calculateRentalDiscount($voucher, 500000.00, 5);
        $this->assertSame(250000.00, $discount5);

        // 6 barang (> 5): ditolak
        $discount6 = DiscountCalculationEngine::calculateRentalDiscount($voucher, 600000.00, 6);
        $this->assertSame(0.0, $discount6);
    }
}
