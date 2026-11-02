<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Mockery;
use Tests\TestCase;

class AdminGoogleSecurityTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        \Illuminate\Support\Facades\Auth::logout();
        session()->flush();

        // Pastikan akun admin rentify.id@gmail.com ada
        User::updateOrCreate(
            ['email' => 'rentify.id@gmail.com'],
            [
                'name' => 'Administrator Rentify',
                'role' => 'admin',
                'password' => bcrypt('SomeRandomUnhackableSecret'),
                'email_verified_at' => now(),
            ]
        );
    }

    public function test_admin_email_cannot_login_using_password_form(): void
    {
        $response = $this->from('/login')->post('/login', [
            'login' => 'rentify.id@gmail.com',
            'password' => 'SomeRandomUnhackableSecret',
        ]);

        $response->assertSessionHasErrors('login');
        $this->assertGuest();
    }

    public function test_admin_cannot_request_password_reset_or_otp(): void
    {
        $response = $this->from('/forgot-password')->post('/forgot-password', [
            'identitas' => 'rentify.id@gmail.com',
        ]);

        $response->assertSessionHas('error');
        $this->assertNull(session('reset_user_id'));
    }

    public function test_customer_cannot_access_admin_dashboard(): void
    {
        $customer = User::create([
            'name' => 'Customer Biasa',
            'email' => 'cust_' . uniqid() . '@example.com',
            'role' => 'customer',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->actingAs($customer)->get('/admin/dashboard');
        $response->assertRedirect('/home');
    }

    public function test_customer_cannot_access_admin_moderation_api(): void
    {
        $customer = User::create([
            'name' => 'Customer Biasa 2',
            'email' => 'cust2_' . uniqid() . '@example.com',
            'role' => 'customer',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->actingAs($customer)->getJson('/api/admin/moderation');
        $response->assertStatus(403);
    }

    public function test_google_callback_with_rentify_id_logs_in_as_admin(): void
    {
        $abstractUser = Mockery::mock(SocialiteUser::class);
        $abstractUser->shouldReceive('getId')->andReturn('google-admin-12345');
        $abstractUser->shouldReceive('getEmail')->andReturn('rentify.id@gmail.com');
        $abstractUser->shouldReceive('getName')->andReturn('Admin Rentify Super');

        $provider = Mockery::mock('Laravel\Socialite\Two\GoogleProvider');
        $provider->shouldReceive('redirectUrl')->andReturnSelf();
        $provider->shouldReceive('user')->andReturn($abstractUser);

        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

        $response = $this->get('/auth/google/callback');

        $response->assertRedirect('/admin/dashboard');
        $this->assertAuthenticated();
        
        $user = auth()->user();
        $this->assertEquals('rentify.id@gmail.com', $user->email);
        $this->assertEquals('admin', $user->role);
        $this->assertTrue(session('admin_auth_via_google'));
        $this->assertEquals('rentify.id@gmail.com', session('admin_authenticated_email'));
    }

    public function test_google_callback_with_non_rentify_email_never_becomes_admin(): void
    {
        $abstractUser = Mockery::mock(SocialiteUser::class);
        $abstractUser->shouldReceive('getId')->andReturn('google-regular-99999');
        $abstractUser->shouldReceive('getEmail')->andReturn('regularuser@gmail.com');
        $abstractUser->shouldReceive('getName')->andReturn('Regular User');

        $provider = Mockery::mock('Laravel\Socialite\Two\GoogleProvider');
        $provider->shouldReceive('redirectUrl')->andReturnSelf();
        $provider->shouldReceive('user')->andReturn($abstractUser);

        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

        // Simulasi agreed terms untuk pendaftaran baru
        $response = $this->withSession(['google_agreed_terms' => true])->get('/auth/google/callback');

        $response->assertRedirect('/customer');
        $this->assertAuthenticated();

        $user = auth()->user();
        $this->assertEquals('regularuser@gmail.com', $user->email);
        $this->assertNotEquals('admin', $user->role);
        $this->assertEquals('customer', $user->role);
    }

    public function test_admin_response_contains_high_security_headers(): void
    {
        /** @var User $admin */
        $admin = User::where('email', 'rentify.id@gmail.com')->firstOrFail();

        $response = $this->actingAs($admin)->withSession([
            'admin_auth_via_google' => true,
            'admin_authenticated_email' => 'rentify.id@gmail.com',
            'admin_session_ip' => '127.0.0.1',
            'admin_session_user_agent' => 'Symfony',
        ])->get('/admin/dashboard');

        $response->assertStatus(200);
        $response->assertHeader('X-Frame-Options', 'DENY');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $this->assertStringContainsString('no-store', (string)$response->headers->get('Cache-Control'));
    }
}
