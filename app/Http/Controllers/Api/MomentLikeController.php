<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Moment;
use App\Services\CoinAccountingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MomentLikeController extends Controller
{
    protected CoinAccountingService $coinService;

    public function __construct(CoinAccountingService $coinService)
    {
        $this->coinService = $coinService;
    }

    /**
     * Berikan Like pada postingan Momen
     * POST /api/moments/{id}/like
     */
    public function store(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Silakan masuk terlebih dahulu untuk menyukai postingan.',
            ], 401);
        }

        $moment = Moment::find($id);
        if (!$moment) {
            return response()->json([
                'success' => false,
                'message' => 'Postingan Momen tidak ditemukan.',
            ], 404);
        }

        $result = $this->coinService->recordLike($user->id, $id);

        if (!$result['success'] && isset($result['message']) && str_contains($result['message'], 'sendiri')) {
            return response()->json([
                'success'     => false,
                'message'     => $result['message'],
                'liked'       => false,
                'likes_count' => $result['likes_count'],
                'minted'      => false,
            ], 422);
        }

        return response()->json([
            'success'     => true,
            'liked'       => true,
            'likes_count' => $result['likes_count'],
            'minted'      => $result['minted'],
            'is_relike'   => $result['is_relike'] ?? false,
            'message'     => $result['minted']
                ? 'Berhasil menyukai postingan! 1 Koin Rentify berhasil dicetak.'
                : 'Berhasil menyukai postingan.',
        ]);
    }

    /**
     * Batalkan Like pada postingan Momen (Unlike)
     * DELETE /api/moments/{id}/like
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Silakan masuk terlebih dahulu untuk membatalkan suka.',
            ], 401);
        }

        $moment = Moment::find($id);
        if (!$moment) {
            return response()->json([
                'success' => false,
                'message' => 'Postingan Momen tidak ditemukan.',
            ], 404);
        }

        $result = $this->coinService->recordUnlike($user->id, $id);

        return response()->json([
            'success'       => true,
            'liked'         => false,
            'likes_count'   => $result['likes_count'],
            'deducted'      => $result['deducted'],
            'deficit_added' => $result['deficit_added'] ?? 0,
            'message'       => 'Berhasil membatalkan suka.',
        ]);
    }
}
