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

        $userCoinBalance = 0;
        if (auth()->check()) {
            $coinService = app(\App\Services\CoinAccountingService::class);
            $userCoinBalance = $coinService->getAvailableBalance((int) auth()->id());
        }

        return view('customer.checkout.index', compact('keranjangPerVendor', 'keranjangs', 'kodeVoucher', 'userCoinBalance'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'no_hp' => 'required|string', 
            'metode_pembayaran' => 'nullable|string',
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

        $userId = auth()->id();

        // Batalkan pesanan 'PENDING_PAYMENT' milik user yang sama agar tidak mengunci stoknya sendiri
        if ($userId) {
            $unpaidOrders = DB::table('orders')
                ->where('user_id', $userId)
                ->whereIn('status', ['Menunggu Pembayaran', 'PENDING_PAYMENT'])
                ->get();

            foreach ($unpaidOrders as $unpaid) {
                $spent = DB::table('moment_coin_ledgers')
                    ->where('order_id', $unpaid->id)
                    ->where('interaction_type', 'checkout_spend')
                    ->first();
                if ($spent) {
                    app(\App\Services\CoinAccountingService::class)->refundOrderCoins(
                        $unpaid->user_id,
                        abs($spent->coins_amount),
                        $unpaid->id
                    );
                }
            }

            DB::table('orders')
                ->where('user_id', $userId)
                ->whereIn('status', ['Menunggu Pembayaran', 'PENDING_PAYMENT'])
                ->update(['status' => 'CANCELLED', 'updated_at' => now()]);
        }

        // Batalkan pesanan 'PENDING_PAYMENT' yang sudah lewat 35 menit tanpa pembayaran (QRIS expiry 30 menit + buffer)
        $expiredOrders = DB::table('orders')
            ->whereIn('status', ['Menunggu Pembayaran', 'PENDING_PAYMENT'])
            ->where('created_at', '<', now()->subMinutes(35))
            ->get();

        foreach ($expiredOrders as $exp) {
            $spent = DB::table('moment_coin_ledgers')
                ->where('order_id', $exp->id)
                ->where('interaction_type', 'checkout_spend')
                ->first();
            if ($spent) {
                app(\App\Services\CoinAccountingService::class)->refundOrderCoins(
                    $exp->user_id,
                    abs($spent->coins_amount),
                    $exp->id
                );
            }
        }

        DB::table('orders')
            ->whereIn('status', ['Menunggu Pembayaran', 'PENDING_PAYMENT'])
            ->where('created_at', '<', now()->subMinutes(35))
            ->update(['status' => 'EXPIRED', 'updated_at' => now()]);

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
        $metodeInput = $request->input('metode_pembayaran', 'QRIS');
        
        // Inisialisasi kode unik 3-digit untuk auto-detect mutasi (Metode 2)
        $kodeUnikDigunakan = 0;
        if ($metodeInput === 'QRIS' && config('mutasi.enable_kode_unik', true)) {
            $kodeUnikDigunakan = rand(100, 999);
        }

        // PERUBAHAN: Menangkap array data voucher jika ada
        $voucherDataStr = $request->input('voucher_data_json');
        $voucherData = [];
        if ($voucherDataStr) {
            $voucherData = json_decode($voucherDataStr, true);
        }

        // PERUBAHAN KOIN RENTIFY (REWARD PLATFORM ALA SHOPEE)
        // Hitung total harga sewa murni seluruh produk di keranjang checkout
        $totalBaseRentalSubtotalAll = 0;
        foreach ($keranjangPerVendor as $vId => $vendorItems) {
            $durasiItem = (int) ($request->durasi_sewa[$vId] ?? 1);
            foreach ($vendorItems as $vItem) {
                $totalBaseRentalSubtotalAll += ((float) $vItem->barang->harga_sewa_harian * $vItem->jumlah * $durasiItem);
            }
        }

        $useCoins = $request->boolean('use_coins') || $request->input('use_coins') === '1' || $request->input('use_coins') === 1;
        $coinService = app(\App\Services\CoinAccountingService::class);
        $totalApplicableCoins = 0;
        $totalCoinDiscountRupiah = 0.0;

        if ($useCoins && $userId) {
            $userIdInt = (int) $userId;
            $requestedCoins = (int) $request->input('coins_to_use', 0);
            if ($requestedCoins <= 0) {
                $requestedCoins = $coinService->getAvailableBalance($userIdInt);
            }

            $coinCalc = $coinService->calculateCheckoutDiscount(
                $userIdInt,
                $requestedCoins,
                (int) $totalBaseRentalSubtotalAll
            );

            $totalApplicableCoins = (int) ($coinCalc['applicable_coins'] ?? 0);
            $totalCoinDiscountRupiah = (float) ($coinCalc['discount_rupiah'] ?? 0);
        }

        $remainingCoinsToDistribute = $totalApplicableCoins;
        $remainingRupiahToDistribute = $totalCoinDiscountRupiah;
        $totalVendorBlocks = count($keranjangPerVendor);
        $currentVendorIndex = 0;

        // BUNGKUS TRANSAKSI MULTI-VENDOR SECARA ATOMIK (All or Nothing)
        // Jika ada 1 query gagal antar vendor, seluruh pembuatan order & item di-rollback agar keranjang tidak terfragmentasi
        DB::beginTransaction();
        try {
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
                $subtotalSewaAsli = 0;

                foreach ($items as $item) {
                    $subtotalSewaAsli += ((float)$item->barang->harga_sewa_harian * $item->jumlah * $durasi);
                    $hargaMarkup = $item->barang->harga_sewa_harian * 1.05;
                    $subtotalSewa += ($hargaMarkup * $item->jumlah * $durasi);
                }

                // PERUBAHAN: Validasi voucher ketat di sisi server (mencegah manipulasi diskon dari client/AJAX)
                $potonganVoucher = 0;
                $voucherIdDipakai = null;
                
                if (isset($voucherData['vendor_id']) && $voucherData['vendor_id'] == $vendorId && !empty($voucherData['voucher_id'])) {
                    $vId = (int) $voucherData['voucher_id'];
                    $dbVoucher = Voucher::where('id', $vId)
                        ->where('vendor_id', $vendorId)
                        ->where('is_active', 1)
                        ->whereDate('tanggal_mulai', '<=', now())
                        ->whereDate('tanggal_selesai', '>=', now())
                        ->lockForUpdate()
                        ->first();

                    if ($dbVoucher && $dbVoucher->kuota_terpakai < $dbVoucher->kuota_total) {
                        if ($subtotalSewaAsli >= (float) $dbVoucher->minimal_belanja) {
                            if ($dbVoucher->tipe_diskon === 'nominal') {
                                $calc = (float) $dbVoucher->nilai_diskon;
                            } else {
                                $calc = ($subtotalSewaAsli * (float) $dbVoucher->nilai_diskon) / 100;
                                if (!is_null($dbVoucher->maksimal_diskon) && $calc > (float) $dbVoucher->maksimal_diskon) {
                                    $calc = (float) $dbVoucher->maksimal_diskon;
                                }
                            }
                            $potonganVoucher = min($subtotalSewaAsli, round($calc, 2));
                            $voucherIdDipakai = $dbVoucher->id;
                        }
                    }
                }

                // Alokasikan diskon koin secara proporsional ke order toko ini
                $currentVendorIndex++;
                $orderCoins = 0;
                $orderCoinDiscount = 0.0;

                if ($totalApplicableCoins > 0 && $totalBaseRentalSubtotalAll > 0) {
                    if ($currentVendorIndex === $totalVendorBlocks) {
                        $orderCoins = $remainingCoinsToDistribute;
                        $orderCoinDiscount = (float) $remainingRupiahToDistribute;
                    } else {
                        $ratio = $subtotalSewaAsli / $totalBaseRentalSubtotalAll;
                        $orderCoins = min($remainingCoinsToDistribute, (int) floor($totalApplicableCoins * $ratio));
                        $orderCoinDiscount = (float) ($orderCoins * 100);
                        $remainingCoinsToDistribute -= $orderCoins;
                        $remainingRupiahToDistribute -= $orderCoinDiscount;
                    }
                }

                // Kenakan kode unik hanya pada salah satu order jika checkout gabungan
                $kodeUnikOrder = 0;
                if ($kodeUnikDigunakan > 0) {
                    $kodeUnikOrder = $kodeUnikDigunakan;
                    $kodeUnikDigunakan = 0;
                }

                // Hitung kalkulasi resmi transaksi menggunakan TransactionCalculationService
                $pricing = \App\Services\TransactionCalculationService::calculateOrderPricing(
                    $subtotalSewaAsli,
                    (float) $ongkir,
                    (float) $potonganVoucher,
                    (int) $kodeUnikOrder,
                    (float) $orderCoinDiscount
                );

                $totalHargaVendor = $pricing['total_price'];
                $totalBayarSemua += $totalHargaVendor;

                $orderId = DB::table('orders')->insertGetId([
                    'user_id' => auth()->id(),
                    'vendor_id' => $vendorId, 
                    'kode_booking' => 'RNT' . strtoupper(uniqid()),
                    'customer_name' => auth()->user()->name ?? 'Customer',
                    'customer_whatsapp' => $nomorWaAman, 
                    'alamat_pengiriman' => $opsi === 'diantar' ? ($request->alamat_customer ?? 'Sesuai Titik GPS') : 'Diambil di Toko',
                    'shipping_address' => $opsi === 'diantar' ? ($request->alamat_customer ?? 'Sesuai Titik GPS') : 'Diambil di Toko',
                    'pin_location' => $opsi === 'diantar' ? ($request->cust_lat . ',' . $request->cust_lon) : null,
                    'metode_pengambilan' => $opsi,
                    'shipping_method' => $opsi,
                    'shipping_fee' => $ongkir,
                    'kode_unik' => $kodeUnikOrder,
                    'tanggal_mulai' => $waktuMulai,
                    'start_rent' => $waktuMulai,      
                    'tanggal_selesai' => $waktuKembali,
                    'end_rent' => $waktuKembali,      
                    'duration_days' => $durasi,
                    'jaminan' => $jaminanTerpilih, 
                    'payment_method' => $metodeInput,
                    'total_biaya' => $totalHargaVendor,
                    'total_price' => $totalHargaVendor,
                    'vendor_earning' => $pricing['vendor_earning'],
                    'rentify_fee' => $pricing['rentify_fee'],
                    
                    // PERUBAHAN: Mencatat data voucher ke database
                    'voucher_id' => $voucherIdDipakai,
                    'potongan_voucher' => $potonganVoucher,

                    // PERUBAHAN: Mencatat data diskon koin Rentify ke database
                    'koin_digunakan' => $orderCoins,
                    'potongan_koin' => $orderCoinDiscount,

                    'status' => $metodeInput === 'COD' ? 'Menunggu Konfirmasi' : 'PENDING_PAYMENT',
                    'created_at' => $waktuSekarang,
                    'updated_at' => $waktuSekarang
                ]);

                // Eksekusi pemotongan saldo koin user secara atomik FIFO jika menggunakan koin
                if ($orderCoins > 0 && $userId) {
                    $coinService->spendCoins((int) $userId, $orderCoins, $orderId);
                }

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

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Log::error('[CheckoutController] Gagal memproses pesanan multi-vendor, transaksi di-rollback: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('error', '⚠️ Terjadi kesalahan saat memproses pesanan Anda. Seluruh transaksi dibatalkan demi keamanan, silakan coba beberapa saat lagi.');
        }

        $metodeInput = $request->input('metode_pembayaran', 'QRIS');

        session([
            'metode_pembayaran' => $metodeInput,
            'total_bayar_semua' => $totalBayarSemua,
            'total_koin_digunakan' => $totalApplicableCoins,
            'total_potongan_koin' => $totalCoinDiscountRupiah,
            'no_hp' => $nomorWaAman,
            'waktu_pesan' => $waktuSekarang->format('Y-m-d H:i:s'),
            'durasi_sewa_array' => $request->durasi_sewa,
            'jaminan_array' => $request->jaminan,
            'opsi_array' => $request->opsi_pengiriman 
        ]);

        $gabunganInvoice = implode('_', $invoiceIds);

        // Kirim notifikasi WhatsApp otomatis ke Customer & Vendor
        foreach ($invoiceIds as $inv) {
            $ordId = (int) str_replace('INV-', '', $inv);
            $createdOrder = DB::table('orders')->where('id', $ordId)->first();
            if ($createdOrder) {
                \App\Services\OrderNotificationService::notifyOrderCreated($createdOrder);
            }
        }

        // Jika COD, langsung ke struk. Jika Transfer/QRIS/E-Wallet, arahkan ke halaman pembayaran (Snap Midtrans)
        if ($metodeInput === 'COD') {
            return redirect()->route('customer.struk', ['id' => $gabunganInvoice]);
        }

        return redirect()->route('customer.qris', ['id' => $gabunganInvoice]);
    }

    public function setCod(Request $request, $id)
    {
        $orderIds = [];
        foreach (explode('_', $id) as $part) {
            $part = trim($part);
            if (str_starts_with($part, 'INV-')) {
                $orderIds[] = (int) str_replace('INV-', '', $part);
            } elseif (is_numeric($part)) {
                $orderIds[] = (int) $part;
            }
        }

        DB::table('orders')->whereIn('id', $orderIds)->update([
            'payment_method' => 'COD',
            'status' => 'Menunggu Konfirmasi',
            'updated_at' => Carbon::now('Asia/Jakarta')
        ]);

        // Kirim notifikasi WhatsApp pesanan COD ke Customer & Vendor
        foreach ($orderIds as $ordId) {
            $codOrder = DB::table('orders')->where('id', $ordId)->first();
            if ($codOrder) {
                \App\Services\OrderNotificationService::notifyOrderCreated($codOrder);
            }
        }

        session([
            'metode_pembayaran' => 'COD'
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'redirect_url' => route('customer.struk', ['id' => $id])
            ]);
        }

        return redirect()->route('customer.struk', ['id' => $id])
            ->with('success', 'Metode pembayaran berhasil dialihkan ke Bayar di Tempat (COD).');
    }

    public function qris(Request $request, $id)
    {
        $orderIds = [];
        foreach (explode('_', $id) as $part) {
            $part = trim($part);
            if (str_starts_with($part, 'INV-')) {
                $orderIds[] = (int) str_replace('INV-', '', $part);
            } elseif (is_numeric($part)) {
                $orderIds[] = (int) $part;
            }
        }

        $orders = DB::table('orders')->whereIn('id', $orderIds)->get();
        if ($orders->isEmpty()) {
            return redirect()->route('customer.pesanan')->with('error', 'Pesanan tidak ditemukan.');
        }

        // Jika sudah lunas, langsung ke struk
        $isPaid = $orders->every(function($o) {
            return in_array($o->status, ['PAID', 'Menunggu Konfirmasi', 'Disetujui', 'Sedang Disewa', 'Selesai']);
        });

        if ($isPaid) {
            return redirect()->route('customer.struk', ['id' => $id]);
        }

        $firstOrder     = $orders->first();
        $totalKodeUnik  = $orders->sum('kode_unik');

        // Pastikan kode unik terpasang untuk auto-verifikasi mutasi notifikasi (Metode 2)
        if (config('mutasi.enable_kode_unik', true) && $totalKodeUnik == 0 && in_array($firstOrder->status, ['PENDING_PAYMENT', 'Menunggu Pembayaran'])) {
            $newKodeUnik = rand(100, 999);
            DB::table('orders')->where('id', $firstOrder->id)->update([
                'kode_unik'   => $newKodeUnik,
                'total_biaya' => ($firstOrder->total_biaya ?: $firstOrder->total_price) + $newKodeUnik,
                'total_price' => ($firstOrder->total_price ?: $firstOrder->total_biaya) + $newKodeUnik,
                'updated_at'  => now(),
            ]);
            $orders = DB::table('orders')->whereIn('id', $orderIds)->get();
            $totalKodeUnik = $newKodeUnik;
        }

        $total          = $orders->sum('total_biaya') ?: $orders->sum('total_price');
        $subtotal       = max(0, $total - $totalKodeUnik);
        $channelCode    = 'QRIS';
        $orderReference = count($orders) === 1
            ? $firstOrder->kode_booking
            : ('RNT-COMBO-' . implode('-', $orderIds));

        // Generate QRIS Dinamis Standar Bank Indonesia / ASPI
        $qrisService  = app(\App\Services\QrisService::class);
        $qrisPayload  = $qrisService->generateDynamicQris($total, $orderReference);
        $qrisImageUrl = $qrisService->getQrImageUrl($qrisPayload, 350);
        $merchantName = $qrisService->getMerchantName($qrisPayload);
        $merchantNmid = $qrisService->getMerchantNmid($qrisPayload);
        $acquirer     = $qrisService->getAcquirer();

        // Simpan metode pembayaran ke database
        DB::table('orders')->whereIn('id', $orderIds)->update([
            'payment_method'      => 'QRIS',
            'updated_at'          => now(),
        ]);

        return view('customer.checkout.qris', [
            'id'              => $id,
            'total'           => $total,
            'subtotal'        => $subtotal,
            'kodeUnik'        => $totalKodeUnik,
            'orders'          => $orders,
            'orderReference'  => $orderReference,
            'midtransOrderId' => $orderReference,
            'channelCode'     => $channelCode,
            'bankName'        => 'QRIS',
            'bankLogo'        => 'QRIS',
            'bankColor'       => '#e11d48',
            'kodePembayaran'  => $orderReference,
            'qrisPayload'     => $qrisPayload,
            'qrisImageUrl'    => $qrisImageUrl,
            'merchantName'    => $merchantName,
            'merchantNmid'    => $merchantNmid,
            'acquirer'        => $acquirer,
            'snapToken'       => null,
            'snapError'       => null,
            'gopayDeeplink'   => null,
        ]);
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
                $part = trim($part);
                if (str_starts_with($part, 'INV-')) {
                    $orderIds[] = (int) str_replace('INV-', '', $part);
                } elseif (is_numeric($part)) {
                    $orderIds[] = (int) $part;
                }
            }

            $orders = DB::table('orders')->whereIn('id', $orderIds)->get();

            if ($orders->isNotEmpty()) {
                // Cegah IDOR: Pastikan pemesan adalah pengguna yang sedang login atau Admin
                $userId = auth()->id();
                $isAdmin = auth()->user()?->role === 'admin';
                $isAuthorized = $orders->every(fn($o) => $o->user_id == $userId) || $isAdmin;
                if (!$isAuthorized) {
                    abort(403, 'Anda tidak memiliki hak akses untuk melihat struk pesanan ini.');
                }

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