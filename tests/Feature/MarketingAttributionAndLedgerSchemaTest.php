<?php

namespace Tests\Feature;

use App\Models\ChatSession;
use App\Models\FinancialLedgerEntry;
use App\Models\MarketingAttribution;
use App\Models\ReferralRecord;
use App\Models\User;
use App\Models\Voucher;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class MarketingAttributionAndLedgerSchemaTest extends TestCase
{
    use DatabaseTransactions;

    public function test_attribution_referral_and_ledger_tables_exist_with_required_columns(): void
    {
        $tables = [
            'marketing_attributions' => [
                'id',
                'chat_session_id',
                'user_id',
                'order_id',
                'touchpoint_type',
                'utm_source',
                'utm_medium',
                'utm_campaign',
                'utm_term',
                'utm_content',
                'gclid',
                'fbclid',
                'fbp',
                'fbc',
                'gbraid',
                'wbraid',
                'referral_code',
                'landing_page_url',
                'referrer_url',
                'client_ip_address',
                'client_user_agent',
                'conversion_value',
                'occurred_at',
                'metadata',
                'created_at',
                'updated_at',
            ],
            'referral_records' => [
                'id',
                'referrer_id',
                'referee_id',
                'referral_code',
                'order_id',
                'status',
                'gross_booking_amount',
                'reward_amount',
                'referee_voucher_id',
                'anti_fraud_score',
                'fraud_check_status',
                'fraud_reasons',
                'escrow_matures_at',
                'clawback_at',
                'is_payout_locked',
                'metadata',
                'created_at',
                'updated_at',
            ],
            'financial_ledger_entries' => [
                'id',
                'entry_type',
                'category',
                'amount',
                'currency',
                'user_id',
                'order_id',
                'campaign_id',
                'pembayaran_id',
                'reference_id',
                'description',
                'running_balance',
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

    public function test_marketing_attribution_modern_click_identifiers(): void
    {
        $user = User::create([
            'name' => 'Ad User',
            'email' => 'aduser_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'customer',
        ]);

        $session = ChatSession::create([
            'session_token' => 'TOK_ATTR_' . uniqid(),
            'user_id' => $user->id,
            'channel' => 'web',
            'status' => 'active',
        ]);

        $attribution = MarketingAttribution::create([
            'chat_session_id' => $session->id,
            'user_id' => $user->id,
            'touchpoint_type' => 'first_touch',
            'utm_source' => 'google_ads',
            'utm_medium' => 'cpc',
            'utm_campaign' => 'sewa_kamera_jogja',
            'gclid' => 'CjwKCAjwz_W1BhBiEiwAd3jhU8vExampleGclid',
            'fbclid' => 'IwAR2v1ExampleFbclidString',
            'fbp' => 'fb.1.1689234567.987654321',
            'fbc' => 'fb.1.1689234567.IwAR2v1ExampleFbclidString',
            'gbraid' => '12345_gbraid_ios_identifier',
            'wbraid' => '67890_wbraid_web_identifier',
            'referral_code' => 'REFJONI2026',
            'conversion_value' => 150000.50,
            'occurred_at' => now(),
            'metadata' => ['device' => 'iPhone 15 Pro'],
        ]);

        $this->assertEquals('CjwKCAjwz_W1BhBiEiwAd3jhU8vExampleGclid', $attribution->gclid);
        $this->assertEquals('IwAR2v1ExampleFbclidString', $attribution->fbclid);
        $this->assertEquals('fb.1.1689234567.987654321', $attribution->fbp);
        $this->assertEquals('fb.1.1689234567.IwAR2v1ExampleFbclidString', $attribution->fbc);
        $this->assertEquals('12345_gbraid_ios_identifier', $attribution->gbraid);
        $this->assertEquals('67890_wbraid_web_identifier', $attribution->wbraid);
        $this->assertEquals('REFJONI2026', $attribution->referral_code);
        $this->assertEquals(150000.50, (float) $attribution->conversion_value);
        $this->assertEquals($session->id, $attribution->session->id);
        $this->assertEquals($user->id, $attribution->user->id);
        $this->assertTrue($user->marketingAttributions->contains($attribution));
    }

    public function test_referral_record_escrow_states_and_relations(): void
    {
        $referrer = User::create([
            'name' => 'Referrer User',
            'email' => 'referrer_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'customer',
        ]);

        $referee = User::create([
            'name' => 'Referee User',
            'email' => 'referee_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'customer',
        ]);

        $voucher = Voucher::create([
            'vendor_id' => $referrer->id,
            'kode_voucher' => 'WELCOME_' . strtoupper(uniqid()),
            'tipe_diskon' => 'persen',
            'nilai_diskon' => 10,
            'minimal_belanja' => 50000,
            'maksimal_diskon' => 25000,
            'kuota_total' => 100,
            'kuota_terpakai' => 0,
            'tanggal_mulai' => now(),
            'tanggal_selesai' => now()->addDays(14),
            'is_active' => true,
        ]);

        $referral = ReferralRecord::create([
            'referrer_id' => $referrer->id,
            'referee_id' => $referee->id,
            'referral_code' => 'REF_' . strtoupper(uniqid()),
            'status' => ReferralRecord::STATUS_PENDING_ESCROW,
            'gross_booking_amount' => 75000.00,
            'reward_amount' => 10000.00,
            'referee_voucher_id' => $voucher->id,
            'anti_fraud_score' => 0.15,
            'fraud_check_status' => ReferralRecord::FRAUD_STATUS_PASSED,
            'escrow_matures_at' => now()->addHours(24),
            'is_payout_locked' => false,
        ]);

        $this->assertTrue($referral->isHeldInEscrow());
        $this->assertTrue($referral->canClawback());
        $this->assertEquals($referrer->id, $referral->referrer->id);
        $this->assertEquals($referee->id, $referral->referee->id);
        $this->assertEquals($voucher->id, $referral->refereeVoucher->id);
        $this->assertTrue($referrer->referralsGiven->contains($referral));
        $this->assertEquals($referral->id, $referee->referralReceived->id);

        // State transition to matured
        $referral->update(['status' => ReferralRecord::STATUS_MATURED]);
        $this->assertFalse($referral->isHeldInEscrow());
        $this->assertTrue(ReferralRecord::matured()->where('id', $referral->id)->exists());

        // State transition to clawback with payout locked
        $referral->update([
            'status' => ReferralRecord::STATUS_CLAWBACK,
            'is_payout_locked' => true,
            'clawback_at' => now(),
        ]);
        $this->assertTrue($referral->is_payout_locked);
    }

    public function test_financial_ledger_entries_decimal_precision_and_balance_helpers(): void
    {
        $vendor = User::create([
            'name' => 'Vendor Ledger',
            'email' => 'vendor_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'vendor',
        ]);

        // 1. Credit Vendor Gross Payout Rp95.000,50
        $creditEntry = FinancialLedgerEntry::create([
            'entry_type' => FinancialLedgerEntry::TYPE_CREDIT,
            'category' => FinancialLedgerEntry::CATEGORY_VENDOR_PAYOUT,
            'amount' => 95000.50,
            'currency' => 'IDR',
            'user_id' => $vendor->id,
            'description' => 'Gross rental payout for booking #101',
            'running_balance' => 95000.50,
        ]);

        // 2. Debit Platform Commission Rp5.000,00
        $commissionEntry = FinancialLedgerEntry::create([
            'entry_type' => FinancialLedgerEntry::TYPE_DEBIT,
            'category' => FinancialLedgerEntry::CATEGORY_PLATFORM_COMMISSION,
            'amount' => 5000.00,
            'currency' => 'IDR',
            'user_id' => $vendor->id,
            'description' => '5% platform commission deduction',
            'running_balance' => 90000.50,
        ]);

        // 3. Debit CPC Ad Spend Rp1.500,25
        $adEntry = FinancialLedgerEntry::create([
            'entry_type' => FinancialLedgerEntry::TYPE_DEBIT,
            'category' => FinancialLedgerEntry::CATEGORY_VENDOR_AD_CPC_DEDUCTION,
            'amount' => 1500.25,
            'currency' => 'IDR',
            'user_id' => $vendor->id,
            'campaign_id' => 42,
            'description' => 'CPC billable click deduction',
            'running_balance' => 88500.25,
        ]);

        // Verify DECIMAL(12,2) exact precision without floating point drift
        $this->assertEquals(95000.50, (float) $creditEntry->amount);
        $this->assertEquals(5000.00, (float) $commissionEntry->amount);
        $this->assertEquals(1500.25, (float) $adEntry->amount);

        // Verify balance helpers
        $userBalance = FinancialLedgerEntry::calculateUserBalance($vendor->id);
        // 95000.50 - 5000.00 - 1500.25 = 88500.25
        $this->assertEquals(88500.25, $userBalance);

        $campaignSpend = FinancialLedgerEntry::calculateCampaignSpend(42);
        $this->assertEquals(1500.25, $campaignSpend);

        $this->assertCount(3, $vendor->ledgerEntries);
    }
}
