<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi OTP - Rentify</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="{{ asset('rentify-theme.css') }}">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
</head>
<body class="min-h-screen w-full flex items-center justify-center p-4 relative overflow-hidden text-slate-800">

    @php
        $status   = session('status', 'customer_wa_step');
        $isVendor = str_starts_with($status, 'vendor_');
        $isEmail  = in_array($status, ['customer_email_step', 'vendor_email_step']);
        $isWa     = in_array($status, ['customer_wa_step', 'vendor_wa_step']);
        $otpType  = $isEmail ? 'email' : 'wa';
        $step2    = ($status === 'vendor_email_step');
    @endphp

    <div class="w-full max-w-[400px] relative z-10">
        <div class="rentify-card p-8 sm:p-10">

            {{-- ── PROGRESS BAR VENDOR ──────────────────────────────── --}}
            @if ($isVendor)
                <div class="flex items-center justify-center gap-2 mb-5">
                    <div class="flex items-center gap-1.5">
                        <div class="w-7 h-7 rounded-full {{ !$step2 ? 'bg-white text-sky-600' : 'bg-white/30 text-slate-800' }} flex items-center justify-center text-xs font-black shadow transition-all">1</div>
                        <span class="text-xs text-[#475569] font-semibold">WA</span>
                    </div>
                    <div class="h-px w-8 bg-white/40"></div>
                    <div class="flex items-center gap-1.5">
                        <div class="w-7 h-7 rounded-full {{ $step2 ? 'bg-white text-sky-600' : 'bg-white/20 text-slate-800/60' }} flex items-center justify-center text-xs font-black transition-all">2</div>
                        <span class="text-xs text-[#475569] font-semibold">Email</span>
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
                        <h1 class="text-xl font-extrabold text-sky-500 mb-1">Langkah 1/2 — Verifikasi WhatsApp</h1>
                        <p class="text-[12px] text-[#475569] font-medium">Masukkan OTP yang dikirim ke WhatsApp vendor Anda.</p>
                    @else
                        <h1 class="text-3xl font-extrabold text-sky-500 tracking-tighter mb-1">Verifikasi WhatsApp</h1>
                        <p class="text-[12px] text-[#475569] font-medium">Masukkan OTP yang dikirim ke WhatsApp Anda.</p>
                    @endif
                @else
                    <div class="w-16 h-16 bg-sky-400/30 border border-sky-300/50 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg">
                        <i class="fa-solid fa-envelope text-2xl text-sky-100 drop-shadow"></i>
                    </div>
                    @if ($isVendor)
                        <h1 class="text-xl font-extrabold text-sky-500 mb-1">Langkah 2/2 — Verifikasi Email</h1>
                        <p class="text-[12px] text-[#475569] font-medium">Masukkan OTP yang dikirim ke Gmail vendor Anda.</p>
                    @else
                        <h1 class="text-3xl font-extrabold text-sky-500 tracking-tighter mb-1">Verifikasi Email</h1>
                        <p class="text-[12px] text-[#475569] font-medium">Masukkan OTP yang dikirim ke Gmail Anda.</p>
                    @endif
                @endif
            </div>

            {{-- ── PESAN ERROR ─────────────────────────────────────────── --}}
            @if (session('error'))
                <div class="bg-rose-500/90 backdrop-blur-md border border-rose-400 text-slate-800 px-4 py-3 rounded-xl text-xs font-bold flex items-center gap-2 shadow-lg mb-5">
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
                        class="rentify-input w-full py-4 text-3xl font-extrabold tracking-[0.6em] text-center focus:outline-none"
                    >
                </div>

                {{-- Hitung Mundur --}}
                <div class="text-center">
                    <p class="text-xs text-slate-800/70 font-medium">Kode kedaluwarsa dalam</p>
                    <p id="countdown" class="text-2xl font-extrabold text-slate-800 mt-0.5 tabular-nums">15:00</p>
                </div>

                <div class="pt-1">
                    <button type="submit" class="w-full rentify-btn text-slate-800 font-extrabold py-3.5 rounded-2xl text-sm tracking-widest uppercase shadow-[0_10px_20px_rgba(0,0,0,0.15)] hover:shadow-[0_15px_25px_rgba(0,0,0,0.2)] transition-all transform hover:-translate-y-0. active:translate-y-05 active:translate-y-0 flex justify-center items-center gap-2">
                        <i class="fa-solid fa-check-circle"></i>
                        <span>Verifikasi OTP</span>
                    </button>
                </div>
            </form>

            <!-- Link kembali -->
            <div class="mt-6 text-center">
                <a href="{{ route('password.request') }}"
                   class="text-xs text-slate-800/70 font-semibold hover:text-sky-700 transition flex items-center justify-center gap-1.5">
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
