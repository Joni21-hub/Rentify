<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\Barang;
use App\Models\Keranjang;
use App\Models\Voucher; // PERUBAHAN: Menambahkan model Voucher

class CheckoutController extends Controller
{
    // PERUBAHAN: Menambahkan fungsi AJAX Cek Voucher
    public function cekVoucher(Request $request)
    {
        $kode = strtoupper($request->kode_voucher);
        $vendorIds = $request->vendor_ids; 

        $voucher = Voucher::where('kode_voucher', $kode)
            ->where('is_active', 1)
            ->whereDate('tanggal_mulai', '<=', now())
            ->whereDate('tanggal_selesai', '>=', now())
            ->first();

        if (!$voucher) {
            return response()->json(['success' => false, 'message' => 'Voucher tidak valid atau sudah kadaluarsa.']);
        }
        
        if ($voucher->kuota_terpakai >= $voucher->kuota_total) {
            return response()->json(['success' => false, 'message' => 'Kuota voucher telah habis.']);
        }
        
        if (!isset($vendorIds[$voucher->vendor_id])) {
            return response()->json(['success' => false, 'message' => 'Voucher ini tidak berlaku untuk toko di keranjang Anda.']);
        }
        
        $subtotalToko = (float) $vendorIds[$voucher->vendor_id];
        
        if ($subtotalToko < $voucher->minimal_belanja) {
            return response()->json(['success' => false, 'message' => 'Minimal belanja Rp ' . number_format($voucher->minimal_belanja, 0, ',', '.') . ' tidak terpenuhi untuk toko ini.']);
        }

        $nilaiDiskon = $voucher->tipe_diskon === 'nominal' ? $voucher->nilai_diskon : ($subtotalToko * $voucher->nilai_diskon) / 100;
        
        if ($voucher->tipe_diskon === 'persen' && !is_null($voucher->maksimal_diskon) && $nilaiDiskon > $voucher->maksimal_diskon) {
            $nilaiDiskon = $voucher->maksimal_diskon;
        }

        return response()->json([
            'success' => true, 
            'message' => 'Voucher berhasil diterapkan!', 
            'potongan' => $nilaiDiskon, 
            'vendor_id' => $voucher->vendor_id,
            'voucher_id' => $voucher->id
        ]);
    }
    
    public function index(Request $request)
    {
        $kodeVoucher = $request->input('kode_voucher');

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
            $mockItem->durasi_sewa = $request->input('durasi_sewa', 1); 
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

        return view('customer.checkout.index', compact('keranjangPerVendor', 'keranjangs', 'kodeVoucher'));
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
            'start_date' => 'nullable|date',
            'start_time' => 'nullable|string',
        ]);

        $waktuSekarang = Carbon::now('Asia/Jakarta'); 
        
        $tanggalMulai = $request->input('start_date', $waktuSekarang->format('Y-m-d'));
        $jamMulai = $request->input('start_time', '09:00');
        $waktuMulai = Carbon::parse($tanggalMulai . ' ' . $jamMulai, 'Asia/Jakarta');

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

        foreach ($keranjangPerVendor as $vendorId => $items) {
            $durasiCek = (int) ($request->durasi_sewa[$vendorId] ?? 1);
            $waktuKembaliCek = $waktuMulai->copy()->addDays($durasiCek);

            foreach ($items as $item) {
                $barangCek = Barang::find($item->barang_id);
                if (!$barangCek) {
                    return redirect()->back()->with('error', "⚠️ Produk tidak ditemukan di database.");
                }

                $bookedQty = DB::table('order_items')
                    ->join('orders', 'order_items.order_id', '=', 'orders.id')
                    ->where('order_items.product_id', $item->barang_id)
                    ->whereNotIn('orders.status', ['Selesai', 'Batal', 'Dibatalkan', 'Ditolak'])
                    ->where(function($query) use ($waktuMulai, $waktuKembaliCek) {
                        $query->where('orders.start_rent', '<', $waktuKembaliCek)
                              ->where('orders.end_rent', '>', $waktuMulai);
                    })
                    ->sum('order_items.quantity');

                $stokTersedia = $barangCek->stok_total - $bookedQty;

                if ($stokTersedia < $item->jumlah) {
                    $tglStr = $waktuMulai->format('d M Y (H:i)');
                    return redirect()->back()->with('error', "⚠️ Maaf, stok untuk barang '{$item->barang->nama}' pada jadwal {$tglStr} sudah habis diboking pengguna lain! (Tersedia: {$stokTersedia} unit). Silakan pilih tanggal atau jam lain.");
                }
            }
        }

        $invoiceIds = [];
        $totalBayarSemua = 0; 
        $nomorWaAman = $request->no_hp;
        
        // PERUBAHAN: Menangkap array data voucher jika ada
        $voucherDataStr = $request->input('voucher_data_json');
        $voucherData = [];
        if ($voucherDataStr) {
            $voucherData = json_decode($voucherDataStr, true);
        }

        foreach ($keranjangPerVendor as $vendorId => $items) {
            $opsi = $request->opsi_pengiriman[$vendorId] ?? 'ambil';
            $ongkir = (int) ($request->ongkir_vendor[$vendorId] ?? 0);
            $durasi = (int) ($request->durasi_sewa[$vendorId] ?? 1);
            $jaminanTerpilih = $request->jaminan[$vendorId] ?? 'KTP'; 

            if ($opsi === 'diantar') {
                $latToko = (float) ($items->first()->barang->latitude ?? $items->first()->barang->vendor->latitude ?? 0);
                $lonToko = (float) ($items->first()->barang->longitude ?? $items->first()->barang->vendor->longitude ?? 0);
                $latCust = (float) $request->cust_lat;
                $lonCust = (float) $request->cust_lon;

                if ($latToko && $lonToko && $latCust && $lonCust) {
                    $earthRadius = 6371;
                    $dLat = deg2rad($latToko - $latCust);
                    $dLon = deg2rad($lonToko - $lonCust);
                    $a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($latCust)) * cos(deg2rad($latToko)) * sin($dLon/2) * sin($dLon/2);
                    $c = 2 * asin(sqrt($a));
                    $jarakKm = ceil($earthRadius * $c);
                    
                    $ongkir = max(4000, $jarakKm * 4000);
                } else {
                    if ($ongkir < 10000) $ongkir = 10000;
                }
            } else {
                $ongkir = 0; 
            }
            
            $waktuKembali = $waktuMulai->copy()->addDays($durasi);
            $subtotalSewa = 0;

            foreach ($items as $item) {
                $hargaMarkup = $item->barang->harga_sewa_harian * 1.05;
                $subtotalSewa += ($hargaMarkup * $item->jumlah * $durasi);
            }

            // PERUBAHAN: Memotong harga dan mencatat ID voucher dari AJAX data (jika vendor id cocok)
            $potonganVoucher = 0;
            $voucherIdDipakai = null;
            
            if (isset($voucherData['vendor_id']) && $voucherData['vendor_id'] == $vendorId) {
                $potonganVoucher = $voucherData['potongan'];
                $voucherIdDipakai = $voucherData['voucher_id'];
            }

            // Pastikan diskon tidak membuat total harga minus
            $totalHargaVendor = max(0, ($subtotalSewa - $potonganVoucher)) + $ongkir;
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
                'start_rent' => $waktuMulai,      
                'end_rent' => $waktuKembali,      
                'duration_days' => $durasi,
                'jaminan' => $jaminanTerpilih, 
                'payment_method' => $request->metode_pembayaran,
                'total_price' => $totalHargaVendor,
                
                // PERUBAHAN: Mencatat data voucher ke database
                'voucher_id' => $voucherIdDipakai,
                'potongan_voucher' => $potonganVoucher,

                'status' => 'Menunggu Konfirmasi',
                'created_at' => $waktuSekarang,
                'updated_at' => $waktuSekarang
            ]);

            // PERUBAHAN: Menjalankan increment() pada kuota voucher jika voucher dipakai
            if ($voucherIdDipakai) {
                Voucher::where('id', $voucherIdDipakai)->increment('kuota_terpakai');
            }

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