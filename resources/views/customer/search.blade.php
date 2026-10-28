@extends('layouts.app')

@section('content')
<style>
    /* Menyembunyikan scrollbar agar kategori bisa digeser (swipe) dengan mulus ala HP */
    .scrollbar-hide::-webkit-scrollbar { display: none; }
    .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
    body { background-color: #f8fafc; font-family: 'Segoe UI', Tahoma, sans-serif; }
</style>

<div class="min-h-screen bg-slate-50 pb-10">

    <!-- 1. HEADER PENCARIAN ALA SHOPEE -->
    <div class="rentify-navbar sticky top-0 z-50 bg-white/95 backdrop-blur-md border-b border-slate-100 shadow-2xs">
        <div class="max-w-7xl mx-auto px-4 py-3 flex gap-3.5 items-center">
            <!-- Tombol Kembali -->
            <a href="{{ route('customer.home') }}" class="text-slate-500 hover:text-sky-600 text-xl transition flex-shrink-0" aria-label="Kembali ke Beranda">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
            
            <!-- Form Search Bar -->
            <form action="{{ route('customer.search') }}" method="GET" class="flex-1 flex items-center border-2 border-sky-500 rounded-full bg-white px-4 py-1.5 overflow-hidden transition focus-within:ring-2 focus-within:ring-sky-200">
                <input type="text" name="q" value="{{ $keyword ?? '' }}" placeholder="Cari di Rentify..." class="rentify-input flex-1 outline-none text-sm text-slate-700 w-full" autofocus>
                
                <!-- Ikon Kaca Pembesar -->
                <button type="submit" class="text-sky-500 ml-2 hover:text-sky-700 transition" aria-label="Cari">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </button>
            </form>
        </div>
    </div>

    <!-- 2. FILTER KATEGORI -->
    <div class="bg-white border-b border-slate-100 mb-3 shadow-[0_2px_4px_rgba(0,0,0,0.02)]">
        <div class="max-w-7xl mx-auto px-4 py-2.5">
            <div class="flex gap-2 overflow-x-auto scrollbar-hide pb-0.5">
                <a href="{{ route('customer.search', ['q' => $keyword]) }}" 
                   class="whitespace-nowrap px-4 py-1.5 rounded-full text-xs font-semibold border transition flex items-center gap-1.5 {{ empty($kategoriId) ? 'bg-sky-50 border-sky-500 text-sky-700 shadow-sm font-bold' : 'bg-white border-slate-200 text-slate-600 hover:bg-slate-50' }}">
                    <i class="fa-solid fa-layer-group text-[11px] {{ empty($kategoriId) ? 'text-sky-500' : 'text-slate-400' }}"></i>
                    Semua Kategori
                </a>
                @foreach($kategoris as $kat)
                    @php
                        $namaLower = strtolower($kat->nama);
                        $iconClass = 'fa-tag';
                        if (str_contains($namaLower, 'have fun')) {
                            $iconClass = 'fa-guitar';
                        } elseif (str_contains($namaLower, 'outdoor')) {
                            $iconClass = 'fa-campground';
                        } elseif (str_contains($namaLower, 'elektronik')) {
                            $iconClass = 'fa-camera';
                        } elseif (str_contains($namaLower, 'kendaraan')) {
                            $iconClass = 'fa-car';
                        } elseif (str_contains($namaLower, 'kos') || str_contains($namaLower, 'kamar')) {
                            $iconClass = 'fa-door-open';
                        }
                    @endphp
                    <a href="{{ route('customer.search', ['q' => $keyword, 'kategori' => $kat->id]) }}" 
                       class="whitespace-nowrap px-4 py-1.5 rounded-full text-xs font-semibold border transition flex items-center gap-1.5 {{ $kategoriId == $kat->id ? 'bg-sky-50 border-sky-500 text-sky-700 shadow-sm font-bold' : 'bg-white border-slate-200 text-slate-600 hover:bg-slate-50' }}">
                        <i class="fa-solid {{ $iconClass }} text-[11px] {{ $kategoriId == $kat->id ? 'text-sky-600' : 'text-slate-400' }}"></i>
                        {{ $kat->nama }}
                    </a>
                @endforeach
            </div>
        </div>
    </div>

    <!-- 3. AREA KONTEN (Hasil Pencarian) -->
    <div class="max-w-7xl mx-auto px-3.5 sm:px-6 lg:px-8 mb-12">
        
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
            <!-- EMPTY STATE -->
            <div class="flex flex-col items-center justify-center mt-16 text-center px-6">
                <div class="w-20 h-20 bg-slate-100 text-slate-300 rounded-full flex items-center justify-center text-4xl mb-4">
                    <i class="fa-solid fa-box-open"></i>
                </div>
                <h3 class="font-bold text-slate-700 text-lg mb-1">Pencarian Tidak Ditemukan</h3>
                <p class="text-sm text-slate-500 mb-6">Barang tidak ada atau lokasinya berada di luar radius 50 KM dari titik Anda.</p>
                <a href="{{ route('customer.search') }}" class="border border-sky-500 text-sky-600 font-bold text-sm px-6 py-2 rounded-full hover:bg-sky-50 transition">
                    Hapus Pencarian
                </a>
            </div>
        @else
            <!-- PRODUCT GRID (MASONRY WATERFALL: MENYESUAIKAN ASLI GAMBAR PANJANG/PENDEK SEPERTI DI BERANDA) -->
            <div class="columns-2 sm:columns-3 md:columns-4 lg:columns-5 gap-2.5 sm:gap-3.5 [column-fill:_balance]">
                @foreach($barangs as $barang)
                    <div class="break-inside-avoid mb-2.5 sm:mb-3.5 bg-white rounded-2xl border border-slate-100/90 shadow-[0_2px_8px_rgba(0,0,0,0.03)] hover:shadow-[0_12px_24px_rgba(14,165,233,0.14)] hover:-translate-y-1 transition-all duration-300 flex flex-col overflow-hidden relative group">
                        
                        <!-- GAMBAR PRODUK DINAMIS (MENYESUAIKAN RASIO FOTO ASLI TANPA TERPOTONG KOTAK) -->
                        <a href="{{ url('/customer/barang/' . ($barang->slug ?? $barang->id)) }}" class="block relative w-full overflow-hidden bg-slate-100 flex items-center justify-center">
                            @php
                                $coverImg = $barang->cover_photo_url;
                                $namaLower = strtolower($barang->nama ?? '');
                                $fallbackImg = str_contains($namaLower, 'kurs') 
                                    ? asset('barang/5I0op8pdywH1SopGwP36pKBhx6y44N6tbBVzcSPx.png') 
                                    : (str_contains($namaLower, 'meja') ? asset('barang/4yEbGpYvTYYai8LZHVP3jFNNO5WBPUokdCJggGBy.png') : asset('storage/placeholders/placeholder-barang.png'));
                            @endphp
                            <img src="{{ $coverImg }}" class="w-full h-auto max-h-[420px] object-cover group-hover:scale-105 transition-transform duration-500 ease-out block" loading="lazy" decoding="async" alt="{{ $barang->nama }}" onerror="this.onerror=null; this.src='{{ $fallbackImg }}';">

                            <!-- BADGE JARAK (JIKA TERSEDIA) -->
                            @if(isset($barang->jarak))
                                <div class="absolute bottom-2 left-2 bg-slate-900/80 backdrop-blur-md px-2 py-0.5 rounded-full text-[9.5px] font-bold text-white shadow-xs flex items-center gap-1">
                                    <i class="fa-solid fa-location-dot text-sky-400 text-[8.5px]"></i>
                                    <span>{{ number_format($barang->jarak, 1, ',', '') }} KM</span>
                                </div>
                            @elseif($barang->kategori)
                                <div class="absolute bottom-2 left-2 bg-white/90 backdrop-blur-md px-2 py-0.5 rounded-full text-[9px] font-bold text-slate-700 shadow-xs border border-white/80 truncate max-w-[110px]">
                                    {{ $barang->kategori->nama }}
                                </div>
                            @endif

                            <!-- BADGE STOK -->
                            @if(isset($barang->stok_total))
                                <div class="absolute top-2 left-2 bg-white/90 backdrop-blur-xs text-emerald-700 text-[9px] font-extrabold px-1.5 py-0.5 rounded-md border border-slate-200/60 shadow-2xs">
                                    Stok: {{ $barang->stok_total }}
                                </div>
                            @endif
                        </a>

                        <!-- TOMBOL WISHLIST -->
                        @php
                            $isFavorit = isset($wishlistIds[$barang->id]);
                        @endphp
                        <form action="{{ route('customer.wishlist.toggle', $barang->id) }}" method="POST" class="absolute top-2 right-2 z-10">
                            @csrf
                            <button type="submit" aria-label="Favoritkan" class="w-7 h-7 bg-white/90 backdrop-blur-md rounded-full shadow-2xs flex items-center justify-center {{ $isFavorit ? 'text-rose-500' : 'text-slate-300 hover:text-rose-400' }} border border-white/80 active:scale-90 transition-all">
                                <i class="fa-solid fa-heart text-[11px]"></i>
                            </button>
                        </form>

                        <!-- INFO PRODUK LENGKAP -->
                        <div class="p-2.5 sm:p-3 flex flex-col flex-1 justify-between">
                            <a href="{{ url('/customer/barang/' . ($barang->slug ?? $barang->id)) }}" class="block">
                                <h4 class="text-[12.5px] sm:text-[13.5px] font-semibold text-slate-800 leading-snug line-clamp-2 mb-1.5 group-hover:text-sky-600 transition-colors">
                                    {{ $barang->nama }}
                                </h4>
                                
                                <!-- HARGA SEWA -->
                                <div class="text-sky-600 font-extrabold text-[14px] sm:text-[15.5px] tracking-tight">
                                    @if($barang->is_kos && $barang->harga_bulanan)
                                        Rp{{ number_format($barang->harga_bulanan_customer ?? $barang->harga_bulanan, 0, ',', '.') }}<span class="text-[9.5px] text-slate-400 font-medium">/bulan</span>
                                    @else
                                        Rp{{ number_format($barang->harga_sewa_customer ?? ($barang->harga_sewa_harian * 1.05), 0, ',', '.') }}<span class="text-[9.5px] text-slate-400 font-medium">/hari</span>
                                    @endif
                                </div>
                            </a>

                            <!-- VENDOR INFO FOOTER (SIGNATURE E-COMMERCE) -->
                            <div class="mt-2.5 pt-2 border-t border-slate-100 flex items-center gap-1.5 text-[10.5px] text-slate-500 min-w-0">
                                <i class="fa-solid fa-store text-sky-500 text-[10px] flex-shrink-0"></i>
                                <span class="truncate font-medium text-slate-600">
                                    {{ $barang->vendor->vendor_name ?? $barang->vendor->name ?? 'Toko Rental' }}
                                </span>
                            </div>
                        </div>

                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-8 mb-6">
                {{ $barangs->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
