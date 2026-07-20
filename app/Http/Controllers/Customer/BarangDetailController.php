<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Barang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BarangDetailController extends Controller
{
    /**
     * Menampilkan halaman detail produk Rentify.
     * Dibuat fleksibel agar bisa membaca parameter {slug} ataupun {id} tanpa eror.
     */
    public function show($slug)
    {
        // Cari barangnya
        $barang = Barang::with(['fotos', 'vendor', 'kategori'])
                        ->where(function ($query) use ($slug) {
                            $query->where('slug', $slug)
                                  ->orWhere('id', $slug);
                        })
                        ->first();

        // Jika barang tidak ada di database
        if (!$barang) {
            return redirect()->route('customer.home')->with('error', '⚠️ Barang yang Anda cari tidak ditemukan di sistem.');
        }

        // FILTER TOKO BANNED: Cek apakah vendor_status adalah 'suspended'
        $statusToko = strtolower($barang->vendor->vendor_status ?? '');
        if ($statusToko === 'suspended') {
            return redirect()->route('customer.home')->with('error', '⚠️ Mohon maaf, barang "' . $barang->nama . '" tidak dapat diakses karena toko pemiliknya sedang ditangguhkan/diblokir sementara oleh Admin.');
        }

        // Menghitung jarak (Haversine Formula)
        if (Auth::check() && Auth::user()->latitude && Auth::user()->longitude) {
            $lat1 = (float) Auth::user()->latitude;
            $lon1 = (float) Auth::user()->longitude;

            $lat2 = (float) ($barang->latitude ?? $barang->vendor->latitude ?? 0);
            $lon2 = (float) ($barang->longitude ?? $barang->vendor->longitude ?? 0);

            if ($lat2 && $lon2 && ($lat2 != 0 || $lon2 != 0)) {
                $earthRadius = 6371; 
                $dLat = deg2rad($lat2 - $lat1);
                $dLon = deg2rad($lon2 - $lon1);

                $a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon/2) * sin($dLon/2);
                $c = 2 * asin(sqrt($a));
                $barang->jarak = $earthRadius * $c;
            }
        }

        return view('customer.barang-detail', compact('barang'));
    }
}