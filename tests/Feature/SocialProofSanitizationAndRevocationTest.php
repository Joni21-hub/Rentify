<?php

namespace Tests\Feature;

use App\Models\Barang;
use App\Models\Kategori;
use App\Models\Penyewaan;
use App\Models\SocialProofReview;
use App\Models\User;
use App\Services\SocialProofService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SocialProofSanitizationAndRevocationTest extends TestCase
{
    use DatabaseTransactions;

    protected User $vendor;
    protected User $customer;
    protected Barang $barang;
    protected Penyewaan $order;

    protected function setUp(): void
    {
        parent::setUp();

        $this->vendor = User::create([
            'name' => 'Vendor Test 18.2',
            'email' => 'vendor_18_2_' . uniqid() . '@rentify.test',
            'password' => bcrypt('password'),
            'role' => 'vendor',
            'whatsapp' => '081234567891',
        ]);

        $this->customer = User::create([
            'name' => 'Customer Test 18.2',
            'email' => 'customer_18_2_' . uniqid() . '@rentify.test',
            'password' => bcrypt('password'),
            'role' => 'customer',
            'whatsapp' => '089876543211',
        ]);

        $kategori = Kategori::firstOrCreate(
            ['nama' => 'Camping'],
            ['is_active' => true]
        );

        $this->barang = Barang::create([
            'vendor_id' => $this->vendor->id,
            'kategori_id' => $kategori->id,
            'nama' => 'Flysheet 3x4 Waterproof',
            'slug' => 'flysheet-3x4-' . uniqid(),
            'deskripsi' => 'Flysheet waterproof perlengkapan tenda',
            'harga_sewa_harian' => 45000,
            'stok_total' => 10,
            'status' => 'tersedia',
        ]);

        $this->order = Penyewaan::create([
            'user_id' => $this->customer->id,
            'vendor_id' => $this->vendor->id,
            'kode_booking' => 'BK-' . strtoupper(uniqid()),
            'customer_name' => $this->customer->name,
            'customer_whatsapp' => $this->customer->whatsapp,
            'tanggal_mulai' => now(),
            'tanggal_selesai' => now()->addDays(2),
            'metode_pengambilan' => 'pickup',
            'payment_method' => 'qris',
            'total_biaya' => 90000,
            'total_price' => 90000,
            'status' => 'Selesai',
            'completed_at' => now(),
        ]);
    }

    public function test_sanitize_and_publish_social_proof_on_high_ratings_with_consent(): void
    {
        $rawMedia = [
            'https://res.cloudinary.com/rentify/image/upload/v1/customer_selfie_with_tenda.jpg',
            'https://res.cloudinary.com/rentify/image/upload/v1/car_trunk_loading.jpg',
        ];

        $rawText = 'Tenda mantap diantar ke Jl. Sudirman No. 45 Jakarta, mobil plat B 1234 XYZ, nomor supir 081298765432.';

        $review = SocialProofReview::create([
            'order_id' => $this->order->id,
            'barang_id' => $this->barang->id,
            'user_id' => $this->customer->id,
            'rating' => 5,
            'review_text' => $rawText,
            'media_urls' => $rawMedia,
            'is_consent_granted' => true,
            'status' => SocialProofReview::STATUS_PENDING_REVIEW,
        ]);

        $service = new SocialProofService();
        $result = $service->sanitizeAndPublishSocialProof($review);

        $this->assertTrue($result['published']);
        $review->refresh();

        $this->assertTrue($review->is_published);
        $this->assertEquals(SocialProofReview::STATUS_PUBLISHED, $review->status);
        $this->assertNotNull($review->consent_granted_at);
        $this->assertNotEmpty($review->sanitized_media_urls);

        // Verify blurring of human faces, addresses, and vehicle plates in media pipeline
        foreach ($review->sanitized_media_urls as $url) {
            $this->assertStringContainsString('sanitized', $url);
            $this->assertStringContainsString('blurred', $url);
        }

        // Verify text sanitization of addresses, vehicle license plates, and phone numbers
        $this->assertStringNotContainsString('Jl. Sudirman No. 45', $review->review_text);
        $this->assertStringNotContainsString('B 1234 XYZ', $review->review_text);
        $this->assertStringNotContainsString('081298765432', $review->review_text);
        $this->assertStringContainsString('[ALAMAT DISAMARKAN]', $review->review_text);
        $this->assertStringContainsString('[PLAT NOMOR DISAMARKAN]', $review->review_text);
        $this->assertStringContainsString('[NOMOR TELEPON DISAMARKAN]', $review->review_text);

        // Verify metadata records PII masking audit
        $metadata = $review->metadata ?? [];
        $this->assertTrue($metadata['pii_sanitized'] ?? false);
        $this->assertTrue($metadata['masking_details']['faces_blurred'] ?? false);
        $this->assertTrue($metadata['masking_details']['plates_blurred'] ?? false);
        $this->assertTrue($metadata['masking_details']['addresses_blurred'] ?? false);
    }

    public function test_sanitize_and_publish_social_proof_fails_without_consent(): void
    {
        $review = SocialProofReview::create([
            'order_id' => $this->order->id,
            'barang_id' => $this->barang->id,
            'user_id' => $this->customer->id,
            'rating' => 5,
            'review_text' => 'Bagus sekali!',
            'media_urls' => ['https://res.cloudinary.com/rentify/image/upload/v1/tenda.jpg'],
            'is_consent_granted' => false, // Consent NOT granted
            'status' => SocialProofReview::STATUS_PENDING_REVIEW,
        ]);

        $service = new SocialProofService();
        $result = $service->sanitizeAndPublishSocialProof($review);

        $this->assertFalse($result['published']);
        $this->assertEquals('consent_not_granted', $result['reason']);

        $review->refresh();
        $this->assertFalse($review->is_published);
        $this->assertNotEquals(SocialProofReview::STATUS_PUBLISHED, $review->status);
    }

    public function test_sanitize_and_publish_social_proof_ineligible_for_low_ratings(): void
    {
        $review = SocialProofReview::create([
            'order_id' => $this->order->id,
            'barang_id' => $this->barang->id,
            'user_id' => $this->customer->id,
            'rating' => 2, // Low rating (< 4 stars)
            'review_text' => 'Tenda bocor sedikit saat hujan deras',
            'is_consent_granted' => true,
            'status' => SocialProofReview::STATUS_PENDING_REVIEW,
        ]);

        $service = new SocialProofService();
        $result = $service->sanitizeAndPublishSocialProof($review);

        $this->assertFalse($result['published']);
        $this->assertEquals('ineligible_rating', $result['reason']);
        $this->assertTrue($result['requires_mediation']);

        $review->refresh();
        $this->assertFalse($review->is_published);
    }

    public function test_revoke_social_proof_consent_clears_cdn_invalidates_cache_and_moves_to_cold_storage(): void
    {
        Http::fake([
            'https://api.cloudflare.com/client/v4/zones/*/purge_cache' => Http::response([
                'success' => true,
                'errors' => [],
                'messages' => [],
                'result' => ['id' => 'cf-purge-' . uniqid()],
            ], 200),
        ]);

        $sanitizedUrls = [
            'https://cdn.rentify21.my.id/sanitized/reviews/photo_1_blurred.jpg',
            'https://cdn.rentify21.my.id/sanitized/reviews/photo_2_blurred.jpg',
        ];

        $review = SocialProofReview::create([
            'order_id' => $this->order->id,
            'barang_id' => $this->barang->id,
            'user_id' => $this->customer->id,
            'rating' => 5,
            'review_text' => 'Pelayanan luar biasa cepat!',
            'media_urls' => ['https://res.cloudinary.com/rentify/image/upload/v1/raw.jpg'],
            'sanitized_media_urls' => $sanitizedUrls,
            'is_consent_granted' => true,
            'consent_granted_at' => now()->subDays(2),
            'is_published' => true,
            'status' => SocialProofReview::STATUS_PUBLISHED,
        ]);

        // Seed cache for widgets
        $widgetCacheKey = "social_proof_widget_barang_{$this->barang->id}";
        Cache::put($widgetCacheKey, ['reviews' => [$review->id]], 3600);
        $this->assertTrue(Cache::has($widgetCacheKey));

        $service = new SocialProofService();
        $result = $service->revokeSocialProofConsent($review);

        $this->assertTrue($result['revoked']);
        $this->assertTrue($result['cdn_purged']);
        $this->assertTrue($result['cache_invalidated']);
        $this->assertTrue($result['cold_storage_moved']);
        $this->assertLessThanOrEqual(15, $result['sla_minutes']);

        $review->refresh();

        // 1. Unpublish and revoke consent
        $this->assertFalse($review->is_published);
        $this->assertFalse($review->is_consent_granted);
        $this->assertEquals(SocialProofReview::STATUS_REVOKED, $review->status);
        $this->assertNotNull($review->consent_revoked_at);

        // 2. Widget cache invalidated
        $this->assertFalse(Cache::has($widgetCacheKey));

        // 3. CDN Purge verification (Cloudflare API called)
        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'purge_cache');
        });

        // 4. Assets moved to private cold storage
        $metadata = $review->metadata ?? [];
        $this->assertNotNull($metadata['cold_storage_path'] ?? null);
        $this->assertNotNull($metadata['cold_storage_moved_at'] ?? null);
        $this->assertEquals('success', $metadata['cdn_purge']['status'] ?? null);
    }
}
