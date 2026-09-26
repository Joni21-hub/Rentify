<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class WhatsAppService
{
    /**
     * Mengirim pesan WhatsApp melalui Fonnte API
     */
    public static function send($nomorTujuan, $pesan)
    {
        // Ganti token ini dengan token dari akun Fonnte Anda
        $token = env('FONNTE_TOKEN', 'TOKEN_FONNTE_ANDA_DISINI'); 

        try {
            $response = Http::withHeaders([
                'Authorization' => $token,
            ])->post('https://api.fonnte.com/send', [
                'target' => $nomorTujuan,
                'message' => $pesan,
                'countryCode' => '62', // Default Indonesia
            ]);

            return $response->json();
        } catch (\Exception $e) {
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }
}
