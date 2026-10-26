<?php

namespace Tests\Feature;

use App\Models\Barang;
use App\Models\Kategori;
use App\Models\Moment;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class MomentFrontendFeedTest extends TestCase
{
    use DatabaseTransactions;

    protected User $customer;
    protected Barang $barang;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customer = User::create([
            'name'     => 'Frontend Test Customer',
            'email'    => 'front_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role'     => 'customer',
        ]);

        $vendor = User::create([
            'name'        => 'Vendor Frontend',
            'email'       => 'vendor_front_' . uniqid() . '@example.com',
            'password'    => bcrypt('password'),
            'role'        => 'vendor',
            'vendor_name' => 'Toko Alat Outdoor',
        ]);

        $kategori = Kategori::create([
            'nama'      => 'Outdoor Gear',
            'slug'      => 'outdoor-gear-' . uniqid(),
            'is_active' => 1,
        ]);

        $this->barang = Barang::create([
            'vendor_id'         => $vendor->id,
            'kategori_id'       => $kategori->id,
            'nama'              => 'Carrier Deuter 65L',
            'slug'              => 'carrier-deuter-65l-' . uniqid(),
            'deskripsi'         => 'Tas gunung kapasitas besar',
            'harga_sewa_harian' => 35000,
            'deposit'           => 100000,
            'denda_per_hari'    => 15000,
            'stok_total'        => 5,
            'status'            => 'tersedia',
            'is_approved'       => 1,
        ]);
    }

    public function test_moments_feed_page_loads_successfully_for_guest(): void
    {
        $response = $this->get('/moments');

        $response->assertStatus(200);
        $response->assertSee('Rentify Moments', false);
        $response->assertSee('id="feed-container"', false);
        $response->assertSee('id="comments-drawer"', false);
        $response->assertSee('id="guest-auth-modal"', false);
        $response->assertSee('id="upload-modal"', false);
        $response->assertSee('id="report-modal"', false);
        $response->assertSee('heic2any', false);
    }

    public function test_moments_feed_page_loads_for_authenticated_user_with_products(): void
    {
        $response = $this->actingAs($this->customer)->get('/moments');

        $response->assertStatus(200);
        $response->assertSee('Carrier Deuter 65L', false);
        $response->assertSee('heic-converting-status', false);
    }

    public function test_api_feed_endpoint_serves_data_for_frontend(): void
    {
        $moment = Moment::create([
            'user_id'       => $this->customer->id,
            'media_url'     => 'https://res.cloudinary.com/rentify/image/upload/v1/test_fe.jpg',
            'caption'       => 'Momen seru camping akhir pekan',
            'status'        => 'active',
            'likes_count'   => 3,
            'ranking_score' => 1.25,
        ]);

        $response = $this->getJson('/api/moments');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertIsArray($data);

        $matching = collect($data)->firstWhere('id', $moment->id);
        $this->assertNotNull($matching);
        $this->assertEquals('Momen seru camping akhir pekan', $matching['caption']);
        $this->assertArrayHasKey('is_liked', $matching);
        $this->assertArrayHasKey('is_bookmarked', $matching);
        $this->assertArrayHasKey('tagged_products', $matching);
    }
}
