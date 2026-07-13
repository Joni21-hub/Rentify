@extends('layouts.app')

@section('content')
<style>
    /* Menyembunyikan scrollbar agar kategori bisa digeser (swipe) dengan mulus ala HP */
    .scrollbar-hide::-webkit-scrollbar { display: none; }
    .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
    body { background-color: #f8fafc; font-family: 'Segoe UI', Tahoma, sans-serif; }
</style>

<div class="min-h-screen bg-slate-50 pb-10">

    <!-- 1. HEADER PENCARIAN ALA SHOPEE (Menyatu di atas, Bersih & Adem) -->
    <div class="bg-white sticky top-0 z-50 px-4 py-3 shadow-sm flex gap-3 items-center">
        <!-- Tombol Kembali -->
        <a href="{{ route('customer.home') }}" class="text-slate-500 hover:text-sky-600 text-xl transition">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        
        <!-- Form Search Bar (Bentuk Kapsul Outline Biru) -->
        <form action="{{ route('customer.search') }}" method="GET" class="flex-1 flex items-center border-2 border-sky-500 rounded-full bg-white px-4 py-1.5 overflow-hidden transition focus-within:ring-2 focus-within:ring-sky-200">
            @if($kategoriId)
                <input type="hidden" name="kategori" value="{{ $kategoriId }}">
            @endif
            <input type="text" name="q" value="{{ $keyword ?? '' }}" placeholder="Cari di Rentify..." class="flex-1 outline-none text-sm text-slate-700 bg-transparent w-full" autofocus>
            
            <!-- Ikon Kaca Pembesar -->
            <button type="submit" class="text-sky-500 ml-2 hover:text-sky-700 transition">
                <i class="fa-solid fa-magnifying-glass"></i>
            </button>
        </form>
    </div>

    <!-- 2. FILTER KATEGORI (Pill Buttons yang tipis dan bersih) -->
    <div class="bg-white px-4 py-3 mb-2 shadow-[0_2px_4px_rgba(0,0,0,0.02)]">
        <div class="flex gap-2 overflow-x-auto scrollbar-hide pb-1">
            <a href="{{ route('customer.search', ['q' => $keyword]) }}" 
               class="whitespace-nowrap px-4 py-1.5 rounded-full text-xs font-semibold border transition {{ empty($kategoriId) ? 'bg-sky-50 border-sky-500 text-sky-700' : 'bg-white border-slate-200 text-slate-600 hover:bg-slate-50' }}">
                Semua Kategori
            </a>
            @foreach($kategoris as $kat)
                <a href="{{ route('customer.search', ['q' => $keyword, 'kategori' => $kat->id]) }}" 
                   class="whitespace-nowrap px-4 py-1.5 rounded-full text-xs font-semibold border transition {{ $kategoriId == $kat->id ? 'bg-sky-50 border-sky-500 text-sky-700' : 'bg-white border-slate-200 text-slate-600 hover:bg-slate-50' }}">
                    {{ $kat->nama }}
                </a>
            @endforeach
        </div>
    </div>

    <!-- 3. AREA KONTEN (Hasil Pencarian) -->
    <div class="px-3">
        
        <!-- Judul Kecil Ala Marketplace -->
        @if(!empty($keyword) || !empty($kategoriId))
            <div class="text-xs font-bold text-slate-500 mb-3 ml-1 uppercase tracking-wide">
                Hasil Pencarian Pilihan ({{ $barangs->total() }})
            </div>
        @else
            <div class="text-xs font-bold text-slate-500 mb-3 ml-1 uppercase tracking-wide">
                Rekomendasi Untuk Mu
            </div>
        @endif

        @if($barangs->isEmpty())
            <!-- EMPTY STATE (Sangat Minimalis Ala Shopee) -->
            <div class="flex flex-col items-center justify-center mt-16 text-center px-6">
                <div class="w-20 h-20 bg-slate-100 text-slate-300 rounded-full flex items-center justify-center text-4xl mb-4">
                    <i class="fa-solid fa-box-open"></i>
                </div>
                <h3 class="font-bold text-slate-700 text-lg mb-1">Pencarian Tidak Ditemukan</h3>
                <p class="text-sm text-slate-500 mb-6">Coba gunakan kata kunci lain atau kurangi filter pencarian Anda.</p>
                <a href="{{ route('customer.search') }}" class="border border-sky-500 text-sky-600 font-bold text-sm px-6 py-2 rounded-full hover:bg-sky-50 transition">
                    Hapus Pencarian
                </a>
            </div>
        @else
            <!-- GRID PRODUK (2 Kolom Presisi Tinggi) -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-2.5">
                @foreach($barangs as $barang)
                    @php 
                        $hargaTampil = $barang->harga_sewa_harian * 1.05; 
                    @endphp
                    <a href="{{ route('customer.barang.show', $barang->slug) }}" class="bg-white rounded-lg border border-slate-100 overflow-hidden hover:shadow-md hover:border-sky-200 transition duration-200 flex flex-col group relative">
                        
                        <!-- Area Foto: Ratio 1:1, Latar Putih Bersih, Gambar tidak kepotong (object-contain) -->
                        <div class="relative w-full aspect-square bg-white flex items-center justify-center p-2 border-b border-slate-50">
                            @if($barang->cover_photo)
                                <img src="{{ asset(str_replace('public/', '', $barang->cover_photo)) }}" alt="{{ $barang->nama }}" class="w-full h-full object-contain mix-blend-multiply group-hover:scale-105 transition duration-300">
                            @else
                                <i class="fa-solid fa-image text-slate-200 text-3xl"></i>
                            @endif
                            
                            <!-- Badge Stok Kecil di Pojok Kanan Atas Foto -->
                            <div class="absolute top-2 right-2 bg-white/90 backdrop-blur-sm border border-emerald-100 text-emerald-600 text-[9px] font-bold px-1.5 py-0.5 rounded shadow-sm">
                                Stok: {{ $barang->stok_total }}
                            </div>
                        </div>

                        <!-- Area Detail Barang -->
                        <div class="p-2.5 flex flex-col flex-1 justify-between">
                            <div>
                                <!-- Nama Barang (Maksimal 2 Baris) -->
                                <h3 class="text-[13px] font-medium text-slate-700 leading-snug line-clamp-2 mb-1.5">
                                    {{ $barang->nama }}
                                </h3>
                            </div>
                            
                            <!-- Harga & Info Vendor -->
                            <div class="mt-1">
                                <div class="text-sky-600 font-bold text-[15px]">
                                    Rp{{ number_format($hargaTampil, 0, ',', '.') }}<span class="text-[9px] text-slate-400 font-normal">/hari</span>
                                </div>
                                <div class="flex items-center gap-1.5 mt-1.5 text-[10px] font-medium text-slate-500">
                                    <i class="fa-solid fa-shop text-slate-400"></i>
                                    <span class="truncate">{{ $barang->vendor->name ?? 'Vendor' }}</span>
                                </div>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>

            <!-- Pagination (Navigasi Halaman) -->
            <div class="mt-8 mb-6">
                {{ $barangs->links() }}
            </div>
        @endif
    </div>
</div>
@endsection