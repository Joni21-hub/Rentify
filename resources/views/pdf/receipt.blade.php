<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <style>
    body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; color: #0D1B3E; }
    .header { background: #0D1B3E; color: white; padding: 20px; text-align: center; }
    .header h1 { font-size: 28px; margin: 0; letter-spacing: -0.5px; }
    .header h1 span { color: #5C9EE8; }
    .booking-code { background: #EEF4FF; border: 2px solid #1E4DAA;
      border-radius: 8px; padding: 10px; text-align: center; margin: 15px 0;
      font-size: 18px; font-weight: bold; color: #1E4DAA; letter-spacing: 2px; }
    .section { margin: 12px 0; }
    .section-title { font-weight: bold; font-size: 12px; color: #1E4DAA;
      border-bottom: 1px solid #C5D8F5; padding-bottom: 4px; margin-bottom: 8px;
      text-transform: uppercase; letter-spacing: 0.05em; }
    table.items { width: 100%; border-collapse: collapse; }
    table.items th { background: #1E4DAA; color: white; padding: 6px 8px; font-size: 10px; }
    table.items td { padding: 5px 8px; border-bottom: 1px solid #eee; }
    .total-row { font-weight: bold; font-size: 13px; }
    .status-badge { display: inline-block; padding: 3px 12px; border-radius: 12px;
      font-size: 10px; font-weight: bold; }
    .status-lunas { background: #D0F5E8; color: #0A5F3C; }
    .footer { text-align: center; margin-top: 20px; color: #888; font-size: 9px;
      border-top: 1px solid #eee; padding-top: 10px; }
  </style>
</head>
<body>

<div class="header">
  <h1><span>R</span>entify</h1>
  <p>Rental Marketplace — Tanda Terima Digital</p>
</div>

<div class="booking-code">{{ $penyewaan->kode_booking }}</div>

<div class="section">
  <div class="section-title">Data Penyewa</div>
  <p><strong>Nama:</strong> {{ $penyewaan->customer->user->nama }}</p>
  <p><strong>Email:</strong> {{ $penyewaan->customer->user->email }}</p>
  <p><strong>Telepon:</strong> {{ $penyewaan->customer->telepon }}</p>
</div>

<div class="section">
  <div class="section-title">Detail Penyewaan</div>
  <p><strong>Tanggal:</strong>
    {{ $penyewaan->tanggal_mulai->format('d M Y') }} –
    {{ $penyewaan->tanggal_selesai->format('d M Y') }}</p>
  <p><strong>Pengambilan:</strong>
    {{ $penyewaan->metode_pengambilan === 'ambil_cabang' ? 'Ambil di Cabang' : 'Antar ke Alamat' }}
  </p>
  @if($penyewaan->cabang)
  <p><strong>Cabang:</strong> {{ $penyewaan->cabang->nama }}</p>
  @endif
</div>

<div class="section">
  <div class="section-title">Rincian Barang</div>
  <table class="items">
    <thead><tr>
      <th>Barang</th><th>Jml</th><th>Durasi</th><th>Harga/Hari</th><th>Subtotal</th>
    </tr></thead>
    <tbody>
      @foreach($penyewaan->details as $detail)
      <tr>
        <td>{{ $detail->barang->nama }}</td>
        <td>{{ $detail->jumlah }}</td>
        <td>{{ $detail->durasi }} hari</td>
        <td>Rp {{ number_format($detail->harga_per_hari, 0, ',', '.') }}</td>
        <td>Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
      </tr>
      @endforeach
    </tbody>
    <tfoot>
      <tr class="total-row">
        <td colspan="4">Biaya Sewa</td>
        <td>Rp {{ number_format($penyewaan->total_biaya, 0, ',', '.') }}</td>
      </tr>
      <tr>
        <td colspan="4">Deposit (Jaminan KTP/SIM)</td>
        <td>Rp {{ number_format($penyewaan->total_deposit, 0, ',', '.') }}</td>
      </tr>
      <tr class="total-row" style="font-size:14px;background:#EEF4FF">
        <td colspan="4">TOTAL</td>
        <td>Rp {{ number_format($penyewaan->total_biaya + $penyewaan->total_deposit, 0, ',', '.') }}</td>
      </tr>
    </tfoot>
  </table>
</div>

<div class="section">
  <strong>Status Pembayaran:</strong>
  <span class="status-badge status-lunas">
    {{ strtoupper($penyewaan->pembayaran?->status ?? 'Belum Bayar') }}
  </span>
</div>

<div class="footer">
  <p>RENTIFY · Blang Pulo, Lhokseumawe · 083183494835 · @rentify.id_</p>
  <p>Jaminan: KTP/SIM · Dicetak: {{ now()->format('d M Y H:i') }}</p>
</div>

</body>
</html>