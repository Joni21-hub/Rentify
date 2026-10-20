<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Pembayaran - Admin Rentify</title>
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
                <a href="{{ route('admin.pembayaran') }}" class="flex items-center space-x-3 px-4 py-3 text-sm font-bold bg-[#e0f2fe] text-[#0369a1] rounded-xl transition duration-200">
                    <i class="fas fa-credit-card text-[#0369a1] w-5"></i>
                    <span>Verifikasi Pembayaran</span>
                </a>
                <a href="/admin/dashboard" class="flex items-center space-x-3 px-4 py-3 text-sm font-semibold text-slate-600 hover:bg-slate-50 hover:text-slate-900 rounded-xl transition duration-200">
                    <i class="fas fa-file-invoice-dollar text-slate-400 w-5"></i>
                    <span>Data Transaksi</span>
                </a>
                <a href="/admin/dashboard" class="flex items-center space-x-3 px-4 py-3 text-sm font-semibold text-slate-600 hover:bg-slate-50 hover:text-slate-900 rounded-xl transition duration-200">
                    <i class="fas fa-ticket-alt text-slate-400 w-5"></i>
                    <span>Monitor Voucher</span>
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
                <h2 class="text-lg font-black text-slate-800 tracking-tight">Pusat Verifikasi & Log Pembayaran</h2>
                <span class="text-[10px] font-bold bg-sky-50 text-sky-700 border border-sky-200 px-2.5 py-0.5 rounded-full flex items-center shadow-sm">
                    <i class="fas fa-receipt mr-1 text-[#0369a1]"></i> Gateway & Mutasi
                </span>
            </div>
            <div class="text-xs font-bold text-slate-500 flex items-center space-x-2 bg-slate-50 px-3 py-1.5 rounded-lg border border-slate-100">
                <i class="far fa-calendar-alt text-[#0369a1]"></i>
                <span>{{ date('d M Y') }}</span>
            </div>
        </header>

        <div class="flex-1 overflow-y-auto p-8 space-y-6">
            
            @if(session('success'))
            <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl text-sm font-medium flex items-center shadow-sm">
                <i class="fas fa-check-circle text-emerald-500 mr-2 text-base"></i>{{ session('success') }}
            </div>
            @endif

            @if(session('error'))
            <div class="bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 rounded-xl text-sm font-medium flex items-center shadow-sm">
                <i class="fas fa-exclamation-circle text-rose-500 mr-2 text-base"></i>{{ session('error') }}
            </div>
            @endif

            <!-- STAT SUMMARY -->
            @php
                $totalLunas = $pembayarans->where('status', 'Lunas')->sum('jumlah');
                $countLunas = $pembayarans->where('status', 'Lunas')->count();
                $countPending = $pembayarans->where('status', '!=', 'Lunas')->where('status', '!=', 'Refunded')->count();
            @endphp
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="rentify-card p-5 border border-slate-100 bg-white relative overflow-hidden">
                    <div class="absolute top-0 right-0 p-4 opacity-10"><i class="fas fa-sack-dollar text-4xl text-emerald-600"></i></div>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Total Dana Terbayar (Lunas)</p>
                    <p class="text-2xl font-black text-emerald-600 mt-1">Rp {{ number_format($totalLunas, 0, ',', '.') }}</p>
                    <p class="text-[11px] text-slate-400 mt-1">{{ $countLunas }} transaksi terverifikasi</p>
                </div>
                <div class="rentify-card p-5 border border-slate-100 bg-white relative overflow-hidden">
                    <div class="absolute top-0 right-0 p-4 opacity-10"><i class="fas fa-clock text-4xl text-amber-500"></i></div>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Menunggu Konfirmasi</p>
                    <p class="text-2xl font-black text-amber-500 mt-1">{{ $countPending }}</p>
                    <p class="text-[11px] text-slate-400 mt-1">Verifikasi manual jika diperlukan</p>
                </div>
                <div class="rentify-card p-5 border border-slate-100 bg-white relative overflow-hidden">
                    <div class="absolute top-0 right-0 p-4 opacity-10"><i class="fas fa-credit-card text-4xl text-[#0369a1]"></i></div>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Total Mutasi Pembayaran</p>
                    <p class="text-2xl font-black text-slate-800 mt-1">{{ $pembayarans->count() }}</p>
                    <p class="text-[11px] text-slate-400 mt-1">Terekam di sistem Rentify</p>
                </div>
            </div>

            <!-- TABLE PEMBAYARAN -->
            <section class="rentify-card border border-slate-200/80 bg-white overflow-hidden">
                <div class="p-5 border-b border-slate-100 bg-slate-50/50 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div>
                        <h3 class="text-sm font-black text-slate-800 uppercase tracking-wider">
                            <i class="fas fa-list-check text-[#0369a1] mr-2"></i>Daftar Mutasi Pembayaran Masuk
                        </h3>
                        <p class="text-xs text-slate-500 font-medium mt-0.5">Pantau status transaksi dari GoPay, QRIS, Midtrans & DOKU Payment Gateway.</p>
                    </div>
                    <div class="relative w-full md:w-64">
                        <i class="fas fa-search absolute left-3 top-2.5 text-slate-300 text-xs"></i>
                        <input type="text" id="paySearchInput" onkeyup="filterPayTable()" placeholder="Cari invoice / pelanggan / metode..." class="rentify-input w-full pl-8 pr-3 py-1.5 text-xs outline-none focus:border-[#0369a1] rounded-lg border border-slate-200">
                    </div>
                </div>

                <div class="p-0 overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs" id="payTable">
                        <thead>
                            <tr class="border-b border-slate-100 bg-slate-50/30 text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                                <th class="p-4 pl-6">Invoice & Pesanan</th>
                                <th class="p-4">Customer</th>
                                <th class="p-4">Metode Bayar</th>
                                <th class="p-4">Nominal Tagihan</th>
                                <th class="p-4">Waktu Terbayar</th>
                                <th class="p-4 text-center">Status</th>
                                <th class="p-4 text-center">Aksi Admin</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($pembayarans as $p)
                            <tr class="hover:bg-slate-50/60 transition pay-row">
                                <td class="p-4 pl-6">
                                    <span class="font-black text-slate-800 text-sm pay-inv">#INV-{{ $p->order_id ?? $p->id }}</span>
                                    <p class="text-[10px] text-slate-400 font-semibold mt-0.5">ID Log: #PAY-{{ $p->id }}</p>
                                </td>
                                <td class="p-4">
                                    <p class="font-bold text-slate-800 pay-cust">{{ $p->customer_name ?? 'Pelanggan' }}</p>
                                    <p class="text-[11px] text-slate-400 font-mono">{{ $p->customer_whatsapp ?? $p->customer_email ?? '-' }}</p>
                                </td>
                                <td class="p-4">
                                    <span class="px-2.5 py-1 rounded-lg text-[10px] font-extrabold uppercase bg-sky-50 text-sky-700 border border-sky-100 pay-method">
                                        {{ $p->metode ?? 'Online' }}
                                    </span>
                                </td>
                                <td class="p-4 font-black text-slate-800 text-sm">
                                    Rp {{ number_format($p->jumlah, 0, ',', '.') }}
                                </td>
                                <td class="p-4 text-slate-500 text-[11px]">
                                    {{ $p->paid_at ? $p->paid_at->format('d M Y, H:i') : ($p->created_at ? $p->created_at->format('d M Y, H:i') : '-') }}
                                </td>
                                <td class="p-4 text-center">
                                    @if(strtolower($p->status) === 'lunas')
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 uppercase tracking-wider shadow-sm">
                                            <i class="fas fa-check-circle mr-1"></i> Lunas
                                        </span>
                                    @elseif(strtolower($p->status) === 'refunded')
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200 uppercase tracking-wider shadow-sm">
                                            <i class="fas fa-rotate-left mr-1"></i> Refunded
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200 uppercase tracking-wider shadow-sm">
                                            <i class="fas fa-clock mr-1 animate-pulse"></i> {{ $p->status }}
                                        </span>
                                    @endif
                                </td>
                                <td class="p-4 text-center">
                                    <div class="flex items-center justify-center space-x-2">
                                        @if(strtolower($p->status) !== 'lunas')
                                            <form action="{{ route('admin.pembayaran.verify', $p->id) }}" method="POST" onsubmit="return confirm('Verifikasi pembayaran ini secara manual?')">
                                                @csrf @method('PATCH')
                                                <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold px-2.5 py-1.5 rounded-lg text-[11px] shadow-sm transition">
                                                    <i class="fas fa-check mr-1"></i> Verifikasi
                                                </button>
                                            </form>
                                        @endif
                                        @if(strtolower($p->status) === 'lunas')
                                            <form action="{{ route('admin.pembayaran.refund', $p->id) }}" method="POST" onsubmit="return confirm('Peringatan: Ubah status pembayaran ini menjadi Refunded?')">
                                                @csrf @method('PATCH')
                                                <button type="submit" class="bg-slate-100 hover:bg-rose-50 hover:text-rose-600 text-slate-500 font-bold px-2.5 py-1.5 rounded-lg text-[11px] transition">
                                                    <i class="fas fa-undo mr-1"></i> Refund
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="py-12 text-center text-slate-400">
                                    <i class="fas fa-receipt text-3xl text-slate-200 block mb-2"></i>
                                    Belum ada data transaksi pembayaran yang tercatat.
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
        function filterPayTable() {
            const query = (document.getElementById('paySearchInput')?.value || '').toLowerCase();
            document.querySelectorAll('.pay-row').forEach(row => {
                const inv = row.querySelector('.pay-inv')?.innerText.toLowerCase() || '';
                const cust = row.querySelector('.pay-cust')?.innerText.toLowerCase() || '';
                const method = row.querySelector('.pay-method')?.innerText.toLowerCase() || '';
                row.style.display = (inv.includes(query) || cust.includes(query) || method.includes(query)) ? '' : 'none';
            });
        }
    </script>
</body>
</html>
