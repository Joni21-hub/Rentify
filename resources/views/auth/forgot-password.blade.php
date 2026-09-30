<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Kata Sandi - Rentify</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="{{ asset('rentify-theme.css') }}">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
</head>
<body class="min-h-screen w-full flex items-center justify-center p-4 relative overflow-hidden text-slate-800">

    <div class="w-full max-w-[400px] relative z-10">
        <div class="rentify-card p-8 sm:p-10">

            <!-- Header -->
            <div class="text-center mb-8">
                <div class="w-16 h-16 bg-sky-100 border border-sky-200 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fa-solid fa-key text-2xl text-sky-600"></i>
                </div>
                <h1 class="text-3xl font-extrabold text-sky-500 tracking-tighter mb-1">Lupa Kata Sandi?</h1>
                <p class="text-[12px] text-[#475569] font-medium leading-relaxed">
                    Masukkan nomor WhatsApp atau alamat email<br>yang terdaftar di akun Rentify Anda.
                </p>
            </div>

            <!-- Pesan Error -->
            @if (session('error'))
                <div class="bg-rose-500/90 backdrop-blur-md border border-rose-400 text-slate-800 px-4 py-3 rounded-xl text-xs font-bold flex items-center gap-2 shadow-lg mb-4">
                    <i class="fa-solid fa-circle-exclamation text-sm flex-shrink-0"></i>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            @if ($errors->any())
                <div class="bg-rose-500/90 backdrop-blur-md border border-rose-400 text-slate-800 px-4 py-3 rounded-xl text-xs font-bold flex items-center gap-2 shadow-lg mb-4">
                    <i class="fa-solid fa-circle-exclamation text-sm flex-shrink-0"></i>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            <!-- Form -->
            <form action="{{ route('password.send.otp') }}" method="POST" class="space-y-5">
                @csrf

                <div class="relative group">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-600 group-focus-within:text-sky-500 transition">
                        <i class="fa-solid fa-id-card text-sm"></i>
                    </span>
                    <input
                        type="text"
                        name="identitas"
                        required
                        autofocus
                        placeholder="No. WhatsApp / Email"
                        value="{{ old('identitas') }}"
                        class="rentify-input w-full pl-11 pr-4 py-3.5 text-sm focus:outline-none font-bold"
                    >
                </div>

                <div class="pt-1">
                    <button type="submit" class="w-full rentify-btn text-slate-800 font-extrabold py-3.5 rounded-2xl text-sm tracking-widest uppercase shadow-[0_10px_20px_rgba(0,0,0,0.15)] hover:shadow-[0_15px_25px_rgba(0,0,0,0.2)] transition-all transform hover:-translate-y-0. active:translate-y-05 active:translate-y-0 flex justify-center items-center gap-2">
                        <i class="fa-solid fa-paper-plane"></i>
                        <span>Kirim Kode OTP</span>
                    </button>
                </div>
            </form>

            <!-- Link kembali -->
            <div class="mt-7 text-center">
                <a href="{{ route('login') }}"
                   class="text-xs text-[#475569] font-semibold hover:text-sky-700 transition flex items-center justify-center gap-1.5">
                    <i class="fa-solid fa-arrow-left text-[10px]"></i>
                    Kembali ke halaman Masuk
                </a>
            </div>

        </div>
    </div>

</body>
</html>
