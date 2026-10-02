@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
    /* Sembunyikan navbar dan footer bawaan agar tampilan full-screen ala Shopee */
    nav, header, footer, .navbar, .header, .top-bar, .footer, #footer { display: none !important; }
    body { background-color: #f8fafc; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; padding-bottom: 90px; }
    
    .shopee-container { max-width: 500px; margin: 0 auto; background: white; min-height: 100vh; }
    
    /* Radio bulat ala Shopee dengan warna Sky Blue Rentify */
    .custom-radio {
        width: 20px;
        height: 20px;
        border-radius: 50%;
        border: 2px solid #cbd5e1;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s;
        flex-shrink: 0;
    }
    .custom-radio.checked {
        border-color: #0284c7;
        background-color: #0284c7;
    }
    .custom-radio.checked::after {
        content: "✓";
        color: white;
        font-size: 12px;
        font-weight: 900;
    }
    
    .channel-row {
        cursor: pointer;
        transition: background-color 0.15s;
    }
    .channel-row:active {
        background-color: #f1f5f9;
    }
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

<div class="shopee-container shadow-sm relative">

    <!-- 1. HEADER ALA SHOPEE -->
    <div class="sticky top-0 z-40 bg-white border-b border-slate-100 px-4 py-3.5 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ route('customer.pesanan') }}" class="text-slate-700 hover:text-sky-600 transition text-lg flex items-center justify-center">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
            <h1 class="text-[17px] font-bold text-slate-800">Metode Pembayaran</h1>
        </div>
        <div class="text-[11px] font-bold text-sky-600 bg-sky-50 px-2.5 py-0.5 rounded-full border border-sky-100">
            {{ $id }}
        </div>
    </div>

    <!-- Ringkasan Singkat Total Tagihan -->
    <div class="bg-gradient-to-r from-sky-50 to-blue-50/50 px-4 py-3 border-b border-sky-100/70 flex items-center justify-between">
        <span class="text-xs font-semibold text-slate-600">Total Tagihan Pembayaran:</span>
        <span class="text-base font-black text-sky-600">Rp {{ number_format($total ?? 0, 0, ',', '.') }}</span>
    </div>

    <!-- 2. DAFTAR METODE PEMBAYARAN (ALA SHOPEE) -->
    <div class="divide-y divide-slate-100">

        <!-- OPSI 1: QRIS -->
        <div class="channel-row p-4 flex items-center justify-between" onclick="pilihMetode('qris')">
            <div class="flex items-start gap-3">
                <div class="w-8 h-8 rounded-lg bg-slate-50 border border-slate-200 flex items-center justify-center font-black text-[10px] text-slate-700 flex-shrink-0 mt-0.5">
                    <span class="text-red-500 font-extrabold text-[9px] leading-tight">QRIS</span>
                </div>
                <div>
                    <div class="text-[14px] font-bold text-slate-800 leading-snug">QRIS</div>
                    <div class="flex items-center gap-1.5 mt-1">
                        <span class="px-1.5 py-0.5 rounded bg-blue-50 text-blue-600 text-[9px] font-extrabold border border-blue-100">DANA</span>
                        <span class="px-1.5 py-0.5 rounded bg-emerald-50 text-emerald-600 text-[9px] font-extrabold border border-emerald-100">GoPay</span>
                        <span class="px-1.5 py-0.5 rounded bg-sky-50 text-sky-700 text-[9px] font-extrabold border border-sky-100">BCA</span>
                        <span class="px-1.5 py-0.5 rounded bg-amber-50 text-amber-700 text-[9px] font-extrabold border border-amber-100">ShopeePay</span>
                        <span class="text-[10px] text-slate-400 font-medium">+ lainnya</span>
                    </div>
                </div>
            </div>
            <div class="custom-radio checked" id="radio-qris"></div>
        </div>

        <!-- OPSI 2: COD -->
        <div class="channel-row p-4 flex items-center justify-between" onclick="pilihMetode('cod')">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-sky-50 border border-sky-100 flex items-center justify-center text-sky-600 text-xs font-black flex-shrink-0">
                    COD
                </div>
                <div>
                    <div class="text-[14px] font-bold text-slate-800 flex items-center gap-1.5">
                        <span>COD (Bayar di Tempat)</span>
                        <i class="fa-regular fa-circle-question text-slate-300 text-xs"></i>
                    </div>
                    <div class="text-[11px] text-slate-400 font-medium">Bayar tunai saat serah terima unit rental</div>
                </div>
            </div>
            <div class="custom-radio" id="radio-cod"></div>
        </div>

        <!-- OPSI 3: TRANSFER BANK (ACCORDION ALA SHOPEE) -->
        <div>
            <div class="channel-row p-4 flex items-center justify-between" onclick="toggleAccordion('bank')">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-sky-100 text-sky-600 flex items-center justify-center text-xs flex-shrink-0">
                        <i class="fa-solid fa-money-bill-transfer"></i>
                    </div>
                    <div>
                        <div class="text-[14px] font-bold text-slate-800 flex items-center gap-1.5">
                            <span>Transfer Bank (Virtual Account)</span>
                            <i class="fa-regular fa-circle-question text-slate-300 text-xs"></i>
                        </div>
                        <div class="text-[11px] text-slate-400 font-medium" id="subtext-bank">BCA, Mandiri, BNI, BRI, Permata, dll</div>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <i class="fa-solid fa-chevron-up text-slate-400 text-xs transition-transform duration-200" id="chevron-bank"></i>
                </div>
            </div>

            <!-- SUB-ITEM BANK VIRTUAL ACCOUNT ALA SHOPEE -->
            <div id="panel-bank" class="bg-slate-50/70 border-t border-slate-100 divide-y divide-slate-100/80 pl-11 pr-4">
                
                <!-- BCA -->
                <div class="channel-row py-3 flex items-center justify-between" onclick="pilihMetode('bank_bca')">
                    <div class="flex items-center gap-2.5">
                        <span class="w-6 h-6 rounded bg-blue-700 text-white font-black text-[8px] flex items-center justify-center">BCA</span>
                        <span class="text-[13px] font-bold text-slate-700">Bank BCA</span>
                    </div>
                    <div class="custom-radio" id="radio-bank_bca"></div>
                </div>

                <!-- Mandiri -->
                <div class="channel-row py-3 flex items-center justify-between" onclick="pilihMetode('bank_mandiri')">
                    <div class="flex items-center gap-2.5">
                        <span class="w-6 h-6 rounded bg-blue-900 text-yellow-400 font-black text-[7px] flex items-center justify-center">MANDIRI</span>
                        <span class="text-[13px] font-bold text-slate-700">Bank Mandiri</span>
                    </div>
                    <div class="custom-radio" id="radio-bank_mandiri"></div>
                </div>

                <!-- BNI -->
                <div class="channel-row py-3 flex items-center justify-between" onclick="pilihMetode('bank_bni')">
                    <div class="flex items-center gap-2.5">
                        <span class="w-6 h-6 rounded bg-orange-600 text-white font-black text-[8px] flex items-center justify-center">BNI</span>
                        <span class="text-[13px] font-bold text-slate-700">Bank BNI</span>
                    </div>
                    <div class="custom-radio" id="radio-bank_bni"></div>
                </div>

                <!-- BRI -->
                <div class="channel-row py-3 flex items-center justify-between" onclick="pilihMetode('bank_bri')">
                    <div class="flex items-center gap-2.5">
                        <span class="w-6 h-6 rounded bg-blue-600 text-white font-black text-[8px] flex items-center justify-center">BRI</span>
                        <span class="text-[13px] font-bold text-slate-700">Bank BRI</span>
                    </div>
                    <div class="custom-radio" id="radio-bank_bri"></div>
                </div>

                <!-- Permata -->
                <div class="channel-row py-3 flex items-center justify-between" onclick="pilihMetode('bank_permata')">
                    <div class="flex items-center gap-2.5">
                        <span class="w-6 h-6 rounded bg-emerald-600 text-white font-black text-[7px] flex items-center justify-center">PERMATA</span>
                        <span class="text-[13px] font-bold text-slate-700">Bank Permata</span>
                    </div>
                    <div class="custom-radio" id="radio-bank_permata"></div>
                </div>

                <!-- CIMB Niaga -->
                <div class="channel-row py-3 flex items-center justify-between" onclick="pilihMetode('bank_cimb')">
                    <div class="flex items-center gap-2.5">
                        <span class="w-6 h-6 rounded bg-red-700 text-white font-black text-[8px] flex items-center justify-center">CIMB</span>
                        <span class="text-[13px] font-bold text-slate-700">Bank CIMB Niaga</span>
                    </div>
                    <div class="custom-radio" id="radio-bank_cimb"></div>
                </div>

                <!-- Bank Lainnya -->
                <div class="channel-row py-3 flex items-center justify-between" onclick="pilihMetode('bank_lainnya')">
                    <div class="flex items-center gap-2.5">
                        <div class="w-6 h-6 rounded bg-slate-600 text-white flex items-center justify-center text-[10px]">
                            <i class="fa-solid fa-building-columns"></i>
                        </div>
                        <div>
                            <span class="text-[13px] font-bold text-slate-700 block leading-tight">Bank lainnya</span>
                            <span class="text-[10px] text-slate-400">Menerima transfer dari semua bank (ATM Bersama / Prima)</span>
                        </div>
                    </div>
                    <div class="custom-radio" id="radio-bank_lainnya"></div>
                </div>
            </div>
        </div>

        <!-- OPSI 4: KARTU KREDIT / DEBIT -->
        <div class="channel-row p-4 flex items-center justify-between" onclick="pilihMetode('credit_card')">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-sky-50 border border-sky-100 flex items-center justify-center text-sky-600 text-xs flex-shrink-0">
                    <i class="fa-solid fa-credit-card"></i>
                </div>
                <div>
                    <div class="text-[14px] font-bold text-slate-800">Kartu Kredit / Debit Online</div>
                    <div class="text-[11px] text-slate-400 font-medium">Visa, Mastercard, JCB, American Express</div>
                </div>
            </div>
            <div class="custom-radio" id="radio-credit_card"></div>
        </div>

        <!-- OPSI 5: E-WALLET / BAYAR INSTAN -->
        <div class="channel-row p-4 flex items-center justify-between" onclick="pilihMetode('gopay')">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-sky-50 border border-sky-100 flex items-center justify-center text-sky-600 text-xs flex-shrink-0">
                    <i class="fa-solid fa-wallet"></i>
                </div>
                <div>
                    <div class="text-[14px] font-bold text-slate-800">GoPay / ShopeePay Instan</div>
                    <div class="text-[11px] text-slate-400 font-medium">Buka aplikasi langsung untuk bayar instan</div>
                </div>
            </div>
            <div class="custom-radio" id="radio-gopay"></div>
        </div>

    </div>

    <!-- Informasi Status Pembayaran & Keamanan -->
    <div class="p-4 mt-2">
        <div id="status-card" class="hidden p-3.5 rounded-xl text-xs font-bold mb-3 transition-all"></div>

        <div class="flex items-center justify-center gap-1.5 text-[11px] text-slate-400 text-center font-medium">
            <i class="fa-solid fa-shield-halved text-sky-500"></i>
            <span>Transaksi aman & terenkripsi oleh Midtrans Payment Gateway</span>
        </div>
    </div>

    <!-- 3. TOMBOL KONFIRMASI FIXED DI BAWAH (ALA SHOPEE) -->
    <div class="fixed bottom-0 left-0 w-full bg-white border-t border-slate-200 px-4 py-3 z-50 shadow-[0_-4px_20px_rgba(0,0,0,0.06)]">
        <div class="max-w-[500px] mx-auto flex items-center justify-between gap-3">
            <div>
                <span class="text-[10px] font-bold text-slate-400 block uppercase">Total Tagihan</span>
                <span class="text-[16px] font-black text-sky-600 leading-tight">Rp {{ number_format($total ?? 0, 0, ',', '.') }}</span>
            </div>
            <button type="button" id="btn-konfirmasi" class="flex-1 bg-gradient-to-r from-sky-500 to-sky-600 hover:from-sky-600 hover:to-sky-700 text-white font-black text-[15px] py-3.5 px-6 rounded-xl shadow-md transition-all active:scale-95 flex items-center justify-center gap-2">
                <span>Konfirmasi</span>
                <i class="fa-solid fa-arrow-right text-xs"></i>
            </button>
        </div>
    </div>

</div>

<script>
    let selectedMetode = 'qris';
    const snapToken = "{{ $snapToken ?? '' }}";
    const invoiceId = "{{ $id }}";
    const checkStatusUrl = "{{ route('customer.pembayaran.check_status', $id) }}";
    const receiptUrl = "{{ route('customer.struk', $id) }}";

    function pilihMetode(metode) {
        selectedMetode = metode;

        // Reset semua radio checkmark
        document.querySelectorAll('.custom-radio').forEach(el => el.classList.remove('checked'));

        // Aktifkan yang dipilih
        const radio = document.getElementById('radio-' + metode);
        if (radio) {
            radio.classList.add('checked');
        }

        // Jika sub-item bank dipilih, beri tanda di accordion juga
        if (metode.startsWith('bank_')) {
            const radioBankParent = document.getElementById('radio-bank_bca'); // sub
        }
    }

    function toggleAccordion(nama) {
        const panel = document.getElementById('panel-' + nama);
        const chevron = document.getElementById('chevron-' + nama);

        if (panel.classList.contains('hidden')) {
            panel.classList.remove('hidden');
            chevron.classList.remove('fa-chevron-down');
            chevron.classList.add('fa-chevron-up');
        } else {
            panel.classList.add('hidden');
            chevron.classList.remove('fa-chevron-up');
            chevron.classList.add('fa-chevron-down');
        }
    }

    document.addEventListener("DOMContentLoaded", function () {
        const btnKonfirmasi = document.getElementById('btn-konfirmasi');
        const statusCard = document.getElementById('status-card');

        function showMessage(type, text) {
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
            .then(res => res.json())
            .then(data => {
                if (data.paid) {
                    showMessage('success', '🎉 Pembayaran lunas & terverifikasi! Mengalihkan ke struk...');
                    setTimeout(() => { window.location.href = data.redirect_url || receiptUrl; }, 1200);
                }
            })
            .catch(e => console.error(e));
        }

        // Polling background tiap 4 detik
        setInterval(checkStatusLive, 4000);

        // Eksekusi ketika tombol Konfirmasi diklik
        btnKonfirmasi.addEventListener('click', function () {
            if (selectedMetode === 'cod') {
                window.location.href = receiptUrl;
                return;
            }

            if (!snapToken) {
                alert("Sesi pembayaran sedang disiapkan. Silakan refresh halaman.");
                return;
            }

            if (typeof window.snap === 'undefined') {
                alert("Midtrans Snap belum siap. Mohon periksa koneksi internet Anda.");
                return;
            }

            // Buka Midtrans Snap Popup
            snap.pay(snapToken, {
                onSuccess: function (result) {
                    showMessage('success', 'Pembayaran berhasil! Mengalihkan ke struk pesanan...');
                    setTimeout(() => { window.location.href = receiptUrl; }, 1200);
                },
                onPending: function (result) {
                    showMessage('pending', 'Menunggu penyelesaian pembayaran di m-banking / e-wallet...');
                    checkStatusLive();
                },
                onError: function (result) {
                    showMessage('error', 'Pembayaran gagal atau dibatalkan.');
                },
                onClose: function () {
                    checkStatusLive();
                }
            });
        });
    });
</script>

@endsection