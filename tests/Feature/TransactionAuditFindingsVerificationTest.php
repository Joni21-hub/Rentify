<?php

namespace Tests\Feature;

use App\Models\Barang;
use App\Models\Kategori;
use App\Models\User;
use App\Models\Voucher;
use App\Services\TransactionCalculationService;
use App\Services\DiscountCalculationEngine;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TransactionAuditFindingsVerificationTest extends TestCase
{
    use DatabaseTransactions;

    protected User $customer;
    protected User $vendor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customer = User::create([
            'name' => 'Test Customer',
            'email' => 'cust_' . uniqid() . '@rentify.test',
            'password' => bcrypt('password123'),
            'role' => 'customer',
            'is_active' => 1,
        ]);
        $this->customer->markEmailAsVerified();

        $this->vendor = User::create([
            'name' => 'Test Vendor',
            'email' => 'vendor_' . uniqid() . '@rentify.test',
            'password' => bcrypt('password123'),
            'role' => 'vendor',
            'vendor_name' => 'Toko Audit',
            'is_active' => 1,
        ]);
        $this->vendor->markEmailAsVerified();
    }

    /**
     * Bukti Uji 1: Rumus Finansial Dasar (10.000 vendor -> 10.500 cust -> 500 fee)
     */
    public function test_financial_base_formula(): void
    {
        $pricing = TransactionCalculationService::calculateOrderPricing(10000.0);
        $this->assertEquals(10000.0, $pricing['subtotal_sewa_asli']);
        $this->assertEquals(10500.0, $pricing['subtotal_sewa_markup']);
        $this->assertEquals(500.0, $pricing['rentify_fee']);
        $this->assertEquals(10000.0, $pricing['vendor_earning']);
        $this->assertEquals(10500.0, $pricing['total_biaya']);
    }

    /**
     * Bukti Uji 2: Bug Voucher Platform 100% (Customer tertagih fee Rp 600 dan Hak Vendor terpotong jika tidak dispesifikasikan)
     */
    public function test_audit_platform_voucher_100_percent_behavior(): void
    {
        $subtotalAsli = 12000.0;
        $potonganVoucher = 12000.0;

        // Jika dipanggil via Checkout legacy (parameter ke-3 tanpa voucherFundingSource = 'platform')
        $legacyPricing = TransactionCalculationService::calculateOrderPricing(
            $subtotalAsli,
            0.0,
            $potonganVoucher
        );

        // BUKTI: Hak vendor terpotong menjadi 0 padahal voucher dari platform!
        $this->assertEquals(0.0, $legacyPricing['vendor_earning']);

        // Jika dipanggil dengan voucherFundingSource = 'platform'
        $platformPricing = TransactionCalculationService::calculateOrderPricing(
            subtotalSewaAsli: $subtotalAsli,
            shippingFee: 0.0,
            voucherDiscount: 0.0,
            kodeUnik: 0,
            coinDiscount: 0.0,
            potonganVoucherPlatform: $potonganVoucher,
            voucherFundingSource: 'platform'
        );

        // Hak vendor utuh 12.000
        $this->assertEquals(12000.0, $platformPricing['vendor_earning']);
        
        // BUKTI: Tagihan customer saat ini masih Rp 600 karena platform fee ditagihkan ke customer!
        $this->assertEquals(600.0, $platformPricing['total_biaya']);
        $this->assertNotEquals(0.0, $platformPricing['total_biaya']);
    }

    /**
     * Bukti Uji 3: Revert Settlement Menolak Status 'Menunggu Ulasan'
     */
    public function test_revert_settlement_rejects_menunggu_ulasan(): void
    {
        $orderId = DB::table('orders')->insertGetId([
            'user_id' => $this->customer->id,
            'vendor_id' => $this->vendor->id,
            'kode_booking' => 'RNT-AUDIT-' . uniqid(),
            'customer_name' => $this->customer->name,
            'customer_whatsapp' => '081234567890',
            'metode_pengambilan' => 'ambil',
            'shipping_fee' => 0,
            'total_biaya' => 10500,
            'total_price' => 10500,
            'vendor_earning' => 10000,
            'rentify_fee' => 500,
            'payment_method' => 'ONLINE',
            'status' => 'Menunggu Ulasan',
            'tanggal_mulai' => now(),
            'tanggal_selesai' => now()->addDay(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $res = TransactionCalculationService::revertOrderSettlement($orderId, 'Testing');
        $this->assertFalse($res['reverted']);
        $this->assertStringContainsString('Pesanan belum dalam status Selesai', $res['message']);
    }
}
