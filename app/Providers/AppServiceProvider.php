<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\Keranjang;
use Illuminate\Support\Facades\Auth;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
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