<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Barang;
use App\Models\FotoBarang;
use App\Services\StorageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VendorFotoBarangController extends Controller
{
    /**
     * Upload foto tambahan (galeri) untuk produk vendor ke disk public
     */
    public function upload(Request $request, $id)
    {
        $barang = Barang::where('vendor_id', Auth::id())->findOrFail($id);

        $request->validate([
            'fotos' => 'required|array|min:1',
            'fotos.*' => 'image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $uploadedCount = 0;
        foreach ($request->file('fotos') as $foto) {
            $fotoPath = StorageService::storePublicFile($foto, 'barang/galeri');

            FotoBarang::create([
                'barang_id' => $barang->id,
                'foto_path' => $fotoPath,
            ]);
            $uploadedCount++;
        }

        return back()->with('success', "{$uploadedCount} foto galeri berhasil ditambahkan!");
    }

    /**
     * Menjadikan foto galeri sebagai cover utama barang
     */
    public function setCover(Request $request, $id, $fotoId)
    {
        $barang = Barang::where('vendor_id', Auth::id())->findOrFail($id);
        $foto = FotoBarang::where('barang_id', $barang->id)->findOrFail($fotoId);

        $oldCover = $barang->cover_photo;
        $barang->cover_photo = $foto->foto_path;
        $barang->save();

        // Jadikan cover lama sebagai foto galeri agar tidak hilang
        if ($oldCover && $oldCover !== $foto->foto_path) {
            $existsInGallery = FotoBarang::where('barang_id', $barang->id)->where('foto_path', $oldCover)->exists();
            if (!$existsInGallery) {
                FotoBarang::create([
                    'barang_id' => $barang->id,
                    'foto_path' => $oldCover,
                ]);
            }
        }

        // Hapus foto yang sekarang jadi cover dari galeri
        $foto->delete();

        return back()->with('success', 'Foto cover utama produk berhasil diperbarui!');
    }

    /**
     * Menghapus salah satu foto galeri dari disk public dan database
     */
    public function destroy($fotoId)
    {
        $foto = FotoBarang::with('barang')->findOrFail($fotoId);

        if (!$foto->barang || $foto->barang->vendor_id != Auth::id()) {
            abort(403, 'Anda tidak memiliki hak akses untuk menghapus foto ini.');
        }

        // Hapus berkas fisik lokal
        if ($foto->foto_path) {
            StorageService::deletePublicFile($foto->foto_path);
        }

        $foto->delete();

        return back()->with('success', 'Foto galeri berhasil dihapus!');
    }
}
