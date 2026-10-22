@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<!-- Library pembuat QR code lokal instan tanpa latency -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap');
    
    /* Sembunyikan navigasi global agar fokus ke pembayaran */
    nav, header, footer, .navbar, .header, .top-bar, .footer, #footer { display: none !important; }
    
    body {
        background: radial-gradient(circle at center, #fff9ef 0%, #bad6eb 100%) fixed !important;
        font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
        color: #0f172a;
        margin: 0;
        padding: 0;
        min-height: 100vh;
    }
    
    .rentify-pay-wrapper {
        max-width: 480px;
        margin: 0 auto;
        padding: 16px 14px 120px 14px;
    }
    
    .rentify-card {
        background: #ffffff;
        border-radius: 20px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 8px 24px rgba(2, 132, 199, 0.06);
    }
    
    .accordion-header {
        cursor: pointer;
        transition: background-color 0.15s;
    }
    .accordion-header:active {
        background-color: #f8fafc;
    }
    
    .step-badge {
        width: 22px;
        height: 22px;
        border-radius: 50%;
        background: #e0f2fe;
        color: #0284c7;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 11px;
        font-weight: 800;
        flex-shrink: 0;
    }

    .btn-rentify-gradient {
        background: linear-gradient(135deg, #38bdf8 0%, #0284c7 100%);
        color: #ffffff;
        transition: all 0.2s ease;
    }
    .btn-rentify-gradient:hover {
        background: linear-gradient(135deg, #0ea5e9 0%, #0369a1 100%);
        box-shadow: 0 8px 20px rgba(2, 132, 199, 0.35);
    }

    .btn-rentify-success {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: #ffffff;
        transition: all 0.2s ease;
    }
    .btn-rentify-success:hover {
        background: linear-gradient(135deg, #059669 0%, #047857 100%);
        box-shadow: 0 8px 20px rgba(16, 185, 129, 0.35);
    }

    #qrcode-canvas-container img, 
    #qrcode-canvas-container canvas {
        margin: 0 auto;
        border-radius: 12px;
        display: block;
    }
</style>

<div class="rentify-pay-wrapper">

    <!-- 1. HEADER NAVIGASI RENTIFY -->
    <div class="rentify-card p-3.5 mb-3 flex items-center justify-between shadow-sm">
        <div class="flex items-center gap-3">
            <a href="{{ route('customer.pesanan') }}" class="w-9 h-9 rounded-xl bg-sky-50 text-[#0284c7] hover:bg-sky-100 flex items-center justify-center transition active:scale-95" title="Kembali ke Pesanan Saya">
                <i class="fa-solid fa-arrow-left text-sm"></i>
            </a>
            <div>
                <h1 class="text-[15px] font-extrabold text-slate-800 leading-tight">Pembayaran QRIS Dinamis</h1>
                <span class="text-[11px] font-medium text-slate-400">No. Pesanan: <b class="text-slate-600 font-mono">{{ $id }}</b></span>
            </div>
        </div>
        <div class="text-[11px] font-extrabold text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-full border border-emerald-100 flex items-center gap-1.5 shrink-0">
            <i class="fa-solid fa-shield-halved text-[10px] text-emerald-600"></i> QRIS ASPI
        </div>
    </div>

    <!-- 2. KARTU TOTAL TAGIHAN & COUNTDOWN -->
    <div class="rentify-card p-4 mb-3">
        <div class="flex items-center justify-between">
            <div>
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block mb-0.5">Total Tagihan</span>
                <span class="text-2xl font-black text-[#0284c7]">Rp {{ number_format($total ?? 0, 0, ',', '.') }}</span>
            </div>
            <div class="text-right">
                <span class="text-[11px] font-extrabold text-emerald-700 bg-emerald-50 border border-emerald-200 px-2.5 py-1 rounded-full inline-flex items-center gap-1">
                    <i class="fa-solid fa-bolt text-[10px] text-amber-500"></i> Auto-Verify 24/7
                </span>
            </div>
        </div>

        @if(!empty($kodeUnik) && $kodeUnik > 0)
        <div class="mt-2.5 pt-2 border-t border-slate-100 flex items-center justify-between text-[11px]">
            <span class="text-slate-500">Subtotal Pesanan: <b class="text-slate-700 font-semibold">Rp {{ number_format($subtotal ?? ($total - $kodeUnik), 0, ',', '.') }}</b></span>
            <span class="text-emerald-700 font-extrabold bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200" title="3-digit angka unik agar transaksi terverifikasi 100% otomatis">
                Kode Unik: +{{ $kodeUnik }}
            </span>
        </div>
        @endif

        <div class="border-t border-slate-100 my-3"></div>

        <div class="flex items-center justify-between text-xs">
            <span class="text-slate-500 font-medium flex items-center gap-1.5">
                <i class="fa-regular fa-clock text-[#0284c7]"></i> Batas waktu pembayaran:
            </span>
            <span id="countdown-timer" class="font-mono font-black text-[#0284c7] bg-sky-50 px-2.5 py-1 rounded-lg border border-sky-100 text-xs tracking-wider">
                23:59:59
            </span>
        </div>
    </div>

    <!-- 3. KARTU QRIS DINAMIS UTAMA -->
    <div class="rentify-card p-5 mb-3 text-center relative overflow-hidden">
        <!-- Stripe aksen atas khas Rentify -->
        <div class="absolute top-0 left-0 right-0 h-1.5 bg-gradient-to-r from-[#38bdf8] via-[#0284c7] to-[#10b981]"></div>

        <div class="pt-1">
            <!-- Header QRIS Resmi -->
            <div class="flex items-center justify-between mb-3 px-1">
                <div class="flex items-center gap-2">
                    <span class="bg-red-600 text-white font-black text-xs px-2.5 py-1 rounded-md tracking-wider">QRIS</span>
                    <span class="text-[11px] font-bold text-slate-600">Standar Pembayaran Nasional</span>
                </div>
                <span class="text-[10px] font-bold text-slate-400 bg-slate-100 px-2 py-0.5 rounded">{{ $acquirer ?? 'DANA Bisnis' }}</span>
            </div>

            <!-- Detail Merchant & NMID -->
            <div class="bg-slate-50 border border-slate-100 rounded-xl p-2.5 mb-3 text-left">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Nama Merchant</span>
                        <span class="text-xs font-black text-slate-800">{{ $merchantName ?? 'Rental kursi' }}</span>
                    </div>
                    <div class="text-right">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">NMID</span>
                        <span class="text-[11px] font-mono font-bold text-slate-600">{{ $merchantNmid ?? 'ID1026587219324' }}</span>
                    </div>
                </div>
            </div>

            <!-- CONTAINER KODE QR DINAMIS -->
            <div class="inline-block p-3.5 bg-white rounded-2xl border-2 border-slate-200/90 shadow-sm mb-3 relative">
                <div id="qrcode-canvas-container" class="w-56 h-56 mx-auto flex items-center justify-center">
                    <!-- Image QR fallback langsung aktif -->
                    <img src="{{ $qrisImageUrl }}" alt="QRIS Dinamis Rentify" class="w-56 h-56 object-contain mx-auto" id="qris-img-fallback" />
                </div>
            </div>

            <p class="text-xs text-slate-600 font-semibold mb-1">
                Scan kode QR di atas menggunakan aplikasi m-Banking atau E-Wallet apa saja
            </p>
            <p class="text-[11px] text-slate-400 font-medium mb-3">
                Nominal <b>Rp {{ number_format($total ?? 0, 0, ',', '.') }}</b> langsung terisi otomatis di layar HP Anda.
            </p>

            <!-- Tombol Aksi Tambahan: Simpan QR & Salin String -->
            <div class="flex items-center justify-center gap-2 pt-2 pb-1">
                <button type="button" onclick="downloadQrCode()" class="flex-1 bg-sky-50 hover:bg-sky-100 text-[#0284c7] border border-sky-200 font-extrabold text-xs py-2 px-3 rounded-xl transition flex items-center justify-center gap-1.5 active:scale-95">
                    <i class="fa-solid fa-download"></i>
                    <span>Simpan Gambar QR</span>
                </button>
                <button type="button" onclick="salinPayloadQris()" class="flex-1 bg-slate-50 hover:bg-slate-100 text-slate-700 border border-slate-200 font-extrabold text-xs py-2 px-3 rounded-xl transition flex items-center justify-center gap-1.5 active:scale-95">
                    <i class="fa-regular fa-copy"></i>
                    <span id="label-salin-qris">Salin Kode QR</span>
                </button>
            </div>

            <!-- Chip Aplikasi yang Didukung -->
            <div class="pt-3 mt-3 border-t border-slate-100">
                <div class="text-[11px] font-bold text-slate-400 mb-2">Didukung oleh seluruh Bank & Dompet Digital:</div>
                <div class="flex items-center justify-center gap-1.5 flex-wrap">
                    <span class="px-2 py-0.5 rounded bg-blue-50 text-blue-600 text-[10px] font-extrabold border border-blue-100">DANA</span>
                    <span class="px-2 py-0.5 rounded bg-emerald-50 text-emerald-600 text-[10px] font-extrabold border border-emerald-100">GoPay</span>
                    <span class="px-2 py-0.5 rounded bg-amber-50 text-amber-700 text-[10px] font-extrabold border border-amber-100">ShopeePay</span>
                    <span class="px-2 py-0.5 rounded bg-purple-50 text-purple-700 text-[10px] font-extrabold border border-purple-100">OVO</span>
                    <span class="px-2 py-0.5 rounded bg-sky-50 text-sky-700 text-[10px] font-extrabold border border-sky-100">BCA Mobile</span>
                    <span class="px-2 py-0.5 rounded bg-blue-900 text-yellow-300 text-[10px] font-extrabold">Livin' Mandiri</span>
                    <span class="px-2 py-0.5 rounded bg-blue-600 text-white text-[10px] font-extrabold">BRImo</span>
                    <span class="px-2 py-0.5 rounded bg-orange-50 text-orange-600 text-[10px] font-extrabold border border-orange-200">BNI Mobile</span>
                </div>
            </div>
        </div>

        <!-- 🧪 PANEL PENGUJIAN OTOMATIS (MODE TESTING) -->
        <div class="mt-4 pt-3.5 border-t border-dashed border-sky-200 text-left">
            <div class="p-3 bg-gradient-to-r from-sky-50 to-indigo-50 border border-sky-200 rounded-xl">
                <div class="flex items-center justify-between mb-1.5">
                    <span class="text-[11px] font-extrabold text-sky-800 flex items-center gap-1.5">
                        <i class="fa-solid fa-flask text-[#0284c7]"></i> Pengujian Cepat
                    </span>
                    <span class="text-[10px] bg-sky-200/60 text-sky-800 font-bold px-2 py-0.5 rounded-full">Test Simulation</span>
                </div>
                <p class="text-[11px] text-slate-600 mb-2 leading-relaxed">
                    Uji alur verifikasi otomatis ke struk pesanan secara langsung:
                </p>
                <button type="button" onclick="simulasikanBayar()" id="btn-simulasi" class="w-full bg-white hover:bg-sky-50 text-[#0284c7] border border-sky-300 font-extrabold text-xs py-2 px-3 rounded-xl transition shadow-sm flex items-center justify-center gap-2 active:scale-95">
                    <i class="fa-solid fa-wand-magic-sparkles text-sky-500"></i>
                    <span>⚡ Simulasikan Pembayaran Berhasil</span>
                </button>
            </div>
        </div>

    </div>

    <!-- 4. PETUNJUK CARA PEMBAYARAN -->
    <div class="rentify-card p-4 mb-3">
        <h2 class="text-xs font-extrabold text-slate-400 uppercase tracking-wider mb-2.5 flex items-center gap-1.5">
            <i class="fa-solid fa-circle-question text-[#0284c7]"></i>
            <span>Petunjuk Pembayaran QRIS</span>
        </h2>

        <div class="divide-y divide-slate-100 border border-slate-100 rounded-xl overflow-hidden">
            <div>
                <div class="accordion-header p-3.5 flex items-center justify-between bg-slate-50/50" onclick="togglePetunjuk('qris-ewallet')">
                    <div class="flex items-center gap-2 font-bold text-xs text-slate-800">
                        <i class="fa-solid fa-wallet text-[#0284c7]"></i>
                        <span>Lewat E-Wallet (DANA, GoPay, OVO, ShopeePay)</span>
                    </div>
                    <i class="fa-solid fa-chevron-up text-slate-400 text-xs transition-transform" id="chevron-qris-ewallet"></i>
                </div>
                <div id="panel-qris-ewallet" class="p-3.5 bg-white text-xs text-slate-600 space-y-2.5">
                    <div class="flex items-start gap-2">
                        <span class="step-badge">1</span>
                        <span>Buka aplikasi e-wallet pilihan Anda (DANA, GoPay, OVO, ShopeePay, dll).</span>
                    </div>
                    <div class="flex items-start gap-2">
                        <span class="step-badge">2</span>
                        <span>Pilih menu <b>Scan / Bayar QRIS</b> di aplikasi Anda.</span>
                    </div>
                    <div class="flex items-start gap-2">
                        <span class="step-badge">3</span>
                        <span>Arahkan kamera smartphone ke kode QRIS di atas (atau pilih foto QR dari galeri jika disimpan).</span>
                    </div>
                    <div class="flex items-start gap-2">
                        <span class="step-badge">4</span>
                        <span>Periksa nama merchant (<b>{{ $merchantName ?? 'Rental kursi' }}</b>) dan nominal <b>Rp {{ number_format($total ?? 0, 0, ',', '.') }}</b> yang otomatis terkunci.</span>
                    </div>
                    <div class="flex items-start gap-2">
                        <span class="step-badge">5</span>
                        <span>Konfirmasi pembayaran dengan PIN Anda. Setelah berhasil, tekan tombol <b>Saya Sudah Bayar</b> di bawah.</span>
                    </div>
                </div>
            </div>

            <div>
                <div class="accordion-header p-3.5 flex items-center justify-between bg-slate-50/50" onclick="togglePetunjuk('qris-bank')">
                    <div class="flex items-center gap-2 font-bold text-xs text-slate-800">
                        <i class="fa-solid fa-building-columns text-[#0284c7]"></i>
                        <span>Lewat Mobile Banking (BCA, Mandiri, BRI, BNI, dll)</span>
                    </div>
                    <i class="fa-solid fa-chevron-down text-slate-400 text-xs transition-transform" id="chevron-qris-bank"></i>
                </div>
                <div id="panel-qris-bank" class="hidden p-3.5 bg-white text-xs text-slate-600 space-y-2.5">
                    <div class="flex items-start gap-2">
                        <span class="step-badge">1</span>
                        <span>Buka aplikasi m-Banking Anda (BCA Mobile, Livin' by Mandiri, BRImo, BNI Mobile, dll).</span>
                    </div>
                    <div class="flex items-start gap-2">
                        <span class="step-badge">2</span>
                        <span>Pilih menu <b>QRIS / Scan QR</b>.</span>
                    </div>
                    <div class="flex items-start gap-2">
                        <span class="step-badge">3</span>
                        <span>Scan kode QR di atas lalu selesaikan pembayaran sesuai nominal.</span>
                    </div>
                    <div class="flex items-start gap-2">
                        <span class="step-badge">4</span>
                        <span>Kembali ke halaman ini dan tekan tombol <b>Saya Sudah Bayar</b>.</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kartu Feedback Live Status -->
        <div id="status-card" class="hidden p-3.5 rounded-xl text-xs font-bold mt-3 transition-all text-center"></div>
    </div>

</div>

<!-- 5. FIXED BOTTOM ACTION BAR DUAL BUTTON (SAYA SUDAH BAYAR + CEK STATUS) -->
<div class="fixed bottom-0 left-0 w-full bg-white/95 backdrop-blur-md border-t border-slate-200/80 px-4 py-3 z-50 shadow-[0_-4px_25px_rgba(2,132,199,0.08)]">
    <div class="max-w-[480px] mx-auto flex gap-2">
        <button type="button" onclick="konfirmasiSudahBayar()" id="btn-sudah-bayar" class="flex-1 btn-rentify-success font-extrabold text-[13.5px] py-3.5 px-4 rounded-2xl shadow-lg shadow-emerald-500/25 transition-all active:scale-[0.98] flex items-center justify-center gap-2">
            <i class="fa-solid fa-circle-check text-sm" id="icon-btn-sudah-bayar"></i>
            <span id="label-btn-sudah-bayar">Saya Sudah Bayar</span>
        </button>
        <button type="button" onclick="checkStatusLive(true)" id="btn-cek-status" class="bg-sky-50 hover:bg-sky-100 text-[#0284c7] border border-sky-200 font-extrabold text-[13px] py-3.5 px-4 rounded-2xl transition-all active:scale-[0.98] flex items-center justify-center gap-1.5 shrink-0">
            <i class="fa-solid fa-rotate text-sm" id="icon-btn-cek"></i>
            <span id="label-btn-cek" class="hidden sm:inline">Cek Status</span>
        </button>
    </div>
</div>

<script>
    const invoiceId = "{{ $id }}";
    const qrisPayload = {!! json_encode($qrisPayload ?? '') !!};
    const qrisImageUrl = {!! json_encode($qrisImageUrl ?? '') !!};
    const checkStatusUrl = "{{ route('customer.pembayaran.check_status', $id) }}";
    const confirmUrl = "{{ route('customer.pembayaran.confirm', $id) }}";
    const simulateUrl = "{{ route('customer.pembayaran.simulate', $id) }}";
    const receiptUrl = "{{ route('customer.struk', $id) }}";
    const csrfToken = "{{ csrf_token() }}";

    // 1. Render Kode QR Dinamis di Browser
    document.addEventListener("DOMContentLoaded", function() {
        if (qrisPayload && typeof QRCode !== 'undefined') {
            try {
                const container = document.getElementById('qrcode-canvas-container');
                if (container) {
                    container.innerHTML = ''; // bersihkan fallback
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
            showMessage('success', 'Gambar QRIS berhasil diunduh. Anda bisa scan dari galeri m-Banking!');
        } else {
            alert('Tidak dapat mengunduh QR code saat ini.');
        }
    }

    // 3. Salin String QRIS
    function salinPayloadQris() {
        if (!qrisPayload) return;
        navigator.clipboard.writeText(qrisPayload).then(function() {
            const label = document.getElementById('label-salin-qris');
            if (label) {
                label.innerText = 'Tersalin! ✓';
                setTimeout(function() { label.innerText = 'Salin Kode QR'; }, 2000);
            }
            showMessage('success', 'Kode string QRIS berhasil disalin ke clipboard!');
        }).catch(function() {
            alert('Kode QRIS: ' + qrisPayload);
        });
    }

    // 4. Toggle Accordion Petunjuk
    function togglePetunjuk(id) {
        const panel = document.getElementById('panel-' + id);
        const chevron = document.getElementById('chevron-' + id);
        if (!panel) return;

        if (panel.classList.contains('hidden')) {
            panel.classList.remove('hidden');
            if (chevron) { chevron.classList.remove('fa-chevron-down'); chevron.classList.add('fa-chevron-up'); }
        } else {
            panel.classList.add('hidden');
            if (chevron) { chevron.classList.remove('fa-chevron-up'); chevron.classList.add('fa-chevron-down'); }
        }
    }

    // 5. Countdown 24 Jam
    let targetTime = new Date().getTime() + (24 * 60 * 60 * 1000);
    function updateCountdown() {
        let now = new Date().getTime();
        let distance = targetTime - now;
        if (distance < 0) {
            const el = document.getElementById("countdown-timer");
            if (el) el.innerText = "00:00:00 (WAKTU HABIS)";
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

    function showMessage(type, text) {
        const statusCard = document.getElementById('status-card');
        if (!statusCard) return;
        statusCard.classList.remove('hidden', 'bg-emerald-50', 'text-emerald-700', 'border-emerald-200', 'bg-amber-50', 'text-amber-700', 'border-amber-200', 'bg-rose-50', 'text-rose-700', 'border-rose-200');
        
        if (type === 'success') {
            statusCard.classList.add('bg-emerald-50', 'text-emerald-700', 'border', 'border-emerald-200');
            statusCard.innerHTML = '<i class="fa-solid fa-circle-check text-emerald-500 mr-1.5"></i> ' + text;
        } else if (type === 'pending') {
            statusCard.classList.add('bg-sky-50', 'text-[#0284c7]', 'border', 'border-sky-200');
            statusCard.innerHTML = '<i class="fa-solid fa-clock text-[#0284c7] mr-1.5"></i> ' + text;
        } else {
            statusCard.classList.add('bg-rose-50', 'text-rose-700', 'border', 'border-rose-200');
            statusCard.innerHTML = '<i class="fa-solid fa-circle-xmark text-rose-500 mr-1.5"></i> ' + text;
        }
    }

    let isChecking = false;

    // 6. Live status polling (otomatis tiap 4 detik & manual saat tombol ditekan)
    function checkStatusLive(isManual = false) {
        if (isChecking) return;
        isChecking = true;

        const iconCek = document.getElementById('icon-btn-cek');
        const labelCek = document.getElementById('label-btn-cek');

        if (isManual && iconCek) {
            iconCek.classList.add('fa-spin');
            if (labelCek) labelCek.innerText = 'Memeriksa...';
        }

        fetch(checkStatusUrl, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            if (data.paid) {
                showMessage('success', '🎉 Pembayaran lunas & terverifikasi! Mengalihkan ke struk...');
                setTimeout(function() { window.location.href = data.redirect_url || receiptUrl; }, 1000);
            } else {
                if (isManual) {
                    showMessage('pending', 'Menunggu pembayaran. Setelah scan QRIS di m-Banking/E-Wallet, tekan tombol "Saya Sudah Bayar".');
                }
            }
        })
        .catch(function(e) { 
            console.error(e); 
        })
        .finally(function() {
            isChecking = false;
            if (isManual && iconCek) {
                setTimeout(function() {
                    iconCek.classList.remove('fa-spin');
                    if (labelCek) labelCek.innerText = 'Cek Status';
                }, 800);
            }
        });
    }

    // Polling background otomatis setiap 4 detik
    setInterval(function() {
        checkStatusLive(false);
    }, 4000);

    // 7. Aksi "Saya Sudah Bayar" oleh Pelanggan
    function konfirmasiSudahBayar() {
        const btn = document.getElementById('btn-sudah-bayar');
        const icon = document.getElementById('icon-btn-sudah-bayar');
        const label = document.getElementById('label-btn-sudah-bayar');

        if (btn) btn.disabled = true;
        if (icon) icon.className = 'fa-solid fa-spinner fa-spin text-sm';
        if (label) label.innerText = 'Mengonfirmasi Pembayaran...';

        fetch(confirmUrl, {
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
                showMessage('success', data.message || 'Pembayaran berhasil dikonfirmasi! Mengalihkan ke struk...');
                if (label) label.innerText = 'Berhasil! Mengalihkan...';
                setTimeout(function() { window.location.href = data.redirect_url || receiptUrl; }, 1000);
            } else {
                alert(data.message || 'Gagal mengonfirmasi pembayaran.');
                resetBtnSudahBayar();
            }
        })
        .catch(function(err) {
            console.error(err);
            alert('Terjadi kesalahan saat memproses konfirmasi.');
            resetBtnSudahBayar();
        });
    }

    function resetBtnSudahBayar() {
        const btn = document.getElementById('btn-sudah-bayar');
        const icon = document.getElementById('icon-btn-sudah-bayar');
        const label = document.getElementById('label-btn-sudah-bayar');
        if (btn) btn.disabled = false;
        if (icon) icon.className = 'fa-solid fa-circle-check text-sm';
        if (label) label.innerText = 'Saya Sudah Bayar';
    }

    // 8. Fungsi Simulasi Bayar Berhasil (Khusus Mode Testing)
    function simulasikanBayar() {
        const btn = document.getElementById('btn-simulasi');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-1.5"></i> Menyimulasikan Pembayaran Lunas...';
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
                showMessage('success', data.message || 'Pembayaran berhasil dikonfirmasi! Mengalihkan ke struk...');
                setTimeout(function() { window.location.href = data.redirect_url || receiptUrl; }, 1000);
            } else {
                alert(data.message || 'Gagal menyimulasikan pembayaran.');
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fa-solid fa-wand-magic-sparkles text-sky-500"></i> <span>⚡ Simulasikan Pembayaran Berhasil</span>';
                }
            }
        })
        .catch(function(err) {
            console.error(err);
            alert('Terjadi kesalahan saat simulasi.');
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = '<i class="fa-solid fa-wand-magic-sparkles text-sky-500"></i> <span>⚡ Simulasikan Pembayaran Berhasil</span>';
            }
        });
    }
</script>

@endsection
