@extends('layouts.app')

@section('content')
<style>
    nav, header, footer { display: none !important; }
    body { background-color: #f8fafc; font-family: 'Segoe UI', Tahoma, sans-serif; padding-top: 15px; padding-bottom: 90px;}
    .lokasi-container { max-width: 600px; margin: 0 auto; padding: 0 15px;}
    
    .clean-card { background: white; border-radius: 16px; padding: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.04); border: 1px solid #e0f2fe; }
    
    .btn-gps { width: 100%; background: linear-gradient(135deg, #38bdf8, #0ea5e9); color: white; border: none; padding: 14px; border-radius: 12px; font-size: 14px; font-weight: 800; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; transition: 0.3s; box-shadow: 0 0 15px rgba(14,165,233,0.4); margin-bottom: 20px; }
    .btn-gps:hover { background: linear-gradient(135deg, #0ea5e9, #0284c7); box-shadow: 0 0 20px rgba(14,165,233,0.6); }

    .btn-simpan { width: 100%; background: #0284c7; color: white; border: none; padding: 14px; border-radius: 12px; font-size: 14px; font-weight: 800; cursor: pointer; transition: 0.3s; margin-top: 10px; }
    .btn-simpan:hover { background: #0369a1; }
    .btn-simpan:disabled { background: #94a3b8; cursor: not-allowed; box-shadow: none; }
</style>

<div class="lokasi-container">
    <div style="font-size: 18px; font-weight: 900; color: #0ea5e9; display: flex; align-items: center; gap: 12px; margin-bottom: 20px;">
        <a href="{{ route('customer.home') }}" style="text-decoration: none; color: #0284c7; font-size: 20px; transition: 0.2s;">←</a> 
        <span>Atur Titik Lokasi Anda</span>
    </div>

    <div class="clean-card">
        <div style="text-align: center; margin-bottom: 20px;">
            <div style="width: 80px; height: 80px; background: #e0f2fe; color: #0ea5e9; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 30px; margin: 0 auto 15px auto; box-shadow: 0 0 15px rgba(14,165,233,0.2);">
                <i class="fa-solid fa-map-location-dot"></i>
            </div>
            <h2 style="font-weight: 900; color: #0f172a; font-size: 16px;">Cari Barang Terdekat</h2>
            <p style="font-size: 13px; color: #64748b; margin-top: 6px;">Izinkan akses GPS agar Rentify dapat menampilkan barang sewaan di radius 50 KM dari tempat Anda.</p>
        </div>

        <button type="button" class="btn-gps" onclick="dapatkanLokasi()">
            <i class="fa-solid fa-crosshairs"></i> Gunakan Titik GPS Saya Saat Ini
        </button>

        <div id="status-gps" style="text-align: center; font-size: 12px; font-weight: 800; color: #0ea5e9; margin-bottom: 15px;"></div>

        <form action="{{ route('customer.lokasi.store') }}" method="POST">
            @csrf
            <!-- Input yang akan terisi otomatis oleh GPS -->
            <label style="display: block; font-size: 12px; font-weight: 800; color: #0284c7; margin-bottom: 6px;">Detail Alamat Lengkap</label>
            <textarea name="alamat_lengkap" id="input_alamat" rows="3" required readonly placeholder="Klik tombol GPS di atas untuk mengisi alamat otomatis..." style="width: 100%; border: 1.5px solid #bae6fd; border-radius: 10px; padding: 12px; font-size: 13px; font-weight: 700; color: #0f172a; outline: none; background: #f8fafc; margin-bottom: 15px;"></textarea>

            <input type="hidden" name="latitude" id="input_lat">
            <input type="hidden" name="longitude" id="input_lon">

            <button type="submit" id="btn-submit" class="btn-simpan" disabled>Simpan Lokasi Saya</button>
        </form>
    </div>
</div>

<script>
    async function dapatkanLokasi() {
        const statusText = document.getElementById('status-gps');
        const btnSubmit = document.getElementById('btn-submit');
        
        statusText.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Sedang mengunci lokasi Anda...';
        
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(async (pos) => {
                const lat = pos.coords.latitude;
                const lon = pos.coords.longitude;
                
                document.getElementById('input_lat').value = lat;
                document.getElementById('input_lon').value = lon;

                try { 
                    const response = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lon}`);
                    const data = await response.json();
                    if(data.display_name) {
                        document.getElementById('input_alamat').value = data.display_name;
                    }
                } catch(e) {
                    document.getElementById('input_alamat').value = "Alamat titik kordinat: " + lat + ", " + lon;
                }
                
                statusText.innerHTML = "✨ Lokasi berhasil dikunci!";
                btnSubmit.disabled = false;
                btnSubmit.style.background = "linear-gradient(135deg, #38bdf8, #0ea5e9)";
                btnSubmit.style.boxShadow = "0 0 15px rgba(14,165,233,0.4)";
                btnSubmit.style.cursor = "pointer";

            }, () => { 
                statusText.innerHTML = "GPS gagal diakses. Pastikan pengaturan lokasi HP Anda menyala.";
                statusText.style.color = "#ef4444";
            });
        } else {
            statusText.innerHTML = "Browser Anda tidak mendukung fitur GPS.";
            statusText.style.color = "#ef4444";
        }
    }
</script>
@endsection