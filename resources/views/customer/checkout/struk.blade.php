@extends('layouts.app')

@section('content')
<style>
    nav, header, footer { display: none !important; }
    body { background-color: #f1f5f9; font-family: 'Segoe UI', Tahoma, sans-serif; padding-top: 20px; padding-bottom: 50px;}
    .struk-container { max-width: 520px; margin: 0 auto; padding: 0 15px; }
    
    /* Kartu Struk Premium */
    .receipt-card { background: white; padding: 30px 25px; border-radius: 16px; box-shadow: 0 10px 30px rgba(0,0,0,0.06); border: 1px solid #e2e8f0; position: relative; overflow: hidden; }
    .receipt-card::top { content: ""; position: absolute; top: 0; left: 0; right: 0; height: 6px; background: linear-gradient(90deg, #0284c7, #38bdf8, #10b981); }
    
    .info-row { display: flex; justify-content: space-between; font-size: 14px; margin-bottom: 10px; color: #475569; }
    
    /* Tombol Kontak & Maps Interaktif */
    .action-link { display: inline-flex; align-items: center; gap: 6px; font-size: 13px; font-weight: 700; color: #0284c7; background: #e0f2fe; padding: 8px 12px; border-radius: 8px; text-decoration: none; transition: all 0.2s; border: 1px solid #bae6fd; margin-top: 8px; word-break: break-word; }
    .action-link:hover { background: #0284c7; color: white; border-color: #0284c7; transform: translateY(-1px); box-shadow: 0 4px 10px rgba(2, 132, 199, 0.2); }
    
    .wa-link { color: #047857; background: #dcfce7; border-color: #86efac; }
    .wa-link:hover { background: #10b981; color: white; border-color: #10b981; box-shadow: 0 4px 10px rgba(16, 185, 129, 0.2); }

    /* Tombol Aksi Bawah */
    .btn-download { display: flex; align-items: center; justify-content: center; gap: 8px; width: 100%; padding: 15px; border-radius: 14px; font-weight: 800; font-size: 15px; cursor: pointer; border: none; background: #10b981; color: white; box-shadow: 0 6px 20px rgba(16, 185, 129, 0.25); transition: 0.2s; margin-top: 25px; }
    .btn-download:hover { background: #059669; transform: translateY(-2px); }
    
    .btn-home { display: block; text-align: center; padding: 15px; border-radius: 14px; font-weight: 800; font-size: 15px; text-decoration: none; margin-top: 12px; background: #0284c7; color: white; box-shadow: 0 6px 20px rgba(2, 132, 199, 0.25); transition: 0.2s; }
    .btn-home:hover { background: #0369a1; transform: translateY(-2px); }

    /* Menyembunyikan elemen tombol saat di-download / print */
    @media print {
        body { background: white !important; padding: 0 !important; }
        .no-print, .btn-download, .btn-home { display: none !important; }
        .receipt-card { box-shadow: none !important; border: none !important; width: 100% !important; max-width: 100% !important; padding: 10px !important; }
    }
</style>

<!-- Memanggil Library html2canvas dari CDN untuk fitur Download Gambar -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

<div class="struk-container">
    
    <!-- Area Kartu Struk yang Akan Diberi ID untuk Di-download -->
    <div class="receipt-card" id="card-struk-download">
        
        <!-- Header Struk -->
        <div style="text-align: center; margin-bottom: 25px; border-bottom: 2px dashed #cbd5e1; padding-bottom: 20px;">
            <div style="width: 50px; height: 50px; background: #dcfce7; color: #10b981; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 24px; margin: 0 auto 12px auto; box-shadow: 0 4px 10px rgba(16, 185, 129, 0.15);">✓</div>
            <div style="font-size: 22px; font-weight: 900; color: #0f172a; letter-spacing: -0.5px;">STRUK PESANAN</div>
            <div style="font-size: 13px; font-weight: 600; color: #64748b; margin-top: 4px;">ID Transaksi: <span style="color: #0f172a;">{{ $id }}</span></div>
            <div style="color: #10b981; font-weight: 800; margin-top: 10px; font-size: 14px; background: #f0fdf4; display: inline-block; padding: 4px 12px; border-radius: 20px; border: 1px solid #bbf7d0;">BERHASIL</div>
        </div>
        
        <!-- Informasi Pembeli -->
        <div class="info-row"><span style="font-weight: 600;">Pembeli</span> <span style="font-weight:800; color:#0f172a;">{{ auth()->user()->name ?? 'Customer' }}</span></div>
        <div class="info-row"><span style="font-weight: 600;">WhatsApp</span> <span style="font-weight:800; color:#0f172a;">{{ $no_hp }}</span></div>
        <div class="info-row"><span style="font-weight: 600;">Metode Pembayaran</span> <span style="font-weight:800; color: #0284c7; background: #e0f2fe; padding: 2px 10px; border-radius: 6px; font-size: 13px;">{{ $metode }}</span></div>
        
        <!-- Rincian Barang per Toko -->
        <div style="margin: 25px 0; border-top: 2px dashed #cbd5e1; padding-top: 20px;">
            <div style="font-size: 13px; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 15px;">RINCIAN BARANG SEWA:</div>
            
            @php $grandSewa = 0; @endphp

            @foreach($keranjangPerVendor as $vendorId => $items)
                @php 
                    $vendor = $items->first()->barang->vendor;
                    $barangPertama = $items->first()->barang;
                    $durasi = (int) ($durasi_sewa_array[$vendorId] ?? 1); 
                    $jaminanToko = $jaminan_array[$vendorId] ?? 'KTP';
                    $opsiToko = $opsi_array[$vendorId] ?? 'ambil';
                    
                    $waRaw = $vendor->whatsapp_vendor ?? ($vendor->no_hp ?? '081234567890');
                    $waClean = preg_replace('/[^0-9]/', '', $waRaw);
                    if (substr($waClean, 0, 1) === '0') {
                        $waClean = '62' . substr($waClean, 1);
                    }

                    $daftarBarang = $items->pluck('barang.nama')->implode(', ');
                    $pesanWa = "Halo Toko *{$vendor->vendor_name}*, saya sudah membuat pesanan di Rentify dengan ID Transaksi: *{$id}*.\n\n" .
                               " *Barang:* {$daftarBarang} ({$durasi} Hari)\n" .
                               " *Jaminan:* {$jaminanToko}\n" .
                               " *Pengiriman:* " . strtoupper($opsiToko) . "\n\n" .
                               "Mohon segera dikonfirmasi ya! Terima kasih.";
                    $linkWa = "https://wa.me/{$waClean}?text=" . urlencode($pesanWa);
                    
                    $lat = $barangPertama->latitude ?? '0';
                    $lon = $barangPertama->longitude ?? '0';
                    $alamat = $barangPertama->alamat ?? 'Alamat toko belum diatur';
                    $linkMaps = ($lat != '0' && $lon != '0') 
                                ? "https://maps.google.com/?q={$lat},{$lon}" 
                                : "https://maps.google.com/?q=" . urlencode($alamat);
                @endphp
                
                <div style="background: #f8fafc; padding: 18px; border-radius: 12px; margin-bottom: 16px; border: 1px solid #cbd5e1; box-shadow: 0 2px 5px rgba(0,0,0,0.02);">
                    
                    <div style="font-size: 14px; font-weight: 800; color: #0f172a; margin-bottom: 12px; border-bottom: 1px solid #e2e8f0; padding-bottom: 10px; display:flex; justify-content:space-between; align-items:center;">
                        <span style="display: flex; align-items: center; gap: 6px;">{{ $vendor->vendor_name ?? 'Vendor' }} <span style="font-size: 12px; font-weight: 600; color: #64748b;">({{ $durasi }} Hari)</span></span>
                        <span style="font-size: 11px; background: #e0f2fe; color: #0284c7; padding: 3px 8px; border-radius: 6px; font-weight: 800; border: 1px solid #bae6fd;">Jaminan: {{ $jaminanToko }}</span>
                    </div>
                    
                    @foreach($items as $item)
                        @php 
                            $hargaMarkup = $item->barang->harga_sewa_harian * 1.05;
                            $sewa = $hargaMarkup * $item->jumlah * $durasi;
                            $grandSewa += $sewa; 
                            
                            $waktuMulai = \Carbon\Carbon::parse($waktu_pesan, 'Asia/Jakarta');
                            $waktuKembali = $waktuMulai->copy()->addDays($durasi);
                        @endphp
                        
                        <div style="margin-bottom: 12px; border-bottom: 1px dashed #e2e8f0; padding-bottom: 10px;">
                            <div style="display: flex; justify-content: space-between; font-size: 14px; font-weight: 700; color:#1e293b;">
                                <span>{{ $item->barang->nama }} <span style="color:#64748b; font-size:12px; font-weight:normal;">(x{{ $item->jumlah }})</span></span>
                                <span style="color:#0f172a;">Rp {{ number_format($sewa, 0, ',', '.') }}</span>
                            </div>
                            
                            @if($item->barang->denda_per_hari > 0)
                            <div style="font-size: 11px; font-weight: 600; color: #e11d48; margin-top: 3px; font-style: italic;">
                                *Denda telat kembali: Rp {{ number_format($item->barang->denda_per_hari, 0, ',', '.') }}/hari
                            </div>
                            @endif
                            
                            @if($item->barang->deposit > 0)
                            <div style="font-size: 11px; font-weight: 600; color: #b45309; margin-top: 2px;">
                                *Deposit fisik (bayar di tempat): Rp {{ number_format($item->barang->deposit * $item->jumlah, 0, ',', '.') }}
                            </div>
                            @endif
                        </div>
                        
                        <div style="font-size: 12px; font-weight: 800; color: #047857; margin-top: 10px; display: flex; align-items: center; gap: 6px;">
                            <span> Kembali Maksimal:</span>
                            <span style="background: #dcfce7; padding: 2px 8px; border-radius: 4px;">{{ $waktuKembali->format('d M Y - H:i') }} WIB</span>
                        </div>
                    @endforeach

                    @if($opsiToko === 'ambil')
                        <div style="margin-top: 10px;">
                            <a href="{{ $linkMaps }}" target="_blank" class="action-link no-print" title="Klik untuk membuka lokasi di Google Maps">
                                <i class="fa-solid fa-location-dot"></i> 📍 Lokasi Toko: {{ $alamat }} (Klik Buka Maps ↗)
                            </a>
                        </div>
                    @endif

                    <div style="margin-top: 6px;">
                        <a href="{{ $linkWa }}" target="_blank" class="action-link wa-link no-print" title="Klik untuk langsung chat ke WhatsApp Vendor">
                            <i class="fa-brands fa-whatsapp text-lg"></i>  WhatsApp Toko: {{ $waRaw }} (Klik Kirim)
                        </a>
                    </div>

                </div>
            @endforeach
        </div>

        <!-- Rincian Akhir Tagihan Web -->
        <div style="background: #f8fafc; padding: 15px; border-radius: 12px; border: 1px solid #e2e8f0;">
            <div class="info-row"><span style="font-weight: 600;">Total Sewa Produk</span> <span style="font-weight: 700; color:#0f172a;">Rp {{ number_format($grandSewa, 0, ',', '.') }}</span></div>
            <div class="info-row" style="border-bottom: 1px solid #cbd5e1; padding-bottom: 12px; margin-bottom: 12px;">
                <span style="font-weight: 600;">Total Ongkos Kirim</span> 
                <span style="font-weight: 700; color:#0f172a;">Rp {{ number_format($total - $grandSewa, 0, ',', '.') }}</span>
            </div>
            
            <div style="display: flex; justify-content: space-between; align-items: center; font-size: 18px; font-weight: 900; color: #0284c7;">
                <span>TOTAL </span>
                <span style="font-size: 22px;">Rp {{ number_format($total, 0, ',', '.') }}</span>
            </div>
            <div style="font-size: 10px; color: #94a3b8; text-align: right; margin-top: 4px;">*Belum termasuk deposit fisik di tempat (jika ada)</div>
        </div>
        
    </div>
    <!-- AKHIR AREA KARTU STRUK -->

    <!-- TOMBOL AKSI BAWAH -->
    <button type="button" id="btn-download-struk" onclick="downloadStrukAsImage()" class="btn-download">
         Download Struk
    </button>

    <a href="{{ route('customer.home') }}" class="btn-home">
        Kembali ke Beranda
    </a>
</div>

<script>
    // SCRIPT AJAIB: MENGUBAH KARTU STRUK MENJADI GAMBAR PNG & DOWNLOAD OTOMATIS
    function downloadStrukAsImage() {
        const btn = document.getElementById('btn-download-struk');
        const originalText = btn.innerHTML;
        
        // Ubah teks tombol jadi loading
        btn.innerHTML = 'Memproses Gambar... Mohon Tunggu...';
        btn.disabled = true;

        // Targetkan elemen yang memiliki ID card-struk-download
        const cardElement = document.getElementById('card-struk-download');

        // Gunakan html2canvas dengan skala resolusi 2x lipat agar gambar tajam (HD)
        html2canvas(cardElement, {
            scale: 2,
            backgroundColor: '#ffffff',
            useCORS: true,
            logging: false
        }).then(canvas => {
            // Ubah hasil render canvas menjadi URL Gambar (PNG)
            const imageURI = canvas.toDataURL("image/png");

            // Buat link download virtual dan klik secara otomatis
            const downloadLink = document.createElement('a');
            downloadLink.href = imageURI;
            downloadLink.download = 'Struk-Rentify-{{ $id }}.png';
            document.body.appendChild(downloadLink);
            downloadLink.click();
            document.body.removeChild(downloadLink);

            // Kembalikan teks tombol seperti semula
            btn.innerHTML = 'Berhasil Didownload! (Klik untuk Download Lagi)';
            btn.disabled = false;
            
            setTimeout(() => {
                btn.innerHTML = originalText;
            }, 3000);
        }).catch(err => {
            console.error("Gagal mendownload struk:", err);
            alert("Maaf, terjadi kesalahan saat memproses gambar. Anda juga bisa menggunakan fitur Screenshot HP atau Ctrl+P di PC.");
            btn.innerHTML = originalText;
            btn.disabled = false;
        });
    }
</script>
@endsection