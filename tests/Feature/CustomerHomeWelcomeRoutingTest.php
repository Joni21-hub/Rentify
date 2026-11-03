<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class CustomerHomeWelcomeRoutingTest extends TestCase
{
    use DatabaseTransactions;

    public function test_guest_cannot_access_beranda_and_sees_welcome_intro_page(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertViewIs('customer.welcome');
        $response->assertSee('Sewa Apapun yang Kamu Butuhkan');
        $response->assertSee('Marketplace Rental');
    }

    public function test_authenticated_customer_accesses_beranda_catalog(): void
    {
        $customer = User::create([
            'name' => 'Budi Customer',
            'email' => 'budi_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'customer',
            'is_active' => 1,
        ]);
        $customer->markEmailAsVerified();

        $response = $this->actingAs($customer)->get('/');

        $response->assertStatus(200);
        $response->assertViewIs('customer.home');
    }

    public function test_authenticated_vendor_can_access_beranda_catalog(): void
    {
        $vendor = User::create([
            'name' => 'Toko Rental Jaya',
            'email' => 'vendor_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'vendor',
            'vendor_name' => 'Rental Jaya Shop',
            'is_active' => 1,
        ]);
        $vendor->markEmailAsVerified();

        $response = $this->actingAs($vendor)->get('/');

        $response->assertStatus(200);
        $response->assertViewIs('customer.home');
        $this->assertEquals('customer', session('active_role'));
    }

    public function test_vendor_in_customer_dashboard_clicking_beranda_stays_in_customer_catalog(): void
    {
        $vendor = User::create([
            'name' => 'Toko Rental Sukses',
            'email' => 'vendor_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'vendor',
            'vendor_name' => 'Sukses Mandiri Rental',
            'is_active' => 1,
        ]);
        $vendor->markEmailAsVerified();

        // Kunjungi customer dashboard / account
        $dashResponse = $this->actingAs($vendor)->get('/customer/dashboard');
        $dashResponse->assertStatus(200);
        $this->assertEquals('customer', session('active_role'));

        // Klik Beranda (menuju route customer.home '/')
        $homeResponse = $this->actingAs($vendor)->get('/');
        $homeResponse->assertStatus(200);
        $homeResponse->assertViewIs('customer.home');
        $this->assertEquals('customer', session('active_role'));
    }

    public function test_authenticated_admin_redirects_to_admin_dashboard(): void
    {
        $admin = User::create([
            'name' => 'Admin Rentify',
            'email' => 'admin_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'is_active' => 1,
        ]);
        $admin->markEmailAsVerified();

        $response = $this->actingAs($admin)->get('/');

        $response->assertRedirect(route('admin.dashboard'));
    }

    public function test_mulai_menyewa_redirects_guest_to_login_and_stores_intended_beranda(): void
    {
        $response = $this->get(route('customer.mulai_menyewa'));

        $response->assertRedirect(route('login'));
        $this->assertEquals(route('customer.home'), session('url.intended'));
    }

    public function test_mulai_menyewa_redirects_authenticated_customer_directly_to_beranda(): void
    {
        $customer = User::create([
            'name' => 'Doni Customer',
            'email' => 'doni_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'customer',
            'is_active' => 1,
        ]);
        $customer->markEmailAsVerified();

        $response = $this->actingAs($customer)->get(route('customer.mulai_menyewa'));

        $response->assertRedirect(route('customer.home'));
    }

    public function test_welcome_page_mulai_menyewa_button_href_for_guest_and_authenticated_user(): void
    {
        // 1. Guest: tombol Mulai Menyewa mengarah ke /mulai-menyewa (menuju login)
        $guestResponse = $this->get('/');
        $guestResponse->assertStatus(200);
        $guestResponse->assertSee(route('customer.mulai_menyewa'));

        // 2. Authenticated Customer: tombol Mulai Menyewa pada landing preview mengarah langsung ke beranda
        $customer = User::create([
            'name' => 'Maya Customer',
            'email' => 'maya_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'customer',
            'is_active' => 1,
        ]);
        $customer->markEmailAsVerified();

        $landingResponse = $this->actingAs($customer)->get('/?landing=1');
        $landingResponse->assertStatus(200);
        $landingResponse->assertSee(route('customer.home'));
    }

    public function test_customer_logging_in_after_mulai_menyewa_lands_on_beranda(): void
    {
        $password = 'Secret123!';
        $customer = User::create([
            'name' => 'Rian Customer',
            'email' => 'rian_' . uniqid() . '@example.com',
            'password' => bcrypt($password),
            'role' => 'customer',
            'is_active' => 1,
        ]);
        $customer->markEmailAsVerified();

        // Guest klik mulai menyewa -> menuju login
        $this->get(route('customer.mulai_menyewa'));

        // Login dengan kredensial akun yang sudah ada
        $loginResponse = $this->post('/login', [
            'login' => $customer->email,
            'password' => $password,
        ]);

        $loginResponse->assertRedirect(route('customer.home'));
    }
}
