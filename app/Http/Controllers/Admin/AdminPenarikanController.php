<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon; // Tambahan untuk memformat tanggal

class AdminPenarikanController extends Controller
{
    public function index()
    {
        // 1. Menggunakan DB Join sesuai logika aman Anda
        $listPenarikan = DB::table('withdrawals')
            ->join('users', 'withdrawals.vendor_id', '=', 'users.id')
            ->select('withdrawals.*', 'users.name as user_name', 'users.vendor_name as toko_name')
            ->orderBy('withdrawals.created_at', 'desc')
            ->get();

        // 2. Trik konversi: Format tanggal & bungkus nama vendor agar View tidak error (crash)
        foreach ($listPenarikan as $item) {
            $item->created_at = Carbon::parse($item->created_at);
            
            // Membuat objek relasi palsu agar View tetap bisa membaca $penarikan->vendor->name
            $item->vendor = (object) [
                'name' => $item->user_name,
                'vendor_name' => $item->toko_name
            ];
        }

        // 3. Kirim dengan nama variabel yang pas dengan View
        return view('admin.penarikan.index', compact('listPenarikan'));
    }

    public function approve($id)
    {
        $penarikan = DB::table('withdrawals')->where('id', $id)->first();

        if (!$penarikan || $penarikan->status != 'pending') {
            return back()->with('error', 'Data penarikan tidak valid atau sudah diproses.');
        }

        DB::beginTransaction();
        try {
            // 1. Ubah status penarikan
            DB::table('withdrawals')->where('id', $id)->update([
                'status' => 'disetujui',
                'updated_at' => now()
            ]);

            // 2. Kurangi saldo ditahan (uangnya sudah sukses ditransfer Admin)
            $saldo = DB::table('saldos')->where('vendor_id', $penarikan->vendor_id)->first();
            if ($saldo) {
                DB::table('saldos')->where('vendor_id', $penarikan->vendor_id)->update([
                    'saldo_ditahan' => $saldo->saldo_ditahan - $penarikan->nominal,
                    'updated_at' => now()
                ]);
            }

            DB::commit();
            return back()->with('success', 'Penarikan dana berhasil disetujui!');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Terjadi kesalahan sistem: ' . $e->getMessage());
        }
    }

    public function reject($id)
    {
        $penarikan = DB::table('withdrawals')->where('id', $id)->first();

        if (!$penarikan || $penarikan->status != 'pending') {
            return back()->with('error', 'Data penarikan tidak valid atau sudah diproses.');
        }

        DB::beginTransaction();
        try {
            // 1. Ubah status penarikan
            DB::table('withdrawals')->where('id', $id)->update([
                'status' => 'ditolak',
                'updated_at' => now()
            ]);

            // 2. Kembalikan uang ke saldo aktif Vendor!
            $saldo = DB::table('saldos')->where('vendor_id', $penarikan->vendor_id)->first();
            if ($saldo) {
                DB::table('saldos')->where('vendor_id', $penarikan->vendor_id)->update([
                    'saldo_aktif' => $saldo->saldo_aktif + $penarikan->nominal,
                    'saldo_ditahan' => $saldo->saldo_ditahan - $penarikan->nominal,
                    'updated_at' => now()
                ]);
            }

            DB::commit();
            return back()->with('success', 'Penarikan dana ditolak, saldo telah dikembalikan ke dompet Vendor.');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Terjadi kesalahan sistem: ' . $e->getMessage());
        }
    }
}