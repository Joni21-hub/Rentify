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
                    <svg class="w-3.5 h-3.5 {{ empty($kategoriId) ? 'text-sky-500' : 'text-slate-400' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><rect width="7" height="7" x="3" y="3" rx="1"/><rect width="7" height="7" x="14" y="3" rx="1"/><rect width="7" height="7" x="14" y="14" rx="1"/><rect width="7" height="7" x="3" y="14" rx="1"/></svg>
                    Semua Kategori
                </a>
                @foreach($kategoris as $kat)
                    @php
                        $namaLower = strtolower($kat->nama);
                    @endphp
                    <a href="{{ route('customer.search', ['q' => $keyword, 'kategori' => $kat->id]) }}" 
                       class="whitespace-nowrap px-4 py-1.5 rounded-full text-xs font-semibold border transition flex items-center gap-1.5 {{ $kategoriId == $kat->id ? 'bg-sky-50 border-sky-500 text-sky-700 shadow-sm font-bold' : 'bg-white border-slate-200 text-slate-600 hover:bg-slate-50' }}">
                        @if (str_contains($namaLower, 'have fun'))
                            <svg class="w-3.5 h-3.5 {{ $kategoriId == $kat->id ? 'text-sky-600' : 'text-slate-400' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="m11.9 12.1 4.514-4.514"/><path d="M20.1 2.3a1 1 0 0 0-1.4 0l-1.114 1.114A2 2 0 0 0 17 4.828v1.344a2 2 0 0 1-.586 1.414A2 2 0 0 1 17.828 7h1.344a2 2 0 0 0 1.414-.586L21.7 5.3a1 1 0 0 0 0-1.4z"/><path d="m6 16 2 2"/><path d="M8.2 9.9C8.7 8.8 9.8 8 11 8c2.8 0 5 2.2 5 5 0 1.2-.8 2.3-1.9 2.8l-.9.4A2 2 0 0 0 12 18a4 4 0 0 1-4 4c-3.3 0-6-2.7-6-6a4 4 0 0 1 4-4 2 2 0 0 0 1.8-1.2z"/><circle cx="11.5" cy="12.5" r=".5" fill="currentColor"/></svg>
                        @elseif (str_contains($namaLower, 'outdoor'))
                            <svg class="w-3.5 h-3.5 {{ $kategoriId == $kat->id ? 'text-sky-600' : 'text-slate-400' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M19 20L12 4L5 20h14z"/><path d="m12 14-3 6"/><path d="m12 14 3 6"/><path d="M3 20h18"/></svg>
                        @elseif (str_contains($namaLower, 'elektronik'))
                            <svg class="w-3.5 h-3.5 {{ $kategoriId == $kat->id ? 'text-sky-600' : 'text-slate-400' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M14.5 4h-5L7 7H4a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-3l-2.5-3z"/><circle cx="12" cy="13" r="3"/></svg>
                        @elseif (str_contains($namaLower, 'kendaraan'))
                            <svg class="w-3.5 h-3.5 {{ $kategoriId == $kat->id ? 'text-sky-600' : 'text-slate-400' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-.6 0-1.1.4-1.4.9l-1.4 2.9A3.7 3.7 0 0 0 2 12v4c0 .6.4 1 1 1h2"/><circle cx="7" cy="17" r="2"/><path d="M9 17h6"/><circle cx="17" cy="17" r="2"/></svg>
                        @elseif (str_contains($namaLower, 'kos') || str_contains($namaLower, 'kamar'))
                            <svg class="w-3.5 h-3.5 {{ $kategoriId == $kat->id ? 'text-sky-600' : 'text-slate-400' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                        @else
                            <svg class="w-3.5 h-3.5 {{ $kategoriId == $kat->id ? 'text-sky-600' : 'text-slate-400' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2H2v10l9.29 9.29c.94.94 2.48.94 3.42 0l6.58-6.58c.94-.94.94-2.48 0-3.42L12 2Z"/><circle cx="7" cy="7" r=".5" fill="currentColor"/></svg>
                        @endif
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
                <h3 class="font-bold text-slate-700 text-lg mb-1">Mohon Maaf, Barang di Sekitar Belum Tersedia</h3>
                <p class="text-sm text-slate-500 mb-6">Barang tidak ditemukan atau berada di luar jangkauan radius {{ round($userRadius ?? 50) }} KM dari posisimu.</p>
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
