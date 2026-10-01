@extends('layouts.vendor')

@section('title', 'Pesanan Masuk — Vendor Rentify')

@section('content')
<div class="px-3.5 sm:px-6 lg:px-8 py-4 sm:py-6 max-w-7xl mx-auto space-y-4 sm:space-y-6">

    <!-- Header Bersih & Profesional -->
    <div class="flex items-center justify-between bg-white p-4 sm:p-5 rounded-2xl sm:rounded-3xl border border-slate-200/80 shadow-xs">
        <div>
            <h1 class="text-base sm:text-xl font-black text-slate-800 tracking-tight">Pesanan Masuk</h1>
            <p class="text-[11px] sm:text-xs text-slate-400 font-medium mt-0.5">Kelola seluruh transaksi sewa berdasarkan status pesanan</p>
        </div>
        <span class="px-3 py-1 bg-sky-50 text-sky-700 text-xs font-black rounded-xl">
            {{ count($pesananMasuk) }} Transaksi
        </span>
    </div>

    @if(session('success'))
        <div class="px-4 py-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl flex items-center gap-2.5 text-xs font-bold shadow-xs">
            <i class="fa-solid fa-circle-check text-emerald-600 text-sm"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <!-- TAB FILTER STATUS PESANAN (ALA TOKOPEDIA / SHOPEE SELLER) -->
    <div class="flex gap-2 overflow-x-auto pb-1 text-xs">
        <!-- 1. Semua -->
        <a href="{{ route('vendor.pesanan.index', ['status' => 'semua']) }}" class="px-3 py-2 rounded-xl shrink-0 transition flex items-center gap-1.5 {{ ($statusFilter ?? 'semua') === 'semua' ? 'bg-slate-900 text-white font-black shadow-xs' : 'bg-white border border-slate-200/80 text-slate-600 font-bold hover:bg-slate-50' }}">
            <span>Semua</span>
            <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ ($statusFilter ?? 'semua') === 'semua' ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-600' }}">{{ $countSemua ?? count($pesananMasuk) }}</span>
        </a>

        <!-- 2. Perlu Diproses -->
        <a href="{{ route('vendor.pesanan.index', ['status' => 'menunggu']) }}" class="px-3 py-2 rounded-xl shrink-0 transition flex items-center gap-1.5 {{ ($statusFilter ?? '') === 'menunggu' ? 'bg-amber-500 text-white font-black shadow-xs' : 'bg-white border border-slate-200/80 text-slate-600 font-bold hover:bg-slate-50' }}">
            <span>Perlu Diproses</span>
            @if(($countMenunggu ?? 0) > 0)
                <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ ($statusFilter ?? '') === 'menunggu' ? 'bg-white text-amber-700' : 'bg-amber-100 text-amber-700' }} font-black">{{ $countMenunggu }}</span>
            @endif
        </a>

        <!-- 3. Sedang Disewa -->
        <a href="{{ route('vendor.pesanan.index', ['status' => 'disewa']) }}" class="px-3 py-2 rounded-xl shrink-0 transition flex items-center gap-1.5 {{ ($statusFilter ?? '') === 'disewa' ? 'bg-sky-600 text-white font-black shadow-xs' : 'bg-white border border-slate-200/80 text-slate-600 font-bold hover:bg-slate-50' }}">
            <span>Sedang Disewa</span>
            <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ ($statusFilter ?? '') === 'disewa' ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-600' }}">{{ $countDisewa ?? 0 }}</span>
        </a>

        <!-- 4. Jatuh Tempo -->
        <a href="{{ route('vendor.pesanan.index', ['status' => 'jatuh_tempo']) }}" class="px-3 py-2 rounded-xl shrink-0 transition flex items-center gap-1.5 {{ ($statusFilter ?? '') === 'jatuh_tempo' ? 'bg-rose-600 text-white font-black shadow-xs' : 'bg-white border border-slate-200/80 text-slate-600 font-bold hover:bg-slate-50' }}">
            <span>Jatuh Tempo</span>
            @if(($countJatuhTempo ?? 0) > 0)
                <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ ($statusFilter ?? '') === 'jatuh_tempo' ? 'bg-white text-rose-700' : 'bg-rose-100 text-rose-700' }} font-black">{{ $countJatuhTempo }}</span>
            @endif
        </a>

        <!-- 5. Selesai -->
        <a href="{{ route('vendor.pesanan.index', ['status' => 'selesai']) }}" class="px-3 py-2 rounded-xl shrink-0 transition flex items-center gap-1.5 {{ ($statusFilter ?? '') === 'selesai' ? 'bg-emerald-600 text-white font-black shadow-xs' : 'bg-white border border-slate-200/80 text-slate-600 font-bold hover:bg-slate-50' }}">
            <span>Selesai</span>
            <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ ($statusFilter ?? '') === 'selesai' ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-600' }}">{{ $countSelesai ?? 0 }}</span>
        </a>

        <!-- 6. Dibatalkan -->
        <a href="{{ route('vendor.pesanan.index', ['status' => 'dibatalkan']) }}" class="px-3 py-2 rounded-xl shrink-0 transition flex items-center gap-1.5 {{ ($statusFilter ?? '') === 'dibatalkan' ? 'bg-slate-600 text-white font-black shadow-xs' : 'bg-white border border-slate-200/80 text-slate-600 font-bold hover:bg-slate-50' }}">
            <span>Dibatalkan</span>
            <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ ($statusFilter ?? '') === 'dibatalkan' ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-600' }}">{{ $countBatal ?? 0 }}</span>
        </a>
    </div>

    <!-- LIST PESANAN: CARD-BASED UNTUK HP (MOBILE FIRST) -->
    <div class="block lg:hidden space-y-3">
        @forelse($pesananMasuk as $pesanan)
            @php
                $st = strtolower($pesanan->status);
                $badgeClass = 'bg-slate-100 text-slate-600';
                if (in_array($st, ['selesai'])) $badgeClass = 'bg-emerald-50 text-emerald-700 border border-emerald-200';
                elseif (in_array($st, ['menunggu konfirmasi', 'pending'])) $badgeClass = 'bg-amber-50 text-amber-700 border border-amber-200';
                elseif (in_array($st, ['disetujui', 'sedang disewa', 'berjalan'])) $badgeClass = 'bg-sky-50 text-sky-700 border border-sky-200';
                elseif (in_array($st, ['dibatalkan', 'ditolak'])) $badgeClass = 'bg-rose-50 text-rose-700 border border-rose-200';
            @endphp
            <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs space-y-3">
                <div class="flex items-center justify-between gap-2 border-b border-slate-100 pb-2.5">
                    <div class="flex items-center gap-2 min-w-0">
                        <span class="font-black text-xs text-slate-800">#ORD-{{ $pesanan->id }}</span>
                        <span class="text-[10px] text-slate-400 font-medium truncate">• {{ \Carbon\Carbon::parse($pesanan->created_at)->format('d M Y, H:i') }}</span>
                    </div>
                    <span class="inline-block px-2 py-0.5 rounded text-[10px] font-black {{ $badgeClass }} shrink-0">
                        {{ ucfirst($pesanan->status) }}
                    </span>
                </div>

                <div class="space-y-1.5">
                    <div class="flex items-center justify-between text-xs">
                        <span class="font-bold text-slate-700">{{ $pesanan->customer_name ?? $pesanan->customer->name ?? 'Pelanggan' }}</span>
                        <span class="text-[10px] font-bold text-slate-500 uppercase px-2 py-0.5 bg-slate-100 rounded-md">
                            {{ $pesanan->payment_method ?? 'COD' }}
                        </span>
                    </div>

                    @if($pesanan->details && $pesanan->details->count() > 0)
                        <p class="text-xs text-slate-600 truncate font-medium">
                            <i class="fa-solid fa-box text-[10px] text-slate-400 mr-1"></i>
                            {{ $pesanan->details->first()->barang->nama ?? 'Produk Rental' }}
                            @if($pesanan->details->count() > 1)
                                <span class="text-sky-600 font-bold">+{{ $pesanan->details->count() - 1 }} lainnya</span>
                            @endif
                        </p>
                    @endif

                    <div class="text-[11px] text-slate-500 flex items-center gap-1 font-medium">
                        <i class="fa-regular fa-calendar text-sky-500 text-[10px]"></i>
                        <span>{{ \Carbon\Carbon::parse($pesanan->start_rent ?? $pesanan->tanggal_mulai)->format('d M') }} — {{ \Carbon\Carbon::parse($pesanan->end_rent ?? $pesanan->tanggal_selesai)->format('d M Y') }}</span>
                    </div>
                </div>

                <div class="pt-2.5 border-t border-slate-100 flex items-center justify-between">
                    <div>
                        <span class="text-[10px] text-slate-400 font-bold uppercase block">Total Biaya</span>
                        <span class="text-sm font-black text-slate-800">
                            Rp {{ number_format($pesanan->total_biaya ?? $pesanan->total_price ?? 0, 0, ',', '.') }}
                        </span>
                    </div>
                    <a href="{{ route('vendor.pesanan.show', $pesanan->id) }}" class="px-3 py-1.5 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-xs font-black shadow-xs transition">
                        Detail Pesanan &rarr;
                    </a>
                </div>
            </div>
        @empty
            <div class="bg-white p-8 rounded-2xl border border-slate-200/80 text-center text-slate-400">
                <i class="fa-solid fa-clipboard-list text-3xl mb-2 text-slate-300"></i>
                <p class="text-xs font-medium">Tidak ada pesanan dengan status ini.</p>
                @if(($statusFilter ?? 'semua') !== 'semua')
                    <a href="{{ route('vendor.pesanan.index', ['status' => 'semua']) }}" class="mt-2 inline-block text-xs font-bold text-sky-600 hover:underline">
                        Lihat Semua Pesanan &rarr;
                    </a>
                @endif
            </div>
        @endforelse
    </div>

    <!-- LIST PESANAN: TABLE UNTUK DESKTOP (LAYAR BESAR) -->
    <div class="hidden lg:block bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
        <table class="w-full text-left text-xs">
            <thead>
                <tr class="border-b border-slate-100 bg-slate-50/50 text-slate-400 font-black uppercase text-[10px] tracking-wider">
                    <th class="px-6 py-4">ID & Tanggal</th>
                    <th class="px-6 py-4">Customer & Durasi</th>
                    <th class="px-6 py-4">Metode Bayar</th>
                    <th class="px-6 py-4">Total Biaya</th>
                    <th class="px-6 py-4">Status</th>
                    <th class="px-6 py-4 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($pesananMasuk as $pesanan)
                    @php
                        $st = strtolower($pesanan->status);
                        $badgeClass = 'bg-slate-100 text-slate-600';
                        if (in_array($st, ['selesai'])) $badgeClass = 'bg-emerald-50 text-emerald-700';
                        elseif (in_array($st, ['menunggu konfirmasi', 'pending'])) $badgeClass = 'bg-amber-50 text-amber-700';
                        elseif (in_array($st, ['disetujui', 'sedang disewa', 'berjalan'])) $badgeClass = 'bg-sky-50 text-sky-700';
                        elseif (in_array($st, ['dibatalkan', 'ditolak'])) $badgeClass = 'bg-rose-50 text-rose-700';
                    @endphp
                    <tr class="hover:bg-slate-50/60 transition">
                        <td class="px-6 py-3.5">
                            <span class="font-black text-slate-800 text-xs">#ORD-{{ $pesanan->id }}</span>
                            <p class="text-[10px] text-slate-400 mt-0.5">{{ \Carbon\Carbon::parse($pesanan->created_at)->format('d M Y, H:i') }}</p>
                        </td>
                        <td class="px-6 py-3.5">
                            <div class="font-bold text-slate-700 text-xs">{{ $pesanan->customer_name ?? $pesanan->customer->name ?? 'Pelanggan' }}</div>
                            <div class="text-[10px] text-slate-400 mt-0.5">
                                {{ \Carbon\Carbon::parse($pesanan->start_rent ?? $pesanan->tanggal_mulai)->format('d M') }} — {{ \Carbon\Carbon::parse($pesanan->end_rent ?? $pesanan->tanggal_selesai)->format('d M Y') }}
                            </div>
                        </td>
                        <td class="px-6 py-3.5">
                            <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-700">
                                {{ $pesanan->payment_method ?? 'COD' }}
                            </span>
                        </td>
                        <td class="px-6 py-3.5 font-black text-slate-800">
                            Rp {{ number_format($pesanan->total_biaya ?? $pesanan->total_price ?? 0, 0, ',', '.') }}
                        </td>
                        <td class="px-6 py-3.5">
                            <span class="inline-block px-2.5 py-1 rounded-full text-[10px] font-black {{ $badgeClass }}">
                                {{ ucfirst($pesanan->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-3.5 text-right">
                            <a href="{{ route('vendor.pesanan.show', $pesanan->id) }}" class="px-3 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-bold transition">
                                Detail
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-slate-400">
                            <i class="fa-solid fa-clipboard-list text-3xl mb-2 text-slate-300"></i>
                            <p class="text-xs font-medium">Tidak ada pesanan dalam status ini.</p>
                            @if(($statusFilter ?? 'semua') !== 'semua')
                                <a href="{{ route('vendor.pesanan.index', ['status' => 'semua']) }}" class="mt-2 inline-block text-xs font-bold text-sky-600 hover:underline">
                                    Lihat Semua Pesanan &rarr;
                                </a>
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>
@endsection