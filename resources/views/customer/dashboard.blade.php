<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Akun Saya - Rentify</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-slate-50 min-h-screen pb-24 text-slate-700">

    <div class="max-w-md mx-auto bg-slate-50 min-h-screen relative shadow-md">
        <div class="bg-gradient-to-br from-sky-400 to-[#0369a1] px-4 pt-10 pb-12 rounded-b-[40px] shadow-md text-white text-center relative">
            <h1 class="text-lg font-bold mb-6">Akun Saya</h1>
            <div class="flex flex-col items-center">
                <div class="w-24 h-24 bg-white rounded-full flex items-center justify-center text-[#0369a1] text-4xl font-black shadow-lg mb-4 border-4 border-sky-100">
                    {{ substr(Auth::user()->nama ?? 'C', 0, 1) }}
                </div>
                <h2 class="text-xl font-black">{{ Auth::user()->nama ?? 'Customer' }}</h2>
                <p class="text-sm text-sky-100 mt-1">{{ Auth::user()->email ?? 'customer@email.com' }}</p>
            </div>
        </div>

        <div class="px-5 mt-[-20px] relative z-10 space-y-4">
            <a href="{{ route('customer.pesanan') }}" class="flex items-center justify-between bg-white p-4 rounded-2xl shadow-sm border border-slate-100 hover:border-sky-300 transition group">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-sky-50 text-sky-500 rounded-full flex items-center justify-center">
                        <i class="fa-solid fa-clock-rotate-left text-lg"></i>
                    </div>
                    <div>
                        <span class="block font-bold text-slate-700">Riwayat Transaksi</span>
                        <span class="block text-xs text-slate-400 mt-0.5">Pantau pesanan & sewaanmu</span>
                    </div>
                </div>
                <i class="fa-solid fa-chevron-right text-slate-300"></i>
            </a>

            <form action="/logout" method="POST" class="w-full">
                @csrf
                <button type="submit" class="w-full flex items-center bg-white p-4 rounded-2xl shadow-sm border border-slate-100 hover:border-red-300 hover:bg-red-50 transition group gap-4">
                    <div class="w-12 h-12 bg-red-50 text-red-500 rounded-full flex items-center justify-center">
                        <i class="fa-solid fa-arrow-right-from-bracket text-lg"></i>
                    </div>
                    <span class="font-bold text-red-500">Keluar Akun</span>
                </button>
            </form>
        </div>
        
        <nav class="fixed bottom-0 left-0 w-full bg-white shadow-[0_-4px_10px_rgba(0,0,0,0.05)] rounded-t-2xl z-50">
            <div class="max-w-md mx-auto flex justify-between items-center px-8 py-3">
                <a href="{{ route('customer.home') }}" class="flex flex-col items-center text-slate-400"><i class="fa-solid fa-house text-xl mb-1"></i><span class="text-[10px] font-semibold">Beranda</span></a>
                <a href="{{ route('customer.wishlist') }}" class="flex flex-col items-center text-slate-400 hover:text-pink-500 transition"><i class="fa-solid fa-heart text-xl mb-1"></i><span class="text-[10px] font-semibold">Favorit</span></a>
                <a href="#" class="flex flex-col items-center text-[#0369a1]"><i class="fa-solid fa-user text-xl mb-1"></i><span class="text-[10px] font-bold">Akun</span></a>
            </div>
        </nav>
    </div>
</body>
</html>