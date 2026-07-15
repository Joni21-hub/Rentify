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
        $fotos = $request->file('fotos');

        // JALUR PINTAS PAMUNGKAS: Inisialisasi Mesin Asli Cloudinary
        $cloudinaryUrl = env('CLOUDINARY_URL') ?: getenv('CLOUDINARY_URL');
        $cloudinary = new \Cloudinary\Cloudinary($cloudinaryUrl);

        // UPLOAD COVER PHOTO KE CLOUDINARY
        $fileCover = $fotos[0];
        $uploadCover = $cloudinary->uploadApi()->upload($fileCover->getRealPath(), [
            'folder' => 'rentify/barang'
        ]);
        $pathCover = $uploadCover['secure_url'];

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
        ]);

        // UPLOAD FOTO GALERI TAMBAHAN KE CLOUDINARY
        if (count($fotos) > 1) {
            for ($i = 1; $i < count($fotos); $i++) {
                $foto = $fotos[$i];
                $uploadGaleri = $cloudinary->uploadApi()->upload($foto->getRealPath(), [
                    'folder' => 'rentify/barang/galeri'
                ]);
                
                FotoBarang::create([
                    'barang_id' => $barang->id,
                    'foto_path' => $uploadGaleri['secure_url']
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

    public function destroy($id)
    {
        $barang = Barang::where('vendor_id', Auth::user()->id)->findOrFail($id);
        
        if ($barang->cover_photo && str_contains($barang->cover_photo, 'cloudinary')) {
            try {
                $cloudinaryUrl = env('CLOUDINARY_URL') ?: getenv('CLOUDINARY_URL');
                $cloudinary = new \Cloudinary\Cloudinary($cloudinaryUrl);

                $path = parse_url($barang->cover_photo, PHP_URL_PATH);
                $pathSegments = explode('/', $path);
                $uploadIndex = array_search('upload', $pathSegments);

                if ($uploadIndex !== false) {
                    $slicedSegments = array_slice($pathSegments, $uploadIndex + 1);
                    if (isset($slicedSegments[0]) && preg_match('/^v\d+$/', $slicedSegments[0])) {
                        array_shift($slicedSegments);
                    }
                    $publicIdWithExt = implode('/', $slicedSegments);
                    $publicId = pathinfo($publicIdWithExt, PATHINFO_FILENAME);
                    $folderPath = pathinfo($publicIdWithExt, PATHINFO_DIRNAME);
                    $finalPublicId = $folderPath !== '.' ? $folderPath . '/' . $publicId : $publicId;

                    $cloudinary->uploadApi()->destroy($finalPublicId);
                }
            } catch (\Exception $e) {
                // Abaikan jika error hapus di cloud agar tidak menghambat hapus data di database
            }
        } elseif ($barang->cover_photo && file_exists(public_path(str_replace('public/', '', $barang->cover_photo)))) {
            @unlink(public_path(str_replace('public/', '', $barang->cover_photo)));
        }

        $barang->delete();
        return redirect()->route('vendor.barang.index')->with('success', 'Barang dihapus.');
    }
}