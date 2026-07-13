<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Persetujuan Penarikan Dana - Admin Rentify</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-[#e0f2fe]/20 text-slate-800 flex h-screen overflow-hidden">

    <aside class="w-64 bg-white border-r border-slate-200 flex flex-col justify-between shadow-sm z-10 overflow-y-auto">
        <div>
            <div class="p-6 border-b border-slate-100 flex items-center space-x-3 bg-gradient-to-r from-[#e0f2fe] to-[#bae6fd]">
                <div class="w-9 h-9 bg-[#0369a1] rounded-xl flex items-center justify-center text-white shadow-md">
                    <i class="fas fa-layer-group text-sm"></i>
                </div>
                <div>
                    <h1 class="font-extrabold text-slate-800 tracking-tight text-sm">Rentify HQ</h1>
                    <p class="text-[10px] text-[#0369a1] font-bold uppercase tracking-wider">Super Administrator</p>
                </div>
            </div>
            
            <nav class="p-4 space-y-1">
                <a href="/admin/dashboard" class="flex items-center space-x-3 px-4 py-3 text-sm font-semibold text-slate-600 hover:bg-slate-50 hover:text-slate-900 rounded-xl transition duration-200">
                    <i class="fas fa-chart-pie text-slate-400"></i>
                    <span>Utama Dashboard</span>
                </a>

                <div class="pt-4 px-4 pb-2 text-[10px] font-extrabold text-slate-400 uppercase tracking-widest">Keuangan & Saldo</div>
                <a href="{{ route('admin.penarikan.index') }}" class="flex items-center space-x-3 px-4 py-3 text-sm font-bold bg-[#e0f2fe] text-[#0369a1] rounded-xl transition duration-200">
                    <i class="fas fa-money-bill-transfer text-base"></i>
                    <span>Persetujuan Penarikan</span>
                </a>
                <a href="/admin/dashboard#kelola-transaksi" class="flex items-center space-x-3 px-4 py-3 text-sm font-semibold text-slate-600 hover:bg-slate-50 hover:text-slate-900 rounded-xl transition duration-200">
                    <i class="fas fa-file-invoice-dollar text-slate-400"></i>
                    <span>Daftar Semua Transaksi</span>
                </a>

                <div class="pt-4 px-4 pb-2 text-[10px] font-extrabold text-slate-400 uppercase tracking-widest">Akses Cepat Master Data</div>
                <a href="/admin/dashboard#kelola-produk" class="flex items-center space-x-3 px-4 py-3 text-sm font-semibold text-slate-600 hover:bg-slate-50 hover:text-slate-900 rounded-xl transition duration-200">
                    <i class="fas fa-arrow-left text-slate-400"></i>
                    <span>Kembali ke Master Data</span>
                </a>
            </nav>
        </div>

        <div class="p-4 border-t border-slate-100">
            <div class="flex items-center justify-between p-3 bg-slate-50 rounded-xl border border-slate-100 mb-2">
                <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 bg-slate-200 rounded-lg flex items-center justify-center text-slate-600 font-bold text-xs shadow-inner">AD</div>
                    <div class="truncate">
                        <h4 class="text-xs font-bold text-slate-700 truncate">Admin Rentify</h4>
                    </div>
                </div>
                <form action="/logout" method="POST">
                    @csrf
                    <button type="submit" class="text-rose-500 hover:text-rose-700 p-2">
                        <i class="fas fa-sign-out-alt"></i>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <main class="flex-1 flex flex-col overflow-hidden bg-slate-50/50">
        
        <header class="h-16 bg-white border-b border-slate-200 px-8 flex items-center justify-between z-0 shadow-sm">
            <div class="flex items-center space-x-3">
                <h2 class="text-lg font-black text-slate-800 tracking-tight">Manajemen Pencairan Saldo Vendor</h2>
            </div>
            <div class="text-xs font-medium text-slate-400 flex items-center space-x-2">
                <i class="far fa-calendar-alt"></i>
                <span>{{ date('d M Y') }}</span>
            </div>
        </header>

        <div class="flex-1 overflow-y-auto p-8 space-y-6">
            
            {{-- Alert Notifikasi Laporan --}}
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

            <section class="bg-white rounded-2xl border border-slate-200/80 shadow-3xs overflow-hidden">
                <div class="p-6 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
                    <h3 class="text-sm font-black text-slate-800 uppercase tracking-wider flex items-center">
                        <span class="w-1.5 h-3.5 bg-emerald-500 rounded-full mr-2"></span>Antrean Permintaan Withdraw
                    </h3>
                    <p class="text-xs text-slate-500 font-medium">Harap transfer dana sebelum menekan tombol Setujui.</p>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-slate-200 bg-slate-100/60 text-[10px] font-bold text-slate-500 uppercase tracking-wider">
                                <th class="p-4 pl-6">Tanggal Permintaan</th>
                                <th class="p-4">Informasi Vendor</th>
                                <th class="p-4">Nominal Tarik</th>
                                <th class="p-4">Tujuan Transfer (Bank/E-Wallet)</th>
                                <th class="p-4 text-center">Status Laporan</th>
                                <th class="p-4 text-center">Tindakan Admin</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm">
                            @forelse($listPenarikan as $penarikan)
                                <tr class="hover:bg-slate-50/60 transition">
                                    
                                    <td class="p-4 pl-6">
                                        <p class="font-bold text-slate-700">{{ $penarikan->created_at->format('d M Y') }}</p>
                                        <p class="text-[10px] text-slate-400 font-semibold">{{ $penarikan->created_at->format('H:i') }} WIB</p>
                                    </td>
                                    
                                    <td class="p-4">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-8 h-8 rounded-full bg-slate-200 flex items-center justify-center text-slate-600 font-bold text-xs">
                                                <i class="fas fa-store"></i>
                                            </div>
                                            <div>
                                                <p class="font-bold text-slate-800">{{ $penarikan->vendor->vendor_name ?? 'Vendor' }}</p>
                                                <p class="text-[10px] text-slate-500">{{ $penarikan->vendor->name ?? 'Pemilik Tidak Ditemukan' }}</p>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="p-4">
                                        <p class="font-black text-emerald-600 text-base tracking-tight">
                                            Rp {{ number_format($penarikan->nominal, 0, ',', '.') }}
                                        </p>
                                    </td>

                                    <td class="p-4">
                                        <div class="bg-slate-50 p-2.5 rounded-lg border border-slate-100 inline-block">
                                            <p class="text-xs font-bold text-slate-800 uppercase tracking-wider mb-1">
                                                <i class="fas fa-building-columns text-slate-400 mr-1"></i> {{ $penarikan->metode }} - {{ $penarikan->nama_bank_ewallet }}
                                            </p>
                                            <p class="text-xs font-medium text-slate-600">No. Rek: <span class="font-mono text-indigo-600 font-bold">{{ $penarikan->nomor_rekening }}</span></p>
                                            <p class="text-[10px] font-medium text-slate-500 mt-0.5">A.N: {{ $penarikan->nama_pemilik }}</p>
                                        </div>
                                    </td>

                                    <td class="p-4 text-center">
                                        @if($penarikan->status === 'pending')
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200 uppercase tracking-wider shadow-sm">
                                                <i class="fas fa-clock mr-1"></i> Tertunda
                                            </span>
                                        @elseif($penarikan->status === 'disetujui')
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 uppercase tracking-wider shadow-sm">
                                                <i class="fas fa-check mr-1"></i> Disetujui
                                            </span>
                                        @elseif($penarikan->status === 'ditolak')
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200 uppercase tracking-wider shadow-sm">
                                                <i class="fas fa-xmark mr-1"></i> Ditolak
                                            </span>
                                        @endif
                                    </td>

                                    <td class="p-4 text-center">
                                        @if($penarikan->status === 'pending')
                                            <div class="flex items-center justify-center space-x-2">
                                                {{-- Tombol Setujui --}}
                                                <form action="{{ route('admin.penarikan.approve', $penarikan->id) }}" method="POST" onsubmit="return confirm('YAKIN SETUJUI?\n\nPastikan Anda SUDAH mentransfer uang sebesar Rp {{ number_format($penarikan->nominal, 0, ',', '.') }} ke rekening {{ $penarikan->nama_bank_ewallet }} tersebut sebelum menekan OK.');">
                                                    @csrf
                                                    <button type="submit" class="bg-emerald-500 hover:bg-emerald-600 text-white text-[11px] font-bold py-2 px-3 rounded-lg shadow-sm transition duration-150 flex items-center">
                                                        Setujui
                                                    </button>
                                                </form>

                                                {{-- Tombol Tolak --}}
                                                <form action="{{ route('admin.penarikan.reject', $penarikan->id) }}" method="POST" onsubmit="return confirm('TOLAK PENARIKAN?\n\nUang akan dikembalikan secara otomatis ke Saldo Aktif Vendor.');">
                                                    @csrf
                                                    <button type="submit" class="bg-rose-500 hover:bg-rose-600 text-white text-[11px] font-bold py-2 px-3 rounded-lg shadow-sm transition duration-150 flex items-center">
                                                        Tolak
                                                    </button>
                                                </form>
                                            </div>
                                        @else
                                            <span class="text-slate-300 font-bold text-xs italic"><i class="fas fa-lock text-[10px] mr-1"></i>Selesai</span>
                                        @endif
                                    </td>

                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="p-12 text-center">
                                        <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-3">
                                            <i class="fas fa-check-double text-2xl text-slate-300"></i>
                                        </div>
                                        <p class="text-sm font-bold text-slate-500">Antrean Bersih</p>
                                        <p class="text-xs text-slate-400 mt-1">Belum ada vendor yang mengajukan penarikan dana hari ini.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </main>

</body>
</html>