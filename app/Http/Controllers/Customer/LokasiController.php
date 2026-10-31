<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LokasiController extends Controller
{
    public function index()
    {
        $lat = session('user_latitude');
        $lon = session('user_longitude');
        $alamat = session('user_alamat');
        $radius = session('user_radius', 50);

        if (Auth::check()) {
            $user = Auth::user();
            $lat = $user->latitude ?: $lat;
            $lon = $user->longitude ?: $lon;
            $alamat = $user->alamat_lengkap ?: $alamat;
            $radius = $user->radius ?: $radius;
        }

        $radius = max(5, min(50, (int) $radius));

        return view('customer.lokasi', compact('lat', 'lon', 'alamat', 'radius'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'latitude' => 'required',
            'longitude' => 'required',
            'alamat_lengkap' => 'required',
            'radius' => 'nullable|numeric|min:5|max:50'
        ]);

        $radius = max(5, min(50, (int) ($request->radius ?: 50)));

        // Simpan koordinat dan radius ke Session memori browser (Berlaku untuk Tamu & Member)
        session([
            'user_latitude' => $request->latitude,
            'user_longitude' => $request->longitude,
            'user_alamat' => $request->alamat_lengkap,
            'user_radius' => $radius
        ]);

        // Jika pengguna sudah login, simpan juga secara permanen ke Database profil
        if (Auth::check()) {
            $user = Auth::user();
            $user->latitude = $request->latitude;
            $user->longitude = $request->longitude;
            $user->alamat_lengkap = $request->alamat_lengkap;
            $user->radius = $radius;
            $user->save();
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Titik lokasi & radius ' . $radius . ' KM berhasil diatur! Menampilkan barang di sekitar Anda.',
                'alamat' => $request->alamat_lengkap,
                'radius' => $radius
            ]);
        }

        return redirect()->route('customer.home')->with('success', 'Titik lokasi & radius ' . $radius . ' KM berhasil diatur! Menampilkan barang di sekitar Anda.');
    }
}
