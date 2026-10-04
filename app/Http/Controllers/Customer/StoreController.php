<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Barang;
use App\Models\Voucher;
use App\Helpers\RentifyHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StoreController extends Controller
{
    /**
     * Menampilkan profil toko (ala Shopee) lengkap dengan katalog barang sewa dan voucher promo toko.
     */
    public function show($id, Request $request)
    {
        $vendor = User::with(['barangs' => function($q) {
            $q->approved();
        }])->findOrFail($id);

        if (!$vendor->isVendor() && $vendor->role !== 'vendor') {
            return redirect()->route('customer.home')->with('error', 'Toko tidak ditemukan.');
        }

        if ($vendor->vendor_status === 'suspended') {
            return redirect()->route('customer.home')->with('error', '⚠️ Toko ini sedang dinonaktifkan sementara.');
        }

        // Hitung jarak user ke toko jika koordinat tersedia
        $jarak = null;
        if (Auth::check() && Auth::user()->latitude && Auth::user()->longitude) {
            $lat1 = (float) Auth::user()->latitude;
            $lon1 = (float) Auth::user()->longitude;
            $lat2 = (float) ($vendor->latitude ?? 0);
            $lon2 = (float) ($vendor->longitude ?? 0);

            if ($lat2 != 0 && $lon2 != 0) {
                $earthRadius = 6371; 
                $dLat = deg2rad($lat2 - $lat1);
                $dLon = deg2rad($lon2 - $lon1);
                $a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon/2) * sin($dLon/2);
                $c = 2 * asin(sqrt($a));
                $jarak = $earthRadius * $c;
            }
        }

        // Ambil voucher aktif yang dibuat oleh toko ini
        $vouchers = Voucher::where('vendor_id', $vendor->id)
            ->where('is_active', 1)
            ->whereDate('tanggal_mulai', '<=', now())
            ->whereDate('tanggal_selesai', '>=', now())
            ->whereColumn('kuota_terpakai', '<', 'kuota_total')
            ->latest()
            ->get();

        // Query produk sewa toko
        $query = Barang::with(['kategori', 'fotos'])
            ->where('vendor_id', $vendor->id)
            ->approved();

        // Filter kategori jika ada
        if ($request->filled('kategori')) {
            $query->whereHas('kategori', function($q) use ($request) {
                $q->where('slug', $request->kategori);
            });
        }

        // Pencarian di dalam toko
        if ($request->filled('q')) {
            $keyword = $request->q;
            $query->where(function($q) use ($keyword) {
                $q->where('nama', 'LIKE', "%{$keyword}%")
                  ->orWhere('deskripsi', 'LIKE', "%{$keyword}%");
            });
        }

        $barangs = $query->latest()->paginate(12)->withQueryString();

        // Kategori produk yang dimiliki toko ini untuk filter
        $kategoris = \App\Models\Kategori::whereHas('barangs', function($q) use ($vendor) {
            $q->where('vendor_id', $vendor->id)->approved();
        })->get();

        $areaDesa = RentifyHelper::formatAreaDesa($vendor->alamat_lengkap);

        return view('customer.toko.show', compact('vendor', 'vouchers', 'barangs', 'kategoris', 'jarak', 'areaDesa'));
    }
}
