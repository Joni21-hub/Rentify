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
        // 1. Tambahan khusus agar tidak Error 500 saat di-deploy ke Vercel
        if (isset($_ENV['VERCEL'])) {
            app()->useStoragePath('/tmp/storage');
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