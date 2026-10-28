<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider; //[cite: 3]
use Illuminate\Support\Facades\View; //[cite: 3]
use App\Models\Keranjang; //[cite: 3]
use Illuminate\Support\Facades\Auth; //[cite: 3]
use Illuminate\Support\Facades\URL; // Wajib ditambahkan untuk keamanan HTTPS
use Illuminate\Support\Facades\Validator;
use App\Rules\ValidImage;
use App\Rules\ValidMedia;

class AppServiceProvider extends ServiceProvider //[cite: 3]
{
    public function register(): void //[cite: 3]
    {
        //[cite: 3]
    }

    public function boot(): void //[cite: 3]
    {
        // 0. Cloud Connection Guard: Cegah kueri aktif ke legacy Aiven Cloud MySQL pada lingkungan non-produksi
        if (!app()->isProduction() || in_array(config('app.env'), ['local', 'testing'])) {
            $host = (string) (config('database.connections.mysql.host') ?: env('DB_HOST'));
            $port = (string) (config('database.connections.mysql.port') ?: env('DB_PORT'));
            $url = (string) (config('database.connections.mysql.url') ?: env('DB_URL') ?: env('DATABASE_URL'));

            $isCloudHost = str_contains(strtolower($host), 'aivencloud.com');
            $isCloudPort = ($port === '18655');
            $isCloudUrl = false;

            if (!empty($url)) {
                $parsed = parse_url($url);
                $urlHost = strtolower($parsed['host'] ?? '');
                $urlPort = (string) ($parsed['port'] ?? '');
                if (str_contains($urlHost, 'aivencloud.com') || $urlPort === '18655' || str_contains(strtolower($url), 'aivencloud.com')) {
                    $isCloudUrl = true;
                }
            }

            if ($isCloudHost || $isCloudPort || $isCloudUrl) {
                throw new \RuntimeException('Cloud Aiven MySQL connection blocked in non-production environment.');
            }
        }

        // 1. TAMBAHAN BARU: Memaksa form menggunakan jalur aman HTTPS saat online agar tidak diblokir browser
        if (config('app.env') !== 'local') {
            URL::forceScheme('https');
        }

        // 2. KODE ASLIMU: Menampilkan angka jumlah keranjang di seluruh halaman website[cite: 3]
        View::composer('*', function ($view) {
            if (Auth::check()) {
                $count = Keranjang::where('user_id', Auth::id())->count();
                $view->with('keranjangCount', $count);
            } else {
                $view->with('keranjangCount', 0);
            }
        });
        // 3. Validator custom rule: valid_image untuk mendukung seluruh format gambar (JPG, PNG, WEBP, HEIC, AVIF, GIF, BMP, dll)
        Validator::extend('valid_image', function ($attribute, $value, $parameters) {
            $maxKb = isset($parameters[0]) ? (int) $parameters[0] : 20480;
            $rule = new ValidImage($maxKb);
            $failed = false;
            $rule->validate($attribute, $value, function ($message) use (&$failed) {
                $failed = true;
            });
            return !$failed;
        });

        Validator::replacer('valid_image', function ($message, $attribute, $rule, $parameters) {
            $maxMb = isset($parameters[0]) ? round(((int) $parameters[0]) / 1024, 1) : 20;
            return "Berkas {$attribute} harus berupa gambar valid (JPG, PNG, WEBP, HEIC, AVIF, GIF, dll) dan maksimal {$maxMb} MB.";
        });

        // 4. Validator custom rule: valid_media untuk mendukung seluruh media produk (Foto semua format & Video)
        Validator::extend('valid_media', function ($attribute, $value, $parameters) {
            $maxKb = isset($parameters[0]) ? (int) $parameters[0] : 102400;
            $allowVideo = !isset($parameters[1]) || filter_var($parameters[1], FILTER_VALIDATE_BOOLEAN);
            $rule = new ValidMedia($maxKb, $allowVideo);
            $failed = false;
            $rule->validate($attribute, $value, function ($message) use (&$failed) {
                $failed = true;
            });
            return !$failed;
        });

        Validator::replacer('valid_media', function ($message, $attribute, $rule, $parameters) {
            $maxMb = isset($parameters[0]) ? round(((int) $parameters[0]) / 1024, 1) : 100;
            return "Berkas {$attribute} harus berupa foto atau video valid dan maksimal {$maxMb} MB.";
        });
    }
}