<?php

namespace Tests\Feature;

use App\Models\Barang;
use App\Models\Kategori;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CustomerPesananSecurityFixTest extends TestCase
{
    use DatabaseTransactions;

    protected User $customer;
    protected User $vendor;
    protected Barang $barang;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customer = User::create([
            'name' => 'Tester Customer',
            'email' => 'customer_' . uniqid() . '@example.com',
            'password' => bcrypt('password123'),
            'role' => 'customer',
            'is_active' => 1,
        ]);
        $this->customer->markEmailAsVerified();

        $this->vendor = User::create([
            'name' => 'Tester Vendor',
            'email' => 'vendor_' . uniqid() . '@example.com',
            'password' => bcrypt('password123'),
            'role' => 'vendor',
            'vendor_name' => 'Toko Outdoor Berkah',
            'is_active' => 1,
        ]);
        $this->vendor->markEmailAsVerified();

        $kategori = Kategori::create([
            'nama' => 'Peralatan Camping',
            'slug' => 'peralatan-camping-' . uniqid(),
            'is_active' => 1,
        ]);

        $this->barang = Barang::create([
            'vendor_id' => $this->vendor->id,
            'kategori_id' => $kategori->id,
            'nama' => 'Kursi Lipat Gunung',
            'slug' => 'kursi-lipat-gunung-' . uniqid(),
            'harga_sewa_harian' => 15000,
            'stok' => 5,
            'stok_tersedia' => 5,
            'status' => 'tersedia',
            'latitude' => '-6.200000',
            'longitude' => '106.816666',
            'alamat' => 'Jl. Sudirman No. 12',
            'is_active' => 1,
        ]);
    }

    public function test_customer_cannot_selesaikan_order_when_status_is_menunggu_konfirmasi()
    {
        $orderId = DB::table('orders')->insertGetId([
            'user_id' => $this->customer->id,
            'vendor_id' => $this->vendor->id,
            'kode_booking' => 'RNT' . strtoupper(uniqid()),
            'customer_name' => $this->customer->name,
            'customer_whatsapp' => '08123456789',
            'alamat_pengiriman' => 'Diambil di Toko',
            'metode_pengambilan' => 'ambil',
            'shipping_method' => 'ambil',
            'tanggal_mulai' => now(),
            'tanggal_selesai' => now()->addDay(),
            'shipping_fee' => 0,
            'payment_method' => 'QRIS',
            'total_biaya' => 15750,
            'total_price' => 15750,
            'vendor_earning' => 15000,
            'rentify_fee' => 750,
            'status' => 'Menunggu Konfirmasi',
            'paid_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($this->customer)
            ->post(route('customer.pesanan.selesai', $orderId));

        $response->assertSessionHas('error');
        $this->assertStringContainsString('Sedang Disewa', session('error'));

        $orderAfter = DB::table('orders')->where('id', $orderId)->first();
        $this->assertEquals('Menunggu Konfirmasi', $orderAfter->status);
    }

    public function test_pesanan_index_does_not_render_selesaikan_button_on_menunggu_konfirmasi_and_renders_maps_toko()
    {
        $orderId = DB::table('orders')->insertGetId([
            'user_id' => $this->customer->id,
            'vendor_id' => $this->vendor->id,
            'kode_booking' => 'RNT' . strtoupper(uniqid()),
            'customer_name' => $this->customer->name,
            'customer_whatsapp' => '08123456789',
            'alamat_pengiriman' => 'Diambil di Toko',
            'metode_pengambilan' => 'ambil',
            'shipping_method' => 'ambil',
            'tanggal_mulai' => now(),
            'tanggal_selesai' => now()->addDay(),
            'shipping_fee' => 0,
            'payment_method' => 'QRIS',
            'total_biaya' => 15750,
            'total_price' => 15750,
            'vendor_earning' => 15000,
            'rentify_fee' => 750,
            'status' => 'Menunggu Konfirmasi',
            'paid_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('order_items')->insert([
            'order_id' => $orderId,
            'product_id' => $this->barang->id,
            'product_name' => $this->barang->nama,
            'price' => 15750,
            'quantity' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($this->customer)
            ->get(route('customer.pesanan', ['status' => 'konfirmasi']));

        $response->assertStatus(200);
        $response->assertDontSee('Selesaikan Pesanan');
        $response->assertSee('Maps Toko');
    }

    public function test_qris_checkout_view_does_not_render_manual_saya_sudah_bayar_button()
    {
        $orderId = DB::table('orders')->insertGetId([
            'user_id' => $this->customer->id,
            'vendor_id' => $this->vendor->id,
            'kode_booking' => 'RNT' . strtoupper(uniqid()),
            'customer_name' => $this->customer->name,
            'customer_whatsapp' => '08123456789',
            'alamat_pengiriman' => 'Diambil di Toko',
            'metode_pengambilan' => 'ambil',
            'shipping_method' => 'ambil',
            'tanggal_mulai' => now(),
            'tanggal_selesai' => now()->addDay(),
            'shipping_fee' => 0,
            'payment_method' => 'QRIS',
            'total_biaya' => 15750,
            'total_price' => 15750,
            'vendor_earning' => 15000,
            'rentify_fee' => 750,
            'status' => 'PENDING_PAYMENT',
            'paid_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($this->customer)
            ->get(route('customer.qris', 'INV-' . $orderId));

        $response->assertStatus(200);
        $response->assertDontSee('Saya Sudah Bayar');
        $response->assertDontSee('btn-confirm-payment');
    }
}
