<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PenyewaanTrackingController extends Controller
{
    // Fungsi untuk menampilkan halaman Riwayat Transaksi / Pesanan
    public function index()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        return redirect()->route('customer.pesanan');
    }

    public function show($kode)
    {
        return redirect()->route('customer.pesanan');
    }
}
