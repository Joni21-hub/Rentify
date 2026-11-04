<?php

namespace Tests\Feature;

use App\Models\Barang;
use App\Models\Cabang;
use App\Models\PaketSewa;
use App\Models\PaketSewaItem;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Tests\TestCase;

class VendorBundleControllerTest extends TestCase
{
    protected User $vendor;
    protected User $otherVendor;
    protected Cabang $cabang1;
    protected Cabang $cabang2;

    protected function setUp(): void
    {
        parent::setUp();

        $v1Id = DB::table('users')->insertGetId([
            'name'              => 'Vendor Utama',
            'email'             => 'vendor_main_' . Str::random(8) . '@rentify.test',
            'password'          => Hash::make('password'),
            'role'              => 'vendor',
            'vendor_status'     => 'approved',
            'email_verified_at' => Carbon::now(),
            'created_at'        => Carbon::now(),
            'updated_at'        => Carbon::now(),
        ]);
        $this->vendor = User::find($v1Id);

        $v2Id = DB::table('users')->insertGetId([
            'name'              => 'Vendor Lain',
            'email'             => 'vendor_other_' . Str::random(8) . '@rentify.test',
            'password'          => Hash::make('password'),
            'role'              => 'vendor',
            'vendor_status'     => 'approved',
            'email_verified_at' => Carbon::now(),
            'created_at'        => Carbon::now(),
            'updated_at'        => Carbon::now(),
        ]);
        $this->otherVendor = User::find($v2Id);

        $c1Id = DB::table('cabangs')->insertGetId([
            'vendor_id'      => $this->vendor->id,
            'nama_cabang'    => 'Cabang Jakarta Pusat',
            'alamat_lengkap' => 'Jl. Thamrin No. 10',
            'created_at'     => Carbon::now(),
            'updated_at'     => Carbon::now(),
        ]);
        $this->cabang1 = Cabang::find($c1Id);

        $c2Id = DB::table('cabangs')->insertGetId([
            'vendor_id'      => $this->vendor->id,
            'nama_cabang'    => 'Cabang Jakarta Selatan',
            'alamat_lengkap' => 'Jl. Sudirman No. 50',
            'created_at'     => Carbon::now(),
            'updated_at'     => Carbon::now(),
        ]);
        $this->cabang2 = Cabang::find($c2Id);
    }

    protected function createItem(array $attributes = []): Barang
    {
        $id = DB::table('barang')->insertGetId(array_merge([
            'vendor_id'             => $this->vendor->id,
            'cabang_id'             => $this->cabang1->id,
            'nama'                  => 'Item Test ' . Str::random(6),
            'slug'                  => 'item-test-' . Str::random(10),
            'harga_sewa_harian'     => 50000.00,
            'deposit'               => 100000.00,
            'denda_per_hari'        => 20000.00,
            'stok_total'            => 10,
            'status'                => 'tersedia',
            'is_approved'           => 1,
            'is_delivery_supported' => 1,
            'alamat'                => 'Jl. Thamrin No. 10',
            'latitude'              => -6.200000,
            'longitude'             => 106.816666,
            'created_at'            => Carbon::now(),
            'updated_at'            => Carbon::now(),
        ], $attributes));

        return Barang::find($id);
    }

    /**
     * Requirement 1.1, 1.6, 1.7:
     * Vendor can create bundle successfully with valid items.
     */
    public function test_vendor_can_create_bundle_successfully_with_valid_items(): void
    {
        $item1 = $this->createItem(['nama' => 'Kamera DSLR Canon']);
        $item2 = $this->createItem(['nama' => 'Lensa 50mm']);

        $payload = [
            'cabang_id'               => $this->cabang1->id,
            'nama'                    => 'Paket Hemat Hunting Foto',
            'deskripsi'               => 'Deskripsi paket hemat foto',
            'harga_sewa_harian_paket' => 90000.00,
            'items'                   => [
                ['barang_id' => $item1->id, 'quantity' => 1],
                ['barang_id' => $item2->id, 'quantity' => 1],
            ],
        ];

        $response = $this->actingAs($this->vendor)
            ->postJson(URL::route('vendor.bundle.store'), $payload);

        $response->assertStatus(201);
        $this->assertDatabaseHas('paket_sewa', [
            'vendor_id'               => $this->vendor->id,
            'cabang_id'               => $this->cabang1->id,
            'nama'                    => 'Paket Hemat Hunting Foto',
            'harga_sewa_harian_paket' => 90000.00,
            'status'                  => 'tersedia',
            'is_delivery_supported'   => 1,
        ]);

        $createdBundleId = $response->json('data.id');
        $this->assertNotNull($createdBundleId);
        $this->assertDatabaseHas('paket_sewa_items', [
            'paket_sewa_id' => $createdBundleId,
            'barang_id'     => $item1->id,
            'quantity'      => 1,
        ]);
        $this->assertDatabaseHas('paket_sewa_items', [
            'paket_sewa_id' => $createdBundleId,
            'barang_id'     => $item2->id,
            'quantity'      => 1,
        ]);
    }

    /**
     * Requirement 1.2:
     * Validation rejects items not owned by the authenticated vendor.
     */
    public function test_validation_rejects_items_not_owned_by_vendor(): void
    {
        $myProduct = $this->createItem();
        $foreignProduct = $this->createItem(['vendor_id' => $this->otherVendor->id]);

        $payload = [
            'cabang_id'               => $this->cabang1->id,
            'nama'                    => 'Paket Ilegal Vendor Lain',
            'harga_sewa_harian_paket' => 100000.00,
            'items'                   => [
                ['barang_id' => $myProduct->id, 'quantity' => 1],
                ['barang_id' => $foreignProduct->id, 'quantity' => 1],
            ],
        ];

        $response = $this->actingAs($this->vendor)
            ->postJson(URL::route('vendor.bundle.store'), $payload);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('items');
    }

    /**
     * Requirement 1.2:
     * Validation rejects unapproved items (is_approved == 0).
     */
    public function test_validation_rejects_unapproved_items(): void
    {
        $item1 = $this->createItem(['is_approved' => 1]);
        $unapprovedItem = $this->createItem(['is_approved' => 0]);

        $payload = [
            'cabang_id'               => $this->cabang1->id,
            'nama'                    => 'Paket Ada Item Belum Approved',
            'harga_sewa_harian_paket' => 100000.00,
            'items'                   => [
                ['barang_id' => $item1->id, 'quantity' => 1],
                ['barang_id' => $unapprovedItem->id, 'quantity' => 1],
            ],
        ];

        $response = $this->actingAs($this->vendor)
            ->postJson(URL::route('vendor.bundle.store'), $payload);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('items');
    }

    /**
     * Requirement 1.2:
     * Validation rejects inactive items (status != 'tersedia').
     */
    public function test_validation_rejects_inactive_items(): void
    {
        $item1 = $this->createItem(['status' => 'tersedia']);
        $inactiveItem = $this->createItem(['status' => 'tidak_tersedia']);

        $payload = [
            'cabang_id'               => $this->cabang1->id,
            'nama'                    => 'Paket Ada Item Tidak Aktif',
            'harga_sewa_harian_paket' => 100000.00,
            'items'                   => [
                ['barang_id' => $item1->id, 'quantity' => 1],
                ['barang_id' => $inactiveItem->id, 'quantity' => 1],
            ],
        ];

        $response = $this->actingAs($this->vendor)
            ->postJson(URL::route('vendor.bundle.store'), $payload);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('items');
    }

    /**
     * Requirement 1.3:
     * Validation rejects bundles with fewer than 2 distinct items.
     */
    public function test_validation_rejects_bundles_with_fewer_than_two_distinct_items(): void
    {
        $item1 = $this->createItem();

        $payload = [
            'cabang_id'               => $this->cabang1->id,
            'nama'                    => 'Paket Cuma Satu Item',
            'harga_sewa_harian_paket' => 50000.00,
            'items'                   => [
                ['barang_id' => $item1->id, 'quantity' => 1],
            ],
        ];

        $response = $this->actingAs($this->vendor)
            ->postJson(URL::route('vendor.bundle.store'), $payload);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('items');
    }

    /**
     * Requirement 1.3:
     * Validation rejects duplicate item IDs in bundle composition.
     */
    public function test_validation_rejects_duplicate_item_ids(): void
    {
        $item1 = $this->createItem();

        $payload = [
            'cabang_id'               => $this->cabang1->id,
            'nama'                    => 'Paket Duplikat Item',
            'harga_sewa_harian_paket' => 100000.00,
            'items'                   => [
                ['barang_id' => $item1->id, 'quantity' => 1],
                ['barang_id' => $item1->id, 'quantity' => 2],
            ],
        ];

        $response = $this->actingAs($this->vendor)
            ->postJson(URL::route('vendor.bundle.store'), $payload);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('items');
    }

    /**
     * Requirement 1.4:
     * Validation rejects items from differing physical branches (barang.cabang_id !== paket_sewa.cabang_id).
     */
    public function test_validation_rejects_items_from_differing_physical_branches(): void
    {
        $itemBranch1 = $this->createItem(['cabang_id' => $this->cabang1->id]);
        $itemBranch2 = $this->createItem(['cabang_id' => $this->cabang2->id]);

        $payload = [
            'cabang_id'               => $this->cabang1->id,
            'nama'                    => 'Paket Beda Cabang',
            'harga_sewa_harian_paket' => 100000.00,
            'items'                   => [
                ['barang_id' => $itemBranch1->id, 'quantity' => 1],
                ['barang_id' => $itemBranch2->id, 'quantity' => 1],
            ],
        ];

        $response = $this->actingAs($this->vendor)
            ->postJson(URL::route('vendor.bundle.store'), $payload);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('items');
    }

    /**
     * Requirement 1.5:
     * Validation rejects items with mismatched pickup GPS coordinates or addresses.
     */
    public function test_validation_rejects_items_with_mismatched_pickup_gps_coordinates(): void
    {
        $itemA = $this->createItem([
            'alamat'    => 'Jl. Thamrin No. 10',
            'latitude'  => -6.200000,
            'longitude' => 106.816666,
        ]);
        $itemB = $this->createItem([
            'alamat'    => 'Jl. Gatsu No. 99',
            'latitude'  => -6.230000,
            'longitude' => 106.820000,
        ]);

        $payload = [
            'cabang_id'               => $this->cabang1->id,
            'nama'                    => 'Paket Mismatched GPS',
            'harga_sewa_harian_paket' => 100000.00,
            'items'                   => [
                ['barang_id' => $itemA->id, 'quantity' => 1],
                ['barang_id' => $itemB->id, 'quantity' => 1],
            ],
        ];

        $response = $this->actingAs($this->vendor)
            ->postJson(URL::route('vendor.bundle.store'), $payload);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('items');
    }

    /**
     * Requirement 4.1:
     * Delivery support inheritance: if any constituent item has is_delivery_supported == false,
     * the bundle evaluates to is_delivery_supported == false.
     */
    public function test_delivery_support_inheritance_in_controller(): void
    {
        $item1 = $this->createItem(['is_delivery_supported' => 1]);
        $item2 = $this->createItem(['is_delivery_supported' => 0]); // Pickup only

        $payload = [
            'cabang_id'               => $this->cabang1->id,
            'nama'                    => 'Paket Tanpa Delivery',
            'harga_sewa_harian_paket' => 120000.00,
            'items'                   => [
                ['barang_id' => $item1->id, 'quantity' => 1],
                ['barang_id' => $item2->id, 'quantity' => 1],
            ],
        ];

        $response = $this->actingAs($this->vendor)
            ->postJson(URL::route('vendor.bundle.store'), $payload);

        $response->assertStatus(201);
        $this->assertDatabaseHas('paket_sewa', [
            'nama'                  => 'Paket Tanpa Delivery',
            'is_delivery_supported' => 0,
        ]);
    }

    /**
     * Requirement 1.7:
     * Bundle toggle status deactivates marketplace visibility without modifying constituent items.
     */
    public function test_bundle_toggle_status_deactivates_visibility_without_modifying_constituent_items(): void
    {
        $item1 = $this->createItem(['status' => 'tersedia']);
        $item2 = $this->createItem(['status' => 'tersedia']);

        $paketId = DB::table('paket_sewa')->insertGetId([
            'vendor_id'               => $this->vendor->id,
            'cabang_id'               => $this->cabang1->id,
            'nama'                    => 'Paket Siap Toggle',
            'slug'                    => 'paket-siap-toggle-' . Str::random(6),
            'harga_sewa_harian_paket' => 150000.00,
            'status'                  => 'tersedia',
            'is_delivery_supported'   => 1,
            'created_at'              => Carbon::now(),
            'updated_at'              => Carbon::now(),
        ]);

        DB::table('paket_sewa_items')->insert([
            ['paket_sewa_id' => $paketId, 'barang_id' => $item1->id, 'quantity' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['paket_sewa_id' => $paketId, 'barang_id' => $item2->id, 'quantity' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
        ]);

        // 1. Toggle status to tidak_aktif
        $response = $this->actingAs($this->vendor)
            ->patchJson(URL::route('vendor.bundle.toggle', $paketId));

        $response->assertOk();
        $this->assertDatabaseHas('paket_sewa', [
            'id'     => $paketId,
            'status' => 'tidak_aktif',
        ]);

        // Verifikasi bahwa status barang konstituen TIDAK berubah
        $this->assertEquals('tersedia', $item1->fresh()->status);
        $this->assertEquals('tersedia', $item2->fresh()->status);

        // 2. Toggle status kembali ke tersedia
        $response2 = $this->actingAs($this->vendor)
            ->patchJson(URL::route('vendor.bundle.toggle', $paketId));

        $response2->assertOk();
        $this->assertDatabaseHas('paket_sewa', [
            'id'     => $paketId,
            'status' => 'tersedia',
        ]);
        $this->assertEquals('tersedia', $item1->fresh()->status);
        $this->assertEquals('tersedia', $item2->fresh()->status);
    }

    /**
     * Requirement 1.1, 1.7:
     * Vendor can update bundle details and items.
     */
    public function test_vendor_can_update_bundle(): void
    {
        $item1 = $this->createItem();
        $item2 = $this->createItem();
        $item3 = $this->createItem();

        $paketId = DB::table('paket_sewa')->insertGetId([
            'vendor_id'               => $this->vendor->id,
            'cabang_id'               => $this->cabang1->id,
            'nama'                    => 'Paket Awal',
            'slug'                    => 'paket-awal-' . Str::random(6),
            'harga_sewa_harian_paket' => 100000.00,
            'status'                  => 'tersedia',
            'is_delivery_supported'   => 1,
            'created_at'              => Carbon::now(),
            'updated_at'              => Carbon::now(),
        ]);

        DB::table('paket_sewa_items')->insert([
            ['paket_sewa_id' => $paketId, 'barang_id' => $item1->id, 'quantity' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['paket_sewa_id' => $paketId, 'barang_id' => $item2->id, 'quantity' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
        ]);

        $updatePayload = [
            'cabang_id'               => $this->cabang1->id,
            'nama'                    => 'Paket Terupdate',
            'harga_sewa_harian_paket' => 140000.00,
            'items'                   => [
                ['barang_id' => $item1->id, 'quantity' => 2],
                ['barang_id' => $item3->id, 'quantity' => 1],
            ],
        ];

        $response = $this->actingAs($this->vendor)
            ->putJson(URL::route('vendor.bundle.update', $paketId), $updatePayload);

        $response->assertOk();
        $this->assertDatabaseHas('paket_sewa', [
            'id'                      => $paketId,
            'nama'                    => 'Paket Terupdate',
            'harga_sewa_harian_paket' => 140000.00,
        ]);
        $this->assertDatabaseMissing('paket_sewa_items', [
            'paket_sewa_id' => $paketId,
            'barang_id'     => $item2->id,
        ]);
        $this->assertDatabaseHas('paket_sewa_items', [
            'paket_sewa_id' => $paketId,
            'barang_id'     => $item3->id,
            'quantity'      => 1,
        ]);
    }
}
