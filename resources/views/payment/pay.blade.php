<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran Pesanan {{ $order->kode_booking }}</title>
    <!-- Midtrans Snap JS Dynamic -->
    @php
        $snapJsUrl = config('midtrans.is_production') 
            ? 'https://app.midtrans.com/snap/snap.js' 
            : 'https://app.sandbox.midtrans.com/snap/snap.js';
    @endphp
    <script src="{{ $snapJsUrl }}" data-client-key="{{ config('midtrans.client_key') }}"></script>
    <!-- Tailwind CSS (Optional, asumsi proyek ini pakai Tailwind/Vite) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex items-center justify-center min-h-screen bg-slate-50 font-sans p-4">
    
    <div class="rentify-card p-8 max-w-md w-full text-center bg-white rounded-3xl shadow-sm border border-slate-200">
        <h2 class="text-xl font-black text-slate-800 mb-4">Selesaikan Pembayaran</h2>
        
        <div class="mb-6 text-left border-b border-slate-100 pb-4 text-sm space-y-1.5">
            <p class="text-slate-500">Kode Booking: <span class="font-bold text-slate-800">{{ $order->kode_booking }}</span></p>
            <p class="text-slate-500">Nama: <span class="font-bold text-slate-800">{{ $order->customer_name }}</span></p>
            <p class="text-slate-500">Total Biaya: <span class="font-black text-sky-600 text-xl">Rp {{ number_format($order->total_biaya, 0, ',', '.') }}</span></p>
        </div>

        <button id="pay-button" class="w-full bg-gradient-to-r from-sky-400 to-sky-600 hover:from-sky-500 hover:to-sky-700 text-white font-bold py-3.5 px-4 rounded-xl shadow-md transition duration-200">
            Bayar Sekarang
        </button>

        <a href="{{ route('customer.pesanan') }}" class="block mt-4 text-xs font-semibold text-slate-400 hover:text-slate-600">
            Kembali ke Daftar Pesanan
        </a>
    </div>

    <script type="text/javascript">
      document.addEventListener("DOMContentLoaded", function() {
          const btn = document.getElementById('pay-button');
          function triggerSnap() {
            snap.pay('{{ $snapToken }}', {
              onSuccess: function(result){
                alert("Pembayaran berhasil!");
                window.location.href = "{{ route('customer.struk', 'INV-' . $order->id) }}";
              },
              onPending: function(result){
                alert("Menunggu pembayaran Anda diselesaikan!");
                window.location.href = "{{ route('customer.pesanan') }}";
              },
              onError: function(result){
                alert("Pembayaran gagal!");
              },
              onClose: function(){
                console.log('User closed popup');
              }
            });
          }

          btn.onclick = triggerSnap;
          setTimeout(triggerSnap, 600);
      });
    </script>
</body>
</html>
