@extends('layouts.app')

@section('content')
<style>
    nav, header, footer { display: none !important; }
    body { background-color: #f8fafc; font-family: 'Segoe UI', Tahoma, sans-serif; padding-top: 15px; padding-bottom: 90px;}
    .history-container { max-width: 600px; margin: 0 auto; padding: 0 15px;}
    
    .header-title { font-size: 18px; font-weight: 800; color: #0284c7; display: flex; align-items: center; gap: 12px; margin-bottom: 20px; }
    
    /* Kartu Pesanan */
    .order-card { background: white; border-radius: 16px; margin-bottom: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.04); border: 1px solid #e2e8f0; overflow: hidden; transition: 0.2s; }
    .order-header { background: #f8fafc; padding: 14px 18px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; font-size: 13px; }
    
    /* Status Badge */
    .badge { padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; }
    .badge-menunggu { background: #56aaef; color: #3b71ef; border: 1px solid #0c6edf; }
    .badge-berjalan { background: #e0f2fe; color: #0284c7; border: 1px solid #bae6fd; }
    .badge-selesai { background: #dceafc; color: #4b89fdd5; border: 1px solid #3690ea; }
    .badge-batal { background: #63aef4; color: #2e70f4eb; border: 1px solid #3371f7; }

    /* Tombol Kontak & Maps Interaktif */
    .action-link { display: inline-flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 700; color: #0284c7; background: #e0f2fe; padding: 6px 10px; border-radius: 8px; text-decoration: none; transition: 0.2s; border: 1px solid #bae6fd; margin-top: 6px; word-break: break-word; }
    .action-link:hover { background: #0284c7; color: white; }
    .wa-link { color: #277be9b9; background: #0d56ff; border-color: #65b4f4; }
    .wa-link:hover { background: #2767f1; color: white; }

    /* Tombol Selesaikan Pesanan */
    .btn-selesai { width: 100%; background: linear-gradient(135deg, #316feb, #3074d3); color: white; border: none; padding: 14px; font-size: 14px; font-weight: 800; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; transition: 0.2s; }
    .btn-selesai:hover { background: #5c94d7; }
</style>

<div class="history-container">
    <div class="header-title">
        <a href="{{ route('customer.home') }}" style="text-decoration: none; color: #0f172a; font-size: 20px;">←</a> 
        <span>Riwayat Transaksi Saya</span>
    </div>

    @if(session('success'))
        <div style="background: #dcfce7; border: 1px solid #368ce2; color: #3491fc; padding: 14px; border-radius: 12px; margin-bottom: 20px; font-size: 13px; font-weight: 700; display: flex; align-items: center; gap: 10px;">
            <span></span> {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div style="background: #fee2e2; border: 1px solid #fecaca; color: #991b1b; padding: 14px; border-radius: 12px; margin-bottom: 20px; font-size: 13px; font-weight: 700;">
             {{ session('error') }}
        </div>
    @endif

    <!-- JIKA BELUM ADA PESANAN (KOSONG) -->
    @if($orders->isEmpty())
        <div style="background: white; border-radius: 16px; padding: 50px 20px; text-align: center; border: 1px solid #e2e8f0; margin-top: 40px;">
            <div style="width: 70px; height: 70px; background: #e0f2fe; color: #0284c7; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 30px; margin: 0 auto 15px auto;">🧾</div>
            <div style="font-size: 16px; font-weight: 800; color: #0f172a;">Belum ada transaksi</div>
            <p style="font-size: 13px; color: #64748b; margin-top: 4px; margin-bottom: 20px;">Kamu belum pernah menyewa barang apapun. Yuk, mulai cari barang impianmu!</p>
            <a href="{{ route('customer.home') }}" style="background: #0284c7; color: white; padding: 12px 25px; border-radius: 10px; font-weight: 800; font-size: 14px; text-decoration: none; display: inline-block; box-shadow: 0 4px 12px rgba(2, 132, 199, 0.25);">Sewa Sekarang</a>
        </div>
    @else
        <!-- JIKA ADA PESANAN (LOOPING DAFTAR TRANSAKSI) -->
        @foreach($orders as $order)
            @php
                $statusClass = 'badge-menunggu';
                if($order->status === 'Berjalan' || $order->status === 'Dikonfirmasi') $statusClass = 'badge-berjalan';
                if($order->status === 'Selesai') $statusClass = 'badge-selesai';
                if($order->status === 'Dibatalkan') $statusClass = 'badge-batal';

                // FORMAT WA VENDOR YANG AMAN
                $waRaw = $order->whatsapp_vendor ?? '081234567890';
                $waClean = preg_replace('/[^0-9]/', '', $waRaw);
                if (substr($waClean, 0, 1) === '0') $waClean = '62' . substr($waClean, 1);
                
                $pesanWa = "Halo Toko *{$order->vendor_name}*, saya ingin menanyakan status pesanan saya dengan ID: *INV-{$order->id}*. Terima kasih.";
                $linkWa = "https://wa.me/{$waClean}?text=" . urlencode($pesanWa);

                $waktuMulai = \Carbon\Carbon::parse($order->start_rent, 'Asia/Jakarta');
                $waktuKembali = \Carbon\Carbon::parse($order->end_rent, 'Asia/Jakarta');
            @endphp

            <div class="order-card">
                <!-- Header Kartu -->
                <div class="order-header">
                    <div>
                        <span style="font-weight: 800; color: #0f172a;">INV-{{ $order->id }}</span>
                        <span style="color: #94a3b8; font-size: 11px; display: block; margin-top: 2px;">{{ \Carbon\Carbon::parse($order->created_at)->format('d M Y, H:i') }} WIB</span>
                    </div>
                    <span class="badge {{ $statusClass }}">{{ $order->status }}</span>
                </div>

                <!-- Body Kartu (Info Toko & Barang) -->
                <div style="padding: 18px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; border-bottom: 1px solid #f1f5f9; padding-bottom: 10px;">
                        <span style="font-size: 14px; font-weight: 800; color: #0284c7;">{{ $order->vendor_name }}</span>
                        <span style="font-size: 11px; background: #f1f5f9; color: #475569; padding: 3px 8px; border-radius: 6px; font-weight: 700;">Jaminan: {{ $order->jaminan ?? 'KTP' }}</span>
                    </div>

                    <!-- Looping Barang -->
                    @foreach($order->items as $item)
                        <div style="display: flex; gap: 12px; margin-bottom: 12px; border-bottom: 1px dashed #f1f5f9; padding-bottom: 12px;">
                            <div style="width: 60px; height: 60px; border-radius: 8px; border: 1px solid #e2e8f0; overflow:hidden; flex-shrink: 0;">
                                @if($item->cover_photo)
                                    <img src="{{ asset(str_replace('public/', '', $item->cover_photo)) }}" class="w-full h-full object-cover">
                                @else
                                    <div style="width:100%; height:100%; background:#f1f5f9; display:flex; align-items:center; justify-content:center; font-size:10px; color:#94a3b8;">No Img</div>
                                @endif
                            </div>
                            <div style="flex: 1;">
                                <div style="font-size: 14px; font-weight: 700; color: #0f172a;">{{ $item->product_name }} <span style="font-size: 12px; color: #64748b; font-weight: normal;">(x{{ $item->quantity }})</span></div>
                                <div style="font-size: 13px; font-weight: 800; color: #0284c7; margin-top: 2px;">Rp {{ number_format($item->price * $item->quantity * $order->duration_days, 0, ',', '.') }} <span style="font-size: 11px; color:#64748b; font-weight:normal;">({{ $order->duration_days }} Hari)</span></div>
                                
                                <!-- Peringatan Denda & Deposit -->
                                @if($item->denda_per_hari > 0)
                                    <div style="font-size: 11px; font-weight: 600; color: #e11d48; margin-top: 3px; font-style: italic;">*Denda telat: Rp {{ number_format($item->denda_per_hari, 0, ',', '.') }}/hari</div>
                                @endif
                                @if($item->deposit > 0)
                                    <div style="font-size: 11px; font-weight: 600; color: #2d5be6; margin-top: 1px;">*Deposit fisik: Rp {{ number_format($item->deposit * $item->quantity, 0, ',', '.') }}</div>
                                @endif

                                <!-- Tombol Google Maps Toko (Jika Ambil) -->
                                @if($order->shipping_method === 'ambil')
                                    @php
                                        $linkMaps = ($item->latitude && $item->longitude) ? "https://maps.google.com/?q={$item->latitude},{$item->longitude}" : "https://maps.google.com/?q=" . urlencode($item->alamat_toko ?? '');
                                    @endphp
                                    <a href="{{ $linkMaps }}" target="_blank" class="action-link" style="margin-top: 6px;"><i class="fa-solid fa-location-dot"></i>  Maps Toko</a>
                                @endif
                            </div>
                        </div>
                    @endforeach

                    <!-- Tombol WhatsApp & Info Waktu -->
                    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 8px; margin-top: 8px;">
                        <div style="font-size: 11.5px; font-weight: 800; color: #047857; background: #dcfce7; padding: 4px 10px; border-radius: 6px;">
                            Kembali: {{ $waktuKembali->format('d M Y, H:i') }}
                        </div>
                        <a href="{{ $linkWa }}" target="_blank" class="action-link wa-link" style="margin-top: 0;"> Chat WhatsApp Toko</a>
                    </div>

                    <!-- Rincian Total Bayar -->
                    <div style="margin-top: 15px; padding-top: 12px; border-top: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <span style="font-size: 11px; color: #64748b; font-weight: 600; display: block;">Total Pengiriman: {{ strtoupper($order->shipping_method) }} (Rp {{ number_format($order->shipping_fee, 0, ',', '.') }})</span>
                            <span style="font-size: 12px; font-weight: 700; color: #475569;">Total Bayar Web</span>
                        </div>
                        <span style="font-size: 18px; font-weight: 900; color: #0284c7;">Rp {{ number_format($order->total_price, 0, ',', '.') }}</span>
                    </div>
                </div>

                <!-- TOMBOL SELESAIKAN PESANAN (MUNCUL JIKA PESANAN BELUM SELESAI / BATAL) -->
                @if($order->status !== 'Selesai' && $order->status !== 'Dibatalkan')
                    <form action="{{ route('customer.pesanan.selesai', $order->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin barang sudah diterima dengan baik dan ingin menyelesaikan pesanan ini? Uang akan diteruskan ke vendor.');">
                        @csrf
                        <button type="submit" class="btn-selesai" title="Klik jika masa sewa selesai dan barang sudah dikembalikan">
                            <span></span> Selesaikan Pesanan & Lepas Dana
                        </button>
                    </form>
                @else
                    <div style="background: #f8fafc; padding: 12px; text-align: center; font-size: 12px; font-weight: 800; color: #64748b; border-top: 1px solid #e2e8f0;">
                        @if($order->status === 'Selesai')
                            Transaksi Selesai — Dana telah diteruskan ke Vendor & Rentify
                        @else
                            Transaksi Dibatalkan
                        @endif
                    </div>
                @endif
            </div>
        @endforeach
    @endif
</div>
@endsection