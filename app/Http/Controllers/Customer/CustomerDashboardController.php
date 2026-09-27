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
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'foto_profil' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $user->name = $request->name;
        $user->email = $request->email;

        if ($request->hasFile('foto_profil')) {
            if ($user->foto_profil && Storage::disk('public')->exists($user->foto_profil)) {
                Storage::disk('public')->delete($user->foto_profil);
            }
            $user->foto_profil = $request->file('foto_profil')->store('profil_users', 'public');
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

        $user->password          = Hash::make($request->password);
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
            'whatsapp_baru' => 'required|string|max:20|unique:users,whatsapp',
        ]);

        // Buat OTP
        $otp = rand(100000, 999999);
        session([
            'otp_wa_baru' => $request->whatsapp_baru,
            'otp_code_change' => $otp,
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
}