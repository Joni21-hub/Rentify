<?php
namespace App\Services;

use App\Models\{Penyewaan, Pengembalian};
use Illuminate\Support\Carbon;

class DendaService
{
    /**
     * Calculate fines for a return.
     * Formula: denda = hari_terlambat * denda_per_hari per item
     */
    public function hitung(Penyewaan $penyewaan, Carbon $tanggalKembali): array
    {
        $hariTerlambat  = 0;
        $dendaTerlambat = 0.00;

        if ($tanggalKembali->isAfter($penyewaan->tanggal_selesai)) {
            $hariTerlambat = $penyewaan->tanggal_selesai
                ->diffInDays($tanggalKembali);

            $dendaTerlambat = $penyewaan->details->sum(
                fn($d) => $d->barang->denda_per_hari * $hariTerlambat * $d->jumlah
            );
        }

        $depositTersedia = $penyewaan->total_deposit;
        $sisaDeposit     = max(0, $depositTersedia - $dendaTerlambat);

        return [
            'hari_terlambat'     => $hariTerlambat,
            'denda_terlambat'    => round($dendaTerlambat, 2),
            'deposit_digunakan'  => min($depositTersedia, $dendaTerlambat),
            'sisa_deposit'       => round($sisaDeposit, 2),
        ];
    }

    /**
     * Process return: create Pengembalian record + update Penyewaan.
     */
    public function prosesKembali(
        Penyewaan $penyewaan,
        Carbon $tanggalKembali,
        int $cabangKembaliId,
        string $kondisi,
        float $dendaKerusakan = 0.00
    ): Pengembalian {
        $calc = $this->hitung($penyewaan, $tanggalKembali);

        $totalDenda = $calc['denda_terlambat'] + $dendaKerusakan;
        $depositDikembalikan = max(0, $penyewaan->total_deposit - $totalDenda);

        $pengembalian = Pengembalian::create([
            'penyewaan_id'       => $penyewaan->id,
            'cabang_kembali_id'  => $cabangKembaliId,
            'tanggal_kembali'    => $tanggalKembali->toDateString(),
            'hari_terlambat'     => $calc['hari_terlambat'],
            'denda_terlambat'    => $calc['denda_terlambat'],
            'denda_kerusakan'    => $dendaKerusakan,
            'kondisi_barang'     => $kondisi,
            'deposit_dikembalikan'=> $depositDikembalikan,
            'status'             => 'menunggu',
        ]);

        $penyewaan->update([
            'total_denda'            => $totalDenda,
            'tanggal_kembali_aktual' => $tanggalKembali->toDateString(),
            'status'                 => 'dikembalikan',
        ]);

        // Restore stock
        foreach ($penyewaan->details as $detail) {
            $detail->barang->stokCabang()
                ->where('cabang_id', $cabangKembaliId)
                ->increment('stok_tersedia', $detail->jumlah);
        }

        return $pengembalian;
    }
}