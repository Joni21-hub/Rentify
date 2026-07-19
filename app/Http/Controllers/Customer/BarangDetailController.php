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
        // Mengambil data barang beserta relasi foto galeri, vendor, dan kategori
        $barang = Barang::with(['fotos', 'vendor', 'kategori'])
                        ->where('slug', $slug)
                        ->orWhere('id', $slug)
                        ->firstOrFail();

        // FITUR BARU: Menghitung jarak akurat ke titik GPS customer (Haversine Formula)
        if (Auth::check() && Auth::user()->latitude && Auth::user()->longitude) {
            $lat1 = (float) Auth::user()->latitude;
            $lon1 = (float) Auth::user()->longitude;

            $lat2 = (float) ($barang->latitude ?? $barang->vendor->latitude ?? 0);
            $lon2 = (float) ($barang->longitude ?? $barang->vendor->longitude ?? 0);

            if ($lat2 && $lon2 && ($lat2 != 0 || $lon2 != 0)) {
                $earthRadius = 6371; // Radius Bumi dalam KM
                $dLat = deg2rad($lat2 - $lat1);
                $dLon = deg2rad($lon2 - $lon1);

                $a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon/2) * sin($dLon/2);
                $c = 2 * asin(sqrt($a));
                $barang->jarak = $earthRadius * $c;
            }
        }

        // Mengembalikan ke tampilan halaman detail
        return view('customer.barang-detail', compact('barang'));
    }
}