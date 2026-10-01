@extends('layouts.vendor')

@section('title', 'Dashboard Vendor — Rentify')

@section('content')
<div class="px-4 sm:px-6 lg:px-8 py-6 max-w-7xl mx-auto space-y-6">

    <!-- ============================================================ -->
    <!-- 1. HEADER & STORE STATUS BAR -->
    <!-- ============================================================ -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-5 sm:p-6 rounded-3xl border border-slate-200 shadow-sm">
        <div class="space-y-1">
            <div class="flex items-center gap-2.5 flex-wrap">
                <h1 class="text-xl sm:text-2xl font-black text-slate-800 tracking-tight">
                    Halo, {{ explode(' ', trim($user->vendor_name ?? $user->name))[0] }}! 👋
                </h1>
                @if(($user->vendor_status ?? 'pending') === 'approved')
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs font-black rounded-full shadow-xs">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        Toko Aktif & Terverifikasi
                    </span>
                @else
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-amber-50 border border-amber-200 text-amber-700 text-xs font-black rounded-full shadow-xs">
                        <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                        Menunggu Verifikasi
                    </span>
                @endif
            </div>
            <p class="text-xs sm:text-sm text-slate-500 font-medium">
                Pusat kendali armada, pesanan, dan keuangan toko <strong class="text-slate-700">{{ $user->vendor_name ?? $user->name }}</strong>.
            </p>
        </div>

        <div class="flex items-center gap-2.5">
            <a href="{{ route('vendor.barang.create') }}" class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-gradient-to-r from-sky-500 to-blue-600 hover:from-sky-600 hover:to-blue-700 text-white rounded-2xl text-xs sm:text-sm font-extrabold shadow-md shadow-sky-500/20 active:scale-95 transition-all">
                <i class="fa-solid fa-cloud-arrow-up text-sm"></i>
                <span>Upload Barang</span>
            </a>
            <a href="{{ route('role.switch', 'customer') }}" class="inline-flex items-center justify-center gap-1.5 px-3.5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-2xl text-xs font-bold transition-all" title="Mode Customer">
                <i class="fa-solid fa-cart-shopping"></i>
                <span class="hidden md:inline">Belanja</span>
            </a>
        </div>
    </div>

    <!-- Alert Toko Pending (jika belum disetujui) -->
    @if(($user->vendor_status ?? 'pending') === 'pending')
        <div class="p-4 sm:p-5 bg-gradient-to-r from-amber-50 to-orange-50 border border-amber-200 rounded-3xl text-amber-900 flex items-start gap-3.5 shadow-sm">
            <div class="w-9 h-9 rounded-2xl bg-amber-500 text-white flex items-center justify-center text-base shrink-0 mt-0.5">
                <i class="fa-solid fa-hourglass-half"></i>
            </div>
            <div class="flex-1 min-w-0">
                <h4 class="font-bold text-sm text-amber-950">Status Kemitraan: Sedang Dikurasi</h4>
                <p class="text-xs text-amber-800/90 mt-0.5 leading-relaxed">
                    Toko Anda sedang ditinjau oleh Admin Rentify. Anda tetap dapat mengunggah dan mengatur produk sekarang agar langsung siap tayang saat toko disetujui!
                </p>
            </div>
        </div>
    @endif

    <!-- ============================================================ -->
    <!-- 2. ACTION CENTER: PESANAN MENUNGGU & PENGEMBALIAN HARI INI -->
    <!-- ============================================================ -->
    @if($pesananMenunggu->count() > 0 || $pengembalianHariIni->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            
            <!-- Pesanan Masuk Menunggu Konfirmasi -->
            @if($pesananMenunggu->count() > 0)
                <div class="bg-amber-50/70 border-2 border-amber-300/80 rounded-3xl p-5 shadow-sm">
                    <div class="flex items-center justify-between mb-3.5">
                        <div class="flex items-center gap-2 text-amber-900 font-extrabold text-sm">
                            <span class="w-2.5 h-2.5 rounded-full bg-amber-500 animate-ping"></span>
                            <i class="fa-solid fa-bell text-amber-600"></i>
                            <span>{{ $pesananMenunggu->count() }} Pesanan Butuh Konfirmasi</span>
                        </div>
                        <a href="{{ route('vendor.pesanan.index') }}" class="text-xs font-black text-amber-700 hover:text-amber-800 underline">Lihat Semua</a>
                    </div>
                    <div class="space-y-2.5">
                        @foreach($pesananMenunggu as $order)
                            <div class="bg-white p-3.5 rounded-2xl border border-amber-200/80 flex items-center justify-between gap-3 shadow-xs">
                                <div class="min-w-0">
                                    <p class="font-extrabold text-xs text-slate-800 truncate">
                                        #{{ $order->id }} — {{ $order->customer->name ?? 'Pelanggan' }}
                                    </p>
                                    <p class="text-[11px] text-slate-500 truncate mt-0.5">
                                        {{ $order->details->first()->barang->nama ?? 'Produk Rental' }}
                                        @if($order->details->count() > 1)
                                            <span class="text-sky-600 font-bold">+{{ $order->details->count() - 1 }} lainnya</span>
                                        @endif
                                    </p>
                                    <p class="text-[11px] font-black text-amber-700 mt-1">
                                        Rp {{ number_format($order->total_biaya ?? $order->total_price ?? 0, 0, ',', '.') }}
                                    </p>
                                </div>
                                <a href="{{ route('vendor.pesanan.index') }}" class="px-3 py-1.5 bg-amber-500 hover:bg-amber-600 text-white rounded-xl text-xs font-black shrink-0 transition">
                                    Proses
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Pengembalian Barang Hari Ini / Jatuh Tempo -->
            @if($pengembalianHariIni->count() > 0)
                <div class="bg-sky-50/70 border-2 border-sky-300/80 rounded-3xl p-5 shadow-sm">
                    <div class="flex items-center justify-between mb-3.5">
                        <div class="flex items-center gap-2 text-sky-950 font-extrabold text-sm">
                            <i class="fa-solid fa-clock-rotate-left text-sky-600"></i>
                            <span>{{ $pengembalianHariIni->count() }} Jadwal Pengembalian Hari Ini</span>
                        </div>
                        <a href="{{ route('vendor.pesanan.index') }}" class="text-xs font-black text-sky-700 hover:text-sky-800 underline">Cek Pesanan</a>
                    </div>
                    <div class="space-y-2.5">
                        @foreach($pengembalianHariIni as $ret)
                            @php
                                $hp = $ret->customer->no_hp ?? $ret->customer->phone ?? '';
                                $cleanPhone = preg_replace('/[^0-9]/', '', $hp);
                                if (str_starts_with($cleanPhone, '0')) {
                                    $cleanPhone = '62' . substr($cleanPhone, 1);
                                }
                            @endphp
                            <div class="bg-white p-3.5 rounded-2xl border border-sky-200/80 flex items-center justify-between gap-3 shadow-xs">
                                <div class="min-w-0">
                                    <p class="font-extrabold text-xs text-slate-800 truncate">
                                        {{ $ret->customer->name ?? 'Penyewa' }}
                                    </p>
                                    <p class="text-[11px] text-slate-500 truncate mt-0.5">
                                        {{ $ret->details->first()->barang->nama ?? 'Barang Rental' }}
                                    </p>
                                    <span class="inline-block px-2 py-0.5 bg-sky-100 text-sky-700 text-[10px] font-black rounded-md mt-1">
                                        Batas: {{ \Carbon\Carbon::parse($ret->tanggal_selesai ?? $ret->end_rent)->format('d M Y') }}
                                    </span>
                                </div>
                                @if(!empty($cleanPhone))
                                    <a href="https://wa.me/{{ $cleanPhone }}?text=Halo%20{{ urlencode($ret->customer->name ?? 'Kak') }},%20kami%20dari%20{{ urlencode($user->vendor_name ?? 'Rentify Vendor') }}%20mengingatkan%20pengembalian%20sewa%20pesanan%20%23{{ $ret->id }}%20hari%20ini.%20Terima%20kasih!" target="_blank" class="px-3 py-1.5 bg-emerald-500 hover:bg-emerald-600 text-white rounded-xl text-xs font-black shrink-0 flex items-center gap-1.5 shadow-xs">
                                        <i class="fa-brands fa-whatsapp text-sm"></i>
                                        <span>WA</span>
                                    </a>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

        </div>
    @endif

    <!-- ============================================================ -->
    <!-- 3. METRIC CARDS (4 STATS TERBAIK) -->
    <!-- ============================================================ -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">
        
        <!-- Saldo Aktif -->
        <div class="p-5 rounded-3xl bg-gradient-to-br from-slate-900 via-slate-800 to-sky-950 text-white shadow-lg relative overflow-hidden group">
            <div class="absolute -right-6 -top-6 w-28 h-28 bg-sky-500/10 rounded-full blur-xl group-hover:bg-sky-500/20 transition-all"></div>
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold text-sky-200">Saldo Siap Ditarik</span>
                <div class="w-8 h-8 rounded-xl bg-white/10 flex items-center justify-center text-sky-300 text-sm">
                    <i class="fa-solid fa-wallet"></i>
                </div>
            </div>
            <h3 class="text-2xl sm:text-3xl font-black tracking-tight text-white mb-3">
                Rp {{ number_format($totalSaldo, 0, ',', '.') }}
            </h3>
            <div class="pt-3 border-t border-white/10 flex items-center justify-between">
                <span class="text-[11px] text-slate-300">
                    Tertahan: <strong class="text-sky-300">Rp {{ number_format($saldoTertahan, 0, ',', '.') }}</strong>
                </span>
                <a href="{{ route('vendor.saldo.index') }}" class="px-2.5 py-1 bg-sky-500 hover:bg-sky-400 text-slate-950 text-[11px] font-black rounded-lg transition">
                    Tarik
                </a>
            </div>
        </div>

        <!-- Armada Rental & Okupansi -->
        <div class="p-5 rounded-3xl bg-white border border-slate-200 shadow-sm flex flex-col justify-between hover:shadow-md transition">
            <div>
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-bold text-slate-500">Armada Sedang Disewa</span>
                    <div class="w-8 h-8 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center text-sm font-black">
                        <i class="fa-solid fa-truck-ramp-box"></i>
                    </div>
                </div>
                <div class="flex items-baseline gap-2">
                    <h3 class="text-2xl sm:text-3xl font-black text-slate-800">{{ $unitSedangDisewa }}</h3>
                    <span class="text-xs font-bold text-slate-400">/ {{ $totalUnitFisik }} unit fisik</span>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100">
                <div class="flex justify-between items-center text-[11px] font-bold text-slate-500 mb-1.5">
                    <span>Okupansi</span>
                    <span class="text-sky-600 font-extrabold">{{ $tingkatKeterisian }}%</span>
                </div>
                <div class="w-full h-2 bg-slate-100 rounded-full overflow-hidden">
                    <div class="h-full bg-gradient-to-r from-sky-400 to-blue-600 rounded-full" style="width: {{ min(100, $tingkatKeterisian) }}%"></div>
                </div>
            </div>
        </div>

        <!-- Pesanan Aktif -->
        <div class="p-5 rounded-3xl bg-white border border-slate-200 shadow-sm flex flex-col justify-between hover:shadow-md transition">
            <div>
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-bold text-slate-500">Pesanan Aktif Berjalan</span>
                    <div class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-sm font-black">
                        <i class="fa-solid fa-clipboard-check"></i>
                    </div>
                </div>
                <div class="flex items-baseline gap-2">
                    <h3 class="text-2xl sm:text-3xl font-black text-slate-800">{{ $jmlPesananAktif }}</h3>
                    <span class="text-xs font-bold text-slate-400">pesanan</span>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between">
                <span class="text-[11px] text-slate-500">Total Transaksi Selesai</span>
                <span class="text-xs font-black text-emerald-600">{{ $totalPenyewaanSelesai }} sukses</span>
            </div>
        </div>

        <!-- Katalog Produk -->
        <div class="p-5 rounded-3xl bg-white border border-slate-200 shadow-sm flex flex-col justify-between hover:shadow-md transition">
            <div>
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-bold text-slate-500">Katalog Produk Tayang</span>
                    <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-sm font-black">
                        <i class="fa-solid fa-box-open"></i>
                    </div>
                </div>
                <div class="flex items-baseline gap-2">
                    <h3 class="text-2xl sm:text-3xl font-black text-slate-800">{{ $produkAktif }}</h3>
                    <span class="text-xs font-bold text-slate-400">/ {{ $totalProduk }} terdaftar</span>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between">
                @if($jmlPending > 0)
                    <span class="text-[11px] font-bold text-amber-600 bg-amber-50 px-2 py-0.5 rounded-md">
                        <i class="fa-solid fa-clock mr-1"></i>{{ $jmlPending }} ditinjau
                    </span>
                @else
                    <span class="text-[11px] font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-md">
                        <i class="fa-solid fa-check mr-1"></i>Semua siap sewa
                    </span>
                @endif
                <a href="{{ route('vendor.barang.index') }}" class="text-[11px] font-extrabold text-sky-600 hover:text-sky-700">Kelola &rarr;</a>
            </div>
        </div>

    </div>

    <!-- ============================================================ -->
    <!-- 4. GRAFIK PENDAPATAN & SHORTCUTS -->
    <!-- ============================================================ -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Area Grafik dengan Toggle 7 Hari vs 30 Hari -->
        <div class="lg:col-span-2 bg-white p-5 sm:p-6 rounded-3xl border border-slate-200 shadow-sm">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
                <div>
                    <h3 class="text-base sm:text-lg font-black text-slate-800">Tren Pendapatan Bersih</h3>
                    <p class="text-xs text-slate-400 font-medium">Bulan ini: <strong class="text-emerald-600">Rp {{ number_format($pendapatanBulanIni, 0, ',', '.') }}</strong></p>
                </div>
                <div class="inline-flex p-1 bg-slate-100 rounded-xl self-start sm:self-auto text-xs font-bold">
                    <button type="button" id="btn7Days" onclick="switchChart(7)" class="px-3 py-1.5 rounded-lg bg-white text-slate-800 shadow-xs transition">
                        7 Hari
                    </button>
                    <button type="button" id="btn30Days" onclick="switchChart(30)" class="px-3 py-1.5 rounded-lg text-slate-500 hover:text-slate-800 transition">
                        30 Hari
                    </button>
                </div>
            </div>

            <div class="relative h-64 sm:h-72 w-full">
                <canvas id="vendorRevenueChart"></canvas>
            </div>
        </div>

        <!-- Kolom Kanan: Top Produk & Aksi Cepat HP -->
        <div class="space-y-6">
            
            <!-- Quick Actions Card (Sangat Membantu di HP) -->
            <div class="bg-white p-5 rounded-3xl border border-slate-200 shadow-sm">
                <h4 class="text-sm font-black text-slate-800 mb-3 flex items-center gap-2">
                    <i class="fa-solid fa-bolt text-amber-500"></i> Aksi Cepat
                </h4>
                <div class="grid grid-cols-2 gap-2.5">
                    <a href="{{ route('vendor.barang.create') }}" class="p-3 bg-sky-50/70 hover:bg-sky-100 border border-sky-100 rounded-2xl flex flex-col items-center text-center transition group">
                        <div class="w-10 h-10 rounded-xl bg-sky-500 text-white flex items-center justify-center text-base mb-1.5 shadow-sm group-hover:scale-105 transition">
                            <i class="fa-solid fa-camera"></i>
                        </div>
                        <span class="text-xs font-extrabold text-slate-800">Foto & Upload</span>
                        <span class="text-[10px] text-slate-500">Via Smartphone</span>
                    </a>

                    <a href="{{ route('vendor.pesanan.index') }}" class="p-3 bg-amber-50/70 hover:bg-amber-100 border border-amber-100 rounded-2xl flex flex-col items-center text-center transition group">
                        <div class="w-10 h-10 rounded-xl bg-amber-500 text-white flex items-center justify-center text-base mb-1.5 shadow-sm group-hover:scale-105 transition">
                            <i class="fa-solid fa-list-check"></i>
                        </div>
                        <span class="text-xs font-extrabold text-slate-800">Cek Pesanan</span>
                        <span class="text-[10px] text-slate-500">Konfirmasi Sewa</span>
                    </a>

                    <a href="{{ route('vendor.saldo.index') }}" class="p-3 bg-emerald-50/70 hover:bg-emerald-100 border border-emerald-100 rounded-2xl flex flex-col items-center text-center transition group">
                        <div class="w-10 h-10 rounded-xl bg-emerald-500 text-white flex items-center justify-center text-base mb-1.5 shadow-sm group-hover:scale-105 transition">
                            <i class="fa-solid fa-money-bill-transfer"></i>
                        </div>
                        <span class="text-xs font-extrabold text-slate-800">Tarik Dana</span>
                        <span class="text-[10px] text-slate-500">Ke Rekening Bank</span>
                    </a>

                    <a href="{{ route('vendor.pengaturan.index') }}" class="p-3 bg-slate-50 hover:bg-slate-100 border border-slate-200/80 rounded-2xl flex flex-col items-center text-center transition group">
                        <div class="w-10 h-10 rounded-xl bg-slate-700 text-white flex items-center justify-center text-base mb-1.5 shadow-sm group-hover:scale-105 transition">
                            <i class="fa-solid fa-store-gear"></i>
                        </div>
                        <span class="text-xs font-extrabold text-slate-800">Profil Toko</span>
                        <span class="text-[10px] text-slate-500">Jam Buka & Lokasi</span>
                    </a>
                </div>
            </div>

            <!-- Top Produk Leaderboard -->
            <div class="bg-white p-5 rounded-3xl border border-slate-200 shadow-sm">
                <div class="flex items-center justify-between mb-3.5">
                    <h4 class="text-sm font-black text-slate-800 flex items-center gap-2">
                        <i class="fa-solid fa-trophy text-amber-500"></i> Produk Paling Laris
                    </h4>
                    <a href="{{ route('vendor.barang.index') }}" class="text-[11px] font-extrabold text-sky-600 hover:text-sky-700">Semua</a>
                </div>

                @if($topProducts->count() > 0)
                    <div class="space-y-3">
                        @foreach($topProducts as $idx => $prod)
                            <div class="flex items-center gap-3">
                                <span class="w-6 h-6 rounded-lg {{ $idx === 0 ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-500' }} flex items-center justify-center font-black text-xs shrink-0">
                                    {{ $idx + 1 }}
                                </span>
                                <div class="w-10 h-10 rounded-xl bg-slate-100 overflow-hidden shrink-0 border border-slate-200">
                                    @php
                                        $furl = $prod->foto_url ?? ($prod->fotos->first()->foto_url ?? null);
                                    @endphp
                                    @if($furl)
                                        <img src="{{ $furl }}" alt="{{ $prod->nama }}" class="w-full h-full object-cover">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center text-slate-300"><i class="fa-solid fa-image text-xs"></i></div>
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-black text-slate-800 truncate">{{ $prod->nama }}</p>
                                    <p class="text-[10px] text-slate-400">Rp {{ number_format($prod->harga_sewa_harian, 0, ',', '.') }}/hari</p>
                                </div>
                                <span class="px-2 py-0.5 bg-sky-50 text-sky-700 rounded-md text-[10px] font-black shrink-0">
                                    {{ $prod->total_sewa ?? 0 }}x disewa
                                </span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-6">
                        <i class="fa-solid fa-box-open text-3xl text-slate-300 mb-2"></i>
                        <p class="text-xs text-slate-400 font-medium">Belum ada statistik sewa produk</p>
                    </div>
                @endif
            </div>

        </div>

    </div>

    <!-- ============================================================ -->
    <!-- 5. TABEL PESANAN TERAKHIR (RESPONSIVE) -->
    <!-- ============================================================ -->
    <div class="bg-white p-5 sm:p-6 rounded-3xl border border-slate-200 shadow-sm">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-base font-black text-slate-800">Pesanan Masuk Terkini</h3>
                <p class="text-xs text-slate-400 font-medium">5 transaksi pemesanan paling baru</p>
            </div>
            <a href="{{ route('vendor.pesanan.index') }}" class="text-xs font-black text-sky-600 hover:text-sky-700">
                Lihat Semua &rarr;
            </a>
        </div>

        @if($recentOrders->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-slate-100 text-slate-400 font-black uppercase text-[10px] tracking-wider">
                            <th class="pb-3">ID Pesanan</th>
                            <th class="pb-3">Penyewa</th>
                            <th class="pb-3">Produk</th>
                            <th class="pb-3">Durasi Sewa</th>
                            <th class="pb-3">Total Biaya</th>
                            <th class="pb-3">Status</th>
                            <th class="pb-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($recentOrders as $order)
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="py-3 font-black text-slate-800">#{{ $order->id }}</td>
                                <td class="py-3">
                                    <div class="font-bold text-slate-700">{{ $order->customer->name ?? 'Pelanggan' }}</div>
                                    <div class="text-[10px] text-slate-400">{{ $order->customer->no_hp ?? '' }}</div>
                                </td>
                                <td class="py-3 font-medium text-slate-700">
                                    {{ $order->details->first()->barang->nama ?? 'Produk' }}
                                    @if($order->details->count() > 1)
                                        <span class="text-sky-600 font-bold">+{{ $order->details->count() - 1 }}</span>
                                    @endif
                                </td>
                                <td class="py-3 text-slate-500">
                                    {{ \Carbon\Carbon::parse($order->tanggal_mulai ?? $order->start_rent)->format('d M') }} - {{ \Carbon\Carbon::parse($order->tanggal_selesai ?? $order->end_rent)->format('d M Y') }}
                                </td>
                                <td class="py-3 font-black text-slate-800">
                                    Rp {{ number_format($order->total_biaya ?? $order->total_price ?? 0, 0, ',', '.') }}
                                </td>
                                <td class="py-3">
                                    @php
                                        $st = strtolower($order->status);
                                        $badgeClass = 'bg-slate-100 text-slate-600';
                                        if (in_array($st, ['selesai'])) $badgeClass = 'bg-emerald-50 text-emerald-700 border border-emerald-200';
                                        elseif (in_array($st, ['menunggu konfirmasi', 'pending'])) $badgeClass = 'bg-amber-50 text-amber-700 border border-amber-200';
                                        elseif (in_array($st, ['disetujui', 'sedang disewa', 'berjalan'])) $badgeClass = 'bg-sky-50 text-sky-700 border border-sky-200';
                                        elseif (in_array($st, ['dibatalkan', 'ditolak'])) $badgeClass = 'bg-rose-50 text-rose-700 border border-rose-200';
                                    @endphp
                                    <span class="inline-block px-2.5 py-1 rounded-full text-[10px] font-black {{ $badgeClass }}">
                                        {{ ucfirst($order->status) }}
                                    </span>
                                </td>
                                <td class="py-3 text-right">
                                    <a href="{{ route('vendor.pesanan.index') }}" class="px-2.5 py-1 bg-slate-100 hover:bg-sky-50 hover:text-sky-600 text-slate-700 rounded-lg text-xs font-bold transition">
                                        Rincian
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-8 text-slate-400">
                <i class="fa-solid fa-receipt text-3xl mb-2 text-slate-300"></i>
                <p class="text-xs font-medium">Belum ada pesanan masuk untuk toko Anda.</p>
            </div>
        @endif
    </div>

</div>
@endsection

@push('scripts')
<script>
    let revenueChart;
    const chart7Labels = {!! json_encode($chart7Labels) !!};
    const chart7Data = {!! json_encode($chart7Data) !!};
    const chart30Labels = {!! json_encode($chart30Labels) !!};
    const chart30Data = {!! json_encode($chart30Data) !!};

    document.addEventListener("DOMContentLoaded", function() {
        const canvas = document.getElementById('vendorRevenueChart');
        if (!canvas) return;
        const ctx = canvas.getContext('2d');
        
        let gradient = ctx.createLinearGradient(0, 0, 0, 300);
        gradient.addColorStop(0, 'rgba(14, 165, 233, 0.35)');
        gradient.addColorStop(1, 'rgba(14, 165, 233, 0.0)');

        revenueChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: chart7Labels,
                datasets: [{
                    label: 'Pendapatan (Rp)',
                    data: chart7Data,
                    borderColor: '#0284c7',
                    backgroundColor: gradient,
                    borderWidth: 3,
                    pointBackgroundColor: '#ffffff',
                    pointBorderColor: '#0284c7',
                    pointBorderWidth: 2.5,
                    pointRadius: 4.5,
                    pointHoverRadius: 7,
                    fill: true,
                    tension: 0.35
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#0f172a',
                        titleFont: { family: 'Plus Jakarta Sans', size: 12, weight: 'bold' },
                        bodyFont: { family: 'Plus Jakarta Sans', size: 12 },
                        padding: 10,
                        cornerRadius: 12,
                        callbacks: {
                            label: function(context) {
                                return ' Pendapatan: Rp ' + context.parsed.y.toLocaleString('id-ID');
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { borderDash: [4, 4], color: '#f1f5f9' },
                        ticks: {
                            font: { family: 'Plus Jakarta Sans', size: 10 },
                            callback: function(value) {
                                if (value >= 1000000) return (value / 1000000).toFixed(1) + ' jt';
                                if (value >= 1000) return (value / 1000).toFixed(0) + ' rb';
                                return value;
                            }
                        },
                        border: { display: false }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { font: { family: 'Plus Jakarta Sans', size: 10 } },
                        border: { display: false }
                    }
                }
            }
        });
    });

    function switchChart(period) {
        if (!revenueChart) return;
        const btn7 = document.getElementById('btn7Days');
        const btn30 = document.getElementById('btn30Days');

        if (period === 7) {
            revenueChart.data.labels = chart7Labels;
            revenueChart.data.datasets[0].data = chart7Data;
            btn7.className = "px-3 py-1.5 rounded-lg bg-white text-slate-800 shadow-xs transition";
            btn30.className = "px-3 py-1.5 rounded-lg text-slate-500 hover:text-slate-800 transition";
        } else {
            revenueChart.data.labels = chart30Labels;
            revenueChart.data.datasets[0].data = chart30Data;
            btn30.className = "px-3 py-1.5 rounded-lg bg-white text-slate-800 shadow-xs transition";
            btn7.className = "px-3 py-1.5 rounded-lg text-slate-500 hover:text-slate-800 transition";
        }
        revenueChart.update();
    }
</script>
@endpush