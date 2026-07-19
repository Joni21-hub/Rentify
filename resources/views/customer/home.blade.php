<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rentify - Sewa Alat Mudah & Cepat</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .scrollbar-hide::-webkit-scrollbar { display: none; } 
        .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
        body { 
            background: linear-gradient(to bottom, #e0f2fe, #ffffff);
            font-family: 'Segoe UI', Tahoma, sans-serif; 
            min-height: 100vh;
        }
    </style>
</head>
<body class="text-slate-800 pb-20 antialiased">

    <!-- HEADER -->
    <header class="bg-white sticky top-0 z-50 shadow-sm px-3 py-2.5 flex gap-3 items-center">
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

    <!-- MAIN -->
    <main class="max-w-md mx-auto mt-2">
        <!-- BANNER PROMO -->
        <section class="mb-3 px-2">
            @if(isset($banners) && $banners->count() > 0)
                <div id="banner-slider" class="flex overflow-x-auto gap-2 scrollbar-hide snap-x">
                    @foreach($banners as $banner)
                        <div class="min-w-full snap-center rounded-lg shadow-sm relative overflow-hidden flex-shrink-0 h-32 bg-slate-200">
                            <img src="{{ asset($banner->gambar_url) }}" class="w-full h-full object-cover">
                        </div>
                    @endforeach
                </div>
            @endif
        </section>

        <!-- KONDISI JIKA USER BELUM ATUR LOKASI -->
        @if(isset($butuhLokasi) && $butuhLokasi)
            <section class="px-3 mt-6">
                <div class="bg-gradient-to-br from-sky-400 to-[#0369a1] rounded-2xl p-6 text-center shadow-[0_4px_20px_rgba(14,165,233,0.3)] border border-sky-300 relative overflow-hidden">
                    <div class="absolute -top-6 -right-6 w-24 h-24 bg-white/10 rounded-full blur-xl"></div>
                    <div class="absolute -bottom-8 -left-8 w-32 h-32 bg-sky-200/20 rounded-full blur-2xl"></div>
                    
                    <div class="relative z-10">
                        <div class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center text-white text-3xl mx-auto mb-4 backdrop-blur-sm border border-white/30 shadow-inner">
                            <i class="fa-solid fa-location-dot"></i>
                        </div>
                        <h3 class="text-white font-black text-lg mb-2">Tentukan Lokasimu Dulu Yuk!</h3>
                        <p class="text-sky-100 text-[13px] font-medium leading-relaxed mb-6">
                            Biar kami bisa mencarikan barang sewaan terdekat (maksimal 50 KM) dari tempatmu.
                        </p>
                        <a href="{{ route('customer.lokasi') }}" class="inline-flex items-center gap-2 bg-white text-sky-600 hover:bg-sky-50 font-black text-sm px-6 py-3 rounded-full shadow-lg transition-transform hover:scale-105">
                            <i class="fa-solid fa-map-pin"></i> Atur Titik Lokasi
                        </a>
                    </div>
                </div>
            </section>

        <!-- KONDISI JIKA SUDAH ADA LOKASI TAPI BARANG KOSONG DI RADIUS 50KM -->
        @elseif($daftarBarang->isEmpty())
            <section class="px-3 mt-6">
                <div class="bg-white rounded-2xl p-8 text-center shadow-sm border border-sky-100">
                    <div class="w-20 h-20 bg-sky-50 text-sky-400 rounded-full flex items-center justify-center text-4xl mx-auto mb-4">
                        <i class="fa-solid fa-face-frown-open"></i>
                    </div>
                    <h3 class="text-slate-700 font-black text-lg mb-2">Yah, Belum Ada Barang Nih...</h3>
                    <p class="text-slate-500 text-[13px] leading-relaxed mb-6">
                        Maaf banget, sepertinya belum ada toko yang menyewakan barang di radius 50 KM dari lokasimu saat ini.
                    </p>
                    <a href="{{ route('customer.lokasi') }}" class="inline-flex items-center gap-2 bg-sky-100 text-sky-600 hover:bg-sky-200 font-bold text-[13px] px-5 py-2.5 rounded-xl transition">
                        <i class="fa-solid fa-location-crosshairs"></i> Ganti Titik Lokasi
                    </a>
                </div>
            </section>

        <!-- KONDISI NORMAL (TAMPILKAN BARANG) -->
        @else
            <section class="px-3">
                <div class="flex items-center justify-between mb-2.5 px-1">
                    <h3 class="font-black text-sky-600 text-[14px] uppercase tracking-wide">Di Sekitar Anda</h3>
                </div>
                
                <div class="grid grid-cols-2 gap-2.5">
                    @foreach($daftarBarang as $barang)
                        <div class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden relative flex flex-col hover:shadow-md transition">
                            
                            <a href="{{ url('/customer/barang/' . ($barang->slug ?? $barang->id)) }}" class="block relative w-full aspect-square bg-white p-1">
                                @if($barang->cover_photo)
                                    <img src="{{ asset(str_replace('public/', '', $barang->cover_photo)) }}" class="w-full h-full object-contain">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-slate-200"><i class="fa-solid fa-image text-3xl"></i></div>
                                @endif
                                
                                <!-- Label Jarak -->
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

    <!-- BOTTOM NAV -->
    <nav class="fixed bottom-0 left-0 w-full bg-white shadow-[0_-2px_10px_rgba(0,0,0,0.03)] border-t border-slate-100 pb-safe z-50">
        <div class="max-w-md mx-auto flex justify-around items-center pt-2.5 pb-2.5">
            <a href="{{ route('customer.home') }}" class="flex flex-col items-center text-sky-600">
                <i class="fa-solid fa-house text-[18px] mb-0.5"></i><span class="text-[9px] font-black">Beranda</span>
            </a>
            <a href="{{ route('customer.wishlist') }}" class="flex flex-col items-center text-slate-400 hover:text-sky-500 transition-colors">
                <i class="fa-solid fa-heart text-[18px] mb-0.5"></i><span class="text-[9px] font-bold">Favorit</span>
            </a>
            <a href="{{ route('customer.dashboard') }}" class="flex flex-col items-center text-slate-400 hover:text-sky-500 transition-colors">
                <i class="fa-solid fa-user text-[18px] mb-0.5"></i><span class="text-[9px] font-bold">Akun</span>
            </a>
        </div>
    </nav>

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