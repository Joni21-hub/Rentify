<?php

namespace Tests\Feature;

use App\Models\Barang;
use App\Models\Kategori;
use App\Models\Keranjang;
use App\Models\Moment;
use App\Models\MomentCoinLedger;
use App\Models\Penyewaan;
use App\Models\User;
use App\Models\UserCoinDeficit;
use App\Services\CoinAccountingService;
use App\Services\TransactionCalculationService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
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

    public function test_checkout_with_rentify_coins_redeems_balance_and_discounts_order_accurately(): void
    {
        $vendor = User::create([
            'name' => 'Vendor Coin Test',
            'email' => 'vendor_coin_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'vendor',
            'vendor_name' => 'Toko Kamera Koin',
            'is_active' => 1,
        ]);

        $kategori = Kategori::create([
            'nama' => 'Kamera ' . uniqid(),
            'slug' => 'kamera-' . uniqid(),
            'is_active' => 1,
        ]);

        $barang = Barang::create([
            'vendor_id' => $vendor->id,
            'kategori_id' => $kategori->id,
            'nama' => 'Sony FX3 Cinema',
            'slug' => 'sony-fx3-' . uniqid(),
            'deskripsi' => 'Kamera cinema profesional',
            'harga_sewa_harian' => 100000,
            'stok_total' => 5,
            'status' => 'tersedia',
            'is_approved' => 1,
        ]);

        $customer = User::create([
            'name' => 'Customer Coin Shopee',
            'email' => 'cust_shopee_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'customer',
            'is_active' => 1,
            'whatsapp' => '081234567890',
        ]);

        // Berikan saldo 300 koin (1 Koin = Rp 100 -> setara Rp 30.000)
        MomentCoinLedger::create([
            'user_id' => $customer->id,
            'interaction_type' => 'like_mint',
            'coins_amount' => 300,
            'remaining_lot_balance' => 300,
            'minted_coin' => true,
        ]);

        $this->assertEquals(300, $this->service->getAvailableBalance($customer->id));

        $cart = Keranjang::create([
            'user_id' => $customer->id,
            'barang_id' => $barang->id,
            'jumlah' => 1,
            'durasi_sewa' => 1,
        ]);

        // Simulasikan checkout dengan toggle koin aktif (use_coins = 1)
        // Subtotal sewa asli = Rp 100.000.
        // Cap 25% = Rp 25.000 (250 koin).
        // Saldo user 300 koin -> Hanya 250 koin yang boleh dipakai, sisa 50 koin.
        $response = $this->actingAs($customer)
            ->withSession(['checkout_selected_ids' => [$cart->id]])
            ->post(route('customer.checkout.store'), [
                'no_hp' => '081234567890',
                'metode_pembayaran' => 'QRIS',
                'durasi_sewa' => [$vendor->id => 1],
                'opsi_pengiriman' => [$vendor->id => 'ambil'],
                'ongkir_vendor' => [$vendor->id => 0],
                'jaminan' => [$vendor->id => 'KTP'],
                'use_coins' => '1',
            ]);

        $response->assertStatus(302);
        $response->assertRedirectContains('/qris/');

        // Periksa order di database
        $order = DB::table('orders')->where('user_id', $customer->id)->latest('id')->first();
        $this->assertNotNull($order);
        $this->assertEquals(250, (int) $order->koin_digunakan);
        $this->assertEquals(25000.0, (float) $order->potongan_koin);
        $this->assertEquals(100000.0, (float) $order->vendor_earning); // Hak vendor tidak dipotong koin platform
        $this->assertEquals(5000.0, (float) $order->rentify_fee);

        // Tagihan customer: (100.000 * 1.05) - 25.000 + kode_unik
        $expectedTotal = (105000 - 25000) + (int) $order->kode_unik;
        $this->assertEquals($expectedTotal, (float) $order->total_price);

        // Saldo koin user harus berkurang 250 koin, bersisa 50 koin
        $this->assertEquals(50, $this->service->getAvailableBalance($customer->id));

        // Catatan mutasi buku besar koin spend tercatat
        $spendLedger = MomentCoinLedger::where('order_id', $order->id)
            ->where('interaction_type', 'checkout_spend')
            ->first();
        $this->assertNotNull($spendLedger);
        $this->assertEquals(-250, $spendLedger->coins_amount);
    }

    public function test_checkout_below_minimum_subtotal_does_not_deduct_coins(): void
    {
        $vendor = User::create([
            'name' => 'Vendor Murah',
            'email' => 'vendor_murah_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'vendor',
            'vendor_name' => 'Toko Murah',
            'is_active' => 1,
        ]);

        $kategori = Kategori::create([
            'nama' => 'Aksesoris ' . uniqid(),
            'slug' => 'aksesoris-' . uniqid(),
            'is_active' => 1,
        ]);

        $barang = Barang::create([
            'vendor_id' => $vendor->id,
            'kategori_id' => $kategori->id,
            'nama' => 'Kabel HDMI 2m',
            'slug' => 'kabel-hdmi-' . uniqid(),
            'deskripsi' => 'Kabel HDMI kecepatan tinggi',
            'harga_sewa_harian' => 25000, // Di bawah Rp 50.000
            'stok_total' => 10,
            'status' => 'tersedia',
            'is_approved' => 1,
        ]);

        $customer = User::create([
            'name' => 'Customer Belanja Murah',
            'email' => 'cust_murah_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'customer',
            'is_active' => 1,
        ]);

        // Beri saldo 100 koin
        MomentCoinLedger::create([
            'user_id' => $customer->id,
            'interaction_type' => 'like_mint',
            'coins_amount' => 100,
            'remaining_lot_balance' => 100,
            'minted_coin' => true,
        ]);

        $cart = Keranjang::create([
            'user_id' => $customer->id,
            'barang_id' => $barang->id,
            'jumlah' => 1,
            'durasi_sewa' => 1,
        ]);

        // Checkout dengan use_coins = 1
        $response = $this->actingAs($customer)
            ->withSession(['checkout_selected_ids' => [$cart->id]])
            ->post(route('customer.checkout.store'), [
                'no_hp' => '081234567890',
                'metode_pembayaran' => 'QRIS',
                'durasi_sewa' => [$vendor->id => 1],
                'opsi_pengiriman' => [$vendor->id => 'ambil'],
                'ongkir_vendor' => [$vendor->id => 0],
                'jaminan' => [$vendor->id => 'KTP'],
                'use_coins' => '1',
            ]);

        $response->assertStatus(302);

        $order = DB::table('orders')->where('user_id', $customer->id)->latest('id')->first();
        $this->assertNotNull($order);
        // Karena subtotal sewa Rp 25.000 < Rp 50.000, diskon koin tidak diterapkan
        $this->assertEquals(0, (int) $order->koin_digunakan);
        $this->assertEquals(0.0, (float) $order->potongan_koin);
        $this->assertEquals(100, $this->service->getAvailableBalance($customer->id)); // Saldo tetap 100
    }

    public function test_cod_settlement_with_coin_discount_adjusts_platform_fee_cleanly(): void
    {
        $pricing = TransactionCalculationService::calculateOrderPricing(
            100000.0, // Subtotal asli vendor Rp 100.000
            0.0,      // Ongkir
            0.0,      // Voucher
            0,        // Kode unik
            15000.0   // Diskon Koin Rentify Rp 15.000 (150 koin)
        );

        // Fee platform 5% dari sewa dasar = Rp 5.000
        $this->assertEquals(5000.0, $pricing['rentify_fee']);
        // Hak vendor tetap penuh Rp 100.000
        $this->assertEquals(100000.0, $pricing['vendor_earning']);
        // Tagihan customer: sewa markup (105.000) - koin (15.000) = 90.000
        $this->assertEquals(90000.0, $pricing['total_biaya']);

        $vendor = User::create([
            'name' => 'Vendor COD Coin',
            'email' => 'vendor_cod_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'vendor',
            'is_active' => 1,
        ]);

        $customer = User::create([
            'name' => 'Cust COD Coin',
            'email' => 'cust_cod_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'customer',
            'is_active' => 1,
        ]);

        $orderId = DB::table('orders')->insertGetId([
            'user_id' => $customer->id,
            'vendor_id' => $vendor->id,
            'kode_booking' => 'RNT-COD-COIN-' . uniqid(),
            'customer_name' => $customer->name,
            'customer_whatsapp' => '081234567890',
            'metode_pengambilan' => 'ambil',
            'shipping_fee' => 0,
            'total_biaya' => $pricing['total_biaya'],
            'total_price' => $pricing['total_biaya'],
            'potongan_koin' => 15000,
            'koin_digunakan' => 150,
            'payment_method' => 'COD',
            'status' => 'Sedang Disewa',
            'tanggal_mulai' => now(),
            'tanggal_selesai' => now()->addDay(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Vendor memiliki saldo dompet awal Rp 20.000
        DB::table('saldos')->updateOrInsert(
            ['vendor_id' => $vendor->id],
            ['saldo_aktif' => 20000, 'saldo_ditahan' => 0]
        );

        // Saat COD diselesaikan:
        // Customer membayar tunai ke vendor Rp 90.000.
        // Hak vendor adalah Rp 100.000. Vendor kekurangan tunai Rp 10.000 karena customer pakai koin platform.
        // Komisi platform adalah Rp 5.000.
        // Net platform debit = 5.000 - 15.000 = -10.000 (artinya platform mengkredit Rp 10.000 ke saldo vendor!).
        // Saldo baru vendor = 20.000 + 10.000 = 30.000.
        // Total kekayaan vendor: Rp 90.000 (tunai) + Rp 10.000 (tambahan saldo) = Rp 100.000 (persis 100% vendor_earning!).
        $settleResult = TransactionCalculationService::settleOrderCompletion($orderId);

        $this->assertEquals('Selesai', $settleResult['status']);
        $this->assertEquals(30000.0, $settleResult['saldo_sesudah']);

        $saldoDb = DB::table('saldos')->where('vendor_id', $vendor->id)->first();
        $this->assertEquals(30000.0, (float) $saldoDb->saldo_aktif);
    }
}
