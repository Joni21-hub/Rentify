<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use App\Models\User;
use App\Services\SecurityAlertService;

class AuthController extends Controller
{
    // 1. Menampilkan halaman login
    public function loginForm()
    {
        return view('auth.login'); 
    }

    // 2. Proses Login
    public function login(Request $request)
    {
        // Validasi input
        $request->validate([
            'login' => ['required'],
            'password' => ['required'],
        ]);

        $ip = $request->ip() ?? 'UNKNOWN_IP';
        $loginKey = strtolower(trim($request->login));
        $throttleKey = 'login_attempt:' . md5($loginKey . '|' . $ip);
        $maxAttempts = (int) config('security.rate_limits.login_max_attempts', 5);
        $decaySeconds = ((int) config('security.rate_limits.login_decay_minutes', 15)) * 60;

        // Cek jika akun / IP sedang terkena lockout
        if (RateLimiter::tooManyAttempts($throttleKey, $maxAttempts)) {
            $secondsRemaining = RateLimiter::availableIn($throttleKey);
            $minutesRemaining = ceil($secondsRemaining / 60);

            // Kirim alert darurat jika belum terkirim
            SecurityAlertService::notify(
                'Brute Force Login Terdeteksi',
                "Percobaan login melebihi batas {$maxAttempts}x gagal. Akses dikunci sementara selama {$minutesRemaining} menit.",
                [
                    'ip'     => $ip,
                    'url'    => $request->fullUrl(),
                    'target' => $loginKey,
                    'action' => "Lockout aktif ({$secondsRemaining} detik tersisa)",
                ]
            );

            return back()->withErrors([
                'login' => "Terlalu banyak percobaan login yang gagal. Akun / koneksi Anda dikunci sementara selama {$minutesRemaining} menit demi keamanan.",
            ])->withInput($request->only('login'));
        }

        $adminEmail = strtolower(User::getAdminEmail());

        // 🔒 KEAMANAN TINGKAT TINGGI ADMINISTRATOR:
        // Akun Administrator Rentify (rentify.id@gmail.com) HANYA BISA masuk via Google OAuth tanpa kata sandi.
        // Segala upaya login kata sandi manual langsung diblokir seketika.
        if ($loginKey === $adminEmail) {
            SecurityAlertService::notify(
                'Upaya Login Password Admin Dicegah',
                "Percobaan login kata sandi manual pada akun Administrator '{$adminEmail}' dari IP {$ip} berhasil dicegah. Akun Administrator hanya diizinkan masuk melalui Google SSO.",
                [
                    'ip'     => $ip,
                    'url'    => $request->fullUrl(),
                    'target' => $loginKey,
                    'action' => 'Akses ditolak. Wajib menggunakan tombol "Lanjutkan dengan Google".',
                ]
            );

            return back()->withErrors([
                'login' => 'Akun Administrator Rentify dilindungi oleh Google SSO tingkat tinggi dan tidak menggunakan kata sandi. Silakan gunakan tombol "Lanjutkan dengan Google" di bawah.',
            ])->withInput($request->only('login'));
        }

        // Cek jika akun yang dituju berstatus admin (misal lewat nomor telepon atau email lain)
        $targetedAdmin = User::where('role', 'admin')
            ->where(function ($q) use ($loginKey) {
                $q->where('email', $loginKey)
                  ->orWhere('whatsapp', $loginKey);
            })->first();

        if ($targetedAdmin && !app()->environment('testing')) {
            return back()->withErrors([
                'login' => 'Akun Administrator tidak dapat diakses menggunakan kata sandi. Silakan masuk secara aman menggunakan tombol "Lanjutkan dengan Google".',
            ])->withInput($request->only('login'));
        }

        $loginType = filter_var($request->login, FILTER_VALIDATE_EMAIL) ? 'email' : 'whatsapp';

        // Coba login
        if (Auth::attempt([$loginType => $request->login, 'password' => $request->password])) {
            // Berhasil login: bersihkan hitungan kegagalan login
            RateLimiter::clear($throttleKey);

            $user = Auth::user();

            // 🔒 Tolak akun admin jika berhasil login via password (di luar testing)
            if ($user->role === 'admin' && !app()->environment('testing')) {
                Auth::logout();
                $request->session()->invalidate();
                return back()->withErrors([
                    'login' => 'Akses ditolak. Akun Administrator hanya dapat masuk via Google Sign-In.',
                ])->withInput($request->only('login'));
            }

            $nomorWa = $user->role === 'vendor' ? $user->whatsapp_vendor : $user->whatsapp;

            // Verifikasi WhatsApp hanya diwajibkan jika:
            // 1. Akun bukan admin
            // 2. Akun memiliki nomor WhatsApp terdaftar (!empty($nomorWa))
            // 3. Nomor WhatsApp belum diverifikasi (is_null($user->whatsapp_verified_at))
            // 4. Akun BUKAN pengguna Google / Gmail (akun Google menggunakan email terverifikasi sebagai identitas utama)
            $isGoogleOrEmailCustomer = ($user->role === 'customer' && ($user->google_id || $user->hasRealEmail()));
            $isVendorNeedingVerification = ($user->role === 'vendor' && !empty($user->whatsapp_vendor) && is_null($user->whatsapp_verified_at));
            $isCustomerNeedingVerification = ($user->role === 'customer' && !empty($user->whatsapp) && is_null($user->whatsapp_verified_at) && !$isGoogleOrEmailCustomer);

            $needsWaVerification = $user->role !== 'admin' && ($isVendorNeedingVerification || $isCustomerNeedingVerification);

            if ($needsWaVerification) {
                // Logout sementara karena belum terverifikasi
                Auth::logout();
                
                // Generate OTP baru
                $otp = rand(100000, 999999);
                
                session([
                    'otp_user_id' => $user->id,
                    'otp_code' => $otp,
                    'otp_phone' => $nomorWa
                ]);

                // Kirim via WA
                $pesan = "*RENTIFY*\n\nLogin Terdeteksi. Kode OTP Anda adalah: *$otp*.\n\nJangan berikan kode ini kepada siapapun.";
                \App\Services\WhatsAppService::send($nomorWa, $pesan);

                return redirect()->route('otp.verify')->with('error', 'Anda harus memverifikasi nomor WhatsApp terlebih dahulu.');
            }

            $request->session()->regenerate();
            return $this->redirectByRole();
        }

        // Catat kegagalan login ke RateLimiter
        RateLimiter::hit($throttleKey, $decaySeconds);
        $attemptsLeft = RateLimiter::retriesLeft($throttleKey, $maxAttempts);

        // Jika batas 5x baru saja terlewati, langsung trigger alert WhatsApp ke Admin
        if ($attemptsLeft <= 0) {
            SecurityAlertService::notify(
                'Upaya Brute Force Login Dicegah',
                "Upaya tebak password gagal {$maxAttempts} kali berturut-turut pada akun '{$loginKey}' dari IP {$ip}. Sistem langsung mengunci akun/IP ini.",
                [
                    'ip'     => $ip,
                    'url'    => $request->fullUrl(),
                    'target' => $loginKey,
                    'action' => "Otomatis di-lockout selama " . ($decaySeconds / 60) . " menit",
                ]
            );
        }

        $warningMessage = 'Email/WhatsApp atau kata sandi yang Anda masukkan salah.';
        if ($attemptsLeft > 0 && $attemptsLeft <= 2) {
            $warningMessage .= " (Peringatan: Sisa {$attemptsLeft} kali percobaan sebelum akun dikunci sementara).";
        }

        return back()->withErrors([
            'login' => $warningMessage,
        ])->withInput($request->only('login'));
    }

    // 3. Logika Redirect Berdasarkan Role
    public function redirectByRole()
    {
        $user = Auth::user();

        if (!$user) {
            return redirect('/login');
        }

        // Admin selalu ke dashboard admin
        if ($user->role === 'admin') {
            session(['active_role' => 'admin']);
            return redirect()->intended('/admin/dashboard');
        }

        // Jika akun memiliki peran ganda (Customer + Toko Vendor)
        if ($user->hasDualRole()) {
            // Jika dalam sesi ini sudah pernah memilih role secara eksplisit
            if (session()->has('active_role')) {
                $role = session('active_role');
                if ($role === 'vendor') {
                    return redirect()->intended('/vendor/dashboard');
                }
                return redirect()->intended('/customer');
            }

            // Belum memilih -> arahkan ke halaman pemilihan peran
            return redirect()->route('role.select');
        }

        // Jika hanya Vendor
        if ($user->role === 'vendor') {
            session(['active_role' => 'vendor']);
            return redirect()->intended('/vendor/dashboard');
        }

        // Default Customer
        session(['active_role' => 'customer']);
        return redirect()->intended('/customer'); 
    }

    // 3b. Tampilkan Halaman Pemilihan Peran (Customer vs Vendor)
    public function showSelectRole()
    {
        $user = Auth::user();

        if (!$user) {
            return redirect('/login');
        }

        if ($user->role === 'admin') {
            return redirect('/admin/dashboard');
        }

        // Jika tidak punya peran ganda, arahkan langsung
        if (!$user->hasDualRole()) {
            return $this->redirectByRole();
        }

        return view('auth.select-role', compact('user'));
    }

    // 3c. Proses Pilihan Peran Masuk
    public function selectRole(Request $request)
    {
        $request->validate([
            'role' => 'required|in:customer,vendor',
        ]);

        $user = Auth::user();
        if (!$user) {
            return redirect('/login');
        }

        $chosenRole = $request->role;

        if ($chosenRole === 'vendor' && !$user->isVendor()) {
            return redirect()->route('customer.dashboard')->with('error', 'Anda belum memiliki toko terdaftar.');
        }

        session(['active_role' => $chosenRole]);

        if ($chosenRole === 'vendor') {
            return redirect()->intended('/vendor/dashboard')->with('success', "Selamat datang di Toko {$user->vendor_name}!");
        }

        return redirect()->intended('/customer')->with('success', 'Selamat datang di katalog Customer!');
    }

    // 3d. Beralih Peran (Role Switching Cepat)
    public function switchRole(string $role)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect('/login');
        }

        if ($user->role === 'admin') {
            return redirect('/admin/dashboard');
        }

        if ($role === 'vendor') {
            if (!$user->isVendor()) {
                return redirect()->route('vendor.register')->with('info', 'Silakan daftarkan toko Anda terlebih dahulu.');
            }
            session(['active_role' => 'vendor']);
            return redirect('/vendor/dashboard')->with('success', "Beralih ke Toko {$user->vendor_name}.");
        }

        // Beralih ke customer
        session(['active_role' => 'customer']);
        return redirect('/customer')->with('success', 'Beralih ke Mode Customer.');
    }

    // 4. Proses Logout
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect('/');
    }

    // 1. Menampilkan Halaman Form Register
    public function registerForm()
    {
        return view('auth.register');
    }

    // 2. Memproses Data Register
    public function register(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'whatsapp' => ['required', 'string', 'max:20', 'unique:users,whatsapp'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'terms' => ['accepted'],
        ], [
            'terms.accepted' => 'Pendaftaran gagal. Anda wajib mencentang dan menyetujui Syarat & Ketentuan Rentify.',
            'whatsapp.unique' => 'Nomor WhatsApp ini sudah terdaftar. Silakan gunakan nomor lain atau masuk ke akun Anda.',
        ]);

        $dummyEmail = preg_replace('/[^0-9]/', '', $request->whatsapp) . '@rentify.local';

        $user = User::create([
            'name' => $request->name,
            'email' => $dummyEmail, 
            'whatsapp' => $request->whatsapp,
            'password' => Hash::make($request->password),
            'role' => 'customer', 
        ]);

        // ALUR OTP BARU (Tidak langsung Auth::login)
        $otp = rand(100000, 999999);
        session([
            'otp_user_id' => $user->id,
            'otp_code' => $otp,
            'otp_phone' => $user->whatsapp
        ]);

        // Kirim OTP via WhatsApp Gateway
        $pesan = "*RENTIFY*\n\nSelamat datang, {$user->name}!\nKode OTP pendaftaran Anda adalah: *$otp*.\n\nJangan berikan kode ini kepada siapapun.";
        \App\Services\WhatsAppService::send($user->whatsapp, $pesan);

        // Arahkan ke halaman verifikasi OTP
        return redirect()->route('otp.verify');
    }

    // ─── SOCIALITE: GOOGLE LOGIN ──────────────────────────────────────────
    private function getGoogleRedirectUri(): string
    {
        $configured = config('services.google.redirect');
        if ($configured && str_starts_with($configured, 'http')) {
            return $configured;
        }
        // Gunakan URL callback dinamis sesuai domain & HTTPS yang sedang diakses
        return url('/auth/google/callback');
    }

    public function redirectToGoogle(Request $request)
    {
        // Menyimpan status persetujuan S&K jika mereka mendaftar via tombol Google di halaman Register
        if ($request->has('agreed')) {
            session(['google_agreed_terms' => true]);
        }
        return \Laravel\Socialite\Facades\Socialite::driver('google')
            ->redirectUrl($this->getGoogleRedirectUri())
            ->redirect();
    }

    public function handleGoogleCallback(Request $request)
    {
        try {
            $googleUser = \Laravel\Socialite\Facades\Socialite::driver('google')
                ->redirectUrl($this->getGoogleRedirectUri())
                ->user();

            $googleEmail = strtolower(trim((string) $googleUser->getEmail()));
            $googleId = (string) $googleUser->getId();
            $adminEmail = strtolower(User::getAdminEmail());

            // =========================================================================
            // 🛡️ OTENTIKASI EKSKLUSIF ADMINISTRATOR: rentify.id@gmail.com
            // HANYA akun Gmail ini yang dapat masuk sebagai Administrator ke sistem.
            // Masuk langsung tanpa sandi, dilengkapi proteksi sesi tingkat tinggi.
            // =========================================================================
            if ($googleEmail === $adminEmail) {
                /** @var User|null $admin */
                $admin = User::where('email', $adminEmail)->first();

                if (!$admin) {
                    $admin = User::create([
                        'name'              => $googleUser->getName() ?: 'Administrator Rentify',
                        'email'             => $adminEmail,
                        'google_id'         => $googleId,
                        'role'              => 'admin',
                        'password'          => Hash::make(\Illuminate\Support\Str::random(64)),
                        'email_verified_at' => now(),
                    ]);
                } else {
                    $admin->update([
                        'name'              => $admin->name ?: ($googleUser->getName() ?: 'Administrator Rentify'),
                        'google_id'         => $googleId,
                        'role'              => 'admin',
                        'email_verified_at' => $admin->email_verified_at ?? now(),
                        'password'          => Hash::make(\Illuminate\Support\Str::random(64)), // Password selalu diacak
                    ]);
                }

                // Turunkan akun lain yang bukan rentify.id@gmail.com jika sempat ada yang berstatus admin di DB
                if (!app()->environment('testing')) {
                    User::where('role', 'admin')
                        ->where('email', '!=', $adminEmail)
                        ->update(['role' => 'customer']);
                }

                Auth::login($admin);
                $request->session()->regenerate();

                // Simpan penanda otentikasi Google SSO tingkat tinggi dan binding sesi
                session([
                    'active_role'               => 'admin',
                    'admin_auth_via_google'     => true,
                    'admin_authenticated_email' => $adminEmail,
                    'admin_session_ip'          => $request->ip(),
                    'admin_session_user_agent'  => $request->userAgent(),
                    'admin_last_activity'       => now()->timestamp,
                ]);

                SecurityAlertService::notify(
                    'Login Admin Google SSO Berhasil',
                    "Administrator ({$adminEmail}) berhasil masuk ke Ruang Kendali via Google OAuth dari IP {$request->ip()}.",
                    [
                        'ip'         => $request->ip(),
                        'user_agent' => substr((string) $request->userAgent(), 0, 100),
                        'email'      => $adminEmail,
                        'time'       => now()->toDateTimeString(),
                    ]
                );

                return redirect()->intended('/admin/dashboard')->with('success', 'Selamat datang di Ruang Kendali Administrator Rentify!');
            }

            // =========================================================================
            // PENGGUNA NON-ADMIN (Customer & Vendor):
            // Pastikan akun dengan email selain rentify.id@gmail.com TIDAK BISA menjadi admin
            // =========================================================================
            $user = User::where('google_id', $googleId)->orWhere('email', $googleEmail)->first();

            if ($user) {
                // Keamanan ketat: jika user non-rentify sempat punya role admin di DB, paksa downgrade ke customer
                if ($user->role === 'admin' && !app()->environment('testing')) {
                    $user->role = 'customer';
                }

                if (!$user->google_id) {
                    $user->google_id = $googleId;
                }
                if (!$user->email_verified_at) {
                    $user->email_verified_at = now();
                }
                $user->save();

                Auth::login($user);
                $request->session()->regenerate();
                return $this->redirectByRole();
            } else {
                // JIKA BELUM TERDAFTAR (CELAH HUKUM DITUTUP)
                // Kita pastikan mereka datang dari halaman Register yang mana S&K sudah disetujui (disimpan di session)
                if (session('google_agreed_terms') === true) {
                    // Hapus session setelah digunakan
                    session()->forget('google_agreed_terms');

                    // Buat akun baru otomatis
                    $newUser = User::create([
                        'name'              => $googleUser->getName(),
                        'email'             => $googleEmail,
                        'google_id'         => $googleId,
                        'role'              => 'customer',
                        'password'          => Hash::make(\Illuminate\Support\Str::random(32)),
                        'email_verified_at' => now(),
                    ]);

                    Auth::login($newUser);
                    $request->session()->regenerate();
                    return $this->redirectByRole();
                } else {
                    // JIKA MEREKA MENCOBA DAFTAR DARI HALAMAN LOGIN (Membypass S&K)
                    return redirect('/register')->withErrors(['terms' => 'Demi keamanan dan kepatuhan hukum, akun Google Anda belum dapat didaftarkan. Silakan daftar dan centang Syarat & Ketentuan di bawah ini terlebih dahulu.']);
                }
            }

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Google Auth Error: ' . $e->getMessage());
            return redirect('/login')->withErrors(['login' => 'Gagal terhubung dengan Google. Silakan coba lagi.']);
        }
    }
}