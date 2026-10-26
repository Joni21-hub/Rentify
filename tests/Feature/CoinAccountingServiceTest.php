<?php

namespace Tests\Feature;

use App\Models\Moment;
use App\Models\MomentCoinLedger;
use App\Models\MomentLike;
use App\Models\Penyewaan;
use App\Models\User;
use App\Models\UserCoinDeficit;
use App\Services\CoinAccountingService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Tests\TestCase;

class CoinAccountingServiceTest extends TestCase
{
    use DatabaseTransactions;

    protected CoinAccountingService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new CoinAccountingService();
    }

    protected function createTestOrder(User $customer): Penyewaan
    {
        return Penyewaan::create([
            'user_id' => $customer->id,
            'customer_name' => $customer->name,
            'customer_whatsapp' => '081234567890',
            'kode_booking' => Penyewaan::generateKodeBooking(),
            'payment_method' => 'manual_transfer',
            'metode_pengambilan' => 'ambil_sendiri',
            'total_biaya' => 100000,
            'total_deposit' => 0,
            'total_denda' => 0,
            'status' => 'menunggu_pembayaran',
            'tanggal_mulai' => now()->toDateString(),
            'tanggal_selesai' => now()->addDays(2)->toDateString(),
        ]);
    }

    public function test_fifo_lot_tracking_and_spend_coins_consumption(): void
    {
        $user = User::create([
            'name' => 'Koin User',
            'email' => 'koin_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'customer',
        ]);

        $order = $this->createTestOrder($user);

        $moment1 = Moment::create([
            'user_id' => $user->id,
            'media_url' => 'https://res.cloudinary.com/rentify/image/upload/v1/m1.jpg',
            'media_type' => 'image',
            'status' => 'active',
        ]);

        $moment2 = Moment::create([
            'user_id' => $user->id,
            'media_url' => 'https://res.cloudinary.com/rentify/image/upload/v1/m2.jpg',
            'media_type' => 'image',
            'status' => 'active',
        ]);

        // Lot 1: 5 koin (tertua)
        $lot1 = MomentCoinLedger::create([
            'user_id' => $user->id,
            'moment_id' => $moment1->id,
            'interaction_type' => 'like_mint',
            'coins_amount' => 5,
            'remaining_lot_balance' => 5,
            'minted_coin' => true,
            'created_at' => now()->subHours(2),
        ]);

        // Lot 2: 10 koin (lebih baru)
        $lot2 = MomentCoinLedger::create([
            'user_id' => $user->id,
            'moment_id' => $moment2->id,
            'interaction_type' => 'like_mint',
            'coins_amount' => 10,
            'remaining_lot_balance' => 10,
            'minted_coin' => true,
            'created_at' => now()->subHour(),
        ]);

        $this->assertEquals(15, $this->service->getAvailableBalance($user->id));

        // Belanjakan 7 koin: harus menghabiskan 5 koin dari lot 1 dan 2 koin dari lot 2
        $result = $this->service->spendCoins($user->id, 7, $order->id);

        $this->assertEquals(7, $result['spent']);
        $this->assertEquals(8, $result['remaining_balance']);

        $lot1->refresh();
        $lot2->refresh();

        $this->assertEquals(0, $lot1->remaining_lot_balance);
        $this->assertEquals(8, $lot2->remaining_lot_balance);
        $this->assertEquals(8, $this->service->getAvailableBalance($user->id));

        // Cek ledger checkout_spend dibuat
        $spendRecord = DB::table('moment_coin_ledgers')
            ->where('user_id', $user->id)
            ->where('interaction_type', 'checkout_spend')
            ->where('order_id', $order->id)
            ->first();
        $this->assertNotNull($spendRecord);
        $this->assertEquals(-7, $spendRecord->coins_amount);
    }

    public function test_spend_coins_rejects_when_balance_insufficient(): void
    {
        $user = User::create([
            'name' => 'Poor User',
            'email' => 'poor_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'customer',
        ]);

        $order = $this->createTestOrder($user);

        $this->expectException(RuntimeException::class);
        $this->service->spendCoins($user->id, 10, $order->id);
    }

    public function test_deficit_debt_recording_on_unlike_when_balance_zero(): void
    {
        $creator = User::create([
            'name' => 'Creator User',
            'email' => 'creator_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'customer',
        ]);

        $interactor = User::create([
            'name' => 'Fan User',
            'email' => 'fan_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'customer',
        ]);

        $moment = Moment::create([
            'user_id' => $creator->id,
            'media_url' => 'https://res.cloudinary.com/rentify/image/upload/v1/m3.jpg',
            'media_type' => 'image',
            'status' => 'active',
            'likes_count' => 1,
        ]);

        // Simulasikan like yang minted
        MomentLike::create([
            'moment_id' => $moment->id,
            'user_id' => $interactor->id,
        ]);

        MomentCoinLedger::create([
            'user_id' => $creator->id,
            'interactor_id' => $interactor->id,
            'moment_id' => $moment->id,
            'interaction_type' => 'like_mint',
            'coins_amount' => 1,
            'remaining_lot_balance' => 0, // Sudah dibelanjakan sebelumnya, sehingga sisa 0
            'minted_coin' => true,
        ]);

        $this->assertEquals(0, $this->service->getAvailableBalance($creator->id));
        $this->assertEquals(0, $this->service->getDeficitBalance($creator->id));

        // Fan melakukan unlike
        $unlikeResult = $this->service->recordUnlike($interactor->id, $moment->id);

        $this->assertTrue($unlikeResult['unliked']);
        $this->assertEquals(1, $unlikeResult['deficit_added']);
        $this->assertEquals(0, $unlikeResult['balance']);

        // Saldo tetap 0 (tidak pernah negatif) dan utang defisit 1 dicatat
        $this->assertEquals(0, $this->service->getAvailableBalance($creator->id));
        $this->assertEquals(1, $this->service->getDeficitBalance($creator->id));
    }

    public function test_automatic_deficit_settlement_priority_on_new_coin_mint(): void
    {
        $creator = User::create([
            'name' => 'Indebted Creator',
            'email' => 'indebted_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'customer',
        ]);

        // Catat defisit awal 2 koin
        UserCoinDeficit::create([
            'user_id' => $creator->id,
            'deficit_coins' => 2,
        ]);

        $this->assertEquals(2, $this->service->getDeficitBalance($creator->id));
        $this->assertEquals(0, $this->service->getAvailableBalance($creator->id));

        // Terima 1 koin baru dari like
        $res1 = $this->service->settleDeficitOrCredit($creator->id, 1, 'like_mint', null, null);

        $this->assertEquals(1, $res1['deficit_paid']);
        $this->assertEquals(0, $res1['credited_to_wallet']);
        $this->assertEquals(1, $res1['remaining_deficit']);
        $this->assertEquals(0, $this->service->getAvailableBalance($creator->id));

        // Terima 2 koin lagi: 1 untuk melunasi sisa defisit, 1 masuk dompet
        $res2 = $this->service->settleDeficitOrCredit($creator->id, 2, 'like_mint', null, null);

        $this->assertEquals(1, $res2['deficit_paid']);
        $this->assertEquals(1, $res2['credited_to_wallet']);
        $this->assertEquals(0, $res2['remaining_deficit']);
        $this->assertEquals(1, $this->service->getAvailableBalance($creator->id));
    }

    public function test_refund_order_coins_pays_off_deficit_first(): void
    {
        $user = User::create([
            'name' => 'Refund User',
            'email' => 'refund_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'customer',
        ]);

        $order = $this->createTestOrder($user);

        UserCoinDeficit::create([
            'user_id' => $user->id,
            'deficit_coins' => 3,
        ]);

        // Refund 5 koin dari order sewa yang dibatalkan
        $refundResult = $this->service->refundOrderCoins($user->id, 5, $order->id);

        $this->assertEquals(3, $refundResult['deficit_paid']);
        $this->assertEquals(2, $refundResult['credited_to_wallet']);
        $this->assertEquals(0, $refundResult['remaining_deficit']);
        $this->assertEquals(2, $this->service->getAvailableBalance($user->id));
    }

    public function test_calculate_checkout_discount_rules_and_floor_rounding(): void
    {
        $user = User::create([
            'name' => 'Discount User',
            'email' => 'discount_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'customer',
        ]);

        // Beri saldo 500 koin
        MomentCoinLedger::create([
            'user_id' => $user->id,
            'interaction_type' => 'like_mint',
            'coins_amount' => 500,
            'remaining_lot_balance' => 500,
            'minted_coin' => true,
        ]);

        // Skenario 1: Subtotal di bawah Rp 50.000 -> Diskon 0
        $resLow = $this->service->calculateCheckoutDiscount($user->id, 100, 45000);
        $this->assertEquals(0, $resLow['applicable_coins']);
        $this->assertEquals(0, $resLow['discount_rupiah']);

        // Skenario 2: Subtotal Rp 75.250 -> 25% = Rp 18.812,5 -> Floor kelipatan 100 = Rp 18.800 (188 koin)
        $resRounding = $this->service->calculateCheckoutDiscount($user->id, 200, 75250);
        $this->assertEquals(188, $resRounding['max_allowed_coins']);
        $this->assertEquals(188, $resRounding['applicable_coins']);
        $this->assertEquals(18800, $resRounding['discount_rupiah']);

        // Skenario 3: Subtotal Rp 100.000 -> 25% = 250 koin, user hanya minta 50 koin
        $resPartial = $this->service->calculateCheckoutDiscount($user->id, 50, 100000);
        $this->assertEquals(250, $resPartial['max_allowed_coins']);
        $this->assertEquals(50, $resPartial['applicable_coins']);
        $this->assertEquals(5000, $resPartial['discount_rupiah']);
    }

    public function test_clawback_moment_coins_on_post_deletion_with_mixed_fifo_lots(): void
    {
        $creator = User::create([
            'name' => 'Clawback Creator',
            'email' => 'clawback_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'customer',
        ]);

        $order = $this->createTestOrder($creator);

        // Post A
        $postA = Moment::create([
            'user_id' => $creator->id,
            'media_url' => 'https://res.cloudinary.com/rentify/image/upload/v1/postA.jpg',
            'media_type' => 'image',
            'status' => 'active',
        ]);

        // Post B
        $postB = Moment::create([
            'user_id' => $creator->id,
            'media_url' => 'https://res.cloudinary.com/rentify/image/upload/v1/postB.jpg',
            'media_type' => 'image',
            'status' => 'active',
        ]);

        // Post A dicetak 10 koin
        $lotA = MomentCoinLedger::create([
            'user_id' => $creator->id,
            'moment_id' => $postA->id,
            'interaction_type' => 'like_mint',
            'coins_amount' => 10,
            'remaining_lot_balance' => 10,
            'minted_coin' => true,
            'created_at' => now()->subHours(3),
        ]);

        // Post B dicetak 10 koin
        $lotB = MomentCoinLedger::create([
            'user_id' => $creator->id,
            'moment_id' => $postB->id,
            'interaction_type' => 'like_mint',
            'coins_amount' => 10,
            'remaining_lot_balance' => 10,
            'minted_coin' => true,
            'created_at' => now()->subHours(2),
        ]);

        // Creator membelanjakan 6 koin (karena FIFO, 6 koin terpotong dari Post A)
        $this->service->spendCoins($creator->id, 6, $order->id);

        $lotA->refresh();
        $this->assertEquals(4, $lotA->remaining_lot_balance);
        $this->assertEquals(14, $this->service->getAvailableBalance($creator->id));

        // Sekarang Post A dihapus:
        // - 4 koin unspent dari Post A langsung dicabut
        // - 6 koin spent dari Post A ditarik dari saldo aktif di Post B (lotB sisa 10 - 6 = 4 koin)
        // - Defisit yang tercipta = 0 karena Post B mencukupi
        $clawback = $this->service->clawbackMomentCoins($postA->id);

        $this->assertEquals(10, $clawback['total_minted']);
        $this->assertEquals(4, $clawback['unspent_revoked']);
        $this->assertEquals(6, $clawback['spent_recovered_from_lots']);
        $this->assertEquals(0, $clawback['deficit_created']);
        $this->assertEquals(4, $clawback['final_available_balance']);
        $this->assertEquals(0, $clawback['final_deficit']);

        // Invariant: User tidak pernah memiliki saldo positif dan defisit aktif secara bersamaan
        $this->assertEquals(4, $this->service->getAvailableBalance($creator->id));
        $this->assertEquals(0, $this->service->getDeficitBalance($creator->id));
    }

    public function test_clawback_creates_deficit_when_other_lots_insufficient(): void
    {
        $creator = User::create([
            'name' => 'Clawback Deficit Creator',
            'email' => 'clawback_def_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'customer',
        ]);

        $order = $this->createTestOrder($creator);

        $postA = Moment::create([
            'user_id' => $creator->id,
            'media_url' => 'https://res.cloudinary.com/rentify/image/upload/v1/postA_def.jpg',
            'media_type' => 'image',
            'status' => 'active',
        ]);

        $postB = Moment::create([
            'user_id' => $creator->id,
            'media_url' => 'https://res.cloudinary.com/rentify/image/upload/v1/postB_def.jpg',
            'media_type' => 'image',
            'status' => 'active',
        ]);

        // Post A dicetak 10 koin
        MomentCoinLedger::create([
            'user_id' => $creator->id,
            'moment_id' => $postA->id,
            'interaction_type' => 'like_mint',
            'coins_amount' => 10,
            'remaining_lot_balance' => 10,
            'minted_coin' => true,
            'created_at' => now()->subHours(3),
        ]);

        // Post B dicetak hanya 2 koin
        MomentCoinLedger::create([
            'user_id' => $creator->id,
            'moment_id' => $postB->id,
            'interaction_type' => 'like_mint',
            'coins_amount' => 2,
            'remaining_lot_balance' => 2,
            'minted_coin' => true,
            'created_at' => now()->subHours(2),
        ]);

        // Creator membelanjakan 10 koin (seluruh saldo Post A terbelanja)
        $this->service->spendCoins($creator->id, 10, $order->id);

        // Saat ini: Post A sisa 0 (terbelanja 10), Post B sisa 2. Total saldo = 2.
        $this->assertEquals(2, $this->service->getAvailableBalance($creator->id));

        // Sekarang Post A dihapus:
        // - unspent pada Post A = 0
        // - spent pada Post A = 10
        // - Saldo pada lot lain (Post B) hanya ada 2 koin -> ditarik habis (sisa 0)
        // - Sisa kewajiban 8 koin dicatat ke user_coin_deficits
        $clawback = $this->service->clawbackMomentCoins($postA->id);

        $this->assertEquals(10, $clawback['total_minted']);
        $this->assertEquals(0, $clawback['unspent_revoked']);
        $this->assertEquals(2, $clawback['spent_recovered_from_lots']);
        $this->assertEquals(8, $clawback['deficit_created']);
        $this->assertEquals(0, $clawback['final_available_balance']);
        $this->assertEquals(8, $clawback['final_deficit']);

        // Invariant: saldo 0, defisit 8
        $this->assertEquals(0, $this->service->getAvailableBalance($creator->id));
        $this->assertEquals(8, $this->service->getDeficitBalance($creator->id));
    }
}
