<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MomentModerationQueue;
use App\Services\CoinAccountingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminMomentModerationController extends Controller
{
    protected CoinAccountingService $coinService;

    public function __construct(CoinAccountingService $coinService)
    {
        $this->coinService = $coinService;
    }

    /**
     * Ambil antrean moderasi postingan Momen.
     * GET /api/admin/moderation
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user || $user->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak. Hanya administrator yang dapat mengakses antrean moderasi.',
            ], 403);
        }

        $status = $request->query('status', 'pending');
        $query = MomentModerationQueue::with(['moment.author', 'moment.reports.user']);

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $items = $query->orderBy('last_reported_at', 'desc')->paginate(20);

        return response()->json([
            'success' => true,
            'data'    => $items,
        ]);
    }

    /**
     * Tolak laporan dan pulihkan postingan Momen menjadi aktif kembali.
     * POST /api/admin/moderation/{id}/dismiss
     */
    public function dismiss(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        if (!$user || $user->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak. Hanya administrator yang dapat melakukan aksi ini.',
            ], 403);
        }

        $queue = MomentModerationQueue::with('moment')->find($id);
        if (!$queue) {
            return response()->json([
                'success' => false,
                'message' => 'Data antrean moderasi tidak ditemukan.',
            ], 404);
        }

        $queue->status = 'dismissed';
        $queue->reviewed_by = $user->id;
        $queue->resolution_notes = $request->input('resolution_notes', 'Laporan ditolak oleh admin.');
        $queue->save();

        if ($queue->moment) {
            $queue->moment->status = 'active';
            $queue->moment->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Laporan ditolak. Postingan telah diaktifkan kembali.',
            'data'    => $queue->fresh(['moment', 'reviewer']),
        ]);
    }

    /**
     * Setujui laporan pelanggaran: hapus postingan dan tarik kembali koin (clawback).
     * POST /api/admin/moderation/{id}/uphold
     */
    public function uphold(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        if (!$user || $user->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak. Hanya administrator yang dapat melakukan aksi ini.',
            ], 403);
        }

        $queue = MomentModerationQueue::with('moment')->find($id);
        if (!$queue) {
            return response()->json([
                'success' => false,
                'message' => 'Data antrean moderasi tidak ditemukan.',
            ], 404);
        }

        $queue->status = 'reviewed';
        $queue->reviewed_by = $user->id;
        $queue->resolution_notes = $request->input('resolution_notes', 'Pelanggaran disetujui. Konten melanggar pedoman komunitas.');
        $queue->save();

        $clawback = null;
        if ($queue->moment) {
            $queue->moment->status = 'deleted';
            $queue->moment->save();

            $clawback = $this->coinService->clawbackMomentCoins($queue->moment->id);
        }

        return response()->json([
            'success'  => true,
            'message'  => 'Laporan disetujui. Postingan telah dihapus dan koin telah ditarik kembali.',
            'data'     => $queue->fresh(['moment', 'reviewer']),
            'clawback' => $clawback,
        ]);
    }
}
