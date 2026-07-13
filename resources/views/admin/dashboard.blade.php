<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pusat Kendali - Admin Rentify</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        /* Transisi halus saat ganti tab */
        .tab-content { animation: fadeIn 0.3s ease-in-out; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</head>
<body class="bg-[#f8fafc] text-slate-800 flex h-screen overflow-hidden">

    <aside class="w-64 bg-white border-r border-slate-200 flex flex-col justify-between shadow-sm z-10 overflow-y-auto">
        <div>
            <div class="p-6 border-b border-slate-100 flex items-center space-x-3 bg-gradient-to-r from-[#e0f2fe] to-[#bae6fd]">
                <div class="w-10 h-10 bg-white rounded-xl flex items-center justify-center shadow-md p-1 overflow-hidden border border-sky-100">
                    <img src="{{ asset('images/logo.png') }}" alt="Logo" class="w-full h-full object-contain" onerror="this.outerHTML='<i class=\'fas fa-briefcase text-sky-600 text-lg\'></i>'">
                </div>
                <div>
                    <h1 class="font-black text-slate-800 tracking-tight text-[15px]">Rentify Admin</h1>
                    <p class="text-[10px] text-[#0369a1] font-bold uppercase tracking-wider">Management Center</p>
                </div>
            </div>
            
            <nav class="p-4 space-y-1">
                <button onclick="switchTab('tab-dashboard', 'Pusat Kendali Sistem Utama')" class="w-full flex items-center space-x-3 px-4 py-3 text-sm font-bold bg-[#e0f2fe] text-[#0369a1] rounded-xl transition duration-200 focus:outline-none menu-btn" id="btn-tab-dashboard">
                    <i class="fas fa-chart-pie text-base w-5"></i>
                    <span>Tinjauan Dasbor</span>
                </button>

                <div class="pt-4 px-4 pb-2 text-[10px] font-extrabold text-slate-400 uppercase tracking-widest">Keuangan & Pesanan</div>
                <a href="{{ route('admin.penarikan.index') }}" class="flex items-center space-x-3 px-4 py-3 text-sm font-semibold text-slate-600 hover:bg-slate-50 hover:text-slate-900 rounded-xl transition duration-200">
                    <i class="fas fa-money-bill-transfer text-slate-400 w-5"></i>
                    <span>Pencairan Dana</span>
                </a>
                <button onclick="switchTab('tab-transaksi', 'Manajemen Transaksi Pesanan')" class="w-full flex items-center space-x-3 px-4 py-3 text-sm font-semibold text-slate-600 hover:bg-slate-50 hover:text-slate-900 rounded-xl transition duration-200 focus:outline-none menu-btn" id="btn-tab-transaksi">
                    <i class="fas fa-file-invoice-dollar text-slate-400 w-5"></i>
                    <span>Data Transaksi</span>
                </button>

                <div class="pt-4 px-4 pb-2 text-[10px] font-extrabold text-slate-400 uppercase tracking-widest">Manajemen Master Data</div>
                <button onclick="switchTab('tab-produk', 'Master Data: Seluruh Produk')" class="w-full flex items-center space-x-3 px-4 py-3 text-sm font-semibold text-slate-600 hover:bg-slate-50 hover:text-slate-900 rounded-xl transition duration-200 focus:outline-none menu-btn" id="btn-tab-produk">
                    <i class="fas fa-boxes text-slate-400 w-5"></i>
                    <span>Inventaris Produk</span>
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
                        <p class="text-[9px] text-slate-400 truncate">Online</p>
                    </div>
                </div>
                <form action="/logout" method="POST">
                    @csrf
                    <button type="submit" class="text-rose-500 hover:text-rose-700 hover:bg-rose-50 p-2 rounded-lg transition">
                        <i class="fas fa-sign-out-alt"></i>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <main class="flex-1 flex flex-col overflow-hidden">
        
        <header class="h-16 bg-white border-b border-slate-200 px-8 flex items-center justify-between z-0 shadow-sm">
            <div class="flex items-center space-x-3">
                <h2 id="header-title" class="text-lg font-black text-slate-800 tracking-tight">Pusat Kendali Sistem Utama</h2>
                <span class="text-[10px] font-bold bg-emerald-50 text-emerald-600 border border-emerald-200 px-2.5 py-0.5 rounded-full flex items-center shadow-sm">
                    <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full mr-1.5 animate-pulse"></span> Server Normal
                </span>
            </div>
            <div class="text-xs font-bold text-slate-500 flex items-center space-x-2 bg-slate-50 px-3 py-1.5 rounded-lg border border-slate-100">
                <i class="far fa-calendar-alt text-[#0369a1]"></i>
                <span>{{ date('d M Y') }}</span>
            </div>
        </header>

        <div class="flex-1 overflow-y-auto p-8">
            
            {{-- Alert Notifikasi --}}
            @if(session('success'))
            <div class="mb-6 bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl text-sm font-medium flex items-center shadow-sm">
                <i class="fas fa-check-circle text-emerald-500 mr-2 text-base"></i>{{ session('success') }}
            </div>
            @endif
            @if(session('error'))
            <div class="mb-6 bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 rounded-xl text-sm font-medium flex items-center shadow-sm">
                <i class="fas fa-exclamation-circle text-rose-500 mr-2 text-base"></i>{{ session('error') }}
            </div>
            @endif

            <div id="tab-dashboard" class="tab-content block space-y-8">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm relative overflow-hidden">
                        <div class="absolute top-0 right-0 p-4 opacity-10"><i class="fas fa-boxes text-5xl"></i></div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Produk Pending</p>
                        <p class="text-3xl font-black text-amber-500 mt-1">{{ $stats['total_pending'] ?? 0 }}</p>
                    </div>
                    <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm relative overflow-hidden">
                        <div class="absolute top-0 right-0 p-4 opacity-10"><i class="fas fa-check-circle text-5xl"></i></div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Produk Aktif</p>
                        <p class="text-3xl font-black text-[#0369a1] mt-1">{{ $stats['total_disetujui'] ?? 0 }}</p>
                    </div>
                    <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm relative overflow-hidden">
                        <div class="absolute top-0 right-0 p-4 opacity-10"><i class="fas fa-store text-5xl"></i></div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Total Vendor</p>
                        <p class="text-3xl font-black text-slate-800 mt-1">{{ $stats['total_vendor_total'] ?? 0 }}</p>
                    </div>
                    <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm relative overflow-hidden">
                        <div class="absolute top-0 right-0 p-4 opacity-10"><i class="fas fa-users text-5xl"></i></div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Total Pelanggan</p>
                        <p class="text-3xl font-black text-slate-800 mt-1">{{ $stats['total_customer'] ?? 0 }}</p>
                    </div>
                </div>

                <section class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
                    <div class="p-5 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
                        <h3 class="text-sm font-black text-slate-800 uppercase tracking-wider"><i class="fas fa-clipboard-check text-amber-500 mr-2"></i>Antrean Kurasi Produk</h3>
                        <span class="bg-amber-100 text-amber-800 text-[10px] px-2 py-1 rounded-md font-bold">{{ $pendingBarangs->count() }} Butuh Review</span>
                    </div>
                    <div class="p-0 overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b border-slate-100 text-[10px] font-bold text-slate-400 uppercase tracking-wider bg-white">
                                    <th class="p-4 pl-6">Nama Barang</th>
                                    <th class="p-4">Toko Vendor</th>
                                    <th class="p-4 text-center">Data Produk</th>
                                    <th class="p-4 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-sm">
                                @forelse($pendingBarangs as $barang)
                                <tr class="hover:bg-slate-50/30 transition">
                                    <td class="p-4 pl-6 font-bold text-slate-700">{{ $barang->nama }}</td>
                                    <td class="p-4 text-slate-500">{{ $barang->vendor->vendor_name ?? 'Umum' }}</td>
                                    <td class="p-4 text-center">
                                        <button onclick="openProductModal('{{ json_encode($barang) }}')" class="bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold px-3 py-1.5 rounded-lg text-xs transition">
                                            <i class="fas fa-eye mr-1"></i> Review
                                        </button>
                                    </td>
                                    <td class="p-4 flex justify-center space-x-2">
                                        <form action="/admin/barang/{{ $barang->id }}/approve" method="POST">
                                            @csrf @method('PATCH')
                                            <button type="submit" class="bg-emerald-500 hover:bg-emerald-600 text-white font-bold px-3 py-1.5 rounded-lg text-xs shadow-sm transition">Setuju</button>
                                        </form>
                                        <form action="/admin/barang/{{ $barang->id }}/reject" method="POST">
                                            @csrf @method('PATCH')
                                            <button type="submit" class="bg-rose-500 hover:bg-rose-600 text-white font-bold px-3 py-1.5 rounded-lg text-xs shadow-sm transition">Tolak</button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="4" class="py-8 text-center text-slate-400 text-xs"><i class="fas fa-check-double text-2xl text-slate-200 block mb-2"></i>Antrean kosong. Hebat!</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <section class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden flex flex-col">
                        <div class="p-5 border-b border-slate-100 bg-slate-50/50">
                            <h3 class="text-sm font-black text-slate-800 uppercase tracking-wider"><i class="fas fa-user-check text-purple-500 mr-2"></i>Pendaftaran Vendor Baru</h3>
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
                                            <p class="font-bold text-slate-800 text-sm">{{ $vendor->vendor_name ?? '-' }}</p>
                                            <p class="text-[10px] text-slate-400">{{ $vendor->name }}</p>
                                        </td>
                                        <td class="p-4 text-center space-x-1">
                                            <button onclick="openVendorModal('{{ json_encode($vendor) }}')" class="bg-purple-50 hover:bg-purple-100 text-purple-700 p-2 rounded-lg transition" title="Lihat Detail"><i class="fas fa-eye"></i></button>
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

                    <section class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden flex flex-col">
                        <div class="p-5 border-b border-slate-100 bg-slate-50/50">
                            <h3 class="text-sm font-black text-slate-800 uppercase tracking-wider"><i class="fas fa-images text-blue-500 mr-2"></i>Manajemen Banner Aplikasi</h3>
                        </div>
                        <div class="p-5 flex-1">
                            <form action="/admin/banner" method="POST" enctype="multipart/form-data" class="flex gap-3 mb-6 bg-slate-50 p-3 rounded-xl border border-slate-100">
                                @csrf
                                <div class="flex-1 space-y-2">
                                    <input type="text" name="judul_promo" placeholder="Judul Banner Promo..." required class="w-full px-3 py-2 text-xs bg-white border border-slate-200 rounded-lg outline-none focus:border-blue-400">
                                    <input type="file" name="gambar" required class="w-full text-[10px] text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:bg-blue-50 file:text-blue-700 file:font-bold">
                                </div>
                                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold px-4 rounded-lg text-xs shadow-sm transition">Upload</button>
                            </form>
                            
                            <div class="grid grid-cols-2 gap-3 max-h-[160px] overflow-y-auto pr-1">
                                @forelse($banners as $banner)
                                <div class="border border-slate-100 rounded-xl overflow-hidden bg-white relative group">
                                    <img src="{{ $banner->gambar_url }}" class="w-full h-20 object-cover">
                                    <div class="absolute inset-0 bg-black/60 flex items-center justify-center opacity-0 group-hover:opacity-100 transition">
                                        <form action="/admin/banner/{{ $banner->id }}" method="POST">
                                            @csrf @method('DELETE')
                                            <button type="submit" onclick="return confirm('Padam banner ini?')" class="bg-rose-500 text-white px-3 py-1 rounded-md text-xs font-bold"><i class="fas fa-trash mr-1"></i> Hapus</button>
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

            <div id="tab-transaksi" class="tab-content hidden">
                <section class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
                    <div class="p-6 border-b border-slate-100 flex justify-between items-center">
                        <h3 class="text-base font-black text-slate-800">Database Transaksi Keseluruhan</h3>
                        <div class="relative">
                            <i class="fas fa-search absolute left-3 top-2.5 text-slate-300"></i>
                            <input type="text" placeholder="Cari ID Pesanan..." class="pl-9 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-blue-400">
                        </div>
                    </div>
                    <div class="p-0 overflow-x-auto">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr class="border-b border-slate-200 bg-slate-50/50 font-bold text-slate-500 uppercase tracking-wider">
                                    <th class="p-4 pl-6">Invoice</th>
                                    <th class="p-4">Customer</th>
                                    <th class="p-4">Total Biaya</th>
                                    <th class="p-4">Tanggal Order</th>
                                    <th class="p-4 text-center">Status</th>
                                    <th class="p-4 text-center">Tindakan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse($allTransaksi ?? [] as $trx)
                                <tr class="hover:bg-slate-50/60 transition">
                                    <td class="p-4 pl-6 font-black text-slate-700">#{{ $trx->kode_penyewaan ?? $trx->kode_transaksi ?? $trx->id ?? '...' }}</td>
                                    <td class="p-4 font-bold text-slate-600">{{ $trx->user->name ?? $trx->customer->name ?? 'User' }}</td>
                                    <td class="p-4 font-bold text-emerald-600 text-sm">Rp {{ number_format($trx->total_harga ?? $trx->total_bayar ?? 0, 0, ',', '.') }}</td>
                                    <td class="p-4 text-slate-500">
                                    {{ $trx->created_at ? \Carbon\Carbon::parse($trx->created_at)->format('d M Y, H:i') : '-' }}
                                    </td>
                                    <td class="p-4 text-center">
                                        <span class="px-2.5 py-1 rounded-md text-[10px] font-bold uppercase tracking-wider
                                            {{ strtolower($trx->status ?? '') == 'selesai' ? 'bg-emerald-100 text-emerald-700' : 'bg-blue-100 text-blue-700' }}">
                                            {{ $trx->status ?? $trx->status_pembayaran ?? 'Pending' }}
                                        </span>
                                    </td>
                                    <td class="p-4 text-center">
                                        <button onclick="alert('Fitur Struk Dinamis Admin menyusul!')" class="bg-slate-100 hover:bg-slate-200 text-slate-600 px-3 py-1.5 rounded-lg text-xs font-bold transition shadow-sm">
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

            <div id="tab-produk" class="tab-content hidden">
                <section class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
                    <div class="p-6 border-b border-slate-100">
                        <h3 class="text-base font-black text-slate-800">Master Data: Inventaris Barang</h3>
                    </div>
                    <div class="p-0 overflow-x-auto">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr class="border-b border-slate-200 bg-slate-50/50 font-bold text-slate-500 uppercase tracking-wider">
                                    <th class="p-4 pl-6">Foto</th>
                                    <th class="p-4">Nama Produk</th>
                                    <th class="p-4">Milik Toko</th>
                                    <th class="p-4">Sewa/Hari</th>
                                    <th class="p-4">Gudang</th>
                                    <th class="p-4 text-center">Katalog</th>
                                    <th class="p-4 text-center">Aksi Danger</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($allBarangs as $p)
                                <tr class="hover:bg-slate-50/60 transition">
                                    <td class="p-4 pl-6">
                                        <div class="w-12 h-12 rounded-lg border border-slate-200 overflow-hidden bg-white shadow-sm">
                                            <img src="{{ $p->cover_photo ? Storage::url($p->cover_photo) : 'https://placehold.co/50' }}" class="w-full h-full object-cover">
                                        </div>
                                    </td>
                                    <td class="p-4 font-bold text-slate-800 text-sm max-w-[200px] truncate">{{ $p->nama }}</td>
                                    <td class="p-4 font-semibold text-[#0369a1]"><i class="fas fa-store text-slate-300 mr-1"></i> {{ $p->vendor->vendor_name ?? 'Umum' }}</td>
                                    <td class="p-4 font-bold text-slate-700">Rp {{ number_format($p->harga_sewa_harian, 0, ',', '.') }}</td>
                                    <td class="p-4 font-bold text-slate-600">{{ $p->stok_total }}</td>
                                    <td class="p-4 text-center">
                                        <span class="px-2 py-1 rounded-md text-[9px] font-black uppercase tracking-widest border {{ $p->status_barang == 'disetujui' ? 'bg-emerald-50 text-emerald-600 border-emerald-200' : ($p->status_barang == 'pending' ? 'bg-amber-50 text-amber-600 border-amber-200' : 'bg-rose-50 text-rose-600 border-rose-200') }}">
                                            {{ $p->status_barang ?? 'pending' }}
                                        </span>
                                    </td>
                                    <td class="p-4 text-center">
                                        <form action="/admin/barang/{{ $p->id }}/delete" method="POST" onsubmit="return confirm('WARNING: Hapus produk ini permanen dari database?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="bg-rose-50 text-rose-600 hover:bg-rose-500 hover:text-white px-3 py-1.5 rounded-lg font-bold transition shadow-sm"><i class="far fa-trash-alt"></i></button>
                                        </form>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>

            <div id="tab-vendor" class="tab-content hidden">
                <section class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
                    <div class="p-6 border-b border-slate-100">
                        <h3 class="text-base font-black text-slate-800">Master Data: Direktori Mitra Vendor</h3>
                    </div>
                    <div class="p-0 overflow-x-auto">
                        <table class="w-full text-left border-collapse text-sm">
                            <thead>
                                <tr class="border-b border-slate-200 bg-slate-50/50 font-bold text-slate-500 uppercase tracking-wider text-xs">
                                    <th class="p-4 pl-6">Profil Toko</th>
                                    <th class="p-4">Identitas Pemilik</th>
                                    <th class="p-4">Kontak (Email/WA)</th>
                                    <th class="p-4 text-center">Lisensi</th>
                                    <th class="p-4 text-center">Blokir</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($allVendors as $v)
                                <tr class="hover:bg-slate-50/60 transition">
                                    <td class="p-4 pl-6 font-black text-slate-800">
                                        <i class="fas fa-store-alt text-[#0369a1] text-lg mr-2 align-middle"></i> {{ $v->vendor_name ?? '-' }}
                                    </td>
                                    <td class="p-4 font-bold text-slate-600">{{ $v->name }}</td>
                                    <td class="p-4">
                                        <p class="font-mono text-slate-500 text-xs">{{ $v->email }}</p>
                                        <p class="text-emerald-600 font-bold text-xs mt-1"><i class="fab fa-whatsapp"></i> {{ $v->whatsapp_vendor ?? '-' }}</p>
                                    </td>
                                    <td class="p-4 text-center">
                                        <span class="px-2.5 py-1 rounded-md text-[10px] font-bold uppercase tracking-wider {{ $v->vendor_status == 'approved' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                            {{ $v->vendor_status }}
                                        </span>
                                    </td>
                                    <td class="p-4 text-center">
                                        <form action="/admin/user/{{ $v->id }}/delete" method="POST" onsubmit="return confirm('Hapus/Blokir vendor ini? Semua barang mereka akan hilang.')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="bg-slate-100 hover:bg-rose-500 hover:text-white text-slate-500 px-3 py-1.5 rounded-lg font-bold transition shadow-sm"><i class="fas fa-ban"></i></button>
                                        </form>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>

            <div id="tab-customer" class="tab-content hidden">
                <section class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
                    <div class="p-6 border-b border-slate-100 flex justify-between items-center">
                        <h3 class="text-base font-black text-slate-800">Master Data: Direktori Pelanggan</h3>
                        <span class="bg-sky-100 text-sky-800 text-xs font-bold px-3 py-1 rounded-full">{{ $allCustomers->count() }} Pengguna Terdaftar</span>
                    </div>
                    <div class="p-0 overflow-x-auto">
                        <table class="w-full text-left border-collapse text-sm">
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
                                <tr class="hover:bg-slate-50/60 transition">
                                    <td class="p-4 pl-6 flex items-center space-x-3">
                                        <div class="w-10 h-10 bg-slate-200 rounded-full flex items-center justify-center text-slate-500 font-bold text-lg"><i class="fas fa-user"></i></div>
                                        <span class="font-bold text-slate-800">{{ $c->name }}</span>
                                    </td>
                                    <td class="p-4 font-mono text-slate-500">{{ $c->email }}</td>
                                    <td class="p-4 font-medium text-slate-500">{{ $c->created_at?->format('d M Y') ?? '-' }}</td>
                                    <td class="p-4 text-center">
                                        <form action="/admin/user/{{ $c->id }}/delete" method="POST" onsubmit="return confirm('Hapus permanen akun pelanggan ini dari database?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-rose-500 hover:text-rose-700 font-bold bg-rose-50 px-3 py-1.5 rounded-lg transition shadow-sm">Hapus</button>
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

    <div id="productModal" class="fixed inset-0 bg-slate-900/60 hidden flex items-center justify-center z-50 p-4 backdrop-blur-sm">
        <div class="bg-white rounded-2xl max-w-2xl w-full p-6 shadow-2xl max-h-[90vh] overflow-y-auto border-t-4 border-[#0369a1]">
            <div class="flex justify-between items-center border-b pb-3 mb-4">
                <h3 class="font-black text-lg text-slate-800 uppercase tracking-wider">Detail Formulir Pengajuan Produk</h3>
                <button onclick="closeProductModal()" class="text-slate-400 hover:text-rose-500 text-2xl transition">&times;</button>
            </div>
            <div class="space-y-4 text-sm">
                <div class="grid grid-cols-2 gap-4 bg-slate-50 p-4 rounded-xl border border-slate-100">
                    <div><p class="text-[10px] text-slate-400 font-bold uppercase">NAMA BARANG</p><p id="md_nama" class="font-black text-slate-800 text-base"></p></div>
                    <div><p class="text-[10px] text-slate-400 font-bold uppercase">KONDISI FISIK</p><p id="md_kondisi" class="font-black text-amber-600 text-base"></p></div>
                </div>
                <div><p class="text-xs text-slate-500 font-bold mb-1 ml-1"><i class="fas fa-align-left mr-1"></i> DESKRIPSI PRODUK</p><p id="md_deskripsi" class="text-slate-600 bg-slate-50 p-4 rounded-xl text-xs leading-relaxed border border-slate-100"></p></div>
                <div class="grid grid-cols-3 gap-3 text-center">
                    <div class="p-3 bg-blue-50 border border-blue-100 rounded-xl"><p class="text-[9px] text-blue-600 font-black tracking-widest uppercase">TARIF SEWA / HARI</p><p id="md_harga" class="font-black text-blue-900 text-lg"></p></div>
                    <div class="p-3 bg-emerald-50 border border-emerald-100 rounded-xl"><p class="text-[9px] text-emerald-600 font-black tracking-widest uppercase">DEPOSIT JAMINAN</p><p id="md_deposit" class="font-black text-emerald-900 text-lg"></p></div>
                    <div class="p-3 bg-rose-50 border border-rose-100 rounded-xl"><p class="text-[9px] text-rose-600 font-black tracking-widest uppercase">DENDA TELAT / HARI</p><p id="md_denda" class="font-black text-rose-900 text-lg"></p></div>
                </div>
                <div class="bg-slate-50 border border-slate-100 p-4 rounded-xl flex justify-between items-center"><p class="text-xs text-slate-500 font-bold">KAPASITAS GUDANG / STOK</p><p id="md_stok" class="font-black text-slate-800 text-lg bg-white px-4 py-1 rounded-lg shadow-sm"></p></div>
            </div>
        </div>
    </div>

    <div id="vendorModal" class="fixed inset-0 bg-slate-900/60 hidden flex items-center justify-center z-50 p-4 backdrop-blur-sm">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border-t-4 border-purple-500">
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
        </div>
    </div>

    <script>
        // SCRIPT UNTUK SISTEM TAB HALAMAN (SINGLE PAGE APP STYLE)
        function switchTab(tabId, titleText) {
            // 1. Sembunyikan semua tab konten
            document.querySelectorAll('.tab-content').forEach(el => {
                el.classList.add('hidden');
                el.classList.remove('block');
            });
            
            // 2. Tampilkan tab yang dipilih
            document.getElementById(tabId).classList.remove('hidden');
            document.getElementById(tabId).classList.add('block');
            
            // 3. Ubah Judul Header Atas
            document.getElementById('header-title').innerText = titleText;

            // 4. Ubah warna tombol menu aktif di sidebar (Opsional agar terlihat lebih interaktif)
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

        // Script Modals
        function openProductModal(dataString) {
            const data = JSON.parse(dataString);
            document.getElementById('md_nama').innerText = data.nama || '-';
            document.getElementById('md_kondisi').innerText = data.kondisi || '-';
            document.getElementById('md_deskripsi').innerText = data.deskripsi || 'Tidak ada deskripsi.';
            document.getElementById('md_stok').innerText = (data.stok_total || '0') + ' Unit Tersedia';
            const fmt = (val) => 'Rp ' + parseFloat(val).toLocaleString('id-ID');
            document.getElementById('md_harga').innerText = fmt(data.harga_sewa_harian || 0);
            document.getElementById('md_deposit').innerText = fmt(data.deposit || 0);
            document.getElementById('md_denda').innerText = fmt(data.denda_per_hari || 0);
            document.getElementById('productModal').classList.remove('hidden');
        }
        function closeProductModal() { document.getElementById('productModal').classList.add('hidden'); }

        function openVendorModal(dataString) {
            const data = JSON.parse(dataString);
            document.getElementById('vd_tokoname').innerText = data.vendor_name || '-';
            document.getElementById('vd_owner').innerText = data.name || '-';
            document.getElementById('vd_email').innerText = data.email || '-';
            document.getElementById('vd_wa').innerText = data.whatsapp_vendor || '-';
            document.getElementById('vendorModal').classList.remove('hidden');
        }
        function closeVendorModal() { document.getElementById('vendorModal').classList.add('hidden'); }
    </script>
</body>
</html>