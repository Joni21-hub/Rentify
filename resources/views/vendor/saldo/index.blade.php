@extends('layouts.vendor')

@section('title', 'Keuangan & Penarikan — Vendor Rentify')

@section('content')
<div class="px-3.5 sm:px-6 lg:px-8 py-4 sm:py-6 max-w-7xl mx-auto space-y-4 sm:space-y-6">

    <!-- Header Bersih & Profesional -->
    <div class="flex items-center justify-between bg-white p-4 sm:p-5 rounded-2xl sm:rounded-3xl border border-slate-200/80 shadow-xs">
        <div>
            <h1 class="text-base sm:text-xl font-black text-slate-800 tracking-tight">Keuangan & Saldo</h1>
            <p class="text-[11px] sm:text-xs text-slate-400 font-medium mt-0.5">Pantau saldo pendapatan toko dan ajukan penarikan dana ke rekening</p>
        </div>
        <a href="{{ route('vendor.saldo.export') }}" class="inline-flex items-center gap-1.5 px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition shadow-xs">
            <i class="fa-solid fa-file-excel text-emerald-600 text-xs"></i>
            <span class="hidden sm:inline">Export Excel</span>
        </a>
    </div>

    @if(session('success'))
        <div class="px-4 py-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl flex items-center gap-2.5 text-xs font-bold shadow-xs">
            <i class="fa-solid fa-circle-check text-emerald-600 text-sm"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6 items-start">
        
        <!-- KOLOM KIRI (2 SPAN): KARTU SALDO & RIWAYAT MUTASI -->
        <div class="lg:col-span-2 space-y-4">
            
            <!-- Dua Kartu Saldo Ringkas -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div class="rounded-2xl sm:rounded-3xl p-4 sm:p-5 bg-gradient-to-br from-slate-900 via-slate-800 to-sky-950 text-white shadow-sm relative overflow-hidden">
                    <span class="text-xs font-bold text-sky-200 flex items-center gap-1.5 mb-1">
                        <i class="fa-solid fa-wallet text-sky-400"></i> Saldo Siap Ditarik
                    </span>
                    <h3 class="text-2xl sm:text-3xl font-black tracking-tight text-white mb-1">
                        Rp {{ number_format($saldo->saldo_aktif ?? 0, 0, ',', '.') }}
                    </h3>
                    <p class="text-[10px] text-sky-200/70 font-medium">Bisa ditarik ke rekening bank / e-wallet</p>
                </div>

                <div class="rounded-2xl sm:rounded-3xl p-4 sm:p-5 bg-white border border-slate-200/80 shadow-xs flex flex-col justify-between">
                    <div>
                        <span class="text-xs font-bold text-slate-500 flex items-center gap-1.5 mb-1">
                            <i class="fa-solid fa-clock text-amber-500"></i> Saldo Dalam Proses Tarik
                        </span>
                        <h3 class="text-2xl sm:text-3xl font-black text-amber-600 mb-1">
                            Rp {{ number_format($saldo->saldo_ditahan ?? 0, 0, ',', '.') }}
                        </h3>
                    </div>
                    <p class="text-[10px] text-slate-400 font-medium">Sedang diproses transfer oleh Admin</p>
                </div>
            </div>

            <!-- Riwayat Mutasi Transaksi -->
            <div class="bg-white rounded-2xl sm:rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
                <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="text-xs sm:text-sm font-black text-slate-800 uppercase tracking-wider">
                        Riwayat Mutasi Saldo
                    </h3>
                    <span class="text-[10px] font-bold text-slate-400">Terbaru</span>
                </div>

                <!-- Tampilan Mobile: List Card/Row -->
                <div class="block lg:hidden divide-y divide-slate-100">
                    @forelse($riwayatMutasi as $mutasi)
                        <div class="p-3.5 flex items-center justify-between gap-3">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-9 h-9 rounded-xl {{ $mutasi->bg }} {{ $mutasi->color }} flex items-center justify-center text-xs shrink-0">
                                    <i class="fa-solid {{ $mutasi->icon }}"></i>
                                </div>
                                <div class="min-w-0">
                                    <p class="font-black text-xs text-slate-800 truncate">{{ $mutasi->jenis }}</p>
                                    <p class="text-[10px] text-slate-400 truncate">{{ \Carbon\Carbon::parse($mutasi->tanggal)->format('d M Y, H:i') }}</p>
                                </div>
                            </div>
                            <div class="text-right shrink-0">
                                <p class="text-xs font-black {{ $mutasi->operator == '+' ? 'text-emerald-600' : 'text-rose-600' }}">
                                    {{ $mutasi->operator }} Rp {{ number_format($mutasi->nominal, 0, ',', '.') }}
                                </p>
                                @if($mutasi->status == 'pending')
                                    <span class="text-[9px] font-bold text-amber-700 bg-amber-50 px-1.5 py-0.5 rounded">Diproses</span>
                                @elseif($mutasi->status == 'disetujui' || $mutasi->status == 'berhasil')
                                    <span class="text-[9px] font-bold text-emerald-700 bg-emerald-50 px-1.5 py-0.5 rounded">Sukses</span>
                                @else
                                    <span class="text-[9px] font-bold text-rose-700 bg-rose-50 px-1.5 py-0.5 rounded">Ditolak</span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="p-8 text-center text-slate-400 text-xs">
                            Belum ada riwayat mutasi saldo.
                        </div>
                    @endforelse
                </div>

                <!-- Tampilan Desktop: Table -->
                <div class="hidden lg:block overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="bg-slate-50/50 border-b border-slate-100 text-slate-400 font-black uppercase text-[10px] tracking-wider">
                                <th class="px-6 py-3.5">Detail Transaksi</th>
                                <th class="px-6 py-3.5">Nominal</th>
                                <th class="px-6 py-3.5 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($riwayatMutasi as $mutasi)
                                <tr class="hover:bg-slate-50/60 transition">
                                    <td class="px-6 py-3.5">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-lg {{ $mutasi->bg }} {{ $mutasi->color }} flex items-center justify-center text-xs shrink-0">
                                                <i class="fa-solid {{ $mutasi->icon }}"></i>
                                            </div>
                                            <div>
                                                <p class="font-black text-slate-800 text-xs">{{ $mutasi->jenis }}</p>
                                                <p class="text-[10px] text-slate-400">{{ \Carbon\Carbon::parse($mutasi->tanggal)->format('d M Y, H:i') }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-3.5 font-black {{ $mutasi->operator == '+' ? 'text-emerald-600' : 'text-rose-600' }}">
                                        {{ $mutasi->operator }} Rp {{ number_format($mutasi->nominal, 0, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-3.5 text-center">
                                        @if($mutasi->status == 'pending')
                                            <span class="px-2 py-0.5 bg-amber-50 text-amber-700 rounded text-[10px] font-bold">Diproses</span>
                                        @elseif($mutasi->status == 'disetujui' || $mutasi->status == 'berhasil')
                                            <span class="px-2 py-0.5 bg-emerald-50 text-emerald-700 rounded text-[10px] font-bold">Sukses</span>
                                        @else
                                            <span class="px-2 py-0.5 bg-rose-50 text-rose-700 rounded text-[10px] font-bold">Ditolak</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-6 py-8 text-center text-slate-400">Belum ada riwayat mutasi saldo.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            </div>

        </div>

        <!-- KOLOM KANAN (1 SPAN): FORM PENARIKAN DANA (COMPACT) -->
        <div class="lg:col-span-1">
            <div class="bg-white p-4 sm:p-5 rounded-2xl sm:rounded-3xl border border-slate-200/80 shadow-xs space-y-3.5">
                <h2 class="text-xs sm:text-sm font-black text-slate-800 uppercase tracking-wider border-b border-slate-100 pb-2.5 flex items-center gap-1.5">
                    <i class="fa-solid fa-money-bill-transfer text-sky-500"></i> Form Tarik Dana
                </h2>

                <form action="{{ route('vendor.saldo.tarik') }}" method="POST" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1">Nominal (Rp)</label>
                        <input type="number" name="nominal" min="10000" max="{{ $saldo->saldo_aktif ?? 0 }}" required placeholder="Min. 10000" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white text-xs font-black text-slate-800 focus:outline-none focus:border-sky-500 shadow-xs">
                        <span class="text-[10px] text-slate-400 mt-1 block">Tersedia: Rp {{ number_format($saldo->saldo_aktif ?? 0, 0, ',', '.') }}</span>
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1">Metode Pencairan</label>
                        <select name="metode" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white text-xs font-bold text-slate-800 focus:outline-none focus:border-sky-500 shadow-xs">
                            <option value="Bank">Transfer Bank</option>
                            <option value="E-Wallet">E-Wallet (Dana / GoPay / OVO)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1">Nama Bank / E-Wallet</label>
                        <input type="text" name="nama_bank_ewallet" required placeholder="BCA / Mandiri / Dana" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white text-xs font-bold text-slate-800 focus:outline-none focus:border-sky-500 shadow-xs">
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1">Nomor Rekening / No. HP</label>
                        <input type="text" name="nomor_rekening" required placeholder="0123456789" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white text-xs font-bold text-slate-800 focus:outline-none focus:border-sky-500 shadow-xs">
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1">Nama Pemilik Akun</label>
                        <input type="text" name="nama_pemilik" required placeholder="Atas nama sesuai rekening" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white text-xs font-bold text-slate-800 focus:outline-none focus:border-sky-500 shadow-xs">
                    </div>

                    <button type="submit" class="w-full py-3 bg-gradient-to-r from-sky-500 to-blue-600 hover:from-sky-600 text-white font-black text-xs rounded-xl shadow-xs transition active:scale-95">
                        Ajukan Penarikan
                    </button>
                </form>
            </div>
        </div>

    </div>

</div>
@endsection