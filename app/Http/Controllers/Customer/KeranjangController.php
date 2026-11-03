<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Keranjang;
use App\Models\Barang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KeranjangController extends Controller
{
    /**
     * 1. Menampilkan halaman keranjang
     */
    public function index()
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu.');
        }

        $keranjangs = Keranjang::with('barang.vendor')->where('user_id', Auth::id())->latest()->get();

        // Kumpulkan tanggal yang penuh dibooking untuk seluruh barang di keranjang
        $fullyBookedByBarang = [];
        $allFullyBookedDates = [];
        foreach ($keranjangs as $item) {
            if ($item->barang) {
                $dates = $item->barang->getFullyBookedDates();
                $fullyBookedByBarang[$item->id] = $dates;
                $allFullyBookedDates = array_merge($allFullyBookedDates, $dates);
            }
        }
        $fullyBookedDates = array_values(array_unique($allFullyBookedDates));

        return view('customer.keranjang', compact('keranjangs', 'fullyBookedDates', 'fullyBookedByBarang'));
    }

    /**
     * 2. Menambah barang ke keranjang
     */
    public function add(Request $request)
    {
        if (!Auth::check()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'status' => 'unauthenticated',
                    'message' => 'Silakan login terlebih dahulu.',
                    'redirect' => route('login')
                ], 401);
            }
            return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu untuk menambah keranjang.');
        }

        $userId = Auth::id();
        $barang_id = $request->barang_id; 
        $barang = Barang::findOrFail($barang_id);

        $durasi = (int) $request->input('durasi_sewa', 1);
        if ($durasi < 1) $durasi = 1;
        $jumlah = (int) $request->input('jumlah', 1);
        if ($jumlah < 1) $jumlah = 1;

        $keranjangLama = Keranjang::where('user_id', $userId)
                                  ->where('barang_id', $barang_id)
                                  ->first();

        if ($keranjangLama) {
            $keranjangLama->jumlah += $jumlah;
            if ($request->has('durasi_sewa')) {
                $keranjangLama->durasi_sewa = $durasi;
            }
            $keranjangLama->save();
        } else {
            Keranjang::create([
                'user_id'     => $userId,
                'barang_id'   => $barang_id,
                'jumlah'      => $jumlah,
                'durasi_sewa' => $durasi
            ]);
        }

        if ($request->ajax() || $request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            $totalKeranjang = Keranjang::where('user_id', $userId)->count();
            return response()->json([
                'status' => 'success',
                'message' => 'Yey! ' . $barang->nama . ' berhasil masuk keranjang.',
                'cart_count' => $totalKeranjang
            ]);
        }

        return back()->with('success', 'Yey! ' . $barang->nama . ' berhasil masuk keranjang.');
    }

    /**
     * 3. FUNGSI UPDATE YANG SUDAH DIPERBAIKI (Bisa simpan Jumlah & Durasi Hari)
     */
    public function update(Request $request, $id)
    {
        $keranjang = Keranjang::where('id', $id)->where('user_id', Auth::id())->firstOrFail();
        
        if ($request->has('jumlah') && $request->jumlah > 0) {
            $keranjang->jumlah = (int) $request->jumlah;
        }

        // Menyimpan pilihan durasi hari ke database
        if ($request->has('durasi_sewa') && $request->durasi_sewa > 0) {
            $keranjang->durasi_sewa = (int) $request->durasi_sewa;
        }

        $keranjang->save();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true, 
                'baru_jumlah' => $keranjang->jumlah,
                'baru_durasi' => $keranjang->durasi_sewa
            ]);
        }

        return back()->with('success', 'Keranjang berhasil diperbarui.');
    }

    /**
     * 4. Menghapus barang dari keranjang
     */
    public function remove($id)
    {
        Keranjang::where('id', $id)->where('user_id', Auth::id())->delete();
        return back()->with('success', 'Barang dihapus dari keranjang.');
    }
}