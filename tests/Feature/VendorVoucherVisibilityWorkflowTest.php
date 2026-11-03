<?php

namespace Tests\Feature;

use App\Models\CustomerVoucher;
use App\Models\User;
use App\Models\Voucher;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class VendorVoucherVisibilityWorkflowTest extends TestCase
{
    use DatabaseTransactions;

    protected User $vendor;
    protected User $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->vendor = User::create([
            'name' => 'Vendor Test Toko',
            'email' => 'vendor_voucher_' . uniqid() . '@example.com',
            'password' => bcrypt('secret123'),
            'role' => 'vendor',
            'is_active' => 1,
            'vendor_status' => 'active',
        ]);
        $this->vendor->markEmailAsVerified();

        $this->customer = User::create([
            'name' => 'Customer Test Toko',
            'email' => 'customer_voucher_' . uniqid() . '@example.com',
            'password' => bcrypt('secret123'),
            'role' => 'customer',
            'is_active' => 1,
        ]);
        $this->customer->markEmailAsVerified();
    }

    public function test_vendor_can_create_public_and_secret_vouchers(): void
    {
        $kodePublik = 'PUB' . strtoupper(uniqid());
        $kodeRahasia = 'SEC' . strtoupper(uniqid());

        // 1. Simpan voucher publik
        $responsePub = $this->actingAs($this->vendor)->post(route('vendor.voucher.store'), [
            'kode_voucher' => $kodePublik,
            'visibilitas' => 'publik',
            'tipe_diskon' => 'persen',
            'nilai_diskon' => 20,
            'maksimal_diskon' => 50000,
            'minimal_belanja' => 100000,
            'kuota_total' => 50,
            'tanggal_mulai' => now()->toDateString(),
            'tanggal_selesai' => now()->addDays(7)->toDateString(),
        ]);
        $responsePub->assertSessionHasNoErrors();
        $this->assertDatabaseHas('vouchers', [
            'kode_voucher' => $kodePublik,
            'visibilitas' => 'publik',
            'tipe_voucher' => 'toko',
            'funding_source' => 'vendor',
            'nilai_diskon' => 20,
        ]);

        // 2. Simpan voucher rahasia
        $responseSec = $this->actingAs($this->vendor)->post(route('vendor.voucher.store'), [
            'kode_voucher' => $kodeRahasia,
            'visibilitas' => 'rahasia',
            'tipe_diskon' => 'nominal',
            'nilai_diskon' => 15000,
            'minimal_belanja' => 50000,
            'kuota_total' => 20,
            'tanggal_mulai' => now()->toDateString(),
            'tanggal_selesai' => now()->addDays(14)->toDateString(),
        ]);
        $responseSec->assertSessionHasNoErrors();
        $this->assertDatabaseHas('vouchers', [
            'kode_voucher' => $kodeRahasia,
            'visibilitas' => 'rahasia',
            'tipe_voucher' => 'toko',
            'funding_source' => 'vendor',
            'nilai_diskon' => 15000,
        ]);
    }

    public function test_vendor_can_toggle_voucher_status(): void
    {
        $voucher = Voucher::create([
            'vendor_id' => $this->vendor->id,
            'kode_voucher' => 'TOGGLE' . strtoupper(uniqid()),
            'tipe_voucher' => 'toko',
            'funding_source' => 'vendor',
            'visibilitas' => 'publik',
            'tipe_diskon' => 'nominal',
            'nilai_diskon' => 10000,
            'minimal_belanja' => 50000,
            'kuota_total' => 10,
            'tanggal_mulai' => now()->toDateString(),
            'tanggal_selesai' => now()->addDays(7)->toDateString(),
            'is_active' => 1,
        ]);

        $this->actingAs($this->vendor)
            ->patch(route('vendor.voucher.toggle', $voucher->id))
            ->assertSessionHas('success');

        $this->assertEquals(0, $voucher->fresh()->is_active);

        $this->actingAs($this->vendor)
            ->patch(route('vendor.voucher.toggle', $voucher->id))
            ->assertSessionHas('success');

        $this->assertEquals(1, $voucher->fresh()->is_active);
    }

    public function test_store_page_shows_public_vouchers_and_hides_secret_vouchers(): void
    {
        $kodePublik = 'PUBLIK' . strtoupper(uniqid());
        $kodeRahasia = 'RAHASIA' . strtoupper(uniqid());

        Voucher::create([
            'vendor_id' => $this->vendor->id,
            'kode_voucher' => $kodePublik,
            'tipe_voucher' => 'toko',
            'funding_source' => 'vendor',
            'visibilitas' => 'publik',
            'tipe_diskon' => 'nominal',
            'nilai_diskon' => 10000,
            'minimal_belanja' => 50000,
            'kuota_total' => 10,
            'tanggal_mulai' => now()->toDateString(),
            'tanggal_selesai' => now()->addDays(7)->toDateString(),
            'is_active' => 1,
        ]);

        Voucher::create([
            'vendor_id' => $this->vendor->id,
            'kode_voucher' => $kodeRahasia,
            'tipe_voucher' => 'toko',
            'funding_source' => 'vendor',
            'visibilitas' => 'rahasia',
            'tipe_diskon' => 'nominal',
            'nilai_diskon' => 25000,
            'minimal_belanja' => 100000,
            'kuota_total' => 10,
            'tanggal_mulai' => now()->toDateString(),
            'tanggal_selesai' => now()->addDays(7)->toDateString(),
            'is_active' => 1,
        ]);

        $response = $this->get(route('customer.toko.show', $this->vendor->id));
        $response->assertStatus(200);
        $response->assertSee($kodePublik);
        $response->assertDontSee($kodeRahasia);
    }

    public function test_customer_promo_page_filters_public_and_allows_secret_search(): void
    {
        $kodePublik = 'CPUB' . strtoupper(uniqid());
        $kodeRahasia = 'CSEC' . strtoupper(uniqid());

        Voucher::create([
            'vendor_id' => $this->vendor->id,
            'kode_voucher' => $kodePublik,
            'tipe_voucher' => 'toko',
            'funding_source' => 'vendor',
            'visibilitas' => 'publik',
            'tipe_diskon' => 'nominal',
            'nilai_diskon' => 5000,
            'minimal_belanja' => 20000,
            'kuota_total' => 10,
            'tanggal_mulai' => now()->toDateString(),
            'tanggal_selesai' => now()->addDays(7)->toDateString(),
            'is_active' => 1,
        ]);

        $secretVoucher = Voucher::create([
            'vendor_id' => $this->vendor->id,
            'kode_voucher' => $kodeRahasia,
            'tipe_voucher' => 'toko',
            'funding_source' => 'vendor',
            'visibilitas' => 'rahasia',
            'tipe_diskon' => 'persen',
            'nilai_diskon' => 30,
            'maksimal_diskon' => 30000,
            'minimal_belanja' => 50000,
            'kuota_total' => 10,
            'tanggal_mulai' => now()->toDateString(),
            'tanggal_selesai' => now()->addDays(7)->toDateString(),
            'is_active' => 1,
        ]);

        // Default list: voucher publik langsung muncul tanpa perlu klaim lewat toko, voucher rahasia tidak muncul
        $responseDefault = $this->actingAs($this->customer)->get(route('customer.voucher'));
        $responseDefault->assertStatus(200);
        $responseDefault->assertSee($kodePublik);
        $responseDefault->assertDontSee($kodeRahasia);

        // Pencarian dengan kode rahasia: voucher rahasia berhasil muncul
        $responseSearch = $this->actingAs($this->customer)->get(route('customer.voucher', ['kode' => $kodeRahasia]));
        $responseSearch->assertStatus(200);
        $responseSearch->assertSee($kodeRahasia);
        $responseSearch->assertSee('Voucher Berhasil Ditemukan!');

        // Klaim otomatis tersimpan di customer_vouchers
        $this->assertDatabaseHas('customer_vouchers', [
            'user_id' => $this->customer->id,
            'voucher_id' => $secretVoucher->id,
            'status' => 'aktif',
        ]);
    }

    public function test_checkout_validates_secret_voucher(): void
    {
        $kodeRahasia = 'CHKSEC' . strtoupper(uniqid());

        $voucher = Voucher::create([
            'vendor_id' => $this->vendor->id,
            'kode_voucher' => $kodeRahasia,
            'tipe_voucher' => 'toko',
            'funding_source' => 'vendor',
            'visibilitas' => 'rahasia',
            'tipe_diskon' => 'nominal',
            'nilai_diskon' => 15000,
            'minimal_belanja' => 50000,
            'kuota_total' => 10,
            'tanggal_mulai' => now()->toDateString(),
            'tanggal_selesai' => now()->addDays(7)->toDateString(),
            'is_active' => 1,
        ]);

        $response = $this->actingAs($this->customer)->postJson(route('customer.checkout.cek_voucher'), [
            'kode_voucher' => $kodeRahasia,
            'vendor_ids' => [
                $this->vendor->id => 60000,
            ],
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'potongan' => 15000,
            'vendor_id' => $this->vendor->id,
            'voucher_id' => $voucher->id,
        ]);
    }

    public function test_voucher_claim_endpoint_creates_customer_voucher(): void
    {
        $kodePublik = 'CLMPUB' . strtoupper(uniqid());

        $voucher = Voucher::create([
            'vendor_id' => $this->vendor->id,
            'kode_voucher' => $kodePublik,
            'tipe_voucher' => 'toko',
            'funding_source' => 'vendor',
            'visibilitas' => 'publik',
            'tipe_diskon' => 'nominal',
            'nilai_diskon' => 10000,
            'minimal_belanja' => 50000,
            'kuota_total' => 10,
            'tanggal_mulai' => now()->toDateString(),
            'tanggal_selesai' => now()->addDays(7)->toDateString(),
            'is_active' => 1,
        ]);

        $response = $this->actingAs($this->customer)->postJson(route('customer.voucher.claim'), [
            'kode_voucher' => $kodePublik,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'is_logged_in' => true,
        ]);

        $this->assertDatabaseHas('customer_vouchers', [
            'user_id' => $this->customer->id,
            'voucher_id' => $voucher->id,
            'status' => 'aktif',
        ]);
    }
}
