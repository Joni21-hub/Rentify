<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Barang;
use App\Models\Kategori;
use App\Models\FotoBarang;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use App\Services\StorageService;

class VendorBarangController extends Controller
{
    public function index(Request $request)
    {
        $vendorId = Auth::user()->id;
        $query = Barang::with('kategori')->where('vendor_id', $vendorId);

        if ($request->has('search') && $request->search != '') {
            $query->where('nama', 'like', '%' . $request->search . '%');
        }
        if ($request->has('status') && $request->status != '') {
            $query->where('status_barang', $request->status);
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
        // Cek Fallback Harga: Jika kategori Kos/Kamar hanya isi harga bulanan
        if (empty($request->harga_sewa_harian) && !empty($request->input('spesifikasi.harga_bulanan'))) {
            $request->merge([
                'harga_sewa_harian' => round(((float)$request->input('spesifikasi.harga_bulanan')) / 30)
            ]);
        }

        $request->validate([
            'kategori_id' => 'required|exists:kategoris,id',
            'nama' => 'required|string|max:255',
            'deskripsi' => 'required|string',
            'harga_sewa_harian' => 'required|numeric|min:0',
            'deposit' => 'required|numeric|min:0',
            'denda_per_hari' => 'required|numeric|min:0',
            'kondisi' => 'required|string',
            'stok_total' => 'required|integer|min:1',
            'alamat' => 'required|string',
            'latitude' => 'required',
            'longitude' => 'required',
            'fotos' => 'required|array|min:1',
            'fotos.*' => 'image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $vendorId = Auth::user()->id;
        /** @var array<int, \Illuminate\Http\UploadedFile> $fotos */
        $fotos = $request->file('fotos', []);

        // PENYIMPANAN MEDIA LOKAL MENGGUNAKAN STORAGE DISK PUBLIC
        $fileCover = $fotos[0] ?? null;
        if (!$fileCover instanceof \Illuminate\Http\UploadedFile) {
            return back()->withErrors(['fotos' => 'Foto cover wajib diunggah.']);
        }
        $pathCover = StorageService::storePublicFile($fileCover, 'barang');

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
            'is_delivery_supported' => $request->is_delivery_supported ? 1 : 0,
            'alamat' => $request->alamat,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'spesifikasi' => $request->input('spesifikasi', []),
        ]);

        // SIMPAN FOTO GALERI TAMBAHAN KE DISK PUBLIC
        if (count($fotos) > 1) {
            for ($i = 1; $i < count($fotos); $i++) {
                $foto = $fotos[$i];
                $pathGaleri = StorageService::storePublicFile($foto, 'barang/galeri');
                
                FotoBarang::create([
                    'barang_id' => $barang->id,
                    'foto_path' => $pathGaleri
                ]);
            }
        }

        return redirect()->route('vendor.barang.index')->with('success', 'Barang berhasil diajukan!');
    }

    public function show($id)
    {
        $barang = Barang::with('kategori')->where('vendor_id', Auth::user()->id)->findOrFail($id);
        $fotoTambahans = \App\Models\FotoBarang::where('barang_id', $barang->id)->get();
        return view('vendor.barang.show', compact('barang', 'fotoTambahans'));
    }

    public function edit($id)
    {
        $barang = Barang::where('vendor_id', Auth::user()->id)->findOrFail($id);
        $kategoris = Kategori::where('is_active', 1)->get();
        return view('vendor.barang.edit', compact('barang', 'kategoris'));
    }

    public function update(Request $request, $id)
    {
        $barang = Barang::where('vendor_id', Auth::user()->id)->findOrFail($id);

        if (empty($request->harga_sewa_harian) && !empty($request->input('spesifikasi.harga_bulanan'))) {
            $request->merge([
                'harga_sewa_harian' => round(((float)$request->input('spesifikasi.harga_bulanan')) / 30)
            ]);
        }

        $request->validate([
            'kategori_id' => 'required|exists:kategoris,id',
            'nama' => 'required|string|max:255',
            'deskripsi' => 'required|string',
            'harga_sewa_harian' => 'required|numeric|min:0',
            'deposit' => 'required|numeric|min:0',
            'denda_per_hari' => 'required|numeric|min:0',
            'kondisi' => 'required|string',
            'stok_total' => 'required|integer|min:1',
            'alamat' => 'required|string',
            'latitude' => 'required',
            'longitude' => 'required',
            'fotos' => 'nullable|array',
            'fotos.*' => 'image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $updateData = [
            'kategori_id' => $request->kategori_id,
            'nama' => $request->nama,
            'deskripsi' => $request->deskripsi,
            'harga_sewa_harian' => $request->harga_sewa_harian,
            'deposit' => $request->deposit,
            'denda_per_hari' => $request->denda_per_hari,
            'kondisi' => $request->kondisi,
            'stok_total' => $request->stok_total,
            'is_delivery_supported' => $request->is_delivery_supported ? 1 : 0,
            'alamat' => $request->alamat,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'spesifikasi' => $request->input('spesifikasi', []),
        ];

        /** @var array<int, \Illuminate\Http\UploadedFile> $fotos */
        $fotos = $request->file('fotos', []);
        if (!empty($fotos)) {
            $fileCover = $fotos[0] ?? null;
            if ($fileCover instanceof \Illuminate\Http\UploadedFile) {
                $pathCover = StorageService::storePublicFile($fileCover, 'barang');

                // Hapus cover photo lama jika ada
                StorageService::deletePublicFile($barang->cover_photo);
                $updateData['cover_photo'] = $pathCover;
            }

            if (count($fotos) > 1) {
                for ($i = 1; $i < count($fotos); $i++) {
                    $fotoItem = $fotos[$i] ?? null;
                    if ($fotoItem instanceof \Illuminate\Http\UploadedFile) {
                        $pathGaleri = StorageService::storePublicFile($fotoItem, 'barang/galeri');
                        FotoBarang::create([
                            'barang_id' => $barang->id,
                            'foto_path' => $pathGaleri
                        ]);
                    }
                }
            }
        }

        $barang->update($updateData);

        return redirect()->route('vendor.barang.index')->with('success', 'Barang berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $barang = Barang::where('vendor_id', Auth::user()->id)->findOrFail($id);
        
        // Hapus cover photo dari penyimpanan lokal
        if ($barang->cover_photo) {
            StorageService::deletePublicFile($barang->cover_photo);
        }

        $barang->delete();
        return redirect()->route('vendor.barang.index')->with('success', 'Barang dihapus.');
    }
}