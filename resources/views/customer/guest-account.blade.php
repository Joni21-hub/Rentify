<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Akun Saya — Rentify</title>
    <meta name="description" content="Masuk atau daftar ke akun Rentify untuk menikmati pengalaman sewa barang mudah, pelacakan pesanan, dan dapatkan Koin Rentify.">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="{{ asset('rentify-theme.css') }}">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            background: radial-gradient(circle at center, #fff9ef 0%, #bad6eb 100%) fixed !important;
        }
    </style>
</head>

<body class="min-h-screen text-slate-800 pb-28 md:pb-12 bg-flowing overflow-x-hidden w-full max-w-full">

    <!-- ===== DESKTOP TOP NAVBAR ===== -->
    <header class="rentify-navbar hidden md:flex sticky top-0 z-50 bg-white/80 backdrop-blur-md border-b border-slate-100">
        <div class="max-w-5xl mx-auto w-full px-6 py-3 flex items-center justify-between gap-4">
            <a href="{{ route('customer.home') }}" class="flex items-center gap-2 flex-shrink-0">
                <img src="https://res.cloudinary.com/fnf8f1pm/image/upload/v1784199454/gambar_logo_trerjo.png" class="h-8 object-contain" alt="Logo">
                <span class="text-xl font-black text-sky-500 tracking-tighter">Rentify</span>
            </a>
            <div class="flex items-center gap-3">
                <a href="{{ route('login') }}" class="px-5 py-2.5 rounded-2xl text-xs font-black uppercase tracking-wider text-sky-700 bg-white/70 hover:bg-white border border-slate-200/80 shadow-xs transition">
                    Masuk
                </a>
                <a href="{{ route('register') }}" class="px-5 py-2.5 rounded-2xl text-xs font-black uppercase tracking-wider bg-gradient-to-br from-sky-400 to-sky-600 hover:from-sky-500 hover:to-sky-700 text-white shadow-md shadow-sky-600/20 transition transform hover:-translate-y-0.5">
                    Daftar
                </a>
            </div>
        </div>
    </header>

    <!-- ===== MAIN CONTENT: GUEST LANDING STATE ===== -->
    <main id="guest-account-view" class="max-w-md mx-auto px-4 pt-6 md:pt-10">

        <!-- Welcome Hero Card -->
        <div class="rentify-card p-6 rounded-3xl text-center shadow-xl shadow-sky-900/5 border border-white/80 mb-6 bg-white/90 backdrop-blur-xl">
            <div class="w-20 h-20 mx-auto rounded-3xl bg-gradient-to-tr from-sky-400 to-sky-600 p-1 shadow-lg shadow-sky-500/25 flex items-center justify-center mb-4">
                <div class="w-full h-full bg-white rounded-[22px] flex items-center justify-center">
                    <i class="fa-solid fa-user-circle text-4xl text-sky-500"></i>
                </div>
            </div>

            <h1 class="text-xl font-black text-slate-800 tracking-tight mb-1">Selamat Datang di Rentify!</h1>
            <p class="text-xs text-slate-500 max-w-xs mx-auto leading-relaxed mb-6">
                Masuk ke akunmu untuk mengelola pesanan sewa, kumpulkan Koin Rentify dari Momen, dan simpan barang favorit.
            </p>

            <!-- Action Buttons -->
            <div class="flex flex-col gap-2.5">
                <a href="{{ route('login') }}" class="rentify-btn w-full py-3.5 px-4 rounded-2xl text-xs font-black tracking-widest uppercase shadow-md transition-all transform hover:-translate-y-0.5 active:translate-y-0 flex items-center justify-center gap-2">
                    <i class="fa-solid fa-arrow-right-to-bracket text-xs"></i>
                    <span>Masuk</span>
                </a>
                <a href="{{ route('register') }}" class="w-full py-3.5 px-4 rounded-2xl bg-white/80 hover:bg-white active:scale-[0.99] text-slate-700 font-extrabold text-xs tracking-wider uppercase transition flex items-center justify-center gap-2 border border-slate-200/90 shadow-xs">
                    <i class="fa-solid fa-user-plus text-xs"></i>
                    <span>Daftar</span>
                </a>
            </div>
        </div>

        <!-- Keuntungan Memiliki Akun Rentify -->
        <div class="rentify-card p-5 rounded-3xl border border-white/80 shadow-md shadow-sky-900/5 mb-6 bg-white/80 backdrop-blur-md">
            <h2 class="text-xs font-black uppercase tracking-wider text-slate-400 mb-4 px-1">
                Keuntungan Akun Rentify
            </h2>

            <div class="space-y-4">
                <!-- Benefit 1: Koin Rentify -->
                <div class="flex items-start gap-3.5">
                    <div class="w-10 h-10 rounded-2xl bg-amber-50 text-amber-500 flex items-center justify-center flex-shrink-0 text-base shadow-xs border border-amber-100">
                        <i class="fa-solid fa-coins"></i>
                    </div>
                    <div>
                        <h3 class="text-xs font-black text-slate-800">Koin Rentify dari Momen</h3>
                        <p class="text-[11px] text-slate-500 leading-snug mt-0.5">
                            Dapatkan koin gratis dari setiap like di postingan Momen. 1 Koin = Rp 100 untuk diskon checkout sewa!
                        </p>
                    </div>
                </div>

                <!-- Benefit 2: Pelacakan Pesanan Real-Time -->
                <div class="flex items-start gap-3.5">
                    <div class="w-10 h-10 rounded-2xl bg-sky-50 text-sky-600 flex items-center justify-center flex-shrink-0 text-base shadow-xs border border-sky-100">
                        <i class="fa-solid fa-truck-fast"></i>
                    </div>
                    <div>
                        <h3 class="text-xs font-black text-slate-800">Pelacakan Pesanan Real-time</h3>
                        <p class="text-[11px] text-slate-500 leading-snug mt-0.5">
                            Pantau siklus sewa mulai dari verifikasi toko, pengambilan barang, masa sewa, hingga pengembalian.
                        </p>
                    </div>
                </div>

                <!-- Benefit 3: Simpan Barang & Momen Favorit -->
                <div class="flex items-start gap-3.5">
                    <div class="w-10 h-10 rounded-2xl bg-rose-50 text-rose-500 flex items-center justify-center flex-shrink-0 text-base shadow-xs border border-rose-100">
                        <i class="fa-solid fa-bookmark"></i>
                    </div>
                    <div>
                        <h3 class="text-xs font-black text-slate-800">Koleksi Favorit & Tersimpan</h3>
                        <p class="text-[11px] text-slate-500 leading-snug mt-0.5">
                            Simpan barang rental impian dan bookmark inspirasi foto/video momen untuk disewa kapan pun kamu butuh.
                        </p>
                    </div>
                </div>

                <!-- Benefit 4: Transaksi Terlindungi -->
                <div class="flex items-start gap-3.5">
                    <div class="w-10 h-10 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center flex-shrink-0 text-base shadow-xs border border-emerald-100">
                        <i class="fa-solid fa-shield-halved"></i>
                    </div>
                    <div>
                        <h3 class="text-xs font-black text-slate-800">Sewa Nyaman & Terpercaya</h3>
                        <p class="text-[11px] text-slate-500 leading-snug mt-0.5">
                            Seluruh vendor rental telah terverifikasi dengan jaminan deposit transparan dan perlindungan penuh.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pusat Bantuan Card -->
        <div class="p-4 rounded-3xl bg-white/80 backdrop-blur-md border border-white/80 shadow-md shadow-sky-900/5 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center flex-shrink-0 text-lg shadow-xs border border-emerald-100">
                    <i class="fa-brands fa-whatsapp"></i>
                </div>
                <div>
                    <h4 class="text-xs font-black text-slate-800">Butuh Bantuan?</h4>
                    <p class="text-[11px] text-slate-500">Hubungi Customer Service WhatsApp Rentify</p>
                </div>
            </div>
            <a href="https://wa.me/6283183494835?text=Halo%20Tim%20Rentify,%20saya%20ingin%20bertanya" target="_blank" rel="noopener noreferrer" class="px-4 py-2 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-extrabold uppercase tracking-wider shadow-xs transition transform hover:-translate-y-0.5">
                Chat
            </a>
        </div>

    </main>

    <!-- ===== BOTTOM NAVIGATION BAR (3 TABS: BERANDA, MOMEN, AKUN) ===== -->
    <x-bottom-navigation-bar active="akun" />

</body>
</html>
