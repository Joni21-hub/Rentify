<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Pengaturan Akun — Rentify</title>
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
<body class="min-h-screen text-slate-800 pb-28 bg-flowing overflow-x-hidden w-full max-w-full">

    <div class="max-w-md mx-auto min-h-screen relative pb-8">

        <!-- HEADER NAVBAR -->
        <header class="rentify-navbar sticky top-0 z-40 px-4 py-3.5 flex items-center justify-between bg-white/80 backdrop-blur-md border-b border-white/60 shadow-xs">
            <a href="{{ route('customer.dashboard') }}" class="w-8 h-8 flex items-center justify-center rounded-xl bg-white/80 hover:bg-white text-slate-700 hover:text-sky-600 transition shadow-xs border border-white/80" aria-label="Kembali ke Profil">
                <i class="fa-solid fa-arrow-left text-xs"></i>
            </a>
            <h1 class="text-base font-black text-slate-900 flex-1 text-center tracking-tight pr-8">Pengaturan Akun</h1>
        </header>

        <div class="px-4 pt-4 space-y-4">
            <!-- KARTU PROFIL HEADER -->
            <div class="rentify-card p-4 rounded-3xl shadow-xs flex items-center gap-3.5">
                @if($user->foto_profil)
                    <img src="{{ str_starts_with((string)$user->foto_profil, 'http') ? $user->foto_profil : Storage::url((string)$user->foto_profil) }}" alt="Foto"
                         class="w-13 h-13 rounded-full object-cover ring-2 ring-sky-300 flex-shrink-0 shadow-xs" style="width: 52px; height: 52px;">
                @else
                    <div class="w-13 h-13 rounded-full bg-gradient-to-br from-sky-400 to-sky-600 flex items-center justify-center flex-shrink-0 ring-2 ring-sky-200 shadow-xs" style="width: 52px; height: 52px;">
                        <span class="text-white font-black text-lg">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                    </div>
                @endif

                <div class="flex-1 min-w-0">
                    <p class="font-black text-slate-900 text-sm truncate leading-tight">{{ $user->name }}</p>
                    <p class="text-slate-500 text-xs truncate mt-0.5 font-medium flex items-center gap-1.5">
                        @if($user->hasRealEmail())
                            <i class="fa-solid fa-envelope text-sky-500 text-[10px]"></i>
                        @else
                            <i class="fa-brands fa-whatsapp text-emerald-500 text-[11px]"></i>
                        @endif
                        <span>{{ $user->display_contact }}</span>
                    </p>
                    @if($user->whatsapp)
                        <div class="mt-1">
                            <span class="inline-flex items-center gap-1 text-[9px] font-bold {{ $user->whatsapp_verified_at ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-amber-50 text-amber-700 border border-amber-200' }} rounded-full px-2 py-0.5">
                                <i class="fa-brands fa-whatsapp"></i>
                                {{ $user->whatsapp_verified_at ? 'WA Terverifikasi' : 'WA Belum Verifikasi' }}
                            </span>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Flash Messages -->
            @if(session('success'))
                <div class="p-3.5 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold flex items-center gap-2 shadow-xs">
                    <i class="fa-solid fa-circle-check text-emerald-600 text-sm"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif
            @if(session('error'))
                <div class="p-3.5 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-bold flex items-center gap-2 shadow-xs">
                    <i class="fa-solid fa-triangle-exclamation text-rose-600 text-sm"></i>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            <!-- KATEGORI 1: PROFIL & KONTAK -->
            <div>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1 mb-1.5">Profil & Kontak</p>
                <div class="rentify-card rounded-2xl overflow-hidden divide-y divide-slate-100 shadow-xs">
                    <!-- Ubah Profil -->
                    <a href="{{ route('customer.settings.profile') }}" class="flex items-center justify-between p-3.5 hover:bg-slate-50/60 transition group">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-sky-100 text-sky-600 rounded-xl flex items-center justify-center flex-shrink-0 group-hover:scale-105 transition shadow-xs">
                                <i class="fa-solid fa-user-pen text-xs"></i>
                            </div>
                            <div>
                                <span class="block text-xs font-bold text-slate-800">Ubah Profil</span>
                                <span class="block text-[10px] text-slate-400 font-medium">Nama dan foto akun</span>
                            </div>
                        </div>
                        <i class="fa-solid fa-chevron-right text-slate-300 text-xs group-hover:text-sky-500 transition"></i>
                    </a>

                    <!-- Ubah Nomor WA -->
                    <a href="{{ route('customer.settings.whatsapp') }}" class="flex items-center justify-between p-3.5 hover:bg-slate-50/60 transition group">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-emerald-100 text-emerald-600 rounded-xl flex items-center justify-center flex-shrink-0 group-hover:scale-105 transition shadow-xs">
                                <i class="fa-brands fa-whatsapp text-sm"></i>
                            </div>
                            <div>
                                <span class="block text-xs font-bold text-slate-800">Nomor WhatsApp</span>
                                <span class="block text-[10px] text-slate-500 font-medium">{{ $user->whatsapp ?? 'Belum diisi' }}</span>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            @if($user->whatsapp_verified_at)
                                <span class="text-[9px] font-bold bg-emerald-50 text-emerald-600 px-2 py-0.5 rounded-full border border-emerald-200">Aktif</span>
                            @endif
                            <i class="fa-solid fa-chevron-right text-slate-300 text-xs group-hover:text-emerald-500 transition"></i>
                        </div>
                    </a>

                    <!-- Alamat Email -->
                    <a href="{{ route('customer.settings.email') }}" class="flex items-center justify-between p-3.5 hover:bg-slate-50/60 transition group">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-amber-100 text-amber-600 rounded-xl flex items-center justify-center flex-shrink-0 group-hover:scale-105 transition shadow-xs">
                                <i class="fa-solid fa-envelope text-xs"></i>
                            </div>
                            <div>
                                <span class="block text-xs font-bold text-slate-800">Alamat Email</span>
                                @if($user->hasRealEmail())
                                    <span class="block text-[10px] text-slate-500 font-medium truncate max-w-[160px]">{{ $user->email }}</span>
                                @else
                                    <span class="block text-[10px] text-amber-600 font-bold flex items-center gap-1">
                                        <i class="fa-solid fa-circle-exclamation text-[9px]"></i> Belum ditambahkan
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            @if($user->hasRealEmail())
                                <span class="text-[9px] font-bold bg-sky-50 text-sky-600 px-2 py-0.5 rounded-full border border-sky-200">Terverifikasi</span>
                            @else
                                <span class="text-[9px] font-extrabold bg-sky-100 text-sky-700 px-2.5 py-0.5 rounded-full border border-sky-200 group-hover:bg-sky-500 group-hover:text-white transition shadow-xs">
                                    + Tambah
                                </span>
                            @endif
                            <i class="fa-solid fa-chevron-right text-slate-300 text-xs group-hover:text-sky-500 transition"></i>
                        </div>
                    </a>
                </div>
            </div>

            <!-- KATEGORI 2: KEAMANAN & PENGIRIMAN -->
            <div>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1 mb-1.5">Keamanan & Alamat</p>
                <div class="rentify-card rounded-2xl overflow-hidden divide-y divide-slate-100 shadow-xs">
                    <!-- Keamanan Akun -->
                    <a href="{{ route('customer.settings.password') }}" class="flex items-center justify-between p-3.5 hover:bg-slate-50/60 transition group">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-rose-100 text-rose-600 rounded-xl flex items-center justify-center flex-shrink-0 group-hover:scale-105 transition shadow-xs">
                                <i class="fa-solid fa-lock text-xs"></i>
                            </div>
                            <div>
                                <span class="block text-xs font-bold text-slate-800">Keamanan Akun</span>
                                <span class="block text-[10px] text-slate-400 font-medium">
                                    @if($user->google_id && !$user->password_changed_at)
                                        Atur sandi login
                                    @else
                                        Ubah kata sandi akun
                                    @endif
                                </span>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            @if($user->google_id && !$user->password_changed_at)
                                <span class="text-[9px] font-bold bg-amber-100 text-amber-700 rounded-full px-2 py-0.5">Belum diatur</span>
                            @endif
                            <i class="fa-solid fa-chevron-right text-slate-300 text-xs group-hover:text-rose-500 transition"></i>
                        </div>
                    </a>

                    <!-- Alamat Pengiriman -->
                    <a href="{{ route('customer.lokasi') }}" class="flex items-center justify-between p-3.5 hover:bg-slate-50/60 transition group">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-sky-100 text-sky-600 rounded-xl flex items-center justify-center flex-shrink-0 group-hover:scale-105 transition shadow-xs">
                                <i class="fa-solid fa-location-dot text-xs"></i>
                            </div>
                            <div>
                                <span class="block text-xs font-bold text-slate-800">Titik Lokasi Pengiriman</span>
                                <span class="block text-[10px] text-slate-400 font-medium">
                                    @if($user->alamat_lengkap)
                                        {{ Str::limit((string)$user->alamat_lengkap, 28) }}
                                    @else
                                        Atur GPS pengantaran
                                    @endif
                                </span>
                            </div>
                        </div>
                        <i class="fa-solid fa-chevron-right text-slate-300 text-xs group-hover:text-sky-500 transition"></i>
                    </a>
                </div>
            </div>

            <!-- KATEGORI 3: BANTUAN & LEGAL -->
            <div>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1 mb-1.5">Bantuan & Legal</p>
                <div class="rentify-card rounded-2xl overflow-hidden divide-y divide-slate-100 shadow-xs">
                    <!-- Pusat Bantuan -->
                    @php $linkWaAdmin = "https://wa.me/6281262364197?text=" . urlencode("Halo admin Rentify, saya butuh bantuan terkait akun saya."); @endphp
                    <a href="{{ $linkWaAdmin }}" target="_blank" rel="noopener noreferrer" class="flex items-center justify-between p-3.5 hover:bg-slate-50/60 transition group">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-emerald-100 text-emerald-600 rounded-xl flex items-center justify-center flex-shrink-0 group-hover:scale-105 transition shadow-xs">
                                <i class="fa-brands fa-whatsapp text-sm"></i>
                            </div>
                            <div>
                                <span class="block text-xs font-bold text-slate-800">Pusat Bantuan WhatsApp</span>
                                <span class="block text-[10px] text-slate-400 font-medium">Layanan CS 24/7</span>
                            </div>
                        </div>
                        <i class="fa-solid fa-arrow-up-right-from-square text-slate-300 text-xs group-hover:text-emerald-500 transition"></i>
                    </a>

                    <!-- Kebijakan Privasi -->
                    <a href="{{ route('customer.dashboard') }}" class="flex items-center justify-between p-3.5 hover:bg-slate-50/60 transition group">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-slate-100 text-slate-600 rounded-xl flex items-center justify-center flex-shrink-0 group-hover:scale-105 transition shadow-xs">
                                <i class="fa-solid fa-shield-halved text-xs"></i>
                            </div>
                            <div>
                                <span class="block text-xs font-bold text-slate-800">Kebijakan Privasi & Syarat</span>
                                <span class="block text-[10px] text-slate-400 font-medium">Ketentuan penggunaan platform</span>
                            </div>
                        </div>
                        <i class="fa-solid fa-chevron-right text-slate-300 text-xs group-hover:text-slate-500 transition"></i>
                    </a>
                </div>
            </div>

            <!-- TOMBOL KELUAR AKUN -->
            <form action="{{ route('logout') }}" method="POST" class="w-full pt-2">
                @csrf
                <button type="submit"
                        onclick="return confirm('Yakin ingin keluar dari akun Rentify?')"
                        class="w-full py-3.5 px-6 rounded-2xl bg-rose-50 hover:bg-rose-100 border border-rose-200 text-rose-600 font-black text-xs uppercase tracking-wider flex items-center justify-center gap-2 shadow-xs transition">
                    <i class="fa-solid fa-power-off text-xs"></i>
                    <span>Keluar Akun</span>
                </button>
            </form>

            <p class="text-center text-[10px] font-bold text-slate-400 pb-2 tracking-wider">Rentify v1.0</p>
        </div>
    </div>
</body>
</html>
