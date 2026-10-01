@extends('layouts.vendor')

@section('title', 'Detail Pesanan — Vendor Rentify')

@section('content')
<div class="px-3.5 sm:px-6 lg:px-8 py-4 sm:py-6 max-w-5xl mx-auto space-y-4">
    
    <div class="flex items-center justify-between">
        <a href="{{ route('vendor.pesanan.index') }}" class="inline-flex items-center gap-1.5 px-3 py-2 bg-white border border-slate-200/80 rounded-xl text-xs font-bold text-slate-700 hover:text-sky-600 transition shadow-xs">
            <i class="fa-solid fa-arrow-left text-xs"></i>
            <span>Daftar Pesanan</span>
        </a>

        @php
            $st = strtolower($pesanan->status);
            $badgeClass = 'bg-slate-100 text-slate-600';
            if (in_array($st, ['selesai'])) $badgeClass = 'bg-emerald-50 text-emerald-700';
            elseif (in_array($st, ['menunggu konfirmasi', 'pending'])) $badgeClass = 'bg-amber-50 text-amber-700';
            elseif (in_array($st, ['disetujui', 'sedang disewa', 'berjalan'])) $badgeClass = 'bg-sky-50 text-sky-700';
            elseif (in_array($st, ['dibatalkan', 'ditolak'])) $badgeClass = 'bg-rose-50 text-rose-700';
        @endphp
        <span class="inline-block px-3 py-1 rounded-lg text-xs font-black {{ $badgeClass }}">
            {{ ucfirst($pesanan->status) }}
        </span>
    </div>

    @if(session('success'))
        <div class="px-4 py-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl flex items-center gap-2.5 text-xs font-bold shadow-xs">
            <i class="fa-solid fa-circle-check text-emerald-600 text-sm"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6 items-start">
        
        <!-- KOLOM KIRI (2 SPAN): INFO CUSTOMER & BARANG -->
        <div class="lg:col-span-2 space-y-4">
            
            <!-- Data Customer & Pengiriman -->
            <div class="bg-white p-4 sm:p-6 rounded-2xl sm:rounded-3xl border border-slate-200/80 shadow-xs space-y-3.5">
                <div class="border-b border-slate-100 pb-3 flex items-center justify-between">
                    <div>
                        <h2 class="text-xs sm:text-sm font-black text-slate-800 uppercase tracking-wider">Informasi Penyewa</h2>
                        <span class="text-[11px] text-slate-400 font-medium">Order ID: #{{ $pesanan->id }}</span>
                    </div>
                    @php
                        $hp = $pesanan->customer_whatsapp ?? ($pesanan->customer->no_hp ?? '');
                        $cleanPhone = preg_replace('/[^0-9]/', '', $hp);
                        if (str_starts_with($cleanPhone, '0')) $cleanPhone = '62' . substr($cleanPhone, 1);
                    @endphp
                    @if(!empty($cleanPhone))
                        <a href="https://wa.me/{{ $cleanPhone }}" target="_blank" class="px-2.5 py-1 bg-emerald-500 hover:bg-emerald-600 text-white rounded-lg text-xs font-bold flex items-center gap-1.5 transition">
                            <i class="fa-brands fa-whatsapp text-sm"></i> WhatsApp
                        </a>
                    @endif
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                        <span class="text-[10px] text-slate-400 font-bold uppercase block mb-0.5">Nama Customer</span>
                        <strong class="text-slate-800 text-sm font-black">{{ $pesanan->customer_name ?? $pesanan->customer->name ?? 'Pelanggan' }}</strong>
                    </div>

                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                        <span class="text-[10px] text-slate-400 font-bold uppercase block mb-0.5">Metode Bayar</span>
                        <span class="inline-block px-2 py-0.5 bg-slate-200 text-slate-800 text-xs font-extrabold rounded">
                            {{ $pesanan->payment_method ?? 'COD' }}
                        </span>
                    </div>

                    <div class="p-3 rounded-xl bg-sky-50/60 border border-sky-100 sm:col-span-2">
                        <span class="text-[10px] text-sky-700 font-bold uppercase block mb-1">Periode Sewa</span>
                        <div class="flex items-center justify-between text-xs font-bold text-sky-950">
                            <span>Mulai: {{ \Carbon\Carbon::parse($pesanan->start_rent ?? $pesanan->tanggal_mulai)->format('d M Y, H:i') }} WIB</span>
                            <span>&rarr;</span>
                            <span>Selesai: {{ \Carbon\Carbon::parse($pesanan->end_rent ?? $pesanan->tanggal_selesai)->format('d M Y, H:i') }} WIB</span>
                        </div>
                    </div>

                    @if(!empty($pesanan->shipping_address ?? $pesanan->alamat_pengiriman))
                        <div class="p-3 rounded-xl bg-slate-50 border border-slate-100 sm:col-span-2">
                            <span class="text-[10px] text-slate-400 font-bold uppercase block mb-0.5">Alamat Pengiriman / Gudang</span>
                            <p class="text-xs text-slate-700 font-medium leading-relaxed">{{ $pesanan->shipping_address ?? $pesanan->alamat_pengiriman }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Daftar Barang Disewa -->
            <div class="bg-white p-4 sm:p-6 rounded-2xl sm:rounded-3xl border border-slate-200/80 shadow-xs space-y-3">
                <h3 class="text-xs sm:text-sm font-black text-slate-800 uppercase tracking-wider border-b border-slate-100 pb-2.5">
                    Item Sewa
                </h3>

                <div class="space-y-2.5">
                    @foreach($pesanan->details as $detail)
                        @if($detail->barang && $detail->barang->vendor_id == Auth::id())
                            @php
                                $cImg = $detail->barang->cover_photo ?? null;
                                if ($cImg && !str_starts_with($cImg, 'http')) {
                                    $cImg = asset(str_replace('public/', '', $cImg));
                                }
                            @endphp
                            <div class="p-3 rounded-xl border border-slate-100 flex items-center justify-between gap-3">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="w-12 h-12 rounded-lg bg-slate-100 overflow-hidden shrink-0 border border-slate-200">
                                        @if($cImg)
                                            <img src="{{ $cImg }}" class="w-full h-full object-cover">
                                        @else
                                            <div class="w-full h-full flex items-center justify-center text-slate-300"><i class="fa-solid fa-image text-xs"></i></div>
                                        @endif
                                    </div>
                                    <div class="min-w-0">
                                        <h4 class="font-black text-xs text-slate-800 truncate">{{ $detail->barang->nama }}</h4>
                                        <p class="text-[11px] text-slate-400">Tarif: Rp {{ number_format($detail->price ?? $detail->barang->harga_sewa_harian ?? 0, 0, ',', '.') }}/hari</p>
                                    </div>
                                </div>
                                <span class="text-xs font-black text-slate-800 shrink-0">
                                    {{ $detail->quantity ?? 1 }} unit
                                </span>
                            </div>
                        @endif
                    @endforeach
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-500">Total Biaya Pesanan</span>
                    <span class="text-lg font-black text-slate-800">
                        Rp {{ number_format($pesanan->total_biaya ?? $pesanan->total_price ?? 0, 0, ',', '.') }}
                    </span>
                </div>
            </div>

        </div>

        <!-- KOLOM KANAN (1 SPAN): UPDATE STATUS -->
        <div class="bg-white p-4 sm:p-5 rounded-2xl sm:rounded-3xl border border-slate-200/80 shadow-xs space-y-4">
            <h3 class="text-xs sm:text-sm font-black text-slate-800 uppercase tracking-wider border-b border-slate-100 pb-2.5">
                Kelola Status Pesanan
            </h3>

            <div class="p-3 rounded-xl bg-slate-50 border border-slate-100 text-center">
                <span class="text-[10px] text-slate-400 font-bold uppercase block mb-1">Status Sekarang</span>
                <span class="text-sm font-black text-slate-800">{{ $pesanan->status }}</span>
            </div>

            <form action="{{ route('vendor.pesanan.status.update', $pesanan->id) }}" method="POST" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Ubah Status Menjadi:</label>
                    <select name="status" class="w-full px-3 py-2.5 rounded-xl border border-slate-200 bg-white text-xs font-bold text-slate-800 focus:outline-none focus:border-sky-500 shadow-xs">
                        <option value="Menunggu Konfirmasi" {{ $pesanan->status == 'Menunggu Konfirmasi' ? 'selected' : '' }}>Menunggu Konfirmasi</option>
                        <option value="Disetujui" {{ $pesanan->status == 'Disetujui' ? 'selected' : '' }}>Disetujui</option>
                        <option value="Sedang Disewa" {{ $pesanan->status == 'Sedang Disewa' ? 'selected' : '' }}>Sedang Disewa</option>
                        <option value="Selesai" {{ $pesanan->status == 'Selesai' ? 'selected' : '' }}>Selesai</option>
                        <option value="Dibatalkan" {{ $pesanan->status == 'Dibatalkan' ? 'selected' : '' }}>Dibatalkan</option>
                    </select>
                </div>

                <button type="submit" class="w-full py-2.5 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-black shadow-xs transition active:scale-95">
                    Perbarui Status
                </button>
            </form>
        </div>

    </div>

</div>
@endsection