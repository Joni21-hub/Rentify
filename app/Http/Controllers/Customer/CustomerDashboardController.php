<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use App\Models\Penyewaan;

class CustomerDashboardController extends Controller
{
    public function index()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // Hitung jumlah pesanan berdasarkan status untuk badge notifikasi
        $countMenunggu = Penyewaan::where('user_id', $user->id)
            ->whereIn('status', ['Menunggu Konfirmasi', 'Menunggu Pembayaran'])
            ->count();
            
        $countDiproses = Penyewaan::where('user_id', $user->id)
            ->where('status', 'Berjalan')
            ->count();
            
        $countDikirim  = 0; // Rentify menggunakan 'Berjalan' untuk status aktif
        
        $countSelesai  = Penyewaan::where('user_id', $user->id)
            ->where('status', 'Selesai')
            ->count();

        return view('customer.dashboard', compact('countMenunggu', 'countDiproses', 'countDikirim', 'countSelesai'));
    }

    public function settings()
    {
        $user = Auth::user();
        return view('customer.settings.index', compact('user'));
    }

    // --- PROFIL ---
    public function settingsProfile()
    {
        $user = Auth::user();
        return view('customer.settings.profile', compact('user'));
    }

    public function updateProfile(Request $request)
    {
        $user = User::find(Auth::id());

        // Validasi: untuk ganti profil wajib masukkan kata sandi
        $request->validate([
            'name' => 'required|string|max:255',
            'password_konfirmasi' => 'required|string',
            'foto_profil' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        // Verifikasi kata sandi
        if (!Hash::check($request->password_konfirmasi, $user->password)) {
            return back()->with('error', 'Kata sandi tidak sesuai. Perubahan tidak disimpan.');
        }

        $user->name = $request->name;

        if ($request->hasFile('foto_profil')) {
            try {
                $cloudinaryUrl = env('CLOUDINARY_URL') ?: getenv('CLOUDINARY_URL');
                $cloudinary = new \Cloudinary\Cloudinary($cloudinaryUrl);

                // Hapus foto lama jika ada di Cloudinary
                if ($user->foto_profil && str_contains($user->foto_profil, 'cloudinary')) {
                    try {
                        $path = parse_url($user->foto_profil, PHP_URL_PATH);
                        $pathSegments = explode('/', $path);
                        $uploadIndex = array_search('upload', $pathSegments);
                        if ($uploadIndex !== false) {
                            $sliced = array_slice($pathSegments, $uploadIndex + 1);
                            if (isset($sliced[0]) && preg_match('/^v\d+$/', $sliced[0])) array_shift($sliced);
                            $publicIdWithExt = implode('/', $sliced);
                            $publicId = pathinfo($publicIdWithExt, PATHINFO_FILENAME);
                            $dir = pathinfo($publicIdWithExt, PATHINFO_DIRNAME);
                            $finalId = $dir !== '.' ? $dir . '/' . $publicId : $publicId;
                            $cloudinary->uploadApi()->destroy($finalId);
                        }
                    } catch (\Exception $e) {}
                }

                $result = $cloudinary->uploadApi()->upload(
                    $request->file('foto_profil')->getRealPath(),
                    ['folder' => 'rentify/customer_profil']
                );
                $user->foto_profil = $result['secure_url'];
            } catch (\Exception $e) {
                return back()->with('error', 'Gagal mengunggah foto: ' . $e->getMessage());
            }
        }

        $user->save();
        return redirect()->route('customer.settings')->with('success', 'Profil berhasil diperbarui!');
    }

    // --- KATA SANDI ---
    public function settingsPassword()
    {
        return view('customer.settings.password');
    }

    public function updatePassword(Request $request)
    {
        $user = User::find(Auth::id());

        // Tentukan apakah user Google yang PERTAMA KALI set password
        $isGoogleFirstTime = $user->google_id && is_null($user->password_changed_at);

        $rules = [
            'password' => 'required|string|min:8|confirmed',
        ];

        if ($isGoogleFirstTime) {
            // User Google belum pernah set password → password_lama TIDAK wajib
            $rules['password_lama'] = 'nullable|string';
        } else {
            // User manual ATAU user Google yang sudah pernah set password → password_lama WAJIB
            $rules['password_lama'] = 'required|string';
        }

        $request->validate($rules);

        // Cek kecocokan password lama jika wajib diisi
        if (!$isGoogleFirstTime) {
            if (!Hash::check($request->password_lama, $user->password)) {
                return back()->with('error', 'Kata sandi lama tidak sesuai.');
            }
        }

        $user->password           = Hash::make($request->password);
        $user->password_changed_at = now();
        $user->save();

        // Kirim notifikasi WhatsApp sebagai peringatan keamanan
        if ($user->whatsapp) {
            $pesan = "*RENTIFY* 🔐\n\nKata sandi akun Anda baru saja *diubah* pada " . now()->format('d M Y H:i') . ".\n\nJika ini BUKAN tindakan Anda, segera hubungi kami di wa.me/6283183494835.";
            \App\Services\WhatsAppService::send($user->whatsapp, $pesan);
        }

        return redirect()->route('customer.settings')->with('success', 'Kata sandi berhasil diubah! Notifikasi keamanan telah dikirim ke WhatsApp Anda.');
    }

    // --- WHATSAPP (UBAH NOMOR DENGAN OTP) ---
    public function settingsWhatsapp()
    {
        $user = Auth::user();
        return view('customer.settings.whatsapp', compact('user'));
    }

    public function updateWhatsapp(Request $request)
    {
        $user = User::find(Auth::id());
        $request->validate([
            'password_konfirmasi' => 'required|string',
            'whatsapp_baru'       => 'required|string|max:20|unique:users,whatsapp',
        ]);

        // Verifikasi kata sandi
        if (!Hash::check($request->password_konfirmasi, $user->password)) {
            return back()->with('error', 'Kata sandi tidak sesuai.');
        }

        // Buat OTP
        $otp = rand(100000, 999999);
        session([
            'otp_wa_baru'      => $request->whatsapp_baru,
            'otp_code_change'  => $otp,
        ]);

        // Kirim OTP via Fonnte
        $pesan = "*RENTIFY*\n\nKode OTP untuk mengubah Nomor WhatsApp Anda adalah: *$otp*.\n\nJangan berikan kode ini kepada siapapun.";
        \App\Services\WhatsAppService::send($request->whatsapp_baru, $pesan);

        return redirect()->route('customer.settings.whatsapp')->with('otp_sent', true)->with('success', 'OTP telah dikirim ke nomor baru Anda.');
    }

    public function verifyWhatsapp(Request $request)
    {
        $request->validate(['otp' => 'required|numeric']);

        if ($request->otp != session('otp_code_change')) {
            return back()->with('otp_sent', true)->with('error', 'Kode OTP tidak valid.');
        }

        $user = User::find(Auth::id());
        $user->whatsapp = session('otp_wa_baru');
        $user->save();

        session()->forget(['otp_wa_baru', 'otp_code_change']);

        return redirect()->route('customer.settings')->with('success', 'Nomor WhatsApp berhasil diubah!');
    }

    // --- EMAIL (UBAH EMAIL DENGAN OTP) ---
    public function settingsEmail()
    {
        $user = Auth::user();
        return view('customer.settings.email', compact('user'));
    }

    // Step 1: Validasi sandi + kirim OTP ke email baru
    public function requestEmailChange(Request $request)
    {
        $user = User::find(Auth::id());
        $request->validate([
            'password_konfirmasi' => 'required|string',
            'email_baru'          => 'required|email|unique:users,email',
        ]);

        if (!Hash::check($request->password_konfirmasi, $user->password)) {
            return back()->with('error', 'Kata sandi tidak sesuai.');
        }

        $otp = rand(100000, 999999);
        session([
            'otp_email_baru'    => $request->email_baru,
            'otp_code_email'    => $otp,
            'otp_email_expires' => now()->addMinutes(10),
        ]);

        // Kirim OTP ke email baru via Mail
        \Mail::raw("Kode OTP Rentify untuk mengganti email Anda: $otp\n\nKode berlaku 10 menit. Jangan berikan kepada siapapun.", function ($msg) use ($request) {
            $msg->to($request->email_baru)
                ->subject('Kode OTP Ganti Email - Rentify');
        });

        return redirect()->route('customer.settings.email')->with('otp_email_sent', true);
    }

    // Step 2: Verifikasi OTP dan simpan email baru
    public function verifyEmailChange(Request $request)
    {
        $request->validate(['otp_email' => 'required|numeric']);

        if (session('otp_email_expires') && now()->gt(session('otp_email_expires'))) {
            return back()->with('otp_email_sent', true)->with('error', 'Kode OTP sudah kedaluwarsa. Minta ulang.');
        }

        if ($request->otp_email != session('otp_code_email')) {
            return back()->with('otp_email_sent', true)->with('error', 'Kode OTP tidak valid.');
        }

        $user = User::find(Auth::id());
        $user->email             = session('otp_email_baru');
        $user->email_verified_at = null; // reset verifikasi
        $user->save();

        session()->forget(['otp_email_baru', 'otp_code_email', 'otp_email_expires']);

        // Notifikasi WA
        if ($user->whatsapp) {
            \App\Services\WhatsAppService::send($user->whatsapp, "*RENTIFY SECURITY*\n\nEmail akun Anda baru saja diubah menjadi: {$user->email}\n\nJika bukan Anda yang melakukan ini, segera hubungi admin.");
        }

        return redirect()->route('customer.settings')->with('success', 'Email berhasil diubah!');
    }
}