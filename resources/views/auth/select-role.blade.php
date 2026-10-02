<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pilih Peran Masuk — Rentify</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="{{ asset('rentify-theme.css') }}">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .bg-flowing {
            background: radial-gradient(circle at center, #fff9ef 0%, #bad6eb 100%) fixed !important;
        }
        .glass-card {
            background: rgba(255, 255, 255, 0.75);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.85);
            box-shadow: 0 20px 45px rgba(15, 23, 42, 0.08);
        }
        .role-card {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .role-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 35px rgba(14, 165, 233, 0.18);
        }
    </style>
</head>
<body class="min-h-screen w-full flex items-center justify-center p-4 sm:p-6 bg-flowing text-slate-800">

    <div class="w-full max-w-2xl relative z-10 my-auto">
        <!-- Header Logo -->
        <div class="text-center mb-8">
            <a href="{{ url('/') }}" class="inline-flex items-center gap-2.5 mb-3 group">
                <img src="{{ asset('images/rentify-icon.svg') }}" class="h-10 w-10 rounded-2xl shadow-xs group-hover:scale-105 transition" alt="Rentify">
                <span class="text-3xl font-black text-sky-600 tracking-tight">Rentify<span class="text-amber-500">.</span></span>
            </a>
            <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">Halo, {{ $user->name }}! 👋</h1>
            <p class="text-sm text-slate-600 font-medium mt-1">Akun Anda terdaftar sebagai <span class="font-bold text-sky-600">Customer</span> dan <span class="font-bold text-emerald-600">Mitra Vendor</span>. Pilih peran untuk melanjutkan:</p>
        </div>

        <!-- Role Choices Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-6">

            <!-- KARTU 1: CUSTOMER -->
            <form action="{{ route('role.select.post') }}" method="POST" class="h-full">
                @csrf
                <input type="hidden" name="role" value="customer">
                <button type="submit" class="w-full h-full text-left glass-card role-card p-6 sm:p-7 rounded-3xl border border-sky-200/80 flex flex-col justify-between group cursor-pointer hover:border-sky-400">
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <div class="w-14 h-14 rounded-2xl bg-sky-100/90 text-sky-600 flex items-center justify-center text-2xl group-hover:scale-110 transition shadow-sm">
                                <i class="fa-solid fa-cart-shopping"></i>
                            </div>
                            <span class="text-[10px] font-extrabold uppercase tracking-wider px-3 py-1 rounded-full bg-sky-100 text-sky-700 border border-sky-200">
                                Mode Belanja
                            </span>
                        </div>
                        <h2 class="text-xl font-black text-slate-900 mb-1.5 group-hover:text-sky-600 transition">Customer / Penyewa</h2>
                        <p class="text-xs text-slate-600 leading-relaxed font-medium">
                            Jelajahi aneka barang rental di katalog, lakukan pemesanan, dan pantau status sewa Anda.
                        </p>
                    </div>

                    <div class="mt-6 pt-4 border-t border-slate-200/60 flex items-center justify-between text-xs font-bold text-sky-600 group-hover:translate-x-1 transition">
                        <span>Masuk sebagai Customer</span>
                        <i class="fa-solid fa-arrow-right"></i>
                    </div>
                </button>
            </form>

            <!-- KARTU 2: MITRA VENDOR -->
            <form action="{{ route('role.select.post') }}" method="POST" class="h-full">
                @csrf
                <input type="hidden" name="role" value="vendor">
                <button type="submit" class="w-full h-full text-left glass-card role-card p-6 sm:p-7 rounded-3xl border border-emerald-200/80 flex flex-col justify-between group cursor-pointer hover:border-emerald-400">
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <div class="w-14 h-14 rounded-2xl bg-emerald-100/90 text-emerald-600 flex items-center justify-center text-2xl group-hover:scale-110 transition shadow-sm">
                                <i class="fa-solid fa-store"></i>
                            </div>
                            @php
                                $status = $user->vendor_status ?? 'pending';
                                $badgeColor = $status === 'approved' ? 'bg-emerald-100 text-emerald-700 border-emerald-200' : ($status === 'pending' ? 'bg-amber-100 text-amber-700 border-amber-200' : 'bg-rose-100 text-rose-700 border-rose-200');
                            @endphp
                            <span class="text-[10px] font-extrabold uppercase tracking-wider px-3 py-1 rounded-full border {{ $badgeColor }}">
                                {{ $status === 'approved' ? 'Toko Aktif' : ($status === 'pending' ? 'Menunggu Verifikasi' : 'Status: ' . ucfirst($status)) }}
                            </span>
                        </div>
                        <h2 class="text-xl font-black text-slate-900 mb-1.5 group-hover:text-emerald-600 transition">{{ $user->vendor_name ?: 'Mitra Vendor' }}</h2>
                        <p class="text-xs text-slate-600 leading-relaxed font-medium">
                            Kelola produk sewa toko Anda, proses pesanan masuk, pantau transaksi, dan tarik saldo.
                        </p>
                    </div>

                    <div class="mt-6 pt-4 border-t border-slate-200/60 flex items-center justify-between text-xs font-bold text-emerald-600 group-hover:translate-x-1 transition">
                        <span>Masuk Dashboard Vendor</span>
                        <i class="fa-solid fa-arrow-right"></i>
                    </div>
                </button>
            </form>

        </div>

        <!-- Info Tambahan & Logout -->
        <div class="glass-card p-4 rounded-2xl text-center flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-500 font-medium">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-circle-info text-sky-500 text-sm"></i>
                <span>Anda dapat beralih peran kapan saja tanpa perlu logout.</span>
            </div>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="text-rose-500 hover:text-rose-700 font-bold transition flex items-center gap-1.5 cursor-pointer">
                    <i class="fa-solid fa-power-off text-xs"></i> Keluar
                </button>
            </form>
        </div>
    </div>

</body>
</html>
