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

        $loginType = filter_var($request->login, FILTER_VALIDATE_EMAIL) ? 'email' : 'whatsapp';

        // Coba login
        if (Auth::attempt([$loginType => $request->login, 'password' => $request->password])) {
            // Berhasil login: bersihkan hitungan kegagalan login
            RateLimiter::clear($throttleKey);

            $user = Auth::user();

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

    public function handleGoogleCallback()
    {
        try {
            $googleUser = \Laravel\Socialite\Facades\Socialite::driver('google')
                ->redirectUrl($this->getGoogleRedirectUri())
                ->user();
            
            // Cek apakah user sudah terdaftar menggunakan google_id ATAU email yang sama
            $user = User::where('google_id', $googleUser->id)->orWhere('email', $googleUser->email)->first();

            if ($user) {
                // Jika user ada tapi google_id-nya masih kosong (mungkin dulu daftar manual), kita update
                if (!$user->google_id) {
                    $user->update(['google_id' => $googleUser->id]);
                }
                // Jika belum diverifikasi, otomatis verifikasi karena Google sudah valid
                if (!$user->email_verified_at) {
                    $user->update(['email_verified_at' => now()]);
                }
                
                Auth::login($user);
                return $this->redirectByRole();
            } else {
                // JIKA BELUM TERDAFTAR (CELAH HUKUM DITUTUP)
                // Kita pastikan mereka datang dari halaman Register yang mana S&K sudah disetujui (disimpan di session)
                if (session('google_agreed_terms') === true) {
                    // Hapus session setelah digunakan
                    session()->forget('google_agreed_terms');

                    // Buat akun baru otomatis
                    $newUser = User::create([
                        'name' => $googleUser->name,
                        'email' => $googleUser->email,
                        'google_id' => $googleUser->id,
                        'role' => 'customer',
                        'password' => Hash::make(uniqid()), // Beri password acak yang tidak mungkin ditebak
                    ]);
                    
                    $newUser->markEmailAsVerified();
                    Auth::login($newUser);
                    return $this->redirectByRole();
                } else {
                    // JIKA MEREKA MENCOBA DAFTAR DARI HALAMAN LOGIN (Membypass S&K)
                    return redirect('/register')->withErrors(['terms' => 'Demi keamanan dan kepatuhan hukum, akun Google Anda belum dapat didaftarkan. Silakan daftar dan centang Syarat & Ketentuan di bawah ini terlebih dahulu.']);
                }
            }
            
        } catch (\Exception $e) {
            return redirect('/login')->withErrors(['login' => 'Gagal terhubung dengan Google. Silakan coba lagi.']);
        }
    }
}