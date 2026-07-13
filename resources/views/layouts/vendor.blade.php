<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Vendor Center - Rentify')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['"Plus Jakarta Sans"', 'sans-serif'] },
                    colors: {
                        navydark: '#0F172A',
                        brand: { deep: '#1E3A8A', main: '#2563EB', sky: '#38BDF8', light: '#E0F2FE' }
                    }
                }
            }
        }
    </script>
    <style>
        /* Smooth Scroll & Glassmorphism Utilities */
        html { scroll-behavior: smooth; }
        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.5);
        }
        .gradient-text {
            background: linear-gradient(135deg, #1E3A8A 0%, #38BDF8 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .gradient-bg {
            background: linear-gradient(135deg, #1E3A8A 0%, #2563EB 50%, #38BDF8 100%);
        }
    </style>
</head>
<body class="bg-brand-light font-sans text-slate-800 antialiased overflow-x-hidden">
    <div class="flex min-h-screen">
        
        <aside class="w-72 fixed inset-y-0 left-0 z-50 bg-navydark shadow-2xl flex flex-col transition-all duration-300">
            <div class="h-24 flex items-center px-8 border-b border-white/10">
                <a href="{{ url('/') }}" class="flex items-center gap-3">
                    <!-- MASUKKAN KODE INI -->
                <img src="{{ asset('images/logo.png') }}" alt="Logo Rentify" class="h-10 w-auto object-contain">
                    <span class="text-2xl font-extrabold text-white tracking-tight">Rentify<span class="text-brand-sky">.</span></span>
                </a>
            </div>

            <div class="flex-1 overflow-y-auto py-8 px-5 space-y-2">
                <p class="px-3 text-[11px] font-bold text-slate-500 uppercase tracking-widest mb-3">Menu Utama</p>
                
                <a href="{{ route('vendor.dashboard') }}" class="flex items-center gap-4 px-4 py-3.5 rounded-2xl font-medium transition-all duration-300 {{ request()->routeIs('vendor.dashboard') ? 'gradient-bg text-white shadow-lg shadow-brand-main/20' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                    <i class="fa-solid fa-chart-pie w-5 text-center text-lg"></i> <span>Dashboard</span>
                </a>

                <a href="{{ route('vendor.barang.index') }}" class="flex items-center gap-4 px-4 py-3.5 rounded-2xl font-medium transition-all duration-300 {{ request()->routeIs('vendor.barang.*') ? 'gradient-bg text-white shadow-lg shadow-brand-main/20' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                    <i class="fa-solid fa-box-open w-5 text-center text-lg"></i> <span>Manajemen Produk</span>
                </a>

                <a href="{{ route('vendor.pesanan.index') }}" class="flex items-center gap-4 px-4 py-3.5 rounded-2xl font-medium transition-all duration-300 {{ request()->routeIs('vendor.pesanan.*') ? 'gradient-bg text-white shadow-lg shadow-brand-main/20' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                    <i class="fa-solid fa-clipboard-list w-5 text-center text-lg"></i> <span>Pesanan Masuk</span>
                </a>

                <a href="{{ route('vendor.saldo.index') }}" class="flex items-center gap-4 px-4 py-3.5 rounded-2xl font-medium transition-all duration-300 {{ request()->routeIs('vendor.saldo.*') ? 'gradient-bg text-white shadow-lg shadow-brand-main/20' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                    <i class="fa-solid fa-wallet w-5 text-center text-lg"></i> <span>Saldo & Penarikan</span>
                </a>

                <div class="my-6 border-t border-white/5"></div>
                <p class="px-3 text-[11px] font-bold text-slate-500 uppercase tracking-widest mb-3">Toko</p>

               <a href="{{ route('vendor.pengaturan.index') }}" class="flex items-center gap-4 px-4 py-3.5 rounded-2xl font-medium transition-all duration-300 {{ request()->routeIs('vendor.pengaturan.*') ? 'gradient-bg text-white shadow-lg shadow-brand-main/20' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                <i class="fa-solid fa-store-gear w-5 text-center text-lg"></i> <span>Pengaturan Toko</span>
            </a>
            </div>

            <div class="p-5 border-t border-white/10">
                <div class="flex items-center gap-3 px-4 py-3 rounded-2xl bg-white/5 border border-white/10">
                    <div class="w-10 h-10 rounded-full bg-slate-700 flex items-center justify-center text-white overflow-hidden">
                        <i class="fa-solid fa-user"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-bold text-white truncate">{{ Auth::user()->vendor_name ?? Auth::user()->name }}</p>
                        <p class="text-xs text-brand-sky truncate">Mitra Vendor</p>
                    </div>
                </div>
                
                <form method="POST" action="{{ route('logout') }}" class="mt-3">
                    @csrf
                    <button type="submit" class="w-full flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-bold text-rose-400 hover:bg-rose-500/10 hover:text-rose-300 rounded-xl transition-colors">
                        <i class="fa-solid fa-power-off"></i> Keluar
                    </button>
                </form>
            </div>
        </aside>

        <main class="flex-1 ml-72">
            @yield('content')
        </main>
        
    </div>
    @stack('scripts')
</body>
</html>