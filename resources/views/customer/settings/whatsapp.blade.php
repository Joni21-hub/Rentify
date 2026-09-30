<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Ubah WhatsApp - Rentify</title>
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
            <h1 class="text-base font-black text-slate-800 flex-1 text-center tracking-tight pr-9">Nomor WhatsApp</h1>
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

            @if(!session('otp_sent'))
                <!-- FORM MINTA OTP -->
                <div class="mb-5 rentify-card p-4 rounded-2xl shadow-sm">
                    <p class="text-xs text-slate-600 font-medium leading-relaxed">
                        Nomor WhatsApp terdaftar saat ini: <b class="text-slate-800 font-bold">{{ $user->whatsapp ?? 'Belum ada' }}</b>.<br>
                        Untuk menggantinya, masukkan nomor baru Anda. Kami akan mengirimkan 6 digit kode OTP verifikasi ke nomor baru.
                    </p>
                </div>

                <form action="{{ route('customer.settings.whatsapp.update') }}" method="POST" class="rentify-card p-5 rounded-3xl shadow-sm space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-slate-600 mb-1.5 ml-1">Konfirmasi Kata Sandi Akun</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                                <i class="fa-solid fa-lock text-xs"></i>
                            </span>
                            <input type="password" name="password_konfirmasi" required placeholder="Masukkan kata sandi akun"
                                   class="rentify-input input-glass w-full pl-11 pr-4 py-3.5 rounded-2xl text-sm font-bold focus:outline-none transition">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 mb-1.5 ml-1">Nomor WhatsApp Baru</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-emerald-600">
                                <i class="fa-brands fa-whatsapp text-sm"></i>
                            </span>
                            <input type="text" name="whatsapp_baru" required placeholder="081234567xxx"
                                   class="rentify-input input-glass w-full pl-11 pr-4 py-3.5 rounded-2xl text-sm font-bold focus:outline-none transition">
                        </div>
                    </div>

                    <div class="pt-2">
                        <button type="submit" class="rentify-btn w-full py-3.5 rounded-2xl text-xs font-black tracking-widest uppercase flex justify-center items-center gap-2 shadow-md transition-all transform hover:-translate-y-0.5 active:translate-y-0">
                            <i class="fa-brands fa-whatsapp text-sm"></i>
                            <span>Kirim Kode OTP WhatsApp</span>
                        </button>
                    </div>
                </form>
            @else
                <!-- FORM MASUKKAN OTP -->
                <div class="mb-6 rentify-card p-5 rounded-3xl text-center shadow-sm">
                    <div class="w-14 h-14 bg-emerald-100 text-emerald-600 rounded-2xl flex items-center justify-center mx-auto mb-3 text-2xl shadow-sm">
                        <i class="fa-solid fa-mobile-screen-button"></i>
                    </div>
                    <h3 class="font-black text-slate-800 text-sm mb-1">Verifikasi WhatsApp</h3>
                    <p class="text-xs text-slate-500 font-medium leading-relaxed">
                        Kode OTP 6-digit telah dikirimkan ke nomor:<br>
                        <b class="text-emerald-700 text-sm block mt-1">{{ session('otp_wa_baru') }}</b>
                    </p>
                </div>

                <form action="{{ route('customer.settings.whatsapp.verify') }}" method="POST" class="space-y-5 text-center">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-slate-600 mb-2">Masukkan 6 Digit OTP</label>
                        <input type="number" name="otp" required maxlength="6"
                            class="rentify-input input-glass w-full text-center tracking-[0.5em] text-2xl py-3.5 rounded-2xl font-black focus:outline-none transition" 
                            placeholder="••••••" autofocus>
                    </div>

                    <div class="pt-2">
                        <button type="submit" class="rentify-btn w-full py-3.5 rounded-2xl text-xs font-black tracking-widest uppercase shadow-md transition-all transform hover:-translate-y-0.5 active:translate-y-0">
                            Verifikasi & Simpan Nomor
                        </button>
                    </div>

                    <p class="text-xs text-slate-500 font-medium">
                        Tidak menerima kode?
                        <a href="{{ route('customer.settings.whatsapp') }}" class="text-sky-600 font-bold hover:underline ml-1">Kirim Ulang</a>
                    </p>
                </form>
            @endif
        </div>
    </div>
</body>
</html>