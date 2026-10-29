<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Produk Baru (Admin God-Mode) - Rentify</title>
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
                    <span class="text-[10px] font-black uppercase tracking-wider bg-emerald-100 text-emerald-800 px-2 py-0.5 rounded-md">God-Mode Access</span>
                </div>
                <h1 class="text-base font-black text-slate-800 tracking-tight">Tambah Produk Baru ke Katalog</h1>
            </div>
        </div>

        <div class="flex items-center space-x-3">
            <a href="{{ route('admin.dashboard') }}" class="px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl transition">
                Batal
            </a>
            <button type="submit" form="createProductForm" class="px-5 py-2.5 bg-[#0369a1] hover:bg-[#0284c7] text-white text-xs font-black rounded-xl shadow-md transition flex items-center space-x-1.5">
                <i class="fas fa-plus-circle"></i>
                <span>Terbitkan Produk</span>
            </button>
        </div>
    </header>

    <div class="max-w-5xl mx-auto p-6 md:p-8 space-y-6">

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

        <form id="createProductForm" action="{{ route('admin.barang.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <!-- SECTION 1: PEMILIK VENDOR & STATUS -->
            <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h2 class="text-sm font-black text-slate-800 uppercase tracking-wider flex items-center">
                        <i class="fas fa-shield-halved text-[#0369a1] mr-2"></i> Kepemilikan Vendor & Status
                    </h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Pilih Vendor Pemilik <span class="text-rose-500">*</span></label>
                        <select name="vendor_id" required class="w-full text-xs font-bold text-slate-700 bg-slate-50 border border-slate-200 rounded-xl p-2.5 focus:outline-none focus:border-sky-500">
                            <option value="">-- Pilih Vendor --</option>
                            @foreach($vendors as $v)
                            <option value="{{ $v->id }}" {{ old('vendor_id') == $v->id ? 'selected' : '' }}>
                                {{ $v->vendor_name ?: $v->name }} ({{ $v->email }})
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Status Kurasi / Approval</label>
                        <select name="status_barang" class="w-full text-xs font-bold text-slate-700 bg-slate-50 border border-slate-200 rounded-xl p-2.5 focus:outline-none focus:border-sky-500">
                            <option value="disetujui" selected>Langsung Disetujui (Katalog Aktif)</option>
                            <option value="pending">Pending Review</option>
                            <option value="ditolak">Ditolak</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Status Ketersediaan</label>
                        <select name="status" class="w-full text-xs font-bold text-slate-700 bg-slate-50 border border-slate-200 rounded-xl p-2.5 focus:outline-none focus:border-sky-500">
                            <option value="tersedia" selected>Tersedia</option>
                            <option value="disewa">Sedang Disewa</option>
                            <option value="tidak_tersedia">Tidak Tersedia</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- SECTION 2: DATA UTAMA PRODUK -->
            <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h2 class="text-sm font-black text-slate-800 uppercase tracking-wider flex items-center">
                        <i class="fas fa-boxes text-[#0369a1] mr-2"></i> Data Utama Produk & Tarif
                    </h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Nama Produk <span class="text-rose-500">*</span></label>
                        <input type="text" name="nama" value="{{ (string) old('nama') }}" required placeholder="Contoh: Kamera Sony A7 III Body Only" class="w-full text-xs font-bold text-slate-800 bg-slate-50 border border-slate-200 rounded-xl p-2.5 focus:outline-none focus:border-sky-500">
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Kategori Produk <span class="text-rose-500">*</span></label>
                        <select name="kategori_id" required class="w-full text-xs font-bold text-slate-700 bg-slate-50 border border-slate-200 rounded-xl p-2.5 focus:outline-none focus:border-sky-500">
                            <option value="">-- Pilih Kategori --</option>
                            @foreach($kategoris as $k)
                            <option value="{{ $k->id }}" {{ old('kategori_id') == $k->id ? 'selected' : '' }}>{{ $k->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Sewa / Hari (Rp) <span class="text-rose-500">*</span></label>
                        <input type="number" step="any" name="harga_sewa_harian" value="{{ (string) old('harga_sewa_harian') }}" required placeholder="100000" class="w-full text-xs font-black text-sky-800 bg-sky-50/50 border border-sky-200 rounded-xl p-2.5 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Deposit Jaminan (Rp) <span class="text-rose-500">*</span></label>
                        <input type="number" step="any" name="deposit" value="{{ (string) old('deposit', '0') }}" required placeholder="50000" class="w-full text-xs font-black text-emerald-800 bg-emerald-50/50 border border-emerald-200 rounded-xl p-2.5 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Denda Telat / Hari (Rp) <span class="text-rose-500">*</span></label>
                        <input type="number" step="any" name="denda_per_hari" value="{{ (string) old('denda_per_hari', '0') }}" required placeholder="50000" class="w-full text-xs font-black text-rose-800 bg-rose-50/50 border border-rose-200 rounded-xl p-2.5 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Stok Total Gudang <span class="text-rose-500">*</span></label>
                        <input type="number" name="stok_total" min="0" value="{{ (int) old('stok_total', 1) }}" required class="w-full text-xs font-black text-slate-900 bg-amber-50 border border-amber-300 rounded-xl p-2.5 focus:outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Kondisi Fisik Barang <span class="text-rose-500">*</span></label>
                        <input type="text" name="kondisi" value="{{ (string) old('kondisi', 'Sangat Baik') }}" required placeholder="Sangat Baik / Seperti Baru" class="w-full text-xs font-bold text-slate-700 bg-slate-50 border border-slate-200 rounded-xl p-2.5 focus:outline-none">
                    </div>
                    <div class="flex items-center pt-6">
                        <label class="flex items-center space-x-2 cursor-pointer">
                            <input type="checkbox" name="is_delivery_supported" value="1" checked class="w-4 h-4 text-sky-600 rounded focus:ring-sky-500">
                            <span class="text-xs font-bold text-slate-700">Mendukung Pengiriman Kurir / Antar Jemput</span>
                        </label>
                    </div>
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Deskripsi Lengkap <span class="text-rose-500">*</span></label>
                    <textarea name="deskripsi" rows="4" required placeholder="Deskripsikan fitur, spesifikasi, dan aturan penggunaan produk..." class="w-full text-xs font-medium text-slate-700 bg-slate-50 border border-slate-200 rounded-xl p-3 focus:outline-none">{{ (string) old('deskripsi') }}</textarea>
                </div>
            </div>

            <!-- SECTION 3: LOKASI & FOTO -->
            <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h2 class="text-sm font-black text-slate-800 uppercase tracking-wider flex items-center">
                        <i class="fas fa-location-dot text-rose-500 mr-2"></i> Lokasi & Berkas Media
                    </h2>
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Alamat Penjemputan Barang <span class="text-rose-500">*</span></label>
                    <textarea name="alamat" rows="2" required placeholder="Alamat lengkap lokasi penyimpanan barang..." class="w-full text-xs font-medium text-slate-700 bg-slate-50 border border-slate-200 rounded-xl p-3 focus:outline-none">{{ (string) old('alamat') }}</textarea>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Latitude</label>
                        <input type="text" name="latitude" value="{{ (string) old('latitude') }}" placeholder="-6.200000" class="w-full text-xs font-mono font-bold text-slate-700 bg-slate-50 border border-slate-200 rounded-xl p-2.5 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Longitude</label>
                        <input type="text" name="longitude" value="{{ (string) old('longitude') }}" placeholder="106.816666" class="w-full text-xs font-mono font-bold text-slate-700 bg-slate-50 border border-slate-200 rounded-xl p-2.5 focus:outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Unggah Foto / Video Produk (Foto Pertama = Cover) <span class="text-rose-500">*</span></label>
                    <input type="file" name="fotos[]" multiple required accept="image/*,video/*" class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:bg-sky-50 file:text-sky-700 file:font-bold">
                    <p class="text-[10px] text-slate-400 mt-1">Anda dapat memilih beberapa foto sekaligus. Berkas foto pertama akan dijadikan cover utama katalog.</p>
                </div>
            </div>

            <!-- SUBMIT BUTTON -->
            <div class="flex items-center justify-end space-x-3 p-6 bg-white rounded-2xl border border-slate-200 shadow-xs">
                <a href="{{ route('admin.dashboard') }}" class="px-5 py-2.5 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl transition">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 bg-[#0369a1] hover:bg-[#0284c7] text-white text-xs font-black rounded-xl shadow-md transition flex items-center space-x-2">
                    <i class="fas fa-check-circle"></i>
                    <span>Simpan & Terbitkan Produk (God-Mode)</span>
                </button>
            </div>
        </form>
    </div>

</body>
</html>
