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
                                <i class="fa-solid fa-camera text-sky-500"></i> Foto Barang <span class="text-rose-500">*</span>
                            </h2>
                            <p class="text-xs text-slate-400 mt-0.5">Upload minimal 1 foto (foto pertama akan jadi Sampul/Cover).</p>
                        </div>
                        <span id="photoCounter" class="text-xs font-black px-2.5 py-1 bg-sky-50 text-sky-700 rounded-lg">
                            0 Foto dipilih
                        </span>
                    </div>

                    <!-- Hidden Input -->
                    <input type="file" name="fotos[]" id="fotos" multiple accept="image/jpeg,image/png,image/jpg" class="hidden">

                    <!-- Mobile-First Big Touch Area -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-4">
                        <!-- Tombol Buka Kamera / Galeri -->
                        <label for="fotos" class="cursor-pointer p-4 rounded-2xl border-2 border-dashed border-sky-400/80 bg-sky-50/60 hover:bg-sky-100/70 transition flex items-center justify-center gap-3 text-sky-700 active:scale-98">
                            <div class="w-11 h-11 rounded-xl bg-sky-500 text-white flex items-center justify-center text-lg shadow-sm">
                                <i class="fa-solid fa-camera"></i>
                            </div>
                            <div class="text-left">
                                <span class="block text-xs font-black">Buka Kamera / Galeri</span>
                                <span class="block text-[11px] text-sky-600/80 font-medium">Bisa pilih banyak foto</span>
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
                        <input type="text" name="nama" value="{{ old('nama') }}" required placeholder="Contoh: Kamera Sony A7 III + Lensa 28-70mm" class="w-full px-4 py-3 rounded-2xl border border-slate-200 text-slate-800 text-sm font-semibold focus:outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-100 transition">
                    </div>

                    <!-- Kategori & Kondisi -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-1.5">
                                Kategori <span class="text-rose-500">*</span>
                            </label>
                            <select name="kategori_id" required class="w-full px-4 py-3 rounded-2xl border border-slate-200 text-slate-800 text-sm font-semibold focus:outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-100 transition bg-white">
                                <option value="">-- Pilih Kategori --</option>
                                @foreach($kategoris as $k)
                                    <option value="{{ $k->id }}" {{ old('kategori_id') == $k->id ? 'selected' : '' }}>
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
                        <textarea name="deskripsi" rows="4" required placeholder="Tuliskan spesifikasi, kelengkapan (tas, charger, baterai tambahan), aturan sewa, atau peringatan penggunaan..." class="w-full px-4 py-3 rounded-2xl border border-slate-200 text-slate-800 text-sm font-medium focus:outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-100 transition leading-relaxed">{{ old('deskripsi') }}</textarea>
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
                        <input type="hidden" name="latitude" id="lat_produk" value="{{ old('latitude', '-6.200000') }}">
                        <input type="hidden" name="longitude" id="lon_produk" value="{{ old('longitude', '106.816666') }}">

                        <!-- Alamat Input -->
                        <div>
                            <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-1.5">
                                Alamat Lengkap Toko / Gudang Penjemputan <span class="text-rose-500">*</span>
                            </label>
                            <textarea name="alamat" id="alamat_produk" rows="2" required placeholder="Jl. Contoh No. 12, RT/RW, Kelurahan, Kecamatan, Kota..." class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-slate-800 text-xs font-medium focus:outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-100 transition">{{ old('alamat') }}</textarea>
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
                        <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-1.5">
                            Tarif Sewa / Hari <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-xs font-bold text-slate-400">Rp</span>
                            <input type="number" name="harga_sewa_harian" value="{{ old('harga_sewa_harian') }}" required min="0" placeholder="150000" class="w-full pl-11 pr-4 py-3 rounded-2xl border border-slate-200 text-slate-800 text-sm font-black focus:outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-100 transition">
                        </div>
                    </div>

                    <!-- Stok Unit Fisik -->
                    <div>
                        <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-1.5">
                            Jumlah Stok Unit <span class="text-rose-500">*</span>
                        </label>
                        <input type="number" name="stok_total" value="{{ old('stok_total', 1) }}" required min="1" class="w-full px-4 py-3 rounded-2xl border border-slate-200 text-slate-800 text-sm font-black focus:outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-100 transition">
                    </div>

                    <!-- Deposit Jaminan -->
                    <div>
                        <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-1.5">
                            Deposit Jaminan (Opsional/Rp) <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-xs font-bold text-slate-400">Rp</span>
                            <input type="number" name="deposit" value="{{ old('deposit', 0) }}" required min="0" placeholder="0" class="w-full pl-11 pr-4 py-3 rounded-2xl border border-slate-200 text-slate-800 text-sm font-semibold focus:outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-100 transition">
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
                            <input type="number" name="denda_per_hari" value="{{ old('denda_per_hari', 0) }}" required min="0" placeholder="50000" class="w-full pl-11 pr-4 py-3 rounded-2xl border border-slate-200 text-slate-800 text-sm font-semibold focus:outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-100 transition">
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
<script>
    // ==========================================
    // 1. MOBILE CAMERA & MULTI-PHOTO PREVIEWS
    // ==========================================
    const inputFotos = document.getElementById('fotos');
    const previewContainer = document.getElementById('preview-container');
    const photoCounter = document.getElementById('photoCounter');
    let selectedFiles = [];

    inputFotos.addEventListener('change', function(e) {
        if (!e.target.files.length) return;
        selectedFiles = selectedFiles.concat(Array.from(e.target.files));
        updateFileInput();
        renderPreviews();
    });

    function updateFileInput() {
        const dt = new DataTransfer();
        selectedFiles.forEach(file => dt.items.add(file));
        inputFotos.files = dt.files;
        photoCounter.textContent = `${selectedFiles.length} Foto dipilih`;
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
            const reader = new FileReader();
            reader.onload = function(e) {
                const card = document.createElement('div');
                card.className = "relative rounded-2xl overflow-hidden border-2 border-slate-200 aspect-square bg-slate-100 shadow-xs group";
                
                const isCover = index === 0;
                const coverBadge = isCover 
                    ? `<div class="absolute top-2 left-2 px-2 py-0.5 bg-gradient-to-r from-emerald-500 to-teal-600 text-white text-[10px] font-black rounded-lg shadow-sm tracking-wider">COVER UTAMA</div>`
                    : '';

                card.innerHTML = `
                    <img src="${e.target.result}" alt="Preview" class="w-full h-full object-cover">
                    ${coverBadge}
                    <button type="button" onclick="removePhoto(${index})" class="absolute top-2 right-2 w-8 h-8 rounded-xl bg-slate-900/80 hover:bg-rose-600 text-white flex items-center justify-center transition active:scale-90 shadow-sm cursor-pointer" title="Hapus foto">
                        <i class="fa-solid fa-trash text-xs"></i>
                    </button>
                    <div class="absolute bottom-0 inset-x-0 p-1.5 bg-gradient-to-t from-slate-900/70 to-transparent text-[10px] text-white font-medium truncate px-2">
                        ${file.name}
                    </div>
                `;
                previewContainer.appendChild(card);
            }
            reader.readAsDataURL(file);
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
</script>
@endpush