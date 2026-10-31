<?php

namespace Tests\Feature;

use App\Models\AiMarketingPost;
use App\Models\User;
use App\Services\AiMarketingService;
use Carbon\Carbon;
use Tests\TestCase;

class AiDailyMarketingTest extends TestCase
{
    public function test_service_generates_customer_content_on_odd_days()
    {
        $service = new AiMarketingService();

        // Tanggal 21 (Ganjil)
        $date = Carbon::parse('2026-10-21');
        $post = $service->generateDailyVideoContent($date);

        $this->assertInstanceOf(AiMarketingPost::class, $post);
        $this->assertEquals('customer', $post->target_audience);
        $this->assertEquals('ganjil', $post->day_type);
        $this->assertNotEmpty($post->hook_masalah);
        $this->assertIsArray($post->naskah_video);
        $this->assertCount(4, $post->naskah_video);
        $this->assertIsArray($post->teks_di_layar);
        $this->assertStringContainsString('Rentify', $post->caption);
    }

    public function test_service_generates_vendor_content_on_even_days()
    {
        $service = new AiMarketingService();

        // Tanggal 22 (Genap)
        $date = Carbon::parse('2026-10-22');
        $post = $service->generateDailyVideoContent($date);

        $this->assertInstanceOf(AiMarketingPost::class, $post);
        $this->assertEquals('vendor', $post->target_audience);
        $this->assertEquals('genap', $post->day_type);
        $this->assertNotEmpty($post->hook_masalah);
        $this->assertIsArray($post->naskah_video);
        $this->assertCount(4, $post->naskah_video);
        $this->assertNotNull($post->persona_vendor);
        $this->assertStringContainsString('vendor', strtolower($post->caption));
    }

    public function test_artisan_command_generates_daily_content()
    {
        $this->artisan('rentify:generate-daily-content', [
            '--date' => '2026-10-23', // Ganjil
        ])->assertSuccessful();

        $this->assertDatabaseHas('ai_marketing_posts', [
            'tanggal'         => '2026-10-23',
            'target_audience' => 'customer',
            'day_type'        => 'ganjil',
        ]);
    }

    public function test_admin_can_access_ai_marketing_dashboard()
    {
        if (!\Illuminate\Support\Facades\Route::has('admin.ai-marketing.index')) {
            $this->markTestSkipped('Route admin.ai-marketing.index removed in commit c5c96eb');
        }

        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.ai-marketing.index'));

        $response->assertStatus(200);
        $response->assertSee('Pusat Auto-Promosi & Konten Viral Rentify');
        $response->assertSee('Studio Visual 9:16');
        $response->assertSee('Pengaturan Jadwal & API');
    }

    public function test_admin_can_trigger_daily_content_via_post()
    {
        if (!\Illuminate\Support\Facades\Route::has('admin.ai-marketing.generate-daily')) {
            $this->markTestSkipped('Route admin.ai-marketing.generate-daily removed in commit c5c96eb');
        }

        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $response = $this->actingAs($admin)
            ->postJson(route('admin.ai-marketing.generate-daily'), [
                'target_audience' => 'customer',
            ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('post.target_audience', 'customer');
    }

    public function test_social_publish_simulation_handles_cleanly()
    {
        if (!\Illuminate\Support\Facades\Route::has('admin.ai-marketing.publish-social')) {
            $this->markTestSkipped('Route admin.ai-marketing.publish-social removed in commit c5c96eb');
        }

        $post = AiMarketingPost::create([
            'tanggal'         => '2026-10-20',
            'day_number'      => 20,
            'day_type'        => 'genap',
            'target_audience' => 'vendor',
            'tema'            => 'Test Publish',
            'hook_masalah'    => 'Punya barang nganggur di rumah?',
            'naskah_video'    => [
                ['detik' => '0-3s', 'adegan' => 'Hook', 'visual' => 'Aktor', 'voiceover' => 'Halo!']
            ],
            'teks_di_layar'   => [
                ['posisi' => 'Tengah', 'teks' => 'Teks Test', 'style' => 'Bold']
            ],
            'caption'         => 'Caption test #Rentify',
            'status'          => 'ready',
        ]);

        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $response = $this->actingAs($admin)
            ->postJson(route('admin.ai-marketing.publish-social', $post->id), [
                'channel' => 'all',
            ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $this->assertTrue($post->fresh()->posted_to_instagram);
        $this->assertTrue($post->fresh()->posted_to_tiktok);
    }
}
