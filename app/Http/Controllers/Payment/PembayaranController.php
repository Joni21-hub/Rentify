<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Models\Pembayaran;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * PembayaranController — Verifikasi Pembayaran QRIS Dinamis & E-Wallet
 *
 * Mengelola status polling pembayaran QRIS, konfirmasi pelanggan,
 * serta pengujian simulasi sandbox yang aman.
 */
class PembayaranController extends Controller
{
    // ─────────────────────────────────────────────────────────────────────────
    // 1.  CHECK STATUS — AJAX Polling dari frontend (QRIS)
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Endpoint polling AJAX untuk cek status pembayaran dari DB.
     * Status diupdate otomatis via Webhook Mutasi Rekening atau verifikasi Admin.
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

        // Cek jika pesanan sudah terkonfirmasi lunas
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
            'message'       => 'Menunggu pembayaran QRIS...',
            'remaining_sec' => $sisaDetik,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 2.  CONFIRM PAYMENT — Pelanggan Konfirmasi Bayar Mandiri
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Konfirmasi pembayaran oleh pelanggan setelah melakukan scan QRIS / transfer
     * Route: POST /customer/pembayaran/confirm/{id}
     */
    public function confirmPayment(Request $request, $id)
    {
        $orderIds = $this->parseOrderIds($id);
        if (empty($orderIds)) {
            return response()->json(['success' => false, 'message' => 'Pesanan tidak ditemukan.'], 404);
        }

        $orders = DB::table('orders')->whereIn('id', $orderIds)->get();
        if ($orders->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'Pesanan tidak ditemukan.'], 404);
        }

        // Cegah IDOR: Pastikan pemesan adalah pengguna yang sedang login atau Admin
        $userId = auth()->id();
        $isAdmin = auth()->user()?->role === 'admin';
        if (!$orders->every(fn($o) => $o->user_id == $userId) && !$isAdmin) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki hak akses untuk mengonfirmasi pesanan ini.'
            ], 403);
        }

        DB::table('orders')->whereIn('id', $orderIds)->update([
            'status'         => 'Menunggu Konfirmasi',
            'payment_method' => 'QRIS',
            'updated_at'     => now(),
        ]);

        foreach ($orders as $ord) {
            Pembayaran::updateOrCreate(
                ['order_id' => $ord->id],
                [
                    'metode'  => 'QRIS',
                    'jumlah'  => $ord->total_biaya ?? $ord->total_price,
                    'status'  => 'Menunggu Konfirmasi',
                ]
            );
        }

        return response()->json([
            'success'      => true,
            'paid'         => true,
            'message'      => '🎉 Pembayaran Anda berhasil dikonfirmasi! Pesanan sedang disiapkan.',
            'redirect_url' => route('customer.struk', ['id' => $id]),
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 3.  SIMULATE PAYMENT — Testing / Sandbox
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Endpoint simulasi pembayaran lunas (Khusus Mode Sandbox / Testing)
     * Route: POST /customer/pembayaran/simulate/{id}
     */
    public function simulatePayment(Request $request, $id)
    {
        // 1. Matikan mutlak di lingkungan production demi keamanan finansial
        if (app()->isProduction()) {
            return response()->json([
                'success' => false,
                'message' => 'Fitur simulasi pembayaran dinonaktifkan di lingkungan produksi.'
            ], 403);
        }

        // 2. Wajib mode debug aktif
        if (!config('app.debug')) {
            return response()->json([
                'success' => false,
                'message' => 'Simulasi hanya dapat digunakan pada mode pengujian/debug.'
            ], 403);
        }

        // 3. Hak akses: Wajib menyertakan token otorisasi simulasi rahasia
        $validToken = config('services.payment_simulation.token', 'RENTIFY_SIM_SECURE_TOKEN_2026');
        $providedToken = $request->header('X-Simulation-Token') ?? $request->input('simulation_token');

        if ($providedToken !== $validToken) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak: Simulasi pembayaran memerlukan token otorisasi pengujian yang valid.'
            ], 403);
        }

        $orderIds = $this->parseOrderIds($id);
        if (empty($orderIds)) {
            return response()->json(['success' => false, 'message' => 'Pesanan tidak ditemukan.'], 404);
        }

        $orders = DB::table('orders')->whereIn('id', $orderIds)->get();
        if ($orders->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'Pesanan tidak ditemukan.'], 404);
        }

        // 4. Cegah IDOR: Pastikan pemesan adalah pemilik pesanan
        $userId = auth()->id();
        if (!$orders->every(fn($o) => $o->user_id == $userId)) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki hak akses untuk pesanan ini.'
            ], 403);
        }

        DB::table('orders')->whereIn('id', $orderIds)->update([
            'status'         => 'Menunggu Konfirmasi',
            'payment_method' => 'QRIS',
            'paid_at'        => now(),
            'updated_at'     => now(),
        ]);

        foreach ($orders as $ord) {
            Pembayaran::updateOrCreate(
                ['order_id' => $ord->id],
                [
                    'metode'  => 'QRIS',
                    'jumlah'  => $ord->total_biaya ?? $ord->total_price,
                    'status'  => 'Lunas',
                    'paid_at' => now(),
                ]
            );

            // Notifikasi WhatsApp otomatis ke Customer & Vendor
            \App\Services\OrderNotificationService::notifyPaymentConfirmed(
                $ord,
                'QRIS (Simulasi Testing)',
                (float) ($ord->total_biaya ?? $ord->total_price ?? 0)
            );
        }

        return response()->json([
            'success'      => true,
            'paid'         => true,
            'message'      => '🎉 Simulasi berhasil! Pembayaran otomatis terkonfirmasi lunas.',
            'redirect_url' => route('customer.struk', ['id' => $id]),
        ]);
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
}
