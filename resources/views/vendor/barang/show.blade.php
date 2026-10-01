@extends('layouts.vendor')

@section('title', 'Detail Produk — Vendor Rentify')

@section('content')
<div class="px-3.5 sm:px-6 lg:px-8 py-4 sm:py-6 max-w-5xl mx-auto space-y-4">
    
    <div class="flex items-center justify-between">
        <a href="{{ route('vendor.barang.index') }}" class="inline-flex items-center gap-1.5 px-3 py-2 bg-white border border-slate-200/80 rounded-xl text-xs font-bold text-slate-700 hover:text-sky-600 transition shadow-xs">
            <i class="fa-solid fa-arrow-left text-xs"></i>
            <span>Daftar Produk</span>
        </a>

        @if($barang->is_approved == 1 || $barang->status_barang == 'disetujui')
            <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-emerald-50 text-emerald-700 text-xs font-black rounded-lg">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Aktif
            </span>
        @else
            <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-amber-50 text-amber-700 text-xs font-black rounded-lg">
                <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span> Ditinjau
            </span>
        @endif
    </div>

    @php
        $cover = $barang->cover_photo ?? ($barang->fotos->first()->foto_path ?? null);
        if ($cover && !str_starts_with($cover, 'http')) {
            $cover = asset(str_replace('public/', '', $cover));
        }
    @endphp

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6">
        
        <!-- Galeri Foto Produk -->
        <div class="lg:col-span-1 space-y-3">
            <div class="aspect-square rounded-2xl sm:rounded-3xl overflow-hidden bg-white border border-slate-200/80 shadow-xs flex items-center justify-center">
                @if($cover)
                    <img src="{{ $cover }}" alt="{{ $barang->nama }}" class="w-full h-full object-cover">
                @else
                    <i class="fa-solid fa-image text-3xl text-slate-300"></i>
                @endif
            </div>

            @if(isset($fotoTambahans) && $fotoTambahans->count() > 0)
                <div class="flex gap-2 overflow-x-auto pb-1">
                    @foreach($fotoTambahans as $foto)
                        @php
                            $fpath = $foto->foto_path;
                            if ($fpath && !str_starts_with($fpath, 'http')) {
                                $fpath = asset(str_replace('public/', '', $fpath));
                            }
                        @endphp
                        <div class="w-16 h-16 rounded-xl overflow-hidden bg-white border border-slate-200 shrink-0">
                            <img src="{{ $fpath }}" class="w-full h-full object-cover">
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Detail Data Produk -->
        <div class="lg:col-span-2 bg-white p-4 sm:p-6 rounded-2xl sm:rounded-3xl border border-slate-200/80 shadow-xs space-y-4">
            
            <div class="flex items-center justify-between gap-2 border-b border-slate-100 pb-3">
                <span class="text-xs font-bold text-sky-600 bg-sky-50 px-2.5 py-1 rounded-lg">
                    {{ $barang->kategori->nama ?? 'Tanpa Kategori' }}
                </span>
                <span class="text-xs font-black text-slate-700">
                    Stok: {{ $barang->stok_total }} unit
                </span>
            </div>

            <div>
                <h1 class="text-lg sm:text-2xl font-black text-slate-800 tracking-tight">{{ $barang->nama }}</h1>
            </div>

            <!-- Tarif Sewa Card -->
            <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200/70 flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500">Tarif Sewa</span>
                <div class="text-right">
                    <span class="text-xl sm:text-2xl font-black text-sky-600">Rp {{ number_format($barang->harga_sewa_harian, 0, ',', '.') }}</span>
                    <span class="text-xs text-slate-400 font-medium">/hari</span>
                </div>
            </div>

            <!-- Ketentuan Tambahan (Deposit & Denda) -->
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5 text-xs">
                <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-1">Kondisi</span>
                    <strong class="text-slate-800 text-xs">{{ $barang->kondisi }}</strong>
                </div>
                <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-1">Deposit</span>
                    <strong class="text-slate-800 text-xs">Rp {{ number_format($barang->deposit, 0, ',', '.') }}</strong>
                </div>
                <div class="p-3 rounded-xl bg-slate-50 border border-slate-100 col-span-2 sm:col-span-1">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-1">Denda Keterlambatan</span>
                    <strong class="text-rose-600 text-xs">Rp {{ number_format($barang->denda_per_hari, 0, ',', '.') }}/hr</strong>
                </div>
            </div>

            <!-- Deskripsi -->
            <div class="pt-2">
                <h3 class="text-xs font-black text-slate-800 uppercase tracking-wider mb-2">Deskripsi Produk</h3>
                <div class="text-xs text-slate-600 leading-relaxed bg-slate-50 p-3.5 rounded-xl border border-slate-100 whitespace-pre-line">
                    {{ $barang->deskripsi }}
                </div>
            </div>

        </div>

    </div>

</div>
@endsection