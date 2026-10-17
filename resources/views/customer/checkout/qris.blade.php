@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
        padding: 16px 14px 110px 14px;
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
</style>

@php
    $isProd = (bool) config('midtrans.is_production', false);
    $snapJsUrl = $isProd 
        ? 'https://app.midtrans.com/snap/snap.js' 
        : 'https://app.sandbox.midtrans.com/snap/snap.js';
    $clientKey = config('midtrans.client_key');
@endphp

<!-- Midtrans Snap JS (Fallback) -->
<script src="{{ $snapJsUrl }}" data-client-key="{{ $clientKey }}"></script>

<div class="rentify-pay-wrapper">

    <!-- 1. HEADER NAVIGASI RENTIFY -->
    <div class="rentify-card p-3.5 mb-3 flex items-center justify-between shadow-sm">
        <div class="flex items-center gap-3">
            <a href="{{ route('customer.pesanan') }}" class="w-9 h-9 rounded-xl bg-sky-50 text-[#0284c7] hover:bg-sky-100 flex items-center justify-center transition active:scale-95" title="Kembali ke Pesanan Saya">
                <i class="fa-solid fa-arrow-left text-sm"></i>
            </a>
            <div>
                <h1 class="text-[15px] font-extrabold text-slate-800 leading-tight">Pembayaran {{ $bankName }}</h1>
                <span class="text-[11px] font-medium text-slate-400">No. Pesanan: <b class="text-slate-600 font-mono">{{ $id }}</b></span>
            </div>
        </div>
        <div class="text-[11px] font-extrabold text-[#0284c7] bg-sky-50 px-2.5 py-1 rounded-full border border-sky-100 flex items-center gap-1.5 shrink-0">
            <i class="fa-solid fa-shield-halved text-[10px]"></i> Resmi
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
                    <i class="fa-solid fa-circle-check text-[10px] text-emerald-500"></i> Verifikasi Otomatis
                </span>
            </div>
        </div>

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

    @if(!empty($snapError))
    <div class="mb-3">
        <div class="bg-amber-50 border border-amber-300 rounded-2xl p-4 shadow-sm">
            <div class="flex items-start gap-3">
                <div class="w-8 h-8 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center shrink-0 mt-0.5">
                    <i class="fa-solid fa-triangle-exclamation text-base"></i>
                </div>
                <div class="flex-1">
                    <h4 class="text-xs font-bold text-amber-900 mb-1">Status Sesi Pembayaran</h4>
                    <p class="text-xs text-amber-800 leading-relaxed">
                        Sistem sedang menyiapkan jalur pembayaran: {{ $snapError }}. Silakan tekan tombol Cek Status Pembayaran di bawah.
                    </p>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- 3. KARTU PEMBAYARAN UTAMA -->
    <div class="rentify-card p-5 mb-3 text-center relative overflow-hidden">
        <!-- Stripe aksen atas khas Rentify -->
        <div class="absolute top-0 left-0 right-0 h-1.5 bg-gradient-to-r from-[#38bdf8] via-[#0284c7] to-[#10b981]"></div>

        @if($channelCode === 'QRIS')
            <!-- TAMPILAN RESMI QRIS -->
            <div class="pt-1">
                <div class="flex items-center justify-center gap-2 mb-3">
                    <span class="bg-red-50 text-red-600 font-black text-xs px-2.5 py-0.5 rounded-lg border border-red-200 tracking-wider">QRIS</span>
                    <span class="text-xs font-bold text-slate-700">Standar Pembayaran Nasional</span>
                </div>

                @if(!empty($qrisImageUrl))
                    <!-- GAMBAR QRIS LANGSUNG MUNCUL -->
                    <div class="inline-block p-3.5 bg-white rounded-2xl border-2 border-slate-100 shadow-sm mb-3">
                        <img src="{{ $qrisImageUrl }}" alt="QRIS Code Rentify" class="w-56 h-56 object-contain mx-auto" id="qris-img" />
                    </div>
                    <p class="text-xs text-slate-500 font-medium mb-3">
                        Buka aplikasi m-Banking atau E-Wallet apa saja, lalu scan kode QR di atas
                    </p>
                @else
                    <!-- Fallback tombol jika QR masih proses generate -->
                    <div class="w-16 h-16 mx-auto mb-3 bg-sky-50 text-[#0284c7] rounded-2xl flex items-center justify-center text-3xl shadow-sm border border-sky-100">
                        <i class="fa-solid fa-qrcode"></i>
                    </div>
                    <p class="text-xs text-slate-500 mb-4">Tekan tombol di bawah untuk menampilkan kode QRIS Anda.</p>
                    <button type="button" onclick="bukaGateway()" class="w-full btn-rentify-gradient font-black py-3.5 px-4 rounded-xl shadow-md transition-all active:scale-95 flex items-center justify-center gap-2 mb-3">
                        <i class="fa-solid fa-qrcode text-lg"></i>
                        <span class="text-sm">Tampilkan Kode QRIS</span>
                    </button>
                @endif

                <!-- Chip Aplikasi yang Didukung -->
                <div class="pt-3 border-t border-slate-100">
                    <div class="text-[11px] font-bold text-slate-400 mb-2">Bisa di-scan dari aplikasi apapun:</div>
                    <div class="flex items-center justify-center gap-1.5 flex-wrap">
                        <span class="px-2 py-0.5 rounded bg-blue-50 text-blue-600 text-[10px] font-extrabold border border-blue-100">DANA</span>
                        <span class="px-2 py-0.5 rounded bg-emerald-50 text-emerald-600 text-[10px] font-extrabold border border-emerald-100">GoPay</span>
                        <span class="px-2 py-0.5 rounded bg-amber-50 text-amber-700 text-[10px] font-extrabold border border-amber-100">ShopeePay</span>
                        <span class="px-2 py-0.5 rounded bg-purple-50 text-purple-700 text-[10px] font-extrabold border border-purple-100">OVO</span>
                        <span class="px-2 py-0.5 rounded bg-sky-50 text-sky-700 text-[10px] font-extrabold border border-sky-100">BCA Mobile</span>
                        <span class="px-2 py-0.5 rounded bg-blue-900 text-yellow-300 text-[10px] font-extrabold">Livin' Mandiri</span>
                        <span class="px-2 py-0.5 rounded bg-blue-600 text-white text-[10px] font-extrabold">BRImo</span>
                    </div>
                </div>
            </div>

        @elseif($channelCode === 'GOPAY')
            <!-- TAMPILAN GOPAY -->
            <div class="pt-1">
                <div class="w-14 h-14 mx-auto rounded-2xl bg-[#00a5cf]/10 flex items-center justify-center text-2xl mb-3 border border-[#00a5cf]/20">
                    <span class="text-[#00a5cf] font-black text-lg">GP</span>
                </div>
                <h3 class="text-base font-extrabold text-slate-800">Pembayaran GoPay</h3>
                <p class="text-xs text-slate-500 mt-1 mb-4">Buka aplikasi GoPay untuk menyelesaikan pembayaran sewa secara otomatis.</p>
                
                @if(!empty($gopayDeeplink))
                    <a href="{{ $gopayDeeplink }}" class="w-full bg-[#00a5cf] hover:bg-[#0090b5] text-white font-extrabold py-3.5 px-4 rounded-xl shadow-md transition-all active:scale-95 flex items-center justify-center gap-2 mb-2">
                        <i class="fa-solid fa-arrow-up-right-from-square"></i>
                        <span>Buka Aplikasi GoPay Sekarang</span>
                    </a>
                @else
                    <button type="button" onclick="bukaGateway()" class="w-full bg-[#00a5cf] text-white font-extrabold py-3.5 px-4 rounded-xl hover:bg-[#0090b5] transition mb-2">
                        <span>Buka Aplikasi GoPay</span>
                    </button>
                @endif
                <p class="text-[11px] text-slate-400 mt-2">Setelah membayar di GoPay, halaman ini otomatis mengalihkan ke struk.</p>
            </div>

        @else
            <!-- TAMPILAN VIRTUAL ACCOUNT BANK -->
            <div class="pt-1">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 text-left">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-xl flex items-center justify-center font-black text-xs text-white shadow-sm" style="background-color: {{ $bankColor }};">
                            {{ $bankLogo }}
                        </div>
                        <div>
                            <div class="text-[14px] font-extrabold text-slate-800">{{ $bankName }}</div>
                            <div class="text-[11px] text-slate-400 font-medium">Virtual Account Resmi</div>
                        </div>
                    </div>
                    <span class="text-[10px] font-extrabold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200">
                        ✓ Aktif
                    </span>
                </div>

                <div class="pt-4 pb-2 text-left">
                    <span class="text-xs font-semibold text-slate-500 block mb-1.5">No. Rekening / Virtual Account:</span>
                    <div class="flex items-center justify-between bg-slate-50 border border-slate-200/80 rounded-xl px-3.5 py-2.5">
                        <span id="text-va" class="text-lg md:text-xl font-black text-slate-800 font-mono tracking-wider">
                            {{ $kodePembayaran }}
                        </span>
                        <button type="button" onclick="salinKodeVA()" class="bg-sky-50 hover:bg-sky-100 text-[#0284c7] border border-sky-200 text-xs font-extrabold px-3 py-1.5 rounded-lg active:scale-95 transition flex items-center gap-1.5">
                            <i class="fa-regular fa-copy"></i>
                            <span id="label-salin">SALIN</span>
                        </button>
                    </div>
                </div>

                <div class="text-[11px] text-slate-400 mt-2 flex items-center gap-1.5 text-left">
                    <i class="fa-solid fa-circle-info text-[#0284c7]"></i>
                    <span>Transfer sesuai nominal tepat hingga digit terakhir. Setelah transfer, sistem otomatis memverifikasi.</span>
                </div>
            </div>
        @endif

        <!-- 🧪 PANEL PENGUJIAN OTOMATIS (KHUSUS MODE SANDBOX) -->
        @if(!config('midtrans.is_production', false))
        <div class="mt-4 pt-3.5 border-t border-dashed border-sky-200 text-left">
            <div class="p-3 bg-gradient-to-r from-sky-50 to-indigo-50 border border-sky-200 rounded-xl">
                <div class="flex items-center justify-between mb-1.5">
                    <span class="text-[11px] font-extrabold text-sky-800 flex items-center gap-1.5">
                        <i class="fa-solid fa-flask text-[#0284c7]"></i> Mode Sandbox (Pengujian)
                    </span>
                    <span class="text-[10px] bg-sky-200/60 text-sky-800 font-bold px-2 py-0.5 rounded-full">Test Mode</span>
                </div>
                <p class="text-[11px] text-slate-600 mb-2.5 leading-relaxed">
                    Uji alur otomatisasi verifikasi ke struk tanpa transfer uang sungguhan:
                </p>
                <button type="button" onclick="simulasikanBayar()" id="btn-simulasi" class="w-full bg-white hover:bg-sky-50 text-[#0284c7] border border-sky-300 font-extrabold text-xs py-2.5 px-3 rounded-xl transition shadow-sm flex items-center justify-center gap-2 active:scale-95">
                    <i class="fa-solid fa-wand-magic-sparkles text-sky-500"></i>
                    <span>⚡ Simulasikan Pembayaran Berhasil (Uji Otomatis)</span>
                </button>
            </div>
        </div>
        @endif

    </div>

    <!-- 4. PETUNJUK CARA PEMBAYARAN -->
    <div class="rentify-card p-4 mb-3">
        <h2 class="text-xs font-extrabold text-slate-400 uppercase tracking-wider mb-2.5 flex items-center gap-1.5">
            <i class="fa-solid fa-circle-question text-[#0284c7]"></i>
            <span>Petunjuk Pembayaran {{ $bankName }}</span>
        </h2>

        <div class="divide-y divide-slate-100 border border-slate-100 rounded-xl overflow-hidden">
            @if($channelCode === 'QRIS')
                <div>
                    <div class="accordion-header p-3.5 flex items-center justify-between bg-slate-50/50" onclick="togglePetunjuk('qris-ewallet')">
                        <div class="flex items-center gap-2 font-bold text-xs text-slate-800">
                            <i class="fa-solid fa-wallet text-[#0284c7]"></i>
                            <span>Lewat E-Wallet (GoPay, DANA, OVO, ShopeePay)</span>
                        </div>
                        <i class="fa-solid fa-chevron-up text-slate-400 text-xs transition-transform" id="chevron-qris-ewallet"></i>
                    </div>
                    <div id="panel-qris-ewallet" class="p-3.5 bg-white text-xs text-slate-600 space-y-2.5">
                        <div class="flex items-start gap-2">
                            <span class="step-badge">1</span>
                            <span>Buka aplikasi e-wallet pilihan Anda (GoPay, DANA, OVO, ShopeePay, dll).</span>
                        </div>
                        <div class="flex items-start gap-2">
                            <span class="step-badge">2</span>
                            <span>Pilih menu <b>Scan / Bayar QRIS</b> di aplikasi Anda.</span>
                        </div>
                        <div class="flex items-start gap-2">
                            <span class="step-badge">3</span>
                            <span>Arahkan kamera smartphone ke kode QRIS di atas.</span>
                        </div>
                        <div class="flex items-start gap-2">
                            <span class="step-badge">4</span>
                            <span>Periksa nama merchant (<b>Rentify</b>) dan total tagihan <b>Rp {{ number_format($total ?? 0, 0, ',', '.') }}</b>.</span>
                        </div>
                        <div class="flex items-start gap-2">
                            <span class="step-badge">5</span>
                            <span>Selesaikan pembayaran. Layar Rentify otomatis beralih ke struk pesanan.</span>
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
                            <span>Buka aplikasi m-Banking Anda (BCA Mobile, Livin' Mandiri, BRImo, BNI Mobile, dll).</span>
                        </div>
                        <div class="flex items-start gap-2">
                            <span class="step-badge">2</span>
                            <span>Pilih fitur <b>QRIS / Scan QR</b>.</span>
                        </div>
                        <div class="flex items-start gap-2">
                            <span class="step-badge">3</span>
                            <span>Scan kode QR di atas lalu konfirmasi pembayaran.</span>
                        </div>
                    </div>
                </div>
            @else
                <div>
                    <div class="accordion-header p-3.5 flex items-center justify-between bg-slate-50/50" onclick="togglePetunjuk('va-guide')">
                        <div class="flex items-center gap-2 font-bold text-xs text-slate-800">
                            <i class="fa-solid fa-mobile-screen text-[#0284c7]"></i>
                            <span>Cara Transfer Virtual Account</span>
                        </div>
                        <i class="fa-solid fa-chevron-up text-slate-400 text-xs transition-transform" id="chevron-va-guide"></i>
                    </div>
                    <div id="panel-va-guide" class="p-3.5 bg-white text-xs text-slate-600 space-y-2.5">
                        <div class="flex items-start gap-2"><span class="step-badge">1</span><span>Buka aplikasi m-Banking atau ATM dari bank pilihan Anda.</span></div>
                        <div class="flex items-start gap-2"><span class="step-badge">2</span><span>Pilih menu <b>Transfer / Virtual Account</b>.</span></div>
                        <div class="flex items-start gap-2"><span class="step-badge">3</span><span>Masukkan nomor: <b class="font-mono text-[#0284c7] font-black">{{ $kodePembayaran }}</b>.</span></div>
                        <div class="flex items-start gap-2"><span class="step-badge">4</span><span>Konfirmasi pembayaran sesuai nominal <b>Rp {{ number_format($total ?? 0, 0, ',', '.') }}</b>.</span></div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Kartu Feedback Live Status -->
        <div id="status-card" class="hidden p-3.5 rounded-xl text-xs font-bold mt-3 transition-all text-center"></div>
    </div>

</div>

<!-- 5. FIXED BOTTOM ACTION BAR KHAS RENTIFY (SATU TOMBOL TUNGGAL, TANPA COD, TANPA DOUBLE BUTTON) -->
<div class="fixed bottom-0 left-0 w-full bg-white/95 backdrop-blur-md border-t border-slate-200/80 px-4 py-3 z-50 shadow-[0_-4px_25px_rgba(2,132,199,0.08)]">
    <div class="max-w-[480px] mx-auto">
        <button type="button" onclick="checkStatusLive(true)" id="btn-cek-status" class="w-full btn-rentify-gradient font-extrabold text-[14px] py-3.5 px-4 rounded-2xl shadow-lg shadow-sky-500/25 transition-all active:scale-[0.98] flex items-center justify-center gap-2">
            <i class="fa-solid fa-rotate text-sm" id="icon-btn-cek"></i>
            <span id="label-btn-cek">Cek Status Pembayaran</span>
        </button>
    </div>
</div>

<script>
    const snapToken = "{{ $snapToken ?? '' }}";
    const invoiceId = "{{ $id }}";
    const checkStatusUrl = "{{ route('customer.pembayaran.check_status', $id) }}";
    const simulateUrl = "{{ route('customer.pembayaran.simulate', $id) }}";
    const receiptUrl = "{{ route('customer.struk', $id) }}";
    const csrfToken = "{{ csrf_token() }}";

    // Salin Kode Virtual Account
    function salinKodeVA() {
        const vaEl = document.getElementById('text-va');
        if (!vaEl) return;
        const text = vaEl.innerText.trim();
        
        navigator.clipboard.writeText(text).then(function() {
            const label = document.getElementById('label-salin');
            if (label) {
                label.innerText = 'TERSLIN! ✓';
                setTimeout(function() { label.innerText = 'SALIN'; }, 2000);
            }
        }).catch(function() {
            alert('Nomor disalin: ' + text);
        });
    }

    // Toggle Accordion Petunjuk
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

    // Countdown 24 Jam
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

    // Fallback Snap Popup jika diperlukan
    function bukaGateway() {
        if (!snapToken) {
            alert("Sesi pembayaran online belum dapat dimulai. Silakan coba sesaat lagi.");
            return;
        }
        if (typeof window.snap === 'undefined') {
            alert("Gateway pembayaran sedang memuat, mohon periksa koneksi internet Anda.");
            return;
        }
        snap.pay(snapToken, {
            onSuccess: function (result) {
                showMessage('success', 'Pembayaran berhasil! Mengalihkan ke struk pesanan...');
                setTimeout(function() { window.location.href = receiptUrl; }, 1200);
            },
            onPending: function (result) {
                showMessage('pending', 'Menunggu penyelesaian transfer bank Anda...');
                checkStatusLive(true);
            },
            onError: function (result) {
                showMessage('error', 'Pembayaran dibatalkan atau terjadi kendala.');
            },
            onClose: function () {
                checkStatusLive(true);
            }
        });
    }

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

    // Live status polling (otomatis tiap 4 detik & manual saat tombol ditekan)
    function checkStatusLive(isManual = false) {
        if (isChecking) return;
        isChecking = true;

        const iconCek = document.getElementById('icon-btn-cek');
        const labelCek = document.getElementById('label-btn-cek');

        if (isManual && iconCek) {
            iconCek.classList.add('fa-spin');
            if (labelCek) labelCek.innerText = 'Memeriksa Pembayaran...';
        }

        fetch(checkStatusUrl, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            if (data.paid) {
                showMessage('success', '🎉 Pembayaran lunas & terverifikasi! Mengalihkan ke struk...');
                if (labelCek) labelCek.innerText = 'Lunas! Mengalihkan...';
                setTimeout(function() { window.location.href = data.redirect_url || receiptUrl; }, 1200);
            } else {
                if (isManual) {
                    showMessage('pending', 'Pembayaran belum terdeteksi. Silakan selesaikan scan / transfer terlebih dahulu.');
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
                    if (labelCek) labelCek.innerText = 'Cek Status Pembayaran';
                }, 800);
            }
        });
    }

    // Polling background otomatis setiap 4 detik
    setInterval(function() {
        checkStatusLive(false);
    }, 4000);

    // Fungsi Simulasi Bayar Berhasil (Khusus Mode Sandbox)
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
                    btn.innerHTML = '<i class="fa-solid fa-wand-magic-sparkles text-sky-500"></i> <span>⚡ Simulasikan Pembayaran Berhasil (Uji Otomatis)</span>';
                }
            }
        })
        .catch(function(err) {
            console.error(err);
            alert('Terjadi kesalahan saat simulasi.');
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = '<i class="fa-solid fa-wand-magic-sparkles text-sky-500"></i> <span>⚡ Simulasikan Pembayaran Berhasil (Uji Otomatis)</span>';
            }
        });
    }
</script>

@endsection