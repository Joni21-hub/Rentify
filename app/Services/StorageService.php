<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
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
     * Mengompresi berkas gambar secara otomatis menjadi WebP atau AVIF
     * untuk efisiensi penyimpanan dan optimasi performa web.
     *
     * @param UploadedFile $file
     * @param string $directory
     * @param string|null $targetFormat 'webp' | 'avif' (default null -> membaca config/env)
     * @param int|null $quality Kualitas kompresi (1-100, default 80)
     * @param int|null $maxDimension Batas maksimal lebar/tinggi pixel (default 1920)
     * @return string Relative path
     */
    public static function storePublicFile(
        UploadedFile $file,
        string $directory = 'uploads',
        ?string $targetFormat = null,
        ?int $quality = null,
        ?int $maxDimension = null
    ): string {
        self::assertWritable();

        if (self::isCompressibleImage($file)) {
            return self::compressAndStoreImage($file, $directory, $targetFormat, $quality, $maxDimension);
        }

        $path = $file->store($directory, 'public');
        if ($path === false) {
            throw new \RuntimeException("Failed to store file to directory: {$directory}");
        }
        return $path;
    }

    /**
     * Memeriksa apakah berkas merupakan format gambar raster yang dapat dikompresi.
     *
     * @param UploadedFile $file
     * @return bool
     */
    public static function isCompressibleImage(UploadedFile $file): bool
    {
        $mime = strtolower((string) ($file->getMimeType() ?: $file->getClientMimeType() ?: ''));
        $ext = strtolower((string) ($file->getClientOriginalExtension() ?: $file->guessExtension() ?: ''));

        // Format vector SVG tidak dikompresi menjadi raster statis
        if (str_contains($mime, 'svg') || $ext === 'svg') {
            return false;
        }

        $compressibleExtensions = [
            'jpg', 'jpeg', 'png', 'webp', 'avif', 'bmp', 'tiff', 'tif', 'jfif', 'heic', 'heif'
        ];

        return str_starts_with($mime, 'image/') || in_array($ext, $compressibleExtensions, true);
    }

    /**
     * Memeriksa apakah berkas atau path merupakan video (MP4, MOV, WEBM, dll).
     *
     * @param string|UploadedFile|null $pathOrFile
     * @return bool
     */
    public static function isVideoMedia(string|UploadedFile|null $pathOrFile): bool
    {
        if (empty($pathOrFile)) {
            return false;
        }

        $videoExtensions = [
            'mp4', 'mov', 'quicktime', 'webm', 'mkv', 'avi', 'wmv', 'flv', '3gp', 'm4v', 'ts', 'ogv'
        ];

        if ($pathOrFile instanceof UploadedFile) {
            $mime = strtolower((string) ($pathOrFile->getMimeType() ?: $pathOrFile->getClientMimeType() ?: ''));
            $ext = strtolower((string) ($pathOrFile->getClientOriginalExtension() ?: $pathOrFile->guessExtension() ?: ''));
            return str_starts_with($mime, 'video/') || in_array($ext, $videoExtensions, true);
        }

        $clean = strtolower((string) parse_url((string) $pathOrFile, PHP_URL_PATH));
        $ext = pathinfo($clean, PATHINFO_EXTENSION);
        return in_array($ext, $videoExtensions, true);
    }

    /**
     * Mengompresi berkas gambar ke format WebP atau AVIF dan menyimpannya ke disk publik.
     *
     * @param UploadedFile $file
     * @param string $directory
     * @param string|null $targetFormat
     * @param int|null $quality
     * @param int|null $maxDimension
     * @return string
     */
    public static function compressAndStoreImage(
        UploadedFile $file,
        string $directory = 'uploads',
        ?string $targetFormat = null,
        ?int $quality = null,
        ?int $maxDimension = null
    ): string {
        $format = strtolower($targetFormat ?: (string) config('filesystems.image_compression_format', env('IMAGE_COMPRESSION_FORMAT', 'webp')));
        if (!in_array($format, ['webp', 'avif'], true)) {
            $format = 'webp';
        }

        $compressionQuality = $quality ?: (int) config('filesystems.image_compression_quality', env('IMAGE_COMPRESSION_QUALITY', 85));
        $maxDim = $maxDimension ?: (int) config('filesystems.image_max_dimension', env('IMAGE_MAX_DIMENSION', 1920));

        try {
            $manager = new ImageManager(new Driver());
            $image = $manager->read($file->getRealPath());

            $width = $image->width();
            $height = $image->height();

            // Jika berkas sudah berformat WebP dan dimensinya tidak melebihi batas maksimal,
            // simpan langsung tanpa re-encoding untuk menghindari "generation loss" (burik akibat kompresi ganda)
            $ext = strtolower((string) ($file->getClientOriginalExtension() ?: $file->guessExtension() ?: ''));
            if ($ext === 'webp' && $format === 'webp' && ($maxDim <= 0 || ($width <= $maxDim && $height <= $maxDim))) {
                $filename = Str::random(40) . '.webp';
                $cleanDir = trim($directory, '/\\');
                $relativePath = $cleanDir ? $cleanDir . '/' . $filename : $filename;
                $fileContent = file_get_contents($file->getRealPath());
                if ($fileContent !== false) {
                    Storage::disk('public')->put($relativePath, $fileContent);
                    return $relativePath;
                }
            }

            // Skalakan dimensi jika melebihi batas (menjaga rasio aspek asli)
            if ($maxDim > 0 && ($width > $maxDim || $height > $maxDim)) {
                $image->scaleDown($maxDim, $maxDim);
            }

            // Encode ke format target dengan kualitas 85% HD
            if ($format === 'avif' && function_exists('imageavif')) {
                $encoded = $image->toAvif($compressionQuality);
                $fileExt = 'avif';
            } else {
                $encoded = $image->toWebp($compressionQuality);
                $fileExt = 'webp';
            }

            $filename = Str::random(40) . '.' . $fileExt;
            $cleanDir = trim($directory, '/\\');
            $relativePath = $cleanDir ? $cleanDir . '/' . $filename : $filename;

            Storage::disk('public')->put($relativePath, (string) $encoded);

            return $relativePath;
        } catch (\Throwable $e) {
            Log::warning("[StorageService] Kompresi gambar ke {$format} gagal, fallback ke penyimpanan standar: " . $e->getMessage());

            $fallbackPath = $file->store($directory, 'public');
            if ($fallbackPath === false) {
                throw new \RuntimeException("Gagal menyimpan berkas ke direktori: {$directory}");
            }
            return $fallbackPath;
        }
    }

    /**
     * Menghapus berkas dari Cloudinary atau disk publik lokal.
     *
     * @param string|null $pathOrUrl
     * @return bool
     */
    public static function deleteFile(?string $pathOrUrl): bool
    {
        if (empty($pathOrUrl)) {
            return false;
        }

        // 1. Jika URL Cloudinary atau format public_id Cloudinary
        if (str_contains($pathOrUrl, 'cloudinary.com') || str_starts_with($pathOrUrl, 'rentify/')) {
            return self::deleteFromCloudinary($pathOrUrl);
        }

        // 2. Jika berkas di disk penyimpanan lokal
        return self::deleteLocalFile($pathOrUrl);
    }

    /**
     * Menghapus berkas dari disk public jika ada.
     * Alias backward-compatible untuk deleteFile.
     *
     * @param string|null $path
     * @return bool
     */
    public static function deletePublicFile(?string $path): bool
    {
        return self::deleteFile($path);
    }

    /**
     * Menghapus berkas dari disk fisik lokal.
     *
     * @param string|null $path
     * @return bool
     */
    public static function deleteLocalFile(?string $path): bool
    {
        if (empty($path)) {
            return false;
        }

        // Abaikan jika URL eksternal
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return false;
        }

        $clean = (string) preg_replace('#^(public/|storage/)+#', '', ltrim($path, '/'));

        $deleted = false;
        if (Storage::disk('public')->exists($clean)) {
            $deleted = Storage::disk('public')->delete($clean);
        }

        if (file_exists(public_path($clean))) {
            @unlink(public_path($clean));
            $deleted = true;
        }

        if (file_exists(public_path('storage/' . $clean))) {
            @unlink(public_path('storage/' . $clean));
            $deleted = true;
        }

        return $deleted;
    }

    /**
     * Mengekstrak Public ID Cloudinary dari URL lengkap atau string path.
     *
     * @param string|null $urlOrPublicId
     * @return string|null
     */
    public static function extractCloudinaryPublicId(?string $urlOrPublicId): ?string
    {
        if (empty($urlOrPublicId)) {
            return null;
        }

        // Jika sudah berupa public_id tanpa skema URL HTTP/HTTPS
        if (!str_starts_with($urlOrPublicId, 'http://') && !str_starts_with($urlOrPublicId, 'https://')) {
            return preg_replace('/\.[^.\/]+$/', '', ltrim($urlOrPublicId, '/'));
        }

        if (!str_contains($urlOrPublicId, 'cloudinary.com')) {
            return null;
        }

        $path = (string) (parse_url($urlOrPublicId, PHP_URL_PATH) ?? '');
        $segments = explode('/', trim($path, '/'));
        $uploadIndex = array_search('upload', $segments);
        if ($uploadIndex === false) {
            return null;
        }

        $sliced = array_slice($segments, $uploadIndex + 1);

        // Lewati segmen transformasi dan nomor versi (v12345678)
        while (!empty($sliced)) {
            $first = $sliced[0];
            if (preg_match('/^v\d+$/', $first)) {
                array_shift($sliced);
                break;
            }
            if (str_contains($first, ',') || str_starts_with($first, 'c_') || str_starts_with($first, 'f_') || str_starts_with($first, 'q_') || str_starts_with($first, 'w_')) {
                array_shift($sliced);
                continue;
            }
            break;
        }

        if (empty($sliced)) {
            return null;
        }

        $publicIdWithExt = implode('/', $sliced);
        $dir = pathinfo($publicIdWithExt, PATHINFO_DIRNAME);
        $filename = pathinfo($publicIdWithExt, PATHINFO_FILENAME);

        return $dir !== '.' ? $dir . '/' . $filename : $filename;
    }

    /**
     * Menghapus aset dari Cloudinary berdasarkan URL atau Public ID.
     *
     * @param string|null $urlOrPublicId
     * @return bool
     */
    public static function deleteFromCloudinary(?string $urlOrPublicId): bool
    {
        $publicId = self::extractCloudinaryPublicId($urlOrPublicId);
        if (!$publicId) {
            return false;
        }

        $cloudinaryUrl = env('CLOUDINARY_URL') ?: getenv('CLOUDINARY_URL');
        if (!$cloudinaryUrl) {
            Log::warning("[StorageService] CLOUDINARY_URL tidak tersedia untuk menghapus aset: {$publicId}");
            return false;
        }

        try {
            $cloudinary = new \Cloudinary\Cloudinary($cloudinaryUrl);
            $isVideo = self::isVideoMedia($urlOrPublicId);
            $options = $isVideo ? ['resource_type' => 'video'] : [];

            $res = $cloudinary->uploadApi()->destroy($publicId, $options);

            // Jika tidak ditemukan dengan type image, coba sebagai video (atau sebaliknya)
            if (($res['result'] ?? '') === 'not found' && !$isVideo) {
                $res = $cloudinary->uploadApi()->destroy($publicId, ['resource_type' => 'video']);
            }

            $success = ($res['result'] ?? '') === 'ok';
            if ($success) {
                Log::info("[StorageService] Berhasil menghapus aset dari Cloudinary: {$publicId}");
            } else {
                Log::notice("[StorageService] Cloudinary destroy response ({$publicId}): " . ($res['result'] ?? 'unknown'));
            }

            return $success;
        } catch (\Throwable $e) {
            Log::error("[StorageService] Gagal menghapus aset Cloudinary ({$publicId}): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Mengunggah berkas ke Cloudinary dengan kualitas visual 100% HD.
     *
     * @param UploadedFile $file
     * @param string $folder
     * @param array $options
     * @return string|null Secure URL berkas di Cloudinary
     */
    public static function uploadToCloudinary(
        UploadedFile $file,
        string $folder = 'rentify/barang',
        array $options = []
    ): ?string {
        $cloudinaryUrl = env('CLOUDINARY_URL') ?: getenv('CLOUDINARY_URL');
        if (!$cloudinaryUrl) {
            return null;
        }

        try {
            $cloudinary = new \Cloudinary\Cloudinary($cloudinaryUrl);
            $isVideo = self::isVideoMedia($file);
            $qualityConfig = (int) config('filesystems.image_compression_quality', env('IMAGE_COMPRESSION_QUALITY', 100));

            $defaultOptions = [
                'folder'        => $folder,
                'resource_type' => $isVideo ? 'video' : 'image',
            ];

            // 100% HD Quality: jika kualitas >= 100, gunakan preset auto:best tanpa penurunan ketajaman
            if (!$isVideo) {
                $defaultOptions['transformation'] = [
                    'quality'      => ($qualityConfig >= 100) ? 'auto:best' : (string) $qualityConfig,
                    'fetch_format' => 'auto',
                ];
            }

            $mergedOptions = array_merge($defaultOptions, $options);

            if ($isVideo && method_exists($cloudinary->uploadApi(), 'uploadLarge')) {
                $uploadResult = $cloudinary->uploadApi()->uploadLarge($file->getRealPath(), $mergedOptions);
            } else {
                $uploadResult = $cloudinary->uploadApi()->upload($file->getRealPath(), $mergedOptions);
            }

            return $uploadResult['secure_url'] ?? null;
        } catch (\Throwable $e) {
            Log::warning("[StorageService] Cloudinary upload to {$folder} failed: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Menyimpan berkas media utama ke Cloudinary jika tersedia,
     * dengan fallback otomatis ke penyimpanan disk publik lokal.
     *
     * @param UploadedFile $file
     * @param string $directory Subfolder tujuan (misal 'barang', 'barang/galeri')
     * @return string URL Cloudinary atau relative path disk publik
     */
    public static function storeMedia(UploadedFile $file, string $directory = 'barang'): string
    {
        // 1. Coba upload ke Cloudinary terlebih dahulu
        $folder = 'rentify/' . trim($directory, '/\\');
        $cloudUrl = self::uploadToCloudinary($file, $folder);
        if ($cloudUrl) {
            return $cloudUrl;
        }

        // 2. Fallback ke disk publik lokal dengan kompresi HD
        return self::storePublicFile($file, $directory);
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
                return self::optimizeCloudinaryUrl($path);
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
                return self::optimizeCloudinaryUrl($raw);
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

    /**
     * Mengoptimasi URL Cloudinary dengan format otomatis dan kualitas 100% HD
     * untuk menjaga ketajaman visual maksimal tanpa kompresi berlebihan (burik).
     *
     * @param string|null $url
     * @return string
     */
    public static function optimizeCloudinaryUrl(?string $url): string
    {
        if (empty($url) || !str_contains($url, 'res.cloudinary.com')) {
            return (string) $url;
        }

        // Jika sudah memiliki parameter optimasi format/kualitas, kembalikan apa adanya
        if (str_contains($url, 'q_') || str_contains($url, 'f_auto') || str_contains($url, 'q_auto')) {
            return $url;
        }

        $quality = (int) config('filesystems.image_compression_quality', env('IMAGE_COMPRESSION_QUALITY', 100));
        $transformation = ($quality >= 100) ? 'f_auto,q_auto:best' : "f_auto,q_{$quality}";

        // Sisipkan parameter optimasi Cloudinary sesuai konfigurasi HD
        if (preg_match('#/(image|video)/upload/#', $url)) {
            return preg_replace('#/(image|video)/upload/#', "/$1/upload/{$transformation}/", $url, 1) ?? $url;
        }

        return $url;
    }
}
