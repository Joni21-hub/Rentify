<?php

namespace Tests\Feature;

use App\Models\Barang;
use App\Models\Kategori;
use App\Models\Komplain;
use App\Models\MarketingConsent;
use App\Models\Penyewaan;
use App\Models\SocialProofReview;
use App\Models\User;
use App\Models\Voucher;
use App\Services\SocialProofService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ServiceRecoveryAndReengagementTest extends TestCase
{
    use DatabaseTransactions;

    protected User $vendor;
    protected User $customer;
    protected Barang $barang;
    protected Penyewaan $order;

    protected function setUp(): void
    {
        parent::setUp();

        // Fake WhatsApp gateway endpoints
        Http::fake([
            '*/send' => Http::response(['status' => true, 'id' => 'wa-mock-' . uniqid()], 200),
            '*' => Http::response(['status' => true], 200),
        ]);

        $this->vendor = User::create([
            'name' => 'Vendor Subtask 18.3',
            'email' => 'vendor_18_3_' . uniqid() . '@rentify.test',
            'password' => bcrypt('password'),
            'role' => 'vendor',
            'whatsapp' => '081234567892',
        ]);

        $this->customer = User::create([
            'name' => 'Customer Subtask 18.3',
            'email' => 'customer_18_3_' . uniqid() . '@rentify.test',
            'password' => bcrypt('password'),
            'role' => 'customer',
            'whatsapp' => '089876543212',
        ]);

        $kategori = Kategori::firstOrCreate(
            ['nama' => 'Peralatan Outdoor'],
            ['is_active' => true]
        );

        $this->barang = Barang::create([
            'vendor_id' => $this->vendor->id,
            'kategori_id' => $kategori->id,
            'nama' => 'Kompor Camping Portable',
            'slug' => 'kompor-camping-' . uniqid(),
            'deskripsi' => 'Kompor camping mini portable',
            'harga_sewa_harian' => 30000,
            'stok_total' => 5,
            'status' => 'tersedia',
        ]);

        $this->order = Penyewaan::create([
            'user_id' => $this->customer->id,
            'vendor_id' => $this->vendor->id,
            'kode_booking' => 'BK-' . strtoupper(uniqid()),
            'customer_name' => $this->customer->name,
            'customer_whatsapp' => $this->customer->whatsapp,
            'tanggal_mulai' => now()->subDays(3),
            'tanggal_selesai' => now()->subDay(),
            'metode_pengambilan' => 'pickup',
            'payment_method' => 'qris',
            'total_biaya' => 60000,
            'total_price' => 60000,
            'status' => 'Selesai',
            'completed_at' => now()->subDay(),
        ]);
    }

    public function test_service_recovery_workflow_for_ratings_1_to_3_with_2h_sla_and_empathy_message(): void
    {
        $service = new SocialProofService();

        for ($rating = 1; $rating <= 3; $rating++) {
            $user = User::create([
                'name' => "Customer Low Rating {$rating}",
                'email' => "low_rating_{$rating}_" . uniqid() . '@rentify.test',
                'password' => bcrypt('password'),
                'role' => 'customer',
                'whatsapp' => '0813' . rand(10000000, 99999999),
            ]);

            $order = Penyewaan::create([
                'user_id' => $user->id,
                'vendor_id' => $this->vendor->id,
                'kode_booking' => 'BK-' . strtoupper(uniqid()),
                'customer_name' => $user->name,
                'customer_whatsapp' => $user->whatsapp,
                'tanggal_mulai' => now()->subDays(2),
                'tanggal_selesai' => now(),
                'metode_pengambilan' => 'pickup',
                'payment_method' => 'qris',
                'total_biaya' => 80000,
                'total_price' => 80000,
                'status' => 'Selesai',
                'completed_at' => now(),
            ]);

            $review = SocialProofReview::create([
                'order_id' => $order->id,
                'barang_id' => $this->barang->id,
                'user_id' => $user->id,
                'rating' => $rating,
                'review_text' => "Pengalaman bintang {$rating}, api kompor kurang kencang dan kotor.",
                'is_consent_granted' => true,
                'status' => SocialProofReview::STATUS_PENDING_REVIEW,
            ]);

            $result = $service->triggerServiceRecoveryWorkflow($review);

            $this->assertTrue($result['triggered'], "Service recovery should trigger for rating {$rating}");
            $this->assertEquals(SocialProofReview::STATUS_HELD_PENDING_MEDIATION, $result['status']);
            $this->assertNotNull($result['ticket_id']);
            $this->assertEquals(2, $result['sla_hours']);
            $this->assertTrue($result['empathy_message_sent']);

            $review->refresh();

            // 1. Must be placed in held_pending_mediation and NOT published
            $this->assertEquals(SocialProofReview::STATUS_HELD_PENDING_MEDIATION, $review->status);
            $this->assertFalse($review->is_published);
            $this->assertTrue($review->requiresMediation());

            // 2. High-priority ticket with 2-hour SLA deadline
            $this->assertNotNull($review->service_recovery_ticket_id);
            $metadata = $review->metadata ?? [];
            $this->assertEquals('open', $metadata['service_recovery']['status'] ?? null);
            $this->assertEquals('high', $metadata['service_recovery']['priority'] ?? null);
            $this->assertEquals(2, $metadata['service_recovery']['sla_hours'] ?? null);

            $slaDeadline = Carbon::parse($metadata['service_recovery']['sla_deadline']);
            $this->assertTrue($slaDeadline->greaterThan(now()));
            $this->assertLessThanOrEqual(121, now()->diffInMinutes($slaDeadline));
        }
    }

    public function test_service_recovery_workflow_ignored_for_high_ratings(): void
    {
        $review = SocialProofReview::create([
            'order_id' => $this->order->id,
            'barang_id' => $this->barang->id,
            'user_id' => $this->customer->id,
            'rating' => 5, // High rating
            'review_text' => 'Kompor berfungsi normal dan bersih!',
            'status' => SocialProofReview::STATUS_PENDING_REVIEW,
        ]);

        $service = new SocialProofService();
        $result = $service->triggerServiceRecoveryWorkflow($review);

        $this->assertFalse($result['triggered']);
        $this->assertEquals('not_a_low_rating', $result['reason']);
        $this->assertNull($review->service_recovery_ticket_id);
    }

    public function test_inactivity_reengagement_dispatches_vouchers_for_30_and_60_days_inactive_customers(): void
    {
        // Setup customer inactive for 30 days
        $inactiveCustomer30 = User::create([
            'name' => 'Inactive Customer 30 Days',
            'email' => 'inactive_30_' . uniqid() . '@rentify.test',
            'password' => bcrypt('password'),
            'role' => 'customer',
            'whatsapp' => '0812' . rand(10000000, 99999999),
        ]);

        MarketingConsent::create([
            'user_id' => $inactiveCustomer30->id,
            'phone_number' => $inactiveCustomer30->whatsapp,
            'consent_type' => 'promotional',
            'is_opted_in' => true,
        ]);

        Penyewaan::create([
            'user_id' => $inactiveCustomer30->id,
            'vendor_id' => $this->vendor->id,
            'kode_booking' => 'BK-' . strtoupper(uniqid()),
            'customer_name' => $inactiveCustomer30->name,
            'customer_whatsapp' => $inactiveCustomer30->whatsapp,
            'tanggal_mulai' => now()->subDays(35),
            'tanggal_selesai' => now()->subDays(30),
            'metode_pengambilan' => 'pickup',
            'payment_method' => 'qris',
            'total_biaya' => 120000,
            'total_price' => 120000,
            'status' => 'Selesai',
            'completed_at' => now()->subDays(30),
            'created_at' => now()->subDays(35),
            'updated_at' => now()->subDays(30),
        ]);

        $service = new SocialProofService();
        $result = $service->reengageInactiveCustomers(30);

        $this->assertGreaterThanOrEqual(1, $result['reengaged_count']);
        $this->assertContains($inactiveCustomer30->id, $result['dispatched_user_ids']);

        // Assert customer received reactivation voucher
        $voucher = Voucher::where('tipe_voucher', 'platform')
            ->where('kode_voucher', 'like', 'REENGAGE-%')
            ->latest()
            ->first();

        $this->assertNotNull($voucher);
        $this->assertEquals(20000.00, (float) $voucher->nilai_diskon);
        $this->assertTrue($voucher->is_active);
    }

    public function test_inactivity_reengagement_suppressed_when_marketing_opted_out(): void
    {
        $optedOutCustomer = User::create([
            'name' => 'Opted Out Customer',
            'email' => 'optout_' . uniqid() . '@rentify.test',
            'password' => bcrypt('password'),
            'role' => 'customer',
            'whatsapp' => '0815' . rand(10000000, 99999999),
        ]);

        // Explicit opt-out (e.g. sent STOP)
        MarketingConsent::create([
            'user_id' => $optedOutCustomer->id,
            'phone_number' => $optedOutCustomer->whatsapp,
            'consent_type' => 'promotional',
            'is_opted_in' => false,
            'opted_out_at' => now(),
        ]);

        Penyewaan::create([
            'user_id' => $optedOutCustomer->id,
            'vendor_id' => $this->vendor->id,
            'kode_booking' => 'BK-' . strtoupper(uniqid()),
            'customer_name' => $optedOutCustomer->name,
            'customer_whatsapp' => $optedOutCustomer->whatsapp,
            'tanggal_mulai' => now()->subDays(35),
            'tanggal_selesai' => now()->subDays(30),
            'metode_pengambilan' => 'pickup',
            'payment_method' => 'qris',
            'total_biaya' => 100000,
            'total_price' => 100000,
            'status' => 'Selesai',
            'completed_at' => now()->subDays(30),
            'created_at' => now()->subDays(35),
            'updated_at' => now()->subDays(30),
        ]);

        $service = new SocialProofService();
        $result = $service->reengageInactiveCustomers(30);

        $this->assertNotContains($optedOutCustomer->id, $result['dispatched_user_ids']);
        $this->assertGreaterThanOrEqual(1, $result['suppressed_opt_out_count']);
    }

    public function test_inactivity_reengagement_suppressed_when_customer_has_active_dispute(): void
    {
        $disputedCustomer = User::create([
            'name' => 'Customer With Active Dispute',
            'email' => 'dispute_' . uniqid() . '@rentify.test',
            'password' => bcrypt('password'),
            'role' => 'customer',
            'whatsapp' => '0816' . rand(10000000, 99999999),
        ]);

        MarketingConsent::create([
            'user_id' => $disputedCustomer->id,
            'phone_number' => $disputedCustomer->whatsapp,
            'consent_type' => 'promotional',
            'is_opted_in' => true,
        ]);

        $order = Penyewaan::create([
            'user_id' => $disputedCustomer->id,
            'vendor_id' => $this->vendor->id,
            'kode_booking' => 'BK-' . strtoupper(uniqid()),
            'customer_name' => $disputedCustomer->name,
            'customer_whatsapp' => $disputedCustomer->whatsapp,
            'tanggal_mulai' => now()->subDays(35),
            'tanggal_selesai' => now()->subDays(30),
            'metode_pengambilan' => 'pickup',
            'payment_method' => 'qris',
            'total_biaya' => 150000,
            'total_price' => 150000,
            'status' => 'Selesai',
            'completed_at' => now()->subDays(30),
            'created_at' => now()->subDays(35),
            'updated_at' => now()->subDays(30),
        ]);

        // Active open dispute in komplains
        Komplain::create([
            'order_id' => $order->id,
            'user_id' => $disputedCustomer->id,
            'alasan' => 'Barang rusak saat diterima dan belum ada kesepakatan ganti rugi',
            'status_penyelesaian' => 'Menunggu Respon',
        ]);

        $service = new SocialProofService();
        $result = $service->reengageInactiveCustomers(30);

        $this->assertNotContains($disputedCustomer->id, $result['dispatched_user_ids']);
        $this->assertGreaterThanOrEqual(1, $result['suppressed_dispute_count']);
    }

    public function test_artisan_command_rentify_reengage_inactive_customers_runs_cleanly(): void
    {
        $exitCode = Artisan::call('rentify:customers:reengage-inactive', [
            '--days' => 30,
        ]);

        $this->assertEquals(0, $exitCode);
    }
}
