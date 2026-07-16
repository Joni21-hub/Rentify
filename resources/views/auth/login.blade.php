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
        
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        /* Efek Kaca Utama (Sangat Transparan + Blur) */
        .glass-panel {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.15);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2), inset 0 0 15px rgba(255, 255, 255, 0.05);
            border-radius: 2rem;
        }

        /* Efek Input Kapsul */
        .input-glass {
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.15);
            transition: all 0.3s ease;
        }
        .input-glass:focus {
            background: rgba(255, 255, 255, 0.1);
            border-color: #3b82f6; /* Warna biru */
            box-shadow: 0 0 15px rgba(59, 130, 246, 0.3);
        }

        /* Animasi Bintang Berkilau di tulisan RENTIFY */
        @keyframes sparkle {
            0%, 100% { opacity: 0; transform: scale(0.5); }
            50% { opacity: 1; transform: scale(1.2); }
        }
        .animate-sparkle {
            animation: sparkle 2s ease-in-out infinite;
        }
    </style>
</head>
<body class="min-h-screen w-full flex items-center justify-center lg:justify-end lg:pr-32 p-4 sm:p-6 relative overflow-hidden bg-cover bg-center bg-no-repeat text-white"
      style="background-image: url('https://res.cloudinary.com/fnf8f1pm/image/upload/v1784175125/background_lqct46.jpg');">

    <!-- Container Form dengan Cincin Cahaya -->
    <div class="relative w-full max-w-[340px] z-10 animate-[translate-y_1s_ease-out]">
        
        <!-- Cincin Cahaya Kanan Atas (Seperti di Mockup) -->
        <div class="absolute -top-5 -right-8 w-20 h-20 border-[1.5px] border-white/40 rounded-full shadow-[0_0_15px_rgba(255,255,255,0.4)] pointer-events-none z-20"></div>
        
        <!-- Cincin Cahaya Kiri Bawah (Seperti di Mockup) -->
        <div class="absolute -bottom-8 -left-6 w-28 h-28 border-[1.5px] border-white/30 rounded-full shadow-[0_0_20px_rgba(255,255,255,0.3)] pointer-events-none z-0"></div>

        <!-- Panel Kaca Glassmorphism -->
        <div class="glass-panel p-8 relative z-10">
            
            <!-- Teks RENTIFY dengan Kilauan Bintang -->
            <div class="text-center mb-6 relative">
                <!-- Bintang Kecil Kiri -->
                <i class="fas fa-star absolute top-0 left-12 text-[8px] text-blue-200 animate-sparkle"></i>
                <!-- Bintang Kecil Kanan -->
                <i class="fas fa-star absolute -top-1 right-14 text-[10px] text-white animate-sparkle" style="animation-delay: 1s;"></i>
                
                <h1 class="text-3xl font-extrabold tracking-widest text-transparent bg-clip-text bg-gradient-to-b from-white via-white to-white/40 drop-shadow-[0_0_10px_rgba(255,255,255,0.3)] mb-4">
                    RENTIFY
                </h1>
                <h2 class="text-[1.35rem] font-bold tracking-tight mb-1 text-white/95">Eksplorasi Dimulai</h2>
                <p class="text-[11px] text-gray-300 font-medium">Masuk untuk Petualangan Anda.</p>
            </div>

            <!-- Form Login -->
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

                <!-- Tombol Masuk Biru Kapsul -->
                <div class="pt-2">
                    <button type="submit" class="w-full bg-[#0070F0] hover:bg-[#005bc4] text-white font-semibold py-3 rounded-full transition-all duration-300 shadow-[0_0_20px_rgba(0,112,240,0.5)] text-sm tracking-wide">
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
                    <a href="/vendor/register" class="font-semibold text-[#facc15] hover:text-yellow-300 transition underline underline-offset-2 decoration-[#facc15]/50">menjadi bagian dari rentify</a>
                </p>
            </div>
            

    <script>
        // Fungsi untuk toggle (melihat/menyembunyikan) password
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