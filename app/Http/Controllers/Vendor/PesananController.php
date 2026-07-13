<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Penyewaan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PesananController extends Controller
{
    public function index()
    {
        $vendorId = Auth::id();

        $pesananMasuk = Penyewaan::whereHas('details.barang', function ($query) use ($vendorId) {
            $query->where('vendor_id', $vendorId);
        })
        ->orderBy('created_at', 'desc')
        ->get();

        // Dikirim dengan 2 nama sekaligus agar anti-error di halaman index
        return view('vendor.pesanan.index', [
            'pesananMasuk' => $pesananMasuk,
            'orders'       => $pesananMasuk
        ]);
    }

    // PERBAIKAN DI FUNGSI SHOW INI:
    public function show($id)
    {
        $vendorId = Auth::id();

        // Wajib menggunakan Model Eloquent (Penyewaan::with) agar relasi 'details' terbaca
        $pesanan = Penyewaan::with(['details.barang'])->findOrFail($id);

        // Kita saring khusus barang yang milik vendor yang sedang login
        $details = $pesanan->details->filter(function ($detail) use ($vendorId) {
            return $detail->barang && $detail->barang->vendor_id == $vendorId;
        });

        if ($details->isEmpty()) {
            abort(403, 'Anda tidak memiliki akses ke barang dalam pesanan ini.');
        }

        // Kita kirimkan variabel $pesanan DAN $details sekaligus agar halaman blade anti-error!
        return view('vendor.pesanan.detail', [
            'pesanan' => $pesanan,
            'details' => $details
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:Menunggu Konfirmasi,Disetujui,Sedang Disewa,Selesai,Dibatalkan'
        ]);

        $pesanan = Penyewaan::findOrFail($id);
        $vendorId = Auth::id();

        if ($request->status === 'Selesai' && $pesanan->status !== 'Selesai') {
            DB::beginTransaction();
            try {
                $hargaAsli = $pesanan->total_price / 1.05;
                $feePlatform = $pesanan->total_price - $hargaAsli;

                $saldoVendor = DB::table('saldos')->where('vendor_id', $vendorId)->first();
                if (!$saldoVendor) {
                    DB::table('saldos')->insert([
                        'vendor_id' => $vendorId,
                        'saldo_aktif' => 0,
                        'saldo_ditahan' => 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $saldoVendor = DB::table('saldos')->where('vendor_id', $vendorId)->first();
                }

                $saldoAktifBaru = $saldoVendor->saldo_aktif;

                if (strtoupper($pesanan->payment_method) === 'COD') {
                    $saldoAktifBaru -= $feePlatform;
                } elseif (strtoupper($pesanan->payment_method) === 'QRIS') {
                    $saldoAktifBaru += $hargaAsli;
                }

                DB::table('saldos')
                    ->where('vendor_id', $vendorId)
                    ->update(['saldo_aktif' => $saldoAktifBaru, 'updated_at' => now()]);

                $pesanan->status = 'Selesai';
                $pesanan->save();

                DB::commit();
                return back()->with('success', 'Pesanan Selesai! Saldo Anda otomatis disesuaikan.');
            } catch (\Exception $e) {
                DB::rollback();
                return back()->with('error', 'Gagal memproses uang: ' . $e->getMessage());
            }
        }

        $pesanan->status = $request->status;
        $pesanan->save();

        return back()->with('success', 'Status pesanan berhasil diperbarui!');
    }
}