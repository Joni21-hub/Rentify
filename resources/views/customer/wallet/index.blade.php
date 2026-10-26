@extends('layouts.app')

@section('title', 'Dompet Koin — Rentify')

@push('head')
<style>
    body {
        background: radial-gradient(circle at center, #fff9ef 0%, #bad6eb 100%) fixed !important;
        font-family: 'Plus Jakarta Sans', sans-serif;
    }
</style>
@endpush

@section('content')
<div class="min-h-screen bg-flowing pb-28 text-slate-800">
    <!-- Top Bar -->
    <header class="rentify-navbar sticky top-0 z-30 bg-white/80 backdrop-blur-md border-b border-white/60 shadow-xs">
        <div class="max-w-md mx-auto px-4 h-14 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('customer.dashboard') }}" class="w-8 h-8 rounded-xl bg-white/80 hover:bg-white text-slate-700 flex items-center justify-center shadow-xs border border-white/80 transition" aria-label="Kembali ke Profil">
                    <i class="fa-solid fa-arrow-left text-xs text-slate-600"></i>
                </a>
                <h1 class="font-black text-base text-slate-900 tracking-tight">Dompet Koin</h1>
            </div>
            <div class="flex items-center gap-1.5 px-3 py-1 rounded-full bg-amber-50 border border-amber-200 text-amber-800 text-xs font-bold shadow-xs">
                <i class="fa-solid fa-coins text-amber-500"></i>
                <span>1 Koin = Rp 100</span>
            </div>
        </div>
    </header>

    <main class="max-w-md mx-auto px-4 py-5 space-y-4">
        <!-- Saldo Utama Card (Rentify Theme Gradient) -->
        <div class="bg-gradient-to-br from-sky-400 via-sky-500 to-sky-600 rounded-3xl p-6 text-white shadow-xl shadow-sky-500/20 relative overflow-hidden">
            <div class="absolute -right-6 -bottom-6 w-32 h-32 rounded-full bg-white/10 pointer-events-none"></div>
            <div class="absolute right-10 -top-8 w-24 h-24 rounded-full bg-sky-300/15 pointer-events-none"></div>

            <div class="relative z-10 space-y-3.5">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-xl bg-white/15 flex items-center justify-center text-amber-300 text-base">
                            <i class="fa-solid fa-wallet"></i>
                        </div>
                        <span class="text-xs font-bold text-sky-100">Saldo Koin Aktif</span>
                    </div>
                    <span class="text-[10px] font-extrabold uppercase px-2.5 py-0.5 rounded-full bg-white/20 text-white tracking-wide">
                        Reward Momen
                    </span>
                </div>

                <div>
                    <div class="flex items-baseline gap-2">
                        <span class="text-4xl font-black tracking-tight text-white">{{ number_format($coinBalance ?? 0, 0, ',', '.') }}</span>
                        <span class="text-amber-300 font-extrabold text-lg">Koin</span>
                    </div>
                    <p class="text-xs text-sky-100 mt-1">
                        Senilai <span class="font-extrabold text-white">Rp {{ number_format(($coinBalance ?? 0) * 100, 0, ',', '.') }}</span> potongan checkout
                    </p>
                </div>

                <div class="pt-2 border-t border-white/15 flex items-center justify-between text-xs text-sky-100">
                    <span class="flex items-center gap-1.5 text-[11px]">
                        <i class="fa-solid fa-shield-check text-emerald-300"></i>
                        <span>Otomatis potong tagihan rental</span>
                    </span>
                    <a href="{{ route('customer.home') }}" class="font-bold text-white hover:underline flex items-center gap-1 text-[11px]">
                        Sewa Barang <i class="fa-solid fa-chevron-right text-[8px]"></i>
                    </a>
                </div>
            </div>
        </div>

        @if(($deficitBalance ?? 0) > 0)
        <!-- Defisit Debt Alert -->
        <div class="bg-rose-50 border border-rose-200 rounded-2xl p-4 text-xs text-rose-800 space-y-1 shadow-xs">
            <div class="flex items-center gap-2 font-bold text-rose-900">
                <i class="fa-solid fa-triangle-exclamation text-rose-600 text-sm"></i>
                <span>Tercatat Utang Defisit: {{ $deficitBalance }} Koin (Rp {{ number_format($deficitBalance * 100, 0, ',', '.') }})</span>
            </div>
            <p class="text-rose-700 leading-relaxed text-[11px]">
                Defisit terjadi karena koin reward yang telah dibelanjakan mengalami pembatalan like. Reward like berikutnya akan melunasi defisit ini terlebih dahulu.
            </p>
        </div>
        @endif

        <!-- Ringkas: Informasi Aturan Koin (Compact Tooltip Style) -->
        <div class="flex items-center gap-2.5 px-4 py-2.5 rounded-2xl bg-white/70 border border-slate-200/60 text-[11px] text-slate-600 shadow-xs">
            <i class="fa-solid fa-circle-info text-sky-500 shrink-0"></i>
            <span class="leading-relaxed">Potongan koin s/d 25% dari subtotal sewa (min. transaksi Rp 50.000). 100% kembali jika pesanan batal.</span>
        </div>

        <!-- Riwayat Mutasi Buku Besar Koin -->
        <div class="rentify-card p-5 rounded-3xl space-y-3 shadow-xs">
            <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                <h3 class="font-black text-xs uppercase tracking-wider text-slate-800">Riwayat Mutasi Koin</h3>
                <span class="text-[10px] text-slate-400 font-semibold">{{ $mutations->total() ?? 0 }} Transaksi</span>
            </div>

            @if($mutations->isEmpty())
                <div class="py-8 text-center space-y-2">
                    <div class="w-12 h-12 rounded-full bg-sky-50 text-sky-500 flex items-center justify-center mx-auto text-lg shadow-inner">
                        <i class="fa-solid fa-clock-rotate-left"></i>
                    </div>
                    <p class="text-xs font-bold text-slate-700">Belum Ada Riwayat Koin</p>
                    <p class="text-[11px] text-slate-400 max-w-xs mx-auto">Unggah momen seru sewa barangmu di tab Momen dan dapatkan reward koin dari setiap like!</p>
                    <div class="pt-2">
                        <a href="{{ route('customer.moments') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-2xl bg-gradient-to-br from-sky-400 to-sky-600 hover:from-sky-500 hover:to-sky-700 text-white font-extrabold uppercase tracking-wider text-xs shadow-md transition transform hover:-translate-y-0.5 active:translate-y-0">
                            <i class="fa-solid fa-camera text-xs"></i> Buka Momen
                        </a>
                    </div>
                </div>
            @else
                <div class="divide-y divide-slate-100 space-y-2">
                    @foreach($mutations as $m)
                        <div class="pt-2 flex items-center justify-between gap-3 text-xs">
                            <div class="flex items-center gap-2.5 min-w-0">
                                @php
                                    $isPositive = $m->coins_amount > 0;
                                    $iconData = match($m->interaction_type) {
                                        'like_mint' => ['fa-solid fa-heart', 'text-rose-500', 'bg-rose-50'],
                                        'unlike_deduct' => ['fa-solid fa-heart-crack', 'text-slate-500', 'bg-slate-100'],
                                        'relike_restore' => ['fa-solid fa-heart-circle-bolt', 'text-amber-500', 'bg-amber-50'],
                                        'checkout_spend' => ['fa-solid fa-bag-shopping', 'text-sky-600', 'bg-sky-50'],
                                        'refund' => ['fa-solid fa-rotate-left', 'text-emerald-600', 'bg-emerald-50'],
                                        'post_deletion_clawback' => ['fa-solid fa-trash-can', 'text-rose-500', 'bg-rose-50'],
                                        default => ['fa-solid fa-coins', 'text-amber-500', 'bg-amber-50']
                                    };
                                    $label = match($m->interaction_type) {
                                        'like_mint' => 'Reward Like Momen',
                                        'unlike_deduct' => 'Penyesuaian Batal Like',
                                        'relike_restore' => 'Pemulihan Suka Momen',
                                        'checkout_spend' => 'Diskon Checkout Pesanan',
                                        'refund' => 'Pengembalian Pesanan Batal',
                                        'post_deletion_clawback' => 'Penarikan Momen Dihapus',
                                        default => 'Mutasi Koin'
                                    };
                                @endphp
                                <div class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0 {{ $iconData[1] }} {{ $iconData[2] }}">
                                    <i class="{{ $iconData[0] }} text-xs"></i>
                                </div>
                                <div class="min-w-0">
                                    <span class="font-bold text-slate-800 block text-[11px] truncate">{{ $label }}</span>
                                    <span class="text-[10px] text-slate-400">{{ $m->created_at->format('d M Y, H:i') }}</span>
                                </div>
                            </div>
                            <div class="text-right shrink-0">
                                <span class="font-black text-xs {{ $isPositive ? 'text-emerald-600' : 'text-slate-700' }}">
                                    {{ $isPositive ? '+' : '' }}{{ $m->coins_amount }} Koin
                                </span>
                                <span class="block text-[9.5px] text-slate-400 font-medium">
                                    {{ $isPositive ? '+' : '' }}Rp {{ number_format(abs($m->coins_amount) * 100, 0, ',', '.') }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>

                @if($mutations->hasPages())
                <div class="pt-3">
                    {{ $mutations->links() }}
                </div>
                @endif
            @endif
        </div>
    </main>

    <!-- Bottom Navigation Bar (3 Tab Ramping) -->
    <x-bottom-navigation-bar active="akun" />
</div>
@endsection
