<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Barang;
use App\Models\FotoBarang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VendorFotoBarangController extends Controller
{
    /**
     * Upload foto tambahan (galeri) untuk produk vendor
     */
    public function upload(Request $request, $id)
    {
        $barang = Barang::where('vendor_id', Auth::id())->findOrFail($id);

        $request->validate([
            'fotos' => 'required|array|min:1',
            'fotos.*' => 'image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $cloudinaryUrl = env('CLOUDINARY_URL') ?: getenv('CLOUDINARY_URL');
        $cloudinary = $cloudinaryUrl ? new \Cloudinary\Cloudinary($cloudinaryUrl) : null;

        $uploadedCount = 0;
        foreach ($request->file('fotos') as $foto) {
            $fotoPath = null;
            if ($cloudinary) {
                try {
                    $upload = $cloudinary->uploadApi()->upload($foto->getRealPath(), [
                        'folder' => 'rentify/barang/galeri'
                    ]);
                    $fotoPath = $upload['secure_url'];
                } catch (\Exception $e) {
                    // Fallback ke penyimpanan lokal jika koneksi Cloudinary bermasalah
                    $filename = time() . '_' . uniqid() . '.' . $foto->getClientOriginalExtension();
                    $foto->move(public_path('uploads/barang/galeri'), $filename);
                    $fotoPath = 'uploads/barang/galeri/' . $filename;
                }
            } else {
                $filename = time() . '_' . uniqid() . '.' . $foto->getClientOriginalExtension();
                $foto->move(public_path('uploads/barang/galeri'), $filename);
                $fotoPath = 'uploads/barang/galeri/' . $filename;
            }

            if ($fotoPath) {
                FotoBarang::create([
                    'barang_id' => $barang->id,
                    'foto_path' => $fotoPath,
                ]);
                $uploadedCount++;
            }
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
     * Menghapus salah satu foto galeri
     */
    public function destroy($fotoId)
    {
        $foto = FotoBarang::with('barang')->findOrFail($fotoId);

        if (!$foto->barang || $foto->barang->vendor_id != Auth::id()) {
            abort(403, 'Anda tidak memiliki hak akses untuk menghapus foto ini.');
        }

        // Hapus file dari Cloudinary jika tersimpan di cloud
        if ($foto->foto_path && str_contains($foto->foto_path, 'cloudinary')) {
            try {
                $cloudinaryUrl = env('CLOUDINARY_URL') ?: getenv('CLOUDINARY_URL');
                if ($cloudinaryUrl) {
                    $cloudinary = new \Cloudinary\Cloudinary($cloudinaryUrl);
                    $path = (string) (parse_url($foto->foto_path, PHP_URL_PATH) ?? '');
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
                }
            } catch (\Exception $e) {
                // Abaikan error Cloudinary agar proses delete DB tetap berjalan
            }
        } elseif ($foto->foto_path && file_exists(public_path($foto->foto_path))) {
            @unlink(public_path($foto->foto_path));
        }

        $foto->delete();

        return back()->with('success', 'Foto galeri berhasil dihapus!');
    }
}
