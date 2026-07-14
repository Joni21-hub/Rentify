<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk ke Rentify</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
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
        /* Animasi Aurora Langit Malam */
        @keyframes auroraEffect {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        .animate-aurora {
            background: linear-gradient(-45deg, #060B26, #0D1B3E, #1A44A0, #0B4F6C, #060B26);
            background-size: 400% 400%;
            animation: auroraEffect 15s ease infinite;
        }

        /* Animasi Kilatan Tombol (Shimmer) */
        @keyframes shimmer {
            100% { left: 125%; }
        }
        .btn-shimmer {
            position: relative;
            overflow: hidden;
        }
        .btn-shimmer::after {
            content: '';
            position: absolute;
            top: -50%; left: -60%;
            width: 30%; height: 200%;
            background: rgba(255, 255, 255, 0.25);
            transform: rotate(30deg);
            animation: shimmer 3.5s infinite ease-in-out;
        }

        /* Entry Form Smooth Animation */
        @keyframes scaleUpBlur {
            from { opacity: 0; transform: scale(0.95) translateY(20px); filter: blur(10px); }
            to { opacity: 1; transform: scale(1) translateY(0); filter: blur(0); }
        }
        .animate-premium-entry {
            animation: scaleUpBlur 1s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
    </style>
</head>
<body class="animate-aurora flex items-center justify-center min-h-screen p-4 overflow-hidden font-sans relative">

    <div class="absolute top-10 left-10 w-32 h-32 bg-sky/10 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute bottom-10 right-10 w-44 h-44 bg-brandBlue/20 rounded-full blur-3xl pointer-events-none"></div>

    <div class="animate-premium-entry bg-slate-900/50 backdrop-blur-xl p-8 md:p-10 rounded-[2.5rem] shadow-[0_0_50px_rgba(0,0,0,0.6)] max-w-md w-full border border-white/10 z-10 relative">
        
       <!-- Logo -->
<div class="flex justify-center mb-4">
    <img src="{{ asset('images/logo_rentify.png') }}" alt="Logo Rentify" class="w-20 h-20 object-contain">
</div>

        <form action="/login" method="POST" class="space-y-5">
            
            @csrf

            <div class="group">
                <label class="block text-[11px] font-bold uppercase tracking-widest text-slate-400 mb-2 group-focus-within:text-neonMint transition">Alamat Email</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400 group-focus-within:text-neonMint transition">
                        <i class="far fa-envelope"></i>
                    </span>
                    <input type="email" name="email" required placeholder="nama@email.com" 
                        class="w-full pl-11 pr-4 py-3.5 bg-navy/60 border border-white/10 rounded-2xl text-sm text-white placeholder-slate-500 focus:outline-none focus:border-neonMint focus:ring-4 focus:ring-neonMint/10 transition duration-300 font-medium">
                </div>
            </div>

            <div class="group">
                <div class="flex justify-between items-center mb-2">
                    <label class="block text-[11px] font-bold uppercase tracking-widest text-slate-400 group-focus-within:text-sky transition">Kata Sandi</label>
                    <a href="#" class="text-xs font-bold text-sky hover:text-neonMint transition">Lupa?</a>
                </div>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400 group-focus-within:text-sky transition">
                        <i class="fas fa-lock"></i>
                    </span>
                    
                    <input type="password" id="passwordField" name="password" required placeholder="••••••••" 
                        class="w-full pl-11 pr-12 py-3.5 bg-navy/60 border border-white/10 rounded-2xl text-sm text-white placeholder-slate-500 focus:outline-none focus:border-sky focus:ring-4 focus:ring-sky/10 transition duration-300 font-medium">
                    
                    <button type="button" onclick="togglePassword()" class="absolute inset-y-0 right-0 flex items-center pr-4 text-slate-400 hover:text-white transition">
                        <i id="eyeIcon" class="far fa-eye"></i>
                    </button>
                </div>
            </div>

            <div class="flex items-center justify-between pt-1">
                <label class="flex items-center cursor-pointer select-none text-xs font-semibold text-slate-400 hover:text-slate-200 transition">
                    <input type="checkbox" name="remember" class="w-4 h-4 rounded border-white/10 bg-navy/60 text-brandBlue focus:ring-0 mr-2 checked:bg-sky">
                    Simpan sesi login
                </label>
            </div>

            <button type="submit" class="btn-shimmer w-full bg-gradient-to-r from-brandBlue to-sky hover:from-sky hover:to-brandBlue text-white font-bold py-4 rounded-2xl transition duration-500 shadow-lg shadow-sky/10 hover:shadow-sky/30 transform hover:-translate-y-0.5 active:translate-y-0 text-sm flex items-center justify-center space-x-2 border border-white/10 mt-2">
                <span class="tracking-wider">login</span> 
                <i class="fas fa-arrow-right text-xs"></i>
            </button>
        </form>

        <div class="mt-8 pt-6 border-t border-white/5 text-center space-y-3">
            <p class="text-xs text-slate-400">
                Belum mendaftar? <a href="/register" class="font-bold text-neonMint hover:underline">Buat Akun Rentify</a>
            </p>
            <p class="text-xs text-slate-400">
                Mau mejadi bagian dari rentify? <a href="/vendor/register" class="font-bold text-neonOrange hover:underline">Daftar sekarang<i class="fas fa-arrow-trend-up text-[10px] ml-0.5"></i></a>
            </p>
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