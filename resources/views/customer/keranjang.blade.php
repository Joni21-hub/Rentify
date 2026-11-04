@extends('layouts.app')

@section('content')
<style>
    nav, header, footer { display: none !important; }
    body { 
        background-color: #f8fafc; 
        font-family: var(--rentify-font, 'Plus Jakarta Sans', sans-serif); 
        padding-bottom: 160px; 
    }
    .cart-container { 
        max-width: 600px; 
        margin: 0 auto; 
        background: #f8fafc; 
        min-height: 100vh; 
        display: flex; 
        flex-direction: column; 
    }
    input[type=number]::-webkit-inner-spin-button, 
    input[type=number]::-webkit-outer-spin-button { 
        -webkit-appearance: none; 
        margin: 0; 
    }

    /* Glassmorphism styling untuk tanggal yang penuh dibooking (Rentify Theme) */
    .rentify-date-glass {
        background: rgba(226, 232, 240, 0.45) !important;
        backdrop-filter: blur(8px) !important;
        -webkit-backdrop-filter: blur(8px) !important;
        border: 1px solid rgba(255, 255, 255, 0.85) !important;
        box-shadow: inset 0 1px 2px rgba(255, 255, 255, 0.95), 0 1px 3px rgba(15, 23, 42, 0.04) !important;
        color: #94a3b8 !important;
        font-weight: 700 !important;
        position: relative;
        cursor: pointer !important;
        user-select: none;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .rentify-date-glass:hover {
        background: rgba(203, 213, 225, 0.65) !important;
        border-color: rgba(244, 63, 94, 0.5) !important;
        transform: translateY(-1px) scale(0.97);
    }
    .rentify-date-glass:active {
        transform: scale(0.92);
    }
    .rentify-date-glass::after {
        content: '';
        position: absolute;
        top: 2.5px;
        right: 2.5px;
        width: 4.5px;
        height: 4.5px;
        border-radius: 50%;
        background: #f43f5e;
        opacity: 0.9;
    }
    @keyframes rentifyMicroShake {
        0%, 100% { transform: translateX(0); }
        20%, 60% { transform: translateX(-3px); }
        40%, 80% { transform: translateX(3px); }
    }
    .animate-micro-shake {
        animation: rentifyMicroShake 0.3s ease-in-out;
    }
</style>

<div class="cart-container relative">

    <!-- TOP BAR -->
    <div class="rentify-navbar sticky top-0 z-30 px-4 py-4 flex items-center justify-between bg-white/90 backdrop-blur-md border-b border-slate-100 shadow-xs">
        <div class="flex items-center gap-3">
            <a href="{{ route('customer.home') }}" class="w-8 h-8 rounded-full bg-slate-100 text-slate-600 hover:text-sky-600 hover:bg-sky-50 flex items-center justify-center transition active:scale-95">
                <i class="fa-solid fa-arrow-left text-sm"></i>
            </a>
            <h1 class="font-black text-slate-800 text-base">Keranjang ({{ $keranjangs->count() }})</h1>
        </div>
        <span class="text-[11px] font-bold text-sky-600 bg-sky-50 px-2.5 py-1 rounded-full border border-sky-100">
            Rentify Cart
        </span>
    </div>

    @if(session('error'))
        <div class="m-3 bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 rounded-2xl text-xs font-bold flex items-center gap-2 shadow-xs">
            <i class="fa-solid fa-circle-exclamation text-rose-500 text-base flex-shrink-0"></i> 
            <span>{{ session('error') }}</span>
        </div>
    @endif

    @if($keranjangs->isEmpty())
        <div class="rentify-card m-4 p-10 text-center border border-slate-100 shadow-sm">
            <div class="w-20 h-20 bg-gradient-to-tr from-sky-50 to-sky-100/80 text-sky-500 rounded-3xl flex items-center justify-center mx-auto mb-4 shadow-sm border border-sky-100">
                <svg class="w-10 h-10 stroke-sky-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="8" cy="21" r="1"/>
                    <circle cx="19" cy="21" r="1"/>
                    <path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/>
                </svg>
            </div>
            <h3 class="font-black text-slate-800 text-lg mb-1.5">Keranjangmu Masih Kosong</h3>
            <p class="text-xs text-slate-400 mb-6 max-w-xs mx-auto">Yuk, mulai cari alat impianmu dan sewa sekarang dengan mudah!</p>
            <a href="{{ route('customer.home') }}" class="inline-flex items-center gap-2 bg-gradient-to-r from-sky-400 to-sky-600 hover:from-sky-500 hover:to-sky-700 shadow-[0_4px_14px_rgba(14,165,233,0.35)] text-white font-extrabold text-xs px-6 py-3 rounded-xl transition active:scale-95">
                <i class="fa-solid fa-compass"></i> Mulai Cari Barang
            </a>
        </div>
    @else
        <form action="{{ route('customer.checkout') }}" method="GET" id="cart-form" class="flex flex-col flex-1">
            
            <!-- HIDDEN INPUT UNTUK JADWAL (Akan diisi oleh Modal Kalender Rentify) -->
            <input type="hidden" name="start_date" id="form-start-date">
            <input type="hidden" name="start_time" id="form-start-time">
            <input type="hidden" name="durasi_sewa" id="form-durasi-sewa">

            <div class="bg-white border-t border-b border-slate-100 mt-2 shadow-xs">
                @foreach($keranjangs as $item)
                    @php
                        $harga = $item->barang->harga_sewa_customer ?? $item->barang->harga_sewa_harian ?? 0;
                        $durasiAktif = $item->durasi_sewa ?? 1;
                        $barangNama = $item->barang ? $item->barang->nama : 'Barang Rental';
                        $vendorNama = $item->barang && $item->barang->vendor ? ($item->barang->vendor->vendor_name ?? $item->barang->vendor->name) : 'Vendor Rentify';
                    @endphp
                    <div class="p-4 border-b border-slate-100 flex items-center gap-3.5 card-item relative transition hover:bg-slate-50/50" data-id="{{ $item->id }}" data-barang-id="{{ $item->barang_id }}">
                        
                        <input type="checkbox" name="selected_items[]" value="{{ $item->id }}" class="item-checkbox w-5 h-5 text-sky-500 rounded-md border-slate-300 focus:ring-sky-500 cursor-pointer flex-shrink-0" data-harga="{{ $harga }}">

                        <div class="w-20 h-20 bg-slate-50 rounded-xl border border-slate-200/80 overflow-hidden flex-shrink-0 flex items-center justify-center p-1 relative shadow-2xs">
                            @if($item->barang)
                                <img src="{{ $item->barang->cover_photo_url }}" class="w-full h-full object-contain" alt="{{ $barangNama }}" onerror="this.onerror=null; this.src='{{ asset('storage/placeholders/placeholder-barang.png') }}';">
                            @else
                                <i class="fa-solid fa-image text-slate-300 text-2xl"></i>
                            @endif
                        </div>

                        <div class="flex-1 min-w-0 pr-6">
                            <button type="button" data-url="{{ route('customer.keranjang.remove', $item->id) }}" onclick="confirmDelete(this.dataset.url)" class="absolute top-4 right-4 text-slate-300 hover:text-rose-500 transition p-1" title="Hapus dari keranjang">
                                <i class="fa-solid fa-trash-can text-sm"></i>
                            </button>

                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider truncate mb-0.5">{{ $vendorNama }}</p>
                            <h4 class="font-extrabold text-slate-800 text-[13.5px] truncate leading-tight mb-1.5">{{ $barangNama }}</h4>
                            
                            <!-- DURASI STEPPER -->
                            <div class="inline-flex items-center gap-1.5 bg-sky-50/80 text-sky-700 text-[11px] font-extrabold px-2 py-0.5 rounded-lg border border-sky-100 mb-2">
                                <span class="text-slate-500 text-[10px] font-semibold">Durasi:</span>
                                <button type="button" class="btn-durasi-minus w-5 h-5 rounded flex items-center justify-center text-sky-600 hover:bg-sky-100 transition active:scale-90 font-black">−</button>
                                <input type="number" value="{{ $durasiAktif }}" min="1" class="rentify-input item-durasi w-7 p-0 text-center text-sky-700 font-black text-xs bg-transparent border-none outline-none" onfocus="this.select()">
                                <span class="text-sky-600 text-[10.5px]">Hari</span>
                                <button type="button" class="btn-durasi-plus w-5 h-5 rounded flex items-center justify-center text-sky-600 hover:bg-sky-100 transition active:scale-90 font-black">+</button>
                            </div>
                            
                            <div class="flex items-center justify-between mt-0.5">
                                <div>
                                    <span class="text-sky-600 font-black text-sm">
                                        Rp {{ number_format($harga, 0, ',', '.') }}
                                    </span>
                                    <span class="text-[10px] text-slate-400 font-normal">/hari</span>
                                </div>
                                
                                <!-- QUANTITY STEPPER -->
                                <div class="flex items-center border border-slate-200 rounded-lg bg-white shadow-2xs">
                                    <button type="button" class="btn-minus w-6 h-6 flex items-center justify-center text-slate-600 hover:bg-slate-50 text-xs font-bold transition active:scale-95">−</button>
                                    <input type="number" value="{{ $item->jumlah }}" class="item-qty w-7 h-6 text-center border-none text-xs font-black text-slate-700 p-0 focus:ring-0 bg-transparent" readonly>
                                    <button type="button" class="btn-plus w-6 h-6 flex items-center justify-center text-slate-600 hover:bg-slate-50 text-xs font-bold transition active:scale-95">+</button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- BLOK BAWAH TERKUNCI -->
            <div class="rentify-navbar fixed bottom-0 left-0 w-full z-40 shadow-[0_-8px_25px_rgba(0,0,0,0.06)]">
                <div class="max-w-[600px] mx-auto bg-white border-t border-slate-200 flex flex-col rounded-t-2xl overflow-hidden">
                    
                    <!-- VOUCHER RENTIFY DRAWER -->
                    <div id="voucher-container" class="w-full border-b border-slate-100 bg-white transition-all duration-300">
                        <div class="px-4 py-3 flex items-center justify-between cursor-pointer hover:bg-slate-50 transition" id="btn-toggle-voucher">
                            <div class="flex items-center gap-2 text-sky-600 font-extrabold text-xs">
                                <i class="fa-solid fa-ticket text-sm"></i>
                                <span>Voucher Promo Rentify</span>
                            </div>
                            <div class="text-[11px] text-slate-400 font-bold flex items-center gap-1.5" id="voucher-status-text">
                                <span>Gunakan kode voucher</span>
                                <i class="fa-solid fa-chevron-up text-[9px] transition-all duration-200" id="voucher-arrow"></i>
                            </div>
                        </div>
                        
                        <div id="voucher-input-area" class="hidden px-4 pb-3.5 pt-1 bg-white">
                            <div class="flex gap-2">
                                <input type="text" id="input-voucher" placeholder="Ketik kode promo..." class="rentify-input flex-1 px-3 py-2 text-xs font-bold uppercase text-sky-700 border border-slate-200 rounded-xl outline-none focus:border-sky-400 transition">
                                <button type="button" id="btn-apply-voucher" class="bg-gradient-to-r from-sky-400 to-sky-600 hover:from-sky-500 hover:to-sky-700 text-white font-extrabold text-xs px-5 py-2 rounded-xl transition active:scale-95 shadow-xs">
                                    Pakai
                                </button>
                            </div>
                        </div>
                        <input type="hidden" name="kode_voucher" id="hidden-voucher-code" value="">
                    </div>

                    <!-- TOTAL & CHECKOUT BAR -->
                    <div class="px-4 py-3 flex items-center justify-between gap-3 bg-white">
                        <label class="flex items-center gap-2 cursor-pointer select-none">
                            <input type="checkbox" id="select-all" class="w-5 h-5 text-sky-500 rounded-md border-slate-300 focus:ring-sky-500 shadow-2xs">
                            <span class="text-xs font-extrabold text-slate-700">Semua</span>
                        </label>

                        <div class="flex items-center gap-3">
                            <div class="text-right">
                                <span class="block text-[9.5px] text-slate-400 font-bold uppercase tracking-wider">Total Pembayaran</span>
                                <span id="original-price" class="hidden text-[10px] text-slate-400 line-through font-bold block">Rp 0</span>
                                <span id="total-price" class="text-base font-black text-sky-600">Rp 0</span>
                            </div>
                            
                            <!-- TOMBOL CHECKOUT MEMICU MODAL KALENDER RENTIFY PERSIS SEPERTI SEWA SEKARANG -->
                            <button type="button" id="btn-checkout" class="bg-gradient-to-r from-sky-400 to-sky-600 shadow-[0_4px_14px_rgba(14,165,233,0.35)] disabled:opacity-40 disabled:cursor-not-allowed text-white font-extrabold text-xs px-6 py-2.5 rounded-xl transition-all flex items-center gap-1.5 cursor-pointer hover:from-sky-500 hover:to-sky-700 active:scale-95" disabled>
                                <span>Checkout</span>
                                <span id="count-badge">(0)</span>
                            </button>
                        </div>
                    </div>

                </div>
            </div>

        </form>
    @endif
</div>

<!-- ===================================================================== -->
<!-- MODAL KALENDER INTERAKTIF RENTIFY (DISAMAKAN DENGAN SEWA SEKARANG)    -->
<!-- ===================================================================== -->
<div id="booking-modal" class="hidden fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm transition-opacity">
    <div class="rentify-card w-full max-w-sm max-h-[92vh] overflow-y-auto transform scale-100 transition-all shadow-2xl rounded-2xl bg-white border border-slate-100">
        <div class="bg-gradient-to-r from-sky-400 to-sky-600 px-6 py-5 relative sticky top-0 z-10">
            <h3 class="text-white font-black text-[17px] flex items-center gap-2">
                <i class="fa-regular fa-calendar-check text-xl"></i> Atur Jadwal Sewa
            </h3>
            <p class="text-sky-100 text-xs font-medium mt-1">Tentukan tanggal mulai dan lama pemakaian.</p>
        </div>
        <div class="p-6">
            <!-- PILIH TANGGAL MULAI DENGAN KALENDER INTERAKTIF RENTIFY -->
            <div class="relative mb-4">
                <label class="block text-[13px] font-bold text-slate-700 mb-1.5 flex items-center justify-between">
                    <span>Tanggal Mulai <span class="text-rose-500">*</span></span>
                    <span id="cal-selected-badge" class="hidden text-[10px] font-extrabold text-sky-600 bg-sky-50 px-2 py-0.5 rounded-full border border-sky-200">
                        Terpilih
                    </span>
                </label>

                <!-- Trigger Input Menampilkan Tanggal Terpilih -->
                <button type="button" id="btn-toggle-calendar" class="w-full flex items-center justify-between px-3.5 py-2.5 bg-white border-2 border-slate-200 rounded-xl hover:border-sky-400 focus:border-sky-500 focus:outline-none transition group text-left shadow-xs">
                    <div class="flex items-center gap-2.5 overflow-hidden">
                        <div class="w-8 h-8 rounded-lg bg-sky-50 text-sky-500 flex items-center justify-center flex-shrink-0 group-hover:bg-sky-500 group-hover:text-white transition-colors">
                            <i class="fa-regular fa-calendar-days text-sm"></i>
                        </div>
                        <div>
                            <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider leading-tight">Jadwal Mulai</p>
                            <p id="display-selected-date" class="text-sm font-bold text-slate-400 group-hover:text-sky-600 transition truncate">
                                Pilih tanggal sewa...
                            </p>
                        </div>
                    </div>
                    <i id="cal-chevron" class="fa-solid fa-chevron-up text-xs text-slate-400 group-hover:text-sky-500 transition-transform duration-200"></i>
                </button>

                <!-- Input Hidden untuk Menyimpan Nilai YYYY-MM-DD ke Form -->
                <input type="hidden" id="modal-date" required value="">

                <!-- FLOATING MINI TOAST PERINGATAN RENTIFY (KECIL & MINIMALIS) -->
                <div id="cal-mini-alert" class="hidden pointer-events-none absolute -top-3 left-1/2 -translate-x-1/2 z-30 flex items-center gap-1.5 bg-slate-900/95 backdrop-blur-md text-white text-[11px] font-bold px-3.5 py-1.5 rounded-full shadow-2xl border border-white/20 transition-all duration-200 opacity-0 transform -translate-y-2 whitespace-nowrap">
                    <span class="w-2 h-2 rounded-full bg-rose-500 animate-ping"></span>
                    <span id="cal-mini-alert-text">Full sudah di booking</span>
                </div>

                <!-- BOX KALENDER INTERAKTIF RENTIFY -->
                <div id="rentify-calendar-box" class="mt-2.5 p-3.5 bg-gradient-to-b from-white to-sky-50/40 rounded-2xl border border-sky-100 shadow-sm transition-all duration-200">
                    
                    <!-- Header Bulan & Tahun + Navigasi -->
                    <div class="flex items-center justify-between mb-2.5 px-0.5">
                        <button type="button" id="cal-btn-prev" class="w-7 h-7 rounded-lg bg-white hover:bg-sky-50 text-slate-600 hover:text-sky-600 border border-slate-200 hover:border-sky-300 flex items-center justify-center text-xs transition active:scale-95 shadow-2xs" aria-label="Bulan Sebelumnya">
                            <i class="fa-solid fa-chevron-left text-[10px]"></i>
                        </button>
                        
                        <div class="text-center">
                            <h4 id="cal-month-year" class="text-xs font-black text-slate-800 tracking-wide uppercase">
                                Bulan Tahun
                            </h4>
                        </div>

                        <button type="button" id="cal-btn-next" class="w-7 h-7 rounded-lg bg-white hover:bg-sky-50 text-slate-600 hover:text-sky-600 border border-slate-200 hover:border-sky-300 flex items-center justify-center text-xs transition active:scale-95 shadow-2xs" aria-label="Bulan Berikutnya">
                            <i class="fa-solid fa-chevron-right text-[10px]"></i>
                        </button>
                    </div>

                    <!-- Baris Nama Hari -->
                    <div class="grid grid-cols-7 gap-1 mb-1.5 text-center">
                        <span class="text-[10px] font-extrabold text-rose-500">Min</span>
                        <span class="text-[10px] font-bold text-slate-400">Sen</span>
                        <span class="text-[10px] font-bold text-slate-400">Sel</span>
                        <span class="text-[10px] font-bold text-slate-400">Rab</span>
                        <span class="text-[10px] font-bold text-slate-400">Kam</span>
                        <span class="text-[10px] font-bold text-slate-400">Jum</span>
                        <span class="text-[10px] font-bold text-slate-400">Sab</span>
                    </div>

                    <!-- Grid Tanggal Dinamis -->
                    <div id="cal-days-grid" class="grid grid-cols-7 gap-1 text-center">
                        <!-- Populated by JS -->
                    </div>

                    <!-- Legend / Keterangan Ringkas -->
                    <div class="flex items-center justify-between text-[10px] text-slate-500 pt-2.5 mt-2.5 border-t border-slate-100 px-1 font-medium">
                        <div class="flex items-center gap-1.5">
                            <span class="w-2.5 h-2.5 rounded-full bg-gradient-to-r from-sky-400 to-sky-600 inline-block shadow-2xs"></span>
                            <span class="text-slate-600 font-bold">Dipilih</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="w-2.5 h-2.5 rounded-md bg-white border border-slate-300 inline-block"></span>
                            <span>Tersedia</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="w-2.5 h-2.5 rounded-md rentify-date-glass inline-block border border-slate-300/70"></span>
                            <span class="text-slate-500 font-bold">Penuh (Glass)</span>
                        </div>
                    </div>

                </div>
            </div>

            <!-- PILIH JAM PENGAMBILAN / PENGANTARAN -->
            <label class="block text-[13px] font-bold text-slate-700 mb-2">Jam Pengambilan/Pengantaran <span class="text-rose-500">*</span></label>
            <select id="modal-time" required class="rentify-input w-full px-4 py-2.5 text-sm font-bold text-sky-700 focus:outline-none mb-4 transition border-2 border-slate-200 rounded-xl focus:border-sky-500">
                <option value="">-- Pilih Jam (WIB) --</option>
                <option value="08:00">08:00 WIB (Pagi)</option>
                <option value="09:00">09:00 WIB</option>
                <option value="10:00">10:00 WIB</option>
                <option value="11:00">11:00 WIB</option>
                <option value="12:00">12:00 WIB (Siang)</option>
                <option value="13:00">13:00 WIB</option>
                <option value="14:00">14:00 WIB</option>
                <option value="15:00">15:00 WIB (Sore)</option>
                <option value="16:00">16:00 WIB</option>
                <option value="17:00">17:00 WIB</option>
                <option value="18:00">18:00 WIB (Malam)</option>
                <option value="19:00">19:00 WIB</option>
                <option value="20:00">20:00 WIB</option>
            </select>

            <!-- PILIH DURASI SEWA (DENGAN PRESET CHIPS PERSIS SEPERTI SEWA SEKARANG) -->
            <label class="block text-[13px] font-bold text-slate-700 mb-2">Durasi Sewa <span class="text-rose-500">*</span></label>
            <div class="flex items-center border-2 border-slate-200 rounded-xl px-4 py-2 mb-2 focus-within:border-sky-500 transition">
                <input type="number" id="modal-durasi" value="1" min="1" required class="rentify-input w-full text-sm font-black text-sky-700 p-0 outline-none border-none">
                <span class="text-xs font-bold text-slate-400">Hari</span>
            </div>

            <div class="flex flex-wrap gap-1.5 mb-6">
                <button type="button" onclick="setModalDurasi(1)" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-sky-50 text-[11px] font-bold text-slate-600 hover:text-sky-600 transition">1 Hari</button>
                <button type="button" onclick="setModalDurasi(3)" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-sky-50 text-[11px] font-bold text-slate-600 hover:text-sky-600 transition">3 Hari</button>
                <button type="button" onclick="setModalDurasi(7)" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-sky-50 text-[11px] font-bold text-slate-600 hover:text-sky-600 transition">7 Hari (1 Mgg)</button>
                <button type="button" onclick="setModalDurasi(14)" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-sky-50 text-[11px] font-bold text-slate-600 hover:text-sky-600 transition">14 Hari</button>
                <button type="button" onclick="setModalDurasi(30)" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-sky-50 text-[11px] font-bold text-slate-600 hover:text-sky-600 transition">30 Hari</button>
            </div>

            <!-- TOMBOL AKSI MODAL -->
            <div class="flex gap-3">
                <button type="button" id="btn-close-modal" class="flex-1 bg-slate-100 text-slate-500 font-bold py-3 rounded-xl hover:bg-slate-200 transition">Batal</button>
                <button type="button" id="btn-confirm-modal" class="flex-1 bg-gradient-to-r from-sky-400 to-sky-600 text-white font-extrabold py-3 rounded-xl hover:from-sky-500 hover:to-sky-700 shadow-[0_4px_15px_rgba(14,165,233,0.35)] transition transform hover:-translate-y-0.5 active:translate-y-0">
                    Lanjut Checkout
                </button>
            </div>
        </div>
    </div>
</div>

<form id="update-form" method="POST" style="display: none;">
    @csrf
    @method('PATCH')
    <input type="hidden" name="jumlah" id="update-jumlah">
    <input type="hidden" name="durasi_sewa" id="update-durasi">
</form>
<form id="delete-form" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

<script>
    // DATA DARI CONTROLLER UNTUK DETEKSI JADWAL BENTROK PER BARANG
    const fullyBookedByBarang = @json($fullyBookedByBarang ?? []);
    const bookedSlotsByBarang = @json($bookedSlotsByBarang ?? []);
    const allFullyBookedDates = @json($fullyBookedDates ?? []);

    function setModalDurasi(val) {
        const inp = document.getElementById('modal-durasi');
        if (inp) inp.value = val;
    }

    document.addEventListener("DOMContentLoaded", function() {
        const itemCheckboxes = document.querySelectorAll('.item-checkbox');
        const selectAllCheckbox = document.getElementById('select-all');
        const totalPriceEl = document.getElementById('total-price');
        const originalPriceEl = document.getElementById('original-price');
        const countBadgeEl = document.getElementById('count-badge');
        
        // Elemen Modal Kalender
        const btnCheckout = document.getElementById('btn-checkout');
        const bookingModal = document.getElementById('booking-modal');
        const btnCloseModal = document.getElementById('btn-close-modal');
        const btnConfirmModal = document.getElementById('btn-confirm-modal');
        const formCart = document.getElementById('cart-form');
        
        // Elemen Kalender Interaktif
        const rentifyCalendarBox = document.getElementById('rentify-calendar-box');
        const calBtnPrev = document.getElementById('cal-btn-prev');
        const calBtnNext = document.getElementById('cal-btn-next');
        const calMonthYear = document.getElementById('cal-month-year');
        const calDaysGrid = document.getElementById('cal-days-grid');
        const modalDateInput = document.getElementById('modal-date');
        const modalTimeSelect = document.getElementById('modal-time');
        const displaySelectedDate = document.getElementById('display-selected-date');
        const calSelectedBadge = document.getElementById('cal-selected-badge');
        const calMiniAlert = document.getElementById('cal-mini-alert');
        const calMiniAlertText = document.getElementById('cal-mini-alert-text');
        const btnToggleCalendar = document.getElementById('btn-toggle-calendar');
        const calChevron = document.getElementById('cal-chevron');
        
        // Voucher
        const inputVoucher = document.getElementById('input-voucher');
        const btnApplyVoucher = document.getElementById('btn-apply-voucher');
        const hiddenVoucherCode = document.getElementById('hidden-voucher-code');
        const statusText = document.getElementById('voucher-status-text');
        
        let diskonPersen = 0;
        let diskonNominal = 0;

        const realNow = new Date();
        const currentRealYear = realNow.getFullYear();
        const currentRealMonth = realNow.getMonth();
        const currentRealDay = realNow.getDate();

        let viewYear = currentRealYear;
        let viewMonth = currentRealMonth;
        let selectedDateStr = '';
        let miniAlertTimer = null;

        const namaBulan = [
            'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
        ];
        const namaHari = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];

        function showMiniAlert(msg) {
            if (!calMiniAlert) return;
            if (miniAlertTimer) clearTimeout(miniAlertTimer);
            if (calMiniAlertText) calMiniAlertText.textContent = msg;

            calMiniAlert.classList.remove('hidden');
            requestAnimationFrame(() => {
                calMiniAlert.classList.remove('opacity-0', '-translate-y-2');
                calMiniAlert.classList.add('opacity-100', 'translate-y-0');
            });

            miniAlertTimer = setTimeout(() => {
                calMiniAlert.classList.remove('opacity-100', 'translate-y-0');
                calMiniAlert.classList.add('opacity-0', '-translate-y-2');
                setTimeout(() => {
                    calMiniAlert.classList.add('hidden');
                }, 250);
            }, 2500);
        }

        function formatDisplayDate(dateStr) {
            const parts = dateStr.split('-');
            const d = new Date(parseInt(parts[0]), parseInt(parts[1]) - 1, parseInt(parts[2]));
            const dayName = namaHari[d.getDay()];
            const dayNum = d.getDate();
            const monthName = namaBulan[d.getMonth()].substring(0, 3);
            const yearNum = d.getFullYear();
            return `${dayName}, ${dayNum} ${monthName} ${yearNum}`;
        }

        // Ambil daftar tanggal penuh khusus untuk barang-barang yang sedang dicentang
        function getSelectedItemsBookedDates() {
            const checkedBoxes = Array.from(itemCheckboxes).filter(cb => cb.checked);
            if (checkedBoxes.length === 0) return allFullyBookedDates;

            const datesSet = new Set();
            checkedBoxes.forEach(cb => {
                const card = cb.closest('.card-item');
                const cartId = card.dataset.id;
                const bDates = fullyBookedByBarang[cartId] || [];
                bDates.forEach(d => datesSet.add(d));
            });
            return Array.from(datesSet);
        }

        const defaultTimeOptions = [
            { value: '08:00', label: '08:00 WIB (Pagi)' },
            { value: '09:00', label: '09:00 WIB' },
            { value: '10:00', label: '10:00 WIB' },
            { value: '11:00', label: '11:00 WIB' },
            { value: '12:00', label: '12:00 WIB (Siang)' },
            { value: '13:00', label: '13:00 WIB' },
            { value: '14:00', label: '14:00 WIB' },
            { value: '15:00', label: '15:00 WIB (Sore)' },
            { value: '16:00', label: '16:00 WIB' },
            { value: '17:00', label: '17:00 WIB' },
            { value: '18:00', label: '18:00 WIB (Malam)' },
            { value: '19:00', label: '19:00 WIB' },
            { value: '20:00', label: '20:00 WIB' }
        ];

        function getSelectedItemsBookedSlots(dateStr) {
            const checkedBoxes = Array.from(itemCheckboxes).filter(cb => cb.checked);
            const slotsSet = new Set();
            checkedBoxes.forEach(cb => {
                const card = cb.closest('.card-item');
                const cartId = card.dataset.id;
                const bSlotsMap = bookedSlotsByBarang[cartId] || {};
                const bSlots = bSlotsMap[dateStr] || [];
                bSlots.forEach(s => slotsSet.add(s));
            });
            return Array.from(slotsSet);
        }

        function updateModalTimeOptions(dateStr) {
            if (!modalTimeSelect) return;
            const bookedForDate = dateStr ? getSelectedItemsBookedSlots(dateStr) : [];
            const curVal = modalTimeSelect.value;
            let curValValid = false;

            let html = '<option value="">-- Pilih Jam (WIB) --</option>';
            defaultTimeOptions.forEach(opt => {
                const isBooked = bookedForDate.includes(opt.value);
                if (isBooked) {
                    html += `<option value="${opt.value}" disabled class="text-slate-300 bg-slate-50">${opt.label} (Penuh)</option>`;
                } else {
                    const sel = (opt.value === curVal);
                    if (sel) curValValid = true;
                    html += `<option value="${opt.value}" ${sel ? 'selected' : ''}>${opt.label}</option>`;
                }
            });

            modalTimeSelect.innerHTML = html;
            if (!curValValid && curVal !== '') {
                modalTimeSelect.value = '';
            }
        }

        function renderCalendar() {
            if (!calDaysGrid || !calMonthYear) return;

            calMonthYear.textContent = `${namaBulan[viewMonth]} ${viewYear}`;

            if (viewYear < currentRealYear || (viewYear === currentRealYear && viewMonth <= currentRealMonth)) {
                calBtnPrev.disabled = true;
                calBtnPrev.classList.add('opacity-40', 'cursor-not-allowed');
            } else {
                calBtnPrev.disabled = false;
                calBtnPrev.classList.remove('opacity-40', 'cursor-not-allowed');
            }

            calDaysGrid.innerHTML = '';

            const firstDayIndex = new Date(viewYear, viewMonth, 1).getDay();
            const totalDays = new Date(viewYear, viewMonth + 1, 0).getDate();
            const bookedList = getSelectedItemsBookedDates();

            for (let i = 0; i < firstDayIndex; i++) {
                const emptyCell = document.createElement('div');
                emptyCell.className = 'w-full aspect-square';
                calDaysGrid.appendChild(emptyCell);
            }

            for (let day = 1; day <= totalDays; day++) {
                const dayStr = `${viewYear}-${String(viewMonth + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
                const isPast = (viewYear < currentRealYear) || 
                               (viewYear === currentRealYear && viewMonth < currentRealMonth) ||
                               (viewYear === currentRealYear && viewMonth === currentRealMonth && day < currentRealDay);

                const isFull = bookedList.includes(dayStr);
                const isSelected = (dayStr === selectedDateStr);

                const btn = document.createElement('button');
                btn.type = 'button';
                btn.textContent = day;

                if (isPast) {
                    btn.className = 'w-full aspect-square flex items-center justify-center text-[11px] font-medium text-slate-300 rounded-xl cursor-not-allowed select-none opacity-40';
                    btn.disabled = true;
                } else if (isFull) {
                    btn.className = 'w-full aspect-square flex items-center justify-center text-[11px] rounded-xl rentify-date-glass';
                    btn.setAttribute('aria-label', `Tanggal ${day} ${namaBulan[viewMonth]} sudah penuh dibooking`);
                    btn.setAttribute('title', 'Full sudah di booking');

                    btn.addEventListener('click', function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        btn.classList.add('animate-micro-shake');
                        setTimeout(() => btn.classList.remove('animate-micro-shake'), 320);
                        showMiniAlert('Full sudah di booking untuk barang terpilih');
                    });
                } else {
                    if (isSelected) {
                        btn.className = 'w-full aspect-square flex items-center justify-center text-[11px] font-black rounded-xl bg-gradient-to-r from-sky-400 to-sky-600 text-white shadow-[0_3px_10px_rgba(14,165,233,0.45)] border border-sky-400 scale-105 transition active:scale-95';
                    } else {
                        btn.className = 'w-full aspect-square flex items-center justify-center text-[11px] font-bold text-slate-700 rounded-xl hover:bg-sky-50 hover:text-sky-600 hover:border-sky-300 border border-transparent transition active:scale-95 bg-white';
                    }

                    btn.addEventListener('click', function(e) {
                        e.preventDefault();
                        selectedDateStr = dayStr;
                        if (modalDateInput) modalDateInput.value = dayStr;
                        if (displaySelectedDate) {
                            displaySelectedDate.textContent = formatDisplayDate(dayStr);
                            displaySelectedDate.classList.remove('text-slate-400');
                            displaySelectedDate.classList.add('text-sky-700', 'font-black');
                        }
                        if (calSelectedBadge) calSelectedBadge.classList.remove('hidden');

                        updateModalTimeOptions(dayStr);
                        renderCalendar();
                    });
                }

                calDaysGrid.appendChild(btn);
            }
        }

        if (calBtnPrev) {
            calBtnPrev.addEventListener('click', function(e) {
                e.preventDefault();
                if (viewMonth === 0) {
                    viewMonth = 11;
                    viewYear--;
                } else {
                    viewMonth--;
                }
                renderCalendar();
            });
        }

        if (calBtnNext) {
            calBtnNext.addEventListener('click', function(e) {
                e.preventDefault();
                if (viewMonth === 11) {
                    viewMonth = 0;
                    viewYear++;
                } else {
                    viewMonth++;
                }
                renderCalendar();
            });
        }

        if (btnToggleCalendar && rentifyCalendarBox && calChevron) {
            let isCalendarOpen = true;
            btnToggleCalendar.addEventListener('click', function(e) {
                e.preventDefault();
                isCalendarOpen = !isCalendarOpen;
                if (isCalendarOpen) {
                    rentifyCalendarBox.classList.remove('hidden');
                    calChevron.classList.remove('fa-chevron-down');
                    calChevron.classList.add('fa-chevron-up');
                } else {
                    rentifyCalendarBox.classList.add('hidden');
                    calChevron.classList.remove('fa-chevron-up');
                    calChevron.classList.add('fa-chevron-down');
                }
            });
        }

        // 1. Munculkan Modal saat Checkout diklik
        btnCheckout.addEventListener('click', function(e) {
            e.preventDefault(); 
            // Sinkronkan nilai durasi awal ke modal dari barang terpilih pertama
            const checkedBoxes = Array.from(itemCheckboxes).filter(cb => cb.checked);
            if (checkedBoxes.length > 0) {
                const firstCard = checkedBoxes[0].closest('.card-item');
                const curDur = parseInt(firstCard.querySelector('.item-durasi').value) || 1;
                document.getElementById('modal-durasi').value = curDur;
            }
            bookingModal.classList.remove('hidden');
            updateModalTimeOptions(selectedDateStr);
            renderCalendar();
        });

        // 2. Tutup Modal
        btnCloseModal.addEventListener('click', function() {
            bookingModal.classList.add('hidden');
        });

        // 3. Konfirmasi Modal & Submit Asli
        btnConfirmModal.addEventListener('click', function() {
            const date = document.getElementById('modal-date').value;
            const time = document.getElementById('modal-time').value;
            const durasi = parseInt(document.getElementById('modal-durasi').value) || 1;

            if(!date) { 
                showMiniAlert('⚠️ Silakan pilih Tanggal Mulai!'); 
                return; 
            }
            
            const bookedList = getSelectedItemsBookedDates();
            if(bookedList.includes(date)) {
                showMiniAlert('Full sudah di booking untuk barang terpilih');
                return;
            }

            if(!time) { 
                showMiniAlert('⚠️ Silakan pilih Jam Pengambilan!'); 
                return; 
            }

            const bookedSlots = getSelectedItemsBookedSlots(date);
            if(bookedSlots.includes(time)) {
                showMiniAlert(`⚠️ Jam ${time} WIB pada tanggal tersebut sudah penuh dibooking! Silakan pilih jam lain.`);
                return;
            }

            if(!durasi || durasi < 1) { 
                showMiniAlert('⚠️ Durasi minimal 1 hari!'); 
                return; 
            }

            // Validasi bentrok tanggal pada rentang durasi sewa
            let isConflict = false;
            let conflictDate = '';
            const parts = date.split('-');
            const startD = new Date(parseInt(parts[0]), parseInt(parts[1]) - 1, parseInt(parts[2]));

            for (let i = 0; i < durasi; i++) {
                const nextD = new Date(startD);
                nextD.setDate(nextD.getDate() + i);
                const chkStr = `${nextD.getFullYear()}-${String(nextD.getMonth() + 1).padStart(2, '0')}-${String(nextD.getDate()).padStart(2, '0')}`;
                if (bookedList.includes(chkStr)) {
                    isConflict = true;
                    conflictDate = chkStr;
                    break;
                }
            }

            if (isConflict) {
                showMiniAlert(`⚠️ Tanggal ${conflictDate} pada durasi sewa sudah penuh!`);
                return;
            }

            // Update nilai durasi pada seluruh barang terpilih agar sinkron
            document.querySelectorAll('.item-checkbox:checked').forEach(cb => {
                const card = cb.closest('.card-item');
                card.querySelector('.item-durasi').value = durasi;
                const qty = parseInt(card.querySelector('.item-qty').value) || 1;
                updateDatabase(card.dataset.id, qty, durasi);
            });

            // Pindahkan data ke hidden input form checkout
            document.getElementById('form-start-date').value = date;
            document.getElementById('form-start-time').value = time;
            document.getElementById('form-durasi-sewa').value = durasi;
            
            formCart.submit();
        });

        document.getElementById('btn-toggle-voucher').addEventListener('click', function() {
            const area = document.getElementById('voucher-input-area');
            const arrow = document.getElementById('voucher-arrow');
            area.classList.toggle('hidden');
            arrow.style.transform = area.classList.contains('hidden') ? 'rotate(0deg)' : 'rotate(180deg)';
        });

        function calculateTotal() {
            let totalMurni = 0;
            let checkedCount = 0;

            itemCheckboxes.forEach(cb => {
                if (cb.checked) {
                    checkedCount++;
                    const card = cb.closest('.card-item');
                    const harga = parseFloat(cb.dataset.harga) || 0;
                    const durasi = parseInt(card.querySelector('.item-durasi').value) || 1;
                    const jumlah = parseInt(card.querySelector('.item-qty').value) || 1;
                    totalMurni += (harga * jumlah * durasi);
                }
            });

            let potong = (totalMurni * diskonPersen) / 100 + diskonNominal;
            let totalSetelahDiskon = Math.max(0, totalMurni - potong);

            if (potong > 0 && checkedCount > 0) {
                originalPriceEl.textContent = 'Rp ' + Math.round(totalMurni).toLocaleString('id-ID');
                originalPriceEl.classList.remove('hidden');
            } else {
                originalPriceEl.classList.add('hidden');
            }

            totalPriceEl.textContent = 'Rp ' + Math.round(totalSetelahDiskon).toLocaleString('id-ID');
            countBadgeEl.textContent = `(${checkedCount})`;
            
            if (checkedCount > 0) {
                btnCheckout.disabled = false;
                btnCheckout.classList.remove('opacity-40', 'cursor-not-allowed');
            } else {
                btnCheckout.disabled = true;
                btnCheckout.classList.add('opacity-40', 'cursor-not-allowed');
            }
            
            if(selectAllCheckbox) selectAllCheckbox.checked = (checkedCount === itemCheckboxes.length && checkedCount > 0);
        }

        function updateDatabase(itemId, newQty, newDurasi) {
            const form = document.getElementById('update-form');
            form.action = `/customer/keranjang/${itemId}`;
            document.getElementById('update-jumlah').value = newQty;
            document.getElementById('update-durasi').value = newDurasi;
            fetch(form.action, { 
                method: 'POST', 
                body: new FormData(form), 
                headers: { 'X-Requested-With': 'XMLHttpRequest' } 
            });
        }

        btnApplyVoucher.addEventListener('click', function() {
            const kode = inputVoucher.value.trim().toUpperCase();
            if (!kode) {
                showMiniAlert('Silakan masukkan kode voucher');
                return;
            }

            // AJAX Cek Voucher resmi via CheckoutController
            fetch("{{ route('customer.checkout.cek_voucher') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ kode_voucher: kode })
            })
            .then(res => res.json())
            .then(data => {
                if (data && data.success) {
                    hiddenVoucherCode.value = kode;
                    diskonNominal = parseFloat(data.potongan) || 0;
                    diskonPersen = 0;

                    statusText.innerHTML = `<span class="bg-sky-500 text-white px-2.5 py-0.5 rounded-full font-black text-[9.5px]">✓ ${data.message || 'Diskon Aktif'}</span>`;
                    document.getElementById('voucher-container').classList.add('border-sky-300', 'bg-sky-50/30');
                    document.getElementById('voucher-input-area').classList.add('hidden');
                    document.getElementById('voucher-arrow').style.transform = 'rotate(0deg)';
                    calculateTotal();
                } else if (kode === 'RENTIFY') {
                    // Fallback promo default Rentify
                    diskonPersen = 10;
                    diskonNominal = 0;
                    hiddenVoucherCode.value = 'RENTIFY';
                    statusText.innerHTML = '<span class="bg-sky-500 text-white px-2.5 py-0.5 rounded-full font-black text-[9.5px]">✓ DISKON 10%</span>';
                    document.getElementById('voucher-container').classList.add('border-sky-300', 'bg-sky-50/30');
                    document.getElementById('voucher-input-area').classList.add('hidden');
                    document.getElementById('voucher-arrow').style.transform = 'rotate(0deg)';
                    calculateTotal();
                } else {
                    showMiniAlert(data && data.message ? data.message : 'Kode voucher tidak valid!');
                }
            })
            .catch(() => {
                if (kode === 'RENTIFY') {
                    diskonPersen = 10;
                    hiddenVoucherCode.value = 'RENTIFY';
                    statusText.innerHTML = '<span class="bg-sky-500 text-white px-2.5 py-0.5 rounded-full font-black text-[9.5px]">✓ DISKON 10%</span>';
                    calculateTotal();
                } else {
                    showMiniAlert('Gagal memeriksa voucher. Coba lagi!');
                }
            });
        });

        itemCheckboxes.forEach(cb => {
            cb.addEventListener('change', function() {
                calculateTotal();
                renderCalendar();
            });
        });

        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', function() {
                itemCheckboxes.forEach(cb => cb.checked = selectAllCheckbox.checked);
                calculateTotal();
                renderCalendar();
            });
        }

        // HANDLER STEPPER DURASI ITEM
        document.querySelectorAll('.btn-durasi-plus').forEach(btn => {
            btn.addEventListener('click', function() {
                const input = this.previousElementSibling.previousElementSibling;
                input.value = (parseInt(input.value) || 1) + 1;
                const card = this.closest('.card-item');
                card.querySelector('.item-checkbox').checked = true;
                calculateTotal();
                renderCalendar();
                updateDatabase(card.dataset.id, card.querySelector('.item-qty').value, input.value);
            });
        });

        document.querySelectorAll('.btn-durasi-minus').forEach(btn => {
            btn.addEventListener('click', function() {
                const input = this.nextElementSibling;
                const cur = parseInt(input.value) || 1;
                if (cur > 1) {
                    input.value = cur - 1;
                    const card = this.closest('.card-item');
                    card.querySelector('.item-checkbox').checked = true;
                    calculateTotal();
                    renderCalendar();
                    updateDatabase(card.dataset.id, card.querySelector('.item-qty').value, input.value);
                }
            });
        });

        document.querySelectorAll('.item-durasi').forEach(input => {
            input.addEventListener('change', function() {
                if(this.value < 1 || this.value === '') this.value = 1;
                const card = this.closest('.card-item');
                card.querySelector('.item-checkbox').checked = true;
                const qty = parseInt(card.querySelector('.item-qty').value) || 1;
                calculateTotal();
                renderCalendar();
                updateDatabase(card.dataset.id, qty, parseInt(this.value));
            });
            input.addEventListener('keyup', function() {
                if(this.value !== '') {
                    calculateTotal();
                    renderCalendar();
                }
            });
        });

        document.querySelectorAll('.btn-plus').forEach(btn => {
            btn.addEventListener('click', function() {
                const input = this.previousElementSibling;
                input.value = parseInt(input.value) + 1;
                const card = this.closest('.card-item');
                card.querySelector('.item-checkbox').checked = true;
                calculateTotal();
                updateDatabase(card.dataset.id, input.value, card.querySelector('.item-durasi').value);
            });
        });

        document.querySelectorAll('.btn-minus').forEach(btn => {
            btn.addEventListener('click', function() {
                const input = this.nextElementSibling;
                if (parseInt(input.value) > 1) {
                    input.value = parseInt(input.value) - 1;
                    const card = this.closest('.card-item');
                    calculateTotal();
                    updateDatabase(card.dataset.id, input.value, card.querySelector('.item-durasi').value);
                }
            });
        });

        // Centang semua secara default saat awal buka keranjang
        itemCheckboxes.forEach(cb => cb.checked = true);
        if (selectAllCheckbox) selectAllCheckbox.checked = (itemCheckboxes.length > 0);

        calculateTotal();
    });

    function confirmDelete(url) {
        if (confirm('Yakin ingin menghapus barang dari keranjang?')) {
            const f = document.getElementById('delete-form');
            f.action = url; 
            f.submit();
        }
    }
</script>
@endsection
