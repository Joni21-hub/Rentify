<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Penyewaan;
use App\Models\Pembayaran;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class PembayaranController extends Controller
{
    public function __construct()
    {
        // Set konfigurasi Midtrans
        \Midtrans\Config::$serverKey = config('midtrans.server_key');
        \Midtrans\Config::$isProduction = config('midtrans.is_production');
        \Midtrans\Config::$isSanitized = config('midtrans.is_sanitized', true);
        \Midtrans\Config::$is3ds = config('midtrans.is_3ds', true);
    }

    public function pay($id)
    {
        $order = Penyewaan::findOrFail($id);

        if ($order->status == 'Lunas' || $order->status == 'Selesai' || $order->status == 'Dibatalkan') {
            return redirect()->back()->with('error', 'Pesanan ini tidak bisa dibayar atau sudah lunas.');
        }

        $params = [
            'transaction_details' => [
                'order_id' => $order->kode_booking,
                'gross_amount' => (int) round($order->total_biaya),
            ],
            'customer_details' => [
                'first_name' => $order->customer_name,
                'phone' => $order->customer_whatsapp,
            ],
            'item_details' => [
                [
                    'id' => 'INV-' . $order->id,
                    'price' => (int) round($order->total_biaya),
                    'quantity' => 1,
                    'name' => 'Sewa Barang ' . $order->kode_booking,
                ]
            ]
        ];

        try {
            $snapToken = \Midtrans\Snap::getSnapToken($params);
            return view('payment.pay', compact('snapToken', 'order'));
        } catch (\Exception $e) {
            Log::error('Midtrans pay() error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal memuat sistem pembayaran: ' . $e->getMessage());
        }
    }

    /**
     * AJAX endpoint untuk verifikasi live status pembayaran dari browser
     */
    public function checkStatus(Request $request, $id)
    {
        $orderIds = [];
        foreach (explode('_', $id) as $part) {
            if (str_starts_with($part, 'INV-')) {
                $orderIds[] = (int) str_replace('INV-', '', $part);
            } elseif (is_numeric($part)) {
                $orderIds[] = (int) $part;
            }
        }

        if (empty($orderIds)) {
            $orders = DB::table('orders')->where('kode_booking', $id)->get();
        } else {
            $orders = DB::table('orders')->whereIn('id', $orderIds)->get();
        }

        if ($orders->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'Pesanan tidak ditemukan'], 404);
        }

        // Cek jika di database statusnya sudah sukses
        $alreadyPaid = $orders->every(function($ord) {
            return in_array($ord->status, ['Menunggu Konfirmasi', 'Disetujui', 'Sedang Disewa', 'berjalan', 'dibayar', 'Selesai']);
        });

        if ($alreadyPaid) {
            return response()->json([
                'success' => true,
                'paid' => true,
                'status' => 'settlement',
                'message' => 'Pembayaran telah lunas & terverifikasi!',
                'redirect_url' => route('customer.struk', ['id' => $id])
            ]);
        }

        // Tentukan Midtrans Order ID yang dicari
        $firstOrder = $orders->first();
        $midtransOrderId = count($orders) === 1 ? $firstOrder->kode_booking : ('RNT-COMBO-' . implode('-', $orders->pluck('id')->toArray()));

        try {
            $response = \Midtrans\Transaction::status($midtransOrderId);
            $trxStatus = $response->transaction_status ?? '';
            $fraudStatus = $response->fraud_status ?? 'accept';
            $paymentType = $response->payment_type ?? 'midtrans';

            $isSettled = false;
            if ($trxStatus == 'capture') {
                if ($fraudStatus == 'accept') $isSettled = true;
            } elseif ($trxStatus == 'settlement') {
                $isSettled = true;
            }

            if ($isSettled) {
                // Update status pesanan ke Menunggu Konfirmasi agar vendor langsung memproses
                DB::table('orders')->whereIn('id', $orders->pluck('id'))->update([
                    'status' => 'Menunggu Konfirmasi',
                    'payment_method' => $paymentType === 'qris' ? 'QRIS' : ($paymentType === 'bank_transfer' ? 'Transfer Bank' : strtoupper($paymentType)),
                    'updated_at' => now(),
                ]);

                // Catat ke tabel pembayarans
                foreach ($orders as $ord) {
                    Pembayaran::updateOrCreate(
                        ['order_id' => $ord->id],
                        [
                            'metode' => $paymentType,
                            'jumlah' => $ord->total_biaya,
                            'status' => 'Lunas'
                        ]
                    );
                }

                return response()->json([
                    'success' => true,
                    'paid' => true,
                    'status' => 'settlement',
                    'message' => 'Pembayaran berhasil dikonfirmasi!',
                    'redirect_url' => route('customer.struk', ['id' => $id])
                ]);
            } elseif ($trxStatus == 'pending') {
                return response()->json([
                    'success' => true,
                    'paid' => false,
                    'status' => 'pending',
                    'message' => 'Menunggu pembayaran diselesaikan oleh customer...'
                ]);
            } elseif (in_array($trxStatus, ['cancel', 'deny', 'expire'])) {
                DB::table('orders')->whereIn('id', $orders->pluck('id'))->update([
                    'status' => 'Dibatalkan',
                    'updated_at' => now(),
                ]);

                return response()->json([
                    'success' => true,
                    'paid' => false,
                    'status' => $trxStatus,
                    'message' => 'Waktu pembayaran telah habis atau dibatalkan.'
                ]);
            }

            return response()->json([
                'success' => true,
                'paid' => false,
                'status' => $trxStatus,
                'message' => 'Status transaksi: ' . $trxStatus
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => true,
                'paid' => false,
                'status' => 'not_found',
                'message' => 'Transaksi belum diproses.'
            ]);
        }
    }

    /**
     * Webhook Midtrans HTTP POST Callback
     */
    public function callback(Request $request)
    {
        $serverKey = config('midtrans.server_key');
        $hashed = hash("sha512", $request->order_id . $request->status_code . $request->gross_amount . $serverKey);
        
        if ($hashed !== $request->signature_key) {
            Log::warning('Midtrans Callback: Invalid Signature', $request->all());
            return response()->json(['message' => 'Invalid signature'], 403);
        }

        $trxStatus = $request->transaction_status;
        $orderId = $request->order_id;
        $paymentType = $request->payment_type;
        $fraudStatus = $request->fraud_status ?? 'accept';

        // Cari data pesanan
        if (str_starts_with($orderId, 'RNT-COMBO-')) {
            $idsPart = str_replace('RNT-COMBO-', '', $orderId);
            $orderIds = explode('-', $idsPart);
            $orders = DB::table('orders')->whereIn('id', $orderIds)->get();
        } else {
            $orders = DB::table('orders')->where('kode_booking', $orderId)->get();
            if ($orders->isEmpty() && str_starts_with($orderId, 'INV-')) {
                $rawId = (int) str_replace('INV-', '', $orderId);
                $orders = DB::table('orders')->where('id', $rawId)->get();
            }
        }

        if ($orders->isEmpty()) {
            Log::warning('Midtrans Callback: Orders not found for ' . $orderId);
            return response()->json(['message' => 'Order not found'], 404);
        }

        $isSettled = false;
        if ($trxStatus == 'capture') {
            if ($fraudStatus == 'accept') $isSettled = true;
        } elseif ($trxStatus == 'settlement') {
            $isSettled = true;
        }

        if ($isSettled) {
            DB::table('orders')->whereIn('id', $orders->pluck('id'))->update([
                'status' => 'Menunggu Konfirmasi',
                'payment_method' => $paymentType === 'qris' ? 'QRIS' : ($paymentType === 'bank_transfer' ? 'Transfer Bank' : strtoupper($paymentType)),
                'updated_at' => now(),
            ]);

            foreach ($orders as $ord) {
                Pembayaran::updateOrCreate(
                    ['order_id' => $ord->id],
                    [
                        'metode' => $paymentType,
                        'jumlah' => $ord->total_biaya,
                        'status' => 'Lunas'
                    ]
                );
            }
            Log::info("Midtrans Callback: Order {$orderId} LUNAS via {$paymentType}");
        } elseif (in_array($trxStatus, ['cancel', 'deny', 'expire'])) {
            DB::table('orders')->whereIn('id', $orders->pluck('id'))->update([
                'status' => 'Dibatalkan',
                'updated_at' => now(),
            ]);
            foreach ($orders as $ord) {
                Pembayaran::updateOrCreate(
                    ['order_id' => $ord->id],
                    [
                        'metode' => $paymentType,
                        'jumlah' => $ord->total_biaya,
                        'status' => 'Gagal'
                    ]
                );
            }
            Log::info("Midtrans Callback: Order {$orderId} BATAL/GAGAL");
        } elseif ($trxStatus == 'pending') {
            DB::table('orders')->whereIn('id', $orders->pluck('id'))->update([
                'status' => 'Menunggu Pembayaran',
                'updated_at' => now(),
            ]);
        }

        return response()->json(['message' => 'Callback processed successfully']);
    }
}
