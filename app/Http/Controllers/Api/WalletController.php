<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MomentCoinLedger;
use App\Services\CoinAccountingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    protected CoinAccountingService $coinService;

    public function __construct(CoinAccountingService $coinService)
    {
        $this->coinService = $coinService;
    }

    /**
     * Mendapatkan saldo koin, utang defisit, dan riwayat mutasi buku besar koin pengguna
     * GET /api/wallet/coins
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Silakan masuk terlebih dahulu untuk mengakses dompet koin.',
            ], 401);
        }

        $balance = $this->coinService->getAvailableBalance($user->id);
        $deficit = $this->coinService->getDeficitBalance($user->id);

        $limit = min(50, max(1, (int) $request->query('limit', 15)));
        $mutations = MomentCoinLedger::where('user_id', $user->id)
            ->with([
                'moment:id,caption,thumbnail_url,media_url',
                'order:id,kode_booking,total_biaya'
            ])
            ->orderBy('created_at', 'desc')
            ->paginate($limit);

        return response()->json([
            'success' => true,
            'data'    => [
                'coin_balance'      => $balance,
                'rupiah_equivalent' => $balance * 100,
                'deficit_coins'     => $deficit,
                'deficit_rupiah'    => $deficit * 100,
                'mutations'         => $mutations,
            ],
        ]);
    }

    /**
     * Menghitung diskon koin maksimal untuk transaksi checkout
     * POST /api/wallet/coins/calculate-discount
     */
    public function calculateDiscount(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Silakan masuk terlebih dahulu.',
            ], 401);
        }

        $requestedCoins = (int) $request->input('coins', 0);
        $baseRentalSubtotal = (int) $request->input('base_subtotal', 0);

        $result = $this->coinService->calculateCheckoutDiscount(
            $user->id,
            $requestedCoins,
            $baseRentalSubtotal
        );

        return response()->json([
            'success' => true,
            'data'    => $result,
        ]);
    }
}
