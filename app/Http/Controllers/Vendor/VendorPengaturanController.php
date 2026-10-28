<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Rules\ValidImage;
use App\Services\StorageService;

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
            'foto_profil' => ['nullable', new ValidImage(10240)],
        ]);

        $user->vendor_name = $request->vendor_name;
        $user->name = $request->name;
        $user->email = $request->email;
        $user->whatsapp_vendor = $request->whatsapp_vendor;

        // UPLOAD FOTO PROFIL KE CLOUDINARY (CARA ASLI ANTI ERROR)
        if ($request->hasFile('foto_profil')) {
            $file = $request->file('foto_profil');
            if ($file instanceof \Illuminate\Http\UploadedFile) {
                // Hapus foto profil lama dari Cloudinary atau disk lokal
                if ($user->foto_profil) {
                    StorageService::deleteFile($user->foto_profil);
                }

                // Unggah foto profil baru via StorageService (Cloudinary 100% HD dengan fallback disk lokal)
                $uploadedUrl = StorageService::storeMedia($file, 'vendor_profil');
                if (!str_starts_with($uploadedUrl, 'http://') && !str_starts_with($uploadedUrl, 'https://')) {
                    $uploadedUrl = StorageService::getPublicUrl($uploadedUrl);
                }

                $user->foto_profil = $uploadedUrl;
            }
        }

        if ($request->filled('password')) {
            $request->validate(['password' => 'min:6|confirmed']);
            $user->password = Hash::make($request->password);
        }

        $user->save();
        return back()->with('success', 'Profil toko dan foto berhasil diperbarui! 🎉');
    }
}