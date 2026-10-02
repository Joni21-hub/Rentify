<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Favorit - Rentify</title>
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

<body class="min-h-screen pb-24 text-slate-800 bg-flowing overflow-x-hidden w-full max-w-full">

    <div class="max-w-md mx-auto min-h-screen relative pb-16">
        
        <!-- TOP HEADER -->
        <header class="rentify-navbar sticky top-0 z-40 px-5 py-3.5 flex items-center justify-between shadow-sm">
            <a href="{{ route('customer.home') }}" class="w-9 h-9 rounded-xl bg-white/60 hover:bg-white flex items-center justify-center text-slate-600 hover:text-sky-600 transition shadow-sm">
                <i class="fa-solid fa-arrow-left text-sm"></i>
            </a>
            <h1 class="text-base font-black text-slate-800 flex-1 text-center tracking-tight pr-9">Favorit</h1>
        </header>

        <main class="p-4">
            @if(isset($wishlists) && $wishlists->count() > 0)
                <div class="grid grid-cols-2 gap-3.5">
                    @foreach($wishlists as $fav)
                    <div class="rentify-card relative rounded-2xl overflow-hidden transition-all transform hover:-translate-y-1 shadow-sm flex flex-col justify-between">
                        <a href="{{ url('/customer/barang/' . ($fav->barang->slug ?? $fav->barang->id)) }}" class="block p-2.5">
                            <div class="relative h-32 bg-slate-100 rounded-xl mb-2.5 flex items-center justify-center overflow-hidden shadow-inner">
                                @if($fav->barang->cover_photo)
                                    <img src="{{ asset(str_replace('public/', '', $fav->barang->cover_photo)) }}" class="w-full h-full object-cover">
                                @else
                                    <i class="fa-solid fa-box text-slate-300 text-3xl"></i>
                                @endif
                            </div>
                            <h4 class="text-xs font-bold text-slate-800 leading-snug mb-1 line-clamp-2">{{ $fav->barang->nama }}</h4>
                            <div class="text-sky-600 font-black text-sm">
                                Rp{{ number_format($fav->barang->harga_sewa_customer, 0, ',', '.') }}<span class="text-[9px] text-slate-400 font-medium">/hari</span>
                            </div>
                        </a>
                        
                        <form action="{{ route('customer.wishlist.toggle', $fav->barang->id) }}" method="POST" class="absolute top-4 right-4 z-10">
                            @csrf
                            <button type="submit" class="w-7 h-7 rounded-full bg-white/90 backdrop-blur-md flex items-center justify-center text-rose-500 hover:scale-110 shadow transition" title="Hapus dari Favorit">
                                <i class="fa-solid fa-heart text-xs"></i>
                            </button>
                        </form>
                    </div>
                    @endforeach
                </div>
            @else
                <div class="flex flex-col items-center justify-center py-28 text-slate-400 text-center px-4">
                    <div class="w-20 h-20 bg-sky-100/80 rounded-3xl flex items-center justify-center mb-4 shadow-sm">
                        <i class="fa-solid fa-heart-crack text-4xl text-sky-400"></i>
                    </div>
                    <h3 class="font-black text-slate-700 text-base mb-1">Belum Ada Favorit</h3>
                    <p class="text-xs font-medium text-slate-500 mb-6 max-w-xs">Kamu belum menambahkan barang apapun ke daftar favoritmu.</p>
                    <a href="{{ route('customer.home') }}" class="rentify-btn px-8 py-3.5 rounded-2xl text-xs uppercase tracking-widest font-extrabold inline-flex items-center gap-2 shadow-md">
                        <span>Cari Barang</span>
                        <i class="fa-solid fa-arrow-right text-xs"></i>
                    </a>
                </div>
            @endif
        </main>
        
        <!-- BOTTOM NAV MOBILE (WARNA KHAS RENTIFY, RAMPING & DEKAT SISI BAWAH) -->
        <div class="fixed bottom-2 left-0 w-full z-50 flex justify-center pointer-events-none px-4">
            <nav class="rentify-card backdrop-blur-xl border border-white/60 pointer-events-auto px-6 py-1.5 rounded-full flex justify-between items-center gap-7 shadow-lg shadow-sky-900/10">
                <a href="{{ route('customer.home') }}" class="flex flex-col items-center text-slate-400 hover:text-sky-500 transition-colors px-1 py-0.5">
                    <i class="fa-solid fa-house text-[16px] mb-0.5"></i><span class="text-[8.5px] font-bold tracking-tight">Beranda</span>
                </a>
                <a href="{{ route('customer.wishlist') }}" class="flex flex-col items-center text-sky-600 px-1 py-0.5">
                    <i class="fa-solid fa-heart text-[16px] mb-0.5"></i><span class="text-[8.5px] font-black tracking-tight">Favorit</span>
                </a>
                <a href="{{ route('customer.dashboard') }}" class="flex flex-col items-center text-slate-400 hover:text-sky-500 transition-colors px-1 py-0.5">
                    <i class="fa-solid fa-user text-[16px] mb-0.5"></i><span class="text-[8.5px] font-bold tracking-tight">Akun</span>
                </a>
            </nav>
        </div>

    </div>

</body>
</html>