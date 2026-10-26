<?php

namespace Tests\Feature;

use App\Models\Moment;
use App\Models\MomentBookmark;
use App\Models\MomentCoinLedger;
use App\Models\MomentModerationQueue;
use App\Models\MomentReport;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class MomentModerationWorkflowTest extends TestCase
{
    use DatabaseTransactions;

    protected User $author;
    protected User $user1;
    protected User $user2;
    protected User $admin;
    protected Moment $moment;

    protected function setUp(): void
    {
        parent::setUp();

        $this->author = User::create([
            'name'     => 'Author Mod User',
            'email'    => 'author_mod_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role'     => 'customer',
        ]);

        $this->user1 = User::create([
            'name'     => 'User 1 Mod',
            'email'    => 'user1_mod_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role'     => 'customer',
        ]);

        $this->user2 = User::create([
            'name'     => 'User 2 Mod',
            'email'    => 'user2_mod_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role'     => 'customer',
        ]);

        $this->admin = User::create([
            'name'     => 'Admin Mod',
            'email'    => 'admin_mod_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role'     => 'admin',
        ]);

        $this->moment = Moment::create([
            'user_id'         => $this->author->id,
            'media_url'       => 'https://res.cloudinary.com/rentify/image/upload/v1/sample_mod.jpg',
            'media_type'      => 'image',
            'status'          => 'active',
            'likes_count'     => 0,
            'bookmarks_count' => 0,
            'reports_count'   => 0,
            'ranking_score'   => 0,
        ]);
    }

    public function test_bookmark_and_unbookmark_toggle_updates_cache_and_table(): void
    {
        // 1. Unauthenticated bookmark attempt
        $unauthRes = $this->postJson("/api/moments/{$this->moment->id}/bookmark");
        $unauthRes->assertStatus(401);

        // 2. Authenticated user adds bookmark
        $resBookmark = $this->actingAs($this->user1)->postJson("/api/moments/{$this->moment->id}/bookmark");
        $resBookmark->assertStatus(200);
        $dataBookmark = $resBookmark->json();
        $this->assertTrue($dataBookmark['success']);
        $this->assertTrue($dataBookmark['bookmarked']);
        $this->assertEquals(1, $dataBookmark['bookmarks_count']);

        $this->assertTrue(DB::table('moment_bookmarks')->where([
            'user_id'   => $this->user1->id,
            'moment_id' => $this->moment->id,
        ])->exists());
        $this->assertEquals(1, $this->moment->fresh()->bookmarks_count);

        // 3. User unbookmarks
        $resUnbookmark = $this->actingAs($this->user1)->deleteJson("/api/moments/{$this->moment->id}/bookmark");
        $resUnbookmark->assertStatus(200);
        $dataUnbookmark = $resUnbookmark->json();
        $this->assertTrue($dataUnbookmark['success']);
        $this->assertFalse($dataUnbookmark['bookmarked']);
        $this->assertEquals(0, $dataUnbookmark['bookmarks_count']);

        $this->assertFalse(DB::table('moment_bookmarks')->where([
            'user_id'   => $this->user1->id,
            'moment_id' => $this->moment->id,
        ])->exists());
        $this->assertEquals(0, $this->moment->fresh()->bookmarks_count);
    }

    public function test_reporting_endpoint_validates_category_and_rejects_duplicate(): void
    {
        // Category invalid
        $invalidCatRes = $this->actingAs($this->user1)->postJson("/api/moments/{$this->moment->id}/report", [
            'category' => 'unsupported_category',
        ]);
        $invalidCatRes->assertStatus(422);

        // Valid report 1
        $report1Res = $this->actingAs($this->user1)->postJson("/api/moments/{$this->moment->id}/report", [
            'category'           => 'spam',
            'reason_description' => 'Iklan spam tidak relevan',
        ]);
        $report1Res->assertStatus(200);
        $this->assertTrue($report1Res->json('success'));
        $this->assertEquals(1, $this->moment->fresh()->reports_count);

        // Duplicate report from same user
        $dupRes = $this->actingAs($this->user1)->postJson("/api/moments/{$this->moment->id}/report", [
            'category' => 'spam',
        ]);
        $dupRes->assertStatus(422);
        $this->assertEquals('Anda sudah pernah melaporkan postingan ini.', $dupRes->json('message'));
        $this->assertEquals(1, $this->moment->fresh()->reports_count);
    }

    public function test_automatic_transition_to_under_moderation_when_reports_reach_five(): void
    {
        // Buat 5 user berbeda untuk melaporkan
        $reporters = [];
        for ($i = 1; $i <= 5; $i++) {
            $reporters[] = User::create([
                'name'     => "Reporter {$i}",
                'email'    => "reporter_{$i}_" . uniqid() . '@example.com',
                'password' => bcrypt('password'),
                'role'     => 'customer',
            ]);
        }

        // 4 laporan pertama: status tetap 'active'
        for ($i = 0; $i < 4; $i++) {
            $res = $this->actingAs($reporters[$i])->postJson("/api/moments/{$this->moment->id}/report", [
                'category' => 'inappropriate',
            ]);
            $res->assertStatus(200);
        }

        $this->assertEquals(4, $this->moment->fresh()->reports_count);
        $this->assertEquals('active', $this->moment->fresh()->status);
        $this->assertFalse(DB::table('moment_moderation_queue')->where('moment_id', $this->moment->id)->exists());

        // Laporan ke-5: memicu transisi otomatis ke under_moderation dan membuat antrean
        $res5 = $this->actingAs($reporters[4])->postJson("/api/moments/{$this->moment->id}/report", [
            'category' => 'fraud',
        ]);
        $res5->assertStatus(200);

        $this->assertEquals(5, $this->moment->fresh()->reports_count);
        $this->assertEquals('under_moderation', $this->moment->fresh()->status);

        $queueItem = DB::table('moment_moderation_queue')->where('moment_id', $this->moment->id)->first();
        $this->assertNotNull($queueItem);
        $this->assertEquals('pending', $queueItem->status);
        $this->assertEquals(5, $queueItem->total_reports);
    }

    public function test_admin_moderation_query_requires_admin_role_and_paginates(): void
    {
        // Buat queue item
        $queue = MomentModerationQueue::create([
            'moment_id'        => $this->moment->id,
            'status'           => 'pending',
            'total_reports'    => 5,
            'last_reported_at' => now(),
        ]);

        // Non-admin request
        $nonAdminRes = $this->actingAs($this->user1)->getJson('/api/admin/moderation');
        $nonAdminRes->assertStatus(403);

        // Admin request
        $adminRes = $this->actingAs($this->admin)->getJson('/api/admin/moderation');
        $adminRes->assertStatus(200);
        $adminData = $adminRes->json();
        $this->assertTrue($adminData['success']);
        $this->assertNotEmpty($adminData['data']['data']);
    }

    public function test_moderation_dismissal_restores_moment_status_to_active(): void
    {
        $this->moment->update(['status' => 'under_moderation', 'reports_count' => 5]);
        $queue = MomentModerationQueue::create([
            'moment_id'        => $this->moment->id,
            'status'           => 'pending',
            'total_reports'    => 5,
            'last_reported_at' => now(),
        ]);

        $dismissRes = $this->actingAs($this->admin)->postJson("/api/admin/moderation/{$queue->id}/dismiss", [
            'resolution_notes' => 'Laporan palsu, konten sesuai standar komunitas.',
        ]);

        $dismissRes->assertStatus(200);
        $this->assertTrue($dismissRes->json('success'));

        $this->assertEquals('dismissed', $queue->fresh()->status);
        $this->assertEquals($this->admin->id, $queue->fresh()->reviewed_by);
        $this->assertEquals('Laporan palsu, konten sesuai standar komunitas.', $queue->fresh()->resolution_notes);
        $this->assertEquals('active', $this->moment->fresh()->status);
    }

    public function test_moderation_upholding_marks_deleted_and_claws_back_coins(): void
    {
        $this->moment->update(['status' => 'under_moderation', 'reports_count' => 6]);
        $queue = MomentModerationQueue::create([
            'moment_id'        => $this->moment->id,
            'status'           => 'pending',
            'total_reports'    => 6,
            'last_reported_at' => now(),
        ]);

        // Simulasikan koin yang telah dicetak dari momen ini
        MomentCoinLedger::create([
            'user_id'               => $this->author->id,
            'interactor_id'         => $this->user1->id,
            'moment_id'             => $this->moment->id,
            'interaction_type'      => 'like_mint',
            'coins_amount'          => 1,
            'remaining_lot_balance' => 1,
            'minted_coin'           => true,
        ]);

        $upholdRes = $this->actingAs($this->admin)->postJson("/api/admin/moderation/{$queue->id}/uphold", [
            'resolution_notes' => 'Terbukti melanggar hak cipta.',
        ]);

        $upholdRes->assertStatus(200);
        $this->assertTrue($upholdRes->json('success'));

        $this->assertEquals('reviewed', $queue->fresh()->status);
        $this->assertEquals($this->admin->id, $queue->fresh()->reviewed_by);
        $this->assertEquals('Terbukti melanggar hak cipta.', $queue->fresh()->resolution_notes);
        $this->assertEquals('deleted', $this->moment->fresh()->status);

        // Verifikasi koin ditarik kembali
        $lot = MomentCoinLedger::where('moment_id', $this->moment->id)
            ->where('interaction_type', 'like_mint')
            ->first();
        $this->assertEquals(0, $lot->remaining_lot_balance);
        $this->assertTrue(DB::table('moment_coin_ledgers')->where([
            'moment_id'        => $this->moment->id,
            'interaction_type' => 'post_deletion_clawback',
        ])->exists());
    }
}
