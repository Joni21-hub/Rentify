<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Kata Sandi - Rentify</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="{{ asset('rentify-theme.css') }}">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; }

        @keyframes gradientFlow {
            0%   { background-position: 0% 50%; }
            50%  { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        .bg-flowing {
            background: linear-gradient(-45deg, #0284c7, #38bdf8, #0ea5e9, #0369a1);
            background-size: 300% 300%;
            animation: gradientFlow 15s ease infinite;
        }
        
        .input-glass {
            background: rgba(255,255,255,0.4);
            border: 1px solid rgba(255,255,255,0.5);
            color: #0f172a;
            transition: all 0.3s ease;
        }
        .input-glass:focus {
            background: rgba(255,255,255,0.7);
            border-color: #ffffff;
            box-shadow: 0 0 15px rgba(255,255,255,0.5);
        }
        .input-glass::placeholder { color: rgba(15,23,42,0.5); font-weight: 500; }
        .sparkle {
            position: absolute; width: 4px; height: 4px;
            background: white; border-radius: 50%; opacity: 0;
            animation: twinkle 4s infinite ease-in-out;
        }
        @keyframes twinkle {
            0%,100% { opacity:0; transform:scale(0.5); }
            50% { opacity:0.8; transform:scale(1.5); box-shadow:0 0 12px rgba(255,255,255,1); }
        }
    </style>
</head>
<body class="min-h-screen w-full flex items-center justify-center p-4 relative overflow-hidden text-slate-800">

    <!-- Efek Bintang Berkedip -->
    <div class="sparkle" style="top:10%;left:20%;animation-delay:0.5s"></div>
    <div class="sparkle" style="top:30%;right:15%;animation-delay:2s"></div>
    <div class="sparkle" style="bottom:15%;left:30%;animation-delay:1s"></div>
    <div class="sparkle" style="bottom:40%;right:25%;animation-delay:3s"></div>
    <div class="sparkle" style="top:60%;left:10%;animation-delay:1.5s"></div>

    <div class="w-full max-w-[400px] relative z-10">
        <div class="rentify-card p-8 sm:p-10">

            <!-- Header -->
            <div class="text-center mb-8">
                <div class="w-16 h-16 rentify-card/20 -white/40 flex items-center justify-center mx-auto mb-4">
                    <i class="fa-solid fa-key text-2xl text-white drop-shadow"></i>
                </div>
                <h1 class="text-2xl font-extrabold text-white drop-shadow mb-1">Lupa Kata Sandi?</h1>
                <p class="text-xs text-white/80 font-medium leading-relaxed">
                    Masukkan nomor WhatsApp atau alamat email<br>yang terdaftar di akun Rentify Anda.
                </p>
            </div>

            <!-- Pesan Error -->
            @if (session('error'))
                <div class="bg-rose-500/90 backdrop-blur-md border border-rose-400 text-white px-4 py-3 rounded-xl text-xs font-bold flex items-center gap-2 shadow-lg mb-4">
                    <i class="fa-solid fa-circle-exclamation text-sm flex-shrink-0"></i>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            @if ($errors->any())
                <div class="bg-rose-500/90 backdrop-blur-md border border-rose-400 text-white px-4 py-3 rounded-xl text-xs font-bold flex items-center gap-2 shadow-lg mb-4">
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
                    <button type="submit"
                        class="w-full rentify-btn text-white font-extrabold py-3.5 rounded-2xl text-sm tracking-widest uppercase shadow-[0_10px_20px_rgba(0,0,0,0.15)] hover:shadow-[0_15px_25px_rgba(0,0,0,0.2)] transition-all transform hover:-translate-y-0. active:translate-y-05 active:translate-y-0 flex justify-center items-center gap-2">
                        <i class="fa-solid fa-paper-plane"></i>
                        <span>Kirim Kode OTP</span>
                    </button>
                </div>
            </form>

            <!-- Link kembali -->
            <div class="mt-7 text-center">
                <a href="{{ route('login') }}"
                   class="text-xs text-white/80 font-semibold hover:text-white transition flex items-center justify-center gap-1.5">
                    <i class="fa-solid fa-arrow-left text-[10px]"></i>
                    Kembali ke halaman Masuk
                </a>
            </div>

        </div>
    </div>

</body>
</html>
