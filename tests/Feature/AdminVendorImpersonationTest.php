<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Http\Controllers\Admin\AdminVendorController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

class AdminVendorImpersonationTest extends TestCase
{
    public function test_impersonate_and_leave_impersonate_methods_exist_and_callable(): void
    {
        $controller = new AdminVendorController();
        $this->assertTrue(method_exists($controller, 'impersonate'));
        $this->assertTrue(method_exists($controller, 'leaveImpersonate'));
        $this->assertTrue(method_exists($controller, 'show'));
    }

    public function test_impersonate_routes_are_registered(): void
    {
        $this->assertTrue(Route::has('admin.vendors.impersonate'));
        $this->assertTrue(Route::has('admin.impersonate.leave'));
    }
}
