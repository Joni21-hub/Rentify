@extends('layouts.app')

@section('content')
<style>
    nav, header, footer { display: none !important; }
    body { 
        background: radial-gradient(circle at center, #fff9ef 0%, #bad6eb 100%) fixed !important; 
        font-family: 'Plus Jakarta Sans', sans-serif; 
        padding-top: 15px; 
        padding-bottom: 90px;
    }
    .lokasi-container { max-width: 600px; margin: 0 auto; padding: 0 15px; }
    
    .clean-card { 
        background: rgba(255, 255, 255, 0.94); 
        backdrop-filter: blur(16px); 
        -webkit-backdrop-filter: blur(16px); 
        border-radius: 28px; 
        padding: 24px; 
        box-shadow: 0 12px 30px -6px rgba(14, 165, 233, 0.12), 0 4px 10px -2px rgba(0, 0, 0, 0.04); 
        border: 1px solid rgba(255, 255, 255, 0.95); 
    }
    
    .btn-gps { 
        width: 100%; 
        background: linear-gradient(135deg, #38bdf8, #0284c7); 
        color: white; 
        border: none; 
        padding: 13px 18px; 
        border-radius: 16px; 
        font-size: 12px; 
        font-weight: 900; 
        text-transform: uppercase; 
        letter-spacing: 0.05em; 
        cursor: pointer; 
        display: flex; 
        align-items: center; 
        justify-content: center; 
        gap: 8px; 
        transition: all 0.25s ease; 
        box-shadow: 0 4px 15px rgba(14,165,233,0.35); 
        margin-bottom: 14px; 
    }
    .btn-gps:hover { 
        background: linear-gradient(135deg, #0ea5e9, #0369a1); 
        box-shadow: 0 6px 20px rgba(14,165,233,0.5); 
        transform: translateY(-1px);
    }
    .btn-gps:active {
        transform: scale(0.98);
    }

    .btn-simpan { 
        width: 100%; 
        background: linear-gradient(135deg, #38bdf8, #0284c7); 
        color: white; 
        border: none; 
        padding: 14px; 
        border-radius: 16px; 
        font-size: 12.5px; 
        font-weight: 900; 
        text-transform: uppercase; 
        letter-spacing: 0.05em; 
        cursor: pointer; 
        transition: all 0.25s ease; 
        margin-top: 14px; 
        box-shadow: 0 4px 15px rgba(14,165,233,0.35);
    }
    .btn-simpan:hover:not(:disabled) { 
        background: linear-gradient(135deg, #0ea5e9, #0369a1); 
        box-shadow: 0 6px 20px rgba(14,165,233,0.5); 
        transform: translateY(-1px);
    }
    .btn-simpan:active:not(:disabled) {
        transform: scale(0.98);
    }
    .btn-simpan:disabled { 
        background: #cbd5e1 !important; 
        color: #94a3b8 !important; 
        cursor: not-allowed; 
        box-shadow: none !important; 
    }

    /* ===== RADAR ANIMATION SECTION ===== */
    .radar-visual-box {
        position: relative;
        width: 100%;
        height: 230px;
        background: radial-gradient(circle at center, #f0f9ff 0%, #e0f2fe 55%, #c8e7fa 100%);
        border: 1.5px solid #bae6fd;
        border-radius: 24px;
        overflow: hidden;
        margin-bottom: 20px;
        box-shadow: inset 0 2px 10px rgba(14, 165, 233, 0.08);
    }

    /* Subtle grid lines */
    .radar-grid-bg {
        position: absolute;
        inset: 0;
        background-image: 
            linear-gradient(to right, rgba(2, 132, 199, 0.06) 1px, transparent 1px),
            linear-gradient(to bottom, rgba(2, 132, 199, 0.06) 1px, transparent 1px);
        background-size: 24px 24px;
        pointer-events: none;
    }

    /* Fixed concentric rings */
    .ring-fixed {
        position: absolute;
        top: 50%;
        left: 50%;
        border-radius: 50%;
        transform: translate(-50%, -50%);
        pointer-events: none;
    }
    .ring-10 {
        width: 25%;
        height: 25%;
        border: 1px dashed rgba(2, 132, 199, 0.28);
    }
    .ring-25 {
        width: 55%;
        height: 55%;
        border: 1px dashed rgba(2, 132, 199, 0.32);
    }
    .ring-50 {
        width: 88%;
        height: 88%;
        border: 1px dashed rgba(2, 132, 199, 0.4);
    }

    .ring-label {
        position: absolute;
        top: 50%;
        left: 50%;
        font-size: 9px;
        font-weight: 800;
        color: #0284c7;
        opacity: 0.6;
        pointer-events: none;
        transform: translate(-50%, -50%);
    }
    .label-10 { margin-top: -16%; }
    .label-25 { margin-top: -31%; }
    .label-50 { margin-top: -47%; }

    /* Continuous Rotating Radar Sweep Beam */
    .radar-sweep {
        position: absolute;
        top: 50%;
        left: 50%;
        width: 90%;
        height: 90%;
        margin-top: -45%;
        margin-left: -45%;
        border-radius: 50%;
        background: conic-gradient(from 0deg at 50% 50%, rgba(14, 165, 233, 0.22) 0deg, rgba(14, 165, 233, 0.06) 38deg, transparent 75deg, transparent 360deg);
        animation: radarSpin 4s linear infinite;
        pointer-events: none;
        z-index: 2;
    }
    @keyframes radarSpin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }

    /* Center user pulsing ring */
    .pulse-wave {
        position: absolute;
        top: 50%;
        left: 50%;
        border-radius: 50%;
        border: 2px solid #0284c7;
        transform: translate(-50%, -50%);
        animation: pulseRipple 2.4s cubic-bezier(0.2, 0.7, 0.3, 1) infinite;
        pointer-events: none;
        z-index: 3;
    }
    .pulse-wave.w2 {
        animation-delay: 1.2s;
    }
    @keyframes pulseRipple {
        0% { width: 14px; height: 14px; opacity: 0.9; }
        100% { width: 75px; height: 75px; opacity: 0; }
    }

    /* Center user pin */
    .center-pin {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 32px;
        height: 32px;
        background: linear-gradient(135deg, #0284c7, #0369a1);
        border: 2.5px solid #ffffff;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 13px;
        box-shadow: 0 4px 14px rgba(2, 132, 199, 0.45);
        z-index: 10;
        cursor: default;
    }

    /* Dynamic Interactive Active Coverage Disc */
    .active-radius-disc {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        border-radius: 50%;
        background: radial-gradient(circle, rgba(14, 165, 233, 0.28) 0%, rgba(14, 165, 233, 0.12) 65%, rgba(14, 165, 233, 0.03) 100%);
        border: 2px solid #0284c7;
        box-shadow: 0 0 20px rgba(14, 165, 233, 0.35), inset 0 0 15px rgba(14, 165, 233, 0.12);
        transition: width 0.25s cubic-bezier(0.34, 1.56, 0.64, 1), height 0.25s cubic-bezier(0.34, 1.56, 0.64, 1);
        pointer-events: none;
        z-index: 4;
    }

    /* Item Pins Inside Radar */
    .item-radar-pin {
        position: absolute;
        z-index: 6;
        display: flex;
        align-items: center;
        gap: 4px;
        background: rgba(255, 255, 255, 0.95);
        padding: 3px 8px;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        border: 1px solid #bae6fd;
        font-size: 10px;
        font-weight: 800;
        color: #0f172a;
        transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        pointer-events: none;
        user-select: none;
    }
    .item-radar-pin.is-active {
        transform: scale(1.06);
        background: #ffffff;
        border: 1.5px solid #0284c7;
        box-shadow: 0 4px 12px rgba(14, 165, 233, 0.35);
        opacity: 1;
    }
    .item-radar-pin.is-inactive {
        opacity: 0.38;
        transform: scale(0.9);
        filter: grayscale(80%);
        background: rgba(241, 245, 249, 0.85);
        border: 1px solid #cbd5e1;
    }

    /* Radius Control Slider & Badges */
    .radius-control-card {
        background: #f8fafc;
        border: 1.5px solid #e2e8f0;
        border-radius: 20px;
        padding: 16px 18px;
        margin-bottom: 18px;
    }

    .radius-badge-box {
        display: inline-flex;
        align-items: baseline;
        gap: 2px;
        background: linear-gradient(135deg, #0284c7, #0369a1);
        color: white;
        padding: 4px 14px;
        border-radius: 20px;
        box-shadow: 0 3px 10px rgba(14, 165, 233, 0.3);
        transition: transform 0.2s ease;
    }
    .radius-badge-box.bounce {
        transform: scale(1.15);
    }

    /* Custom Sleek Slider */
    .custom-range {
        -webkit-appearance: none;
        appearance: none;
        width: 100%;
        height: 8px;
        border-radius: 999px;
        outline: none;
        background: linear-gradient(to right, #0ea5e9 0%, #0ea5e9 var(--track-fill, 100%), #e2e8f0 var(--track-fill, 100%), #e2e8f0 100%);
        transition: background 0.1s ease;
        cursor: pointer;
        margin: 14px 0 8px 0;
    }
    .custom-range::-webkit-slider-thumb {
        -webkit-appearance: none;
        appearance: none;
        width: 24px;
        height: 24px;
        border-radius: 50%;
        background: #ffffff;
        border: 3.5px solid #0284c7;
        box-shadow: 0 2px 10px rgba(14, 165, 233, 0.5);
        cursor: pointer;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }
    .custom-range::-webkit-slider-thumb:hover {
        transform: scale(1.18);
        box-shadow: 0 4px 14px rgba(14, 165, 233, 0.7);
    }
    .custom-range::-moz-range-thumb {
        width: 24px;
        height: 24px;
        border-radius: 50%;
        background: #ffffff;
        border: 3.5px solid #0284c7;
        box-shadow: 0 2px 10px rgba(14, 165, 233, 0.5);
        cursor: pointer;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }

    /* Quick Preset Buttons */
    .preset-pill {
        padding: 5px 12px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 800;
        cursor: pointer;
        border: 1px solid #e2e8f0;
        background: #ffffff;
        color: #475569;
        transition: all 0.2s ease;
    }
    .preset-pill:hover {
        border-color: #bae6fd;
        background: #f0f9ff;
        color: #0284c7;
        transform: translateY(-1px);
    }
    .preset-pill.active {
        background: #0284c7;
        color: #ffffff;
        border-color: #0284c7;
        box-shadow: 0 2px 8px rgba(14, 165, 233, 0.35);
    }
</style>

<div class="lokasi-container">
    <div style="font-size: 18px; font-weight: 900; color: #0f172a; display: flex; align-items: center; gap: 12px; margin-bottom: 18px;">
        <a href="{{ route('customer.dashboard') }}" style="width: 36px; height: 36px; border-radius: 12px; background: rgba(255,255,255,0.88); border: 1px solid rgba(255,255,255,0.9); display: flex; align-items: center; justify-content: center; color: #475569; text-decoration: none; box-shadow: 0 2px 6px rgba(0,0,0,0.05); transition: 0.2s;">
            <i class="fa-solid fa-arrow-left" style="font-size: 13px;"></i>
        </a> 
        <span>Atur Titik & Jarak Lokasi</span>
    </div>

    <div class="clean-card">
        <!-- HEADER KARTU -->
        <div style="text-align: center; margin-bottom: 18px;">
            <div style="width: 68px; height: 68px; background: #e0f2fe; color: #0284c7; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 26px; margin: 0 auto 12px auto; box-shadow: 0 4px 15px rgba(14,165,233,0.22);">
                <i class="fa-solid fa-map-location-dot"></i>
            </div>
            <h2 style="font-weight: 900; color: #0f172a; font-size: 16.5px;">Cari Rental Terdekat</h2>
            <p style="font-size: 12.5px; color: #64748b; margin-top: 5px; line-height: 1.45;">
                Rentify akan menyaring etalase barang sewaan dalam radius <strong id="text-radius-desc" style="color: #0284c7;">{{ $radius ?? 50 }} KM</strong> dari lokasi Anda.
            </p>
        </div>

        <!-- RADAR VISUAL ANIMATION -->
        <div class="radar-visual-box" id="radar-container" title="Visual Interaktif Radius Jarak">
            <!-- Grid pattern -->
            <div class="radar-grid-bg"></div>

            <!-- Fixed concentric rings -->
            <div class="ring-fixed ring-10"></div>
            <span class="ring-label label-10">10 km</span>

            <div class="ring-fixed ring-25"></div>
            <span class="ring-label label-25">25 km</span>

            <div class="ring-fixed ring-50"></div>
            <span class="ring-label label-50">50 km</span>

            <!-- Continuous Sweep Beam -->
            <div class="radar-sweep"></div>

            <!-- Animated Dynamic Active Disc (Coverage) -->
            <div class="active-radius-disc" id="radar-active-disc"></div>

            <!-- Pulsing Rings in Center -->
            <div class="pulse-wave"></div>
            <div class="pulse-wave w2"></div>

            <!-- Center User Pin -->
            <div class="center-pin" title="Posisi Anda">
                <i class="fa-solid fa-location-dot"></i>
            </div>

            <!-- Simulated Rental Item Pins with Live Highlight Animation -->
            <!-- 1. Tenda Camping: 8 KM (dekat) -->
            <div class="item-radar-pin" id="pin-item-1" data-dist="8" style="top: 36%; left: 56%;">
                <i class="fa-solid fa-campground text-amber-500"></i>
                <span>Tenda 8km</span>
            </div>

            <!-- 2. Kamera Mirrorless: 18 KM (menengah) -->
            <div class="item-radar-pin" id="pin-item-2" data-dist="18" style="top: 66%; left: 32%;">
                <i class="fa-solid fa-camera text-sky-500"></i>
                <span>Kamera 18km</span>
            </div>

            <!-- 3. Motor / Vespa: 32 KM (luas) -->
            <div class="item-radar-pin" id="pin-item-3" data-dist="32" style="top: 72%; left: 68%;">
                <i class="fa-solid fa-motorcycle text-emerald-500"></i>
                <span>Vespa 32km</span>
            </div>

            <!-- 4. Sound Equipment: 46 KM (maksimal) -->
            <div class="item-radar-pin" id="pin-item-4" data-dist="46" style="top: 22%; left: 24%;">
                <i class="fa-solid fa-guitar text-rose-500"></i>
                <span>Sound 46km</span>
            </div>
        </div>

        <!-- RADIUS SELECTOR SLIDER & PRESETS -->
        <div class="radius-control-card">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 4px;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <div style="width: 28px; height: 28px; border-radius: 8px; background: #e0f2fe; color: #0284c7; display: flex; align-items: center; justify-content: center; font-size: 12px;">
                        <i class="fa-solid fa-compass"></i>
                    </div>
                    <span style="font-size: 13px; font-weight: 800; color: #0f172a;">Jarak Radius Rental</span>
                </div>
                <div class="radius-badge-box" id="radius-badge-container">
                    <span id="badge-km-val" style="font-size: 16px; font-weight: 900; line-height: 1;">{{ $radius ?? 50 }}</span>
                    <span style="font-size: 11px; font-weight: 800; opacity: 0.9;">KM</span>
                </div>
            </div>

            <!-- Custom Range Slider -->
            <input type="range" 
                   id="input_radius" 
                   min="5" 
                   max="50" 
                   step="1" 
                   value="{{ $radius ?? 50 }}" 
                   class="custom-range"
                   aria-label="Pilih radius jangkauan dari 5 sampai 50 kilometer">

            <div style="display: flex; justify-content: space-between; font-size: 10.5px; font-weight: 700; color: #94a3b8; margin-bottom: 12px; padding: 0 2px;">
                <span>Min: 5 KM</span>
                <span>25 KM</span>
                <span>Maks: 50 KM</span>
            </div>

            <!-- Quick Preset Chips -->
            <div style="display: flex; align-items: center; justify-content: space-between; gap: 6px;">
                <button type="button" class="preset-pill" onclick="setRadiusPreset(5)">5 KM</button>
                <button type="button" class="preset-pill" onclick="setRadiusPreset(10)">10 KM</button>
                <button type="button" class="preset-pill" onclick="setRadiusPreset(20)">20 KM</button>
                <button type="button" class="preset-pill" onclick="setRadiusPreset(35)">35 KM</button>
                <button type="button" class="preset-pill" onclick="setRadiusPreset(50)">50 KM</button>
            </div>
        </div>

        <!-- BUTTON DETEKSI GPS -->
        <button type="button" class="btn-gps" onclick="dapatkanLokasi()">
            <i class="fa-solid fa-crosshairs"></i> Gunakan Titik GPS Saat Ini
        </button>

        <div id="status-gps" style="text-align: center; font-size: 12px; font-weight: 800; color: #0284c7; margin-bottom: 14px;">
            @if(!empty($lat) && !empty($lon))
                <i class="fa-solid fa-circle-check text-emerald-500"></i> Titik koordinat sebelumnya sudah tersimpan.
            @endif
        </div>

        <!-- FORM SIMPAN TITIK & RADIUS -->
        <form action="{{ route('customer.lokasi.store') }}" method="POST" id="form-lokasi">
            @csrf
            <label style="display: block; font-size: 12px; font-weight: 800; color: #0284c7; margin-bottom: 6px;">
                <i class="fa-solid fa-location-dot mr-1"></i> Detail Alamat Lengkap
            </label>
            <textarea name="alamat_lengkap" 
                      id="input_alamat" 
                      rows="3" 
                      required 
                      readonly 
                      placeholder="Klik tombol GPS di atas untuk mendeteksi alamat otomatis..." 
                      style="width: 100%; border: 1.5px solid #bae6fd; border-radius: 16px; padding: 12px; font-size: 13px; font-weight: 700; color: #0f172a; outline: none; background: rgba(248, 250, 252, 0.85); margin-bottom: 4px;" 
                      class="rentify-input">{{ $alamat ?? '' }}</textarea>

            <input type="hidden" name="latitude" id="input_lat" value="{{ $lat ?? '' }}">
            <input type="hidden" name="longitude" id="input_lon" value="{{ $lon ?? '' }}">
            <input type="hidden" name="radius" id="form_input_radius" value="{{ $radius ?? 50 }}">

            <button type="submit" 
                    id="btn-submit" 
                    class="btn-simpan" 
                    {{ (!empty($lat) && !empty($lon) && !empty($alamat)) ? '' : 'disabled' }}>
                Simpan Titik Lokasi & Jarak ({{ $radius ?? 50 }} KM)
            </button>
        </form>
    </div>
</div>

<script>
    // State Radius
    let currentRadius = parseInt("{{ $radius ?? 50 }}", 10) || 50;
    const slider = document.getElementById('input_radius');
    const badgeVal = document.getElementById('badge-km-val');
    const badgeContainer = document.getElementById('radius-badge-container');
    const textRadiusDesc = document.getElementById('text-radius-desc');
    const formInputRadius = document.getElementById('form_input_radius');
    const radarActiveDisc = document.getElementById('radar-active-disc');
    const btnSubmit = document.getElementById('btn-submit');

    // Update Visual Radar & Slider
    function updateRadiusUI(radiusKm, triggerBounce = true) {
        currentRadius = Math.max(5, Math.min(50, parseInt(radiusKm, 10) || 50));
        
        // Update input slider & hidden input
        slider.value = currentRadius;
        formInputRadius.value = currentRadius;
        
        // Update track fill gradient
        const percent = ((currentRadius - 5) / (50 - 5)) * 100;
        slider.style.setProperty('--track-fill', `${percent}%`);

        // Update text badge
        badgeVal.textContent = currentRadius;
        textRadiusDesc.textContent = `${currentRadius} KM`;

        // Update submit button text if not completely disabled or if ready
        if (btnSubmit) {
            btnSubmit.textContent = `Simpan Titik Lokasi & Jarak (${currentRadius} KM)`;
        }

        // Animated bounce effect on badge
        if (triggerBounce && badgeContainer) {
            badgeContainer.classList.remove('bounce');
            void badgeContainer.offsetWidth; // trigger reflow
            badgeContainer.classList.add('bounce');
        }

        // Update active preset button highlight
        document.querySelectorAll('.preset-pill').forEach(btn => {
            const btnVal = parseInt(btn.textContent, 10);
            if (btnVal === currentRadius) {
                btn.classList.add('active');
            } else {
                btn.classList.remove('active');
            }
        });

        // Dynamic Radar Disc Resize (Animation)
        // 5 km -> 18% width/height
        // 50 km -> 88% width/height
        const minPct = 18;
        const maxPct = 88;
        const discSizePct = minPct + ((currentRadius - 5) / (50 - 5)) * (maxPct - minPct);
        
        radarActiveDisc.style.width = `${discSizePct}%`;
        radarActiveDisc.style.height = `${discSizePct}%`;

        // Update Simulated Item Pins inside/outside the current radius
        document.querySelectorAll('.item-radar-pin').forEach(pin => {
            const pinDist = parseFloat(pin.getAttribute('data-dist')) || 0;
            if (currentRadius >= pinDist) {
                pin.classList.add('is-active');
                pin.classList.remove('is-inactive');
            } else {
                pin.classList.remove('is-active');
                pin.classList.add('is-inactive');
            }
        });
    }

    // Set Preset from quick buttons
    function setRadiusPreset(km) {
        updateRadiusUI(km, true);
    }

    // Slider Event Listeners
    slider.addEventListener('input', (e) => {
        updateRadiusUI(e.target.value, false);
    });

    slider.addEventListener('change', (e) => {
        updateRadiusUI(e.target.value, true);
    });

    // Initialize UI on load
    document.addEventListener('DOMContentLoaded', () => {
        updateRadiusUI(currentRadius, false);
    });

    // Dapatkan Lokasi GPS
    async function dapatkanLokasi() {
        const statusText = document.getElementById('status-gps');
        
        statusText.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Sedang mengunci lokasi Anda...';
        statusText.style.color = "#0284c7";
        
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
                
                statusText.innerHTML = '<i class="fa-solid fa-circle-check text-emerald-500"></i> Lokasi berhasil dikunci!';
                btnSubmit.disabled = false;
                btnSubmit.style.background = "linear-gradient(135deg, #38bdf8, #0284c7)";
                btnSubmit.style.boxShadow = "0 4px 15px rgba(14,165,233,0.35)";
                btnSubmit.style.cursor = "pointer";
                btnSubmit.textContent = `Simpan Titik Lokasi & Jarak (${currentRadius} KM)`;

            }, () => { 
                statusText.innerHTML = '<i class="fa-solid fa-triangle-exclamation"></i> GPS gagal diakses. Pastikan izin lokasi browser Anda aktif.';
                statusText.style.color = "#ef4444";
            }, {
                timeout: 10000,
                enableHighAccuracy: true
            });
        } else {
            statusText.innerHTML = '<i class="fa-solid fa-triangle-exclamation"></i> Browser Anda tidak mendukung fitur GPS.';
            statusText.style.color = "#ef4444";
        }
    }
</script>
@endsection
