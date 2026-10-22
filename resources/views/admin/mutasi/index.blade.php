<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Auto-Detect Mutasi QRIS (24/7) - Admin Rentify</title>
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
                <div class="w-10 h-10 rentify-card flex items-center justify-center p-1 overflow-hidden border border-sky-100 bg-white rounded-xl">
                    <img src="{{ asset('images/logo.png') }}" alt="Logo" class="w-full h-full object-contain" onerror="this.outerHTML='<i class=\'fas fa-briefcase text-sky-600 text-lg\'></i>'">
                </div>
                <div>
                    <h1 class="font-black text-slate-800 tracking-tight text-[15px]">Rentify Admin</h1>
                    <p class="text-[10px] text-[#0369a1] font-bold uppercase tracking-wider">Auto-Mutasi Center</p>
                </div>
            </div>
            
            <nav class="p-4 space-y-1">
                <a href="/admin/dashboard" class="flex items-center space-x-3 px-4 py-3 text-sm font-semibold text-slate-600 hover:bg-slate-50 hover:text-slate-900 rounded-xl transition duration-200">
                    <i class="fas fa-chart-pie text-slate-400 w-5"></i>
                    <span>Tinjauan Dasbor</span>
                </a>

                <div class="pt-4 px-4 pb-2 text-[10px] font-extrabold text-slate-400 uppercase tracking-widest">Keuangan & Pembayaran</div>
                <a href="{{ route('admin.pembayaran') }}" class="flex items-center space-x-3 px-4 py-3 text-sm font-semibold text-slate-600 hover:bg-slate-50 hover:text-slate-900 rounded-xl transition duration-200">
                    <i class="fas fa-credit-card text-slate-400 w-5"></i>
                    <span>Verifikasi Pembayaran</span>
                </a>
                <a href="{{ route('admin.mutasi.index') }}" class="flex items-center space-x-3 px-4 py-3 text-sm font-bold bg-[#e0f2fe] text-[#0369a1] rounded-xl transition duration-200">
                    <i class="fas fa-bolt text-[#0369a1] w-5"></i>
                    <span>Auto-Detect Mutasi</span>
                </a>
                <a href="{{ route('admin.penarikan.index') }}" class="flex items-center space-x-3 px-4 py-3 text-sm font-semibold text-slate-600 hover:bg-slate-50 hover:text-slate-900 rounded-xl transition duration-200">
                    <i class="fas fa-money-bill-transfer text-slate-400 w-5"></i>
                    <span>Pencairan Dana</span>
                </a>
            </nav>
        </div>

        <div class="p-4 border-t border-slate-100">
            <a href="/" class="flex items-center space-x-3 px-4 py-2.5 text-xs font-bold text-slate-500 hover:bg-slate-50 rounded-xl transition">
                <i class="fas fa-arrow-left"></i>
                <span>Kembali ke Website</span>
            </a>
        </div>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="flex-1 flex flex-col h-screen overflow-y-auto">
        <!-- TOPBAR -->
        <header class="bg-white border-b border-slate-200 px-8 py-4 flex items-center justify-between sticky top-0 z-20">
            <div>
                <h2 class="text-xl font-black text-slate-800">Auto-Detect Mutasi QRIS (Metode 2)</h2>
                <p class="text-xs text-slate-500">Verifikasi pembayaran otomatis 100% via Android Notification Forwarder 24/7</p>
            </div>
            <div class="flex items-center gap-3">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-extrabold bg-emerald-50 text-emerald-700 border border-emerald-200">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    Webhook Listener Aktif
                </span>
            </div>
        </header>

        <div class="p-8 space-y-6">

            <!-- FLASH ALERTS -->
            @if(session('success'))
            <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-2xl text-sm font-bold text-emerald-800 flex items-center gap-3">
                <i class="fas fa-circle-check text-emerald-600 text-lg"></i>
                <span>{{ session('success') }}</span>
            </div>
            @endif

            @if(session('warning'))
            <div class="p-4 bg-amber-50 border border-amber-200 rounded-2xl text-sm font-bold text-amber-800 flex items-center gap-3">
                <i class="fas fa-triangle-exclamation text-amber-600 text-lg"></i>
                <span>{{ session('warning') }}</span>
            </div>
            @endif

            @if(session('error'))
            <div class="p-4 bg-rose-50 border border-rose-200 rounded-2xl text-sm font-bold text-rose-800 flex items-center gap-3">
                <i class="fas fa-circle-xmark text-rose-600 text-lg"></i>
                <span>{{ session('error') }}</span>
            </div>
            @endif

            <!-- 1. STATS WIDGETS -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-5">
                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
                    <div class="flex items-center justify-between text-slate-400 mb-2">
                        <span class="text-xs font-bold uppercase tracking-wider">Total Notifikasi</span>
                        <div class="w-8 h-8 rounded-lg bg-sky-50 text-[#0284c7] flex items-center justify-center">
                            <i class="fas fa-bell text-xs"></i>
                        </div>
                    </div>
                    <div class="text-2xl font-black text-slate-800">{{ number_format($totalMutasi) }}</div>
                    <p class="text-[11px] text-slate-400 mt-1">Diterima dari HP Android</p>
                </div>

                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
                    <div class="flex items-center justify-between text-slate-400 mb-2">
                        <span class="text-xs font-bold uppercase tracking-wider">Lunas Otomatis</span>
                        <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                            <i class="fas fa-circle-check text-xs"></i>
                        </div>
                    </div>
                    <div class="text-2xl font-black text-emerald-600">{{ number_format($countMatched) }}</div>
                    <p class="text-[11px] text-slate-400 mt-1">Cocok dengan pesanan</p>
                </div>

                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
                    <div class="flex items-center justify-between text-slate-400 mb-2">
                        <span class="text-xs font-bold uppercase tracking-wider">Belum Terhubung</span>
                        <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center">
                            <i class="fas fa-clock text-xs"></i>
                        </div>
                    </div>
                    <div class="text-2xl font-black text-amber-600">{{ number_format($countUnmatched) }}</div>
                    <p class="text-[11px] text-slate-400 mt-1">Menunggu pencocokan</p>
                </div>

                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
                    <div class="flex items-center justify-between text-slate-400 mb-2">
                        <span class="text-xs font-bold uppercase tracking-wider">Total Dana Masuk</span>
                        <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center">
                            <i class="fas fa-wallet text-xs"></i>
                        </div>
                    </div>
                    <div class="text-2xl font-black text-[#0369a1]">Rp {{ number_format($totalNominalMasuk, 0, ',', '.') }}</div>
                    <p class="text-[11px] text-slate-400 mt-1">Terverifikasi sistem</p>
                </div>
            </div>

            <!-- 2. SETUP ANDROID & SIMULASI WIDGET -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                <!-- KARTU PANDUAN KONEKSI HP ANDROID -->
                <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-black text-slate-800 flex items-center gap-2">
                            <i class="fas fa-mobile-screen text-[#0284c7]"></i>
                            <span>Konfigurasi HP Android Forwarder</span>
                        </h3>
                        <span class="text-[10px] font-extrabold bg-blue-50 text-blue-700 px-2 py-0.5 rounded border border-blue-200">MacroDroid / Tasker</span>
                    </div>

                    <p class="text-xs text-slate-500 mb-4 leading-relaxed">
                        Pasang aplikasi <b>MacroDroid</b> atau <b>Notification Forwarder</b> pada HP toko Anda yang terpasang aplikasi <b>DANA Bisnis / BCA</b>, lalu atur aksi HTTP Request POST:
                    </p>

                    <div class="space-y-3 text-xs">
                        <div>
                            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block mb-1">Webhook URL (Endpoint Server)</span>
                            <div class="flex items-center gap-2">
                                <input type="text" readonly value="{{ $webhookUrl }}" id="input-webhook-url" class="flex-1 bg-slate-50 border border-slate-200 px-3 py-2 rounded-xl font-mono text-[11px] text-slate-700 select-all">
                                <button type="button" onclick="salinTeks('input-webhook-url', this)" class="bg-sky-50 text-[#0284c7] hover:bg-sky-100 font-extrabold px-3 py-2 rounded-xl transition border border-sky-200">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                        </div>

                        <div>
                            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block mb-1">Header Keamanan (Secret Key)</span>
                            <div class="flex items-center gap-2">
                                <input type="text" readonly value="{{ $webhookSecret }}" id="input-webhook-secret" class="flex-1 bg-slate-50 border border-slate-200 px-3 py-2 rounded-xl font-mono text-[11px] text-slate-700 select-all">
                                <button type="button" onclick="salinTeks('input-webhook-secret', this)" class="bg-sky-50 text-[#0284c7] hover:bg-sky-100 font-extrabold px-3 py-2 rounded-xl transition border border-sky-200">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                            <span class="text-[10px] text-slate-400 mt-1 block">Pasang di Header: <code>X-Webhook-Secret: {{ $webhookSecret }}</code></span>
                        </div>

                        <div class="p-3 bg-slate-50 rounded-xl border border-slate-100 font-mono text-[10.5px] text-slate-600">
                            <b>Format Body JSON MacroDroid:</b><br>
                            {<br>
                            &nbsp;&nbsp;"app": "[not_app_name]",<br>
                            &nbsp;&nbsp;"title": "[not_title]",<br>
                            &nbsp;&nbsp;"content": "[not_text]"<br>
                            }
                        </div>
                    </div>
                </div>

                <!-- KARTU PENGUJIAN / SIMULATOR NOTIFIKASI -->
                <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-black text-slate-800 flex items-center gap-2">
                            <i class="fas fa-flask text-amber-500"></i>
                            <span>Simulasi Notifikasi Masuk (Live Test)</span>
                        </h3>
                        <span class="text-[10px] font-extrabold bg-amber-50 text-amber-700 px-2 py-0.5 rounded border border-amber-200">Testing Mode</span>
                    </div>

                    <p class="text-xs text-slate-500 mb-4 leading-relaxed">
                        Uji verifikasi mutasi otomatis tanpa transfer uang asli. Sistem akan menyimulasikan notifikasi push dari HP:
                    </p>

                    <form action="{{ route('admin.mutasi.simulate') }}" method="POST" class="space-y-3">
                        @csrf
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block mb-1">Aplikasi / Bank</label>
                                <select name="app_name" class="w-full bg-slate-50 border border-slate-200 px-3 py-2 rounded-xl text-xs font-semibold focus:border-[#0284c7] outline-none">
                                    <option value="DANA">DANA Bisnis / QRIS</option>
                                    <option value="BCA">BCA Mobile / QRIS</option>
                                    <option value="GOPAY">GoPay / GoPay Merchant</option>
                                    <option value="SHOPEEPAY">ShopeePay</option>
                                    <option value="MANDIRI">Livin' Mandiri</option>
                                    <option value="BRI">BRImo</option>
                                </select>
                            </div>

                            <div>
                                <label class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block mb-1">Nominal (Rp)</label>
                                <input type="number" name="nominal" required placeholder="Contoh: 50142" class="w-full bg-slate-50 border border-slate-200 px-3 py-2 rounded-xl text-xs font-semibold focus:border-[#0284c7] outline-none">
                            </div>
                        </div>

                        <div>
                            <label class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block mb-1">Nama Pengirim (Opsional)</label>
                            <input type="text" name="sender" placeholder="Contoh: Budi Santoso" class="w-full bg-slate-50 border border-slate-200 px-3 py-2 rounded-xl text-xs font-semibold focus:border-[#0284c7] outline-none">
                        </div>

                        <button type="submit" class="w-full bg-gradient-to-r from-[#0284c7] to-[#0369a1] hover:from-[#0369a1] hover:to-[#075985] text-white font-extrabold text-xs py-2.5 px-4 rounded-xl shadow-md shadow-sky-500/20 transition flex items-center justify-center gap-2">
                            <i class="fas fa-paper-plane"></i>
                            <span>Kirim Simulasi Notifikasi Webhook</span>
                        </button>
                    </form>
                </div>

            </div>

            <!-- 3. TABEL LOG MUTASI REALTIME -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-black text-slate-800">Log Mutasi Rekening Masuk</h3>
                        <p class="text-xs text-slate-500">Daftar riwayat seluruh notifikasi pembayaran yang ditangkap oleh listener Rentify.</p>
                    </div>

                    <!-- Filter Status -->
                    <div class="flex items-center gap-2">
                        <a href="{{ route('admin.mutasi.index', ['status' => 'all']) }}" class="px-3 py-1.5 rounded-xl text-xs font-extrabold transition {{ $statusFilter === 'all' ? 'bg-[#0284c7] text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                            Semua
                        </a>
                        <a href="{{ route('admin.mutasi.index', ['status' => 'MATCHED']) }}" class="px-3 py-1.5 rounded-xl text-xs font-extrabold transition {{ $statusFilter === 'MATCHED' ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                            Lunas Otomatis
                        </a>
                        <a href="{{ route('admin.mutasi.index', ['status' => 'UNMATCHED']) }}" class="px-3 py-1.5 rounded-xl text-xs font-extrabold transition {{ $statusFilter === 'UNMATCHED' ? 'bg-amber-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                            Belum Terhubung
                        </a>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50/75 border-b border-slate-100 text-slate-400 font-bold uppercase tracking-wider text-[10px]">
                            <tr>
                                <th class="py-3 px-4">Waktu</th>
                                <th class="py-3 px-4">Provider</th>
                                <th class="py-3 px-4">Nominal</th>
                                <th class="py-3 px-4">Isi Notifikasi</th>
                                <th class="py-3 px-4">Status & Pesanan</th>
                                <th class="py-3 px-4 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                            @forelse($mutasis as $m)
                            <tr class="hover:bg-slate-50/50 transition">
                                <td class="py-3.5 px-4 font-mono text-[11px] text-slate-500 whitespace-nowrap">
                                    {{ $m->created_at ? $m->created_at->format('d/m/Y H:i:s') : '-' }}
                                </td>
                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    <span class="px-2.5 py-1 rounded-md text-[10px] font-extrabold
                                        {{ $m->app_name === 'DANA' ? 'bg-blue-50 text-blue-700 border border-blue-200' :
                                           ($m->app_name === 'BCA' ? 'bg-sky-50 text-sky-800 border border-sky-200' :
                                           ($m->app_name === 'GOPAY' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-700')) }}">
                                        {{ $m->app_name }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    <span class="font-extrabold text-[#0284c7] text-sm">
                                        Rp {{ number_format($m->nominal, 0, ',', '.') }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 max-w-xs">
                                    <p class="truncate text-slate-700 font-normal" title="{{ $m->content }}">
                                        {{ $m->content }}
                                    </p>
                                    @if($m->sender)
                                    <span class="text-[10px] text-slate-400">Pengirim: <b>{{ $m->sender }}</b></span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    @if($m->status === 'MATCHED')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <i class="fas fa-circle-check text-[9px]"></i> MATCHED
                                        </span>
                                        @if($m->order_id)
                                        <div class="mt-1">
                                            <a href="{{ route('customer.struk', 'INV-' . $m->order_id) }}" target="_blank" class="text-[11px] font-bold text-sky-600 hover:underline">
                                                Order #INV-{{ $m->order_id }} ↗
                                            </a>
                                        </div>
                                        @endif
                                    @elseif($m->status === 'UNMATCHED')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-amber-50 text-amber-700 border border-amber-200">
                                            <i class="fas fa-clock text-[9px]"></i> UNMATCHED
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-slate-100 text-slate-600">
                                            {{ $m->status }}
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                    @if($m->status === 'UNMATCHED')
                                    <!-- Modal Trigger untuk manual match -->
                                    <button type="button" onclick="bukaModalMatch({{ $m->id }}, {{ $m->nominal }})" class="bg-sky-50 text-[#0284c7] hover:bg-sky-100 px-2.5 py-1 rounded-lg font-bold text-[11px] border border-sky-200 transition">
                                        Hubungkan Pesanan
                                    </button>
                                    @else
                                    <span class="text-slate-300">-</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="py-12 text-center text-slate-400">
                                    <i class="fas fa-bell-slash text-3xl mb-2 block text-slate-300"></i>
                                    Belum ada rekaman notifikasi mutasi yang masuk.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($mutasis->hasPages())
                <div class="p-4 border-t border-slate-100">
                    {{ $mutasis->links() }}
                </div>
                @endif
            </div>

        </div>
    </main>

    <!-- MODAL MANUAL MATCH -->
    <div id="modal-match" class="hidden fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-100">
            <div class="flex items-center justify-between mb-4">
                <h4 class="text-sm font-extrabold text-slate-800">Hubungkan Mutasi ke Pesanan</h4>
                <button type="button" onclick="tutupModalMatch()" class="text-slate-400 hover:text-slate-600">
                    <i class="fas fa-xmark text-lg"></i>
                </button>
            </div>
            <p class="text-xs text-slate-500 mb-4">
                Pilih pesanan yang ingin diverifikasi lunas menggunakan mutasi sebesar <b id="modal-nominal" class="text-sky-600"></b>:
            </p>
            <form id="form-manual-match" method="POST">
                @csrf
                <div class="mb-4">
                    <label class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block mb-1">Daftar Pesanan Pending</label>
                    <select name="order_id" required class="w-full bg-slate-50 border border-slate-200 px-3 py-2 rounded-xl text-xs font-semibold outline-none focus:border-sky-500">
                        <option value="">-- Pilih Pesanan --</option>
                        @foreach($pendingOrders as $po)
                        <option value="{{ $po->id }}">
                            INV-{{ $po->id }} - {{ $po->customer_name }} (Rp {{ number_format($po->total_biaya ?: $po->total_price, 0, ',', '.') }}) [{{ $po->status }}]
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-center justify-end gap-2">
                    <button type="button" onclick="tutupModalMatch()" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-100 transition">Batal</button>
                    <button type="submit" class="px-4 py-2 rounded-xl text-xs font-extrabold bg-[#0284c7] hover:bg-[#0369a1] text-white transition">Verifikasi Lunas</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function salinTeks(elementId, btn) {
            const input = document.getElementById(elementId);
            if (!input) return;
            navigator.clipboard.writeText(input.value).then(function() {
                const icon = btn.querySelector('i');
                if (icon) {
                    icon.className = 'fas fa-check text-emerald-600';
                    setTimeout(() => { icon.className = 'fas fa-copy'; }, 2000);
                }
            });
        }

        function bukaModalMatch(mutasiId, nominal) {
            document.getElementById('modal-nominal').innerText = 'Rp ' + Number(nominal).toLocaleString('id-ID');
            document.getElementById('form-manual-match').action = '/admin/mutasi/' + mutasiId + '/match';
            document.getElementById('modal-match').classList.remove('hidden');
        }

        function tutupModalMatch() {
            document.getElementById('modal-match').classList.add('hidden');
        }
    </script>

</body>
</html>
