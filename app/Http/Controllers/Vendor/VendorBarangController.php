<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Barang;
use App\Models\Kategori;
use App\Models\FotoBarang;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class VendorBarangController extends Controller
{
    public function index(Request $request)
    {
        $vendorId = Auth::user()->id;
        $query = Barang::with('kategori')->where('vendor_id', $vendorId);

        // FITUR PENCARIAN & FILTER BERFUNGSI SEKARANG
        if ($request->has('search') && $request->search != '') {
            $query->where('nama', 'like', '%' . $request->search . '%');
        }
        if ($request->has('status') && $request->status != '') {
            $query->where('status_barang', $request->status); // Sesuaikan dengan nama kolom status kurasi Anda
        }

        $barangs = $query->latest()->get();
        return view('vendor.barang.index', compact('barangs'));
    }

    public function create()
    {
        $kategoris = Kategori::where('is_active', 1)->get();
        return view('vendor.barang.create', compact('kategoris'));
    }

   public function store(Request $request)
    {
        $request->validate([
            'kategori_id' => 'required|exists:kategoris,id',
            'nama' => 'required|string|max:255',
            'deskripsi' => 'required|string',
            'harga_sewa_harian' => 'required|numeric|min:0',
            'deposit' => 'required|numeric|min:0',
            'denda_per_hari' => 'required|numeric|min:0',
            'kondisi' => 'required|string',
            'stok_total' => 'required|integer|min:1',
            'alamat' => 'required|string', // Tambahan Validasi
            'latitude' => 'required',      // Tambahan Validasi
            'longitude' => 'required',     // Tambahan Validasi
            'fotos' => 'required|array|min:1',
            'fotos.*' => 'image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $vendorId = Auth::user()->id;
        $fotos = $request->file('fotos');

        $fileCover = $fotos[0];
        $namaCover = $fileCover->hashName();
        $fileCover->move(public_path('barang'), $namaCover);
        $pathCover = 'public/barang/' . $namaCover;

        $barang = Barang::create([
            'vendor_id' => $vendorId,
            'kategori_id' => $request->kategori_id,
            'nama' => $request->nama,
            'slug' => Str::slug($request->nama) . '-' . rand(1000, 9999),
            'deskripsi' => $request->deskripsi,
            'harga_sewa_harian' => $request->harga_sewa_harian,
            'deposit' => $request->deposit,
            'denda_per_hari' => $request->denda_per_hari,
            'kondisi' => $request->kondisi,
            'stok_total' => $request->stok_total,
            'status' => 'tersedia',
            'is_approved' => 0, 
            'cover_photo' => $pathCover,
            
            // SIMPAN DATA LOKASI KE DATABASE
            'is_delivery_supported' => $request->is_delivery_supported ? 1 : 0,
            'alamat' => $request->alamat,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
        ]);

        if (count($fotos) > 1) {
            for ($i = 1; $i < count($fotos); $i++) {
                $foto = $fotos[$i];
                $namaTambahan = $foto->hashName();
                $foto->move(public_path('barang/galeri'), $namaTambahan);
                
                FotoBarang::create([
                    'barang_id' => $barang->id,
                    'foto_path' => 'public/barang/galeri/' . $namaTambahan
                ]);
            }
        }

        return redirect()->route('vendor.barang.index')->with('success', 'Barang berhasil diajukan!');
    }

    // FITUR TOMBOL LIHAT DETAIL
    public function show($id)
    {
        // 1. Cari data barang berdasarkan ID dan pastikan itu milik vendor yang sedang login
        $barang = Barang::with('kategori')->where('vendor_id', Auth::user()->id)->findOrFail($id);
        
        // 2. Ambil juga foto-foto tambahan dari galeri (jika ada)
        $fotoTambahans = \App\Models\FotoBarang::where('barang_id', $barang->id)->get();

        // 3. Tampilkan ke halaman detail khusus vendor
        return view('vendor.barang.show', compact('barang', 'fotoTambahans'));
    }

    // FITUR TOMBOL EDIT (Mencegah Error BadMethodCallException)
    public function edit($id)
    {
        $barang = Barang::where('vendor_id', Auth::user()->id)->findOrFail($id);
        $kategoris = Kategori::where('is_active', 1)->get();
        // Catatan: Pastikan Anda sudah punya file resources/views/vendor/barang/edit.blade.php
        return view('vendor.barang.edit', compact('barang', 'kategoris'));
    }

    public function destroy($id)
    {
        $barang = Barang::where('vendor_id', Auth::user()->id)->findOrFail($id);
        $oldCover = str_replace('public/', '', $barang->cover_photo);
        if ($barang->cover_photo && file_exists(public_path($oldCover))) {
            unlink(public_path($oldCover));
        }
        $barang->delete();
        return redirect()->route('vendor.barang.index')->with('success', 'Barang dihapus.');
    }
}