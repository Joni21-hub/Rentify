<?php

namespace App\Services;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

/**
 * DokuService
 *
 * Service modular untuk integrasi DOKU Checkout (Jokul) API.
 * Menangani pembuatan header otorisasi (HMAC-SHA256) sesuai spesifikasi resmi DOKU,
 * pembuatan invoice/tagihan, dan verifikasi webhook notification.
 *
 * Referensi: https://developers.doku.com/accept-payment/checkout
 */
class DokuService
{
    private string $clientId;
    private string $secretKey;
    private string $baseUrl;
    private int    $expiryMinutes;

    public function __construct()
    {
        $this->clientId      = config('doku.client_id');
        $this->secretKey     = config('doku.secret_key');
        $this->baseUrl       = rtrim(config('doku.base_url'), '/');
        $this->expiryMinutes = (int) config('doku.expiry_minutes', 30);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // A.  HEADER GENERATOR (sesuai spesifikasi DOKU Checkout)
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Buat header standar request DOKU.
     *
     * Signature = HMAC-SHA256(
     *   "Client-Id:{clientId}\n" +
     *   "Request-Id:{requestId}\n" +
     *   "Request-Timestamp:{timestamp}\n" +
     *   "Request-Target:{path}\n" +
     *   "Digest:{digest}"    ← hanya jika ada body
     * )
     *
     * @param  string $requestTarget  Path endpoint, misal "/checkout/v1/payment"
     * @param  array  $body           Request body yang akan di-JSON-encode
     * @return array  HTTP headers
     */
    public function buildHeaders(string $requestTarget, array $body = []): array
    {
        $requestId        = (string) Str::uuid();
        $requestTimestamp = Carbon::now('UTC')->format('Y-m-d\TH:i:s\Z');

        // Digest = SHA-256 dari JSON body (base64)
        $bodyJson = empty($body) ? '' : json_encode($body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $digest   = empty($body) ? '' : base64_encode(hash('sha256', $bodyJson, true));

        // Komponen string yang akan di-HMAC
        $components   = [];
        $components[] = "Client-Id:{$this->clientId}";
        $components[] = "Request-Id:{$requestId}";
        $components[] = "Request-Timestamp:{$requestTimestamp}";
        $components[] = "Request-Target:{$requestTarget}";
        if (!empty($digest)) {
            $components[] = "Digest:{$digest}";
        }
        $signatureString = implode("\n", $components);

        $signature = base64_encode(hash_hmac('sha256', $signatureString, $this->secretKey, true));

        $headers = [
            'Client-Id'         => $this->clientId,
            'Request-Id'        => $requestId,
            'Request-Timestamp' => $requestTimestamp,
            'Signature'         => "HMACSHA256={$signature}",
            'Content-Type'      => 'application/json',
            'Accept'            => 'application/json',
        ];

        if (!empty($digest)) {
            $headers['Digest'] = "SHA-256={$digest}";
        }

        return $headers;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // B.  CREATE CHECKOUT  — hasilkan payment_url dari DOKU
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Buat halaman checkout DOKU dan dapatkan payment URL.
     *
     * @param  array  $order  Data pesanan dari database
     * @return array  ['success' => bool, 'payment_url' => string, 'invoice_number' => string, 'expired_at' => Carbon, 'raw' => array]
     */
    public function createCheckout(array $order): array
    {
        $endpoint = '/checkout/v1/payment';

        $expiredAt = Carbon::now('UTC')->addMinutes($this->expiryMinutes);

        $invoiceNumber = $order['kode_booking'];
        $amount        = (int) round($order['total_biaya']);

        // Bersihkan nama barang agar ≤50 karakter (limit DOKU)
        $itemName = Str::limit('Sewa Barang Rentify - ' . $invoiceNumber, 50, '');

        $body = [
            'client' => [
                'id' => $this->clientId,
            ],
            'order' => [
                'invoice_number'  => $invoiceNumber,
                'line_items'      => [
                    [
                        'name'     => $itemName,
                        'price'    => $amount,
                        'quantity' => 1,
                    ],
                ],
                'amount'          => $amount,
                'currency'        => 'IDR',
                'callback_url'    => route('doku.notify'),
                'payment_due_date' => $expiredAt->format('Y-m-d\TH:i:s\Z'),
            ],
            'payment' => [
                'payment_due_date' => $this->expiryMinutes, // menit
            ],
            'customer' => [
                'name'  => $order['customer_name']  ?? 'Pelanggan',
                'email' => $order['customer_email'] ?? 'customer@rentify.id',
                'phone' => $this->normalizePhone($order['customer_whatsapp'] ?? '08123456789'),
            ],
        ];

        // Tambahkan return_url jika ada
        if (!empty($order['return_url'])) {
            $body['order']['callback_url_cancel'] = $order['return_url'];
        }

        $headers = $this->buildHeaders($endpoint, $body);

        Log::info('[DOKU] createCheckout request', [
            'invoice' => $invoiceNumber,
            'amount'  => $amount,
        ]);

        try {
            $response = Http::withHeaders($headers)
                ->timeout(15)
                ->post($this->baseUrl . $endpoint, $body);

            $data = $response->json();

            Log::info('[DOKU] createCheckout response', [
                'invoice'     => $invoiceNumber,
                'status_code' => $response->status(),
                'response'    => $data,
            ]);

            if ($response->successful() && !empty($data['response']['payment']['url'])) {
                return [
                    'success'        => true,
                    'payment_url'    => $data['response']['payment']['url'],
                    'invoice_number' => $invoiceNumber,
                    'expired_at'     => $expiredAt,
                    'raw'            => $data,
                ];
            }

            $errorMessage = $data['error']['message']
                ?? ($data['response']['payment']['url_description'] ?? 'Unknown DOKU error');

            Log::error('[DOKU] createCheckout gagal', [
                'invoice'  => $invoiceNumber,
                'response' => $data,
            ]);

            return [
                'success' => false,
                'message' => $errorMessage,
                'raw'     => $data,
            ];

        } catch (\Throwable $e) {
            Log::error('[DOKU] createCheckout exception: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Koneksi ke DOKU gagal: ' . $e->getMessage(),
                'raw'     => [],
            ];
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // C.  VALIDASI SIGNATURE WEBHOOK (Incoming notification dari DOKU)
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Verifikasi keaslian notifikasi webhook dari DOKU.
     *
     * DOKU mengirimkan header:
     *   Client-Id, Request-Id, Request-Timestamp, Signature, Digest
     *
     * Digest = SHA-256 dari JSON body (base64)
     * Signature harus cocok dengan yang kita hitung ulang.
     *
     * @param  string  $requestId
     * @param  string  $requestTimestamp
     * @param  string  $incomingSignature
     * @param  string  $requestTarget     Path webhook kita, misal "/api/payments/doku/notify"
     * @param  string  $bodyRaw           Raw request body (string JSON)
     * @return bool
     */
    public function verifyWebhookSignature(
        string $requestId,
        string $requestTimestamp,
        string $incomingSignature,
        string $requestTarget,
        string $bodyRaw
    ): bool {
        $digest = base64_encode(hash('sha256', $bodyRaw, true));

        $components = [
            "Client-Id:{$this->clientId}",
            "Request-Id:{$requestId}",
            "Request-Timestamp:{$requestTimestamp}",
            "Request-Target:{$requestTarget}",
            "Digest:{$digest}",
        ];
        $signatureString = implode("\n", $components);

        $expectedSignature = 'HMACSHA256=' . base64_encode(
            hash_hmac('sha256', $signatureString, $this->secretKey, true)
        );

        return hash_equals($expectedSignature, $incomingSignature);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // D.  HELPERS
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Normalisasi nomor HP ke format internasional 62xxx (tanpa +).
     */
    private function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/[^0-9]/', '', $phone);

        if (str_starts_with($digits, '0')) {
            $digits = '62' . substr($digits, 1);
        } elseif (!str_starts_with($digits, '62')) {
            $digits = '62' . $digits;
        }

        return $digits;
    }
}
