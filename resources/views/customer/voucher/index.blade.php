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
        .rentify-card {
            background: rgba(255, 255, 255, 0.88);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.85);
            box-shadow: 0 10px 25px -5px rgba(14, 165, 233, 0.08), 0 8px 10px -6px rgba(0, 0, 0, 0.03);
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
            <!-- FORM KLAIM KODE VOUCHER -->
            <div class="rentify-card p-4 rounded-2xl mb-4 border border-sky-200/70 shadow-xs space-y-2.5">
                <div class="flex items-center justify-between">
                    <h2 class="text-xs font-black text-slate-800 flex items-center gap-1.5 uppercase tracking-wide">
                        <i class="fa-solid fa-ticket text-sky-500"></i> Punya Kode Voucher?
                    </h2>
                    <span class="text-[10px] text-sky-700 font-extrabold bg-gradient-to-r from-sky-100 to-sky-50 px-2.5 py-0.5 rounded-full border border-sky-200/80 shadow-2xs">Kupon Promo</span>
                </div>

                <form action="{{ route('customer.voucher') }}" method="GET" class="flex gap-2 pt-1" id="formCekVoucher">
                    <div class="relative flex-1">
                        <i class="fa-solid fa-ticket absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                        <input type="text" name="kode" id="inputCekKode" value="{{ request('kode') }}" required 
                               placeholder="Masukkan kode voucher" 
                               class="w-full pl-9 pr-3 py-2.5 rounded-xl border border-slate-200 bg-white text-xs font-black text-slate-800 uppercase focus:outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20 tracking-wider shadow-2xs">
                    </div>
                    <button type="submit" id="btnCekKode"
                            class="px-4 py-2.5 bg-gradient-to-r from-sky-400 via-sky-500 to-sky-600 hover:from-sky-500 hover:to-sky-700 text-white font-black text-xs rounded-xl shadow-xs shadow-sky-500/25 transition active:scale-95 shrink-0 flex items-center gap-1.5">
                        <i class="fa-solid fa-magnifying-glass text-[10px]"></i>
                        <span>Cek / Klaim</span>
                    </button>
                </form>
            </div>

            @if(isset($searchStatus) && $searchStatus === 'error')
                <!-- NOTIFIKASI GAGAL CARI VOUCHER -->
                <div class="p-3.5 bg-rose-50 border border-rose-200 text-rose-800 rounded-2xl text-xs font-bold flex items-center gap-2.5 mb-4 shadow-xs">
                    <i class="fa-solid fa-circle-exclamation text-rose-500 text-base shrink-0"></i>
                    <p class="flex-1">{{ $searchMessage }}</p>
                </div>
            @endif

            @if(isset($searchedVoucher) && $searchedVoucher)
                <!-- KARTU VOUCHER HASIL PENCARIAN -->
                <div class="mb-5 rounded-2xl overflow-hidden border border-sky-300 bg-gradient-to-br from-sky-50/90 via-white to-sky-100/40 shadow-md p-4 space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-1.5 text-xs font-black text-sky-700 uppercase tracking-wide">
                            <i class="fa-solid fa-circle-check text-sky-500 text-sm"></i>
                            <span>Voucher Berhasil Ditemukan!</span>
                        </div>
                        <span class="px-2.5 py-0.5 rounded-full text-[9.5px] font-black bg-gradient-to-r from-sky-100 to-sky-50 text-sky-800 border border-sky-200/80 shadow-2xs">
                            {{ ($searchedVoucher->visibilitas ?? 'publik') === 'rahasia' ? '🔒 Voucher Khusus' : '🌐 Voucher Promo' }}
                        </span>
                    </div>

                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-sky-400 via-sky-500 to-sky-600 text-white flex items-center justify-center font-black text-base shadow-xs shadow-sky-500/25 shrink-0 relative overflow-hidden">
                            <div class="absolute -right-2 -top-2 w-6 h-6 bg-white/20 rounded-full blur-xs pointer-events-none"></div>
                            @if($searchedVoucher->tipe_diskon === 'persen')
                                {{ $searchedVoucher->nilai_diskon }}%
                            @else
                                <span class="text-xs">Rp</span>
                            @endif
                        </div>
                        <div>
                            <div class="text-sm font-black text-slate-800">
                                @if($searchedVoucher->tipe_diskon === 'persen')
                                    Diskon {{ $searchedVoucher->nilai_diskon }}%
                                    @if($searchedVoucher->maksimal_diskon)
                                        <span class="text-xs text-slate-500 font-semibold">(Maks Rp {{ number_format($searchedVoucher->maksimal_diskon, 0, ',', '.') }})</span>
                                    @endif
                                @else
                                    Potongan Rp {{ number_format($searchedVoucher->nilai_diskon, 0, ',', '.') }}
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="p-2.5 bg-gradient-to-r from-sky-50/80 to-white/90 rounded-xl text-[11px] text-slate-600 flex items-center justify-between border border-sky-100">
                        <span>Min. Belanja: <strong class="text-slate-800">Rp {{ number_format($searchedVoucher->minimal_belanja, 0, ',', '.') }}</strong></span>
                        <span>Berlaku s/d: <strong class="text-slate-800">{{ \Carbon\Carbon::parse($searchedVoucher->tanggal_selesai)->translatedFormat('d M Y') }}</strong></span>
                    </div>

                    <div class="flex items-center justify-between gap-2 pt-1 border-t border-sky-100">
                        <div class="flex items-center gap-1.5 bg-white px-2.5 py-1.5 rounded-lg border border-sky-200 shadow-2xs">
                            <i class="fa-solid fa-ticket text-sky-500 text-xs"></i>
                            <span class="font-mono font-black text-xs text-sky-900 tracking-wider">{{ $searchedVoucher->kode_voucher }}</span>
                        </div>

                        <button type="button" onclick="salinKode('{{ $searchedVoucher->kode_voucher }}', this)" 
                                class="px-3.5 py-1.5 bg-gradient-to-r from-sky-400 via-sky-500 to-sky-600 hover:from-sky-500 hover:to-sky-700 text-white rounded-xl text-xs font-bold transition flex items-center gap-1 shadow-xs shadow-sky-500/25 active:scale-95">
                            <i class="fa-regular fa-copy"></i>
                            <span>Salin</span>
                        </button>
                    </div>
                </div>
            @endif

            <!-- DAFTAR VOUCHER -->
            <div class="flex items-center justify-between mb-3 pt-2">
                <h3 class="text-xs font-black text-slate-800 uppercase tracking-wide flex items-center gap-1.5">
                    <i class="fa-solid fa-ticket text-sky-500"></i> Voucher Tersedia
                </h3>
                <span class="text-[10px] text-slate-500 font-semibold">{{ count($vouchers) }} Kupon Aktif</span>
            </div>

            @if(isset($vouchers) && $vouchers->count() > 0)
                <div class="space-y-3.5">
                    @foreach($vouchers as $voucher)
                    <div class="relative rounded-2xl overflow-hidden shadow-xs border border-sky-200/90 bg-gradient-to-br from-white via-sky-50/50 to-blue-50/30 transition-all hover:shadow-md hover:border-sky-300 group">
                        <!-- Ticket Cutouts on perforation divider -->
                        <span class="absolute left-0 top-[58px] -translate-x-1/2 -translate-y-1/2 w-4 h-4 rounded-full bg-[#bad6eb] border border-sky-200/80 z-10 pointer-events-none"></span>
                        <span class="absolute right-0 top-[58px] translate-x-1/2 -translate-y-1/2 w-4 h-4 rounded-full bg-[#bad6eb] border border-sky-200/80 z-10 pointer-events-none"></span>

                        <!-- TICKET HEADER -->
                        <div class="p-3.5 pb-3 flex items-start justify-between border-b border-dashed border-sky-200/90 bg-gradient-to-r from-sky-500/10 via-sky-400/5 to-transparent relative z-0">
                            <div class="flex items-center gap-2.5">
                                <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-sky-400 via-sky-500 to-sky-600 text-white flex items-center justify-center font-black text-base shadow-xs shadow-sky-500/25 shrink-0 relative overflow-hidden">
                                    <div class="absolute -right-2 -top-2 w-6 h-6 bg-white/20 rounded-full blur-xs pointer-events-none"></div>
                                    @if($voucher->tipe_diskon === 'persen')
                                        <div class="text-center leading-none">
                                            <span class="text-xs">{{ $voucher->nilai_diskon }}%</span>
                                            <span class="block text-[7.5px] font-bold text-sky-100 mt-0.5">OFF</span>
                                        </div>
                                    @else
                                        <div class="text-center leading-none">
                                            <span class="text-[11px] font-black">Rp</span>
                                            <span class="block text-[7.5px] font-bold text-sky-100 mt-0.5">POT</span>
                                        </div>
                                    @endif
                                </div>
                                <div>
                                    <div class="text-xs font-black text-slate-800">
                                        @if($voucher->tipe_diskon === 'persen')
                                            Diskon {{ $voucher->nilai_diskon }}%
                                            @if($voucher->maksimal_diskon)
                                                <span class="text-[10px] text-slate-500 font-semibold">(Maks Rp {{ number_format($voucher->maksimal_diskon, 0, ',', '.') }})</span>
                                            @endif
                                        @else
                                            Potongan Rp {{ number_format($voucher->nilai_diskon, 0, ',', '.') }}
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <span class="text-[9px] font-black px-2.5 py-0.5 rounded-full bg-gradient-to-r from-sky-100 to-sky-50 text-sky-700 border border-sky-200/90 shadow-2xs">
                                Promo
                            </span>
                        </div>

                        <!-- TICKET BODY -->
                        <div class="p-3.5 pt-3 bg-gradient-to-b from-transparent to-sky-50/40 flex flex-col gap-2">
                            <div class="flex items-center justify-between text-[11px] text-slate-500">
                                <span>Min. Belanja: <strong class="text-slate-700">Rp {{ number_format($voucher->minimal_belanja, 0, ',', '.') }}</strong></span>
                                <span>Berlaku s/d: <strong class="text-slate-700">{{ \Carbon\Carbon::parse($voucher->tanggal_selesai)->translatedFormat('d M Y') }}</strong></span>
                            </div>

                            <!-- KODE & TOMBOL SALIN -->
                            <div class="mt-1 flex items-center justify-between bg-white/95 backdrop-blur-xs p-2 rounded-xl border border-sky-200/80 shadow-2xs">
                                <div class="flex items-center gap-2 pl-1.5">
                                    <i class="fa-solid fa-ticket text-sky-500 text-xs"></i>
                                    <span class="font-mono font-black text-xs text-sky-900 tracking-wider select-all">{{ $voucher->kode_voucher }}</span>
                                </div>
                                <button type="button" onclick="salinKode('{{ $voucher->kode_voucher }}', this)" 
                                        class="px-3.5 py-1.5 bg-gradient-to-r from-sky-400 via-sky-500 to-sky-600 hover:from-sky-500 hover:to-sky-700 active:scale-95 text-white text-[10px] font-black uppercase tracking-wider rounded-xl transition-all flex items-center gap-1 shadow-xs shadow-sky-500/25">
                                    <i class="fa-regular fa-copy"></i>
                                    <span>Salin</span>
                                </button>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            @else
                <div class="rentify-card flex flex-col items-center justify-center py-16 text-slate-400 text-center px-4 rounded-2xl border border-sky-200/70 shadow-xs">
                    <div class="w-16 h-16 bg-sky-100/80 rounded-2xl flex items-center justify-center mb-3 shadow-xs">
                        <i class="fa-solid fa-ticket text-2xl text-sky-400"></i>
                    </div>
                    <h4 class="font-black text-slate-700 text-sm mb-1">Belum Ada Voucher Tersedia</h4>
                    <p class="text-xs text-slate-500">Saat ini belum ada voucher promo yang aktif.</p>
                </div>
            @endif
        </main>
        
        <!-- BOTTOM NAV MOBILE -->
        <x-bottom-navigation-bar active="akun" />

    </div>

    <!-- TOAST NOTIFIKASI SALIN -->
    <div id="copyToast" class="fixed top-5 left-1/2 -translate-x-1/2 z-50 bg-slate-900/90 backdrop-blur-md text-white px-4 py-2 rounded-full text-xs font-bold shadow-xl opacity-0 pointer-events-none transition-all duration-300 flex items-center gap-2">
        <i class="fa-solid fa-circle-check text-sky-400"></i>
        <span id="toastMsg">Kode voucher berhasil disalin!</span>
    </div>

    <script>
        function salinKode(kode, btn) {
            // Panggil claim endpoint jika tersedia
            fetch('{{ route('customer.voucher.claim') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ kode_voucher: kode })
            }).catch(err => console.log('Claim fetch notice:', err));

            navigator.clipboard.writeText(kode).then(() => {
                const oriHtml = btn.innerHTML;
                btn.innerHTML = '<i class="fa-solid fa-check"></i> <span>Tersalin!</span>';
                btn.classList.add('bg-slate-800');

                const toast = document.getElementById('copyToast');
                document.getElementById('toastMsg').innerText = `Kode "${kode}" berhasil disalin! Gunakan saat checkout.`;
                toast.classList.remove('opacity-0', 'pointer-events-none');
                toast.classList.add('opacity-100');

                setTimeout(() => {
                    toast.classList.remove('opacity-100');
                    toast.classList.add('opacity-0', 'pointer-events-none');
                    btn.innerHTML = oriHtml;
                    btn.classList.remove('bg-slate-800');
                }, 2000);
            }).catch(() => {
                prompt("Salin kode voucher berikut:", kode);
            });
        }
    </script>

</body>
</html>
