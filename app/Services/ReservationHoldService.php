<?php

namespace App\Services;

use App\Models\Pembayaran;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReservationHoldService
{
    public const DEFAULT_HOLD_TTL_MINUTES = 15;

    /**
     * Requirement 4.6:
     * Menetapkan logical reservation hold pada pesanan dengan status 'Menunggu Pembayaran'
     * dan TTL kedaluwarsa 15 menit (payment_expired_at = now()->addMinutes(15)).
     *
     * @param int $orderId
     * @param int $ttlMinutes
     * @return void
     */
    public function applyHold(int $orderId, int $ttlMinutes = self::DEFAULT_HOLD_TTL_MINUTES): void
    {
        $now = Carbon::now();
        DB::table('orders')->where('id', $orderId)->update([
            'status'             => 'Menunggu Pembayaran',
            'payment_expired_at' => $now->copy()->addMinutes($ttlMinutes),
            'updated_at'         => $now,
        ]);
    }

    /**
     * Requirement 4.7:
     * Transisi status reservation hold menjadi booking permanen ('Disetujui')
     * setelah konfirmasi pembayaran terverifikasi lunas.
     *
     * @param int|array<int> $orderIds
     * @return int Jumlah pesanan yang berhasil diperbarui
     */
    public function transitionHoldToPermanent(int|array $orderIds): int
    {
        $ids = is_array($orderIds) ? $orderIds : [$orderIds];
        if (empty($ids)) {
            return 0;
        }

        $now = Carbon::now();

        $affected = DB::table('orders')
            ->whereIn('id', $ids)
            ->whereIn('status', ['Menunggu Pembayaran', 'PENDING_PAYMENT', 'Menunggu Konfirmasi', 'pending'])
            ->update([
                'status'     => 'Disetujui',
                'paid_at'    => $now,
                'updated_at' => $now,
            ]);

        foreach ($ids as $id) {
            $order = DB::table('orders')->where('id', $id)->first();
            if ($order) {
                Pembayaran::updateOrCreate(
                    ['order_id' => $id],
                    [
                        'payment_group_id' => $order->payment_group_id ?? null,
                        'metode'           => $order->payment_method ?? 'QRIS',
                        'jumlah'           => $order->total_biaya ?? $order->total_price ?? 0,
                        'status'           => 'Lunas',
                        'paid_at'          => $now,
                    ]
                );
            }
        }

        return $affected;
    }

    /**
     * Static helper for transitioning holds.
     */
    public static function confirmPayment(int|array $orderIds): int
    {
        return (new static())->transitionHoldToPermanent($orderIds);
    }
}
