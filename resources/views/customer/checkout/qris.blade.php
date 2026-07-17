@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
    /* Sembunyikan navbar dan footer */
    nav, header, footer, .navbar, .header, .top-bar, .footer, #footer { display: none !important; }
    
    body { background-color: #f0f8ff; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
    .qris-container { max-width: 500px; margin: 40px auto; padding: 0 15px; text-align: center; }
    
    .rentify-card { background: white; padding: 30px 20px; box-shadow: 0 10px 25px rgba(2, 132, 199, 0.1); border-radius: 16px; border-top: 5px solid #0ea5e9; }
    
    .rentify-logo { font-size: 24px; font-weight: 800; color: #0284c7; margin-bottom: 5px; }
    .rentify-logo span { color: #38bdf8; }
    
    .invoice-badge { display: inline-block; background: #e0f2fe; color: #0369a1; padding: 5px 15px; border-radius: 20px; font-size: 13px; font-weight: 600; margin-bottom: 25px; }
    
    .instruction-text { font-size: 14px; color: #64748b; margin-bottom: 10px; font-weight: 500; }
    .total-amount { font-size: 28px; font-weight: 800; color: #0f172a; margin-bottom: 20px; }
    
    .qris-wrapper { background: #f8fafc; padding: 20px; border-radius: 12px; border: 1px dashed #cbd5e1; display: inline-block; width: 100%; max-width: 300px; margin: 0 auto 15px auto; }
    .qris-img { width: 100%; height: auto; aspect-ratio: 1/1; object-fit: contain; border-radius: 8px; }
    
    .supported-payments { font-size: 11px; color: #94a3b8; margin-top: 10px; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; }
    
    /* Tombol Unduh/Simpan QRIS Baru */
    .btn-download { display: inline-flex; align-items: center; justify-content: center; gap: 8px; width: 100%; background: #f0f9ff; color: #0284c7; border: 2px solid #bae6fd; padding: 12px; font-size: 15px; font-weight: 700; border-radius: 12px; cursor: pointer; text-decoration: none; transition: all 0.2s; margin-top: 5px; margin-bottom: 5px; box-sizing: border-box; }
    .btn-download:hover { background: #e0f2fe; border-color: #7dd3fc; transform: translateY(-1px); }
    
    .helper-text { font-size: 11px; color: #64748b; margin-bottom: 20px; margin-top: 4px; }
    
    .btn-selesai { display: block; background: linear-gradient(135deg, #38bdf8, #0284c7); color: white; border: none; padding: 15px; font-size: 16px; font-weight: 700; border-radius: 12px; cursor: pointer; text-decoration: none; transition: transform 0.2s, box-shadow 0.2s; margin-top: 10px; text-align: center; }
    .btn-selesai:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(2, 132, 199, 0.3); color: white; }
    
    .warning-text { font-size: 12px; color: #ef4444; margin-top: 15px; }
</style>

<div class="qris-container">
    <div class="rentify-card">
        <div class="rentify-logo">Renti<span>fy</span></div>
        <div class="invoice-badge">No. Pesanan: {{ $id }}</div>
        
        <div class="instruction-text">Silakan scan QRIS di bawah ini untuk membayar:</div>
        
        <div class="total-amount">Rp {{ number_format($total ?? 0, 0, ',', '.') }}</div>
        
        <div class="qris-wrapper">
            <img src="{{ asset('https://res.cloudinary.com/fnf8f1pm/image/upload/v1784278754/Qris_kqb63q.jpg') }}" alt="Scan QRIS" class="qris-img">
            
            <div class="supported-payments">BANK/EWALLET</div>
        </div>

        <a href="{{ asset('images/qris.png') }}" download="QRIS-Rentify-{{ $id }}.png" class="btn-download">
            <i class="fa-solid fa-download"></i> Simpan Gambar QRIS
        </a>
        <p class="helper-text">*Atau ketuk dan tahan lama pada gambar QRIS untuk menyimpan langsung</p>
        
        <a href="{{ route('customer.struk', $id) }}" class="btn-selesai">Cek status pembayaran</a>
        
        <div class="warning-text">*Harap selesaikan pembayaran dalam waktu 15 menit agar pesanan tidak otomatis dibatalkan.</div>
    </div>
</div>
@endsection