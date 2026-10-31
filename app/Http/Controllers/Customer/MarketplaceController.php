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
        $keyword = trim($request->input('q', ''));
        $kategoriId = $request->input('kategori', $request->input('category'));
        $kategoriInput = $kategoriId;

        // 1. FILTER: Ambil barang disetujui dan vendor TIDAK suspended (konsisten dengan CustomerHomeController)
        $query = Barang::with(['vendor', 'kategori'])
            ->where('status_barang', 'disetujui') 
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

        if (!empty($kategoriInput)) {
            if (is_numeric($kategoriInput)) {
                $query->where('kategori_id', $kategoriInput);
            } else {
                $query->where(function($sub) use ($kategoriInput) {
                    $sub->whereHas('kategori', function($q) use ($kategoriInput) {
                        $q->where('nama', 'like', '%' . $kategoriInput . '%');
                    })->orWhere('nama', 'like', '%' . $kategoriInput . '%')
                      ->orWhere('deskripsi', 'like', '%' . $kategoriInput . '%');
                });
            }
        }

        $semuaBarang = $query->latest()->get();
        $filteredBarangs = collect();

        // 2. FILTER HYPERLOCAL (Rumus Haversine) - Sinkron penuh dengan session & profil Auth & Radius 5-50 KM
        $lat1 = session('user_latitude');
        $lon1 = session('user_longitude');

        if (Auth::check()) {
            $user = Auth::user();
            if ($user->latitude && $user->longitude) {
                $lat1 = $user->latitude;
                $lon1 = $user->longitude;
            }
        }

        $userRadius = session('user_radius');
        if (Auth::check() && Auth::user()->radius) {
            $userRadius = Auth::user()->radius;
        }
        $userRadius = max(5, min(50, (float) ($userRadius ?: 50)));

        if ($lat1 && $lon1) {
            $lat1 = (float) $lat1;
            $lon1 = (float) $lon1;

            foreach ($semuaBarang as $barang) {
                $lat2 = (float) ($barang->latitude ?: ($barang->vendor->latitude ?? 0));
                $lon2 = (float) ($barang->longitude ?: ($barang->vendor->longitude ?? 0));

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

                // Masukkan barang yang jaraknya <= radius pilihan customer
                if ($jarak <= $userRadius) {
                    $barang->jarak = $jarak; 
                    $filteredBarangs->push($barang);
                }
            }

            // Fallback: Jika pencarian spesifik (ada keyword) tidak ada dalam radius,
            // tetapi ada barang yang cocok di luar radius, tetap tampilkan agar customer tidak buntu
            if ($filteredBarangs->isEmpty() && !empty($keyword) && $semuaBarang->isNotEmpty()) {
                foreach ($semuaBarang as $barang) {
                    $lat2 = (float) ($barang->latitude ?: ($barang->vendor->latitude ?? 0));
                    $lon2 = (float) ($barang->longitude ?: ($barang->vendor->longitude ?? 0));
                    if ($lat2 && $lon2) {
                        $dLat = deg2rad($lat2 - $lat1);
                        $dLon = deg2rad($lon2 - $lon1);
                        $a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon/2) * sin($dLon/2);
                        $barang->jarak = $earthRadius * (2 * asin(sqrt($a)));
                    }
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
        $wishlistIds = Auth::check() 
            ? \App\Models\Wishlist::where('user_id', Auth::id())->pluck('barang_id')->flip()->toArray() 
            : [];

        return view('customer.search', compact('barangs', 'kategoris', 'keyword', 'kategoriId', 'wishlistIds', 'userRadius'));
    }

    /**
     * Halaman Khusus Kategori ala Shopee/Tokopedia
     */
    public function kategori(Request $request, $id)
    {
        // 1. Temukan kategori berdasarkan ID numerik atau nama/slug
        if (is_numeric($id)) {
            $kategori = Kategori::where('id', $id)->first();
        } else {
            $cleaned = (string) str_replace(['-', '_'], ' ', (string) $id);
            $slugClean = strtolower(trim($cleaned));
            $kategori = Kategori::whereRaw('LOWER(nama) = ?', [$slugClean])
                ->orWhereRaw('LOWER(nama) LIKE ?', ['%' . $slugClean . '%'])
                ->first();
        }

        if (!$kategori) {
            abort(404, 'Kategori tidak ditemukan');
        }

        $allKategoris = Kategori::where('is_active', 1)->get();
        $sort = $request->input('sort', 'terbaru');

        // 2. Query barang disetujui & vendor tidak suspended
        $query = Barang::with(['vendor', 'kategori', 'fotos'])
            ->where('status_barang', 'disetujui')
            ->where('kategori_id', $kategori->id)
            ->whereHas('vendor', function ($q) {
                $q->where('vendor_status', '!=', 'suspended')
                  ->orWhereNull('vendor_status');
            });

        if ($sort === 'harga_asc') {
            $query->orderBy('harga_sewa_harian', 'asc');
        } elseif ($sort === 'harga_desc') {
            $query->orderBy('harga_sewa_harian', 'desc');
        } else {
            $query->latest();
        }

        $semuaBarang = $query->get();
        $filteredBarangs = collect();

        // 3. Hyperlocal haversine formula
        $lat1 = session('user_latitude');
        $lon1 = session('user_longitude');

        if (Auth::check()) {
            $user = Auth::user();
            if ($user->latitude && $user->longitude) {
                $lat1 = $user->latitude;
                $lon1 = $user->longitude;
            }
        }

        $userRadius = session('user_radius');
        if (Auth::check() && Auth::user()->radius) {
            $userRadius = Auth::user()->radius;
        }
        $userRadius = max(5, min(50, (float) ($userRadius ?: 50)));
        $hasLokasi = false;

        if ($lat1 && $lon1) {
            $hasLokasi = true;
            $lat1 = (float) $lat1;
            $lon1 = (float) $lon1;
            $earthRadius = 6371;

            foreach ($semuaBarang as $barang) {
                $lat2 = (float) ($barang->latitude ?: ($barang->vendor->latitude ?? 0));
                $lon2 = (float) ($barang->longitude ?: ($barang->vendor->longitude ?? 0));

                if ($lat2 && $lon2 && ($lat2 != 0 || $lon2 != 0)) {
                    $dLat = deg2rad($lat2 - $lat1);
                    $dLon = deg2rad($lon2 - $lon1);
                    $a = sin($dLat / 2) * sin($dLat / 2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) * sin($dLon / 2);
                    $c = 2 * asin(sqrt($a));
                    $barang->jarak = $earthRadius * $c;
                }
                $filteredBarangs->push($barang);
            }

            if ($sort === 'terdekat') {
                $filteredBarangs = $filteredBarangs->sortBy(function ($item) {
                    return $item->jarak ?? 999999;
                })->values();
            }
        } else {
            $filteredBarangs = $semuaBarang;
        }

        // 4. Paginasi
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 16;
        $currentItems = $filteredBarangs->slice(($currentPage - 1) * $perPage, $perPage)->all();

        $barangs = new LengthAwarePaginator($currentItems, $filteredBarangs->count(), $perPage, $currentPage, [
            'path' => $request->url(),
            'query' => $request->query(),
        ]);

        $wishlistIds = Auth::check() 
            ? \App\Models\Wishlist::where('user_id', Auth::id())->pluck('barang_id')->flip()->toArray() 
            : [];

        return view('customer.kategori', compact('kategori', 'allKategoris', 'barangs', 'userRadius', 'hasLokasi', 'wishlistIds', 'sort'));
    }
}