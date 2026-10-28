@extends('layouts.vendor')

@section('title', 'Upload Produk Baru — Vendor Rentify')

@section('content')
<div class="px-4 sm:px-6 lg:px-8 py-6 max-w-5xl mx-auto space-y-6">

    <!-- Header & Navigation -->
    <div class="flex items-center gap-3">
        <a href="{{ route('vendor.barang.index') }}" class="w-10 h-10 rounded-2xl bg-white border border-slate-200 text-slate-600 hover:text-sky-600 hover:border-sky-300 flex items-center justify-center shadow-xs transition active:scale-95">
            <i class="fa-solid fa-arrow-left text-sm"></i>
        </a>
        <div class="flex-1 min-w-0">
            <h1 class="text-xl sm:text-2xl font-black text-slate-800 tracking-tight">Upload Produk Baru</h1>
            <p class="text-xs sm:text-sm text-slate-500 font-medium">Bisa langsung foto produk lewat kamera HP atau pilih dari galeri.</p>
        </div>
    </div>

    <!-- Error Alerts -->
    @if($errors->any())
        <div class="p-4 sm:p-5 bg-rose-50 border border-rose-200 text-rose-700 rounded-3xl space-y-2 shadow-xs">
            <div class="flex items-center gap-2 font-bold text-sm">
                <i class="fa-solid fa-triangle-exclamation text-rose-500"></i>
                <span>Mohon periksa kembali formulir:</span>
            </div>
            <ul class="list-disc list-inside text-xs space-y-1 font-medium text-rose-600">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('vendor.barang.store') }}" method="POST" enctype="multipart/form-data" id="formUploadBarang" class="space-y-6">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- ============================================== -->
            <!-- KOLOM KIRI (2 SPAN): INFO DASAR & PENGIRIMAN -->
            <!-- ============================================== -->
            <div class="lg:col-span-2 space-y-6">
                
                <!-- 1. FOTO PRODUK (MOBILE FIRST: DI ATAS AGAR MUDAH DIFOTO) -->
                <div class="bg-white p-5 sm:p-6 rounded-3xl border border-slate-200 shadow-sm">
                    <div class="flex items-center justify-between mb-3 border-b border-slate-100 pb-3">
                        <div>
                            <h2 class="text-base font-black text-slate-800 flex items-center gap-2">
                                <i class="fa-solid fa-photo-film text-sky-500"></i> Media Barang (Foto / Video) <span class="text-rose-500">*</span>
                            </h2>
                            <p class="text-xs text-slate-400 mt-0.5">Upload minimal 1 foto/video (berkas pertama jadi Cover). Foto dikompresi otomatis ke WebP/AVIF HD.</p>
                        </div>
                        <span id="photoCounter" class="text-xs font-black px-2.5 py-1 bg-sky-50 text-sky-700 rounded-lg">
                            0 Media dipilih
                        </span>
                    </div>

                    <!-- Hidden Input -->
                    <input type="file" name="fotos[]" id="fotos" multiple accept="image/*,video/*,.heic,.heif,.avif,.webp,.mp4,.mov,.mkv,.webm" class="hidden">

                    <!-- Mobile-First Big Touch Area -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-4">
                        <!-- Tombol Buka Kamera / Galeri -->
                        <label for="fotos" class="cursor-pointer p-4 rounded-2xl border-2 border-dashed border-sky-400/80 bg-sky-50/60 hover:bg-sky-100/70 transition flex items-center justify-center gap-3 text-sky-700 active:scale-98">
                            <div class="w-11 h-11 rounded-xl bg-sky-500 text-white flex items-center justify-center text-lg shadow-sm">
                                <i class="fa-solid fa-camera"></i>
                            </div>
                            <div class="text-left">
                                <span class="block text-xs font-black">Buka Kamera / Galeri</span>
                                <span class="block text-[11px] text-sky-600/80 font-medium">Bisa pilih foto & video</span>
                            </div>
                        </label>

                        <!-- Tips Foto HP -->
                        <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200/70 flex items-center gap-3 text-slate-600">
                            <div class="w-9 h-9 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center shrink-0">
                                <i class="fa-solid fa-lightbulb text-sm"></i>
                            </div>
                            <p class="text-[11px] leading-snug">
                                Gunakan pencahayaan terang dan foto dari berbagai sudut agar cepat disewa!
                            </p>
                        </div>
                    </div>

                    <!-- Live Previews Grid -->
                    <div id="preview-container" class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                        <!-- Preview Cards will be appended here -->
                    </div>
                </div>

                <!-- 2. INFORMASI DASAR -->
                <div class="bg-white p-5 sm:p-6 rounded-3xl border border-slate-200 shadow-sm space-y-4">
                    <h2 class="text-base font-black text-slate-800 border-b border-slate-100 pb-3 flex items-center gap-2">
                        <i class="fa-solid fa-circle-info text-sky-500"></i> Informasi Barang
                    </h2>

                    <!-- Nama Barang -->
                    <div>
                        <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-1.5">
                            Nama Barang <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="nama" value="{{ (string) old('nama') }}" required class="w-full px-4 py-3 rounded-2xl border border-slate-200 text-slate-800 text-sm font-semibold focus:outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-100 transition">
                    </div>

                    <!-- Kategori & Kondisi -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-1.5">
                                Kategori <span class="text-rose-500">*</span>
                            </label>
                            <select name="kategori_id" id="kategori_id" required onchange="handleKategoriChange()" class="w-full px-4 py-3 rounded-2xl border border-slate-200 text-slate-800 text-sm font-semibold focus:outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-100 transition bg-white">
                                <option value="">-- Pilih Kategori --</option>
                                @foreach($kategoris as $k)
                                    <option value="{{ $k->id }}" data-nama="{{ strtolower($k->nama) }}" {{ old('kategori_id') == $k->id ? 'selected' : '' }}>
                                        {{ $k->nama }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-1.5">
                                Kondisi Fisik <span class="text-rose-500">*</span>
                            </label>
                            <select name="kondisi" required class="w-full px-4 py-3 rounded-2xl border border-slate-200 text-slate-800 text-sm font-semibold focus:outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-100 transition bg-white">
                                <option value="Sangat Baik" {{ old('kondisi') == 'Sangat Baik' ? 'selected' : '' }}>Sangat Baik (Mulus / Seperti Baru)</option>
                                <option value="Baik" {{ old('kondisi', 'Baik') == 'Baik' ? 'selected' : '' }}>Baik (Normal & Berfungsi Penuh)</option>
                                <option value="Cukup" {{ old('kondisi') == 'Cukup' ? 'selected' : '' }}>Cukup (Ada Minus Lecet Wajar)</option>
                            </select>
                        </div>
                    </div>

                    <!-- Deskripsi Barang -->
                    <div>
                        <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-1.5">
                            Deskripsi & Kelengkapan Unit <span class="text-rose-500">*</span>
                        </label>
                        <textarea name="deskripsi" rows="4" required placeholder="Tuliskan spesifikasi, kelengkapan (tas, charger, baterai tambahan), aturan sewa, atau peringatan penggunaan..." class="w-full px-4 py-3 rounded-2xl border border-slate-200 text-slate-800 text-sm font-medium focus:outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-100 transition leading-relaxed">{{ (string) old('deskripsi') }}</textarea>
                    </div>
                </div>

                <!-- ============================================== -->
                <!-- 2.5 SPESIFIKASI KHUSUS KATEGORI (DINAMIS) -->
                <!-- ============================================== -->

                <!-- PANEL 1: KOS / KAMAR (ALA MAMIKOS / OYO / AIRBNB) -->
                <div id="panel-kos-kamar" class="hidden bg-white p-5 sm:p-6 rounded-3xl border-2 border-sky-300 shadow-sm space-y-5 transition-all">
                    <div class="border-b border-sky-100 pb-3 flex items-center justify-between gap-3">
                        <div class="flex items-center gap-2.5">
                            <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-sky-400 to-blue-600 text-white flex items-center justify-center text-lg shadow-sm shrink-0">
                                <i class="fa-solid fa-house-chimney-window"></i>
                            </div>
                            <div>
                                <h3 class="text-sm font-black text-slate-800">Spesifikasi Kos & Kamar</h3>
                            </div>
                        </div>

                        <!-- Kanan Atas Bingkai: Badge & Tombol Panduan -->
                        <div class="flex items-center gap-2 shrink-0">
                            <span class="hidden sm:inline-block px-2.5 py-1 bg-sky-50 text-sky-600 rounded-full text-[10px] font-black border border-sky-200">
                                Fitur Kos Profesional
                            </span>

                            <!-- Tombol Panduan di Atas Kanan Bingkai -->
                            <div class="relative">
                                <button type="button" data-btn-panduan="true" onclick="togglePanduanPanel('kos', event)" class="w-8 h-8 rounded-full bg-sky-50 hover:bg-sky-100 text-sky-600 border border-sky-200 flex items-center justify-center shadow-xs transition hover:scale-105 cursor-pointer focus:outline-none" title="Lihat Panduan Lengkap Kategori Kos & Kamar">
                                    <i class="fa-solid fa-circle-exclamation text-sky-500 text-sm"></i>
                                </button>

                                <!-- Popover Card Panduan Kos -->
                                <div id="popover-panduan-kos" class="popover-panduan-spesifikasi hidden absolute right-0 top-full mt-2 w-84 sm:w-[440px] max-w-[calc(100vw-36px)] bg-white rounded-3xl shadow-2xl border-2 border-sky-200 p-4 sm:p-5 z-50 text-slate-700 text-left">
                                    <div class="flex items-center justify-between border-b border-sky-100 pb-2.5 mb-3">
                                        <div class="flex items-center gap-2 text-xs font-black text-slate-800">
                                            <i class="fa-solid fa-house-chimney-window text-sky-500 text-sm"></i>
                                            <span>Panduan Lengkap Kos & Kamar</span>
                                        </div>
                                        <button type="button" onclick="closeAllPanduan()" class="w-6 h-6 rounded-full hover:bg-slate-100 text-slate-400 hover:text-slate-600 flex items-center justify-center text-xs cursor-pointer">
                                            <i class="fa-solid fa-xmark"></i>
                                        </button>
                                    </div>
                                    <div class="max-h-[440px] overflow-y-auto space-y-3.5 text-xs pr-1.5 scrollbar-thin">
                                        <!-- Cakupan Properti -->
                                        <div class="p-3 rounded-2xl bg-sky-50/70 border border-sky-100 space-y-1.5">
                                            <p class="font-black text-sky-900 flex items-center gap-1.5">
                                                <i class="fa-solid fa-circle-check text-sky-600"></i> Cakupan Unit & Properti:
                                            </p>
                                            <p class="text-[11px] text-slate-600 leading-relaxed">
                                                Kamar kos bulanan / tahunan (putra, putri, campur, pasutri), paviliun mandiri, kontrakan mini, serta kamar singgah transit per malam (homestay / guest room).
                                            </p>
                                        </div>

                                        <!-- Panduan Kolom per Kolom -->
                                        <div class="space-y-2">
                                            <p class="font-black text-slate-800 flex items-center gap-1.5">
                                                <i class="fa-solid fa-list-check text-sky-500"></i> Panduan Pengisian Setiap Kolom:
                                            </p>
                                            <ul class="space-y-2 text-[11px] text-slate-600 list-disc pl-4 leading-relaxed">
                                                <li><strong>Skema Periode Sewa:</strong> Pilih <em>Harian & Bulanan</em> (paling fleksibel untuk tamu transit dan anak kos), <em>Khusus Bulanan</em> (hanya menerima sewa menetap), atau <em>Khusus Harian</em> (penginapan per malam ala homestay).</li>
                                                <li><strong>Tarif Bulanan (Rp):</strong> Masukkan angka nominal sewa 1 bulan penuh (contoh: <code>850000</code>). Nominal ini akan diformat otomatis dan tampil sebagai harga utama pencarian kos.</li>
                                                <li><strong>Jenis Kos & Pasutri:</strong> Tentukan penghuni (Putra, Putri, Campur, atau Pasutri). Jika menerima pasutri, disarankan centang aturan <em>Wajib Surat Nikah</em> demi keamanan legalitas.</li>
                                                <li><strong>Kamar Mandi:</strong> Jelaskan <em>Kamar Mandi Dalam</em> (privasi penuh) atau <em>Kamar Mandi Luar</em> (sharing bersama kamar lain).</li>
                                                <li><strong>Ukuran Kamar:</strong> Cantumkan ukuran nyata (contoh: <code>3 x 4 Meter</code>) agar penyewa bisa memperkirakan kapasitas kasur dan lemari pakaian.</li>
                                                <li><strong>Fasilitas:</strong> Centang fasilitas yang benar-benar siap pakai (AC, Springbed, Lemari, WiFi, Dapur Bersama, Parkir Aman, CCTV 24 Jam).</li>
                                            </ul>
                                        </div>

                                        <!-- Kejelasan Listrik & Biaya -->
                                        <div class="p-3 rounded-2xl bg-slate-50 border border-slate-200 text-[11px] text-slate-600 space-y-1.5">
                                            <p class="font-black text-slate-800 flex items-center gap-1.5">
                                                <i class="fa-solid fa-bolt text-amber-500"></i> Ketentuan Listrik & Biaya Tambahan:
                                            </p>
                                            <p class="leading-relaxed">
                                                Jelaskan secara transparan di deskripsi: Apakah tarif sudah <strong>Termasuk Listrik (Free PLN)</strong> atau menggunakan <strong>Token Mandiri</strong> per kamar, serta apakah ada biaya tambahan untuk alat elektronik berdaya besar (dispenser/kulkas mini).
                                            </p>
                                        </div>

                                        <!-- SOP & Keamanan Vendor -->
                                        <div class="p-3 rounded-2xl bg-sky-50/50 border border-sky-100 text-[11px] text-slate-600 space-y-1.5">
                                            <p class="font-black text-sky-900 flex items-center gap-1.5">
                                                <i class="fa-solid fa-shield-halved text-sky-600"></i> SOP Penerimaan & Tips Vendor:
                                            </p>
                                            <ul class="space-y-1 list-disc pl-4 leading-relaxed">
                                                <li>Unggah foto kamar dengan pencahayaan terang, sudut luas, serta foto kamar mandi dan dapur bersama.</li>
                                                <li>Wajib minta fotokopi/foto e-KTP asli calon penyewa dan kontak darurat keluarga saat serah terima kunci.</li>
                                                <li>Terapkan uang deposit jaminan kunci/kerusakan (misal Rp 100.000 - Rp 300.000) yang dikembalikan utuh saat check-out.</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Skema Sewa -->
                    <div>
                        <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-2">
                            Skema Periode Sewa Kos <span class="text-rose-500">*</span>
                        </label>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5">
                            <label class="cursor-pointer">
                                <input type="radio" name="spesifikasi[tipe_sewa]" value="keduanya" {{ old('spesifikasi.tipe_sewa', 'keduanya') == 'keduanya' ? 'checked' : '' }} onchange="toggleKosPricing()" class="peer sr-only">
                                <div class="p-3 rounded-2xl border-2 border-slate-200 peer-checked:border-sky-500 peer-checked:bg-sky-50/50 text-center transition">
                                    <div class="text-xs font-black text-slate-800"><i class="fa-solid fa-calendar-check text-sky-500 mr-1"></i> Harian & Bulanan</div>
                                    <div class="text-[10px] text-slate-400 mt-0.5">Bisa harian dan bulanan</div>
                                </div>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" name="spesifikasi[tipe_sewa]" value="bulanan" {{ old('spesifikasi.tipe_sewa') == 'bulanan' ? 'checked' : '' }} onchange="toggleKosPricing()" class="peer sr-only">
                                <div class="p-3 rounded-2xl border-2 border-slate-200 peer-checked:border-sky-500 peer-checked:bg-sky-50/50 text-center transition">
                                    <div class="text-xs font-black text-slate-800"><i class="fa-solid fa-calendar-days text-sky-500 mr-1"></i> Khusus Bulanan</div>
                                    <div class="text-[10px] text-slate-400 mt-0.5">Sistem kos per bulan</div>
                                </div>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" name="spesifikasi[tipe_sewa]" value="harian" {{ old('spesifikasi.tipe_sewa') == 'harian' ? 'checked' : '' }} onchange="toggleKosPricing()" class="peer sr-only">
                                <div class="p-3 rounded-2xl border-2 border-slate-200 peer-checked:border-sky-500 peer-checked:bg-sky-50/50 text-center transition">
                                    <div class="text-xs font-black text-slate-800"><i class="fa-solid fa-clock text-sky-500 mr-1"></i> Khusus Per Malam</div>
                                    <div class="text-[10px] text-slate-400 mt-0.5">Penginapan harian</div>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Input Tarif Bulanan Kos -->
                    <div id="wrapper_harga_bulanan" class="p-4 rounded-2xl bg-sky-50/70 border border-sky-200 space-y-1.5">
                        <label class="block text-xs font-black text-slate-800 uppercase tracking-wider">
                            Tarif Sewa Per Bulan (Rp) <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-xs font-bold text-slate-400">Rp</span>
                            <input type="number" name="spesifikasi[harga_bulanan]" id="input_harga_bulanan" value="{{ (string) old('spesifikasi.harga_bulanan') }}" min="0" class="w-full pl-11 pr-4 py-3 rounded-2xl border border-slate-200 bg-white text-slate-800 text-sm font-black focus:outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-100 transition">
                        </div>
                        <p class="text-[11px] text-sky-700 font-medium">
                            <i class="fa-solid fa-circle-info mr-1"></i> Tarif per bulan akan tampil mencolok di pencarian & halaman detail untuk calon penyewa kos.
                        </p>
                    </div>

                    <!-- Tipe Kos, Kamar Mandi, Ukuran Kamar -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div>
                            <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-1.5">Jenis Kos</label>
                            <select name="spesifikasi[tipe_kos]" class="w-full px-3.5 py-2.5 rounded-2xl border border-slate-200 text-slate-800 text-xs font-semibold focus:outline-none focus:border-sky-500 bg-white">
                                <option value="Campur" {{ old('spesifikasi.tipe_kos') == 'Campur' ? 'selected' : '' }}>Kos Campur</option>
                                <option value="Putri" {{ old('spesifikasi.tipe_kos') == 'Putri' ? 'selected' : '' }}>Khusus Putri</option>
                                <option value="Putra" {{ old('spesifikasi.tipe_kos') == 'Putra' ? 'selected' : '' }}>Khusus Putra</option>
                                <option value="Pasutri" {{ old('spesifikasi.tipe_kos') == 'Pasutri' ? 'selected' : '' }}>Boleh Pasutri</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-1.5">Kamar Mandi</label>
                            <select name="spesifikasi[tipe_kamar_mandi]" class="w-full px-3.5 py-2.5 rounded-2xl border border-slate-200 text-slate-800 text-xs font-semibold focus:outline-none focus:border-sky-500 bg-white">
                                <option value="Dalam" {{ old('spesifikasi.tipe_kamar_mandi') == 'Dalam' ? 'selected' : '' }}>Kamar Mandi Dalam</option>
                                <option value="Luar" {{ old('spesifikasi.tipe_kamar_mandi') == 'Luar' ? 'selected' : '' }}>Kamar Mandi Luar</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-1.5">Ukuran Kamar</label>
                            <input type="text" name="spesifikasi[ukuran_kamar]" value="{{ (string) old('spesifikasi.ukuran_kamar') }}" class="w-full px-3.5 py-2.5 rounded-2xl border border-slate-200 text-slate-800 text-xs font-semibold focus:outline-none focus:border-sky-500">
                        </div>
                    </div>

                    <!-- Fasilitas Kamar & Bersama -->
                    <div>
                        <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-2">
                            Fasilitas Kamar & Kos (Pilih yang Tersedia)
                        </label>
                        @php
                            $fasilitasKosList = [
                                ['AC', 'fa-snowflake'],
                                ['WiFi Cepat', 'fa-wifi'],
                                ['Kamar Mandi Dalam', 'fa-bath'],
                                ['Kasur & Springbed', 'fa-bed'],
                                ['Lemari Pakaian', 'fa-door-closed'],
                                ['Meja & Kursi Belajar', 'fa-chair'],
                                ['Water Heater', 'fa-temperature-arrow-up'],
                                ['Dapur Bersama', 'fa-utensils'],
                                ['Parkir Motor Aman', 'fa-motorcycle'],
                                ['Parkir Mobil', 'fa-car'],
                                ['CCTV 24 Jam', 'fa-video'],
                                ['Listrik Termasuk', 'fa-bolt'],
                            ];
                            $oldFasKos = old('spesifikasi.fasilitas', []);
                        @endphp
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                            @foreach($fasilitasKosList as [$fas, $icon])
                            <label class="cursor-pointer relative flex items-center p-2.5 rounded-xl border border-slate-200 hover:border-sky-300 has-[:checked]:border-sky-500 has-[:checked]:bg-sky-50/70 transition">
                                <input type="checkbox" name="spesifikasi[fasilitas][]" value="{{ $fas }}" {{ in_array($fas, (array)$oldFasKos) ? 'checked' : '' }} class="mr-2 rounded text-sky-500 focus:ring-sky-400">
                                <i class="fa-solid {{ $icon }} text-sky-500 mr-2 text-xs"></i>
                                <span class="text-xs font-semibold text-slate-700">{{ $fas }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>

                    <!-- Aturan Kos -->
                    <div>
                        <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-2">
                            Aturan & Ketentuan Kos
                        </label>
                        @php
                            $aturanKosList = [
                                'Akses 24 Jam (Bebas Jam Malam)',
                                'Boleh Bawa Tamu Menginap',
                                'Khusus Pasutri Wajib Surat Nikah',
                                'Dilarang Merokok di Dalam Kamar',
                                'Dilarang Membawa Hewan Peliharaan',
                                'Maksimal 2 Orang Per Kamar',
                            ];
                            $oldAturan = old('spesifikasi.aturan', []);
                        @endphp
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            @foreach($aturanKosList as $atr)
                            <label class="cursor-pointer relative flex items-center p-2 rounded-xl border border-slate-200 hover:border-sky-300 has-[:checked]:border-sky-500 has-[:checked]:bg-sky-50/70 transition">
                                <input type="checkbox" name="spesifikasi[aturan][]" value="{{ $atr }}" {{ in_array($atr, (array)$oldAturan) ? 'checked' : '' }} class="mr-2 rounded text-sky-500 focus:ring-sky-400">
                                <span class="text-[11px] font-medium text-slate-700">{{ $atr }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- PANEL 2: KENDARAAN (ALA TRAVELOKA / TURO) -->
                <div id="panel-kendaraan" class="hidden bg-white p-5 sm:p-6 rounded-3xl border-2 border-cyan-300 shadow-sm space-y-5 transition-all">
                    <div class="border-b border-cyan-100 pb-3 flex items-center justify-between gap-3">
                        <div class="flex items-center gap-2.5">
                            <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-cyan-400 to-sky-600 text-white flex items-center justify-center text-lg shadow-sm shrink-0">
                                <i class="fa-solid fa-car-side"></i>
                            </div>
                            <div>
                                <h3 class="text-sm font-black text-slate-800">Spesifikasi Rental Kendaraan</h3>
                            </div>
                        </div>

                        <!-- Kanan Atas Bingkai: Badge & Tombol Panduan -->
                        <div class="flex items-center gap-2 shrink-0">
                            <span class="hidden sm:inline-block px-2.5 py-1 bg-cyan-50 text-cyan-600 rounded-full text-[10px] font-black border border-cyan-200">
                                Rental Kendaraan
                            </span>

                            <!-- Tombol Panduan di Atas Kanan Bingkai -->
                            <div class="relative">
                                <button type="button" data-btn-panduan="true" onclick="togglePanduanPanel('kendaraan', event)" class="w-8 h-8 rounded-full bg-sky-50 hover:bg-sky-100 text-sky-600 border border-sky-200 flex items-center justify-center shadow-xs transition hover:scale-105 cursor-pointer focus:outline-none" title="Lihat Panduan Lengkap Rental Kendaraan">
                                    <i class="fa-solid fa-circle-exclamation text-sky-500 text-sm"></i>
                                </button>

                                <!-- Popover Card Panduan Kendaraan -->
                                <div id="popover-panduan-kendaraan" class="popover-panduan-spesifikasi hidden absolute right-0 top-full mt-2 w-84 sm:w-[440px] max-w-[calc(100vw-36px)] bg-white rounded-3xl shadow-2xl border-2 border-sky-200 p-4 sm:p-5 z-50 text-slate-700 text-left">
                                    <div class="flex items-center justify-between border-b border-cyan-100 pb-2.5 mb-3">
                                        <div class="flex items-center gap-2 text-xs font-black text-slate-800">
                                            <i class="fa-solid fa-car-side text-cyan-600 text-sm"></i>
                                            <span>Panduan Lengkap Rental Kendaraan</span>
                                        </div>
                                        <button type="button" onclick="closeAllPanduan()" class="w-6 h-6 rounded-full hover:bg-slate-100 text-slate-400 hover:text-slate-600 flex items-center justify-center text-xs cursor-pointer">
                                            <i class="fa-solid fa-xmark"></i>
                                        </button>
                                    </div>
                                    <div class="max-h-[440px] overflow-y-auto space-y-3.5 text-xs pr-1.5 scrollbar-thin">
                                        <!-- Cakupan Unit -->
                                        <div class="p-3 rounded-2xl bg-cyan-50/70 border border-cyan-100 space-y-1.5">
                                            <p class="font-black text-cyan-900 flex items-center gap-1.5">
                                                <i class="fa-solid fa-circle-check text-cyan-600"></i> Cakupan Unit Kendaraan:
                                            </p>
                                            <p class="text-[11px] text-slate-600 leading-relaxed">
                                                Mobil penumpang (City Car, MPV keluarga, SUV, Minibus), sepeda motor (matic harian, maxi scooter, trail, sport), armada niaga (pickup bak, blind van), dan sepeda santai/listrik (E-bike).
                                            </p>
                                        </div>

                                        <!-- Panduan Kolom Spesifikasi -->
                                        <div class="space-y-2">
                                            <p class="font-black text-slate-800 flex items-center gap-1.5">
                                                <i class="fa-solid fa-list-check text-sky-500"></i> Panduan Pengisian Setiap Kolom:
                                            </p>
                                            <ul class="space-y-2 text-[11px] text-slate-600 list-disc pl-4 leading-relaxed">
                                                <li><strong>Jenis & Transmisi:</strong> Tentukan tipe unit serta sistem transmisi <em>Automatic (Matic)</em> untuk kenyamanan perkotaan atau <em>Manual</em> untuk performa tanjakan.</li>
                                                <li><strong>Bahan Bakar & Konsumsi:</strong> Tentukan Bensin (Pertalite/Pertamax), Solar/Diesel, Listrik EV, atau Hybrid.</li>
                                                <li><strong>Kapasitas Kursi:</strong> Pilih 2 orang (motor/city car 2-seater), 4-5 kursi (sedan/hatchback), 7-8 kursi (MPV/SUV), atau 12+ kursi (HiAce/Elf).</li>
                                                <li><strong>Opsi Layanan Driver:</strong> Pilih <em>Lepas Kunci Saja</em>, <em>Dengan Driver / Supir</em>, atau <em>Bisa Keduanya</em>. Jika dengan supir, jelaskan di deskripsi apakah tarif sudah termasuk uang makan supir & BBM.</li>
                                                <li><strong>Tahun Unit:</strong> Tulis tahun pembuatan (misal: <code>2022</code>, <code>2024</code>) untuk transparansi kondisi armada.</li>
                                                <li><strong>Fasilitas:</strong> Centang kelengkapan (AC double blower, audio bluetooth, kamera parkir mundur, charger mobil, e-toll, 2 helm SNI + jas hujan).</li>
                                            </ul>
                                        </div>

                                        <!-- Standar Kelengkapan Safety -->
                                        <div class="p-3 rounded-2xl bg-slate-50 border border-slate-200 text-[11px] text-slate-600 space-y-1.5">
                                            <p class="font-black text-slate-800 flex items-center gap-1.5">
                                                <i class="fa-solid fa-toolbox text-cyan-600"></i> Kelengkapan Unit & Dokumen Wajib:
                                            </p>
                                            <p class="leading-relaxed">
                                                Sertakan fotokopi STNK resmi yang masih berlaku pajaknya. Untuk mobil wajib tersedia ban serep, dongkrak, kunci roda, dan segitiga darurat. Untuk motor wajib tersedia 2 helm SNI bersih dan jas hujan.
                                            </p>
                                        </div>

                                        <!-- SOP Serah Terima & Keamanan Aset -->
                                        <div class="p-3 rounded-2xl bg-cyan-50/50 border border-cyan-100 text-[11px] text-slate-600 space-y-1.5">
                                            <p class="font-black text-cyan-900 flex items-center gap-1.5">
                                                <i class="fa-solid fa-shield-halved text-cyan-600"></i> SOP Serah Terima & Proteksi Vendor:
                                            </p>
                                            <ul class="space-y-1 list-disc pl-4 leading-relaxed">
                                                <li><strong>Foto/Video 360 Derajat:</strong> Selalu rekam video kondisi keliling bodi unit (catat baret/penyok yang sudah ada) bersama penyewa sebelum kunci diserahkan.</li>
                                                <li><strong>Catat Odometer & BBM:</strong> Catat kilometer awal dan posisi jarum bensin di nota serah terima (gunakan aturan pengembalian bensin setara / <em>same-to-same</em>).</li>
                                                <li><strong>Jaminan Identitas Asli:</strong> Wajib menahan minimal 1 kartu identitas fisik asli penyewa (e-KTP) dan verifikasi keaslian SIM yang aktif.</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div>
                            <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-1.5">Jenis Kendaraan</label>
                            <select name="spesifikasi[tipe_kendaraan]" class="w-full px-3.5 py-2.5 rounded-2xl border border-slate-200 text-slate-800 text-xs font-semibold focus:outline-none focus:border-cyan-500 bg-white">
                                <option value="Mobil" {{ old('spesifikasi.tipe_kendaraan') == 'Mobil' ? 'selected' : '' }}>Mobil Penumpang</option>
                                <option value="Motor" {{ old('spesifikasi.tipe_kendaraan') == 'Motor' ? 'selected' : '' }}>Sepeda Motor</option>
                                <option value="Pickup / Box" {{ old('spesifikasi.tipe_kendaraan') == 'Pickup / Box' ? 'selected' : '' }}>Mobil Pickup / Box Niaga</option>
                                <option value="Sepeda" {{ old('spesifikasi.tipe_kendaraan') == 'Sepeda' ? 'selected' : '' }}>Sepeda Gowes / Listrik</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-1.5">Transmisi</label>
                            <select name="spesifikasi[transmisi]" class="w-full px-3.5 py-2.5 rounded-2xl border border-slate-200 text-slate-800 text-xs font-semibold focus:outline-none focus:border-cyan-500 bg-white">
                                <option value="Automatic" {{ old('spesifikasi.transmisi') == 'Automatic' ? 'selected' : '' }}>Automatic (Matic)</option>
                                <option value="Manual" {{ old('spesifikasi.transmisi') == 'Manual' ? 'selected' : '' }}>Manual</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-1.5">Bahan Bakar</label>
                            <select name="spesifikasi[bahan_bakar]" class="w-full px-3.5 py-2.5 rounded-2xl border border-slate-200 text-slate-800 text-xs font-semibold focus:outline-none focus:border-cyan-500 bg-white">
                                <option value="Bensin" {{ old('spesifikasi.bahan_bakar') == 'Bensin' ? 'selected' : '' }}>Bensin (Pertalite / Pertamax)</option>
                                <option value="Solar / Diesel" {{ old('spesifikasi.bahan_bakar') == 'Solar / Diesel' ? 'selected' : '' }}>Solar / Diesel</option>
                                <option value="Listrik (EV)" {{ old('spesifikasi.bahan_bakar') == 'Listrik (EV)' ? 'selected' : '' }}>Listrik (EV)</option>
                                <option value="Hybrid" {{ old('spesifikasi.bahan_bakar') == 'Hybrid' ? 'selected' : '' }}>Hybrid</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div>
                            <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-1.5">Kapasitas Kursi</label>
                            <select name="spesifikasi[kapasitas_penumpang]" class="w-full px-3.5 py-2.5 rounded-2xl border border-slate-200 text-slate-800 text-xs font-semibold focus:outline-none focus:border-cyan-500 bg-white">
                                <option value="2 Orang" {{ old('spesifikasi.kapasitas_penumpang') == '2 Orang' ? 'selected' : '' }}>2 Orang (Motor/City Car)</option>
                                <option value="4-5 Kursi" {{ old('spesifikasi.kapasitas_penumpang', '4-5 Kursi') == '4-5 Kursi' ? 'selected' : '' }}>4 - 5 Kursi (Sedan/Hatchback)</option>
                                <option value="7-8 Kursi" {{ old('spesifikasi.kapasitas_penumpang') == '7-8 Kursi' ? 'selected' : '' }}>7 - 8 Kursi (MPV/SUV)</option>
                                <option value="12+ Kursi" {{ old('spesifikasi.kapasitas_penumpang') == '12+ Kursi' ? 'selected' : '' }}>12+ Kursi (HiAce / Elf)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-1.5">Opsi Layanan Driver</label>
                            <select name="spesifikasi[opsi_driver]" class="w-full px-3.5 py-2.5 rounded-2xl border border-slate-200 text-slate-800 text-xs font-semibold focus:outline-none focus:border-cyan-500 bg-white">
                                <option value="Lepas Kunci Saja" {{ old('spesifikasi.opsi_driver') == 'Lepas Kunci Saja' ? 'selected' : '' }}>Lepas Kunci Saja</option>
                                <option value="Dengan Driver / Supir" {{ old('spesifikasi.opsi_driver') == 'Dengan Driver / Supir' ? 'selected' : '' }}>Dengan Driver / Supir</option>
                                <option value="Bisa Keduanya (Lepas Kunci / Driver)" {{ old('spesifikasi.opsi_driver') == 'Bisa Keduanya (Lepas Kunci / Driver)' ? 'selected' : '' }}>Bisa Lepas Kunci / Dengan Driver</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-1.5">Tahun Kendaraan</label>
                            <input type="number" name="spesifikasi[tahun_kendaraan]" value="{{ (string) old('spesifikasi.tahun_kendaraan') }}" class="w-full px-3.5 py-2.5 rounded-2xl border border-slate-200 text-slate-800 text-xs font-semibold focus:outline-none focus:border-cyan-500">
                        </div>
                    </div>

                    <!-- Fasilitas Kendaraan -->
                    <div>
                        <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-2">Fasilitas & Kelengkapan Kendaraan</label>
                        @php
                            $fasilitasVehicleList = [
                                ['AC Double Blower Dingin', 'fa-snowflake'],
                                ['Audio Bluetooth & USB', 'fa-music'],
                                ['Kamera Parkir Mundur', 'fa-camera'],
                                ['Charger HP Mobil', 'fa-charging-station'],
                                ['E-Toll Card Tersedia', 'fa-credit-card'],
                                ['2 Helm SNI + Jas Hujan (Motor)', 'fa-helmet-safety'],
                                ['Kunci Pengaman Tambahan', 'fa-lock'],
                            ];
                            $oldFasVehicle = old('spesifikasi.fasilitas_kendaraan', []);
                        @endphp
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                            @foreach($fasilitasVehicleList as [$fV, $icV])
                            <label class="cursor-pointer relative flex items-center p-2 rounded-xl border border-slate-200 hover:border-cyan-300 has-[:checked]:border-cyan-500 has-[:checked]:bg-cyan-50/70 transition">
                                <input type="checkbox" name="spesifikasi[fasilitas_kendaraan][]" value="{{ $fV }}" {{ in_array($fV, (array)$oldFasVehicle) ? 'checked' : '' }} class="mr-2 rounded text-cyan-600 focus:ring-cyan-400">
                                <i class="fa-solid {{ $icV }} text-cyan-600 mr-2 text-xs"></i>
                                <span class="text-[11px] font-semibold text-slate-700">{{ $fV }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- PANEL 3: ELEKTRONIK & GEAR -->
                <div id="panel-elektronik" class="hidden bg-white p-5 sm:p-6 rounded-3xl border-2 border-blue-300 shadow-sm space-y-4 transition-all">
                    <div class="border-b border-blue-100 pb-3 flex items-center justify-between gap-3">
                        <div class="flex items-center gap-2.5">
                            <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-blue-400 to-indigo-600 text-white flex items-center justify-center text-lg shadow-sm shrink-0">
                                <i class="fa-solid fa-camera"></i>
                            </div>
                            <div>
                                <h3 class="text-sm font-black text-slate-800">Spesifikasi Elektronik & Gear</h3>
                            </div>
                        </div>

                        <!-- Kanan Atas Bingkai: Badge & Tombol Panduan -->
                        <div class="flex items-center gap-2 shrink-0">
                            <span class="hidden sm:inline-block px-2.5 py-1 bg-blue-50 text-blue-600 rounded-full text-[10px] font-black border border-blue-200">
                                Gear Elektronik
                            </span>

                            <!-- Tombol Panduan di Atas Kanan Bingkai -->
                            <div class="relative">
                                <button type="button" data-btn-panduan="true" onclick="togglePanduanPanel('elektronik', event)" class="w-8 h-8 rounded-full bg-sky-50 hover:bg-sky-100 text-sky-600 border border-sky-200 flex items-center justify-center shadow-xs transition hover:scale-105 cursor-pointer focus:outline-none" title="Lihat Panduan Lengkap Kategori Elektronik & Gear">
                                    <i class="fa-solid fa-circle-exclamation text-sky-500 text-sm"></i>
                                </button>

                                <!-- Popover Card Panduan Elektronik -->
                                <div id="popover-panduan-elektronik" class="popover-panduan-spesifikasi hidden absolute right-0 top-full mt-2 w-84 sm:w-[440px] max-w-[calc(100vw-36px)] bg-white rounded-3xl shadow-2xl border-2 border-sky-200 p-4 sm:p-5 z-50 text-slate-700 text-left">
                                    <div class="flex items-center justify-between border-b border-blue-100 pb-2.5 mb-3">
                                        <div class="flex items-center gap-2 text-xs font-black text-slate-800">
                                            <i class="fa-solid fa-camera text-blue-600 text-sm"></i>
                                            <span>Panduan Lengkap Elektronik & Gear</span>
                                        </div>
                                        <button type="button" onclick="closeAllPanduan()" class="w-6 h-6 rounded-full hover:bg-slate-100 text-slate-400 hover:text-slate-600 flex items-center justify-center text-xs cursor-pointer">
                                            <i class="fa-solid fa-xmark"></i>
                                        </button>
                                    </div>
                                    <div class="max-h-[440px] overflow-y-auto space-y-3.5 text-xs pr-1.5 scrollbar-thin">
                                        <!-- Cakupan Unit -->
                                        <div class="p-3 rounded-2xl bg-blue-50/70 border border-blue-100 space-y-1.5">
                                            <p class="font-black text-blue-900 flex items-center gap-1.5">
                                                <i class="fa-solid fa-circle-check text-blue-600"></i> Cakupan Perangkat Elektronik:
                                            </p>
                                            <p class="text-[11px] text-slate-600 leading-relaxed">
                                                Kamera Mirrorless / DSLR & lensa (Sony, Canon, Fujifilm), Action Cam (GoPro, Insta360), Drone foto/video (DJI Mini/Mavic), Gimbal stabilizer (Ronin/Osmo), Mic wireless clip-on, Lighting studio & softbox, iPad/Tablet, Proyektor, serta Laptop editing.
                                            </p>
                                        </div>

                                        <!-- Panduan Kolom Spesifikasi -->
                                        <div class="space-y-2">
                                            <p class="font-black text-slate-800 flex items-center gap-1.5">
                                                <i class="fa-solid fa-list-check text-sky-500"></i> Panduan Pengisian Setiap Kolom:
                                            </p>
                                            <ul class="space-y-2 text-[11px] text-slate-600 list-disc pl-4 leading-relaxed">
                                                <li><strong>Merek / Brand Lengkap:</strong> Tulis tipe spesifik dan seri model (contoh: <code>Sony A7 IV + Lensa 24-70mm GM</code> atau <code>DJI Mini 4 Pro Fly More Combo</code>).</li>
                                                <li><strong>Kondisi Fungsi & Lensa:</strong> Sebutkan status optik secara jujur (contoh: <em>Sensor bersih bebas jamur / dead pixel, Shutter count 15rb, Autofocus normal</em>).</li>
                                                <li><strong>Kelengkapan Paket yang Didapat:</strong> Centang unit utama, baterai cadangan (minimal 2x), charger dock original, memory card high speed (V30/V60), tas/hardcase pelindung, kabel HDMI/Type-C, dan tripod kokoh.</li>
                                            </ul>
                                        </div>

                                        <!-- Standar Kesiapan Unit -->
                                        <div class="p-3 rounded-2xl bg-slate-50 border border-slate-200 text-[11px] text-slate-600 space-y-1.5">
                                            <p class="font-black text-slate-800 flex items-center gap-1.5">
                                                <i class="fa-solid fa-battery-three-quarters text-blue-600"></i> Standar Kesiapan Sebelum Diserahkan:
                                            </p>
                                            <p class="leading-relaxed">
                                                Pastikan seluruh baterai telah di-charge 100% penuh, memory card sudah diformat bersih bebas dari data lama, dan tutup sensor / lensa selalu terpasang dengan aman saat pengiriman.
                                            </p>
                                        </div>

                                        <!-- SOP Serah Terima & Keamanan Aset -->
                                        <div class="p-3 rounded-2xl bg-blue-50/50 border border-blue-100 text-[11px] text-slate-600 space-y-1.5">
                                            <p class="font-black text-blue-900 flex items-center gap-1.5">
                                                <i class="fa-solid fa-shield-halved text-blue-600"></i> SOP Serah Terima & Proteksi Vendor:
                                            </p>
                                            <ul class="space-y-1 list-disc pl-4 leading-relaxed">
                                                <li>Uji fungsionalitas tombol daya, autofocus, layar sentuh, dan tes rekaman audio di hadapan penyewa saat serah terima.</li>
                                                <li>Terapkan deposit jaminan (uang tunai / e-KTP asli + dokumen sekunder) untuk unit bernilai tinggi.</li>
                                                <li>Ingatkan penyewa mengenai pantangan: Dilarang terkena air hujan (non-waterproof) dan hindari sorotan sinar laser panggung langsung ke sensor kamera.</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-1.5">Merek / Brand</label>
                            <input type="text" name="spesifikasi[merek]" value="{{ (string) old('spesifikasi.merek') }}" class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-slate-800 text-xs font-semibold focus:outline-none focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-1.5">Kondisi Fungsi & Lensa</label>
                            <input type="text" name="spesifikasi[kondisi_detail]" value="{{ (string) old('spesifikasi.kondisi_detail') }}" class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-slate-800 text-xs font-semibold focus:outline-none focus:border-blue-500">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-2">Kelengkapan Paket yang Didapat Penyewa</label>
                        @php
                            $kelengkapanElecList = [
                                ['Unit Utama', 'fa-cube'],
                                ['Baterai Cadangan (2x)', 'fa-battery-full'],
                                ['Charger & Adapter Original', 'fa-plug'],
                                ['Memory Card High Speed', 'fa-sd-card'],
                                ['Tas / Hardcase Pelindung', 'fa-suitcase'],
                                ['Kabel HDMI / USB Data', 'fa-network-wired'],
                                ['Tripod / Monopod Kokoh', 'fa-arrows-to-dot'],
                            ];
                            $oldKelElec = old('spesifikasi.kelengkapan_elektronik', []);
                        @endphp
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                            @foreach($kelengkapanElecList as [$kE, $icE])
                            <label class="cursor-pointer relative flex items-center p-2 rounded-xl border border-slate-200 hover:border-blue-300 has-[:checked]:border-blue-500 has-[:checked]:bg-blue-50/70 transition">
                                <input type="checkbox" name="spesifikasi[kelengkapan_elektronik][]" value="{{ $kE }}" {{ in_array($kE, (array)$oldKelElec) ? 'checked' : '' }} class="mr-2 rounded text-blue-600 focus:ring-blue-400">
                                <i class="fa-solid {{ $icE }} text-blue-600 mr-2 text-xs"></i>
                                <span class="text-[11px] font-semibold text-slate-700">{{ $kE }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- PANEL 4: OUTDOOR & CAMPING -->
                <div id="panel-outdoor" class="hidden bg-white p-5 sm:p-6 rounded-3xl border-2 border-teal-300 shadow-sm space-y-4 transition-all">
                    <div class="border-b border-teal-100 pb-3 flex items-center justify-between gap-3">
                        <div class="flex items-center gap-2.5">
                            <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-teal-400 to-emerald-600 text-white flex items-center justify-center text-lg shadow-sm shrink-0">
                                <i class="fa-solid fa-campground"></i>
                            </div>
                            <div>
                                <h3 class="text-sm font-black text-slate-800">Spesifikasi Outdoor & Camping</h3>
                            </div>
                        </div>

                        <!-- Kanan Atas Bingkai: Badge & Tombol Panduan -->
                        <div class="flex items-center gap-2 shrink-0">
                            <span class="hidden sm:inline-block px-2.5 py-1 bg-teal-50 text-teal-600 rounded-full text-[10px] font-black border border-teal-200">
                                Outdoor Gear
                            </span>

                            <!-- Tombol Panduan di Atas Kanan Bingkai -->
                            <div class="relative">
                                <button type="button" data-btn-panduan="true" onclick="togglePanduanPanel('outdoor', event)" class="w-8 h-8 rounded-full bg-sky-50 hover:bg-sky-100 text-sky-600 border border-sky-200 flex items-center justify-center shadow-xs transition hover:scale-105 cursor-pointer focus:outline-none" title="Lihat Panduan Lengkap Kategori Outdoor & Camping">
                                    <i class="fa-solid fa-circle-exclamation text-sky-500 text-sm"></i>
                                </button>

                                <!-- Popover Card Panduan Outdoor -->
                                <div id="popover-panduan-outdoor" class="popover-panduan-spesifikasi hidden absolute right-0 top-full mt-2 w-84 sm:w-[440px] max-w-[calc(100vw-36px)] bg-white rounded-3xl shadow-2xl border-2 border-sky-200 p-4 sm:p-5 z-50 text-slate-700 text-left">
                                    <div class="flex items-center justify-between border-b border-teal-100 pb-2.5 mb-3">
                                        <div class="flex items-center gap-2 text-xs font-black text-slate-800">
                                            <i class="fa-solid fa-campground text-teal-600 text-sm"></i>
                                            <span>Panduan Lengkap Outdoor & Camping</span>
                                        </div>
                                        <button type="button" onclick="closeAllPanduan()" class="w-6 h-6 rounded-full hover:bg-slate-100 text-slate-400 hover:text-slate-600 flex items-center justify-center text-xs cursor-pointer">
                                            <i class="fa-solid fa-xmark"></i>
                                        </button>
                                    </div>
                                    <div class="max-h-[440px] overflow-y-auto space-y-3.5 text-xs pr-1.5 scrollbar-thin">
                                        <!-- Cakupan Peralatan -->
                                        <div class="p-3 rounded-2xl bg-teal-50/70 border border-teal-100 space-y-1.5">
                                            <p class="font-black text-teal-900 flex items-center gap-1.5">
                                                <i class="fa-solid fa-circle-check text-teal-600"></i> Cakupan Perlengkapan Outdoor:
                                            </p>
                                            <p class="text-[11px] text-slate-600 leading-relaxed">
                                                Tenda dome kapasitas 2P/4P/6P, flysheet peneduh, sleeping bag bulu/polar, matras foil / kasur angin, tas ransel keril (40L - 80L), kompor portable & nesting masak, lampu tenda / headlamp, trekking pole, serta meja dan kursi lipat santai.
                                            </p>
                                        </div>

                                        <!-- Panduan Kolom Spesifikasi -->
                                        <div class="space-y-2">
                                            <p class="font-black text-slate-800 flex items-center gap-1.5">
                                                <i class="fa-solid fa-list-check text-sky-500"></i> Panduan Pengisian Setiap Kolom:
                                            </p>
                                            <ul class="space-y-2 text-[11px] text-slate-600 list-disc pl-4 leading-relaxed">
                                                <li><strong>Kapasitas / Ukuran:</strong> Tuliskan kapasitas orang atau volume liter (contoh: <code>Tenda 4-5 Orang Double Layer</code> atau <code>Carrier 60+10 Liter</code>).</li>
                                                <li><strong>Ketahanan Cuaca & Material:</strong> Sebutkan spesifikasi bahan tahan air (contoh: <code>Waterproof PU 3000mm Anti Badai, Frame Alloy Aluminium Ringan</code>).</li>
                                                <li><strong>Kelengkapan Camping:</strong> Centang pasak tenda lengkap (tidak boleh kurang dari 8-12 biji), tali guyline reflektif, footprint/alas terpal, tas original, dan frame cadangan.</li>
                                            </ul>
                                        </div>

                                        <!-- Standar Kebersihan & Kering -->
                                        <div class="p-3 rounded-2xl bg-slate-50 border border-slate-200 text-[11px] text-slate-600 space-y-1.5">
                                            <p class="font-black text-slate-800 flex items-center gap-1.5">
                                                <i class="fa-solid fa-sun text-amber-500"></i> Standar Kebersihan & Bebas Lembap:
                                            </p>
                                            <p class="leading-relaxed">
                                                Pastikan tenda dan kantong tidur (sleeping bag) dalam kondisi <strong>100% kering sempurna dan bersih dari tanah/pasir</strong>. Tenda yang disimpan/diserahkan dalam kondisi lembap akan cepat berjamur dan lapisan anti-airnya mengelupas.
                                            </p>
                                        </div>

                                        <!-- SOP Serah Terima & Keamanan Aset -->
                                        <div class="p-3 rounded-2xl bg-teal-50/50 border border-teal-100 text-[11px] text-slate-600 space-y-1.5">
                                            <p class="font-black text-teal-900 flex items-center gap-1.5">
                                                <i class="fa-solid fa-shield-halved text-teal-600"></i> SOP Serah Terima & Proteksi Vendor:
                                            </p>
                                            <ul class="space-y-1 list-disc pl-4 leading-relaxed">
                                                <li>Hitung jumlah pasak besi dan rangkaian batang frame tenda bersama penyewa saat pengambilan dan pengembalian unit.</li>
                                                <li>Periksa kondisi tali elastis dalam frame tenda agar tidak kendor atau putus.</li>
                                                <li>Berikan instruksi agar penyewa tidak menyalakan api/memasak di dalam tenda tertutup untuk keamanan jiwa dan keselamatan kain tenda.</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-1.5">Kapasitas / Ukuran</label>
                            <input type="text" name="spesifikasi[kapasitas_outdoor]" value="{{ (string) old('spesifikasi.kapasitas_outdoor') }}" class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-slate-800 text-xs font-semibold focus:outline-none focus:border-teal-500">
                        </div>
                        <div>
                            <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-1.5">Ketahanan Cuaca & Material</label>
                            <input type="text" name="spesifikasi[fitur_outdoor]" value="{{ (string) old('spesifikasi.fitur_outdoor') }}" class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-slate-800 text-xs font-semibold focus:outline-none focus:border-teal-500">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-2">Kelengkapan Camping</label>
                        @php
                            $kelengkapanOutdoorList = [
                                ['Pasak Tenda Lengkap', 'fa-location-pin'],
                                ['Tali Guyline Reflektif', 'fa-link'],
                                ['Footprint / Alas Terpal Tenda', 'fa-layer-group'],
                                ['Tas Tenda Original', 'fa-bag-shopping'],
                                ['Frame Cadangan', 'fa-circle-nodes'],
                            ];
                            $oldKelOut = old('spesifikasi.kelengkapan_outdoor', []);
                        @endphp
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                            @foreach($kelengkapanOutdoorList as [$kO, $icO])
                            <label class="cursor-pointer relative flex items-center p-2 rounded-xl border border-slate-200 hover:border-teal-300 has-[:checked]:border-teal-500 has-[:checked]:bg-teal-50/70 transition">
                                <input type="checkbox" name="spesifikasi[kelengkapan_outdoor][]" value="{{ $kO }}" {{ in_array($kO, (array)$oldKelOut) ? 'checked' : '' }} class="mr-2 rounded text-teal-600 focus:ring-teal-400">
                                <i class="fa-solid {{ $icO }} text-teal-600 mr-2 text-xs"></i>
                                <span class="text-[11px] font-semibold text-slate-700">{{ $kO }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- PANEL 5: PERALATAN HAVE FUN -->
                <div id="panel-have-fun" class="hidden bg-white p-5 sm:p-6 rounded-3xl border-2 border-indigo-300 shadow-sm space-y-4 transition-all">
                    <div class="border-b border-indigo-100 pb-3 flex items-center justify-between gap-3">
                        <div class="flex items-center gap-2.5">
                            <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-indigo-400 to-purple-600 text-white flex items-center justify-center text-lg shadow-sm shrink-0">
                                <i class="fa-solid fa-guitar"></i>
                            </div>
                            <div>
                                <h3 class="text-sm font-black text-slate-800">Spesifikasi Peralatan Have Fun</h3>
                            </div>
                        </div>

                        <!-- Kanan Atas Bingkai: Badge & Tombol Panduan -->
                        <div class="flex items-center gap-2 shrink-0">
                            <span class="hidden sm:inline-block px-2.5 py-1 bg-indigo-50 text-indigo-600 rounded-full text-[10px] font-black border border-indigo-200">
                                Have Fun & Party
                            </span>

                            <!-- Tombol Panduan di Atas Kanan Bingkai -->
                            <div class="relative">
                                <button type="button" data-btn-panduan="true" onclick="togglePanduanPanel('have-fun', event)" class="w-8 h-8 rounded-full bg-sky-50 hover:bg-sky-100 text-sky-600 border border-sky-200 flex items-center justify-center shadow-xs transition hover:scale-105 cursor-pointer focus:outline-none" title="Lihat Panduan Lengkap Kategori Peralatan Have Fun">
                                    <i class="fa-solid fa-circle-exclamation text-sky-500 text-sm"></i>
                                </button>

                                <!-- Popover Card Panduan Have Fun -->
                                <div id="popover-panduan-have-fun" class="popover-panduan-spesifikasi hidden absolute right-0 top-full mt-2 w-84 sm:w-[440px] max-w-[calc(100vw-36px)] bg-white rounded-3xl shadow-2xl border-2 border-sky-200 p-4 sm:p-5 z-50 text-slate-700 text-left">
                                    <div class="flex items-center justify-between border-b border-indigo-100 pb-2.5 mb-3">
                                        <div class="flex items-center gap-2 text-xs font-black text-slate-800">
                                            <i class="fa-solid fa-guitar text-indigo-600 text-sm"></i>
                                            <span>Panduan Lengkap Peralatan Have Fun</span>
                                        </div>
                                        <button type="button" onclick="closeAllPanduan()" class="w-6 h-6 rounded-full hover:bg-slate-100 text-slate-400 hover:text-slate-600 flex items-center justify-center text-xs cursor-pointer">
                                            <i class="fa-solid fa-xmark"></i>
                                        </button>
                                    </div>
                                    <div class="max-h-[440px] overflow-y-auto space-y-3.5 text-xs pr-1.5 scrollbar-thin">
                                        <!-- Cakupan Unit -->
                                        <div class="p-3 rounded-2xl bg-indigo-50/70 border border-indigo-100 space-y-1.5">
                                            <p class="font-black text-indigo-900 flex items-center gap-1.5">
                                                <i class="fa-solid fa-circle-check text-indigo-600"></i> Cakupan Unit & Hiburan:
                                            </p>
                                            <p class="text-[11px] text-slate-600 leading-relaxed">
                                                Konsol Game (PS4/PS5, Nintendo Switch, Oculus/VR, Steering Wheel G29), Alat Musik (Gitar akustik/listrik, Keyboard, Cajon, Mini Amp), Sound System & Karaoke (Speaker portable bluetooth 8-15 inch, Dual Mic Wireless), Mesin Bubble pesta, Lampu disko panggung, Board game seru, dan Proyektor nobar mini.
                                            </p>
                                        </div>

                                        <!-- Panduan Kolom Spesifikasi -->
                                        <div class="space-y-2">
                                            <p class="font-black text-slate-800 flex items-center gap-1.5">
                                                <i class="fa-solid fa-list-check text-sky-500"></i> Panduan Pengisian Setiap Kolom:
                                            </p>
                                            <ul class="space-y-2 text-[11px] text-slate-600 list-disc pl-4 leading-relaxed">
                                                <li><strong>Jenis Paket Hiburan:</strong> Tuliskan nama paket lengkap dan jumlah perlengkapan (contoh: <code>Paket PS5 Disc Edition + 2 Stick DualSense + 5 Game Populer (FIFA 24, Tekken 8)</code> atau <code>Speaker Portable Party 12 Inch + 2 Mic Wireless + Stand Tripod</code>).</li>
                                                <li><strong>Kelengkapan Game / Aksesoris:</strong> Cantumkan seluruh kabel pelengkap (Kabel HDMI 2.1, Kabel Power, Kabel Charger Type-C), adaptor, stand mic, baterai cadangan, tas pelindung, dan kaset game yang disertakan.</li>
                                            </ul>
                                        </div>

                                        <!-- Standar Kesiapan Perangkat -->
                                        <div class="p-3 rounded-2xl bg-slate-50 border border-slate-200 text-[11px] text-slate-600 space-y-1.5">
                                            <p class="font-black text-slate-800 flex items-center gap-1.5">
                                                <i class="fa-solid fa-gamepad text-indigo-600"></i> Standar Kesiapan Perangkat & Akun:
                                            </p>
                                            <p class="leading-relaxed">
                                                Pastikan stik/controller bebas dari kendala <em>stick drift</em>, volume speaker jernih tanpa distorsi suara, dan untuk konsol game gunakan profil tamu (*guest account*) agar data pribadi Anda tetap terlindungi.
                                            </p>
                                        </div>

                                        <!-- SOP Serah Terima & Keamanan Aset -->
                                        <div class="p-3 rounded-2xl bg-indigo-50/50 border border-indigo-100 text-[11px] text-slate-600 space-y-1.5">
                                            <p class="font-black text-indigo-900 flex items-center gap-1.5">
                                                <i class="fa-solid fa-shield-halved text-indigo-600"></i> SOP Serah Terima & Proteksi Vendor:
                                            </p>
                                            <ul class="space-y-1 list-disc pl-4 leading-relaxed">
                                                <li>Wajib tes nyala dan coba suara speaker / gameplay konsol di hadapan penyewa sebelum serah terima.</li>
                                                <li>Pastikan semua kabel pendukung dan controller dihitung bersama penyewa.</li>
                                                <li>Terapkan deposit jaminan (uang tunai / e-KTP asli) untuk peralatan bernilai tinggi seperti PS5, keyboard musik, dan speaker aktif premium.</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-1.5">Jenis Paket Hiburan</label>
                            <input type="text" name="spesifikasi[jenis_hiburan]" value="{{ (string) old('spesifikasi.jenis_hiburan') }}" class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-slate-800 text-xs font-semibold focus:outline-none focus:border-indigo-500">
                        </div>
                        <div>
                            <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-1.5">Kelengkapan Game / Aksesoris</label>
                            <input type="text" name="spesifikasi[kelengkapan_have_fun]" value="{{ (string) old('spesifikasi.kelengkapan_have_fun') }}" class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-slate-800 text-xs font-semibold focus:outline-none focus:border-indigo-500">
                        </div>
                    </div>
                </div>

                <!-- 3. LOKASI GUDANG / PRODUK (GPS 1-TAP MOBILE) -->
                <div class="bg-white p-5 sm:p-6 rounded-3xl border border-slate-200 shadow-sm space-y-4">
                    <h2 class="text-base font-black text-slate-800 border-b border-slate-100 pb-3 flex items-center gap-2">
                        <i class="fa-solid fa-map-location-dot text-sky-500"></i> Titik Lokasi Gudang & Opsi Antar
                    </h2>

                    <!-- Pilihan Layanan Antar Kurir -->
                    <div>
                        <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-2">
                            Layanan Antar ke Penyewa (Kurir Toko) <span class="text-rose-500">*</span>
                        </label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <label class="cursor-pointer relative">
                                <input type="radio" name="is_delivery_supported" value="1" {{ old('is_delivery_supported', '1') == '1' ? 'checked' : '' }} class="peer sr-only">
                                <div class="p-3.5 rounded-2xl border-2 border-slate-200 peer-checked:border-sky-500 peer-checked:bg-sky-50/50 flex items-center gap-3 transition">
                                    <div class="w-9 h-9 rounded-xl bg-sky-100 text-sky-600 flex items-center justify-center text-sm font-bold">
                                        <i class="fa-solid fa-motorcycle"></i>
                                    </div>
                                    <div>
                                        <p class="text-xs font-black text-slate-800">Sedia Kurir Antar</p>
                                        <p class="text-[11px] text-slate-500">Dihitung otomatis per KM</p>
                                    </div>
                                </div>
                            </label>

                            <label class="cursor-pointer relative">
                                <input type="radio" name="is_delivery_supported" value="0" {{ old('is_delivery_supported') == '0' ? 'checked' : '' }} class="peer sr-only">
                                <div class="p-3.5 rounded-2xl border-2 border-slate-200 peer-checked:border-rose-400 peer-checked:bg-rose-50/50 flex items-center gap-3 transition">
                                    <div class="w-9 h-9 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center text-sm font-bold">
                                        <i class="fa-solid fa-person-walking"></i>
                                    </div>
                                    <div>
                                        <p class="text-xs font-black text-slate-800">Hanya Ambil di Toko</p>
                                        <p class="text-[11px] text-slate-500">Penyewa wajib ambil sendiri</p>
                                    </div>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Deteksi GPS HP Otomatis -->
                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 space-y-3">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                            <div>
                                <span class="text-xs font-black text-slate-800">1-Tap GPS Koordinat HP</span>
                                <p class="text-[11px] text-slate-500">Tekan tombol saat Anda di toko untuk mengisi koordinat & alamat akurat.</p>
                            </div>
                            <button type="button" onclick="getLokasiGPS()" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-gradient-to-r from-sky-500 to-blue-600 hover:from-sky-600 text-white rounded-xl text-xs font-extrabold shadow-sm active:scale-95 transition">
                                <i class="fa-solid fa-location-crosshairs text-sm"></i>
                                <span>Deteksi GPS HP</span>
                            </button>
                        </div>

                        <!-- Status Bar GPS -->
                        <div id="status_gps" class="px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl text-xs font-medium text-slate-600 flex items-center gap-2">
                            <i class="fa-solid fa-circle-dot text-slate-400 text-xs"></i>
                            <span>GPS belum diaktifkan (Bisa tekan tombol atau isi alamat langsung).</span>
                        </div>

                        <!-- Hidden Inputs Lat/Lon -->
                        <input type="hidden" name="latitude" id="lat_produk" value="{{ (string) old('latitude', '-6.200000') }}">
                        <input type="hidden" name="longitude" id="lon_produk" value="{{ (string) old('longitude', '106.816666') }}">

                        <!-- Alamat Input -->
                        <div>
                            <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-1.5">
                                Alamat Lengkap Toko / Gudang Penjemputan <span class="text-rose-500">*</span>
                            </label>
                            <textarea name="alamat" id="alamat_produk" rows="2" required placeholder="Alamat lengkap lokasi penjemputan barang..." class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-slate-800 text-xs font-medium focus:outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-100 transition">{{ (string) old('alamat') }}</textarea>
                        </div>
                    </div>

                </div>

            </div>

            <!-- ============================================== -->
            <!-- KOLOM KANAN (1 SPAN): HARGA, STOK & SUBMIT -->
            <!-- ============================================== -->
            <div class="space-y-6">
                
                <!-- Harga & Ketentuan Keuangan -->
                <div class="bg-white p-5 sm:p-6 rounded-3xl border border-slate-200 shadow-sm space-y-4">
                    <h2 class="text-base font-black text-slate-800 border-b border-slate-100 pb-3 flex items-center gap-2">
                        <i class="fa-solid fa-money-bill-wave text-emerald-500"></i> Tarif & Stok
                    </h2>

                    <!-- Harga Sewa / Hari -->
                    <div>
                        <label id="label_harga_harian" class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-1.5">
                            Tarif Sewa / Hari <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-xs font-bold text-slate-400">Rp</span>
                            <input type="number" name="harga_sewa_harian" id="harga_sewa_harian" value="{{ (string) old('harga_sewa_harian') }}" required min="0" placeholder="150000" class="w-full pl-11 pr-4 py-3 rounded-2xl border border-slate-200 text-slate-800 text-sm font-black focus:outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-100 transition">
                        </div>
                        <p id="hint_harga_harian" class="text-[10px] text-slate-400 mt-1">Tarif dasar harian untuk sewa barang.</p>
                    </div>

                    <!-- Stok Unit Fisik -->
                    <div>
                        <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-1.5">
                            Jumlah Stok Unit <span class="text-rose-500">*</span>
                        </label>
                        <input type="number" name="stok_total" value="{{ (string) old('stok_total', 1) }}" required min="1" class="w-full px-4 py-3 rounded-2xl border border-slate-200 text-slate-800 text-sm font-black focus:outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-100 transition">
                    </div>

                    <!-- Deposit Jaminan -->
                    <div>
                        <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-1.5">
                            Deposit Jaminan (Opsional/Rp) <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-xs font-bold text-slate-400">Rp</span>
                            <input type="number" name="deposit" value="{{ (string) old('deposit', 0) }}" required min="0" placeholder="0" class="w-full pl-11 pr-4 py-3 rounded-2xl border border-slate-200 text-slate-800 text-sm font-semibold focus:outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-100 transition">
                        </div>
                        <p class="text-[10px] text-slate-400 mt-1">Dikembalikan utuh ke penyewa setelah barang kembali aman.</p>
                    </div>

                    <!-- Denda Keterlambatan -->
                    <div>
                        <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-1.5">
                            Denda Telat / Hari <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-xs font-bold text-slate-400">Rp</span>
                            <input type="number" name="denda_per_hari" value="{{ (string) old('denda_per_hari', 0) }}" required min="0" placeholder="50000" class="w-full pl-11 pr-4 py-3 rounded-2xl border border-slate-200 text-slate-800 text-sm font-semibold focus:outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-100 transition">
                        </div>
                    </div>
                </div>

                <!-- Card Tombol Aksi Submit -->
                <div class="bg-white p-5 rounded-3xl border border-slate-200 shadow-sm space-y-3">
                    <button type="submit" id="btnSubmitBarang" class="w-full py-4 px-5 bg-gradient-to-r from-sky-500 to-blue-600 hover:from-sky-600 hover:to-blue-700 active:scale-98 text-white font-black text-sm rounded-2xl shadow-lg shadow-sky-500/25 flex items-center justify-center gap-2 transition cursor-pointer">
                        <i class="fa-solid fa-cloud-arrow-up text-base"></i>
                        <span>AJUKAN PRODUK SEKARANG</span>
                    </button>
                    <p class="text-[11px] text-slate-400 text-center font-medium">
                        Produk Anda akan langsung masuk ke antrean kurasi Admin untuk ditinjau.
                    </p>
                </div>

            </div>

        </div>
    </form>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/heic2any@0.0.4/dist/heic2any.min.js"></script>
<script>
    // ==========================================
    // 1. MOBILE CAMERA & MULTI-PHOTO PREVIEWS & WEBP COMPRESSION
    // ==========================================
    const inputFotos = document.getElementById('fotos');
    const previewContainer = document.getElementById('preview-container');
    const photoCounter = document.getElementById('photoCounter');
    let selectedFiles = [];

    // Helper client-side image compression to WebP
    async function compressImageToWebp(file, maxDimension = 1920, quality = 0.85) {
        if (!file || !file.type || !file.type.startsWith('image/') || file.type.includes('svg') || file.type.includes('gif')) {
            return file;
        }

        return new Promise((resolve) => {
            const img = new Image();
            const url = URL.createObjectURL(file);
            img.onload = () => {
                URL.revokeObjectURL(url);
                let width = img.width;
                let height = img.height;

                if (width > maxDimension || height > maxDimension) {
                    if (width > height) {
                        height = Math.round((height * maxDimension) / width);
                        width = maxDimension;
                    } else {
                        width = Math.round((width * maxDimension) / height);
                        height = maxDimension;
                    }
                }

                const canvas = document.createElement('canvas');
                canvas.width = width;
                canvas.height = height;
                const ctx = canvas.getContext('2d');
                ctx.drawImage(img, 0, 0, width, height);

                canvas.toBlob((blob) => {
                    if (!blob) {
                        resolve(file);
                        return;
                    }
                    const newName = file.name.replace(/\.[^/.]+$/, "") + ".webp";
                    const webpFile = new File([blob], newName, { type: 'image/webp' });
                    resolve(webpFile);
                }, 'image/webp', quality);
            };
            img.onerror = () => {
                URL.revokeObjectURL(url);
                resolve(file);
            };
            img.src = url;
        });
    }

    inputFotos.addEventListener('change', async function(e) {
        if (!e.target.files.length) return;
        const incomingFiles = Array.from(e.target.files);
        const processed = [];

        if (photoCounter) photoCounter.textContent = 'Memproses berkas...';

        for (const file of incomingFiles) {
            let currentFile = file;
            const ext = (file.name || '').split('.').pop().toLowerCase();
            const mime = file.type || '';
            const isVideo = mime.startsWith('video/') || ['mp4', 'mov', 'quicktime', 'webm', 'mkv', 'avi', '3gp', 'm4v'].includes(ext);

            // Jika berkas adalah video, langsung simpan tanpa kompresi gambar raster
            if (isVideo) {
                processed.push(currentFile);
                continue;
            }

            if (['heic', 'heif'].includes(ext) || mime.includes('heic') || mime.includes('heif')) {
                try {
                    if (typeof heic2any === 'function') {
                        const convertedBlob = await heic2any({
                            blob: file,
                            toType: 'image/jpeg',
                            quality: 0.9
                        });
                        const blob = Array.isArray(convertedBlob) ? convertedBlob[0] : convertedBlob;
                        currentFile = new File([blob], file.name.replace(/\.(heic|heif)$/i, '.jpg'), { type: 'image/jpeg' });
                    }
                } catch (err) {
                    console.warn('heic2any failed, fallback to raw file:', err);
                }
            }

            // Kompresi client-side ke WebP untuk hemat kuota & akselerasi upload
            try {
                const compressed = await compressImageToWebp(currentFile);
                processed.push(compressed);
            } catch (err) {
                console.warn('WebP compression fallback:', err);
                processed.push(currentFile);
            }
        }

        selectedFiles = selectedFiles.concat(processed);
        updateFileInput();
        renderPreviews();
    });

    function updateFileInput() {
        const dt = new DataTransfer();
        selectedFiles.forEach(file => dt.items.add(file));
        inputFotos.files = dt.files;
        photoCounter.textContent = `${selectedFiles.length} Berkas dipilih`;
        if (selectedFiles.length > 0) {
            photoCounter.className = "text-xs font-black px-2.5 py-1 bg-emerald-50 text-emerald-700 rounded-lg";
        } else {
            photoCounter.className = "text-xs font-black px-2.5 py-1 bg-sky-50 text-sky-700 rounded-lg";
        }
    }

    function removePhoto(index) {
        selectedFiles.splice(index, 1);
        updateFileInput();
        renderPreviews();
    }

    function renderPreviews() {
        previewContainer.innerHTML = '';
        if (selectedFiles.length === 0) return;

        selectedFiles.forEach((file, index) => {
            const isVideo = file.type.startsWith('video/') || /\.(mp4|mov|quicktime|webm|mkv|avi|3gp|m4v)$/i.test(file.name);
            const card = document.createElement('div');
            card.className = "relative rounded-2xl overflow-hidden border-2 border-slate-200 aspect-square bg-slate-100 shadow-xs group";
            
            const isCover = index === 0;
            const coverBadge = isCover 
                ? `<div class="absolute top-2 left-2 px-2 py-0.5 bg-gradient-to-r from-emerald-500 to-teal-600 text-white text-[10px] font-black rounded-lg shadow-sm tracking-wider z-10">COVER UTAMA</div>`
                : '';
            const videoBadge = isVideo
                ? `<div class="absolute ${isCover ? 'top-8' : 'top-2'} left-2 px-2 py-0.5 bg-slate-900/80 text-white text-[10px] font-bold rounded-lg shadow-sm flex items-center gap-1 z-10"><i class="fa-solid fa-video text-rose-400 text-[9px]"></i> VIDEO</div>`
                : '';

            const objectUrl = URL.createObjectURL(file);
            const mediaHtml = isVideo
                ? `<video src="${objectUrl}" class="w-full h-full object-cover" muted playsinline></video>`
                : `<img src="${objectUrl}" alt="Preview" class="w-full h-full object-cover">`;

            card.innerHTML = `
                ${mediaHtml}
                ${coverBadge}
                ${videoBadge}
                <button type="button" onclick="removePhoto(${index})" class="absolute top-2 right-2 w-8 h-8 rounded-xl bg-slate-900/80 hover:bg-rose-600 text-white flex items-center justify-center transition active:scale-90 shadow-sm cursor-pointer z-10" title="Hapus berkas">
                    <i class="fa-solid fa-trash text-xs"></i>
                </button>
                <div class="absolute bottom-0 inset-x-0 p-1.5 bg-gradient-to-t from-slate-900/70 to-transparent text-[10px] text-white font-medium truncate px-2 z-10">
                    ${file.name}
                </div>
            `;
            previewContainer.appendChild(card);
        });
    }

    // ==========================================
    // 2. 1-TAP GPS REVERSE GEOCODING
    // ==========================================
    async function getLokasiGPS() {
        const statusDiv = document.getElementById('status_gps');
        const alamatInput = document.getElementById('alamat_produk');
        
        statusDiv.innerHTML = '<span class="text-sky-600 font-bold flex items-center gap-2"><i class="fa-solid fa-spinner fa-spin"></i> Mendeteksi koordinat & alamat jalan Anda...</span>';
        statusDiv.className = "px-3.5 py-2.5 bg-sky-50 border border-sky-200 rounded-xl text-xs font-medium text-slate-700 flex items-center gap-2";

        if (!navigator.geolocation) {
            statusDiv.innerHTML = '<span class="text-rose-600 font-bold flex items-center gap-2"><i class="fa-solid fa-triangle-exclamation"></i> GPS tidak didukung di browser ini.</span>';
            return;
        }

        navigator.geolocation.getCurrentPosition(
            async function(position) {
                const lat = position.coords.latitude;
                const lon = position.coords.longitude;

                document.getElementById('lat_produk').value = lat;
                document.getElementById('lon_produk').value = lon;

                try {
                    const res = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lon}`);
                    const data = await res.json();
                    if (data && data.display_name) {
                        alamatInput.value = data.display_name;
                    }
                } catch(err) {
                    console.log("Reverse geocoding error:", err);
                }

                statusDiv.innerHTML = `<span class="text-emerald-700 font-bold flex items-center gap-2"><i class="fa-solid fa-circle-check text-emerald-600"></i> Titik GPS Berhasil Terkunci (${lat.toFixed(4)}, ${lon.toFixed(4)})</span>`;
                statusDiv.className = "px-3.5 py-2.5 bg-emerald-50 border border-emerald-200 rounded-xl text-xs font-medium flex items-center gap-2";
            },
            function(err) {
                statusDiv.innerHTML = '<span class="text-amber-700 font-bold flex items-center gap-2"><i class="fa-solid fa-circle-exclamation text-amber-600"></i> Izin GPS ditolak. Koordinat default tersimpan, Anda tetap bisa mengisi alamat manual.</span>';
                statusDiv.className = "px-3.5 py-2.5 bg-amber-50 border border-amber-200 rounded-xl text-xs font-medium flex items-center gap-2";
            },
            { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
        );
    }

    // Indikator Submit Loading
    document.getElementById('formUploadBarang').addEventListener('submit', function() {
        const btn = document.getElementById('btnSubmitBarang');
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin text-base mr-2"></i> Mengunggah ke Cloud...';
        btn.classList.add('opacity-75', 'cursor-not-allowed');
    });

    // ==========================================
    // 3. LOGIKA KATEGORI DINAMIS (KOS, KENDARAAN, DLL)
    // ==========================================
    function handleKategoriChange() {
        const select = document.getElementById('kategori_id');
        if (!select) return;
        const selectedOption = select.options[select.selectedIndex];
        const katNama = (selectedOption ? (selectedOption.getAttribute('data-nama') || selectedOption.text) : '').toLowerCase();

        // Sembunyikan semua panel spesifikasi
        const panels = ['panel-kos-kamar', 'panel-kendaraan', 'panel-elektronik', 'panel-outdoor', 'panel-have-fun'];
        panels.forEach(id => {
            const el = document.getElementById(id);
            if (el) el.classList.add('hidden');
        });
        closeAllPanduan();

        const labelHarian = document.getElementById('label_harga_harian');
        const hintHarian = document.getElementById('hint_harga_harian');
        const inputHarian = document.getElementById('harga_sewa_harian');

        if (katNama.includes('kos') || katNama.includes('kamar')) {
            document.getElementById('panel-kos-kamar')?.classList.remove('hidden');
            if (labelHarian) labelHarian.innerHTML = 'Tarif Sewa / Malam (Harian) <span class="text-rose-500">*</span>';
            if (hintHarian) hintHarian.innerText = 'Tarif per malam untuk penyewa harian. Jika khusus bulanan, sistem akan menghitung estimasi otomatis.';
            toggleKosPricing();
        } else if (katNama.includes('kendaraan')) {
            document.getElementById('panel-kendaraan')?.classList.remove('hidden');
            if (labelHarian) labelHarian.innerHTML = 'Tarif Sewa Kendaraan / Hari <span class="text-rose-500">*</span>';
            if (hintHarian) hintHarian.innerText = 'Tarif sewa per 24 jam (sesuai opsi lepas kunci / supir).';
        } else if (katNama.includes('elektronik')) {
            document.getElementById('panel-elektronik')?.classList.remove('hidden');
            if (labelHarian) labelHarian.innerHTML = 'Tarif Sewa Gear / Hari <span class="text-rose-500">*</span>';
            if (hintHarian) hintHarian.innerText = 'Tarif sewa per hari untuk 1 paket kelengkapan unit.';
        } else if (katNama.includes('outdoor')) {
            document.getElementById('panel-outdoor')?.classList.remove('hidden');
            if (labelHarian) labelHarian.innerHTML = 'Tarif Sewa Alat Camping / Hari <span class="text-rose-500">*</span>';
            if (hintHarian) hintHarian.innerText = 'Tarif per hari (biasanya dihitung per 24 jam pendakian).';
        } else if (katNama.includes('have fun')) {
            document.getElementById('panel-have-fun')?.classList.remove('hidden');
            if (labelHarian) labelHarian.innerHTML = 'Tarif Sewa Paket Fun / Hari <span class="text-rose-500">*</span>';
            if (hintHarian) hintHarian.innerText = 'Tarif sewa per hari untuk paket pesta & hiburan.';
        } else {
            if (labelHarian) labelHarian.innerHTML = 'Tarif Sewa / Hari <span class="text-rose-500">*</span>';
            if (hintHarian) hintHarian.innerText = 'Tarif dasar harian untuk sewa barang.';
        }
    }

    function toggleKosPricing() {
        const radios = document.getElementsByName('spesifikasi[tipe_sewa]');
        let selected = 'keduanya';
        for (let r of radios) {
            if (r.checked) { selected = r.value; break; }
        }

        const wrapperBulanan = document.getElementById('wrapper_harga_bulanan');
        const inputBulanan = document.getElementById('input_harga_bulanan');
        const inputHarian = document.getElementById('harga_sewa_harian');

        if (selected === 'bulanan') {
            if (wrapperBulanan) wrapperBulanan.style.display = 'block';
            if (inputBulanan) inputBulanan.required = true;
            // Jika khusus bulanan dan harian kosong, isi estimasi per malam
            if (inputHarian && (!inputHarian.value || inputHarian.value == 0)) {
                if (inputBulanan && inputBulanan.value > 0) {
                    inputHarian.value = Math.round(inputBulanan.value / 30);
                }
            }
        } else if (selected === 'harian') {
            if (wrapperBulanan) wrapperBulanan.style.display = 'none';
            if (inputBulanan) { inputBulanan.required = false; }
        } else {
            if (wrapperBulanan) wrapperBulanan.style.display = 'block';
            if (inputBulanan) inputBulanan.required = true;
        }
    }

    // ==========================================
    // 4. PANDUAN SPESIFIKASI PER KATEGORI (MINIMALIS)
    // ==========================================
    function togglePanduanPanel(category, e) {
        if (e) {
            e.preventDefault();
            e.stopPropagation();
        }
        const targetId = 'popover-panduan-' + category;
        const target = document.getElementById(targetId);
        const allPopovers = document.querySelectorAll('.popover-panduan-spesifikasi');
        
        allPopovers.forEach(pop => {
            if (pop.id !== targetId) {
                pop.classList.add('hidden');
            }
        });

        if (target) {
            target.classList.toggle('hidden');
        }
    }

    function closeAllPanduan() {
        document.querySelectorAll('.popover-panduan-spesifikasi').forEach(pop => {
            pop.classList.add('hidden');
        });
    }

    document.addEventListener('click', function(e) {
        if (!e.target.closest('.popover-panduan-spesifikasi') && !e.target.closest('[data-btn-panduan]')) {
            closeAllPanduan();
        }
    });

    // Auto-trigger on page load (mendukung Old Value)
    document.addEventListener('DOMContentLoaded', function() {
        handleKategoriChange();
    });
</script>
@endpush