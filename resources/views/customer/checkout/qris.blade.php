@extends('layouts.app')

@php
    /** @var mixed $id */
    /** @var mixed $total */
    /** @var mixed $kodeUnik */
    /** @var mixed $subtotal */
    /** @var mixed $merchantName */
    /** @var mixed $merchantNmid */
    /** @var mixed $qrisImageUrl */
    /** @var mixed $qrisPayload */
    /** @var mixed $orderItems */
    /** @var mixed $paymentExpiredAt */
    $formattedTotal = number_format($total ?? 0, 0, ',', '.');
    $formattedSubtotal = number_format($subtotal ?? ($total - ($kodeUnik ?? 0)), 0, ',', '.');
    $cleanMerchantName = $merchantName ?? 'Rental Kursi';
    $cleanNmid = $merchantNmid ?? 'ID1026587219324';
    $csWaNumber = '6283183494835';
    $csWaText = urlencode("Halo Admin Rentify, saya butuh bantuan konfirmasi pembayaran QRIS untuk pesanan {$id}.");
    $csWaUrl = "https://wa.me/{$csWaNumber}?text={$csWaText}";
@endphp

@section('title', 'Pembayaran QRIS — Rentify')

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
{{-- Library pembuat QR code lokal instan tanpa latency --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap');
    
    /* Sembunyikan navigasi default app agar fokus penuh ke pembayaran */
    nav, header, footer, .navbar, .header, .top-bar, .footer, #footer, .bottom-nav { 
        display: none !important; 
    }
    
    body {
        background: radial-gradient(circle at 50% 0%, #e0f2fe 0%, #f8fafc 40%, #f1f5f9 100%) fixed !important;
        font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
        color: #0f172a;
        margin: 0;
        padding: 0;
        min-height: 100vh;
        -webkit-font-smoothing: antialiased;
    }
    
    .qris-container {
        max-width: 480px;
        margin: 0 auto;
        padding: 16px 14px 60px 14px;
    }

    /* Kartu bertema Rentify Modern */
    .rentify-card {
        background: #ffffff;
        border-radius: 24px;
        border: 1px solid rgba(226, 232, 240, 0.9);
        box-shadow: 0 10px 25px -5px rgba(2, 132, 199, 0.08), 0 4px 10px -2px rgba(0, 0, 0, 0.03);
        position: relative;
        overflow: hidden;
    }

    .rentify-card-glass {
        background: rgba(255, 255, 255, 0.92);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border-radius: 20px;
        border: 1px solid rgba(255, 255, 255, 0.9);
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.04);
    }

    /* Animasi radar listening */
    @keyframes pulse-radar {
        0% { transform: scale(0.95); opacity: 0.8; }
        50% { transform: scale(1.3); opacity: 0; }
        100% { transform: scale(0.95); opacity: 0; }
    }
    .radar-wave {
        animation: pulse-radar 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
    }

    /* Efek Viewfinder Corner QR Code */
    .viewfinder-corner {
        position: absolute;
        width: 16px;
        height: 16px;
        border-color: #0284c7;
        pointer-events: none;
    }
    .corner-tl { top: -2px; left: -2px; border-top: 3px solid #0284c7; border-left: 3px solid #0284c7; border-top-left-radius: 8px; }
    .corner-tr { top: -2px; right: -2px; border-top: 3px solid #0284c7; border-right: 3px solid #0284c7; border-top-right-radius: 8px; }
    .corner-bl { bottom: -2px; left: -2px; border-bottom: 3px solid #0284c7; border-left: 3px solid #0284c7; border-bottom-left-radius: 8px; }
    .corner-br { bottom: -2px; right: -2px; border-bottom: 3px solid #0284c7; border-right: 3px solid #0284c7; border-bottom-right-radius: 8px; }

    /* Canvas styling */
    #qrcode-canvas-container img, 
    #qrcode-canvas-container canvas {
        margin: 0 auto;
        display: block;
        max-width: 100%;
        height: auto;
    }

    /* Toast Tooltip saat Salin */
    .copy-toast {
        position: fixed;
        bottom: 24px;
        left: 50%;
        transform: translateX(-50%) translateY(100px);
        background: #0f172a;
        color: #ffffff;
        font-size: 13px;
        font-weight: 700;
        padding: 10px 18px;
        border-radius: 9999px;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.25);
        display: flex;
        align-items: center;
        gap: 8px;
        z-index: 9999;
        transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.3s;
        opacity: 0;
        pointer-events: none;
    }
    .copy-toast.show {
        transform: translateX(-50%) translateY(0);
        opacity: 1;
    }

    /* Modal Zoom Layar Penuh */
    .qr-zoom-modal {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.85);
        backdrop-filter: blur(8px);
        z-index: 9998;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }
    .qr-zoom-modal.active {
        display: flex;
    }
</style>

<div class="qris-container">

    <!-- 1. HEADER NAVIGASI & KEAMANAN -->
    <div class="rentify-card-glass p-3 mb-3 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ route('customer.pesanan') }}" class="w-9 h-9 rounded-xl bg-white hover:bg-slate-100 text-slate-700 flex items-center justify-center transition active:scale-95 border border-slate-200/80 shadow-xs" title="Kembali ke Pesanan">
                <i class="fa-solid fa-arrow-left text-sm"></i>
            </a>
            <div>
                <h1 class="text-[15px] font-extrabold text-slate-900 leading-tight">Pembayaran QRIS</h1>
                <span class="text-[11.5px] text-slate-500 font-medium">
                    ID: <strong class="font-mono text-slate-700 font-bold">{{ $id }}</strong>
                </span>
            </div>
        </div>
    </div>

    <!-- 2. KARTU HERO TOTAL PEMBAYARAN & COUNTDOWN -->
    <div class="rentify-card p-5 mb-3.5">
        <!-- Top Gradient Stripe -->
        <div class="absolute top-0 left-0 right-0 h-1.5 bg-gradient-to-r from-sky-500 via-sky-400 to-indigo-500"></div>

        <div class="flex items-start justify-between">
            <div>
                <span class="text-[11.5px] font-bold uppercase tracking-wider text-slate-500 block mb-1">
                    Total Pembayaran
                </span>
                <div class="flex items-baseline gap-2">
                    <span class="text-3xl font-black text-slate-900 tracking-tight" id="display-nominal">
                        Rp {{ $formattedTotal }}
                    </span>
                </div>
            </div>

            <!-- Tombol Salin Nominal -->
            <button type="button" onclick="salinNominal('{{ $total }}')" class="inline-flex items-center gap-1.5 bg-sky-50 hover:bg-sky-100 text-sky-700 border border-sky-200 text-xs font-bold py-2 px-3 rounded-xl transition active:scale-95 shadow-2xs" title="Salin nominal agar tidak salah ketik di aplikasi bank">
                <i class="fa-regular fa-copy text-sky-600" id="copy-icon"></i>
                <span id="copy-text">Salin</span>
            </button>
        </div>

        @if(!empty($kodeUnik) && $kodeUnik > 0)
        <!-- Box Kode / Admin QRIS -->
        <div class="mt-3.5 px-3.5 py-2.5 rounded-xl bg-sky-50/70 border border-sky-100 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <div class="w-6 h-6 rounded-lg bg-sky-100 text-sky-600 flex items-center justify-center shrink-0 text-xs">
                    <i class="fa-solid fa-receipt text-[11px]"></i>
                </div>
                <span class="text-xs font-bold text-slate-700">Kode, Admin QRIS</span>
            </div>
            <span class="text-xs font-black text-sky-700 font-mono bg-white px-2.5 py-1 rounded-lg border border-sky-200/80 shadow-2xs">
                +{{ $kodeUnik }}
            </span>
        </div>
        @endif

        <!-- Countdown Timer -->
        <div class="flex items-center justify-between text-xs pt-3.5 mt-3.5 border-t border-slate-100">
            <span class="text-slate-500 font-medium flex items-center gap-1.5">
                <i class="fa-regular fa-clock text-slate-400"></i> Batas waktu pembayaran
            </span>
            <div class="flex items-center gap-1.5 font-mono font-black text-amber-800 bg-amber-50/90 px-3 py-1 rounded-xl border border-amber-200 text-xs shadow-2xs">
                <span class="inline-block w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                <span id="countdown-timer">23:59:59</span>
            </div>
        </div>
    </div>

    <!-- 3. STANDEE QRIS UTAMA (DILENGKAPI BINGKAI RESMI & MODERN) -->
    <div class="rentify-card p-4 sm:p-5 mb-3.5 text-center">

        <!-- Standee Header: Official QRIS Ribbon -->
        <div class="bg-gradient-to-r from-[#cc0000] via-[#dc2626] to-[#b91c1c] text-white rounded-2xl p-3 mb-4 shadow-sm relative overflow-hidden">
            <!-- Decorative circle -->
            <div class="absolute -right-4 -top-4 w-20 h-20 bg-white/10 rounded-full blur-xs"></div>

            <div class="flex items-center justify-between relative z-10">
                <div class="text-left">
                    <div class="flex items-center gap-1.5">
                        <span class="text-xl font-black tracking-wider leading-none">QRIS</span>
                        <span class="text-[10px] font-bold uppercase tracking-wider bg-white/20 px-1.5 py-0.5 rounded text-white">
                            ASPI / BI
                        </span>
                    </div>
                    <span class="text-[10.5px] font-medium text-red-100 tracking-wide block mt-0.5">
                        Standar Pembayaran Nasional
                    </span>
                </div>

                <!-- Rentify Official Badge -->
                <div class="text-right">
                    <span class="inline-flex items-center gap-1 bg-white text-[#dc2626] text-[10.5px] font-black px-2 py-1 rounded-lg shadow-2xs">
                        <i class="fa-solid fa-circle-check text-sky-500 text-[10px]"></i>
                        <span>Rentify</span>
                    </span>
                </div>
            </div>
        </div>

        <!-- Info Merchant -->
        <div class="flex items-center justify-between text-xs px-1 pb-3 mb-3 border-b border-slate-100">
            <div class="text-left">
                <span class="text-[10.5px] uppercase font-bold tracking-wider text-slate-400 block">Merchant Resmi</span>
                <span class="font-extrabold text-slate-800 text-[13px] leading-tight block truncate max-w-[200px]" id="merchant-name-label">
                    {{ $cleanMerchantName }}
                </span>
            </div>
            <div class="text-right">
                <span class="text-[10.5px] uppercase font-bold tracking-wider text-slate-400 block">NMID</span>
                <span class="font-mono text-slate-600 font-semibold text-[11px] block" id="merchant-nmid-label">
                    {{ $cleanNmid }}
                </span>
            </div>
        </div>

        <!-- BINGKAI QR CODE RESMI (DENGAN VIEWFINDER & PADDING OPTIMAL) -->
        <div class="relative inline-block my-1">
            <div class="p-4 sm:p-5 bg-white border-2 border-slate-200/90 rounded-3xl shadow-sm relative group transition hover:border-sky-300">
                <!-- 4 Viewfinder Corner Brackets -->
                <div class="viewfinder-corner corner-tl"></div>
                <div class="viewfinder-corner corner-tr"></div>
                <div class="viewfinder-corner corner-bl"></div>
                <div class="viewfinder-corner corner-br"></div>

                <!-- QR Code Container -->
                <div id="qrcode-canvas-container" class="w-60 h-60 mx-auto flex items-center justify-center relative bg-white">
                    <img src="{{ $qrisImageUrl }}" alt="Kode QRIS Rentify" class="w-60 h-60 object-contain mx-auto" id="qris-img-fallback" />
                </div>
            </div>

            <!-- Petunjuk Mini di Bawah QR -->
            <div class="mt-2 text-[11px] font-semibold text-slate-500 flex items-center justify-center gap-1.5">
                <i class="fa-solid fa-qrcode text-sky-600"></i>
                <span>Scan dengan m-Banking / E-Wallet apa saja</span>
            </div>
        </div>

        <!-- Supported Banks & E-Wallets Logos Ribbon -->
        <div class="mt-3.5 pt-3 border-t border-slate-100">
            <span class="text-[10px] uppercase font-extrabold tracking-wider text-slate-400 block mb-2">
                Mendukung Pembayaran Seluruh Bank & E-Wallet
            </span>
            <div class="flex flex-wrap items-center justify-center gap-1.5">
                <span class="text-[10.5px] font-bold text-slate-700 bg-slate-100 px-2 py-0.5 rounded-md border border-slate-200">BCA</span>
                <span class="text-[10.5px] font-bold text-slate-700 bg-slate-100 px-2 py-0.5 rounded-md border border-slate-200">Livin Mandiri</span>
                <span class="text-[10.5px] font-bold text-slate-700 bg-slate-100 px-2 py-0.5 rounded-md border border-slate-200">BRImo</span>
                <span class="text-[10.5px] font-bold text-slate-700 bg-slate-100 px-2 py-0.5 rounded-md border border-slate-200">BNI</span>
                <span class="text-[10.5px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-200">GoPay</span>
                <span class="text-[10.5px] font-bold text-sky-700 bg-sky-50 px-2 py-0.5 rounded-md border border-sky-200">DANA</span>
                <span class="text-[10.5px] font-bold text-purple-700 bg-purple-50 px-2 py-0.5 rounded-md border border-purple-200">OVO</span>
                <span class="text-[10.5px] font-bold text-orange-700 bg-orange-50 px-2 py-0.5 rounded-md border border-orange-200">ShopeePay</span>
            </div>
        </div>

        <!-- TOMBOL AKSI UTAMA: UNDUH QR BERBINGKAI & PERBESAR -->
        <div class="mt-4 pt-3.5 border-t border-slate-100 flex flex-col sm:flex-row gap-2">
            <!-- Tombol Unduh QR Berbingkai (Sesuai Permintaan User) -->
            <button type="button" onclick="downloadFramedQrCode()" class="flex-1 inline-flex items-center justify-center gap-2 bg-gradient-to-r from-sky-500 via-sky-600 to-sky-700 hover:from-sky-600 hover:to-sky-800 text-white text-xs font-black py-3 px-4 rounded-xl shadow-md shadow-sky-500/25 transition transform active:scale-95">
                <i class="fa-solid fa-download text-sm"></i>
                <span>Simpan QR Berbingkai</span>
            </button>

            <!-- Tombol Perbesar / Fullscreen -->
            <button type="button" onclick="openZoomModal()" class="inline-flex items-center justify-center gap-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold py-3 px-4 rounded-xl transition active:scale-95 border border-slate-200" title="Perbesar QR di layar penuh untuk scan dari HP lain">
                <i class="fa-solid fa-maximize text-xs text-slate-500"></i>
                <span>Perbesar</span>
            </button>
        </div>

    </div>

    <!-- 4. LIVE REAL-TIME RADAR: AUTO-DETEKSI PEMBAYARAN -->
    <div class="rentify-card-glass p-4 mb-3.5 border border-sky-100/90 shadow-sm relative overflow-hidden" style="background: rgba(255, 255, 255, 0.88); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px);">
        <div class="flex items-center gap-3">
            <!-- Pulsing Radar Animation (Biru Rentify) -->
            <div class="relative flex items-center justify-center w-10 h-10 shrink-0">
                <span class="radar-wave absolute inline-flex h-full w-full rounded-full bg-sky-400 opacity-60"></span>
                <span class="relative inline-flex rounded-full h-4 w-4 bg-sky-500 shadow-md shadow-sky-500/40"></span>
            </div>

            <div class="flex-1 min-w-0" id="status-indicator">
                <div class="text-[13px] font-extrabold text-slate-900 flex items-center gap-1.5">
                    <span>Mengecek Pembayaran Realtime...</span>
                </div>
                <p class="text-[11.5px] text-slate-500 mt-0.5 leading-snug">
                    Setelah Anda scan & bayar di m-Banking / e-Wallet, sistem otomatis memverifikasi dan langsung beralih ke Struk Pesanan.
                </p>
            </div>
        </div>
    </div>

    <!-- 5. PETUNJUK PEMBAYARAN INTERAKTIF (5 LANGKAH CEPAT) -->
    <div class="rentify-card p-4 sm:p-5 mb-3.5">
        <div class="flex items-center justify-between mb-3.5">
            <h2 class="text-xs font-black text-slate-800 uppercase tracking-wider flex items-center gap-2">
                <i class="fa-solid fa-circle-question text-sky-600"></i>
                <span>Cara Bayar Menggunakan QRIS</span>
            </h2>
            <span class="text-[11px] font-bold text-sky-600 bg-sky-50 px-2 py-0.5 rounded-full border border-sky-100">
                Semua Aplikasi
            </span>
        </div>

        <div class="space-y-3 text-xs text-slate-600">
            <!-- Step 1 -->
            <div class="flex items-start gap-3 p-2.5 rounded-xl bg-slate-50/70 border border-slate-100">
                <span class="w-6 h-6 rounded-full bg-sky-500 text-white font-black flex items-center justify-center shrink-0 text-[11px] shadow-2xs">1</span>
                <div>
                    <span class="font-bold text-slate-800 block">Buka Aplikasi Pembayaran</span>
                    <span class="text-[11.5px] text-slate-500">Buka m-Banking (BCA, Mandiri, BRI, BNI, dll) atau E-Wallet (DANA, GoPay, OVO, ShopeePay).</span>
                </div>
            </div>

            <!-- Step 2 -->
            <div class="flex items-start gap-3 p-2.5 rounded-xl bg-slate-50/70 border border-slate-100">
                <span class="w-6 h-6 rounded-full bg-sky-500 text-white font-black flex items-center justify-center shrink-0 text-[11px] shadow-2xs">2</span>
                <div>
                    <span class="font-bold text-slate-800 block">Pilih Menu Scan / QRIS</span>
                    <span class="text-[11.5px] text-slate-500">Klik ikon kamera QRIS atau menu <strong>Bayar / Scan</strong> di aplikasi Anda.</span>
                </div>
            </div>

            <!-- Step 3 -->
            <div class="flex items-start gap-3 p-2.5 rounded-xl bg-slate-50/70 border border-slate-100">
                <span class="w-6 h-6 rounded-full bg-sky-500 text-white font-black flex items-center justify-center shrink-0 text-[11px] shadow-2xs">3</span>
                <div>
                    <span class="font-bold text-slate-800 block">Scan atau Pilih dari Galeri</span>
                    <span class="text-[11.5px] text-slate-500">
                        Arahkan kamera ke QR di atas, <strong>ATAU jika bayar dari HP yang sama:</strong> klik tombol <strong>"Simpan QR Berbingkai"</strong> di atas, lalu pilih opsi <strong>Ambil dari Galeri</strong> pada menu scan aplikasi Anda.
                    </span>
                </div>
            </div>

            <!-- Step 4 -->
            <div class="flex items-start gap-3 p-2.5 rounded-xl bg-slate-50/70 border border-slate-100">
                <span class="w-6 h-6 rounded-full bg-sky-500 text-white font-black flex items-center justify-center shrink-0 text-[11px] shadow-2xs">4</span>
                <div>
                    <span class="font-bold text-slate-800 block">Periksa Nominal Pembayaran</span>
                    <span class="text-[11.5px] text-slate-500">
                        Pastikan nominal pembayaran adalah tepat <strong>Rp {{ $formattedTotal }}</strong> dan nama merchant adalah <strong>{{ $cleanMerchantName }}</strong>.
                    </span>
                </div>
            </div>

            <!-- Step 5 -->
            <div class="flex items-start gap-3 p-2.5 rounded-xl bg-emerald-50/60 border border-emerald-100">
                <span class="w-6 h-6 rounded-full bg-emerald-500 text-white font-black flex items-center justify-center shrink-0 text-[11px] shadow-2xs"><i class="fa-solid fa-check text-[10px]"></i></span>
                <div>
                    <span class="font-bold text-emerald-900 block">Konfirmasi & Masukkan PIN</span>
                    <span class="text-[11.5px] text-emerald-800/90">
                        Selesaikan transaksi. Halaman ini akan <strong>otomatis beralih ke struk pesanan</strong> dalam beberapa detik setelah dana masuk!
                    </span>
                </div>
            </div>
        </div>
    </div>

    @if(isset($orderItems) && count($orderItems) > 0)
    <!-- 6. RINCIAN BARANG PESANAN (COLLAPSIBLE) -->
    <div class="rentify-card p-4 mb-3.5">
        <details class="group cursor-pointer">
            <summary class="flex items-center justify-between text-xs font-bold text-slate-700 list-none">
                <span class="flex items-center gap-2">
                    <i class="fa-solid fa-box-archive text-sky-600"></i>
                    <span>Rincian Barang yang Disewa ({{ count($orderItems) }} item)</span>
                </span>
                <i class="fa-solid fa-chevron-down text-slate-400 group-open:rotate-180 transition-transform"></i>
            </summary>
            
            <div class="mt-3 pt-3 border-t border-slate-100 space-y-2.5">
                @foreach($orderItems as $item)
                <div class="flex items-center justify-between text-xs">
                    <div class="flex items-center gap-2.5 min-w-0">
                        @if(!empty($item->cover_photo))
                        <img src="{{ $item->cover_photo }}" alt="{{ $item->barang_nama }}" class="w-8 h-8 rounded-lg object-cover border border-slate-200 shrink-0">
                        @else
                        <div class="w-8 h-8 rounded-lg bg-sky-50 text-sky-600 flex items-center justify-center font-bold text-xs shrink-0">
                            <i class="fa-solid fa-cube"></i>
                        </div>
                        @endif
                        <div class="truncate">
                            <span class="font-bold text-slate-800 block truncate">{{ $item->barang_nama ?? 'Barang Sewa' }}</span>
                            <span class="text-[11px] text-slate-500">{{ $item->quantity ?? 1 }}x Unit • {{ $item->duration_days ?? 1 }} Hari</span>
                        </div>
                    </div>
                    <span class="font-bold text-slate-700 shrink-0 ml-2">
                        Rp {{ number_format($item->subtotal ?? 0, 0, ',', '.') }}
                    </span>
                </div>
                @endforeach
            </div>
        </details>
    </div>
    @endif

    <!-- 7. BANTUAN CS WHATSAPP -->
    <div class="text-center mb-4">
        <a href="{{ $csWaUrl }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-full bg-emerald-50 hover:bg-emerald-100 text-emerald-700 text-xs font-bold border border-emerald-200 transition active:scale-95 shadow-2xs">
            <i class="fa-brands fa-whatsapp text-sm text-emerald-600"></i>
            <span>Butuh Bantuan Pembayaran? Chat CS Rentify</span>
        </a>
    </div>

    @if(request('dev') || request('test'))
    <!-- 8. MODE PENGEMBANG / TESTING SANDBOX -->
    <div class="p-3.5 bg-amber-50 border border-amber-200 rounded-2xl mb-4 text-center">
        <span class="text-[11px] font-bold text-amber-800 block mb-1.5 flex items-center justify-center gap-1">
            <i class="fa-solid fa-vial"></i> Developer Testing Sandbox
        </span>
        <button type="button" onclick="simulasikanBayar()" class="text-xs bg-amber-600 hover:bg-amber-700 text-white font-extrabold py-2 px-4 rounded-xl shadow-xs transition active:scale-95">
            Simulasikan Pembayaran Berhasil (Bypass)
        </button>
    </div>
    @endif

    <!-- 9. LINK KEMBALI KE PESANAN -->
    <div class="text-center">
        <a href="{{ route('customer.pesanan') }}" class="text-xs text-slate-400 hover:text-slate-600 font-semibold transition">
            ← Kembali ke Halaman Pesanan
        </a>
    </div>

</div>

<!-- TOAST TOOLTIP SAAT COPY -->
<div id="copy-toast" class="copy-toast">
    <i class="fa-solid fa-circle-check text-sky-400 text-sm"></i>
    <span id="copy-toast-msg">Nominal berhasil disalin!</span>
</div>

<!-- MODAL ZOOM LAYAR PENUH -->
<div id="qr-zoom-modal" class="qr-zoom-modal" onclick="closeZoomModal(event)">
    <div class="bg-white rounded-3xl p-6 max-w-xs w-full text-center shadow-2xl relative" onclick="event.stopPropagation()">
        <button type="button" onclick="closeZoomModal()" class="absolute top-3 right-3 w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-500 flex items-center justify-center text-sm font-bold">
            <i class="fa-solid fa-xmark"></i>
        </button>

        <span class="text-xs font-extrabold text-slate-800 uppercase tracking-wider block mb-1">
            Scan QR Code
        </span>
        <span class="text-[11px] text-slate-500 block mb-4">
            Arahkan kamera smartphone lain ke layar ini
        </span>

        <div class="p-4 bg-white border border-slate-200 rounded-2xl inline-block shadow-sm mb-3">
            <div id="zoom-qr-target" class="w-64 h-64 flex items-center justify-center">
                <!-- Diisi via JavaScript -->
            </div>
        </div>

        <div class="text-center">
            <span class="text-lg font-black text-slate-900 block">Rp {{ $formattedTotal }}</span>
            <span class="text-xs text-slate-500 font-medium">{{ $cleanMerchantName }}</span>
        </div>
    </div>
</div>

<script>
    const invoiceId = "{{ $id }}";
    const totalAmount = "{{ $total }}";
    const formattedTotal = "{{ $formattedTotal }}";
    const merchantName = {!! json_encode($cleanMerchantName) !!};
    const merchantNmid = {!! json_encode($cleanNmid) !!};
    const kodeUnik = {{ $kodeUnik ?? 0 }};
    const qrisPayload = {!! json_encode($qrisPayload ?? '') !!};
    const qrisImageUrl = {!! json_encode($qrisImageUrl ?? '') !!};
    const checkStatusUrl = "{{ route('customer.pembayaran.check_status', $id) }}";
    const simulateUrl = "{{ route('customer.pembayaran.simulate', $id) }}";
    const receiptUrl = "{{ route('customer.struk', $id) }}";
    const csrfToken = "{{ csrf_token() }}";

    // ─────────────────────────────────────────────────────────────────────────
    // 1. RENDER KODE QR DINAMIS (QRCode.js)
    // ─────────────────────────────────────────────────────────────────────────
    document.addEventListener("DOMContentLoaded", function() {
        if (qrisPayload && typeof QRCode !== 'undefined') {
            try {
                const container = document.getElementById('qrcode-canvas-container');
                if (container) {
                    container.innerHTML = '';
                    new QRCode(container, {
                        text: qrisPayload,
                        width: 240,
                        height: 240,
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

    // ─────────────────────────────────────────────────────────────────────────
    // 2. SALIN NOMINAL DENGAN FEEDBACK INSTAN
    // ─────────────────────────────────────────────────────────────────────────
    function salinNominal(amount) {
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(amount).then(showCopySuccess).catch(() => fallbackCopy(amount));
        } else {
            fallbackCopy(amount);
        }
    }

    function fallbackCopy(text) {
        const tempInput = document.createElement("input");
        tempInput.value = text;
        document.body.appendChild(tempInput);
        tempInput.select();
        try {
            document.execCommand("copy");
            showCopySuccess();
        } catch (e) {
            console.warn("Copy failed", e);
        }
        document.body.removeChild(tempInput);
    }

    function showCopySuccess() {
        const copyIcon = document.getElementById('copy-icon');
        const copyText = document.getElementById('copy-text');
        const toast = document.getElementById('copy-toast');

        if (copyIcon && copyText) {
            copyIcon.className = 'fa-solid fa-check text-sky-600';
            copyText.textContent = 'Tersalin!';
            setTimeout(() => {
                copyIcon.className = 'fa-regular fa-copy text-sky-600';
                copyText.textContent = 'Salin';
            }, 2000);
        }

        if (toast) {
            toast.classList.add('show');
            setTimeout(() => toast.classList.remove('show'), 2200);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 3. GENERATOR DOWNLOAD QRIS BERBINGKAI RESMI & PROFESIONAL
    //    Memenuhi instruksi user: "ukuran qris untuk di dowload di kasih bingkai nya ya,
    //    jadi saat customer dowload qris nya gk full minimal ada bingkai nya lahh"
    // ─────────────────────────────────────────────────────────────────────────
    function drawRoundedRect(ctx, x, y, width, height, radius, fill, stroke) {
        if (typeof radius === 'number') {
            radius = {tl: radius, tr: radius, br: radius, bl: radius};
        } else {
            radius = Object.assign({tl: 0, tr: 0, br: 0, bl: 0}, radius);
        }
        ctx.beginPath();
        ctx.moveTo(x + radius.tl, y);
        ctx.lineTo(x + width - radius.tr, y);
        ctx.quadraticCurveTo(x + width, y, x + width, y + radius.tr);
        ctx.lineTo(x + width, y + height - radius.br);
        ctx.quadraticCurveTo(x + width, y + height, x + width - radius.br, y + height);
        ctx.lineTo(x + radius.bl, y + height);
        ctx.quadraticCurveTo(x, y + height, x, y + height - radius.bl);
        ctx.lineTo(x, y + radius.tl);
        ctx.quadraticCurveTo(x, y, x + radius.tl, y);
        ctx.closePath();
        if (fill) ctx.fill();
        if (stroke) ctx.stroke();
    }

    function downloadFramedQrCode() {
        const container = document.getElementById('qrcode-canvas-container');
        const srcCanvas = container ? container.querySelector('canvas') : null;
        const srcImg = container ? container.querySelector('img') : null;

        // Siapkan Canvas Berbingkai Resolusi Tinggi (840 x 1180 px)
        const canvas = document.createElement('canvas');
        canvas.width = 840;
        canvas.height = 1180;
        const ctx = canvas.getContext('2d');

        // A. Background Canvas Luar
        ctx.fillStyle = '#f1f5f9';
        ctx.fillRect(0, 0, canvas.width, canvas.height);

        // B. Kartu Utama Berbingkai Putih
        ctx.fillStyle = '#ffffff';
        ctx.strokeStyle = '#e2e8f0';
        ctx.lineWidth = 3;
        drawRoundedRect(ctx, 35, 35, 770, 1110, 32, true, true);

        // C. Aksen Garis Atas Biru Rentify
        const topGrad = ctx.createLinearGradient(35, 35, 805, 35);
        topGrad.addColorStop(0, '#0284c7');
        topGrad.addColorStop(0.5, '#38bdf8');
        topGrad.addColorStop(1, '#6366f1');
        ctx.fillStyle = topGrad;
        drawRoundedRect(ctx, 35, 35, 770, 10, {tl: 32, tr: 32, br: 0, bl: 0}, true, false);

        // D. Header Merah QRIS Resmi Bank Indonesia
        const qrisHeaderGrad = ctx.createLinearGradient(65, 65, 775, 65);
        qrisHeaderGrad.addColorStop(0, '#b91c1c');
        qrisHeaderGrad.addColorStop(0.5, '#dc2626');
        qrisHeaderGrad.addColorStop(1, '#b91c1c');
        ctx.fillStyle = qrisHeaderGrad;
        drawRoundedRect(ctx, 65, 65, 710, 105, 20, true, false);

        // Teks QRIS Putih Tebal
        ctx.fillStyle = '#ffffff';
        ctx.font = '900 44px "Plus Jakarta Sans", Arial, sans-serif';
        ctx.textAlign = 'left';
        ctx.fillText('QRIS', 95, 122);

        // Subtitle QRIS
        ctx.fillStyle = '#ffffff';
        ctx.font = '700 13px "Plus Jakarta Sans", Arial, sans-serif';
        ctx.fillText('STANDAR PEMBAYARAN NASIONAL', 95, 146);

        ctx.fillStyle = '#fecaca';
        ctx.font = '600 11px "Plus Jakarta Sans", Arial, sans-serif';
        ctx.fillText('BANK INDONESIA • ASPI • GERBANG PEMBAYARAN NASIONAL', 95, 161);

        // Badge Rentify Verified di kanan header
        ctx.fillStyle = '#ffffff';
        drawRoundedRect(ctx, 610, 85, 145, 52, 14, true, false);

        ctx.fillStyle = '#0284c7';
        ctx.font = '900 18px "Plus Jakarta Sans", Arial, sans-serif';
        ctx.textAlign = 'center';
        ctx.fillText('Rentify', 682, 114);

        ctx.fillStyle = '#0284c7';
        ctx.font = '800 10px "Plus Jakarta Sans", Arial, sans-serif';
        ctx.fillText('✓ VERIFIED', 682, 128);

        // E. Info Merchant & Invoice
        ctx.textAlign = 'left';
        ctx.fillStyle = '#64748b';
        ctx.font = '800 12px "Plus Jakarta Sans", Arial, sans-serif';
        ctx.fillText('MERCHANT RESMI:', 65, 205);

        ctx.fillStyle = '#0f172a';
        ctx.font = '800 24px "Plus Jakarta Sans", Arial, sans-serif';
        ctx.fillText(merchantName, 65, 235);

        // Baris NMID & No. Pesanan
        ctx.fillStyle = '#64748b';
        ctx.font = '600 13px "Plus Jakarta Sans", Arial, sans-serif';
        ctx.fillText('NMID: ' + merchantNmid, 65, 260);

        ctx.textAlign = 'right';
        ctx.fillStyle = '#0284c7';
        ctx.font = '700 14px "Plus Jakarta Sans", monospace';
        ctx.fillText('Pesanan: ' + invoiceId, 775, 260);

        // Garis Pembatas Halus
        ctx.strokeStyle = '#e2e8f0';
        ctx.lineWidth = 1.5;
        ctx.beginPath();
        ctx.moveTo(65, 275);
        ctx.lineTo(775, 275);
        ctx.stroke();

        // F. BINGKAI QR CODE TENGAH (The Frame Highlight)
        // Posisi Frame Putih QR: x=170, y=295, w=500, h=500
        const frameX = 170;
        const frameY = 295;
        const frameW = 500;
        const frameH = 500;

        ctx.fillStyle = '#ffffff';
        ctx.strokeStyle = '#cbd5e1';
        ctx.lineWidth = 2.5;
        drawRoundedRect(ctx, frameX, frameY, frameW, frameH, 28, true, true);

        // 4 Viewfinder Corner Accents (Warna Biru Rentify)
        const bracketLen = 36;
        const bracketThick = 5;
        ctx.strokeStyle = '#0284c7';
        ctx.lineWidth = bracketThick;
        ctx.lineCap = 'round';

        // Top-Left
        ctx.beginPath();
        ctx.moveTo(frameX + 16, frameY + 16 + bracketLen);
        ctx.lineTo(frameX + 16, frameY + 16);
        ctx.lineTo(frameX + 16 + bracketLen, frameY + 16);
        ctx.stroke();

        // Top-Right
        ctx.beginPath();
        ctx.moveTo(frameX + frameW - 16 - bracketLen, frameY + 16);
        ctx.lineTo(frameX + frameW - 16, frameY + 16);
        ctx.lineTo(frameX + frameW - 16, frameY + 16 + bracketLen);
        ctx.stroke();

        // Bottom-Left
        ctx.beginPath();
        ctx.moveTo(frameX + 16, frameY + frameH - 16 - bracketLen);
        ctx.lineTo(frameX + 16, frameY + frameH - 16);
        ctx.lineTo(frameX + 16 + bracketLen, frameY + frameH - 16);
        ctx.stroke();

        // Bottom-Right
        ctx.beginPath();
        ctx.moveTo(frameX + frameW - 16 - bracketLen, frameY + frameH - 16);
        ctx.lineTo(frameX + frameW - 16, frameY + frameH - 16);
        ctx.lineTo(frameX + frameW - 16, frameY + frameH - 16 - bracketLen);
        ctx.stroke();

        // Gambar QR Code di tengah frame dengan margin aman
        const qrSize = 410;
        const qrPosX = frameX + (frameW - qrSize) / 2;
        const qrPosY = frameY + (frameH - qrSize) / 2;

        function finishAndDownload() {
            // G. Box Total Pembayaran di Bawah QR
            const totalBoxX = 65;
            const totalBoxY = 815;
            const totalBoxW = 710;
            const totalBoxH = 140;

            ctx.fillStyle = '#f0f9ff';
            ctx.strokeStyle = '#bae6fd';
            ctx.lineWidth = 2;
            drawRoundedRect(ctx, totalBoxX, totalBoxY, totalBoxW, totalBoxH, 20, true, true);

            ctx.textAlign = 'left';
            ctx.fillStyle = '#0369a1';
            ctx.font = '800 13px "Plus Jakarta Sans", Arial, sans-serif';
            ctx.fillText('TOTAL PEMBAYARAN', totalBoxX + 25, totalBoxY + 36);

            ctx.fillStyle = '#0f172a';
            ctx.font = '900 42px "Plus Jakarta Sans", Arial, sans-serif';
            ctx.fillText('Rp ' + formattedTotal, totalBoxX + 25, totalBoxY + 84);

            if (kodeUnik > 0) {
                ctx.fillStyle = '#0284c7';
                ctx.font = '700 13px "Plus Jakarta Sans", Arial, sans-serif';
                ctx.fillText('• Kode, Admin QRIS: +' + kodeUnik + ' (Harap transfer tepat sesuai nominal)', totalBoxX + 25, totalBoxY + 115);
            } else {
                ctx.fillStyle = '#475569';
                ctx.font = '600 13px "Plus Jakarta Sans", Arial, sans-serif';
                ctx.fillText('Pastikan nominal transfer tepat hingga digit terakhir.', totalBoxX + 25, totalBoxY + 115);
            }

            // H. Supported Apps & Footer
            ctx.textAlign = 'center';
            ctx.fillStyle = '#64748b';
            ctx.font = '800 12px "Plus Jakarta Sans", Arial, sans-serif';
            ctx.fillText('MENDUKUNG SEMUA BANK & E-WALLET DI INDONESIA:', 420, 990);

            ctx.fillStyle = '#334155';
            ctx.font = '700 13px "Plus Jakarta Sans", Arial, sans-serif';
            ctx.fillText('BCA • MANDIRI • BRI • BNI • GOPAY • DANA • OVO • SHOPEEPAY • LIVIN', 420, 1018);

            // Garis pembatas footer
            ctx.strokeStyle = '#f1f5f9';
            ctx.lineWidth = 1.5;
            ctx.beginPath();
            ctx.moveTo(100, 1045);
            ctx.lineTo(740, 1045);
            ctx.stroke();

            // Tanggal Cetak & Watermark Rentify
            const now = new Date();
            const dateStr = now.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }) + 
                            ', ' + String(now.getHours()).padStart(2, '0') + ':' + String(now.getMinutes()).padStart(2, '0') + ' WIB';

            ctx.fillStyle = '#94a3b8';
            ctx.font = '600 11px "Plus Jakarta Sans", Arial, sans-serif';
            ctx.fillText('Dicetak otomatis oleh Sistem Rentify (rentify21.my.id) • ' + dateStr, 420, 1080);

            // Trigger Download File PNG
            const link = document.createElement('a');
            link.download = 'QRIS-Rentify-' + invoiceId + '.png';
            link.href = canvas.toDataURL('image/png');
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);

            // Toast Notifikasi
            const toast = document.getElementById('copy-toast');
            const toastMsg = document.getElementById('copy-toast-msg');
            if (toast && toastMsg) {
                toastMsg.textContent = 'Gambar QRIS berbingkai berhasil disimpan!';
                toast.classList.add('show');
                setTimeout(() => toast.classList.remove('show'), 2500);
            }
        }

        // Gambar QR Code sumber
        if (srcCanvas) {
            ctx.drawImage(srcCanvas, qrPosX, qrPosY, qrSize, qrSize);
            finishAndDownload();
        } else if (srcImg && srcImg.complete && srcImg.naturalWidth > 0) {
            ctx.drawImage(srcImg, qrPosX, qrPosY, qrSize, qrSize);
            finishAndDownload();
        } else if (qrisImageUrl) {
            const tempImg = new Image();
            tempImg.crossOrigin = 'anonymous';
            tempImg.onload = function() {
                ctx.drawImage(tempImg, qrPosX, qrPosY, qrSize, qrSize);
                finishAndDownload();
            };
            tempImg.onerror = function() {
                // Fallback teks jika gambar eksternal gagal
                ctx.fillStyle = '#0f172a';
                ctx.font = '600 14px "Plus Jakarta Sans", Arial, sans-serif';
                ctx.textAlign = 'center';
                ctx.fillText('Gagal memuat QR matriks', frameX + frameW/2, frameY + frameH/2);
                finishAndDownload();
            };
            tempImg.src = qrisImageUrl;
        } else {
            alert('Matriks QR belum siap. Silakan tunggu beberapa detik dan coba kembali.');
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 4. MODAL PERBESAR QR (FULLSCREEN PREVIEW)
    // ─────────────────────────────────────────────────────────────────────────
    function openZoomModal() {
        const modal = document.getElementById('qr-zoom-modal');
        const target = document.getElementById('zoom-qr-target');
        const srcContainer = document.getElementById('qrcode-canvas-container');

        if (modal && target && srcContainer) {
            target.innerHTML = srcContainer.innerHTML;
            modal.classList.add('active');
        }
    }

    function closeZoomModal(e) {
        const modal = document.getElementById('qr-zoom-modal');
        if (modal) {
            modal.classList.remove('active');
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 5. COUNTDOWN BATAS WAKTU PEMBAYARAN
    // ─────────────────────────────────────────────────────────────────────────
    @php
        $targetIso = isset($paymentExpiredAt) ? $paymentExpiredAt->toIso8601String() : null;
    @endphp
    let targetTime = @if(!empty($targetIso)) new Date("{{ $targetIso }}").getTime() @else (new Date().getTime() + 24 * 60 * 60 * 1000) @endif;

    function updateCountdown() {
        let now = new Date().getTime();
        let distance = targetTime - now;
        const el = document.getElementById("countdown-timer");

        if (distance < 0) {
            if (el) {
                el.innerText = "00:00:00 (Habis)";
                el.parentElement.classList.remove('text-amber-800', 'bg-amber-50/90', 'border-amber-200');
                el.parentElement.classList.add('text-rose-800', 'bg-rose-50', 'border-rose-200');
            }
            return;
        }

        let hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        let minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        let seconds = Math.floor((distance % (1000 * 60)) / 1000);

        if (el) {
            el.innerText = String(hours).padStart(2, '0') + ":" + 
                           String(minutes).padStart(2, '0') + ":" + 
                           String(seconds).padStart(2, '0');
        }
    }
    setInterval(updateCountdown, 1000);
    updateCountdown();

    // ─────────────────────────────────────────────────────────────────────────
    // 6. POLLING OTOMATIS STATUS PEMBAYARAN (Setiap 3 Detik)
    // ─────────────────────────────────────────────────────────────────────────
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
                    statusEl.innerHTML = `
                        <div class="text-[13px] font-black text-sky-600 flex items-center gap-1.5 animate-bounce">
                            <i class="fa-solid fa-circle-check text-sky-600 text-sm"></i>
                            <span>Pembayaran Berhasil Dikonfirmasi!</span>
                        </div>
                        <p class="text-[11.5px] text-slate-500 mt-0.5">
                            Mengalihkan otomatis ke Struk Pesanan Anda...
                        </p>
                    `;
                }
                setTimeout(function() {
                    window.location.href = data.redirect_url || receiptUrl;
                }, 800);
            } else if (data.status === 'EXPIRED') {
                const statusEl = document.getElementById('status-indicator');
                if (statusEl) {
                    statusEl.innerHTML = `
                        <div class="text-[13px] font-black text-rose-400 flex items-center gap-1.5">
                            <i class="fa-solid fa-circle-exclamation text-rose-400 text-sm"></i>
                            <span>Batas Waktu Pembayaran Telah Habis</span>
                        </div>
                        <p class="text-[11px] text-rose-200 mt-0.5">
                            Pesanan telah kedaluwarsa. Silakan lakukan pemesanan ulang.
                        </p>
                    `;
                }
            }
        })
        .catch(function(e) {
            console.debug('Polling check error:', e);
        })
        .finally(function() {
            isChecking = false;
        });
    }

    // Jalankan polling otomatis tiap 3 detik
    setInterval(checkStatusLive, 3000);
    // Cek awal 1.5 detik setelah halaman terbuka
    setTimeout(checkStatusLive, 1500);

    // ─────────────────────────────────────────────────────────────────────────
    // 7. FUNGSI SIMULASI BAYAR (DEV / TEST MODE)
    // ─────────────────────────────────────────────────────────────────────────
    window.simulasikanBayar = function() {
        const statusEl = document.getElementById('status-indicator');
        if (statusEl) {
            statusEl.innerHTML = `
                <div class="text-[13px] font-black text-sky-400 flex items-center gap-1.5">
                    <i class="fa-solid fa-spinner fa-spin text-sky-400"></i>
                    <span>Menyimulasikan pembayaran mutasi rekening...</span>
                </div>
            `;
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
                    statusEl.innerHTML = `
                        <div class="text-[13px] font-black text-sky-600 flex items-center gap-1.5">
                            <i class="fa-solid fa-circle-check text-sky-600"></i>
                            <span>Simulasi Berhasil! Mengalihkan ke struk...</span>
                        </div>
                    `;
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
