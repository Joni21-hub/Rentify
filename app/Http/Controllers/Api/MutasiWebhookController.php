<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\MutasiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MutasiWebhookController extends Controller
{
    protected MutasiService $mutasiService;

    public function __construct(MutasiService $mutasiService)
    {
        $this->mutasiService = $mutasiService;
    }

    /**
     * Endpoint Penerima Webhook Forwarder Notifikasi Android
     * Route: POST /api/webhook/mutasi
     */
    public function handle(Request $request)
    {
        // 1. Verifikasi Secret Key
        $configuredSecret = config('mutasi.webhook_secret', env('MUTASI_WEBHOOK_SECRET', 'rentify_mutasi_secret_2026'));

        $incomingSecret = $request->header('X-Webhook-Secret')
            ?: ($request->bearerToken()
                ?: ($request->input('secret')
                    ?: $request->query('secret')));

        if (!empty($configuredSecret) && $incomingSecret !== $configuredSecret) {
            Log::warning('[MutasiWebhookController] Unauthorized request: secret tidak cocok', [
                'ip'       => $request->ip(),
                'incoming' => $incomingSecret ? substr($incomingSecret, 0, 4) . '***' : null,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: Secret key webhook tidak valid.',
            ], 401);
        }

        // 2. Ambil payload fleksibel (bisa JSON, form-data, atau urlencoded)
        $payload = $request->isJson() ? $request->json()->all() : $request->all();

        Log::info('[MutasiWebhookController] Notifikasi Android Diterima', [
            'ip'      => $request->ip(),
            'payload' => $payload,
        ]);

        if (empty($payload)) {
            return response()->json([
                'success' => false,
                'message' => 'Payload kosong. Harap kirim data JSON atau formulir notifikasi.',
            ], 400);
        }

        // 3. Proses via MutasiService
        try {
            $result = $this->mutasiService->processIncoming($payload);

            return response()->json($result, 200);
        } catch (\Throwable $e) {
            Log::error('[MutasiWebhookController] Gagal memproses mutasi: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan internal server: ' . $e->getMessage(),
            ], 500);
        }
    }
}
