<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Profil - Rentify</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="{{ asset('rentify-theme.css') }}">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            background: radial-gradient(circle at center, #fff9ef 0%, #bad6eb 100%) fixed !important;
        }
        .bg-flowing {
            background: radial-gradient(circle at center, #fff9ef 0%, #bad6eb 100%) fixed !important;
        }
    </style>
</head>

<body class="min-h-screen text-slate-800 pb-24 md:pb-8 bg-flowing overflow-x-hidden w-full max-w-full">

    <!-- ===== DESKTOP TOP NAVBAR (HIDDEN ON MOBILE) ===== -->
    <header class="rentify-navbar hidden md:flex sticky top-0 z-50">
        <div class="max-w-6xl mx-auto w-full px-6 py-3 flex items-center justify-between gap-4">
            <a href="{{ route('customer.home') }}" class="flex items-center gap-2 flex-shrink-0">
                <img src="https://res.cloudinary.com/fnf8f1pm/image/upload/v1784199454/gambar_logo_trerjo.png" class="h-8 object-contain" alt="Logo">
                <span class="text-xl font-black text-sky-500 tracking-tighter">Rentify</span>
            </a>
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

    <!-- ===== MOBILE: Profile View ===== -->
    <div class="md:hidden max-w-sm mx-auto px-4 pt-6 relative z-10 w-full">
        <!-- KARTU PROFIL HEADER -->
        <div class="rentify-card p-6 text-center relative mb-4 shadow-lg rounded-3xl">
            <a href="{{ route('customer.settings') }}" class="absolute top-4 right-4 w-9 h-9 bg-white/70 hover:bg-white rounded-xl flex items-center justify-center text-slate-600 hover:text-sky-600 transition shadow-sm" title="Pengaturan">
                <i class="fa-solid fa-gear text-sm"></i>
            </a>
            
            <div class="relative inline-block mb-3">
                <div class="w-20 h-20 rounded-full bg-sky-100 border-4 border-white shadow-md overflow-hidden flex items-center justify-center text-sky-600 text-3xl font-black mx-auto">
                    @if(Auth::user()->foto_profil)
                        <img src="{{ str_starts_with(Auth::user()->foto_profil, 'http') ? Auth::user()->foto_profil : Storage::url(Auth::user()->foto_profil) }}" alt="Foto" class="w-full h-full object-cover">
                    @else
                        {{ substr(Auth::user()->name ?? 'C', 0, 1) }}
                    @endif
                </div>
            </div>

            <h2 class="text-xl font-black text-slate-800 tracking-tight">{{ Auth::user()->name ?? 'Customer' }}</h2>
            <div class="inline-flex items-center gap-1.5 mt-1.5 bg-white/60 px-3 py-1 rounded-full border border-slate-200/60 text-xs font-semibold text-slate-600">
                <i class="fa-solid fa-envelope text-[11px] text-sky-500"></i>
                <span class="truncate max-w-[200px]">{{ Auth::user()->display_contact }}</span>
            </div>
            
            @if(Auth::user()->whatsapp_verified_at)
            <div class="mt-2 inline-flex items-center gap-1.5 text-[10px] font-bold text-emerald-700 bg-emerald-100/80 px-2.5 py-0.5 rounded-full border border-emerald-300">
                <i class="fa-solid fa-shield-check text-emerald-600"></i> WA Terverifikasi
            </div>
            @endif
        </div>

        <!-- MENU-MENU MOBILE -->
        <div class="space-y-3">
            <!-- PESANAN SAYA -->
            <div class="rentify-card p-4 rounded-2xl">
                <div class="flex justify-between items-center mb-3 pb-2.5 border-b border-slate-200/60">
                    <h3 class="font-black text-slate-800 text-xs uppercase tracking-wider">Pesanan Saya</h3>
                    <a href="{{ route('customer.pesanan') }}" class="text-[11px] font-bold text-sky-600 hover:text-sky-700 transition flex items-center gap-1">
                        Riwayat <i class="fa-solid fa-chevron-right text-[8px]"></i>
                    </a>
                </div>
                <div class="grid grid-cols-3 gap-2 text-center">
                    <a href="{{ route('customer.pesanan') }}" class="flex flex-col items-center p-2 rounded-xl hover:bg-white/50 transition group">
                        <div class="w-10 h-10 rounded-xl bg-sky-50 flex items-center justify-center text-sky-600 group-hover:scale-105 transition mb-1 relative shadow-sm">
                            <i class="fa-solid fa-clock-rotate-left text-base"></i>
                            @if(isset($countMenunggu) && $countMenunggu > 0)
                                <span class="absolute -top-1 -right-1 bg-rose-500 text-white text-[9px] font-black w-4 h-4 rounded-full flex items-center justify-center shadow-sm">{{ $countMenunggu }}</span>
                            @endif
                        </div>
                        <span class="text-[10px] font-bold text-slate-700 leading-tight">Konfirmasi</span>
                    </a>
                    <a href="{{ route('customer.pesanan') }}" class="flex flex-col items-center p-2 rounded-xl hover:bg-white/50 transition group">
                        <div class="w-10 h-10 rounded-xl bg-sky-50 flex items-center justify-center text-sky-600 group-hover:scale-105 transition mb-1 relative shadow-sm">
                            <i class="fa-solid fa-truck-fast text-base"></i>
                            @if(isset($countDiproses) && $countDiproses > 0)
                                <span class="absolute -top-1 -right-1 bg-rose-500 text-white text-[9px] font-black w-4 h-4 rounded-full flex items-center justify-center shadow-sm">{{ $countDiproses }}</span>
                            @endif
                        </div>
                        <span class="text-[10px] font-bold text-slate-700 leading-tight">Berjalan</span>
                    </a>
                    <a href="{{ route('customer.pesanan') }}" class="flex flex-col items-center p-2 rounded-xl hover:bg-white/50 transition group">
                        <div class="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600 group-hover:scale-105 transition mb-1 shadow-sm">
                            <i class="fa-solid fa-star text-base"></i>
                        </div>
                        <span class="text-[10px] font-bold text-slate-700 leading-tight">Ulasan</span>
                    </a>
                </div>
            </div>

            <!-- GANTI TITIK LOKASI -->
            <a href="{{ url('/customer/lokasi') }}" class="rentify-card p-3.5 rounded-2xl flex items-center justify-between group hover:translate-y-[-2px] transition">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-sky-100 text-sky-600 rounded-xl flex items-center justify-center shadow-sm">
                        <i class="fa-solid fa-location-dot text-base"></i>
                    </div>
                    <div>
                        <span class="block font-black text-slate-800 text-xs">Ganti Titik Lokasi</span>
                        <span class="block text-[10px] font-medium text-slate-500">Atur GPS untuk cari barang terdekat</span>
                    </div>
                </div>
                <i class="fa-solid fa-chevron-right text-slate-400 text-xs group-hover:text-sky-600 transition"></i>
            </a>

            <!-- PUSAT BANTUAN -->
            @php $linkWaAdmin = "https://wa.me/6283183494835?text=" . urlencode("Halo admin, saya mengalami masalah di Rentify, mohon bantuannya."); @endphp
            <a href="{{ $linkWaAdmin }}" target="_blank" class="rentify-card p-3.5 rounded-2xl flex items-center justify-between group hover:translate-y-[-2px] transition">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-emerald-100 text-emerald-600 rounded-xl flex items-center justify-center shadow-sm">
                        <i class="fa-brands fa-whatsapp text-lg"></i>
                    </div>
                    <div>
                        <span class="block font-black text-emerald-800 text-xs">Pusat Bantuan WhatsApp</span>
                        <span class="block text-[10px] font-semibold text-emerald-600/80">Hubungi Admin Rentify (24/7)</span>
                    </div>
                </div>
                <i class="fa-solid fa-arrow-right text-emerald-500 text-xs"></i>
            <!-- MODE VENDOR / BUKA TOKO (MOBILE) -->
            @if(Auth::user()->isVendor())
            <a href="{{ route('role.switch', 'vendor') }}" class="rentify-card p-3.5 rounded-2xl flex items-center justify-between group hover:translate-y-[-2px] transition bg-gradient-to-r from-sky-50 to-emerald-50 border border-emerald-200">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-emerald-500 text-white rounded-xl flex items-center justify-center shadow-sm">
                        <i class="fa-solid fa-store text-base"></i>
                    </div>
                    <div>
                        <span class="block font-black text-slate-800 text-xs">Beralih ke Toko Saya</span>
                        <span class="block text-[10px] font-semibold text-emerald-700 truncate max-w-[180px]">{{ Auth::user()->vendor_name }} &bull; Masuk Mode Toko</span>
                    </div>
                </div>
                <i class="fa-solid fa-arrow-right text-emerald-600 text-xs"></i>
            </a>
            @else
            <a href="{{ route('vendor.register') }}" class="rentify-card p-3.5 rounded-2xl flex items-center justify-between group hover:translate-y-[-2px] transition bg-gradient-to-r from-white to-sky-50 border border-sky-200">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-sky-500 text-white rounded-xl flex items-center justify-center shadow-sm">
                        <i class="fa-solid fa-shop text-base"></i>
                    </div>
                    <div>
                        <span class="block font-black text-slate-800 text-xs">Buka Toko Rental</span>
                        <span class="block text-[10px] font-semibold text-sky-700">Daftar sebagai Mitra & sewakan barang</span>
                    </div>
                </div>
                <i class="fa-solid fa-arrow-right text-sky-600 text-xs"></i>
            </a>
            @endif
        </div>

        <!-- BOTTOM NAV MOBILE -->
        <div class="md:hidden fixed bottom-4 left-0 w-full z-50 flex justify-center pointer-events-none px-4">
            <nav class="rentify-card backdrop-blur-xl border border-white/80 pointer-events-auto px-6 py-2.5 w-full max-w-xs flex justify-around items-center shadow-lg rounded-2xl">
                <a href="{{ route('customer.home') }}" class="flex flex-col items-center text-slate-400 hover:text-sky-600 transition-colors">
                    <i class="fa-solid fa-house text-[18px] mb-0.5"></i><span class="text-[9px] font-bold">Beranda</span>
                </a>
                <a href="{{ route('customer.wishlist') }}" class="flex flex-col items-center text-slate-400 hover:text-sky-600 transition-colors">
                    <i class="fa-solid fa-heart text-[18px] mb-0.5"></i><span class="text-[9px] font-bold">Favorit</span>
                </a>
                <a href="{{ route('customer.dashboard') }}" class="flex flex-col items-center text-sky-600">
                    <i class="fa-solid fa-user text-[18px] mb-0.5"></i><span class="text-[9px] font-black">Akun</span>
                </a>
            </nav>
        </div>
    </div>

    <!-- ===== DESKTOP: Sidebar + Content Layout ===== -->
    <div class="hidden md:block max-w-5xl mx-auto px-6 py-8">
        <div class="flex gap-6">
            <!-- SIDEBAR KIRI (PROFIL) -->
            <aside class="w-72 flex-shrink-0">
                <!-- Kartu Profil -->
                <div class="rentify-card p-6 text-center mb-4 rounded-3xl">
                    <div class="w-20 h-20 rounded-full overflow-hidden mx-auto mb-3 border-4 border-white shadow-md flex items-center justify-center bg-sky-100 text-sky-600 text-3xl font-black">
                        @if(Auth::user()->foto_profil)
                            <img src="{{ str_starts_with(Auth::user()->foto_profil, 'http') ? Auth::user()->foto_profil : Storage::url(Auth::user()->foto_profil) }}" alt="Foto" class="w-full h-full object-cover">
                        @else
                            {{ substr(Auth::user()->name ?? 'C', 0, 1) }}
                        @endif
                    </div>
                    <h2 class="font-black text-lg text-slate-800">{{ Auth::user()->name ?? 'Customer' }}</h2>
                    <p class="text-slate-500 text-xs mt-0.5 break-all">{{ Auth::user()->display_contact }}</p>
                    @if(Auth::user()->whatsapp_verified_at)
                    <div class="mt-2.5 inline-flex items-center gap-1 text-[10px] font-bold text-emerald-700 bg-emerald-100 px-3 py-1 rounded-full border border-emerald-300">
                        <i class="fa-solid fa-shield-check"></i> WA Terverifikasi
                    </div>
                    @endif
                </div>

                <!-- Menu Navigasi Sidebar -->
                <div class="rentify-card overflow-hidden rounded-2xl">
                    <a href="{{ route('customer.dashboard') }}" class="flex items-center gap-3 px-5 py-3.5 bg-sky-50 border-l-4 border-sky-500 text-sky-700 font-bold text-sm">
                        <i class="fa-solid fa-user w-5 text-center"></i> Profil
                    </a>
                    <a href="{{ route('customer.pesanan') }}" class="flex items-center gap-3 px-5 py-3.5 hover:bg-sky-50 text-slate-600 hover:text-sky-600 font-semibold text-sm transition border-b border-slate-100">
                        <i class="fa-solid fa-box-open w-5 text-center"></i> Pesanan
                    </a>
                    <a href="{{ route('customer.wishlist') }}" class="flex items-center gap-3 px-5 py-3.5 hover:bg-sky-50 text-slate-600 hover:text-sky-600 font-semibold text-sm transition border-b border-slate-100">
                        <i class="fa-solid fa-heart w-5 text-center"></i> Favorit
                    </a>
                    <a href="{{ route('customer.lokasi') }}" class="flex items-center gap-3 px-5 py-3.5 hover:bg-sky-50 text-slate-600 hover:text-sky-600 font-semibold text-sm transition border-b border-slate-100">
                        <i class="fa-solid fa-location-dot w-5 text-center"></i> Titik Lokasi
                    </a>
                    @if(Auth::user()->isVendor())
                    <a href="{{ route('role.switch', 'vendor') }}" class="flex items-center gap-3 px-5 py-3.5 bg-gradient-to-r from-emerald-50 to-teal-50 hover:from-emerald-100 hover:to-teal-100 text-emerald-800 font-extrabold text-sm transition border-b border-emerald-200">
                        <i class="fa-solid fa-store w-5 text-center text-emerald-600"></i> Mode Toko Vendor
                    </a>
                    @else
                    <a href="{{ route('vendor.register') }}" class="flex items-center gap-3 px-5 py-3.5 bg-gradient-to-r from-sky-50 to-blue-50 hover:from-sky-100 hover:to-blue-100 text-sky-800 font-extrabold text-sm transition border-b border-sky-200">
                        <i class="fa-solid fa-shop w-5 text-center text-sky-600"></i> Buka Toko Rental
                    </a>
                    @endif
                    <a href="{{ route('customer.settings') }}" class="flex items-center gap-3 px-5 py-3.5 hover:bg-sky-50 text-slate-600 hover:text-sky-600 font-semibold text-sm transition border-b border-slate-100">
                        <i class="fa-solid fa-gear w-5 text-center"></i> Pengaturan Akun
                    </a>
                    <form action="/logout" method="POST">
                        @csrf
                        <button type="submit" class="w-full flex items-center gap-3 px-5 py-3.5 hover:bg-rose-50 text-rose-600 hover:text-rose-700 font-bold text-sm transition text-left">
                            <i class="fa-solid fa-power-off w-5 text-center"></i> Keluar Akun
                        </button>
                    </form>
                </div>
            </aside>

            <!-- KONTEN KANAN -->
            <div class="flex-1 space-y-5">
                <!-- Ringkasan Pesanan -->
                <div class="rentify-card p-6 rounded-3xl">
                    <div class="flex justify-between items-center mb-5">
                        <h3 class="font-black text-slate-800 text-base">Pesanan Saya</h3>
                        <a href="{{ route('customer.pesanan', ['status' => 'semua']) }}" class="text-xs font-bold text-sky-600 hover:underline">Lihat Semua <i class="fa-solid fa-chevron-right text-[9px]"></i></a>
                    </div>
                    <div class="grid grid-cols-3 gap-4">
                        <a href="{{ route('customer.pesanan', ['status' => 'menunggu']) }}" class="flex flex-col items-center gap-2 p-4 rounded-2xl bg-sky-50 hover:bg-sky-100 transition group shadow-sm active:scale-95">
                            <div class="relative">
                                <i class="fa-solid fa-clock-rotate-left text-2xl text-sky-500"></i>
                                @if(isset($countMenunggu) && $countMenunggu > 0)
                                    <span class="absolute -top-2 -right-2 bg-rose-500 text-white text-[9px] font-bold w-4 h-4 rounded-full flex items-center justify-center">{{ $countMenunggu }}</span>
                                @endif
                            </div>
                            <span class="text-xs font-bold text-slate-600 text-center leading-tight">Menunggu Konfirmasi</span>
                        </a>
                        <a href="{{ route('customer.pesanan', ['status' => 'berjalan']) }}" class="flex flex-col items-center gap-2 p-4 rounded-2xl bg-sky-50 hover:bg-sky-100 transition group shadow-sm active:scale-95">
                            <div class="relative">
                                <i class="fa-solid fa-truck-fast text-2xl text-sky-600"></i>
                                @if(isset($countDiproses) && $countDiproses > 0)
                                    <span class="absolute -top-2 -right-2 bg-rose-500 text-white text-[9px] font-bold w-4 h-4 rounded-full flex items-center justify-center">{{ $countDiproses }}</span>
                                @endif
                            </div>
                            <span class="text-xs font-bold text-slate-600 text-center leading-tight">Sedang Berjalan</span>
                        </a>
                        <a href="{{ route('customer.pesanan', ['status' => 'selesai']) }}" class="flex flex-col items-center gap-2 p-4 rounded-2xl bg-emerald-50 hover:bg-emerald-100 transition group shadow-sm active:scale-95">
                            <i class="fa-solid fa-star text-2xl text-emerald-500"></i>
                            <span class="text-xs font-bold text-slate-600 text-center leading-tight">Beri Ulasan</span>
                        </a>
                    </div>

                </div>

                <!-- Menu Cepat -->
                <div class="rentify-card p-6 rounded-3xl">
                    <h3 class="font-black text-slate-800 text-base mb-4">Aksi Cepat</h3>
                    <div class="grid grid-cols-2 gap-3">
                        <a href="{{ url('/customer/lokasi') }}" class="flex items-center gap-3 p-4 rounded-2xl bg-white/70 hover:bg-white border border-slate-200/60 hover:border-sky-300 transition shadow-sm">
                            <div class="w-10 h-10 rounded-xl bg-sky-100 text-sky-600 flex items-center justify-center">
                                <i class="fa-solid fa-location-dot"></i>
                            </div>
                            <div>
                                <span class="block font-bold text-sm text-slate-800">Ganti Lokasi</span>
                                <span class="block text-[11px] text-slate-500">Atur titik GPS Anda</span>
                            </div>
                        </a>
                        @php $linkWaAdmin = "https://wa.me/6281262364197?text=" . urlencode("Halo admin, saya mengalami masalah di Rentify, mohon bantuannya."); @endphp
                        <a href="{{ $linkWaAdmin }}" target="_blank" class="flex items-center gap-3 p-4 rounded-2xl bg-white/70 hover:bg-white border border-slate-200/60 hover:border-emerald-300 transition shadow-sm">
                            <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center">
                                <i class="fa-brands fa-whatsapp text-xl"></i>
                            </div>
                            <div>
                                <span class="block font-bold text-sm text-slate-800">Pusat Bantuan</span>
                                <span class="block text-[11px] text-slate-500">Hubungi Admin 24/7</span>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>