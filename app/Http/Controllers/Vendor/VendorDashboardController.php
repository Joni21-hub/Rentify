<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Barang;
use App\Models\Penyewaan;
use Carbon\Carbon;

class VendorDashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if (!$user->isVendor()) {
            return redirect('/')->with('error', 'Anda tidak memiliki akses ke halaman Vendor.');
        }

        // 1. STATISTIK PRODUK & ARMADA RENTAL
        $totalProduk = Barang::where('vendor_id', $user->id)->count();
        $produkAktif = Barang::where('vendor_id', $user->id)->where('status_barang', 'disetujui')->count();
        $jmlPending  = Barang::where('vendor_id', $user->id)->where('status_barang', 'pending')->count();
        $totalUnitFisik = (int) Barang::where('vendor_id', $user->id)->sum('stok_total');

        // 2. STATISTIK PESANAN
        $pesananQuery = Penyewaan::whereHas('details.barang', function($query) use ($user) {
            $query->where('vendor_id', $user->id);
        });

        $jmlPesananAktif = (clone $pesananQuery)->whereIn('status', ['Menunggu Konfirmasi', 'Disetujui', 'Sedang Disewa', 'berjalan', 'dibayar'])->count();
        $totalPenyewaanSelesai = (clone $pesananQuery)->where('status', 'Selesai')->count();

        // Unit yang sedang aktif disewa di luar saat ini
        $unitSedangDisewa = (clone $pesananQuery)->whereIn('status', ['Sedang Disewa', 'berjalan'])->count();
        $unitStandby = max(0, $totalUnitFisik - $unitSedangDisewa);
        $tingkatKeterisian = $totalUnitFisik > 0 ? round(($unitSedangDisewa / $totalUnitFisik) * 100) : 0;

        // 3. ACTION CENTER: PESANAN MENDESAK & PENGEMBALIAN HARI INI
        $pesananMenunggu = (clone $pesananQuery)
            ->with(['customer', 'details.barang'])
            ->where('status', 'Menunggu Konfirmasi')
            ->latest()
            ->take(3)
            ->get();

        $pengembalianHariIni = (clone $pesananQuery)
            ->with(['customer', 'details.barang'])
            ->whereIn('status', ['Sedang Disewa', 'berjalan', 'Disetujui'])
            ->whereDate('tanggal_selesai', '<=', now()->toDateString())
            ->orderBy('tanggal_selesai', 'asc')
            ->take(3)
            ->get();

        // 4. KEUANGAN & SALDO TOKO
        $saldoVendor = DB::table('saldos')->where('vendor_id', $user->id)->first();
        if (!$saldoVendor) {
            DB::table('saldos')->insert([
                'vendor_id'     => $user->id,
                'saldo_aktif'   => 0,
                'saldo_ditahan' => 0,
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);
            $saldoVendor = DB::table('saldos')->where('vendor_id', $user->id)->first();
        }
        $totalSaldo = $saldoVendor ? (float) $saldoVendor->saldo_aktif : 0;

        // Estimasi Saldo Tertahan (Pesanan Sedang Berjalan / Belum Selesai)
        $omsetBerjalan = (clone $pesananQuery)->whereIn('status', ['Sedang Disewa', 'berjalan', 'Disetujui'])->sum('total_biaya');
        $saldoTertahan = $omsetBerjalan > 0 ? round($omsetBerjalan / 1.05) : 0;

        // Total pendapatan bersih bulan ini
        $omsetBulanIni = (clone $pesananQuery)
            ->where('status', 'Selesai')
            ->whereMonth('updated_at', now()->month)
            ->whereYear('updated_at', now()->year)
            ->sum('total_biaya');
        $pendapatanBulanIni = $omsetBulanIni > 0 ? round($omsetBulanIni / 1.05) : 0;

        // 5. GRAFIK PENDAPATAN (7 HARI TERAKHIR & 30 HARI TERAKHIR)
        $chart7Labels = [];
        $chart7Data = [];
        for ($i = 6; $i >= 0; $i--) {
            $tanggal = now()->subDays($i);
            $chart7Labels[] = $tanggal->translatedFormat('D, d M');
            $pemasukan = (clone $pesananQuery)
                ->where('status', 'Selesai')
                ->whereDate('updated_at', $tanggal->format('Y-m-d'))
                ->sum('total_biaya');
            $chart7Data[] = $pemasukan > 0 ? round($pemasukan / 1.05) : 0;
        }

        $chart30Labels = [];
        $chart30Data = [];
        for ($i = 29; $i >= 0; $i -= 3) {
            $tanggal = now()->subDays($i);
            $chart30Labels[] = $tanggal->translatedFormat('d M');
            $pemasukan = (clone $pesananQuery)
                ->where('status', 'Selesai')
                ->whereBetween('updated_at', [
                    $tanggal->copy()->subDays(2)->startOfDay(),
                    $tanggal->copy()->endOfDay()
                ])
                ->sum('total_biaya');
            $chart30Data[] = $pemasukan > 0 ? round($pemasukan / 1.05) : 0;
        }

        // 6. VOUCHER PROMO TOKO (PENTING UNTUK PROMOSI TOKO)
        $totalVoucher = \App\Models\Voucher::where('vendor_id', $user->id)->count();
        $vouchers = \App\Models\Voucher::where('vendor_id', $user->id)->latest()->take(3)->get();

        // Status Ringkas Pesanan (Untuk 4-Grid Status ala Marketplace Besar)
        $jmlMenungguKonfirmasi = (clone $pesananQuery)->where('status', 'Menunggu Konfirmasi')->count();
        $jmlSedangDisewa = $unitSedangDisewa;
        $jmlJatuhTempo = (clone $pesananQuery)->whereIn('status', ['Sedang Disewa', 'berjalan', 'Disetujui'])->whereDate('tanggal_selesai', '<=', now()->toDateString())->count();
        $jmlSelesai = $totalPenyewaanSelesai;

        // 7. PRODUK TERLARIS (TOP 4)
        $topProducts = Barang::where('vendor_id', $user->id)
            ->withCount(['details as total_sewa'])
            ->orderBy('total_sewa', 'desc')
            ->take(4)
            ->get();

        // 8. PESANAN TERAKHIR (TABEL MINI)
        $recentOrders = (clone $pesananQuery)
            ->with(['customer', 'details.barang'])
            ->latest()
            ->take(5)
            ->get();

        return view('vendor.dashboard', compact(
            'user',
            'totalProduk',
            'produkAktif',
            'jmlPending',
            'totalUnitFisik',
            'unitSedangDisewa',
            'unitStandby',
            'tingkatKeterisian',
            'jmlPesananAktif',
            'totalPenyewaanSelesai',
            'jmlMenungguKonfirmasi',
            'jmlSedangDisewa',
            'jmlJatuhTempo',
            'jmlSelesai',
            'pesananMenunggu',
            'pengembalianHariIni',
            'totalSaldo',
            'saldoTertahan',
            'pendapatanBulanIni',
            'totalVoucher',
            'vouchers',
            'chart7Labels',
            'chart7Data',
            'chart30Labels',
            'chart30Data',
            'topProducts',
            'recentOrders'
        ));
    }
}