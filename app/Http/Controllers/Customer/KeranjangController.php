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
        return view('customer.keranjang', compact('keranjangs'));
    }

    /**
     * 2. Menambah barang ke keranjang
     */
    public function add(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu untuk menambah keranjang.');
        }

        $userId = Auth::id();
        $barang_id = $request->barang_id; 
        $barang = Barang::findOrFail($barang_id);

        $keranjangLama = Keranjang::where('user_id', $userId)
                                  ->where('barang_id', $barang_id)
                                  ->first();

        if ($keranjangLama) {
            $keranjangLama->jumlah += 1;
            $keranjangLama->save();
        } else {
            Keranjang::create([
                'user_id'     => $userId,
                'barang_id'   => $barang_id,
                'jumlah'      => 1,
                'durasi_sewa' => 1
            ]);
        }

        return back()->with('success', 'Yey! ' . $barang->nama . ' berhasil masuk keranjang.');
    }

    /**
     * 3. FUNGSI UPDATE YANG SUDAH DIPERBAIKI (Menyimpan jumlah baru ke Database)
     */
    public function update(Request $request, $id)
    {
        // Cari data keranjang milik user yang sedang login
        $keranjang = Keranjang::where('id', $id)->where('user_id', Auth::id())->firstOrFail();
        
        // Update jumlahnya dengan angka baru dari tombol [+] atau [-]
        if ($request->has('jumlah') && $request->jumlah > 0) {
            $keranjang->jumlah = (int) $request->jumlah;
            $keranjang->save();
        }

        // Jika dipanggil dari JavaScript (AJAX), beri balikan sukses
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'baru' => $keranjang->jumlah]);
        }

        return back()->with('success', 'Jumlah barang berhasil diperbarui.');
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