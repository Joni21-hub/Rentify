<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk ke Rentify — Eksplorasi Dimulai</title>
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
    <div class="blob w-72 h-72 bg-cyan-300 rounded-full top-[10%] left-[15%]" style="animation-delay: 0s;"></div>
    <div class="blob w-96 h-96 bg-blue-200 rounded-full bottom-[10%] right-[10%]" style="animation-delay: -5s;"></div>
    <div class="blob w-64 h-64 bg-sky-200 rounded-full top-[40%] right-[30%]" style="animation-delay: -2s;"></div>

    <div class="w-full max-w-md relative z-10">
        
        <!-- PANEL KACA FORM -->
        <div class="glass-panel p-8 sm:p-10 relative">
            
            <div class="text-center mb-8">
                <!-- LOGO RENTIFY (ELEGAN & MODERN) -->
                <div class="inline-flex items-center justify-center mb-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-sky-400 to-blue-600 flex items-center justify-center shadow-lg shadow-blue-500/30 mr-3">
                        <i class="fa-solid fa-gem text-white text-lg"></i>
                    </div>
                    <h1 class="text-3xl sm:text-4xl font-logo font-extrabold tracking-tight text-transparent bg-clip-text bg-gradient-to-r from-slate-800 to-slate-600">
                        Rentify
                    </h1>
                </div>
                <h2 class="text-sm font-semibold text-slate-600 tracking-wide uppercase">Selamat Datang Kembali</h2>
                <p class="text-xs text-slate-500 mt-1">Masuk untuk melanjutkan eksplorasi Anda.</p>
            </div>

            <form action="/login" method="POST" class="space-y-4">
                @csrf

                <div class="space-y-1.5">
                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider pl-1">Alamat Email</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                            <i class="fa-regular fa-envelope"></i>
                        </span>
                        <input type="email" name="email" required placeholder="contoh@email.com" 
                            class="input-clean w-full pl-11 pr-4 py-3 rounded-xl text-sm focus:outline-none font-medium">
                    </div>
                </div>

                <div class="space-y-1.5">
                    <div class="flex justify-between items-center pl-1 pr-1">
                        <label class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Kata Sandi</label>
                        <a href="/forgot-password" class="text-[10px] font-bold text-sky-600 hover:text-sky-800 transition">Lupa sandi?</a>
                    </div>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                            <i class="fa-solid fa-lock text-sm"></i>
                        </span>
                        <input type="password" id="passwordField" name="password" required placeholder="Masukkan kata sandi" 
                            class="input-clean w-full pl-11 pr-11 py-3 rounded-xl text-sm focus:outline-none font-medium">
                        <button type="button" onclick="togglePassword()" class="absolute inset-y-0 right-0 flex items-center pr-4 text-slate-400 hover:text-sky-500 transition">
                            <i id="eyeIcon" class="fa-regular fa-eye-slash text-sm"></i>
                        </button>
                    </div>
                </div>

                @if ($errors->any())
                    <div class="bg-rose-50 border border-rose-200 text-rose-600 px-4 py-3 rounded-xl text-xs font-medium flex items-start gap-2 shadow-sm">
                        <i class="fa-solid fa-circle-exclamation mt-0.5"></i>
                        <span>{{ $errors->first() }}</span>
                    </div>
                @endif

                @if (session('error'))
                    <div class="bg-rose-50 border border-rose-200 text-rose-600 px-4 py-3 rounded-xl text-xs font-medium flex items-start gap-2 shadow-sm">
                        <i class="fa-solid fa-circle-exclamation mt-0.5"></i>
                        <span>{{ session('error') }}</span>
                    </div>
                @endif

                <div class="pt-2">
                    <button type="submit" class="btn-modern w-full text-white font-bold py-3.5 rounded-xl text-sm tracking-wide flex justify-center items-center gap-2">
                        <span>Masuk ke Akun</span>
                        <i class="fa-solid fa-arrow-right-to-bracket text-xs"></i>
                    </button>
                </div>
                
                <div class="flex items-center gap-3 my-5">
                    <div class="h-px bg-slate-200 flex-1"></div>
                    <span class="text-[10px] font-bold text-slate-400 tracking-wider">ATAU</span>
                    <div class="h-px bg-slate-200 flex-1"></div>
                </div>

                <a href="{{ route('google.login') }}" class="w-full flex items-center justify-center gap-3 py-3 bg-white border border-slate-200 hover:border-slate-300 hover:bg-slate-50 rounded-xl transition-all font-bold text-slate-600 text-sm shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" class="w-5 h-5">
                        <path fill="#FFC107" d="M43.611,20.083H42V20H24v8h11.303c-1.649,4.657-6.08,8-11.303,8c-6.627,0-12-5.373-12-12c0-6.627,5.373-12,12-12c3.059,0,5.842,1.154,7.961,3.039l5.657-5.657C34.046,6.053,29.268,4,24,4C12.955,4,4,12.955,4,24c0,11.045,8.955,20,20,20c11.045,0,20-8.955,20-20C44,22.659,43.862,21.35,43.611,20.083z"></path>
                        <path fill="#FF3D00" d="M6.306,14.691l6.571,4.819C14.655,15.108,18.961,12,24,12c3.059,0,5.842,1.154,7.961,3.039l5.657-5.657C34.046,6.053,29.268,4,24,4C16.318,4,9.656,8.337,6.306,14.691z"></path>
                        <path fill="#4CAF50" d="M24,44c5.166,0,9.86-1.977,13.409-5.192l-6.19-5.238C29.211,35.091,26.715,36,24,36c-5.202,0-9.619-3.317-11.283-7.946l-6.522,5.025C9.505,39.556,16.227,44,24,44z"></path>
                        <path fill="#1976D2" d="M43.611,20.083H42V20H24v8h11.303c-0.792,2.237-2.231,4.166-4.087,5.571c0.001-0.001,0.002-0.001,0.003-0.002l6.19,5.238C36.971,39.205,44,34,44,24C44,22.659,43.862,21.35,43.611,20.083z"></path>
                    </svg>
                    Masuk dengan Google
                </a>
            </form>

            <div class="mt-8 space-y-3">
                <div class="text-center">
                    <p class="text-[11px] text-slate-500 font-medium">Belum punya akun? 
                        <a href="/register" class="text-sky-600 font-bold hover:text-sky-700 hover:underline transition ml-1">Daftar sekarang</a>
                    </p>
                </div>
                
                <!-- LINK VENDOR YANG ELEGAN -->
                <div class="bg-slate-50/50 border border-slate-100 rounded-xl p-3 flex items-center justify-between group hover:bg-sky-50/50 transition cursor-pointer" onclick="window.location.href='/vendor/register'">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center group-hover:scale-110 transition">
                            <i class="fa-solid fa-store text-[10px]"></i>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold text-slate-700">Punya barang tak terpakai?</p>
                            <p class="text-[9px] text-slate-500">Mulai hasilkan uang sebagai Vendor.</p>
                        </div>
                    </div>
                    <i class="fa-solid fa-chevron-right text-slate-300 text-[10px] group-hover:text-indigo-500 transition translate-x-0 group-hover:translate-x-1"></i>
                </div>
            </div>
            
        </div>
    </div>

    <!-- TOMBOL INSTALL PWA -->
    <div id="installPwaContainer" style="display: none;" class="fixed bottom-6 right-6 z-50">
        <button id="installPwaBtn" class="bg-white hover:bg-slate-50 text-slate-700 font-bold py-2.5 px-5 rounded-full shadow-xl border border-slate-200 flex items-center gap-2.5 transition-all transform hover:-translate-y-1 text-xs">
            <div class="w-6 h-6 rounded-full bg-sky-100 text-sky-600 flex items-center justify-center">
                <i class="fa-solid fa-download text-[10px]"></i>
            </div>
            <span>Install Aplikasi</span>
        </button>
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

        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js').catch(err => console.error('PWA Error', err));
            });
        }

        let deferredPrompt;
        const installContainer = document.getElementById('installPwaContainer');
        const installBtn = document.getElementById('installPwaBtn');

        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            deferredPrompt = e;
            installContainer.style.display = 'block';
        });

        installBtn.addEventListener('click', async () => {
            if (deferredPrompt) {
                deferredPrompt.prompt();
                await deferredPrompt.userChoice;
                deferredPrompt = null;
                installContainer.style.display = 'none';
            }
        });

        window.addEventListener('appinstalled', () => {
            installContainer.style.display = 'none';
            deferredPrompt = null;
        });
    </script>
</body>
</html>