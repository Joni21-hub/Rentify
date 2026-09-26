<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun Customer — Rentify</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- ================================================================= -->
    <!-- PWA RENTIFY META TAGS -->
    <!-- ================================================================= -->
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#040b16">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <link rel="apple-touch-icon" href="https://res.cloudinary.com/fnf8f1pm/image/upload/v1784260498/ukuran_satu_g4ihwu.png">

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; }

        /* KACA SANGAT TRANSPARAN & BENING */
        .glass-panel {
            background: rgba(255, 255, 255, 0.01);
            backdrop-filter: blur(5px);
            -webkit-backdrop-filter: blur(5px);
            border: 1px solid rgba(255, 255, 255, 0.15);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2), inset 0 0 15px rgba(255, 255, 255, 0.05);
            border-radius: 2rem;
        }

        /* TOMBOL MASUK BERGRADASI & ANIMASI CAHAYA MENGALIR */
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

        /* BINTANG BERKEDIP NYATA */
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

        /* AURORA BERGERAK HALUS */
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

        /* GAMBAR LATAR BERNAPAS */
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

    <!-- BINTANG BERKEDIP -->
    <div class="star-point top-[8%] left-[15%]" style="--duration: 3s; --delay: 0s;"></div>
    <div class="star-point top-[15%] left-[30%]" style="--duration: 4s; --delay: 1s;"></div>
    <div class="star-point top-[6%] left-[60%]" style="--duration: 2.5s; --delay: 0.5s;"></div>
    <div class="star-point top-[20%] left-[50%]" style="--duration: 3.5s; --delay: 2s; background-color: #a7f3d0;"></div>
    <div class="star-point top-[12%] left-[80%]" style="--duration: 4s; --delay: 1.2s;"></div>
    <div class="star-point top-[25%] left-[85%]" style="--duration: 3s; --delay: 0.2s;"></div>

    <!-- CONTAINER FORM -->
    <div class="relative w-full max-w-[360px] z-20 my-[5vh] lg:my-0">
        
        <!-- CINCIN CAHAYA MELINGKARI PANEL KACA -->
        <div class="absolute -top-4 -right-4 w-20 h-20 border-[1px] border-white/40 rounded-full shadow-[0_0_15px_rgba(255,255,255,0.4)] pointer-events-none z-0"></div>
        <div class="absolute -bottom-6 -left-6 w-24 h-24 border-[1px] border-white/30 rounded-full shadow-[0_0_20px_rgba(255,255,255,0.2)] pointer-events-none z-0"></div>

        <!-- PANEL KACA FORM -->
        <div class="glass-panel p-6 sm:p-8 relative z-10">
            
            <div class="text-center mb-5">
                <h1 class="text-2xl font-extrabold tracking-widest text-transparent bg-clip-text bg-gradient-to-b from-white via-white to-white/40 drop-shadow-[0_0_12px_rgba(255,255,255,0.4)] mb-2">
                    RENTIFY
                </h1>
                <p class="text-[11px] text-gray-300 font-medium">Mulai petualangan serumu bersama kami.</p>
            </div>

            @if ($errors->any())
                <div class="mb-4 bg-rose-50/10 border border-rose-300/50 text-rose-200 px-4 py-3 rounded-lg text-[10px] font-bold">
                    <ul class="list-disc pl-4 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('register') }}" method="POST" class="space-y-3">
                @csrf 

                <!-- TOMBOL LOGIN GOOGLE -->
                <a href="{{ route('google.login') }}" class="w-full flex items-center justify-center gap-3 py-2.5 bg-white/10 hover:bg-white/20 border border-white/20 rounded-full transition-all font-bold text-white text-[11px] backdrop-blur-md mb-3">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" class="w-4 h-4">
                        <path fill="#FFC107" d="M43.611,20.083H42V20H24v8h11.303c-1.649,4.657-6.08,8-11.303,8c-6.627,0-12-5.373-12-12c0-6.627,5.373-12,12-12c3.059,0,5.842,1.154,7.961,3.039l5.657-5.657C34.046,6.053,29.268,4,24,4C12.955,4,4,12.955,4,24c0,11.045,8.955,20,20,20c11.045,0,20-8.955,20-20C44,22.659,43.862,21.35,43.611,20.083z"></path>
                        <path fill="#FF3D00" d="M6.306,14.691l6.571,4.819C14.655,15.108,18.961,12,24,12c3.059,0,5.842,1.154,7.961,3.039l5.657-5.657C34.046,6.053,29.268,4,24,4C16.318,4,9.656,8.337,6.306,14.691z"></path>
                        <path fill="#4CAF50" d="M24,44c5.166,0,9.86-1.977,13.409-5.192l-6.19-5.238C29.211,35.091,26.715,36,24,36c-5.202,0-9.619-3.317-11.283-7.946l-6.522,5.025C9.505,39.556,16.227,44,24,44z"></path>
                        <path fill="#1976D2" d="M43.611,20.083H42V20H24v8h11.303c-0.792,2.237-2.231,4.166-4.087,5.571c0.001-0.001,0.002-0.001,0.003-0.002l6.19,5.238C36.971,39.205,44,34,44,24C44,22.659,43.862,21.35,43.611,20.083z"></path>
                    </svg>
                    Lanjutkan dengan Google
                </a>

                <div class="flex items-center gap-3 my-3">
                    <div class="h-px bg-white/20 flex-1"></div>
                    <span class="text-[9px] font-bold text-white/50 tracking-widest">ATAU DAFTAR MANUAL</span>
                    <div class="h-px bg-white/20 flex-1"></div>
                </div>

                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-gray-400 text-sm"><i class="fa-solid fa-user"></i></span>
                    <input type="text" name="name" value="{{ old('name') }}" required class="input-glass w-full pl-10 pr-4 py-2.5 rounded-full text-xs text-white placeholder-gray-400/80 focus:outline-none" placeholder="Nama Lengkap">
                </div>

                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-gray-400 text-sm"><i class="fa-solid fa-envelope"></i></span>
                    <input type="email" name="email" value="{{ old('email') }}" required class="input-glass w-full pl-10 pr-4 py-2.5 rounded-full text-xs text-white placeholder-gray-400/80 focus:outline-none" placeholder="Alamat Email">
                </div>

                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-emerald-400 text-sm"><i class="fa-brands fa-whatsapp"></i></span>
                    <input type="text" name="whatsapp" value="{{ old('whatsapp') }}" required class="input-glass w-full pl-10 pr-4 py-2.5 rounded-full text-xs text-white placeholder-gray-400/80 focus:outline-none" placeholder="Nomor WhatsApp">
                </div>

                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-gray-400 text-sm"><i class="fa-solid fa-lock"></i></span>
                    <input type="password" id="passInput" name="password" required class="input-glass w-full pl-10 pr-10 py-2.5 rounded-full text-xs text-white placeholder-gray-400/80 focus:outline-none" placeholder="Kata Sandi (Min 8)">
                    <button type="button" onclick="togglePass('passInput', 'eye1')" class="absolute inset-y-0 right-0 flex items-center pr-4 text-gray-400 hover:text-white transition"><i id="eye1" class="fa-regular fa-eye-slash text-xs"></i></button>
                </div>

                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-gray-400 text-sm"><i class="fa-solid fa-shield-halved"></i></span>
                    <input type="password" id="passConfirmInput" name="password_confirmation" required class="input-glass w-full pl-10 pr-10 py-2.5 rounded-full text-xs text-white placeholder-gray-400/80 focus:outline-none" placeholder="Konfirmasi Kata Sandi">
                    <button type="button" onclick="togglePass('passConfirmInput', 'eye2')" class="absolute inset-y-0 right-0 flex items-center pr-4 text-gray-400 hover:text-white transition"><i id="eye2" class="fa-regular fa-eye-slash text-xs"></i></button>
                </div>

                <!-- KOTAK CENTANG S&K TERKUNCI -->
                <div class="flex items-start pt-1 pb-1">
                    <div class="flex items-center h-4 relative group">
                        <input id="terms_customer" name="terms" type="checkbox" required disabled
                            class="w-3 h-3 border border-gray-400 rounded focus:ring-2 focus:ring-blue-300 bg-transparent checked:bg-blue-600 transition opacity-50 cursor-not-allowed">
                    </div>
                    <div class="ml-2 text-[9px]">
                        <label class="font-medium text-gray-300 leading-tight block">
                            Menyetujui 
                            <button type="button" onclick="openModal()" class="font-bold text-blue-400 hover:text-blue-300 underline transition cursor-pointer">Syarat & Ketentuan</button>.
                        </label>
                        <p id="scrollAlert" class="text-[8px] text-rose-400 font-bold mt-0.5 animate-pulse">
                            <i class="fa-solid fa-lock mr-0.5"></i> Baca dokumen untuk membuka centang
                        </p>
                    </div>
                </div>

                <div class="pt-2">
                    <button type="submit" class="btn-gradient-animated w-full text-white font-bold py-3 rounded-full text-[11px] tracking-widest uppercase flex items-center justify-center gap-2">
                        <span>Daftar Sekarang</span>
                    </button>
                </div>
            </form>

            <div class="mt-4 text-center">
                <p class="text-[10px] text-gray-300 font-medium">Sudah punya akun? 
                    <a href="{{ route('login') }}" class="text-blue-400 font-bold hover:text-blue-300 hover:underline transition ml-1">Masuk di sini</a>
                </p>
            </div>
            
            <div class="mt-4 flex justify-center">
                <span class="px-4 py-1 rounded-full border border-white/20 text-[8px] uppercase tracking-widest text-gray-300 font-semibold bg-white/5">
                    ---------
                </span>
            </div>
        </div>
    </div>

    <!-- MODAL POPUP SYARAT & KETENTUAN (TEMA GELAP) -->
    <div id="termsModal" class="fixed inset-0 bg-[#040b16]/90 hidden flex items-center justify-center z-50 p-4 backdrop-blur-md">
        <div class="glass-panel max-w-2xl w-full flex flex-col shadow-2xl max-h-[85vh] overflow-hidden">
            <!-- Header Modal -->
            <div class="bg-white/5 px-6 py-4 flex justify-between items-center shrink-0 border-b border-white/10">
                <div>
                    <h3 class="font-black text-white text-base tracking-wider">Syarat & Ketentuan</h3>
                    <p class="text-[9px] text-gray-400">Silakan gulir hingga akhir untuk menyetujui.</p>
                </div>
                <button onclick="closeModal()" class="text-gray-400 hover:text-white transition text-xl">&times;</button>
            </div>
            
            <!-- Konten Bisa Di-scroll -->
            <div id="termsContent" onscroll="checkScroll(this)" class="p-6 overflow-y-auto space-y-4 text-[11px] text-gray-300 leading-relaxed custom-scrollbar">
                <p>Selamat datang di Rentify. Dengan mendaftar dan menggunakan platform ini, Anda menyatakan tunduk dan terikat pada syarat dan ketentuan berikut sesuai dengan hukum yang berlaku di Republik Indonesia.</p>

                <div>
                    <h3 class="text-[12px] font-bold text-white mb-1">1. Status dan Peran Platform</h3>
                    <ul class="list-disc pl-4 space-y-1">
                        <li>Rentify adalah perantara (Penyelenggara Sistem Elektronik) yang mempertemukan pemilik barang (Vendor) dengan penyewa (Customer).</li>
                        <li>Rentify tidak memiliki atau menyimpan barang yang disewakan.</li>
                    </ul>
                </div>

                <div>
                    <h3 class="text-[12px] font-bold text-white mb-1">2. Pelepasan Tanggung Jawab</h3>
                    <ul class="list-disc pl-4 space-y-1">
                        <li>Rentify terlepas dari tanggung jawab hukum atas kerusakan, kehilangan, atau malfungsi barang sewaan.</li>
                        <li>Sengketa akibat penipuan atau penyalahgunaan adalah urusan langsung antara Vendor dan Customer.</li>
                    </ul>
                </div>
                
                <div class="h-10"></div> <!-- Ruang ekstra agar scroll terdeteksi mentok -->
            </div>
            
            <!-- Footer Modal -->
            <div class="p-4 border-t border-white/10 bg-white/5 flex justify-between items-center shrink-0">
                <span id="scrollProgress" class="text-[9px] font-bold text-rose-400 animate-pulse"><i class="fa-solid fa-arrow-down mr-1"></i> Gulir ke bawah</span>
                <button onclick="closeModal()" class="bg-white/10 hover:bg-white/20 text-white font-bold py-1.5 px-4 rounded-lg transition text-[10px]">Tutup</button>
            </div>
        </div>
    </div>

    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: rgba(255, 255, 255, 0.05); }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(255, 255, 255, 0.2); border-radius: 10px; }
    </style>

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

        function openModal() { document.getElementById('termsModal').classList.remove('hidden'); }
        function closeModal() { document.getElementById('termsModal').classList.add('hidden'); }

        function checkScroll(element) {
            if (element.scrollHeight - element.scrollTop <= element.clientHeight + 15) {
                const checkbox = document.getElementById('terms_customer');
                checkbox.disabled = false;
                checkbox.classList.remove('opacity-50', 'cursor-not-allowed');
                
                const alertText = document.getElementById('scrollAlert');
                alertText.innerHTML = '<i class="fa-solid fa-check-circle mr-0.5"></i> Syarat dibaca, silakan centang.';
                alertText.classList.replace('text-rose-400', 'text-emerald-400');
                alertText.classList.remove('animate-pulse');

                const progressText = document.getElementById('scrollProgress');
                progressText.innerHTML = '<i class="fa-solid fa-check text-emerald-400 mr-1"></i> Disetujui';
                progressText.classList.remove('text-rose-400', 'animate-pulse');
                progressText.classList.add('text-gray-400');
            }
        }
    </script>
</body>
</html>