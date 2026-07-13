@extends('layouts.vendor')

@section('title', 'Detail Produk - Vendor Rentify')

@section('content')
<div class="p-4 md:p-8">
    
    <div class="mb-6">
        <a href="{{ route('vendor.barang.index') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-slate-200 text-slate-600 font-bold rounded-xl hover:text-brand-main hover:border-brand-main transition-all shadow-sm text-sm">
            <i class="fa-solid fa-arrow-left"></i> Kembali ke Daftar Produk
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <div class="lg:col-span-1 space-y-4">
            <div class="glass-card rounded-3xl p-4 shadow-sm border border-slate-100 bg-white">
                <div class="aspect-square rounded-2xl overflow-hidden bg-slate-50 flex items-center justify-center border border-slate-100">
                    @if($barang->cover_photo)
                        <img src="{{ asset(str_replace('public/', '', $barang->cover_photo)) }}" alt="{{ $barang->nama }}" class="w-full h-full object-contain">
                    @else
                        <i class="fa-solid fa-image text-4xl text-slate-300"></i>
                    @endif
                </div>
            </div>

            @if(isset($fotoTambahans) && $fotoTambahans->count() > 0)
            <div class="flex gap-3 overflow-x-auto pb-2">
                @foreach($fotoTambahans as $foto)
                <div class="w-20 h-20 rounded-xl overflow-hidden border border-slate-200 flex-shrink-0 bg-white shadow-sm">
                    <img src="{{ asset(str_replace('public/', '', $foto->foto_path)) }}" class="w-full h-full object-cover">
                </div>
                @endforeach
            </div>
            @endif
        </div>

        <div class="lg:col-span-2">
            <div class="glass-card rounded-3xl p-6 md:p-8 shadow-sm border border-slate-100 bg-white h-full">
                
                <div class="flex justify-between items-center mb-4 border-b border-slate-100 pb-4">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-blue-50 text-xs font-bold text-brand-main">
                        <i class="fa-solid fa-tag"></i> {{ $barang->kategori->nama ?? 'Tanpa Kategori' }}
                    </span>
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">
                        Stok Tersedia: <span class="text-slate-800 text-sm ml-1">{{ $barang->stok_total }} Unit</span>
                    </span>
                </div>

                <div class="mb-6 flex justify-between items-start">
                    <h1 class="text-3xl font-black text-slate-800 tracking-tight">{{ $barang->nama }}</h1>
                    
                    @if($barang->is_approved == 1)
                        <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-emerald-50 border border-emerald-100 text-emerald-600 text-xs font-bold whitespace-nowrap">
                            <i class="fa-solid fa-check-circle"></i> Disetujui
                        </span>
                    @else
                        <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-amber-50 border border-amber-100 text-amber-600 text-xs font-bold whitespace-nowrap">
                            <i class="fa-solid fa-clock"></i> Menunggu Kurasi
                        </span>
                    @endif
                </div>

                <div class="bg-slate-50 border border-slate-100 rounded-2xl p-5 mb-6">
                    <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-1">Harga Sewa</p>
                    <div class="flex items-end gap-2">
                        <h2 class="text-3xl font-black text-brand-deep">Rp {{ number_format($barang->harga_sewa_harian, 0, ',', '.') }}</h2>
                        <span class="text-sm font-bold text-slate-500 mb-1">/ hari</span>
                    </div>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-3 gap-4 mb-8">
                    <div class="border border-slate-100 rounded-2xl p-4">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Kondisi</p>
                        <p class="font-bold text-slate-700">{{ $barang->kondisi }}</p>
                    </div>
                    <div class="border border-slate-100 rounded-2xl p-4">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Jaminan / Deposit</p>
                        <p class="font-bold text-slate-700">Rp {{ number_format($barang->deposit, 0, ',', '.') }}</p>
                    </div>
                    <div class="border border-slate-100 rounded-2xl p-4 col-span-2 md:col-span-1">
                        <p class="text-[10px] font-bold text-rose-400 uppercase tracking-widest mb-1">Denda Terlambat</p>
                        <p class="font-bold text-rose-600">Rp {{ number_format($barang->denda_per_hari, 0, ',', '.') }} <span class="text-xs font-normal">/hari</span></p>
                    </div>
                </div>

                <div>
                    <h3 class="flex items-center gap-2 font-bold text-slate-800 mb-3 text-sm">
                        <i class="fa-solid fa-file-lines text-slate-400"></i> Deskripsi Produk
                    </h3>
                    <div class="text-sm text-slate-600 leading-relaxed bg-slate-50 p-4 rounded-2xl border border-slate-100">
                        {!! nl2br(e($barang->deskripsi)) !!}
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection