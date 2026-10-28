<?php

namespace Tests\Feature;

use App\Models\Barang;
use App\Models\Kategori;
use App\Models\Keranjang;
use App\Models\User;
use App\Models\Voucher;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class MultiVendorCheckoutTransactionTest extends TestCase
{
    use DatabaseTransactions;

    protected User $customer;
    protected User $vendor1;
    protected User $vendor2;
    protected Barang $barangVendor1;
    protected Barang $barangVendor2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customer = User::create([
            'name' => 'Customer MultiVendor',
            'email' => 'cust_mv_' . uniqid() . '@example.com',
            'password' => bcrypt('password123'),
            'role' => 'customer',
            'is_active' => 1,
            'whatsapp' => '081234567890',
        ]);
        $this->customer->markEmailAsVerified();

        $this->vendor1 = User::create([
            'name' => 'Vendor Satu',
            'email' => 'vendor1_' . uniqid() . '@example.com',
            'password' => bcrypt('password123'),
            'role' => 'vendor',
            'vendor_name' => 'Toko Alat Musik',
            'is_active' => 1,
            'whatsapp' => '081211112222',
        ]);
        $this->vendor1->markEmailAsVerified();

        $this->vendor2 = User::create([
            'name' => 'Vendor Dua',
            'email' => 'vendor2_' . uniqid() . '@example.com',
            'password' => bcrypt('password123'),
            'role' => 'vendor',
            'vendor_name' => 'Toko Kamera Pro',
            'is_active' => 1,
            'whatsapp' => '081233334444',
        ]);
        $this->vendor2->markEmailAsVerified();

        $kategori = Kategori::create([
            'nama' => 'Rental Umum ' . uniqid(),
            'slug' => 'rental-umum-' . uniqid(),
            'is_active' => 1,
        ]);

        $this->barangVendor1 = Barang::create([
            'vendor_id' => $this->vendor1->id,
            'kategori_id' => $kategori->id,
            'nama' => 'Gitar Akustik Yamaha',
            'slug' => 'gitar-yamaha-' . uniqid(),
            'deskripsi' => 'Gitar akustik jernih',
            'harga_sewa_harian' => 50000,
            'stok_total' => 5,
            'status' => 'tersedia',
            'is_approved' => 1,
        ]);

        $this->barangVendor2 = Barang::create([
            'vendor_id' => $this->vendor2->id,
            'kategori_id' => $kategori->id,
            'nama' => 'Tripod Manfrotto',
            'slug' => 'tripod-manfrotto-' . uniqid(),
            'deskripsi' => 'Tripod kokoh kamera',
            'harga_sewa_harian' => 30000,
            'stok_total' => 5,
            'status' => 'tersedia',
            'is_approved' => 1,
        ]);
    }

    public function test_multi_vendor_checkout_succeeds_and_creates_all_orders_atomically(): void
    {
        $cart1 = Keranjang::create([
            'user_id' => $this->customer->id,
            'barang_id' => $this->barangVendor1->id,
            'jumlah' => 1,
            'durasi_sewa' => 2,
        ]);

        $cart2 = Keranjang::create([
            'user_id' => $this->customer->id,
            'barang_id' => $this->barangVendor2->id,
            'jumlah' => 1,
            'durasi_sewa' => 2,
        ]);

        $payload = [
            'no_hp' => '081234567890',
            'metode_pembayaran' => 'QRIS',
            'durasi_sewa' => [
                $this->vendor1->id => 2,
                $this->vendor2->id => 2,
            ],
            'opsi_pengiriman' => [
                $this->vendor1->id => 'ambil',
                $this->vendor2->id => 'ambil',
            ],
            'ongkir_vendor' => [
                $this->vendor1->id => 0,
                $this->vendor2->id => 0,
            ],
            'jaminan' => [
                $this->vendor1->id => 'KTP',
                $this->vendor2->id => 'KTP',
            ],
            'start_date' => now()->toDateString(),
            'start_time' => '10:00',
        ];

        $response = $this->actingAs($this->customer)
            ->withSession([
                'checkout_selected_ids' => [$cart1->id, $cart2->id],
            ])
            ->post(route('customer.checkout.store'), $payload);

        $response->assertStatus(302);
        $response->assertSessionHasNoErrors();

        // Verifikasi 2 order dibuat untuk masing-masing vendor
        $orderVendor1 = DB::table('orders')->where('user_id', $this->customer->id)->where('vendor_id', $this->vendor1->id)->first();
        $orderVendor2 = DB::table('orders')->where('user_id', $this->customer->id)->where('vendor_id', $this->vendor2->id)->first();

        $this->assertNotNull($orderVendor1);
        $this->assertNotNull($orderVendor2);

        // Verifikasi order items dibuat
        $this->assertTrue(DB::table('order_items')->where('order_id', $orderVendor1->id)->where('product_id', $this->barangVendor1->id)->exists());
        $this->assertTrue(DB::table('order_items')->where('order_id', $orderVendor2->id)->where('product_id', $this->barangVendor2->id)->exists());

        // Verifikasi keranjang dihapus
        $this->assertFalse(Keranjang::where('id', $cart1->id)->exists());
        $this->assertFalse(Keranjang::where('id', $cart2->id)->exists());
    }

    public function test_multi_vendor_checkout_rolls_back_everything_if_any_vendor_query_fails(): void
    {
        $voucher1 = Voucher::create([
            'vendor_id' => $this->vendor1->id,
            'kode_voucher' => 'DISC10_' . uniqid(),
            'tipe_diskon' => 'nominal',
            'nilai_diskon' => 5000,
            'minimal_belanja' => 10000,
            'kuota_total' => 10,
            'kuota_terpakai' => 0,
            'tanggal_mulai' => now()->subDay(),
            'tanggal_selesai' => now()->addDays(5),
            'is_active' => 1,
        ]);

        $cart1 = Keranjang::create([
            'user_id' => $this->customer->id,
            'barang_id' => $this->barangVendor1->id,
            'jumlah' => 1,
            'durasi_sewa' => 1,
        ]);

        $cart2 = Keranjang::create([
            'user_id' => $this->customer->id,
            'barang_id' => $this->barangVendor2->id,
            'jumlah' => 1,
            'durasi_sewa' => 1,
        ]);

        // Simulasikan kegagalan database saat memproses pesanan untuk Vendor 2
        // Melalui DB event listener untuk mendeteksi query insert order vendor 2 dan throw exception
        $vendor2Id = $this->vendor2->id;
        DB::listen(function ($query) use ($vendor2Id) {
            if (str_contains($query->sql, 'insert into `orders`') && in_array($vendor2Id, $query->bindings)) {
                throw new \RuntimeException('Simulasi kegagalan database pada vendor 2!');
            }
        });

        $voucherJson = json_encode([
            'vendor_id' => $this->vendor1->id,
            'voucher_id' => $voucher1->id,
        ]);

        $payload = [
            'no_hp' => '081234567890',
            'metode_pembayaran' => 'QRIS',
            'voucher_data_json' => $voucherJson,
            'durasi_sewa' => [
                $this->vendor1->id => 1,
                $this->vendor2->id => 1,
            ],
            'opsi_pengiriman' => [
                $this->vendor1->id => 'ambil',
                $this->vendor2->id => 'ambil',
            ],
            'ongkir_vendor' => [
                $this->vendor1->id => 0,
                $this->vendor2->id => 0,
            ],
            'jaminan' => [
                $this->vendor1->id => 'KTP',
                $this->vendor2->id => 'KTP',
            ],
            'start_date' => now()->toDateString(),
            'start_time' => '10:00',
        ];

        $response = $this->actingAs($this->customer)
            ->withSession([
                'checkout_selected_ids' => [$cart1->id, $cart2->id],
            ])
            ->post(route('customer.checkout.store'), $payload);

        // Harus dialihkan kembali dengan flash error
        $response->assertStatus(302);
        $response->assertSessionHas('error');

        // BUKTI ATOMISITAS ("JIKA GAGAL SATU MAKA GAGAL SEMUA"):
        // 1. Tidak ada order yang tersimpan untuk Vendor 1 maupun Vendor 2
        $countOrders = DB::table('orders')->where('user_id', $this->customer->id)->count();
        $this->assertEquals(0, $countOrders, 'Harus tidak ada order yang tersimpan karena transaksi di-rollback secara utuh.');

        // 2. Tidak ada order_items yang tersimpan
        $countOrderItems = DB::table('order_items')
            ->whereIn('product_id', [$this->barangVendor1->id, $this->barangVendor2->id])
            ->count();
        $this->assertEquals(0, $countOrderItems, 'Order items tidak boleh tersisa di database.');

        // 3. Kuota voucher vendor 1 tidak terpotong (tetap 0)
        $voucher1Fresh = Voucher::find($voucher1->id);
        $this->assertEquals(0, $voucher1Fresh->kuota_terpakai, 'Kuota voucher harus tetap utuh (rolled back).');

        // 4. Keranjang tidak terfragmentasi: kedua item keranjang masih utuh
        $this->assertTrue(Keranjang::where('id', $cart1->id)->exists(), 'Item keranjang vendor 1 harus tetap ada.');
        $this->assertTrue(Keranjang::where('id', $cart2->id)->exists(), 'Item keranjang vendor 2 harus tetap ada.');
    }
}
