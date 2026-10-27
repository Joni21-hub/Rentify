<!DOCTYPE html>
<html lang="id" class="scroll-smooth bg-[#090D16] text-slate-100 antialiased selection:bg-sky-500 selection:text-white">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rentify — Sewa Apapun Terdekat, Kapan Saja & Tanpa Beban</title>
    <meta name="description" content="Platform marketplace rental #1 terpercaya. Sewa kamera, tenda camping, drone, motor, hingga gadget dari toko rental terverifikasi dalam radius 50 KM.">
    <meta name="keywords" content="Rentify, sewa kamera, rental tenda camping, sewa drone, rental motor, marketplace sewa barang indonesia">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="{{ url('/') }}" />

    <!-- Favicon & Touch Icons -->
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="https://res.cloudinary.com/fnf8f1pm/image/upload/v1784472853/logolimaduabelas_gjelaz.png">
    <meta name="theme-color" content="#090D16">

    <!-- Open Graph & Social Cards -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url('/') }}">
    <meta property="og:site_name" content="Rentify">
    <meta property="og:title" content="Rentify — Platform Marketplace Rental Terpercaya">
    <meta property="og:description" content="Sewa kamera, alat outdoor, gadget & kendaraan dari toko rental terdekat dalam radius 50 KM. Hemat hingga 95% tanpa harus membeli.">
    <meta property="og:image" content="https://res.cloudinary.com/fnf8f1pm/image/upload/v1784472853/logolimaduabelas_gjelaz.png">

    <!-- Schema.org JSON-LD -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@graph": [
        {
          "@type": "WebSite",
          "@id": "{{ url('/') }}/#website",
          "url": "{{ url('/') }}",
          "name": "Rentify",
          "description": "Platform marketplace rental barang, kamera, alat outdoor, dan elektronik terpercaya.",
          "potentialAction": {
            "@type": "SearchAction",
            "target": "{{ route('customer.search') }}?q={search_term_string}",
            "query-input": "required name=search_term_string"
          }
        },
        {
          "@type": "Organization",
          "@id": "{{ url('/') }}/#organization",
          "name": "Rentify",
          "url": "{{ url('/') }}",
          "logo": "https://res.cloudinary.com/fnf8f1pm/image/upload/v1784472853/logolimaduabelas_gjelaz.png"
        }
      ]
    }
    </script>

    <!-- Tailwind CSS & Master Fonts -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <style>
        :root {
            --brand-primary: #0ea5e9;
            --brand-light: #38bdf8;
            --brand-dark: #0284c7;
            --surface-card: rgba(255, 255, 255, 0.035);
            --surface-border: rgba(255, 255, 255, 0.08);
        }

        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif !important;
            background-color: #090D16 !important;
            color: #f1f5f9;
            overflow-x: hidden;
        }

        /* Dot Grid Studio Pattern */
        .grid-studio-pattern {
            background-image: radial-gradient(rgba(255, 255, 255, 0.07) 1px, transparent 1px);
            background-size: 32px 32px;
        }

        /* Ambient Lighting Cones */
        .hero-glow-top {
            background: radial-gradient(circle 750px at 50% -10%, rgba(56, 189, 248, 0.22) 0%, rgba(14, 165, 233, 0.06) 45%, transparent 75%);
        }
        .ambient-radial-center {
            background: radial-gradient(circle 500px at center, rgba(14, 165, 233, 0.15) 0%, transparent 70%);
        }

        /* Frosted Glassmorphism Deluxe */
        .glass-deluxe {
            background: rgba(15, 23, 42, 0.65);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.09);
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.45), inset 0 1px 0 rgba(255, 255, 255, 0.12);
        }

        .glass-card {
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.05) 0%, rgba(255, 255, 255, 0.015) 100%);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.35), inset 0 1px 0 rgba(255, 255, 255, 0.1);
            transition: all 0.35s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .glass-card:hover {
            transform: translateY(-4px);
            border-color: rgba(56, 189, 248, 0.4);
            background: linear-gradient(180deg, rgba(56, 189, 248, 0.08) 0%, rgba(255, 255, 255, 0.03) 100%);
            box-shadow: 0 25px 50px rgba(14, 165, 233, 0.18), inset 0 1px 0 rgba(255, 255, 255, 0.2);
        }

        /* Buttons Deluxe */
        .btn-glow-primary {
            background: linear-gradient(135deg, #38bdf8 0%, #0284c7 100%);
            box-shadow: 0 10px 30px -5px rgba(14, 165, 233, 0.5), inset 0 1px 0 rgba(255, 255, 255, 0.4);
            transition: all 0.25s ease;
        }
        .btn-glow-primary:hover {
            background: linear-gradient(135deg, #7dd3fc 0%, #0369a1 100%);
            box-shadow: 0 15px 35px -3px rgba(14, 165, 233, 0.65), inset 0 1px 0 rgba(255, 255, 255, 0.6);
            transform: translateY(-2px);
        }

        .btn-ghost-deluxe {
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.12);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.08);
            transition: all 0.25s ease;
        }
        .btn-ghost-deluxe:hover {
            background: rgba(255, 255, 255, 0.09);
            border-color: rgba(255, 255, 255, 0.25);
            transform: translateY(-2px);
        }

        /* Floating HUD Keyframes */
        @keyframes floatHUD1 {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-7px); }
        }
        @keyframes floatHUD2 {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(7px); }
        }
        .animate-hud-1 { animation: floatHUD1 6s ease-in-out infinite; }
        .animate-hud-2 { animation: floatHUD2 7.5s ease-in-out infinite 1s; }

        /* Neon Pulse Radar */
        @keyframes radarPulse {
            0% { transform: scale(0.95); opacity: 0.8; }
            50% { transform: scale(1.15); opacity: 0.3; }
            100% { transform: scale(0.95); opacity: 0.8; }
        }
        .radar-pulse { animation: radarPulse 3s infinite ease-in-out; }

        /* Custom scrollbar */
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #090D16; }
        ::-webkit-scrollbar-thumb { background: #1e293b; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #38bdf8; }
    </style>
</head>
<body class="grid-studio-pattern min-h-screen flex flex-col justify-between">

    <!-- ============================================================== -->
    <!-- 1. FLOATING LUXURY NAVBAR -->
    <!-- ============================================================== -->
    <header class="fixed top-0 inset-x-0 z-50 px-4 sm:px-6 lg:px-8 py-3.5 transition-all">
        <div class="max-w-6xl mx-auto glass-deluxe rounded-full px-5 sm:px-7 py-2.5 sm:py-3 flex items-center justify-between shadow-2xl">
            
            <!-- Brand Logo & Status -->
            <a href="{{ url('/') }}" class="flex items-center gap-3 group">
                <div class="relative flex items-center justify-center">
                    <div class="absolute -inset-1 rounded-full bg-sky-500/25 blur-sm group-hover:bg-sky-500/40 transition"></div>
                    <img src="https://res.cloudinary.com/fnf8f1pm/image/upload/v1784199454/gambar_logo_trerjo.png" 
                         class="h-8 w-auto object-contain relative transition-transform duration-300 group-hover:scale-105" 
                         alt="Rentify Emblem">
                </div>
                <div class="flex flex-col">
                    <span class="text-base sm:text-lg font-black tracking-tight text-white leading-none group-hover:text-sky-300 transition">Rentify</span>
                    <span class="text-[9px] font-bold tracking-widest text-sky-400 uppercase mt-0.5 flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-ping"></span>
                        RENTAL PLATFORM
                    </span>
                </div>
            </a>

            <!-- Quick Navigation (Desktop) -->
            <nav class="hidden lg:flex items-center gap-8 text-xs font-semibold text-slate-300 tracking-wide">
                <a href="#kategori" class="hover:text-white transition">Kategori Sewa</a>
                <a href="#keunggulan" class="hover:text-white transition">Kenapa Rentify?</a>
                <a href="#cara-kerja" class="hover:text-white transition">Cara Kerja</a>
                <a href="#keamanan" class="hover:text-white transition">Keamanan & Garansi</a>
                <a href="#faq" class="hover:text-white transition">FAQ</a>
                <a href="{{ route('vendor.register') }}" class="text-sky-400 hover:text-sky-300 font-bold transition flex items-center gap-1">
                    <span>Buka Toko Rental</span>
                    <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i>
                </a>
            </nav>

            <!-- Authentication CTAs -->
            <div class="flex items-center gap-2 sm:gap-3">
                <a href="{{ route('login') }}" 
                   class="btn-ghost-deluxe px-4 sm:px-5 py-2 rounded-full text-xs font-bold text-slate-200 hover:text-white transition active:scale-95 flex items-center gap-2">
                    <i class="fa-solid fa-arrow-right-to-bracket text-sky-400 text-xs"></i>
                    <span>Masuk</span>
                </a>
                <a href="{{ route('register') }}" 
                   class="btn-glow-primary px-4 sm:px-5 py-2 rounded-full text-xs font-extrabold uppercase tracking-wider text-white transition active:scale-95 flex items-center gap-1.5">
                    <span>Daftar Gratis</span>
                </a>
            </div>

        </div>
    </header>

    <!-- ============================================================== -->
    <!-- 2. HERO: VALUE PROPOSITION & HIGH-END IMPACT -->
    <!-- ============================================================== -->
    <section class="relative pt-32 sm:pt-40 pb-20 sm:pb-32 overflow-hidden hero-glow-top">
        
        <!-- Ambient Light Core -->
        <div class="absolute top-1/4 left-1/2 -translate-x-1/2 w-[700px] sm:w-[900px] h-[400px] bg-sky-500/15 blur-[140px] rounded-full pointer-events-none"></div>

        <div class="max-w-6xl mx-auto px-4 sm:px-6 relative z-10 text-center">
            
            <!-- Verified Pill Badge -->
            <div class="inline-flex items-center gap-2.5 px-4 py-1.5 rounded-full bg-white/[0.04] border border-white/[0.1] backdrop-blur-xl mb-6 shadow-inner">
                <span class="flex h-2 w-2 rounded-full bg-emerald-400 animate-pulse"></span>
                <span class="text-[11px] sm:text-xs font-extrabold tracking-wide text-sky-300 uppercase">
                    ✨ Marketplace Rental #1 Terpercaya di Sekitarmu
                </span>
            </div>

            <!-- Bold, Crystal Clear Headline -->
            <h1 class="text-3xl sm:text-5xl md:text-6xl lg:text-7xl font-black tracking-tight text-white leading-[1.1] max-w-4xl mx-auto mb-6">
                Sewa Apapun yang Kamu Butuhkan, <br class="hidden sm:inline" />
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-sky-400 via-sky-200 to-indigo-300">
                    Kapan Saja & Tanpa Beban Membeli.
                </span>
            </h1>

            <!-- Plain, Punchy Subtitle: Clear & Relatable -->
            <p class="text-sm sm:text-base md:text-lg text-slate-300 max-w-2xl mx-auto font-normal leading-relaxed mb-8 sm:mb-10">
                Dari kamera sinema, tenda camping, drone 4K, hingga kendaraan liburan. Rentify menghubungkan kamu dengan ratusan toko rental terverifikasi dalam radius <strong>50 KM</strong>. Nikmati pengalaman terbaik dengan <strong>hemat hingga 95%</strong>.
            </p>

            <!-- Dual Clear Call To Action -->
            <div class="flex flex-col sm:flex-row items-center justify-center gap-3.5 max-w-md mx-auto mb-16">
                <a href="{{ route('login') }}" 
                   class="w-full sm:w-auto btn-glow-primary px-8 py-3.5 rounded-full text-sm font-extrabold text-white transition flex items-center justify-center gap-2.5 shadow-xl">
                    <i class="fa-solid fa-magnifying-glass text-xs"></i>
                    <span>Mulai Cari Barang Rental</span>
                </a>
                <a href="{{ route('vendor.register') }}" 
                   class="w-full sm:w-auto btn-ghost-deluxe px-7 py-3.5 rounded-full text-sm font-bold text-slate-200 hover:text-white transition flex items-center justify-center gap-2">
                    <i class="fa-solid fa-store text-sky-400"></i>
                    <span>Punya Barang? Buka Rental</span>
                </a>
            </div>

            <!-- Mini Trust Badges -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 max-w-3xl mx-auto mb-16">
                <div class="px-4 py-2.5 rounded-2xl bg-white/[0.03] border border-white/[0.06] flex items-center gap-2.5 text-left">
                    <i class="fa-solid fa-location-crosshairs text-sky-400 text-base"></i>
                    <div>
                        <div class="text-xs font-black text-white">Radius 50 KM</div>
                        <div class="text-[10px] text-slate-400">Toko Terdekat di Sekitarmu</div>
                    </div>
                </div>
                <div class="px-4 py-2.5 rounded-2xl bg-white/[0.03] border border-white/[0.06] flex items-center gap-2.5 text-left">
                    <i class="fa-solid fa-shield-halved text-emerald-400 text-base"></i>
                    <div>
                        <div class="text-xs font-black text-white">100% Terverifikasi</div>
                        <div class="text-[10px] text-slate-400">Toko & Akun Berizin</div>
                    </div>
                </div>
                <div class="px-4 py-2.5 rounded-2xl bg-white/[0.03] border border-white/[0.06] flex items-center gap-2.5 text-left">
                    <i class="fa-solid fa-qrcode text-sky-400 text-base"></i>
                    <div>
                        <div class="text-xs font-black text-white">QRIS & Escrow</div>
                        <div class="text-[10px] text-slate-400">Deposit Balik Otomatis</div>
                    </div>
                </div>
                <div class="px-4 py-2.5 rounded-2xl bg-white/[0.03] border border-white/[0.06] flex items-center gap-2.5 text-left">
                    <i class="fa-solid fa-camera-rotate text-purple-400 text-base"></i>
                    <div>
                        <div class="text-xs font-black text-white">Cek Fisik Digital</div>
                        <div class="text-[10px] text-slate-400">Anti-Ribet & Bebas Sengketa</div>
                    </div>
                </div>
            </div>

            <!-- ============================================================== -->
            <!-- 3. VISUAL STUDIO SHOWCASE (3D STAGE & FLOATING HUD) -->
            <!-- ============================================================== -->
            <div class="relative max-w-4xl mx-auto">
                
                <!-- Underglow ambient -->
                <div class="absolute -bottom-10 left-1/2 -translate-x-1/2 w-3/4 h-32 bg-sky-500/20 blur-3xl pointer-events-none"></div>

                <!-- Main Glass Centerpiece Showcase -->
                <div class="relative mx-auto rounded-3xl p-3 sm:p-5 bg-gradient-to-b from-white/[0.08] via-white/[0.02] to-transparent border border-white/[0.1] shadow-2xl backdrop-blur-2xl">
                    <div class="relative rounded-2xl overflow-hidden aspect-[16/9] sm:aspect-[21/9] bg-[#0c1220] flex items-center justify-center border border-white/[0.06]">
                        
                        <!-- Rich High-End Gear Visual (Atmosphere of Studio, Cameras, Outdoor & Tech) -->
                        <img src="https://images.unsplash.com/photo-1516035069371-29a1b244cc32?auto=format&fit=crop&w=1600&q=85" 
                             alt="Rentify Studio Gear" 
                             class="w-full h-full object-cover object-center opacity-70 filter brightness-95 contrast-105">

                        <!-- Velvet Gradient Vignettes -->
                        <div class="absolute inset-0 bg-gradient-to-t from-[#090D16] via-[#090D16]/30 to-transparent"></div>
                        <div class="absolute inset-0 bg-gradient-to-r from-[#090D16]/60 via-transparent to-[#090D16]/60"></div>
                        
                        <!-- Core Showcase Content Overlay -->
                        <div class="absolute bottom-5 inset-x-5 sm:inset-x-8 flex flex-col sm:flex-row items-start sm:items-end justify-between gap-3 text-left">
                            <div>
                                <span class="px-2.5 py-1 rounded-md bg-sky-500/20 border border-sky-400/30 text-sky-300 text-[10px] font-black uppercase tracking-wider inline-block mb-1.5">
                                    ● HYPERLOCAL RADAR AKTIF
                                </span>
                                <h3 class="text-base sm:text-xl font-black text-white tracking-tight">
                                    Jaringan Rental Resmi Terdekat
                                </h3>
                                <p class="text-xs text-slate-300 max-w-md hidden sm:block mt-0.5">
                                    Kamera, lensa, camping gear, dan gadget siap ambil hari ini di kota Anda.
                                </p>
                            </div>
                            <div class="flex items-center gap-2">
                                <a href="{{ route('login') }}" class="px-4 py-2 rounded-xl bg-white/10 hover:bg-white/20 border border-white/20 text-white text-xs font-bold transition flex items-center gap-1.5">
                                    <span>Lihat Semua Kategori</span>
                                    <i class="fa-solid fa-arrow-right text-[10px]"></i>
                                </a>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- FLOATING HUD 1: Radar Proximity (Top Left) -->
                <div class="absolute -top-6 -left-2 sm:-left-6 z-20 animate-hud-1 hidden sm:block">
                    <div class="glass-deluxe rounded-2xl p-3.5 text-left max-w-[220px] shadow-2xl border border-sky-400/25">
                        <div class="flex items-center gap-2.5 mb-1.5">
                            <div class="w-7 h-7 rounded-lg bg-sky-500/20 text-sky-400 flex items-center justify-center text-xs">
                                <i class="fa-solid fa-radar radar-pulse"></i>
                            </div>
                            <div>
                                <span class="text-[9px] font-black uppercase text-sky-400 tracking-wider block leading-tight">RADAR GPS</span>
                                <span class="text-xs font-bold text-white leading-tight">1.2 KM Terdekat</span>
                            </div>
                        </div>
                        <p class="text-[10px] text-slate-300 leading-snug">
                            Tersedia 45+ alat siap diambil langsung hari ini.
                        </p>
                    </div>
                </div>

                <!-- FLOATING HUD 2: Verified Escrow (Top Right) -->
                <div class="absolute -top-4 -right-2 sm:-right-6 z-20 animate-hud-2 hidden sm:block">
                    <div class="glass-deluxe rounded-2xl p-3.5 text-left max-w-[220px] shadow-2xl border border-emerald-400/25">
                        <div class="flex items-center gap-2 mb-1.5">
                            <div class="w-7 h-7 rounded-lg bg-emerald-500/20 text-emerald-400 flex items-center justify-center text-xs">
                                <i class="fa-solid fa-shield-check"></i>
                            </div>
                            <div>
                                <span class="text-[9px] font-black uppercase text-emerald-400 tracking-wider block leading-tight">ESCROW SYSTEM</span>
                                <span class="text-xs font-bold text-white leading-tight">Dana Terlindungi</span>
                            </div>
                        </div>
                        <p class="text-[10px] text-slate-300 leading-snug">
                            Deposit kembali otomatis setelah barang selesai dipakai.
                        </p>
                    </div>
                </div>

                <!-- FLOATING HUD 3: Huge Savings (Bottom Left) -->
                <div class="absolute -bottom-6 -left-2 sm:-left-6 z-20 animate-hud-2 hidden md:block">
                    <div class="glass-deluxe rounded-2xl p-3 text-left flex items-center gap-3 shadow-2xl border border-white/15">
                        <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-sky-400 to-sky-600 text-white flex items-center justify-center text-sm shadow-lg shadow-sky-500/30 flex-shrink-0">
                            <i class="fa-solid fa-piggy-bank"></i>
                        </div>
                        <div>
                            <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider block">EFISIENSI BIAYA</span>
                            <span class="text-xs font-black text-white">Hemat Biaya Hingga 95%</span>
                        </div>
                    </div>
                </div>

                <!-- FLOATING HUD 4: Digital Checklist (Bottom Right) -->
                <div class="absolute -bottom-6 -right-2 sm:-right-6 z-20 animate-hud-1 hidden md:block">
                    <div class="glass-deluxe rounded-2xl p-3 text-left flex items-center gap-3 shadow-2xl border border-white/15">
                        <div class="w-9 h-9 rounded-xl bg-purple-500/20 text-purple-400 border border-purple-400/30 flex items-center justify-center text-sm flex-shrink-0">
                            <i class="fa-solid fa-clipboard-check"></i>
                        </div>
                        <div>
                            <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider block">DOKUMENTASI FOTO</span>
                            <span class="text-xs font-black text-white">Cek Fisik Sebelum Sewa</span>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </section>

    <!-- ============================================================== -->
    <!-- 4. VIRAL INTERACTIVE "BELI VS SEWA" VALUE CALCULATOR -->
    <!-- ============================================================== -->
    <section id="keunggulan" class="py-20 sm:py-28 relative">
        <div class="max-w-6xl mx-auto px-4 sm:px-6">
            
            <div class="text-center max-w-2xl mx-auto mb-14">
                <span class="text-xs font-black text-sky-400 tracking-widest uppercase block mb-2">
                    💡 PERHITUNGAN CERDAS
                </span>
                <h2 class="text-2xl sm:text-4xl font-black text-white tracking-tight">
                    Kenapa Beli Jika Cuma Dipakai Sesekali?
                </h2>
                <p class="text-xs sm:text-sm text-slate-400 mt-2.5">
                    Bandingkan pengeluaran nyata antara membeli peralatan mahal versus menyewanya di Rentify.
                </p>
            </div>

            <!-- Interactive Comparison Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 max-w-4xl mx-auto">
                
                <!-- Opsi Beli Sendiri -->
                <div class="glass-card rounded-3xl p-7 sm:p-9 border border-rose-500/20 relative overflow-hidden bg-gradient-to-b from-rose-500/[0.03] to-transparent">
                    <div class="flex items-center justify-between mb-6">
                        <span class="text-[10px] font-black tracking-widest uppercase text-rose-400 bg-rose-500/10 px-3 py-1 rounded-full">
                            MEMBELI SENDIRI
                        </span>
                        <div class="w-8 h-8 rounded-full bg-rose-500/20 text-rose-400 flex items-center justify-center text-sm">
                            <i class="fa-solid fa-xmark"></i>
                        </div>
                    </div>

                    <h3 class="text-xl font-black text-white mb-2">Beban Modal & Depresiasi Aset</h3>
                    <p class="text-xs text-slate-400 mb-6">Membeli alat profesional yang jarang digunakan setiap minggu:</p>

                    <ul class="space-y-4 text-xs text-slate-300">
                        <li class="flex items-start gap-3">
                            <i class="fa-solid fa-circle-xmark text-rose-400 text-sm mt-0.5 flex-shrink-0"></i>
                            <div>
                                <strong class="text-white">Keluar Puluhan Juta Sekaligus:</strong> Modal langsung terkunci pada barang yang dipakai 1-2 kali setahun.
                            </div>
                        </li>
                        <li class="flex items-start gap-3">
                            <i class="fa-solid fa-circle-xmark text-rose-400 text-sm mt-0.5 flex-shrink-0"></i>
                            <div>
                                <strong class="text-white">Nilai Turun 30-50%:</strong> Depresiasi harga barang begitu keluar seri terbaru di pasaran.
                            </div>
                        </li>
                        <li class="flex items-start gap-3">
                            <i class="fa-solid fa-circle-xmark text-rose-400 text-sm mt-0.5 flex-shrink-0"></i>
                            <div>
                                <strong class="text-white">Pusing Perawatan & Servis:</strong> Harus ganti baterai, kalibrasi, bersihkan jamur lensa, dan simpan di dry box.
                            </div>
                        </li>
                    </ul>

                    <div class="mt-8 pt-5 border-t border-white/[0.08] flex items-center justify-between text-xs">
                        <span class="text-slate-400">Total Pengeluaran:</span>
                        <span class="text-rose-400 font-extrabold text-sm">Rp 15.000.000 - Rp 60.000.000+</span>
                    </div>
                </div>

                <!-- Opsi Sewa di Rentify -->
                <div class="glass-card rounded-3xl p-7 sm:p-9 border border-sky-400/40 relative overflow-hidden bg-gradient-to-b from-sky-500/[0.08] to-transparent shadow-xl">
                    <div class="flex items-center justify-between mb-6">
                        <span class="text-[10px] font-black tracking-widest uppercase text-sky-300 bg-sky-500/20 px-3 py-1 rounded-full">
                            SEWA DI RENTIFY
                        </span>
                        <div class="w-8 h-8 rounded-full bg-sky-500/20 text-sky-400 flex items-center justify-center text-sm">
                            <i class="fa-solid fa-check"></i>
                        </div>
                    </div>

                    <h3 class="text-xl font-black text-white mb-2">Hemat 95% & Bebas Khawatir</h3>
                    <p class="text-xs text-sky-200/80 mb-6">Akses fleksibel barang prima saat proyek atau liburan dimulai:</p>

                    <ul class="space-y-4 text-xs text-slate-200">
                        <li class="flex items-start gap-3">
                            <i class="fa-solid fa-circle-check text-sky-400 text-sm mt-0.5 flex-shrink-0"></i>
                            <div>
                                <strong class="text-white">Bayar Hanya Waktu Pakai:</strong> Cukup ratusan ribu untuk durasi proyek atau akhir pekan Anda.
                            </div>
                        </li>
                        <li class="flex items-start gap-3">
                            <i class="fa-solid fa-circle-check text-sky-400 text-sm mt-0.5 flex-shrink-0"></i>
                            <div>
                                <strong class="text-white">Alat Selalu Seri Terbaru:</strong> Bebas ganti kamera, lensa, tenda, atau drone teranyar sesuka hati.
                            </div>
                        </li>
                        <li class="flex items-start gap-3">
                            <i class="fa-solid fa-circle-check text-sky-400 text-sm mt-0.5 flex-shrink-0"></i>
                            <div>
                                <strong class="text-white">Zero Biaya Perawatan:</strong> Barang diterima bersih, terkalibrasi, dan siap pakai langsung.
                            </div>
                        </li>
                    </ul>

                    <div class="mt-8 pt-5 border-t border-white/[0.08] flex items-center justify-between text-xs">
                        <span class="text-slate-300">Biaya Rata-Rata:</span>
                        <span class="text-sky-300 font-extrabold text-sm">Mulai Rp 50.000 / hari</span>
                    </div>
                </div>

            </div>

        </div>
    </section>

    <!-- ============================================================== -->
    <!-- 5. 5 KATEGORI UNGGULAN: JELAS & BUKAN JUALAN SATUAN -->
    <!-- ============================================================== -->
    <section id="kategori" class="py-20 sm:py-28 relative bg-[#070b13]/80">
        <div class="max-w-6xl mx-auto px-4 sm:px-6">
            
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-14">
                <div>
                    <span class="text-xs font-black text-sky-400 tracking-widest uppercase block mb-2">
                        🌟 EKOSISTEM RENTAL
                    </span>
                    <h2 class="text-2xl sm:text-4xl font-black text-white tracking-tight">
                        Jelajahi Berbagai Kategori Pilihan
                    </h2>
                </div>
                <p class="text-xs sm:text-sm text-slate-400 max-w-md">
                    Rentify menghadirkan ribuan pilihan barang dari toko rental terpercaya untuk segala kebutuhan kreatif, petualangan, dan hobi Anda.
                </p>
            </div>

            <!-- 5 Curated Category Showcase (Visual, Aesthetic, High Clarity) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                
                <!-- Kategori 1: Kamera & Sinematografi -->
                <div class="glass-card rounded-3xl overflow-hidden group border border-white/[0.08]">
                    <div class="relative aspect-[16/10] overflow-hidden bg-slate-900">
                        <img src="https://images.unsplash.com/photo-1516035069371-29a1b244cc32?auto=format&fit=crop&w=800&q=80" 
                             alt="Kamera & Sinematografi" 
                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500 opacity-80">
                        <div class="absolute inset-0 bg-gradient-to-t from-[#0B0F19] via-transparent to-transparent"></div>
                        <span class="absolute top-3.5 left-3.5 px-3 py-1 rounded-full bg-black/60 backdrop-blur-md text-sky-300 text-[10px] font-black uppercase tracking-wider border border-white/10">
                            📸 FOTOGRAFI & VIDEO
                        </span>
                    </div>
                    <div class="p-6 text-left">
                        <h3 class="text-lg font-black text-white mb-1.5">Kamera & Sinematografi</h3>
                        <p class="text-xs text-slate-300 leading-relaxed mb-4">
                            Sony FX3, Cinema Line, Mirrorless A7IV, Lensa Prime G-Master, Gimbal DJI Ronin, dan Lighting Studio.
                        </p>
                        <div class="flex flex-wrap gap-1.5 text-[10px] font-semibold text-sky-300">
                            <span class="px-2.5 py-0.5 rounded-md bg-white/[0.05] border border-white/[0.08]">Kamera Mirrorless</span>
                            <span class="px-2.5 py-0.5 rounded-md bg-white/[0.05] border border-white/[0.08]">Lensa Tele & Prime</span>
                            <span class="px-2.5 py-0.5 rounded-md bg-white/[0.05] border border-white/[0.08]">Gimbal & Rig</span>
                        </div>
                    </div>
                </div>

                <!-- Kategori 2: Outdoor, Camping & Naik Gunung -->
                <div class="glass-card rounded-3xl overflow-hidden group border border-white/[0.08]">
                    <div class="relative aspect-[16/10] overflow-hidden bg-slate-900">
                        <img src="https://images.unsplash.com/photo-1510312305653-8ed496efae75?auto=format&fit=crop&w=800&q=80" 
                             alt="Outdoor Camping" 
                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500 opacity-80">
                        <div class="absolute inset-0 bg-gradient-to-t from-[#0B0F19] via-transparent to-transparent"></div>
                        <span class="absolute top-3.5 left-3.5 px-3 py-1 rounded-full bg-black/60 backdrop-blur-md text-emerald-300 text-[10px] font-black uppercase tracking-wider border border-white/10">
                            ⛺ OUTDOOR & PETUALANGAN
                        </span>
                    </div>
                    <div class="p-6 text-left">
                        <h3 class="text-lg font-black text-white mb-1.5">Camping & Perlengkapan Gunung</h3>
                        <p class="text-xs text-slate-300 leading-relaxed mb-4">
                            Tenda dome 2-6 orang, carrier deuter/osprey, sleeping bag bulu angsa, kompor portabel, dan flysheet.
                        </p>
                        <div class="flex flex-wrap gap-1.5 text-[10px] font-semibold text-emerald-300">
                            <span class="px-2.5 py-0.5 rounded-md bg-white/[0.05] border border-white/[0.08]">Tenda 4-Musim</span>
                            <span class="px-2.5 py-0.5 rounded-md bg-white/[0.05] border border-white/[0.08]">Carrier & Pack</span>
                            <span class="px-2.5 py-0.5 rounded-md bg-white/[0.05] border border-white/[0.08]">Cooking Set</span>
                        </div>
                    </div>
                </div>

                <!-- Kategori 3: Drone & Aerial Videografi -->
                <div class="glass-card rounded-3xl overflow-hidden group border border-white/[0.08]">
                    <div class="relative aspect-[16/10] overflow-hidden bg-slate-900">
                        <img src="https://images.unsplash.com/photo-1527977966376-1c8408f9f108?auto=format&fit=crop&w=800&q=80" 
                             alt="Drone & Aerial" 
                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500 opacity-80">
                        <div class="absolute inset-0 bg-gradient-to-t from-[#0B0F19] via-transparent to-transparent"></div>
                        <span class="absolute top-3.5 left-3.5 px-3 py-1 rounded-full bg-black/60 backdrop-blur-md text-amber-300 text-[10px] font-black uppercase tracking-wider border border-white/10">
                            🚁 DRONE & AERIAL
                        </span>
                    </div>
                    <div class="p-6 text-left">
                        <h3 class="text-lg font-black text-white mb-1.5">Drone & Kamera Aksi 4K</h3>
                        <p class="text-xs text-slate-300 leading-relaxed mb-4">
                            DJI Mavic 3 Pro, DJI Mini 4 Pro, GoPro Hero Black, Insta360 X4 untuk tangkapan visual udara resolusi tinggi.
                        </p>
                        <div class="flex flex-wrap gap-1.5 text-[10px] font-semibold text-amber-300">
                            <span class="px-2.5 py-0.5 rounded-md bg-white/[0.05] border border-white/[0.08]">DJI Mavic Series</span>
                            <span class="px-2.5 py-0.5 rounded-md bg-white/[0.05] border border-white/[0.08]">GoPro 4K/5.3K</span>
                            <span class="px-2.5 py-0.5 rounded-md bg-white/[0.05] border border-white/[0.08]">Insta360 Action</span>
                        </div>
                    </div>
                </div>

                <!-- Kategori 4: Gaming, Konsol & Gadget -->
                <div class="glass-card rounded-3xl overflow-hidden group border border-white/[0.08]">
                    <div class="relative aspect-[16/10] overflow-hidden bg-slate-900">
                        <img src="https://images.unsplash.com/photo-1606813907291-d86efa9b94db?auto=format&fit=crop&w=800&q=80" 
                             alt="Gaming & Konsol" 
                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500 opacity-80">
                        <div class="absolute inset-0 bg-gradient-to-t from-[#0B0F19] via-transparent to-transparent"></div>
                        <span class="absolute top-3.5 left-3.5 px-3 py-1 rounded-full bg-black/60 backdrop-blur-md text-indigo-300 text-[10px] font-black uppercase tracking-wider border border-white/10">
                            🎮 GADGET & KONSOL
                        </span>
                    </div>
                    <div class="p-6 text-left">
                        <h3 class="text-lg font-black text-white mb-1.5">Konsol Game & Laptop Pro</h3>
                        <p class="text-xs text-slate-300 leading-relaxed mb-4">
                            PlayStation 5, Nintendo Switch OLED, MacBook Pro untuk editing, iPad Pro stylus, dan VR Headset Meta Quest.
                        </p>
                        <div class="flex flex-wrap gap-1.5 text-[10px] font-semibold text-indigo-300">
                            <span class="px-2.5 py-0.5 rounded-md bg-white/[0.05] border border-white/[0.08]">PlayStation 5</span>
                            <span class="px-2.5 py-0.5 rounded-md bg-white/[0.05] border border-white/[0.08]">Nintendo Switch</span>
                            <span class="px-2.5 py-0.5 rounded-md bg-white/[0.05] border border-white/[0.08]">MacBook & iPad</span>
                        </div>
                    </div>
                </div>

                <!-- Kategori 5: Mobilitas & Kendaraan Liburan -->
                <div class="glass-card rounded-3xl overflow-hidden group border border-white/[0.08]">
                    <div class="relative aspect-[16/10] overflow-hidden bg-slate-900">
                        <img src="https://images.unsplash.com/photo-1558981806-ec527fa84c39?auto=format&fit=crop&w=800&q=80" 
                             alt="Mobilitas & Motor" 
                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500 opacity-80">
                        <div class="absolute inset-0 bg-gradient-to-t from-[#0B0F19] via-transparent to-transparent"></div>
                        <span class="absolute top-3.5 left-3.5 px-3 py-1 rounded-full bg-black/60 backdrop-blur-md text-rose-300 text-[10px] font-black uppercase tracking-wider border border-white/10">
                            🛵 KENDARAAN & TOUR
                        </span>
                    </div>
                    <div class="p-6 text-left">
                        <h3 class="text-lg font-black text-white mb-1.5">Motor Wisata & Mobilitas Kota</h3>
                        <p class="text-xs text-slate-300 leading-relaxed mb-4">
                            Vespa matic klasik, Yamaha NMAX/XMAX, Honda ADV untuk eksplorasi kota atau trip pantai tanpa ribet bawa kendaraan sendiri.
                        </p>
                        <div class="flex flex-wrap gap-1.5 text-[10px] font-semibold text-rose-300">
                            <span class="px-2.5 py-0.5 rounded-md bg-white/[0.05] border border-white/[0.08]">Vespa Matic</span>
                            <span class="px-2.5 py-0.5 rounded-md bg-white/[0.05] border border-white/[0.08]">Maxi Scooter</span>
                            <span class="px-2.5 py-0.5 rounded-md bg-white/[0.05] border border-white/[0.08]">Sepeda Listrik</span>
                        </div>
                    </div>
                </div>

                <!-- Kategori 6: Audio & Perlengkapan Acara -->
                <div class="glass-card rounded-3xl overflow-hidden group border border-white/[0.08] flex flex-col justify-between">
                    <div>
                        <div class="relative aspect-[16/10] overflow-hidden bg-slate-900">
                            <img src="https://images.unsplash.com/photo-1598488035139-bdbb2231ce04?auto=format&fit=crop&w=800&q=80" 
                                 alt="Audio & Event" 
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500 opacity-80">
                            <div class="absolute inset-0 bg-gradient-to-t from-[#0B0F19] via-transparent to-transparent"></div>
                            <span class="absolute top-3.5 left-3.5 px-3 py-1 rounded-full bg-black/60 backdrop-blur-md text-cyan-300 text-[10px] font-black uppercase tracking-wider border border-white/10">
                                🎤 AUDIO & EVENT
                            </span>
                        </div>
                        <div class="p-6 text-left">
                            <h3 class="text-lg font-black text-white mb-1.5">Audio, Sound & Proyektor</h3>
                            <p class="text-xs text-slate-300 leading-relaxed mb-4">
                                Mic wireless Rode/DJI Mic, portable sound system JBL, proyektor lumens tinggi untuk nobar dan acara kantor.
                            </p>
                            <div class="flex flex-wrap gap-1.5 text-[10px] font-semibold text-cyan-300">
                                <span class="px-2.5 py-0.5 rounded-md bg-white/[0.05] border border-white/[0.08]">Mic Wireless</span>
                                <span class="px-2.5 py-0.5 rounded-md bg-white/[0.05] border border-white/[0.08]">Sound Portable</span>
                                <span class="px-2.5 py-0.5 rounded-md bg-white/[0.05] border border-white/[0.08]">Proyektor HD</span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </section>

    <!-- ============================================================== -->
    <!-- 6. CARA KERJA RENTIFY: 4 LANGKAH SEDERHANA -->
    <!-- ============================================================== -->
    <section id="cara-kerja" class="py-20 sm:py-28 relative">
        <div class="max-w-6xl mx-auto px-4 sm:px-6">
            
            <div class="text-center max-w-2xl mx-auto mb-16">
                <span class="text-xs font-black text-sky-400 tracking-widest uppercase block mb-2">
                    ⚡ MUDAH & CEPAT
                </span>
                <h2 class="text-2xl sm:text-4xl font-black text-white tracking-tight">
                    Cara Sewa Barang di Rentify
                </h2>
                <p class="text-xs sm:text-sm text-slate-400 mt-2.5">
                    Hanya perlu 4 langkah praktis dari mencari barang hingga pengembalian selesai.
                </p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                
                <!-- Step 1 -->
                <div class="glass-card rounded-3xl p-7 text-left relative flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-6">
                            <span class="w-10 h-10 rounded-2xl bg-sky-500/20 text-sky-400 font-black text-sm flex items-center justify-center">01</span>
                            <i class="fa-solid fa-magnifying-glass-location text-slate-500 text-lg"></i>
                        </div>
                        <h4 class="text-base font-black text-white mb-2">Cari Barang Terdekat</h4>
                        <p class="text-xs text-slate-300 leading-relaxed">
                            Cari barang impianmu. Sistem radar otomatis menampilkan toko rental terdekat dalam radius hingga 50 KM.
                        </p>
                    </div>
                    <div class="mt-6 pt-3 border-t border-white/[0.06] text-[11px] text-sky-400 font-bold">
                        Filter Jarak & Rating
                    </div>
                </div>

                <!-- Step 2 -->
                <div class="glass-card rounded-3xl p-7 text-left relative flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-6">
                            <span class="w-10 h-10 rounded-2xl bg-sky-500/20 text-sky-400 font-black text-sm flex items-center justify-center">02</span>
                            <i class="fa-solid fa-calendar-check text-slate-500 text-lg"></i>
                        </div>
                        <h4 class="text-base font-black text-white mb-2">Booking & Bayar Aman</h4>
                        <p class="text-xs text-slate-300 leading-relaxed">
                            Pilih tanggal pemakaian. Bayar aman via QRIS atau Transfer. Dana dan deposit ditahan sistem secara aman.
                        </p>
                    </div>
                    <div class="mt-6 pt-3 border-t border-white/[0.06] text-[11px] text-sky-400 font-bold">
                        Konfirmasi Instan
                    </div>
                </div>

                <!-- Step 3 -->
                <div class="glass-card rounded-3xl p-7 text-left relative flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-6">
                            <span class="w-10 h-10 rounded-2xl bg-sky-500/20 text-sky-400 font-black text-sm flex items-center justify-center">03</span>
                            <i class="fa-solid fa-camera-viewfinder text-slate-500 text-lg"></i>
                        </div>
                        <h4 class="text-base font-black text-white mb-2">Cek Fisik & Ambil</h4>
                        <p class="text-xs text-slate-300 leading-relaxed">
                            Ambil di toko atau diantar. Verifikasi kondisi fisik barang bersama toko dengan foto manifest digital resmi.
                        </p>
                    </div>
                    <div class="mt-6 pt-3 border-t border-white/[0.06] text-[11px] text-sky-400 font-bold">
                        Anti Sengketa Kondisi
                    </div>
                </div>

                <!-- Step 4 -->
                <div class="glass-card rounded-3xl p-7 text-left relative flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-6">
                            <span class="w-10 h-10 rounded-2xl bg-emerald-500/20 text-emerald-400 font-black text-sm flex items-center justify-center">04</span>
                            <i class="fa-solid fa-rotate-left text-slate-500 text-lg"></i>
                        </div>
                        <h4 class="text-base font-black text-white mb-2">Kembali & Deposit Cair</h4>
                        <p class="text-xs text-slate-300 leading-relaxed">
                            Selesai pakai, kembalikan ke toko. Begitu diverifikasi, uang deposit langsung cair kembali dan dapat Koin Rentify.
                        </p>
                    </div>
                    <div class="mt-6 pt-3 border-t border-white/[0.06] text-[11px] text-emerald-400 font-bold">
                        Deposit Kembali 100%
                    </div>
                </div>

            </div>

        </div>
    </section>

    <!-- ============================================================== -->
    <!-- 7. KEAMANAN & SISTEM GARANSI TERPERCAYA -->
    <!-- ============================================================== -->
    <section id="keamanan" class="py-20 sm:py-28 relative bg-[#070b13]/80">
        <div class="max-w-6xl mx-auto px-4 sm:px-6">
            
            <div class="text-center max-w-2xl mx-auto mb-16">
                <span class="text-xs font-black text-emerald-400 tracking-widest uppercase block mb-2">
                    🛡️ KEAMANAN & KEPERCAYAAN
                </span>
                <h2 class="text-2xl sm:text-4xl font-black text-white tracking-tight">
                    Sewa Nyaman Tanpa Rasa Khawatir
                </h2>
                <p class="text-xs sm:text-sm text-slate-400 mt-2.5">
                    Protokol perlindungan ganda yang melindungi penyewa maupun pemilik barang di setiap transaksi.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                
                <div class="glass-card rounded-3xl p-8 text-left">
                    <div class="w-12 h-12 rounded-2xl bg-sky-500/15 text-sky-400 flex items-center justify-center text-xl mb-6">
                        <i class="fa-solid fa-id-card-clip"></i>
                    </div>
                    <h3 class="text-lg font-black text-white mb-2.5">Verifikasi Identitas Ketat</h3>
                    <p class="text-xs text-slate-300 leading-relaxed">
                        Penyewa dan toko mitra wajib melewati verifikasi data resmi (KTP & Kontak). Komunitas bersih dari akun palsu dan oknum tidak bertanggung jawab.
                    </p>
                </div>

                <div class="glass-card rounded-3xl p-8 text-left">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-500/15 text-emerald-400 flex items-center justify-center text-xl mb-6">
                        <i class="fa-solid fa-vault"></i>
                    </div>
                    <h3 class="text-lg font-black text-white mb-2.5">Rekening Bersama (Escrow)</h3>
                    <p class="text-xs text-slate-300 leading-relaxed">
                        Uang sewa dan deposit tidak langsung diserahkan sebelum Anda menerima barang sesuai kesepakatan. Jika barang batal/tidak sesuai, dana kembali utuh.
                    </p>
                </div>

                <div class="glass-card rounded-3xl p-8 text-left">
                    <div class="w-12 h-12 rounded-2xl bg-purple-500/15 text-purple-400 flex items-center justify-center text-xl mb-6">
                        <i class="fa-solid fa-headset"></i>
                    </div>
                    <h3 class="text-lg font-black text-white mb-2.5">Bantuan & Mediasi Siaga</h3>
                    <p class="text-xs text-slate-300 leading-relaxed">
                        Tim admin Rentify siap menjadi penengah jika ada kendala saat serah terima maupun pengembalian. Anda tidak ditinggal sendirian.
                    </p>
                </div>

            </div>

        </div>
    </section>

    <!-- ============================================================== -->
    <!-- 8. AJAKAN MITRA TOKO / VENDOR (PASSIVE INCOME DARI BARANG PASIF) -->
    <!-- ============================================================== -->
    <section class="py-20 sm:py-28 relative overflow-hidden">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 relative z-10">
            <div class="glass-deluxe rounded-3xl p-8 sm:p-12 border border-sky-400/30 relative overflow-hidden text-center sm:text-left shadow-2xl">
                
                <!-- Glow decor -->
                <div class="absolute -right-20 -bottom-20 w-80 h-80 bg-sky-500/20 rounded-full blur-3xl pointer-events-none"></div>

                <div class="flex flex-col lg:flex-row items-center justify-between gap-8 relative z-10">
                    <div class="max-w-xl">
                        <span class="text-xs font-black tracking-widest uppercase text-sky-400 block mb-2">
                            💼 KHUSUS PEMILIK BARANG & TOKO RENTAL
                        </span>
                        <h2 class="text-2xl sm:text-4xl font-black text-white tracking-tight mb-3">
                            Punya Barang Nganggur? <br class="hidden sm:inline" />
                            <span class="text-transparent bg-clip-text bg-gradient-to-r from-sky-300 to-indigo-200">
                                Jadikan Mesin Penghasilan Pasif.
                            </span>
                        </h2>
                        <p class="text-xs sm:text-sm text-slate-300 leading-relaxed">
                            Daripada kamera, tenda, atau genset hanya disimpan dan berdebu di lemari, sewakan di Rentify! Dapatkan penghasilan jutaan rupiah per bulan dengan proteksi deposit dan data penyewa terverifikasi.
                        </p>
                    </div>

                    <div class="flex-shrink-0">
                        <a href="{{ route('vendor.register') }}" 
                           class="btn-glow-primary px-8 py-4 rounded-full text-white font-black text-xs sm:text-sm uppercase tracking-wider transition inline-flex items-center gap-2.5 shadow-xl">
                            <i class="fa-solid fa-store text-xs"></i>
                            <span>Daftar Jadi Toko Rental</span>
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- ============================================================== -->
    <!-- 9. FAQ INTERAKTIF (PERTANYAAN UMUM YANG SERING DITANYAKAN) -->
    <!-- ============================================================== -->
    <section id="faq" class="py-20 sm:py-28 relative bg-[#070b13]/80">
        <div class="max-w-4xl mx-auto px-4 sm:px-6">
            
            <div class="text-center max-w-xl mx-auto mb-14">
                <span class="text-xs font-black text-sky-400 tracking-widest uppercase block mb-2">
                    ❓ PERTANYAAN UMUM
                </span>
                <h2 class="text-2xl sm:text-4xl font-black text-white tracking-tight">
                    Pertanyaan yang Sering Diajukan
                </h2>
                <p class="text-xs sm:text-sm text-slate-400 mt-2">
                    Segala hal yang perlu kamu ketahui sebelum mulai menyewa di Rentify.
                </p>
            </div>

            <!-- Accordion List -->
            <div class="space-y-3.5" id="faq-accordion">
                
                <!-- FAQ 1 -->
                <div class="glass-card rounded-2xl p-5 border border-white/[0.08] cursor-pointer" onclick="toggleFaq(1)">
                    <div class="flex items-center justify-between gap-4">
                        <h4 class="text-sm font-bold text-white">Apa syarat utama untuk menyewa barang di Rentify?</h4>
                        <i id="faq-icon-1" class="fa-solid fa-chevron-down text-sky-400 text-xs transition-transform duration-300"></i>
                    </div>
                    <div id="faq-content-1" class="hidden mt-3 pt-3 border-t border-white/[0.06] text-xs text-slate-300 leading-relaxed">
                        Cukup mendaftar akun di Rentify dan melakukan verifikasi identitas (seperti KTP/SIM yang berlaku). Beberapa toko mungkin memerlukan deposit jaminan yang akan dikembalikan 100% setelah masa sewa berakhir.
                    </div>
                </div>

                <!-- FAQ 2 -->
                <div class="glass-card rounded-2xl p-5 border border-white/[0.08] cursor-pointer" onclick="toggleFaq(2)">
                    <div class="flex items-center justify-between gap-4">
                        <h4 class="text-sm font-bold text-white">Bagaimana jika barang mengalami kerusakan saat saya gunakan?</h4>
                        <i id="faq-icon-2" class="fa-solid fa-chevron-down text-sky-400 text-xs transition-transform duration-300"></i>
                    </div>
                    <div id="faq-content-2" class="hidden mt-3 pt-3 border-t border-white/[0.06] text-xs text-slate-300 leading-relaxed">
                        Sebelum serah terima, Anda dan pihak toko rental akan melakukan inspeksi fisik bersama dan mendokumentasikan foto kondisi awal di sistem Rentify. Jika terjadi kerusakan akibat kelalaian pemakaian, biaya perbaikan akan dikomunikasikan secara transparan sesuai kesepakatan deposit.
                    </div>
                </div>

                <!-- FAQ 3 -->
                <div class="glass-card rounded-2xl p-5 border border-white/[0.08] cursor-pointer" onclick="toggleFaq(3)">
                    <div class="flex items-center justify-between gap-4">
                        <h4 class="text-sm font-bold text-white">Apakah pembayaran di Rentify aman?</h4>
                        <i id="faq-icon-3" class="fa-solid fa-chevron-down text-sky-400 text-xs transition-transform duration-300"></i>
                    </div>
                    <div id="faq-content-3" class="hidden mt-3 pt-3 border-t border-white/[0.06] text-xs text-slate-300 leading-relaxed">
                        Sangat aman. Rentify menggunakan sistem pembayaran escrow (rekening bersama) dengan QRIS resmi. Uang sewa tidak langsung dicairkan ke toko sampai Anda berhasil menerima barang dalam kondisi sesuai.
                    </div>
                </div>

                <!-- FAQ 4 -->
                <div class="glass-card rounded-2xl p-5 border border-white/[0.08] cursor-pointer" onclick="toggleFaq(4)">
                    <div class="flex items-center justify-between gap-4">
                        <h4 class="text-sm font-bold text-white">Berapa radius jarak pencarian barang di Rentify?</h4>
                        <i id="faq-icon-4" class="fa-solid fa-chevron-down text-sky-400 text-xs transition-transform duration-300"></i>
                    </div>
                    <div id="faq-content-4" class="hidden mt-3 pt-3 border-t border-white/[0.06] text-xs text-slate-300 leading-relaxed">
                        Sistem Rentify mengutamakan konsep hyperlocal dengan radius hingga 50 KM dari koordinat Anda. Hal ini memastikan barang bisa Anda ambil langsung secara fleksibel atau diantar cepat tanpa menunggu kargo berhari-hari.
                    </div>
                </div>

                <!-- FAQ 5 -->
                <div class="glass-card rounded-2xl p-5 border border-white/[0.08] cursor-pointer" onclick="toggleFaq(5)">
                    <div class="flex items-center justify-between gap-4">
                        <h4 class="text-sm font-bold text-white">Bagaimana cara saya mendaftar sebagai toko rental (Vendor)?</h4>
                        <i id="faq-icon-5" class="fa-solid fa-chevron-down text-sky-400 text-xs transition-transform duration-300"></i>
                    </div>
                    <div id="faq-content-5" class="hidden mt-3 pt-3 border-t border-white/[0.06] text-xs text-slate-300 leading-relaxed">
                        Klik tombol "Buka Toko Rental" di navigasi atas, lengkapi profil toko Anda dan unggah barang yang ingin disewakan. Tim Rentify akan memverifikasi dalam waktu singkat sehingga barang Anda segera bisa disewa ribuan pengguna.
                    </div>
                </div>

            </div>

        </div>
    </section>

    <!-- ============================================================== -->
    <!-- 10. GRAND CTA: MULAI PETUALANGANMU -->
    <!-- ============================================================== -->
    <section class="py-24 sm:py-32 relative text-center">
        <div class="max-w-3xl mx-auto px-4 sm:px-6">
            
            <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-white/[0.04] border border-white/[0.08] mb-6">
                <span class="w-2 h-2 rounded-full bg-sky-400 animate-ping"></span>
                <span class="text-xs font-bold text-sky-400 uppercase tracking-wider">
                    GABUNG SEKARANG
                </span>
            </div>

            <h2 class="text-3xl sm:text-5xl font-black text-white tracking-tight mb-5">
                Siap Menjelajahi Barang Impianmu?
            </h2>
            <p class="text-xs sm:text-base text-slate-300 max-w-lg mx-auto leading-relaxed mb-10">
                Masuk ke platform Rentify untuk melihat ratusan barang sewaan terdekat, mengecek ketersediaan jadwal toko, dan mulai sewa dengan mudah.
            </p>

            <div class="flex flex-col sm:flex-row items-center justify-center gap-3.5 max-w-sm mx-auto">
                <a href="{{ route('login') }}" 
                   class="w-full sm:w-auto btn-glow-primary px-9 py-4 rounded-full text-xs sm:text-sm font-black uppercase tracking-wider text-white transition flex items-center justify-center gap-2.5 shadow-2xl">
                    <i class="fa-solid fa-arrow-right-to-bracket text-xs"></i>
                    <span>Masuk ke Rentify</span>
                </a>
                <a href="{{ route('register') }}" 
                   class="w-full sm:w-auto btn-ghost-deluxe px-8 py-4 rounded-full text-xs sm:text-sm font-bold tracking-wider text-slate-200 transition flex items-center justify-center gap-2">
                    <i class="fa-solid fa-user-plus text-sky-400 text-xs"></i>
                    <span>Daftar Akun Baru</span>
                </a>
            </div>

        </div>
    </section>

    <!-- ============================================================== -->
    <!-- 11. FOOTER LENGKAP & BERKELAS -->
    <!-- ============================================================== -->
    <footer class="border-t border-white/[0.08] bg-[#060911] py-12 text-xs text-slate-400 mt-auto">
        <div class="max-w-6xl mx-auto px-4 sm:px-6">
            <div class="flex flex-col md:flex-row items-center justify-between gap-8 pb-8 border-b border-white/[0.06] text-center md:text-left">
                
                <div class="flex items-center gap-3">
                    <img src="https://res.cloudinary.com/fnf8f1pm/image/upload/v1784199454/gambar_logo_trerjo.png" 
                         class="h-8 w-auto object-contain opacity-95" 
                         alt="Rentify Emblem">
                    <div class="flex flex-col">
                        <span class="text-base font-black text-white tracking-tight">Rentify</span>
                        <span class="text-[9px] font-bold text-sky-400 uppercase tracking-widest">Platform Marketplace Rental Terpercaya</span>
                    </div>
                </div>

                <div class="flex flex-wrap justify-center items-center gap-6 sm:gap-8 font-semibold text-slate-300 text-xs">
                    <a href="#kategori" class="hover:text-white transition">Kategori</a>
                    <a href="#keunggulan" class="hover:text-white transition">Beli vs Sewa</a>
                    <a href="#cara-kerja" class="hover:text-white transition">Cara Kerja</a>
                    <a href="#keamanan" class="hover:text-white transition">Keamanan</a>
                    <a href="#faq" class="hover:text-white transition">FAQ</a>
                    <a href="{{ route('vendor.register') }}" class="hover:text-sky-300 text-sky-400 transition">Mitra Toko</a>
                    <a href="{{ route('login') }}" class="hover:text-white transition">Masuk</a>
                    <a href="{{ route('register') }}" class="hover:text-white transition">Daftar</a>
                </div>

            </div>

            <div class="pt-8 flex flex-col sm:flex-row items-center justify-between gap-4 text-[11px] text-slate-500 text-center sm:text-left">
                <p>&copy; 2026 Rentify (<a href="https://rentify21.my.id" class="text-sky-400 hover:underline">rentify21.my.id</a>). All rights reserved.</p>
                <p class="font-bold text-[10px] text-slate-500 tracking-wider">PLATFORM RENTAL HYPERLOCAL RADIUS 50 KM</p>
            </div>
        </div>
    </footer>

    <!-- Interactive Scripts for Accordion -->
    <script>
        function toggleFaq(id) {
            const content = document.getElementById('faq-content-' + id);
            const icon = document.getElementById('faq-icon-' + id);
            if (content.classList.contains('hidden')) {
                content.classList.remove('hidden');
                icon.classList.add('rotate-180');
            } else {
                content.classList.add('hidden');
                icon.classList.remove('rotate-180');
            }
        }
    </script>

</body>
</html>
