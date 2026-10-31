<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User; 

class AdminVendorController extends Controller
{
    public function index()
    {
        $vendors = User::where('role', 'vendor')->orderBy('created_at', 'desc')->get();
        return view('admin.vendors.index', compact('vendors'));
    }

    public function validasiVendor()
    {
        $vendors = User::where('role', 'vendor')->orderBy('created_at', 'desc')->get();
        return view('admin.vendors.index', compact('vendors'));
    }

    /**
     * Masuk langsung ke dashboard toko vendor (God-Mode Impersonate)
     */
    public function show($id)
    {
        return $this->impersonate($id);
    }

    /**
     * Masuk ke Toko Vendor (Impersonate) sebagai Administrator
     * Admin benar-benar masuk ke dashboard toko tersebut dan dapat melihat seluruh aktivitas & mengubah data secara riil.
     */
    public function impersonate($id)
    {
        $currentAuth = Auth::user();
        $isActualAdmin = ($currentAuth && $currentAuth->role === 'admin');
        $hasAdminSession = session()->has('admin_impersonator_id');

        if (!$isActualAdmin && !$hasAdminSession) {
            abort(403, 'Akses ditolak. Hanya Administrator yang berhak mengelola toko vendor.');
        }

        // Simpan ID admin asli di session jika belum tersimpan
        if ($isActualAdmin && !$hasAdminSession) {
            session(['admin_impersonator_id' => $currentAuth->id]);
        }

        /** @var User $vendor */
        $vendor = User::findOrFail($id);
        if (!$vendor->isVendor() && $vendor->role !== 'vendor') {
            return redirect()->back()->with('error', 'Pengguna ini bukan akun mitra vendor.');
        }

        // Pastikan vendor ditandai terverifikasi agar tidak terhambat middleware verified
        if (!$vendor->hasVerifiedEmail()) {
            $vendor->markEmailAsVerified();
        }

        // Login sebagai vendor
        Auth::login($vendor);
        session(['active_role' => 'vendor']);

        $storeName = $vendor->vendor_name ?: $vendor->name;
        return redirect()->route('vendor.dashboard')->with('success', "Anda berhasil masuk ke Toko {$storeName}! Anda sekarang dapat melihat seluruh transaksi & mengubah data toko.");
    }

    /**
     * Keluar dari Toko Vendor dan kembali ke sesi Administrator
     */
    public function leaveImpersonate()
    {
        $adminId = session('admin_impersonator_id');
        if (!$adminId) {
            return redirect()->route('admin.dashboard');
        }

        /** @var User|null $admin */
        $admin = User::find($adminId);
        if ($admin instanceof User && $admin->role === 'admin') {
            session()->forget('admin_impersonator_id');
            Auth::login($admin);
            session(['active_role' => 'admin']);
            return redirect()->route('admin.dashboard')->with('success', 'Sesi kelola toko selesai. Anda telah kembali ke Dashboard Administrator.');
        }

        return redirect()->route('admin.dashboard');
    }

    /**
     * Menyetujui pendaftaran Vendor Baru dari Dashboard
     */
    public function approveVendor($id)
    {
        $vendor = User::findOrFail($id);
        $vendor->vendor_status = 'approved';
        $vendor->save();

        return redirect()->back()->with('success', 'Kemitraan Vendor ' . $vendor->vendor_name . ' berhasil disetujui!');
    }

    /**
     * Menolak pendaftaran Vendor Baru dari Dashboard
     */
    public function rejectVendor($id)
    {
        $vendor = User::findOrFail($id);
        $vendor->vendor_status = 'rejected';
        $vendor->save();

        return redirect()->back()->with('error', 'Pendaftaran Vendor ' . $vendor->vendor_name . ' telah ditolak.');
    }

    /**
     * Verifikasi Vendor melalui route patch vendors.verify
     */
    public function verify($id)
    {
        return $this->approveVendor($id);
    }
}
