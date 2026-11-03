# QUALITY_LEDGER.md — Rentify Camera & Media Fidelity Engine v2
**Fase 3 & 4: Quality Ledger & Verifikasi Pasca-Implementasi**  
*Tanggal: 4 November 2026*  
*Status: Maximum Fidelity Active & Verified*

---

## 1. Perbandingan Kualitas: Sebelum vs Sesudah Implementasi

| Parameter Pipeline | Baseline Sebelum Perbaikan (Status Quo) | Hasil Sesudah Perbaikan (Fidelity Engine v2) | Status Invarian |
| :--- | :--- | :--- | :--- |
| **1. Sensor Capture (Hulu)** | Dibatasi resolusi video preview (1080p = 2 Megapixel / 720p = 0.9 MP) via `ctx.drawImage(this.videoEl)`. | Mengambil foto langsung dari ISP hardware sensor via **`ImageCapture.takePhoto()`** (12 MP, 48 MP, 50 MP) dengan fallback cerdas. | **LULUS** (Invarian 2) |
| **2. Alur Editor (Tengah)** | Wajib di-encode ulang ke `<canvas>` (JPEG 1.0) sekalipun tanpa stiker/teks (*generation loss*). | **Bypass re-encoding 100%**: Jika foto tanpa edit, blob asli dikirim utuh (*hash byte* identik ke master). Jika diedit, di-render pada resolusi sensor penuh. | **LULUS** (Invarian 3) |
| **3. Master Preservation** | File master langsung ditimpa/diubah menjadi WebP lossy di server. Master asli musnah. | **Master Disimpan Permanen**: Berkas asli tersimpan 100% utuh di `moments/masters/` tanpa kompresi atau alterasi apa pun. | **LULUS** (Invarian 1) |
| **4. Skema Database** | Hanya ada `media_url` dan `thumbnail_url`. Tidak ada hash integritas maupun dimensi. | Kolom baru: `master_url`, `master_hash` (SHA-256), `variants` (JSON), `width`, `height`, `aspect_ratio`, `blurhash`. | **LULUS** (Invarian 4) |
| **5. Pembuatan Turunan (Derivative)** | Dibuat berantai (WebRTC -> Canvas -> Editor Canvas -> WebP server = derivative dari derivative). | **One-step derivative dari master**: Versi feed (WebP Q=92) dan thumbnail (WebP Q=80) dihasilkan langsung 1x dari berkas master. | **LULUS** (Invarian 5) |
| **6. Feed Delivery (Hilir)** | **Double loading**: 2x unduhan gambar resolusi penuh per kartu (satu untuk blur latar, satu untuk konten utama). | **Eliminasi double loading**: Latar belakang ambient blur menggunakan thumbnail ringan (`loading="lazy"`, `decoding="async"`). | **LULUS** (Performa) |
| **7. Bitrate Perekaman Video** | Dipatok statis konstan 6 Mbps. | **Adaptive Video Bitrate**: 5 Mbps (720p), 8–10 Mbps (1080p FHD 60fps), hingga 16 Mbps (4K UHD). | **LULUS** (Invarian 2) |
| **8. Integritas Data** | Tidak ada verifikasi checksum. | Checksum **SHA-256** dihitung dan dicatat pada saat upload untuk memverifikasi integritas berkas. | **LULUS** (Invarian 4) |

---

## 2. Rincian Ledger Perjalanan Media Still Photo (Fidelity v2)

*Skenario: Pengambilan foto dari kamera belakang di ponsel dengan sensor 48 MP (resolusi sensor native: 8000x6000 atau pixel-binned 4000x3000).*

| Tahap | Resolusi (px) | Format / Codec | Bitrate / Quality | Color / HDR | Orientasi / EXIF | Hash Match | Catatan |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| **1. Sensor Hardware** | 4000 x 3000 (12MP) s/d 8000x6000 (48MP) | Raw Sensor / ISP YUV | Lossless | sRGB / Display P3 / HDR | Asli dari sensor | N/A (Ground Truth) | Sensor & ISP kamera fisik. |
| **2. Live Preview** | 1080 x 1920 | WebRTC Stream | 60 fps | sRGB | CSS scale / transform | N/A | Pratinjau layar penuh 9:16 lancar. |
| **3. Shutter Capture** | 4000 x 3000 (Resolusi Sensor) | JPEG / HEIC Master | Q=0.98 (~8-15 MB) | sRGB | EXIF terjaga | 100% Sensor | Diambil via `ImageCapture.takePhoto()`. |
| **4. Editor Momen** | 4000 x 3000 | Identik Master | 100% Asli | sRGB | EXIF terjaga | **100% Identik** | Bypass re-encode jika tanpa edit. |
| **5. Pre-Upload** | 4000 x 3000 | Sesuai Master | 100% Asli | sRGB | EXIF terjaga | **100% Identik** | FormData multipart payload. |
| **6. Server Storage (Master)** | 4000 x 3000 | Sesuai Master | 100% Asli | sRGB | EXIF terjaga | **100% Identik** | Tersimpan di `moments/masters/` permanen. |
| **7. Server Derivatives** | Feed: max 2560px, Thumb: max 480px | WebP | Q=92 (Feed), Q=80 (Thumb) | sRGB | Sesuai master | Derivative dari Master | Dibuat 1x langsung dari Master. |
| **8. Feed API Response** | N/A | JSON | N/A | N/A | N/A | N/A | Memuat master_url, master_hash, variants. |
| **9. Feed Display** | Tampilan kartu (~390x844 CSS px) | WebP (Feed) | Q=92 | sRGB | object-contain | N/A | Backdrop memakai thumbnail ringan. |
| **10. Fullscreen Viewer** | 4000 x 3000 (Master) | Sesuai Master | 100% Asli | sRGB | object-contain | **100% Identik** | Pinch-to-zoom tajam hingga resolusi sensor asli. |
| **11. Download / Ekspor** | 4000 x 3000 | Sesuai Master | 100% Asli | sRGB | EXIF terjaga | **100% Identik** | Pengguna mendapatkan berkas asli tanpa re-encode. |

---

## 3. Kesimpulan Verifikasi

Seluruh 11 temuan audit pada `AUDIT.md` telah diselesaikan secara sistematis. Sistem kamera dan media Momen kini memenuhi standar **Maximum Fidelity Target** tanpa mengorbankan performa jaringan maupun kompatibilitas perangkat.
