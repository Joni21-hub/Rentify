<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    // 1. Menampilkan halaman login (Disamakan dengan web.php yang memanggil 'loginForm')
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
            
            // Panggil fungsi redirect tanpa oper data (karena dideteksi otomatis di dalam fungsi)
            return $this->redirectByRole();
        }

        // Jika login gagal
        return back()->withErrors([
            'email' => 'Email atau password yang Anda masukkan salah.',
        ]);
    }

    // 3. Logika Redirect Berdasarkan Role (Sudah PUBLIC & Otomatis membaca user)
    public function redirectByRole()
    {
        $user = Auth::user();

        // Jika ada orang iseng akses '/' tapi belum login, tendang ke halaman login
        if (!$user) {
            return redirect('/login');
        }

        // Pengalihan berdasarkan role yang SESUAI dengan routes/web.php kamu
        if ($user->role == 'admin') {
            return redirect()->intended('/admin/dashboard');
        } elseif ($user->role == 'vendor') {
            return redirect()->intended('/vendor/dashboard');
        } elseif ($user->role == 'customer') {
            return redirect()->intended('/customer'); // Mengarah ke MarketplaceController
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

    // Tambahkan ini di dalam class AuthController

    // 1. Menampilkan Halaman Form Register
    public function registerForm()
    {
        return view('auth.register');
    }

    // 2. Memproses Data Register
    public function register(Request $request)
    {
        // Validasi data inputan
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'], // Wajib konfirmasi password
        ]);

        // Membuat user baru dengan role 'customer' secara otomatis
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'customer', // Set otomatis sebagai customer/penyewa
        ]);

        // Langsung otomatis login setelah sukses daftar
        Auth::login($user);

        // Alihkan ke dashboard customer sesuai role
        return $this->redirectByRole();
    }
}