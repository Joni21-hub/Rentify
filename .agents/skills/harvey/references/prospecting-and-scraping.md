# Prospecting & Scraping Guide (Rentify Edition)

Panduan taktis menemukan data kontak vendor rental dan calon customer potensial di Indonesia tanpa tools berbayar (Zero-Cost Lead Generation).

---

## 1. Google Dorking untuk Vendor Rental Indonesia

Gunakan operator pencarian Google untuk menemukan profil Instagram, situs web, dan nomor WhatsApp bisnis rental lokal.

### 1.1 Mencari Profil Instagram Vendor Rental
Pola pencarian mencari akun Instagram yang menyertakan nomor WhatsApp atau link `wa.me` di bio:
```text
site:instagram.com "rental kamera" ("jakarta" OR "surabaya" OR "bandung") "wa.me"
site:instagram.com "sewa alat camping" ("yogyakarta" OR "malang" OR "semarang") "08"
site:instagram.com "sewa kebaya" OR "rental jas" "wa.me"
site:instagram.com "rental sound system" ("bekasi" OR "tangerang" OR "depok") "whatsapp"
site:instagram.com "rental ps5" OR "sewa playstation" "wa.me"
```

### 1.2 Mencari Nomor WhatsApp & Linktree di Profil Bisnis
```text
"rental kamera" "linktr.ee" OR "bit.ly" OR "wa.wizard" "surabaya"
"sewa outdoor" "wa.me/628" site:facebook.com
"sewa alat berat" OR "rental genset" "contact us" filetype:pdf OR site:id
```

### 1.3 Mencari Calon Customer (Event, Komunitas, Mahasiswa)
```text
site:instagram.com "event organizer" ("wedding" OR "gathering") "jakarta" "contact"
site:instagram.com "bem" OR "himpunan" ("ugm" OR "ui" OR "itb" OR "unair") "divisi perlengkapan"
"open trip" "bromo" OR "prau" OR "rinjani" "contact person" "08"
"production house" OR "filmmaker" "surabaya" "contact"
```

---

## 2. Google Maps & Direktori Bisnis Lokal

Google Maps adalah sumber data nomor 1 untuk bisnis rental fisik di Indonesia.

### 2.1 Keyword Pencarian Google Maps
* **Kamera & Studio**: `rental kamera terdekat`, `sewa lensa`, `studio foto rental`.
* **Camping & Petualangan**: `sewa tenda camping`, `rental outdoor`, `rental peralatan gunung`.
* **Pakaian & Pesta**: `sewa gaun pesta`, `rental kebaya wisuda`, `sewa jas pria`, `sewa kostum`.
* **Acara & Hiburan**: `rental sound system`, `sewa proyektor terdekat`, `sewa genset`, `rental tenda pesta`.
* **Gaming**: `rental ps terdekat`, `sewa ps5 harian`.

### 2.2 Data yang Wajib Diambil dari Google Maps
1. **Nama Bisnis**: Nama lengkap sesuai Google Maps.
2. **Kategori Bisnis**: Kamera, Outdoor, Busana, Sound, dll.
3. **Alamat & Kota**: Menentukan radius pengiriman/jangkauan.
4. **Nomor Telepon**: Nomor HP/WhatsApp (biasanya berformat `08...` atau `+628...`).
5. **Rating & Jumlah Ulasan**:
   * Rating 4.0 - 4.8 dengan ulasan > 20: Vendor stabil, volume sewa tinggi.
   * Ulasan negatif seputar "admin slow respon di WA" atau "jadwal bentrok": **High-Value Trigger Signal!**
6. **Jam Operasional**: Jam buka-tutup (menghindari mengirim pesan di luar jam kerja).

---

## 3. Ekstraksi Data dari Instagram

Bisnis rental di Indonesia hampir 90% mengandalkan Instagram sebagai etalase visual:

### 3.1 Ciri-Ciri Vendor Ideal di Instagram
* **Bio**: Berisi info kota, link `wa.me/628...`, atau link aggregator (Linktree/Lynk.id).
* **Highlights (Sorotan)**: Memuat "Pricelist", "Format Sewa", "Syarat & Ketentuan", "Testimoni".
* **Aktivitas Terakhir**: Ada postingan atau story dalam 7-14 hari terakhir (membuktikan bisnis aktif).

### 3.2 Tanda-Tanda Bisnis yang Butuh Rentify
* Format sewa di sorotan masih manual: *"Isi nama, tanggal sewa, jaminan KTP fisik, transfer DP manual"*.
* Sering update story: *"Slot weekend ini sudah full"* (bisa didorong untuk ekspansi stok/mitra).
* Tidak punya website pemesanan otomatis sendiri.

---

## 4. Validasi & Sanitasi Data Kontak

Sebelum pesan dikirim oleh sub-agent **Sender**, kontak wajib divalidasi secara deterministik:

### 4.1 Validasi Nomor WhatsApp Indonesia
* Normalisasi format: Ubah `0812...` atau `812...` menjadi format standar internasional `62812...`.
* Hapus karakter non-digit seperti spasi, tanda hubung (`-`), atau tanda kurung.
* Validasi panjang: Nomor HP Indonesia valid memiliki panjang 10–13 digit setelah `628`.
* Filter nomor kantor telepon kabel (misal kode area `021`, `031`, `022` tanpa WhatsApp).

### 4.2 Validasi Email (B2B / Formal)
* Syntax regex checking.
* Domain MX Record Lookup (memastikan domain memiliki mail exchange aktif).
* Hindari email umum seperti `noreply@`, `admin@` jika targetnya adalah owner/manajer langsung.

---

## 5. Kuota Harian Pengumpulan Data (Scout Daily Targets)

Agar pipeline selalu terisi dengan stabil tanpa memicu pembatasan:
* **Target riset harian**: 25–40 profil bisnis rental.
* **Kontak tervalidasi**: 15–20 kontak baru per hari.
* **Segmentasi**: Minimal 70% Vendor lokal, 30% Calon Customer (EO/Organisasi).
