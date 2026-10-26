<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Moment;
use App\Models\MomentComment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MomentCommentController extends Controller
{
    /**
     * Ambil daftar komentar untuk momen tertentu.
     * GET /api/moments/{id}/comments
     */
    public function index(Request $request, int $id): JsonResponse
    {
        $moment = Moment::where('status', '!=', 'deleted')->find($id);
        if (!$moment) {
            return response()->json([
                'success' => false,
                'message' => 'Postingan Momen tidak ditemukan.',
            ], 404);
        }

        $currentUser = $request->user();

        $comments = MomentComment::with('user')
            ->where('moment_id', $id)
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->get()
            ->map(function (MomentComment $c) use ($currentUser, $moment) {
                return [
                    'id'               => $c->id,
                    'moment_id'        => $c->moment_id,
                    'user_id'          => $c->user_id,
                    'user_name'        => $c->user->name ?? 'Pengguna Rentify',
                    'user_username'    => $c->user->username ?? ($c->user ? 'user' . $c->user->id : 'pengguna'),
                    'user_avatar'      => $c->user->foto_profil ?? null,
                    'comment'          => $c->comment,
                    'created_at'       => $c->created_at ? $c->created_at->toISOString() : null,
                    'created_at_human' => $c->created_at ? $c->created_at->diffForHumans() : '',
                    'can_delete'       => $currentUser && (
                        $currentUser->id === $c->user_id ||
                        $currentUser->id === $moment->user_id ||
                        $currentUser->role === 'admin'
                    ),
                ];
            });

        return response()->json([
            'success'        => true,
            'data'           => $comments,
            'comments_count' => (int) $moment->comments_count,
        ]);
    }

    /**
     * Tambahkan komentar baru pada postingan momen.
     * POST /api/moments/{id}/comments
     */
    public function store(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Silakan masuk terlebih dahulu untuk berkomentar.',
            ], 401);
        }

        $moment = Moment::where('status', '!=', 'deleted')->find($id);
        if (!$moment) {
            return response()->json([
                'success' => false,
                'message' => 'Postingan Momen tidak ditemukan.',
            ], 404);
        }

        $request->validate([
            'comment' => 'required|string|min:1|max:500',
        ], [
            'comment.required' => 'Komentar tidak boleh kosong.',
            'comment.max'      => 'Komentar maksimal 500 karakter.',
        ]);

        $comment = MomentComment::create([
            'moment_id' => $moment->id,
            'user_id'   => $user->id,
            'comment'   => trim($request->input('comment')),
        ]);

        $moment->increment('comments_count');

        return response()->json([
            'success'        => true,
            'message'        => 'Komentar berhasil dikirim!',
            'data'           => [
                'id'               => $comment->id,
                'moment_id'        => $comment->moment_id,
                'user_id'          => $user->id,
                'user_name'        => $user->name,
                'user_username'    => $user->username ?? 'user' . $user->id,
                'user_avatar'      => $user->foto_profil ?? null,
                'comment'          => $comment->comment,
                'created_at'       => $comment->created_at->toISOString(),
                'created_at_human' => 'Baru saja',
                'can_delete'       => true,
            ],
            'comments_count' => (int) $moment->comments_count,
        ], 201);
    }

    /**
     * Hapus komentar pada postingan momen.
     * DELETE /api/moments/{id}/comments/{commentId}
     */
    public function destroy(Request $request, int $id, int $commentId): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Silakan masuk terlebih dahulu.',
            ], 401);
        }

        $moment = Moment::where('status', '!=', 'deleted')->find($id);
        if (!$moment) {
            return response()->json([
                'success' => false,
                'message' => 'Postingan Momen tidak ditemukan.',
            ], 404);
        }

        $comment = MomentComment::where('moment_id', $id)->find($commentId);
        if (!$comment) {
            return response()->json([
                'success' => false,
                'message' => 'Komentar tidak ditemukan.',
            ], 404);
        }

        // Hanya pembuat komentar, pembuat momen, atau admin yang dapat menghapus
        if ($user->id !== $comment->user_id && $user->id !== $moment->user_id && $user->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki hak akses untuk menghapus komentar ini.',
            ], 403);
        }

        $comment->delete();

        $moment->comments_count = max(0, $moment->comments_count - 1);
        $moment->save();

        return response()->json([
            'success'        => true,
            'message'        => 'Komentar berhasil dihapus.',
            'comments_count' => (int) $moment->comments_count,
        ]);
    }
}
