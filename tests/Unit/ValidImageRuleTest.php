<?php

namespace Tests\Unit;

use App\Rules\ValidImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class ValidImageRuleTest extends TestCase
{
    /**
     * Uji format gambar standar yang umum digunakan.
     */
    public function test_standard_image_formats_pass_validation(): void
    {
        $rule = new ValidImage(20480);
        $formats = [
            'photo.jpg'  => 'image/jpeg',
            'photo.jpeg' => 'image/jpeg',
            'photo.png'  => 'image/png',
            'photo.webp' => 'image/webp',
            'photo.gif'  => 'image/gif',
            'photo.bmp'  => 'image/bmp',
            'photo.svg'  => 'image/svg+xml',
            'photo.jfif' => 'image/jpeg',
            'photo.tiff' => 'image/tiff',
            'photo.avif' => 'image/avif',
        ];

        foreach ($formats as $filename => $mime) {
            $file = UploadedFile::fake()->create($filename, 500, $mime);
            $validator = Validator::make(['foto' => $file], ['foto' => $rule]);
            $this->assertTrue($validator->passes(), "Format {$filename} ({$mime}) harus lulus validasi.");
        }
    }

    /**
     * Uji format HEIC/HEIF dari perangkat iOS iPhone (seringkali terkirim dengan mime octet-stream).
     */
    public function test_heic_and_heif_formats_pass_validation(): void
    {
        $rule = new ValidImage(20480);

        // MIME image/heic
        $heic1 = UploadedFile::fake()->create('iphone_photo.heic', 1024, 'image/heic');
        $validator1 = Validator::make(['foto' => $heic1], ['foto' => $rule]);
        $this->assertTrue($validator1->passes(), "File HEIC dengan MIME image/heic harus lulus.");

        // MIME application/octet-stream (fallback browser/sistem)
        $heic2 = UploadedFile::fake()->create('camera_raw.heic', 1024, 'application/octet-stream');
        $validator2 = Validator::make(['foto' => $heic2], ['foto' => $rule]);
        $this->assertTrue($validator2->passes(), "File HEIC dengan MIME application/octet-stream harus tetap lulus.");

        // HEIF
        $heif = UploadedFile::fake()->create('live_photo.heif', 1024, 'image/heif');
        $validator3 = Validator::make(['foto' => $heif], ['foto' => $rule]);
        $this->assertTrue($validator3->passes(), "File HEIF harus lulus.");
    }

    /**
     * Uji berkas bukan gambar (harus ditolak demi keamanan).
     */
    public function test_non_image_files_are_rejected(): void
    {
        $rule = new ValidImage(20480);
        $invalidFiles = [
            'document.pdf' => 'application/pdf',
            'script.sh'    => 'text/x-shellscript',
            'program.exe'  => 'application/x-msdownload',
            'archive.zip'  => 'application/zip',
        ];

        foreach ($invalidFiles as $filename => $mime) {
            $file = UploadedFile::fake()->create($filename, 200, $mime);
            $validator = Validator::make(['foto' => $file], ['foto' => $rule]);
            $this->assertFalse($validator->passes(), "File non-gambar {$filename} harus ditolak.");
        }
    }

    /**
     * Uji pembatasan ukuran berkas.
     */
    public function test_oversized_files_are_rejected(): void
    {
        $rule = new ValidImage(5120); // Batas 5MB

        // File 4MB (lulus)
        $validFile = UploadedFile::fake()->create('small.jpg', 4000, 'image/jpeg');
        $validator1 = Validator::make(['foto' => $validFile], ['foto' => $rule]);
        $this->assertTrue($validator1->passes());

        // File 6MB (gagal)
        $oversizedFile = UploadedFile::fake()->create('large.jpg', 6144, 'image/jpeg');
        $validator2 = Validator::make(['foto' => $oversizedFile], ['foto' => $rule]);
        $this->assertFalse($validator2->passes());
    }
}
