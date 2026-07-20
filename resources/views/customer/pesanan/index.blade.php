@extends('layouts.app')

@section('content')
<style>
    nav, header, footer { display: none !important; }
    body { background-color: #f8fafc; font-family: 'Segoe UI', Tahoma, sans-serif; padding-top: 15px; padding-bottom: 90px;}
    .history-container { max-width: 600px; margin: 0 auto; padding: 0 15px;}
    
    .header-title { font-size: 18px; font-weight: 800; color: #0284c7; display: flex; align-items: center; gap: 12px; margin-bottom: 20px; }
    
    .order-card { background: white; border-radius: 16px; margin-bottom: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.04); border: 1px solid #e0f2fe; overflow: hidden; transition: 0.2s; }
    .order-header { background: #f8fafc; padding: 14px 18px; border-bottom: 1px solid #e0f2fe; display: flex; justify-content: space-between; align-items: center; font-size: 13px; }
    
    .badge { padding: 4px 12px; border-radius: 20px; font-size: 10px; font-weight: 900; text-transform: uppercase; letter-spacing: 0.5px; box-shadow: 0 0 8px rgba(14,165,233,0.15); }
    .badge-menunggu { background: #e0f2fe; color: #0284c7; border: 1px solid #7dd3fc; }
    .badge-berjalan { background: linear-gradient(135deg, #38bdf8, #0ea5e9); color: white; border: none; box-shadow: 0 0 10px rgba(14,165,233,0.4); }
    .badge-selesai { background: #f0f9ff; color: #0369a1; border: 1px solid #bae6fd; box-shadow: none; }
    .badge-batal { background: #f8fafc; color: #94a3b8; border: 1px solid #e2e8f0; box-shadow: none; }

    .action-link { display: inline-flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 800; color: #0284c7; background: #e0f2fe; padding: 8px 14px; border-radius: 8px; text-decoration: none; transition: 0.3s; border: 1px solid #bae6fd; margin-top: 6px; word-break: break-word; }
    .action-link:hover { background: #bae6fd; box-shadow: 0 0 10px rgba(14,165,233,0.2); }
    
    .wa-link { color: white; background: linear-gradient(135deg, #38bdf8, #0ea5e9); border: none; box-shadow: 0 0 12px rgba(14,165,233,0.35); }
    .wa-link:hover { background: linear-gradient(135deg, #0ea5e9, #0284c7); box-shadow: 0 0 15px rgba(14,165,233,0.5); transform: translateY(-1px); }

    .btn-selesai { width: 100%; background: linear-gradient(135deg, #38bdf8, #0ea5e9); color: white; border: none; padding: 15px; font-size: 14px; font-weight: 800; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; transition: 0.3s; box-shadow: 0 0 15px rgba(14,165,233,0.4); }
    .btn-selesai:hover { background: linear-gradient(135deg, #0ea5e9, #0284c7); box-shadow: 0 0 20px rgba(14,165,233,0.6); }
</style>

<div class="history-container">
    <div class="header-title">
        <a href="{{ route('customer.dashboard') }}" style="text-decoration: none; color: #0284c7; font-size: 20px; transition: 0.2s;" onmouseover="this.style.color='#0ea5e9'" onmouseout="this.style.color='#0284c7'">←</a> 
        <span>Riwayat Transaksi Saya</span>
    </div>

    @if(session('success'))
        <div style="background: #e0f2fe; border: 1px solid #7dd3fc; color: #0284c7; padding: 14px; border-radius: 12px; margin-bottom: 20px; font-size: 13px; font-weight: 800; display: flex; align-items: center; gap: 10px; box-shadow: 0 0 10px rgba(14,165,233,0.15);">
            <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div style="background: #f1f5f9; border: 1px solid #cbd5e1; color: #475569; padding: 14px; border-radius: 12px; margin-bottom: 20px; font-size: 13px; font-weight: 800; display: flex; align-items: center; gap: 10px;">
             <i class="fa-solid fa-circle-exclamation"></i> {{ session('error') }}
        </div>
    @endif

    @if($orders->isEmpty())
        <div style="background: white; border-radius: 16px; padding: 50px 20px; text-align: center; border: 1px solid #e0f2fe; margin-top: 40px; box-shadow: 0 4px 15px rgba(0,0,0,0.02);">
            <div style="width: 70px; height: 70px; background: #e0f2fe; color: #0ea5e9; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 28px; margin: 0 auto 15px auto; box-shadow: 0 0 15px rgba(14,165,233,0.2);">
                <i class="fa-solid fa-receipt"></i>
            </div>
            <div style="font-size: 16px; font-weight: 800; color: #0f172a;">Belum ada transaksi</div>
            <p style="font-size: 13px; color: #64748b; margin-top: 4px; margin-bottom: 20px;">Kamu belum pernah menyewa barang apapun. Yuk, mulai cari barang impianmu!</p>
            <a href="{{ route('customer.home') }}" style="background: linear-gradient(135deg, #38bdf8, #0ea5e9); color: white; padding: 12px 25px; border-radius: 10px; font-weight: 800; font-size: 14px; text-decoration: none; display: inline-block; box-shadow: 0 0 15px rgba(14,165,233,0.4); transition: 0.3s;" onmouseover="this.style.boxShadow='0 0 20px rgba(14,165,233,0.6)'" onmouseout="this.style.boxShadow='0 0 15px rgba(14,165,233,0.4)'">Sewa Sekarang</a>
        </div>
    @else
        @foreach($orders as $order)
            @php
                $statusClass = 'badge-menunggu';
                if($order->status === 'Berjalan' || $order->status === 'Dikonfirmasi') $statusClass = 'badge-berjalan';
                if($order->status === 'Selesai') $statusClass = 'badge-selesai';
                if($order->status === 'Dibatalkan') $statusClass = 'badge-batal';

                $waRaw = $order->whatsapp_vendor ?? '081234567890';
                $waClean = preg_replace('/[^0-9]/', '', $waRaw);
                if (substr($waClean, 0, 1) === '0') $waClean = '62' . substr($waClean, 1);
                
                $namaTokoAsli = $order->vendor_name ?? $order->owner_name ?? 'Vendor';
                
                $pesanWa = "Halo Toko *" . $namaTokoAsli . "*, saya ingin menanyakan status pesanan saya dengan ID: *INV-" . $order->id . "*. Terima kasih.";
                $linkWa = "https://wa.me/" . $waClean . "?text=" . urlencode($pesanWa);

                $waktuMulai = \Carbon\Carbon::parse($order->start_rent, 'Asia/Jakarta');
                $waktuKembali = \Carbon\Carbon::parse($order->end_rent, 'Asia/Jakarta');
            @endphp

            <div class="order-card">
                <div class="order-header">
                    <div>
                        <span style="font-weight: 900; color: #0284c7;">INV-{{ $order->id }}</span>
                        <span style="color: #94a3b8; font-size: 11px; font-weight: 600; display: block; margin-top: 2px;">{{ \Carbon\Carbon::parse($order->created_at)->format('d M Y, H:i') }} WIB</span>
                    </div>
                    <span class="badge {{ $statusClass }}">{{ $order->status }}</span>
                </div>

                <div style="padding: 18px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; border-bottom: 1px solid #f0f9ff; padding-bottom: 10px;">
                        <span style="font-size: 14px; font-weight: 900; color: #0ea5e9;">{{ $namaTokoAsli }}</span>
                        <span style="font-size: 11px; background: #e0f2fe; color: #0284c7; padding: 3px 8px; border-radius: 6px; font-weight: 800; border: 1px solid #bae6fd;">Jaminan: {{ $order->jaminan ?? 'KTP' }}</span>
                    </div>

                    @foreach($order->items as $item)
                        <div style="display: flex; gap: 12px; margin-bottom: 12px; border-bottom: 1px dashed #e0f2fe; padding-bottom: 12px;">
                            <div style="width: 60px; height: 60px; border-radius: 8px; border: 1px solid #e0f2fe; overflow:hidden; flex-shrink: 0; background: white;">
                                @if($item->cover_photo)
                                    <img src="{{ asset(str_replace('public/', '', $item->cover_photo)) }}" class="w-full h-full object-contain">
                                @else
                                    <div style="width:100%; height:100%; background:#f8fafc; display:flex; align-items:center; justify-content:center; font-size:10px; color:#94a3b8;"><i class="fa-solid fa-image"></i></div>
                                @endif
                            </div>
                            <div style="flex: 1;">
                                <div style="font-size: 14px; font-weight: 800; color: #0f172a;">{{ $item->product_name }} <span style="font-size: 12px; color: #94a3b8; font-weight: 700;">(x{{ $item->quantity }})</span></div>
                                <div style="font-size: 13px; font-weight: 900; color: #0ea5e9; margin-top: 2px;">Rp {{ number_format($item->price * $item->quantity * $order->duration_days, 0, ',', '.') }} <span style="font-size: 11px; color:#94a3b8; font-weight:600;">({{ $order->duration_days }} Hari)</span></div>
                                
                                <div style="margin-top: 6px; display: flex; flex-direction: column; gap: 4px;">
                                    @if($item->denda_per_hari > 0)
                                        <span style="font-size: 10px; font-weight: 800; color: #0284c7; background: #e0f2fe; border: 1px solid #bae6fd; display: inline-block; padding: 2px 8px; border-radius: 4px; width: fit-content;">Denda telat: Rp {{ number_format($item->denda_per_hari, 0, ',', '.') }}/hari</span>
                                    @endif
                                    @if($item->deposit > 0)
                                        <span style="font-size: 10px; font-weight: 800; color: #0369a1; background: #f0f9ff; border: 1px solid #e0f2fe; display: inline-block; padding: 2px 8px; border-radius: 4px; width: fit-content;">Deposit fisik: Rp {{ number_format($item->deposit * $item->quantity, 0, ',', '.') }}</span>
                                    @endif
                                </div>

                                @if($order->shipping_method === 'ambil')
                                    @php
                                        $linkMaps = ($item->latitude && $item->longitude) ? "https://maps.google.com/?q={$item->latitude},{$item->longitude}" : "https://maps.google.com/?q=" . urlencode($item->alamat_toko ?? '');
                                    @endphp
                                    <a href="{{ $linkMaps }}" target="_blank" class="action-link" style="margin-top: 8px;"><i class="fa-solid fa-location-dot"></i> Maps Toko</a>
                                @endif
                            </div>
                        </div>
                    @endforeach

                    <div style="background: #f0f9ff; border: 1px solid #bae6fd; padding: 12px 14px; border-radius: 12px; margin-top: 10px; margin-bottom: 12px;">
                        <div style="font-size: 11px; font-weight: 800; color: #0369a1; text-transform: uppercase; margin-bottom: 6px; display: flex; align-items: center; gap: 6px;">
                            <i class="fa-regular fa-calendar-check"></i> Jadwal Pemakaian Barang (WIB)
                        </div>
                        <div style="display: flex; justify-content: space-between; font-size: 12px; font-weight: 800; color: #0f172a; flex-wrap: wrap; gap: 4px;">
                            <span>Ambil: <strong style="color: #0284c7;">{{ $waktuMulai->format('d M Y (H:i)') }}</strong></span>
                            <span>s/d</span>
                            <span>Kembali: <strong style="color: #0ea5e9;">{{ $waktuKembali->format('d M Y (H:i)') }}</strong></span>
                        </div>
                    </div>

                    <div style="display: flex; justify-content: flex-end; margin-top: 5px;">
                        <a href="{{ $linkWa }}" target="_blank" class="action-link wa-link" style="margin-top: 0; width: 100%; justify-content: center;"><i class="fa-brands fa-whatsapp text-sm"></i> Chat WhatsApp Toko</a>
                    </div>

                    <!-- BAGIAN YANG DIREVISI: Mengubah "Total Bayar Web" menjadi "Total Tagihan (COD/QRIS)" -->
                    <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #e0f2fe; display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <span style="font-size: 11px; color: #64748b; font-weight: 700; display: block;">Pengiriman: {{ strtoupper($order->shipping_method) }} (Rp {{ number_format($order->shipping_fee, 0, ',', '.') }})</span>
                            <span style="font-size: 13px; font-weight: 900; color: #0f172a; margin-top: 2px; display: block;">Total Tagihan <span style="color: #0284c7;">({{ $order->payment_method }})</span></span>
                        </div>
                        <div style="text-align: right;">
                            <span style="font-size: 20px; font-weight: 900; color: #0ea5e9;">Rp {{ number_format($order->total_price, 0, ',', '.') }}</span>
                            @if($order->payment_method === 'COD')
                                <span style="display: block; font-size: 10px; color: #ef4444; font-weight: 700; margin-top: 2px;">*Bayar pas ketemu</span>
                            @endif
                        </div>
                    </div>
                </div>

                @if($order->status !== 'Selesai' && $order->status !== 'Dibatalkan')
                    <form action="{{ route('customer.pesanan.selesai', $order->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin barang sudah diterima dengan baik dan ingin menyelesaikan pesanan ini?');">
                        @csrf
                        <button type="submit" class="btn-selesai" title="Klik jika masa sewa selesai dan barang sudah dikembalikan">
                            <i class="fa-solid fa-check-double"></i> Selesaikan Pesanan & Lepas Dana
                        </button>
                    </form>
                @else
                    <div style="background: #f0f9ff; padding: 14px; text-align: center; font-size: 12px; font-weight: 900; color: #94a3b8; border-top: 1px solid #e0f2fe;">
                        @if($order->status === 'Selesai')
                            <span style="color: #0ea5e9;"><i class="fa-solid fa-shield-check"></i> Transaksi Selesai</span>
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