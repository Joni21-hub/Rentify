<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Penyewaan; // Tambahkan model Penyewaan

class VendorSaldoController extends Controller
{
    public function index()
    {
        $vendorId = Auth::id();

        // 1. Cek & Ambil Saldo
        $saldo = DB::table('saldos')->where('vendor_id', $vendorId)->first();
        if (!$saldo) {
            DB::table('saldos')->insert([
                'vendor_id' => $vendorId,
                'saldo_aktif' => 0,
                'saldo_ditahan' => 0,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            $saldo = DB::table('saldos')->where('vendor_id', $vendorId)->first();
        }

        // 2. KUMPULKAN RIWAYAT MUTASI (Trik Menggabungkan 2 Tabel Tanpa Migration Baru)
        $mutasi = collect();

        // A. Ambil Data Penarikan Dana
        $withdrawals = DB::table('withdrawals')->where('vendor_id', $vendorId)->get();
        foreach ($withdrawals as $w) {
            $mutasi->push((object)[
                'tanggal' => $w->created_at,
                'jenis' => 'Penarikan Saldo',
                'nominal' => $w->nominal,
                'keterangan' => 'Tarik dana ke ' . $w->nama_bank_ewallet . ' (' . $w->nomor_rekening . ')',
                'status' => strtolower($w->status),
                'icon' => 'fa-money-bill-transfer',
                'color' => 'text-amber-500',
                'bg' => 'bg-amber-50',
                'operator' => '-'
            ]);
        }

        // B. Ambil Data Pendapatan dari Pesanan Selesai
        $pesananSelesai = Penyewaan::whereHas('details.barang', function($query) use ($vendorId) {
            $query->where('vendor_id', $vendorId);
        })->where('status', 'Selesai')->get();

        foreach ($pesananSelesai as $p) {
            $hargaAsli = $p->total_price / 1.05;
            $feePlatform = $p->total_price - $hargaAsli;

            if (strtoupper($p->payment_method) === 'QRIS') {
                $mutasi->push((object)[
                    'tanggal' => $p->updated_at,
                    'jenis' => 'Pendapatan QRIS',
                    'nominal' => $hargaAsli,
                    'keterangan' => 'Pemasukan uang sewa - Order #' . $p->id,
                    'status' => 'berhasil',
                    'icon' => 'fa-arrow-down-long',
                    'color' => 'text-emerald-500',
                    'bg' => 'bg-emerald-50',
                    'operator' => '+'
                ]);
            } elseif (strtoupper($p->payment_method) === 'COD') {
                $mutasi->push((object)[
                    'tanggal' => $p->updated_at,
                    'jenis' => 'Potongan Fee',
                    'nominal' => $feePlatform,
                    'keterangan' => 'Potongan fee 5% platform (Transaksi COD) - Order #' . $p->id,
                    'status' => 'berhasil',
                    'icon' => 'fa-arrow-up-right-dots',
                    'color' => 'text-rose-500',
                    'bg' => 'bg-rose-50',
                    'operator' => '-'
                ]);
            }
        }

        // Urutkan semua data dari yang paling baru (descending)
        $riwayatMutasi = $mutasi->sortByDesc('tanggal')->values();

        return view('vendor.saldo.index', compact('saldo', 'riwayatMutasi'));
    }

    // Fungsi storePenarikan tidak saya ubah logika aslinya, 100% aman
    public function storePenarikan(Request $request)
    {
        $request->validate([
            'nominal' => 'required|numeric|min:10000',
            'metode' => 'required|in:Bank,E-Wallet',
            'nama_bank_ewallet' => 'required|string|max:255',
            'nomor_rekening' => 'required|string|max:255',
            'nama_pemilik' => 'required|string|max:255',
        ], [
            'nominal.min' => 'Minimal penarikan adalah Rp 10.000'
        ]);

        $vendorId = Auth::id();
        $nominalTarik = $request->nominal;

        $saldo = DB::table('saldos')->where('vendor_id', $vendorId)->first();

        if (!$saldo || $saldo->saldo_aktif < $nominalTarik) {
            return back()->with('error', 'Saldo aktif Anda tidak mencukupi untuk penarikan ini.');
        }

        DB::beginTransaction();
        try {
            DB::table('saldos')->where('vendor_id', $vendorId)->update([
                'saldo_aktif' => $saldo->saldo_aktif - $nominalTarik,
                'saldo_ditahan' => $saldo->saldo_ditahan + $nominalTarik,
                'updated_at' => now()
            ]);

            DB::table('withdrawals')->insert([
                'vendor_id' => $vendorId,
                'nominal' => $nominalTarik,
                'metode' => $request->metode,
                'nama_bank_ewallet' => $request->nama_bank_ewallet,
                'nomor_rekening' => $request->nomor_rekening,
                'nama_pemilik' => $request->nama_pemilik,
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now()
            ]);

            DB::commit();
            return back()->with('success', 'Pengajuan penarikan dana berhasil dikirim. Menunggu persetujuan Admin.');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Terjadi kesalahan sistem: ' . $e->getMessage());
        }
    }

    // FITUR UNDUH LAPORAN EXCEL (CSV)
    // FITUR UNDUH LAPORAN EXCEL (BENTUK TABEL RAPI)
    public function export()
    {
        $vendorId = Auth::id();
        $mutasi = collect();

        // Ambil Data Penarikan
        $withdrawals = DB::table('withdrawals')->where('vendor_id', $vendorId)->get();
        foreach ($withdrawals as $w) {
            $mutasi->push((object)[
                'tanggal' => $w->created_at, 'jenis' => 'Penarikan Saldo', 
                'nominal' => $w->nominal, 'keterangan' => 'Tarik dana ke ' . $w->nama_bank_ewallet,
                'status' => strtolower($w->status), 'operator' => '-'
            ]);
        }

        // Ambil Data Pendapatan
        $pesananSelesai = \App\Models\Penyewaan::whereHas('details.barang', function($query) use ($vendorId) {
            $query->where('vendor_id', $vendorId);
        })->where('status', 'Selesai')->get();

        foreach ($pesananSelesai as $p) {
            $hargaAsli = $p->total_price / 1.05;
            $feePlatform = $p->total_price - $hargaAsli;

            if (strtoupper($p->payment_method) === 'QRIS') {
                $mutasi->push((object)[
                    'tanggal' => $p->updated_at, 'jenis' => 'Pendapatan QRIS',
                    'nominal' => $hargaAsli, 'keterangan' => 'Order #' . $p->id,
                    'status' => 'berhasil', 'operator' => '+'
                ]);
            } elseif (strtoupper($p->payment_method) === 'COD') {
                $mutasi->push((object)[
                    'tanggal' => $p->updated_at, 'jenis' => 'Potongan Fee Platform',
                    'nominal' => $feePlatform, 'keterangan' => 'Order #' . $p->id,
                    'status' => 'berhasil', 'operator' => '-'
                ]);
            }
        }

        $riwayatMutasi = $mutasi->sortByDesc('tanggal')->values();

        // BUAT TAMPILAN TABEL HTML (EXCEL BISA MEMBACA INI DENGAN SEMPURNA)
        $html = '<table border="1" cellpadding="5" style="border-collapse: collapse;">';
        $html .= '<tr style="background-color: #1E3A8A; color: white;">
                    <th>Tanggal Transaksi</th>
                    <th>Jenis Transaksi</th>
                    <th>Keterangan</th>
                    <th>Nominal (Rp)</th>
                    <th>Status</th>
                  </tr>';
        
        foreach ($riwayatMutasi as $m) {
            $html .= '<tr>';
            $html .= '<td>' . \Carbon\Carbon::parse($m->tanggal)->format('Y-m-d H:i') . '</td>';
            $html .= '<td>' . $m->jenis . '</td>';
            $html .= '<td>' . $m->keterangan . '</td>';
            $html .= '<td>' . $m->operator . $m->nominal . '</td>';
            $html .= '<td>' . strtoupper($m->status) . '</td>';
            $html .= '</tr>';
        }
        $html .= '</table>';

        // Paksa browser mendownload sebagai file .xls
        $fileName = 'Laporan_Keuangan_Rentify_' . date('Y-m-d') . '.xls';
        return response($html)
            ->header('Content-Type', 'application/vnd.ms-excel')
            ->header('Content-Disposition', 'attachment; filename="' . $fileName . '"');
    }
}