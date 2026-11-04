<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Barang;
use App\Models\Banner;
use App\Models\User;
use App\Models\Kategori;
use App\Models\Voucher;
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
        $pendingBarangs = Barang::with(['vendor', 'kategori', 'fotos'])->where('status_barang', 'pending')->latest()->get();
        $pendingVendors = User::where('role', 'vendor')->where('vendor_status', 'pending')->latest()->get();

        $allBarangs = Barang::with(['vendor', 'kategori', 'fotos'])->latest()->get();
        $allVendors = User::where('role', 'vendor')->latest()->get();
        $allCustomers = User::where('role', 'customer')->latest()->get();
        $kategoris = Kategori::where('is_active', 1)->orderBy('nama')->get();
        
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
        $allVouchers = Voucher::with('vendor')->latest()->get();

        $voucherStats = [
            'total' => $allVouchers->count(),
            'platform_count' => $allVouchers->where('funding_source', 'platform')->count(),
            'vendor_count' => $allVouchers->where('funding_source', '!=', 'platform')->count(),
            'platform_kuota_total' => (int) $allVouchers->where('funding_source', 'platform')->sum('kuota_total'),
            'platform_kuota_terpakai' => (int) $allVouchers->where('funding_source', 'platform')->sum('kuota_terpakai'),
            'platform_kuota_reserved' => (int) $allVouchers->where('funding_source', 'platform')->sum('kuota_reserved'),
            'platform_anggaran_total' => (float) $allVouchers->where('funding_source', 'platform')->sum('anggaran_maksimal_platform'),
            'platform_anggaran_terpakai' => (float) $allVouchers->where('funding_source', 'platform')->sum('anggaran_terpakai'),
            'total_active' => $allVouchers->where('is_active', 1)->count(),
            'total_suspended' => $allVouchers->where('is_active', 0)->count(),
        ];

        // --- PUSAT NOTIFIKASI & FEED AKTIVITAS TERPADU (GOD-MODE) ---
        $pendingWithdrawals = DB::table('withdrawals')
            ->leftJoin('users', 'withdrawals.vendor_id', '=', 'users.id')
            ->where('withdrawals.status', 'pending')
            ->select('withdrawals.*', 'users.vendor_name', 'users.name as owner_name')
            ->orderBy('withdrawals.created_at', 'desc')
            ->get();

        $pendingPembayarans = DB::table('pembayarans')
            ->where('status', 'pending')
            ->count();

        // Koleksi Aktivitas Masuk Real-Time
        $activityAlerts = collect();

        foreach ($pendingBarangs as $pb) {
            $activityAlerts->push([
                'id' => 'brg_' . $pb->id,
                'type' => 'barang',
                'title' => 'Pengajuan Produk Baru',
                'item_name' => $pb->nama,
                'sender' => $pb->vendor?->vendor_name ?? 'Vendor',
                'owner' => $pb->vendor?->name ?? '-',
                'badge' => 'Kurasi Produk',
                'badge_color' => 'bg-amber-100 text-amber-800 border-amber-200',
                'icon' => 'fa-boxes',
                'icon_bg' => 'bg-amber-50 text-amber-600',
                'time' => $pb->created_at,
                'payload' => $pb,
            ]);
        }

        foreach ($pendingVendors as $pv) {
            $activityAlerts->push([
                'id' => 'vnd_' . $pv->id,
                'type' => 'vendor',
                'title' => 'Pendaftaran Vendor Baru',
                'item_name' => $pv->vendor_name ?? 'Toko Baru',
                'sender' => $pv->name ?? 'Calon Mitra',
                'owner' => $pv->email ?? '-',
                'badge' => 'Validasi Toko',
                'badge_color' => 'bg-purple-100 text-purple-800 border-purple-200',
                'icon' => 'fa-store',
                'icon_bg' => 'bg-purple-50 text-purple-600',
                'time' => $pv->created_at,
                'payload' => $pv,
            ]);
        }

        foreach ($pendingWithdrawals as $pw) {
            $activityAlerts->push([
                'id' => 'wd_' . $pw->id,
                'type' => 'withdrawal',
                'title' => 'Permintaan Tarik Saldo',
                'item_name' => 'Rp ' . number_format($pw->nominal, 0, ',', '.'),
                'sender' => $pw->vendor_name ?? $pw->owner_name ?? 'Vendor',
                'owner' => ($pw->nama_bank_ewallet ?? 'Bank') . ' - ' . ($pw->nomor_rekening ?? ''),
                'badge' => 'Pencairan Dana',
                'badge_color' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                'icon' => 'fa-money-bill-transfer',
                'icon_bg' => 'bg-emerald-50 text-emerald-600',
                'time' => $pw->created_at ? \Carbon\Carbon::parse($pw->created_at) : now(),
                'payload' => $pw,
            ]);
        }

        // Urutkan feed aktivitas berdasarkan tanggal terbaru
        $activityAlerts = $activityAlerts->sortByDesc(function ($item) {
            return $item['time'] ? \Carbon\Carbon::parse($item['time'])->timestamp : 0;
        })->values();

        $stats = [
            'total_pending' => $pendingBarangs->count(),
            'total_disetujui' => Barang::where('status_barang', 'disetujui')->count(),
            'total_ditolak' => Barang::where('status_barang', 'ditolak')->count(),
            'total_banner' => $banners->count(),
            'total_pending_vendor' => $pendingVendors->count(),
            'total_pending_withdrawals' => $pendingWithdrawals->count(),
            'total_customer' => $allCustomers->count(),
            'total_vendor_total' => $allVendors->count(),
            'total_unread_alerts' => $pendingBarangs->count() + $pendingVendors->count() + $pendingWithdrawals->count() + $pendingPembayarans
        ];

        return view('admin.dashboard', compact(
            'banners', 'pendingBarangs', 'pendingVendors', 
            'allBarangs', 'allVendors', 'allCustomers', 'stats', 
            'allTransaksi', 'allVouchers', 'voucherStats', 'kategoris', 'pendingWithdrawals', 'activityAlerts'
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

    // --- FITUR BARU: MODERASI & MANAJEMEN VOUCHER ADMIN ---
    
    public function storeVoucher(Request $request)
    {
        $request->validate([
            'kode_voucher' => 'required|string|max:50|unique:vouchers,kode_voucher',
            'tipe_diskon' => 'required|in:persen,nominal,bogo_2_bayar_1,bogo_3_bayar_2',
            'nilai_diskon' => 'required|numeric|min:0',
            'maksimal_diskon' => 'nullable|numeric|min:0',
            'minimal_belanja' => 'nullable|numeric|min:0',
            'min_quantity' => 'nullable|integer|min:1',
            'kuota_total' => 'nullable|integer|min:1',
            'anggaran_maksimal_platform' => 'nullable|numeric|min:0',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'visibilitas' => 'required|in:publik,rahasia',
            'cakupan_produk' => 'required|in:seluruh_toko,produk_tertentu',
        ], [
            'kode_voucher.unique' => 'Kode voucher ini sudah digunakan, silakan gunakan kode lain.',
            'tanggal_selesai.after_or_equal' => 'Tanggal selesai tidak boleh sebelum tanggal mulai.',
        ]);

        $kodeClean = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', (string) $request->kode_voucher));

        $tipeDiskon = $request->tipe_diskon;
        $nilaiDiskon = (float) $request->nilai_diskon;
        $minQuantity = (int) ($request->min_quantity ?: 1);

        if ($tipeDiskon === 'bogo_2_bayar_1') {
            $tipeDiskon = 'persen';
            $nilaiDiskon = 50.00;
            $minQuantity = 2;
        } elseif ($tipeDiskon === 'bogo_3_bayar_2') {
            $tipeDiskon = 'persen';
            $nilaiDiskon = 33.3333;
            $minQuantity = 3;
        }

        Voucher::create([
            'vendor_id' => null, // Platform master voucher
            'kode_voucher' => $kodeClean,
            'tipe_voucher' => 'platform',
            'funding_source' => 'platform',
            'tipe_diskon' => $tipeDiskon,
            'nilai_diskon' => $nilaiDiskon,
            'maksimal_diskon' => $request->filled('maksimal_diskon') ? (float) $request->maksimal_diskon : null,
            'minimal_belanja' => (float) ($request->minimal_belanja ?? 0.0),
            'min_quantity' => $minQuantity,
            'kuota_total' => $request->filled('kuota_total') ? (int) $request->kuota_total : 100,
            'kuota_terpakai' => 0,
            'kuota_reserved' => 0,
            'anggaran_maksimal_platform' => $request->filled('anggaran_maksimal_platform') ? (float) $request->anggaran_maksimal_platform : null,
            'anggaran_terpakai' => 0.0,
            'anggaran_reserved' => 0.0,
            'tanggal_mulai' => $request->tanggal_mulai,
            'tanggal_selesai' => $request->tanggal_selesai,
            'visibilitas' => $request->visibilitas ?? 'publik',
            'cakupan_produk' => $request->cakupan_produk ?? 'seluruh_toko',
            'is_active' => 1,
            'suspended_by_admin' => 0,
            'auto_exhausted' => 0,
        ]);

        return redirect('/admin/dashboard#tab-voucher')->with('success', "Voucher Master Platform [{$kodeClean}] berhasil ditambahkan secara manual!");
    }

    public function destroyVoucher(string $id)
    {
        $voucher = Voucher::findOrFail($id);
        $kode = $voucher->kode_voucher;

        if (\App\Models\OrderVoucherRedemption::where('voucher_id', $id)->exists()) {
            return redirect('/admin/dashboard#tab-voucher')->with('error', "Gagal menghapus voucher [{$kode}]! Voucher telah memiliki riwayat transaksi/redemption.");
        }

        $voucher->delete();
        return redirect('/admin/dashboard#tab-voucher')->with('success', "Voucher [{$kode}] berhasil dihapus permanen.");
    }

    public function clearPlatformVouchers()
    {
        $deletedCount = Voucher::where('funding_source', 'platform')->delete();
        return redirect('/admin/dashboard#tab-voucher')->with('success', "Berhasil membersihkan {$deletedCount} voucher platform. Katalog voucher kini bersih dan siap diisi manual.");
    }

    public function suspendVoucher(string $id)
    {
        DB::table('vouchers')->where('id', $id)->update([
            'is_active' => 0,
            'suspended_by_admin' => 1,
            'updated_at' => now(),
        ]);
        return redirect('/admin/dashboard#tab-voucher')->with('error', "Voucher telah DIMATIKAN PAKSA! Customer tidak akan bisa lagi mengklaim atau memakai voucher ini.");
    }

    public function activateVoucher(string $id)
    {
        DB::table('vouchers')->where('id', $id)->update([
            'is_active' => 1,
            'suspended_by_admin' => 0,
            'updated_at' => now(),
        ]);
        return redirect('/admin/dashboard#tab-voucher')->with('success', "Voucher diaktifkan kembali! Customer kini dapat menggunakannya kembali.");
    }
}
