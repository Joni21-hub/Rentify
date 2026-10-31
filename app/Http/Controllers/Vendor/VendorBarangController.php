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
use App\Rules\ValidMedia;
use App\Services\AiMarketingService;

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
            'fotos.*' => ['required', new ValidMedia(102400, true)],
        ]);

        $vendorId = Auth::user()->id;
        /** @var array<int, \Illuminate\Http\UploadedFile> $fotos */
        $fotos = $request->file('fotos', []);

        // PENYIMPANAN MEDIA CLOUDINARY (100% HD) DENGAN FALLBACK DISK PUBLIK
        $fileCover = $fotos[0] ?? null;
        if (!$fileCover instanceof \Illuminate\Http\UploadedFile) {
            return back()->withErrors(['fotos' => 'Foto cover wajib diunggah.']);
        }
        $pathCover = StorageService::storeMedia($fileCover, 'barang');

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

        // SIMPAN FOTO GALERI TAMBAHAN KE CLOUDINARY / DISK PUBLIK
        if (count($fotos) > 1) {
            for ($i = 1; $i < count($fotos); $i++) {
                $foto = $fotos[$i];
                $pathGaleri = StorageService::storeMedia($foto, 'barang/galeri');
                
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
            'fotos.*' => ['required', new ValidMedia(102400, true)],
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
                $pathCover = StorageService::storeMedia($fileCover, 'barang');

                // Hapus cover photo lama jika ada dari Cloudinary / penyimpanan lokal
                if ($barang->cover_photo && $barang->cover_photo !== $pathCover) {
                    StorageService::deleteFile($barang->cover_photo);
                }
                $updateData['cover_photo'] = $pathCover;
            }

            if (count($fotos) > 1) {
                for ($i = 1; $i < count($fotos); $i++) {
                    $fotoItem = $fotos[$i] ?? null;
                    if ($fotoItem instanceof \Illuminate\Http\UploadedFile) {
                        $pathGaleri = StorageService::storeMedia($fotoItem, 'barang/galeri');
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
        
        // Hapus barang (Event deleting pada model Barang akan otomatis membersihkan
        // cover photo dan semua foto galeri terkait dari Cloudinary dan disk penyimpanan)
        $barang->delete();

        return redirect()->route('vendor.barang.index')->with('success', 'Barang dan seluruh fotonya berhasil dihapus.');
    }

    /**
     * Generate Copywriting Iklan & Promosi Produk untuk Vendor via AI / Smart Heuristic
     */
    public function generateMarketingCopywriting(Request $request, $id, AiMarketingService $aiMarketingService)
    {
        $barang = Barang::with('kategori')->where('vendor_id', Auth::user()->id)->findOrFail($id);

        $tone = $request->input('tone', 'fomo');
        $customNote = $request->input('custom_note');
        $diskon = $request->input('diskon');

        $productData = [
            'nama'        => $barang->nama,
            'kategori'    => $barang->kategori->nama ?? 'Umum',
            'harga_sewa'  => $barang->harga_sewa_harian,
            'deskripsi'   => $barang->deskripsi,
            'diskon'      => $diskon,
        ];

        $copywriting = $aiMarketingService->generateCopywriting($productData, 'all', $tone, $customNote);

        return response()->json([
            'success'     => true,
            'barang_nama' => $barang->nama,
            'data'        => $copywriting,
        ]);
    }
}