<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Tambah Produk - Vendor Rentify</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { theme: { extend: { fontFamily: { sans: ['"Plus Jakarta Sans"', 'sans-serif'] }, colors: { brand: { 50: '#eff6ff', 100: '#dbeafe', 500: '#3b82f6', 900: '#1e3a8a' } } } } }
    </script>
    <style> 
        .glass-panel { background: rgba(255, 255, 255, 0.85); backdrop-filter: blur(12px); border: 1px solid rgba(255, 255, 255, 0.5); } 
        /* Styling Kartu Radio Pilihan */
        .radio-card input:checked + div { border-color: #3b82f6; background-color: #eff6ff; color: #1e3a8a; box-shadow: 0 4px 12px rgba(59, 130, 246, 0.15); }
        .radio-card-no input:checked + div { border-color: #ef4444; background-color: #fef2f2; color: #991b1b; box-shadow: 0 4px 12px rgba(239, 68, 68, 0.15); }
    </style>
</head>
<body class="bg-[#F4F8FF] font-sans text-slate-700 antialiased overflow-x-hidden">

    <div class="flex min-h-screen">
        <aside class="w-64 fixed inset-y-0 left-0 z-50 glass-panel shadow-sm border-r border-slate-200 flex flex-col">
            <div class="h-20 flex items-center justify-center border-b border-white/50 px-6">
                <a href="{{ url('/') }}" class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-brand-900 flex items-center justify-center text-white"><i class="fa-solid fa-store text-sm"></i></div>
                    <span class="text-xl font-bold text-brand-900 tracking-tight">Rentify<span class="text-emerald-500">.</span></span>
                </a>
            </div>
            <div class="flex-1 overflow-y-auto py-6 px-4 space-y-1">
                <p class="px-4 text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Menu Utama</p>
                <a href="{{ route('vendor.dashboard') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-500 hover:bg-white hover:text-slate-700 hover:shadow-sm transition-all font-medium"><i class="fa-solid fa-chart-pie w-5 text-center"></i> <span>Dashboard Utama</span></a>
                <a href="{{ route('vendor.barang.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl bg-white text-brand-900 shadow-sm border border-slate-100 font-medium transition-all"><i class="fa-solid fa-box-open w-5 text-center"></i> <span>Manajemen Produk</span></a>
            </div>
        </aside>

        <main class="flex-1 ml-64 p-8">
            <header class="mb-8 flex items-center gap-4">
                <a href="{{ route('vendor.barang.index') }}" class="w-10 h-10 rounded-xl bg-white border border-slate-200 flex items-center justify-center text-slate-500 hover:bg-slate-50 transition-all shadow-sm"><i class="fa-solid fa-arrow-left"></i></a>
                <div><h1 class="text-2xl font-bold text-slate-800">Tambah Produk Baru</h1><p class="text-slate-500 mt-1">Lengkapi detail barang yang akan disewakan. Barang akan ditinjau oleh Admin.</p></div>
            </header>

            @if($errors->any())
                <div class="mb-6 p-4 bg-rose-50 border border-rose-100 text-rose-600 rounded-xl">
                    <div class="flex items-center gap-2 font-bold mb-2"><i class="fa-solid fa-triangle-exclamation"></i> Terjadi Kesalahan!</div>
                    <ul class="list-disc list-inside text-sm">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif

            <form action="{{ route('vendor.barang.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div class="lg:col-span-2 space-y-6">
                        
                        <div class="glass-panel p-6 rounded-2xl shadow-sm">
                            <h2 class="text-lg font-bold text-slate-800 mb-4 border-b border-slate-100 pb-3">Informasi Dasar</h2>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-1">Nama Barang <span class="text-rose-500">*</span></label>
                                    <input type="text" name="nama" value="{{ old('nama') }}" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 outline-none transition-all bg-white" placeholder="">
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-semibold text-slate-700 mb-1">Kategori <span class="text-rose-500">*</span></label>
                                        <select name="kategori_id" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 outline-none transition-all bg-white">
                                            <option value="">-- Pilih Kategori --</option>
                                            @foreach($kategoris as $k)
                                                <option value="{{ $k->id }}" {{ old('kategori_id') == $k->id ? 'selected' : '' }}>{{ $k->nama }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-slate-700 mb-1">Kondisi Barang <span class="text-rose-500">*</span></label>
                                        <select name="kondisi" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 outline-none transition-all bg-white">
                                            <option value="Sangat Baik">Sangat Baik</option>
                                            <option value="Baik">Baik</option>
                                            <option value="Cukup">Cukup (Ada minus wajar)</option>
                                        </select>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-1">Deskripsi Lengkap <span class="text-rose-500">*</span></label>
                                    <textarea name="deskripsi" rows="4" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 outline-none transition-all bg-white">{{ old('deskripsi') }}</textarea>
                                </div>
                            </div>
                        </div>

                        <!-- PANEL LOKASI & PENGIRIMAN YANG SUDAH DIPERBESAR & OTOMATIS -->
                        <div class="glass-panel p-6 rounded-2xl shadow-sm">
                            <h2 class="text-lg font-bold text-slate-800 mb-4 border-b border-slate-100 pb-3">Lokasi & Opsi Pengiriman</h2>
                            
                            <div class="space-y-5">
                                <!-- PILIHAN YA / TIDAK DIPERBESAR -->
                                <div>
                                    <label class="block text-sm font-bold text-slate-800 mb-2">Apakah Toko Menyediakan Layanan Antar (Kurir)? <span class="text-rose-500">*</span></label>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <label class="radio-card cursor-pointer">
                                            <input type="radio" name="is_delivery_supported" value="1" checked class="hidden">
                                            <div class="p-4 rounded-xl border-2 border-slate-200 transition-all flex items-center gap-3">
                                                <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-lg font-bold">o</div>
                                                <div>
                                                    <div class="font-bold text-sm">Ya, Sediakan Antar</div>
                                                    <div class="text-xs text-slate-500 mt-0.5">Kurir toko bisa antar (Rp 4.000/Km)</div>
                                                </div>
                                            </div>
                                        </label>

                                        <label class="radio-card-no cursor-pointer">
                                            <input type="radio" name="is_delivery_supported" value="0" class="hidden">
                                            <div class="p-4 rounded-xl border-2 border-slate-200 transition-all flex items-center gap-3">
                                                <div class="w-10 h-10 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center text-lg font-bold">o</div>
                                                <div>
                                                    <div class="font-bold text-sm">Tidak, Hanya Ambil</div>
                                                    <div class="text-xs text-slate-500 mt-0.5">Pembeli wajib datang ke toko</div>
                                                </div>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                                
                                <!-- DETEKSI GPS & ALAMAT OTOMATIS -->
                                <div class="bg-slate-50 p-4 rounded-xl border border-slate-200">
                                    <label class="block text-sm font-bold text-slate-800 mb-1">Deteksi Titik GPS & Alamat Gudang <span class="text-rose-500">*</span></label>
                                    <p class="text-xs text-slate-500 mb-3">Klik tombol di bawah ini saat Anda berada di lokasi produk. Sistem akan otomatis mengisi koordinat dan alamat jalan Anda!</p>
                                    
                                    <input type="hidden" name="latitude" id="lat_produk" value="{{ old('latitude') }}">
                                    <input type="hidden" name="longitude" id="lon_produk" value="{{ old('longitude') }}">
                                    
                                    <div class="flex flex-col sm:flex-row gap-3 mb-3">
                                        <button type="button" onclick="getLokasiProduk()" class="px-5 py-3 bg-brand-900 hover:bg-brand-800 text-white rounded-xl text-sm font-bold shadow-md transition-all flex items-center justify-center gap-2">
                                            <i class="fa-solid fa-location-crosshairs text-lg"></i> Deteksi Lokasi
                                        </button>
                                        <div id="status_gps" class="flex-1 px-4 py-3 bg-white border border-slate-200 text-slate-600 rounded-xl text-sm font-medium flex items-center justify-center sm:justify-start">
                                            <i class="fa-regular fa-clock mr-2 text-amber-500"></i> Lokasi belum dideteksi
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Alamat Lengkap Produk / Gudang <span class="text-rose-500">*</span></label>
                                        <textarea name="alamat" id="alamat_produk" rows="2" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 bg-white text-sm" placeholder="Akan terisi otomatis saat tombol 'Deteksi Lokasi' diklik... (Bisa diedit manual juga)">{{ old('alamat') }}</textarea>
                                    </div>
                                </div>

                            </div>
                        </div>

                        <div class="glass-panel p-6 rounded-2xl shadow-sm">
                            <h2 class="text-lg font-bold text-slate-800 mb-4 border-b border-slate-100 pb-3">Harga & Ketersediaan</h2>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div><label class="block text-sm font-semibold text-slate-700 mb-1">Harga Sewa / Hari <span class="text-rose-500">*</span></label><div class="relative"><div class="absolute inset-y-0 left-0 pl-4 flex items-center text-slate-500">Rp</div><input type="number" name="harga_sewa_harian" required class="w-full pl-12 pr-4 py-2.5 rounded-xl border border-slate-200 focus:border-brand-500 outline-none bg-white"></div></div>
                                <div><label class="block text-sm font-semibold text-slate-700 mb-1">Stok Total <span class="text-rose-500">*</span></label><input type="number" name="stok_total" value="1" required min="1" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-brand-500 outline-none bg-white"></div>
                                <div><label class="block text-sm font-semibold text-slate-700 mb-1">Deposit Jaminan <span class="text-rose-500">*</span></label><div class="relative"><div class="absolute inset-y-0 left-0 pl-4 flex items-center text-slate-500">Rp</div><input type="number" name="deposit" required class="w-full pl-12 pr-4 py-2.5 rounded-xl border border-slate-200 focus:border-brand-500 outline-none bg-white"></div></div>
                                <div><label class="block text-sm font-semibold text-slate-700 mb-1">Denda Terlambat / Hari <span class="text-rose-500">*</span></label><div class="relative"><div class="absolute inset-y-0 left-0 pl-4 flex items-center text-slate-500">Rp</div><input type="number" name="denda_per_hari" required class="w-full pl-12 pr-4 py-2.5 rounded-xl border border-slate-200 focus:border-brand-500 outline-none bg-white"></div></div>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-6">
                        <div class="glass-panel p-6 rounded-2xl shadow-sm">
                            <h2 class="text-lg font-bold text-slate-800 mb-2 border-b border-slate-100 pb-3">Foto Produk <span class="text-rose-500">*</span></h2>
                            <p class="text-xs text-slate-500 mb-4">Pilih foto satu per satu atau sekaligus.</p>
                            <input type="file" name="fotos[]" id="fotos" multiple accept="image/*" class="hidden">
                            <label for="fotos" class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed border-brand-300 rounded-xl bg-brand-50 hover:bg-brand-100 cursor-pointer">
                                <i class="fa-solid fa-cloud-arrow-up text-3xl text-brand-500 mb-2"></i>
                                <span class="text-sm font-semibold text-brand-900">Klik Tambah Foto</span>
                            </label>
                            <div id="preview-container" class="mt-4 grid grid-cols-2 gap-3"></div>
                        </div>

                        <div class="glass-panel p-6 rounded-2xl shadow-sm">
                            <button type="submit" class="w-full py-3 px-4 bg-brand-900 text-white font-bold rounded-xl shadow-sm hover:bg-brand-800"><i class="fa-solid fa-paper-plane mr-2"></i> Ajukan Barang</button>
                        </div>
                    </div>
                </div>
            </form>
        </main>
    </div>

    <script>
        // SCRIPT LOKASI & ALAMAT OTOMATIS (REVERSE GEOCODING)
        async function getLokasiProduk() {
            const statusDiv = document.getElementById('status_gps');
            const alamatInput = document.getElementById('alamat_produk');
            
            statusDiv.innerHTML = '<span class="text-amber-600 font-bold"><i class="fa-solid fa-spinner fa-spin mr-2"></i> Mencari koordinat & alamat...</span>';
            
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    async function(pos) {
                        const lat = pos.coords.latitude;
                        const lon = pos.coords.longitude;
                        
                        document.getElementById('lat_produk').value = lat;
                        document.getElementById('lon_produk').value = lon;
                        
                        // Menerjemahkan Koordinat Menjadi Nama Jalan via OpenStreetMap
                        try {
                            const response = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lon}`);
                            const data = await response.json();
                            if(data.display_name) {
                                alamatInput.value = data.display_name;
                            }
                        } catch(e) {
                            console.log("Gagal mengambil nama jalan, koordinat tetap aman.");
                        }

                        statusDiv.innerHTML = '<span class="text-emerald-600 font-bold"><i class="fa-solid fa-check-circle mr-2"></i> ✅ Lokasi & Alamat Terisi!</span>';
                        statusDiv.className = "flex-1 px-4 py-3 bg-emerald-50 border border-emerald-200 rounded-xl text-sm font-medium flex items-center justify-center sm:justify-start";
                    }, 
                    function() {
                        statusDiv.innerHTML = '<span class="text-rose-600 font-bold"><i class="fa-solid fa-xmark mr-2"></i> Akses GPS Ditolak!</span>';
                        alert("Harap izinkan akses lokasi (GPS) pada browser Anda agar alamat bisa terisi otomatis.");
                    }
                );
            } else {
                alert("Browser Anda tidak mendukung fitur GPS.");
            }
        }

        // SCRIPT FOTO ASLI ANDA
        const inputFotos = document.getElementById('fotos');
        const previewContainer = document.getElementById('preview-container');
        let selectedFiles = []; 
        inputFotos.addEventListener('change', function(e) {
            selectedFiles = selectedFiles.concat(Array.from(e.target.files));
            updateInputFiles(); renderPreviews();
        });
        function updateInputFiles() {
            const dataTransfer = new DataTransfer();
            selectedFiles.forEach(file => dataTransfer.items.add(file));
            inputFotos.files = dataTransfer.files;
        }
        function removeFile(index) {
            selectedFiles.splice(index, 1);
            updateInputFiles(); renderPreviews();
        }
        function renderPreviews() {
            previewContainer.innerHTML = ''; 
            selectedFiles.forEach((file, index) => {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const div = document.createElement('div');
                    div.className = 'relative group rounded-xl overflow-hidden border border-slate-200 h-24 bg-slate-100';
                    const coverBadge = index === 0 ? '<div class="absolute bottom-0 left-0 right-0 bg-emerald-500 text-white text-[10px] text-center py-1 font-bold">COVER</div>' : '';
                    div.innerHTML = `<img src="${e.target.result}" class="w-full h-full object-cover">${coverBadge}
                        <div class="absolute inset-0 bg-black/50 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                            <button type="button" onclick="removeFile(${index})" class="w-8 h-8 rounded-full bg-rose-500 text-white shadow-lg"><i class="fa-solid fa-trash text-xs"></i></button>
                        </div>`;
                    previewContainer.appendChild(div);
                }
                reader.readAsDataURL(file);
            });
        }
    </script>
</body>
</html>