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
            <div class="bg-white border-t border-b border-slate-100 mt-2 mb-24">
                @foreach($keranjangs as $item)
                    @php
                        $harga = $item->barang->harga_sewa_customer ?? $item->barang->harga_sewa_harian ?? 0;
                    @endphp
                    <div class="p-4 border-b border-slate-50 flex items-center gap-4 card-item relative" data-id="{{ $item->id }}">
                        
                        <input type="checkbox" 
                               name="selected_items[]" 
                               value="{{ $item->id }}" 
                               class="item-checkbox w-5 h-5 text-sky-500 rounded border-slate-300 focus:ring-sky-500 cursor-pointer flex-shrink-0"
                               data-harga="{{ $harga }}"
                               data-durasi="1">

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
                            
                            <div class="inline-flex items-center gap-1 bg-sky-50 text-sky-500 text-[11px] font-bold px-2 py-1 rounded mb-2 cursor-pointer">
                                <span>Durasi: 1 Hari</span>
                                <i class="fa-solid fa-caret-down text-[10px]"></i>
                            </div>
                            
                            <div class="flex items-center justify-between">
                                <div class="text-sky-500 font-bold text-base">
                                    Rp {{ number_format($harga, 0, ',', '.') }}
                                </div>
                                
                                <!-- TOMBOL + DAN - YANG SUDAH TERHUBUNG KE DATABASE -->
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
        const countBadgeEl = document.getElementById('count-badge');
        const btnCheckout = document.getElementById('btn-checkout');
        const cartForm = document.getElementById('cart-form');

        function calculateTotal() {
            let total = 0;
            let checkedCount = 0;

            itemCheckboxes.forEach(cb => {
                if (cb.checked) {
                    checkedCount++;
                    const harga = parseFloat(cb.dataset.harga) || 0;
                    const durasi = parseInt(cb.dataset.durasi) || 1;
                    const qtyInput = cb.closest('.card-item').querySelector('.item-qty');
                    const jumlah = parseInt(qtyInput.value) || 1;
                    total += (harga * jumlah * durasi);
                }
            });

            totalPriceEl.textContent = 'Rp ' + total.toLocaleString('id-ID');
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

        // FUNGSI SAKTI: SIMPAN JUMLAH KE DATABASE TANPA REFRESH
        function updateDatabase(itemId, newQty) {
            const form = document.getElementById('update-form');
            form.action = `/customer/keranjang/${itemId}`;
            document.getElementById('update-jumlah').value = newQty;
            
            // Kirim ke server secara diam-diam
            fetch(form.action, {
                method: 'POST',
                body: new FormData(form),
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            }).catch(err => console.log('Update gagal:', err));
        }

        itemCheckboxes.forEach(cb => cb.addEventListener('change', calculateTotal));

        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', function() {
                itemCheckboxes.forEach(cb => cb.checked = selectAllCheckbox.checked);
                calculateTotal();
            });
        }

        // EVENT TOMBOL [+]
        document.querySelectorAll('.btn-plus').forEach(btn => {
            btn.addEventListener('click', function() {
                const input = this.previousElementSibling;
                let newQty = parseInt(input.value) + 1;
                input.value = newQty;
                
                const card = this.closest('.card-item');
                card.querySelector('.item-checkbox').checked = true;
                
                calculateTotal();
                updateDatabase(card.dataset.id, newQty); // Langsung simpan ke DB!
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
                    calculateTotal();
                    updateDatabase(card.dataset.id, newQty); // Langsung simpan ke DB!
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