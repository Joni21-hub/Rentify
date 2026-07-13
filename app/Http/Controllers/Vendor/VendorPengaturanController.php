<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class VendorPengaturanController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        return view('vendor.pengaturan.index', compact('user'));
    }

   public function update(Request $request)
    {
        $user = User::find(Auth::id());

        $request->validate([
            'vendor_name' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$user->id,
            'whatsapp_vendor' => 'nullable|string|max:20',
            'foto_profil' => 'nullable|image|mimes:jpeg,png,jpg|max:2048' // Validasi Foto
        ]);

        $user->vendor_name = $request->vendor_name;
        $user->name = $request->name;
        $user->email = $request->email;
        $user->whatsapp_vendor = $request->whatsapp_vendor;

        // PROSES UPLOAD FOTO PROFIL
        if ($request->hasFile('foto_profil')) {
            $foto = $request->file('foto_profil');
            $namaFoto = time() . '_' . $foto->hashName();
            $foto->move(public_path('vendor_profil'), $namaFoto);
            
            // Hapus foto lama jika ada
            if ($user->foto_profil && file_exists(public_path($user->foto_profil))) {
                unlink(public_path($user->foto_profil));
            }
            $user->foto_profil = 'vendor_profil/' . $namaFoto;
        }

        if ($request->filled('password')) {
            $request->validate(['password' => 'min:6|confirmed']);
            $user->password = Hash::make($request->password);
        }

        $user->save();
        return back()->with('success', 'Profil toko dan foto berhasil diperbarui! 🎉');
    }
}