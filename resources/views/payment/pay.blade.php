<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran Pesanan {{ $order->kode_booking }}</title>
    <!-- Midtrans Snap JS (Sandbox) -->
    <script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ config('midtrans.client_key') }}"></script>
    <!-- Tailwind CSS (Optional, asumsi proyek ini pakai Tailwind/Vite) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 flex items-center justify-center min-h-screen">
    
    <div class="bg-white p-8 rounded-lg shadow-md max-w-md w-full text-center">
        <h2 class="text-2xl font-bold mb-4">Selesaikan Pembayaran</h2>
        
        <div class="mb-6 text-left border-b pb-4">
            <p class="text-gray-600">Kode Booking: <span class="font-bold text-gray-800">{{ $order->kode_booking }}</span></p>
            <p class="text-gray-600">Nama: <span class="font-bold text-gray-800">{{ $order->customer_name }}</span></p>
            <p class="text-gray-600">Total Biaya: <span class="font-bold text-gray-800 text-lg">Rp {{ number_format($order->total_biaya, 0, ',', '.') }}</span></p>
        </div>

        <button id="pay-button" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded transition duration-200">
            Pilih Metode Pembayaran
        </button>

        <a href="{{ route('customer.dashboard') }}" class="block mt-4 text-sm text-gray-500 hover:text-gray-800">
            Kembali ke Dashboard
        </a>
    </div>

    <script type="text/javascript">
      // Tombol bayar diklik
      document.getElementById('pay-button').onclick = function(){
        // SnapToken dari controller di-trigger di sini
        snap.pay('{{ $snapToken }}', {
          // Callback sukses
          onSuccess: function(result){
            alert("Pembayaran berhasil!");
            console.log(result);
            window.location.href = "{{ route('customer.dashboard') }}";
          },
          // Callback pending
          onPending: function(result){
            alert("Menunggu pembayaran Anda!");
            console.log(result);
            window.location.href = "{{ route('customer.dashboard') }}";
          },
          // Callback error
          onError: function(result){
            alert("Pembayaran gagal!");
            console.log(result);
          },
          // User menutup popup
          onClose: function(){
            alert('Anda menutup pop-up sebelum menyelesaikan pembayaran');
          }
        });
      };
    </script>
</body>
</html>
