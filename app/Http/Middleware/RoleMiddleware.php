<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = $request->user();

        if (!$user) {
            return redirect('/login');
        }

        // 1. Jika role asli user langsung cocok
        if (in_array($user->role, $roles)) {
            return $next($request);
        }

        // 2. Jika rute meminta role 'customer', semua pengguna non-admin diizinkan
        if (in_array('customer', $roles) && $user->isCustomer()) {
            if (session('active_role') !== 'customer') {
                session(['active_role' => 'customer']);
            }
            return $next($request);
        }

        // 3. Jika rute meminta role 'vendor', periksa apakah user memiliki toko
        if (in_array('vendor', $roles) && $user->isVendor()) {
            if (session('active_role') !== 'vendor') {
                session(['active_role' => 'vendor']);
            }
            return $next($request);
        }

        // 4. Periksa role aktif di session
        $activeRole = session('active_role');
        if ($activeRole && in_array($activeRole, $roles)) {
            return $next($request);
        }

        // Jika tidak punya akses, arahkan kembali sesuai role
        return redirect('/home')->with('error', 'Anda tidak memiliki akses ke halaman tersebut.');
    }
}