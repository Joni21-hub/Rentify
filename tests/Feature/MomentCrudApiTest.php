<?php

namespace Tests\Feature;

use App\Jobs\ProcessAiMomentTaggingJob;
use App\Models\Barang;
use App\Models\Kategori;
use App\Models\Moment;
use App\Models\MomentAiTagSuggestion;
use App\Models\MomentCoinLedger;
use App\Models\MomentTaggedProduct;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class MomentCrudApiTest extends TestCase
{
    use DatabaseTransactions;

    protected User $author;
    protected User $otherUser;
    protected User $admin;
    protected Barang $barang1;
    protected Barang $barang2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->author = User::create([
            'name'     => 'Author User',
            'email'    => 'author_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role'     => 'customer',
        ]);

        $this->otherUser = User::create([
            'name'     => 'Other User',
            'email'    => 'other_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role'     => 'customer',
        ]);

        $this->admin = User::create([
            'name'     => 'Admin User',
            'email'    => 'admin_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role'     => 'admin',
        ]);

        $vendor = User::create([
            'name'        => 'Vendor User',
            'email'       => 'vendor_' . uniqid() . '@example.com',
            'password'    => bcrypt('password'),
            'role'        => 'vendor',
            'vendor_name' => 'Kamera Rental Shop',
        ]);

        $kategori = Kategori::create([
            'nama'      => 'Kamera & Lensa',
            'slug'      => 'kamera-lensa-' . uniqid(),
            'is_active' => 1,
        ]);

        $this->barang1 = Barang::create([
            'vendor_id'         => $vendor->id,
            'kategori_id'       => $kategori->id,
            'nama'              => 'Sony A7IV Mirrorless Camera',
            'slug'              => 'sony-a7iv-' . uniqid(),
            'deskripsi'         => 'Kamera fullframe profesional',
            'harga_sewa_harian' => 250000,
            'deposit'           => 500000,
            'denda_per_hari'    => 100000,
            'stok_total'        => 3,
            'status'            => 'tersedia',
            'is_approved'       => 1,
        ]);

        $this->barang2 = Barang::create([
            'vendor_id'         => $vendor->id,
            'kategori_id'       => $kategori->id,
            'nama'              => 'Lensa Sony FE 24-70mm GM II',
            'slug'              => 'lensa-sony-24-70-' . uniqid(),
            'deskripsi'         => 'Lensa zoom standar tajam',
            'harga_sewa_harian' => 180000,
            'deposit'           => 300000,
            'denda_per_hari'    => 50000,
            'stok_total'        => 2,
            'status'            => 'tersedia',
            'is_approved'       => 1,
        ]);
    }

    public function test_media_validation_rejects_unsupported_file_types(): void
    {
        $file = UploadedFile::fake()->create('document.pdf', 500, 'application/pdf');

        $response = $this->actingAs($this->author)
            ->postJson('/api/moments', [
                'media'   => $file,
                'caption' => 'Testing pdf upload',
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['media']);
    }

    public function test_media_validation_allows_large_photo(): void
    {
        Queue::fake();

        // 15 MB = 15360 KB (sebelumnya dibatasi 10MB, sekarang bebas)
        $photo = UploadedFile::fake()->create('large_photo.jpg', 15360, 'image/jpeg');

        $response = $this->actingAs($this->author)
            ->postJson('/api/moments', [
                'media'   => $photo,
                'caption' => 'Foto resolusi tinggi tanpa batasan ukuran',
            ]);

        $response->assertStatus(201);
        $this->assertTrue($response->json('success'));
    }

    public function test_media_validation_allows_large_video(): void
    {
        Queue::fake();

        // 60 MB = 61440 KB (sebelumnya dibatasi 50MB, sekarang bebas)
        $video = UploadedFile::fake()->create('large_video.mp4', 61440, 'video/mp4');

        $response = $this->actingAs($this->author)
            ->postJson('/api/moments', [
                'media'      => $video,
                'media_type' => 'video',
                'caption'    => 'Video resolusi tinggi tanpa batasan ukuran',
            ]);

        $response->assertStatus(201);
        $this->assertTrue($response->json('success'));
    }

    public function test_media_validation_allows_video_duration_greater_than_30s(): void
    {
        Queue::fake();

        $video = UploadedFile::fake()->create('sample_video.mp4', 10000, 'video/mp4');

        $response = $this->actingAs($this->author)
            ->postJson('/api/moments', [
                'media'                  => $video,
                'media_type'             => 'video',
                'video_duration_seconds' => 45,
                'caption'                => 'Video durasi 45 detik bebas tanpa batasan durasi',
            ]);

        $response->assertStatus(201);
        $this->assertTrue($response->json('success'));
    }

    public function test_store_moment_successfully_uploads_photo_with_tags_and_dispatches_job(): void
    {
        Queue::fake();

        $photo = UploadedFile::fake()->image('camera_moment.jpg', 800, 600);

        $response = $this->actingAs($this->author)
            ->postJson('/api/moments', [
                'media'       => $photo,
                'caption'     => 'Hasil foto kamera Sony A7IV sewa di Rentify!',
                'product_ids' => [$this->barang1->id, $this->barang2->id],
            ]);

        $response->assertStatus(201);
        $resData = $response->json();
        $this->assertTrue($resData['success']);
        $this->assertEquals('Momen berhasil dibagikan!', $resData['message']);

        $this->assertTrue(DB::table('moments')->where([
            'user_id' => $this->author->id,
            'caption' => 'Hasil foto kamera Sony A7IV sewa di Rentify!',
            'status'  => 'active',
        ])->exists());

        $momentId = $response->json('data.id');
        $this->assertTrue(DB::table('moment_tagged_products')->where([
            'moment_id'     => $momentId,
            'product_id'    => $this->barang1->id,
            'display_order' => 1,
        ])->exists());
        $this->assertTrue(DB::table('moment_tagged_products')->where([
            'moment_id'     => $momentId,
            'product_id'    => $this->barang2->id,
            'display_order' => 2,
        ])->exists());

        Queue::assertPushed(ProcessAiMomentTaggingJob::class, function ($job) use ($momentId) {
            return $job->momentId === $momentId;
        });
    }

    public function test_store_moment_handles_server_side_fallback_heic_transcoding(): void
    {
        Queue::fake();

        $heicFile = UploadedFile::fake()->create('iphone_photo.heic', 3000, 'image/heic');

        $response = $this->actingAs($this->author)
            ->postJson('/api/moments', [
                'media'   => $heicFile,
                'caption' => 'Foto HEIC dari iPhone',
            ]);

        $response->assertStatus(201);
        $mediaUrl = $response->json('data.media_url');
        $this->assertNotNull($mediaUrl);
        $this->assertStringEndsWith('.jpg', $mediaUrl);
    }

    public function test_store_moment_successfully_uploads_multiple_photos(): void
    {
        Queue::fake();

        $photo1 = UploadedFile::fake()->image('photo_1.jpg', 800, 600);
        $photo2 = UploadedFile::fake()->image('photo_2.png', 800, 600);
        $photo3 = UploadedFile::fake()->image('photo_3.webp', 800, 600);

        $response = $this->actingAs($this->author)
            ->postJson('/api/moments', [
                'media'   => [$photo1, $photo2, $photo3],
                'caption' => 'Postingan Carousel 3 Foto Keren',
            ]);

        $response->assertStatus(201);
        $this->assertTrue($response->json('success'));

        $mediaUrls = $response->json('data.media_urls');
        $this->assertIsArray($mediaUrls);
        $this->assertCount(3, $mediaUrls);
        $this->assertEquals($response->json('data.media_url'), $mediaUrls[0]);
    }

    public function test_store_moment_rejects_more_than_10_photos(): void
    {
        $photos = [];
        for ($i = 0; $i < 11; $i++) {
            $photos[] = UploadedFile::fake()->image("photo_{$i}.jpg", 100, 100);
        }

        $response = $this->actingAs($this->author)
            ->postJson('/api/moments', [
                'media' => $photos,
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['media']);
    }

    public function test_store_moment_rejects_video_in_multi_upload(): void
    {
        $photo = UploadedFile::fake()->image('photo_1.jpg', 800, 600);
        $video = UploadedFile::fake()->create('clip.mp4', 1000, 'video/mp4');

        $response = $this->actingAs($this->author)
            ->postJson('/api/moments', [
                'media' => [$photo, $video],
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['media']);
    }

    public function test_author_authorization_for_updating_caption_and_product_tags(): void
    {
        $moment = Moment::create([
            'user_id'   => $this->author->id,
            'media_url' => 'https://res.cloudinary.com/rentify/image/upload/v1/test.jpg',
            'caption'   => 'Caption Lama',
            'status'    => 'active',
        ]);

        // Non-author mencoba update
        $forbiddenResponse = $this->actingAs($this->otherUser)
            ->putJson("/api/moments/{$moment->id}", [
                'caption' => 'Hacked Caption',
            ]);

        $forbiddenResponse->assertStatus(403);
        $this->assertEquals('Caption Lama', $moment->fresh()->caption);

        // Author yang sah melakukan update
        $successResponse = $this->actingAs($this->author)
            ->putJson("/api/moments/{$moment->id}", [
                'caption'     => 'Caption Baru yang Sudah Diperbaiki',
                'product_ids' => [$this->barang1->id],
            ]);

        $successResponse->assertStatus(200);
        $this->assertEquals('Caption Baru yang Sudah Diperbaiki', $moment->fresh()->caption);
        $this->assertTrue(DB::table('moment_tagged_products')->where([
            'moment_id'  => $moment->id,
            'product_id' => $this->barang1->id,
        ])->exists());
    }

    public function test_author_authorization_for_deleting_moment_and_clawback(): void
    {
        $moment = Moment::create([
            'user_id'   => $this->author->id,
            'media_url' => 'https://res.cloudinary.com/rentify/image/upload/v1/test2.jpg',
            'status'    => 'active',
        ]);

        // Simulasikan 1 koin tersimpan di lot postingan ini
        MomentCoinLedger::create([
            'user_id'               => $this->author->id,
            'interactor_id'         => $this->otherUser->id,
            'moment_id'             => $moment->id,
            'interaction_type'      => 'like_mint',
            'coins_amount'          => 1,
            'remaining_lot_balance' => 1,
            'minted_coin'           => true,
        ]);

        // User lain mencoba menghapus
        $forbiddenResponse = $this->actingAs($this->otherUser)
            ->deleteJson("/api/moments/{$moment->id}");

        $forbiddenResponse->assertStatus(403);

        // Author menghapus postingan
        $successResponse = $this->actingAs($this->author)
            ->deleteJson("/api/moments/{$moment->id}");

        $successResponse->assertStatus(200);
        $this->assertEquals('deleted', $moment->fresh()->status);

        // Verifikasi koin ditarik kembali (remaining_lot_balance = 0)
        $lot = MomentCoinLedger::where('moment_id', $moment->id)
            ->where('interaction_type', 'like_mint')
            ->first();
        $this->assertEquals(0, $lot->remaining_lot_balance);
    }

    public function test_admin_can_delete_any_moment(): void
    {
        $moment = Moment::create([
            'user_id'   => $this->author->id,
            'media_url' => 'https://res.cloudinary.com/rentify/image/upload/v1/test3.jpg',
            'status'    => 'active',
        ]);

        $adminResponse = $this->actingAs($this->admin)
            ->deleteJson("/api/moments/{$moment->id}");

        $adminResponse->assertStatus(200);
        $this->assertEquals('deleted', $moment->fresh()->status);
    }

    public function test_suggestions_and_reprocess_ai_tag_endpoints(): void
    {
        Queue::fake();

        $moment = Moment::create([
            'user_id'   => $this->author->id,
            'media_url' => 'https://res.cloudinary.com/rentify/image/upload/v1/test4.jpg',
            'status'    => 'active',
        ]);

        MomentAiTagSuggestion::create([
            'moment_id'        => $moment->id,
            'product_id'       => $this->barang1->id,
            'confidence_score' => 0.9500,
            'expires_at'       => now()->addDays(7),
        ]);

        // Test GET suggestions
        $suggestionsResponse = $this->getJson("/api/moments/{$moment->id}/suggestions");
        $suggestionsResponse->assertStatus(200);
        $suggestionsResponse->assertJsonCount(1, 'data');
        $this->assertEquals($this->barang1->id, $suggestionsResponse->json('data.0.product_id'));

        // Test non-author triggers reprocess ai tag
        $forbiddenReprocess = $this->actingAs($this->otherUser)
            ->postJson("/api/moments/{$moment->id}/ai-tag");
        $forbiddenReprocess->assertStatus(403);

        // Test author triggers reprocess ai tag
        $successReprocess = $this->actingAs($this->author)
            ->postJson("/api/moments/{$moment->id}/ai-tag");
        $successReprocess->assertStatus(202);

        Queue::assertPushed(ProcessAiMomentTaggingJob::class, function ($job) use ($moment) {
            return $job->momentId === $moment->id;
        });
    }
}
