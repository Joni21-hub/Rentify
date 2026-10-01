<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Pengaturan Akun - Rentify</title>
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
<body class="min-h-screen text-slate-800 pb-12 bg-flowing overflow-x-hidden w-full max-w-full">

    <div class="max-w-md mx-auto min-h-screen relative pb-8">

        <!-- HEADER NAVBAR -->
        <header class="rentify-navbar sticky top-0 z-50 px-5 py-3.5 flex items-center justify-between shadow-sm">
            <a href="{{ route('customer.dashboard') }}" class="w-9 h-9 flex items-center justify-center rounded-xl bg-white/70 hover:bg-white text-slate-600 hover:text-sky-600 transition shadow-sm">
                <i class="fa-solid fa-arrow-left text-sm"></i>
            </a>
            <h1 class="text-base font-black text-slate-800 flex-1 text-center tracking-tight pr-9">Pengaturan Akun</h1>
        </header>

        <div class="px-4 pt-4">
            <!-- KARTU PROFIL HEADER -->
            <div class="rentify-card p-4 rounded-3xl shadow-sm flex items-center gap-4 mb-4">
                <!-- Foto Profil / Inisial -->
                @if($user->foto_profil)
                    <img src="{{ str_starts_with($user->foto_profil, 'http') ? $user->foto_profil : Storage::url($user->foto_profil) }}" alt="Foto"
                         class="w-14 h-14 rounded-full object-cover ring-2 ring-sky-200 flex-shrink-0 shadow-sm">
                @else
                    <div class="w-14 h-14 rounded-full bg-gradient-to-br from-sky-400 to-sky-600 flex items-center justify-center flex-shrink-0 ring-2 ring-sky-200 shadow-sm">
                        <span class="text-white font-extrabold text-xl">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                    </div>
                @endif

                <div class="flex-1 min-w-0">
                    <p class="font-black text-slate-800 text-sm truncate leading-tight">{{ $user->name }}</p>
                    
                    <!-- Kontak Utama (No WA atau Email Asli, bukan dummy) -->
                    <p class="text-slate-500 text-xs truncate mt-0.5 font-medium flex items-center gap-1.5">
                        @if($user->hasRealEmail())
                            <i class="fa-solid fa-envelope text-sky-500 text-[10px]"></i>
                        @else
                            <i class="fa-brands fa-whatsapp text-emerald-500 text-[11px]"></i>
                        @endif
                        <span>{{ $user->display_contact }}</span>
                    </p>

                    <!-- Badge Status Verifikasi WhatsApp -->
                    <div class="flex items-center gap-1.5 mt-1.5">
                        @if($user->whatsapp)
                            <span class="inline-flex items-center gap-1 text-[9px] font-bold {{ $user->whatsapp_verified_at ? 'bg-emerald-100 text-emerald-700 border border-emerald-300' : 'bg-amber-100 text-amber-700 border border-amber-300' }} rounded-full px-2 py-0.5">
                                <i class="fa-brands fa-whatsapp"></i>
                                {{ $user->whatsapp_verified_at ? 'WA Terverifikasi' : 'WA Belum Verifikasi' }}
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Notifikasi Flash Message -->
            @if(session('success'))
                <div class="mb-4 p-3.5 rounded-2xl bg-emerald-100/90 border border-emerald-300 text-emerald-800 text-xs font-bold flex items-center gap-2 shadow-sm">
                    <i class="fa-solid fa-circle-check text-emerald-600 text-sm"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif
            @if(session('error'))
                <div class="mb-4 p-3.5 rounded-2xl bg-rose-100/90 border border-rose-300 text-rose-800 text-xs font-bold flex items-center gap-2 shadow-sm">
                    <i class="fa-solid fa-triangle-exclamation text-rose-600 text-sm"></i>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            <!-- SEKSI: PENGATURAN AKUN -->
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1 mb-2">Pengaturan Akun</p>

            <div class="rentify-card rounded-2xl overflow-hidden divide-y divide-slate-100/80 mb-4 shadow-sm">

                <!-- Ubah Profil -->
                <a href="{{ route('customer.settings.profile') }}" class="flex items-center justify-between p-3.5 hover:bg-white/60 transition group">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 bg-sky-100 text-sky-600 rounded-xl flex items-center justify-center flex-shrink-0 group-hover:scale-105 transition shadow-sm">
                            <i class="fa-solid fa-user-pen text-sm"></i>
                        </div>
                        <div>
                            <span class="block text-xs font-bold text-slate-800">Ubah Profil</span>
                            <span class="block text-[10px] text-slate-400 font-medium">Nama dan foto akun</span>
                        </div>
                    </div>
                    <i class="fa-solid fa-chevron-right text-slate-300 text-xs group-hover:text-sky-500 transition"></i>
                </a>

                <!-- Ubah Nomor WA -->
                <a href="{{ route('customer.settings.whatsapp') }}" class="flex items-center justify-between p-3.5 hover:bg-white/60 transition group">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 bg-emerald-100 text-emerald-600 rounded-xl flex items-center justify-center flex-shrink-0 group-hover:scale-105 transition shadow-sm">
                            <i class="fa-brands fa-whatsapp text-base"></i>
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

                <!-- Alamat Email (Solusi Marketplace: Cek Email Riil) -->
                <a href="{{ route('customer.settings.email') }}" class="flex items-center justify-between p-3.5 hover:bg-white/60 transition group">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 bg-amber-100 text-amber-600 rounded-xl flex items-center justify-center flex-shrink-0 group-hover:scale-105 transition shadow-sm">
                            <i class="fa-solid fa-envelope text-sm"></i>
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
                            <span class="text-[9px] font-extrabold bg-sky-100 text-sky-600 px-2.5 py-0.5 rounded-full border border-sky-200 group-hover:bg-sky-500 group-hover:text-white transition shadow-sm">
                                + Tambahkan
                            </span>
                        @endif
                        <i class="fa-solid fa-chevron-right text-slate-300 text-xs group-hover:text-sky-500 transition"></i>
                    </div>
                </a>

                <!-- Keamanan Akun -->
                <a href="{{ route('customer.settings.password') }}" class="flex items-center justify-between p-3.5 hover:bg-white/60 transition group">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 bg-rose-100 text-rose-600 rounded-xl flex items-center justify-center flex-shrink-0 group-hover:scale-105 transition shadow-sm">
                            <i class="fa-solid fa-lock text-sm"></i>
                        </div>
                        <div>
                            <span class="block text-xs font-bold text-slate-800">Keamanan Akun</span>
                            <span class="block text-[10px] text-slate-400 font-medium">
                                @if($user->google_id && !$user->password_changed_at)
                                    Atur sandi untuk login manual
                                @else
                                    Ubah kata sandi akun
                                @endif
                            </span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        @if($user->google_id && !$user->password_changed_at)
                            <span class="text-[9px] font-bold bg-amber-100 text-amber-600 rounded-full px-2 py-0.5">Belum diatur</span>
                        @endif
                        <i class="fa-solid fa-chevron-right text-slate-300 text-xs group-hover:text-rose-500 transition"></i>
                    </div>
                </a>

                <!-- Alamat Pengiriman -->
                <a href="{{ route('customer.lokasi') }}" class="flex items-center justify-between p-3.5 hover:bg-white/60 transition group">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 bg-purple-100 text-purple-600 rounded-xl flex items-center justify-center flex-shrink-0 group-hover:scale-105 transition shadow-sm">
                            <i class="fa-solid fa-map-location-dot text-sm"></i>
                        </div>
                        <div>
                            <span class="block text-xs font-bold text-slate-800">Alamat Pengiriman</span>
                            <span class="block text-[10px] text-slate-400 font-medium">
                                @if($user->alamat_lengkap)
                                    {{ Str::limit($user->alamat_lengkap, 28) }}
                                @else
                                    Atur lokasi pengiriman barang
                                @endif
                            </span>
                        </div>
                    </div>
                    <i class="fa-solid fa-chevron-right text-slate-300 text-xs group-hover:text-purple-500 transition"></i>
                </a>
            </div>

            <!-- SEKSI: BANTUAN -->
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1 mb-2">Bantuan & Legal</p>

            <div class="rentify-card rounded-2xl overflow-hidden divide-y divide-slate-100/80 mb-6 shadow-sm">
                <!-- Pusat Bantuan -->
                @php $linkWaAdmin = "https://wa.me/6281262364197?text=" . urlencode("Halo admin Rentify, saya butuh bantuan terkait akun saya."); @endphp
                <a href="{{ $linkWaAdmin }}" target="_blank" rel="noopener noreferrer" class="flex items-center justify-between p-3.5 hover:bg-white/60 transition group">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 bg-emerald-100 text-emerald-600 rounded-xl flex items-center justify-center flex-shrink-0 group-hover:scale-105 transition shadow-sm">
                            <i class="fa-brands fa-whatsapp text-base"></i>
                        </div>
                        <div>
                            <span class="block text-xs font-bold text-slate-800">Pusat Bantuan WhatsApp</span>
                            <span class="block text-[10px] text-slate-400 font-medium">Chat Customer Service (24/7)</span>
                        </div>
                    </div>
                    <i class="fa-solid fa-arrow-up-right-from-square text-slate-300 text-xs group-hover:text-emerald-500 transition"></i>
                </a>

                <!-- Kebijakan Privasi -->
                <a href="{{ route('customer.dashboard') }}" class="flex items-center justify-between p-3.5 hover:bg-white/60 transition group">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 bg-slate-100 text-slate-600 rounded-xl flex items-center justify-center flex-shrink-0 group-hover:scale-105 transition shadow-sm">
                            <i class="fa-solid fa-shield-halved text-sm"></i>
                        </div>
                        <div>
                            <span class="block text-xs font-bold text-slate-800">Kebijakan Privasi & Syarat</span>
                            <span class="block text-[10px] text-slate-400 font-medium">Ketentuan penggunaan platform</span>
                        </div>
                    </div>
                    <i class="fa-solid fa-chevron-right text-slate-300 text-xs group-hover:text-slate-500 transition"></i>
                </a>
            </div>

            <!-- TOMBOL KELUAR AKUN (STANDAR MASTER THEME) -->
            <form action="{{ route('logout') }}" method="POST" class="w-full flex justify-center mb-6">
                @csrf
                <button type="submit"
                        onclick="return confirm('Yakin ingin keluar dari akun Rentify?')"
                        class="rentify-btn-danger w-full py-3.5 px-6 rounded-2xl text-xs font-black tracking-widest uppercase flex items-center justify-center gap-2 shadow-md transition-all transform hover:-translate-y-0.5 active:translate-y-0">
                    <i class="fa-solid fa-power-off text-sm"></i>
                    <span>KELUAR AKUN</span>
                </button>
            </form>

            <p class="text-center text-[10px] font-bold text-slate-400 pb-4 tracking-wider">Rentify Platform v1.0</p>
        </div>
    </div>
</body>
</html>