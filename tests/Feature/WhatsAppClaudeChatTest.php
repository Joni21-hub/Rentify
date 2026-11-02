<?php

namespace Tests\Feature;

use App\Http\Controllers\WhatsAppWebhookController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WhatsAppClaudeChatTest extends TestCase
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
            'services.anthropic.api_key'       => 'test-claude-key',
            'services.anthropic.model'         => 'claude-3-5-sonnet-20241022',
        ]);
    }

    public function test_customer_chat_uses_claude_sonnet_with_concise_comfortable_reply(): void
    {
        $claudeCalled = false;
        Http::fake([
            'http://127.0.0.1:3000/send' => Http::response(['status' => true], 200),
            'https://api.anthropic.com/v1/messages' => function ($request) use (&$claudeCalled) {
                $claudeCalled = true;
                $payload = json_decode($request->body(), true);

                $this->assertStringContainsString('claude-3-5-sonnet', $payload['model']);
                $this->assertLessThanOrEqual(250, $payload['max_tokens']);
                $this->assertStringContainsString('JANGAN PERNAH MENGIRIM TEKS PANJANG', $payload['system']);

                return Http::response([
                    'id'      => 'msg_cust_01',
                    'type'    => 'message',
                    'role'    => 'assistant',
                    'content' => [
                        ['type' => 'text', 'text' => 'Halo Kak! Sewa tenda camping ada nih, mau buat berapa orang dan tanggal berapa? 😊']
                    ],
                    'model'   => 'claude-3-5-sonnet-20241022',
                ], 200);
            },
        ]);

        $controller = new WhatsAppWebhookController();
        $request = Request::create('/webhook/whatsapp', 'POST', [
            'sender'  => '081298765432',
            'name'    => 'Budi',
            'message' => 'Halo mau tanya sewa tenda camping ada gak ya?',
        ]);

        $response = $controller->handle($request);

        $this->assertTrue($claudeCalled, 'Claude API harus dipanggil untuk chat customer');
        $this->assertEquals(200, $response->getStatusCode());
        $data = $response->getData(true);
        $this->assertStringContainsString('tenda camping', $data['reply']);
    }

    public function test_vendor_chat_uses_claude_sonnet_for_store_and_commission_inquiry(): void
    {
        $claudeCalled = false;
        Http::fake([
            'http://127.0.0.1:3000/send' => Http::response(['status' => true], 200),
            'https://api.anthropic.com/v1/messages' => function ($request) use (&$claudeCalled) {
                $claudeCalled = true;
                return Http::response([
                    'id'      => 'msg_vendor_01',
                    'type'    => 'message',
                    'role'    => 'assistant',
                    'content' => [
                        ['type' => 'text', 'text' => 'Buka toko di Rentify 100% gratis tanpa biaya admin, Kak. Komisi platform cuma 5% dari harga sewa barang saat laku. Mau sewakan barang apa nih?']
                    ],
                    'model'   => 'claude-3-5-sonnet-20241022',
                ], 200);
            },
        ]);

        $controller = new WhatsAppWebhookController();
        $request = Request::create('/webhook/whatsapp', 'POST', [
            'sender'  => '081311223344',
            'name'    => 'Toko Rental Jaya',
            'message' => 'Halo, potongan fee buat vendor berapa persen ya?',
        ]);

        $response = $controller->handle($request);

        $this->assertTrue($claudeCalled);
        $data = $response->getData(true);
        $this->assertStringContainsString('5%', $data['reply']);
        $this->assertStringContainsString('gratis', $data['reply']);
    }

    public function test_gemini_is_not_called_for_customer_or_vendor(): void
    {
        $geminiCalled = false;
        Http::fake([
            'http://127.0.0.1:3000/send' => Http::response(['status' => true], 200),
            'https://generativelanguage.googleapis.com/*' => function () use (&$geminiCalled) {
                $geminiCalled = true;
                return Http::response([], 200);
            },
            'https://api.anthropic.com/v1/messages' => Http::response([
                'id'      => 'msg_cust_02',
                'type'    => 'message',
                'role'    => 'assistant',
                'content' => [
                    ['type' => 'text', 'text' => 'Halo Kak! Ada yang bisa Ucup bantu? 😊']
                ],
                'model'   => 'claude-3-5-sonnet-20241022',
            ], 200),
        ]);

        $controller = new WhatsAppWebhookController();
        $request = Request::create('/webhook/whatsapp', 'POST', [
            'sender'  => '081298765432',
            'name'    => 'Pelanggan',
            'message' => 'Tes chat',
        ]);

        $controller->handle($request);

        $this->assertFalse($geminiCalled, 'Gemini API tidak boleh dipanggil sama sekali sesuai instruksi pengguna');
    }
}
