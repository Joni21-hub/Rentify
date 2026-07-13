<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Barang;
use App\Models\Banner;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB; // WAJIB DITAMBAHKAN UNTUK AKSES TABEL ORDERS

class AdminDashboardController extends Controller
{
    /**
     * Memaparkan Halaman Utama Dashboard Admin bersama semua data menu
     */
    public function index()
    {
        $banners = Banner::latest()->get();
        $pendingBarangs = Barang::with(['vendor', 'kategori'])->where('status_barang', 'pending')->latest()->get();
        $pendingVendors = User::where('role', 'vendor')->where('vendor_status', 'pending')->latest()->get();

        $allBarangs = Barang::with(['vendor', 'kategori'])->latest()->get();
        $allVendors = User::where('role', 'vendor')->latest()->get();
        $allCustomers = User::where('role', 'customer')->latest()->get();
        
        // PERBAIKAN: Ambil data dari tabel 'orders' karena CheckoutController menyimpannya ke sana
        $allTransaksi = DB::table('orders')->orderBy('created_at', 'desc')->get();

        // Cari tahu detail barang dan nama vendor untuk masing-masing transaksi
        foreach ($allTransaksi as $trx) {
            $trx->items = DB::table('order_items')
                ->leftJoin('barang', 'order_items.product_id', '=', 'barang.id')
                ->leftJoin('users as vendor', 'barang.vendor_id', '=', 'vendor.id')
                ->where('order_items.order_id', $trx->id)
                ->select('order_items.*', 'barang.nama as barang_nama', 'vendor.vendor_name', 'vendor.name as owner_name')
                ->get();
        }

        $stats = [
            'total_pending' => $pendingBarangs->count(),
            'total_disetujui' => Barang::where('status_barang', 'disetujui')->count(),
            'total_banner' => $banners->count(),
            'total_pending_vendor' => $pendingVendors->count(),
            'total_customer' => $allCustomers->count(),
            'total_vendor_total' => $allVendors->count()
        ];

        return view('admin.dashboard', compact(
            'banners', 'pendingBarangs', 'pendingVendors', 
            'allBarangs', 'allVendors', 'allCustomers', 'stats', 'allTransaksi'
        ));
    }

    public function storeBanner(Request $request)
    {
        $request->validate([
            'judul_promo' => 'required|string|max:255',
            'gambar' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $banner = new Banner();
        $banner->judul_promo = $request->judul_promo;

        if ($request->hasFile('gambar')) {
            $path = $request->file('gambar')->store('banners', 'public');
            $banner->gambar_url = Storage::url($path);
        }
        $banner->save();

        return redirect()->back()->with('success', 'Banner promosi baru berjaya dimuat naik!');
    }

    public function destroyBanner($id)
    {
        $banner = Banner::findOrFail($id);
        $filePath = str_replace('/storage/', '', $banner->gambar_url);
        if (Storage::disk('public')->exists($filePath)) {
            Storage::disk('public')->delete($filePath);
        }
        $banner->delete();

        return redirect()->back()->with('success', 'Banner promosi berjaya dipadam.');
    }

    public function approveBarang($id)
    {
        $barang = Barang::findOrFail($id);
        $barang->status_barang = 'disetujui';
        $barang->is_approved = 1; 
        $barang->approved_at = now(); 
        $barang->save();

        return redirect()->back()->with('success', "Item '{$barang->nama}' diluluskan! Kini dipaparkan di katalog Customer.");
    }

    public function rejectBarang($id)
    {
        $barang = Barang::findOrFail($id);
        $barang->status_barang = 'ditolak';
        $barang->is_approved = 0;
        $barang->save();

        return redirect()->back()->with('error', "Item '{$barang->nama}' telah ditolak daripada sistem.");
    }

    public function destroyBarang($id)
    {
        $barang = Barang::findOrFail($id);
        $barang->delete();
        return redirect()->back()->with('success', 'Produk berhasil dihapus secara permanen dari sistem.');
    }

    public function destroyUser($id)
    {
        $user = User::findOrFail($id);
        $user->delete();
        return redirect()->back()->with('success', 'Akun pengguna/vendor berhasil dihapus dari sistem.');
    }
}