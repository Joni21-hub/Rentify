<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Barang; 
use App\Models\Banner; 
use App\Models\Kategori;
use Illuminate\Support\Facades\Auth;

class CustomerHomeController extends Controller
{
    public function index()
    {
        // 1. Jika belum login (Guest/Pengunjung), tampilkan halaman perkenalan web Rentify (Landing Page)
        if (!Auth::check()) {
            return view('customer.welcome');
        }

        $user = Auth::user();

        // 2. Jika user adalah admin, arahkan ke dashboard admin
        if ($user->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        // Saat mengakses beranda katalog customer, pastikan session active_role adalah customer
        if (session('active_role') !== 'customer') {
            session(['active_role' => 'customer']);
        }

        $lat1 = session('user_latitude');
        $lon1 = session('user_longitude');

        if ($user->latitude && $user->longitude) {
            $lat1 = $user->latitude;
            $lon1 = $user->longitude;
        }

        $banners = Banner::all();
        $kategoris = Kategori::where('is_active', 1)->get();
        $daftarBarang = collect(); 
        $hasLokasi = false;

        // Ambil semua barang disetujui dari vendor aktif
        $semuaBarang = Barang::with(['vendor', 'kategori'])
            ->where('status_barang', 'disetujui')
            ->whereHas('vendor', function ($query) {
                $query->where('vendor_status', '!=', 'suspended')
                      ->orWhereNull('vendor_status'); 
            })
            ->latest()
            ->get();

        // 2. Jika koordinat DITEMUKAN (baik dari tamu maupun member), proses perhitungan jarak
        if ($lat1 && $lon1) {
            $hasLokasi = true;
            $lat1 = (float) $lat1;
            $lon1 = (float) $lon1;

            foreach ($semuaBarang as $barang) {
                $lat2 = (float) ($barang->latitude ?? $barang->vendor->latitude ?? 0);
                $lon2 = (float) ($barang->longitude ?? $barang->vendor->longitude ?? 0);

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

            // Urutkan barang dari jarak terdekat ala Shopee/Tokopedia Nearby
            $daftarBarang = $daftarBarang->sortBy('jarak')->values();
        } else {
            $hasLokasi = false;
        }

        // Standar Shopee & Tokopedia:
        // Saat pengguna pertama kali klik link (belum pasang titik lokasi),
        // JANGAN kosongkan katalog! Tampilkan etalase barang rekomendasi terbaik agar marketplace terlihat aktif, menarik & terpercaya.
        $rekomendasiBarang = $semuaBarang;
        $butuhLokasi = !$hasLokasi;

        if (!$hasLokasi || $daftarBarang->isEmpty()) {
            $katalogTampil = $daftarBarang->isNotEmpty() ? $daftarBarang : $rekomendasiBarang;
        } else {
            $katalogTampil = $daftarBarang;
        }

        $wishlistIds = Auth::check() 
            ? \App\Models\Wishlist::where('user_id', Auth::id())->pluck('barang_id')->flip()->toArray() 
            : [];

        return view('customer.home', [
            'daftarBarang' => $katalogTampil,
            'banners' => $banners,
            'butuhLokasi' => $butuhLokasi,
            'hasLokasi' => $hasLokasi,
            'rekomendasiBarang' => $rekomendasiBarang,
            'kategoris' => $kategoris,
            'wishlistIds' => $wishlistIds
        ]);
    }

    /**
     * Halaman Pengenalan Rentify Publik (Dapat diakses langsung via /tentang)
     */
    public function about()
    {
        return view('customer.welcome');
    }

    // PERBAIKAN: Fungsi show kini mendukung SLUG, memblokir toko Banned, dan memakai logika lokasi Tamu/Member Anda
    public function show($slug)
    {
        // 1. Cari barang berdasarkan slug atau id
        $barang = Barang::with(['fotos', 'vendor', 'kategori', 'ulasans.user'])
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

        $fullyBookedDates = $barang->getFullyBookedDates();
        $bookedDatesInfo = $barang->getBookedDatesInfo();

        return view('customer.barang-detail', compact('barang', 'fullyBookedDates', 'bookedDatesInfo'));
    }
}