<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun Customer — Rentify</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; }

        /* Animasi Bola-Bola Biru Langit (Floating Blobs) */
        @keyframes float {
            0%, 100% { transform: translateY(0px) scale(1); }
            50% { transform: translateY(-20px) scale(1.05); }
        }
        .animate-float-1 { animation: float 8s ease-in-out infinite; }
        .animate-float-2 { animation: float 12s ease-in-out infinite alternate; }

        /* Efek Kaca Es Bening (Light Glassmorphism) */
        .glass-card {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.9);
            box-shadow: 0 20px 50px rgba(14, 165, 233, 0.12), 0 0 20px rgba(255, 255, 255, 0.6);
        }

        /* Input Kapsul Sejuk */
        .input-sky {
            background: rgba(240, 249, 255, 0.7);
            border: 1px solid rgba(186, 230, 253, 0.8);
            transition: all 0.3s ease;
        }
        .input-sky:focus {
            background: #ffffff;
            border-color: #0284c7;
            box-shadow: 0 0 15px rgba(14, 165, 233, 0.25);
        }

        /* Efek Shimmer Kilatan Tombol */
        @keyframes shimmer {
            100% { left: 125%; }
        }
        .btn-shimmer { position: relative; overflow: hidden; }
        .btn-shimmer::after {
            content: ''; position: absolute; top: -50%; left: -60%;
            width: 30%; height: 200%;
            background: rgba(255, 255, 255, 0.35);
            transform: rotate(30deg);
            animation: shimmer 3.5s infinite ease-in-out;
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4 sm:p-6 relative overflow-hidden bg-gradient-to-br from-cyan-50 via-sky-100 to-blue-200 text-slate-700">

    <!-- ================================================================= -->
    <!-- LATAR BELAKANG BIRU AWAN & LAUT SEJUK (TANPA NAVY) -->
    <!-- ================================================================= -->
    
    <!-- Bola-Bola Air Melayang (Glow Orbs) -->
    <div class="absolute top-[10%] left-[15%] w-72 h-72 bg-gradient-to-tr from-cyan-300/40 to-sky-400/40 rounded-full blur-3xl pointer-events-none animate-float-1"></div>
    <div class="absolute bottom-[10%] right-[15%] w-80 h-80 bg-gradient-to-bl from-blue-300/40 via-sky-300/30 to-teal-200/40 rounded-full blur-3xl pointer-events-none animate-float-2"></div>
    <div class="absolute top-[40%] right-[30%] w-48 h-48 bg-cyan-200/50 rounded-full blur-2xl pointer-events-none"></div>

    <!-- ================================================================= -->
    <!-- KARTU DAFTAR LIGHT GLASSMORPHISM -->
    <!-- ================================================================= -->
    
    <div class="w-full max-w-md glass-card rounded-[2.5rem] p-8 sm:p-10 relative z-10 transition-all duration-300">
        
        <!-- Judul RENTIFY Biru Laut -->
        <div class="text-center mb-6">
            <div class="inline-block px-3 py-1 rounded-full bg-sky-100/80 border border-sky-200 text-[#0284c7] text-[10px] font-bold uppercase tracking-widest mb-2 shadow-sm">
                <i class="fa-solid fa-water sm:mr-1 animate-bounce"></i> Customer Portal
            </div>
            <h1 class="text-3xl sm:text-4xl font-extrabold tracking-wider text-transparent bg-clip-text bg-gradient-to-r from-cyan-600 via-sky-600 to-blue-600 drop-shadow-sm">
                RENTIFY
            </h1>
            <p class="text-xs sm:text-sm text-sky-700/80 font-medium mt-1">Mulai petualangan serumu bersama kami.</p>
        </div>

        <!-- Peringatan Error (Jika Ada Input Salah) -->
        @if ($errors->any())
            <div class="mb-5 p-4 bg-rose-50/90 border border-rose-200 rounded-2xl text-rose-600 text-xs shadow-sm backdrop-blur-md">
                <div class="font-bold flex items-center gap-1.5 mb-1 text-rose-700">
                    <i class="fa-solid fa-circle-exclamation"></i> Periksa kembali inputanmu:
                </div>
                <ul class="list-disc pl-5 space-y-0.5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Formulir Pendaftaran (100% Sesuai Backend Asli) -->
        <form action="{{ route('register') }}" method="POST" class="space-y-4">
            @csrf 

            <!-- Nama Lengkap -->
            <div class="space-y-1">
                <label class="text-[11px] font-bold text-sky-800 uppercase tracking-wider block ml-1">Nama Lengkap</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-sky-400 text-sm">
                        <i class="fa-solid fa-user"></i>
                    </span>
                    <input type="text" name="name" value="{{ old('name') }}" required
                        class="input-sky w-full pl-11 pr-4 py-3 rounded-2xl text-sm text-slate-800 placeholder-slate-400 focus:outline-none font-medium shadow-inner" 
                        placeholder="Masukkan nama lengkap Anda">
                </div>
            </div>

            <!-- Alamat Email -->
            <div class="space-y-1">
                <label class="text-[11px] font-bold text-sky-800 uppercase tracking-wider block ml-1">Alamat Email</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-sky-400 text-sm">
                        <i class="fa-solid fa-envelope"></i>
                    </span>
                    <input type="email" name="email" value="{{ old('email') }}" required
                        class="input-sky w-full pl-11 pr-4 py-3 rounded-2xl text-sm text-slate-800 placeholder-slate-400 focus:outline-none font-medium shadow-inner" 
                        placeholder="contoh@email.com">
                </div>
            </div>

            <!-- Kata Sandi (Password) + Tombol Intip -->
            <div class="space-y-1">
                <label class="text-[11px] font-bold text-sky-800 uppercase tracking-wider block ml-1">Kata Sandi (Password)</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-sky-400 text-sm">
                        <i class="fa-solid fa-lock"></i>
                    </span>
                    <input type="password" id="passInput" name="password" required
                        class="input-sky w-full pl-11 pr-11 py-3 rounded-2xl text-sm text-slate-800 placeholder-slate-400 focus:outline-none font-medium shadow-inner" 
                        placeholder="Minimal 8 karakter">
                    <button type="button" onclick="togglePass('passInput', 'eye1')" class="absolute inset-y-0 right-0 flex items-center pr-4 text-sky-400 hover:text-sky-600 transition">
                        <i id="eye1" class="fa-regular fa-eye-slash text-xs"></i>
                    </button>
                </div>
            </div>

            <!-- Konfirmasi Kata Sandi + Tombol Intip -->
            <div class="space-y-1">
                <label class="text-[11px] font-bold text-sky-800 uppercase tracking-wider block ml-1">Konfirmasi Kata Sandi</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-sky-400 text-sm">
                        <i class="fa-solid fa-shield-halved"></i>
                    </span>
                    <input type="password" id="passConfirmInput" name="password_confirmation" required
                        class="input-sky w-full pl-11 pr-11 py-3 rounded-2xl text-sm text-slate-800 placeholder-slate-400 focus:outline-none font-medium shadow-inner" 
                        placeholder="Ulangi kata sandi Anda">
                    <button type="button" onclick="togglePass('passConfirmInput', 'eye2')" class="absolute inset-y-0 right-0 flex items-center pr-4 text-sky-400 hover:text-sky-600 transition">
                        <i id="eye2" class="fa-regular fa-eye-slash text-xs"></i>
                    </button>
                </div>
            </div>

            <!-- Tombol Daftar (Sea Blue Shimmer Gradient) -->
            <div class="pt-2">
                <button type="submit" 
                    class="btn-shimmer w-full py-4 bg-gradient-to-r from-cyan-500 via-sky-500 to-blue-600 hover:from-cyan-600 hover:via-sky-600 hover:to-blue-700 text-white rounded-2xl font-bold text-sm shadow-lg shadow-sky-500/25 hover:shadow-sky-500/40 transition-all transform hover:-translate-y-0.5 active:translate-y-0 tracking-wide flex items-center justify-center gap-2">
                    <span>Daftar Akun Sekarang</span>
                    <i class="fa-solid fa-arrow-right text-xs"></i>
                </button>
            </div>
        </form>

        <!-- Link Masuk -->
        <div class="text-center mt-6 pt-5 border-t border-sky-100/80">
            <p class="text-xs text-slate-500 font-medium">Sudah punya akun Rentify? 
                <a href="{{ route('login') }}" class="text-sky-600 font-bold hover:text-cyan-600 hover:underline transition ml-1">Masuk di sini</a>
            </p>
        </div>

    </div>

    <!-- Skrip Intip Kata Sandi -->
    <script>
        function togglePass(inputId, eyeId) {
            const input = document.getElementById(inputId);
            const eye = document.getElementById(eyeId);
            if (input.type === 'password') {
                input.type = 'text';
                eye.classList.remove('fa-eye-slash');
                eye.classList.add('fa-eye');
            } else {
                input.type = 'password';
                eye.classList.remove('fa-eye');
                eye.classList.add('fa-eye-slash');
            }
        }
    </script>

</body>
</html>