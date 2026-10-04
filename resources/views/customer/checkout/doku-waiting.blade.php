@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
    /* Sembunyikan navbar dan footer bawaan agar tampilan full-screen ala Shopee */
    nav, header, footer, .navbar, .header, .top-bar, .footer, #footer { display: none !important; }
    body {
        background: #f1f5f9;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
        padding-bottom: 100px;
        margin: 0;
    }
    .shopee-container { max-width: 500px; margin: 0 auto; background: white; min-height: 100vh; box-shadow: 0 0 20px rgba(0,0,0,0.05); }

    /* Countdown Ring */
    .countdown-ring { position: relative; width: 120px; height: 120px; }
    .countdown-ring svg { transform: rotate(-90deg); }
    .countdown-ring .ring-bg { fill: none; stroke: #e2e8f0; stroke-width: 8; }
    .countdown-ring .ring-progress { fill: none; stroke: #f59e0b; stroke-width: 8; stroke-linecap: round; transition: stroke-dashoffset 1s linear; }
    .countdown-center { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); text-align: center; }

    /* Pulse animation untuk status menunggu */
    @keyframes pulse-ring {
        0% { box-shadow: 0 0 0 0 rgba(245, 158, 11, 0.4); }
        70% { box-shadow: 0 0 0 12px rgba(245, 158, 11, 0); }
        100% { box-shadow: 0 0 0 0 rgba(245, 158, 11, 0); }
    }
    .pulse-badge { animation: pulse-ring 2s infinite; }

    /* Animasi sukses */
    @keyframes success-bounce {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.05); }
    }
    .success-animate { animation: success-bounce 0.6s ease 3; }

    /* Spinner loading */
    @keyframes spin { to { transform: rotate(360deg); } }
    .spinner { animation: spin 1s linear infinite; }
</style>

@php
    $expiredAtJs   = $expiredAt ? $expiredAt->timestamp * 1000 : (now()->addMinutes(30)->timestamp * 1000);
    $totalDurasiMs = 30 * 60 * 1000; // 30 menit default
    if ($expiredAt) {
        $createdAt      = \Carbon\Carbon::parse($firstOrder->created_at ?? now());
        $totalDurasiMs  = max($totalDurasiMs, (int)(($expiredAt->timestamp - $createdAt->timestamp) * 1000));
    }
    $checkStatusUrl = route('customer.pembayaran.check_status', $id);
    $payUrl         = route('customer.pembayaran.pay', $id);
    $struktUrl      = route('customer.struk', $id);
    $successUrl     = route('customer.doku.success', $id);
@endphp

<div class="shopee-container relative">

    {{-- ─── HEADER ──────────────────────────────────────────────────────── --}}
    <div class="sticky top-0 z-40 bg-white border-b border-slate-100 px-4 py-3.5 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ route('customer.pesanan') }}" class="text-slate-700 hover:text-sky-600 transition text-lg flex items-center justify-center p-1">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
            <div>
                <h1 class="text-[16px] font-bold text-slate-800 leading-tight">Selesaikan Pembayaran</h1>
                <span class="text-[11px] text-slate-400 font-medium">No. Pesanan: {{ $id }}</span>
            </div>
        </div>
        <div class="text-[11px] font-bold text-emerald-600 bg-emerald-50 px-2.5 py-1 rounded-full border border-emerald-100 flex items-center gap-1">
            <i class="fa-solid fa-shield-halved text-[10px]"></i> Aman
        </div>
    </div>

    {{-- ─── MAIN STATUS SECTION ──────────────────────────────────────────── --}}
    <div id="section-pending">

        {{-- Countdown Ring --}}
        <div class="px-4 py-8 flex flex-col items-center text-center border-b border-slate-100">

            <div class="countdown-ring mb-4 pulse-badge rounded-full">
                <svg viewBox="0 0 120 120" width="120" height="120">
                    <circle class="ring-bg" cx="60" cy="60" r="52"/>
                    <circle id="ring-progress" class="ring-progress" cx="60" cy="60" r="52"
                        stroke-dasharray="326.7"
                        stroke-dashoffset="0"/>
                </svg>
                <div class="countdown-center">
                    <div id="countdown-timer" class="font-mono font-black text-amber-600 text-xl leading-none">30:00</div>
                    <div class="text-[10px] text-slate-400 mt-0.5 font-semibold">Tersisa</div>
                </div>
            </div>

            <h2 class="text-base font-black text-slate-800 mb-1">Menunggu Pembayaran</h2>
            <p class="text-xs text-slate-500 max-w-xs">
                Tekan tombol di bawah untuk melanjutkan ke halaman pembayaran resmi DOKU.
                Pembayaran akan dikonfirmasi otomatis setelah selesai.
            </p>

            <div class="mt-4 bg-amber-50 border border-amber-200 rounded-xl px-4 py-3 w-full max-w-sm">
                <div class="text-xs text-amber-700 font-semibold flex items-center gap-2">
                    <i class="fa-solid fa-clock"></i>
                    Bayar sebelum waktu habis agar unit sewa tidak dilepaskan.
                </div>
            </div>
        </div>

        {{-- Ringkasan tagihan --}}
        <div class="px-4 py-4 border-b border-slate-100">
            <div class="bg-slate-50 rounded-xl p-4">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-extrabold text-slate-500 uppercase tracking-wider">Total Tagihan</span>
                    <span class="text-lg font-black text-sky-600">Rp {{ number_format($total ?? 0, 0, ',', '.') }}</span>
                </div>
                @foreach($orders as $order)
                <div class="flex items-center justify-between text-xs text-slate-600 py-1.5 border-t border-slate-200/60">
                    <span class="font-medium truncate pr-2">{{ $order->kode_booking }}</span>
                    <span class="font-bold text-slate-700 shrink-0">Rp {{ number_format($order->total_biaya ?? $order->total_price ?? 0, 0, ',', '.') }}</span>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Petunjuk --}}
        <div class="px-4 py-4 border-b border-slate-100">
            <h3 class="text-xs font-extrabold text-slate-400 uppercase tracking-wider mb-3">Cara Pembayaran</h3>
            <div class="space-y-3">
                <div class="flex items-start gap-3">
                    <div class="w-6 h-6 rounded-full bg-sky-100 text-sky-600 flex items-center justify-center text-[11px] font-black shrink-0">1</div>
                    <p class="text-xs text-slate-600">Tekan <b>"Bayar di DOKU"</b> — Anda akan diarahkan ke halaman resmi DOKU.</p>
                </div>
                <div class="flex items-start gap-3">
                    <div class="w-6 h-6 rounded-full bg-sky-100 text-sky-600 flex items-center justify-center text-[11px] font-black shrink-0">2</div>
                    <p class="text-xs text-slate-600">Pilih metode pembayaran (QRIS, Virtual Account Bank, dll) lalu selesaikan pembayaran.</p>
                </div>
                <div class="flex items-start gap-3">
                    <div class="w-6 h-6 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-[11px] font-black shrink-0">3</div>
                    <p class="text-xs text-slate-600">Setelah berhasil, halaman ini akan <b>otomatis terupdate</b> dalam beberapa detik tanpa perlu refresh.</p>
                </div>
            </div>
        </div>

        {{-- Live status feedback --}}
        <div class="px-4 py-2">
            <div id="status-card" class="hidden p-3.5 rounded-xl text-xs font-bold transition-all"></div>
            <div id="polling-indicator" class="flex items-center gap-2 text-xs text-slate-400 mt-2">
                <i class="fa-solid fa-circle-notch spinner text-sky-400"></i>
                <span>Memeriksa status pembayaran secara otomatis...</span>
            </div>
        </div>

    </div>{{-- /section-pending --}}

    {{-- ─── SECTION SUKSES (hidden awalnya, muncul saat PAID) ─────────────── --}}
    <div id="section-success" class="hidden px-4 py-10 flex flex-col items-center text-center">
        <div class="w-24 h-24 rounded-full bg-emerald-100 flex items-center justify-center mb-5 success-animate">
            <i class="fa-solid fa-circle-check text-emerald-500 text-5xl"></i>
        </div>
        <h2 class="text-xl font-black text-slate-800 mb-2">Pembayaran Berhasil! 🎉</h2>
        <p class="text-sm text-slate-500 mb-6">Pesanan Anda telah terkonfirmasi. Vendor akan segera memproses penyewaan.</p>
        <a id="btn-lihat-pesanan" href="{{ $successUrl }}"
           class="w-full max-w-xs bg-emerald-500 hover:bg-emerald-600 text-white font-black py-3.5 rounded-xl shadow-md transition active:scale-95 flex items-center justify-center gap-2">
            <i class="fa-solid fa-bag-shopping"></i>
            Lihat Detail Pesanan
        </a>
    </div>

    {{-- ─── SECTION EXPIRED ─────────────────────────────────────────────── --}}
    <div id="section-expired" class="hidden px-4 py-10 flex flex-col items-center text-center">
        <div class="w-20 h-20 rounded-full bg-rose-100 flex items-center justify-center mb-4">
            <i class="fa-solid fa-clock text-rose-400 text-4xl"></i>
        </div>
        <h2 class="text-lg font-black text-slate-800 mb-2">Waktu Pembayaran Habis</h2>
        <p class="text-xs text-slate-500 mb-6 max-w-xs">
            Batas waktu pembayaran telah habis. Ketersediaan unit sewa telah dilepaskan.
        </p>
        <a href="{{ route('customer.home') }}"
           class="w-full max-w-xs bg-sky-500 hover:bg-sky-600 text-white font-black py-3.5 rounded-xl shadow-md transition active:scale-95 flex items-center justify-center gap-2">
            <i class="fa-solid fa-house"></i>
            Cari Barang Lain
        </a>
    </div>

    {{-- ─── FIXED BOTTOM BAR ─────────────────────────────────────────────── --}}
    <div id="bottom-bar" class="fixed bottom-0 left-0 w-full bg-white border-t border-slate-200 px-4 py-3 z-50 shadow-[0_-4px_20px_rgba(0,0,0,0.06)]">
        <div class="max-w-[500px] mx-auto flex items-center gap-2.5">
            <a href="{{ $payUrl }}"
               class="flex-1 bg-gradient-to-r from-sky-500 to-blue-600 hover:from-sky-600 hover:to-blue-700 text-white font-black text-[14px] py-3.5 px-4 rounded-xl shadow-md transition-all active:scale-95 flex items-center justify-center gap-2">
                <i class="fa-solid fa-credit-card text-xs"></i>
                <span>Bayar di DOKU</span>
            </a>
            <button type="button" onclick="checkStatusManual()"
                class="bg-slate-100 hover:bg-slate-200 text-slate-700 font-extrabold text-[13px] py-3.5 px-4 rounded-xl transition-all active:scale-95 flex items-center justify-center gap-1.5"
                title="Cek Status Sekarang">
                <i class="fa-solid fa-rotate text-sky-600"></i>
                <span>Cek</span>
            </button>
        </div>
    </div>

</div>

<script>
    // ── Konfigurasi ──────────────────────────────────────────────────────────
    const checkStatusUrl  = "{{ $checkStatusUrl }}";
    const successUrl      = "{{ $successUrl }}";
    const expiredAtMs     = {{ $expiredAtJs }};
    const circumference   = 326.7; // 2 * π * 52

    let pollingInterval   = null;
    let countdownInterval = null;
    let isPaidOrExpired   = false;

    // ── Countdown Timer ──────────────────────────────────────────────────────
    function updateCountdown() {
        const now       = Date.now();
        const remaining = Math.max(0, expiredAtMs - now);
        const totalMs   = {{ $totalDurasiMs }};

        if (remaining <= 0) {
            clearInterval(countdownInterval);
            if (!isPaidOrExpired) {
                showExpired();
            }
            return;
        }

        const minutes = Math.floor(remaining / 60000);
        const seconds = Math.floor((remaining % 60000) / 1000);
        document.getElementById('countdown-timer').innerText =
            String(minutes).padStart(2, '0') + ':' + String(seconds).padStart(2, '0');

        // Ring progress
        const fraction       = remaining / totalMs;
        const offset         = circumference * (1 - fraction);
        const ringEl         = document.getElementById('ring-progress');
        if (ringEl) ringEl.style.strokeDashoffset = offset;

        // Warna ring berdasarkan sisa waktu
        if (fraction < 0.25) {
            ringEl && ringEl.setAttribute('stroke', '#ef4444');
        } else if (fraction < 0.5) {
            ringEl && ringEl.setAttribute('stroke', '#f59e0b');
        } else {
            ringEl && ringEl.setAttribute('stroke', '#10b981');
        }
    }

    // ── Polling Status (auto setiap 4 detik) ────────────────────────────────
    function checkStatusAuto() {
        if (isPaidOrExpired) return;

        fetch(checkStatusUrl, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(data => {
            if (data.paid || data.status === 'PAID') {
                isPaidOrExpired = true;
                showSuccess(data.redirect_url || successUrl);
            } else if (data.status === 'EXPIRED' || data.status === 'FAILED' || data.status === 'CANCELLED') {
                isPaidOrExpired = true;
                showExpired();
            }
        })
        .catch(err => console.warn('[DOKU Polling] Error:', err));
    }

    function checkStatusManual() {
        showStatus('pending', 'Memeriksa status pembayaran...');
        fetch(checkStatusUrl, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(data => {
            if (data.paid || data.status === 'PAID') {
                isPaidOrExpired = true;
                showSuccess(data.redirect_url || successUrl);
            } else if (data.status === 'EXPIRED') {
                isPaidOrExpired = true;
                showExpired();
            } else {
                showStatus('pending', '⏳ Pembayaran belum diterima. Tekan "Bayar di DOKU" untuk melanjutkan.');
            }
        })
        .catch(() => showStatus('error', 'Gagal memeriksa status. Coba lagi.'));
    }

    // ── UI Transitions ───────────────────────────────────────────────────────
    function showSuccess(redirectUrl) {
        clearInterval(pollingInterval);
        clearInterval(countdownInterval);

        document.getElementById('section-pending').classList.add('hidden');
        document.getElementById('bottom-bar').classList.add('hidden');
        document.getElementById('section-success').classList.remove('hidden');

        const btnEl = document.getElementById('btn-lihat-pesanan');
        if (btnEl && redirectUrl) btnEl.href = redirectUrl;

        setTimeout(() => { window.location.href = redirectUrl || successUrl; }, 3000);
    }

    function showExpired() {
        clearInterval(pollingInterval);
        clearInterval(countdownInterval);

        document.getElementById('section-pending').classList.add('hidden');
        document.getElementById('bottom-bar').classList.add('hidden');
        document.getElementById('section-expired').classList.remove('hidden');
        document.getElementById('countdown-timer').innerText = '00:00';
    }

    function showStatus(type, text) {
        const card = document.getElementById('status-card');
        if (!card) return;
        card.classList.remove('hidden', 'bg-emerald-50', 'text-emerald-700', 'border-emerald-200',
                              'bg-amber-50', 'text-amber-700', 'border-amber-200',
                              'bg-rose-50', 'text-rose-700', 'border-rose-200', 'border');
        if (type === 'success') {
            card.classList.add('bg-emerald-50', 'text-emerald-700', 'border', 'border-emerald-200');
            card.innerHTML = '<i class="fa-solid fa-circle-check mr-1.5"></i> ' + text;
        } else if (type === 'pending') {
            card.classList.add('bg-amber-50', 'text-amber-700', 'border', 'border-amber-200');
            card.innerHTML = '<i class="fa-solid fa-clock mr-1.5"></i> ' + text;
        } else {
            card.classList.add('bg-rose-50', 'text-rose-700', 'border', 'border-rose-200');
            card.innerHTML = '<i class="fa-solid fa-circle-xmark mr-1.5"></i> ' + text;
        }
    }

    // ── Boot ─────────────────────────────────────────────────────────────────
    document.addEventListener('DOMContentLoaded', function () {
        updateCountdown();
        countdownInterval = setInterval(updateCountdown, 1000);

        // Polling otomatis setiap 4 detik
        pollingInterval = setInterval(checkStatusAuto, 4000);

        // Cek sekali langsung saat halaman dimuat
        checkStatusAuto();
    });
</script>

@endsection
