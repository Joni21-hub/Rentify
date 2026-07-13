<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Favorit Saya - Rentify</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'ocean-light': '#e0f2fe', 
                        'ocean-mid': '#bae6fd',   
                        'ocean-dark': '#0369a1',  
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-slate-50 min-h-screen pb-24 text-slate-700">

    <div class="max-w-md mx-auto bg-slate-50 min-h-screen relative shadow-md">
        
        <div class="bg-gradient-to-r from-sky-400 to-[#0369a1] sticky top-0 z-50 shadow-md px-4 py-4 flex items-center gap-4">
            <h1 class="text-lg font-bold text-white flex-1 text-center">Favorit Saya</h1>
        </div>

        <div class="p-4">
            @if(isset($wishlists) && $wishlists->count() > 0)
                <div class="grid grid-cols-2 gap-3">
                    
                    @foreach($wishlists as $fav)
                    <div class="relative block bg-white rounded-xl shadow-sm border border-sky-50 hover:shadow-md transition">
                        <a href="{{ url('/customer/barang/' . ($fav->barang->slug ?? $fav->barang->id)) }}" class="block p-2">
                            <div class="relative h-28 bg-slate-100 rounded-lg mb-2 flex items-center justify-center overflow-hidden">
                                @if($fav->barang->cover_photo)
                                    <img src="{{ asset(str_replace('public/', '', $fav->barang->cover_photo)) }}" class="w-full h-full object-cover">
                                @else
                                    <i class="fa-solid fa-box text-slate-300 text-3xl"></i>
                                @endif
                            </div>
                            <h4 class="text-sm font-bold text-slate-700 leading-tight mb-1 truncate">{{ $fav->barang->nama }}</h4>
                            <p class="text-ocean-dark font-bold text-sm">Rp {{ number_format($fav->barang->harga_sewa_customer, 0, ',', '.') }}</p>
                        </a>
                        
                        <form action="{{ route('customer.wishlist.toggle', $fav->barang->id) }}" method="POST" class="absolute top-3 right-3 z-10">
                            @csrf
                            <button type="submit" class="w-7 h-7 bg-white/80 backdrop-blur-md rounded-full flex items-center justify-center text-pink-500 hover:text-slate-300 shadow-sm transition">
                                <i class="fa-solid fa-heart text-xs"></i>
                            </button>
                        </form>
                    </div>
                    @endforeach

                </div>
            @else
                <div class="flex flex-col items-center justify-center py-32 text-slate-400 text-center">
                    <div class="w-24 h-24 bg-sky-50 rounded-full flex items-center justify-center mb-4">
                        <i class="fa-solid fa-heart-crack text-5xl text-sky-300"></i>
                    </div>
                    <h3 class="font-bold text-slate-600 mb-1">Belum ada favorit</h3>
                    <p class="text-sm font-medium text-slate-500 mb-6 px-4">Kamu belum menambahkan barang apapun ke daftar favoritmu.</p>
                    <a href="{{ route('customer.home') }}" class="bg-gradient-to-r from-sky-400 to-[#0369a1] text-white px-8 py-3 rounded-full text-sm font-bold shadow-md hover:shadow-lg transition">
                        Cari Barang
                    </a>
                </div>
            @endif
        </div>
        
        <nav class="fixed bottom-0 left-0 w-full bg-white shadow-[0_-4px_10px_rgba(0,0,0,0.05)] rounded-t-2xl z-50">
            <div class="max-w-md mx-auto flex justify-between items-center px-8 py-3">
                
                <a href="{{ route('customer.home') }}" class="flex flex-col items-center text-slate-400 hover:text-ocean-dark transition">
                    <i class="fa-solid fa-house text-xl mb-1"></i>
                    <span class="text-[10px] font-semibold">Beranda</span>
                </a>
                
                <a href="#" class="flex flex-col items-center text-pink-500">
                    <i class="fa-solid fa-heart text-xl mb-1"></i>
                    <span class="text-[10px] font-bold">Favorit</span>
                </a>

                <a href="{{ route('customer.dashboard') }}" class="flex flex-col items-center text-slate-400 hover:text-ocean-dark transition">
                    <i class="fa-solid fa-user text-xl mb-1"></i>
                    <span class="text-[10px] font-semibold">Akun</span>
                </a>

            </div>
        </nav>

    </div>

</body>
</html>