<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Keuangan & Transaksi - Admin Rentify</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="{{ asset('rentify-theme.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="text-slate-800 flex h-screen overflow-hidden bg-slate-50/50">

    <!-- SIDEBAR -->
    <aside class="w-64 bg-white border-r border-slate-200 flex flex-col justify-between shadow-sm z-10 overflow-y-auto">
        <div>
            <div class="p-6 border-b border-slate-100 flex items-center space-x-3 bg-gradient-to-r from-[#e0f2fe] to-[#bae6fd]">
                <div class="w-10 h-10 rentify-card flex items-center justify-center p-1 overflow-hidden border border-sky-100">
                    <img src="{{ asset('images/logo.png') }}" alt="Logo" class="w-full h-full object-contain" onerror="this.outerHTML='<i class=\'fas fa-briefcase text-sky-600 text-lg\'></i>'">
                </div>
                <div>
                    <h1 class="font-black text-slate-800 tracking-tight text-[15px]">Rentify Admin</h1>
                    <p class="text-[10px] text-[#0369a1] font-bold uppercase tracking-wider">Management Center</p>
                </div>
            </div>
            
            <nav class="p-4 space-y-1">
                <a href="/admin/dashboard" class="flex items-center space-x-3 px-4 py-3 text-sm font-semibold text-slate-600 hover:bg-slate-50 hover:text-slate-900 rounded-xl transition duration-200">
                    <i class="fas fa-chart-pie text-slate-400 w-5"></i>
                    <span>Tinjauan Dasbor</span>
                </a>

                <div class="pt-4 px-4 pb-2 text-[10px] font-extrabold text-slate-400 uppercase tracking-widest">Keuangan & Pesanan</div>
                <a href="{{ route('admin.penarikan.index') }}" class="flex items-center space-x-3 px-4 py-3 text-sm font-semibold text-slate-600 hover:bg-slate-50 hover:text-slate-900 rounded-xl transition duration-200">
                    <i class="fas fa-money-bill-transfer text-slate-400 w-5"></i>
                    <span>Pencairan Dana</span>
                </a>
                <a href="{{ route('admin.pembayaran') }}" class="flex items-center space-x-3 px-4 py-3 text-sm font-semibold text-slate-600 hover:bg-slate-50 hover:text-slate-900 rounded-xl transition duration-200">
                    <i class="fas fa-credit-card text-slate-400 w-5"></i>
                    <span>Verifikasi Pembayaran</span>
                </a>
                <a href="{{ route('admin.laporan') }}" class="flex items-center space-x-3 px-4 py-3 text-sm font-bold bg-[#e0f2fe] text-[#0369a1] rounded-xl transition duration-200">
                    <i class="fas fa-chart-line text-[#0369a1] w-5"></i>
                    <span>Laporan Keuangan</span>
                </a>
                <a href="/admin/dashboard" class="flex items-center space-x-3 px-4 py-3 text-sm font-semibold text-slate-600 hover:bg-slate-50 hover:text-slate-900 rounded-xl transition duration-200">
                    <i class="fas fa-file-invoice-dollar text-slate-400 w-5"></i>
                    <span>Data Transaksi</span>
                </a>
                <a href="/admin/dashboard" class="flex items-center space-x-3 px-4 py-3 text-sm font-semibold text-slate-600 hover:bg-slate-50 hover:text-slate-900 rounded-xl transition duration-200">
                    <i class="fas fa-ticket-alt text-slate-400 w-5"></i>
                    <span>Monitor Voucher</span>
                </a>

                <div class="pt-4 px-4 pb-2 text-[10px] font-extrabold text-slate-400 uppercase tracking-widest">Pemasaran & Promosi</div>
                <a href="{{ route('admin.ai-marketing.index') }}" class="flex items-center justify-between px-4 py-3 text-sm font-semibold text-slate-600 hover:bg-slate-50 hover:text-slate-900 rounded-xl transition duration-200">
                    <div class="flex items-center space-x-3">
                        <i class="fas fa-wand-magic-sparkles text-sky-500 w-5"></i>
                        <span>AI Marketing</span>
                    </div>
                    <span class="px-1.5 py-0.5 rounded text-[9px] font-black uppercase tracking-wider bg-sky-500 text-white">AI</span>
                </a>

                <div class="pt-4 px-4 pb-2 text-[10px] font-extrabold text-slate-400 uppercase tracking-widest">Manajemen Master Data</div>
                <a href="{{ route('admin.vendors.validation') }}" class="flex items-center space-x-3 px-4 py-3 text-sm font-semibold text-slate-600 hover:bg-slate-50 hover:text-slate-900 rounded-xl transition duration-200">
                    <i class="fas fa-user-shield text-slate-400 w-5"></i>
                    <span>Validasi Vendor</span>
                </a>
                <a href="/admin/dashboard" class="flex items-center space-x-3 px-4 py-3 text-sm font-semibold text-slate-600 hover:bg-slate-50 hover:text-slate-900 rounded-xl transition duration-200">
                    <i class="fas fa-boxes text-slate-400 w-5"></i>
                    <span>Inventaris Produk</span>
                </a>
                <a href="/admin/dashboard" class="flex items-center space-x-3 px-4 py-3 text-sm font-semibold text-slate-600 hover:bg-slate-50 hover:text-slate-900 rounded-xl transition duration-200">
                    <i class="fas fa-store text-slate-400 w-5"></i>
                    <span>Mitra Vendor</span>
                </a>
                <a href="/admin/dashboard" class="flex items-center space-x-3 px-4 py-3 text-sm font-semibold text-slate-600 hover:bg-slate-50 hover:text-slate-900 rounded-xl transition duration-200">
                    <i class="fas fa-users text-slate-400 w-5"></i>
                    <span>Data Pelanggan</span>
                </a>
            </nav>
        </div>

        <div class="p-4 border-t border-slate-100">
            <div class="flex items-center justify-between p-3 bg-slate-50 rounded-xl border border-slate-100 mb-2">
                <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 bg-[#0369a1] rounded-lg flex items-center justify-center text-white font-bold text-xs shadow-md">AD</div>
                    <div class="truncate">
                        <h4 class="text-xs font-bold text-slate-800 truncate">Super Admin</h4>
                        <p class="text-[9px] text-slate-400 truncate">Online</p>
                    </div>
                </div>
                <form action="/logout" method="POST">
                    @csrf
                    <button type="submit" class="text-rose-500 hover:text-rose-700 hover:bg-rose-50 p-2 rounded-lg transition" title="Logout">
                        <i class="fas fa-sign-out-alt"></i>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="flex-1 flex flex-col overflow-hidden">
        
        <header class="rentify-navbar h-16 px-8 flex items-center justify-between z-0 bg-white border-b border-slate-200">
            <div class="flex items-center space-x-3">
                <h2 class="text-lg font-black text-slate-800 tracking-tight">Laporan Keuangan & Komisi Rentify</h2>
                <span class="text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 px-2.5 py-0.5 rounded-full flex items-center shadow-sm">
                    <i class="fas fa-scale-balanced mr-1 text-emerald-600"></i> Audit Fee 5%
                </span>
            </div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('admin.laporan.excel', 'all') }}" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs px-3.5 py-2 rounded-xl transition shadow-sm flex items-center space-x-1.5">
                    <i class="fas fa-file-excel"></i>
                    <span>Ekspor Excel</span>
                </a>
                <div class="text-xs font-bold text-slate-500 flex items-center space-x-2 bg-slate-50 px-3 py-1.5 rounded-lg border border-slate-100">
                    <i class="far fa-calendar-alt text-[#0369a1]"></i>
                    <span>{{ date('d M Y') }}</span>
                </div>
            </div>
        </header>

        <div class="flex-1 overflow-y-auto p-8 space-y-6">
            
            <!-- FILTER BAR -->
            <form action="{{ route('admin.laporan') }}" method="GET" class="rentify-card p-5 border border-slate-200/80 bg-white flex flex-wrap items-end gap-4">
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Mulai Tanggal</label>
                    <input type="date" name="start_date" value="{{ $startDate ?? '' }}" class="rentify-input px-3 py-1.5 text-xs border border-slate-200 rounded-lg">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Sampai Tanggal</label>
                    <input type="date" name="end_date" value="{{ $endDate ?? '' }}" class="rentify-input px-3 py-1.5 text-xs border border-slate-200 rounded-lg">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Status Pesanan</label>
                    <select name="status" class="rentify-input px-3 py-1.5 text-xs border border-slate-200 rounded-lg">
                        <option value="all" {{ ($statusFilter ?? '') === 'all' ? 'selected' : '' }}>Semua Status</option>
                        <option value="Selesai" {{ ($statusFilter ?? '') === 'Selesai' ? 'selected' : '' }}>Selesai</option>
                        <option value="Diproses" {{ ($statusFilter ?? '') === 'Diproses' ? 'selected' : '' }}>Diproses / Disewa</option>
                        <option value="Pending" {{ ($statusFilter ?? '') === 'Pending' ? 'selected' : '' }}>Menunggu Bayar</option>
                        <option value="Dibatalkan" {{ ($statusFilter ?? '') === 'Dibatalkan' ? 'selected' : '' }}>Dibatalkan</option>
                    </select>
                </div>
                <div class="flex items-center space-x-2">
                    <button type="submit" class="bg-[#0369a1] hover:bg-[#0284c7] text-white font-bold text-xs px-4 py-2 rounded-lg transition shadow-sm flex items-center space-x-1.5">
                        <i class="fas fa-filter"></i> <span>Terapkan</span>
                    </button>
                    @if($startDate || $endDate || $statusFilter)
                    <a href="{{ route('admin.laporan') }}" class="text-slate-500 hover:text-slate-800 text-xs font-semibold px-3 py-2 rounded-lg transition bg-slate-100">
                        Reset
                    </a>
                    @endif
                </div>
            </form>

            <!-- STAT SUMMARY CARDS -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div class="rentify-card p-5 border border-slate-100 bg-white relative overflow-hidden">
                    <div class="absolute top-0 right-0 p-4 opacity-10"><i class="fas fa-coins text-4xl text-[#0369a1]"></i></div>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Komisi Bersih Rentify (5%)</p>
                    <p class="text-2xl font-black text-emerald-600 mt-1">Rp {{ number_format($stats['total_fee_rentify'], 0, ',', '.') }}</p>
                    <p class="text-[11px] text-slate-400 mt-1">Pendapatan riil platform</p>
                </div>
                <div class="rentify-card p-5 border border-slate-100 bg-white relative overflow-hidden">
                    <div class="absolute top-0 right-0 p-4 opacity-10"><i class="fas fa-chart-pie text-4xl text-sky-600"></i></div>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Total Nilai Sewa (Gross)</p>
                    <p class="text-2xl font-black text-slate-800 mt-1">Rp {{ number_format($stats['total_pendapatan_kotor'], 0, ',', '.') }}</p>
                    <p class="text-[11px] text-slate-400 mt-1">{{ $stats['transaksi_selesai'] }} pesanan selesai</p>
                </div>
                <div class="rentify-card p-5 border border-slate-100 bg-white relative overflow-hidden">
                    <div class="absolute top-0 right-0 p-4 opacity-10"><i class="fas fa-truck text-4xl text-indigo-500"></i></div>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Total Ongkos Kirim</p>
                    <p class="text-2xl font-black text-indigo-600 mt-1">Rp {{ number_format($stats['total_ongkir'], 0, ',', '.') }}</p>
                    <p class="text-[11px] text-slate-400 mt-1">Diteruskan ke kurir/vendor</p>
                </div>
                <div class="rentify-card p-5 border border-slate-100 bg-white relative overflow-hidden">
                    <div class="absolute top-0 right-0 p-4 opacity-10"><i class="fas fa-ticket text-4xl text-rose-500"></i></div>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Total Diskon Voucher</p>
                    <p class="text-2xl font-black text-rose-500 mt-1">Rp {{ number_format($stats['total_diskon'], 0, ',', '.') }}</p>
                    <p class="text-[11px] text-slate-400 mt-1">Potongan harga promosi</p>
                </div>
            </div>

            <!-- TABLE ORDERS -->
            <section class="rentify-card border border-slate-200/80 bg-white overflow-hidden">
                <div class="p-5 border-b border-slate-100 bg-slate-50/50 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div>
                        <h3 class="text-sm font-black text-slate-800 uppercase tracking-wider">
                            <i class="fas fa-table-list text-[#0369a1] mr-2"></i>Rincian Mutasi Pesanan ({{ $allOrders->count() }})
                        </h3>
                    </div>
                    <div class="relative w-full md:w-64">
                        <i class="fas fa-search absolute left-3 top-2.5 text-slate-300 text-xs"></i>
                        <input type="text" id="lapSearchInput" onkeyup="filterLaporanTable()" placeholder="Cari invoice / customer / toko..." class="rentify-input w-full pl-8 pr-3 py-1.5 text-xs outline-none focus:border-[#0369a1] rounded-lg border border-slate-200">
                    </div>
                </div>

                <div class="p-0 overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs" id="laporanTable">
                        <thead>
                            <tr class="border-b border-slate-100 bg-slate-50/30 text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                                <th class="p-4 pl-6">Invoice</th>
                                <th class="p-4">Customer</th>
                                <th class="p-4">Toko Vendor</th>
                                <th class="p-4">Total Biaya</th>
                                <th class="p-4">Hak Rentify (5%)</th>
                                <th class="p-4 text-center">Status</th>
                                <th class="p-4 text-center">Cetak Struk</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($allOrders as $t)
                            @php
                                $st = strtolower($t->status ?? '');
                                $ong = (float) ($t->shipping_fee ?? 0);
                                $tot = (float) ($t->total_price ?? 0);
                                $pot = (float) ($t->potongan_voucher ?? 0);
                                if (in_array($st, ['dibatalkan', 'batal', 'cancelled', 'ditolak'])) {
                                    $feeT = 0;
                                } else {
                                    $feeT = \App\Services\TransactionCalculationService::getBreakdownForOrder($t)['rentify_fee'];
                                }
                            @endphp
                            <tr class="hover:bg-slate-50/60 transition lap-row">
                                <td class="p-4 pl-6">
                                    <span class="font-black text-slate-800 text-sm lap-inv">#INV-{{ $t->id }}</span>
                                    <p class="text-[10px] text-slate-400 font-semibold mt-0.5">
                                        {{ $t->created_at ? \Carbon\Carbon::parse($t->created_at)->format('d M Y, H:i') : '-' }}
                                    </p>
                                </td>
                                <td class="p-4">
                                    <p class="font-bold text-slate-800 lap-cust">{{ $t->customer_name ?: $t->customer_account_name ?: 'Pelanggan' }}</p>
                                    <p class="text-[11px] text-slate-400 font-mono">{{ $t->customer_whatsapp ?: $t->customer_email ?: '-' }}</p>
                                </td>
                                <td class="p-4">
                                    <span class="font-semibold text-[#0369a1] lap-vendor">
                                        <i class="fas fa-store text-slate-300 mr-1"></i>{{ $t->vendor_name ?: 'Rentify Official' }}
                                    </span>
                                </td>
                                <td class="p-4">
                                    <span class="font-bold text-slate-800 text-sm block">Rp {{ number_format($tot, 0, ',', '.') }}</span>
                                    <span class="text-[10px] text-slate-400">Ongkir: Rp {{ number_format($ong, 0, ',', '.') }}</span>
                                </td>
                                <td class="p-4 font-black {{ $feeT > 0 ? 'text-emerald-600' : 'text-slate-400' }} text-sm">
                                    Rp {{ number_format($feeT, 0, ',', '.') }}
                                </td>
                                <td class="p-4 text-center">
                                    <span class="px-2.5 py-1 rounded-md text-[10px] font-bold uppercase tracking-wider
                                        {{ in_array($st, ['selesai']) ? 'bg-emerald-100 text-emerald-700' : (in_array($st, ['dibatalkan', 'batal', 'cancelled']) ? 'bg-rose-100 text-rose-700' : 'bg-blue-100 text-sky-700') }}">
                                        {{ $t->status ?? 'Pending' }}
                                    </span>
                                </td>
                                <td class="p-4 text-center">
                                    <a href="{{ route('admin.laporan.pdf', $t->id) }}" target="_blank" class="bg-slate-100 hover:bg-[#e0f2fe] hover:text-[#0369a1] text-slate-600 font-bold px-2.5 py-1.5 rounded-lg text-[11px] transition inline-flex items-center space-x-1">
                                        <i class="fas fa-print"></i> <span>Struk</span>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="py-12 text-center text-slate-400">
                                    <i class="fas fa-folder-open text-3xl text-slate-200 block mb-2"></i>
                                    Tidak ada data pesanan pada periode filter yang dipilih.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </main>

    <script>
        function filterLaporanTable() {
            const query = (document.getElementById('lapSearchInput')?.value || '').toLowerCase();
            document.querySelectorAll('.lap-row').forEach(row => {
                const inv = row.querySelector('.lap-inv')?.innerText.toLowerCase() || '';
                const cust = row.querySelector('.lap-cust')?.innerText.toLowerCase() || '';
                const vendor = row.querySelector('.lap-vendor')?.innerText.toLowerCase() || '';
                row.style.display = (inv.includes(query) || cust.includes(query) || vendor.includes(query)) ? '' : 'none';
            });
        }
    </script>
</body>
</html>
