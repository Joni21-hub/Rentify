<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Moment;
use App\Models\Wishlist;
use App\Models\Barang;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class CustomerProfileHybridViewTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Requirement 1.6 & 6.18: Guest landing state when accessing /account unauthenticated.
     */
    public function test_unauthenticated_guest_accessing_account_route_renders_guest_landing_state(): void
    {
        $response = $this->get('/account');

        $response->assertStatus(200);
        $response->assertSee('guest-account-view', false);
        $response->assertSee('Selamat Datang di Rentify!');
        $response->assertSee('Masuk');
        $response->assertSee('Daftar');
        $response->assertSee('Keuntungan Akun Rentify');
        $response->assertSee('bottom-navigation-bar', false);
    }

    /**
     * Requirement 6.1, 6.2, 6.3, 6.4, 6.9, 6.16, 6.17: Full hybrid customer profile.
     */
    public function test_authenticated_customer_accessing_dashboard_renders_hybrid_profile_elements(): void
    {
        $customer = User::create([
            'name' => 'Budi Santoso',
            'email' => 'budi.santoso@gmail.com',
            'password' => bcrypt('password123'),
            'role' => 'customer',
            'whatsapp' => '081298765432',
            'whatsapp_verified_at' => now(),
        ]);

        // Create 2 test orders in different stages
        $vendor = User::where('role', 'vendor')->first() ?? $customer;
        
        $orderBase = [
            'user_id' => $customer->id,
            'customer_name' => $customer->name,
            'customer_whatsapp' => '081298765432',
            'vendor_id' => $vendor->id,
            'metode_pengambilan' => 'ambil',
            'shipping_method' => 'ambil',
            'shipping_fee' => 0,
            'kode_unik' => 0,
            'tanggal_mulai' => now(),
            'start_rent' => now(),
            'tanggal_selesai' => now()->addDay(),
            'end_rent' => now()->addDay(),
            'duration_days' => 1,
            'jaminan' => 'KTP',
            'payment_method' => 'QRIS',
            'created_at' => now(),
            'updated_at' => now(),
        ];

        DB::table('orders')->insert(array_merge($orderBase, [
            'kode_booking' => 'RNTTEST01',
            'status' => 'Menunggu Pembayaran',
            'total_biaya' => 150000,
        ]));

        DB::table('orders')->insert(array_merge($orderBase, [
            'kode_booking' => 'RNTTEST02',
            'status' => 'Menunggu Konfirmasi Toko',
            'total_biaya' => 250000,
        ]));

        // Create 1 active moment
        Moment::create([
            'user_id' => $customer->id,
            'media_url' => 'https://res.cloudinary.com/rentify/image/upload/v1/test.jpg',
            'media_type' => 'image',
            'caption' => 'Kamera sewa di Bromo!',
            'likes_count' => 5,
            'reports_count' => 0,
            'status' => 'active',
        ]);

        $response = $this->actingAs($customer)->get(route('customer.dashboard'));

        $response->assertStatus(200);

        // 1. Profile Header & Info
        $response->assertSee('Budi Santoso');
        $response->assertDontSee('Penyewa aktif');
        $response->assertSee('Edit Profil');
        $response->assertSee(route('customer.settings.profile'));

        // 2. Three Stats Counters
        $response->assertSee('Momen');
        $response->assertSee('Like');
        $response->assertSee('Koin');
        $response->assertSee(route('customer.wallet.coins'));

        // 3. Shopee-Style Order Status Icons (Tanpa Header Teks)
        $response->assertDontSee('Pesanan Saya');
        $response->assertDontSee('Lihat Riwayat Pesanan');
        $response->assertSee('Belum Bayar');
        $response->assertSee('Konfirmasi');
        $response->assertSee('Berjalan');
        $response->assertSee('Beri Ulasan');
        $response->assertSee(route('customer.pesanan', ['status' => 'belum_bayar']));
        $response->assertSee(route('customer.pesanan', ['status' => 'konfirmasi']));
        $response->assertSee(route('customer.pesanan', ['status' => 'berjalan']));
        $response->assertSee(route('customer.pesanan', ['status' => 'ulasan']));

        // 4. Riwayat Pesanan via Settings Slide-Over Drawer
        $response->assertSee('Riwayat Pesanan');
        $response->assertSee(route('customer.pesanan', ['status' => 'semua']));

        // 4. Segmented Tabs
        $response->assertSee('Momen');
        $response->assertSee('Favorit');
        $response->assertSee('tab-moments', false);
        $response->assertSee('tab-saved', false);

        // 5. Settings Slide-over Drawer
        $response->assertSee('settings-drawer-panel', false);
        $response->assertSee('Titik Lokasi Pengiriman');
        $response->assertSee('Voucher & Promo', false);
        $content = (string) $response->getContent();
        $this->assertTrue(str_contains($content, 'Buka Toko Rental') || str_contains($content, 'Beralih ke Toko Saya') || str_contains($content, 'Beralih ke Mode Toko'));
        $response->assertSee('Pusat Bantuan WhatsApp');
        $response->assertSee('Keluar Akun');
    }

    public function test_authenticated_customer_with_wishlist_accessing_account_route(): void
    {
        $customer = User::create([
            'name' => 'Siti Rahma',
            'email' => 'siti.rahma@gmail.com',
            'password' => bcrypt('password123'),
            'role' => 'customer',
        ]);

        $vendor = User::where('role', 'vendor')->first() ?? $customer;
        $barang = Barang::create([
            'vendor_id' => $vendor->id,
            'nama' => 'Kamera DSLR Canon',
            'slug' => 'kamera-dslr-canon-' . uniqid(),
            'harga_sewa_harian' => 50000,
            'stok_total' => 2,
            'status' => 'tersedia',
            'is_approved' => true,
        ]);

        Wishlist::create([
            'user_id' => $customer->id,
            'barang_id' => $barang->id,
        ]);

        $response = $this->actingAs($customer)->get('/account');
        $response->assertStatus(200);
    }

    /**
     * Requirement 6.7: Full Order Transaction Management with 8 lifecycle statuses.
     */
    public function test_customer_pesanan_index_renders_all_eight_lifecycle_tabs(): void
    {
        $customer = User::where('role', 'customer')->first();
        if (!$customer) {
            $customer = User::create([
                'name' => 'Test User',
                'email' => 'testuser@example.com',
                'password' => bcrypt('password123'),
                'role' => 'customer',
            ]);
        }

        $response = $this->actingAs($customer)->get(route('customer.pesanan', ['status' => 'semua']));

        $response->assertStatus(200);
        $response->assertSee('Semua');
        $response->assertSee('Belum Bayar');
        $response->assertSee('Konfirmasi');
        $response->assertSee('Siap Diambil/Dikirim');
        $response->assertSee('Berjalan');
        $response->assertSee('Menunggu Ulasan');
        $response->assertSee('Selesai');
        $response->assertSee('Dibatalkan');
    }
}
