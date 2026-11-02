# 🎬 Rentify Remotion Video Marketing Engine (`./vid-remotion`)

Engine otomasi video marketing berbasis [Remotion](https://www.remotion.dev/) untuk menghasilkan video vertikal (9:16 - 1080x1920) siap posting untuk **Instagram Reels, TikTok, YouTube Shorts, dan WhatsApp Status**.

---

## 🚀 6 Template Video Pemasaran (Full-Funnel Coverage)

### 0. `RentifyUIShowcase` (Durasi: 20 Detik / 600 Frames)
Flagship Interactive UI Showcase Reel:
* **Scene 1 (Beranda & Discovery):** Search bar pencarian barang, filter kategori, badge lokasi Semarang.
* **Scene 2 (Katalog Produk):** Grid kartu produk interaktif dengan harga sewa dan rating.
* **Scene 3 (PDP & 3 Pilar Keamanan):** Detail produk Sony A7 IV, perbandingan hemat 98%, serta 3 pilar keamanan (Escrow, Biometrik, Digital Manifest).
* **Scene 4 (Checkout & QRIS):** Rincian biaya, diskon voucher `-Rp 50.000`, dan modal pembayaran instan QRIS.
* **Scene 5 (Mitra Pemilik Barang):** Simulasi pendapatan pasif bulanan Rp 3.850.000/bln dengan 0% komisi.
* **Scene 6 (CTA & Outro):** Ajakan transaksi ke domain resmi `rentify21.my.id`.

### 1. `RentifyBrandPromo` (Durasi: 15 Detik / 450 Frames)
Video profil brand *dual-sided* (Penyewa vs Pemilik):
* **Scene 1 (Penyewa):** Akses alat premium tanpa beban beli baru (hemat hingga 95%).
* **Scene 2 (Pemilik Aset):** Simulasi *Passive Income* nyata dengan **0% potongan komisi listing**.
* **Scene 3 (3 Pilar Keamanan):** Rekening Escrow, KYC Biometrik Terverifikasi, dan Digital Manifest.
* **Scene 4 (CTA):** Branding resmi dan ajakan sewa di `rentify21.my.id`.

### 2. `HowItWorksPromo` (Durasi: 12 Detik / 360 Frames)
Video edukasi alur sewa kilat 4 langkah mudah untuk menghilangkan friksi audiens baru:
* **Step 1:** Cari & Pilih Alat (Kamera, Drone, Tenda dekat lokasimu).
* **Step 2:** Verifikasi Biometrik Kilat (e-KTP & selfie 1x seumur hidup).
* **Step 3:** Bayar Aman via Rekening Escrow (QRIS / Transfer Bank, uang ditahan aman sampai barang tiba).
* **Step 4:** Terima & Pakai Sepuasnya (Diantar ke rumah atau ambil di vendor terdekat).

### 3. `ProductCatalogPromo` (Durasi: 12 Detik / 360 Frames)
Showcase produk fotografi/kamera premium berbasis schema **Zod**:
* Menampilkan: Sony Alpha 7 IV Full-Frame, Kategori, Harga sewa Rp 250.000/hari vs Beli baru Rp 36,5 Juta (Hemat 98%), Vendor terverifikasi, Rating 4.9 (128 ulasan), dan 4 poin fitur unggulan.
* Dapat diparameterisasi langsung via CLI JSON props.

### 4. `OutdoorCampingPromo` (Durasi: 12 Detik / 360 Frames)
Showcase perlengkapan petualangan & naik gunung:
* Menampilkan: Paket Camping Gunung 4 Orang (Tenda Dome Double Layer + Cooking Set Nesting DS-308 + 4 Sleeping Bag Polar + Kompor Windproof & Gas).
* Harga sewa Rp 85.000/hari vs Beli baru Rp 3.250.000 (Hemat 97%), Vendor Semarang Adventure Outdoor, Rating 4.95.

### 5. `OwnerYieldPromo` (Durasi: 12 Detik / 360 Frames)
Video akuisisi vendor / pemilik barang (*supply-side growth*):
* Mengedukasi pemilik kamera, drone, dan alat outdoor yang menganggur di lemari.
* Counter animasi simulasi passive income Rp 2.400.000/bulan untuk aset Rp 15 Juta.
* Menegaskan kebijakan **0% biaya potongan platform untuk vendor**.

### 6. `FlashDealPromo` (Durasi: 10 Detik / 300 Frames)
Video promosi konversi tinggi berbatas waktu:
* Animasi badge Flash Sale berapi (*high energy*).
* Diskon 30% + Cashback Koin, kupon voucher promo `RENTIFYWEEKEND`.

---

## 🛠️ Panduan Penggunaan & Perintah CLI

Pastikan berada di direktori `vid-remotion`:
```bash
cd vid-remotion
```

### 1. Menjalankan Remotion Studio (Preview Visual Interaktif)
Buka studio GUI di browser untuk melihat animasi secara real-time:
```bash
npm run dev
```

### 2. Render Video MP4 Langsung
Render video berkualitas tinggi ke folder `out/`:
```bash
# Render video Flagship UI Showcase
npm run render:ui

# Render video Brand Awareness dual-sided
npm run render:brand

# Render video Edukasi Cara Sewa 4 Langkah
npm run render:howitworks

# Render video Showcase Kamera & Fotografi
npm run render:product

# Render video Showcase Paket Camping & Outdoor
npm run render:outdoor

# Render video Akuisisi Mitra Pemilik Aset
npm run render:owner

# Render video Promo Flash Deal Weekend
npm run render:flash

# Render SELURUH 7 video sekaligus
npm run render:all
```

### 3. Render Preview Gambar (Still Frame)
Menghasilkan poster / thumbnail visual instan tanpa render video utuh:
```bash
npm run preview:all
```

---

## 🎨 Token Desain & Spesifikasi
* **Resolusi:** 1080 x 1920 piksel (Rasio Aspek 9:16 Vertikal).
* **Frame Rate:** 30 FPS.
* **Format Render:** MP4 (H.264 / AAC) teroptimasi untuk Instagram Reels, TikTok, YouTube Shorts.
* **Warna Brand:** Sky `#0284c7`, Amber `#f59e0b`, Emerald `#10b981`, Slate `#0f172a`.
* **Tema Visual:** *Warm glassmorphism* & *modern dynamic motion* serasi dengan portal Rentify.
