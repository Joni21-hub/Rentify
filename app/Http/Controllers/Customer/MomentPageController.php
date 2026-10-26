<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Barang;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MomentPageController extends Controller
{
    /**
     * Tampilkan halaman feed Momen komunitas Rentify.
     */
    public function index(Request $request): View
    {
        $products = Barang::where(function ($q) {
            $q->where('status', 'tersedia')
              ->orWhere('status_barang', 'disetujui');
        })->select('id', 'nama', 'slug', 'harga_sewa_harian', 'stok_total', 'cover_photo')
          ->orderBy('nama', 'asc')
          ->limit(100)
          ->get();

        return view('customer.moments.index', compact('products'));
    }

    /**
     * Tampilkan antarmuka pembuat Momen interaktif (Kamera & Editor bergaya TikTok / IG Reels).
     */
    public function create(Request $request): View
    {
        $products = Barang::where(function ($q) {
            $q->where('status', 'tersedia')
              ->orWhere('status_barang', 'disetujui');
        })->select('id', 'nama', 'slug', 'harga_sewa_harian', 'stok_total', 'cover_photo')
          ->orderBy('nama', 'asc')
          ->limit(100)
          ->get();

        return view('customer.moments.create', compact('products'));
    }
}
