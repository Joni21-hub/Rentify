<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Barang;
use App\Models\Kategori;

class MarketplaceController extends Controller
{
    public function search(Request $request)
    {
        $keyword = $request->input('q');
        $kategoriId = $request->input('kategori');

        // GEMBOK KEAMANAN: Wajib is_approved == 1 (Sudah di-ACC Admin) & Stok > 0
        $query = Barang::with(['vendor', 'kategori'])
            ->where('is_approved', 1) 
            ->where('stok_total', '>', 0);

        if (!empty($keyword)) {
            $query->where(function($q) use ($keyword) {
                $q->where('nama', 'like', '%' . $keyword . '%')
                  ->orWhere('deskripsi', 'like', '%' . $keyword . '%')
                  ->orWhereHas('vendor', function($v) use ($keyword) {
                      $v->where('name', 'like', '%' . $keyword . '%');
                  });
            });
        }

        if (!empty($kategoriId)) {
            $query->where('kategori_id', $kategoriId);
        }

        $barangs = $query->latest()->paginate(12)->withQueryString();
        $kategoris = Kategori::all();

        return view('customer.search', compact('barangs', 'kategoris', 'keyword', 'kategoriId'));
    }
}