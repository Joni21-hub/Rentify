@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css"/>
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

<style>
    nav, header, footer { display: none !important; }
    body { background-color: #f8fafc; font-family: 'Segoe UI', Tahoma, sans-serif; padding-bottom: 70px; }
    .detail-container { max-width: 600px; margin: 0 auto; background: white; min-height: 100vh; }
    .swiper-pagination-bullet { background: #cbd5e1; opacity: 1; }
    .swiper-pagination-bullet-active { background: #0ea5e9; width: 16px; border-radius: 8px; }
</style>

<div class="detail-container shadow-sm relative">

    <!-- Tombol Back -->
    <a href="{{ url()->previous() }}" class="absolute top-4 left-4 z-20 w-8 h-8 bg-black/30 backdrop-blur-sm text-white rounded-full flex items-center justify-center hover:bg-black/50 transition">
        <i class="fa-solid fa-arrow-left text-sm"></i>
    </a>

    <!-- AREA SLIDER GAMBAR YANG SUDAH SESUAI DATABASE CLOUDINARY -->
    <div class="swiper productSwiper w-full aspect-square bg-white border-b border-slate-100">
        <div class="swiper-wrapper">
            
            <!-- 1. Foto Utama (Cover Photo) -->
            <div class="swiper-slide flex items-center justify-center p-4">
                @if($barang->cover_photo)
                    @php
                        $coverUrl = str_starts_with($barang->cover_photo, 'http') 
                            ? $barang->cover_photo 
                            : asset(str_replace('public/', '', $barang->cover_photo));
                    @endphp
                    <img src="{{ $coverUrl }}" class="w-full h-full object-contain" onerror="this.src='https://placehold.co/400?text=Foto+Utama+Rusak'">
                @else
                    <i class="fa-solid fa-image text-slate-200 text-6xl"></i>
                @endif
            </div>

            <!-- 2. Foto Galeri Tambahan (Menggunakan kolom foto_path asli) -->
            @if(isset($barang->fotos) && $barang->fotos->count() > 0)
                @foreach($barang->fotos as $foto)
                @php
                    // PERBAIKAN MUTLAK: Mengambil langsung dari kolom foto_path Cloudinary!
                    $rawPath = $foto->foto_path ?? $foto->foto ?? $foto->gambar ?? '';
                    
                    $fotoUrl = str_starts_with($rawPath, 'http') 
                        ? $rawPath 
                        : asset(str_replace('public/', '', $rawPath));
                @endphp
                
                @if(!empty($rawPath))
                <div class="swiper-slide flex items-center justify-center p-4">
                    <img src="{{ $fotoUrl }}" class="w-full h-full object-contain" onerror="this.src='https://placehold.co/400?text=Foto+Galeri+Rusak'">
                </div>
                @endif
                @endforeach
            @endif

        </div>
        <div class="swiper-pagination"></div>
    </div>

    <!-- Area Info Utama -->
    <div class="p-4 border-b border-slate-100 bg-white">
        <div class="flex items-center justify-between mb-2">
            <span class="text-[10px] font-black text-sky-600 bg-sky-50 px-2 py-0.5 rounded uppercase border border-sky-100">
                🏷️ {{ $barang->kategori->nama ?? 'Umum' }}
            </span>
            <span class="text-[11px] text-slate-500 font-medium">Sisa Stok: <strong class="text-slate-800">{{ $barang->stok_total }}</strong> unit</span>
        </div>
        
        <h1 class="text-[15px] font-medium text-slate-800 leading-snug mb-2">{{ $barang->nama }}</h1>
        
        <div class="text-sky-500 font-bold text-xl">
            Rp{{ number_format($barang->harga_sewa_customer ?? $barang->harga_sewa_harian, 0, ',', '.') }}<span class="text-[12px] font-normal text-slate-400">/hari</span>
        </div>
    </div>

    <!-- Spesifikasi -->
    <div class="p-4 border-b border-slate-100 bg-white">
        <h3 class="text-[13px] font-bold text-slate-800 mb-3 flex items-center gap-2"><i class="fa-solid fa-list text-slate-400"></i> Rincian Barang</h3>
        <div class="grid grid-cols-2 gap-y-3 text-[12px]">
            <div>
                <span class="block text-slate-400 mb-0.5">Kondisi</span>
                <span class="font-semibold text-slate-700">{{ $barang->kondisi ?? 'Sangat Baik' }}</span>
            </div>
            <div>
                <span class="block text-slate-400 mb-0.5">Jaminan / Deposit Fisik</span>
                <span class="font-semibold text-amber-600">Rp{{ number_format($barang->deposit ?? 0, 0, ',', '.') }}</span>
            </div>
            @if(isset($barang->denda_per_hari) && $barang->denda_per_hari > 0)
            <div class="col-span-2 pt-2 border-t border-slate-50">
                <span class="block text-slate-400 mb-0.5">Denda Keterlambatan</span>
                <span class="font-semibold text-rose-500">Rp{{ number_format($barang->denda_per_hari, 0, ',', '.') }} / hari</span>
            </div>
            @endif
        </div>
    </div>

    <!-- Deskripsi Produk -->
    <div class="p-4 bg-white border-b border-slate-100">
        <h3 class="text-[13px] font-bold text-slate-800 mb-2 flex items-center gap-2"><i class="fa-solid fa-align-left text-slate-400"></i> Deskripsi Produk</h3>
        <p class="text-[12px] text-slate-600 leading-relaxed whitespace-pre-line">{{ $barang->deskripsi }}</p>
    </div>

    <!-- LOKASI MAPS & PROFIL VENDOR -->
    <div class="p-4 bg-white mb-4 border-b border-slate-100">
        <h3 class="text-[13px] font-bold text-slate-800 mb-2 flex items-center gap-2"><i class="fa-solid fa-store text-slate-400"></i> Toko & Lokasi Pengambilan</h3>
        <div class="flex items-center gap-3 mb-3">
            <div class="w-10 h-10 bg-sky-100 text-sky-500 rounded-full flex items-center justify-center font-black text-lg">
                {{ substr($barang->vendor->vendor_name ?? 'V', 0, 1) }}
            </div>
            <div>
                <div class="font-bold text-slate-700 text-[13px]">{{ $barang->vendor->vendor_name ?? 'Vendor Rentify' }}</div>
                <div class="text-[11px] text-slate-500 mt-0.5 line-clamp-1">{{ $barang->vendor->alamat ?? 'Alamat tersedia setelah pemesanan' }}</div>
            </div>
        </div>

        @php
            $lat = $barang->latitude ?? $barang->vendor->latitude ?? null;
            $long = $barang->longitude ?? $barang->vendor->longitude ?? null;
            $alamat = $barang->alamat ?? $barang->vendor->alamat ?? $barang->vendor->vendor_name ?? 'Toko';
            
            $mapsUrl = ($lat && $long) ? "https://maps.google.com/?q={$lat},{$long}" : "https://maps.google.com/?q=" . urlencode($alamat);
        @endphp
        
        <a href="{{ $mapsUrl }}" target="_blank" class="w-full bg-slate-50 border border-slate-200 text-sky-600 font-bold text-[12px] py-2.5 rounded-lg flex items-center justify-center gap-2 hover:bg-slate-100 transition">
            <i class="fa-solid fa-map-location-dot text-sm"></i> Buka Peta Lokasi (Maps)
        </a>
    </div>

    <!-- BOTTOM BAR: TOMBOL BERFUNGSI -->
    <div class="fixed bottom-0 left-0 w-full bg-white border-t border-slate-200 px-3 py-2.5 flex items-center justify-center z-50">
        <div class="w-full max-w-md flex gap-2">
            
            <form action="{{ route('customer.keranjang.add') }}" method="POST" class="w-1/2">
                @csrf
                <input type="hidden" name="barang_id" value="{{ $barang->id }}">
                <input type="hidden" name="jumlah" value="1">
                <button type="submit" class="w-full bg-sky-50 text-sky-600 border border-sky-400 font-bold text-[13px] py-2.5 rounded-md flex items-center justify-center gap-2 hover:bg-sky-100 transition">
                    <i class="fa-solid fa-cart-plus"></i> Masukkan
                </button>
            </form>

            <form action="{{ route('customer.checkout') }}" method="GET" class="w-1/2">
                <input type="hidden" name="direct_barang_id" value="{{ $barang->id }}">
                <input type="hidden" name="jumlah" value="1">
                <button type="submit" class="w-full bg-sky-500 text-white font-bold text-[13px] py-2.5 rounded-md flex items-center justify-center hover:bg-sky-600 transition">
                    Sewa Sekarang
                </button>
            </form>
        </div>
    </div>

</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        var swiper = new Swiper(".productSwiper", {
            pagination: { el: ".swiper-pagination", clickable: true },
            loop: false,
            spaceBetween: 10,
        });
    });
</script>
@endsection