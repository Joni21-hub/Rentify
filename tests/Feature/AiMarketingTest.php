<?php

namespace Tests\Feature;

use App\Models\Barang;
use App\Models\Kategori;
use App\Models\User;
use App\Models\Voucher;
use App\Services\AiMarketingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AiMarketingTest extends TestCase
{
    public function test_ai_marketing_service_generates_copywriting()
    {
        $service = new AiMarketingService();

        $productData = [
            'nama'        => 'Kamera Sony A7 IV Fullset',
            'kategori'    => 'Kamera & Lensa',
            'harga_sewa'  => 350000,
            'deskripsi'   => 'Kamera mirrorless full-frame profesional untuk event dan video.',
            'diskon'      => 'Diskon 15% kode SEWAASIK',
        ];

        $result = $service->generateCopywriting($productData, 'all', 'fomo', 'Termasuk 2 baterai cadangan');

        $this->assertIsArray($result);
        $this->assertArrayHasKey('whatsapp', $result);
        $this->assertArrayHasKey('instagram', $result);
        $this->assertArrayHasKey('tiktok', $result);
        $this->assertArrayHasKey('banner', $result);
        $this->assertStringContainsString('Kamera Sony A7 IV Fullset', $result['whatsapp']);
        $this->assertStringContainsString('Rentify', $result['instagram']);
    }

    public function test_ai_marketing_service_generates_admin_campaign()
    {
        $service = new AiMarketingService();

        $result = $service->generateAdminCampaign('Promo Mudik Seru', 'Pemudik & Keluarga', 'Diskon 25%');

        $this->assertIsArray($result);
        $this->assertArrayHasKey('judul_kampanye', $result);
        $this->assertArrayHasKey('slogan', $result);
        $this->assertArrayHasKey('banner_promo', $result);
        $this->assertArrayHasKey('broadcast_notifikasi', $result);
        $this->assertStringContainsString('Promo Mudik Seru', $result['judul_kampanye']);
    }

    public function test_vendor_can_generate_copy_via_post_endpoint()
    {
        $vendor = User::factory()->create([
            'role'          => 'vendor',
            'vendor_status' => 'approved',
        ]);

        $response = $this->actingAs($vendor)
            ->postJson(route('vendor.ai-marketing.generate-copy'), [
                'manual_nama'     => 'PlayStation 5 + 2 Stik',
                'manual_kategori' => 'Gaming & Konsol',
                'manual_harga'    => 120000,
                'platform'        => 'whatsapp',
                'tone'            => 'fomo',
            ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonStructure([
            'success',
            'data' => ['whatsapp', 'instagram', 'tiktok'],
            'product'
        ]);
    }

    public function test_vendor_can_auto_apply_voucher_recommended_by_ai()
    {
        $vendor = User::factory()->create([
            'role'          => 'vendor',
            'vendor_status' => 'approved',
        ]);

        $voucherCode = 'AIPROMO' . rand(100, 999);

        $response = $this->actingAs($vendor)
            ->postJson(route('vendor.ai-marketing.auto-apply-voucher'), [
                'kode_voucher'    => $voucherCode,
                'tipe_diskon'     => 'persen',
                'nilai_diskon'    => 20,
                'minimal_belanja' => 100000,
                'maksimal_diskon' => 50000,
                'durasi_hari'     => 14,
            ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);

        $this->assertDatabaseHas('vouchers', [
            'vendor_id'    => $vendor->id,
            'kode_voucher' => $voucherCode,
            'nilai_diskon' => 20,
            'is_active'    => 1,
        ]);
    }
}
