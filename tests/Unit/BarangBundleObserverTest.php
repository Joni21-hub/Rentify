<?php

namespace Tests\Unit;

use App\Events\VendorBundleAlertEvent;
use App\Models\Barang;
use App\Models\Cabang;
use App\Models\DetailPenyewaan;
use App\Models\OrderItemComponent;
use App\Models\PaketSewa;
use App\Models\PaketSewaItem;
use App\Models\Penyewaan;
use App\Models\User;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\DataProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class BarangBundleObserverTest extends TestCase
{
    protected User $vendor;
    protected User $customer;
    protected Cabang $cabang;

    protected function setUp(): void
    {
        parent::setUp();

        $vId = DB::table('users')->insertGetId([
            'name'              => 'Vendor Observer Test',
            'email'             => 'vendor_obs_' . Str::random(8) . '@rentify.test',
            'password'          => Hash::make('password'),
            'role'              => 'vendor',
            'vendor_status'     => 'approved',
            'whatsapp'          => '081299991111',
            'whatsapp_vendor'   => '081299991111',
            'email_verified_at' => Carbon::now(),
            'created_at'        => Carbon::now(),
            'updated_at'        => Carbon::now(),
        ]);
        $this->vendor = User::find($vId);

        $cId = DB::table('users')->insertGetId([
            'name'              => 'Customer Observer Test',
            'email'             => 'cust_obs_' . Str::random(8) . '@rentify.test',
            'password'          => Hash::make('password'),
            'role'              => 'customer',
            'email_verified_at' => Carbon::now(),
            'created_at'        => Carbon::now(),
            'updated_at'        => Carbon::now(),
        ]);
        $this->customer = User::find($cId);

        $cabangId = DB::table('cabangs')->insertGetId([
            'vendor_id'      => $this->vendor->id,
            'nama_cabang'    => 'Cabang Observer Test',
            'alamat_lengkap' => 'Jl. Pengamat No. 99',
            'created_at'     => Carbon::now(),
            'updated_at'     => Carbon::now(),
        ]);
        $this->cabang = Cabang::find($cabangId);
    }

    protected function createBarang(array $attributes = []): Barang
    {
        $id = DB::table('barang')->insertGetId(array_merge([
            'vendor_id'             => $this->vendor->id,
            'cabang_id'             => $this->cabang->id,
            'nama'                  => 'Barang Obs ' . Str::random(5),
            'slug'                  => 'barang-obs-' . Str::random(10),
            'harga_sewa_harian'     => 50000.00,
            'deposit'               => 100000.00,
            'denda_per_hari'        => 20000.00,
            'stok_total'            => 10,
            'status'                => 'tersedia',
            'is_approved'           => 1,
            'is_delivery_supported' => 1,
            'created_at'            => Carbon::now(),
            'updated_at'            => Carbon::now(),
        ], $attributes));

        return Barang::find($id);
    }

    protected function createPaketSewa(array $attributes = []): PaketSewa
    {
        $id = DB::table('paket_sewa')->insertGetId(array_merge([
            'vendor_id'               => $this->vendor->id,
            'cabang_id'               => $this->cabang->id,
            'nama'                    => 'Paket Obs ' . Str::random(5),
            'slug'                    => 'paket-obs-' . Str::random(8),
            'harga_sewa_harian_paket' => 120000.00,
            'status'                  => 'tersedia',
            'is_delivery_supported'   => 1,
            'created_at'              => Carbon::now(),
            'updated_at'              => Carbon::now(),
        ], $attributes));

        return PaketSewa::find($id);
    }

    protected function createOrder(array $attributes = []): int
    {
        return DB::table('orders')->insertGetId(array_merge([
            'user_id'            => $this->customer->id,
            'vendor_id'          => $this->vendor->id,
            'customer_name'      => $this->customer->name,
            'customer_whatsapp'  => '081234567890',
            'metode_pengambilan' => 'pickup',
            'tanggal_mulai'      => Carbon::now(),
            'tanggal_selesai'    => Carbon::now()->addDays(2),
            'payment_method'     => 'qris',
            'kode_booking'       => Penyewaan::generateKodeBooking(),
            'total_biaya'        => 150000.00,
            'status'             => 'Menunggu Pembayaran',
            'created_at'         => Carbon::now(),
            'updated_at'         => Carbon::now(),
        ], $attributes));
    }

    protected function createOrderItemComponent(int $orderItemId, int $barangId, array $attributes = []): int
    {
        return DB::table('order_item_components')->insertGetId(array_merge([
            'order_item_id'        => $orderItemId,
            'barang_id'            => $barangId,
            'quantity'             => 1,
            'harga_komponen_asli'  => 50000.00,
            'subtotal_sewa_asli'   => 50000.00,
            'subtotal_sewa_markup' => 52500.00,
            'subtotal_sewa_bersih' => 52500.00,
            'created_at'           => Carbon::now(),
            'updated_at'           => Carbon::now(),
        ], $attributes));
    }

    /**
     * Requirement 1.8, 3.1:
     * When master item's status changes to unavailable, all associated bundles transition to 'tidak_aktif'
     * and dispatch vendor alerts.
     */
    public function test_bundle_deactivates_and_dispatches_alert_when_item_status_changes_to_unavailable(): void
    {
        Event::fake([VendorBundleAlertEvent::class]);

        $item1 = $this->createBarang(['status' => 'tersedia']);
        $item2 = $this->createBarang(['status' => 'tersedia']);
        $bundle = $this->createPaketSewa(['status' => 'tersedia']);

        DB::table('paket_sewa_items')->insert([
            ['paket_sewa_id' => $bundle->id, 'barang_id' => $item1->id, 'quantity' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['paket_sewa_id' => $bundle->id, 'barang_id' => $item2->id, 'quantity' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
        ]);

        // Ubah status master item 1 menjadi tidak_tersedia
        $item1->update(['status' => 'tidak_tersedia']);

        // Verifikasi status bundle berubah menjadi tidak_aktif
        $this->assertEquals('tidak_aktif', $bundle->fresh()->status);

        // Verifikasi alert ter-dispatch
        Event::assertDispatched(VendorBundleAlertEvent::class, function ($event) use ($bundle, $item1) {
            return $event->bundle->id === $bundle->id && $event->barang->id === $item1->id;
        });
    }

    /**
     * Requirement 1.8, 3.1:
     * When master item becomes unapproved (is_approved = 0), all associated bundles transition to 'tidak_aktif'
     * and dispatch vendor alerts.
     */
    public function test_bundle_deactivates_and_dispatches_alert_when_item_becomes_unapproved(): void
    {
        Event::fake([VendorBundleAlertEvent::class]);

        $item1 = $this->createBarang(['is_approved' => 1]);
        $item2 = $this->createBarang(['is_approved' => 1]);
        $bundle = $this->createPaketSewa(['status' => 'tersedia']);

        DB::table('paket_sewa_items')->insert([
            ['paket_sewa_id' => $bundle->id, 'barang_id' => $item1->id, 'quantity' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['paket_sewa_id' => $bundle->id, 'barang_id' => $item2->id, 'quantity' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
        ]);

        // Admin mencabut approval master item 2
        $item2->update(['is_approved' => 0]);

        $this->assertEquals('tidak_aktif', $bundle->fresh()->status);
        Event::assertDispatched(VendorBundleAlertEvent::class, function ($event) use ($bundle, $item2) {
            return $event->bundle->id === $bundle->id && $event->barang->id === $item2->id;
        });
    }

    /**
     * Requirement 1.8, 3.1, 4.9:
     * When master item is deleted, all associated bundles transition to 'tidak_aktif' and dispatch vendor alerts.
     */
    public function test_bundle_deactivates_and_dispatches_alert_when_item_is_deleted(): void
    {
        Event::fake([VendorBundleAlertEvent::class]);

        $item1 = $this->createBarang();
        $item2 = $this->createBarang();
        $bundle = $this->createPaketSewa(['status' => 'tersedia']);

        DB::table('paket_sewa_items')->insert([
            ['paket_sewa_id' => $bundle->id, 'barang_id' => $item1->id, 'quantity' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['paket_sewa_id' => $bundle->id, 'barang_id' => $item2->id, 'quantity' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
        ]);

        // Hapus item1 (tidak ada pesanan aktif)
        // Catatan: paket_sewa_items foreign key ke barang restrict pada database asli jika ada constraint,
        // observer deleting hook berjalan sebelum delete query
        $item1->delete();

        $this->assertEquals('tidak_aktif', $bundle->fresh()->status);
        Event::assertDispatched(VendorBundleAlertEvent::class);
    }

    /**
     * Requirement 1.9, 3.2:
     * Updating deposit or denda_per_hari on a master item automatically recalculates aggregate deposit
     * and daily fines on related bundles.
     */
    public function test_updating_deposit_or_denda_recalculates_aggregate_rates_on_bundle(): void
    {
        $item1 = $this->createBarang([
            'deposit'        => 100000.00,
            'denda_per_hari' => 15000.00,
        ]);
        $item2 = $this->createBarang([
            'deposit'        => 50000.00,
            'denda_per_hari' => 10000.00,
        ]);

        // 1. PaketSewa bundle
        $bundle = $this->createPaketSewa();
        DB::table('paket_sewa_items')->insert([
            ['paket_sewa_id' => $bundle->id, 'barang_id' => $item1->id, 'quantity' => 2, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['paket_sewa_id' => $bundle->id, 'barang_id' => $item2->id, 'quantity' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
        ]);

        // 2. Legacy bundle Barang (is_paket = 1)
        $legacyBundleId = DB::table('barang')->insertGetId([
            'vendor_id'         => $this->vendor->id,
            'nama'              => 'Legacy Bundle Kamera',
            'slug'              => 'legacy-bundle-' . Str::random(8),
            'harga_sewa_harian' => 150000.00,
            'deposit'           => 250000.00,
            'denda_per_hari'    => 40000.00,
            'is_paket'          => 1,
            'stok_total'        => 5,
            'status'            => 'tersedia',
            'is_approved'       => 1,
            'created_at'        => Carbon::now(),
            'updated_at'        => Carbon::now(),
        ]);
        $legacyBundle = Barang::find($legacyBundleId);

        DB::table('paket_sewa_items')->insert([
            ['paket_id' => $legacyBundle->id, 'barang_id' => $item1->id, 'quantity' => 2, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['paket_id' => $legacyBundle->id, 'barang_id' => $item2->id, 'quantity' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
        ]);

        // Initial check: 2 * 100k + 1 * 50k = 250k deposit; 2 * 15k + 1 * 10k = 40k denda
        $this->assertEquals(250000.00, $bundle->deposit);
        $this->assertEquals(40000.00, $bundle->denda_per_hari);

        // Update item 1: deposit naik ke 200k, denda naik ke 25k
        $item1->update([
            'deposit'        => 200000.00,
            'denda_per_hari' => 25000.00,
        ]);

        // Verifikasi pada PaketSewa: 2 * 200k + 1 * 50k = 450k deposit; 2 * 25k + 1 * 10k = 60k denda
        $this->assertEquals(450000.00, $bundle->fresh()->deposit);
        $this->assertEquals(60000.00, $bundle->fresh()->denda_per_hari);

        // Verifikasi pada Legacy Barang Bundle: kolom deposit & denda_per_hari otomatis ter-update di database
        $this->assertEquals(450000.00, (float) $legacyBundle->fresh()->deposit);
        $this->assertEquals(60000.00, (float) $legacyBundle->fresh()->denda_per_hari);
    }

    /**
     * Requirement 3.3, 4.9:
     * Attempting to delete a master item referenced in active bundle orders
     * ('Menunggu Pembayaran', 'Disetujui', 'Sedang Disewa') is blocked with HTTP 422.
     */
    #[DataProvider('activeOrderStatusProvider')]
    public function test_attempting_to_delete_master_item_with_active_bundle_orders_is_blocked_with_422(string $activeStatus): void
    {
        $item = $this->createBarang();

        // 1. Buat pesanan aktif
        $orderId = $this->createOrder(['status' => $activeStatus]);

        // 2. Buat line item order
        $orderItemId = DB::table('order_items')->insertGetId([
            'order_id'     => $orderId,
            'product_name' => 'Bundle Kamera A',
            'price'        => 150000.00,
            'quantity'     => 1,
            'item_type'    => 'paket',
            'created_at'   => Carbon::now(),
            'updated_at'   => Carbon::now(),
        ]);

        // 3. Buat order_item_components mereferensikan master item
        $this->createOrderItemComponent($orderItemId, $item->id);

        // 4. Mencoba menghapus master item harus gagal dengan HTTP 422
        $caught = false;
        try {
            $item->delete();
        } catch (HttpException $e) {
            $caught = true;
            $this->assertEquals(422, $e->getStatusCode());
        }

        $this->assertTrue($caught, "Expected HTTP 422 exception when deleting item with order status '{$activeStatus}'.");

        // Verifikasi item masih tetap ada di database
        $this->assertDatabaseHas('barang', ['id' => $item->id]);
    }

    public static function activeOrderStatusProvider(): array
    {
        return [
            ['Menunggu Pembayaran'],
            ['Disetujui'],
            ['Sedang Disewa'],
        ];
    }

    /**
     * Requirement 3.3:
     * Master item without active orders can be safely deleted.
     */
    public function test_master_item_without_active_orders_can_be_deleted(): void
    {
        $item = $this->createBarang();

        // Penghapusan harus berhasil tanpa exception
        $deleted = $item->delete();

        $this->assertTrue($deleted);
        $this->assertDatabaseMissing('barang', ['id' => $item->id]);
    }
}
