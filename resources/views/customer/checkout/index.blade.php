@extends('layouts.app')

@section('content')
<style>
    nav, header, footer { display: none !important; }
    body { background-color: #f8fafc; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; padding-top: 10px; padding-bottom: 85px;}
    .checkout-container { max-width: 600px; margin: 0 auto; padding: 0 12px;}
    
    .header-title { font-size: 17px; font-weight: 800; color: #0284c7; display: flex; align-items: center; gap: 10px; margin-bottom: 12px; }
    .clean-card { background: white; border-radius: 12px; padding: 12px 14px; margin-bottom: 10px; box-shadow: 0 2px 6px rgba(0,0,0,0.03); border: 1px solid #f1f5f9; }
    .section-title { font-size: 13.5px; font-weight: 800; color: #0284c7; margin-bottom: 8px; margin-top: 10px; display: flex; align-items: center; justify-content: space-between; }
    
    .radio-list-group { border: 1px solid #e2e8f0; border-radius: 10px; overflow: hidden; margin-bottom: 8px; }
    .radio-list-item { display: flex; justify-content: space-between; align-items: center; padding: 10px 14px; background: white; cursor: pointer; transition: 0.2s; }
    .radio-list-item:not(:last-child) { border-bottom: 1px solid #e2e8f0; }
    .radio-list-item:hover { background: #f8fafc; }
    .radio-label { font-size: 13.5px; font-weight: 700; color: #0284c7; }
    .radio-price { font-size: 13px; font-weight: 600; color: #475569; display: flex; align-items: center; gap: 8px; }
    
    .panel-lokasi { display: none; background: #f0f9ff; padding: 10px 14px; border-top: 1px dashed #bae6fd; font-size: 12px; animation: slideDown 0.3s ease-out; }
    .panel-lokasi.active { display: block; }
    @keyframes slideDown { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }

    .jaminan-box { display: flex; gap: 8px; margin-top: 4px; margin-bottom: 10px; }
    .jaminan-item { flex: 1; border: 1.5px solid #cbd5e1; border-radius: 10px; padding: 8px 12px; display: flex; align-items: center; justify-content: space-between; cursor: pointer; transition: all 0.2s; background: white; }
    .jaminan-item:hover { border-color: #0284c7; background: #f0f9ff; }
    .radio-jaminan:checked + span { font-weight: 800; color: #0284c7; }
    .jaminan-item:has(.radio-jaminan:checked) { border-color: #0ea5e9; background: #f0f9ff; box-shadow: 0 0 12px rgba(14, 165, 233, 0.25); }
    .jaminan-item.disabled-jaminan { opacity: 0.4; cursor: not-allowed; background: #f1f5f9; border-color: #e2e8f0; }

    .bottom-bar { position: fixed; bottom: 0; left: 0; width: 100%; background: white; box-shadow: 0 -4px 15px rgba(0,0,0,0.05); display: flex; justify-content: space-between; align-items: center; z-index: 1000; padding-left: 16px;}
    .btn-buat-pesanan { background: #0284c7; color: white; border: none; padding: 14px 26px; font-size: 14.5px; font-weight: 800; cursor: pointer; transition: 0.2s; }
    .btn-buat-pesanan:hover { background: #0369a1; box-shadow: 0 0 15px rgba(2, 132, 199, 0.4); }

    @keyframes slideUpSheet {
        from { transform: translateY(100%); }
        to { transform: translateY(0); }
    }
</style>

<div class="checkout-container">
    <div class="header-title">
        <span onclick="history.back()" style="cursor: pointer; font-size: 20px;">←</span> Checkout Rentify
    </div>

    @if(session('error'))
        <div style="background: #fee2e2; border: 1px solid #fecaca; color: #991b1b; padding: 12px 16px; border-radius: 10px; margin-bottom: 15px; font-size: 13px; font-weight: 700; display: flex; align-items: center; gap: 8px;">
            <i class="fa-solid fa-circle-exclamation"></i> {{ session('error') }}
        </div>
    @endif

    <form action="{{ route('customer.checkout.store') }}" method="POST" id="form-checkout">
        @csrf
        <input type="hidden" name="cust_lat" id="global_lat">
        <input type="hidden" name="cust_lon" id="global_lon">
        <input type="hidden" name="alamat_customer" id="global_alamat">
        <input type="hidden" name="no_hp_hidden" id="global_hp">
        
        <!-- Menyimpan detail JSON voucher jika sukses di AJAX -->
        <input type="hidden" name="voucher_data_json" id="input_voucher_data_json" value="">
        
        <input type="hidden" name="start_date" value="{{ request('start_date', date('Y-m-d')) }}">
        <input type="hidden" name="start_time" value="{{ request('start_time', '09:00') }}">

        @foreach($keranjangPerVendor as $vendorId => $items)
        @php 
            $vendor = $items->first()->barang->vendor;
            $barangPertama = $items->first()->barang;
            $bisaDiantar = $items->every(fn($i) => $i->barang->is_delivery_supported == 1);
            $latProduk = $barangPertama->latitude ?? '0';
            $lonProduk = $barangPertama->longitude ?? '0';
            $namaTokoAsli = $vendor->vendor_name ?? $vendor->name ?? 'Vendor Rentify';
            $durasiDefault = $items->first()->durasi_sewa ?? 1;
        @endphp
        
        <div class="vendor-block" data-vendor="{{ $vendorId }}" data-lat="{{ $latProduk }}" data-lon="{{ $lonProduk }}">
            
            <div class="section-title" style="color: #0f172a; margin-top: 10px; margin-bottom: 8px; justify-content: flex-start; gap: 8px;">
                <div style="width: 30px; height: 30px; border-radius: 50%; background: #0284c7; color: white; display: flex; align-items: center; justify-content: center; font-size: 13px; font-weight: 800; overflow: hidden; border: 2px solid #e0f2fe; flex-shrink: 0;">
                    @if(!empty($vendor->foto_profil_url))
                        <img src="{{ $vendor->foto_profil_url }}" class="w-full h-full object-cover">
                    @else
                        {{ strtoupper(substr($namaTokoAsli, 0, 1)) }}
                    @endif
                </div>
                <span style="font-size: 14.5px; font-weight: 800; color: #0f172a;">{{ $namaTokoAsli }}</span>
            </div>
            
            <div class="section-title" style="margin-top: 6px; margin-bottom: 6px;">Pesanan Anda</div>
            @foreach($items as $item)
                @php $hargaTampil = $item->barang->harga_sewa_harian * 1.05; @endphp
                <div style="display: flex; gap: 12px; margin-bottom: 10px; padding-bottom: 10px; border-bottom: 1px dashed #e2e8f0;">
                    <div style="width: 60px; height: 60px; border-radius: 8px; border: 1px solid #e2e8f0; overflow:hidden; flex-shrink: 0;">
                        @if($item->barang->cover_photo)
                            <img src="{{ asset(str_replace('public/', '', $item->barang->cover_photo)) }}" class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full bg-slate-100 flex items-center justify-center text-slate-400 text-xs">No Img</div>
                        @endif
                    </div>
                    <div style="flex: 1;">
                        <div style="font-weight: 700; font-size: 13.5px; color: #1e293b;">{{ $item->barang->nama }}</div>
                        <div style="font-weight: 800; font-size: 13.5px; color: #0284c7; margin-top: 2px;">
                            Rp {{ number_format($hargaTampil, 0, ',', '.') }} <span style="font-size: 11px; font-weight: normal; color:#64748b;">/hari</span>
                        </div>
                        @if($item->barang->deposit > 0)
                            <div style="font-size: 10.5px; font-weight: 700; color: #0369a1; background: #e0f2fe; display: inline-block; padding: 2px 7px; border-radius: 4px; margin-top: 4px; border: 1px solid #bae6fd;">
                                Deposit: Rp {{ number_format($item->barang->deposit * $item->jumlah, 0, ',', '.') }}
                            </div>
                        @endif
                        <input type="hidden" class="harga-sewa-item" value="{{ $hargaTampil * $item->jumlah }}">
                    </div>
                    <div style="background: #f1f5f9; color: #64748b; font-weight: 800; font-size: 11.5px; padding: 3px 9px; border-radius: 20px; height: fit-content;">
                        x{{ $item->jumlah }}
                    </div>
                </div>
            @endforeach

            <div class="section-title mt-2" style="display: flex; justify-content: space-between; align-items: center;">
                <span><i class="fa-regular fa-calendar-check mr-1"></i> Jadwal Sewa (WIB)</span>
                <button type="button" onclick="bukaModalJadwal('{{ $vendorId }}')" style="background: #e0f2fe; color: #0284c7; border: 1px solid #bae6fd; padding: 3px 10px; border-radius: 6px; font-size: 11px; font-weight: 800; cursor: pointer; display: flex; align-items: center; gap: 4px;">
                    <i class="fa-solid fa-pen-to-square"></i> Ubah Jadwal
                </button>
            </div>
            <div class="clean-card" onclick="bukaModalJadwal('{{ $vendorId }}')" style="padding: 10px 14px; background: #f0f9ff; border-color: #bae6fd; box-shadow: none; margin-bottom: 8px; cursor: pointer;" title="Klik untuk mengubah jadwal sewa">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 5px;">
                    <span style="font-size: 12.5px; font-weight: 600; color: #475569;">Tanggal Mulai</span>
                    <span id="display_tgl_mulai_{{ $vendorId }}" style="font-size: 13px; font-weight: 800; color: #0284c7;">
                        {{ request('start_date') ? date('d M Y', strtotime(request('start_date'))) : date('d M Y') }}
                    </span>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 5px;">
                    <span style="font-size: 12.5px; font-weight: 600; color: #475569;">Jam Ambil/Antar</span>
                    <span id="display_jam_mulai_{{ $vendorId }}" style="font-size: 13px; font-weight: 800; color: #0284c7;">
                        {{ request('start_time', '09:00') }} WIB
                    </span>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px dashed #cbd5e1; padding-top: 6px;">
                    <span style="font-size: 12.5px; font-weight: 600; color: #475569;">Durasi Pemakaian</span>
                    <span id="display_durasi_{{ $vendorId }}" style="font-size: 13.5px; font-weight: 900; color: #0ea5e9;">{{ $durasiDefault }} Hari</span>
                </div>
            </div>

            <input type="hidden" name="durasi_sewa[{{ $vendorId }}]" class="input-durasi" value="{{ $durasiDefault }}">

            <div class="section-title mt-2">Jaminan Dokumen</div>
            <p style="font-size: 11px; color: #64748b; margin-top: -4px; margin-bottom: 6px;">*Pilih 1 dokumen fisik untuk Jaminan.</p>
            <div class="jaminan-box mb-3">
                <label class="jaminan-item label-jaminan-{{ $vendorId }}">
                    <input type="radio" name="jaminan[{{ $vendorId }}]" value="KTP" class="radio-jaminan hidden" data-vendor="{{ $vendorId }}" onchange="updateJaminanExclusive()">
                    <span style="font-size: 12.5px;">Kartu Tanda Penduduk (KTP)</span>
                    <i class="fa-solid fa-check text-sky-600 opacity-0 check-icon"></i>
                </label>
                <label class="jaminan-item label-jaminan-{{ $vendorId }}">
                    <input type="radio" name="jaminan[{{ $vendorId }}]" value="SIM" class="radio-jaminan hidden" data-vendor="{{ $vendorId }}" onchange="updateJaminanExclusive()">
                    <span style="font-size: 12.5px;">Surat Izin Mengemudi (SIM)</span>
                    <i class="fa-solid fa-check text-sky-600 opacity-0 check-icon"></i>
                </label>
            </div>

            <div class="section-title mt-2">Opsi Pengiriman</div>
            <div class="radio-list-group">
                <label class="radio-list-item" onclick="bukaPanel('ambil', '{{ $vendorId }}')">
                    <span class="radio-label">Ambil di Tempat</span>
                    <div class="radio-price"><span>Rp 0</span> <input type="radio" name="opsi_pengiriman[{{ $vendorId }}]" value="ambil" class="radio-opsi" onchange="hitungSemuaTotal()" checked style="width:16px; height:16px;"></div>
                </label>
                
                <div id="panel_ambil_{{ $vendorId }}" class="panel-lokasi active">
                    <div style="font-weight: 800; color: #0369a1; margin-bottom: 3px;">📍 Lokasi Toko Pengambilan:</div>
                    <div style="color: #334155; font-weight: 700; font-size: 13px;">{{ $namaTokoAsli }}</div>
                    
                    @php
                        $areaSaja = \App\Helpers\RentifyHelper::formatAreaDesa($barangPertama->alamat ?? $vendor->alamat_lengkap);
                    @endphp
                    <div style="color: #64748b; font-size: 12px; font-weight: 600; margin-top: 2px;">
                        <i class="fa-solid fa-map-pin text-[10px] mr-1"></i> Area: {{ $areaSaja }}
                    </div>
                    
                    <div style="margin-top: 6px; font-size: 11px; color: #0284c7; background: #ffffff; padding: 6px 10px; border-radius: 6px; border: 1px solid #bae6fd; font-weight: 600; display: flex; align-items: center; gap: 6px;">
                        <i class="fa-solid fa-lock text-[10px] flex-shrink-0"></i>
                        <span>Alamat lengkap & Maps terbuka di Riwayat setelah sewa.</span>
                    </div>
                </div>

                @if($bisaDiantar)
                <label class="radio-list-item" style="border-top: 1px solid #e2e8f0;" onclick="bukaPanel('antar', '{{ $vendorId }}')">
                    <span class="radio-label">Reguler (Diantar)</span>
                    <div class="radio-price"><span class="teks-ongkir-vendor text-slate-400" style="font-size: 12px;">Pilih Lokasi</span> <input type="radio" name="opsi_pengiriman[{{ $vendorId }}]" value="diantar" class="radio-opsi" onchange="hitungSemuaTotal()" style="width:16px; height:16px;"></div>
                </label>
                <div id="panel_antar_{{ $vendorId }}" class="panel-lokasi bg-white border-t border-slate-200">
                    <div style="font-weight: 800; color: #0f172a; margin-bottom: 6px;">📍 Masukkan Alamat Pengiriman Anda:</div>
                    <textarea class="rentify-input sync-alamat w-full p-2.5 text-xs mb-2 focus:outline-none" rows="2" placeholder="Cth: Jl. Merdeka No. 10 (Gunakan tombol GPS di bawah agar otomatis)" onchange="syncData()"></textarea>
                    <button type="button" onclick="dapatkanLokasi()" style="width: 100%; background: #0284c7; color: white; padding: 9px; border-radius: 8px; font-weight: bold; font-size: 12px;">📍 Sinkronisasi Titik GPS Saya</button>
                    <div class="status-gps mt-2 text-xs font-bold text-sky-600 text-center"></div>
                </div>
                @endif
                <input type="hidden" name="ongkir_vendor[{{ $vendorId }}]" class="input-ongkir-vendor" value="0">
            </div>
            
            @if(!$bisaDiantar)
            <div style="margin-top: 6px; font-size: 11px; color: #64748b; font-style: italic; line-height: 1.4; background: #f8fafc; padding: 8px 12px; border-radius: 8px; border-left: 3px solid #94a3b8;">
                ℹ Mohon maaf, vendor ini belum menyediakan layanan pengantaran.
            </div>
            @endif
            
            <div style="height: 1px; background: #e2e8f0; margin: 14px 0;"></div> 
        </div>
        @endforeach

        <div class="section-title">Informasi Kontak Anda</div>
        <div class="clean-card p-3" style="border-left: 4px solid #0284c7;">
            <label style="display: block; font-size: 12.5px; font-weight: 800; color: #0f172a; margin-bottom: 4px;">No WhatsApp <span style="color: #0284c7;">*</span></label>
            <input type="text" name="no_hp" id="input_wa_wajib" value="{{ auth()->user()->no_hp ?? '' }}" placeholder="08xxxxxxxxxx" required style="width: 100%; padding: 10px 12px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 13.5px; font-weight: 700; color: #0f172a; outline: none; transition: 0.2s;" onfocus="this.style.borderColor='#0284c7'" onblur="this.style.borderColor='#cbd5e1'">
        </div>

        <div class="section-title mt-2">Metode Pembayaran</div>
        <div class="clean-card mb-3" onclick="bukaModalMetodePembayaran()" style="padding: 12px 14px; border: 1.5px solid #bae6fd; background: #ffffff; cursor: pointer; transition: all 0.2s; box-shadow: 0 2px 6px rgba(2, 132, 199, 0.05);" onmouseover="this.style.borderColor='#0284c7'" onmouseout="this.style.borderColor='#bae6fd'">
            <div style="display: flex; align-items: center; justify-content: space-between;">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <div id="display-metode-icon" style="width: 38px; height: 38px; border-radius: 10px; background: #ecfdf5; color: #059669; display: flex; align-items: center; justify-content: center; font-size: 16px; flex-shrink: 0; font-weight: 900;">
                        <i class="fa-solid fa-handshake"></i>
                    </div>
                    <div>
                        <div id="display-metode-nama" style="font-size: 13.5px; font-weight: 800; color: #0f172a;">COD (Bayar di Tempat)</div>
                        <div id="display-metode-sub" style="font-size: 11px; color: #64748b; font-weight: 500;">Bayar tunai langsung saat serah terima barang</div>
                    </div>
                </div>
                <div style="display: flex; align-items: center; gap: 5px; color: #0284c7; font-size: 12px; font-weight: 800;">
                    <span>Ubah</span>
                    <i class="fa-solid fa-chevron-right" style="font-size: 10px;"></i>
                </div>
            </div>

            <input type="hidden" name="metode_pembayaran" id="input_metode_pembayaran" value="COD">
        </div>

        <!-- PERUBAHAN: Panel Voucher Toko Dinamis dengan AJAX -->
        <div class="section-title mt-2">Voucher Promo Toko</div>
        <div class="clean-card" id="card-voucher" style="padding: 0; overflow: hidden; transition: all 0.3s; margin-bottom: 12px;">
            <div onclick="toggleVoucher()" style="padding: 12px 14px; display: flex; justify-content: space-between; align-items: center; cursor: pointer; background: white;">
                <span style="font-size: 13px; font-weight: 800; color: #0284c7; display: flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-ticket"></i> Gunakan Voucher Toko
                </span>
                <span id="voucher-status-label" style="font-size: 11.5px; font-weight: 700; color: #64748b; display: flex; align-items: center; gap: 6px;">
                    Punya kode? <span style="font-size: 10px;">▼</span>
                </span>
            </div>
            <div id="voucher-panel" style="display: none; padding: 10px 14px; background: #f8fafc; border-top: 1px solid #f1f5f9;">
                <div style="display: flex; gap: 8px;">
                    <input type="text" id="input_kode_voucher_field" placeholder="Ketik kode voucher toko" style="flex: 1; padding: 8px 12px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 12.5px; font-weight: 700; text-transform: uppercase; outline: none; color: #0f172a;" onfocus="this.style.borderColor='#0284c7'" onblur="this.style.borderColor='#cbd5e1'" class="rentify-input">
                    <button type="button" onclick="terapkanVoucher()" id="btn-terapkan-voucher" style="background: #0284c7; color: white; border: none; padding: 8px 16px; border-radius: 8px; font-weight: 800; font-size: 12px; cursor: pointer; transition: 0.2s;">Pakai</button>
                </div>
                <div id="voucher-message" style="margin-top: 6px; font-size: 11px; font-weight: 600; color: #ef4444; display: none;"></div>
            </div>
        </div>

        <div class="section-title mt-2">Rincian Pembayaran</div>
        <div class="clean-card mb-4">
            <div style="display: flex; justify-content: space-between; font-size: 13.5px; margin-bottom: 6px; color: #475569;">
                <span>Subtotal Produk</span><span id="grand-sewa" style="font-weight: 700; color: #1e293b;">Rp 0</span>
            </div>
            
            <div id="row-diskon" style="display: none; justify-content: space-between; font-size: 13.5px; margin-bottom: 6px; color: #0284c7; font-weight: 800;">
                <span>Diskon Voucher Toko</span><span id="grand-diskon">- Rp 0</span>
            </div>

            <div style="display: flex; justify-content: space-between; font-size: 13.5px; border-bottom: 1px solid #e2e8f0; padding-bottom: 10px; margin-bottom: 10px; color: #475569;">
                <span>Subtotal Pengiriman</span><span id="grand-ongkir" style="font-weight: 700; color: #1e293b;">Rp 0</span>
            </div>
            <div style="display: flex; justify-content: space-between; align-items: center; font-size: 15px; font-weight: 800; color: #0f172a;">
                <span>Total Pembayaran</span><span id="grand-total" style="color:#0284c7; font-size: 18px;">Rp 0</span>
            </div>
            <div style="font-size: 10.5px; color: #94a3b8; text-align: right; margin-top: 4px;">*Tidak termasuk uang jaminan deposit vendor</div>
        </div>

        <div class="bottom-bar">
            <div>
                <div style="font-size: 11.5px; font-weight: 600; color: #64748b;">Total Pembayaran</div>
                <div style="font-size: 19px; font-weight: 900; color: #0284c7;" id="bar-total">Rp 0</div>
            </div>
            <button type="button" class="btn-buat-pesanan" onclick="validasiSubmit()">Buat Pesanan</button>
        </div>

        <!-- BOTTOM SHEET MODAL UBAH JADWAL SEWA -->
        <div id="modalUbahJadwal" style="display: none; position: fixed; inset: 0; z-index: 99999;">
            <div onclick="tutupModalJadwal()" style="position: absolute; inset: 0; background: rgba(15, 23, 42, 0.55); backdrop-filter: blur(2px);"></div>
            <div style="position: absolute; bottom: 0; left: 0; right: 0; max-width: 550px; margin: 0 auto; background: white; border-radius: 20px 20px 0 0; box-shadow: 0 -10px 30px rgba(0,0,0,0.15); max-height: 85vh; display: flex; flex-direction: column; overflow: hidden; animation: slideUpSheet 0.25s cubic-bezier(0.16, 1, 0.3, 1);">
                <div style="padding: 16px 20px; border-bottom: 1px solid #f1f5f9; display: flex; align-items: center; justify-content: space-between;">
                    <div style="font-size: 15px; font-weight: 900; color: #0f172a;">Ubah Jadwal Sewa (WIB)</div>
                    <button type="button" onclick="tutupModalJadwal()" style="background: #f1f5f9; border: none; width: 32px; height: 32px; border-radius: 50%; color: #64748b; font-size: 14px; cursor: pointer; display: flex; align-items: center; justify-content: center;">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                <div style="padding: 18px 20px; overflow-y: auto; display: flex; flex-direction: column; gap: 14px;">
                    <div>
                        <label style="display: block; font-size: 12.5px; font-weight: 700; color: #334155; margin-bottom: 6px;">
                            <i class="fa-regular fa-calendar text-sky-500 mr-1"></i> Tanggal Mulai Sewa
                        </label>
                        <input type="date" id="modal_input_date" value="{{ request('start_date', date('Y-m-d')) }}" min="{{ date('Y-m-d') }}" style="width: 100%; padding: 10px 14px; border: 1.5px solid #cbd5e1; border-radius: 10px; font-size: 13.5px; font-weight: 700; color: #0f172a; outline: none;" onfocus="this.style.borderColor='#0284c7'" onblur="this.style.borderColor='#cbd5e1'">
                    </div>
                    <div>
                        <label style="display: block; font-size: 12.5px; font-weight: 700; color: #334155; margin-bottom: 6px;">
                            <i class="fa-regular fa-clock text-sky-500 mr-1"></i> Jam Pengambilan / Pengantaran
                        </label>
                        <select id="modal_input_time" style="width: 100%; padding: 10px 14px; border: 1.5px solid #cbd5e1; border-radius: 10px; font-size: 13.5px; font-weight: 700; color: #0f172a; outline: none; background: white;" onfocus="this.style.borderColor='#0284c7'" onblur="this.style.borderColor='#cbd5e1'">
                            @php
                                $jamOptions = ['08:00', '09:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00', '17:00', '18:00', '19:00', '20:00', '21:00'];
                                $curTime = request('start_time', '09:00');
                            @endphp
                            @foreach($jamOptions as $jam)
                                <option value="{{ $jam }}" {{ substr($curTime, 0, 5) == $jam ? 'selected' : '' }}>{{ $jam }} WIB</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label style="display: block; font-size: 12.5px; font-weight: 700; color: #334155; margin-bottom: 6px;">
                            <i class="fa-solid fa-hourglass-half text-sky-500 mr-1"></i> Durasi Pemakaian (Hari)
                        </label>
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <button type="button" onclick="ubahDurasiModal(-1)" style="width: 42px; height: 42px; border-radius: 10px; border: 1.5px solid #cbd5e1; background: #f8fafc; font-size: 18px; font-weight: 900; color: #0284c7; cursor: pointer; display: flex; align-items: center; justify-content: center;">-</button>
                            <input type="number" id="modal_input_durasi" value="{{ $durasiDefault ?? 1 }}" min="1" max="90" style="flex: 1; text-align: center; padding: 10px; border: 1.5px solid #cbd5e1; border-radius: 10px; font-size: 15px; font-weight: 900; color: #0f172a; outline: none;" onchange="if(this.value < 1) this.value = 1;">
                            <button type="button" onclick="ubahDurasiModal(1)" style="width: 42px; height: 42px; border-radius: 10px; border: 1.5px solid #cbd5e1; background: #f8fafc; font-size: 18px; font-weight: 900; color: #0284c7; cursor: pointer; display: flex; align-items: center; justify-content: center;">+</button>
                        </div>
                    </div>
                </div>
                <div style="padding: 14px 20px; border-top: 1px solid #f1f5f9; background: #fafafa;">
                    <button type="button" onclick="simpanJadwalModal()" style="width: 100%; padding: 12px; background: #0284c7; color: white; border: none; border-radius: 12px; font-weight: 800; font-size: 14px; cursor: pointer; transition: 0.15s;">
                        Terapkan Jadwal Baru
                    </button>
                </div>
            </div>
        </div>

        <!-- BOTTOM SHEET MODAL PILIH METODE PEMBAYARAN (ALA SHOPEE) -->
        <div id="modalMetodePembayaran" style="display: none; position: fixed; inset: 0; z-index: 99999;">
            <!-- Backdrop Overlay -->
            <div onclick="tutupModalMetodePembayaran()" style="position: absolute; inset: 0; background: rgba(15, 23, 42, 0.55); backdrop-filter: blur(2px);"></div>
            
            <!-- Modal Sheet -->
            <div style="position: absolute; bottom: 0; left: 0; right: 0; max-width: 550px; margin: 0 auto; background: white; border-radius: 20px 20px 0 0; box-shadow: 0 -10px 30px rgba(0,0,0,0.15); max-height: 85vh; display: flex; flex-direction: column; overflow: hidden; animation: slideUpSheet 0.25s cubic-bezier(0.16, 1, 0.3, 1);">
                
                <!-- Header Sheet -->
                <div style="padding: 16px 20px; border-bottom: 1px solid #f1f5f9; display: flex; align-items: center; justify-content: space-between;">
                    <div style="font-size: 16px; font-weight: 900; color: #0f172a;">Pilih Metode Pembayaran</div>
                    <button type="button" onclick="tutupModalMetodePembayaran()" style="background: #f1f5f9; border: none; width: 32px; height: 32px; border-radius: 50%; color: #64748b; font-size: 14px; cursor: pointer; display: flex; align-items: center; justify-content: center;">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <!-- Body Options List -->
                <div style="padding: 14px 16px; overflow-y: auto; display: flex; flex-direction: column; gap: 8px;">
                    
                    <!-- KATEGORI 1: COD (DEFAULT ACTIVE) -->
                    <div style="font-size: 11px; font-weight: 800; color: #64748b; text-transform: uppercase; margin-top: 4px; margin-bottom: 2px;">Bayar di Tempat</div>
                    <div class="opsi-bayar-row" onclick="pilihOpsiPembayaran('COD', 'COD (Bayar di Tempat)', 'Bayar tunai langsung saat serah terima barang', 'fa-solid fa-handshake', '#059669', '#ecfdf5')" style="padding: 12px 14px; border: 1.5px solid #0284c7; background: #f0f9ff; border-radius: 14px; display: flex; align-items: center; justify-content: space-between; cursor: pointer; transition: all 0.15s;" id="opsi-row-COD">
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <div style="width: 36px; height: 36px; border-radius: 10px; background: #ecfdf5; color: #059669; display: flex; align-items: center; justify-content: center; font-size: 15px; flex-shrink: 0;">
                                <i class="fa-solid fa-handshake"></i>
                            </div>
                            <div>
                                <div style="font-size: 13.5px; font-weight: 800; color: #0f172a;">COD (Bayar di Tempat)</div>
                                <div style="font-size: 11px; color: #64748b;">Bayar tunai saat serah terima barang</div>
                            </div>
                        </div>
                        <div class="radio-indicator" id="radio-indicator-COD" style="width: 20px; height: 20px; border-radius: 50%; border: 2px solid #0284c7; background: #0284c7; display: flex; align-items: center; justify-content: center; color: white; font-size: 11px; font-weight: 900;">✓</div>
                    </div>

                    <!-- KATEGORI 2: TRANSFER BANK (VIRTUAL ACCOUNT) -->
                    <div style="font-size: 11px; font-weight: 800; color: #64748b; text-transform: uppercase; margin-top: 10px; margin-bottom: 2px;">Transfer Bank (Virtual Account)</div>

                    <!-- MANDIRI -->
                    <div class="opsi-bayar-row" onclick="pilihOpsiPembayaran('BANK_MANDIRI', 'Bank Mandiri (Virtual Account)', 'Transfer Livin\' by Mandiri & ATM (Verifikasi Otomatis)', 'fa-solid fa-building-columns', '#002d62', '#eff6ff')" style="padding: 12px 14px; border: 1.5px solid #e2e8f0; background: white; border-radius: 14px; display: flex; align-items: center; justify-content: space-between; cursor: pointer; transition: all 0.15s;" id="opsi-row-BANK_MANDIRI">
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <div style="width: 36px; height: 36px; border-radius: 10px; background: #002d62; color: #ffb700; display: flex; align-items: center; justify-content: center; font-size: 10px; font-weight: 900; flex-shrink: 0;">
                                MANDIRI
                            </div>
                            <div>
                                <div style="font-size: 13.5px; font-weight: 800; color: #0f172a;">Bank Mandiri</div>
                                <div style="font-size: 11px; color: #64748b;">Verifikasi Otomatis (Livin' by Mandiri & ATM)</div>
                            </div>
                        </div>
                        <div class="radio-indicator" id="radio-indicator-BANK_MANDIRI" style="width: 20px; height: 20px; border-radius: 50%; border: 2px solid #cbd5e1; background: white; display: flex; align-items: center; justify-content: center; color: white; font-size: 11px; font-weight: 900;"></div>
                    </div>

                    <!-- BCA -->
                    <div class="opsi-bayar-row" onclick="pilihOpsiPembayaran('BANK_BCA', 'Bank BCA (Virtual Account)', 'Transfer BCA mobile & KlikBCA (Verifikasi Otomatis)', 'fa-solid fa-building-columns', '#005baa', '#eff6ff')" style="padding: 12px 14px; border: 1.5px solid #e2e8f0; background: white; border-radius: 14px; display: flex; align-items: center; justify-content: space-between; cursor: pointer; transition: all 0.15s;" id="opsi-row-BANK_BCA">
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <div style="width: 36px; height: 36px; border-radius: 10px; background: #005baa; color: white; display: flex; align-items: center; justify-content: center; font-size: 11.5px; font-weight: 900; flex-shrink: 0;">
                                BCA
                            </div>
                            <div>
                                <div style="font-size: 13.5px; font-weight: 800; color: #0f172a;">Bank BCA</div>
                                <div style="font-size: 11px; color: #64748b;">Verifikasi Otomatis (m-BCA, KlikBCA, ATM)</div>
                            </div>
                        </div>
                        <div class="radio-indicator" id="radio-indicator-BANK_BCA" style="width: 20px; height: 20px; border-radius: 50%; border: 2px solid #cbd5e1; background: white; display: flex; align-items: center; justify-content: center; color: white; font-size: 11px; font-weight: 900;"></div>
                    </div>

                    <!-- BNI -->
                    <div class="opsi-bayar-row" onclick="pilihOpsiPembayaran('BANK_BNI', 'Bank BNI (Virtual Account)', 'Transfer BNI Mobile & ATM BNI (Verifikasi Otomatis)', 'fa-solid fa-building-columns', '#f15a24', '#fff7ed')" style="padding: 12px 14px; border: 1.5px solid #e2e8f0; background: white; border-radius: 14px; display: flex; align-items: center; justify-content: space-between; cursor: pointer; transition: all 0.15s;" id="opsi-row-BANK_BNI">
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <div style="width: 36px; height: 36px; border-radius: 10px; background: #f15a24; color: white; display: flex; align-items: center; justify-content: center; font-size: 11.5px; font-weight: 900; flex-shrink: 0;">
                                BNI
                            </div>
                            <div>
                                <div style="font-size: 13.5px; font-weight: 800; color: #0f172a;">Bank BNI</div>
                                <div style="font-size: 11px; color: #64748b;">Verifikasi Otomatis (BNI Mobile, ATM BNI)</div>
                            </div>
                        </div>
                        <div class="radio-indicator" id="radio-indicator-BANK_BNI" style="width: 20px; height: 20px; border-radius: 50%; border: 2px solid #cbd5e1; background: white; display: flex; align-items: center; justify-content: center; color: white; font-size: 11px; font-weight: 900;"></div>
                    </div>

                    <!-- BRI -->
                    <div class="opsi-bayar-row" onclick="pilihOpsiPembayaran('BANK_BRI', 'Bank BRI (BRIVA)', 'Transfer BRImo & ATM BRI (Verifikasi Otomatis)', 'fa-solid fa-building-columns', '#00529c', '#eff6ff')" style="padding: 12px 14px; border: 1.5px solid #e2e8f0; background: white; border-radius: 14px; display: flex; align-items: center; justify-content: space-between; cursor: pointer; transition: all 0.15s;" id="opsi-row-BANK_BRI">
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <div style="width: 36px; height: 36px; border-radius: 10px; background: #00529c; color: white; display: flex; align-items: center; justify-content: center; font-size: 11.5px; font-weight: 900; flex-shrink: 0;">
                                BRI
                            </div>
                            <div>
                                <div style="font-size: 13.5px; font-weight: 800; color: #0f172a;">Bank BRI (BRIVA)</div>
                                <div style="font-size: 11px; color: #64748b;">Verifikasi Otomatis (BRImo, ATM BRI)</div>
                            </div>
                        </div>
                        <div class="radio-indicator" id="radio-indicator-BANK_BRI" style="width: 20px; height: 20px; border-radius: 50%; border: 2px solid #cbd5e1; background: white; display: flex; align-items: center; justify-content: center; color: white; font-size: 11px; font-weight: 900;"></div>
                    </div>

                    <!-- PERMATA -->
                    <div class="opsi-bayar-row" onclick="pilihOpsiPembayaran('BANK_PERMATA', 'Bank Permata (Virtual Account)', 'Transfer PermataMobile X & ATM (Verifikasi Otomatis)', 'fa-solid fa-building-columns', '#008852', '#ecfdf5')" style="padding: 12px 14px; border: 1.5px solid #e2e8f0; background: white; border-radius: 14px; display: flex; align-items: center; justify-content: space-between; cursor: pointer; transition: all 0.15s;" id="opsi-row-BANK_PERMATA">
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <div style="width: 36px; height: 36px; border-radius: 10px; background: #008852; color: white; display: flex; align-items: center; justify-content: center; font-size: 9px; font-weight: 900; flex-shrink: 0;">
                                PERMATA
                            </div>
                            <div>
                                <div style="font-size: 13.5px; font-weight: 800; color: #0f172a;">Bank Permata</div>
                                <div style="font-size: 11px; color: #64748b;">Verifikasi Otomatis (PermataMobile X, ATM)</div>
                            </div>
                        </div>
                        <div class="radio-indicator" id="radio-indicator-BANK_PERMATA" style="width: 20px; height: 20px; border-radius: 50%; border: 2px solid #cbd5e1; background: white; display: flex; align-items: center; justify-content: center; color: white; font-size: 11px; font-weight: 900;"></div>
                    </div>

                    <!-- BANK LAINNYA -->
                    <div class="opsi-bayar-row" onclick="pilihOpsiPembayaran('BANK_LAINNYA', 'Bank Lainnya (Virtual Account)', 'Transfer ATM Bersama, Prima & Alto', 'fa-solid fa-building-columns', '#475569', '#f8fafc')" style="padding: 12px 14px; border: 1.5px solid #e2e8f0; background: white; border-radius: 14px; display: flex; align-items: center; justify-content: space-between; cursor: pointer; transition: all 0.15s;" id="opsi-row-BANK_LAINNYA">
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <div style="width: 36px; height: 36px; border-radius: 10px; background: #475569; color: white; display: flex; align-items: center; justify-content: center; font-size: 13px; font-weight: 900; flex-shrink: 0;">
                                <i class="fa-solid fa-building-columns"></i>
                            </div>
                            <div>
                                <div style="font-size: 13.5px; font-weight: 800; color: #0f172a;">Bank Lainnya</div>
                                <div style="font-size: 11px; color: #64748b;">Transfer dari bank lain via ATM Bersama/Prima</div>
                            </div>
                        </div>
                        <div class="radio-indicator" id="radio-indicator-BANK_LAINNYA" style="width: 20px; height: 20px; border-radius: 50%; border: 2px solid #cbd5e1; background: white; display: flex; align-items: center; justify-content: center; color: white; font-size: 11px; font-weight: 900;"></div>
                    </div>

                    <!-- KATEGORI 3: QRIS & E-WALLET -->
                    <div style="font-size: 11px; font-weight: 800; color: #64748b; text-transform: uppercase; margin-top: 10px; margin-bottom: 2px;">QRIS & E-Wallet</div>

                    <!-- QRIS -->
                    <div class="opsi-bayar-row" onclick="pilihOpsiPembayaran('QRIS', 'QRIS (Semua E-Wallet & Bank)', 'GoPay, DANA, ShopeePay, OVO, BCA, Livin, dll', 'fa-solid fa-qrcode', '#0284c7', '#e0f2fe')" style="padding: 12px 14px; border: 1.5px solid #e2e8f0; background: white; border-radius: 14px; display: flex; align-items: center; justify-content: space-between; cursor: pointer; transition: all 0.15s;" id="opsi-row-QRIS">
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <div style="width: 36px; height: 36px; border-radius: 10px; background: #e0f2fe; color: #0284c7; display: flex; align-items: center; justify-content: center; font-size: 16px; flex-shrink: 0;">
                                <i class="fa-solid fa-qrcode"></i>
                            </div>
                            <div>
                                <div style="font-size: 13.5px; font-weight: 800; color: #0f172a;">QRIS (Instan & Semua E-Wallet)</div>
                                <div style="font-size: 11px; color: #64748b;">GoPay, DANA, ShopeePay, OVO, BCA, Livin, dll</div>
                            </div>
                        </div>
                        <div class="radio-indicator" id="radio-indicator-QRIS" style="width: 20px; height: 20px; border-radius: 50%; border: 2px solid #cbd5e1; background: white; display: flex; align-items: center; justify-content: center; color: white; font-size: 11px; font-weight: 900;"></div>
                    </div>

                    <!-- GOPAY -->
                    <div class="opsi-bayar-row" onclick="pilihOpsiPembayaran('GOPAY', 'GoPay (Bayar Instan)', 'Buka aplikasi GoPay langsung', 'fa-solid fa-wallet', '#00a5cf', '#e0f2fe')" style="padding: 12px 14px; border: 1.5px solid #e2e8f0; background: white; border-radius: 14px; display: flex; align-items: center; justify-content: space-between; cursor: pointer; transition: all 0.15s;" id="opsi-row-GOPAY">
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <div style="width: 36px; height: 36px; border-radius: 10px; background: #e0f2fe; color: #00a5cf; display: flex; align-items: center; justify-content: center; font-size: 15px; flex-shrink: 0;">
                                <i class="fa-solid fa-wallet"></i>
                            </div>
                            <div>
                                <div style="font-size: 13.5px; font-weight: 800; color: #0f172a;">GoPay</div>
                                <div style="font-size: 11px; color: #64748b;">Bayar instan via aplikasi GoPay</div>
                            </div>
                        </div>
                        <div class="radio-indicator" id="radio-indicator-GOPAY" style="width: 20px; height: 20px; border-radius: 50%; border: 2px solid #cbd5e1; background: white; display: flex; align-items: center; justify-content: center; color: white; font-size: 11px; font-weight: 900;"></div>
                    </div>

                    <!-- SHOPEEPAY -->
                    <div class="opsi-bayar-row" onclick="pilihOpsiPembayaran('SHOPEEPAY', 'ShopeePay (Bayar Instan)', 'Buka aplikasi ShopeePay langsung', 'fa-solid fa-wallet', '#ee4d2d', '#fff1f0')" style="padding: 12px 14px; border: 1.5px solid #e2e8f0; background: white; border-radius: 14px; display: flex; align-items: center; justify-content: space-between; cursor: pointer; transition: all 0.15s;" id="opsi-row-SHOPEEPAY">
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <div style="width: 36px; height: 36px; border-radius: 10px; background: #fff1f0; color: #ee4d2d; display: flex; align-items: center; justify-content: center; font-size: 15px; flex-shrink: 0;">
                                <i class="fa-solid fa-wallet"></i>
                            </div>
                            <div>
                                <div style="font-size: 13.5px; font-weight: 800; color: #0f172a;">ShopeePay</div>
                                <div style="font-size: 11px; color: #64748b;">Bayar instan via aplikasi ShopeePay</div>
                            </div>
                        </div>
                        <div class="radio-indicator" id="radio-indicator-SHOPEEPAY" style="width: 20px; height: 20px; border-radius: 50%; border: 2px solid #cbd5e1; background: white; display: flex; align-items: center; justify-content: center; color: white; font-size: 11px; font-weight: 900;"></div>
                    </div>

                </div>

                <!-- Tombol Konfirmasi Sheet -->
                <div style="padding: 12px 16px; border-top: 1px solid #f1f5f9; background: #fafafa;">
                    <button type="button" onclick="tutupModalMetodePembayaran()" style="width: 100%; padding: 13px; background: #0284c7; color: white; border: none; border-radius: 12px; font-weight: 800; font-size: 14px; cursor: pointer; transition: 0.15s;">
                        Konfirmasi Pilihan
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    const formatRp = (angka) => 'Rp ' + Math.round(angka).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    let lokasiCustomer = null;
    
    // PERUBAHAN: Variabel global untuk menyimpan data diskon aktif
    let diskonAktif = 0;

    function toggleVoucher() {
        const panel = document.getElementById('voucher-panel');
        panel.style.display = (panel.style.display === 'none' || panel.style.display === '') ? 'block' : 'none';
    }

    // PERUBAHAN: Fungsi AJAX Terapkan Voucher
    function terapkanVoucher() {
        const field = document.getElementById('input_kode_voucher_field');
        const kode = field.value.trim().toUpperCase();
        const msgDiv = document.getElementById('voucher-message');
        const btn = document.getElementById('btn-terapkan-voucher');
        
        if (kode === '') {
            msgDiv.innerText = '⚠️ Silakan ketik kode voucher!';
            msgDiv.style.color = '#ef4444';
            msgDiv.style.display = 'block';
            return;
        }

        // Kumpulkan total belanja per vendor untuk dikirim ke server
        const vendorSubtotals = {};
        document.querySelectorAll('.vendor-block').forEach(block => {
            const vId = block.getAttribute('data-vendor');
            const durasi = parseInt(block.querySelector('.input-durasi').value) || 1;
            let subSewaToko = 0;
            block.querySelectorAll('.harga-sewa-item').forEach(el => { 
                subSewaToko += parseInt(el.value) * durasi; 
            });
            vendorSubtotals[vId] = subSewaToko;
        });

        btn.innerText = 'Cek...';
        btn.disabled = true;

        fetch('{{ route('customer.checkout.cek_voucher') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                kode_voucher: kode,
                vendor_ids: vendorSubtotals
            })
        })
        .then(response => response.json())
        .then(data => {
            btn.innerText = 'Pakai';
            btn.disabled = false;
            msgDiv.style.display = 'block';

            if (data.success) {
                msgDiv.innerText = '✅ ' + data.message;
                msgDiv.style.color = '#10b981';
                
                // Simpan data voucher (potongan dan ID)
                diskonAktif = data.potongan;
                document.getElementById('input_voucher_data_json').value = JSON.stringify({
                    potongan: data.potongan,
                    vendor_id: data.vendor_id,
                    voucher_id: data.voucher_id
                });
                
                const card = document.getElementById('card-voucher');
                const label = document.getElementById('voucher-status-label');
                card.style.borderColor = '#0ea5e9';
                card.style.background = '#f0f9ff';
                label.innerHTML = '<span style="background: #0284c7; color: white; padding: 2px 8px; border-radius: 6px; font-size: 11px; font-weight: 800;">✓ POTONGAN ' + formatRp(data.potongan) + '</span>';
                
                hitungSemuaTotal();
            } else {
                msgDiv.innerText = '❌ ' + data.message;
                msgDiv.style.color = '#ef4444';
                diskonAktif = 0;
                document.getElementById('input_voucher_data_json').value = '';
                hitungSemuaTotal();
            }
        })
        .catch(error => {
            btn.innerText = 'Pakai';
            btn.disabled = false;
            msgDiv.innerText = '⚠️ Terjadi kesalahan koneksi.';
            msgDiv.style.display = 'block';
        });
    }

    function updateJaminanExclusive() {
        const selectedMap = {}; 
        const allRadios = document.querySelectorAll('.radio-jaminan');
        
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

    // PERUBAHAN: Memasukkan diskon dinamis ke perhitungan total
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

        const rowDiskon = document.getElementById('row-diskon');
        if (diskonAktif > 0) {
            document.getElementById('grand-diskon').innerText = "- " + formatRp(diskonAktif);
            rowDiskon.style.display = 'flex';
        } else {
            rowDiskon.style.display = 'none';
        }

        document.getElementById('grand-ongkir').innerText = formatRp(totalOngkirSemua);
        
        // Mencegah total belanja menjadi minus jika diskon lebih besar
        const grandTotal = Math.max(0, (totalSewaSemua - diskonAktif)) + totalOngkirSemua;
        
        document.getElementById('grand-total').innerText = formatRp(grandTotal);
        document.getElementById('bar-total').innerText = formatRp(grandTotal);
    }

    function validasiSubmit() {
        const inputWa = document.getElementById('input_wa_wajib');
        if (!inputWa.value || inputWa.value.trim() === '') {
            alert("⚠️ Mohon isi Nomor WhatsApp Customer terlebih dahulu agar vendor dapat menghubungi Anda.");
            inputWa.focus();
            inputWa.style.borderColor = '#0284c7';
            return;
        }

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

        let adaDiantarTanpaGPS = false;
        document.querySelectorAll('.vendor-block').forEach(block => {
            if (block.querySelector('.radio-opsi:checked').value === 'diantar' && !lokasiCustomer) adaDiantarTanpaGPS = true;
        });
        if (adaDiantarTanpaGPS) {
            alert("⚠️ Ada produk yang Anda pilih 'Diantar'. Mohon tekan tombol 'Sinkronisasi Titik GPS Saya' agar kurir tahu titik pengiriman.");
            return;
        }

        const btnSubmit = document.querySelector('.btn-buat-pesanan');
        if (btnSubmit) {
            btnSubmit.disabled = true;
            btnSubmit.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-2"></i> Memproses Pesanan...';
            btnSubmit.style.opacity = '0.7';
            btnSubmit.style.cursor = 'not-allowed';
        }

        syncData(); 
        document.getElementById('form-checkout').submit();
    }

    let targetVendorJadwal = null;

    function bukaModalJadwal(vendorId) {
        targetVendorJadwal = vendorId;
        const curDurasi = document.querySelector(`input[name="durasi_sewa[${vendorId}]"]`)?.value || 1;
        document.getElementById('modal_input_durasi').value = curDurasi;
        document.getElementById('modalUbahJadwal').style.display = 'block';
        document.body.style.overflow = 'hidden';
    }

    function tutupModalJadwal() {
        document.getElementById('modalUbahJadwal').style.display = 'none';
        document.body.style.overflow = '';
    }

    function ubahDurasiModal(delta) {
        const input = document.getElementById('modal_input_durasi');
        let val = (parseInt(input.value) || 1) + delta;
        if (val < 1) val = 1;
        input.value = val;
    }

    function simpanJadwalModal() {
        const newDate = document.getElementById('modal_input_date').value;
        const newTime = document.getElementById('modal_input_time').value;
        const newDurasi = parseInt(document.getElementById('modal_input_durasi').value) || 1;

        const inputStartDate = document.querySelector('input[name="start_date"]');
        if (inputStartDate) inputStartDate.value = newDate;
        const inputStartTime = document.querySelector('input[name="start_time"]');
        if (inputStartTime) inputStartTime.value = newTime;

        document.querySelectorAll('.vendor-block').forEach(block => {
            const vId = block.getAttribute('data-vendor');
            const durInput = block.querySelector('.input-durasi');
            if (durInput) durInput.value = newDurasi;

            const dispTgl = document.getElementById(`display_tgl_mulai_${vId}`);
            if (dispTgl && newDate) {
                const d = new Date(newDate + 'T00:00:00');
                const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
                dispTgl.innerText = String(d.getDate()).padStart(2, '0') + ' ' + months[d.getMonth()] + ' ' + d.getFullYear();
            }

            const dispJam = document.getElementById(`display_jam_mulai_${vId}`);
            if (dispJam) dispJam.innerText = newTime + ' WIB';

            const dispDur = document.getElementById(`display_durasi_${vId}`);
            if (dispDur) dispDur.innerText = newDurasi + ' Hari';
        });

        tutupModalJadwal();
        hitungSemuaTotal();
    }

    let currentMetode = 'COD';

    function bukaModalMetodePembayaran() {
        document.getElementById('modalMetodePembayaran').style.display = 'block';
        document.body.style.overflow = 'hidden';
    }

    function tutupModalMetodePembayaran() {
        document.getElementById('modalMetodePembayaran').style.display = 'none';
        document.body.style.overflow = '';
    }

    function pilihOpsiPembayaran(kode, nama, sub, ikon, textCol, bgCol) {
        currentMetode = kode;
        document.getElementById('input_metode_pembayaran').value = kode;
        
        // Update tampilan di kartu checkout
        document.getElementById('display-metode-nama').innerText = nama;
        document.getElementById('display-metode-sub').innerText = sub;
        const iconContainer = document.getElementById('display-metode-icon');
        iconContainer.innerHTML = `<i class="${ikon}"></i>`;
        iconContainer.style.background = bgCol;
        iconContainer.style.color = textCol;

        // Update indikator radio di bottom sheet
        const allMethods = ['COD', 'BANK_MANDIRI', 'BANK_BCA', 'BANK_BNI', 'BANK_BRI', 'BANK_PERMATA', 'BANK_LAINNYA', 'QRIS', 'GOPAY', 'SHOPEEPAY'];
        allMethods.forEach(k => {
            const rad = document.getElementById('radio-indicator-' + k);
            const row = document.getElementById('opsi-row-' + k);
            if (rad && row) {
                if (k === kode) {
                    rad.style.borderColor = '#0284c7';
                    rad.style.background = '#0284c7';
                    rad.innerText = '✓';
                    row.style.borderColor = '#0284c7';
                    row.style.background = '#f0f9ff';
                } else {
                    rad.style.borderColor = '#cbd5e1';
                    rad.style.background = 'white';
                    rad.innerText = '';
                    row.style.borderColor = '#e2e8f0';
                    row.style.background = 'white';
                }
            }
        });

        // Tutup modal secara mulus
        setTimeout(tutupModalMetodePembayaran, 200);
    }

    window.onload = function() {
        hitungSemuaTotal();
        updateJaminanExclusive();
    };
</script>
@endsection