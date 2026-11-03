@extends('layouts.vendor')

@php
    /** @var \Illuminate\Database\Eloquent\Collection<int, \App\Models\Voucher> $vouchers */
    /** @var array $promoAudit */
    $vouchers = $vouchers ?? collect();
    $promoAudit = $promoAudit ?? [];
@endphp

@section('title', 'Voucher Toko — Vendor Rentify')

@section('content')
<div class="px-3.5 sm:px-6 lg:px-8 py-4 sm:py-6 max-w-7xl mx-auto space-y-4 sm:space-y-6">

    <!-- Header Bersih & Profesional -->
    <div class="flex items-center justify-between bg-white p-4 sm:p-5 rounded-2xl sm:rounded-3xl border border-slate-200/80 shadow-xs">
        <div>
            <h1 class="text-base sm:text-xl font-black text-slate-800 tracking-tight">Voucher Diskon Toko</h1>
            <p class="text-[11px] sm:text-xs text-slate-400 font-medium mt-0.5">Kelola kupon promosi publik di etalase atau voucher rahasia khusus pelanggan pilihan</p>
        </div>
        <span class="px-3 py-1 bg-sky-50 text-sky-700 text-xs font-black rounded-xl border border-sky-200/60">
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

    @if(isset($promoAudit['recommendations']) && count($promoAudit['recommendations']) > 0)
        <!-- PANEL REKOMENDASI AUTO-PROMOSI CERDAS (AI MARKETING ENGINE) -->
        <div class="bg-gradient-to-r from-sky-500/15 via-sky-400/5 to-transparent p-4 sm:p-5 rounded-2xl sm:rounded-3xl border border-sky-200/80 shadow-xs space-y-3">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-sky-200/40 pb-2.5">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-gradient-to-br from-sky-400 to-sky-600 text-white flex items-center justify-center text-xs shadow-xs">
                        <i class="fa-solid fa-bolt"></i>
                    </div>
                    <div>
                        <h2 class="text-xs sm:text-sm font-black text-slate-800">Rekomendasi Auto-Promosi Cerdas</h2>
                        <p class="text-[11px] text-slate-500">Ditemukan {{ count($promoAudit['recommendations']) }} barang butuh dorongan diskon agar cepat disewa kembali</p>
                    </div>
                </div>
                <span class="text-[10px] font-black text-sky-700 bg-sky-100/80 px-2.5 py-1 rounded-full uppercase tracking-wider self-start sm:self-auto border border-sky-200/60">
                    Smart Marketing Assistant
                </span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3 pt-1">
                @foreach($promoAudit['recommendations'] as $rec)
                    <div class="bg-white p-3.5 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col justify-between gap-3 hover:border-sky-300 transition group">
                        <div>
                            <div class="flex items-center justify-between gap-1 mb-1">
                                <span class="text-[9px] font-bold text-sky-600 bg-sky-50 px-2 py-0.5 rounded-md truncate">
                                    {{ $rec['kategori'] }}
                                </span>
                                <span class="text-[9px] font-black text-rose-600 bg-rose-50 px-2 py-0.5 rounded-full">
                                    Diskon {{ $rec['nilai_diskon'] }}%
                                </span>
                            </div>
                            <h3 class="text-xs font-black text-slate-800 line-clamp-1">{{ $rec['nama_barang'] }}</h3>
                            <p class="text-[11px] text-slate-400 mt-1 leading-relaxed">{{ $rec['alasan_urgensi'] }}</p>
                        </div>

                        <div class="pt-2 border-t border-slate-100 flex items-center justify-between gap-2">
                            <span class="font-mono text-[10px] font-black text-slate-600 bg-slate-100 px-2 py-1 rounded-lg">
                                {{ $rec['kode_voucher'] }}
                            </span>
                            <button type="button" 
                                onclick="applyPromoRecommendation({{ (string) json_encode($rec) }})"
                                class="px-3 py-1.5 bg-sky-50 hover:bg-sky-500 text-sky-600 hover:text-white rounded-xl text-[10px] font-black transition active:scale-95 flex items-center gap-1.5">
                                <i class="fa-solid fa-wand-magic-sparkles text-[9px]"></i>
                                <span>Terapkan</span>
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
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
                        <input type="text" name="kode_voucher" id="input_kode_voucher" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white text-xs font-black text-slate-800 uppercase focus:outline-none focus:border-sky-500 shadow-xs" placeholder="RENTALHEMAT">
                    </div>

                    <!-- Pilihan Visibilitas Voucher: Publik vs Rahasia -->
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1.5 flex items-center justify-between">
                            <span>Visibilitas Voucher</span>
                            <span class="text-[10px] text-slate-400 font-medium">Pilih Tipe Tampil</span>
                        </label>
                        <div class="grid grid-cols-2 gap-2">
                            <label id="label_visibilitas_publik" class="relative flex flex-col p-2.5 rounded-xl border-2 cursor-pointer transition select-none bg-sky-50/50 border-sky-500 text-sky-900">
                                <input type="radio" name="visibilitas" value="publik" checked class="sr-only" onchange="updateVisibilitasUI()">
                                <div class="flex items-center gap-1.5 text-xs font-black">
                                    <i class="fa-solid fa-globe text-sky-600 text-xs"></i>
                                    <span>Publik</span>
                                </div>
                                <p class="text-[10px] text-slate-500 mt-1 leading-snug">
                                    Tampil di profil toko & etalase. Bebas diklaim siapa saja.
                                </p>
                            </label>

                            <label id="label_visibilitas_rahasia" class="relative flex flex-col p-2.5 rounded-xl border-2 cursor-pointer transition select-none bg-slate-50/60 border-slate-200 text-slate-700 hover:border-slate-300">
                                <input type="radio" name="visibilitas" value="rahasia" class="sr-only" onchange="updateVisibilitasUI()">
                                <div class="flex items-center gap-1.5 text-xs font-black">
                                    <i class="fa-solid fa-key text-purple-600 text-xs"></i>
                                    <span>Rahasia</span>
                                </div>
                                <p class="text-[10px] text-slate-500 mt-1 leading-snug">
                                    Hanya lewat kode pribadi. Klaim di checkout atau halaman promo.
                                </p>
                            </label>
                        </div>
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
                            <input type="number" name="nilai_diskon" id="input_nilai_diskon" required min="1" class="w-full px-3 py-2.5 rounded-xl border border-slate-200 bg-white text-xs font-bold text-slate-800 focus:outline-none focus:border-sky-500 shadow-xs" placeholder="10000">
                        </div>
                    </div>

                    <div id="box_maksimal_diskon" class="hidden">
                        <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1">Maks. Potongan (Rp)</label>
                        <input type="number" name="maksimal_diskon" id="maksimal_diskon" min="1" class="w-full px-3 py-2.5 rounded-xl border border-slate-200 bg-white text-xs font-bold text-slate-800 focus:outline-none focus:border-sky-500 shadow-xs" placeholder="50000">
                    </div>

                    <div class="grid grid-cols-2 gap-2.5">
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1">Min. Belanja</label>
                            <input type="number" name="minimal_belanja" id="input_minimal_belanja" required min="0" value="0" class="w-full px-3 py-2.5 rounded-xl border border-slate-200 bg-white text-xs font-bold text-slate-800 focus:outline-none focus:border-sky-500 shadow-xs">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1">Kuota</label>
                            <input type="number" name="kuota_total" id="input_kuota_total" required min="1" value="50" class="w-full px-3 py-2.5 rounded-xl border border-slate-200 bg-white text-xs font-bold text-slate-800 focus:outline-none focus:border-sky-500 shadow-xs">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-2.5">
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1">Mulai</label>
                            <input type="date" name="tanggal_mulai" id="input_tanggal_mulai" required min="{{ date('Y-m-d') }}" value="{{ date('Y-m-d') }}" class="w-full px-2.5 py-2 rounded-xl border border-slate-200 bg-white text-[11px] font-bold text-slate-800 focus:outline-none focus:border-sky-500 shadow-xs">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1">Selesai</label>
                            <input type="date" name="tanggal_selesai" id="input_tanggal_selesai" required min="{{ date('Y-m-d') }}" class="w-full px-2.5 py-2 rounded-xl border border-slate-200 bg-white text-[11px] font-bold text-slate-800 focus:outline-none focus:border-sky-500 shadow-xs">
                        </div>
                    </div>

                    <button type="submit" class="w-full py-3 bg-gradient-to-r from-sky-400 via-sky-500 to-sky-600 hover:from-sky-500 hover:to-sky-700 text-white font-black text-xs rounded-xl shadow-xs shadow-sky-500/25 transition active:scale-95">
                        Simpan Voucher
                    </button>
                </form>
            </div>
        </div>

        <!-- KOLOM KANAN: Daftar Kupon Voucher (Sleek Card List) -->
        <div class="lg:col-span-2 space-y-3">
            <h2 class="text-xs sm:text-sm font-black text-slate-800 uppercase tracking-wider flex items-center justify-between">
                <span>Voucher Toko & Riwayat</span>
                <span class="text-[11px] text-slate-400 font-semibold lowercase">publik & rahasia</span>
            </h2>

            @forelse($vouchers as $v)
                <div class="bg-gradient-to-r from-sky-50/40 via-white to-white rounded-2xl border border-sky-200/80 shadow-xs overflow-hidden flex flex-col sm:flex-row items-stretch hover:border-sky-300 transition-all {{ !$v->is_active ? 'opacity-70 bg-slate-50/40' : '' }}">
                    <!-- Sisi Kiri / Header Kupon -->
                    <div class="w-full sm:w-32 {{ $v->is_active && $v->tanggal_selesai >= date('Y-m-d') ? ($v->visibilitas === 'rahasia' ? 'bg-gradient-to-br from-purple-600 via-indigo-600 to-purple-700' : 'bg-gradient-to-br from-sky-400 via-sky-500 to-sky-600') : 'bg-slate-400' }} text-white p-3 sm:p-4 flex sm:flex-col items-center justify-between sm:justify-center border-b sm:border-b-0 sm:border-r-2 border-dashed border-white/50 relative overflow-hidden shadow-inner">
                        <div class="absolute -right-3 -top-3 w-10 h-10 bg-white/20 rounded-full blur-xs pointer-events-none"></div>
                        <i class="fa-solid {{ $v->visibilitas === 'rahasia' ? 'fa-key' : 'fa-ticket' }} text-xl sm:text-2xl opacity-90 drop-shadow-xs"></i>
                        <span class="text-[10px] font-black uppercase tracking-wider text-center mt-0 sm:mt-1 drop-shadow-xs">
                            {{ $v->tipe_diskon == 'persen' ? $v->nilai_diskon . '%' : 'Rp ' . number_format($v->nilai_diskon / 1000, 0) . 'k' }}
                        </span>
                        <span class="text-[8.5px] font-extrabold uppercase tracking-widest text-sky-100 opacity-90 mt-0.5">
                            {{ $v->visibilitas === 'rahasia' ? 'RAHASIA' : 'PUBLIK' }}
                        </span>
                    </div>

                    <!-- Isi Kupon -->
                    <div class="flex-1 p-3.5 sm:p-4 flex flex-col justify-between gap-2.5">
                        <div class="flex items-start justify-between gap-2">
                            <div class="space-y-1">
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <span class="inline-block px-2 py-0.5 bg-gradient-to-r from-sky-50 to-sky-100 text-sky-800 rounded text-[11px] font-black uppercase tracking-widest border border-sky-200 font-mono shadow-2xs">
                                        {{ $v->kode_voucher }}
                                    </span>
                                    
                                    @if(($v->visibilitas ?? 'publik') === 'rahasia')
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[9.5px] font-extrabold bg-gradient-to-r from-purple-100 to-purple-50 text-purple-700 border border-purple-200">
                                            <i class="fa-solid fa-key text-[8.5px]"></i> Rahasia
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[9.5px] font-extrabold bg-gradient-to-r from-sky-100 to-sky-50 text-sky-700 border border-sky-200">
                                            <i class="fa-solid fa-globe text-[8.5px]"></i> Publik
                                        </span>
                                    @endif

                                    @if($v->is_active && $v->tanggal_selesai >= date('Y-m-d'))
                                        <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-gradient-to-r from-sky-100 to-sky-50 text-sky-700 border border-sky-200/60">Aktif</span>
                                    @else
                                        <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-slate-200 text-slate-600">Nonaktif</span>
                                    @endif
                                </div>

                                <h3 class="font-black text-slate-800 text-xs sm:text-sm">
                                    @if($v->tipe_diskon == 'persen')
                                        Diskon {{ $v->nilai_diskon }}% @if($v->maksimal_diskon)(Maks. Rp {{ number_format($v->maksimal_diskon, 0, ',', '.') }})@endif
                                    @else
                                        Potongan Rp {{ number_format($v->nilai_diskon, 0, ',', '.') }}
                                    @endif
                                </h3>
                                <p class="text-[11px] text-slate-400">
                                    Min. sewa: Rp {{ number_format($v->minimal_belanja, 0, ',', '.') }}
                                    @if(($v->visibilitas ?? 'publik') === 'rahasia')
                                        • <span class="text-purple-600 font-medium">Hanya via kode (CO / Promo)</span>
                                    @else
                                        • <span class="text-sky-600 font-medium">Tampil di etalase toko</span>
                                    @endif
                                </p>
                            </div>

                            <!-- Tombol Aksi: Salin Kode, Toggle Aktif, Hapus -->
                            <div class="flex items-center gap-1.5 shrink-0">
                                <!-- Salin Kode -->
                                <button type="button" onclick="copyVoucherCode('{{ $v->kode_voucher }}', this)" 
                                        class="px-2.5 py-1.5 rounded-lg bg-sky-50 text-sky-700 hover:bg-sky-100 text-[10px] font-black transition flex items-center gap-1" title="Salin kode untuk dibagikan">
                                    <i class="fa-regular fa-copy text-[10px]"></i>
                                    <span class="hidden sm:inline">Salin</span>
                                </button>

                                <!-- Toggle Aktif/Nonaktif -->
                                <form action="{{ route('vendor.voucher.toggle', $v->id) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" 
                                            class="px-2.5 py-1.5 rounded-lg {{ $v->is_active ? 'bg-amber-50 text-amber-700 hover:bg-amber-100' : 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100' }} text-[10px] font-black transition" 
                                            title="{{ $v->is_active ? 'Klik untuk Nonaktifkan' : 'Klik untuk Aktifkan' }}">
                                        {{ $v->is_active ? 'Matikan' : 'Aktifkan' }}
                                    </button>
                                </form>

                                <!-- Hapus -->
                                <form action="{{ route('vendor.voucher.destroy', $v->id) }}" method="POST" onsubmit="return confirm('Hapus voucher promo ini permanen?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="w-7 h-7 rounded-lg bg-rose-50 text-rose-500 hover:bg-rose-100 flex items-center justify-center transition active:scale-90" title="Hapus permanen">
                                        <i class="fa-solid fa-trash text-xs"></i>
                                    </button>
                                </form>
                            </div>
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
                    <p class="text-xs font-medium">Belum ada voucher diskon dibuat.</p>
                </div>
            @endforelse
        </div>

    </div>

</div>

<!-- TOAST SALIN KODE -->
<div id="vendorToast" class="fixed top-5 left-1/2 -translate-x-1/2 z-50 bg-slate-900/95 text-white px-4 py-2 rounded-full text-xs font-bold shadow-xl opacity-0 pointer-events-none transition-all duration-300 flex items-center gap-2">
    <i class="fa-solid fa-circle-check text-emerald-400 text-sm"></i>
    <span id="vendorToastMsg">Kode voucher berhasil disalin!</span>
</div>

<script>
    function updateVisibilitasUI() {
        const publikRadio = document.querySelector('input[name="visibilitas"][value="publik"]');
        const labelPublik = document.getElementById('label_visibilitas_publik');
        const labelRahasia = document.getElementById('label_visibilitas_rahasia');

        if (publikRadio && publikRadio.checked) {
            labelPublik.className = 'relative flex flex-col p-2.5 rounded-xl border-2 cursor-pointer transition select-none bg-sky-50/50 border-sky-500 text-sky-900';
            labelRahasia.className = 'relative flex flex-col p-2.5 rounded-xl border-2 cursor-pointer transition select-none bg-slate-50/60 border-slate-200 text-slate-700 hover:border-slate-300';
        } else {
            labelPublik.className = 'relative flex flex-col p-2.5 rounded-xl border-2 cursor-pointer transition select-none bg-slate-50/60 border-slate-200 text-slate-700 hover:border-slate-300';
            labelRahasia.className = 'relative flex flex-col p-2.5 rounded-xl border-2 cursor-pointer transition select-none bg-purple-50/50 border-purple-500 text-purple-900';
        }
    }

    function toggleMaksimalDiskon() {
        const tipe = document.getElementById('tipe_diskon').value;
        const box = document.getElementById('box_maksimal_diskon');
        const input = document.getElementById('maksimal_diskon');
        const nilaiInput = document.getElementById('input_nilai_diskon');

        if (tipe === 'persen') {
            box.classList.remove('hidden');
            input.required = true;
            nilaiInput.max = '100';
            nilaiInput.placeholder = 'Contoh: 15 (%)';
        } else {
            box.classList.add('hidden');
            input.required = false;
            input.value = '';
            nilaiInput.removeAttribute('max');
            nilaiInput.placeholder = '10000';
        }
    }

    function applyPromoRecommendation(rec) {
        document.getElementById('input_kode_voucher').value = rec.kode_voucher;
        document.getElementById('tipe_diskon').value = rec.tipe_diskon;
        toggleMaksimalDiskon();
        document.getElementById('input_nilai_diskon').value = rec.nilai_diskon;
        if (rec.maksimal_diskon) {
            document.getElementById('maksimal_diskon').value = rec.maksimal_diskon;
        }
        document.getElementById('input_minimal_belanja').value = rec.minimal_belanja;
        document.getElementById('input_kuota_total').value = 30;
        
        const today = new Date();
        const nextWeek = new Date();
        nextWeek.setDate(today.getDate() + (rec.durasi_hari || 7));
        
        document.getElementById('input_tanggal_mulai').value = today.toISOString().split('T')[0];
        document.getElementById('input_tanggal_selesai').value = nextWeek.toISOString().split('T')[0];
        
        // Default rekomendasi AI ke publik
        const publikRadio = document.querySelector('input[name="visibilitas"][value="publik"]');
        if (publikRadio) {
            publikRadio.checked = true;
            updateVisibilitasUI();
        }

        document.getElementById('input_kode_voucher').focus();
        document.getElementById('input_kode_voucher').scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    function copyVoucherCode(kode, btn) {
        navigator.clipboard.writeText(kode).then(() => {
            const toast = document.getElementById('vendorToast');
            document.getElementById('vendorToastMsg').innerText = `Kode "${kode}" berhasil disalin!`;
            toast.classList.remove('opacity-0', 'pointer-events-none');
            toast.classList.add('opacity-100');

            const oriText = btn.innerHTML;
            btn.innerHTML = '<i class="fa-solid fa-check text-[10px]"></i> <span class="hidden sm:inline">Disalin</span>';

            setTimeout(() => {
                toast.classList.remove('opacity-100');
                toast.classList.add('opacity-0', 'pointer-events-none');
                btn.innerHTML = oriText;
            }, 2000);
        }).catch(() => {
            prompt("Salin kode voucher ini:", kode);
        });
    }

    // Inisialisasi saat load
    document.addEventListener('DOMContentLoaded', function() {
        toggleMaksimalDiskon();
        updateVisibilitasUI();
    });
</script>
@endsection
