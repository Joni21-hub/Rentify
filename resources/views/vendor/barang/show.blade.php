@extends('layouts.vendor')

@section('title', 'Detail Produk — Vendor Rentify')

@section('content')
<div class="px-3.5 sm:px-6 lg:px-8 py-4 sm:py-6 max-w-5xl mx-auto space-y-4">
    
    <!-- Top Action Bar -->
    <div class="flex items-center justify-between gap-3">
        <a href="{{ route('vendor.barang.index') }}" class="inline-flex items-center gap-1.5 px-3 py-2 bg-white border border-slate-200/80 rounded-xl text-xs font-bold text-slate-700 hover:text-sky-600 transition shadow-xs">
            <i class="fa-solid fa-arrow-left text-xs"></i>
            <span>Daftar Produk</span>
        </a>

        <div class="flex items-center gap-2">
            <a href="{{ route('vendor.barang.edit', $barang->id) }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-sky-500 hover:bg-sky-600 text-white rounded-xl text-xs font-black shadow-xs transition active:scale-95">
                <i class="fa-solid fa-pen-to-square text-xs"></i>
                <span>Edit Produk</span>
            </a>

            @if($barang->is_approved == 1 || $barang->status_barang == 'disetujui')
                <span class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-emerald-50 text-emerald-700 text-xs font-black rounded-xl">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Aktif
                </span>
            @else
                <span class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-amber-50 text-amber-700 text-xs font-black rounded-xl">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span> Ditinjau
                </span>
            @endif
        </div>
    </div>

    <!-- Alert Notifikasi -->
    @if(session('success'))
        <div class="px-4 py-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl flex items-center gap-2.5 text-xs font-bold shadow-xs">
            <i class="fa-solid fa-circle-check text-emerald-600 text-sm"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div class="px-4 py-3 bg-rose-50 border border-rose-200 text-rose-800 rounded-2xl flex items-center gap-2.5 text-xs font-bold shadow-xs">
            <i class="fa-solid fa-triangle-exclamation text-rose-600 text-sm"></i>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    @php
        $rawCover = (string) ($barang->cover_photo ?? ($barang->fotos->first()->foto_path ?? ''));
        $cover = $rawCover;
        if ($rawCover && !str_starts_with($rawCover, 'http')) {
            $cover = asset((string) str_replace('public/', '', $rawCover));
        }
    @endphp

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6">
        
        <!-- Galeri & Foto Produk -->
        <div class="lg:col-span-1 space-y-4">
            <!-- Cover Photo Card -->
            <div class="bg-white p-3.5 rounded-2xl sm:rounded-3xl border border-slate-200/80 shadow-xs space-y-2">
                <div class="flex items-center justify-between pb-1">
                    <span class="text-[11px] font-black text-slate-700 uppercase tracking-wider">Foto Cover Utama</span>
                    <span class="text-[10px] font-bold text-sky-600 bg-sky-50 px-2 py-0.5 rounded-md">Cover</span>
                </div>
                <div class="aspect-square rounded-2xl overflow-hidden bg-slate-50 border border-slate-100 flex items-center justify-center">
                    @if($cover)
                        <img src="{{ $cover }}" alt="{{ $barang->nama }}" class="w-full h-full object-cover">
                    @else
                        <i class="fa-solid fa-image text-3xl text-slate-300"></i>
                    @endif
                </div>
            </div>

            <!-- Galeri Foto Tambahan -->
            <div class="bg-white p-3.5 rounded-2xl sm:rounded-3xl border border-slate-200/80 shadow-xs space-y-3">
                <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                    <h3 class="text-xs font-black text-slate-800 uppercase tracking-wider">
                        Foto Galeri ({{ isset($fotoTambahans) ? $fotoTambahans->count() : 0 }})
                    </h3>
                </div>

                @if(isset($fotoTambahans) && $fotoTambahans->count() > 0)
                    <div class="grid grid-cols-2 gap-2.5">
                        @foreach($fotoTambahans as $foto)
                            @php
                                $rawFpath = (string) ($foto->foto_path ?? '');
                                $fpath = $rawFpath;
                                if ($rawFpath && !str_starts_with($rawFpath, 'http')) {
                                    $fpath = asset((string) str_replace('public/', '', $rawFpath));
                                }
                            @endphp
                            <div class="relative group rounded-xl overflow-hidden bg-slate-100 border border-slate-200 aspect-square">
                                <img src="{{ $fpath }}" class="w-full h-full object-cover">
                                
                                <!-- Action Buttons Overlay -->
                                <div class="absolute inset-0 bg-slate-900/60 opacity-0 group-hover:opacity-100 transition-opacity flex flex-col items-center justify-center gap-1.5 p-1">
                                    <!-- Set as cover -->
                                    <form action="{{ route('vendor.barang.fotos.cover', [$barang->id, $foto->id]) }}" method="POST" class="w-full">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="w-full py-1 px-1.5 bg-sky-500 hover:bg-sky-600 text-white rounded text-[9px] font-black tracking-tight transition" title="Jadikan Cover Utama">
                                            Jadikan Cover
                                        </button>
                                    </form>

                                    <!-- Delete photo -->
                                    <form action="{{ route('vendor.fotos.destroy', $foto->id) }}" method="POST" onsubmit="return confirm('Hapus foto ini?');" class="w-full">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="w-full py-1 px-1.5 bg-rose-500 hover:bg-rose-600 text-white rounded text-[9px] font-black tracking-tight transition" title="Hapus Foto">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-[11px] text-slate-400 font-medium text-center py-2">Belum ada foto galeri tambahan.</p>
                @endif

                <!-- Form Upload Foto Tambahan Cepat -->
                <form action="{{ route('vendor.barang.fotos.upload', $barang->id) }}" method="POST" enctype="multipart/form-data" class="pt-2 border-t border-slate-100 space-y-2">
                    @csrf
                    <input type="file" name="fotos[]" id="inputFotoTambah" multiple accept="image/jpeg,image/png,image/jpg,image/webp" class="hidden" onchange="this.form.submit()">
                    <label for="inputFotoTambah" class="w-full py-2 px-3 bg-slate-50 hover:bg-slate-100 border border-dashed border-slate-300 rounded-xl text-center text-xs font-bold text-slate-600 flex items-center justify-center gap-2 cursor-pointer transition">
                        <i class="fa-solid fa-cloud-arrow-up text-sky-500"></i>
                        <span>Tambah Foto Galeri</span>
                    </label>
                </form>
            </div>
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
                <span class="text-xs font-bold text-slate-500">Tarif Sewa Harian</span>
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
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-1">Denda Terlambat</span>
                    <strong class="text-rose-600 text-xs">Rp {{ number_format($barang->denda_per_hari, 0, ',', '.') }}/hr</strong>
                </div>
            </div>

            <!-- Layanan Pengantaran & Lokasi -->
            <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-100 text-xs space-y-1.5">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Opsi Pengambilan</span>
                    <span class="px-2 py-0.5 rounded text-[10px] font-black {{ $barang->is_delivery_supported ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-700' }}">
                        {{ $barang->is_delivery_supported ? 'Bisa Diantar / Ambil di Toko' : 'Hanya Ambil di Toko' }}
                    </span>
                </div>
                @if($barang->alamat)
                    <div class="pt-1">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Alamat / Titik Barang</span>
                        <p class="text-xs text-slate-700 font-semibold mt-0.5">{{ $barang->alamat }}</p>
                    </div>
                @endif
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