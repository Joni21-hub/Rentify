<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class MultiRoleAuthTest extends TestCase
{
    use DatabaseTransactions;

    public function test_admin_redirects_to_admin_dashboard(): void
    {
        $admin = User::create([
            'name' => 'Admin Test',
            'email' => 'admin_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'is_active' => 1,
        ]);
        $admin->markEmailAsVerified();

        $response = $this->actingAs($admin)->get('/login');
        // Saat sudah login, akses login dialihkan via controller
        $redirectResponse = (new \App\Http\Controllers\AuthController())->redirectByRole();
        $this->assertEquals(url('/admin/dashboard'), $redirectResponse->getTargetUrl());
    }

    public function test_vendor_redirects_to_vendor_dashboard(): void
    {
        $vendor = User::create([
            'name' => 'Vendor Pure',
            'email' => 'vendor_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'vendor',
            'vendor_name' => null, // Pure vendor role
            'is_active' => 1,
        ]);
        $vendor->markEmailAsVerified();

        $this->actingAs($vendor);
        $redirectResponse = (new \App\Http\Controllers\AuthController())->redirectByRole();
        $this->assertEquals(url('/vendor/dashboard'), $redirectResponse->getTargetUrl());
    }

    public function test_customer_redirects_to_customer_home(): void
    {
        $customer = User::create([
            'name' => 'Customer Pure',
            'email' => 'customer_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'customer',
            'vendor_name' => null,
            'is_active' => 1,
        ]);
        $customer->markEmailAsVerified();

        $this->actingAs($customer);
        $redirectResponse = (new \App\Http\Controllers\AuthController())->redirectByRole();
        $this->assertEquals(url('/customer'), $redirectResponse->getTargetUrl());
    }

    public function test_dual_role_user_without_session_redirects_to_select_role_route(): void
    {
        $dualUser = User::create([
            'name' => 'Dual Role User',
            'email' => 'dual_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'customer',
            'vendor_name' => 'Toko Dual Role',
            'is_active' => 1,
        ]);
        $dualUser->markEmailAsVerified();
        $this->assertTrue($dualUser->hasDualRole());

        $this->actingAs($dualUser);
        session()->forget('active_role');

        $redirectResponse = (new \App\Http\Controllers\AuthController())->redirectByRole();
        $this->assertEquals(route('role.select'), $redirectResponse->getTargetUrl());
        $this->assertEquals(url('/select-role'), $redirectResponse->getTargetUrl());
    }

    public function test_dual_role_user_with_active_role_vendor_redirects_to_vendor_dashboard(): void
    {
        $dualUser = User::create([
            'name' => 'Dual Role User 2',
            'email' => 'dual2_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'customer',
            'vendor_name' => 'Toko Dual Role 2',
            'is_active' => 1,
        ]);
        $dualUser->markEmailAsVerified();

        $this->actingAs($dualUser);
        session(['active_role' => 'vendor']);

        $redirectResponse = (new \App\Http\Controllers\AuthController())->redirectByRole();
        $this->assertEquals(url('/vendor/dashboard'), $redirectResponse->getTargetUrl());
    }

    public function test_dual_role_user_with_active_role_customer_redirects_to_customer_home(): void
    {
        $dualUser = User::create([
            'name' => 'Dual Role User 3',
            'email' => 'dual3_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'customer',
            'vendor_name' => 'Toko Dual Role 3',
            'is_active' => 1,
        ]);
        $dualUser->markEmailAsVerified();

        $this->actingAs($dualUser);
        session(['active_role' => 'customer']);

        $redirectResponse = (new \App\Http\Controllers\AuthController())->redirectByRole();
        $this->assertEquals(url('/customer'), $redirectResponse->getTargetUrl());
    }
}
