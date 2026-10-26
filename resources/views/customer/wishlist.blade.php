<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Barang Favorit — Rentify</title>
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

<body class="min-h-screen pb-28 text-slate-800 bg-flowing overflow-x-hidden w-full max-w-full">

    <div class="max-w-md mx-auto min-h-screen relative pb-8">
        
        <!-- TOP HEADER -->
        <header class="rentify-navbar sticky top-0 z-40 px-4 py-3.5 flex items-center justify-between bg-white/80 backdrop-blur-md border-b border-white/60 shadow-xs">
            <a href="{{ route('customer.dashboard') }}" class="w-8 h-8 rounded-xl bg-white/80 hover:bg-white text-slate-700 flex items-center justify-center shadow-xs border border-white/80 transition" aria-label="Kembali ke Profil">
                <i class="fa-solid fa-arrow-left text-xs text-slate-600"></i>
            </a>
            <h1 class="text-base font-black text-slate-900 flex-1 text-center pr-8 tracking-tight">Barang Favorit</h1>
        </header>

        <main class="p-4 space-y-4">
            @if(isset($wishlists) && $wishlists->count() > 0)
                <div class="grid grid-cols-2 gap-3">
                    @foreach($wishlists as $fav)
                    <div class="rentify-card relative rounded-2xl overflow-hidden shadow-xs hover:shadow-md transition flex flex-col justify-between">
                        <a href="{{ url('/customer/barang/' . ($fav->barang->slug ?? $fav->barang->id)) }}" class="block p-2.5 space-y-2">
                            <div class="relative aspect-square bg-slate-100 rounded-xl overflow-hidden shadow-inner">
                                @if($fav->barang && $fav->barang->cover_photo)
                                    <img src="{{ str_starts_with((string)$fav->barang->cover_photo, 'http') ? $fav->barang->cover_photo : Storage::url((string)$fav->barang->cover_photo) }}" alt="{{ $fav->barang->nama }}" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-slate-300">
                                        <i class="fa-solid fa-box text-2xl"></i>
                                    </div>
                                @endif
                            </div>
                            <h4 class="text-xs font-bold text-slate-800 leading-snug line-clamp-2">{{ $fav->barang->nama }}</h4>
                            <div class="text-sky-600 font-black text-xs">
                                Rp {{ number_format($fav->barang->harga_sewa_customer ?? $fav->barang->harga_sewa_harian ?? 0, 0, ',', '.') }}<span class="text-[9px] text-slate-400 font-normal">/hari</span>
                            </div>
                        </a>
                        
                        <form action="{{ route('customer.wishlist.toggle', $fav->barang->id) }}" method="POST" class="absolute top-4 right-4 z-10">
                            @csrf
                            <button type="submit" class="w-7 h-7 rounded-full bg-white/90 backdrop-blur-md flex items-center justify-center text-rose-500 hover:scale-110 shadow-sm transition" title="Hapus dari Favorit">
                                <i class="fa-solid fa-heart text-xs"></i>
                            </button>
                        </form>
                    </div>
                    @endforeach
                </div>
            @else
                <div class="rentify-card p-10 rounded-3xl flex flex-col items-center justify-center text-center shadow-xs my-8 space-y-3">
                    <div class="w-16 h-16 bg-rose-50 border border-rose-100 rounded-full flex items-center justify-center text-2xl text-rose-500 shadow-inner">
                        <i class="fa-solid fa-heart"></i>
                    </div>
                    <div class="space-y-1">
                        <h3 class="font-black text-slate-800 text-base">Belum Ada Favorit</h3>
                        <p class="text-xs text-slate-500 max-w-xs mx-auto">Tandai barang rental yang kamu sukai dengan ikon hati agar mudah ditemukan kembali.</p>
                    </div>
                    <div class="pt-2">
                        <a href="{{ route('customer.home') }}" class="inline-flex items-center gap-2 px-6 py-3 rounded-2xl bg-gradient-to-br from-sky-400 to-sky-600 hover:from-sky-500 hover:to-sky-700 text-white font-extrabold uppercase tracking-wider text-xs shadow-md transition transform hover:-translate-y-0.5 active:translate-y-0">
                            <span>Cari Barang</span>
                            <i class="fa-solid fa-arrow-right text-xs"></i>
                        </a>
                    </div>
                </div>
            @endif
        </main>
        
        <!-- BOTTOM NAV MOBILE (3 TAB RAMPING) -->
        <x-bottom-navigation-bar active="akun" />

    </div>

</body>
</html>
