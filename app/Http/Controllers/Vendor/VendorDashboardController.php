<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Barang;
use App\Models\Penyewaan;

class VendorDashboardController extends Controller
{
    public function index()
    {
        if (Auth::user()->role !== 'vendor') {
            return redirect('/')->with('error', 'Anda tidak memiliki akses ke halaman Vendor.');
        }

        $user = Auth::user();
        
        $totalProduk = Barang::where('vendor_id', $user->id)->count();
        $produkAktif = Barang::where('vendor_id', $user->id)->where('status_barang', 'disetujui')->count();
        $jmlPending = Barang::where('vendor_id', $user->id)->where('status_barang', 'pending')->count();

        $jmlPesanan = Penyewaan::whereHas('details.barang', function($query) use ($user) {
            $query->where('vendor_id', $user->id);
        })->whereIn('status', ['Menunggu Konfirmasi', 'Sedang Disewa', 'dibayar', 'berjalan'])->count();
        
        $totalPenyewaan = Penyewaan::whereHas('details.barang', function($query) use ($user) {
            $query->where('vendor_id', $user->id);
        })->where('status', 'Selesai')->count();

        $saldoVendor = DB::table('saldos')->where('vendor_id', $user->id)->first();
        $totalSaldo = $saldoVendor ? $saldoVendor->saldo_aktif : 0;

        // GRAFIK PENDAPATAN ASLI (7 Hari Terakhir - Dipotong Fee Platform 5%)
        $chartData = [];
        $chartLabels = [];
        
        for ($i = 6; $i >= 0; $i--) {
            $tanggal = now()->subDays($i);
            $chartLabels[] = $tanggal->translatedFormat('D'); // Menghasilkan: Sen, Sel, Rab, dst
            
            // Hitung total harga transaksi 'Selesai' pada tanggal tersebut
            $pemasukanHarian = Penyewaan::whereHas('details.barang', function($query) use ($user) {
                $query->where('vendor_id', $user->id);
            })
            ->where('status', 'Selesai')
            ->whereDate('updated_at', $tanggal->format('Y-m-d'))
            ->sum('total_price');

            // Hitung harga asli setelah dipotong 5% (Total Price / 1.05)
            $pendapatanBersih = $pemasukanHarian > 0 ? ($pemasukanHarian / 1.05) : 0;
            $chartData[] = $pendapatanBersih;
        }

        return view('vendor.dashboard', compact(
            'user', 'totalProduk', 'produkAktif', 'jmlPending', 
            'jmlPesanan', 'totalPenyewaan', 'totalSaldo', 'chartData', 'chartLabels'
        ));
    }
}