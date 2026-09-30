<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buat Kata Sandi Baru - Rentify</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="{{ asset('rentify-theme.css') }}">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
</head>
<body class="min-h-screen w-full flex items-center justify-center p-4 relative overflow-hidden text-slate-800">

    <!-- Bintang Berkedip -->
    <div class="w-full max-w-[400px] relative z-10">
        <div class="rentify-card p-8 sm:p-10">

            <!-- Header -->
            <div class="text-center mb-8">
                <div class="w-16 h-16 bg-sky-100 border border-sky-200 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fa-solid fa-shield-halved text-2xl text-sky-600"></i>
                </div>
                <h1 class="text-3xl font-extrabold text-sky-500 tracking-tighter mb-1">Buat Kata Sandi Baru</h1>
                <p class="text-[12px] text-[#475569] font-medium">Pastikan sandi baru Anda kuat dan mudah diingat.</p>
            </div>

            <!-- Error -->
            @if ($errors->any())
                <div class="bg-rose-500/90 backdrop-blur-md border border-rose-400 text-slate-800 px-4 py-3 rounded-xl text-xs font-bold flex items-center gap-2 shadow-lg mb-5">
                    <i class="fa-solid fa-circle-exclamation text-sm flex-shrink-0"></i>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif
            @if (session('error'))
                <div class="bg-rose-500/90 backdrop-blur-md border border-rose-400 text-slate-800 px-4 py-3 rounded-xl text-xs font-bold flex items-center gap-2 shadow-lg mb-5">
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
                        class="rentify-input w-full pl-11 pr-11 py-3.5 text-sm focus:outline-none font-bold"
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
                    <p id="strengthLabel" class="text-[11px] font-bold text-slate-800/50 text-right transition-all"></p>
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
                        class="rentify-input w-full pl-11 pr-11 py-3.5 text-sm focus:outline-none font-bold"
                    >
                    <button type="button" onclick="togglePwd('passwordConfirm','eye2')"
                        class="absolute inset-y-0 right-0 flex items-center pr-4 text-slate-600 hover:text-sky-500 transition">
                        <i id="eye2" class="fa-regular fa-eye-slash text-sm"></i>
                    </button>
                </div>

                <div class="pt-1">
                    <button type="submit" class="w-full rentify-btn text-slate-800 font-extrabold py-3.5 rounded-2xl text-sm tracking-widest uppercase shadow-[0_10px_20px_rgba(0,0,0,0.15)] hover:shadow-[0_15px_25px_rgba(0,0,0,0.2)] transition-all transform hover:-translate-y-0. active:translate-y-05 active:translate-y-0 flex justify-center items-center gap-2">
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
