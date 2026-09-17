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
        // PERBAIKAN: Menambahkan validasi wajib centang S&K (terms) dan pesan error khusus
        $request->validate([
            'name'            => 'required|string|max:255',
            'vendor_name'     => 'required|string|max:255',
            'email'           => 'required|string|email|max:255|unique:users',
            'whatsapp_vendor' => 'required|string|max:20',
            'password'        => 'required|string|min:8|confirmed',
            'terms'           => 'accepted', // <- INI KUNCI VALIDASINYA
        ], [
            'terms.accepted'  => 'Pendaftaran gagal. Anda wajib menyatakan data asli dan menyetujui Syarat & Ketentuan Rentify.',
        ]);

        // 2. Simpan user baru ke database beserta data tokonya
        User::create([
            'name'            => $request->name,
            'vendor_name'     => $request->vendor_name,
            'email'           => $request->email,
            'whatsapp_vendor' => $request->whatsapp_vendor,
            'password'        => Hash::make($request->password),
            
            // Pengaturan default untuk Vendor
            'role'            => 'vendor',
            'vendor_status'   => 'pending', 
        ]);

        // 3. Arahkan ke halaman sukses
        return redirect()->route('vendor.register.success')->with('success', 'Pendaftaran berhasil! Menunggu persetujuan admin.');
    }
    
    // Menampilkan Dashboard Vendor
    public function dashboard()
    {
        return view('vendor.dashboard'); 
    }
}