<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun Customer — Rentify</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#38bdf8">

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; }

        @keyframes gradientFlow {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        .bg-flowing {
            background: linear-gradient(-45deg, #0284c7, #38bdf8, #818cf8, #60a5fa, #0ea5e9);
            background-size: 300% 300%;
            animation: gradientFlow 15s ease infinite;
        }

        .glass-panel {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(25px);
            -webkit-backdrop-filter: blur(25px);
            border: 1px solid rgba(255, 255, 255, 0.4);
            border-top: 1px solid rgba(255, 255, 255, 0.7);
            border-left: 1px solid rgba(255, 255, 255, 0.7);
            box-shadow: 0 25px 45px rgba(0, 0, 0, 0.2);
            border-radius: 2rem;
        }

        .input-glass {
            background: rgba(255, 255, 255, 0.4);
            border: 1px solid rgba(255, 255, 255, 0.5);
            color: #0f172a;
            transition: all 0.3s ease;
        }
        .input-glass:focus {
            background: rgba(255, 255, 255, 0.7);
            border-color: #ffffff;
            box-shadow: 0 0 15px rgba(255, 255, 255, 0.5);
        }
        .input-glass::placeholder { color: rgba(15, 23, 42, 0.5); font-weight: 500; }

        .blob {
            position: absolute;
            filter: blur(80px);
            z-index: 0;
            opacity: 0.8;
            animation: float 10s ease-in-out infinite alternate;
        }
        @keyframes float {
            0% { transform: translateY(0px) scale(1); }
            100% { transform: translateY(-40px) scale(1.1); }
        }
    </style>
</head>
<body class="min-h-screen w-full flex items-center justify-center p-4 sm:p-6 relative overflow-hidden bg-flowing text-slate-800">

    <div class="blob w-[30rem] h-[30rem] bg-indigo-500 rounded-full top-[-5%] left-[-5%]" style="animation-delay: 0s;"></div>
    <div class="blob w-[25rem] h-[25rem] bg-cyan-300 rounded-full bottom-[-5%] right-[-5%]" style="animation-delay: -3s;"></div>
    <div class="blob w-[20rem] h-[20rem] bg-blue-400 rounded-full top-[30%] right-[20%]" style="animation-delay: -6s;"></div>

    <div class="w-full max-w-[380px] relative z-10 my-[2vh] lg:my-0">
        
        <div class="glass-panel p-6 sm:p-8 relative">
            
            <div class="text-center mb-6">
                <h1 class="text-3xl sm:text-4xl font-extrabold tracking-widest text-white drop-shadow-[0_5px_5px_rgba(0,0,0,0.3)] mb-1.5" style="text-shadow: 0 0 20px rgba(255,255,255,0.4);">
                    RENTIFY
                </h1>
                <p class="text-[11px] text-white/90 font-medium tracking-wide">Mulai petualangan serumu bersama kami.</p>
            </div>

            @if ($errors->any())
                <div class="mb-4 bg-rose-500/90 backdrop-blur-md border border-rose-400 text-white px-4 py-3 rounded-xl text-[10px] font-bold shadow-lg">
                    <ul class="list-disc pl-4 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('register') }}" method="POST" class="space-y-3.5">
                @csrf 

                <a href="{{ route('google.login') }}" class="w-full flex items-center justify-center gap-3 py-3 bg-white/20 hover:bg-white/30 border border-white/50 rounded-2xl transition-all font-extrabold text-white text-xs shadow-sm mb-2">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" class="w-4 h-4 bg-white rounded-full p-0.5">
                        <path fill="#FFC107" d="M43.611,20.083H42V20H24v8h11.303c-1.649,4.657-6.08,8-11.303,8c-6.627,0-12-5.373-12-12c0-6.627,5.373-12,12-12c3.059,0,5.842,1.154,7.961,3.039l5.657-5.657C34.046,6.053,29.268,4,24,4C12.955,4,4,12.955,4,24c0,11.045,8.955,20,20,20c11.045,0,20-8.955,20-20C44,22.659,43.862,21.35,43.611,20.083z"></path>
                        <path fill="#FF3D00" d="M6.306,14.691l6.571,4.819C14.655,15.108,18.961,12,24,12c3.059,0,5.842,1.154,7.961,3.039l5.657-5.657C34.046,6.053,29.268,4,24,4C16.318,4,9.656,8.337,6.306,14.691z"></path>
                        <path fill="#4CAF50" d="M24,44c5.166,0,9.86-1.977,13.409-5.192l-6.19-5.238C29.211,35.091,26.715,36,24,36c-5.202,0-9.619-3.317-11.283-7.946l-6.522,5.025C9.505,39.556,16.227,44,24,44z"></path>
                        <path fill="#1976D2" d="M43.611,20.083H42V20H24v8h11.303c-0.792,2.237-2.231,4.166-4.087,5.571c0.001-0.001,0.002-0.001,0.003-0.002l6.19,5.238C36.971,39.205,44,34,44,24C44,22.659,43.862,21.35,43.611,20.083z"></path>
                    </svg>
                    Daftar dengan Google
                </a>

                <div class="flex items-center gap-3 my-2">
                    <div class="h-px bg-white/40 flex-1"></div>
                    <span class="text-[9px] font-bold text-white tracking-widest">ATAU MANUAL</span>
                    <div class="h-px bg-white/40 flex-1"></div>
                </div>

                <div class="relative group">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-600 group-focus-within:text-blue-600 transition"><i class="fa-solid fa-user text-xs"></i></span>
                    <input type="text" name="name" value="{{ old('name') }}" required class="input-glass w-full pl-10 pr-4 py-2.5 rounded-2xl text-[11px] focus:outline-none font-bold" placeholder="Nama Lengkap">
                </div>

                <div class="relative group">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-600 group-focus-within:text-green-600 transition"><i class="fa-brands fa-whatsapp text-xs"></i></span>
                    <input type="text" name="whatsapp" value="{{ old('whatsapp') }}" required class="input-glass w-full pl-10 pr-4 py-2.5 rounded-2xl text-[11px] focus:outline-none font-bold" placeholder="No. WhatsApp">
                </div>

                <div class="relative group">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-600 group-focus-within:text-blue-600 transition"><i class="fa-solid fa-lock text-xs"></i></span>
                    <input type="password" id="passInput" name="password" required class="input-glass w-full pl-10 pr-10 py-2.5 rounded-2xl text-[11px] focus:outline-none font-bold" placeholder="Kata Sandi (Min 8)">
                    <button type="button" onclick="togglePass('passInput', 'eye1')" class="absolute inset-y-0 right-0 flex items-center pr-4 text-slate-600 hover:text-blue-600 transition"><i id="eye1" class="fa-regular fa-eye-slash text-xs"></i></button>
                </div>

                <div class="relative group">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-600 group-focus-within:text-blue-600 transition"><i class="fa-solid fa-shield-halved text-xs"></i></span>
                    <input type="password" id="passConfirmInput" name="password_confirmation" required class="input-glass w-full pl-10 pr-10 py-2.5 rounded-2xl text-[11px] focus:outline-none font-bold" placeholder="Konfirmasi Kata Sandi">
                    <button type="button" onclick="togglePass('passConfirmInput', 'eye2')" class="absolute inset-y-0 right-0 flex items-center pr-4 text-slate-600 hover:text-blue-600 transition"><i id="eye2" class="fa-regular fa-eye-slash text-xs"></i></button>
                </div>

                <div class="flex items-start pt-1 pb-1">
                    <div class="flex items-center h-4 relative group mt-0.5">
                        <input id="terms_customer" name="terms" type="checkbox" required disabled
                            class="w-3.5 h-3.5 border border-white/50 rounded focus:ring-2 focus:ring-blue-300 bg-white/30 checked:bg-blue-600 transition opacity-50 cursor-not-allowed">
                    </div>
                    <div class="ml-2 text-[10px]">
                        <label class="font-bold text-white/90 leading-tight block drop-shadow-sm">
                            Menyetujui 
                            <button type="button" onclick="openModal()" class="font-extrabold text-blue-200 hover:text-white underline transition cursor-pointer">Syarat & Ketentuan</button>.
                        </label>
                        <p id="scrollAlert" class="text-[8.5px] text-rose-300 font-extrabold mt-0.5 animate-pulse drop-shadow-sm">
                            <i class="fa-solid fa-lock mr-0.5"></i> Baca dokumen untuk membuka centang
                        </p>
                    </div>
                </div>

                <div class="pt-2">
                    <button type="submit" class="w-full bg-white hover:bg-gray-50 text-blue-600 font-extrabold py-3.5 rounded-2xl text-xs tracking-widest uppercase shadow-[0_10px_20px_rgba(0,0,0,0.15)] hover:shadow-[0_15px_25px_rgba(0,0,0,0.2)] transition-all transform hover:-translate-y-0.5 active:translate-y-0 flex justify-center items-center gap-2">
                        <span>BUAT AKUN</span>
                    </button>
                </div>
            </form>

            <div class="mt-5 text-center">
                <p class="text-[11px] text-white/90 font-medium">Sudah punya akun? 
                    <a href="{{ route('login') }}" class="text-white font-extrabold hover:text-blue-100 hover:underline transition ml-1">Masuk di sini</a>
                </p>
            </div>
            
        </div>
    </div>

    <!-- MODAL POPUP SYARAT & KETENTUAN -->
    <div id="termsModal" class="fixed inset-0 bg-black/60 hidden flex items-center justify-center z-50 p-4 backdrop-blur-md">
        <div class="bg-white/10 backdrop-blur-3xl border border-white/30 rounded-3xl max-w-2xl w-full flex flex-col shadow-2xl max-h-[85vh] overflow-hidden">
            <div class="bg-white/10 px-6 py-4 flex justify-between items-center shrink-0 border-b border-white/10">
                <div>
                    <h3 class="font-extrabold text-white text-sm tracking-wide">Syarat & Ketentuan</h3>
                    <p class="text-[10px] text-white/70">Silakan gulir hingga akhir untuk menyetujui.</p>
                </div>
                <button onclick="closeModal()" class="text-white/50 hover:text-white transition text-xl">&times;</button>
            </div>
            
            <div id="termsContent" onscroll="checkScroll(this)" class="p-6 overflow-y-auto space-y-4 text-[11px] text-white/80 leading-relaxed custom-scrollbar">
                <p>Selamat datang di Rentify. Dengan mendaftar dan menggunakan platform ini, Anda menyatakan tunduk dan terikat pada syarat dan ketentuan berikut sesuai dengan hukum yang berlaku di Republik Indonesia.</p>
                <div>
                    <h3 class="text-[12px] font-extrabold text-white mb-1">1. Status dan Peran Platform</h3>
                    <ul class="list-disc pl-4 space-y-1">
                        <li>Rentify adalah perantara (Penyelenggara Sistem Elektronik) yang mempertemukan pemilik barang (Vendor) dengan penyewa (Customer).</li>
                        <li>Rentify tidak memiliki atau menyimpan barang yang disewakan.</li>
                    </ul>
                </div>
                <div class="h-10"></div>
            </div>
            
            <div class="p-4 border-t border-white/10 bg-white/10 flex justify-between items-center shrink-0">
                <span id="scrollProgress" class="text-[10px] font-extrabold text-rose-300 animate-pulse"><i class="fa-solid fa-arrow-down mr-1"></i> Gulir ke bawah</span>
                <button onclick="closeModal()" class="bg-white hover:bg-gray-100 text-blue-600 font-extrabold py-1.5 px-4 rounded-xl transition text-[10px] shadow-lg">Tutup</button>
            </div>
        </div>
    </div>

    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 5px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: rgba(255,255,255,0.05); }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.3); border-radius: 10px; }
    </style>

    <script>
        function togglePass(inputId, eyeId) {
            const input = document.getElementById(inputId);
            const eye = document.getElementById(eyeId);
            if (input.type === 'password') {
                input.type = 'text';
                eye.classList.replace('fa-eye-slash', 'fa-eye');
            } else {
                input.type = 'password';
                eye.classList.replace('fa-eye', 'fa-eye-slash');
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
                alertText.classList.replace('text-rose-300', 'text-emerald-300');
                alertText.classList.remove('animate-pulse');

                const progressText = document.getElementById('scrollProgress');
                progressText.innerHTML = '<i class="fa-solid fa-check text-emerald-300 mr-1"></i> Disetujui';
                progressText.classList.remove('text-rose-300', 'animate-pulse');
                progressText.classList.add('text-white/80');
            }
        }
    </script>
</body>
</html>