@extends('layouts.vendor')

@section('title', 'Saldo & Penarikan - Vendor Rentify')

@section('content')
<div class="p-4 md:p-8">
    <header class="mb-8">
        <h1 class="text-3xl font-black text-slate-800 tracking-tight">Keuangan & Penarikan <span class="text-2xl">💳</span></h1>
        <p class="text-slate-500 mt-2 font-medium">Kelola pendapatan toko, pantau riwayat mutasi, dan ajukan penarikan dana.</p>
    </header>

    @if(session('success'))
        <div class="mb-8 px-5 py-4 bg-emerald-50 border border-emerald-100 text-emerald-700 rounded-2xl flex items-center gap-3 shadow-sm">
            <div class="w-8 h-8 rounded-full bg-emerald-500 text-white flex items-center justify-center flex-shrink-0"><i class="fa-solid fa-check"></i></div>
            <p class="font-semibold">{{ session('success') }}</p>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
        
        <div class="lg:col-span-2 space-y-6">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="rounded-3xl p-6 gradient-bg text-white shadow-xl shadow-brand-main/20 relative overflow-hidden">
                    <div class="absolute -right-6 -top-6 w-32 h-32 bg-white/10 rounded-full blur-2xl"></div>
                    <p class="text-brand-light text-sm font-semibold mb-1">Saldo Aktif (Bisa Ditarik)</p>
                    <h3 class="text-4xl font-black mb-4 tracking-tight">Rp {{ number_format($saldo->saldo_aktif ?? 0, 0, ',', '.') }}</h3>
                    <p class="text-[10px] text-brand-light/80 font-medium"><i class="fa-solid fa-circle-info mr-1"></i> Bertambah saat pesanan QRIS selesai.</p>
                </div>

                <div class="glass-card rounded-3xl p-6 shadow-sm border border-slate-200 bg-white">
                    <p class="text-slate-500 text-sm font-semibold mb-1">Saldo Ditahan (Proses Tarik)</p>
                    <h3 class="text-4xl font-black text-amber-500 mb-4 tracking-tight">Rp {{ number_format($saldo->saldo_ditahan ?? 0, 0, ',', '.') }}</h3>
                    <p class="text-[10px] text-slate-400 font-medium"><i class="fa-solid fa-clock mr-1"></i> Menunggu transfer Admin.</p>
                </div>
            </div>

            <div class="glass-card rounded-3xl shadow-sm border border-slate-200 bg-white overflow-hidden">
                <div class="p-6 border-b border-slate-100 bg-slate-50 flex items-center justify-between">
                    <h3 class="font-extrabold text-lg text-slate-800">Mutasi & Riwayat Transaksi</h3>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm border-collapse">
                        <thead class="bg-white border-b border-slate-100">
                            <tr>
                                <th class="px-6 py-4 text-xs font-extrabold text-slate-400 uppercase">Detail Transaksi</th>
                                <th class="px-6 py-4 text-xs font-extrabold text-slate-400 uppercase">Nominal</th>
                                <th class="px-6 py-4 text-xs font-extrabold text-slate-400 uppercase text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @forelse($riwayatMutasi as $mutasi)
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="flex items-start gap-4">
                                            <div class="w-10 h-10 rounded-xl {{ $mutasi->bg }} {{ $mutasi->color }} flex items-center justify-center text-sm flex-shrink-0">
                                                <i class="fa-solid {{ $mutasi->icon }}"></i>
                                            </div>
                                            <div>
                                                <p class="font-bold text-slate-800">{{ $mutasi->jenis }}</p>
                                                <p class="text-xs text-slate-500">{{ $mutasi->keterangan }}</p>
                                                <p class="text-[10px] font-bold text-slate-400 mt-1"><i class="fa-regular fa-calendar"></i> {{ \Carbon\Carbon::parse($mutasi->tanggal)->format('d M Y, H:i') }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 align-middle">
                                        <span class="font-black {{ $mutasi->operator == '+' ? 'text-emerald-600' : 'text-rose-600' }} text-base whitespace-nowrap">
                                            {{ $mutasi->operator }} Rp {{ number_format($mutasi->nominal, 0, ',', '.') }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center align-middle">
                                        @if($mutasi->status == 'pending')
                                            <span class="px-3 py-1 bg-amber-50 text-amber-600 rounded-lg text-[10px] font-bold uppercase">Pending</span>
                                        @elseif($mutasi->status == 'disetujui' || $mutasi->status == 'berhasil')
                                            <span class="px-3 py-1 bg-emerald-50 text-emerald-600 rounded-lg text-[10px] font-bold uppercase">Berhasil</span>
                                        @else
                                            <span class="px-3 py-1 bg-rose-50 text-rose-600 rounded-lg text-[10px] font-bold uppercase">Ditolak</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-6 py-10 text-center text-slate-500 font-medium">Belum ada riwayat mutasi.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="p-4 border-t border-slate-100 bg-slate-50 flex justify-end">
                    <a href="{{ route('vendor.saldo.export') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-slate-200 text-slate-600 font-bold rounded-lg hover:text-emerald-600 hover:border-emerald-300 transition-all text-xs shadow-sm">
                        <i class="fa-solid fa-file-excel text-emerald-500"></i> Unduh Laporan (.xls)
                    </a>
                </div>

            </div>
        </div>

        <div class="lg:col-span-1">
            <div class="glass-card rounded-3xl p-6 shadow-sm border border-slate-200 bg-white sticky top-28">
                <h3 class="font-extrabold text-lg text-slate-800 mb-4 border-b border-slate-100 pb-4"><i class="fa-solid fa-money-bill-transfer text-brand-main mr-2"></i> Ajukan Penarikan</h3>

                <form action="{{ route('vendor.saldo.tarik') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-extrabold text-slate-500 uppercase mb-1">Nominal Tarik (Rp)</label>
                        <input type="number" name="nominal" min="10000" max="{{ $saldo->saldo_aktif ?? 0 }}" required class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold focus:ring-2 focus:ring-brand-main/20 outline-none" placeholder="10000">
                    </div>
                    <div>
                        <label class="block text-xs font-extrabold text-slate-500 uppercase mb-1">Metode</label>
                        <select name="metode" required class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold focus:ring-2 focus:ring-brand-main/20 outline-none">
                            <option value="Bank">Transfer Bank Lokal</option>
                            <option value="E-Wallet">E-Wallet (Dana, GoPay, OVO)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-extrabold text-slate-500 uppercase mb-1">Nama Bank / E-Wallet</label>
                        <input type="text" name="nama_bank_ewallet" required class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-brand-main/20 outline-none" placeholder="Contoh: BCA">
                    </div>
                    <div>
                        <label class="block text-xs font-extrabold text-slate-500 uppercase mb-1">No. Rekening / HP</label>
                        <input type="text" name="nomor_rekening" required class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold focus:ring-2 focus:ring-brand-main/20 outline-none" placeholder="Nomor rekening">
                    </div>
                    <div>
                        <label class="block text-xs font-extrabold text-slate-500 uppercase mb-1">Nama Pemilik Rekening</label>
                        <input type="text" name="nama_pemilik" required class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-brand-main/20 outline-none" placeholder="Atas nama">
                    </div>
                    <button type="submit" class="w-full bg-navydark hover:bg-slate-800 text-white font-bold py-3.5 rounded-xl transition-all shadow-md mt-2">
                        Kirim Pengajuan
                    </button>
                </form>
            </div>
        </div>

    </div>
</div>
@endsection