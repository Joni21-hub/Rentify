<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Models\Pembayaran;
use App\Services\DokuService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * PembayaranController — DOKU Checkout Integration
 *
 * Menggantikan integrasi Midtrans sepenuhnya dengan DOKU Checkout.
 * Alur:
 *   1. pay()         → Generate DOKU payment URL, redirect customer
 *   2. checkStatus() → AJAX polling status pesanan (dari DB, bukan API DOKU)
 *   3. notify()      → Webhook POST dari DOKU, update status otomatis
 *   4. return()      → Halaman return setelah customer selesai di DOKU
 */
class PembayaranController extends Controller
{
    private DokuService $doku;

    public function __construct(DokuService $doku)
    {
        $this->doku = $doku;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 1.  PAY — Redirect ke DOKU Checkout
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Membuat tagihan DOKU dan meredirect customer ke halaman pembayaran DOKU.
     * Dipanggil dari halaman qris/pembayaran saat customer menekan "Bayar Sekarang".
     *
     * Route: GET /customer/pembayaran/{id}/pay
     */
    public function pay(Request $request, $id)
    {
        // Ambil order tunggal atau gabungan (format: INV-123_INV-456)
        $orderIds = $this->parseOrderIds($id);

        if (empty($orderIds)) {
            return redirect()->route('customer.pesanan')
                ->with('error', 'Format ID pesanan tidak valid.');
        }

        $orders = DB::table('orders')->whereIn('id', $orderIds)->get();

        if ($orders->isEmpty()) {
            return redirect()->route('customer.pesanan')
                ->with('error', 'Pesanan tidak ditemukan.');
        }

        // Cek jika sudah dibayar
        $isPaid = $orders->every(fn($o) => in_array($o->status, [
            'PAID', 'Menunggu Konfirmasi', 'Disetujui', 'Sedang Disewa', 'Selesai'
        ]));

        if ($isPaid) {
            return redirect()->route('customer.struk', ['id' => $id]);
        }

        $firstOrder = $orders->first();
        $total      = $orders->sum('total_biaya') ?: $orders->sum('total_price');

        // Gunakan payment_url yang sudah ada jika belum expired
        $existingUrl     = $firstOrder->doku_payment_url ?? null;
        $paymentExpiredAt = $firstOrder->payment_expired_at
            ? Carbon::parse($firstOrder->payment_expired_at)
            : null;

        if ($existingUrl && $paymentExpiredAt && $paymentExpiredAt->isFuture()) {
            return redirect()->away($existingUrl);
        }

        // Generate DOKU Checkout baru
        $kodeBooking = count($orders) === 1
            ? $firstOrder->kode_booking
            : ('RNT-COMBO-' . implode('-', $orderIds));

        // Siapkan user email
        $user       = auth()->user();
        $userEmail  = $user ? ($user->email ?? 'customer@rentify.id') : 'customer@rentify.id';

        $orderData = [
            'kode_booking'       => $kodeBooking,
            'total_biaya'        => $total,
            'customer_name'      => $firstOrder->customer_name ?? ($user->name ?? 'Pelanggan'),
            'customer_email'     => $userEmail,
            'customer_whatsapp'  => $firstOrder->customer_whatsapp ?? ($user->no_hp ?? '08123456789'),
            'return_url'         => route('customer.pembayaran.return', ['id' => $id]),
        ];

        $result = $this->doku->createCheckout($orderData);

        if (!$result['success']) {
            Log::error('[PembayaranController] DOKU checkout gagal', $result);
            return redirect()->back()
                ->with('error', 'Gagal menginisiasi pembayaran: ' . ($result['message'] ?? 'Error tidak diketahui.'));
        }

        $paymentUrl = $result['payment_url'];
        $expiredAt  = $result['expired_at'];

        // Simpan payment_url dan expiry ke semua order terkait
        DB::table('orders')->whereIn('id', $orderIds)->update([
            'doku_invoice_number' => $kodeBooking,
            'doku_payment_url'    => $paymentUrl,
            'payment_expired_at'  => $expiredAt,
            'status'              => 'PENDING_PAYMENT',
            'updated_at'          => now(),
        ]);

        Log::info("[PembayaranController] Redirect ke DOKU pay: {$kodeBooking}", [
            'url'        => $paymentUrl,
            'expired_at' => $expiredAt,
        ]);

        return redirect()->away($paymentUrl);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 2.  CHECK STATUS — AJAX Polling dari frontend
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Endpoint polling AJAX untuk cek status pembayaran dari DB.
     * Tidak memanggil API DOKU langsung — status diupdate via webhook.
     *
     * Route: GET /customer/pembayaran/check-status/{id}
     */
    public function checkStatus(Request $request, $id)
    {
        $orderIds = $this->parseOrderIds($id);

        if (empty($orderIds)) {
            return response()->json(['success' => false, 'message' => 'ID tidak valid'], 400);
        }

        $orders = DB::table('orders')->whereIn('id', $orderIds)->get();

        if ($orders->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'Pesanan tidak ditemukan'], 404);
        }

        $firstOrder = $orders->first();

        // Cek PAID
        $isPaid = $orders->every(fn($o) => in_array($o->status, [
            'PAID', 'Menunggu Konfirmasi', 'Disetujui', 'Sedang Disewa', 'Selesai'
        ]));

        if ($isPaid) {
            return response()->json([
                'success'      => true,
                'paid'         => true,
                'status'       => 'PAID',
                'message'      => '🎉 Pembayaran berhasil dikonfirmasi!',
                'redirect_url' => route('customer.struk', ['id' => $id]),
            ]);
        }

        // Cek EXPIRED
        $paymentExpiredAt = $firstOrder->payment_expired_at
            ? Carbon::parse($firstOrder->payment_expired_at)
            : null;

        if ($paymentExpiredAt && $paymentExpiredAt->isPast()) {
            // Tandai EXPIRED jika masih PENDING_PAYMENT
            DB::table('orders')
                ->whereIn('id', $orderIds)
                ->where('status', 'PENDING_PAYMENT')
                ->update(['status' => 'EXPIRED', 'updated_at' => now()]);

            return response()->json([
                'success' => true,
                'paid'    => false,
                'status'  => 'EXPIRED',
                'message' => 'Batas waktu pembayaran telah habis. Silakan buat pesanan baru.',
            ]);
        }

        // Masih PENDING — kembalikan sisa waktu
        $sisaDetik = $paymentExpiredAt ? max(0, (int) $paymentExpiredAt->diffInSeconds(now(), false) * -1) : 1800;

        return response()->json([
            'success'       => true,
            'paid'          => false,
            'status'        => $firstOrder->status ?? 'PENDING_PAYMENT',
            'message'       => 'Menunggu pembayaran dari customer...',
            'remaining_sec' => $sisaDetik,
            'payment_url'   => $firstOrder->doku_payment_url ?? null,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 3.  NOTIFY — Webhook POST dari DOKU (Idempotent)
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Handler webhook notifikasi pembayaran dari DOKU.
     * Idempotent: update hanya terjadi jika status belum final.
     *
     * Route: POST /api/payments/doku/notify  (tanpa middleware auth)
     */
    public function notify(Request $request)
    {
        $bodyRaw = $request->getContent();

        Log::info('[DOKU Webhook] Incoming notification', [
            'headers' => $request->headers->all(),
            'body'    => $bodyRaw,
        ]);

        // ── A. Verifikasi Signature ────────────────────────────────────────
        $incomingSignature = $request->header('Signature', '');
        $requestId         = $request->header('Request-Id', '');
        $requestTimestamp  = $request->header('Request-Timestamp', '');
        $requestTarget     = '/api/payments/doku/notify';

        $isValid = $this->doku->verifyWebhookSignature(
            $requestId,
            $requestTimestamp,
            $incomingSignature,
            $requestTarget,
            $bodyRaw
        );

        if (!$isValid) {
            Log::warning('[DOKU Webhook] Invalid signature', [
                'incoming'  => $incomingSignature,
                'client_id' => $request->header('Client-Id'),
            ]);
            return response()->json(['message' => 'Invalid signature'], 403);
        }

        // ── B. Parse payload ───────────────────────────────────────────────
        $payload = $request->json()->all();

        $trxStatus     = strtoupper($payload['transaction']['status'] ?? '');
        $invoiceNumber = $payload['order']['invoice_number'] ?? '';
        $dokuPaymentId = $payload['transaction']['id'] ?? '';
        $channelCode   = $payload['channel_code'] ?? $payload['payment']['channel_code'] ?? '';
        $amount        = (float) ($payload['order']['amount'] ?? 0);
        $paidAt        = isset($payload['transaction']['date'])
            ? Carbon::parse($payload['transaction']['date'])
            : now();

        Log::info('[DOKU Webhook] Parsed', compact('trxStatus', 'invoiceNumber', 'dokuPaymentId', 'channelCode'));

        if (empty($invoiceNumber)) {
            return response()->json(['message' => 'invoice_number kosong'], 400);
        }

        // ── C. Temukan pesanan ─────────────────────────────────────────────
        $orders = $this->findOrdersByInvoice($invoiceNumber);

        if ($orders->isEmpty()) {
            Log::warning('[DOKU Webhook] Pesanan tidak ditemukan', ['invoice' => $invoiceNumber]);
            return response()->json(['message' => 'Order not found'], 404);
        }

        $orderIds = $orders->pluck('id')->toArray();

        // ── D. Idempotency — skip jika sudah final ─────────────────────────
        $alreadyFinal = $orders->every(fn($o) => in_array($o->status, [
            'PAID', 'EXPIRED', 'FAILED', 'CANCELLED',
            'Menunggu Konfirmasi', 'Disetujui', 'Sedang Disewa', 'Selesai'
        ]));

        if ($alreadyFinal) {
            Log::info('[DOKU Webhook] Idempotency skip — status sudah final', ['invoice' => $invoiceNumber]);
            return response()->json(['message' => 'Already processed']);
        }

        // ── E. Update status berdasarkan notifikasi ────────────────────────
        if ($trxStatus === 'SUCCESS') {
            DB::table('orders')->whereIn('id', $orderIds)->update([
                'status'          => 'PAID',
                'payment_method'  => $channelCode ?: 'DOKU',
                'doku_payment_id' => $dokuPaymentId,
                'paid_at'         => $paidAt,
                'updated_at'      => now(),
            ]);

            // Catat ke tabel pembayarans (upsert idempoten)
            foreach ($orders as $order) {
                Pembayaran::updateOrCreate(
                    ['order_id' => $order->id],
                    [
                        'metode'          => 'DOKU',
                        'doku_payment_id' => $dokuPaymentId,
                        'doku_channel'    => $channelCode,
                        'jumlah'          => $order->total_biaya ?? $order->total_price,
                        'status'          => 'Lunas',
                        'paid_at'         => $paidAt,
                    ]
                );
            }

            Log::info('[DOKU Webhook] ✅ Pembayaran SUKSES', [
                'invoice'  => $invoiceNumber,
                'channel'  => $channelCode,
                'paid_at'  => $paidAt,
            ]);

        } elseif (in_array($trxStatus, ['EXPIRED', 'FAILED', 'CANCELLED', 'REVERSED'])) {
            $newStatus = in_array($trxStatus, ['CANCELLED', 'REVERSED']) ? 'CANCELLED' : $trxStatus;

            DB::table('orders')->whereIn('id', $orderIds)->update([
                'status'          => $newStatus,
                'doku_payment_id' => $dokuPaymentId ?: null,
                'updated_at'      => now(),
            ]);

            foreach ($orders as $order) {
                Pembayaran::updateOrCreate(
                    ['order_id' => $order->id],
                    [
                        'metode'          => 'DOKU',
                        'doku_payment_id' => $dokuPaymentId,
                        'doku_channel'    => $channelCode,
                        'jumlah'          => $order->total_biaya ?? $order->total_price,
                        'status'          => 'Gagal',
                    ]
                );
            }

            Log::info('[DOKU Webhook] ❌ Pembayaran GAGAL/EXPIRED', [
                'invoice' => $invoiceNumber,
                'status'  => $newStatus,
            ]);
        } else {
            Log::info('[DOKU Webhook] Status tidak dikenali: ' . $trxStatus, ['invoice' => $invoiceNumber]);
        }

        // DOKU mengharapkan respons 200 dengan header Client-Id
        return response()->json(['message' => 'Notification received'])
            ->header('Client-Id', config('doku.client_id'));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 4.  RETURN — Halaman setelah customer selesai di DOKU
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Return URL yang dibuka setelah customer menyelesaikan (atau menutup) halaman DOKU.
     * Redirect ke halaman menunggu pembayaran (qris view) dengan status polling aktif.
     *
     * Route: GET /customer/pembayaran/{id}/return
     */
    public function return(Request $request, $id)
    {
        // Cek status dari DB setelah kembali dari DOKU
        $orderIds = $this->parseOrderIds($id);
        $orders   = DB::table('orders')->whereIn('id', $orderIds)->get();

        if ($orders->isEmpty()) {
            return redirect()->route('customer.pesanan');
        }

        $isPaid = $orders->every(fn($o) => in_array($o->status, [
            'PAID', 'Menunggu Konfirmasi', 'Disetujui', 'Sedang Disewa', 'Selesai'
        ]));

        if ($isPaid) {
            return redirect()->route('customer.doku.success', ['id' => $id]);
        }

        // Belum terkonfirmasi, redirect ke halaman menunggu (polling aktif)
        return redirect()->route('customer.doku.waiting', ['id' => $id]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // HELPERS
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Parse format ID pesanan: "INV-123_INV-456" → [123, 456]
     */
    private function parseOrderIds(string $rawId): array
    {
        $ids = [];
        foreach (explode('_', $rawId) as $part) {
            $part = trim($part);
            if (str_starts_with($part, 'INV-')) {
                $numericPart = substr($part, 4);
                if (is_numeric($numericPart)) {
                    $ids[] = (int) $numericPart;
                }
            } elseif (is_numeric($part)) {
                $ids[] = (int) $part;
            }
        }
        return $ids;
    }

    /**
     * Temukan pesanan berdasarkan invoice number (kode_booking atau doku_invoice_number).
     */
    private function findOrdersByInvoice(string $invoice)
    {
        // Coba via doku_invoice_number
        $orders = DB::table('orders')->where('doku_invoice_number', $invoice)->get();
        if ($orders->isNotEmpty()) return $orders;

        // Coba via kode_booking (untuk order tunggal)
        $orders = DB::table('orders')->where('kode_booking', $invoice)->get();
        if ($orders->isNotEmpty()) return $orders;

        // Combo format: RNT-COMBO-123-456
        if (str_starts_with($invoice, 'RNT-COMBO-')) {
            $idsPart = str_replace('RNT-COMBO-', '', $invoice);
            $ids     = array_filter(explode('-', $idsPart), 'is_numeric');
            if (!empty($ids)) {
                return DB::table('orders')->whereIn('id', $ids)->get();
            }
        }

        return collect();
    }
}
