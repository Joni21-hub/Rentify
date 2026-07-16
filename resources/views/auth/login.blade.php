<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk ke Rentify — Eksplorasi Dimulai</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        navy: '#060B26',
                        brandBlue: '#1A44A0',
                        sky: '#4A90E2',
                        neonMint: '#00F5D4',
                        neonOrange: '#FF9F1C'
                    }
                }
            }
        }
    </script>
    <style>
        /* 1. ANIMASI GAMBAR ASLI BERGERAK HALUS (CINEMATIC BREATHING / SLOW ZOOM) */
        @keyframes cinematicZoom {
            0% { transform: scale(1) translate(0, 0); }
            50% { transform: scale(1.04) translate(-10px, -5px); }
            100% { transform: scale(1) translate(0, 0); }
        }
        .animate-living-bg {
            animation: cinematicZoom 20s ease-in-out infinite alternate;
        }

        /* 2. EFEK CAHAYA AURORA DALAM GAMBAR IKUT BERGERAK & MENYALA (MIX BLEND SCREEN) */
        @keyframes auroraPulse {
            0%, 100% { opacity: 0.3; transform: scaleY(1) translateX(0); filter: hue-rotate(0deg); }
            50% { opacity: 0.7; transform: scaleY(1.15) translateX(20px); filter: hue-rotate(15deg); }
        }
        .aurora-live-overlay {
            animation: auroraPulse 8s ease-in-out infinite alternate;
            mix-blend-mode: screen; /* Rahasia agar cahaya menyatu dengan warna gambar asli di belakangnya */
        }

        /* 3. ANIMASI BINTANG BERKEDIP */
        @keyframes twinkle {
            0%, 100% { opacity: 0.1; transform: scale(0.7); }
            50% { opacity: 1; transform: scale(1.4); }
        }
        .star { animation: twinkle var(--duration, 3s) ease-in-out infinite; animation-delay: var(--delay, 0s); }

        /* 4. ANIMASI KILATAN TOMBOL (SHIMMER) */
        @keyframes shimmer {
            100% { left: 125%; }
        }
        .btn-shimmer { position: relative; overflow: hidden; }
        .btn-shimmer::after {
            content: ''; position: absolute; top: -50%; left: -60%;
            width: 30%; height: 200%;
            background: rgba(255, 255, 255, 0.3);
            transform: rotate(30deg);
            animation: shimmer 4s infinite ease-in-out;
        }

        /* 5. ANIMASI MUNCUL FORM (FADE & ZOOM) */
        @keyframes premiumEntry {
            from { opacity: 0; transform: scale(0.88) translateY(30px); filter: blur(12px); }
            to { opacity: 1; transform: scale(1) translateY(0); filter: blur(0); }
        }
        .animate-form-entry {
            animation: premiumEntry 1.2s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        /* 6. EFEK GLOW KACA INTENS (GLASSMORPHISM) */
        .glass-panel {
            background: rgba(10, 17, 40, 0.45);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1px solid rgba(255, 255, 255, 0.18);
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.6), 0 0 40px rgba(0, 245, 212, 0.15);
        }
        .glass-panel:hover {
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.7), 0 0 60px rgba(0, 245, 212, 0.25);
        }
    </style>
</head>
<body class="bg-[#03071e] min-h-screen w-full flex items-center justify-center lg:justify-end lg:pr-20 xl:pr-32 p-4 sm:p-6 relative overflow-hidden font-sans selection:bg-neonMint selection:text-navy">

    <!-- ================================================================= -->
    <!-- LAYER 1: GAMBAR ASLI ORANG DUDUK (DENGAN EFEK BERGERAK/BERNAPAS) -->
    <!-- ================================================================= -->
    
    <!-- PENTING: Ganti link di dalam url(...) di bawah ini dengan link Cloudinary gambar ilustrasimu! -->
    <div class="absolute inset-0 bg-cover bg-center bg-no-repeat z-0 animate-living-bg" 
         style="background-image: url('https://res.cloudinary.com/fnf8f1pm/image/upload/v1784175125/background_lqct46.jpg');">
    </div>

    <!-- Layer Penggelap Tipis agar Form Kaca Tetap Mudah Dibaca -->
    <div class="absolute inset-0 bg-gradient-to-r from-black/20 via-transparent to-black/60 z-1 pointer-events-none"></div>

    <!-- ================================================================= -->
    <!-- LAYER 2: EFEK MENGHIDUPKAN GAMBAR (CAHAYA AURORA BERGERAK & BINTANG) -->
    <!-- ================================================================= -->

    <!-- Overlay Cahaya Aurora Hidup (Menyatu dengan aurora di gambar asli) -->
    <div class="absolute top-0 inset-x-0 h-3/4 bg-gradient-to-r from-emerald-400/30 via-teal-300/30 to-cyan-400/30 blur-[90px] pointer-events-none aurora-live-overlay z-1"></div>
    <div class="absolute top-[10%] left-[15%] w-2/3 h-1/2 bg-gradient-to-br from-blue-500/20 via-indigo-500/20 to-neonMint/30 rounded-full blur-[100px] pointer-events-none aurora-live-overlay z-1" style="animation-delay: -3s;"></div>

    <!-- Bintang Berkedip Nyata di Langit -->
    <div class="absolute inset-0 z-1 pointer-events-none">
        <div class="star absolute top-[12%] left-[18%] w-1 h-1 bg-white rounded-full shadow-[0_0_8px_white]" style="--duration: 2.2s; --delay: 0.1s;"></div>
        <div class="star absolute top-[22%] left-[35%] w-1.5 h-1.5 bg-cyan-200 rounded-full shadow-[0_0_10px_cyan]" style="--duration: 3.8s; --delay: 0.8s;"></div>
        <div class="star absolute top-[8%] left-[55%] w-1 h-1 bg-white rounded-full" style="--duration: 2.9s; --delay: 0.4s;"></div>
        <div class="star absolute top-[18%] left-[72%] w-2 h-2 bg-emerald-200 rounded-full shadow-[0_0_12px_#00F5D4]" style="--duration: 3.3s; --delay: 1.2s;"></div>
        <div class="star absolute top-[28%] left-[25%] w-1 h-1 bg-white rounded-full" style="--duration: 4.1s; --delay: 1.9s;"></div>
        <div class="star absolute top-[15%] right-[20%] w-1.5 h-1.5 bg-white rounded-full shadow-[0_0_8px_white]" style="--duration: 3.5s; --delay: 0.6s;"></div>
    </div>

    <!-- ================================================================= -->
    <!-- FORM LOGIN GLASSMORPHISM (POSISI TENGAH DI HP, KANAN DI LAPTOP) -->
    <!-- ================================================================= -->
    
    <div class="animate-form-entry glass-panel p-8 md:p-10 rounded-[2.5rem] max-w-md w-full z-10 relative transition-all duration-500">
        
        <!-- Logo & Judul Eksplorasi -->
        <div class="flex flex-col items-center justify-center mb-6 text-center">
            <div class="relative group mb-3">
                <div class="absolute -inset-2 bg-gradient-to-r from-cyan-400 to-blue-600 rounded-full blur-md opacity-40 group-hover:opacity-80 transition duration-500"></div>
                <img src="https://res.cloudinary.com/fnf8f1pm/image/upload/v1784128533/logo_lzsrcn.png" alt="Logo Rentify" class="w-16 h-16 sm:w-20 sm:h-20 object-contain relative z-10 drop-shadow-[0_4px_10px_rgba(0,0,0,0.5)]">
            </div>
            
            <h1 class="text-2xl sm:text-3xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-white via-slate-100 to-cyan-300 tracking-tight">
                Eksplorasi Dimulai
            </h1>
            <p class="text-xs sm:text-sm text-cyan-200/80 mt-1 font-medium tracking-wide">
                Masuk untuk Petualangan Anda.
            </p>
        </div>

        <!-- Form Input -->
        <form action="/login" method="POST" class="space-y-4">
            @csrf

            <!-- Email -->
            <div class="group">
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400 group-focus-within:text-neonMint transition">
                        <i class="far fa-envelope"></i>
                    </span>
                    <input type="email" name="email" required placeholder="Email" 
                        class="w-full pl-11 pr-4 py-3.5 bg-navy/50 hover:bg-navy/70 border border-white/15 rounded-2xl text-sm text-white placeholder-slate-400 focus:outline-none focus:border-neonMint focus:bg-navy/80 focus:ring-4 focus:ring-neonMint/15 transition-all duration-300 font-medium shadow-inner">
                </div>
            </div>

            <!-- Kata Sandi -->
            <div class="group">
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400 group-focus-within:text-sky transition">
                        <i class="fas fa-lock"></i>
                    </span>
                    
                    <input type="password" id="passwordField" name="password" required placeholder="Password" 
                        class="w-full pl-11 pr-12 py-3.5 bg-navy/50 hover:bg-navy/70 border border-white/15 rounded-2xl text-sm text-white placeholder-slate-400 focus:outline-none focus:border-sky focus:bg-navy/80 focus:ring-4 focus:ring-sky/15 transition-all duration-300 font-medium shadow-inner">
                    
                    <button type="button" onclick="togglePassword()" class="absolute inset-y-0 right-0 flex items-center pr-4 text-slate-400 hover:text-white transition">
                        <i id="eyeIcon" class="far fa-eye"></i>
                    </button>
                </div>
                <div class="flex justify-end mt-1.5">
                    <a href="#" class="text-[11px] font-semibold text-cyan-300/80 hover:text-neonMint transition drop-shadow">Lupa Kata Sandi?</a>
                </div>
            </div>

            <!-- Remember Me -->
            <div class="flex items-center justify-between pb-1">
                <label class="flex items-center cursor-pointer select-none text-xs font-medium text-slate-300 hover:text-white transition">
                    <input type="checkbox" name="remember" class="w-4 h-4 rounded border-white/20 bg-navy/60 text-brandBlue focus:ring-0 mr-2 checked:bg-neonMint checked:border-neonMint transition">
                    Simpan sesi login
                </label>
            </div>

            <!-- Tombol Masuk Glowing -->
            <button type="submit" class="btn-shimmer w-full bg-gradient-to-r from-blue-600 via-indigo-600 to-cyan-500 hover:from-cyan-500 hover:via-indigo-600 hover:to-blue-600 text-white font-bold py-4 rounded-2xl transition-all duration-500 shadow-[0_0_25px_rgba(0,245,212,0.4)] hover:shadow-[0_0_40px_rgba(0,245,212,0.8)] transform hover:-translate-y-0.5 active:translate-y-0 text-sm sm:text-base flex items-center justify-center space-x-2 border border-white/20">
                <span class="tracking-wider">Masuk Sekarang</span> 
            </button>
        </form>

        <!-- Link Daftar Footer -->
        <div class="mt-6 pt-5 border-t border-white/10 text-center space-y-2.5">
            <p class="text-xs text-slate-300 font-medium">
                <a href="/register" class="font-bold text-cyan-300 hover:text-white hover:underline transition">Daftar di sini</a> (customer)
            </p>
            <p class="text-xs text-slate-300 font-medium">
                <a href="/vendor/register" class="font-bold text-amber-400 hover:text-amber-300 hover:underline transition flex items-center justify-center gap-1">
                    <span>Gabung Jadi Vendor</span>
                    <i class="fas fa-sparkles text-[10px] animate-bounce"></i>
                </a>
            </p>
        </div>
        
        <!-- Label Super Control -->
        <div class="mt-4 flex justify-center">
            <span class="px-3 py-1 rounded-full bg-white/5 border border-white/10 text-[10px] uppercase tracking-widest text-slate-400 font-semibold backdrop-blur-md">
                Super Control
            </span>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordField = document.getElementById('passwordField');
            const eyeIcon = document.getElementById('eyeIcon');
            
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                eyeIcon.classList.remove('fa-eye');
                eyeIcon.classList.add('fa-eye-slash');
            } else {
                passwordField.type = 'password';
                eyeIcon.classList.remove('fa-eye-slash');
                eyeIcon.classList.add('fa-eye');
            }
        }
    </script>

</body>
</html>