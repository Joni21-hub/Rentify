<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\Barang;
use App\Models\Keranjang;

class CheckoutController extends Controller
{
    public function index(Request $request)
    {
        if ($request->filled('direct_barang_id')) {
            $barangId = $request->direct_barang_id;
            session(['checkout_direct_id' => $barangId]); 
            session()->forget('checkout_selected_ids'); 
            
            $barang = Barang::with('vendor')->findOrFail($barangId);
            
            $mockItem = new Keranjang();
            $mockItem->id = 0; 
            $mockItem->user_id = auth()->id();
            $mockItem->barang_id = $barang->id;
            $mockItem->jumlah = $request->input('jumlah', 1);
            $mockItem->setRelation('barang', $barang);
            
            $keranjangs = collect([$mockItem]);
            
        } else {
            session()->forget('checkout_direct_id');
            $query = Keranjang::with('barang.vendor')->where('user_id', auth()->id());
            
            $selectedIds = $request->input('selected_items', $request->input('keranjang_id', []));
            
            if (!empty($selectedIds)) {
                $selectedIdsArray = is_array($selectedIds) ? $selectedIds : [$selectedIds];
                $query->whereIn('id', $selectedIdsArray);
                session(['checkout_selected_ids' => $selectedIdsArray]); 
            } else {
                return redirect()->route('customer.keranjang')->with('error', 'Silakan centang minimal satu barang yang ingin disewa.');
            }
            
            $keranjangs = $query->get();
        }

        if ($keranjangs->isEmpty()) {
            return redirect()->route('customer.home')->with('error', 'Barang tidak ditemukan.');
        }

        $keranjangPerVendor = $keranjangs->groupBy(function($item) {
            return $item->barang->vendor_id;
        });

        return view('customer.checkout.index', compact('keranjangPerVendor', 'keranjangs'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'no_hp' => 'required|string', 
            'metode_pembayaran' => 'required|in:COD,QRIS',
            'durasi_sewa' => 'required|array', 
            'opsi_pengiriman' => 'required|array', 
            'ongkir_vendor' => 'required|array',
            'jaminan' => 'required|array', 
        ]);

        $waktuSekarang = Carbon::now('Asia/Jakarta'); 
        $isDirectCheckout = false;
        
        if (session()->has('checkout_direct_id')) {
            $isDirectCheckout = true;
            $barangId = session('checkout_direct_id');
            $barang = Barang::with('vendor')->findOrFail($barangId);
            
            $mockItem = new Keranjang();
            $mockItem->id = 0;
            $mockItem->user_id = auth()->id();
            $mockItem->barang_id = $barang->id;
            $mockItem->jumlah = 1;
            $mockItem->setRelation('barang', $barang);
            
            $keranjangs = collect([$mockItem]);
        } else {
            $query = Keranjang::with('barang')->where('user_id', auth()->id());
            
            if (session()->has('checkout_selected_ids')) {
                $selectedSession = session('checkout_selected_ids');
                $selectedSessionArray = is_array($selectedSession) ? $selectedSession : [$selectedSession];
                $query->whereIn('id', $selectedSessionArray);
            }
            $keranjangs = $query->get();
        }

        $keranjangPerVendor = $keranjangs->groupBy(fn($item) => $item->barang->vendor_id);
        $invoiceIds = [];
        $totalBayarSemua = 0; 
        $nomorWaAman = $request->no_hp;

        foreach ($keranjangPerVendor as $vendorId => $items) {
            $opsi = $request->opsi_pengiriman[$vendorId] ?? 'ambil';
            $ongkir = (int) ($request->ongkir_vendor[$vendorId] ?? 0);
            $durasi = (int) ($request->durasi_sewa[$vendorId] ?? 1);
            $jaminanTerpilih = $request->jaminan[$vendorId] ?? 'KTP'; 
            
            $waktuKembali = $waktuSekarang->copy()->addDays($durasi);
            $subtotalSewa = 0;

            foreach ($items as $item) {
                $hargaMarkup = $item->barang->harga_sewa_harian * 1.05;
                $subtotalSewa += ($hargaMarkup * $item->jumlah * $durasi);
            }

            $totalHargaVendor = $subtotalSewa + $ongkir;
            $totalBayarSemua += $totalHargaVendor;

            $orderId = DB::table('orders')->insertGetId([
                'user_id' => auth()->id(),
                'vendor_id' => $vendorId, 
                'customer_name' => auth()->user()->name ?? 'Customer',
                'customer_whatsapp' => $nomorWaAman, 
                'shipping_address' => $opsi === 'diantar' ? ($request->alamat_customer ?? 'Sesuai Titik GPS') : 'Diambil di Toko',
                'pin_location' => $opsi === 'diantar' ? ($request->cust_lat . ',' . $request->cust_lon) : null,
                'shipping_method' => $opsi,
                'shipping_fee' => $ongkir,
                'start_rent' => $waktuSekarang,
                'end_rent' => $waktuKembali,
                'duration_days' => $durasi,
                'jaminan' => $jaminanTerpilih, 
                'payment_method' => $request->metode_pembayaran,
                'total_price' => $totalHargaVendor,
                'status' => 'Menunggu Konfirmasi',
                'created_at' => $waktuSekarang,
                'updated_at' => $waktuSekarang
            ]);

            $invoiceIds[] = 'INV-' . $orderId;

            foreach ($items as $item) {
                DB::table('order_items')->insert([
                    'order_id' => $orderId,
                    'product_id' => $item->barang_id,
                    'product_name' => $item->barang->nama,
                    'price' => $item->barang->harga_sewa_harian * 1.05, 
                    'quantity' => $item->jumlah,
                    'created_at' => $waktuSekarang,
                    'updated_at' => $waktuSekarang
                ]);

                // FITUR BARU: Kurangi stok di gudang secara otomatis saat barang resmi disewa!
                \App\Models\Barang::where('id', $item->barang_id)->decrement('stok_total', $item->jumlah);
            }
        }

        if (!$isDirectCheckout) {
            if (session()->has('checkout_selected_ids')) {
                $selectedSession = session('checkout_selected_ids');
                $selectedSessionArray = is_array($selectedSession) ? $selectedSession : [$selectedSession];
                Keranjang::whereIn('id', $selectedSessionArray)->delete();
                session()->forget('checkout_selected_ids');
            } else {
                Keranjang::where('user_id', auth()->id())->delete();
            }
        } else {
            session()->forget('checkout_direct_id');
        }

        session([
            'metode_pembayaran' => $request->metode_pembayaran,
            'total_bayar_semua' => $totalBayarSemua,
            'no_hp' => $nomorWaAman,
            'waktu_pesan' => $waktuSekarang->format('Y-m-d H:i:s'),
            'keranjang_per_vendor' => $keranjangPerVendor,
            'durasi_sewa_array' => $request->durasi_sewa,
            'jaminan_array' => $request->jaminan,
            'opsi_array' => $request->opsi_pengiriman 
        ]);

        $gabunganInvoice = implode('_', $invoiceIds); 
        return redirect()->route($request->metode_pembayaran === 'COD' ? 'customer.struk' : 'customer.qris', ['id' => $gabunganInvoice]);
    }

    public function qris($id) 
    { 
        $total = session('total_bayar_semua', 0);
        
        if ($total == 0) {
            $orderIds = [];
            foreach (explode('_', $id) as $part) {
                if (str_starts_with($part, 'INV-')) $orderIds[] = (int) str_replace('INV-', '', $part);
            }
            $total = DB::table('orders')->whereIn('id', $orderIds)->sum('total_price');
        }

        return view('customer.checkout.qris', ['id' => $id, 'total' => $total]); 
    }

    public function struk($id)
    {
        $keranjangPerVendor = session('keranjang_per_vendor', collect());
        $metode = session('metode_pembayaran', 'COD');
        $total = session('total_bayar_semua', 0);
        $no_hp = session('no_hp', '');
        $waktu_pesan = session('waktu_pesan', Carbon::now('Asia/Jakarta')->format('Y-m-d H:i:s'));
        $durasi_sewa_array = session('durasi_sewa_array', []);
        $jaminan_array = session('jaminan_array', []);
        $opsi_array = session('opsi_array', []);

        if ($total == 0 || $keranjangPerVendor->isEmpty()) {
            $orderIds = [];
            foreach (explode('_', $id) as $part) {
                if (str_starts_with($part, 'INV-')) $orderIds[] = (int) str_replace('INV-', '', $part);
            }

            $orders = DB::table('orders')->whereIn('id', $orderIds)->get();

            if ($orders->isNotEmpty()) {
                $total = $orders->sum('total_price');
                $metode = $orders->first()->payment_method ?? 'COD';
                $no_hp = $orders->first()->customer_whatsapp ?? '';
                $waktu_pesan = $orders->first()->created_at ?? Carbon::now('Asia/Jakarta')->format('Y-m-d H:i:s');

                $mockKeranjangList = collect();
                foreach ($orders as $order) {
                    $durasi_sewa_array[$order->vendor_id] = $order->duration_days;
                    $jaminan_array[$order->vendor_id] = $order->jaminan;
                    $opsi_array[$order->vendor_id] = $order->shipping_method;

                    $items = DB::table('order_items')->where('order_id', $order->id)->get();
                    foreach ($items as $item) {
                        $barang = Barang::with('vendor')->find($item->product_id);
                        if ($barang) {
                            $mockItem = new Keranjang();
                            $mockItem->id = $item->id;
                            $mockItem->user_id = $order->user_id;
                            $mockItem->barang_id = $item->product_id;
                            $mockItem->jumlah = $item->quantity;
                            $mockItem->setRelation('barang', $barang);
                            $mockKeranjangList->push($mockItem);
                        }
                    }
                }

                if ($mockKeranjangList->isNotEmpty()) {
                    $keranjangPerVendor = $mockKeranjangList->groupBy(fn($item) => $item->barang->vendor_id);
                }
            }
        }

        return view('customer.checkout.struk', [
            'id' => $id,
            'keranjangPerVendor' => $keranjangPerVendor,
            'metode' => $metode,
            'total' => $total,
            'no_hp' => $no_hp,
            'waktu_pesan' => $waktu_pesan,
            'durasi_sewa_array' => $durasi_sewa_array,
            'jaminan_array' => $jaminan_array,
            'opsi_array' => $opsi_array 
        ]);
    }
}