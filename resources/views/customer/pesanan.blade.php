<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Transaksi - Rentify</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="{{ asset('rentify-theme.css') }}">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body class="min-h-screen pb-20 text-slate-700">

    <div class="max-w-md mx-auto bg-slate-50 min-h-screen relative shadow-md">
        
        <div class="rentify-navbar bg-gradient-to-r from-sky-400 to-[#0369a1] sticky top-0 z-50 px-4 py-4 flex items-center gap-4">
            <a href="{{ route('customer.dashboard') }}" class="text-white hover:text-sky-200 transition">
                <i class="fa-solid fa-arrow-left text-lg"></i>
            </a>
            <h1 class="text-lg font-bold text-white flex-1 text-center pr-6">Riwayat Transaksi</h1>
        </div>

        <div class="p-4">
            <div class="flex flex-col items-center justify-center py-32 text-slate-400 text-center">
                <div class="w-24 h-24 bg-sky-50 rounded-full flex items-center justify-center mb-4">
                    <i class="fa-solid fa-receipt text-5xl text-sky-300"></i>
                </div>
                <h3 class="font-bold text-slate-600 mb-1">Belum ada transaksi</h3>
                <p class="text-sm font-medium text-slate-500 mb-6 px-4">Kamu belum pernah menyewa barang apapun. Yuk, mulai cari barang impianmu!</p>
                <a href="{{ route('customer.home') }}" class="bg-gradient-to-r from-sky-400 to-[#0369a1] text-white px-8 py-3 rounded-full text-sm font-bold shadow-md hover:shadow-lg transition">
                    Sewa Sekarang
                </a>
            </div>
        </div>
        
        <!-- BOTTOM NAV MOBILE (WARNA KHAS RENTIFY, RAMPING & DEKAT SISI BAWAH) -->
        <div class="fixed bottom-2 left-0 w-full z-50 flex justify-center pointer-events-none px-4">
            <nav class="rentify-card backdrop-blur-xl border border-white/60 pointer-events-auto px-6 py-1.5 rounded-full flex justify-between items-center gap-7 shadow-lg shadow-sky-900/10">
                <a href="{{ route('customer.home') }}" class="flex flex-col items-center text-slate-400 hover:text-sky-500 transition-colors px-1 py-0.5">
                    <i class="fa-solid fa-house text-[16px] mb-0.5"></i><span class="text-[8.5px] font-bold tracking-tight">Beranda</span>
                </a>
                <a href="{{ route('customer.wishlist') }}" class="flex flex-col items-center text-slate-400 hover:text-sky-500 transition-colors px-1 py-0.5">
                    <i class="fa-solid fa-heart text-[16px] mb-0.5"></i><span class="text-[8.5px] font-bold tracking-tight">Favorit</span>
                </a>
                <a href="{{ route('customer.dashboard') }}" class="flex flex-col items-center text-sky-600 px-1 py-0.5">
                    <i class="fa-solid fa-user text-[16px] mb-0.5"></i><span class="text-[8.5px] font-black tracking-tight">Akun</span>
                </a>
            </nav>
        </div>

    </div>

</body>
</html>