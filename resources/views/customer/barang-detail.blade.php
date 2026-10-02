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
    input[type=number]::-webkit-inner-spin-button, 
    input[type=number]::-webkit-outer-spin-button { -webkit-appearance: none; margin: 0; }
</style>

<div class="detail-container shadow-sm relative">

    <a href="{{ url()->previous() }}" class="absolute top-4 left-4 z-20 w-8 h-8 bg-black/30 backdrop-blur-sm text-white rounded-full flex items-center justify-center hover:bg-black/50 transition">
        <i class="fa-solid fa-arrow-left text-sm"></i>
    </a>

    <!-- TOMBOL KERANJANG HEADER DENGAN BADGE -->
    <a href="{{ route('customer.keranjang') }}" id="btn-header-cart" class="absolute top-4 right-4 z-20 w-8 h-8 bg-black/30 backdrop-blur-sm text-white rounded-full flex items-center justify-center hover:bg-black/50 transition relative">
        <i class="fa-solid fa-cart-shopping text-xs"></i>
        <span id="header-cart-badge" class="absolute -top-1 -right-1 bg-rose-500 text-white text-[9px] font-black w-4.5 h-4.5 rounded-full flex items-center justify-center border border-white shadow-xs {{ ($keranjangCount ?? 0) > 0 ? '' : 'hidden' }}">
            {{ $keranjangCount ?? 0 }}
        </span>
    </a>

    <div class="swiper productSwiper w-full aspect-square bg-white border-b border-slate-100">
        <div class="swiper-wrapper">
            
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

            @if(isset($barang->fotos) && $barang->fotos->count() > 0)
                @foreach($barang->fotos as $foto)
                @php
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

    <div class="p-4 border-b border-slate-100 bg-white">
        <div class="flex items-center justify-between mb-2">
            <span class="text-[10px] font-black text-sky-600 bg-sky-50 px-2 py-0.5 rounded uppercase border border-sky-100">
                🏷️ {{ $barang->kategori->nama ?? 'Umum' }}
            </span>
            
            @php $stokNyata = max(0, $barang->stok_total); @endphp
            <span class="text-[11px] text-slate-500 font-medium">Sisa Stok: <strong class="{{ $stokNyata == 0 ? 'text-rose-500' : 'text-slate-800' }}">{{ $stokNyata }}</strong> unit</span>
        </div>
        
        <h1 class="text-[15px] font-medium text-slate-800 leading-snug mb-2">{{ $barang->nama }}</h1>
        
        @if($barang->is_kos && $barang->harga_bulanan)
            <div class="space-y-1.5">
                <div class="flex items-baseline gap-2">
                    <span class="text-sky-600 font-black text-2xl">
                        Rp{{ number_format($barang->harga_bulanan_customer ?? $barang->harga_bulanan, 0, ',', '.') }}
                    </span>
                    <span class="text-xs font-bold text-slate-400">/ bulan</span>
                </div>
                <div class="inline-flex items-center gap-1.5 bg-sky-50 text-sky-700 px-3 py-1 rounded-xl text-xs font-bold border border-sky-100">
                    <i class="fa-solid fa-moon text-sky-500"></i>
                    <span>Tersedia Harian: <strong>Rp{{ number_format($barang->harga_sewa_customer ?? $barang->harga_sewa_harian, 0, ',', '.') }}</strong> / hari</span>
                </div>
            </div>
        @else
            <div class="text-sky-500 font-bold text-xl">
                Rp{{ number_format($barang->harga_sewa_customer ?? $barang->harga_sewa_harian, 0, ',', '.') }}<span class="text-[12px] font-normal text-slate-400">/hari</span>
            </div>
        @endif

        @if($stokNyata <= 0)
            <div class="mt-3 bg-rose-50 border border-rose-200 text-rose-600 px-3 py-2 rounded-lg text-[11.5px] font-bold flex items-center gap-2">
                <i class="fa-solid fa-circle-exclamation text-rose-500 text-sm"></i>
                <span>Barang habis disewakan, mohon tunggu sampai dikembalikan.</span>
            </div>
        @endif
    </div>

    @if($barang->is_kos)
    <!-- ============================================== -->
    <!-- SPESIFIKASI KHUSUS KOS / KAMAR (ALA MAMIKOS / OYO) -->
    <!-- ============================================== -->
    <div class="p-4 border-b border-slate-100 bg-white space-y-4">
        <div class="flex items-center justify-between">
            <h3 class="text-[13px] font-bold text-slate-800 flex items-center gap-2">
                <i class="fa-solid fa-door-open text-sky-500"></i> Spesifikasi Kamar & Fasilitas Kos
            </h3>
            <span class="text-[10px] font-extrabold px-2.5 py-0.5 rounded-full bg-sky-50 text-sky-600 border border-sky-200">
                {{ $barang->spesifikasi['tipe_kos'] ?? 'Kos' }}
            </span>
        </div>

        <!-- 3 Highlight Badges -->
        <div class="grid grid-cols-3 gap-2">
            <div class="bg-slate-50 border border-slate-100 rounded-xl p-2.5 text-center">
                <span class="block text-[10px] text-slate-400 font-medium">Tipe Kos</span>
                <span class="text-xs font-bold text-slate-700">{{ $barang->spesifikasi['tipe_kos'] ?? 'Campur' }}</span>
            </div>
            <div class="bg-slate-50 border border-slate-100 rounded-xl p-2.5 text-center">
                <span class="block text-[10px] text-slate-400 font-medium">Kamar Mandi</span>
                <span class="text-xs font-bold text-slate-700">{{ ($barang->spesifikasi['tipe_kamar_mandi'] ?? 'Dalam') == 'Dalam' ? 'KM Dalam' : 'KM Luar' }}</span>
            </div>
            <div class="bg-slate-50 border border-slate-100 rounded-xl p-2.5 text-center">
                <span class="block text-[10px] text-slate-400 font-medium">Ukuran Kamar</span>
                <span class="text-xs font-bold text-slate-700">{{ $barang->spesifikasi['ukuran_kamar'] ?? '3 x 4 m' }}</span>
            </div>
        </div>

        @if(!empty($barang->spesifikasi['fasilitas']))
        <div>
            <span class="block text-xs font-bold text-slate-700 mb-2">Fasilitas Kamar & Bersama</span>
            <div class="grid grid-cols-2 gap-2 text-xs">
                @php
                    $iconMapKos = [
                        'AC' => 'fa-snowflake',
                        'WiFi Cepat' => 'fa-wifi',
                        'Kamar Mandi Dalam' => 'fa-bath',
                        'Kasur & Springbed' => 'fa-bed',
                        'Lemari Pakaian' => 'fa-door-closed',
                        'Meja & Kursi Belajar' => 'fa-chair',
                        'Water Heater' => 'fa-temperature-arrow-up',
                        'Dapur Bersama' => 'fa-utensils',
                        'Parkir Motor Aman' => 'fa-motorcycle',
                        'Parkir Mobil' => 'fa-car',
                        'CCTV 24 Jam' => 'fa-video',
                        'Listrik Termasuk' => 'fa-bolt',
                    ];
                @endphp
                @foreach((array)$barang->spesifikasi['fasilitas'] as $fas)
                <div class="flex items-center gap-2 p-2 rounded-xl bg-slate-50 border border-slate-100 text-slate-700">
                    <i class="fa-solid {{ $iconMapKos[$fas] ?? 'fa-circle-check' }} text-sky-500 text-xs"></i>
                    <span class="text-[11.5px] font-semibold">{{ $fas }}</span>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        @if(!empty($barang->spesifikasi['aturan']))
        <div>
            <span class="block text-xs font-bold text-slate-700 mb-2">Aturan & Ketentuan Kos</span>
            <div class="space-y-1.5">
                @foreach((array)$barang->spesifikasi['aturan'] as $atr)
                <div class="flex items-center gap-2 text-xs text-slate-600">
                    <i class="fa-regular fa-circle-check text-sky-500 text-xs flex-shrink-0"></i>
                    <span class="text-[11.5px]">{{ $atr }}</span>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
    @endif

    @if($barang->is_kendaraan)
    <!-- ============================================== -->
    <!-- SPESIFIKASI KHUSUS KENDARAAN (ALA TRAVELOKA / TURO) -->
    <!-- ============================================== -->
    <div class="p-4 border-b border-slate-100 bg-white space-y-4">
        <div class="flex items-center justify-between">
            <h3 class="text-[13px] font-bold text-slate-800 flex items-center gap-2">
                <i class="fa-solid fa-car-side text-cyan-500"></i> Spesifikasi Rental Kendaraan
            </h3>
            @if(!empty($barang->spesifikasi['tahun_kendaraan']))
            <span class="text-[10px] font-extrabold px-2.5 py-0.5 rounded-full bg-cyan-50 text-cyan-600 border border-cyan-200">
                Tahun {{ $barang->spesifikasi['tahun_kendaraan'] }}
            </span>
            @endif
        </div>

        <!-- 4 Metric Cards -->
        <div class="grid grid-cols-2 gap-2">
            <div class="flex items-center gap-2.5 p-2.5 rounded-xl bg-slate-50 border border-slate-100">
                <div class="w-8 h-8 rounded-lg bg-cyan-100 text-cyan-600 flex items-center justify-center text-xs flex-shrink-0">
                    <i class="fa-solid fa-gears"></i>
                </div>
                <div>
                    <span class="block text-[10px] text-slate-400 font-medium">Transmisi</span>
                    <span class="text-xs font-bold text-slate-800">{{ $barang->spesifikasi['transmisi'] ?? 'Automatic' }}</span>
                </div>
            </div>

            <div class="flex items-center gap-2.5 p-2.5 rounded-xl bg-slate-50 border border-slate-100">
                <div class="w-8 h-8 rounded-lg bg-cyan-100 text-cyan-600 flex items-center justify-center text-xs flex-shrink-0">
                    <i class="fa-solid fa-users"></i>
                </div>
                <div>
                    <span class="block text-[10px] text-slate-400 font-medium">Kapasitas</span>
                    <span class="text-xs font-bold text-slate-800">{{ $barang->spesifikasi['kapasitas_penumpang'] ?? '4-5 Kursi' }}</span>
                </div>
            </div>

            <div class="flex items-center gap-2.5 p-2.5 rounded-xl bg-slate-50 border border-slate-100">
                <div class="w-8 h-8 rounded-lg bg-cyan-100 text-cyan-600 flex items-center justify-center text-xs flex-shrink-0">
                    <i class="fa-solid fa-gas-pump"></i>
                </div>
                <div>
                    <span class="block text-[10px] text-slate-400 font-medium">Bahan Bakar</span>
                    <span class="text-xs font-bold text-slate-800">{{ $barang->spesifikasi['bahan_bakar'] ?? 'Bensin' }}</span>
                </div>
            </div>

            <div class="flex items-center gap-2.5 p-2.5 rounded-xl bg-slate-50 border border-slate-100">
                <div class="w-8 h-8 rounded-lg bg-cyan-100 text-cyan-600 flex items-center justify-center text-xs flex-shrink-0">
                    <i class="fa-solid fa-id-card"></i>
                </div>
                <div>
                    <span class="block text-[10px] text-slate-400 font-medium">Layanan</span>
                    <span class="text-xs font-bold text-slate-800 line-clamp-1">{{ $barang->spesifikasi['opsi_driver'] ?? 'Lepas Kunci' }}</span>
                </div>
            </div>
        </div>

        @if(!empty($barang->spesifikasi['fasilitas_kendaraan']))
        <div>
            <span class="block text-xs font-bold text-slate-700 mb-2">Fasilitas Kendaraan</span>
            <div class="grid grid-cols-2 gap-2 text-xs">
                @php
                    $iconMapVehicle = [
                        'AC Double Blower Dingin' => 'fa-snowflake',
                        'Audio Bluetooth & USB' => 'fa-music',
                        'Kamera Parkir Mundur' => 'fa-camera',
                        'Charger HP Mobil' => 'fa-charging-station',
                        'E-Toll Card Tersedia' => 'fa-credit-card',
                        '2 Helm SNI + Jas Hujan (Motor)' => 'fa-helmet-safety',
                        'Kunci Pengaman Tambahan' => 'fa-lock',
                    ];
                @endphp
                @foreach((array)$barang->spesifikasi['fasilitas_kendaraan'] as $fV)
                <div class="flex items-center gap-2 p-2 rounded-xl bg-slate-50 border border-slate-100 text-slate-700">
                    <i class="fa-solid {{ $iconMapVehicle[$fV] ?? 'fa-circle-check' }} text-cyan-500 text-xs"></i>
                    <span class="text-[11.5px] font-semibold">{{ $fV }}</span>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
    @endif

    @if($barang->is_elektronik && !empty($barang->spesifikasi))
    <!-- ============================================== -->
    <!-- SPESIFIKASI KHUSUS ELEKTRONIK & GADGET -->
    <!-- ============================================== -->
    <div class="p-4 border-b border-slate-100 bg-white space-y-3">
        <div class="flex items-center justify-between">
            <h3 class="text-[13px] font-bold text-slate-800 flex items-center gap-2">
                <i class="fa-solid fa-camera text-blue-500"></i> Spesifikasi Alat & Kelengkapan
            </h3>
            @if(!empty($barang->spesifikasi['merek']))
            <span class="text-[10px] font-extrabold px-2.5 py-0.5 rounded-full bg-blue-50 text-blue-600 border border-blue-200">
                Brand: {{ $barang->spesifikasi['merek'] }}
            </span>
            @endif
        </div>

        @if(!empty($barang->spesifikasi['kondisi_detail']))
        <div class="p-2.5 rounded-xl bg-blue-50/50 border border-blue-100 text-xs flex items-center gap-2 text-slate-700">
            <i class="fa-solid fa-circle-check text-blue-500"></i>
            <span>{{ $barang->spesifikasi['kondisi_detail'] }}</span>
        </div>
        @endif

        @if(!empty($barang->spesifikasi['kelengkapan_elektronik']))
        <div>
            <span class="block text-xs font-bold text-slate-700 mb-2">Kelengkapan Paket Rental</span>
            <div class="flex flex-wrap gap-1.5">
                @foreach((array)$barang->spesifikasi['kelengkapan_elektronik'] as $item)
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-slate-50 border border-slate-200 text-slate-700 text-[11px] font-semibold">
                    <i class="fa-solid fa-check text-blue-500 text-[10px]"></i>
                    {{ $item }}
                </span>
                @endforeach
            </div>
        </div>
        @endif
    </div>
    @endif

    @if($barang->is_outdoor && !empty($barang->spesifikasi))
    <!-- ============================================== -->
    <!-- SPESIFIKASI KHUSUS OUTDOOR & CAMPING -->
    <!-- ============================================== -->
    <div class="p-4 border-b border-slate-100 bg-white space-y-3">
        <div class="flex items-center justify-between">
            <h3 class="text-[13px] font-bold text-slate-800 flex items-center gap-2">
                <i class="fa-solid fa-campground text-teal-500"></i> Spesifikasi Tenda & Outdoor
            </h3>
            @if(!empty($barang->spesifikasi['kapasitas_outdoor']))
            <span class="text-[10px] font-extrabold px-2.5 py-0.5 rounded-full bg-teal-50 text-teal-600 border border-teal-200">
                {{ $barang->spesifikasi['kapasitas_outdoor'] }}
            </span>
            @endif
        </div>

        @if(!empty($barang->spesifikasi['fitur_outdoor']))
        <div class="p-2.5 rounded-xl bg-teal-50/50 border border-teal-100 text-xs flex items-center gap-2 text-slate-700">
            <i class="fa-solid fa-shield-halved text-teal-500"></i>
            <span>{{ $barang->spesifikasi['fitur_outdoor'] }}</span>
        </div>
        @endif

        @if(!empty($barang->spesifikasi['kelengkapan_outdoor']))
        <div>
            <span class="block text-xs font-bold text-slate-700 mb-2">Kelengkapan Camping</span>
            <div class="flex flex-wrap gap-1.5">
                @foreach((array)$barang->spesifikasi['kelengkapan_outdoor'] as $item)
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-slate-50 border border-slate-200 text-slate-700 text-[11px] font-semibold">
                    <i class="fa-solid fa-check text-teal-500 text-[10px]"></i>
                    {{ $item }}
                </span>
                @endforeach
            </div>
        </div>
        @endif
    </div>
    @endif

    @if($barang->is_have_fun && !empty($barang->spesifikasi))
    <!-- ============================================== -->
    <!-- SPESIFIKASI KHUSUS PERALATAN HAVE FUN -->
    <!-- ============================================== -->
    <div class="p-4 border-b border-slate-100 bg-white space-y-3">
        <div class="flex items-center justify-between">
            <h3 class="text-[13px] font-bold text-slate-800 flex items-center gap-2">
                <i class="fa-solid fa-guitar text-indigo-500"></i> Paket Have Fun & Hiburan
            </h3>
            <span class="text-[10px] font-extrabold px-2.5 py-0.5 rounded-full bg-indigo-50 text-indigo-600 border border-indigo-200">
                Party & Game
            </span>
        </div>

        @if(!empty($barang->spesifikasi['jenis_hiburan']))
        <div class="p-2.5 rounded-xl bg-indigo-50/50 border border-indigo-100 text-xs flex items-center gap-2 text-slate-700 font-semibold">
            <i class="fa-solid fa-dice text-indigo-500"></i>
            <span>{{ $barang->spesifikasi['jenis_hiburan'] }}</span>
        </div>
        @endif

        @if(!empty($barang->spesifikasi['kelengkapan_have_fun']))
        <div>
            <span class="block text-xs font-bold text-slate-700 mb-1.5">Kelengkapan & Aksesoris</span>
            <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-600 font-medium">
                {{ $barang->spesifikasi['kelengkapan_have_fun'] }}
            </div>
        </div>
        @endif
    </div>
    @endif

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

    <div class="p-4 bg-white border-b border-slate-100">
        <h3 class="text-[13px] font-bold text-slate-800 mb-2 flex items-center gap-2"><i class="fa-solid fa-align-left text-slate-400"></i> Deskripsi Produk</h3>
        <p class="text-[12px] text-slate-600 leading-relaxed whitespace-pre-line">{{ $barang->deskripsi }}</p>
    </div>

    <div class="p-4 bg-white mb-4 border-b border-slate-100">
        <h3 class="text-[13px] font-bold text-slate-800 mb-2 flex items-center gap-2"><i class="fa-solid fa-store text-slate-400"></i> Informasi Toko</h3>
        <div class="flex items-center justify-between mb-3">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-sky-100 text-sky-500 rounded-full flex items-center justify-center font-black text-lg shadow-sm border border-sky-200">
                    {{ substr($barang->vendor->vendor_name ?? 'V', 0, 1) }}
                </div>
                <div>
                    <div class="font-bold text-slate-700 text-[13px]">{{ $barang->vendor->vendor_name ?? 'Vendor Rentify' }}</div>
                    
                    <div class="text-[10px] text-sky-600 font-semibold mt-0.5 flex items-center gap-1 bg-sky-50 w-fit px-2 py-0.5 rounded border border-sky-100">
                        <i class="fa-solid fa-lock text-[9px]"></i> Maps terbuka setelah sewa
                    </div>
                </div>
            </div>

            @if(isset($barang->jarak))
            <div class="rentify-card border border-slate-200 text-slate-600 px-2.5 py-1 text-right flex-shrink-0">
                <span class="block text-[8px] text-slate-400 uppercase font-black tracking-wider">Jarak Ke Titikmu</span>
                <span class="font-black text-xs flex items-center justify-end gap-1 text-sky-500"><i class="fa-solid fa-location-dot"></i> {{ number_format($barang->jarak, 1, ',', '') }} KM</span>
            </div>
            @endif
        </div>

        <div class="w-full bg-slate-50 border border-slate-200 text-slate-600 font-bold text-[12px] py-2.5 px-3 rounded-lg flex items-center justify-center gap-2 text-center shadow-sm">
            <i class="fa-solid fa-map-location-dot text-sky-500 text-sm"></i> 
            @php
                $alamatFullDetail = $barang->alamat ?? 'Area belum diatur';
                $pecahAlamatDetail = explode(',', $alamatFullDetail);
                $areaSajaDetail = count($pecahAlamatDetail) > 1 ? trim(implode(',', array_slice($pecahAlamatDetail, 1))) : $alamatFullDetail;
            @endphp
            <span class="line-clamp-1 truncate">Area Toko: {{ $areaSajaDetail }}</span>
            @if(isset($barang->jarak))
                <span class="text-slate-400 font-normal">• ±{{ number_format($barang->jarak, 1, ',', '') }} KM</span>
            @endif
        </div>
        <p class="text-[10px] text-slate-400 text-center mt-1.5 font-medium">*Titik Maps akurat & alamat lengkap akan diberikan di struk pesanan.</p>
    </div>

    <div class="rentify-navbar fixed bottom-0 left-0 w-full px-3 py-2.5 flex items-center justify-center z-50">
        <div class="w-full max-w-md flex gap-2">
            
            @if($stokNyata > 0)
                <form action="{{ route('customer.keranjang.add') }}" method="POST" id="form-add-to-cart" class="w-1/2">
                    @csrf
                    <input type="hidden" name="barang_id" value="{{ $barang->id }}">
                    <input type="hidden" name="jumlah" value="1">
                    <button type="submit" id="btn-add-to-cart" class="w-full bg-sky-50 text-sky-600 border border-sky-400 font-bold text-[13px] py-2.5 rounded-md flex items-center justify-center gap-2 hover:bg-sky-100 transition active:scale-95">
                        <i class="fa-solid fa-cart-plus" id="btn-add-icon"></i>
                        <span id="btn-add-text">Masukkan</span>
                    </button>
                </form>

                <form action="{{ route('customer.checkout') }}" method="GET" id="form-sewa-sekarang" class="w-1/2">
                    <input type="hidden" name="direct_barang_id" value="{{ $barang->id }}">
                    <input type="hidden" name="jumlah" value="1">
                    <input type="hidden" name="start_date" id="direct-start-date">
                    <input type="hidden" name="start_time" id="direct-start-time">
                    <input type="hidden" name="durasi_sewa" id="direct-durasi">
                    
                    <button type="button" id="btn-sewa-sekarang" class="w-full bg-gradient-to-r from-sky-400 to-sky-600 text-white font-bold text-[13px] py-2.5 rounded-md flex items-center justify-center hover:from-sky-500 hover:to-sky-700 transition shadow-[0_0_10px_rgba(14,165,233,0.3)]">
                        Sewa Sekarang
                    </button>
                </form>
            @else
                <button disabled type="button" class="w-full bg-slate-200 text-slate-500 font-bold text-[13px] py-3 rounded-md flex items-center justify-center gap-2 cursor-not-allowed">
                    <i class="fa-solid fa-ban text-rose-500"></i> Stok Habis / Sedang Disewa
                </button>
            @endif

        </div>
    </div>

    <!-- FLOATING TOAST UNTUK NOTIFIKASI MASUK KERANJANG -->
    <div id="cart-toast" class="hidden pointer-events-none fixed bottom-16 left-1/2 -translate-x-1/2 z-[90] bg-slate-900/90 backdrop-blur-md text-white px-4 py-2 rounded-full shadow-2xl border border-white/20 flex items-center gap-2.5 transition-all duration-300 opacity-0 translate-y-3">
        <div class="w-5 h-5 rounded-full bg-emerald-500 text-white flex items-center justify-center text-[10px] flex-shrink-0">
            <i class="fa-solid fa-check"></i>
        </div>
        <span class="text-xs font-semibold text-slate-100" id="cart-toast-text">Berhasil masuk keranjang</span>
        <a href="{{ route('customer.keranjang') }}" class="pointer-events-auto text-xs font-black text-sky-400 hover:text-sky-300 underline underline-offset-2 ml-1 flex-shrink-0">
            Lihat
        </a>
    </div>

</div>

<div id="booking-modal" class="hidden fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm transition-opacity">
    <div class="rentify-card w-full max-w-sm overflow-hidden transform scale-100 transition-all">
        <div class="bg-gradient-to-r from-sky-400 to-sky-600 px-6 py-5 relative">
            <h3 class="text-white font-black text-[17px] flex items-center gap-2">
                <i class="fa-regular fa-calendar-check text-xl"></i> Atur Jadwal Sewa
            </h3>
            <p class="text-sky-100 text-xs font-medium mt-1">Tentukan tanggal mulai dan lama pemakaian.</p>
        </div>
        <div class="p-6">
            <label class="block text-[13px] font-bold text-slate-700 mb-2">Tanggal Mulai <span class="text-rose-500">*</span></label>
            <input type="date" id="modal-date" required class="rentify-input w-full px-4 py-2.5 text-sm font-bold text-sky-700 focus:outline-none mb-4 transition">

            <label class="block text-[13px] font-bold text-slate-700 mb-2">Jam Pengambilan/Pengantaran <span class="text-rose-500">*</span></label>
            <select id="modal-time" required class="rentify-input w-full px-4 py-2.5 text-sm font-bold text-sky-700 focus:outline-none mb-4 transition">
                <option value="">-- Pilih Jam (WIB) --</option>
                <option value="08:00">08:00 WIB (Pagi)</option>
                <option value="09:00">09:00 WIB</option>
                <option value="10:00">10:00 WIB</option>
                <option value="11:00">11:00 WIB</option>
                <option value="12:00">12:00 WIB (Siang)</option>
                <option value="13:00">13:00 WIB</option>
                <option value="14:00">14:00 WIB</option>
                <option value="15:00">15:00 WIB (Sore)</option>
                <option value="16:00">16:00 WIB</option>
                <option value="17:00">17:00 WIB</option>
                <option value="18:00">18:00 WIB (Malam)</option>
                <option value="19:00">19:00 WIB</option>
                <option value="20:00">20:00 WIB</option>
            </select>

            <label class="block text-[13px] font-bold text-slate-700 mb-2">Durasi Sewa <span class="text-rose-500">*</span></label>
            <div class="flex items-center border-2 border-slate-200 rounded-xl px-4 py-2 mb-2 focus-within:border-sky-500 transition">
                <input type="number" id="modal-durasi" value="1" min="1" required class="rentify-input w-full text-sm font-black text-sky-700 p-0 outline-none">
                <span class="text-xs font-bold text-slate-400">Hari</span>
            </div>

            <div class="flex flex-wrap gap-1.5 mb-6">
                <button type="button" onclick="document.getElementById('modal-durasi').value=1" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-sky-50 text-[11px] font-bold text-slate-600 hover:text-sky-600 transition">1 Hari</button>
                <button type="button" onclick="document.getElementById('modal-durasi').value=3" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-sky-50 text-[11px] font-bold text-slate-600 hover:text-sky-600 transition">3 Hari</button>
                <button type="button" onclick="document.getElementById('modal-durasi').value=7" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-sky-50 text-[11px] font-bold text-slate-600 hover:text-sky-600 transition">7 Hari (1 Mgg)</button>
                @if($barang->is_kos)
                <button type="button" onclick="document.getElementById('modal-durasi').value=30" class="px-2.5 py-1 rounded-lg bg-sky-100 hover:bg-sky-200 text-[11px] font-black text-sky-700 border border-sky-200 transition">30 Hari (1 Bulan)</button>
                @else
                <button type="button" onclick="document.getElementById('modal-durasi').value=14" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-sky-50 text-[11px] font-bold text-slate-600 hover:text-sky-600 transition">14 Hari</button>
                <button type="button" onclick="document.getElementById('modal-durasi').value=30" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-sky-50 text-[11px] font-bold text-slate-600 hover:text-sky-600 transition">30 Hari</button>
                @endif
            </div>

            <div class="flex gap-3">
                <button type="button" id="btn-close-modal" class="flex-1 bg-slate-100 text-slate-500 font-bold py-3 rounded-xl hover:bg-slate-200 transition">Batal</button>
                <button type="button" id="btn-confirm-modal" class="flex-1 bg-sky-500 text-white font-bold py-3 rounded-xl hover:bg-sky-600 shadow-[0_0_15px_rgba(14,165,233,0.4)] transition">Lanjut Checkout</button>
            </div>
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

        const btnSewaSekarang = document.getElementById('btn-sewa-sekarang');
        const bookingModal = document.getElementById('booking-modal');
        const btnCloseModal = document.getElementById('btn-close-modal');
        const btnConfirmModal = document.getElementById('btn-confirm-modal');
        const formSewaSekarang = document.getElementById('form-sewa-sekarang');

        if(btnSewaSekarang) {
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('modal-date').setAttribute('min', today);

            btnSewaSekarang.addEventListener('click', function(e) {
                e.preventDefault();
                bookingModal.classList.remove('hidden');
            });

            btnCloseModal.addEventListener('click', function() {
                bookingModal.classList.add('hidden');
            });

            btnConfirmModal.addEventListener('click', function() {
                const date = document.getElementById('modal-date').value;
                const time = document.getElementById('modal-time').value;
                const durasi = document.getElementById('modal-durasi').value;

                if(!date) { alert('⚠️ Silakan pilih Tanggal Mulai!'); return; }
                if(!time) { alert('⚠️ Silakan pilih Jam Pengambilan!'); return; }
                if(!durasi || durasi < 1) { alert('⚠️ Durasi minimal 1 hari!'); return; }

                document.getElementById('direct-start-date').value = date;
                document.getElementById('direct-start-time').value = time;
                document.getElementById('direct-durasi').value = durasi;
                
                formSewaSekarang.submit();
            });
        }

        // LOGIKA ANIMASI MASUK KERANJANG & AJAX
        const formAddToCart = document.getElementById('form-add-to-cart');
        const btnAddToCart = document.getElementById('btn-add-to-cart');
        const btnAddText = document.getElementById('btn-add-text');
        const btnAddIcon = document.getElementById('btn-add-icon');
        const headerCartBtn = document.getElementById('btn-header-cart');
        const headerCartBadge = document.getElementById('header-cart-badge');
        const cartToast = document.getElementById('cart-toast');
        const cartToastText = document.getElementById('cart-toast-text');
        let toastTimer = null;

        function showCartToast(msg) {
            if (!cartToast) return;
            if (toastTimer) clearTimeout(toastTimer);
            if (cartToastText) cartToastText.textContent = msg;

            cartToast.classList.remove('hidden');
            requestAnimationFrame(() => {
                cartToast.classList.remove('opacity-0', 'translate-y-3');
                cartToast.classList.add('opacity-100', 'translate-y-0');
            });

            toastTimer = setTimeout(() => {
                cartToast.classList.remove('opacity-100', 'translate-y-0');
                cartToast.classList.add('opacity-0', 'translate-y-3');
                setTimeout(() => {
                    cartToast.classList.add('hidden');
                }, 300);
            }, 2600);
        }

        function triggerFlyAnimation() {
            if (!headerCartBtn || !btnAddToCart) return;

            const startRect = btnAddToCart.getBoundingClientRect();
            const endRect = headerCartBtn.getBoundingClientRect();

            // Ambil gambar produk yang sedang aktif
            const activeSlideImg = document.querySelector('.productSwiper .swiper-slide-active img') || document.querySelector('.productSwiper img');
            const imgSrc = activeSlideImg ? activeSlideImg.src : null;

            // Buat elemen terbang
            const flyer = document.createElement('div');
            flyer.style.position = 'fixed';
            flyer.style.zIndex = '99999';
            flyer.style.left = (startRect.left + startRect.width / 2 - 20) + 'px';
            flyer.style.top = (startRect.top + startRect.height / 2 - 20) + 'px';
            flyer.style.width = '42px';
            flyer.style.height = '42px';
            flyer.style.borderRadius = '50%';
            flyer.style.overflow = 'hidden';
            flyer.style.border = '2px solid #0ea5e9';
            flyer.style.boxShadow = '0 10px 25px rgba(14, 165, 233, 0.5)';
            flyer.style.pointerEvents = 'none';
            flyer.style.transition = 'all 0.65s cubic-bezier(0.2, 0.8, 0.25, 1)';
            flyer.style.background = '#ffffff';

            if (imgSrc) {
                flyer.innerHTML = `<img src="${imgSrc}" style="width:100%;height:100%;object-fit:cover;">`;
            } else {
                flyer.innerHTML = `<div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;background:#0ea5e9;color:white;"><i class="fa-solid fa-cart-shopping text-sm"></i></div>`;
            }

            document.body.appendChild(flyer);

            // Frame selanjutnya: melayang parabolik ke ikon keranjang
            requestAnimationFrame(() => {
                flyer.style.left = (endRect.left + endRect.width / 2 - 12) + 'px';
                flyer.style.top = (endRect.top + endRect.height / 2 - 12) + 'px';
                flyer.style.width = '24px';
                flyer.style.height = '24px';
                flyer.style.transform = 'scale(0.4) rotate(360deg)';
                flyer.style.opacity = '0.7';
            });

            // Setelah mendarat di keranjang
            setTimeout(() => {
                if (flyer.parentNode) flyer.parentNode.removeChild(flyer);

                // Animasi getar/bounce di ikon keranjang
                headerCartBtn.style.transition = 'transform 0.15s ease-out';
                headerCartBtn.style.transform = 'scale(1.35) rotate(-12deg)';

                setTimeout(() => {
                    headerCartBtn.style.transform = 'scale(0.9) rotate(6deg)';
                    setTimeout(() => {
                        headerCartBtn.style.transform = 'scale(1) rotate(0deg)';
                    }, 120);
                }, 120);

                // Animasi pop pada badge angka
                if (headerCartBadge) {
                    headerCartBadge.style.transition = 'transform 0.2s cubic-bezier(0.175, 0.885, 0.32, 1.275)';
                    headerCartBadge.style.transform = 'scale(1.4)';
                    setTimeout(() => {
                        headerCartBadge.style.transform = 'scale(1)';
                    }, 200);
                }
            }, 650);
        }

        if (formAddToCart && btnAddToCart) {
            formAddToCart.addEventListener('submit', function(e) {
                e.preventDefault();

                // 1. Jalankan animasi terbang ke keranjang
                triggerFlyAnimation();

                // 2. Efek haptic pada tombol
                btnAddToCart.disabled = true;
                const originalText = btnAddText ? btnAddText.textContent : 'Masukkan';
                if (btnAddText) btnAddText.textContent = 'Memasukkan...';
                if (btnAddIcon) btnAddIcon.className = 'fa-solid fa-spinner fa-spin';

                // 3. Request AJAX
                const formData = new FormData(formAddToCart);
                fetch(formAddToCart.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(res => {
                    if (res.status === 401) {
                        window.location.href = "{{ route('login') }}";
                        return;
                    }
                    return res.json();
                })
                .then(data => {
                    if (data && data.status === 'success') {
                        if (btnAddText) btnAddText.textContent = 'Tersimpan ✓';
                        if (btnAddIcon) btnAddIcon.className = 'fa-solid fa-check text-emerald-500';

                        // Update angka badge keranjang
                        if (headerCartBadge) {
                            headerCartBadge.textContent = data.cart_count;
                            headerCartBadge.classList.remove('hidden');
                        }

                        // Tampilkan toast notifikasi elegan
                        showCartToast(data.message || 'Barang berhasil masuk keranjang');
                    }
                })
                .catch(err => {
                    console.error('Error add to cart:', err);
                    if (btnAddText) btnAddText.textContent = 'Gagal';
                })
                .finally(() => {
                    setTimeout(() => {
                        btnAddToCart.disabled = false;
                        if (btnAddText) btnAddText.textContent = originalText;
                        if (btnAddIcon) btnAddIcon.className = 'fa-solid fa-cart-plus';
                    }, 1600);
                });
            });
        }
    });
</script>
@endsection