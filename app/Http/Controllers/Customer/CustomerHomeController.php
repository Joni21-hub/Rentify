<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Barang; 
use App\Models\Banner; 
use Illuminate\Support\Facades\Auth;

class CustomerHomeController extends Controller
{
    public function index()
    {
        $banners = Banner::all();
        $daftarBarang = collect(); // Default kosong
        $butuhLokasi = false;

        // Cek apakah user sudah login
        if (Auth::check()) {
            $user = Auth::user();
            
            // Cek apakah user sudah punya latitude dan longitude
            if ($user->latitude && $user->longitude) {
                // Ambil semua barang yang disetujui
                $semuaBarang = Barang::where('status_barang', 'disetujui')->get();

                // Filter barang berdasarkan radius 50 KM (Haversine Formula)
                $lat1 = (float) $user->latitude;
                $lon1 = (float) $user->longitude;

                foreach ($semuaBarang as $barang) {
                    $lat2 = (float) $barang->latitude;
                    $lon2 = (float) $barang->longitude;

                    // Lewati barang yang tidak punya koordinat
                    if (!$lat2 || !$lon2 || ($lat2 == 0 && $lon2 == 0)) {
                        continue;
                    }

                    // Rumus Haversine
                    $earthRadius = 6371; // Radius Bumi dalam KM
                    $dLat = deg2rad($lat2 - $lat1);
                    $dLon = deg2rad($lon2 - $lon1);
                    
                    $a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon/2) * sin($dLon/2);
                    $c = 2 * asin(sqrt($a));
                    $jarak = $earthRadius * $c;

                    // Jika jarak <= 50 KM, masukkan ke daftar yang akan ditampilkan
                    if ($jarak <= 50) {
                        $barang->jarak = $jarak; // Menyimpan info jarak sementara (opsional)
                        $daftarBarang->push($barang);
                    }
                }
            } else {
                // User sudah login, tapi belum atur lokasi
                $butuhLokasi = true;
            }
        } else {
            // User belum login, minta login/atur lokasi
            $butuhLokasi = true;
        }

        return view('customer.home', compact('daftarBarang', 'banners', 'butuhLokasi'));
    }

    public function show($id)
    {
        $barang = Barang::findOrFail($id);
        return view('customer.barang-detail', compact('barang'));
    }
}