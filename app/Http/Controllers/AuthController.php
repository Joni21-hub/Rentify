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
        // PERBAIKAN: Menambahkan validasi wajib centang S&K dan pesan error khusus
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'terms' => ['accepted'], // <- INI KUNCI VALIDASINYA
        ], [
            'terms.accepted' => 'Pendaftaran gagal. Anda wajib mencentang dan menyetujui Syarat & Ketentuan Rentify.',
        ]);

        // Membuat user baru dengan role 'customer' secara otomatis
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'customer', 
        ]);

        // Langsung otomatis login setelah sukses daftar
        Auth::login($user);

        // Alihkan ke dashboard customer sesuai role
        return $this->redirectByRole();
    }
}