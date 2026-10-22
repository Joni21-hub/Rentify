<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    /**
     * Mengirim pesan WhatsApp (Mendukung Gateway Mandiri Baileys & Fallback Fonnte)
     */
    public static function send($nomorTujuan, $pesan)
    {
        $provider = config('services.whatsapp.provider', env('WHATSAPP_PROVIDER', 'self_hosted'));

        if ($provider === 'self_hosted') {
            return self::sendViaSelfHosted($nomorTujuan, $pesan);
        }

        return self::sendViaFonnte($nomorTujuan, $pesan);
    }

    /**
     * Kirim via Self-Hosted Baileys Gateway (100% Gratis, Tanpa Watermark)
     */
    public static function sendViaSelfHosted($nomorTujuan, $pesan)
    {
        $gatewayUrl = rtrim(config('services.whatsapp.gateway_url', env('WHATSAPP_GATEWAY_URL', 'http://127.0.0.1:3000')), '/');
        $secret = config('services.whatsapp.gateway_secret', env('WHATSAPP_GATEWAY_SECRET', 'rentify-wa-secret-2026'));

        try {
            $response = Http::timeout(8)
                ->withHeaders([
                    'X-Gateway-Secret' => $secret,
                    'Accept'           => 'application/json',
                ])
                ->post("{$gatewayUrl}/send", [
                    'target'  => $nomorTujuan,
                    'message' => $pesan,
                ]);

            if ($response->successful()) {
                return $response->json();
            }

            Log::warning('WhatsApp Gateway Self-Hosted send failed:', [
                'status' => $response->status(),
                'body'   => $response->body(),
                'target' => $nomorTujuan,
            ]);

            return [
                'status'  => false,
                'message' => $response->json('error') ?? 'Gagal mengirim pesan melalui WhatsApp Gateway',
            ];
        } catch (\Exception $e) {
            Log::error('WhatsApp Gateway Self-Hosted Exception: ' . $e->getMessage());
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Fallback: Kirim melalui Fonnte API
     */
    public static function sendViaFonnte($nomorTujuan, $pesan)
    {
        $token = env('FONNTE_TOKEN', 'TOKEN_FONNTE_ANDA_DISINI');

        try {
            $response = Http::withHeaders([
                'Authorization' => $token,
            ])->post('https://api.fonnte.com/send', [
                'target'      => $nomorTujuan,
                'message'     => $pesan,
                'countryCode' => '62',
            ]);

            return $response->json();
        } catch (\Exception $e) {
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Ambil status koneksi WhatsApp Gateway (QR Code, nomor terhubung, dsb)
     */
    public static function getGatewayStatus()
    {
        $gatewayUrl = rtrim(config('services.whatsapp.gateway_url', env('WHATSAPP_GATEWAY_URL', 'http://127.0.0.1:3000')), '/');

        try {
            $response = Http::timeout(3)->get("{$gatewayUrl}/status");
            if ($response->successful()) {
                return $response->json();
            }
        } catch (\Exception $e) {
            // Gateway mungkin sedang booting atau tidak aktif
        }

        return [
            'status' => 'offline',
            'qr'     => null,
            'user'   => null,
            'uptime' => 0,
        ];
    }

    /**
     * Reset chat session ratchets (memperbaiki Bad MAC tanpa harus scan QR ulang)
     */
    public static function resetChatSessions()
    {
        $gatewayUrl = rtrim(config('services.whatsapp.gateway_url', env('WHATSAPP_GATEWAY_URL', 'http://127.0.0.1:3000')), '/');
        $secret = config('services.whatsapp.gateway_secret', env('WHATSAPP_GATEWAY_SECRET', 'rentify-wa-secret-2026'));

        try {
            $response = Http::timeout(5)
                ->withHeaders(['X-Gateway-Secret' => $secret])
                ->post("{$gatewayUrl}/reset-chat-sessions");

            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Putuskan sesi WhatsApp dari Gateway
     */
    public static function logoutGateway()
    {
        $gatewayUrl = rtrim(config('services.whatsapp.gateway_url', env('WHATSAPP_GATEWAY_URL', 'http://127.0.0.1:3000')), '/');
        $secret = config('services.whatsapp.gateway_secret', env('WHATSAPP_GATEWAY_SECRET', 'rentify-wa-secret-2026'));

        try {
            $response = Http::timeout(5)
                ->withHeaders(['X-Gateway-Secret' => $secret])
                ->post("{$gatewayUrl}/logout");

            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }
}
