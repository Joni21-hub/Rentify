<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Pusat Kendali - Admin Rentify (Super Admin God-Mode)</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="{{ asset('rentify-theme.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .tab-content { animation: fadeIn 0.3s ease-in-out; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</head>
<body class="text-slate-800 flex h-screen overflow-hidden bg-slate-50">

    <aside class="w-64 bg-white border-r border-slate-200 flex flex-col justify-between shadow-sm z-10 overflow-y-auto">
        <div>
            <div class="p-6 border-b border-slate-100 flex items-center space-x-3 bg-gradient-to-r from-[#e0f2fe] to-[#bae6fd]">
                <div class="w-10 h-10 rentify-card flex items-center justify-center p-1 overflow-hidden border border-sky-100">
                    <img src="{{ asset('images/logo.png') }}" alt="Logo" class="w-full h-full object-contain" onerror="this.outerHTML='<i class=\'fas fa-briefcase text-sky-600 text-lg\'></i>'">
                </div>
                <div>
                    <h1 class="font-black text-slate-800 tracking-tight text-[15px]">Rentify Admin</h1>
                    <p class="text-[10px] text-[#0369a1] font-bold uppercase tracking-wider">Super God-Mode</p>
                </div>
            </div>
            
            <nav class="p-4 space-y-1">
                <button onclick="switchTab('tab-dashboard', 'Pusat Kendali Sistem Utama')" class="w-full flex items-center space-x-3 px-4 py-3 text-sm font-bold bg-[#e0f2fe] text-[#0369a1] rounded-xl transition duration-200 focus:outline-none menu-btn" id="btn-tab-dashboard">
                    <i class="fas fa-chart-pie text-base w-5"></i>
                    <span>Tinjauan Dasbor</span>
                </button>

                <!-- PUSAT NOTIFIKASI & FEED MASUK -->
                <button onclick="switchTab('tab-feed', 'Pusat Notifikasi & Feed Aktivitas Vendor')" class="w-full flex items-center justify-between px-4 py-3 text-sm font-semibold text-slate-600 hover:bg-slate-50 hover:text-slate-900 rounded-xl transition duration-200 focus:outline-none menu-btn" id="btn-tab-feed">
                    <div class="flex items-center space-x-3">
                        <i class="fas fa-bell text-slate-400 w-5"></i>
                        <span>Feed Info Masuk</span>
                    </div>
                    @if(($stats['total_unread_alerts'] ?? 0) > 0)
                    <span class="bg-rose-500 text-white text-[10px] font-black px-2 py-0.5 rounded-full shadow-xs">{{ $stats['total_unread_alerts'] }}</span>
                    @endif
                </button>

                <div class="pt-4 px-4 pb-2 text-[10px] font-extrabold text-slate-400 uppercase tracking-widest">Keuangan & Pesanan</div>
                <a href="{{ route('admin.penarikan.index') }}" class="flex items-center justify-between px-4 py-3 text-sm font-semibold text-slate-600 hover:bg-slate-50 hover:text-slate-900 rounded-xl transition duration-200">
                    <div class="flex items-center space-x-3">
                        <i class="fas fa-money-bill-transfer text-slate-400 w-5"></i>
                        <span>Pencairan Dana</span>
                    </div>
                    @if(($stats['total_pending_withdrawals'] ?? 0) > 0)
                    <span class="bg-emerald-100 text-emerald-800 text-[10px] font-bold px-2 py-0.5 rounded-full">{{ $stats['total_pending_withdrawals'] }}</span>
                    @endif
                </a>
                <a href="{{ route('admin.pembayaran') }}" class="flex items-center space-x-3 px-4 py-3 text-sm font-semibold text-slate-600 hover:bg-slate-50 hover:text-slate-900 rounded-xl transition duration-200">
                    <i class="fas fa-credit-card text-slate-400 w-5"></i>
                    <span>Verifikasi Pembayaran</span>
                </a>
                <a href="{{ route('admin.laporan') }}" class="flex items-center space-x-3 px-4 py-3 text-sm font-semibold text-slate-600 hover:bg-slate-50 hover:text-slate-900 rounded-xl transition duration-200">
                    <i class="fas fa-chart-line text-slate-400 w-5"></i>
                    <span>Laporan Keuangan</span>
                </a>
                <button onclick="switchTab('tab-transaksi', 'Manajemen Transaksi Pesanan')" class="w-full flex items-center space-x-3 px-4 py-3 text-sm font-semibold text-slate-600 hover:bg-slate-50 hover:text-slate-900 rounded-xl transition duration-200 focus:outline-none menu-btn" id="btn-tab-transaksi">
                    <i class="fas fa-file-invoice-dollar text-slate-400 w-5"></i>
                    <span>Data Transaksi</span>
                </button>
                <button onclick="switchTab('tab-voucher', 'Pemantauan Keamanan Promo & Voucher')" class="w-full flex items-center space-x-3 px-4 py-3 text-sm font-semibold text-slate-600 hover:bg-slate-50 hover:text-slate-900 rounded-xl transition duration-200 focus:outline-none menu-btn" id="btn-tab-voucher">
                    <i class="fas fa-ticket-alt text-slate-400 w-5"></i>
                    <span>Monitor Voucher</span>
                </button>

                <div class="pt-4 px-4 pb-2 text-[10px] font-extrabold text-slate-400 uppercase tracking-widest">Komunikasi & CS</div>
                <a href="{{ route('admin.whatsapp.index') }}" class="flex items-center justify-between px-4 py-3 text-sm font-semibold text-slate-600 hover:bg-slate-50 hover:text-slate-900 rounded-xl transition duration-200">
                    <div class="flex items-center space-x-3">
                        <i class="fa-brands fa-whatsapp text-emerald-600 w-5 text-base"></i>
                        <span>WhatsApp Gateway</span>
                    </div>
                    <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-emerald-100 text-emerald-800">Direct</span>
                </a>

                <div class="pt-4 px-4 pb-2 text-[10px] font-extrabold text-slate-400 uppercase tracking-widest">Manajemen Master Data (God-Mode)</div>
                <a href="{{ route('admin.vendors.validation') }}" class="flex items-center justify-between px-4 py-3 text-sm font-semibold text-slate-600 hover:bg-slate-50 hover:text-slate-900 rounded-xl transition duration-200">
                    <div class="flex items-center space-x-3">
                        <i class="fas fa-user-shield text-slate-400 w-5"></i>
                        <span>Validasi Vendor</span>
                    </div>
                    @if(($stats['total_pending_vendor'] ?? 0) > 0)
                    <span class="bg-amber-100 text-amber-800 text-[10px] font-bold px-2 py-0.5 rounded-full">{{ $stats['total_pending_vendor'] }}</span>
                    @endif
                </a>
                <button onclick="switchTab('tab-produk', 'Master Data: Seluruh Produk (God-Mode)')" class="w-full flex items-center justify-between px-4 py-3 text-sm font-semibold text-slate-600 hover:bg-slate-50 hover:text-slate-900 rounded-xl transition duration-200 focus:outline-none menu-btn" id="btn-tab-produk">
                    <div class="flex items-center space-x-3">
                        <i class="fas fa-boxes text-slate-400 w-5"></i>
                        <span>Inventaris Produk</span>
                    </div>
                    <span class="bg-sky-100 text-sky-800 text-[10px] font-bold px-2 py-0.5 rounded-full">{{ $allBarangs->count() }}</span>
                </button>
                <button onclick="switchTab('tab-vendor', 'Master Data: Seluruh Mitra Vendor')" class="w-full flex items-center space-x-3 px-4 py-3 text-sm font-semibold text-slate-600 hover:bg-slate-50 hover:text-slate-900 rounded-xl transition duration-200 focus:outline-none menu-btn" id="btn-tab-vendor">
                    <i class="fas fa-store text-slate-400 w-5"></i>
                    <span>Mitra Vendor</span>
                </button>
                <button onclick="switchTab('tab-customer', 'Master Data: Seluruh Pelanggan')" class="w-full flex items-center space-x-3 px-4 py-3 text-sm font-semibold text-slate-600 hover:bg-slate-50 hover:text-slate-900 rounded-xl transition duration-200 focus:outline-none menu-btn" id="btn-tab-customer">
                    <i class="fas fa-users text-slate-400 w-5"></i>
                    <span>Data Pelanggan</span>
                </button>
            </nav>
        </div>

        <div class="p-4 border-t border-slate-100">
            <div class="flex items-center justify-between p-3 bg-slate-50 rounded-xl border border-slate-100 mb-2">
                <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 bg-[#0369a1] rounded-lg flex items-center justify-center text-white font-bold text-xs shadow-md">AD</div>
                    <div class="truncate">
                        <h4 class="text-xs font-bold text-slate-800 truncate">Super Admin</h4>
                        <p class="text-[9px] text-slate-400 truncate">Otoritas Penuh</p>
                    </div>
                </div>
                <form action="/logout" method="POST">
                    @csrf
                    <button type="submit" class="text-rose-500 hover:text-rose-700 hover:bg-rose-50 p-2 rounded-lg transition" title="Keluar">
                        <i class="fas fa-sign-out-alt"></i>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <main class="flex-1 flex flex-col overflow-hidden">
        
        <!-- HEADER TOPBAR -->
        <header class="rentify-navbar h-16 px-8 flex items-center justify-between z-20 border-b border-slate-200 bg-white">
            <div class="flex items-center space-x-3">
                <h2 id="header-title" class="text-lg font-black text-slate-800 tracking-tight">Pusat Kendali Sistem Utama</h2>
                <span class="text-[10px] font-bold bg-emerald-50 text-emerald-600 border border-emerald-200 px-2.5 py-0.5 rounded-full flex items-center shadow-xs">
                    <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full mr-1.5 animate-pulse"></span> Mode Admin God-Mode
                </span>
            </div>

            <div class="flex items-center space-x-3">
                <!-- NOTIFICATION BELL WITH DROPDOWN -->
                <div class="relative">
                    <button id="notifBellBtn" onclick="toggleNotifDropdown()" class="relative p-2.5 bg-slate-50 hover:bg-slate-100 text-slate-600 rounded-xl border border-slate-200 transition focus:outline-none flex items-center justify-center">
                        <i class="far fa-bell text-base"></i>
                        @if(($stats['total_unread_alerts'] ?? 0) > 0)
                        <span class="absolute -top-1 -right-1 bg-rose-500 text-white text-[10px] font-black w-5 h-5 rounded-full flex items-center justify-center animate-bounce shadow-md">
                            {{ $stats['total_unread_alerts'] > 99 ? '99+' : $stats['total_unread_alerts'] }}
                        </span>
                        @endif
                    </button>

                    <!-- DROPDOWN CONTENT -->
                    <div id="notifDropdown" class="hidden absolute right-0 mt-2 w-96 bg-white rounded-2xl shadow-2xl border border-slate-200 z-50 overflow-hidden text-xs">
                        <div class="p-4 bg-gradient-to-r from-sky-50 to-blue-50 border-b border-slate-200/80 flex items-center justify-between">
                            <div class="flex items-center space-x-2">
                                <i class="fas fa-bell text-[#0369a1]"></i>
                                <span class="font-black text-slate-800 text-sm">Informasi Masuk Vendor</span>
                            </div>
                            <span class="text-[10px] font-bold bg-[#0369a1] text-white px-2 py-0.5 rounded-full">{{ $stats['total_unread_alerts'] ?? 0 }} Pending</span>
                        </div>

                        <div class="max-h-80 overflow-y-auto divide-y divide-slate-100">
                            @forelse($activityAlerts->take(8) as $alert)
                            <div class="p-3.5 hover:bg-slate-50 transition flex items-start space-x-3">
                                <div class="w-8 h-8 rounded-xl {{ $alert['icon_bg'] }} flex items-center justify-center shrink-0 mt-0.5">
                                    <i class="fas {{ $alert['icon'] }} text-xs"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between mb-0.5">
                                        <span class="text-[9px] font-black px-1.5 py-0.5 rounded border {{ $alert['badge_color'] }}">{{ $alert['badge'] }}</span>
                                        <span class="text-[10px] text-slate-400">{{ $alert['time'] ? \Carbon\Carbon::parse($alert['time'])->diffForHumans() : 'Baru saja' }}</span>
                                    </div>
                                    <p class="font-bold text-slate-800 text-xs truncate">{{ $alert['item_name'] }}</p>
                                    <p class="text-[11px] text-slate-500 truncate"><span class="font-semibold">{{ $alert['sender'] }}</span> • {{ $alert['owner'] }}</p>

                                    <div class="mt-2 flex items-center space-x-2">
                                        @if($alert['type'] == 'barang')
                                        <button onclick="closeNotifDropdown(); openComprehensiveProductModal('{{ addslashes((string)(json_encode($alert['payload']) ?: '{}')) }}')" class="px-2.5 py-1 bg-sky-50 hover:bg-sky-100 text-[#0369a1] font-bold text-[10px] rounded-lg transition">
                                            <i class="fas fa-eye mr-1"></i> Inspeksi Lengkap
                                        </button>
                                        @elseif($alert['type'] == 'vendor')
                                        <a href="{{ route('admin.vendors.validation') }}" class="px-2.5 py-1 bg-purple-50 hover:bg-purple-100 text-purple-700 font-bold text-[10px] rounded-lg transition">
                                            <i class="fas fa-user-check mr-1"></i> Validasi Toko
                                        </a>
                                        @elseif($alert['type'] == 'withdrawal')
                                        <a href="{{ route('admin.penarikan.index') }}" class="px-2.5 py-1 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-bold text-[10px] rounded-lg transition">
                                            <i class="fas fa-money-bill-transfer mr-1"></i> Proses Pencairan
                                        </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @empty
                            <div class="p-8 text-center text-slate-400">
                                <i class="fas fa-check-circle text-2xl text-emerald-400 block mb-1"></i>
                                <span>Semua informasi telah diperiksa. Tidak ada antrean baru.</span>
                            </div>
                            @endforelse
                        </div>

                        <div class="p-3 bg-slate-50 border-t border-slate-100 text-center">
                            <button onclick="closeNotifDropdown(); switchTab('tab-feed', 'Pusat Notifikasi & Feed Aktivitas Vendor')" class="text-xs font-bold text-[#0369a1] hover:underline">
                                Buka Seluruh Feed Aktivitas Masuk &rarr;
                            </button>
                        </div>
                    </div>
                </div>

                <div class="text-xs font-bold text-slate-500 flex items-center space-x-2 bg-slate-50 px-3 py-2 rounded-xl border border-slate-200">
                    <i class="far fa-calendar-alt text-[#0369a1]"></i>
                    <span>{{ date('d M Y') }}</span>
                </div>
            </div>
        </header>

        <div class="flex-1 overflow-y-auto p-8">
            
            @if(session('success'))
            <div class="mb-6 bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl text-sm font-medium flex items-center shadow-xs">
                <i class="fas fa-check-circle text-emerald-500 mr-2 text-base"></i>{{ session('success') }}
            </div>
            @endif
            @if(session('error'))
            <div class="mb-6 bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 rounded-xl text-sm font-medium flex items-center shadow-xs">
                <i class="fas fa-exclamation-circle text-rose-500 mr-2 text-base"></i>{{ session('error') }}
            </div>
            @endif

            <!-- TOAST FEEDBACK NOTIFICATION (UNTUK AJAX QUICK STOCK) -->
            <div id="ajaxToast" class="fixed bottom-6 right-6 hidden z-50 bg-slate-900 text-white px-5 py-3.5 rounded-2xl shadow-2xl flex items-center space-x-3 transition transform duration-300">
                <i id="ajaxToastIcon" class="fas fa-check-circle text-emerald-400 text-lg"></i>
                <div>
                    <h5 id="ajaxToastTitle" class="text-xs font-black">Informasi Diperbarui</h5>
                    <p id="ajaxToastMsg" class="text-[11px] text-slate-300 font-medium"></p>
                </div>
            </div>

            <!-- ===================== TAB 1: DASHBOARD ===================== -->
            <div id="tab-dashboard" class="tab-content block space-y-8">

                <!-- ALERT BANNER PEMBERITAHUAN MASUK -->
                @if(($stats['total_unread_alerts'] ?? 0) > 0)
                <div class="bg-gradient-to-r from-sky-600 to-[#0369a1] text-white p-5 rounded-2xl shadow-md flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 rounded-2xl bg-white/10 flex items-center justify-center text-2xl shrink-0 border border-white/20">
                            <i class="fas fa-bell-concierge"></i>
                        </div>
                        <div>
                            <div class="flex items-center space-x-2 mb-1">
                                <span class="bg-amber-400 text-slate-950 text-[10px] font-black px-2 py-0.5 rounded uppercase tracking-wider">Perhatian Admin</span>
                                <span class="text-xs text-sky-200 font-medium">{{ $stats['total_unread_alerts'] }} Aktivitas Baru Menunggu</span>
                            </div>
                            <h3 class="text-base font-black">Terdapat data & pengajuan vendor masuk yang belum diproses!</h3>
                            <p class="text-xs text-sky-100">Kurasi produk baru ({{ $stats['total_pending'] }}), validasi vendor ({{ $stats['total_pending_vendor'] }}), pencairan dana ({{ $stats['total_pending_withdrawals'] }}).</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-2 shrink-0">
                        <button onclick="switchTab('tab-feed', 'Pusat Notifikasi & Feed Aktivitas Vendor')" class="px-4 py-2.5 bg-white text-[#0369a1] font-black text-xs rounded-xl shadow hover:bg-sky-50 transition">
                            <i class="fas fa-list-check mr-1.5"></i> Periksa Semua Feed
                        </button>
                    </div>
                </div>
                @endif

                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <div class="rentify-card p-6 border border-slate-100 relative overflow-hidden bg-white">
                        <div class="absolute top-0 right-0 p-4 opacity-10"><i class="fas fa-boxes text-5xl"></i></div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Produk Pending Kurasi</p>
                        <p class="text-3xl font-black text-amber-500 mt-1">{{ $stats['total_pending'] ?? 0 }}</p>
                        <button onclick="switchTab('tab-produk', 'Master Data: Seluruh Produk (God-Mode)'); filterProdukStatus('pending')" class="text-[10px] font-bold text-amber-600 hover:underline mt-2 inline-block">Kelola di Inventaris &rarr;</button>
                    </div>
                    <div class="rentify-card p-6 border border-slate-100 relative overflow-hidden bg-white">
                        <div class="absolute top-0 right-0 p-4 opacity-10"><i class="fas fa-check-circle text-5xl"></i></div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Produk Aktif Katalog</p>
                        <p class="text-3xl font-black text-[#0369a1] mt-1">{{ $stats['total_disetujui'] ?? 0 }}</p>
                        <button onclick="switchTab('tab-produk', 'Master Data: Seluruh Produk (God-Mode)'); filterProdukStatus('disetujui')" class="text-[10px] font-bold text-sky-600 hover:underline mt-2 inline-block">Lihat Semua Aktif &rarr;</button>
                    </div>
                    <div class="rentify-card p-6 border border-slate-100 relative overflow-hidden bg-white">
                        <div class="absolute top-0 right-0 p-4 opacity-10"><i class="fas fa-store text-5xl"></i></div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Total Mitra Vendor</p>
                        <p class="text-3xl font-black text-slate-800 mt-1">{{ $stats['total_vendor_total'] ?? 0 }}</p>
                        <span class="text-[10px] font-bold text-purple-600 mt-2 inline-block">{{ $stats['total_pending_vendor'] ?? 0 }} butuh validasi</span>
                    </div>
                    <div class="rentify-card p-6 border border-slate-100 relative overflow-hidden bg-white">
                        <div class="absolute top-0 right-0 p-4 opacity-10"><i class="fas fa-users text-5xl"></i></div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Total Pelanggan</p>
                        <p class="text-3xl font-black text-slate-800 mt-1">{{ $stats['total_customer'] ?? 0 }}</p>
                        <span class="text-[10px] font-bold text-slate-400 mt-2 inline-block">Pengguna aktif terdaftar</span>
                    </div>
                </div>

                <!-- ANTREAN KURASI PRODUK (PENGAJUAN DARI VENDOR) -->
                <section class="rentify-card border border-slate-200/80 overflow-hidden bg-white">
                    <div class="p-5 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
                        <div class="flex items-center space-x-2">
                            <h3 class="text-sm font-black text-slate-800 uppercase tracking-wider"><i class="fas fa-clipboard-check text-amber-500 mr-2"></i>Antrean Kurasi Produk Masuk Vendor</h3>
                            <span class="bg-amber-100 text-amber-800 text-[10px] px-2.5 py-0.5 rounded-full font-black">{{ $pendingBarangs->count() }} Butuh Respon</span>
                        </div>
                        <button onclick="switchTab('tab-produk', 'Master Data: Seluruh Produk (God-Mode)')" class="text-xs font-bold text-[#0369a1] hover:underline">
                            Buka Seluruh Inventaris Produk &rarr;
                        </button>
                    </div>
                    <div class="p-0 overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b border-slate-100 text-[10px] font-bold text-slate-400 uppercase tracking-wider bg-white">
                                    <th class="p-4 pl-6">Foto</th>
                                    <th class="p-4">Nama Produk & Kategori</th>
                                    <th class="p-4">Toko Vendor</th>
                                    <th class="p-4">Tarif Sewa & Deposit</th>
                                    <th class="p-4 text-center">Stok</th>
                                    <th class="p-4 text-center">Inspeksi Data</th>
                                    <th class="p-4 text-center">Keputusan Kurasi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-sm">
                                @forelse($pendingBarangs as $barang)
                                <tr class="hover:bg-slate-50/40 transition">
                                    <td class="p-4 pl-6">
                                        <div class="w-12 h-12 rounded-xl border border-slate-200 overflow-hidden bg-slate-100 relative shrink-0">
                                            @if($barang->is_cover_video)
                                            <video src="{{ $barang->cover_photo_url }}" class="w-full h-full object-cover" muted></video>
                                            <span class="absolute top-1 left-1 bg-black/80 text-white text-[8px] font-black px-1 rounded">VID</span>
                                            @else
                                            <img src="{{ $barang->cover_photo_url }}" class="w-full h-full object-cover" onerror="this.src='{{ asset('storage/placeholders/placeholder-barang.png') }}'">
                                            @endif
                                        </div>
                                    </td>
                                    <td class="p-4">
                                        <p class="font-bold text-slate-800 text-sm">{{ $barang->nama }}</p>
                                        <span class="text-[10px] font-bold bg-slate-100 text-slate-600 px-2 py-0.5 rounded">{{ $barang->kategori?->nama ?? 'Tanpa Kategori' }}</span>
                                    </td>
                                    <td class="p-4 text-slate-600">
                                        <p class="font-bold text-[#0369a1]"><i class="fas fa-store text-slate-300 mr-1"></i> {{ $barang->vendor?->vendor_name ?? 'Umum' }}</p>
                                        <p class="text-[10px] text-slate-400">{{ $barang->vendor?->name }} • {{ $barang->vendor?->whatsapp_vendor ?? '-' }}</p>
                                    </td>
                                    <td class="p-4">
                                        <p class="font-black text-slate-800 text-xs">Rp {{ number_format($barang->harga_sewa_harian, 0, ',', '.') }} <span class="text-[10px] text-slate-400 font-normal">/hari</span></p>
                                        <p class="text-[10px] text-emerald-600 font-bold">Deposit: Rp {{ number_format($barang->deposit, 0, ',', '.') }}</p>
                                    </td>
                                    <td class="p-4 text-center font-black text-slate-800">
                                        {{ $barang->stok_total }} unit
                                    </td>
                                    <td class="p-4 text-center">
                                        <button onclick="openComprehensiveProductModal('{{ addslashes((string)(json_encode($barang) ?: '{}')) }}')" class="bg-sky-50 hover:bg-sky-100 text-[#0369a1] font-bold px-3 py-1.5 rounded-lg text-xs transition border border-sky-100 shadow-xs">
                                            <i class="fas fa-eye mr-1"></i> Inspeksi 100%
                                        </button>
                                    </td>
                                    <td class="p-4 text-center">
                                        <div class="flex items-center justify-center space-x-1.5">
                                            <form action="/admin/barang/{{ $barang->id }}/approve" method="POST">
                                                @csrf @method('PATCH')
                                                <button type="submit" class="bg-emerald-500 hover:bg-emerald-600 text-white font-black px-3 py-1.5 rounded-lg text-xs shadow-xs transition">Setujui</button>
                                            </form>
                                            <form action="/admin/barang/{{ $barang->id }}/reject" method="POST">
                                                @csrf @method('PATCH')
                                                <button type="submit" class="bg-rose-500 hover:bg-rose-600 text-white font-black px-3 py-1.5 rounded-lg text-xs shadow-xs transition">Tolak</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="py-10 text-center text-slate-400 text-xs">
                                        <i class="fas fa-check-double text-3xl text-emerald-300 block mb-2"></i>
                                        Antrean kosong! Seluruh produk vendor sudah ditinjau oleh Admin.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- PENDAFTARAN VENDOR BARU -->
                    <section class="rentify-card border border-slate-200/80 overflow-hidden flex flex-col bg-white">
                        <div class="p-5 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
                            <h3 class="text-sm font-black text-slate-800 uppercase tracking-wider"><i class="fas fa-user-check text-purple-500 mr-2"></i>Pendaftaran Vendor Baru</h3>
                            <a href="{{ route('admin.vendors.validation') }}" class="text-[11px] font-bold text-purple-600 hover:underline">Kelola &rarr;</a>
                        </div>
                        <div class="p-0 overflow-x-auto flex-1">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="border-b border-slate-100 text-[10px] font-bold text-slate-400 uppercase bg-white">
                                        <th class="p-4 pl-6">Nama Toko & Pemilik</th>
                                        <th class="p-4 text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-50 text-xs font-medium text-slate-600">
                                    @forelse($pendingVendors as $vendor)
                                    <tr class="hover:bg-slate-50/50 transition">
                                        <td class="p-4 pl-6">
                                            <a href="{{ route('admin.vendors.impersonate', $vendor->id) }}" class="font-bold text-slate-800 text-sm hover:text-purple-600 hover:underline flex items-center gap-1.5" title="Klik untuk Masuk ke Dashboard Toko Ini">
                                                <span>{{ $vendor->vendor_name ?? '-' }}</span>
                                                <i class="fas fa-door-open text-xs text-purple-500"></i>
                                            </a>
                                            <p class="text-[10px] text-slate-400">{{ $vendor->name }} • {{ $vendor->email }}</p>
                                        </td>
                                        <td class="p-4 text-center space-x-1">
                                            <a href="{{ route('admin.vendors.impersonate', $vendor->id) }}" class="bg-purple-100 hover:bg-purple-200 text-purple-800 p-2 rounded-lg transition inline-block text-xs" title="Masuk & Kelola Toko Ini"><i class="fas fa-door-open"></i></a>
                                            <button onclick="openVendorModal('{{ json_encode($vendor) ?: '{}' }}')" class="bg-purple-50 hover:bg-purple-100 text-purple-700 p-2 rounded-lg transition" title="Lihat Detail"><i class="fas fa-eye"></i></button>
                                            <form action="/admin/vendors/{{ $vendor->id }}/approve-validation" method="POST" class="inline">
                                                @csrf <button type="submit" class="bg-emerald-50 hover:bg-emerald-100 text-emerald-600 p-2 rounded-lg transition" title="Setuju"><i class="fas fa-check"></i></button>
                                            </form>
                                            <form action="/admin/vendors/{{ $vendor->id }}/reject-validation" method="POST" class="inline">
                                                @csrf <button type="submit" class="bg-rose-50 hover:bg-rose-100 text-rose-600 p-2 rounded-lg transition" title="Tolak"><i class="fas fa-times"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="2" class="py-8 text-center text-slate-400 text-xs"><i class="fas fa-coffee text-2xl text-slate-200 block mb-2"></i>Tidak ada vendor mendaftar.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </section>

                    <!-- MANAJEMEN BANNER -->
                    <section class="rentify-card border border-slate-200/80 overflow-hidden flex flex-col bg-white">
                        <div class="p-5 border-b border-slate-100 bg-slate-50/50">
                            <h3 class="text-sm font-black text-slate-800 uppercase tracking-wider"><i class="fas fa-images text-sky-500 mr-2"></i>Manajemen Banner Aplikasi</h3>
                        </div>
                        <div class="p-5 flex-1">
                            <form action="/admin/banner" method="POST" enctype="multipart/form-data" class="flex gap-3 mb-6 bg-slate-50 p-3 rounded-xl border border-slate-100">
                                @csrf
                                <div class="flex-1 space-y-2">
                                    <input type="text" name="judul_promo" placeholder="Judul Banner Promo..." required class="rentify-input w-full px-3 py-2 text-xs outline-none">
                                    <input type="file" name="gambar" required accept="image/*,.heic,.heif,.avif,.webp" class="w-full text-[10px] text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:bg-blue-50 file:text-sky-700 file:font-bold">
                                </div>
                                <button type="submit" class="rentify-btn text-white font-bold px-4 rounded-lg text-xs shadow-sm transition">Upload</button>
                            </form>
                            
                            <div class="grid grid-cols-2 gap-3 max-h-[160px] overflow-y-auto pr-1">
                                @forelse($banners as $banner)
                                <div class="border border-slate-100 rounded-xl overflow-hidden bg-white relative group">
                                    <img src="{{ $banner->gambar_url }}" class="w-full h-20 object-cover">
                                    <div class="absolute inset-0 bg-black/60 flex items-center justify-center opacity-0 group-hover:opacity-100 transition">
                                        <form action="/admin/banner/{{ $banner->id }}" method="POST">
                                            @csrf @method('DELETE')
                                            <button type="submit" onclick="return confirm('Padam banner ini?')" class="rentify-btn-danger text-white px-3 py-1 rounded-md text-xs font-bold"><i class="fas fa-trash mr-1"></i> Hapus</button>
                                        </form>
                                    </div>
                                    <div class="p-2 bg-slate-800 text-white text-[9px] font-bold truncate text-center">{{ $banner->judul_promo }}</div>
                                </div>
                                @empty
                                <div class="col-span-2 text-center text-slate-400 text-xs py-4">Belum ada banner.</div>
                                @endforelse
                            </div>
                        </div>
                    </section>
                </div>
            </div>

            <!-- ===================== TAB FEED: PUSAT NOTIFIKASI & AKTIVITAS MASUK TERPADU ===================== -->
            <div id="tab-feed" class="tab-content hidden space-y-6">
                <section class="rentify-card border border-slate-200/80 overflow-hidden bg-white">
                    <div class="p-6 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div>
                            <div class="flex items-center space-x-2">
                                <h3 class="text-base font-black text-slate-800"><i class="fas fa-bell text-[#0369a1] mr-2"></i>Pusat Informasi & Feed Aktivitas Masuk Vendor</h3>
                                <span class="bg-rose-100 text-rose-800 text-xs font-black px-2.5 py-0.5 rounded-full">{{ $stats['total_unread_alerts'] }} Tertunda</span>
                            </div>
                            <p class="text-xs text-slate-500 mt-1">Seluruh data yang masuk dari vendor (unggah produk, pendaftaran toko, perubahan stok, permintaan pencairan) termonitor lengkap di sini.</p>
                        </div>
                        <div class="flex items-center space-x-2">
                            <span class="text-xs text-slate-400 font-bold">Total Entri: {{ $activityAlerts->count() }}</span>
                        </div>
                    </div>

                    <div class="p-6">
                        <div class="space-y-3">
                            @forelse($activityAlerts as $alert)
                            <div class="p-4 rounded-2xl border border-slate-200/80 bg-white hover:border-sky-300 transition shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
                                <div class="flex items-start space-x-3.5">
                                    <div class="w-10 h-10 rounded-2xl {{ $alert['icon_bg'] }} flex items-center justify-center text-sm shrink-0 border border-slate-100">
                                        <i class="fas {{ $alert['icon'] }}"></i>
                                    </div>
                                    <div>
                                        <div class="flex items-center space-x-2">
                                            <span class="text-[9px] font-black uppercase px-2 py-0.5 rounded border {{ $alert['badge_color'] }}">{{ $alert['badge'] }}</span>
                                            <span class="text-[11px] text-slate-400 font-medium">{{ $alert['time'] ? \Carbon\Carbon::parse($alert['time'])->diffForHumans() : 'Baru saja' }}</span>
                                        </div>
                                        <h4 class="text-sm font-black text-slate-800 mt-1">{{ $alert['item_name'] }}</h4>
                                        <p class="text-xs text-slate-500 mt-0.5">
                                            <i class="fas fa-store text-slate-400 mr-1"></i> Dari: <span class="font-bold text-slate-700">{{ $alert['sender'] }}</span> • Pemilik/Rincian: {{ $alert['owner'] }}
                                        </p>
                                    </div>
                                </div>

                                <div class="flex items-center space-x-2 shrink-0">
                                    @if($alert['type'] == 'barang')
                                    <button onclick="openComprehensiveProductModal('{{ addslashes((string)(json_encode($alert['payload']) ?: '{}')) }}')" class="px-3.5 py-2 bg-sky-50 hover:bg-sky-100 text-[#0369a1] font-bold text-xs rounded-xl transition border border-sky-200 shadow-xs">
                                        <i class="fas fa-eye mr-1.5"></i> Inspeksi Penuh Produk
                                    </button>
                                    <a href="{{ route('admin.barang.edit', $alert['payload']->id) }}" class="px-3.5 py-2 bg-[#0369a1] hover:bg-sky-700 text-white font-bold text-xs rounded-xl transition shadow-xs">
                                        <i class="fas fa-pen-to-square mr-1.5"></i> Edit God-Mode
                                    </a>
                                    @elseif($alert['type'] == 'vendor')
                                    <a href="{{ route('admin.vendors.validation') }}" class="px-3.5 py-2 bg-purple-50 hover:bg-purple-100 text-purple-700 font-bold text-xs rounded-xl transition border border-purple-200">
                                        <i class="fas fa-user-check mr-1.5"></i> Buka Validasi Toko
                                    </a>
                                    @elseif($alert['type'] == 'withdrawal')
                                    <a href="{{ route('admin.penarikan.index') }}" class="px-3.5 py-2 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-bold text-xs rounded-xl transition border border-emerald-200">
                                        <i class="fas fa-money-bill-transfer mr-1.5"></i> Proses Pencairan Dana
                                    </a>
                                    @endif
                                </div>
                            </div>
                            @empty
                            <div class="py-12 text-center text-slate-400 text-sm">
                                <i class="fas fa-shield-cat text-4xl text-slate-300 block mb-2"></i>
                                Belum ada aktivitas atau informasi masuk baru dari vendor. Semua data aman dan terkelola!
                            </div>
                            @endforelse
                        </div>
                    </div>
                </section>
            </div>

            <!-- ===================== TAB 2: INVENTARIS PRODUK (GOD-MODE) ===================== -->
            <div id="tab-produk" class="tab-content hidden space-y-6">
                <section class="rentify-card border border-slate-200/80 overflow-hidden bg-white">
                    <div class="p-6 border-b border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div>
                            <div class="flex items-center space-x-2">
                                <h3 class="text-base font-black text-slate-800"><i class="fas fa-boxes text-[#0369a1] mr-2"></i>Master Inventaris Barang Vendor (God-Mode Controls)</h3>
                                <span class="bg-rose-100 text-rose-800 text-[10px] font-black px-2 py-0.5 rounded uppercase">Akses Penuh Pemilik</span>
                            </div>
                            <p class="text-xs text-slate-500 mt-1">Admin memiliki derajat kendali mutlak: sesuaikan stok barang secara instan, periksa 100% data isian vendor, dan ubah data apapun.</p>
                        </div>
                        
                        <!-- FILTER STATUS PILLS & SEARCH -->
                        <div class="flex flex-col sm:flex-row sm:items-center gap-3">
                            <div class="relative">
                                <i class="fas fa-search absolute left-3 top-2.5 text-slate-300"></i>
                                <input type="text" id="searchProduk" onkeyup="filterProdukTable()" placeholder="Cari nama produk / toko / kategori..." class="rentify-input pl-9 pr-4 py-2 text-xs focus:outline-none w-64">
                            </div>
                            <a href="{{ route('admin.barang.create') }}" class="px-3.5 py-2 bg-[#0369a1] hover:bg-[#0284c7] text-white rounded-xl text-xs font-bold transition flex items-center space-x-1.5 shadow-xs">
                                <i class="fas fa-plus"></i>
                                <span>Tambah Produk</span>
                            </a>
                        </div>
                    </div>

                    <!-- STATUS TABS FILTER -->
                    <div class="px-6 py-3 bg-slate-50/70 border-b border-slate-100 flex flex-wrap items-center gap-2 text-xs">
                        <span class="text-[11px] font-bold text-slate-400 uppercase mr-2">Filter Status:</span>
                        <button onclick="filterProdukStatus('all')" class="produk-filter-btn px-3 py-1 rounded-lg font-bold bg-[#0369a1] text-white transition" data-status="all">Semua ({{ $allBarangs->count() }})</button>
                        <button onclick="filterProdukStatus('disetujui')" class="produk-filter-btn px-3 py-1 rounded-lg font-bold bg-white text-slate-600 hover:bg-slate-200 border border-slate-200 transition" data-status="disetujui">Aktif Katalog ({{ $stats['total_disetujui'] ?? 0 }})</button>
                        <button onclick="filterProdukStatus('pending')" class="produk-filter-btn px-3 py-1 rounded-lg font-bold bg-white text-slate-600 hover:bg-slate-200 border border-slate-200 transition" data-status="pending">Pending Kurasi ({{ $stats['total_pending'] ?? 0 }})</button>
                        <button onclick="filterProdukStatus('ditolak')" class="produk-filter-btn px-3 py-1 rounded-lg font-bold bg-white text-slate-600 hover:bg-slate-200 border border-slate-200 transition" data-status="ditolak">Ditolak ({{ $stats['total_ditolak'] ?? 0 }})</button>
                    </div>

                    <div class="p-0 overflow-x-auto">
                        <table class="w-full text-left border-collapse text-xs" id="produkTable">
                            <thead>
                                <tr class="border-b border-slate-200 bg-slate-50 font-bold text-slate-500 uppercase tracking-wider">
                                    <th class="p-4 pl-6">Foto</th>
                                    <th class="p-4">Nama Produk & Kategori</th>
                                    <th class="p-4">Toko Vendor & Kontak</th>
                                    <th class="p-4">Tarif Sewa / Hari</th>
                                    <th class="p-4 text-center">Kontrol Stok Langsung</th>
                                    <th class="p-4 text-center">Status Katalog</th>
                                    <th class="p-4 text-center">Aksi God-Mode</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse($allBarangs as $p)
                                <tr class="hover:bg-slate-50/70 transition produk-row" data-status="{{ $p->status_barang ?? 'pending' }}" data-stock="{{ $p->stok_total }}">
                                    <td class="p-4 pl-6">
                                        <div class="w-12 h-12 rounded-xl border border-slate-200 overflow-hidden bg-slate-100 relative shrink-0">
                                            @if($p->is_cover_video)
                                            <video src="{{ $p->cover_photo_url }}" class="w-full h-full object-cover" muted></video>
                                            <span class="absolute top-1 left-1 bg-black/80 text-white text-[8px] font-black px-1 rounded">VID</span>
                                            @else
                                            <img src="{{ $p->cover_photo_url }}" class="w-full h-full object-cover" onerror="this.src='{{ asset('storage/placeholders/placeholder-barang.png') }}';">
                                            @endif
                                        </div>
                                    </td>
                                    <td class="p-4">
                                        <p class="font-bold text-slate-800 text-sm max-w-[220px] truncate produk-nama">{{ $p->nama }}</p>
                                        <span class="text-[10px] font-bold bg-slate-100 text-slate-600 px-2 py-0.5 rounded mt-0.5 inline-block produk-kategori">{{ $p->kategori?->nama ?? 'Tanpa Kategori' }}</span>
                                    </td>
                                    <td class="p-4">
                                        @if($p->vendor_id)
                                        <a href="{{ route('admin.vendors.impersonate', $p->vendor_id) }}" class="font-semibold text-[#0369a1] hover:text-sky-700 hover:underline inline-flex items-center gap-1 group produk-vendor" title="Masuk ke Dashboard Toko Ini">
                                            <i class="fas fa-store text-slate-300 group-hover:text-[#0369a1] transition mr-1"></i>
                                            <span>{{ $p->vendor?->vendor_name ?? 'Umum' }}</span>
                                            <i class="fas fa-arrow-up-right-from-square text-[9px] opacity-70"></i>
                                        </a>
                                        @else
                                        <p class="font-semibold text-slate-500 produk-vendor"><i class="fas fa-store text-slate-300 mr-1"></i> Umum</p>
                                        @endif
                                        <div class="flex items-center space-x-2 text-[10px] text-slate-400 mt-0.5">
                                            <span>{{ $p->vendor?->name }}</span>
                                            @if($p->vendor?->whatsapp_vendor)
                                            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $p->vendor->whatsapp_vendor) }}" target="_blank" class="text-emerald-600 font-bold hover:underline" title="Hubungi Vendor via WA">
                                                <i class="fab fa-whatsapp"></i> Chat
                                            </a>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="p-4">
                                        <p class="font-black text-slate-800 text-xs">Rp {{ number_format($p->harga_sewa_harian, 0, ',', '.') }}</p>
                                        <p class="text-[10px] text-slate-400">Deposit: Rp {{ number_format($p->deposit, 0, ',', '.') }}</p>
                                    </td>

                                    <!-- QUICK INLINE-EDIT STOK KONTROL -->
                                    <td class="p-4 text-center">
                                        <div class="inline-flex items-center space-x-1 bg-slate-50 p-1 rounded-xl border border-slate-200">
                                            <button type="button" onclick="adjustQuickStock({{ $p->id }}, -1)" class="w-6 h-6 rounded-lg bg-white hover:bg-slate-200 text-slate-700 font-black text-xs flex items-center justify-center transition shadow-xs">-</button>
                                            <input type="number" id="quick-stock-input-{{ $p->id }}" value="{{ $p->stok_total }}" min="0" class="w-12 text-center font-black text-xs bg-white border border-slate-200 rounded-lg py-1 focus:outline-none focus:border-sky-500">
                                            <button type="button" onclick="adjustQuickStock({{ $p->id }}, 1)" class="w-6 h-6 rounded-lg bg-white hover:bg-slate-200 text-slate-700 font-black text-xs flex items-center justify-center transition shadow-xs">+</button>
                                            <button type="button" onclick="saveQuickStock({{ $p->id }})" title="Simpan Stok Langsung" class="px-2 py-1 bg-[#0369a1] hover:bg-[#0284c7] text-white rounded-lg text-[10px] font-bold shadow-xs transition flex items-center space-x-1">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        </div>
                                        <div id="stock-feedback-{{ $p->id }}" class="text-[9px] font-bold text-emerald-600 hidden mt-1">Tersimpan!</div>
                                    </td>

                                    <td class="p-4 text-center">
                                        <span class="px-2.5 py-1 rounded-md text-[9px] font-black uppercase tracking-widest border {{ $p->status_barang == 'disetujui' ? 'bg-emerald-50 text-emerald-600 border-emerald-200' : ($p->status_barang == 'pending' ? 'bg-amber-50 text-amber-600 border-amber-200' : 'bg-rose-50 text-rose-600 border-rose-200') }}">
                                            {{ $p->status_barang ?? 'pending' }}
                                        </span>
                                        <span class="block text-[9px] text-slate-400 mt-1 uppercase font-semibold">({{ $p->status ?? 'tersedia' }})</span>
                                    </td>

                                    <td class="p-4 text-center">
                                        <div class="flex items-center justify-center space-x-1.5">
                                            <!-- INSPEKSI LENGKAP MODAL -->
                                            <button type="button" onclick="openComprehensiveProductModal('{{ addslashes((string)(json_encode($p) ?: '{}')) }}')" class="bg-sky-50 hover:bg-sky-100 text-[#0369a1] px-2.5 py-1.5 rounded-lg font-bold transition shadow-xs" title="Inspeksi Lengkap 100% Data">
                                                <i class="fas fa-eye text-xs"></i>
                                            </button>

                                            <!-- GOD-MODE EDIT PAGE -->
                                            <a href="{{ route('admin.barang.edit', $p->id) }}" class="bg-indigo-50 hover:bg-indigo-100 text-indigo-700 px-2.5 py-1.5 rounded-lg font-bold transition shadow-xs" title="Edit Penuh (God-Mode)">
                                                <i class="fas fa-pen-to-square text-xs"></i>
                                            </a>

                                            <!-- DELETE -->
                                            <form action="/admin/barang/{{ $p->id }}/delete" method="POST" onsubmit="return confirm('Hapus permanen produk ini dari sistem?')" class="inline">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="bg-rose-50 hover:bg-rose-500 hover:text-white text-rose-600 px-2.5 py-1.5 rounded-lg font-bold transition shadow-xs" title="Hapus Permanen">
                                                    <i class="far fa-trash-alt text-xs"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="7" class="p-12 text-center text-slate-400">Belum ada produk terdaftar di inventaris.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>

            <!-- ===================== TAB 3: MONITOR VOUCHER ===================== -->
            <div id="tab-voucher" class="tab-content hidden">
                <section class="rentify-card border border-slate-200/80 overflow-hidden bg-white">
                    <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-rose-50/10">
                        <h3 class="text-base font-black text-slate-800"><i class="fas fa-ticket-alt text-rose-500 mr-2"></i>Pemantauan Voucher & Kill-Switch</h3>
                        <p class="text-[10px] text-slate-500 font-bold bg-white px-3 py-1 rounded-full border border-slate-200">Matikan paksa voucher yang melanggar aturan</p>
                    </div>
                    <div class="p-0 overflow-x-auto">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr class="border-b border-slate-200 bg-slate-50/50 font-bold text-slate-500 uppercase tracking-wider">
                                    <th class="p-4 pl-6">Kode & Pemilik Toko</th>
                                    <th class="p-4 text-center">Tipe Diskon</th>
                                    <th class="p-4 text-center">Kuota Pakai</th>
                                    <th class="p-4 text-center">Masa Berlaku</th>
                                    <th class="p-4 text-center">Status</th>
                                    <th class="p-4 text-center">Aksi (Kill Switch)</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse($allVouchers ?? [] as $v)
                                <tr class="hover:bg-slate-50/60 transition">
                                    <td class="p-4 pl-6">
                                        <p class="font-black text-slate-800 text-sm tracking-wide">{{ $v->kode_voucher ?? $v->code ?? '-' }}</p>
                                        <p class="text-[10px] text-slate-400"><i class="fas fa-store mr-1"></i>{{ $v->vendor?->vendor_name ?? $v->vendor?->name ?? 'Vendor Umum' }}</p>
                                    </td>
                                    <td class="p-4 text-center">
                                        @php
                                            $tipe = $v->tipe_diskon ?? $v->discount_type ?? 'nominal';
                                            $isFixed = in_array($tipe, ['nominal', 'fixed']);
                                            $nilai = $v->nilai_diskon ?? $v->discount_value ?? 0;
                                        @endphp
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $isFixed ? 'bg-blue-50 text-blue-700' : 'bg-purple-50 text-purple-700' }}">
                                            {{ $isFixed ? 'Potongan Rp ' . number_format($nilai, 0, ',', '.') : 'Diskon ' . (float)$nilai . '%' }}
                                        </span>
                                    </td>
                                    <td class="p-4 text-center font-bold text-slate-700">
                                        {{ $v->kuota_terpakai ?? $v->used_count ?? 0 }} / {{ $v->kuota_total ?? $v->usage_limit ?? '∞' }}
                                    </td>
                                    <td class="p-4 text-center text-slate-500">
                                        @php
                                            $tglSelesai = $v->tanggal_selesai ?? $v->valid_until ?? null;
                                        @endphp
                                        {{ $tglSelesai ? \Carbon\Carbon::parse($tglSelesai)->format('d M Y') : 'Selamanya' }}
                                    </td>
                                    <td class="p-4 text-center">
                                        @if($v->is_active)
                                            <span class="px-2 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider bg-emerald-100 text-emerald-800">Aktif</span>
                                        @else
                                            <span class="px-2 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider bg-rose-100 text-rose-800">Dimatikan</span>
                                        @endif
                                    </td>
                                    <td class="p-4 text-center">
                                        @if($v->is_active)
                                            <form action="/admin/vouchers/{{ $v->id }}/suspend" method="POST" onsubmit="return confirm('Matikan paksa voucher ini?')">
                                                @csrf @method('PATCH')
                                                <button type="submit" class="bg-rose-50 hover:bg-rose-500 hover:text-white text-rose-600 px-3 py-1 rounded-lg font-bold transition shadow-xs text-xs">
                                                    <i class="fas fa-power-off mr-1"></i> Matikan
                                                </button>
                                            </form>
                                        @else
                                            <form action="/admin/vouchers/{{ $v->id }}/activate" method="POST" onsubmit="return confirm('Aktifkan kembali voucher ini?')">
                                                @csrf @method('PATCH')
                                                <button type="submit" class="bg-emerald-50 hover:bg-emerald-500 hover:text-white text-emerald-600 px-3 py-1 rounded-lg font-bold transition shadow-xs text-xs">
                                                    <i class="fas fa-play mr-1"></i> Aktifkan
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="6" class="p-12 text-center text-slate-400">Belum ada voucher terdaftar di platform.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>

            <!-- ===================== TAB 4: TRANSAKSI ===================== -->
            <div id="tab-transaksi" class="tab-content hidden">
                <section class="rentify-card border border-slate-200/80 overflow-hidden bg-white">
                    <div class="p-6 border-b border-slate-100 flex justify-between items-center">
                        <h3 class="text-base font-black text-slate-800">Manajemen Transaksi Pesanan Customer</h3>
                        <div class="relative">
                            <i class="fas fa-search absolute left-3 top-2.5 text-slate-300"></i>
                            <input type="text" id="searchTransaksi" onkeyup="filterTransaksiTable()" placeholder="Cari invoice / pembeli..." class="rentify-input pl-9 pr-4 py-2 text-xs focus:outline-none">
                        </div>
                    </div>
                    <div class="p-0 overflow-x-auto">
                        <table class="w-full text-left border-collapse text-xs" id="transaksiTable">
                            <thead>
                                <tr class="border-b border-slate-200 bg-slate-50/50 font-bold text-slate-500 uppercase tracking-wider">
                                    <th class="p-4 pl-6">Invoice</th>
                                    <th class="p-4">Customer</th>
                                    <th class="p-4">Pendapatan Fee & Total</th>
                                    <th class="p-4">Tanggal Order</th>
                                    <th class="p-4 text-center">Status</th>
                                    <th class="p-4 text-center">Tindakan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse($allTransaksi ?? [] as $trx)
                                <tr class="hover:bg-slate-50/60 transition trx-row">
                                    <td class="p-4 pl-6 font-black text-slate-700 trx-invoice">#INV-{{ $trx->id }}</td>
                                    <td class="p-4 font-bold text-slate-600 trx-customer">{{ $trx->customer_name ?? 'User' }}</td>
                                    
                                    <td class="p-4">
                                        @php
                                            $statusTrx = strtolower($trx->status ?? '');
                                            if (in_array($statusTrx, ['dibatalkan', 'batal', 'cancelled', 'ditolak'])) {
                                                $feeHitung = 0;
                                            } else {
                                                $breakdown = \App\Services\TransactionCalculationService::getBreakdownForOrder($trx);
                                                $feeHitung = $breakdown['rentify_fee'];
                                            }
                                        @endphp
                                        <span class="font-black {{ $feeHitung > 0 ? 'text-emerald-600' : 'text-slate-400' }} text-sm block">
                                            Rp {{ number_format($feeHitung, 0, ',', '.') }}
                                        </span>
                                        <span class="text-[10px] text-slate-400 font-semibold">Total Tagihan: Rp {{ number_format($trx->total_price ?? 0, 0, ',', '.') }}</span>
                                    </td>

                                    <td class="p-4 text-slate-500">
                                        {{ $trx->created_at ? \Carbon\Carbon::parse($trx->created_at)->format('d M Y, H:i') : '-' }}
                                    </td>
                                    <td class="p-4 text-center">
                                        <span class="px-2.5 py-1 rounded-md text-[10px] font-bold uppercase tracking-wider
                                            {{ strtolower($trx->status ?? '') == 'selesai' ? 'bg-emerald-100 text-emerald-700' : (in_array(strtolower($trx->status ?? ''), ['dibatalkan', 'batal', 'cancelled']) ? 'bg-rose-100 text-rose-700' : 'bg-blue-100 text-sky-700') }}">
                                            {{ $trx->status ?? 'Pending' }}
                                        </span>
                                    </td>
                                    <td class="p-4 text-center">
                                        <button onclick="openTransaksiModal('{{ json_encode($trx) ?: '{}' }}')" class="bg-sky-50 hover:bg-sky-100 text-sky-600 px-3 py-1.5 rounded-lg text-xs font-bold transition shadow-sm">
                                            <i class="fas fa-file-invoice mr-1"></i> Detail
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="6" class="p-12 text-center text-slate-400">Database transaksi kosong.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>

            <!-- ===================== TAB 5: MITRA VENDOR ===================== -->
            <div id="tab-vendor" class="tab-content hidden">
                <section class="rentify-card border border-slate-200/80 overflow-hidden bg-white">
                    <div class="p-6 border-b border-slate-100 flex justify-between items-center">
                        <h3 class="text-base font-black text-slate-800">Master Data: Direktori Mitra Vendor</h3>
                        <div class="relative">
                            <i class="fas fa-search absolute left-3 top-2.5 text-slate-300"></i>
                            <input type="text" id="searchVendor" onkeyup="filterVendorTab()" placeholder="Cari nama toko / pemilik / email..." class="rentify-input pl-9 pr-4 py-2 text-xs focus:outline-none">
                        </div>
                    </div>
                    <div class="p-0 overflow-x-auto">
                        <table class="w-full text-left border-collapse text-sm" id="vendorTabTable">
                            <thead>
                                <tr class="border-b border-slate-200 bg-slate-50/50 font-bold text-slate-500 uppercase tracking-wider text-xs">
                                    <th class="p-4 pl-6">Profil Toko</th>
                                    <th class="p-4">Identitas Pemilik</th>
                                    <th class="p-4">Kontak (Email/WA)</th>
                                    <th class="p-4 text-center">Lisensi</th>
                                    <th class="p-4 text-center">Kelola & Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($allVendors as $v)
                                <tr class="hover:bg-slate-50/60 transition tab-vendor-row">
                                    <td class="p-4 pl-6 tab-vendor-shop">
                                        <a href="{{ route('admin.vendors.impersonate', $v->id) }}" class="font-black text-slate-800 hover:text-[#0369a1] hover:underline flex items-center group gap-2.5" title="Klik untuk Masuk ke Dashboard Toko Ini">
                                            <div class="w-8 h-8 rounded-lg bg-sky-50 text-[#0369a1] flex items-center justify-center font-bold text-sm group-hover:scale-105 transition shrink-0">
                                                <i class="fas fa-store-alt"></i>
                                            </div>
                                            <div>
                                                <span class="block text-sm leading-tight">{{ $v->vendor_name ?? '-' }}</span>
                                                <span class="text-[10px] text-sky-600 font-bold flex items-center gap-1 mt-0.5 group-hover:underline">
                                                    <i class="fas fa-door-open"></i> Masuk Toko &rarr;
                                                </span>
                                            </div>
                                        </a>
                                    </td>
                                    <td class="p-4 font-bold text-slate-600 tab-vendor-owner">{{ $v->name }}</td>
                                    <td class="p-4">
                                        <p class="font-mono text-slate-500 text-xs tab-vendor-email">{{ $v->email }}</p>
                                        <p class="text-emerald-600 font-bold text-xs mt-1"><i class="fab fa-whatsapp"></i> {{ $v->whatsapp_vendor ?? '-' }}</p>
                                    </td>
                                    <td class="p-4 text-center">
                                        @if($v->vendor_status == 'approved')
                                            <span class="px-2.5 py-1 rounded-md text-[10px] font-bold uppercase tracking-wider bg-emerald-100 text-emerald-800">Aktif</span>
                                        @elseif($v->vendor_status == 'suspended')
                                            <span class="px-2.5 py-1 rounded-md text-[10px] font-bold uppercase tracking-wider bg-rose-100 text-rose-800"><i class="fas fa-lock mr-1"></i>Banned</span>
                                        @else
                                            <span class="px-2.5 py-1 rounded-md text-[10px] font-bold uppercase tracking-wider bg-amber-100 text-amber-800">{{ $v->vendor_status }}</span>
                                        @endif
                                    </td>
                                    <td class="p-4 text-center">
                                        <div class="flex items-center justify-center gap-2">
                                            <a href="{{ route('admin.vendors.impersonate', $v->id) }}" class="bg-gradient-to-r from-sky-500 to-blue-600 hover:from-sky-600 hover:to-blue-700 text-white font-bold px-3 py-1.5 rounded-lg text-xs shadow-xs transition flex items-center gap-1.5 whitespace-nowrap active:scale-95" title="Masuk & Kelola Toko Ini">
                                                <i class="fas fa-door-open"></i> Masuk Toko
                                            </a>
                                            @if($v->vendor_status == 'approved')
                                                <form action="/admin/vendors/{{ $v->id }}/suspend" method="POST" onsubmit="return confirm('Banned sementara vendor ini?\n\nSemua produk mereka akan disembunyikan otomatis dari pelanggan.')">
                                                    @csrf @method('PATCH')
                                                    <button type="submit" class="bg-amber-50 hover:bg-amber-500 hover:text-white text-amber-600 px-2.5 py-1.5 rounded-lg font-bold transition shadow-xs text-xs" title="Banned Sementara"><i class="fas fa-user-clock"></i></button>
                                                </form>
                                            @elseif($v->vendor_status == 'suspended')
                                                <form action="/admin/vendors/{{ $v->id }}/activate" method="POST" onsubmit="return confirm('Buka banned vendor ini?\n\nProduk mereka akan kembali tampil di halaman pelanggan.')">
                                                    @csrf @method('PATCH')
                                                    <button type="submit" class="bg-emerald-50 hover:bg-emerald-500 hover:text-white text-emerald-600 px-2.5 py-1.5 rounded-lg font-bold transition shadow-xs text-xs" title="Buka Banned"><i class="fas fa-unlock-keyhole"></i></button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>

            <!-- ===================== TAB 6: DATA PELANGGAN ===================== -->
            <div id="tab-customer" class="tab-content hidden">
                <section class="rentify-card border border-slate-200/80 overflow-hidden bg-white">
                    <div class="p-6 border-b border-slate-100 flex justify-between items-center">
                        <div class="flex items-center space-x-3">
                            <h3 class="text-base font-black text-slate-800">Master Data: Direktori Pelanggan</h3>
                            <span class="bg-sky-100 text-sky-800 text-xs font-bold px-3 py-1 rounded-full">{{ $allCustomers->count() }} Pengguna Terdaftar</span>
                        </div>
                        <div class="relative">
                            <i class="fas fa-search absolute left-3 top-2.5 text-slate-300"></i>
                            <input type="text" id="searchCustomer" onkeyup="filterCustomerTab()" placeholder="Cari nama / email pelanggan..." class="rentify-input pl-9 pr-4 py-2 text-xs focus:outline-none">
                        </div>
                    </div>
                    <div class="p-0 overflow-x-auto">
                        <table class="w-full text-left border-collapse text-sm" id="customerTabTable">
                            <thead>
                                <tr class="border-b border-slate-200 bg-slate-50/50 font-bold text-slate-500 uppercase tracking-wider text-xs">
                                    <th class="p-4 pl-6">Profil Pelanggan</th>
                                    <th class="p-4">Alamat Email</th>
                                    <th class="p-4">Tanggal Bergabung</th>
                                    <th class="p-4 text-center">Hapus Data</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse($allCustomers as $c)
                                <tr class="hover:bg-slate-50/60 transition tab-customer-row">
                                    <td class="p-4 pl-6 flex items-center space-x-3">
                                        <div class="w-10 h-10 bg-slate-200 rounded-full flex items-center justify-center text-slate-500 font-bold text-lg"><i class="fas fa-user"></i></div>
                                        <span class="font-bold text-slate-800 tab-customer-name">{{ $c->name }}</span>
                                    </td>
                                    <td class="p-4 font-mono text-slate-500 tab-customer-email">{{ $c->email }}</td>
                                    <td class="p-4 font-medium text-slate-500">{{ $c->created_at?->format('d M Y') ?? '-' }}</td>
                                    <td class="p-4 text-center">
                                        <form action="/admin/user/{{ $c->id }}/delete" method="POST" onsubmit="return confirm('Hapus permanen akun pelanggan ini dari database?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-rose-500 hover:text-rose-700 font-bold bg-rose-50 px-3 py-1.5 rounded-lg transition shadow-sm text-xs">Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="4" class="p-12 text-center text-slate-400">Belum ada akun customer terdaftar.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>

        </div>
    </main>

    <!-- ===================== MODAL INSPEKSI LENGKAP PRODUK (100% TRANSPARANSI DATA VENDOR) ===================== -->
    <div id="productModal" class="fixed inset-0 bg-slate-900/60 hidden flex items-center justify-center z-50 p-4 backdrop-blur-sm">
        <div class="bg-white rounded-3xl max-w-4xl w-full p-6 sm:p-8 max-h-[92vh] overflow-y-auto border-t-8 border-[#0369a1] shadow-2xl space-y-6">
            
            <!-- HEADER MODAL -->
            <div class="flex justify-between items-start border-b border-slate-100 pb-4">
                <div>
                    <div class="flex items-center space-x-2 mb-1">
                        <span id="md_badge_approval" class="text-[9px] font-black uppercase px-2 py-0.5 rounded-md border"></span>
                        <span id="md_kategori" class="text-[10px] font-bold text-sky-700 bg-sky-50 px-2.5 py-0.5 rounded-md"></span>
                        <span id="md_id" class="text-[10px] font-mono text-slate-400"></span>
                    </div>
                    <h3 id="md_nama" class="font-black text-xl text-slate-800 tracking-tight"></h3>
                </div>
                <button onclick="closeProductModal()" class="text-slate-400 hover:text-rose-500 text-3xl font-light transition">&times;</button>
            </div>

            <!-- ISI MODAL: 2 KOLOM (MEDIA & DETAIL LENGKAP) -->
            <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
                
                <!-- KOLOM KIRI: MEDIA & GALERI LENGKAP (5 COLS) -->
                <div class="md:col-span-5 space-y-4">
                    <div class="w-full aspect-square rounded-2xl overflow-hidden border border-slate-200 bg-slate-900 flex items-center justify-center relative">
                        <img id="md_cover_img" src="" class="w-full h-full object-cover">
                        <video id="md_cover_vid" src="" class="w-full h-full object-cover hidden" controls></video>
                        <span id="md_media_type_badge" class="absolute top-2 left-2 bg-black/70 text-white text-[9px] font-bold px-2 py-0.5 rounded">Cover</span>
                    </div>

                    <div>
                        <p class="text-[11px] font-bold text-slate-600 uppercase mb-2 flex items-center justify-between">
                            <span>Galeri Foto Vendor</span>
                            <span id="md_galeri_count" class="text-[10px] text-slate-400"></span>
                        </p>
                        <div id="md_galeri_container" class="grid grid-cols-4 gap-2"></div>
                    </div>

                    <!-- KONTROL STOK LANGSUNG DI DALAM MODAL -->
                    <div class="p-4 bg-amber-50 rounded-2xl border border-amber-200 space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-black text-amber-900 uppercase tracking-wider">Atur Stok Produk Langsung:</span>
                            <span id="md_current_stock_label" class="text-xs font-black text-amber-800"></span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <input type="number" id="md_stock_input" min="0" class="flex-1 text-sm font-black text-slate-800 bg-white border border-amber-300 rounded-xl px-3 py-1.5 focus:outline-none">
                            <button type="button" onclick="saveModalStock()" class="px-4 py-2 bg-[#0369a1] hover:bg-[#0284c7] text-white text-xs font-bold rounded-xl shadow-xs transition">
                                Update
                            </button>
                        </div>
                        <p class="text-[9px] text-amber-700 italic">* Mengubah stok di sini langsung mengupdate stok vendor di katalog pelanggan.</p>
                    </div>
                </div>

                <!-- KOLOM KANAN: RINCIAN 100% DATA ISIAN VENDOR (7 COLS) -->
                <div class="md:col-span-7 space-y-4 text-xs">
                    
                    <!-- IDENTITAS VENDOR PEMILIK -->
                    <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100 space-y-2">
                        <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest block">Mitra Vendor Pengunggah:</span>
                        <div class="flex items-center justify-between">
                            <div>
                                <h4 id="md_vendor_shop" class="text-sm font-black text-slate-800"></h4>
                                <p id="md_vendor_owner" class="text-[11px] text-slate-500 font-medium"></p>
                            </div>
                            <div id="md_vendor_wa_btn"></div>
                        </div>
                    </div>

                    <!-- TARIF & BIAYA -->
                    <div class="grid grid-cols-3 gap-3 text-center">
                        <div class="p-3 bg-sky-50 border border-sky-100 rounded-xl">
                            <p class="text-[9px] text-sky-600 font-black tracking-widest uppercase">TARIF SEWA / HARI</p>
                            <p id="md_harga" class="font-black text-sky-900 text-sm mt-0.5"></p>
                        </div>
                        <div class="p-3 bg-emerald-50 border border-emerald-100 rounded-xl">
                            <p class="text-[9px] text-emerald-600 font-black tracking-widest uppercase">DEPOSIT JAMINAN</p>
                            <p id="md_deposit" class="font-black text-emerald-900 text-sm mt-0.5"></p>
                        </div>
                        <div class="p-3 bg-rose-50 border border-rose-100 rounded-xl">
                            <p class="text-[9px] text-rose-600 font-black tracking-widest uppercase">DENDA TELAT / HARI</p>
                            <p id="md_denda" class="font-black text-rose-900 text-sm mt-0.5"></p>
                        </div>
                    </div>

                    <!-- KONDISI & PENGANTARAN -->
                    <div class="grid grid-cols-2 gap-3">
                        <div class="p-3 bg-slate-50 border border-slate-100 rounded-xl">
                            <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest block">KONDISI FISIK</span>
                            <span id="md_kondisi" class="font-bold text-slate-800 text-xs"></span>
                        </div>
                        <div class="p-3 bg-slate-50 border border-slate-100 rounded-xl">
                            <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest block">PENGIRIMAN / AMBIL</span>
                            <span id="md_delivery" class="font-bold text-slate-800 text-xs"></span>
                        </div>
                    </div>

                    <!-- ALAMAT & LOKASI -->
                    <div class="p-3.5 bg-slate-50 border border-slate-100 rounded-xl space-y-1">
                        <div class="flex items-center justify-between">
                            <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest"><i class="fas fa-location-dot text-rose-500 mr-1"></i> ALAMAT & KOORDINAT PENGAMBILAN</span>
                            <a id="md_maps_link" href="#" target="_blank" class="text-[10px] font-bold text-sky-600 hover:underline"><i class="fas fa-map-marked-alt mr-1"></i> Buka Maps</a>
                        </div>
                        <p id="md_alamat" class="text-xs text-slate-700 font-medium leading-relaxed"></p>
                        <p id="md_coords" class="text-[10px] font-mono text-slate-400"></p>
                    </div>

                    <!-- SPESIFIKASI TEKNIS -->
                    <div class="p-3.5 bg-slate-50 border border-slate-100 rounded-xl space-y-1.5">
                        <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest"><i class="fas fa-sliders text-indigo-500 mr-1"></i> SPESIFIKASI TEKNIS & KELENGKAPAN</span>
                        <div id="md_specs_list" class="grid grid-cols-2 gap-2 text-xs"></div>
                    </div>

                    <!-- DESKRIPSI -->
                    <div>
                        <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest block mb-1 ml-1"><i class="fas fa-align-left mr-1"></i> DESKRIPSI PRODUK</span>
                        <p id="md_deskripsi" class="text-slate-600 bg-slate-50 p-3.5 rounded-xl text-xs leading-relaxed border border-slate-100 max-h-32 overflow-y-auto"></p>
                    </div>
                </div>
            </div>

            <!-- MODAL FOOTER ACTION BAR -->
            <div class="flex items-center justify-between pt-4 border-t border-slate-100">
                <div id="md_approval_buttons" class="flex items-center space-x-2"></div>

                <div class="flex items-center space-x-2">
                    <a id="md_godmode_edit_btn" href="#" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl shadow-xs transition flex items-center space-x-1.5">
                        <i class="fas fa-pen-to-square"></i>
                        <span>Buka Edit Penuh (God-Mode)</span>
                    </a>
                    <button type="button" onclick="closeProductModal()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ===================== MODAL IDENTITAS VENDOR ===================== -->
    <div id="vendorModal" class="fixed inset-0 bg-slate-900/60 hidden flex items-center justify-center z-50 p-4 backdrop-blur-sm">
        <div class="rentify-card max-w-md w-full p-6 border-t-4 border-purple-500 bg-white">
            <div class="flex justify-between items-center border-b pb-3 mb-4">
                <h3 class="font-black text-lg text-slate-800 uppercase tracking-wider">Identitas Pengaju Vendor</h3>
                <button onclick="closeVendorModal()" class="text-slate-400 hover:text-rose-500 text-2xl transition">&times;</button>
            </div>
            <div class="space-y-3 text-xs font-semibold">
                <div class="p-3 bg-slate-50 border border-slate-100 rounded-xl"><span class="block text-[9px] font-black tracking-widest text-slate-400 mb-1">NAMA TOKO BISNIS:</span><span id="vd_tokoname" class="text-base font-black text-purple-700"></span></div>
                <div class="p-3 bg-slate-50 border border-slate-100 rounded-xl"><span class="block text-[9px] font-black tracking-widest text-slate-400 mb-1">IDENTITAS PEMILIK SAH:</span><span id="vd_owner" class="text-sm text-slate-800 font-bold"></span></div>
                <div class="p-3 bg-slate-50 border border-slate-100 rounded-xl"><span class="block text-[9px] font-black tracking-widest text-slate-400 mb-1">EMAIL TERDAFTAR:</span><span id="vd_email" class="text-sm text-slate-600 font-mono"></span></div>
                <div class="p-3 bg-slate-50 border border-slate-100 rounded-xl"><span class="block text-[9px] font-black tracking-widest text-slate-400 mb-1">WHATSAPP TOKO (AKTIF):</span><span id="vd_wa" class="text-emerald-600 font-black text-sm"></span></div>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 flex gap-2">
                <a id="vd_btn_enter" href="#" class="flex-1 bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white font-extrabold py-2.5 px-4 rounded-xl text-center text-xs shadow-md transition flex items-center justify-center gap-2">
                    <i class="fas fa-door-open"></i> Masuk & Kelola Dashboard Toko
                </a>
            </div>
        </div>
    </div>

    <!-- ===================== MODAL TRANSAKSI ===================== -->
    <div id="transaksiModal" class="fixed inset-0 bg-slate-900/60 hidden flex items-center justify-center z-50 p-4 backdrop-blur-sm">
        <div class="rentify-card max-w-lg w-full p-6 border-t-4 border-emerald-500 max-h-[90vh] overflow-y-auto bg-white">
            <div class="flex justify-between items-center border-b pb-3 mb-4">
                <div>
                    <h3 class="font-black text-lg text-slate-800 uppercase tracking-wider">Struk Rincian Pesanan</h3>
                    <p id="tx_invoice" class="text-xs font-bold text-emerald-600"></p>
                </div>
                <button onclick="closeTransaksiModal()" class="text-slate-400 hover:text-rose-500 text-2xl transition">&times;</button>
            </div>
            
            <div class="space-y-4 text-xs">
                <div class="bg-slate-50 p-3 rounded-xl border border-slate-100 flex justify-between">
                    <div>
                        <span class="block text-[9px] font-black text-slate-400 uppercase">PEMBELI</span>
                        <span id="tx_customer" class="font-bold text-slate-800 text-sm"></span>
                    </div>
                    <div class="text-right">
                        <span class="block text-[9px] font-black text-slate-400 uppercase">METODE & STATUS</span>
                        <span id="tx_method" class="font-bold text-sky-600"></span> | <span id="tx_status" class="font-bold text-emerald-600"></span>
                    </div>
                </div>

                <div>
                    <span class="block text-[10px] font-black text-slate-400 uppercase mb-2">DAFTAR BARANG DISEWA:</span>
                    <div id="tx_items" class="space-y-2 max-h-40 overflow-y-auto pr-1"></div>
                </div>

                <div class="border-t border-slate-100 pt-3 space-y-1.5 text-xs">
                    <div class="flex justify-between text-slate-500">
                        <span>Durasi Sewa:</span>
                        <span id="tx_duration" class="font-bold text-slate-700"></span>
                    </div>
                    <div class="flex justify-between text-slate-500">
                        <span>Total Ongkos Kirim:</span>
                        <span id="tx_ongkir" class="font-bold text-slate-700"></span>
                    </div>
                    
                    <div class="flex justify-between text-rose-500 font-bold bg-rose-50 p-2 rounded-lg my-1">
                        <span>Diskon Toko / Promo:</span>
                        <span id="tx_diskon">- Rp 0</span>
                    </div>

                    <div class="flex justify-between text-slate-800 text-sm font-black pt-2 border-t border-dashed">
                        <span>Total Akhir Dibayar:</span>
                        <span id="tx_total" class="text-sky-600"></span>
                    </div>
                    <div class="flex justify-between bg-emerald-50 p-2 rounded-lg text-emerald-800 font-black text-sm mt-2 border border-emerald-100">
                        <span>Hak Rentify (Fee 5% dari Harga Dasar):</span>
                        <span id="tx_fee"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JAVASCRIPT LOGIC (GOD-MODE POWERED) -->
    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        let currentModalProductId = null;

        function switchTab(tabId, titleText) {
            document.querySelectorAll('.tab-content').forEach(el => {
                el.classList.add('hidden');
                el.classList.remove('block');
            });
            
            const target = document.getElementById(tabId);
            if(target) {
                target.classList.remove('hidden');
                target.classList.add('block');
            }
            if(titleText) {
                document.getElementById('header-title').innerText = titleText;
            }

            document.querySelectorAll('.menu-btn').forEach(btn => {
                btn.classList.remove('bg-[#e0f2fe]', 'text-[#0369a1]');
                btn.classList.add('text-slate-600');
            });
            let activeBtn = document.getElementById('btn-' + tabId);
            if(activeBtn) {
                activeBtn.classList.remove('text-slate-600');
                activeBtn.classList.add('bg-[#e0f2fe]', 'text-[#0369a1]');
            }
        }

        // Toggle Notifikasi Dropdown
        function toggleNotifDropdown() {
            const el = document.getElementById('notifDropdown');
            el.classList.toggle('hidden');
        }
        function closeNotifDropdown() {
            document.getElementById('notifDropdown')?.classList.add('hidden');
        }
        window.addEventListener('click', function(e) {
            const bell = document.getElementById('notifBellBtn');
            const drop = document.getElementById('notifDropdown');
            if (drop && !drop.contains(e.target) && !bell.contains(e.target)) {
                drop.classList.add('hidden');
            }
        });

        // Tampilkan Toast Notifikasi
        function showAjaxToast(msg, title = 'Berhasil Diperbarui', isError = false) {
            const toast = document.getElementById('ajaxToast');
            const toastMsg = document.getElementById('ajaxToastMsg');
            const toastTitle = document.getElementById('ajaxToastTitle');
            const toastIcon = document.getElementById('ajaxToastIcon');
            
            toastMsg.innerText = msg;
            toastTitle.innerText = title;

            if (isError) {
                toastIcon.className = 'fas fa-exclamation-circle text-rose-400 text-lg';
            } else {
                toastIcon.className = 'fas fa-check-circle text-emerald-400 text-lg';
            }

            toast.classList.remove('hidden');
            setTimeout(() => {
                toast.classList.add('hidden');
            }, 3500);
        }

        // Quick Stock Adjuster (+ / -)
        function adjustQuickStock(id, delta) {
            const input = document.getElementById('quick-stock-input-' + id);
            if (!input) return;
            let val = parseInt(input.value) || 0;
            val = Math.max(0, val + delta);
            input.value = val;
        }

        // Simpan Quick Stock via AJAX
        async function saveQuickStock(id) {
            const input = document.getElementById('quick-stock-input-' + id);
            const feedback = document.getElementById('stock-feedback-' + id);
            if (!input) return;

            const newStock = parseInt(input.value);
            if (isNaN(newStock) || newStock < 0) {
                alert('Jumlah stok harus berupa angka valid (minimal 0).');
                return;
            }

            try {
                const response = await fetch(`/admin/barang/${id}/quick-stock`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ stok_total: newStock })
                });

                const data = await response.json();
                if (response.ok && data.success) {
                    if (feedback) {
                        feedback.classList.remove('hidden');
                        setTimeout(() => feedback.classList.add('hidden'), 2000);
                    }
                    showAjaxToast(data.message, 'Stok Terupdate!');
                    
                    // Update atribut pada baris tabel
                    const row = input.closest('tr');
                    if (row) row.setAttribute('data-stock', newStock);
                } else {
                    showAjaxToast(data.message || 'Gagal mengubah stok.', 'Error', true);
                }
            } catch (err) {
                console.error(err);
                showAjaxToast('Terjadi kesalahan jaringan.', 'Error', true);
            }
        }

        // Simpan Stok dari dalam Modal Inspeksi
        async function saveModalStock() {
            if (!currentModalProductId) return;
            const input = document.getElementById('md_stock_input');
            const newStock = parseInt(input.value);
            if (isNaN(newStock) || newStock < 0) {
                alert('Stok harus bernilai 0 atau lebih.');
                return;
            }

            try {
                const response = await fetch(`/admin/barang/${currentModalProductId}/quick-stock`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ stok_total: newStock })
                });

                const data = await response.json();
                if (response.ok && data.success) {
                    document.getElementById('md_current_stock_label').innerText = `${newStock} Unit`;
                    showAjaxToast(data.message, 'Stok Produk Diperbarui!');
                    
                    // Update juga input di tabel utama jika ada
                    const tableInput = document.getElementById('quick-stock-input-' + currentModalProductId);
                    if (tableInput) tableInput.value = newStock;
                } else {
                    showAjaxToast(data.message || 'Gagal mengubah stok.', 'Error', true);
                }
            } catch (err) {
                console.error(err);
                showAjaxToast('Terjadi kesalahan jaringan.', 'Error', true);
            }
        }

        // ==========================================
        // INSPEKSI LENGKAP 100% PRODUK MODAL
        // ==========================================
        function openComprehensiveProductModal(dataString) {
            let data = {};
            try {
                data = typeof dataString === 'string' ? JSON.parse(dataString) : dataString;
            } catch (e) {
                console.error('Gagal mem-parse data produk:', e);
                return;
            }

            currentModalProductId = data.id;
            const fmt = (val) => 'Rp ' + parseFloat(val || 0).toLocaleString('id-ID');

            // Header Data
            document.getElementById('md_id').innerText = '#ID-' + data.id;
            document.getElementById('md_nama').innerText = data.nama || '-';
            document.getElementById('md_kategori').innerText = data.kategori?.nama || 'Tanpa Kategori';

            const status = (data.status_barang || 'pending').toLowerCase();
            const badgeEl = document.getElementById('md_badge_approval');
            if (status === 'disetujui') {
                badgeEl.innerText = 'Disetujui / Aktif';
                badgeEl.className = 'text-[9px] font-black uppercase px-2 py-0.5 rounded-md border bg-emerald-50 text-emerald-700 border-emerald-200';
            } else if (status === 'ditolak') {
                badgeEl.innerText = 'Ditolak';
                badgeEl.className = 'text-[9px] font-black uppercase px-2 py-0.5 rounded-md border bg-rose-50 text-rose-700 border-rose-200';
            } else {
                badgeEl.innerText = 'Pending Kurasi';
                badgeEl.className = 'text-[9px] font-black uppercase px-2 py-0.5 rounded-md border bg-amber-50 text-amber-700 border-amber-200';
            }

            // Cover Media
            const coverImg = document.getElementById('md_cover_img');
            const coverVid = document.getElementById('md_cover_vid');
            let coverUrl = data.cover_photo_url || data.cover_photo || '';
            let isVideo = data.is_cover_video || (coverUrl.match(/\.(mp4|webm|mov|ogg)$/i) !== null);

            if (isVideo && coverUrl) {
                coverImg.classList.add('hidden');
                coverVid.classList.remove('hidden');
                coverVid.src = coverUrl;
            } else {
                coverVid.classList.add('hidden');
                coverImg.classList.remove('hidden');
                coverImg.src = coverUrl || 'https://placehold.co/400?text=No+Cover';
            }

            // Galeri Foto Tambahan
            const galeriContainer = document.getElementById('md_galeri_container');
            galeriContainer.innerHTML = '';
            const fotos = data.fotos || [];
            document.getElementById('md_galeri_count').innerText = `${fotos.length} Berkas Tambahan`;

            if (fotos.length > 0) {
                fotos.forEach((f, idx) => {
                    const fUrl = f.foto_url || f.foto_path || '';
                    const fIsVid = f.is_video || (fUrl.match(/\.(mp4|webm|mov|ogg)$/i) !== null);
                    
                    const div = document.createElement('div');
                    div.className = 'aspect-square rounded-xl overflow-hidden border border-slate-200 bg-slate-100 cursor-pointer hover:border-sky-500 transition relative';
                    
                    if (fIsVid) {
                        div.innerHTML = `<video src="${fUrl}" class="w-full h-full object-cover" muted></video><span class="absolute top-1 left-1 bg-black/80 text-white text-[7px] font-bold px-1 rounded">VID</span>`;
                        div.onclick = () => {
                            coverImg.classList.add('hidden');
                            coverVid.classList.remove('hidden');
                            coverVid.src = fUrl;
                            coverVid.play();
                        };
                    } else {
                        div.innerHTML = `<img src="${fUrl}" class="w-full h-full object-cover" onerror="this.src='https://placehold.co/100?text=Err'">`;
                        div.onclick = () => {
                            coverVid.classList.add('hidden');
                            coverVid.pause();
                            coverImg.classList.remove('hidden');
                            coverImg.src = fUrl;
                        };
                    }
                    galeriContainer.appendChild(div);
                });
            } else {
                galeriContainer.innerHTML = '<div class="col-span-4 text-[10px] text-slate-400 italic">Tidak ada foto galeri tambahan.</div>';
            }

            // Stok Data
            document.getElementById('md_current_stock_label').innerText = `${data.stok_total || 0} Unit`;
            document.getElementById('md_stock_input').value = data.stok_total || 0;

            // Vendor Detail
            const vendor = data.vendor || {};
            document.getElementById('md_vendor_shop').innerText = vendor.vendor_name || 'Vendor Toko';
            document.getElementById('md_vendor_owner').innerText = `${vendor.name || '-'} • ${vendor.email || '-'}`;
            
            const waBtn = document.getElementById('md_vendor_wa_btn');
            if (vendor.whatsapp_vendor) {
                const waClean = vendor.whatsapp_vendor.replace(/[^0-9]/g, '');
                waBtn.innerHTML = `
                    <a href="https://wa.me/${waClean}" target="_blank" class="px-3 py-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-bold text-xs rounded-xl flex items-center space-x-1 border border-emerald-200 transition">
                        <i class="fab fa-whatsapp"></i> <span>Chat WA</span>
                    </a>
                `;
            } else {
                waBtn.innerHTML = '<span class="text-[10px] text-slate-400">No WA belum ada</span>';
            }

            // Tarif
            document.getElementById('md_harga').innerText = fmt(data.harga_sewa_harian);
            document.getElementById('md_deposit').innerText = fmt(data.deposit);
            document.getElementById('md_denda').innerText = fmt(data.denda_per_hari);

            // Kondisi & Pengantaran
            document.getElementById('md_kondisi').innerText = data.kondisi || '-';
            document.getElementById('md_delivery').innerText = data.is_delivery_supported ? 'Mendukung Kurir / Antar' : 'Hanya Ambil di Lokasi (Self Pick-up)';

            // Lokasi & Koordinat
            document.getElementById('md_alamat').innerText = data.alamat || 'Alamat belum diatur.';
            const lat = data.latitude || '';
            const lng = data.longitude || '';
            const coordsEl = document.getElementById('md_coords');
            const mapsLink = document.getElementById('md_maps_link');

            if (lat && lng) {
                coordsEl.innerText = `Titik Koordinat: ${lat}, ${lng}`;
                mapsLink.href = `https://maps.google.com/?q=${lat},${lng}`;
                mapsLink.classList.remove('hidden');
            } else {
                coordsEl.innerText = 'Titik koordinat belum diisi.';
                mapsLink.classList.add('hidden');
            }

            // Spesifikasi
            const specsList = document.getElementById('md_specs_list');
            specsList.innerHTML = '';
            let specs = data.spesifikasi || {};
            if (typeof specs === 'string') {
                try { specs = JSON.parse(specs); } catch (e) { specs = {}; }
            }

            if (Object.keys(specs).length > 0) {
                for (const [key, val] of Object.entries(specs)) {
                    if (!val) continue;
                    const formattedVal = Array.isArray(val) ? val.join(', ') : val;
                    const item = document.createElement('div');
                    item.className = 'bg-white p-2 rounded-lg border border-slate-200';
                    item.innerHTML = `<span class="block text-[8px] font-black uppercase text-slate-400">${key.replace('_', ' ')}</span><span class="font-bold text-slate-800 text-xs">${formattedVal}</span>`;
                    specsList.appendChild(item);
                }
            } else {
                specsList.innerHTML = '<div class="col-span-2 text-[10px] text-slate-400 italic">Tidak ada spesifikasi khusus.</div>';
            }

            // Deskripsi
            document.getElementById('md_deskripsi').innerText = data.deskripsi || 'Tidak ada deskripsi.';

            // Approval Decision Buttons
            const approvalContainer = document.getElementById('md_approval_buttons');
            approvalContainer.innerHTML = '';
            if (status === 'pending') {
                approvalContainer.innerHTML = `
                    <form action="/admin/barang/${data.id}/approve" method="POST" class="inline">
                        <input type="hidden" name="_token" value="${csrfToken}">
                        <input type="hidden" name="_method" value="PATCH">
                        <button type="submit" class="px-4 py-2 bg-emerald-500 hover:bg-emerald-600 text-white font-bold text-xs rounded-xl shadow-xs transition">
                            <i class="fas fa-check mr-1"></i> Setujui Produk
                        </button>
                    </form>
                    <form action="/admin/barang/${data.id}/reject" method="POST" class="inline">
                        <input type="hidden" name="_token" value="${csrfToken}">
                        <input type="hidden" name="_method" value="PATCH">
                        <button type="submit" class="px-4 py-2 bg-rose-500 hover:bg-rose-600 text-white font-bold text-xs rounded-xl shadow-xs transition">
                            <i class="fas fa-times mr-1"></i> Tolak Produk
                        </button>
                    </form>
                `;
            }

            // God-Mode Edit Page Button Link
            document.getElementById('md_godmode_edit_btn').href = `/admin/barang/${data.id}/edit`;

            // Buka Modal
            document.getElementById('productModal').classList.remove('hidden');
        }

        // Alias fungsi lama agar aman
        function openProductModal(dataString) {
            openComprehensiveProductModal(dataString);
        }

        function closeProductModal() {
            const coverVid = document.getElementById('md_cover_vid');
            if (coverVid) coverVid.pause();
            document.getElementById('productModal').classList.add('hidden');
        }

        // ==========================================
        // FILTER INVENTARIS PRODUK STATUS & SEARCH
        // ==========================================
        function filterProdukStatus(status) {
            document.querySelectorAll('.produk-filter-btn').forEach(btn => {
                if (btn.getAttribute('data-status') === status) {
                    btn.className = 'produk-filter-btn px-3 py-1 rounded-lg font-bold bg-[#0369a1] text-white transition';
                } else {
                    btn.className = 'produk-filter-btn px-3 py-1 rounded-lg font-bold bg-white text-slate-600 hover:bg-slate-200 border border-slate-200 transition';
                }
            });

            document.querySelectorAll('.produk-row').forEach(row => {
                const rowStatus = (row.getAttribute('data-status') || '').toLowerCase();
                if (status === 'all' || rowStatus === status) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        function filterProdukTable() {
            const query = (document.getElementById('searchProduk')?.value || '').toLowerCase();
            document.querySelectorAll('.produk-row').forEach(row => {
                const nama = row.querySelector('.produk-nama')?.innerText.toLowerCase() || '';
                const vendor = row.querySelector('.produk-vendor')?.innerText.toLowerCase() || '';
                const kategori = row.querySelector('.produk-kategori')?.innerText.toLowerCase() || '';
                row.style.display = (nama.includes(query) || vendor.includes(query) || kategori.includes(query)) ? '' : 'none';
            });
        }

        // Vendor & Customer Modal Handlers
        function openVendorModal(dataString) {
            const data = JSON.parse(dataString);
            document.getElementById('vd_tokoname').innerText = data.vendor_name || '-';
            document.getElementById('vd_owner').innerText = data.name || '-';
            document.getElementById('vd_email').innerText = data.email || '-';
            document.getElementById('vd_wa').innerText = data.whatsapp_vendor || '-';
            const enterBtn = document.getElementById('vd_btn_enter');
            if (enterBtn && data.id) {
                enterBtn.href = '/admin/vendors/' + data.id + '/impersonate';
            }
            document.getElementById('vendorModal').classList.remove('hidden');
        }
        function closeVendorModal() { document.getElementById('vendorModal').classList.add('hidden'); }

        function openTransaksiModal(dataString) {
            const data = JSON.parse(dataString);
            const fmt = (val) => 'Rp ' + parseFloat(val || 0).toLocaleString('id-ID');
            
            document.getElementById('tx_invoice').innerText = '#INV-' + data.id;
            document.getElementById('tx_customer').innerText = data.customer_name || 'User';
            document.getElementById('tx_method').innerText = data.payment_method || 'COD';
            document.getElementById('tx_status').innerText = data.status || 'Pending';
            document.getElementById('tx_duration').innerText = (data.duration_days || 1) + ' Hari';
            
            let ongkir = parseFloat(data.shipping_fee || 0);
            let total = parseFloat(data.total_price || 0);
            let diskon = parseFloat(data.potongan_voucher || 0);
            
            let sewaBesertaFee = total - ongkir + diskon;
            let sewaAsli = sewaBesertaFee / 1.05;
            
            let rentifyFee = parseFloat(data.rentify_fee || 0);
            let fee = rentifyFee > 0 ? rentifyFee : (sewaBesertaFee - sewaAsli);

            let status = (data.status || 'pending').toLowerCase();
            if (['dibatalkan', 'batal', 'cancelled', 'ditolak'].includes(status)) {
                fee = 0;
            }

            document.getElementById('tx_ongkir').innerText = fmt(ongkir);
            document.getElementById('tx_diskon').innerText = '- ' + fmt(diskon);
            document.getElementById('tx_total').innerText = fmt(total);
            document.getElementById('tx_fee').innerText = fmt(fee);

            let itemsHtml = '';
            if (data.items && data.items.length > 0) {
                data.items.forEach(item => {
                    itemsHtml += `
                        <div class="flex justify-between items-center p-2 bg-slate-50 rounded-lg border border-slate-100">
                            <div>
                                <div class="font-bold text-slate-800">${item.product_name || item.barang_nama || 'Barang'}</div>
                                <div class="text-[10px] text-slate-400">Toko: ${item.vendor_name || '-'} | ${item.quantity} Unit</div>
                            </div>
                            <div class="font-bold text-slate-700">${fmt(item.price * item.quantity)}</div>
                        </div>
                    `;
                });
            } else {
                itemsHtml = '<div class="text-center text-slate-400 py-2">Rincian barang tidak ditemukan.</div>';
            }
            document.getElementById('tx_items').innerHTML = itemsHtml;
            document.getElementById('transaksiModal').classList.remove('hidden');
        }
        function closeTransaksiModal() { document.getElementById('transaksiModal').classList.add('hidden'); }

        function filterTransaksiTable() {
            const query = (document.getElementById('searchTransaksi')?.value || '').toLowerCase();
            document.querySelectorAll('.trx-row').forEach(row => {
                const inv = row.querySelector('.trx-invoice')?.innerText.toLowerCase() || '';
                const cust = row.querySelector('.trx-customer')?.innerText.toLowerCase() || '';
                row.style.display = (inv.includes(query) || cust.includes(query)) ? '' : 'none';
            });
        }

        function filterVendorTab() {
            const query = (document.getElementById('searchVendor')?.value || '').toLowerCase();
            document.querySelectorAll('.tab-vendor-row').forEach(row => {
                const shop = row.querySelector('.tab-vendor-shop')?.innerText.toLowerCase() || '';
                const owner = row.querySelector('.tab-vendor-owner')?.innerText.toLowerCase() || '';
                const email = row.querySelector('.tab-vendor-email')?.innerText.toLowerCase() || '';
                row.style.display = (shop.includes(query) || owner.includes(query) || email.includes(query)) ? '' : 'none';
            });
        }

        function filterCustomerTab() {
            const query = (document.getElementById('searchCustomer')?.value || '').toLowerCase();
            document.querySelectorAll('.tab-customer-row').forEach(row => {
                const name = row.querySelector('.tab-customer-name')?.innerText.toLowerCase() || '';
                const email = row.querySelector('.tab-customer-email')?.innerText.toLowerCase() || '';
                row.style.display = (name.includes(query) || email.includes(query)) ? '' : 'none';
            });
        }
    </script>
</body>
</html>
