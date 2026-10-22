@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
{{-- Library pembuat QR code lokal instan tanpa latency --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
    
    /* Sembunyikan navigasi global agar fokus ke pembayaran */
    nav, header, footer, .navbar, .header, .top-bar, .footer, #footer { display: none !important; }
    
    body {
        background-color: #f8fafc !important;
        font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
        color: #0f172a;
        margin: 0;
        padding: 0;
        min-height: 100vh;
    }
    
    .qris-wrapper {
        max-width: 440px;
        margin: 0 auto;
        padding: 16px 14px 40px 14px;
    }
    
    .clean-card {
        background: #ffffff;
        border-radius: 16px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
    }
    
    #qrcode-canvas-container img, 
    #qrcode-canvas-container canvas {
        margin: 0 auto;
        border-radius: 8px;
        display: block;
    }
</style>

<div class="qris-wrapper">

    <!-- 1. HEADER NAVIGASI -->
    <div class="clean-card p-3.5 mb-3 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ route('customer.pesanan') }}" class="w-9 h-9 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 flex items-center justify-center transition active:scale-95" title="Kembali ke Pesanan Saya">
                <i class="fa-solid fa-arrow-left text-sm"></i>
            </a>
            <div>
                <h1 class="text-[15px] font-bold text-slate-900 leading-tight">Pembayaran QRIS</h1>
                <span class="text-xs text-slate-500 font-medium">ID Pesanan: <strong class="font-mono text-slate-700 font-semibold">{{ $id }}</strong></span>
            </div>
        </div>
    </div>

    <!-- 2. KARTU TOTAL PEMBAYARAN & COUNTDOWN -->
    <div class="clean-card p-4 mb-3">
        <div class="flex items-center justify-between">
            <div>
                <span class="text-xs font-medium text-slate-500 block mb-0.5">Total Pembayaran</span>
                <span class="text-2xl font-black text-slate-900 tracking-tight">Rp {{ number_format($total ?? 0, 0, ',', '.') }}</span>
            </div>
            @if(!empty($kodeUnik) && $kodeUnik > 0)
            <div class="text-right">
                <span class="text-[11px] font-semibold text-emerald-700 bg-emerald-50 border border-emerald-200 px-2.5 py-1 rounded-md inline-block" title="Kode unik otomatis untuk membedakan transaksi Anda">
                    Kode Unik: +{{ $kodeUnik }}
                </span>
            </div>
            @endif
        </div>

        @if(!empty($kodeUnik) && $kodeUnik > 0)
        <div class="mt-2 pt-2 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
            <span>Subtotal Pesanan:</span>
            <span class="font-medium text-slate-700">Rp {{ number_format($subtotal ?? ($total - $kodeUnik), 0, ',', '.') }}</span>
        </div>
        @endif

        <div class="flex items-center justify-between text-xs pt-3 mt-3 border-t border-slate-100">
            <span class="text-slate-500 flex items-center gap-1.5">
                <i class="fa-regular fa-clock text-slate-400"></i> Batas waktu pembayaran
            </span>
            <span id="countdown-timer" class="font-mono font-bold text-amber-700 bg-amber-50 px-2 py-0.5 rounded border border-amber-200/70 text-xs">
                23:59:59
            </span>
        </div>
    </div>

    <!-- 3. KARTU QRIS UTAMA -->
    <div class="clean-card p-5 mb-3 text-center">
        <!-- Header QRIS -->
        <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
            <div class="flex items-center gap-2">
                <span class="bg-[#d32f2f] text-white text-[11px] font-black px-2 py-0.5 rounded tracking-wider">QRIS</span>
                <span class="text-xs font-semibold text-slate-700">Standar Pembayaran Nasional</span>
            </div>
            <span class="text-xs font-medium text-slate-500">{{ $merchantName ?? 'Rental Kursi' }}</span>
        </div>

        <!-- Container Kode QR -->
        <div class="p-4 bg-white border border-slate-200 rounded-2xl inline-block shadow-2xs">
            <div id="qrcode-canvas-container" class="w-56 h-56 mx-auto flex items-center justify-center">
                <img src="{{ $qrisImageUrl }}" alt="QRIS" class="w-56 h-56 object-contain mx-auto" id="qris-img-fallback" />
            </div>
        </div>

        <p class="text-xs text-slate-600 font-medium mt-3">
            Scan kode QR dengan aplikasi <strong>m-Banking</strong> atau <strong>e-Wallet</strong> apa saja
        </p>

        <!-- Tombol Simpan QR -->
        <div class="mt-3">
            <button type="button" onclick="downloadQrCode()" class="inline-flex items-center gap-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold py-2 px-4 rounded-xl transition active:scale-95">
                <i class="fa-solid fa-download text-xs text-slate-500"></i>
                <span>Simpan Gambar QR</span>
            </button>
        </div>

        <!-- Status Realtime Indicator -->
        <div id="status-indicator" class="mt-4 pt-3.5 border-t border-slate-100 flex items-center justify-center gap-2 text-xs text-slate-500">
            <span class="relative flex h-2.5 w-2.5">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-sky-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-sky-500"></span>
            </span>
            <span>Menunggu pembayaran... Halaman otomatis dialihkan ke struk setelah pembayaran terdeteksi.</span>
        </div>
    </div>

    <!-- 4. PETUNJUK PEMBAYARAN -->
    <div class="clean-card p-4 mb-3">
        <h2 class="text-xs font-bold text-slate-800 uppercase tracking-wider mb-3 flex items-center gap-2">
            <i class="fa-solid fa-circle-question text-sky-600"></i>
            <span>Petunjuk Pembayaran QRIS</span>
        </h2>

        <div class="space-y-3 text-xs text-slate-600">
            <div class="flex items-start gap-3">
                <span class="w-5 h-5 rounded-full bg-sky-50 text-sky-600 font-bold flex items-center justify-center shrink-0 text-[11px] border border-sky-100">1</span>
                <span>Buka aplikasi <strong>m-Banking</strong> atau <strong>e-Wallet</strong> yang mendukung QRIS (BCA, Mandiri, BRI, BNI, DANA, GoPay, OVO, ShopeePay, dll).</span>
            </div>
            <div class="flex items-start gap-3">
                <span class="w-5 h-5 rounded-full bg-sky-50 text-sky-600 font-bold flex items-center justify-center shrink-0 text-[11px] border border-sky-100">2</span>
                <span>Pilih menu <strong>Scan / Bayar</strong> di aplikasi Anda.</span>
            </div>
            <div class="flex items-start gap-3">
                <span class="w-5 h-5 rounded-full bg-sky-50 text-sky-600 font-bold flex items-center justify-center shrink-0 text-[11px] border border-sky-100">3</span>
                <span>Arahkan kamera ke kode QR di atas (atau pilih foto QR dari galeri jika disimpan).</span>
            </div>
            <div class="flex items-start gap-3">
                <span class="w-5 h-5 rounded-full bg-sky-50 text-sky-600 font-bold flex items-center justify-center shrink-0 text-[11px] border border-sky-100">4</span>
                <span>Periksa nominal pembayaran (<strong>Rp {{ number_format($total ?? 0, 0, ',', '.') }}</strong>) dan nama merchant.</span>
            </div>
            <div class="flex items-start gap-3">
                <span class="w-5 h-5 rounded-full bg-sky-50 text-sky-600 font-bold flex items-center justify-center shrink-0 text-[11px] border border-sky-100">5</span>
                <span>Konfirmasi pembayaran dan masukkan PIN Anda.</span>
            </div>
            <div class="flex items-start gap-3">
                <span class="w-5 h-5 rounded-full bg-emerald-50 text-emerald-600 font-bold flex items-center justify-center shrink-0 text-[11px] border border-emerald-100"><i class="fa-solid fa-check text-[10px]"></i></span>
                <span class="text-slate-700 font-medium">Setelah pembayaran selesai, sistem otomatis mendeteksi transaksi dan langsung beralih ke <strong>struk pesanan</strong>.</span>
            </div>
        </div>
    </div>

    @if(request('dev') || request('test'))
    <!-- Mode Pengembang / Testing (Hanya muncul jika parameter ?test=1 atau ?dev=1 ada di URL) -->
    <div class="p-3 bg-amber-50 border border-amber-200 rounded-xl mb-3 text-center">
        <span class="text-[11px] font-bold text-amber-800 block mb-1.5">Developer Testing</span>
        <button type="button" onclick="simulasikanBayar()" class="text-xs bg-amber-600 text-white font-bold py-1.5 px-3 rounded-lg hover:bg-amber-700">
            Simulasikan Pembayaran Berhasil
        </button>
    </div>
    @endif

    <!-- 5. LINK KEMBALI KE PESANAN -->
    <div class="text-center mt-4">
        <a href="{{ route('customer.pesanan') }}" class="text-xs text-slate-500 hover:text-slate-700 font-medium">
            ← Kembali ke Daftar Pesanan
        </a>
    </div>

</div>

<script>
    const invoiceId = "{{ $id }}";
    const qrisPayload = {!! json_encode($qrisPayload ?? '') !!};
    const qrisImageUrl = {!! json_encode($qrisImageUrl ?? '') !!};
    const checkStatusUrl = "{{ route('customer.pembayaran.check_status', $id) }}";
    const simulateUrl = "{{ route('customer.pembayaran.simulate', $id) }}";
    const receiptUrl = "{{ route('customer.struk', $id) }}";
    const csrfToken = "{{ csrf_token() }}";

    // 1. Render Kode QR Dinamis di Browser
    document.addEventListener("DOMContentLoaded", function() {
        if (qrisPayload && typeof QRCode !== 'undefined') {
            try {
                const container = document.getElementById('qrcode-canvas-container');
                if (container) {
                    container.innerHTML = '';
                    new QRCode(container, {
                        text: qrisPayload,
                        width: 224,
                        height: 224,
                        colorDark: "#000000",
                        colorLight: "#ffffff",
                        correctLevel: QRCode.CorrectLevel.M
                    });
                }
            } catch (e) {
                console.warn("QRCode local render notice, using image fallback:", e);
            }
        }
    });

    // 2. Download / Simpan Gambar QR
    function downloadQrCode() {
        const container = document.getElementById('qrcode-canvas-container');
        let dataUrl = '';
        
        const canvas = container ? container.querySelector('canvas') : null;
        const img = container ? container.querySelector('img') : null;

        if (canvas) {
            dataUrl = canvas.toDataURL('image/png');
        } else if (img && img.src) {
            dataUrl = img.src;
        } else if (qrisImageUrl) {
            dataUrl = qrisImageUrl;
        }

        if (dataUrl) {
            const link = document.createElement('a');
            link.download = 'QRIS-Rentify-' + invoiceId + '.png';
            link.href = dataUrl;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        } else {
            alert('Tidak dapat mengunduh QR code saat ini.');
        }
    }

    // 3. Countdown Batas Waktu
    let targetTime = new Date().getTime() + (24 * 60 * 60 * 1000);
    function updateCountdown() {
        let now = new Date().getTime();
        let distance = targetTime - now;
        if (distance < 0) {
            const el = document.getElementById("countdown-timer");
            if (el) el.innerText = "00:00:00";
            return;
        }
        let hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        let minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        let seconds = Math.floor((distance % (1000 * 60)) / 1000);
        const el = document.getElementById("countdown-timer");
        if (el) {
            el.innerText = String(hours).padStart(2, '0') + ":" + 
                           String(minutes).padStart(2, '0') + ":" + 
                           String(seconds).padStart(2, '0');
        }
    }
    setInterval(updateCountdown, 1000);
    updateCountdown();

    // 4. Polling Otomatis Status Pembayaran (Setiap 3 detik)
    let isChecking = false;
    let isRedirecting = false;

    function checkStatusLive() {
        if (isChecking || isRedirecting) return;
        isChecking = true;

        fetch(checkStatusUrl, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            if (data.paid && !isRedirecting) {
                isRedirecting = true;
                const statusEl = document.getElementById('status-indicator');
                if (statusEl) {
                    statusEl.innerHTML = '<span class="text-emerald-600 font-bold flex items-center gap-1.5"><i class="fa-solid fa-circle-check text-emerald-500"></i> Pembayaran berhasil! Mengalihkan ke struk...</span>';
                }
                setTimeout(function() {
                    window.location.href = data.redirect_url || receiptUrl;
                }, 800);
            }
        })
        .catch(function(e) {
            console.debug('Polling check:', e);
        })
        .finally(function() {
            isChecking = false;
        });
    }

    // Mulai polling otomatis
    setInterval(checkStatusLive, 3000);
    // Cek awal 1.5 detik setelah halaman terbuka
    setTimeout(checkStatusLive, 1500);

    // 5. Fungsi Simulasi Bayar Berhasil (Console / Dev)
    window.simulasikanBayar = function() {
        const statusEl = document.getElementById('status-indicator');
        if (statusEl) {
            statusEl.innerHTML = '<span class="text-sky-600 font-bold flex items-center gap-1.5"><i class="fa-solid fa-spinner fa-spin"></i> Menyimulasikan pembayaran...</span>';
        }

        fetch(simulateUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            if (data.success) {
                isRedirecting = true;
                if (statusEl) {
                    statusEl.innerHTML = '<span class="text-emerald-600 font-bold flex items-center gap-1.5"><i class="fa-solid fa-circle-check text-emerald-500"></i> Pembayaran berhasil! Mengalihkan ke struk...</span>';
                }
                setTimeout(function() {
                    window.location.href = data.redirect_url || receiptUrl;
                }, 800);
            } else {
                alert(data.message || 'Gagal simulasi.');
            }
        })
        .catch(function(err) {
            console.error(err);
            alert('Terjadi kesalahan saat simulasi.');
        });
    };
</script>

@endsection
