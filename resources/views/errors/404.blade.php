<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>404 — Halaman Tidak Ditemukan | Rentify</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gradient-to-br from-slate-50 via-sky-50 to-slate-100 min-h-screen flex items-center justify-center p-4 font-sans text-slate-800">
    <div class="max-w-md w-full bg-white/90 backdrop-blur-md rounded-2xl shadow-xl border border-sky-100 p-8 text-center">
        <div class="w-20 h-20 bg-amber-100 text-amber-600 rounded-full flex items-center justify-center mx-auto mb-6 shadow-inner">
            <i class="fa-solid fa-triangle-exclamation text-3xl"></i>
        </div>

        <span class="inline-block px-3 py-1 bg-amber-100 text-amber-700 text-xs font-semibold rounded-full uppercase tracking-wider mb-3">
            Status HTTP 404
        </span>

        <h1 class="text-2xl font-bold text-slate-900 mb-2">Halaman Tidak Ditemukan</h1>
        <p class="text-sm text-slate-600 mb-6 leading-relaxed">
            Halaman yang Anda tuju mungkin sudah dipindahkan, dihapus, atau tautan yang dimasukkan salah.
        </p>

        <div class="flex flex-col sm:flex-row gap-3 justify-center">
            <a href="{{ url('/') }}" class="px-5 py-2.5 bg-sky-600 hover:bg-sky-700 text-white text-sm font-medium rounded-xl shadow-md transition-all flex items-center justify-center gap-2">
                <i class="fa-solid fa-house"></i> Ke Beranda
            </a>
            <button onclick="window.history.back()" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-medium rounded-xl transition-all flex items-center justify-center gap-2">
                <i class="fa-solid fa-arrow-left"></i> Kembali
            </button>
        </div>

        <div class="mt-8 pt-6 border-t border-slate-100 text-xs text-slate-400">
            Rentify &copy; {{ date('Y') }} &bull; Platform Marketplace Rental Terpercaya
        </div>
    </div>
</body>
</html>
