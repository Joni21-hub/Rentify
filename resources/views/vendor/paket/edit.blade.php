@extends('layouts.vendor')

@section('title', 'Edit Paket Sewa — Vendor Rentify')

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
                    <i class="fa-solid fa-boxes-stacked mr-1"></i> Edit Paket Sewa
                </span>
            </div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-800 tracking-tight mt-0.5">{{ $paket->nama }}</h1>
            <p class="text-xs sm:text-sm text-slate-500 font-medium">Perbarui tarif, deskripsi, atau komposisi barang di dalam paket sewa ini.</p>
        </div>
    </div>

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

    <form action="{{ route('vendor.paket.update', $paket->id) }}" method="POST" enctype="multipart/form-data" id="formEditPaket" class="space-y-6">
        @csrf
        @method('PUT')

        @php
            $existingMap = [];
            foreach ($paket->paketItems as $pi) {
                $existingMap[$pi->barang_id] = $pi->quantity;
            }
        @endphp

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- KOLOM KIRI (2 SPAN): MEDIA, KOMPOSISI BARANG & INFORMASI PAKET -->
            <div class="lg:col-span-2 space-y-6">
                
                <!-- 1. FOTO & MEDIA PAKET (MATCHING HALAMAN EDIT PRODUK) -->
                <div class="bg-white p-5 sm:p-6 rounded-3xl border border-slate-200 shadow-sm space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <div>
                            <h2 class="text-base font-black text-slate-800 flex items-center gap-2">
                                <i class="fa-solid fa-photo-film text-sky-500"></i> Media Paket (Foto & Galeri)
                            </h2>
                            <p class="text-xs text-slate-400 mt-0.5">
                                Kelola foto cover utama, foto galeri paket, atau unggah foto/video baru.
                            </p>
                        </div>
                    </div>

                    <!-- FOTO SAMPUL SAAT INI -->
                    @if($paket->cover_photo_url)
                        <div class="space-y-1.5">
                            <span class="text-xs font-black text-slate-700 uppercase tracking-wider block">Foto Sampul Utama Saat Ini:</span>
                            <div class="relative rounded-2xl overflow-hidden border border-slate-200 aspect-video bg-slate-100 shadow-xs max-w-md">
                                <img id="currentCoverImg" src="{{ $paket->cover_photo_url }}" alt="{{ $paket->nama }}" class="w-full h-full object-cover">
                                <span class="absolute bottom-2 left-2 px-2.5 py-1 bg-slate-900/80 backdrop-blur-xs text-white rounded-lg text-[10px] font-black tracking-wide flex items-center gap-1.5 shadow-xs">
                                    <i class="fa-solid fa-star text-amber-400 text-[10px]"></i> Sampul Saat Ini
                                </span>
                            </div>
                        </div>
                    @endif

                    <!-- FOTO GALERI EKSIS (JIKA ADA) -->
                    @if($paket->fotos && $paket->fotos->count() > 0)
                        <div class="space-y-2 pt-2 border-t border-slate-100" id="existingGallerySection">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-black text-slate-700 uppercase tracking-wider">
                                    Foto Galeri Paket:
                                </span>
                                <span id="existingCountBadge" class="text-xs font-black px-2.5 py-1 bg-slate-100 text-slate-600 rounded-lg">
                                    {{ $paket->fotos->count() }} Foto
                                </span>
                            </div>

                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3" id="existingGalleryGrid">
                                @foreach($paket->fotos as $foto)
                                    <div class="relative rounded-xl overflow-hidden border border-slate-200 aspect-square bg-slate-100 group shadow-xs" id="foto-card-{{ $foto->id }}">
                                        <img src="{{ $foto->foto_url }}" alt="Galeri {{ $paket->nama }}" class="w-full h-full object-cover">
                                        
                                        <!-- Actions Overlay -->
                                        <div class="absolute inset-0 bg-slate-900/75 opacity-0 group-hover:opacity-100 transition-opacity flex flex-col items-center justify-center gap-1.5 p-2">
                                            <button type="button" onclick="setCoverAjax({{ $paket->id }}, {{ $foto->id }})" class="w-full py-1.5 px-2 rounded-lg bg-amber-500 hover:bg-amber-600 text-white text-[10px] font-black flex items-center justify-center gap-1 transition shadow-xs cursor-pointer" title="Jadikan foto ini sebagai sampul utama">
                                                <i class="fa-solid fa-star text-[9px]"></i> Set Sampul
                                            </button>
                                            <button type="button" onclick="deleteFotoAjax({{ $foto->id }})" class="w-full py-1.5 px-2 rounded-lg bg-rose-600 hover:bg-rose-700 text-white text-[10px] font-black flex items-center justify-center gap-1 transition shadow-xs cursor-pointer" title="Hapus foto galeri ini">
                                                <i class="fa-solid fa-trash text-[9px]"></i> Hapus
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- UNGGAH FOTO BARU -->
                    <div class="space-y-3 pt-2 border-t border-slate-100">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-black text-slate-800 uppercase tracking-wider">Unggah Foto / Video Baru (Opsional)</span>
                            <span id="photoCounter" class="text-xs font-black px-2.5 py-1 bg-emerald-50 text-emerald-700 rounded-lg hidden shrink-0">
                                0 Media dipilih
                            </span>
                        </div>

                        <input type="file" name="fotos[]" id="fotos" multiple accept="image/*,video/*,.heic,.heif,.avif,.webp,.mp4,.mov,.mkv,.webm" class="hidden">

                        <label for="fotos" class="cursor-pointer p-4 rounded-2xl border-2 border-dashed border-sky-400/80 bg-sky-50/60 hover:bg-sky-100/70 transition flex items-center justify-center gap-3 text-sky-700 active:scale-98">
                            <div class="w-10 h-10 rounded-xl bg-sky-500 text-white flex items-center justify-center text-base shadow-sm">
                                <i class="fa-solid fa-cloud-arrow-up"></i>
                            </div>
                            <div class="text-left">
                                <span class="block text-xs font-black">Pilih / Tambah Media Baru</span>
                                <span class="block text-[11px] text-sky-600 font-medium">Bisa pilih lebih dari satu foto & video (Maks 100MB)</span>
                            </div>
                        </label>

                        @if($paket->cover_photo_url)
                            <label class="flex items-start gap-2.5 cursor-pointer bg-slate-50 p-3 rounded-2xl border border-slate-200/80">
                                <input type="checkbox" name="replace_cover" id="replaceCoverCheckbox" value="1" checked class="w-4 h-4 text-sky-600 rounded border-slate-300 focus:ring-sky-500 mt-0.5">
                                <div class="text-[11px] leading-tight">
                                    <span class="font-bold text-slate-700 block">Jadikan foto baru pertama sebagai Sampul Utama</span>
                                    <span class="text-[10px] text-slate-400">Jika tidak dicentang, semua foto baru ditambahkan ke galeri dan sampul saat ini dipertahankan.</span>
                                </div>
                            </label>
                        @endif

                        <!-- Live Previews Grid for Newly Selected Files -->
                        <div id="newPhotoSection" class="hidden space-y-2 pt-2 border-t border-slate-100">
                            <span class="text-xs font-black text-slate-700 block">Pratinjau Media Baru yang Dipilih:</span>
                            <div id="preview-container" class="grid grid-cols-2 sm:grid-cols-4 gap-3"></div>
                        </div>
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

                    <!-- List Barang Vendor -->
                    <div class="space-y-2.5 max-h-[380px] overflow-y-auto pr-1">
                        @foreach($myProducts as $prod)
                            @php
                                $imgUrl = $prod->cover_photo_url;
                                $isChecked = isset($existingMap[$prod->id]);
                                $existingQty = $existingMap[$prod->id] ?? 1;
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
                                       data-denda="{{ $prod->denda_per_hari }}"
                                       {{ $isChecked ? 'checked' : '' }}>

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

                                <!-- Input Jumlah -->
                                <div class="qty-container {{ $isChecked ? 'flex' : 'hidden' }} items-center gap-1.5 shrink-0" id="qty_box_{{ $prod->id }}">
                                    <span class="text-[10px] font-bold text-slate-500">Qty:</span>
                                    <input type="number" 
                                           min="1" 
                                           max="{{ max(1, $prod->stok_total) }}" 
                                           value="{{ $existingQty }}" 
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
                            <span class="font-bold text-indigo-900">Perkiraan Kapasitas Paket Saat Ini:</span>
                            <span class="font-black text-indigo-700 text-sm" id="bundleEstimateText">0 paket</span>
                        </div>
                        <p class="text-[11px] text-indigo-600 mt-1 leading-snug">
                            Kapasitas paket dihitung secara otomatis mengikuti stok terendah barang penyusunnya.
                        </p>
                    </div>

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
                        <input type="text" name="nama" value="{{ old('nama', $paket->nama) }}" required class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 focus:bg-white focus:border-sky-500 shadow-xs transition">
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
                                    <option value="{{ $kat->id }}" {{ old('kategori_id', $paket->kategori_id) == $kat->id ? 'selected' : '' }}>
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
                                <input type="number" name="harga_sewa_harian" id="hargaSewaInput" value="{{ old('harga_sewa_harian', (int)$paket->harga_sewa_harian) }}" required min="0" class="w-full pl-10 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-black text-slate-800 focus:bg-white focus:border-sky-500 shadow-xs transition">
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
                                <input type="number" name="deposit" id="depositInput" value="{{ old('deposit', (int)$paket->deposit) }}" min="0" class="w-full pl-10 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 focus:bg-white focus:border-sky-500 shadow-xs transition">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-black text-slate-700 mb-1.5">
                                Denda Keterlambatan (Per Hari)
                            </label>
                            <div class="relative">
                                <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-xs font-black text-slate-400">Rp</span>
                                <input type="number" name="denda_per_hari" id="dendaInput" value="{{ old('denda_per_hari', (int)$paket->denda_per_hari) }}" min="0" class="w-full pl-10 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 focus:bg-white focus:border-sky-500 shadow-xs transition">
                            </div>
                        </div>
                    </div>

                    <!-- Deskripsi Paket -->
                    <div>
                        <label class="block text-xs font-black text-slate-700 mb-1.5">
                            Deskripsi Paket <span class="text-rose-500">*</span>
                        </label>
                        <textarea name="deskripsi" rows="4" required class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:bg-white focus:border-sky-500 shadow-xs transition">{{ old('deskripsi', $paket->deskripsi) }}</textarea>
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
                        <input type="checkbox" name="is_delivery_supported" value="1" {{ old('is_delivery_supported', $paket->is_delivery_supported) ? 'checked' : '' }} class="w-4 h-4 text-sky-600 rounded border-slate-300 focus:ring-sky-500 mt-0.5">
                        <div class="text-xs">
                            <span class="font-bold text-slate-800 block">Mendukung Pengantaran (Delivery)</span>
                            <span class="text-[11px] text-slate-400 font-medium">Bisa diantar kurir vendor ke lokasi pelanggan</span>
                        </div>
                    </label>
                </div>

                <!-- SUBMIT BUTTON -->
                <button type="submit" id="btnSubmitPaket" class="w-full py-3.5 px-4 bg-gradient-to-r from-sky-500 to-indigo-600 hover:from-sky-600 hover:to-indigo-700 text-white rounded-2xl text-xs font-black shadow-md transition transform active:scale-95 flex items-center justify-center gap-2">
                    <i class="fa-solid fa-floppy-disk"></i>
                    <span>Simpan Perubahan Paket</span>
                </button>

            </div>

        </div>
    </form>

</div>

<script src="https://cdn.jsdelivr.net/npm/heic2any@0.0.4/dist/heic2any.min.js"></script>
<script>
// AJAX Delete Foto
async function deleteFotoAjax(fotoId) {
    if (!confirm('Hapus foto ini dari galeri paket sewa?')) return;
    try {
        const token = document.querySelector('input[name="_token"]')?.value || '{{ csrf_token() }}';
        const res = await fetch(`{{ url('vendor/fotos') }}/${fotoId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        const data = await res.json();
        if (data.success) {
            const card = document.getElementById(`foto-card-${fotoId}`);
            if (card) {
                card.classList.add('transition-all', 'duration-300', 'opacity-0', 'scale-90');
                setTimeout(() => {
                    card.remove();
                    const grid = document.getElementById('existingGalleryGrid');
                    const badge = document.getElementById('existingCountBadge');
                    if (grid) {
                        const remaining = grid.querySelectorAll('[id^="foto-card-"]').length;
                        if (badge) badge.textContent = `${remaining} Foto`;
                        if (remaining === 0) {
                            const sec = document.getElementById('existingGallerySection');
                            if (sec) sec.remove();
                        }
                    }
                }, 300);
            }
        } else {
            alert(data.message || 'Gagal menghapus foto.');
        }
    } catch (e) {
        console.error(e);
        alert('Terjadi kesalahan saat menghapus foto.');
    }
}

// AJAX Set Sampul Foto
async function setCoverAjax(paketId, fotoId) {
    if (!confirm('Jadikan foto ini sebagai foto sampul utama paket sewa?')) return;
    try {
        const token = document.querySelector('input[name="_token"]')?.value || '{{ csrf_token() }}';
        const res = await fetch(`{{ url('vendor/barang') }}/${paketId}/fotos/${fotoId}/cover`, {
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        const data = await res.json();
        if (data.success) {
            window.location.reload();
        } else {
            alert(data.message || 'Gagal mengubah foto sampul.');
        }
    } catch (e) {
        console.error(e);
        alert('Terjadi kesalahan saat mengubah foto sampul.');
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const checkboxes = document.querySelectorAll('.item-checkbox');
    const badge = document.getElementById('bundleStockBadge');
    const hiddenContainer = document.getElementById('hiddenItemsInputs');
    const estimateCard = document.getElementById('bundleEstimateCard');
    const estimateText = document.getElementById('bundleEstimateText');
    const sumHint = document.getElementById('sumItemsPriceHint');
    const form = document.getElementById('formEditPaket');

    // Multi-Foto Upload & Preview
    const fotoInput = document.getElementById('fotos');
    const newPhotoSection = document.getElementById('newPhotoSection');
    const previewContainer = document.getElementById('preview-container');
    const photoCounter = document.getElementById('photoCounter');
    const replaceCoverCheckbox = document.getElementById('replaceCoverCheckbox');
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

            if (photoCounter) {
                photoCounter.classList.remove('hidden');
                photoCounter.textContent = 'Memproses...';
            }

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

    if (replaceCoverCheckbox) {
        replaceCoverCheckbox.addEventListener('change', renderPreviews);
    }

    function updateFileInput() {
        if (!fotoInput) return;
        const dt = new DataTransfer();
        selectedFiles.forEach(file => dt.items.add(file));
        fotoInput.files = dt.files;
        if (photoCounter) {
            photoCounter.textContent = `${selectedFiles.length} Media baru dipilih`;
            if (selectedFiles.length > 0) {
                photoCounter.className = "text-xs font-black px-2.5 py-1 bg-emerald-50 text-emerald-700 rounded-lg";
                photoCounter.classList.remove('hidden');
            } else {
                photoCounter.className = "text-xs font-black px-2.5 py-1 bg-sky-50 text-sky-700 rounded-lg";
                photoCounter.classList.add('hidden');
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
        const willReplaceCover = replaceCoverCheckbox ? replaceCoverCheckbox.checked : true;

        selectedFiles.forEach((file, index) => {
            const isVideo = file.type.startsWith('video/') || /\.(mp4|mov|quicktime|webm|mkv|avi|3gp|m4v)$/i.test(file.name);
            const card = document.createElement('div');
            card.className = "relative rounded-2xl overflow-hidden border border-slate-200 aspect-square bg-slate-100 shadow-xs group";

            const isCover = (index === 0 && willReplaceCover);
            const coverBadge = isCover 
                ? `<div class="absolute top-2 left-2 px-2 py-0.5 bg-gradient-to-r from-emerald-500 to-teal-600 text-white text-[10px] font-black rounded-lg shadow-sm tracking-wider z-10 flex items-center gap-1"><i class="fa-solid fa-star text-[8px]"></i> SAMPUL BARU</div>`
                : `<div class="absolute top-2 left-2 px-2 py-0.5 bg-slate-900/80 backdrop-blur-xs text-white text-[10px] font-bold rounded-lg shadow-sm z-10 flex items-center gap-1"><i class="fa-solid fa-images text-sky-400 text-[9px]"></i> GALERI BARU</div>`;
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
                <button type="button" onclick="removePhoto(${index})" class="absolute top-2 right-2 w-7 h-7 rounded-lg bg-slate-900/80 hover:bg-rose-600 text-white flex items-center justify-center transition active:scale-90 shadow-sm cursor-pointer z-10" title="Batal pilih berkas">
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

        checkboxes.forEach((cb) => {
            const id = cb.dataset.id;
            const stock = parseInt(cb.dataset.stock) || 0;
            const price = parseFloat(cb.dataset.price) || 0;
            const qtyBox = document.getElementById('qty_box_' + id);
            const qtyInput = qtyBox ? qtyBox.querySelector('.item-qty-input') : null;

            if (cb.checked) {
                selectedCount++;
                if (qtyBox) qtyBox.classList.remove('hidden');
                if (qtyBox) qtyBox.classList.add('flex');

                const qty = qtyInput ? (parseInt(qtyInput.value) || 1) : 1;

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

                const possible = Math.floor(stock / qty);
                if (minCapacity === null || possible < minCapacity) {
                    minCapacity = possible;
                }

                sumPrice += price * qty;
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
