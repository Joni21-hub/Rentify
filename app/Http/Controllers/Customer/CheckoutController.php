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
        // JALUR 1: SEWA SEKARANG (Pesanan Instan dari Halaman Detail Barang)
        if ($request->filled('direct_barang_id')) {
            $barangId = $request->direct_barang_id;
            session(['checkout_direct_id' => $barangId]); 
            session()->forget('checkout_selected_ids'); // Bersihkan memori keranjang
            
            $barang = Barang::with('vendor')->findOrFail($barangId);
            
            // Buat keranjang virtual di dalam memori (TIDAK disimpan ke database)
            $mockItem = new Keranjang();
            $mockItem->id = 0; 
            $mockItem->user_id = auth()->id();
            $mockItem->barang_id = $barang->id;
            $mockItem->jumlah = $request->input('jumlah', 1);
            $mockItem->setRelation('barang', $barang);
            
            $keranjangs = collect([$mockItem]);
            
        } 
        // JALUR 2: CHECKOUT DARI KERANJANG (Hanya ambil yang dicentang!)
        else {
            session()->forget('checkout_direct_id');
            $query = Keranjang::with('barang.vendor')->where('user_id', auth()->id());
            
            // Menangkap array ID barang yang dicentang (name="selected_items[]")
            $selectedIds = $request->input('selected_items', $request->input('keranjang_id', []));
            
            if (!empty($selectedIds)) {
                $selectedIdsArray = is_array($selectedIds) ? $selectedIds : [$selectedIds];
                $query->whereIn('id', $selectedIdsArray);
                session(['checkout_selected_ids' => $selectedIdsArray]); 
            } else {
                // Jika tidak ada yang dicentang sama sekali, tolak ke halaman keranjang
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
        
        // Pastikan jalur mana yang sedang diproses saat simpan ke database
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
            }
        }

        // PENGHAPUSAN KERANJANG YANG AKURAT
        if (!$isDirectCheckout) {
            // Hanya hapus barang yang TEPAT DICENTANG saat checkout
            if (session()->has('checkout_selected_ids')) {
                $selectedSession = session('checkout_selected_ids');
                $selectedSessionArray = is_array($selectedSession) ? $selectedSession : [$selectedSession];
                Keranjang::whereIn('id', $selectedSessionArray)->delete();
                session()->forget('checkout_selected_ids');
            } else {
                Keranjang::where('user_id', auth()->id())->delete();
            }
        } else {
            // Jika jalur 'Sewa Sekarang', keranjang di database TIDAK DISENTUH SAMA SEKALI
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

    public function qris($id) { return view('customer.checkout.qris', ['id' => $id, 'total' => session('total_bayar_semua', 0)]); }

    public function struk($id)
    {
        return view('customer.checkout.struk', [
            'id' => $id,
            'keranjangPerVendor' => session('keranjang_per_vendor', collect()),
            'metode' => session('metode_pembayaran', 'COD'),
            'total' => session('total_bayar_semua', 0),
            'no_hp' => session('no_hp', ''),
            'waktu_pesan' => session('waktu_pesan', Carbon::now('Asia/Jakarta')->format('Y-m-d H:i:s')),
            'durasi_sewa_array' => session('durasi_sewa_array', []),
            'jaminan_array' => session('jaminan_array', []),
            'opsi_array' => session('opsi_array', []) 
        ]);
    }
}