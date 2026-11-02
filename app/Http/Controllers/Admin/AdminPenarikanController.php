<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AuditLogService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
        DB::beginTransaction();
        try {
            $penarikan = DB::table('withdrawals')->where('id', $id)->lockForUpdate()->first();

            if (!$penarikan || $penarikan->status !== 'pending') {
                DB::rollback();
                return back()->with('error', 'Data penarikan tidak valid atau sudah diproses.');
            }

            // 1. Ubah status penarikan
            DB::table('withdrawals')->where('id', $id)->update([
                'status' => 'disetujui',
                'updated_at' => now()
            ]);

            // 2. Kurangi saldo ditahan (uangnya sudah sukses ditransfer Admin)
            $saldo = DB::table('saldos')->where('vendor_id', $penarikan->vendor_id)->lockForUpdate()->first();
            $saldoDitahanSebelum = $saldo ? (float) $saldo->saldo_ditahan : 0.0;
            $saldoAktifSebelum = $saldo ? (float) $saldo->saldo_aktif : 0.0;
            $saldoDitahanBaru = max(0, $saldoDitahanSebelum - (float) $penarikan->nominal);

            if ($saldo) {
                DB::table('saldos')->where('vendor_id', $penarikan->vendor_id)->update([
                    'saldo_ditahan' => $saldoDitahanBaru,
                    'updated_at' => now()
                ]);
            }

            // 3. Catat Trail Audit Admin
            AuditLogService::log(
                action: 'pencairan_penarikan_vendor',
                auditableType: 'App\Models\Withdrawal',
                auditableId: $id,
                before: [
                    'withdrawal_id'         => (int) $id,
                    'vendor_id'             => (int) $penarikan->vendor_id,
                    'nominal_penarikan'     => (float) $penarikan->nominal,
                    'status_penarikan'      => 'pending',
                    'bank'                  => $penarikan->bank ?? null,
                    'nomor_rekening'        => $penarikan->nomor_rekening ?? null,
                    'nama_rekening'         => $penarikan->nama_rekening ?? null,
                    'saldo_aktif_sebelum'   => $saldoAktifSebelum,
                    'saldo_ditahan_sebelum' => $saldoDitahanSebelum,
                ],
                after: [
                    'withdrawal_id'         => (int) $id,
                    'vendor_id'             => (int) $penarikan->vendor_id,
                    'nominal_penarikan'     => (float) $penarikan->nominal,
                    'status_penarikan'      => 'disetujui',
                    'saldo_aktif_sesudah'   => $saldoAktifSebelum,
                    'saldo_ditahan_sesudah' => $saldoDitahanBaru,
                    'keterangan'            => 'Pencairan dana disetujui, dana sukses ditransfer ke rekening vendor',
                ],
                adminId: auth()->id(),
                request: request()
            );

            DB::commit();
            return back()->with('success', 'Penarikan dana berhasil disetujui dan tercatat dalam trail audit.');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Terjadi kesalahan sistem: ' . $e->getMessage());
        }
    }

    public function reject($id)
    {
        DB::beginTransaction();
        try {
            $penarikan = DB::table('withdrawals')->where('id', $id)->lockForUpdate()->first();

            if (!$penarikan || $penarikan->status !== 'pending') {
                DB::rollback();
                return back()->with('error', 'Data penarikan tidak valid atau sudah diproses.');
            }

            // 1. Ubah status penarikan
            DB::table('withdrawals')->where('id', $id)->update([
                'status' => 'ditolak',
                'updated_at' => now()
            ]);

            // 2. Kembalikan uang ke saldo aktif Vendor secara aman!
            $saldo = DB::table('saldos')->where('vendor_id', $penarikan->vendor_id)->lockForUpdate()->first();
            $saldoDitahanSebelum = $saldo ? (float) $saldo->saldo_ditahan : 0.0;
            $saldoAktifSebelum = $saldo ? (float) $saldo->saldo_aktif : 0.0;

            $saldoDitahanBaru = max(0, $saldoDitahanSebelum - (float) $penarikan->nominal);
            $saldoAktifBaru = $saldoAktifSebelum + (float) $penarikan->nominal;

            if ($saldo) {
                DB::table('saldos')->where('vendor_id', $penarikan->vendor_id)->update([
                    'saldo_aktif'   => $saldoAktifBaru,
                    'saldo_ditahan' => $saldoDitahanBaru,
                    'updated_at'    => now()
                ]);
            }

            // 3. Catat Trail Audit Admin
            AuditLogService::log(
                action: 'penolakan_penarikan_vendor',
                auditableType: 'App\Models\Withdrawal',
                auditableId: $id,
                before: [
                    'withdrawal_id'         => (int) $id,
                    'vendor_id'             => (int) $penarikan->vendor_id,
                    'nominal_penarikan'     => (float) $penarikan->nominal,
                    'status_penarikan'      => 'pending',
                    'saldo_aktif_sebelum'   => $saldoAktifSebelum,
                    'saldo_ditahan_sebelum' => $saldoDitahanSebelum,
                ],
                after: [
                    'withdrawal_id'         => (int) $id,
                    'vendor_id'             => (int) $penarikan->vendor_id,
                    'nominal_penarikan'     => (float) $penarikan->nominal,
                    'status_penarikan'      => 'ditolak',
                    'saldo_aktif_sesudah'   => $saldoAktifBaru,
                    'saldo_ditahan_sesudah' => $saldoDitahanBaru,
                    'keterangan'            => 'Penarikan ditolak, dana dikembalikan ke saldo aktif vendor',
                ],
                adminId: auth()->id(),
                request: request()
            );

            DB::commit();
            return back()->with('success', 'Penarikan dana berhasil ditolak, saldo dikembalikan ke saldo aktif vendor dan tercatat dalam trail audit.');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Terjadi kesalahan sistem: ' . $e->getMessage());
        }
    }
}
