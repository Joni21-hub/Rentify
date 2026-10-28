<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMomentRequest;
use App\Jobs\ProcessAiMomentTaggingJob;
use App\Models\Moment;
use App\Models\MomentAiTagSuggestion;
use App\Models\MomentBookmark;
use App\Models\MomentModerationQueue;
use App\Models\MomentReport;
use App\Models\MomentTaggedProduct;
use App\Services\CoinAccountingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MomentController extends Controller
{
    protected CoinAccountingService $coinService;

    public function __construct(CoinAccountingService $coinService)
    {
        $this->coinService = $coinService;
    }

    /**
     * Feed Momen publik berbasis cursor-based pagination dan gravity ranking score.
     * GET /api/moments
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $limit = max(1, min(50, (int) $request->query('limit', 10)));

        // Cek kondisi starvation fallback: kurang dari 10 postingan dalam 7 hari terakhir
        $sevenDaysAgo = now()->subDays(7);
        $recentCount = Moment::where('status', 'active')
            ->where('created_at', '>=', $sevenDaysAgo)
            ->count();

        $fallbackMode = ($recentCount < 10);

        $query = Moment::with(['author', 'taggedProducts.product.vendor'])
            ->where('status', 'active');

        if ($fallbackMode) {
            // Starvation fallback: ambil all-time top engaged moments
            $query->orderByRaw('(likes_count + bookmarks_count) DESC')
                  ->orderBy('id', 'desc');
        } else {
            // Standard feed: diurutkan berdasarkan skor ranking gravitasi
            $query->orderBy('ranking_score', 'desc')
                  ->orderBy('id', 'desc');
        }

        // Penanganan cursor pagination
        $cursor = $request->query('cursor');
        if (is_string($cursor)) {
            $base64Decoded = base64_decode($cursor, true);
            $decoded = $base64Decoded ? json_decode($base64Decoded, true) : null;
            if (is_array($decoded) && isset($decoded['id'])) {
                $cursorId = (int) $decoded['id'];

                if ($fallbackMode && isset($decoded['engage'])) {
                    $cursorEngage = (int) $decoded['engage'];
                    $query->where(function ($q) use ($cursorEngage, $cursorId) {
                        $q->whereRaw('(likes_count + bookmarks_count) < ?', [$cursorEngage])
                          ->orWhere(function ($q2) use ($cursorEngage, $cursorId) {
                              $q2->whereRaw('(likes_count + bookmarks_count) = ?', [$cursorEngage])
                                 ->where('id', '<', $cursorId);
                          });
                    });
                } elseif (isset($decoded['score'])) {
                    $cursorScore = (float) $decoded['score'];
                    $query->where(function ($q) use ($cursorScore, $cursorId) {
                        $q->where('ranking_score', '<', $cursorScore)
                          ->orWhere(function ($q2) use ($cursorScore, $cursorId) {
                              $q2->where('ranking_score', '=', $cursorScore)
                                 ->where('id', '<', $cursorId);
                          });
                    });
                }
            }
        }

        $items = $query->take($limit + 1)->get();
        $hasMore = $items->count() > $limit;
        $moments = $hasMore ? $items->take($limit) : $items;

        $nextCursor = null;
        if ($hasMore && $moments->isNotEmpty()) {
            $last = $moments->last();
            $jsonPayload = json_encode([
                'id'     => $last->id,
                'score'  => (float) $last->ranking_score,
                'engage' => (int) ($last->likes_count + $last->bookmarks_count),
            ]);
            $nextCursor = $jsonPayload ? base64_encode($jsonPayload) : null;
        }

        $transformed = $moments->map(function (Moment $m) use ($user) {
            return [
                'id'                     => $m->id,
                'author'                 => [
                    'id'          => $m->author->id ?? null,
                    'name'        => $m->author->name ?? 'Pengguna Rentify',
                    'username'    => $m->author->username ?? ($m->author ? 'user' . $m->author->id : null),
                    'foto_profil' => $m->author->foto_profil ?? null,
                ],
                'media_url'              => $m->media_url,
                'thumbnail_url'          => $m->thumbnail_url,
                'media_urls'             => $m->media_urls ?: ($m->media_url ? [$m->media_url] : []),
                'media_type'             => $m->media_type,
                'video_duration_seconds' => $m->video_duration_seconds,
                'caption'                => $m->caption,
                'likes_count'            => (int) $m->likes_count,
                'comments_count'         => (int) ($m->comments_count ?? 0),
                'bookmarks_count'        => (int) $m->bookmarks_count,
                'reports_count'          => (int) $m->reports_count,
                'ranking_score'          => (float) $m->ranking_score,
                'status'                 => $m->status,
                'is_liked'               => $m->isLikedBy($user),
                'is_bookmarked'          => $m->isBookmarkedBy($user),
                'created_at'             => $m->created_at ? $m->created_at->toISOString() : null,
                'tagged_products'        => $m->taggedProducts->map(function ($tp) {
                    return [
                        'id'            => $tp->product_id,
                        'display_order' => $tp->display_order,
                        'nama'          => $tp->product->nama ?? null,
                        'harga_sewa'    => $tp->product->harga_sewa ?? 0,
                        'cover_photo'   => $tp->product->cover_photo ?? null,
                        'vendor_name'   => $tp->product->vendor->vendor_name ?? null,
                        'stock'         => $tp->product->stok ?? 1,
                    ];
                }),
            ];
        });

        return response()->json([
            'success'       => true,
            'data'          => $transformed,
            'next_cursor'   => $nextCursor,
            'has_more'      => $hasMore,
            'fallback_mode' => $fallbackMode,
        ]);
    }

    /**
     * Tampilkan detail Momen spesifik.
     * GET /api/moments/{id}
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $moment = Moment::with(['author', 'taggedProducts.product.vendor'])->find($id);

        if (!$moment || $moment->status === 'deleted') {
            return response()->json([
                'success' => false,
                'message' => 'Postingan Momen tidak ditemukan.',
            ], 404);
        }

        $user = $request->user();

        // Jika postingan under_moderation, hanya author atau admin yang berhak melihat
        if ($moment->status === 'under_moderation') {
            if (!$user || ($user->id !== $moment->user_id && $user->role !== 'admin')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Postingan sedang dalam peninjauan moderasi.',
                ], 403);
            }
        }

        return response()->json([
            'success' => true,
            'data'    => [
                'id'                     => $moment->id,
                'author'                 => [
                    'id'          => $moment->author->id ?? null,
                    'name'        => $moment->author->name ?? 'Pengguna Rentify',
                    'username'    => $moment->author->username ?? null,
                    'foto_profil' => $moment->author->foto_profil ?? null,
                ],
                'media_url'              => $moment->media_url,
                'thumbnail_url'          => $moment->thumbnail_url,
                'media_urls'             => $moment->media_urls ?: ($moment->media_url ? [$moment->media_url] : []),
                'media_type'             => $moment->media_type,
                'video_duration_seconds' => $moment->video_duration_seconds,
                'caption'                => $moment->caption,
                'likes_count'            => (int) $moment->likes_count,
                'comments_count'         => (int) ($moment->comments_count ?? 0),
                'bookmarks_count'        => (int) $moment->bookmarks_count,
                'reports_count'          => (int) $moment->reports_count,
                'ranking_score'          => (float) $moment->ranking_score,
                'status'                 => $moment->status,
                'is_liked'               => $moment->isLikedBy($user),
                'is_bookmarked'          => $moment->isBookmarkedBy($user),
                'created_at'             => $moment->created_at ? $moment->created_at->toISOString() : null,
                'tagged_products'        => $moment->taggedProducts->map(function ($tp) {
                    return [
                        'id'            => $tp->product_id,
                        'display_order' => $tp->display_order,
                        'nama'          => $tp->product->nama ?? null,
                        'harga_sewa'    => $tp->product->harga_sewa ?? 0,
                        'cover_photo'   => $tp->product->cover_photo ?? null,
                        'vendor_name'   => $tp->product->vendor->vendor_name ?? null,
                        'stock'         => $tp->product->stok ?? 1,
                    ];
                }),
            ],
        ]);
    }

    /**
     * Buat postingan Momen baru dengan unggah media ke Cloudinary.
     * POST /api/moments
     */
    public function store(StoreMomentRequest $request): JsonResponse
    {
        $user = $request->user();
        $mediaInput = $request->file('media');

        $videoExtensions = [
            'mp4', 'mov', 'quicktime', 'webm', 'mkv', 'avi', 'wmv', 'flv', '3gp',
            '3g2', 'm4v', 'ts', 'mpeg', 'mpg', 'ogv', 'vob', 'm2ts', 'divx', 'rm', 'rmvb', 'asf', 'm4p', 'f4v'
        ];

        $mediaUrls = [];
        $mediaType = 'image';
        $thumbnailUrl = null;
        $videoDuration = null;

        if (is_array($mediaInput)) {
            // Multi-photo upload (Carousel Post)
            foreach ($mediaInput as $file) {
                if ($file instanceof \Illuminate\Http\UploadedFile && $file->isValid()) {
                    $uploadResult = $this->uploadMediaToCloudinary($file, 'image');
                    $mediaUrls[] = $uploadResult['secure_url'];
                }
            }

            if (empty($mediaUrls)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal memproses berkas foto yang diunggah.',
                ], 422);
            }

            $primaryMediaUrl = $mediaUrls[0];
            $mediaType = 'image';
        } else {
            // Single media (image or video)
            $file = $mediaInput;
            $mime = $file->getMimeType() ?: '';
            $ext = strtolower($file->getClientOriginalExtension() ?: '');

            $mediaType = (str_starts_with($mime, 'video/') || in_array($ext, $videoExtensions)) ? 'video' : 'image';
            if ($request->filled('media_type') && in_array($request->input('media_type'), ['image', 'video'])) {
                $mediaType = $request->input('media_type');
            }

            // Upload media ke Cloudinary dengan optimasi f_auto, q_auto dan fallback HEIC
            $uploadResult = $this->uploadMediaToCloudinary($file, $mediaType);
            $primaryMediaUrl = $uploadResult['secure_url'];
            $thumbnailUrl = $uploadResult['thumbnail_url'];
            $mediaUrls = [$primaryMediaUrl];

            if ($mediaType === 'video') {
                $videoDuration = $request->input('video_duration_seconds');
            }
        }

        $moment = Moment::create([
            'user_id'                => $user->id,
            'media_url'              => $primaryMediaUrl,
            'thumbnail_url'          => $thumbnailUrl,
            'media_urls'             => $mediaUrls,
            'media_type'             => $mediaType,
            'video_duration_seconds' => $videoDuration,
            'caption'                => $request->input('caption'),
            'likes_count'            => 0,
            'lifetime_minted_count'  => 0,
            'bookmarks_count'        => 0,
            'reports_count'          => 0,
            'ranking_score'          => 0,
            'status'                 => 'active',
        ]);

        // Simpan produk yang ditandai (maksimal 3 item)
        if ($request->filled('product_ids')) {
            $productIds = array_slice((array) $request->input('product_ids'), 0, 3);
            foreach ($productIds as $index => $prodId) {
                MomentTaggedProduct::create([
                    'moment_id'     => $moment->id,
                    'product_id'    => (int) $prodId,
                    'display_order' => $index + 1,
                ]);
            }
        }

        // Dispatch background job untuk analisis AI Vision tagging
        ProcessAiMomentTaggingJob::dispatch($moment->id);

        return response()->json([
            'success' => true,
            'message' => 'Momen berhasil dibagikan!',
            'data'    => $moment->load(['author', 'taggedProducts.product']),
        ], 201);
    }

    /**
     * Perbarui caption dan produk yang ditandai pada Momen.
     * PUT/PATCH /api/moments/{id}
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Silakan masuk terlebih dahulu.'], 401);
        }

        $moment = Moment::findOrFail($id);

        if ($moment->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki hak akses untuk mengedit momen ini.',
            ], 403);
        }

        $request->validate([
            'caption'       => 'nullable|string|max:1000',
            'product_ids'   => 'nullable|array|max:3',
            'product_ids.*' => 'integer|exists:barang,id',
        ]);

        if ($request->has('caption')) {
            $moment->caption = $request->input('caption');
            $moment->save();
        }

        if ($request->has('product_ids')) {
            MomentTaggedProduct::where('moment_id', $moment->id)->delete();
            $productIds = array_slice((array) $request->input('product_ids'), 0, 3);
            foreach ($productIds as $index => $prodId) {
                MomentTaggedProduct::create([
                    'moment_id'     => $moment->id,
                    'product_id'    => (int) $prodId,
                    'display_order' => $index + 1,
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Momen berhasil diperbarui.',
            'data'    => $moment->load(['author', 'taggedProducts.product']),
        ]);
    }

    /**
     * Hapus postingan Momen (soft-delete) dan lakukan penarikan koin (clawback).
     * DELETE /api/moments/{id}
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Silakan masuk terlebih dahulu.'], 401);
        }

        $moment = Moment::findOrFail($id);

        if ($moment->user_id !== $user->id && $user->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki hak akses untuk menghapus momen ini.',
            ], 403);
        }

        // Tandai postingan sebagai deleted (soft-delete dari feed)
        $moment->status = 'deleted';
        $moment->save();

        // Eksekusi penarikan koin yang telah dicetak dari postingan ini
        $clawback = $this->coinService->clawbackMomentCoins($moment->id);

        return response()->json([
            'success'  => true,
            'message'  => 'Momen berhasil dihapus.',
            'clawback' => $clawback,
        ]);
    }

    /**
     * Ambil rekomendasi tag produk hasil analisis AI yang masih aktif.
     * GET /api/moments/{id}/suggestions
     */
    public function suggestions(int $id): JsonResponse
    {
        $moment = Moment::find($id);
        if (!$moment) {
            return response()->json(['success' => false, 'message' => 'Postingan Momen tidak ditemukan.'], 404);
        }

        $suggestions = MomentAiTagSuggestion::with('product')
            ->where('moment_id', $id)
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->orderBy('confidence_score', 'desc')
            ->limit(5)
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $suggestions,
        ]);
    }

    /**
     * Picu proses ulang analisis AI Vision untuk Moment ini.
     * POST /api/moments/{id}/ai-tag
     */
    public function reprocessAiTag(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Silakan masuk terlebih dahulu.'], 401);
        }

        $moment = Moment::findOrFail($id);

        if ($moment->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Hanya pembuat momen yang dapat meminta analisis ulang AI.',
            ], 403);
        }

        ProcessAiMomentTaggingJob::dispatch($moment->id);

        return response()->json([
            'success' => true,
            'message' => 'Analisis AI sedang diproses ulang di latar belakang.',
        ], 202);
    }

    /**
     * Simpan Momen ke bookmark (Tersimpan).
     * POST /api/moments/{id}/bookmark
     */
    public function bookmark(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Silakan masuk terlebih dahulu.'], 401);
        }

        $moment = Moment::where('status', '!=', 'deleted')->find($id);
        if (!$moment) {
            return response()->json(['success' => false, 'message' => 'Postingan Momen tidak ditemukan.'], 404);
        }

        $bookmark = MomentBookmark::firstOrCreate([
            'user_id'   => $user->id,
            'moment_id' => $moment->id,
        ]);

        if ($bookmark->wasRecentlyCreated) {
            $moment->increment('bookmarks_count');
            $moment->updateRankingScore();
        }

        return response()->json([
            'success'         => true,
            'bookmarked'      => true,
            'bookmarks_count' => (int) $moment->bookmarks_count,
            'message'         => 'Momen berhasil disimpan ke daftar tersimpan.',
        ]);
    }

    /**
     * Hapus Momen dari bookmark.
     * DELETE /api/moments/{id}/bookmark
     */
    public function unbookmark(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Silakan masuk terlebih dahulu.'], 401);
        }

        $moment = Moment::where('status', '!=', 'deleted')->find($id);
        if (!$moment) {
            return response()->json(['success' => false, 'message' => 'Postingan Momen tidak ditemukan.'], 404);
        }

        $deleted = MomentBookmark::where('user_id', $user->id)
            ->where('moment_id', $moment->id)
            ->delete();

        if ($deleted) {
            $moment->bookmarks_count = max(0, $moment->bookmarks_count - 1);
            $moment->updateRankingScore();
        }

        return response()->json([
            'success'         => true,
            'bookmarked'      => false,
            'bookmarks_count' => (int) $moment->bookmarks_count,
            'message'         => 'Momen dihapus dari daftar tersimpan.',
        ]);
    }

    /**
     * Laporkan Momen yang melanggar aturan.
     * POST /api/moments/{id}/report
     */
    public function report(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Silakan masuk terlebih dahulu.'], 401);
        }

        $moment = Moment::where('status', '!=', 'deleted')->find($id);
        if (!$moment) {
            return response()->json(['success' => false, 'message' => 'Postingan Momen tidak ditemukan.'], 404);
        }

        $request->validate([
            'category'           => 'required|in:spam,inappropriate,fraud,copyright',
            'reason_description' => 'nullable|string|max:1000',
        ], [
            'category.required' => 'Pilih salah satu kategori laporan.',
            'category.in'       => 'Kategori laporan tidak valid. Pilih dari: spam, inappropriate, fraud, copyright.',
        ]);

        $alreadyReported = MomentReport::where('moment_id', $moment->id)
            ->where('user_id', $user->id)
            ->exists();

        if ($alreadyReported) {
            return response()->json([
                'success' => false,
                'message' => 'Anda sudah pernah melaporkan postingan ini.',
            ], 422);
        }

        MomentReport::create([
            'moment_id'          => $moment->id,
            'user_id'            => $user->id,
            'category'           => $request->input('category'),
            'reason_description' => $request->input('reason_description'),
            'created_at'         => now(),
        ]);

        $moment->increment('reports_count');
        $totalReports = $moment->reports_count;

        // Jika jumlah laporan unik mencapai threshold (>= 5), pindahkan ke status under_moderation
        if ($totalReports >= 5) {
            $moment->status = 'under_moderation';
            $moment->save();

            MomentModerationQueue::updateOrCreate(
                ['moment_id' => $moment->id],
                [
                    'status'           => 'pending',
                    'total_reports'    => $totalReports,
                    'last_reported_at' => now(),
                ]
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Laporan Anda telah diterima dan akan ditinjau.',
        ]);
    }

    /**
     * Helper unggah media ke Cloudinary dengan deteksi tipe, kompresi, dan fallback HEIC.
     */
    protected function uploadMediaToCloudinary($file, string $mediaType): array
    {
        $cloudinaryUrl = config('cloudinary.cloud_url') ?: env('CLOUDINARY_URL') ?: getenv('CLOUDINARY_URL');
        $ext = strtolower($file->getClientOriginalExtension() ?: '');

        // Lingkungan testing atau bila kredensial Cloudinary belum diatur
        if (app()->environment('testing') || empty($cloudinaryUrl) || str_contains($cloudinaryUrl, 'dummy') || str_contains($cloudinaryUrl, 'test')) {
            try {
                if (in_array($ext, ['heic', 'heif'])) {
                    $filename = \Illuminate\Support\Str::random(40) . '.jpg';
                    $rawBytes = (string) file_get_contents($file->getRealPath());
                    \Illuminate\Support\Facades\Storage::disk('public')->put('moments/' . $filename, $rawBytes);
                    $path = 'moments/' . $filename;
                } else {
                    $path = \App\Services\StorageService::storePublicFile($file, 'moments');
                }
                $fullUrl = \App\Services\StorageService::getPublicUrl($path);
                return [
                    'secure_url'    => $fullUrl,
                    'thumbnail_url' => $mediaType === 'video' ? $fullUrl : null,
                ];
            } catch (\Throwable $e) {
                return [
                    'secure_url'    => 'https://res.cloudinary.com/fnf8f1pm/image/upload/v1792881951/rentify/moments/naikdt1lgug5qgylrk1l.jpg',
                    'thumbnail_url' => null,
                ];
            }
        }

        try {
            $cloudinary = new \Cloudinary\Cloudinary($cloudinaryUrl);
            $qualityConfig = (int) config('filesystems.image_compression_quality', env('IMAGE_COMPRESSION_QUALITY', 100));
            $qualityValue = ($qualityConfig >= 100) ? 'auto:best' : (string) $qualityConfig;

            $options = [
                'folder'        => 'rentify/moments',
                'resource_type' => $mediaType === 'video' ? 'video' : 'image',
                'transformation' => [
                    'quality'      => $qualityValue,
                    'fetch_format' => 'auto',
                ],
            ];

            if (in_array($ext, ['heic', 'heif'])) {
                $options['format'] = 'jpg';
            }

            if ($mediaType === 'video' && method_exists($cloudinary->uploadApi(), 'uploadLarge')) {
                $uploadResult = $cloudinary->uploadApi()->uploadLarge($file->getRealPath(), $options);
            } else {
                $uploadResult = $cloudinary->uploadApi()->upload($file->getRealPath(), $options);
            }

            $secureUrl = $uploadResult['secure_url'] ?? '';
            $thumbnailUrl = null;

            if ($mediaType === 'video' && isset($uploadResult['public_id'])) {
                $cloudName = $cloudinary->configuration->cloud->cloudName ?? 'fnf8f1pm';
                $thumbnailUrl = "https://res.cloudinary.com/{$cloudName}/video/upload/c_thumb,w_400/{$uploadResult['public_id']}.jpg";
            }

            return [
                'secure_url'    => $secureUrl,
                'thumbnail_url' => $thumbnailUrl,
            ];
        } catch (\Throwable $e) {
            Log::error("Cloudinary upload failed: " . $e->getMessage());

            try {
                $path = \App\Services\StorageService::storePublicFile($file, 'moments');
                $fullUrl = \App\Services\StorageService::getPublicUrl($path);
                return [
                    'secure_url'    => $fullUrl,
                    'thumbnail_url' => $mediaType === 'video' ? $fullUrl : null,
                ];
            } catch (\Throwable $fallbackEx) {
                Log::error("Local moments fallback failed: " . $fallbackEx->getMessage());
                return [
                    'secure_url'    => 'https://res.cloudinary.com/fnf8f1pm/image/upload/v1792881951/rentify/moments/naikdt1lgug5qgylrk1l.jpg',
                    'thumbnail_url' => null,
                ];
            }
        }
    }
}
