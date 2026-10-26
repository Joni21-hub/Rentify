<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Riwayat Transaksi - Rentify</title>
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
<body class="min-h-screen pb-24 text-slate-800 bg-flowing">

    <div class="max-w-md mx-auto min-h-screen relative">
        
        <header class="rentify-navbar sticky top-0 z-50 px-4 py-3.5 flex items-center justify-between bg-white/80 backdrop-blur-md border-b border-white/60 shadow-xs">
            <a href="{{ route('customer.dashboard') }}" class="w-8 h-8 rounded-xl bg-white/80 hover:bg-white text-slate-700 flex items-center justify-center shadow-xs border border-white/80 transition">
                <i class="fa-solid fa-arrow-left text-xs text-slate-600"></i>
            </a>
            <h1 class="text-base font-black text-slate-800 flex-1 text-center pr-8 tracking-tight">Riwayat Transaksi</h1>
        </header>

        <div class="p-4 pt-8">
            <div class="rentify-card p-8 rounded-3xl flex flex-col items-center justify-center text-center shadow-sm">
                <div class="w-20 h-20 bg-sky-50 rounded-full flex items-center justify-center mb-4 shadow-inner">
                    <i class="fa-solid fa-receipt text-3xl text-sky-400"></i>
                </div>
                <h3 class="font-black text-slate-800 text-base mb-1">Belum Ada Transaksi</h3>
                <p class="text-xs font-medium text-slate-500 mb-6 max-w-xs leading-relaxed">Kamu belum pernah menyewa barang apapun. Yuk, mulai cari barang impianmu!</p>
                <a href="{{ route('customer.home') }}" class="rentify-btn px-8 py-3.5 rounded-2xl text-xs font-black tracking-widest uppercase shadow-md transition-all transform hover:-translate-y-0.5 active:translate-y-0 flex items-center justify-center gap-2">
                    <span>Sewa Sekarang</span>
                    <i class="fa-solid fa-arrow-right text-xs"></i>
                </a>
            </div>
        </div>
        
        <!-- BOTTOM NAV MOBILE (3 TAB RAMPING) -->
        <x-bottom-navigation-bar active="akun" />

    </div>

</body>
</html>