<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Validasi Vendor - Admin Rentify</title>
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
                <a href="{{ route('admin.laporan') }}" class="flex items-center space-x-3 px-4 py-3 text-sm font-semibold text-slate-600 hover:bg-slate-50 hover:text-slate-900 rounded-xl transition duration-200">
                    <i class="fas fa-chart-line text-slate-400 w-5"></i>
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

                <div class="pt-4 px-4 pb-2 text-[10px] font-extrabold text-slate-400 uppercase tracking-widest">Manajemen Master Data</div>
                <a href="{{ route('admin.vendors.validation') }}" class="flex items-center space-x-3 px-4 py-3 text-sm font-bold bg-[#e0f2fe] text-[#0369a1] rounded-xl transition duration-200">
                    <i class="fas fa-user-shield text-[#0369a1] w-5"></i>
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
                <h2 class="text-lg font-black text-slate-800 tracking-tight">Validasi Pendaftaran Mitra Vendor</h2>
                <span class="text-[10px] font-bold bg-sky-50 text-sky-700 border border-sky-200 px-2.5 py-0.5 rounded-full flex items-center shadow-sm">
                    <i class="fas fa-shield-halved mr-1 text-[#0369a1]"></i> Moderasi Keamanan
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

            <!-- STAT SUMMARY CARDS -->
            @php
                $pendingCount = $vendors->where('vendor_status', 'pending')->count();
                $approvedCount = $vendors->where('vendor_status', 'approved')->count();
                $rejectedCount = $vendors->where('vendor_status', 'rejected')->count();
            @endphp
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="rentify-card p-5 border border-slate-100 bg-white relative overflow-hidden">
                    <div class="absolute top-0 right-0 p-4 opacity-10"><i class="fas fa-clock text-4xl text-amber-500"></i></div>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Menunggu Validasi</p>
                    <p class="text-2xl font-black text-amber-500 mt-1">{{ $pendingCount }}</p>
                    <p class="text-[11px] text-slate-400 mt-1">Perlu tindakan persetujuan admin</p>
                </div>
                <div class="rentify-card p-5 border border-slate-100 bg-white relative overflow-hidden">
                    <div class="absolute top-0 right-0 p-4 opacity-10"><i class="fas fa-store text-4xl text-[#0369a1]"></i></div>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Mitra Disetujui</p>
                    <p class="text-2xl font-black text-[#0369a1] mt-1">{{ $approvedCount }}</p>
                    <p class="text-[11px] text-slate-400 mt-1">Toko aktif di platform</p>
                </div>
                <div class="rentify-card p-5 border border-slate-100 bg-white relative overflow-hidden">
                    <div class="absolute top-0 right-0 p-4 opacity-10"><i class="fas fa-user-xmark text-4xl text-rose-500"></i></div>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Ditolak / Banned</p>
                    <p class="text-2xl font-black text-rose-500 mt-1">{{ $rejectedCount + $vendors->where('vendor_status', 'suspended')->count() }}</p>
                    <p class="text-[11px] text-slate-400 mt-1">Akses dinonaktifkan</p>
                </div>
            </div>

            <!-- TABLE VENDOR -->
            <section class="rentify-card border border-slate-200/80 bg-white overflow-hidden">
                <div class="p-5 border-b border-slate-100 bg-slate-50/50 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div>
                        <h3 class="text-sm font-black text-slate-800 uppercase tracking-wider">
                            <i class="fas fa-user-check text-[#0369a1] mr-2"></i>Daftar Pengajuan Kemitraan Vendor
                        </h3>
                        <p class="text-xs text-slate-500 font-medium mt-0.5">Verifikasi identitas dan toko sebelum menyetujui kemitraan.</p>
                    </div>
                    <div class="relative w-full md:w-64">
                        <i class="fas fa-search absolute left-3 top-2.5 text-slate-300 text-xs"></i>
                        <input type="text" id="vendorSearchInput" onkeyup="filterVendorTable()" placeholder="Cari toko atau pemilik..." class="rentify-input w-full pl-8 pr-3 py-1.5 text-xs outline-none focus:border-[#0369a1] rounded-lg border border-slate-200">
                    </div>
                </div>

                <div class="p-0 overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs" id="vendorTable">
                        <thead>
                            <tr class="border-b border-slate-100 bg-slate-50/30 text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                                <th class="p-4 pl-6">Profil Toko & Pemilik</th>
                                <th class="p-4">Kontak (Email / WA)</th>
                                <th class="p-4">Tanggal Daftar</th>
                                <th class="p-4 text-center">Status</th>
                                <th class="p-4 text-center">Aksi Keputusan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($vendors as $vendor)
                            <tr class="hover:bg-slate-50/60 transition vendor-row">
                                <td class="p-4 pl-6">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-[#e0f2fe] to-[#bae6fd] text-[#0369a1] flex items-center justify-center font-black text-sm shrink-0">
                                            <i class="fas fa-store"></i>
                                        </div>
                                        <div>
                                            <a href="{{ route('admin.vendors.impersonate', $vendor->id) }}" class="font-bold text-slate-800 text-sm hover:text-[#0369a1] hover:underline flex items-center gap-1.5 vendor-shop" title="Masuk ke Dashboard Toko Ini">
                                                <span>{{ $vendor->vendor_name ?? '-' }}</span>
                                                <i class="fas fa-arrow-up-right-from-square text-[10px] text-[#0369a1]"></i>
                                            </a>
                                            <p class="text-[11px] text-slate-500 vendor-owner"><i class="far fa-user text-slate-400 mr-1"></i>{{ $vendor->name }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="p-4">
                                    <p class="font-mono text-slate-600 text-xs vendor-email">{{ $vendor->email }}</p>
                                    <p class="text-emerald-600 font-bold text-[11px] mt-0.5">
                                        <i class="fab fa-whatsapp mr-1"></i>{{ $vendor->whatsapp_vendor ?? '-' }}
                                    </p>
                                </td>
                                <td class="p-4 text-slate-500 text-[11px]">
                                    {{ $vendor->created_at ? $vendor->created_at->format('d M Y, H:i') : '-' }}
                                </td>
                                <td class="p-4 text-center">
                                    @if(($vendor->vendor_status ?? 'pending') === 'approved')
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 uppercase tracking-wider shadow-sm">
                                            <i class="fas fa-check-circle mr-1"></i> Disetujui
                                        </span>
                                    @elseif(($vendor->vendor_status ?? 'pending') === 'rejected')
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200 uppercase tracking-wider shadow-sm">
                                            <i class="fas fa-times-circle mr-1"></i> Ditolak
                                        </span>
                                    @elseif(($vendor->vendor_status ?? 'pending') === 'suspended')
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[10px] font-bold bg-rose-100 text-rose-800 uppercase tracking-wider shadow-sm">
                                            <i class="fas fa-lock mr-1"></i> Banned
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200 uppercase tracking-wider shadow-sm">
                                            <i class="fas fa-clock mr-1 animate-pulse"></i> Tertunda
                                        </span>
                                    @endif
                                </td>
                                <td class="p-4 text-center">
                                    <div class="flex items-center justify-center space-x-2">
                                        <a href="{{ route('admin.vendors.impersonate', $vendor->id) }}" class="bg-[#0369a1] hover:bg-sky-700 text-white font-bold px-3 py-1.5 rounded-lg text-xs shadow-sm transition flex items-center gap-1.5 active:scale-95 whitespace-nowrap" title="Masuk ke Dashboard Toko Ini">
                                            <i class="fas fa-door-open"></i> Masuk Toko
                                        </a>
                                        @if(($vendor->vendor_status ?? 'pending') === 'pending')
                                            <!-- Setujui -->
                                            <form action="{{ route('admin.vendors.approve-validation', $vendor->id) }}" method="POST" onsubmit="return confirm('Setujui pendaftaran Vendor {{ $vendor->vendor_name }}?')">
                                                @csrf
                                                <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold px-3 py-1.5 rounded-lg text-xs shadow-sm transition flex items-center">
                                                    <i class="fas fa-check mr-1"></i> Setuju
                                                </button>
                                            </form>
                                            <!-- Tolak -->
                                            <form action="{{ route('admin.vendors.reject', $vendor->id) }}" method="POST" onsubmit="return confirm('Tolak pendaftaran Vendor {{ $vendor->vendor_name }}?')">
                                                @csrf
                                                <button type="submit" class="bg-rose-500 hover:bg-rose-600 text-white font-bold px-3 py-1.5 rounded-lg text-xs shadow-sm transition flex items-center">
                                                    <i class="fas fa-times mr-1"></i> Tolak
                                                </button>
                                            </form>
                                        @elseif($vendor->vendor_status === 'approved')
                                            <span class="text-emerald-600 font-bold text-xs flex items-center justify-center bg-emerald-50 border border-emerald-200 px-2.5 py-1 rounded-lg">
                                                <i class="fas fa-badge-check mr-1"></i> Mitra Sah
                                            </span>
                                        @elseif($vendor->vendor_status === 'rejected')
                                            <form action="{{ route('admin.vendors.approve-validation', $vendor->id) }}" method="POST" onsubmit="return confirm('Pertimbangkan kembali & setujui Vendor {{ $vendor->vendor_name }}?')">
                                                @csrf
                                                <button type="submit" class="bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold px-3 py-1 rounded-lg text-xs transition">
                                                    <i class="fas fa-rotate-left mr-1"></i> Buka Akses
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-slate-400 font-bold text-xs">-</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="py-12 text-center text-slate-400">
                                    <i class="fas fa-store-slash text-3xl text-slate-200 block mb-2"></i>
                                    Belum ada data pendaftaran vendor.
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
        function filterVendorTable() {
            const query = document.getElementById('vendorSearchInput').value.toLowerCase();
            const rows = document.querySelectorAll('.vendor-row');
            rows.forEach(row => {
                const shop = row.querySelector('.vendor-shop')?.innerText.toLowerCase() || '';
                const owner = row.querySelector('.vendor-owner')?.innerText.toLowerCase() || '';
                const email = row.querySelector('.vendor-email')?.innerText.toLowerCase() || '';
                if (shop.includes(query) || owner.includes(query) || email.includes(query)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }
    </script>
</body>
</html>