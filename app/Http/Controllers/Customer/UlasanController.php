<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Penyewaan;
use App\Models\Ulasan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UlasanController extends Controller
{
    /**
     * Simpan atau perbarui ulasan untuk pesanan rental.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int|string|\App\Models\Penyewaan  $penyewaan
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function store(Request $request, $penyewaan)
    {
        $userId = Auth::id();
        if (!$userId) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Silakan login terlebih dahulu.'], 401);
            }
            return redirect()->route('login');
        }

        // Cari data pesanan
        $order = ($penyewaan instanceof Penyewaan) 
            ? $penyewaan 
            : Penyewaan::with('details')->find($penyewaan);

        if (!$order) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Pesanan tidak ditemukan.'], 404);
            }
            return redirect()->back()->with('error', '⚠️ Pesanan tidak ditemukan.');
        }

        // Otorisasi: Pastikan pesanan adalah milik customer yang sedang login
        if ((int)$order->user_id !== (int)$userId) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Anda tidak memiliki akses ke pesanan ini.'], 403);
            }
            return redirect()->back()->with('error', '⚠️ Anda tidak berhak mengulas pesanan ini.');
        }

        // Validasi input
        $validated = $request->validate([
            'rating'    => 'required|integer|min:1|max:5',
            'komentar'  => 'nullable|string|max:1000',
            'barang_id' => 'nullable|integer',
        ], [
            'rating.required' => 'Mohon berikan rating bintang (1 - 5).',
            'rating.min'      => 'Rating minimal adalah 1 bintang.',
            'rating.max'      => 'Rating maksimal adalah 5 bintang.',
            'komentar.max'    => 'Komentar ulasan maksimal 1000 karakter.',
        ]);

        // Tentukan barang_id yang diulas
        $barangId = $validated['barang_id'] ?? null;

        if (!$barangId) {
            // Ambil barang pertama dari order_items jika tidak disediakan
            $firstItem = DB::table('order_items')->where('order_id', $order->id)->first();
            $barangId = $firstItem ? $firstItem->product_id : null;
        }

        if (!$barangId) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Barang yang diulas tidak valid.'], 422);
            }
            return redirect()->back()->with('error', '⚠️ Barang rental untuk pesanan ini tidak ditemukan.');
        }

        // Simpan / perbarui ulasan (idempoten & bebas duplikasi)
        $ulasan = Ulasan::updateOrCreate(
            [
                'order_id'  => $order->id,
                'user_id'   => $userId,
                'barang_id' => $barangId,
            ],
            [
                'rating'    => (int) $validated['rating'],
                'komentar'  => $validated['komentar'] ? trim($validated['komentar']) : null,
            ]
        );

        // Jika status pesanan adalah 'Menunggu Ulasan', otomatis transisikan ke 'Selesai'
        if ($order->status === 'Menunggu Ulasan') {
            DB::table('orders')->where('id', $order->id)->update([
                'status'     => 'Selesai',
                'updated_at' => now('Asia/Jakarta'),
            ]);
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => '🎉 Terima kasih! Ulasan berhasil disimpan.',
                'ulasan'  => $ulasan,
            ]);
        }

        return redirect()->route('customer.pesanan', ['status' => 'selesai'])
            ->with('success', '🎉 Terima kasih! Ulasan bintang & testimoni Anda telah dipublikasikan.');
    }
}
