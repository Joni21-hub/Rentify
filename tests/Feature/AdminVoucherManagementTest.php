<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Voucher;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AdminVoucherManagementTest extends TestCase
{
    use DatabaseTransactions;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::firstOrCreate(
            ['email' => 'admin_test@rentify.id'],
            [
                'name' => 'Admin Test',
                'password' => bcrypt('password123'),
                'role' => 'admin',
                'is_admin' => 1,
            ]
        );
    }

    public function test_admin_can_view_voucher_tab_in_dashboard(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/dashboard#tab-voucher');
        $response->assertStatus(200);
        $response->assertSee('Pemantauan Terpadu Voucher');
        $response->assertSee('Tambah Voucher');
    }

    public function test_admin_can_store_manual_platform_voucher(): void
    {
        $payload = [
            'kode_voucher' => 'DISKONMANUAL50',
            'tipe_diskon' => 'persen',
            'nilai_diskon' => 50,
            'maksimal_diskon' => 100000,
            'minimal_belanja' => 50000,
            'min_quantity' => 1,
            'kuota_total' => 200,
            'anggaran_maksimal_platform' => 10000000,
            'tanggal_mulai' => '2026-01-01',
            'tanggal_selesai' => '2026-12-31',
            'visibilitas' => 'publik',
            'cakupan_produk' => 'seluruh_toko',
        ];

        $response = $this->actingAs($this->admin)->post('/admin/vouchers', $payload);

        $response->assertRedirect('/admin/dashboard#tab-voucher');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('vouchers', [
            'kode_voucher' => 'DISKONMANUAL50',
            'funding_source' => 'platform',
            'tipe_voucher' => 'platform',
            'nilai_diskon' => 50.00,
        ]);
    }

    public function test_admin_can_delete_a_voucher(): void
    {
        $voucher = Voucher::create([
            'kode_voucher' => 'DELTEST01',
            'tipe_voucher' => 'platform',
            'funding_source' => 'platform',
            'tipe_diskon' => 'nominal',
            'nilai_diskon' => 25000,
            'kuota_total' => 50,
            'tanggal_mulai' => '2026-01-01',
            'tanggal_selesai' => '2026-12-31',
            'is_active' => 1,
        ]);

        $response = $this->actingAs($this->admin)->delete("/admin/vouchers/{$voucher->id}");

        $response->assertRedirect('/admin/dashboard#tab-voucher');
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('vouchers', ['id' => $voucher->id]);
    }

    public function test_admin_can_suspend_and_activate_voucher(): void
    {
        $voucher = Voucher::create([
            'kode_voucher' => 'SWITCH01',
            'tipe_voucher' => 'platform',
            'funding_source' => 'platform',
            'tipe_diskon' => 'persen',
            'nilai_diskon' => 10,
            'kuota_total' => 50,
            'tanggal_mulai' => '2026-01-01',
            'tanggal_selesai' => '2026-12-31',
            'is_active' => 1,
        ]);

        // Suspend
        $response = $this->actingAs($this->admin)->patch("/admin/vouchers/{$voucher->id}/suspend");
        $response->assertRedirect('/admin/dashboard#tab-voucher');
        $this->assertDatabaseHas('vouchers', ['id' => $voucher->id, 'is_active' => 0, 'suspended_by_admin' => 1]);

        // Activate
        $response = $this->actingAs($this->admin)->patch("/admin/vouchers/{$voucher->id}/activate");
        $response->assertRedirect('/admin/dashboard#tab-voucher');
        $this->assertDatabaseHas('vouchers', ['id' => $voucher->id, 'is_active' => 1, 'suspended_by_admin' => 0]);
    }

    public function test_admin_can_clear_all_platform_vouchers(): void
    {
        Voucher::create([
            'kode_voucher' => 'CLR01',
            'tipe_voucher' => 'platform',
            'funding_source' => 'platform',
            'tipe_diskon' => 'persen',
            'nilai_diskon' => 10,
            'kuota_total' => 10,
            'tanggal_mulai' => '2026-01-01',
            'tanggal_selesai' => '2026-12-31',
            'is_active' => 1,
        ]);

        $response = $this->actingAs($this->admin)->delete('/admin/vouchers/clear-platform');
        $response->assertRedirect('/admin/dashboard#tab-voucher');
        $this->assertDatabaseMissing('vouchers', ['kode_voucher' => 'CLR01']);
    }

    public function test_platform_vouchers_seeder_catalog_has_exact_204_vouchers_structure(): void
    {
        $seeder = new \Database\Seeders\PlatformVouchersSeeder();
        $catalog = $seeder->getCatalog();

        $this->assertCount(204, $catalog);

        $persen = array_filter($catalog, fn($v) => $v['tipe_diskon'] === 'persen');
        $nominal = array_filter($catalog, fn($v) => $v['tipe_diskon'] === 'nominal');

        $this->assertCount(125, $persen);
        $this->assertCount(79, $nominal);

        // All percentage vouchers must enforce max_quantity and have no maksimal_diskon cap
        foreach ($persen as $v) {
            $this->assertNotNull($v['max_quantity']);
            $this->assertNull($v['maksimal_diskon']);
            $this->assertSame(1, $v['min_quantity']);
        }

        // All nominal vouchers must enforce maksimal_diskon = nilai_diskon and max_quantity null
        foreach ($nominal as $v) {
            $this->assertNull($v['max_quantity']);
            $this->assertSame($v['nilai_diskon'], $v['maksimal_diskon']);
            $this->assertGreaterThan(0, $v['nilai_diskon']);
        }
    }

    public function test_platform_vouchers_seeder_executes_successfully(): void
    {
        $seeder = new \Database\Seeders\PlatformVouchersSeeder();
        $seeder->run();

        $this->assertSame(204, Voucher::where('funding_source', 'platform')->count());
        $this->assertSame(125, Voucher::where('funding_source', 'platform')->where('tipe_diskon', 'persen')->count());
        $this->assertSame(79, Voucher::where('funding_source', 'platform')->where('tipe_diskon', 'nominal')->count());
    }
}
