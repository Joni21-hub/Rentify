<?php

namespace App\Services;

use App\Models\Moment;
use App\Models\MomentCoinLedger;
use App\Models\MomentLike;
use App\Models\User;
use App\Models\UserCoinDeficit;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

class CoinAccountingService
{
    /**
     * Mendapatkan saldo koin tersedia yang siap dibelanjakan (berdasarkan sisa lot FIFO aktif).
     */
    public function getAvailableBalance(int $userId): int
    {
        return (int) MomentCoinLedger::where('user_id', $userId)
            ->where('remaining_lot_balance', '>', 0)
            ->sum('remaining_lot_balance');
    }

    /**
     * Mendapatkan total utang defisit pengguna.
     */
    public function getDeficitBalance(int $userId): int
    {
        $deficit = UserCoinDeficit::where('user_id', $userId)->first();
        return $deficit ? (int) $deficit->deficit_coins : 0;
    }

    /**
     * Mengkalkulasi potongan diskon koin maksimal pada saat checkout pesanan.
     * Aturan:
     * - Nilai: 1 Koin = Rp 100
     * - Subtotal sewa murni minimal Rp 50.000
     * - Diskon maksimal 25% dari subtotal sewa murni
     * - Aturan pembulatan ke bawah (floor) ke kelipatan Rp 100 terdekat
     * - Pengecualian biaya: jaminan/deposit, ongkir, admin fee tidak dipotong koin
     */
    public function calculateCheckoutDiscount(int $userId, int $requestedCoins, int $baseRentalSubtotal): array
    {
        $availableCoins = $this->getAvailableBalance($userId);

        if ($baseRentalSubtotal < 50000 || $requestedCoins <= 0 || $availableCoins <= 0) {
            return [
                'base_subtotal'     => $baseRentalSubtotal,
                'requested_coins'   => $requestedCoins,
                'applicable_coins'  => 0,
                'discount_rupiah'   => 0,
                'max_allowed_coins' => 0,
                'available_coins'   => $availableCoins,
            ];
        }

        // Floor rounding ke kelipatan Rp 100
        $maxRupiahDiscount = floor(($baseRentalSubtotal * 0.25) / 100) * 100;
        $maxAllowedCoins = (int) ($maxRupiahDiscount / 100);

        $applicableCoins = min($requestedCoins, $maxAllowedCoins, $availableCoins);
        $discountRupiah = $applicableCoins * 100;

        return [
            'base_subtotal'     => $baseRentalSubtotal,
            'requested_coins'   => $requestedCoins,
            'applicable_coins'  => $applicableCoins,
            'discount_rupiah'   => (int) $discountRupiah,
            'max_allowed_coins' => $maxAllowedCoins,
            'available_coins'   => $availableCoins,
        ];
    }

    /**
     * Memproses pengeluaran koin untuk pesanan sewa secara atomik dengan FIFO lot tracking.
     */
    public function spendCoins(int $userId, int $coinsAmount, int $orderId): array
    {
        if ($coinsAmount <= 0) {
            throw new InvalidArgumentException("Jumlah koin yang dibelanjakan harus lebih dari 0.");
        }

        return DB::transaction(function () use ($userId, $coinsAmount, $orderId) {
            // Lock semua lot koin yang masih memiliki saldo positif menggunakan FIFO (created_at ASC)
            $lots = MomentCoinLedger::where('user_id', $userId)
                ->where('remaining_lot_balance', '>', 0)
                ->orderBy('created_at', 'asc')
                ->lockForUpdate()
                ->get();

            $totalAvailable = $lots->sum('remaining_lot_balance');
            if ($totalAvailable < $coinsAmount) {
                throw new RuntimeException("Saldo koin tidak mencukupi untuk transaksi checkout. Tersedia: {$totalAvailable}, Diminta: {$coinsAmount}");
            }

            $remainingToSpend = $coinsAmount;
            foreach ($lots as $lot) {
                $deduct = min($remainingToSpend, $lot->remaining_lot_balance);
                $lot->remaining_lot_balance -= $deduct;
                $lot->save();

                $remainingToSpend -= $deduct;
                if ($remainingToSpend <= 0) {
                    break;
                }
            }

            // Catat transaksi pengeluaran pada buku besar
            $ledger = MomentCoinLedger::create([
                'user_id'               => $userId,
                'order_id'              => $orderId,
                'interaction_type'      => 'checkout_spend',
                'coins_amount'          => -$coinsAmount,
                'remaining_lot_balance' => 0,
                'minted_coin'           => false,
            ]);

            $newBalance = $totalAvailable - $coinsAmount;

            return [
                'spent'             => $coinsAmount,
                'remaining_balance' => $newBalance,
                'ledger_id'         => $ledger->id,
            ];
        });
    }

    /**
     * Mengalokasikan perolehan koin baru atau koin refund untuk melunasi utang buku besar defisit terlebih dahulu.
     */
    public function settleDeficitOrCredit(
        int $userId,
        int $coinsAmount,
        string $sourceType,
        ?int $referenceId = null,
        ?int $interactorId = null
    ): array {
        if ($coinsAmount <= 0) {
            return [
                'deficit_paid'        => 0,
                'credited_to_wallet'  => 0,
                'remaining_deficit'   => $this->getDeficitBalance($userId),
                'available_balance'   => $this->getAvailableBalance($userId),
            ];
        }

        return DB::transaction(function () use ($userId, $coinsAmount, $sourceType, $referenceId, $interactorId) {
            // Lock catatan defisit pengguna
            $deficit = UserCoinDeficit::firstOrCreate(
                ['user_id' => $userId],
                ['deficit_coins' => 0]
            );
            $deficit = UserCoinDeficit::where('id', $deficit->id)->lockForUpdate()->first();

            $activeDeficit = (int) $deficit->deficit_coins;
            $deficitPaid = 0;
            $remainingCoins = $coinsAmount;

            if ($activeDeficit > 0) {
                $deficitPaid = min($activeDeficit, $coinsAmount);
                $deficit->deficit_coins -= $deficitPaid;
                $deficit->last_settled_at = now();
                $deficit->save();

                $remainingCoins = $coinsAmount - $deficitPaid;
            }

            // Catat lot baru ke moment_coin_ledgers
            $momentId = in_array($sourceType, ['like_mint', 'relike_restore']) ? $referenceId : null;
            $orderId = ($sourceType === 'refund') ? $referenceId : null;

            $ledger = MomentCoinLedger::create([
                'user_id'               => $userId,
                'interactor_id'         => $interactorId,
                'moment_id'             => $momentId,
                'order_id'              => $orderId,
                'interaction_type'      => $sourceType,
                'coins_amount'          => $coinsAmount,
                'remaining_lot_balance' => $remainingCoins,
                'minted_coin'           => in_array($sourceType, ['like_mint', 'relike_restore']),
            ]);

            return [
                'deficit_paid'        => $deficitPaid,
                'credited_to_wallet'  => $remainingCoins,
                'remaining_deficit'   => (int) $deficit->fresh()->deficit_coins,
                'available_balance'   => $this->getAvailableBalance($userId),
                'ledger_id'           => $ledger->id,
            ];
        });
    }

    /**
     * Memproses pemberian Like dengan aturan anti-fraud, daily cap 200, post cap 500, dan re-like anti-griefing.
     */
    public function recordLike(int $userId, int $momentId): array
    {
        return DB::transaction(function () use ($userId, $momentId) {
            $moment = Moment::where('id', $momentId)->lockForUpdate()->firstOrFail();
            $interactor = User::findOrFail($userId);
            $creatorId = $moment->user_id;

            // 1. Cek Anti-Fraud: Pencegahan self-like
            if ($creatorId === $userId) {
                return [
                    'success'      => false,
                    'message'      => 'Tidak dapat memberikan like pada postingan sendiri.',
                    'minted'       => false,
                    'likes_count'  => $moment->likes_count,
                ];
            }

            // Cek apakah interaksi like saat ini sudah aktif
            $existingActiveLike = MomentLike::where('moment_id', $momentId)
                ->where('user_id', $userId)
                ->first();

            if ($existingActiveLike) {
                return [
                    'success'      => true,
                    'message'      => 'Postingan sudah disukai.',
                    'minted'       => false,
                    'likes_count'  => $moment->likes_count,
                ];
            }

            // Cek riwayat historis interaksi pada pair (interactor, moment)
            $previousMintedLedger = MomentCoinLedger::where('moment_id', $momentId)
                ->where('interactor_id', $userId)
                ->where('minted_coin', true)
                ->first();

            $isReLike = ($previousMintedLedger !== null);

            // Validasi kelayakan pencetakan koin (minting)
            $canMint = false;
            if ($isReLike) {
                // Re-like: pulihkan koin tanpa menambah lifetime_minted_count
                $canMint = true;
            } else {
                // Like baru: cek umur akun >= 3 hari
                $accountAgeOk = $interactor->created_at <= now()->subDays(3);

                // Cek batas seumur hidup postingan (maksimal 500 koin)
                $postCapOk = $moment->lifetime_minted_count < 500;

                // Cek batas harian kreator (maksimal 200 koin hari ini)
                $todayMintedCount = MomentCoinLedger::where('user_id', $creatorId)
                    ->where('interaction_type', 'like_mint')
                    ->whereDate('created_at', now()->toDateString())
                    ->count();
                $dailyCapOk = $todayMintedCount < 200;

                $canMint = ($accountAgeOk && $postCapOk && $dailyCapOk);
            }

            // Catat like ke tabel moment_likes
            MomentLike::create([
                'moment_id'  => $momentId,
                'user_id'    => $userId,
                'created_at' => now(),
            ]);

            $moment->likes_count += 1;
            if ($canMint && !$isReLike) {
                $moment->lifetime_minted_count += 1;
            }
            $moment->save();

            $settlement = null;
            if ($canMint) {
                $sourceType = $isReLike ? 'relike_restore' : 'like_mint';
                $settlement = $this->settleDeficitOrCredit($creatorId, 1, $sourceType, $momentId, $userId);
            } else {
                // Catat di ledger sebagai non-minted like untuk audit trail
                MomentCoinLedger::create([
                    'user_id'               => $creatorId,
                    'interactor_id'         => $userId,
                    'moment_id'             => $momentId,
                    'interaction_type'      => 'like_mint',
                    'coins_amount'          => 0,
                    'remaining_lot_balance' => 0,
                    'minted_coin'           => false,
                ]);
            }

            return [
                'success'      => true,
                'minted'       => $canMint,
                'is_relike'    => $isReLike,
                'likes_count'  => $moment->likes_count,
                'settlement'   => $settlement,
            ];
        });
    }

    /**
     * Memproses pembatalan Like (unlike): memotong saldo jika minted, atau mencatat defisit jika saldo tidak cukup.
     */
    public function recordUnlike(int $userId, int $momentId, ?int $creatorId = null): array
    {
        return DB::transaction(function () use ($userId, $momentId, $creatorId) {
            $moment = Moment::where('id', $momentId)->lockForUpdate()->firstOrFail();
            $targetCreatorId = $creatorId ?? $moment->user_id;

            // Hapus like aktif jika ada
            $like = MomentLike::where('moment_id', $momentId)
                ->where('user_id', $userId)
                ->first();

            if ($like) {
                $like->delete();
                $moment->likes_count = max(0, $moment->likes_count - 1);
                $moment->save();
            }

            // Periksa apakah interaksi ini pernah menghasilkan minted coin
            $mintedInteraction = MomentCoinLedger::where('moment_id', $momentId)
                ->where('interactor_id', $userId)
                ->where('minted_coin', true)
                ->first();

            if (!$mintedInteraction) {
                return [
                    'unliked'        => true,
                    'deducted'       => false,
                    'deficit_added'  => 0,
                    'likes_count'    => $moment->likes_count,
                    'balance'        => $this->getAvailableBalance($targetCreatorId),
                ];
            }

            // Lock defisit dan lot kreator
            $deficit = UserCoinDeficit::firstOrCreate(
                ['user_id' => $targetCreatorId],
                ['deficit_coins' => 0]
            );
            $deficit = UserCoinDeficit::where('id', $deficit->id)->lockForUpdate()->first();

            $lots = MomentCoinLedger::where('user_id', $targetCreatorId)
                ->where('remaining_lot_balance', '>', 0)
                ->orderBy('created_at', 'asc')
                ->lockForUpdate()
                ->get();

            $totalAvailable = $lots->sum('remaining_lot_balance');

            if ($totalAvailable >= 1) {
                // Potong 1 koin dari lot yang relevan (utamakan lot moment ini, fallback ke lot terakhir)
                $targetLot = $lots->where('moment_id', $momentId)->first() ?? $lots->last();
                $targetLot->remaining_lot_balance -= 1;
                $targetLot->save();

                MomentCoinLedger::create([
                    'user_id'               => $targetCreatorId,
                    'interactor_id'         => $userId,
                    'moment_id'             => $momentId,
                    'interaction_type'      => 'unlike_deduct',
                    'coins_amount'          => -1,
                    'remaining_lot_balance' => 0,
                    'minted_coin'           => false,
                ]);

                return [
                    'unliked'        => true,
                    'deducted'       => true,
                    'deficit_added'  => 0,
                    'likes_count'    => $moment->likes_count,
                    'balance'        => $this->getAvailableBalance($targetCreatorId),
                ];
            } else {
                // Saldo 0: Catat utang defisit 1 koin (Rp 100), saldo tetap 0
                $deficit->deficit_coins += 1;
                $deficit->save();

                MomentCoinLedger::create([
                    'user_id'               => $targetCreatorId,
                    'interactor_id'         => $userId,
                    'moment_id'             => $momentId,
                    'interaction_type'      => 'unlike_deduct',
                    'coins_amount'          => 0,
                    'remaining_lot_balance' => 0,
                    'minted_coin'           => false,
                ]);

                return [
                    'unliked'        => true,
                    'deducted'       => false,
                    'deficit_added'  => 1,
                    'likes_count'    => $moment->likes_count,
                    'balance'        => 0,
                    'total_deficit'  => (int) $deficit->deficit_coins,
                ];
            }
        });
    }

    /**
     * Memproses pengembalian koin 100% pada pembatalan atau penolakan pesanan sewa dengan prioritas pelunasan defisit.
     */
    public function refundOrderCoins(int $userId, int $coinsAmount, int $orderId): array
    {
        return $this->settleDeficitOrCredit($userId, $coinsAmount, 'refund', $orderId);
    }

    /**
     * Melakukan rekonsiliasi penarikan koin saat postingan Momen dihapus (clawback koin sisa / alokasi defisit).
     * Memastikan user tidak pernah memiliki saldo positif dan defisit aktif secara bersamaan.
     */
    public function clawbackMomentCoins(int $momentId): array
    {
        return DB::transaction(function () use ($momentId) {
            $moment = Moment::where('id', $momentId)->lockForUpdate()->firstOrFail();
            $creatorId = $moment->user_id;

            // Lock defisit kreator
            $deficit = UserCoinDeficit::firstOrCreate(
                ['user_id' => $creatorId],
                ['deficit_coins' => 0]
            );
            $deficit = UserCoinDeficit::where('id', $deficit->id)->lockForUpdate()->first();

            // Ambil semua lot koin yang dihasilkan oleh postingan ini
            $momentLots = MomentCoinLedger::where('moment_id', $momentId)
                ->where('user_id', $creatorId)
                ->where('minted_coin', true)
                ->lockForUpdate()
                ->get();

            $totalMintedOnPost = (int) $momentLots->sum('coins_amount');
            $unspentOnPost = (int) $momentLots->where('remaining_lot_balance', '>', 0)->sum('remaining_lot_balance');
            $spentFromPost = $totalMintedOnPost - $unspentOnPost;

            // 1. Tarik kembali koin yang belum terbelanjakan pada postingan ini
            foreach ($momentLots as $lot) {
                if ($lot->remaining_lot_balance > 0) {
                    $lot->remaining_lot_balance = 0;
                    $lot->save();
                }
            }

            // 2. Untuk koin yang sudah terlanjur dibelanjakan:
            // Tarik dari saldo aktif di postingan/lot lain terlebih dahulu
            $deficitCreated = 0;
            $recoveredFromOtherLots = 0;

            if ($spentFromPost > 0) {
                $otherLots = MomentCoinLedger::where('user_id', $creatorId)
                    ->where('moment_id', '!=', $momentId)
                    ->where('remaining_lot_balance', '>', 0)
                    ->orderBy('created_at', 'asc')
                    ->lockForUpdate()
                    ->get();

                $otherAvailable = (int) $otherLots->sum('remaining_lot_balance');

                if ($otherAvailable >= $spentFromPost) {
                    // Cukup ditutupi oleh lot lain
                    $rem = $spentFromPost;
                    foreach ($otherLots as $otherLot) {
                        $dec = min($rem, $otherLot->remaining_lot_balance);
                        $otherLot->remaining_lot_balance -= $dec;
                        $otherLot->save();

                        $rem -= $dec;
                        if ($rem <= 0) {
                            break;
                        }
                    }
                    $recoveredFromOtherLots = $spentFromPost;
                } else {
                    // Kurangi seluruh saldo dari lot lain hingga 0
                    foreach ($otherLots as $otherLot) {
                        $otherLot->remaining_lot_balance = 0;
                        $otherLot->save();
                    }
                    $recoveredFromOtherLots = $otherAvailable;

                    // Sisanya baru dicatat sebagai utang defisit
                    $deficitCreated = $spentFromPost - $otherAvailable;
                    $deficit->deficit_coins += $deficitCreated;
                    $deficit->save();
                }
            }

            // Catat transaksi penarikan koin pada ledger
            MomentCoinLedger::create([
                'user_id'               => $creatorId,
                'moment_id'             => $momentId,
                'interaction_type'      => 'post_deletion_clawback',
                'coins_amount'          => -$totalMintedOnPost,
                'remaining_lot_balance' => 0,
                'minted_coin'           => false,
            ]);

            return [
                'moment_id'                 => $momentId,
                'total_minted'              => $totalMintedOnPost,
                'unspent_revoked'           => $unspentOnPost,
                'spent_recovered_from_lots' => $recoveredFromOtherLots,
                'deficit_created'           => $deficitCreated,
                'final_available_balance'   => $this->getAvailableBalance($creatorId),
                'final_deficit'             => (int) $deficit->deficit_coins,
            ];
        });
    }
}
