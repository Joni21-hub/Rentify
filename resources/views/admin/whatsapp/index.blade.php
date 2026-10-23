@php
    /** @var string $status */
    $status = $status ?? 'offline';
    /** @var string|null $qr */
    $qr = $qr ?? null;
    /** @var array|null $user */
    $user = $user ?? null;
    /** @var string $adminPhone */
    $adminPhone = $adminPhone ?? config('security.admin_phone', '083183494835');
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WhatsApp Gateway (Self-Hosted) - Admin Rentify</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="{{ asset('rentify-theme.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap');
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

                <div class="pt-4 px-4 pb-2 text-[10px] font-extrabold text-slate-400 uppercase tracking-widest">Pemasaran & Promosi</div>
                <a href="{{ route('admin.ai-marketing.index') }}" class="flex items-center justify-between px-4 py-3 text-sm font-semibold text-slate-600 hover:bg-slate-50 hover:text-slate-900 rounded-xl transition duration-200">
                    <div class="flex items-center space-x-3">
                        <i class="fas fa-wand-magic-sparkles text-sky-500 w-5"></i>
                        <span>AI Marketing</span>
                    </div>
                    <span class="px-1.5 py-0.5 rounded text-[9px] font-black uppercase tracking-wider bg-sky-500 text-white">AI</span>
                </a>

                <div class="pt-4 px-4 pb-2 text-[10px] font-extrabold text-slate-400 uppercase tracking-widest">Komunikasi & CS</div>
                <a href="{{ route('admin.whatsapp.index') }}" class="flex items-center justify-between px-4 py-3 text-sm font-bold bg-[#e0f2fe] text-[#0369a1] rounded-xl transition duration-200">
                    <div class="flex items-center space-x-3">
                        <i class="fa-brands fa-whatsapp text-emerald-600 w-5 text-base"></i>
                        <span>WhatsApp Gateway</span>
                    </div>
                    <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-emerald-600 text-white">Active</span>
                </a>

                <div class="pt-4 px-4 pb-2 text-[10px] font-extrabold text-slate-400 uppercase tracking-widest">Manajemen Master Data</div>
                <a href="{{ route('admin.vendors.validation') }}" class="flex items-center justify-between px-4 py-3 text-sm font-semibold text-slate-600 hover:bg-slate-50 hover:text-slate-900 rounded-xl transition duration-200">
                    <div class="flex items-center space-x-3">
                        <i class="fas fa-user-shield text-slate-400 w-5"></i>
                        <span>Validasi Vendor</span>
                    </div>
                </a>
            </nav>
        </div>

        <div class="p-4 border-t border-slate-100">
            <div class="flex items-center justify-between p-3 bg-slate-50 rounded-xl border border-slate-100">
                <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 bg-[#0369a1] rounded-lg flex items-center justify-center text-white font-bold text-xs shadow-md">AD</div>
                    <div class="truncate">
                        <h4 class="text-xs font-bold text-slate-800 truncate">Super Admin</h4>
                        <p class="text-[9px] text-emerald-600 font-bold truncate">Online</p>
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

    <!-- MAIN CONTENT -->
    <main class="flex-1 flex flex-col overflow-hidden">
        <!-- TOP HEADER -->
        <header class="h-16 bg-white border-b border-slate-200 px-8 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-lg border border-emerald-100">
                    <i class="fa-brands fa-whatsapp"></i>
                </div>
                <div>
                    <h2 class="text-base font-extrabold text-slate-800">WhatsApp Gateway Mandiri</h2>
                    <p class="text-xs text-slate-500">Koneksi Langsung Multi-Device • 100% Bebas Watermark • Tanpa Biaya SaaS</p>
                </div>
            </div>

            <div class="flex items-center space-x-3">
                <button onclick="checkStatusManual()" class="px-3.5 py-1.5 text-xs font-bold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-lg transition flex items-center gap-2">
                    <i class="fas fa-rotate" id="refresh-icon"></i>
                    <span>Cek Status</span>
                </button>
            </div>
        </header>

        <!-- FLASH MESSAGES -->
        @if(session('success'))
        <div class="mx-8 mt-4 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-bold rounded-xl flex items-center gap-3">
            <i class="fas fa-circle-check text-emerald-500 text-base"></i>
            <span>{{ session('success') }}</span>
        </div>
        @endif

        @if(session('error'))
        <div class="mx-8 mt-4 p-4 bg-rose-50 border border-rose-200 text-rose-800 text-sm font-bold rounded-xl flex items-center gap-3">
            <i class="fas fa-circle-exclamation text-rose-500 text-base"></i>
            <span>{{ session('error') }}</span>
        </div>
        @endif

        <!-- SCROLLABLE BODY -->
        <div class="flex-1 overflow-y-auto p-8 space-y-6">

            <!-- BANNER HIGHLIGHT -->
            <div class="p-5 bg-gradient-to-r from-emerald-900 via-teal-900 to-slate-900 text-white rounded-2xl shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider bg-emerald-500 text-white">Baileys Engine</span>
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-white/20 text-emerald-200">Self-Hosted</span>
                    </div>
                    <h3 class="text-lg font-black tracking-tight">Koneksi WhatsApp Resmi Rentify Tanpa Watermark Fonnte</h3>
                    <p class="text-xs text-slate-300 max-w-2xl">
                        Seluruh notifikasi OTP, peringatan keamanan akun, konfirmasi vendor rental, dan balasan Customer Service otomatis kini dikirim langsung melalui gateway internal Rentify.
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <div class="text-right">
                        <span class="block text-[10px] uppercase font-bold text-slate-300 tracking-wider">Status Watermark</span>
                        <span class="text-sm font-black text-emerald-400 flex items-center justify-end gap-1.5">
                            <i class="fas fa-shield-halved"></i> 100% Bersih
                        </span>
                    </div>
                </div>
            </div>

            <!-- GRID SECTION -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

                <!-- LEFT COLUMN: STATUS & QR SCANNER (7 COLS) -->
                <div class="lg:col-span-7 bg-white rounded-2xl border border-slate-200 p-6 shadow-sm flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                            <div class="flex items-center gap-2.5">
                                <span class="w-3 h-3 rounded-full {{ $status === 'connected' ? 'bg-emerald-500 animate-pulse' : ($status === 'qr_ready' ? 'bg-amber-500' : 'bg-rose-500') }}" id="status-indicator-dot"></span>
                                <h4 class="text-sm font-black text-slate-800 uppercase tracking-wider">Status Perangkat WhatsApp</h4>
                            </div>
                            <span class="px-2.5 py-1 rounded-full text-xs font-bold {{ $status === 'connected' ? 'bg-emerald-100 text-emerald-800' : ($status === 'qr_ready' ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-700') }}" id="status-badge">
                                {{ $status === 'connected' ? 'TERHUBUNG (ONLINE)' : ($status === 'qr_ready' ? 'SIAP PINDAI QR' : 'MENGHUBUNGKAN...') }}
                            </span>
                        </div>

                        <!-- STATE 1: CONNECTED -->
                        <div id="section-connected" class="{{ $status === 'connected' ? '' : 'hidden' }} py-8 text-center space-y-4">
                            <div class="w-20 h-20 bg-emerald-100 text-emerald-600 rounded-3xl mx-auto flex items-center justify-center text-4xl shadow-inner border border-emerald-200">
                                <i class="fa-brands fa-whatsapp"></i>
                            </div>
                            <div>
                                <h3 class="text-xl font-black text-slate-800" id="connected-phone">{{ $user['phone'] ?? '-' }}</h3>
                                <p class="text-xs text-slate-500 mt-1" id="connected-name">{{ $user['name'] ?? 'WhatsApp Admin Resmi' }}</p>
                            </div>
                            <div class="max-w-md mx-auto p-4 bg-emerald-50/70 border border-emerald-100 rounded-xl text-left text-xs text-emerald-900 space-y-2">
                                <div class="flex items-center justify-between">
                                    <span class="font-medium text-slate-600">Protokol:</span>
                                    <span class="font-bold">WhatsApp Multi-Device (WebSocket)</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="font-medium text-slate-600">Penyimpanan Sesi:</span>
                                    <span class="font-bold text-sky-700">Aiven Cloud MySQL (Permanen)</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="font-medium text-slate-600">Integrasi Webhook:</span>
                                    <span class="font-bold text-emerald-700">Aktif (Auto-Reply & CS Rentify)</span>
                                </div>
                            </div>

                            <div class="flex items-center justify-center gap-3 pt-4 flex-wrap">
                                <form action="{{ route('admin.whatsapp.reset-sessions') }}" method="POST" onsubmit="return confirm('Reset sesi enkripsi chat? Ini akan memperbaiki kendala \'Menunggu pesan ini\' tanpa menghapus login QR WhatsApp.')">
                                    @csrf
                                    <button type="submit" class="px-4 py-2 bg-sky-50 hover:bg-sky-100 text-sky-700 text-xs font-bold rounded-xl border border-sky-200 transition flex items-center gap-1.5 shadow-sm">
                                        <i class="fas fa-shield-halved text-sky-600"></i> Perbaiki Enkripsi ('Menunggu Pesan')
                                    </button>
                                </form>

                                <form action="{{ route('admin.whatsapp.logout') }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin memutuskan koneksi nomor WhatsApp ini?')">
                                    @csrf
                                    <button type="submit" class="px-4 py-2 bg-rose-50 hover:bg-rose-100 text-rose-700 text-xs font-bold rounded-xl border border-rose-200 transition flex items-center gap-1.5">
                                        <i class="fas fa-power-off"></i> Putuskan Sesi (Logout)
                                    </button>
                                </form>
                            </div>
                        </div>

                        <!-- STATE 2: WAITING FOR QR SCAN -->
                        <div id="section-qr" class="{{ $status === 'qr_ready' ? '' : 'hidden' }} py-6 text-center space-y-5">
                            <div class="inline-block p-4 bg-white border-2 border-slate-200 rounded-2xl shadow-md">
                                <img id="qr-image" src="{{ $qr ?? '' }}" alt="QR Code WhatsApp" class="w-64 h-64 object-contain mx-auto {{ empty($qr) ? 'hidden' : '' }}">
                                <div id="qr-loading-spinner" class="w-64 h-64 flex flex-col items-center justify-center text-slate-400 {{ !empty($qr) ? 'hidden' : '' }}">
                                    <i class="fas fa-circle-notch fa-spin text-3xl mb-2 text-sky-600"></i>
                                    <span class="text-xs font-bold">Menyiapkan kode QR...</span>
                                </div>
                            </div>

                            <div class="max-w-md mx-auto text-left bg-slate-50 border border-slate-200 p-4 rounded-xl text-xs space-y-2">
                                <h5 class="font-bold text-slate-800 flex items-center gap-1.5">
                                    <i class="fas fa-qrcode text-sky-600"></i> Cara Menautkan Perangkat:
                                </h5>
                                <ol class="list-decimal list-inside space-y-1 text-slate-600">
                                    <li>Buka aplikasi WhatsApp di HP Anda (Nomor WhatsApp Rentify: <strong>081262364197</strong>).</li>
                                    <li>Ketuk ikon <strong>Titik Tiga (⋮)</strong> di Android atau menu <strong>Pengaturan</strong> di iPhone.</li>
                                    <li>Pilih <strong>Perangkat Tertaut (Linked Devices)</strong>.</li>
                                    <li>Ketuk <strong>Tautkan Perangkat</strong> lalu arahkan kamera HP ke kode QR di atas.</li>
                                </ol>
                            </div>
                        </div>

                        <!-- STATE 3: OFFLINE / CONNECTING -->
                        <div id="section-offline" class="{{ ($status !== 'connected' && $status !== 'qr_ready') ? '' : 'hidden' }} py-12 text-center space-y-4">
                            <div class="w-16 h-16 bg-slate-100 text-slate-400 rounded-2xl mx-auto flex items-center justify-center text-2xl">
                                <i class="fas fa-satellite-dish fa-spin text-sky-600"></i>
                            </div>
                            <div>
                                <h4 class="text-base font-bold text-slate-800">Menghubungkan ke Gateway WhatsApp...</h4>
                                <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">Sistem sedang menginisialisasi socket Baileys. Halaman akan memperbarui otomatis begitu QR Code siap dipindai.</p>
                            </div>
                        </div>

                    </div>

                    <div class="pt-4 border-t border-slate-100 text-center">
                        <span class="text-[11px] text-slate-400 flex items-center justify-center gap-1.5">
                            <i class="fas fa-clock"></i> Polling status otomatis setiap 3 detik
                        </span>
                    </div>
                </div>

                <!-- RIGHT COLUMN: TEST MESSAGE & SYSTEM ARCHITECTURE (5 COLS) -->
                <div class="lg:col-span-5 space-y-6">

                    <!-- TEST SEND CARD -->
                    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
                        <div class="flex items-center gap-2 pb-4 border-b border-slate-100">
                            <i class="fas fa-paper-plane text-sky-600"></i>
                            <h4 class="text-sm font-black text-slate-800 uppercase tracking-wider">Uji Coba Kirim Pesan</h4>
                        </div>

                        <form action="{{ route('admin.whatsapp.test') }}" method="POST" class="mt-4 space-y-4">
                            @csrf
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Nomor WhatsApp Tujuan</label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400 text-xs font-bold">+62</span>
                                    <input type="text" name="nomor" value="{{ $adminPhone }}" required placeholder="83183494835" class="w-full pl-12 pr-4 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-sky-500 font-semibold">
                                </div>
                                <span class="text-[10px] text-slate-400 mt-1 block">Bisa masukkan nomor berawalan 08 atau 62.</span>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Isi Pesan WhatsApp</label>
                                <textarea name="pesan" rows="3" required class="w-full p-3 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-sky-500 font-medium">Halo, ini pesan uji coba resmi dari sistem Rentify WhatsApp Gateway mandiri tanpa watermark!</textarea>
                            </div>

                            <button type="submit" class="w-full py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-extrabold rounded-xl shadow-md shadow-emerald-600/20 transition flex items-center justify-center gap-2">
                                <i class="fas fa-paper-plane"></i>
                                <span>Kirim Pesan Uji Coba</span>
                            </button>
                        </form>
                    </div>

                    <!-- ARCHITECTURE OVERVIEW -->
                    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm space-y-3">
                        <div class="flex items-center gap-2 pb-3 border-b border-slate-100">
                            <i class="fas fa-circle-nodes text-sky-600"></i>
                            <h4 class="text-sm font-black text-slate-800 uppercase tracking-wider">Konfigurasi Sistem</h4>
                        </div>
                        <ul class="text-xs space-y-2 text-slate-600">
                            <li class="flex items-start gap-2">
                                <i class="fas fa-check text-emerald-500 mt-0.5"></i>
                                <span><strong>Penyedia:</strong> Microservice Mandiri (Baileys v6)</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <i class="fas fa-check text-emerald-500 mt-0.5"></i>
                                <span><strong>Biaya Langganan:</strong> Rp 0 (Gratis Selamanya)</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <i class="fas fa-check text-emerald-500 mt-0.5"></i>
                                <span><strong>Watermark Fonnte:</strong> Dihapus total</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <i class="fas fa-check text-emerald-500 mt-0.5"></i>
                                <span><strong>Sesi Permanen:</strong> Tersimpan di database MySQL Aiven, tidak hilang saat restart</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <i class="fas fa-check text-emerald-500 mt-0.5"></i>
                                <span><strong>Webhook Auto-Reply:</strong> Aktif di <code class="text-[10px] bg-slate-100 px-1 py-0.5 rounded">/webhook/whatsapp</code></span>
                            </li>
                        </ul>
                    </div>

                </div>

            </div>

        </div>
    </main>

    <!-- LIVE POLLING SCRIPT -->
    <script>
        let isPolling = true;

        async function fetchStatus() {
            try {
                const res = await fetch("{{ route('admin.whatsapp.status') }}");
                if (!res.ok) return;
                const data = await res.json();
                renderStatus(data);
            } catch (err) {
                console.error("Failed to poll status:", err);
            }
        }

        function renderStatus(data) {
            const status = data.status || 'offline';
            const badge = document.getElementById('status-badge');
            const dot = document.getElementById('status-indicator-dot');
            const secConnected = document.getElementById('section-connected');
            const secQr = document.getElementById('section-qr');
            const secOffline = document.getElementById('section-offline');

            if (status === 'connected') {
                badge.className = 'px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800';
                badge.textContent = 'TERHUBUNG (ONLINE)';
                dot.className = 'w-3 h-3 rounded-full bg-emerald-500 animate-pulse';

                if (data.user) {
                    if (data.user.phone) document.getElementById('connected-phone').textContent = '+' + data.user.phone;
                    if (data.user.name) document.getElementById('connected-name').textContent = data.user.name;
                }

                secConnected.classList.remove('hidden');
                secQr.classList.add('hidden');
                secOffline.classList.add('hidden');
            } else if (status === 'qr_ready') {
                badge.className = 'px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800';
                badge.textContent = 'SIAP PINDAI QR';
                dot.className = 'w-3 h-3 rounded-full bg-amber-500';

                const qrImg = document.getElementById('qr-image');
                const qrSpinner = document.getElementById('qr-loading-spinner');
                if (data.qr) {
                    qrImg.src = data.qr;
                    qrImg.classList.remove('hidden');
                    qrSpinner.classList.add('hidden');
                }

                secConnected.classList.add('hidden');
                secQr.classList.remove('hidden');
                secOffline.classList.add('hidden');
            } else {
                badge.className = 'px-2.5 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-700';
                badge.textContent = 'MENGHUBUNGKAN...';
                dot.className = 'w-3 h-3 rounded-full bg-rose-500';

                secConnected.classList.add('hidden');
                secQr.classList.add('hidden');
                secOffline.classList.remove('hidden');
            }
        }

        function checkStatusManual() {
            const icon = document.getElementById('refresh-icon');
            icon.classList.add('fa-spin');
            fetchStatus().finally(() => {
                setTimeout(() => icon.classList.remove('fa-spin'), 600);
            });
        }

        // Poll every 3 seconds
        setInterval(fetchStatus, 3000);
    </script>
</body>
</html>
