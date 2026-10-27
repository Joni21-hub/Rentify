<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class VendorDashboardAccessTest extends TestCase
{
    use DatabaseTransactions;

    public function test_customer_redirect_chain(): void
    {
        $customer = User::create([
            'name' => 'Customer Biasa',
            'email' => 'cust_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'customer',
            'vendor_name' => null,
            'is_active' => 1,
        ]);
        $customer->markEmailAsVerified();

        // 1. Customer accesses /vendor/dashboard
        $response = $this->actingAs($customer)->get('/vendor/dashboard');
        $this->assertEquals(302, $response->getStatusCode());
        echo "\n1. /vendor/dashboard redirected to: " . $response->headers->get('Location') . "\n";

        // 2. Customer follows redirect to /home
        $response2 = $this->actingAs($customer)->get('/home');
        echo "2. /home status: " . $response2->getStatusCode() . ", target: " . $response2->headers->get('Location') . "\n";

        // 3. Follow redirect from /home (which should go to /customer)
        $response3 = $this->actingAs($customer)->get($response2->headers->get('Location'));
        echo "3. Target status: " . $response3->getStatusCode() . "\n";
    }
}
