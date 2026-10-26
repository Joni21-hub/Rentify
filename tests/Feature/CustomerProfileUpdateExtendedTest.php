<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class CustomerProfileUpdateExtendedTest extends TestCase
{
    use DatabaseTransactions;

    public function test_customer_can_update_extended_profile_fields(): void
    {
        $customer = User::create([
            'name' => 'Joni Utama',
            'email' => 'jutama@example.com',
            'password' => bcrypt('secret123'),
            'role' => 'customer',
        ]);

        $response = $this->actingAs($customer)->post(route('customer.settings.profile.update'), [
            'name' => 'Joni Utama Edited',
            'bio' => 'Hobi hiking & fotografi outdoor 🏕️',
            'whatsapp' => '081234567890',
            'instagram_username' => '@joni.utama',
            'jenis_kelamin' => 'Laki-laki',
            'tanggal_lahir' => '1998-05-20',
            'alamat_lengkap' => 'Jl. Merdeka No. 45, Jakarta Selatan',
        ]);

        $response->assertRedirect(route('customer.settings'));
        $response->assertSessionHas('success');

        $customer->refresh();
        $this->assertEquals('Joni Utama Edited', $customer->name);
        $this->assertEquals('Hobi hiking & fotografi outdoor 🏕️', $customer->bio);
        $this->assertEquals('081234567890', $customer->whatsapp);
        $this->assertEquals('joni.utama', $customer->instagram_username); // @ stripped
        $this->assertEquals('Laki-laki', $customer->jenis_kelamin);
        $this->assertEquals('1998-05-20', $customer->tanggal_lahir);
        $this->assertEquals('Jl. Merdeka No. 45, Jakarta Selatan', $customer->alamat_lengkap);
    }
}
