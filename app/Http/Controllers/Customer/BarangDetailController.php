<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Barang;
use Illuminate\Http\Request;

class BarangDetailController extends Controller
{
    /**
     * Menampilkan halaman detail produk Rentify.
     * Dibuat fleksibel agar bisa membaca parameter {slug} ataupun {id} tanpa eror.
     */
    public function show($slug)
    {
        // KODE DIPERBAIKI: Menambahkan dengan relasi 'fotos' untuk mengambil galeri gambar tambahan
        $barang = Barang::with('fotos')
                        ->where('slug', $slug)
                        ->orWhere('id', $slug)
                        ->firstOrFail();

        // Mengembalikan ke tampilan halaman detail (customer/barang-detail.blade.php)
        return view('customer.barang-detail', compact('barang'));
    }
}