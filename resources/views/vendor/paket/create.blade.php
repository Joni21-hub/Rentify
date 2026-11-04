@extends('layouts.vendor')

@section('title', 'Buat Paket Sewa Baru — Vendor Rentify')

@section('content')
<div class="px-4 sm:px-6 lg:px-8 py-6 max-w-5xl mx-auto space-y-6">

    <!-- Header & Navigation -->
    <div class="flex items-center gap-3">
        <a href="{{ route('vendor.barang.index') }}" class="w-10 h-10 rounded-2xl bg-white border border-slate-200 text-slate-600 hover:text-sky-600 hover:border-sky-300 flex items-center justify-center shadow-xs transition active:scale-95">
            <i class="fa-solid fa-arrow-left text-sm"></i>
        </a>
        <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-indigo-50 text-indigo-600 border border-indigo-200/60">
                    <i class="fa-solid fa-boxes-stacked mr-1"></i> Paket Sewa
                </span>
            </div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-800 tracking-tight mt-0.5">Buat Paket Sewa Baru</h1>
            <p class="text-xs sm:text-sm text-slate-500 font-medium">Gabungkan barang yang sudah ada menjadi satu paket tanpa perlu upload ulang atau duplikasi stok.</p>
        </div>
    </div>

    <!-- Info Box: Cara Kerja Sinkronisasi Stok Otomatis -->
    <div class="p-4 sm:p-5 bg-gradient-to-r from-sky-50 via-indigo-50/50 to-blue-50 border border-sky-200/80 rounded-3xl flex items-start gap-3.5 shadow-xs">
        <div class="w-10 h-10 rounded-2xl bg-sky-500 text-white flex items-center justify-center shrink-0 shadow-sm mt-0.5">
            <i class="fa-solid fa-link text-base"></i>
        </div>
        <div class="text-xs space-y-1">
            <h4 class="font-black text-slate-800">Sinkronisasi Stok Inventaris Terpadu</h4>
            <p class="text-slate-600 leading-relaxed font-medium">
                Setiap barang di dalam paket tetap mengacu pada stok fisik aslinya. Jika paket disewa, ketersediaan barang satuan otomatis berkurang. Sebaliknya, jika barang satuan disewa, ketersediaan paket juga langsung menyesuaikan secara otomatis.
            </p>
        </div>
    </div>

    <!-- Error Alerts -->
    @if($errors->any())
        <div class="p-4 sm:p-5 bg-rose-50 border border-rose-200 text-rose-700 rounded-3xl space-y-2 shadow-xs">
            <div class="flex items-center gap-2 font-bold text-sm">
                <i class="fa-solid fa-triangle-exclamation text-rose-500"></i>
                <span>Mohon periksa formulir berikut:</span>
            </div>
            <ul class="list-disc list-inside text-xs space-y-1 font-medium text-rose-600">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('vendor.paket.store') }}" method="POST" enctype="multipart/form-data" id="formUploadPaket" class="space-y-6">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- KOLOM KIRI (2 SPAN): MEDIA, KOMPOSISI BARANG & INFORMASI PAKET -->
            <div class="lg:col-span-2 space-y-6">
                
                <!-- 1. MEDIA PAKET (FOTO / VIDEO) — MATCHING HALAMAN UPLOAD PRODUK -->
                <div class="bg-white p-5 sm:p-6 rounded-3xl border border-slate-200 shadow-sm space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <div>
                            <h2 class="text-base font-black text-slate-800 flex items-center gap-2">
                                <i class="fa-solid fa-photo-film text-sky-500"></i> Media Paket (Foto / Video)
                            </h2>
                            <p class="text-xs text-slate-400 mt-0.5">
                                Bisa pilih lebih dari 1 foto/video (berkas pertama jadi Cover). Foto dikompresi otomatis ke WebP/AVIF HD.
                            </p>
                        </div>
                        <span id="photoCounter" class="text-xs font-black px-2.5 py-1 bg-sky-50 text-sky-700 rounded-lg">
                            0 Media dipilih
                        </span>
                    </div>

                    <!-- Hidden Input -->
                    <input type="file" name="fotos[]" id="fotos" multiple accept="image/*,video/*,.heic,.heif,.avif,.webp,.mp4,.mov,.mkv,.webm" class="hidden">

                    <!-- Mobile-First Big Touch Area -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-2">
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
                                Gunakan foto jelas yang menampilkan keseluruhan isi bundling paket sewa Anda!
                            </p>
                        </div>
                    </div>

                    <p class="text-[10px] text-slate-400 italic">
                        * Opsional: Jika tidak diunggah, otomatis memakai foto cover dan galeri barang komponen penyusun paket.
                    </p>

                    <!-- Live Previews Grid -->
                    <div id="newPhotoSection" class="hidden space-y-2 pt-2 border-t border-slate-100">
                        <span class="text-[11px] font-black text-slate-700 block">Pratinjau Media Paket:</span>
                        <div id="preview-container" class="grid grid-cols-2 sm:grid-cols-3 gap-3"></div>
                    </div>
                </div>

                <!-- 2. PILIH KOMPOSISI BARANG -->
                <div class="bg-white p-5 sm:p-6 rounded-3xl border border-slate-200 shadow-sm space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-3 border-b border-slate-100 gap-2">
                        <div>
                            <h2 class="text-base font-black text-slate-800 flex items-center gap-2">
                                <i class="fa-solid fa-layer-group text-sky-500"></i> Komposisi Barang Paket <span class="text-rose-500">*</span>
                            </h2>
                            <p class="text-xs text-slate-400 mt-0.5">Centang barang milik toko Anda yang ingin digabungkan ke dalam paket.</p>
                        </div>
                        <div id="bundleStockBadge" class="self-start sm:self-auto px-3 py-1.5 rounded-xl bg-slate-100 text-slate-700 text-xs font-black">
                            0 barang dipilih
                        </div>
                    </div>

                    @if($myProducts->isEmpty())
                        <div class="p-6 text-center text-slate-400 bg-slate-50 rounded-2xl border border-dashed border-slate-200">
                            <i class="fa-solid fa-box-open text-3xl mb-2 text-slate-300"></i>
                            <p class="text-xs font-semibold">Toko Anda belum memiliki barang satuan.</p>
                            <a href="{{ route('vendor.barang.create') }}" class="inline-block mt-3 px-4 py-2 bg-sky-500 text-white rounded-xl text-xs font-bold shadow-xs">
                                Tambah Barang Satuan Dulu
                            </a>
                        </div>
                    @else
                        <!-- List Barang Vendor -->
                        <div class="space-y-2.5 max-h-[380px] overflow-y-auto pr-1">
                            @foreach($myProducts as $index => $prod)
                                @php
                                    $imgUrl = $prod->cover_photo_url;
                                @endphp
                                <div class="product-item-card p-3 rounded-2xl border border-slate-200 hover:border-sky-300 bg-white transition flex items-center gap-3">
                                    <input type="checkbox" 
                                           id="item_check_{{ $prod->id }}" 
                                           class="w-4 h-4 text-sky-600 rounded border-slate-300 focus:ring-sky-500 shrink-0 item-checkbox"
                                           data-id="{{ $prod->id }}"
                                           data-stock="{{ $prod->stok_total }}"
                                           data-price="{{ $prod->harga_sewa_harian }}"
                                           data-name="{{ $prod->nama }}"
                                           data-deposit="{{ $prod->deposit }}"
                                           data-denda="{{ $prod->denda_per_hari }}">

                                    <div class="w-12 h-12 rounded-xl bg-slate-100 overflow-hidden shrink-0 border border-slate-200">
                                        @if($imgUrl)
                                            <img src="{{ $imgUrl }}" alt="{{ $prod->nama }}" class="w-full h-full object-cover">
                                        @else
                                            <div class="w-full h-full flex items-center justify-center text-slate-300">
                                                <i class="fa-solid fa-image text-xs"></i>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="flex-1 min-w-0">
                                        <label for="item_check_{{ $prod->id }}" class="block text-xs font-black text-slate-800 truncate cursor-pointer">
                                            {{ $prod->nama }}
                                        </label>
                                        <div class="flex items-center gap-2 mt-0.5 text-[11px] text-slate-400 font-medium">
                                            <span>Stok fisik: <strong class="text-slate-700">{{ $prod->stok_total }} unit</strong></span>
                                            <span>•</span>
                                            <span class="text-sky-600 font-bold">Rp {{ number_format($prod->harga_sewa_harian, 0, ',', '.') }}/hari</span>
                                        </div>
                                    </div>

                                    <!-- Input Jumlah (Quantity in 1 Bundle) -->
                                    <div class="qty-container hidden items-center gap-1.5 shrink-0" id="qty_box_{{ $prod->id }}">
                                        <span class="text-[10px] font-bold text-slate-500">Qty:</span>
                                        <input type="number" 
                                               min="1" 
                                               max="{{ max(1, $prod->stok_total) }}" 
                                               value="1" 
                                               class="w-16 px-2 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs font-bold text-center focus:bg-white focus:border-sky-500 item-qty-input"
                                               data-id="{{ $prod->id }}"
                                               data-stock="{{ $prod->stok_total }}">
                                        <span class="text-[10px] text-slate-400 font-medium">unit</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Live Stock & Capacity Indicator -->
                        <div id="bundleEstimateCard" class="hidden p-3.5 bg-indigo-50/70 border border-indigo-200 rounded-2xl">
                            <div class="flex items-center justify-between text-xs">
                                <span class="font-bold text-indigo-900">Perkiraan Kapasitas Paket:</span>
                                <span class="font-black text-indigo-700 text-sm" id="bundleEstimateText">0 paket</span>
                            </div>
                            <p class="text-[11px] text-indigo-600 mt-1 leading-snug">
                                Kapasitas paket otomatis terikat dengan stok fisik barang penyusun terendah.
                            </p>
                        </div>
                    @endif

                    <!-- Hidden inputs container for form submission -->
                    <div id="hiddenItemsInputs"></div>
                </div>

                <!-- 2. DETAIL INFORMASI PAKET -->
                <div class="bg-white p-5 sm:p-6 rounded-3xl border border-slate-200 shadow-sm space-y-4">
                    <h2 class="text-base font-black text-slate-800 flex items-center gap-2 pb-3 border-b border-slate-100">
                        <i class="fa-solid fa-tag text-sky-500"></i> Detail Paket Sewa
                    </h2>

                    <!-- Nama Paket -->
                    <div>
                        <label class="block text-xs font-black text-slate-700 mb-1.5">
                            Nama Paket Sewa <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="nama" value="{{ old('nama') }}" required placeholder="Contoh: Paket Camping 2 Orang (Tenda + 2 Kursi)" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 placeholder-slate-400 focus:bg-white focus:border-sky-500 shadow-xs transition">
                    </div>

                    <!-- Kategori & Harga Sewa -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-black text-slate-700 mb-1.5">
                                Kategori <span class="text-rose-500">*</span>
                            </label>
                            <select name="kategori_id" required class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-700 focus:bg-white focus:border-sky-500 shadow-xs transition">
                                <option value="">Pilih Kategori</option>
                                @foreach($kategoris as $kat)
                                    <option value="{{ $kat->id }}" {{ old('kategori_id') == $kat->id ? 'selected' : '' }}>
                                        {{ $kat->nama }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-black text-slate-700 mb-1.5">
                                Tarif Sewa Paket (Per Hari) <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-xs font-black text-slate-400">Rp</span>
                                <input type="number" name="harga_sewa_harian" id="hargaSewaInput" value="{{ old('harga_sewa_harian') }}" required min="0" placeholder="0" class="w-full pl-10 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-black text-slate-800 focus:bg-white focus:border-sky-500 shadow-xs transition">
                            </div>
                            <p class="text-[10px] text-slate-400 mt-1" id="sumItemsPriceHint"></p>
                        </div>
                    </div>

                    <!-- Deposit & Denda -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-black text-slate-700 mb-1.5">
                                Deposit / Uang Jaminan
                            </label>
                            <div class="relative">
                                <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-xs font-black text-slate-400">Rp</span>
                                <input type="number" name="deposit" id="depositInput" value="{{ old('deposit', 0) }}" min="0" placeholder="0" class="w-full pl-10 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 focus:bg-white focus:border-sky-500 shadow-xs transition">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-black text-slate-700 mb-1.5">
                                Denda Keterlambatan (Per Hari)
                            </label>
                            <div class="relative">
                                <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-xs font-black text-slate-400">Rp</span>
                                <input type="number" name="denda_per_hari" id="dendaInput" value="{{ old('denda_per_hari', 0) }}" min="0" placeholder="0" class="w-full pl-10 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 focus:bg-white focus:border-sky-500 shadow-xs transition">
                            </div>
                        </div>
                    </div>

                    <!-- Deskripsi Paket -->
                    <div>
                        <label class="block text-xs font-black text-slate-700 mb-1.5">
                            Deskripsi Paket <span class="text-rose-500">*</span>
                        </label>
                        <textarea name="deskripsi" id="deskripsiTextarea" rows="4" required placeholder="Jelaskan isi paket, kelebihan bundling ini, serta petunjuk penggunaan..." class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 placeholder-slate-400 focus:bg-white focus:border-sky-500 shadow-xs transition">{{ old('deskripsi') }}</textarea>
                    </div>

                </div>

            </div>

            <!-- KOLOM KANAN (1 SPAN): PENGIRIMAN & AKSI -->
            <div class="space-y-6">
                
                <!-- DUKUNGAN DELIVERY -->
                <div class="bg-white p-5 rounded-3xl border border-slate-200 shadow-sm space-y-3">
                    <h2 class="text-sm font-black text-slate-800 flex items-center gap-2">
                        <i class="fa-solid fa-truck-fast text-sky-500"></i> Pengiriman
                    </h2>

                    <label class="flex items-start gap-3 cursor-pointer">
                        <input type="checkbox" name="is_delivery_supported" value="1" {{ old('is_delivery_supported', true) ? 'checked' : '' }} class="w-4 h-4 text-sky-600 rounded border-slate-300 focus:ring-sky-500 mt-0.5">
                        <div class="text-xs">
                            <span class="font-bold text-slate-800 block">Mendukung Pengantaran (Delivery)</span>
                            <span class="text-[11px] text-slate-400 font-medium">Bisa diantar kurir vendor ke lokasi pelanggan</span>
                        </div>
                    </label>
                </div>

                <!-- SUBMIT BUTTON -->
                <button type="submit" id="btnSubmitPaket" class="w-full py-3.5 px-4 bg-gradient-to-r from-sky-500 to-indigo-600 hover:from-sky-600 hover:to-indigo-700 text-white rounded-2xl text-xs font-black shadow-md transition transform active:scale-95 flex items-center justify-center gap-2">
                    <i class="fa-solid fa-circle-check"></i>
                    <span>Terbitkan Paket Sewa</span>
                </button>

            </div>

        </div>
    </form>

</div>

<script src="https://cdn.jsdelivr.net/npm/heic2any@0.0.4/dist/heic2any.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const checkboxes = document.querySelectorAll('.item-checkbox');
    const badge = document.getElementById('bundleStockBadge');
    const hiddenContainer = document.getElementById('hiddenItemsInputs');
    const estimateCard = document.getElementById('bundleEstimateCard');
    const estimateText = document.getElementById('bundleEstimateText');
    const sumHint = document.getElementById('sumItemsPriceHint');
    const hargaInput = document.getElementById('hargaSewaInput');
    const depositInput = document.getElementById('depositInput');
    const dendaInput = document.getElementById('dendaInput');
    const form = document.getElementById('formUploadPaket');

    // Multi-Foto Upload & Preview
    const fotoInput = document.getElementById('fotos');
    const newPhotoSection = document.getElementById('newPhotoSection');
    const previewContainer = document.getElementById('preview-container');
    const photoCounter = document.getElementById('photoCounter');
    let selectedFiles = [];

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

    if (fotoInput) {
        fotoInput.addEventListener('change', async function(e) {
            if (!e.target.files.length) return;
            const incomingFiles = Array.from(e.target.files);
            const processed = [];

            if (photoCounter) photoCounter.textContent = 'Memproses...';

            for (const file of incomingFiles) {
                let currentFile = file;
                const ext = (file.name || '').split('.').pop().toLowerCase();
                const mime = file.type || '';

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
                        console.warn('heic2any fallback:', err);
                    }
                }

                try {
                    const compressed = await compressImageToWebp(currentFile);
                    processed.push(compressed);
                } catch (err) {
                    processed.push(currentFile);
                }
            }

            selectedFiles = selectedFiles.concat(processed);
            updateFileInput();
            renderPreviews();
        });
    }

    function updateFileInput() {
        if (!fotoInput) return;
        const dt = new DataTransfer();
        selectedFiles.forEach(file => dt.items.add(file));
        fotoInput.files = dt.files;
        if (photoCounter) {
            photoCounter.textContent = `${selectedFiles.length} Media dipilih`;
            if (selectedFiles.length > 0) {
                photoCounter.className = "text-xs font-black px-2.5 py-1 bg-emerald-50 text-emerald-700 rounded-lg";
            } else {
                photoCounter.className = "text-xs font-black px-2.5 py-1 bg-sky-50 text-sky-700 rounded-lg";
            }
        }
        if (newPhotoSection) {
            if (selectedFiles.length > 0) {
                newPhotoSection.classList.remove('hidden');
            } else {
                newPhotoSection.classList.add('hidden');
            }
        }
    }

    window.removePhoto = function(index) {
        selectedFiles.splice(index, 1);
        updateFileInput();
        renderPreviews();
    };

    function renderPreviews() {
        if (!previewContainer) return;
        previewContainer.innerHTML = '';
        selectedFiles.forEach((file, index) => {
            const isVideo = file.type.startsWith('video/') || /\.(mp4|mov|quicktime|webm|mkv|avi|3gp|m4v)$/i.test(file.name);
            const card = document.createElement('div');
            card.className = "relative rounded-2xl overflow-hidden border border-slate-200 aspect-square bg-slate-100 shadow-xs group";

            const isCover = index === 0;
            const coverBadge = isCover 
                ? `<div class="absolute top-2 left-2 px-2 py-0.5 bg-gradient-to-r from-emerald-500 to-teal-600 text-white text-[10px] font-black rounded-lg shadow-sm tracking-wider z-10 flex items-center gap-1"><i class="fa-solid fa-star text-[8px]"></i> COVER UTAMA</div>`
                : `<div class="absolute top-2 left-2 px-2 py-0.5 bg-slate-900/80 backdrop-blur-xs text-white text-[10px] font-bold rounded-lg shadow-sm z-10 flex items-center gap-1"><i class="fa-solid fa-images text-sky-400 text-[9px]"></i> GALERI #${index}</div>`;
            const videoBadge = isVideo
                ? `<div class="absolute ${isCover ? 'top-8' : 'top-8'} left-2 px-2 py-0.5 bg-slate-900/80 text-white text-[10px] font-bold rounded-lg shadow-sm flex items-center gap-1 z-10"><i class="fa-solid fa-video text-rose-400 text-[9px]"></i> VIDEO</div>`
                : '';

            const objectUrl = URL.createObjectURL(file);
            const mediaHtml = isVideo
                ? `<video src="${objectUrl}" class="w-full h-full object-cover" muted playsinline></video>`
                : `<img src="${objectUrl}" alt="Preview" class="w-full h-full object-cover">`;

            card.innerHTML = `
                ${mediaHtml}
                ${coverBadge}
                ${videoBadge}
                <button type="button" onclick="removePhoto(${index})" class="absolute top-2 right-2 w-7 h-7 rounded-lg bg-slate-900/80 hover:bg-rose-600 text-white flex items-center justify-center transition active:scale-90 shadow-sm cursor-pointer z-10" title="Hapus berkas">
                    <i class="fa-solid fa-trash text-[10px]"></i>
                </button>
                <div class="absolute bottom-0 inset-x-0 p-1.5 bg-gradient-to-t from-slate-900/75 to-transparent text-[10px] text-white font-medium truncate px-2 z-10">
                    ${file.name}
                </div>
            `;
            previewContainer.appendChild(card);
        });
    }

    function updateComposition() {
        hiddenContainer.innerHTML = '';
        let selectedCount = 0;
        let minCapacity = null;
        let sumPrice = 0;
        let sumDeposit = 0;
        let sumDenda = 0;

        checkboxes.forEach((cb) => {
            const id = cb.dataset.id;
            const stock = parseInt(cb.dataset.stock) || 0;
            const price = parseFloat(cb.dataset.price) || 0;
            const deposit = parseFloat(cb.dataset.deposit) || 0;
            const denda = parseFloat(cb.dataset.denda) || 0;
            const qtyBox = document.getElementById('qty_box_' + id);
            const qtyInput = qtyBox ? qtyBox.querySelector('.item-qty-input') : null;

            if (cb.checked) {
                selectedCount++;
                if (qtyBox) qtyBox.classList.remove('hidden');
                if (qtyBox) qtyBox.classList.add('flex');

                const qty = qtyInput ? (parseInt(qtyInput.value) || 1) : 1;

                // Hidden inputs for submission
                const inputBarang = document.createElement('input');
                inputBarang.type = 'hidden';
                inputBarang.name = `items[${selectedCount - 1}][barang_id]`;
                inputBarang.value = id;
                hiddenContainer.appendChild(inputBarang);

                const inputQty = document.createElement('input');
                inputQty.type = 'hidden';
                inputQty.name = `items[${selectedCount - 1}][quantity]`;
                inputQty.value = qty;
                hiddenContainer.appendChild(inputQty);

                // Capacity calculation: floor(stock / qty)
                const possible = Math.floor(stock / qty);
                if (minCapacity === null || possible < minCapacity) {
                    minCapacity = possible;
                }

                sumPrice += price * qty;
                sumDeposit += deposit * qty;
                sumDenda += denda * qty;
            } else {
                if (qtyBox) qtyBox.classList.add('hidden');
                if (qtyBox) qtyBox.classList.remove('flex');
            }
        });

        badge.textContent = `${selectedCount} barang dipilih`;

        if (selectedCount > 0) {
            estimateCard.classList.remove('hidden');
            estimateText.textContent = `${Math.max(0, minCapacity || 0)} paket siap sewa`;
            sumHint.textContent = `Total tarif satuan komponen: Rp ${sumPrice.toLocaleString('id-ID')}/hari`;

            // Auto-fill defaults if empty
            if (!depositInput.value || depositInput.value == '0') {
                depositInput.value = sumDeposit;
            }
            if (!dendaInput.value || dendaInput.value == '0') {
                dendaInput.value = sumDenda;
            }
        } else {
            estimateCard.classList.add('hidden');
            sumHint.textContent = '';
        }
    }

    checkboxes.forEach(cb => {
        cb.addEventListener('change', updateComposition);
    });

    document.querySelectorAll('.item-qty-input').forEach(input => {
        input.addEventListener('input', updateComposition);
    });

    form.addEventListener('submit', function(e) {
        let selected = 0;
        checkboxes.forEach(cb => { if(cb.checked) selected++; });
        if (selected === 0) {
            e.preventDefault();
            alert('⚠️ Harap pilih minimal 1 barang untuk menyusun paket sewa!');
        }
    });

    updateComposition();
});
</script>
@endsection
