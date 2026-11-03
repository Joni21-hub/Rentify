# AUDIT.md — Rentify Camera & Media Fidelity Engine v2
**Fase 1: Audit dan Quality Ledger Awal**  
*Tanggal: 4 November 2026*  
*Target: Fitur Kamera & Feed Momen Rentify*

---

## 1. Ringkasan Eksekutif & Karakteristik Sistem Saat Ini

Audit ini dilakukan berdasarkan spesifikasi teknis **"RENTIFY — Camera & Media Fidelity Engine v2"** untuk memetakan seluruh perjalanan media: **Kamera → Preview → Capture → Editor → Upload → Server Processing → Storage/CDN → Feed Momen → Viewer/Playback → Download**.

Sistem saat ini adalah aplikasi web monolitik **Laravel 11 (PHP 8.2/8.3)** yang beroperasi sebagai **Progressive Web App (PWA)** dengan antarmuka kamera berbasis web standar (HTML5 `<video>`, WebRTC `getUserMedia`, MediaRecorder API, dan HTML5 2D Canvas).

### Temuan Kunci Awal:
1. **Sensor Terpotong & Dibatasi Resolusi Preview (Hulu):** Foto dijepret langsung dari frame elemen video HTML5 (`ctx.drawImage(this.videoEl)`), sehingga resolusi still photo dibatasi maksimal 1080p (1080x1920) atau 720p (720x1280), mengabaikan sensor kamera perangkat asli (12 MP, 48 MP, atau 50 MP).
2. **Generation Loss (Kompresi & Re-encode Berganda):** Foto mengalami sedikitnya 3x siklus re-encoding (WebRTC decode → Canvas JPEG 98% → Editor Canvas JPEG 100% → Server StorageService WebP 95% / Cloudinary auto).
3. **Master File Tidak Tersimpan Utuh (Melanggar Invarian 1 & 3):** Server tidak menyimpan master asli tak tersentuh. Pada disk lokal, file langsung dikonversi menjadi WebP lossy. Pada Cloudinary, file ditransformasi otomatis saat diunggah.
4. **Ketiadaan Derivative Ladder & HLS (Hilir):** Tidak ada pembuatan ladder adaptif multi-resolusi untuk foto (360px, 720px, 1280px, 2560px) maupun video (HLS 1080p, 720p, 480p, 360p). Video diunggah utuh dan diputar langsung dari raw MP4/WebM tanpa adaptive bitrate streaming.
5. **Double Loading pada Feed:** Pada feed Momen, berkas gambar resolusi penuh diunduh dua kali secara paralel per kartu (satu untuk latar belakang blur ambient, satu untuk elemen utama di tengah).

---

## 2. Quality Ledger Awal (Baseline Status Quo)

### A. Alur Media Foto (Still Photo)
*Skenario: Pengambilan foto dari kamera belakang di ponsel dengan sensor 48 MP (resolusi sensor native: 8000x6000 atau binned 4000x3000).*

| Tahap | Resolusi (px) | Format / Codec | Bitrate / Quality | Color / HDR | Orientasi / EXIF | Hash Match | Siapa yang Mengubah & Alasan |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| **1. Sensor Hardware** | 4000 x 3000 (12MP) s/d 8000x6000 (48MP) | Raw Sensor / ISP YUV | Lossless | sRGB / Display P3 / HDR | Asli dari sensor | N/A (Ground Truth) | Sensor HP & Camera ISP. |
| **2. Live Preview** | 1080 x 1920 (atau 720x1280) | WebRTC Stream (YUV420p) | ~4-6 Mbps live | sRGB (SDR) | CSS scale / transform | Berubah | `camera-engine.js:175-263` (`getUserMedia` constraint 1080x1920). Driver memotong sensor ke rasio preview. |
| **3. Capture Shutter** | 1080 x 1920 | JPEG (Canvas export) | JPEG Q=0.98 (~1.2 MB) | sRGB | Datar (Canvas pixel bitmap, EXIF hilang) | Berubah | `camera-engine.js:612-695` (`ctx.drawImage(this.videoEl)`). Resolusi sensor 48MP terbuang; EXIF & GPS hilang total. |
| **4. Editor Momen** | 1080 x 1920 | JPEG (Canvas re-encode) | JPEG Q=1.0 (~2.1 MB) | sRGB | Tidak ada metadata | Berubah | `editor-engine.js:1376-1456` (`bakePhotoWithOverlays`). Sekalipun tanpa edit/teks, di-render ulang ke canvas baru. |
| **5. Pre-Upload Payload** | 1080 x 1920 | JPEG (`moment_xxx.jpg`) | ~2.1 MB | sRGB | Tidak ada metadata | Berubah | `editor-engine.js:1533` (disusun ke FormData multipart). |
| **6. Server Storage (Lokal)** | 1080 x 1920 | WebP (Intervention GD) | WebP Q=95 (~650 KB) | sRGB | Tidak ada EXIF | Berubah | `StorageService.php:172` (`image->toWebp(95)`). File master JPEG ditimpa/dihapus; hanya WebP tersimpan. |
| **7. Server Storage (Cloudinary)** | 1080 x 1920 | WebP/JPG (f_auto, q_auto) | Variatif (~350-500 KB) | sRGB | Metadata di-strip | Berubah | `MomentController.php:660` (Cloudinary transformation `fetch_format: auto`). Master hilang. |
| **8. Feed API Response** | N/A | JSON | N/A | N/A | N/A | N/A | `MomentController.php:112` (`media_url` tunggal, tanpa dimensi `width`/`height` & blurhash). |
| **9. Feed Display** | 1080 x 1920 (ditampilkan di ~390x844 CSS px) | WebP/JPG | Sesuai URL server | sRGB | CSS object-contain | N/A | `index.blade.php:683-684, 748-750` (di-load 2x: backdrop blur3xl + foreground image). |
| **10. Fullscreen Viewer** | 1080 x 1920 | WebP/JPG | Sesuai URL server | sRGB | CSS object-contain | N/A | Memakai file feed yang sama; pinch-to-zoom pecah karena resolusi still hanya 1080p. |
| **11. Download** | 1080 x 1920 | WebP/JPG terkompresi | Sesuai file server | sRGB | Hilang metadata asli | 0% cocok dengan Master Sensor | Pengguna hanya mendapatkan file WebP 1080p hasil re-kompresi, bukan file asli kamera. |

---

### B. Alur Media Video
*Skenario: Perekaman video klip berdurasi 15 detik melalui kamera aplikasi.*

| Tahap | Resolusi (px) | Format / Codec | Bitrate / Quality | FPS | Audio | Hash Match | Siapa yang Mengubah & Alasan |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| **1. Sensor Hardware** | 1080 x 1920 s/d 4K | YUV / NV12 Hardware | Sesuai sensor | 30/60 fps | Stereo PCM 48kHz | N/A | Sensor & Encoder HP. |
| **2. MediaRecorder Capture** | 1080 x 1920 (atau 720p) | WebM/VP8/Opus atau MP4/AVC1 | Video: 6 Mbps, Audio: 128 kbps | ~30 fps | Mono/Stereo Opus/AAC | Berubah | `camera-engine.js:787-805` (bitrate dipatok 6 Mbps, format tergantung peramban browser). |
| **3. Editor Momen** | 1080 x 1920 | Asli dari MediaRecorder | 6 Mbps | ~30 fps | Audio asli kamera | Identik | `editor-engine.js:1504-1522` (video tidak di-encode ulang di client; namun teks/overlay/musik TIDAK dibake). |
| **4. Pre-Upload Payload** | 1080 x 1920 | WebM / MP4 | 6 Mbps | ~30 fps | Sesuai rekaman | Identik | `editor-engine.js:1533` (FormData biasa tanpa chunking resumable). |
| **5. Server Storage (Lokal)** | 1080 x 1920 | WebM / MP4 mentah | 6 Mbps | ~30 fps | Sesuai rekaman | Identik | `StorageService.php:59` (disimpan apa adanya via `$file->store()`). Tanpa faststart remux, tanpa HLS ladder. |
| **6. Server Storage (Cloudinary)** | 1080 x 1920 | MP4 / WebM | Variatif via Cloudinary | ~30 fps | Sesuai rekaman | Berubah | `MomentController.php:673` (`uploadLarge`). Thumbnail di-generate `c_thumb,w_400`. |
| **7. Feed Delivery** | 1080 x 1920 | Raw MP4 / WebM | 6 Mbps | ~30 fps | Sesuai rekaman | Identik | `index.blade.php:722-730` (tag `<video>` memuat raw file URL tanpa adaptive HLS ladder). |
| **8. Feed Playback** | 1080 x 1920 | Raw MP4 / WebM | 6 Mbps | ~30 fps | Muted di feed | N/A | Pada jaringan 3G/4G Indonesia, file 6 Mbps menyebabkan buffering stall signifikan tanpa adaptive bitrate. |

---

## 3. Temuan Detail Berdasarkan Tingkat Keparahan (Severity)

### 🔴 TEMUAN TINGKAT KRITIS (CRITICAL SEVERITY)

#### 1. Sensor Still Photo Tertinggi Terbuang Karena Pengambilan Menggunakan Frame Video Preview
- **Lokasi Kode:** `public/js/moments/camera-engine.js:612-622`, `645-648`
- **Bukti:**
  ```javascript
  let naturalWidth = this.videoEl.videoWidth || 1080;
  let naturalHeight = this.videoEl.videoHeight || 1920;
  const canvas = document.createElement('canvas');
  canvas.width = naturalWidth;
  canvas.height = naturalHeight;
  const ctx = canvas.getContext('2d');
  ...
  ctx.drawImage(this.videoEl, 0, 0, canvas.width, canvas.height);
  ```
- **Dampak Kualitas:** Pada perangkat modern (Samsung, iPhone, Xiaomi) dengan kamera 12 MP–50 MP, hasil jepretan kamera Momen diturunkan paksa menjadi resolusi video preview (maksimal 1080x1920 = ~2.07 MP atau bahkan 720x1280 = ~0.92 MP). Gambar kehilangan detail tekstur, ketajaman optik sensor, dan dynamic range hardware.
- **Rujukan Invarian:** Melanggar Invarian 2 (*"Tanpa kompresi diam-diam"*), Bagian 1 (*"Hulu: memakai kemampuan terbaik hardware"*), dan Bagian 5 (*"Resolusi still tertinggi yang didukung dan stabil; capture tidak dibatasi resolusi preview"*).

---

#### 2. Master Asli Tidak Disimpan (Overwritten / Transformed on Ingestion)
- **Lokasi Kode:** 
  - `app/Services/StorageService.php:55-57, 138-180`
  - `app/Http/Controllers/Api/MomentController.php:634-635, 650-664`
- **Bukti:**
  Di `StorageService.php`:
  ```php
  if (self::isCompressibleImage($file)) {
      return self::compressAndStoreImage($file, $directory, $targetFormat, $quality, $maxDimension);
  }
  // compressAndStoreImage langsung melakukan re-encode ke WebP Q=95 dan menimpa penyimpanan
  $encoded = $image->toWebp($compressionQuality);
  Storage::disk('public')->put($relativePath, (string) $encoded);
  return $relativePath;
  ```
  Di `MomentController.php`:
  ```php
  $options['transformation'] = [
      'quality'      => $qualityValue,
      'fetch_format' => 'auto',
  ];
  ```
- **Dampak Kualitas:** File master yang asli dari kamera/galeri dihancurkan pada saat upload. Ketika format masa depan atau kebutuhan cetak/download foto original diminta, resolusi dan fidelity asli tidak bisa dipulihkan kembali.
- **Rujukan Invarian:** Melanggar Invarian 1 (*"Master tidak pernah ditimpa. File asli dari kamera atau galeri tersimpan utuh; versi lain hanyalah derivative"*).

---

#### 3. Re-Encoding Ganda (Generation Loss) pada Foto Tanpa Perubahan Nyata
- **Lokasi Kode:**
  - `public/js/moments/camera-engine.js:687-695` (Canvas to JPEG 98%)
  - `public/js/moments/editor-engine.js:1376-1456` (`bakePhotoWithOverlays` dipanggil otomatis untuk semua foto)
  - `public/js/moments/editor-engine.js:1523-1529`
- **Bukti:**
  ```javascript
  let blobToUpload = singleItem ? singleItem.blob : this.mediaBlob;
  if (!isVid) {
      try {
          blobToUpload = await this.bakePhotoWithOverlays(singleItem);
      } catch (e) {
          blobToUpload = singleItem ? singleItem.blob : this.mediaBlob;
      }
  }
  ```
  Di dalam `bakePhotoWithOverlays`, file di-decode ulang ke `<canvas>` dan di-encode kembali via `canvas.toBlob(..., 'image/jpeg', 1.0)`. Setelah itu, backend meng-encode lagi ke WebP 95% via Intervention Image GD driver.
- **Dampak Kualitas:** Terjadi 3x siklus re-encoding lossy (YUV → JPEG → JPEG → WebP) yang menimbulkan artefak kompresi (blocking, ringing) dan penurunan kontras halus.
- **Rujukan Invarian:** Melanggar Invarian 3 (*"Tanpa edit berarti upload apa adanya (hash identik)"*) dan Invarian 5 (*"Derivative dibuat dari master, satu kali. Jangan membuat derivative dari derivative"*).

---

### 🟠 TEMUAN TINGKAT TINGGI (HIGH SEVERITY)

#### 4. Ketiadaan Adaptive Streaming (HLS Ladder) untuk Video Momen
- **Lokasi Kode:** 
  - `app/Http/Controllers/Api/MomentController.php:248-264, 297-304`
  - `resources/views/customer/moments/index.blade.php:677, 722-730`
- **Bukti:**
  Video disajikan langsung dalam tag `<video src="${m.media_url}" ...>` sebagai satu file MP4/WebM monolitik ber-bitrate tetap (6 Mbps).
- **Dampak Kualitas & Performa:**
  - Pada koneksi seluler 3G/4G Indonesia di luar kota besar, video mengalami buffering parah atau gagal berputar (*infinite spinner*).
  - Tidak ada ladder 1080p / 720p / 480p / 360p adaptif.
  - Video MP4 lokal tidak diproses dengan `-movflags +faststart`, sehingga metadata `moov atom` bisa berada di akhir file, memaksa browser mengunduh seluruh file sebelum dapat memutar detik pertama.
- **Rujukan Invarian:** Melanggar Bagian 8 (*"Video ffprobe dulu... HLS: 1080p, 720p, 480p, 360p... Keyframe tiap ~2s"*) dan Bagian 9 (*"Video: HLS lewat pemutar native... buffering stabil"*).

---

#### 5. Ketiadaan Resumable Multipart Upload (Tus / S3 Direct Upload)
- **Lokasi Kode:** `public/js/moments/editor-engine.js:1559-1566`
- **Bukti:**
  Upload dilakukan menggunakan single `fetch('/api/moments', { method: 'POST', body: formData })`.
- **Dampak Keandalan:**
  - Video berukuran besar (> 50–100 MB) atau multi-media carousel (10 item) rentan gagal di tengah jalan saat terjadi fluktuasi sinyal radio/WiFi.
  - Gagal pada byte ke-99% memaksa pengguna mengulang upload dari 0%.
  - Melebihi timeout proxy Nginx atau batas eksekusi PHP pada koneksi lambat.
- **Rujukan Invarian:** Melanggar Bagian 7 (*"Direct-to-storage dan resumable... Part 8–16 MiB... State machine persisten... Idempotensi"*).

---

#### 6. Edit Recipe Video (Overlays, Filter, Stiker, Musik) Tidak Di-Bake ke Hasil Akhir
- **Lokasi Kode:** `public/js/moments/editor-engine.js:1500-1535`
- **Bukti:**
  ```javascript
  const isVid = singleItem ? singleItem.type === 'video' : this.mediaType === 'video';
  let blobToUpload = singleItem ? singleItem.blob : this.mediaBlob;
  if (!isVid) {
      blobToUpload = await this.bakePhotoWithOverlays(singleItem);
  }
  // Pada video, blobToUpload langsung diunggah mentah tanpa baking teks overlay, filter, maupun background music!
  ```
- **Dampak Fungsional & Kualitas:** Pengguna menambahkan teks, stiker, atau memilih lagu di layar Editor Momen, tetapi saat dipublikasikan ke Feed Momen, video muncul polosan tanpa teks/stiker dan tanpa musik pengiring.
- **Rujukan Invarian:** Melanggar Bagian 6 (*"Hasil render sama dengan preview. Efek yang dipilih pengguna harus benar-benar ada di file yang diunggah"*).

---

### 🟡 TEMUAN TINGKAT SEDANG (MEDIUM SEVERITY)

#### 7. Double Loading Media pada Kartu Feed Momen
- **Lokasi Kode:** `resources/views/customer/moments/index.blade.php:683-684`, `748-750`
- **Bukti:**
  ```html
  <!-- Ambient Blurred Backdrop -->
  <img src="${m.media_url}" class="absolute inset-0 w-full h-full object-cover filter blur-3xl opacity-35 scale-110 pointer-events-none" ...>
  <!-- High-res Sharp Image -->
  <img src="${m.media_url}" class="relative z-10 w-full h-full object-contain pointer-events-none" ...>
  ```
- **Dampak Kualitas & Bandwidth:** Dua elemen `<img>` mengarah ke URL resolusi tinggi yang persis sama. Meskipun browser memiliki memory cache, ini memicu overhead decode bitmap ganda di RAM GPU/ponsel (terutama berbahaya untuk perangkat kelas bawah / low-end Android). Seharusnya backdrop blur memanfaatkan `blurhash` atau thumbnail ultra-kecil (32x32 atau 64x64).

---

#### 8. Ketiadaan Metadata Dimensi (Width, Height, Aspect Ratio) pada API Feed (Layout Shift)
- **Lokasi Kode:** `app/Http/Controllers/Api/MomentController.php:103-125`
- **Bukti:**
  Response JSON API `index()` hanya mengembalikan `media_url`, `thumbnail_url`, `media_type`. Tidak ada field `width`, `height`, `aspect_ratio`, `blurhash`, atau `variants`.
- **Dampak Visual:** Terjadi Cumulative Layout Shift (CLS) saat feed di-scroll cepat, karena kartu penampung tidak mengetahui rasio aspek pasti media sebelum berkas selesai diunduh.

---

#### 9. Penggunaan Driver PHP GD daripada Imagick / Libvips untuk Image Processing
- **Lokasi Kode:** `app/Services/StorageService.php:9-10`, `142`
- **Bukti:**
  ```php
  use Intervention\Image\ImageManager;
  use Intervention\Image\Drivers\Gd\Driver;
  ...
  $manager = new ImageManager(new Driver());
  ```
- **Dampak:** Library GD memuat seluruh uncompressed bitmap ke memori PHP. Foto kamera 48 MP (8000x6000) membutuhkan alokasi memori runtime sebesar `8000 x 6000 x 4 byte = ~192 MB` per request. Selain itu, algoritma downscale GD (bicubic/bilinear) inferior dibandingkan Lanczos3 pada libvips/Imagick, menghasilkan ketajaman garis yang kurang optimal.

---

### 🟢 TEMUAN TINGKAT RENDAH (LOW SEVERITY)

#### 10. Hardcoded Limit MediaRecorder pada 6 Mbps Video Bitrate
- **Lokasi Kode:** `public/js/moments/camera-engine.js:788`
- **Bukti:** `videoBitsPerSecond: 6000000` (6 Mbps) dipasang konstan untuk semua skenario dan resolusi. Pada video 1080p 60fps atau adegan dinamis (gerakan cepat), 6 Mbps dapat memunculkan macroblocking. Sebaliknya pada video statis 720p, 6 Mbps memboroskan kuota.

#### 11. Tidak Ada Verifikasi Checksum SHA-256 Klien-Server
- **Lokasi Kode:** `app/Http/Controllers/Api/MomentController.php:223-305`
- **Bukti:** Tidak ada validasi SHA-256 hash antara file yang dikirim oleh klien dengan byte yang diterima oleh server, membuka risiko silent data corruption pada jaringan seluler yang tidak stabil.

---

## 4. Tahap dengan Penurunan Kualitas Terbesar (Worst Degradation Points)

Dari audit di atas, penurunan kualitas terbesar terjadi pada dua titik utama:

1. **Titik Hulu (Capture Shutter — `camera-engine.js:612`):**
   - **Degradasi: Penurunan resolusi sebesar 75% s/d 90% secara instan.**
   - Sensor 12 MP (4000x3000) atau 48 MP diturunkan ke 1080x1920 (2 MP) karena hanya menyalin frame dari stream video preview HTML5.
   - ISP hardware exposure, Ultra HDR gainmap, dan detail lensa terabaikan.
2. **Titik Tengah & Hilir (Re-encoding & Ketiadaan Master):**
   - File mengalami kompresi beruntun 3x.
   - Master asli hilang, sehingga tidak ada jalan kembali untuk menyajikan gambar dalam kualitas sensor aslinya saat dibuka di Fullscreen Viewer.

---

## 5. Kesimpulan Audit

Kamera dan media pipeline Momen Rentify saat ini memiliki fungsionalitas UI yang baik (pratinjau layar penuh, tata letak carousel, filter preset), tetapi arsitektur fidelity medianya melanggar prinsip-prinsip dasar fidelity:
- Sensor asli terpotong oleh batas preview.
- Master file tidak diabadikan.
- Edit non-destruktif belum terwujud (re-encode dipaksakan pada foto, dan diabaikan pada video).
- Delivery belum adaptif (beban ganda pada jaringan lambat).

Audit ini menjadi dasar penyusunan **PLAN.md (Fase 2)** untuk merestrukturisasi pipeline media Rentify secara terukur dan aman.
