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
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Coba login
        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            
            // Panggil fungsi redirect tanpa oper data
            return $this->redirectByRole();
        }

        // Jika login gagal
        return back()->withErrors([
            'email' => 'Email atau password yang Anda masukkan salah.',
        ]);
    }

    // 3. Logika Redirect Berdasarkan Role
    public function redirectByRole()
    {
        $user = Auth::user();

        // Jika ada orang iseng akses '/' tapi belum login, tendang ke halaman login
        if (!$user) {
            return redirect('/login');
        }

        // Pengalihan berdasarkan role
        if ($user->role == 'admin') {
            return redirect()->intended('/admin/dashboard');
        } elseif ($user->role == 'vendor') {
            return redirect()->intended('/vendor/dashboard');
        } elseif ($user->role == 'customer') {
            return redirect()->intended('/customer'); 
        }
        
        return redirect('/login');
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
        // PERBAIKAN: Menambahkan validasi wajib centang S&K dan nomor WA
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'whatsapp' => ['required', 'string', 'max:20', 'unique:users,whatsapp'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'terms' => ['accepted'],
        ], [
            'terms.accepted' => 'Pendaftaran gagal. Anda wajib mencentang dan menyetujui Syarat & Ketentuan Rentify.',
            'whatsapp.unique' => 'Nomor WhatsApp ini sudah terdaftar.',
        ]);

        // Membuat user baru dengan role 'customer' secara otomatis
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'whatsapp' => $request->whatsapp,
            'password' => Hash::make($request->password),
            'role' => 'customer', 
        ]);

        // Langsung otomatis login setelah sukses daftar
        Auth::login($user);

        // PICU PENGIRIMAN EMAIL VERIFIKASI
        event(new \Illuminate\Auth\Events\Registered($user));

        // Alihkan ke dashboard customer sesuai role
        return $this->redirectByRole();
    }

    // ─── SOCIALITE: GOOGLE LOGIN ──────────────────────────────────────────
    public function redirectToGoogle()
    {
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
                // Jika belum pernah daftar sama sekali, buatkan akun customer otomatis
                $newUser = User::create([
                    'name' => $googleUser->name,
                    'email' => $googleUser->email,
                    'google_id' => $googleUser->id,
                    'role' => 'customer',
                    'password' => Hash::make(uniqid()), // Beri password acak yang tidak mungkin ditebak
                ]);
                
                // Karena pakai Google, langsung verifikasi emailnya
                $newUser->markEmailAsVerified();

                Auth::login($newUser);
                return $this->redirectByRole();
            }
            
        } catch (\Exception $e) {
            return redirect('/login')->withErrors(['email' => 'Gagal login menggunakan Google. Silakan coba lagi.']);
        }
    }
}