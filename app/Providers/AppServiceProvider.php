<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider; //[cite: 3]
use Illuminate\Support\Facades\View; //[cite: 3]
use App\Models\Keranjang; //[cite: 3]
use Illuminate\Support\Facades\Auth; //[cite: 3]
use Illuminate\Support\Facades\URL; // Wajib ditambahkan untuk keamanan HTTPS

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
    }
}