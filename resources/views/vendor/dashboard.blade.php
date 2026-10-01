@extends('layouts.vendor')

@section('title', 'Dashboard Vendor — Rentify')

@section('content')
<div class="px-3.5 sm:px-6 lg:px-8 py-4 sm:py-6 max-w-7xl mx-auto space-y-4 sm:space-y-6">

    <!-- ============================================================ -->
    <!-- 1. COMPACT STORE HEADER (ALA TOKOPEDIA / SHOPEE SELLER) -->
    <!-- ============================================================ -->
    <div class="bg-white p-3.5 sm:p-5 rounded-2xl sm:rounded-3xl border border-slate-200/80 shadow-xs flex items-center justify-between gap-3">
        <div class="flex items-center gap-3 min-w-0">
            <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl sm:rounded-2xl bg-gradient-to-tr from-sky-500 to-blue-600 text-white flex items-center justify-center font-black text-base sm:text-lg shadow-sm shrink-0">
                <i class="fa-solid fa-store"></i>
            </div>
            <div class="min-w-0">
                <div class="flex items-center gap-2">
                    <h1 class="text-base sm:text-xl font-black text-slate-800 tracking-tight truncate">
                        {{ $user->vendor_name ?? $user->name }}
                    </h1>
                    @if(($user->vendor_status ?? 'pending') === 'approved')
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-emerald-50 border border-emerald-200 text-emerald-700 text-[10px] font-extrabold rounded-md shrink-0">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            Aktif
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-amber-50 border border-amber-200 text-amber-700 text-[10px] font-extrabold rounded-md shrink-0">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                            Kurasi
                        </span>
                    @endif
                </div>
                <p class="text-[11px] sm:text-xs text-slate-400 font-medium truncate mt-0.5">
                    ID Toko: #VND-{{ $user->id }} • {{ $totalProduk }} Produk Terdaftar
                </p>
            </div>
        </div>

        <!-- Tombol Tambah Barang Khusus Desktop (Layar Lebar) -->
        <div class="hidden lg:flex items-center gap-2">
            <a href="{{ route('vendor.barang.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-sky-500 to-blue-600 hover:from-sky-600 text-white rounded-xl text-xs font-black shadow-sm transition active:scale-95">
                <i class="fa-solid fa-plus text-xs"></i>
                <span>Upload Produk</span>
            </a>
            <a href="{{ route('vendor.voucher.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition">
                <i class="fa-solid fa-ticket text-xs text-sky-600"></i>
                <span>Voucher</span>
            </a>
        </div>
    </div>

    <!-- Alert Kurasi Jika Belum Disetujui -->
    @if(($user->vendor_status ?? 'pending') === 'pending')
        <div class="p-3 sm:p-4 bg-amber-50 border border-amber-200 rounded-2xl text-amber-900 flex items-center gap-3 text-xs shadow-xs">
            <i class="fa-solid fa-hourglass-half text-amber-600 text-base shrink-0"></i>
            <p class="font-medium text-[11px] sm:text-xs leading-tight">
                <strong>Status Kemitraan:</strong> Toko Anda sedang dalam antrean verifikasi Admin. Anda sudah bisa mulai mengunggah katalog & membuat voucher promo.
            </p>
        </div>
    @endif

    <!-- ============================================================ -->
    <!-- 2. SALDO & KEUANGAN TOKO (COMPACT CARD) -->
    <!-- ============================================================ -->
    <div class="bg-gradient-to-br from-slate-900 via-slate-800 to-sky-950 text-white p-4 sm:p-5 rounded-2xl sm:rounded-3xl shadow-md relative overflow-hidden">
        <div class="flex items-center justify-between gap-3 mb-2">
            <span class="text-xs font-bold text-sky-200 flex items-center gap-1.5">
                <i class="fa-solid fa-wallet text-sky-400"></i> Saldo Toko
            </span>
            <a href="{{ route('vendor.saldo.index') }}" class="px-3 py-1 bg-sky-400 hover:bg-sky-300 text-slate-950 text-xs font-black rounded-lg transition active:scale-95">
                Tarik Dana
            </a>
        </div>
        
        <h3 class="text-2xl sm:text-3xl font-black tracking-tight text-white mb-3">
            Rp {{ number_format($totalSaldo, 0, ',', '.') }}
        </h3>

        <div class="pt-2.5 border-t border-white/10 flex items-center justify-between text-[11px] sm:text-xs text-slate-300 font-medium">
            <span>Tertahan: <strong class="text-sky-300">Rp {{ number_format($saldoTertahan, 0, ',', '.') }}</strong></span>
            <span>Bulan Ini: <strong class="text-emerald-400">Rp {{ number_format($pendapatanBulanIni, 0, ',', '.') }}</strong></span>
        </div>
    </div>

    <!-- ============================================================ -->
    <!-- 3. STATUS PESANAN (4-GRID ALA TOKOPEDIA / SHOPEE SELLER) -->
    <!-- ============================================================ -->
    <div>
        <div class="flex items-center justify-between mb-2">
            <h2 class="text-xs sm:text-sm font-black text-slate-800 uppercase tracking-wider">
                Status Pesanan
            </h2>
            <a href="{{ route('vendor.pesanan.index') }}" class="text-[11px] font-extrabold text-sky-600 hover:underline">
                Lihat Semua &rarr;
            </a>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 sm:gap-3">
            
            <!-- 1. Perlu Konfirmasi -->
            <a href="{{ route('vendor.pesanan.index') }}" class="p-3 sm:p-4 rounded-2xl bg-white border {{ $jmlMenungguKonfirmasi > 0 ? 'border-amber-300 bg-amber-50/40' : 'border-slate-200/80' }} shadow-xs hover:border-amber-400 transition flex flex-col justify-between group">
                <div class="flex items-center justify-between mb-1.5">
                    <span class="text-[11px] sm:text-xs font-bold text-slate-600">Perlu Diproses</span>
                    <i class="fa-solid fa-bell text-xs {{ $jmlMenungguKonfirmasi > 0 ? 'text-amber-500 animate-bounce' : 'text-slate-300' }}"></i>
                </div>
                <div class="flex items-baseline gap-1.5">
                    <span class="text-xl sm:text-2xl font-black {{ $jmlMenungguKonfirmasi > 0 ? 'text-amber-600' : 'text-slate-800' }}">
                        {{ $jmlMenungguKonfirmasi }}
                    </span>
                    <span class="text-[10px] text-slate-400">pesanan</span>
                </div>
            </a>

            <!-- 2. Sedang Disewa -->
            <a href="{{ route('vendor.pesanan.index') }}" class="p-3 sm:p-4 rounded-2xl bg-white border border-slate-200/80 shadow-xs hover:border-sky-400 transition flex flex-col justify-between group">
                <div class="flex items-center justify-between mb-1.5">
                    <span class="text-[11px] sm:text-xs font-bold text-slate-600">Sedang Disewa</span>
                    <i class="fa-solid fa-truck-ramp-box text-xs text-sky-500"></i>
                </div>
                <div class="flex items-baseline gap-1.5">
                    <span class="text-xl sm:text-2xl font-black text-slate-800">
                        {{ $jmlSedangDisewa }}
                    </span>
                    <span class="text-[10px] text-slate-400">unit berjalan</span>
                </div>
            </a>

            <!-- 3. Jatuh Tempo / Pengembalian -->
            <a href="{{ route('vendor.pesanan.index') }}" class="p-3 sm:p-4 rounded-2xl bg-white border {{ $jmlJatuhTempo > 0 ? 'border-rose-300 bg-rose-50/40' : 'border-slate-200/80' }} shadow-xs hover:border-rose-400 transition flex flex-col justify-between group">
                <div class="flex items-center justify-between mb-1.5">
                    <span class="text-[11px] sm:text-xs font-bold text-slate-600">Jatuh Tempo</span>
                    <i class="fa-solid fa-clock-rotate-left text-xs {{ $jmlJatuhTempo > 0 ? 'text-rose-500' : 'text-slate-300' }}"></i>
                </div>
                <div class="flex items-baseline gap-1.5">
                    <span class="text-xl sm:text-2xl font-black {{ $jmlJatuhTempo > 0 ? 'text-rose-600' : 'text-slate-800' }}">
                        {{ $jmlJatuhTempo }}
                    </span>
                    <span class="text-[10px] text-slate-400">hari ini</span>
                </div>
            </a>

            <!-- 4. Transaksi Selesai -->
            <a href="{{ route('vendor.pesanan.index') }}" class="p-3 sm:p-4 rounded-2xl bg-white border border-slate-200/80 shadow-xs hover:border-emerald-400 transition flex flex-col justify-between group">
                <div class="flex items-center justify-between mb-1.5">
                    <span class="text-[11px] sm:text-xs font-bold text-slate-600">Selesai</span>
                    <i class="fa-solid fa-circle-check text-xs text-emerald-500"></i>
                </div>
                <div class="flex items-baseline gap-1.5">
                    <span class="text-xl sm:text-2xl font-black text-slate-800">
                        {{ $jmlSelesai }}
                    </span>
                    <span class="text-[10px] text-slate-400">sukses</span>
                </div>
            </a>

        </div>
    </div>

    <!-- ============================================================ -->
    <!-- 4. PUSAT FITUR UTAMA: KATALOG, VOUCHER TOKO & ARMADA -->
    <!-- ============================================================ -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
        
        <!-- KATALOG PRODUK SAYA -->
        <a href="{{ route('vendor.barang.index') }}" class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-xs hover:border-sky-300 transition flex items-center justify-between group">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center text-base shrink-0 group-hover:scale-105 transition">
                    <i class="fa-solid fa-boxes-stacked"></i>
                </div>
                <div>
                    <h4 class="text-xs sm:text-sm font-black text-slate-800 group-hover:text-sky-600 transition">Produk Saya</h4>
                    <p class="text-[11px] text-slate-400 font-medium">{{ $produkAktif }} Aktif • {{ $jmlPending }} Ditinjau</p>
                </div>
            </div>
            <i class="fa-solid fa-chevron-right text-xs text-slate-300 group-hover:text-sky-500 group-hover:translate-x-0.5 transition"></i>
        </a>

        <!-- VOUCHER PROMO TOKO (YANG SEBELUMNYA HILANG - KINI JADI FITUR UTAMA) -->
        <a href="{{ route('vendor.voucher.index') }}" class="p-4 rounded-2xl bg-gradient-to-r from-amber-50 to-orange-50 border border-amber-200/80 shadow-xs hover:border-amber-400 transition flex items-center justify-between group">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-amber-500 text-white flex items-center justify-center text-base shrink-0 shadow-xs group-hover:scale-105 transition">
                    <i class="fa-solid fa-ticket"></i>
                </div>
                <div>
                    <div class="flex items-center gap-1.5">
                        <h4 class="text-xs sm:text-sm font-black text-amber-950 group-hover:text-amber-700 transition">Voucher Toko</h4>
                        <span class="px-1.5 py-0.5 bg-amber-200/80 text-amber-800 text-[9px] font-black rounded-md uppercase">Promo</span>
                    </div>
                    <p class="text-[11px] text-amber-800/80 font-medium">
                        {{ $totalVoucher > 0 ? $totalVoucher . ' Voucher Diskon Aktif' : 'Buat diskon untuk customer' }}
                    </p>
                </div>
            </div>
            <span class="text-xs font-black text-amber-700 bg-white/80 px-2.5 py-1 rounded-lg border border-amber-200">
                Kelola &rarr;
            </span>
        </a>

        <!-- KESEHATAN ARMADA & OKUPANSI -->
        <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-xs flex flex-col justify-between sm:col-span-2 lg:col-span-1">
            <div class="flex items-center justify-between mb-2">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-chart-pie text-sky-500 text-xs"></i>
                    <span class="text-xs font-bold text-slate-700">Okupansi Armada</span>
                </div>
                <span class="text-xs font-black text-sky-600">{{ $tingkatKeterisian }}%</span>
            </div>
            <div class="w-full h-2 bg-slate-100 rounded-full overflow-hidden mb-1.5">
                <div class="h-full bg-gradient-to-r from-sky-400 to-blue-600 rounded-full" style="width: {{ min(100, $tingkatKeterisian) }}%"></div>
            </div>
            <div class="flex justify-between text-[10px] text-slate-400 font-medium">
                <span>{{ $unitSedangDisewa }} unit disewa</span>
                <span>{{ $unitStandby }} unit standby</span>
            </div>
        </div>

    </div>

    <!-- ============================================================ -->
    <!-- 5. JADWAL PENGEMBALIAN & PESANAN BUTUH TINDAKAN (JIKA ADA) -->
    <!-- ============================================================ -->
    @if($pengembalianHariIni->count() > 0)
        <div class="bg-sky-50/70 border border-sky-200 rounded-2xl p-3.5 sm:p-4 space-y-2.5">
            <div class="flex items-center justify-between">
                <span class="text-xs font-black text-sky-950 flex items-center gap-1.5">
                    <i class="fa-solid fa-clock-rotate-left text-sky-600"></i> Pengembalian Hari Ini
                </span>
                <span class="text-[10px] font-bold text-sky-600 bg-white px-2 py-0.5 rounded-full border border-sky-100">
                    {{ $pengembalianHariIni->count() }} Jadwal
                </span>
            </div>
            <div class="space-y-2">
                @foreach($pengembalianHariIni as $ret)
                    @php
                        $hp = $ret->customer->no_hp ?? $ret->customer->phone ?? '';
                        $cleanPhone = preg_replace('/[^0-9]/', '', $hp);
                        if (str_starts_with($cleanPhone, '0')) {
                            $cleanPhone = '62' . substr($cleanPhone, 1);
                        }
                    @endphp
                    <div class="bg-white p-2.5 sm:p-3 rounded-xl border border-sky-100 flex items-center justify-between gap-2 shadow-xs">
                        <div class="min-w-0">
                            <p class="font-black text-xs text-slate-800 truncate">{{ $ret->customer->name ?? 'Penyewa' }}</p>
                            <p class="text-[10px] text-slate-400 truncate">{{ $ret->details->first()->barang->nama ?? 'Produk' }}</p>
                        </div>
                        @if(!empty($cleanPhone))
                            <a href="https://wa.me/{{ $cleanPhone }}?text=Halo%20{{ urlencode($ret->customer->name ?? 'Kak') }},%20pengingat%20pengembalian%20sewa%20pesanan%20%23{{ $ret->id }}%20hari%20ini.%20Terima%20kasih!" target="_blank" class="px-2.5 py-1 bg-emerald-500 hover:bg-emerald-600 text-white rounded-lg text-[11px] font-black shrink-0 flex items-center gap-1">
                                <i class="fa-brands fa-whatsapp"></i> Chat WA
                            </a>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- ============================================================ -->
    <!-- 6. GRAFIK PENDAPATAN & PRODUK TERLARIS -->
    <!-- ============================================================ -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6">
        
        <!-- Grafik Penjualan (Clean & Ringkas) -->
        <div class="lg:col-span-2 bg-white p-4 sm:p-5 rounded-2xl sm:rounded-3xl border border-slate-200/80 shadow-xs">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-xs sm:text-sm font-black text-slate-800">Tren Pendapatan</h3>
                    <p class="text-[10px] text-slate-400">Total bersih setelah bagi hasil</p>
                </div>
                <div class="inline-flex p-1 bg-slate-100 rounded-xl text-[11px] font-bold">
                    <button type="button" id="btn7Days" onclick="switchChart(7)" class="px-2.5 py-1 rounded-lg bg-white text-slate-800 shadow-xs transition">
                        7 Hari
                    </button>
                    <button type="button" id="btn30Days" onclick="switchChart(30)" class="px-2.5 py-1 rounded-lg text-slate-500 hover:text-slate-800 transition">
                        30 Hari
                    </button>
                </div>
            </div>

            <div class="relative h-52 sm:h-64 w-full">
                <canvas id="vendorRevenueChart"></canvas>
            </div>
        </div>

        <!-- Kolom Kanan: Top Produk Leaderboard -->
        <div class="bg-white p-4 sm:p-5 rounded-2xl sm:rounded-3xl border border-slate-200/80 shadow-xs space-y-3">
            <div class="flex items-center justify-between border-b border-slate-100 pb-2.5">
                <h4 class="text-xs sm:text-sm font-black text-slate-800 flex items-center gap-1.5">
                    <i class="fa-solid fa-trophy text-amber-500"></i> Terlaris
                </h4>
                <a href="{{ route('vendor.barang.index') }}" class="text-[11px] font-extrabold text-sky-600 hover:underline">Semua</a>
            </div>

            @if($topProducts->count() > 0)
                <div class="space-y-2.5">
                    @foreach($topProducts as $idx => $prod)
                        <div class="flex items-center gap-2.5">
                            <span class="w-5 h-5 rounded-md {{ $idx === 0 ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-500' }} flex items-center justify-center font-black text-[10px] shrink-0">
                                {{ $idx + 1 }}
                            </span>
                            <div class="w-8 h-8 rounded-lg bg-slate-100 overflow-hidden shrink-0 border border-slate-200">
                                @php
                                    $furl = $prod->cover_photo ?? ($prod->fotos->first()->foto_path ?? null);
                                    if ($furl && !str_starts_with($furl, 'http')) {
                                        $furl = asset(str_replace('public/', '', $furl));
                                    }
                                @endphp
                                @if($furl)
                                    <img src="{{ $furl }}" alt="{{ $prod->nama }}" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-slate-300"><i class="fa-solid fa-image text-xs"></i></div>
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-xs font-black text-slate-800 truncate">{{ $prod->nama }}</p>
                                <p class="text-[10px] text-slate-400">Rp {{ number_format($prod->harga_sewa_harian, 0, ',', '.') }}/hr</p>
                            </div>
                            <span class="px-2 py-0.5 bg-sky-50 text-sky-700 rounded text-[10px] font-black shrink-0">
                                {{ $prod->total_sewa ?? 0 }}x
                            </span>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-6 text-slate-400">
                    <i class="fa-solid fa-box-open text-2xl text-slate-300 mb-1"></i>
                    <p class="text-[11px] font-medium">Belum ada statistik sewa</p>
                </div>
            @endif
        </div>

    </div>

    <!-- ============================================================ -->
    <!-- 7. TRANSAKSI TERAKHIR (COMPACT LIST) -->
    <!-- ============================================================ -->
    <div class="bg-white p-4 sm:p-5 rounded-2xl sm:rounded-3xl border border-slate-200/80 shadow-xs">
        <div class="flex items-center justify-between mb-3 border-b border-slate-100 pb-2.5">
            <h3 class="text-xs sm:text-sm font-black text-slate-800">Pesanan Masuk Terbaru</h3>
            <a href="{{ route('vendor.pesanan.index') }}" class="text-[11px] font-extrabold text-sky-600 hover:underline">
                Semua Pesanan &rarr;
            </a>
        </div>

        @if($recentOrders->count() > 0)
            <div class="space-y-2">
                @foreach($recentOrders as $order)
                    <div class="p-3 rounded-xl border border-slate-100 hover:bg-slate-50/80 transition flex items-center justify-between gap-3">
                        <div class="min-w-0">
                            <div class="flex items-center gap-2">
                                <span class="font-black text-xs text-slate-800">#{{ $order->id }}</span>
                                <span class="text-[10px] text-slate-400 font-medium">• {{ $order->customer->name ?? 'Pelanggan' }}</span>
                            </div>
                            <p class="text-[11px] text-slate-600 truncate mt-0.5">
                                {{ $order->details->first()->barang->nama ?? 'Produk Rental' }}
                                @if($order->details->count() > 1)
                                    <span class="text-sky-600 font-bold">+{{ $order->details->count() - 1 }}</span>
                                @endif
                            </p>
                        </div>
                        <div class="text-right shrink-0">
                            <p class="text-xs font-black text-slate-800">
                                Rp {{ number_format($order->total_biaya ?? $order->total_price ?? 0, 0, ',', '.') }}
                            </p>
                            @php
                                $st = strtolower($order->status);
                                $badgeClass = 'bg-slate-100 text-slate-600';
                                if (in_array($st, ['selesai'])) $badgeClass = 'bg-emerald-50 text-emerald-700';
                                elseif (in_array($st, ['menunggu konfirmasi', 'pending'])) $badgeClass = 'bg-amber-50 text-amber-700';
                                elseif (in_array($st, ['disetujui', 'sedang disewa', 'berjalan'])) $badgeClass = 'bg-sky-50 text-sky-700';
                                elseif (in_array($st, ['dibatalkan', 'ditolak'])) $badgeClass = 'bg-rose-50 text-rose-700';
                            @endphp
                            <span class="inline-block px-2 py-0.5 rounded text-[9px] font-black {{ $badgeClass }} mt-0.5">
                                {{ ucfirst($order->status) }}
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-6 text-slate-400">
                <p class="text-xs font-medium">Belum ada pesanan masuk</p>
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
        
        let gradient = ctx.createLinearGradient(0, 0, 0, 240);
        gradient.addColorStop(0, 'rgba(14, 165, 233, 0.35)');
        gradient.addColorStop(1, 'rgba(14, 165, 233, 0.0)');

        revenueChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: chart7Labels,
                datasets: [{
                    label: 'Pendapatan',
                    data: chart7Data,
                    borderColor: '#0284c7',
                    backgroundColor: gradient,
                    borderWidth: 2.5,
                    pointBackgroundColor: '#ffffff',
                    pointBorderColor: '#0284c7',
                    pointBorderWidth: 2,
                    pointRadius: 3.5,
                    pointHoverRadius: 6,
                    fill: true,
                    tension: 0.3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#0f172a',
                        titleFont: { family: 'Plus Jakarta Sans', size: 11, weight: 'bold' },
                        bodyFont: { family: 'Plus Jakarta Sans', size: 11 },
                        padding: 8,
                        cornerRadius: 10,
                        callbacks: {
                            label: function(context) {
                                return ' Rp ' + context.parsed.y.toLocaleString('id-ID');
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { borderDash: [3, 3], color: '#f1f5f9' },
                        ticks: {
                            font: { family: 'Plus Jakarta Sans', size: 9 },
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
                        ticks: { font: { family: 'Plus Jakarta Sans', size: 9 } },
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
            btn7.className = "px-2.5 py-1 rounded-lg bg-white text-slate-800 shadow-xs transition";
            btn30.className = "px-2.5 py-1 rounded-lg text-slate-500 hover:text-slate-800 transition";
        } else {
            revenueChart.data.labels = chart30Labels;
            revenueChart.data.datasets[0].data = chart30Data;
            btn30.className = "px-2.5 py-1 rounded-lg bg-white text-slate-800 shadow-xs transition";
            btn7.className = "px-2.5 py-1 rounded-lg text-slate-500 hover:text-slate-800 transition";
        }
        revenueChart.update();
    }
</script>
@endpush