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

        /* 1. EFEK KACA YANG LEBIH TRANSPARAN & GLOWING */
        .glass-panel {
            background: rgba(255, 255, 255, 0.01); /* Sangat tipis/transparan */
            backdrop-filter: blur(8px); /* Blur dikurangi agar lebih tembus pandang */
            -webkit-backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, 0.15);
            /* Outer glow tipis & Inner glow untuk kesan menyala */
            box-shadow: 0 0 25px rgba(255, 255, 255, 0.05), inset 0 0 15px rgba(255, 255, 255, 0.08); 
            border-radius: 2rem;
            transition: all 0.4s ease;
        }
        .glass-panel:hover {
            box-shadow: 0 0 35px rgba(255, 255, 255, 0.1), inset 0 0 20px rgba(255, 255, 255, 0.12);
        }

        .input-glass {
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.12);
            transition: all 0.3s ease;
        }
        .input-glass:focus {
            background: rgba(255, 255, 255, 0.1);
            border-color: #3b82f6;
            box-shadow: 0 0 15px rgba(59, 130, 246, 0.4);
        }

        /* 2. BACKGROUND MENGHEMBUS (CINEMATIC ZOOM) */
        @keyframes cinematicZoom {
            0% { transform: scale(1) translate(0, 0); }
            50% { transform: scale(1.05) translate(-15px, -5px); }
            100% { transform: scale(1) translate(0, 0); }
        }
        .animate-living-bg {
            animation: cinematicZoom 30s ease-in-out infinite alternate;
        }

        /* 3. AURORA BERGERAK (MIX BLEND SCREEN) */
        @keyframes auroraMove {
            0% { transform: translateX(-15%) scale(1); opacity: 0.3; }
            50% { transform: translateX(15%) translateY(-20px) scale(1.1); opacity: 0.7; }
            100% { transform: translateX(-15%) scale(1); opacity: 0.3; }
        }
        .aurora-light {
            position: absolute; top: -10%; left: -20%; width: 140%; height: 70%;
            background: radial-gradient(ellipse at top, rgba(0, 255, 200, 0.35), transparent 60%);
            mix-blend-mode: screen;
            animation: auroraMove 15s ease-in-out infinite alternate;
            pointer-events: none; z-index: 1;
        }
        .aurora-light-2 {
            position: absolute; top: 0%; right: -20%; width: 120%; height: 60%;
            background: radial-gradient(ellipse at top right, rgba(0, 150, 255, 0.25), transparent 50%);
            mix-blend-mode: color-dodge;
            animation: auroraMove 20s ease-in-out infinite alternate-reverse;
            pointer-events: none; z-index: 1;
        }

        /* 4. BINTANG BERKEDIP NYATA */
        @keyframes twinkle {
            0%, 100% { opacity: 0.2; transform: scale(0.6); box-shadow: 0 0 0px #fff; }
            50% { opacity: 1; transform: scale(1.3) rotate(45deg); box-shadow: 0 0 12px #fff; }
        }
        .star {
            position: absolute; background-color: #fff; border-radius: 50%;
            animation: twinkle var(--duration) ease-in-out infinite;
            animation-delay: var(--delay); z-index: 1; pointer-events: none;
        }

        /* 5. BURUNG TERBANG */
        @keyframes flyAcross {
            0% { transform: translateX(-10vw) translateY(10vh) scale(0.6); }
            100% { transform: translateX(110vw) translateY(-5vh) scale(0.8); }
        }
        .birds {
            position: absolute; top: 22%; left: 0; width: 100px;
            animation: flyAcross 25s linear infinite; z-index: 1; opacity: 0.75;
        }
    </style>
</head>
<body class="min-h-screen w-full flex items-center justify-center lg:justify-end lg:pr-32 p-4 sm:p-6 relative overflow-hidden bg-[#040b16] text-white">

    <!-- LAYER 0: Background Asli Cloudinary (Dengan efek zoom lambat) -->
    <div class="absolute inset-0 bg-cover bg-center bg-no-repeat z-0 animate-living-bg" 
         style="background-image: url('https://res.cloudinary.com/fnf8f1pm/image/upload/v1784175125/background_lqct46.jpg');">
    </div>

    <!-- LAYER 1: Cahaya Aurora & Alam Bergerak (Overlays) -->
    <div class="aurora-light"></div>
    <div class="aurora-light-2"></div>

    <!-- LAYER 2: Bintang Berkedip -->
    <div class="star w-1 h-1 top-[10%] left-[20%]" style="--duration: 3s; --delay: 0s;"></div>
    <div class="star w-1.5 h-1.5 top-[15%] left-[50%]" style="--duration: 4s; --delay: 1s;"></div>
    <div class="star w-1 h-1 top-[8%] left-[75%]" style="--duration: 2.5s; --delay: 0.5s;"></div>
    <div class="star w-2 h-2 top-[22%] left-[30%]" style="--duration: 5s; --delay: 2s; background-color: #a7f3d0;"></div>
    <div class="star w-1 h-1 top-[18%] left-[85%]" style="--duration: 3.5s; --delay: 1.5s;"></div>
    <div class="star w-1.5 h-1.5 top-[6%] left-[40%]" style="--duration: 4.5s; --delay: 0.2s;"></div>

    <!-- LAYER 3: Siluet Burung Terbang -->
    <div class="birds flex space-x-3 text-[#030914]">
        <svg class="w-6 h-6 fill-current transform -rotate-12" viewBox="0 0 24 24"><path d="M21,9 C18,9 15,10 12,13 C9,10 6,9 3,9 C2,9 1,9.5 1,10 C1,10.5 2,11 3,11 C5,11 8,12 10,15 C10.5,15.8 11.2,16 12,16 C12.8,16 13.5,15.8 14,15 C16,12 19,11 21,11 C22,11 23,10.5 23,10 C23,9.5 22,9 21,9 Z"/></svg>
        <svg class="w-4 h-4 fill-current transform -rotate-6 mt-3" viewBox="0 0 24 24"><path d="M21,9 C18,9 15,10 12,13 C9,10 6,9 3,9 C2,9 1,9.5 1,10 C1,10.5 2,11 3,11 C5,11 8,12 10,15 C10.5,15.8 11.2,16 12,16 C12.8,16 13.5,15.8 14,15 C16,12 19,11 21,11 C22,11 23,10.5 23,10 C23,9.5 22,9 21,9 Z"/></svg>
    </div>

    <!-- LAYER 4: Container Form Utama (Lebih Kecil: max-w-[310px]) -->
    <div class="relative w-full max-w-[310px] z-20 animate-[translate-y_1s_ease-out]">
        
        <!-- Cincin Cahaya -->
        <div class="absolute -top-5 -right-8 w-16 h-16 border-[1.5px] border-white/40 rounded-full shadow-[0_0_15px_rgba(255,255,255,0.4)] pointer-events-none z-20"></div>
        <div class="absolute -bottom-8 -left-6 w-24 h-24 border-[1.5px] border-white/30 rounded-full shadow-[0_0_20px_rgba(255,255,255,0.3)] pointer-events-none z-0"></div>

        <!-- Panel Kaca Glassmorphism -->
        <div class="glass-panel p-7 relative z-10">
            
            <!-- Teks RENTIFY dengan Kilauan Bintang -->
            <div class="text-center mb-6 relative">
                <i class="fas fa-star absolute top-0 left-[38px] text-[8px] text-blue-200 animate-pulse"></i>
                <i class="fas fa-star absolute -top-1 right-[45px] text-[10px] text-white animate-pulse" style="animation-delay: 1s;"></i>
                
                <h1 class="text-3xl font-extrabold tracking-widest text-transparent bg-clip-text bg-gradient-to-b from-white via-white to-white/40 drop-shadow-[0_0_12px_rgba(255,255,255,0.4)] mb-3">
                    RENTIFY
                </h1>
                <h2 class="text-xl font-bold tracking-tight mb-1 text-white/95">Eksplorasi Dimulai</h2>
                <p class="text-[10px] text-gray-300 font-medium">Masuk untuk Petualangan Anda.</p>
            </div>

            <!-- Form Login -->
            <form action="/login" method="POST" class="space-y-4">
                @csrf

                <!-- Input Email -->
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-gray-400 text-xs">
                        <i class="far fa-envelope"></i>
                    </span>
                    <input type="email" name="email" required placeholder="Email" 
                        class="input-glass w-full pl-10 pr-4 py-3 rounded-full text-[13px] text-white placeholder-gray-400/80 focus:outline-none">
                </div>

                <!-- Input Password -->
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-gray-400 text-xs">
                        <i class="fas fa-lock"></i>
                    </span>
                    <input type="password" id="passwordField" name="password" required placeholder="Password" 
                        class="input-glass w-full pl-10 pr-10 py-3 rounded-full text-[13px] text-white placeholder-gray-400/80 focus:outline-none">
                    <button type="button" onclick="togglePassword()" class="absolute inset-y-0 right-0 flex items-center pr-4 text-gray-400 hover:text-white transition">
                        <i id="eyeIcon" class="far fa-eye-slash text-[11px]"></i>
                    </button>
                </div>

                <!-- Tombol Masuk Glow Effect (Lebih menyala saat disentuh) -->
                <div class="pt-3">
                    <button type="submit" class="w-full bg-[#0070F0] hover:bg-[#1C82FF] text-white font-semibold py-3 rounded-full transition-all duration-300 shadow-[0_0_15px_rgba(0,112,240,0.5)] hover:shadow-[0_0_30px_rgba(0,112,240,0.9)] text-sm tracking-wide">
                        Masuk Sekarang
                    </button>
                </div>
            </form>

            <!-- Link Daftar Customer & Vendor -->
            <div class="mt-6 text-center space-y-2.5">
                <p class="text-[11px] text-gray-300">
                    <a href="/register" class="text-white hover:text-blue-300 transition underline underline-offset-2">Daftar di sini</a>
                </p>
                <p class="text-[11px]">
                    <a href="/vendor/register" class="font-semibold text-[#facc15] hover:text-yellow-300 transition underline underline-offset-2 decoration-[#facc15]/50">Daftar menjadi bagian dari rentify</a>
                </p>
            </div>
            
            <!-- Label Super Control -->
            <div class="mt-6 flex justify-center">
                <span class="px-4 py-1 rounded-full border border-white/20 text-[9px] uppercase tracking-widest text-gray-400 font-semibold bg-white/5">
                    
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