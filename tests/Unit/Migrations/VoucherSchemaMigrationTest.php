<?php

namespace Tests\Unit\Migrations;

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class VoucherSchemaMigrationTest extends TestCase
{
    /**
     * Test all required tables and altered columns exist in database schema.
     */
    public function test_all_voucher_and_refund_tables_and_columns_exist(): void
    {
        // 1. Table vouchers altered columns
        $this->assertTrue(Schema::hasTable('vouchers'));
        $voucherColumns = [
            'id', 'vendor_id', 'kode_voucher', 'tipe_diskon', 'nilai_diskon',
            'maksimal_diskon', 'minimal_belanja', 'kuota_total', 'kuota_terpakai',
            'tanggal_mulai', 'tanggal_selesai', 'is_active',
            'tipe_voucher', 'funding_source', 'kuota_reserved', 'kuota_klaim_total',
            'kuota_klaim_terpakai', 'anggaran_maksimal_platform', 'anggaran_reserved',
            'anggaran_terpakai', 'visibilitas', 'cakupan_produk',
        ];
        foreach ($voucherColumns as $col) {
            $this->assertTrue(Schema::hasColumn('vouchers', $col), "Column vouchers.{$col} should exist");
        }

        // 2. Table voucher_barangs
        $this->assertTrue(Schema::hasTable('voucher_barangs'));
        foreach ([
            'id', 'voucher_id', 'barang_id', 'created_at'
        ] as $col) {
            $this->assertTrue(Schema::hasColumn('voucher_barangs', $col), "Column voucher_barangs.{$col} should exist");
        }

        // 3. Tables voucher_kategoris and voucher_vendors
        $this->assertTrue(Schema::hasTable('voucher_kategoris'));
        foreach ([
            'id', 'voucher_id', 'kategori_id', 'created_at'
        ] as $col) {
            $this->assertTrue(Schema::hasColumn('voucher_kategoris', $col), "Column voucher_kategoris.{$col} should exist");
        }

        $this->assertTrue(Schema::hasTable('voucher_vendors'));
        foreach ([
            'id', 'voucher_id', 'vendor_id', 'created_at'
        ] as $col) {
            $this->assertTrue(Schema::hasColumn('voucher_vendors', $col), "Column voucher_vendors.{$col} should exist");
        }

        // 4. Table customer_vouchers
        $this->assertTrue(Schema::hasTable('customer_vouchers'));
        foreach ([
            'id', 'user_id', 'voucher_id', 'status', 'claimed_at', 'used_at', 'created_at', 'updated_at'
        ] as $col) {
            $this->assertTrue(Schema::hasColumn('customer_vouchers', $col), "Column customer_vouchers.{$col} should exist");
        }

        // 5. Alterations on orders, order_items, and pembayarans
        $this->assertTrue(Schema::hasTable('orders'));
        foreach ([
            'payment_group_id', 'voucher_toko_id', 'potongan_voucher_toko',
            'voucher_platform_id', 'potongan_voucher_platform', 'voucher_id', 'potongan_voucher'
        ] as $col) {
            $this->assertTrue(Schema::hasColumn('orders', $col), "Column orders.{$col} should exist");
        }

        $this->assertTrue(Schema::hasTable('order_items'));
        foreach ([
            'subtotal_sewa_asli', 'subtotal_sewa_markup', 'alokasi_diskon_toko',
            'alokasi_diskon_platform', 'alokasi_diskon_koin', 'subtotal_sewa_bersih', 'status_item'
        ] as $col) {
            $this->assertTrue(Schema::hasColumn('order_items', $col), "Column order_items.{$col} should exist");
        }

        $this->assertTrue(Schema::hasTable('pembayarans'));
        $this->assertTrue(Schema::hasColumn('pembayarans', 'payment_group_id'));

        // 6. Table order_voucher_redemptions
        $this->assertTrue(Schema::hasTable('order_voucher_redemptions'));
        foreach ([
            'id', 'payment_group_id', 'order_id', 'voucher_id', 'customer_voucher_id',
            'tipe_voucher', 'alokasi_diskon', 'status', 'created_at', 'updated_at'
        ] as $col) {
            $this->assertTrue(Schema::hasColumn('order_voucher_redemptions', $col), "Column order_voucher_redemptions.{$col} should exist");
        }

        // 7. Tables refunds and admin_audit_logs
        $this->assertTrue(Schema::hasTable('refunds'));
        foreach ([
            'id', 'order_id', 'user_id', 'nominal_refund', 'tipe_refund', 'metode_refund',
            'nama_bank', 'nomor_rekening', 'atas_nama', 'status', 'bukti_transfer_refund',
            'processed_by', 'processed_at', 'catatan', 'created_at', 'updated_at'
        ] as $col) {
            $this->assertTrue(Schema::hasColumn('refunds', $col), "Column refunds.{$col} should exist");
        }

        $this->assertTrue(Schema::hasTable('admin_audit_logs'));
        foreach ([
            'id', 'admin_id', 'action', 'auditable_type', 'auditable_id',
            'payload_before', 'payload_after', 'ip_address', 'user_agent', 'created_at'
        ] as $col) {
            $this->assertTrue(Schema::hasColumn('admin_audit_logs', $col), "Column admin_audit_logs.{$col} should exist");
        }
    }

    /**
     * Test vendor_id nullability on vouchers table for platform-funded vouchers.
     */
    public function test_vouchers_table_allows_null_vendor_id_for_platform_voucher(): void
    {
        $voucherId = DB::table('vouchers')->insertGetId([
            'vendor_id'                  => null,
            'kode_voucher'               => 'PLATFORM' . strtoupper(uniqid()),
            'tipe_diskon'                => 'nominal',
            'nilai_diskon'               => 10000.00,
            'maksimal_diskon'            => null,
            'minimal_belanja'            => 50000.00,
            'kuota_total'                => 100,
            'kuota_terpakai'             => 0,
            'tanggal_mulai'              => now()->toDateString(),
            'tanggal_selesai'            => now()->addDays(30)->toDateString(),
            'is_active'                  => true,
            'tipe_voucher'               => 'platform',
            'funding_source'             => 'platform',
            'kuota_reserved'             => 0,
            'kuota_klaim_total'          => 100,
            'kuota_klaim_terpakai'       => 0,
            'anggaran_maksimal_platform' => 1000000.00,
            'anggaran_reserved'          => 0.00,
            'anggaran_terpakai'          => 0.00,
            'visibilitas'                => 'publik',
            'cakupan_produk'             => 'seluruh_toko',
            'created_at'                 => now(),
            'updated_at'                 => now(),
        ]);

        $this->assertNotNull($voucherId);

        $inserted = DB::table('vouchers')->where('id', $voucherId)->first();
        $this->assertNull($inserted->vendor_id);
        $this->assertEquals('platform', $inserted->tipe_voucher);
        $this->assertEquals('platform', $inserted->funding_source);

        // Clean up
        DB::table('vouchers')->where('id', $voucherId)->delete();
    }

    /**
     * Test unique constraint on customer_vouchers (1-user-1-claim at database level).
     */
    public function test_customer_vouchers_enforces_one_user_one_claim_unique_constraint(): void
    {
        $userId = DB::table('users')->insertGetId([
            'name'       => 'Claim User Test',
            'email'      => 'claimuser_' . uniqid() . '@example.com',
            'password'   => bcrypt('password'),
            'role'       => 'customer',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $voucherId = DB::table('vouchers')->insertGetId([
            'vendor_id'       => null,
            'kode_voucher'    => 'CLAIM' . strtoupper(uniqid()),
            'tipe_diskon'     => 'nominal',
            'nilai_diskon'    => 5000.00,
            'minimal_belanja' => 20000.00,
            'kuota_total'     => 50,
            'kuota_terpakai'  => 0,
            'tanggal_mulai'   => now()->toDateString(),
            'tanggal_selesai' => now()->addDays(14)->toDateString(),
            'is_active'       => true,
            'tipe_voucher'    => 'platform',
            'funding_source'  => 'platform',
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);

        try {
            // First claim must succeed
            $claimId = DB::table('customer_vouchers')->insertGetId([
                'user_id'    => $userId,
                'voucher_id' => $voucherId,
                'status'     => 'aktif',
                'claimed_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $this->assertNotNull($claimId);

            // Second claim by the same user on the same voucher must fail with QueryException (duplicate entry)
            $duplicateFailed = false;
            try {
                DB::table('customer_vouchers')->insert([
                    'user_id'    => $userId,
                    'voucher_id' => $voucherId,
                    'status'     => 'aktif',
                    'claimed_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } catch (QueryException $e) {
                $duplicateFailed = true;
            }

            $this->assertTrue($duplicateFailed, 'Duplicate claim must be rejected by unique constraint');
        } finally {
            DB::table('customer_vouchers')->where('voucher_id', $voucherId)->delete();
            DB::table('vouchers')->where('id', $voucherId)->delete();
            DB::table('users')->where('id', $userId)->delete();
        }
    }

    /**
     * Test whitelist pivot tables foreign key cascade deletions and unique constraints.
     */
    public function test_whitelist_pivot_tables_constraints_and_cascade_deletions(): void
    {
        $vendorId = DB::table('users')->insertGetId([
            'name'       => 'Vendor Pivot Test',
            'email'      => 'vendorpivot_' . uniqid() . '@example.com',
            'password'   => bcrypt('password'),
            'role'       => 'vendor',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $voucherId = DB::table('vouchers')->insertGetId([
            'vendor_id'       => $vendorId,
            'kode_voucher'    => 'PIVOT' . strtoupper(uniqid()),
            'tipe_diskon'     => 'persen',
            'nilai_diskon'    => 10.00,
            'minimal_belanja' => 10000.00,
            'kuota_total'     => 10,
            'tanggal_mulai'   => now()->toDateString(),
            'tanggal_selesai' => now()->addDays(7)->toDateString(),
            'is_active'       => true,
            'tipe_voucher'    => 'toko',
            'funding_source'  => 'vendor',
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);

        $kategoriId = DB::table('kategoris')->insertGetId([
            'nama'       => 'Kategori Test ' . uniqid(),
            'is_active'  => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $barangId = DB::table('barang')->insertGetId([
            'vendor_id'         => $vendorId,
            'kategori_id'       => $kategoriId,
            'nama'              => 'Barang Pivot Test',
            'slug'              => 'barang-pivot-' . uniqid(),
            'harga_sewa_harian' => 50000.00,
            'stok_total'        => 5,
            'status'            => 'tersedia',
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);

        try {
            // 1. Pivot voucher_barangs
            DB::table('voucher_barangs')->insert([
                'voucher_id' => $voucherId,
                'barang_id'  => $barangId,
                'created_at' => now(),
            ]);
            $this->assertEquals(1, DB::table('voucher_barangs')->where('voucher_id', $voucherId)->count());

            // 2. Pivot voucher_kategoris
            DB::table('voucher_kategoris')->insert([
                'voucher_id'  => $voucherId,
                'kategori_id' => $kategoriId,
                'created_at'  => now(),
            ]);
            $this->assertEquals(1, DB::table('voucher_kategoris')->where('voucher_id', $voucherId)->count());

            // 3. Pivot voucher_vendors
            DB::table('voucher_vendors')->insert([
                'voucher_id' => $voucherId,
                'vendor_id'  => $vendorId,
                'created_at' => now(),
            ]);
            $this->assertEquals(1, DB::table('voucher_vendors')->where('voucher_id', $voucherId)->count());

            // Test cascade delete on voucher deletion
            DB::table('vouchers')->where('id', $voucherId)->delete();

            $this->assertEquals(0, DB::table('voucher_barangs')->where('voucher_id', $voucherId)->count());
            $this->assertEquals(0, DB::table('voucher_kategoris')->where('voucher_id', $voucherId)->count());
            $this->assertEquals(0, DB::table('voucher_vendors')->where('voucher_id', $voucherId)->count());
        } finally {
            DB::table('barang')->where('id', $barangId)->delete();
            DB::table('kategoris')->where('id', $kategoriId)->delete();
            DB::table('users')->where('id', $vendorId)->delete();
        }
    }

    /**
     * Test refunds table enforces ON DELETE RESTRICT on order_id and user_id.
     */
    public function test_refunds_table_enforces_on_delete_restrict_and_default_status(): void
    {
        $userId = DB::table('users')->insertGetId([
            'name'       => 'Refund Customer Test',
            'email'      => 'refundcust_' . uniqid() . '@example.com',
            'password'   => bcrypt('password'),
            'role'       => 'customer',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $orderId = DB::table('orders')->insertGetId([
            'user_id'            => $userId,
            'kode_booking'       => 'RNT' . strtoupper(uniqid()),
            'payment_group_id'   => 'PGP' . strtoupper(uniqid()),
            'customer_name'      => 'Refund Customer',
            'customer_whatsapp'  => '081234567890',
            'metode_pengambilan' => 'ambil',
            'tanggal_mulai'      => now(),
            'tanggal_selesai'    => now()->addDays(2),
            'payment_method'     => 'QRIS',
            'total_biaya'        => 105000.00,
            'status'             => 'Dibatalkan',
            'created_at'         => now(),
            'updated_at'         => now(),
        ]);

        $refundId = DB::table('refunds')->insertGetId([
            'order_id'       => $orderId,
            'user_id'        => $userId,
            'nominal_refund' => 105000.00,
            'tipe_refund'    => 'pembatalan_total',
            'metode_refund'  => 'manual_transfer',
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);

        try {
            $refund = DB::table('refunds')->where('id', $refundId)->first();
            $this->assertEquals('MENUNGGU_DATA_REKENING', $refund->status);

            // Attempting to delete the order must throw QueryException due to ON DELETE RESTRICT
            $caughtException = false;
            try {
                DB::table('orders')->where('id', $orderId)->delete();
            } catch (QueryException $e) {
                $caughtException = true;
            }
            $this->assertTrue($caughtException, "Deleting order with active refund record must be restricted");

            // Attempting to delete the user must also throw QueryException due to ON DELETE RESTRICT
            $caughtUserException = false;
            try {
                DB::table('users')->where('id', $userId)->delete();
            } catch (QueryException $e) {
                $caughtUserException = true;
            }
            $this->assertTrue($caughtUserException, "Deleting user with active refund record must be restricted");
        } finally {
            DB::table('refunds')->where('id', $refundId)->delete();
            DB::table('orders')->where('id', $orderId)->delete();
            DB::table('users')->where('id', $userId)->delete();
        }
    }

    /**
     * Test order_voucher_redemptions default status and uniqueness constraint.
     */
    public function test_order_voucher_redemptions_defaults_and_uniqueness(): void
    {
        $userId = DB::table('users')->insertGetId([
            'name'       => 'Redemption User',
            'email'      => 'redemp_' . uniqid() . '@example.com',
            'password'   => bcrypt('password'),
            'role'       => 'customer',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $voucherId = DB::table('vouchers')->insertGetId([
            'vendor_id'       => null,
            'kode_voucher'    => 'REDEMP' . strtoupper(uniqid()),
            'tipe_diskon'     => 'nominal',
            'nilai_diskon'    => 10000.00,
            'minimal_belanja' => 50000.00,
            'kuota_total'     => 100,
            'tanggal_mulai'   => now()->toDateString(),
            'tanggal_selesai' => now()->addDays(30)->toDateString(),
            'is_active'       => true,
            'tipe_voucher'    => 'platform',
            'funding_source'  => 'platform',
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);

        $claimId = DB::table('customer_vouchers')->insertGetId([
            'user_id'    => $userId,
            'voucher_id' => $voucherId,
            'status'     => 'aktif',
            'claimed_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $paymentGroupId = 'PGP_MULTI_' . strtoupper(uniqid());

        $orderId = DB::table('orders')->insertGetId([
            'user_id'            => $userId,
            'kode_booking'       => 'RNT' . strtoupper(uniqid()),
            'payment_group_id'   => $paymentGroupId,
            'customer_name'      => 'Redemption Customer',
            'customer_whatsapp'  => '081234567891',
            'metode_pengambilan' => 'antar',
            'tanggal_mulai'      => now(),
            'tanggal_selesai'    => now()->addDays(2),
            'payment_method'     => 'QRIS',
            'total_biaya'        => 100000.00,
            'created_at'         => now(),
            'updated_at'         => now(),
        ]);

        try {
            $redemptionId = DB::table('order_voucher_redemptions')->insertGetId([
                'payment_group_id'    => $paymentGroupId,
                'order_id'            => $orderId,
                'voucher_id'          => $voucherId,
                'customer_voucher_id' => $claimId,
                'tipe_voucher'        => 'platform',
                'alokasi_diskon'      => 10000.00,
                'created_at'          => now(),
                'updated_at'          => now(),
            ]);

            $redemption = DB::table('order_voucher_redemptions')->where('id', $redemptionId)->first();
            $this->assertEquals('reserved', $redemption->status);
            $this->assertEquals(10000.00, (float) $redemption->alokasi_diskon);

            // Duplicate redemption of same voucher on same order must fail with QueryException
            $duplicateFailed = false;
            try {
                DB::table('order_voucher_redemptions')->insert([
                    'payment_group_id'    => $paymentGroupId,
                    'order_id'            => $orderId,
                    'voucher_id'          => $voucherId,
                    'customer_voucher_id' => $claimId,
                    'tipe_voucher'        => 'platform',
                    'alokasi_diskon'      => 5000.00,
                    'created_at'          => now(),
                    'updated_at'          => now(),
                ]);
            } catch (QueryException $e) {
                $duplicateFailed = true;
            }

            $this->assertTrue($duplicateFailed, 'Duplicate voucher redemption on same order must be rejected');
        } finally {
            DB::table('order_voucher_redemptions')->where('order_id', $orderId)->delete();
            DB::table('orders')->where('id', $orderId)->delete();
            DB::table('customer_vouchers')->where('id', $claimId)->delete();
            DB::table('vouchers')->where('id', $voucherId)->delete();
            DB::table('users')->where('id', $userId)->delete();
        }
    }

    /**
     * Test full rollback and re-migration forward integrity (php artisan migrate:rollback and php artisan migrate).
     */
    public function test_migrations_rollback_and_forward_execution_integrity(): void
    {
        // Clean up any stray test vouchers with null vendor_id
        DB::table('vouchers')->whereNull('vendor_id')->delete();

        // Rollback all 7 migrations created in TASK-167
        $exitRollback = Artisan::call('migrate:rollback', [
            '--step' => 7,
        ]);
        fwrite(STDERR, "ROLLBACK OUTPUT:\n" . Artisan::output() . "\n");
        $this->assertEquals(0, $exitRollback);

        // Verify tables/columns were rolled back
        $this->assertFalse(Schema::hasTable('refunds'));
        $this->assertFalse(Schema::hasTable('admin_audit_logs'));
        $this->assertFalse(Schema::hasTable('order_voucher_redemptions'));
        $this->assertFalse(Schema::hasTable('customer_vouchers'));
        $this->assertFalse(Schema::hasTable('voucher_vendors'));
        $this->assertFalse(Schema::hasTable('voucher_kategoris'));
        $this->assertFalse(Schema::hasTable('voucher_barangs'));
        $this->assertFalse(Schema::hasColumn('orders', 'voucher_toko_id'));
        $this->assertFalse(Schema::hasColumn('order_items', 'subtotal_sewa_asli'));
        $this->assertFalse(Schema::hasColumn('pembayarans', 'payment_group_id'));
        $this->assertFalse(Schema::hasColumn('vouchers', 'tipe_voucher'));

        // Migrate forward again
        $exitMigrate = Artisan::call('migrate', [
            '--path' => 'database/migrations',
        ]);
        $this->assertEquals(0, $exitMigrate);

        // Verify all tables and columns are restored
        $this->assertTrue(Schema::hasTable('refunds'));
        $this->assertTrue(Schema::hasTable('admin_audit_logs'));
        $this->assertTrue(Schema::hasTable('order_voucher_redemptions'));
        $this->assertTrue(Schema::hasTable('customer_vouchers'));
        $this->assertTrue(Schema::hasTable('voucher_vendors'));
        $this->assertTrue(Schema::hasTable('voucher_kategoris'));
        $this->assertTrue(Schema::hasTable('voucher_barangs'));
        $this->assertTrue(Schema::hasColumn('orders', 'voucher_toko_id'));
        $this->assertTrue(Schema::hasColumn('order_items', 'subtotal_sewa_asli'));
        $this->assertTrue(Schema::hasColumn('pembayarans', 'payment_group_id'));
        $this->assertTrue(Schema::hasColumn('vouchers', 'tipe_voucher'));
    }
}
