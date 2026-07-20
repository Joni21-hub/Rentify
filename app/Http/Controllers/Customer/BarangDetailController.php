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
        // Langkah 1: Cari barangnya terlebih dahulu
        $barang = Barang::with(['fotos', 'vendor', 'kategori'])
                        ->where(function ($query) use ($slug) {
                            $query->where('slug', $slug)
                                  ->orWhere('id', $slug);
                        })
                        ->first();

        // Langkah 2: Jika barang memang sudah dihapus permanen
        if (!$barang) {
            return redirect()->route('customer.home')->with('error', '⚠️ Barang yang Anda cari tidak ditemukan di sistem.');
        }

        // Langkah 3: FILTER TOKO BANNED (Peringatan Jelas, Bukan Error 404)
        $statusToko = strtolower($barang->vendor->status ?? '');
        if ($statusToko === 'banned') {
            return redirect()->route('customer.home')->with('error', '⚠️ Mohon maaf, barang "' . $barang->nama . '" tidak dapat diakses karena toko pemiliknya sedang ditangguhkan/diblokir sementara oleh Admin.');
        }

        // Langkah 4: Menghitung jarak akurat ke titik GPS customer (Haversine Formula)
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

        // Mengembalikan ke tampilan halaman detail jika semuanya aman
        return view('customer.barang-detail', compact('barang'));
    }
}