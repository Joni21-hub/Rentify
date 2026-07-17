@extends('layouts.app')

@section('content')
<style>
    nav, header, footer { display: none !important; }
    /* padding-bottom diperbesar jadi 160px agar daftar barang tidak tertutup blok bawah */
    body { background-color: #f8fafc; font-family: 'Segoe UI', Tahoma, sans-serif; padding-bottom: 160px; }
    .cart-container { max-width: 600px; margin: 0 auto; background: #f8fafc; min-height: 100vh; display: flex; flex-direction: column; }
    input[type=number]::-webkit-inner-spin-button, 
    input[type=number]::-webkit-outer-spin-button { -webkit-appearance: none; margin: 0; }
</style>

<div class="cart-container relative">

    <!-- TOP BAR -->
    <div class="bg-white sticky top-0 z-30 px-4 py-4 shadow-sm border-b border-slate-100 flex items-center">
        <div class="flex items-center gap-3">
            <a href="{{ route('customer.home') }}" class="text-slate-600 hover:text-sky-500 transition">
                <i class="fa-solid fa-arrow-left text-xl"></i>
            </a>
            <h1 class="font-black text-slate-800 text-lg">Keranjang Saya ({{ $keranjangs->count() }})</h1>
        </div>
    </div>

    @if(session('error'))
        <div class="m-3 bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 rounded-xl text-sm font-bold flex items-center gap-2">
            <i class="fa-solid fa-circle-exclamation text-rose-500 text-lg"></i> {{ session('error') }}
        </div>
    @endif

    @if($keranjangs->isEmpty())
        <div class="bg-white m-4 p-10 rounded-2xl text-center border border-slate-100 shadow-sm">
            <div class="w-20 h-20 bg-sky-50 text-sky-500 rounded-full flex items-center justify-center text-4xl mx-auto mb-4 shadow-[0_0_15px_rgba(14,165,233,0.3)]">
                <i class="fa-solid fa-cart-shopping"></i>
            </div>
            <h3 class="font-bold text-slate-800 text-lg mb-2">Keranjangmu Masih Kosong</h3>
            <p class="text-sm text-slate-400 mb-6">Yuk, mulai cari alat impianmu dan sewa sekarang!</p>
            <a href="{{ route('customer.home') }}" class="inline-block bg-gradient-to-r from-sky-400 to-sky-600 hover:from-sky-500 hover:to-sky-700 shadow-[0_0_15px_rgba(14,165,233,0.4)] text-white font-bold text-sm px-8 py-3 rounded-xl transition">
                Mulai Belanja
            </a>
        </div>
    @else
        <form action="{{ route('customer.checkout') }}" method="GET" id="cart-form" class="flex flex-col flex-1">
            
            <div class="bg-white border-t border-b border-slate-100 mt-2 shadow-sm">
                @foreach($keranjangs as $item)
                    @php
                        $harga = $item->barang->harga_sewa_customer ?? $item->barang->harga_sewa_harian ?? 0;
                        $durasiAktif = $item->durasi_sewa ?? 1;
                    @endphp
                    <div class="p-4 border-b border-slate-50 flex items-center gap-4 card-item relative" data-id="{{ $item->id }}">
                        
                        <input type="checkbox" name="selected_items[]" value="{{ $item->id }}" class="item-checkbox w-5 h-5 text-sky-500 rounded border-slate-300 focus:ring-sky-500 cursor-pointer flex-shrink-0" data-harga="{{ $harga }}">

                        <div class="w-20 h-20 bg-white rounded-xl border border-slate-100 overflow-hidden flex-shrink-0 flex items-center justify-center p-1">
                            @if($item->barang->cover_photo)
                                <img src="{{ asset(str_replace('public/', '', $item->barang->cover_photo)) }}" class="w-full h-full object-contain">
                            @else
                                <i class="fa-solid fa-image text-slate-300 text-2xl"></i>
                            @endif
                        </div>

                        <div class="flex-1 min-w-0">
                            <button type="button" onclick="confirmDelete('{{ route('customer.keranjang.remove', $item->id) }}')" class="absolute top-4 right-4 text-slate-300 hover:text-rose-500 transition">
                                <i class="fa-solid fa-trash-can text-sm"></i>
                            </button>

                            <h4 class="font-bold text-slate-800 text-base truncate pr-6 mb-1">{{ $item->barang->nama }}</h4>
                            
                            <div class="inline-flex items-center gap-1 bg-sky-50 text-sky-500 text-[11.5px] font-bold px-3 py-1 rounded mb-2 border border-sky-100 shadow-sm cursor-text">
                                <span>Durasi: </span>
                                <input type="number" value="{{ $durasiAktif }}" min="1" class="item-durasi w-8 bg-transparent border-none p-0 text-center text-sky-600 focus:ring-0 outline-none font-black" onfocus="this.select()">
                                <span>Hari</span>
                                <i class="fa-solid fa-pen text-[9px] ml-1 opacity-50"></i>
                            </div>
                            
                            <div class="flex items-center justify-between">
                                <div class="text-sky-500 font-bold text-base">
                                    Rp {{ number_format($harga, 0, ',', '.') }}
                                </div>
                                
                                <div class="flex items-center border border-slate-200 rounded-lg bg-white">
                                    <button type="button" class="btn-minus w-7 h-7 flex items-center justify-center text-slate-500 hover:bg-slate-50 text-lg transition">−</button>
                                    <input type="number" value="{{ $item->jumlah }}" class="item-qty w-8 h-7 text-center border-none text-sm font-bold text-slate-700 p-0 focus:ring-0 bg-transparent" readonly>
                                    <button type="button" class="btn-plus w-7 h-7 flex items-center justify-center text-slate-500 hover:bg-slate-50 text-lg transition">+</button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- BLOK BAWAH TERKUNCI (VOUCHER + CHECKOUT GABUNG JADI SATU) -->
            <div class="fixed bottom-0 left-0 w-full z-40 shadow-[0_-8px_25px_rgba(0,0,0,0.06)]">
                <div class="max-w-[600px] mx-auto bg-white border-t border-slate-200 flex flex-col rounded-t-2xl overflow-hidden">
                    
                    <!-- VOUCHER RENTIFY (DI ATAS TOTAL BAYAR, TERKUNCI DI BAWAH) -->
                    <div id="voucher-container" class="w-full border-b border-slate-100 bg-white transition-all duration-300">
                        <div class="px-5 py-3.5 flex items-center justify-between cursor-pointer hover:bg-slate-50 transition" id="btn-toggle-voucher">
                            <div class="flex items-center gap-2 text-sky-500 font-bold text-sm">
                                <i class="fa-solid fa-ticket text-base"></i> Voucher Rentify
                            </div>
                            <div class="text-xs text-slate-400 font-medium flex items-center gap-2" id="voucher-status-text">
                                Gunakan/masukkan kode <i class="fa-solid fa-chevron-up text-[10px] transition-transform duration-200" id="voucher-arrow"></i>
                            </div>
                        </div>
                        
                        <!-- KOTAK KETIK VOUCHER (Muncul ke atas saat diklik) -->
                        <div id="voucher-input-area" class="hidden px-5 pb-4 pt-1 bg-white">
                            <div class="flex gap-3">
                                <input type="text" id="input-voucher" placeholder="KETIK KODE: RENTIFY" class="flex-1 bg-slate-50 border border-slate-100 rounded-lg px-4 py-2.5 text-sm font-bold uppercase text-sky-700 outline-none focus:border-sky-400 focus:shadow-[0_0_10px_rgba(14,165,233,0.2)] transition">
                                <button type="button" id="btn-apply-voucher" class="bg-gradient-to-r from-sky-400 to-sky-500 hover:from-sky-500 hover:to-sky-600 shadow-[0_0_10px_rgba(14,165,233,0.3)] text-white font-bold text-sm px-6 py-2.5 rounded-lg transition">Pakai</button>
                            </div>
                        </div>
                        <input type="hidden" name="kode_voucher" id="hidden-voucher-code" value="">
                    </div>

                    <!-- AREA TOTAL & TOMBOL CHECKOUT -->
                    <div class="px-5 py-3.5 flex items-center justify-between gap-3 bg-white">
                        <label class="flex items-center gap-2 cursor-pointer select-none">
                            <input type="checkbox" id="select-all" class="w-5 h-5 text-sky-500 rounded border-slate-300 focus:ring-sky-500 shadow-sm">
                            <span class="text-sm font-bold text-slate-700">Semua</span>
                        </label>

                        <div class="flex items-center gap-4">
                            <div class="text-right">
                                <span class="block text-[10px] text-slate-400 font-bold uppercase tracking-wider mb-0.5">Total Pembayaran</span>
                                <span id="original-price" class="hidden text-xs text-slate-400 line-through font-bold block">Rp 0</span>
                                <span id="total-price" class="text-lg font-black text-sky-500">Rp 0</span>
                            </div>
                            
                            <button type="submit" id="btn-checkout" class="bg-gradient-to-r from-sky-400 to-sky-600 shadow-[0_0_15px_rgba(14,165,233,0.4)] disabled:bg-none disabled:bg-slate-300 disabled:shadow-none text-white font-bold text-sm px-7 py-3 rounded-xl transition-all flex items-center gap-1 cursor-pointer" disabled>
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
    document.addEventListener("DOMContentLoaded", function() {
        const itemCheckboxes = document.querySelectorAll('.item-checkbox');
        const selectAllCheckbox = document.getElementById('select-all');
        const totalPriceEl = document.getElementById('total-price');
        const originalPriceEl = document.getElementById('original-price');
        const countBadgeEl = document.getElementById('count-badge');
        const btnCheckout = document.getElementById('btn-checkout');
        
        const inputVoucher = document.getElementById('input-voucher');
        const btnApplyVoucher = document.getElementById('btn-apply-voucher');
        const hiddenVoucherCode = document.getElementById('hidden-voucher-code');
        const statusText = document.getElementById('voucher-status-text');
        
        let diskonPersen = 0;

        // Buka tutup voucher dengan panah yang logis
        document.getElementById('btn-toggle-voucher').addEventListener('click', function() {
            const area = document.getElementById('voucher-input-area');
            const arrow = document.getElementById('voucher-arrow');
            area.classList.toggle('hidden');
            // Panah menghadap atas saat tertutup (karena mengembang ke atas), menghadap bawah saat terbuka
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

            let nominalDiskon = (totalMurni * diskonPersen) / 100;
            let totalSetelahDiskon = totalMurni - nominalDiskon;

            if (diskonPersen > 0 && checkedCount > 0) {
                originalPriceEl.textContent = 'Rp ' + totalMurni.toLocaleString('id-ID');
                originalPriceEl.classList.remove('hidden');
            } else {
                originalPriceEl.classList.add('hidden');
            }

            totalPriceEl.textContent = 'Rp ' + totalSetelahDiskon.toLocaleString('id-ID');
            countBadgeEl.textContent = `(${checkedCount})`;
            
            if (checkedCount > 0) {
                btnCheckout.disabled = false;
                btnCheckout.classList.remove('disabled:bg-slate-300', 'disabled:bg-none');
            } else {
                btnCheckout.disabled = true;
                btnCheckout.classList.add('disabled:bg-slate-300', 'disabled:bg-none');
            }
            
            if(selectAllCheckbox) selectAllCheckbox.checked = (checkedCount === itemCheckboxes.length && checkedCount > 0);
        }

        function updateDatabase(itemId, newQty, newDurasi) {
            const form = document.getElementById('update-form');
            form.action = `/customer/keranjang/${itemId}`;
            document.getElementById('update-jumlah').value = newQty;
            document.getElementById('update-durasi').value = newDurasi;
            fetch(form.action, { method: 'POST', body: new FormData(form), headers: { 'X-Requested-With': 'XMLHttpRequest' } });
        }

        btnApplyVoucher.addEventListener('click', function() {
            const kode = inputVoucher.value.trim().toUpperCase();
            if (kode === 'RENTIFY') {
                diskonPersen = 10; 
                hiddenVoucherCode.value = 'RENTIFY';
                
                statusText.innerHTML = '<span class="bg-sky-500 text-white px-3 py-1 rounded-md shadow-[0_0_12px_rgba(14,165,233,0.6)] font-black text-[10px] tracking-wide">✓ DISKON 10%</span>';
                document.getElementById('voucher-container').classList.add('border-sky-300', 'shadow-[0_0_15px_rgba(14,165,233,0.15)]');
                
                document.getElementById('voucher-input-area').classList.add('hidden');
                document.getElementById('voucher-arrow').style.transform = 'rotate(0deg)';
            } else {
                alert('Kode tidak valid! Coba ketik: RENTIFY');
            }
            calculateTotal();
        });

        itemCheckboxes.forEach(cb => cb.addEventListener('change', calculateTotal));
        if (selectAllCheckbox) selectAllCheckbox.addEventListener('change', function() {
            itemCheckboxes.forEach(cb => cb.checked = selectAllCheckbox.checked);
            calculateTotal();
        });

        document.querySelectorAll('.item-durasi').forEach(input => {
            input.addEventListener('change', function() {
                if(this.value < 1 || this.value === '') this.value = 1;
                const card = this.closest('.card-item');
                card.querySelector('.item-checkbox').checked = true;
                const qty = parseInt(card.querySelector('.item-qty').value) || 1;
                calculateTotal();
                updateDatabase(card.dataset.id, qty, parseInt(this.value));
            });
            input.addEventListener('keyup', function() {
                if(this.value !== '') calculateTotal(); 
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

        calculateTotal();
    });

    function confirmDelete(url) {
        if (confirm('Yakin ingin menghapus barang?')) {
            const f = document.getElementById('delete-form');
            f.action = url; f.submit();
        }
    }
</script>
@endsection