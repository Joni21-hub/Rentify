<?php

namespace Tests\Unit;

use App\DTOs\GroundingContextDTO;
use App\Models\Barang;
use App\Models\DetailPenyewaan;
use App\Models\Kategori;
use App\Models\Pembayaran;
use App\Models\Penyewaan;
use App\Models\User;
use App\Services\ContextGroundingService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ContextGroundingTest extends TestCase
{
    use DatabaseTransactions;

    protected ContextGroundingService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ContextGroundingService();
    }

    public function test_retrieval_of_active_orders_and_roles_by_user_id(): void
    {
        // 1. Arrange: Create customer and vendor
        $vendor = User::create([
            'name' => 'Vendor Test Studio',
            'email' => 'vendor_' . uniqid() . '@test.com',
            'password' => bcrypt('secret123'),
            'role' => 'vendor',
            'vendor_status' => 'approved',
            'vendor_name' => 'Studio Kamera Jogja',
            'whatsapp' => '089876543210',
        ]);

        $customer = User::create([
            'name' => 'Customer Budi Test',
            'email' => 'customer_' . uniqid() . '@test.com',
            'password' => bcrypt('secret123'),
            'role' => 'customer',
            'whatsapp' => '081234567890',
        ]);

        // Create category and product
        $kategori = Kategori::firstOrCreate(['nama' => 'Elektronik'], ['is_active' => true]);
        $barang = Barang::create([
            'vendor_id' => $vendor->id,
            'kategori_id' => $kategori->id,
            'nama' => 'Sony Alpha A7 IV',
            'slug' => 'sony-alpha-a7-iv-' . uniqid(),
            'deskripsi' => 'Kamera mirrorless full frame profesional',
            'harga_sewa_harian' => 150000,
            'deposit' => 100000,
            'kondisi' => 'Sangat Baik',
            'stok_total' => 2,
            'status' => 'tersedia',
        ]);

        // Active Order 1: Sedang Disewa with Pembayaran PAID
        $activeOrder = Penyewaan::create([
            'user_id' => $customer->id,
            'vendor_id' => $vendor->id,
            'kode_booking' => 'RNT' . strtoupper(uniqid()),
            'status' => 'sedang_disewa',
            'payment_method' => 'qris',
            'metode_pengambilan' => 'ambil',
            'total_biaya' => 300000,
            'tanggal_mulai' => Carbon::today(),
            'tanggal_selesai' => Carbon::today()->addDays(2),
            'paid_at' => Carbon::now(),
            'customer_name' => $customer->name,
            'customer_whatsapp' => $customer->whatsapp,
        ]);

        DetailPenyewaan::create([
            'order_id' => $activeOrder->id,
            'product_id' => $barang->id,
            'product_name' => $barang->nama,
            'price' => 150000,
            'quantity' => 1,
            'harga_sewa' => 150000,
            'jumlah' => 1,
        ]);

        Pembayaran::create([
            'order_id' => $activeOrder->id,
            'metode' => 'qris',
            'jumlah' => 300000,
            'status' => 'PAID',
            'paid_at' => Carbon::now(),
        ]);

        // Past Completed Order (status = selesai) -> should be in history, NOT active orders
        $completedOrder = Penyewaan::create([
            'user_id' => $customer->id,
            'vendor_id' => $vendor->id,
            'kode_booking' => 'RNT' . strtoupper(uniqid()),
            'status' => 'selesai',
            'payment_method' => 'qris',
            'metode_pengambilan' => 'ambil',
            'total_biaya' => 150000,
            'tanggal_mulai' => Carbon::today()->subDays(5),
            'tanggal_selesai' => Carbon::today()->subDays(4),
            'completed_at' => Carbon::now()->subDays(4),
            'paid_at' => Carbon::now()->subDays(5),
            'customer_name' => $customer->name,
            'customer_whatsapp' => $customer->whatsapp,
        ]);

        // 2. Act: Call getCustomerGroundingContext by User ID
        $context = $this->service->getCustomerGroundingContext(userId: $customer->id);

        // 3. Assert:
        $this->assertInstanceOf(GroundingContextDTO::class, $context);
        $this->assertEquals($customer->id, $context->userId);
        $this->assertEquals($customer->name, $context->userName);
        $this->assertTrue($context->isAuthenticated);
        $this->assertContains('customer', $context->roles);
        $this->assertEquals('customer', $context->primaryRole);

        // Active orders should contain activeOrder and exclude completedOrder
        $this->assertTrue($context->hasActiveOrders());
        $this->assertCount(1, $context->activeOrders);
        $this->assertEquals($activeOrder->kode_booking, $context->activeOrders[0]['kode_booking']);
        $this->assertEquals('sedang_disewa', $context->activeOrders[0]['status']);
        $this->assertEquals('paid', $context->activeOrders[0]['payment_state']);
        $this->assertTrue($context->activeOrders[0]['is_paid']);
        $this->assertEquals(300000, $context->activeOrders[0]['total_biaya']);
        $this->assertNotEmpty($context->activeOrders[0]['items']);
        $this->assertEquals($barang->nama, $context->activeOrders[0]['items'][0]['nama']);

        // Order history should contain completedOrder
        $this->assertCount(1, $context->orderHistory);
        $this->assertEquals($completedOrder->kode_booking, $context->orderHistory[0]['kode_booking']);
    }

    public function test_retrieval_of_active_orders_by_phone_number_with_format_variations(): void
    {
        // 1. Arrange: User with WhatsApp '081298765432'
        $rawNumber = '081298765432';
        $user = User::create([
            'name' => 'Sarah Pelanggan',
            'email' => 'sarah_' . uniqid() . '@test.com',
            'password' => bcrypt('password'),
            'role' => 'customer',
            'whatsapp' => $rawNumber,
        ]);

        $order = Penyewaan::create([
            'user_id' => $user->id,
            'kode_booking' => 'RNT_SARAH_' . uniqid(),
            'status' => 'menunggu_pembayaran',
            'payment_method' => 'transfer_bank',
            'metode_pengambilan' => 'ambil',
            'tanggal_mulai' => Carbon::today(),
            'tanggal_selesai' => Carbon::today()->addDays(2),
            'total_biaya' => 200000,
            'customer_name' => $user->name,
            'customer_whatsapp' => $rawNumber,
        ]);

        // 2. Act: Query using international format "+6281298765432"
        $contextInternational = $this->service->getCustomerGroundingContext(phoneNumber: '+6281298765432');

        // 3. Assert:
        $this->assertTrue($contextInternational->isAuthenticated);
        $this->assertEquals($user->id, $contextInternational->userId);
        $this->assertEquals('Sarah Pelanggan', $contextInternational->userName);
        $this->assertCount(1, $contextInternational->activeOrders);
        $this->assertEquals($order->kode_booking, $contextInternational->activeOrders[0]['kode_booking']);
        $this->assertEquals('pending', $contextInternational->activeOrders[0]['payment_state']);

        // Also query using format with dashes "0812-9876-5432"
        $contextDashed = $this->service->getCustomerGroundingContext(phoneNumber: '0812-9876-5432');
        $this->assertTrue($contextDashed->isAuthenticated);
        $this->assertEquals($user->id, $contextDashed->userId);
    }

    public function test_retrieval_for_vendor_and_multi_role_users(): void
    {
        // 1. Arrange: Vendor user
        $vendorUser = User::create([
            'name' => 'Pak Joko Vendor',
            'email' => 'joko_' . uniqid() . '@test.com',
            'password' => bcrypt('secret'),
            'role' => 'vendor',
            'vendor_status' => 'approved',
            'vendor_name' => 'Joko Outdoor Equipment',
            'whatsapp' => '081399887766',
        ]);

        // 2. Act
        $context = $this->service->getCustomerGroundingContext(userId: $vendorUser->id);

        // 3. Assert
        $this->assertEquals('vendor', $context->primaryRole);
        $this->assertContains('vendor', $context->roles);
        $this->assertContains('customer', $context->roles);

        // Admin user
        $adminUser = User::create([
            'name' => 'Admin Utama Rentify',
            'email' => 'admin_' . uniqid() . '@rentify.test',
            'password' => bcrypt('secret'),
            'role' => 'admin',
            'whatsapp' => '083183494835',
        ]);

        $adminContext = $this->service->getCustomerGroundingContext(userId: $adminUser->id);
        $this->assertEquals('admin', $adminContext->primaryRole);
        $this->assertContains('admin', $adminContext->roles);
    }

    public function test_retrieval_for_anonymous_guest_user(): void
    {
        // Act: No user ID, unknown phone number
        $context = $this->service->getCustomerGroundingContext(phoneNumber: '089999999999');

        // Assert:
        $this->assertFalse($context->isAuthenticated);
        $this->assertTrue($context->isGuest());
        $this->assertEquals('guest', $context->primaryRole);
        $this->assertEquals(['guest'], $context->roles);
        $this->assertEmpty($context->activeOrders);
        $this->assertFalse($context->hasActiveOrders());
    }

    public function test_payment_states_resolution_across_scenarios(): void
    {
        $testUser = User::create([
            'name' => 'Scenario User',
            'email' => 'scenario_' . uniqid() . '@test.com',
            'password' => bcrypt('password'),
            'role' => 'customer',
            'whatsapp' => '081200000000',
        ]);

        // Scenario A: COD order -> 'cod'
        $orderCod = Penyewaan::create([
            'user_id' => $testUser->id,
            'kode_booking' => 'RNT_COD_' . uniqid(),
            'status' => 'menunggu_konfirmasi',
            'payment_method' => 'COD',
            'metode_pengambilan' => 'ambil',
            'tanggal_mulai' => Carbon::today(),
            'tanggal_selesai' => Carbon::today()->addDays(1),
            'total_biaya' => 120000,
            'customer_name' => 'Scenario User',
            'customer_whatsapp' => '081200000001',
        ]);
        $stateCod = $this->service->resolvePaymentState($orderCod);
        $this->assertEquals('cod', $stateCod);

        // Scenario B: Pembayaran record with status 'Menunggu Konfirmasi'
        $orderWaiting = Penyewaan::create([
            'user_id' => $testUser->id,
            'kode_booking' => 'RNT_WAIT_' . uniqid(),
            'status' => 'menunggu_konfirmasi',
            'payment_method' => 'transfer_bank',
            'metode_pengambilan' => 'ambil',
            'tanggal_mulai' => Carbon::today(),
            'tanggal_selesai' => Carbon::today()->addDays(1),
            'total_biaya' => 250000,
            'customer_name' => 'Scenario User',
            'customer_whatsapp' => '081200000002',
        ]);
        Pembayaran::create([
            'order_id' => $orderWaiting->id,
            'metode' => 'transfer_bank',
            'jumlah' => 250000,
            'status' => 'Menunggu Konfirmasi',
        ]);
        $orderWaiting->load('pembayaran');
        $stateWaiting = $this->service->resolvePaymentState($orderWaiting);
        $this->assertEquals('waiting_confirmation', $stateWaiting);

        // Scenario C: Order marked as paid via paid_at
        $orderPaid = Penyewaan::create([
            'user_id' => $testUser->id,
            'kode_booking' => 'RNT_PAID_' . uniqid(),
            'status' => 'disetujui',
            'payment_method' => 'qris',
            'metode_pengambilan' => 'ambil',
            'tanggal_mulai' => Carbon::today(),
            'tanggal_selesai' => Carbon::today()->addDays(1),
            'total_biaya' => 350000,
            'paid_at' => Carbon::now(),
            'customer_name' => 'Scenario User',
            'customer_whatsapp' => '081200000003',
        ]);
        $statePaid = $this->service->resolvePaymentState($orderPaid);
        $this->assertEquals('paid', $statePaid);
    }

    public function test_order_query_guard_triggers_missing_record_notice_for_unknown_order(): void
    {
        // Act: User queries an unknown order ID that does not exist in the database
        $unknownOrderCode = 'RNT-GHOST-99999';
        $context = $this->service->getCustomerGroundingContext(
            orderQuery: $unknownOrderCode
        );

        // Assert: Guard triggers, orderFound = false, anti-hallucination notice provided
        $this->assertFalse($context->orderFound);
        $this->assertTrue($context->isOrderGuardTriggered);
        $this->assertTrue($context->hasOrderGuard());
        $this->assertNotNull($context->missingRecordNotice);
        $this->assertStringContainsString($unknownOrderCode, (string) $context->missingRecordNotice);
        $this->assertStringContainsString('tidak ditemukan dalam sistem', (string) $context->missingRecordNotice);
        $this->assertStringContainsString('valid', (string) $context->missingRecordNotice);

        // Prompt context should strictly mandate anti-hallucination behavior
        $systemPrompt = $context->toPromptContext();
        $this->assertStringContainsString('ORDER QUERY GUARD AKTIF - ANTI-HALUSINASI', $systemPrompt);
        $this->assertStringContainsString('Dilarang keras mengarang', $systemPrompt);
        $this->assertStringContainsString($unknownOrderCode, $systemPrompt);
    }

    public function test_order_query_guard_resolves_valid_known_order_correctly(): void
    {
        // 1. Arrange: Create legitimate order in database
        $customer = User::create([
            'name' => 'Andi Wijaya',
            'email' => 'andi_' . uniqid() . '@test.com',
            'password' => bcrypt('password'),
            'role' => 'customer',
            'whatsapp' => '081288887777',
        ]);

        $knownOrderCode = 'RNT-VALID-' . strtoupper(uniqid());
        $order = Penyewaan::create([
            'user_id' => $customer->id,
            'kode_booking' => $knownOrderCode,
            'status' => 'sedang_disewa',
            'payment_method' => 'qris',
            'metode_pengambilan' => 'ambil',
            'total_biaya' => 450000,
            'tanggal_mulai' => Carbon::today(),
            'tanggal_selesai' => Carbon::today()->addDays(3),
            'paid_at' => Carbon::now(),
            'customer_name' => 'Andi Wijaya',
            'customer_whatsapp' => '081288887777',
        ]);

        // 2. Act: User asks specifically about this valid order
        $context = $this->service->getCustomerGroundingContext(
            orderQuery: $knownOrderCode
        );

        // 3. Assert: Order found, no missing notice, queried order contains verified data
        $this->assertTrue($context->orderFound);
        $this->assertFalse($context->isOrderGuardTriggered);
        $this->assertFalse($context->hasOrderGuard());
        $this->assertNull($context->missingRecordNotice);
        $this->assertNotNull($context->queriedOrder);
        $this->assertEquals($knownOrderCode, $context->queriedOrder['kode_booking']);
        $this->assertEquals('sedang_disewa', $context->queriedOrder['status']);
        $this->assertEquals(450000, $context->queriedOrder['total_biaya']);
        $this->assertEquals('paid', $context->queriedOrder['payment_state']);

        // System prompt context should ground the LLM with verified facts
        $systemPrompt = $context->toPromptContext();
        $this->assertStringContainsString('DETAIL PESANAN SPESIFIK TERVERIFIKASI', $systemPrompt);
        $this->assertStringContainsString($knownOrderCode, $systemPrompt);
        $this->assertStringNotContainsString('ORDER QUERY GUARD AKTIF', $systemPrompt);
    }

    public function test_order_query_guard_extracts_and_guards_order_from_free_text_message(): void
    {
        // Act: User mentions unknown order inside free-text message
        $userMessage = 'Halo Ucup, tolong cek status pengiriman pesanan saya dengan kode RNT-UNKNOWN-5544 ya';
        $context = $this->service->getCustomerGroundingContext(
            options: ['message' => $userMessage]
        );

        // Assert: Service extracted RNT-UNKNOWN-5544 and triggered the anti-hallucination guard
        $this->assertFalse($context->orderFound);
        $this->assertTrue($context->isOrderGuardTriggered);
        $this->assertStringContainsString('RNT-UNKNOWN-5544', (string) $context->missingRecordNotice);
    }

    public function test_dto_serialization_and_helper_methods(): void
    {
        $dto = new GroundingContextDTO(
            userId: 42,
            userName: 'Testing User',
            phoneNumber: '08123456789',
            roles: ['customer'],
            primaryRole: 'customer',
            isAuthenticated: true,
            activeOrders: [['kode_booking' => 'RNT001', 'status' => 'diproses', 'total_biaya' => 100000]],
            channel: 'whatsapp'
        );

        $array = $dto->toArray();
        $this->assertEquals(42, $array['user_id']);
        $this->assertEquals('Testing User', $array['user_name']);
        $this->assertEquals('whatsapp', $array['channel']);
        $this->assertTrue($dto->hasActiveOrders());
        $this->assertFalse($dto->isGuest());
        $this->assertFalse($dto->hasOrderGuard());
        $this->assertNotEmpty($dto->toSystemPrompt());
    }
}
