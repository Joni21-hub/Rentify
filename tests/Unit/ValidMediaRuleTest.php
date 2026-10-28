<?php

namespace Tests\Unit;

use App\Rules\ValidMedia;
use App\Services\StorageService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class ValidMediaRuleTest extends TestCase
{
    /**
     * Uji seluruh format gambar lolos validasi.
     */
    public function test_all_image_formats_pass_validation(): void
    {
        $rule = new ValidMedia(102400, true);
        $images = [
            'photo.jpg'  => 'image/jpeg',
            'photo.jpeg' => 'image/jpeg',
            'photo.png'  => 'image/png',
            'photo.webp' => 'image/webp',
            'photo.heic' => 'image/heic',
            'photo.heif' => 'application/octet-stream',
            'photo.avif' => 'image/avif',
            'photo.gif'  => 'image/gif',
            'photo.bmp'  => 'image/bmp',
            'photo.tiff' => 'image/tiff',
            'photo.raw'  => 'application/octet-stream',
            'photo.dng'  => 'image/x-adobe-dng',
        ];

        foreach ($images as $filename => $mime) {
            $file = UploadedFile::fake()->create($filename, 5000, $mime);
            $validator = Validator::make(['media' => $file], ['media' => $rule]);
            $this->assertTrue($validator->passes(), "Berkas gambar {$filename} ({$mime}) harus lulus validasi.");
        }
    }

    /**
     * Uji format video lolos validasi.
     */
    public function test_video_formats_pass_validation(): void
    {
        $rule = new ValidMedia(102400, true);
        $videos = [
            'video.mp4'  => 'video/mp4',
            'video.mov'  => 'video/quicktime',
            'video.webm' => 'video/webm',
            'video.mkv'  => 'video/x-matroska',
            'video.avi'  => 'video/x-msvideo',
        ];

        foreach ($videos as $filename => $mime) {
            $file = UploadedFile::fake()->create($filename, 15000, $mime);
            $validator = Validator::make(['media' => $file], ['media' => $rule]);
            $this->assertTrue($validator->passes(), "Berkas video {$filename} ({$mime}) harus lulus validasi.");
        }
    }

    /**
     * Uji berkas terlarang (non-media) ditolak demi keamanan sistem.
     */
    public function test_non_media_files_are_rejected(): void
    {
        $rule = new ValidMedia(102400, true);
        $invalidFiles = [
            'malicious.php' => 'application/x-php',
            'script.sh'     => 'text/x-shellscript',
            'document.pdf'  => 'application/pdf',
            'archive.zip'   => 'application/zip',
            'virus.exe'     => 'application/x-msdownload',
        ];

        foreach ($invalidFiles as $filename => $mime) {
            $file = UploadedFile::fake()->create($filename, 500, $mime);
            $validator = Validator::make(['media' => $file], ['media' => $rule]);
            $this->assertFalse($validator->passes(), "Berkas non-media {$filename} harus ditolak.");
        }
    }

    /**
     * Uji deteksi video pada StorageService::isVideoMedia.
     */
    public function test_storage_service_is_video_media_detection(): void
    {
        $this->assertTrue(StorageService::isVideoMedia('barang/demo.mp4'));
        $this->assertTrue(StorageService::isVideoMedia('barang/clip.mov'));
        $this->assertTrue(StorageService::isVideoMedia('barang/stream.webm'));
        $this->assertFalse(StorageService::isVideoMedia('barang/cover.webp'));
        $this->assertFalse(StorageService::isVideoMedia('barang/cover.jpg'));
        $this->assertFalse(StorageService::isVideoMedia('barang/photo.avif'));
        $this->assertFalse(StorageService::isVideoMedia(null));
    }
}
