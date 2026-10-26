<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PesananController extends Controller
{
    public function index(Request $request)
    {
        $userId = auth()->id();

        $baseQuery = DB::table('orders')
            ->leftJoin('users as vendors', 'orders.vendor_id', '=', 'vendors.id')
            ->select('orders.*', 'vendors.vendor_name', 'vendors.name as owner_name', 'vendors.whatsapp_vendor')
            ->where('orders.user_id', $userId);

        $countSemua      = (clone $baseQuery)->count();
        $countBelumBayar = (clone $baseQuery)->whereIn('orders.status', [
            'Menunggu Pembayaran', 'PENDING_PAYMENT', 'pending'
        ])->count();
        $countKonfirmasi = (clone $baseQuery)->whereIn('orders.status', [
            'Menunggu Konfirmasi', 'Menunggu Konfirmasi Toko', 'Disetujui'
        ])->count();
        $countSiap       = (clone $baseQuery)->whereIn('orders.status', [
            'Siap Diambil/Dikirim', 'Siap Diambil', 'Siap Dikirim'
        ])->count();
        $countBerjalan   = (clone $baseQuery)->whereIn('orders.status', [
            'Sedang Disewa', 'Berjalan', 'PAID', 'dibayar'
        ])->count();
        $countUlasan     = (clone $baseQuery)->where(function($q) {
            $q->where('orders.status', 'Menunggu Ulasan')
              ->orWhere(function($sub) {
                  $sub->where('orders.status', 'Selesai')
                      ->whereNotExists(function($notEx) {
                          $notEx->select(DB::raw(1))
                                ->from('ulasans')
                                ->whereColumn('ulasans.order_id', 'orders.id');
                      });
              });
        })->count();
        $countSelesai    = (clone $baseQuery)->where('orders.status', 'Selesai')->count();
        $countDibatalkan = (clone $baseQuery)->whereIn('orders.status', [
            'Dibatalkan', 'CANCELLED', 'EXPIRED'
        ])->count();

        // Compatibility
        $countMenunggu   = $countBelumBayar + $countKonfirmasi;

        $statusFilter = $request->query('status', 'semua');
        $query = clone $baseQuery;

        if ($statusFilter === 'belum_bayar') {
            $query->whereIn('orders.status', [
                'Menunggu Pembayaran', 'PENDING_PAYMENT', 'pending'
            ]);
        } elseif ($statusFilter === 'konfirmasi') {
            $query->whereIn('orders.status', [
                'Menunggu Konfirmasi', 'Menunggu Konfirmasi Toko', 'Siap Diambil/Dikirim', 'Disetujui'
            ]);
        } elseif ($statusFilter === 'siap_dikirim') {
            $query->whereIn('orders.status', [
                'Siap Diambil/Dikirim', 'Siap Diambil', 'Siap Dikirim'
            ]);
        } elseif ($statusFilter === 'menunggu') {
            $query->whereIn('orders.status', [
                'Menunggu Konfirmasi', 'Menunggu Pembayaran', 'PENDING_PAYMENT', 'pending', 'Menunggu Konfirmasi Toko', 'Disetujui'
            ]);
        } elseif ($statusFilter === 'berjalan') {
            $query->whereIn('orders.status', [
                'PAID', 'Disetujui', 'Sedang Disewa', 'Berjalan', 'dibayar'
            ]);
        } elseif ($statusFilter === 'ulasan') {
            $query->where(function($q) {
                $q->where('orders.status', 'Menunggu Ulasan')
                  ->orWhere(function($sub) {
                      $sub->where('orders.status', 'Selesai')
                          ->whereNotExists(function($notEx) {
                              $notEx->select(DB::raw(1))
                                    ->from('ulasans')
                                    ->whereColumn('ulasans.order_id', 'orders.id');
                          });
                  });
            });
        } elseif ($statusFilter === 'selesai') {
            $query->where('orders.status', 'Selesai');
        } elseif ($statusFilter === 'dibatalkan') {
            $query->whereIn('orders.status', ['Dibatalkan', 'CANCELLED', 'EXPIRED']);
        }

        $orders = $query->orderBy('orders.created_at', 'desc')->get();

        foreach ($orders as $order) {
            $order->items = DB::table('order_items')
                ->leftJoin('barang', 'order_items.product_id', '=', 'barang.id')
                ->select('order_items.*', 'barang.deposit', 'barang.denda_per_hari', 'barang.cover_photo', 'barang.latitude', 'barang.longitude', 'barang.alamat as alamat_toko')
                ->where('order_items.order_id', $order->id)
                ->get();

            $order->ulasans = DB::table('ulasans')
                ->where('order_id', $order->id)
                ->get();
        }

        return view('customer.pesanan.index', compact(
            'orders', 
            'statusFilter', 
            'countSemua', 
            'countBelumBayar', 
            'countKonfirmasi', 
            'countSiap',
            'countBerjalan', 
            'countUlasan', 
            'countSelesai', 
            'countDibatalkan',
            'countMenunggu'
        ));
    }

    public function selesaikan($id)
    {
        $order = DB::table('orders')->where('id', $id)->where('user_id', auth()->id())->first();

        if (!$order) {
            return redirect()->back()->with('error', 'Pesanan tidak ditemukan!');
        }

        if (in_array($order->status, ['Selesai', 'Menunggu Ulasan'])) {
            return redirect()->back()->with('error', 'Pesanan ini sudah selesai sebelumnya.');
        }

        try {
            \App\Services\TransactionCalculationService::settleOrderCompletion($id);

            // Alirkan status ke 'Menunggu Ulasan' agar customer diarahkan memberi rating & testimoni
            DB::table('orders')->where('id', $id)->update([
                'status'     => 'Menunggu Ulasan',
                'updated_at' => Carbon::now('Asia/Jakarta'),
            ]);

            $updatedOrder = DB::table('orders')->where('id', $id)->first();
            if ($updatedOrder) {
                \App\Services\OrderNotificationService::notifyOrderStatusUpdated($updatedOrder, 'Menunggu Ulasan');
            }

            return redirect()->route('customer.pesanan', ['status' => 'ulasan'])
                ->with('success', '🎉 Pesanan berhasil diselesaikan! Yuk berikan ulasan bintang & pengalaman rental Anda.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menyelesaikan pesanan: ' . $e->getMessage());
        }
    }

    public function batalkan($id)
    {
        $order = DB::table('orders')->where('id', $id)->where('user_id', auth()->id())->first();

        if (!$order) {
            return redirect()->back()->with('error', 'Pesanan tidak ditemukan!');
        }

        $cancellableStatuses = ['Menunggu Konfirmasi', 'Menunggu Pembayaran', 'PENDING_PAYMENT', 'pending'];
        if (!in_array($order->status, $cancellableStatuses)) {
            return redirect()->back()->with('error', 'Pesanan tidak dapat dibatalkan pada status saat ini.');
        }

        DB::table('orders')->where('id', $id)->update([
            'status' => 'Dibatalkan',
            'updated_at' => Carbon::now('Asia/Jakarta'),
        ]);

        if (!empty($order->voucher_id)) {
            DB::table('vouchers')->where('id', $order->voucher_id)->where('kuota_terpakai', '>', 0)->decrement('kuota_terpakai');
        }

        // Refund koin jika checkout menggunakan koin reward
        $spentLedger = \App\Models\MomentCoinLedger::where('order_id', $order->id)
            ->where('interaction_type', 'checkout_spend')
            ->first();

        if ($spentLedger) {
            app(\App\Services\CoinAccountingService::class)->refundOrderCoins(
                $order->user_id,
                abs($spentLedger->coins_amount),
                $order->id
            );
        }

        return redirect()->back()->with('success', 'Pesanan berhasil dibatalkan dan koin Anda telah dikembalikan!');
    }
}
