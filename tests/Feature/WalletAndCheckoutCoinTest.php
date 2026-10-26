<?php

namespace Tests\Feature;

use App\Models\Moment;
use App\Models\MomentCoinLedger;
use App\Models\Penyewaan;
use App\Models\User;
use App\Models\UserCoinDeficit;
use App\Services\CoinAccountingService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class WalletAndCheckoutCoinTest extends TestCase
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
            'status' => 'Menunggu Konfirmasi',
            'tanggal_mulai' => now()->toDateString(),
            'tanggal_selesai' => now()->addDays(2)->toDateString(),
        ]);
    }

    public function test_wallet_api_requires_authentication(): void
    {
        $resIndex = $this->getJson('/api/wallet/coins');
        $resIndex->assertStatus(401);

        $resCalc = $this->postJson('/api/wallet/coins/calculate-discount', [
            'coins' => 10,
            'base_subtotal' => 60000,
        ]);
        $resCalc->assertStatus(401);
    }

    public function test_wallet_api_returns_balances_and_mutation_history(): void
    {
        $user = User::create([
            'name' => 'Wallet User',
            'email' => 'wallet_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'customer',
        ]);

        $order = $this->createTestOrder($user);

        // Beri 10 koin
        MomentCoinLedger::create([
            'user_id' => $user->id,
            'interaction_type' => 'like_mint',
            'coins_amount' => 10,
            'remaining_lot_balance' => 10,
            'minted_coin' => true,
        ]);

        // Belanjakan 3 koin
        $this->service->spendCoins($user->id, 3, $order->id);

        // Catat defisit 1 koin
        UserCoinDeficit::create([
            'user_id' => $user->id,
            'deficit_coins' => 1,
        ]);

        $response = $this->actingAs($user)->getJson('/api/wallet/coins');
        $response->assertStatus(200);

        $data = $response->json();
        $this->assertTrue($data['success']);
        $this->assertEquals(7, $data['data']['coin_balance']);
        $this->assertEquals(700, $data['data']['rupiah_equivalent']);
        $this->assertEquals(1, $data['data']['deficit_coins']);
        $this->assertEquals(100, $data['data']['deficit_rupiah']);
        $this->assertNotEmpty($data['data']['mutations']['data']);
    }

    public function test_calculate_discount_api_endpoint(): void
    {
        $user = User::create([
            'name' => 'Discount API User',
            'email' => 'disc_api_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'customer',
        ]);

        MomentCoinLedger::create([
            'user_id' => $user->id,
            'interaction_type' => 'like_mint',
            'coins_amount' => 300,
            'remaining_lot_balance' => 300,
            'minted_coin' => true,
        ]);

        // Subtotal Rp 80.000, 25% max = 200 koin (Rp 20.000)
        $response = $this->actingAs($user)->postJson('/api/wallet/coins/calculate-discount', [
            'coins' => 150,
            'base_subtotal' => 80000,
        ]);

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertTrue($data['success']);
        $this->assertEquals(150, $data['data']['applicable_coins']);
        $this->assertEquals(15000, $data['data']['discount_rupiah']);
        $this->assertEquals(200, $data['data']['max_allowed_coins']);
    }

    public function test_order_cancellation_triggers_coin_refund_and_deficit_prioritization(): void
    {
        $customer = User::create([
            'name' => 'Cancel Order Customer',
            'email' => 'cancel_cust_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'customer',
        ]);

        $order = $this->createTestOrder($customer);

        // Simulasikan checkout yang memakai 5 koin
        MomentCoinLedger::create([
            'user_id' => $customer->id,
            'order_id' => $order->id,
            'interaction_type' => 'checkout_spend',
            'coins_amount' => -5,
            'remaining_lot_balance' => 0,
            'minted_coin' => false,
        ]);

        // Catat user punya utang defisit 2 koin
        UserCoinDeficit::create([
            'user_id' => $customer->id,
            'deficit_coins' => 2,
        ]);

        $this->assertEquals(0, $this->service->getAvailableBalance($customer->id));
        $this->assertEquals(2, $this->service->getDeficitBalance($customer->id));

        // Customer membatalkan pesanan via POST /customer/pesanan/{id}/batal
        $response = $this->actingAs($customer)->post("/customer/pesanan/{$order->id}/batal");
        $response->assertStatus(302); // Redirect back with success message

        // 5 koin di-refund:
        // - 2 koin melunasi defisit utang -> sisa defisit = 0
        // - 3 koin kembali ke saldo dompet -> saldo dompet = 3 koin
        $this->assertEquals(0, $this->service->getDeficitBalance($customer->id));
        $this->assertEquals(3, $this->service->getAvailableBalance($customer->id));

        $order->refresh();
        $this->assertEquals('Dibatalkan', $order->status);
    }
}
