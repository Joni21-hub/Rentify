# Pipeline & Workflow Operations (Rentify Edition)

Panduan operasional siklus hidup prospek (lead lifecycle), pembatasan kuota pengiriman (rate limits), jendela waktu pengiriman (send windows), dan gerbang keselamatan (pre-send gate).

---

## 1. Siklus Hidup Prospek (Lead Pipeline States)

Setiap prospek bergerak melalui status deterministik berikut:

```
[ DISCOVERED ]  ---> Prospek ditemukan oleh sub-agent Scout
       │
       ▼
  [ SCORED ]    ---> Dihitung fit score (1-100) & diekstraksi sinyalnya
       │             (Skor < 40 otomatis masuk status DISQUALIFIED)
       ▼
  [ QUEUED ]    ---> Sub-agent Writer menyusun draf pesan (Touch 1)
       │
       ▼
  [ OUTBOX ]    ---> Menunggu verifikasi / persetujuan manusia
       │
       ▼
   [ SENT ]     ---> Pesan terkirim melalui WhatsApp / IG / Email
       │
       ▼
  [ REPLIED ]   ---> Prospek memberikan balasan (diklasifikasi oleh Handler)
       │
 ┌─────┴───────────────────┬──────────────────┐
 ▼                         ▼                  ▼
[ QUALIFIED ]     [ NOT_INTERESTED ]    [ UNSUBSCRIBED ]
(Lanjut onboarding/   (Arsipkan kontak)   (Blokir kontak permanen,
 registrasi toko)                         dilarang kirim ulang)
```

---

## 2. Kuota Pengiriman Harian & Proteksi Anti-Ban (Rate Limits)

Pengiriman pesan otomatis tanpa kontrol ritme akan berujung pada **pemblokiran nomor WhatsApp** dan **pembatasan akun Instagram**. Harvey menerapkan aturan batas aman berikut:

### 2.1 WhatsApp Limits (Wajib Patuh)
* **Nomor Baru (Usia < 1 bulan)**: Maksimal **10–15 pesan baru/hari**.
* **Nomor Matang (Warmed up > 3 bulan)**: Maksimal **25–35 pesan baru/hari**.
* **Jeda Antar Pesan (Jitter)**: Acak antara **180 hingga 420 detik (3–7 menit)** antar pengiriman. Dilarang mengirim berturut-turut dalam hitungan detik.
* **Variasi Teks**: Sub-agent Writer wajib membuat variasi pembuka dan kalimat per pesan agar tidak terbaca sebagai *broadcast blast* oleh sistem deteksi spam WhatsApp.

### 2.2 Instagram DM Limits
* Maksimal **15–20 DM baru/hari** per akun Instagram.
* Jeda antar DM minimal **10–15 menit**.
* Pastikan akun Instagram sudah saling follow atau minimal follow target terlebih dahulu sebelum mengirim DM.

### 2.3 Email Limits
* Jika domain email baru: ikuti fase warmup (mulai dari 5 email/hari, naik 3 email/hari hingga batas 50 email/hari).
* Bounce rate threshold: Jika tingkat pentalan (bounce rate) > 5%, **hentikan pengiriman otomatis seketika**.

---

## 3. Jendela Waktu Pengiriman (Send Window & Quiet Hours)

Menghubungi pemilik bisnis di waktu yang salah akan merusak reputasi brand dan memicu komplain.

* **Waktu Pengiriman Aktif**:
  * **Hari Kerja (Senin – Jumat)**: Pukul **09:30 – 16:30 WIB**.
  * **Sabtu**: Pukul **10:00 – 14:00 WIB** (Fokus pada vendor yang sedang bersiap untuk persewaan weekend).
  * **Minggu & Hari Libur Nasional**: **TIDAK ADA OUTREACH DINGIN (OFF / QUIET HOURS)**. Khusus pesan balasan yang sudah berjalan tetap diperbolehkan.
* **Zona Waktu**: WIB (Waktu Indonesia Barat) / WITA / WIT disesuaikan dengan kota domisili target bisnis.

---

## 4. Gerbang Keselamatan Pra-Kirim (Deterministic Pre-Send Gate)

Sebelum pesan meninggalkan antrean `OUTBOX`, skrip filter deterministik memeriksa 6 syarat mutlak:

1. **Check Do-Not-Contact (DNC)**:
   * Nomor atau akun TIDAK terdaftar di daftar blokir, vendor yang sudah aktif bergabung di Rentify, atau kompetitor langsung.
2. **Check Word Count**:
   * WhatsApp: Panjang kata antara 25 – 70 kata. Jika > 80 kata, tolak dan minta Writer memangkas draf.
3. **Check Banned Words**:
   * Memastikan tidak ada kata terlarang (em dash `—`, "revolusioner", "tanpa risiko", kata-kata all-caps, atau link URL di pesan pertama).
4. **Check Single CTA**:
   * Memastikan hanya ada tepat SATU tanda tanya (?) di seluruh isi pesan Touch 1.
5. **Check Working Hours**:
   * Jam lokal target saat ini berada dalam rentang jendela pengiriman aktif.
6. **Check Daily Quota Remaining**:
   * Batas harian untuk kanal yang dipilih belum tercapai.

---

## 5. Klasifikasi Respon (Intent Classification Matrix)

Sub-agent **Handler** mengelompokkan balasan masuk ke dalam 6 kategori aksi:

| Kategori Respon | Contoh Kalimat Balasan Masuk | Aksi Sub-Agent Handler |
| :--- | :--- | :--- |
| **INTERESTED** | *"Boleh infonya kak", "Gimana caranya gabung?", "Berapa biayanya?"* | Kirim penjelasan singkat 5% fee + link onboarding toko di [rentify21.my.id/vendor/register](https://rentify21.my.id). |
| **OBJECTION** | *"Takut barang hilang", "Saya gaptek", "Di WA sudah ramai"* | Buka [objection-handling.md](./objection-handling.md) dan jawab dengan metode LAARC. |
| **QUESTION** | *"Ini aplikasi web atau playstore?", "Ada minimal barang sewa?"* | Jawab to the point, maksimal 40 kata, lalu tutup dengan konfirmasi. |
| **WRONG_PERSON** | *"Ini HP staf gudang kak", "Ownernya lagi di luar kota"* | Minta nomor atau kontak PIC/Owner yang menangani kerjasama kemitraan dengan sopan. |
| **NOT_INTERESTED** | *"Belum butuh kak", "Skip dulu"* | Ucapkan terima kasih ramah (*"Siap kak, terima kasih banyak waktunya 🙏"*). Pindahkan status ke `NOT_INTERESTED`. Dilarang mendebat. |
| **OPTOUT_UNSUB** | *"Jangan spam", "Hapus nomor saya", "Dapat nomor dari mana?"* | Minta maaf singkat, hapus dari antrean, dan masukkan ke daftar `DO_NOT_CONTACT` permanen. |
