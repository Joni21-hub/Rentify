<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>500 — Kendala Server | Rentify</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        rentify: { blue: '#0284c7', sky: '#38bdf8', primary: '#0ea5e9' }
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gradient-to-br from-slate-50 via-sky-50 to-slate-100 min-h-screen flex items-center justify-center p-4 font-sans text-slate-800">
    <div class="max-w-lg w-full bg-white/90 backdrop-blur-md rounded-2xl shadow-xl border border-sky-100 p-8 text-center">
        <div class="w-20 h-20 bg-sky-100 text-sky-600 rounded-full flex items-center justify-center mx-auto mb-6 shadow-inner">
            <i class="fa-solid fa-server text-3xl"></i>
        </div>

        <span class="inline-block px-3 py-1 bg-red-100 text-red-700 text-xs font-semibold rounded-full uppercase tracking-wider mb-3">
            Status HTTP 500
        </span>

        <h1 class="text-2xl font-bold text-slate-900 mb-2">Terjadi Kendala pada Server</h1>
        <p class="text-sm text-slate-600 mb-6 leading-relaxed">
            Sistem Rentify sedang mengalami gangguan sementara saat memproses permintaan Anda. Transaksi dan data Anda tetap aman. Silakan coba kembali beberapa saat lagi.
        </p>

        <div class="flex flex-col sm:flex-row gap-3 justify-center">
            <button onclick="window.location.reload()" class="px-5 py-2.5 bg-sky-600 hover:bg-sky-700 text-white text-sm font-medium rounded-xl shadow-md transition-all flex items-center justify-center gap-2">
                <i class="fa-solid fa-rotate-right"></i> Muat Ulang
            </button>
            <a href="{{ url('/') }}" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-medium rounded-xl transition-all flex items-center justify-center gap-2">
                <i class="fa-solid fa-house"></i> Ke Beranda
            </a>
            <a href="https://wa.me/6283183494835?text={{ urlencode('Halo Admin Rentify, saya mengalami kendala server 500 saat mengakses website ' . url()->current()) }}" target="_blank" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-xl shadow-md transition-all flex items-center justify-center gap-2">
                <i class="fa-brands fa-whatsapp"></i> Bantuan WA
            </a>
        </div>

        <div class="mt-8 pt-6 border-t border-slate-100 text-xs text-slate-400">
            Rentify &copy; {{ date('Y') }} &bull; Platform Marketplace Rental Terpercaya
        </div>
    </div>
</body>
</html>
