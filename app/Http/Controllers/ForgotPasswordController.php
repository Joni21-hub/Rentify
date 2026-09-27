<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Models\User;

class ForgotPasswordController extends Controller
{
    // Step 1: Tampilkan form input identitas (WA atau Email)
    public function showForm()
    {
        return view('auth.forgot-password');
    }

    // Step 2: Proses identitas, tentukan alur berdasarkan jenis akun, kirim OTP
    public function sendOtp(Request $request)
    {
        $request->validate(['identitas' => 'required|string']);
        $input = trim($request->identitas);

        // Cari user: bisa via WA (customer), email (google/vendor), atau whatsapp_vendor
        $user = User::where('whatsapp', $input)
                    ->orWhere('email', $input)
                    ->orWhere('whatsapp_vendor', $input)
                    ->first();

        if (!$user) {
            return back()->with('error', 'Akun dengan identitas tersebut tidak ditemukan.');
        }

        // Buat OTP 6 digit
        $otpWa    = rand(100000, 999999);
        $otpEmail = rand(100000, 999999);

        session([
            'reset_user_id'       => $user->id,
            'reset_otp_wa'        => $otpWa,
            'reset_otp_email'     => $otpEmail,
            'reset_otp_expires'   => now()->addMinutes(15)->toDateTimeString(),
            'reset_wa_verified'   => false,
            'reset_email_verified'=> false,
        ]);

        if ($user->role === 'vendor') {
            // Vendor: kirim OTP ke KEDUANYA
            \App\Services\WhatsAppService::send(
                $user->whatsapp_vendor,
                "*RENTIFY VENDOR*\n\nPermintaan reset kata sandi diterima.\nKode OTP WhatsApp Anda: *{$otpWa}*\n\nBerlaku 15 menit. Jangan berikan kepada siapapun."
            );
            Mail::raw(
                "Kode OTP Email reset kata sandi Rentify Anda: {$otpEmail}\n\nBerlaku 15 menit. Jangan berikan kepada siapapun.",
                fn($m) => $m->to($user->email)->subject('Reset Kata Sandi Vendor - Rentify')
            );

            return redirect()->route('password.reset.otp.form')->with('status', 'vendor_wa_step');

        } elseif ($user->google_id || !str_ends_with($user->email, '@rentify.local')) {
            // Customer Google: kirim OTP ke email
            Mail::raw(
                "Kode OTP reset kata sandi Rentify Anda: {$otpEmail}\n\nBerlaku 15 menit. Jangan berikan kepada siapapun.",
                fn($m) => $m->to($user->email)->subject('Reset Kata Sandi - Rentify')
            );

            return redirect()->route('password.reset.otp.form')->with('status', 'customer_email_step');

        } else {
            // Customer WA: kirim OTP ke WhatsApp
            \App\Services\WhatsAppService::send(
                $user->whatsapp,
                "*RENTIFY*\n\nKode OTP reset kata sandi Anda: *{$otpWa}*\n\nBerlaku 15 menit. Jangan berikan kepada siapapun."
            );

            return redirect()->route('password.reset.otp.form')->with('status', 'customer_wa_step');
        }
    }

    // Step 3: Tampilkan form OTP
    public function showOtpForm()
    {
        if (!session('reset_user_id')) {
            return redirect()->route('password.request')->with('error', 'Sesi tidak valid. Silakan mulai ulang.');
        }
        return view('auth.reset-otp');
    }

    // Step 4: Verifikasi OTP (WA atau Email, tergantung step)
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'otp'      => 'required|numeric|digits:6',
            'otp_type' => 'required|in:wa,email',
        ]);

        if (!session('reset_user_id')) {
            return redirect()->route('password.request')->with('error', 'Sesi tidak valid.');
        }

        // Cek kadaluarsa
        if (now()->gt(session('reset_otp_expires'))) {
            session()->forget([
                'reset_user_id', 'reset_otp_wa', 'reset_otp_email',
                'reset_otp_expires', 'reset_wa_verified', 'reset_email_verified',
            ]);
            return redirect()->route('password.request')
                ->with('error', 'Kode OTP sudah kedaluwarsa. Silakan ulangi proses.');
        }

        $user = User::find(session('reset_user_id'));

        if ($request->otp_type === 'wa') {
            if ($request->otp != session('reset_otp_wa')) {
                return back()
                    ->with('error', 'Kode OTP WhatsApp tidak valid.')
                    ->with('status', request()->session()->get('_flash.old.status') ?? 'customer_wa_step');
            }
            session(['reset_wa_verified' => true]);

            // Vendor: setelah WA verified, lanjut ke email OTP
            if ($user->role === 'vendor') {
                return redirect()->route('password.reset.otp.form')->with('status', 'vendor_email_step');
            }

        } elseif ($request->otp_type === 'email') {
            if ($request->otp != session('reset_otp_email')) {
                return back()
                    ->with('error', 'Kode OTP Email tidak valid.')
                    ->with('status', request()->session()->get('_flash.old.status') ?? 'customer_email_step');
            }
            session(['reset_email_verified' => true]);
        }

        // Cek apakah sudah cukup verifikasi
        $waVerified    = session('reset_wa_verified');
        $emailVerified = session('reset_email_verified');

        if ($user->role === 'vendor') {
            if (!$waVerified || !$emailVerified) {
                return redirect()->route('password.reset.otp.form')->with('status', 'vendor_email_step');
            }
        }

        // Semua verifikasi selesai → izinkan buat sandi baru
        session(['reset_can_set_password' => true]);
        return redirect()->route('password.reset.new.form');
    }

    // Step 5: Form sandi baru
    public function showNewPasswordForm()
    {
        if (!session('reset_can_set_password')) {
            return redirect()->route('password.request')->with('error', 'Akses tidak sah.');
        }
        return view('auth.reset-new-password');
    }

    // Step 6: Simpan sandi baru
    public function saveNewPassword(Request $request)
    {
        if (!session('reset_can_set_password')) {
            return redirect()->route('password.request')->with('error', 'Akses tidak sah.');
        }

        $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::find(session('reset_user_id'));
        if (!$user) {
            return redirect()->route('password.request')->with('error', 'Sesi tidak valid.');
        }

        $user->password           = Hash::make($request->password);
        $user->password_changed_at = now();
        $user->save();

        // Notifikasi keamanan
        if ($user->whatsapp) {
            \App\Services\WhatsAppService::send(
                $user->whatsapp,
                "*RENTIFY SECURITY*\n\nKata sandi akun Anda baru saja berhasil direset.\n\nJika bukan Anda yang melakukan ini, segera hubungi admin Rentify."
            );
        }
        if ($user->whatsapp_vendor) {
            \App\Services\WhatsAppService::send(
                $user->whatsapp_vendor,
                "*RENTIFY SECURITY*\n\nKata sandi akun vendor Anda baru saja berhasil direset.\n\nJika bukan Anda yang melakukan ini, segera hubungi admin Rentify."
            );
        }

        session()->forget([
            'reset_user_id', 'reset_otp_wa', 'reset_otp_email',
            'reset_otp_expires', 'reset_wa_verified', 'reset_email_verified',
            'reset_can_set_password',
        ]);

        return redirect()->route('login')
            ->with('success', 'Kata sandi berhasil direset! Silakan masuk dengan sandi baru Anda.');
    }
}
