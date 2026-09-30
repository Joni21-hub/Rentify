<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keamanan Akun - Rentify</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="{{ asset('rentify-theme.css') }}">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="min-h-screen text-slate-800 pb-24">

    <div class="max-w-md mx-auto min-h-screen bg-white relative shadow-md">

        {{-- HEADER --}}
        <div class="bg-gradient-to-r from-[#0369a1] to-sky-400 px-5 pt-12 pb-6">
            <a href="{{ route('customer.settings') }}"
               class="inline-flex items-center gap-2 text-white/80 hover:text-white text-sm font-semibold mb-4 transition">
                <i class="fa-solid fa-arrow-left"></i> Kembali
            </a>
            <h1 class="text-white font-extrabold text-xl">Keamanan Akun</h1>
            <p class="text-white/70 text-xs mt-1">
                @if(Auth::user()->google_id && !Auth::user()->password_changed_at)
                    Buat kata sandi pertama Anda untuk login manual
                @else
                    Perbarui kata sandi akun Rentify Anda
                @endif
            </p>
        </div>

        <div class="px-5 py-6 space-y-5">

            {{-- Pesan Error --}}
            @if(session('error'))
                <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-sm font-bold flex items-center gap-2">
                    <i class="fa-solid fa-triangle-exclamation text-rose-500"></i>
                    {{ session('error') }}
                </div>
            @endif

            @if($errors->any())
                <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-sm font-bold">
                    <ul class="list-disc pl-5 space-y-1">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Banner INFO: Khusus user Google yang belum pernah set password --}}
            @if(Auth::user()->google_id && !Auth::user()->password_changed_at)
                <div class="p-4 rounded-xl bg-blue-50 border border-blue-200 flex items-start gap-3">
                    <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                        <i class="fa-brands fa-google text-sky-500 text-sm"></i>
                    </div>
                    <div>
                        <p class="text-sky-800 text-sm font-bold">Login via Google</p>
                        <p class="text-sky-600 text-xs font-medium mt-0.5 leading-relaxed">
                            Buat kata sandi untuk bisa login manual tanpa Google.
                            Setelah diatur, Anda wajib memasukkan sandi lama saat ingin mengubahnya.
                        </p>
                    </div>
                </div>
            @endif

            <form action="{{ route('customer.settings.password.update') }}" method="POST" class="space-y-5">
                @csrf

                {{-- FIELD KATA SANDI LAMA
                     Disembunyikan jika: Google + belum pernah set password
                     Ditampilkan jika: user manual ATAU Google yang sudah pernah set password
                --}}
                @if(!(Auth::user()->google_id && !Auth::user()->password_changed_at))
                    <div>
                        <label class="block text-xs font-bold text-slate-500 mb-1.5 ml-1">
                            Kata Sandi Saat Ini <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="password"
                                   name="password_lama"
                                   id="password_lama"
                                   required
                                   placeholder="Masukkan sandi Anda saat ini"
                                   class="rentify-input w-full px-4 py-3 pr-11 focus:outline-none focus: font-semibold text-sm transition">
                            <button type="button" onclick="togglePassword('password_lama', 'eye_lama')"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition">
                                <i id="eye_lama" class="fa-solid fa-eye-slash text-sm"></i>
                            </button>
                        </div>
                    </div>
                @endif

                {{-- KATA SANDI BARU --}}
                <div>
                    <label class="block text-xs font-bold text-slate-500 mb-1.5 ml-1">
                        Kata Sandi Baru <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <input type="password"
                               name="password"
                               id="password_baru"
                               required
                               placeholder="Minimal 8 karakter"
                               oninput="checkStrength(this.value)"
                               class="rentify-input w-full px-4 py-3 pr-11 focus:outline-none focus: font-semibold text-sm transition">
                        <button type="button" onclick="togglePassword('password_baru', 'eye_baru')"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition">
                            <i id="eye_baru" class="fa-solid fa-eye-slash text-sm"></i>
                        </button>
                    </div>

                    {{-- Password Strength Indicator --}}
                    <div class="mt-2 px-1">
                        <div class="flex gap-1 mb-1">
                            <div id="bar1" class="h-1.5 flex-1 rounded-full bg-slate-200 transition-all duration-300"></div>
                            <div id="bar2" class="h-1.5 flex-1 rounded-full bg-slate-200 transition-all duration-300"></div>
                            <div id="bar3" class="h-1.5 flex-1 rounded-full bg-slate-200 transition-all duration-300"></div>
                        </div>
                        <p id="strength_label" class="text-[10px] font-bold text-slate-400"></p>
                    </div>
                </div>

                {{-- KONFIRMASI SANDI BARU --}}
                <div>
                    <label class="block text-xs font-bold text-slate-500 mb-1.5 ml-1">
                        Konfirmasi Sandi Baru <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <input type="password"
                               name="password_confirmation"
                               id="password_conf"
                               required
                               placeholder="Ketik ulang sandi baru"
                               class="rentify-input w-full px-4 py-3 pr-11 focus:outline-none focus: font-semibold text-sm transition">
                        <button type="button" onclick="togglePassword('password_conf', 'eye_conf')"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition">
                            <i id="eye_conf" class="fa-solid fa-eye-slash text-sm"></i>
                        </button>
                    </div>
                </div>

                {{-- Tips keamanan --}}
                <div class="bg-slate-50 rounded-xl p-3 border border-slate-100">
                    <p class="text-[10px] font-bold text-slate-500 mb-1.5 flex items-center gap-1">
                        <i class="fa-solid fa-lightbulb text-amber-400"></i> Tips Kata Sandi Kuat
                    </p>
                    <ul class="text-[10px] text-slate-400 font-medium space-y-0.5 pl-4 list-disc">
                        <li>Minimal 8 karakter</li>
                        <li>Kombinasikan huruf besar, kecil, dan angka</li>
                        <li>Tambahkan simbol (!@#$%) untuk lebih kuat</li>
                        <li>Jangan gunakan nama atau tanggal lahir</li>
                    </ul>
                </div>

                {{-- TOMBOL SIMPAN --}}
                <div class="pt-2">
                    <button type="submit"
                            class="w-full bg-gradient-to-r from-[#0369a1] to-sky-500 hover:from-[#025d8f] hover:to-sky-600 text-white font-extrabold py-3.5 rounded-xl text-sm shadow-lg shadow-sky-500/30 transition transform hover:-translate-y-0. active:translate-y-05 active:translate-y-0 flex items-center justify-center gap-2">
                        <i class="fa-solid fa-shield-check"></i>
                        @if(Auth::user()->google_id && !Auth::user()->password_changed_at)
                            Buat Kata Sandi
                        @else
                            Simpan Sandi Baru
                        @endif
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Toggle show/hide password
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

        // Password strength indicator
        function checkStrength(val) {
            const bar1  = document.getElementById('bar1');
            const bar2  = document.getElementById('bar2');
            const bar2_ = document.getElementById('bar2');
            const bar3  = document.getElementById('bar3');
            const label = document.getElementById('strength_label');

            let score = 0;
            if (val.length >= 8)                        score++;
            if (/[A-Z]/.test(val) && /[a-z]/.test(val)) score++;
            if (/[0-9]/.test(val))                      score++;
            if (/[^A-Za-z0-9]/.test(val))               score++;

            // Reset
            [bar1, bar2, bar3].forEach(b => b.className = 'h-1.5 flex-1 rounded-full bg-slate-200 transition-all duration-300');
            label.className = 'text-[10px] font-bold text-slate-400';

            if (val.length === 0) {
                label.textContent = '';
                return;
            }

            if (score <= 1) {
                // Lemah
                bar1.className = 'h-1.5 flex-1 rounded-full bg-rose-400 transition-all duration-300';
                label.className = 'text-[10px] font-bold text-rose-500';
                label.textContent = '⚡ Lemah';
            } else if (score === 2 || score === 3) {
                // Cukup
                bar1.className = 'h-1.5 flex-1 rounded-full bg-amber-400 transition-all duration-300';
                bar2.className = 'h-1.5 flex-1 rounded-full bg-amber-400 transition-all duration-300';
                label.className = 'text-[10px] font-bold text-amber-500';
                label.textContent = '🔐 Cukup';
            } else {
                // Kuat
                bar1.className = 'h-1.5 flex-1 rounded-full bg-emerald-500 transition-all duration-300';
                bar2.className = 'h-1.5 flex-1 rounded-full bg-emerald-500 transition-all duration-300';
                bar3.className = 'h-1.5 flex-1 rounded-full bg-emerald-500 transition-all duration-300';
                label.className = 'text-[10px] font-bold text-emerald-600';
                label.textContent = '✅ Kuat';
            }
        }
    </script>
</body>
</html>
