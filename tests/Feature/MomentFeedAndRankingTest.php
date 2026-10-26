<?php

namespace Tests\Feature;

use App\Models\Moment;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class MomentFeedAndRankingTest extends TestCase
{
    use DatabaseTransactions;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'name'     => 'Feed Test User',
            'email'    => 'feed_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role'     => 'customer',
        ]);
    }

    public function test_ranking_score_formula_and_gravity_decay(): void
    {
        // 1. Post baru (0 jam lalu, created_at = now())
        // likes = 10, bookmarks = 5
        // numerator = (10 * 0.6) + (5 * 0.4) = 6 + 2 = 8
        // gravity = pow(0 + 2, 1.5) = pow(2, 1.5) = 2.828427...
        // expected score = 8 / 2.828427 = 2.8284
        $momentFresh = Moment::create([
            'user_id'         => $this->user->id,
            'media_url'       => 'https://res.cloudinary.com/rentify/image/upload/v1/fresh.jpg',
            'likes_count'     => 10,
            'bookmarks_count' => 5,
            'status'          => 'active',
            'created_at'      => now(),
        ]);

        $scoreFresh = $momentFresh->calculateRankingScore();
        $this->assertEqualsWithDelta(2.8284, $scoreFresh, 0.001);

        // 2. Post lama (22 jam lalu)
        // likes = 10, bookmarks = 5
        // numerator = 8
        // gravity = pow(22 + 2, 1.5) = pow(24, 1.5) = 117.5755...
        // expected score = 8 / 117.5755 = 0.0680
        $momentOld = Moment::create([
            'user_id'         => $this->user->id,
            'media_url'       => 'https://res.cloudinary.com/rentify/image/upload/v1/old.jpg',
            'likes_count'     => 10,
            'bookmarks_count' => 5,
            'status'          => 'active',
        ]);
        $momentOld->forceFill(['created_at' => now()->subHours(22)])->save();

        $scoreOld = $momentOld->calculateRankingScore();
        $this->assertEqualsWithDelta(0.0680, $scoreOld, 0.001);

        // Score fresh jauh lebih tinggi dari score old karena peluruhan waktu (gravity decay)
        $this->assertGreaterThan($scoreOld, $scoreFresh);
    }

    public function test_recalculate_ranking_scores_artisan_command(): void
    {
        $moment = Moment::create([
            'user_id'         => $this->user->id,
            'media_url'       => 'https://res.cloudinary.com/rentify/image/upload/v1/calc.jpg',
            'likes_count'     => 20,
            'bookmarks_count' => 10,
            'ranking_score'   => 0,
            'status'          => 'active',
        ]);
        $moment->forceFill(['created_at' => now()->subHours(2)])->save();

        $this->assertEquals(0, (float) $moment->ranking_score);

        // Jalankan perintah console recalculate
        Artisan::call('moments:recalculate-ranking-scores');

        $moment->refresh();
        $this->assertGreaterThan(0, (float) $moment->ranking_score);
    }

    public function test_feed_excludes_under_moderation_and_deleted_posts(): void
    {
        $activePost = Moment::create([
            'user_id'       => $this->user->id,
            'media_url'     => 'https://res.cloudinary.com/rentify/image/upload/v1/act.jpg',
            'status'        => 'active',
            'ranking_score' => 5.0,
        ]);

        $modPost = Moment::create([
            'user_id'       => $this->user->id,
            'media_url'     => 'https://res.cloudinary.com/rentify/image/upload/v1/mod.jpg',
            'status'        => 'under_moderation',
            'ranking_score' => 10.0,
        ]);

        $deletedPost = Moment::create([
            'user_id'       => $this->user->id,
            'media_url'     => 'https://res.cloudinary.com/rentify/image/upload/v1/del.jpg',
            'status'        => 'deleted',
            'ranking_score' => 15.0,
        ]);

        $response = $this->getJson('/api/moments');
        $response->assertStatus(200);

        $ids = collect($response->json('data'))->pluck('id')->all();

        $this->assertContains($activePost->id, $ids);
        $this->assertNotContains($modPost->id, $ids);
        $this->assertNotContains($deletedPost->id, $ids);
    }

    public function test_cursor_based_pagination_deterministic_fetching(): void
    {
        // Buat 12 postingan aktif dengan skor berbeda dalam 7 hari terakhir agar tidak starving
        $createdMoments = [];
        for ($i = 1; $i <= 12; $i++) {
            $createdMoments[] = Moment::create([
                'user_id'         => $this->user->id,
                'media_url'       => "https://res.cloudinary.com/rentify/image/upload/v1/cursor_{$i}.jpg",
                'status'          => 'active',
                'ranking_score'   => $i * 1.5,
                'likes_count'     => $i * 2,
                'bookmarks_count' => $i,
                'created_at'      => now()->subHours(10 - ($i % 5)),
            ]);
        }

        // Ambil batch pertama (limit 5)
        $page1Res = $this->getJson('/api/moments?limit=5');
        $page1Res->assertStatus(200);
        $page1Data = $page1Res->json();

        $this->assertCount(5, $page1Data['data']);
        $this->assertTrue($page1Data['has_more']);
        $this->assertNotNull($page1Data['next_cursor']);

        $page1Ids = collect($page1Data['data'])->pluck('id')->all();

        // Ambil batch kedua menggunakan next_cursor
        $cursor = urlencode($page1Data['next_cursor']);
        $page2Res = $this->getJson("/api/moments?limit=5&cursor={$cursor}");
        $page2Res->assertStatus(200);
        $page2Data = $page2Res->json();

        $page2Ids = collect($page2Data['data'])->pluck('id')->all();

        // Verifikasi tidak ada drift atau duplicate id antar page
        $intersect = array_intersect($page1Ids, $page2Ids);
        $this->assertEmpty($intersect, 'Halaman 1 dan 2 tidak boleh memiliki ID yang tumpang tindih.');
    }

    public function test_starvation_fallback_returns_all_time_top_engaged_moments(): void
    {
        // Buat 3 postingan lama (> 7 hari) dengan tingkat engagement berbeda
        // Post A: likes=50, bookmarks=20 (total engagement = 70)
        $topEngaged = Moment::create([
            'user_id'         => $this->user->id,
            'media_url'       => 'https://res.cloudinary.com/rentify/image/upload/v1/starv_top.jpg',
            'status'          => 'active',
            'likes_count'     => 50,
            'bookmarks_count' => 20,
            'ranking_score'   => 0.001,
        ]);
        $topEngaged->forceFill(['created_at' => now()->subDays(10)])->save();

        // Post B: likes=5, bookmarks=2 (total engagement = 7)
        $lowEngaged = Moment::create([
            'user_id'         => $this->user->id,
            'media_url'       => 'https://res.cloudinary.com/rentify/image/upload/v1/starv_low.jpg',
            'status'          => 'active',
            'likes_count'     => 5,
            'bookmarks_count' => 2,
            'ranking_score'   => 0.005,
        ]);
        $lowEngaged->forceFill(['created_at' => now()->subDays(9)])->save();

        // Karena dalam 7 hari terakhir ada < 10 postingan, fallback_mode harus aktif
        $response = $this->getJson('/api/moments');
        $response->assertStatus(200);

        $this->assertTrue($response->json('fallback_mode'));
        $data = $response->json('data');

        // Post dengan total engagement 70 harus muncul di atas post dengan engagement 7
        $topIndex = null;
        $lowIndex = null;
        foreach ($data as $idx => $item) {
            if ($item['id'] === $topEngaged->id) {
                $topIndex = $idx;
            }
            if ($item['id'] === $lowEngaged->id) {
                $lowIndex = $idx;
            }
        }

        $this->assertNotNull($topIndex);
        $this->assertNotNull($lowIndex);
        $this->assertLessThan($lowIndex, $topIndex, 'Top engaged moment harus muncul lebih awal dalam starvation fallback mode.');
    }
}
