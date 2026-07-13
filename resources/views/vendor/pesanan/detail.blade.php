@extends('layouts.vendor')

@section('title', 'Detail Pesanan - Vendor Rentify')

@section('content')
<div class="p-4 md:p-8">
    <div class="mb-6">
        <a href="{{ route('vendor.pesanan.index') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-slate-200 text-slate-600 font-bold rounded-xl hover:text-brand-main hover:border-brand-main transition-all shadow-sm text-sm">
            <i class="fa-solid fa-arrow-left"></i> Kembali ke Riwayat Pesanan
        </a>
    </div>

    @if(session('success'))
        <div class="mb-8 px-5 py-4 bg-emerald-50 border border-emerald-100 text-emerald-700 rounded-2xl flex items-center gap-3 shadow-sm">
            <div class="w-8 h-8 rounded-full bg-emerald-500 text-white flex items-center justify-center flex-shrink-0"><i class="fa-solid fa-check"></i></div>
            <p class="font-semibold">{{ session('success') }}</p>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <div class="lg:col-span-2 space-y-6">
            
            <div class="glass-card rounded-3xl p-6 md:p-8 shadow-sm border border-slate-100 bg-white">
                <div class="flex items-center gap-3 mb-6 border-b border-slate-100 pb-4">
                    <div class="w-10 h-10 rounded-xl bg-blue-50 text-brand-main flex items-center justify-center text-lg"><i class="fa-solid fa-user-tag"></i></div>
                    <div>
                        <h2 class="text-xl font-extrabold text-slate-800">Informasi Customer</h2>
                        <p class="text-xs font-bold text-slate-400 mt-1 uppercase tracking-widest">Order ID: #{{ $pesanan->id }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm">
                    <div class="bg-slate-50 p-4 rounded-2xl border border-slate-100">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Nama Penyewa</p>
                        <p class="font-black text-slate-800 text-lg">{{ $pesanan->customer_name }}</p>
                    </div>
                    <div class="bg-emerald-50 p-4 rounded-2xl border border-emerald-100">
                        <p class="text-[10px] font-bold text-emerald-600/70 uppercase tracking-widest mb-1">WhatsApp Customer</p>
                        <p class="font-black text-emerald-700 text-lg flex items-center gap-2"><i class="fa-brands fa-whatsapp"></i> {{ $pesanan->customer_whatsapp }}</p>
                    </div>
                    <div class="bg-slate-50 p-4 rounded-2xl border border-slate-100">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Sistem Pengiriman</p>
                        <p class="font-black text-slate-800 capitalize"><i class="fa-solid fa-truck-fast text-brand-sky mr-2"></i>{{ $pesanan->shipping_method }}</p>
                    </div>
                    <div class="bg-slate-50 p-4 rounded-2xl border border-slate-100">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Metode Pembayaran</p>
                        <span class="inline-flex px-3 py-1 rounded-lg text-xs font-bold uppercase shadow-sm {{ strtoupper($pesanan->payment_method) == 'QRIS' ? 'bg-brand-main text-white' : 'bg-orange-500 text-white' }}">
                            {{ strtoupper($pesanan->payment_method) }}
                        </span>
                    </div>
                    <div class="md:col-span-2 bg-slate-50 p-4 rounded-2xl border border-slate-100">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Alamat Pengiriman</p>
                        <p class="font-semibold text-slate-700 leading-relaxed">{{ $pesanan->shipping_address }}</p>
                    </div>
                </div>
            </div>

            <div class="glass-card rounded-3xl shadow-sm border border-slate-100 bg-white overflow-hidden">
                <div class="p-6 md:p-8 border-b border-slate-100 bg-slate-50/50">
                    <h2 class="text-xl font-extrabold text-slate-800">Daftar Barang Disewa</h2>
                </div>
                <div class="p-6 md:p-8">
                    <div class="space-y-4">
                        @foreach($pesanan->details as $detail)
                            @if($detail->barang && $detail->barang->vendor_id == Auth::id())
                            <div class="flex items-center gap-4 p-4 rounded-2xl border border-slate-100 hover:border-brand-main/30 hover:bg-blue-50/20 transition-colors">
                                @php $cover = $detail->barang->cover_photo ? asset(str_replace('public/', '', $detail->barang->cover_photo)) : 'https://placehold.co/100'; @endphp
                                <img src="{{ $cover }}" class="w-16 h-16 rounded-xl object-cover shadow-sm">
                                <div class="flex-1">
                                    <p class="font-black text-slate-800 text-base">{{ $detail->barang->nama }}</p>
                                    <p class="text-xs font-bold text-slate-500 mt-1">Durasi Sewa: <span class="text-brand-main">{{ $pesanan->duration_days }} Hari</span></p>
                                </div>
                                <div class="text-right">
                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Subtotal</p>
                                    <p class="font-black text-brand-deep text-lg">Rp {{ number_format(($detail->price ?? 0) * $pesanan->duration_days, 0, ',', '.') }}</p>
                                </div>
                            </div>
                            @endif
                        @endforeach
                    </div>
                </div>
                
                <div class="p-6 md:p-8 bg-gradient-to-br from-slate-800 to-navydark text-white text-right">
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">Total Pendapatan Pesanan Ini</p>
                    <p class="text-4xl font-black text-brand-sky">Rp {{ number_format($pesanan->total_price, 0, ',', '.') }}</p>
                </div>
            </div>
        </div>

        <div class="lg:col-span-1">
            <div class="glass-card rounded-3xl p-6 md:p-8 shadow-sm border border-slate-100 bg-white sticky top-28">
                
                <div class="text-center mb-8">
                    <div class="w-16 h-16 rounded-full bg-blue-50 text-brand-main flex items-center justify-center text-2xl mx-auto mb-4">
                        <i class="fa-solid fa-arrows-rotate"></i>
                    </div>
                    <h2 class="text-xl font-extrabold text-slate-800">Status Penyewaan</h2>
                    <p class="text-xs font-bold text-slate-500 mt-1">Perbarui status transaksi ini</p>
                </div>
                
                <div class="mb-8 p-5 rounded-2xl bg-slate-50 border border-slate-200 text-center">
                    <p class="text-[10px] text-slate-400 uppercase font-extrabold tracking-widest mb-2">Status Saat Ini</p>
                    <p class="text-xl font-black text-slate-800">{{ $pesanan->status }}</p>
                </div>

                <form action="{{ route('vendor.pesanan.status.update', $pesanan->id) }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-extrabold text-slate-500 uppercase tracking-widest mb-2">Tindakan Selanjutnya</label>
                        <select name="status" class="w-full px-4 py-4 bg-white border border-slate-200 rounded-2xl text-sm font-bold text-slate-700 focus:ring-2 focus:ring-brand-main/20 focus:border-brand-main outline-none shadow-sm transition-all appearance-none">
                            <option value="Menunggu Konfirmasi" {{ $pesanan->status == 'Menunggu Konfirmasi' ? 'selected' : '' }}>Menunggu Konfirmasi</option>
                            <option value="Disetujui" {{ $pesanan->status == 'Disetujui' ? 'selected' : '' }}>Disetujui (Siap)</option>
                            <option value="Sedang Disewa" {{ $pesanan->status == 'Sedang Disewa' ? 'selected' : '' }}>Sedang Disewa (Berjalan)</option>
                            <option value="Selesai" {{ $pesanan->status == 'Selesai' ? 'selected' : '' }}>✅ Selesai (Barang Kembali)</option>
                            <option value="Dibatalkan" {{ $pesanan->status == 'Dibatalkan' ? 'selected' : '' }}>❌ Dibatalkan</option>
                        </select>
                    </div>
                    <button type="submit" class="w-full bg-brand-main hover:bg-brand-deep text-white font-bold py-4 rounded-2xl transition-all shadow-lg shadow-brand-main/30 group">
                        Simpan Perubahan <i class="fa-solid fa-check ml-2 group-hover:scale-125 transition-transform"></i>
                    </button>
                </form>

                <div class="mt-6 p-4 rounded-xl bg-blue-50/50 border border-blue-100 flex items-start gap-3">
                    <i class="fa-solid fa-circle-info text-brand-main mt-0.5"></i>
                    <p class="text-[10px] text-slate-600 font-medium leading-relaxed">
                        Jika status diubah menjadi <b class="text-brand-main">"Selesai"</b>, sistem otomatis akan menghitung *fee* platform dan memperbarui Dompet Saldo Anda!
                    </p>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection