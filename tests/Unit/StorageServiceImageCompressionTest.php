<?php

namespace Tests\Unit;

use App\Services\StorageService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Tests\TestCase;

class StorageServiceImageCompressionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_compress_and_store_image_converts_jpeg_to_webp(): void
    {
        // Buat file gambar temporary JPEG
        $temp = tempnam(sys_get_temp_dir(), 'test_jpeg');
        $gd = imagecreatetruecolor(800, 600);
        $color = (int) imagecolorallocate($gd, 100, 150, 200);
        imagefilledrectangle($gd, 0, 0, 800, 600, $color);
        imagejpeg($gd, $temp, 90);
        imagedestroy($gd);

        $uploaded = new UploadedFile($temp, 'sample.jpg', 'image/jpeg', null, true);

        $savedPath = StorageService::storePublicFile($uploaded, 'barang', 'webp', 80, 1920);

        $this->assertNotEmpty($savedPath);
        $this->assertStringEndsWith('.webp', $savedPath);
        $this->assertTrue(Storage::disk('public')->exists($savedPath));

        // Verifikasi hasil kompresi dapat dibaca kembali
        $content = Storage::disk('public')->get($savedPath);
        $manager = new ImageManager(new Driver());
        $readImage = $manager->read($content);

        $this->assertEquals(800, $readImage->width());
        $this->assertEquals(600, $readImage->height());

        @unlink($temp);
    }

    public function test_compress_and_store_image_converts_to_avif(): void
    {
        if (!function_exists('imageavif')) {
            $this->markTestSkipped('GD imageavif is not available on this environment');
        }

        $temp = tempnam(sys_get_temp_dir(), 'test_avif');
        $gd = imagecreatetruecolor(400, 300);
        $color = (int) imagecolorallocate($gd, 50, 120, 220);
        imagefilledrectangle($gd, 0, 0, 400, 300, $color);
        imagejpeg($gd, $temp, 85);
        imagedestroy($gd);

        $uploaded = new UploadedFile($temp, 'photo.jpg', 'image/jpeg', null, true);

        $savedPath = StorageService::storePublicFile($uploaded, 'barang', 'avif', 80, 1920);

        $this->assertNotEmpty($savedPath);
        $this->assertStringEndsWith('.avif', $savedPath);
        $this->assertTrue(Storage::disk('public')->exists($savedPath));

        @unlink($temp);
    }

    public function test_compress_and_store_image_scales_down_large_dimensions(): void
    {
        // Buat gambar besar 3000x2000
        $temp = tempnam(sys_get_temp_dir(), 'test_large');
        $gd = imagecreatetruecolor(3000, 2000);
        imagejpeg($gd, $temp, 80);
        imagedestroy($gd);

        $uploaded = new UploadedFile($temp, 'huge_photo.jpg', 'image/jpeg', null, true);

        // Batasi dimensi maksimal ke 1200px
        $savedPath = StorageService::storePublicFile($uploaded, 'barang', 'webp', 80, 1200);

        $this->assertNotEmpty($savedPath);
        $content = Storage::disk('public')->get($savedPath);

        $manager = new ImageManager(new Driver());
        $readImage = $manager->read($content);

        // Lebar harus di-scale down ke 1200 dan tinggi ke 800 (aspek rasio 3:2 terjaga)
        $this->assertEquals(1200, $readImage->width());
        $this->assertEquals(800, $readImage->height());

        @unlink($temp);
    }

    public function test_non_image_files_bypass_compression(): void
    {
        $temp = tempnam(sys_get_temp_dir(), 'test_doc');
        file_put_contents($temp, 'Sample text content');

        $uploaded = new UploadedFile($temp, 'document.txt', 'text/plain', null, true);

        $savedPath = StorageService::storePublicFile($uploaded, 'docs');

        $this->assertNotEmpty($savedPath);
        $this->assertStringEndsWith('.txt', $savedPath);
        $this->assertTrue(Storage::disk('public')->exists($savedPath));

        @unlink($temp);
    }

    public function test_already_optimized_webp_within_dimension_preserves_file(): void
    {
        $temp = tempnam(sys_get_temp_dir(), 'test_webp') . '.webp';
        $gd = imagecreatetruecolor(800, 600);
        imagewebp($gd, $temp, 85);
        imagedestroy($gd);

        $originalHash = md5_file($temp);
        $uploaded = new UploadedFile($temp, 'client_compressed.webp', 'image/webp', null, true);

        $savedPath = StorageService::storePublicFile($uploaded, 'barang');

        $this->assertNotEmpty($savedPath);
        $this->assertStringEndsWith('.webp', $savedPath);
        $this->assertTrue(Storage::disk('public')->exists($savedPath));

        $savedContent = (string) Storage::disk('public')->get($savedPath);
        $this->assertEquals($originalHash, md5($savedContent));

        @unlink($temp);
    }

    public function test_optimize_cloudinary_url_injects_hd_quality_parameters(): void
    {
        $raw = 'https://res.cloudinary.com/fnf8f1pm/image/upload/v1792881951/rentify/moments/naikdt1lgug5qgylrk1l.jpg';
        $optimized = StorageService::optimizeCloudinaryUrl($raw);

        $quality = (int) config('filesystems.image_compression_quality', 100);
        $expectedParam = ($quality >= 100) ? 'f_auto,q_auto:best' : "f_auto,q_{$quality}";

        $this->assertEquals(
            "https://res.cloudinary.com/fnf8f1pm/image/upload/{$expectedParam}/v1792881951/rentify/moments/naikdt1lgug5qgylrk1l.jpg",
            $optimized
        );

        // Abaikan jika sudah memiliki optimasi
        $alreadyOpt = 'https://res.cloudinary.com/fnf8f1pm/image/upload/f_auto,q_auto:best/v1792881951/test.jpg';
        $this->assertEquals($alreadyOpt, StorageService::optimizeCloudinaryUrl($alreadyOpt));
    }

    public function test_extract_cloudinary_public_id_handles_various_formats(): void
    {
        $this->assertEquals(
            'rentify/barang/zvdnnm8vo52rxe7xxjs7',
            StorageService::extractCloudinaryPublicId('https://res.cloudinary.com/fnf8f1pm/image/upload/v1790928970/rentify/barang/zvdnnm8vo52rxe7xxjs7.png')
        );

        $this->assertEquals(
            'rentify/moments/jumszc7ixhgqlcdbs5zm',
            StorageService::extractCloudinaryPublicId('https://res.cloudinary.com/fnf8f1pm/image/upload/f_auto,q_85/v1792883907/rentify/moments/jumszc7ixhgqlcdbs5zm.png')
        );

        $this->assertEquals(
            'rentify/barang/epir3hg5l8m80lkoxbpb',
            StorageService::extractCloudinaryPublicId('rentify/barang/epir3hg5l8m80lkoxbpb.jpg')
        );
    }

    public function test_delete_local_file_removes_physical_file(): void
    {
        $testPath = 'test_delete_sample.txt';
        Storage::disk('public')->put($testPath, 'temporary content');
        $this->assertTrue(Storage::disk('public')->exists($testPath));

        $result = StorageService::deleteFile($testPath);
        $this->assertTrue($result);
        $this->assertFalse(Storage::disk('public')->exists($testPath));
    }
}
