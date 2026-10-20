<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Penyewaan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PesananController extends Controller
{
    public function index(Request $request)
    {
        $vendorId = Auth::id();

        $baseQuery = Penyewaan::with(['customer', 'details.barang'])
            ->where(function ($q) use ($vendorId) {
                $q->where('vendor_id', $vendorId)
                  ->orWhereHas('details.barang', function ($query) use ($vendorId) {
                      $query->where('vendor_id', $vendorId);
                  });
            });

        // Hitung total untuk setiap tab status
        $countSemua = (clone $baseQuery)->count();
        $countMenunggu = (clone $baseQuery)->whereIn('status', ['Menunggu Konfirmasi', 'pending'])->count();
        $countDisewa = (clone $baseQuery)->whereIn('status', ['Sedang Disewa', 'berjalan', 'Disetujui'])->count();
        $countJatuhTempo = (clone $baseQuery)->whereIn('status', ['Sedang Disewa', 'berjalan', 'Disetujui'])
            ->where(function($q) {
                $q->whereDate('tanggal_selesai', '<=', now()->toDateString())
                  ->orWhereDate('end_rent', '<=', now()->toDateString());
            })->count();
        $countSelesai = (clone $baseQuery)->where('status', 'Selesai')->count();
        $countBatal = (clone $baseQuery)->whereIn('status', ['Dibatalkan', 'ditolak'])->count();

        // Filter berdasarkan tab yang dipilih
        $statusFilter = $request->query('status', 'semua');
        $query = clone $baseQuery;

        if ($statusFilter === 'menunggu') {
            $query->whereIn('status', ['Menunggu Konfirmasi', 'pending']);
        } elseif ($statusFilter === 'disewa') {
            $query->whereIn('status', ['Sedang Disewa', 'berjalan', 'Disetujui']);
        } elseif ($statusFilter === 'jatuh_tempo') {
            $query->whereIn('status', ['Sedang Disewa', 'berjalan', 'Disetujui'])
                  ->where(function($q) {
                      $q->whereDate('tanggal_selesai', '<=', now()->toDateString())
                        ->orWhereDate('end_rent', '<=', now()->toDateString());
                  });
        } elseif ($statusFilter === 'selesai') {
            $query->where('status', 'Selesai');
        } elseif ($statusFilter === 'dibatalkan') {
            $query->whereIn('status', ['Dibatalkan', 'ditolak']);
        }

        $pesananMasuk = $query->orderBy('created_at', 'desc')->get();

        return view('vendor.pesanan.index', [
            'pesananMasuk'    => $pesananMasuk,
            'orders'          => $pesananMasuk,
            'statusFilter'    => $statusFilter,
            'countSemua'      => $countSemua,
            'countMenunggu'   => $countMenunggu,
            'countDisewa'     => $countDisewa,
            'countJatuhTempo' => $countJatuhTempo,
            'countSelesai'    => $countSelesai,
            'countBatal'      => $countBatal,
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

        if ($details->isEmpty() && $pesanan->vendor_id != $vendorId) {
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

        $vendorId = Auth::id();
        $pesanan = Penyewaan::with(['details.barang'])->findOrFail($id);

        // Otorisasi: Pastikan pesanan adalah milik vendor yang login
        $isOwner = ($pesanan->vendor_id == $vendorId) || $pesanan->details->contains(function ($detail) use ($vendorId) {
            return $detail->barang && $detail->barang->vendor_id == $vendorId;
        });

        if (!$isOwner) {
            abort(403, 'Anda tidak memiliki hak akses untuk mengubah status pesanan ini.');
        }

        if ($request->status === 'Selesai' && $pesanan->status !== 'Selesai') {
            DB::beginTransaction();
            try {
                $totalNilai = (float) ($pesanan->total_biaya ?: $pesanan->total_price);
                $hargaAsli = round($totalNilai / 1.05);
                $feePlatform = $totalNilai - $hargaAsli;

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

                $saldoAktifBaru = (float) $saldoVendor->saldo_aktif;

                // Jika COD: Vendor pegang tunai 100%, fee sistem 5% dipotong dari saldo vendor
                // Jika Online/Midtrans: Uang sewa bersih (setelah fee 5%) dimasukkan ke saldo vendor
                if (strtoupper($pesanan->payment_method ?? '') === 'COD') {
                    $saldoAktifBaru -= $feePlatform;
                } else {
                    $saldoAktifBaru += $hargaAsli;
                }

                DB::table('saldos')
                    ->where('vendor_id', $vendorId)
                    ->update(['saldo_aktif' => $saldoAktifBaru, 'updated_at' => now()]);

                $pesanan->status = 'Selesai';
                $pesanan->save();

                DB::commit();
                return back()->with('success', 'Pesanan Selesai! Saldo toko Anda otomatis diperbarui.');
            } catch (\Exception $e) {
                DB::rollback();
                return back()->with('error', 'Gagal memproses saldo: ' . $e->getMessage());
            }
        }

        if ($request->status === 'Dibatalkan' && $pesanan->status !== 'Dibatalkan') {
            // Pulihkan kuota voucher jika transaksi dibatalkan
            if ($pesanan->voucher_id) {
                \App\Models\Voucher::where('id', $pesanan->voucher_id)
                    ->where('kuota_terpakai', '>', 0)
                    ->decrement('kuota_terpakai');
            }
        }

        $pesanan->status = $request->status;
        $pesanan->save();

        return back()->with('success', 'Status pesanan berhasil diperbarui!');
    }
}