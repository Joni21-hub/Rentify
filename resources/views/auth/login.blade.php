<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk ke Rentify</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#0369a1">

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; }

        /* BACKGROUND AIR LAUT BERGELOMBANG (CALMING OCEAN) */
        @keyframes oceanBreath {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        .bg-ocean {
            background: linear-gradient(-45deg, #0f172a, #0369a1, #0c4a6e, #0284c7);
            background-size: 200% 200%;
            animation: oceanBreath 12s ease-in-out infinite;
        }

        /* EFEK KACA NYATA (REAL GLASS) */
        .glass-panel {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(30px);
            -webkit-backdrop-filter: blur(30px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-top: 1px solid rgba(255, 255, 255, 0.4);
            border-left: 1px solid rgba(255, 255, 255, 0.4);
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.4);
            border-radius: 2rem;
        }

        /* INPUT KACA */
        .input-glass {
            background: rgba(255, 255, 255, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: #ffffff;
            transition: all 0.3s ease;
        }
        .input-glass:focus {
            background: rgba(255, 255, 255, 0.4);
            border-color: #ffffff;
            box-shadow: 0 0 15px rgba(255, 255, 255, 0.5);
        }
        .input-glass::placeholder { color: rgba(255, 255, 255, 0.6); font-weight: 500; }

        /* BINTANG / KILAUAN AIR */
        .sparkle {
            position: absolute;
            width: 3px; height: 3px;
            background-color: white;
            border-radius: 50%;
            opacity: 0;
            animation: twinkle 5s infinite ease-in-out;
        }
        @keyframes twinkle {
            0%, 100% { opacity: 0; transform: scale(0.5); }
            50% { opacity: 0.6; transform: scale(1.5); box-shadow: 0 0 10px rgba(255,255,255,0.8); }
        }
    </style>
</head>
<body class="min-h-screen w-full flex items-center justify-center p-4 relative overflow-hidden bg-ocean text-white">

    <!-- KILAUAN AIR LAUT (SPARKLES) -->
    <div class="sparkle top-[20%] left-[15%]" style="animation-delay: 0s;"></div>
    <div class="sparkle top-[10%] left-[60%]" style="animation-delay: 2s;"></div>
    <div class="sparkle bottom-[30%] left-[20%]" style="animation-delay: 1s;"></div>
    <div class="sparkle top-[40%] right-[15%]" style="animation-delay: 3s;"></div>
    <div class="sparkle bottom-[15%] right-[25%]" style="animation-delay: 4s;"></div>

    <div class="w-full max-w-[380px] relative z-10">
        
        <div class="glass-panel p-8 sm:p-10 relative">
            
            <div class="text-center mb-8">
                <h1 class="text-4xl sm:text-5xl font-extrabold tracking-widest text-white drop-shadow-md mb-2" style="text-shadow: 0 0 20px rgba(255,255,255,0.3);">
                    RENTIFY
                </h1>
                <p class="text-[12px] text-white/70 font-medium tracking-wide">Mulai petualangan serumu bersama kami.</p>
            </div>

            <form action="/login" method="POST" class="space-y-4">
                @csrf

                <!-- INPUT BISA EMAIL ATAU WHATSAPP -->
                <div class="relative group">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-white/50 group-focus-within:text-white transition">
                        <i class="fa-solid fa-user"></i>
                    </span>
                    <input type="text" name="login" required placeholder="Email / No. WhatsApp" 
                        class="input-glass w-full pl-11 pr-4 py-3.5 rounded-2xl text-sm focus:outline-none font-bold">
                </div>

                <div class="relative group">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-white/50 group-focus-within:text-white transition">
                        <i class="fa-solid fa-lock text-sm"></i>
                    </span>
                    <input type="password" id="passwordField" name="password" required placeholder="Kata Sandi" 
                        class="input-glass w-full pl-11 pr-11 py-3.5 rounded-2xl text-sm focus:outline-none font-bold">
                    <button type="button" onclick="togglePassword()" class="absolute inset-y-0 right-0 flex items-center pr-4 text-white/50 hover:text-white transition">
                        <i id="eyeIcon" class="fa-regular fa-eye-slash text-sm"></i>
                    </button>
                </div>

                @if ($errors->any())
                    <div class="bg-rose-500/80 backdrop-blur border border-rose-400 text-white px-4 py-3 rounded-xl text-xs font-bold flex items-center gap-2 shadow-lg">
                        <i class="fa-solid fa-circle-exclamation text-sm"></i>
                        <span>{{ $errors->first() }}</span>
                    </div>
                @endif
                @if (session('error'))
                    <div class="bg-rose-500/80 backdrop-blur border border-rose-400 text-white px-4 py-3 rounded-xl text-xs font-bold flex items-center gap-2 shadow-lg">
                        <i class="fa-solid fa-circle-exclamation text-sm"></i>
                        <span>{{ session('error') }}</span>
                    </div>
                @endif

                <div class="pt-2">
                    <button type="submit" class="w-full bg-white/10 hover:bg-white/20 border border-white/40 text-white font-extrabold py-3.5 rounded-2xl text-sm tracking-widest uppercase shadow-lg transition-all transform hover:-translate-y-0.5 active:translate-y-0 flex justify-center items-center gap-2 backdrop-blur-md">
                        <span>MASUK</span>
                    </button>
                </div>
                
                <div class="flex items-center gap-3 my-5">
                    <div class="h-px bg-white/20 flex-1"></div>
                    <span class="text-[10px] font-bold text-white/50 tracking-widest">ATAU</span>
                    <div class="h-px bg-white/20 flex-1"></div>
                </div>

                <a href="{{ route('google.login') }}" class="w-full flex items-center justify-center gap-3 py-3.5 bg-white/10 hover:bg-white/20 border border-white/30 rounded-2xl transition-all font-extrabold text-white text-sm shadow-sm backdrop-blur-md">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" class="w-5 h-5 bg-white rounded-full p-0.5">
                        <path fill="#FFC107" d="M43.611,20.083H42V20H24v8h11.303c-1.649,4.657-6.08,8-11.303,8c-6.627,0-12-5.373-12-12c0-6.627,5.373-12,12-12c3.059,0,5.842,1.154,7.961,3.039l5.657-5.657C34.046,6.053,29.268,4,24,4C12.955,4,4,12.955,4,24c0,11.045,8.955,20,20,20c11.045,0,20-8.955,20-20C44,22.659,43.862,21.35,43.611,20.083z"></path>
                        <path fill="#FF3D00" d="M6.306,14.691l6.571,4.819C14.655,15.108,18.961,12,24,12c3.059,0,5.842,1.154,7.961,3.039l5.657-5.657C34.046,6.053,29.268,4,24,4C16.318,4,9.656,8.337,6.306,14.691z"></path>
                        <path fill="#4CAF50" d="M24,44c5.166,0,9.86-1.977,13.409-5.192l-6.19-5.238C29.211,35.091,26.715,36,24,36c-5.202,0-9.619-3.317-11.283-7.946l-6.522,5.025C9.505,39.556,16.227,44,24,44z"></path>
                        <path fill="#1976D2" d="M43.611,20.083H42V20H24v8h11.303c-0.792,2.237-2.231,4.166-4.087,5.571c0.001-0.001,0.002-0.001,0.003-0.002l6.19,5.238C36.971,39.205,44,34,44,24C44,22.659,43.862,21.35,43.611,20.083z"></path>
                    </svg>
                    Lanjutkan dengan Google
                </a>
            </form>

            <div class="mt-8 space-y-4 text-center">
                <p class="text-[11px] text-white/70 font-medium">Belum punya akun? 
                    <a href="/register" class="text-white font-extrabold hover:text-sky-200 hover:underline transition ml-1">Daftar sekarang</a>
                </p>
                <div class="h-px w-1/2 mx-auto bg-white/10"></div>
                <p class="text-[11px]">
                    <a href="/vendor/register" class="text-sky-300 font-extrabold hover:text-sky-100 hover:underline underline-offset-4 transition">
                        Daftar menjadi bagian dari rentify
                    </a>
                </p>
            </div>
            
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordField = document.getElementById('passwordField');
            const eyeIcon = document.getElementById('eyeIcon');
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                eyeIcon.classList.replace('fa-eye-slash', 'fa-eye');
            } else {
                passwordField.type = 'password';
                eyeIcon.classList.replace('fa-eye', 'fa-eye-slash');
            }
        }
    </script>
</body>
</html>