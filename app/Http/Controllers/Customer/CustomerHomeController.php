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
        $daftarBarang = collect(); 
        $butuhLokasi = false;

        // 1. Cek Lokasi: Ambil dari Session (Tamu) atau Database (Member)
        $lat1 = session('user_latitude');
        $lon1 = session('user_longitude');

        if (Auth::check()) {
            $user = Auth::user();
            // Jika member punya lokasi di profil, utamakan lokasi profilnya
            if ($user->latitude && $user->longitude) {
                $lat1 = $user->latitude;
                $lon1 = $user->longitude;
            }
        }

        // 2. Jika koordinat DITEMUKAN (baik dari tamu maupun member), proses pencarian barang
        if ($lat1 && $lon1) {
            $semuaBarang = Barang::where('status_barang', 'disetujui')
                ->whereHas('vendor', function ($query) {
                    $query->where('vendor_status', '!=', 'suspended')
                          ->orWhereNull('vendor_status'); 
                })
                ->get();

            $lat1 = (float) $lat1;
            $lon1 = (float) $lon1;

            foreach ($semuaBarang as $barang) {
                $lat2 = (float) $barang->latitude;
                $lon2 = (float) $barang->longitude;

                if (!$lat2 || !$lon2 || ($lat2 == 0 && $lon2 == 0)) {
                    continue; // Lewati barang yang tokonya tidak pasang pin lokasi
                }

                $earthRadius = 6371; // Radius Bumi dalam KM
                $dLat = deg2rad($lat2 - $lat1);
                $dLon = deg2rad($lon2 - $lon1);
                
                $a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon/2) * sin($dLon/2);
                $c = 2 * asin(sqrt($a));
                $jarak = $earthRadius * $c;

                // Tampilkan barang yang berjarak maksimal 50 KM
                if ($jarak <= 50) {
                    $barang->jarak = $jarak; 
                    $daftarBarang->push($barang);
                }
            }
        } else {
            // 3. Jika sama sekali tidak ada data koordinat, munculkan Banner "Tentukan Lokasimu"
            $butuhLokasi = true;
        }

        return view('customer.home', compact('daftarBarang', 'banners', 'butuhLokasi'));
    }

    // PERBAIKAN: Fungsi show kini mendukung SLUG, memblokir toko Banned, dan memakai logika lokasi Tamu/Member Anda
    public function show($slug)
    {
        // 1. Cari barang berdasarkan slug atau id
        $barang = Barang::with(['fotos', 'vendor', 'kategori'])
                        ->where(function ($query) use ($slug) {
                            $query->where('slug', $slug)
                                  ->orWhere('id', $slug);
                        })
                        ->first();

        // 2. Jika barang tidak ditemukan
        if (!$barang) {
            return redirect()->route('customer.home')->with('error', '⚠️ Barang yang Anda cari tidak ditemukan di sistem.');
        }

        // 3. FILTER TOKO BANNED: Cegah bypass dari URL
        $statusToko = strtolower($barang->vendor->vendor_status ?? '');
        if ($statusToko === 'suspended') {
            return redirect()->route('customer.home')->with('error', '⚠️ Mohon maaf, barang "' . $barang->nama . '" tidak dapat diakses karena toko pemiliknya sedang ditangguhkan/diblokir sementara oleh Admin.');
        }

        // 4. Hitung Jarak (Mendukung Guest Session persis seperti fungsi index Anda)
        $lat1 = session('user_latitude');
        $lon1 = session('user_longitude');

        if (Auth::check()) {
            $user = Auth::user();
            if ($user->latitude && $user->longitude) {
                $lat1 = $user->latitude;
                $lon1 = $user->longitude;
            }
        }

        if ($lat1 && $lon1) {
            $lat2 = (float) ($barang->latitude ?? $barang->vendor->latitude ?? 0);
            $lon2 = (float) ($barang->longitude ?? $barang->vendor->longitude ?? 0);

            if ($lat2 && $lon2 && ($lat2 != 0 || $lon2 != 0)) {
                $earthRadius = 6371; 
                $dLat = deg2rad($lat2 - (float)$lat1);
                $dLon = deg2rad($lon2 - (float)$lon1);

                $a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad((float)$lat1)) * cos(deg2rad($lat2)) * sin($dLon/2) * sin($dLon/2);
                $c = 2 * asin(sqrt($a));
                $barang->jarak = $earthRadius * $c;
            }
        }

        return view('customer.barang-detail', compact('barang'));
    }
}