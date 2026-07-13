<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        // Mengecek apakah user sudah login dan apakah role-nya ada di daftar yang diizinkan
        if ($request->user() && in_array($request->user()->role, $roles)) {
            return $next($request);
        }

        // Jika tidak punya akses, arahkan kembali ke dashboard atau pesan error
        return redirect('/home')->with('error', 'Anda tidak memiliki akses.');
    }
}