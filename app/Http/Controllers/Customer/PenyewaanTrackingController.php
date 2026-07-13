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

        // Untuk sementara kita arahkan ke halaman view kosong dulu
        // Nanti bisa ditambahkan logika untuk menarik data pesanan dari database
        return view('customer.pesanan');
    }
}