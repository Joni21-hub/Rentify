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

        if (Auth::check()) {
            $user = Auth::user();
            
            if ($user->latitude && $user->longitude) {
                // FILTER BANNED: Hanya ambil barang yang tokonya TIDAK di-suspend
                $semuaBarang = Barang::where('status_barang', 'disetujui')
                    ->whereHas('vendor', function ($query) {
                        $query->where('vendor_status', '!=', 'suspended')
                              ->orWhereNull('vendor_status'); // Jaga-jaga jika ada vendor tanpa status eksplisit
                    })
                    ->get();

                $lat1 = (float) $user->latitude;
                $lon1 = (float) $user->longitude;

                foreach ($semuaBarang as $barang) {
                    $lat2 = (float) $barang->latitude;
                    $lon2 = (float) $barang->longitude;

                    if (!$lat2 || !$lon2 || ($lat2 == 0 && $lon2 == 0)) {
                        continue;
                    }

                    $earthRadius = 6371; 
                    $dLat = deg2rad($lat2 - $lat1);
                    $dLon = deg2rad($lon2 - $lon1);
                    
                    $a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon/2) * sin($dLon/2);
                    $c = 2 * asin(sqrt($a));
                    $jarak = $earthRadius * $c;

                    if ($jarak <= 50) {
                        $barang->jarak = $jarak; 
                        $daftarBarang->push($barang);
                    }
                }
            } else {
                $butuhLokasi = true;
            }
        } else {
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