<?php

namespace Tests\Feature;

use App\Models\AiMarketingPost;
use App\Services\AiMarketingService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class AiMarketingTest extends TestCase
{
    use RefreshDatabase;

    public function test_ai_marketing_service_generates_daily_customer_video_content()
    {
        $service = new AiMarketingService();

        // Tanggal ganjil -> target customer
        $date = Carbon::parse('2026-10-21');
        $post = $service->generateDailyVideoContent($date);

        $this->assertInstanceOf(AiMarketingPost::class, $post);
        $this->assertEquals('customer', $post->target_audience);
        $this->assertNotEmpty($post->hook_masalah);
        $this->assertIsArray($post->naskah_video);
        $this->assertCount(4, $post->naskah_video);
        $this->assertStringContainsString('rentify21.my.id', $post->caption);
    }

    public function test_ai_marketing_service_generates_daily_vendor_video_content()
    {
        $service = new AiMarketingService();

        // Tanggal genap -> target vendor
        $date = Carbon::parse('2026-10-22');
        $post = $service->generateDailyVideoContent($date);

        $this->assertInstanceOf(AiMarketingPost::class, $post);
        $this->assertEquals('vendor', $post->target_audience);
        $this->assertNotEmpty($post->hook_masalah);
        $this->assertIsArray($post->naskah_video);
        $this->assertStringContainsString('rentify21.my.id', $post->caption);
    }

    public function test_ai_marketing_service_publishes_to_all_platforms()
    {
        $service = new AiMarketingService();
        $date = Carbon::parse('2026-10-23');
        $post = $service->generateDailyVideoContent($date);

        $results = $service->publishToAllPlatforms($post, ['instagram', 'tiktok', 'facebook']);

        $this->assertArrayHasKey('instagram', $results);
        $this->assertArrayHasKey('tiktok', $results);
        $this->assertArrayHasKey('facebook', $results);

        $this->assertTrue($results['instagram']['success']);
        $this->assertTrue($results['tiktok']['success']);
        $this->assertTrue($results['facebook']['success']);

        $post->refresh();
        $this->assertTrue($post->posted_to_instagram);
        $this->assertTrue($post->posted_to_tiktok);
        $this->assertTrue($post->posted_to_facebook);
        $this->assertNotNull($post->instagram_post_id);
        $this->assertNotNull($post->tiktok_post_id);
        $this->assertNotNull($post->facebook_post_id);
    }

    public function test_console_command_generate_daily_content_with_publish()
    {
        $exitCode = Artisan::call('rentify:generate-daily-content', [
            '--date'      => '2026-10-25',
            '--publish'   => true,
            '--platform'  => 'all',
        ]);

        $this->assertEquals(0, $exitCode);

        $latestPost = AiMarketingPost::latest('id')->first();
        $this->assertNotNull($latestPost);
        $this->assertTrue($latestPost->posted_to_instagram);
        $this->assertTrue($latestPost->posted_to_tiktok);
        $this->assertTrue($latestPost->posted_to_facebook);
    }
}
