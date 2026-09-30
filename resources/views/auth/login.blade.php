<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk ke Rentify</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#38bdf8">

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; }

        /* Latar Belakang Gradasi Melingkar */
        .bg-flowing {
            background: radial-gradient(circle at center, #fff9ef 0%, #bad6eb 100%);
        }

        .glass-panel {
            background: linear-gradient(to right, #FFFDF5 0%, #CBE0F5 100%);
            border: 1px solid rgba(255, 255, 255, 0.8);
            box-shadow: 0 25px 45px rgba(0, 0, 0, 0.1);
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
    </style>
</head>
<body class="min-h-screen w-full flex items-center justify-center p-4 py-8 relative overflow-x-hidden bg-flowing text-slate-800">

    <div class="w-full max-w-[380px] relative z-10 my-auto">
        
        <div class="glass-panel p-8 sm:p-10 relative">
            
            <div class="text-center mb-8">
                <h1 class="text-5xl sm:text-6xl font-black tracking-tighter text-sky-500 mb-2">
                    Rentify
                </h1>
                <p class="text-[12px] text-[#475569] font-medium tracking-wide">Mulai petualangan serumu bersama kami.</p>
            </div>

            <form action="/login" method="POST" class="space-y-4">
                @csrf

                <div class="relative group">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-[#475569] group-focus-within:text-sky-500 transition">
                        <i class="fa-solid fa-user"></i>
                    </span>
                    <input type="text" name="login" required placeholder="No. WhatsApp / Email" 
                        class="input-glass w-full pl-11 pr-4 py-3.5 rounded-2xl text-sm focus:outline-none font-bold">
                </div>

                <div class="relative group">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-[#475569] group-focus-within:text-sky-500 transition">
                        <i class="fa-solid fa-lock text-sm"></i>
                    </span>
                    <input type="password" id="passwordField" name="password" required placeholder="Kata Sandi" 
                        class="input-glass w-full pl-11 pr-11 py-3.5 rounded-2xl text-sm focus:outline-none font-bold">
                    <button type="button" onclick="togglePassword()" class="absolute inset-y-0 right-0 flex items-center pr-4 text-[#475569] hover:text-sky-500 transition">
                        <i id="eyeIcon" class="fa-regular fa-eye-slash text-sm"></i>
                    </button>
                </div>
                
                <div class="flex justify-end -mt-2 mb-2">
                    <a href="{{ route('password.request') }}" class="text-xs font-bold text-sky-600 hover:text-sky-700 transition">Lupa kata sandi?</a>
                </div>

                @if ($errors->any())
                    <div class="bg-rose-100 border border-rose-300 text-rose-700 px-4 py-3 rounded-xl text-xs font-bold flex items-center gap-2 shadow-sm">
                        <i class="fa-solid fa-circle-exclamation text-sm"></i>
                        <span>{{ $errors->first() }}</span>
                    </div>
                @endif
                @if (session('error'))
                    <div class="bg-rose-100 border border-rose-300 text-rose-700 px-4 py-3 rounded-xl text-xs font-bold flex items-center gap-2 shadow-sm">
                        <i class="fa-solid fa-circle-exclamation text-sm"></i>
                        <span>{{ session('error') }}</span>
                    </div>
                @endif

                <div class="pt-2">
                    <button type="submit" class="w-full bg-gradient-to-br from-sky-400 to-sky-600 hover:from-sky-500 hover:to-sky-700 text-white font-extrabold py-3.5 rounded-2xl text-sm tracking-widest uppercase shadow-md transition-all transform hover:-translate-y-0.5 active:translate-y-0 flex justify-center items-center gap-2">
                        <span>MASUK</span>
                    </button>
                </div>
                
                <div class="flex items-center gap-3 my-5">
                    <div class="h-px bg-slate-300 flex-1"></div>
                    <span class="text-[10px] font-bold text-[#475569] tracking-widest">ATAU</span>
                    <div class="h-px bg-slate-300 flex-1"></div>
                </div>

                <!-- Di Login, Asumsinya mereka sudah pernah mendaftar dan menyetujui S&K -->
                <a href="{{ route('google.login') }}" class="w-full flex items-center justify-center gap-3 py-3.5 bg-white/60 hover:bg-white/80 border border-slate-300 rounded-2xl transition-all font-extrabold text-[#475569] text-sm shadow-sm">
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
                <p class="text-[11px] text-[#475569] font-medium">Belum punya akun? 
                    <a href="/register" class="text-sky-600 font-extrabold hover:text-sky-700 hover:underline transition ml-1">Daftar sekarang</a>
                </p>
                <div class="h-px w-1/2 mx-auto bg-slate-300"></div>
                <div class="pt-1">
                    <a href="/vendor/register" class="inline-block px-5 py-2 bg-white/65 border border-sky-200 rounded-full text-[11px] font-extrabold text-sky-600 hover:bg-white/90 hover:text-sky-700 hover:border-sky-300 hover:shadow-sm transition-all">
                        Daftar menjadi bagian dari rentify
                    </a>
                </div>
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