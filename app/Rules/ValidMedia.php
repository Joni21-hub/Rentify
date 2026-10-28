<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;

class ValidMedia implements ValidationRule
{
    /**
     * Maximum allowed file size in kilobytes.
     * Default: 102400 KB (100 MB). 0 means no limit.
     */
    protected int $maxKilobytes;

    /**
     * Whether video files are allowed.
     */
    protected bool $allowVideo;

    /**
     * Allowed image extensions.
     *
     * @var array<string>
     */
    protected array $allowedImageExtensions = [
        'jpg', 'jpeg', 'png', 'webp', 'heic', 'heif', 'avif', 'gif', 'bmp', 'tiff', 'tif',
        'svg', 'ico', 'jfif', 'raw', 'cr2', 'nef', 'arw', 'dng', 'psd', 'ai', 'eps'
    ];

    /**
     * Allowed video extensions.
     *
     * @var array<string>
     */
    protected array $allowedVideoExtensions = [
        'mp4', 'mov', 'quicktime', 'webm', 'mkv', 'avi', 'wmv', 'flv', '3gp',
        '3g2', 'm4v', 'ts', 'mpeg', 'mpg', 'ogv', 'vob', 'm2ts', 'divx', 'rm', 'rmvb', 'asf', 'm4p', 'f4v'
    ];

    public function __construct(int $maxKilobytes = 102400, bool $allowVideo = true)
    {
        $this->maxKilobytes = $maxKilobytes;
        $this->allowVideo = $allowVideo;
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
        $isImageExt = in_array($clientExt, $this->allowedImageExtensions, true)
            || in_array($guessedExt, $this->allowedImageExtensions, true);

        // Khusus format heic/heif/avif sering terdeteksi application/octet-stream oleh finfo
        $isHeicOrAvif = in_array($clientExt, ['heic', 'heif', 'avif', 'jfif', 'webp'], true)
            || str_contains($mime, 'heic') || str_contains($mime, 'heif') || str_contains($mime, 'avif');

        $isImage = $isImageMime || $isImageExt || $isHeicOrAvif;

        $isVideoMime = str_starts_with($mime, 'video/') || str_starts_with($clientMime, 'video/');
        $isVideoExt = in_array($clientExt, $this->allowedVideoExtensions, true)
            || in_array($guessedExt, $this->allowedVideoExtensions, true);

        $isVideo = $isVideoMime || $isVideoExt;

        if ($this->allowVideo) {
            if (!$isImage && !$isVideo) {
                $fail("Berkas {$attribute} harus berupa gambar valid (JPG, PNG, WEBP, HEIC, AVIF, dll) atau video (MP4, MOV, WEBM, dll).");
            }
        } else {
            if (!$isImage) {
                $fail("Berkas {$attribute} harus berupa file gambar valid (JPG, JPEG, PNG, WEBP, HEIC, HEIF, AVIF, GIF, BMP, atau format gambar lainnya).");
            }
        }
    }
}
