<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class GoogleAndWhatsAppAuthAuditTest extends TestCase
{
    use DatabaseTransactions;

    public function test_google_customer_without_whatsapp_can_login_directly_without_otp_prompt(): void
    {
        $password = 'Secret123!';
        $googleCustomer = User::create([
            'name' => 'Google Customer User',
            'email' => 'google_user_' . uniqid() . '@gmail.com',
            'google_id' => 'google_sub_' . uniqid(),
            'whatsapp' => null,
            'whatsapp_verified_at' => null,
            'password' => bcrypt($password),
            'role' => 'customer',
        ]);
        $googleCustomer->markEmailAsVerified();

        $response = $this->post('/login', [
            'login' => $googleCustomer->email,
            'password' => $password,
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect('/customer');
        $this->assertAuthenticatedAs($googleCustomer);
    }

    public function test_email_customer_without_whatsapp_does_not_get_trapped_in_otp_verify(): void
    {
        $password = 'Secret123!';
        $emailCustomer = User::create([
            'name' => 'Email Customer User',
            'email' => 'email_customer_' . uniqid() . '@customdomain.com',
            'whatsapp' => null,
            'whatsapp_verified_at' => null,
            'password' => bcrypt($password),
            'role' => 'customer',
        ]);
        $emailCustomer->markEmailAsVerified();

        $response = $this->post('/login', [
            'login' => $emailCustomer->email,
            'password' => $password,
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect('/customer');
        $this->assertAuthenticatedAs($emailCustomer);
    }

    public function test_trapped_google_user_on_otp_verify_page_auto_rescues_and_logs_in(): void
    {
        $googleCustomer = User::create([
            'name' => 'Trapped User',
            'email' => 'trapped_' . uniqid() . '@gmail.com',
            'google_id' => 'google_' . uniqid(),
            'whatsapp' => null,
            'whatsapp_verified_at' => null,
            'password' => bcrypt('password'),
            'role' => 'customer',
        ]);
        $googleCustomer->markEmailAsVerified();

        // Simulate trapped session on /otp/verify
        $response = $this->withSession([
            'otp_user_id' => $googleCustomer->id,
            'otp_code' => 123456,
            'otp_phone' => null,
        ])->get('/otp/verify');

        $response->assertRedirect('/customer');
        $this->assertAuthenticatedAs($googleCustomer);
    }

    public function test_trapped_google_user_resend_button_auto_rescues_and_logs_in(): void
    {
        $googleCustomer = User::create([
            'name' => 'Trapped User 2',
            'email' => 'trapped2_' . uniqid() . '@gmail.com',
            'google_id' => 'google_' . uniqid(),
            'whatsapp' => null,
            'whatsapp_verified_at' => null,
            'password' => bcrypt('password'),
            'role' => 'customer',
        ]);
        $googleCustomer->markEmailAsVerified();

        $response = $this->withSession([
            'otp_user_id' => $googleCustomer->id,
            'otp_code' => 123456,
            'otp_phone' => null,
        ])->post('/otp/resend');

        $response->assertRedirect('/customer');
        $this->assertAuthenticatedAs($googleCustomer);
    }

    public function test_whatsapp_only_customer_still_requires_whatsapp_otp(): void
    {
        $password = 'Secret123!';
        $waNumber = '08' . rand(100000000, 999999999);
        $waCustomer = User::create([
            'name' => 'WA Only Customer',
            'email' => $waNumber . '@rentify.local',
            'whatsapp' => $waNumber,
            'whatsapp_verified_at' => null,
            'password' => bcrypt($password),
            'role' => 'customer',
        ]);

        $response = $this->post('/login', [
            'login' => $waCustomer->whatsapp,
            'password' => $password,
        ]);

        $response->assertRedirect(route('otp.verify'));
        $this->assertGuest();
        $this->assertEquals($waCustomer->id, session('otp_user_id'));
        $this->assertEquals($waNumber, session('otp_phone'));
    }

    public function test_vendor_with_unverified_vendor_whatsapp_requires_otp(): void
    {
        $password = 'Secret123!';
        $vendorWa = '08' . rand(100000000, 999999999);
        $vendor = User::create([
            'name' => 'Vendor Test',
            'vendor_name' => 'Toko Audit Test',
            'email' => 'vendor_' . uniqid() . '@shop.com',
            'whatsapp_vendor' => $vendorWa,
            'whatsapp_verified_at' => null,
            'password' => bcrypt($password),
            'role' => 'vendor',
        ]);

        $response = $this->post('/login', [
            'login' => $vendor->email,
            'password' => $password,
        ]);

        $response->assertRedirect(route('otp.verify'));
        $this->assertGuest();
        $this->assertEquals($vendor->id, session('otp_user_id'));
        $this->assertEquals($vendorWa, session('otp_phone'));
    }
}
