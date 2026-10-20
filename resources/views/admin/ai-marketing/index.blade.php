<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Agent Marketing & Auto-Promosi - Admin Rentify</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="{{ asset('rentify-theme.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .tab-btn.active {
            background-color: #0369a1;
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(3, 105, 161, 0.25);
        }
        .aspect-9-16 {
            aspect-ratio: 9 / 16;
        }
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
                <a href="{{ route('admin.ai-marketing.index') }}" class="flex items-center justify-between px-4 py-3 text-sm font-bold bg-[#e0f2fe] text-[#0369a1] rounded-xl transition duration-200">
                    <div class="flex items-center space-x-3">
                        <i class="fas fa-robot text-[#0369a1] w-5"></i>
                        <span>AI Auto-Promosi</span>
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

    <!-- MAIN CONTENT -->
    <main class="flex-1 flex flex-col h-screen overflow-hidden">
        
        <!-- Topbar -->
        <header class="bg-white border-b border-slate-200 px-8 py-4 flex flex-wrap items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-sky-100 text-sky-800 border border-sky-200 flex items-center gap-1.5">
                        <i class="fas fa-sparkles text-sky-600"></i> AI Marketing Agent
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-0.5 rounded-full text-xs font-bold {{ $isAiOnline ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-sky-50 text-sky-700 border border-sky-200' }}">
                        <span class="w-2 h-2 rounded-full {{ $isAiOnline ? 'bg-emerald-500 animate-pulse' : 'bg-sky-500' }}"></span>
                        {{ $isAiOnline ? 'Gemini 1.5 Flash Aktif' : 'Smart Heuristic Engine Aktif' }}
                    </span>
                </div>
                <h1 class="text-xl font-black text-slate-800 tracking-tight mt-1 flex items-center gap-2">
                    Pusat Auto-Promosi & Konten Viral Rentify
                </h1>
            </div>

            <!-- Mode Rotasi 50:50 Hari Ganjil/Genap Indicator -->
            <div class="flex items-center gap-3 bg-slate-50 border border-slate-200 p-2 px-4 rounded-2xl">
                <div class="text-right">
                    <p class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Pola Hari Ini: Tgl {{ $dayNumber }} ({{ ucfirst($dayType) }})</p>
                    <p class="text-xs font-black {{ $todayDefaultTarget === 'customer' ? 'text-emerald-700' : 'text-indigo-700' }} flex items-center gap-1.5 justify-end">
                        <i class="fas {{ $todayDefaultTarget === 'customer' ? 'fa-users' : 'fa-store' }}"></i>
                        <span>Target: {{ $todayDefaultTarget === 'customer' ? 'Penyewa (Customer)' : 'Pemilik Barang (Vendor)' }}</span>
                    </p>
                </div>
                <div class="w-9 h-9 rounded-xl flex items-center justify-center font-black text-sm text-white shadow-sm {{ $todayDefaultTarget === 'customer' ? 'bg-emerald-600' : 'bg-indigo-600' }}">
                    {{ $dayNumber }}
                </div>
            </div>
        </header>

        <!-- Subnav Tab Bar -->
        <div class="bg-white border-b border-slate-200 px-8 py-2.5 flex items-center justify-between overflow-x-auto">
            <div class="flex items-center space-x-2">
                <button onclick="switchAiTab('tab-autopilot')" id="btn-tab-autopilot" class="tab-btn active px-4 py-2 rounded-xl text-xs font-extrabold text-slate-600 hover:bg-slate-100 transition flex items-center gap-2">
                    <i class="fas fa-clapperboard"></i>
                    <span>Konten Harian & Video Script</span>
                </button>
                <button onclick="switchAiTab('tab-studio')" id="btn-tab-studio" class="tab-btn px-4 py-2 rounded-xl text-xs font-extrabold text-slate-600 hover:bg-slate-100 transition flex items-center gap-2">
                    <i class="fas fa-mobile-screen-button"></i>
                    <span>Studio Visual 9:16</span>
                </button>
                <button onclick="switchAiTab('tab-settings')" id="btn-tab-settings" class="tab-btn px-4 py-2 rounded-xl text-xs font-extrabold text-slate-600 hover:bg-slate-100 transition flex items-center gap-2">
                    <i class="fas fa-sliders"></i>
                    <span>Pengaturan Jadwal & API</span>
                </button>
                <button onclick="switchAiTab('tab-history')" id="btn-tab-history" class="tab-btn px-4 py-2 rounded-xl text-xs font-extrabold text-slate-600 hover:bg-slate-100 transition flex items-center gap-2">
                    <i class="fas fa-clock-rotate-left"></i>
                    <span>Riwayat Konten ({{ $totalGeneratedPosts }})</span>
                </button>
                <button onclick="switchAiTab('tab-campaign')" id="btn-tab-campaign" class="tab-btn px-4 py-2 rounded-xl text-xs font-extrabold text-slate-600 hover:bg-slate-100 transition flex items-center gap-2">
                    <i class="fas fa-bullhorn"></i>
                    <span>Banner & Kampanye Event</span>
                </button>
            </div>

            <!-- Quick Trigger Button -->
            <form action="{{ route('admin.ai-marketing.generate-daily') }}" method="POST" class="flex items-center gap-2">
                @csrf
                <button type="submit" class="px-4 py-2 rounded-xl bg-gradient-to-r from-[#0369a1] to-sky-500 hover:from-sky-700 hover:to-sky-600 text-white font-black text-xs shadow-md hover:shadow-lg transition flex items-center gap-1.5 active:scale-95">
                    <i class="fas fa-wand-magic-sparkles"></i>
                    <span>Generate Konten Hari Ini</span>
                </button>
            </form>
        </div>

        <!-- Content Area -->
        <div class="flex-1 overflow-y-auto p-8 space-y-6">

            @if(session('success'))
            <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center justify-between text-xs sm:text-sm font-bold shadow-sm">
                <div class="flex items-center gap-2">
                    <i class="fas fa-check-circle text-emerald-600 text-base"></i>
                    <span>{{ session('success') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            @endif

            <!-- 50:50 ROTATION SUMMARY KPI ROW -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs flex items-center justify-between">
                    <div>
                        <p class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">Konten Customer (Ganjil)</p>
                        <p class="text-2xl font-black text-emerald-600 mt-1">{{ number_format($totalCustomerPosts) }}</p>
                        <p class="text-[11px] text-slate-500 mt-0.5">Edukasi hemat sewa</p>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl">
                        <i class="fas fa-users"></i>
                    </div>
                </div>

                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs flex items-center justify-between">
                    <div>
                        <p class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">Konten Vendor (Genap)</p>
                        <p class="text-2xl font-black text-indigo-600 mt-1">{{ number_format($totalVendorPosts) }}</p>
                        <p class="text-[11px] text-slate-500 mt-0.5">Ajak sewakan barang</p>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl">
                        <i class="fas fa-store"></i>
                    </div>
                </div>

                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs flex items-center justify-between">
                    <div>
                        <p class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">Jadwal Auto-Pilot</p>
                        <p class="text-2xl font-black text-[#0369a1] mt-1">{{ $settings['schedule_time'] }} <span class="text-xs text-slate-400 font-semibold">WIB</span></p>
                        <p class="text-[11px] text-slate-500 mt-0.5">Laravel Scheduler Aktif</p>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-sky-50 text-sky-600 flex items-center justify-center text-xl">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>

                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs flex items-center justify-between">
                    <div>
                        <p class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">Sosial Media Published</p>
                        <p class="text-2xl font-black text-rose-600 mt-1">{{ number_format($totalPublishedSocial) }}</p>
                        <p class="text-[11px] text-slate-500 mt-0.5">Instagram & TikTok</p>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center text-xl">
                        <i class="fab fa-instagram"></i>
                    </div>
                </div>
            </div>

            <!-- ========================================== -->
            <!-- TAB 1: KONTEN HARIAN & VIDEO SCRIPT 50:50 -->
            <!-- ========================================== -->
            <div id="tab-autopilot" class="ai-tab-panel space-y-6">

                @if($todayPost)
                <!-- Target Header Banner -->
                <div class="p-6 rounded-3xl text-white shadow-xl relative overflow-hidden {{ $todayPost->isCustomerTarget() ? 'bg-gradient-to-r from-emerald-900 via-teal-900 to-slate-900 border border-emerald-800/40' : 'bg-gradient-to-r from-indigo-950 via-slate-900 to-sky-950 border border-indigo-800/40' }}">
                    <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div class="space-y-2 max-w-2xl">
                            <div class="flex items-center gap-2">
                                <span class="px-3 py-1 rounded-full text-xs font-black uppercase tracking-wider {{ $todayPost->isCustomerTarget() ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-400/30' : 'bg-indigo-500/20 text-indigo-300 border border-indigo-400/30' }}">
                                    <i class="fas {{ $todayPost->isCustomerTarget() ? 'fa-users' : 'fa-store' }} mr-1"></i>
                                    {{ $todayPost->isCustomerTarget() ? 'FOKUS 1: MENARIK CUSTOMER (PENYEWA)' : 'FOKUS 2: MENARIK VENDOR (PEMILIK BARANG)' }}
                                </span>
                                <span class="text-xs text-slate-300 font-bold">
                                    • Hari {{ ucfirst($todayPost->day_type) }} (Tgl {{ $todayPost->day_number }})
                                </span>
                            </div>
                            <h2 class="text-xl sm:text-2xl font-black text-white leading-tight">
                                {{ $todayPost->tema }}
                            </h2>
                            <p class="text-xs text-slate-300">
                                @if($todayPost->isCustomerTarget())
                                    <i class="fas fa-bullseye text-emerald-400 mr-1"></i> <strong>Kategori:</strong> {{ $todayPost->kategori_barang }} — Edukasi kebutuhan sesekali/jarang dipakai agar TIDAK PERLU BELI MAHAL.
                                @else
                                    <i class="fas fa-bullseye text-indigo-400 mr-1"></i> <strong>Persona Vendor:</strong> {{ $todayPost->persona_vendor }} — Mengajak barang nganggur di rumah/gudang jadi cuan passive income.
                                @endif
                            </p>
                        </div>

                        <!-- Action Switch / Re-generate -->
                        <div class="flex flex-wrap items-center gap-2">
                            <form action="{{ route('admin.ai-marketing.generate-daily') }}" method="POST">
                                @csrf
                                <input type="hidden" name="target_audience" value="{{ $todayPost->isCustomerTarget() ? 'vendor' : 'customer' }}">
                                <button type="submit" class="px-3.5 py-2.5 rounded-xl bg-white/10 hover:bg-white/20 border border-white/20 text-white font-extrabold text-xs transition flex items-center gap-1.5">
                                    <i class="fas fa-repeat"></i>
                                    <span>Ganti ke {{ $todayPost->isCustomerTarget() ? 'Vendor (Hari Genap)' : 'Customer (Hari Ganjil)' }}</span>
                                </button>
                            </form>
                            <button onclick="switchAiTab('tab-studio')" class="px-4 py-2.5 rounded-xl bg-white text-slate-900 font-black text-xs shadow-md hover:bg-slate-100 transition flex items-center gap-1.5">
                                <i class="fas fa-mobile-screen-button text-[#0369a1]"></i>
                                <span>Buka Studio 9:16</span>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

                    <!-- Kolom Kiri: Hook & Naskah Video 15-30 Detik -->
                    <div class="lg:col-span-7 space-y-6">

                        <!-- 1. Hook Masalah 3 Detik Pertama -->
                        <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm space-y-3">
                            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center text-sm font-bold">
                                        <i class="fas fa-bolt"></i>
                                    </div>
                                    <div>
                                        <h3 class="text-sm font-black text-slate-800">Hook Masalah 3 Detik Pertama (Scroll Stopper)</h3>
                                        <p class="text-[10px] text-slate-400">Kalimat pembuka yang menghentikan scrolling penonton di TikTok / Reels</p>
                                    </div>
                                </div>
                                <button onclick="copyText('hookText')" class="px-2.5 py-1 rounded-lg text-[10px] font-bold bg-slate-100 hover:bg-slate-200 text-slate-700 transition flex items-center gap-1">
                                    <i class="far fa-copy"></i> Salin
                                </button>
                            </div>
                            <div class="p-4 rounded-2xl bg-amber-50/70 border border-amber-200/70 relative">
                                <p id="hookText" class="text-sm sm:text-base font-black text-slate-900 leading-snug">
                                    "{{ $todayPost->hook_masalah }}"
                                </p>
                            </div>
                        </div>

                        <!-- 2. Breakdown Naskah Video Pendek 15-30 Detik -->
                        <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm space-y-4">
                            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                                <div>
                                    <h3 class="text-sm font-black text-slate-800 flex items-center gap-2">
                                        <i class="fas fa-film text-sky-500"></i>
                                        Naskah Video Vertikal (15 - 30 Detik)
                                    </h3>
                                    <p class="text-[10px] text-slate-400">Struktur 4 adegan terencana untuk video Reels & TikTok</p>
                                </div>
                                <button onclick="copyFullScript()" class="px-2.5 py-1 rounded-lg text-[10px] font-bold bg-sky-50 hover:bg-sky-100 text-sky-700 border border-sky-200 transition flex items-center gap-1">
                                    <i class="far fa-copy"></i> Salin Naskah
                                </button>
                            </div>

                            <div class="space-y-3" id="scriptScenesContainer">
                                @if(is_array($todayPost->naskah_video))
                                    @foreach($todayPost->naskah_video as $index => $scene)
                                    <div class="p-4 rounded-2xl border border-slate-100 bg-slate-50/60 hover:bg-slate-50 transition space-y-2">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center gap-2">
                                                <span class="w-6 h-6 rounded-full bg-[#0369a1] text-white text-[10px] font-black flex items-center justify-center">
                                                    {{ $index + 1 }}
                                                </span>
                                                <span class="text-xs font-black text-slate-800">{{ $scene['adegan'] ?? ('Adegan ' . ($index + 1)) }}</span>
                                            </div>
                                            <span class="px-2 py-0.5 rounded text-[10px] font-extrabold bg-sky-100 text-sky-800">
                                                {{ $scene['detik'] ?? '0-3s' }}
                                            </span>
                                        </div>
                                        <div class="text-xs text-slate-600 pl-8 space-y-1">
                                            <p><span class="font-bold text-slate-400 uppercase text-[9px]">Visual:</span> {{ $scene['visual'] ?? '' }}</p>
                                            <p class="p-2.5 rounded-xl bg-white border border-slate-200/60 text-slate-900 font-semibold italic">
                                                🎙️ "{{ $scene['voiceover'] ?? '' }}"
                                            </p>
                                        </div>
                                    </div>
                                    @endforeach
                                @endif
                            </div>
                        </div>

                    </div>

                    <!-- Kolom Kanan: Teks di Layar, Caption, Direct Actions -->
                    <div class="lg:col-span-5 space-y-6">

                        <!-- 3. Teks di Layar (Overlay 9:16) -->
                        <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm space-y-3">
                            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                                <div>
                                    <h3 class="text-sm font-black text-slate-800 flex items-center gap-2">
                                        <i class="fas fa-font text-indigo-500"></i>
                                        Teks di Layar (On-Screen Text)
                                    </h3>
                                    <p class="text-[10px] text-slate-400">Penempatan teks vertikal pada video</p>
                                </div>
                            </div>

                            <div class="space-y-2">
                                @if(is_array($todayPost->teks_di_layar))
                                    @foreach($todayPost->teks_di_layar as $overlay)
                                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-100 flex items-center justify-between text-xs">
                                        <div>
                                            <span class="text-[9px] font-extrabold uppercase tracking-wider text-slate-400 block">{{ $overlay['posisi'] ?? 'Tengah' }}</span>
                                            <span class="font-black text-slate-800">{{ $overlay['teks'] ?? '' }}</span>
                                        </div>
                                        <span class="text-[10px] font-semibold text-slate-500 bg-white px-2 py-1 rounded-lg border border-slate-200">
                                            {{ $overlay['style'] ?? 'Teks Bold' }}
                                        </span>
                                    </div>
                                    @endforeach
                                @endif
                            </div>
                        </div>

                        <!-- 4. Caption Persuasif Media Sosial -->
                        <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm space-y-3">
                            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                                <div>
                                    <h3 class="text-sm font-black text-slate-800 flex items-center gap-2">
                                        <i class="fas fa-quote-left text-sky-500"></i>
                                        Caption Instagram & TikTok
                                    </h3>
                                    <p class="text-[10px] text-slate-400">Siap diposting bersama video/visual</p>
                                </div>
                                <button onclick="copyText('fullCaptionText')" class="px-2.5 py-1 rounded-lg text-[10px] font-bold bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 transition flex items-center gap-1">
                                    <i class="far fa-copy"></i> Salin Caption
                                </button>
                            </div>

                            <textarea id="fullCaptionText" rows="6" readonly class="w-full p-3 rounded-2xl bg-slate-50 border border-slate-200 text-xs text-slate-800 font-medium focus:outline-none resize-none">{{ $todayPost->caption }}</textarea>

                            <!-- Social Action Buttons -->
                            <div class="pt-2 grid grid-cols-2 gap-2">
                                <button onclick="publishToSocial('instagram', {{ $todayPost->id }})" class="py-2.5 px-3 rounded-xl bg-gradient-to-r from-pink-600 via-rose-500 to-amber-500 text-white font-extrabold text-xs shadow-sm hover:opacity-95 transition flex items-center justify-center gap-1.5">
                                    <i class="fab fa-instagram"></i>
                                    <span>Post ke Instagram</span>
                                </button>
                                <button onclick="publishToSocial('tiktok', {{ $todayPost->id }})" class="py-2.5 px-3 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-extrabold text-xs shadow-sm transition flex items-center justify-center gap-1.5">
                                    <i class="fab fa-tiktok"></i>
                                    <span>Post ke TikTok</span>
                                </button>
                            </div>
                            <button onclick="shareWhatsApp()" class="w-full py-2.5 px-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs shadow-sm transition flex items-center justify-center gap-1.5">
                                <i class="fab fa-whatsapp"></i>
                                <span>Broadcast WhatsApp Customer/Vendor</span>
                            </button>
                        </div>

                    </div>

                </div>
                @else
                <!-- Empty State -->
                <div class="bg-white rounded-3xl p-12 text-center border border-slate-200 space-y-4">
                    <div class="w-16 h-16 rounded-full bg-sky-50 text-sky-600 flex items-center justify-center mx-auto text-2xl">
                        <i class="fas fa-wand-magic-sparkles"></i>
                    </div>
                    <h3 class="text-lg font-black text-slate-800">Belum Ada Konten Harian</h3>
                    <p class="text-xs text-slate-500 max-w-md mx-auto">
                        Klik tombol di bawah untuk membuat ide video pendek 15-30 detik otomatis sesuai target hari ini ({{ $todayDefaultTarget === 'customer' ? 'Customer' : 'Vendor' }}).
                    </p>
                    <form action="{{ route('admin.ai-marketing.generate-daily') }}" method="POST">
                        @csrf
                        <button type="submit" class="py-3 px-6 rounded-2xl bg-gradient-to-r from-[#0369a1] to-sky-500 text-white font-black text-sm shadow-md hover:shadow-lg transition">
                            Generate Konten Harian Sekarang
                        </button>
                    </form>
                </div>
                @endif

            </div>

            <!-- ========================================== -->
            <!-- TAB 2: STUDIO VISUAL & VIDEO VERTIKAL 9:16 -->
            <!-- ========================================== -->
            <div id="tab-studio" class="ai-tab-panel hidden space-y-6">

                <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 pb-4">
                        <div>
                            <h2 class="text-base font-black text-slate-800 flex items-center gap-2">
                                <i class="fas fa-mobile-screen-button text-sky-500"></i>
                                Studio Visual & Simulasi Video 9:16 (Instagram Reels / TikTok)
                            </h2>
                            <p class="text-xs text-slate-500 mt-0.5">
                                Preview tampilan layar smartphone vertikal, jalankan simulasi video 15-30 detik, atau download poster visual high-res (1080x1920) untuk cover reels.
                            </p>
                        </div>

                        <!-- Controls -->
                        <div class="flex items-center gap-2">
                            <button id="btnPlayVideoSim" onclick="playVideoSimulation()" class="px-4 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-black text-xs shadow-md transition flex items-center gap-1.5">
                                <i class="fas fa-play text-emerald-400"></i>
                                <span>Putar Simulasi Video</span>
                            </button>
                            <button onclick="exportCanvas916()" class="px-4 py-2 rounded-xl bg-[#0369a1] hover:bg-sky-700 text-white font-black text-xs shadow-md transition flex items-center gap-1.5">
                                <i class="fas fa-download"></i>
                                <span>Unduh Visual 9:16</span>
                            </button>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 pt-6 items-center">

                        <!-- Smartphone Mockup Frame -->
                        <div class="lg:col-span-5 flex justify-center">
                            <div class="relative w-[320px] aspect-9-16 bg-slate-950 rounded-[44px] p-3 shadow-2xl border-4 border-slate-800 ring-1 ring-slate-900/50">
                                
                                <!-- Dynamic Screen Container -->
                                <div id="phoneScreen" class="w-full h-full rounded-[34px] overflow-hidden relative flex flex-col justify-between p-6 text-white transition-all duration-500 {{ ($todayPost && $todayPost->isCustomerTarget()) ? 'bg-gradient-to-b from-slate-900 via-emerald-950 to-teal-950' : 'bg-gradient-to-b from-slate-900 via-indigo-950 to-sky-950' }}">
                                    
                                    <!-- Background Floating Elements -->
                                    <div class="absolute -top-10 -right-10 w-40 h-40 bg-sky-500/20 rounded-full blur-2xl pointer-events-none"></div>
                                    <div class="absolute -bottom-10 -left-10 w-40 h-40 bg-emerald-500/20 rounded-full blur-2xl pointer-events-none"></div>

                                    <!-- Top Bar: Logo & Badge -->
                                    <div class="relative z-10 flex items-center justify-between">
                                        <div class="flex items-center gap-1.5 bg-black/40 backdrop-blur-md px-2.5 py-1 rounded-full border border-white/10">
                                            <span class="w-2 h-2 rounded-full bg-sky-400 animate-pulse"></span>
                                            <span class="text-[10px] font-black uppercase tracking-wider text-sky-300">Rentify</span>
                                        </div>
                                        <span id="screenSceneBadge" class="text-[9px] font-black uppercase tracking-wider bg-white/20 backdrop-blur-md px-2 py-0.5 rounded-full text-white">
                                            Adegan 1 (0-3s)
                                        </span>
                                    </div>

                                    <!-- Center Content: Big Hook / Main Message -->
                                    <div class="relative z-10 text-center space-y-3 my-auto">
                                        <div id="screenSticker" class="inline-block px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider {{ ($todayPost && $todayPost->isCustomerTarget()) ? 'bg-emerald-500 text-slate-950' : 'bg-indigo-500 text-white' }} shadow-md">
                                            {{ ($todayPost && $todayPost->isCustomerTarget()) ? '⚡ SOLUSI ANTI BONCOS' : '💸 MESIN CUAN PASIF' }}
                                        </div>

                                        <h2 id="screenMainText" class="text-xl sm:text-2xl font-black text-white leading-tight drop-shadow-md">
                                            {{ $todayPost ? $todayPost->hook_masalah : 'Mau Acara Sehari Masa Harus Beli Baru?' }}
                                        </h2>

                                        <p id="screenSubText" class="text-xs text-slate-200 font-medium px-2 leading-relaxed">
                                            {{ ($todayPost && $todayPost->isCustomerTarget()) ? 'Sewa hemat barang outdoor, kamera, bor, dan perlengkapan harian di Rentify!' : 'Punya barang nganggur di rumah? Sewakan dan hasilkan cuan jutaan tiap bulan!' }}
                                        </p>
                                    </div>

                                    <!-- Bottom Area: CTA & Website -->
                                    <div class="relative z-10 space-y-2 text-center">
                                        <div class="p-2.5 rounded-2xl bg-white text-slate-900 font-black text-xs shadow-lg flex items-center justify-center gap-1.5">
                                            <span>👉 Buka Web rentify.id Sekarang</span>
                                        </div>
                                        <p class="text-[9px] text-slate-400 font-bold uppercase tracking-widest">
                                            #SewaMudah #RentifyIndonesia
                                        </p>
                                    </div>

                                </div>

                                <!-- Smartphone Notch -->
                                <div class="absolute top-5 left-1/2 -translate-x-1/2 w-28 h-4 bg-slate-900 rounded-full flex items-center justify-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-slate-950 border border-slate-700"></span>
                                    <span class="w-1.5 h-1.5 rounded-full bg-sky-950"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Right Panel: Scene Selector & Customizer -->
                        <div class="lg:col-span-7 space-y-5">
                            
                            <!-- Timer & Progress Bar -->
                            <div class="bg-slate-50 p-5 rounded-2xl border border-slate-200 space-y-3">
                                <div class="flex items-center justify-between text-xs font-bold text-slate-700">
                                    <span id="simStatusText">Siap Dimainkan</span>
                                    <span id="simTimerText">00:00 / 00:25</span>
                                </div>
                                <div class="w-full bg-slate-200 rounded-full h-2.5 overflow-hidden">
                                    <div id="simProgressBar" class="bg-[#0369a1] h-2.5 rounded-full transition-all duration-300" style="width: 0%"></div>
                                </div>
                            </div>

                            <!-- Scene Switcher Buttons -->
                            <div class="space-y-2">
                                <label class="text-[11px] font-extrabold text-slate-700 uppercase tracking-wider block">Pilih Adegan untuk Ditampilkan:</label>
                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                                    <button type="button" onclick="setVisualScene(0)" class="scene-select-btn p-3 rounded-xl border border-slate-200 hover:border-sky-500 bg-white text-left transition active:scale-95">
                                        <span class="text-[9px] font-black text-slate-400 block uppercase">Adegan 1</span>
                                        <span class="text-xs font-bold text-slate-800">Hook 0-3s</span>
                                    </button>
                                    <button type="button" onclick="setVisualScene(1)" class="scene-select-btn p-3 rounded-xl border border-slate-200 hover:border-sky-500 bg-white text-left transition active:scale-95">
                                        <span class="text-[9px] font-black text-slate-400 block uppercase">Adegan 2</span>
                                        <span class="text-xs font-bold text-slate-800">Masalah 3-10s</span>
                                    </button>
                                    <button type="button" onclick="setVisualScene(2)" class="scene-select-btn p-3 rounded-xl border border-slate-200 hover:border-sky-500 bg-white text-left transition active:scale-95">
                                        <span class="text-[9px] font-black text-slate-400 block uppercase">Adegan 3</span>
                                        <span class="text-xs font-bold text-slate-800">Solusi 10-20s</span>
                                    </button>
                                    <button type="button" onclick="setVisualScene(3)" class="scene-select-btn p-3 rounded-xl border border-slate-200 hover:border-sky-500 bg-white text-left transition active:scale-95">
                                        <span class="text-[9px] font-black text-slate-400 block uppercase">Adegan 4</span>
                                        <span class="text-xs font-bold text-slate-800">CTA 20-30s</span>
                                    </button>
                                </div>
                            </div>

                            <!-- Storyboard Prompt & Guidance -->
                            <div class="p-4 rounded-2xl bg-sky-50/60 border border-sky-100 text-xs space-y-1">
                                <p class="font-black text-sky-900 flex items-center gap-1.5">
                                    <i class="fas fa-camera"></i>
                                    <span>Panduan Visual Sutradara (Format 9:16):</span>
                                </p>
                                <p class="text-slate-600 leading-relaxed">
                                    {{ $todayPost ? $todayPost->visual_prompt : 'Format vertikal 9:16, pencahayaan terang, dominan warna Sky Blue dan Emerald/Indigo untuk branding Rentify.' }}
                                </p>
                            </div>

                            <!-- Canvas Hidden for Export -->
                            <canvas id="exportCanvas" width="1080" height="1920" class="hidden"></canvas>

                        </div>

                    </div>
                </div>

            </div>

            <!-- ========================================== -->
            <!-- TAB 3: PENGATURAN JADWAL & API INTEGRASI  -->
            <!-- ========================================== -->
            <div id="tab-settings" class="ai-tab-panel hidden space-y-6">

                <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm space-y-6">
                    <div class="border-b border-slate-100 pb-4">
                        <h2 class="text-base font-black text-slate-800 flex items-center gap-2">
                            <i class="fas fa-sliders text-sky-500"></i>
                            Konfigurasi Otomasi Harian & Integrasi Media Sosial
                        </h2>
                        <p class="text-xs text-slate-500 mt-1">
                            Atur integrasi Google Gemini API di <code class="bg-slate-100 px-1 py-0.5 rounded text-sky-700">config/rentify_ai.php</code>, jam eksekusi Laravel Scheduler, dan akun Instagram/TikTok.
                        </p>
                    </div>

                    <form action="{{ route('admin.ai-marketing.save-settings') }}" method="POST" class="space-y-6">
                        @csrf

                        <!-- Section: Gemini API -->
                        <div class="space-y-4">
                            <h3 class="text-xs font-extrabold text-slate-400 uppercase tracking-wider flex items-center gap-2">
                                <i class="fas fa-brain text-sky-600"></i> 1. Google Gemini API Engine
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-[11px] font-extrabold text-slate-700 uppercase tracking-wider mb-1">GEMINI API KEY</label>
                                    <input type="password" name="gemini_api_key" value="{{ $settings['gemini_api_key'] }}" placeholder="AIzaSy..." class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-mono font-medium focus:outline-none focus:border-sky-500">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-extrabold text-slate-700 uppercase tracking-wider mb-1">MODEL GEMINI</label>
                                    <select name="gemini_model" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-bold focus:outline-none focus:border-sky-500">
                                        <option value="gemini-1.5-flash" {{ $settings['gemini_model'] === 'gemini-1.5-flash' ? 'selected' : '' }}>gemini-1.5-flash (Cepat, Hemat & Akurat - Rekomendasi)</option>
                                        <option value="gemini-1.5-pro" {{ $settings['gemini_model'] === 'gemini-1.5-pro' ? 'selected' : '' }}>gemini-1.5-pro (Penalaran Kompleks)</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Section: Laravel Scheduler & Rotation -->
                        <div class="space-y-4 pt-4 border-t border-slate-100">
                            <h3 class="text-xs font-extrabold text-slate-400 uppercase tracking-wider flex items-center gap-2">
                                <i class="fas fa-clock text-indigo-600"></i> 2. Jadwal Otomatis (Laravel Scheduler)
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-[11px] font-extrabold text-slate-700 uppercase tracking-wider mb-1">Jam Eksekusi Harian (WIB)</label>
                                    <input type="time" name="schedule_time" value="{{ $settings['schedule_time'] }}" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-bold focus:outline-none focus:border-sky-500">
                                    <p class="text-[10px] text-slate-400 mt-1">Command otomatis: <code class="text-sky-700">php artisan rentify:generate-daily-content</code></p>
                                </div>
                                <div class="space-y-2 pt-5">
                                    <label class="flex items-center gap-2 text-xs font-bold text-slate-700 cursor-pointer">
                                        <input type="checkbox" name="auto_post_instagram" {{ $settings['auto_post_instagram'] ? 'checked' : '' }} class="rounded text-sky-600 focus:ring-sky-500 w-4 h-4">
                                        <span>Auto-Post Langsung ke Instagram Feed/Reels saat di-generate</span>
                                    </label>
                                    <label class="flex items-center gap-2 text-xs font-bold text-slate-700 cursor-pointer">
                                        <input type="checkbox" name="auto_post_tiktok" {{ $settings['auto_post_tiktok'] ? 'checked' : '' }} class="rounded text-sky-600 focus:ring-sky-500 w-4 h-4">
                                        <span>Auto-Post Langsung ke TikTok saat di-generate</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Section: Social Media APIs -->
                        <div class="space-y-4 pt-4 border-t border-slate-100">
                            <h3 class="text-xs font-extrabold text-slate-400 uppercase tracking-wider flex items-center gap-2">
                                <i class="fas fa-share-nodes text-rose-600"></i> 3. Kredensial Media Sosial
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-[11px] font-extrabold text-slate-700 uppercase tracking-wider mb-1">Instagram Graph API Token</label>
                                    <input type="password" name="instagram_token" value="{{ $settings['instagram_token'] }}" placeholder="EAA..." class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-mono focus:outline-none focus:border-sky-500">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-extrabold text-slate-700 uppercase tracking-wider mb-1">Instagram Business Account ID</label>
                                    <input type="text" name="instagram_account_id" value="{{ $settings['instagram_account_id'] }}" placeholder="1784..." class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-mono focus:outline-none focus:border-sky-500">
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-[11px] font-extrabold text-slate-700 uppercase tracking-wider mb-1">TikTok Access Token</label>
                                    <input type="password" name="tiktok_token" value="{{ $settings['tiktok_token'] }}" placeholder="act..." class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-mono focus:outline-none focus:border-sky-500">
                                </div>
                            </div>
                        </div>

                        <div class="pt-4 flex justify-end">
                            <button type="submit" class="py-3 px-6 rounded-2xl bg-slate-900 hover:bg-slate-800 text-white font-black text-xs shadow-md transition flex items-center gap-2">
                                <i class="fas fa-save"></i>
                                <span>Simpan Perubahan Pengaturan</span>
                            </button>
                        </div>
                    </form>
                </div>

            </div>

            <!-- ========================================== -->
            <!-- TAB 4: RIWAYAT KONTEN HARIAN (GANJIL-GENAP) -->
            <!-- ========================================== -->
            <div id="tab-history" class="ai-tab-panel hidden space-y-6">

                <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
                    <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                        <div>
                            <h3 class="text-sm font-black text-slate-800 flex items-center gap-2">
                                <i class="fas fa-calendar-days text-sky-500"></i>
                                Kalender Log Konten Auto-Promosi Harian
                            </h3>
                            <p class="text-[10px] text-slate-400">Arsip ide konten Reels & TikTok yang telah dibuat oleh AI</p>
                        </div>
                        <span class="text-xs font-bold text-slate-500">{{ $recentPosts->count() }} Konten Terdata</span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs text-slate-600">
                            <thead class="bg-slate-50 text-[10px] uppercase font-black text-slate-400 border-b border-slate-100">
                                <tr>
                                    <th class="py-3 px-4">Tanggal</th>
                                    <th class="py-3 px-4">Pola Hari</th>
                                    <th class="py-3 px-4">Target Audiens</th>
                                    <th class="py-3 px-4">Hook Masalah</th>
                                    <th class="py-3 px-4">Status Publish</th>
                                    <th class="py-3 px-4 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 font-medium">
                                @forelse($recentPosts as $post)
                                <tr class="hover:bg-slate-50/70 transition">
                                    <td class="py-3.5 px-4 font-bold text-slate-800 whitespace-nowrap">
                                        {{ $post->tanggal->format('d M Y') }}
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase {{ $post->day_type === 'ganjil' ? 'bg-amber-100 text-amber-800' : 'bg-sky-100 text-sky-800' }}">
                                            Tgl {{ $post->day_number }} ({{ $post->day_type }})
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-black border flex items-center gap-1 w-max {{ $post->target_badge['color'] }}">
                                            <i class="fas {{ $post->target_badge['icon'] }}"></i>
                                            {{ $post->target_badge['text'] }}
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4 max-w-xs truncate text-slate-800 font-semibold" title="{{ $post->hook_masalah }}">
                                        "{{ $post->hook_masalah }}"
                                    </td>
                                    <td class="py-3.5 px-4 whitespace-nowrap">
                                        <div class="flex items-center gap-1.5">
                                            <span class="w-2 h-2 rounded-full {{ $post->posted_to_instagram ? 'bg-emerald-500' : 'bg-slate-300' }}"></span>
                                            <span class="text-[10px] {{ $post->posted_to_instagram ? 'text-emerald-700 font-bold' : 'text-slate-400' }}">IG</span>
                                            <span class="w-2 h-2 rounded-full {{ $post->posted_to_tiktok ? 'bg-emerald-500' : 'bg-slate-300' }} ml-1"></span>
                                            <span class="text-[10px] {{ $post->posted_to_tiktok ? 'text-emerald-700 font-bold' : 'text-slate-400' }}">TikTok</span>
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-4 text-right space-x-1 whitespace-nowrap">
                                        <button onclick="publishToSocial('all', {{ $post->id }})" class="p-1.5 px-2.5 rounded-lg bg-sky-50 text-sky-700 hover:bg-sky-100 font-bold text-[10px] transition">
                                            <i class="fas fa-paper-plane mr-1"></i> Publish
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="py-8 text-center text-slate-400">
                                        Belum ada riwayat konten harian.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

            <!-- ========================================== -->
            <!-- TAB 5: BANNER & KAMPANYE EVENT PLATFORM    -->
            <!-- ========================================== -->
            <div id="tab-campaign" class="ai-tab-panel hidden space-y-6">

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

                    <!-- Form Generator Kampanye -->
                    <div class="lg:col-span-5 space-y-6">
                        <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm space-y-4">
                            <div class="border-b border-slate-100 pb-3">
                                <h3 class="text-sm font-black text-slate-800 flex items-center gap-2">
                                    <i class="fas fa-bullhorn text-sky-500"></i>
                                    Rancang Kampanye Promosi Acara
                                </h3>
                                <p class="text-xs text-slate-400">Buat ide slogan, broadcast, dan banner momen tertentu</p>
                            </div>

                            <form id="adminCampaignForm" onsubmit="handleGenerateAdminCampaign(event)" class="space-y-4">
                                @csrf
                                <div>
                                    <label class="block text-[11px] font-extrabold text-slate-700 uppercase tracking-wider mb-1.5">
                                        Tema / Momen Acara Promosi
                                    </label>
                                    <input type="text" name="tema" id="admin_tema" required placeholder="Contoh: Promo Gajian Payday / Libur Semester / Mudik Hemat" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white text-xs font-bold text-slate-800 focus:outline-none focus:border-sky-500 shadow-xs">
                                </div>

                                <div class="flex flex-wrap gap-1.5">
                                    <button type="button" onclick="setTema('Payday Promo Gajian Akhir Bulan')" class="px-2.5 py-1 rounded-lg text-[10px] font-bold bg-slate-100 hover:bg-slate-200 text-slate-700 transition">💰 Payday</button>
                                    <button type="button" onclick="setTema('Liburan Akhir Pekan Seru & Hemat')" class="px-2.5 py-1 rounded-lg text-[10px] font-bold bg-slate-100 hover:bg-slate-200 text-slate-700 transition">🏖️ Weekend</button>
                                    <button type="button" onclick="setTema('Spesial Mudik & Perlengkapan Pulang Kampung')" class="px-2.5 py-1 rounded-lg text-[10px] font-bold bg-slate-100 hover:bg-slate-200 text-slate-700 transition">🚗 Mudik</button>
                                    <button type="button" onclick="setTema('Peralatan Tugas & Project Mahasiswa')" class="px-2.5 py-1 rounded-lg text-[10px] font-bold bg-slate-100 hover:bg-slate-200 text-slate-700 transition">🎓 Kampus</button>
                                </div>

                                <div>
                                    <label class="block text-[11px] font-extrabold text-slate-700 uppercase tracking-wider mb-1.5">Target Segmen Pelanggan</label>
                                    <input type="text" name="target_audiens" id="admin_target" placeholder="Contoh: Mahasiswa, Wisatawan, Fotografer" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-medium focus:outline-none focus:border-sky-500">
                                </div>

                                <div>
                                    <label class="block text-[11px] font-extrabold text-slate-700 uppercase tracking-wider mb-1.5">Usulan Skema Diskon</label>
                                    <input type="text" name="usulan_diskon" id="admin_diskon" placeholder="Contoh: Diskon 20% s/d Rp 50.000" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-medium focus:outline-none focus:border-sky-500">
                                </div>

                                <button type="submit" id="btnAdminCampaignSubmit" class="w-full py-3 px-4 rounded-xl bg-gradient-to-r from-[#0369a1] to-sky-500 hover:from-sky-700 hover:to-sky-600 text-white font-black text-xs shadow-md transition flex items-center justify-center gap-2">
                                    <i class="fas fa-wand-magic-sparkles"></i>
                                    <span>Generate Kampanye dengan AI</span>
                                </button>
                            </form>
                        </div>

                        <!-- Upload Banner Form -->
                        <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm space-y-4">
                            <div class="flex items-center gap-2 text-slate-800 font-black text-sm border-b border-slate-100 pb-3">
                                <i class="fas fa-images text-sky-500"></i>
                                <span>Publikasikan Banner Baru ke Aplikasi</span>
                            </div>
                            <form action="{{ route('admin.ai-marketing.create-banner') }}" method="POST" enctype="multipart/form-data" class="space-y-3">
                                @csrf
                                <div>
                                    <label class="block text-[10px] font-extrabold text-slate-500 uppercase tracking-wider mb-1">Judul / Slogan Promo Banner</label>
                                    <input type="text" name="judul_promo" id="banner_judul_promo" required placeholder="Judul banner promo..." class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-800 focus:outline-none focus:border-sky-500">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-extrabold text-slate-500 uppercase tracking-wider mb-1">File Gambar Banner (JPG / PNG / WebP)</label>
                                    <input type="file" name="gambar" required accept="image/*" class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-sky-50 file:text-sky-700 hover:file:bg-sky-100 cursor-pointer">
                                </div>
                                <button type="submit" class="w-full py-2.5 px-3 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-extrabold text-xs transition flex items-center justify-center gap-1.5">
                                    <i class="fas fa-upload"></i> Unggah & Tayangkan Banner
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Hasil AI Campaign & Banner Aktif -->
                    <div class="lg:col-span-7 space-y-6">

                        <div id="adminLoadingState" class="hidden bg-white rounded-3xl p-12 border border-slate-200 shadow-sm text-center">
                            <div class="inline-flex items-center justify-center w-14 h-14 rounded-full bg-sky-50 text-sky-600 mb-4 animate-spin">
                                <i class="fas fa-spinner text-2xl"></i>
                            </div>
                            <h3 class="text-base font-black text-slate-800">Menyusun Strategi Kampanye...</h3>
                            <p class="text-xs text-slate-400 mt-1">AI sedang menganalisis demografi audiens dan merumuskan punchline kampanye.</p>
                        </div>

                        <div id="adminResultContainer" class="hidden bg-white rounded-3xl p-6 border border-slate-200 shadow-sm space-y-4">
                            <div class="border-b border-slate-100 pb-3">
                                <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider bg-sky-50 text-sky-700 border border-sky-100">
                                    Hasil AI Campaign
                                </span>
                                <h3 id="resJudulKampanye" class="text-base font-black text-slate-800 mt-1"></h3>
                                <p id="resSlogan" class="text-xs text-slate-500 italic mt-0.5"></p>
                            </div>

                            <div class="p-4 rounded-2xl bg-sky-50/60 border border-sky-100 space-y-2">
                                <p class="text-[10px] font-black uppercase tracking-wider text-sky-800">Ide Banner Aplikasi:</p>
                                <p id="resBannerJudul" class="text-sm font-black text-slate-900"></p>
                                <p id="resBannerSub" class="text-xs text-slate-600"></p>
                                <button type="button" onclick="useBannerIdea()" class="mt-2 text-[11px] font-bold text-sky-700 hover:text-sky-900 flex items-center gap-1">
                                    <i class="fas fa-arrow-down"></i> Masukkan ke Form Upload Banner
                                </button>
                            </div>

                            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100 space-y-1">
                                <p class="text-[10px] font-black uppercase tracking-wider text-slate-400">Pesan Broadcast Push / WhatsApp:</p>
                                <p id="resBroadcast" class="text-xs text-slate-700 leading-relaxed font-medium"></p>
                            </div>
                        </div>

                        <!-- Active Banners List -->
                        <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm space-y-4">
                            <h3 class="text-sm font-black text-slate-800 flex items-center gap-2 border-b border-slate-100 pb-3">
                                <i class="fas fa-layer-group text-slate-400"></i>
                                Banner Tayang di Halaman Depan Customer
                            </h3>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                @forelse($activeBanners as $banner)
                                <div class="rounded-2xl border border-slate-200 overflow-hidden relative group">
                                    <img src="{{ $banner->gambar_url }}" alt="{{ $banner->judul_promo }}" class="w-full h-32 object-cover">
                                    <div class="p-2.5 bg-white flex items-center justify-between">
                                        <p class="text-xs font-bold text-slate-800 truncate" title="{{ $banner->judul_promo }}">{{ $banner->judul_promo }}</p>
                                        <form action="{{ route('admin.banner.destroy', $banner->id) }}" method="POST" onsubmit="return confirm('Hapus banner ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-rose-500 hover:text-rose-700 p-1 text-xs">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                                @empty
                                <div class="col-span-2 py-6 text-center text-xs text-slate-400">
                                    Belum ada banner aktif di beranda.
                                </div>
                                @endforelse
                            </div>
                        </div>

                    </div>

                </div>

            </div>

        </div>

    </main>

    <!-- TOAST NOTIFICATION -->
    <div id="aiToast" class="fixed bottom-6 right-6 z-50 transform translate-y-20 opacity-0 transition-all duration-300 pointer-events-none">
        <div class="bg-slate-900 text-white px-5 py-3 rounded-2xl shadow-xl flex items-center gap-3 text-xs font-bold border border-slate-700">
            <i id="toastIcon" class="fas fa-check-circle text-emerald-400 text-base"></i>
            <span id="toastMsg">Berhasil disalin!</span>
        </div>
    </div>

    <!-- JAVASCRIPT -->
    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        // Tab Navigation
        function switchAiTab(tabId) {
            document.querySelectorAll('.ai-tab-panel').forEach(el => el.classList.add('hidden'));
            document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));

            const target = document.getElementById(tabId);
            if (target) {
                target.classList.remove('hidden');
            }

            const activeBtn = document.getElementById('btn-' + tabId);
            if (activeBtn) {
                activeBtn.classList.add('active');
            }
        }

        // Toast Message
        function showToast(msg, icon = 'fa-check-circle', color = 'text-emerald-400') {
            const toast = document.getElementById('aiToast');
            const toastMsg = document.getElementById('toastMsg');
            const toastIcon = document.getElementById('toastIcon');

            toastMsg.innerText = msg;
            toastIcon.className = `fas ${icon} ${color} text-base`;

            toast.classList.remove('translate-y-20', 'opacity-0');
            setTimeout(() => {
                toast.classList.add('translate-y-20', 'opacity-0');
            }, 3000);
        }

        // Copy Text Helper
        function copyText(elementId) {
            const el = document.getElementById(elementId);
            const text = el.tagName === 'TEXTAREA' || el.tagName === 'INPUT' ? el.value : el.innerText;
            navigator.clipboard.writeText(text).then(() => {
                showToast('Teks berhasil disalin ke clipboard!');
            });
        }

        // Copy Full Script
        function copyFullScript() {
            const container = document.getElementById('scriptScenesContainer');
            if (!container) return;
            const text = container.innerText;
            navigator.clipboard.writeText(text).then(() => {
                showToast('Naskah video lengkap berhasil disalin!');
            });
        }

        // Share WhatsApp
        function shareWhatsApp() {
            const caption = document.getElementById('fullCaptionText');
            if (!caption) return;
            const text = encodeURIComponent(caption.value);
            window.open(`https://api.whatsapp.com/send?text=${text}`, '_blank');
        }

        // Publish to Social Media (AJAX)
        function publishToSocial(channel, postId) {
            showToast(`Mengirim konten ke ${channel.toUpperCase()}...`, 'fa-spinner fa-spin', 'text-sky-400');

            fetch(`/admin/ai-marketing/publish-social/${postId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ channel: channel })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showToast(`Berhasil diproses ke ${channel.toUpperCase()}!`, 'fa-check-circle', 'text-emerald-400');
                } else {
                    showToast(data.message || 'Gagal mempublikasikan', 'fa-triangle-exclamation', 'text-rose-400');
                }
            })
            .catch(err => {
                showToast('Terjadi kesalahan koneksi', 'fa-triangle-exclamation', 'text-rose-400');
            });
        }

        // Interactive Studio 9:16 Scenes
        const currentScenes = @json($todayPost ? ($todayPost->naskah_video ?? []) : []);
        const todayHook = @json($todayPost ? $todayPost->hook_masalah : 'Mau Acara Sehari Masa Harus Beli Baru?');
        const isCustomerTarget = @json($todayPost ? $todayPost->isCustomerTarget() : true);

        function setVisualScene(index) {
            const screenMainText = document.getElementById('screenMainText');
            const screenSubText = document.getElementById('screenSubText');
            const screenSceneBadge = document.getElementById('screenSceneBadge');
            const screenSticker = document.getElementById('screenSticker');

            if (!currentScenes || currentScenes.length === 0) return;

            const scene = currentScenes[index] || currentScenes[0];
            screenSceneBadge.innerText = `Adegan ${index + 1} (${scene.detik || ''})`;
            
            if (index === 0) {
                screenSticker.innerText = isCustomerTarget ? '⚡ SOLUSI ANTI BONCOS' : '💸 MESIN CUAN PASIF';
                screenMainText.innerText = todayHook;
                screenSubText.innerText = scene.voiceover || 'Stop beli baru untuk barang yang cuma dipakai sesekali!';
            } else if (index === 1) {
                screenSticker.innerText = isCustomerTarget ? '⚠️ DILEMA BELI MAHAL' : '⏳ BARANG BERDEBU';
                screenMainText.innerText = isCustomerTarget ? 'Beli Jutaan Cuma Dipakai 2 Kali?' : 'Barang Bagus Nganggur di Gudang?';
                screenSubText.innerText = scene.voiceover || '';
            } else if (index === 2) {
                screenSticker.innerText = isCustomerTarget ? '💡 SEWA AJA DI RENTIFY' : '🚀 JADI VENDOR RENTIFY';
                screenMainText.innerText = isCustomerTarget ? 'Sewa Hemat Mulai Puluhan Ribu!' : 'Dapat Cuan Pasif Tiap Bulan!';
                screenSubText.innerText = scene.voiceover || '';
            } else {
                screenSticker.innerText = '📲 CALL TO ACTION';
                screenMainText.innerText = isCustomerTarget ? 'Cek Katalog di rentify.id Sekarang!' : 'Daftarkan Barangmu di rentify.id!';
                screenSubText.innerText = 'Klik link di bio profil untuk info selengkapnya.';
            }
        }

        // Video Simulation Player
        let simInterval = null;
        let simCurrentScene = 0;
        function playVideoSimulation() {
            if (simInterval) {
                clearInterval(simInterval);
                simInterval = null;
                document.getElementById('btnPlayVideoSim').innerHTML = '<i class="fas fa-play text-emerald-400"></i><span>Putar Simulasi Video</span>';
                document.getElementById('simStatusText').innerText = 'Simulasi Dihentikan';
                return;
            }

            document.getElementById('btnPlayVideoSim').innerHTML = '<i class="fas fa-stop text-rose-400"></i><span>Hentikan Simulasi</span>';
            simCurrentScene = 0;
            let seconds = 0;
            const totalSeconds = 24;

            setVisualScene(0);
            document.getElementById('simStatusText').innerText = 'Sedang Memutar Adegan 1...';

            simInterval = setInterval(() => {
                seconds++;
                const progress = (seconds / totalSeconds) * 100;
                document.getElementById('simProgressBar').style.width = `${progress}%`;
                document.getElementById('simTimerText').innerText = `00:${seconds < 10 ? '0' + seconds : seconds} / 00:24`;

                if (seconds === 3) {
                    setVisualScene(1);
                    document.getElementById('simStatusText').innerText = 'Sedang Memutar Adegan 2 (Masalah)...';
                } else if (seconds === 10) {
                    setVisualScene(2);
                    document.getElementById('simStatusText').innerText = 'Sedang Memutar Adegan 3 (Solusi Rentify)...';
                } else if (seconds === 18) {
                    setVisualScene(3);
                    document.getElementById('simStatusText').innerText = 'Sedang Memutar Adegan 4 (Call To Action)...';
                } else if (seconds >= totalSeconds) {
                    clearInterval(simInterval);
                    simInterval = null;
                    document.getElementById('btnPlayVideoSim').innerHTML = '<i class="fas fa-rotate-right text-emerald-400"></i><span>Putar Ulang Simulasi</span>';
                    document.getElementById('simStatusText').innerText = 'Simulasi Selesai';
                    showToast('Simulasi video 9:16 selesai diputar!');
                }
            }, 1000);
        }

        // Export Canvas High-Res 9:16 (1080 x 1920)
        function exportCanvas916() {
            showToast('Membuat render visual 9:16 (1080x1920)...', 'fa-spinner fa-spin', 'text-sky-400');
            const canvas = document.getElementById('exportCanvas');
            const ctx = canvas.getContext('2d');

            // Draw Background Gradient
            const gradient = ctx.createLinearGradient(0, 0, 0, 1920);
            if (isCustomerTarget) {
                gradient.addColorStop(0, '#0f172a');
                gradient.addColorStop(0.5, '#064e3b');
                gradient.addColorStop(1, '#134e4a');
            } else {
                gradient.addColorStop(0, '#0f172a');
                gradient.addColorStop(0.5, '#312e81');
                gradient.addColorStop(1, '#0c4a6e');
            }
            ctx.fillStyle = gradient;
            ctx.fillRect(0, 0, 1080, 1920);

            // Draw Rentify Brand Header
            ctx.fillStyle = '#38bdf8';
            ctx.font = 'bold 44px "Plus Jakarta Sans", sans-serif';
            ctx.fillText('⚡ RENTIFY INDONESIA', 80, 140);

            ctx.fillStyle = '#94a3b8';
            ctx.font = '28px "Plus Jakarta Sans", sans-serif';
            ctx.fillText('Platform Persewaan Mudah, Murah & Dekat', 80, 185);

            // Draw Badge
            ctx.fillStyle = isCustomerTarget ? '#10b981' : '#6366f1';
            ctx.beginPath();
            ctx.roundRect(80, 260, 420, 60, [30]);
            ctx.fill();

            ctx.fillStyle = '#ffffff';
            ctx.font = 'bold 26px "Plus Jakarta Sans", sans-serif';
            ctx.fillText(isCustomerTarget ? 'SOLUSI GAYA HIDUP HEMAT' : 'MONETISASI BARANG NGANGGUR', 105, 300);

            // Draw Main Hook
            ctx.fillStyle = '#ffffff';
            ctx.font = '900 64px "Plus Jakarta Sans", sans-serif';
            wrapText(ctx, `"${todayHook}"`, 80, 480, 920, 85);

            // Draw Middle Comparison Box
            ctx.fillStyle = 'rgba(255, 255, 255, 0.08)';
            ctx.beginPath();
            ctx.roundRect(80, 950, 920, 400, [32]);
            ctx.fill();

            ctx.fillStyle = '#38bdf8';
            ctx.font = 'bold 36px "Plus Jakarta Sans", sans-serif';
            ctx.fillText(isCustomerTarget ? '💡 Kenapa Harus Sewa di Rentify?' : '💰 Keuntungan Jadi Vendor Rentify:', 120, 1030);

            ctx.fillStyle = '#e2e8f0';
            ctx.font = '32px "Plus Jakarta Sans", sans-serif';
            if (isCustomerTarget) {
                ctx.fillText('• Hemat hingga 90% dibanding beli barang baru', 120, 1100);
                ctx.fillText('• Barang berkualitas, bersih & siap langsung pakai', 120, 1170);
                ctx.fillText('• Praktis, sewa dari pemilik rental terdekat di kotamu', 120, 1240);
            } else {
                ctx.fillText('• Barang nganggur jadi passive income bulanan', 120, 1100);
                ctx.fillText('• Gratis buka toko tanpa modal ruko fisik', 120, 1170);
                ctx.fillText('• Keamanan data & uang sewa terverifikasi', 120, 1240);
            }

            // Bottom CTA Box
            ctx.fillStyle = '#ffffff';
            ctx.beginPath();
            ctx.roundRect(80, 1550, 920, 140, [32]);
            ctx.fill();

            ctx.fillStyle = '#0f172a';
            ctx.font = 'bold 44px "Plus Jakarta Sans", sans-serif';
            ctx.textAlign = 'center';
            ctx.fillText('👉 Buka Web rentify.id Sekarang!', 540, 1635);
            ctx.textAlign = 'left';

            // Download Trigger
            const dataUrl = canvas.toDataURL('image/png');
            const link = document.createElement('a');
            link.download = `Rentify_Reels_9_16_${Date.now()}.png`;
            link.href = dataUrl;
            link.click();

            // Save to Backend
            @if($todayPost)
            fetch(`/admin/ai-marketing/save-visual/{{ $todayPost->id }}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ image_data: dataUrl })
            });
            @endif

            showToast('Visual 9:16 (1080x1920) berhasil diunduh!');
        }

        // Helper Canvas Wrap Text
        function wrapText(ctx, text, x, y, maxWidth, lineHeight) {
            const words = text.split(' ');
            let line = '';
            for (let n = 0; n < words.length; n++) {
                const testLine = line + words[n] + ' ';
                const metrics = ctx.measureText(testLine);
                const testWidth = metrics.width;
                if (testWidth > maxWidth && n > 0) {
                    ctx.fillText(line, x, y);
                    line = words[n] + ' ';
                    y += lineHeight;
                } else {
                    line = testLine;
                }
            }
            ctx.fillText(line, x, y);
        }

        // Quick Preset Theme
        function setTema(val) {
            document.getElementById('admin_tema').value = val;
        }

        // Legacy Campaign Generator (AJAX)
        function handleGenerateAdminCampaign(e) {
            e.preventDefault();
            const form = document.getElementById('adminCampaignForm');
            const submitBtn = document.getElementById('btnAdminCampaignSubmit');
            const loadingState = document.getElementById('adminLoadingState');
            const resultBox = document.getElementById('adminResultContainer');

            submitBtn.disabled = true;
            submitBtn.classList.add('opacity-50');
            loadingState.classList.remove('hidden');
            resultBox.classList.add('hidden');

            const formData = new FormData(form);

            fetch('{{ route("admin.ai-marketing.generate-campaign") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                loadingState.classList.add('hidden');
                submitBtn.disabled = false;
                submitBtn.classList.remove('opacity-50');

                if (data.success && data.campaign) {
                    const c = data.campaign;
                    document.getElementById('resJudulKampanye').innerText = c.judul_kampanye || '';
                    document.getElementById('resSlogan').innerText = c.slogan || '';
                    if (c.banner_promo) {
                        document.getElementById('resBannerJudul').innerText = c.banner_promo.judul_banner || '';
                        document.getElementById('resBannerSub').innerText = c.banner_promo.deskripsi_banner || '';
                    }
                    document.getElementById('resBroadcast').innerText = c.broadcast_notifikasi || '';
                    resultBox.classList.remove('hidden');
                    showToast('Kampanye berhasil dibuat oleh AI!');
                }
            })
            .catch(() => {
                loadingState.classList.add('hidden');
                submitBtn.disabled = false;
                submitBtn.classList.remove('opacity-50');
                showToast('Gagal menghasilkan kampanye', 'fa-triangle-exclamation', 'text-rose-400');
            });
        }

        function useBannerIdea() {
            const ideaTitle = document.getElementById('resBannerJudul').innerText;
            const bannerInput = document.getElementById('banner_judul_promo');
            if (bannerInput && ideaTitle) {
                bannerInput.value = ideaTitle;
                bannerInput.focus();
                bannerInput.scrollIntoView({ behavior: 'smooth' });
                showToast('Ide judul banner disalin ke form!');
            }
        }
    </script>
</body>
</html>
