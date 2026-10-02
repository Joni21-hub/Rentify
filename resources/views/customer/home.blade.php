<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rentify - Platform Penyewaan Barang & Alat Terpercaya</title>
    <meta name="description" content="Rentify (rentify21.my.id) adalah platform marketplace penyewaan alat, kendaraan, dan barang dengan mudah, cepat, dan aman.">
    <meta name="keywords" content="Rentify, rentify21, sewa alat, platform penyewaan, marketplace sewa barang">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="{{ url('/') }}" />
    <meta name="google-site-verification" content="4y_McbDs1dvq3scZmY9q_XMoaPfzxNrcNR94o3N0nEc" />
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
<body class="text-slate-800 antialiased">

    <!-- ===== TOP NAVBAR (DESKTOP ONLY - HIDDEN ON MOBILE) ===== -->
    <header class="rentify-navbar hidden md:flex sticky top-0 z-50 ">
        <div class="max-w-7xl mx-auto w-full px-6 py-3 flex items-center gap-4">
            <!-- Logo -->
            <a href="{{ route('customer.home') }}" class="flex items-center gap-2 flex-shrink-0">
                <img src="https://res.cloudinary.com/fnf8f1pm/image/upload/v1784199454/gambar_logo_trerjo.png" class="h-8 object-contain" alt="Logo">
                <span class="text-xl font-black text-sky-500 tracking-tighter">Rentify</span>
            </a>

            <!-- Search Bar (Desktop) -->
            <a href="{{ route('customer.search') }}" class="flex-1 flex items-center bg-slate-50 border border-slate-200 hover:border-sky-400 rounded-full px-4 py-2.5 text-slate-400 text-sm transition">
                <i class="fa-solid fa-magnifying-glass mr-2 text-sky-500"></i>
                Cari barang sewa...
            </a>

            <!-- Desktop Nav Links -->
            <nav class="flex items-center gap-1">
                <a href="{{ route('customer.home') }}" class="flex flex-col items-center px-4 py-2 text-sky-600 rounded-xl">
                    <i class="fa-solid fa-house text-lg"></i>
                    <span class="text-[10px] font-bold mt-0.5">Beranda</span>
                </a>
                <a href="{{ route('customer.wishlist') }}" class="flex flex-col items-center px-4 py-2 text-slate-400 hover:text-sky-500 rounded-xl transition">
                    <i class="fa-solid fa-heart text-lg"></i>
                    <span class="text-[10px] font-bold mt-0.5">Favorit</span>
                </a>
                <a href="{{ route('customer.keranjang') }}" class="relative flex flex-col items-center px-4 py-2 text-slate-400 hover:text-sky-500 rounded-xl transition">
                    <i class="fa-solid fa-cart-shopping text-lg"></i>
                    <span class="text-[10px] font-bold mt-0.5">Keranjang</span>
                    @auth
                        @php $jumlahKeranjang = \App\Models\Keranjang::where('user_id', auth()->id())->count(); @endphp
                        @if($jumlahKeranjang > 0)
                            <span class="absolute top-1 right-2 bg-rose-500 text-white text-[9px] font-bold w-4 h-4 flex items-center justify-center rounded-full border border-white">{{ $jumlahKeranjang }}</span>
                        @endif
                    @endauth
                </a>
                @auth
                <a href="{{ route('customer.dashboard') }}" class="flex flex-col items-center px-4 py-2 text-slate-400 hover:text-sky-500 rounded-xl transition">
                    @if(Auth::user()->foto_profil)
                        <img src="{{ str_starts_with(Auth::user()->foto_profil, 'http') ? Auth::user()->foto_profil : Storage::url(Auth::user()->foto_profil) }}" class="w-8 h-8 rounded-full object-cover border-2 border-sky-300" alt="Profil">
                    @else
                        <div class="w-8 h-8 rounded-full bg-sky-500 text-white flex items-center justify-center font-black text-sm">{{ substr(Auth::user()->name, 0, 1) }}</div>
                    @endif
                    <span class="text-[10px] font-bold mt-0.5">Akun</span>
                </a>
                @else
                <a href="{{ route('login') }}" class="rentify-btn text-white font-bold px-5 py-2 rounded-full text-sm transition">
                    Masuk
                </a>
                @endauth
            </nav>
        </div>
    </header>

    <!-- ===== TOP NAVBAR (MOBILE ONLY) ===== -->
    <header class="rentify-navbar md:hidden sticky top-0 z-50 px-3 py-2.5 flex gap-3 items-center">
        <a href="{{ route('customer.home') }}" class="flex-shrink-0 flex items-center gap-1.5">
            <img src="https://res.cloudinary.com/fnf8f1pm/image/upload/v1784199454/gambar_logo_trerjo.png" class="h-6 object-contain" alt="Logo">
            <span class="text-lg font-black text-sky-500 tracking-tighter">Rentify</span>
        </a>
        <a href="{{ route('customer.search') }}" class="flex-1 flex items-center bg-slate-50/90 rounded-full px-3.5 py-2 text-slate-400 text-[13px] border border-sky-100 hover:border-sky-300 transition shadow-2xs">
            <i class="fa-solid fa-magnifying-glass mr-2 text-sky-500"></i>
            Cari barang sewa...
        </a>
        <a href="{{ route('customer.keranjang') }}" class="relative text-sky-500 text-xl flex-shrink-0 ml-1 hover:text-sky-600 transition">
            <i class="fa-solid fa-cart-shopping"></i>
            @auth
                @php $jumlahKeranjang = \App\Models\Keranjang::where('user_id', auth()->id())->count(); @endphp
                @if($jumlahKeranjang > 0)
                    <span class="absolute -top-1 -right-1.5 bg-rose-500 text-white text-[9px] font-bold w-4 h-4 flex items-center justify-center rounded-full border border-white">{{ $jumlahKeranjang }}</span>
                @endif
            @endauth
        </a>
    </header>

    <!-- ===== MAIN CONTENT AREA ===== -->
    <main class="pb-20 md:pb-8 md:max-w-7xl md:mx-auto md:px-6 pt-3.5 sm:pt-4 md:pt-4">

        <!-- BANNER PROMO (SPACING DIPERBAIKI, TIDAK MELEKAT PADA PENCARIAN) -->
        <section class="mb-3.5 px-3 sm:px-4 md:px-0">
            @if(isset($banners) && $banners->count() > 0)
                <div class="relative group">
                    <div id="banner-slider" class="flex overflow-x-auto gap-3 scrollbar-hide snap-x rounded-2xl">
                        @foreach($banners as $banner)
                            <div class="min-w-full snap-center rounded-2xl shadow-xs relative overflow-hidden flex-shrink-0 aspect-[21/9] sm:aspect-[24/9] md:aspect-[3/1] max-h-48 md:max-h-56 bg-slate-100 border border-slate-200/60">
                                <img src="{{ asset($banner->gambar_url) }}" class="w-full h-full object-cover object-center" alt="Promo Banner">
                            </div>
                        @endforeach
                    </div>
                    @if($banners->count() > 1)
                        <!-- Indikator Slide Halus Ala Shopee -->
                        <div id="banner-dots" class="absolute bottom-2.5 left-1/2 -translate-x-1/2 flex items-center gap-1.5 z-10 bg-black/25 backdrop-blur-xs px-2.5 py-1 rounded-full pointer-events-none">
                            @foreach($banners as $idx => $banner)
                                <span class="banner-dot block h-1.5 rounded-full transition-all duration-300 {{ $idx === 0 ? 'w-4 bg-white' : 'w-1.5 bg-white/60' }}"></span>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endif
        </section>

        <!-- ===== ETALASE KATEGORI UTAMA (IDENTITAS VISUAL RENTIFY SERAGAM) ===== -->
        <section class="mb-4 px-3 sm:px-4 md:px-0">
            <div class="bg-white rounded-2xl p-2.5 sm:p-3.5 shadow-2xs border border-slate-200/80">
                @php
                    $categoryMeta = [
                        'have fun' => [
                            'icon' => 'fa-solid fa-guitar',
                            'line1' => 'Peralatan',
                            'line2' => 'Have Fun',
                        ],
                        'outdoor' => [
                            'icon' => 'fa-solid fa-campground',
                            'line1' => 'Outdoor &',
                            'line2' => 'Camping',
                        ],
                        'elektronik' => [
                            'icon' => 'fa-solid fa-camera',
                            'line1' => 'Kamera &',
                            'line2' => 'Elektronik',
                        ],
                        'kendaraan' => [
                            'icon' => 'fa-solid fa-car-side',
                            'line1' => 'Rental',
                            'line2' => 'Kendaraan',
                        ],
                        'kos' => [
                            'icon' => 'fa-solid fa-house-chimney-window',
                            'line1' => 'Kos &',
                            'line2' => 'Kamar',
                        ],
                    ];

                    $kategorisList = isset($kategoris) && $kategoris->count() > 0 
                        ? $kategoris 
                        : \App\Models\Kategori::where('is_active', 1)->get();
                @endphp

                <!-- 5 Kategori Simetris, Unified Visual Style -->
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
                           class="group flex flex-col items-center text-center p-1 sm:p-1.5 rounded-xl hover:bg-slate-50 transition-all duration-200">
                            
                            <!-- Ikon Seragam Satu Gaya + Warna Utama Rentify (text-sky-600, bg-sky-50) -->
                            <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-xl bg-sky-50/90 border border-sky-100/80 text-sky-600 flex items-center justify-center text-base sm:text-lg shadow-2xs group-hover:bg-sky-100 group-hover:border-sky-200 group-hover:text-sky-700 group-hover:scale-105 transition-all duration-200">
                                <i class="{{ $meta['icon'] }}"></i>
                            </div>

                            <!-- Label Kategori 2 Baris Bersih & Rapi -->
                            <div class="w-full min-h-[26px] sm:min-h-[28px] flex flex-col justify-center items-center mt-1.5">
                                <span class="text-[10px] sm:text-[11px] font-semibold text-slate-700 group-hover:text-sky-600 transition-colors leading-[1.2] block">
                                    {{ $line1 }}
                                </span>
                                @if($line2)
                                    <span class="text-[9.5px] sm:text-[10.5px] font-medium text-slate-500 group-hover:text-sky-600 transition-colors leading-[1.2] block">
                                        {{ $line2 }}
                                    </span>
                                @endif
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- KONDISI JIKA USER BELUM ATUR LOKASI -->
        @if(isset($butuhLokasi) && $butuhLokasi)
            <section class="px-3 mt-6 md:px-0">
                <div class="bg-gradient-to-br from-sky-400 to-[#0369a1] rounded-2xl p-8 text-center shadow-lg border border-sky-300 relative overflow-hidden max-w-lg mx-auto">
                    <div class="absolute -top-6 -right-6 w-24 h-24 bg-white/10 rounded-full blur-xl"></div>
                    <div class="relative z-10">
                        <div class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center text-white text-3xl mx-auto mb-4">
                            <i class="fa-solid fa-location-dot"></i>
                        </div>
                        <h3 class="text-white font-black text-lg mb-2">Tentukan Lokasimu Dulu Yuk!</h3>
                        <p class="text-sky-100 text-[13px] font-medium leading-relaxed mb-6">Biar kami bisa mencarikan barang sewaan terdekat (maksimal 50 KM) dari tempatmu.</p>
                        <a href="{{ route('customer.lokasi') }}" class="inline-flex items-center gap-2 bg-white text-sky-600 hover:bg-sky-50 font-black text-sm px-6 py-3 rounded-xl transition-all shadow-sm">
                            <i class="fa-solid fa-map-pin"></i> Atur Titik Lokasi
                        </a>
                    </div>
                </div>
            </section>

        @elseif($daftarBarang->isEmpty())
            <section class="px-3 mt-6 md:px-0">
                <div class="bg-white rounded-2xl border border-slate-200/80 p-8 text-center max-w-md mx-auto shadow-2xs">
                    <div class="w-20 h-20 bg-sky-50 text-sky-400 rounded-full flex items-center justify-center text-4xl mx-auto mb-4">
                        <i class="fa-solid fa-face-frown-open"></i>
                    </div>
                    <h3 class="text-slate-700 font-black text-lg mb-2">Yah, Belum Ada Barang Nih...</h3>
                    <p class="text-slate-500 text-[13px] leading-relaxed mb-6">Maaf banget, belum ada toko yang menyewakan barang di radius 50 KM dari lokasimu saat ini.</p>
                    <a href="{{ route('customer.lokasi') }}" class="inline-flex items-center gap-2 bg-sky-100 text-sky-600 hover:bg-sky-200 font-bold text-[13px] px-5 py-2.5 rounded-xl transition">
                        <i class="fa-solid fa-location-crosshairs"></i> Ganti Titik Lokasi
                    </a>
                </div>
            </section>

        @else
            <section class="px-3 md:px-0 mt-3">
                <div class="flex items-center justify-between mb-3 px-0.5">
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-lg bg-sky-50 border border-sky-100/80 flex items-center justify-center text-sky-600 text-xs">
                            <i class="fa-solid fa-location-dot"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-slate-800 text-[13.5px] sm:text-[15px] leading-tight">Barang di Sekitarmu</h3>
                            <p class="text-[10px] sm:text-[11px] text-slate-400 font-medium">Radius &le; 50 KM dari posisimu</p>
                        </div>
                    </div>
                    <a href="{{ route('customer.lokasi') }}" class="text-[11px] font-semibold text-sky-600 hover:text-sky-700 bg-sky-50/80 hover:bg-sky-100 border border-sky-100 px-2.5 py-1 rounded-full flex items-center gap-1 transition">
                        <i class="fa-solid fa-location-crosshairs text-[10px]"></i>
                        <span>Ubah</span>
                    </a>
                </div>
                <!-- GRID: 2 kolom HP, 3 kolom tablet, 5 kolom Desktop -->
                <div class="grid grid-cols-2 gap-2.5 md:grid-cols-3 lg:grid-cols-5">
                    @foreach($daftarBarang as $barang)
                        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs hover:shadow-md hover:border-sky-200 transition-all duration-200 overflow-hidden relative flex flex-col group">
                            <a href="{{ url('/customer/barang/' . ($barang->slug ?? $barang->id)) }}" class="block relative w-full aspect-square bg-slate-50 overflow-hidden">
                                @if($barang->cover_photo)
                                    <img src="{{ asset(str_replace('public/', '', $barang->cover_photo)) }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" alt="{{ $barang->nama }}">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-slate-300"><i class="fa-solid fa-image text-3xl"></i></div>
                                @endif
                                @if(isset($barang->jarak))
                                <div class="absolute bottom-2 left-2 bg-white/95 backdrop-blur-xs px-2 py-0.5 rounded-md text-[10px] font-bold text-sky-600 shadow-2xs border border-slate-200/70 flex items-center gap-1">
                                    <i class="fa-solid fa-location-dot text-[9px]"></i>
                                    <span>{{ number_format($barang->jarak, 1, ',', '') }} KM</span>
                                </div>
                                @endif
                            </a>
                            @php
                                $isFavorit = Auth::check() ? \App\Models\Wishlist::where('user_id', Auth::id())->where('barang_id', $barang->id)->exists() : false;
                            @endphp
                            <form action="{{ route('customer.wishlist.toggle', $barang->id) }}" method="POST" class="absolute top-2 right-2 z-10">
                                @csrf
                                <button type="submit" class="w-7 h-7 rounded-full bg-white/90 backdrop-blur-xs flex items-center justify-center {{ $isFavorit ? 'text-rose-500' : 'text-slate-400 hover:text-rose-400' }} shadow-2xs border border-slate-200/60 transition hover:scale-105">
                                    <i class="fa-solid fa-heart text-[11px]"></i>
                                </button>
                            </form>
                            <a href="{{ url('/customer/barang/' . ($barang->slug ?? $barang->id)) }}" class="p-2.5 sm:p-3 flex flex-col flex-1 justify-between bg-white">
                                <h4 class="text-[12px] sm:text-[13px] font-semibold text-slate-800 leading-snug line-clamp-2 mb-2 group-hover:text-sky-600 transition-colors">{{ $barang->nama }}</h4>
                                <div class="mt-auto">
                                    <div class="text-sky-600 font-extrabold text-[13.5px] sm:text-[15px]">
                                        @if($barang->is_kos && $barang->harga_bulanan)
                                            Rp{{ number_format($barang->harga_bulanan_customer ?? $barang->harga_bulanan, 0, ',', '.') }}<span class="text-[9.5px] text-slate-400 font-normal">/bln</span>
                                        @else
                                            Rp{{ number_format($barang->harga_sewa_customer, 0, ',', '.') }}<span class="text-[9.5px] text-slate-400 font-normal">/hari</span>
                                        @endif
                                    </div>
                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif
    </main>

    <!-- BOTTOM NAV (MOBILE ONLY) -->
    <div class="md:hidden fixed bottom-2.5 left-1/2 -translate-x-1/2 z-50 pointer-events-none w-full max-w-[280px] px-3">
        <nav class="pointer-events-auto bg-white/95 backdrop-blur-md border border-slate-200/90 shadow-md px-2 py-1 rounded-full flex items-center justify-between">
            <a href="{{ route('customer.home') }}" class="flex items-center gap-1.5 px-3 py-1 bg-sky-50 text-sky-600 rounded-full font-bold text-[11px] border border-sky-100 shadow-2xs">
                <i class="fa-solid fa-house text-[12px]"></i>
                <span>Beranda</span>
            </a>
            <a href="{{ route('customer.wishlist') }}" class="flex items-center gap-1.5 px-2.5 py-1 text-slate-400 hover:text-sky-500 rounded-full font-medium text-[11px] transition-colors">
                <i class="fa-solid fa-heart text-[13px]"></i>
                <span class="text-[10.5px]">Favorit</span>
            </a>
            <a href="{{ route('customer.dashboard') }}" class="flex items-center gap-1.5 px-2.5 py-1 text-slate-400 hover:text-sky-500 rounded-full font-medium text-[11px] transition-colors">
                <i class="fa-solid fa-user text-[13px]"></i>
                <span class="text-[10.5px]">Akun</span>
            </a>
        </nav>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const slider = document.getElementById('banner-slider');
            const dots = document.querySelectorAll('.banner-dot');
            if (slider && slider.children.length > 1) {
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

                let currentSlide = 0;
                const totalSlides = slider.children.length;

                setInterval(() => {
                    currentSlide = (currentSlide + 1) % totalSlides;
                    slider.scrollTo({
                        left: currentSlide * slider.clientWidth,
                        behavior: 'smooth'
                    });
                    updateDots(currentSlide);
                }, 5000);

                slider.addEventListener('scroll', () => {
                    const activeIndex = Math.round(slider.scrollLeft / slider.clientWidth);
                    updateDots(activeIndex);
                }, { passive: true });
            }
        });
    </script>
</body>
</html>
