<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\User;
use App\Services\SecurityAlertService;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated.',
                ], 401);
            }
            return redirect('/login');
        }

        // =====================================================================
        // 🔒 PROTEKSI KEAMANAN TINGKAT TINGGI KHUSUS ROLE ADMIN 🔒
        // =====================================================================
        if (in_array('admin', $roles)) {
            $adminEmail = strtolower(User::getAdminEmail());

            // 1b. Mode Kelola Toko Vendor (Impersonate):
            // Jika admin sedang mengelola toko vendor dan kembali ke rute admin, pulihkan sesi admin
            if (session()->has('admin_impersonator_id')) {
                /** @var User|null $adminImpersonator */
                $adminImpersonator = User::find(session('admin_impersonator_id'));
                if ($adminImpersonator instanceof User && $adminImpersonator->role === 'admin') {
                    // Di luar testing, pastikan impersonator adalah email admin resmi
                    if (app()->environment('testing') || strtolower(trim((string)$adminImpersonator->email)) === $adminEmail) {
                        session()->forget('admin_impersonator_id');
                        Auth::login($adminImpersonator);
                        session([
                            'active_role'               => 'admin',
                            'admin_auth_via_google'     => true,
                            'admin_authenticated_email' => $adminEmail,
                            'admin_session_ip'          => $request->ip(),
                            'admin_session_user_agent'  => $request->userAgent(),
                            'admin_last_activity'       => now()->timestamp,
                        ]);
                        $user = $adminImpersonator;
                    }
                }
            }

            // Validasi 1: User WAJIB memiliki role 'admin'
            if ($user->role !== 'admin') {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Akses ditolak. Anda tidak memiliki izin Administrator.',
                    ], 403);
                }
                return redirect('/home')->with('error', 'Akses ditolak. Anda tidak memiliki izin Administrator.');
            }

            // Validasi 2: Di lingkungan produksi/lokal, email WAJIB rentify.id@gmail.com
            $userEmail = strtolower(trim((string)$user->email));
            if (!app()->environment('testing') && $userEmail !== $adminEmail) {
                Auth::logout();
                $request->session()->invalidate();
                SecurityAlertService::notify(
                    'Pelanggaran Akses Administrator Ilegal',
                    "User dengan email '{$userEmail}' mencoba mengakses rute admin. Hanya '{$adminEmail}' yang diizinkan.",
                    [
                        'ip'         => $request->ip(),
                        'user_id'    => $user->id,
                        'email'      => $userEmail,
                        'url'        => $request->fullUrl(),
                        'action'     => 'Otomatis di-logout dan sesi dibatalkan.',
                    ]
                );

                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Akses ditolak. Akun tidak memiliki hak istimewa Administrator.',
                    ], 403);
                }
                return redirect('/login')->withErrors([
                    'login' => 'Akses ditolak. Hanya akun Google resmi rentify.id@gmail.com yang berhak mengakses Ruang Kendali Administrator.',
                ]);
            }

            // Validasi 3: Di lingkungan produksi/lokal, WAJIB otentikasi sah via Google OAuth (SSO)
            if (!app()->environment('testing')) {
                $isGoogleAuthed = session('admin_auth_via_google') === true 
                    && session('admin_authenticated_email') === $adminEmail;

                if (!$isGoogleAuthed) {
                    Auth::logout();
                    $request->session()->invalidate();
                    SecurityAlertService::notify(
                        'Upaya Akses Admin Tanpa Google SSO',
                        "Percobaan akses ke rute admin tanpa bukti sesi Google OAuth yang sah dari IP {$request->ip()}.",
                        [
                            'ip'         => $request->ip(),
                            'url'        => $request->fullUrl(),
                            'user_id'    => $user->id,
                            'action'     => 'Sesi dibatalkan. Wajib login ulang via Google SSO.',
                        ]
                    );

                    if ($request->expectsJson()) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Sesi tidak valid. Administrator wajib masuk via Google Sign-In.',
                        ], 403);
                    }
                    return redirect('/login')->withErrors([
                        'login' => 'Demi keamanan maksimal, Administrator Rentify hanya dapat masuk melalui tombol "Lanjutkan dengan Google" (Google SSO).',
                    ]);
                }

                // Validasi 4: Anti Session-Hijacking (Binding IP & User Agent)
                if (config('security.admin_auth.bind_session', true)) {
                    $sessionIp = session('admin_session_ip');
                    $sessionUa = session('admin_session_user_agent');

                    if ($sessionIp && $sessionIp !== $request->ip()) {
                        Auth::logout();
                        $request->session()->invalidate();
                        SecurityAlertService::notify(
                            'Peringatan Session Hijacking Admin',
                            "IP sesi admin berubah dari {$sessionIp} menjadi {$request->ip()}. Sesi segera dimatikan demi keamanan.",
                            [
                                'original_ip' => $sessionIp,
                                'current_ip'  => $request->ip(),
                                'url'         => $request->fullUrl(),
                            ]
                        );
                        return redirect('/login')->withErrors([
                            'login' => 'Perubahan jaringan / IP terdeteksi pada sesi Administrator. Harap masuk kembali melalui Google SSO.',
                        ]);
                    }

                    if ($sessionUa && $sessionUa !== $request->userAgent()) {
                        Auth::logout();
                        $request->session()->invalidate();
                        return redirect('/login')->withErrors([
                            'login' => 'Perubahan perangkat / browser terdeteksi pada sesi Administrator. Harap masuk kembali.',
                        ]);
                    }
                }

                // Validasi 5: Session Inactivity Timeout (60 menit default)
                $timeoutMinutes = (int) config('security.admin_auth.session_timeout_minutes', 60);
                $lastActivity = session('admin_last_activity');
                if ($lastActivity && (now()->timestamp - $lastActivity > ($timeoutMinutes * 60))) {
                    Auth::logout();
                    $request->session()->invalidate();
                    return redirect('/login')->withErrors([
                        'login' => "Sesi Administrator telah berakhir karena tidak ada aktivitas selama {$timeoutMinutes} menit. Silakan masuk kembali melalui Google SSO.",
                    ]);
                }

                // Perbarui timestamp aktivitas terakhir
                session(['admin_last_activity' => now()->timestamp]);
            }

            // Pastikan active_role disetel ke 'admin'
            if (session('active_role') !== 'admin') {
                session(['active_role' => 'admin']);
            }

            /** @var Response $response */
            $response = $next($request);

            // Pasang HTTP Security Headers tingkat tinggi untuk seluruh halaman/respons Admin
            $response->headers->set('X-Frame-Options', 'DENY');
            $response->headers->set('X-Content-Type-Options', 'nosniff');
            $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
            $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0, post-check=0, pre-check=0');
            $response->headers->set('Pragma', 'no-cache');

            return $response;
        }

        // =====================================================================
        // ROLE LAINNYA (CUSTOMER & VENDOR)
        // =====================================================================
        // Cegah pengguna non-admin memakai sesi active_role = 'admin'
        if ($user->role !== 'admin' && session('active_role') === 'admin') {
            session(['active_role' => $user->isVendor() ? 'vendor' : 'customer']);
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

        // 4. Periksa role aktif di session (kecuali admin)
        $activeRole = session('active_role');
        if ($activeRole && $activeRole !== 'admin' && in_array($activeRole, $roles)) {
            return $next($request);
        }

        // Jika tidak punya akses, arahkan kembali sesuai role
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses ke resource tersebut.',
            ], 403);
        }

        return redirect('/home')->with('error', 'Anda tidak memiliki akses ke halaman tersebut.');
    }
}
