<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Wishlist;
use App\Models\Barang;

class WishlistController extends Controller
{
    // 1. Fungsi menampilkan halaman Favorit
    public function index()
    {
        if (!Auth::check()) return redirect()->route('login');
        
        
        $wishlists = Wishlist::with('barang')
                        ->whereHas('barang') 
                        ->where('user_id', Auth::id())
                        ->latest()
                        ->get();
                        
        return view('customer.wishlist', compact('wishlists')); 
    }

    // 2. Fungsi Tombol Hati (Tambah/Hapus Favorit)
    public function toggle($id)
    {
        if (!Auth::check()) return redirect()->route('login');

        $user_id = Auth::id();
        $cekFavorit = Wishlist::where('user_id', $user_id)->where('barang_id', $id)->first();

        if ($cekFavorit) {
           
            $cekFavorit->delete();
        } else {
           
            Wishlist::create(['user_id' => $user_id, 'barang_id' => $id]);
        }
        
        return back(); 
    }
}