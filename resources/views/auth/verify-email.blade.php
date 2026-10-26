<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Email - Rentify</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="{{ asset('rentify-theme.css') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script>tailwind.config = { theme: { extend: { fontFamily: { sans: ['"Plus Jakarta Sans"', 'sans-serif'] } } } }</script>

</head>
<body class="flex items-center justify-center min-h-screen">
    <div class="rentify-card p-8 max-w-md w-full text-center">
        <h2 class="text-2xl font-bold mb-4">Verifikasi Email Anda</h2>
        
        <p class="text-gray-600 mb-6">
            Terima kasih telah mendaftar! Sebelum mulai menggunakan Rentify, silakan verifikasi alamat email Anda dengan mengeklik tautan yang baru saja kami kirimkan ke email Anda.
        </p>

        @if (session('success'))
            <div class="mb-4 font-medium text-sm text-green-600 bg-green-50 py-2 rounded">
                {{ session('success') }}
            </div>
        @endif

        <p class="text-gray-500 mb-4 text-sm">Tidak menerima email?</p>
        
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit" class="w-full bg-gradient-to-br from-sky-400 to-sky-600 hover:from-sky-500 hover:to-sky-700 text-white font-extrabold py-3.5 rounded-2xl text-sm tracking-widest uppercase shadow-md transition-all transform hover:-translate-y-0.5 active:translate-y-0">
                Kirim Ulang Email Verifikasi
            </button>
        </form>

        <form method="POST" action="{{ route('logout') }}" class="mt-4">
            @csrf
            <button type="submit" class="w-full py-3 bg-white/60 hover:bg-white/80 border border-slate-300 rounded-2xl font-extrabold text-[#475569] hover:text-rose-600 text-xs transition-all shadow-sm">
                Logout
            </button>
        </form>
    </div>
</body>
</html>
