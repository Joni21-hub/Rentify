@extends('layouts.vendor')

@section('title', 'AI Agent Marketing & Auto-Promosi — Rentify')

@section('content')
<div class="space-y-6">

    <!-- Header Section -->
    <div class="bg-gradient-to-r from-slate-900 via-sky-950 to-blue-900 text-white rounded-3xl p-6 sm:p-8 shadow-xl relative overflow-hidden border border-sky-800/40">
        <div class="absolute -right-10 -bottom-10 w-64 h-64 bg-sky-500/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute right-12 top-6 opacity-15 hidden md:block">
            <i class="fa-solid fa-wand-magic-sparkles text-9xl text-sky-400"></i>
        </div>

        <div class="relative z-10 max-w-2xl">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-bold bg-sky-500/20 text-sky-300 border border-sky-400/30 mb-3">
                <i class="fa-solid fa-robot"></i>
                <span>Rentify AI Marketing Agent v2.0</span>
                <span class="w-2 h-2 rounded-full {{ $isAiOnline ? 'bg-emerald-400 animate-pulse' : 'bg-amber-400' }}"></span>
                <span class="text-[10px] text-slate-300">{{ $isAiOnline ? 'Gemini AI Active' : 'Smart Heuristic Active' }}</span>
            </div>

            <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-white mb-2">
                Asisten Pemasaran & <span class="bg-gradient-to-r from-sky-400 to-cyan-300 bg-clip-text text-transparent">Auto-Promosi Toko</span>
            </h1>
            <p class="text-xs sm:text-sm text-slate-300 leading-relaxed">
                Tingkatkan omset persewaan Anda dengan rekomendasi diskon otomatis untuk stok tertidur, audit performa inventaris, dan generator materi promosi media sosial bertenaga AI.
            </p>

            <div class="flex flex-wrap items-center gap-3 mt-5 pt-4 border-t border-white/10 text-xs font-bold text-slate-300">
                <div class="flex items-center gap-1.5">
                    <i class="fa-solid fa-boxes-stacked text-sky-400"></i>
                    <span>{{ $auditResult['summary']['total_produk'] ?? 0 }} Produk Terdaftar</span>
                </div>
                <span class="text-white/20">•</span>
                <div class="flex items-center gap-1.5 text-amber-300">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    <span>{{ $auditResult['summary']['total_stagnant'] ?? 0 }} Perlu Dorongan Promosi</span>
                </div>
                <span class="text-white/20">•</span>
                <div class="flex items-center gap-1.5 text-emerald-300">
                    <i class="fa-solid fa-ticket"></i>
                    <span>{{ $auditResult['summary']['active_vouchers_count'] ?? 0 }} Voucher Aktif</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Feedback Alerts -->
    @if(session('success'))
    <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center justify-between text-xs sm:text-sm font-bold shadow-sm">
        <div class="flex items-center gap-2">
            <i class="fa-solid fa-circle-check text-emerald-600 text-base"></i>
            <span>{{ session('success') }}</span>
        </div>
        <button type="button" onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>
    @endif

    <!-- Navigation Tabs -->
    <div class="flex border-b border-slate-200 gap-2 sm:gap-4 overflow-x-auto pb-1 text-xs sm:text-sm font-bold">
        <button type="button" onclick="switchAiTab('copywriter')" id="tab-btn-copywriter" class="ai-tab-btn flex items-center gap-2 px-4 py-2.5 rounded-xl border-b-2 border-sky-600 text-sky-600 bg-sky-50 transition">
            <i class="fa-solid fa-pen-nib"></i>
            <span>AI Copywriter & Generator Konten</span>
        </button>
        <button type="button" onclick="switchAiTab('radar')" id="tab-btn-radar" class="ai-tab-btn flex items-center gap-2 px-4 py-2.5 rounded-xl border-b-2 border-transparent text-slate-500 hover:text-slate-800 hover:bg-slate-50 transition">
            <i class="fa-solid fa-radar"></i>
            <span>Radar Stok & Rekomendasi Diskon</span>
            @if(($auditResult['summary']['total_stagnant'] ?? 0) > 0)
                <span class="px-2 py-0.5 rounded-full text-[10px] bg-rose-500 text-white font-extrabold animate-pulse">
                    {{ $auditResult['summary']['total_stagnant'] }}
                </span>
            @endif
        </button>
        <button type="button" onclick="switchAiTab('vouchers')" id="tab-btn-vouchers" class="ai-tab-btn flex items-center gap-2 px-4 py-2.5 rounded-xl border-b-2 border-transparent text-slate-500 hover:text-slate-800 hover:bg-slate-50 transition">
            <i class="fa-solid fa-ticket-simple"></i>
            <span>Voucher Toko & Tips AI</span>
        </button>
    </div>

    <!-- ======================================================== -->
    <!-- TAB 1: AI COPYWRITER & GENERATOR KONTEN -->
    <!-- ======================================================== -->
    <div id="tab-pane-copywriter" class="ai-tab-pane space-y-6">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

            <!-- Form Generator -->
            <div class="lg:col-span-5 bg-white rounded-3xl p-6 border border-slate-200 shadow-sm space-y-5">
                <div class="border-b border-slate-100 pb-4">
                    <h2 class="text-base font-black text-slate-800 flex items-center gap-2">
                        <i class="fa-solid fa-sliders text-sky-500"></i>
                        Parameter Materi Iklan
                    </h2>
                    <p class="text-xs text-slate-500 mt-1">Pilih barang dan format copywriting yang ingin Anda rancang.</p>
                </div>

                <form id="copywriterForm" onsubmit="handleGenerateCopy(event)" class="space-y-4">
                    @csrf
                    <!-- Pilih Barang -->
                    <div>
                        <label class="block text-[11px] font-extrabold text-slate-700 uppercase tracking-wider mb-1.5">
                            Pilih Produk Rental
                        </label>
                        <select name="barang_id" id="copy_barang_id" onchange="onSelectBarang(this)" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50/50 text-xs font-bold text-slate-800 focus:outline-none focus:border-sky-500 focus:bg-white shadow-xs">
                            <option value="">-- Ketik / Pilih Barang Toko --</option>
                            @foreach($products as $prod)
                                <option value="{{ $prod->id }}" 
                                    data-nama="{{ $prod->nama }}"
                                    data-kategori="{{ $prod->kategori->nama ?? 'Umum' }}"
                                    data-harga="{{ $prod->harga_sewa_harian }}"
                                    data-deskripsi="{{ strip_tags($prod->deskripsi ?? '') }}"
                                    data-stok="{{ $prod->stok_total }}">
                                    {{ $prod->nama }} (Rp {{ number_format($prod->harga_sewa_harian, 0, ',', '.') }}/hari)
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Custom Input (Jika barang tidak dipilih) -->
                    <div id="manualProductBox" class="space-y-3 pt-1 border-t border-dashed border-slate-200">
                        <div>
                            <label class="block text-[10px] font-extrabold text-slate-500 uppercase tracking-wider mb-1">Nama Produk / Paket</label>
                            <input type="text" name="manual_nama" id="manual_nama" placeholder="Contoh: Paket Camping 4 Orang Lengkap" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-medium text-slate-800 focus:outline-none focus:border-sky-500">
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-[10px] font-extrabold text-slate-500 uppercase tracking-wider mb-1">Tarif Sewa/Hari (Rp)</label>
                                <input type="number" name="manual_harga" id="manual_harga" placeholder="75000" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-medium text-slate-800 focus:outline-none focus:border-sky-500">
                            </div>
                            <div>
                                <label class="block text-[10px] font-extrabold text-slate-500 uppercase tracking-wider mb-1">Kategori</label>
                                <input type="text" name="manual_kategori" id="manual_kategori" placeholder="Outdoor / Kamera" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-medium text-slate-800 focus:outline-none focus:border-sky-500">
                            </div>
                        </div>
                    </div>

                    <!-- Channel & Tone -->
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] font-extrabold text-slate-700 uppercase tracking-wider mb-1.5">Channel Promosi</label>
                            <select name="platform" id="copy_platform" class="w-full px-3 py-2.5 rounded-xl border border-slate-200 bg-white text-xs font-bold text-slate-800 focus:outline-none focus:border-sky-500">
                                <option value="all">Semua Channel</option>
                                <option value="whatsapp">WhatsApp Broadcast</option>
                                <option value="instagram">Instagram Feed / Story</option>
                                <option value="tiktok">TikTok / Reels Script</option>
                                <option value="banner">Banner Headline</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[11px] font-extrabold text-slate-700 uppercase tracking-wider mb-1.5">Gaya Bahasa (Tone)</label>
                            <select name="tone" id="copy_tone" class="w-full px-3 py-2.5 rounded-xl border border-slate-200 bg-white text-xs font-bold text-slate-800 focus:outline-none focus:border-sky-500">
                                <option value="fomo">Urgensi & Diskon Heboh</option>
                                <option value="casual">Santai & Friendly</option>
                                <option value="professional">Resmi & Terpercaya</option>
                                <option value="solution">Solutif & Hemat Budget</option>
                            </select>
                        </div>
                    </div>

                    <!-- Diskon Info -->
                    <div>
                        <label class="block text-[11px] font-extrabold text-slate-700 uppercase tracking-wider mb-1.5">Voucher / Diskon Khusus (Opsional)</label>
                        <input type="text" name="diskon" id="copy_diskon" placeholder="Misal: Diskon 20% kode VOUCHERHEMAT atau Gratis Ongkir" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-medium text-slate-800 focus:outline-none focus:border-sky-500">
                    </div>

                    <!-- Catatan Tambahan -->
                    <div>
                        <label class="block text-[11px] font-extrabold text-slate-700 uppercase tracking-wider mb-1.5">Catatan Tambahan untuk AI</label>
                        <textarea name="custom_note" id="copy_note" rows="2" placeholder="Contoh: Tekankan cocok untuk pemula, include memory card gratis..." class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs font-medium text-slate-800 focus:outline-none focus:border-sky-500"></textarea>
                    </div>

                    <button type="submit" id="btnSubmitGenerate" class="w-full py-3.5 px-4 rounded-2xl bg-gradient-to-r from-sky-500 to-blue-600 hover:from-sky-600 hover:to-blue-700 text-white font-black text-xs sm:text-sm shadow-md shadow-sky-500/20 flex items-center justify-center gap-2 transition transform active:scale-98">
                        <i class="fa-solid fa-wand-magic-sparkles text-sm"></i>
                        <span>Generate Konten Promosi</span>
                    </button>
                </form>
            </div>

            <!-- Output Copywriting AI -->
            <div class="lg:col-span-7 space-y-4">
                
                <!-- Loading State (Hidden by default) -->
                <div id="copyLoadingState" class="hidden bg-white rounded-3xl p-12 border border-slate-200 shadow-sm text-center">
                    <div class="inline-flex items-center justify-center w-14 h-14 rounded-full bg-sky-50 text-sky-600 mb-4 animate-spin">
                        <i class="fa-solid fa-circle-notch text-2xl"></i>
                    </div>
                    <h3 class="text-base font-black text-slate-800">Sedang Meracik Copywriting...</h3>
                    <p class="text-xs text-slate-400 mt-1 max-w-sm mx-auto">AI sedang menganalisis keunggulan produk dan menyusun kalimat penawaran yang memikat customer.</p>
                </div>

                <!-- Empty State -->
                <div id="copyEmptyState" class="bg-white rounded-3xl p-8 sm:p-12 border border-slate-200 shadow-sm text-center">
                    <div class="w-16 h-16 rounded-2xl bg-sky-50 text-sky-500 flex items-center justify-center mx-auto mb-4 text-2xl">
                        <i class="fa-solid fa-bullhorn"></i>
                    </div>
                    <h3 class="text-base font-black text-slate-800">Materi Promosi Siap Dibuat</h3>
                    <p class="text-xs text-slate-500 mt-1.5 max-w-md mx-auto">
                        Pilih barang di sebelah kiri dan klik tombol <b>Generate Konten Promosi</b> untuk melihat copywriting WhatsApp, caption Instagram, hook video TikTok, dan headline banner siap pakai.
                    </p>
                    <div class="mt-6 flex flex-wrap justify-center gap-2">
                        <span class="px-3 py-1 rounded-full text-[11px] font-bold bg-slate-100 text-slate-600"><i class="fa-brands fa-whatsapp text-emerald-500 mr-1"></i> WhatsApp Broadcast</span>
                        <span class="px-3 py-1 rounded-full text-[11px] font-bold bg-slate-100 text-slate-600"><i class="fa-brands fa-instagram text-rose-500 mr-1"></i> Instagram Caption</span>
                        <span class="px-3 py-1 rounded-full text-[11px] font-bold bg-slate-100 text-slate-600"><i class="fa-brands fa-tiktok text-slate-800 mr-1"></i> TikTok Hook</span>
                    </div>
                </div>

                <!-- Result Cards (Populated by JS) -->
                <div id="copyResultContainer" class="hidden space-y-4">
                    
                    <!-- Meta info source -->
                    <div class="flex items-center justify-between px-2 text-[11px] font-bold text-slate-500">
                        <span id="resultAiSource" class="inline-flex items-center gap-1.5 text-sky-600">
                            <i class="fa-solid fa-sparkles"></i> Dibuat oleh AI
                        </span>
                        <span class="text-slate-400">Siap disalin & dibagikan</span>
                    </div>

                    <!-- 1. WhatsApp Card -->
                    <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm space-y-3">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                            <div class="flex items-center gap-2 text-emerald-700 font-black text-xs sm:text-sm">
                                <i class="fa-brands fa-whatsapp text-base text-emerald-600"></i>
                                <span>Pesan Broadcast WhatsApp</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <button type="button" onclick="copyContent('resWaText', this)" class="px-2.5 py-1 rounded-lg text-[11px] font-bold text-slate-600 hover:text-slate-900 bg-slate-100 hover:bg-slate-200 transition">
                                    <i class="fa-solid fa-copy mr-1"></i> Salin
                                </button>
                                <a id="btnShareWa" href="#" target="_blank" class="px-2.5 py-1 rounded-lg text-[11px] font-bold text-white bg-emerald-600 hover:bg-emerald-700 transition flex items-center gap-1">
                                    <i class="fa-brands fa-whatsapp"></i> Bagikan
                                </a>
                            </div>
                        </div>
                        <pre id="resWaText" class="text-xs font-mono text-slate-800 bg-slate-50 p-3.5 rounded-xl whitespace-pre-wrap leading-relaxed select-all border border-slate-100"></pre>
                    </div>

                    <!-- 2. Instagram Card -->
                    <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm space-y-3">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                            <div class="flex items-center gap-2 text-rose-700 font-black text-xs sm:text-sm">
                                <i class="fa-brands fa-instagram text-base text-rose-600"></i>
                                <span>Caption & Hashtag Instagram</span>
                            </div>
                            <button type="button" onclick="copyContent('resIgText', this)" class="px-2.5 py-1 rounded-lg text-[11px] font-bold text-slate-600 hover:text-slate-900 bg-slate-100 hover:bg-slate-200 transition">
                                <i class="fa-solid fa-copy mr-1"></i> Salin Caption
                            </button>
                        </div>
                        <pre id="resIgText" class="text-xs font-mono text-slate-800 bg-slate-50 p-3.5 rounded-xl whitespace-pre-wrap leading-relaxed select-all border border-slate-100"></pre>
                    </div>

                    <!-- 3. TikTok & Reels Hook -->
                    <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm space-y-3">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                            <div class="flex items-center gap-2 text-slate-900 font-black text-xs sm:text-sm">
                                <i class="fa-brands fa-tiktok text-base text-slate-900"></i>
                                <span>Konsep Video TikTok / Reels (Hook & Script)</span>
                            </div>
                            <button type="button" onclick="copyContent('resTtText', this)" class="px-2.5 py-1 rounded-lg text-[11px] font-bold text-slate-600 hover:text-slate-900 bg-slate-100 hover:bg-slate-200 transition">
                                <i class="fa-solid fa-copy mr-1"></i> Salin Konsep
                            </button>
                        </div>
                        <pre id="resTtText" class="text-xs font-mono text-slate-800 bg-slate-50 p-3.5 rounded-xl whitespace-pre-wrap leading-relaxed select-all border border-slate-100"></pre>
                    </div>

                    <!-- 4. Banner Headline -->
                    <div id="resBannerBox" class="bg-gradient-to-r from-sky-50 to-indigo-50 rounded-2xl p-5 border border-sky-100 space-y-2">
                        <div class="flex items-center gap-2 text-sky-800 font-black text-xs">
                            <i class="fa-solid fa-image text-sky-600"></i>
                            <span>Slogan Banner Rekomendasi AI</span>
                        </div>
                        <h4 id="resBannerTitle" class="text-sm font-black text-slate-900"></h4>
                        <p id="resBannerSub" class="text-xs text-slate-600 font-medium"></p>
                    </div>

                </div>

            </div>

        </div>
    </div>

    <!-- ======================================================== -->
    <!-- TAB 2: RADAR AUTO-PROMOSI & AUDIT STOK -->
    <!-- ======================================================== -->
    <div id="tab-pane-radar" class="ai-tab-pane hidden space-y-6">
        
        <!-- Summary Metric Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
                <p class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">Total Barang</p>
                <p class="text-2xl font-black text-slate-800 mt-1">{{ $auditResult['summary']['total_produk'] ?? 0 }}</p>
                <p class="text-[11px] text-slate-500 mt-1">Katalog toko Anda</p>
            </div>
            <div class="bg-white p-5 rounded-2xl border border-rose-100 bg-rose-50/20 shadow-sm">
                <p class="text-[10px] font-extrabold text-rose-500 uppercase tracking-wider">Stok Tertidur / Sepi</p>
                <p class="text-2xl font-black text-rose-600 mt-1">{{ $auditResult['summary']['total_stagnant'] ?? 0 }}</p>
                <p class="text-[11px] text-rose-500 mt-1">Perlu dorongan promo</p>
            </div>
            <div class="bg-white p-5 rounded-2xl border border-emerald-100 bg-emerald-50/20 shadow-sm">
                <p class="text-[10px] font-extrabold text-emerald-600 uppercase tracking-wider">Produk Terlaris</p>
                <p class="text-2xl font-black text-emerald-600 mt-1">{{ $auditResult['summary']['total_popular'] ?? 0 }}</p>
                <p class="text-[11px] text-emerald-600 mt-1">Frekuensi sewa tinggi</p>
            </div>
            <div class="bg-white p-5 rounded-2xl border border-sky-100 bg-sky-50/20 shadow-sm">
                <p class="text-[10px] font-extrabold text-sky-600 uppercase tracking-wider">Health Score Toko</p>
                <p class="text-2xl font-black text-sky-600 mt-1">{{ $auditResult['summary']['health_score'] ?? 100 }}%</p>
                <p class="text-[11px] text-sky-600 mt-1">Indeks sirkulasi stok</p>
            </div>
        </div>

        <!-- Banner Penjelasan AI Radar -->
        <div class="p-4 rounded-2xl bg-amber-50 border border-amber-200/80 flex items-start gap-3">
            <div class="w-8 h-8 rounded-xl bg-amber-200/80 text-amber-800 flex items-center justify-center shrink-0 text-sm">
                <i class="fa-solid fa-lightbulb"></i>
            </div>
            <div class="text-xs text-amber-900 leading-relaxed">
                <p class="font-black text-amber-950 mb-0.5">Bagaimana AI Radar Bekerja?</p>
                AI memindai barang-barang yang stoknya masih menganggur atau jarang disewa dalam 25 hari terakhir. Untuk barang tersebut, AI merancang diskon persentase dan voucher otomatis agar barang segera disewa customer dan mendatangkan pemasukan.
            </div>
        </div>

        <!-- Rekomendasi Tindakan Cerdas -->
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-base font-black text-slate-800 flex items-center gap-2">
                        <i class="fa-solid fa-wand-magic-sparkles text-sky-500"></i>
                        Rekomendasi Auto-Promosi & Voucher AI
                    </h3>
                    <p class="text-xs text-slate-500">Klik 'Terapkan Voucher' untuk langsung mengaktifkan promo ke toko Anda.</p>
                </div>
            </div>

            @if(empty($auditResult['recommendations']))
                <div class="bg-white rounded-3xl p-10 border border-slate-200 text-center shadow-sm">
                    <div class="w-14 h-14 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center mx-auto mb-3 text-2xl">
                        <i class="fa-solid fa-circle-check"></i>
                    </div>
                    <h4 class="text-sm font-black text-slate-800">Semua Inventaris Bergerak Bagus!</h4>
                    <p class="text-xs text-slate-500 max-w-sm mx-auto mt-1">Tidak ada stok yang tertidur lama. Anda tetap bisa menggunakan Tab Copywriter untuk promosi berkala.</p>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($auditResult['recommendations'] as $rec)
                        <div class="bg-white rounded-2xl p-5 border border-slate-200 hover:border-sky-300 shadow-xs hover:shadow-md transition flex flex-col justify-between space-y-4">
                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider bg-rose-50 text-rose-600 border border-rose-100">
                                        {{ $rec['kategori'] }}
                                    </span>
                                    <span class="text-[11px] font-bold text-slate-400">
                                        Stok: {{ $rec['stok'] }} unit
                                    </span>
                                </div>
                                <h4 class="font-black text-slate-800 text-sm mb-1 leading-snug">
                                    {{ $rec['nama_barang'] }}
                                </h4>
                                <p class="text-xs font-bold text-sky-600 mb-2">
                                    Rp {{ number_format($rec['harga_sewa'], 0, ',', '.') }} / hari
                                </p>
                                <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-100 text-[11px] text-slate-600 space-y-1">
                                    <p class="text-rose-600 font-bold"><i class="fa-solid fa-clock mr-1"></i> {{ $rec['alasan_urgensi'] }}</p>
                                    <p class="text-slate-700 font-medium">💡 Saran: <b>Diskon {{ $rec['nilai_diskon'] }}%</b> (Potongan ~Rp {{ number_format($rec['potongan_nominal'], 0, ',', '.') }})</p>
                                </div>
                            </div>

                            <div class="space-y-2 pt-2 border-t border-slate-100">
                                <div class="flex items-center justify-between text-[11px]">
                                    <span class="text-slate-400">Kode Rekomendasi:</span>
                                    <span class="font-mono font-black text-slate-800 px-2 py-0.5 bg-slate-100 rounded">{{ $rec['kode_voucher'] }}</span>
                                </div>

                                <div class="grid grid-cols-2 gap-2">
                                    <!-- 1-Click Terapkan Voucher -->
                                    <form onsubmit="handleApplyVoucher(event, this)">
                                        @csrf
                                        <input type="hidden" name="kode_voucher" value="{{ $rec['kode_voucher'] }}">
                                        <input type="hidden" name="tipe_diskon" value="{{ $rec['tipe_diskon'] }}">
                                        <input type="hidden" name="nilai_diskon" value="{{ $rec['nilai_diskon'] }}">
                                        <input type="hidden" name="minimal_belanja" value="{{ $rec['minimal_belanja'] }}">
                                        <input type="hidden" name="maksimal_diskon" value="{{ $rec['maksimal_diskon'] }}">
                                        <input type="hidden" name="durasi_hari" value="{{ $rec['durasi_hari'] }}">
                                        <button type="submit" class="w-full py-2 px-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-[11px] shadow-sm transition flex items-center justify-center gap-1">
                                            <i class="fa-solid fa-bolt"></i> Aktifkan Voucher
                                        </button>
                                    </form>

                                    <!-- Quick Copywrite -->
                                    <button type="button" onclick="quickFillCopywriter('{{ $rec['barang_id'] }}', 'Diskon {{ $rec['nilai_diskon'] }}% kode {{ $rec['kode_voucher'] }}')" class="w-full py-2 px-2.5 rounded-xl bg-sky-50 hover:bg-sky-100 text-sky-700 font-extrabold text-[11px] border border-sky-200 transition flex items-center justify-center gap-1">
                                        <i class="fa-solid fa-pen"></i> Buat Iklan
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

    </div>

    <!-- ======================================================== -->
    <!-- TAB 3: VOUCHER TOKO & TIPS AI -->
    <!-- ======================================================== -->
    <div id="tab-pane-vouchers" class="ai-tab-pane hidden space-y-6">
        
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Voucher Aktif -->
            <div class="lg:col-span-7 bg-white rounded-3xl p-6 border border-slate-200 shadow-sm space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                    <div>
                        <h3 class="text-base font-black text-slate-800 flex items-center gap-2">
                            <i class="fa-solid fa-ticket text-sky-500"></i>
                            Voucher Diskon Aktif Toko Anda
                        </h3>
                        <p class="text-xs text-slate-500">Voucher ini dapat langsung diklaim customer saat menyewa barang.</p>
                    </div>
                    <a href="{{ route('vendor.voucher.index') }}" class="text-xs font-bold text-sky-600 hover:underline">
                        Kelola Semua &rarr;
                    </a>
                </div>

                @if($activeVouchers->isEmpty())
                    <div class="p-8 text-center bg-slate-50 rounded-2xl border border-dashed border-slate-200">
                        <i class="fa-solid fa-ticket-simple text-3xl text-slate-300 mb-2"></i>
                        <p class="text-xs font-bold text-slate-600">Belum ada voucher diskon aktif di toko Anda.</p>
                        <p class="text-[11px] text-slate-400 mt-1">Gunakan rekomendasi dari Tab Radar untuk membuat voucher dengan 1 klik!</p>
                    </div>
                @else
                    <div class="space-y-3">
                        @foreach($activeVouchers as $v)
                            <div class="p-4 rounded-2xl border border-slate-100 bg-slate-50/60 flex items-center justify-between gap-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-sky-500/10 text-sky-600 font-mono font-black text-xs flex items-center justify-center shrink-0">
                                        <i class="fa-solid fa-percent"></i>
                                    </div>
                                    <div>
                                        <p class="font-mono font-black text-slate-800 text-xs sm:text-sm">{{ $v->kode_voucher }}</p>
                                        <p class="text-[11px] text-slate-500 font-medium">
                                            @if($v->tipe_diskon === 'persen')
                                                Diskon {{ $v->nilai_diskon }}% @if($v->maksimal_diskon)(Maks. Rp {{ number_format($v->maksimal_diskon, 0, ',', '.') }})@endif
                                            @else
                                                Potongan Rp {{ number_format($v->nilai_diskon, 0, ',', '.') }}
                                            @endif
                                            • Min. Sewa Rp {{ number_format($v->minimal_belanja, 0, ',', '.') }}
                                        </p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                        Aktif s/d {{ \Carbon\Carbon::parse($v->tanggal_selesai)->format('d M Y') }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Panduan AI Marketing -->
            <div class="lg:col-span-5 bg-gradient-to-br from-sky-900 to-slate-900 text-white rounded-3xl p-6 shadow-sm space-y-4">
                <div class="flex items-center gap-2 text-sky-400 font-black text-sm">
                    <i class="fa-solid fa-graduation-cap"></i>
                    <span>Tips Sukses Promosi Rental dari AI</span>
                </div>

                <div class="space-y-3 text-xs text-slate-300">
                    <div class="p-3 rounded-xl bg-white/5 border border-white/10 space-y-1">
                        <p class="font-black text-white flex items-center gap-1.5">
                            <i class="fa-solid fa-calendar-day text-sky-400"></i> Waktu Terbaik Promosi
                        </p>
                        <p class="text-[11px]">Kirimkan broadcast WhatsApp atau posting medsos pada hari Kamis siang & Jumat sore untuk menjaring customer yang merencanakan liburan akhir pekan.</p>
                    </div>

                    <div class="p-3 rounded-xl bg-white/5 border border-white/10 space-y-1">
                        <p class="font-black text-white flex items-center gap-1.5">
                            <i class="fa-solid fa-tags text-sky-400"></i> Psikologi Kode Diskon
                        </p>
                        <p class="text-[11px]">Gunakan kode voucher yang mudah diingat seperti <b>SEWAHEMAT</b> atau <b>WEEKENDSERU</b>. Kode yang simpel meningkatkan konversi checkout hingga 34%.</p>
                    </div>

                    <div class="p-3 rounded-xl bg-white/5 border border-white/10 space-y-1">
                        <p class="font-black text-white flex items-center gap-1.5">
                            <i class="fa-solid fa-camera text-sky-400"></i> Visual Berkualitas
                        </p>
                        <p class="text-[11px]">Pastikan foto cover barang Anda terang dan menunjukkan kondisi asli yang siap pakai. Customer rental sangat peduli pada kebersihan & kelengkapan unit.</p>
                    </div>
                </div>
            </div>
        </div>

    </div>

</div>

<!-- JAVASCRIPT LOGIC -->
<script>
    // Tab Switcher
    function switchAiTab(tabName) {
        document.querySelectorAll('.ai-tab-pane').forEach(el => el.classList.add('hidden'));
        document.querySelectorAll('.ai-tab-btn').forEach(el => {
            el.classList.remove('border-sky-600', 'text-sky-600', 'bg-sky-50');
            el.classList.add('border-transparent', 'text-slate-500');
        });

        const activePane = document.getElementById('tab-pane-' + tabName);
        const activeBtn = document.getElementById('tab-btn-' + tabName);
        if (activePane) activePane.classList.remove('hidden');
        if (activeBtn) {
            activeBtn.classList.remove('border-transparent', 'text-slate-500');
            activeBtn.classList.add('border-sky-600', 'text-sky-600', 'bg-sky-50');
        }
    }

    // Handle Barang Select
    function onSelectBarang(selectEl) {
        const manualBox = document.getElementById('manualProductBox');
        if (selectEl.value) {
            manualBox.classList.add('hidden');
        } else {
            manualBox.classList.remove('hidden');
        }
    }

    // Quick fill from Radar recommendations
    function quickFillCopywriter(barangId, diskonText) {
        switchAiTab('copywriter');
        const select = document.getElementById('copy_barang_id');
        select.value = barangId;
        onSelectBarang(select);
        document.getElementById('copy_diskon').value = diskonText;
        document.getElementById('copy_tone').value = 'fomo';
        document.getElementById('btnSubmitGenerate').scrollIntoView({ behavior: 'smooth' });
    }

    // Copy Content to Clipboard
    function copyContent(elementId, btnEl) {
        const text = document.getElementById(elementId).innerText;
        navigator.clipboard.writeText(text).then(() => {
            const originalHtml = btnEl.innerHTML;
            btnEl.innerHTML = '<i class="fa-solid fa-check text-emerald-600 mr-1"></i> Tersalin!';
            btnEl.classList.add('bg-emerald-50', 'text-emerald-700');
            setTimeout(() => {
                btnEl.innerHTML = originalHtml;
                btnEl.classList.remove('bg-emerald-50', 'text-emerald-700');
            }, 2000);
        });
    }

    // Generate Copywriting via AJAX
    function handleGenerateCopy(event) {
        event.preventDefault();
        const form = document.getElementById('copywriterForm');
        const formData = new FormData(form);

        const emptyState = document.getElementById('copyEmptyState');
        const loadingState = document.getElementById('copyLoadingState');
        const resultContainer = document.getElementById('copyResultContainer');

        emptyState.classList.add('hidden');
        resultContainer.classList.add('hidden');
        loadingState.classList.remove('hidden');

        fetch("{{ route('vendor.ai-marketing.generate-copy') }}", {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(json => {
            loadingState.classList.add('hidden');
            if (json.success && json.data) {
                const data = json.data;
                document.getElementById('resWaText').innerText = data.whatsapp || '';
                document.getElementById('resIgText').innerText = data.instagram || '';
                document.getElementById('resTtText').innerText = data.tiktok || '';
                
                // WhatsApp direct share link
                const waEncoded = encodeURIComponent(data.whatsapp || '');
                document.getElementById('btnShareWa').href = 'https://api.whatsapp.com/send?text=' + waEncoded;

                // Banner
                if (data.banner) {
                    document.getElementById('resBannerBox').classList.remove('hidden');
                    document.getElementById('resBannerTitle').innerText = data.banner.headline || '';
                    document.getElementById('resBannerSub').innerText = data.banner.subheadline || '';
                } else {
                    document.getElementById('resBannerBox').classList.add('hidden');
                }

                if (data.ai_source) {
                    document.getElementById('resultAiSource').innerHTML = '<i class="fa-solid fa-sparkles text-sky-500"></i> ' + data.ai_source;
                }

                resultContainer.classList.remove('hidden');
            } else {
                alert('Gagal memproses AI: ' + (json.message || 'Terjadi kesalahan sistem.'));
                emptyState.classList.remove('hidden');
            }
        })
        .catch(err => {
            loadingState.classList.add('hidden');
            emptyState.classList.remove('hidden');
            alert('Terjadi kendala jaringan saat menghubungi server AI.');
        });
    }

    // 1-Click Apply Voucher
    function handleApplyVoucher(event, formEl) {
        event.preventDefault();
        const btn = formEl.querySelector('button[type="submit"]');
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Mengaktifkan...';

        const formData = new FormData(formEl);

        fetch("{{ route('vendor.ai-marketing.auto-apply-voucher') }}", {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(json => {
            if (json.success) {
                btn.innerHTML = '<i class="fa-solid fa-check"></i> Aktif!';
                btn.classList.remove('bg-emerald-600', 'hover:bg-emerald-700');
                btn.classList.add('bg-slate-400', 'cursor-not-allowed');
                alert(json.message);
                // Refresh list if needed or stay active
            } else {
                btn.disabled = false;
                btn.innerHTML = originalText;
                alert('Gagal membuat voucher: ' + (json.message || 'Kode mungkin sudah ada.'));
            }
        })
        .catch(err => {
            btn.disabled = false;
            btn.innerHTML = originalText;
            alert('Terjadi kesalahan jaringan.');
        });
    }
</script>
@endsection
