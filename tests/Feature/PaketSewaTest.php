<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Barang;
use App\Models\Kategori;
use App\Models\PaketSewaItem;
use App\Models\Penyewaan;
use App\Models\DetailPenyewaan;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class PaketSewaTest extends TestCase
{
    use DatabaseTransactions;

    protected User $vendor;
    protected Kategori $kategori;
    protected Barang $itemA;
    protected Barang $itemB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->vendor = User::factory()->create([
            'role' => 'vendor',
            'vendor_status' => 'approved',
            'email_verified_at' => now(),
        ]);

        $this->kategori = Kategori::firstOrCreate(
            ['nama' => 'Outdoor & Camping'],
            ['is_active' => 1]
        );

        $this->itemA = Barang::create([
            'vendor_id' => $this->vendor->id,
            'kategori_id' => $this->kategori->id,
            'nama' => 'Meja Lipat Portable',
            'slug' => 'meja-lipat-portable-' . uniqid(),
            'deskripsi' => 'Meja lipat aluminium',
            'harga_sewa_harian' => 20000,
            'deposit' => 50000,
            'denda_per_hari' => 10000,
            'kondisi' => 'Baik',
            'stok_total' => 4,
            'status' => 'tersedia',
            'status_barang' => 'disetujui',
            'is_approved' => 1,
            'is_paket' => 0,
        ]);

        $this->itemB = Barang::create([
            'vendor_id' => $this->vendor->id,
            'kategori_id' => $this->kategori->id,
            'nama' => 'Kursi Lipat Santai',
            'slug' => 'kursi-lipat-santai-' . uniqid(),
            'deskripsi' => 'Kursi lipat outdoor',
            'harga_sewa_harian' => 10000,
            'deposit' => 25000,
            'denda_per_hari' => 5000,
            'kondisi' => 'Baik',
            'stok_total' => 5,
            'status' => 'tersedia',
            'status_barang' => 'disetujui',
            'is_approved' => 1,
            'is_paket' => 0,
        ]);
    }

    public function test_vendor_can_create_paket_sewa_with_multiple_items()
    {
        $response = $this->actingAs($this->vendor)->post(route('vendor.paket.store'), [
            'nama' => 'Paket Piknik Asyik (1 Meja + 2 Kursi)',
            'kategori_id' => $this->kategori->id,
            'deskripsi' => 'Bundling meja dan 2 kursi lipat hemat',
            'harga_sewa_harian' => 35000,
            'deposit' => 75000,
            'denda_per_hari' => 15000,
            'is_delivery_supported' => 1,
            'items' => [
                ['barang_id' => $this->itemA->id, 'quantity' => 1],
                ['barang_id' => $this->itemB->id, 'quantity' => 2],
            ],
        ]);

        $response->assertRedirect(route('vendor.barang.index', ['tipe' => 'paket']));
        $response->assertSessionHas('success');

        $paket = Barang::where('nama', 'Paket Piknik Asyik (1 Meja + 2 Kursi)')->first();
        $this->assertNotNull($paket);
        $this->assertTrue((bool)$paket->is_paket);
        $this->assertEquals(35000, (float)$paket->harga_sewa_harian);

        // Periksa relasi komposisi paket
        $this->assertCount(2, $paket->paketItems);
        $this->assertDatabaseHas('paket_sewa_items', [
            'paket_id' => $paket->id,
            'barang_id' => $this->itemA->id,
            'quantity' => 1,
        ]);
        $this->assertDatabaseHas('paket_sewa_items', [
            'paket_id' => $paket->id,
            'barang_id' => $this->itemB->id,
            'quantity' => 2,
        ]);
    }

    public function test_paket_sewa_stok_total_is_dynamically_calculated_from_lowest_component()
    {
        // Item A stok = 4 (butuh 1 -> bisa 4 paket)
        // Item B stok = 5 (butuh 2 -> floor(5/2) = 2 paket)
        // Maka stok paket = min(4, 2) = 2

        $paket = Barang::create([
            'vendor_id' => $this->vendor->id,
            'kategori_id' => $this->kategori->id,
            'nama' => 'Paket Santai',
            'slug' => 'paket-santai-' . uniqid(),
            'deskripsi' => 'Paket santai outdoor',
            'harga_sewa_harian' => 35000,
            'stok_total' => 0,
            'status' => 'tersedia',
            'status_barang' => 'disetujui',
            'is_approved' => 1,
            'is_paket' => 1,
        ]);

        PaketSewaItem::create(['paket_id' => $paket->id, 'barang_id' => $this->itemA->id, 'quantity' => 1]);
        PaketSewaItem::create(['paket_id' => $paket->id, 'barang_id' => $this->itemB->id, 'quantity' => 2]);

        $this->assertEquals(2, $paket->stok_total);

        // Jika stok Item B bertambah menjadi 8 -> floor(8/2) = 4, maka stok paket jadi min(4, 4) = 4
        $this->itemB->stok_total = 8;
        $this->itemB->save();
        $paket->refresh();
        $this->assertEquals(4, $paket->stok_total);

        // Jika stok Item A turun menjadi 1 -> min(1, 4) = 1
        $this->itemA->stok_total = 1;
        $this->itemA->save();
        $paket->refresh();
        $this->assertEquals(1, $paket->stok_total);

        // Jika stok Item B menjadi 1 (< 2 butuh) -> floor(1/2) = 0
        $this->itemB->stok_total = 1;
        $this->itemB->save();
        $paket->refresh();
        $this->assertEquals(0, $paket->stok_total);
    }

    public function test_bundle_rental_reduces_single_item_available_stock()
    {
        $customer = User::factory()->create(['role' => 'customer']);

        $paket = Barang::create([
            'vendor_id' => $this->vendor->id,
            'kategori_id' => $this->kategori->id,
            'nama' => 'Paket Bundling Stok Sync',
            'slug' => 'paket-bundling-stok-sync-' . uniqid(),
            'deskripsi' => 'Paket untuk uji sinkronisasi',
            'harga_sewa_harian' => 35000,
            'stok_total' => 0,
            'status' => 'tersedia',
            'status_barang' => 'disetujui',
            'is_approved' => 1,
            'is_paket' => 1,
        ]);

        PaketSewaItem::create(['paket_id' => $paket->id, 'barang_id' => $this->itemA->id, 'quantity' => 1]);
        PaketSewaItem::create(['paket_id' => $paket->id, 'barang_id' => $this->itemB->id, 'quantity' => 2]);

        $rentStart = now()->addDays(2)->setTime(9, 0, 0);
        $rentEnd   = $rentStart->copy()->addDays(1);

        // Buat pesanan aktif untuk PAKET (quantity 1 paket)
        $order = Penyewaan::create([
            'user_id' => $customer->id,
            'vendor_id' => $this->vendor->id,
            'customer_name' => $customer->name ?? 'Customer Test',
            'customer_whatsapp' => '081234567890',
            'metode_pengambilan' => 'Ambil Sendiri',
            'alamat_pengiriman' => 'Alamat Pengiriman',
            'payment_method' => 'QRIS',
            'kode_booking' => Penyewaan::generateKodeBooking(),
            'status' => 'Sedang Disewa',
            'start_rent' => $rentStart,
            'end_rent' => $rentEnd,
            'tanggal_mulai' => $rentStart->toDateString(),
            'tanggal_selesai' => $rentEnd->toDateString(),
            'total_biaya' => 35000,
            'vendor_earning' => 35000,
            'rentify_fee' => 0,
        ]);

        DetailPenyewaan::create([
            'order_id' => $order->id,
            'product_id' => $paket->id,
            'product_name' => $paket->nama,
            'price' => 35000,
            'quantity' => 1,
            'subtotal_sewa_asli' => 35000,
            'subtotal_sewa_markup' => 35000,
            'subtotal_sewa_bersih' => 35000,
        ]);

        // Cek ketersediaan barang satuan:
        // Item A stok awal 4, dipakai paket 1 -> sisa harus 3
        $this->assertEquals(1, $this->itemA->getBookedQuantityInPeriod($rentStart, $rentEnd));
        $this->assertEquals(3, $this->itemA->getAvailableStockInPeriod($rentStart, $rentEnd));

        // Item B stok awal 5, dipakai paket 2 -> sisa harus 3
        $this->assertEquals(2, $this->itemB->getBookedQuantityInPeriod($rentStart, $rentEnd));
        $this->assertEquals(3, $this->itemB->getAvailableStockInPeriod($rentStart, $rentEnd));
    }

    public function test_single_item_rental_reduces_bundle_availability()
    {
        $customer = User::factory()->create(['role' => 'customer']);

        $paket = Barang::create([
            'vendor_id' => $this->vendor->id,
            'kategori_id' => $this->kategori->id,
            'nama' => 'Paket Bundling Ketersediaan',
            'slug' => 'paket-bundling-ketersediaan-' . uniqid(),
            'deskripsi' => 'Paket ketersediaan',
            'harga_sewa_harian' => 35000,
            'stok_total' => 0,
            'status' => 'tersedia',
            'status_barang' => 'disetujui',
            'is_approved' => 1,
            'is_paket' => 1,
        ]);

        // Item A = 4 stok, butuh 1
        // Item B = 5 stok, butuh 2 (kapasitas paket awal = 2)
        PaketSewaItem::create(['paket_id' => $paket->id, 'barang_id' => $this->itemA->id, 'quantity' => 1]);
        PaketSewaItem::create(['paket_id' => $paket->id, 'barang_id' => $this->itemB->id, 'quantity' => 2]);

        $rentStart = now()->addDays(3)->setTime(10, 0, 0);
        $rentEnd   = $rentStart->copy()->addDays(1);

        // Sebelum ada sewa satuan: paket tersedia 2 unit
        $this->assertEquals(2, $paket->getAvailableStockInPeriod($rentStart, $rentEnd));

        // Ada pelanggan menyewa Item B (kursi satuan) sebanyak 4 unit pada rentang tersebut
        $order = Penyewaan::create([
            'user_id' => $customer->id,
            'vendor_id' => $this->vendor->id,
            'customer_name' => $customer->name ?? 'Customer Test',
            'customer_whatsapp' => '081234567890',
            'metode_pengambilan' => 'Ambil Sendiri',
            'alamat_pengiriman' => 'Alamat Pengiriman',
            'payment_method' => 'QRIS',
            'kode_booking' => Penyewaan::generateKodeBooking(),
            'status' => 'Sedang Disewa',
            'start_rent' => $rentStart,
            'end_rent' => $rentEnd,
            'tanggal_mulai' => $rentStart->toDateString(),
            'tanggal_selesai' => $rentEnd->toDateString(),
            'total_biaya' => 40000,
            'vendor_earning' => 40000,
            'rentify_fee' => 0,
        ]);

        DetailPenyewaan::create([
            'order_id' => $order->id,
            'product_id' => $this->itemB->id,
            'product_name' => $this->itemB->nama,
            'price' => 10000,
            'quantity' => 4,
            'subtotal_sewa_asli' => 40000,
            'subtotal_sewa_markup' => 40000,
            'subtotal_sewa_bersih' => 40000,
        ]);

        // Sisa Item B pada tanggal tersebut tinggal 5 - 4 = 1 unit.
        // Karena paket butuh 2 kursi, floor(1/2) = 0!
        // Paket otomatis TIDAK TERSEDIA (stok tersedia = 0) pada tanggal tersebut!
        $this->assertEquals(0, $paket->getAvailableStockInPeriod($rentStart, $rentEnd));

        // Namun pada tanggal lain (misal 10 hari ke depan) di mana Item B tidak disewa,
        // paket otomatis TETAP TERSEDIA 2 unit!
        $otherStart = now()->addDays(10)->setTime(10, 0, 0);
        $otherEnd   = $otherStart->copy()->addDays(1);
        $this->assertEquals(2, $paket->getAvailableStockInPeriod($otherStart, $otherEnd));
    }

    public function test_vendor_cannot_include_other_vendors_products()
    {
        $otherVendor = User::factory()->create(['role' => 'vendor']);
        $alienItem = Barang::create([
            'vendor_id' => $otherVendor->id,
            'kategori_id' => $this->kategori->id,
            'nama' => 'Barang Vendor Lain',
            'slug' => 'alien-item-' . uniqid(),
            'harga_sewa_harian' => 50000,
            'stok_total' => 2,
            'is_paket' => 0,
        ]);

        $response = $this->actingAs($this->vendor)->post(route('vendor.paket.store'), [
            'nama' => 'Paket Ilegal',
            'kategori_id' => $this->kategori->id,
            'deskripsi' => 'Mencoba pakai barang toko lain',
            'harga_sewa_harian' => 60000,
            'items' => [
                ['barang_id' => $this->itemA->id, 'quantity' => 1],
                ['barang_id' => $alienItem->id, 'quantity' => 1],
            ],
        ]);

        $response->assertSessionHasErrors('items');
        $this->assertDatabaseMissing('barang', ['nama' => 'Paket Ilegal']);
    }

    public function test_vendor_can_update_paket_sewa()
    {
        $paket = Barang::create([
            'vendor_id' => $this->vendor->id,
            'kategori_id' => $this->kategori->id,
            'nama' => 'Paket Lama',
            'slug' => 'paket-lama-' . uniqid(),
            'deskripsi' => 'Deskripsi lama',
            'harga_sewa_harian' => 30000,
            'stok_total' => 0,
            'is_paket' => 1,
        ]);

        PaketSewaItem::create(['paket_id' => $paket->id, 'barang_id' => $this->itemA->id, 'quantity' => 1]);

        $response = $this->actingAs($this->vendor)->put(route('vendor.paket.update', $paket->id), [
            'nama' => 'Paket Diperbarui',
            'kategori_id' => $this->kategori->id,
            'deskripsi' => 'Deskripsi baru lebih lengkap',
            'harga_sewa_harian' => 45000,
            'items' => [
                ['barang_id' => $this->itemA->id, 'quantity' => 2],
                ['barang_id' => $this->itemB->id, 'quantity' => 3],
            ],
        ]);

        $response->assertRedirect(route('vendor.barang.index', ['tipe' => 'paket']));
        $paket->refresh();

        $this->assertEquals('Paket Diperbarui', $paket->nama);
        $this->assertEquals(45000, (float)$paket->harga_sewa_harian);
        $this->assertCount(2, $paket->paketItems);
        $this->assertEquals(2, $paket->paketItems()->where('barang_id', $this->itemA->id)->first()->quantity);
        $this->assertEquals(3, $paket->paketItems()->where('barang_id', $this->itemB->id)->first()->quantity);
    }

    public function test_checkout_rejects_bundle_if_any_component_is_out_of_stock_on_selected_date()
    {
        $customer = User::factory()->create(['role' => 'customer']);

        $paket = Barang::create([
            'vendor_id' => $this->vendor->id,
            'kategori_id' => $this->kategori->id,
            'nama' => 'Paket Camping Pro',
            'slug' => 'paket-camping-pro-' . uniqid(),
            'deskripsi' => 'Paket camping',
            'harga_sewa_harian' => 50000,
            'stok_total' => 0,
            'status' => 'tersedia',
            'status_barang' => 'disetujui',
            'is_approved' => 1,
            'is_paket' => 1,
        ]);

        // Item A (meja) stok = 4, butuh 1
        // Item B (kursi) stok = 5, butuh 2
        PaketSewaItem::create(['paket_id' => $paket->id, 'barang_id' => $this->itemA->id, 'quantity' => 1]);
        PaketSewaItem::create(['paket_id' => $paket->id, 'barang_id' => $this->itemB->id, 'quantity' => 2]);

        $sewaDate = now()->addDays(5)->format('Y-m-d');
        $rentStart = \Carbon\Carbon::parse($sewaDate . ' 08:00:00');
        $rentEnd   = $rentStart->copy()->addDays(1);

        // Kunci seluruh stok Item B (kursi) pada tanggal tersebut oleh pelanggan lain
        $otherOrder = Penyewaan::create([
            'user_id' => User::factory()->create(['role' => 'customer'])->id,
            'vendor_id' => $this->vendor->id,
            'customer_name' => 'Other Customer',
            'customer_whatsapp' => '081234567891',
            'metode_pengambilan' => 'Ambil Sendiri',
            'alamat_pengiriman' => 'Alamat Lain',
            'payment_method' => 'QRIS',
            'kode_booking' => Penyewaan::generateKodeBooking(),
            'status' => 'Sedang Disewa',
            'start_rent' => $rentStart,
            'end_rent' => $rentEnd,
            'tanggal_mulai' => $rentStart->toDateString(),
            'tanggal_selesai' => $rentEnd->toDateString(),
            'total_biaya' => 50000,
            'vendor_earning' => 50000,
            'rentify_fee' => 0,
        ]);

        DetailPenyewaan::create([
            'order_id' => $otherOrder->id,
            'product_id' => $this->itemB->id,
            'product_name' => $this->itemB->nama,
            'price' => 10000,
            'quantity' => 5, // Habiskan semua 5 kursi
            'subtotal_sewa_asli' => 50000,
            'subtotal_sewa_markup' => 50000,
            'subtotal_sewa_bersih' => 50000,
        ]);

        // Masukkan paket ke keranjang customer
        $keranjang = \App\Models\Keranjang::create([
            'user_id' => $customer->id,
            'barang_id' => $paket->id,
            'jumlah' => 1,
            'durasi_sewa' => 1,
        ]);

        // Customer mencoba checkout paket pada tanggal tersebut saat komponen (kursi) habis
        $response = $this->actingAs($customer)
            ->withSession(['checkout_selected_ids' => [$keranjang->id]])
            ->post(route('customer.checkout.store'), [
                'start_date' => $sewaDate,
                'start_time' => '08:00',
                'durasi_sewa' => [$this->vendor->id => 1],
                'opsi_pengiriman' => [$this->vendor->id => 'Ambil Sendiri'],
                'ongkir_vendor' => [$this->vendor->id => 0],
                'jaminan' => [$this->vendor->id => 'KTP'],
                'no_hp' => '081234567890',
            ]);

        $response->assertSessionHas('error');
        $errorMessage = session('error');
        $this->assertStringContainsString('tidak mencukupi', $errorMessage);
    }

    public function test_vendor_can_upload_multiple_photos_for_paket_sewa()
    {
        \Illuminate\Support\Facades\Storage::fake('public');

        $file1 = \Illuminate\Http\UploadedFile::fake()->image('cover.jpg', 600, 600);
        $file2 = \Illuminate\Http\UploadedFile::fake()->image('gallery1.jpg', 600, 600);
        $file3 = \Illuminate\Http\UploadedFile::fake()->image('gallery2.jpg', 600, 600);

        $response = $this->actingAs($this->vendor)->post(route('vendor.paket.store'), [
            'nama' => 'Paket Glamping Mewah Multi Foto',
            'kategori_id' => $this->kategori->id,
            'deskripsi' => 'Paket lengkap dengan banyak dokumentasi foto',
            'harga_sewa_harian' => 50000,
            'items' => [
                ['barang_id' => $this->itemA->id, 'quantity' => 1],
            ],
            'fotos' => [$file1, $file2, $file3],
        ]);

        $response->assertRedirect(route('vendor.barang.index', ['tipe' => 'paket']));

        $paket = Barang::where('nama', 'Paket Glamping Mewah Multi Foto')->first();
        $this->assertNotNull($paket);
        $this->assertNotNull($paket->cover_photo);

        // Foto 2 dan 3 harus tersimpan di tabel foto_barangs
        $this->assertCount(2, $paket->fotos);

        // Uji update dengan menambah foto baru tanpa mengganti cover
        $file4 = \Illuminate\Http\UploadedFile::fake()->image('gallery3.jpg', 600, 600);
        $oldCover = $paket->cover_photo;

        $updateResponse = $this->actingAs($this->vendor)->put(route('vendor.paket.update', $paket->id), [
            'nama' => 'Paket Glamping Mewah Multi Foto Updated',
            'kategori_id' => $this->kategori->id,
            'deskripsi' => 'Update deskripsi paket',
            'harga_sewa_harian' => 55000,
            'items' => [
                ['barang_id' => $this->itemA->id, 'quantity' => 1],
            ],
            'replace_cover' => 0,
            'fotos' => [$file4],
        ]);

        $updateResponse->assertRedirect(route('vendor.barang.index', ['tipe' => 'paket']));

        $paket->refresh();
        $this->assertEquals($oldCover, $paket->cover_photo);
        $this->assertCount(3, $paket->fotos);
    }
}
