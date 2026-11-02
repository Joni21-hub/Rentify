<?php

namespace Tests\Feature;

use App\Http\Controllers\WhatsAppWebhookController;
use App\Services\RentifyGuardianService;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WhatsAppUcupAssistantTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config([
            'services.whatsapp.provider'       => 'self_hosted',
            'services.whatsapp.gateway_url'    => 'http://127.0.0.1:3000',
            'services.whatsapp.gateway_secret' => 'rentify-wa-secret-2026',
            'security.admin_phone'             => '083183494835',
            'guardian.owner_phones'            => ['083183494835'],
            'rentify_ai.gemini.api_key'        => 'dummy-key',
        ]);
    }

    public function test_normal_customer_081262364197_receives_ucup_reply(): void
    {
        Http::fake([
            'http://127.0.0.1:3000/send' => Http::response(['status' => true], 200),
            'https://generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                ['text' => 'Halo Kak! Aku Ucup dari Rentify. Ada yang bisa Ucup bantu seputar sewa barang? 😊']
                            ]
                        ]
                    ]
                ]
            ], 200),
        ]);

        $controller = new WhatsAppWebhookController();
        $request = Request::create('/webhook/whatsapp', 'POST', [
            'sender'  => '081262364197',
            'name'    => 'Joni Utama',
            'message' => 'Halo mau sewa kamera dong',
        ]);

        $response = $controller->handle($request);

        $this->assertEquals(200, $response->getStatusCode());
        $data = $response->getData(true);
        $this->assertTrue($data['status']);
        $this->assertStringContainsString('Ucup', $data['reply']);
    }

    public function test_gemini_fallback_when_3_8_flash_is_503(): void
    {
        $callCount = 0;
        Http::fake([
            'http://127.0.0.1:3000/send' => Http::response(['status' => true], 200),
            'https://generativelanguage.googleapis.com/v1beta/models/gemini-3.8-flash:generateContent*' => Http::response([
                'error' => ['code' => 503, 'message' => 'High demand']
            ], 503),
            'https://generativelanguage.googleapis.com/v1beta/models/gemini-3.5-flash:generateContent*' => function () use (&$callCount) {
                $callCount++;
                return Http::response([
                    'candidates' => [
                        [
                            'content' => [
                                ['parts' => [['text' => 'Halo Kak! Aku Ucup dari Rentify, siap bantu sewa barang!']]]
                            ]
                        ]
                    ]
                ], 200);
            },
        ]);

        $controller = new WhatsAppWebhookController();
        $request = Request::create('/webhook/whatsapp', 'POST', [
            'sender'  => '081262364197',
            'name'    => 'Customer Test',
            'message' => 'mau tanya harga',
        ]);

        $response = $controller->handle($request);

        $this->assertEquals(200, $response->getStatusCode());
        $data = $response->getData(true);
        $this->assertTrue($data['status']);
        $this->assertStringContainsString('Ucup', $data['reply']);
        $this->assertGreaterThan(0, $callCount, 'Harus otomatis fallback ke gemini-3.5-flash saat 3.8-flash 503');
    }

    public function test_owner_reset_wa_command_triggers_session_ratchet_reset(): void
    {
        Http::fake([
            'http://127.0.0.1:3000/reset-chat-sessions' => Http::response(['status' => true], 200),
        ]);

        $guardian = app(RentifyGuardianService::class);
        $reply = $guardian->handleOwnerMessage('cup tolong reset wa dong karena ada menunggu pesan', '083183494835');

        $this->assertStringContainsString('SESI ENKRIPSI WHATSAPP BERHASIL DI-RESET', $reply);
        $this->assertStringContainsString('Menunggu pesan ini', $reply);

        Http::assertSent(function ($req) {
            return $req->url() === 'http://127.0.0.1:3000/reset-chat-sessions';
        });
    }

    public function test_owner_natural_help_command_presents_ucup_persona(): void
    {
        $guardian = app(RentifyGuardianService::class);
        $reply = $guardian->handleOwnerMessage('halo kamu siapa ucup', '083183494835');

        $this->assertStringContainsString('UCUP', $reply);
        $this->assertStringContainsString('BOS JONI', $reply);
        $this->assertStringContainsString('reset wa', $reply);
    }

    public function test_order_query_guard_intercepts_unknown_order_in_webhook(): void
    {
        Http::fake([
            'http://127.0.0.1:3000/send' => Http::response(['status' => true], 200),
        ]);

        $controller = new WhatsAppWebhookController();
        $request = Request::create('/webhook/whatsapp', 'POST', [
            'sender'  => '081262364197',
            'name'    => 'Pelanggan Uji',
            'message' => 'Halo Ucup, tolong cek status pesanan saya kode RNT-GHOST-888 ya',
        ]);

        $response = $controller->handle($request);

        $this->assertEquals(200, $response->getStatusCode());
        $data = $response->getData(true);
        $this->assertTrue($data['status']);
        $this->assertStringContainsString('RNT-GHOST-888', $data['reply']);
        $this->assertStringContainsString('tidak ditemukan dalam sistem kami', $data['reply']);
    }

    public function test_progressive_lead_capture_in_webhook(): void
    {
        Http::fake([
            'http://127.0.0.1:3000/send' => Http::response(['status' => true], 200),
        ]);

        $senderPhone = '0812' . rand(10000000, 99999999);
        $controller = new WhatsAppWebhookController();
        $request = Request::create('/webhook/whatsapp', 'POST', [
            'sender'  => $senderPhone,
            'name'    => 'Radit',
            'message' => 'Halo mau sewa tenda camping buat 4 hari budget 200000',
        ]);

        $response = $controller->handle($request);

        $this->assertEquals(200, $response->getStatusCode());
        $data = $response->getData(true);
        $this->assertTrue($data['status']);

        // Verify ChatSession captured the lead progressively
        $session = \App\Models\ChatSession::where('phone_number', $senderPhone)->first();
        $this->assertNotNull($session);
        $this->assertEquals('Radit', $session->customer_name);
        $this->assertEquals(4, $session->metadata['lead_profile']['duration_days']);
        $this->assertEquals(200000, $session->metadata['lead_profile']['budget_amount']);
    }
}
