<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rentify - Daftar Akun Baru</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 min-h-screen flex items-center justify-center p-4 bg-gradient-to-br from-slate-50 to-blue-50">

    <div class="w-full max-w-md bg-white rounded-3xl shadow-xl shadow-blue-900/5 border border-slate-100 p-8">
        
        <div class="text-center mb-8">
            <h1 class="text-3xl font-extrabold tracking-wider text-[#0B2E83]">RENTIFY</h1>
            <p class="text-sm text-slate-500 mt-2">Mulai perjalanan bersama kami.</p>
        </div>

        @if ($errors->any())
            <div class="mb-5 p-4 bg-rose-50 border border-rose-100 rounded-2xl text-rose-600 text-sm">
                <ul class="list-disc pl-4 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('register') }}" method="POST" class="space-y-5">
            @csrf 

            <div class="space-y-1.5">
                <label class="text-xs font-bold text-slate-500 uppercase tracking-wider block">Nama Lengkap</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                        <i class="fa-solid fa-user"></i>
                    </span>
                    <input type="text" name="name" value="{{ old('name') }}" required
                        class="w-full pl-11 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm focus:outline-none focus:border-[#0B2E83] focus:bg-white transition text-slate-800" 
                        placeholder="Masukkan nama lengkap Anda">
                </div>
            </div>

            <div class="space-y-1.5">
                <label class="text-xs font-bold text-slate-500 uppercase tracking-wider block">Alamat Email</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                        <i class="fa-solid fa-envelope"></i>
                    </span>
                    <input type="email" name="email" value="{{ old('email') }}" required
                        class="w-full pl-11 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm focus:outline-none focus:border-[#0B2E83] focus:bg-white transition text-slate-800" 
                        placeholder="contoh@email.com">
                </div>
            </div>

            <div class="space-y-1.5">
                <label class="text-xs font-bold text-slate-500 uppercase tracking-wider block">Kata Sandi (Password)</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                        <i class="fa-solid fa-lock"></i>
                    </span>
                    <input type="password" name="password" required
                        class="w-full pl-11 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm focus:outline-none focus:border-[#0B2E83] focus:bg-white transition text-slate-800" 
                        placeholder="Minimal 8 karakter">
                </div>
            </div>

            <div class="space-y-1.5">
                <label class="text-xs font-bold text-slate-500 uppercase tracking-wider block">Konfirmasi Kata Sandi</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                        <i class="fa-solid fa-lock-open"></i>
                    </span>
                    <input type="password" name="password_confirmation" required
                        class="w-full pl-11 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm focus:outline-none focus:border-[#0B2E83] focus:bg-white transition text-slate-800" 
                        placeholder="Ulangi kata sandi Anda">
                </div>
            </div>

            <button type="submit" 
                class="w-full py-3.5 bg-[#0B2E83] hover:bg-[#082263] text-white rounded-2xl font-semibold text-sm shadow-lg shadow-blue-900/20 transition transform active:scale-[0.98]">
                Daftar Akun Baru
            </button>
        </form>

        <div class="text-center mt-8 pt-6 border-t border-slate-100">
            <p class="text-sm text-slate-500">Sudah punya akun Rentify? 
                <a href="{{ route('login') }}" class="text-[#0B2E83] font-bold hover:underline">Masuk di sini</a>
            </p>
        </div>

    </div>

</body>
</html>