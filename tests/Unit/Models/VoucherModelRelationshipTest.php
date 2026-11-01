<?php

namespace Tests\Unit\Models;

use App\Models\AdminAuditLog;
use App\Models\Barang;
use App\Models\CustomerVoucher;
use App\Models\DetailPenyewaan;
use App\Models\Kategori;
use App\Models\OrderVoucherRedemption;
use App\Models\Pembayaran;
use App\Models\Penyewaan;
use App\Models\Refund;
use App\Models\User;
use App\Models\Voucher;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class VoucherModelRelationshipTest extends TestCase
{
    use DatabaseTransactions;

    protected User $admin;
    protected User $vendor;
    protected User $customer;
    protected Kategori $kategori;
    protected Barang $barang;

    protected function setUp(): void
    {
        parent::setUp();

        $uniq = uniqid();

        $this->admin = User::create([
            'name'     => "Admin {$uniq}",
            'email'    => "admin_{$uniq}@example.com",
            'password' => 'secret123',
            'role'     => 'admin',
        ]);

        $this->vendor = User::create([
            'name'          => "Vendor {$uniq}",
            'email'         => "vendor_{$uniq}@example.com",
            'password'      => 'secret123',
            'role'          => 'vendor',
            'vendor_name'   => "Vendor Store {$uniq}",
            'vendor_status' => 'verified',
        ]);

        $this->customer = User::create([
            'name'     => "Customer {$uniq}",
            'email'    => "customer_{$uniq}@example.com",
            'password' => 'secret123',
            'role'     => 'customer',
        ]);

        $this->kategori = Kategori::create([
            'nama' => "Kategori {$uniq}",
            'slug' => "kategori-{$uniq}",
        ]);

        $this->barang = Barang::create([
            'vendor_id'         => $this->vendor->id,
            'kategori_id'       => $this->kategori->id,
            'nama'              => "Barang Kamera {$uniq}",
            'slug'              => "barang-kamera-{$uniq}",
            'harga_sewa_harian' => 100000.00,
            'deposit'           => 50000.00,
            'denda_per_hari'    => 20000.00,
            'kondisi'           => 'sangat_baik',
            'stok_total'        => 5,
            'status'            => 'tersedia',
        ]);
    }

    /**
     * 1. Test User <-> Voucher relationship and vendor whitelist.
     */
    public function test_user_and_voucher_associations(): void
    {
        $uniq = uniqid();

        // 1.1 Store voucher belongsTo vendor & vendor hasMany vouchers
        $tokoVoucher = Voucher::create([
            'vendor_id'       => $this->vendor->id,
            'kode_voucher'    => "TOKO{$uniq}",
            'tipe_voucher'    => 'toko',
            'funding_source'  => 'vendor',
            'tipe_diskon'     => 'nominal',
            'nilai_diskon'    => 15000.00,
            'minimal_belanja' => 50000.00,
            'kuota_total'     => 50,
            'tanggal_mulai'   => '2026-11-01',
            'tanggal_selesai' => '2026-11-30',
            'is_active'       => true,
            'visibilitas'     => 'publik',
            'cakupan_produk'  => 'seluruh_toko',
        ]);

        $this->assertInstanceOf(User::class, $tokoVoucher->vendor);
        $this->assertEquals($this->vendor->id, $tokoVoucher->vendor->id);
        $this->assertTrue($this->vendor->vouchers->contains($tokoVoucher));

        // 1.2 Platform voucher with null vendor_id
        $platformVoucher = Voucher::create([
            'vendor_id'                  => null,
            'kode_voucher'               => "PLAT{$uniq}",
            'tipe_voucher'               => 'platform',
            'funding_source'             => 'platform',
            'tipe_diskon'                => 'persen',
            'nilai_diskon'               => 10.00,
            'maksimal_diskon'            => 25000.00,
            'minimal_belanja'            => 75000.00,
            'kuota_total'                => 100,
            'anggaran_maksimal_platform' => 5000000.00,
            'anggaran_reserved'          => 0.00,
            'anggaran_terpakai'          => 0.00,
            'tanggal_mulai'              => '2026-11-01',
            'tanggal_selesai'            => '2026-12-31',
            'is_active'                  => true,
            'visibilitas'                => 'publik',
            'cakupan_produk'             => 'seluruh_toko',
        ]);

        $this->assertNull($platformVoucher->vendor);

        // 1.3 Whitelist vendor linkage via voucher_vendors
        $platformVoucher->vendors()->attach($this->vendor->id);
        $this->assertTrue($platformVoucher->fresh()->vendors->contains($this->vendor));
        $this->assertTrue($this->vendor->fresh()->targetVouchers->contains($platformVoucher));
    }

    /**
     * 2. Test Voucher <-> Barang and Kategori whitelist associations.
     */
    public function test_voucher_barang_and_kategori_whitelist_associations(): void
    {
        $uniq = uniqid();

        $voucher = Voucher::create([
            'vendor_id'       => $this->vendor->id,
            'kode_voucher'    => "PROD{$uniq}",
            'tipe_voucher'    => 'toko',
            'funding_source'  => 'vendor',
            'tipe_diskon'     => 'nominal',
            'nilai_diskon'    => 20000.00,
            'minimal_belanja' => 60000.00,
            'kuota_total'     => 30,
            'tanggal_mulai'   => '2026-11-01',
            'tanggal_selesai' => '2026-11-20',
            'is_active'       => true,
            'visibilitas'     => 'publik',
            'cakupan_produk'  => 'produk_spesifik',
        ]);

        // Attach Barang
        $voucher->barangs()->attach($this->barang->id);
        $this->assertTrue($voucher->fresh()->barangs->contains($this->barang));
        $this->assertTrue($this->barang->fresh()->vouchers->contains($voucher));

        // Attach Kategori
        $voucher->kategoris()->attach($this->kategori->id);
        $this->assertTrue($voucher->fresh()->kategoris->contains($this->kategori));
        $this->assertTrue($this->kategori->fresh()->vouchers->contains($voucher));
    }

    /**
     * 3. Test CustomerVoucher model, scopes, datetime casting, and relationships.
     */
    public function test_customer_voucher_associations_and_scopes(): void
    {
        $uniq = uniqid();

        $voucher = Voucher::create([
            'vendor_id'            => null,
            'kode_voucher'         => "CLAIM{$uniq}",
            'tipe_voucher'         => 'platform',
            'funding_source'       => 'platform',
            'tipe_diskon'          => 'nominal',
            'nilai_diskon'         => 10000.00,
            'minimal_belanja'      => 50000.00,
            'kuota_total'          => 20,
            'kuota_klaim_total'    => 20,
            'kuota_klaim_terpakai' => 1,
            'tanggal_mulai'        => '2026-11-01',
            'tanggal_selesai'      => '2026-11-15',
            'is_active'            => true,
        ]);

        $claimedTime = Carbon::now()->subHour();
        $usedTime = Carbon::now();

        $claimAktif = CustomerVoucher::create([
            'user_id'    => $this->customer->id,
            'voucher_id' => $voucher->id,
            'status'     => 'aktif',
            'claimed_at' => $claimedTime,
        ]);

        $claimTerpakai = CustomerVoucher::create([
            'user_id'    => $this->vendor->id, // Use another user because of unique (user_id, voucher_id)
            'voucher_id' => $voucher->id,
            'status'     => 'terpakai',
            'claimed_at' => $claimedTime,
            'used_at'    => $usedTime,
        ]);

        // Verify relationships
        $this->assertInstanceOf(User::class, $claimAktif->user);
        $this->assertEquals($this->customer->id, $claimAktif->user->id);
        $this->assertInstanceOf(Voucher::class, $claimAktif->voucher);
        $this->assertEquals($voucher->id, $claimAktif->voucher->id);

        $this->assertTrue($this->customer->fresh()->customerVouchers->contains($claimAktif));
        $this->assertTrue($voucher->fresh()->customerVouchers->contains($claimAktif));

        // Verify datetime casting
        $this->assertInstanceOf(Carbon::class, $claimAktif->claimed_at);
        $this->assertInstanceOf(Carbon::class, $claimTerpakai->used_at);

        // Verify scopes
        $aktifResults = CustomerVoucher::aktif()->where('voucher_id', $voucher->id)->get();
        $this->assertTrue($aktifResults->contains($claimAktif));
        $this->assertFalse($aktifResults->contains($claimTerpakai));

        $terpakaiResults = CustomerVoucher::terpakai()->where('voucher_id', $voucher->id)->get();
        $this->assertTrue($terpakaiResults->contains($claimTerpakai));
        $this->assertFalse($terpakaiResults->contains($claimAktif));

        $claimTerpakai->update(['status' => 'kedaluwarsa']);
        $kedaluwarsaResults = CustomerVoucher::kedaluwarsa()->where('voucher_id', $voucher->id)->get();
        $this->assertTrue($kedaluwarsaResults->contains($claimTerpakai));
    }

    /**
     * 4. Test Penyewaan, DetailPenyewaan, and OrderVoucherRedemption associations.
     */
    public function test_penyewaan_detail_and_order_voucher_redemption_associations(): void
    {
        $uniq = uniqid();

        $voucherToko = Voucher::create([
            'vendor_id'       => $this->vendor->id,
            'kode_voucher'    => "TOKORED{$uniq}",
            'tipe_voucher'    => 'toko',
            'funding_source'  => 'vendor',
            'tipe_diskon'     => 'nominal',
            'nilai_diskon'    => 10000.00,
            'minimal_belanja' => 40000.00,
            'kuota_total'     => 10,
            'tanggal_mulai'   => '2026-11-01',
            'tanggal_selesai' => '2026-11-20',
            'is_active'       => true,
        ]);

        $voucherPlatform = Voucher::create([
            'vendor_id'       => null,
            'kode_voucher'    => "PLATRED{$uniq}",
            'tipe_voucher'    => 'platform',
            'funding_source'  => 'platform',
            'tipe_diskon'     => 'nominal',
            'nilai_diskon'    => 15000.00,
            'minimal_belanja' => 50000.00,
            'kuota_total'     => 10,
            'tanggal_mulai'   => '2026-11-01',
            'tanggal_selesai' => '2026-11-20',
            'is_active'       => true,
        ]);

        $order = Penyewaan::create([
            'user_id'                   => $this->customer->id,
            'vendor_id'                 => $this->vendor->id,
            'customer_name'             => $this->customer->name,
            'customer_whatsapp'         => '081234567890',
            'metode_pengambilan'        => 'ambil',
            'kode_booking'              => "RNT{$uniq}",
            'payment_group_id'          => "PGP{$uniq}",
            'voucher_toko_id'           => $voucherToko->id,
            'potongan_voucher_toko'     => 10000.00,
            'voucher_platform_id'       => $voucherPlatform->id,
            'potongan_voucher_platform' => 15000.00,
            'total_biaya'               => 175000.00,
            'status'                    => 'Menunggu Pembayaran',
            'tanggal_mulai'             => '2026-11-05',
            'tanggal_selesai'           => '2026-11-07',
            'payment_method'            => 'QRIS',
        ]);

        // Check Penyewaan -> Voucher associations
        $this->assertInstanceOf(Voucher::class, $order->voucherToko);
        $this->assertEquals($voucherToko->id, $order->voucherToko->id);
        $this->assertInstanceOf(Voucher::class, $order->voucherPlatform);
        $this->assertEquals($voucherPlatform->id, $order->voucherPlatform->id);

        // Check DetailPenyewaan association
        $detail = DetailPenyewaan::create([
            'order_id'                => $order->id,
            'product_id'              => $this->barang->id,
            'product_name'            => $this->barang->nama,
            'price'                   => 100000.00,
            'quantity'                => 2,
            'subtotal_sewa_asli'      => 200000.00,
            'subtotal_sewa_markup'    => 210000.00,
            'alokasi_diskon_toko'     => 10000.00,
            'alokasi_diskon_platform' => 15000.00,
            'alokasi_diskon_koin'     => 0.00,
            'subtotal_sewa_bersih'    => 185000.00,
            'status_item'             => 'aktif',
        ]);

        $this->assertTrue($order->fresh()->details->contains($detail));
        $this->assertInstanceOf(Penyewaan::class, $detail->penyewaan);
        $this->assertEquals($order->id, $detail->penyewaan->id);
        $this->assertInstanceOf(Penyewaan::class, $detail->order);
        $this->assertEquals($order->id, $detail->order->id);
        $this->assertInstanceOf(Barang::class, $detail->barang);
        $this->assertEquals($this->barang->id, $detail->barang->id);

        // Create CustomerVoucher for redemption
        $cvToko = CustomerVoucher::create([
            'user_id'    => $this->customer->id,
            'voucher_id' => $voucherToko->id,
            'status'     => 'terpakai',
            'claimed_at' => Carbon::now()->subMinutes(10),
        ]);

        $redemption = OrderVoucherRedemption::create([
            'payment_group_id'    => "PGP{$uniq}",
            'order_id'            => $order->id,
            'penyewaan_id'        => $order->id,
            'voucher_id'          => $voucherToko->id,
            'customer_voucher_id' => $cvToko->id,
            'tipe_voucher'        => 'toko',
            'alokasi_diskon'      => 10000.00,
            'status'              => 'applied',
        ]);

        $this->assertTrue($order->fresh()->redemptions->contains($redemption));
        $this->assertTrue($voucherToko->fresh()->redemptions->contains($redemption));
        $this->assertTrue($cvToko->fresh()->redemptions->contains($redemption));
        $this->assertInstanceOf(Penyewaan::class, $redemption->order);
        $this->assertEquals($order->id, $redemption->order->id);
        $this->assertInstanceOf(Penyewaan::class, $redemption->penyewaan);
        $this->assertEquals($order->id, $redemption->penyewaan->id);
        $this->assertInstanceOf(Voucher::class, $redemption->voucher);
        $this->assertEquals($voucherToko->id, $redemption->voucher->id);
        $this->assertInstanceOf(CustomerVoucher::class, $redemption->customerVoucher);
        $this->assertEquals($cvToko->id, $redemption->customerVoucher->id);
    }

    /**
     * 5. Test Pembayaran relationship and payment_group_id linkage.
     */
    public function test_pembayaran_associations_and_linkage(): void
    {
        $uniq = uniqid();

        $order = Penyewaan::create([
            'user_id'            => $this->customer->id,
            'vendor_id'          => $this->vendor->id,
            'customer_name'      => $this->customer->name,
            'customer_whatsapp'  => '081234567890',
            'metode_pengambilan' => 'ambil',
            'kode_booking'       => "RNT{$uniq}",
            'payment_group_id'   => "PGP{$uniq}",
            'total_biaya'        => 120000.00,
            'status'             => 'Menunggu Pembayaran',
            'tanggal_mulai'      => '2026-11-05',
            'tanggal_selesai'    => '2026-11-06',
            'payment_method'     => 'QRIS',
        ]);

        $pembayaran = Pembayaran::create([
            'order_id'         => $order->id,
            'payment_group_id' => "PGP{$uniq}",
            'metode'           => 'QRIS',
            'jumlah'           => 120000.00,
            'status'           => 'pending',
        ]);

        $this->assertInstanceOf(Pembayaran::class, $order->fresh()->pembayaran);
        $this->assertEquals($pembayaran->id, $order->fresh()->pembayaran->id);

        $this->assertInstanceOf(Penyewaan::class, $pembayaran->order);
        $this->assertEquals($order->id, $pembayaran->order->id);
        $this->assertInstanceOf(Penyewaan::class, $pembayaran->penyewaan);
        $this->assertEquals($order->id, $pembayaran->penyewaan->id);
        $this->assertEquals("PGP{$uniq}", $pembayaran->payment_group_id);
    }

    /**
     * 6. Test Refund model associations, default status, and processor.
     */
    public function test_refund_associations_and_default_status(): void
    {
        $uniq = uniqid();

        $order = Penyewaan::create([
            'user_id'            => $this->customer->id,
            'vendor_id'          => $this->vendor->id,
            'customer_name'      => $this->customer->name,
            'customer_whatsapp'  => '081234567890',
            'metode_pengambilan' => 'ambil',
            'kode_booking'       => "RNT{$uniq}",
            'payment_group_id'   => "PGP{$uniq}",
            'total_biaya'        => 150000.00,
            'status'             => 'Dibatalkan',
            'tanggal_mulai'      => '2026-11-05',
            'tanggal_selesai'    => '2026-11-06',
            'payment_method'     => 'QRIS',
        ]);

        $refund = Refund::create([
            'order_id'       => $order->id,
            'penyewaan_id'   => $order->id,
            'user_id'        => $this->customer->id,
            'nominal_refund' => 150000.00,
            'tipe_refund'    => 'pembatalan_total',
            'metode_refund'  => 'manual_transfer',
            'nama_bank'      => 'BCA',
            'nomor_rekening' => '1234567890',
            'atas_nama'      => 'Customer Rentify',
            'processed_by'   => $this->admin->id,
            'processed_at'   => Carbon::now(),
            'catatan'        => 'Refund pembatalan penyewaan',
        ]);

        // Default status verification
        $this->assertEquals('MENUNGGU_DATA_REKENING', $refund->status);

        // Model associations
        $this->assertTrue($order->fresh()->refunds->contains($refund));
        $this->assertInstanceOf(Penyewaan::class, $refund->order);
        $this->assertEquals($order->id, $refund->order->id);
        $this->assertInstanceOf(Penyewaan::class, $refund->penyewaan);
        $this->assertEquals($order->id, $refund->penyewaan->id);

        $this->assertInstanceOf(User::class, $refund->user);
        $this->assertEquals($this->customer->id, $refund->user->id);
        $this->assertTrue($this->customer->fresh()->refunds->contains($refund));

        $this->assertInstanceOf(User::class, $refund->processor);
        $this->assertEquals($this->admin->id, $refund->processor->id);
        $this->assertTrue($this->admin->fresh()->processedRefunds->contains($refund));
    }

    /**
     * 7. Test AdminAuditLog polymorphic relationship and JSON array casting.
     */
    public function test_admin_audit_log_polymorphic_relation_and_json_casting(): void
    {
        $uniq = uniqid();

        $voucher = Voucher::create([
            'vendor_id'                  => null,
            'kode_voucher'               => "AUDIT{$uniq}",
            'tipe_voucher'               => 'platform',
            'funding_source'             => 'platform',
            'tipe_diskon'                => 'nominal',
            'nilai_diskon'               => 20000.00,
            'minimal_belanja'            => 100000.00,
            'kuota_total'                => 50,
            'anggaran_maksimal_platform' => 1000000.00,
            'anggaran_reserved'          => 0.00,
            'anggaran_terpakai'          => 0.00,
            'tanggal_mulai'              => '2026-11-01',
            'tanggal_selesai'            => '2026-11-30',
            'is_active'                  => true,
        ]);

        $payloadBefore = [
            'anggaran_maksimal_platform' => 1000000.00,
            'is_active'                  => true,
            'tags'                       => ['promo', 'november'],
        ];

        $payloadAfter = [
            'anggaran_maksimal_platform' => 2000000.00,
            'is_active'                  => false,
            'tags'                       => ['promo', 'november', 'extended'],
        ];

        $log = AdminAuditLog::create([
            'admin_id'       => $this->admin->id,
            'action'         => 'UPDATE_VOUCHER_BUDGET',
            'auditable_type' => Voucher::class,
            'auditable_id'   => $voucher->id,
            'payload_before' => $payloadBefore,
            'payload_after'  => $payloadAfter,
            'ip_address'     => '127.0.0.1',
            'user_agent'     => 'PHPUnit Test Runner',
        ]);

        // Verify relationships
        $this->assertInstanceOf(User::class, $log->admin);
        $this->assertEquals($this->admin->id, $log->admin->id);
        $this->assertTrue($this->admin->fresh()->adminAuditLogs->contains($log));

        $this->assertInstanceOf(Voucher::class, $log->auditable);
        $this->assertEquals($voucher->id, $log->auditable->id);

        // Verify JSON casting to array
        $freshLog = $log->fresh();
        $this->assertIsArray($freshLog->payload_before);
        $this->assertIsArray($freshLog->payload_after);
        $this->assertEquals(1000000.00, $freshLog->payload_before['anggaran_maksimal_platform']);
        $this->assertEquals(2000000.00, $freshLog->payload_after['anggaran_maksimal_platform']);
        $this->assertEquals(['promo', 'november', 'extended'], $freshLog->payload_after['tags']);
    }

    /**
     * 8. Test decimal:2 casting precision across all financial models.
     */
    public function test_decimal_casting_precision_across_all_financial_models(): void
    {
        $uniq = uniqid();

        // 8.1 Voucher decimal casts
        $voucher = Voucher::create([
            'vendor_id'                  => null,
            'kode_voucher'               => "DEC{$uniq}",
            'tipe_voucher'               => 'platform',
            'funding_source'             => 'platform',
            'tipe_diskon'                => 'nominal',
            'nilai_diskon'               => 15000.00,
            'minimal_belanja'            => 50000.00,
            'kuota_total'                => 10,
            'kuota_reserved'             => 2,
            'kuota_klaim_total'          => 10,
            'kuota_klaim_terpakai'       => 1,
            'anggaran_maksimal_platform' => 500000.50,
            'anggaran_reserved'          => 15000.25,
            'anggaran_terpakai'          => 30000.75,
            'tanggal_mulai'              => '2026-11-01',
            'tanggal_selesai'            => '2026-11-20',
            'is_active'                  => true,
        ]);

        $freshVoucher = $voucher->fresh();
        $this->assertSame('500000.50', (string) $freshVoucher->anggaran_maksimal_platform);
        $this->assertSame('15000.25', (string) $freshVoucher->anggaran_reserved);
        $this->assertSame('30000.75', (string) $freshVoucher->anggaran_terpakai);
        $this->assertIsInt($freshVoucher->kuota_total);
        $this->assertIsInt($freshVoucher->kuota_reserved);

        // 8.2 Penyewaan decimal casts
        $order = Penyewaan::create([
            'user_id'                   => $this->customer->id,
            'vendor_id'                 => $this->vendor->id,
            'customer_name'             => $this->customer->name,
            'customer_whatsapp'         => '081234567890',
            'metode_pengambilan'        => 'ambil',
            'kode_booking'              => "RNT{$uniq}",
            'payment_group_id'          => "PGP{$uniq}",
            'voucher_toko_id'           => null,
            'potongan_voucher_toko'     => 12500.50,
            'voucher_platform_id'       => null,
            'potongan_voucher_platform' => 25000.75,
            'total_biaya'               => 200000.00,
            'status'                    => 'Menunggu Pembayaran',
            'tanggal_mulai'             => '2026-11-05',
            'tanggal_selesai'           => '2026-11-06',
            'payment_method'            => 'QRIS',
        ]);

        $freshOrder = $order->fresh();
        $this->assertSame('12500.50', (string) $freshOrder->potongan_voucher_toko);
        $this->assertSame('25000.75', (string) $freshOrder->potongan_voucher_platform);

        // 8.3 DetailPenyewaan decimal casts
        $detail = DetailPenyewaan::create([
            'order_id'                => $order->id,
            'product_id'              => $this->barang->id,
            'product_name'            => $this->barang->nama,
            'price'                   => 100000.50,
            'quantity'                => 1,
            'subtotal_sewa_asli'      => 100000.50,
            'subtotal_sewa_markup'    => 105000.53,
            'alokasi_diskon_toko'     => 12500.50,
            'alokasi_diskon_platform' => 25000.75,
            'alokasi_diskon_koin'     => 5000.00,
            'subtotal_sewa_bersih'    => 62500.28,
            'status_item'             => 'aktif',
        ]);

        $freshDetail = $detail->fresh();
        $this->assertSame('100000.50', (string) $freshDetail->subtotal_sewa_asli);
        $this->assertSame('105000.53', (string) $freshDetail->subtotal_sewa_markup);
        $this->assertSame('12500.50', (string) $freshDetail->alokasi_diskon_toko);
        $this->assertSame('25000.75', (string) $freshDetail->alokasi_diskon_platform);
        $this->assertSame('5000.00', (string) $freshDetail->alokasi_diskon_koin);
        $this->assertSame('62500.28', (string) $freshDetail->subtotal_sewa_bersih);

        // 8.4 Refund decimal casts
        $refund = Refund::create([
            'order_id'       => $order->id,
            'user_id'        => $this->customer->id,
            'nominal_refund' => 62500.28,
            'tipe_refund'    => 'pembatalan_parsial',
            'metode_refund'  => 'manual_transfer',
        ]);

        $freshRefund = $refund->fresh();
        $this->assertSame('62500.28', (string) $freshRefund->nominal_refund);

        // 8.5 OrderVoucherRedemption decimal casts
        $cv = CustomerVoucher::create([
            'user_id'    => $this->customer->id,
            'voucher_id' => $voucher->id,
            'status'     => 'aktif',
            'claimed_at' => Carbon::now(),
        ]);

        $redemption = OrderVoucherRedemption::create([
            'payment_group_id'    => "PGP{$uniq}",
            'order_id'            => $order->id,
            'voucher_id'          => $voucher->id,
            'customer_voucher_id' => $cv->id,
            'tipe_voucher'        => 'platform',
            'alokasi_diskon'      => 25000.75,
            'status'              => 'applied',
        ]);

        $freshRedemption = $redemption->fresh();
        $this->assertSame('25000.75', (string) $freshRedemption->alokasi_diskon);
    }
}
