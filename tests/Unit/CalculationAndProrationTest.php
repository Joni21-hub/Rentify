<?php

namespace Tests\Unit;

use App\Models\CustomerVoucher;
use App\Models\OrderVoucherRedemption;
use App\Models\Penyewaan;
use App\Models\User;
use App\Models\Voucher;
use App\Services\DiscountCalculationEngine;
use App\Services\MultiVendorProrationService;
use App\Services\TransactionCalculationService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CalculationAndProrationTest extends TestCase
{
    use DatabaseTransactions;

    protected MultiVendorProrationService $prorationService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->prorationService = new MultiVendorProrationService();
    }

    /**
     * Requirement 4.1 & 4.2:
     * 100% platform voucher discount does not reduce vendor_earning.
     * Platform voucher discounts are absorbed by Rentify, preserving the vendor's base rental + shipping.
     */
    public function test_100_percent_platform_voucher_discount_does_not_reduce_vendor_earning(): void
    {
        $subtotalSewaAsli = 100000.00;
        $shippingFee = 15000.00;
        $potonganVoucherToko = 0.00;
        $kodeUnik = 0;
        $coinDiscount = 0.00;
        $potonganVoucherPlatform = 100000.00; // 100% platform voucher discount
        $totalDeposit = 50000.00;

        $pricing = TransactionCalculationService::calculateOrderPricing(
            subtotalSewaAsli: $subtotalSewaAsli,
            shippingFee: $shippingFee,
            potonganVoucherToko: $potonganVoucherToko,
            kodeUnik: $kodeUnik,
            coinDiscount: $coinDiscount,
            potonganVoucherPlatform: $potonganVoucherPlatform,
            voucherFundingSource: 'platform',
            totalDeposit: $totalDeposit
        );

        // Subtotal sewa asli 100.000 + 5% markup = 105.000, fee = 5.000
        $this->assertSame(100000.00, $pricing['subtotal_sewa_asli']);
        $this->assertSame(105000.00, $pricing['subtotal_sewa_markup']);
        $this->assertSame(5000.00, $pricing['rentify_fee']);

        // Vendor earning MUST remain 100% intact: subtotal_sewa_asli (100.000) + shippingFee (15.000) = 115.000
        $this->assertSame(115000.00, $pricing['vendor_earning']);

        // Platform voucher discount is isolated and reported correctly
        $this->assertSame(0.00, $pricing['potongan_voucher_toko']);
        $this->assertSame(100000.00, $pricing['potongan_voucher_platform']);
        $this->assertSame(100000.00, $pricing['potongan_voucher']);

        // Total bill to customer = rentifyFee (5.000) + shippingFee (15.000) + totalDeposit (50.000) + kodeUnik (0) = 70.000
        $this->assertSame(70000.00, $pricing['total_biaya']);
        $this->assertSame(70000.00, $pricing['total_price']);
    }

    /**
     * Requirement 4.1 & 4.2:
     * Backward-compatibility redirect: mapping 3rd argument to potonganVoucherPlatform
     * if voucherFundingSource === 'platform' and potonganVoucherPlatform == 0.0.
     */
    public function test_backward_compatibility_mapping_for_platform_voucher_funding_source(): void
    {
        $subtotalSewaAsli = 80000.00;
        $shippingFee = 10000.00;
        $legacyVoucherDiscount = 80000.00; // Passed as 3rd parameter
        $kodeUnik = 0;
        $coinDiscount = 0.00;
        $potonganVoucherPlatform = 0.00;
        $voucherFundingSource = 'platform';

        $pricing = TransactionCalculationService::calculateOrderPricing(
            $subtotalSewaAsli,
            $shippingFee,
            $legacyVoucherDiscount,
            $kodeUnik,
            $coinDiscount,
            $potonganVoucherPlatform,
            $voucherFundingSource
        );

        // 3rd arg must be mapped to potongan_voucher_platform, and potongan_voucher_toko reset to 0
        $this->assertSame(0.00, $pricing['potongan_voucher_toko']);
        $this->assertSame(80000.00, $pricing['potongan_voucher_platform']);
        $this->assertSame(80000.00, $pricing['potongan_voucher']);

        // Vendor earning must NOT be diminished: 80.000 + 10.000 = 90.000
        $this->assertSame(90000.00, $pricing['vendor_earning']);
    }

    /**
     * Requirement 4.3:
     * BOGO Sewa 2 Bayar 1 returns 0.00 discount when cart quantity is 1,
     * and 50% discount when cart quantity >= 2.
     */
    public function test_bogo_sewa_2_bayar_1_guards_quantity_and_applies_fifty_percent(): void
    {
        $bogoVoucher = [
            'kode_voucher'    => 'BOGO2B1',
            'tipe_diskon'     => 'persen',
            'nilai_diskon'    => 50.0,
            'min_quantity'    => 2,
            'minimal_belanja' => 0.0,
            'maksimal_diskon' => 0.0,
        ];

        // Quantity 1 < min_quantity 2: must return 0.00 discount
        $discountQty1 = DiscountCalculationEngine::calculateRentalDiscount($bogoVoucher, 100000.00, 1);
        $this->assertSame(0.00, $discountQty1);

        // Quantity 2 >= min_quantity 2: must return 50% discount (50.000)
        $discountQty2 = DiscountCalculationEngine::calculateRentalDiscount($bogoVoucher, 100000.00, 2);
        $this->assertSame(50000.00, $discountQty2);

        // Quantity 3 >= min_quantity 2: must return 50% discount
        $discountQty3 = DiscountCalculationEngine::calculateRentalDiscount($bogoVoucher, 100000.00, 3);
        $this->assertSame(50000.00, $discountQty3);

        // Also test with explicit tipe_diskon = 'bogo' and Eloquent model
        $eloquentBogo2 = new Voucher([
            'kode_voucher'    => 'BOGO-SEWA2-BAYAR1',
            'tipe_diskon'     => 'bogo',
            'min_quantity'    => 2,
            'minimal_belanja' => 0.0,
            'maksimal_diskon' => 0.0,
        ]);

        $this->assertSame(0.00, DiscountCalculationEngine::calculateRentalDiscount($eloquentBogo2, 60000.00, 1));
        $this->assertSame(30000.00, DiscountCalculationEngine::calculateRentalDiscount($eloquentBogo2, 60000.00, 2));
    }

    /**
     * Requirement 4.3:
     * BOGO Sewa 3 Bayar 2 evaluates exact rational 1/3 discount using
     * ceil(($eligibleSubtotal / 3.0) * 100) / 100 without Rp 10 rounding deviations.
     */
    public function test_bogo_sewa_3_bayar_2_evaluates_exact_rational_one_third_without_rounding_deviations(): void
    {
        $bogo3Voucher = [
            'kode_voucher'    => 'BOGO3B2',
            'tipe_diskon'     => 'bogo_3_bayar_2',
            'min_quantity'    => 3,
            'minimal_belanja' => 0.0,
            'maksimal_diskon' => 0.0,
        ];

        // Subtotal = 100,000.00:
        // Rational 1/3: ceil((100000 / 3.0) * 100) / 100 = ceil(3333333.333...) / 100 = 33333.34
        // (A naive 33.33% would yield 33,330.00, which has an unacceptable Rp 3.34 / Rp 10 deviation)
        $discount100k = DiscountCalculationEngine::calculateRentalDiscount($bogo3Voucher, 100000.00, 3);
        $this->assertSame(33333.34, $discount100k);

        // Subtotal = 50,000.00:
        // Rational 1/3: ceil((50000 / 3.0) * 100) / 100 = ceil(1666666.666...) / 100 = 16666.67
        $discount50k = DiscountCalculationEngine::calculateRentalDiscount($bogo3Voucher, 50000.00, 3);
        $this->assertSame(16666.67, $discount50k);

        // Subtotal = 70,000.00:
        // Rational 1/3: ceil((70000 / 3.0) * 100) / 100 = ceil(2333333.333...) / 100 = 23333.34
        $discount70k = DiscountCalculationEngine::calculateRentalDiscount($bogo3Voucher, 70000.00, 3);
        $this->assertSame(23333.34, $discount70k);

        // Guard: Quantity 2 (< min_quantity 3) must return 0.00
        $discountQty2 = DiscountCalculationEngine::calculateRentalDiscount($bogo3Voucher, 100000.00, 2);
        $this->assertSame(0.00, $discountQty2);
    }

    /**
     * Requirement 4.3:
     * BOGO Sewa 3 Bayar 2 respects maksimal_diskon cap when rational 1/3 exceeds cap.
     */
    public function test_bogo_sewa_3_bayar_2_respects_max_discount_cap(): void
    {
        $bogo3VoucherCapped = [
            'kode_voucher'    => 'BOGO3B2CAP',
            'tipe_diskon'     => 'bogo_3_bayar_2',
            'min_quantity'    => 3,
            'minimal_belanja' => 0.0,
            'maksimal_diskon' => 25000.00, // Cap at 25,000
        ];

        // 1/3 of 100,000 is 33,333.34, which exceeds 25,000.00 -> must be capped at 25000.00
        $discount = DiscountCalculationEngine::calculateRentalDiscount($bogo3VoucherCapped, 100000.00, 3);
        $this->assertSame(25000.00, $discount);
    }

    /**
     * Requirement 4.4:
     * Multi-vendor discount proration across carts with uneven subtotals,
     * verifying zero cent discrepancy (sum(allocations) === totalPlatformDiscount).
     */
    public function test_multi_vendor_discount_proration_with_uneven_subtotals_and_zero_cent_discrepancy(): void
    {
        $vendorOrders = [
            [
                'vendor_id'             => 101,
                'subtotal_sewa_asli'    => 100000.00,
                'potongan_voucher_toko' => 0.0,
                'items'                 => [
                    ['item_id' => 1, 'subtotal_sewa_asli' => 100000.00, 'alokasi_toko' => 0.0],
                ],
            ],
            [
                'vendor_id'             => 102,
                'subtotal_sewa_asli'    => 75000.00,
                'potongan_voucher_toko' => 0.0,
                'items'                 => [
                    ['item_id' => 2, 'subtotal_sewa_asli' => 75000.00, 'alokasi_toko' => 0.0],
                ],
            ],
            [
                'vendor_id'             => 103,
                'subtotal_sewa_asli'    => 33333.33,
                'potongan_voucher_toko' => 0.0,
                'items'                 => [
                    ['item_id' => 3, 'subtotal_sewa_asli' => 33333.33, 'alokasi_toko' => 0.0],
                ],
            ],
        ];

        $totalPlatformDiscount = 50000.00;

        $result = $this->prorationService->proratePlatformDiscount($vendorOrders, $totalPlatformDiscount);

        $allocations = $result['allocations'];

        // 1. Verify zero cent discrepancy: exact equality
        $this->assertSame(50000.00, round(array_sum($allocations), 2));
        $this->assertSame(50000.00, $result['total_allocated']);

        // 2. Verify deterministic 0.01 cent cascade tie-breaker sorting by net_eligible DESC, then vendor_id ASC
        $this->assertGreaterThan(0.0, $allocations[101]);
        $this->assertGreaterThan(0.0, $allocations[102]);
        $this->assertGreaterThan(0.0, $allocations[103]);

        // Total net eligible cart = 100000 + 75000 + 33333.33 = 208333.33
        // Proportions:
        // v101: 100000 / 208333.33 * 50000 = 24000.00
        // v102: 75000 / 208333.33 * 50000 = 18000.00
        // v103: 33333.33 / 208333.33 * 50000 = 7999.999... -> 8000.00
        $this->assertEqualsWithDelta(24000.00, $allocations[101], 0.05);
        $this->assertEqualsWithDelta(18000.00, $allocations[102], 0.05);
        $this->assertEqualsWithDelta(8000.00, $allocations[103], 0.05);
    }

    /**
     * Requirement 4.4:
     * Multi-vendor proration enforces the Upper Boundary Guard
     * (alokasi <= net_eligible_subtotal) so no sub-order receives allocation exceeding its net eligible subtotal.
     */
    public function test_multi_vendor_proration_enforces_upper_boundary_guard_per_sub_order(): void
    {
        $vendorOrders = [
            [
                'vendor_id'             => 201,
                'subtotal_sewa_asli'    => 100.00, // Very small subtotal
                'potongan_voucher_toko' => 0.0,
                'items'                 => [
                    ['item_id' => 11, 'subtotal_sewa_asli' => 100.00, 'alokasi_toko' => 0.0],
                ],
            ],
            [
                'vendor_id'             => 202,
                'subtotal_sewa_asli'    => 50000.00,
                'potongan_voucher_toko' => 0.0,
                'items'                 => [
                    ['item_id' => 12, 'subtotal_sewa_asli' => 50000.00, 'alokasi_toko' => 0.0],
                ],
            ],
        ];

        // Total discount requested is 30,000.00
        $totalPlatformDiscount = 30000.00;

        $result = $this->prorationService->proratePlatformDiscount($vendorOrders, $totalPlatformDiscount);

        $allocations = $result['allocations'];

        // Vendor 201 net eligible subtotal is only 100.00: allocation MUST NOT exceed 100.00
        $this->assertLessThanOrEqual(100.00, $allocations[201]);
        $this->assertLessThanOrEqual(50000.00, $allocations[202]);

        // Zero cent discrepancy
        $this->assertSame(30000.00, round(array_sum($allocations), 2));
    }

    /**
     * Requirement 5.2:
     * Waterfall non-overflow in DiscountCalculationEngine:
     * Platform commission (5%), shipping fee, and security deposits remain untouched
     * so that total_biaya >= rentifyFee + shippingFee + totalDeposit + kodeUnik.
     */
    public function test_waterfall_non_overflow_safeguards_platform_fee_shipping_and_deposit_in_engine(): void
    {
        $params = [
            'subtotal_sewa_asli'        => 50000.00,
            'potongan_voucher_toko'     => 100000.00, // Trying to overflow store voucher
            'potongan_voucher_platform' => 50000.00,  // Trying to overflow platform voucher
            'potongan_koin'             => 50000.00,  // Trying to overflow coin discount
            'shipping_fee'              => 20000.00,
            'total_deposit'             => 100000.00,
            'kode_unik'                 => 123,
            'payment_method'            => 'ONLINE',
        ];

        $breakdown = DiscountCalculationEngine::evaluateWaterfall($params);

        // Rentify fee: 5% of 50,000 = 2,500
        $this->assertSame(2500.00, $breakdown['rentify_fee']);
        $this->assertSame(20000.00, $breakdown['shipping_fee']);
        $this->assertSame(100000.00, $breakdown['total_deposit']);
        $this->assertSame(123, $breakdown['kode_unik']);

        // Store voucher capped at subtotal_sewa_asli (50,000)
        $this->assertSame(50000.00, $breakdown['potongan_voucher_toko']);
        $this->assertSame(0.00, $breakdown['potongan_voucher_platform']);
        $this->assertSame(0.00, $breakdown['potongan_koin']);

        // Invariant: total_biaya >= rentifyFee + shippingFee + totalDeposit + kodeUnik
        $expectedMinimum = 2500.00 + 20000.00 + 100000.00 + 123; // 122,623.00
        $this->assertGreaterThanOrEqual($expectedMinimum, $breakdown['total_biaya']);
        $this->assertSame(122623.00, $breakdown['total_biaya']);
    }

    /**
     * Requirement 5.2:
     * Waterfall non-overflow in TransactionCalculationService::calculateOrderPricing.
     */
    public function test_waterfall_non_overflow_safeguards_platform_fee_shipping_and_deposit_in_pricing_service(): void
    {
        $pricing = TransactionCalculationService::calculateOrderPricing(
            subtotalSewaAsli: 50000.00,
            shippingFee: 20000.00,
            potonganVoucherToko: 50000.00,
            kodeUnik: 123,
            coinDiscount: 50000.00,
            potonganVoucherPlatform: 50000.00,
            voucherFundingSource: null,
            totalDeposit: 100000.00
        );

        $this->assertSame(2500.00, $pricing['rentify_fee']);
        $this->assertSame(20000.00, $pricing['shipping_fee']);
        $this->assertSame(100000.00, $pricing['total_deposit']);
        $this->assertSame(123, $pricing['kode_unik']);

        $expectedMinimum = 2500.00 + 20000.00 + 100000.00 + 123; // 122,623.00
        $this->assertGreaterThanOrEqual($expectedMinimum, $pricing['total_biaya']);
        $this->assertSame(122623.00, $pricing['total_biaya']);
        $this->assertSame(122623.00, $pricing['total_price']);
    }

    /**
     * Requirement 4.4:
     * Persist allocation records into order_voucher_redemptions while consuming
     * exactly 1 platform quota per payment_group_id across multiple sub-orders.
     */
    public function test_multi_vendor_platform_voucher_redemptions_persistence_and_single_quota_consumption(): void
    {
        $uniq = uniqid();
        $user = User::create([
            'name'     => "Test Buyer {$uniq}",
            'email'    => "buyer_{$uniq}@example.com",
            'password' => Hash::make('password'),
            'role'     => 'customer',
        ]);

        $vendor1 = User::create([
            'name'     => "Vendor 1 {$uniq}",
            'email'    => "v1_{$uniq}@example.com",
            'password' => Hash::make('password'),
            'role'     => 'vendor',
        ]);

        $vendor2 = User::create([
            'name'     => "Vendor 2 {$uniq}",
            'email'    => "v2_{$uniq}@example.com",
            'password' => Hash::make('password'),
            'role'     => 'vendor',
        ]);

        $platformVoucher = Voucher::create([
            'kode_voucher'               => "PLATVOUCH{$uniq}",
            'tipe_voucher'               => 'platform',
            'funding_source'             => 'platform',
            'tipe_diskon'                => 'nominal',
            'nilai_diskon'               => 20000.00,
            'minimal_belanja'            => 50000.00,
            'kuota_total'                => 10,
            'kuota_terpakai'             => 0,
            'anggaran_maksimal_platform' => 500000.00,
            'anggaran_terpakai'          => 0.00,
            'tanggal_mulai'              => Carbon::now()->subDay()->toDateString(),
            'tanggal_selesai'            => Carbon::now()->addDays(5)->toDateString(),
            'is_active'                  => true,
            'suspended_by_admin'         => false,
            'auto_exhausted'             => false,
            'min_quantity'               => 1,
        ]);

        $customerVoucher = CustomerVoucher::create([
            'user_id'    => $user->id,
            'voucher_id' => $platformVoucher->id,
            'status'     => 'aktif',
            'claimed_at' => Carbon::now(),
        ]);

        $paymentGroupId = 'PGP' . strtoupper(uniqid());

        $order1 = Penyewaan::create([
            'user_id'                   => $user->id,
            'vendor_id'                 => $vendor1->id,
            'customer_name'             => $user->name,
            'customer_whatsapp'         => '081234567890',
            'kode_booking'              => 'BK1' . strtoupper(uniqid()),
            'payment_group_id'          => $paymentGroupId,
            'payment_method'            => 'ONLINE',
            'metode_pengambilan'        => 'ambil',
            'tanggal_mulai'             => Carbon::now(),
            'tanggal_selesai'           => Carbon::now()->addDays(2),
            'total_biaya'               => 60000.00,
            'total_deposit'             => 0.00,
            'voucher_platform_id'       => $platformVoucher->id,
            'potongan_voucher_platform' => 12000.00,
            'status'                    => 'Menunggu Pembayaran',
        ]);

        $order2 = Penyewaan::create([
            'user_id'                   => $user->id,
            'vendor_id'                 => $vendor2->id,
            'customer_name'             => $user->name,
            'customer_whatsapp'         => '081234567890',
            'kode_booking'              => 'BK2' . strtoupper(uniqid()),
            'payment_group_id'          => $paymentGroupId,
            'payment_method'            => 'ONLINE',
            'metode_pengambilan'        => 'ambil',
            'tanggal_mulai'             => Carbon::now(),
            'tanggal_selesai'           => Carbon::now()->addDays(2),
            'total_biaya'               => 40000.00,
            'total_deposit'             => 0.00,
            'voucher_platform_id'       => $platformVoucher->id,
            'potongan_voucher_platform' => 8000.00,
            'status'                    => 'Menunggu Pembayaran',
        ]);

        $proratedOrders = [
            [
                'id'                         => $order1->id,
                'order_id'                   => $order1->id,
                'vendor_id'                  => $vendor1->id,
                'potongan_voucher_platform'  => 12000.00,
            ],
            [
                'id'                         => $order2->id,
                'order_id'                   => $order2->id,
                'vendor_id'                  => $vendor2->id,
                'potongan_voucher_platform'  => 8000.00,
            ],
        ];

        $redemptions = $this->prorationService->persistPlatformDiscountRedemptions(
            $paymentGroupId,
            $platformVoucher,
            $proratedOrders,
            $customerVoucher->id
        );

        // 1. Must create 2 redemption records (one per sub-order)
        $this->assertCount(2, $redemptions);

        $this->assertDatabaseHas('order_voucher_redemptions', [
            'payment_group_id' => $paymentGroupId,
            'order_id'         => $order1->id,
            'voucher_id'       => $platformVoucher->id,
            'alokasi_diskon'   => 12000.00,
            'tipe_voucher'     => 'platform',
            'status'           => 'aktif',
        ]);

        $this->assertDatabaseHas('order_voucher_redemptions', [
            'payment_group_id' => $paymentGroupId,
            'order_id'         => $order2->id,
            'voucher_id'       => $platformVoucher->id,
            'alokasi_diskon'   => 8000.00,
            'tipe_voucher'     => 'platform',
            'status'           => 'aktif',
        ]);

        // 2. Must consume EXACTLY 1 platform voucher quota for the whole payment group
        $platformVoucher->refresh();
        $this->assertSame(1, $platformVoucher->kuota_terpakai, 'Platform voucher quota must increment by exactly 1 per payment_group_id');

        // 3. Customer voucher marked as 'terpakai'
        $customerVoucher->refresh();
        $this->assertSame('terpakai', $customerVoucher->status);
        $this->assertNotNull($customerVoucher->used_at);
    }
}
