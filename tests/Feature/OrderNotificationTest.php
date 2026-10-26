<?php

namespace Tests\Feature;

use App\Services\OrderNotificationService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class OrderNotificationTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        config([
            'services.whatsapp.provider' => 'self_hosted',
            'services.whatsapp.gateway_url' => 'http://127.0.0.1:3000',
            'services.whatsapp.gateway_secret' => 'rentify-wa-secret-2026',
        ]);
    }

    public function test_notify_order_created_sends_whatsapp_to_customer_and_vendor(): void
    {
        Http::fake([
            'http://127.0.0.1:3000/send' => Http::response(['status' => true, 'messageId' => 'mock-id-1'], 200),
        ]);

        $vendorId = DB::table('users')->insertGetId([
            'name' => 'Kamera Vendor Test',
            'email' => 'vendor_notif_' . uniqid() . '@example.com',
            'password' => bcrypt('password123'),
            'role' => 'vendor',
            'vendor_name' => 'Kamera Vendor Store',
            'whatsapp' => '081299990001',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $orderId = DB::table('orders')->insertGetId([
            'user_id' => $vendorId,
            'vendor_id' => $vendorId,
            'customer_name' => 'Budi Santoso',
            'customer_whatsapp' => '081234567890',
            'metode_pengambilan' => 'Ambil Sendiri',
            'tanggal_mulai' => now()->toDateString(),
            'tanggal_selesai' => now()->addDays(2)->toDateString(),
            'kode_booking' => 'RNTTEST01',
            'total_biaya' => 250000,
            'total_price' => 250000,
            'payment_method' => 'QRIS',
            'status' => 'PENDING_PAYMENT',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('order_items')->insert([
            'order_id' => $orderId,
            'product_id' => 1,
            'product_name' => 'Sony A6400 Mirrorless',
            'price' => 250000,
            'quantity' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $order = DB::table('orders')->where('id', $orderId)->first();

        OrderNotificationService::notifyOrderCreated($order);

        // Pastikan terkirim ke customer (081234567890) dan vendor (081299990001)
        Http::assertSent(function ($request) use ($orderId) {
            return $request['target'] === '081234567890'
                && str_contains($request['message'], "INV-{$orderId}")
                && str_contains($request['message'], 'QRIS Dinamis');
        });

        Http::assertSent(function ($request) use ($orderId) {
            return $request['target'] === '081299990001'
                && str_contains($request['message'], "INV-{$orderId}")
                && str_contains($request['message'], 'PESANAN SEWA BARU MASUK');
        });
    }

    public function test_notify_payment_confirmed_sends_official_receipt(): void
    {
        Http::fake([
            'http://127.0.0.1:3000/send' => Http::response(['status' => true, 'messageId' => 'mock-id-2'], 200),
        ]);

        $vendorId = DB::table('users')->insertGetId([
            'name' => 'Camping Store Vendor',
            'email' => 'vendor_camp_' . uniqid() . '@example.com',
            'password' => bcrypt('password123'),
            'role' => 'vendor',
            'vendor_name' => 'Camping Outdoor',
            'whatsapp' => '081299990002',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $orderId = DB::table('orders')->insertGetId([
            'user_id' => $vendorId,
            'vendor_id' => $vendorId,
            'customer_name' => 'Siti Rahma',
            'customer_whatsapp' => '087811112222',
            'metode_pengambilan' => 'Ambil Sendiri',
            'tanggal_mulai' => now()->toDateString(),
            'tanggal_selesai' => now()->addDays(2)->toDateString(),
            'kode_booking' => 'RNTTEST02',
            'total_biaya' => 150000,
            'total_price' => 150000,
            'payment_method' => 'QRIS',
            'status' => 'diproses',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('order_items')->insert([
            'order_id' => $orderId,
            'product_id' => 1,
            'product_name' => 'Tenda Dome 4 Orang',
            'price' => 150000,
            'quantity' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $order = DB::table('orders')->where('id', $orderId)->first();

        OrderNotificationService::notifyPaymentConfirmed($order, 'QRIS (BCA Mobile)', 150000);

        Http::assertSent(function ($request) use ($orderId) {
            return $request['target'] === '087811112222'
                && str_contains($request['message'], 'PEMBAYARAN DIVERIFIKASI LUNAS')
                && str_contains($request['message'], "INV-{$orderId}")
                && str_contains($request['message'], '150.000');
        });

        Http::assertSent(function ($request) use ($orderId) {
            return $request['target'] === '081299990002'
                && str_contains($request['message'], 'PESANAN SEWA TERBAYAR LUNAS')
                && str_contains($request['message'], "INV-{$orderId}");
        });
    }

    public function test_notify_order_status_updated_sends_lifecycle_updates(): void
    {
        Http::fake([
            'http://127.0.0.1:3000/send' => Http::response(['status' => true, 'messageId' => 'mock-id-3'], 200),
        ]);

        $vendorId = DB::table('users')->insertGetId([
            'name' => 'Rental PS Vendor',
            'email' => 'vendor_ps_' . uniqid() . '@example.com',
            'password' => bcrypt('password123'),
            'role' => 'vendor',
            'vendor_name' => 'PS Rental Game',
            'whatsapp' => '081299990003',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $orderId = DB::table('orders')->insertGetId([
            'user_id' => $vendorId,
            'vendor_id' => $vendorId,
            'customer_name' => 'Rian Pratama',
            'customer_whatsapp' => '085712345678',
            'metode_pengambilan' => 'Ambil Sendiri',
            'tanggal_mulai' => now()->toDateString(),
            'tanggal_selesai' => now()->addDays(2)->toDateString(),
            'payment_method' => 'QRIS',
            'kode_booking' => 'RNTTEST03',
            'total_biaya' => 200000,
            'total_price' => 200000,
            'status' => 'Disetujui',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('order_items')->insert([
            'order_id' => $orderId,
            'product_id' => 1,
            'product_name' => 'PlayStation 5 Console',
            'price' => 200000,
            'quantity' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $order = DB::table('orders')->where('id', $orderId)->first();

        // 1. Status: Disetujui
        OrderNotificationService::notifyOrderStatusUpdated($order, 'Disetujui');
        Http::assertSent(function ($request) use ($orderId) {
            return $request['target'] === '085712345678'
                && str_contains($request['message'], 'PESANAN SEWA DISETUJUI')
                && str_contains($request['message'], "INV-{$orderId}");
        });

        // 2. Status: Sedang Disewa
        OrderNotificationService::notifyOrderStatusUpdated($order, 'Sedang Disewa');
        Http::assertSent(function ($request) use ($orderId) {
            return $request['target'] === '085712345678'
                && str_contains($request['message'], 'MASA SEWA AKTIF')
                && str_contains($request['message'], "INV-{$orderId}");
        });

        // 3. Status: Selesai
        OrderNotificationService::notifyOrderStatusUpdated($order, 'Selesai');
        Http::assertSent(function ($request) use ($orderId) {
            return $request['target'] === '085712345678'
                && str_contains($request['message'], 'SEWA SELESAI — TERIMA KASIH')
                && str_contains($request['message'], "INV-{$orderId}");
        });

        Http::assertSent(function ($request) use ($orderId) {
            return $request['target'] === '081299990003'
                && str_contains($request['message'], 'PENDAPATAN DITERUSKAN')
                && str_contains($request['message'], "INV-{$orderId}");
        });
    }

    public function test_notification_failure_does_not_throw_exception(): void
    {
        Http::fake([
            'http://127.0.0.1:3000/send' => Http::response(['error' => 'Internal Server Error'], 500),
        ]);

        $order = (object) [
            'id' => 9999,
            'customer_name' => 'Test Crash',
            'customer_whatsapp' => '081999999999',
            'kode_booking' => 'RNTFAIL',
            'total_biaya' => 100000,
            'payment_method' => 'QRIS',
        ];

        // Memastikan eksekusi aman tanpa uncaught exception
        OrderNotificationService::notifyOrderCreated($order);
        OrderNotificationService::notifyPaymentConfirmed($order);
        OrderNotificationService::notifyOrderStatusUpdated($order, 'Selesai');

        $this->assertTrue(true);
    }
}
