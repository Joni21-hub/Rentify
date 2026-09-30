<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi OTP — Rentify</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="{{ asset('rentify-theme.css') }}">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <meta name="theme-color" content="#38bdf8">
    
</head>
<body class="min-h-screen w-full flex items-center justify-center p-4 sm:p-6 relative overflow-hidden text-slate-800">

    <div class="w-full max-w-[400px] relative z-10">
        <div class="rentify-card p-8 sm:p-10 text-center relative">
            
            <div class="w-16 h-16 rentify-card/20 flex items-center justify-center mx-auto mb-5 -white/40">
                <i class="fa-solid fa-mobile-screen-button text-2xl text-slate-800"></i>
            </div>

            <h2 class="text-2xl font-extrabold text-sky-500-md mb-2">Verifikasi OTP</h2>
            <p class="text-xs text-slate-800/90 mb-6 leading-relaxed">
                Kami telah mengirimkan 6 digit kode OTP ke WhatsApp <br>
                <b>{{ substr(session('otp_phone', '08123xxx'), 0, 7) }}xxxx</b>
            </p>

            @if (session('error'))
                <div class="mb-4 bg-rose-500/90 backdrop-blur-md border border-rose-400 text-slate-800 px-4 py-3 rounded-xl text-xs font-bold shadow-lg">
                    <i class="fa-solid fa-triangle-exclamation mr-1"></i> {{ session('error') }}
                </div>
            @endif
            
            @if (session('success'))
                <div class="mb-4 bg-emerald-500/90 backdrop-blur-md border border-emerald-400 text-slate-800 px-4 py-3 rounded-xl text-xs font-bold shadow-lg">
                    <i class="fa-solid fa-check-circle mr-1"></i> {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-4 bg-rose-500/90 backdrop-blur-md border border-rose-400 text-slate-800 px-4 py-3 rounded-xl text-xs font-bold shadow-lg">
                    {{ $errors->first() }}
                </div>
            @endif

            <form action="{{ route('otp.verify') }}" method="POST" class="space-y-5">
                @csrf
                <div class="relative">
                    <input type="number" name="otp" required maxlength="6"
                        class="rentify-input w-full text-center tracking-[0.5em] text-2xl py-4 focus:outline-none font-bold" 
                        placeholder="••••••" autofocus>
                </div>

                <button type="submit" class="w-full rentify-btn text-slate-800 font-extrabold py-3.5 rounded-2xl text-sm tracking-widest uppercase shadow-[0_10px_20px_rgba(0,0,0,0.15)] hover:shadow-[0_15px_25px_rgba(0,0,0,0.2)] transition-all transform hover:-translate-y-0. active:translate-y-05 active:translate-y-0 flex justify-center items-center gap-2">
                    <i class="fa-solid fa-check-circle"></i>
                    <span>Verifikasi</span>
                </button>
            </form>

            <div class="mt-6 flex flex-col gap-2 text-xs text-slate-800/90">
                <p>Belum menerima kode?</p>
                <form action="{{ route('otp.resend') }}" method="POST">
                    @csrf
                    <button type="submit" class="rentify-btn w-full py-3.5 text-sm tracking-widest uppercase">
                        Kirim Ulang Kode
                    </button>
                </form>
            </div>
            
        </div>
    </div>

</body>
</html>
