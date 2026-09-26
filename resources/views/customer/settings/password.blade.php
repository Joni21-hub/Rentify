<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ubah Kata Sandi - Rentify</title>
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
            <a href="{{ route('customer.settings') }}" class="w-8 h-8 flex items-center justify-center rounded-full bg-slate-100 text-slate-600 hover:bg-slate-200 transition">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
            <h1 class="text-lg font-extrabold text-slate-800">Ubah Kata Sandi</h1>
        </div>

        <div class="px-5 py-6">
            @if(session('error'))
                <div class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-600 text-sm font-bold flex items-center gap-2">
                    <i class="fa-solid fa-triangle-exclamation"></i> {{ session('error') }}
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

            <form action="{{ route('customer.settings.password.update') }}" method="POST" class="space-y-6">
                @csrf
                
                <div>
                    <label class="block text-xs font-bold text-slate-500 mb-1.5 ml-1">Kata Sandi Saat Ini</label>
                    <input type="password" name="password_lama" required placeholder="Masukkan sandi Anda saat ini" class="w-full px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 font-bold text-sm transition">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-500 mb-1.5 ml-1">Kata Sandi Baru</label>
                    <input type="password" name="password" required placeholder="Minimal 8 karakter" class="w-full px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 font-bold text-sm transition">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-500 mb-1.5 ml-1">Konfirmasi Sandi Baru</label>
                    <input type="password" name="password_confirmation" required placeholder="Ketik ulang sandi baru" class="w-full px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 font-bold text-sm transition">
                </div>

                <!-- SIMPAN -->
                <div class="pt-4">
                    <button type="submit" class="w-full bg-rose-500 hover:bg-rose-600 text-white font-extrabold py-3.5 rounded-xl text-sm shadow-lg shadow-rose-500/30 transition transform hover:-translate-y-0.5">
                        Simpan Sandi Baru
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
