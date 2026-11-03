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
    }
    .ticket-sawtooth::before, .ticket-sawtooth::after {
        content: '';
        position: absolute;
        width: 14px;
        height: 14px;
        background: #f8fafc;
        border-radius: 50%;
        z-index: 10;
    }
    .ticket-sawtooth::before { 
        top: -7px; 
        left: 89px; 
        box-shadow: inset 0 -1px 2px rgba(14, 165, 233, 0.15);
    }
    .ticket-sawtooth::after { 
        bottom: -7px; 
        left: 89px; 
        box-shadow: inset 0 1px 2px rgba(14, 165, 233, 0.15);
    }
</style>

<div class="store-container relative">

    @if(Auth::check() && (Auth::user()->role === 'admin' || session()->has('admin_impersonator_id')))
    <!-- ADMIN BAR UNTUK MASUK KE DASHBOARD VENDOR -->
    <div class="bg-gradient-to-r from-purple-800 to-indigo-900 text-white px-4 py-2.5 flex items-center justify-between text-xs shadow-md sticky top-0 z-40 border-b border-purple-500/30">
        <div class="flex items-center gap-2">
            <span class="w-6 h-6 rounded-md bg-amber-400 text-slate-900 flex items-center justify-center font-black text-xs shrink-0">
                <i class="fa-solid fa-crown"></i>
            </span>
            <div>
                <span class="font-extrabold text-amber-300">Mode Admin:</span>
                <span class="font-medium text-white/90">Etalase {{ $vendor->vendor_name }}</span>
            </div>
        </div>
        <a href="{{ route('admin.vendors.impersonate', $vendor->id) }}" class="bg-amber-400 hover:bg-amber-300 text-slate-950 font-black px-3 py-1.5 rounded-lg text-xs shadow transition flex items-center gap-1.5 active:scale-95 whitespace-nowrap">
            <i class="fa-solid fa-door-open"></i>
            <span>Masuk Dashboard Toko</span>
        </a>
    </div>
    @elseif(Auth::check() && Auth::id() === $vendor->id)
    <!-- OWNER BAR UNTUK MASUK KE DASHBOARD TOKO SENDIRI -->
    <div class="bg-gradient-to-r from-sky-500 via-sky-600 to-sky-600 text-white px-4 py-2 flex items-center justify-between text-xs shadow-sm sticky top-0 z-40 border-b border-sky-400/30">
        <div class="flex items-center gap-2">
            <i class="fa-solid fa-store text-sky-200"></i>
            <span class="font-medium text-white/95">Ini adalah profil publik toko Anda.</span>
        </div>
        <a href="{{ route('vendor.dashboard') }}" class="bg-white hover:bg-sky-50 text-sky-600 font-extrabold px-3 py-1 rounded-lg text-xs shadow-xs transition flex items-center gap-1.5 active:scale-95">
            <i class="fa-solid fa-chart-pie"></i>
            <span>Buka Dashboard Toko</span>
        </a>
    </div>
    @endif

    @php
        $rawPrevious = url()->previous();
        $currentUrl = url()->current();

        $isInvalidPrevious = empty($rawPrevious)
            || $rawPrevious === $currentUrl
            || str_contains($rawPrevious, 'sw.js')
            || preg_match('/\.(js|json|css|png|jpg|jpeg|gif|svg|webp|ico)(\?.*)?$/i', parse_url($rawPrevious, PHP_URL_PATH) ?? '')
            || (parse_url($rawPrevious, PHP_URL_HOST) && parse_url($rawPrevious, PHP_URL_HOST) !== request()->getHost());

        $backUrl = $isInvalidPrevious ? route('customer.home') : $rawPrevious;
    @endphp

    <!-- TOP BAR -->
    <div class="sticky top-0 z-30 bg-white/90 backdrop-blur-md border-b border-slate-100 px-4 py-3 flex items-center justify-between shadow-xs">
        <a href="{{ $backUrl }}" 
           onclick="if (window.history.length > 1 && document.referrer && document.referrer.includes(window.location.host) && !document.referrer.match(/(sw\.js|\.js|\.css|\.json)/i)) { event.preventDefault(); window.history.back(); }" 
           class="w-9 h-9 rounded-full bg-slate-100 flex items-center justify-center text-slate-700 hover:bg-slate-200 transition active:scale-95" 
           aria-label="Kembali">
            <i class="fa-solid fa-arrow-left text-sm"></i>
        </a>
        <h1 class="text-sm font-extrabold text-slate-800 truncate px-2">{{ $vendor->vendor_name ?? $vendor->name }}</h1>
        <a href="{{ route('customer.keranjang') }}" class="w-9 h-9 rounded-full bg-slate-100 flex items-center justify-center text-slate-700 hover:text-sky-600 hover:bg-sky-50 transition relative group active:scale-95" aria-label="Keranjang Belanja">
            <svg class="w-4.5 h-4.5 stroke-slate-700 group-hover:stroke-sky-600 group-hover:scale-105 transition-all" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="8" cy="21" r="1"/>
                <circle cx="19" cy="21" r="1"/>
                <path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/>
            </svg>
            @php $cartCount = \App\Models\Keranjang::where('user_id', Auth::id())->count(); @endphp
            @if($cartCount > 0)
                <span class="absolute -top-1 -right-1 bg-gradient-to-r from-sky-400 to-sky-600 text-white text-[9px] font-black min-w-[17px] h-4 px-1 rounded-full flex items-center justify-center border-2 border-white shadow-xs leading-none">{{ $cartCount > 99 ? '99+' : $cartCount }}</span>
            @endif
        </a>
    </div>

    <!-- STORE HERO PROFILE -->
    <div class="bg-gradient-to-br from-sky-400 via-sky-500 to-sky-600 text-white p-5 rounded-b-3xl shadow-md shadow-sky-500/15 border-b border-sky-300/30 relative overflow-hidden">
        <!-- Background accents -->
        <div class="absolute -right-10 -top-10 w-44 h-44 bg-white/20 rounded-full blur-2xl pointer-events-none"></div>
        <div class="absolute -left-10 -bottom-10 w-36 h-36 bg-sky-200/25 rounded-full blur-xl pointer-events-none"></div>

        <div class="flex items-center gap-4 relative z-10">
            <!-- Profil Avatar Toko -->
            <div class="w-16 h-16 rounded-full overflow-hidden bg-white/20 backdrop-blur-xs border-2 border-white/70 flex items-center justify-center shadow-md flex-shrink-0">
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
                </div>

                <div class="flex items-center gap-2 mt-1.5 text-xs text-sky-50/90 flex-wrap">
                    <span class="flex items-center gap-1 font-semibold">
                        <i class="fa-solid fa-location-dot text-sky-200 text-xs"></i> {{ $areaDesa }}
                    </span>
                    @if(isset($jarak))
                        <span>&bull;</span>
                        <span class="bg-white/20 backdrop-blur-xs px-2 py-0.5 rounded-full text-[11px] font-bold text-white">
                            ±{{ number_format($jarak, 1, ',', '') }} KM
                        </span>
                    @endif
                </div>

                <div class="mt-2 text-[11px] text-sky-50/90 flex items-center gap-3">
                    <span><strong class="text-white font-extrabold">{{ $barangs->total() }}</strong> Produk Disewakan</span>
                </div>
            </div>
        </div>

        <!-- Tombol Aksi Toko -->
        <div class="mt-4 pt-3.5 border-t border-white/20 flex items-center gap-2 relative z-10">
            <button type="button" onclick="navigator.clipboard.writeText(window.location.href); alert('Link profil toko berhasil disalin!');" class="w-full bg-gradient-to-r from-white/25 via-white/20 to-white/15 hover:from-white/35 hover:via-white/30 hover:to-white/25 text-white active:scale-95 py-2.5 px-4 rounded-xl font-bold text-xs flex items-center justify-center gap-1.5 backdrop-blur-md border border-white/30 transition shadow-xs">
                <i class="fa-solid fa-share-nodes"></i> Bagikan Toko
            </button>
        </div>
    </div>

    <!-- PENCARIAN DALAM TOKO -->
    <div class="p-4 pb-2">
        <form action="{{ route('customer.toko.show', $vendor->id) }}" method="GET" class="relative">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari barang di toko ini..." 
                   class="w-full bg-white border border-slate-200 rounded-xl pl-9 pr-4 py-2.5 text-xs font-semibold text-slate-800 placeholder-slate-400 focus:outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-100 shadow-2xs transition">
            <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
            @if(request('kategori'))
                <input type="hidden" name="kategori" value="{{ request('kategori') }}">
            @endif
        </form>
    </div>

    <!-- SECTION: VOUCHER TOKO -->
    @if(isset($vouchers) && $vouchers->count() > 0)
    <div class="px-4 py-2">
        <div class="flex items-center justify-between mb-2.5">
            <h3 class="text-xs font-black text-slate-800 flex items-center gap-1.5 uppercase tracking-wide">
                <i class="fa-solid fa-ticket text-sky-500"></i> Voucher Toko
            </h3>
            <span class="text-[10px] text-slate-500 font-semibold">Klaim & pakai saat checkout</span>
        </div>

        <!-- Horizontal scroll vouchers ala Rentify -->
        <div class="flex gap-3 overflow-x-auto pb-2 scrollbar-none">
            @foreach($vouchers as $v)
            <div class="ticket-sawtooth flex-shrink-0 w-72 rounded-2xl border border-sky-200/90 shadow-xs overflow-hidden flex bg-gradient-to-br from-sky-50/90 via-white to-sky-100/40 hover:shadow-md hover:border-sky-300 transition-all">
                <!-- Sisi Kiri: Nilai Diskon -->
                <div class="w-24 bg-gradient-to-br from-sky-400 via-sky-500 to-sky-600 text-white p-3 flex flex-col items-center justify-center text-center relative overflow-hidden border-r-2 border-dashed border-white/50 shadow-inner shrink-0">
                    <div class="absolute -right-3 -top-3 w-10 h-10 bg-white/20 rounded-full blur-xs pointer-events-none"></div>
                    <span class="text-[9px] font-black uppercase tracking-wider text-sky-100 drop-shadow-xs">Kupon</span>
                    <div class="font-black text-sm leading-tight mt-0.5 text-white drop-shadow-xs">
                        @if($v->tipe_diskon === 'persen')
                            {{ $v->nilai_diskon }}%
                            <span class="block text-[8px] font-bold text-sky-100">OFF</span>
                        @else
                            Rp{{ number_format($v->nilai_diskon / 1000, 0) }}rb
                            <span class="block text-[8px] font-bold text-sky-100">POTONGAN</span>
                        @endif
                    </div>
                </div>

                <!-- Sisi Kanan: Detail & Tombol Klaim -->
                <div class="flex-1 p-2.5 pl-3 flex flex-col justify-between bg-gradient-to-r from-transparent via-white/60 to-sky-50/30">
                    <div>
                        <div class="text-[11px] font-black text-slate-800">
                            Min. Belanja Rp{{ number_format($v->minimal_belanja, 0, ',', '.') }}
                        </div>
                        <div class="text-[9.5px] text-slate-500 font-medium mt-0.5 flex items-center gap-1">
                            <i class="fa-regular fa-clock text-[8.5px] text-sky-500"></i>
                            <span>Hingga {{ \Carbon\Carbon::parse($v->tanggal_selesai)->translatedFormat('d M Y') }}</span>
                        </div>
                    </div>

                    <div class="mt-2 flex items-center justify-between pt-1.5 border-t border-dashed border-sky-200/80">
                        <span class="font-mono text-[10px] font-black text-sky-700 bg-gradient-to-r from-sky-100/90 to-sky-50 px-2 py-0.5 rounded-md border border-sky-200/80 shadow-2xs">
                            {{ $v->kode_voucher }}
                        </span>
                        @php $isClaimed = in_array($v->id, $claimedVoucherIds ?? []); @endphp
                        <button type="button" onclick="klaimVoucher('{{ $v->kode_voucher }}', this)" 
                                class="{{ $isClaimed ? 'bg-gradient-to-r from-slate-700 to-slate-800' : 'bg-gradient-to-r from-sky-400 via-sky-500 to-sky-600 hover:from-sky-500 hover:to-sky-700' }} active:scale-95 text-white text-[10px] font-black px-3 py-1 rounded-lg shadow-xs shadow-sky-500/25 transition">
                            {{ $isClaimed ? '✓ Diklaim' : 'Klaim' }}
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
               class="px-3.5 py-1.5 rounded-full text-xs font-bold whitespace-nowrap transition {{ !request('kategori') ? 'bg-gradient-to-r from-sky-400 to-sky-500 text-white shadow-xs shadow-sky-500/25' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50' }}">
                Semua Barang
            </a>
            @foreach($kategoris as $kat)
                <a href="{{ route('customer.toko.show', ['id' => $vendor->id, 'kategori' => $kat->slug]) }}" 
                   class="px-3.5 py-1.5 rounded-full text-xs font-bold whitespace-nowrap transition {{ request('kategori') == $kat->slug ? 'bg-gradient-to-r from-sky-400 to-sky-500 text-white shadow-xs shadow-sky-500/25' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50' }}">
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
            <div class="columns-2 gap-3 [column-fill:_balance]">
                @foreach($barangs as $item)
                    @php
                        $coverUrl = $item->cover_photo_url;
                        $hargaSewa = $item->harga_sewa_customer ?? ($item->harga_sewa_harian * 1.05);
                        $namaLower = strtolower($item->nama ?? '');
                        $fallbackImg = str_contains($namaLower, 'kurs') 
                            ? asset('barang/5I0op8pdywH1SopGwP36pKBhx6y44N6tbBVzcSPx.png') 
                            : (str_contains($namaLower, 'meja') ? asset('barang/4yEbGpYvTYYai8LZHVP3jFNNO5WBPUokdCJggGBy.png') : asset('storage/placeholders/placeholder-barang.png'));
                    @endphp
                    <a href="{{ route('customer.barang.show', $item->slug ?? $item->id) }}" 
                       class="break-inside-avoid mb-3 bg-white rounded-2xl border border-slate-200/80 overflow-hidden shadow-2xs hover:shadow-md transition flex flex-col">
                        
                        <!-- Gambar Cover -->
                        <div class="w-full bg-slate-100 relative overflow-hidden flex items-center justify-center">
                            <img src="{{ $coverUrl }}" alt="{{ $item->nama }}" class="w-full h-auto max-h-[420px] object-cover block" onerror="this.onerror=null; this.src='{{ $fallbackImg }}';">

                            <span class="absolute top-2 left-2 bg-white/95 backdrop-blur-xs text-sky-600 text-[9px] font-extrabold px-2 py-0.5 rounded-md border border-slate-200/60 shadow-2xs">
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
                                    <span class="text-sky-600 font-bold">Bebas Dep.</span>
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
    <i class="fa-solid fa-circle-check text-sky-400 text-sm"></i>
    <span id="voucherToastText">Voucher berhasil diklaim & disalin!</span>
</div>

<script>
    function klaimVoucher(kode, btn) {
        // Simpan klaim via AJAX ke backend
        fetch('{{ route('customer.voucher.claim') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ kode_voucher: kode })
        }).catch(err => console.log('Claim fetch notice:', err));

        navigator.clipboard.writeText(kode).then(() => {
            btn.innerHTML = '✓ Diklaim';
            btn.classList.remove('from-sky-400', 'to-sky-600', 'hover:from-sky-500', 'hover:to-sky-700');
            btn.classList.add('from-slate-700', 'to-slate-800');

            const toast = document.getElementById('voucherToast');
            document.getElementById('voucherToastText').innerText = `Voucher ${kode} berhasil diklaim & disalin! Siap dipakai saat checkout.`;
            toast.classList.remove('opacity-0', 'pointer-events-none');
            toast.classList.add('opacity-100');

            setTimeout(() => {
                toast.classList.remove('opacity-100');
                toast.classList.add('opacity-0', 'pointer-events-none');
            }, 3000);
        }).catch(() => {
            prompt("Salin kode voucher ini dan gunakan saat checkout:", kode);
        });
    }
</script>
@endsection
