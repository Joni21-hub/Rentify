<?php

namespace Tests\Feature;

use App\Models\Barang;
use App\Models\Kategori;
use App\Models\Penyewaan;
use App\Models\User;
use App\Services\TransactionCalculationService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TransactionCalculationsAuditTest extends TestCase
{
    use DatabaseTransactions;

    protected User $customer;
    protected User $vendor;
    protected Barang $barang;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customer = User::create([
            'name' => 'Customer Audit',
            'email' => 'cust_audit_' . uniqid() . '@example.com',
            'password' => bcrypt('password123'),
            'role' => 'customer',
            'is_active' => 1,
        ]);
        $this->customer->markEmailAsVerified();

        $this->vendor = User::create([
            'name' => 'Vendor Audit',
            'email' => 'vendor_audit_' . uniqid() . '@example.com',
            'password' => bcrypt('password123'),
            'role' => 'vendor',
            'vendor_name' => 'Toko Audit Sejahtera',
            'is_active' => 1,
        ]);
        $this->vendor->markEmailAsVerified();

        $kategori = Kategori::create([
            'nama' => 'Peralatan Audit',
            'slug' => 'peralatan-audit-' . uniqid(),
            'is_active' => 1,
        ]);

        $this->barang = Barang::create([
            'vendor_id' => $this->vendor->id,
            'kategori_id' => $kategori->id,
            'nama' => 'Kamera Sony A7III',
            'slug' => 'kamera-sony-a7iii-' . uniqid(),
            'deskripsi' => 'Kamera profesional full frame',
            'harga_sewa_harian' => 100000,
            'deposit' => 50000,
            'denda_per_hari' => 25000,
            'kondisi' => 'Sangat Baik',
            'stok_total' => 5,
            'status' => 'tersedia',
            'is_approved' => 1,
            'cover_photo' => 'barang/sony.jpg',
            'is_delivery_supported' => 1,
            'alamat' => 'Jl. Diponegoro No 1',
            'latitude' => '-6.2000',
            'longitude' => '106.8166',
        ]);
    }

    /**
     * Uji 1: Perhitungan sewa dasar tanpa diskon & tanpa ongkir
     */
    public function test_pricing_calculation_standard_order(): void
    {
        $subtotalAsli = 100000.0;
        $pricing = TransactionCalculationService::calculateOrderPricing($subtotalAsli);

        $this->assertEquals(100000.0, $pricing['subtotal_sewa_asli']);
        $this->assertEquals(105000.0, $pricing['subtotal_sewa_markup']);
        $this->assertEquals(5000.0, $pricing['rentify_fee']);
        $this->assertEquals(100000.0, $pricing['vendor_earning']);
        $this->assertEquals(105000.0, $pricing['total_biaya']);

        // Persamaan neraca seimbang
        $this->assertEquals($pricing['total_biaya'], $pricing['vendor_earning'] + $pricing['rentify_fee']);
    }

    /**
     * Uji 2: Perhitungan sewa dengan ongkos kirim (ongkir 100% milik vendor)
     */
    public function test_pricing_calculation_with_shipping_fee(): void
    {
        $subtotalAsli = 200000.0;
        $shippingFee  = 25000.0;
        $pricing = TransactionCalculationService::calculateOrderPricing($subtotalAsli, $shippingFee);

        $this->assertEquals(200000.0, $pricing['subtotal_sewa_asli']);
        $this->assertEquals(210000.0, $pricing['subtotal_sewa_markup']);
        $this->assertEquals(10000.0, $pricing['rentify_fee']);
        // Hak vendor harus sewa asli + ongkir utuh
        $this->assertEquals(225000.0, $pricing['vendor_earning']);
        // Tagihan customer = sewa markup + ongkir
        $this->assertEquals(235000.0, $pricing['total_biaya']);

        // Persamaan neraca seimbang
        $this->assertEquals($pricing['total_biaya'], $pricing['vendor_earning'] + $pricing['rentify_fee']);
    }

    /**
     * Uji 3: Perhitungan sewa dengan voucher toko vendor
     */
    public function test_pricing_calculation_with_voucher_discount(): void
    {
        $subtotalAsli    = 100000.0;
        $voucherDiscount = 15000.0;
        $pricing = TransactionCalculationService::calculateOrderPricing($subtotalAsli, 0, $voucherDiscount);

        // Komisi platform 5% dari harga sewa murni
        $this->assertEquals(5000.0, $pricing['rentify_fee']);
        // Hak vendor dipotong voucher toko
        $this->assertEquals(85000.0, $pricing['vendor_earning']);
        // Tagihan customer = sewa markup (105.000) - voucher (15.000) = 90.000
        $this->assertEquals(90000.0, $pricing['total_biaya']);

        // Persamaan neraca seimbang
        $this->assertEquals($pricing['total_biaya'], $pricing['vendor_earning'] + $pricing['rentify_fee']);
    }

    /**
     * Uji 4: Perhitungan sewa dengan kode unik verifikasi
     */
    public function test_pricing_calculation_with_kode_unik(): void
    {
        $subtotalAsli = 100000.0;
        $shippingFee  = 10000.0;
        $voucher      = 5000.0;
        $kodeUnik     = 456;

        $pricing = TransactionCalculationService::calculateOrderPricing($subtotalAsli, $shippingFee, $voucher, $kodeUnik);

        $this->assertEquals(5000.0, $pricing['rentify_fee']);
        $this->assertEquals(105000.0, $pricing['vendor_earning']); // (100k - 5k) + 10k ongkir
        $this->assertEquals(110456.0, $pricing['total_biaya']); // (105k - 5k) + 10k + 456

        // Total customer = vendor_earning + rentify_fee + kode_unik
        $this->assertEquals($pricing['total_biaya'], $pricing['vendor_earning'] + $pricing['rentify_fee'] + $kodeUnik);
    }

    /**
     * Uji 5: Penyelesaian pesanan Online mengkredit saldo vendor secara presisi
     */
    public function test_online_order_completion_credits_vendor_saldo_accurately(): void
    {
        $pricing = TransactionCalculationService::calculateOrderPricing(100000, 20000, 0, 0);

        $orderId = DB::table('orders')->insertGetId([
            'user_id' => $this->customer->id,
            'vendor_id' => $this->vendor->id,
            'kode_booking' => 'RNT-TEST-ONLINE-' . uniqid(),
            'customer_name' => $this->customer->name,
            'customer_whatsapp' => '081234567890',
            'metode_pengambilan' => 'antar',
            'shipping_fee' => 20000,
            'total_biaya' => $pricing['total_biaya'],
            'total_price' => $pricing['total_biaya'],
            'payment_method' => 'QRIS',
            'status' => 'Sedang Disewa',
            'tanggal_mulai' => now(),
            'tanggal_selesai' => now()->addDay(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Pastikan saldo awal 0
        DB::table('saldos')->updateOrInsert(
            ['vendor_id' => $this->vendor->id],
            ['saldo_aktif' => 0, 'saldo_ditahan' => 0]
        );

        $result = TransactionCalculationService::settleOrderCompletion($orderId);

        $this->assertEquals('Selesai', $result['status']);
        $this->assertEquals(120000.0, $result['vendor_earning']);
        $this->assertEquals(5000.0, $result['rentify_fee']);
        $this->assertEquals(120000.0, $result['saldo_sesudah']);

        // Verifikasi di database
        $orderInDb = DB::table('orders')->where('id', $orderId)->first();
        $this->assertEquals('Selesai', $orderInDb->status);
        $this->assertEquals(120000.0, (float) $orderInDb->vendor_earning);
        $this->assertEquals(5000.0, (float) $orderInDb->rentify_fee);

        $saldoInDb = DB::table('saldos')->where('vendor_id', $this->vendor->id)->first();
        $this->assertEquals(120000.0, (float) $saldoInDb->saldo_aktif);
    }

    /**
     * Uji 6: Penyelesaian pesanan COD mendebit fee 5% dari dompet saldo vendor
     */
    public function test_cod_order_completion_debits_platform_fee_from_vendor_saldo(): void
    {
        $pricing = TransactionCalculationService::calculateOrderPricing(100000, 0, 0, 0);

        $orderId = DB::table('orders')->insertGetId([
            'user_id' => $this->customer->id,
            'vendor_id' => $this->vendor->id,
            'kode_booking' => 'RNT-TEST-COD-' . uniqid(),
            'customer_name' => $this->customer->name,
            'customer_whatsapp' => '081234567890',
            'metode_pengambilan' => 'ambil',
            'shipping_fee' => 0,
            'total_biaya' => $pricing['total_biaya'],
            'total_price' => $pricing['total_biaya'],
            'payment_method' => 'COD',
            'status' => 'Sedang Disewa',
            'tanggal_mulai' => now(),
            'tanggal_selesai' => now()->addDay(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Berikan saldo awal vendor 50.000
        DB::table('saldos')->updateOrInsert(
            ['vendor_id' => $this->vendor->id],
            ['saldo_aktif' => 50000, 'saldo_ditahan' => 0]
        );

        $result = TransactionCalculationService::settleOrderCompletion($orderId);

        $this->assertEquals('Selesai', $result['status']);
        $this->assertEquals(5000.0, $result['rentify_fee']);
        // Saldo vendor harus berkurang 5.000 menjadi 45.000
        $this->assertEquals(45000.0, $result['saldo_sesudah']);

        $saldoInDb = DB::table('saldos')->where('vendor_id', $this->vendor->id)->first();
        $this->assertEquals(45000.0, (float) $saldoInDb->saldo_aktif);
    }

    /**
     * Uji 7: Tombol selesaikan oleh customer memicu sinkronisasi saldo vendor
     */
    public function test_customer_settle_order_endpoint_updates_vendor_saldo(): void
    {
        $pricing = TransactionCalculationService::calculateOrderPricing(100000, 10000, 0, 0);

        $orderId = DB::table('orders')->insertGetId([
            'user_id' => $this->customer->id,
            'vendor_id' => $this->vendor->id,
            'kode_booking' => 'RNT-CUST-COMPLETE-' . uniqid(),
            'customer_name' => $this->customer->name,
            'customer_whatsapp' => '081234567890',
            'metode_pengambilan' => 'antar',
            'shipping_fee' => 10000,
            'total_biaya' => $pricing['total_biaya'],
            'total_price' => $pricing['total_biaya'],
            'payment_method' => 'QRIS',
            'status' => 'Sedang Disewa',
            'tanggal_mulai' => now(),
            'tanggal_selesai' => now()->addDay(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('saldos')->updateOrInsert(
            ['vendor_id' => $this->vendor->id],
            ['saldo_aktif' => 0, 'saldo_ditahan' => 0]
        );

        $response = $this->actingAs($this->customer)
            ->post(route('customer.pesanan.selesai', $orderId));

        $response->assertStatus(302);

        $orderInDb = DB::table('orders')->where('id', $orderId)->first();
        // Sesuai arsitektur Shopee-style Rentify, penyelesaian pesanan oleh customer mengalirkan status ke 'Menunggu Ulasan'
        $this->assertEquals('Menunggu Ulasan', $orderInDb->status);

        $saldoInDb = DB::table('saldos')->where('vendor_id', $this->vendor->id)->first();
        // 100.000 sewa + 10.000 ongkir = 110.000
        $this->assertEquals(110000.0, (float) $saldoInDb->saldo_aktif);
    }

    /**
     * Uji 8: Idempotensi penyelesaian pesanan mencegah double credit saldo
     */
    public function test_settlement_idempotency_prevents_double_crediting(): void
    {
        $pricing = TransactionCalculationService::calculateOrderPricing(100000);

        $orderId = DB::table('orders')->insertGetId([
            'user_id' => $this->customer->id,
            'vendor_id' => $this->vendor->id,
            'kode_booking' => 'RNT-IDEMPOTENT-' . uniqid(),
            'customer_name' => $this->customer->name,
            'customer_whatsapp' => '081234567890',
            'metode_pengambilan' => 'ambil',
            'shipping_fee' => 0,
            'total_biaya' => $pricing['total_biaya'],
            'total_price' => $pricing['total_biaya'],
            'payment_method' => 'QRIS',
            'status' => 'Sedang Disewa',
            'tanggal_mulai' => now(),
            'tanggal_selesai' => now()->addDay(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('saldos')->updateOrInsert(
            ['vendor_id' => $this->vendor->id],
            ['saldo_aktif' => 0, 'saldo_ditahan' => 0]
        );

        // Eksekusi pertama
        $res1 = TransactionCalculationService::settleOrderCompletion($orderId);
        $this->assertEquals(100000.0, $res1['saldo_sesudah']);
        $this->assertFalse($res1['was_already_done']);

        // Eksekusi kedua (harus idempotent, tidak menambah saldo lagi)
        $res2 = TransactionCalculationService::settleOrderCompletion($orderId);
        $this->assertEquals(100000.0, $res2['saldo_sesudah']);
        $this->assertTrue($res2['was_already_done']);

        $saldoInDb = DB::table('saldos')->where('vendor_id', $this->vendor->id)->first();
        $this->assertEquals(100000.0, (float) $saldoInDb->saldo_aktif);
    }

    /**
     * Uji 9: Command Artisan rentify:audit-transactions berjalan mulus
     */
    public function test_artisan_audit_command_runs_successfully(): void
    {
        $exitCode = \Illuminate\Support\Facades\Artisan::call('rentify:audit-transactions');
        $this->assertEquals(0, $exitCode);
    }
}
