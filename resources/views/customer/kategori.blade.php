@extends('layouts.app')

@section('title', $kategori->nama . ' — Marketplace Rental Rentify')

@section('content')
<style>
    /* Menyembunyikan scrollbar agar swipe kategori mulus */
    .scrollbar-hide::-webkit-scrollbar { display: none; }
    .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
    body { background-color: #f8fafc; font-family: 'Segoe UI', -apple-system, BlinkMacSystemFont, Roboto, sans-serif; }
</style>

@php
    $categoryMeta = [
        'have fun' => [
            'badge' => 'Have Fun & Party',
            'desc' => 'Alat musik, sound party, gitar akustik/listrik, keyboard & konsol game siap sewa.',
            'svg' => '<svg class="w-6 h-6 sm:w-7 sm:h-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M14.5 10.5l3-3"/><path d="M11.5 13.5l3-3"/><path d="M17 7l2.5-2.5a1 1 0 0 0 0-1.4l-.6-.6a1 1 0 0 0-1.4 0l-2.5 2.5"/><path d="M14 14a3 3 0 0 0-4-4l-5.5 5.5a2 2 0 0 0 0 2.8l1.2 1.2a2 2 0 0 0 2.8 0l5.5-5.5z"/><circle cx="12" cy="12" r="1.2" fill="currentColor"/></svg>',
            'gradient' => 'from-indigo-600 via-sky-600 to-sky-500',
            'badge_bg' => 'bg-indigo-50 border-indigo-200 text-indigo-700',
        ],
        'outdoor' => [
            'badge' => 'Adventure & Camping',
            'desc' => 'Tenda dome kapasitas 2P/4P/6P, carrier, sleeping bag, kompor portable & alat mendaki.',
            'svg' => '<svg class="w-6 h-6 sm:w-7 sm:h-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M19 20L12 4L5 20h14z"/><path d="m12 14-3 6"/><path d="m12 14 3 6"/><path d="M3 20h18"/></svg>',
            'gradient' => 'from-teal-600 via-emerald-600 to-sky-600',
            'badge_bg' => 'bg-teal-50 border-teal-200 text-teal-700',
        ],
        'elektronik' => [
            'badge' => 'Foto, Video & Gadget',
            'desc' => 'Kamera Mirrorless, DSLR, lensa Sony/Canon, drone foto/video, gimbal & mic wireless.',
            'svg' => '<svg class="w-6 h-6 sm:w-7 sm:h-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M14.5 4h-5L7 7H4a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-3l-2.5-3z"/><circle cx="12" cy="13" r="3"/></svg>',
            'gradient' => 'from-sky-600 via-blue-600 to-indigo-600',
            'badge_bg' => 'bg-sky-50 border-sky-200 text-sky-700',
        ],
        'kendaraan' => [
            'badge' => 'Motor, Mobil & Sepeda',
            'desc' => 'Sewa motor matic harian, mobil keluarga lepas kunci / driver & sepeda jalanan.',
            'svg' => '<svg class="w-6 h-6 sm:w-7 sm:h-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-.6 0-1.1.4-1.4.9l-1.4 2.9A3.7 3.7 0 0 0 2 12v4c0 .6.4 1 1 1h2"/><circle cx="7" cy="17" r="2"/><path d="M9 17h6"/><circle cx="17" cy="17" r="2"/></svg>',
            'gradient' => 'from-blue-600 via-indigo-600 to-cyan-600',
            'badge_bg' => 'bg-blue-50 border-blue-200 text-blue-700',
        ],
        'kos' => [
            'badge' => 'Hunian & Kamar Sewa',
            'desc' => 'Kamar kos harian/bulanan, homestay nyaman, lokasi strategis dan fasilitas lengkap.',
            'svg' => '<svg class="w-6 h-6 sm:w-7 sm:h-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>',
            'gradient' => 'from-amber-600 via-orange-600 to-sky-600',
            'badge_bg' => 'bg-amber-50 border-amber-200 text-amber-700',
        ],
    ];

    $defaultSvg = '<svg class="w-6 h-6 sm:w-7 sm:h-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="m20.59 13.41-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>';

    $currentNameLow = strtolower($kategori->nama);
    $activeKey = null;
    foreach (['kos', 'kendaraan', 'elektronik', 'outdoor', 'have fun'] as $kKey) {
        if (str_contains($currentNameLow, $kKey)) { $activeKey = $kKey; break; }
    }
    $currMeta = $activeKey && isset($categoryMeta[$activeKey]) ? $categoryMeta[$activeKey] : null;
    $currSvg = $currMeta ? $currMeta['svg'] : $defaultSvg;
    $currGradient = $currMeta ? $currMeta['gradient'] : 'from-sky-600 via-sky-700 to-blue-700';
    $currDesc = $currMeta ? $currMeta['desc'] : 'Jelajahi koleksi barang rental terpercaya untuk kategori ' . $kategori->nama . ' di Rentify.';
    $currBadge = $currMeta ? $currMeta['badge'] : 'Kategori Pilihan';
@endphp

<div class="min-h-screen bg-slate-50 pb-20 md:pb-12">

    <!-- 1. STICKY TOP NAVBAR ALA SHOPEE / TOKOPEDIA -->
    <header class="rentify-navbar sticky top-0 z-50 bg-white/95 backdrop-blur-md border-b border-slate-100 shadow-2xs">
        <div class="max-w-7xl mx-auto px-3.5 sm:px-6 py-2.5 flex items-center justify-between gap-3">
            <!-- Sisi Kiri: Tombol Back & Judul Kategori -->
            <div class="flex items-center gap-3 min-w-0">
                <a href="{{ route('customer.home') }}" 
                   class="w-9 h-9 rounded-xl bg-slate-100 hover:bg-sky-50 text-slate-600 hover:text-sky-600 flex items-center justify-center transition flex-shrink-0 active:scale-95" 
                   aria-label="Kembali ke Beranda">
                    <i class="fa-solid fa-arrow-left text-sm"></i>
                </a>
                <div class="min-w-0">
                    <h1 class="text-base sm:text-lg font-black text-slate-800 truncate leading-tight flex items-center gap-1.5">
                        <span>{{ $kategori->nama }}</span>
                    </h1>
                    <p class="text-[11px] text-slate-400 font-medium truncate leading-none mt-0.5">
                        {{ $barangs->total() }} barang rental siap disewa
                    </p>
                </div>
            </div>

            <!-- Sisi Kanan: Action Buttons (Cari & Keranjang) -->
            <div class="flex items-center gap-2 flex-shrink-0">
                <a href="{{ route('customer.search', ['kategori' => $kategori->id]) }}" 
                   class="w-9 h-9 rounded-xl bg-slate-50 hover:bg-sky-50 text-slate-600 hover:text-sky-600 border border-slate-200/80 flex items-center justify-center transition shadow-2xs" 
                   title="Cari di kategori ini">
                    <i class="fa-solid fa-magnifying-glass text-xs"></i>
                </a>
                <a href="{{ route('customer.keranjang') }}" 
                   class="relative w-9 h-9 rounded-xl bg-sky-50 hover:bg-sky-100 text-sky-600 border border-sky-100 flex items-center justify-center transition shadow-2xs" 
                   title="Keranjang Belanja">
                    <i class="fa-solid fa-cart-shopping text-xs"></i>
                    @php $jumlahKeranjang = $keranjangCount ?? 0; @endphp
                    @if($jumlahKeranjang > 0)
                        <span class="absolute -top-1 -right-1 bg-gradient-to-r from-sky-500 to-sky-600 text-white text-[9px] font-black min-w-[16px] h-4 px-1 rounded-full flex items-center justify-center border-2 border-white shadow-xs leading-none">
                            {{ $jumlahKeranjang > 99 ? '99+' : $jumlahKeranjang }}
                        </span>
                    @endif
                </a>
            </div>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-3 sm:px-6 pt-3.5 sm:pt-5">

        <!-- 2. HERO BANNER KATEGORI RESMI (DESAIN ELEGAN ALA TOKOPEDIA / SHOPEE) -->
        <section class="mb-4">
            <div class="relative overflow-hidden rounded-2xl sm:rounded-3xl bg-gradient-to-r {{ $currGradient }} text-white p-4 sm:p-6 shadow-md">
                <!-- Elemen Dekoratif Aksen -->
                <div class="absolute -right-8 -bottom-10 w-44 h-44 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>
                <div class="absolute right-10 top-2 w-24 h-24 bg-sky-400/20 rounded-full blur-xl pointer-events-none"></div>

                <div class="relative z-10 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                    <div class="flex items-start sm:items-center gap-3.5">
                        <!-- Kotak Ikon Kategori -->
                        <div class="w-13 h-13 sm:w-16 sm:h-16 rounded-2xl bg-white/15 backdrop-blur-md border border-white/30 flex items-center justify-center text-white flex-shrink-0 shadow-sm">
                            {!! $currSvg !!}
                        </div>
                        <div>
                            <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-white/20 backdrop-blur-xs text-[10px] sm:text-[11px] font-bold text-white uppercase tracking-wider mb-1">
                                <i class="fa-solid fa-sparkles text-[9px]"></i>
                                <span>{{ $currBadge }}</span>
                            </div>
                            <h2 class="text-xl sm:text-2xl font-black text-white leading-tight">
                                {{ $kategori->nama }}
                            </h2>
                            <p class="text-xs sm:text-sm text-sky-100 font-medium mt-1 leading-snug max-w-xl">
                                {{ $currDesc }}
                            </p>
                        </div>
                    </div>

                    <!-- Mini Highlights / Trust Badges -->
                    <div class="flex flex-wrap sm:flex-col gap-2 self-stretch sm:self-auto sm:items-end justify-between border-t border-white/15 sm:border-t-0 pt-3 sm:pt-0">
                        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl bg-white/15 backdrop-blur-sm text-xs font-bold text-white shadow-2xs">
                            <i class="fa-solid fa-box-check text-sky-200"></i>
                            <span>{{ $barangs->total() }} Produk Aktif</span>
                        </div>
                        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl bg-white/15 backdrop-blur-sm text-xs font-bold text-white shadow-2xs">
                            <i class="fa-solid fa-shield-check text-sky-200"></i>
                            <span>Vendor Terverifikasi</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- 3. BAR PILIH KATEGORI CEPAT (HORIZONTAL SWIPE ALA SHOPEE) -->
        <section class="mb-4">
            <div class="bg-white rounded-2xl p-2 sm:p-3 border border-slate-100 shadow-2xs">
                <div class="flex items-center justify-between px-1 mb-2">
                    <span class="text-[11px] sm:text-xs font-bold text-slate-500 uppercase tracking-wider flex items-center gap-1.5">
                        <i class="fa-solid fa-layer-group text-sky-500"></i>
                        Pindah Kategori
                    </span>
                    <a href="{{ route('customer.home') }}" class="text-[11px] font-bold text-sky-600 hover:text-sky-700">
                        Lihat Semua di Beranda &rarr;
                    </a>
                </div>
                <div class="flex items-center gap-2 overflow-x-auto scrollbar-hide pb-0.5">
                    @foreach($allKategoris as $cat)
                        @php
                            $isCatActive = ($cat->id == $kategori->id);
                            $catNameLow = strtolower($cat->nama);
                            $catMetaKey = null;
                            foreach (['kos', 'kendaraan', 'elektronik', 'outdoor', 'have fun'] as $kKey) {
                                if (str_contains($catNameLow, $kKey)) { $catMetaKey = $kKey; break; }
                            }
                            $cMeta = $catMetaKey && isset($categoryMeta[$catMetaKey]) ? $categoryMeta[$catMetaKey] : null;
                            $iconSvg = $cMeta ? $cMeta['svg'] : $defaultSvg;
                        @endphp
                        <a href="{{ route('customer.kategori', $cat->id) }}" 
                           class="flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-bold whitespace-nowrap transition-all duration-200 flex-shrink-0 {{ $isCatActive ? 'bg-sky-500 text-white shadow-xs scale-[1.02]' : 'bg-slate-50 hover:bg-slate-100 text-slate-600 border border-slate-200/60' }}">
                            <span class="w-4 h-4 flex items-center justify-center {{ $isCatActive ? 'text-white' : 'text-sky-600' }}">
                                {!! str_replace(['w-6 h-6', 'w-7 h-7'], 'w-4 h-4', $iconSvg) !!}
                            </span>
                            <span>{{ $cat->nama }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- 4. TOOLBAR FILTER & URUTKAN -->
        <section class="mb-3 flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 px-1">
            <div class="flex items-center gap-2">
                <span class="text-xs font-extrabold text-slate-700">Urutkan:</span>
                <div class="inline-flex rounded-xl bg-slate-100 p-0.5 text-xs font-bold">
                    <a href="{{ route('customer.kategori', ['id' => $kategori->id, 'sort' => 'terbaru']) }}" 
                       class="px-3 py-1.5 rounded-lg transition {{ $sort === 'terbaru' ? 'bg-white text-sky-600 shadow-2xs font-black' : 'text-slate-500 hover:text-slate-700' }}">
                        Terbaru
                    </a>
                    @if($hasLokasi)
                        <a href="{{ route('customer.kategori', ['id' => $kategori->id, 'sort' => 'terdekat']) }}" 
                           class="px-3 py-1.5 rounded-lg transition {{ $sort === 'terdekat' ? 'bg-white text-sky-600 shadow-2xs font-black' : 'text-slate-500 hover:text-slate-700' }}">
                            Terdekat
                        </a>
                    @endif
                    <a href="{{ route('customer.kategori', ['id' => $kategori->id, 'sort' => 'harga_asc']) }}" 
                       class="px-3 py-1.5 rounded-lg transition {{ $sort === 'harga_asc' ? 'bg-white text-sky-600 shadow-2xs font-black' : 'text-slate-500 hover:text-slate-700' }}">
                        Termurah
                    </a>
                    <a href="{{ route('customer.kategori', ['id' => $kategori->id, 'sort' => 'harga_desc']) }}" 
                       class="px-3 py-1.5 rounded-lg transition {{ $sort === 'harga_desc' ? 'bg-white text-sky-600 shadow-2xs font-black' : 'text-slate-500 hover:text-slate-700' }}">
                        Tertinggi
                    </a>
                </div>
            </div>

            <div class="text-[11px] sm:text-xs text-slate-500 font-medium">
                Menampilkan <span class="font-bold text-slate-800">{{ $barangs->count() }}</span> dari <span class="font-bold text-slate-800">{{ $barangs->total() }}</span> barang
            </div>
        </section>

        <!-- 5. GRID PRODUK KATALOG MASONRY (SESUAI DENGAN DESAIN BERANDA) -->
        @if($barangs->isEmpty())
            <!-- EMPTY STATE KHUSUS KATEGORI -->
            <div class="bg-white rounded-3xl p-8 sm:p-12 text-center border border-slate-100 shadow-2xs max-w-lg mx-auto my-8">
                <div class="w-16 h-16 bg-sky-50 text-sky-500 rounded-2xl flex items-center justify-center text-3xl mx-auto mb-3 shadow-2xs">
                    <i class="fa-solid fa-box-open"></i>
                </div>
                <h3 class="text-slate-800 font-black text-base sm:text-lg mb-1">
                    Belum Ada Barang di Kategori Ini
                </h3>
                <p class="text-slate-400 text-xs sm:text-sm leading-relaxed mb-6 max-w-sm mx-auto">
                    Toko rental di sekitarmu belum mengunggah barang sewa untuk kategori <span class="font-bold text-slate-600">{{ $kategori->nama }}</span>.
                </p>
                <div class="flex items-center justify-center gap-2.5">
                    <a href="{{ route('customer.home') }}" class="inline-flex items-center gap-1.5 bg-sky-500 hover:bg-sky-600 text-white font-bold text-xs px-5 py-2.5 rounded-xl shadow-xs transition active:scale-95">
                        <i class="fa-solid fa-arrow-left"></i> Kembali ke Beranda
                    </a>
                    <a href="{{ route('customer.search') }}" class="inline-flex items-center gap-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs px-4 py-2.5 rounded-xl transition">
                        Cari Barang Lain
                    </a>
                </div>
            </div>
        @else
            <div class="columns-2 sm:columns-3 md:columns-4 lg:columns-5 gap-2.5 sm:gap-3.5 [column-fill:_balance]">
                @foreach($barangs as $barang)
                    <div class="break-inside-avoid mb-2.5 sm:mb-3.5 bg-white rounded-2xl border border-slate-100/90 shadow-[0_2px_8px_rgba(0,0,0,0.03)] hover:shadow-[0_12px_24px_rgba(14,165,233,0.14)] hover:-translate-y-1 transition-all duration-300 flex flex-col overflow-hidden relative group">
                        
                        <!-- GAMBAR PRODUK DINAMIS -->
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
                                        Rp{{ number_format($barang->harga_sewa_customer, 0, ',', '.') }}<span class="text-[9.5px] text-slate-400 font-medium">/hari</span>
                                    @endif
                                </div>
                            </a>

                            <!-- VENDOR INFO FOOTER -->
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

            <!-- PAGINATION -->
            <div class="mt-6 flex justify-center">
                {{ $barangs->appends(['sort' => $sort])->links() }}
            </div>
        @endif

    </main>

    <!-- BOTTOM NAV (MOBILE ONLY - 3 TAB RESMI) -->
    <x-bottom-navigation-bar active="beranda" />

</div>
@endsection
