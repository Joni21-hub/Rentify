<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PesananController extends Controller
{
    public function index()
    {
        $orders = DB::table('orders')
            ->leftJoin('users as vendors', 'orders.vendor_id', '=', 'vendors.id')
            ->select('orders.*', 'vendors.name as vendor_name', 'vendors.whatsapp_vendor')
            ->where('orders.user_id', auth()->id())
            ->orderBy('orders.created_at', 'desc')
            ->get();

        foreach ($orders as $order) {
            $order->items = DB::table('order_items')
                ->leftJoin('barang', 'order_items.product_id', '=', 'barang.id')
                ->select('order_items.*', 'barang.deposit', 'barang.denda_per_hari', 'barang.cover_photo', 'barang.latitude', 'barang.longitude', 'barang.alamat as alamat_toko')
                ->where('order_items.order_id', $order->id)
                ->get();
        }

        return view('customer.pesanan.index', compact('orders'));
    }

    public function selesaikan($id)
    {
        $order = DB::table('orders')->where('id', $id)->where('user_id', auth()->id())->first();

        if (!$order) {
            return redirect()->back()->with('error', 'Pesanan tidak ditemukan!');
        }

        if ($order->status === 'Selesai') {
            return redirect()->back()->with('error', 'Pesanan ini sudah selesai sebelumnya.');
        }

        $ongkir = $order->shipping_fee;
        $totalSewaMarkup = $order->total_price - $ongkir;
        
        $sewaAsliVendor = $totalSewaMarkup / 1.05;
        $feeRentify = $totalSewaMarkup - $sewaAsliVendor;
        $pendapatanVendor = $sewaAsliVendor + $ongkir;

        // REVOLUSI STOK: Kita TIDAK lagi melakukan increment() pada stok_total!
        // Karena kolom stok_total adalah Stok Master Fisik yang tetap, saat status order diubah menjadi 'Selesai',
        // sistem query Anti-Bentrok otomatis melepaskan kunci jadwalnya (karena status 'Selesai' diabaikan di query overlap).
        // Barang otomatis kembali tersedia di tanggal tersebut tanpa membuat angka stok master membengkak!

        DB::table('orders')->where('id', $id)->update([
            'status' => 'Selesai',
            'vendor_earning' => round($pendapatanVendor, 2),
            'rentify_fee' => round($feeRentify, 2),
            'completed_at' => Carbon::now('Asia/Jakarta'),
            'updated_at' => Carbon::now('Asia/Jakarta')
        ]);

        return redirect()->back()->with('success', '🎉 Pesanan berhasil diselesaikan! Terima kasih telah menyewa di Rentify.');
    }
}