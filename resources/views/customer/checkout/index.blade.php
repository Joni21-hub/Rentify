@extends('layouts.app')

@section('content')
<style>
    nav, header, footer { display: none !important; }
    body { background-color: #f8fafc; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; padding-top: 15px; padding-bottom: 90px;}
    .checkout-container { max-width: 600px; margin: 0 auto; padding: 0 15px;}
    
    .header-title { font-size: 18px; font-weight: 800; color: #0284c7; display: flex; align-items: center; gap: 12px; margin-bottom: 20px; }
    .clean-card { background: white; border-radius: 12px; padding: 16px; margin-bottom: 16px; box-shadow: 0 2px 8px rgba(0,0,0,0.04); border: 1px solid #f1f5f9; }
    .section-title { font-size: 14px; font-weight: 800; color: #0284c7; margin-bottom: 12px; display: flex; align-items: center; justify-content: space-between; }
    
    .radio-list-group { border: 1px solid #e2e8f0; border-radius: 10px; overflow: hidden; }
    .radio-list-item { display: flex; justify-content: space-between; align-items: center; padding: 14px 16px; background: white; cursor: pointer; transition: 0.2s; }
    .radio-list-item:not(:last-child) { border-bottom: 1px solid #e2e8f0; }
    .radio-list-item:hover { background: #f8fafc; }
    .radio-label { font-size: 14px; font-weight: 600; color: #0284c7; }
    .radio-price { font-size: 14px; font-weight: 600; color: #475569; display: flex; align-items: center; gap: 10px; }
    
    .panel-lokasi { display: none; background: #f0f9ff; padding: 14px 16px; border-top: 1px dashed #bae6fd; font-size: 13px; animation: slideDown 0.3s ease-out; }
    .panel-lokasi.active { display: block; }
    @keyframes slideDown { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }

    /* Style Khusus Jaminan KTP/SIM */
    .jaminan-box { display: flex; gap: 10px; margin-top: 5px; }
    .jaminan-item { flex: 1; border: 1.5px solid #cbd5e1; border-radius: 10px; padding: 10px 12px; display: flex; align-items: center; justify-content: space-between; cursor: pointer; transition: all 0.2s; background: white; }
    .jaminan-item:hover { border-color: #0284c7; background: #f0f9ff; }
    .radio-jaminan:checked + span { font-weight: 800; color: #0284c7; }
    .jaminan-item:has(.radio-jaminan:checked) { border-color: #0284c7; background: #e0f2fe; box-shadow: 0 2px 6px rgba(2, 132, 199, 0.15); }
    /* Style saat Jaminan Didisable (Karena sudah dipilih toko lain) */
    .jaminan-item.disabled-jaminan { opacity: 0.4; cursor: not-allowed; background: #f1f5f9; border-color: #e2e8f0; }

    .bottom-bar { position: fixed; bottom: 0; left: 0; width: 100%; background: white; box-shadow: 0 -4px 15px rgba(0,0,0,0.05); display: flex; justify-content: space-between; align-items: center; z-index: 1000; padding-left: 20px;}
    .btn-buat-pesanan { background: #0284c7; color: white; border: none; padding: 16px 30px; font-size: 15px; font-weight: 800; cursor: pointer; }
</style>

<div class="checkout-container">
    <div class="header-title">
        <span onclick="history.back()" style="cursor: pointer; font-size: 20px;">←</span> Checkout Rentify
    </div>

    <form action="{{ route('customer.checkout.store') }}" method="POST" id="form-checkout">
        @csrf
        <input type="hidden" name="cust_lat" id="global_lat">
        <input type="hidden" name="cust_lon" id="global_lon">
        <input type="hidden" name="alamat_customer" id="global_alamat">
        <input type="hidden" name="no_hp_hidden" id="global_hp">

        @foreach($keranjangPerVendor as $vendorId => $items)
        @php 
            $vendor = $items->first()->barang->vendor;
            $barangPertama = $items->first()->barang;
            $bisaDiantar = $items->every(fn($i) => $i->barang->is_delivery_supported == 1);
            $latProduk = $barangPertama->latitude ?? '0';
            $lonProduk = $barangPertama->longitude ?? '0';
            $alamatProduk = $barangPertama->alamat ?? 'Alamat produk belum diatur oleh vendor.';
        @endphp
        
        <!-- BLOK TOKO -->
        <div class="vendor-block" data-vendor="{{ $vendorId }}" data-lat="{{ $latProduk }}" data-lon="{{ $lonProduk }}">
            
            <!-- FITUR 3: AVATAR PROFIL VENDOR DI HEADER TOKO -->
            <div class="section-title" style="color: #0f172a; margin-top: 20px; justify-content: flex-start; gap: 10px;">
                <div style="width: 28px; height: 28px; border-radius: 50%; background: #0284c7; color: white; display: flex; align-items: center; justify-content: center; font-size: 13px; font-weight: 800; overflow: hidden; border: 2px solid #e0f2fe; flex-shrink: 0;">
                    @if(isset($vendor->avatar) && $vendor->avatar)
                        <img src="{{ asset('storage/' . $vendor->avatar) }}" class="w-full h-full object-cover">
                    @else
                        {{ strtoupper(substr($vendor->vendor_name ?? 'V', 0, 1)) }}
                    @endif
                </div>
                <span>Toko: {{ $vendor->vendor_name ?? 'Vendor' }}</span>
            </div>
            
            <!-- 1. Pesanan Anda -->
            <div class="section-title">Pesanan Anda</div>
            @foreach($items as $item)
                @php $hargaTampil = $item->barang->harga_sewa_harian * 1.05; @endphp
                <div style="display: flex; gap: 15px; margin-bottom: 15px; padding-bottom: 15px; border-bottom: 1px dashed #e2e8f0;">
                    <div style="width: 70px; height: 70px; border-radius: 8px; border: 1px solid #e2e8f0; overflow:hidden;">
                        @if($item->barang->cover_photo)
                            <img src="{{ asset(str_replace('public/', '', $item->barang->cover_photo)) }}" class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full bg-slate-100 flex items-center justify-center text-slate-400 text-xs">No Img</div>
                        @endif
                    </div>
                    <div style="flex: 1;">
                        <div style="font-weight: 700; font-size: 14px; color: #1e293b;">{{ $item->barang->nama }}</div>
                        <div style="font-weight: 800; font-size: 14px; color: #0284c7; margin-top: 4px;">
                            Rp {{ number_format($hargaTampil, 0, ',', '.') }} <span style="font-size: 11px; font-weight: normal; color:#64748b;">/hari</span>
                        </div>
                        @if($item->barang->deposit > 0)
                            <div style="font-size: 11px; font-weight: 700; color: #b45309; background: #fef3c7; display: inline-block; padding: 2px 8px; border-radius: 4px; margin-top: 6px;">
                                Deposit (Bayar di Tempat): Rp {{ number_format($item->barang->deposit * $item->jumlah, 0, ',', '.') }}
                            </div>
                        @endif
                        <input type="hidden" class="harga-sewa-item" value="{{ $hargaTampil * $item->jumlah }}">
                    </div>
                    <div style="background: #f1f5f9; color: #64748b; font-weight: 800; font-size: 12px; padding: 4px 10px; border-radius: 20px; height: fit-content;">
                        x{{ $item->jumlah }}
                    </div>
                </div>
            @endforeach

            <!-- 2. Lama Sewa -->
            <div class="section-title mt-4">Lama Sewa</div>
            <div class="clean-card" style="display: flex; justify-content: space-between; align-items: center; padding: 12px 16px;">
                <span style="font-size: 14px; font-weight: 600; color: #475569;">Berapa hari Anda menyewa?</span>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <input type="number" name="durasi_sewa[{{ $vendorId }}]" class="input-durasi" value="1" min="1" onchange="hitungSemuaTotal()" style="width: 50px; text-align: center; border: 1px solid #cbd5e1; border-radius: 8px; font-weight: bold; padding: 6px; outline: none; color:#0284c7;"> 
                    <span style="font-size: 13px; font-weight: 700; color: #64748b;">Hari</span>
                </div>
            </div>

            <!-- FITUR 4 & 5: OPSI JAMINAN KTP/SIM (EXCLUSIVE RULES) -->
            <div class="section-title mt-4">Jaminan Dokumen</div>
            <p style="font-size: 11px; color: #64748b; margin-top: -6px; margin-bottom: 8px;">*Pilih 1 dokumen fisik untuk Jaminan.</p>
            <div class="jaminan-box mb-4">
                <label class="jaminan-item label-jaminan-{{ $vendorId }}">
                    <input type="radio" name="jaminan[{{ $vendorId }}]" value="KTP" class="radio-jaminan hidden" data-vendor="{{ $vendorId }}" onchange="updateJaminanExclusive()">
                    <span style="font-size: 13px;">Kartu Tanda Penduduk (KTP)</span>
                    <i class="fa-solid fa-check text-sky-600 opacity-0 check-icon"></i>
                </label>
                <label class="jaminan-item label-jaminan-{{ $vendorId }}">
                    <input type="radio" name="jaminan[{{ $vendorId }}]" value="SIM" class="radio-jaminan hidden" data-vendor="{{ $vendorId }}" onchange="updateJaminanExclusive()">
                    <span style="font-size: 13px;">Surat Izin Mengemudi (SIM)</span>
                    <i class="fa-solid fa-check text-sky-600 opacity-0 check-icon"></i>
                </label>
            </div>

            <!-- 3. Opsi Pengiriman -->
            <div class="section-title mt-4">Opsi Pengiriman</div>
            <div class="radio-list-group">
                <label class="radio-list-item" onclick="bukaPanel('ambil', '{{ $vendorId }}')">
                    <span class="radio-label">Ambil di Tempat</span>
                    <div class="radio-price"><span>Rp 0</span> <input type="radio" name="opsi_pengiriman[{{ $vendorId }}]" value="ambil" class="radio-opsi" onchange="hitungSemuaTotal()" checked style="width:16px; height:16px;"></div>
                </label>
                <div id="panel_ambil_{{ $vendorId }}" class="panel-lokasi active">
                    <div style="font-weight: 800; color: #0369a1; margin-bottom: 5px;">📍 Lokasi Toko Pengambilan:</div>
                    <div style="color: #334155;">{{ $alamatProduk }}</div>
                    @if($latProduk != '0')
                    <a href="https://maps.google.com/?q={{ $latProduk }},{{ $lonProduk }}" target="_blank" style="display: inline-block; margin-top: 8px; color: #0284c7; font-weight: bold; text-decoration: underline;">🗺️ Lihat di Google Maps</a>
                    @endif
                </div>

                @if($bisaDiantar)
                <label class="radio-list-item" style="border-top: 1px solid #e2e8f0;" onclick="bukaPanel('antar', '{{ $vendorId }}')">
                    <span class="radio-label">Reguler (Diantar)</span>
                    <div class="radio-price"><span class="teks-ongkir-vendor text-slate-400" style="font-size: 12px;">Pilih Lokasi</span> <input type="radio" name="opsi_pengiriman[{{ $vendorId }}]" value="diantar" class="radio-opsi" onchange="hitungSemuaTotal()" style="width:16px; height:16px;"></div>
                </label>
                <div id="panel_antar_{{ $vendorId }}" class="panel-lokasi bg-white border-t border-slate-200">
                    <div style="font-weight: 800; color: #0f172a; margin-bottom: 8px;">📍 Masukkan Alamat Pengiriman Anda:</div>
                    <textarea class="sync-alamat w-full rounded-lg border border-slate-300 p-3 text-sm mb-3 focus:border-sky-500 outline-none" rows="2" placeholder="Cth: Jl. Merdeka No. 10 (Gunakan tombol GPS di bawah agar otomatis)" onchange="syncData()"></textarea>
                    <button type="button" onclick="dapatkanLokasi()" style="width: 100%; background: #0284c7; color: white; padding: 10px; border-radius: 8px; font-weight: bold; font-size: 13px;">📍 Sinkronisasi Titik GPS Saya</button>
                    <div class="status-gps mt-2 text-xs font-bold text-sky-600 text-center"></div>
                </div>
                @endif
                <input type="hidden" name="ongkir_vendor[{{ $vendorId }}]" class="input-ongkir-vendor" value="0">
            </div>
            
            <!-- FITUR 2: INFORMASI ABU-ABU JIKA TIDAK BISA DIANTAR -->
            @if(!$bisaDiantar)
            <div style="margin-top: 8px; font-size: 11.5px; color: #64748b; font-style: italic; line-height: 1.4; background: #f8fafc; padding: 10px 14px; border-radius: 8px; border-left: 3px solid #94a3b8;">
                ℹ Mohon maaf, vendor ini belum menyediakan layanan pengantaran.
            </div>
            @endif
            
            <div style="height: 1px; background: #e2e8f0; margin: 30px 0;"></div> 
        </div>
        @endforeach

        <!-- FITUR 1: FIELD NOMOR WHATSAPP CUSTOMER (WAJIB DIISI SEBELUM BUAT PESANAN) -->
        <div class="section-title">Informasi Kontak Anda</div>
        <div class="clean-card p-4" style="border-left: 4px solid #0284c7;">
            <label style="display: block; font-size: 13px; font-weight: 800; color: #0f172a; margin-bottom: 4px;">No WhatsApp <span style="color: #ef4444;">*</span></label>
            <input type="text" name="no_hp" id="input_wa_wajib" value="{{ auth()->user()->no_hp ?? '' }}" placeholder="" required style="width: 100%; padding: 12px 14px; border: 1.5px solid #cbd5e1; border-radius: 10px; font-size: 14px; font-weight: 700; color: #0f172a; outline: none; transition: 0.2s;" onfocus="this.style.borderColor='#0284c7'" onblur="this.style.borderColor='#cbd5e1'">
        </div>

        <!-- 4. Metode Pembayaran -->
        <div class="section-title mt-4">Metode Pembayaran</div>
        <div class="radio-list-group mb-6">
            <label class="radio-list-item">
                <span class="radio-label" style="display:flex; align-items:center; gap:8px;"><span style="background:#0284c7; color:white; padding:2px 6px; border-radius:4px; font-size:10px;">COD</span> Cash on Delivery</span>
                <input type="radio" name="metode_pembayaran" value="COD" checked style="width:16px; height:16px;">
            </label>
            <label class="radio-list-item">
                <span class="radio-label" style="display:flex; align-items:center; gap:8px;"><span style="background:#0284c7; color:white; padding:2px 6px; border-radius:4px; font-size:10px;">QRIS</span> DANA, GoPay, BCA, dll</span>
                <input type="radio" name="metode_pembayaran" value="QRIS" style="width:16px; height:16px;">
            </label>
        </div>

        <!-- 5. Rincian Pembayaran -->
        <div class="section-title">Rincian Pembayaran</div>
        <div class="clean-card">
            <div style="display: flex; justify-content: space-between; font-size: 14px; margin-bottom: 8px; color: #475569;">
                <span>Subtotal Produk</span><span id="grand-sewa" style="font-weight: 700; color: #1e293b;">Rp 0</span>
            </div>
            <div style="display: flex; justify-content: space-between; font-size: 14px; border-bottom: 1px solid #e2e8f0; padding-bottom: 12px; margin-bottom: 12px; color: #475569;">
                <span>Subtotal Pengiriman</span><span id="grand-ongkir" style="font-weight: 700; color: #1e293b;">Rp 0</span>
            </div>
            <div style="display: flex; justify-content: space-between; font-size: 16px; font-weight: 800; color: #0f172a;">
                <span>Total Pembayaran</span><span id="grand-total" style="color:#0284c7; font-size: 18px;">Rp 0</span>
            </div>
            <div style="font-size: 11px; color: #94a3b8; text-align: right; margin-top: 6px;">*Tidak termasuk uang jaminan deposit vendor</div>
        </div>

        <div class="bottom-bar">
            <div>
                <div style="font-size: 12px; font-weight: 600; color: #64748b;">Total Pembayaran</div>
                <div style="font-size: 20px; font-weight: 900; color: #0284c7;" id="bar-total">Rp 0</div>
            </div>
            <button type="button" class="btn-buat-pesanan" onclick="validasiSubmit()">Buat Pesanan</button>
        </div>
    </form>
</div>

<script>
    const formatRp = (angka) => 'Rp ' + Math.round(angka).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    let lokasiCustomer = null;

    // FITUR 5: LOGIKA JAMINAN EKSKLUSIF (TIDAK BOLEH SAMA ANTAR TOKO)
    function updateJaminanExclusive() {
        const selectedMap = {}; 
        const allRadios = document.querySelectorAll('.radio-jaminan');
        
        // 1. Catat dokumen apa yang sudah dipilih oleh vendor mana
        allRadios.forEach(radio => {
            const label = radio.closest('.jaminan-item');
            const icon = label.querySelector('.check-icon');
            if (radio.checked) {
                selectedMap[radio.getAttribute('data-vendor')] = radio.value;
                if(icon) icon.style.opacity = '1';
            } else {
                if(icon) icon.style.opacity = '0';
            }
        });

        // 2. Kunci (Disable) dokumen yang sama pada toko lain
        allRadios.forEach(radio => {
            const vendorId = radio.getAttribute('data-vendor');
            const value = radio.value;
            const label = radio.closest('.jaminan-item');

            let DipakaiTokoLain = false;
            for (const [vId, val] of Object.entries(selectedMap)) {
                if (vId !== vendorId && val === value) {
                    DipakaiTokoLain = true;
                    break;
                }
            }

            if (DipakaiTokoLain) {
                radio.disabled = true;
                label.classList.add('disabled-jaminan');
                label.title = 'Dokumen ini sudah dipilih sebagai jaminan untuk toko lain';
            } else {
                radio.disabled = false;
                label.classList.remove('disabled-jaminan');
                label.title = '';
            }
        });
    }

    function bukaPanel(jenis, vendorId) {
        document.querySelectorAll(`#panel_ambil_${vendorId}, #panel_antar_${vendorId}`).forEach(el => {
            if(el) el.classList.remove('active');
        });
        const target = document.getElementById(`panel_${jenis}_${vendorId}`);
        if(target) target.classList.add('active');
    }

    function syncData() {
        const alamats = document.querySelectorAll('.sync-alamat');
        let valAlamat = '';
        alamats.forEach(el => { if(el.value) valAlamat = el.value; });
        alamats.forEach(el => el.value = valAlamat);
        document.getElementById('global_alamat').value = valAlamat;
    }

    function hitungJarakKm(lat1, lon1, lat2, lon2) {
        if(!lat1 || !lon1 || !lat2 || !lon2) return 0;
        const R = 6371; 
        const dLat = (lat2 - lat1) * Math.PI / 180;
        const dLon = (lon2 - lon1) * Math.PI / 180;
        const a = Math.sin(dLat/2) * Math.sin(dLat/2) + Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) * Math.sin(dLon/2) * Math.sin(dLon/2);
        return R * (2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a)));
    }

    async function dapatkanLokasi() {
        document.querySelectorAll('.status-gps').forEach(el => el.innerHTML = "Mencari lokasi...");
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(async (pos) => {
                lokasiCustomer = { lat: pos.coords.latitude, lon: pos.coords.longitude };
                document.getElementById('global_lat').value = lokasiCustomer.lat;
                document.getElementById('global_lon').value = lokasiCustomer.lon;
                try { 
                    const response = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lokasiCustomer.lat}&lon=${lokasiCustomer.lon}`);
                    const data = await response.json();
                    if(data.display_name) {
                        document.querySelectorAll('.sync-alamat').forEach(el => el.value = data.display_name);
                        syncData();
                    }
                } catch(e) {}
                document.querySelectorAll('.status-gps').forEach(el => el.innerHTML = "✅ Lokasi terkunci! Tarif diupdate.");
                hitungSemuaTotal();
            }, () => { alert("Gagal mendapat lokasi. Pastikan GPS menyala."); });
        }
    }

    function hitungSemuaTotal() {
        let totalSewaSemua = 0, totalOngkirSemua = 0;
        document.querySelectorAll('.vendor-block').forEach(block => {
            const durasi = parseInt(block.querySelector('.input-durasi').value) || 1;
            let subSewa = 0, ongkirToko = 0;
            block.querySelectorAll('.harga-sewa-item').forEach(el => { subSewa += parseInt(el.value) * durasi; });

            const opsiTerpilih = block.querySelector('.radio-opsi:checked').value;
            if (opsiTerpilih === 'diantar' && lokasiCustomer) {
                const latToko = parseFloat(block.getAttribute('data-lat'));
                const lonToko = parseFloat(block.getAttribute('data-lon'));
                const jarak = hitungJarakKm(lokasiCustomer.lat, lokasiCustomer.lon, latToko, lonToko);
                ongkirToko = Math.ceil(jarak) * 4000; 
                if(ongkirToko === 0) ongkirToko = 4000; 
            }
            block.querySelector('.input-ongkir-vendor').value = ongkirToko;
            if(opsiTerpilih === 'diantar') {
                block.querySelector('.teks-ongkir-vendor').innerText = (ongkirToko > 0) ? formatRp(ongkirToko) : 'Minta GPS';
                block.querySelector('.teks-ongkir-vendor').style.color = '#0284c7';
            } else {
                const lableAntar = block.querySelector('.teks-ongkir-vendor');
                if(lableAntar) { lableAntar.innerText = 'Pilih Lokasi'; lableAntar.style.color = '#94a3b8'; }
            }
            totalSewaSemua += subSewa; totalOngkirSemua += ongkirToko;
        });

        document.getElementById('grand-sewa').innerText = formatRp(totalSewaSemua);
        document.getElementById('grand-ongkir').innerText = formatRp(totalOngkirSemua);
        const grandTotal = totalSewaSemua + totalOngkirSemua;
        document.getElementById('grand-total').innerText = formatRp(grandTotal);
        document.getElementById('bar-total').innerText = formatRp(grandTotal);
    }

    function validasiSubmit() {
        // 1. Validasi Nomor WA Wajib
        const inputWa = document.getElementById('input_wa_wajib');
        if (!inputWa.value || inputWa.value.trim() === '') {
            alert("⚠️ Mohon isi Nomor WhatsApp Customer terlebih dahulu agar vendor dapat menghubungi Anda.");
            inputWa.focus();
            inputWa.style.borderColor = '#ef4444';
            return;
        }

        // 2. Validasi Jaminan per toko sudah dipilih
        let jaminanLengkap = true;
        document.querySelectorAll('.vendor-block').forEach(block => {
            const vId = block.getAttribute('data-vendor');
            const jaminanDipilih = block.querySelector(`input[name="jaminan[${vId}]"]:checked`);
            if(!jaminanDipilih) jaminanLengkap = false;
        });
        if(!jaminanLengkap) {
            alert("⚠️ Mohon pilih Jaminan Dokumen (KTP / SIM) untuk setiap toko sebelum membuat pesanan.");
            return;
        }

        // 3. Validasi GPS jika ada yang minta Diantar
        let adaDiantarTanpaGPS = false;
        document.querySelectorAll('.vendor-block').forEach(block => {
            if (block.querySelector('.radio-opsi:checked').value === 'diantar' && !lokasiCustomer) adaDiantarTanpaGPS = true;
        });
        if (adaDiantarTanpaGPS) {
            alert("⚠️ Ada produk yang Anda pilih 'Diantar'. Mohon tekan tombol 'Sinkronisasi Titik GPS Saya' agar kurir tahu titik pengiriman.");
            return;
        }

        syncData(); 
        document.getElementById('form-checkout').submit();
    }

    window.onload = function() {
        hitungSemuaTotal();
        updateJaminanExclusive();
    };
</script>
@endsection