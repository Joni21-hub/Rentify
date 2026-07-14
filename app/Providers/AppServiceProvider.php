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