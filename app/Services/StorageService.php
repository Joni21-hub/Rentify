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
            return $path;
        }

        // Cek keberadaan berkas fisik pada disk publik lokal
        if (Storage::disk('public')->exists($path)) {
            return asset('storage/' . ltrim($path, '/'));
        }

        // Fallback untuk media lama yang belum disinkronkan ke lokal
        return asset('storage/' . $fallbackPlaceholder);
    }
}
