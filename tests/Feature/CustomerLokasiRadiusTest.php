<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CustomerLokasiRadiusTest extends TestCase
{
    public function test_lokasi_page_renders_successfully()
    {
        $response = $this->get('/lokasi');
        $response->assertStatus(200);
        $response->assertSee('Atur Titik & Jarak Lokasi', false);
        $response->assertSee('Jarak Radius Rental', false);
        $response->assertSee('input_radius', false);
    }

    public function test_lokasi_store_saves_radius_to_session_and_authenticated_user()
    {
        $user = User::factory()->create([
            'role' => 'customer',
        ]);

        $response = $this->actingAs($user)->post('/lokasi', [
            'latitude' => '-6.200000',
            'longitude' => '106.816666',
            'alamat_lengkap' => 'Jakarta Pusat, DKI Jakarta',
            'radius' => 25
        ]);

        $response->assertRedirect(route('customer.home'));
        $this->assertEquals(25, session('user_radius'));
        $this->assertEquals(25, $user->fresh()->radius);
    }

    public function test_lokasi_store_ajax_returns_json()
    {
        $response = $this->postJson('/lokasi', [
            'latitude' => '-7.250445',
            'longitude' => '112.768845',
            'alamat_lengkap' => 'Surabaya, Jawa Timur',
            'radius' => 15
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'radius' => 15,
            'alamat' => 'Surabaya, Jawa Timur'
        ]);
        $this->assertEquals(15, session('user_radius'));
    }

    public function test_lokasi_page_prefills_saved_user_radius()
    {
        $user = User::factory()->create([
            'role' => 'customer',
            'latitude' => '-6.914744',
            'longitude' => '107.609810',
            'alamat_lengkap' => 'Bandung, Jawa Barat',
            'radius' => 35
        ]);

        $response = $this->actingAs($user)->get('/lokasi');
        $response->assertStatus(200);
        $response->assertSee('value="35"', false);
    }
}
