<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Barang;
use App\Models\Banner;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use App\Rules\ValidImage;
use App\Services\StorageService;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $banners = Banner::latest()->get();
        $pendingBarangs = Barang::with(['vendor', 'kategori'])->where('status_barang', 'pending')->latest()->get();
        $pendingVendors = User::where('role', 'vendor')->where('vendor_status', 'pending')->latest()->get();

        $allBarangs = Barang::with(['vendor', 'kategori'])->latest()->get();
        $allVendors = User::where('role', 'vendor')->latest()->get();
        $allCustomers = User::where('role', 'customer')->latest()->get();
        
        $allTransaksi = DB::table('orders')->orderBy('created_at', 'desc')->get();

        foreach ($allTransaksi as $trx) {
            $trx->items = DB::table('order_items')
                ->leftJoin('barang', 'order_items.product_id', '=', 'barang.id')
                ->leftJoin('users as vendor', 'barang.vendor_id', '=', 'vendor.id')
                ->where('order_items.order_id', $trx->id)
                ->select('order_items.*', 'barang.nama as barang_nama', 'vendor.vendor_name', 'vendor.name as owner_name')
                ->get();
        }

        // --- FITUR BARU: AMBIL DATA VOUCHER UNTUK PEMANTAUAN ADMIN ---
        // Menggunakan DB facade agar kebal dari error meskipun Model Voucher belum di-import
        $allVouchers = DB::table('vouchers')
            ->leftJoin('users', 'vouchers.vendor_id', '=', 'users.id')
            ->select('vouchers.*', 'users.vendor_name as vendor_name_rel')
            ->orderBy('vouchers.id', 'desc')
            ->get();
            
        // Memanipulasi objek agar terbaca oleh Blade UI ($v->vendor->vendor_name)
        foreach ($allVouchers as $v) {
            $v->vendor = (object)['vendor_name' => $v->vendor_name_rel];
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
            'allBarangs', 'allVendors', 'allCustomers', 'stats', 'allTransaksi', 'allVouchers'
        ));
    }

   public function storeBanner(Request $request)
    {
        $request->validate([
            'judul_promo' => 'required|string|max:255',
            'gambar' => ['required', new ValidImage(20480)],
        ]);

        $banner = new Banner();
        $banner->judul_promo = $request->judul_promo;

        if ($request->hasFile('gambar')) {
            $file = $request->file('gambar');
            if ($file instanceof \Illuminate\Http\UploadedFile) {
                $uploadedUrl = StorageService::storeMedia($file, 'banners');
                if (!str_starts_with($uploadedUrl, 'http://') && !str_starts_with($uploadedUrl, 'https://')) {
                    $uploadedUrl = StorageService::getPublicUrl($uploadedUrl);
                }

                $banner->gambar_url = $uploadedUrl;
            }
        }
        $banner->save();

        return redirect()->back()->with('success', 'Banner promosi baru berjaya dimuat naik!');
    }

    public function destroyBanner(string $id)
    {
        $banner = Banner::findOrFail($id);

        // Model event 'deleting' pada Banner akan otomatis membersihkan gambar dari Cloudinary & disk lokal
        $banner->delete();

        return redirect()->back()->with('success', 'Banner promosi berjaya dipadam.');
    }

    public function approveBarang(string $id)
    {
        $barang = Barang::findOrFail($id);
        $barang->status_barang = 'disetujui';
        $barang->is_approved = 1; 
        $barang->approved_at = now(); 
        $barang->save();

        return redirect()->back()->with('success', "Item '{$barang->nama}' diluluskan! Kini dipaparkan di katalog Customer.");
    }

    public function rejectBarang(string $id)
    {
        $barang = Barang::findOrFail($id);
        $barang->status_barang = 'ditolak';
        $barang->is_approved = 0;
        $barang->save();

        return redirect()->back()->with('error', "Item '{$barang->nama}' telah ditolak daripada sistem.");
    }

    public function destroyBarang(string $id)
    {
        $barang = Barang::findOrFail($id);
        $barang->delete();
        return redirect()->back()->with('success', 'Produk berhasil dihapus secara permanen dari sistem.');
    }

    public function destroyUser(string $id)
    {
        $user = User::findOrFail($id);
        $user->delete();
        return redirect()->back()->with('success', 'Akun pengguna/vendor berhasil dihapus dari sistem.');
    }
    
    public function suspendVendor(string $id)
    {
        $vendor = User::findOrFail($id);
        $vendor->vendor_status = 'suspended';
        $vendor->save();

        Barang::where('vendor_id', $id)->update(['is_approved' => 0]);

        return redirect()->back()->with('error', "Akses Vendor '{$vendor->vendor_name}' telah Ditangguhkan (Banned). Semua produknya otomatis disembunyikan.");
    }

    public function activateVendor(string $id)
    {
        $vendor = User::findOrFail($id);
        $vendor->vendor_status = 'approved';
        $vendor->save();

        Barang::where('vendor_id', $id)->update(['is_approved' => 1]);

        return redirect()->back()->with('success', "Banned dibuka! Vendor '{$vendor->vendor_name}' dan produknya telah aktif kembali.");
    }

    // --- FITUR BARU: MODERASI VOUCHER VENDOR (KILL-SWITCH) ---
    
    public function suspendVoucher(string $id)
    {
        DB::table('vouchers')->where('id', $id)->update(['is_active' => 0]);
        return redirect()->back()->with('error', "Voucher telah DIMATIKAN PAKSA! Customer tidak akan bisa lagi mengklaim voucher ini.");
    }

    public function activateVoucher(string $id)
    {
        DB::table('vouchers')->where('id', $id)->update(['is_active' => 1]);
        return redirect()->back()->with('success', "Voucher diaktifkan kembali! Customer kini bisa menggunakannya lagi.");
    }
}