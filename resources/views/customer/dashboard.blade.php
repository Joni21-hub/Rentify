<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Profil — Rentify</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="{{ asset('rentify-theme.css') }}">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            background: radial-gradient(circle at center, #fff9ef 0%, #bad6eb 100%) fixed !important;
        }
        .rentify-card {
            background: rgba(255, 255, 255, 0.88);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.85);
            box-shadow: 0 10px 25px -5px rgba(14, 165, 233, 0.08), 0 8px 10px -6px rgba(0, 0, 0, 0.03);
        }
        .avatar-box {
            width: 72px;
            height: 72px;
            min-width: 72px;
            min-height: 72px;
            max-width: 72px;
            max-height: 72px;
        }
        @media (min-width: 640px) {
            .avatar-box {
                width: 80px;
                height: 80px;
                min-width: 80px;
                min-height: 80px;
                max-width: 80px;
                max-height: 80px;
            }
        }
        .shopee-badge {
            position: absolute;
            top: -2px;
            right: 0px;
            background: #0ea5e9;
            color: white;
            font-size: 9px;
            font-weight: 800;
            min-width: 16px;
            height: 16px;
            padding: 0 4px;
            border-radius: 9999px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 1px 3px rgba(14, 165, 233, 0.3);
            border: 1.5px solid white;
            pointer-events: none;
            line-height: 1;
        }
        @keyframes rentifyClockTick {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .rentify-clock-hand {
            transform-origin: 12px 12px;
            animation: rentifyClockTick 5s linear infinite;
        }
        .group:hover .rentify-clock-hand {
            animation-duration: 1.5s;
        }
        .gold-pill {
            background: linear-gradient(135deg, #FFFBEB 0%, #FEF3C7 100%);
            border: 1px solid #FDE68A;
        }
    </style>
</head>

<body class="min-h-screen text-slate-800 pb-28 md:pb-12 bg-flowing overflow-x-hidden w-full max-w-full">

    <!-- ===== DESKTOP TOP NAVBAR (HIDDEN ON MOBILE) ===== -->
    <header class="rentify-navbar hidden md:flex sticky top-0 z-50 bg-white/70 backdrop-blur-md border-b border-white/60">
        <div class="max-w-5xl mx-auto w-full px-6 py-3 flex items-center justify-between gap-4">
            <a href="{{ route('customer.home') }}" class="flex items-center gap-2 flex-shrink-0">
                <img src="https://res.cloudinary.com/fnf8f1pm/image/upload/v1784199454/gambar_logo_trerjo.png" class="h-8 object-contain" alt="Logo">
                <span class="text-xl font-black text-sky-500 tracking-tighter">Rentify</span>
            </a>
            <nav class="flex items-center gap-2">
                <a href="{{ route('customer.home') }}" class="flex flex-col items-center px-4 py-2 text-slate-400 hover:text-sky-500 rounded-xl transition">
                    <i class="fa-solid fa-house text-lg"></i>
                    <span class="text-[10px] font-bold mt-0.5">Beranda</span>
                </a>
                <a href="{{ route('customer.moments') }}" class="flex flex-col items-center px-4 py-2 text-slate-400 hover:text-sky-500 rounded-xl transition">
                    <i class="fa-solid fa-camera text-lg"></i>
                    <span class="text-[10px] font-bold mt-0.5">Momen</span>
                </a>
                <a href="{{ route('customer.keranjang') }}" class="flex flex-col items-center px-4 py-2 text-slate-400 hover:text-sky-500 rounded-xl transition group">
                    <svg class="w-5 h-5 stroke-slate-400 group-hover:stroke-sky-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="8" cy="21" r="1"/>
                        <circle cx="19" cy="21" r="1"/>
                        <path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/>
                    </svg>
                    <span class="text-[10px] font-bold mt-0.5">Keranjang</span>
                </a>
                <a href="{{ route('customer.dashboard') }}" class="flex flex-col items-center px-4 py-2 text-sky-600 rounded-xl font-bold">
                    <i class="fa-solid fa-user text-lg"></i>
                    <span class="text-[10px] font-extrabold mt-0.5">Akun</span>
                </a>
            </nav>
        </div>
    </header>

    <!-- ===== MAIN CONTAINER (RESPONSIVE: MOBILE FIRST & DESKTOP ENHANCED) ===== -->
    <main class="max-w-md md:max-w-5xl mx-auto px-4 pt-3 sm:pt-6 relative z-10 w-full space-y-4">

        <!-- GRID CONTAINER: 1 COLUMN ON MOBILE, 2 COLUMNS ON DESKTOP -->
        <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-start">

            <!-- LEFT COLUMN (DESKTOP: PROFILE INFO & FAST ACTIONS) -->
            <div class="md:col-span-5 space-y-4">

                <!-- ============================================================== -->
                <!-- 1. PROFILE HEADER CARD (RENTIFY THEME STYLED) -->
                <!-- ============================================================== -->
                <div class="rentify-card p-4 sm:p-5 rounded-3xl space-y-4">
                    <!-- Header Bar: Avatar, Info Kontak & Tombol Garis 3 Menu Sejajar Nama -->
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-3.5 flex-1 min-w-0">
                            <!-- Avatar Ringed with Rentify Sky Blue -->
                            <div class="relative shrink-0 avatar-box">
                                <div class="w-full h-full rounded-full p-1 bg-gradient-to-tr from-sky-400 to-sky-600 shadow-md flex items-center justify-center">
                                    <div class="w-full h-full rounded-full bg-white overflow-hidden flex items-center justify-center text-sky-600 font-black text-2xl border border-white shadow-inner">
                                        @if($user->foto_profil)
                                            <img src="{{ str_starts_with((string)$user->foto_profil, 'http') ? $user->foto_profil : Storage::url((string)$user->foto_profil) }}" 
                                                 alt="Foto Profil" 
                                                 class="w-full h-full object-cover rounded-full" 
                                                 style="width: 100%; height: 100%; object-fit: cover; aspect-ratio: 1/1;">
                                        @else
                                            {{ strtoupper(substr($user->name ?? 'C', 0, 1)) }}
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Info Nama & Kontak -->
                            <div class="space-y-1 flex-1 min-w-0">
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <h2 class="text-base sm:text-lg font-black text-slate-900 tracking-tight truncate">{{ $user->name ?? 'Customer' }}</h2>
                                </div>
                                
                                <div class="text-[11px] text-slate-500 flex items-center gap-1.5 flex-wrap">
                                    @if(method_exists($user, 'hasRealEmail') && $user->hasRealEmail())
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-sky-50 text-sky-700 font-medium border border-sky-100/60 max-w-full">
                                            <i class="fa-solid fa-envelope text-sky-500 text-[10px] shrink-0"></i>
                                            <span class="truncate">{{ $user->display_contact ?? $user->email }}</span>
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 font-medium border border-emerald-100/60 max-w-full">
                                            <i class="fa-brands fa-whatsapp text-emerald-600 text-[11px] shrink-0"></i>
                                            <span class="truncate">{{ $user->display_contact ?? $user->whatsapp }}</span>
                                        </span>
                                    @endif

                                    @if(!empty($user->instagram_username))
                                        <a href="https://instagram.com/{{ $user->instagram_username }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-pink-50 text-pink-700 font-medium border border-pink-100/60 hover:bg-pink-100 transition max-w-full">
                                            <i class="fa-brands fa-instagram text-pink-500 text-[11px] shrink-0"></i>
                                            <span class="truncate">&#64;{{ $user->instagram_username }}</span>
                                        </a>
                                    @endif
                                </div>

                                @if(!empty($user->bio))
                                    <p class="text-[11px] text-slate-600 line-clamp-2 leading-relaxed pt-0.5">
                                        {{ $user->bio }}
                                    </p>
                                @endif
                            </div>
                        </div>

                        <!-- Aksi Kanan: Tombol Edit Profil Ringkas & Tombol Menu Garis 3 Berdampingan -->
                        <div class="flex items-center gap-1.5 shrink-0 mt-0.5">
                            <a href="{{ route('customer.settings.profile') }}" 
                               aria-label="Edit Profil" 
                               title="Edit Profil"
                               class="w-8 h-8 sm:w-9 sm:h-9 rounded-xl sm:rounded-2xl bg-white/90 hover:bg-white text-slate-700 hover:text-sky-600 font-bold border border-slate-200/80 hover:border-sky-300 shadow-xs flex items-center justify-center transition active:scale-95 group shrink-0">
                                <i class="fa-solid fa-user-pen text-sky-500 group-hover:scale-110 transition-transform text-xs"></i>
                                <span class="sr-only">Edit Profil</span>
                            </a>

                            <button onclick="openSettingsDrawer()" 
                                    aria-label="Menu & Pengaturan" 
                                    title="Menu & Pengaturan"
                                    class="w-8 h-8 sm:w-9 sm:h-9 rounded-xl sm:rounded-2xl bg-white/90 hover:bg-white text-slate-700 hover:text-sky-600 shadow-xs border border-slate-200/80 hover:border-sky-300 flex items-center justify-center transition active:scale-95 shrink-0">
                                <i class="fa-solid fa-bars text-sm sm:text-base"></i>
                            </button>
                        </div>
                    </div>

                    <!-- TIGA METRIK STATISTIK UTAMA (MOMEN, LIKE, KOIN) -->
                    <div class="grid grid-cols-3 divide-x divide-sky-100 bg-sky-50/60 rounded-2xl p-2.5 text-center border border-sky-100/70 shadow-xs">
                        <!-- Metrik 1: Momen -->
                        <div class="px-2">
                            <span class="block text-base font-black text-slate-800">{{ ($momentsCount ?? 0) > 0 ? $momentsCount : '-' }}</span>
                            <span class="text-[10px] font-bold text-slate-500">Momen</span>
                        </div>

                        <!-- Metrik 2: Like (Total Like di Seluruh Momen) -->
                        <div class="px-2">
                            <span class="block text-base font-black text-slate-800">{{ ($likesCount ?? 0) > 0 ? number_format($likesCount, 0, ',', '.') : '-' }}</span>
                            <span class="text-[10px] font-bold text-slate-500">Like</span>
                        </div>

                        <!-- Metrik 3: Koin Rentify (Clickable to /wallet/coins) -->
                        <a href="{{ route('customer.wallet.coins') }}" class="px-2 group hover:opacity-85 transition cursor-pointer">
                            <div class="flex items-center justify-center gap-1">
                                <i class="fa-solid fa-coins text-amber-500 text-xs"></i>
                                <span class="text-base font-black text-amber-600 group-hover:underline">{{ number_format($coinBalance ?? 0, 0, ',', '.') }}</span>
                            </div>
                            <span class="text-[10px] font-bold text-amber-700 flex items-center justify-center gap-0.5">
                                Koin <i class="fa-solid fa-chevron-right text-[7px] text-amber-500"></i>
                            </span>
                        </a>
                    </div>
                </div>

                <!-- ============================================================== -->
                <!-- STATUS PESANAN ALA SHOPEE (LINE ICONS BIRU - TANPA HEADER TEKS) -->
                <!-- ============================================================== -->
                <div class="rentify-card p-3 sm:p-4 rounded-3xl shadow-xs border border-white/80">
                    <div class="grid grid-cols-4 gap-1 text-center">
                        <!-- 1. Belum Bayar (Dompet Terbuka) -->
                        <a href="{{ route('customer.pesanan', ['status' => 'belum_bayar']) }}" class="flex flex-col items-center py-1.5 px-1 rounded-2xl hover:bg-sky-50/70 transition group active:scale-95">
                            <div class="relative w-10 h-10 flex items-center justify-center mb-1 text-sky-500 group-hover:scale-110 group-hover:text-sky-600 transition-all">
                                <svg class="w-6 h-6 stroke-sky-500 group-hover:stroke-sky-600 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                    <!-- Lipatan dompet yang terbuka ke atas -->
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 9.75l2.67-4.45A2.25 2.25 0 018.35 4.125h7.3a2.25 2.25 0 011.93 1.175l2.67 4.45" />
                                    <!-- Kartu/uang menyembul dari bukaan dompet -->
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 7.5V5.25a1.5 1.5 0 011.5-1.5h6a1.5 1.5 0 011.5 1.5V7.5" />
                                    <!-- Badan utama dompet -->
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 10.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 10.5v7.5A2.25 2.25 0 0118.75 20.25H5.25A2.25 2.25 0 013 18V10.5z" />
                                    <!-- Kancing / saku dompet -->
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 13.5h4.5v3.75h-4.5a1.875 1.875 0 010-3.75z" />
                                    <circle cx="18.5" cy="15.375" r="0.75" fill="currentColor" />
                                </svg>
                                @if(isset($countBelumBayar) && $countBelumBayar > 0)
                                    <span class="absolute -top-1 -right-1 bg-gradient-to-r from-sky-500 to-sky-600 text-white text-[9px] font-black min-w-[17px] h-4 px-1 rounded-full flex items-center justify-center border-2 border-white shadow-xs leading-none">
                                        {{ $countBelumBayar > 99 ? '99+' : $countBelumBayar }}
                                    </span>
                                @endif
                            </div>
                            <span class="text-[11px] font-bold text-slate-700 group-hover:text-sky-600 transition-colors leading-tight">Belum Bayar</span>
                        </a>

                        <!-- 2. Konfirmasi -->
                        <a href="{{ route('customer.pesanan', ['status' => 'konfirmasi']) }}" class="flex flex-col items-center py-1.5 px-1 rounded-2xl hover:bg-sky-50/70 transition group active:scale-95">
                            <div class="relative w-10 h-10 flex items-center justify-center mb-1 text-sky-500 group-hover:scale-110 group-hover:text-sky-600 transition-all">
                                <svg class="w-6 h-6 stroke-sky-500 group-hover:stroke-sky-600 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                                </svg>
                                @if(isset($countKonfirmasi) && $countKonfirmasi > 0)
                                    <span class="absolute -top-1 -right-1 bg-gradient-to-r from-sky-500 to-sky-600 text-white text-[9px] font-black min-w-[17px] h-4 px-1 rounded-full flex items-center justify-center border-2 border-white shadow-xs leading-none">
                                        {{ $countKonfirmasi > 99 ? '99+' : $countKonfirmasi }}
                                    </span>
                                @endif
                            </div>
                            <span class="text-[11px] font-bold text-slate-700 group-hover:text-sky-600 transition-colors leading-tight">Konfirmasi</span>
                        </a>

                        <!-- 3. Berjalan (Jam Unik Berputar) -->
                        <a href="{{ route('customer.pesanan', ['status' => 'berjalan']) }}" class="flex flex-col items-center py-1.5 px-1 rounded-2xl hover:bg-sky-50/70 transition group active:scale-95">
                            <div class="relative w-10 h-10 flex items-center justify-center mb-1 text-sky-500 group-hover:scale-110 group-hover:text-sky-600 transition-all">
                                <svg class="w-6 h-6 stroke-sky-500 group-hover:stroke-sky-600 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                    <!-- Lingkaran dial jam -->
                                    <circle cx="12" cy="12" r="9" stroke-linecap="round" stroke-linejoin="round" />
                                    <!-- Titik penanda jam 12, 3, 6, 9 -->
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1.5M12 19.5V21M3 12h1.5M19.5 12H21" stroke-width="1.5" opacity="0.6" />
                                    <!-- Poros tengah jam -->
                                    <circle cx="12" cy="12" r="1.2" fill="currentColor" />
                                    <!-- Jarum jam berputar unik -->
                                    <g class="rentify-clock-hand">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 12V6.5" stroke-width="2" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 12l3.2 2" stroke-width="1.75" />
                                    </g>
                                </svg>
                                @if(isset($countBerjalan) && $countBerjalan > 0)
                                    <span class="absolute -top-1 -right-1 bg-gradient-to-r from-sky-500 to-sky-600 text-white text-[9px] font-black min-w-[17px] h-4 px-1 rounded-full flex items-center justify-center border-2 border-white shadow-xs leading-none">
                                        {{ $countBerjalan > 99 ? '99+' : $countBerjalan }}
                                    </span>
                                @endif
                            </div>
                            <span class="text-[11px] font-bold text-slate-700 group-hover:text-sky-600 transition-colors leading-tight">Berjalan</span>
                        </a>

                        <!-- 4. Beri Ulasan -->
                        <a href="{{ route('customer.pesanan', ['status' => 'ulasan']) }}" class="flex flex-col items-center py-1.5 px-1 rounded-2xl hover:bg-sky-50/70 transition group active:scale-95">
                            <div class="relative w-10 h-10 flex items-center justify-center mb-1 text-sky-500 group-hover:scale-110 group-hover:text-sky-600 transition-all">
                                <svg class="w-6 h-6 stroke-sky-500 group-hover:stroke-sky-600 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z" />
                                </svg>
                                @if(isset($countUlasan) && $countUlasan > 0)
                                    <span class="absolute -top-1 -right-1 bg-gradient-to-r from-sky-500 to-sky-600 text-white text-[9px] font-black min-w-[17px] h-4 px-1 rounded-full flex items-center justify-center border-2 border-white shadow-xs leading-none">
                                        {{ $countUlasan > 99 ? '99+' : $countUlasan }}
                                    </span>
                                @endif
                            </div>
                            <span class="text-[11px] font-bold text-slate-700 group-hover:text-sky-600 transition-colors leading-tight">Beri Ulasan</span>
                        </a>
                    </div>
                </div>

                <!-- AKSI CEPAT DESKTOP (TITIK LOKASI & VOUCHER SAYA) -->
                <div class="hidden md:grid grid-cols-2 gap-3">
                    <a href="{{ route('customer.lokasi') }}" class="rentify-card p-3.5 rounded-2xl hover:border-sky-300 transition shadow-xs flex items-center gap-3 group">
                        <div class="w-9 h-9 rounded-xl bg-sky-100 text-sky-600 flex items-center justify-center text-sm group-hover:scale-105 transition-transform">
                            <i class="fa-solid fa-location-dot"></i>
                        </div>
                        <div>
                            <span class="block font-bold text-xs text-slate-800">Titik Lokasi</span>
                            <span class="block text-[10px] text-slate-400">Atur GPS Anda</span>
                        </div>
                    </a>

                    <a href="{{ route('customer.voucher') }}" class="rentify-card p-3.5 rounded-2xl hover:border-amber-300 transition shadow-xs flex items-center gap-3 group">
                        <div class="w-9 h-9 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center text-sm group-hover:scale-105 transition-transform">
                            <i class="fa-solid fa-ticket"></i>
                        </div>
                        <div>
                            <span class="block font-bold text-xs text-slate-800">Voucher</span>
                            <span class="block text-[10px] text-slate-400">Klaim kupon</span>
                        </div>
                    </a>
                </div>

            </div>

            <!-- RIGHT COLUMN (DESKTOP: MOMENTS / SAVED TABS) -->
            <div class="md:col-span-7 space-y-4">

                <!-- ============================================================== -->
                <!-- 2. SHOPEE-STYLE TABS (TULISAN BIRU & INDIKATOR GARIS BIRU) -->
                <!-- ============================================================== -->
                <div class="rentify-card rounded-3xl overflow-hidden shadow-xs border border-white/80">
                    <!-- Tab Header: Underline Shopee Style (Tulisan Biru & Garis Indikator Biru) -->
                    <div class="flex border-b border-slate-100 bg-white/70 backdrop-blur-md">
                        <button onclick="switchTab('tab-moments')" id="btn-tab-moments" class="flex-1 pt-3.5 pb-2 text-center relative group transition-colors">
                            <div class="flex items-center justify-center gap-1.5 text-xs font-black text-sky-600 transition-colors" id="label-tab-moments">
                                <i class="fa-solid fa-table-cells text-xs"></i>
                                <span>Momen</span>
                                @if(($userMoments->count() ?? 0) > 0)
                                    <span id="badge-moments" class="text-[10px] px-1.5 py-0.2 rounded-full bg-sky-100 text-sky-700 font-bold">{{ $userMoments->count() }}</span>
                                @endif
                            </div>
                            <div id="indicator-tab-moments" class="w-10 h-0.5 bg-sky-500 rounded-full mx-auto mt-2 transition-all duration-200"></div>
                        </button>
                        <button onclick="switchTab('tab-saved')" id="btn-tab-saved" class="flex-1 pt-3.5 pb-2 text-center relative group transition-colors">
                            <div class="flex items-center justify-center gap-1.5 text-xs font-semibold text-slate-400 group-hover:text-sky-600 transition-colors" id="label-tab-saved">
                                <i class="fa-regular fa-heart text-xs transition-colors"></i>
                                <span>Favorit</span>
                                @if((($savedBarang?->count() ?? 0) + ($savedMoments?->count() ?? 0)) > 0)
                                    <span id="badge-saved" class="text-[10px] px-1.5 py-0.2 rounded-full bg-slate-100 text-slate-500 font-bold">{{ (($savedBarang?->count() ?? 0) + ($savedMoments?->count() ?? 0)) }}</span>
                                @endif
                            </div>
                            <div id="indicator-tab-saved" class="w-10 h-0.5 bg-sky-500 rounded-full mx-auto mt-2 opacity-0 transition-all duration-200"></div>
                        </button>
                    </div>

                    <!-- TAB 1: MOMEN SAYA (INSTAGRAM REELS STYLE 9:16 VERTICAL GRID) -->
                    <div id="content-tab-moments" class="p-3">
                        @if($userMoments->isEmpty())
                            <!-- Empty State Momen -->
                            <div class="py-12 px-4 text-center space-y-3">
                                <div class="w-14 h-14 rounded-full bg-sky-50 text-sky-500 flex items-center justify-center text-2xl mx-auto shadow-inner border border-sky-100/60">
                                    <i class="fa-solid fa-camera"></i>
                                </div>
                                <div class="space-y-1">
                                    <h4 class="font-bold text-sm text-slate-800">Belum Ada Momen</h4>
                                    <p class="text-xs text-slate-400 max-w-xs mx-auto">Bagikan foto atau video pendek pengalaman sewa barangmu dan dapatkan reward koin!</p>
                                </div>
                                <div class="pt-2">
                                    <a href="{{ route('customer.moments', ['upload' => 1]) }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-full bg-gradient-to-r from-sky-400 to-sky-600 hover:from-sky-500 hover:to-sky-700 text-white font-bold text-xs shadow-md shadow-sky-500/20 transition transform hover:-translate-y-0.5 active:translate-y-0">
                                        <i class="fa-solid fa-plus"></i> Buat Momen Pertama
                                    </a>
                                </div>
                            </div>
                        @else
                            <!-- 3-Column Instagram Reels Style Grid (aspect-[9/16] / 1080x1920) -->
                            <div class="grid grid-cols-3 gap-1.5 sm:gap-2">
                                @foreach($userMoments as $moment)
                                    <a href="{{ route('customer.moments') }}#moment-card-{{ $moment->id }}" class="group relative aspect-[9/16] bg-slate-900 rounded-2xl overflow-hidden shadow-xs hover:shadow-lg transition-all duration-300 transform hover:-translate-y-0.5 active:scale-95 border border-slate-200/60">
                                        @if($moment->media_type === 'video')
                                            <video src="{{ $moment->media_url }}#t=0.001" preload="metadata" playsinline muted class="w-full h-full object-cover group-hover:scale-105 transition duration-500"></video>
                                            <!-- Video Duration/Badge -->
                                            <div class="absolute top-2 right-2 px-1.5 py-0.5 rounded-md bg-black/60 backdrop-blur-md text-white flex items-center gap-1 text-[8px] font-semibold border border-white/20 shadow-xs">
                                                <i class="fa-solid fa-video text-[7px] text-sky-400"></i>
                                                <span>{{ $moment->video_duration_seconds ? sprintf('%02d:%02d', floor($moment->video_duration_seconds / 60), $moment->video_duration_seconds % 60) : 'Reels' }}</span>
                                            </div>
                                        @elseif(!empty($moment->media_urls) && count($moment->media_urls) > 1)
                                            <img src="{{ $moment->media_url }}" alt="{{ $moment->caption ?? 'Momen' }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                                            <!-- Multi-Photo / Carousel Badge (Instagram Style) -->
                                            <div class="absolute top-2 right-2 px-1.5 py-0.5 rounded-md bg-black/60 backdrop-blur-md text-white flex items-center gap-1 text-[8px] font-semibold border border-white/20 shadow-xs">
                                                <i class="fa-solid fa-images text-[8px] text-sky-400"></i>
                                                <span>{{ count($moment->media_urls) }}</span>
                                            </div>
                                        @else
                                            <img src="{{ $moment->media_url }}" alt="{{ $moment->caption ?? 'Momen' }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                                            <!-- Photo Badge -->
                                            <div class="absolute top-2 right-2 w-5 h-5 rounded-full bg-black/50 backdrop-blur-md text-white flex items-center justify-center text-[9px] border border-white/20 shadow-xs">
                                                <i class="fa-regular fa-image text-[8px] text-sky-300"></i>
                                            </div>
                                        @endif
                                        
                                        <!-- TikTok / IG Bottom Gradient & Metrics (Always visible) -->
                                        <div class="absolute inset-x-0 bottom-0 pt-8 pb-2 px-2 bg-gradient-to-t from-black/85 via-black/40 to-transparent flex flex-col justify-end">
                                            @if(!empty($moment->caption))
                                                <p class="text-[9px] text-white/90 font-medium line-clamp-1 mb-1 drop-shadow-sm leading-tight">{{ $moment->caption }}</p>
                                            @endif
                                            <div class="flex items-center justify-between text-white text-[10px] font-bold">
                                                <span class="flex items-center gap-1 drop-shadow-md">
                                                    @if($moment->media_type === 'video')
                                                        <i class="fa-solid fa-play text-[8px] text-sky-400"></i>
                                                        <span>{{ $moment->views_count ?? max(1, $moment->likes_count * 3) }}</span>
                                                    @else
                                                        <i class="fa-solid fa-heart text-[8px] text-rose-400"></i>
                                                        <span>{{ $moment->likes_count }}</span>
                                                    @endif
                                                </span>
                                                @if($moment->media_type === 'video')
                                                    <span class="flex items-center gap-0.5 text-[9px] text-white/80">
                                                        <i class="fa-solid fa-heart text-[7px] text-rose-400"></i>
                                                        {{ $moment->likes_count }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>

                                        <!-- Border Glow on Hover -->
                                        <div class="absolute inset-0 ring-1 ring-inset ring-white/10 group-hover:ring-sky-400/50 rounded-2xl transition"></div>
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <!-- TAB 2: TERSIMPAN (FAVORIT BARANG & MOMEN) -->
                    <div id="content-tab-saved" class="hidden p-3 space-y-3">
                        <!-- Sub Filter Pill (Modern Shopee Style) -->
                        <div class="flex items-center gap-2 pb-2.5 border-b border-slate-100">
                            <button onclick="switchSavedSubFilter('saved-barang')" id="btn-saved-barang" class="px-3.5 py-1.5 rounded-full text-xs font-bold bg-sky-50 text-sky-600 border border-sky-200 shadow-xs transition">
                                Barang Rental @if(($savedBarang?->count() ?? 0) > 0) ({{ $savedBarang->count() }}) @endif
                            </button>
                            <button onclick="switchSavedSubFilter('saved-moments')" id="btn-saved-moments" class="px-3.5 py-1.5 rounded-full text-xs font-medium bg-white text-slate-500 border border-slate-200/80 hover:text-sky-600 hover:border-sky-200 transition">
                                Momen Disimpan @if(($savedMoments?->count() ?? 0) > 0) ({{ $savedMoments->count() }}) @endif
                            </button>
                        </div>

                        <!-- Sub-Content: Barang Rental Favorit -->
                        <div id="sub-content-saved-barang" class="space-y-2.5">
                            @if(!$savedBarang || $savedBarang->isEmpty())
                                <div class="py-10 px-4 text-center space-y-3">
                                    <div class="w-14 h-14 rounded-full bg-sky-50 text-sky-500 flex items-center justify-center text-2xl mx-auto shadow-inner border border-sky-100/60">
                                        <i class="fa-regular fa-heart"></i>
                                    </div>
                                    <div class="space-y-1">
                                        <h4 class="font-bold text-sm text-slate-800">Belum Ada Favorit</h4>
                                        <p class="text-xs text-slate-400 max-w-xs mx-auto">Tandai barang rental yang kamu sukai dengan ikon hati agar tersimpan rapi di sini.</p>
                                    </div>
                                    <div class="pt-1">
                                        <a href="{{ route('customer.home') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-full bg-sky-50 hover:bg-sky-100 text-sky-600 font-bold text-xs border border-sky-100 transition shadow-xs">
                                            <i class="fa-solid fa-magnifying-glass text-[11px]"></i> Cari Barang Rental
                                        </a>
                                    </div>
                                </div>
                            @else
                                <div class="grid grid-cols-2 gap-2.5">
                                    @foreach($savedBarang as $fav)
                                        @if($fav->barang)
                                        <div class="group bg-white rounded-2xl p-2.5 border border-slate-100 hover:border-sky-200 shadow-xs hover:shadow-md transition-all flex flex-col justify-between relative overflow-hidden">
                                            <a href="{{ route('customer.barang.show', $fav->barang->slug ?? $fav->barang->id) }}" class="block space-y-2">
                                                <div class="aspect-square rounded-xl bg-slate-50 overflow-hidden relative">
                                                    <img src="{{ $fav->barang->cover_photo_url }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-300" onerror="this.onerror=null; this.src='{{ asset('storage/placeholders/placeholder-barang.png') }}';">
                                                    <!-- Floating Sky Blue Heart Badge -->
                                                    <div class="absolute top-1.5 right-1.5 w-6 h-6 rounded-full bg-white/95 backdrop-blur-xs flex items-center justify-center text-sky-500 shadow-xs">
                                                        <i class="fa-solid fa-heart text-[10px]"></i>
                                                    </div>
                                                </div>
                                                <div class="space-y-0.5">
                                                    <h5 class="font-bold text-xs text-slate-800 line-clamp-1 leading-snug group-hover:text-sky-600 transition">{{ $fav->barang->nama }}</h5>
                                                    <span class="text-xs font-black text-sky-600 block">Rp {{ number_format($fav->barang->harga_sewa_harian ?? 0, 0, ',', '.') }}<span class="text-[9px] font-normal text-slate-400">/hari</span></span>
                                                </div>
                                            </a>
                                        </div>
                                        @endif
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        <!-- Sub-Content: Momen Disimpan -->
                        <div id="sub-content-saved-moments" class="hidden">
                            @if(!$savedMoments || $savedMoments->isEmpty())
                                <div class="py-10 px-4 text-center space-y-3">
                                    <div class="w-14 h-14 rounded-full bg-sky-50 text-sky-600 flex items-center justify-center text-2xl mx-auto shadow-inner border border-sky-100/60">
                                        <i class="fa-solid fa-bookmark"></i>
                                    </div>
                                    <div class="space-y-1">
                                        <h4 class="font-bold text-sm text-slate-800">Belum Ada Momen Disimpan</h4>
                                        <p class="text-xs text-slate-400 max-w-xs mx-auto">Simpan inspirasi gaya atau review sewa dari Momen untuk referensimu.</p>
                                    </div>
                                    <div class="pt-1">
                                        <a href="{{ route('customer.moments') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-full bg-sky-50 hover:bg-sky-100 text-sky-600 font-bold text-xs border border-sky-100 transition shadow-xs">
                                            <i class="fa-solid fa-camera text-[11px]"></i> Jelajahi Momen
                                        </a>
                                    </div>
                                </div>
                            @else
                                <div class="grid grid-cols-3 gap-1.5 sm:gap-2">
                                    @foreach($savedMoments as $bm)
                                        @if($bm->moment)
                                        <a href="{{ route('customer.moments') }}#moment-card-{{ $bm->moment->id }}" class="group relative aspect-[9/16] bg-slate-900 rounded-2xl overflow-hidden shadow-xs hover:shadow-lg transition-all duration-300 transform hover:-translate-y-0.5 active:scale-95 border border-slate-200/60 block">
                                            @if($bm->moment->media_type === 'video')
                                                <video src="{{ $bm->moment->media_url }}#t=0.001" preload="metadata" playsinline muted class="w-full h-full object-cover group-hover:scale-105 transition duration-500"></video>
                                                <div class="absolute top-2 right-2 px-1.5 py-0.5 rounded-md bg-black/60 backdrop-blur-md text-white flex items-center gap-1 text-[8px] font-semibold border border-white/20 shadow-xs">
                                                    <i class="fa-solid fa-video text-[7px] text-sky-400"></i>
                                                </div>
                                            @elseif(!empty($bm->moment->media_urls) && count($bm->moment->media_urls) > 1)
                                                <img src="{{ $bm->moment->media_url }}" alt="Momen Disimpan" class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                                                <div class="absolute top-2 right-2 px-1.5 py-0.5 rounded-md bg-black/60 backdrop-blur-md text-white flex items-center gap-1 text-[8px] font-semibold border border-white/20 shadow-xs">
                                                    <i class="fa-solid fa-images text-[8px] text-sky-400"></i>
                                                    <span>{{ count($bm->moment->media_urls) }}</span>
                                                </div>
                                            @else
                                                <img src="{{ $bm->moment->media_url }}" alt="Momen Disimpan" class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                                                <div class="absolute top-2 right-2 w-5 h-5 rounded-full bg-black/50 backdrop-blur-md text-white flex items-center justify-center text-[9px] border border-white/20 shadow-xs">
                                                    <i class="fa-regular fa-image text-[8px] text-sky-300"></i>
                                                </div>
                                            @endif

                                            <!-- Bottom Vignette & Bookmark Badge -->
                                            <div class="absolute inset-x-0 bottom-0 pt-8 pb-2 px-2 bg-gradient-to-t from-black/85 via-black/40 to-transparent flex items-center justify-between text-white text-[10px] font-bold">
                                                <span class="flex items-center gap-1 drop-shadow-md">
                                                    <i class="fa-solid fa-heart text-[8px] text-rose-400"></i>
                                                    {{ $bm->moment->likes_count }}
                                                </span>
                                                <span class="w-5 h-5 rounded-full bg-sky-500/90 backdrop-blur-xs flex items-center justify-center text-white text-[8px] shadow-xs">
                                                    <i class="fa-solid fa-bookmark"></i>
                                                </span>
                                            </div>

                                            <!-- Hover Glow Ring -->
                                            <div class="absolute inset-0 ring-1 ring-inset ring-white/10 group-hover:ring-sky-400/50 rounded-2xl transition"></div>
                                        </a>
                                        @endif
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

            </div>

        </div>

    </main>

    <!-- ============================================================== -->
    <!-- 4. SETTINGS SLIDE-OVER DRAWER (PENGATURAN PANEL GESER) -->
    <!-- ============================================================== -->
    <div id="settings-drawer-backdrop" onclick="closeSettingsDrawer()" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm transition-opacity"></div>
    <div id="settings-drawer-panel" class="fixed top-0 right-0 bottom-0 z-50 w-full max-w-xs bg-white shadow-2xl transform translate-x-full transition-transform duration-300 flex flex-col justify-between">
        <!-- Drawer Header -->
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-xl bg-sky-100 text-sky-600 flex items-center justify-center text-sm">
                    <i class="fa-solid fa-gear"></i>
                </div>
                <h3 class="font-black text-sm text-slate-800">Menu & Pengaturan</h3>
            </div>
            <button onclick="closeSettingsDrawer()" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-500 flex items-center justify-center transition">
                <i class="fa-solid fa-xmark text-sm"></i>
            </button>
        </div>

        <!-- Drawer Menu Items -->
        <div class="p-4 space-y-4 flex-1 overflow-y-auto">
            <!-- SEKSI 1: AKTIVITAS & TRANSAKSI -->
            <div>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-2 mb-1.5">Aktivitas & Transaksi</p>
                <div class="space-y-1.5">
                    <!-- Riwayat Pesanan & Transaksi -->
                    <a href="{{ route('customer.pesanan', ['status' => 'semua']) }}" class="flex items-center justify-between p-3 rounded-2xl hover:bg-sky-50 text-slate-700 transition group">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-sky-100 text-sky-600 flex items-center justify-center text-sm">
                                <i class="fa-solid fa-receipt"></i>
                            </div>
                            <div>
                                <span class="font-bold text-xs text-slate-800 block">Riwayat Pesanan</span>
                                <span class="text-[10px] text-slate-400">Daftar transaksi & status sewa</span>
                            </div>
                        </div>
                        <i class="fa-solid fa-chevron-right text-xs text-slate-400 group-hover:text-sky-600 transition"></i>
                    </a>

                    <!-- Dompet Koin Rentify -->
                    <a href="{{ route('customer.wallet.coins') }}" class="flex items-center justify-between p-3 rounded-2xl hover:bg-amber-50/70 text-slate-700 transition group border border-amber-100/60 bg-amber-50/20">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center text-sm">
                                <i class="fa-solid fa-coins"></i>
                            </div>
                            <div>
                                <span class="font-bold text-xs text-slate-800 block">Dompet Koin Rentify</span>
                                <span class="text-[10px] text-amber-600 font-semibold">{{ number_format($coinBalance ?? 0, 0, ',', '.') }} Koin Tersedia</span>
                            </div>
                        </div>
                        <i class="fa-solid fa-chevron-right text-xs text-slate-400 group-hover:text-amber-600 transition"></i>
                    </a>

                    <!-- Voucher & Promo Diskon Saya -->
                    <a href="{{ route('customer.voucher') }}" class="flex items-center justify-between p-3 rounded-2xl hover:bg-amber-50 text-slate-700 transition group">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center text-sm">
                                <i class="fa-solid fa-ticket"></i>
                            </div>
                            <div>
                                <span class="font-bold text-xs text-slate-800 block">Voucher & Promo</span>
                                <span class="text-[10px] text-slate-400">Klaim & pakai kupon sewa</span>
                            </div>
                        </div>
                        <i class="fa-solid fa-chevron-right text-xs text-slate-400 group-hover:text-amber-600 transition"></i>
                    </a>

                    <!-- Titik Lokasi GPS Pengiriman -->
                    <a href="{{ route('customer.lokasi') }}" class="flex items-center justify-between p-3 rounded-2xl hover:bg-sky-50 text-slate-700 transition group">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-sky-100 text-sky-600 flex items-center justify-center text-sm">
                                <i class="fa-solid fa-location-dot"></i>
                            </div>
                            <div>
                                <span class="font-bold text-xs text-slate-800 block">Titik Lokasi Pengiriman</span>
                                <span class="text-[10px] text-slate-400">Atur pin point GPS pengantaran</span>
                            </div>
                        </div>
                        <i class="fa-solid fa-chevron-right text-xs text-slate-400 group-hover:text-sky-600 transition"></i>
                    </a>
                </div>
            </div>

            <!-- SEKSI 2: PENGATURAN AKUN -->
            <div>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-2 mb-1.5">Pengaturan Akun</p>
                <div class="space-y-1.5">
                    <!-- Edit Profil -->
                    <a href="{{ route('customer.settings.profile') }}" class="flex items-center justify-between p-3 rounded-2xl hover:bg-sky-50 text-slate-700 transition group">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-sky-100 text-sky-600 flex items-center justify-center text-sm">
                                <i class="fa-solid fa-user-pen"></i>
                            </div>
                            <span class="font-bold text-xs text-slate-800">Edit Profil & Biodata</span>
                        </div>
                        <i class="fa-solid fa-chevron-right text-xs text-slate-400 group-hover:text-sky-600 transition"></i>
                    </a>

                    <!-- Keamanan Akun & Sandi -->
                    <a href="{{ route('customer.settings.password') }}" class="flex items-center justify-between p-3 rounded-2xl hover:bg-slate-50 text-slate-700 transition group">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center text-sm">
                                <i class="fa-solid fa-shield-halved"></i>
                            </div>
                            <span class="font-bold text-xs text-slate-800">Keamanan & Sandi</span>
                        </div>
                        <i class="fa-solid fa-chevron-right text-xs text-slate-400 group-hover:text-slate-600 transition"></i>
                    </a>
                </div>
            </div>

            <!-- SEKSI 3: LAINNYA & BANTUAN -->
            <div>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-2 mb-1.5">Lainnya</p>
                <div class="space-y-1.5">
                    <!-- Mode Toko / Vendor -->
                    <a href="{{ route('vendor.dashboard') }}" class="flex items-center justify-between p-3 rounded-2xl hover:bg-sky-50 text-slate-700 transition group">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-sky-100 text-sky-600 flex items-center justify-center text-sm">
                                <i class="fa-solid fa-store"></i>
                            </div>
                            <div>
                                <span class="font-bold text-xs text-slate-800 block">Beralih ke Mode Toko</span>
                                <span class="text-[10px] text-slate-400">Kelola barang rental toko Anda</span>
                            </div>
                        </div>
                        <i class="fa-solid fa-chevron-right text-xs text-slate-400 group-hover:text-sky-600 transition"></i>
                    </a>

                    <!-- WhatsApp CS Help Center -->
                    <a href="https://wa.me/6283183494835?text=Halo%20Tim%20Rentify,%20saya%20butuh%20bantuan" target="_blank" rel="noopener" class="flex items-center justify-between p-3 rounded-2xl hover:bg-emerald-50 text-slate-700 transition group">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center text-sm">
                                <i class="fa-brands fa-whatsapp text-base"></i>
                            </div>
                            <div>
                                <span class="font-bold text-xs text-slate-800 block">Pusat Bantuan WhatsApp</span>
                                <span class="text-[10px] text-slate-400">Layanan pelanggan 24/7</span>
                            </div>
                        </div>
                        <i class="fa-solid fa-chevron-right text-xs text-slate-400 group-hover:text-emerald-600 transition"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Drawer Footer: Logout -->
        <div class="p-4 border-t border-slate-100">
            <form action="{{ route('logout') ?? '/logout' }}" method="POST">
                @csrf
                <button type="submit" class="w-full py-3 px-4 rounded-2xl bg-rose-50 hover:bg-rose-100 border border-rose-200/80 text-rose-600 font-black text-xs uppercase tracking-wider flex items-center justify-center gap-2 transition transform hover:-translate-y-0.5 active:translate-y-0 shadow-xs">
                    <i class="fa-solid fa-right-from-bracket"></i> Keluar Akun
                </button>
            </form>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- 5. BOTTOM NAV (3 TAB RAMPING: BERANDA, MOMEN, AKUN) -->
    <!-- ============================================================== -->
    <x-bottom-navigation-bar active="akun" />

    <!-- JAVASCRIPT LOGIC TABS & SETTINGS DRAWER -->
    <script>
        // Retensi posisi scroll tab tersegmentasi "Momen Saya" dan "Tersimpan" (Requirement 6.15)
        const segmentedTabScroll = {
            'tab-moments': 0,
            'tab-saved': 0
        };
        let activeSegmentedTab = 'tab-moments';

        // Switch Segmented Tabs: Momen Saya vs Tersimpan (Shopee Underline Style)
        function switchTab(tabId) {
            if (activeSegmentedTab === tabId) return;

            // Simpan posisi scroll tampilan aktif
            segmentedTabScroll[activeSegmentedTab] = window.scrollY || window.pageYOffset || 0;

            const labelMoments = document.getElementById('label-tab-moments');
            const labelSaved = document.getElementById('label-tab-saved');
            const indicatorMoments = document.getElementById('indicator-tab-moments');
            const indicatorSaved = document.getElementById('indicator-tab-saved');
            const badgeMoments = document.getElementById('badge-moments');
            const badgeSaved = document.getElementById('badge-saved');
            const contentMoments = document.getElementById('content-tab-moments');
            const contentSaved = document.getElementById('content-tab-saved');

            if (tabId === 'tab-moments') {
                if (labelMoments) labelMoments.className = "flex items-center justify-center gap-1.5 text-xs font-black text-sky-600 transition-colors";
                if (labelSaved) labelSaved.className = "flex items-center justify-center gap-1.5 text-xs font-semibold text-slate-400 group-hover:text-sky-600 transition-colors";
                if (indicatorMoments) indicatorMoments.classList.remove('opacity-0');
                if (indicatorSaved) indicatorSaved.classList.add('opacity-0');
                if (badgeMoments) badgeMoments.className = "text-[10px] px-1.5 py-0.2 rounded-full bg-sky-100 text-sky-700 font-bold";
                if (badgeSaved) badgeSaved.className = "text-[10px] px-1.5 py-0.2 rounded-full bg-slate-100 text-slate-500 font-bold";
                contentMoments.classList.remove('hidden');
                contentSaved.classList.add('hidden');
            } else {
                if (labelSaved) labelSaved.className = "flex items-center justify-center gap-1.5 text-xs font-black text-sky-600 transition-colors";
                if (labelMoments) labelMoments.className = "flex items-center justify-center gap-1.5 text-xs font-semibold text-slate-400 group-hover:text-sky-600 transition-colors";
                if (indicatorSaved) indicatorSaved.classList.remove('opacity-0');
                if (indicatorMoments) indicatorMoments.classList.add('opacity-0');
                if (badgeSaved) badgeSaved.className = "text-[10px] px-1.5 py-0.2 rounded-full bg-sky-100 text-sky-700 font-bold";
                if (badgeMoments) badgeMoments.className = "text-[10px] px-1.5 py-0.2 rounded-full bg-slate-100 text-slate-500 font-bold";
                contentSaved.classList.remove('hidden');
                contentMoments.classList.add('hidden');
            }

            activeSegmentedTab = tabId;

            // Pulihkan posisi scroll yang tersimpan untuk tab tujuan secara instan tanpa reload
            const savedScroll = segmentedTabScroll[tabId] || 0;
            window.scrollTo({
                top: savedScroll,
                behavior: 'instant'
            });
        }

        // Sub-filter Tersimpan: Barang vs Momen
        function switchSavedSubFilter(filter) {
            const btnBarang = document.getElementById('btn-saved-barang');
            const btnMoments = document.getElementById('btn-saved-moments');
            const contentBarang = document.getElementById('sub-content-saved-barang');
            const contentMoments = document.getElementById('sub-content-saved-moments');

            if (filter === 'saved-barang') {
                btnBarang.className = "px-3.5 py-1.5 rounded-full text-xs font-bold bg-sky-50 text-sky-600 border border-sky-200 shadow-xs transition";
                btnMoments.className = "px-3.5 py-1.5 rounded-full text-xs font-medium bg-white text-slate-500 border border-slate-200/80 hover:text-sky-600 hover:border-sky-200 transition";
                contentBarang.classList.remove('hidden');
                contentMoments.classList.add('hidden');
            } else {
                btnMoments.className = "px-3.5 py-1.5 rounded-full text-xs font-bold bg-sky-50 text-sky-600 border border-sky-200 shadow-xs transition";
                btnBarang.className = "px-3.5 py-1.5 rounded-full text-xs font-medium bg-white text-slate-500 border border-slate-200/80 hover:text-sky-600 hover:border-sky-200 transition";
                contentMoments.classList.remove('hidden');
                contentBarang.classList.add('hidden');
            }
        }

        // Settings Slide-Over Drawer
        function openSettingsDrawer() {
            document.getElementById('settings-drawer-backdrop').classList.remove('hidden');
            document.getElementById('settings-drawer-panel').classList.remove('translate-x-full');
            document.body.classList.add('overflow-hidden');
        }

        function closeSettingsDrawer() {
            document.getElementById('settings-drawer-backdrop').classList.add('hidden');
            document.getElementById('settings-drawer-panel').classList.add('translate-x-full');
            document.body.classList.remove('overflow-hidden');
        }
    </script>
</body>
</html>