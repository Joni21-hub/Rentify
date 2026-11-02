<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AuditLogService;
use App\Services\OrderNotificationService;
use App\Services\TransactionCalculationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminPembayaranController extends Controller
{
    public function index()
    {
        $pembayarans = DB::table('pembayarans')
            ->leftJoin('orders', 'pembayarans.order_id', '=', 'orders.id')
            ->leftJoin('users', 'orders.user_id', '=', 'users.id')
            ->select(
                'pembayarans.*',
                'orders.customer_name',
                'orders.customer_whatsapp',
                'orders.total_price',
                'users.email as customer_email'
            )
            ->orderBy('pembayarans.created_at', 'desc')
            ->get();

        foreach ($pembayarans as $p) {
            $p->created_at = $p->created_at ? Carbon::parse($p->created_at) : null;
            $p->paid_at = $p->paid_at ? Carbon::parse($p->paid_at) : null;
        }

        return view('admin.pembayaran.index', compact('pembayarans'));
    }

    public function verify($id)
    {
        $pembayaran = DB::table('pembayarans')->where('id', $id)->first();
        if (!$pembayaran) {
            return back()->with('error', 'Data pembayaran tidak ditemukan.');
        }

        DB::beginTransaction();
        try {
            $statusSebelum = $pembayaran->status;

            DB::table('pembayarans')->where('id', $id)->update([
                'status' => 'Lunas',
                'paid_at' => now(),
                'updated_at' => now()
            ]);

            $order = null;
            // Update status pesanan jika masih pending
            if ($pembayaran->order_id) {
                $orderBefore = DB::table('orders')->where('id', $pembayaran->order_id)->first();

                DB::table('orders')->where('id', $pembayaran->order_id)->update([
                    'status' => 'Menunggu Konfirmasi',
                    'paid_at' => now(),
                    'updated_at' => now()
                ]);

                $order = DB::table('orders')->where('id', $pembayaran->order_id)->first();
                if ($order) {
                    OrderNotificationService::notifyPaymentConfirmed(
                        $order,
                        'Transfer Bank (Verifikasi Manual Admin)',
                        (float) ($pembayaran->jumlah ?? $order->total_price ?? 0)
                    );
                }
            }

            // Catat Trail Audit Admin
            AuditLogService::log(
                action: 'verifikasi_manual_pembayaran',
                auditableType: 'App\Models\Pembayaran',
                auditableId: $id,
                before: [
                    'pembayaran_id'  => $id,
                    'status'         => $statusSebelum,
                    'jumlah'         => (float) $pembayaran->jumlah,
                    'order_id'       => $pembayaran->order_id,
                    'order_status'   => $orderBefore?->status ?? null,
                ],
                after: [
                    'pembayaran_id'  => $id,
                    'status'         => 'Lunas',
                    'jumlah'         => (float) $pembayaran->jumlah,
                    'order_id'       => $pembayaran->order_id,
                    'order_status'   => $order?->status ?? 'Menunggu Konfirmasi',
                    'keterangan'     => 'Diverifikasi lunas secara manual oleh Admin',
                ],
                adminId: auth()->id(),
                request: request()
            );

            DB::commit();
            return back()->with('success', 'Pembayaran berhasil diverifikasi secara manual dan tercatat dalam trail audit.');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Gagal memverifikasi pembayaran: ' . $e->getMessage());
        }
    }

    public function refund($id)
    {
        $pembayaran = DB::table('pembayarans')->where('id', $id)->first();
        if (!$pembayaran) {
            return back()->with('error', 'Data pembayaran tidak ditemukan.');
        }

        DB::beginTransaction();
        try {
            $statusSebelum = $pembayaran->status;
            $orderBefore = null;

            DB::table('pembayarans')->where('id', $id)->update([
                'status' => 'Refunded',
                'updated_at' => now()
            ]);

            if ($pembayaran->order_id) {
                $orderBefore = DB::table('orders')->where('id', $pembayaran->order_id)->first();
                if ($orderBefore && strtolower($orderBefore->status) === 'selesai') {
                    // Revert saldo vendor secara atomik jika pesanan sebelumnya sudah lunas/selesai
                    TransactionCalculationService::revertOrderSettlement($pembayaran->order_id, 'Refund oleh Administrator');
                } else {
                    DB::table('orders')->where('id', $pembayaran->order_id)->update([
                        'status' => 'dibatalkan',
                        'updated_at' => now()
                    ]);
                }

                $order = DB::table('orders')->where('id', $pembayaran->order_id)->first();
                if ($order) {
                    OrderNotificationService::notifyOrderStatusUpdated($order, 'Dibatalkan', 'Pembayaran telah direfund oleh Administrator.');
                }

                // Catat Trail Audit Pembatalan Pesanan akibat Refund
                AuditLogService::log(
                    action: 'pembatalan_pesanan',
                    auditableType: 'App\Models\Penyewaan',
                    auditableId: $pembayaran->order_id,
                    before: [
                        'order_id'       => $pembayaran->order_id,
                        'status'         => $orderBefore ? $orderBefore->status : null,
                        'total_biaya'    => $orderBefore ? (float) ($orderBefore->total_biaya ?? $orderBefore->total_price) : null,
                        'alasan'         => 'Refund oleh Administrator',
                    ],
                    after: [
                        'order_id'       => $pembayaran->order_id,
                        'status'         => $order ? $order->status : 'dibatalkan',
                        'total_biaya'    => $orderBefore ? (float) ($orderBefore->total_biaya ?? $orderBefore->total_price) : null,
                        'keterangan'     => 'Pesanan dibatalkan via eksekusi refund pembayaran oleh Admin',
                    ],
                    adminId: auth()->id(),
                    request: request()
                );
            }

            // Catat Trail Audit Refund Pembayaran
            AuditLogService::log(
                action: 'refund_pembayaran',
                auditableType: 'App\Models\Pembayaran',
                auditableId: $id,
                before: [
                    'pembayaran_id'     => $id,
                    'status_sebelum'    => $statusSebelum,
                    'nominal_refund'    => (float) $pembayaran->jumlah,
                    'order_id'          => $pembayaran->order_id,
                    'order_status'      => $orderBefore?->status,
                    'order_total'       => $orderBefore ? (float) ($orderBefore->total_biaya ?? $orderBefore->total_price) : null,
                ],
                after: [
                    'pembayaran_id'     => $id,
                    'status_sesudah'    => 'Refunded',
                    'nominal_refund'    => (float) $pembayaran->jumlah,
                    'order_id'          => $pembayaran->order_id,
                    'order_status'      => $orderBefore ? ($orderBefore->status === 'selesai' ? 'selesai (saldo vendor direvert)' : 'dibatalkan') : null,
                    'keterangan'        => 'Refund dana pelanggan diproses oleh Admin',
                ],
                adminId: auth()->id(),
                request: request()
            );

            DB::commit();
            return back()->with('success', 'Status pembayaran telah diubah menjadi Refunded dan tercatat dalam trail audit.');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Gagal memproses refund: ' . $e->getMessage());
        }
    }

    public function cancelOrder(Request $request, $orderId)
    {
        $order = DB::table('orders')->where('id', $orderId)->first();
        if (!$order) {
            return back()->with('error', 'Pesanan tidak ditemukan.');
        }

        DB::beginTransaction();
        try {
            $statusSebelum = $order->status;

            DB::table('orders')->where('id', $orderId)->update([
                'status'     => 'dibatalkan',
                'updated_at' => now(),
            ]);

            // Pulihkan kuota voucher jika transaksi dibatalkan
            if (!empty($order->voucher_id)) {
                DB::table('vouchers')->where('id', $order->voucher_id)->where('kuota_terpakai', '>', 0)->decrement('kuota_terpakai');
            }

            // Catat Trail Audit Pembatalan Pesanan oleh Admin
            AuditLogService::log(
                action: 'pembatalan_pesanan',
                auditableType: 'App\Models\Penyewaan',
                auditableId: $orderId,
                before: [
                    'order_id'       => $orderId,
                    'status'         => $statusSebelum,
                    'total_biaya'    => (float) ($order->total_biaya ?? $order->total_price ?? 0),
                    'payment_method' => $order->payment_method ?? null,
                    'customer_name'  => $order->customer_name ?? null,
                ],
                after: [
                    'order_id'       => $orderId,
                    'status'         => 'dibatalkan',
                    'alasan'         => $request->input('alasan', 'Dibatalkan oleh Administrator'),
                ],
                adminId: auth()->id(),
                request: request()
            );

            OrderNotificationService::notifyOrderStatusUpdated($order, 'Dibatalkan', 'Pesanan telah dibatalkan oleh Administrator.');

            DB::commit();
            return back()->with('success', "Pesanan #INV-{$orderId} berhasil dibatalkan dan tercatat dalam trail audit.");
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Gagal membatalkan pesanan: ' . $e->getMessage());
        }
    }
}
