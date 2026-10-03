<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Voucher & Promo - Rentify</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="{{ asset('rentify-theme.css') }}">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            background: radial-gradient(circle at center, #fff9ef 0%, #bad6eb 100%) fixed !important;
        }
        .bg-flowing {
            background: radial-gradient(circle at center, #fff9ef 0%, #bad6eb 100%) fixed !important;
        }
    </style>
</head>

<body class="min-h-screen pb-24 text-slate-800 bg-flowing overflow-x-hidden w-full max-w-full">

    <div class="max-w-md mx-auto min-h-screen relative pb-16">
        
        <!-- TOP HEADER -->
        <header class="rentify-navbar sticky top-0 z-40 px-5 py-3.5 flex items-center justify-between shadow-sm">
            <a href="{{ route('customer.dashboard') }}" class="w-9 h-9 rounded-xl bg-white/60 hover:bg-white flex items-center justify-center text-slate-600 hover:text-sky-600 transition shadow-sm">
                <i class="fa-solid fa-arrow-left text-sm"></i>
            </a>
            <h1 class="text-base font-black text-slate-800 flex-1 text-center tracking-tight pr-9">Voucher & Promo</h1>
        </header>

        <main class="p-4">
            <!-- BANNER INFO -->
            <div class="rentify-card p-4 rounded-2xl mb-4 bg-gradient-to-r from-sky-500/10 via-sky-400/5 to-transparent border border-sky-200/60 flex items-center gap-3.5">
                <div class="w-10 h-10 rounded-xl bg-sky-500 text-white flex items-center justify-center text-lg flex-shrink-0 shadow-sm">
                    <i class="fa-solid fa-gift"></i>
                </div>
                <div>
                    <h3 class="text-xs font-black text-slate-800">Hemat Sewa dengan Voucher</h3>
                    <p class="text-[11px] text-slate-500 mt-0.5">Salin kode voucher dan masukkan saat checkout pesanan Anda.</p>
                </div>
            </div>

            @if(isset($vouchers) && $vouchers->count() > 0)
                <div class="space-y-3.5">
                    @foreach($vouchers as $voucher)
                    <div class="rentify-card relative rounded-2xl overflow-hidden shadow-sm border border-slate-200/80 transition-all hover:shadow-md">
                        <!-- TICKET HEADER -->
                        <div class="p-3.5 pb-2.5 flex items-start justify-between border-b border-dashed border-slate-200">
                            <div class="flex items-center gap-2.5">
                                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-amber-400 to-amber-500 text-white flex items-center justify-center font-black text-base shadow-sm">
                                    @if($voucher->tipe_diskon === 'persen')
                                        <i class="fa-solid fa-percent text-sm"></i>
                                    @else
                                        <span class="text-xs">Rp</span>
                                    @endif
                                </div>
                                <div>
                                    <div class="text-xs font-extrabold text-slate-800">
                                        @if($voucher->tipe_diskon === 'persen')
                                            Diskon {{ $voucher->nilai_diskon }}%
                                            @if($voucher->maksimal_diskon)
                                                <span class="text-[10px] text-slate-500 font-semibold">(Maks Rp {{ number_format($voucher->maksimal_diskon, 0, ',', '.') }})</span>
                                            @endif
                                        @else
                                            Potongan Rp {{ number_format($voucher->nilai_diskon, 0, ',', '.') }}
                                        @endif
                                    </div>
                                    <div class="text-[10px] font-semibold text-sky-600 flex items-center gap-1 mt-0.5">
                                        <i class="fa-solid fa-shop text-[9px]"></i>
                                        <span>{{ $voucher->vendor->vendor_name ?? ($voucher->vendor->name ?? 'Vendor Rentify') }}</span>
                                    </div>
                                </div>
                            </div>
                            <span class="text-[9px] font-bold px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-600 border border-emerald-200">
                                Aktif
                            </span>
                        </div>

                        <!-- TICKET BODY -->
                        <div class="p-3.5 pt-2.5 bg-slate-50/50 flex flex-col gap-2">
                            <div class="flex items-center justify-between text-[11px] text-slate-500">
                                <span>Min. Belanja: <strong class="text-slate-700">Rp {{ number_format($voucher->minimal_belanja, 0, ',', '.') }}</strong></span>
                                <span>Berlaku s/d: <strong class="text-slate-700">{{ \Carbon\Carbon::parse($voucher->tanggal_selesai)->translatedFormat('d M Y') }}</strong></span>
                            </div>

                            <!-- KODE & TOMBOL SALIN -->
                            <div class="mt-1 flex items-center justify-between bg-white p-2 rounded-xl border border-slate-200">
                                <div class="flex items-center gap-2 pl-1">
                                    <i class="fa-solid fa-ticket text-sky-500 text-xs"></i>
                                    <span class="font-mono font-black text-xs text-slate-800 tracking-wider select-all">{{ $voucher->kode_voucher }}</span>
                                </div>
                                <button type="button" onclick="salinKode('{{ $voucher->kode_voucher }}', this)" 
                                        class="px-3 py-1 bg-sky-500 hover:bg-sky-600 active:scale-95 text-white text-[10px] font-bold rounded-lg transition-all flex items-center gap-1 shadow-xs">
                                    <i class="fa-regular fa-copy"></i>
                                    <span>Salin</span>
                                </button>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            @else
                <div class="flex flex-col items-center justify-center py-24 text-slate-400 text-center px-4">
                    <div class="w-20 h-20 bg-sky-100/80 rounded-3xl flex items-center justify-center mb-4 shadow-sm">
                        <i class="fa-solid fa-ticket text-4xl text-sky-400"></i>
                    </div>
                    <h3 class="font-black text-slate-700 text-base mb-1">Belum Ada Voucher Tersedia</h3>
                    <p class="text-xs font-medium text-slate-500 mb-6 max-w-xs">Saat ini belum ada promo aktif dari vendor. Kunjungi toko favoritmu secara berkala untuk promo menarik!</p>
                    <a href="{{ route('customer.home') }}" class="rentify-btn px-8 py-3.5 rounded-2xl text-xs uppercase tracking-widest font-extrabold inline-flex items-center gap-2 shadow-md">
                        <span>Jelajahi Beranda</span>
                        <i class="fa-solid fa-arrow-right text-xs"></i>
                    </a>
                </div>
            @endif
        </main>
        
        <!-- BOTTOM NAV MOBILE -->
        <div class="fixed bottom-2 left-0 w-full z-50 flex justify-center pointer-events-none px-4">
            <nav class="rentify-card backdrop-blur-xl border border-white/60 pointer-events-auto px-6 py-1.5 rounded-full flex justify-between items-center gap-7 shadow-lg shadow-sky-900/10">
                <a href="{{ route('customer.home') }}" class="flex flex-col items-center text-slate-400 hover:text-sky-500 transition-colors px-1 py-0.5">
                    <i class="fa-solid fa-house text-[16px] mb-0.5"></i><span class="text-[8.5px] font-bold tracking-tight">Beranda</span>
                </a>
                <a href="{{ route('customer.wishlist') }}" class="flex flex-col items-center text-slate-400 hover:text-sky-500 transition-colors px-1 py-0.5">
                    <i class="fa-solid fa-heart text-[16px] mb-0.5"></i><span class="text-[8.5px] font-bold tracking-tight">Favorit</span>
                </a>
                <a href="{{ route('customer.dashboard') }}" class="flex flex-col items-center text-sky-600 px-1 py-0.5">
                    <i class="fa-solid fa-user text-[16px] mb-0.5"></i><span class="text-[8.5px] font-black tracking-tight">Akun</span>
                </a>
            </nav>
        </div>

    </div>

    <!-- TOAST NOTIFIKASI SALIN -->
    <div id="copyToast" class="fixed top-5 left-1/2 -translate-x-1/2 z-50 bg-slate-900/90 backdrop-blur-md text-white px-4 py-2 rounded-full text-xs font-bold shadow-xl opacity-0 pointer-events-none transition-all duration-300 flex items-center gap-2">
        <i class="fa-solid fa-circle-check text-emerald-400"></i>
        <span id="toastMsg">Kode voucher berhasil disalin!</span>
    </div>

    <script>
        function salinKode(kode, btn) {
            navigator.clipboard.writeText(kode).then(() => {
                const oriHtml = btn.innerHTML;
                btn.innerHTML = '<i class="fa-solid fa-check"></i> <span>Tersalin!</span>';
                btn.classList.remove('bg-sky-500', 'hover:bg-sky-600');
                btn.classList.add('bg-emerald-500');

                const toast = document.getElementById('copyToast');
                toast.classList.remove('opacity-0', 'pointer-events-none');
                toast.classList.add('opacity-100');

                setTimeout(() => {
                    toast.classList.remove('opacity-100');
                    toast.classList.add('opacity-0', 'pointer-events-none');
                    btn.innerHTML = oriHtml;
                    btn.classList.remove('bg-emerald-500');
                    btn.classList.add('bg-sky-500', 'hover:bg-sky-600');
                }, 2000);
            }).catch(() => {
                prompt("Salin kode voucher berikut:", kode);
            });
        }
    </script>

</body>
</html>
