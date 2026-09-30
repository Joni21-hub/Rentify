<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Keamanan Akun - Rentify</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="{{ asset('rentify-theme.css') }}">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            background: radial-gradient(circle at center, #fff9ef 0%, #bad6eb 100%) fixed !important;
        }
        .bg-flowing {
            background: radial-gradient(circle at center, #fff9ef 0%, #bad6eb 100%) fixed !important;
        }
    </style>
</head>
<body class="min-h-screen text-slate-800 pb-16 bg-flowing overflow-x-hidden w-full max-w-full">

    <div class="max-w-md mx-auto min-h-screen relative pb-8">

        <!-- HEADER -->
        <header class="rentify-navbar sticky top-0 z-50 px-5 py-3.5 flex items-center justify-between shadow-sm">
            <a href="{{ route('customer.settings') }}" class="w-9 h-9 flex items-center justify-center rounded-xl bg-white/70 hover:bg-white text-slate-600 hover:text-sky-600 transition shadow-sm">
                <i class="fa-solid fa-arrow-left text-sm"></i>
            </a>
            <h1 class="text-base font-black text-slate-800 flex-1 text-center tracking-tight pr-9">Keamanan Akun</h1>
        </header>

        <div class="px-4 py-6 space-y-4">

            <!-- Flash Error / Success -->
            @if(session('error'))
                <div class="p-3.5 rounded-2xl bg-rose-100 border border-rose-300 text-rose-800 text-xs font-bold flex items-center gap-2 shadow-sm">
                    <i class="fa-solid fa-triangle-exclamation text-rose-600 text-sm"></i>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            @if($errors->any())
                <div class="p-3.5 rounded-2xl bg-rose-100 border border-rose-300 text-rose-800 text-xs font-bold shadow-sm">
                    <ul class="list-disc pl-5 space-y-1">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Info Box: Khusus user Google yang belum set password -->
            @if(Auth::user()->google_id && !Auth::user()->password_changed_at)
                <div class="p-4 rounded-2xl bg-sky-50 border border-sky-200 flex items-start gap-3 shadow-sm">
                    <div class="w-8 h-8 bg-sky-100 text-sky-600 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                        <i class="fa-brands fa-google text-sm"></i>
                    </div>
                    <div>
                        <p class="text-sky-900 text-xs font-bold">Login via Google Aktif</p>
                        <p class="text-sky-700 text-[11px] font-medium mt-0.5 leading-relaxed">
                            Buat kata sandi akun untuk dapat masuk manual menggunakan nomor WhatsApp atau email tanpa harus menekan tombol Google.
                        </p>
                    </div>
                </div>
            @endif

            <form action="{{ route('customer.settings.password.update') }}" method="POST" class="rentify-card p-5 rounded-3xl shadow-sm space-y-4">
                @csrf

                <!-- Kata Sandi Lama -->
                @if(!(Auth::user()->google_id && !Auth::user()->password_changed_at))
                    <div>
                        <label class="block text-xs font-bold text-slate-600 mb-1.5 ml-1">
                            Kata Sandi Saat Ini <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="password" name="password_lama" id="password_lama" required placeholder="Masukkan sandi saat ini"
                                   class="rentify-input input-glass w-full px-4 py-3.5 pr-11 text-sm font-bold focus:outline-none transition">
                            <button type="button" onclick="togglePassword('password_lama', 'eye_lama')" class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition">
                                <i id="eye_lama" class="fa-solid fa-eye-slash text-sm"></i>
                            </button>
                        </div>
                    </div>
                @endif

                <!-- Kata Sandi Baru -->
                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1.5 ml-1">
                        Kata Sandi Baru <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <input type="password" name="password" id="password_baru" required placeholder="Minimal 8 karakter" oninput="checkStrength(this.value)"
                               class="rentify-input input-glass w-full px-4 py-3.5 pr-11 text-sm font-bold focus:outline-none transition">
                        <button type="button" onclick="togglePassword('password_baru', 'eye_baru')" class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition">
                            <i id="eye_baru" class="fa-solid fa-eye-slash text-sm"></i>
                        </button>
                    </div>

                    <!-- Indikator Kekuatan Sandi -->
                    <div class="mt-2 px-1">
                        <div class="flex gap-1 mb-1">
                            <div id="bar1" class="h-1.5 flex-1 rounded-full bg-slate-200 transition-all duration-300"></div>
                            <div id="bar2" class="h-1.5 flex-1 rounded-full bg-slate-200 transition-all duration-300"></div>
                            <div id="bar3" class="h-1.5 flex-1 rounded-full bg-slate-200 transition-all duration-300"></div>
                        </div>
                        <p id="strength_label" class="text-[10px] font-bold text-slate-400"></p>
                    </div>
                </div>

                <!-- Konfirmasi Sandi Baru -->
                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1.5 ml-1">
                        Konfirmasi Sandi Baru <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <input type="password" name="password_confirmation" id="password_conf" required placeholder="Ketik ulang sandi baru"
                               class="rentify-input input-glass w-full px-4 py-3.5 pr-11 text-sm font-bold focus:outline-none transition">
                        <button type="button" onclick="togglePassword('password_conf', 'eye_conf')" class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition">
                            <i id="eye_conf" class="fa-solid fa-eye-slash text-sm"></i>
                        </button>
                    </div>
                </div>

                <!-- Tombol Simpan -->
                <div class="pt-3">
                    <button type="submit" class="rentify-btn w-full py-3.5 rounded-2xl text-xs font-black tracking-widest uppercase shadow-md transition-all transform hover:-translate-y-0.5 active:translate-y-0 flex items-center justify-center gap-2">
                        <i class="fa-solid fa-shield-check"></i>
                        <span>{{ (Auth::user()->google_id && !Auth::user()->password_changed_at) ? 'Buat Kata Sandi' : 'Simpan Sandi Baru' }}</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function togglePassword(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon  = document.getElementById(iconId);
            if (!input) return;
            if (input.type === 'password') {
                input.type = 'text';
                icon.className = 'fa-solid fa-eye text-sm';
            } else {
                input.type = 'password';
                icon.className = 'fa-solid fa-eye-slash text-sm';
            }
        }

        function checkStrength(val) {
            const bar1  = document.getElementById('bar1');
            const bar2  = document.getElementById('bar2');
            const bar3  = document.getElementById('bar3');
            const label = document.getElementById('strength_label');

            let score = 0;
            if (val.length >= 8) score++;
            if (/[A-Z]/.test(val) && /[a-z]/.test(val)) score++;
            if (/[0-9]/.test(val)) score++;
            if (/[^A-Za-z0-9]/.test(val)) score++;

            [bar1, bar2, bar3].forEach(b => b.className = 'h-1.5 flex-1 rounded-full bg-slate-200 transition-all duration-300');
            label.className = 'text-[10px] font-bold text-slate-400';

            if (val.length === 0) {
                label.textContent = '';
                return;
            }

            if (score <= 1) {
                bar1.className = 'h-1.5 flex-1 rounded-full bg-rose-400 transition-all duration-300';
                label.className = 'text-[10px] font-bold text-rose-500';
                label.textContent = '? Lemah';
            } else if (score === 2 || score === 3) {
                bar1.className = 'h-1.5 flex-1 rounded-full bg-amber-400 transition-all duration-300';
                bar2.className = 'h-1.5 flex-1 rounded-full bg-amber-400 transition-all duration-300';
                label.className = 'text-[10px] font-bold text-amber-500';
                label.textContent = '?? Cukup';
            } else {
                bar1.className = 'h-1.5 flex-1 rounded-full bg-emerald-500 transition-all duration-300';
                bar2.className = 'h-1.5 flex-1 rounded-full bg-emerald-500 transition-all duration-300';
                bar3.className = 'h-1.5 flex-1 rounded-full bg-emerald-500 transition-all duration-300';
                label.className = 'text-[10px] font-bold text-emerald-600';
                label.textContent = '? Kuat';
            }
        }
    </script>
</body>
</html>