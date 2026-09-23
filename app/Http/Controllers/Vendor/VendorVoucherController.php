<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Voucher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class VendorVoucherController extends Controller
{
    // 1. Menampilkan halaman daftar voucher dan form pembuatannya
    public function index()
    {
        $vouchers = Voucher::where('vendor_id', Auth::id())->latest()->get();
        return view('vendor.voucher.index', compact('vouchers'));
    }

    // 2. Memproses penyimpanan voucher baru ke database
    public function store(Request $request)
    {
        $request->validate([
            'kode_voucher' => 'required|string|unique:vouchers,kode_voucher',
            'tipe_diskon' => 'required|in:nominal,persen',
            'nilai_diskon' => 'required|numeric|min:1',
            'maksimal_diskon' => 'nullable|numeric|min:1',
            'minimal_belanja' => 'required|numeric|min:0',
            'kuota_total' => 'required|integer|min:1',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
        ], [
            'kode_voucher.unique' => 'Kode Voucher ini sudah pernah dipakai, buat kode lain.',
            'tanggal_selesai.after_or_equal' => 'Tanggal selesai tidak boleh lebih cepat dari tanggal mulai.'
        ]);

        Voucher::create([
            'vendor_id' => Auth::id(),
            // Str::slug memastikan kode voucher jadi huruf kapital semua tanpa spasi (cth: PROMOJONI)
            'kode_voucher' => strtoupper(Str::slug($request->kode_voucher, '')), 
            'tipe_diskon' => $request->tipe_diskon,
            'nilai_diskon' => $request->nilai_diskon,
            'maksimal_diskon' => $request->maksimal_diskon,
            'minimal_belanja' => $request->minimal_belanja,
            'kuota_total' => $request->kuota_total,
            'kuota_terpakai' => 0,
            'tanggal_mulai' => $request->tanggal_mulai,
            'tanggal_selesai' => $request->tanggal_selesai,
            'is_active' => 1,
        ]);

        return back()->with('success', 'Voucher berhasil dibuat dan aktif!');
    }

    // 3. Menghapus voucher
    public function destroy($id)
    {
        $voucher = Voucher::where('vendor_id', Auth::id())->findOrFail($id);
        $voucher->delete();
        return back()->with('success', 'Voucher berhasil dihapus!');
    }
}