<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LokasiController extends Controller
{
    public function index()
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu.');
        }
        return view('customer.lokasi');
    }

    public function store(Request $request)
    {
        $request->validate([
            'latitude' => 'required',
            'longitude' => 'required',
            'alamat_lengkap' => 'required'
        ]);

        $user = Auth::user();
        $user->latitude = $request->latitude;
        $user->longitude = $request->longitude;
        $user->alamat_lengkap = $request->alamat_lengkap;
        $user->save();

        return redirect()->route('customer.home')->with('success', 'Lokasi Anda berhasil disimpan! Sekarang Anda bisa melihat barang di sekitar Anda.');
    }
}