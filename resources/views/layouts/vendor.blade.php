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
    <link rel="stylesheet" href="{{ asset('rentify-theme.css') }}">
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['"Plus Jakarta Sans"', 'sans-serif'] },
                    colors: {
                        navydark: '#0F172A',
                        brand: { deep: '#0369a1', main: '#0ea5e9', sky: '#38BDF8', light: '#E0F2FE' }
                    }
                }
            }
        }
    </script>
    <style>
        /* Smooth Scroll & Glassmorphism Utilities */
        html { scroll-behavior: smooth; }
        
        .gradient-text {
            background: linear-gradient(135deg, #0369a1 0%, #38BDF8 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .gradient-bg {
            background: linear-gradient(135deg, #0369a1 0%, #0ea5e9 50%, #38BDF8 100%);
        }
    </style>
</head>
<body class="font-sans text-slate-800 antialiased overflow-x-hidden">
    <div class="flex min-h-screen">
        
        <aside class="w-72 fixed inset-y-0 left-0 z-50 bg-navydark shadow-2xl flex flex-col transition-all duration-300">
            <div class="h-24 flex items-center px-8 border-b border-white/10">
                <a href="{{ url('/') }}" class="flex items-center gap-3">
                    <img src="https://res.cloudinary.com/fnf8f1pm/image/upload/v1784199454/gambar_logo_trerjo.png" alt="Logo Rentify" class="h-10 w-auto object-contain">
                    <span class="text-2xl font-extrabold text-white tracking-tight">Rentify<span class="text-sky-500">.</span></span>
                </a>
            </div>

            <div class="flex-1 overflow-y-auto py-8 px-5 space-y-2">
                <p class="px-3 text-[11px] font-bold text-slate-500 uppercase tracking-widest mb-3">Menu Utama</p>
                
                <a href="{{ route('vendor.dashboard') }}" class="flex items-center gap-4 px-4 py-3.5 font-medium transition-all duration-300 {{ request()->routeIs('vendor.dashboard') ? 'rentify-btn text-white /20' : 'text-slate-400 hover:rentify-card/5 hover:text-white' }}">
                    <i class="fa-solid fa-chart-pie w-5 text-center text-lg"></i> <span>Dashboard</span>
                </a>

                <a href="{{ route('vendor.barang.index') }}" class="flex items-center gap-4 px-4 py-3.5 font-medium transition-all duration-300 {{ request()->routeIs('vendor.barang.*') ? 'rentify-btn text-white /20' : 'text-slate-400 hover:rentify-card/5 hover:text-white' }}">
                    <i class="fa-solid fa-box-open w-5 text-center text-lg"></i> <span>Manajemen Produk</span>
                </a>

                <a href="{{ route('vendor.pesanan.index') }}" class="flex items-center gap-4 px-4 py-3.5 font-medium transition-all duration-300 {{ request()->routeIs('vendor.pesanan.*') ? 'rentify-btn text-white /20' : 'text-slate-400 hover:rentify-card/5 hover:text-white' }}">
                    <i class="fa-solid fa-clipboard-list w-5 text-center text-lg"></i> <span>Pesanan Masuk</span>
                </a>

                <a href="{{ route('vendor.saldo.index') }}" class="flex items-center gap-4 px-4 py-3.5 font-medium transition-all duration-300 {{ request()->routeIs('vendor.saldo.*') ? 'rentify-btn text-white /20' : 'text-slate-400 hover:rentify-card/5 hover:text-white' }}">
                    <i class="fa-solid fa-wallet w-5 text-center text-lg"></i> <span>Saldo & Penarikan</span>
                </a>

                <a href="{{ route('vendor.voucher.index') }}" class="flex items-center gap-4 px-4 py-3.5 font-medium transition-all duration-300 {{ request()->routeIs('vendor.voucher.*') ? 'rentify-btn text-white /20' : 'text-slate-400 hover:rentify-card/5 hover:text-white' }}">
                    <i class="fa-solid fa-ticket w-5 text-center text-lg"></i> <span>Voucher Toko</span>
                </a>

                <div class="my-6 border-t border-white/5"></div>
                <p class="px-3 text-[11px] font-bold text-slate-500 uppercase tracking-widest mb-3">Toko</p>

                <a href="{{ route('vendor.pengaturan.index') }}" class="flex items-center gap-4 px-4 py-3.5 font-medium transition-all duration-300 {{ request()->routeIs('vendor.pengaturan.*') ? 'rentify-btn text-white /20' : 'text-slate-400 hover:rentify-card/5 hover:text-white' }}">
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
                        <p class="text-xs text-sky-500 truncate">Mitra Vendor</p>
                    </div>
                </div>

                <!-- Tombol Beralih ke Mode Customer -->
                <a href="{{ route('role.switch', 'customer') }}" class="w-full flex items-center justify-center gap-2 px-4 py-2.5 mt-3 text-xs font-bold text-sky-300 bg-sky-500/10 hover:bg-sky-500/20 border border-sky-400/20 rounded-xl transition-all">
                    <i class="fa-solid fa-cart-shopping"></i> Beralih ke Mode Customer
                </a>
                
                <form method="POST" action="{{ route('logout') }}" class="mt-2">
                    @csrf
                    <button type="submit" class="w-full flex items-center justify-center gap-2 px-4 py-2 text-xs font-bold text-rose-400 hover:bg-rose-500/10 hover:text-rose-300 rounded-xl transition-colors cursor-pointer">
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