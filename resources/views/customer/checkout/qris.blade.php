@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
    /* Sembunyikan navbar dan footer bawaan agar tampilan full-screen ala Shopee */
    nav, header, footer, .navbar, .header, .top-bar, .footer, #footer { display: none !important; }
    body { background-color: #f1f5f9; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; padding-bottom: 110px; margin: 0; }
    
    .shopee-container { max-width: 500px; margin: 0 auto; background: white; min-height: 100vh; box-shadow: 0 0 20px rgba(0,0,0,0.05); }
    
    .accordion-header { cursor: pointer; transition: background-color 0.15s; }
    .accordion-header:active { background-color: #f8fafc; }
    
    .step-badge {
        width: 20px;
        height: 20px;
        border-radius: 50%;
        background: #e0f2fe;
        color: #0284c7;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 11px;
        font-weight: 900;
        flex-shrink: 0;
    }
</style>

@php
    $isProd = config('midtrans.is_production', true);
    $snapJsUrl = $isProd 
        ? 'https://app.midtrans.com/snap/snap.js' 
        : 'https://app.sandbox.midtrans.com/snap/snap.js';
    $clientKey = config('midtrans.client_key');
@endphp

<!-- Payment Gateway Snap JS -->
<script src="{{ $snapJsUrl }}" data-client-key="{{ $clientKey }}"></script>

<div class="shopee-container relative">

    <!-- 1. HEADER HALAMAN PEMBAYARAN -->
    <div class="sticky top-0 z-40 bg-white border-b border-slate-100 px-4 py-3.5 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ route('customer.pesanan') }}" class="text-slate-700 hover:text-sky-600 transition text-lg flex items-center justify-center p-1">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
            <div>
                <h1 class="text-[16px] font-bold text-slate-800 leading-tight">Pembayaran {{ $bankName }}</h1>
                <span class="text-[11px] text-slate-400 font-medium">No. Pesanan: {{ $id }}</span>
            </div>
        </div>
        <div class="text-[11px] font-bold text-sky-600 bg-sky-50 px-2.5 py-1 rounded-full border border-sky-100 flex items-center gap-1">
            <i class="fa-solid fa-shield-halved text-[10px]"></i> Resmi
        </div>
    </div>

    <!-- 2. COUNTDOWN BANNER (WAKTU PEMBAYARAN ALA SHOPEE) -->
    <div class="bg-gradient-to-r from-amber-500/10 via-amber-50 to-orange-50 border-b border-amber-200/70 px-4 py-3 flex items-center justify-between">
        <div class="flex items-center gap-2">
            <i class="fa-solid fa-clock text-amber-500 text-sm"></i>
            <span class="text-xs font-semibold text-slate-700">Bayar sebelum:</span>
        </div>
        <div class="flex items-center gap-1.5 font-mono font-black text-amber-600 text-sm tracking-wider" id="countdown-timer">
            23:59:59
        </div>
    </div>

    <!-- 3. RINGKASAN TOTAL TAGIHAN -->
    <div class="px-4 py-4 border-b border-slate-100 bg-white flex items-center justify-between">
        <div>
            <span class="text-xs text-slate-500 block font-medium">Total Pembayaran</span>
            <span class="text-xl font-black text-sky-600">Rp {{ number_format($total ?? 0, 0, ',', '.') }}</span>
        </div>
        <div class="text-right">
            <span class="text-[11px] font-bold text-emerald-600 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded-full inline-block">
                Verifikasi Otomatis
            </span>
        </div>
    </div>

    <!-- 4. KARTU KODE PEMBAYARAN UTAMA (ALA SHOPEE) -->
    <div class="p-4 bg-slate-50 border-b border-slate-200/60">
        
        @if($channelCode === 'QRIS')
            <!-- TAMPILAN KHUSUS QRIS -->
            <div class="bg-white rounded-2xl border-2 border-sky-100 p-5 shadow-sm text-center">
                <div class="flex items-center justify-center gap-2 mb-2">
                    <span class="text-red-500 font-black text-lg">QRIS</span>
                    <span class="text-xs font-bold text-slate-500">• Pembayaran Cepat</span>
                </div>

                <!-- Gambar QR Code -->
                <div class="w-56 h-56 mx-auto my-3 p-3 bg-white border-2 border-slate-200 rounded-2xl shadow-inner flex items-center justify-center">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=RENTIFY-{{ $id }}-{{ $total }}" 
                         alt="QRIS Rentify" 
                         class="w-full h-full object-contain">
                </div>

                <div class="text-xs font-bold text-slate-700 mt-2">
                    Pindai QR dengan Aplikasi Pembayaran
                </div>
                <div class="flex items-center justify-center gap-1.5 flex-wrap mt-2">
                    <span class="px-2 py-0.5 rounded bg-blue-50 text-blue-600 text-[10px] font-extrabold border border-blue-100">DANA</span>
                    <span class="px-2 py-0.5 rounded bg-emerald-50 text-emerald-600 text-[10px] font-extrabold border border-emerald-100">GoPay</span>
                    <span class="px-2 py-0.5 rounded bg-amber-50 text-amber-700 text-[10px] font-extrabold border border-amber-100">ShopeePay</span>
                    <span class="px-2 py-0.5 rounded bg-purple-50 text-purple-700 text-[10px] font-extrabold border border-purple-100">OVO</span>
                    <span class="px-2 py-0.5 rounded bg-sky-50 text-sky-700 text-[10px] font-extrabold border border-sky-100">BCA Mobile</span>
                    <span class="px-2 py-0.5 rounded bg-blue-900 text-yellow-300 text-[10px] font-extrabold">Livin'</span>
                </div>
            </div>

        @elseif($channelCode === 'GOPAY' || $channelCode === 'SHOPEEPAY')
            <!-- TAMPILAN KHUSUS E-WALLET -->
            <div class="bg-white rounded-2xl border-2 border-sky-100 p-5 shadow-sm text-center">
                <div class="w-14 h-14 mx-auto rounded-2xl bg-sky-50 text-sky-600 flex items-center justify-center text-2xl mb-3">
                    <i class="fa-solid fa-wallet"></i>
                </div>
                <h3 class="text-base font-extrabold text-slate-800">{{ $bankName }}</h3>
                <p class="text-xs text-slate-500 mt-1 mb-4">Tekan tombol bayar di bawah untuk membuka aplikasi {{ $bankName }} secara otomatis.</p>
                <button type="button" onclick="bukaGateway()" class="w-full bg-sky-600 text-white font-black py-3 rounded-xl hover:bg-sky-700 transition">
                    Buka Aplikasi {{ $bankName }}
                </button>
            </div>

        @else
            <!-- TAMPILAN VIRTUAL ACCOUNT BANK (MANDIRI, BCA, BNI, BRI, PERMATA, LAINNYA) -->
            <div class="bg-white rounded-2xl border-2 border-sky-100 p-4 shadow-sm">
                
                <!-- Identitas Bank -->
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-xl flex items-center justify-center font-black text-xs text-white shadow-sm" style="background-color: {{ $bankColor }};">
                            {{ $bankLogo }}
                        </div>
                        <div>
                            <div class="text-[14px] font-extrabold text-slate-800">{{ $bankName }}</div>
                            <div class="text-[11px] text-slate-400 font-medium">Virtual Account Resmi</div>
                        </div>
                    </div>
                    <span class="text-[10px] font-bold text-sky-600 bg-sky-50 px-2 py-0.5 rounded-full border border-sky-100">
                        Otomatis
                    </span>
                </div>

                <!-- Bagian Nomor Virtual Account (Kode Pembayaran) -->
                <div class="pt-3 pb-2">
                    <span class="text-xs font-semibold text-slate-500 block mb-1">
                        No. Rekening / Virtual Account:
                    </span>
                    <div class="flex items-center justify-between bg-slate-50 border border-slate-200/80 rounded-xl px-3.5 py-2.5">
                        <span id="text-va" class="text-lg md:text-xl font-black text-slate-800 font-mono tracking-wider">
                            {{ $kodePembayaran }}
                        </span>
                        <button type="button" onclick="salinKodeVA()" class="bg-sky-50 hover:bg-sky-100 text-sky-600 border border-sky-200 text-xs font-extrabold px-3 py-1.5 rounded-lg active:scale-95 transition flex items-center gap-1.5">
                            <i class="fa-regular fa-copy"></i>
                            <span id="label-salin">SALIN</span>
                        </button>
                    </div>
                </div>

                <div class="text-[11px] text-slate-400 mt-2 flex items-center gap-1.5">
                    <i class="fa-solid fa-circle-info text-sky-500"></i>
                    <span>Transfer sesuai nominal tepat hingga digit terakhir.</span>
                </div>
            </div>
        @endif

    </div>

    <!-- 5. PETUNJUK CARA PEMBAYARAN (ACCORDION KHUSUS BANK INI) -->
    <div class="p-4">
        <h2 class="text-xs font-extrabold text-slate-400 uppercase tracking-wider mb-2.5">
            Petunjuk Pembayaran {{ $bankName }}
        </h2>

        <div class="bg-white border border-slate-200 rounded-xl divide-y divide-slate-100 overflow-hidden shadow-sm">
            
            @if(str_contains($channelCode, 'MANDIRI'))
                <!-- PETUNJUK MANDIRI LIVIN -->
                <div>
                    <div class="accordion-header p-3.5 flex items-center justify-between" onclick="togglePetunjuk('mandiri-livin')">
                        <div class="flex items-center gap-2 font-bold text-xs text-slate-800">
                            <i class="fa-solid fa-mobile-screen text-sky-600"></i>
                            <span>Livin' by Mandiri (Mobile Banking)</span>
                        </div>
                        <i class="fa-solid fa-chevron-down text-slate-400 text-xs transition-transform" id="chevron-mandiri-livin"></i>
                    </div>
                    <div id="panel-mandiri-livin" class="p-3.5 bg-slate-50/70 border-t border-slate-100 text-xs text-slate-600 space-y-2">
                        <div class="flex items-start gap-2">
                            <span class="step-badge">1</span>
                            <span>Buka aplikasi <b>Livin' by Mandiri</b> dan masuk dengan akun Anda.</span>
                        </div>
                        <div class="flex items-start gap-2">
                            <span class="step-badge">2</span>
                            <span>Pilih menu <b>Bayar</b> lalu pilih <b>Virtual Account</b>.</span>
                        </div>
                        <div class="flex items-start gap-2">
                            <span class="step-badge">3</span>
                            <span>Masukkan Nomor Virtual Account: <b class="font-mono text-sky-700 font-black">{{ $kodePembayaran }}</b>.</span>
                        </div>
                        <div class="flex items-start gap-2">
                            <span class="step-badge">4</span>
                            <span>Periksa detail tagihan <b>Rp {{ number_format($total ?? 0, 0, ',', '.') }}</b>, lalu tekan <b>Lanjut</b>.</span>
                        </div>
                        <div class="flex items-start gap-2">
                            <span class="step-badge">5</span>
                            <span>Masukkan <b>PIN Livin'</b> Anda untuk menyelesaikan pembayaran.</span>
                        </div>
                    </div>
                </div>

                <!-- PETUNJUK MANDIRI ATM -->
                <div>
                    <div class="accordion-header p-3.5 flex items-center justify-between" onclick="togglePetunjuk('mandiri-atm')">
                        <div class="flex items-center gap-2 font-bold text-xs text-slate-800">
                            <i class="fa-solid fa-building-columns text-sky-600"></i>
                            <span>ATM Bank Mandiri</span>
                        </div>
                        <i class="fa-solid fa-chevron-down text-slate-400 text-xs transition-transform" id="chevron-mandiri-atm"></i>
                    </div>
                    <div id="panel-mandiri-atm" class="hidden p-3.5 bg-slate-50/70 border-t border-slate-100 text-xs text-slate-600 space-y-2">
                        <div class="flex items-start gap-2">
                            <span class="step-badge">1</span>
                            <span>Masukkan kartu ATM Mandiri dan PIN Anda.</span>
                        </div>
                        <div class="flex items-start gap-2">
                            <span class="step-badge">2</span>
                            <span>Pilih menu <b>Bayar / Beli</b> &gt; <b>Lainnya</b> &gt; <b>Virtual Account</b>.</span>
                        </div>
                        <div class="flex items-start gap-2">
                            <span class="step-badge">3</span>
                            <span>Masukkan kode <b>{{ $kodePembayaran }}</b> lalu tekan Benar.</span>
                        </div>
                        <div class="flex items-start gap-2">
                            <span class="step-badge">4</span>
                            <span>Konfirmasi detail pembayaran dan simpan struk transaksi.</span>
                        </div>
                    </div>
                </div>

            @elseif(str_contains($channelCode, 'BCA'))
                <!-- PETUNJUK BCA MOBILE -->
                <div>
                    <div class="accordion-header p-3.5 flex items-center justify-between" onclick="togglePetunjuk('bca-m')">
                        <div class="flex items-center gap-2 font-bold text-xs text-slate-800">
                            <i class="fa-solid fa-mobile-screen text-sky-600"></i>
                            <span>m-BCA (BCA mobile)</span>
                        </div>
                        <i class="fa-solid fa-chevron-down text-slate-400 text-xs transition-transform" id="chevron-bca-m"></i>
                    </div>
                    <div id="panel-bca-m" class="p-3.5 bg-slate-50/70 border-t border-slate-100 text-xs text-slate-600 space-y-2">
                        <div class="flex items-start gap-2">
                            <span class="step-badge">1</span>
                            <span>Buka aplikasi <b>BCA mobile</b> dan pilih menu <b>m-BCA</b>.</span>
                        </div>
                        <div class="flex items-start gap-2">
                            <span class="step-badge">2</span>
                            <span>Pilih menu <b>m-Transfer</b> &gt; <b>BCA Virtual Account</b>.</span>
                        </div>
                        <div class="flex items-start gap-2">
                            <span class="step-badge">3</span>
                            <span>Masukkan Nomor Virtual Account: <b class="font-mono text-sky-700 font-black">{{ $kodePembayaran }}</b>.</span>
                        </div>
                        <div class="flex items-start gap-2">
                            <span class="step-badge">4</span>
                            <span>Periksa konfirmasi tagihan, lalu masukkan <b>PIN m-BCA</b> Anda.</span>
                        </div>
                    </div>
                </div>

                <!-- PETUNJUK ATM BCA -->
                <div>
                    <div class="accordion-header p-3.5 flex items-center justify-between" onclick="togglePetunjuk('bca-atm')">
                        <div class="flex items-center gap-2 font-bold text-xs text-slate-800">
                            <i class="fa-solid fa-building-columns text-sky-600"></i>
                            <span>ATM BCA</span>
                        </div>
                        <i class="fa-solid fa-chevron-down text-slate-400 text-xs transition-transform" id="chevron-bca-atm"></i>
                    </div>
                    <div id="panel-bca-atm" class="hidden p-3.5 bg-slate-50/70 border-t border-slate-100 text-xs text-slate-600 space-y-2">
                        <div class="flex items-start gap-2"><span class="step-badge">1</span><span>Masukkan kartu ATM BCA dan PIN.</span></div>
                        <div class="flex items-start gap-2"><span class="step-badge">2</span><span>Pilih <b>Transaksi Lainnya</b> &gt; <b>Transfer</b> &gt; <b>ke Rek BCA Virtual Account</b>.</span></div>
                        <div class="flex items-start gap-2"><span class="step-badge">3</span><span>Ketik nomor <b>{{ $kodePembayaran }}</b> dan tekan Benar.</span></div>
                    </div>
                </div>

            @elseif(str_contains($channelCode, 'BRI'))
                <!-- PETUNJUK BRIMO -->
                <div>
                    <div class="accordion-header p-3.5 flex items-center justify-between" onclick="togglePetunjuk('bri-mo')">
                        <div class="flex items-center gap-2 font-bold text-xs text-slate-800">
                            <i class="fa-solid fa-mobile-screen text-sky-600"></i>
                            <span>BRImo (BRI Mobile)</span>
                        </div>
                        <i class="fa-solid fa-chevron-down text-slate-400 text-xs transition-transform" id="chevron-bri-mo"></i>
                    </div>
                    <div id="panel-bri-mo" class="p-3.5 bg-slate-50/70 border-t border-slate-100 text-xs text-slate-600 space-y-2">
                        <div class="flex items-start gap-2"><span class="step-badge">1</span><span>Buka aplikasi <b>BRImo</b> dan pilih menu <b>Tagihan</b> &gt; <b>BRIVA</b>.</span></div>
                        <div class="flex items-start gap-2"><span class="step-badge">2</span><span>Masukkan Nomor BRIVA: <b class="font-mono text-sky-700 font-black">{{ $kodePembayaran }}</b>.</span></div>
                        <div class="flex items-start gap-2"><span class="step-badge">3</span><span>Konfirmasi tagihan lalu masukkan <b>PIN BRImo</b> Anda.</span></div>
                    </div>
                </div>

            @elseif(str_contains($channelCode, 'BNI'))
                <!-- PETUNJUK BNI MOBILE -->
                <div>
                    <div class="accordion-header p-3.5 flex items-center justify-between" onclick="togglePetunjuk('bni-m')">
                        <div class="flex items-center gap-2 font-bold text-xs text-slate-800">
                            <i class="fa-solid fa-mobile-screen text-sky-600"></i>
                            <span>BNI Mobile Banking</span>
                        </div>
                        <i class="fa-solid fa-chevron-down text-slate-400 text-xs transition-transform" id="chevron-bni-m"></i>
                    </div>
                    <div id="panel-bni-m" class="p-3.5 bg-slate-50/70 border-t border-slate-100 text-xs text-slate-600 space-y-2">
                        <div class="flex items-start gap-2"><span class="step-badge">1</span><span>Buka <b>BNI Mobile Banking</b>, pilih menu <b>Pembayaran</b> &gt; <b>Virtual Account Billing</b>.</span></div>
                        <div class="flex items-start gap-2"><span class="step-badge">2</span><span>Masukkan Nomor VA: <b class="font-mono text-sky-700 font-black">{{ $kodePembayaran }}</b>.</span></div>
                        <div class="flex items-start gap-2"><span class="step-badge">3</span><span>Masukkan <b>Password Transaksi</b> Anda.</span></div>
                    </div>
                </div>

            @else
                <!-- PETUNJUK UMUM / BANK LAINNYA -->
                <div>
                    <div class="accordion-header p-3.5 flex items-center justify-between" onclick="togglePetunjuk('gen-m')">
                        <div class="flex items-center gap-2 font-bold text-xs text-slate-800">
                            <i class="fa-solid fa-mobile-screen text-sky-600"></i>
                            <span>Mobile Banking (Semua Bank)</span>
                        </div>
                        <i class="fa-solid fa-chevron-down text-slate-400 text-xs transition-transform" id="chevron-gen-m"></i>
                    </div>
                    <div id="panel-gen-m" class="p-3.5 bg-slate-50/70 border-t border-slate-100 text-xs text-slate-600 space-y-2">
                        <div class="flex items-start gap-2"><span class="step-badge">1</span><span>Buka aplikasi m-Banking dari bank apa saja.</span></div>
                        <div class="flex items-start gap-2"><span class="step-badge">2</span><span>Pilih menu Transfer Antar Bank / Virtual Account.</span></div>
                        <div class="flex items-start gap-2"><span class="step-badge">3</span><span>Ketik nomor: <b class="font-mono text-sky-700 font-black">{{ $kodePembayaran }}</b>.</span></div>
                        <div class="flex items-start gap-2"><span class="step-badge">4</span><span>Konfirmasi pembayaran sesuai nominal Rp {{ number_format($total ?? 0, 0, ',', '.') }}.</span></div>
                    </div>
                </div>
            @endif

        </div>

        <!-- Kartu Live Status Feedback -->
        <div id="status-card" class="hidden p-3.5 rounded-xl text-xs font-bold mt-4 transition-all"></div>
    </div>

    <!-- 6. FIXED BOTTOM ACTION BAR (ALA SHOPEE) -->
    <div class="fixed bottom-0 left-0 w-full bg-white border-t border-slate-200 px-4 py-3 z-50 shadow-[0_-4px_20px_rgba(0,0,0,0.06)]">
        <div class="max-w-[500px] mx-auto flex items-center gap-2.5">
            <button type="button" onclick="bukaGateway()" class="flex-1 bg-gradient-to-r from-sky-500 to-sky-600 hover:from-sky-600 hover:to-sky-700 text-white font-black text-[14px] py-3.5 px-4 rounded-xl shadow-md transition-all active:scale-95 flex items-center justify-center gap-2">
                <i class="fa-solid fa-credit-card text-xs"></i>
                <span>Bayar Lewat Gateway</span>
            </button>
            
            <button type="button" onclick="checkStatusLive()" class="bg-slate-100 hover:bg-slate-200 text-slate-700 font-extrabold text-[13px] py-3.5 px-4 rounded-xl transition-all active:scale-95 flex items-center justify-center gap-1.5" title="Cek Status Pembayaran">
                <i class="fa-solid fa-rotate text-sky-600"></i>
                <span>Cek Status</span>
            </button>
        </div>
    </div>

</div>

<script>
    const snapToken = "{{ $snapToken ?? '' }}";
    const invoiceId = "{{ $id }}";
    const checkStatusUrl = "{{ route('customer.pembayaran.check_status', $id) }}";
    const receiptUrl = "{{ route('customer.struk', $id) }}";

    // Fungsi Salin Kode Virtual Account
    function salinKodeVA() {
        const vaEl = document.getElementById('text-va');
        if (!vaEl) return;
        const text = vaEl.innerText.trim();
        
        navigator.clipboard.writeText(text).then(function() {
            const label = document.getElementById('label-salin');
            label.innerText = 'TERSLIN! ✓';
            setTimeout(function() { label.innerText = 'SALIN'; }, 2000);
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

    // Countdown 24 Jam Live
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

    // Buka Gateway (Snap Midtrans) yang sudah terkunci ke bank ini
    function bukaGateway() {
        if (!snapToken) {
            alert("Sesi pembayaran sedang disiapkan. Silakan refresh halaman.");
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
                checkStatusLive();
            },
            onError: function (result) {
                showMessage('error', 'Pembayaran dibatalkan atau terjadi kendala.');
            },
            onClose: function () {
                checkStatusLive();
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
            statusCard.classList.add('bg-amber-50', 'text-amber-700', 'border', 'border-amber-200');
            statusCard.innerHTML = '<i class="fa-solid fa-clock text-amber-500 mr-1.5"></i> ' + text;
        } else {
            statusCard.classList.add('bg-rose-50', 'text-rose-700', 'border', 'border-rose-200');
            statusCard.innerHTML = '<i class="fa-solid fa-circle-xmark text-rose-500 mr-1.5"></i> ' + text;
        }
    }

    // Live status verification
    function checkStatusLive() {
        fetch(checkStatusUrl, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            if (data.paid) {
                showMessage('success', '🎉 Pembayaran lunas & terverifikasi! Mengalihkan ke struk...');
                setTimeout(function() { window.location.href = data.redirect_url || receiptUrl; }, 1200);
            }
        })
        .catch(function(e) { console.error(e); });
    }

    // Polling background tiap 4 detik
    setInterval(checkStatusLive, 4000);
</script>

@endsection