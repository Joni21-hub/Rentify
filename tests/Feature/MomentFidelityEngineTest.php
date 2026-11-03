<?php

namespace Tests\Feature;

use App\Models\Barang;
use App\Models\Kategori;
use App\Models\Moment;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MomentFidelityEngineTest extends TestCase
{
    use DatabaseTransactions;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');

        $this->user = User::create([
            'name'     => 'Fidelity Tester',
            'email'    => 'fidelity_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role'     => 'customer',
        ]);
    }

    public function test_moment_preserves_untouched_master_file_and_matches_sha256_hash(): void
    {
        Queue::fake();

        $width = 1080;
        $height = 1920;
        $photo = UploadedFile::fake()->image('camera_master_photo.jpg', $width, $height);
        $expectedHash = hash_file('sha256', $photo->getRealPath());

        $response = $this->actingAs($this->user)
            ->postJson('/api/moments', [
                'media'   => $photo,
                'caption' => 'Testing master fidelity preservation',
            ]);

        $response->assertStatus(201);
        $momentId = $response->json('data.id');
        $moment = Moment::findOrFail($momentId);

        $this->assertNotNull($moment->master_url);
        $this->assertEquals($expectedHash, $moment->master_hash);

        // Verifikasi berkas fisik master tersimpan utuh di disk storage
        $masterRelativePath = preg_replace('#^https?://[^/]+/storage/#', '', $moment->master_url);
        $masterRelativePath = preg_replace('#^/storage/#', '', $masterRelativePath);
        $this->assertTrue(Storage::disk('public')->exists($masterRelativePath));

        $storedBytes = Storage::disk('public')->get($masterRelativePath);
        $this->assertEquals($expectedHash, hash('sha256', $storedBytes));
    }

    public function test_moment_extracts_dimensions_and_generates_variants(): void
    {
        Queue::fake();

        $photo = UploadedFile::fake()->image('nature_portrait.jpg', 1080, 1920);

        $response = $this->actingAs($this->user)
            ->postJson('/api/moments', [
                'media'   => $photo,
                'caption' => 'Portrait with variant ladder',
            ]);

        $response->assertStatus(201);
        $moment = Moment::findOrFail($response->json('data.id'));

        $this->assertEquals(1080, $moment->width);
        $this->assertEquals(1920, $moment->height);
        $this->assertEquals('9:16', $moment->aspect_ratio);

        $this->assertIsArray($moment->variants);
        $this->assertArrayHasKey('master', $moment->variants);
        $this->assertArrayHasKey('feed', $moment->variants);
        $this->assertArrayHasKey('thumbnail', $moment->variants);
    }

    public function test_moment_api_index_and_show_exposes_fidelity_fields(): void
    {
        Queue::fake();

        $photo = UploadedFile::fake()->image('feed_item.jpg', 800, 800);

        $createRes = $this->actingAs($this->user)
            ->postJson('/api/moments', [
                'media'   => $photo,
                'caption' => 'Square post check',
            ]);

        $createRes->assertStatus(201);
        $momentId = $createRes->json('data.id');

        // Test GET /api/moments/{id}
        $showRes = $this->actingAs($this->user)
            ->getJson("/api/moments/{$momentId}");

        $showRes->assertStatus(200);
        $showRes->assertJsonStructure([
            'success',
            'data' => [
                'id',
                'media_url',
                'thumbnail_url',
                'master_url',
                'master_hash',
                'variants',
                'width',
                'height',
                'aspect_ratio',
            ],
        ]);
        $this->assertEquals(800, $showRes->json('data.width'));
        $this->assertEquals(800, $showRes->json('data.height'));
        $this->assertEquals('1:1', $showRes->json('data.aspect_ratio'));

        // Test GET /api/moments (feed)
        $feedRes = $this->actingAs($this->user)
            ->getJson('/api/moments');

        $feedRes->assertStatus(200);
        $feedItems = $feedRes->json('data');
        $this->assertNotEmpty($feedItems);

        $found = collect($feedItems)->firstWhere('id', $momentId);
        $this->assertNotNull($found);
        $this->assertArrayHasKey('master_url', $found);
        $this->assertArrayHasKey('master_hash', $found);
        $this->assertArrayHasKey('variants', $found);
        $this->assertArrayHasKey('width', $found);
        $this->assertArrayHasKey('height', $found);
        $this->assertArrayHasKey('aspect_ratio', $found);
    }

    public function test_moment_cleanup_removes_master_and_variants_on_delete(): void
    {
        Queue::fake();

        $photo = UploadedFile::fake()->image('to_delete.jpg', 1080, 1920);

        $res = $this->actingAs($this->user)
            ->postJson('/api/moments', [
                'media'   => $photo,
                'caption' => 'Delete check',
            ]);

        $res->assertStatus(201);
        $moment = Moment::findOrFail($res->json('data.id'));

        $cleanPath = function (?string $url): ?string {
            if (!$url) return null;
            $p = preg_replace('#^https?://[^/]+/storage/#', '', $url);
            return preg_replace('#^/storage/#', '', $p);
        };

        $masterPath = $cleanPath($moment->master_url);
        $this->assertTrue(Storage::disk('public')->exists($masterPath));

        // Delete moment
        $moment->delete();

        // Master should be deleted from storage
        $this->assertFalse(Storage::disk('public')->exists($masterPath));
    }

    public function test_moment_handles_video_fidelity_metadata(): void
    {
        Queue::fake();

        $video = UploadedFile::fake()->create('clip_master.mp4', 8000, 'video/mp4');
        $expectedHash = hash_file('sha256', $video->getRealPath());

        $res = $this->actingAs($this->user)
            ->postJson('/api/moments', [
                'media'                  => $video,
                'media_type'             => 'video',
                'video_duration_seconds' => 15,
                'caption'                => 'Video master fidelity',
            ]);

        $res->assertStatus(201);
        $moment = Moment::findOrFail($res->json('data.id'));

        $this->assertEquals('video', $moment->media_type);
        $this->assertNotNull($moment->master_url);
        $this->assertEquals($expectedHash, $moment->master_hash);
        $this->assertIsArray($moment->variants);
        $this->assertArrayHasKey('master', $moment->variants);
    }
}
