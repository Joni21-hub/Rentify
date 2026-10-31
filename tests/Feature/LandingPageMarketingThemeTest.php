<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class LandingPageMarketingThemeTest extends TestCase
{
    use DatabaseTransactions;

    public function test_landing_page_renders_rentify_master_theme_and_no_products(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertViewIs('customer.welcome');

        // Brand & Hero Narrative Verification (from Marketing Screenshots)
        $response->assertSee('Rentify');
        $response->assertSee('Sewa-menyewa');
        $response->assertSee('antar teman, kini');
        $response->assertSee('kotamu.');
        $response->assertSee('Marketplace sewa-menyewa untuk semua barang');
        $response->assertSee('Barang nganggur jadi penghasilan. Butuh sebentar, tinggal sewa.');
        $response->assertSee('Mulai Menyewa');
        $response->assertSee('Buka Toko');
        $response->assertSee('Lihat-lihat dan mulai cari barang tanpa perlu login.');

        // Radar Card & Floating Discovery
        $response->assertSee('Tersedia di Sekitarmu');
        $response->assertSee('Google Pixel 7');
        $response->assertSee('Fender Acoustasonic');
        $response->assertSee('Sony Alpha A7III');
        $response->assertSee('Gulir untuk melihat perjalanan sebuah gitar');

        // Storytelling: Perjalanan Sebuah Gitar
        $response->assertSee('Begini Rentify bekerja');
        $response->assertSee('Dari kamarmu,');
        $response->assertSee('ke seluruh kota.');
        $response->assertSee('Gitar nganggur di kamar');
        $response->assertSee('Melintasi kotamu');
        $response->assertSee('Uang sewa masuk');
        $response->assertSee('Sekarang giliranmu');

        // Categories: Beyond Camping
        $response->assertSee('Ada di sekitarmu');
        $response->assertSee('Bukan cuma alat camping.');
        $response->assertSee('Semua bisa disewa.');
        $response->assertSee('Musik');
        $response->assertSee('Elektronik');
        $response->assertSee('Kamera');
        $response->assertSee('Outdoor');
        $response->assertSee('Kendaraan');

        // Cara Kerja: Dual Tabs (Penyewa & Pemilik)
        $response->assertSee('Penyewa');
        $response->assertSee('Pemilik');
        $response->assertSee('Cari');
        $response->assertSee('Bayar aman');
        $response->assertSee('Ambil & kembalikan', false);

        // Rasa Aman: 3 Protection Pillars
        $response->assertSee('Rasa Aman');
        $response->assertSee('Sewa ke orang baru,');
        $response->assertSee('tetap tenang.');
        $response->assertSee('Uang ditahan aman (escrow)');
        $response->assertSee('Identitas terverifikasi');
        $response->assertSee('Foto serah terima');

        // Verification: NO Product Feed or Catalog Grids on Marketing Homepage
        $response->assertDontSee('Barang Pilihan Siap Sewa Hari Ini');
        $response->assertDontSee('Contoh Barang Siap Sewa');
    }

    public function test_landing_page_preview_flag_accessible_by_authenticated_user(): void
    {
        $customer = User::create([
            'name' => 'Sarah Renter',
            'email' => 'sarah_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'customer',
            'is_active' => 1,
        ]);
        $customer->markEmailAsVerified();

        // Direct / goes to customer.home catalog
        $homeResponse = $this->actingAs($customer)->get('/');
        $homeResponse->assertStatus(200);
        $homeResponse->assertViewIs('customer.home');

        // /?landing=1 previews the marketing landing page
        $landingResponse = $this->actingAs($customer)->get('/?landing=1');
        $landingResponse->assertStatus(200);
        $landingResponse->assertViewIs('customer.welcome');
        $landingResponse->assertSee('Katalog Belanja');
    }
}
