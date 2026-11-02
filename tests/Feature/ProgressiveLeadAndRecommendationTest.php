<?php

namespace Tests\Feature;

use App\Models\Barang;
use App\Models\ChatSession;
use App\Models\Kategori;
use App\Models\User;
use App\Services\ContextGroundingService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ProgressiveLeadAndRecommendationTest extends TestCase
{
    use DatabaseTransactions;

    protected ContextGroundingService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ContextGroundingService();
    }

    /**
     * Requirement 3.2: Anonymous catalog inquiries without forced sign-up.
     */
    public function test_anonymous_catalog_exploration_without_forced_signup(): void
    {
        // 1. Arrange: Seed a category and rental items
        $vendor = User::create([
            'name' => 'Vendor Fotografi',
            'email' => 'vendor_foto_' . uniqid() . '@test.com',
            'password' => bcrypt('password'),
            'role' => 'vendor',
            'vendor_status' => 'approved',
            'vendor_name' => 'Jogja Gear Rental',
            'whatsapp' => '0812' . rand(10000000, 99999999),
        ]);

        $kategori = Kategori::firstOrCreate(['nama' => 'Fotografi & Videografi'], ['is_active' => true]);

        $barang1 = Barang::create([
            'vendor_id' => $vendor->id,
            'kategori_id' => $kategori->id,
            'nama' => 'Sony A7 IV Kit Lens',
            'slug' => 'sony-a7-iv-kit-' . uniqid(),
            'deskripsi' => 'Kamera mirrorless hybrid foto video',
            'harga_sewa_harian' => 150000,
            'deposit' => 100000,
            'kondisi' => 'Sangat Baik',
            'stok_total' => 2,
            'status' => 'tersedia',
        ]);

        $barang2 = Barang::create([
            'vendor_id' => $vendor->id,
            'kategori_id' => $kategori->id,
            'nama' => 'Fujifilm X-T4 Mirrorless',
            'slug' => 'fujifilm-xt4-' . uniqid(),
            'deskripsi' => 'Kamera warna sinematik fujifilm',
            'harga_sewa_harian' => 120000,
            'deposit' => 100000,
            'kondisi' => 'Baik',
            'stok_total' => 1,
            'status' => 'tersedia',
        ]);

        // 2. Act: Anonymous guest queries dynamic grounding context without login
        $context = $this->service->getCustomerGroundingContext();

        // 3. Assert: DTO correctly recognizes guest without authentication exception
        $this->assertFalse($context->isAuthenticated);
        $this->assertTrue($context->isGuest());
        $this->assertEquals('guest', $context->primaryRole);
        $this->assertEquals(['guest'], $context->roles);
        $this->assertNull($context->userId);

        // System prompt formats gracefully for guest
        $prompt = $context->toPromptContext();
        $this->assertStringContainsString('Tamu Anonim (Guest/Prospek Lead', $prompt);

        // Act: Anonymous guest searches catalog via keyword
        $searchResults = $this->service->searchCatalog('Sony A7');
        $this->assertGreaterThanOrEqual(1, $searchResults['total_found']);
        $this->assertEquals('Sony A7', $searchResults['keyword']);
        $this->assertEquals($barang1->id, $searchResults['items'][0]['id']);

        // Assert 5% customer markup calculation
        $expectedCustomerRate = round(150000 * 1.05);
        $this->assertEquals($expectedCustomerRate, $searchResults['items'][0]['harga_sewa_customer']);

        // Act: Search with price filter
        $budgetSearch = $this->service->searchCatalog('', ['max_price' => 130000, 'kategori_id' => $kategori->id]);
        $foundIds = array_column($budgetSearch['items'], 'id');
        $this->assertContains($barang2->id, $foundIds);
        $this->assertNotContains($barang1->id, $foundIds);
    }

    /**
     * Requirement 3.5: Progressive lead profiling without interrupting conversation flows.
     */
    public function test_progressive_lead_profile_capture_step_by_step(): void
    {
        $sessionToken = 'test_guest_session_' . uniqid();

        // Step 1: Guest shares name & interest in a product
        $step1 = $this->service->captureLeadProgressively($sessionToken, [
            'name' => 'Dimas Arya',
            'intent' => 'rent_camera',
            'item' => 'Sony A7 IV',
            'category' => 'Fotografi',
        ]);

        $this->assertTrue($step1['success']);
        $this->assertEquals('Dimas Arya', $step1['customer_name']);
        $this->assertEquals('Sony A7 IV', $step1['lead_profile']['item']);
        $this->assertEquals('rent_camera', $step1['lead_profile']['intent']);
        $this->assertFalse($step1['is_complete']);

        // Step 2: Guest specifies rental duration ("3 hari")
        $step2 = $this->service->captureLeadProgressively($sessionToken, [
            'duration' => '3 hari',
        ]);

        $this->assertTrue($step2['success']);
        // Verify previously captured attributes are preserved
        $this->assertEquals('Dimas Arya', $step2['lead_profile']['name']);
        $this->assertEquals('Sony A7 IV', $step2['lead_profile']['item']);
        $this->assertEquals(3, $step2['lead_profile']['duration_days']);
        $this->assertEquals('3 hari', $step2['lead_profile']['duration_text']);
        $this->assertFalse($step2['is_complete']);

        // Step 3: Guest provides budget ("Rp 500.000") and contact number ("081234567899")
        $step3 = $this->service->captureLeadProgressively($sessionToken, [
            'budget' => 'Rp 500.000',
            'contact' => '081234567899',
            'email' => 'dimas@example.test',
        ]);

        $this->assertTrue($step3['success']);
        $this->assertEquals(500000, $step3['lead_profile']['budget_amount']);
        $this->assertEquals('081234567899', $step3['lead_profile']['contact']);
        $this->assertEquals('dimas@example.test', $step3['lead_profile']['email']);
        $this->assertEquals('Dimas Arya', $step3['lead_profile']['name']);
        $this->assertEquals(3, $step3['lead_profile']['duration_days']);
        // Core fields now captured -> profile complete
        $this->assertTrue($step3['is_complete']);

        // Verify ChatSession record persisted in database
        $savedSession = ChatSession::where('session_token', $sessionToken)->first();
        $this->assertNotNull($savedSession);
        $this->assertEquals('Dimas Arya', $savedSession->customer_name);
        $this->assertEquals('081234567899', $savedSession->phone_number);
        $this->assertIsArray($savedSession->metadata);
        $this->assertArrayHasKey('lead_profile', $savedSession->metadata);

        // Step 4: Grounding context includes captured lead profile in LLM instructions
        $groundedContext = $this->service->getCustomerGroundingContext(
            options: ['session_token' => $sessionToken]
        );

        $this->assertNotNull($groundedContext->leadProfile);
        $this->assertEquals('Dimas Arya', $groundedContext->leadProfile['name']);

        $promptContext = $groundedContext->toPromptContext();
        $this->assertStringContainsString('PROFIL PROSPEK LEAD TERTANGKAP', $promptContext);
        $this->assertStringContainsString('Dimas Arya', $promptContext);
        $this->assertStringContainsString('081234567899', $promptContext);
    }

    /**
     * Progressive lead profiling via WhatsApp phone identifier.
     */
    public function test_progressive_lead_capture_via_whatsapp_phone_identifier(): void
    {
        $phone = '0813' . rand(10000000, 99999999);

        $result = $this->service->captureLeadProgressively($phone, [
            'name' => 'Bambang Sudirman',
            'duration' => 7,
            'budget' => 1200000,
            'intent' => 'camping_gear',
        ]);

        $this->assertTrue($result['success']);
        $this->assertNotNull($result['session_token']);
        $this->assertStringStartsWith('wa_', $result['session_token']);
        $this->assertEquals('Bambang Sudirman', $result['customer_name']);
        $this->assertEquals($phone, $result['phone_number']);
        $this->assertEquals(7, $result['lead_profile']['duration_days']);
        $this->assertEquals(1200000, $result['lead_profile']['budget_amount']);

        // ChatSession created with channel = 'whatsapp'
        $session = ChatSession::where('session_token', $result['session_token'])->first();
        $this->assertNotNull($session);
        $this->assertEquals('whatsapp', $session->channel);
    }

    /**
     * Requirement 3.7: Commercial catalog recommendations (up-selling & cross-selling).
     */
    public function test_commercial_recommendations_upselling_and_cross_selling(): void
    {
        // 1. Arrange: Create vendor, category, and catalog items
        $vendor = User::create([
            'name' => 'Vendor Kamera Studio',
            'email' => 'studio_' . uniqid() . '@test.com',
            'password' => bcrypt('password'),
            'role' => 'vendor',
            'vendor_status' => 'approved',
            'vendor_name' => 'Studio Foto Nusantara',
            'whatsapp' => '0815' . rand(10000000, 99999999),
        ]);

        $kategori = Kategori::firstOrCreate(['nama' => 'Kamera & Lensa'], ['is_active' => true]);

        // Target Product: Rp 100.000 / day base price
        $targetItem = Barang::create([
            'vendor_id' => $vendor->id,
            'kategori_id' => $kategori->id,
            'nama' => 'Sony A6400 Mirrorless Camera',
            'slug' => 'sony-a6400-' . uniqid(),
            'deskripsi' => 'Kamera APS-C terbaik untuk pemula dan vlogger',
            'harga_sewa_harian' => 100000,
            'deposit' => 50000,
            'kondisi' => 'Sangat Baik',
            'stok_total' => 3,
            'status' => 'tersedia',
        ]);

        // Premium Alternative: Rp 250.000 / day base price
        $premiumItem = Barang::create([
            'vendor_id' => $vendor->id,
            'kategori_id' => $kategori->id,
            'nama' => 'Sony FX3 Cinema Line Camera',
            'slug' => 'sony-fx3-' . uniqid(),
            'deskripsi' => 'Kamera sinema profesional 4K 120fps',
            'harga_sewa_harian' => 250000,
            'deposit' => 200000,
            'kondisi' => 'Sangat Baik',
            'stok_total' => 1,
            'status' => 'tersedia',
        ]);

        // Compatible Accessories in catalog
        $tripod = Barang::create([
            'vendor_id' => $vendor->id,
            'kategori_id' => $kategori->id,
            'nama' => 'Tripod Manfrotto Compact Action',
            'slug' => 'tripod-manfrotto-' . uniqid(),
            'deskripsi' => 'Tripod kokoh dan fleksibel untuk kamera mirrorless',
            'harga_sewa_harian' => 25000,
            'deposit' => 10000,
            'kondisi' => 'Baik',
            'stok_total' => 5,
            'status' => 'tersedia',
        ]);

        $memoryCard = Barang::create([
            'vendor_id' => $vendor->id,
            'kategori_id' => $kategori->id,
            'nama' => 'SD Card SanDisk Extreme Pro 128GB',
            'slug' => 'sd-card-sandisk-128gb-' . uniqid(),
            'deskripsi' => 'Memory card kecepatan tinggi support 4K video',
            'harga_sewa_harian' => 15000,
            'deposit' => 10000,
            'kondisi' => 'Sangat Baik',
            'stok_total' => 10,
            'status' => 'tersedia',
        ]);

        // Catalog Alternative (same category, similar price)
        $altItem = Barang::create([
            'vendor_id' => $vendor->id,
            'kategori_id' => $kategori->id,
            'nama' => 'Canon EOS M50 Mark II',
            'slug' => 'canon-m50-mark-ii-' . uniqid(),
            'deskripsi' => 'Kamera vlogging ringan dan ringkas',
            'harga_sewa_harian' => 95000,
            'deposit' => 50000,
            'kondisi' => 'Baik',
            'stok_total' => 2,
            'status' => 'tersedia',
        ]);

        // 2. Act: Get recommendations for target item
        $recommendations = $this->service->getRecommendations($targetItem);

        // 3. Assert Target Product Info & 5% Markup
        $this->assertEquals($targetItem->id, $recommendations['target_product']['id']);
        $this->assertEquals(100000, $recommendations['target_product']['harga_sewa_harian']);
        $this->assertEquals(105000, $recommendations['target_product']['harga_sewa_customer']); // 5% markup

        // Assert Up-Selling: Multi-Day Packages
        $packages = $recommendations['upselling']['multi_day_packages'];
        $this->assertCount(4, $packages);

        // Tier 1: 3 Days (5% discount)
        $pkg3 = $packages[0];
        $this->assertEquals('3_days', $pkg3['package_tier']);
        $this->assertEquals(3, $pkg3['duration_days']);
        $this->assertEquals(5, $pkg3['discount_percent']);
        $this->assertEquals(105000, $pkg3['daily_rate_normal']);
        $this->assertEquals(round(105000 * 0.95), $pkg3['daily_rate_discounted']);
        $this->assertEquals(315000, $pkg3['normal_total']);
        $this->assertEquals(round(315000 * 0.95), $pkg3['package_total']);
        $this->assertEquals(315000 - round(315000 * 0.95), $pkg3['savings']);
        $this->assertNotEmpty($pkg3['pitch']);

        // Tier 2: Weekly (15% discount)
        $pkgWeekly = $packages[1];
        $this->assertEquals('weekly', $pkgWeekly['package_tier']);
        $this->assertEquals(7, $pkgWeekly['duration_days']);
        $this->assertEquals(15, $pkgWeekly['discount_percent']);
        $this->assertEquals(735000, $pkgWeekly['normal_total']);
        $this->assertEquals(round(735000 * 0.85), $pkgWeekly['package_total']);
        $this->assertEquals(735000 - round(735000 * 0.85), $pkgWeekly['savings']);

        // Assert Up-Selling: Premium Alternatives
        $premiumAlternatives = $recommendations['upselling']['premium_alternatives'];
        $this->assertNotEmpty($premiumAlternatives);
        $premiumIds = array_column($premiumAlternatives, 'id');
        $this->assertContains($premiumItem->id, $premiumIds);

        // Assert Cross-Selling: Live Catalog Accessories
        $catalogAcc = $recommendations['cross_selling']['catalog_accessories'];
        $this->assertNotEmpty($catalogAcc);
        $accIds = array_column($catalogAcc, 'id');
        $this->assertTrue(in_array($tripod->id, $accIds) || in_array($memoryCard->id, $accIds));

        // Assert Cross-Selling: Curated Addons
        $curated = $recommendations['cross_selling']['curated_addons'];
        $this->assertNotEmpty($curated);
        $curatedNames = implode(' ', array_column($curated, 'name'));
        $this->assertStringContainsString('Tripod', $curatedNames);

        // Assert Cross-Selling: Catalog Alternatives
        $catalogAlts = $recommendations['cross_selling']['catalog_alternatives'];
        $this->assertNotEmpty($catalogAlts);
        $altIds = array_column($catalogAlts, 'id');
        $this->assertContains($altItem->id, $altIds);

        // Commercial Pitch
        $this->assertNotEmpty($recommendations['commercial_pitch']);
    }

    /**
     * Recommendations by product ID or slug string.
     */
    public function test_recommendations_by_product_id_and_slug(): void
    {
        $vendor = User::create([
            'name' => 'Outdoor Gear Vendor',
            'email' => 'outdoor_' . uniqid() . '@test.com',
            'password' => bcrypt('password'),
            'role' => 'vendor',
            'whatsapp' => '0816' . rand(10000000, 99999999),
        ]);

        $kategori = Kategori::firstOrCreate(['nama' => 'Camping & Outdoor'], ['is_active' => true]);

        $tenda = Barang::create([
            'vendor_id' => $vendor->id,
            'kategori_id' => $kategori->id,
            'nama' => 'Tenda Dome Eiger 4 Orang Waterproof',
            'slug' => 'tenda-dome-eiger-4p-' . uniqid(),
            'harga_sewa_harian' => 60000,
            'deposit' => 30000,
            'kondisi' => 'Sangat Baik',
            'stok_total' => 4,
            'status' => 'tersedia',
        ]);

        // Call by integer ID
        $recById = $this->service->getRecommendations($tenda->id);
        $this->assertEquals($tenda->id, $recById['target_product']['id']);

        // Call by slug string
        $recBySlug = $this->service->getRecommendations($tenda->slug);
        $this->assertEquals($tenda->id, $recBySlug['target_product']['id']);

        // Verify curated camping addons (sleeping bag, stove)
        $curated = $recBySlug['cross_selling']['curated_addons'];
        $this->assertNotEmpty($curated);
        $curatedNames = implode(' ', array_column($curated, 'name'));
        $this->assertStringContainsString('Sleeping Bag', $curatedNames);
    }
}
