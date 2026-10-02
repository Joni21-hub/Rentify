@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
    /* Sembunyikan navbar dan footer default */
    nav, header, footer, .navbar, .header, .top-bar, .footer, #footer { display: none !important; }
    body { background-color: #f8fafc; font-family: 'Segoe UI', Tahoma, sans-serif; min-height: 100vh; padding: 20px 12px 50px 12px; }
    .payment-container { max-width: 480px; margin: 0 auto; }
</style>

@php
    $isProd = config('midtrans.is_production', true);
    $snapJsUrl = $isProd 
        ? 'https://app.midtrans.com/snap/snap.js' 
        : 'https://app.sandbox.midtrans.com/snap/snap.js';
    $clientKey = config('midtrans.client_key');
@endphp

<!-- Midtrans Snap JS -->
<script src="{{ $snapJsUrl }}" data-client-key="{{ $clientKey }}"></script>

<div class="payment-container">
    
    <!-- Top Navigation -->
    <div class="flex items-center justify-between mb-4 px-1">
        <a href="{{ route('customer.pesanan') }}" class="w-9 h-9 rounded-full bg-white border border-slate-200 text-slate-600 flex items-center justify-center hover:bg-slate-50 transition shadow-sm">
            <i class="fa-solid fa-arrow-left text-sm"></i>
        </a>
        <div class="text-xs font-bold text-slate-500 flex items-center gap-1.5 bg-sky-50 px-3 py-1 rounded-full border border-sky-100">
            <i class="fa-solid fa-shield-halved text-sky-500"></i>
            <span>Midtrans Payment Gateway</span>
        </div>
        <div class="w-9"></div> <!-- spacer -->
    </div>

    <!-- Main Payment Card -->
    <div class="bg-white rounded-3xl border border-slate-200/90 shadow-sm p-6 relative overflow-hidden text-center">
        <!-- Top Gradient Accent -->
        <div class="absolute top-0 left-0 right-0 h-1.5 bg-gradient-to-r from-sky-400 to-blue-600"></div>

        <div class="text-2xl font-black text-sky-600 tracking-tight mb-1">
            Renti<span class="text-sky-400">fy</span>
        </div>

        <div class="inline-flex items-center gap-1.5 px-3 py-1 bg-sky-50 text-sky-700 rounded-full text-xs font-bold border border-sky-100 mb-4">
            <span>No. Pesanan: <strong>{{ $id }}</strong></span>
        </div>

        <p class="text-xs font-semibold text-slate-500 mb-1">Total Tagihan Pembayaran:</p>
        <div class="text-3xl font-black text-slate-900 mb-4">
            Rp {{ number_format($total ?? 0, 0, ',', '.') }}
        </div>

        <!-- Banner Kanal Pembayaran Otomatis -->
        <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200/80 mb-5 text-left">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] font-black uppercase text-slate-500 tracking-wider">Metode Tersedia (Midtrans)</span>
                <span class="text-[10px] font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-100">
                    <i class="fa-solid fa-bolt mr-1"></i> Instan
                </span>
            </div>
            
            <div class="grid grid-cols-2 gap-2 text-xs">
                <div class="flex items-center gap-2 p-2 bg-white rounded-xl border border-slate-100 font-semibold text-slate-700">
                    <i class="fa-solid fa-qrcode text-sky-500 text-sm"></i>
                    <span>QRIS (GoPay/DANA/BCA)</span>
                </div>
                <div class="flex items-center gap-2 p-2 bg-white rounded-xl border border-slate-100 font-semibold text-slate-700">
                    <i class="fa-solid fa-building-columns text-sky-500 text-sm"></i>
                    <span>Virtual Account Bank</span>
                </div>
                <div class="flex items-center gap-2 p-2 bg-white rounded-xl border border-slate-100 font-semibold text-slate-700">
                    <i class="fa-solid fa-credit-card text-sky-500 text-sm"></i>
                    <span>Kartu Kredit / Debit</span>
                </div>
                <div class="flex items-center gap-2 p-2 bg-white rounded-xl border border-slate-100 font-semibold text-slate-700">
                    <i class="fa-solid fa-wallet text-sky-500 text-sm"></i>
                    <span>ShopeePay / E-Wallet</span>
                </div>
            </div>
        </div>

        <!-- Error State jika Snap Token gagal dibuat -->
        @if(empty($snapToken))
            <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-700 text-xs mb-4 text-left">
                <div class="font-bold flex items-center gap-1.5 mb-1 text-rose-800">
                    <i class="fa-solid fa-circle-exclamation text-rose-500"></i>
                    <span>Kendala Memuat Sesi Pembayaran</span>
                </div>
                <p>{{ $snapError ?? 'Token pembayaran gagal dibuat. Silakan klik tombol di bawah untuk mencoba kembali.' }}</p>
            </div>

            <button type="button" onclick="location.reload()" class="w-full bg-sky-500 hover:bg-sky-600 text-white font-bold py-3 px-4 rounded-xl text-sm transition">
                <i class="fa-solid fa-rotate-right mr-1.5"></i> Coba Muat Ulang
            </button>
        @else
            <!-- Success / Pending Animated Status Card -->
            <div id="status-card" class="hidden p-4 rounded-2xl mb-4 text-xs font-bold transition-all"></div>

            <!-- Tombol Utama Buka Midtrans Snap Popup -->
            <button type="button" id="btn-pay-snap" class="w-full bg-gradient-to-r from-sky-400 to-sky-600 hover:from-sky-500 hover:to-sky-700 text-white font-black text-sm py-3.5 px-4 rounded-2xl shadow-[0_4px_15px_rgba(14,165,233,0.35)] transition-all flex items-center justify-center gap-2 mb-2.5 transform active:scale-95">
                <i class="fa-solid fa-lock text-white"></i>
                <span>Bayar Sekarang (Buka Midtrans)</span>
            </button>

            <!-- Tombol Cek Status Pembayaran Manual -->
            <button type="button" id="btn-check-status" class="w-full bg-slate-50 hover:bg-slate-100 text-slate-700 border border-slate-200 font-bold text-xs py-3 px-4 rounded-2xl transition flex items-center justify-center gap-2">
                <i class="fa-solid fa-rotate text-sky-500" id="spinner-icon"></i>
                <span>Cek Status Pembayaran</span>
            </button>

            <!-- Status Indicator Live Polling -->
            <div class="mt-4 flex items-center justify-center gap-2 text-[11px] text-slate-400 font-medium" id="polling-indicator">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <span>Pemeriksaan otomatis status pembayaran aktif...</span>
            </div>

            <!-- Timer Countdown 15 Menit -->
            <div class="mt-3 text-[11px] text-amber-600 font-semibold flex items-center justify-center gap-1.5">
                <i class="fa-regular fa-clock"></i>
                <span>Selesaikan pembayaran dalam <strong id="countdown-timer">15:00</strong> menit</span>
            </div>
        @endif

        <div class="mt-6 pt-4 border-t border-slate-100 text-[10px] text-slate-400 space-y-1">
            <p>🔒 Pembayaran diproses dengan enkripsi keamanan standar bank oleh Midtrans.</p>
            <p>Setelah pembayaran Anda selesai, sistem akan otomatis mengarahkan ke Struk Transaksi.</p>
        </div>
    </div>
</div>

@if(!empty($snapToken))
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const snapToken = "{{ $snapToken }}";
        const invoiceId = "{{ $id }}";
        const checkStatusUrl = "{{ route('customer.pembayaran.check_status', $id) }}";
        const receiptUrl = "{{ route('customer.struk', $id) }}";

        const btnPaySnap = document.getElementById('btn-pay-snap');
        const btnCheckStatus = document.getElementById('btn-check-status');
        const spinnerIcon = document.getElementById('spinner-icon');
        const statusCard = document.getElementById('status-card');

        let isChecking = false;
        let pollingInterval = null;

        function showMessage(type, text) {
            statusCard.classList.remove('hidden', 'bg-emerald-50', 'text-emerald-700', 'border-emerald-200', 'bg-amber-50', 'text-amber-700', 'border-amber-200', 'bg-rose-50', 'text-rose-700', 'border-rose-200');
            
            if (type === 'success') {
                statusCard.classList.add('bg-emerald-50', 'text-emerald-700', 'border', 'border-emerald-200');
                statusCard.innerHTML = '<i class="fa-solid fa-circle-check text-emerald-500 text-sm mr-1.5"></i> ' + text;
            } else if (type === 'pending') {
                statusCard.classList.add('bg-amber-50', 'text-amber-700', 'border', 'border-amber-200');
                statusCard.innerHTML = '<i class="fa-solid fa-clock text-amber-500 text-sm mr-1.5"></i> ' + text;
            } else {
                statusCard.classList.add('bg-rose-50', 'text-rose-700', 'border', 'border-rose-200');
                statusCard.innerHTML = '<i class="fa-solid fa-circle-xmark text-rose-500 text-sm mr-1.5"></i> ' + text;
            }
        }

        // Fungsi Buka Midtrans Snap Popup
        function launchSnap() {
            if (typeof window.snap === 'undefined') {
                console.warn("Midtrans Snap belum siap.");
                return;
            }

            snap.pay(snapToken, {
                onSuccess: function (result) {
                    console.log("Snap Success:", result);
                    showMessage('success', 'Pembayaran berhasil! Mengalihkan ke struk pesanan...');
                    clearInterval(pollingInterval);
                    setTimeout(function() {
                        window.location.href = receiptUrl;
                    }, 1500);
                },
                onPending: function (result) {
                    console.log("Snap Pending:", result);
                    showMessage('pending', 'Menunggu penyelesaian pembayaran di m-banking / e-wallet...');
                    checkStatusLive();
                },
                onError: function (result) {
                    console.error("Snap Error:", result);
                    showMessage('error', 'Pembayaran gagal. Silakan klik Bayar Sekarang untuk mencoba lagi.');
                },
                onClose: function () {
                    console.log("Customer menutup popup Snap.");
                    checkStatusLive();
                }
            });
        }

        // Live status verification via API
        function checkStatusLive(isManual = false) {
            if (isChecking) return;
            isChecking = true;

            if (isManual) {
                spinnerIcon.classList.add('fa-spin');
            }

            fetch(checkStatusUrl, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => res.json())
            .then(data => {
                isChecking = false;
                if (isManual) spinnerIcon.classList.remove('fa-spin');

                if (data.paid) {
                    clearInterval(pollingInterval);
                    showMessage('success', '🎉 Pembayaran lunas & terverifikasi! Mengalihkan...');
                    setTimeout(() => {
                        window.location.href = data.redirect_url || receiptUrl;
                    }, 1200);
                } else if (data.status === 'pending') {
                    if (isManual) {
                        showMessage('pending', 'Tagihan belum dibayar. Silakan selesaikan pembayaran Anda.');
                    }
                } else if (data.status === 'expired' || data.status === 'cancel') {
                    clearInterval(pollingInterval);
                    showMessage('error', 'Waktu pembayaran telah kedaluwarsa atau dibatalkan.');
                }
            })
            .catch(err => {
                isChecking = false;
                if (isManual) spinnerIcon.classList.remove('fa-spin');
                console.error("Status check error:", err);
            });
        }

        // Event listener tombol bayar
        btnPaySnap.addEventListener('click', function () {
            launchSnap();
        });

        // Event listener tombol cek manual
        btnCheckStatus.addEventListener('click', function () {
            checkStatusLive(true);
        });

        // Background polling setiap 4 detik
        pollingInterval = setInterval(function () {
            checkStatusLive(false);
        }, 4000);

        // Auto-launch Snap popup setelah 600ms halaman dimuat
        setTimeout(function () {
            launchSnap();
        }, 600);

        // Countdown timer 15 menit
        let timeLeft = 15 * 60;
        const countdownEl = document.getElementById('countdown-timer');
        const timerInterval = setInterval(function () {
            timeLeft--;
            if (timeLeft <= 0) {
                clearInterval(timerInterval);
                clearInterval(pollingInterval);
                countdownEl.innerText = "00:00";
                showMessage('error', 'Waktu pembayaran telah habis.');
                return;
            }
            const mins = Math.floor(timeLeft / 60);
            const secs = timeLeft % 60;
            countdownEl.innerText = (mins < 10 ? '0' : '') + mins + ':' + (secs < 10 ? '0' : '') + secs;
        }, 1000);
    });
</script>
@endif

@endsection