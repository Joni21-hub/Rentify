<?php

namespace Tests\Unit;

use App\Models\Barang;
use App\Models\Cabang;
use App\Models\PaketSewa;
use App\Models\PaketSewaItem;
use App\Models\User;
use App\Services\BundleInventoryService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class BundleInventoryServiceTest extends TestCase
{
    protected BundleInventoryService $inventoryService;
    protected User $vendor;
    protected Cabang $cabang;

    protected function setUp(): void
    {
        parent::setUp();
        $this->inventoryService = new BundleInventoryService();

        $vendorId = DB::table('users')->insertGetId([
            'name'       => 'Vendor Test Inventory',
            'email'      => 'vendor_inv_' . Str::random(8) . '@example.com',
            'password'   => Hash::make('password'),
            'role'       => 'vendor',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
        $this->vendor = User::find($vendorId);

        $cabangId = DB::table('cabangs')->insertGetId([
            'vendor_id'      => $this->vendor->id,
            'nama_cabang'    => 'Cabang Inventory Test',
            'alamat_lengkap' => 'Jl. Uji Gudang No. 12',
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
            'nama'                  => 'Barang Test ' . Str::random(5),
            'slug'                  => 'barang-test-' . Str::random(8),
            'harga_sewa_harian'     => 50000.00,
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
            'nama'                    => 'Paket Test ' . Str::random(5),
            'slug'                    => 'paket-test-' . Str::random(8),
            'harga_sewa_harian_paket' => 120000.00,
            'status'                  => 'tersedia',
            'is_delivery_supported'   => 1,
            'created_at'              => Carbon::now(),
            'updated_at'              => Carbon::now(),
        ], $attributes));

        return PaketSewa::find($id);
    }

    /**
     * Requirement 1.6, 4.1:
     * Test calculateMaxStock() across varied inventory bottlenecks.
     */
    public function test_calculate_max_stock_with_varied_inventory_bottlenecks(): void
    {
        $paket = $this->createPaketSewa();

        // Item A: stok 10, butuh 1 => kapasitas 10
        $itemA = $this->createBarang(['stok_total' => 10]);
        // Item B: stok 6, butuh 2 => kapasitas 3 (bottleneck)
        $itemB = $this->createBarang(['stok_total' => 6]);
        // Item C: stok 20, butuh 4 => kapasitas 5
        $itemC = $this->createBarang(['stok_total' => 20]);

        DB::table('paket_sewa_items')->insert([
            ['paket_sewa_id' => $paket->id, 'barang_id' => $itemA->id, 'quantity' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['paket_sewa_id' => $paket->id, 'barang_id' => $itemB->id, 'quantity' => 2, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['paket_sewa_id' => $paket->id, 'barang_id' => $itemC->id, 'quantity' => 4, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
        ]);

        $maxStock = $this->inventoryService->calculateMaxStock($paket->fresh());

        $this->assertSame(3, $maxStock);
    }

    /**
     * Requirement 1.6, 4.1:
     * Verify that fractional stock yields integer floor results (e.g. stock 5 with requirement 2 yields 2 units, not 2.5).
     */
    public function test_fractional_stock_yields_integer_floor_results(): void
    {
        $paket = $this->createPaketSewa();

        // Item 1: stok 5, butuh 2 => floor(5/2) = 2
        $item1 = $this->createBarang(['stok_total' => 5]);
        // Item 2: stok 11, butuh 3 => floor(11/3) = 3
        $item2 = $this->createBarang(['stok_total' => 11]);

        DB::table('paket_sewa_items')->insert([
            ['paket_sewa_id' => $paket->id, 'barang_id' => $item1->id, 'quantity' => 2, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['paket_sewa_id' => $paket->id, 'barang_id' => $item2->id, 'quantity' => 3, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
        ]);

        $maxStock = $this->inventoryService->calculateMaxStock($paket->fresh());

        $this->assertSame(2, $maxStock);
        $this->assertIsInt($maxStock);
    }

    /**
     * Requirement 1.6, 4.1:
     * When stock is insufficient (e.g. stock 1 with requirement 2), max stock is 0.
     */
    public function test_insufficient_stock_yields_zero(): void
    {
        $paket = $this->createPaketSewa();

        $item1 = $this->createBarang(['stok_total' => 1]);
        $item2 = $this->createBarang(['stok_total' => 10]);

        DB::table('paket_sewa_items')->insert([
            ['paket_sewa_id' => $paket->id, 'barang_id' => $item1->id, 'quantity' => 2, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['paket_sewa_id' => $paket->id, 'barang_id' => $item2->id, 'quantity' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
        ]);

        $maxStock = $this->inventoryService->calculateMaxStock($paket->fresh());

        $this->assertSame(0, $maxStock);
    }

    /**
     * Requirement 1.6:
     * An empty bundle without items yields 0 stock.
     */
    public function test_empty_bundle_yields_zero_stock(): void
    {
        $paket = $this->createPaketSewa();

        $maxStock = $this->inventoryService->calculateMaxStock($paket->fresh());

        $this->assertSame(0, $maxStock);
    }

    /**
     * Requirement 1.6, 3.1:
     * When constituent item is inactive or unapproved, bundle stock evaluates to 0.
     */
    public function test_inactive_or_unapproved_constituent_item_yields_zero_stock(): void
    {
        $paket = $this->createPaketSewa();

        $itemA = $this->createBarang(['stok_total' => 10, 'status' => 'tidak_tersedia']);
        $itemB = $this->createBarang(['stok_total' => 10, 'is_approved' => 0]);

        DB::table('paket_sewa_items')->insert([
            ['paket_sewa_id' => $paket->id, 'barang_id' => $itemA->id, 'quantity' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['paket_sewa_id' => $paket->id, 'barang_id' => $itemB->id, 'quantity' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
        ]);

        $maxStock = $this->inventoryService->calculateMaxStock($paket->fresh());

        $this->assertSame(0, $maxStock);
    }

    /**
     * Requirement 4.1:
     * Verify delivery support inheritance: if any constituent item has is_delivery_supported == false,
     * the bundle evaluates to is_delivery_supported == false.
     */
    public function test_delivery_support_inheritance(): void
    {
        $item1 = $this->createBarang(['is_delivery_supported' => 1]);
        $item2 = $this->createBarang(['is_delivery_supported' => 1]);
        $item3 = $this->createBarang(['is_delivery_supported' => 0]); // Not supported

        // All supported
        $this->assertTrue($this->inventoryService->resolveDeliverySupport([$item1->id, $item2->id]));

        // One item not supported -> false
        $this->assertFalse($this->inventoryService->resolveDeliverySupport([$item1->id, $item2->id, $item3->id]));

        // Empty array -> false
        $this->assertFalse($this->inventoryService->resolveDeliverySupport([]));
    }
}
