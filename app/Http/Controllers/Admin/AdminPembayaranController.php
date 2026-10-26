<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

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
            DB::table('pembayarans')->where('id', $id)->update([
                'status' => 'Lunas',
                'paid_at' => now(),
                'updated_at' => now()
            ]);

            // Update status pesanan jika masih pending
            if ($pembayaran->order_id) {
                DB::table('orders')->where('id', $pembayaran->order_id)->update([
                    'status' => 'Menunggu Konfirmasi',
                    'paid_at' => now(),
                    'updated_at' => now()
                ]);

                $order = DB::table('orders')->where('id', $pembayaran->order_id)->first();
                if ($order) {
                    \App\Services\OrderNotificationService::notifyPaymentConfirmed(
                        $order,
                        'Transfer Bank (Verifikasi Manual Admin)',
                        (float) ($pembayaran->jumlah ?? $order->total_price ?? 0)
                    );
                }
            }

            DB::commit();
            return back()->with('success', 'Pembayaran berhasil diverifikasi secara manual.');
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
            DB::table('pembayarans')->where('id', $id)->update([
                'status' => 'Refunded',
                'updated_at' => now()
            ]);

            if ($pembayaran->order_id) {
                $orderBefore = DB::table('orders')->where('id', $pembayaran->order_id)->first();
                if ($orderBefore && strtolower($orderBefore->status) === 'selesai') {
                    // Revert saldo vendor secara atomik jika pesanan sebelumnya sudah lunas/selesai
                    \App\Services\TransactionCalculationService::revertOrderSettlement($pembayaran->order_id, 'Refund oleh Administrator');
                } else {
                    DB::table('orders')->where('id', $pembayaran->order_id)->update([
                        'status' => 'dibatalkan',
                        'updated_at' => now()
                    ]);
                }

                $order = DB::table('orders')->where('id', $pembayaran->order_id)->first();
                if ($order) {
                    \App\Services\OrderNotificationService::notifyOrderStatusUpdated($order, 'Dibatalkan', 'Pembayaran telah direfund oleh Administrator.');
                }
            }

            DB::commit();
            return back()->with('success', 'Status pembayaran telah diubah menjadi Refunded.');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Gagal memproses refund: ' . $e->getMessage());
        }
    }
}
