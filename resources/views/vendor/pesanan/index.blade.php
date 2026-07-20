@extends('layouts.vendor')

@section('title', 'Pesanan Masuk - Vendor Rentify')

@section('content')
<div class="p-4 md:p-8">
    <header class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="text-3xl font-black text-slate-800 tracking-tight">Pesanan Masuk <span class="text-2xl"></span></h1>
            <p class="text-slate-500 mt-2 font-medium">Kelola daftar pesanan dari customer, proses penyewaan, dan pantau riwayat.</p>
        </div>
    </header>

    @if(session('success'))
        <div class="mb-8 px-5 py-4 bg-emerald-50 border border-emerald-100 text-emerald-700 rounded-2xl flex items-center gap-3 shadow-sm animate-fade-in-down">
            <div class="w-8 h-8 rounded-full bg-emerald-500 text-white flex items-center justify-center flex-shrink-0"><i class="fa-solid fa-check"></i></div>
            <p class="font-semibold">{{ session('success') }}</p>
        </div>
    @endif

    <div class="glass-card rounded-3xl shadow-sm border border-white/50 overflow-hidden">
        <div class="p-6 border-b border-slate-100 bg-white/50 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <h3 class="font-extrabold text-lg text-slate-800">Riwayat Transaksi Customer</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm border-collapse">
                <thead class="bg-slate-50/50 border-b border-slate-100">
                    <tr>
                        <th class="px-6 py-5 text-xs font-extrabold text-slate-400 uppercase tracking-widest">Tgl Transaksi</th>
                        <th class="px-6 py-5 text-xs font-extrabold text-slate-400 uppercase tracking-widest">Customer & Jadwal</th>
                        <th class="px-6 py-5 text-xs font-extrabold text-slate-400 uppercase tracking-widest">Metode Bayar</th>
                        <th class="px-6 py-5 text-xs font-extrabold text-slate-400 uppercase tracking-widest">Total Tagihan</th>
                        <th class="px-6 py-5 text-xs font-extrabold text-slate-400 uppercase tracking-widest text-center">Status</th>
                        <th class="px-6 py-5 text-xs font-extrabold text-slate-400 uppercase tracking-widest text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($pesananMasuk as $pesanan)
                        <tr class="hover:bg-blue-50/30 transition-colors group">
                            <td class="px-6 py-5 whitespace-nowrap">
                                <p class="font-bold text-slate-700">#ORD-{{ $pesanan->id }}</p>
                                <p class="text-xs text-slate-500 mt-1 font-medium"><i class="fa-regular fa-clock mr-1 text-brand-sky"></i> {{ \Carbon\Carbon::parse($pesanan->created_at)->format('d M Y, H:i') }}</p>
                            </td>
                            <td class="px-6 py-5">
                                <p class="font-bold text-slate-800">{{ $pesanan->customer_name }}</p>
                                
                                <!-- BENTENG JADWAL (VENDOR INDEX): Menampilkan Jadwal Kapan Barang Harus Diserahkan -->
                                <div class="mt-2 inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-blue-50 border border-blue-100 text-brand-main text-[10px] font-bold">
                                    <i class="fa-regular fa-calendar-check"></i> Mulai: {{ \Carbon\Carbon::parse($pesanan->start_rent)->format('d M Y') }}
                                </div>
                            </td>
                            <td class="px-6 py-5 align-middle">
                                @if(strtoupper($pesanan->payment_method) == 'QRIS')
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-blue-50 border border-blue-100 text-brand-main text-[11px] font-bold shadow-sm">
                                        <i class="fa-solid fa-qrcode"></i> QRIS
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-orange-50 border border-orange-100 text-orange-600 text-[11px] font-bold shadow-sm">
                                        <i class="fa-solid fa-money-bill-wave"></i> COD
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-5 align-middle">
                                <span class="font-black text-slate-800 text-base">Rp {{ number_format($pesanan->total_price, 0, ',', '.') }}</span>
                            </td>
                            <td class="px-6 py-5 text-center align-middle">
                                @php
                                    $statusClass = 'bg-slate-50 text-slate-600 border-slate-200';
                                    $icon = 'fa-circle-info';
                                    if($pesanan->status == 'Menunggu Konfirmasi') { $statusClass = 'bg-amber-50 text-amber-600 border-amber-100'; $icon = 'fa-clock'; }
                                    elseif($pesanan->status == 'Disetujui') { $statusClass = 'bg-blue-50 text-brand-main border-blue-100'; $icon = 'fa-thumbs-up'; }
                                    elseif($pesanan->status == 'Sedang Disewa') { $statusClass = 'bg-purple-50 text-purple-600 border-purple-100'; $icon = 'fa-people-carry-box'; }
                                    elseif($pesanan->status == 'Selesai') { $statusClass = 'bg-emerald-50 text-emerald-600 border-emerald-100'; $icon = 'fa-check'; }
                                    elseif($pesanan->status == 'Dibatalkan') { $statusClass = 'bg-rose-50 text-rose-600 border-rose-100'; $icon = 'fa-xmark'; }
                                @endphp
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl {{ $statusClass }} border text-[11px] font-bold shadow-sm whitespace-nowrap">
                                    <i class="fa-solid {{ $icon }}"></i> {{ $pesanan->status }}
                                </span>
                            </td>
                            <td class="px-6 py-5 text-right align-middle">
                                <a href="{{ route('vendor.pesanan.show', $pesanan->id) }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-slate-50 text-brand-main hover:bg-brand-main hover:text-white transition-colors border border-slate-200 hover:border-brand-main font-bold text-xs shadow-sm">
                                    Proses / Detail <i class="fa-solid fa-arrow-right"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-20 text-center">
                                <div class="inline-flex w-24 h-24 rounded-full bg-gradient-to-br from-blue-50 to-sky-50 text-brand-main items-center justify-center mb-5 shadow-inner border border-white">
                                    <i class="fa-solid fa-clipboard-list text-4xl opacity-80"></i>
                                </div>
                                <h3 class="text-xl font-black text-slate-800 mb-2">Belum ada pesanan</h3>
                                <p class="text-slate-500 text-sm max-w-md mx-auto font-medium">Saat ini belum ada customer yang menyewa barang Anda.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
<style>
    @keyframes fadeInDown { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
    .animate-fade-in-down { animation: fadeInDown 0.5s ease-out; }
</style>
@endsection