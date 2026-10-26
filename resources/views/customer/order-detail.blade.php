@php
    /** @var object $order */
    /** @var iterable $items */
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Invoice Pesanan #{{ $order->id }} - Rentify</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="{{ asset('rentify-theme.css') }}">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            background: radial-gradient(circle at center, #fff9ef 0%, #bad6eb 100%) fixed !important;
        }
    </style>
</head>
<body class="text-slate-800 p-4 md:p-10 bg-flowing">

    <div class="max-w-3xl mx-auto rentify-card rounded-3xl overflow-hidden border border-white/80 shadow-xl" id="printable-struk">
        
        <div class="bg-gradient-to-br from-sky-400 via-sky-500 to-sky-600 text-white p-8 text-center relative shadow-md">
            <h1 class="text-2xl sm:text-3xl font-black tracking-widest uppercase">Rentify Invoice</h1>
            <p class="text-xs text-sky-100 font-medium mt-1">Blang Pulo, Pintu II PT. Arun | Telp: 083183494835</p>
            <div class="mt-3 sm:mt-0 sm:absolute sm:top-5 sm:right-5 inline-block bg-white/20 backdrop-blur-md border border-white/40 text-white font-black text-[11px] px-3.5 py-1 rounded-full uppercase tracking-wider shadow-xs">
                {{ $order->status }}
            </div>
        </div>

        <div class="p-6 md:p-8 grid grid-cols-1 md:grid-cols-2 gap-6 border-b border-dashed">
            <div class="space-y-1.5 text-sm">
                <p class="text-xs font-bold uppercase text-slate-400">Data Penyewa:</p>
                <p class="font-bold text-lg text-slate-900">{{ $order->customer_name }}</p>
                <p><i class="fab fa-whatsapp text-emerald-500"></i> {{ $order->customer_whatsapp }}</p>
                <p class="text-slate-500 leading-tight"><i class="fas fa-map-marker-alt text-red-500"></i> {{ $order->shipping_address }}</p>
            </div>
            
            <div class="space-y-1.5 text-sm md:text-right">
                <p class="text-xs font-bold uppercase text-slate-400">Rincian Waktu Rental:</p>
                <p class="text-slate-600">Tanggal Order: <strong>{{ date('d M Y', strtotime((string)$order->created_at) ?: time()) }}</strong></p>
                <p class="text-sky-700">Mulai: <strong>{{ date('d M Y H:i', strtotime((string)$order->start_rent) ?: time()) }}</strong></p>
                <p class="text-red-600">Kembali: <strong>{{ date('d M Y H:i', strtotime((string)$order->end_rent) ?: time()) }}</strong></p>
                <p class="text-xs font-bold bg-slate-100 text-slate-700 inline-block px-2.5 py-1 rounded-md mt-1">Durasi total: {{ $order->duration_days }} Hari</p>
            </div>
        </div>

        <div class="p-6 md:p-8">
            <h3 class="text-xs font-bold uppercase text-slate-400 mb-3">Item Perlengkapan Yang Disewa:</h3>
            <div class="space-y-3">
                @foreach($items as $item)
                <div class="flex justify-between items-center border-b pb-3 text-sm">
                    <div>
                        <p class="font-bold text-slate-800">{{ $item->product_name }}</p>
                        <p class="text-xs text-slate-400">{{ $item->quantity }} unit x Rp {{ number_format($item->price, 0, ',', '.') }} / hari</p>
                    </div>
                    <span class="font-bold text-slate-900">
                        Rp {{ number_format(($item->price * $item->quantity) * $order->duration_days, 0, ',', '.') }}
                    </span>
                </div>
                @endforeach
            </div>

            <div class="mt-6 bg-slate-50 p-4 rounded-xl space-y-2 text-sm">
                <div class="flex justify-between text-slate-500">
                    <span>Metode Pengiriman: <strong class="text-slate-700 uppercase">{{ $order->shipping_method }}</strong></span>
                    <span>Rp {{ number_format($order->shipping_fee, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between text-slate-500">
                    <span>Metode Pembayaran:</span>
                    <span class="font-bold text-slate-700">{{ $order->payment_method }}</span>
                </div>
                <div class="flex justify-between text-base font-black text-slate-900 pt-2 border-t">
                    <span>Total Pembayaran (Net):</span>
                    <span class="text-xl text-sky-800">Rp {{ number_format($order->total_price, 0, ',', '.') }}</span>
                </div>
            </div>
            
            <div class="mt-8 grid grid-cols-1 sm:grid-cols-3 gap-3 print:hidden">
                <button onclick="window.print()" class="bg-gradient-to-r from-sky-400 to-sky-600 hover:from-sky-500 hover:to-sky-700 text-white text-center py-3.5 px-4 rounded-2xl font-black text-xs uppercase tracking-wider transition shadow-md shadow-sky-500/20 transform hover:-translate-y-0.5 active:translate-y-0 flex items-center justify-center gap-1.5">
                    <i class="fas fa-download"></i> <span>Download Struk</span>
                </button>
                <a href="https://wa.me/6283183494835" target="_blank" class="bg-emerald-600 hover:bg-emerald-700 text-white text-center py-3.5 px-4 rounded-2xl font-black text-xs uppercase tracking-wider transition shadow-md shadow-emerald-500/20 flex justify-center items-center gap-1.5 transform hover:-translate-y-0.5 active:translate-y-0">
                    <i class="fab fa-whatsapp text-sm"></i> <span>Hubungi Vendor</span>
                </a>
                @if($order->status == 'Menunggu Konfirmasi')
                <a href="/customer/order/{{ $order->id }}/cancel" class="bg-rose-50 hover:bg-rose-100 text-rose-600 text-center py-3.5 px-4 rounded-2xl font-black text-xs uppercase tracking-wider transition border border-rose-200/80 shadow-xs flex items-center justify-center gap-1.5 transform hover:-translate-y-0.5 active:translate-y-0">
                    <span>Batalkan Penyewaan</span>
                </a>
                @endif
            </div>
        </div>
    </div>

    @if(session('success_checkout'))
    <script>
        window.addEventListener('load', () => {
            setTimeout(() => {
                window.print(); // Otomatis trigger dialog save as PDF / cetak struk di device HP/Laptop
            }, 1000);
        });
    </script>
    @endif

</body>
</html>