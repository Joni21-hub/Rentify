<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Services\WhatsAppService;

class OtpController extends Controller
{
    public function showVerifyForm()
    {
        // Pastikan ada session user id sebelum menampilkan form OTP
        if (!session()->has('otp_user_id')) {
            return redirect('/login')->with('error', 'Sesi verifikasi tidak valid. Silakan login kembali.');
        }

        return view('auth.otp-verify');
    }

    public function verify(Request $request)
    {
        $request->validate([
            'otp' => 'required|numeric|digits:6',
        ]);

        if (!session()->has('otp_user_id') || !session()->has('otp_code')) {
            return redirect('/login')->with('error', 'Sesi OTP Anda telah kedaluwarsa. Silakan login ulang.');
        }

        // Cek kecocokan OTP
        if ($request->otp == session('otp_code')) {
            $user = User::find(session('otp_user_id'));
            
            if ($user) {
                // Tandai WA terverifikasi
                $user->update(['whatsapp_verified_at' => now()]);
                
                // Login user
                Auth::login($user);
                
                // Hapus session OTP
                session()->forget(['otp_user_id', 'otp_code', 'otp_phone']);
                
                // Pengalihan berdasarkan role
                if ($user->role == 'vendor') {
                    return redirect()->intended('/vendor/dashboard')->with('success', 'Nomor WhatsApp berhasil diverifikasi! Jangan lupa verifikasi email Anda jika belum.');
                } elseif ($user->role == 'customer') {
                    return redirect()->intended('/customer')->with('success', 'Nomor WhatsApp berhasil diverifikasi! Selamat datang di Rentify.');
                }
                
                return redirect('/');
            }
        }

        return back()->withErrors(['otp' => 'Kode OTP yang Anda masukkan salah. Silakan coba lagi.']);
    }

    public function resend(Request $request)
    {
        if (!session()->has('otp_user_id')) {
            return redirect('/login')->with('error', 'Sesi telah habis.');
        }

        $user = User::find(session('otp_user_id'));
        $nomor = session('otp_phone'); // Nomor WA yang dikirimkan OTP (bisa WA user atau WA vendor)

        if ($user && $nomor) {
            $newOtp = rand(100000, 999999);
            session(['otp_code' => $newOtp]);

            // Kirim via Fonnte
            $pesan = "*RENTIFY*\n\nKode OTP (Sekali Pakai) Anda adalah: *$newOtp*.\n\nJangan berikan kode ini kepada siapapun demi keamanan akun Anda.";
            WhatsAppService::send($nomor, $pesan);

            return back()->with('success', 'Kode OTP baru telah dikirim ke WhatsApp Anda.');
        }

        return back()->with('error', 'Gagal mengirim ulang OTP.');
    }
}
