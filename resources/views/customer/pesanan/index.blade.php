@extends('layouts.app')

@section('title', 'Pesanan — Rentify')

@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap');
    body { 
        font-family: 'Plus Jakarta Sans', sans-serif !important; 
        background: radial-gradient(circle at center, #fff9ef 0%, #bad6eb 100%) fixed !important;
    }
    .rentify-card {
        background: rgba(255, 255, 255, 0.9) !important;
        backdrop-filter: blur(20px) !important;
        -webkit-backdrop-filter: blur(20px) !important;
        border: 1px solid rgba(255, 255, 255, 0.85) !important;
        box-shadow: 0 10px 25px -5px rgba(14, 165, 233, 0.08), 0 8px 10px -6px rgba(0, 0, 0, 0.03) !important;
    }
    .no-scrollbar::-webkit-scrollbar { display: none; }
    .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>

<div class="min-h-screen bg-flowing pb-28 text-slate-800">

    <!-- Top Sticky Header -->
    <header class="rentify-navbar sticky top-0 z-40 bg-white/80 backdrop-blur-md border-b border-white/60 shadow-xs">
        <div class="max-w-2xl mx-auto px-4 h-14 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('customer.dashboard') }}" class="w-9 h-9 rounded-2xl bg-white/80 hover:bg-white text-slate-700 flex items-center justify-center shadow-xs border border-white/80 transition active:scale-95" aria-label="Kembali ke Profil">
                    <i class="fa-solid fa-arrow-left text-xs text-slate-600"></i>
                </a>
                <h1 class="text-base font-black text-slate-900 tracking-tight">Pesanan</h1>
            </div>
            <span class="text-xs font-black text-sky-700 bg-sky-50 px-3 py-1 rounded-full border border-sky-100 shadow-xs">
                {{ $countSemua ?? count($orders) }} Transaksi
            </span>
        </div>

        <!-- Tab Filter Status Pesanan (Rentify Design System) -->
        <div class="max-w-2xl mx-auto px-4 pb-2.5 overflow-x-auto no-scrollbar flex items-center gap-1.5 -webkit-overflow-scrolling-touch">
            <a href="{{ route('customer.pesanan', ['status' => 'semua']) }}" class="px-3.5 py-1.5 rounded-full text-xs whitespace-nowrap transition shadow-xs flex items-center gap-1 {{ ($statusFilter ?? 'semua') === 'semua' ? 'bg-gradient-to-r from-sky-400 to-sky-600 text-white font-black shadow-sky-500/25' : 'bg-white/80 text-slate-600 hover:text-sky-600 border border-white/80 font-bold backdrop-blur-sm shadow-2xs' }}">
                <span>Semua</span>
                @if(($countSemua ?? count($orders)) > 0)
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] font-black {{ ($statusFilter ?? 'semua') === 'semua' ? 'bg-white/20 text-white' : 'bg-sky-50 text-sky-700' }}">{{ $countSemua ?? count($orders) }}</span>
                @endif
            </a>
            <a href="{{ route('customer.pesanan', ['status' => 'belum_bayar']) }}" class="px-3.5 py-1.5 rounded-full text-xs whitespace-nowrap transition shadow-xs flex items-center gap-1 {{ ($statusFilter ?? '') === 'belum_bayar' ? 'bg-gradient-to-r from-sky-400 to-sky-600 text-white font-black shadow-sky-500/25' : 'bg-white/80 text-slate-600 hover:text-sky-600 border border-white/80 font-bold backdrop-blur-sm shadow-2xs' }}">
                <span>Belum Bayar</span>
                @if(($countBelumBayar ?? 0) > 0)
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] font-black {{ ($statusFilter ?? '') === 'belum_bayar' ? 'bg-white/20 text-white' : 'bg-amber-100 text-amber-700' }}">{{ $countBelumBayar }}</span>
                @endif
            </a>
            <a href="{{ route('customer.pesanan', ['status' => 'konfirmasi']) }}" class="px-3.5 py-1.5 rounded-full text-xs whitespace-nowrap transition shadow-xs flex items-center gap-1 {{ ($statusFilter ?? '') === 'konfirmasi' ? 'bg-gradient-to-r from-sky-400 to-sky-600 text-white font-black shadow-sky-500/25' : 'bg-white/80 text-slate-600 hover:text-sky-600 border border-white/80 font-bold backdrop-blur-sm shadow-2xs' }}">
                <span>Konfirmasi</span>
                @if(($countKonfirmasi ?? 0) > 0)
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] font-black {{ ($statusFilter ?? '') === 'konfirmasi' ? 'bg-white/20 text-white' : 'bg-sky-50 text-sky-700' }}">{{ $countKonfirmasi }}</span>
                @endif
            </a>
            <a href="{{ route('customer.pesanan', ['status' => 'siap_dikirim']) }}" class="px-3.5 py-1.5 rounded-full text-xs whitespace-nowrap transition shadow-xs flex items-center gap-1 {{ ($statusFilter ?? '') === 'siap_dikirim' ? 'bg-gradient-to-r from-sky-400 to-sky-600 text-white font-black shadow-sky-500/25' : 'bg-white/80 text-slate-600 hover:text-sky-600 border border-white/80 font-bold backdrop-blur-sm shadow-2xs' }}">
                <span>Siap Diambil/Dikirim</span>
                @if(($countSiap ?? 0) > 0)
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] font-black {{ ($statusFilter ?? '') === 'siap_dikirim' ? 'bg-white/20 text-white' : 'bg-sky-50 text-sky-700' }}">{{ $countSiap }}</span>
                @endif
            </a>
            <a href="{{ route('customer.pesanan', ['status' => 'berjalan']) }}" class="px-3.5 py-1.5 rounded-full text-xs whitespace-nowrap transition shadow-xs flex items-center gap-1 {{ ($statusFilter ?? '') === 'berjalan' ? 'bg-gradient-to-r from-sky-400 to-sky-600 text-white font-black shadow-sky-500/25' : 'bg-white/80 text-slate-600 hover:text-sky-600 border border-white/80 font-bold backdrop-blur-sm shadow-2xs' }}">
                <span>Berjalan</span>
                @if(($countBerjalan ?? 0) > 0)
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] font-black {{ ($statusFilter ?? '') === 'berjalan' ? 'bg-white/20 text-white' : 'bg-sky-50 text-sky-700' }}">{{ $countBerjalan }}</span>
                @endif
            </a>
            <a href="{{ route('customer.pesanan', ['status' => 'ulasan']) }}" class="px-3.5 py-1.5 rounded-full text-xs whitespace-nowrap transition shadow-xs flex items-center gap-1 {{ ($statusFilter ?? '') === 'ulasan' ? 'bg-gradient-to-r from-sky-400 to-sky-600 text-white font-black shadow-sky-500/25' : 'bg-white/80 text-slate-600 hover:text-sky-600 border border-white/80 font-bold backdrop-blur-sm shadow-2xs' }}">
                <span>Menunggu Ulasan</span>
                @if(($countUlasan ?? 0) > 0)
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] font-black {{ ($statusFilter ?? '') === 'ulasan' ? 'bg-white/20 text-white' : 'bg-amber-100 text-amber-700' }}">{{ $countUlasan }}</span>
                @endif
            </a>
            <a href="{{ route('customer.pesanan', ['status' => 'selesai']) }}" class="px-3.5 py-1.5 rounded-full text-xs whitespace-nowrap transition shadow-xs flex items-center gap-1 {{ ($statusFilter ?? '') === 'selesai' ? 'bg-gradient-to-r from-sky-400 to-sky-600 text-white font-black shadow-sky-500/25' : 'bg-white/80 text-slate-600 hover:text-sky-600 border border-white/80 font-bold backdrop-blur-sm shadow-2xs' }}">
                <span>Selesai</span>
                @if(($countSelesai ?? 0) > 0)
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] font-black {{ ($statusFilter ?? '') === 'selesai' ? 'bg-white/20 text-white' : 'bg-sky-50 text-sky-700' }}">{{ $countSelesai }}</span>
                @endif
            </a>
            <a href="{{ route('customer.pesanan', ['status' => 'dibatalkan']) }}" class="px-3.5 py-1.5 rounded-full text-xs whitespace-nowrap transition shadow-xs flex items-center gap-1 {{ ($statusFilter ?? '') === 'dibatalkan' ? 'bg-gradient-to-r from-sky-400 to-sky-600 text-white font-black shadow-sky-500/25' : 'bg-white/80 text-slate-600 hover:text-sky-600 border border-white/80 font-bold backdrop-blur-sm shadow-2xs' }}">
                <span>Dibatalkan</span>
                @if(($countDibatalkan ?? 0) > 0)
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] font-black {{ ($statusFilter ?? '') === 'dibatalkan' ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-600' }}">{{ $countDibatalkan }}</span>
                @endif
            </a>
        </div>
    </header>

    <main class="max-w-2xl mx-auto px-4 pt-4 space-y-4">
        @if(session('success'))
            <div class="p-3.5 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold flex items-center gap-2 shadow-xs">
                <i class="fa-solid fa-circle-check text-emerald-600 text-sm"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="p-3.5 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-bold flex items-center gap-2 shadow-xs">
                <i class="fa-solid fa-circle-exclamation text-rose-600 text-sm"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        @if($orders->isEmpty())
            <div class="rentify-card p-10 rounded-3xl text-center space-y-3 shadow-sm my-6">
                <div class="w-16 h-16 rounded-full bg-sky-50 text-sky-500 flex items-center justify-center text-2xl mx-auto shadow-inner">
                    <i class="fa-solid fa-receipt"></i>
                </div>
                <div class="space-y-1">
                    <h3 class="font-black text-slate-800 text-base">Belum Ada Transaksi</h3>
                    <p class="text-xs text-slate-500 max-w-xs mx-auto">Kamu belum memiliki pesanan dengan status ini. Mulai cari dan sewa barang impianmu!</p>
                </div>
                <div class="pt-2">
                    <a href="{{ route('customer.home') }}" class="inline-flex items-center gap-2 px-6 py-3 rounded-2xl bg-gradient-to-br from-sky-400 to-sky-600 hover:from-sky-500 hover:to-sky-700 text-white font-extrabold uppercase tracking-wider text-xs shadow-md transition transform hover:-translate-y-0.5 active:translate-y-0">
                        <span>Sewa Sekarang</span>
                        <i class="fa-solid fa-arrow-right text-xs"></i>
                    </a>
                </div>
            </div>
        @else
            @foreach($orders as $order)
                @php
                    $isBelumBayar = in_array($order->status, ['Menunggu Pembayaran', 'PENDING_PAYMENT', 'pending']);
                    $isSelesai = ($order->status === 'Selesai');
                    $isMenungguUlasan = ($order->status === 'Menunggu Ulasan');
                    $isDibatalkan = in_array($order->status, ['Dibatalkan', 'CANCELLED', 'EXPIRED']);
                    $isBerjalan = in_array($order->status, ['Sedang Disewa', 'Berjalan', 'PAID', 'dibayar', 'Dikonfirmasi']);

                    $orderUlasans = collect($order->ulasans ?? []);
                    $hasUlasan = $orderUlasans->isNotEmpty();

                    $statusBadgeClass = match(true) {
                        $isBelumBayar => 'bg-amber-50 text-amber-700 border-amber-200/80',
                        $isMenungguUlasan => 'bg-amber-50 text-amber-700 border-amber-200/80',
                        $isBerjalan => 'bg-sky-50 text-sky-700 border-sky-200/80',
                        $isSelesai => 'bg-sky-50 text-sky-700 border-sky-200/80',
                        $isDibatalkan => 'bg-slate-100 text-slate-500 border-slate-200',
                        default => 'bg-sky-50 text-sky-700 border-sky-200/80'
                    };

                    $waRaw = (string)($order->whatsapp_vendor ?? '081234567890');
                    $waClean = (string)preg_replace('/[^0-9]/', '', $waRaw);
                    if (str_starts_with($waClean, '0')) $waClean = '62' . substr($waClean, 1);

                    $namaTokoAsli = $order->vendor_name ?? $order->owner_name ?? 'Vendor Rentify';
                    $pesanWa = "Halo Toko *" . $namaTokoAsli . "*, saya ingin menanyakan status pesanan saya dengan ID: *INV-" . $order->id . "*. Terima kasih.";
                    $linkWa = "https://wa.me/" . $waClean . "?text=" . urlencode($pesanWa);

                    $waktuMulai = \Carbon\Carbon::parse($order->start_rent, 'Asia/Jakarta');
                    $waktuKembali = \Carbon\Carbon::parse($order->end_rent, 'Asia/Jakarta');
                @endphp

                <!-- Card Pesanan (Rentify Theme) -->
                <article class="rentify-card rounded-3xl overflow-hidden shadow-xs hover:shadow-md transition duration-200 border border-white/80">
                    <!-- Header Card: Info Toko & Status -->
                    <div class="p-4 bg-white/70 backdrop-blur-md border-b border-slate-100/80 flex items-center justify-between gap-2">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <div class="w-8 h-8 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center text-xs shrink-0 border border-sky-100/70 shadow-2xs">
                                <i class="fa-solid fa-store"></i>
                            </div>
                            <div class="min-w-0">
                                <span class="font-black text-xs text-slate-900 block truncate">{{ $namaTokoAsli }}</span>
                                <span class="text-[10px] text-slate-400 font-semibold tracking-tight">INV-{{ $order->id }} • {{ \Carbon\Carbon::parse($order->created_at)->format('d M Y, H:i') }}</span>
                            </div>
                        </div>
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider border shrink-0 flex items-center gap-1 {{ $statusBadgeClass }}">
                            @if($isSelesai)
                                <i class="fa-solid fa-circle-check text-sky-500 text-[10px]"></i>
                            @elseif($isBelumBayar)
                                <i class="fa-solid fa-clock text-amber-500 text-[10px]"></i>
                            @elseif($isMenungguUlasan)
                                <i class="fa-solid fa-star text-amber-500 text-[10px]"></i>
                            @elseif($isBerjalan)
                                <i class="fa-solid fa-arrows-rotate text-sky-500 text-[10px]"></i>
                            @endif
                            <span>{{ $order->status }}</span>
                        </span>
                    </div>

                    <!-- Body Card: Daftar Barang Sewa -->
                    <div class="p-4 space-y-3">
                        @foreach($order->items as $item)
                            @php
                                $itemUlasan = $orderUlasans->firstWhere('barang_id', $item->product_id);
                                $fotoItemUrl = !empty($item->cover_photo) 
                                    ? \App\Services\StorageService::getPublicUrl((string)$item->cover_photo) 
                                    : ((isset($item->barang) && !empty($item->barang->cover_photo_url)) 
                                        ? $item->barang->cover_photo_url 
                                        : asset('storage/placeholders/placeholder-barang.png'));
                            @endphp

                            <!-- Item Box -->
                            <div class="p-3.5 rounded-2xl bg-sky-50/50 backdrop-blur-xs border border-sky-100/70 space-y-2.5">
                                <div class="flex gap-3">
                                    <div class="w-16 h-16 rounded-2xl bg-white overflow-hidden shrink-0 border border-slate-200/70 flex items-center justify-center shadow-2xs">
                                        <img src="{{ $fotoItemUrl }}" alt="{{ $item->product_name }}" class="w-full h-full object-cover" onerror="this.onerror=null; this.src='{{ asset('storage/placeholders/placeholder-barang.png') }}';">
                                    </div>
                                    <div class="flex-1 min-w-0 space-y-1">
                                        <h4 class="font-black text-xs text-slate-800 leading-snug line-clamp-1">{{ $item->product_name }}</h4>
                                        <div class="flex items-center gap-2 text-[11px]">
                                            <span class="font-black text-sky-600">Rp {{ number_format($item->price * $item->quantity * $order->duration_days, 0, ',', '.') }}</span>
                                            <span class="text-slate-400 font-semibold">({{ $order->duration_days }} Hari • x{{ $item->quantity }})</span>
                                        </div>

                                        <div class="flex flex-wrap gap-1.5 pt-0.5">
                                            <span class="text-[9.5px] px-2 py-0.5 rounded-lg bg-white/90 text-sky-700 border border-sky-100 font-bold shadow-2xs">
                                                Jaminan: {{ $order->jaminan ?? 'KTP' }}
                                            </span>
                                            @if($item->denda_per_hari > 0)
                                                <span class="text-[9.5px] px-2 py-0.5 rounded-lg bg-amber-50 text-amber-700 border border-amber-100 font-bold">
                                                    Denda: Rp {{ number_format($item->denda_per_hari, 0, ',', '.') }}/hari
                                                </span>
                                            @endif
                                            @if($item->deposit > 0)
                                                <span class="text-[9.5px] px-2 py-0.5 rounded-lg bg-sky-50 text-sky-700 border border-sky-100 font-bold">
                                                    Deposit: Rp {{ number_format($item->deposit * $item->quantity, 0, ',', '.') }}
                                                </span>
                                            @endif
                                        </div>

                                        @if($order->shipping_method === 'ambil')
                                            @php
                                                $linkMaps = ($item->latitude && $item->longitude) ? "https://maps.google.com/?q={$item->latitude},{$item->longitude}" : "https://maps.google.com/?q=" . urlencode($item->alamat_toko ?? '');
                                            @endphp
                                            <div class="pt-0.5">
                                                <a href="{{ $linkMaps }}" target="_blank" class="inline-flex items-center gap-1 text-[10px] font-bold text-sky-600 hover:text-sky-700">
                                                    <i class="fa-solid fa-location-dot text-sky-500"></i> Lihat Lokasi Toko
                                                </a>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                @if($isSelesai || $isMenungguUlasan)
                                    @if($itemUlasan)
                                        <div class="p-2.5 rounded-xl bg-amber-50/80 border border-amber-200/70 flex items-start justify-between gap-2">
                                            <div class="min-w-0">
                                                <div class="flex items-center gap-1 text-amber-500 font-bold text-[11px]">
                                                    @for($s = 1; $s <= 5; $s++)
                                                        <i class="fa-solid fa-star {{ $s <= $itemUlasan->rating ? 'text-amber-400' : 'text-slate-200' }} text-[10px]"></i>
                                                    @endfor
                                                    <span class="text-slate-700 ml-1 font-black">{{ $itemUlasan->rating }}.0</span>
                                                    <span class="text-[9.5px] text-slate-400 font-medium ml-1">• Ulasanmu</span>
                                                </div>
                                                @if(!empty($itemUlasan->komentar))
                                                    <p class="text-slate-600 text-[11px] italic mt-1 line-clamp-2">"{{ $itemUlasan->komentar }}"</p>
                                                @endif
                                            </div>
                                            <button type="button" 
                                                onclick="openReviewModal('{{ $order->id }}', '{{ $item->product_id }}', '{{ addslashes($item->product_name) }}', '{{ $fotoItemUrl }}', {{ $itemUlasan->rating }}, '{{ addslashes($itemUlasan->komentar ?? '') }}')"
                                                class="text-[10px] font-black text-sky-600 hover:text-sky-700 underline shrink-0">
                                                Ubah
                                            </button>
                                        </div>
                                    @elseif($order->items->count() > 1)
                                        <div class="pt-0.5">
                                            <button type="button" 
                                                onclick="openReviewModal('{{ $order->id }}', '{{ $item->product_id }}', '{{ addslashes($item->product_name) }}', '{{ $fotoItemUrl }}', 5, '')"
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-sky-50 hover:bg-sky-100 text-sky-700 border border-sky-200/80 text-xs font-bold transition active:scale-95">
                                                <i class="fa-solid fa-star text-amber-400 text-xs"></i>
                                                <span>Ulas Barang Ini</span>
                                            </button>
                                        </div>
                                    @endif
                                @endif
                            </div>
                        @endforeach

                        <!-- Jadwal Pemakaian -->
                        <div class="bg-sky-50/70 rounded-2xl p-3 border border-sky-100/80 text-xs flex items-center justify-between gap-2 flex-wrap">
                            <div class="flex items-center gap-1.5 text-sky-900 font-extrabold">
                                <i class="fa-regular fa-calendar-check text-sky-500 text-sm"></i>
                                <span>Masa Sewa:</span>
                            </div>
                            <div class="text-[11px] font-bold text-slate-700 flex items-center gap-2">
                                <span>{{ $waktuMulai->format('d M (H:i)') }}</span>
                                <i class="fa-solid fa-arrow-right text-[9px] text-slate-400"></i>
                                <span>{{ $waktuKembali->format('d M Y (H:i)') }}</span>
                            </div>
                        </div>

                        <!-- Ringkasan Biaya & Metode -->
                        <div class="pt-2.5 border-t border-slate-100/80 flex items-center justify-between">
                            <div>
                                <span class="text-[10px] text-slate-400 block font-semibold">Pengiriman: {{ strtoupper($order->shipping_method) }} (Rp {{ number_format($order->shipping_fee, 0, ',', '.') }})</span>
                                <span class="text-xs font-black text-slate-700">Total Tagihan <span class="text-slate-400 font-normal">({{ $order->payment_method }})</span></span>
                            </div>
                            <div class="text-right">
                                <span class="text-base font-black text-sky-600">Rp {{ number_format($order->total_price, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Footer Card: Action Buttons (Tema Rentify Standard) -->
                    <div class="p-3.5 bg-white/70 backdrop-blur-md border-t border-slate-100/80 flex items-center justify-between gap-2 flex-wrap">
                        <div class="flex items-center gap-1.5 flex-wrap">
                            <a href="{{ $linkWa }}" target="_blank" class="px-3.5 py-2 rounded-2xl bg-white hover:bg-emerald-50 text-slate-700 hover:text-emerald-700 font-extrabold text-xs flex items-center gap-1.5 border border-slate-200/80 shadow-xs transition active:scale-95">
                                <i class="fa-brands fa-whatsapp text-emerald-500 text-sm"></i>
                                <span>Chat Toko</span>
                            </a>

                            @if($order->shipping_method === 'ambil')
                                @php
                                    $firstItemWithLoc = $order->items->first();
                                    $linkMapsFooter = ($firstItemWithLoc && $firstItemWithLoc->latitude && $firstItemWithLoc->longitude) 
                                        ? "https://maps.google.com/?q={$firstItemWithLoc->latitude},{$firstItemWithLoc->longitude}" 
                                        : "https://maps.google.com/?q=" . urlencode($firstItemWithLoc->alamat_toko ?? '');
                                @endphp
                                <a href="{{ $linkMapsFooter }}" target="_blank" class="px-3.5 py-2 rounded-2xl bg-sky-50 hover:bg-sky-100 text-sky-700 font-extrabold text-xs flex items-center gap-1.5 border border-sky-200/80 shadow-xs transition active:scale-95" title="Buka Titik Lokasi Toko di Google Maps">
                                    <i class="fa-solid fa-location-dot text-sky-600 text-xs"></i>
                                    <span>Maps Toko</span>
                                </a>
                            @endif
                        </div>

                        <div class="flex items-center gap-2">
                            @if($order->status === 'Menunggu Pembayaran' || $order->status === 'PENDING_PAYMENT')
                                <a href="{{ route('customer.qris', 'INV-' . $order->id) }}" class="px-4 py-2 rounded-2xl bg-gradient-to-r from-sky-400 to-sky-600 hover:from-sky-500 hover:to-sky-700 text-white font-extrabold text-xs flex items-center gap-1.5 shadow-sm shadow-sky-500/20 active:scale-95 transition">
                                    <i class="fa-solid fa-credit-card text-xs"></i>
                                    <span>Bayar Sekarang</span>
                                </a>
                            @elseif(in_array($order->status, ['Sedang Disewa', 'Berjalan']))
                                <form action="{{ route('customer.pesanan.selesai', $order->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin barang sudah diterima & selesai disewa?');">
                                    @csrf
                                    <button type="submit" class="px-4 py-2 rounded-2xl bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 text-white font-extrabold text-xs flex items-center gap-1.5 shadow-sm shadow-emerald-500/20 active:scale-95 transition">
                                        <i class="fa-solid fa-check text-xs"></i>
                                        <span>Selesaikan Pesanan</span>
                                    </button>
                                </form>
                            @elseif($isMenungguUlasan)
                                @php
                                    $firstItem = $order->items->first();
                                    $firstPhoto = ($firstItem && isset($firstItem->barang) && !empty($firstItem->barang->cover_photo_url)) 
                                        ? $firstItem->barang->cover_photo_url 
                                        : (($firstItem && !empty($firstItem->cover_photo)) 
                                            ? \App\Services\StorageService::getPublicUrl((string)$firstItem->cover_photo) 
                                            : asset('storage/placeholders/placeholder-barang.png'));
                                    $firstItemName = $firstItem ? $firstItem->product_name : 'Barang Rental';
                                    $firstItemId = $firstItem ? $firstItem->product_id : '';
                                    $firstUlasan = $orderUlasans->first();
                                @endphp
                                <button type="button" 
                                    onclick="openReviewModal('{{ $order->id }}', '{{ $firstItemId }}', '{{ addslashes($firstItemName) }}', '{{ $firstPhoto }}', {{ $firstUlasan ? $firstUlasan->rating : 5 }}, '{{ addslashes($firstUlasan->komentar ?? '') }}')"
                                    class="px-4 py-2 rounded-2xl bg-gradient-to-r from-sky-400 to-sky-600 hover:from-sky-500 hover:to-sky-700 text-white font-extrabold text-xs flex items-center gap-1.5 shadow-sm shadow-sky-500/20 active:scale-95 transition transform hover:-translate-y-0.5">
                                    <i class="fa-solid fa-star text-amber-300 text-xs"></i>
                                    <span>Beri Ulasan</span>
                                </button>
                            @elseif($isSelesai)
                                @php
                                    $firstItem = $order->items->first();
                                    $firstPhoto = ($firstItem && isset($firstItem->barang) && !empty($firstItem->barang->cover_photo_url)) 
                                        ? $firstItem->barang->cover_photo_url 
                                        : (($firstItem && !empty($firstItem->cover_photo)) 
                                            ? \App\Services\StorageService::getPublicUrl((string)$firstItem->cover_photo) 
                                            : asset('storage/placeholders/placeholder-barang.png'));
                                    $firstItemName = $firstItem ? $firstItem->product_name : 'Barang Rental';
                                    $firstItemId = $firstItem ? ($firstItem->product_id ?? '') : '';
                                @endphp
                                @if($hasUlasan)
                                    @if($firstItemId)
                                        <a href="{{ route('customer.barang.show', $firstItemId) }}" class="px-4 py-2 rounded-2xl bg-sky-50 hover:bg-sky-100 text-sky-700 font-extrabold text-xs flex items-center gap-1.5 border border-sky-200/80 shadow-xs active:scale-95 transition transform hover:-translate-y-0.5">
                                            <i class="fa-solid fa-rotate-right text-xs"></i>
                                            <span>Sewa Lagi</span>
                                        </a>
                                    @endif
                                @else
                                    <button type="button" 
                                        onclick="openReviewModal('{{ $order->id }}', '{{ $firstItemId }}', '{{ addslashes($firstItemName) }}', '{{ $firstPhoto }}', 5, '')"
                                        class="px-4 py-2 rounded-2xl bg-gradient-to-r from-sky-400 to-sky-600 hover:from-sky-500 hover:to-sky-700 text-white font-extrabold text-xs flex items-center gap-1.5 shadow-sm shadow-sky-500/20 active:scale-95 transition transform hover:-translate-y-0.5">
                                        <i class="fa-solid fa-star text-amber-300 text-xs"></i>
                                        <span>Beri Ulasan</span>
                                    </button>
                                @endif
                            @else
                                <span class="text-xs font-bold text-slate-400 flex items-center gap-1 px-3 py-1.5 bg-slate-100/90 rounded-2xl border border-slate-200/80">
                                    Dibatalkan
                                </span>
                            @endif
                        </div>
                    </div>
                </article>
            @endforeach
        @endif
    </main>

    <!-- Modal Beri / Ubah Ulasan -->
    <div id="review-modal" class="hidden fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm transition-opacity">
        <div class="rentify-card w-full max-w-md overflow-hidden transform scale-100 transition-all rounded-3xl bg-white shadow-2xl">
            <!-- Modal Header -->
            <div class="bg-gradient-to-r from-sky-400 to-sky-600 px-6 py-4 flex items-center justify-between text-white">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center">
                        <i class="fa-solid fa-star text-amber-300 text-sm"></i>
                    </div>
                    <div>
                        <h3 class="font-black text-sm tracking-tight" id="review-modal-title">Beri Ulasan Barang</h3>
                        <p class="text-[11px] text-sky-100 font-medium">Bantu penyewa lain dengan penilaian jujurmu</p>
                    </div>
                </div>
                <button type="button" onclick="closeReviewModal()" class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center text-white transition">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>

            <!-- Form Ulasan -->
            <form id="review-form" method="POST" action="" class="p-6 space-y-4">
                @csrf
                <input type="hidden" name="barang_id" id="review-barang-id" value="">
                <input type="hidden" name="rating" id="review-rating-input" value="5">

                <!-- Product Preview Card -->
                <div class="flex items-center gap-3 p-3 rounded-2xl bg-slate-50 border border-slate-100">
                    <div class="w-12 h-12 rounded-xl bg-slate-200 overflow-hidden shrink-0 border border-slate-200/80 flex items-center justify-center" id="review-product-photo-box">
                        <img id="review-product-photo" src="" alt="Produk" class="w-full h-full object-cover">
                        <i id="review-product-icon" class="fa-solid fa-box text-slate-400 text-base hidden"></i>
                    </div>
                    <div class="min-w-0 flex-1">
                        <h4 class="font-bold text-xs text-slate-800 truncate" id="review-product-name">Nama Barang</h4>
                        <span class="text-[10px] text-sky-600 font-semibold" id="review-order-code">INV-123</span>
                    </div>
                </div>

                <!-- Star Rating Picker -->
                <div class="text-center py-1">
                    <label class="block text-xs font-bold text-slate-700 mb-2">Berapa bintang untuk kualitas barang ini?</label>
                    <div class="flex items-center justify-center gap-2 text-3xl" id="star-rating-container">
                        @for($i = 1; $i <= 5; $i++)
                            <button type="button" class="star-btn transition transform hover:scale-125 focus:outline-hidden text-amber-400" data-star="{{ $i }}">
                                <i class="fa-solid fa-star"></i>
                            </button>
                        @endfor
                    </div>
                    <div class="mt-2 text-xs font-extrabold text-amber-600" id="star-rating-label">
                        ⭐⭐⭐⭐⭐ Luar Biasa!
                    </div>
                </div>

                <!-- Textarea Testimoni -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5 flex items-center justify-between">
                        <span>Ceritakan Pengalaman Sewamu</span>
                        <span class="text-[10px] text-slate-400 font-normal"><span id="char-count">0</span>/1000</span>
                    </label>
                    <textarea 
                        name="komentar" 
                        id="review-komentar" 
                        rows="4" 
                        maxlength="1000"
                        placeholder="Bagaimana kondisi fisik & fungsi barang? Apakah bersih dan sesuai deskripsi? Apakah respon pemilik toko memuaskan?"
                        class="w-full text-xs p-3 rounded-2xl border border-slate-200 focus:ring-2 focus:ring-sky-400 focus:border-transparent placeholder-slate-400 resize-none transition leading-relaxed"></textarea>
                </div>

                <!-- Footer Buttons -->
                <div class="flex items-center gap-2 pt-1">
                    <button type="button" onclick="closeReviewModal()" class="w-1/3 py-2.5 rounded-2xl border border-slate-200 text-slate-600 font-bold text-xs hover:bg-slate-50 transition active:scale-95">
                        Batal
                    </button>
                    <button type="submit" id="btn-submit-review" class="w-2/3 py-2.5 rounded-2xl bg-gradient-to-r from-sky-400 to-sky-600 hover:from-sky-500 hover:to-sky-700 text-white font-extrabold text-xs shadow-md shadow-sky-500/20 transition transform hover:-translate-y-0.5 active:translate-y-0 flex items-center justify-center gap-2">
                        <i class="fa-solid fa-paper-plane text-xs"></i>
                        <span>Kirim Ulasan</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Script Modal & Star Rating -->
    <script>
        const ratingLabels = {
            1: '⭐ Sangat Mengecewakan',
            2: '⭐⭐ Kurang Memuaskan',
            3: '⭐⭐⭐ Cukup Baik',
            4: '⭐⭐⭐⭐ Memuaskan & Sesuai',
            5: '⭐⭐⭐⭐⭐ Luar Biasa! Sangat Direkomendasikan'
        };

        let selectedRating = 5;

        function setStars(rating) {
            selectedRating = parseInt(rating);
            document.getElementById('review-rating-input').value = selectedRating;
            document.getElementById('star-rating-label').textContent = ratingLabels[selectedRating] || '';

            const starButtons = document.querySelectorAll('#star-rating-container .star-btn');
            starButtons.forEach(btn => {
                const starVal = parseInt(btn.getAttribute('data-star'));
                const icon = btn.querySelector('i');
                if (starVal <= selectedRating) {
                    btn.classList.add('text-amber-400');
                    btn.classList.remove('text-slate-200');
                    icon.classList.remove('fa-regular');
                    icon.classList.add('fa-solid');
                } else {
                    btn.classList.remove('text-amber-400');
                    btn.classList.add('text-slate-200');
                }
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            const starButtons = document.querySelectorAll('#star-rating-container .star-btn');
            starButtons.forEach(btn => {
                btn.addEventListener('click', function() {
                    const starVal = this.getAttribute('data-star');
                    setStars(starVal);
                });
            });

            const komentarInput = document.getElementById('review-komentar');
            const charCount = document.getElementById('char-count');
            if (komentarInput && charCount) {
                komentarInput.addEventListener('input', function() {
                    charCount.textContent = this.value.length;
                });
            }
        });

        function openReviewModal(orderId, barangId, barangNama, barangFoto, currentRating = 5, currentKomentar = '') {
            const form = document.getElementById('review-form');
            form.action = '{{ url("/customer/ulasan") }}/' + orderId;

            document.getElementById('review-barang-id').value = barangId || '';
            document.getElementById('review-product-name').textContent = barangNama || 'Barang Rental';
            document.getElementById('review-order-code').textContent = 'Pesanan INV-' + orderId;

            const photoImg = document.getElementById('review-product-photo');
            const photoIcon = document.getElementById('review-product-icon');
            if (barangFoto && barangFoto.trim() !== '') {
                photoImg.src = barangFoto;
                photoImg.classList.remove('hidden');
                photoIcon.classList.add('hidden');
            } else {
                photoImg.classList.add('hidden');
                photoIcon.classList.remove('hidden');
            }

            const komentarEl = document.getElementById('review-komentar');
            komentarEl.value = currentKomentar || '';
            document.getElementById('char-count').textContent = (currentKomentar || '').length;

            const modalTitle = document.getElementById('review-modal-title');
            if (currentKomentar || (currentRating && currentRating !== 5)) {
                modalTitle.textContent = 'Ubah Ulasan Barang';
            } else {
                modalTitle.textContent = 'Beri Ulasan Barang';
            }

            setStars(currentRating || 5);

            const modal = document.getElementById('review-modal');
            modal.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
        }

        function closeReviewModal() {
            const modal = document.getElementById('review-modal');
            modal.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }
    </script>

    <!-- Bottom Navigation Bar Mobile (3 Tab Ramping) -->
    <x-bottom-navigation-bar active="akun" />
</div>
@endsection
