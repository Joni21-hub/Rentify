<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout Rentify - Shopee Style</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: { extend: { colors: { navy: '#0D1B3E', brandBlue: '#1E4DAA', sky: '#5C9EE8', ice: '#C8DFF8', bgSoft: '#F4F8FF', accent: '#1FBF8F' } } }
        }
    </script>
</head>
<body class="bg-bgSoft font-sans text-slate-800">

    <nav class="bg-white border-b border-ice sticky top-0 z-50 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 h-20 flex justify-between items-center">
            <a href="/customer" class="flex items-center space-x-2">
                <div class="w-10 h-10 bg-brandBlue rounded-lg flex items-center justify-center font-bold text-white text-xl">R</div>
                <span class="text-2xl font-bold text-navy tracking-wider">RENTIFY</span>
            </a>
            <a href="/customer" class="text-sm font-semibold text-brandBlue hover:underline"><i class="fas fa-arrow-left mr-1"></i> Kembali</a>
        </div>
    </nav>

    <div class="max-w-6xl mx-auto px-4 py-10">
        <h2 class="text-2xl font-bold text-navy mb-6"><i class="fas fa-shopping-basket text-brandBlue mr-2"></i> Konfirmasi Penyewaan</h2>

        @if(count($cart) > 0)
        <form action="/customer/cart/checkout" method="POST" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            @csrf
            
            <div class="lg:col-span-2 space-y-6">
                
                <div class="bg-white p-6 rounded-2xl border border-ice shadow-sm space-y-4">
                    <h3 class="font-bold text-navy text-lg border-b pb-2"><i class="fas fa-user-edit text-brandBlue mr-2"></i> Data Lengkap Pemesan</h3>
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Nama Lengkap</label>
                        <input type="text" name="customer_name" required class="w-full border border-ice rounded-xl p-3 text-sm focus:outline-brandBlue" placeholder="Contoh: Muhammad Rafli">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Nomor WhatsApp Aktif</label>
                        <input type="text" name="customer_whatsapp" required class="w-full border border-ice rounded-xl p-3 text-sm focus:outline-brandBlue" placeholder="Contoh: 0831xxxxxxx">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Alamat Pengiriman Detail</label>
                        <textarea name="shipping_address" required rows="3" class="w-full border border-ice rounded-xl p-3 text-sm focus:outline-brandBlue" placeholder="Nama Jalan, nomor rumah, RT/RW atau keterangan patokan lokasi"></textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Titik Lokasi (Link Share-Loc Google Maps)</label>
                        <input type="text" name="pin_location" class="w-full border border-ice rounded-xl p-3 text-sm focus:outline-brandBlue" placeholder="Tempel link pin maps disini">
                    </div>
                </div>

                <div class="bg-white p-6 rounded-2xl border border-ice shadow-sm space-y-4">
                    <h3 class="font-bold text-navy text-lg border-b pb-2"><i class="fas fa-clock text-brandBlue mr-2"></i> Atur Durasi Waktu Rental (Sistem Flat 24 Jam)</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Tanggal & Jam Mulai</label>
                            <input type="datetime-local" id="start_rent" name="start_rent" required class="w-full border border-ice rounded-xl p-3 text-sm focus:outline-brandBlue">
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Tanggal & Jam Pengembalian</label>
                            <input type="datetime-local" id="end_rent" name="end_rent" required class="w-full border border-ice rounded-xl p-3 text-sm focus:outline-brandBlue">
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div class="bg-white p-6 rounded-2xl border border-ice shadow-sm">
                        <h4 class="font-bold text-navy mb-3"><i class="fas fa-truck text-brandBlue mr-1"></i> Opsi Pengiriman</h4>
                        <label class="flex items-center space-x-3 p-3 border rounded-xl mb-2 cursor-pointer hover:bg-slate-50">
                            <input type="radio" name="shipping_method" value="ambil" checked onchange="updateOngkir(0)" class="text-brandBlue">
                            <span class="text-sm">Ambil Sendiri (Gratis Rp 0)</span>
                        </label>
                        <label class="flex items-center space-x-3 p-3 border rounded-xl cursor-pointer hover:bg-slate-50">
                            <input type="radio" name="shipping_method" value="antar" onchange="updateOngkir(5000)" class="text-brandBlue">
                            <span class="text-sm">Diantar Kurir (Flat Rp 5.000)</span>
                        </label>
                    </div>

                    <div class="bg-white p-6 rounded-2xl border border-ice shadow-sm">
                        <h4 class="font-bold text-navy mb-3"><i class="fas fa-wallet text-brandBlue mr-1"></i> Metode Pembayaran</h4>
                        <label class="flex items-center space-x-3 p-3 border rounded-xl mb-2 cursor-pointer">
                            <input type="radio" name="payment_method" value="COD" checked onclick="toggleQris(false)" class="text-brandBlue">
                            <span class="text-sm">COD (Bayar di Tempat)</span>
                        </label>
                        <label class="flex items-center space-x-3 p-3 border rounded-xl cursor-pointer">
                            <input type="radio" name="payment_method" value="QRIS" onclick="toggleQris(true)" class="text-brandBlue">
                            <span class="text-sm">QRIS (Scan Barcode E-Wallet)</span>
                        </label>
                        
                        <div id="qris-box" class="hidden mt-4 p-3 bg-slate-50 border rounded-xl text-center">
                            <p class="text-xs text-slate-500 mb-2 font-semibold">Silakan scan kode QRIS Rentify berikut:</p>
                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=RentifyDummyQRIS" alt="QRIS Rentify" class="mx-auto w-36 h-36 border p-2 bg-white">
                        </div>
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <div class="bg-white p-6 rounded-2xl border border-ice shadow-sm space-y-4 sticky top-24">
                    <h3 class="font-bold text-navy text-lg border-b pb-2">Keranjang Item</h3>
                    
                    <div class="max-h-48 overflow-y-auto space-y-3 pr-1">
                        @foreach($cart as $id => $item)
                        <div class="flex justify-between items-center text-sm border-b pb-2">
                            <div>
                                <p class="font-semibold text-navy">{{ $item['name'] }}</p>
                                <p class="text-xs text-slate-400">{{ $item['quantity'] }}x @ Rp {{ number_format($item['price'], 0, ',', '.') }}</p>
                            </div>
                            <span class="font-bold text-slate-700">Rp {{ number_format($item['price'] * $item['quantity'], 0, ',', '.') }}</span>
                        </div>
                        @endforeach
                    </div>

                    <div class="space-y-2 text-sm pt-2">
                        <div class="flex justify-between text-slate-500">
                            <span>Subtotal Rate Pokok</span>
                            <span id="subtotal" data-value="{{ $total }}">Rp {{ number_format($total, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between text-slate-500">
                            <span>Ongkos Kirim</span>
                            <span id="ongkir-display">Rp 0</span>
                        </div>
                        <div class="border-t pt-3 flex justify-between font-black text-lg text-navy">
                            <span>Total Tagihan</span>
                            <span id="grand-total" class="text-accent">Rp {{ number_format($total, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <button type="submit" class="w-full bg-brandBlue hover:bg-navy text-white font-bold py-3 px-4 rounded-xl transition shadow-lg text-center text-sm flex justify-center items-center">
                        <i class="fas fa-shopping-bag mr-2"></i> Selesaikan Pemesanan
                    </button>
                </div>
            </div>
        </form>
        @else
        <div class="bg-white p-12 rounded-2xl border text-center shadow-sm">
            <i class="fas fa-shopping-cart text-5xl text-slate-300 mb-4"></i>
            <p class="text-slate-500 font-medium">Keranjang belanjamu masih kosong nih.</p>
            <a href="/customer" class="mt-4 inline-block bg-brandBlue text-white font-bold py-2 px-6 rounded-xl text-sm hover:bg-navy transition">Mulai Belanja</a>
        </div>
        @endif
    </div>

    <script>
        let baseTotal = parseInt(document.getElementById('subtotal')?.getAttribute('data-value') || 0);
        let currentOngkir = 0;

        function updateOngkir(amount) {
            currentOngkir = amount;
            document.getElementById('ongkir-display').innerText = "Rp " + amount.toLocaleString('id-ID');
            calculateTotal();
        }

        function calculateTotal() {
            let finalTotal = baseTotal + currentOngkir;
            document.getElementById('grand-total').innerText = "Rp " + finalTotal.toLocaleString('id-ID');
        }

        function toggleQris(show) {
            document.getElementById('qris-box').classList.toggle('hidden', !show);
        }
    </script>
</body>
</html>