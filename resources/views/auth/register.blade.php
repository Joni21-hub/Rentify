<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun Customer — Rentify</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- PWA RENTIFY META TAGS -->
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#f0f9ff">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <link rel="apple-touch-icon" href="https://res.cloudinary.com/fnf8f1pm/image/upload/v1784260498/ukuran_satu_g4ihwu.png">

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Outfit:wght@700;800&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        
        .font-logo { font-family: 'Outfit', sans-serif; }

        /* ANIMASI BACKGROUND MENGALIR (SOFT BLUE) */
        @keyframes gradientFlow {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        .bg-flowing {
            background: linear-gradient(-45deg, #e0f2fe, #bae6fd, #f0f9ff, #dbeafe);
            background-size: 400% 400%;
            animation: gradientFlow 15s ease infinite;
        }

        /* EFEK BOLA CAHAYA MELAYANG (BLOBS) */
        @keyframes float {
            0%, 100% { transform: translateY(0) scale(1); }
            50% { transform: translateY(-20px) scale(1.05); }
        }
        .blob {
            position: absolute;
            filter: blur(60px);
            z-index: 0;
            opacity: 0.6;
            animation: float 10s ease-in-out infinite alternate;
        }

        /* KACA TRANSPARAN ELEGAN (LIGHT GLASSMORPHISM) */
        .glass-panel {
            background: rgba(255, 255, 255, 0.6);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.8);
            box-shadow: 0 20px 40px rgba(14, 165, 233, 0.1), inset 0 0 0 1px rgba(255, 255, 255, 0.5);
            border-radius: 1.5rem;
        }

        /* INPUT KACA (CLEAN) */
        .input-clean {
            background: rgba(255, 255, 255, 0.7);
            border: 1px solid rgba(226, 232, 240, 0.8);
            color: #334155;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .input-clean:focus {
            background: #ffffff;
            border-color: #38bdf8;
            box-shadow: 0 0 0 4px rgba(56, 189, 248, 0.15);
        }
        .input-clean::placeholder {
            color: #94a3b8;
        }

        /* TOMBOL UTAMA (MODERN GRADIENT) */
        .btn-modern {
            background: linear-gradient(135deg, #0ea5e9, #2563eb);
            box-shadow: 0 4px 15px rgba(14, 165, 233, 0.3);
            transition: all 0.3s;
        }
        .btn-modern:hover {
            box-shadow: 0 6px 20px rgba(14, 165, 233, 0.4);
            transform: translateY(-1px);
        }
        .btn-modern:active {
            transform: translateY(1px);
        }
    </style>
</head>
<body class="min-h-screen w-full flex items-center justify-center p-4 sm:p-6 relative overflow-hidden bg-flowing text-slate-800">

    <!-- EFEK BOLA CAHAYA DIBELAKANG (BLOBS) -->
    <div class="blob w-72 h-72 bg-cyan-300 rounded-full top-[5%] left-[10%]" style="animation-delay: 0s;"></div>
    <div class="blob w-96 h-96 bg-blue-200 rounded-full bottom-[5%] right-[5%]" style="animation-delay: -5s;"></div>
    <div class="blob w-64 h-64 bg-sky-200 rounded-full top-[30%] right-[20%]" style="animation-delay: -2s;"></div>

    <div class="w-full max-w-md relative z-10 my-[5vh] lg:my-0">
        
        <!-- PANEL KACA FORM -->
        <div class="glass-panel p-6 sm:p-8 relative">
            
            <div class="text-center mb-6">
                <!-- LOGO RENTIFY -->
                <div class="inline-flex items-center justify-center mb-2">
                    <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-sky-400 to-blue-600 flex items-center justify-center shadow-lg shadow-blue-500/30 mr-2.5">
                        <i class="fa-solid fa-gem text-white text-sm"></i>
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-logo font-extrabold tracking-tight text-transparent bg-clip-text bg-gradient-to-r from-slate-800 to-slate-600">
                        Rentify
                    </h1>
                </div>
                <h2 class="text-xs font-semibold text-slate-600 tracking-wide uppercase">Pendaftaran Customer</h2>
            </div>

            @if ($errors->any())
                <div class="mb-4 bg-rose-50 border border-rose-200 text-rose-600 px-4 py-3 rounded-xl text-[10px] font-medium shadow-sm">
                    <ul class="list-disc pl-4 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('register') }}" method="POST" class="space-y-3.5">
                @csrf 

                <!-- TOMBOL LOGIN GOOGLE -->
                <a href="{{ route('google.login') }}" class="w-full flex items-center justify-center gap-3 py-2.5 bg-white border border-slate-200 hover:border-slate-300 hover:bg-slate-50 rounded-xl transition-all font-bold text-slate-600 text-xs shadow-sm mb-2">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" class="w-4 h-4">
                        <path fill="#FFC107" d="M43.611,20.083H42V20H24v8h11.303c-1.649,4.657-6.08,8-11.303,8c-6.627,0-12-5.373-12-12c0-6.627,5.373-12,12-12c3.059,0,5.842,1.154,7.961,3.039l5.657-5.657C34.046,6.053,29.268,4,24,4C12.955,4,4,12.955,4,24c0,11.045,8.955,20,20,20c11.045,0,20-8.955,20-20C44,22.659,43.862,21.35,43.611,20.083z"></path>
                        <path fill="#FF3D00" d="M6.306,14.691l6.571,4.819C14.655,15.108,18.961,12,24,12c3.059,0,5.842,1.154,7.961,3.039l5.657-5.657C34.046,6.053,29.268,4,24,4C16.318,4,9.656,8.337,6.306,14.691z"></path>
                        <path fill="#4CAF50" d="M24,44c5.166,0,9.86-1.977,13.409-5.192l-6.19-5.238C29.211,35.091,26.715,36,24,36c-5.202,0-9.619-3.317-11.283-7.946l-6.522,5.025C9.505,39.556,16.227,44,24,44z"></path>
                        <path fill="#1976D2" d="M43.611,20.083H42V20H24v8h11.303c-0.792,2.237-2.231,4.166-4.087,5.571c0.001-0.001,0.002-0.001,0.003-0.002l6.19,5.238C36.971,39.205,44,34,44,24C44,22.659,43.862,21.35,43.611,20.083z"></path>
                    </svg>
                    Daftar dengan Google
                </a>

                <div class="flex items-center gap-3 my-2">
                    <div class="h-px bg-slate-200 flex-1"></div>
                    <span class="text-[9px] font-bold text-slate-400 tracking-widest">ATAU MANUAL</span>
                    <div class="h-px bg-slate-200 flex-1"></div>
                </div>

                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400"><i class="fa-solid fa-user text-xs"></i></span>
                    <input type="text" name="name" value="{{ old('name') }}" required class="input-clean w-full pl-10 pr-4 py-2.5 rounded-xl text-[11px] focus:outline-none font-medium" placeholder="Nama Lengkap">
                </div>

                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400"><i class="fa-solid fa-envelope text-xs"></i></span>
                    <input type="email" name="email" value="{{ old('email') }}" required class="input-clean w-full pl-10 pr-4 py-2.5 rounded-xl text-[11px] focus:outline-none font-medium" placeholder="Alamat Email">
                </div>

                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-emerald-500"><i class="fa-brands fa-whatsapp text-xs"></i></span>
                    <input type="text" name="whatsapp" value="{{ old('whatsapp') }}" required class="input-clean w-full pl-10 pr-4 py-2.5 rounded-xl text-[11px] focus:outline-none font-medium" placeholder="Nomor WhatsApp">
                </div>

                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400"><i class="fa-solid fa-lock text-xs"></i></span>
                    <input type="password" id="passInput" name="password" required class="input-clean w-full pl-10 pr-10 py-2.5 rounded-xl text-[11px] focus:outline-none font-medium" placeholder="Kata Sandi (Min 8)">
                    <button type="button" onclick="togglePass('passInput', 'eye1')" class="absolute inset-y-0 right-0 flex items-center pr-4 text-slate-400 hover:text-sky-500 transition"><i id="eye1" class="fa-regular fa-eye-slash text-xs"></i></button>
                </div>

                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400"><i class="fa-solid fa-shield-halved text-xs"></i></span>
                    <input type="password" id="passConfirmInput" name="password_confirmation" required class="input-clean w-full pl-10 pr-10 py-2.5 rounded-xl text-[11px] focus:outline-none font-medium" placeholder="Konfirmasi Kata Sandi">
                    <button type="button" onclick="togglePass('passConfirmInput', 'eye2')" class="absolute inset-y-0 right-0 flex items-center pr-4 text-slate-400 hover:text-sky-500 transition"><i id="eye2" class="fa-regular fa-eye-slash text-xs"></i></button>
                </div>

                <!-- KOTAK CENTANG S&K -->
                <div class="flex items-start pt-1 pb-1">
                    <div class="flex items-center h-4 relative group mt-0.5">
                        <input id="terms_customer" name="terms" type="checkbox" required disabled
                            class="w-3.5 h-3.5 border border-slate-300 rounded focus:ring-2 focus:ring-sky-300 bg-white checked:bg-sky-500 transition opacity-50 cursor-not-allowed">
                    </div>
                    <div class="ml-2 text-[10px]">
                        <label class="font-medium text-slate-500 leading-tight block">
                            Menyetujui 
                            <button type="button" onclick="openModal()" class="font-bold text-sky-600 hover:text-sky-700 underline transition cursor-pointer">Syarat & Ketentuan</button>.
                        </label>
                        <p id="scrollAlert" class="text-[8.5px] text-rose-500 font-bold mt-0.5 animate-pulse">
                            <i class="fa-solid fa-lock mr-0.5"></i> Baca dokumen untuk membuka centang
                        </p>
                    </div>
                </div>

                <div class="pt-2">
                    <button type="submit" class="btn-modern w-full text-white font-bold py-3 rounded-xl text-[11px] tracking-wide flex justify-center items-center gap-2">
                        <span>Buat Akun Sekarang</span>
                        <i class="fa-solid fa-arrow-right text-xs"></i>
                    </button>
                </div>
            </form>

            <div class="mt-5 text-center">
                <p class="text-[11px] text-slate-500 font-medium">Sudah punya akun? 
                    <a href="{{ route('login') }}" class="text-sky-600 font-bold hover:text-sky-700 hover:underline transition ml-1">Masuk di sini</a>
                </p>
            </div>
            
        </div>
    </div>

    <!-- MODAL POPUP SYARAT & KETENTUAN (TERANG) -->
    <div id="termsModal" class="fixed inset-0 bg-slate-900/60 hidden flex items-center justify-center z-50 p-4 backdrop-blur-sm">
        <div class="bg-white rounded-2xl max-w-2xl w-full flex flex-col shadow-2xl max-h-[85vh] overflow-hidden">
            <!-- Header Modal -->
            <div class="bg-slate-50 px-6 py-4 flex justify-between items-center shrink-0 border-b border-slate-200">
                <div>
                    <h3 class="font-bold text-slate-800 text-sm tracking-wide">Syarat & Ketentuan</h3>
                    <p class="text-[10px] text-slate-500">Silakan gulir hingga akhir untuk menyetujui.</p>
                </div>
                <button onclick="closeModal()" class="text-slate-400 hover:text-slate-600 transition text-xl">&times;</button>
            </div>
            
            <!-- Konten Bisa Di-scroll -->
            <div id="termsContent" onscroll="checkScroll(this)" class="p-6 overflow-y-auto space-y-4 text-[11px] text-slate-600 leading-relaxed custom-scrollbar">
                <p>Selamat datang di Rentify. Dengan mendaftar dan menggunakan platform ini, Anda menyatakan tunduk dan terikat pada syarat dan ketentuan berikut sesuai dengan hukum yang berlaku di Republik Indonesia.</p>

                <div>
                    <h3 class="text-[12px] font-bold text-slate-800 mb-1">1. Status dan Peran Platform</h3>
                    <ul class="list-disc pl-4 space-y-1">
                        <li>Rentify adalah perantara (Penyelenggara Sistem Elektronik) yang mempertemukan pemilik barang (Vendor) dengan penyewa (Customer).</li>
                        <li>Rentify tidak memiliki atau menyimpan barang yang disewakan.</li>
                    </ul>
                </div>

                <div>
                    <h3 class="text-[12px] font-bold text-slate-800 mb-1">2. Pelepasan Tanggung Jawab</h3>
                    <ul class="list-disc pl-4 space-y-1">
                        <li>Rentify terlepas dari tanggung jawab hukum atas kerusakan, kehilangan, atau malfungsi barang sewaan.</li>
                        <li>Sengketa akibat penipuan atau penyalahgunaan adalah urusan langsung antara Vendor dan Customer.</li>
                    </ul>
                </div>
                
                <div class="h-10"></div>
            </div>
            
            <!-- Footer Modal -->
            <div class="p-4 border-t border-slate-200 bg-slate-50 flex justify-between items-center shrink-0">
                <span id="scrollProgress" class="text-[10px] font-bold text-rose-500 animate-pulse"><i class="fa-solid fa-arrow-down mr-1"></i> Gulir ke bawah</span>
                <button onclick="closeModal()" class="bg-sky-100 hover:bg-sky-200 text-sky-700 font-bold py-1.5 px-4 rounded-lg transition text-[10px]">Tutup</button>
            </div>
        </div>
    </div>

    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 5px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #f1f5f9; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
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
                alertText.classList.replace('text-rose-500', 'text-emerald-500');
                alertText.classList.remove('animate-pulse');

                const progressText = document.getElementById('scrollProgress');
                progressText.innerHTML = '<i class="fa-solid fa-check text-emerald-500 mr-1"></i> Disetujui';
                progressText.classList.remove('text-rose-500', 'animate-pulse');
                progressText.classList.add('text-slate-500');
            }
        }
    </script>
</body>
</html>