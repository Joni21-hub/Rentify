<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class VendorController extends Controller
{
    // Menampilkan Form Registrasi Vendor
    public function showRegisterForm()
    {
        return view('auth.register-vendor'); 
    }

    // Memproses Pendaftaran Vendor (Mendukung pendaftaran baru & pembukaan toko untuk customer eksisting)
    public function register(Request $request)
    {
        // 1. Jika user sudah login sebagai Customer
        if (\Illuminate\Support\Facades\Auth::check()) {
            $currentUser = \Illuminate\Support\Facades\Auth::user();

            if ($currentUser->role === 'admin') {
                return redirect()->route('admin.dashboard')->with('error', 'Akun Admin tidak dapat membuka toko.');
            }

            if ($currentUser->isVendor() && $currentUser->vendor_status !== 'rejected') {
                return redirect()->route('vendor.dashboard')->with('info', 'Anda sudah memiliki toko terdaftar: ' . $currentUser->vendor_name);
            }

            $request->validate([
                'vendor_name'     => 'required|string|max:255',
                'whatsapp_vendor' => 'required|string|max:20|unique:users,whatsapp_vendor,' . $currentUser->id,
                'terms'           => 'accepted',
            ], [
                'terms.accepted'         => 'Pendaftaran gagal. Anda wajib menyatakan data asli dan menyetujui Syarat & Ketentuan Rentify.',
                'whatsapp_vendor.unique' => 'Nomor WhatsApp Toko sudah digunakan oleh akun lain.',
            ]);

            $currentUser->vendor_name     = $request->vendor_name;
            $currentUser->whatsapp_vendor = $request->whatsapp_vendor;
            $currentUser->vendor_status   = 'pending';
            $currentUser->role            = 'vendor';
            if ($request->filled('name')) {
                $currentUser->name = $request->name;
            }
            $currentUser->save();

            session(['active_role' => 'vendor']);

            // Kirim OTP verifikasi WhatsApp Vendor
            $otp = rand(100000, 999999);
            session([
                'otp_user_id' => $currentUser->id,
                'otp_code'    => $otp,
                'otp_phone'   => $currentUser->whatsapp_vendor,
                'active_role' => 'vendor',
            ]);

            $pesan = "*RENTIFY VENDOR*\n\nSelamat datang, {$currentUser->vendor_name}!\nKode OTP pendaftaran toko Anda adalah: *$otp*.\n\nJangan berikan kode ini kepada siapapun.";
            \App\Services\WhatsAppService::send($currentUser->whatsapp_vendor, $pesan);

            return redirect()->route('otp.verify')->with('success', 'Toko Anda berhasil didaftarkan! Silakan masukkan kode OTP yang dikirim ke nomor WhatsApp Toko.');
        }

        // 2. Jika user BELUM login
        $request->validate([
            'name'            => 'required|string|max:255',
            'vendor_name'     => 'required|string|max:255',
            'email'           => 'required|string|email|max:255',
            'whatsapp_vendor' => 'required|string|max:20',
            'password'        => 'required|string|min:8|confirmed',
            'terms'           => 'accepted', 
        ], [
            'terms.accepted'  => 'Pendaftaran gagal. Anda wajib menyatakan data asli dan menyetujui Syarat & Ketentuan Rentify.',
        ]);

        $existingUser = User::where('email', $request->email)->first();

        if ($existingUser) {
            // Jika akun adalah admin
            if ($existingUser->role === 'admin') {
                return back()->withErrors(['email' => 'Email ini digunakan untuk akun Admin dan tidak dapat didaftarkan sebagai vendor.'])->withInput();
            }

            // Jika akun sudah memiliki toko aktif / pending
            if ($existingUser->isVendor() && $existingUser->vendor_status !== 'rejected') {
                return back()->withErrors(['email' => "Email ini sudah memiliki toko '{$existingUser->vendor_name}'. Silakan langsung masuk (login)."])->withInput();
            }

            // Akun terdaftar sebagai Customer biasa -> Validasi password akun eksisting
            if (!Hash::check($request->password, $existingUser->password)) {
                return back()->withErrors([
                    'password' => 'Email ini sudah terdaftar sebagai Customer Rentify. Masukkan kata sandi akun Anda yang benar untuk membuka toko di akun ini.'
                ])->withInput();
            }

            // Periksa keunikan nomor WhatsApp toko terhadap akun lain
            $waExists = User::where('whatsapp_vendor', $request->whatsapp_vendor)
                ->where('id', '!=', $existingUser->id)
                ->exists();
            if ($waExists) {
                return back()->withErrors(['whatsapp_vendor' => 'Nomor WhatsApp Toko sudah digunakan oleh akun lain.'])->withInput();
            }

            // Perbarui akun eksisting dengan data toko
            $existingUser->name            = $request->name ?: $existingUser->name;
            $existingUser->vendor_name     = $request->vendor_name;
            $existingUser->whatsapp_vendor = $request->whatsapp_vendor;
            $existingUser->vendor_status   = 'pending';
            $existingUser->role            = 'vendor';
            $existingUser->save();

            $user = $existingUser;
        } else {
            // Email belum ada -> Periksa nomor WA vendor belum dipakai
            $waExists = User::where('whatsapp_vendor', $request->whatsapp_vendor)->exists();
            if ($waExists) {
                return back()->withErrors(['whatsapp_vendor' => 'Nomor WhatsApp Toko sudah terdaftar.'])->withInput();
            }

            // Buat user baru
            $user = User::create([
                'name'            => $request->name,
                'vendor_name'     => $request->vendor_name,
                'email'           => $request->email,
                'whatsapp_vendor' => $request->whatsapp_vendor,
                'password'        => Hash::make($request->password),
                'role'            => 'vendor',
                'vendor_status'   => 'pending', 
            ]);

            event(new \Illuminate\Auth\Events\Registered($user));
        }

        // Set OTP WA untuk verifikasi toko
        $otp = rand(100000, 999999);
        session([
            'otp_user_id' => $user->id,
            'otp_code'    => $otp,
            'otp_phone'   => $user->whatsapp_vendor,
            'active_role' => 'vendor',
        ]);

        $pesan = "*RENTIFY VENDOR*\n\nSelamat datang, {$user->vendor_name}!\nKode OTP pendaftaran toko Anda adalah: *$otp*.\n\nJangan berikan kode ini kepada siapapun.";
        \App\Services\WhatsAppService::send($user->whatsapp_vendor, $pesan);

        return redirect()->route('otp.verify')->with('success', 'Pendaftaran toko berhasil! Masukkan kode OTP yang dikirimkan ke WhatsApp toko Anda.');
    }
    
    // Menampilkan Dashboard Vendor
    public function dashboard()
    {
        return view('vendor.dashboard'); 
    }
}