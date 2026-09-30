<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buat Kata Sandi Baru - Rentify</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="{{ asset('rentify-theme.css') }}">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; }

        @keyframes gradientFlow {
            0%   { background-position: 0% 50%; }
            50%  { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        .bg-flowing {
            background: linear-gradient(-45deg, #0284c7, #38bdf8, #0ea5e9, #0369a1);
            background-size: 300% 300%;
            animation: gradientFlow 15s ease infinite;
        }
        .glass-panel {
            background: rgba(255,255,255,0.15);
            backdrop-filter: blur(25px);
            -webkit-backdrop-filter: blur(25px);
            border: 1px solid rgba(255,255,255,0.4);
            border-top: 1px solid rgba(255,255,255,0.7);
            border-left: 1px solid rgba(255,255,255,0.7);
            box-shadow: 0 25px 45px rgba(0,0,0,0.2);
            border-radius: 2rem;
        }
        .input-glass {
            background: rgba(255,255,255,0.4);
            border: 1px solid rgba(255,255,255,0.5);
            color: #0f172a;
            transition: all 0.3s ease;
        }
        .input-glass:focus {
            background: rgba(255,255,255,0.7);
            border-color: #ffffff;
            box-shadow: 0 0 15px rgba(255,255,255,0.5);
        }
        .input-glass::placeholder { color: rgba(15,23,42,0.5); font-weight: 500; }
        .sparkle {
            position: absolute; width: 4px; height: 4px;
            background: white; border-radius: 50%; opacity: 0;
            animation: twinkle 4s infinite ease-in-out;
        }
        @keyframes twinkle {
            0%,100% { opacity:0; transform:scale(0.5); }
            50% { opacity:0.8; transform:scale(1.5); box-shadow:0 0 12px rgba(255,255,255,1); }
        }
        /* Strength bar segments */
        .strength-seg {
            height: 4px; border-radius: 9999px; flex: 1;
            background: rgba(255,255,255,0.2);
            transition: background 0.3s ease;
        }
    </style>
</head>
<body class="min-h-screen w-full flex items-center justify-center p-4 relative overflow-hidden text-slate-800">

    <!-- Bintang Berkedip -->
    <div class="sparkle" style="top:10%;left:20%;animation-delay:0.5s"></div>
    <div class="sparkle" style="top:30%;right:15%;animation-delay:2s"></div>
    <div class="sparkle" style="bottom:15%;left:30%;animation-delay:1s"></div>
    <div class="sparkle" style="bottom:40%;right:25%;animation-delay:3s"></div>
    <div class="sparkle" style="top:60%;left:10%;animation-delay:1.5s"></div>

    <div class="w-full max-w-[400px] relative z-10">
        <div class="glass-panel p-8 sm:p-10">

            <!-- Header -->
            <div class="text-center mb-8">
                <div class="w-16 h-16 bg-white/20 border border-white/40 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg">
                    <i class="fa-solid fa-shield-halved text-2xl text-white drop-shadow"></i>
                </div>
                <h1 class="text-2xl font-extrabold text-white drop-shadow mb-1">Buat Kata Sandi Baru</h1>
                <p class="text-xs text-white/80 font-medium">Pastikan sandi baru Anda kuat dan mudah diingat.</p>
            </div>

            <!-- Error -->
            @if ($errors->any())
                <div class="bg-rose-500/90 backdrop-blur-md border border-rose-400 text-white px-4 py-3 rounded-xl text-xs font-bold flex items-center gap-2 shadow-lg mb-5">
                    <i class="fa-solid fa-circle-exclamation text-sm flex-shrink-0"></i>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif
            @if (session('error'))
                <div class="bg-rose-500/90 backdrop-blur-md border border-rose-400 text-white px-4 py-3 rounded-xl text-xs font-bold flex items-center gap-2 shadow-lg mb-5">
                    <i class="fa-solid fa-circle-exclamation text-sm flex-shrink-0"></i>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            <!-- Form -->
            <form action="{{ route('password.reset.new.save') }}" method="POST" class="space-y-5">
                @csrf

                {{-- Password --}}
                <div class="relative group">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-600 group-focus-within:text-sky-500 transition">
                        <i class="fa-solid fa-lock text-sm"></i>
                    </span>
                    <input
                        type="password"
                        id="passwordField"
                        name="password"
                        required
                        placeholder="Kata Sandi Baru"
                        oninput="checkStrength(this.value)"
                        class="input-glass w-full pl-11 pr-11 py-3.5 rounded-2xl text-sm focus:outline-none font-bold"
                    >
                    <button type="button" onclick="togglePwd('passwordField','eye1')"
                        class="absolute inset-y-0 right-0 flex items-center pr-4 text-slate-600 hover:text-sky-500 transition">
                        <i id="eye1" class="fa-regular fa-eye-slash text-sm"></i>
                    </button>
                </div>

                {{-- Indikator Kekuatan --}}
                <div class="space-y-1.5 -mt-1">
                    <div class="flex gap-1.5">
                        <div class="strength-seg" id="seg1"></div>
                        <div class="strength-seg" id="seg2"></div>
                        <div class="strength-seg" id="seg3"></div>
                    </div>
                    <p id="strengthLabel" class="text-[11px] font-bold text-white/50 text-right transition-all"></p>
                </div>

                {{-- Konfirmasi Password --}}
                <div class="relative group">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-600 group-focus-within:text-sky-500 transition">
                        <i class="fa-solid fa-lock-open text-sm"></i>
                    </span>
                    <input
                        type="password"
                        id="passwordConfirm"
                        name="password_confirmation"
                        required
                        placeholder="Konfirmasi Kata Sandi Baru"
                        class="input-glass w-full pl-11 pr-11 py-3.5 rounded-2xl text-sm focus:outline-none font-bold"
                    >
                    <button type="button" onclick="togglePwd('passwordConfirm','eye2')"
                        class="absolute inset-y-0 right-0 flex items-center pr-4 text-slate-600 hover:text-sky-500 transition">
                        <i id="eye2" class="fa-regular fa-eye-slash text-sm"></i>
                    </button>
                </div>

                <div class="pt-1">
                    <button type="submit"
                        class="w-full bg-gradient-to-br from-sky-400 to-sky-600 hover:from-sky-500 hover:to-sky-700 text-white font-extrabold py-3.5 rounded-2xl text-sm tracking-widest uppercase shadow-[0_10px_20px_rgba(0,0,0,0.15)] hover:shadow-[0_15px_25px_rgba(0,0,0,0.2)] transition-all transform hover:-translate-y-0.5 active:translate-y-0 flex justify-center items-center gap-2">
                        <i class="fa-solid fa-floppy-disk"></i>
                        <span>Simpan Sandi Baru</span>
                    </button>
                </div>
            </form>

        </div>
    </div>

    <script>
        function togglePwd(fieldId, eyeId) {
            const field = document.getElementById(fieldId);
            const icon  = document.getElementById(eyeId);
            if (field.type === 'password') {
                field.type = 'text';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            } else {
                field.type = 'password';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            }
        }

        function checkStrength(val) {
            const seg1 = document.getElementById('seg1');
            const seg2 = document.getElementById('seg2');
            const seg3 = document.getElementById('seg3');
            const lbl  = document.getElementById('strengthLabel');

            // Reset
            [seg1, seg2, seg3].forEach(s => s.style.background = 'rgba(255,255,255,0.2)');

            if (!val) { lbl.textContent = ''; return; }

            let score = 0;
            if (val.length >= 8)                         score++;
            if (/[A-Z]/.test(val) && /[a-z]/.test(val)) score++;
            if (/[0-9]/.test(val) || /[^A-Za-z0-9]/.test(val)) score++;

            if (score === 1) {
                seg1.style.background = '#f87171'; // red
                lbl.textContent = 'Lemah';
                lbl.style.color = '#f87171';
            } else if (score === 2) {
                seg1.style.background = '#fbbf24';
                seg2.style.background = '#fbbf24';
                lbl.textContent = 'Cukup';
                lbl.style.color = '#fbbf24';
            } else if (score === 3) {
                seg1.style.background = '#34d399';
                seg2.style.background = '#34d399';
                seg3.style.background = '#34d399';
                lbl.textContent = 'Kuat ✓';
                lbl.style.color = '#34d399';
            }
        }
    </script>
</body>
</html>
