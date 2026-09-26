<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi OTP — Rentify</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <meta name="theme-color" content="#38bdf8">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; }

        @keyframes gradientFlow {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        .bg-flowing {
            background: linear-gradient(-45deg, #0284c7, #38bdf8, #0ea5e9, #0369a1);
            background-size: 300% 300%;
            animation: gradientFlow 15s ease infinite;
        }

        .glass-panel {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(25px);
            -webkit-backdrop-filter: blur(25px);
            border: 1px solid rgba(255, 255, 255, 0.4);
            border-top: 1px solid rgba(255, 255, 255, 0.7);
            border-left: 1px solid rgba(255, 255, 255, 0.7);
            box-shadow: 0 25px 45px rgba(0, 0, 0, 0.2);
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

        .sparkle {
            position: absolute;
            width: 4px; height: 4px;
            background-color: white;
            border-radius: 50%;
            opacity: 0;
            animation: twinkle 4s infinite ease-in-out;
        }
        @keyframes twinkle {
            0%, 100% { opacity: 0; transform: scale(0.5); }
            50% { opacity: 0.8; transform: scale(1.5); box-shadow: 0 0 12px rgba(255,255,255,1); }
        }
        
        /* Hilangkan panah spinner di input number */
        input[type=number]::-webkit-inner-spin-button, 
        input[type=number]::-webkit-outer-spin-button { 
            -webkit-appearance: none; 
            margin: 0; 
        }
    </style>
</head>
<body class="min-h-screen w-full flex items-center justify-center p-4 sm:p-6 relative overflow-hidden bg-flowing text-slate-800">

    <div class="sparkle top-[15%] left-[20%]" style="animation-delay: 0s;"></div>
    <div class="sparkle top-[25%] right-[25%]" style="animation-delay: 1.5s;"></div>
    <div class="sparkle bottom-[30%] left-[10%]" style="animation-delay: 0.7s;"></div>
    <div class="sparkle bottom-[20%] right-[15%]" style="animation-delay: 2s;"></div>

    <div class="w-full max-w-[400px] relative z-10">
        <div class="glass-panel p-8 sm:p-10 text-center relative">
            
            <div class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-5 border border-white/40 shadow-lg">
                <i class="fa-solid fa-mobile-screen-button text-2xl text-white"></i>
            </div>

            <h2 class="text-2xl font-extrabold text-white drop-shadow-md mb-2">Verifikasi OTP</h2>
            <p class="text-xs text-white/90 mb-6 leading-relaxed">
                Kami telah mengirimkan 6 digit kode OTP ke WhatsApp <br>
                <b>{{ substr(session('otp_phone', '08123xxx'), 0, 7) }}xxxx</b>
            </p>

            @if (session('error'))
                <div class="mb-4 bg-rose-500/90 backdrop-blur-md border border-rose-400 text-white px-4 py-3 rounded-xl text-xs font-bold shadow-lg">
                    <i class="fa-solid fa-triangle-exclamation mr-1"></i> {{ session('error') }}
                </div>
            @endif
            
            @if (session('success'))
                <div class="mb-4 bg-emerald-500/90 backdrop-blur-md border border-emerald-400 text-white px-4 py-3 rounded-xl text-xs font-bold shadow-lg">
                    <i class="fa-solid fa-check-circle mr-1"></i> {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-4 bg-rose-500/90 backdrop-blur-md border border-rose-400 text-white px-4 py-3 rounded-xl text-xs font-bold shadow-lg">
                    {{ $errors->first() }}
                </div>
            @endif

            <form action="{{ route('otp.verify') }}" method="POST" class="space-y-5">
                @csrf
                <div class="relative">
                    <input type="number" name="otp" required maxlength="6"
                        class="input-glass w-full text-center tracking-[0.5em] text-2xl py-4 rounded-2xl focus:outline-none font-bold" 
                        placeholder="••••••" autofocus>
                </div>

                <button type="submit" class="w-full bg-white hover:bg-gray-50 text-blue-600 font-extrabold py-3.5 rounded-2xl text-sm tracking-widest uppercase shadow-[0_10px_20px_rgba(0,0,0,0.15)] hover:shadow-[0_15px_25px_rgba(0,0,0,0.2)] transition-all transform hover:-translate-y-0.5 active:translate-y-0 flex justify-center items-center gap-2">
                    <i class="fa-solid fa-check-circle"></i>
                    <span>Verifikasi</span>
                </button>
            </form>

            <div class="mt-6 flex flex-col gap-2 text-xs text-white/90">
                <p>Belum menerima kode?</p>
                <form action="{{ route('otp.resend') }}" method="POST">
                    @csrf
                    <button type="submit" class="font-extrabold text-blue-100 hover:text-white underline transition">
                        Kirim Ulang Kode
                    </button>
                </form>
            </div>
            
        </div>
    </div>

</body>
</html>
