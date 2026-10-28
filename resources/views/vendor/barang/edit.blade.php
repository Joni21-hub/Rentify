@extends('layouts.vendor')

@section('content')
<div class="max-w-4xl mx-auto space-y-6 pb-20">

    <!-- Header & Breadcrumb -->
    <div class="flex items-center justify-between">
        <div>
            <a href="{{ route('vendor.barang.index') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-sky-600 hover:text-sky-700 transition mb-1">
                <i class="fa-solid fa-arrow-left"></i> Kembali ke Daftar Produk
            </a>
            <h1 class="text-xl sm:text-2xl font-black text-slate-800 tracking-tight">Edit Produk Sewa</h1>
            <p class="text-xs text-slate-500 font-medium">Perbarui informasi, spesifikasi kategori, tarif, dan foto barang.</p>
        </div>
        <span class="px-3 py-1.5 rounded-full text-xs font-black {{ $barang->status_barang == 'disetujui' ? 'bg-emerald-50 text-emerald-600 border border-emerald-200' : 'bg-amber-50 text-amber-600 border border-amber-200' }}">
            {{ ucfirst($barang->status_barang ?? 'Pending') }}
        </span>
    </div>

    <!-- Alert Validasi Eror -->
    @if ($errors->any())
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-700 text-xs space-y-1">
            <div class="font-black flex items-center gap-2 text-sm text-rose-800">
                <i class="fa-solid fa-triangle-exclamation"></i> Terjadi Kesalahan Input:
            </div>
            <ul class="list-disc pl-5 font-semibold">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('vendor.barang.update', $barang->id) }}" method="POST" enctype="multipart/form-data" id="formEditBarang">
        @csrf
        @method('PUT')

        @php
            $spek = $barang->spesifikasi ?? [];
        @endphp

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <!-- ============================================== -->
            <!-- KOLOM KIRI (2 SPAN): FOTO, INFORMASI & SPESIFIKASI -->
            <!-- ============================================== -->
            <div class="lg:col-span-2 space-y-6">

                <!-- 1. FOTO PRODUK -->
                <div class="bg-white p-5 sm:p-6 rounded-3xl border border-slate-200 shadow-sm space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <div>
                            <h2 class="text-base font-black text-slate-800 flex items-center gap-2">
                                <i class="fa-solid fa-camera text-sky-500"></i> Foto Produk
                            </h2>
                            <p class="text-xs text-slate-400 mt-0.5">Biarkan kosong jika tidak ingin mengganti foto cover.</p>
                        </div>
                    </div>

                    <!-- Foto Saat Ini -->
                    @if($barang->cover_photo_url)
                    <div class="p-3 bg-slate-50 rounded-2xl border border-slate-100 flex items-center gap-3">
                        <img src="{{ $barang->cover_photo_url }}" class="w-16 h-16 rounded-xl object-cover border border-slate-200" alt="Cover Saat Ini" onerror="this.onerror=null; this.src='{{ asset('storage/placeholders/placeholder-barang.png') }}';">
                        <div>
                            <span class="text-xs font-black text-slate-800 block">Foto Cover Saat Ini</span>
                            <span class="text-[11px] text-slate-400">Pilih foto baru di bawah jika ingin memperbarui.</span>
                        </div>
                    </div>
                    @endif

                    <!-- Hidden Input -->
                    <input type="file" name="fotos[]" id="fotos" multiple accept="image/*,video/*,.heic,.heif,.avif,.webp,.mp4,.mov,.mkv,.webm" class="hidden">

                    <label for="fotos" class="cursor-pointer p-4 rounded-2xl border-2 border-dashed border-sky-400/80 bg-sky-50/60 hover:bg-sky-100/70 transition flex items-center justify-center gap-3 text-sky-700">
                        <div class="w-10 h-10 rounded-xl bg-sky-500 text-white flex items-center justify-center text-base shadow-sm">
                            <i class="fa-solid fa-cloud-arrow-up"></i>
                        </div>
                        <div class="text-left">
                            <span class="block text-xs font-black">Unggah Foto / Video Baru (Opsional)</span>
                            <span class="block text-[11px] text-sky-600 font-medium">Bisa pilih foto & video baru</span>
                        </div>
                    </label>

                    <!-- Counter & Pratinjau Foto Baru -->
                    <div id="newPhotoSection" class="hidden mt-3 pt-3 border-t border-slate-100">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-bold text-slate-700">Foto Baru yang Dipilih:</span>
                            <span id="photoCounter" class="text-xs font-black px-2.5 py-1 bg-emerald-50 text-emerald-700 rounded-lg">0 Foto dipilih</span>
                        </div>
                        <div id="preview-container" class="grid grid-cols-2 sm:grid-cols-4 gap-3"></div>
                    </div>
                </div>

                <!-- 2. INFORMASI DASAR -->
                <div class="bg-white p-5 sm:p-6 rounded-3xl border border-slate-200 shadow-sm space-y-4">
                    <h2 class="text-base font-black text-slate-800 border-b border-slate-100 pb-3 flex items-center gap-2">
                        <i class="fa-solid fa-circle-info text-sky-500"></i> Informasi Produk
                    </h2>

                    <!-- Nama Barang -->
                    <div>
                        <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-1.5">
                            Nama Barang / Unit <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="nama" value="{{ (string) old('nama', $barang->nama) }}" required class="w-full px-4 py-3 rounded-2xl border border-slate-200 text-slate-800 text-sm font-semibold focus:outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-100 transition">
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
                                    <option value="{{ $k->id }}" data-nama="{{ strtolower($k->nama) }}" {{ old('kategori_id', $barang->kategori_id) == $k->id ? 'selected' : '' }}>
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
                                <option value="Sangat Baik" {{ old('kondisi', $barang->kondisi) == 'Sangat Baik' ? 'selected' : '' }}>Sangat Baik (Mulus / Seperti Baru)</option>
                                <option value="Baik" {{ old('kondisi', $barang->kondisi) == 'Baik' ? 'selected' : '' }}>Baik (Normal & Berfungsi Penuh)</option>
                                <option value="Cukup" {{ old('kondisi', $barang->kondisi) == 'Cukup' ? 'selected' : '' }}>Cukup (Ada Minus Lecet Wajar)</option>
                            </select>
                        </div>
                    </div>

                    <!-- Deskripsi Barang -->
                    <div>
                        <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-1.5">
                            Deskripsi & Kelengkapan Unit <span class="text-rose-500">*</span>
                        </label>
                        <textarea name="deskripsi" rows="4" required class="w-full px-4 py-3 rounded-2xl border border-slate-200 text-slate-800 text-sm font-medium focus:outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-100 transition leading-relaxed">{{ (string) old('deskripsi', $barang->deskripsi) }}</textarea>
                    </div>
                </div>

                <!-- ============================================== -->
                <!-- 2.5 SPESIFIKASI KHUSUS KATEGORI (DINAMIS) -->
                <!-- ============================================== -->

                <!-- PANEL 1: KOS / KAMAR -->
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
                        @php $tipeSewa = old('spesifikasi.tipe_sewa', $spek['tipe_sewa'] ?? 'keduanya'); @endphp
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5">
                            <label class="cursor-pointer">
                                <input type="radio" name="spesifikasi[tipe_sewa]" value="keduanya" {{ $tipeSewa == 'keduanya' ? 'checked' : '' }} onchange="toggleKosPricing()" class="peer sr-only">
                                <div class="p-3 rounded-2xl border-2 border-slate-200 peer-checked:border-sky-500 peer-checked:bg-sky-50/50 text-center transition">
                                    <div class="text-xs font-black text-slate-800"><i class="fa-solid fa-calendar-check text-sky-500 mr-1"></i> Harian & Bulanan</div>
                                    <div class="text-[10px] text-slate-400 mt-0.5">Bisa harian dan bulanan</div>
                                </div>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" name="spesifikasi[tipe_sewa]" value="bulanan" {{ $tipeSewa == 'bulanan' ? 'checked' : '' }} onchange="toggleKosPricing()" class="peer sr-only">
                                <div class="p-3 rounded-2xl border-2 border-slate-200 peer-checked:border-sky-500 peer-checked:bg-sky-50/50 text-center transition">
                                    <div class="text-xs font-black text-slate-800"><i class="fa-solid fa-calendar-days text-sky-500 mr-1"></i> Khusus Bulanan</div>
                                    <div class="text-[10px] text-slate-400 mt-0.5">Sistem kos per bulan</div>
                                </div>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" name="spesifikasi[tipe_sewa]" value="harian" {{ $tipeSewa == 'harian' ? 'checked' : '' }} onchange="toggleKosPricing()" class="peer sr-only">
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
                            <input type="number" name="spesifikasi[harga_bulanan]" id="input_harga_bulanan" value="{{ (string) old('spesifikasi.harga_bulanan', $spek['harga_bulanan'] ?? '') }}" min="0" class="w-full pl-11 pr-4 py-3 rounded-2xl border border-slate-200 bg-white text-slate-800 text-sm font-black focus:outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-100 transition">
                        </div>
                    </div>

                    <!-- Tipe Kos, Kamar Mandi, Ukuran Kamar -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        @php
                            $tipeKos = old('spesifikasi.tipe_kos', $spek['tipe_kos'] ?? 'Campur');
                            $tipeKM = old('spesifikasi.tipe_kamar_mandi', $spek['tipe_kamar_mandi'] ?? 'Dalam');
                        @endphp
                        <div>
                            <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-1.5">Jenis Kos</label>
                            <select name="spesifikasi[tipe_kos]" class="w-full px-3.5 py-2.5 rounded-2xl border border-slate-200 text-slate-800 text-xs font-semibold focus:outline-none focus:border-sky-500 bg-white">
                                <option value="Campur" {{ $tipeKos == 'Campur' ? 'selected' : '' }}>Kos Campur</option>
                                <option value="Putri" {{ $tipeKos == 'Putri' ? 'selected' : '' }}>Khusus Putri</option>
                                <option value="Putra" {{ $tipeKos == 'Putra' ? 'selected' : '' }}>Khusus Putra</option>
                                <option value="Pasutri" {{ $tipeKos == 'Pasutri' ? 'selected' : '' }}>Boleh Pasutri</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-1.5">Kamar Mandi</label>
                            <select name="spesifikasi[tipe_kamar_mandi]" class="w-full px-3.5 py-2.5 rounded-2xl border border-slate-200 text-slate-800 text-xs font-semibold focus:outline-none focus:border-sky-500 bg-white">
                                <option value="Dalam" {{ $tipeKM == 'Dalam' ? 'selected' : '' }}>Kamar Mandi Dalam</option>
                                <option value="Luar" {{ $tipeKM == 'Luar' ? 'selected' : '' }}>Kamar Mandi Luar</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-1.5">Ukuran Kamar</label>
                            <input type="text" name="spesifikasi[ukuran_kamar]" value="{{ (string) old('spesifikasi.ukuran_kamar', $spek['ukuran_kamar'] ?? '') }}" class="w-full px-3.5 py-2.5 rounded-2xl border border-slate-200 text-slate-800 text-xs font-semibold focus:outline-none focus:border-sky-500">
                        </div>
                    </div>

                    <!-- Fasilitas Kamar & Bersama -->
                    <div>
                        <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-2">
                            Fasilitas Kamar & Kos
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
                            $savedFasKos = (array)old('spesifikasi.fasilitas', $spek['fasilitas'] ?? []);
                        @endphp
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                            @foreach($fasilitasKosList as [$fas, $icon])
                            <label class="cursor-pointer relative flex items-center p-2.5 rounded-xl border border-slate-200 hover:border-sky-300 has-[:checked]:border-sky-500 has-[:checked]:bg-sky-50/70 transition">
                                <input type="checkbox" name="spesifikasi[fasilitas][]" value="{{ $fas }}" {{ in_array($fas, $savedFasKos) ? 'checked' : '' }} class="mr-2 rounded text-sky-500 focus:ring-sky-400">
                                <i class="fa-solid {{ $icon }} text-sky-500 mr-2 text-xs"></i>
                                <span class="text-xs font-semibold text-slate-700">{{ $fas }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- PANEL 2: KENDARAAN -->
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

                    @php
                        $tipeKendaraan = old('spesifikasi.tipe_kendaraan', $spek['tipe_kendaraan'] ?? 'Mobil');
                        $transmisi = old('spesifikasi.transmisi', $spek['transmisi'] ?? 'Automatic');
                        $bahanBakar = old('spesifikasi.bahan_bakar', $spek['bahan_bakar'] ?? 'Bensin');
                        $kapasitas = old('spesifikasi.kapasitas_penumpang', $spek['kapasitas_penumpang'] ?? '4-5 Kursi');
                        $opsiDriver = old('spesifikasi.opsi_driver', $spek['opsi_driver'] ?? 'Lepas Kunci Saja');
                    @endphp

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div>
                            <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-1.5">Jenis Kendaraan</label>
                            <select name="spesifikasi[tipe_kendaraan]" class="w-full px-3.5 py-2.5 rounded-2xl border border-slate-200 text-slate-800 text-xs font-semibold focus:outline-none focus:border-cyan-500 bg-white">
                                <option value="Mobil" {{ $tipeKendaraan == 'Mobil' ? 'selected' : '' }}>Mobil Penumpang</option>
                                <option value="Motor" {{ $tipeKendaraan == 'Motor' ? 'selected' : '' }}>Sepeda Motor</option>
                                <option value="Pickup / Box" {{ $tipeKendaraan == 'Pickup / Box' ? 'selected' : '' }}>Mobil Pickup / Box Niaga</option>
                                <option value="Sepeda" {{ $tipeKendaraan == 'Sepeda' ? 'selected' : '' }}>Sepeda Gowes / Listrik</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-1.5">Transmisi</label>
                            <select name="spesifikasi[transmisi]" class="w-full px-3.5 py-2.5 rounded-2xl border border-slate-200 text-slate-800 text-xs font-semibold focus:outline-none focus:border-cyan-500 bg-white">
                                <option value="Automatic" {{ $transmisi == 'Automatic' ? 'selected' : '' }}>Automatic (Matic)</option>
                                <option value="Manual" {{ $transmisi == 'Manual' ? 'selected' : '' }}>Manual</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-1.5">Bahan Bakar</label>
                            <select name="spesifikasi[bahan_bakar]" class="w-full px-3.5 py-2.5 rounded-2xl border border-slate-200 text-slate-800 text-xs font-semibold focus:outline-none focus:border-cyan-500 bg-white">
                                <option value="Bensin" {{ $bahanBakar == 'Bensin' ? 'selected' : '' }}>Bensin (Pertalite / Pertamax)</option>
                                <option value="Solar / Diesel" {{ $bahanBakar == 'Solar / Diesel' ? 'selected' : '' }}>Solar / Diesel</option>
                                <option value="Listrik (EV)" {{ $bahanBakar == 'Listrik (EV)' ? 'selected' : '' }}>Listrik (EV)</option>
                                <option value="Hybrid" {{ $bahanBakar == 'Hybrid' ? 'selected' : '' }}>Hybrid</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div>
                            <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-1.5">Kapasitas Kursi</label>
                            <select name="spesifikasi[kapasitas_penumpang]" class="w-full px-3.5 py-2.5 rounded-2xl border border-slate-200 text-slate-800 text-xs font-semibold focus:outline-none focus:border-cyan-500 bg-white">
                                <option value="2 Orang" {{ $kapasitas == '2 Orang' ? 'selected' : '' }}>2 Orang (Motor/City Car)</option>
                                <option value="4-5 Kursi" {{ $kapasitas == '4-5 Kursi' ? 'selected' : '' }}>4 - 5 Kursi (Sedan/Hatchback)</option>
                                <option value="7-8 Kursi" {{ $kapasitas == '7-8 Kursi' ? 'selected' : '' }}>7 - 8 Kursi (MPV/SUV)</option>
                                <option value="12+ Kursi" {{ $kapasitas == '12+ Kursi' ? 'selected' : '' }}>12+ Kursi (HiAce / Elf)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-1.5">Opsi Layanan Driver</label>
                            <select name="spesifikasi[opsi_driver]" class="w-full px-3.5 py-2.5 rounded-2xl border border-slate-200 text-slate-800 text-xs font-semibold focus:outline-none focus:border-cyan-500 bg-white">
                                <option value="Lepas Kunci Saja" {{ $opsiDriver == 'Lepas Kunci Saja' ? 'selected' : '' }}>Lepas Kunci Saja</option>
                                <option value="Dengan Driver / Supir" {{ $opsiDriver == 'Dengan Driver / Supir' ? 'selected' : '' }}>Dengan Driver / Supir</option>
                                <option value="Bisa Keduanya (Lepas Kunci / Driver)" {{ $opsiDriver == 'Bisa Keduanya (Lepas Kunci / Driver)' ? 'selected' : '' }}>Bisa Lepas Kunci / Dengan Driver</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-1.5">Tahun Kendaraan</label>
                            <input type="number" name="spesifikasi[tahun_kendaraan]" value="{{ (string) old('spesifikasi.tahun_kendaraan', $spek['tahun_kendaraan'] ?? '') }}" class="w-full px-3.5 py-2.5 rounded-2xl border border-slate-200 text-slate-800 text-xs font-semibold focus:outline-none focus:border-cyan-500">
                        </div>
                    </div>
                </div>

                <!-- PANEL 3: ELEKTRONIK -->
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
                            <input type="text" name="spesifikasi[merek]" value="{{ (string) old('spesifikasi.merek', $spek['merek'] ?? '') }}" class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-slate-800 text-xs font-semibold focus:outline-none focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-1.5">Kondisi Fungsi & Lensa</label>
                            <input type="text" name="spesifikasi[kondisi_detail]" value="{{ (string) old('spesifikasi.kondisi_detail', $spek['kondisi_detail'] ?? '') }}" class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-slate-800 text-xs font-semibold focus:outline-none focus:border-blue-500">
                        </div>
                    </div>
                </div>

                <!-- PANEL 4: OUTDOOR -->
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
                            <input type="text" name="spesifikasi[kapasitas_outdoor]" value="{{ (string) old('spesifikasi.kapasitas_outdoor', $spek['kapasitas_outdoor'] ?? '') }}" class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-slate-800 text-xs font-semibold focus:outline-none focus:border-teal-500">
                        </div>
                        <div>
                            <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-1.5">Ketahanan Cuaca & Material</label>
                            <input type="text" name="spesifikasi[fitur_outdoor]" value="{{ (string) old('spesifikasi.fitur_outdoor', $spek['fitur_outdoor'] ?? '') }}" class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-slate-800 text-xs font-semibold focus:outline-none focus:border-teal-500">
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
                                    <div class="max-h-[440px] overflow-y-auto space-y-3.5 text-xs pr-1 scrollbar-thin">
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
                            <input type="text" name="spesifikasi[jenis_hiburan]" value="{{ (string) old('spesifikasi.jenis_hiburan', $spek['jenis_hiburan'] ?? '') }}" class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-slate-800 text-xs font-semibold focus:outline-none focus:border-indigo-500">
                        </div>
                        <div>
                            <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-1.5">Kelengkapan Game / Aksesoris</label>
                            <input type="text" name="spesifikasi[kelengkapan_have_fun]" value="{{ (string) old('spesifikasi.kelengkapan_have_fun', $spek['kelengkapan_have_fun'] ?? '') }}" class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-slate-800 text-xs font-semibold focus:outline-none focus:border-indigo-500">
                        </div>
                    </div>
                </div>

                <!-- 3. LOKASI GUDANG / PRODUK -->
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
                                <input type="radio" name="is_delivery_supported" value="1" {{ old('is_delivery_supported', $barang->is_delivery_supported) == '1' ? 'checked' : '' }} class="peer sr-only">
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
                                <input type="radio" name="is_delivery_supported" value="0" {{ old('is_delivery_supported', $barang->is_delivery_supported) == '0' ? 'checked' : '' }} class="peer sr-only">
                                <div class="p-3.5 rounded-2xl border-2 border-slate-200 peer-checked:border-rose-400 peer-checked:bg-rose-50/50 flex items-center gap-3 transition">
                                    <div class="w-9 h-9 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center text-sm font-bold">
                                        <i class="fa-solid fa-person-walking"></i>
                                    </div>
                                    <div>
                                        <p class="text-xs font-black text-slate-800">Ambil Mandiri</p>
                                        <p class="text-[11px] text-slate-500">Penyewa datang ke toko</p>
                                    </div>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Alamat Input -->
                    <div>
                        <input type="hidden" name="latitude" id="lat_produk" value="{{ (string) old('latitude', $barang->latitude) }}">
                        <input type="hidden" name="longitude" id="lon_produk" value="{{ (string) old('longitude', $barang->longitude) }}">

                        <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-1.5">
                            Alamat Lengkap Toko / Gudang Penjemputan <span class="text-rose-500">*</span>
                        </label>
                        <textarea name="alamat" id="alamat_produk" rows="2" required class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-slate-800 text-xs font-medium focus:outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-100 transition">{{ (string) old('alamat', $barang->alamat) }}</textarea>
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
                            <input type="number" name="harga_sewa_harian" id="harga_sewa_harian" value="{{ (string) old('harga_sewa_harian', (int)$barang->harga_sewa_harian) }}" required min="0" class="w-full pl-11 pr-4 py-3 rounded-2xl border border-slate-200 text-slate-800 text-sm font-black focus:outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-100 transition">
                        </div>
                        <p id="hint_harga_harian" class="text-[10px] text-slate-400 mt-1">Tarif dasar harian untuk sewa barang.</p>
                    </div>

                    <!-- Stok Unit Fisik -->
                    <div>
                        <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-1.5">
                            Jumlah Stok Unit <span class="text-rose-500">*</span>
                        </label>
                        <input type="number" name="stok_total" value="{{ (string) old('stok_total', $barang->stok_total) }}" required min="1" class="w-full px-4 py-3 rounded-2xl border border-slate-200 text-slate-800 text-sm font-black focus:outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-100 transition">
                    </div>

                    <!-- Deposit Jaminan -->
                    <div>
                        <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-1.5">
                            Deposit Jaminan (Rp) <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-xs font-bold text-slate-400">Rp</span>
                            <input type="number" name="deposit" value="{{ (string) old('deposit', (int)$barang->deposit) }}" required min="0" class="w-full pl-11 pr-4 py-3 rounded-2xl border border-slate-200 text-slate-800 text-sm font-semibold focus:outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-100 transition">
                        </div>
                    </div>

                    <!-- Denda Keterlambatan -->
                    <div>
                        <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-1.5">
                            Denda Telat / Hari <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-xs font-bold text-slate-400">Rp</span>
                            <input type="number" name="denda_per_hari" value="{{ (string) old('denda_per_hari', (int)$barang->denda_per_hari) }}" required min="0" class="w-full pl-11 pr-4 py-3 rounded-2xl border border-slate-200 text-slate-800 text-sm font-semibold focus:outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-100 transition">
                        </div>
                    </div>
                </div>

                <!-- Tombol Simpan -->
                <div class="bg-white p-5 rounded-3xl border border-slate-200 shadow-sm space-y-3">
                    <button type="submit" id="btnSubmitBarang" class="w-full py-4 px-5 bg-gradient-to-r from-sky-500 to-blue-600 hover:from-sky-600 hover:to-blue-700 active:scale-98 text-white font-black text-sm rounded-2xl shadow-lg shadow-sky-500/25 flex items-center justify-center gap-2 transition cursor-pointer">
                        <i class="fa-solid fa-floppy-disk text-base"></i>
                        <span>SIMPAN PERUBAHAN PRODUK</span>
                    </button>
                </div>

            </div>

        </div>
    </form>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/heic2any@0.0.4/dist/heic2any.min.js"></script>
<script>
    // Preview & Konversi HEIC + Kompresi WebP foto baru di edit
    const inputFotos = document.getElementById('fotos');
    const newPhotoSection = document.getElementById('newPhotoSection');
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

    if (inputFotos) {
        inputFotos.addEventListener('change', async function(e) {
            if (!e.target.files.length) return;
            const incomingFiles = Array.from(e.target.files);
            const processed = [];

            if (newPhotoSection) newPhotoSection.classList.remove('hidden');
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
                        console.warn('heic2any failed:', err);
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
            updateFileInputEdit();
            renderPreviewsEdit();
        });
    }

    function updateFileInputEdit() {
        if (!inputFotos) return;
        const dt = new DataTransfer();
        selectedFiles.forEach(file => dt.items.add(file));
        inputFotos.files = dt.files;
        if (photoCounter) photoCounter.textContent = `${selectedFiles.length} Berkas dipilih`;
        if (newPhotoSection) {
            if (selectedFiles.length > 0) {
                newPhotoSection.classList.remove('hidden');
            } else {
                newPhotoSection.classList.add('hidden');
            }
        }
    }

    function removePhotoEdit(index) {
        selectedFiles.splice(index, 1);
        updateFileInputEdit();
        renderPreviewsEdit();
    }

    function renderPreviewsEdit() {
        if (!previewContainer) return;
        previewContainer.innerHTML = '';
        selectedFiles.forEach((file, index) => {
            const isVideo = file.type.startsWith('video/') || /\.(mp4|mov|quicktime|webm|mkv|avi|3gp|m4v)$/i.test(file.name);
            const card = document.createElement('div');
            card.className = "relative rounded-2xl overflow-hidden border border-slate-200 aspect-square bg-slate-100 shadow-xs";

            const videoBadge = isVideo
                ? `<div class="absolute top-1.5 left-1.5 px-1.5 py-0.5 bg-slate-900/80 text-white text-[9px] font-bold rounded flex items-center gap-1 z-10"><i class="fa-solid fa-video text-rose-400 text-[8px]"></i> Video</div>`
                : '';

            const objectUrl = URL.createObjectURL(file);
            const mediaHtml = isVideo
                ? `<video src="${objectUrl}" class="w-full h-full object-cover" muted playsinline></video>`
                : `<img src="${objectUrl}" alt="Preview" class="w-full h-full object-cover">`;

            card.innerHTML = `
                ${mediaHtml}
                ${videoBadge}
                <button type="button" onclick="removePhotoEdit(${index})" class="absolute top-1.5 right-1.5 w-6 h-6 rounded-lg bg-slate-900/80 hover:bg-rose-600 text-white flex items-center justify-center transition active:scale-90 text-[10px] cursor-pointer z-10" title="Hapus berkas">
                    <i class="fa-solid fa-trash"></i>
                </button>
                <div class="absolute bottom-0 inset-x-0 p-1 bg-gradient-to-t from-slate-900/80 to-transparent text-[9px] text-white font-medium truncate px-1.5 z-10">
                    ${file.name}
                </div>
            `;
            previewContainer.appendChild(card);
        });
    }
    function handleKategoriChange() {
        const select = document.getElementById('kategori_id');
        if (!select) return;
        const selectedOption = select.options[select.selectedIndex];
        const katNama = (selectedOption ? (selectedOption.getAttribute('data-nama') || selectedOption.text) : '').toLowerCase();

        const panels = ['panel-kos-kamar', 'panel-kendaraan', 'panel-elektronik', 'panel-outdoor', 'panel-have-fun'];
        panels.forEach(id => {
            const el = document.getElementById(id);
            if (el) el.classList.add('hidden');
        });
        closeAllPanduan();

        const labelHarian = document.getElementById('label_harga_harian');
        const hintHarian = document.getElementById('hint_harga_harian');

        if (katNama.includes('kos') || katNama.includes('kamar')) {
            document.getElementById('panel-kos-kamar')?.classList.remove('hidden');
            if (labelHarian) labelHarian.innerHTML = 'Tarif Sewa / Malam (Harian) <span class="text-rose-500">*</span>';
            if (hintHarian) hintHarian.innerText = 'Tarif per malam untuk penyewa harian.';
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
            if (hintHarian) hintHarian.innerText = 'Tarif per hari pendakian/camping.';
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

        if (selected === 'bulanan') {
            if (wrapperBulanan) wrapperBulanan.style.display = 'block';
            if (inputBulanan) inputBulanan.required = true;
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

    document.addEventListener('DOMContentLoaded', function() {
        handleKategoriChange();
    });
</script>
@endpush
