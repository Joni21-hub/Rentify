<?php

namespace Tests\Feature;

use App\Models\Barang;
use App\Models\Kategori;
use App\Models\MarketingConsent;
use App\Models\Penyewaan;
use App\Models\SocialProofReview;
use App\Models\User;
use App\Models\Voucher;
use App\Services\SocialProofService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class PostRentalReviewTest extends TestCase
{
    use DatabaseTransactions;

    protected User $vendor;
    protected User $customer;
    protected Barang $barang;

    protected function setUp(): void
    {
        parent::setUp();

        $this->vendor = User::create([
            'name' => 'Vendor Test Subtask 18.1',
            'email' => 'vendor_18_1_' . uniqid() . '@rentify.test',
            'password' => bcrypt('password'),
            'role' => 'vendor',
            'whatsapp' => '081234567890',
        ]);

        $this->customer = User::create([
            'name' => 'Customer Test Subtask 18.1',
            'email' => 'customer_18_1_' . uniqid() . '@rentify.test',
            'password' => bcrypt('password'),
            'role' => 'customer',
            'whatsapp' => '089876543210',
        ]);

        $kategori = Kategori::firstOrCreate(
            ['nama' => 'Camping'],
            ['is_active' => true]
        );

        $this->barang = Barang::create([
            'vendor_id' => $this->vendor->id,
            'kategori_id' => $kategori->id,
            'nama' => 'Tenda Dome 4P Subtask 18.1',
            'slug' => 'tenda-dome-4p-subtask-18-1-' . uniqid(),
            'deskripsi' => 'Tenda camping kapasitas 4 orang',
            'harga_sewa_harian' => 60000,
            'stok_total' => 5,
            'status' => 'tersedia',
        ]);
    }

    private function createOrder(int $totalBiaya = 60000, ?Carbon $completedAt = null, ?User $customer = null): Penyewaan
    {
        $user = $customer ?? $this->customer;
        $order = Penyewaan::create([
            'user_id' => $user->id,
            'vendor_id' => $this->vendor->id,
            'kode_booking' => 'BK-' . strtoupper(uniqid()),
            'customer_name' => $user->name,
            'customer_whatsapp' => $user->whatsapp ?? '089876543210',
            'tanggal_mulai' => now(),
            'tanggal_selesai' => now()->addDays(2),
            'metode_pengambilan' => 'pickup',
            'payment_method' => 'qris',
            'total_biaya' => $totalBiaya,
            'total_price' => $totalBiaya,
            'status' => 'Selesai',
            'completed_at' => $completedAt ?? now(),
        ]);

        return $order;
    }

    public function test_schedule_review_prompt_one_hour_post_completion_during_daytime(): void
    {
        // Completed at 14:00 WIB (Asia/Jakarta)
        $completedAt = Carbon::create(2026, 11, 2, 14, 0, 0, 'Asia/Jakarta');
        $order = $this->createOrder(60000, $completedAt);

        $service = new SocialProofService();
        $result = $service->schedulePostRentalReviewPrompt($order, $completedAt);

        $this->assertTrue($result['scheduled']);
        $this->assertFalse($result['deferred']);
        $this->assertEquals(
            '2026-11-02 15:00:00',
            $result['scheduled_for']->timezone('Asia/Jakarta')->format('Y-m-d H:i:s')
        );
    }

    public function test_schedule_review_prompt_deferred_during_late_night_quiet_hours(): void
    {
        // Completed at 21:30 WIB -> +1h is 22:30 WIB (quiet hours 22:00 - 07:01 WIB)
        // Must defer to 07:01 WIB next morning
        $completedAt = Carbon::create(2026, 11, 2, 21, 30, 0, 'Asia/Jakarta');
        $order = $this->createOrder(75000, $completedAt);

        $service = new SocialProofService();
        $result = $service->schedulePostRentalReviewPrompt($order, $completedAt);

        $this->assertTrue($result['scheduled']);
        $this->assertTrue($result['deferred']);
        $this->assertEquals(
            '2026-11-03 07:01:00',
            $result['scheduled_for']->timezone('Asia/Jakarta')->format('Y-m-d H:i:s')
        );
    }

    public function test_schedule_review_prompt_deferred_during_early_morning_quiet_hours(): void
    {
        // Completed at 04:30 WIB -> +1h is 05:30 WIB (quiet hours 22:00 - 07:01 WIB)
        // Must defer to 07:01 WIB same morning
        $completedAt = Carbon::create(2026, 11, 3, 4, 30, 0, 'Asia/Jakarta');
        $order = $this->createOrder(75000, $completedAt);

        $service = new SocialProofService();
        $result = $service->schedulePostRentalReviewPrompt($order, $completedAt);

        $this->assertTrue($result['scheduled']);
        $this->assertTrue($result['deferred']);
        $this->assertEquals(
            '2026-11-03 07:01:00',
            $result['scheduled_for']->timezone('Asia/Jakarta')->format('Y-m-d H:i:s')
        );
    }

    public function test_schedule_review_prompt_suppressed_when_marketing_consent_opted_out(): void
    {
        // Customer explicitly opted out of marketing
        MarketingConsent::updateOrCreate(
            ['phone_number' => $this->customer->whatsapp, 'consent_type' => 'promotional'],
            [
                'user_id' => $this->customer->id,
                'is_opted_in' => false,
                'opted_out_at' => now(),
            ]
        );

        $order = $this->createOrder(100000, now());

        $service = new SocialProofService();
        $result = $service->schedulePostRentalReviewPrompt($order);

        $this->assertFalse($result['scheduled']);
        $this->assertEquals('marketing_consent_opted_out', $result['reason']);
    }

    public function test_process_review_incentive_awards_equal_voucher_for_all_ratings_1_to_5(): void
    {
        $service = new SocialProofService();

        for ($rating = 1; $rating <= 5; $rating++) {
            $user = User::create([
                'name' => "Customer Rating {$rating}",
                'email' => "cust_rating_{$rating}_" . uniqid() . '@rentify.test',
                'password' => bcrypt('password'),
                'role' => 'customer',
                'whatsapp' => '0812' . rand(10000000, 99999999),
            ]);

            $order = $this->createOrder(50000, now(), $user);

            $review = SocialProofReview::create([
                'order_id' => $order->id,
                'barang_id' => $this->barang->id,
                'user_id' => $user->id,
                'rating' => $rating,
                'review_text' => "Feedback rating {$rating} star test",
                'status' => $rating <= 3 ? SocialProofReview::STATUS_HELD_PENDING_MEDIATION : SocialProofReview::STATUS_PENDING_REVIEW,
            ]);

            $result = $service->processReviewIncentive($review);

            // Must award flat Rp15.000 platform discount voucher regardless of rating (1-5 stars)
            $this->assertTrue($result['awarded'], "Incentive should be awarded for rating {$rating}");
            $this->assertEquals(15000.00, (float) $result['amount']);
            $this->assertNotNull($result['voucher_id']);

            $review->refresh();
            $this->assertEquals(15000.00, (float) $review->incentive_reward_amount);
            $this->assertNotNull($review->incentive_voucher_id);

            $voucher = Voucher::find($review->incentive_voucher_id);
            $this->assertNotNull($voucher);
            $this->assertEquals('platform', $voucher->tipe_voucher);
            $this->assertEquals('platform', $voucher->funding_source);
            $this->assertNull($voucher->vendor_id);
            $this->assertEquals('nominal', $voucher->tipe_diskon);
            $this->assertEquals(15000.00, (float) $voucher->nilai_diskon);
            $this->assertEquals(75000.00, (float) $voucher->minimal_belanja);
            $this->assertTrue($voucher->is_active);

            // Valid for 30 days
            $expectedEndDate = now()->addDays(30)->toDateString();
            $this->assertEquals($expectedEndDate, Carbon::parse($voucher->tanggal_selesai)->toDateString());
        }
    }

    public function test_process_review_incentive_denied_for_orders_below_50k(): void
    {
        $order = $this->createOrder(49000, now()); // Below Rp50.000 minimum spend

        $review = SocialProofReview::create([
            'order_id' => $order->id,
            'barang_id' => $this->barang->id,
            'user_id' => $this->customer->id,
            'rating' => 5,
            'review_text' => 'Bagus tapi total di bawah 50rb',
            'status' => SocialProofReview::STATUS_PENDING_REVIEW,
        ]);

        $service = new SocialProofService();
        $result = $service->processReviewIncentive($review);

        $this->assertFalse($result['awarded']);
        $this->assertEquals('minimum_order_spend_not_met', $result['reason']);
        $this->assertEquals(50000, $result['threshold']);

        $review->refresh();
        $this->assertNull($review->incentive_voucher_id);
        $this->assertEquals(0, (float) $review->incentive_reward_amount);
    }

    public function test_process_review_incentive_enforces_anti_farming_velocity_limit_per_30_days(): void
    {
        $service = new SocialProofService();

        // First order >= 50.000
        $order1 = $this->createOrder(80000, now()->subDays(5));
        $review1 = SocialProofReview::create([
            'order_id' => $order1->id,
            'barang_id' => $this->barang->id,
            'user_id' => $this->customer->id,
            'rating' => 5,
            'review_text' => 'Order pertama, sangat puas!',
            'status' => SocialProofReview::STATUS_PENDING_REVIEW,
            'created_at' => now()->subDays(5),
        ]);

        $res1 = $service->processReviewIncentive($review1);
        $this->assertTrue($res1['awarded']);

        // Second order within 30 days (attempting to farm vouchers)
        $order2 = $this->createOrder(90000, now());
        $review2 = SocialProofReview::create([
            'order_id' => $order2->id,
            'barang_id' => $this->barang->id,
            'user_id' => $this->customer->id,
            'rating' => 4,
            'review_text' => 'Order kedua, mau ambil voucher lagi',
            'status' => SocialProofReview::STATUS_PENDING_REVIEW,
            'created_at' => now(),
        ]);

        $res2 = $service->processReviewIncentive($review2);
        $this->assertFalse($res2['awarded']);
        $this->assertEquals('velocity_limit_exceeded', $res2['reason']);

        $review2->refresh();
        $this->assertNull($review2->incentive_voucher_id);
    }
}
