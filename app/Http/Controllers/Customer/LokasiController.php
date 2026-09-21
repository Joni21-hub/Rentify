<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LokasiController extends Controller
{
    public function index()
    {
        // PERBAIKAN 1: Hapus blokade login agar pengunjung (Guest) bisa melihat halaman maps
        return view('customer.lokasi');
    }

    public function store(Request $request)
    {
        $request->validate([
            'latitude' => 'required',
            'longitude' => 'required',
            'alamat_lengkap' => 'required'
        ]);

        // PERBAIKAN 2: Simpan koordinat ke Session memori browser (Berlaku untuk Tamu & Member)
        session([
            'user_latitude' => $request->latitude,
            'user_longitude' => $request->longitude,
            'user_alamat' => $request->alamat_lengkap
        ]);

        // PERBAIKAN 3: Jika pengguna sudah login, simpan juga secara permanen ke Database profil
        if (Auth::check()) {
            $user = Auth::user();
            $user->latitude = $request->latitude;
            $user->longitude = $request->longitude;
            $user->alamat_lengkap = $request->alamat_lengkap;
            $user->save();
        }

        return redirect()->route('customer.home')->with('success', 'Titik lokasi berhasil diatur! Menampilkan barang di sekitar Anda.');
    }
}