<?php

namespace Tests\Feature;

use App\Models\Barang;
use App\Models\Kategori;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class CustomerCategoryPageTest extends TestCase
{
    use DatabaseTransactions;

    protected User $customer;
    protected User $vendor;
    protected Kategori $kategori;
    protected Barang $barang;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customer = User::create([
            'name' => 'Test Customer',
            'email' => 'customer_' . uniqid() . '@rentify.test',
            'password' => bcrypt('password123'),
            'role' => 'customer',
            'is_active' => 1,
        ]);
        $this->customer->markEmailAsVerified();

        $this->vendor = User::create([
            'name' => 'Vendor Test',
            'vendor_name' => 'Toko Kamera Sukses',
            'email' => 'vendor_' . uniqid() . '@rentify.test',
            'password' => bcrypt('password123'),
            'role' => 'vendor',
            'is_active' => 1,
        ]);
        $this->vendor->markEmailAsVerified();

        $this->kategori = Kategori::firstOrCreate(
            ['nama' => 'Kamera & Elektronik Test'],
            ['is_active' => 1]
        );

        $this->barang = Barang::create([
            'vendor_id' => $this->vendor->id,
            'kategori_id' => $this->kategori->id,
            'nama' => 'Sony A7IV Mirrorless Pro',
            'slug' => 'sony-a7iv-mirrorless-pro-' . uniqid(),
            'deskripsi' => 'Kamera profesional siap pakai untuk wedding & video.',
            'harga_sewa_harian' => 250000,
            'status_barang' => 'disetujui',
            'stok' => 2,
        ]);
    }

    public function test_home_page_renders_category_filter_buttons_or_links(): void
    {
        $response = $this->actingAs($this->customer)->get('/');

        $response->assertStatus(200);
        $response->assertViewIs('customer.home');

        // Memastikan kategori terender di Beranda baik sebagai filter button data-cat-id atau link
        $content = (string) $response->getContent();
        $hasButton = str_contains($content, 'data-cat-id="' . $this->kategori->id . '"') || str_contains($content, 'toggleCategoryFilter(\'' . $this->kategori->id . '\'');
        $hasLink = str_contains($content, route('customer.kategori', $this->kategori->id));

        $this->assertTrue($hasButton || $hasLink, 'Category filter button or link should be present on home page');
    }

    public function test_category_page_renders_dedicated_view_with_items(): void
    {
        $response = $this->actingAs($this->customer)->get(route('customer.kategori', $this->kategori->id));

        $response->assertStatus(200);
        $response->assertViewIs('customer.kategori');
        $response->assertSee($this->kategori->nama);
        $response->assertSee($this->barang->nama);
        $response->assertSee('Toko Kamera Sukses');
        $response->assertSee('Pindah Kategori');
        $response->assertSee('Urutkan:');
    }

    public function test_category_page_supports_slug_or_name_resolution(): void
    {
        $response = $this->actingAs($this->customer)->get('/kategori/kamera');

        $response->assertStatus(200);
        $response->assertViewIs('customer.kategori');
        $response->assertSee($this->barang->nama);
    }

    public function test_category_page_alias_route_works(): void
    {
        $response = $this->actingAs($this->customer)->get('/customer/kategori/' . $this->kategori->id);

        $response->assertStatus(200);
        $response->assertViewIs('customer.kategori');
        $response->assertSee($this->barang->nama);
    }

    public function test_category_sorting_by_price_works(): void
    {
        $cheaperBarang = Barang::create([
            'vendor_id' => $this->vendor->id,
            'kategori_id' => $this->kategori->id,
            'nama' => 'Baterai Sony Murah',
            'slug' => 'baterai-sony-murah-' . uniqid(),
            'deskripsi' => 'Baterai cadangan.',
            'harga_sewa_harian' => 50000,
            'status_barang' => 'disetujui',
            'stok' => 5,
        ]);

        $responseAsc = $this->actingAs($this->customer)->get(route('customer.kategori', [
            'id' => $this->kategori->id,
            'sort' => 'harga_asc',
        ]));
        $responseAsc->assertStatus(200);
        $responseAsc->assertSeeInOrder([$cheaperBarang->nama, $this->barang->nama]);

        $responseDesc = $this->actingAs($this->customer)->get(route('customer.kategori', [
            'id' => $this->kategori->id,
            'sort' => 'harga_desc',
        ]));
        $responseDesc->assertStatus(200);
        $responseDesc->assertSeeInOrder([$this->barang->nama, $cheaperBarang->nama]);
    }

    public function test_empty_category_renders_friendly_empty_state(): void
    {
        $emptyKategori = Kategori::create([
            'nama' => 'Kategori Kosong Unik',
            'is_active' => 1,
        ]);

        $response = $this->actingAs($this->customer)->get(route('customer.kategori', $emptyKategori->id));

        $response->assertStatus(200);
        $response->assertViewIs('customer.kategori');
        $response->assertSee('Belum Ada Barang di Kategori Ini');
        $response->assertSee('Kembali ke Beranda');
    }
}
