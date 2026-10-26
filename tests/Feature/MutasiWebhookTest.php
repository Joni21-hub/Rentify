<?php

namespace Tests\Feature;

use App\Models\Mutasi;
use App\Models\Pembayaran;
use App\Models\User;
use App\Services\MutasiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class MutasiWebhookTest extends TestCase
{
    /**
     * Uji verifikasi parser notifikasi berbagai bank & e-wallet
     */
    public function test_mutasi_service_parses_various_notifications()
    {
        $service = new MutasiService();

        // 1. DANA
        $dana = $service->parseNotification("Kamu menerima uang Rp 50.142 dari John Doe via QRIS.");
        $this->assertEquals('DANA', $dana['app_name']);
        $this->assertEquals(50142.0, $dana['nominal']);
        $this->assertEquals('CR', $dana['tipe']);

        // 2. BCA Mobile
        $bca = $service->parseNotification("m-Transfer: Rp 150.000,00 dari BUDI SANTOSO BERHASIL");
        $this->assertEquals('BCA', $bca['app_name']);
        $this->assertEquals(150000.0, $bca['nominal']);
        $this->assertEquals('CR', $bca['tipe']);

        // 3. GoPay
        $gopay = $service->parseNotification("Penerimaan saldo GoPay Rp 75.000 dari SITI");
        $this->assertEquals('GOPAY', $gopay['app_name']);
        $this->assertEquals(75000.0, $gopay['nominal']);
        $this->assertEquals('CR', $gopay['tipe']);

        // 4. Debit / Transaksi Keluar harus diabaikan
        $debit = $service->parseNotification("Kamu telah melakukan pembayaran ke Indomaret sebesar Rp 25.000");
        $this->assertEquals('DB', $debit['tipe']);
    }

    /**
     * Uji webhook endpoint POST /api/webhook/mutasi dengan otentikasi secret
     */
    public function test_webhook_rejects_unauthorized_secret()
    {
        $response = $this->postJson('/api/webhook/mutasi', [
            'content' => 'Kamu menerima uang Rp 50.000',
        ], [
            'X-Webhook-Secret' => 'wrong-secret',
        ]);

        $response->assertStatus(401);
    }

    /**
     * Uji webhook endpoint POST /api/webhook/mutasi berhasil mencocokkan order
     */
    public function test_webhook_auto_matches_order()
    {
        // Siapkan user
        $user = User::first() ?? User::create([
            'name' => 'User Mutasi Test',
            'email' => 'mutasi_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'customer',
        ]);

        // Buat dummy pending order dengan nominal unik
        $nominalUnik = 49876.0;
        $orderId = DB::table('orders')->insertGetId([
            'user_id'            => $user->id,
            'kode_booking'       => 'TEST-' . uniqid(),
            'customer_name'      => 'Customer Test',
            'customer_whatsapp'  => '081234567890',
            'metode_pengambilan' => 'ambil',
            'tanggal_mulai'      => now(),
            'tanggal_selesai'    => now()->addDays(2),
            'payment_method'     => 'QRIS',
            'kode_unik'          => 876,
            'total_biaya'        => $nominalUnik,
            'total_price'        => $nominalUnik,
            'status'             => 'PENDING_PAYMENT',
            'created_at'         => now(),
            'updated_at'         => now(),
        ]);

        // Simulasikan webhook dari Android Notification Forwarder
        $secret = config('mutasi.webhook_secret', 'rentify_mutasi_secret_2026');

        $response = $this->postJson('/api/webhook/mutasi', [
            'app_name' => 'DANA',
            'title'    => 'DANA',
            'content'  => 'Kamu menerima uang Rp 49.876 dari John Doe via QRIS.',
        ], [
            'X-Webhook-Secret' => $secret,
        ]);

        $response->assertStatus(200);
        $this->assertTrue($response->json('success'));
        $this->assertTrue($response->json('matched'));
        $this->assertEquals($orderId, $response->json('order_id'));

        // Pastikan status order di database terupdate menjadi Menunggu Konfirmasi / Lunas
        $updatedOrder = DB::table('orders')->where('id', $orderId)->first();
        $this->assertEquals('Menunggu Konfirmasi', $updatedOrder->status);

        // Pastikan pembayaran tercatat lunas
        $pembayaran = Pembayaran::where('order_id', $orderId)->first();
        $this->assertNotNull($pembayaran);
        $this->assertEquals('Lunas', $pembayaran->status);
        $this->assertEquals($nominalUnik, (float) $pembayaran->jumlah);

        // Pastikan mutasi tercatat sebagai MATCHED
        $mutasi = Mutasi::where('order_id', $orderId)->first();
        $this->assertNotNull($mutasi);
        $this->assertEquals('MATCHED', $mutasi->status);
    }
}
