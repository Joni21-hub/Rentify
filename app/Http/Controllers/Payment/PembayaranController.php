<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Penyewaan;
use App\Models\Pembayaran;
use Illuminate\Support\Facades\Log;

class PembayaranController extends Controller
{
    public function __construct()
    {
        // Set konfigurasi Midtrans
        \Midtrans\Config::$serverKey = config('midtrans.server_key');
        \Midtrans\Config::$isProduction = config('midtrans.is_production');
        \Midtrans\Config::$isSanitized = config('midtrans.is_sanitized');
        \Midtrans\Config::$is3ds = config('midtrans.is_3ds');
    }

    public function pay($id)
    {
        $order = Penyewaan::findOrFail($id);

        // Jika pesanan sudah dibayar/selesai, tidak perlu generate token
        if ($order->status == 'Lunas' || $order->status == 'Selesai' || $order->status == 'Dibatalkan') {
            return redirect()->back()->with('error', 'Pesanan ini tidak bisa dibayar atau sudah lunas.');
        }

        // Siapkan parameter untuk Midtrans
        $params = array(
            'transaction_details' => array(
                'order_id' => $order->kode_booking, // Gunakan kode booking sebagai Order ID
                'gross_amount' => (int) $order->total_biaya,
            ),
            'customer_details' => array(
                'first_name' => $order->customer_name,
                'phone' => $order->customer_whatsapp,
            ),
        );

        try {
            // Dapatkan Snap Token dari Midtrans
            $snapToken = \Midtrans\Snap::getSnapToken($params);
            
            // Tampilkan halaman pembayaran (buat view khusus nanti atau passing ke view yang sudah ada)
            return view('payment.pay', compact('snapToken', 'order'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal memuat sistem pembayaran: ' . $e->getMessage());
        }
    }

    public function callback(Request $request)
    {
        // Verifikasi Signature Key Midtrans (Opsional tapi direkomendasikan untuk keamanan)
        $serverKey = config('midtrans.server_key');
        $hashed = hash("sha512", $request->order_id . $request->status_code . $request->gross_amount . $serverKey);
        
        if ($hashed !== $request->signature_key) {
            return response()->json(['message' => 'Invalid signature'], 403);
        }

        $transaction_status = $request->transaction_status;
        $order_id = $request->order_id;
        $payment_type = $request->payment_type;

        $order = Penyewaan::where('kode_booking', $order_id)->first();
        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        if ($transaction_status == 'capture' || $transaction_status == 'settlement') {
            // Pembayaran Sukses
            $order->update(['status' => 'Berjalan']);

            // Catat di tabel pembayarans
            Pembayaran::updateOrCreate(
                ['order_id' => $order->id],
                [
                    'metode' => $payment_type,
                    'jumlah' => $order->total_biaya,
                    'status' => 'Lunas'
                ]
            );
        } else if ($transaction_status == 'cancel' || $transaction_status == 'deny' || $transaction_status == 'expire') {
            $order->update(['status' => 'Dibatalkan']);
            Pembayaran::updateOrCreate(
                ['order_id' => $order->id],
                [
                    'metode' => $payment_type,
                    'jumlah' => $order->total_biaya,
                    'status' => 'Gagal'
                ]
            );
        } else if ($transaction_status == 'pending') {
            $order->update(['status' => 'Menunggu Pembayaran']);
        }

        return response()->json(['message' => 'Callback received successfully']);
    }
}
