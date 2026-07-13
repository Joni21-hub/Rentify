<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Barang; 
use App\Models\Banner; 

class CustomerHomeController extends Controller
{
    // 1. Fungsi untuk menampilkan halaman utama (Katalog Semua Barang & Banner)
   public function index()
    {
        // PERBAIKAN: Kita pakai kolom 'status_barang' yang memang sudah terbukti berubah menjadi 'disetujui' di database Anda
        $daftarBarang = Barang::where('status_barang', 'disetujui')->get(); 

        // Menarik data banner promosi
        $banners = Banner::all(); 

        return view('customer.home', compact('daftarBarang', 'banners'));
    }

    // 2. Fungsi untuk menampilkan halaman Detail Barang berdasarkan ID
    public function show($id)
    {
        // Mencari data barang yang diklik berdasarkan ID. Jika tidak ada, otomatis error 404.
        $barang = Barang::findOrFail($id);

        // Mengarahkan ke file resources/views/customer/barang-detail.blade.php
        return view('customer.barang-detail', compact('barang'));
    }
}