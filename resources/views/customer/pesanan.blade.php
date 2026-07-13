<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Transaksi - Rentify</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-slate-50 min-h-screen pb-24 text-slate-700">

    <div class="max-w-md mx-auto bg-slate-50 min-h-screen relative shadow-md">
        
        <div class="bg-gradient-to-r from-sky-400 to-[#0369a1] sticky top-0 z-50 shadow-md px-4 py-4 flex items-center gap-4">
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
        
        <nav class="fixed bottom-0 left-0 w-full bg-white shadow-[0_-4px_10px_rgba(0,0,0,0.05)] rounded-t-2xl z-50">
            <div class="max-w-md mx-auto flex justify-between items-center px-8 py-3">
                <a href="{{ route('customer.home') }}" class="flex flex-col items-center text-slate-400 hover:text-ocean-dark transition">
                    <i class="fa-solid fa-house text-xl mb-1"></i>
                    <span class="text-[10px] font-semibold">Beranda</span>
                </a>
                <a href="{{ route('customer.wishlist') }}" class="flex flex-col items-center text-slate-400 hover:text-pink-500 transition">
                    <i class="fa-solid fa-heart text-xl mb-1"></i>
                    <span class="text-[10px] font-semibold">Favorit</span>
                </a>
                <a href="{{ route('customer.dashboard') }}" class="flex flex-col items-center text-[#0369a1]">
                    <i class="fa-solid fa-user text-xl mb-1"></i>
                    <span class="text-[10px] font-bold">Akun</span>
                </a>
            </div>
        </nav>

    </div>

</body>
</html>