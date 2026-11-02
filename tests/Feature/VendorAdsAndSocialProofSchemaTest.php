<?php

namespace Tests\Feature;

use App\Models\Barang;
use App\Models\ChatSession;
use App\Models\Kategori;
use App\Models\Penyewaan;
use App\Models\SocialProofReview;
use App\Models\User;
use App\Models\VendorAdCampaign;
use App\Models\VendorAdClick;
use App\Models\Voucher;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class VendorAdsAndSocialProofSchemaTest extends TestCase
{
    use DatabaseTransactions;

    public function test_vendor_ads_and_social_proof_tables_exist_with_required_columns(): void
    {
        $tables = [
            'vendor_ad_campaigns' => [
                'id',
                'vendor_id',
                'barang_id',
                'campaign_name',
                'billing_model',
                'bid_amount',
                'ppb_commission_rate',
                'daily_budget',
                'total_budget',
                'current_daily_spend',
                'total_spend',
                'start_date',
                'end_date',
                'status',
                'target_keywords',
                'target_categories',
                'impressions_count',
                'clicks_count',
                'bookings_count',
                'metadata',
                'created_at',
                'updated_at',
            ],
            'vendor_ad_clicks' => [
                'id',
                'campaign_id',
                'barang_id',
                'user_id',
                'chat_session_id',
                'session_token',
                'ip_hash',
                'user_agent',
                'interaction_type',
                'is_billable',
                'deduplication_reason',
                'charged_amount',
                'attributed_order_id',
                'attributed_at',
                'clicked_at',
                'metadata',
                'created_at',
                'updated_at',
            ],
            'social_proof_reviews' => [
                'id',
                'order_id',
                'barang_id',
                'user_id',
                'ulasan_id',
                'rating',
                'review_text',
                'media_urls',
                'sanitized_media_urls',
                'is_consent_granted',
                'consent_granted_at',
                'consent_revoked_at',
                'is_published',
                'status',
                'incentive_reward_amount',
                'incentive_voucher_id',
                'service_recovery_ticket_id',
                'metadata',
                'created_at',
                'updated_at',
            ],
        ];

        foreach ($tables as $table => $columns) {
            $this->assertTrue(Schema::hasTable($table), "Table {$table} does not exist.");
            foreach ($columns as $column) {
                $this->assertTrue(
                    Schema::hasColumn($table, $column),
                    "Table {$table} is missing column {$column}."
                );
            }
        }
    }

    public function test_vendor_ad_campaign_billing_models_and_scopes(): void
    {
        $vendor = User::create([
            'name' => 'Campaign Vendor',
            'email' => 'vendor_ad_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'vendor',
        ]);

        $kategori = Kategori::firstOrCreate(
            ['nama' => 'Elektronik'],
            ['icon' => 'camera', 'slug' => 'elektronik']
        );

        $barang = Barang::create([
            'vendor_id' => $vendor->id,
            'kategori_id' => $kategori->id,
            'nama' => 'Sony A7 IV Promo',
            'slug' => 'sony-a7-iv-promo-' . uniqid(),
            'harga_sewa_harian' => 150000,
            'deposit' => 50000,
            'denda_per_hari' => 30000,
            'stok_total' => 2,
            'status' => 'tersedia',
        ]);

        // CPC Campaign
        $cpcCampaign = VendorAdCampaign::create([
            'vendor_id' => $vendor->id,
            'barang_id' => $barang->id,
            'campaign_name' => 'CPC Camera Ads',
            'billing_model' => VendorAdCampaign::MODEL_CPC,
            'bid_amount' => 1500.00,
            'daily_budget' => 50000.00,
            'current_daily_spend' => 0.00,
            'status' => VendorAdCampaign::STATUS_ACTIVE,
        ]);

        // PPB Campaign (Pay-per-Booking)
        $ppbCampaign = VendorAdCampaign::create([
            'vendor_id' => $vendor->id,
            'barang_id' => $barang->id,
            'campaign_name' => 'PPB High Converting Promo',
            'billing_model' => VendorAdCampaign::MODEL_PPB,
            'ppb_commission_rate' => 10.00,
            'status' => VendorAdCampaign::STATUS_ACTIVE,
        ]);

        // Flat-Daily Campaign
        $flatDailyCampaign = VendorAdCampaign::create([
            'vendor_id' => $vendor->id,
            'barang_id' => $barang->id,
            'campaign_name' => 'Weekend Banner Spotlight Daily',
            'billing_model' => VendorAdCampaign::MODEL_FLAT_DAILY,
            'bid_amount' => 25000.00,
            'start_date' => now(),
            'end_date' => now()->addDay(),
            'status' => VendorAdCampaign::STATUS_ACTIVE,
        ]);

        // Flat-Weekly Campaign
        $flatWeeklyCampaign = VendorAdCampaign::create([
            'vendor_id' => $vendor->id,
            'barang_id' => $barang->id,
            'campaign_name' => 'Holiday Weekly Featured',
            'billing_model' => VendorAdCampaign::MODEL_FLAT_WEEKLY,
            'bid_amount' => 120000.00,
            'start_date' => now(),
            'end_date' => now()->addWeek(),
            'status' => VendorAdCampaign::STATUS_ACTIVE,
        ]);

        $this->assertTrue($cpcCampaign->isActive());
        $this->assertFalse($cpcCampaign->hasDepletedBudget());

        // Check Scopes
        $this->assertCount(1, VendorAdCampaign::forVendor($vendor->id)->cpc()->get());
        $this->assertCount(1, VendorAdCampaign::forVendor($vendor->id)->ppb()->get());
        $this->assertCount(1, VendorAdCampaign::forVendor($vendor->id)->flatDaily()->get());
        $this->assertCount(1, VendorAdCampaign::forVendor($vendor->id)->flatWeekly()->get());

        // Check Relationships
        $this->assertEquals($vendor->id, $cpcCampaign->vendor->id);
        $this->assertEquals($barang->id, $cpcCampaign->barang->id);
        $this->assertTrue($vendor->adCampaigns->contains($cpcCampaign));
        $this->assertTrue($barang->adCampaigns->contains($cpcCampaign));
    }

    public function test_vendor_ad_click_deduplication_and_attribution(): void
    {
        $vendor = User::create([
            'name' => 'Click Vendor',
            'email' => 'click_vendor_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'vendor',
        ]);

        $customer = User::create([
            'name' => 'Clicking Customer',
            'email' => 'clicker_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'customer',
        ]);

        $campaign = VendorAdCampaign::create([
            'vendor_id' => $vendor->id,
            'campaign_name' => 'Deduplication Test Campaign',
            'billing_model' => VendorAdCampaign::MODEL_CPC,
            'bid_amount' => 2000.00,
            'status' => VendorAdCampaign::STATUS_ACTIVE,
        ]);

        $session = ChatSession::create([
            'session_token' => 'GUEST_TOK_' . uniqid(),
            'user_id' => $customer->id,
            'status' => 'active',
        ]);

        $ipHash = hash('sha256', '192.168.1.100');

        // First click is billable
        $click1 = VendorAdClick::create([
            'campaign_id' => $campaign->id,
            'user_id' => $customer->id,
            'chat_session_id' => $session->id,
            'session_token' => $session->session_token,
            'ip_hash' => $ipHash,
            'interaction_type' => 'chatbot_button',
            'is_billable' => true,
            'charged_amount' => 2000.00,
            'clicked_at' => now(),
        ]);

        // Second click within 24 hours is cooling-off deduplicated (non-billable)
        $click2 = VendorAdClick::create([
            'campaign_id' => $campaign->id,
            'user_id' => $customer->id,
            'chat_session_id' => $session->id,
            'session_token' => $session->session_token,
            'ip_hash' => $ipHash,
            'interaction_type' => 'chatbot_button',
            'is_billable' => false,
            'deduplication_reason' => 'cooling_off_24h',
            'charged_amount' => 0.00,
            'clicked_at' => now()->addMinutes(10),
        ]);

        $this->assertTrue($click1->is_billable);
        $this->assertEquals(2000.00, (float) $click1->charged_amount);
        $this->assertFalse($click2->is_billable);
        $this->assertEquals('cooling_off_24h', $click2->deduplication_reason);

        // Scopes
        $this->assertCount(1, VendorAdClick::where('campaign_id', $campaign->id)->billable()->get());
        $this->assertCount(1, VendorAdClick::where('campaign_id', $campaign->id)->nonBillable()->get());
        $this->assertCount(2, VendorAdClick::where('campaign_id', $campaign->id)->within24Hours()->get());

        // Relationships
        $this->assertEquals($campaign->id, $click1->campaign->id);
        $this->assertEquals($session->id, $click1->chatSession->id);
        $this->assertTrue($campaign->clicks->contains($click1));
    }

    public function test_social_proof_reviews_consent_sanitization_and_service_recovery(): void
    {
        $vendor = User::create([
            'name' => 'Social Vendor',
            'email' => 'social_v_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'vendor',
        ]);

        $customer = User::create([
            'name' => 'Reviewer Customer',
            'email' => 'reviewer_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'customer',
        ]);

        $kategori = Kategori::firstOrCreate(
            ['nama' => 'Camping'],
            ['icon' => 'tent', 'slug' => 'camping']
        );

        $barang = Barang::create([
            'vendor_id' => $vendor->id,
            'kategori_id' => $kategori->id,
            'nama' => 'Tenda Dome 4P',
            'slug' => 'tenda-dome-4p-' . uniqid(),
            'harga_sewa_harian' => 50000,
            'deposit' => 20000,
            'denda_per_hari' => 15000,
            'stok_total' => 5,
            'status' => 'tersedia',
        ]);

        $order = Penyewaan::create([
            'user_id' => $customer->id,
            'vendor_id' => $vendor->id,
            'customer_name' => $customer->name,
            'customer_whatsapp' => '081234567890',
            'kode_booking' => Penyewaan::generateKodeBooking(),
            'payment_method' => 'qris',
            'metode_pengambilan' => 'ambil_sendiri',
            'total_biaya' => 100000,
            'total_deposit' => 0,
            'total_denda' => 0,
            'status' => 'Selesai',
            'tanggal_mulai' => now()->subDays(2),
            'tanggal_selesai' => now()->subDay(),
        ]);

        $voucher = Voucher::create([
            'vendor_id' => $vendor->id,
            'kode_voucher' => 'REVIEW_' . strtoupper(uniqid()),
            'tipe_diskon' => 'nominal',
            'nilai_diskon' => 15000,
            'minimal_belanja' => 75000,
            'maksimal_diskon' => 15000,
            'kuota_total' => 100,
            'kuota_terpakai' => 0,
            'tanggal_mulai' => now(),
            'tanggal_selesai' => now()->addDays(30),
            'is_active' => true,
        ]);

        // 1. High rating review (5-star) with PII sanitization and customer consent
        $highReview = SocialProofReview::create([
            'order_id' => $order->id,
            'barang_id' => $barang->id,
            'user_id' => $customer->id,
            'rating' => 5,
            'review_text' => 'Tenda bersih, pasang mudah, pelayanan ramah!',
            'media_urls' => ['https://res.cloudinary.com/rentify/image/upload/v1/camping_raw.jpg'],
            'sanitized_media_urls' => ['https://res.cloudinary.com/rentify/image/upload/v1/camping_blurred_faces.jpg'],
            'is_consent_granted' => true,
            'consent_granted_at' => now(),
            'is_published' => true,
            'status' => SocialProofReview::STATUS_PUBLISHED,
            'incentive_reward_amount' => 15000.00,
            'incentive_voucher_id' => $voucher->id,
        ]);

        $this->assertTrue($highReview->isEligibleForPublishing());
        $this->assertFalse($highReview->requiresMediation());
        $this->assertEquals(['https://res.cloudinary.com/rentify/image/upload/v1/camping_blurred_faces.jpg'], $highReview->sanitized_media_urls);
        $this->assertEquals($voucher->id, $highReview->incentiveVoucher->id);

        // 2. Low rating review (2-star) triggering service recovery mediation
        $lowReview = SocialProofReview::create([
            'order_id' => $order->id,
            'barang_id' => $barang->id,
            'user_id' => $customer->id,
            'rating' => 2,
            'review_text' => 'Pasak tenda kurang 2 buah, tolong dicek lagi sebelum kirim.',
            'is_consent_granted' => false,
            'is_published' => false,
            'status' => SocialProofReview::STATUS_HELD_PENDING_MEDIATION,
            'service_recovery_ticket_id' => 'SR-TICKET-' . strtoupper(uniqid()),
        ]);

        $this->assertFalse($lowReview->isEligibleForPublishing());
        $this->assertTrue($lowReview->requiresMediation());

        // Scopes
        $this->assertCount(1, SocialProofReview::published()->get());
        $this->assertCount(1, SocialProofReview::highRating()->get());
        $this->assertCount(1, SocialProofReview::serviceRecoveryRequired()->get());
        $this->assertCount(1, SocialProofReview::consentGranted()->get());

        // Relationships
        $this->assertEquals($barang->id, $highReview->barang->id);
        $this->assertEquals($customer->id, $highReview->user->id);
        $this->assertTrue($barang->socialProofReviews->contains($highReview));
        $this->assertTrue($customer->socialProofReviews->contains($highReview));
    }
}
