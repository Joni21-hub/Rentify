@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
    nav, header, footer, .navbar, .header, .top-bar, .footer, #footer { display: none !important; }
    body {
        background: radial-gradient(circle at 50% 0%, #d1fae5 0%, #f0fdf4 40%, #f1f5f9 100%) fixed;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
        margin: 0;
        min-height: 100vh;
    }

    @keyframes pop-in {
        0% { transform: scale(0.5); opacity: 0; }
        80% { transform: scale(1.1); }
        100% { transform: scale(1); opacity: 1; }
    }
    @keyframes slide-up {
        from { transform: translateY(30px); opacity: 0; }
        to   { transform: translateY(0);    opacity: 1; }
    }
    @keyframes confetti-fall {
        0%   { transform: translateY(-10px) rotate(0deg);   opacity: 1; }
        100% { transform: translateY(100px) rotate(360deg); opacity: 0; }
    }

    .icon-success { animation: pop-in 0.6s cubic-bezier(.36, .07, .19, .97) both; }
    .slide-up { animation: slide-up 0.5s ease both; }
    .slide-up-2 { animation: slide-up 0.5s ease 0.15s both; }
    .slide-up-3 { animation: slide-up 0.5s ease 0.3s both; }

    .confetti-wrap {
        position: fixed; top: 0; left: 0; width: 100%; pointer-events: none; overflow: hidden; z-index: 999;
    }
    .confetti-piece {
        position: absolute; width: 10px; height: 10px; top: -20px; border-radius: 2px;
        animation: confetti-fall 2.5s ease-in forwards;
    }

    .receipt-row {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        padding: 10px 0;
        border-bottom: 1px dashed #e2e8f0;
        font-size: 13px;
    }
    .receipt-row:last-child { border-bottom: none; }
    .receipt-label { color: #64748b; font-weight: 500; }
    .receipt-value { color: #0f172a; font-weight: 700; text-align: right; max-width: 60%; }
</style>

{{-- Konfeti --}}
<div class="confetti-wrap" id="confetti-wrap"></div>

@php
    // Ambil data pesanan dari DB berdasarkan ID
    $orderIds = [];
    foreach (explode('_', $id ?? '') as $part) {
        if (str_starts_with($part, 'INV-')) {
            $orderIds[] = (int) str_replace('INV-', '', $part);
        } elseif (is_numeric($part)) {
            $orderIds[] = (int) $part;
        }
    }
    $orders     = !empty($orderIds) ? \Illuminate\Support\Facades\DB::table('orders')->whereIn('id', $orderIds)->get() : collect();
    $firstOrder = $orders->first();
    $total      = $orders->sum('total_biaya') ?: $orders->sum('total_price');

    $itemNames = [];
    foreach ($orders as $order) {
        $items = \Illuminate\Support\Facades\DB::table('order_items')->where('order_id', $order->id)->get();
        foreach ($items as $item) {
            $itemNames[] = $item->product_name ?? 'Barang Sewa';
        }
    }
    $namaBarang = implode(', ', array_unique($itemNames)) ?: 'Barang Sewa Rentify';

    $tanggalMulai   = $firstOrder ? \Carbon\Carbon::parse($firstOrder->tanggal_mulai ?? $firstOrder->start_rent)->format('d M Y') : '-';
    $tanggalSelesai = $firstOrder ? \Carbon\Carbon::parse($firstOrder->tanggal_selesai ?? $firstOrder->end_rent)->format('d M Y') : '-';
    $paidAtStr      = $firstOrder && $firstOrder->paid_at
        ? \Carbon\Carbon::parse($firstOrder->paid_at)->setTimezone('Asia/Jakarta')->format('d M Y, H:i')
        : now('Asia/Jakarta')->format('d M Y, H:i');
    $paymentMethod  = $firstOrder->payment_method ?? 'DOKU';
    $nomorPesanan   = !empty($orderIds) ? 'INV-' . implode('_INV-', $orderIds) : ($id ?? '-');
@endphp

<div class="max-w-lg mx-auto px-4 py-10">

    {{-- Ikon Sukses --}}
    <div class="flex flex-col items-center text-center mb-6 slide-up">
        <div class="w-24 h-24 rounded-full bg-emerald-100 flex items-center justify-center mb-4 icon-success shadow-lg shadow-emerald-200/60">
            <i class="fa-solid fa-circle-check text-emerald-500 text-5xl"></i>
        </div>
        <h1 class="text-2xl font-black text-slate-800 mb-1">Pembayaran Berhasil!</h1>
        <p class="text-sm text-slate-500">Terima kasih! Pesanan Anda telah dikonfirmasi.</p>
    </div>

    {{-- Kartu Ringkasan Transaksi --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden mb-5 slide-up-2">

        {{-- Stripe atas --}}
        <div class="h-1.5 bg-gradient-to-r from-emerald-400 via-sky-400 to-indigo-400"></div>

        <div class="px-5 py-5">
            <div class="text-xs font-extrabold text-slate-400 uppercase tracking-wider mb-4">
                Ringkasan Transaksi
            </div>

            <div class="receipt-row">
                <span class="receipt-label">No. Pesanan</span>
                <span class="receipt-value font-mono text-sky-700">{{ $nomorPesanan }}</span>
            </div>

            <div class="receipt-row">
                <span class="receipt-label">Barang Sewa</span>
                <span class="receipt-value">{{ \Illuminate\Support\Str::limit($namaBarang, 60) }}</span>
            </div>

            <div class="receipt-row">
                <span class="receipt-label">Tanggal Rental</span>
                <span class="receipt-value">{{ $tanggalMulai }} – {{ $tanggalSelesai }}</span>
            </div>

            <div class="receipt-row">
                <span class="receipt-label">Metode Bayar</span>
                <span class="receipt-value">{{ strtoupper($paymentMethod) }}</span>
            </div>

            <div class="receipt-row">
                <span class="receipt-label">Waktu Pembayaran</span>
                <span class="receipt-value">{{ $paidAtStr }} WIB</span>
            </div>

            <div class="receipt-row border-none pt-4">
                <span class="text-base font-black text-slate-800">Total Bayar</span>
                <span class="text-xl font-black text-emerald-600">Rp {{ number_format($total, 0, ',', '.') }}</span>
            </div>
        </div>

        {{-- Status badge --}}
        <div class="px-5 pb-4">
            <div class="bg-emerald-50 border border-emerald-200 rounded-xl px-4 py-2.5 flex items-center gap-2.5">
                <i class="fa-solid fa-circle-check text-emerald-500"></i>
                <div>
                    <div class="text-xs font-extrabold text-emerald-700">Pembayaran Diterima</div>
                    <div class="text-[11px] text-emerald-600">Vendor sedang memproses pesanan Anda</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Tombol Aksi --}}
    <div class="space-y-3 slide-up-3">
        <a href="{{ route('customer.pesanan') }}"
           class="w-full bg-gradient-to-r from-emerald-500 to-emerald-600 hover:from-emerald-600 hover:to-emerald-700 text-white font-black py-3.5 rounded-xl shadow-md transition-all active:scale-95 flex items-center justify-center gap-2">
            <i class="fa-solid fa-bag-shopping"></i>
            Lihat Semua Pesanan
        </a>

        @if($firstOrder)
        <a href="{{ route('customer.pesanan.pdf', $firstOrder->id) }}"
           class="w-full bg-white border-2 border-slate-200 hover:border-sky-400 text-slate-700 hover:text-sky-600 font-bold py-3.5 rounded-xl transition-all active:scale-95 flex items-center justify-center gap-2 text-sm">
            <i class="fa-solid fa-file-pdf text-rose-500"></i>
            Unduh Invoice PDF
        </a>
        @endif

        <a href="{{ route('customer.home') }}"
           class="w-full text-center text-xs text-slate-400 hover:text-slate-600 py-2 block transition">
            Kembali ke Beranda
        </a>
    </div>

</div>

<script>
    // Konfeti animasi
    (function () {
        const colors = ['#10b981', '#3b82f6', '#f59e0b', '#ec4899', '#8b5cf6', '#ef4444'];
        const wrap   = document.getElementById('confetti-wrap');
        if (!wrap) return;

        for (let i = 0; i < 60; i++) {
            const el = document.createElement('div');
            el.className = 'confetti-piece';
            el.style.left            = Math.random() * 100 + 'vw';
            el.style.background      = colors[Math.floor(Math.random() * colors.length)];
            el.style.width           = (6 + Math.random() * 8) + 'px';
            el.style.height          = (6 + Math.random() * 8) + 'px';
            el.style.animationDelay  = (Math.random() * 1.5) + 's';
            el.style.animationDuration = (2 + Math.random() * 1.5) + 's';
            el.style.borderRadius    = Math.random() > 0.5 ? '50%' : '2px';
            wrap.appendChild(el);
        }

        // Bersihkan setelah animasi selesai
        setTimeout(() => wrap.remove(), 5000);
    })();
</script>

@endsection
