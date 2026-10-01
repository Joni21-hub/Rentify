<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Vendor Center — Rentify')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
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
        html { scroll-behavior: smooth; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #F8FAFC; }
        .gradient-text {
            background: linear-gradient(135deg, #0369a1 0%, #38BDF8 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .gradient-bg {
            background: linear-gradient(135deg, #0369a1 0%, #0ea5e9 50%, #38BDF8 100%);
        }
        /* Mobile Drawer Transition */
        #mobileDrawer {
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
    </style>
</head>
<body class="font-sans text-slate-800 antialiased overflow-x-hidden min-h-screen flex flex-col">

    <!-- ========================================== -->
    <!-- 1. MOBILE TOP NAVBAR (< lg) -->
    <!-- ========================================== -->
    <header class="lg:hidden sticky top-0 z-40 bg-navydark text-white px-4 py-3 flex items-center justify-between shadow-md">
        <div class="flex items-center gap-3">
            <button type="button" onclick="toggleDrawer()" class="w-10 h-10 rounded-xl bg-white/10 hover:bg-white/20 flex items-center justify-center text-white transition cursor-pointer">
                <i class="fa-solid fa-bars-staggered text-base"></i>
            </button>
            <a href="{{ route('vendor.dashboard') }}" class="flex items-center gap-2">
                <img src="https://res.cloudinary.com/fnf8f1pm/image/upload/v1784199454/gambar_logo_trerjo.png" alt="Logo" class="h-8 w-auto object-contain">
                <span class="text-lg font-black tracking-tight text-white">Rentify<span class="text-sky-400">.</span></span>
            </a>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('vendor.barang.create') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-gradient-to-r from-sky-400 to-sky-600 text-white rounded-xl text-xs font-black shadow-sm">
                <i class="fa-solid fa-plus text-xs"></i> <span>Upload</span>
            </a>
            <a href="{{ route('role.switch', 'customer') }}" class="w-9 h-9 rounded-xl bg-white/10 flex items-center justify-center text-sky-300" title="Ke Mode Customer">
                <i class="fa-solid fa-cart-shopping text-sm"></i>
            </a>
        </div>
    </header>

    <!-- ========================================== -->
    <!-- 2. MOBILE DRAWER BACKDROP & SIDEBAR (< lg) -->
    <!-- ========================================== -->
    <div id="drawerBackdrop" onclick="toggleDrawer()" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 hidden transition-opacity duration-300"></div>

    <aside id="mobileDrawer" class="fixed inset-y-0 left-0 w-72 bg-navydark text-white z-50 transform -translate-x-full lg:translate-x-0 transition-transform duration-300 shadow-2xl flex flex-col">
        <!-- Drawer Header -->
        <div class="h-20 flex items-center justify-between px-6 border-b border-white/10">
            <a href="{{ url('/') }}" class="flex items-center gap-3">
                <img src="https://res.cloudinary.com/fnf8f1pm/image/upload/v1784199454/gambar_logo_trerjo.png" alt="Logo Rentify" class="h-9 w-auto object-contain">
                <span class="text-2xl font-black text-white tracking-tight">Rentify<span class="text-sky-500">.</span></span>
            </a>
            <button type="button" onclick="toggleDrawer()" class="lg:hidden text-slate-400 hover:text-white p-2">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <!-- Toko Info Widget -->
        <div class="px-5 pt-5 pb-3">
            <div class="p-3.5 rounded-2xl bg-white/5 border border-white/10 flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-sky-500 to-blue-600 text-white flex items-center justify-center font-black text-base shadow-sm">
                    <i class="fa-solid fa-store"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-black text-white truncate">{{ Auth::user()->vendor_name ?? Auth::user()->name }}</p>
                    @php $status = Auth::user()->vendor_status ?? 'pending'; @endphp
                    <span class="inline-flex items-center gap-1 text-[10px] font-bold {{ $status === 'approved' ? 'text-emerald-400' : 'text-amber-400' }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ $status === 'approved' ? 'bg-emerald-400' : 'bg-amber-400 animate-pulse' }}"></span>
                        {{ $status === 'approved' ? 'Toko Aktif' : 'Menunggu Review' }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Tombol Tambah Barang Menonjol di Sidebar -->
        <div class="px-5 py-2">
            <a href="{{ route('vendor.barang.create') }}" class="w-full flex items-center justify-center gap-2 py-3 px-4 bg-gradient-to-r from-sky-400 to-blue-600 hover:from-sky-500 hover:to-blue-700 text-white font-extrabold text-xs rounded-xl shadow-md transition transform active:scale-95">
                <i class="fa-solid fa-cloud-arrow-up text-sm"></i>
                <span>UPLOAD PRODUK BARU</span>
            </a>
        </div>

        <!-- Menu Navigasi -->
        <div class="flex-1 overflow-y-auto py-3 px-5 space-y-1.5">
            <p class="px-3 text-[10px] font-extrabold text-slate-500 uppercase tracking-widest mb-2">Pusat Bisnis</p>
            
            <a href="{{ route('vendor.dashboard') }}" class="flex items-center gap-3.5 px-4 py-3 rounded-xl font-bold text-xs transition-all {{ request()->routeIs('vendor.dashboard') ? 'bg-sky-500/20 text-sky-400 border border-sky-500/30' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                <i class="fa-solid fa-chart-pie w-5 text-center text-sm"></i> <span>Dashboard</span>
            </a>

            <a href="{{ route('vendor.barang.index') }}" class="flex items-center gap-3.5 px-4 py-3 rounded-xl font-bold text-xs transition-all {{ request()->routeIs('vendor.barang.*') ? 'bg-sky-500/20 text-sky-400 border border-sky-500/30' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                <i class="fa-solid fa-box-open w-5 text-center text-sm"></i> <span>Kelola Produk</span>
            </a>

            <a href="{{ route('vendor.pesanan.index') }}" class="flex items-center gap-3.5 px-4 py-3 rounded-xl font-bold text-xs transition-all {{ request()->routeIs('vendor.pesanan.*') ? 'bg-sky-500/20 text-sky-400 border border-sky-500/30' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                <i class="fa-solid fa-clipboard-list w-5 text-center text-sm"></i> <span>Pesanan Masuk</span>
            </a>

            <a href="{{ route('vendor.saldo.index') }}" class="flex items-center gap-3.5 px-4 py-3 rounded-xl font-bold text-xs transition-all {{ request()->routeIs('vendor.saldo.*') ? 'bg-sky-500/20 text-sky-400 border border-sky-500/30' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                <i class="fa-solid fa-wallet w-5 text-center text-sm"></i> <span>Saldo & Tarik Dana</span>
            </a>

            <a href="{{ route('vendor.voucher.index') }}" class="flex items-center gap-3.5 px-4 py-3 rounded-xl font-bold text-xs transition-all {{ request()->routeIs('vendor.voucher.*') ? 'bg-sky-500/20 text-sky-400 border border-sky-500/30' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                <i class="fa-solid fa-ticket w-5 text-center text-sm"></i> <span>Voucher Diskon Toko</span>
            </a>

            <div class="my-4 border-t border-white/5"></div>
            <p class="px-3 text-[10px] font-extrabold text-slate-500 uppercase tracking-widest mb-2">Toko & Pengaturan</p>

            <a href="{{ route('vendor.pengaturan.index') }}" class="flex items-center gap-3.5 px-4 py-3 rounded-xl font-bold text-xs transition-all {{ request()->routeIs('vendor.pengaturan.*') ? 'bg-sky-500/20 text-sky-400 border border-sky-500/30' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                <i class="fa-solid fa-store-gear w-5 text-center text-sm"></i> <span>Pengaturan Toko</span>
            </a>
        </div>

        <!-- Footer Sidebar (Beralih Mode & Logout) -->
        <div class="p-4 border-t border-white/10 bg-navydark/50 space-y-2">
            <a href="{{ route('role.switch', 'customer') }}" class="w-full flex items-center justify-center gap-2 px-3.5 py-2.5 text-xs font-bold text-sky-300 bg-sky-500/10 hover:bg-sky-500/20 border border-sky-400/20 rounded-xl transition-all">
                <i class="fa-solid fa-cart-shopping"></i> Beralih ke Mode Customer
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full flex items-center justify-center gap-2 px-3.5 py-2 text-xs font-bold text-rose-400 hover:bg-rose-500/10 rounded-xl transition-colors cursor-pointer">
                    <i class="fa-solid fa-power-off text-xs"></i> Keluar
                </button>
            </form>
        </div>
    </aside>

    <!-- ========================================== -->
    <!-- 3. MAIN CONTENT AREA -->
    <!-- ========================================== -->
    <main class="flex-1 lg:ml-72 pb-24 lg:pb-12 min-h-screen">
        @yield('content')
    </main>

    <!-- ========================================== -->
    <!-- 4. MOBILE BOTTOM APP BAR (< lg) -->
    <!-- ========================================== -->
    <nav class="lg:hidden fixed bottom-0 left-0 right-0 z-40 bg-white/95 backdrop-blur-md border-t border-slate-200 px-3 py-1.5 shadow-lg flex items-center justify-around">
        <!-- Dashboard -->
        <a href="{{ route('vendor.dashboard') }}" class="flex flex-col items-center py-1 px-2 {{ request()->routeIs('vendor.dashboard') ? 'text-sky-600' : 'text-slate-400 hover:text-slate-600' }}">
            <i class="fa-solid fa-chart-pie text-lg"></i>
            <span class="text-[9px] font-bold mt-0.5">Beranda</span>
        </a>

        <!-- Produk -->
        <a href="{{ route('vendor.barang.index') }}" class="flex flex-col items-center py-1 px-2 {{ request()->routeIs('vendor.barang.index') ? 'text-sky-600' : 'text-slate-400 hover:text-slate-600' }}">
            <i class="fa-solid fa-box-open text-lg"></i>
            <span class="text-[9px] font-bold mt-0.5">Produk</span>
        </a>

        <!-- Center Upload Button (Floating Action) -->
        <a href="{{ route('vendor.barang.create') }}" class="flex flex-col items-center -mt-6 group">
            <div class="w-13 h-13 rounded-2xl bg-gradient-to-tr from-sky-500 to-blue-600 text-white flex items-center justify-center text-xl shadow-lg shadow-sky-500/30 group-active:scale-95 transition-all border-4 border-white">
                <i class="fa-solid fa-plus font-black"></i>
            </div>
            <span class="text-[9px] font-black text-sky-600 mt-1">Upload</span>
        </a>

        <!-- Pesanan -->
        <a href="{{ route('vendor.pesanan.index') }}" class="flex flex-col items-center py-1 px-2 {{ request()->routeIs('vendor.pesanan.*') ? 'text-sky-600' : 'text-slate-400 hover:text-slate-600' }}">
            <i class="fa-solid fa-clipboard-list text-lg"></i>
            <span class="text-[9px] font-bold mt-0.5">Pesanan</span>
        </a>

        <!-- Saldo -->
        <a href="{{ route('vendor.saldo.index') }}" class="flex flex-col items-center py-1 px-2 {{ request()->routeIs('vendor.saldo.*') ? 'text-sky-600' : 'text-slate-400 hover:text-slate-600' }}">
            <i class="fa-solid fa-wallet text-lg"></i>
            <span class="text-[9px] font-bold mt-0.5">Saldo</span>
        </a>
    </nav>

    <!-- Script Drawer Toggle -->
    <script>
        function toggleDrawer() {
            const drawer = document.getElementById('mobileDrawer');
            const backdrop = document.getElementById('drawerBackdrop');
            if (drawer.classList.contains('-translate-x-full')) {
                drawer.classList.remove('-translate-x-full');
                backdrop.classList.remove('hidden');
            } else {
                drawer.classList.add('-translate-x-full');
                backdrop.classList.add('hidden');
            }
        }
    </script>

    @stack('scripts')
</body>
</html>