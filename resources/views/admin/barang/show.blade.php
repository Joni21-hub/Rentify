<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Produk: {{ $barang->nama }} - Admin Rentify</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="{{ asset('rentify-theme.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="text-slate-800 bg-slate-50 min-h-screen">

    <header class="bg-white border-b border-slate-200 sticky top-0 z-30 px-6 py-4 flex items-center justify-between shadow-xs">
        <div class="flex items-center space-x-4">
            <a href="{{ route('admin.dashboard') }}" class="w-9 h-9 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 flex items-center justify-center transition">
                <i class="fas fa-arrow-left text-sm"></i>
            </a>
            <div>
                <span class="text-[10px] font-black uppercase tracking-wider bg-sky-100 text-[#0369a1] px-2 py-0.5 rounded-md">Inspeksi Lengkap 100% Data</span>
                <h1 class="text-base font-black text-slate-800 tracking-tight">{{ $barang->nama }}</h1>
            </div>
        </div>

        <div class="flex items-center space-x-2">
            <a href="{{ route('admin.barang.edit', $barang->id) }}" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-black rounded-xl shadow-xs transition flex items-center space-x-1.5">
                <i class="fas fa-pen-to-square"></i>
                <span>Edit God-Mode</span>
            </a>
        </div>
    </header>

    <div class="max-w-5xl mx-auto p-6 md:p-8 space-y-6">
        <div class="bg-white rounded-3xl border border-slate-200 p-6 md:p-8 shadow-xs grid grid-cols-1 md:grid-cols-12 gap-8">
            <div class="md:col-span-5 space-y-4">
                <div class="aspect-square rounded-2xl overflow-hidden border border-slate-200 bg-slate-900 flex items-center justify-center">
                    @if($barang->is_cover_video)
                    <video src="{{ $barang->cover_photo_url }}" class="w-full h-full object-cover" controls></video>
                    @else
                    <img src="{{ $barang->cover_photo_url }}" class="w-full h-full object-cover" onerror="this.src='{{ asset('storage/placeholders/placeholder-barang.png') }}'">
                    @endif
                </div>

                @if($barang->fotos->count() > 0)
                <div class="space-y-2">
                    <p class="text-xs font-bold text-slate-600 uppercase">Galeri Foto Tambahan ({{ $barang->fotos->count() }})</p>
                    <div class="grid grid-cols-3 gap-2">
                        @foreach($barang->fotos as $f)
                        <div class="aspect-square rounded-xl overflow-hidden border border-slate-200 bg-slate-100">
                            @if($f->is_video)
                            <video src="{{ $f->foto_url }}" class="w-full h-full object-cover" muted></video>
                            @else
                            <img src="{{ $f->foto_url }}" class="w-full h-full object-cover" onerror="this.src='{{ asset('storage/placeholders/placeholder-barang.png') }}'">
                            @endif
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>

            <div class="md:col-span-7 space-y-4 text-xs">
                <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100 flex items-center justify-between">
                    <div>
                        <span class="text-[9px] font-black text-slate-400 uppercase">VENDOR PEMILIK</span>
                        <h4 class="text-sm font-black text-slate-800">{{ $barang->vendor?->vendor_name ?? '-' }}</h4>
                        <p class="text-[11px] text-slate-500">{{ $barang->vendor?->name }} • {{ $barang->vendor?->email }}</p>
                    </div>
                    @if($barang->vendor?->whatsapp_vendor)
                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', (string)$barang->vendor->whatsapp_vendor) }}" target="_blank" class="px-3 py-1.5 bg-emerald-50 text-emerald-700 font-bold text-xs rounded-xl border border-emerald-200">
                        <i class="fab fa-whatsapp mr-1"></i> WA Vendor
                    </a>
                    @endif
                </div>

                <div class="grid grid-cols-3 gap-3 text-center">
                    <div class="p-3 bg-sky-50 rounded-xl border border-sky-100">
                        <span class="text-[9px] font-black text-sky-600 uppercase">SEWA / HARI</span>
                        <p class="font-black text-sky-900 text-sm mt-0.5">Rp {{ number_format($barang->harga_sewa_harian, 0, ',', '.') }}</p>
                    </div>
                    <div class="p-3 bg-emerald-50 rounded-xl border border-emerald-100">
                        <span class="text-[9px] font-black text-emerald-600 uppercase">DEPOSIT</span>
                        <p class="font-black text-emerald-900 text-sm mt-0.5">Rp {{ number_format($barang->deposit, 0, ',', '.') }}</p>
                    </div>
                    <div class="p-3 bg-rose-50 rounded-xl border border-rose-100">
                        <span class="text-[9px] font-black text-rose-600 uppercase">DENDA TELAT</span>
                        <p class="font-black text-rose-900 text-sm mt-0.5">Rp {{ number_format($barang->denda_per_hari, 0, ',', '.') }}</p>
                    </div>
                </div>

                <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100 flex items-center justify-between">
                    <span class="font-bold text-slate-700">Kapasitas Stok Gudang:</span>
                    <span class="font-black text-slate-900 text-sm px-3 py-1 bg-white rounded-lg border border-slate-200">{{ $barang->stok_total }} Unit</span>
                </div>

                <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100 space-y-1">
                    <span class="text-[9px] font-black text-slate-400 uppercase">ALAMAT & KOORDINAT</span>
                    <p class="font-medium text-slate-800">{{ $barang->alamat ?: 'Tidak ada alamat tercatat.' }}</p>
                    @if($barang->latitude && $barang->longitude)
                    <p class="text-[10px] font-mono text-slate-400">{{ $barang->latitude }}, {{ $barang->longitude }}</p>
                    @endif
                </div>

                <div>
                    <span class="text-[9px] font-black text-slate-400 uppercase block mb-1">DESKRIPSI PRODUK</span>
                    <p class="p-3 bg-slate-50 rounded-xl border border-slate-100 text-slate-600 leading-relaxed">{{ $barang->deskripsi ?: 'Tidak ada deskripsi.' }}</p>
                </div>
            </div>
        </div>
    </div>

</body>
</html>
