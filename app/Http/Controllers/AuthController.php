<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

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

        $loginType = filter_var($request->login, FILTER_VALIDATE_EMAIL) ? 'email' : 'whatsapp';

        // Coba login
        if (Auth::attempt([$loginType => $request->login, 'password' => $request->password])) {
            $user = Auth::user();

            // Cek apakah WA sudah diverifikasi (kecuali admin)
            if (is_null($user->whatsapp_verified_at) && $user->role !== 'admin') {
                // Logout sementara karena belum terverifikasi
                Auth::logout();
                
                // Generate OTP baru
                $otp = rand(100000, 999999);
                $nomorWa = $user->role == 'vendor' ? $user->whatsapp_vendor : $user->whatsapp;
                
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

        // Jika login gagal
        return back()->withErrors([
            'login' => 'Email/WhatsApp atau kata sandi yang Anda masukkan salah.',
        ]);
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

        // Kirim OTP via Fonnte
        $pesan = "*RENTIFY*\n\nSelamat datang, {$user->name}!\nKode OTP pendaftaran Anda adalah: *$otp*.\n\nJangan berikan kode ini kepada siapapun.";
        \App\Services\WhatsAppService::send($user->whatsapp, $pesan);

        // Arahkan ke halaman verifikasi OTP
        return redirect()->route('otp.verify');
    }

    // ─── SOCIALITE: GOOGLE LOGIN ──────────────────────────────────────────
    public function redirectToGoogle(Request $request)
    {
        // Menyimpan status persetujuan S&K jika mereka mendaftar via tombol Google di halaman Register
        if ($request->has('agreed')) {
            session(['google_agreed_terms' => true]);
        }
        return \Laravel\Socialite\Facades\Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $googleUser = \Laravel\Socialite\Facades\Socialite::driver('google')->user();
            
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