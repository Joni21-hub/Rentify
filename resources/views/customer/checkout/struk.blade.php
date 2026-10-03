@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
    nav, header, footer, .navbar, .header, .top-bar, .footer, #footer { display: none !important; }
    
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap');
    
    body { 
        background: radial-gradient(circle at center, #fff9ef 0%, #bad6eb 100%) fixed !important; 
        font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; 
        padding: 24px 12px 60px 12px;
        color: #0f172a;
    }
    
    .struk-wrapper { 
        max-width: 480px; 
        margin: 0 auto; 
    }
    
    .receipt-card { 
        background: #ffffff; 
        border-radius: 24px; 
        box-shadow: 0 12px 36px rgba(2, 132, 199, 0.12), 0 2px 8px rgba(0, 0, 0, 0.04); 
        border: 1px solid #e2e8f0; 
        position: relative; 
        overflow: hidden; 
        line-height: 1.35; 
        -webkit-font-smoothing: antialiased;
        padding: 26px 20px;
    }
    .receipt-card, .receipt-card * { 
        box-sizing: border-box; 
    }
    
    .receipt-top-stripe {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 6px;
        background: linear-gradient(90deg, #0284c7, #38bdf8, #10b981);
    }
    
    .info-row { 
        display: flex; 
        justify-content: space-between; 
        align-items: center;
        font-size: 13px; 
        margin-bottom: 8px; 
        color: #475569; 
        line-height: 1.4; 
    }
    .info-row:last-child {
        margin-bottom: 0;
    }
    
    .action-btn-pill {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 11.5px;
        font-weight: 700;
        padding: 7px 12px;
        border-radius: 10px;
        text-decoration: none;
        transition: all 0.2s;
        word-break: break-word;
    }
    .maps-pill {
        color: #0284c7;
        background: #f0f9ff;
        border: 1px solid #bae6fd;
    }
    .maps-pill:hover {
        background: #0284c7;
        color: #ffffff;
    }
    .wa-pill {
        color: #059669;
        background: #ecfdf5;
        border: 1px solid #a7f3d0;
    }
    .wa-pill:hover {
        background: #10b981;
        color: #ffffff;
    }
    
    .btn-action-main {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        width: 100%;
        padding: 14px;
        border-radius: 16px;
        font-weight: 800;
        font-size: 14px;
        cursor: pointer;
        border: none;
        transition: transform 0.15s, box-shadow 0.15s;
        text-decoration: none;
    }
    .btn-download-img {
        background: linear-gradient(135deg, #10b981, #059669);
        color: #ffffff;
        box-shadow: 0 6px 20px rgba(16, 185, 129, 0.28);
        margin-top: 20px;
    }
    .btn-download-img:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(16, 185, 129, 0.35);
    }
    .btn-back-home {
        background: #ffffff;
        color: #0284c7;
        border: 1.5px solid #bae6fd;
        margin-top: 10px;
        box-shadow: 0 4px 14px rgba(2, 132, 199, 0.08);
    }
    .btn-back-home:hover {
        background: #f0f9ff;
        transform: translateY(-1px);
    }

    @media print {
        body { background: white !important; padding: 0 !important; }
        .no-print, .btn-action-main { display: none !important; }
        .receipt-card { box-shadow: none !important; border: none !important; width: 100% !important; max-width: 100% !important; padding: 10px !important; }
    }
</style>

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

<div class="struk-wrapper">
    
    <!-- KARTU STRUK (YANG AKAN DI-DOWNLOAD & PERSIS SAMA DENGAN TAMPILAN LAYAR) -->
    <div class="receipt-card" id="card-struk-download">
        
        <div class="receipt-top-stripe"></div>
        
        <!-- HEADER IDENTITAS RENTIFY -->
        <div style="text-align: center; padding-top: 6px; padding-bottom: 18px; border-bottom: 2px dashed #cbd5e1;">
            
            <!-- Logo & Brand Header -->
            <div style="display: flex; align-items: center; justify-content: center; gap: 8px; margin-bottom: 4px;">
                <img src="https://res.cloudinary.com/fnf8f1pm/image/upload/v1784199454/gambar_logo_trerjo.png" 
                     alt="Rentify" 
                     crossorigin="anonymous"
                     style="height: 34px; width: 34px; object-fit: contain;">
                <span style="font-size: 22px; font-weight: 900; color: #0284c7; letter-spacing: -0.5px;">Rentify</span>
            </div>
            
            <div style="font-size: 10px; font-weight: 800; color: #64748b; letter-spacing: 0.5px; text-transform: uppercase;">
                Bukti Transaksi Pemesanan Resmi
            </div>
            
            <!-- ID Transaksi -->
            <div style="margin-top: 10px; display: inline-flex; align-items: center; gap: 6px; background: #f8fafc; border: 1px solid #e2e8f0; padding: 4px 12px; border-radius: 20px;">
                <span style="font-size: 11px; font-weight: 600; color: #64748b;">No. Transaksi:</span>
                <span style="font-size: 12px; font-weight: 900; color: #0f172a; font-family: monospace;">{{ $id }}</span>
            </div>

            <!-- STATUS PEMBAYARAN: BERSIH TANPA KATA MIDTRANS -->
            <div style="margin-top: 12px;">
                @if(strtoupper($metode) === 'COD')
                    <div style="display: inline-flex; align-items: center; gap: 6px; background: #f0f9ff; color: #0284c7; border: 1.5px solid #bae6fd; padding: 6px 16px; border-radius: 30px; font-size: 12px; font-weight: 900;">
                        <i class="fa-solid fa-handshake text-xs"></i>
                        <span>COD &bull; BAYAR DI TEMPAT</span>
                    </div>
                    <div style="font-size: 10.5px; color: #64748b; margin-top: 4px; font-weight: 600;">
                        (Bayar tunai langsung saat serah terima barang)
                    </div>
                @else
                    <div style="display: inline-flex; align-items: center; gap: 6px; background: #ecfdf5; color: #059669; border: 1.5px solid #a7f3d0; padding: 6px 16px; border-radius: 30px; font-size: 12px; font-weight: 900;">
                        <i class="fa-solid fa-circle-check text-xs"></i>
                        <span>LUNAS &bull; PEMBAYARAN TERKONFIRMASI</span>
                    </div>
                    <div style="font-size: 10.5px; color: #64748b; margin-top: 4px; font-weight: 600;">
                        (Transaksi digital berhasil diverifikasi sistem)
                    </div>
                @endif
            </div>

        </div>

        <!-- INFORMASI DATA CUSTOMER -->
        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 14px; padding: 12px 14px; margin-top: 16px; margin-bottom: 18px;">
            <div class="info-row">
                <span style="font-size: 12px; font-weight: 600; color: #64748b; display: flex; align-items: center; gap: 6px;">
                    <i class="fa-solid fa-user text-slate-400 text-xs"></i> Pembeli
                </span>
                <span style="font-size: 13px; font-weight: 800; color: #0f172a;">
                    {{ auth()->user()->name ?? 'Customer' }}
                </span>
            </div>
            
            <div class="info-row">
                <span style="font-size: 12px; font-weight: 600; color: #64748b; display: flex; align-items: center; gap: 6px;">
                    <i class="fa-brands fa-whatsapp text-emerald-500 text-xs"></i> WhatsApp
                </span>
                <span style="font-size: 13px; font-weight: 800; color: #0f172a; font-family: monospace;">
                    {{ $no_hp }}
                </span>
            </div>

            <div class="info-row">
                <span style="font-size: 12px; font-weight: 600; color: #64748b; display: flex; align-items: center; gap: 6px;">
                    <i class="fa-solid fa-wallet text-sky-500 text-xs"></i> Pembayaran
                </span>
                <span style="font-size: 11.5px; font-weight: 800; color: #0284c7; background: #e0f2fe; padding: 2px 10px; border-radius: 6px; border: 1px solid #bae6fd;">
                    @if(strtoupper($metode) === 'COD')
                        COD (Bayar di Tempat)
                    @elseif(str_contains(strtoupper($metode), 'QRIS'))
                        QRIS
                    @elseif(str_contains(strtoupper($metode), 'BANK') || str_contains(strtoupper($metode), 'VA'))
                        Transfer Bank (Virtual Account)
                    @else
                        Pembayaran Online Otomatis
                    @endif
                </span>
            </div>
        </div>

        <!-- RINCIAN BARANG SEWA -->
        <div style="margin-bottom: 16px;">
            <div style="font-size: 12px; font-weight: 800; color: #475569; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 10px; display: flex; align-items: center; gap: 6px;">
                <i class="fa-solid fa-box-open text-sky-500"></i>
                <span>Rincian Barang Sewa</span>
            </div>

            @php $grandSewa = 0; @endphp

            @foreach($keranjangPerVendor as $vendorId => $items)
                @php 
                    $vendor = $items->first()->barang->vendor;
                    $barangPertama = $items->first()->barang;
                    $durasi = (int) ($durasi_sewa_array[$vendorId] ?? 1); 
                    $jaminanToko = $jaminan_array[$vendorId] ?? 'KTP';
                    $opsiToko = $opsi_array[$vendorId] ?? 'ambil';
                    
                    $orderAsli = \Illuminate\Support\Facades\DB::table('orders')
                        ->where('vendor_id', $vendorId)
                        ->where(function($q) use ($id) {
                            $orderIds = [];
                            foreach (explode('_', $id) as $part) {
                                if (str_starts_with($part, 'INV-')) $orderIds[] = (int) str_replace('INV-', '', $part);
                            }
                            $q->whereIn('id', $orderIds);
                        })->first();

                    $waktuMulai = $orderAsli ? \Carbon\Carbon::parse($orderAsli->start_rent, 'Asia/Jakarta') : \Carbon\Carbon::parse($waktu_pesan, 'Asia/Jakarta');
                    $waktuKembali = $orderAsli ? \Carbon\Carbon::parse($orderAsli->end_rent, 'Asia/Jakarta') : $waktuMulai->copy()->addDays($durasi);

                    $waRaw = $vendor->whatsapp_vendor ?? ($vendor->no_hp ?? '081234567890');
                    $waClean = preg_replace('/[^0-9]/', '', $waRaw);
                    if (substr($waClean, 0, 1) === '0') {
                        $waClean = '62' . substr($waClean, 1);
                    }

                    $daftarBarang = $items->pluck('barang.nama')->implode(', ');
                    $pesanWa = "Halo Toko *{$vendor->vendor_name}*, saya sudah membuat pesanan di Rentify dengan ID Transaksi: *{$id}*.\n\n" .
                               " *Barang:* {$daftarBarang} ({$durasi} Hari)\n" .
                               " *Jadwal Pakai:* " . $waktuMulai->format('d M Y (H:i)') . " s/d " . $waktuKembali->format('d M Y (H:i)') . " WIB\n" .
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
                
                <div style="background: #ffffff; padding: 14px; border-radius: 14px; margin-bottom: 12px; border: 1.5px solid #e2e8f0;">
                    
                    <!-- Vendor Header -->
                    <div style="font-size: 13px; font-weight: 800; color: #0f172a; margin-bottom: 10px; border-bottom: 1px solid #f1f5f9; padding-bottom: 8px; display:flex; justify-content:space-between; align-items:center; gap: 8px; flex-wrap: wrap;">
                        <div style="display: flex; align-items: center; gap: 6px;">
                            <i class="fa-solid fa-store text-sky-500 text-xs"></i>
                            <span>{{ $vendor->vendor_name ?? 'Vendor' }}</span>
                            <span style="font-size: 11px; font-weight: 800; color: #0284c7; background: #e0f2fe; padding: 2px 7px; border-radius: 6px; border: 1px solid #bae6fd; display: inline-flex; align-items: center; gap: 3px; white-space: nowrap;">
                                <i class="fa-regular fa-clock" style="font-size: 9.5px;"></i> {{ $durasi }} Hari
                            </span>
                        </div>
                        <span style="font-size: 10.5px; background: #f8fafc; color: #475569; padding: 2px 7px; border-radius: 6px; font-weight: 800; border: 1px solid #cbd5e1; white-space: nowrap;">
                            Jaminan: {{ $jaminanToko }}
                        </span>
                    </div>
                    
                    <!-- Items List -->
                    @foreach($items as $item)
                        @php 
                            $hargaMarkup = $item->barang->harga_sewa_harian * 1.05;
                            $sewa = $hargaMarkup * $item->jumlah * $durasi;
                            $grandSewa += $sewa; 
                        @endphp
                        
                        <div style="margin-bottom: 8px; border-bottom: 1px dashed #f1f5f9; padding-bottom: 8px;">
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; font-size: 13px; font-weight: 700; color: #1e293b;">
                                <span style="flex: 1; padding-right: 8px;">
                                    {{ $item->barang->nama }} 
                                    <span style="color: #64748b; font-size: 11.5px; font-weight: 600;">(x{{ $item->jumlah }})</span>
                                </span>
                                <span style="color: #0f172a; font-weight: 800; white-space: nowrap;">
                                    Rp {{ number_format($sewa, 0, ',', '.') }}
                                </span>
                            </div>
                            
                            @if($item->barang->denda_per_hari > 0)
                            <div style="font-size: 10.5px; font-weight: 600; color: #e11d48; margin-top: 2px;">
                                *Denda telat: Rp {{ number_format($item->barang->denda_per_hari, 0, ',', '.') }}/hari
                            </div>
                            @endif
                            
                            @if($item->barang->deposit > 0)
                            <div style="font-size: 10.5px; font-weight: 600; color: #b45309; margin-top: 2px;">
                                *Deposit fisik di tempat: Rp {{ number_format($item->barang->deposit * $item->jumlah, 0, ',', '.') }}
                            </div>
                            @endif
                        </div>
                    @endforeach

                    <!-- JADWAL PEMAKAIAN -->
                    <div style="background: #f0f9ff; border: 1px solid #bae6fd; padding: 10px 12px; border-radius: 10px; margin-top: 8px;">
                        <div style="font-size: 10.5px; font-weight: 800; color: #0369a1; text-transform: uppercase; margin-bottom: 4px; display: flex; align-items: center; gap: 5px;">
                            <i class="fa-regular fa-calendar-check"></i> Jadwal Pemakaian (WIB)
                        </div>
                        <div style="font-size: 11.5px; font-weight: 700; color: #0f172a; display: flex; justify-content: space-between; margin-bottom: 3px;">
                            <span style="color: #64748b;">Mulai Ambil:</span>
                            <span style="color: #0284c7; font-weight: 800;">{{ $waktuMulai->format('d M Y - H:i') }} WIB</span>
                        </div>
                        <div style="font-size: 11.5px; font-weight: 700; color: #0f172a; display: flex; justify-content: space-between;">
                            <span style="color: #64748b;">Maks Kembali:</span>
                            <span style="color: #0ea5e9; font-weight: 800;">{{ $waktuKembali->format('d M Y - H:i') }} WIB</span>
                        </div>
                    </div>

                    <!-- LOKASI & WHATSAPP TOKO (TAMPAK RAPI BAIK DI LAYAR MAUPUN SAAT DI-DOWNLOAD) -->
                    <div style="margin-top: 8px; display: flex; flex-direction: column; gap: 5px;">
                        @if($opsiToko === 'ambil')
                            <a href="{{ $linkMaps }}" target="_blank" class="action-btn-pill maps-pill" title="Klik untuk membuka titik lokasi toko di Google Maps">
                                <i class="fa-solid fa-location-dot" style="font-size: 12px; flex-shrink: 0;"></i>
                                <span style="flex: 1;">Lokasi Toko: {{ $alamat }} (Buka Maps ↗)</span>
                            </a>
                        @endif

                        <a href="{{ $linkWa }}" target="_blank" class="action-btn-pill wa-pill" title="Klik untuk chat WhatsApp ke vendor toko">
                            <i class="fa-brands fa-whatsapp" style="font-size: 13px; flex-shrink: 0;"></i>
                            <span>Chat WhatsApp Toko: {{ $waRaw }} (Kirim Pesan ↗)</span>
                        </a>
                    </div>

                </div>
            @endforeach
        </div>

        <!-- TOTAL RINCIAN PEMBAYARAN -->
        <div style="background: linear-gradient(135deg, #f8fafc 0%, #f0f9ff 100%); padding: 14px 16px; border-radius: 14px; border: 1.5px solid #bae6fd;">
            <div class="info-row">
                <span style="font-weight: 600; color: #64748b;">Total Sewa Produk</span>
                <span style="font-weight: 800; color: #0f172a;">Rp {{ number_format($grandSewa, 0, ',', '.') }}</span>
            </div>
            
            <div class="info-row" style="border-bottom: 1px dashed #cbd5e1; padding-bottom: 8px; margin-bottom: 8px;">
                <span style="font-weight: 600; color: #64748b;">Total Ongkos Kirim</span> 
                <span style="font-weight: 800; color: #0f172a;">Rp {{ number_format($total - $grandSewa, 0, ',', '.') }}</span>
            </div>
            
            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 4px;">
                <span style="font-size: 14px; font-weight: 900; color: #0f172a; text-transform: uppercase; letter-spacing: 0.5px;">TOTAL PEMBAYARAN</span>
                <span style="font-size: 20px; font-weight: 900; color: #0284c7;">Rp {{ number_format($total, 0, ',', '.') }}</span>
            </div>
            
            <div style="font-size: 10px; color: #94a3b8; text-align: right; margin-top: 4px;">
                *Belum termasuk deposit fisik di tempat (jika ada)
            </div>
        </div>

        <!-- FOOTER RESMI STRUK -->
        <div style="text-align: center; margin-top: 18px; padding-top: 14px; border-top: 1px dashed #e2e8f0; font-size: 10.5px; color: #94a3b8; line-height: 1.4;">
            <i class="fa-solid fa-shield-halved text-sky-500 mr-1"></i>
            <span>Transaksi Resmi Rentify &bull; Harap simpan struk ini sebagai bukti serah terima unit rental.</span>
        </div>
        
    </div>

    <!-- TOMBOL AKSI DI BAWAH (HANYA DITAMPILKAN DI LAYAR, TIDAK MASUK FILE GAMBAR STRUK) -->
    <button type="button" id="btn-download-struk" onclick="downloadStrukAsImage()" class="btn-action-main btn-download-img">
        <i class="fa-solid fa-download text-base"></i>
        <span>Download Struk Gambar (PNG)</span>
    </button>

    <a href="{{ route('customer.home') }}" class="btn-action-main btn-back-home">
        <i class="fa-solid fa-house text-sm"></i>
        <span>Kembali ke Beranda</span>
    </a>

</div>

<script>
    function downloadStrukAsImage() {
        const btn = document.getElementById('btn-download-struk');
        const originalHtml = btn.innerHTML;
        
        btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin text-base"></i> <span>Sedang Merender Struk Kualitas Tinggi...</span>';
        btn.disabled = true;

        const cardElement = document.getElementById('card-struk-download');

        html2canvas(cardElement, {
            scale: 3, // Kualitas resolusi tinggi (3x retina) agar teks super tajam & tidak pecah
            backgroundColor: '#ffffff',
            useCORS: true,
            allowTaint: true,
            logging: false,
            scrollX: 0,
            scrollY: 0,
            windowWidth: cardElement.scrollWidth,
            onclone: (clonedDoc) => {
                const card = clonedDoc.getElementById('card-struk-download');
                if (card) {
                    card.style.transform = 'none';
                    card.style.boxShadow = 'none';
                    card.style.margin = '0 auto';
                }
            }
        }).then(canvas => {
            const imageURI = canvas.toDataURL("image/png");

            const downloadLink = document.createElement('a');
            downloadLink.href = imageURI;
            downloadLink.download = 'Struk-Rentify-{{ $id }}.png';
            document.body.appendChild(downloadLink);
            downloadLink.click();
            document.body.removeChild(downloadLink);

            btn.innerHTML = '<i class="fa-solid fa-check text-base"></i> <span>Struk Berhasil Didownload!</span>';
            btn.disabled = false;
            
            setTimeout(() => {
                btn.innerHTML = originalHtml;
            }, 3000);
        }).catch(err => {
            console.error("Gagal mendownload struk:", err);
            alert("Maaf, terjadi kesalahan saat memproses gambar. Anda juga dapat menggunakan tangkapan layar (screenshot) HP Anda.");
            btn.innerHTML = originalHtml;
            btn.disabled = false;
        });
    }
</script>
@endsection