@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css"/>
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

<style>
    nav, header, footer { display: none !important; }
    body { background-color: #f8fafc; font-family: 'Segoe UI', Tahoma, sans-serif; padding-bottom: 70px; }
    .detail-container { max-width: 600px; margin: 0 auto; background: white; min-height: 100vh; }
    .swiper-pagination-bullet { background: #cbd5e1; opacity: 1; }
    .swiper-pagination-bullet-active { background: #0ea5e9; width: 16px; border-radius: 8px; }
    input[type=number]::-webkit-inner-spin-button, 
    input[type=number]::-webkit-outer-spin-button { -webkit-appearance: none; margin: 0; }
</style>

<div class="detail-container shadow-sm relative">

    <a href="{{ url()->previous() }}" class="absolute top-4 left-4 z-20 w-8 h-8 bg-black/30 backdrop-blur-sm text-white rounded-full flex items-center justify-center hover:bg-black/50 transition">
        <i class="fa-solid fa-arrow-left text-sm"></i>
    </a>

    <div class="swiper productSwiper w-full aspect-square bg-white border-b border-slate-100">
        <div class="swiper-wrapper">
            
            <div class="swiper-slide flex items-center justify-center p-4">
                @if($barang->cover_photo)
                    @php
                        $coverUrl = str_starts_with($barang->cover_photo, 'http') 
                            ? $barang->cover_photo 
                            : asset(str_replace('public/', '', $barang->cover_photo));
                    @endphp
                    <img src="{{ $coverUrl }}" class="w-full h-full object-contain" onerror="this.src='https://placehold.co/400?text=Foto+Utama+Rusak'">
                @else
                    <i class="fa-solid fa-image text-slate-200 text-6xl"></i>
                @endif
            </div>

            @if(isset($barang->fotos) && $barang->fotos->count() > 0)
                @foreach($barang->fotos as $foto)
                @php
                    $rawPath = $foto->foto_path ?? $foto->foto ?? $foto->gambar ?? '';
                    $fotoUrl = str_starts_with($rawPath, 'http') 
                        ? $rawPath 
                        : asset(str_replace('public/', '', $rawPath));
                @endphp
                
                @if(!empty($rawPath))
                <div class="swiper-slide flex items-center justify-center p-4">
                    <img src="{{ $fotoUrl }}" class="w-full h-full object-contain" onerror="this.src='https://placehold.co/400?text=Foto+Galeri+Rusak'">
                </div>
                @endif
                @endforeach
            @endif

        </div>
        <div class="swiper-pagination"></div>
    </div>

    <div class="p-4 border-b border-slate-100 bg-white">
        <div class="flex items-center justify-between mb-2">
            <span class="text-[10px] font-black text-sky-600 bg-sky-50 px-2 py-0.5 rounded uppercase border border-sky-100">
                🏷️ {{ $barang->kategori->nama ?? 'Umum' }}
            </span>
            
            @php $stokNyata = max(0, $barang->stok_total); @endphp
            <span class="text-[11px] text-slate-500 font-medium">Sisa Stok: <strong class="{{ $stokNyata == 0 ? 'text-rose-500' : 'text-slate-800' }}">{{ $stokNyata }}</strong> unit</span>
        </div>
        
        <h1 class="text-[15px] font-medium text-slate-800 leading-snug mb-2">{{ $barang->nama }}</h1>
        
        <div class="text-sky-500 font-bold text-xl">
            Rp{{ number_format($barang->harga_sewa_customer ?? $barang->harga_sewa_harian, 0, ',', '.') }}<span class="text-[12px] font-normal text-slate-400">/hari</span>
        </div>

        @if($stokNyata <= 0)
            <div class="mt-3 bg-rose-50 border border-rose-200 text-rose-600 px-3 py-2 rounded-lg text-[11.5px] font-bold flex items-center gap-2">
                <i class="fa-solid fa-circle-exclamation text-rose-500 text-sm"></i>
                <span>Barang habis disewakan, mohon tunggu sampai dikembalikan.</span>
            </div>
        @endif
    </div>

    <div class="p-4 border-b border-slate-100 bg-white">
        <h3 class="text-[13px] font-bold text-slate-800 mb-3 flex items-center gap-2"><i class="fa-solid fa-list text-slate-400"></i> Rincian Barang</h3>
        <div class="grid grid-cols-2 gap-y-3 text-[12px]">
            <div>
                <span class="block text-slate-400 mb-0.5">Kondisi</span>
                <span class="font-semibold text-slate-700">{{ $barang->kondisi ?? 'Sangat Baik' }}</span>
            </div>
            <div>
                <span class="block text-slate-400 mb-0.5">Jaminan / Deposit Fisik</span>
                <span class="font-semibold text-amber-600">Rp{{ number_format($barang->deposit ?? 0, 0, ',', '.') }}</span>
            </div>
            @if(isset($barang->denda_per_hari) && $barang->denda_per_hari > 0)
            <div class="col-span-2 pt-2 border-t border-slate-50">
                <span class="block text-slate-400 mb-0.5">Denda Keterlambatan</span>
                <span class="font-semibold text-rose-500">Rp{{ number_format($barang->denda_per_hari, 0, ',', '.') }} / hari</span>
            </div>
            @endif
        </div>
    </div>

    <div class="p-4 bg-white border-b border-slate-100">
        <h3 class="text-[13px] font-bold text-slate-800 mb-2 flex items-center gap-2"><i class="fa-solid fa-align-left text-slate-400"></i> Deskripsi Produk</h3>
        <p class="text-[12px] text-slate-600 leading-relaxed whitespace-pre-line">{{ $barang->deskripsi }}</p>
    </div>

    <div class="p-4 bg-white mb-4 border-b border-slate-100">
        <h3 class="text-[13px] font-bold text-slate-800 mb-2 flex items-center gap-2"><i class="fa-solid fa-store text-slate-400"></i> Informasi Toko</h3>
        <div class="flex items-center justify-between mb-3">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-sky-100 text-sky-500 rounded-full flex items-center justify-center font-black text-lg shadow-sm border border-sky-200">
                    {{ substr($barang->vendor->vendor_name ?? 'V', 0, 1) }}
                </div>
                <div>
                    <div class="font-bold text-slate-700 text-[13px]">{{ $barang->vendor->vendor_name ?? 'Vendor Rentify' }}</div>
                    
                    <div class="text-[10px] text-sky-600 font-semibold mt-0.5 flex items-center gap-1 bg-sky-50 w-fit px-2 py-0.5 rounded border border-sky-100">
                        <i class="fa-solid fa-lock text-[9px]"></i> Maps terbuka setelah sewa
                    </div>
                </div>
            </div>

            @if(isset($barang->jarak))
            <div class="bg-white border border-slate-200 text-slate-600 px-2.5 py-1 rounded-lg text-right flex-shrink-0 shadow-sm">
                <span class="block text-[8px] text-slate-400 uppercase font-black tracking-wider">Jarak Ke Titikmu</span>
                <span class="font-black text-xs flex items-center justify-end gap-1 text-sky-500"><i class="fa-solid fa-location-dot"></i> {{ number_format($barang->jarak, 1, ',', '') }} KM</span>
            </div>
            @endif
        </div>

        <div class="w-full bg-slate-50 border border-slate-200 text-slate-600 font-bold text-[12px] py-2.5 px-3 rounded-lg flex items-center justify-center gap-2 text-center shadow-sm">
            <i class="fa-solid fa-map-location-dot text-sky-500 text-sm"></i> 
            @php
                $alamatFullDetail = $barang->alamat ?? 'Area belum diatur';
                $pecahAlamatDetail = explode(',', $alamatFullDetail);
                $areaSajaDetail = count($pecahAlamatDetail) > 1 ? trim(implode(',', array_slice($pecahAlamatDetail, 1))) : $alamatFullDetail;
            @endphp
            <span class="line-clamp-1 truncate">Area Toko: {{ $areaSajaDetail }}</span>
            @if(isset($barang->jarak))
                <span class="text-slate-400 font-normal">• ±{{ number_format($barang->jarak, 1, ',', '') }} KM</span>
            @endif
        </div>
        <p class="text-[10px] text-slate-400 text-center mt-1.5 font-medium">*Titik Maps akurat & alamat lengkap akan diberikan di struk pesanan.</p>
    </div>

    <div class="fixed bottom-0 left-0 w-full bg-white border-t border-slate-200 px-3 py-2.5 flex items-center justify-center z-50">
        <div class="w-full max-w-md flex gap-2">
            
            @if($stokNyata > 0)
                <form action="{{ route('customer.keranjang.add') }}" method="POST" class="w-1/2">
                    @csrf
                    <input type="hidden" name="barang_id" value="{{ $barang->id }}">
                    <input type="hidden" name="jumlah" value="1">
                    <button type="submit" class="w-full bg-sky-50 text-sky-600 border border-sky-400 font-bold text-[13px] py-2.5 rounded-md flex items-center justify-center gap-2 hover:bg-sky-100 transition">
                        <i class="fa-solid fa-cart-plus"></i> Masukkan
                    </button>
                </form>

                <form action="{{ route('customer.checkout') }}" method="GET" id="form-sewa-sekarang" class="w-1/2">
                    <input type="hidden" name="direct_barang_id" value="{{ $barang->id }}">
                    <input type="hidden" name="jumlah" value="1">
                    <input type="hidden" name="start_date" id="direct-start-date">
                    <input type="hidden" name="start_time" id="direct-start-time">
                    <input type="hidden" name="durasi_sewa" id="direct-durasi">
                    
                    <button type="button" id="btn-sewa-sekarang" class="w-full bg-gradient-to-r from-sky-400 to-sky-600 text-white font-bold text-[13px] py-2.5 rounded-md flex items-center justify-center hover:from-sky-500 hover:to-sky-700 transition shadow-[0_0_10px_rgba(14,165,233,0.3)]">
                        Sewa Sekarang
                    </button>
                </form>
            @else
                <button disabled type="button" class="w-full bg-slate-200 text-slate-500 font-bold text-[13px] py-3 rounded-md flex items-center justify-center gap-2 cursor-not-allowed">
                    <i class="fa-solid fa-ban text-rose-500"></i> Stok Habis / Sedang Disewa
                </button>
            @endif

        </div>
    </div>

</div>

<div id="booking-modal" class="hidden fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm transition-opacity">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-sm overflow-hidden transform scale-100 transition-transform">
        <div class="bg-gradient-to-r from-sky-400 to-sky-600 px-6 py-5 relative">
            <h3 class="text-white font-black text-[17px] flex items-center gap-2">
                <i class="fa-regular fa-calendar-check text-xl"></i> Atur Jadwal Sewa
            </h3>
            <p class="text-sky-100 text-xs font-medium mt-1">Tentukan tanggal mulai dan lama pemakaian.</p>
        </div>
        <div class="p-6">
            <label class="block text-[13px] font-bold text-slate-700 mb-2">Tanggal Mulai <span class="text-rose-500">*</span></label>
            <input type="date" id="modal-date" required class="w-full border-2 border-slate-200 rounded-xl px-4 py-2.5 text-sm font-bold text-sky-700 focus:border-sky-500 focus:ring-sky-200 outline-none mb-4 transition">

            <label class="block text-[13px] font-bold text-slate-700 mb-2">Jam Pengambilan/Pengantaran <span class="text-rose-500">*</span></label>
            <select id="modal-time" required class="w-full border-2 border-slate-200 rounded-xl px-4 py-2.5 text-sm font-bold text-sky-700 focus:border-sky-500 focus:ring-sky-200 outline-none mb-4 transition">
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

            <label class="block text-[13px] font-bold text-slate-700 mb-2">Durasi Sewa <span class="text-rose-500">*</span></label>
            <div class="flex items-center border-2 border-slate-200 rounded-xl px-4 py-2 mb-6 focus-within:border-sky-500 transition">
                <input type="number" id="modal-durasi" value="1" min="1" required class="w-full bg-transparent border-none text-sm font-black text-sky-700 focus:ring-0 p-0 outline-none">
                <span class="text-xs font-bold text-slate-400">Hari</span>
            </div>

            <div class="flex gap-3">
                <button type="button" id="btn-close-modal" class="flex-1 bg-slate-100 text-slate-500 font-bold py-3 rounded-xl hover:bg-slate-200 transition">Batal</button>
                <button type="button" id="btn-confirm-modal" class="flex-1 bg-sky-500 text-white font-bold py-3 rounded-xl hover:bg-sky-600 shadow-[0_0_15px_rgba(14,165,233,0.4)] transition">Lanjut Checkout</button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        var swiper = new Swiper(".productSwiper", {
            pagination: { el: ".swiper-pagination", clickable: true },
            loop: false,
            spaceBetween: 10,
        });

        const btnSewaSekarang = document.getElementById('btn-sewa-sekarang');
        const bookingModal = document.getElementById('booking-modal');
        const btnCloseModal = document.getElementById('btn-close-modal');
        const btnConfirmModal = document.getElementById('btn-confirm-modal');
        const formSewaSekarang = document.getElementById('form-sewa-sekarang');

        if(btnSewaSekarang) {
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('modal-date').setAttribute('min', today);

            btnSewaSekarang.addEventListener('click', function(e) {
                e.preventDefault();
                bookingModal.classList.remove('hidden');
            });

            btnCloseModal.addEventListener('click', function() {
                bookingModal.classList.add('hidden');
            });

            btnConfirmModal.addEventListener('click', function() {
                const date = document.getElementById('modal-date').value;
                const time = document.getElementById('modal-time').value;
                const durasi = document.getElementById('modal-durasi').value;

                if(!date) { alert('⚠️ Silakan pilih Tanggal Mulai!'); return; }
                if(!time) { alert('⚠️ Silakan pilih Jam Pengambilan!'); return; }
                if(!durasi || durasi < 1) { alert('⚠️ Durasi minimal 1 hari!'); return; }

                document.getElementById('direct-start-date').value = date;
                document.getElementById('direct-start-time').value = time;
                document.getElementById('direct-durasi').value = durasi;
                
                formSewaSekarang.submit();
            });
        }
    });
</script>
@endsection