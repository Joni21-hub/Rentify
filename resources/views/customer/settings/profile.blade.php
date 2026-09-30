<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Ubah Profil - Rentify</title>
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
<body class="min-h-screen text-slate-800 pb-16 bg-flowing overflow-x-hidden w-full max-w-full">

    <div class="max-w-md mx-auto min-h-screen relative pb-8">

        <!-- HEADER -->
        <header class="rentify-navbar sticky top-0 z-50 px-5 py-3.5 flex items-center justify-between shadow-sm">
            <a href="{{ route('customer.settings') }}" class="w-9 h-9 flex items-center justify-center rounded-xl bg-white/70 hover:bg-white text-slate-600 hover:text-sky-600 transition shadow-sm">
                <i class="fa-solid fa-arrow-left text-sm"></i>
            </a>
            <h1 class="text-base font-black text-slate-800 flex-1 text-center tracking-tight pr-9">Profil Saya</h1>
        </header>

        <div class="px-4 py-6">

            @if(session('error'))
                <div class="mb-5 p-3.5 rounded-2xl bg-rose-100 border border-rose-300 text-rose-800 text-xs font-bold flex items-center gap-2 shadow-sm">
                    <i class="fa-solid fa-triangle-exclamation text-rose-600 text-sm"></i>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            @if(session('success'))
                <div class="mb-5 p-3.5 rounded-2xl bg-emerald-100 border border-emerald-300 text-emerald-800 text-xs font-bold flex items-center gap-2 shadow-sm">
                    <i class="fa-solid fa-circle-check text-emerald-600 text-sm"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if($errors->any())
                <div class="mb-5 p-3.5 rounded-2xl bg-rose-100 border border-rose-300 text-rose-800 text-xs font-bold shadow-sm">
                    <ul class="list-disc pl-5 space-y-1">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('customer.settings.profile.update') }}" method="POST" enctype="multipart/form-data" class="rentify-card p-6 rounded-3xl shadow-sm space-y-6">
                @csrf

                <!-- FOTO PROFIL -->
                <div class="flex flex-col items-center">
                    <div class="relative group cursor-pointer mb-2">
                        <div class="w-24 h-24 rounded-full overflow-hidden border-4 border-white shadow-md bg-sky-100 flex items-center justify-center text-sky-600 text-3xl font-black relative">
                            @if($user->foto_profil)
                                <img src="{{ str_starts_with($user->foto_profil, 'http') ? $user->foto_profil : Storage::url($user->foto_profil) }}" alt="Foto" class="w-full h-full object-cover">
                            @else
                                {{ substr($user->name ?? 'C', 0, 1) }}
                            @endif
                            <div class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition rounded-full">
                                <i class="fa-solid fa-camera text-white text-xl"></i>
                            </div>
                        </div>
                        <input type="file" name="foto_profil" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" accept="image/*">
                    </div>
                    <span class="text-xs text-slate-500 font-medium">Ketuk untuk mengganti foto profil</span>
                </div>

                <!-- NAMA -->
                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1.5 ml-1">Nama Lengkap</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                           class="rentify-input input-glass w-full px-4 py-3.5 rounded-2xl font-bold text-sm focus:outline-none transition">
                </div>

                <!-- SIMPAN -->
                <div class="pt-2">
                    <button type="submit" class="rentify-btn w-full py-3.5 rounded-2xl text-xs font-black tracking-widest uppercase shadow-md transition-all transform hover:-translate-y-0.5 active:translate-y-0">
                        Simpan Profil
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>