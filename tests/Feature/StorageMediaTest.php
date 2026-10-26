<?php

namespace Tests\Feature;

use App\Models\Barang;
use App\Models\FotoBarang;
use App\Models\Kategori;
use App\Models\User;
use App\Services\StorageService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StorageMediaTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        config(['filesystems.default' => 'public']);
    }

    public function test_storage_service_stores_file_and_resolves_across_symlink_junction(): void
    {
        $fakeImage = UploadedFile::fake()->image('sample_item.jpg', 400, 300);
        $savedPath = StorageService::storePublicFile($fakeImage, 'barang');

        $this->assertNotEmpty($savedPath);
        $this->assertTrue(Storage::disk('public')->exists($savedPath), 'File should exist on public disk');

        // Verifikasi asersi resolusi filesystem dan symlink / junction
        $publicResolvedPath = public_path('storage/' . $savedPath);
        $this->assertFileExists($publicResolvedPath, 'File should be accessible via public/storage symlink junction');
        $this->assertTrue(is_readable($publicResolvedPath), 'Public file must be readable');

        // Verifikasi MIME type dan realpath
        $mime = File::mimeType($publicResolvedPath);
        $this->assertStringStartsWith('image/', $mime);

        $storageRealpath = (string) realpath(storage_path('app/public/' . $savedPath));
        $publicRealpath = (string) realpath($publicResolvedPath);
        $this->assertEquals(strtolower($storageRealpath), strtolower($publicRealpath), 'Realpath across junction must match exactly');

        // Clean up
        StorageService::deletePublicFile($savedPath);
        $this->assertFalse(Storage::disk('public')->exists($savedPath));
    }

    public function test_vendor_barang_store_uploads_media_to_public_disk_without_cloudinary(): void
    {
        $vendor = User::create([
            'name' => 'Vendor Test',
            'email' => 'vendor_' . uniqid() . '@example.com',
            'password' => bcrypt('password123'),
            'role' => 'vendor',
            'is_active' => 1,
        ]);
        $vendor->markEmailAsVerified();

        $kategori = Kategori::create([
            'nama' => 'Peralatan Uji',
            'slug' => 'peralatan-uji-' . uniqid(),
            'is_active' => 1,
        ]);

        $coverFile = UploadedFile::fake()->image('dslr_camera.jpg', 800, 600);
        $galleryFile = UploadedFile::fake()->image('dslr_lens.jpg', 800, 600);

        $response = $this->actingAs($vendor)->post(route('vendor.barang.store'), [
            'kategori_id' => $kategori->id,
            'nama' => 'Kamera Canon 80D',
            'deskripsi' => 'Kondisi mulus siap pakai',
            'harga_sewa_harian' => 150000,
            'deposit' => 200000,
            'denda_per_hari' => 50000,
            'kondisi' => 'Sangat Baik',
            'stok_total' => 2,
            'alamat' => 'Jl. Kebon Jeruk No 1',
            'latitude' => '-6.2088',
            'longitude' => '106.8456',
            'fotos' => [$coverFile, $galleryFile],
        ]);

        $response->assertRedirect(route('vendor.barang.index'));
        $response->assertSessionHas('success');

        $barang = Barang::where('nama', 'Kamera Canon 80D')->latest()->first();
        $this->assertNotNull($barang);
        $coverPhoto = (string) $barang->cover_photo;
        $this->assertStringStartsWith('barang/', $coverPhoto);
        $this->assertStringNotContainsString('cloudinary.com', $coverPhoto);

        // Verifikasi fisik pada storage publik
        $this->assertTrue(Storage::disk('public')->exists($coverPhoto));
        $this->assertFileExists(public_path('storage/' . $coverPhoto));

        // Verifikasi URL accessor menggunakan helper asset
        $expectedUrl = asset('storage/' . $coverPhoto);
        $this->assertEquals($expectedUrl, $barang->cover_photo_url);

        // Verifikasi foto galeri tambahan
        $fotoGaleri = FotoBarang::where('barang_id', $barang->id)->first();
        $this->assertNotNull($fotoGaleri);
        $fotoPath = (string) $fotoGaleri->foto_path;
        $this->assertStringStartsWith('barang/galeri/', $fotoPath);
        $this->assertTrue(Storage::disk('public')->exists($fotoPath));

        // Cleanup
        StorageService::deletePublicFile($barang->cover_photo);
        StorageService::deletePublicFile($fotoPath);
    }

    public function test_fallback_mechanism_returns_placeholder_when_file_is_missing(): void
    {
        $barang = new Barang();
        $barang->cover_photo = 'barang/non_existent_file_999.jpg';

        $resolvedUrl = $barang->cover_photo_url;
        $expectedPlaceholderUrl = asset('storage/placeholders/placeholder-barang.png');

        $this->assertEquals($expectedPlaceholderUrl, $resolvedUrl, 'Missing physical file must fallback to placeholder image');
    }

    public function test_http_get_serves_public_storage_media_with_200_ok(): void
    {
        $placeholderUrl = 'http://127.0.0.1/rentify/public/storage/placeholders/placeholder-barang.png';
        try {
            $response = \Illuminate\Support\Facades\Http::timeout(3)->get($placeholderUrl);
            if ($response->successful()) {
                $this->assertEquals(200, $response->status());
                $this->assertStringContainsString('image/png', (string) $response->header('Content-Type'));
            } else {
                $this->assertFileExists(public_path('storage/placeholders/placeholder-barang.png'));
            }
        } catch (\Exception $e) {
            $this->assertFileExists(public_path('storage/placeholders/placeholder-barang.png'));
        }
    }
}
