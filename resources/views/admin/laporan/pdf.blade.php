<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Pesanan #INV-{{ $order->id }} - Rentify</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; }
            .print-shadow-none { box-shadow: none !important; border: 1px solid #e2e8f0 !important; }
        }
    </style>
</head>
<body class="bg-slate-100 min-h-screen py-8 px-4 flex flex-col items-center">

    <div class="max-w-2xl w-full mb-4 flex justify-between items-center no-print">
        <a href="{{ route('admin.laporan') }}" class="text-xs font-bold text-slate-600 hover:text-slate-900 bg-white px-3 py-2 rounded-xl shadow-sm border border-slate-200 flex items-center space-x-1.5 transition">
            <i class="fas fa-arrow-left"></i> <span>Kembali ke Laporan</span>
        </a>
        <button onclick="window.print()" class="bg-[#0369a1] hover:bg-[#0284c7] text-white font-bold text-xs px-4 py-2 rounded-xl shadow-md transition flex items-center space-x-2">
            <i class="fas fa-print"></i> <span>Cetak Dokumen</span>
        </button>
    </div>

    <div class="max-w-2xl w-full bg-white rounded-2xl shadow-lg border border-slate-200 p-8 print-shadow-none">
        
        <!-- HEADER -->
        <div class="flex justify-between items-start border-b border-slate-100 pb-6 mb-6">
            <div class="flex items-center space-x-3">
                <div class="w-12 h-12 bg-sky-50 border border-sky-100 rounded-xl flex items-center justify-center text-[#0369a1] text-2xl font-black">
                    R
                </div>
                <div>
                    <h1 class="text-xl font-black text-slate-800 tracking-tight">RENTIFY</h1>
                    <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Official Rental Marketplace</p>
                </div>
            </div>
            <div class="text-right">
                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">INVOICE PESANAN</p>
                <p class="text-lg font-black text-[#0369a1]">#INV-{{ $order->id }}</p>
                <p class="text-[11px] text-slate-500 mt-0.5">
                    {{ $order->created_at ? \Carbon\Carbon::parse($order->created_at)->format('d F Y, H:i') : '-' }} WIB
                </p>
            </div>
        </div>

        <!-- INFO GRID -->
        <div class="grid grid-cols-2 gap-6 mb-6 bg-slate-50 p-4 rounded-xl border border-slate-100 text-xs">
            <div>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Informasi Penyewa</p>
                <p class="font-bold text-slate-800 text-sm">{{ $order->customer_name ?: $order->customer_account_name ?: 'Customer' }}</p>
                <p class="text-slate-500 mt-0.5"><i class="fab fa-whatsapp text-emerald-600 mr-1"></i>{{ $order->customer_whatsapp ?: '-' }}</p>
                <p class="text-slate-500"><i class="far fa-envelope text-slate-400 mr-1"></i>{{ $order->customer_email ?: '-' }}</p>
            </div>
            <div>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Mitra Toko & Pengiriman</p>
                <p class="font-bold text-slate-800 text-sm">{{ $order->vendor_name ?: 'Rentify Official' }}</p>
                <p class="text-slate-500 mt-0.5">Metode: <span class="font-semibold text-slate-700">{{ strtoupper($order->shipping_method ?: 'REGULER') }}</span></p>
                <p class="text-slate-500 truncate" title="{{ $order->alamat_pengiriman ?: $order->shipping_address }}">
                    Alamat: {{ $order->alamat_pengiriman ?: $order->shipping_address ?: 'Ambil Sendiri di Toko' }}
                </p>
            </div>
        </div>

        <!-- ITEMS TABLE -->
        <div class="mb-6">
            <p class="text-[11px] font-extrabold text-slate-400 uppercase tracking-widest mb-2">Daftar Barang Sewa</p>
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-slate-200 text-slate-400 uppercase font-bold text-[10px] pb-2">
                        <th class="py-2">Item Barang</th>
                        <th class="py-2 text-center">Durasi</th>
                        <th class="py-2 text-center">Qty</th>
                        <th class="py-2 text-right">Harga Sewa</th>
                        <th class="py-2 text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($items as $item)
                    <tr>
                        <td class="py-3 font-bold text-slate-800">{{ $item->barang_nama ?? 'Item Sewaan' }}</td>
                        <td class="py-3 text-center text-slate-600 font-medium">{{ $order->duration_days ?? 1 }} Hari</td>
                        <td class="py-3 text-center text-slate-600 font-bold">{{ $item->quantity ?? 1 }}</td>
                        <td class="py-3 text-right text-slate-600">Rp {{ number_format($item->price ?? 0, 0, ',', '.') }}</td>
                        <td class="py-3 text-right font-bold text-slate-800">Rp {{ number_format(($item->price ?? 0) * ($item->quantity ?? 1), 0, ',', '.') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-4 text-center text-slate-400">Rincian item tercatat dalam database pesanan.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- TOTALS & FEE CALCULATION -->
        @php
            $ongkir = (float) ($order->shipping_fee ?? 0);
            $totalTagihan = (float) ($order->total_price ?? 0);
            $diskon = (float) ($order->potongan_voucher ?? 0);
            $sewaBersih = $totalTagihan - $ongkir + $diskon;
            $sewaAsli = $sewaBersih / 1.05;
            $feePlatform = ($order->rentify_fee && $order->rentify_fee > 0) ? $order->rentify_fee : ($sewaBersih - $sewaAsli);
        @endphp
        <div class="border-t border-slate-200 pt-4 space-y-2 text-xs">
            <div class="flex justify-between text-slate-600">
                <span>Ongkos Kirim:</span>
                <span class="font-bold text-slate-800">Rp {{ number_format($ongkir, 0, ',', '.') }}</span>
            </div>
            @if($diskon > 0)
            <div class="flex justify-between text-rose-600">
                <span>Potongan Voucher Promo:</span>
                <span class="font-bold">- Rp {{ number_format($diskon, 0, ',', '.') }}</span>
            </div>
            @endif
            <div class="flex justify-between text-slate-800 text-sm font-black pt-2 border-t border-slate-200">
                <span>Total Biaya Pembayaran:</span>
                <span class="text-[#0369a1]">Rp {{ number_format($totalTagihan, 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between bg-emerald-50 text-emerald-800 p-2.5 rounded-lg text-xs font-bold border border-emerald-100 mt-2">
                <span>Hak Komisi Platform Rentify (5%):</span>
                <span>Rp {{ number_format($feePlatform, 0, ',', '.') }}</span>
            </div>
        </div>

        <!-- FOOTER & STATUS -->
        <div class="mt-8 pt-6 border-t border-slate-100 flex justify-between items-center text-xs">
            <div>
                <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Status Transaksi:</p>
                <p class="font-black uppercase tracking-wider mt-0.5 {{ strtolower($order->status ?? '') === 'selesai' ? 'text-emerald-600' : 'text-sky-600' }}">
                    {{ $order->status ?? 'Pending' }}
                </p>
            </div>
            <div class="text-right text-[10px] text-slate-400">
                <p>Dokumen ini diterbitkan secara otomatis oleh sistem</p>
                <p class="font-bold">Rentify Technology Platform</p>
            </div>
        </div>

    </div>

</body>
</html>
