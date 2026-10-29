<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rentify - Platform Marketplace Rental Barang & Alat Terpercaya</title>
    <meta name="description" content="Rentify (rentify21.my.id) adalah platform marketplace penyewaan alat, kendaraan, kamera, camping dan barang dengan mudah, cepat, dan aman.">
    <meta name="keywords" content="Rentify, rentify21, sewa alat, platform penyewaan, marketplace sewa barang, rental kamera, rental tenda, rental motor">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="{{ url('/') }}" />
    <meta name="google-site-verification" content="4y_McbDs1dvq3scZmY9q_XMoaPfzxNrcNR94o3N0nEc" />

    <!-- Favicon & Icons Google-Approved -->
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="https://res.cloudinary.com/fnf8f1pm/image/upload/v1784472853/logolimaduabelas_gjelaz.png">
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#0ea5e9">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">

    <!-- Open Graph / Facebook / WhatsApp Preview -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url('/') }}">
    <meta property="og:site_name" content="Rentify">
    <meta property="og:title" content="Rentify - Platform Marketplace Rental Barang & Alat Terpercaya">
    <meta property="og:description" content="Sewa kamera, alat camping, kendaraan, elektronik & perlengkapan terbaik dengan mudah, cepat, dan aman di Rentify.">
    <meta property="og:image" content="https://res.cloudinary.com/fnf8f1pm/image/upload/v1784472853/logolimaduabelas_gjelaz.png">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Rentify - Platform Penyewaan Barang & Alat Terpercaya">
    <meta name="twitter:description" content="Platform marketplace sewa kamera, camping, kendaraan & elektronik terpercaya.">
    <meta name="twitter:image" content="https://res.cloudinary.com/fnf8f1pm/image/upload/v1784472853/logolimaduabelas_gjelaz.png">

    <!-- Schema.org JSON-LD Structured Data untuk Google Search -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@graph": [
        {
          "@type": "WebSite",
          "@id": "{{ url('/') }}/#website",
          "url": "{{ url('/') }}",
          "name": "Rentify",
          "description": "Platform marketplace rental barang, alat, kendaraan & elektronik terpercaya.",
          "potentialAction": {
            "@type": "SearchAction",
            "target": "{{ route('customer.search') }}?q={search_term_string}",
            "query-input": "required name=search_term_string"
          }
        },
        {
          "@type": "Organization",
          "@id": "{{ url('/') }}/#organization",
          "name": "Rentify",
          "url": "{{ url('/') }}",
          "logo": "https://res.cloudinary.com/fnf8f1pm/image/upload/v1784472853/logolimaduabelas_gjelaz.png"
        }
      ]
    }
    </script>

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="{{ asset('rentify-theme.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .scrollbar-hide::-webkit-scrollbar { display: none; }
        .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
</head>
<body class="text-slate-800 antialiased bg-slate-50/50">

    <!-- WRAPPER KONTEN TAB BERANDA (UNTUK SMOOTH TAB SWAPPING) -->
    <div id="rentify-page-content" data-tab="beranda" class="w-full min-h-screen">

    <!-- ===== TOP NAVBAR (DESKTOP ONLY) ===== -->
    <header class="hidden md:block bg-white/90 backdrop-blur-md border-b border-slate-100/80 shadow-2xs sticky top-0 z-50">
        <div class="max-w-7xl mx-auto w-full px-6 py-3 flex items-center gap-5">
            <!-- Logo -->
            <a href="{{ route('customer.home') }}" class="flex items-center gap-2.5 flex-shrink-0 hover:opacity-95 transition">
                <img src="https://res.cloudinary.com/fnf8f1pm/image/upload/v1784199454/gambar_logo_trerjo.png" class="h-9 object-contain" alt="Rentify Logo">
                <span class="text-2xl font-black text-sky-500 tracking-tighter">Rentify</span>
            </a>

            <!-- Search Bar (Desktop) -->
            <a href="{{ route('customer.search') }}" class="flex-1 flex items-center bg-slate-100/80 hover:bg-slate-100 rounded-full px-4 py-2.5 text-slate-400 text-sm transition border border-slate-200/60 shadow-2xs">
                <i class="fa-solid fa-magnifying-glass mr-2.5 text-sky-500"></i>
                <span class="truncate">Cari kamera Sony, tenda camping, motor NMAX, genset...</span>
            </a>

            <!-- Desktop Nav Links -->
            <nav class="flex items-center gap-1">
                <a href="{{ route('customer.home') }}" class="flex flex-col items-center px-3.5 py-1.5 text-sky-600 rounded-xl font-bold">
                    <i class="fa-solid fa-house text-base"></i>
                    <span class="text-[10px] mt-0.5">Beranda</span>
                </a>
                <a href="{{ route('customer.moments') }}" class="flex flex-col items-center px-3.5 py-1.5 text-slate-500 hover:text-sky-500 rounded-xl transition">
                    <i class="fa-solid fa-camera text-base"></i>
                    <span class="text-[10px] font-semibold mt-0.5">Momen</span>
                </a>
                <a href="{{ route('customer.wishlist') }}" class="flex flex-col items-center px-3.5 py-1.5 text-slate-500 hover:text-sky-500 rounded-xl transition">
                    <i class="fa-solid fa-heart text-base"></i>
                    <span class="text-[10px] font-semibold mt-0.5">Favorit</span>
                </a>
                <a href="{{ route('customer.keranjang') }}" class="relative flex flex-col items-center px-3.5 py-1.5 text-slate-500 hover:text-sky-500 rounded-xl transition group">
                    <svg class="w-5 h-5 stroke-slate-500 group-hover:stroke-sky-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="8" cy="21" r="1"/>
                        <circle cx="19" cy="21" r="1"/>
                        <path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/>
                    </svg>
                    <span class="text-[10px] font-semibold mt-0.5">Keranjang</span>
                    @auth
                        @php $jumlahKeranjang = $keranjangCount ?? 0; @endphp
                        @if($jumlahKeranjang > 0)
                            <span class="absolute top-0 right-2 bg-gradient-to-r from-sky-500 to-sky-600 text-white text-[9px] font-black min-w-[17px] h-4 px-1 rounded-full flex items-center justify-center border-2 border-white shadow-xs leading-none">{{ $jumlahKeranjang > 99 ? '99+' : $jumlahKeranjang }}</span>
                        @endif
                    @endauth
                </a>
                @auth
                <a href="{{ route('customer.dashboard') }}" class="flex flex-col items-center px-3.5 py-1.5 text-slate-500 hover:text-sky-500 rounded-xl transition">
                    @if(Auth::user()->foto_profil)
                        <img src="{{ str_starts_with((string)Auth::user()->foto_profil, 'http') ? Auth::user()->foto_profil : Storage::url((string)Auth::user()->foto_profil) }}" class="w-7 h-7 rounded-full object-cover border-2 border-sky-300" alt="Profil">
                    @else
                        <div class="w-7 h-7 rounded-full bg-sky-500 text-white flex items-center justify-center font-black text-xs">{{ substr(Auth::user()->name, 0, 1) }}</div>
                    @endif
                    <span class="text-[10px] font-semibold mt-0.5">Akun</span>
                </a>
                @else
                <a href="{{ route('login') }}" class="rentify-btn text-white font-bold px-5 py-2 rounded-full text-xs shadow-xs transition ml-2">
                    Masuk
                </a>
                @endauth
            </nav>
        </div>
    </header>

    <!-- ===== TOP NAVBAR (MOBILE ONLY) ===== -->
    <header class="md:hidden sticky top-0 z-50 px-3.5 pt-2.5 pb-2 flex gap-2.5 items-center bg-white/90 backdrop-blur-md border-b border-slate-100">
        <a href="{{ route('customer.home') }}" class="flex-shrink-0 flex items-center gap-1.5 hover:opacity-90 transition">
            <img src="https://res.cloudinary.com/fnf8f1pm/image/upload/v1784199454/gambar_logo_trerjo.png" class="h-8 object-contain" alt="Logo">
            <span class="text-xl font-black text-sky-500 tracking-tighter">Rentify</span>
        </a>
        <a href="{{ route('customer.search') }}" class="flex-1 flex items-center bg-slate-100 hover:bg-slate-200/80 rounded-full px-3.5 py-2 text-slate-400 text-xs border border-slate-200/60 shadow-2xs transition">
            <i class="fa-solid fa-magnifying-glass mr-2 text-sky-500 text-xs"></i>
            <span class="truncate">Cari barang sewa...</span>
        </a>
        <a href="{{ route('customer.keranjang') }}" 
           aria-label="Keranjang Belanja" 
           title="Keranjang Belanja"
           class="relative w-9 h-9 flex-shrink-0 ml-0.5 rounded-2xl flex items-center justify-center text-sky-500 hover:text-sky-600 hover:bg-sky-50 active:scale-95 transition-all group">
            <svg class="w-5 h-5 stroke-sky-500 group-hover:stroke-sky-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="8" cy="21" r="1"/>
                <circle cx="19" cy="21" r="1"/>
                <path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/>
            </svg>
            @auth
                @php $jumlahKeranjang = $keranjangCount ?? 0; @endphp
                @if($jumlahKeranjang > 0)
                    <span class="absolute -top-0.5 -right-0.5 bg-gradient-to-r from-sky-500 to-sky-600 text-white text-[9px] font-black min-w-[16px] h-4 px-1 rounded-full flex items-center justify-center border-2 border-white shadow-xs leading-none">
                        {{ $jumlahKeranjang > 99 ? '99+' : $jumlahKeranjang }}
                    </span>
                @endif
            @endauth
        </a>
    </header>

    <!-- ===== MAIN CONTENT AREA ===== -->
    <main class="pb-24 md:pb-12 md:max-w-7xl md:mx-auto md:px-6 pt-3">

        <!-- ===== BANNER CAROUSEL ALA SHOPEE ===== -->
        <section class="mb-3.5 px-3 sm:px-4 md:px-0">
            @if(isset($banners) && $banners->count() > 0)
                <div class="relative group">
                    <div id="banner-slider" class="flex overflow-x-auto gap-3 scrollbar-hide snap-x snap-mandatory rounded-2xl shadow-xs" style="-webkit-overflow-scrolling: touch;">
                        @foreach($banners as $idx => $banner)
                            <div class="w-full min-w-full snap-start rounded-2xl relative overflow-hidden flex-shrink-0 bg-slate-100 border border-slate-200/60 aspect-[1600/592]" style="aspect-ratio: 1600 / 592; -webkit-mask-image: -webkit-radial-gradient(white, black); transform: translateZ(0);">
                                <img src="{{ asset($banner->gambar_url) }}" alt="Banner Promo" class="absolute inset-0 w-full h-full object-cover object-center" {{ $idx === 0 ? 'loading=eager fetchpriority=high' : 'loading=lazy' }} decoding="async">
                            </div>
                        @endforeach
                    </div>
                    @if($banners->count() > 1)
                        <!-- Indikator Slide Halus Ala Shopee -->
                        <div id="banner-dots" class="absolute bottom-2.5 left-1/2 -translate-x-1/2 flex items-center gap-1.5 z-20 bg-black/35 backdrop-blur-sm px-2.5 py-1 rounded-full pointer-events-none">
                            @foreach($banners as $idx => $banner)
                                <span class="banner-dot block h-1.5 rounded-full transition-all duration-300 {{ $idx === 0 ? 'w-4 bg-white' : 'w-1.5 bg-white/60' }}"></span>
                            @endforeach
                        </div>
                    @endif
                </div>
            @else
                <!-- Hero Default Jika Belum Ada Banner Upload -->
                <div class="rounded-2xl bg-gradient-to-r from-sky-500 via-sky-600 to-blue-700 text-white p-5 sm:p-7 shadow-md relative overflow-hidden">
                    <div class="absolute -right-8 -bottom-10 w-44 h-44 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>
                    <div class="relative z-10 max-w-lg">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-white/20 backdrop-blur-md text-[11px] font-extrabold tracking-wider uppercase mb-2">
                            <i class="fa-solid fa-sparkles text-amber-300"></i> Marketplace Rental Terlengkap
                        </span>
                        <h2 class="text-xl sm:text-2xl font-black tracking-tight leading-snug">Sewa Apapun Lebih Hemat & Praktis di Rentify</h2>
                        <p class="text-xs sm:text-sm text-sky-100 mt-1 leading-relaxed">Temukan kamera, alat camping, kendaraan & perlengkapan hobi terdekat dari lokasimu.</p>
                        <div class="mt-4 flex items-center gap-2">
                            <a href="{{ route('customer.search') }}" class="px-4 py-2 rounded-xl bg-white text-sky-700 font-extrabold text-xs shadow-xs hover:bg-sky-50 active:scale-95 transition inline-flex items-center gap-1.5">
                                <i class="fa-solid fa-magnifying-glass text-[11px]"></i> Jelajahi Barang
                            </a>
                            <button type="button" onclick="autoDetectLocation()" class="px-3.5 py-2 rounded-xl bg-white/20 hover:bg-white/30 text-white font-bold text-xs transition inline-flex items-center gap-1.5">
                                <i class="fa-solid fa-location-crosshairs text-[11px]"></i> Cek Terdekat
                            </button>
                        </div>
                    </div>
                </div>
            @endif
        </section>


        <!-- ===== ETALASE KATEGORI UTAMA (RINGKAS & PROPORSIONAL) ===== -->
        <section class="mb-4 px-3 sm:px-4 md:px-0">
            <div class="bg-white rounded-2xl p-2.5 sm:p-3 shadow-2xs border border-slate-100">
                @php
                    $categoryMeta = [
                        'have fun' => [
                            'icon' => 'fa-solid fa-guitar',
                            'bg' => 'bg-gradient-to-br from-indigo-50 to-purple-100/70',
                            'border' => 'border-indigo-200/60',
                            'text' => 'text-indigo-600',
                            'badge' => 'HOT',
                            'badge_grad' => 'from-indigo-500 to-purple-600',
                            'line1' => 'Peralatan',
                            'line2' => 'Have Fun',
                        ],
                        'outdoor' => [
                            'icon' => 'fa-solid fa-campground',
                            'bg' => 'bg-gradient-to-br from-emerald-50 to-teal-100/70',
                            'border' => 'border-emerald-200/60',
                            'text' => 'text-emerald-600',
                            'badge' => 'CAMP',
                            'badge_grad' => 'from-emerald-500 to-teal-600',
                            'line1' => 'Outdoor &',
                            'line2' => 'Camping',
                        ],
                        'elektronik' => [
                            'icon' => 'fa-solid fa-camera',
                            'bg' => 'bg-gradient-to-br from-sky-50 to-blue-100/70',
                            'border' => 'border-sky-200/60',
                            'text' => 'text-sky-600',
                            'badge' => 'GADGET',
                            'badge_grad' => 'from-sky-500 to-blue-600',
                            'line1' => 'Kamera &',
                            'line2' => 'Elektronik',
                        ],
                        'kendaraan' => [
                            'icon' => 'fa-solid fa-car-side',
                            'bg' => 'bg-gradient-to-br from-amber-50 to-orange-100/70',
                            'border' => 'border-amber-200/60',
                            'text' => 'text-amber-600',
                            'badge' => 'RENTAL',
                            'badge_grad' => 'from-amber-500 to-orange-500',
                            'line1' => 'Rental',
                            'line2' => 'Kendaraan',
                        ],
                        'kos' => [
                            'icon' => 'fa-solid fa-house-chimney-window',
                            'bg' => 'bg-gradient-to-br from-rose-50 to-pink-100/70',
                            'border' => 'border-rose-200/60',
                            'text' => 'text-rose-600',
                            'badge' => 'KOST',
                            'badge_grad' => 'from-rose-500 to-pink-600',
                            'line1' => 'Kos &',
                            'line2' => 'Kamar',
                        ],
                    ];

                    $kategorisList = $kategoris ?? collect();
                @endphp

                <!-- 5 Kategori Row Simetris, Ringkas & Proporsional -->
                <div class="grid grid-cols-5 gap-1 sm:gap-2">
                    @foreach($kategorisList as $cat)
                        @php
                            $namaLow = strtolower($cat->nama);
                            $metaKey = 'have fun';
                            foreach (['kos', 'kendaraan', 'elektronik', 'outdoor', 'have fun'] as $kKey) {
                                if (str_contains($namaLow, $kKey)) { $metaKey = $kKey; break; }
                            }
                            $meta = $categoryMeta[$metaKey] ?? $categoryMeta['have fun'];

                            $line1 = $meta['line1'];
                            $line2 = $meta['line2'];
                            if (!isset($categoryMeta[$metaKey])) {
                                $words = explode(' ', $cat->nama, 2);
                                $line1 = $words[0] ?? $cat->nama;
                                $line2 = $words[1] ?? '';
                            }
                        @endphp
                        <a href="{{ route('customer.search', ['kategori' => $cat->id]) }}" 
                           class="group flex flex-col items-center text-center p-0.5 sm:p-1 rounded-xl hover:bg-slate-50 transition-all duration-200">
                            
                            <!-- Ikon Squircle Ringkas & Proporsional -->
                            <div class="relative mb-1 flex items-center justify-center">
                                <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl {{ $meta['bg'] }} border {{ $meta['border'] }} {{ $meta['text'] }} flex items-center justify-center text-[15px] sm:text-lg shadow-2xs group-hover:scale-105 transition-all duration-200">
                                    <i class="{{ $meta['icon'] }}"></i>
                                </div>
                                <!-- Micro Badge -->
                                @if(!empty($meta['badge']))
                                    <span class="absolute -bottom-0.5 px-1 py-0.2 bg-gradient-to-r {{ $meta['badge_grad'] }} text-white text-[5.5px] sm:text-[6.5px] font-black rounded-full uppercase tracking-wider shadow-2xs scale-90 sm:scale-100 whitespace-nowrap">
                                        {{ $meta['badge'] }}
                                    </span>
                                @endif
                            </div>

                            <!-- Label Kategori Bersih & Rapi -->
                            <div class="w-full flex flex-col justify-center items-center mt-0.5">
                                <span class="text-[9.5px] sm:text-[10.5px] font-semibold text-slate-700 group-hover:text-sky-600 transition-colors leading-[1.15] block">
                                    {{ $line1 }}
                                </span>
                                @if($line2)
                                    <span class="text-[8.5px] sm:text-[9.5px] font-medium text-slate-500 group-hover:text-sky-600 transition-colors leading-[1.15] block">
                                        {{ $line2 }}
                                    </span>
                                @endif
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- ===== INTERACTIVE CALLOUT LOKASI (NON-BLOCKING) ===== -->
        @if(!$hasLokasi)
            <section class="mb-4 px-3 sm:px-4 md:px-0">
                <div class="bg-gradient-to-r from-sky-500 via-sky-600 to-blue-600 rounded-2xl p-3.5 sm:p-4 text-white shadow-md relative overflow-hidden">
                    <div class="absolute -right-6 -bottom-8 w-32 h-32 bg-white/10 rounded-full blur-xl pointer-events-none"></div>
                    <div class="relative z-10 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-white/20 backdrop-blur-sm flex items-center justify-center text-white flex-shrink-0 text-base shadow-2xs">
                                <i class="fa-solid fa-map-location-dot"></i>
                            </div>
                            <div>
                                <h4 class="font-black text-[13.5px] sm:text-sm leading-tight text-white">Lihat Barang Rental Terdekat Di Sekitarmu</h4>
                                <p class="text-[11px] sm:text-xs text-sky-100 font-medium leading-tight mt-0.5">Aktifkan lokasimu untuk melihat jarak akurat & toko dalam radius 50 KM.</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 self-end sm:self-auto flex-shrink-0 w-full sm:w-auto">
                            <button type="button" onclick="autoDetectLocation()" id="btn-gps-home" class="flex-1 sm:flex-none justify-center px-3.5 py-2 rounded-xl bg-white text-sky-700 text-xs font-black shadow-xs hover:bg-sky-50 active:scale-95 transition flex items-center gap-1.5">
                                <i class="fa-solid fa-crosshairs text-[11px]"></i>
                                <span>Gunakan GPS Saya</span>
                            </button>
                            <a href="{{ route('customer.lokasi') }}" class="px-3.5 py-2 rounded-xl bg-white/20 hover:bg-white/30 text-white text-xs font-bold transition flex items-center gap-1">
                                <span>Pilih Manual</span>
                                <i class="fa-solid fa-chevron-right text-[9px]"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </section>
        @endif

        <!-- ===== PRODUCT FEED / KATALOG UTAMA ===== -->
        <section class="px-3 md:px-0 mt-2">
            <!-- Header Section Pilihan -->
            <div class="flex items-center justify-between py-1 px-1 mb-2.5">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-sky-100 text-sky-600 flex items-center justify-center text-sm flex-shrink-0 shadow-2xs">
                        @if($hasLokasi)
                            <i class="fa-solid fa-location-dot"></i>
                        @else
                            <i class="fa-solid fa-fire text-amber-500"></i>
                        @endif
                    </div>
                    <div>
                        <h3 class="font-black text-slate-800 text-[14px] sm:text-[15.5px] leading-tight tracking-tight">
                            {{ $hasLokasi ? 'Barang di Sekitarmu' : 'Rekomendasi Pilihan Terbaik' }}
                        </h3>
                        <p class="text-[11px] sm:text-[12px] text-slate-400 font-medium leading-tight">
                            {{ $hasLokasi ? 'Radius ≤ 50 KM dari posisimu' : 'Katalog rental terpopuler & siap disewa' }}
                        </p>
                    </div>
                </div>

                @if($hasLokasi)
                    <a href="{{ route('customer.lokasi') }}" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-sky-50 hover:bg-sky-100 text-sky-600 border border-sky-100 text-[11px] font-bold transition flex-shrink-0 shadow-2xs">
                        <i class="fa-solid fa-location-crosshairs text-[10.5px]"></i>
                        <span>Ubah</span>
                    </a>
                @else
                    <a href="{{ route('customer.lokasi') }}" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-sky-50 hover:bg-sky-100 text-sky-600 border border-sky-100 text-[11px] font-bold transition flex-shrink-0 shadow-2xs">
                        <i class="fa-solid fa-map-pin text-[10.5px]"></i>
                        <span>Atur Lokasi</span>
                    </a>
                @endif
            </div>

            @if($daftarBarang->isEmpty())
                <!-- EMPTY STATE FALLBACK JIKA DATABASE KOSONG SAMA SEKALI -->
                <div class="bg-white rounded-2xl p-8 text-center border border-slate-100 shadow-2xs max-w-md mx-auto my-6">
                    <div class="w-16 h-16 bg-sky-50 text-sky-400 rounded-full flex items-center justify-center text-3xl mx-auto mb-3">
                        <i class="fa-solid fa-box-open"></i>
                    </div>
                    <h3 class="text-slate-700 font-black text-base mb-1">Belum Ada Barang Rental</h3>
                    <p class="text-slate-400 text-xs leading-relaxed mb-5">Belum ada barang rental yang disetujui di kategori ini saat ini.</p>
                    <a href="{{ route('customer.search') }}" class="inline-flex items-center gap-1.5 bg-sky-500 hover:bg-sky-600 text-white font-bold text-xs px-5 py-2.5 rounded-xl shadow-xs transition">
                        <i class="fa-solid fa-magnifying-glass"></i> Cari Semua Barang
                    </a>
                </div>
            @else
                <!-- PRODUCT GRID (MASONRY WATERFALL: MENYESUAIKAN ASLI GAMBAR PANJANG/PENDEK YANG DIUPLOAD) -->
                <div class="columns-2 sm:columns-3 md:columns-4 lg:columns-5 gap-2.5 sm:gap-3.5 [column-fill:_balance]">
                    @foreach($daftarBarang as $barang)
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
            @endif
        </section>
    </main>
    </div> <!-- END WRAPPER KONTEN TAB BERANDA -->

    <!-- BOTTOM NAV (MOBILE ONLY - 3 TAB RESMI) -->
    <x-bottom-navigation-bar active="beranda" />

    <!-- SCRIPT BANNER SLIDER & AUTO-DETECT LOCATION -->
    <script>
        function initHomeScripts() {
            // 1. BANNER SLIDER AUTO-SLIDE
            const slider = document.getElementById('banner-slider');
            const dots = document.querySelectorAll('.banner-dot');
            if (slider && slider.children.length > 1 && !slider.dataset.sliderInit) {
                slider.dataset.sliderInit = "true";
                const slides = Array.from(slider.children);
                const totalSlides = slides.length;
                let currentSlide = 0;
                let isUserInteracting = false;
                let autoSlideTimer = null;

                const updateDots = (activeIndex) => {
                    dots.forEach((dot, idx) => {
                        if (idx === activeIndex) {
                            dot.classList.remove('w-1.5', 'bg-white/60');
                            dot.classList.add('w-4', 'bg-white');
                        } else {
                            dot.classList.remove('w-4', 'bg-white');
                            dot.classList.add('w-1.5', 'bg-white/60');
                        }
                    });
                };

                const goToSlide = (index) => {
                    if (index >= 0 && index < totalSlides) {
                        currentSlide = index;
                        const targetSlide = slides[currentSlide];
                        if (targetSlide) {
                            slider.scrollTo({
                                left: targetSlide.offsetLeft,
                                behavior: 'smooth'
                            });
                            updateDots(currentSlide);
                        }
                    }
                };

                const startAutoSlide = () => {
                    stopAutoSlide();
                    autoSlideTimer = setInterval(() => {
                        if (isUserInteracting) return;
                        currentSlide = (currentSlide + 1) % totalSlides;
                        goToSlide(currentSlide);
                    }, 4500);
                };

                const stopAutoSlide = () => {
                    if (autoSlideTimer) {
                        clearInterval(autoSlideTimer);
                        autoSlideTimer = null;
                    }
                };

                slider.addEventListener('touchstart', () => {
                    isUserInteracting = true;
                    stopAutoSlide();
                }, { passive: true });

                slider.addEventListener('touchend', () => {
                    setTimeout(() => {
                        isUserInteracting = false;
                        startAutoSlide();
                    }, 3000);
                }, { passive: true });

                let scrollTimeout;
                slider.addEventListener('scroll', () => {
                    clearTimeout(scrollTimeout);
                    scrollTimeout = setTimeout(() => {
                        const scrollPos = slider.scrollLeft;
                        let closestIdx = 0;
                        let minDiff = Infinity;
                        slides.forEach((slide, idx) => {
                            const diff = Math.abs(slide.offsetLeft - scrollPos);
                            if (diff < minDiff) {
                                minDiff = diff;
                                closestIdx = idx;
                            }
                        });
                        currentSlide = closestIdx;
                        updateDots(closestIdx);
                    }, 50);
                }, { passive: true });

                startAutoSlide();
            }
        }

        if (document.readyState === 'loading') {
            document.addEventListener("DOMContentLoaded", initHomeScripts);
        } else {
            initHomeScripts();
        }

        // 2. AUTO-DETECT GPS LOCATION (SMOOTH & INSTANT)
        function autoDetectLocation() {
            if (!navigator.geolocation) {
                window.location.href = "{{ route('customer.lokasi') }}";
                return;
            }

            const btnHome = document.getElementById('btn-gps-home');
            const btnTop = document.getElementById('top-btn-gps');
            const loadingHtml = '<i class="fa-solid fa-circle-notch fa-spin text-[10px]"></i> <span>Mencari...</span>';

            if (btnHome) {
                btnHome.innerHTML = loadingHtml;
                btnHome.disabled = true;
            }
            if (btnTop) {
                btnTop.innerHTML = loadingHtml;
                btnTop.disabled = true;
            }

            navigator.geolocation.getCurrentPosition(async (pos) => {
                const lat = pos.coords.latitude;
                const lon = pos.coords.longitude;
                let alamat = "Lokasi Saya (GPS)";

                try {
                    const res = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lon}`);
                    const data = await res.json();
                    if (data && data.display_name) {
                        alamat = data.display_name;
                    }
                } catch(e) {
                    console.warn("Reverse geocode failed", e);
                }

                const formData = new FormData();
                formData.append('latitude', lat);
                formData.append('longitude', lon);
                formData.append('alamat_lengkap', alamat);
                formData.append('_token', '{{ csrf_token() }}');

                try {
                    const saveRes = await fetch("{{ route('customer.lokasi') }}", {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: formData
                    });
                    if (saveRes.ok) {
                        window.location.reload();
                    } else {
                        window.location.href = "{{ route('customer.lokasi') }}";
                    }
                } catch(err) {
                    window.location.href = "{{ route('customer.lokasi') }}";
                }
            }, (err) => {
                window.location.href = "{{ route('customer.lokasi') }}";
            }, {
                timeout: 10000,
                enableHighAccuracy: true
            });
        }
    </script>
</body>
</html>
