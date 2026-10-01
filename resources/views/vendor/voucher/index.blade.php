@extends('layouts.vendor')

@section('title', 'Voucher Toko — Vendor Rentify')

@section('content')
<div class="px-3.5 sm:px-6 lg:px-8 py-4 sm:py-6 max-w-7xl mx-auto space-y-4 sm:space-y-6">

    <!-- Header Bersih & Profesional -->
    <div class="flex items-center justify-between bg-white p-4 sm:p-5 rounded-2xl sm:rounded-3xl border border-slate-200/80 shadow-xs">
        <div>
            <h1 class="text-base sm:text-xl font-black text-slate-800 tracking-tight">Voucher Diskon Toko</h1>
            <p class="text-[11px] sm:text-xs text-slate-400 font-medium mt-0.5">Kelola kode promosi diskon untuk menarik lebih banyak penyewa</p>
        </div>
        <span class="px-3 py-1 bg-amber-50 text-amber-800 text-xs font-black rounded-xl">
            {{ count($vouchers) }} Voucher
        </span>
    </div>

    @if(session('success'))
        <div class="px-4 py-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl flex items-center gap-2.5 text-xs font-bold shadow-xs">
            <i class="fa-solid fa-circle-check text-emerald-600 text-sm"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif
    
    @if ($errors->any())
        <div class="px-4 py-3 bg-rose-50 border border-rose-200 text-rose-800 rounded-2xl text-xs font-medium space-y-1 shadow-xs">
            @foreach ($errors->all() as $error)
                <p>• {{ $error }}</p>
            @endforeach
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6 items-start">
        
        <!-- KOLOM KIRI: Form Buat Voucher (Compact & Touch-Friendly) -->
        <div class="lg:col-span-1">
            <div class="bg-white p-4 sm:p-5 rounded-2xl sm:rounded-3xl border border-slate-200/80 shadow-xs space-y-3.5">
                <h2 class="text-xs sm:text-sm font-black text-slate-800 uppercase tracking-wider border-b border-slate-100 pb-2.5 flex items-center gap-1.5">
                    <i class="fa-solid fa-plus text-sky-500"></i> Buat Voucher Baru
                </h2>
                
                <form action="{{ route('vendor.voucher.store') }}" method="POST" class="space-y-3">
                    @csrf
                    
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1">Kode Voucher</label>
                        <input type="text" name="kode_voucher" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white text-xs font-black text-slate-800 uppercase focus:outline-none focus:border-sky-500 shadow-xs" placeholder="RENTALHEMAT">
                    </div>

                    <div class="grid grid-cols-2 gap-2.5">
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1">Tipe</label>
                            <select name="tipe_diskon" id="tipe_diskon" onchange="toggleMaksimalDiskon()" required class="w-full px-3 py-2.5 rounded-xl border border-slate-200 bg-white text-xs font-bold text-slate-800 focus:outline-none focus:border-sky-500 shadow-xs">
                                <option value="nominal">Nominal (Rp)</option>
                                <option value="persen">Persentase (%)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1">Nilai Diskon</label>
                            <input type="number" name="nilai_diskon" required min="1" class="w-full px-3 py-2.5 rounded-xl border border-slate-200 bg-white text-xs font-bold text-slate-800 focus:outline-none focus:border-sky-500 shadow-xs" placeholder="10000">
                        </div>
                    </div>

                    <div id="box_maksimal_diskon" class="hidden">
                        <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1">Maks. Potongan (Rp)</label>
                        <input type="number" name="maksimal_diskon" id="maksimal_diskon" min="1" class="w-full px-3 py-2.5 rounded-xl border border-slate-200 bg-white text-xs font-bold text-slate-800 focus:outline-none focus:border-sky-500 shadow-xs" placeholder="50000">
                    </div>

                    <div class="grid grid-cols-2 gap-2.5">
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1">Min. Belanja</label>
                            <input type="number" name="minimal_belanja" required min="0" value="0" class="w-full px-3 py-2.5 rounded-xl border border-slate-200 bg-white text-xs font-bold text-slate-800 focus:outline-none focus:border-sky-500 shadow-xs">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1">Kuota</label>
                            <input type="number" name="kuota_total" required min="1" value="50" class="w-full px-3 py-2.5 rounded-xl border border-slate-200 bg-white text-xs font-bold text-slate-800 focus:outline-none focus:border-sky-500 shadow-xs">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-2.5">
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1">Mulai</label>
                            <input type="date" name="tanggal_mulai" required min="{{ date('Y-m-d') }}" value="{{ date('Y-m-d') }}" class="w-full px-2.5 py-2 rounded-xl border border-slate-200 bg-white text-[11px] font-bold text-slate-800 focus:outline-none focus:border-sky-500 shadow-xs">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1">Selesai</label>
                            <input type="date" name="tanggal_selesai" required min="{{ date('Y-m-d') }}" class="w-full px-2.5 py-2 rounded-xl border border-slate-200 bg-white text-[11px] font-bold text-slate-800 focus:outline-none focus:border-sky-500 shadow-xs">
                        </div>
                    </div>

                    <button type="submit" class="w-full py-3 bg-gradient-to-r from-sky-500 to-blue-600 hover:from-sky-600 text-white font-black text-xs rounded-xl shadow-xs transition active:scale-95">
                        Simpan Voucher
                    </button>
                </form>
            </div>
        </div>

        <!-- KOLOM KANAN: Daftar Kupon Voucher (Sleek Card List) -->
        <div class="lg:col-span-2 space-y-3">
            <h2 class="text-xs sm:text-sm font-black text-slate-800 uppercase tracking-wider">
                Voucher Aktif & Riwayat
            </h2>

            @forelse($vouchers as $v)
                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden flex flex-col sm:flex-row items-stretch">
                    <!-- Sisi Kiri / Header Kupon -->
                    <div class="w-full sm:w-28 {{ $v->is_active && $v->tanggal_selesai >= date('Y-m-d') ? 'bg-gradient-to-br from-sky-500 to-blue-600' : 'bg-slate-300' }} text-white p-3 sm:p-4 flex sm:flex-col items-center justify-between sm:justify-center border-b sm:border-b-0 sm:border-r-2 border-dashed border-white/40">
                        <i class="fa-solid fa-ticket text-xl sm:text-2xl opacity-90"></i>
                        <span class="text-[10px] font-black uppercase tracking-wider text-center mt-0 sm:mt-1">
                            {{ $v->tipe_diskon == 'persen' ? $v->nilai_diskon . '%' : 'Rp ' . number_format($v->nilai_diskon / 1000, 0) . 'k' }}
                        </span>
                    </div>

                    <!-- Isi Kupon -->
                    <div class="flex-1 p-3.5 sm:p-4 flex flex-col justify-between gap-2">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <span class="inline-block px-2 py-0.5 bg-sky-50 text-sky-700 rounded text-[10px] font-black uppercase tracking-widest border border-sky-100">
                                    {{ $v->kode_voucher }}
                                </span>
                                <h3 class="font-black text-slate-800 text-xs sm:text-sm mt-1">
                                    @if($v->tipe_diskon == 'persen')
                                        Diskon {{ $v->nilai_diskon }}% @if($v->maksimal_diskon)(Maks. Rp {{ number_format($v->maksimal_diskon, 0, ',', '.') }})@endif
                                    @else
                                        Potongan Rp {{ number_format($v->nilai_diskon, 0, ',', '.') }}
                                    @endif
                                </h3>
                                <p class="text-[11px] text-slate-400 mt-0.5">Min. sewa: Rp {{ number_format($v->minimal_belanja, 0, ',', '.') }}</p>
                            </div>

                            <form action="{{ route('vendor.voucher.destroy', $v->id) }}" method="POST" onsubmit="return confirm('Hapus voucher promo ini?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="w-7 h-7 rounded-lg bg-rose-50 text-rose-500 hover:bg-rose-100 flex items-center justify-center transition active:scale-90" title="Hapus">
                                    <i class="fa-solid fa-trash text-xs"></i>
                                </button>
                            </form>
                        </div>

                        <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-[10px] font-medium text-slate-500">
                            <span>
                                Berlaku: {{ \Carbon\Carbon::parse($v->tanggal_mulai)->format('d M') }} — {{ \Carbon\Carbon::parse($v->tanggal_selesai)->format('d M Y') }}
                            </span>
                            <span>
                                Kuota: <strong class="text-slate-800">{{ $v->kuota_terpakai }}/{{ $v->kuota_total }}</strong>
                            </span>
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-white p-8 rounded-2xl border border-slate-200/80 text-center text-slate-400">
                    <i class="fa-solid fa-ticket text-3xl mb-2 text-slate-300"></i>
                    <p class="text-xs font-medium">Belum ada voucher diskon aktif.</p>
                </div>
            @endforelse
        </div>

    </div>

</div>

<script>
    function toggleMaksimalDiskon() {
        const tipe = document.getElementById('tipe_diskon').value;
        const box = document.getElementById('box_maksimal_diskon');
        const input = document.getElementById('maksimal_diskon');
        if (tipe === 'persen') {
            box.classList.remove('hidden');
            input.required = true;
        } else {
            box.classList.add('hidden');
            input.required = false;
            input.value = '';
        }
    }
</script>
@endsection