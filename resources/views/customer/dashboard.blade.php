<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Saya - Rentify</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        @keyframes gradientFlow {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        .bg-flowing {
            background: linear-gradient(-45deg, #0284c7, #38bdf8, #0ea5e9, #0369a1);
            background-size: 300% 300%;
            animation: gradientFlow 15s ease infinite;
        }
        .glass-panel {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(25px);
            -webkit-backdrop-filter: blur(25px);
            border: 1px solid rgba(255, 255, 255, 0.4);
            border-top: 1px solid rgba(255, 255, 255, 0.7);
            border-left: 1px solid rgba(255, 255, 255, 0.7);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            border-radius: 1.5rem;
        }
        .glass-menu {
            background: rgba(255, 255, 255, 0.6);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.8);
            transition: all 0.3s ease;
        }
        .glass-menu:hover {
            background: rgba(255, 255, 255, 0.8);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        }
        .sparkle {
            position: absolute;
            width: 3px; height: 3px;
            background-color: white;
            border-radius: 50%;
            opacity: 0;
            animation: twinkle 4s infinite ease-in-out;
        }
        @keyframes twinkle {
            0%, 100% { opacity: 0; transform: scale(0.5); }
            50% { opacity: 0.8; transform: scale(1.5); box-shadow: 0 0 10px rgba(255,255,255,1); }
        }
        .bottom-nav-glass {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-top: 1px solid rgba(255, 255, 255, 1);
        }
    </style>
</head>

<!-- =================== MOBILE VERSION (body bg-flowing) =================== -->
<body class="bg-flowing min-h-screen text-slate-800 pb-24 md:pb-0">

    <!-- Efek Bintang -->
    <div class="sparkle" style="top:10%;left:10%;animation-delay:0s;"></div>
    <div class="sparkle" style="top:25%;right:15%;animation-delay:1.5s;"></div>
    <div class="sparkle" style="top:50%;left:20%;animation-delay:0.7s;"></div>
    <div class="sparkle hidden md:block" style="top:75%;right:30%;animation-delay:2s;"></div>
    <div class="sparkle hidden md:block" style="top:40%;right:40%;animation-delay:1s;"></div>

    <!-- ===== DESKTOP TOP NAVBAR (HIDDEN ON MOBILE) ===== -->
    <header class="hidden md:flex bg-white sticky top-0 z-50 shadow-sm border-b border-slate-100">
        <div class="max-w-6xl mx-auto w-full px-6 py-3 flex items-center gap-4">
            <a href="{{ route('customer.home') }}" class="flex items-center gap-2 flex-shrink-0">
                <img src="https://res.cloudinary.com/fnf8f1pm/image/upload/v1784199454/gambar_logo_trerjo.png" class="h-8 object-contain" alt="Logo">
                <span class="text-xl font-black text-sky-500 tracking-tighter">Rentify</span>
            </a>
            <div class="flex-1"></div>
            <nav class="flex items-center gap-1">
                <a href="{{ route('customer.home') }}" class="flex flex-col items-center px-4 py-2 text-slate-400 hover:text-sky-500 rounded-xl transition">
                    <i class="fa-solid fa-house text-lg"></i>
                    <span class="text-[10px] font-bold mt-0.5">Beranda</span>
                </a>
                <a href="{{ route('customer.wishlist') }}" class="flex flex-col items-center px-4 py-2 text-slate-400 hover:text-sky-500 rounded-xl transition">
                    <i class="fa-solid fa-heart text-lg"></i>
                    <span class="text-[10px] font-bold mt-0.5">Favorit</span>
                </a>
                <a href="{{ route('customer.keranjang') }}" class="flex flex-col items-center px-4 py-2 text-slate-400 hover:text-sky-500 rounded-xl transition">
                    <i class="fa-solid fa-cart-shopping text-lg"></i>
                    <span class="text-[10px] font-bold mt-0.5">Keranjang</span>
                </a>
                <a href="{{ route('customer.dashboard') }}" class="flex flex-col items-center px-4 py-2 text-sky-600 rounded-xl">
                    <i class="fa-solid fa-user text-lg"></i>
                    <span class="text-[10px] font-extrabold mt-0.5">Akun</span>
                </a>
            </nav>
        </div>
    </header>

    <!-- ===== MOBILE: Full Screen Profile ===== -->
    <div class="md:hidden max-w-md mx-auto min-h-screen relative z-10">
        <!-- HEADER GLASSMORPHISM -->
        <div class="px-5 pt-10 pb-8 text-center text-white relative">
            <a href="{{ route('customer.settings') }}" class="absolute top-8 right-6 w-10 h-10 bg-white/10 backdrop-blur-md rounded-full flex items-center justify-center border border-white/20 text-white hover:bg-white/30 transition shadow-lg z-20">
                <i class="fa-solid fa-gear text-lg"></i>
            </a>
            <h1 class="text-xl font-extrabold mb-6 tracking-wide drop-shadow-md">Profil Saya</h1>
            <div class="relative inline-block mb-4">
                <div class="w-24 h-24 bg-white/20 backdrop-blur-md rounded-full overflow-hidden flex items-center justify-center text-white text-4xl font-black shadow-2xl border-4 border-white/50 relative z-10">
                    @if(Auth::user()->foto_profil)
                        <img src="{{ str_starts_with(Auth::user()->foto_profil, 'http') ? Auth::user()->foto_profil : Storage::url(Auth::user()->foto_profil) }}" alt="Foto" class="w-full h-full object-cover">
                    @else
                        {{ substr(Auth::user()->name ?? 'C', 0, 1) }}
                    @endif
                </div>
                
            </div>
            <h2 class="text-2xl font-black drop-shadow-md">{{ Auth::user()->name ?? 'Customer' }}</h2>
            <div class="inline-flex items-center gap-2 mt-2 bg-white/20 backdrop-blur-md px-4 py-1.5 rounded-full border border-white/30 text-xs font-semibold">
                <i class="fa-solid fa-envelope"></i>
                <span>{{ Auth::user()->email ?? (Auth::user()->whatsapp ?? 'customer@rentify.com') }}</span>
            </div>
            @if(Auth::user()->whatsapp_verified_at)
            <div class="mt-2 inline-flex items-center gap-1.5 text-[10px] font-bold text-emerald-300 bg-emerald-900/30 px-3 py-1 rounded-full border border-emerald-500/30">
                <i class="fa-solid fa-shield-check"></i> WA Terverifikasi
            </div>
            @endif
        </div>
        <div class="px-5 space-y-4">
            <!-- PESANAN SAYA -->
            <div class="glass-panel p-4 rounded-2xl relative overflow-hidden">
                <div class="flex justify-between items-center mb-4 pb-3 border-b border-white/20">
                    <h3 class="font-extrabold text-slate-800 text-sm">Pesanan Saya</h3>
                    <a href="{{ route('customer.pesanan') }}" class="text-[10px] font-bold text-blue-600 hover:text-blue-800 transition flex items-center gap-1">Lihat Riwayat <i class="fa-solid fa-chevron-right text-[8px]"></i></a>
                </div>
                <div class="grid grid-cols-3 gap-2 text-center relative z-10">
                    <a href="{{ route('customer.pesanan') }}" class="flex flex-col items-center group relative">
                        <div class="w-10 h-10 bg-white/60 rounded-xl flex items-center justify-center text-slate-600 group-hover:text-blue-600 group-hover:bg-white shadow-sm transition mb-1.5 relative">
                            <i class="fa-solid fa-clock-rotate-left text-lg"></i>
                            @if(isset($countMenunggu) && $countMenunggu > 0)
                                <span class="absolute -top-1 -right-1 bg-rose-500 text-white text-[9px] font-bold w-4 h-4 rounded-full flex items-center justify-center shadow-sm border border-white">{{ $countMenunggu }}</span>
                            @endif
                        </div>
                        <span class="text-[9px] font-bold text-slate-700 leading-tight">Menunggu<br>Konfirmasi</span>
                    </a>
                    <a href="{{ route('customer.pesanan') }}" class="flex flex-col items-center group relative">
                        <div class="w-10 h-10 bg-white/60 rounded-xl flex items-center justify-center text-slate-600 group-hover:text-blue-600 group-hover:bg-white shadow-sm transition mb-1.5 relative">
                            <i class="fa-solid fa-truck-fast text-lg"></i>
                            @if(isset($countDiproses) && $countDiproses > 0)
                                <span class="absolute -top-1 -right-1 bg-rose-500 text-white text-[9px] font-bold w-4 h-4 rounded-full flex items-center justify-center shadow-sm border border-white">{{ $countDiproses }}</span>
                            @endif
                        </div>
                        <span class="text-[9px] font-bold text-slate-700 leading-tight">Sedang<br>Berjalan</span>
                    </a>
                    <a href="{{ route('customer.pesanan') }}" class="flex flex-col items-center group relative">
                        <div class="w-10 h-10 bg-white/60 rounded-xl flex items-center justify-center text-slate-600 group-hover:text-emerald-500 group-hover:bg-white shadow-sm transition mb-1.5">
                            <i class="fa-solid fa-star text-lg"></i>
                        </div>
                        <span class="text-[9px] font-bold text-slate-700 leading-tight">Beri<br>Ulasan</span>
                    </a>
                </div>
            </div>
            <!-- GANTI TITIK LOKASI -->
            <a href="{{ url('/customer/lokasi') }}" class="flex items-center justify-between glass-menu p-4 rounded-2xl group">
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 bg-gradient-to-r from-sky-400 to-blue-500 text-white rounded-full flex items-center justify-center shadow-md">
                        <i class="fa-solid fa-location-dot"></i>
                    </div>
                    <div>
                        <span class="block font-extrabold text-slate-800 text-sm">Ganti Titik Lokasi</span>
                        <span class="block text-[10px] font-medium text-slate-500 mt-0.5">Atur GPS untuk cari barang terdekat</span>
                    </div>
                </div>
                <i class="fa-solid fa-chevron-right text-slate-400 text-sm group-hover:text-blue-600 transition"></i>
            </a>
            <!-- PUSAT BANTUAN -->
            @php $linkWaAdmin = "https://wa.me/6283183494835?text=" . urlencode("Halo admin, saya mengalami masalah di Rentify, mohon bantuannya."); @endphp
            <a href="{{ $linkWaAdmin }}" target="_blank" class="flex items-center justify-between glass-menu p-4 rounded-2xl group">
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 bg-sky-500 text-white rounded-xl flex items-center justify-center shadow-md shadow-sky-500/40">
                        <i class="fa-brands fa-whatsapp text-xl"></i>
                    </div>
                    <div>
                        <span class="block font-extrabold text-sky-700 text-sm">Pusat Bantuan</span>
                        <span class="block text-[10px] font-bold text-sky-600/70 mt-0.5">Hubungi Admin Rentify (24/7)</span>
                    </div>
                </div>
                <i class="fa-solid fa-arrow-right text-sky-400 text-sm"></i>
            </a>
            <!-- KELUAR -->
            <form action="/logout" method="POST" class="w-full pt-4 pb-8 flex justify-center">
                @csrf
                <button type="submit" class="flex items-center justify-center bg-white/20 backdrop-blur-md px-6 py-3 rounded-full border border-white/50 hover:bg-rose-500 hover:border-rose-500 hover:text-white transition-all gap-2 group shadow-lg text-white w-full max-w-[200px]">
                    <i class="fa-solid fa-power-off text-sm"></i>
                    <span class="font-extrabold text-xs tracking-wide">KELUAR AKUN</span>
                </button>
            </form>
        </div>
        <!-- BOTTOM NAV MOBILE -->
        <nav class="md:hidden fixed bottom-0 left-0 w-full bg-white shadow-[0_-2px_10px_rgba(0,0,0,0.03)] border-t border-slate-100 z-50">
            <div class="max-w-md mx-auto flex justify-around items-center pt-2.5 pb-2.5">
                <a href="{{ route('customer.home') }}" class="flex flex-col items-center text-slate-400 hover:text-sky-500 transition-colors">
                    <i class="fa-solid fa-house text-[18px] mb-0.5"></i><span class="text-[9px] font-bold">Beranda</span>
                </a>
                <a href="{{ route('customer.wishlist') }}" class="flex flex-col items-center text-slate-400 hover:text-sky-500 transition-colors">
                    <i class="fa-solid fa-heart text-[18px] mb-0.5"></i><span class="text-[9px] font-bold">Favorit</span>
                </a>
                <a href="#" class="flex flex-col items-center text-sky-600">
                    <i class="fa-solid fa-user text-[18px] mb-0.5"></i><span class="text-[9px] font-black">Akun</span>
                </a>
            </div>
        </nav>
    </div>

    <!-- ===== DESKTOP: Sidebar + Content Layout ===== -->
    <div class="hidden md:block max-w-5xl mx-auto px-6 py-8">
        <div class="flex gap-6">
            <!-- SIDEBAR KIRI (PROFIL) -->
            <aside class="w-72 flex-shrink-0">
                <!-- Kartu Profil -->
                <div class="bg-white/20 backdrop-blur-xl border border-white/40 rounded-2xl p-6 text-white text-center shadow-lg mb-4">
                    <div class="w-20 h-20 rounded-full overflow-hidden mx-auto mb-3 border-4 border-white/50 shadow-xl">
                        @if(Auth::user()->foto_profil)
                            <img src="{{ str_starts_with(Auth::user()->foto_profil, 'http') ? Auth::user()->foto_profil : Storage::url(Auth::user()->foto_profil) }}" alt="Foto" class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full bg-white/30 flex items-center justify-center text-3xl font-black">{{ substr(Auth::user()->name ?? 'C', 0, 1) }}</div>
                        @endif
                    </div>
                    <h2 class="font-black text-lg">{{ Auth::user()->name ?? 'Customer' }}</h2>
                    <p class="text-sky-200 text-[11px] mt-1 break-all">{{ Auth::user()->email ?? Auth::user()->whatsapp }}</p>
                    @if(Auth::user()->whatsapp_verified_at)
                    <div class="mt-2 inline-flex items-center gap-1 text-[10px] font-bold text-emerald-300 bg-emerald-900/30 px-3 py-1 rounded-full border border-emerald-500/30">
                        <i class="fa-solid fa-shield-check"></i> WA Terverifikasi
                    </div>
                    @endif
                </div>
                <!-- Menu Navigasi Sidebar -->
                <div class="glass-panel overflow-hidden">
                    <a href="{{ route('customer.dashboard') }}" class="flex items-center gap-3 px-5 py-3.5 bg-white/40 border-l-4 border-sky-500 text-sky-700 font-bold text-sm">
                        <i class="fa-solid fa-user w-5 text-center"></i> Profil Saya
                    </a>
                    <a href="{{ route('customer.pesanan') }}" class="flex items-center gap-3 px-5 py-3.5 hover:bg-white/40 text-slate-600 hover:text-sky-600 font-semibold text-sm transition border-b border-slate-50">
                        <i class="fa-solid fa-box-open w-5 text-center"></i> Pesanan Saya
                    </a>
                    <a href="{{ route('customer.wishlist') }}" class="flex items-center gap-3 px-5 py-3.5 hover:bg-white/40 text-slate-600 hover:text-sky-600 font-semibold text-sm transition border-b border-slate-50">
                        <i class="fa-solid fa-heart w-5 text-center"></i> Favorit
                    </a>
                    <a href="{{ route('customer.lokasi') }}" class="flex items-center gap-3 px-5 py-3.5 hover:bg-white/40 text-slate-600 hover:text-sky-600 font-semibold text-sm transition border-b border-slate-50">
                        <i class="fa-solid fa-location-dot w-5 text-center"></i> Titik Lokasi
                    </a>
                    <a href="{{ route('customer.settings') }}" class="flex items-center gap-3 px-5 py-3.5 hover:bg-white/40 text-slate-600 hover:text-sky-600 font-semibold text-sm transition border-b border-slate-50">
                        <i class="fa-solid fa-gear w-5 text-center"></i> Pengaturan Akun
                    </a>
                    <form action="/logout" method="POST">
                        @csrf
                        <button type="submit" class="w-full flex items-center gap-3 px-5 py-3.5 hover:bg-rose-50 text-slate-500 hover:text-rose-500 font-semibold text-sm transition text-left">
                            <i class="fa-solid fa-power-off w-5 text-center"></i> Keluar Akun
                        </button>
                    </form>
                </div>
            </aside>

            <!-- KONTEN KANAN -->
            <div class="flex-1 space-y-5">
                <!-- Ringkasan Pesanan -->
                <div class="glass-panel p-6">
                    <div class="flex justify-between items-center mb-5">
                        <h3 class="font-extrabold text-slate-800 text-base">Pesanan Saya</h3>
                        <a href="{{ route('customer.pesanan') }}" class="text-xs font-bold text-sky-600 hover:underline">Lihat Semua <i class="fa-solid fa-chevron-right text-[9px]"></i></a>
                    </div>
                    <div class="grid grid-cols-3 gap-4">
                        <a href="{{ route('customer.pesanan') }}" class="flex flex-col items-center gap-2 p-4 rounded-xl bg-sky-50 hover:bg-sky-100 transition group">
                            <div class="relative">
                                <i class="fa-solid fa-clock-rotate-left text-2xl text-sky-500"></i>
                                @if(isset($countMenunggu) && $countMenunggu > 0)
                                    <span class="absolute -top-2 -right-2 bg-rose-500 text-white text-[9px] font-bold w-4 h-4 rounded-full flex items-center justify-center">{{ $countMenunggu }}</span>
                                @endif
                            </div>
                            <span class="text-xs font-bold text-slate-600 text-center leading-tight">Menunggu Konfirmasi</span>
                        </a>
                        <a href="{{ route('customer.pesanan') }}" class="flex flex-col items-center gap-2 p-4 rounded-xl bg-emerald-50 hover:bg-emerald-100 transition group">
                            <div class="relative">
                                <i class="fa-solid fa-truck-fast text-2xl text-emerald-500"></i>
                                @if(isset($countDiproses) && $countDiproses > 0)
                                    <span class="absolute -top-2 -right-2 bg-rose-500 text-white text-[9px] font-bold w-4 h-4 rounded-full flex items-center justify-center">{{ $countDiproses }}</span>
                                @endif
                            </div>
                            <span class="text-xs font-bold text-slate-600 text-center leading-tight">Sedang Berjalan</span>
                        </a>
                        <a href="{{ route('customer.pesanan') }}" class="flex flex-col items-center gap-2 p-4 rounded-xl bg-amber-50 hover:bg-amber-100 transition group">
                            <i class="fa-solid fa-star text-2xl text-amber-500"></i>
                            <span class="text-xs font-bold text-slate-600 text-center leading-tight">Beri Ulasan</span>
                        </a>
                    </div>
                </div>

                <!-- Menu Cepat -->
                <div class="glass-panel p-6">
                    <h3 class="font-extrabold text-slate-800 text-base mb-4">Aksi Cepat</h3>
                    <div class="grid grid-cols-2 gap-3">
                        <a href="{{ url('/customer/lokasi') }}" class="flex items-center gap-3 p-4 rounded-xl border border-slate-100 hover:border-sky-200 hover:bg-sky-50 transition">
                            <div class="w-10 h-10 rounded-full bg-sky-100 text-sky-600 flex items-center justify-center">
                                <i class="fa-solid fa-location-dot"></i>
                            </div>
                            <div>
                                <span class="block font-bold text-sm text-slate-700">Ganti Lokasi</span>
                                <span class="block text-[11px] text-slate-400">Atur titik GPS Anda</span>
                            </div>
                        </a>
                        @php $linkWaAdmin = "https://wa.me/6283183494835?text=" . urlencode("Halo admin, saya mengalami masalah di Rentify, mohon bantuannya."); @endphp
                        <a href="{{ $linkWaAdmin }}" target="_blank" class="flex items-center gap-3 p-4 rounded-xl border border-slate-100 hover:border-emerald-200 hover:bg-emerald-50 transition">
                            <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center">
                                <i class="fa-brands fa-whatsapp text-xl"></i>
                            </div>
                            <div>
                                <span class="block font-bold text-sm text-slate-700">Pusat Bantuan</span>
                                <span class="block text-[11px] text-slate-400">Hubungi Admin 24/7</span>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>
