@extends('layouts.vendor')

@section('title', 'Voucher Toko - Vendor Rentify')

@section('content')
<div class="p-4 md:p-8">
    <header class="mb-8">
        <h1 class="text-3xl font-black text-slate-800 tracking-tight">Voucher Toko <span class="text-2xl">🎟️</span></h1>
        <p class="text-slate-500 mt-2 font-medium">Buat promo diskon eksklusif untuk menarik lebih banyak pelanggan.</p>
    </header>

    @if(session('success'))
        <div class="mb-8 px-5 py-4 bg-emerald-50 border border-emerald-100 text-emerald-700 rounded-2xl flex items-center gap-3 shadow-sm">
            <div class="w-8 h-8 rounded-full bg-emerald-500 text-white flex items-center justify-center flex-shrink-0"><i class="fa-solid fa-check"></i></div>
            <p class="font-semibold">{{ session('success') }}</p>
        </div>
    @endif
    
    @if ($errors->any())
        <div class="mb-8 px-5 py-4 bg-rose-50 border border-rose-100 text-rose-700 rounded-2xl shadow-sm">
            <ul class="list-disc list-inside text-sm font-semibold">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
        
        <!-- KOLOM KIRI: Form Buat Voucher -->
        <div class="lg:col-span-1">
            <div class="glass-card rounded-3xl p-6 shadow-sm border border-slate-100 bg-white sticky top-28">
                <h3 class="text-lg font-extrabold text-slate-800 mb-6 border-b border-slate-100 pb-3"><i class="fa-solid fa-ticket text-brand-main mr-2"></i> Buat Voucher Baru</h3>
                
                <form action="{{ route('vendor.voucher.store') }}" method="POST" class="space-y-4">
                    @csrf
                    
                    <div>
                        <label class="block text-xs font-extrabold text-slate-500 uppercase tracking-widest mb-1">Kode Voucher</label>
                        <input type="text" name="kode_voucher" required class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-black text-slate-800 focus:ring-2 focus:ring-brand-main/20 outline-none uppercase" placeholder="Contoh: PROMOJONI">
                        <p class="text-[10px] text-slate-400 mt-1">Tanpa spasi, maksimal 10 karakter disarankan.</p>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-extrabold text-slate-500 uppercase tracking-widest mb-1">Tipe Diskon</label>
                            <select name="tipe_diskon" id="tipe_diskon" onchange="toggleMaksimalDiskon()" required class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold focus:ring-2 focus:ring-brand-main/20 outline-none">
                                <option value="nominal">Nominal (Rp)</option>
                                <option value="persen">Persentase (%)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-extrabold text-slate-500 uppercase tracking-widest mb-1">Nilai Diskon</label>
                            <input type="number" name="nilai_diskon" required min="1" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold focus:ring-2 focus:ring-brand-main/20 outline-none" placeholder="10000">
                        </div>
                    </div>

                    <div id="box_maksimal_diskon" class="hidden">
                        <label class="block text-xs font-extrabold text-rose-500 uppercase tracking-widest mb-1">Maksimal Potongan (Rp)</label>
                        <input type="number" name="maksimal_diskon" id="maksimal_diskon" min="1" class="w-full px-4 py-3 bg-rose-50 border border-rose-200 rounded-xl text-sm font-bold focus:ring-2 focus:ring-rose-500/20 outline-none text-rose-700" placeholder="Contoh: 20000">
                        <p class="text-[10px] text-rose-400 mt-1">* Wajib diisi agar Anda tidak rugi jika transaksi sangat besar.</p>
                    </div>

                    <div>
                        <label class="block text-xs font-extrabold text-slate-500 uppercase tracking-widest mb-1">Min. Belanja (Rp)</label>
                        <input type="number" name="minimal_belanja" required min="0" value="0" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold focus:ring-2 focus:ring-brand-main/20 outline-none" placeholder="0 untuk tanpa batas">
                    </div>

                    <div>
                        <label class="block text-xs font-extrabold text-slate-500 uppercase tracking-widest mb-1">Kuota Total</label>
                        <input type="number" name="kuota_total" required min="1" value="50" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold focus:ring-2 focus:ring-brand-main/20 outline-none">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-extrabold text-slate-500 uppercase tracking-widest mb-1">Tgl Mulai</label>
                            <input type="date" name="tanggal_mulai" required min="{{ date('Y-m-d') }}" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold focus:ring-2 focus:ring-brand-main/20 outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-extrabold text-slate-500 uppercase tracking-widest mb-1">Tgl Berakhir</label>
                            <input type="date" name="tanggal_selesai" required min="{{ date('Y-m-d') }}" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold focus:ring-2 focus:ring-brand-main/20 outline-none">
                        </div>
                    </div>

                    <button type="submit" class="w-full bg-brand-main hover:bg-brand-deep text-white font-bold py-3.5 rounded-xl transition-all shadow-lg shadow-brand-main/30 mt-4">
                        Buat Voucher Promo <i class="fa-solid fa-arrow-right ml-1"></i>
                    </button>
                </form>
            </div>
        </div>

        <!-- KOLOM KANAN: Daftar Voucher -->
        <div class="lg:col-span-2 space-y-4">
            @forelse($vouchers as $v)
                <div class="flex flex-col sm:flex-row bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden group">
                    <!-- Sisi Kiri Kupon (Warna warni) -->
                    <div class="w-full sm:w-32 {{ $v->is_active && $v->tanggal_selesai >= date('Y-m-d') ? 'gradient-bg' : 'bg-slate-300' }} text-white p-4 flex flex-col justify-center items-center relative border-r-2 border-dashed border-white/50">
                        <i class="fa-solid fa-ticket text-3xl mb-1 opacity-80"></i>
                        <span class="text-[10px] font-bold uppercase tracking-widest text-center mt-1">
                            {{ $v->tipe_diskon == 'persen' ? 'Diskon %' : 'Potongan' }}
                        </span>
                    </div>
                    
                    <!-- Sisi Kanan Kupon (Detail) -->
                    <div class="flex-1 p-5 relative">
                        <div class="flex justify-between items-start mb-2">
                            <div>
                                <span class="inline-block px-2 py-1 bg-blue-50 text-brand-main rounded text-[10px] font-black uppercase tracking-widest mb-1 border border-blue-100">
                                    {{ $v->kode_voucher }}
                                </span>
                                <h4 class="font-black text-slate-800 text-lg">
                                    @if($v->tipe_diskon == 'persen')
                                        Diskon {{ $v->nilai_diskon }}% (Maks. Rp{{ number_format($v->maksimal_diskon, 0, ',', '.') }})
                                    @else
                                        Potongan Rp{{ number_format($v->nilai_diskon, 0, ',', '.') }}
                                    @endif
                                </h4>
                            </div>
                            
                            <!-- Tombol Hapus -->
                            <form action="{{ route('vendor.voucher.destroy', $v->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus voucher ini?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="w-8 h-8 rounded-full bg-rose-50 text-rose-500 flex items-center justify-center hover:bg-rose-500 hover:text-white transition-colors">
                                    <i class="fa-solid fa-trash-can text-xs"></i>
                                </button>
                            </form>
                        </div>
                        
                        <p class="text-xs font-medium text-slate-500 mb-4">Min. Belanja: <span class="font-bold text-slate-700">Rp{{ number_format($v->minimal_belanja, 0, ',', '.') }}</span></p>
                        
                        <div class="flex flex-wrap items-center gap-4 pt-4 border-t border-slate-100">
                            <div class="text-[11px] text-slate-500 font-bold">
                                <i class="fa-regular fa-clock text-brand-main mr-1"></i> 
                                Berlaku: {{ \Carbon\Carbon::parse($v->tanggal_mulai)->format('d M') }} - {{ \Carbon\Carbon::parse($v->tanggal_selesai)->format('d M Y') }}
                            </div>
                            <div class="text-[11px] text-slate-500 font-bold">
                                <i class="fa-solid fa-users text-amber-500 mr-1"></i>
                                Kuota: <span class="text-amber-600">{{ $v->kuota_terpakai }}</span> / {{ $v->kuota_total }} Dipakai
                            </div>
                            
                            <!-- Status Badge -->
                            <div class="ml-auto">
                                @if($v->tanggal_selesai < date('Y-m-d'))
                                    <span class="px-2 py-1 bg-rose-50 text-rose-600 rounded text-[10px] font-bold">Kedaluwarsa</span>
                                @elseif($v->kuota_terpakai >= $v->kuota_total)
                                    <span class="px-2 py-1 bg-slate-100 text-slate-500 rounded text-[10px] font-bold">Kuota Habis</span>
                                @else
                                    <span class="px-2 py-1 bg-emerald-50 text-emerald-600 rounded text-[10px] font-bold">Aktif</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-16 bg-white border border-slate-100 rounded-3xl">
                    <div class="w-20 h-20 mx-auto bg-slate-50 rounded-full flex items-center justify-center text-slate-300 text-3xl mb-4">
                        <i class="fa-solid fa-ticket-simple"></i>
                    </div>
                    <h3 class="font-bold text-slate-700">Belum ada Voucher</h3>
                    <p class="text-sm text-slate-500 mt-1">Buat voucher pertamamu di form sebelah kiri.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>

<!-- Script untuk memunculkan kolom Maksimal Diskon jika memilih Persen -->
<script>
    function toggleMaksimalDiskon() {
        var tipe = document.getElementById('tipe_diskon').value;
        var boxMaks = document.getElementById('box_maksimal_diskon');
        var inputMaks = document.getElementById('maksimal_diskon');
        
        if(tipe === 'persen') {
            boxMaks.classList.remove('hidden');
            inputMaks.setAttribute('required', 'required');
        } else {
            boxMaks.classList.add('hidden');
            inputMaks.removeAttribute('required');
            inputMaks.value = '';
        }
    }
</script>
@endsection