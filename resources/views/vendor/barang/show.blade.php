@extends('layouts.vendor')

@section('title', 'Detail Produk — Vendor Rentify')

@section('content')
<div class="px-3.5 sm:px-6 lg:px-8 py-4 sm:py-6 max-w-5xl mx-auto space-y-4">
    
    <!-- Top Action Bar -->
    <div class="flex items-center justify-between gap-3">
        <a href="{{ route('vendor.barang.index') }}" class="inline-flex items-center gap-1.5 px-3 py-2 bg-white border border-slate-200/80 rounded-xl text-xs font-bold text-slate-700 hover:text-sky-600 transition shadow-xs">
            <i class="fa-solid fa-arrow-left text-xs"></i>
            <span>Daftar Produk</span>
        </a>

        <div class="flex items-center gap-2">
            <!-- Tombol AI Copywriter & Promosi Iklan -->
            <button type="button" onclick="openMarketingModal()" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-white rounded-xl text-xs font-black shadow-xs transition active:scale-95">
                <i class="fa-solid fa-wand-magic-sparkles text-xs"></i>
                <span class="hidden sm:inline">Promosikan Produk</span>
                <span class="sm:hidden">Promosi</span>
            </button>

            <a href="{{ route('vendor.barang.edit', $barang->id) }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-sky-500 hover:bg-sky-600 text-white rounded-xl text-xs font-black shadow-xs transition active:scale-95">
                <i class="fa-solid fa-pen-to-square text-xs"></i>
                <span>Edit Produk</span>
            </a>

            @if($barang->is_approved == 1 || $barang->status_barang == 'disetujui')
                <span class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-emerald-50 text-emerald-700 text-xs font-black rounded-xl">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Aktif
                </span>
            @else
                <span class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-amber-50 text-amber-700 text-xs font-black rounded-xl">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span> Ditinjau
                </span>
            @endif
        </div>
    </div>

    <!-- Alert Notifikasi -->
    @if(session('success'))
        <div class="px-4 py-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl flex items-center gap-2.5 text-xs font-bold shadow-xs">
            <i class="fa-solid fa-circle-check text-emerald-600 text-sm"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div class="px-4 py-3 bg-rose-50 border border-rose-200 text-rose-800 rounded-2xl flex items-center gap-2.5 text-xs font-bold shadow-xs">
            <i class="fa-solid fa-triangle-exclamation text-rose-600 text-sm"></i>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    @php
        $cover = $barang->cover_photo_url;
    @endphp

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6">
        
        <!-- Galeri & Foto Produk -->
        <div class="lg:col-span-1 space-y-4">
            <!-- Cover Photo Card -->
            <div class="bg-white p-3.5 rounded-2xl sm:rounded-3xl border border-slate-200/80 shadow-xs space-y-2">
                <div class="flex items-center justify-between pb-1">
                    <span class="text-[11px] font-black text-slate-700 uppercase tracking-wider">Foto Cover Utama</span>
                    <span class="text-[10px] font-bold text-sky-600 bg-sky-50 px-2 py-0.5 rounded-md">Cover</span>
                </div>
                <div class="aspect-square rounded-2xl overflow-hidden bg-slate-50 border border-slate-100 flex items-center justify-center">
                    @if($cover)
                        @if(\App\Services\StorageService::isVideoMedia($cover))
                            <video src="{{ $cover }}" class="w-full h-full object-cover" controls playsinline></video>
                        @else
                            <img src="{{ $cover }}" alt="{{ $barang->nama }}" class="w-full h-full object-cover" onerror="this.onerror=null; this.src='{{ asset('storage/placeholders/placeholder-barang.png') }}';">
                        @endif
                    @else
                        <i class="fa-solid fa-image text-3xl text-slate-300"></i>
                    @endif
                </div>
            </div>

            <!-- Galeri Foto Tambahan -->
            <div class="bg-white p-3.5 rounded-2xl sm:rounded-3xl border border-slate-200/80 shadow-xs space-y-3">
                <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                    <h3 class="text-xs font-black text-slate-800 uppercase tracking-wider">
                        Foto Galeri ({{ isset($fotoTambahans) ? $fotoTambahans->count() : 0 }})
                    </h3>
                </div>

                @if(isset($fotoTambahans) && $fotoTambahans->count() > 0)
                    <div class="grid grid-cols-2 gap-2.5">
                        @foreach($fotoTambahans as $foto)
                            @php
                                $fpath = $foto->foto_url;
                            @endphp
                            <div class="relative group rounded-xl overflow-hidden bg-slate-100 border border-slate-200 aspect-square">
                                @if(\App\Services\StorageService::isVideoMedia($foto->foto_path))
                                    <video src="{{ $fpath }}" class="w-full h-full object-cover" muted playsinline></video>
                                    <div class="absolute top-1.5 left-1.5 px-1.5 py-0.5 bg-slate-900/80 text-white text-[9px] font-bold rounded flex items-center gap-1 z-10"><i class="fa-solid fa-video text-rose-400 text-[8px]"></i> Video</div>
                                @else
                                    <img src="{{ $fpath }}" class="w-full h-full object-cover" onerror="this.onerror=null; this.src='{{ asset('storage/placeholders/placeholder-barang.png') }}';">
                                @endif
                                
                                <!-- Action Buttons Overlay -->
                                <div class="absolute inset-0 bg-slate-900/60 opacity-0 group-hover:opacity-100 transition-opacity flex flex-col items-center justify-center gap-1.5 p-1">
                                    <!-- Set as cover -->
                                    <form action="{{ route('vendor.barang.fotos.cover', [$barang->id, $foto->id]) }}" method="POST" class="w-full">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="w-full py-1 px-1.5 bg-sky-500 hover:bg-sky-600 text-white rounded text-[9px] font-black tracking-tight transition" title="Jadikan Cover Utama">
                                            Jadikan Cover
                                        </button>
                                    </form>

                                    <!-- Delete photo -->
                                    <form action="{{ route('vendor.fotos.destroy', $foto->id) }}" method="POST" onsubmit="return confirm('Hapus foto ini?');" class="w-full">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="w-full py-1 px-1.5 bg-rose-500 hover:bg-rose-600 text-white rounded text-[9px] font-black tracking-tight transition" title="Hapus Foto">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-[11px] text-slate-400 font-medium text-center py-2">Belum ada foto galeri tambahan.</p>
                @endif

                <!-- Form Upload Foto Tambahan Cepat -->
                <form action="{{ route('vendor.barang.fotos.upload', $barang->id) }}" method="POST" enctype="multipart/form-data" class="pt-2 border-t border-slate-100 space-y-2">
                    @csrf
                    <input type="file" name="fotos[]" id="inputFotoTambah" multiple accept="image/*,video/*,.heic,.heif,.avif,.webp,.mp4,.mov,.mkv,.webm" class="hidden" onchange="this.form.submit()">
                    <label for="inputFotoTambah" class="w-full py-2 px-3 bg-slate-50 hover:bg-slate-100 border border-dashed border-slate-300 rounded-xl text-center text-xs font-bold text-slate-600 flex items-center justify-center gap-2 cursor-pointer transition">
                        <i class="fa-solid fa-cloud-arrow-up text-sky-500"></i>
                        <span>Tambah Media Galeri (Foto/Video)</span>
                    </label>
                </form>
            </div>
        </div>

        <!-- Detail Data Produk -->
        <div class="lg:col-span-2 bg-white p-4 sm:p-6 rounded-2xl sm:rounded-3xl border border-slate-200/80 shadow-xs space-y-4">
            
            <div class="flex items-center justify-between gap-2 border-b border-slate-100 pb-3">
                <span class="text-xs font-bold text-sky-600 bg-sky-50 px-2.5 py-1 rounded-lg">
                    {{ $barang->kategori->nama ?? 'Tanpa Kategori' }}
                </span>
                <span class="text-xs font-black text-slate-700">
                    Stok: {{ $barang->stok_total }} unit
                </span>
            </div>

            <div>
                <h1 class="text-lg sm:text-2xl font-black text-slate-800 tracking-tight">{{ $barang->nama }}</h1>
            </div>

            <!-- Tarif Sewa Card -->
            <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200/70 flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500">Tarif Sewa Harian</span>
                <div class="text-right">
                    <span class="text-xl sm:text-2xl font-black text-sky-600">Rp {{ number_format($barang->harga_sewa_harian, 0, ',', '.') }}</span>
                    <span class="text-xs text-slate-400 font-medium">/hari</span>
                </div>
            </div>

            <!-- Ketentuan Tambahan (Deposit & Denda) -->
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5 text-xs">
                <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-1">Kondisi</span>
                    <strong class="text-slate-800 text-xs">{{ $barang->kondisi }}</strong>
                </div>
                <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-1">Deposit</span>
                    <strong class="text-slate-800 text-xs">Rp {{ number_format($barang->deposit, 0, ',', '.') }}</strong>
                </div>
                <div class="p-3 rounded-xl bg-slate-50 border border-slate-100 col-span-2 sm:col-span-1">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-1">Denda Terlambat</span>
                    <strong class="text-rose-600 text-xs">Rp {{ number_format($barang->denda_per_hari, 0, ',', '.') }}/hr</strong>
                </div>
            </div>

            <!-- Layanan Pengantaran & Lokasi -->
            <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-100 text-xs space-y-1.5">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Opsi Pengambilan</span>
                    <span class="px-2 py-0.5 rounded text-[10px] font-black {{ $barang->is_delivery_supported ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-700' }}">
                        {{ $barang->is_delivery_supported ? 'Bisa Diantar / Ambil di Toko' : 'Hanya Ambil di Toko' }}
                    </span>
                </div>
                @if($barang->alamat)
                    <div class="pt-1">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Alamat / Titik Barang</span>
                        <p class="text-xs text-slate-700 font-semibold mt-0.5">{{ $barang->alamat }}</p>
                    </div>
                @endif
            </div>

            <!-- Deskripsi -->
            <div class="pt-2">
                <h3 class="text-xs font-black text-slate-800 uppercase tracking-wider mb-2">Deskripsi Produk</h3>
                <div class="text-xs text-slate-600 leading-relaxed bg-slate-50 p-3.5 rounded-xl border border-slate-100 whitespace-pre-line">
                    {{ $barang->deskripsi }}
                </div>
            </div>

        </div>

    </div>

</div>

<!-- ============================================================== -->
<!-- MODAL AI MARKETING COPYWRITER & PROMOSI PRODUK -->
<!-- ============================================================== -->
<div id="modalMarketing" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs hidden items-center justify-center p-3.5 sm:p-4 overflow-y-auto">
    <div class="bg-white rounded-3xl max-w-2xl w-full p-4 sm:p-6 shadow-2xl border border-slate-200/80 my-auto space-y-4 max-h-[90vh] flex flex-col">
        <!-- Modal Header -->
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-amber-500 to-orange-500 text-white flex items-center justify-center text-sm shadow-xs">
                    <i class="fa-solid fa-wand-magic-sparkles"></i>
                </div>
                <div>
                    <h3 class="text-sm sm:text-base font-black text-slate-800">Rentify AI Copywriter</h3>
                    <p class="text-[11px] text-slate-400 font-medium">Buat naskah promosi WhatsApp, Instagram, dan TikTok dalam hitungan detik</p>
                </div>
            </div>
            <button type="button" onclick="closeMarketingModal()" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-500 flex items-center justify-center transition">
                <i class="fa-solid fa-xmark text-sm"></i>
            </button>
        </div>

        <div class="overflow-y-auto space-y-4 pr-1 flex-1">
            <!-- Form Pengaturan Copywriting -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5 bg-slate-50 p-3 rounded-2xl border border-slate-100 text-xs">
                <div>
                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Gaya Bahasa (Tone)</label>
                    <select id="mktTone" class="w-full px-2.5 py-2 rounded-xl bg-white border border-slate-200 font-bold text-slate-700 text-xs focus:outline-none focus:border-sky-500">
                        <option value="fomo">🔥 FOMO / Urgensi Terbatas</option>
                        <option value="casual">😊 Santai & Akrab</option>
                        <option value="solution">💡 Solusi Hemat Beli vs Sewa</option>
                        <option value="professional">💼 Profesional & Terpercaya</option>
                    </select>
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Catatan Promo (Opsional)</label>
                    <input type="text" id="mktDiskon" placeholder="Contoh: Diskon 20% Minggu Ini" class="w-full px-2.5 py-2 rounded-xl bg-white border border-slate-200 font-medium text-slate-700 text-xs focus:outline-none focus:border-sky-500">
                </div>
                <div class="flex items-end">
                    <button type="button" id="btnGenMarketing" onclick="generateCopywriting()" class="w-full py-2 bg-gradient-to-r from-sky-500 to-blue-600 hover:from-sky-600 text-white font-black text-xs rounded-xl shadow-xs transition active:scale-95 flex items-center justify-center gap-1.5">
                        <i class="fa-solid fa-arrows-rotate text-xs"></i>
                        <span>Generate Naskah</span>
                    </button>
                </div>
            </div>

            <!-- Loading State -->
            <div id="mktLoading" class="hidden py-8 text-center space-y-2">
                <div class="w-8 h-8 border-3 border-sky-500 border-t-transparent rounded-full animate-spin mx-auto"></div>
                <p class="text-xs font-bold text-slate-600">AI Copywriter sedang meracik naskah iklan persuasif...</p>
            </div>

            <!-- Tab Output Copywriting -->
            <div id="mktResult" class="space-y-3">
                <!-- Tab Selector -->
                <div class="flex items-center gap-1.5 border-b border-slate-100 pb-2">
                    <button type="button" onclick="switchMktTab('wa')" id="tabBtnWa" class="px-3 py-1.5 rounded-xl text-xs font-black bg-emerald-50 text-emerald-700 border border-emerald-200/60 transition flex items-center gap-1.5">
                        <i class="fa-brands fa-whatsapp text-emerald-600"></i>
                        <span>WhatsApp Broadcast</span>
                    </button>
                    <button type="button" onclick="switchMktTab('ig')" id="tabBtnIg" class="px-3 py-1.5 rounded-xl text-xs font-bold bg-slate-50 text-slate-600 hover:bg-slate-100 transition flex items-center gap-1.5">
                        <i class="fa-brands fa-instagram text-pink-600"></i>
                        <span>Instagram / Reels</span>
                    </button>
                    <button type="button" onclick="switchMktTab('tt')" id="tabBtnTt" class="px-3 py-1.5 rounded-xl text-xs font-bold bg-slate-50 text-slate-600 hover:bg-slate-100 transition flex items-center gap-1.5">
                        <i class="fa-brands fa-tiktok text-slate-900"></i>
                        <span>TikTok / Hook 3s</span>
                    </button>
                </div>

                <!-- Konten Tab WhatsApp -->
                <div id="tabContentWa" class="space-y-2">
                    <div class="relative">
                        <textarea id="txtWa" rows="8" readonly class="w-full p-3.5 rounded-2xl bg-slate-50 border border-slate-200 text-xs font-mono text-slate-700 leading-relaxed focus:outline-none"></textarea>
                        <button type="button" onclick="copyMktText('txtWa', 'Teks WhatsApp')" class="absolute top-2.5 right-2.5 px-3 py-1.5 bg-white border border-slate-200 text-slate-700 hover:text-emerald-600 rounded-xl text-[11px] font-bold shadow-xs transition active:scale-95 flex items-center gap-1.5">
                            <i class="fa-regular fa-copy"></i>
                            <span>Salin Pesan WA</span>
                        </button>
                    </div>
                </div>

                <!-- Konten Tab Instagram -->
                <div id="tabContentIg" class="hidden space-y-2">
                    <div class="relative">
                        <textarea id="txtIg" rows="8" readonly class="w-full p-3.5 rounded-2xl bg-slate-50 border border-slate-200 text-xs font-mono text-slate-700 leading-relaxed focus:outline-none"></textarea>
                        <button type="button" onclick="copyMktText('txtIg', 'Caption Instagram')" class="absolute top-2.5 right-2.5 px-3 py-1.5 bg-white border border-slate-200 text-slate-700 hover:text-pink-600 rounded-xl text-[11px] font-bold shadow-xs transition active:scale-95 flex items-center gap-1.5">
                            <i class="fa-regular fa-copy"></i>
                            <span>Salin Caption IG</span>
                        </button>
                    </div>
                </div>

                <!-- Konten Tab TikTok -->
                <div id="tabContentTt" class="hidden space-y-2">
                    <div class="relative">
                        <textarea id="txtTt" rows="8" readonly class="w-full p-3.5 rounded-2xl bg-slate-50 border border-slate-200 text-xs font-mono text-slate-700 leading-relaxed focus:outline-none"></textarea>
                        <button type="button" onclick="copyMktText('txtTt', 'Naskah TikTok')" class="absolute top-2.5 right-2.5 px-3 py-1.5 bg-white border border-slate-200 text-slate-700 hover:text-slate-900 rounded-xl text-[11px] font-bold shadow-xs transition active:scale-95 flex items-center gap-1.5">
                            <i class="fa-regular fa-copy"></i>
                            <span>Salin Naskah TikTok</span>
                        </button>
                    </div>
                </div>

                <!-- Engine Source Badge -->
                <div class="flex items-center justify-between text-[10px] text-slate-400 pt-1">
                    <span id="mktEngineBadge">Ditenagai oleh Rentify AI Copywriter</span>
                    <span>Slogan: "Sewa gampang, cuan datang!"</span>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function openMarketingModal() {
        const modal = document.getElementById('modalMarketing');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        // Jika belum pernah di-generate, otomatis jalankan sekali
        if (!document.getElementById('txtWa').value) {
            generateCopywriting();
        }
    }

    function closeMarketingModal() {
        const modal = document.getElementById('modalMarketing');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    function switchMktTab(tab) {
        // Reset classes
        const tabs = ['wa', 'ig', 'tt'];
        tabs.forEach(t => {
            const btn = document.getElementById('tabBtn' + t.charAt(0).toUpperCase() + t.slice(1));
            const content = document.getElementById('tabContent' + t.charAt(0).toUpperCase() + t.slice(1));
            if (t === tab) {
                btn.className = 'px-3 py-1.5 rounded-xl text-xs font-black bg-sky-50 text-sky-700 border border-sky-200/60 transition flex items-center gap-1.5';
                content.classList.remove('hidden');
            } else {
                btn.className = 'px-3 py-1.5 rounded-xl text-xs font-bold bg-slate-50 text-slate-600 hover:bg-slate-100 transition flex items-center gap-1.5';
                content.classList.add('hidden');
            }
        });
    }

    async function generateCopywriting() {
        const btn = document.getElementById('btnGenMarketing');
        const loading = document.getElementById('mktLoading');
        const result = document.getElementById('mktResult');
        const tone = document.getElementById('mktTone').value;
        const diskon = document.getElementById('mktDiskon').value;

        btn.disabled = true;
        btn.classList.add('opacity-50');
        loading.classList.remove('hidden');
        result.classList.add('opacity-30');

        try {
            const res = await fetch("{{ route('vendor.barang.marketing_copywriting', $barang->id) }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    tone: tone,
                    diskon: diskon
                })
            });

            const json = await res.json();
            if (json.success && json.data) {
                const d = json.data;
                document.getElementById('txtWa').value = d.whatsapp || '';
                document.getElementById('txtIg').value = d.instagram || '';
                document.getElementById('txtTt').value = d.tiktok || '';
                if (d.ai_source) {
                    document.getElementById('mktEngineBadge').innerText = 'Ditenagai oleh ' + d.ai_source;
                }
            }
        } catch (e) {
            console.error('Error generating copywriting:', e);
            alert('Gagal membuat naskah promosi. Silakan coba kembali.');
        } finally {
            btn.disabled = false;
            btn.classList.remove('opacity-50');
            loading.classList.add('hidden');
            result.classList.remove('opacity-30');
        }
    }

    function copyMktText(elementId, label) {
        const el = document.getElementById(elementId);
        el.select();
        navigator.clipboard.writeText(el.value).then(() => {
            alert(label + ' berhasil disalin ke clipboard!');
        }).catch(() => {
            document.execCommand('copy');
            alert(label + ' berhasil disalin!');
        });
    }
</script>
@endsection