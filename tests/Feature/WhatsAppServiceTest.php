<?php

namespace Tests\Feature;

use App\Services\WhatsAppService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class WhatsAppServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config([
            'services.whatsapp.provider' => 'self_hosted',
            'services.whatsapp.gateway_url' => 'http://127.0.0.1:3000',
            'services.whatsapp.gateway_secret' => 'rentify-wa-secret-2026',
        ]);
    }

    public function test_send_via_self_hosted_dispatches_correct_payload_and_secret(): void
    {
        Http::fake([
            'http://127.0.0.1:3000/send' => Http::response([
                'status' => true,
                'messageId' => 'mock-msg-123',
                'target' => '6281234567890@s.whatsapp.net',
            ], 200),
        ]);

        $result = WhatsAppService::send('081234567890', 'Halo dari Rentify');

        $this->assertTrue($result['status']);
        $this->assertEquals('mock-msg-123', $result['messageId']);

        Http::assertSent(function ($request) {
            return $request->url() === 'http://127.0.0.1:3000/send'
                && $request->hasHeader('X-Gateway-Secret', 'rentify-wa-secret-2026')
                && $request['target'] === '081234567890'
                && $request['message'] === 'Halo dari Rentify';
        });
    }

    public function test_send_handles_gateway_503_without_unhandled_crash(): void
    {
        Http::fake([
            'http://127.0.0.1:3000/send' => Http::response([
                'status' => false,
                'error' => 'WhatsApp client is not connected',
            ], 503),
        ]);

        $result = WhatsAppService::send('081234567890', 'Pesan saat disconnected');

        $this->assertFalse($result['status']);
        $this->assertEquals('WhatsApp client is not connected', $result['message']);
    }

    public function test_send_handles_connection_refused_resiliently(): void
    {
        Http::fake([
            'http://127.0.0.1:3000/send' => function () {
                throw new \Illuminate\Http\Client\ConnectionException('Connection refused');
            },
        ]);

        $result = WhatsAppService::send('081234567890', 'Pesan saat server mati');

        $this->assertFalse($result['status']);
        $this->assertStringContainsString('Connection refused', $result['message']);
    }

    public function test_get_gateway_status_returns_structured_payload(): void
    {
        Http::fake([
            'http://127.0.0.1:3000/status' => Http::response([
                'status' => 'unpaired',
                'paired' => false,
                'qr' => 'data:image/png;base64,mockqr',
                'user' => null,
                'uptime' => 120,
            ], 200),
        ]);

        $status = WhatsAppService::getGatewayStatus();

        $this->assertEquals('unpaired', $status['status']);
        $this->assertFalse($status['paired']);
        $this->assertNotNull($status['qr']);
    }
}
