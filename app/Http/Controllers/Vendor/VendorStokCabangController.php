<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Barang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VendorStokCabangController extends Controller
{
    /**
     * Menampilkan halaman kelola stok cepat untuk seluruh produk vendor
     */
    public function index(Request $request)
    {
        $vendorId = Auth::id();
        $query = Barang::with('kategori')->where('vendor_id', $vendorId);

        if ($request->filled('search')) {
            $query->where('nama', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('kategori_id')) {
            $query->where('kategori_id', $request->kategori_id);
        }

        $barangs = $query->orderBy('nama', 'asc')->get();
        $kategoris = \App\Models\Kategori::where('is_active', 1)->get();

        return view('vendor.stok.index', compact('barangs', 'kategoris'));
    }

    /**
     * Memperbarui jumlah stok fisik produk (mendukung single update atau bulk update)
     */
    public function update(Request $request)
    {
        $vendorId = Auth::id();

        // 1. Bulk update: format [ 'stocks' => [ 'barang_id' => 'stok_total' ] ]
        if ($request->has('stocks') && is_array($request->stocks)) {
            $updated = 0;
            foreach ($request->stocks as $barangId => $stok) {
                $stokInt = max(0, (int) $stok);
                $affected = Barang::where('id', $barangId)
                    ->where('vendor_id', $vendorId)
                    ->update(['stok_total' => $stokInt]);
                if ($affected) $updated++;
            }
            return back()->with('success', "Stok untuk {$updated} produk berhasil diperbarui!");
        }

        // 2. Single update: format [ 'barang_id' => id, 'stok_total' => total ]
        $request->validate([
            'barang_id' => 'required|exists:barang,id',
            'stok_total' => 'required|integer|min:0'
        ]);

        $barang = Barang::where('vendor_id', $vendorId)->findOrFail($request->barang_id);
        $barang->stok_total = (int) $request->stok_total;
        $barang->save();

        return back()->with('success', "Stok '{$barang->nama}' berhasil diubah menjadi {$barang->stok_total} unit.");
    }
}
