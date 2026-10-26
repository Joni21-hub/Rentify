<?php

namespace Tests\Feature;

use App\Models\Moment;
use App\Models\MomentCoinLedger;
use App\Models\MomentLike;
use App\Models\User;
use App\Models\UserCoinDeficit;
use App\Services\CoinAccountingService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class MomentLikeInteractionTest extends TestCase
{
    use DatabaseTransactions;

    protected CoinAccountingService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new CoinAccountingService();
    }

    public function test_guest_cannot_like_or_unlike_moment(): void
    {
        $creator = User::create([
            'name' => 'Creator Guest Test',
            'email' => 'creator_guest_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'customer',
        ]);

        $moment = Moment::create([
            'user_id' => $creator->id,
            'media_url' => 'https://res.cloudinary.com/rentify/image/upload/v1/test.jpg',
            'media_type' => 'image',
            'status' => 'active',
        ]);

        $postRes = $this->postJson("/api/moments/{$moment->id}/like");
        $postRes->assertStatus(401);

        $deleteRes = $this->deleteJson("/api/moments/{$moment->id}/like");
        $deleteRes->assertStatus(401);
    }

    public function test_authenticated_user_can_like_and_unlike_via_api(): void
    {
        $creator = User::create([
            'name' => 'Creator API',
            'email' => 'creator_api_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'customer',
        ]);

        $interactor = User::create([
            'name' => 'Interactor API',
            'email' => 'interactor_api_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'customer',
        ]);
        $interactor->forceFill(['created_at' => now()->subDays(4)])->save();

        $moment = Moment::create([
            'user_id' => $creator->id,
            'media_url' => 'https://res.cloudinary.com/rentify/image/upload/v1/api_test.jpg',
            'media_type' => 'image',
            'status' => 'active',
            'likes_count' => 0,
        ]);

        // 1. Like via API
        $responseLike = $this->actingAs($interactor)->postJson("/api/moments/{$moment->id}/like");
        $responseLike->assertStatus(200);
        $dataLike = $responseLike->json();
        $this->assertTrue($dataLike['success']);
        $this->assertTrue($dataLike['liked']);
        $this->assertEquals(1, $dataLike['likes_count']);
        $this->assertTrue($dataLike['minted']);

        $this->assertEquals(1, $this->service->getAvailableBalance($creator->id));
        $this->assertEquals(1, $moment->fresh()->likes_count);
        $this->assertEquals(1, $moment->fresh()->lifetime_minted_count);

        // 2. Unlike via API
        $responseUnlike = $this->actingAs($interactor)->deleteJson("/api/moments/{$moment->id}/like");
        $responseUnlike->assertStatus(200);
        $dataUnlike = $responseUnlike->json();
        $this->assertTrue($dataUnlike['success']);
        $this->assertFalse($dataUnlike['liked']);
        $this->assertEquals(0, $dataUnlike['likes_count']);
        $this->assertTrue($dataUnlike['deducted']);

        $this->assertEquals(0, $this->service->getAvailableBalance($creator->id));
        $this->assertEquals(0, $moment->fresh()->likes_count);
    }

    public function test_self_like_prevention_anti_fraud(): void
    {
        $creator = User::create([
            'name' => 'Self Liker',
            'email' => 'self_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'customer',
        ]);
        $creator->forceFill(['created_at' => now()->subDays(5)])->save();

        $moment = Moment::create([
            'user_id' => $creator->id,
            'media_url' => 'https://res.cloudinary.com/rentify/image/upload/v1/self.jpg',
            'media_type' => 'image',
            'status' => 'active',
        ]);

        $response = $this->actingAs($creator)->postJson("/api/moments/{$moment->id}/like");
        $response->assertStatus(422);
        $data = $response->json();
        $this->assertFalse($data['success']);
        $this->assertFalse($data['liked']);

        $this->assertEquals(0, $this->service->getAvailableBalance($creator->id));
    }

    public function test_account_age_less_than_3_days_does_not_mint_coin(): void
    {
        $creator = User::create([
            'name' => 'Creator Age Test',
            'email' => 'creator_age_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'customer',
        ]);

        // Akun baru dibuat 1 hari yang lalu
        $youngUser = User::create([
            'name' => 'Young User',
            'email' => 'young_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'customer',
        ]);
        $youngUser->forceFill(['created_at' => now()->subDay()])->save();

        $moment = Moment::create([
            'user_id' => $creator->id,
            'media_url' => 'https://res.cloudinary.com/rentify/image/upload/v1/young.jpg',
            'media_type' => 'image',
            'status' => 'active',
        ]);

        $res = $this->actingAs($youngUser)->postJson("/api/moments/{$moment->id}/like");
        $res->assertStatus(200);
        $data = $res->json();
        $this->assertTrue($data['success']);
        $this->assertTrue($data['liked']);
        $this->assertEquals(1, $data['likes_count']);
        $this->assertFalse($data['minted']); // Tidak menghasilkan koin

        $this->assertEquals(0, $this->service->getAvailableBalance($creator->id));
        $this->assertEquals(0, $moment->fresh()->lifetime_minted_count);
        $this->assertEquals(1, $moment->fresh()->likes_count);

        // Ketika di-unlike: tidak ada saldo yang terpotong dan tidak ada defisit
        $unlikeRes = $this->actingAs($youngUser)->deleteJson("/api/moments/{$moment->id}/like");
        $unlikeRes->assertStatus(200);
        $dataUnlike = $unlikeRes->json();
        $this->assertFalse($dataUnlike['deducted']);
        $this->assertEquals(0, $dataUnlike['deficit_added']);
        $this->assertEquals(0, $this->service->getDeficitBalance($creator->id));
    }

    public function test_post_lifetime_cap_500_coins(): void
    {
        $creator = User::create([
            'name' => 'Creator Cap Test',
            'email' => 'creator_cap_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'customer',
        ]);

        $moment = Moment::create([
            'user_id' => $creator->id,
            'media_url' => 'https://res.cloudinary.com/rentify/image/upload/v1/cap.jpg',
            'media_type' => 'image',
            'status' => 'active',
            'lifetime_minted_count' => 500, // Sudah mencapai cap 500 koin
            'likes_count' => 500,
        ]);

        $interactor = User::create([
            'name' => 'Liker Cap',
            'email' => 'liker_cap_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'customer',
        ]);
        $interactor->forceFill(['created_at' => now()->subDays(5)])->save();

        $res = $this->actingAs($interactor)->postJson("/api/moments/{$moment->id}/like");
        $res->assertStatus(200);
        $data = $res->json();
        $this->assertTrue($data['success']);
        $this->assertTrue($data['liked']);
        $this->assertEquals(501, $data['likes_count']);
        $this->assertFalse($data['minted']); // Cap tercapai: like bertambah, koin tidak dicetak

        $this->assertEquals(500, $moment->fresh()->lifetime_minted_count);
        $this->assertEquals(501, $moment->fresh()->likes_count);
        $this->assertEquals(0, $this->service->getAvailableBalance($creator->id));
    }

    public function test_relike_anti_griefing_cycle_restores_state_without_counter_creep(): void
    {
        $creator = User::create([
            'name' => 'Creator Relike',
            'email' => 'creator_relike_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'customer',
        ]);

        $interactor = User::create([
            'name' => 'Interactor Relike',
            'email' => 'interactor_relike_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'customer',
        ]);
        $interactor->forceFill(['created_at' => now()->subDays(4)])->save();

        $moment = Moment::create([
            'user_id' => $creator->id,
            'media_url' => 'https://res.cloudinary.com/rentify/image/upload/v1/relike.jpg',
            'media_type' => 'image',
            'status' => 'active',
            'likes_count' => 0,
            'lifetime_minted_count' => 0,
        ]);

        // Putaran 1: Like awal (minted = true)
        $this->actingAs($interactor)->postJson("/api/moments/{$moment->id}/like");
        $this->assertEquals(1, $moment->fresh()->lifetime_minted_count);
        $this->assertEquals(1, $this->service->getAvailableBalance($creator->id));

        // Putaran 2: Unlike (saldo dipotong kembali jadi 0)
        $this->actingAs($interactor)->deleteJson("/api/moments/{$moment->id}/like");
        $this->assertEquals(0, $this->service->getAvailableBalance($creator->id));
        $this->assertEquals(1, $moment->fresh()->lifetime_minted_count);

        // Putaran 3: Re-like (koin dipulihkan ke 1, lifetime_minted_count TIDAK bertambah)
        $resRelike = $this->actingAs($interactor)->postJson("/api/moments/{$moment->id}/like");
        $resRelike->assertStatus(200);
        $dataRelike = $resRelike->json();
        $this->assertTrue($dataRelike['minted']);
        $this->assertTrue($dataRelike['is_relike']);

        $this->assertEquals(1, $this->service->getAvailableBalance($creator->id));
        $this->assertEquals(1, $moment->fresh()->lifetime_minted_count); // Tetap 1, no counter creep!

        // Putaran 4-10: Ulangi like-unlike berkali-kali
        for ($i = 0; $i < 5; $i++) {
            $this->actingAs($interactor)->deleteJson("/api/moments/{$moment->id}/like");
            $this->actingAs($interactor)->postJson("/api/moments/{$moment->id}/like");
        }

        // Hasil akhir: lifetime counter tetap 1, saldo tetap 1, tidak ada defisit
        $this->assertEquals(1, $moment->fresh()->lifetime_minted_count);
        $this->assertEquals(1, $this->service->getAvailableBalance($creator->id));
        $this->assertEquals(0, $this->service->getDeficitBalance($creator->id));
    }
}
