<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi OTP - Rentify</title>
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
        .input-glass::placeholder { color: rgba(15,23,42,0.4); }
        .sparkle {
            position: absolute; width: 4px; height: 4px;
            background: white; border-radius: 50%; opacity: 0;
            animation: twinkle 4s infinite ease-in-out;
        }
        @keyframes twinkle {
            0%,100% { opacity:0; transform:scale(0.5); }
            50% { opacity:0.8; transform:scale(1.5); box-shadow:0 0 12px rgba(255,255,255,1); }
        }
    </style>
</head>
<body class="min-h-screen w-full flex items-center justify-center p-4 relative overflow-hidden bg-flowing text-slate-800">

    <!-- Efek Bintang Berkedip -->
    <div class="sparkle" style="top:10%;left:20%;animation-delay:0.5s"></div>
    <div class="sparkle" style="top:30%;right:15%;animation-delay:2s"></div>
    <div class="sparkle" style="bottom:15%;left:30%;animation-delay:1s"></div>
    <div class="sparkle" style="bottom:40%;right:25%;animation-delay:3s"></div>
    <div class="sparkle" style="top:60%;left:10%;animation-delay:1.5s"></div>

    @php
        $status   = session('status', 'customer_wa_step');
        $isVendor = str_starts_with($status, 'vendor_');
        $isEmail  = in_array($status, ['customer_email_step', 'vendor_email_step']);
        $isWa     = in_array($status, ['customer_wa_step', 'vendor_wa_step']);
        $otpType  = $isEmail ? 'email' : 'wa';
        $step2    = ($status === 'vendor_email_step');
    @endphp

    <div class="w-full max-w-[400px] relative z-10">
        <div class="glass-panel p-8 sm:p-10">

            {{-- ── PROGRESS BAR VENDOR ──────────────────────────────── --}}
            @if ($isVendor)
                <div class="flex items-center justify-center gap-2 mb-5">
                    <div class="flex items-center gap-1.5">
                        <div class="w-7 h-7 rounded-full {{ !$step2 ? 'bg-white text-sky-600' : 'bg-white/30 text-white' }} flex items-center justify-center text-xs font-black shadow transition-all">1</div>
                        <span class="text-xs text-white/80 font-semibold">WA</span>
                    </div>
                    <div class="h-px w-8 bg-white/40"></div>
                    <div class="flex items-center gap-1.5">
                        <div class="w-7 h-7 rounded-full {{ $step2 ? 'bg-white text-sky-600' : 'bg-white/20 text-white/60' }} flex items-center justify-center text-xs font-black transition-all">2</div>
                        <span class="text-xs text-white/80 font-semibold">Email</span>
                    </div>
                </div>
            @endif

            {{-- ── HEADER ────────────────────────────────────────────── --}}
            <div class="text-center mb-7">
                @if ($isWa)
                    <div class="w-16 h-16 bg-green-500/30 border border-green-400/50 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg">
                        <i class="fa-brands fa-whatsapp text-3xl text-green-300 drop-shadow"></i>
                    </div>
                    @if ($isVendor)
                        <h1 class="text-xl font-extrabold text-white drop-shadow mb-1">Langkah 1/2 — Verifikasi WhatsApp</h1>
                        <p class="text-xs text-white/80 font-medium">Masukkan OTP yang dikirim ke WhatsApp vendor Anda.</p>
                    @else
                        <h1 class="text-2xl font-extrabold text-white drop-shadow mb-1">Verifikasi WhatsApp</h1>
                        <p class="text-xs text-white/80 font-medium">Masukkan OTP yang dikirim ke WhatsApp Anda.</p>
                    @endif
                @else
                    <div class="w-16 h-16 bg-sky-400/30 border border-sky-300/50 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg">
                        <i class="fa-solid fa-envelope text-2xl text-sky-100 drop-shadow"></i>
                    </div>
                    @if ($isVendor)
                        <h1 class="text-xl font-extrabold text-white drop-shadow mb-1">Langkah 2/2 — Verifikasi Email</h1>
                        <p class="text-xs text-white/80 font-medium">Masukkan OTP yang dikirim ke Gmail vendor Anda.</p>
                    @else
                        <h1 class="text-2xl font-extrabold text-white drop-shadow mb-1">Verifikasi Email</h1>
                        <p class="text-xs text-white/80 font-medium">Masukkan OTP yang dikirim ke Gmail Anda.</p>
                    @endif
                @endif
            </div>

            {{-- ── PESAN ERROR ─────────────────────────────────────────── --}}
            @if (session('error'))
                <div class="bg-rose-500/90 backdrop-blur-md border border-rose-400 text-white px-4 py-3 rounded-xl text-xs font-bold flex items-center gap-2 shadow-lg mb-5">
                    <i class="fa-solid fa-circle-exclamation text-sm flex-shrink-0"></i>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            {{-- ── FORM OTP ─────────────────────────────────────────────── --}}
            <form action="{{ route('password.reset.otp.verify') }}" method="POST" class="space-y-5">
                @csrf
                <input type="hidden" name="otp_type" value="{{ $otpType }}">

                <div class="text-center">
                    <input
                        type="text"
                        name="otp"
                        inputmode="numeric"
                        maxlength="6"
                        autofocus
                        placeholder="• • • • • •"
                        class="input-glass w-full py-4 rounded-2xl text-3xl font-extrabold tracking-[0.6em] text-center focus:outline-none"
                    >
                </div>

                {{-- Hitung Mundur --}}
                <div class="text-center">
                    <p class="text-xs text-white/70 font-medium">Kode kedaluwarsa dalam</p>
                    <p id="countdown" class="text-2xl font-extrabold text-white mt-0.5 tabular-nums">15:00</p>
                </div>

                <div class="pt-1">
                    <button type="submit"
                        class="w-full bg-gradient-to-br from-sky-400 to-sky-600 hover:from-sky-500 hover:to-sky-700 text-white font-extrabold py-3.5 rounded-2xl text-sm tracking-widest uppercase shadow-[0_10px_20px_rgba(0,0,0,0.15)] hover:shadow-[0_15px_25px_rgba(0,0,0,0.2)] transition-all transform hover:-translate-y-0.5 active:translate-y-0 flex justify-center items-center gap-2">
                        <i class="fa-solid fa-check-circle"></i>
                        <span>Verifikasi OTP</span>
                    </button>
                </div>
            </form>

            <!-- Link kembali -->
            <div class="mt-6 text-center">
                <a href="{{ route('password.request') }}"
                   class="text-xs text-white/70 font-semibold hover:text-white transition flex items-center justify-center gap-1.5">
                    <i class="fa-solid fa-arrow-left text-[10px]"></i>
                    Mulai ulang dari awal
                </a>
            </div>

        </div>
    </div>

    <script>
        // Hitung mundur 15 menit
        let totalSeconds = 15 * 60;
        const countdownEl = document.getElementById('countdown');

        const timer = setInterval(() => {
            if (totalSeconds <= 0) {
                clearInterval(timer);
                countdownEl.textContent = '00:00';
                countdownEl.classList.add('text-rose-300');
                return;
            }
            totalSeconds--;
            const m = String(Math.floor(totalSeconds / 60)).padStart(2, '0');
            const s = String(totalSeconds % 60).padStart(2, '0');
            countdownEl.textContent = `${m}:${s}`;

            if (totalSeconds <= 60) {
                countdownEl.classList.add('text-rose-300');
            }
        }, 1000);
    </script>
</body>
</html>
