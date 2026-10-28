<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;

class ValidImage implements ValidationRule
{
    /**
     * Maximum allowed file size in kilobytes.
     * Default: 20480 KB (20 MB). 0 means no limit.
     */
    protected int $maxKilobytes;

    /**
     * Allowed image extensions.
     *
     * @var array<string>
     */
    protected array $allowedExtensions = [
        'jpg', 'jpeg', 'png', 'webp', 'heic', 'heif', 'avif', 'gif', 'bmp', 'tiff', 'tif',
        'svg', 'ico', 'jfif', 'raw', 'cr2', 'nef', 'arw', 'dng', 'psd', 'ai', 'eps'
    ];

    public function __construct(int $maxKilobytes = 20480)
    {
        $this->maxKilobytes = $maxKilobytes;
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!$value instanceof UploadedFile) {
            $fail("Berkas {$attribute} harus berupa berkas unggahan yang valid.");
            return;
        }

        if (!$value->isValid()) {
            $fail("Berkas {$attribute} gagal diunggah atau melebihi batas upload server.");
            return;
        }

        // Check file size
        if ($this->maxKilobytes > 0) {
            $sizeInKb = $value->getSize() / 1024;
            if ($sizeInKb > $this->maxKilobytes) {
                $maxMb = round($this->maxKilobytes / 1024, 1);
                $fail("Ukuran berkas {$attribute} maksimal adalah {$maxMb} MB.");
                return;
            }
        }

        // Check format
        $mime = strtolower((string) ($value->getMimeType() ?: ''));
        $clientMime = strtolower((string) ($value->getClientMimeType() ?: ''));
        $clientExt = strtolower((string) ($value->getClientOriginalExtension() ?: ''));
        $guessedExt = strtolower((string) ($value->guessExtension() ?: ''));

        $isImageMime = str_starts_with($mime, 'image/') || str_starts_with($clientMime, 'image/');
        $isImageExt = in_array($clientExt, $this->allowedExtensions, true)
            || in_array($guessedExt, $this->allowedExtensions, true);

        // Khusus format heic/heif/avif sering terdeteksi application/octet-stream oleh finfo
        $isHeicOrAvif = in_array($clientExt, ['heic', 'heif', 'avif', 'jfif', 'webp'], true)
            || str_contains($mime, 'heic') || str_contains($mime, 'heif') || str_contains($mime, 'avif');

        if (!$isImageMime && !$isImageExt && !$isHeicOrAvif) {
            $fail("Berkas {$attribute} harus berupa file gambar (JPG, JPEG, PNG, WEBP, HEIC, HEIF, AVIF, GIF, BMP, atau format gambar lainnya).");
        }
    }
}
