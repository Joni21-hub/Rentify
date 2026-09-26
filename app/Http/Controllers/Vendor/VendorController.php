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

    // Memproses Pendaftaran Vendor
    public function register(Request $request)
    {
        $request->validate([
            'name'            => 'required|string|max:255',
            'vendor_name'     => 'required|string|max:255',
            'email'           => 'required|string|email|max:255|unique:users',
            'whatsapp_vendor' => 'required|string|max:20|unique:users,whatsapp_vendor',
            'password'        => 'required|string|min:8|confirmed',
            'terms'           => 'accepted', 
        ], [
            'terms.accepted'  => 'Pendaftaran gagal. Anda wajib menyatakan data asli dan menyetujui Syarat & Ketentuan Rentify.',
            'whatsapp_vendor.unique' => 'Nomor WhatsApp Toko sudah terdaftar.',
        ]);

        // 2. Simpan user baru ke database beserta data tokonya
        $user = User::create([
            'name'            => $request->name,
            'vendor_name'     => $request->vendor_name,
            'email'           => $request->email,
            'whatsapp_vendor' => $request->whatsapp_vendor,
            'password'        => Hash::make($request->password),
            
            // Pengaturan default untuk Vendor
            'role'            => 'vendor',
            'vendor_status'   => 'pending', 
        ]);

        // 3. Memicu email verifikasi bawaan Laravel (verifikasi Gmail)
        event(new \Illuminate\Auth\Events\Registered($user));

        // 4. ALUR OTP WA BARU (Tidak langsung masuk ke sukses)
        $otp = rand(100000, 999999);
        session([
            'otp_user_id' => $user->id,
            'otp_code' => $otp,
            'otp_phone' => $user->whatsapp_vendor
        ]);

        // Kirim OTP via Fonnte
        $pesan = "*RENTIFY VENDOR*\n\nSelamat datang, {$user->vendor_name}!\nKode OTP pendaftaran toko Anda adalah: *$otp*.\n\nJangan berikan kode ini kepada siapapun.";
        \App\Services\WhatsAppService::send($user->whatsapp_vendor, $pesan);

        // Arahkan ke halaman verifikasi OTP
        return redirect()->route('otp.verify');
    }
    
    // Menampilkan Dashboard Vendor
    public function dashboard()
    {
        return view('vendor.dashboard'); 
    }
}