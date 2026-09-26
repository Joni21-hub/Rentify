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
        return view('customer.settings', compact('user'));
    }

    public function updateSettings(Request $request)
    {
        $user = User::find(Auth::id());

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'foto_profil' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        $user->name = $request->name;
        $user->email = $request->email;

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        if ($request->hasFile('foto_profil')) {
            if ($user->foto_profil && Storage::disk('public')->exists($user->foto_profil)) {
                Storage::disk('public')->delete($user->foto_profil);
            }
            $user->foto_profil = $request->file('foto_profil')->store('profil_users', 'public');
        }

        $user->save();

        return redirect()->route('customer.dashboard')->with('success', 'Profil Anda berhasil diperbarui!');
    }
}