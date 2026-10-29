<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>God-Mode Edit: {{ $barang->nama }} - Admin Rentify</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="{{ asset('rentify-theme.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="text-slate-800 bg-slate-50 min-h-screen">

    <!-- TOPBAR -->
    <header class="bg-white border-b border-slate-200 sticky top-0 z-30 px-6 py-4 flex items-center justify-between shadow-xs">
        <div class="flex items-center space-x-4">
            <a href="{{ route('admin.dashboard') }}" class="w-9 h-9 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 flex items-center justify-center transition">
                <i class="fas fa-arrow-left text-sm"></i>
            </a>
            <div>
                <div class="flex items-center space-x-2">
                    <span class="text-[10px] font-black uppercase tracking-wider bg-rose-100 text-rose-800 px-2 py-0.5 rounded-md">God-Mode Access</span>
                    <span class="text-xs font-bold text-slate-400">ID #{{ $barang->id }}</span>
                </div>
                <h1 class="text-base font-black text-slate-800 tracking-tight truncate max-w-md">{{ $barang->nama }}</h1>
            </div>
        </div>

        <div class="flex items-center space-x-3">
            <a href="{{ route('admin.dashboard') }}" class="px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl transition">
                Batal
            </a>
            <button type="submit" form="editProductForm" class="px-5 py-2.5 bg-[#0369a1] hover:bg-[#0284c7] text-white text-xs font-black rounded-xl shadow-md transition flex items-center space-x-1.5">
                <i class="fas fa-save"></i>
                <span>Simpan Perubahan Penuh</span>
            </button>
        </div>
    </header>

    <div class="max-w-6xl mx-auto p-6 md:p-8 space-y-6">

        @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl text-xs font-bold flex items-center shadow-xs">
            <i class="fas fa-check-circle text-emerald-500 mr-2 text-base"></i>{{ session('success') }}
        </div>
        @endif
        @if(session('error'))
        <div class="bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 rounded-xl text-xs font-bold flex items-center shadow-xs">
            <i class="fas fa-exclamation-circle text-rose-500 mr-2 text-base"></i>{{ session('error') }}
        </div>
        @endif
        @if($errors->any())
        <div class="bg-rose-50 border border-rose-200 text-rose-800 p-4 rounded-xl text-xs">
            <p class="font-bold mb-1"><i class="fas fa-triangle-exclamation mr-1"></i> Terdapat kesalahan pada input:</p>
            <ul class="list-disc list-inside space-y-0.5">
                @foreach($errors->all() as $err)
                <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form id="editProductForm" action="{{ route('admin.barang.update', $barang->id) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- SECTION 1: KEPEMILIKAN & STATUS MODERASI -->
            <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h2 class="text-sm font-black text-slate-800 uppercase tracking-wider flex items-center">
                        <i class="fas fa-shield-halved text-[#0369a1] mr-2"></i> Kepemilikan Vendor & Status Approval
                    </h2>
                    <span class="text-[11px] font-bold text-slate-500 bg-slate-100 px-2.5 py-1 rounded-lg">Admin Override Controls</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Pemilik Vendor</label>
                        <select name="vendor_id" class="w-full text-xs font-bold text-slate-700 bg-slate-50 border border-slate-200 rounded-xl p-2.5 focus:outline-none focus:border-sky-500">
                            @foreach($vendors as $v)
                            <option value="{{ $v->id }}" {{ $barang->vendor_id == $v->id ? 'selected' : '' }}>
                                {{ $v->vendor_name ?: $v->name }} ({{ $v->email }})
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Status Kurasi / Approval</label>
                        <select name="status_barang" class="w-full text-xs font-bold text-slate-700 bg-slate-50 border border-slate-200 rounded-xl p-2.5 focus:outline-none focus:border-sky-500">
                            <option value="disetujui" {{ $barang->status_barang == 'disetujui' ? 'selected' : '' }}>Disetujui (Tampil di Katalog)</option>
                            <option value="pending" {{ $barang->status_barang == 'pending' ? 'selected' : '' }}>Pending (Menunggu Review)</option>
                            <option value="ditolak" {{ $barang->status_barang == 'ditolak' ? 'selected' : '' }}>Ditolak (Disembunyikan)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Status Ketersediaan</label>
                        <select name="status" class="w-full text-xs font-bold text-slate-700 bg-slate-50 border border-slate-200 rounded-xl p-2.5 focus:outline-none focus:border-sky-500">
                            <option value="tersedia" {{ $barang->status == 'tersedia' ? 'selected' : '' }}>Tersedia (Bisa Disewa)</option>
                            <option value="disewa" {{ $barang->status == 'disewa' ? 'selected' : '' }}>Sedang Disewa</option>
                            <option value="tidak_tersedia" {{ $barang->status == 'tidak_tersedia' ? 'selected' : '' }}>Tidak Tersedia / Habis</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- SECTION 2: INFORMASI UTAMA & HARGA & STOK -->
            <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h2 class="text-sm font-black text-slate-800 uppercase tracking-wider flex items-center">
                        <i class="fas fa-boxes text-[#0369a1] mr-2"></i> Data Utama Produk & Tarif
                    </h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Nama Produk <span class="text-rose-500">*</span></label>
                        <input type="text" name="nama" value="{{ (string) old('nama', $barang->nama) }}" required class="w-full text-xs font-bold text-slate-800 bg-slate-50 border border-slate-200 rounded-xl p-2.5 focus:outline-none focus:border-sky-500">
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Kategori Produk <span class="text-rose-500">*</span></label>
                        <select name="kategori_id" required class="w-full text-xs font-bold text-slate-700 bg-slate-50 border border-slate-200 rounded-xl p-2.5 focus:outline-none focus:border-sky-500">
                            @foreach($kategoris as $k)
                            <option value="{{ $k->id }}" {{ $barang->kategori_id == $k->id ? 'selected' : '' }}>{{ $k->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Sewa / Hari (Rp) <span class="text-rose-500">*</span></label>
                        <input type="number" step="any" name="harga_sewa_harian" value="{{ (float) old('harga_sewa_harian', (float)$barang->harga_sewa_harian) }}" required class="w-full text-xs font-black text-sky-800 bg-sky-50/50 border border-sky-200 rounded-xl p-2.5 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Deposit Jaminan (Rp) <span class="text-rose-500">*</span></label>
                        <input type="number" step="any" name="deposit" value="{{ (float) old('deposit', (float)$barang->deposit) }}" required class="w-full text-xs font-black text-emerald-800 bg-emerald-50/50 border border-emerald-200 rounded-xl p-2.5 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Denda Telat / Hari (Rp) <span class="text-rose-500">*</span></label>
                        <input type="number" step="any" name="denda_per_hari" value="{{ (float) old('denda_per_hari', (float)$barang->denda_per_hari) }}" required class="w-full text-xs font-black text-rose-800 bg-rose-50/50 border border-rose-200 rounded-xl p-2.5 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Stok Total Gudang <span class="text-rose-500">*</span></label>
                        <input type="number" name="stok_total" min="0" value="{{ (int) old('stok_total', $barang->stok_total) }}" required class="w-full text-xs font-black text-slate-900 bg-amber-50 border border-amber-300 rounded-xl p-2.5 focus:outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Kondisi Fisik Barang <span class="text-rose-500">*</span></label>
                        <input type="text" name="kondisi" value="{{ (string) old('kondisi', $barang->kondisi) }}" required placeholder="Misal: Sangat Baik / Seperti Baru / Grade A" class="w-full text-xs font-bold text-slate-700 bg-slate-50 border border-slate-200 rounded-xl p-2.5 focus:outline-none">
                    </div>
                    <div class="flex items-center pt-6">
                        <label class="flex items-center space-x-2 cursor-pointer">
                            <input type="checkbox" name="is_delivery_supported" value="1" {{ $barang->is_delivery_supported ? 'checked' : '' }} class="w-4 h-4 text-sky-600 rounded focus:ring-sky-500">
                            <span class="text-xs font-bold text-slate-700">Mendukung Layanan Kurir / Antar Jemput ke Alamat Pelanggan</span>
                        </label>
                    </div>
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Deskripsi Lengkap Produk <span class="text-rose-500">*</span></label>
                    <textarea name="deskripsi" rows="4" required class="w-full text-xs font-medium text-slate-700 bg-slate-50 border border-slate-200 rounded-xl p-3 focus:outline-none focus:border-sky-500">{{ (string) old('deskripsi', $barang->deskripsi) }}</textarea>
                </div>
            </div>

            <!-- SECTION 3: LOKASI & TITIK KOORDINAT -->
            <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h2 class="text-sm font-black text-slate-800 uppercase tracking-wider flex items-center">
                        <i class="fas fa-location-dot text-rose-500 mr-2"></i> Lokasi & Titik Koordinat Pengambilan
                    </h2>
                    @if($barang->latitude && $barang->longitude)
                    <a href="https://maps.google.com/?q={{ $barang->latitude }},{{ $barang->longitude }}" target="_blank" class="text-xs font-bold text-sky-600 hover:underline flex items-center">
                        <i class="fas fa-map-marked-alt mr-1"></i> Buka Google Maps
                    </a>
                    @endif
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Alamat Lengkap <span class="text-rose-500">*</span></label>
                    <textarea name="alamat" rows="2" required class="w-full text-xs font-medium text-slate-700 bg-slate-50 border border-slate-200 rounded-xl p-3 focus:outline-none">{{ (string) old('alamat', $barang->alamat) }}</textarea>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Latitude</label>
                        <input type="text" name="latitude" value="{{ (string) old('latitude', $barang->latitude) }}" placeholder="-6.200000" class="w-full text-xs font-mono font-bold text-slate-700 bg-slate-50 border border-slate-200 rounded-xl p-2.5 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Longitude</label>
                        <input type="text" name="longitude" value="{{ (string) old('longitude', $barang->longitude) }}" placeholder="106.816666" class="w-full text-xs font-mono font-bold text-slate-700 bg-slate-50 border border-slate-200 rounded-xl p-2.5 focus:outline-none">
                    </div>
                </div>
            </div>

            <!-- SECTION 4: SPESIFIKASI TEKNIS & KELENGKAPAN -->
            <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h2 class="text-sm font-black text-slate-800 uppercase tracking-wider flex items-center">
                        <i class="fas fa-sliders text-indigo-500 mr-2"></i> Spesifikasi Teknis & Fasilitas
                    </h2>
                </div>

                @php
                    $specs = is_array($barang->spesifikasi) ? $barang->spesifikasi : (is_string($barang->spesifikasi) ? (json_decode($barang->spesifikasi, true) ?: []) : []);
                @endphp

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Merek / Brand</label>
                        <input type="text" name="spesifikasi[brand]" value="{{ $specs['brand'] ?? $specs['merek'] ?? '' }}" placeholder="Misal: Sony, Canon, Honda" class="w-full text-xs font-bold text-slate-700 bg-slate-50 border border-slate-200 rounded-xl p-2.5 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Model / Seri</label>
                        <input type="text" name="spesifikasi[model]" value="{{ $specs['model'] ?? $specs['seri'] ?? '' }}" placeholder="Misal: A7 III, Beat 2024" class="w-full text-xs font-bold text-slate-700 bg-slate-50 border border-slate-200 rounded-xl p-2.5 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Tarif Bulanan (Khusus Kos/Kamar)</label>
                        <input type="number" step="any" name="spesifikasi[harga_bulanan]" value="{{ $specs['harga_bulanan'] ?? '' }}" placeholder="Rp (opsional)" class="w-full text-xs font-bold text-slate-700 bg-slate-50 border border-slate-200 rounded-xl p-2.5 focus:outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Kelengkapan / Aksesoris yang Disertakan</label>
                    <input type="text" name="spesifikasi[kelengkapan]" value="{{ is_array($specs['kelengkapan'] ?? null) ? implode(', ', $specs['kelengkapan']) : ($specs['kelengkapan'] ?? '') }}" placeholder="Misal: Charger, Baterai Cadangan, Tas, Helm 2 Unit" class="w-full text-xs font-medium text-slate-700 bg-slate-50 border border-slate-200 rounded-xl p-2.5 focus:outline-none">
                </div>
            </div>

            <!-- SECTION 5: MANAJEMEN MEDIA & FOTO GALERI -->
            <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h2 class="text-sm font-black text-slate-800 uppercase tracking-wider flex items-center">
                        <i class="fas fa-images text-emerald-500 mr-2"></i> Foto Cover Utama & Galeri Media
                    </h2>
                    <span class="text-xs text-slate-400">Total Foto: {{ 1 + $barang->fotos->count() }} Berkas</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- COVER PHOTO -->
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 uppercase mb-2">Foto Cover Saat Ini</label>
                        <div class="w-full aspect-video rounded-xl overflow-hidden border border-slate-200 bg-slate-100 mb-3 flex items-center justify-center">
                            @if($barang->is_cover_video)
                            <video src="{{ $barang->cover_photo_url }}" class="w-full h-full object-cover" controls></video>
                            @else
                            <img src="{{ $barang->cover_photo_url }}" class="w-full h-full object-cover" onerror="this.src='{{ asset('storage/placeholders/placeholder-barang.png') }}'">
                            @endif
                        </div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Ganti Cover Photo (Opsional)</label>
                        <input type="file" name="cover_photo" accept="image/*,video/*" class="w-full text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:bg-sky-50 file:text-sky-700 file:font-bold">
                    </div>

                    <!-- GALERI FOTO TAMBAHAN -->
                    <div class="md:col-span-2 space-y-3">
                        <label class="block text-[11px] font-bold text-slate-600 uppercase">Foto Galeri Tambahan yang Terdaftar</label>
                        
                        @if($barang->fotos->count() > 0)
                        <div class="grid grid-cols-3 sm:grid-cols-4 gap-3">
                            @foreach($barang->fotos as $f)
                            <div class="relative group border border-slate-200 rounded-xl overflow-hidden bg-slate-50 aspect-square">
                                @if($f->is_video)
                                <video src="{{ $f->foto_url }}" class="w-full h-full object-cover" muted></video>
                                <span class="absolute top-1 left-1 bg-black/70 text-white text-[9px] px-1 rounded font-bold">Video</span>
                                @else
                                <img src="{{ $f->foto_url }}" class="w-full h-full object-cover" onerror="this.src='{{ asset('storage/placeholders/placeholder-barang.png') }}'">
                                @endif
                                
                                <div class="absolute inset-0 bg-rose-900/60 opacity-0 group-hover:opacity-100 transition flex items-center justify-center p-2">
                                    <label class="flex items-center space-x-1.5 text-white text-xs font-bold cursor-pointer bg-rose-600 hover:bg-rose-700 px-2 py-1 rounded-md">
                                        <input type="checkbox" name="delete_fotos[]" value="{{ $f->id }}" class="rounded text-rose-500">
                                        <span>Hapus</span>
                                    </label>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        <p class="text-[10px] text-slate-400 italic">* Centang 'Hapus' pada foto yang ingin dihapus dari galeri saat menyimpan.</p>
                        @else
                        <div class="p-6 text-center text-slate-400 text-xs bg-slate-50 rounded-xl border border-dashed border-slate-200">
                            Belum ada foto galeri tambahan yang diunggah.
                        </div>
                        @endif

                        <div class="pt-3 border-t border-slate-100">
                            <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Tambah Foto Galeri Baru (Opsional)</label>
                            <input type="file" name="fotos[]" multiple accept="image/*,video/*" class="w-full text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:bg-emerald-50 file:text-emerald-700 file:font-bold">
                        </div>
                    </div>
                </div>
            </div>

            <!-- FOOTER ACTION BAR -->
            <div class="flex items-center justify-between p-6 bg-white rounded-2xl border border-slate-200 shadow-xs">
                <button type="button" onclick="if(confirm('Hapus produk ini permanen dari sistem? Tindakan tidak dapat dibatalkan.')) { document.getElementById('deletePermanentForm').submit(); }" class="px-4 py-2.5 bg-rose-50 hover:bg-rose-100 text-rose-600 rounded-xl text-xs font-bold transition">
                    <i class="far fa-trash-alt mr-1"></i> Hapus Produk Permanen
                </button>

                <div class="flex items-center space-x-3">
                    <a href="{{ route('admin.dashboard') }}" class="px-4 py-2.5 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl transition">
                        Batal
                    </a>
                    <button type="submit" class="px-6 py-2.5 bg-[#0369a1] hover:bg-[#0284c7] text-white text-xs font-black rounded-xl shadow-md transition flex items-center space-x-2">
                        <i class="fas fa-check-circle"></i>
                        <span>Simpan Perubahan Penuh (God-Mode)</span>
                    </button>
                </div>
            </div>
        </form>

        <form id="deletePermanentForm" action="{{ route('admin.barang.destroy', $barang->id) }}" method="POST" class="hidden">
            @csrf
            @method('DELETE')
        </form>

    </div>

</body>
</html>
