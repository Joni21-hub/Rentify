@extends('layouts.vendor')

@section('title', 'Katalog Produk — Vendor Rentify')

@section('content')
<div class="px-3.5 sm:px-6 lg:px-8 py-4 sm:py-6 max-w-7xl mx-auto space-y-4 sm:space-y-6">

    <!-- Header Bersih & Profesional -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-white p-4 sm:p-5 rounded-2xl sm:rounded-3xl border border-slate-200/80 shadow-xs">
        <div>
            <h1 class="text-base sm:text-xl font-black text-slate-800 tracking-tight">Katalog Produk</h1>
            <p class="text-[11px] sm:text-xs text-slate-400 font-medium mt-0.5">{{ count($barangs) }} produk terdaftar di toko Anda</p>
        </div>
        
        <a href="{{ route('vendor.barang.create') }}" class="inline-flex items-center justify-center gap-1.5 px-4 py-2.5 bg-gradient-to-r from-sky-500 to-blue-600 hover:from-sky-600 text-white rounded-xl text-xs font-black shadow-sm transition active:scale-95">
            <i class="fa-solid fa-plus text-xs"></i>
            <span>Tambah Produk</span>
        </a>
    </div>

    @if(session('success'))
        <div class="px-4 py-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl flex items-center gap-2.5 text-xs font-bold shadow-xs">
            <i class="fa-solid fa-circle-check text-emerald-600 text-sm"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <!-- Toolbar Pencarian & Filter -->
    <form method="GET" action="{{ route('vendor.barang.index') }}" class="flex flex-col sm:flex-row gap-2.5">
        <div class="relative flex-1">
            <i class="fa-solid fa-magnifying-glass text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2 text-xs"></i>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama produk..." class="w-full pl-9 pr-4 py-2.5 bg-white border border-slate-200/80 rounded-xl text-xs font-semibold text-slate-800 placeholder-slate-400 focus:outline-none focus:border-sky-500 shadow-xs transition">
        </div>
        <div class="flex gap-2">
            <select name="status" class="flex-1 sm:flex-none px-3 py-2.5 bg-white border border-slate-200/80 rounded-xl text-xs font-semibold text-slate-700 focus:outline-none focus:border-sky-500 shadow-xs transition">
                <option value="">Semua Status</option>
                <option value="disetujui" {{ request('status') == 'disetujui' ? 'selected' : '' }}>Aktif</option>
                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Ditinjau</option>
            </select>
            <button type="submit" class="px-4 py-2.5 bg-slate-800 hover:bg-slate-900 text-white text-xs font-bold rounded-xl shadow-xs transition shrink-0">
                Filter
            </button>
        </div>
    </form>

    <!-- LIST PRODUK: CARD-BASED UNTUK HP (MOBILE FIRST) -->
    <div class="block lg:hidden space-y-2.5">
        @forelse($barangs as $barang)
            @php
                $imgUrl = $barang->cover_photo ?? ($barang->fotos->first()->foto_path ?? null);
                if ($imgUrl && !str_starts_with($imgUrl, 'http')) {
                    $imgUrl = asset(str_replace('public/', '', $imgUrl));
                }
            @endphp
            <div class="bg-white p-3.5 rounded-2xl border border-slate-200/80 shadow-xs space-y-3">
                <div class="flex items-start gap-3">
                    <div class="w-16 h-16 rounded-xl bg-slate-100 overflow-hidden shrink-0 border border-slate-200">
                        @if($imgUrl)
                            <img src="{{ $imgUrl }}" alt="{{ $barang->nama }}" class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full flex items-center justify-center text-slate-300">
                                <i class="fa-solid fa-image text-lg"></i>
                            </div>
                        @endif
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between gap-1 mb-0.5">
                            <span class="text-[10px] font-bold text-sky-600 bg-sky-50 px-2 py-0.5 rounded-md truncate">
                                {{ $barang->kategori->nama ?? 'Umum' }}
                            </span>
                            @if($barang->is_approved == 1 || $barang->status_barang == 'disetujui')
                                <span class="text-[9px] font-black text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full shrink-0">
                                    Aktif
                                </span>
                            @else
                                <span class="text-[9px] font-black text-amber-700 bg-amber-50 px-2 py-0.5 rounded-full shrink-0">
                                    Ditinjau
                                </span>
                            @endif
                        </div>
                        <h3 class="text-xs font-black text-slate-800 truncate">{{ $barang->nama }}</h3>
                        <p class="text-xs font-black text-slate-900 mt-1">
                            Rp {{ number_format($barang->harga_sewa_harian, 0, ',', '.') }}<span class="text-[10px] font-normal text-slate-400">/hari</span>
                        </p>
                    </div>
                </div>

                <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-xs">
                    <span class="text-[11px] font-bold text-slate-500">
                        Stok: <strong class="text-slate-800">{{ $barang->stok_total }} unit</strong>
                    </span>
                    <div class="flex items-center gap-1.5">
                        <a href="{{ route('vendor.barang.show', $barang->id) }}" class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-[11px] font-bold transition">
                            Detail
                        </a>
                        <form action="{{ route('vendor.barang.destroy', $barang->id) }}" method="POST" onsubmit="return confirm('Hapus barang ini secara permanen?');" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="px-2.5 py-1 bg-rose-50 hover:bg-rose-100 text-rose-600 rounded-lg text-[11px] font-bold transition">
                                Hapus
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="bg-white p-8 rounded-2xl border border-slate-200/80 text-center text-slate-400">
                <i class="fa-solid fa-box-open text-3xl mb-2 text-slate-300"></i>
                <p class="text-xs font-medium">Belum ada produk di etalase toko.</p>
            </div>
        @endforelse
    </div>

    <!-- LIST PRODUK: TABLE UNTUK DESKTOP (LAYAR BESAR) -->
    <div class="hidden lg:block bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
        <table class="w-full text-left text-xs">
            <thead>
                <tr class="border-b border-slate-100 bg-slate-50/50 text-slate-400 font-black uppercase text-[10px] tracking-wider">
                    <th class="px-6 py-4">Produk</th>
                    <th class="px-6 py-4">Tarif Sewa</th>
                    <th class="px-6 py-4">Stok Fisik</th>
                    <th class="px-6 py-4">Status</th>
                    <th class="px-6 py-4 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($barangs as $barang)
                    @php
                        $imgUrl = $barang->cover_photo ?? ($barang->fotos->first()->foto_path ?? null);
                        if ($imgUrl && !str_starts_with($imgUrl, 'http')) {
                            $imgUrl = asset(str_replace('public/', '', $imgUrl));
                        }
                    @endphp
                    <tr class="hover:bg-slate-50/60 transition">
                        <td class="px-6 py-3.5">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 rounded-xl bg-slate-100 overflow-hidden shrink-0 border border-slate-200">
                                    @if($imgUrl)
                                        <img src="{{ $imgUrl }}" alt="{{ $barang->nama }}" class="w-full h-full object-cover">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center text-slate-300">
                                            <i class="fa-solid fa-image text-sm"></i>
                                        </div>
                                    @endif
                                </div>
                                <div class="min-w-0">
                                    <h3 class="font-black text-slate-800 truncate text-xs">{{ $barang->nama }}</h3>
                                    <span class="text-[10px] font-bold text-sky-600">{{ $barang->kategori->nama ?? 'Tanpa Kategori' }}</span>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-3.5 font-black text-slate-800">
                            Rp {{ number_format($barang->harga_sewa_harian, 0, ',', '.') }}<span class="text-[10px] font-normal text-slate-400">/hari</span>
                        </td>
                        <td class="px-6 py-3.5 font-bold text-slate-700">
                            {{ $barang->stok_total }} unit
                        </td>
                        <td class="px-6 py-3.5">
                            @if($barang->is_approved == 1 || $barang->status_barang == 'disetujui')
                                <span class="inline-block px-2.5 py-1 rounded-full text-[10px] font-black bg-emerald-50 text-emerald-700">
                                    Aktif
                                </span>
                            @else
                                <span class="inline-block px-2.5 py-1 rounded-full text-[10px] font-black bg-amber-50 text-amber-700">
                                    Ditinjau
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-3.5 text-right">
                            <div class="flex items-center justify-end gap-1.5">
                                <a href="{{ route('vendor.barang.show', $barang->id) }}" class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-bold transition">
                                    Detail
                                </a>
                                <form action="{{ route('vendor.barang.destroy', $barang->id) }}" method="POST" onsubmit="return confirm('Hapus barang ini?');" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-2.5 py-1 bg-rose-50 hover:bg-rose-100 text-rose-600 rounded-lg text-xs font-bold transition">
                                        Hapus
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-slate-400">
                            <i class="fa-solid fa-box-open text-3xl mb-2 text-slate-300"></i>
                            <p class="text-xs font-medium">Belum ada produk di etalase toko.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>
@endsection