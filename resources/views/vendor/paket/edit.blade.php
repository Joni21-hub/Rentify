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
            
            <!-- KOLOM KIRI (2 SPAN): KOMPOSISI BARANG & INFORMASI PAKET -->
            <div class="lg:col-span-2 space-y-6">
                
                <!-- 1. PILIH KOMPOSISI BARANG -->
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

            <!-- KOLOM KANAN (1 SPAN): FOTO & PENGIRIMAN -->
            <div class="space-y-6">
                
                <!-- FOTO / MEDIA PAKET -->
                <div class="bg-white p-5 rounded-3xl border border-slate-200 shadow-sm space-y-4">
                    <div>
                        <h2 class="text-sm font-black text-slate-800 flex items-center gap-2">
                            <i class="fa-solid fa-camera text-sky-500"></i> Foto Sampul Paket
                        </h2>
                        <p class="text-[11px] text-slate-400 mt-0.5">
                            Unggah foto baru jika ingin mengganti sampul paket.
                        </p>
                    </div>

                    @if($paket->cover_photo_url)
                        <div class="relative rounded-2xl overflow-hidden border border-slate-200">
                            <img src="{{ $paket->cover_photo_url }}" alt="{{ $paket->nama }}" class="w-full h-40 object-cover">
                            <span class="absolute bottom-2 left-2 px-2 py-0.5 bg-black/60 text-white rounded-md text-[10px] font-bold">Foto Saat Ini</span>
                        </div>
                    @endif

                    <input type="file" name="fotos[]" id="fotos" accept="image/*" class="hidden">

                    <label for="fotos" class="cursor-pointer p-4 rounded-2xl border-2 border-dashed border-sky-300 bg-sky-50/50 hover:bg-sky-100/60 transition flex flex-col items-center justify-center text-center gap-2 text-sky-700">
                        <div class="w-10 h-10 rounded-xl bg-sky-500 text-white flex items-center justify-center text-base shadow-sm">
                            <i class="fa-solid fa-cloud-arrow-up"></i>
                        </div>
                        <span class="text-xs font-black">Ganti Foto Sampul</span>
                        <span class="text-[10px] text-slate-400 font-medium">JPG, PNG, WebP (Maks 100MB)</span>
                    </label>

                    <div id="photoPreviewContainer" class="hidden">
                        <img id="photoPreview" class="w-full h-40 object-cover rounded-xl border border-slate-200">
                    </div>
                </div>

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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const checkboxes = document.querySelectorAll('.item-checkbox');
    const badge = document.getElementById('bundleStockBadge');
    const hiddenContainer = document.getElementById('hiddenItemsInputs');
    const estimateCard = document.getElementById('bundleEstimateCard');
    const estimateText = document.getElementById('bundleEstimateText');
    const sumHint = document.getElementById('sumItemsPriceHint');
    const fotoInput = document.getElementById('fotos');
    const previewContainer = document.getElementById('photoPreviewContainer');
    const photoPreview = document.getElementById('photoPreview');
    const form = document.getElementById('formEditPaket');

    if (fotoInput) {
        fotoInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(evt) {
                    photoPreview.src = evt.target.result;
                    previewContainer.classList.remove('hidden');
                };
                reader.readAsDataURL(file);
            }
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
