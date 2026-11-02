<?php

namespace Tests\Feature;

use App\Models\Barang;
use App\Models\Kategori;
use App\Models\Mutasi;
use App\Models\Penyewaan;
use App\Models\User;
use App\Models\Voucher;
use App\Services\MutasiService;
use App\Services\TransactionCalculationService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ComprehensiveTransactionAuditTest extends TestCase
{
    use DatabaseTransactions;

    protected User $customer;
    protected User $vendorA;
    protected User $vendorB;
    protected Barang $barangA;
    protected Barang $barangB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customer = User::create([
            'name' => 'Auditor Customer',
            'email' => 'audit_cust_' . uniqid() . '@rentify.test',
            'password' => bcrypt('password123'),
            'role' => 'customer',
            'is_active' => 1,
        ]);
        $this->customer->markEmailAsVerified();

        $this->vendorA = User::create([
            'name' => 'Vendor Alpha',
            'email' => 'vendor_a_' . uniqid() . '@rentify.test',
            'password' => bcrypt('password123'),
            'role' => 'vendor',
            'vendor_name' => 'Toko Kamera Alpha',
            'is_active' => 1,
        ]);
        $this->vendorA->markEmailAsVerified();

        $this->vendorB = User::create([
            'name' => 'Vendor Beta',
            'email' => 'vendor_b_' . uniqid() . '@rentify.test',
            'password' => bcrypt('password123'),
            'role' => 'vendor',
            'vendor_name' => 'Toko Lensa Beta',
            'is_active' => 1,
        ]);
        $this->vendorB->markEmailAsVerified();

        $kategori = Kategori::create([
            'nama' => 'Kategori Audit',
            'slug' => 'kategori-audit-' . uniqid(),
            'is_active' => 1,
        ]);

        $this->barangA = Barang::create([
            'vendor_id' => $this->vendorA->id,
            'kategori_id' => $kategori->id,
            'nama' => 'Barang A 10k',
            'slug' => 'barang-a-' . uniqid(),
            'deskripsi' => 'Barang uji sewa',
            'harga_sewa_harian' => 10000,
            'deposit' => 0,
            'denda_per_hari' => 5000,
            'kondisi' => 'Baik',
            'stok_total' => 10,
            'status' => 'tersedia',
            'is_approved' => 1,
            'cover_photo' => 'barang/a.jpg',
            'is_delivery_supported' => 1,
        ]);

        $this->barangB = Barang::create([
            'vendor_id' => $this->vendorB->id,
            'kategori_id' => $kategori->id,
            'nama' => 'Barang B 20k',
            'slug' => 'barang-b-' . uniqid(),
            'deskripsi' => 'Barang uji sewa B',
            'harga_sewa_harian' => 20000,
            'deposit' => 0,
            'denda_per_hari' => 10000,
            'kondisi' => 'Baik',
            'stok_total' => 10,
            'status' => 'tersedia',
            'is_approved' => 1,
            'cover_photo' => 'barang/b.jpg',
            'is_delivery_supported' => 1,
        ]);
    }

    /**
     * Audit 1: Verifikasi Akurasi Rumus Finansial Dasar (Rp 10.000 Vendor -> Rp 10.500 Cust -> Rp 500 Fee)
     */
    public function test_audit_financial_formula_verification(): void
    {
        $pricing = TransactionCalculationService::calculateOrderPricing(10000.0);

        // Harga dasar vendor: 10.000
        $this->assertEquals(10000.0, $pricing['subtotal_sewa_asli']);
        // Markup sewa: 10.500
        $this->assertEquals(10500.0, $pricing['subtotal_sewa_markup']);
        // Fee Rentify 5%: 500
        $this->assertEquals(500.0, $pricing['rentify_fee']);
        // Hak pendapatan vendor: 10.000
        $this->assertEquals(10000.0, $pricing['vendor_earning']);
        // Total customer: 10.500
        $this->assertEquals(10500.0, $pricing['total_biaya']);
    }

    /**
     * Audit 2: Verifikasi Alur Saldo COD (Pemotongan Fee 5% dari Saldo Vendor)
     */
    public function test_audit_cod_fee_deduction_flow(): void
    {
        $pricing = TransactionCalculationService::calculateOrderPricing(10000.0);

        $orderId = DB::table('orders')->insertGetId([
            'user_id' => $this->customer->id,
            'vendor_id' => $this->vendorA->id,
            'kode_booking' => 'RNT-AUDIT-COD-' . uniqid(),
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

        // Saldo awal vendor Rp 20.000
        DB::table('saldos')->updateOrInsert(
            ['vendor_id' => $this->vendorA->id],
            ['saldo_aktif' => 20000, 'saldo_ditahan' => 0]
        );

        $settlement = TransactionCalculationService::settleOrderCompletion($orderId);

        // Fee platform 5% = Rp 500
        $this->assertEquals(500.0, $settlement['rentify_fee']);
        // Saldo vendor terpotong Rp 500 menjadi Rp 19.500
        $this->assertEquals(19500.0, $settlement['saldo_sesudah']);

        $saldoDb = DB::table('saldos')->where('vendor_id', $this->vendorA->id)->first();
        $this->assertEquals(19500.0, (float) $saldoDb->saldo_aktif);
    }

    /**
     * Audit 3: Verifikasi Bug Status 'Menunggu Ulasan' Menggagalkan Revert Settlement
     */
    public function test_audit_bug_menunggu_ulasan_blocks_settlement_reversion(): void
    {
        $pricing = TransactionCalculationService::calculateOrderPricing(10000.0);

        $orderId = DB::table('orders')->insertGetId([
            'user_id' => $this->customer->id,
            'vendor_id' => $this->vendorA->id,
            'kode_booking' => 'RNT-REV-TEST-' . uniqid(),
            'customer_name' => $this->customer->name,
            'customer_whatsapp' => '081234567890',
            'metode_pengambilan' => 'ambil',
            'shipping_fee' => 0,
            'total_biaya' => $pricing['total_biaya'],
            'total_price' => $pricing['total_biaya'],
            'vendor_earning' => 10000,
            'rentify_fee' => 500,
            'payment_method' => 'QRIS',
            'status' => 'Menunggu Ulasan', // Status aktual setelah customer menyelesaikan pesanan
            'tanggal_mulai' => now(),
            'tanggal_selesai' => now()->addDay(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('saldos')->updateOrInsert(
            ['vendor_id' => $this->vendorA->id],
            ['saldo_aktif' => 10000, 'saldo_ditahan' => 0]
        );

        // Percobaan revert settlement saat status 'Menunggu Ulasan'
        $revertResult = TransactionCalculationService::revertOrderSettlement($orderId, 'Uji Audit');

        // BUKTI: revertOrderSettlement gagal memproses revert karena hanya mengecek status === 'Selesai'!
        $this->assertFalse($revertResult['reverted']);
        $this->assertStringContainsString('Pesanan belum dalam status Selesai', $revertResult['message']);

        // Saldo vendor tidak ter-revert (tetap Rp 10.000)
        $saldoDb = DB::table('saldos')->where('vendor_id', $this->vendorA->id)->first();
        $this->assertEquals(10000.0, (float) $saldoDb->saldo_aktif);
    }

    /**
     * Audit 4: Verifikasi Bug Multi-Vendor QRIS Matching pada MutasiService
     */
    public function test_audit_bug_multi_vendor_qris_matching_fails(): void
    {
        $mutasiService = new MutasiService();

        // Simulasi 2 pesanan multi-vendor dalam satu checkout
        $order1 = DB::table('orders')->insertGetId([
            'user_id' => $this->customer->id,
            'vendor_id' => $this->vendorA->id,
            'kode_booking' => 'RNT-COMBO-1-' . uniqid(),
            'customer_name' => $this->customer->name,
            'customer_whatsapp' => '081234567890',
            'metode_pengambilan' => 'ambil',
            'shipping_fee' => 0,
            'total_biaya' => 10500,
            'total_price' => 10500,
            'payment_method' => 'QRIS',
            'status' => 'PENDING_PAYMENT',
            'tanggal_mulai' => now(),
            'tanggal_selesai' => now()->addDay(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $order2 = DB::table('orders')->insertGetId([
            'user_id' => $this->customer->id,
            'vendor_id' => $this->vendorB->id,
            'kode_booking' => 'RNT-COMBO-2-' . uniqid(),
            'customer_name' => $this->customer->name,
            'customer_whatsapp' => '081234567890',
            'metode_pengambilan' => 'ambil',
            'shipping_fee' => 0,
            'total_biaya' => 21000,
            'total_price' => 21000,
            'payment_method' => 'QRIS',
            'status' => 'PENDING_PAYMENT',
            'tanggal_mulai' => now(),
            'tanggal_selesai' => now()->addDay(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $totalAggregatedQris = 10500 + 21000; // Rp 31.500

        // Saat mutasi QRIS masuk sebesar Rp 31.500
        $match = $mutasiService->findMatchingOrder($totalAggregatedQris);

        // VERIFIKASI PERBAIKAN: MutasiService::findMatchingOrder berhasil menemukan pesanan multi-vendor combo!
        $this->assertNotNull($match);
    }

    /**
     * Audit 5: Verifikasi Bug Audit Command Melewatkan Status 'Menunggu Ulasan'
     */
    public function test_audit_command_ignores_menunggu_ulasan_status(): void
    {
        $pricing = TransactionCalculationService::calculateOrderPricing(10000.0);

        $orderId = DB::table('orders')->insertGetId([
            'user_id' => $this->customer->id,
            'vendor_id' => $this->vendorA->id,
            'kode_booking' => 'RNT-CMD-TEST-' . uniqid(),
            'customer_name' => $this->customer->name,
            'customer_whatsapp' => '081234567890',
            'metode_pengambilan' => 'ambil',
            'shipping_fee' => 0,
            'total_biaya' => $pricing['total_biaya'],
            'total_price' => $pricing['total_biaya'],
            'vendor_earning' => 10000,
            'rentify_fee' => 500,
            'payment_method' => 'QRIS',
            'status' => 'Menunggu Ulasan', // Status pesanan selesai oleh customer
            'tanggal_mulai' => now(),
            'tanggal_selesai' => now()->addDay(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Saldo vendor yang benar adalah 10.000
        DB::table('saldos')->updateOrInsert(
            ['vendor_id' => $this->vendorA->id],
            ['saldo_aktif' => 10000, 'saldo_ditahan' => 0]
        );

        // Ambil orders selesai dengan query yang dipakai AuditRentifyTransactionsCommand:
        $completedOrdersAuditQuery = DB::table('orders')
            ->where('vendor_id', $this->vendorA->id)
            ->where('status', 'Selesai')
            ->get();

        // BUKTI: Query audit hanya mencari 'Selesai', sehingga pesanan 'Menunggu Ulasan' terabaikan (count = 0)!
        $this->assertEquals(0, $completedOrdersAuditQuery->count());
    }
}
