<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Barang;
use App\Models\Kategori;
use Illuminate\Support\Facades\Auth;
use Illuminate\Pagination\LengthAwarePaginator;

class MarketplaceController extends Controller
{
    public function search(Request $request)
    {
        $keyword = $request->input('q');
        $kategoriId = $request->input('kategori');

        // 1. FILTER KETAT: Hanya ambil barang disetujui, stok > 0, dan vendor TIDAK suspended
        $query = Barang::with(['vendor', 'kategori'])
            ->where('status_barang', 'disetujui') 
            ->where('stok_total', '>', 0)
            ->whereHas('vendor', function ($q) {
                $q->where('vendor_status', '!=', 'suspended')
                  ->orWhereNull('vendor_status');
            });

        if (!empty($keyword)) {
            $query->where(function($q) use ($keyword) {
                $q->where('nama', 'like', '%' . $keyword . '%')
                  ->orWhere('deskripsi', 'like', '%' . $keyword . '%')
                  ->orWhereHas('vendor', function($v) use ($keyword) {
                      $v->where('name', 'like', '%' . $keyword . '%')
                        ->orWhere('vendor_name', 'like', '%' . $keyword . '%');
                  });
            });
        }

        if (!empty($kategoriId)) {
            $query->where('kategori_id', $kategoriId);
        }

        $semuaBarang = $query->latest()->get();
        $filteredBarangs = collect();

        // 2. FILTER HYPERLOCAL 50 KM (Rumus Haversine)
        if (Auth::check() && Auth::user()->latitude && Auth::user()->longitude) {
            $lat1 = (float) Auth::user()->latitude;
            $lon1 = (float) Auth::user()->longitude;

            foreach ($semuaBarang as $barang) {
                $lat2 = (float) ($barang->latitude ?? $barang->vendor->latitude ?? 0);
                $lon2 = (float) ($barang->longitude ?? $barang->vendor->longitude ?? 0);

                // Lewati jika toko tidak punya koordinat
                if (!$lat2 || !$lon2 || ($lat2 == 0 && $lon2 == 0)) {
                    continue; 
                }

                $earthRadius = 6371; // Radius Bumi dalam KM
                $dLat = deg2rad($lat2 - $lat1);
                $dLon = deg2rad($lon2 - $lon1);

                $a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon/2) * sin($dLon/2);
                $c = 2 * asin(sqrt($a));
                $jarak = $earthRadius * $c;

                // Hanya masukkan barang yang jaraknya <= 50 KM dari customer
                if ($jarak <= 50) {
                    $barang->jarak = $jarak; // Simpan info jarak untuk ditampilkan di layar
                    $filteredBarangs->push($barang);
                }
            }
        } else {
            // Jika customer belum atur lokasi, tampilkan apa adanya
            $filteredBarangs = $semuaBarang;
        }

        // 3. Paginasi Manual agar fitur ->links() di tampilan tidak eror
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 12;
        $currentItems = $filteredBarangs->slice(($currentPage - 1) * $perPage, $perPage)->all();
        
        $barangs = new LengthAwarePaginator($currentItems, $filteredBarangs->count(), $perPage, $currentPage, [
            'path' => $request->url(),
            'query' => $request->query(),
        ]);

        $kategoris = Kategori::all();

        return view('customer.search', compact('barangs', 'kategoris', 'keyword', 'kategoriId'));
    }
}