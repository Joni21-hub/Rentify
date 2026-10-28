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
}
