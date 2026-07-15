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
            'foto_profil' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        $user->vendor_name = $request->vendor_name;
        $user->name = $request->name;
        $user->email = $request->email;
        $user->whatsapp_vendor = $request->whatsapp_vendor;

        // UPLOAD FOTO PROFIL KE CLOUDINARY (CARA ASLI ANTI ERROR)
        if ($request->hasFile('foto_profil')) {
            if ($user->foto_profil && str_contains($user->foto_profil, 'cloudinary')) {
                try {
                    $cloudinaryUrl = env('CLOUDINARY_URL') ?: getenv('CLOUDINARY_URL');
                    $cloudinary = new \Cloudinary\Cloudinary($cloudinaryUrl);

                    $path = parse_url($user->foto_profil, PHP_URL_PATH);
                    $pathSegments = explode('/', $path);
                    $uploadIndex = array_search('upload', $pathSegments);
                    if ($uploadIndex !== false) {
                        $slicedSegments = array_slice($pathSegments, $uploadIndex + 1);
                        if (isset($slicedSegments[0]) && preg_match('/^v\d+$/', $slicedSegments[0])) {
                            array_shift($slicedSegments);
                        }
                        $publicIdWithExt = implode('/', $slicedSegments);
                        $publicId = pathinfo($publicIdWithExt, PATHINFO_FILENAME);
                        $folderPath = pathinfo($publicIdWithExt, PATHINFO_DIRNAME);
                        $finalPublicId = $folderPath !== '.' ? $folderPath . '/' . $publicId : $publicId;
                        $cloudinary->uploadApi()->destroy($finalPublicId);
                    }
                } catch (\Exception $e) {
                    // Abaikan agar proses update profil tetap lancar
                }
            } elseif ($user->foto_profil && file_exists(public_path($user->foto_profil))) {
                @unlink(public_path($user->foto_profil));
            }

            // Upload foto baru menggunakan mesin asli Cloudinary
            $cloudinaryUrl = env('CLOUDINARY_URL') ?: getenv('CLOUDINARY_URL');
            $cloudinary = new \Cloudinary\Cloudinary($cloudinaryUrl);
            $uploadResult = $cloudinary->uploadApi()->upload($request->file('foto_profil')->getRealPath(), [
                'folder' => 'rentify/vendor_profil'
            ]);

            $user->foto_profil = $uploadResult['secure_url'];
        }

        if ($request->filled('password')) {
            $request->validate(['password' => 'min:6|confirmed']);
            $user->password = Hash::make($request->password);
        }

        $user->save();
        return back()->with('success', 'Profil toko dan foto berhasil diperbarui! 🎉');
    }
}