<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpKernel\Exception\HttpException;

class StorageService
{
    /**
     * Memeriksa hak akses tulis pada direktori penyimpanan publik.
     *
     * @throws HttpException
     */
    public static function assertWritable(): void
    {
        $publicStoragePath = storage_path('app/public');

        if (!file_exists($publicStoragePath)) {
            @mkdir($publicStoragePath, 0755, true);
        }

        if (!is_writable($publicStoragePath)) {
            Log::error('[StorageService] Write permission denied on public storage directory: ' . $publicStoragePath);
            throw new HttpException(503, 'Penyimpanan server tidak memiliki izin tulis. Silakan hubungi administrator.');
        }
    }

    /**
     * Menyimpan berkas yang diunggah ke disk public secara aman.
     *
     * @param UploadedFile $file
     * @param string $directory
     * @return string Relative path
     */
    public static function storePublicFile(UploadedFile $file, string $directory = 'uploads'): string
    {
        self::assertWritable();
        $path = $file->store($directory, 'public');
        if ($path === false) {
            throw new \RuntimeException("Failed to store file to directory: {$directory}");
        }
        return $path;
    }

    /**
     * Menghapus berkas dari disk public jika ada.
     *
     * @param string|null $path
     * @return bool
     */
    public static function deletePublicFile(?string $path): bool
    {
        if (empty($path)) {
            return false;
        }

        // Abaikan jika URL eksternal (misal Cloudinary lama)
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return false;
        }

        if (Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->delete($path);
        }

        return false;
    }

    /**
     * Mengembalikan URL publik berkas dengan mekanisme fallback otomatis jika berkas fisik tidak ditemukan.
     *
     * @param string|null $path
     * @param string $fallbackPlaceholder
     * @return string
     */
    public static function getPublicUrl(?string $path, string $fallbackPlaceholder = 'placeholders/placeholder-barang.png'): string
    {
        if (empty($path)) {
            return asset('storage/' . $fallbackPlaceholder);
        }

        // Jika path adalah URL lengkap eksternal (Cloudinary lama / CDN)
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            // Abaikan jika URL mock palsu
            if (!str_contains($path, 'res.cloudinary.com/rentify/')) {
                return $path;
            }
        }

        $clean = (string) preg_replace('#^(public/|storage/)+#', '', ltrim((string) $path, '/'));
        $baseName = basename($clean);

        // 1. Cek keberadaan berkas fisik pada public/
        if (file_exists(public_path($clean))) {
            return asset($clean);
        }

        // 2. Cek keberadaan berkas fisik pada public/storage/
        if (file_exists(public_path('storage/' . $clean))) {
            return asset('storage/' . $clean);
        }

        // 3. Cek keberadaan berkas fisik pada disk publik lokal
        if (Storage::disk('public')->exists($clean)) {
            return asset('storage/' . $clean);
        }

        // 4. Cek variasi subfolder barang atau galeri
        if (file_exists(public_path('barang/' . $baseName))) {
            return asset('barang/' . $baseName);
        }
        if (file_exists(public_path('barang/galeri/' . $baseName))) {
            return asset('barang/galeri/' . $baseName);
        }
        if (Storage::disk('public')->exists('barang/' . $baseName)) {
            return asset('storage/barang/' . $baseName);
        }
        if (Storage::disk('public')->exists('barang/galeri/' . $baseName)) {
            return asset('storage/barang/galeri/' . $baseName);
        }

        // Fallback untuk media lama yang belum disinkronkan ke lokal
        return asset('storage/' . $fallbackPlaceholder);
    }

    /**
     * Resolusi URL cover photo barang dengan validasi multi-path dan smart heuristic fallback
     *
     * @param \App\Models\Barang $barang
     * @return string
     */
    public static function getBarangImageUrl(\App\Models\Barang $barang): string
    {
        $raw = (string)($barang->cover_photo ?? '');

        // 1. Jika URL eksternal (Cloudinary, S3, CDN)
        if (str_starts_with($raw, 'http://') || str_starts_with($raw, 'https://')) {
            if (!str_contains($raw, 'res.cloudinary.com/rentify/')) {
                return $raw;
            }
        }

        // 2. Jika berkas fisik eksis di public atau disk storage
        if (!empty($raw)) {
            $clean = (string) preg_replace('#^(public/|storage/)+#', '', ltrim($raw, '/'));
            $baseName = basename($clean);

            if (file_exists(public_path($clean))) {
                return asset($clean);
            }
            if (file_exists(public_path('storage/' . $clean))) {
                return asset('storage/' . $clean);
            }
            if (Storage::disk('public')->exists($clean)) {
                return asset('storage/' . $clean);
            }
            if (file_exists(public_path('barang/' . $baseName))) {
                return asset('barang/' . $baseName);
            }
            if (Storage::disk('public')->exists('barang/' . $baseName)) {
                return asset('storage/barang/' . $baseName);
            }
        }

        // 3. Smart Heuristic Fallback berdasarkan Nama Barang
        $nama = strtolower((string)($barang->nama ?? ''));
        if (str_contains($nama, 'meja') || str_contains($nama, 'table')) {
            return asset('barang/4yEbGpYvTYYai8LZHVP3jFNNO5WBPUokdCJggGBy.png');
        }
        if (str_contains($nama, 'kursi') || str_contains($nama, 'kursu') || str_contains($nama, 'chair')) {
            return asset('barang/5I0op8pdywH1SopGwP36pKBhx6y44N6tbBVzcSPx.png');
        }
        if (str_contains($nama, 'kamera') || str_contains($nama, 'canon') || str_contains($nama, 'sony') || str_contains($nama, 'dslr') || str_contains($nama, 'mirrorless') || str_contains($nama, 'lensa')) {
            return asset('barang/76I5uIi6kWS9jygmdLXmrbnuOE51KZ9PDil8VVGR.jpg');
        }
        if (str_contains($nama, 'pancing') || str_contains($nama, 'joran') || str_contains($nama, 'reel')) {
            return asset('barang/6RT8a4qXbJ1pKq45xVOsawLlX59abVBqMkaEviPE.png');
        }
        if (str_contains($nama, 'bola') || str_contains($nama, 'voli') || str_contains($nama, 'futsal') || str_contains($nama, 'basket')) {
            return 'https://res.cloudinary.com/fnf8f1pm/image/upload/v1791090849/rentify/barang/epir3hg5l8m80lkoxbpb.jpg';
        }

        // 4. Kategori-based Fallback
        $kategoriNama = strtolower((string)($barang->kategori->nama ?? ''));
        if (str_contains($kategoriNama, 'outdoor') || str_contains($kategoriNama, 'camp')) {
            return asset('barang/4yEbGpYvTYYai8LZHVP3jFNNO5WBPUokdCJggGBy.png');
        }
        if (str_contains($kategoriNama, 'elektronik')) {
            return asset('barang/76I5uIi6kWS9jygmdLXmrbnuOE51KZ9PDil8VVGR.jpg');
        }
        if (str_contains($kategoriNama, 'have fun')) {
            return asset('barang/5I0op8pdywH1SopGwP36pKBhx6y44N6tbBVzcSPx.png');
        }

        // 5. Default Fallback Image
        return asset('storage/placeholders/placeholder-barang.png');
    }
}
