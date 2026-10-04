@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
    nav, header, footer { display: none !important; }
    body { 
        background: radial-gradient(circle at center, #fff9ef 0%, #bad6eb 100%) fixed !important; 
        font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        padding-bottom: 80px;
    }
    .store-container {
        max-width: 600px;
        margin: 0 auto;
        min-height: 100vh;
        background: #f8fafc;
        box-shadow: 0 4px 20px rgba(0,0,0,0.06);
    }
    .ticket-sawtooth {
        position: relative;
        background: white;
    }
    .ticket-sawtooth::before, .ticket-sawtooth::after {
        content: '';
        position: absolute;
        top: 50%;
        width: 14px;
        height: 14px;
        background: #f8fafc;
        border-radius: 50%;
        transform: translateY(-50%);
        z-index: 10;
    }
    .ticket-sawtooth::before { left: -7px; }
    .ticket-sawtooth::after { right: -7px; }
</style>

<div class="store-container relative">

    <!-- TOP BAR -->
    <div class="sticky top-0 z-30 bg-white/90 backdrop-blur-md border-b border-slate-100 px-4 py-3 flex items-center justify-between shadow-xs">
        <a href="{{ url()->previous() }}" class="w-9 h-9 rounded-full bg-slate-100 flex items-center justify-center text-slate-700 hover:bg-slate-200 transition">
            <i class="fa-solid fa-arrow-left text-sm"></i>
        </a>
        <h1 class="text-sm font-extrabold text-slate-800 truncate px-2">{{ $vendor->vendor_name ?? $vendor->name }}</h1>
        <a href="{{ route('customer.keranjang') }}" class="w-9 h-9 rounded-full bg-slate-100 flex items-center justify-center text-slate-700 hover:bg-slate-200 transition relative">
            <i class="fa-solid fa-cart-shopping text-xs"></i>
        </a>
    </div>

    <!-- STORE HERO PROFILE -->
    <div class="bg-gradient-to-br from-sky-600 to-blue-700 text-white p-5 rounded-b-3xl shadow-sm relative overflow-hidden">
        <!-- Background accents -->
        <div class="absolute -right-8 -top-8 w-36 h-36 bg-white/10 rounded-full blur-xl pointer-events-none"></div>
        <div class="absolute -left-8 -bottom-8 w-32 h-32 bg-sky-400/20 rounded-full blur-lg pointer-events-none"></div>

        <div class="flex items-center gap-4 relative z-10">
            <!-- Profil Avatar Toko -->
            <div class="w-16 h-16 rounded-full overflow-hidden bg-white/20 border-2 border-white/60 flex items-center justify-center shadow-md flex-shrink-0">
                @if(!empty($vendor->foto_profil_url))
                    <img src="{{ $vendor->foto_profil_url }}" class="w-full h-full object-cover">
                @else
                    <span class="text-2xl font-black text-white">{{ strtoupper(substr($vendor->vendor_name ?? 'V', 0, 1)) }}</span>
                @endif
            </div>

            <!-- Nama & Info Toko -->
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-1.5 flex-wrap">
                    <h2 class="text-lg font-black text-white truncate leading-tight">{{ $vendor->vendor_name ?? $vendor->name }}</h2>
                    <span class="inline-flex items-center gap-1 bg-white/20 backdrop-blur-xs text-white text-[10px] font-bold px-2 py-0.5 rounded-full border border-white/30">
                        <i class="fa-solid fa-circle-check text-sky-200 text-[9px]"></i> Toko Resmi
                    </span>
                </div>

                <div class="flex items-center gap-2 mt-1.5 text-xs text-sky-100 flex-wrap">
                    <span class="flex items-center gap-1 font-semibold">
                        <i class="fa-solid fa-location-dot text-sky-300 text-xs"></i> {{ $areaDesa }}
                    </span>
                    @if(isset($jarak))
                        <span>&bull;</span>
                        <span class="bg-white/15 px-2 py-0.5 rounded-full text-[11px] font-bold">
                            ±{{ number_format($jarak, 1, ',', '') }} KM
                        </span>
                    @endif
                </div>

                <div class="mt-2 text-[11px] text-sky-100 flex items-center gap-3">
                    <span><strong class="text-white">{{ $barangs->total() }}</strong> Produk Disewakan</span>
                </div>
            </div>
        </div>

        <!-- Tombol Aksi Hubungi Toko -->
        <div class="mt-4 pt-3 border-t border-white/15 flex items-center gap-2 relative z-10">
            @php
                $waRaw = $vendor->whatsapp_vendor ?? ($vendor->whatsapp ?? '');
                $waClean = preg_replace('/[^0-9]/', '', $waRaw);
                if (str_starts_with($waClean, '0')) {
                    $waClean = '62' . substr($waClean, 1);
                }
                $waLink = $waClean ? "https://wa.me/{$waClean}?text=" . urlencode("Halo Toko " . ($vendor->vendor_name ?? '') . ", saya ingin bertanya seputar sewa barang di Rentify.") : '#';
            @endphp
            @if($waClean)
                <a href="{{ $waLink }}" target="_blank" class="flex-1 bg-white text-sky-700 hover:bg-sky-50 py-2 px-3 rounded-xl font-extrabold text-xs flex items-center justify-center gap-1.5 shadow-sm transition">
                    <i class="fa-brands fa-whatsapp text-emerald-600 text-sm"></i> Chat Toko
                </a>
            @endif
            <button type="button" onclick="navigator.clipboard.writeText(window.location.href); alert('Link toko berhasil disalin!');" class="bg-white/15 hover:bg-white/25 text-white py-2 px-3 rounded-xl font-bold text-xs flex items-center justify-center gap-1.5 backdrop-blur-xs transition">
                <i class="fa-solid fa-share-nodes"></i> Bagikan
            </button>
        </div>
    </div>

    <!-- PENCARIAN DALAM TOKO -->
    <div class="p-4 pb-2">
        <form action="{{ route('customer.toko.show', $vendor->id) }}" method="GET" class="relative">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari barang di toko ini..." 
                   class="w-full bg-white border border-slate-200 rounded-xl pl-9 pr-4 py-2.5 text-xs font-semibold text-slate-800 placeholder-slate-400 focus:outline-none focus:border-sky-500 shadow-2xs">
            <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
            @if(request('kategori'))
                <input type="hidden" name="kategori" value="{{ request('kategori') }}">
            @endif
        </form>
    </div>

    <!-- SECTION: VOUCHER TOKO (ALA SHOPEE) -->
    @if(isset($vouchers) && $vouchers->count() > 0)
    <div class="px-4 py-2">
        <div class="flex items-center justify-between mb-2.5">
            <h3 class="text-xs font-black text-slate-800 flex items-center gap-1.5 uppercase tracking-wide">
                <i class="fa-solid fa-ticket text-amber-500"></i> Voucher Toko
            </h3>
            <span class="text-[10px] text-slate-500 font-semibold">Klaim & pakai saat checkout</span>
        </div>

        <!-- Horizontal scroll vouchers ala Shopee -->
        <div class="flex gap-3 overflow-x-auto pb-2 scrollbar-none">
            @foreach($vouchers as $v)
            <div class="ticket-sawtooth flex-shrink-0 w-72 rounded-xl border border-amber-200/80 shadow-xs overflow-hidden flex bg-gradient-to-r from-amber-500/10 via-white to-white">
                <!-- Sisi Kiri: Nilai Diskon -->
                <div class="w-24 bg-gradient-to-br from-amber-400 to-amber-500 text-white p-3 flex flex-col items-center justify-center text-center">
                    <span class="text-[9px] font-black uppercase tracking-wider opacity-90">Kupon</span>
                    <div class="font-black text-sm leading-tight mt-0.5">
                        @if($v->tipe_diskon === 'persen')
                            {{ $v->nilai_diskon }}%
                            <span class="block text-[8px] font-bold">OFF</span>
                        @else
                            Rp{{ number_format($v->nilai_diskon / 1000, 0) }}rb
                            <span class="block text-[8px] font-bold">POTONGAN</span>
                        @endif
                    </div>
                </div>

                <!-- Sisi Kanan: Detail & Tombol Klaim -->
                <div class="flex-1 p-2.5 pl-3 flex flex-col justify-between">
                    <div>
                        <div class="text-[11px] font-black text-slate-800">
                            Min. Belanja Rp{{ number_format($v->minimal_belanja, 0, ',', '.') }}
                        </div>
                        <div class="text-[9.5px] text-slate-500 font-medium mt-0.5">
                            Hingga {{ \Carbon\Carbon::parse($v->tanggal_selesai)->translatedFormat('d M Y') }}
                        </div>
                    </div>

                    <div class="mt-2 flex items-center justify-between pt-1 border-t border-dashed border-slate-200">
                        <span class="font-mono text-[10px] font-black text-sky-700 bg-sky-50 px-1.5 py-0.5 rounded border border-sky-100">
                            {{ $v->kode_voucher }}
                        </span>
                        <button type="button" onclick="klaimVoucher('{{ $v->kode_voucher }}', this)" 
                                class="bg-amber-500 hover:bg-amber-600 active:scale-95 text-white text-[10px] font-black px-3 py-1 rounded-lg shadow-2xs transition">
                            Klaim
                        </button>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- SECTION: FILTER KATEGORI BARANG -->
    @if(isset($kategoris) && $kategoris->count() > 1)
    <div class="px-4 py-2">
        <div class="flex gap-2 overflow-x-auto pb-1 scrollbar-none">
            <a href="{{ route('customer.toko.show', $vendor->id) }}" 
               class="px-3 py-1.5 rounded-full text-xs font-bold whitespace-nowrap transition {{ !request('kategori') ? 'bg-sky-600 text-white shadow-2xs' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50' }}">
                Semua Barang
            </a>
            @foreach($kategoris as $kat)
                <a href="{{ route('customer.toko.show', ['id' => $vendor->id, 'kategori' => $kat->slug]) }}" 
                   class="px-3 py-1.5 rounded-full text-xs font-bold whitespace-nowrap transition {{ request('kategori') == $kat->slug ? 'bg-sky-600 text-white shadow-2xs' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50' }}">
                    {{ $kat->nama }}
                </a>
            @endforeach
        </div>
    </div>
    @endif

    <!-- SECTION: PRODUK SEWA TOKO -->
    <div class="p-4 pt-2">
        <div class="flex items-center justify-between mb-3">
            <h3 class="text-xs font-black text-slate-800 uppercase tracking-wide">
                Daftar Barang Sewa
            </h3>
            <span class="text-[11px] text-slate-500 font-semibold">{{ $barangs->total() }} unit</span>
        </div>

        @if($barangs->count() > 0)
            <div class="grid grid-cols-2 gap-3">
                @foreach($barangs as $item)
                    @php
                        $coverUrl = $item->cover_photo 
                            ? (str_starts_with($item->cover_photo, 'http') ? $item->cover_photo : asset(str_replace('public/', '', $item->cover_photo)))
                            : null;
                        $hargaSewa = $item->harga_sewa_customer ?? ($item->harga_sewa_harian * 1.05);
                    @endphp
                    <a href="{{ route('customer.barang.show', $item->slug ?? $item->id) }}" 
                       class="bg-white rounded-2xl border border-slate-200/80 overflow-hidden shadow-2xs hover:shadow-md transition flex flex-col">
                        
                        <!-- Gambar Cover -->
                        <div class="w-full aspect-square bg-slate-100 relative overflow-hidden">
                            @if($coverUrl)
                                <img src="{{ $coverUrl }}" alt="{{ $item->nama }}" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-slate-300">
                                    <i class="fa-solid fa-image text-3xl"></i>
                                </div>
                            @endif

                            <span class="absolute top-2 left-2 bg-white/90 backdrop-blur-xs text-sky-700 text-[9px] font-extrabold px-2 py-0.5 rounded-md border border-slate-200/60 shadow-2xs">
                                {{ $item->kategori->nama ?? 'Sewa' }}
                            </span>

                            @if($item->stok_total <= 0)
                                <div class="absolute inset-0 bg-slate-900/60 flex items-center justify-center text-white text-[11px] font-black">
                                    Habis Disewa
                                </div>
                            @endif
                        </div>

                        <!-- Info Produk -->
                        <div class="p-3 flex-1 flex flex-col justify-between">
                            <div>
                                <h4 class="text-xs font-bold text-slate-800 line-clamp-2 leading-snug">{{ $item->nama }}</h4>
                                
                                <div class="mt-2 text-sky-600 font-black text-sm">
                                    Rp{{ number_format($hargaSewa, 0, ',', '.') }}
                                    <span class="text-[10px] font-medium text-slate-400">/hari</span>
                                </div>
                            </div>

                            <div class="mt-2 pt-2 border-t border-slate-100 flex items-center justify-between text-[10px] text-slate-500 font-semibold">
                                <span>Sisa: <strong class="text-slate-700">{{ max(0, $item->stok_total) }}</strong></span>
                                @if($item->deposit > 0)
                                    <span class="text-amber-600 font-bold">Dep. Rp{{ number_format($item->deposit / 1000, 0) }}rb</span>
                                @else
                                    <span class="text-emerald-600 font-bold">Bebas Dep.</span>
                                @endif
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>

            <div class="mt-5">
                {{ $barangs->links() }}
            </div>
        @else
            <div class="text-center py-12 bg-white rounded-2xl border border-slate-200/80 p-6">
                <i class="fa-solid fa-box-open text-slate-300 text-4xl mb-3"></i>
                <h4 class="text-sm font-bold text-slate-700">Belum ada barang sewa</h4>
                <p class="text-xs text-slate-400 mt-1">Toko ini belum menambahkan barang sewa di kategori ini.</p>
            </div>
        @endif
    </div>

</div>

<!-- TOAST KLAIM VOUCHER -->
<div id="voucherToast" class="fixed top-5 left-1/2 -translate-x-1/2 z-50 bg-slate-900/95 text-white px-4 py-2.5 rounded-full text-xs font-bold shadow-xl opacity-0 pointer-events-none transition-all duration-300 flex items-center gap-2">
    <i class="fa-solid fa-circle-check text-emerald-400 text-sm"></i>
    <span id="voucherToastText">Voucher berhasil diklaim & disalin!</span>
</div>

<script>
    function klaimVoucher(kode, btn) {
        navigator.clipboard.writeText(kode).then(() => {
            const oriHtml = btn.innerHTML;
            btn.innerHTML = '✓ Disalin';
            btn.classList.remove('bg-amber-500', 'hover:bg-amber-600');
            btn.classList.add('bg-emerald-600');

            const toast = document.getElementById('voucherToast');
            document.getElementById('voucherToastText').innerText = `Voucher ${kode} disalin! Pakai saat checkout.`;
            toast.classList.remove('opacity-0', 'pointer-events-none');
            toast.classList.add('opacity-100');

            setTimeout(() => {
                toast.classList.remove('opacity-100');
                toast.classList.add('opacity-0', 'pointer-events-none');
                btn.innerHTML = oriHtml;
                btn.classList.remove('bg-emerald-600');
                btn.classList.add('bg-amber-500', 'hover:bg-amber-600');
            }, 2500);
        }).catch(() => {
            prompt("Salin kode voucher ini dan gunakan saat checkout:", kode);
        });
    }
</script>
@endsection
