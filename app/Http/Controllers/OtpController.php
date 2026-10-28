<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use App\Services\WhatsAppService;
use App\Services\SecurityAlertService;

class OtpController extends Controller
{
    public function showVerifyForm()
    {
        // Pastikan ada session user id sebelum menampilkan form OTP
        if (!session()->has('otp_user_id')) {
            return redirect('/login')->with('error', 'Sesi verifikasi tidak valid. Silakan login kembali.');
        }

        $userId = session('otp_user_id');
        $user = User::find($userId);

        if (!$user instanceof User) {
            session()->forget(['otp_user_id', 'otp_code', 'otp_phone']);
            return redirect('/login')->with('error', 'Sesi verifikasi tidak valid. Silakan login kembali.');
        }

        $nomorWa = $user->role === 'vendor' ? $user->whatsapp_vendor : $user->whatsapp;
        $isGoogleOrEmailCustomer = ($user->role === 'customer' && ($user->google_id || $user->hasRealEmail()));

        // Jika user tidak memiliki nomor WhatsApp atau merupakan akun Google/Gmail,
        // jangan kunci mereka di halaman verifikasi OTP WhatsApp. Langsung login & redirect.
        if (empty($nomorWa) || $isGoogleOrEmailCustomer) {
            session()->forget(['otp_user_id', 'otp_code', 'otp_phone']);
            Auth::login($user);
            return (new AuthController())->redirectByRole();
        }

        return view('auth.otp-verify');
    }

    public function verify(Request $request)
    {
        $request->validate([
            'otp' => 'required|numeric|digits:6',
        ]);

        if (!session()->has('otp_user_id')) {
            return redirect('/login')->with('error', 'Sesi OTP Anda telah kedaluwarsa. Silakan login ulang.');
        }

        $userId = session('otp_user_id');
        $user = User::find($userId);

        if ($user instanceof User) {
            $nomorWa = $user->role === 'vendor' ? $user->whatsapp_vendor : $user->whatsapp;
            $isGoogleOrEmailCustomer = ($user->role === 'customer' && ($user->google_id || $user->hasRealEmail()));
            if (empty($nomorWa) || $isGoogleOrEmailCustomer) {
                session()->forget(['otp_user_id', 'otp_code', 'otp_phone']);
                Auth::login($user);
                return (new AuthController())->redirectByRole();
            }
        }

        if (!session()->has('otp_code')) {
            return redirect('/login')->with('error', 'Sesi OTP Anda telah kedaluwarsa. Silakan login ulang.');
        }

        $userId = session('otp_user_id');
        $ip = $request->ip() ?? 'UNKNOWN_IP';
        $throttleKey = 'otp_verify_attempt:' . md5($userId . '|' . $ip);
        $maxAttempts = (int) config('security.rate_limits.otp_verify_max_attempts', 5);
        $decaySeconds = ((int) config('security.rate_limits.otp_verify_decay_minutes', 15)) * 60;

        if (RateLimiter::tooManyAttempts($throttleKey, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            $minutes = ceil($seconds / 60);

            SecurityAlertService::notify(
                'Brute Force Kode OTP Terdeteksi',
                "Percobaan tebak OTP salah {$maxAttempts}x pada User ID {$userId}. Verifikasi dikunci {$minutes} menit.",
                [
                    'ip'     => $ip,
                    'url'    => $request->fullUrl(),
                    'target' => "User ID: {$userId} (WA: " . (session('otp_phone') ?? '-') . ")",
                    'action' => "Verifikasi OTP dibekukan {$minutes} menit",
                ]
            );

            return back()->withErrors(['otp' => "Terlalu banyak percobaan kode OTP yang salah. Silakan coba lagi dalam {$minutes} menit."]);
        }

        // Cek kecocokan OTP
        if ($request->otp == session('otp_code')) {
            RateLimiter::clear($throttleKey);

            $user = User::find($userId);
            
            if ($user instanceof User) {
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

        // Catat kegagalan
        RateLimiter::hit($throttleKey, $decaySeconds);
        $attemptsLeft = RateLimiter::retriesLeft($throttleKey, $maxAttempts);

        if ($attemptsLeft <= 0) {
            SecurityAlertService::notify(
                'Brute Force Kode OTP Mencapai Batas',
                "Upaya tebak OTP salah {$maxAttempts} kali berurutan pada User ID {$userId}. Sesi terkunci.",
                [
                    'ip'     => $ip,
                    'url'    => $request->fullUrl(),
                    'target' => "User ID: {$userId} (WA: " . (session('otp_phone') ?? '-') . ")",
                    'action' => "Lockout OTP 15 menit",
                ]
            );
        }

        $msg = 'Kode OTP yang Anda masukkan salah. Silakan coba lagi.';
        if ($attemptsLeft > 0 && $attemptsLeft <= 2) {
            $msg .= " (Sisa {$attemptsLeft} kesempatan sebelum dibekukan sementara).";
        }

        return back()->withErrors(['otp' => $msg]);
    }

    public function resend(Request $request)
    {
        if (!session()->has('otp_user_id')) {
            return redirect('/login')->with('error', 'Sesi telah habis.');
        }

        $userId = session('otp_user_id');
        $ip = $request->ip() ?? 'UNKNOWN_IP';
        $resendKey = 'otp_resend_throttle:' . md5($userId . '|' . $ip);
        $maxResends = (int) config('security.rate_limits.otp_resend_max_attempts', 3);
        $decaySeconds = ((int) config('security.rate_limits.otp_resend_decay_minutes', 10)) * 60;

        if (RateLimiter::tooManyAttempts($resendKey, $maxResends)) {
            $seconds = RateLimiter::availableIn($resendKey);
            $minutes = ceil($seconds / 60);

            SecurityAlertService::notify(
                'Spam / Bombing Permintaan Kirim OTP',
                "Upaya spam pengiriman ulang OTP melebihi {$maxResends}x pada User ID {$userId}.",
                [
                    'ip'     => $ip,
                    'url'    => $request->fullUrl(),
                    'target' => "User ID: {$userId} (WA: " . (session('otp_phone') ?? '-') . ")",
                    'action' => "Kirim ulang OTP dibatasi ({$minutes} menit)",
                ]
            );

            return back()->with('error', "Terlalu sering meminta kode OTP. Mohon tunggu {$minutes} menit sebelum meminta kode baru.");
        }

        RateLimiter::hit($resendKey, $decaySeconds);

        $user = User::find($userId);

        if (!$user instanceof User) {
            return redirect('/login')->with('error', 'Pengguna tidak ditemukan.');
        }

        $nomorWa = $user->role === 'vendor' ? $user->whatsapp_vendor : $user->whatsapp;
        $isGoogleOrEmailCustomer = ($user->role === 'customer' && ($user->google_id || $user->hasRealEmail()));
        if (empty($nomorWa) || $isGoogleOrEmailCustomer) {
            session()->forget(['otp_user_id', 'otp_code', 'otp_phone']);
            Auth::login($user);
            return (new AuthController())->redirectByRole();
        }

        $nomor = session('otp_phone') ?: $nomorWa; // Nomor WA yang dikirimkan OTP (bisa WA user atau WA vendor)

        if ($nomor) {
            $newOtp = rand(100000, 999999);
            session(['otp_code' => $newOtp]);

            // Kirim via WhatsApp Gateway
            $pesan = "*RENTIFY*\n\nKode OTP (Sekali Pakai) Anda adalah: *$newOtp*.\n\nJangan berikan kode ini kepada siapapun demi keamanan akun Anda.";
            WhatsAppService::send($nomor, $pesan);

            return back()->with('success', 'Kode OTP baru telah dikirim ke WhatsApp Anda.');
        }

        return back()->with('error', 'Gagal mengirim ulang OTP.');
    }
}
