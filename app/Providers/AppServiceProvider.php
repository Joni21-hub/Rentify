<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\Keranjang;
use Illuminate\Support\Facades\Auth;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // 1. Perbaikan Vercel: Buat otomatis folder storage & view yang dibutuhkan Laravel
        if (isset($_ENV['VERCEL'])) {
            $storagePath = '/tmp/storage';
            app()->useStoragePath($storagePath);

            foreach (['/framework/views', '/framework/cache/data', '/framework/sessions', '/logs'] as $path) {
                if (!is_dir($storagePath . $path)) {
                    mkdir($storagePath . $path, 0755, true);
                }
            }
        }

        // 2. Kode asli Anda (Mengirimkan variabel $keranjangCount ke semua view)
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