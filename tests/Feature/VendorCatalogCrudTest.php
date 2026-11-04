<?php

namespace Tests\Feature;

use App\Models\Barang;
use App\Models\Kategori;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class VendorCatalogCrudTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        config(['filesystems.default' => 'public']);
    }

    public function test_public_catalog_browsing_search_and_filtering(): void
    {
        $responseHome = $this->get('/');
        $responseHome->assertStatus(200);

        $responseSearch = $this->get('/search?q=tenda');
        $responseSearch->assertStatus(200);
    }

    public function test_vendor_can_create_update_and_delete_rental_item_on_barang_table(): void
    {
        $vendor = User::create([
            'name' => 'Vendor Katalog Test',
            'email' => 'vendor_katalog_' . uniqid() . '@example.com',
            'password' => bcrypt('secret123'),
            'role' => 'vendor',
            'is_active' => 1,
        ]);
        $vendor->markEmailAsVerified();

        $kategori = Kategori::create([
            'nama' => 'Outdoor Camping',
            'slug' => 'outdoor-camping-' . uniqid(),
            'is_active' => 1,
        ]);

        $coverPhoto = UploadedFile::fake()->image('tenda_dome.jpg', 640, 480);

        // CREATE
        $responseCreate = $this->actingAs($vendor)->post(route('vendor.barang.store'), [
            'kategori_id' => $kategori->id,
            'nama' => 'Tenda Dome 4 Orang',
            'deskripsi' => 'Tenda anti badai waterproof',
            'harga_sewa_harian' => 75000,
            'deposit' => 100000,
            'denda_per_hari' => 25000,
            'kondisi' => 'Bagus',
            'stok_total' => 5,
            'alamat' => 'Jl. Merdeka No 45',
            'latitude' => '-6.1754',
            'longitude' => '106.8272',
            'fotos' => [$coverPhoto],
        ]);

        $responseCreate->assertRedirect(route('vendor.barang.index'));

        // Assert data exists in singular 'barang' table
        $barang = DB::table('barang')->where('nama', 'Tenda Dome 4 Orang')->first();
        $this->assertNotNull($barang, 'Item must be created in `barang` table');
        $this->assertEquals(75000, (float)$barang->harga_sewa_harian);
        $this->assertEquals($vendor->id, $barang->vendor_id);

        // UPDATE
        $responseUpdate = $this->actingAs($vendor)->put(route('vendor.barang.update', $barang->id), [
            'kategori_id' => $kategori->id,
            'nama' => 'Tenda Dome 4 Orang (Updated)',
            'deskripsi' => 'Deskripsi diperbarui',
            'harga_sewa_harian' => 85000,
            'deposit' => 100000,
            'denda_per_hari' => 25000,
            'kondisi' => 'Sangat Bagus',
            'stok_total' => 6,
            'alamat' => 'Jl. Merdeka No 45',
            'latitude' => '-6.1754',
            'longitude' => '106.8272',
        ]);

        $responseUpdate->assertRedirect(route('vendor.barang.index'));

        $updatedBarang = DB::table('barang')->where('id', $barang->id)->first();
        $this->assertEquals('Tenda Dome 4 Orang (Updated)', $updatedBarang->nama);
        $this->assertEquals(85000, (float)$updatedBarang->harga_sewa_harian);
        $this->assertEquals(6, $updatedBarang->stok_total);

        // DELETE
        $responseDelete = $this->actingAs($vendor)->delete(route('vendor.barang.destroy', $barang->id));
        $responseDelete->assertRedirect(route('vendor.barang.index'));

        $deletedBarang = DB::table('barang')->where('id', $barang->id)->first();
        $this->assertNull($deletedBarang, 'Item must be removed from `barang` table after destroy');
    }

    public function test_vendor_can_browse_katalog_and_manage_stock_and_bundles(): void
    {
        $vendor = User::create([
            'name' => 'Vendor Paket Test',
            'email' => 'vendor_paket_' . uniqid() . '@example.com',
            'password' => bcrypt('secret123'),
            'role' => 'vendor',
            'is_active' => 1,
        ]);
        $vendor->markEmailAsVerified();

        $kategori = Kategori::create([
            'nama' => 'Elektronik Audio',
            'slug' => 'elektronik-audio-' . uniqid(),
            'is_active' => 1,
        ]);

        $itemA = Barang::create([
            'vendor_id' => $vendor->id,
            'kategori_id' => $kategori->id,
            'nama' => 'Speaker Portable',
            'slug' => 'speaker-portable-' . uniqid(),
            'deskripsi' => 'Speaker bluetooth',
            'harga_sewa_harian' => 50000,
            'stok_total' => 10,
            'status' => 'tersedia',
            'status_barang' => 'disetujui',
            'is_approved' => 1,
            'is_paket' => 0,
        ]);

        $paket = Barang::create([
            'vendor_id' => $vendor->id,
            'kategori_id' => $kategori->id,
            'nama' => 'Paket Acara Ultah',
            'slug' => 'paket-acara-ultah-' . uniqid(),
            'deskripsi' => 'Paket sound system lengkap',
            'harga_sewa_harian' => 120000,
            'stok_total' => 0,
            'status' => 'tersedia',
            'status_barang' => 'disetujui',
            'is_approved' => 1,
            'is_paket' => 1,
        ]);

        \App\Models\PaketSewaItem::create([
            'paket_id' => $paket->id,
            'barang_id' => $itemA->id,
            'quantity' => 2,
        ]);

        // 1. Akses index katalog barang umum
        $resBarang = $this->actingAs($vendor)->get(route('vendor.barang.index'));
        $resBarang->assertStatus(200);
        $resBarang->assertSee('Paket Acara Ultah');
        $resBarang->assertSee('5 paket'); // Stok terhitung: 10 / 2 = 5 paket

        // 2. Akses index katalog terfilter paket sewa
        $resPaketFilter = $this->actingAs($vendor)->get(route('vendor.barang.index', ['tipe' => 'paket']));
        $resPaketFilter->assertStatus(200);
        $resPaketFilter->assertSee('Paket Acara Ultah');

        // 3. Akses route vendor.paket.index (harus redirect ke vendor.barang.index dengan tipe=paket)
        $resPaketIndex = $this->actingAs($vendor)->get(route('vendor.paket.index'));
        $resPaketIndex->assertRedirect(route('vendor.barang.index', ['tipe' => 'paket']));

        // 4. Akses halaman kelola stok vendor
        $resStok = $this->actingAs($vendor)->get(route('vendor.stok'));
        $resStok->assertStatus(200);
        $resStok->assertSee('Kelola Stok Unit Rental');
        $resStok->assertSee('Speaker Portable');
    }
}
