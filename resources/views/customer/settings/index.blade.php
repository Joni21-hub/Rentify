<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan Akun - Rentify</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 min-h-screen text-slate-800 pb-24">

    <div class="max-w-md mx-auto min-h-screen bg-white relative shadow-md">
        
        <!-- HEADER -->
        <div class="bg-white px-5 pt-6 pb-4 sticky top-0 z-50 shadow-sm flex items-center gap-4">
            <a href="{{ route('customer.dashboard') }}" class="w-8 h-8 flex items-center justify-center rounded-full bg-slate-100 text-slate-600 hover:bg-slate-200 transition">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
            <h1 class="text-lg font-extrabold text-slate-800">Pengaturan Akun</h1>
        </div>

        <div class="px-5 py-6">
            @if(session('success'))
                <div class="mb-4 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-600 text-sm font-bold flex items-center gap-2">
                    <i class="fa-solid fa-check-circle"></i> {{ session('success') }}
                </div>
            @endif

            <h3 class="font-extrabold text-slate-800 mb-3 text-sm ml-1 text-slate-500 uppercase tracking-widest">Profil & Keamanan</h3>
            
            <div class="bg-white border border-slate-100 rounded-2xl shadow-sm overflow-hidden divide-y divide-slate-100">
                <!-- PROFIL -->
                <a href="{{ route('customer.settings.profile') }}" class="flex items-center justify-between p-4 hover:bg-slate-50 transition group">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-blue-50 text-blue-500 rounded-full flex items-center justify-center group-hover:scale-110 transition">
                            <i class="fa-solid fa-user-pen"></i>
                        </div>
                        <div>
                            <span class="block text-sm font-bold text-slate-800">Profil Saya</span>
                            <span class="block text-[10px] text-slate-400 font-medium">Ubah nama, email, dan foto profil</span>
                        </div>
                    </div>
                    <i class="fa-solid fa-chevron-right text-slate-300 text-xs"></i>
                </a>

                <!-- WHATSAPP -->
                <a href="{{ route('customer.settings.whatsapp') }}" class="flex items-center justify-between p-4 hover:bg-slate-50 transition group">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-emerald-50 text-emerald-500 rounded-full flex items-center justify-center group-hover:scale-110 transition">
                            <i class="fa-brands fa-whatsapp text-lg"></i>
                        </div>
                        <div>
                            <span class="block text-sm font-bold text-slate-800">Nomor WhatsApp</span>
                            <span class="block text-[10px] text-slate-400 font-medium">{{ substr($user->whatsapp ?? '', 0, 8) }}xxx (OTP)</span>
                        </div>
                    </div>
                    <i class="fa-solid fa-chevron-right text-slate-300 text-xs"></i>
                </a>

                <!-- KATA SANDI -->
                <a href="{{ route('customer.settings.password') }}" class="flex items-center justify-between p-4 hover:bg-slate-50 transition group">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-rose-50 text-rose-500 rounded-full flex items-center justify-center group-hover:scale-110 transition">
                            <i class="fa-solid fa-lock"></i>
                        </div>
                        <div>
                            <span class="block text-sm font-bold text-slate-800">Ubah Kata Sandi</span>
                            <span class="block text-[10px] text-slate-400 font-medium">Amankan akun Anda secara berkala</span>
                        </div>
                    </div>
                    <i class="fa-solid fa-chevron-right text-slate-300 text-xs"></i>
                </a>
            </div>
            
            <p class="text-center text-xs font-bold text-slate-400 mt-8">Rentify App v1.0.0</p>
        </div>
    </div>
</body>
</html>
