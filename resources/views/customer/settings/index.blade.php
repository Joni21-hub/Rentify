<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan Akun - Rentify</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: #f1f5f9; }
    </style>
</head>
<body class="min-h-screen pb-10">

    <div class="max-w-md mx-auto min-h-screen bg-slate-100 relative shadow-xl">

        {{-- ═══════════════════════════════════════════
             HEADER BIRU GRADIENT (Shopee / Rentify style)
        ════════════════════════════════════════════ --}}
        <div class="bg-gradient-to-r from-[#0369a1] to-sky-400 pt-12 pb-16 px-5 relative">
            {{-- Tombol back --}}
            <a href="{{ route('customer.dashboard') }}" class="absolute top-5 left-4 w-8 h-8 flex items-center justify-center rounded-full bg-white/20 text-white hover:bg-white/30 transition">
                <i class="fa-solid fa-arrow-left text-sm"></i>
            </a>
            <h1 class="text-center text-white font-extrabold text-lg tracking-wide">Akun Saya</h1>

            {{-- Kartu profil --}}
            <div class="mt-6 bg-white rounded-2xl shadow-lg px-5 py-4 flex items-center gap-4">
                {{-- Foto profil / Inisial --}}
                @if($user->foto_profil)
                    <img src="{{ $user->foto_profil }}" alt="Foto Profil"
                         class="w-16 h-16 rounded-full object-cover ring-2 ring-sky-200 flex-shrink-0">
                @else
                    <div class="w-16 h-16 rounded-full bg-gradient-to-br from-sky-400 to-blue-600 flex items-center justify-center flex-shrink-0 ring-2 ring-sky-200">
                        <span class="text-white font-extrabold text-2xl">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                    </div>
                @endif

                <div class="flex-1 min-w-0">
                    <p class="font-extrabold text-slate-800 text-base truncate">{{ $user->name }}</p>
                    <p class="text-slate-400 text-xs truncate">{{ $user->email }}</p>

                    {{-- Badge WhatsApp --}}
                    <div class="flex items-center gap-1.5 mt-1.5">
                        @if($user->whatsapp)
                            <span class="inline-flex items-center gap-1 text-[10px] font-bold {{ $user->whatsapp_verified_at ? 'bg-emerald-50 text-emerald-600 border border-emerald-200' : 'bg-amber-50 text-amber-600 border border-amber-200' }} rounded-full px-2 py-0.5">
                                <i class="fa-brands fa-whatsapp"></i>
                                {{ $user->whatsapp_verified_at ? 'Terverifikasi' : 'Belum Verifikasi' }}
                            </span>
                            <span class="text-[10px] text-slate-400 font-medium">{{ $user->whatsapp }}</span>
                        @else
                            <span class="inline-flex items-center gap-1 text-[10px] font-bold bg-slate-100 text-slate-400 rounded-full px-2 py-0.5">
                                <i class="fa-brands fa-whatsapp"></i> Belum diisi
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Notifikasi --}}
        <div class="px-4 -mt-2">
            @if(session('success'))
                <div class="mb-3 p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm font-bold flex items-center gap-2 shadow-sm">
                    <i class="fa-solid fa-circle-check text-emerald-500"></i>
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="mb-3 p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-sm font-bold flex items-center gap-2 shadow-sm">
                    <i class="fa-solid fa-triangle-exclamation text-rose-500"></i>
                    {{ session('error') }}
                </div>
            @endif
        </div>

        <div class="px-4 pt-2 space-y-3">

            {{-- ═══════════════════════════════════
                 SEKSI: AKUN SAYA
            ════════════════════════════════════ --}}
            <p class="text-[10px] font-extrabold text-slate-400 uppercase tracking-[0.15em] ml-1 mt-2">Akun Saya</p>

            <div class="bg-white rounded-2xl shadow-sm overflow-hidden divide-y divide-slate-100">

                {{-- Ubah Profil --}}
                <a href="{{ route('customer.settings.profile') }}" class="flex items-center justify-between p-4 hover:bg-slate-50 active:bg-slate-100 transition group">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center flex-shrink-0 group-hover:scale-105 transition">
                            <i class="fa-solid fa-user-pen"></i>
                        </div>
                        <div>
                            <span class="block text-sm font-bold text-slate-800">Ubah Profil</span>
                            <span class="block text-[10px] text-slate-400 font-medium">Nama, foto, dan email</span>
                        </div>
                    </div>
                    <i class="fa-solid fa-chevron-right text-slate-300 text-xs"></i>
                </a>

                {{-- Ubah Nomor WA --}}
                <a href="{{ route('customer.settings.whatsapp') }}" class="flex items-center justify-between p-4 hover:bg-slate-50 active:bg-slate-100 transition group">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center flex-shrink-0 group-hover:scale-105 transition">
                            <i class="fa-brands fa-whatsapp text-lg"></i>
                        </div>
                        <div>
                            <span class="block text-sm font-bold text-slate-800">Ubah Nomor WhatsApp</span>
                            <span class="block text-[10px] text-slate-400 font-medium">Verifikasi via OTP</span>
                        </div>
                    </div>
                    <i class="fa-solid fa-chevron-right text-slate-300 text-xs"></i>
                </a>

                {{-- Alamat Email --}}
                <a href="{{ route('customer.settings.email') }}" class="flex items-center justify-between p-4 hover:bg-slate-50 transition group">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-orange-50 text-orange-500 rounded-full flex items-center justify-center">
                            <i class="fa-solid fa-envelope"></i>
                        </div>
                        <div>
                            <span class="block text-sm font-bold text-slate-800">Alamat Email</span>
                            <span class="block text-[10px] text-slate-400">{{ $user->email }}</span>
                        </div>
                    </div>
                    <i class="fa-solid fa-chevron-right text-slate-300 text-xs"></i>
                </a>

                {{-- Keamanan Akun --}}
                <a href="{{ route('customer.settings.password') }}" class="flex items-center justify-between p-4 hover:bg-slate-50 active:bg-slate-100 transition group">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-rose-100 text-rose-600 rounded-full flex items-center justify-center flex-shrink-0 group-hover:scale-105 transition">
                            <i class="fa-solid fa-lock"></i>
                        </div>
                        <div>
                            <span class="block text-sm font-bold text-slate-800">Keamanan Akun</span>
                            <span class="block text-[10px] text-slate-400 font-medium">
                                @if($user->google_id && !$user->password_changed_at)
                                    Buat kata sandi untuk login manual
                                @else
                                    Ubah kata sandi akun Anda
                                @endif
                            </span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        @if($user->google_id && !$user->password_changed_at)
                            <span class="text-[9px] font-bold bg-amber-100 text-amber-600 rounded-full px-2 py-0.5">Belum diatur</span>
                        @endif
                        <i class="fa-solid fa-chevron-right text-slate-300 text-xs"></i>
                    </div>
                </a>

                {{-- Alamat Saya --}}
                <a href="{{ route('customer.lokasi') }}" class="flex items-center justify-between p-4 hover:bg-slate-50 active:bg-slate-100 transition group">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-purple-100 text-purple-600 rounded-full flex items-center justify-center flex-shrink-0 group-hover:scale-105 transition">
                            <i class="fa-solid fa-map-location-dot"></i>
                        </div>
                        <div>
                            <span class="block text-sm font-bold text-slate-800">Alamat Saya</span>
                            <span class="block text-[10px] text-slate-400 font-medium">
                                @if($user->alamat_lengkap)
                                    {{ Str::limit($user->alamat_lengkap, 35) }}
                                @else
                                    Atur lokasi pengiriman Anda
                                @endif
                            </span>
                        </div>
                    </div>
                    <i class="fa-solid fa-chevron-right text-slate-300 text-xs"></i>
                </a>
            </div>

            {{-- ═══════════════════════════════════
                 SEKSI: BANTUAN
            ════════════════════════════════════ --}}
            <p class="text-[10px] font-extrabold text-slate-400 uppercase tracking-[0.15em] ml-1 mt-4">Bantuan</p>

            <div class="bg-white rounded-2xl shadow-sm overflow-hidden divide-y divide-slate-100">

                {{-- Pusat Bantuan --}}
                <a href="https://wa.me/6283183494835" target="_blank" rel="noopener noreferrer" class="flex items-center justify-between p-4 hover:bg-slate-50 active:bg-slate-100 transition group">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-sky-100 text-sky-600 rounded-full flex items-center justify-center flex-shrink-0 group-hover:scale-105 transition">
                            <i class="fa-solid fa-headset"></i>
                        </div>
                        <div>
                            <span class="block text-sm font-bold text-slate-800">Pusat Bantuan</span>
                            <span class="block text-[10px] text-slate-400 font-medium">Chat admin via WhatsApp</span>
                        </div>
                    </div>
                    <i class="fa-solid fa-arrow-up-right-from-square text-slate-300 text-xs"></i>
                </a>

                {{-- Kebijakan Privasi --}}
                <a href="#" class="flex items-center justify-between p-4 hover:bg-slate-50 active:bg-slate-100 transition group">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-slate-100 text-slate-500 rounded-full flex items-center justify-center flex-shrink-0 group-hover:scale-105 transition">
                            <i class="fa-solid fa-shield-halved"></i>
                        </div>
                        <div>
                            <span class="block text-sm font-bold text-slate-800">Kebijakan Privasi</span>
                            <span class="block text-[10px] text-slate-400 font-medium">Ketentuan penggunaan Rentify</span>
                        </div>
                    </div>
                    <i class="fa-solid fa-chevron-right text-slate-300 text-xs"></i>
                </a>
            </div>

            {{-- ═══════════════════════════════════
                 TOMBOL KELUAR AKUN
            ════════════════════════════════════ --}}
            <div class="mt-4">
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit"
                            onclick="return confirm('Yakin ingin keluar dari akun Rentify?')"
                            class="w-full flex items-center justify-center gap-2 bg-white border-2 border-rose-200 text-rose-500 hover:bg-rose-50 active:bg-rose-100 font-extrabold py-3.5 rounded-2xl text-sm shadow-sm transition">
                        <i class="fa-solid fa-right-from-bracket"></i>
                        Keluar Akun
                    </button>
                </form>
            </div>

            {{-- Footer --}}
            <p class="text-center text-[10px] font-bold text-slate-400 mt-6 pb-2">Rentify v1.0.0</p>
        </div>
    </div>
</body>
</html>
