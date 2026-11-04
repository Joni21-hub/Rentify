<?php

namespace App\Console\Commands;

use App\Models\CustomerVoucher;
use App\Models\Voucher;
use App\Services\CoinAccountingService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class ReleaseExpiredBundleReservationsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rentify:release-expired-bundle-reservations';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Membatalkan pesanan bundle dan barang yang belum dibayar melewati batas TTL (payment_expired_at < now()) dan melepaskan reservasi stok.';

    /**
     * Execute the console command.
     */
    public function handle(CoinAccountingService $coinService): int
    {
        $now = Carbon::now();

        // Cari seluruh pesanan yang masih memegang reservasi stok namun telah kedaluwarsa
        $expiredOrders = DB::table('orders')
            ->whereIn('status', ['Menunggu Pembayaran', 'PENDING_PAYMENT', 'pending'])
            ->whereNotNull('payment_expired_at')
            ->where('payment_expired_at', '<', $now)
            ->get();

        if ($expiredOrders->isEmpty()) {
            $this->info('Tidak ada reservasi sewa yang kedaluwarsa.');
            return Command::SUCCESS;
        }

        $releasedCount = 0;

        foreach ($expiredOrders as $order) {
            DB::beginTransaction();
            try {
                // 1. Transisi status pesanan menjadi 'Dibatalkan' agar stok dan slot kalender langsung bebas
                DB::table('orders')
                    ->where('id', $order->id)
                    ->update([
                        'status'     => 'Dibatalkan',
                        'updated_at' => $now,
                    ]);

                // 2. Kembalikan koin Rentify jika pesanan sempat memotong koin
                if (!empty($order->koin_digunakan) && $order->koin_digunakan > 0 && !empty($order->user_id)) {
                    try {
                        $coinService->refundOrderCoins((int) $order->user_id, (int) $order->koin_digunakan, (int) $order->id);
                    } catch (\Throwable $e) {
                        Log::warning("Gagal mengembalikan koin pesanan expired #{$order->id}: " . $e->getMessage());
                    }
                }

                // 3. Pulihkan kuota voucher toko / platform jika digunakan
                if (!empty($order->voucher_id)) {
                    Voucher::where('id', $order->voucher_id)
                        ->where('kuota_terpakai', '>', 0)
                        ->decrement('kuota_terpakai');

                    if (!empty($order->user_id) && Schema::hasTable('customer_vouchers')) {
                        CustomerVoucher::where('user_id', $order->user_id)
                            ->where('voucher_id', $order->voucher_id)
                            ->where('status', 'terpakai')
                            ->update([
                                'status'  => 'aktif',
                                'used_at' => null,
                            ]);
                    }
                }

                DB::commit();
                $releasedCount++;
                $this->line("Pesanan #INV-{$order->id} ({$order->kode_booking}) dibatalkan. Reservasi stok dilepaskan.");
            } catch (\Throwable $e) {
                DB::rollBack();
                Log::error("Gagal melepaskan reservasi pesanan expired #{$order->id}: " . $e->getMessage());
                $this->error("Gagal membatalkan pesanan #{$order->id}: " . $e->getMessage());
            }
        }

        $this->info("Berhasil melepaskan {$releasedCount} reservasi sewa yang kedaluwarsa.");
        return Command::SUCCESS;
    }
}
