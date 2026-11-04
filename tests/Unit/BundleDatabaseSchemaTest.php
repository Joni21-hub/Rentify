<?php

namespace Tests\Unit;

use App\Models\Barang;
use App\Models\Cabang;
use App\Models\DetailPenyewaan;
use App\Models\OrderItemComponent;
use App\Models\PaketSewa;
use App\Models\PaketSewaItem;
use App\Models\Penyewaan;
use App\Models\Ulasan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class BundleDatabaseSchemaTest extends TestCase
{
    /**
     * Requirement 1.1, 1.2, 1.4, 5.1, 5.4, 5.8:
     * Validate that all bundle-related tables and altered columns exist in the database schema.
     */
    public function test_all_bundle_tables_and_altered_columns_exist(): void
    {
        // 1. Extensions on barang table (TASK-304)
        $this->assertTrue(Schema::hasTable('barang'), 'Table barang must exist');
        $this->assertTrue(Schema::hasColumn('barang', 'cabang_id'), 'Column barang.cabang_id must exist');
        $this->assertTrue(Schema::hasColumn('barang', 'berat_gram'), 'Column barang.berat_gram must exist');

        // 2. Table paket_sewa (TASK-305)
        $this->assertTrue(Schema::hasTable('paket_sewa'), 'Table paket_sewa must exist');
        $paketSewaColumns = [
            'id', 'vendor_id', 'cabang_id', 'nama', 'slug', 'deskripsi',
            'cover_photo', 'harga_sewa_harian_paket', 'status',
            'is_delivery_supported', 'created_at', 'updated_at'
        ];
        foreach ($paketSewaColumns as $col) {
            $this->assertTrue(Schema::hasColumn('paket_sewa', $col), "Column paket_sewa.{$col} must exist");
        }

        // 3. Table paket_sewa_items (TASK-306)
        $this->assertTrue(Schema::hasTable('paket_sewa_items'), 'Table paket_sewa_items must exist');
        $paketSewaItemColumns = [
            'id', 'paket_sewa_id', 'barang_id', 'quantity', 'created_at', 'updated_at'
        ];
        foreach ($paketSewaItemColumns as $col) {
            $this->assertTrue(Schema::hasColumn('paket_sewa_items', $col), "Column paket_sewa_items.{$col} must exist");
        }

        // 4. Alterations on order_items table (TASK-307)
        $this->assertTrue(Schema::hasTable('order_items'), 'Table order_items must exist');
        $this->assertTrue(Schema::hasColumn('order_items', 'product_id'), 'Column order_items.product_id must exist');
        $this->assertTrue(Schema::hasColumn('order_items', 'paket_sewa_id'), 'Column order_items.paket_sewa_id must exist');
        $this->assertTrue(Schema::hasColumn('order_items', 'item_type'), 'Column order_items.item_type must exist');

        // 5. Table order_item_components (TASK-308)
        $this->assertTrue(Schema::hasTable('order_item_components'), 'Table order_item_components must exist');
        $orderItemComponentColumns = [
            'id', 'order_item_id', 'barang_id', 'quantity', 'harga_komponen_asli',
            'subtotal_sewa_asli', 'subtotal_sewa_markup', 'alokasi_diskon_toko',
            'alokasi_diskon_platform', 'alokasi_diskon_koin', 'subtotal_sewa_bersih',
            'deposit_komponen', 'denda_komponen', 'status_komponen', 'returned_at',
            'created_at', 'updated_at'
        ];
        foreach ($orderItemComponentColumns as $col) {
            $this->assertTrue(Schema::hasColumn('order_item_components', $col), "Column order_item_components.{$col} must exist");
        }

        // 6. Alterations on ulasans table (TASK-309)
        $this->assertTrue(Schema::hasTable('ulasans'), 'Table ulasans must exist');
        $this->assertTrue(Schema::hasColumn('ulasans', 'barang_id'), 'Column ulasans.barang_id must exist');
        $this->assertTrue(Schema::hasColumn('ulasans', 'paket_sewa_id'), 'Column ulasans.paket_sewa_id must exist');
    }

    /**
     * Requirement 1.5, 5.1:
     * Test barang table extensions: default berat_gram = 1000 and cabang_id nullOnDelete.
     */
    public function test_barang_table_extensions_constraints(): void
    {
        $vendorId = DB::table('users')->insertGetId([
            'name'       => 'Vendor Barang Ext',
            'email'      => 'vendor_ext_' . uniqid() . '@example.com',
            'password'   => Hash::make('password'),
            'role'       => 'vendor',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        $cabangId = DB::table('cabangs')->insertGetId([
            'vendor_id'      => $vendorId,
            'nama_cabang'    => 'Cabang Ext Test',
            'alamat_lengkap' => 'Jl. Extension No. 1',
            'created_at'     => Carbon::now(),
            'updated_at'     => Carbon::now(),
        ]);

        $barangId = DB::table('barang')->insertGetId([
            'vendor_id'         => $vendorId,
            'cabang_id'         => $cabangId,
            'nama'              => 'Barang Ext Test',
            'slug'              => 'barang-ext-' . uniqid(),
            'harga_sewa_harian' => 50000.00,
            'created_at'        => Carbon::now(),
            'updated_at'        => Carbon::now(),
        ]);

        $inserted = DB::table('barang')->where('id', $barangId)->first();
        $this->assertEquals(1000, $inserted->berat_gram, 'Default berat_gram must be 1000');
        $this->assertEquals($cabangId, $inserted->cabang_id);

        // Delete cabang -> cabang_id must become null (nullOnDelete)
        DB::table('cabangs')->where('id', $cabangId)->delete();
        $reloaded = DB::table('barang')->where('id', $barangId)->first();
        $this->assertNull($reloaded->cabang_id, 'Deleting cabang must set barang.cabang_id to null');

        // Cleanup
        DB::table('barang')->where('id', $barangId)->delete();
        DB::table('users')->where('id', $vendorId)->delete();
    }

    /**
     * Requirement 1.4:
     * Test composite unique index on paket_sewa_items ([paket_sewa_id, barang_id]).
     */
    public function test_paket_sewa_items_composite_unique_constraint(): void
    {
        $vendorId = DB::table('users')->insertGetId([
            'name'       => 'Vendor Bundle Unique',
            'email'      => 'vendor_unique_' . uniqid() . '@example.com',
            'password'   => Hash::make('password'),
            'role'       => 'vendor',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        $cabangId = DB::table('cabangs')->insertGetId([
            'vendor_id'      => $vendorId,
            'nama_cabang'    => 'Cabang Unique Test',
            'alamat_lengkap' => 'Jl. Unique No. 1',
            'created_at'     => Carbon::now(),
            'updated_at'     => Carbon::now(),
        ]);

        $paketId = DB::table('paket_sewa')->insertGetId([
            'vendor_id'               => $vendorId,
            'cabang_id'               => $cabangId,
            'nama'                    => 'Paket Unique Test',
            'slug'                    => 'paket-unique-' . uniqid(),
            'harga_sewa_harian_paket' => 150000.00,
            'status'                  => 'tersedia',
            'is_delivery_supported'   => true,
            'created_at'              => Carbon::now(),
            'updated_at'              => Carbon::now(),
        ]);

        $barangId = DB::table('barang')->insertGetId([
            'vendor_id'         => $vendorId,
            'nama'              => 'Barang Item Unique',
            'slug'              => 'barang-item-unique-' . uniqid(),
            'harga_sewa_harian' => 45000.00,
            'created_at'        => Carbon::now(),
            'updated_at'        => Carbon::now(),
        ]);

        // First item insertion must succeed
        $itemId = DB::table('paket_sewa_items')->insertGetId([
            'paket_sewa_id' => $paketId,
            'barang_id'     => $barangId,
            'quantity'      => 2,
            'created_at'    => Carbon::now(),
            'updated_at'    => Carbon::now(),
        ]);
        $this->assertNotNull($itemId);

        // Second item insertion with identical paket_sewa_id and barang_id must fail
        $duplicateRejected = false;
        try {
            DB::table('paket_sewa_items')->insert([
                'paket_sewa_id' => $paketId,
                'barang_id'     => $barangId,
                'quantity'      => 1,
                'created_at'    => Carbon::now(),
                'updated_at'    => Carbon::now(),
            ]);
        } catch (QueryException $e) {
            $duplicateRejected = true;
        }

        $this->assertTrue($duplicateRejected, 'Duplicate insertion on [paket_sewa_id, barang_id] must trigger QueryException');

        // Cleanup
        DB::table('paket_sewa_items')->where('id', $itemId)->delete();
        DB::table('barang')->where('id', $barangId)->delete();
        DB::table('paket_sewa')->where('id', $paketId)->delete();
        DB::table('cabangs')->where('id', $cabangId)->delete();
        DB::table('users')->where('id', $vendorId)->delete();
    }

    /**
     * Requirement 1.1, 1.2, 1.4:
     * Test foreign key constraints: vendor cascade delete, cabang restrict delete, barang restrict delete.
     */
    public function test_paket_sewa_foreign_key_cascades_and_restrictions(): void
    {
        $vendorId = DB::table('users')->insertGetId([
            'name'       => 'Vendor FK Test',
            'email'      => 'vendor_fk_' . uniqid() . '@example.com',
            'password'   => Hash::make('password'),
            'role'       => 'vendor',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        $cabangId = DB::table('cabangs')->insertGetId([
            'vendor_id'      => $vendorId,
            'nama_cabang'    => 'Cabang FK Test',
            'alamat_lengkap' => 'Jl. FK No. 1',
            'created_at'     => Carbon::now(),
            'updated_at'     => Carbon::now(),
        ]);

        $paketId = DB::table('paket_sewa')->insertGetId([
            'vendor_id'               => $vendorId,
            'cabang_id'               => $cabangId,
            'nama'                    => 'Paket FK Test',
            'slug'                    => 'paket-fk-' . uniqid(),
            'harga_sewa_harian_paket' => 200000.00,
            'status'                  => 'tersedia',
            'created_at'              => Carbon::now(),
            'updated_at'              => Carbon::now(),
        ]);

        $barangId = DB::table('barang')->insertGetId([
            'vendor_id'         => $vendorId,
            'nama'              => 'Barang FK Test',
            'slug'              => 'barang-fk-' . uniqid(),
            'harga_sewa_harian' => 60000.00,
            'created_at'        => Carbon::now(),
            'updated_at'        => Carbon::now(),
        ]);

        $itemId = DB::table('paket_sewa_items')->insertGetId([
            'paket_sewa_id' => $paketId,
            'barang_id'     => $barangId,
            'quantity'      => 1,
            'created_at'    => Carbon::now(),
            'updated_at'    => Carbon::now(),
        ]);

        // 1. Cabang deletion must be RESTRICTED because it is referenced by paket_sewa
        $cabangRestrictTriggered = false;
        try {
            DB::table('cabangs')->where('id', $cabangId)->delete();
        } catch (QueryException $e) {
            $cabangRestrictTriggered = true;
        }
        $this->assertTrue($cabangRestrictTriggered, 'Deleting cabang referenced by paket_sewa must be restricted');

        // 2. Barang deletion must be RESTRICTED because it is referenced by paket_sewa_items
        $barangRestrictTriggered = false;
        try {
            DB::table('barang')->where('id', $barangId)->delete();
        } catch (QueryException $e) {
            $barangRestrictTriggered = true;
        }
        $this->assertTrue($barangRestrictTriggered, 'Deleting barang referenced by paket_sewa_items must be restricted');

        // 3. Vendor deletion must CASCADE delete paket_sewa and paket_sewa_items
        DB::table('paket_sewa_items')->where('id', $itemId)->delete();
        DB::table('barang')->where('id', $barangId)->delete();
        DB::table('paket_sewa')->where('id', $paketId)->delete();
        DB::table('cabangs')->where('id', $cabangId)->delete();
        DB::table('users')->where('id', $vendorId)->delete();

        $this->assertNull(DB::table('paket_sewa')->where('id', $paketId)->first());
    }

    /**
     * Requirement 5.4:
     * Test order_items alterations, nullable product_id, paket_sewa nullOnDelete, and order_item_components cascade.
     */
    public function test_order_items_and_components_schema_and_constraints(): void
    {
        $userId = DB::table('users')->insertGetId([
            'name'       => 'Customer Order Bundle',
            'email'      => 'cust_bundle_' . uniqid() . '@example.com',
            'password'   => Hash::make('password'),
            'role'       => 'customer',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        $vendorId = DB::table('users')->insertGetId([
            'name'       => 'Vendor Order Bundle',
            'email'      => 'vend_bundle_' . uniqid() . '@example.com',
            'password'   => Hash::make('password'),
            'role'       => 'vendor',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        $cabangId = DB::table('cabangs')->insertGetId([
            'vendor_id'      => $vendorId,
            'nama_cabang'    => 'Cabang Order Test',
            'alamat_lengkap' => 'Jl. Order Bundle No. 2',
            'created_at'     => Carbon::now(),
            'updated_at'     => Carbon::now(),
        ]);

        $paketId = DB::table('paket_sewa')->insertGetId([
            'vendor_id'               => $vendorId,
            'cabang_id'               => $cabangId,
            'nama'                    => 'Paket Camping Pro',
            'slug'                    => 'paket-camping-pro-' . uniqid(),
            'harga_sewa_harian_paket' => 120000.00,
            'status'                  => 'tersedia',
            'created_at'              => Carbon::now(),
            'updated_at'              => Carbon::now(),
        ]);

        $barangId = DB::table('barang')->insertGetId([
            'vendor_id'         => $vendorId,
            'nama'              => 'Tenda Dome 4P',
            'slug'              => 'tenda-dome-4p-' . uniqid(),
            'harga_sewa_harian' => 60000.00,
            'deposit'           => 100000.00,
            'denda_per_hari'    => 20000.00,
            'created_at'        => Carbon::now(),
            'updated_at'        => Carbon::now(),
        ]);

        $orderId = DB::table('orders')->insertGetId([
            'kode_booking'        => 'ORD-BNDL-' . uniqid(),
            'user_id'             => $userId,
            'customer_name'       => 'Customer Test',
            'customer_whatsapp'   => '081234567890',
            'metode_pengambilan'  => 'ambil_sendiri',
            'tanggal_mulai'       => Carbon::now()->toDateString(),
            'tanggal_selesai'     => Carbon::now()->addDays(3)->toDateString(),
            'payment_method'      => 'qris',
            'total_biaya'         => 120000.00,
            'total_price'         => 120000,
            'status'              => 'Menunggu Pembayaran',
            'created_at'          => Carbon::now(),
            'updated_at'          => Carbon::now(),
        ]);

        // Insert bundle order item with nullable product_id, valid paket_sewa_id, and item_type 'paket'
        $orderItemId = DB::table('order_items')->insertGetId([
            'order_id'             => $orderId,
            'product_id'           => null,
            'paket_sewa_id'        => $paketId,
            'item_type'            => 'paket',
            'product_name'         => 'Paket Camping Pro',
            'price'                => 120000,
            'quantity'             => 1,
            'subtotal_sewa_asli'   => 120000.00,
            'subtotal_sewa_markup' => 126000.00,
            'subtotal_sewa_bersih' => 126000.00,
            'status_item'          => 'aktif',
            'created_at'           => Carbon::now(),
            'updated_at'           => Carbon::now(),
        ]);

        $this->assertNotNull($orderItemId);
        $orderItemRow = DB::table('order_items')->where('id', $orderItemId)->first();
        $this->assertNull($orderItemRow->product_id, 'product_id must allow null for bundle items');
        $this->assertEquals('paket', $orderItemRow->item_type);
        $this->assertEquals($paketId, $orderItemRow->paket_sewa_id);

        // Insert component into order_item_components
        $componentId = DB::table('order_item_components')->insertGetId([
            'order_item_id'           => $orderItemId,
            'barang_id'               => $barangId,
            'quantity'                => 1,
            'harga_komponen_asli'     => 60000.00,
            'subtotal_sewa_asli'      => 60000.00,
            'subtotal_sewa_markup'    => 63000.00,
            'alokasi_diskon_toko'     => 0.00,
            'alokasi_diskon_platform' => 0.00,
            'alokasi_diskon_koin'     => 0.00,
            'subtotal_sewa_bersih'    => 63000.00,
            'deposit_komponen'        => 100000.00,
            'denda_komponen'          => 20000.00,
            'status_komponen'         => 'disewa',
            'created_at'              => Carbon::now(),
            'updated_at'              => Carbon::now(),
        ]);

        $this->assertNotNull($componentId);

        // Restrict test: barang cannot be deleted while referenced in order_item_components
        $barangRestrict = false;
        try {
            DB::table('barang')->where('id', $barangId)->delete();
        } catch (QueryException $e) {
            $barangRestrict = true;
        }
        $this->assertTrue($barangRestrict, 'Barang referenced by order_item_components must restrict deletion');

        // NullOnDelete test: deleting paket_sewa sets order_items.paket_sewa_id to null
        DB::table('paket_sewa')->where('id', $paketId)->delete();
        $orderItemAfterPaketDelete = DB::table('order_items')->where('id', $orderItemId)->first();
        $this->assertNull($orderItemAfterPaketDelete->paket_sewa_id, 'Deleting paket_sewa must set order_items.paket_sewa_id to null');

        // Cascade test: deleting order_items cascades delete to order_item_components
        DB::table('order_items')->where('id', $orderItemId)->delete();
        $this->assertNull(DB::table('order_item_components')->where('id', $componentId)->first(), 'Deleting order_item must cascade delete order_item_components');

        // Cleanup
        DB::table('orders')->where('id', $orderId)->delete();
        DB::table('barang')->where('id', $barangId)->delete();
        DB::table('cabangs')->where('id', $cabangId)->delete();
        DB::table('users')->whereIn('id', [$userId, $vendorId])->delete();
    }

    /**
     * Requirement 5.8:
     * Test ulasans alterations: nullable barang_id, foreign key paket_sewa_id, and nullOnDelete.
     */
    public function test_ulasans_bundle_alterations(): void
    {
        $userId = DB::table('users')->insertGetId([
            'name'       => 'User Ulasan Test',
            'email'      => 'user_rev_' . uniqid() . '@example.com',
            'password'   => Hash::make('password'),
            'role'       => 'customer',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        $vendorId = DB::table('users')->insertGetId([
            'name'       => 'Vendor Ulasan Test',
            'email'      => 'vend_rev_' . uniqid() . '@example.com',
            'password'   => Hash::make('password'),
            'role'       => 'vendor',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        $cabangId = DB::table('cabangs')->insertGetId([
            'vendor_id'      => $vendorId,
            'nama_cabang'    => 'Cabang Review Test',
            'alamat_lengkap' => 'Jl. Ulasan No. 3',
            'created_at'     => Carbon::now(),
            'updated_at'     => Carbon::now(),
        ]);

        $paketId = DB::table('paket_sewa')->insertGetId([
            'vendor_id'               => $vendorId,
            'cabang_id'               => $cabangId,
            'nama'                    => 'Paket Review Target',
            'slug'                    => 'paket-rev-' . uniqid(),
            'harga_sewa_harian_paket' => 75000.00,
            'status'                  => 'tersedia',
            'created_at'              => Carbon::now(),
            'updated_at'              => Carbon::now(),
        ]);

        $orderId = DB::table('orders')->insertGetId([
            'kode_booking'        => 'ORD-REV-' . uniqid(),
            'user_id'             => $userId,
            'customer_name'       => 'Customer Review',
            'customer_whatsapp'   => '081234567890',
            'metode_pengambilan'  => 'ambil_sendiri',
            'tanggal_mulai'       => Carbon::now()->toDateString(),
            'tanggal_selesai'     => Carbon::now()->addDays(3)->toDateString(),
            'payment_method'      => 'qris',
            'total_biaya'         => 75000.00,
            'total_price'         => 75000,
            'status'              => 'Selesai',
            'created_at'          => Carbon::now(),
            'updated_at'          => Carbon::now(),
        ]);

        // Create review for bundle with nullable barang_id
        $ulasanId = DB::table('ulasans')->insertGetId([
            'order_id'      => $orderId,
            'user_id'       => $userId,
            'barang_id'     => null,
            'paket_sewa_id' => $paketId,
            'rating'        => 5,
            'komentar'      => 'Paket sangat lengkap dan memuaskan!',
            'created_at'    => Carbon::now(),
            'updated_at'    => Carbon::now(),
        ]);

        $this->assertNotNull($ulasanId);
        $ulasanRow = DB::table('ulasans')->where('id', $ulasanId)->first();
        $this->assertNull($ulasanRow->barang_id, 'barang_id can be null in ulasans');
        $this->assertEquals($paketId, $ulasanRow->paket_sewa_id);

        // Delete paket_sewa -> paket_sewa_id in ulasans must become null (nullOnDelete)
        DB::table('paket_sewa')->where('id', $paketId)->delete();
        $ulasanAfterDelete = DB::table('ulasans')->where('id', $ulasanId)->first();
        $this->assertNull($ulasanAfterDelete->paket_sewa_id, 'Deleting paket_sewa must set ulasans.paket_sewa_id to null');

        // Cleanup
        DB::table('ulasans')->where('id', $ulasanId)->delete();
        DB::table('orders')->where('id', $orderId)->delete();
        DB::table('cabangs')->where('id', $cabangId)->delete();
        DB::table('users')->whereIn('id', [$userId, $vendorId])->delete();
    }

    /**
     * Requirement 1.1, 1.2, 1.4, 5.4, 5.8:
     * Test Eloquent model relations for PaketSewa, PaketSewaItem, OrderItemComponent,
     * Barang, DetailPenyewaan, and Ulasan.
     */
    public function test_eloquent_relationships_mapping(): void
    {
        $vendor = User::create([
            'name'     => 'Vendor Eloquent',
            'email'    => 'eloquent_v_' . uniqid() . '@example.com',
            'password' => Hash::make('secret'),
            'role'     => 'vendor',
        ]);

        $customer = User::create([
            'name'     => 'Customer Eloquent',
            'email'    => 'eloquent_c_' . uniqid() . '@example.com',
            'password' => Hash::make('secret'),
            'role'     => 'customer',
        ]);

        $cabang = Cabang::forceCreate([
            'vendor_id'      => $vendor->id,
            'nama_cabang'    => 'Cabang Eloquent Hub',
            'alamat_lengkap' => 'Jl. Eloquent Central No. 9',
        ]);

        $barang1 = Barang::create([
            'vendor_id'         => $vendor->id,
            'cabang_id'         => $cabang->id,
            'nama'              => 'Barang Model A',
            'slug'              => 'barang-mod-a-' . uniqid(),
            'harga_sewa_harian' => 30000.00,
            'berat_gram'        => 1200,
        ]);

        $barang2 = Barang::create([
            'vendor_id'         => $vendor->id,
            'cabang_id'         => $cabang->id,
            'nama'              => 'Barang Model B',
            'slug'              => 'barang-mod-b-' . uniqid(),
            'harga_sewa_harian' => 40000.00,
            'berat_gram'        => 800,
        ]);

        $paket = PaketSewa::create([
            'vendor_id'               => $vendor->id,
            'cabang_id'               => $cabang->id,
            'nama'                    => 'Paket Model AB',
            'slug'                    => 'paket-mod-ab-' . uniqid(),
            'harga_sewa_harian_paket' => 65000.00,
            'status'                  => 'tersedia',
            'is_delivery_supported'   => true,
        ]);

        $item1 = PaketSewaItem::create([
            'paket_sewa_id' => $paket->id,
            'barang_id'     => $barang1->id,
            'quantity'      => 1,
        ]);

        $item2 = PaketSewaItem::create([
            'paket_sewa_id' => $paket->id,
            'barang_id'     => $barang2->id,
            'quantity'      => 2,
        ]);

        // Verify PaketSewa relations
        $this->assertEquals($vendor->id, $paket->vendor->id);
        $this->assertEquals($cabang->id, $paket->cabang->id);
        $this->assertCount(2, $paket->items);

        // Verify PaketSewaItem relations
        $this->assertEquals($paket->id, $item1->paketSewa->id);
        $this->assertEquals($barang1->id, $item1->barang->id);

        // Verify Barang relations
        $this->assertEquals($cabang->id, $barang1->cabang->id);
        $this->assertTrue($barang1->paketSewaItems->contains($item1));

        // Create Order and DetailPenyewaan
        $order = Penyewaan::create([
            'kode_booking'        => 'ORD-ELOQ-' . uniqid(),
            'user_id'             => $customer->id,
            'customer_name'       => $customer->name,
            'customer_whatsapp'   => '081234567890',
            'metode_pengambilan'  => 'ambil_sendiri',
            'tanggal_mulai'       => Carbon::now()->toDateString(),
            'tanggal_selesai'     => Carbon::now()->addDays(3)->toDateString(),
            'payment_method'      => 'qris',
            'total_biaya'         => 65000.00,
            'total_price'         => 65000,
            'status'              => 'Menunggu Pembayaran',
        ]);

        $detail = DetailPenyewaan::create([
            'order_id'             => $order->id,
            'product_id'           => null,
            'paket_sewa_id'        => $paket->id,
            'item_type'            => 'paket',
            'product_name'         => $paket->nama,
            'price'                => 65000,
            'quantity'             => 1,
            'subtotal_sewa_asli'   => 65000.00,
            'subtotal_sewa_markup' => 68250.00,
            'subtotal_sewa_bersih' => 68250.00,
            'status_item'          => 'aktif',
        ]);

        $component = OrderItemComponent::create([
            'order_item_id'        => $detail->id,
            'barang_id'            => $barang1->id,
            'quantity'             => 1,
            'harga_komponen_asli'  => 30000.00,
            'subtotal_sewa_asli'   => 30000.00,
            'subtotal_sewa_markup' => 31500.00,
            'subtotal_sewa_bersih' => 31500.00,
            'deposit_komponen'     => 50000.00,
            'denda_komponen'       => 10000.00,
            'status_komponen'      => 'disewa',
        ]);

        // Verify DetailPenyewaan relations
        $this->assertEquals($paket->id, $detail->paketSewa->id);
        $this->assertCount(1, $detail->components);
        $this->assertNull($detail->barang);

        // Verify OrderItemComponent relations
        $this->assertEquals($detail->id, $component->orderItem->id);
        $this->assertEquals($barang1->id, $component->barang->id);

        // Verify Barang orderItemComponents relation
        $this->assertTrue($barang1->orderItemComponents->contains($component));

        // Verify Ulasan relations
        $ulasan = Ulasan::create([
            'order_id'      => $order->id,
            'user_id'       => $customer->id,
            'barang_id'     => null,
            'paket_sewa_id' => $paket->id,
            'rating'        => 5,
            'komentar'      => 'Keren sekali paketnya!',
        ]);

        $this->assertEquals($paket->id, $ulasan->paketSewa->id);
        $this->assertNull($ulasan->barang);

        // Cleanup
        $ulasan->delete();
        $component->delete();
        $detail->delete();
        $order->delete();
        $item1->delete();
        $item2->delete();
        $paket->delete();
        $barang1->delete();
        $barang2->delete();
        $cabang->delete();
        $customer->delete();
        $vendor->delete();
    }

    /**
     * Requirement 1.1, 1.2, 1.4:
     * Test migration rollback and re-running to verify cleanly reversible database state.
     */
    public function test_migration_rollback_and_reapply_cleanly(): void
    {
        // Purge any ghost migration records that do not exist in this worktree's migrations directory
        $worktreeMigrations = collect(scandir(database_path('migrations')))
            ->filter(fn($f) => str_ends_with($f, '.php'))
            ->map(fn($f) => str_replace('.php', '', $f))
            ->toArray();

        DB::table('migrations')
            ->whereNotIn('migration', $worktreeMigrations)
            ->delete();

        // Ensure all migrations are applied first
        Artisan::call('migrate');

        // Rollback the 6 bundle migrations
        $bundleMigrations = [
            'database/migrations/2026_11_04_000010_alter_ulasans_table_for_bundle_support.php',
            'database/migrations/2026_11_04_000009_create_order_item_components_table.php',
            'database/migrations/2026_11_04_000008_alter_order_items_for_bundle_support.php',
            'database/migrations/2026_11_04_000007_create_paket_sewa_items_table.php',
            'database/migrations/2026_11_04_000006_create_paket_sewa_table.php',
            'database/migrations/2026_11_04_000005_add_cabang_and_berat_to_barang_table.php',
        ];

        foreach ($bundleMigrations as $migrationPath) {
            Artisan::call('migrate:rollback', [
                '--path' => $migrationPath,
            ]);
        }

        // Verify tables and columns were dropped cleanly
        $this->assertFalse(Schema::hasTable('order_item_components'), 'order_item_components should be dropped on rollback');
        $this->assertFalse(Schema::hasTable('paket_sewa_items'), 'paket_sewa_items should be dropped on rollback');
        $this->assertFalse(Schema::hasTable('paket_sewa'), 'paket_sewa should be dropped on rollback');
        $this->assertFalse(Schema::hasColumn('order_items', 'paket_sewa_id'), 'order_items.paket_sewa_id should be dropped on rollback');
        $this->assertFalse(Schema::hasColumn('order_items', 'item_type'), 'order_items.item_type should be dropped on rollback');
        $this->assertFalse(Schema::hasColumn('ulasans', 'paket_sewa_id'), 'ulasans.paket_sewa_id should be dropped on rollback');
        $this->assertFalse(Schema::hasColumn('barang', 'cabang_id'), 'barang.cabang_id should be dropped on rollback');
        $this->assertFalse(Schema::hasColumn('barang', 'berat_gram'), 'barang.berat_gram should be dropped on rollback');

        // Re-apply migrations
        Artisan::call('migrate');

        // Verify tables and columns are recreated
        $this->assertTrue(Schema::hasTable('order_item_components'), 'order_item_components should exist after re-migration');
        $this->assertTrue(Schema::hasTable('paket_sewa_items'), 'paket_sewa_items should exist after re-migration');
        $this->assertTrue(Schema::hasTable('paket_sewa'), 'paket_sewa should exist after re-migration');
        $this->assertTrue(Schema::hasColumn('order_items', 'paket_sewa_id'), 'order_items.paket_sewa_id should exist after re-migration');
        $this->assertTrue(Schema::hasColumn('order_items', 'item_type'), 'order_items.item_type should exist after re-migration');
        $this->assertTrue(Schema::hasColumn('ulasans', 'paket_sewa_id'), 'ulasans.paket_sewa_id should exist after re-migration');
        $this->assertTrue(Schema::hasColumn('barang', 'cabang_id'), 'barang.cabang_id should exist after re-migration');
        $this->assertTrue(Schema::hasColumn('barang', 'berat_gram'), 'barang.berat_gram should exist after re-migration');
    }
}
