<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Akun Saya - Rentify</title>
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
<body class="bg-flowing min-h-screen text-slate-800 pb-24">

    <!-- Efek Bintang -->
    <div class="sparkle top-[10%] left-[10%]" style="animation-delay: 0s;"></div>
    <div class="sparkle top-[25%] right-[15%]" style="animation-delay: 1.5s;"></div>
    <div class="sparkle top-[50%] left-[20%]" style="animation-delay: 0.7s;"></div>

    <div class="max-w-md mx-auto min-h-screen relative z-10">
        
        <!-- HEADER GLASSMORPHISM -->
        <div class="px-5 pt-10 pb-8 text-center text-white relative">
            <!-- PENGATURAN ICON -->
            <a href="{{ route('customer.settings') }}" class="absolute top-8 right-6 w-10 h-10 bg-white/10 backdrop-blur-md rounded-full flex items-center justify-center border border-white/20 text-white hover:bg-white/30 transition shadow-lg z-20">
                <i class="fa-solid fa-gear text-lg"></i>
            </a>

            <h1 class="text-xl font-extrabold mb-6 tracking-wide drop-shadow-md">Profil Saya</h1>
            
            <div class="relative inline-block mb-4">
                <div class="w-24 h-24 bg-white/20 backdrop-blur-md rounded-full overflow-hidden flex items-center justify-center text-white text-4xl font-black shadow-2xl border-4 border-white/50 relative z-10">
                    @if(Auth::user()->foto_profil)
                        <img src="{{ Storage::url(Auth::user()->foto_profil) }}" alt="Foto" class="w-full h-full object-cover">
                    @else
                        {{ substr(Auth::user()->name ?? 'C', 0, 1) }}
                    @endif
                </div>
                <!-- Aura Glow -->
                <div class="absolute inset-0 bg-white/30 blur-2xl rounded-full scale-125"></div>
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
            
            <!-- PESANAN SAYA (SHOPEE STYLE) -->
            <div class="glass-panel p-4 rounded-2xl relative overflow-hidden">
                <div class="flex justify-between items-center mb-4 pb-3 border-b border-white/20">
                    <h3 class="font-extrabold text-slate-800 text-sm">Pesanan Saya</h3>
                    <a href="{{ route('customer.pesanan') }}" class="text-[10px] font-bold text-blue-600 hover:text-blue-800 transition flex items-center gap-1">
                        Lihat Riwayat <i class="fa-solid fa-chevron-right text-[8px]"></i>
                    </a>
                </div>
                
                <div class="grid grid-cols-3 gap-2 text-center relative z-10">
                    <!-- Menunggu -->
                    <a href="{{ route('customer.pesanan') }}" class="flex flex-col items-center group relative">
                        <div class="w-10 h-10 bg-white/60 rounded-xl flex items-center justify-center text-slate-600 group-hover:text-blue-600 group-hover:bg-white shadow-sm transition mb-1.5 relative">
                            <i class="fa-solid fa-clock-rotate-left text-lg"></i>
                            @if(isset($countMenunggu) && $countMenunggu > 0)
                                <span class="absolute -top-1 -right-1 bg-rose-500 text-white text-[9px] font-bold w-4 h-4 rounded-full flex items-center justify-center shadow-sm border border-white">{{ $countMenunggu }}</span>
                            @endif
                        </div>
                        <span class="text-[9px] font-bold text-slate-700 leading-tight">Menunggu<br>Konfirmasi</span>
                    </a>
                    
                    <!-- Berjalan -->
                    <a href="{{ route('customer.pesanan') }}" class="flex flex-col items-center group relative">
                        <div class="w-10 h-10 bg-white/60 rounded-xl flex items-center justify-center text-slate-600 group-hover:text-blue-600 group-hover:bg-white shadow-sm transition mb-1.5 relative">
                            <i class="fa-solid fa-truck-fast text-lg"></i>
                            @if(isset($countDiproses) && $countDiproses > 0)
                                <span class="absolute -top-1 -right-1 bg-rose-500 text-white text-[9px] font-bold w-4 h-4 rounded-full flex items-center justify-center shadow-sm border border-white">{{ $countDiproses }}</span>
                            @endif
                        </div>
                        <span class="text-[9px] font-bold text-slate-700 leading-tight">Sedang<br>Berjalan</span>
                    </a>
                    
                    <!-- Selesai -->
                    <a href="{{ route('customer.pesanan') }}" class="flex flex-col items-center group relative">
                        <div class="w-10 h-10 bg-white/60 rounded-xl flex items-center justify-center text-slate-600 group-hover:text-emerald-500 group-hover:bg-white shadow-sm transition mb-1.5 relative">
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
                        <i class="fa-solid fa-location-dot group-hover:bounce transition-transform"></i>
                    </div>
                    <div>
                        <span class="block font-extrabold text-slate-800 text-sm">Ganti Titik Lokasi</span>
                        <span class="block text-[10px] font-medium text-slate-500 mt-0.5">Atur GPS untuk cari barang terdekat</span>
                    </div>
                </div>
                <i class="fa-solid fa-chevron-right text-slate-400 text-sm group-hover:text-blue-600 transition"></i>
            </a>

            <!-- PUSAT BANTUAN -->
            @php 
                $pesanBantuan = "Halo admin, saya mengalami masalah di Rentify, mohon bantuannya.";
                $linkWaAdmin = "https://wa.me/6283183494835?text=" . urlencode($pesanBantuan);
            @endphp
            <a href="{{ $linkWaAdmin }}" target="_blank" class="flex items-center justify-between glass-menu p-4 rounded-2xl group border-sky-300">
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 bg-sky-500 text-white rounded-xl flex items-center justify-center shadow-md shadow-sky-500/40">
                        <i class="fa-brands fa-whatsapp text-xl group-hover:scale-110 transition-transform"></i>
                    </div>
                    <div>
                        <span class="block font-extrabold text-sky-700 text-sm">Pusat Bantuan</span>
                        <span class="block text-[10px] font-bold text-sky-600/70 mt-0.5">Hubungi Admin Rentify (24/7)</span>
                    </div>
                </div>
                <i class="fa-solid fa-arrow-right text-sky-400 text-sm group-hover:text-sky-600 transition"></i>
            </a>

            <!-- TOMBOL KELUAR AKUN -->
            <form action="/logout" method="POST" class="w-full pt-4 pb-8 flex justify-center">
                @csrf
                <button type="submit" class="flex items-center justify-center bg-white/20 backdrop-blur-md px-6 py-3 rounded-full border border-white/50 hover:bg-rose-500 hover:border-rose-500 hover:text-white transition-all gap-2 group shadow-lg text-white w-full max-w-[200px]">
                    <i class="fa-solid fa-power-off text-sm"></i>
                    <span class="font-extrabold text-xs tracking-wide">KELUAR AKUN</span>
                </button>
            </form>
            
        </div>
        
        <!-- BOTTOM NAVIGATION -->
        <nav class="fixed bottom-0 left-0 w-full bottom-nav-glass shadow-[0_-10px_30px_rgba(0,0,0,0.1)] rounded-t-3xl z-50 pb-safe">
            <div class="max-w-md mx-auto flex justify-between items-center px-10 py-4">
                <a href="{{ route('customer.home') }}" class="flex flex-col items-center text-slate-400 hover:text-blue-600 transition">
                    <i class="fa-solid fa-house text-xl mb-1.5"></i>
                    <span class="text-[10px] font-bold">Beranda</span>
                </a>
                <a href="{{ route('customer.wishlist') }}" class="flex flex-col items-center text-slate-400 hover:text-blue-600 transition">
                    <i class="fa-solid fa-heart text-xl mb-1.5"></i>
                    <span class="text-[10px] font-bold">Favorit</span>
                </a>
                <a href="#" class="flex flex-col items-center text-blue-600 relative">
                    <div class="absolute -top-3 w-10 h-1 bg-blue-600 rounded-full"></div>
                    <i class="fa-solid fa-user text-xl mb-1.5"></i>
                    <span class="text-[10px] font-extrabold">Akun</span>
                </a>
            </div>
        </nav>
    </div>
</body>
</html>