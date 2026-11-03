<?php

namespace Tests\Feature;

use App\Models\Barang;
use App\Models\CustomerVoucher;
use App\Models\Kategori;
use App\Models\Keranjang;
use App\Models\User;
use App\Models\Voucher;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class CheckoutVoucherSelectionTest extends TestCase
{
    use DatabaseTransactions;

    protected User $customer;
    protected User $vendor;
    protected Barang $barang;
    protected Voucher $voucher;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customer = User::create([
            'name' => 'Customer Shopee Style',
            'email' => 'cust_shopee_' . uniqid() . '@example.com',
            'password' => bcrypt('password123'),
            'role' => 'customer',
            'is_active' => 1,
            'whatsapp' => '081234567891',
        ]);
        $this->customer->markEmailAsVerified();

        $this->vendor = User::create([
            'name' => 'Vendor Kamera',
            'email' => 'vendor_cam_' . uniqid() . '@example.com',
            'password' => bcrypt('password123'),
            'role' => 'vendor',
            'vendor_name' => 'Toko Kamera Pro',
            'is_active' => 1,
            'whatsapp' => '081222223333',
        ]);
        $this->vendor->markEmailAsVerified();

        $kategori = Kategori::firstOrCreate(['nama' => 'Elektronik']);

        $this->barang = Barang::create([
            'vendor_id' => $this->vendor->id,
            'kategori_id' => $kategori->id,
            'nama' => 'Sony A7IV Pro Camera',
            'slug' => 'sony-a7iv-' . uniqid(),
            'deskripsi' => 'Kamera mirrorless profesional',
            'harga_sewa_harian' => 100000,
            'status' => 'tersedia',
            'stok_total' => 5,
            'stok' => 5,
            'is_delivery_supported' => 1,
        ]);

        $this->voucher = Voucher::create([
            'vendor_id' => $this->vendor->id,
            'kode_voucher' => 'SHOPEEVOUCHER' . rand(100, 999),
            'tipe_diskon' => 'nominal',
            'nilai_diskon' => 20000,
            'minimal_belanja' => 50000,
            'kuota_total' => 10,
            'kuota_terpakai' => 0,
            'tanggal_mulai' => now()->subDay()->toDateString(),
            'tanggal_selesai' => now()->addDays(7)->toDateString(),
            'is_active' => 1,
            'visibilitas' => 'publik',
        ]);
    }

    public function test_checkout_page_renders_with_available_vouchers()
    {
        $keranjang = Keranjang::create([
            'user_id' => $this->customer->id,
            'barang_id' => $this->barang->id,
            'jumlah' => 1,
            'durasi_sewa' => 1,
        ]);

        $response = $this->actingAs($this->customer)->get(route('customer.checkout', [
            'selected_items' => [$keranjang->id],
        ]));

        $response->assertStatus(200);
        $response->assertSee('Voucher Promo Toko');
        $response->assertSee('modalVoucher');
        $response->assertSee($this->voucher->kode_voucher);
    }

    public function test_cek_voucher_by_code_and_auto_claims_customer_voucher()
    {
        $response = $this->actingAs($this->customer)->postJson(route('customer.checkout.cek_voucher'), [
            'kode_voucher' => $this->voucher->kode_voucher,
            'vendor_ids' => [
                $this->vendor->id => 105000,
            ],
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'voucher_id' => $this->voucher->id,
            'potongan' => 20000,
            'kode_voucher' => $this->voucher->kode_voucher,
        ]);

        // Verifikasi CustomerVoucher tercatat otomatis di akun pelanggan
        $this->assertDatabaseHas('customer_vouchers', [
            'user_id' => $this->customer->id,
            'voucher_id' => $this->voucher->id,
            'status' => 'aktif',
        ]);
    }

    public function test_cek_voucher_by_id_direct_selection()
    {
        $response = $this->actingAs($this->customer)->postJson(route('customer.checkout.cek_voucher'), [
            'voucher_id' => $this->voucher->id,
            'vendor_ids' => [
                $this->vendor->id => 105000,
            ],
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'voucher_id' => $this->voucher->id,
            'potongan' => 20000,
        ]);
    }

    public function test_cek_voucher_fails_when_minimum_belanja_not_met()
    {
        $response = $this->actingAs($this->customer)->postJson(route('customer.checkout.cek_voucher'), [
            'kode_voucher' => $this->voucher->kode_voucher,
            'vendor_ids' => [
                $this->vendor->id => 25000, // di bawah minimal_belanja 50.000
            ],
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => false,
        ]);
        $response->assertSeeText('Minimal belanja');
    }

    public function test_checkout_store_with_voucher_increments_quota_and_updates_customer_voucher()
    {
        $keranjang = Keranjang::create([
            'user_id' => $this->customer->id,
            'barang_id' => $this->barang->id,
            'jumlah' => 1,
            'durasi_sewa' => 1,
        ]);

        CustomerVoucher::create([
            'user_id' => $this->customer->id,
            'voucher_id' => $this->voucher->id,
            'status' => 'aktif',
            'claimed_at' => now(),
        ]);

        $voucherDataJson = json_encode([
            'potongan' => 20000,
            'vendor_id' => $this->vendor->id,
            'voucher_id' => $this->voucher->id,
            'kode_voucher' => $this->voucher->kode_voucher,
        ]);

        $payload = [
            'no_hp' => '081234567891',
            'metode_pembayaran' => 'COD',
            'durasi_sewa' => [
                $this->vendor->id => 1,
            ],
            'opsi_pengiriman' => [
                $this->vendor->id => 'ambil',
            ],
            'ongkir_vendor' => [
                $this->vendor->id => 0,
            ],
            'jaminan' => [
                $this->vendor->id => 'KTP',
            ],
            'start_date' => now()->toDateString(),
            'start_time' => '10:00',
            'voucher_data_json' => $voucherDataJson,
        ];

        $response = $this->actingAs($this->customer)
            ->withSession([
                'checkout_selected_ids' => [$keranjang->id],
            ])
            ->post(route('customer.checkout.store'), $payload);

        $response->assertStatus(302);
        $response->assertSessionHasNoErrors();

        // Verifikasi kuota voucher bertambah
        $this->voucher->refresh();
        $this->assertEquals(1, $this->voucher->kuota_terpakai);

        // Verifikasi CustomerVoucher di-update menjadi terpakai
        $this->assertDatabaseHas('customer_vouchers', [
            'user_id' => $this->customer->id,
            'voucher_id' => $this->voucher->id,
            'status' => 'terpakai',
        ]);
        $this->assertEquals(1, $this->voucher->kuota_terpakai);

        // Verifikasi CustomerVoucher di-update menjadi terpakai
        $this->assertDatabaseHas('customer_vouchers', [
            'user_id' => $this->customer->id,
            'voucher_id' => $this->voucher->id,
            'status' => 'terpakai',
        ]);
    }
}
