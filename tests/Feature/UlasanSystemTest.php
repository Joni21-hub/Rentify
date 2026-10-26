<?php

namespace Tests\Feature;

use App\Models\Barang;
use App\Models\DetailPenyewaan;
use App\Models\Kategori;
use App\Models\Penyewaan;
use App\Models\Ulasan;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class UlasanSystemTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_ulasan_submission_lifecycle_and_order_transition(): void
    {
        // 1. Setup Customer & Vendor
        $customer = User::create([
            'name' => 'Budi Customer',
            'email' => 'budi_' . uniqid() . '@rentify.test',
            'password' => bcrypt('password123'),
            'role' => 'customer',
            'is_active' => 1,
        ]);
        $customer->markEmailAsVerified();

        $vendor = User::create([
            'name' => 'Vendor Mantap',
            'email' => 'vendor_' . uniqid() . '@rentify.test',
            'password' => bcrypt('password123'),
            'role' => 'vendor',
            'vendor_name' => 'Kamera Jaya Rental',
            'is_active' => 1,
        ]);
        $vendor->markEmailAsVerified();

        $kategori = Kategori::create([
            'nama' => 'Fotografi ' . uniqid(),
            'slug' => 'fotografi-' . uniqid(),
        ]);

        $barang = Barang::create([
            'vendor_id' => $vendor->id,
            'kategori_id' => $kategori->id,
            'nama' => 'Sony A7 IV Professional',
            'slug' => 'sony-a7-iv-' . uniqid(),
            'deskripsi' => 'Kamera mirrorless sensor fullframe terbaik.',
            'harga_sewa_harian' => 250000,
            'deposit' => 100000,
            'denda_per_hari' => 50000,
            'kondisi' => 'Sangat Baik',
            'stok_total' => 2,
            'status' => 'tersedia',
            'is_approved' => 1,
        ]);

        // 2. Setup Order dalam status 'Menunggu Ulasan'
        $order = Penyewaan::create([
            'user_id' => $customer->id,
            'vendor_id' => $vendor->id,
            'customer_name' => $customer->name,
            'customer_whatsapp' => '081234567890',
            'kode_booking' => Penyewaan::generateKodeBooking(),
            'payment_method' => 'ONLINE',
            'metode_pengambilan' => 'ambil',
            'shipping_method' => 'ambil',
            'total_biaya' => 250000,
            'total_price' => 250000,
            'status' => 'Menunggu Ulasan',
            'tanggal_mulai' => now()->subDays(2),
            'tanggal_selesai' => now()->subDay(),
        ]);

        DetailPenyewaan::create([
            'order_id' => $order->id,
            'product_id' => $barang->id,
            'product_name' => $barang->nama,
            'price' => $barang->harga_sewa_harian,
            'quantity' => 1,
        ]);

        // Initial check: barang has 0 reviews and 0.0 rating
        $this->assertEquals(0, $barang->total_ulasan);
        $this->assertEquals(0.0, $barang->rating_rata_rata);

        // 3. Tes Otorisasi: Customer lain tidak boleh mengulas pesanan ini
        $otherCustomer = User::create([
            'name' => 'Intruder',
            'email' => 'intruder_' . uniqid() . '@rentify.test',
            'password' => bcrypt('password123'),
            'role' => 'customer',
            'is_active' => 1,
        ]);

        $responseUnauthorized = $this->actingAs($otherCustomer)->post(route('customer.ulasan.store', $order->id), [
            'rating' => 5,
            'komentar' => 'Hacker ulasan',
            'barang_id' => $barang->id,
        ]);
        $responseUnauthorized->assertStatus(302);
        $this->assertNull(Ulasan::where('order_id', $order->id)->where('user_id', $otherCustomer->id)->first());

        // 4. Tes Validasi Rating (Rating wajib 1 - 5)
        $responseInvalid = $this->actingAs($customer)->post(route('customer.ulasan.store', $order->id), [
            'rating' => 10,
            'komentar' => 'Rating kebesaran',
        ]);
        $responseInvalid->assertSessionHasErrors('rating');

        // 5. Submit Ulasan Berhasil oleh Customer Pemilik Pesanan
        $responseSuccess = $this->actingAs($customer)->post(route('customer.ulasan.store', $order->id), [
            'rating' => 5,
            'komentar' => 'Kamera sangat bersih dan bekerja luar biasa! Pengalaman rental memuaskan.',
            'barang_id' => $barang->id,
        ]);

        $responseSuccess->assertRedirect(route('customer.pesanan', ['status' => 'selesai']));
        $responseSuccess->assertSessionHas('success');

        // Verifikasi tersimpan di tabel ulasans
        $savedUlasan = Ulasan::where('order_id', $order->id)
            ->where('user_id', $customer->id)
            ->where('barang_id', $barang->id)
            ->first();
        $this->assertNotNull($savedUlasan);
        $this->assertEquals(5, $savedUlasan->rating);
        $this->assertEquals('Kamera sangat bersih dan bekerja luar biasa! Pengalaman rental memuaskan.', $savedUlasan->komentar);

        // Verifikasi status pesanan bertransisi dari 'Menunggu Ulasan' ke 'Selesai'
        $order->refresh();
        $this->assertEquals('Selesai', $order->status);

        // Verifikasi kalkulasi agregat rating & total ulasan pada model Barang
        $barang->refresh();
        $this->assertEquals(1, $barang->total_ulasan);
        $this->assertEquals(5.0, $barang->rating_rata_rata);

        // 6. Tes Idempotensi / Ubah Ulasan (updateOrCreate tanpa duplikasi baris)
        $this->actingAs($customer)->post(route('customer.ulasan.store', $order->id), [
            'rating' => 4,
            'komentar' => 'Revisi: sangat bagus tapi baterai cadangan agak cepat habis.',
            'barang_id' => $barang->id,
        ]);

        $this->assertEquals(1, Ulasan::where('order_id', $order->id)->count());
        $barang->refresh();
        $this->assertEquals(4.0, $barang->rating_rata_rata);

        // 7. Verifikasi Halaman Detail Produk Menampilkan Ulasan
        $detailResponse = $this->get(route('customer.barang.show', $barang->slug));
        $detailResponse->assertStatus(200);
        $detailResponse->assertSee('Budi Customer');
        $detailResponse->assertSee('Revisi: sangat bagus tapi baterai cadangan agak cepat habis.');
    }

    public function test_customer_finishing_order_flows_to_menunggu_ulasan_and_aggregates_multiple_ratings(): void
    {
        $customer1 = User::create([
            'name' => 'Customer Satu',
            'email' => 'c1_' . uniqid() . '@rentify.test',
            'password' => bcrypt('password123'),
            'role' => 'customer',
            'is_active' => 1,
        ]);
        $customer1->markEmailAsVerified();

        $customer2 = User::create([
            'name' => 'Customer Dua',
            'email' => 'c2_' . uniqid() . '@rentify.test',
            'password' => bcrypt('password123'),
            'role' => 'customer',
            'is_active' => 1,
        ]);
        $customer2->markEmailAsVerified();

        $vendor = User::create([
            'name' => 'Vendor Tenda',
            'email' => 'vendor_tenda_' . uniqid() . '@rentify.test',
            'password' => bcrypt('password123'),
            'role' => 'vendor',
            'vendor_name' => 'Outdoor Pro Rental',
            'is_active' => 1,
        ]);
        $vendor->markEmailAsVerified();

        $kategori = Kategori::create([
            'nama' => 'Camping ' . uniqid(),
            'slug' => 'camping-' . uniqid(),
        ]);

        $barang = Barang::create([
            'vendor_id' => $vendor->id,
            'kategori_id' => $kategori->id,
            'nama' => 'Tenda Dome Arpenaz 4 Orang',
            'slug' => 'tenda-dome-' . uniqid(),
            'harga_sewa_harian' => 60000,
            'kondisi' => 'Sangat Baik',
            'stok_total' => 5,
            'status' => 'tersedia',
            'is_approved' => 1,
        ]);

        // Order 1 disewa Customer 1 (status 'Sedang Disewa')
        $order1 = Penyewaan::create([
            'user_id' => $customer1->id,
            'vendor_id' => $vendor->id,
            'customer_name' => $customer1->name,
            'customer_whatsapp' => '081234567891',
            'kode_booking' => Penyewaan::generateKodeBooking(),
            'payment_method' => 'ONLINE',
            'paid_at' => now()->subDay(),
            'metode_pengambilan' => 'ambil',
            'shipping_method' => 'ambil',
            'total_biaya' => 60000,
            'total_price' => 60000,
            'status' => 'Sedang Disewa',
            'tanggal_mulai' => now()->subDays(2),
            'tanggal_selesai' => now()->subDay(),
        ]);

        DetailPenyewaan::create([
            'order_id' => $order1->id,
            'product_id' => $barang->id,
            'product_name' => $barang->nama,
            'price' => $barang->harga_sewa_harian,
            'quantity' => 1,
        ]);

        // Customer 1 klik "Selesaikan Pesanan"
        $finishResponse = $this->actingAs($customer1)->post(route('customer.pesanan.selesai', $order1->id));
        $finishResponse->assertRedirect(route('customer.pesanan', ['status' => 'ulasan']));

        $order1->refresh();
        $this->assertEquals('Menunggu Ulasan', $order1->status);

        // Pastikan pesanan muncul di halaman tab 'ulasan'
        $pesananUlasanPage = $this->actingAs($customer1)->get(route('customer.pesanan', ['status' => 'ulasan']));
        $pesananUlasanPage->assertStatus(200);
        $pesananUlasanPage->assertSee('Tenda Dome Arpenaz 4 Orang');
        $pesananUlasanPage->assertSee('Beri Ulasan');

        // Customer 1 memberikan ulasan rating 5 bintang
        $this->actingAs($customer1)->post(route('customer.ulasan.store', $order1->id), [
            'rating' => 5,
            'komentar' => 'Tenda sangat bersih, pasak lengkap, dan waterproof saat hujan deras!',
            'barang_id' => $barang->id,
        ]);

        // Customer 2 langsung membuat order kedua yang sudah Selesai
        $order2 = Penyewaan::create([
            'user_id' => $customer2->id,
            'vendor_id' => $vendor->id,
            'customer_name' => $customer2->name,
            'customer_whatsapp' => '081234567892',
            'kode_booking' => Penyewaan::generateKodeBooking(),
            'payment_method' => 'ONLINE',
            'paid_at' => now()->subDay(),
            'metode_pengambilan' => 'ambil',
            'shipping_method' => 'ambil',
            'total_biaya' => 60000,
            'total_price' => 60000,
            'status' => 'Selesai',
            'tanggal_mulai' => now()->subDays(3),
            'tanggal_selesai' => now()->subDays(2),
        ]);

        DetailPenyewaan::create([
            'order_id' => $order2->id,
            'product_id' => $barang->id,
            'product_name' => $barang->nama,
            'price' => $barang->harga_sewa_harian,
            'quantity' => 1,
        ]);

        // Customer 2 memberikan ulasan rating 4 bintang
        $this->actingAs($customer2)->post(route('customer.ulasan.store', $order2->id), [
            'rating' => 4,
            'komentar' => 'Tenda bagus dan luas, pemasangan mudah.',
            'barang_id' => $barang->id,
        ]);

        // Verifikasi kalkulasi agregat gabungan: (5 + 4) / 2 = 4.5
        $barang->refresh();
        $this->assertEquals(2, $barang->total_ulasan);
        $this->assertEquals(4.5, $barang->rating_rata_rata);

        // Verifikasi halaman detail menampilkan kedua ulasan
        $detailPage = $this->get(route('customer.barang.show', $barang->slug));
        $detailPage->assertStatus(200);
        $detailPage->assertSee('4.5');
        $detailPage->assertSee('2 Ulasan');
        $detailPage->assertSee('Customer Satu');
        $detailPage->assertSee('Customer Dua');
    }
}
