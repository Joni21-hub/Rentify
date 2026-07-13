@extends('layouts.vendor')

@section('title', 'Manajemen Produk - Vendor Rentify')

@section('content')
<div class="p-4 md:p-8">
    <header class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="text-3xl font-black text-slate-800 tracking-tight">Manajemen Produk <span class="text-2xl"></span></h1>
            <p class="text-slate-500 mt-2 font-medium">Kelola daftar barang sewaan Anda dan pantau status persetujuan dari Admin Rentify.</p>
        </div>
        
        <a href="{{ route('vendor.barang.create') }}" class="inline-flex items-center justify-center gap-2 px-6 py-3.5 gradient-bg text-white font-bold rounded-2xl hover:opacity-90 transition-opacity shadow-lg shadow-brand-main/30 group">
            <i class="fa-solid fa-plus group-hover:rotate-90 transition-transform duration-300"></i> Tambah Barang Baru
        </a>
    </header>

    @if(session('success'))
        <div class="mb-8 px-5 py-4 bg-emerald-50 border border-emerald-100 text-emerald-700 rounded-2xl flex items-center gap-3 shadow-sm animate-fade-in-down">
            <div class="w-8 h-8 rounded-full bg-emerald-500 text-white flex items-center justify-center flex-shrink-0">
                <i class="fa-solid fa-check"></i>
            </div>
            <p class="font-semibold">{{ session('success') }}</p>
        </div>
    @endif

    <!-- Toolbar Pencarian & Filter -->
    <form method="GET" action="{{ route('vendor.barang.index') }}" class="flex flex-col md:flex-row gap-4 mb-6 w-full">
        <div class="relative flex-1">
            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                <i class="fa-solid fa-magnifying-glass text-slate-400"></i>
            </div>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama produk Anda..." class="w-full pl-11 pr-4 py-3.5 bg-white border border-slate-200 rounded-2xl text-sm focus:ring-2 focus:ring-brand-main/20 focus:border-brand-main outline-none transition-all shadow-sm font-medium placeholder-slate-400">
        </div>
        <div class="flex gap-3">
            <select name="status" class="px-4 py-3.5 bg-white border border-slate-200 rounded-2xl text-sm font-medium text-slate-600 focus:ring-2 focus:ring-brand-main/20 focus:border-brand-main outline-none shadow-sm transition-all">
                <option value="">Semua Status</option>
                <option value="disetujui" {{ request('status') == 'disetujui' ? 'selected' : '' }}>Aktif (Disetujui)</option>
                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Menunggu Kurasi</option>
            </select>
            <button type="submit" class="px-5 py-3.5 bg-white border border-slate-200 rounded-2xl text-slate-600 hover:text-brand-main hover:border-brand-main transition-colors shadow-sm font-bold">
                <i class="fa-solid fa-filter"></i> Terapkan
            </button>
        </div>
    </form>

    <div class="glass-card rounded-3xl shadow-sm border border-white/50 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/50 border-b border-slate-100">
                        <th class="px-6 py-5 text-xs font-extrabold text-slate-400 uppercase tracking-widest whitespace-nowrap">Info Barang</th>
                        <th class="px-6 py-5 text-xs font-extrabold text-slate-400 uppercase tracking-widest whitespace-nowrap">Harga Sewa</th>
                        <th class="px-6 py-5 text-xs font-extrabold text-slate-400 uppercase tracking-widest whitespace-nowrap">Stok</th>
                        <th class="px-6 py-5 text-xs font-extrabold text-slate-400 uppercase tracking-widest whitespace-nowrap">Status Kurasi</th>
                        <th class="px-6 py-5 text-xs font-extrabold text-slate-400 uppercase tracking-widest text-right whitespace-nowrap">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    
                    @forelse($barangs as $barang)
                    <tr class="hover:bg-blue-50/30 transition-colors duration-200 group">
                        <td class="px-6 py-5">
                            <div class="flex items-center gap-4">
                                <div class="w-16 h-16 rounded-2xl bg-slate-100 overflow-hidden flex-shrink-0 border border-slate-200 shadow-sm relative group-hover:shadow-md transition-all">
                                    @if($barang->cover_photo)
                                        <!-- SESUDAHNYA (Hapus kata 'storage/') -->
                                        <img src="{{ asset(str_replace('public/', '', $barang->cover_photo)) }}" alt="{{ $barang->nama }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center text-slate-300 bg-slate-50">
                                            <i class="fa-solid fa-image text-2xl"></i>
                                        </div>
                                    @endif
                                </div>
                                <div>
                                    <h3 class="font-bold text-slate-800 text-base group-hover:text-brand-main transition-colors">{{ $barang->nama }}</h3>
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-slate-100 text-[11px] font-semibold text-slate-500 mt-1.5">
                                        <i class="fa-solid fa-tags text-brand-sky"></i> 
                                        {{ $barang->kategori->nama ?? 'Tanpa Kategori' }}
                                    </span>
                                </div>
                            </div>
                        </td>

                        <td class="px-6 py-5 align-middle">
                            <div class="flex flex-col">
                                <span class="font-black text-slate-800 text-base">Rp {{ number_format($barang->harga_sewa_harian, 0, ',', '.') }}</span>
                                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Per Hari</span>
                            </div>
                        </td>

                        <td class="px-6 py-5 align-middle">
                            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-blue-50 text-sm font-bold text-brand-main border border-blue-100 shadow-sm">
                                <i class="fa-solid fa-boxes-stacked opacity-70"></i> {{ $barang->stok_total }}
                            </div>
                        </td>

                        <td class="px-6 py-5 align-middle">
                            @if($barang->is_approved == 1)
                                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-emerald-50 border border-emerald-100 text-emerald-600 text-xs font-bold shadow-sm">
                                    <div class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></div>
                                    Disetujui
                                </div>
                            @else
                                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-amber-50 border border-amber-100 text-amber-600 text-xs font-bold shadow-sm">
                                    <i class="fa-solid fa-clock-rotate-left opacity-70"></i>
                                    Menunggu
                                </div>
                            @endif
                        </td>

                        <td class="px-6 py-5 align-middle text-right">
                            <div class="flex items-center justify-end gap-2 opacity-100 md:opacity-0 md:group-hover:opacity-100 transition-opacity duration-300">
                                
                                <a href="{{ route('vendor.barang.show', $barang->id) }}" class="w-9 h-9 rounded-xl bg-sky-50 text-brand-sky hover:bg-brand-sky hover:text-white transition-colors flex items-center justify-center tooltip border border-sky-100" title="Lihat Detail">
                                    <i class="fa-solid fa-eye text-sm"></i>
                                </a>

                                <form action="{{ route('vendor.barang.destroy', $barang->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus barang ({{ $barang->nama }}) ini secara permanen?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="w-9 h-9 rounded-xl bg-rose-50 text-rose-500 hover:bg-rose-500 hover:text-white transition-colors flex items-center justify-center tooltip border border-rose-100" title="Hapus Barang">
                                        <i class="fa-solid fa-trash-can text-sm"></i>
                                    </button>
                                </form>
                                
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-20 text-center">
                            <div class="inline-flex w-24 h-24 rounded-full bg-gradient-to-br from-blue-50 to-sky-50 text-brand-main items-center justify-center mb-5 shadow-inner border border-white">
                                <i class="fa-solid fa-box-open text-4xl opacity-80"></i>
                            </div>
                            <h3 class="text-xl font-black text-slate-800 mb-2">Belum ada produk di etalase</h3>
                            <p class="text-slate-500 text-sm max-w-md mx-auto mb-6 font-medium">Toko Anda saat ini masih kosong. Mulai tambahkan barang sewaan pertama Anda agar pelanggan bisa mulai menyewa.</p>
                            <a href="{{ route('vendor.barang.create') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-white border border-slate-200 text-slate-700 font-bold rounded-xl hover:text-brand-main hover:border-brand-main hover:shadow-md transition-all text-sm">
                                <i class="fa-solid fa-plus"></i> Tambah Barang Sekarang
                            </a>
                        </td>
                    </tr>
                    @endforelse

                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
    /* Tambahan animasi ringan saat notifikasi muncul */
    @keyframes fadeInDown {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-fade-in-down {
        animation: fadeInDown 0.5s ease-out;
    }
</style>
@endsection