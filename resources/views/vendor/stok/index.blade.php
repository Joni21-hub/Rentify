@extends('layouts.vendor')

@section('title', 'Kelola Stok Unit — Vendor Rentify')

@section('content')
<div class="px-3.5 sm:px-6 lg:px-8 py-4 sm:py-6 max-w-7xl mx-auto space-y-4 sm:space-y-6">

    <!-- Header Bersih & Profesional -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-white p-4 sm:p-5 rounded-2xl sm:rounded-3xl border border-slate-200/80 shadow-xs">
        <div>
            <h1 class="text-base sm:text-xl font-black text-slate-800 tracking-tight">Kelola Stok Unit Rental</h1>
            <p class="text-[11px] sm:text-xs text-slate-400 font-medium mt-0.5">Perbarui ketersediaan unit fisik produk rental Anda dengan cepat</p>
        </div>
        
        <div class="flex items-center gap-2">
            <a href="{{ route('vendor.barang.index') }}" class="inline-flex items-center justify-center gap-1.5 px-3.5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition">
                <i class="fa-solid fa-box-open text-xs"></i>
                <span>Katalog Produk</span>
            </a>
        </div>
    </div>

    <!-- Alert Notifikasi -->
    @if(session('success'))
        <div class="px-4 py-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl flex items-center gap-2.5 text-xs font-bold shadow-xs">
            <i class="fa-solid fa-circle-check text-emerald-600 text-sm"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if($errors->any())
        <div class="px-4 py-3 bg-rose-50 border border-rose-200 text-rose-800 rounded-2xl text-xs font-bold shadow-xs">
            {{ $errors->first() }}
        </div>
    @endif

    <!-- Toolbar Pencarian & Filter -->
    <form method="GET" action="{{ route('vendor.stok') }}" class="flex flex-col sm:flex-row gap-2.5">
        <div class="relative flex-1">
            <i class="fa-solid fa-magnifying-glass text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2 text-xs"></i>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama produk..." class="w-full pl-9 pr-4 py-2.5 bg-white border border-slate-200/80 rounded-xl text-xs font-semibold text-slate-800 placeholder-slate-400 focus:outline-none focus:border-sky-500 shadow-xs transition">
        </div>
        <div class="flex gap-2">
            <select name="kategori_id" class="flex-1 sm:flex-none px-3 py-2.5 bg-white border border-slate-200/80 rounded-xl text-xs font-semibold text-slate-700 focus:outline-none focus:border-sky-500 shadow-xs transition">
                <option value="">Semua Kategori</option>
                @foreach($kategoris as $kat)
                    <option value="{{ $kat->id }}" {{ request('kategori_id') == $kat->id ? 'selected' : '' }}>{{ $kat->nama }}</option>
                @endforeach
            </select>
            <button type="submit" class="px-4 py-2.5 bg-slate-800 hover:bg-slate-900 text-white text-xs font-bold rounded-xl shadow-xs transition shrink-0">
                Filter
            </button>
        </div>
    </form>

    <form action="{{ route('vendor.stok.update') }}" method="POST" id="formBulkStok">
        @csrf
        @method('PUT')

        <!-- LIST PRODUK: TABLE UNTUK DESKTOP (LAYAR BESAR) -->
        <div class="hidden lg:block bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50/50 text-slate-400 font-black uppercase text-[10px] tracking-wider">
                        <th class="px-6 py-4">Produk</th>
                        <th class="px-6 py-4">Kategori</th>
                        <th class="px-6 py-4">Status Stok</th>
                        <th class="px-6 py-4">Jumlah Stok Fisik</th>
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
                                        <span class="text-[10px] text-slate-400">Rp {{ number_format($barang->harga_sewa_harian, 0, ',', '.') }}/hari</span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-3.5 font-bold text-sky-600">
                                {{ $barang->kategori->nama ?? 'Umum' }}
                            </td>
                            <td class="px-6 py-3.5">
                                @if($barang->stok_total > 2)
                                    <span class="inline-block px-2.5 py-1 rounded-full text-[10px] font-black bg-emerald-50 text-emerald-700">
                                        Tersedia
                                    </span>
                                @elseif($barang->stok_total > 0)
                                    <span class="inline-block px-2.5 py-1 rounded-full text-[10px] font-black bg-amber-50 text-amber-700">
                                        Menipis ({{ $barang->stok_total }})
                                    </span>
                                @else
                                    <span class="inline-block px-2.5 py-1 rounded-full text-[10px] font-black bg-rose-50 text-rose-700">
                                        Habis
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-3.5">
                                <div class="flex items-center gap-2">
                                    <input type="number" name="stocks[{{ $barang->id }}]" value="{{ $barang->stok_total }}" min="0" class="w-24 px-3 py-1.5 rounded-xl border border-slate-200 text-xs font-black text-slate-800 focus:outline-none focus:border-sky-500 shadow-xs">
                                    <span class="text-slate-400 text-[11px] font-medium">unit</span>
                                </div>
                            </td>
                            <td class="px-6 py-3.5 text-right">
                                <a href="{{ route('vendor.barang.show', $barang->id) }}" class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-bold transition">
                                    Lihat Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-slate-400">
                                <i class="fa-solid fa-box-open text-3xl mb-2 text-slate-300"></i>
                                <p class="text-xs font-medium">Tidak ada produk ditemukan.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            @if(count($barangs) > 0)
                <div class="p-4 bg-slate-50/70 border-t border-slate-100 flex items-center justify-between">
                    <span class="text-xs text-slate-500 font-semibold">Ubah jumlah stok pada kolom di atas, lalu klik simpan.</span>
                    <button type="submit" class="px-5 py-2.5 bg-sky-600 hover:bg-sky-700 text-white rounded-xl text-xs font-black shadow-sm transition active:scale-95">
                        <i class="fa-solid fa-floppy-disk mr-1.5"></i> Simpan Semua Perubahan Stok
                    </button>
                </div>
            @endif
        </div>

        <!-- LIST PRODUK: CARD-BASED UNTUK MOBILE -->
        <div class="block lg:hidden space-y-3">
            @forelse($barangs as $barang)
                @php
                    $imgUrl = $barang->cover_photo ?? ($barang->fotos->first()->foto_path ?? null);
                    if ($imgUrl && !str_starts_with($imgUrl, 'http')) {
                        $imgUrl = asset(str_replace('public/', '', $imgUrl));
                    }
                @endphp
                <div class="bg-white p-3.5 rounded-2xl border border-slate-200/80 shadow-xs space-y-3">
                    <div class="flex items-start gap-3">
                        <div class="w-14 h-14 rounded-xl bg-slate-100 overflow-hidden shrink-0 border border-slate-200">
                            @if($imgUrl)
                                <img src="{{ $imgUrl }}" alt="{{ $barang->nama }}" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-slate-300">
                                    <i class="fa-solid fa-image text-base"></i>
                                </div>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <span class="text-[10px] font-bold text-sky-600 bg-sky-50 px-2 py-0.5 rounded-md inline-block mb-1">
                                {{ $barang->kategori->nama ?? 'Umum' }}
                            </span>
                            <h3 class="text-xs font-black text-slate-800 truncate">{{ $barang->nama }}</h3>
                        </div>
                    </div>

                    <div class="pt-2.5 border-t border-slate-100 flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-600">Stok Fisik:</span>
                        <div class="flex items-center gap-1.5">
                            <input type="number" name="stocks[{{ $barang->id }}]" value="{{ $barang->stok_total }}" min="0" class="w-20 px-2.5 py-1 rounded-lg border border-slate-200 text-xs font-black text-slate-800 text-center">
                            <span class="text-xs text-slate-400">unit</span>
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-white p-8 rounded-2xl border border-slate-200/80 text-center text-slate-400">
                    <i class="fa-solid fa-box-open text-3xl mb-2 text-slate-300"></i>
                    <p class="text-xs font-medium">Tidak ada produk ditemukan.</p>
                </div>
            @endforelse

            @if(count($barangs) > 0)
                <div class="pt-2">
                    <button type="submit" class="w-full py-3 bg-sky-600 hover:bg-sky-700 text-white rounded-xl text-xs font-black shadow-md transition active:scale-95">
                        <i class="fa-solid fa-floppy-disk mr-1.5"></i> Simpan Perubahan Stok
                    </button>
                </div>
            @endif
        </div>

    </form>

</div>
@endsection
