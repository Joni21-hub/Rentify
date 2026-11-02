<?php

namespace Tests\Unit\Services;

use App\Models\Barang;
use App\Models\DetailPenyewaan;
use App\Models\Kategori;
use App\Models\Penyewaan;
use App\Models\User;
use App\Models\Voucher;
use App\Services\MultiVendorProrationService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class MultiVendorProrationServiceTest extends TestCase
{
    use DatabaseTransactions;

    protected MultiVendorProrationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new MultiVendorProrationService();
    }

    // =========================================================================
    // 1. Tests for calculateMaxPlatformDiscount (TASK-189)
    // =========================================================================

    public function test_calculate_max_platform_discount_returns_zero_when_below_minimum_spend(): void
    {
        $voucher = new Voucher([
            'tipe_diskon'     => 'nominal',
            'nilai_diskon'    => 20000.00,
            'minimal_belanja' => 100000.00,
        ]);

        // Belanja 99.999 (< 100.000)
        $discount = $this->service->calculateMaxPlatformDiscount($voucher, 99999.00);
        $this->assertSame(0.0, $discount);

        // Belanja 0
        $discountZero = $this->service->calculateMaxPlatformDiscount($voucher, 0.0);
        $this->assertSame(0.0, $discountZero);
    }

    public function test_calculate_max_platform_discount_percentage_with_and_without_cap(): void
    {
        // 10% tanpa batas maksimal diskon
        $voucherNoCap = new Voucher([
            'tipe_diskon'     => 'persen',
            'nilai_diskon'    => 10.0,
            'minimal_belanja' => 50000.00,
            'maksimal_diskon' => null,
        ]);

        $discount = $this->service->calculateMaxPlatformDiscount($voucherNoCap, 200000.00);
        $this->assertSame(20000.00, $discount);

        // 20% dengan batas maksimal diskon Rp25.000
        $voucherWithCap = new Voucher([
            'tipe_diskon'     => 'persen',
            'nilai_diskon'    => 20.0,
            'minimal_belanja' => 50000.00,
            'maksimal_diskon' => 25000.00,
        ]);

        // 20% dari 200.000 = 40.000 -> harus dicap di 25.000
        $discountCapped = $this->service->calculateMaxPlatformDiscount($voucherWithCap, 200000.00);
        $this->assertSame(25000.00, $discountCapped);

        // 20% dari 100.000 = 20.000 -> di bawah cap 25.000
        $discountUnderCap = $this->service->calculateMaxPlatformDiscount($voucherWithCap, 100000.00);
        $this->assertSame(20000.00, $discountUnderCap);
    }

    public function test_calculate_max_platform_discount_bounded_by_total_net_eligible_cart(): void
    {
        // Diskon nominal Rp100.000 tetapi keranjang hanya Rp60.000
        $voucher = new Voucher([
            'tipe_diskon'     => 'nominal',
            'nilai_diskon'    => 100000.00,
            'minimal_belanja' => 50000.00,
        ]);

        $discount = $this->service->calculateMaxPlatformDiscount($voucher, 60000.00);
        $this->assertSame(60000.00, $discount);
    }

    // =========================================================================
    // 2. Tests for Division-by-Zero Guard (TASK-190 & TASK-193)
    // =========================================================================

    public function test_division_by_zero_guard_when_store_vouchers_discount_100_percent_of_rental(): void
    {
        // Skenario: 2 vendor, masing-masing subtotal sewa Rp100.000 didiskon penuh oleh voucher toko masing-masing Rp100.000
        $vendorOrders = [
            [
                'vendor_id'             => 1,
                'subtotal_sewa_asli'    => 100000.00,
                'potongan_voucher_toko' => 100000.00,
                'items'                 => [
                    [
                        'id'                  => 10,
                        'subtotal_sewa_asli'  => 100000.00,
                        'alokasi_diskon_toko' => 100000.00,
                        'is_eligible'         => true,
                    ],
                ],
            ],
            [
                'vendor_id'             => 2,
                'subtotal_sewa_asli'    => 50000.00,
                'potongan_voucher_toko' => 50000.00,
                'items'                 => [
                    [
                        'id'                  => 20,
                        'subtotal_sewa_asli'  => 50000.00,
                        'alokasi_diskon_toko' => 50000.00,
                        'is_eligible'         => true,
                    ],
                ],
            ],
        ];

        // total_net_eligible_cart = (100k - 100k) + (50k - 50k) = 0.0
        $result = $this->service->proratePlatformDiscount($vendorOrders, 30000.00);

        $this->assertSame(0.0, $result['total_net_eligible_cart']);
        $this->assertSame(0.0, $result['total_platform_discount']);
        $this->assertSame(0.0, $result['total_allocated']);
        $this->assertSame(0.0, $result['allocations'][1]);
        $this->assertSame(0.0, $result['allocations'][2]);
        $this->assertSame(0.0, $result['orders'][0]['items'][0]['alokasi_diskon_platform']);
        $this->assertSame(0.0, $result['orders'][1]['items'][0]['alokasi_diskon_platform']);
    }

    // =========================================================================
    // 3. Tests for Line-Item Whitelist Filtering (TASK-190 & TASK-193)
    // =========================================================================

    public function test_line_item_whitelist_filtering_excludes_non_whitelisted_items(): void
    {
        $uniq = uniqid();
        $vendor = User::create([
            'name'          => "Vendor {$uniq}",
            'email'         => "v_{$uniq}@example.com",
            'password'      => 'secret',
            'role'          => 'vendor',
            'vendor_status' => 'verified',
        ]);

        $kategoriA = Kategori::create(['nama' => "Kat A {$uniq}", 'slug' => "kat-a-{$uniq}"]);
        $kategoriB = Kategori::create(['nama' => "Kat B {$uniq}", 'slug' => "kat-b-{$uniq}"]);

        $barangWhitelisted = Barang::create([
            'vendor_id'         => $vendor->id,
            'kategori_id'       => $kategoriA->id,
            'nama'              => "Barang Whitelisted {$uniq}",
            'slug'              => "brg-wl-{$uniq}",
            'harga_sewa_harian' => 100000.00,
            'deposit'           => 0,
            'denda_per_hari'    => 0,
            'kondisi'           => 'baik',
            'stok_total'        => 5,
            'status'            => 'tersedia',
        ]);

        $barangNonWhitelisted = Barang::create([
            'vendor_id'         => $vendor->id,
            'kategori_id'       => $kategoriB->id,
            'nama'              => "Barang Non-WL {$uniq}",
            'slug'              => "brg-nonwl-{$uniq}",
            'harga_sewa_harian' => 100000.00,
            'deposit'           => 0,
            'denda_per_hari'    => 0,
            'kondisi'           => 'baik',
            'stok_total'        => 5,
            'status'            => 'tersedia',
        ]);

        // Platform voucher spesifik untuk kategori A
        $platformVoucher = Voucher::create([
            'kode_voucher'    => "PLATFORM{$uniq}",
            'tipe_voucher'    => 'platform',
            'funding_source'  => 'platform',
            'tipe_diskon'     => 'nominal',
            'nilai_diskon'    => 40000.00,
            'minimal_belanja' => 50000.00,
            'kuota_total'     => 10,
            'tanggal_mulai'   => '2026-11-01',
            'tanggal_selesai' => '2026-11-30',
            'is_active'       => true,
            'cakupan_produk'  => 'kategori_spesifik',
        ]);
        $platformVoucher->kategoris()->attach($kategoriA->id);

        $vendorOrders = [
            [
                'vendor_id'             => $vendor->id,
                'subtotal_sewa_asli'    => 200000.00,
                'potongan_voucher_toko' => 0.0,
                'items'                 => [
                    [
                        'id'                 => 101,
                        'product_id'         => $barangWhitelisted->id,
                        'kategori_id'        => $kategoriA->id,
                        'subtotal_sewa_asli' => 100000.00,
                    ],
                    [
                        'id'                 => 102,
                        'product_id'         => $barangNonWhitelisted->id,
                        'kategori_id'        => $kategoriB->id,
                        'subtotal_sewa_asli' => 100000.00,
                    ],
                ],
            ],
        ];

        $result = $this->service->proratePlatformDiscount($vendorOrders, 40000.00, $platformVoucher);

        // Hanya barang kategori A (Rp100.000) yang eligible, bukan Rp200.000
        $this->assertEquals(100000.00, $result['total_net_eligible_cart']);
        $this->assertEquals(40000.00, $result['total_allocated']);
        $this->assertEquals(40000.00, $result['allocations'][$vendor->id]);

        $items = $result['orders'][0]['items'];
        $this->assertEquals(40000.00, $items[0]['alokasi_diskon_platform']);
        // Barang non-whitelisted wajib Rp0
        $this->assertEquals(0.00, $items[1]['alokasi_diskon_platform']);
    }

    // =========================================================================
    // 4. Tests for Exact Penny Distribution without Floating Drift (TASK-191 & TASK-193)
    // =========================================================================

    public function test_exact_penny_distribution_across_multiple_vendors_without_floating_leakage(): void
    {
        // 3 vendor dengan subtotal identik: 100.000, 100.000, 100.000 (Total = 300.000)
        // Diskon platform Rp100.00 (atau 100.00) dibagi 3 -> 33.333333...
        // Total harus persis 100.00 dengan pembagian sen (33.34, 33.33, 33.33)
        $vendorOrders = [
            [
                'vendor_id'          => 1,
                'subtotal_sewa_asli' => 100000.00,
                'items'              => [
                    ['id' => 101, 'subtotal_sewa_asli' => 100000.00, 'is_eligible' => true],
                ],
            ],
            [
                'vendor_id'          => 2,
                'subtotal_sewa_asli' => 100000.00,
                'items'              => [
                    ['id' => 201, 'subtotal_sewa_asli' => 100000.00, 'is_eligible' => true],
                ],
            ],
            [
                'vendor_id'          => 3,
                'subtotal_sewa_asli' => 100000.00,
                'items'              => [
                    ['id' => 301, 'subtotal_sewa_asli' => 100000.00, 'is_eligible' => true],
                ],
            ],
        ];

        $targetDiscount = 100.00;
        $result = $this->service->proratePlatformDiscount($vendorOrders, $targetDiscount);

        $allocations = $result['allocations'];
        $this->assertEquals(33.34, $allocations[1]);
        $this->assertEquals(33.33, $allocations[2]);
        $this->assertEquals(33.33, $allocations[3]);

        // Verifikasi zero floating point leakage
        $this->assertEquals($targetDiscount, array_sum($allocations));
        $this->assertEquals($targetDiscount, $result['total_allocated']);

        // Verifikasi line items dalam masing-masing vendor
        $sumLineItems = 0.0;
        foreach ($result['orders'] as $order) {
            foreach ($order['items'] as $item) {
                $sumLineItems += $item['alokasi_diskon_platform'];
            }
        }
        $this->assertEquals($targetDiscount, round($sumLineItems, 2));
    }

    // =========================================================================
    // 5. Tests for Ceiling Fallback (TASK-191 & TASK-193)
    // =========================================================================

    public function test_ceiling_fallback_cascades_remainder_when_top_vendor_reaches_capacity(): void
    {
        // Vendor 1 hanya punya net sewa Rp10.00 (kapasitas maksimal Rp10.00)
        // Vendor 2 punya net sewa Rp100.00
        // Total diskon yang dialokasikan Rp50.00
        // Proporsional Vendor 1 = (10 / 110) * 50 = 4.54
        // Proporsional Vendor 2 = (100 / 110) * 50 = 45.45
        // Total base = 49.99, sisa sen = 0.01
        // Jika kapasitas Vendor 1 sengaja diset pas pada batas (misal alokasi mencapai ceiling),
        // sen sisa harus dialirkan ke vendor berikutnya yang memiliki sisa kapasitas.
        $vendorOrders = [
            [
                'vendor_id'          => 1,
                'subtotal_sewa_asli' => 10.00,
                'items'              => [['id' => 1, 'subtotal_sewa_asli' => 10.00, 'is_eligible' => true]],
            ],
            [
                'vendor_id'          => 2,
                'subtotal_sewa_asli' => 100.00,
                'items'              => [['id' => 2, 'subtotal_sewa_asli' => 100.00, 'is_eligible' => true]],
            ],
        ];

        // Tes batas ekstrem: diskon Rp110.00 (100% dari seluruh keranjang)
        $result = $this->service->proratePlatformDiscount($vendorOrders, 110.00);

        $this->assertEquals(10.00, $result['allocations'][1]);
        $this->assertEquals(100.00, $result['allocations'][2]);
        $this->assertEquals(110.00, $result['total_allocated']);
    }

    // =========================================================================
    // 6. Tests for Deterministic Tie-Breaker (TASK-191 & TASK-193)
    // =========================================================================

    public function test_deterministic_tie_breaker_between_vendors_with_identical_subtotals(): void
    {
        // Vendor 5 dan Vendor 2 memiliki nilai sewa bersih persis sama: Rp50.00 masing-masing
        // Diskon platform Rp25.01
        // Base per vendor: floor((50 / 100) * 25.01 * 100) / 100 = 12.50
        // Total allocated = 25.00, sisa remainder = 0.01
        // Karena net_eligible sama, tie-breaker: vendor_id ASC -> Vendor 2 harus mendapat 12.51, Vendor 5 mendapat 12.50
        $vendorOrders = [
            [
                'vendor_id'          => 5,
                'subtotal_sewa_asli' => 50.00,
                'items'              => [['id' => 501, 'subtotal_sewa_asli' => 50.00, 'is_eligible' => true]],
            ],
            [
                'vendor_id'          => 2,
                'subtotal_sewa_asli' => 50.00,
                'items'              => [['id' => 201, 'subtotal_sewa_asli' => 50.00, 'is_eligible' => true]],
            ],
        ];

        $result = $this->service->proratePlatformDiscount($vendorOrders, 25.01);

        $this->assertEquals(12.51, $result['allocations'][2]);
        $this->assertEquals(12.50, $result['allocations'][5]);
        $this->assertEquals(25.01, $result['total_allocated']);
    }

    public function test_deterministic_tie_breaker_for_line_items_by_item_id_asc(): void
    {
        // 1 vendor dengan 2 line items bernilai sewa identik (Rp50.00 masing-masing, item_id: 302 dan 105)
        // Diskon vendor = Rp15.01
        // Base per item: floor((50 / 100) * 15.01 * 100) / 100 = 7.50
        // Sisa 0.01 harus diberikan ke item_id 105 (karena 105 < 302 secara ASC)
        $vendorOrders = [
            [
                'vendor_id'          => 1,
                'subtotal_sewa_asli' => 100.00,
                'items'              => [
                    ['id' => 302, 'subtotal_sewa_asli' => 50.00, 'is_eligible' => true],
                    ['id' => 105, 'subtotal_sewa_asli' => 50.00, 'is_eligible' => true],
                ],
            ],
        ];

        $result = $this->service->proratePlatformDiscount($vendorOrders, 15.01);

        $items = $result['orders'][0]['items'];
        $itemAllocations = [];
        foreach ($items as $it) {
            $itemAllocations[$it['id']] = $it['alokasi_diskon_platform'];
        }

        $this->assertEquals(7.51, $itemAllocations[105]);
        $this->assertEquals(7.50, $itemAllocations[302]);
        $this->assertEquals(15.01, array_sum($itemAllocations));
    }

    // =========================================================================
    // 7. Tests for prorateCoinDiscount (TASK-192 & TASK-193)
    // =========================================================================

    public function test_prorate_coin_discount_enforces_50_percent_rental_ceiling_and_penny_reconciliation(): void
    {
        // Vendor 1: markup = 100.00, voucher = 0. Remaining sewa = 100.00. Cap 50% = 50.00.
        // Vendor 2: markup = 40.00, voucher = 0. Remaining sewa = 40.00. Cap 50% = 20.00.
        // Total remaining = 140.00. Total cap koin = 70.00.
        // Customer mencoba pakai koin Rp100.00 -> harus dipotong maksimal sampai Rp70.00 (50% dari total sewa).
        $vendorOrders = [
            [
                'vendor_id'             => 1,
                'subtotal_sewa_markup'  => 100.00,
                'potongan_voucher_toko' => 0.0,
                'potongan_voucher_platform' => 0.0,
                'items'                 => [
                    [
                        'id'                     => 11,
                        'subtotal_sewa_markup'   => 60.00,
                        'alokasi_diskon_toko'    => 0.0,
                        'alokasi_diskon_platform'=> 0.0,
                    ],
                    [
                        'id'                     => 12,
                        'subtotal_sewa_markup'   => 40.00,
                        'alokasi_diskon_toko'    => 0.0,
                        'alokasi_diskon_platform'=> 0.0,
                    ],
                ],
            ],
            [
                'vendor_id'             => 2,
                'subtotal_sewa_markup'  => 40.00,
                'potongan_voucher_toko' => 0.0,
                'potongan_voucher_platform' => 0.0,
                'items'                 => [
                    [
                        'id'                     => 21,
                        'subtotal_sewa_markup'   => 40.00,
                        'alokasi_diskon_toko'    => 0.0,
                        'alokasi_diskon_platform'=> 0.0,
                    ],
                ],
            ],
        ];

        $result = $this->service->prorateCoinDiscount($vendorOrders, 100.00);

        // Koin dialokasikan maksimal 70.00 (50% cap)
        $this->assertEquals(70.00, $result['total_coin_discount']);
        $this->assertEquals(70.00, $result['total_allocated']);
        $this->assertEquals(50.00, $result['allocations'][1]);
        $this->assertEquals(20.00, $result['allocations'][2]);

        // Verifikasi per baris item
        $itemsV1 = $result['orders'][0]['items'];
        $this->assertEquals(30.00, $itemsV1[0]['alokasi_diskon_koin']); // 50% dari 60
        $this->assertEquals(20.00, $itemsV1[1]['alokasi_diskon_koin']); // 50% dari 40
        $this->assertEquals(30.00, $itemsV1[0]['subtotal_sewa_bersih']); // 60 - 30 = 30
        $this->assertEquals(20.00, $itemsV1[1]['subtotal_sewa_bersih']); // 40 - 20 = 20

        $itemsV2 = $result['orders'][1]['items'];
        $this->assertEquals(20.00, $itemsV2[0]['alokasi_diskon_koin']); // 50% dari 40
        $this->assertEquals(20.00, $itemsV2[0]['subtotal_sewa_bersih']); // 40 - 20 = 20
    }

    public function test_prorate_coin_discount_fractional_split_with_penny_reconciliation(): void
    {
        // 3 vendor dengan remaining sewa identik Rp100.00 masing-masing. Cap masing-masing Rp50.00.
        // Diskon koin Rp70.00 -> dibagi 3 = 23.3333...
        // Pembagian harus 23.34, 23.33, 23.33 dengan sum = 70.00
        $vendorOrders = [
            [
                'vendor_id'            => 1,
                'subtotal_sewa_markup' => 100.00,
                'items'                => [['id' => 1, 'subtotal_sewa_markup' => 100.00]],
            ],
            [
                'vendor_id'            => 2,
                'subtotal_sewa_markup' => 100.00,
                'items'                => [['id' => 2, 'subtotal_sewa_markup' => 100.00]],
            ],
            [
                'vendor_id'            => 3,
                'subtotal_sewa_markup' => 100.00,
                'items'                => [['id' => 3, 'subtotal_sewa_markup' => 100.00]],
            ],
        ];

        $result = $this->service->prorateCoinDiscount($vendorOrders, 70.00);

        $this->assertEquals(70.00, $result['total_allocated']);
        $this->assertEquals(23.34, $result['allocations'][1]);
        $this->assertEquals(23.33, $result['allocations'][2]);
        $this->assertEquals(23.33, $result['allocations'][3]);
        $this->assertEquals(70.00, array_sum($result['allocations']));
    }

    public function test_prorate_coin_discount_guard_when_remaining_sewa_is_zero(): void
    {
        // Skenario sewa sisa 0 (voucher toko + voucher platform sudah menutupi 100% sewa markup)
        $vendorOrders = [
            [
                'vendor_id'                 => 1,
                'subtotal_sewa_markup'      => 105.00,
                'potongan_voucher_toko'     => 50.00,
                'potongan_voucher_platform' => 55.00,
                'items'                     => [
                    [
                        'id'                        => 1,
                        'subtotal_sewa_markup'      => 105.00,
                        'alokasi_diskon_toko'       => 50.00,
                        'alokasi_diskon_platform'   => 55.00,
                    ],
                ],
            ],
        ];

        $result = $this->service->prorateCoinDiscount($vendorOrders, 50.00);

        $this->assertSame(0.0, $result['total_remaining_sewa']);
        $this->assertSame(0.0, $result['total_coin_discount']);
        $this->assertSame(0.0, $result['total_allocated']);
        $this->assertSame(0.0, $result['allocations'][1]);
        $this->assertSame(0.0, $result['orders'][0]['items'][0]['alokasi_diskon_koin']);
        $this->assertSame(0.0, $result['orders'][0]['items'][0]['subtotal_sewa_bersih']);
    }
}
