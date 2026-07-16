@extends('layouts.app')

@section('content')
<style>
    nav, header, footer { display: none !important; }
    body { background-color: #f8fafc; font-family: 'Segoe UI', Tahoma, sans-serif; padding-bottom: 90px; }
    .cart-container { max-width: 600px; margin: 0 auto; background: #f8fafc; min-height: 100vh; }
    input[type=number]::-webkit-inner-spin-button, 
    input[type=number]::-webkit-outer-spin-button { -webkit-appearance: none; margin: 0; }
</style>

<div class="cart-container relative">

    <!-- TOP BAR -->
    <div class="bg-white sticky top-0 z-30 px-4 py-4 shadow-sm border-b border-slate-100 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ route('customer.home') }}" class="text-slate-600 hover:text-sky-500 transition">
                <i class="fa-solid fa-arrow-left text-xl"></i>
            </a>
            <h1 class="font-black text-slate-800 text-lg">Keranjang Saya ({{ $keranjangs->count() }})</h1>
        </div>
        <a href="#" class="text-sky-500 font-bold text-sm">Ubah</a>
    </div>

    @if(session('error'))
        <div class="m-3 bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 rounded-xl text-sm font-bold flex items-center gap-2">
            <i class="fa-solid fa-circle-exclamation text-rose-500 text-lg"></i> {{ session('error') }}
        </div>
    @endif

    @if($keranjangs->isEmpty())
        <div class="bg-white m-4 p-10 rounded-2xl text-center border border-slate-100 shadow-sm">
            <div class="w-20 h-20 bg-sky-50 text-sky-500 rounded-full flex items-center justify-center text-4xl mx-auto mb-4">
                <i class="fa-solid fa-cart-shopping"></i>
            </div>
            <h3 class="font-bold text-slate-800 text-lg mb-2">Keranjangmu Masih Kosong</h3>
            <p class="text-sm text-slate-400 mb-6">Yuk, mulai cari alat impianmu dan sewa sekarang!</p>
            <a href="{{ route('customer.home') }}" class="inline-block bg-sky-500 hover:bg-sky-600 text-white font-bold text-sm px-8 py-3 rounded-xl shadow-md transition">
                Mulai Belanja
            </a>
        </div>
    @else
        <form action="{{ route('customer.checkout') }}" method="GET" id="cart-form">
            
            <!-- LIST BARANG -->
            <div class="bg-white border-t border-b border-slate-100 mt-2 mb-3">
                @foreach($keranjangs as $item)
                    @php
                        $harga = $item->barang->harga_sewa_customer ?? $item->barang->harga_sewa_harian ?? 0;
                        $durasiAktif = $item->durasi_sewa ?? 1;
                    @endphp
                    <div class="p-4 border-b border-slate-50 flex items-center gap-4 card-item relative" data-id="{{ $item->id }}">
                        
                        <input type="checkbox" 
                               name="selected_items[]" 
                               value="{{ $item->id }}" 
                               class="item-checkbox w-5 h-5 text-sky-500 rounded border-slate-300 focus:ring-sky-500 cursor-pointer flex-shrink-0"
                               data-harga="{{ $harga }}">

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
                            
                            <!-- FITUR BARU: DROPDOWN PILIHAN HARI (BISA PILIH 1 - 14 HARI) -->
                            <div class="mb-2">
                                <select class="item-durasi bg-sky-50 border border-sky-200 text-sky-600 text-xs font-bold rounded-lg px-2.5 py-1 outline-none cursor-pointer hover:bg-sky-100 transition">
                                    @for($i = 1; $i <= 14; $i++)
                                        <option value="{{ $i }}" {{ $durasiAktif == $i ? 'selected' : '' }}>Durasi: {{ $i }} Hari</option>
                                    @endfor
                                </select>
                            </div>
                            
                            <div class="flex items-center justify-between">
                                <div class="text-sky-500 font-bold text-base">
                                    Rp {{ number_format($harga, 0, ',', '.') }}<span class="text-[11px] text-slate-400 font-normal">/hari</span>
                                </div>
                                
                                <div class="flex items-center border border-slate-200 rounded-lg bg-white">
                                    <button type="button" class="btn-minus w-7 h-7 flex items-center justify-center text-slate-500 hover:bg-slate-50 text-lg transition" data-action="minus">−</button>
                                    <input type="number" value="{{ $item->jumlah }}" class="item-qty w-8 h-7 text-center border-none text-sm font-bold text-slate-700 p-0 focus:ring-0 bg-transparent" readonly>
                                    <button type="button" class="btn-plus w-7 h-7 flex items-center justify-center text-slate-500 hover:bg-slate-50 text-lg transition" data-action="plus">+</button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- FITUR BARU: KOTAK VOUCHER PEMOTONG HARGA (RENTIFY) -->
            <div class="bg-white p-4 border-t border-b border-slate-100 mb-24 shadow-sm">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-bold text-slate-700 flex items-center gap-2">
                        <i class="fa-solid fa-ticket text-sky-500 text-base"></i> Voucher Diskon Rentify
                    </span>
                    <span id="voucher-badge" class="hidden bg-emerald-100 text-emerald-700 text-[11px] font-extrabold px-2 py-0.5 rounded-md">
                        ✓ Diskon 10% Aktif
                    </span>
                </div>
                <div class="flex gap-2">
                    <input type="text" id="input-voucher" placeholder="Ketik kode: RENTIFY" class="flex-1 bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-sm font-bold uppercase text-slate-700 outline-none focus:border-sky-500 transition">
                    <button type="button" id="btn-apply-voucher" class="bg-sky-500 hover:bg-sky-600 text-white font-bold text-xs px-5 py-2 rounded-xl transition shadow-sm">
                        Pakai
                    </button>
                </div>
                <!-- Input tersembunyi untuk membawa diskon ke Checkout -->
                <input type="hidden" name="kode_voucher" id="hidden-voucher-code" value="">
                <input type="hidden" name="potongan_diskon" id="hidden-voucher-discount" value="0">
            </div>

            <!-- BOTTOM BAR CHECKOUT -->
            <div class="fixed bottom-0 left-0 w-full bg-white border-t border-slate-200 px-4 py-3 shadow-[0_-4px_15px_rgba(0,0,0,0.03)] z-40">
                <div class="max-w-[600px] mx-auto flex items-center justify-between gap-3">
                    
                    <label class="flex items-center gap-2 cursor-pointer select-none">
                        <input type="checkbox" id="select-all" class="w-5 h-5 text-sky-500 rounded border-slate-300 focus:ring-sky-500">
                        <span class="text-sm font-bold text-slate-700">Semua</span>
                    </label>

                    <div class="flex items-center gap-4">
                        <div class="text-right">
                            <span class="block text-[10px] text-slate-400 font-bold uppercase tracking-wider mb-0.5">Total Pembayaran</span>
                            <!-- Menampilkan harga coret jika ada voucher -->
                            <span id="original-price" class="hidden text-xs text-slate-400 line-through font-bold block">Rp 0</span>
                            <span id="total-price" class="text-lg font-black text-sky-500">Rp 0</span>
                        </div>

                        <button type="submit" id="btn-checkout" class="bg-sky-500 hover:bg-sky-600 disabled:bg-slate-300 text-white font-bold text-sm px-6 py-3 rounded-xl shadow-md transition flex items-center gap-1 cursor-pointer" disabled>
                            <span>Checkout</span>
                            <span id="count-badge">(0)</span>
                        </button>
                    </div>
                </div>
            </div>

        </form>
    @endif

</div>

<!-- FORM TERSEMBUNYI UNTUK UPDATE DATABASE OTOMATIS -->
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
        const cartForm = document.getElementById('cart-form');
        
        // Elemen Voucher
        const inputVoucher = document.getElementById('input-voucher');
        const btnApplyVoucher = document.getElementById('btn-apply-voucher');
        const voucherBadge = document.getElementById('voucher-badge');
        const hiddenVoucherCode = document.getElementById('hidden-voucher-code');
        const hiddenVoucherDiscount = document.getElementById('hidden-voucher-discount');
        
        let diskonPersen = 0;

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

            // Hitung Potongan Voucher
            let nominalDiskon = (totalMurni * diskonPersen) / 100;
            let totalSetelahDiskon = totalMurni - nominalDiskon;

            // Update Nilai ke Form Tersembunyi
            hiddenVoucherDiscount.value = nominalDiskon;

            // Tampilkan ke Layar
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
                btnCheckout.classList.remove('disabled:bg-slate-300', 'cursor-not-allowed');
            } else {
                btnCheckout.disabled = true;
                btnCheckout.classList.add('disabled:bg-slate-300', 'cursor-not-allowed');
            }

            if (selectAllCheckbox) {
                selectAllCheckbox.checked = (checkedCount === itemCheckboxes.length && itemCheckboxes.length > 0);
            }
        }

        // FUNGSI SAKTI: SIMPAN JUMLAH & DURASI KE DATABASE TANPA REFRESH
        function updateDatabase(itemId, newQty, newDurasi) {
            const form = document.getElementById('update-form');
            form.action = `/customer/keranjang/${itemId}`;
            document.getElementById('update-jumlah').value = newQty;
            document.getElementById('update-durasi').value = newDurasi;
            
            fetch(form.action, {
                method: 'POST',
                body: new FormData(form),
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            }).catch(err => console.log('Update gagal:', err));
        }

        // Event Klik Voucher RENTIFY
        btnApplyVoucher.addEventListener('click', function() {
            const kode = inputVoucher.value.trim().toUpperCase();
            if (kode === 'RENTIFY') {
                diskonPersen = 10; // Diskon 10%
                hiddenVoucherCode.value = 'RENTIFY';
                voucherBadge.classList.remove('hidden');
                inputVoucher.classList.add('border-emerald-500', 'bg-emerald-50');
                btnApplyVoucher.textContent = 'Terpasang';
                btnApplyVoucher.classList.replace('bg-sky-500', 'bg-emerald-500');
                alert('🎉 Selamat! Voucher RENTIFY berhasil dipasang (Diskon 10%).');
            } else if (kode === '') {
                alert('⚠️ Silakan ketik kode voucher terlebih dahulu!');
            } else {
                alert('❌ Kode voucher tidak valid! Coba ketik: RENTIFY');
            }
            calculateTotal();
        });

        itemCheckboxes.forEach(cb => cb.addEventListener('change', calculateTotal));

        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', function() {
                itemCheckboxes.forEach(cb => cb.checked = selectAllCheckbox.checked);
                calculateTotal();
            });
        }

        // EVENT DROPDOWN DURASI HARI (LANGSUNG KALIKAN HARGA & SIMPAN KE DB!)
        document.querySelectorAll('.item-durasi').forEach(select => {
            select.addEventListener('change', function() {
                const card = this.closest('.card-item');
                card.querySelector('.item-checkbox').checked = true;
                
                const qty = parseInt(card.querySelector('.item-qty').value) || 1;
                const durasi = parseInt(this.value) || 1;
                
                calculateTotal();
                updateDatabase(card.dataset.id, qty, durasi);
            });
        });

        // EVENT TOMBOL [+]
        document.querySelectorAll('.btn-plus').forEach(btn => {
            btn.addEventListener('click', function() {
                const input = this.previousElementSibling;
                let newQty = parseInt(input.value) + 1;
                input.value = newQty;
                
                const card = this.closest('.card-item');
                card.querySelector('.item-checkbox').checked = true;
                
                const durasi = parseInt(card.querySelector('.item-durasi').value) || 1;
                calculateTotal();
                updateDatabase(card.dataset.id, newQty, durasi);
            });
        });

        // EVENT TOMBOL [-]
        document.querySelectorAll('.btn-minus').forEach(btn => {
            btn.addEventListener('click', function() {
                const input = this.nextElementSibling;
                if (parseInt(input.value) > 1) {
                    let newQty = parseInt(input.value) - 1;
                    input.value = newQty;
                    
                    const card = this.closest('.card-item');
                    const durasi = parseInt(card.querySelector('.item-durasi').value) || 1;
                    calculateTotal();
                    updateDatabase(card.dataset.id, newQty, durasi);
                }
            });
        });

        if (cartForm) {
            cartForm.addEventListener('submit', function(e) {
                const anyChecked = Array.from(itemCheckboxes).some(cb => cb.checked);
                if (!anyChecked) {
                    e.preventDefault();
                    alert('Silakan centang minimal satu barang untuk melanjutkan checkout!');
                }
            });
        }

        calculateTotal();
    });

    function confirmDelete(url) {
        if (confirm('Yakin ingin menghapus barang ini dari keranjang?')) {
            const form = document.getElementById('delete-form');
            form.action = url;
            form.submit();
        }
    }
</script>
@endsection