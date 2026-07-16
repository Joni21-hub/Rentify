<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk ke Rentify — Eksplorasi Dimulai</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; }

        /* 1. KACA SANGAT TRANSPARAN & BENING */
        .glass-panel {
            background: rgba(255, 255, 255, 0.01); /* Sangat bening */
            backdrop-filter: blur(5px); /* Blur diturunkan drastis agar tembus pandang */
            -webkit-backdrop-filter: blur(5px);
            border: 1px solid rgba(255, 255, 255, 0.15);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2), inset 0 0 15px rgba(255, 255, 255, 0.05);
            border-radius: 2rem;
        }

        /* 2. TOMBOL MASUK BERGRADASI & ANIMASI CAHAYA MENGALIR */
        .btn-gradient-animated {
            background: linear-gradient(90deg, #005bc4, #00d2ff, #2785f0);
            background-size: 200% auto;
            animation: gradientFlow 3s linear infinite;
            box-shadow: 0 0 20px rgba(0, 210, 255, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
        }
        .btn-gradient-animated:hover {
            box-shadow: 0 0 35px rgba(0, 210, 255, 0.8);
            transform: scale(1.02);
        }
        @keyframes gradientFlow {
            0% { background-position: 0% center; }
            100% { background-position: 200% center; }
        }

        /* 3. BINTANG BERKEDIP NYATA (Titik Cahaya Halus) */
        @keyframes twinkleSky {
            0%, 100% { opacity: 0.1; transform: scale(0.5); box-shadow: 0 0 2px 0px rgba(255,255,255,0.1); }
            50% { opacity: 1; transform: scale(1.2); box-shadow: 0 0 8px 2px rgba(255,255,255,0.8); }
        }
        .star-point {
            position: absolute;
            width: 2px; height: 2px;
            background-color: #ffffff;
            border-radius: 50%;
            animation: twinkleSky var(--duration) ease-in-out infinite alternate;
            animation-delay: var(--delay);
            pointer-events: none;
            z-index: 1;
        }

        /* 4. AURORA BERGERAK HALUS */
        @keyframes auroraSway {
            0% { transform: translateX(-10%) scale(1); opacity: 0.3; filter: hue-rotate(0deg); }
            100% { transform: translateX(10%) scale(1.1); opacity: 0.6; filter: hue-rotate(15deg); }
        }
        .aurora-sky {
            position: absolute; top: -10%; left: -10%; width: 120%; height: 50%;
            background: radial-gradient(ellipse at top, rgba(0, 255, 200, 0.25), transparent 60%);
            mix-blend-mode: screen;
            animation: auroraSway 12s ease-in-out infinite alternate;
            pointer-events: none; z-index: 1;
        }

        /* 5. GAMBAR LATAR BERNAPAS (CINEMATIC) */
        @keyframes cinematicBreath {
            0% { transform: scale(1); }
            100% { transform: scale(1.04) translate(-5px, -3px); }
        }
        .animate-bg-breath {
            animation: cinematicBreath 20s ease-in-out infinite alternate;
        }

        /* Input Kaca */
        .input-glass {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.15);
            transition: all 0.3s;
        }
        .input-glass:focus {
            background: rgba(255, 255, 255, 0.12);
            border-color: #00d2ff;
            box-shadow: 0 0 15px rgba(0, 210, 255, 0.3);
        }
    </style>
</head>
<body class="min-h-screen w-full flex items-center justify-center lg:justify-end lg:pr-32 p-4 relative overflow-hidden bg-[#040b16] text-white">

    <!-- GAMBAR LATAR ASLI -->
    <div class="absolute inset-0 bg-cover bg-center bg-no-repeat z-0 animate-bg-breath" 
         style="background-image: url('https://res.cloudinary.com/fnf8f1pm/image/upload/v1784175125/background_lqct46.jpg');">
    </div>

    <!-- AURORA BERGERAK -->
    <div class="aurora-sky"></div>

    <!-- BINTANG BERKEDIP (Hanya di area langit atas) -->
    <div class="star-point top-[8%] left-[15%]" style="--duration: 3s; --delay: 0s;"></div>
    <div class="star-point top-[15%] left-[30%]" style="--duration: 4s; --delay: 1s;"></div>
    <div class="star-point top-[6%] left-[60%]" style="--duration: 2.5s; --delay: 0.5s;"></div>
    <div class="star-point top-[20%] left-[50%]" style="--duration: 3.5s; --delay: 2s; background-color: #a7f3d0;"></div>
    <div class="star-point top-[12%] left-[80%]" style="--duration: 4s; --delay: 1.2s;"></div>
    <div class="star-point top-[25%] left-[85%]" style="--duration: 3s; --delay: 0.2s;"></div>

    <!-- CONTAINER FORM (mb-[15vh] mengangkat form ke atas di HP agar logo kursi terlihat) -->
    <div class="relative w-full max-w-[340px] z-20 mb-[15vh] lg:mb-0">
        
        <!-- CINCIN CAHAYA MELINGKARI PANEL KACA -->
        <!-- Kanan Atas -->
        <div class="absolute -top-4 -right-4 w-20 h-20 border-[1px] border-white/40 rounded-full shadow-[0_0_15px_rgba(255,255,255,0.4)] pointer-events-none z-0"></div>
        <!-- Kiri Bawah -->
        <div class="absolute -bottom-6 -left-6 w-24 h-24 border-[1px] border-white/30 rounded-full shadow-[0_0_20px_rgba(255,255,255,0.2)] pointer-events-none z-0"></div>

        <!-- PANEL KACA FORM -->
        <div class="glass-panel p-8 relative z-10">
            
            <div class="text-center mb-6">
                <!-- Tulisan RENTIFY Murni Tanpa Bintang -->
                <h1 class="text-3xl font-extrabold tracking-widest text-transparent bg-clip-text bg-gradient-to-b from-white via-white to-white/40 drop-shadow-[0_0_12px_rgba(255,255,255,0.4)] mb-3">
                    RENTIFY
                </h1>
                <h2 class="text-xl font-bold tracking-tight mb-1 text-white/95">Eksplorasi Dimulai</h2>
                <p class="text-[11px] text-gray-300 font-medium">Masuk untuk Petualangan Anda.</p>
            </div>

            <!-- Form -->
            <form action="/login" method="POST" class="space-y-4">
                @csrf

                <!-- Input Email -->
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-gray-400 text-sm">
                        <i class="far fa-envelope"></i>
                    </span>
                    <input type="email" name="email" required placeholder="Email" 
                        class="input-glass w-full pl-10 pr-4 py-3 rounded-full text-sm text-white placeholder-gray-400/80 focus:outline-none">
                </div>

                <!-- Input Password -->
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-gray-400 text-sm">
                        <i class="fas fa-lock"></i>
                    </span>
                    <input type="password" id="passwordField" name="password" required placeholder="Password" 
                        class="input-glass w-full pl-10 pr-10 py-3 rounded-full text-sm text-white placeholder-gray-400/80 focus:outline-none">
                    <button type="button" onclick="togglePassword()" class="absolute inset-y-0 right-0 flex items-center pr-4 text-gray-400 hover:text-white transition">
                        <i id="eyeIcon" class="far fa-eye-slash text-xs"></i>
                    </button>
                </div>

                {{-- MENCETAK PERINGATAN JIKA EMAIL / PASSWORD SALAH --}}
                @if ($errors->any())
                    <div class="mb-4 bg-rose-50 border border-rose-300 text-rose-700 px-4 py-3 rounded-lg text-xs font-bold flex items-center">
                        <i class="fas fa-exclamation-triangle mr-2 text-rose-500 text-sm"></i>
                        <span>{{ $errors->first() }}</span>
                    </div>
                @endif

                @if (session('error'))
                    <div class="mb-4 bg-rose-50 border border-rose-300 text-rose-700 px-4 py-3 rounded-lg text-xs font-bold flex items-center">
                        <i class="fas fa-exclamation-circle mr-2 text-rose-500 text-sm"></i>
                        <span>{{ session('error') }}</span>
                    </div>
                @endif

                <!-- TOMBOL MASUK BERBAHAYA (GRADASI BERGERAK GLOWING) -->
                <div class="pt-3">
                    <button type="submit" class="btn-gradient-animated w-full text-white font-bold py-3.5 rounded-full text-sm tracking-widest uppercase">
                        login
                    </button>
                </div>
            </form>

            <div class="mt-6 text-center space-y-2.5">
                <p class="text-[11px] text-gray-300">
                    <a href="/register" class="text-white hover:text-blue-300 transition underline underline-offset-2">Daftar di sini</a>
                </p>
                <p class="text-[11px]">
                    <a href="/vendor/register" class="font-semibold text-[#facc15] hover:text-yellow-300 transition underline underline-offset-2 decoration-[#facc15]/50">Daftar menjadi bagian dari rentify</a>
                </p>
            </div>
            
            <div class="mt-6 flex justify-center">
                <span class="px-4 py-1.5 rounded-full border border-white/20 text-[9px] uppercase tracking-widest text-gray-300 font-semibold bg-white/5">
                    ---------
                </span>
            </div>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordField = document.getElementById('passwordField');
            const eyeIcon = document.getElementById('eyeIcon');
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                eyeIcon.classList.remove('fa-eye-slash');
                eyeIcon.classList.add('fa-eye');
            } else {
                passwordField.type = 'password';
                eyeIcon.classList.remove('fa-eye');
                eyeIcon.classList.add('fa-eye-slash');
            }
        }
    </script>
</body>
</html>