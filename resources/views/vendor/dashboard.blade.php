@extends('layouts.vendor')

@section('title', 'Dashboard - Rentify')

@section('content')
<header class="sticky top-0 z-40 glass-card px-10 py-5 flex justify-between items-center shadow-sm">
    <div>
        <h1 class="text-2xl font-extrabold text-slate-800">Halo, {{ explode(' ', trim($user->name ?? Auth::user()->name))[0] }} </h1>
        <p class="text-sm text-slate-500 font-medium mt-1">Pantau ringkasan bisnis penyewaan Anda hari ini.</p>
    </div>
    <div class="flex items-center gap-4">
        <button class="w-11 h-11 rounded-full bg-white border border-slate-200 text-slate-500 hover:text-brand-main hover:border-brand-main transition-all flex items-center justify-center relative shadow-sm">
            <i class="fa-regular fa-bell text-lg"></i>
            <span class="absolute top-2 right-2 w-2.5 h-2.5 bg-rose-500 rounded-full border-2 border-white"></span>
        </button>
        <div class="flex items-center gap-2 px-4 py-2 bg-emerald-50 border border-emerald-100 rounded-full text-emerald-600 font-bold text-sm shadow-sm">
            <div class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></div>
            Toko Aktif
        </div>
    </div>
</header>

<div class="p-10 space-y-8">
    
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        
        <div class="rounded-3xl p-6 gradient-bg text-white shadow-xl shadow-brand-main/20 relative overflow-hidden group">
            <div class="absolute -right-6 -top-6 w-32 h-32 bg-white/10 rounded-full blur-2xl group-hover:bg-white/20 transition-all duration-500"></div>
            <p class="text-brand-light text-sm font-semibold mb-1">Total Saldo Tersedia</p>
            <h3 class="text-3xl font-black mb-4 tracking-tight">Rp {{ number_format($totalSaldo, 0, ',', '.') }}</h3>
            <div class="flex justify-between items-end">
                <a href="{{ route('vendor.saldo.index') }}" class="text-xs font-bold bg-white/20 hover:bg-white/30 px-3 py-1.5 rounded-lg transition-colors backdrop-blur-sm">Tarik Dana</a>
                <i class="fa-solid fa-wallet text-4xl opacity-50"></i>
            </div>
        </div>

        <div class="glass-card rounded-3xl p-6 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-slate-500 text-sm font-semibold mb-1">Pesanan Aktif</p>
                    <h3 class="text-3xl font-black text-slate-800">{{ $jmlPesanan }}</h3>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-500 flex items-center justify-center text-xl shadow-sm">
                    <i class="fa-solid fa-people-carry-box"></i>
                </div>
            </div>
            <p class="text-xs font-semibold text-emerald-500 mt-4 bg-emerald-50 inline-block px-2 py-1 rounded-md"><i class="fa-solid fa-arrow-trend-up"></i> Perlu diproses</p>
        </div>

        <div class="glass-card rounded-3xl p-6 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-slate-500 text-sm font-semibold mb-1">Total Penyewaan</p>
                    <h3 class="text-3xl font-black text-slate-800">{{ $totalPenyewaan }}</h3>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-500 flex items-center justify-center text-xl shadow-sm">
                    <i class="fa-solid fa-handshake"></i>
                </div>
            </div>
            <p class="text-xs font-semibold text-slate-400 mt-4">Transaksi berhasil</p>
        </div>

        <div class="glass-card rounded-3xl p-6 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-slate-500 text-sm font-semibold mb-1">Produk Aktif</p>
                    <h3 class="text-3xl font-black text-slate-800">{{ $produkAktif }} <span class="text-sm font-medium text-slate-400">/ {{ $totalProduk }}</span></h3>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-blue-50 text-brand-main flex items-center justify-center text-xl shadow-sm">
                    <i class="fa-solid fa-box-open"></i>
                </div>
            </div>
            <p class="text-xs font-semibold text-rose-500 mt-4 bg-rose-50 inline-block px-2 py-1 rounded-md">{{ $jmlPending }} Menunggu kurasi</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <div class="lg:col-span-2 glass-card rounded-3xl p-8 shadow-sm">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-lg font-bold text-slate-800">Grafik Pendapatan</h3>
                <select class="bg-slate-50 border border-slate-200 text-slate-600 text-sm rounded-xl focus:ring-brand-main focus:border-brand-main block p-2 font-medium">
                    <option>7 Hari Terakhir</option>
                    <option>Bulan Ini</option>
                </select>
            </div>
            <div class="relative h-72 w-full">
                <canvas id="incomeChart"></canvas>
            </div>
        </div>

        <div class="glass-card rounded-3xl p-8 shadow-sm flex flex-col">
            <h3 class="text-lg font-bold text-slate-800 mb-6">Akses Cepat</h3>
            
            <div class="space-y-4 flex-1">
                <a href="{{ route('vendor.barang.create') }}" class="flex items-center gap-4 p-4 rounded-2xl border border-slate-100 hover:border-brand-main hover:bg-brand-50 transition-all group">
                    <div class="w-12 h-12 rounded-xl bg-brand-light text-brand-main flex items-center justify-center group-hover:scale-110 transition-transform">
                        <i class="fa-solid fa-plus text-xl"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-slate-800 group-hover:text-brand-main transition-colors">Tambah Produk</h4>
                        <p class="text-xs text-slate-500 mt-0.5">Upload barang sewaan baru</p>
                    </div>
                </a>

                <a href="{{ route('vendor.pesanan.index') }}" class="flex items-center gap-4 p-4 rounded-2xl border border-slate-100 hover:border-amber-400 hover:bg-amber-50 transition-all group">
                    <div class="w-12 h-12 rounded-xl bg-amber-100 text-amber-500 flex items-center justify-center group-hover:scale-110 transition-transform">
                        <i class="fa-solid fa-clock-rotate-left text-xl"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-slate-800 group-hover:text-amber-600 transition-colors">Cek Pesanan</h4>
                        <p class="text-xs text-slate-500 mt-0.5">Kelola pesanan masuk</p>
                    </div>
                </a>
            </div>
            
            <div class="mt-6 pt-6 border-t border-slate-100">
                <div class="flex items-center justify-between text-sm">
                    <span class="text-slate-500 font-medium">Performa Toko</span>
                    <span class="font-bold text-emerald-500">Sangat Baik </span>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@stack('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const ctx = document.getElementById('incomeChart').getContext('2d');
        
        // Setup Gradient untuk Area bawah garis
        let gradient = ctx.createLinearGradient(0, 0, 0, 400);
        gradient.addColorStop(0, 'rgba(37, 99, 235, 0.2)'); // Brand Main transparan
        gradient.addColorStop(1, 'rgba(37, 99, 235, 0)');

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'],
                datasets: [{
                    label: 'Pendapatan (Rp)',
                    data: {!! json_encode($chartData) !!}, // Mengambil data dummy dari Controller
                    borderColor: '#2563EB',
                    backgroundColor: gradient,
                    borderWidth: 3,
                    pointBackgroundColor: '#ffffff',
                    pointBorderColor: '#38BDF8',
                    pointBorderWidth: 2,
                    pointRadius: 5,
                    pointHoverRadius: 7,
                    fill: true,
                    tension: 0.4 // Membuat garis melengkung (smooth)
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { borderDash: [5, 5], color: '#f1f5f9' },
                        border: { display: false }
                    },
                    x: {
                        grid: { display: false },
                        border: { display: false }
                    }
                }
            }
        });
    });
</script>