<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminLaporanController extends Controller
{
    /**
     * Tampilan Dashboard Laporan Keuangan & Transaksi Admin
     */
    public function index(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $statusFilter = $request->input('status');

        $query = DB::table('orders')
            ->leftJoin('users as customer', 'orders.user_id', '=', 'customer.id')
            ->leftJoin('users as vendor', 'orders.vendor_id', '=', 'vendor.id')
            ->select(
                'orders.*',
                'customer.name as customer_account_name',
                'customer.email as customer_email',
                'vendor.vendor_name',
                'vendor.name as vendor_owner'
            )
            ->orderBy('orders.created_at', 'desc');

        if ($startDate) {
            $query->whereDate('orders.created_at', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('orders.created_at', '<=', $endDate);
        }
        if ($statusFilter && $statusFilter !== 'all') {
            $query->where('orders.status', $statusFilter);
        }

        $allOrders = $query->get();

        // Hitung statistik
        $totalPendapatanKotor = 0;
        $totalFeeRentify = 0;
        $totalOngkir = 0;
        $totalDiskonVoucher = 0;
        $transaksiSelesaiCount = 0;
        $transaksiBatalCount = 0;

        foreach ($allOrders as $t) {
            $st = strtolower($t->status ?? '');
            $ong = (float) ($t->shipping_fee ?? 0);
            $tot = (float) ($t->total_price ?? 0);
            $pot = (float) ($t->potongan_voucher ?? 0);

            if (in_array($st, ['dibatalkan', 'batal', 'cancelled', 'ditolak'])) {
                $transaksiBatalCount++;
            } else {
                if ($st === 'selesai') {
                    $transaksiSelesaiCount++;
                }
                $totalPendapatanKotor += $tot;
                $totalOngkir += $ong;
                $totalDiskonVoucher += $pot;

                $sewaBersih = $tot - $ong + $pot;
                $sewaAsli = $sewaBersih / 1.05;
                $feeT = ($t->rentify_fee && $t->rentify_fee > 0) ? $t->rentify_fee : ($sewaBersih - $sewaAsli);
                $totalFeeRentify += $feeT;
            }
        }

        $stats = [
            'total_transaksi' => $allOrders->count(),
            'transaksi_selesai' => $transaksiSelesaiCount,
            'transaksi_batal' => $transaksiBatalCount,
            'total_pendapatan_kotor' => $totalPendapatanKotor,
            'total_fee_rentify' => $totalFeeRentify,
            'total_ongkir' => $totalOngkir,
            'total_diskon' => $totalDiskonVoucher
        ];

        return view('admin.laporan.index', compact('allOrders', 'stats', 'startDate', 'endDate', 'statusFilter'));
    }

    /**
     * Filter & Redirect
     */
    public function generate(Request $request)
    {
        return redirect()->route('admin.laporan', $request->all());
    }

    /**
     * Ekspor Laporan ke Format Excel (.xls)
     */
    public function excel(Request $request, $id = 'all')
    {
        $query = DB::table('orders')
            ->leftJoin('users as customer', 'orders.user_id', '=', 'customer.id')
            ->leftJoin('users as vendor', 'orders.vendor_id', '=', 'vendor.id')
            ->select(
                'orders.*',
                'customer.name as customer_account_name',
                'customer.email as customer_email',
                'vendor.vendor_name'
            )
            ->orderBy('orders.created_at', 'desc');

        if ($id && $id !== 'all') {
            $query->where('orders.id', $id);
        }

        $orders = $query->get();

        $html = '<table border="1" cellpadding="5" style="border-collapse: collapse; font-family: sans-serif;">';
        $html .= '<tr style="background-color: #0369a1; color: #ffffff; font-weight: bold;">
                    <th>Invoice</th>
                    <th>Tanggal Transaksi</th>
                    <th>Nama Customer</th>
                    <th>WhatsApp Customer</th>
                    <th>Toko Vendor</th>
                    <th>Metode Pembayaran</th>
                    <th>Total Tagihan (Rp)</th>
                    <th>Ongkir (Rp)</th>
                    <th>Diskon Voucher (Rp)</th>
                    <th>Komisi Rentify 5% (Rp)</th>
                    <th>Status Pesanan</th>
                  </tr>';

        foreach ($orders as $t) {
            $st = strtolower($t->status ?? '');
            $ong = (float) ($t->shipping_fee ?? 0);
            $tot = (float) ($t->total_price ?? 0);
            $pot = (float) ($t->potongan_voucher ?? 0);

            if (in_array($st, ['dibatalkan', 'batal', 'cancelled', 'ditolak'])) {
                $feeT = 0;
            } else {
                $sewaBersih = $tot - $ong + $pot;
                $sewaAsli = $sewaBersih / 1.05;
                $feeT = ($t->rentify_fee && $t->rentify_fee > 0) ? $t->rentify_fee : ($sewaBersih - $sewaAsli);
            }

            $html .= '<tr>';
            $html .= '<td>#INV-' . $t->id . '</td>';
            $html .= '<td>' . ($t->created_at ? Carbon::parse($t->created_at)->format('d/m/Y H:i') : '-') . '</td>';
            $html .= '<td>' . ($t->customer_name ?: $t->customer_account_name) . '</td>';
            $html .= '<td>' . ($t->customer_whatsapp ?: '-') . '</td>';
            $html .= '<td>' . ($t->vendor_name ?: 'Rentify Official') . '</td>';
            $html .= '<td>' . strtoupper($t->payment_method ?: 'ONLINE') . '</td>';
            $html .= '<td>' . number_format($tot, 0, ',', '.') . '</td>';
            $html .= '<td>' . number_format($ong, 0, ',', '.') . '</td>';
            $html .= '<td>' . number_format($pot, 0, ',', '.') . '</td>';
            $html .= '<td>' . number_format($feeT, 0, ',', '.') . '</td>';
            $html .= '<td>' . strtoupper($t->status ?: 'PENDING') . '</td>';
            $html .= '</tr>';
        }

        $html .= '</table>';

        $fileName = 'Laporan_Transaksi_Rentify_' . date('Y-m-d_His') . '.xls';

        return response($html)
            ->header('Content-Type', 'application/vnd.ms-excel')
            ->header('Content-Disposition', 'attachment; filename="' . $fileName . '"');
    }

    /**
     * Tampilan Cetak / PDF Invoice & Rincian Transaksi
     */
    public function pdf($id)
    {
        $order = DB::table('orders')
            ->leftJoin('users as customer', 'orders.user_id', '=', 'customer.id')
            ->leftJoin('users as vendor', 'orders.vendor_id', '=', 'vendor.id')
            ->where('orders.id', $id)
            ->select(
                'orders.*',
                'customer.name as customer_account_name',
                'customer.email as customer_email',
                'vendor.vendor_name',
                'vendor.name as vendor_owner'
            )
            ->first();

        if (!$order) {
            return back()->with('error', 'Pesanan tidak ditemukan.');
        }

        $items = DB::table('order_items')
            ->leftJoin('barang', 'order_items.product_id', '=', 'barang.id')
            ->where('order_items.order_id', $id)
            ->select('order_items.*', 'barang.nama as barang_nama')
            ->get();

        return view('admin.laporan.pdf', compact('order', 'items'));
    }
}
