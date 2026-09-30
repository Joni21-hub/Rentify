<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ubah WhatsApp - Rentify</title>
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
        
        <!-- HEADER -->
        <div class="bg-white px-5 pt-6 pb-4 sticky top-0 z-50 shadow-sm flex items-center gap-4">
            <a href="{{ route('customer.settings') }}" class="w-8 h-8 flex items-center justify-center rounded-full bg-slate-100 text-slate-600 hover:bg-slate-200 transition">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
            <h1 class="text-lg font-extrabold text-slate-800">Ubah Nomor WhatsApp</h1>
        </div>

        <div class="px-5 py-6">
            @if(session('error'))
                <div class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-600 text-sm font-bold flex items-center gap-2">
                    <i class="fa-solid fa-triangle-exclamation"></i> {{ session('error') }}
                </div>
            @endif

            @if(session('success'))
                <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-600 text-sm font-bold flex items-center gap-2">
                    <i class="fa-solid fa-check-circle"></i> {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-600 text-sm font-bold">
                    <ul class="list-disc pl-5">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if(!session('otp_sent'))
                <!-- FORM MINTA OTP -->
                <div class="mb-6 bg-slate-50 p-4 rounded-2xl border border-slate-100">
                    <p class="text-xs text-slate-500 font-medium leading-relaxed">
                        Nomor Anda saat ini adalah <b class="text-slate-800">{{ $user->whatsapp }}</b>.
                        Untuk mengubahnya, masukkan nomor baru Anda di bawah. Kami akan mengirimkan 6-digit kode OTP ke nomor baru tersebut via WhatsApp.
                    </p>
                </div>

                <form action="{{ route('customer.settings.whatsapp.update') }}" method="POST" class="space-y-6">
                    @csrf
                    <div class="mb-4">
                        <label class="block text-xs font-bold text-slate-500 mb-1.5 ml-1">Konfirmasi Kata Sandi</label>
                        <input type="password" name="password_konfirmasi" required placeholder="Masukkan kata sandi Anda" class="w-full px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 focus:outline-none focus:border-emerald-500 font-bold text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 mb-1.5 ml-1">Nomor WhatsApp Baru</label>
                        <input type="text" name="whatsapp_baru" required placeholder="Contoh: 081234567890" class="w-full px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100 font-bold text-sm transition">
                    </div>

                    <div class="pt-2">
                        <button type="submit" class="w-full rentify-btn text-white font-extrabold py-3.5 rounded-xl text-sm shadow-lg shadow-sky-500/30 transition transform hover:-translate-y-0.5 flex justify-center items-center gap-2">
                            <i class="fa-brands fa-whatsapp text-lg"></i> Kirim Kode OTP
                        </button>
                    </div>
                </form>
            @else
                <!-- FORM MASUKKAN OTP -->
                <div class="mb-6 bg-emerald-50 p-4 rounded-2xl border border-emerald-100 text-center">
                    <div class="w-12 h-12 rentify-card text-emerald-500 flex items-center justify-center mx-auto mb-3 text-xl">
                        <i class="fa-solid fa-message-sms"></i>
                    </div>
                    <p class="text-xs text-emerald-700 font-bold leading-relaxed">
                        Kami telah mengirimkan 6 digit OTP ke nomor baru Anda:<br>
                        <span class="text-emerald-900 text-sm block mt-1">{{ session('otp_wa_baru') }}</span>
                    </p>
                </div>

                <form action="{{ route('customer.settings.whatsapp.verify') }}" method="POST" class="space-y-6 text-center">
                    @csrf
                    <div>
                        <input type="number" name="otp" required maxlength="6"
                        class="w-full text-center tracking-[0.5em] text-2xl py-4 rounded-2xl border-2 border-slate-200 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100 focus:outline-none font-bold bg-slate-50 transition" 
                        placeholder="••••••" autofocus>
                    </div>

                    <div class="pt-2">
                        <button type="submit" class="w-full bg-slate-800 hover:bg-slate-900 text-white font-extrabold py-3.5 rounded-xl text-sm shadow-lg shadow-slate-900/30 transition transform hover:-translate-y-0.5">
                            Verifikasi & Simpan
                        </button>
                    </div>
                </form>
            @endif
        </div>
    </div>
</body>
</html>
