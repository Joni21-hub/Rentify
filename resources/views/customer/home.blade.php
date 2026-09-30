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
    <header class="hidden md:flex bg-white sticky top-0 z-50 shadow-sm border-b border-slate-100">
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
                <a href="{{ route('login') }}" class="bg-sky-500 hover:bg-sky-600 text-white font-bold px-5 py-2 rounded-full text-sm transition">
                    Masuk
                </a>
                @endauth
            </nav>
        </div>
    </header>

    <!-- ===== TOP NAVBAR (MOBILE ONLY) ===== -->
    <header class="md:hidden bg-white sticky top-0 z-50 shadow-sm px-3 py-2.5 flex gap-3 items-center">
        <a href="{{ route('customer.home') }}" class="flex-shrink-0 flex items-center gap-1.5">
            <img src="https://res.cloudinary.com/fnf8f1pm/image/upload/v1784199454/gambar_logo_trerjo.png" class="h-6 object-contain" alt="Logo">
            <span class="text-lg font-black text-sky-500 tracking-tighter">Rentify</span>
        </a>
        <a href="{{ route('customer.search') }}" class="flex-1 flex items-center bg-slate-50 rounded-full px-3 py-2 text-slate-400 text-[13px] border border-sky-100 hover:border-sky-300 transition">
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
    <main class="pb-20 md:pb-8 md:max-w-7xl md:mx-auto md:px-6 md:mt-4">

        <!-- BANNER PROMO -->
        <section class="mb-4 px-2 md:px-0">
            @if(isset($banners) && $banners->count() > 0)
                <div id="banner-slider" class="flex overflow-x-auto gap-2 scrollbar-hide snap-x md:grid md:grid-cols-1">
                    @foreach($banners as $banner)
                        <div class="min-w-full snap-center rounded-xl shadow-sm relative overflow-hidden flex-shrink-0 h-36 md:h-56 bg-slate-200">
                            <img src="{{ asset($banner->gambar_url) }}" class="w-full h-full object-cover">
                        </div>
                    @endforeach
                </div>
            @endif
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
                        <a href="{{ route('customer.lokasi') }}" class="inline-flex items-center gap-2 bg-white text-sky-600 hover:bg-sky-50 font-black text-sm px-6 py-3 rounded-full shadow-lg transition-transform hover:scale-105">
                            <i class="fa-solid fa-map-pin"></i> Atur Titik Lokasi
                        </a>
                    </div>
                </div>
            </section>

        @elseif($daftarBarang->isEmpty())
            <section class="px-3 mt-6 md:px-0">
                <div class="bg-white rounded-2xl p-8 text-center shadow-sm border border-sky-100 max-w-md mx-auto">
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
            <section class="px-3 md:px-0 mt-2">
                <div class="flex items-center justify-between mb-3 px-1 md:px-0">
                    <h3 class="font-black text-sky-600 text-[14px] uppercase tracking-wide">Di Sekitar Anda</h3>
                </div>
                <!-- GRID: 2 kolom HP, 3 kolom tablet, 5 kolom Desktop -->
                <div class="grid grid-cols-2 gap-2.5 md:grid-cols-3 lg:grid-cols-5">
                    @foreach($daftarBarang as $barang)
                        <div class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden relative flex flex-col hover:shadow-md transition">
                            <a href="{{ url('/customer/barang/' . ($barang->slug ?? $barang->id)) }}" class="block relative w-full aspect-square bg-white p-1">
                                @if($barang->cover_photo)
                                    <img src="{{ asset(str_replace('public/', '', $barang->cover_photo)) }}" class="w-full h-full object-contain">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-slate-200"><i class="fa-solid fa-image text-3xl"></i></div>
                                @endif
                                @if(isset($barang->jarak))
                                <div class="absolute bottom-2 left-2 bg-white/90 backdrop-blur-sm px-2 py-0.5 rounded text-[10px] font-bold text-sky-600 shadow-sm border border-sky-100">
                                    <i class="fa-solid fa-location-dot mr-1"></i>{{ number_format($barang->jarak, 1, ',', '') }} KM
                                </div>
                                @endif
                            </a>
                            @php
                                $isFavorit = Auth::check() ? \App\Models\Wishlist::where('user_id', Auth::id())->where('barang_id', $barang->id)->exists() : false;
                            @endphp
                            <form action="{{ route('customer.wishlist.toggle', $barang->id) }}" method="POST" class="absolute top-2 right-2 z-10">
                                @csrf
                                <button type="submit" class="w-7 h-7 bg-white/90 backdrop-blur-md rounded-full flex items-center justify-center {{ $isFavorit ? 'text-rose-500' : 'text-slate-300' }} shadow-sm border border-slate-100">
                                    <i class="fa-solid fa-heart text-[11px]"></i>
                                </button>
                            </form>
                            <a href="{{ url('/customer/barang/' . ($barang->slug ?? $barang->id)) }}" class="p-2.5 flex flex-col flex-1 justify-between border-t border-slate-50">
                                <h4 class="text-[12.5px] font-medium text-slate-700 leading-snug line-clamp-2 mb-1.5">{{ $barang->nama }}</h4>
                                <div class="text-sky-600 font-black text-[14.5px]">
                                    Rp{{ number_format($barang->harga_sewa_customer, 0, ',', '.') }}<span class="text-[9px] text-slate-400 font-medium">/hari</span>
                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif
    </main>

    <!-- BOTTOM NAV (MOBILE ONLY) -->
    <div class="md:hidden fixed bottom-4 left-0 w-full z-50 flex justify-center pointer-events-none">
        <nav class="bg-white/70 backdrop-blur-xl shadow-lg border border-white/50 rounded-full pointer-events-auto px-6 py-2.5 mx-4 flex justify-around items-center gap-8">
            <a href="{{ route('customer.home') }}" class="flex flex-col items-center text-sky-600">
                <i class="fa-solid fa-house text-[18px] mb-0.5"></i><span class="text-[9px] font-black">Beranda</span>
            </a>
            <a href="{{ route('customer.wishlist') }}" class="flex flex-col items-center text-slate-400 hover:text-sky-500 transition-colors">
                <i class="fa-solid fa-heart text-[18px] mb-0.5"></i><span class="text-[9px] font-bold">Favorit</span>
            </a>
            <a href="{{ route('customer.dashboard') }}" class="flex flex-col items-center text-slate-400 hover:text-sky-500 transition-colors">
                <i class="fa-solid fa-user text-[18px] mb-0.5"></i><span class="text-[9px] font-bold">Akun</span>
            </a>
        </nav>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const slider = document.getElementById('banner-slider');
            if (slider && slider.children.length > 1) {
                setInterval(() => {
                    let maxScroll = slider.scrollWidth - slider.clientWidth;
                    let nextScroll = slider.scrollLeft + slider.clientWidth;
                    if (nextScroll > maxScroll + 10) {
                        slider.scrollTo({ left: 0, behavior: 'smooth' });
                    } else {
                        slider.scrollTo({ left: nextScroll, behavior: 'smooth' });
                    }
                }, 7000);
            }
        });
    </script>
</body>
</html>
