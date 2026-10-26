<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Services\TransactionCalculationService;

class AuditRentifyTransactionsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rentify:audit-transactions {--fix : Perbaiki data order dan sinkronisasi saldo vendor otomatis}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Audit seluruh transaksi di Rentify (Customer -> Vendor -> Admin Fee), cek kesesuaian perhitungan dan sinkronisasi saldo';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $shouldFix = (bool) $this->option('fix');

        $this->info("================================================================================");
        $this->info("         AUDIT INTEGRITAS KEUANGAN & TRANSAKSI PLATFORM RENTIFY                 ");
        $this->info("================================================================================");
        $this->comment("Mode: " . ($shouldFix ? "PERBAIKAN AKTIF (--fix)" : "INSPEKSI SAJA (Dry-Run)"));
        $this->newLine();

        $orders = DB::table('orders')->orderBy('id', 'asc')->get();
        $totalOrders = $orders->count();

        $this->info("Ditemukan {$totalOrders} transaksi di tabel orders.");

        $totalCustomerPaid = 0.0;
        $totalPlatformFee  = 0.0;
        $totalVendorEarn   = 0.0;
        $totalOngkir       = 0.0;
        $totalVoucher      = 0.0;
        $totalKodeUnik     = 0;

        $inconsistenciesFound = 0;
        $fixedOrdersCount     = 0;

        $details = [];

        foreach ($orders as $order) {
            $breakdown = TransactionCalculationService::getBreakdownForOrder($order);

            $storedFee     = (float) ($order->rentify_fee ?? 0);
            $storedEarning = (float) ($order->vendor_earning ?? 0);

            $calculatedFee     = $breakdown['rentify_fee'];
            $calculatedEarning = $breakdown['vendor_earning'];
            $totalTagihan      = $breakdown['total_biaya'];

            $isFeeMismatch     = (abs($storedFee - $calculatedFee) > 0.01);
            $isEarningMismatch = (abs($storedEarning - $calculatedEarning) > 0.01);

            $needsFix = ($isFeeMismatch || $isEarningMismatch);

            if ($needsFix) {
                $inconsistenciesFound++;

                if ($shouldFix) {
                    DB::table('orders')->where('id', $order->id)->update([
                        'rentify_fee'    => $calculatedFee,
                        'vendor_earning' => $calculatedEarning,
                        'updated_at'     => now(),
                    ]);
                    $fixedOrdersCount++;
                }
            }

            $st = strtolower($order->status ?? '');
            if (!in_array($st, ['dibatalkan', 'batal', 'cancelled', 'ditolak'])) {
                $totalCustomerPaid += $totalTagihan;
                $totalPlatformFee  += $calculatedFee;
                $totalVendorEarn   += $calculatedEarning;
                $totalOngkir       += $breakdown['shipping_fee'];
                $totalVoucher      += $breakdown['potongan_voucher'];
                $totalKodeUnik     += $breakdown['kode_unik'];
            }

            $details[] = [
                'ID'              => "#INV-{$order->id}",
                'Status'          => $order->status,
                'Metode'          => $order->payment_method ?? 'ONLINE',
                'Total Tagihan'   => 'Rp ' . number_format($totalTagihan, 0, ',', '.'),
                'Vendor Earning'  => 'Rp ' . number_format($calculatedEarning, 0, ',', '.'),
                'Rentify Fee(5%)' => 'Rp ' . number_format($calculatedFee, 0, ',', '.'),
                'Ongkir'          => 'Rp ' . number_format($breakdown['shipping_fee'], 0, ',', '.'),
                'Voucher'         => 'Rp ' . number_format($breakdown['potongan_voucher'], 0, ',', '.'),
                'Status Sinkron'  => $needsFix ? ($shouldFix ? 'DIPERBAIKI' : 'BELUM TERSIMPAN') : 'SESUAI',
            ];
        }

        if (count($details) <= 25) {
            $this->table(
                ['Invoice', 'Status', 'Metode', 'Total Tagihan', 'Hak Vendor', 'Hak Rentify', 'Ongkir', 'Voucher', 'Status Sinkron'],
                $details
            );
        } else {
            $this->info("Menampilkan 10 transaksi pertama dan 5 transaksi terakhir:");
            $sample = array_merge(array_slice($details, 0, 10), [
                ['ID' => '...', 'Status' => '...', 'Metode' => '...', 'Total Tagihan' => '...', 'Vendor Earning' => '...', 'Rentify Fee(5%)' => '...', 'Ongkir' => '...', 'Voucher' => '...', 'Status Sinkron' => '...'],
            ], array_slice($details, -5));
            $this->table(
                ['Invoice', 'Status', 'Metode', 'Total Tagihan', 'Hak Vendor', 'Hak Rentify', 'Ongkir', 'Voucher', 'Status Sinkron'],
                $sample
            );
        }

        $this->newLine();
        $this->info("=================== RINGKASAN AGREGASI KEUANGAN ===================");
        $this->line("1. Total Transaksi Sah (Tidak Batal) : Rp " . number_format($totalCustomerPaid, 0, ',', '.'));
        $this->line("2. Total Hak Pendapatan Vendor       : Rp " . number_format($totalVendorEarn, 0, ',', '.'));
        $this->line("3. Total Hak Komisi Rentify (5%)     : Rp " . number_format($totalPlatformFee, 0, ',', '.'));
        $this->line("4. Total Ongkos Kirim (Vendor)       : Rp " . number_format($totalOngkir, 0, ',', '.'));
        $this->line("5. Total Potongan Voucher Toko       : Rp " . number_format($totalVoucher, 0, ',', '.'));
        $this->line("6. Total Kode Unik                   : Rp " . number_format($totalKodeUnik, 0, ',', '.'));
        $this->line("7. Transaksi Perlu Sinkronisasi      : {$inconsistenciesFound} order");
        if ($shouldFix) {
            $this->info("   -> Berhasil menyinkronkan {$fixedOrdersCount} order ke database!");
        }

        // Audit & Sinkronisasi Saldo Vendor
        $this->newLine();
        $this->info("=================== AUDIT KONSISTENSI SALDO VENDOR ================");

        $vendors = DB::table('users')->where('role', 'vendor')->get();
        $saldoRows = [];

        foreach ($vendors as $v) {
            $vendorId = $v->id;

            // Saldo saat ini di tabel saldos
            $saldoRecord = DB::table('saldos')->where('vendor_id', $vendorId)->first();
            $saldoAktifSaatIni = $saldoRecord ? (float) $saldoRecord->saldo_aktif : 0.0;
            $saldoDitahanSaatIni = $saldoRecord ? (float) $saldoRecord->saldo_ditahan : 0.0;

            // Ambil semua order selesai vendor
            $completedOrders = DB::table('orders')
                ->where('vendor_id', $vendorId)
                ->where('status', 'Selesai')
                ->get();

            $expectedEarningsOnline = 0.0;
            $expectedCodFees        = 0.0;

            foreach ($completedOrders as $ord) {
                $bd = TransactionCalculationService::getBreakdownForOrder($ord);
                if (strtoupper($ord->payment_method ?? '') === 'COD') {
                    $expectedCodFees += $bd['rentify_fee'];
                } else {
                    $expectedEarningsOnline += $bd['vendor_earning'];
                }
            }

            // Total penarikan yang disetujui (uang keluar)
            $approvedWithdrawals = (float) DB::table('withdrawals')
                ->where('vendor_id', $vendorId)
                ->where('status', 'disetujui')
                ->sum('nominal');

            // Total penarikan pending (sedang ditahan)
            $pendingWithdrawals = (float) DB::table('withdrawals')
                ->where('vendor_id', $vendorId)
                ->where('status', 'pending')
                ->sum('nominal');

            // Saldo aktif teoritis = (Pendapatan Online Selesai - Fee COD Selesai) - Penarikan Disetujui - Penarikan Pending
            // Sesuai aturan pemilik: Tetap izinkan saldo minus (berutang fee COD), akan terpotong otomatis saat ada sewa online berikutnya
            $saldoAktifSeharusnya = round(($expectedEarningsOnline - $expectedCodFees) - $approvedWithdrawals - $pendingWithdrawals, 2);
            $saldoDitahanSeharusnya = round($pendingWithdrawals, 2);

            $isSaldoMismatch = (abs($saldoAktifSaatIni - $saldoAktifSeharusnya) > 0.01) || (abs($saldoDitahanSaatIni - $saldoDitahanSeharusnya) > 0.01);

            if ($isSaldoMismatch && $shouldFix) {
                if (!$saldoRecord) {
                    DB::table('saldos')->insert([
                        'vendor_id'     => $vendorId,
                        'saldo_aktif'   => $saldoAktifSeharusnya,
                        'saldo_ditahan' => $saldoDitahanSeharusnya,
                        'created_at'    => now(),
                        'updated_at'    => now(),
                    ]);
                } else {
                    DB::table('saldos')->where('vendor_id', $vendorId)->update([
                        'saldo_aktif'   => $saldoAktifSeharusnya,
                        'saldo_ditahan' => $saldoDitahanSeharusnya,
                        'updated_at'    => now(),
                    ]);
                }
            }

            $saldoRows[] = [
                'Vendor ID'       => $vendorId,
                'Nama Toko'       => $v->vendor_name ?: $v->name,
                'Order Selesai'   => $completedOrders->count(),
                'Saldo Aktif DB'  => 'Rp ' . number_format($saldoAktifSaatIni, 0, ',', '.'),
                'Saldo Seharusnya'=> 'Rp ' . number_format($saldoAktifSeharusnya, 0, ',', '.'),
                'Saldo Ditahan'   => 'Rp ' . number_format($saldoDitahanSaatIni, 0, ',', '.'),
                'Status'          => $isSaldoMismatch ? ($shouldFix ? 'DISINKRONKAN' : 'SELISIH') : 'KONSISTEN',
            ];
        }

        $this->table(
            ['ID', 'Nama Toko', 'Order Selesai', 'Saldo Aktif DB', 'Saldo Seharusnya', 'Saldo Ditahan', 'Status'],
            $saldoRows
        );

        $this->newLine();
        $this->info("Audit transaksi & saldo platform Rentify selesai!");
        return Command::SUCCESS;
    }
}
