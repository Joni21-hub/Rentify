<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
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
}