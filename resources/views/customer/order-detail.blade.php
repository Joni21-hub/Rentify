<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice Pesanan #{{ $order->id }} - Rentify</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-slate-100 font-sans text-slate-800 p-4 md:p-10">

    <div class="max-w-3xl mx-auto bg-white rounded-3xl shadow-xl overflow-hidden border border-slate-200" id="printable-struk">
        
        <div class="bg-gradient-to-r from-blue-900 to-indigo-900 text-white p-8 text-center relative">
            <h1 class="text-3xl font-black tracking-widest">RENTIFY INVOICE</h1>
            <p class="text-xs opacity-70 mt-1">Blang Pulo, Pintu II PT. Arun | Telp: 083183494835</p>
            <div class="absolute top-4 right-4 bg-emerald-500 text-white font-bold text-xs px-3 py-1 rounded-full uppercase">
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
                <p class="text-slate-600">Tanggal Order: <strong>{{ date('d M Y', strtotime($order->created_at)) }}</strong></p>
                <p class="text-blue-700">Mulai: <strong>{{ date('d M Y H:i', strtotime($order->start_rent)) }}</strong></p>
                <p class="text-red-600">Kembali: <strong>{{ date('d M Y H:i', strtotime($order->end_rent)) }}</strong></p>
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
                    <span class="text-xl text-blue-800">Rp {{ number_format($order->total_price, 0, ',', '.') }}</span>
                </div>
            </div>
            
            <div class="mt-8 grid grid-cols-1 sm:grid-cols-3 gap-3 print:hidden">
                <button onclick="window.print()" class="bg-emerald-600 hover:bg-emerald-700 text-white text-center py-3 rounded-xl font-bold text-xs transition shadow-md">
                    <i class="fas fa-download mr-1"></i> Download Struk
                </button>
                <a href="https://wa.me/6283183494835" target="_blank" class="bg-blue-600 hover:bg-blue-700 text-white text-center py-3 rounded-xl font-bold text-xs transition shadow-md flex justify-center items-center">
                    <i class="fab fa-whatsapp mr-1 text-sm"></i> Hubungi Vendor
                </a>
                @if($order->status == 'Menunggu Konfirmasi')
                <a href="/customer/order/{{ $order->id }}/cancel" class="bg-red-50 hover:bg-red-100 text-red-600 text-center py-3 rounded-xl font-bold text-xs transition border border-red-200">
                    Batalkan Penyewaan
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