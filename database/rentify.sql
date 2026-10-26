-- Rentify Backup Dump from Cloud Aiven MySQL
-- Generated at 2026-10-23 20:37:44

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
SET FOREIGN_KEY_CHECKS=0;

--
-- Table structure for table `ai_marketing_posts`
--
DROP TABLE IF EXISTS `ai_marketing_posts`;
CREATE TABLE `ai_marketing_posts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tanggal` date NOT NULL,
  `day_number` tinyint unsigned NOT NULL,
  `day_type` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `target_audience` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tema` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `kategori_barang` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `persona_vendor` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `hook_masalah` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `naskah_video` json NOT NULL,
  `teks_di_layar` json NOT NULL,
  `caption` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `visual_prompt` text COLLATE utf8mb4_unicode_ci,
  `visual_media_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ai_source` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Gemini AI',
  `status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ready',
  `posted_to_instagram` tinyint(1) NOT NULL DEFAULT '0',
  `posted_to_tiktok` tinyint(1) NOT NULL DEFAULT '0',
  `instagram_post_id` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tiktok_post_id` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `posted_at` timestamp NULL DEFAULT NULL,
  `log_info` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ai_marketing_posts_tanggal_index` (`tanggal`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ai_marketing_posts`
--
LOCK TABLES `ai_marketing_posts` WRITE;
/*!40000 ALTER TABLE `ai_marketing_posts` DISABLE KEYS */;
INSERT INTO `ai_marketing_posts` (`id`, `tanggal`, `day_number`, `day_type`, `target_audience`, `tema`, `kategori_barang`, `persona_vendor`, `hook_masalah`, `naskah_video`, `teks_di_layar`, `caption`, `visual_prompt`, `visual_media_url`, `ai_source`, `status`, `posted_to_instagram`, `posted_to_tiktok`, `instagram_post_id`, `tiktok_post_id`, `posted_at`, `log_info`, `created_at`, `updated_at`) VALUES
(1, '2026-10-21', 21, 'ganjil', 'customer', 'Solusi Cerdas: Sewa Otomotif & Motor Harian Tanpa Perlu Beli Mahal!', 'Otomotif & Motor Harian', NULL, 'Motor lagi diservis atau butuh kendaraan harian darurat tanpa ribet kredit?', '[{\"detik\": \"0-3s\", \"adegan\": \"Hook Masalah (Shocking Question)\", \"visual\": \"Close-up aktor menatap kamera dengan ekspresi bingung memegang struk harga atau melihat barang mahal online.\", \"voiceover\": \"Motor lagi diservis atau butuh kendaraan harian darurat tanpa ribet kredit? Jangan buang uang jutaan cuma buat barang yang dipakai sesekali!\"}, {\"detik\": \"3-10s\", \"adegan\": \"Dilema Beli vs Numpuk di Gudang\", \"visual\": \"Potongan video barang menumpuk berdebu di pojok kamar atau gudang, diiringi teks kalkulasi harga beli vs jarang dipakai.\", \"voiceover\": \"Udah beli mahal, dipakai cuma 2 kali, ujung-ujungnya cuma berdebu dan menuh-menuhin gudang rumah.\"}, {\"detik\": \"10-20s\", \"adegan\": \"Solusi Sewa di Rentify\", \"visual\": \"Aktor tersenyum scroll web Rentify di smartphone, menampilkan katalog unit terdekat dengan harga sewa murah meriah.\", \"voiceover\": \"Untung sekarang ada Rentify! Kamu bisa sewa Otomotif & Motor Harian terbaik dengan harga mulai puluhan ribu aja per hari. Unit bersih, terawat, dan siap pakai!\"}, {\"detik\": \"20-30s\", \"adegan\": \"Call To Action\", \"visual\": \"Tampilan halaman depan web Rentify di layar smartphone dengan tombol \'Sewa Sekarang\' yang diklik.\", \"voiceover\": \"Ngapain beli mahal kalau bisa sewa hemat? Cek katalog terdekat dan booking sekarang juga di web Rentify!\"}]', '[{\"teks\": \"Motor lagi diservis atau butuh kendaraan harian darurat tanpa ribet kredit?\", \"style\": \"Teks merah tebal font bold sans-serif\", \"posisi\": \"Tengah Atas\"}, {\"teks\": \"Beli: Rp 2.500.000 vs Sewa: Rp 45.000\", \"style\": \"Kotak komparasi kontras\", \"posisi\": \"Tengah\"}, {\"teks\": \"Sewa Mudah & Dekat di Rentify\", \"style\": \"Badge biru langit cerah\", \"posisi\": \"Bawah\"}, {\"teks\": \"👉 Buka localhost Sekarang!\", \"style\": \"Tombol pill CTA bergerak\", \"posisi\": \"Bawah\"}]', 'Motor lagi diservis atau butuh kendaraan harian darurat tanpa ribet kredit? 😱\n\nBanyak orang yang kejebak beli barang jutaan padahal cuma dipakai 1-2 kali setahun. Akhirnya? Menumpuk di gudang dan rusak dimakan waktu!\n\nStop boros! Mulai sekarang, kebutuhan sesekali seperti Otomotif & Motor Harian (Motor matic harian, helm touring, action cam) cukup SEWA aja di Rentify. Tarif super hemat, barang berkualitas, dan langsung dekat dari lokasimu! ✨\n\n📲 Yuk cek katalog barang rental terdekat sekarang:\n🌐 http://localhost\n\n#Rentify #SewaAja #SolusiHemat #RentalBarang #SewaKamera #SewaCamping #SewaPerkakas #AntiBeliMahal #GayaHidupHemat #RentalDekatKamu', 'Format 9:16 vertikal. Visual terbagi dua bagian split-screen: atas menampilkan dilema beli mahal, bawah menampilkan solusi sewa praktis di Rentify dengan warna dominan Sky Blue & Emerald Green.', NULL, 'Smart Heuristic Engine', 'ready', 0, 0, NULL, NULL, NULL, NULL, '2026-10-20 13:46:03', '2026-10-20 13:46:03'),
(2, '2026-10-22', 22, 'genap', 'vendor', 'Ide Cuan Bapak-bapak / Pekerja: Dari Barang Nganggur Jadi Passive Income Jutaan!', NULL, 'Bapak-bapak / Pekerja', 'Bor listrik, tangga lipat, atau tenda camping nganggur di garasi? Jadikan mesin cuan di Rentify!', '[{\"detik\": \"0-3s\", \"adegan\": \"Hook Barang Nganggur Berdebu\", \"visual\": \"Kamera menyorot barang-barang bagus di lemari/gudang yang tertutup debu, lalu aktor mengetuk-ngetuk barang itu dengan rasa gemas.\", \"voiceover\": \"Bor listrik, tangga lipat, atau tenda camping nganggur di garasi? Jadikan mesin cuan di Rentify! Tau gak kalau barang kamu ini banyak banget yang cari tiap hari?\"}, {\"detik\": \"3-10s\", \"adegan\": \"Kalkulasi Potensi Cuan Pasif\", \"visual\": \"Grafis animasi uang mengalir masuk ke saldo smartphone saat barang disewa orang selama akhir pekan.\", \"voiceover\": \"Daripada didiemin harganya makin turun, mending disewakan! Sewa 2 hari tiap akhir pekan, sebulan bisa dapet 1 sampai 3 juta bersih!\"}, {\"detik\": \"10-20s\", \"adegan\": \"Solusi Jadi Vendor di Rentify\", \"visual\": \"Tampilan mudah foto barang dan upload ke web Rentify via ponsel, langsung tayang ke calon penyewa terdekat.\", \"voiceover\": \"Di Rentify, daftar jadi vendor itu GRATIS! Gak perlu sewa ruko fisik. Ada sistem verifikasi aman, jaminan identitas, dan uang sewa langsung cair ke rekening kamu!\"}, {\"detik\": \"20-30s\", \"adegan\": \"Call To Action Vendor\", \"visual\": \"Aktor menunjukkan ponsel dengan notifikasi \'Pesanan Sewa Masuk\' dan mengacungkan jempol ke kamera.\", \"voiceover\": \"Mulai bisnis rentalmu sendiri hari ini tanpa modal! Buka web Rentify dan daftarkan barang nganggurmu sekarang!\"}]', '[{\"teks\": \"Bor listrik, tangga lipat, atau tenda camping nganggur di garasi? Jadikan mesin cuan di Rentify!\", \"style\": \"Teks oranye cerah font tebal\", \"posisi\": \"Tengah Atas\"}, {\"teks\": \"Barang Nganggur = Cuan Rp 1.500.000+/Bulan\", \"style\": \"Badge penghasilan hijau\", \"posisi\": \"Tengah\"}, {\"teks\": \"Daftar Vendor Gratis Tanpa Modal Ruko\", \"style\": \"Badge putih border biru\", \"posisi\": \"Bawah\"}, {\"teks\": \"🚀 Mulai Sewakan di rentify.id Sekarang!\", \"style\": \"Pill CTA kontras\", \"posisi\": \"Bawah\"}]', 'Bor listrik, tangga lipat, atau tenda camping nganggur di garasi? Jadikan mesin cuan di Rentify! 💸✨\n\nTau gak sih? Ribuan orang di sekitarmu lagi butuh barang yang kebetulan lagi nganggur di rumahmu!\n\nDaripada cuma menuh-menuhin ruangan dan berdebu, yuk daftarkan barangmu di Rentify. Mulai dari mahasiswa, ibu rumah tangga, sampai pemilik rental toko, semuanya bisa dapat penghasilan pasif rutin setiap bulan!\n\n✅ Gratis pendaftaran toko rental online\n✅ Sistem verifikasi penyewa aman\n✅ Uang sewa langsung masuk ke saldo & bisa dicairkan kapan saja\n\n🔗 Mulai sewakan barangmu sekarang di:\n🌐 http://localhost/vendor/register\n\n#RentifyVendor #CuanDariRumah #PassiveIncome #PeluangBisnis #IdeUsahaMahasiswa #BisnisRental #BarangNganggurJadiUang #SewaBarangOnline', 'Format 9:16 vertikal. Visual berenergi tinggi, menampilkan transisi dari barang berdebu di lemari menjadi notifikasi saldo cuan bertambah di aplikasi Rentify. Dominan warna Indigo & Gold/Yellow.', NULL, 'Smart Heuristic Engine', 'ready', 0, 0, NULL, NULL, NULL, NULL, '2026-10-20 13:46:11', '2026-10-20 13:46:11'),
(3, '2026-10-25', 25, 'ganjil', 'customer', 'Solusi Cerdas: Sewa Fotografi & Kamera Acara Tanpa Perlu Beli Mahal!', 'Fotografi & Kamera Acara', NULL, 'Ada acara wisuda & foto prewed sehari, masa beli kamera 20 juta?', '[{\"detik\": \"0-3s\", \"adegan\": \"Hook Masalah (Shocking Question)\", \"visual\": \"Close-up aktor menatap kamera dengan ekspresi bingung memegang struk harga atau melihat barang mahal online.\", \"voiceover\": \"Ada acara wisuda & foto prewed sehari, masa beli kamera 20 juta? Jangan buang uang jutaan cuma buat barang yang dipakai sesekali!\"}, {\"detik\": \"3-10s\", \"adegan\": \"Dilema Beli vs Numpuk di Gudang\", \"visual\": \"Potongan video barang menumpuk berdebu di pojok kamar atau gudang, diiringi teks kalkulasi harga beli vs jarang dipakai.\", \"voiceover\": \"Udah beli mahal, dipakai cuma 2 kali, ujung-ujungnya cuma berdebu dan menuh-menuhin gudang rumah.\"}, {\"detik\": \"10-20s\", \"adegan\": \"Solusi Sewa di Rentify\", \"visual\": \"Aktor tersenyum scroll web Rentify di smartphone, menampilkan katalog unit terdekat dengan harga sewa murah meriah.\", \"voiceover\": \"Untung sekarang ada Rentify! Kamu bisa sewa Fotografi & Kamera Acara terbaik dengan harga mulai puluhan ribu aja per hari. Unit bersih, terawat, dan siap pakai!\"}, {\"detik\": \"20-30s\", \"adegan\": \"Call To Action\", \"visual\": \"Tampilan halaman depan web Rentify di layar smartphone dengan tombol \'Sewa Sekarang\' yang diklik.\", \"voiceover\": \"Ngapain beli mahal kalau bisa sewa hemat? Cek katalog terdekat dan booking sekarang juga di web Rentify!\"}]', '[{\"teks\": \"Ada acara wisuda & foto prewed sehari, masa beli kamera 20 juta?\", \"style\": \"Teks merah tebal font bold sans-serif\", \"posisi\": \"Tengah Atas\"}, {\"teks\": \"Beli: Rp 2.500.000 vs Sewa: Rp 45.000\", \"style\": \"Kotak komparasi kontras\", \"posisi\": \"Tengah\"}, {\"teks\": \"Sewa Mudah & Dekat di Rentify\", \"style\": \"Badge biru langit cerah\", \"posisi\": \"Bawah\"}, {\"teks\": \"👉 Buka localhost Sekarang!\", \"style\": \"Tombol pill CTA bergerak\", \"posisi\": \"Bawah\"}]', 'Ada acara wisuda & foto prewed sehari, masa beli kamera 20 juta? 😱\n\nBanyak orang yang kejebak beli barang jutaan padahal cuma dipakai 1-2 kali setahun. Akhirnya? Menumpuk di gudang dan rusak dimakan waktu!\n\nStop boros! Mulai sekarang, kebutuhan sesekali seperti Fotografi & Kamera Acara (Kamera mirrorless, lensa tele, gimbal stabilizer, mic wireless) cukup SEWA aja di Rentify. Tarif super hemat, barang berkualitas, dan langsung dekat dari lokasimu! ✨\n\n📲 Yuk cek katalog barang rental terdekat sekarang:\n🌐 http://localhost\n\n#Rentify #SewaAja #SolusiHemat #RentalBarang #SewaKamera #SewaCamping #SewaPerkakas #AntiBeliMahal #GayaHidupHemat #RentalDekatKamu', 'Format 9:16 vertikal. Visual terbagi dua bagian split-screen: atas menampilkan dilema beli mahal, bawah menampilkan solusi sewa praktis di Rentify dengan warna dominan Sky Blue & Emerald Green.', NULL, 'Smart Heuristic Engine', 'ready', 0, 0, NULL, NULL, NULL, NULL, '2026-10-20 13:57:34', '2026-10-20 13:57:34'),
(4, '2026-10-26', 26, 'genap', 'vendor', 'Ide Cuan Bapak-bapak / Pekerja: Dari Barang Nganggur Jadi Passive Income Jutaan!', NULL, 'Bapak-bapak / Pekerja', 'Bor listrik, tangga lipat, atau tenda camping nganggur di garasi? Jadikan mesin cuan di Rentify!', '[{\"detik\": \"0-3s\", \"adegan\": \"Hook Barang Nganggur Berdebu\", \"visual\": \"Kamera menyorot barang-barang bagus di lemari/gudang yang tertutup debu, lalu aktor mengetuk-ngetuk barang itu dengan rasa gemas.\", \"voiceover\": \"Bor listrik, tangga lipat, atau tenda camping nganggur di garasi? Jadikan mesin cuan di Rentify! Tau gak kalau barang kamu ini banyak banget yang cari tiap hari?\"}, {\"detik\": \"3-10s\", \"adegan\": \"Kalkulasi Potensi Cuan Pasif\", \"visual\": \"Grafis animasi uang mengalir masuk ke saldo smartphone saat barang disewa orang selama akhir pekan.\", \"voiceover\": \"Daripada didiemin harganya makin turun, mending disewakan! Sewa 2 hari tiap akhir pekan, sebulan bisa dapet 1 sampai 3 juta bersih!\"}, {\"detik\": \"10-20s\", \"adegan\": \"Solusi Jadi Vendor di Rentify\", \"visual\": \"Tampilan mudah foto barang dan upload ke web Rentify via ponsel, langsung tayang ke calon penyewa terdekat.\", \"voiceover\": \"Di Rentify, daftar jadi vendor itu GRATIS! Gak perlu sewa ruko fisik. Ada sistem verifikasi aman, jaminan identitas, dan uang sewa langsung cair ke rekening kamu!\"}, {\"detik\": \"20-30s\", \"adegan\": \"Call To Action Vendor\", \"visual\": \"Aktor menunjukkan ponsel dengan notifikasi \'Pesanan Sewa Masuk\' dan mengacungkan jempol ke kamera.\", \"voiceover\": \"Mulai bisnis rentalmu sendiri hari ini tanpa modal! Buka web Rentify dan daftarkan barang nganggurmu sekarang!\"}]', '[{\"teks\": \"Bor listrik, tangga lipat, atau tenda camping nganggur di garasi? Jadikan mesin cuan di Rentify!\", \"style\": \"Teks oranye cerah font tebal\", \"posisi\": \"Tengah Atas\"}, {\"teks\": \"Barang Nganggur = Cuan Rp 1.500.000+/Bulan\", \"style\": \"Badge penghasilan hijau\", \"posisi\": \"Tengah\"}, {\"teks\": \"Daftar Vendor Gratis Tanpa Modal Ruko\", \"style\": \"Badge putih border biru\", \"posisi\": \"Bawah\"}, {\"teks\": \"🚀 Mulai Sewakan di rentify.id Sekarang!\", \"style\": \"Pill CTA kontras\", \"posisi\": \"Bawah\"}]', 'Bor listrik, tangga lipat, atau tenda camping nganggur di garasi? Jadikan mesin cuan di Rentify! 💸✨\n\nTau gak sih? Ribuan orang di sekitarmu lagi butuh barang yang kebetulan lagi nganggur di rumahmu!\n\nDaripada cuma menuh-menuhin ruangan dan berdebu, yuk daftarkan barangmu di Rentify. Mulai dari mahasiswa, ibu rumah tangga, sampai pemilik rental toko, semuanya bisa dapat penghasilan pasif rutin setiap bulan!\n\n✅ Gratis pendaftaran toko rental online\n✅ Sistem verifikasi penyewa aman\n✅ Uang sewa langsung masuk ke saldo & bisa dicairkan kapan saja\n\n🔗 Mulai sewakan barangmu sekarang di:\n🌐 http://localhost/vendor/register\n\n#RentifyVendor #CuanDariRumah #PassiveIncome #PeluangBisnis #IdeUsahaMahasiswa #BisnisRental #BarangNganggurJadiUang #SewaBarangOnline', 'Format 9:16 vertikal. Visual berenergi tinggi, menampilkan transisi dari barang berdebu di lemari menjadi notifikasi saldo cuan bertambah di aplikasi Rentify. Dominan warna Indigo & Gold/Yellow.', NULL, 'Smart Heuristic Engine', 'ready', 1, 1, 'IG_CEFDIK2YERI2', 'TT_KWECZTO1XBK3', '2026-10-20 13:57:35', 'Instagram: Siap posting (Simulasi Sandbox - Token belum di-set di .env). ID: IG_CEFDIK2YERI2\nTikTok: Siap posting (Simulasi Sandbox - Token belum di-set di .env). ID: TT_KWECZTO1XBK3', '2026-10-20 13:57:34', '2026-10-20 13:57:35'),
(5, '2026-10-23', 23, 'ganjil', 'customer', 'Solusi Cerdas: Sewa Musik & Band Tanpa Perlu Beli Mahal!', 'Musik & Band', NULL, 'Mau tampil akustikan akhir pekan ini tapi belum punya gitar bagus?', '[{\"detik\": \"0-3s\", \"adegan\": \"Hook Masalah (Shocking Question)\", \"visual\": \"Close-up aktor menatap kamera dengan ekspresi bingung memegang struk harga atau melihat barang mahal online.\", \"voiceover\": \"Mau tampil akustikan akhir pekan ini tapi belum punya gitar bagus? Jangan buang uang jutaan cuma buat barang yang dipakai sesekali!\"}, {\"detik\": \"3-10s\", \"adegan\": \"Dilema Beli vs Numpuk di Gudang\", \"visual\": \"Potongan video barang menumpuk berdebu di pojok kamar atau gudang, diiringi teks kalkulasi harga beli vs jarang dipakai.\", \"voiceover\": \"Udah beli mahal, dipakai cuma 2 kali, ujung-ujungnya cuma berdebu dan menuh-menuhin gudang rumah.\"}, {\"detik\": \"10-20s\", \"adegan\": \"Solusi Sewa di Rentify\", \"visual\": \"Aktor tersenyum scroll web Rentify di smartphone, menampilkan katalog unit terdekat dengan harga sewa murah meriah.\", \"voiceover\": \"Untung sekarang ada Rentify! Kamu bisa sewa Musik & Band terbaik dengan harga mulai puluhan ribu aja per hari. Unit bersih, terawat, dan siap pakai!\"}, {\"detik\": \"20-30s\", \"adegan\": \"Call To Action\", \"visual\": \"Tampilan halaman depan web Rentify di layar smartphone dengan tombol \'Sewa Sekarang\' yang diklik.\", \"voiceover\": \"Ngapain beli mahal kalau bisa sewa hemat? Cek katalog terdekat dan booking sekarang juga di web Rentify!\"}]', '[{\"teks\": \"Mau tampil akustikan akhir pekan ini tapi belum punya gitar bagus?\", \"style\": \"Teks merah tebal font bold sans-serif\", \"posisi\": \"Tengah Atas\"}, {\"teks\": \"Beli: Rp 2.500.000 vs Sewa: Rp 45.000\", \"style\": \"Kotak komparasi kontras\", \"posisi\": \"Tengah\"}, {\"teks\": \"Sewa Mudah & Dekat di Rentify\", \"style\": \"Badge biru langit cerah\", \"posisi\": \"Bawah\"}, {\"teks\": \"👉 Buka rentify21.my.id Sekarang!\", \"style\": \"Tombol pill CTA bergerak\", \"posisi\": \"Bawah\"}]', 'Mau tampil akustikan akhir pekan ini tapi belum punya gitar bagus? 😱\n\nBanyak orang yang kejebak beli barang jutaan padahal cuma dipakai 1-2 kali setahun. Akhirnya? Menumpuk di gudang dan rusak dimakan waktu!\n\nStop boros! Mulai sekarang, kebutuhan sesekali seperti Musik & Band (Gitar akustik elektrik, keyboard piano, amplifier portable) cukup SEWA aja di Rentify. Tarif super hemat, barang berkualitas, dan langsung dekat dari lokasimu! ✨\n\n📲 Yuk cek katalog barang rental terdekat sekarang:\n🌐 https://rentify21.my.id\n\n#Rentify #SewaAja #SolusiHemat #RentalBarang #SewaKamera #SewaCamping #SewaPerkakas #AntiBeliMahal #GayaHidupHemat #RentalDekatKamu', 'Format 9:16 vertikal. Visual terbagi dua bagian split-screen: atas menampilkan dilema beli mahal, bawah menampilkan solusi sewa praktis di Rentify dengan warna dominan Sky Blue & Emerald Green.', NULL, 'Smart Heuristic Engine', 'ready', 0, 0, NULL, NULL, NULL, NULL, '2026-10-23 08:00:01', '2026-10-23 08:00:01');
/*!40000 ALTER TABLE `ai_marketing_posts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `banners`
--
DROP TABLE IF EXISTS `banners`;
CREATE TABLE `banners` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `judul_promo` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `gambar_url` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `banners`
--
LOCK TABLES `banners` WRITE;
/*!40000 ALTER TABLE `banners` DISABLE KEYS */;
INSERT INTO `banners` (`id`, `judul_promo`, `gambar_url`, `created_at`, `updated_at`) VALUES
(1, 1, 'https://res.cloudinary.com/fnf8f1pm/image/upload/v1790930129/rentify/banners/rncptmvfqlfrbke5nh8v.jpg', '2026-10-02 08:35:30', '2026-10-02 08:35:30');
/*!40000 ALTER TABLE `banners` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `barang`
--
DROP TABLE IF EXISTS `barang`;
CREATE TABLE `barang` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `vendor_id` bigint unsigned NOT NULL,
  `kategori_id` bigint unsigned DEFAULT NULL,
  `nama` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `deskripsi` text COLLATE utf8mb4_unicode_ci,
  `spesifikasi` json DEFAULT NULL,
  `harga_sewa_harian` decimal(15,2) NOT NULL,
  `deposit` decimal(15,2) NOT NULL DEFAULT '0.00',
  `denda_per_hari` decimal(15,2) NOT NULL DEFAULT '0.00',
  `kondisi` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `stok_total` int NOT NULL DEFAULT '0',
  `cover_photo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'tersedia',
  `is_delivery_supported` tinyint(1) NOT NULL DEFAULT '1',
  `alamat` text COLLATE utf8mb4_unicode_ci,
  `latitude` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `longitude` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_approved` tinyint(1) NOT NULL DEFAULT '0',
  `approved_at` datetime DEFAULT NULL,
  `cover_image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `status_barang` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  PRIMARY KEY (`id`),
  UNIQUE KEY `barang_slug_unique` (`slug`),
  KEY `barang_vendor_id_foreign` (`vendor_id`),
  CONSTRAINT `barang_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `barang`
--
LOCK TABLES `barang` WRITE;
/*!40000 ALTER TABLE `barang` DISABLE KEYS */;
INSERT INTO `barang` (`id`, `vendor_id`, `kategori_id`, `nama`, `slug`, `deskripsi`, `spesifikasi`, `harga_sewa_harian`, `deposit`, `denda_per_hari`, `kondisi`, `stok_total`, `cover_photo`, `status`, `is_delivery_supported`, `alamat`, `latitude`, `longitude`, `is_approved`, `approved_at`, `cover_image`, `created_at`, `updated_at`, `status_barang`) VALUES
(10, 16, 110, 'Kamera Canon M50', 'kamera-canon-m50-4430', 'Hehe', NULL, 100000.00, 0.00, 70000.00, 'Sangat Baik', 3, 'https://res.cloudinary.com/fnf8f1pm/image/upload/v1790928970/rentify/barang/zvdnnm8vo52rxe7xxjs7.png', 'tersedia', 1, 'Universitas Malikussaleh, First, Jalan Tgk. Abdul Wahab Dahlawy, Reuleut Timu, Sawang, Aceh Utara, Aceh, 23442, Indonesia', 5.2315083, 96.9806555, 1, '2026-10-02 08:17:36', NULL, '2026-10-02 08:16:11', '2026-10-03 17:20:55', 'disetujui'),
(11, 4, 108, 'bola', 'bola-4830', 'bola voli', '{\"merek\": null, \"tipe_kos\": \"Campur\", \"tipe_sewa\": \"keduanya\", \"transmisi\": \"Automatic\", \"bahan_bakar\": \"Bensin\", \"opsi_driver\": \"Lepas Kunci Saja\", \"ukuran_kamar\": \"3 x 4 m\", \"fitur_outdoor\": \"Waterproof PU 3000mm, Double Layer\", \"harga_bulanan\": null, \"jenis_hiburan\": \"Bola voli\", \"kondisi_detail\": \"Fungsi Normal 100%, Sensor Bersih Bebas Jamur\", \"tipe_kendaraan\": \"Mobil\", \"tahun_kendaraan\": \"2023\", \"tipe_kamar_mandi\": \"Dalam\", \"kapasitas_outdoor\": \"4 Orang (Dome Tent)\", \"kapasitas_penumpang\": \"4-5 Kursi\", \"kelengkapan_have_fun\": \"1 bola\"}', 15000.00, 0.00, 0.00, 'Baik', 1, 'https://res.cloudinary.com/fnf8f1pm/image/upload/v1791090849/rentify/barang/epir3hg5l8m80lkoxbpb.jpg', 'tersedia', 1, 'Tambonbaroh, Dewantara, Aceh Utara, Aceh, 24354, Indonesia', 5.2385046, 97.0272635, 1, '2026-10-04 05:17:29', NULL, '2026-10-04 05:14:10', '2026-10-04 05:17:29', 'disetujui');
/*!40000 ALTER TABLE `barang` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cabangs`
--
DROP TABLE IF EXISTS `cabangs`;
CREATE TABLE `cabangs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `vendor_id` bigint unsigned NOT NULL,
  `nama_cabang` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `alamat_lengkap` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `koordinat` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `cabangs_vendor_id_foreign` (`vendor_id`),
  CONSTRAINT `cabangs_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cabangs`
--
LOCK TABLES `cabangs` WRITE;
/*!40000 ALTER TABLE `cabangs` DISABLE KEYS */;
/*!40000 ALTER TABLE `cabangs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache`
--
DROP TABLE IF EXISTS `cache`;
CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cache`
--
LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES
('laravel-cache-sec_alert_cooldown_0e649e384b0fca2d2a4a914495f41ff1', 'b:1;', 1792744361),
('laravel-cache-wa_guardian_history_6282183394549', 'a:2:{i:0;a:2:{s:4:\"role\";s:4:\"user\";s:4:\"text\";s:37:\"p\nwhy ad ap\np\nwhy ad ap\np\nwhy ad ap\np\";}i:1;a:2:{s:4:\"role\";s:9:\"assistant\";s:4:\"text\";s:36:\"Rules:**\n    *   1-2 sentences? Yes.\";}}', 1792756759),
('laravel-cache-wa_guardian_history_6283183494835', 'a:2:{i:0;a:2:{s:4:\"role\";s:4:\"user\";s:4:\"text\";s:17:\"bagaimana keadaan\";}i:1;a:2:{s:4:\"role\";s:9:\"assistant\";s:4:\"text\";s:54:\". Website beroperasi lancar, database Aiven terhubung,\";}}', 1792750541);
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_locks`
--
DROP TABLE IF EXISTS `cache_locks`;
CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cache_locks`
--
LOCK TABLES `cache_locks` WRITE;
/*!40000 ALTER TABLE `cache_locks` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_locks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `chats`
--
DROP TABLE IF EXISTS `chats`;
CREATE TABLE `chats` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `pengirim_id` bigint unsigned NOT NULL,
  `penerima_id` bigint unsigned NOT NULL,
  `order_id` bigint unsigned DEFAULT NULL,
  `pesan` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `chats_pengirim_id_foreign` (`pengirim_id`),
  KEY `chats_penerima_id_foreign` (`penerima_id`),
  KEY `chats_order_id_foreign` (`order_id`),
  CONSTRAINT `chats_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `chats_penerima_id_foreign` FOREIGN KEY (`penerima_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `chats_pengirim_id_foreign` FOREIGN KEY (`pengirim_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `chats`
--
LOCK TABLES `chats` WRITE;
/*!40000 ALTER TABLE `chats` DISABLE KEYS */;
/*!40000 ALTER TABLE `chats` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--
DROP TABLE IF EXISTS `failed_jobs`;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `failed_jobs`
--
LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `foto_barangs`
--
DROP TABLE IF EXISTS `foto_barangs`;
CREATE TABLE `foto_barangs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `barang_id` bigint unsigned NOT NULL,
  `foto_path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `foto_barangs_barang_id_foreign` (`barang_id`),
  CONSTRAINT `foto_barangs_barang_id_foreign` FOREIGN KEY (`barang_id`) REFERENCES `barang` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `foto_barangs`
--
LOCK TABLES `foto_barangs` WRITE;
/*!40000 ALTER TABLE `foto_barangs` DISABLE KEYS */;
INSERT INTO `foto_barangs` (`id`, `barang_id`, `foto_path`, `created_at`, `updated_at`) VALUES
(1, 10, 'https://res.cloudinary.com/fnf8f1pm/image/upload/v1790928972/rentify/barang/galeri/qggrjrfztflob3lvuhib.jpg', '2026-10-02 08:16:12', '2026-10-02 08:16:12');
/*!40000 ALTER TABLE `foto_barangs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `job_batches`
--
DROP TABLE IF EXISTS `job_batches`;
CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `job_batches`
--
LOCK TABLES `job_batches` WRITE;
/*!40000 ALTER TABLE `job_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `job_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jobs`
--
DROP TABLE IF EXISTS `jobs`;
CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `jobs`
--
LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kategoris`
--
DROP TABLE IF EXISTS `kategoris`;
CREATE TABLE `kategoris` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nama` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=113 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `kategoris`
--
LOCK TABLES `kategoris` WRITE;
/*!40000 ALTER TABLE `kategoris` DISABLE KEYS */;
INSERT INTO `kategoris` (`id`, `nama`, `is_active`, `created_at`, `updated_at`) VALUES
(108, 'Peralatan Have Fun', 1, '2026-10-02 08:10:00', '2026-10-02 08:10:00'),
(109, 'Outdoor', 1, '2026-10-02 08:10:00', '2026-10-02 08:10:00'),
(110, 'Elektronik', 1, '2026-10-02 08:10:01', '2026-10-02 08:10:01'),
(111, 'Kendaraan', 1, '2026-10-02 08:10:01', '2026-10-02 08:10:01'),
(112, 'Kos / Kamar', 1, '2026-10-02 08:10:02', '2026-10-02 08:10:02');
/*!40000 ALTER TABLE `kategoris` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `keranjangs`
--
DROP TABLE IF EXISTS `keranjangs`;
CREATE TABLE `keranjangs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `barang_id` bigint unsigned NOT NULL,
  `jumlah` int NOT NULL DEFAULT '1',
  `durasi_sewa` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `keranjangs_user_id_foreign` (`user_id`),
  KEY `keranjangs_barang_id_foreign` (`barang_id`),
  CONSTRAINT `keranjangs_barang_id_foreign` FOREIGN KEY (`barang_id`) REFERENCES `barang` (`id`) ON DELETE CASCADE,
  CONSTRAINT `keranjangs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `keranjangs`
--
LOCK TABLES `keranjangs` WRITE;
/*!40000 ALTER TABLE `keranjangs` DISABLE KEYS */;
INSERT INTO `keranjangs` (`id`, `user_id`, `barang_id`, `jumlah`, `durasi_sewa`, `created_at`, `updated_at`) VALUES
(1, 6, 10, 2, 1, '2026-10-02 19:52:42', '2026-10-02 19:52:48');
/*!40000 ALTER TABLE `keranjangs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `komplains`
--
DROP TABLE IF EXISTS `komplains`;
CREATE TABLE `komplains` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `alasan` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `bukti_foto` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status_penyelesaian` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Menunggu Respon',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `komplains_order_id_foreign` (`order_id`),
  KEY `komplains_user_id_foreign` (`user_id`),
  CONSTRAINT `komplains_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `komplains_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `komplains`
--
LOCK TABLES `komplains` WRITE;
/*!40000 ALTER TABLE `komplains` DISABLE KEYS */;
/*!40000 ALTER TABLE `komplains` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--
DROP TABLE IF EXISTS `migrations`;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=45 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--
LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2026_06_30_000001_create_users_table', 1),
(2, '2026_06_30_030448_create_orders_table', 1),
(3, '2026_06_30_030449_create_order_items_table', 1),
(4, '2026_06_30_104211_add_vendor_status_to_users_table', 1),
(5, '2026_06_30_143047_create_cache_table', 1),
(6, '2026_06_30_143048_create_jobs_table', 1),
(7, '2026_07_01_175623_create_barang_table', 1),
(8, '2026_07_02_135002_create_banners_table', 1),
(9, '2026_07_02_141316_add_status_barang_to_barang_table', 1),
(10, '2026_07_04_103434_create_kategoris_table', 1),
(11, '2026_07_04_111241_add_cover_photo_to_barang_table', 1),
(12, '2026_07_04_170639_create_foto_barangs_table', 1),
(13, '2026_07_05_044316_create_keranjangs_table', 1),
(14, '2026_07_05_182042_create_wishlists_table', 1),
(15, '2026_07_06_165028_create_saldos_table', 1),
(16, '2026_07_06_165031_create_withdrawals_table', 1),
(17, '2026_07_08_121901_add_foto_profil_to_users_table', 1),
(18, '2026_07_08_174445_add_multivendor_columns', 1),
(19, '2026_07_08_183844_add_lokasi_produk_to_barang', 1),
(20, '2026_07_11_093849_add_jaminan_to_orders', 1),
(21, '2026_07_11_114430_add_fee_to_orders', 1),
(22, '2026_09_22_201044_create_vouchers_table', 1),
(23, '2026_09_22_203552_add_voucher_columns_to_penyewaans_table', 1),
(24, '2026_09_26_104650_create_pembayarans_table', 1),
(25, '2026_09_26_104651_create_pengembalians_table', 1),
(26, '2026_09_26_104651_create_ulasans_table', 1),
(27, '2026_09_26_104652_create_chats_table', 1),
(28, '2026_09_26_104652_create_komplains_table', 1),
(29, '2026_09_26_104653_create_cabangs_table', 1),
(30, '2026_09_26_111406_add_alamat_lengkap_to_users_table', 2),
(31, '2026_09_26_125841_add_google_id_and_whatsapp_to_users_table', 3),
(32, '2026_09_26_171324_add_whatsapp_verified_at_to_users_table', 4),
(33, '2026_09_27_050518_add_password_changed_at_to_users_table', 5),
(34, '2026_10_01_141412_add_compatibility_columns_to_orders_table', 6),
(40, '2026_10_04_000001_add_doku_columns_to_orders_and_pembayarans', 7),
(41, '2026_10_20_000001_create_ai_marketing_posts_table', 8),
(42, '2026_10_22_000001_create_whatsapp_sessions_table', 9),
(43, '2026_10_22_000002_create_mutasis_table', 10),
(44, '2026_10_22_000003_add_kode_unik_to_orders_table', 10);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mutasis`
--
DROP TABLE IF EXISTS `mutasis`;
CREATE TABLE `mutasis` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint unsigned DEFAULT NULL,
  `app_name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'DANA',
  `package_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tipe` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'CR',
  `nominal` decimal(15,2) NOT NULL,
  `sender` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'UNMATCHED',
  `catatan` text COLLATE utf8mb4_unicode_ci,
  `raw_payload` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `mutasis_order_id_index` (`order_id`),
  KEY `mutasis_nominal_index` (`nominal`),
  CONSTRAINT `mutasis_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `mutasis`
--
LOCK TABLES `mutasis` WRITE;
/*!40000 ALTER TABLE `mutasis` DISABLE KEYS */;
INSERT INTO `mutasis` (`id`, `order_id`, `app_name`, `package_name`, `tipe`, `nominal`, `sender`, `title`, `content`, `status`, `catatan`, `raw_payload`, `created_at`, `updated_at`) VALUES
(2, NULL, 'DANA', NULL, 'CR', 0.00, NULL, 'Pembayaran Masuk', '[not_text]', 'IGNORED', 'Diabaikan: Transaksi keluar/debit atau nominal tidak terdeteksi.', '{\"app\": \"DANA\", \"title\": \"Pembayaran Masuk\", \"content\": \"[not_text]\"}', '2026-10-22 16:05:16', '2026-10-22 16:05:16'),
(3, NULL, 'DANA', NULL, 'CR', 0.00, NULL, 'DANA', '[not_text]', 'IGNORED', 'Diabaikan: Transaksi keluar/debit atau nominal tidak terdeteksi.', '{\"app\": \"DANA\", \"title\": \"DANA\", \"content\": \"[not_text]\"}', '2026-10-23 02:24:03', '2026-10-23 02:24:03'),
(4, NULL, 'DANA', NULL, 'CR', 0.00, NULL, 'DANA', '[not_text]', 'IGNORED', 'Diabaikan: Transaksi keluar/debit atau nominal tidak terdeteksi.', '{\"app\": \"DANA\", \"title\": \"DANA\", \"content\": \"[not_text]\"}', '2026-10-23 12:07:47', '2026-10-23 12:07:48');
/*!40000 ALTER TABLE `mutasis` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order_items`
--
DROP TABLE IF EXISTS `order_items`;
CREATE TABLE `order_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint unsigned NOT NULL,
  `product_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `product_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `price` int NOT NULL,
  `quantity` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `order_items_order_id_foreign` (`order_id`),
  CONSTRAINT `order_items_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=58 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `order_items`
--
LOCK TABLES `order_items` WRITE;
/*!40000 ALTER TABLE `order_items` DISABLE KEYS */;
INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `product_name`, `price`, `quantity`, `created_at`, `updated_at`) VALUES
(1, 1, 10, 'Kamera Canon M50', 105000, 1, '2026-10-02 15:23:29', '2026-10-02 15:23:29'),
(2, 2, 10, 'Kamera Canon M50', 105000, 1, '2026-10-02 16:26:12', '2026-10-02 16:26:12'),
(3, 3, 10, 'Kamera Canon M50', 105000, 1, '2026-10-02 16:58:14', '2026-10-02 16:58:14'),
(4, 4, 10, 'Kamera Canon M50', 105000, 1, '2026-10-03 00:00:52', '2026-10-03 00:00:52'),
(5, 5, 10, 'Kamera Canon M50', 105000, 1, '2026-10-03 00:01:22', '2026-10-03 00:01:22'),
(6, 6, 10, 'Kamera Canon M50', 105000, 1, '2026-10-03 12:17:49', '2026-10-03 12:17:49'),
(7, 7, 10, 'Kamera Canon M50', 105000, 1, '2026-10-03 12:17:56', '2026-10-03 12:17:56'),
(8, 8, 10, 'Kamera Canon M50', 105000, 1, '2026-10-03 20:03:31', '2026-10-03 20:03:31'),
(9, 9, 10, 'Kamera Canon M50', 105000, 1, '2026-10-04 01:17:50', '2026-10-04 01:17:50'),
(10, 10, 10, 'Kamera Canon M50', 105000, 1, '2026-10-04 01:17:53', '2026-10-04 01:17:53'),
(11, 11, 10, 'Kamera Canon M50', 105000, 1, '2026-10-04 01:17:53', '2026-10-04 01:17:53'),
(12, 12, 10, 'Kamera Canon M50', 105000, 1, '2026-10-04 01:35:15', '2026-10-04 01:35:15'),
(13, 13, 10, 'Kamera Canon M50', 105000, 1, '2026-10-04 01:35:27', '2026-10-04 01:35:27'),
(14, 14, 10, 'Kamera Canon M50', 105000, 1, '2026-10-04 01:36:26', '2026-10-04 01:36:26'),
(15, 15, 10, 'Kamera Canon M50', 105000, 1, '2026-10-04 01:37:21', '2026-10-04 01:37:21'),
(16, 16, 10, 'Kamera Canon M50', 105000, 1, '2026-10-04 01:39:13', '2026-10-04 01:39:13'),
(17, 17, 10, 'Kamera Canon M50', 105000, 1, '2026-10-04 01:39:28', '2026-10-04 01:39:28'),
(18, 18, 10, 'Kamera Canon M50', 105000, 1, '2026-10-04 01:58:41', '2026-10-04 01:58:41'),
(19, 19, 10, 'Kamera Canon M50', 105000, 1, '2026-10-04 01:59:09', '2026-10-04 01:59:09'),
(20, 20, 10, 'Kamera Canon M50', 105000, 1, '2026-10-04 02:00:12', '2026-10-04 02:00:12'),
(21, 21, 10, 'Kamera Canon M50', 105000, 1, '2026-10-04 02:01:42', '2026-10-04 02:01:42'),
(22, 22, 10, 'Kamera Canon M50', 105000, 1, '2026-10-04 02:03:14', '2026-10-04 02:03:14'),
(23, 23, 10, 'Kamera Canon M50', 105000, 1, '2026-10-04 02:03:31', '2026-10-04 02:03:31'),
(24, 24, 10, 'Kamera Canon M50', 105000, 1, '2026-10-04 10:52:00', '2026-10-04 10:52:00'),
(25, 25, 10, 'Kamera Canon M50', 105000, 1, '2026-10-04 11:06:55', '2026-10-04 11:06:55'),
(26, 26, 11, 'bola', 15750, 1, '2026-10-04 12:19:54', '2026-10-04 12:19:54'),
(27, 27, 11, 'bola', 15750, 1, '2026-10-04 19:38:47', '2026-10-04 19:38:47'),
(28, 28, 11, 'bola', 15750, 1, '2026-10-04 19:39:39', '2026-10-04 19:39:39'),
(29, 29, 10, 'Kamera Canon M50', 105000, 1, '2026-10-05 12:44:02', '2026-10-05 12:44:02'),
(30, 30, 10, 'Kamera Canon M50', 105000, 1, '2026-10-05 13:29:03', '2026-10-05 13:29:03'),
(31, 31, 10, 'Kamera Canon M50', 105000, 1, '2026-10-06 18:17:15', '2026-10-06 18:17:15'),
(32, 32, 10, 'Kamera Canon M50', 105000, 1, '2026-10-06 22:05:08', '2026-10-06 22:05:08'),
(33, 33, 10, 'Kamera Canon M50', 105000, 1, '2026-10-16 14:29:24', '2026-10-16 14:29:24'),
(34, 34, 11, 'bola', 15750, 1, '2026-10-16 14:31:40', '2026-10-16 14:31:40'),
(35, 35, 10, 'Kamera Canon M50', 105000, 1, '2026-10-16 19:40:07', '2026-10-16 19:40:07'),
(36, 36, 10, 'Kamera Canon M50', 105000, 1, '2026-10-16 19:40:15', '2026-10-16 19:40:15'),
(37, 37, 10, 'Kamera Canon M50', 105000, 1, '2026-10-16 19:46:23', '2026-10-16 19:46:23'),
(38, 38, 10, 'Kamera Canon M50', 105000, 1, '2026-10-16 19:47:04', '2026-10-16 19:47:04'),
(39, 39, 11, 'bola', 15750, 1, '2026-10-16 19:47:34', '2026-10-16 19:47:34'),
(40, 40, 11, 'bola', 15750, 1, '2026-10-16 20:35:14', '2026-10-16 20:35:14'),
(41, 41, 11, 'bola', 15750, 1, '2026-10-16 20:36:33', '2026-10-16 20:36:33'),
(42, 42, 10, 'Kamera Canon M50', 105000, 1, '2026-10-16 20:37:00', '2026-10-16 20:37:00'),
(43, 43, 11, 'bola', 15750, 1, '2026-10-16 22:26:19', '2026-10-16 22:26:19'),
(44, 44, 10, 'Kamera Canon M50', 105000, 1, '2026-10-16 22:34:07', '2026-10-16 22:34:07'),
(45, 45, 10, 'Kamera Canon M50', 105000, 1, '2026-10-16 22:35:05', '2026-10-16 22:35:05'),
(46, 46, 10, 'Kamera Canon M50', 105000, 1, '2026-10-16 22:35:28', '2026-10-16 22:35:28'),
(47, 47, 11, 'bola', 15750, 1, '2026-10-16 22:37:29', '2026-10-16 22:37:29'),
(48, 48, 10, 'Kamera Canon M50', 105000, 1, '2026-10-18 03:31:52', '2026-10-18 03:31:52'),
(49, 49, 10, 'Kamera Canon M50', 105000, 1, '2026-10-18 03:34:55', '2026-10-18 03:34:55'),
(50, 50, 10, 'Kamera Canon M50', 105000, 1, '2026-10-18 03:38:44', '2026-10-18 03:38:44'),
(51, 51, 10, 'Kamera Canon M50', 105000, 1, '2026-10-18 03:39:33', '2026-10-18 03:39:33'),
(52, 52, 11, 'bola', 15750, 1, '2026-10-18 04:27:07', '2026-10-18 04:27:07'),
(53, 56, 10, 'Kamera Canon M50', 105000, 1, '2026-10-22 22:39:47', '2026-10-22 22:39:47'),
(54, 57, 11, 'bola', 15750, 1, '2026-10-22 23:04:31', '2026-10-22 23:04:31'),
(55, 58, 11, 'bola', 15750, 1, '2026-10-22 23:06:40', '2026-10-22 23:06:40'),
(56, 59, 11, 'bola', 15750, 1, '2026-10-22 23:44:37', '2026-10-22 23:44:37'),
(57, 60, 10, 'Kamera Canon M50', 105000, 1, '2026-10-23 03:17:11', '2026-10-23 03:17:11');
/*!40000 ALTER TABLE `order_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `orders`
--
DROP TABLE IF EXISTS `orders`;
CREATE TABLE `orders` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `vendor_id` bigint unsigned DEFAULT NULL,
  `kode_booking` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `doku_invoice_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Nomor invoice yang dikirim ke DOKU Checkout',
  `doku_payment_url` text COLLATE utf8mb4_unicode_ci COMMENT 'URL halaman pembayaran DOKU',
  `doku_payment_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'ID transaksi dari DOKU (dari webhook notification)',
  `payment_expired_at` timestamp NULL DEFAULT NULL COMMENT 'Batas waktu pembayaran (otomatis EXPIRED jika melewati ini)',
  `paid_at` timestamp NULL DEFAULT NULL COMMENT 'Waktu konfirmasi pembayaran sukses dari DOKU',
  `cabang_id` bigint unsigned DEFAULT NULL,
  `customer_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `customer_whatsapp` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `alamat_pengiriman` text COLLATE utf8mb4_unicode_ci,
  `shipping_address` text COLLATE utf8mb4_unicode_ci,
  `pin_location` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `metode_pengambilan` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `shipping_method` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `shipping_fee` int NOT NULL DEFAULT '0',
  `kode_unik` int NOT NULL DEFAULT '0' COMMENT '3-digit kode unik verifikasi mutasi QRIS otomatis',
  `tanggal_mulai` datetime NOT NULL,
  `start_rent` datetime DEFAULT NULL,
  `tanggal_selesai` datetime NOT NULL,
  `end_rent` datetime DEFAULT NULL,
  `tanggal_kembali_aktual` datetime DEFAULT NULL,
  `duration_days` int NOT NULL DEFAULT '1',
  `jaminan` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_method` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `voucher_id` bigint unsigned DEFAULT NULL,
  `total_biaya` decimal(15,2) NOT NULL,
  `total_price` decimal(15,2) DEFAULT NULL,
  `potongan_voucher` decimal(15,2) NOT NULL DEFAULT '0.00',
  `vendor_earning` decimal(15,2) NOT NULL DEFAULT '0.00',
  `rentify_fee` decimal(15,2) NOT NULL DEFAULT '0.00',
  `completed_at` datetime DEFAULT NULL,
  `total_deposit` decimal(15,2) NOT NULL DEFAULT '0.00',
  `total_denda` decimal(15,2) NOT NULL DEFAULT '0.00',
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Menunggu Konfirmasi',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `orders_kode_booking_unique` (`kode_booking`),
  KEY `orders_user_id_foreign` (`user_id`),
  KEY `orders_voucher_id_foreign` (`voucher_id`),
  CONSTRAINT `orders_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `orders_voucher_id_foreign` FOREIGN KEY (`voucher_id`) REFERENCES `vouchers` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=61 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `orders`
--
LOCK TABLES `orders` WRITE;
/*!40000 ALTER TABLE `orders` DISABLE KEYS */;
INSERT INTO `orders` (`id`, `user_id`, `vendor_id`, `kode_booking`, `doku_invoice_number`, `doku_payment_url`, `doku_payment_id`, `payment_expired_at`, `paid_at`, `cabang_id`, `customer_name`, `customer_whatsapp`, `alamat_pengiriman`, `shipping_address`, `pin_location`, `metode_pengambilan`, `shipping_method`, `shipping_fee`, `kode_unik`, `tanggal_mulai`, `start_rent`, `tanggal_selesai`, `end_rent`, `tanggal_kembali_aktual`, `duration_days`, `jaminan`, `payment_method`, `voucher_id`, `total_biaya`, `total_price`, `potongan_voucher`, `vendor_earning`, `rentify_fee`, `completed_at`, `total_deposit`, `total_denda`, `status`, `created_at`, `updated_at`) VALUES
(1, 4, 16, 'RNT6ABF6A01CD3BB', NULL, NULL, NULL, NULL, NULL, NULL, 'Joni Utama', '081262364197', 'Diambil di Toko', 'Diambil di Toko', NULL, 'ambil', 'ambil', 0, 0, '2026-10-02 13:00:00', '2026-10-02 13:00:00', '2026-10-03 13:00:00', '2026-10-03 13:00:00', NULL, 1, 'SIM', 'QRIS', NULL, 105000.00, 105000.00, 0.00, 100000.00, 5000.00, '2026-10-03 01:48:59', 0.00, 0.00, 'Selesai', '2026-10-02 15:23:29', '2026-10-03 01:48:59'),
(2, 4, 16, 'RNT6ABF78B50F5FF', NULL, NULL, NULL, NULL, NULL, NULL, 'Joni Utama', '081262364197', 'Diambil di Toko', 'Diambil di Toko', NULL, 'ambil', 'ambil', 0, 0, '2026-10-02 18:00:00', '2026-10-02 18:00:00', '2026-10-03 18:00:00', '2026-10-03 18:00:00', NULL, 1, 'SIM', 'QRIS', NULL, 105000.00, 105000.00, 0.00, 0.00, 0.00, NULL, 0.00, 0.00, 'Dibatalkan', '2026-10-02 16:26:12', '2026-10-03 20:55:19'),
(3, 4, 16, 'RNT6ABF8036F2B4A', NULL, NULL, NULL, NULL, NULL, NULL, 'Joni Utama', '081262364197', 'Diambil di Toko', 'Diambil di Toko', NULL, 'ambil', 'ambil', 0, 0, '2026-10-22 13:00:00', '2026-10-22 13:00:00', '2026-10-23 13:00:00', '2026-10-23 13:00:00', NULL, 1, 'KTP', 'QRIS', NULL, 105000.00, 105000.00, 0.00, 0.00, 0.00, NULL, 0.00, 0.00, 'Dibatalkan', '2026-10-02 16:58:14', '2026-10-03 20:55:19'),
(4, 4, 16, 'RNT6ABFE34487420', NULL, NULL, NULL, NULL, NULL, NULL, 'Joni Utama', '081262364197', 'CV. Celcom Jaya Pulsa, 26, Jalan Raya Medan Banda Aceh, Palohlada, Dewantara, Aceh Utara, Aceh, 24354, Indonesia', 'CV. Celcom Jaya Pulsa, 26, Jalan Raya Medan Banda Aceh, Palohlada, Dewantara, Aceh Utara, Aceh, 24354, Indonesia', '5.2365799,97.0238357', 'diantar', 'diantar', 20000, 0, '2026-10-22 13:00:00', '2026-10-22 13:00:00', '2026-10-23 13:00:00', '2026-10-23 13:00:00', NULL, 1, 'SIM', 'QRIS', NULL, 125000.00, 125000.00, 0.00, 0.00, 0.00, NULL, 0.00, 0.00, 'Dibatalkan', '2026-10-03 00:00:52', '2026-10-03 20:55:19'),
(5, 4, 16, 'RNT6ABFE362E656E', NULL, NULL, NULL, NULL, NULL, NULL, 'Joni Utama', '081262364197', 'CV. Celcom Jaya Pulsa, 26, Jalan Raya Medan Banda Aceh, Palohlada, Dewantara, Aceh Utara, Aceh, 24354, Indonesia', 'CV. Celcom Jaya Pulsa, 26, Jalan Raya Medan Banda Aceh, Palohlada, Dewantara, Aceh Utara, Aceh, 24354, Indonesia', '5.2365799,97.0238357', 'diantar', 'diantar', 20000, 0, '2026-10-22 13:00:00', '2026-10-22 13:00:00', '2026-10-23 13:00:00', '2026-10-23 13:00:00', NULL, 1, 'SIM', 'QRIS', NULL, 125000.00, 125000.00, 0.00, 0.00, 0.00, NULL, 0.00, 0.00, 'Dibatalkan', '2026-10-03 00:01:22', '2026-10-03 20:55:19'),
(6, 4, 16, 'RNT6AC08FFD7D831', NULL, NULL, NULL, NULL, NULL, NULL, 'Joni Utama', '081262364197', 'Diambil di Toko', 'Diambil di Toko', NULL, 'ambil', 'ambil', 0, 0, '2026-11-20 12:00:00', '2026-11-20 12:00:00', '2026-11-21 12:00:00', '2026-11-21 12:00:00', NULL, 1, 'KTP', 'QRIS', NULL, 105000.00, 105000.00, 0.00, 0.00, 0.00, NULL, 0.00, 0.00, 'Dibatalkan', '2026-10-03 12:17:49', '2026-10-03 20:55:19'),
(7, 4, 16, 'RNT6AC09004D4A56', NULL, NULL, NULL, NULL, NULL, NULL, 'Joni Utama', '081262364197', 'Diambil di Toko', 'Diambil di Toko', NULL, 'ambil', 'ambil', 0, 0, '2026-11-20 12:00:00', '2026-11-20 12:00:00', '2026-11-21 12:00:00', '2026-11-21 12:00:00', NULL, 1, 'KTP', 'QRIS', NULL, 105000.00, 105000.00, 0.00, 0.00, 0.00, NULL, 0.00, 0.00, 'Dibatalkan', '2026-10-03 12:17:56', '2026-10-03 20:55:19'),
(8, 17, 16, 'RNT6AC0FD23CB65C', NULL, NULL, NULL, NULL, NULL, NULL, 'Khairin Nisa', '082362232393', 'Diambil di Toko', 'Diambil di Toko', NULL, 'ambil', 'ambil', 0, 0, '2026-10-04 12:00:00', '2026-10-04 12:00:00', '2026-10-05 12:00:00', '2026-10-05 12:00:00', NULL, 1, 'KTP', 'COD', NULL, 105000.00, 105000.00, 0.00, 100000.00, 5000.00, '2026-10-03 20:37:04', 0.00, 0.00, 'Selesai', '2026-10-03 20:03:31', '2026-10-03 20:37:04'),
(9, 4, 16, 'RNT6AC146CED76A2', NULL, NULL, NULL, NULL, NULL, NULL, 'Joni Utama', '081262364197', 'Diambil di Toko', 'Diambil di Toko', NULL, 'ambil', 'ambil', 0, 0, '2026-10-08 13:00:00', '2026-10-08 13:00:00', '2026-10-09 13:00:00', '2026-10-09 13:00:00', NULL, 1, 'KTP', 'ONLINE', NULL, 105000.00, 105000.00, 0.00, 0.00, 0.00, NULL, 0.00, 0.00, 'Dibatalkan', '2026-10-04 01:17:50', '2026-10-03 20:55:19'),
(10, 4, 16, 'RNT6AC146D18FB18', NULL, NULL, NULL, NULL, NULL, NULL, 'Joni Utama', '081262364197', 'Diambil di Toko', 'Diambil di Toko', NULL, 'ambil', 'ambil', 0, 0, '2026-10-08 13:00:00', '2026-10-08 13:00:00', '2026-10-09 13:00:00', '2026-10-09 13:00:00', NULL, 1, 'KTP', 'ONLINE', NULL, 105000.00, 105000.00, 0.00, 0.00, 0.00, NULL, 0.00, 0.00, 'Dibatalkan', '2026-10-04 01:17:53', '2026-10-03 20:55:19'),
(11, 4, 16, 'RNT6AC146D1ED5EE', NULL, NULL, NULL, NULL, NULL, NULL, 'Joni Utama', '081262364197', 'Diambil di Toko', 'Diambil di Toko', NULL, 'ambil', 'ambil', 0, 0, '2026-10-08 13:00:00', '2026-10-08 13:00:00', '2026-10-09 13:00:00', '2026-10-09 13:00:00', NULL, 1, 'KTP', 'ONLINE', NULL, 105000.00, 105000.00, 0.00, 0.00, 0.00, NULL, 0.00, 0.00, 'Dibatalkan', '2026-10-04 01:17:53', '2026-10-03 20:55:19'),
(12, 4, 16, 'RNT6AC14AE38EBEC', NULL, NULL, NULL, NULL, NULL, NULL, 'Joni Utama', '081262364197', 'Diambil di Toko', 'Diambil di Toko', NULL, 'ambil', 'ambil', 0, 0, '2026-10-21 13:00:00', '2026-10-21 13:00:00', '2026-10-22 13:00:00', '2026-10-22 13:00:00', NULL, 1, 'SIM', 'EWALLET', NULL, 105000.00, 105000.00, 0.00, 0.00, 0.00, NULL, 0.00, 0.00, 'Dibatalkan', '2026-10-04 01:35:15', '2026-10-03 20:55:19'),
(13, 4, 16, 'RNT6AC14AEFA8956', NULL, NULL, NULL, NULL, NULL, NULL, 'Joni Utama', '081262364197', 'Diambil di Toko', 'Diambil di Toko', NULL, 'ambil', 'ambil', 0, 0, '2026-10-21 13:00:00', '2026-10-21 13:00:00', '2026-10-22 13:00:00', '2026-10-22 13:00:00', NULL, 1, 'SIM', 'EWALLET', NULL, 105000.00, 105000.00, 0.00, 0.00, 0.00, NULL, 0.00, 0.00, 'Dibatalkan', '2026-10-04 01:35:27', '2026-10-03 20:55:19'),
(14, 4, 16, 'RNT6AC14B2AACE5E', NULL, NULL, NULL, NULL, NULL, NULL, 'Joni Utama', '081262364197', 'Diambil di Toko', 'Diambil di Toko', NULL, 'ambil', 'ambil', 0, 0, '2026-10-21 13:00:00', '2026-10-21 13:00:00', '2026-10-22 13:00:00', '2026-10-22 13:00:00', NULL, 1, 'SIM', 'BANK', NULL, 105000.00, 105000.00, 0.00, 0.00, 0.00, NULL, 0.00, 0.00, 'Dibatalkan', '2026-10-04 01:36:26', '2026-10-03 20:55:19'),
(15, 4, 16, 'RNT6AC14B61A3D56', NULL, NULL, NULL, NULL, NULL, NULL, 'Joni Utama', '081262364197', 'Diambil di Toko', 'Diambil di Toko', NULL, 'ambil', 'ambil', 0, 0, '2026-10-26 13:00:00', '2026-10-26 13:00:00', '2026-10-27 13:00:00', '2026-10-27 13:00:00', NULL, 1, 'SIM', 'COD', NULL, 105000.00, 105000.00, 0.00, 0.00, 0.00, NULL, 0.00, 0.00, 'Menunggu Konfirmasi', '2026-10-04 01:37:21', '2026-10-04 01:37:21'),
(16, 4, 16, 'RNT6AC14BD1CD30A', NULL, NULL, NULL, NULL, NULL, NULL, 'Joni Utama', '081262364197', 'Diambil di Toko', 'Diambil di Toko', NULL, 'ambil', 'ambil', 0, 0, '2026-10-26 13:00:00', '2026-10-26 13:00:00', '2026-10-27 13:00:00', '2026-10-27 13:00:00', NULL, 1, 'SIM', 'QRIS', NULL, 105000.00, 105000.00, 0.00, 0.00, 0.00, NULL, 0.00, 0.00, 'Dibatalkan', '2026-10-04 01:39:13', '2026-10-03 20:55:19'),
(17, 4, 16, 'RNT6AC14BE0E5C93', NULL, NULL, NULL, NULL, NULL, NULL, 'Joni Utama', '081262364197', 'Diambil di Toko', 'Diambil di Toko', NULL, 'ambil', 'ambil', 0, 0, '2026-10-26 13:00:00', '2026-10-26 13:00:00', '2026-10-27 13:00:00', '2026-10-27 13:00:00', NULL, 1, 'SIM', 'BANK', NULL, 105000.00, 105000.00, 0.00, 0.00, 0.00, NULL, 0.00, 0.00, 'Dibatalkan', '2026-10-04 01:39:28', '2026-10-03 20:55:19'),
(18, 4, 16, 'RNT6AC15061ED2C5', NULL, NULL, NULL, NULL, NULL, NULL, 'Joni Utama', '081262364197', 'Diambil di Toko', 'Diambil di Toko', NULL, 'ambil', 'ambil', 0, 0, '2026-10-17 19:00:00', '2026-10-17 19:00:00', '2026-10-18 19:00:00', '2026-10-18 19:00:00', NULL, 1, 'SIM', 'BANK_MANDIRI', NULL, 105000.00, 105000.00, 0.00, 0.00, 0.00, NULL, 0.00, 0.00, 'Dibatalkan', '2026-10-04 01:58:41', '2026-10-03 20:55:19'),
(19, 4, 16, 'RNT6AC1507D31E9A', NULL, NULL, NULL, NULL, NULL, NULL, 'Joni Utama', '081262364197', 'Diambil di Toko', 'Diambil di Toko', NULL, 'ambil', 'ambil', 0, 0, '2026-10-17 19:00:00', '2026-10-17 19:00:00', '2026-10-18 19:00:00', '2026-10-18 19:00:00', NULL, 1, 'SIM', 'QRIS', NULL, 105000.00, 105000.00, 0.00, 0.00, 0.00, NULL, 0.00, 0.00, 'Dibatalkan', '2026-10-04 01:59:09', '2026-10-03 20:55:19'),
(20, 4, 16, 'RNT6AC150BC5A63A', NULL, NULL, NULL, NULL, NULL, NULL, 'Joni Utama', '081262364197', 'Diambil di Toko', 'Diambil di Toko', NULL, 'ambil', 'ambil', 0, 0, '2026-10-17 19:00:00', '2026-10-17 19:00:00', '2026-10-18 19:00:00', '2026-10-18 19:00:00', NULL, 1, 'SIM', 'GOPAY', NULL, 105000.00, 105000.00, 0.00, 0.00, 0.00, NULL, 0.00, 0.00, 'Dibatalkan', '2026-10-04 02:00:12', '2026-10-03 20:55:19'),
(21, 4, 16, 'RNT6AC1511649850', NULL, NULL, NULL, NULL, NULL, NULL, 'Joni Utama', '081262364197', 'Diambil di Toko', 'Diambil di Toko', NULL, 'ambil', 'ambil', 0, 0, '2026-10-04 15:00:00', '2026-10-04 15:00:00', '2026-10-05 15:00:00', '2026-10-05 15:00:00', NULL, 1, 'SIM', 'QRIS', NULL, 105000.00, 105000.00, 0.00, 0.00, 0.00, NULL, 0.00, 0.00, 'Dibatalkan', '2026-10-04 02:01:42', '2026-10-03 20:55:19'),
(22, 4, 16, 'RNT6AC15172249A1', NULL, NULL, NULL, NULL, NULL, NULL, 'Joni Utama', '081262364197', 'Diambil di Toko', 'Diambil di Toko', NULL, 'ambil', 'ambil', 0, 0, '2026-10-04 15:00:00', '2026-10-04 15:00:00', '2026-10-05 15:00:00', '2026-10-05 15:00:00', NULL, 1, 'SIM', 'BANK_MANDIRI', NULL, 105000.00, 105000.00, 0.00, 0.00, 0.00, NULL, 0.00, 0.00, 'Dibatalkan', '2026-10-04 02:03:14', '2026-10-03 20:55:19'),
(23, 4, 16, 'RNT6AC1518414B92', NULL, NULL, NULL, NULL, NULL, NULL, 'Joni Utama', '081262364197', 'Diambil di Toko', 'Diambil di Toko', NULL, 'ambil', 'ambil', 0, 0, '2026-10-04 15:00:00', '2026-10-04 15:00:00', '2026-10-05 15:00:00', '2026-10-05 15:00:00', NULL, 1, 'SIM', 'BANK_MANDIRI', NULL, 105000.00, 105000.00, 0.00, 0.00, 0.00, NULL, 0.00, 0.00, 'Dibatalkan', '2026-10-04 02:03:31', '2026-10-03 20:55:19'),
(24, 4, 16, 'RNT6AC1CD607250B', NULL, NULL, NULL, NULL, NULL, NULL, 'Joni Utama', '081262364197', 'Diambil di Toko', 'Diambil di Toko', NULL, 'ambil', 'ambil', 0, 0, '2026-10-16 14:00:00', '2026-10-16 14:00:00', '2026-10-17 14:00:00', '2026-10-17 14:00:00', NULL, 1, 'SIM', 'QRIS', NULL, 105000.00, 105000.00, 0.00, 0.00, 0.00, NULL, 0.00, 0.00, 'Dibatalkan', '2026-10-04 10:52:00', '2026-10-04 04:06:55'),
(25, 4, 16, 'RNT6AC1D0DFA9D8A', NULL, NULL, NULL, NULL, NULL, NULL, 'Joni Utama', '081262364197', 'Diambil di Toko', 'Diambil di Toko', NULL, 'ambil', 'ambil', 0, 0, '2026-10-16 14:00:00', '2026-10-16 14:00:00', '2026-10-17 14:00:00', '2026-10-17 14:00:00', NULL, 1, 'SIM', 'COD', NULL, 105000.00, 105000.00, 0.00, 0.00, 0.00, NULL, 0.00, 0.00, 'Menunggu Konfirmasi', '2026-10-04 11:06:55', '2026-10-04 11:06:55'),
(26, 4, 4, 'RNT6AC1E1FA5CCF8', NULL, NULL, NULL, NULL, NULL, NULL, 'Joni Utama', '081262364197', 'Diambil di Toko', 'Diambil di Toko', NULL, 'ambil', 'ambil', 0, 0, '2026-10-08 15:00:00', '2026-10-08 15:00:00', '2026-10-09 15:00:00', '2026-10-09 15:00:00', NULL, 1, 'SIM', 'COD', 1, 3150.00, 3150.00, 12600.00, 0.00, 0.00, NULL, 0.00, 0.00, 'Menunggu Konfirmasi', '2026-10-04 12:19:54', '2026-10-04 12:19:54'),
(27, 4, 4, 'RNT6AC248D7D32AB', NULL, NULL, NULL, NULL, NULL, NULL, 'Joni Utama', '081262364197', 'Diambil di Toko', 'Diambil di Toko', NULL, 'ambil', 'ambil', 0, 0, '2026-10-09 15:00:00', '2026-10-09 15:00:00', '2026-10-10 15:00:00', '2026-10-10 15:00:00', NULL, 1, 'SIM', 'QRIS', NULL, 15750.00, 15750.00, 0.00, 0.00, 0.00, NULL, 0.00, 0.00, 'CANCELLED', '2026-10-04 19:38:47', '2026-10-04 12:39:21'),
(28, 4, 4, 'RNT6AC2490B2F4C8', NULL, NULL, NULL, NULL, NULL, NULL, 'Joni Utama', '081262364197', 'Diambil di Toko', 'Diambil di Toko', NULL, 'ambil', 'ambil', 0, 0, '2026-10-23 15:00:00', '2026-10-23 15:00:00', '2026-10-24 15:00:00', '2026-10-24 15:00:00', NULL, 1, 'SIM', 'COD', NULL, 15750.00, 15750.00, 0.00, 0.00, 0.00, NULL, 0.00, 0.00, 'Menunggu Konfirmasi', '2026-10-04 19:39:39', '2026-10-04 19:39:39'),
(29, 4, 16, 'RNT6AC339253E03F', NULL, NULL, NULL, NULL, NULL, NULL, 'Joni Utama', '081262364197', 'Diambil di Toko', 'Diambil di Toko', NULL, 'ambil', 'ambil', 0, 0, '2026-10-16 15:00:00', '2026-10-16 15:00:00', '2026-10-17 15:00:00', '2026-10-17 15:00:00', NULL, 1, 'SIM', 'COD', NULL, 105000.00, 105000.00, 0.00, 0.00, 0.00, NULL, 0.00, 0.00, 'Menunggu Konfirmasi', '2026-10-05 12:44:02', '2026-10-05 12:44:02'),
(30, 4, 16, 'RNT6AC343B25BD72', NULL, NULL, NULL, NULL, NULL, NULL, 'Joni Utama', '081262364197', 'Diambil di Toko', 'Diambil di Toko', NULL, 'ambil', 'ambil', 0, 0, '2026-10-29 15:00:00', '2026-10-29 15:00:00', '2026-10-30 15:00:00', '2026-10-30 15:00:00', NULL, 1, 'SIM', 'QRIS', NULL, 105000.00, 105000.00, 0.00, 0.00, 0.00, NULL, 0.00, 0.00, 'CANCELLED', '2026-10-05 13:29:03', '2026-10-06 11:17:15'),
(31, 4, 16, 'RNT6AC4D8BC2C0B7', NULL, NULL, NULL, NULL, NULL, NULL, 'Joni Utama', '081262364197', 'Diambil di Toko', 'Diambil di Toko', NULL, 'ambil', 'ambil', 0, 0, '2026-10-22 15:00:00', '2026-10-22 15:00:00', '2026-10-23 15:00:00', '2026-10-23 15:00:00', NULL, 1, 'SIM', 'QRIS', NULL, 105000.00, 105000.00, 0.00, 0.00, 0.00, NULL, 0.00, 0.00, 'CANCELLED', '2026-10-06 18:17:15', '2026-10-06 15:05:08'),
(32, 4, 16, 'RNT6AC50E250CA36', NULL, NULL, NULL, NULL, NULL, NULL, 'Joni Utama', '081262364197', 'Diambil di Toko', 'Diambil di Toko', NULL, 'ambil', 'ambil', 0, 0, '2026-10-22 15:00:00', '2026-10-22 15:00:00', '2026-10-23 15:00:00', '2026-10-23 15:00:00', NULL, 1, 'SIM', 'QRIS', NULL, 105000.00, 105000.00, 0.00, 0.00, 0.00, NULL, 0.00, 0.00, 'CANCELLED', '2026-10-06 22:05:08', '2026-10-16 07:29:24'),
(33, 4, 16, 'RNT6AD1D255A5C15', NULL, NULL, NULL, NULL, NULL, NULL, 'Joni Utama', '081262364197', 'Diambil di Toko', 'Diambil di Toko', NULL, 'ambil', 'ambil', 0, 0, '2026-10-20 14:00:00', '2026-10-20 14:00:00', '2026-10-21 14:00:00', '2026-10-21 14:00:00', NULL, 1, 'SIM', 'QRIS', NULL, 105000.00, 105000.00, 0.00, 0.00, 0.00, NULL, 0.00, 0.00, 'CANCELLED', '2026-10-16 14:29:24', '2026-10-16 07:31:40'),
(34, 4, 4, 'RNT6AD1D2DD0747C', NULL, NULL, NULL, NULL, NULL, NULL, 'Joni Utama', '081262364197', 'Diambil di Toko', 'Diambil di Toko', NULL, 'ambil', 'ambil', 0, 0, '2026-10-22 14:00:00', '2026-10-22 14:00:00', '2026-10-23 14:00:00', '2026-10-23 14:00:00', NULL, 1, 'SIM', 'GOPAY', NULL, 15750.00, 15750.00, 0.00, 0.00, 0.00, NULL, 0.00, 0.00, 'CANCELLED', '2026-10-16 14:31:40', '2026-10-16 12:40:07'),
(35, 4, 16, 'RNT6AD21B283C2D1', NULL, NULL, NULL, NULL, NULL, NULL, 'Joni Utama', '081262364197', 'Diambil di Toko', 'Diambil di Toko', NULL, 'ambil', 'ambil', 0, 0, '2026-10-31 15:00:00', '2026-10-31 15:00:00', '2026-11-01 15:00:00', '2026-11-01 15:00:00', NULL, 1, 'SIM', 'QRIS', NULL, 105000.00, 105000.00, 0.00, 0.00, 0.00, NULL, 0.00, 0.00, 'CANCELLED', '2026-10-16 19:40:07', '2026-10-16 12:40:15'),
(36, 4, 16, 'RNT6AD21B2FB466A', NULL, NULL, NULL, NULL, NULL, NULL, 'Joni Utama', '081262364197', 'Diambil di Toko', 'Diambil di Toko', NULL, 'ambil', 'ambil', 0, 0, '2026-10-31 15:00:00', '2026-10-31 15:00:00', '2026-11-01 15:00:00', '2026-11-01 15:00:00', NULL, 1, 'SIM', 'QRIS', NULL, 105000.00, 105000.00, 0.00, 0.00, 0.00, NULL, 0.00, 0.00, 'CANCELLED', '2026-10-16 19:40:15', '2026-10-16 13:35:14'),
(37, 17, 16, 'RNT6AD21C9FB1AA0', NULL, NULL, NULL, NULL, NULL, NULL, 'Khairin Nisa', '082362232393', 'Diambil di Toko', 'Diambil di Toko', NULL, 'ambil', 'ambil', 0, 0, '2026-10-28 15:00:00', '2026-10-28 15:00:00', '2026-10-29 15:00:00', '2026-10-29 15:00:00', NULL, 1, 'KTP', 'QRIS', NULL, 105000.00, 105000.00, 0.00, 0.00, 0.00, NULL, 0.00, 0.00, 'CANCELLED', '2026-10-16 19:46:23', '2026-10-16 12:47:04'),
(38, 17, 16, 'RNT6AD21CC87D858', NULL, NULL, NULL, NULL, NULL, NULL, 'Khairin Nisa', '082362232393', 'Diambil di Toko', 'Diambil di Toko', NULL, 'ambil', 'ambil', 0, 0, '2026-10-28 15:00:00', '2026-10-28 15:00:00', '2026-10-29 15:00:00', '2026-10-29 15:00:00', NULL, 1, 'SIM', 'GOPAY', NULL, 105000.00, 105000.00, 0.00, 0.00, 0.00, NULL, 0.00, 0.00, 'CANCELLED', '2026-10-16 19:47:04', '2026-10-16 12:47:34'),
(39, 17, 4, 'RNT6AD21CE6C7BCC', NULL, NULL, NULL, NULL, NULL, NULL, 'Khairin Nisa', '082362232393', 'Diambil di Toko', 'Diambil di Toko', NULL, 'ambil', 'ambil', 0, 0, '2026-10-29 15:00:00', '2026-10-29 15:00:00', '2026-10-30 15:00:00', '2026-10-30 15:00:00', NULL, 1, 'SIM', 'BANK_MANDIRI', NULL, 15750.00, 15750.00, 0.00, 0.00, 0.00, NULL, 0.00, 0.00, 'CANCELLED', '2026-10-16 19:47:34', '2026-10-16 15:25:49'),
(40, 4, 4, 'RNT6AD22812E4753', NULL, NULL, NULL, NULL, NULL, NULL, 'Joni Utama', '081262364197', 'Diambil di Toko', 'Diambil di Toko', NULL, 'ambil', 'ambil', 0, 0, '2026-10-30 15:00:00', '2026-10-30 15:00:00', '2026-10-31 15:00:00', '2026-10-31 15:00:00', NULL, 1, 'KTP', 'GOPAY', NULL, 15750.00, 15750.00, 0.00, 0.00, 0.00, NULL, 0.00, 0.00, 'CANCELLED', '2026-10-16 20:35:14', '2026-10-16 13:35:56'),
(41, 4, 4, 'RNT6AD2286258897', NULL, NULL, NULL, NULL, NULL, NULL, 'Joni Utama', '081262364197', 'Diambil di Toko', 'Diambil di Toko', NULL, 'ambil', 'ambil', 0, 0, '2026-10-20 14:00:00', '2026-10-20 14:00:00', '2026-10-21 14:00:00', '2026-10-21 14:00:00', NULL, 1, 'SIM', 'COD', NULL, 15750.00, 15750.00, 0.00, 0.00, 0.00, NULL, 0.00, 0.00, 'Menunggu Konfirmasi', '2026-10-16 20:36:33', '2026-10-16 20:36:33'),
(42, 4, 16, 'RNT6AD2287D4366A', NULL, NULL, NULL, NULL, NULL, NULL, 'Joni Utama', '081262364197', 'Diambil di Toko', 'Diambil di Toko', NULL, 'ambil', 'ambil', 0, 0, '2026-10-21 14:00:00', '2026-10-21 14:00:00', '2026-10-22 14:00:00', '2026-10-22 14:00:00', NULL, 1, 'KTP', 'QRIS', NULL, 105000.00, 105000.00, 0.00, 0.00, 0.00, NULL, 0.00, 0.00, 'CANCELLED', '2026-10-16 20:37:00', '2026-10-16 15:34:07'),
(43, 17, 4, 'RNT6AD2421BB1451', NULL, NULL, NULL, NULL, NULL, NULL, 'Khairin Nisa', '082362232393', 'Diambil di Toko', 'Diambil di Toko', NULL, 'ambil', 'ambil', 0, 0, '2026-10-25 15:00:00', '2026-10-25 15:00:00', '2026-10-28 15:00:00', '2026-10-28 15:00:00', NULL, 3, 'KTP', 'GOPAY', NULL, 47250.00, 47250.00, 0.00, 45000.00, 2250.00, '2026-10-18 16:53:04', 0.00, 0.00, 'Selesai', '2026-10-16 22:26:19', '2026-10-18 16:53:04'),
(44, 4, 16, 'RNT6AD243F027FBA', NULL, NULL, NULL, NULL, NULL, NULL, 'Joni Utama', '081262364197', 'Diambil di Toko', 'Diambil di Toko', NULL, 'ambil', 'ambil', 0, 0, '2026-10-18 13:00:00', '2026-10-18 13:00:00', '2026-10-19 13:00:00', '2026-10-19 13:00:00', NULL, 1, 'SIM', 'QRIS', NULL, 105000.00, 105000.00, 0.00, 0.00, 0.00, NULL, 0.00, 0.00, 'CANCELLED', '2026-10-16 22:34:07', '2026-10-16 15:35:05'),
(45, 4, 16, 'RNT6AD2442A11E0B', NULL, NULL, NULL, NULL, NULL, NULL, 'Joni Utama', '081262364197', 'Diambil di Toko', 'Diambil di Toko', NULL, 'ambil', 'ambil', 0, 0, '2026-10-25 13:00:00', '2026-10-25 13:00:00', '2026-10-26 13:00:00', '2026-10-26 13:00:00', NULL, 1, 'SIM', 'COD', NULL, 105000.00, 105000.00, 0.00, 0.00, 0.00, NULL, 0.00, 0.00, 'Menunggu Konfirmasi', '2026-10-16 22:35:05', '2026-10-16 22:35:05'),
(46, 4, 16, 'RNT6AD24441611BB', NULL, NULL, NULL, NULL, NULL, NULL, 'Joni Utama', '081262364197', 'Diambil di Toko', 'Diambil di Toko', NULL, 'ambil', 'ambil', 0, 0, '2026-10-18 13:00:00', '2026-10-18 13:00:00', '2026-10-19 13:00:00', '2026-10-19 13:00:00', NULL, 1, 'SIM', 'QRIS', NULL, 105000.00, 105000.00, 0.00, 0.00, 0.00, NULL, 0.00, 0.00, 'CANCELLED', '2026-10-16 22:35:28', '2026-10-17 20:31:52'),
(47, 17, 4, 'RNT6AD244B97E29B', NULL, NULL, NULL, NULL, NULL, NULL, 'Khairin Nisa', '082362232393', 'Diambil di Toko', 'Diambil di Toko', NULL, 'ambil', 'ambil', 0, 0, '2026-11-19 12:00:00', '2026-11-19 12:00:00', '2026-11-20 12:00:00', '2026-11-20 12:00:00', NULL, 1, 'SIM', 'QRIS', NULL, 15750.00, 15750.00, 0.00, 0.00, 0.00, NULL, 0.00, 0.00, 'EXPIRED', '2026-10-16 22:37:29', '2026-10-17 20:31:52'),
(48, 4, 16, 'RNT6AD3DB391FD20', NULL, 'https://api.sandbox.midtrans.com/v2/qris/8a183d91-21bb-46d0-97a0-93e9118a6715/qr-code', NULL, NULL, NULL, NULL, 'Joni Utama', '081262364197', 'Diambil di Toko', 'Diambil di Toko', NULL, 'ambil', 'ambil', 0, 0, '2026-10-22 14:00:00', '2026-10-22 14:00:00', '2026-10-23 14:00:00', '2026-10-23 14:00:00', NULL, 1, 'SIM', 'COD', NULL, 105000.00, 105000.00, 0.00, 0.00, 0.00, NULL, 0.00, 0.00, 'Menunggu Konfirmasi', '2026-10-18 03:31:52', '2026-10-18 03:34:04'),
(49, 4, 16, 'RNT6AD3DBEF9E91C', NULL, 'https://simulator.sandbox.midtrans.com/v2/deeplink/detail?tref=A120261017203456ECXDpcLj6LID&callbackUrl=https%3A%2F%2Frentify21.my.id%2Fcustomer%2Fstruk%2FINV-49%3Forder_id%3DRNT6AD3DBEF9E91C', NULL, NULL, NULL, NULL, 'Joni Utama', '081262364197', 'Diambil di Toko', 'Diambil di Toko', NULL, 'ambil', 'ambil', 0, 0, '2026-10-30 14:00:00', '2026-10-30 14:00:00', '2026-10-31 14:00:00', '2026-10-31 14:00:00', NULL, 1, 'SIM', 'GOPAY', NULL, 105000.00, 105000.00, 0.00, 0.00, 0.00, NULL, 0.00, 0.00, 'Menunggu Konfirmasi', '2026-10-18 03:34:55', '2026-10-17 20:36:06'),
(50, 4, 16, 'RNT6AD3DCD573DB2', NULL, 'https://api.sandbox.midtrans.com/v2/qris/3ef8120b-d8cc-46e5-a231-8af9a4cf4403/qr-code', NULL, NULL, NULL, NULL, 'Joni Utama', '081262364197', 'Diambil di Toko', 'Diambil di Toko', NULL, 'ambil', 'ambil', 0, 0, '2026-10-27 16:00:00', '2026-10-27 16:00:00', '2026-10-28 16:00:00', '2026-10-28 16:00:00', NULL, 1, 'SIM', 'QRIS', NULL, 105000.00, 105000.00, 0.00, 0.00, 0.00, NULL, 0.00, 0.00, 'CANCELLED', '2026-10-18 03:38:44', '2026-10-17 20:39:11'),
(51, 4, 16, 'RNT6AD3DD065E979', NULL, 'https://api.sandbox.midtrans.com/v2/qris/1530260d-e0c2-4a62-b716-d57d694735cf/qr-code', NULL, NULL, NULL, NULL, 'Joni Utama', '081262364197', 'Diambil di Toko', 'Diambil di Toko', NULL, 'ambil', 'ambil', 0, 0, '2026-10-26 16:00:00', '2026-10-26 16:00:00', '2026-10-27 16:00:00', '2026-10-27 16:00:00', NULL, 1, 'SIM', 'QRIS', NULL, 105000.00, 105000.00, 0.00, 0.00, 0.00, NULL, 0.00, 0.00, 'CANCELLED', '2026-10-18 03:39:33', '2026-10-17 21:26:21'),
(52, 4, 4, 'RNT6AD3E82BA9F2F', NULL, 'https://api.sandbox.midtrans.com/v2/qris/ce4ef3ec-6754-4cc5-885c-3d43e1da1738/qr-code', NULL, NULL, '2026-10-17 21:27:43', NULL, 'Joni Utama', '081262364197', 'Diambil di Toko', 'Diambil di Toko', NULL, 'ambil', 'ambil', 0, 0, '2026-11-20 13:00:00', '2026-11-20 13:00:00', '2026-11-21 13:00:00', '2026-11-21 13:00:00', NULL, 1, 'SIM', 'QRIS', NULL, 15750.00, 15750.00, 0.00, 0.00, 0.00, NULL, 0.00, 0.00, 'Menunggu Konfirmasi', '2026-10-18 04:27:07', '2026-10-17 21:27:43'),
(54, 1, NULL, 'TEST-6ada28e0af3d5', NULL, NULL, NULL, NULL, NULL, NULL, 'Customer Test', '081234567890', NULL, NULL, NULL, 'ambil', NULL, 0, 876, '2026-10-22 15:16:48', NULL, '2026-10-24 15:16:48', NULL, NULL, 1, NULL, 'QRIS', NULL, 49876.00, 49876.00, 0.00, 0.00, 0.00, NULL, 0.00, 0.00, 'EXPIRED', '2026-10-22 15:16:48', '2026-10-22 16:04:31'),
(56, 4, 16, 'RNT6ADA2E4400C36', 'RNT6ADA2E4400C36', 'https://api.qrserver.com/v1/create-qr-code/?size=350x350&margin=15&data=00020101021226570011ID.DANA.WWW011893600915300617401802090061740180303UMI51440014ID.CO.QRIS.WWW0215ID10265872193240303UMI52047394530336054061054475802ID5912Rental+kursi600416056105316646304840D', NULL, NULL, NULL, NULL, 'Joni Utama', '081262364197', 'Diambil di Toko', 'Diambil di Toko', NULL, 'ambil', 'ambil', 0, 447, '2026-11-27 17:00:00', '2026-11-27 17:00:00', '2026-11-28 17:00:00', '2026-11-28 17:00:00', NULL, 1, 'SIM', 'QRIS', NULL, 105447.00, 105447.00, 0.00, 0.00, 0.00, NULL, 0.00, 0.00, 'CANCELLED', '2026-10-22 22:39:47', '2026-10-22 16:44:23'),
(57, 17, 4, 'RNT6ADA341026BBA', 'RNT6ADA341026BBA', 'https://api.qrserver.com/v1/create-qr-code/?size=350x350&margin=15&data=00020101021226570011ID.DANA.WWW011893600915300617401802090061740180303UMI51440014ID.CO.QRIS.WWW0215ID10265872193240303UMI5204739453033605405159715802ID5912Rental+kursi600416056105316646304FCE8', NULL, NULL, NULL, NULL, 'Khairin Nisa', '082362232393', 'Diambil di Toko', 'Diambil di Toko', NULL, 'ambil', 'ambil', 0, 221, '2026-11-27 14:00:00', '2026-11-27 14:00:00', '2026-11-28 14:00:00', '2026-11-28 14:00:00', NULL, 1, 'SIM', 'QRIS', NULL, 15971.00, 15971.00, 0.00, 0.00, 0.00, NULL, 0.00, 0.00, 'Menunggu Konfirmasi', '2026-10-22 23:04:31', '2026-10-22 16:05:17'),
(58, 17, 4, 'RNT6ADA3490BE1F0', 'RNT6ADA3490BE1F0', 'https://api.qrserver.com/v1/create-qr-code/?size=350x350&margin=15&data=00020101021226570011ID.DANA.WWW011893600915300617401802090061740180303UMI51440014ID.CO.QRIS.WWW0215ID10265872193240303UMI5204739453033605405164845802ID5912Rental+kursi60041605610531664630403D5', NULL, NULL, NULL, NULL, 'Khairin Nisa', '082362232393', 'Diambil di Toko', 'Diambil di Toko', NULL, 'ambil', 'ambil', 0, 734, '2026-11-28 14:00:00', '2026-11-28 14:00:00', '2026-11-29 14:00:00', '2026-11-29 14:00:00', NULL, 1, 'SIM', 'QRIS', NULL, 16484.00, 16484.00, 0.00, 0.00, 0.00, NULL, 0.00, 0.00, 'PENDING_PAYMENT', '2026-10-22 23:06:40', '2026-10-22 16:06:42'),
(59, 4, 4, 'RNT6ADA3D762101C', 'RNT6ADA3D762101C', 'https://api.qrserver.com/v1/create-qr-code/?size=350x350&margin=15&data=00020101021226570011ID.DANA.WWW011893600915300617401802090061740180303UMI51440014ID.CO.QRIS.WWW0215ID10265872193240303UMI5204739453033605405165885802ID5912Rental+kursi600416056105316646304BB9F', NULL, NULL, NULL, NULL, 'Joni Utama', '081262364197', 'Diambil di Toko', 'Diambil di Toko', NULL, 'ambil', 'ambil', 0, 838, '2026-11-30 13:00:00', '2026-11-30 13:00:00', '2026-12-01 13:00:00', '2026-12-01 13:00:00', NULL, 1, 'SIM', 'QRIS', NULL, 16588.00, 16588.00, 0.00, 0.00, 0.00, NULL, 0.00, 0.00, 'Menunggu Konfirmasi', '2026-10-22 23:44:37', '2026-10-22 16:44:49'),
(60, 4, 16, 'RNT6ADA6F4840AAF', 'RNT6ADA6F4840AAF', 'https://api.qrserver.com/v1/create-qr-code/?size=350x350&margin=15&data=00020101021226570011ID.DANA.WWW011893600915300617401802090061740180303UMI51440014ID.CO.QRIS.WWW0215ID10265872193240303UMI52047394530336054061052955802ID5912Rental+kursi600416056105316646304BCB2', NULL, NULL, NULL, NULL, 'Joni Utama', '081262364197', 'Diambil di Toko', 'Diambil di Toko', NULL, 'ambil', 'ambil', 0, 295, '2026-12-30 14:00:00', '2026-12-30 14:00:00', '2026-12-31 14:00:00', '2026-12-31 14:00:00', NULL, 1, 'SIM', 'QRIS', NULL, 105295.00, 105295.00, 0.00, 0.00, 0.00, NULL, 0.00, 0.00, 'PENDING_PAYMENT', '2026-10-23 03:17:11', '2026-10-22 20:17:21');
/*!40000 ALTER TABLE `orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--
DROP TABLE IF EXISTS `password_reset_tokens`;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `password_reset_tokens`
--
LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pembayarans`
--
DROP TABLE IF EXISTS `pembayarans`;
CREATE TABLE `pembayarans` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint unsigned NOT NULL,
  `metode` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `doku_payment_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'ID transaksi DOKU',
  `doku_channel` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Kanal pembayaran: QRIS, VIRTUAL_ACCOUNT_BCA, dll',
  `paid_at` timestamp NULL DEFAULT NULL COMMENT 'Waktu pembayaran dikonfirmasi sukses',
  `jumlah` decimal(15,2) NOT NULL,
  `bukti_pembayaran` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Menunggu Konfirmasi',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pembayarans_order_id_foreign` (`order_id`),
  CONSTRAINT `pembayarans_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pembayarans`
--
LOCK TABLES `pembayarans` WRITE;
/*!40000 ALTER TABLE `pembayarans` DISABLE KEYS */;
INSERT INTO `pembayarans` (`id`, `order_id`, `metode`, `doku_payment_id`, `doku_channel`, `paid_at`, `jumlah`, `bukti_pembayaran`, `status`, `created_at`, `updated_at`) VALUES
(1, 43, 'gopay', NULL, NULL, '2026-10-16 15:27:03', 47250.00, NULL, 'Lunas', '2026-10-16 15:27:03', '2026-10-16 15:27:03'),
(2, 49, 'gopay', NULL, NULL, '2026-10-17 20:36:06', 105000.00, NULL, 'Lunas', '2026-10-17 20:36:07', '2026-10-17 20:36:07'),
(3, 52, 'QRIS', NULL, NULL, '2026-10-17 21:27:43', 15750.00, NULL, 'Lunas', '2026-10-17 21:27:43', '2026-10-17 21:27:43'),
(6, 57, 'QRIS', NULL, NULL, '2026-10-22 16:05:17', 15971.00, NULL, 'Menunggu Konfirmasi', '2026-10-22 16:05:17', '2026-10-22 16:05:17'),
(7, 59, 'QRIS', NULL, NULL, '2026-10-22 16:44:49', 16588.00, NULL, 'Menunggu Konfirmasi', '2026-10-22 16:44:49', '2026-10-22 16:44:49');
/*!40000 ALTER TABLE `pembayarans` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pengembalians`
--
DROP TABLE IF EXISTS `pengembalians`;
CREATE TABLE `pengembalians` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint unsigned NOT NULL,
  `tanggal_dikembalikan` datetime NOT NULL,
  `kondisi_barang` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `denda_keterlambatan` decimal(15,2) NOT NULL DEFAULT '0.00',
  `denda_kerusakan` decimal(15,2) NOT NULL DEFAULT '0.00',
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Diajukan',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pengembalians_order_id_foreign` (`order_id`),
  CONSTRAINT `pengembalians_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pengembalians`
--
LOCK TABLES `pengembalians` WRITE;
/*!40000 ALTER TABLE `pengembalians` DISABLE KEYS */;
/*!40000 ALTER TABLE `pengembalians` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `saldos`
--
DROP TABLE IF EXISTS `saldos`;
CREATE TABLE `saldos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `vendor_id` bigint unsigned NOT NULL,
  `saldo_aktif` decimal(15,2) NOT NULL DEFAULT '0.00',
  `saldo_ditahan` decimal(15,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `saldos_vendor_id_foreign` (`vendor_id`),
  CONSTRAINT `saldos_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `saldos`
--
LOCK TABLES `saldos` WRITE;
/*!40000 ALTER TABLE `saldos` DISABLE KEYS */;
INSERT INTO `saldos` (`id`, `vendor_id`, `saldo_aktif`, `saldo_ditahan`, `created_at`, `updated_at`) VALUES
(1, 4, 0.00, 0.00, '2026-10-01 14:20:12', '2026-10-01 14:20:12'),
(2, 2, 0.00, 0.00, '2026-10-01 15:06:02', '2026-10-01 15:06:02'),
(3, 16, 0.00, 0.00, '2026-10-02 08:09:44', '2026-10-02 08:09:44');
/*!40000 ALTER TABLE `saldos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--
DROP TABLE IF EXISTS `sessions`;
CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--
LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ulasans`
--
DROP TABLE IF EXISTS `ulasans`;
CREATE TABLE `ulasans` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `barang_id` bigint unsigned NOT NULL,
  `rating` int NOT NULL DEFAULT '5',
  `komentar` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ulasans_order_id_foreign` (`order_id`),
  KEY `ulasans_user_id_foreign` (`user_id`),
  KEY `ulasans_barang_id_foreign` (`barang_id`),
  CONSTRAINT `ulasans_barang_id_foreign` FOREIGN KEY (`barang_id`) REFERENCES `barang` (`id`) ON DELETE CASCADE,
  CONSTRAINT `ulasans_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `ulasans_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ulasans`
--
LOCK TABLES `ulasans` WRITE;
/*!40000 ALTER TABLE `ulasans` DISABLE KEYS */;
/*!40000 ALTER TABLE `ulasans` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `google_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password_changed_at` timestamp NULL DEFAULT NULL,
  `role` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'customer',
  `vendor_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `whatsapp` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `whatsapp_verified_at` timestamp NULL DEFAULT NULL,
  `whatsapp_vendor` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `latitude` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `longitude` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `alamat_lengkap` text COLLATE utf8mb4_unicode_ci,
  `foto_profil` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `vendor_status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--
LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` (`id`, `name`, `email`, `google_id`, `email_verified_at`, `password`, `password_changed_at`, `role`, `vendor_name`, `whatsapp`, `whatsapp_verified_at`, `whatsapp_vendor`, `latitude`, `longitude`, `alamat_lengkap`, `foto_profil`, `vendor_status`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'Super Admin', 'admin@rentify.com', NULL, '2026-09-26 11:01:34', '$2y$12$gYrD3AzpoOQ7mbYlij5J7ul.FMRN54eS4CxRvg9fc9k8xbmypjMZe', NULL, 'admin', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'pending', NULL, '2026-09-26 11:01:35', '2026-09-26 11:01:35'),
(2, 'Vendor Sewa Kamera', 'vendor@rentify.com', NULL, '2026-09-26 11:01:36', '$2y$12$gVz4TMVZZVvoW2h0CBZg9.7Cm7OIhp1cu0rA/iBqJXEc5e/ur0Pkq', NULL, 'vendor', 'Sewa Kamera Jakarta', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'approved', NULL, '2026-09-26 11:01:36', '2026-09-26 11:01:36'),
(4, 'Joni Utama', 'jutama937@gmail.com', 102126265197434674301, '2026-10-01 14:01:52', '$2y$12$O4L1RwqqfKukO6iwztF0buUVFJEwoB4YeZXixRxsbOLJ.XrPsMQmi', '2026-09-27 20:01:20', 'vendor', 'Rentify', NULL, '2026-10-01 14:01:05', '082183394549', 5.2375573, 97.019691, 'Jalan Raya Medan Banda Aceh, Palohlada, Dewantara, Aceh Utara, Aceh, 24354, Indonesia', 'https://res.cloudinary.com/fnf8f1pm/image/upload/v1790882079/rentify/customer_profil/cmaidb11w8haofmtax0w.png', 'approved', NULL, '2026-09-26 12:51:31', '2026-10-16 12:39:37'),
(5, 'rtootilipi', 'zwdgsuph@formtests.info', NULL, NULL, '$2y$12$11X.4UwHSa85uNYbpPndk.u30dkII5KnM44j2w5MC2c8bWZpHI.rK', NULL, 'vendor', 'implntsnhe', NULL, NULL, 'otyfkhpqos', NULL, NULL, NULL, NULL, 'approved', NULL, '2026-09-26 13:39:51', '2026-10-22 11:04:27'),
(6, 'Joni Utama', 'utamajoni29@gmail.com', 115045329914410956925, '2026-09-26 15:31:18', '$2y$12$H4dLDsjMY3pQPWV61eTc7e3AXfnZbFTpdmfqQPyfb/1tO35VLl.bW', NULL, 'customer', NULL, NULL, NULL, NULL, 5.2365979, 97.0238461, 'CV. Celcom Jaya Pulsa, 26, Jalan Raya Medan Banda Aceh, Palohlada, Dewantara, Aceh Utara, Aceh, 24354, Indonesia', NULL, 'pending', NULL, '2026-09-26 15:31:18', '2026-10-02 19:50:11'),
(8, 'Joni Utama', '081262364197@rentify.local', NULL, NULL, '$2y$12$392w2EgRWjzu2QpGq0Pwp.M/Mwag3fSc2edZPVSo1myyCUKH7wGom', NULL, 'customer', NULL, '081262364197', NULL, NULL, NULL, NULL, NULL, NULL, 'pending', NULL, '2026-09-26 17:05:11', '2026-09-26 17:05:11'),
(10, 'Joni Utama', 'rentify.id@gmail.com', 113122120602427729499, '2026-10-01 13:01:54', '$2y$12$s8DHIzqFKLuGAIinsiMQK.kGukfN.Hc1UrSr30LueOCTsdS/uuZ3S', NULL, 'admin', 'Rentify', NULL, '2026-09-27 06:09:52', '081262364197', NULL, NULL, NULL, NULL, 'pending', NULL, '2026-09-27 06:09:19', '2026-10-22 11:02:36'),
(11, 'Annisa Liandes', 'anisaliandes@gmail.com', 117177104077451266945, NULL, '$2y$12$xZqcm.rPeSoG516ww7ulmOXPpAJfMeFgMotFovEOgSB9itjoKfx9y', '2026-09-28 10:40:58', 'customer', NULL, '082273013438', NULL, NULL, NULL, NULL, NULL, 'https://res.cloudinary.com/fnf8f1pm/image/upload/v1790592514/rentify/customer_profil/soebszyh5awvkmnzwhl1.jpg', 'pending', NULL, '2026-09-28 10:38:32', '2026-09-28 11:20:59'),
(12, 'Naila Safarina', '081360479781@rentify.local', NULL, NULL, '$2y$12$7TV4uCtfVvKVM82CDCbPh.G/Pdpvh2JZqmh.imouU5pv/BM64gQLe', NULL, 'customer', NULL, '081360479781', '2026-09-29 08:26:27', NULL, NULL, NULL, NULL, NULL, 'pending', NULL, '2026-09-29 08:26:06', '2026-09-29 08:26:27'),
(13, 'sasi dara aisyah', 'sasidaraaisyah4@gmail.com', 113694627693960026842, '2026-09-30 08:21:09', '$2y$12$cciP8meO0NtgySTyGYJWDea2pXU8R0dazlP/zU0N66fWfmqYIOtOW', '2026-09-30 08:23:15', 'customer', NULL, '082185403056', NULL, NULL, NULL, NULL, NULL, 'https://res.cloudinary.com/fnf8f1pm/image/upload/v1790757403/rentify/customer_profil/qapttsfmiho4iivdcysi.jpg', 'pending', NULL, '2026-09-30 08:21:09', '2026-09-30 08:38:01'),
(14, 'Asu Ngeteh', 'asun46768@gmail.com', 105286936275501556746, '2026-09-30 09:27:03', '$2y$12$fOvnhIG0jVWxwijRrRoGvOvwD54UBvMPANhztPACfS6IOOn9XkogG', NULL, 'customer', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'pending', NULL, '2026-09-30 09:27:03', '2026-09-30 09:27:03'),
(15, 'Frann', '083125922565@rentify.local', NULL, NULL, '$2y$12$Nv16UlToEoVZFzNrZ3bEIuAeJt7BkcpBm/nKlSBDuZag1x9zKBbYm', '2026-10-05 21:43:48', 'customer', NULL, '083125922565', '2026-09-30 18:11:38', NULL, 5.2413264, 97.0255336, 'Keudekruenggeukueh, Dewantara, Aceh Utara, Aceh, 24354, Indonesia', 'https://res.cloudinary.com/fnf8f1pm/image/upload/v1790859807/rentify/customer_profil/vzpzbberpzuixgupx4ke.jpg', 'pending', NULL, '2026-09-30 18:10:58', '2026-10-05 22:23:53'),
(16, 'Ghufran Fadlurrahman', 'ghufranfadlur@gmail.com', 110356081856103934971, '2026-10-02 08:09:43', '$2y$12$YPsNYC0kVva9KYDvCM2OVeTXgcdtfw6xo36qWVqbApTuUcMM4i20y', NULL, 'vendor', 'Franz Store', NULL, '2026-10-02 08:07:21', '083125922565', 5.231506, 96.980655, 'Universitas Malikussaleh, First, Jalan Universitas Malikussaleh, Reuleut Timu, Muara Batu, Aceh Utara, Aceh, 23442, Indonesia', NULL, 'approved', NULL, '2026-10-02 08:06:44', '2026-10-16 09:16:17'),
(17, 'Khairin Nisa', 'kharinnisa146@gmail.com', 102052519445448806527, '2026-10-03 12:50:53', '$2y$12$3GZkpwFY3uHnBI4XlppPWuwu.xTOSlQNhvXMZdKJH9nZC5..KSIQm', '2026-10-18 09:55:23', 'customer', NULL, NULL, NULL, NULL, 5.2296067, 96.99329, 'Reuleut Timu, Muara Batu, Aceh Utara, Aceh, 23442, Indonesia', NULL, 'pending', NULL, '2026-10-03 12:50:52', '2026-10-18 09:55:23'),
(18, 'Hanif', '082253204649@rentify.local', NULL, NULL, '$2y$12$pZmb8hFq6JDb2aHd/HDki.rxSCzPe3WGQ8psgz0LoGtS8dJbSSvdu', NULL, 'customer', NULL, '082253204649', '2026-10-08 16:58:40', NULL, NULL, NULL, NULL, NULL, 'pending', NULL, '2026-10-08 16:58:20', '2026-10-08 16:58:40'),
(19, 'Ferry Maulana', 'ferrymaulana727@gmail.com', 102014522191757362215, '2026-10-21 03:08:49', '$2y$12$kWY3rr3HAh/o/30zZbQKweCaO1yBAMu4xB7TEvjs5AOXbrqbJ4oMS', NULL, 'customer', NULL, NULL, NULL, NULL, 5.2318733, 96.9806001, 'Fakultas Kedokteran, First, Jalan Sultanah Nahrasiyah, Reuleut Barat, Muara Batu, Aceh Utara, Aceh, 23442, Indonesia', NULL, 'pending', NULL, '2026-10-21 03:08:49', '2026-10-21 03:11:02'),
(20, 'Azkia Akbar', 'azkiaakbar62@gmail.com', 112769089137301550749, '2026-10-21 15:46:32', '$2y$12$5pX7RAaJEw.ZucNjsM1QCOC90CnnFe2mAqjMZuiPmARsBXaVUu1Dq', NULL, 'customer', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'pending', NULL, '2026-10-21 15:46:32', '2026-10-21 15:46:32'),
(21, 'Azkia Akbar', 'azkiaakbar501@gmail.com', 108938525859644474309, '2026-10-21 15:46:55', '$2y$12$ni4pCgIxKir5NqG6X/1dl.q/vgxve8sTitzoOGnCj93rkDwQKtBgm', NULL, 'customer', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'pending', NULL, '2026-10-21 15:46:55', '2026-10-21 15:46:55');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `vouchers`
--
DROP TABLE IF EXISTS `vouchers`;
CREATE TABLE `vouchers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `vendor_id` bigint unsigned NOT NULL,
  `kode_voucher` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipe_diskon` enum('nominal','persen') COLLATE utf8mb4_unicode_ci NOT NULL,
  `nilai_diskon` decimal(15,2) NOT NULL,
  `maksimal_diskon` decimal(15,2) DEFAULT NULL,
  `minimal_belanja` decimal(15,2) NOT NULL DEFAULT '0.00',
  `kuota_total` int NOT NULL DEFAULT '0',
  `kuota_terpakai` int NOT NULL DEFAULT '0',
  `tanggal_mulai` date NOT NULL,
  `tanggal_selesai` date NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `vouchers_kode_voucher_unique` (`kode_voucher`),
  KEY `vouchers_vendor_id_foreign` (`vendor_id`),
  CONSTRAINT `vouchers_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `vouchers`
--
LOCK TABLES `vouchers` WRITE;
/*!40000 ALTER TABLE `vouchers` DISABLE KEYS */;
INSERT INTO `vouchers` (`id`, `vendor_id`, `kode_voucher`, `tipe_diskon`, `nilai_diskon`, `maksimal_diskon`, `minimal_belanja`, `kuota_total`, `kuota_terpakai`, `tanggal_mulai`, `tanggal_selesai`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 4, 'RENTAL1', 'persen', 80.00, 70000.00, 0.00, 50, 1, '2026-10-04', '2026-10-23', 1, '2026-10-04 05:15:04', '2026-10-04 05:19:54');
/*!40000 ALTER TABLE `vouchers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `whatsapp_sessions`
--
DROP TABLE IF EXISTS `whatsapp_sessions`;
CREATE TABLE `whatsapp_sessions` (
  `key` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `whatsapp_sessions`
--
LOCK TABLES `whatsapp_sessions` WRITE;
/*!40000 ALTER TABLE `whatsapp_sessions` DISABLE KEYS */;
INSERT INTO `whatsapp_sessions` (`key`, `value`, `created_at`, `updated_at`) VALUES
('app-state-sync-key-AAAAANcD', '{\"keyData\":\"B5zpFcPUcKNDQqnNs2TTJ4DwkOdZm52Nh4dt7FOrM8Q=\",\"fingerprint\":{\"rawId\":1365019454,\"currentIndex\":2,\"deviceIndexes\":[0,2]},\"timestamp\":\"1792667022740\"}', '2026-10-22 11:03:43', '2026-10-22 11:03:43'),
('creds', '{\"noiseKey\":{\"private\":{\"type\":\"Buffer\",\"data\":\"4KxUvEacD30+xj4Y9GYlOWY9bFKAlW88fiFZF4U89Eo=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"hi6Z9OQs66NnbIMaI+9lzr5W+a2+GBVIZHaDfcMDGVI=\"}},\"pairingEphemeralKeyPair\":{\"private\":{\"type\":\"Buffer\",\"data\":\"cGKjpjIjOpFGmznNJMbg+AhPV24IGLuyt9zTFY0Pn3k=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"NearKJx08czH+1tuR5VDbu3RdqvT7sThpvfQlFnXgBY=\"}},\"signedIdentityKey\":{\"private\":{\"type\":\"Buffer\",\"data\":\"CKdiDmjx/6q3klH+fDHPoEYEKnU+Yx58caYgv2IUL3s=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"I+gAgXQquiGhsCVzFVEd648k22KX68FMkTCJJQhVFkk=\"}},\"signedPreKey\":{\"keyPair\":{\"private\":{\"type\":\"Buffer\",\"data\":\"cFZ2YwEq+laT30racYbxe32EjcbREYwJKuFCRTLwYHg=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"IpnmupqWEkCWp5oqb7gb2na/gE3EpTysukZiPXyM104=\"}},\"signature\":{\"type\":\"Buffer\",\"data\":\"XZj4s7KjR+UAB+v0BiCyPaqCkipmeIO0zIj9iLaZKpvLnxR3aW+sQR5eUDxk+ZHEBlMu5swY7ldOJoNyPSvYgQ==\"},\"keyId\":1},\"registrationId\":105,\"advSecretKey\":\"aCHQhCU5Xw9lfvQAlNVMHlZ91N+zDcfbm4mpLLsngks=\",\"processedHistoryMessages\":[],\"nextPreKeyId\":428,\"firstUnuploadedPreKeyId\":428,\"accountSyncCounter\":0,\"accountSettings\":{\"unarchiveChats\":false},\"registered\":false,\"account\":{\"details\":\"CL6W8ooFEPDa59YGGAIgACgA\",\"accountSignatureKey\":\"5aE0j/nMzkK0dditRPKel0p4mTaX1IcD/6gVmptLDic=\",\"accountSignature\":\"1ob02UfOmtNxk5rm2NXZHFcMaz6K5+bKq9xjJBtJlljDFX5kIyd9P3zDXt3FwdDmPmAp2MSdNeO0ISHBTylZAw==\",\"deviceSignature\":\"cEHjfnSfzF/RBgvik59P7b+gSXNCFlpd7LjgeyQ8eq8WNJq0T0ZyaT1FRc3aVMUhkiuM/A+6qiFZkZ1WzuV3gg==\"},\"me\":{\"id\":\"6281262364197:3@s.whatsapp.net\",\"name\":\"Rentify\",\"lid\":\"249070069219550:3@lid\"},\"signalIdentities\":[{\"identifier\":{\"name\":\"6281262364197:3@s.whatsapp.net\",\"deviceId\":0},\"identifierKey\":{\"type\":\"Buffer\",\"data\":\"BeWhNI/5zM5CtHXYrUTynpdKeJk2l9SHA/+oFZqbSw4n\"}}],\"platform\":\"smba\",\"routingInfo\":{\"type\":\"Buffer\",\"data\":\"CBIIBQgI\"},\"lastAccountSyncTimestamp\":1792667085,\"myAppStateKeyId\":\"AAAAANcD\"}', '2026-10-22 11:03:16', '2026-10-23 20:33:52'),
('pre-key-10', '{\"private\":{\"type\":\"Buffer\",\"data\":\"aPSkMbFOXLa3MfoN8aAM9Wrrx6yXrgjc/knG5XsQA3U=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"H0cdw2Uj0elFNSe80/7z63gCpZBsob5lPDLldA5KqnI=\"}}', '2026-10-22 11:03:25', '2026-10-22 11:03:27'),
('pre-key-100', '{\"private\":{\"type\":\"Buffer\",\"data\":\"cJA5hcmxLAxtQFzxd5vktD1fjBZXmja6HaSBzvTpRG8=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"DS/v/NEyKV8/yJSsy6qPgRY8TfFr7VsTUqudMVmn2Sk=\"}}', '2026-10-22 11:47:29', '2026-10-22 11:47:29'),
('pre-key-101', '{\"private\":{\"type\":\"Buffer\",\"data\":\"yGxNDYu6aMlcKm41Y22AHb232S4Q5nAkQwpZjbER0nA=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"HZgwK5216bInyj6y1UHHi+egi9jZrEsa/OYtrpgbA2U=\"}}', '2026-10-22 11:47:33', '2026-10-22 11:47:33'),
('pre-key-102', '{\"private\":{\"type\":\"Buffer\",\"data\":\"ABySakBl4kOUjFn3A6f5cUdeFiSYU2BYtnZpMie8fUE=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"a2dgYx3FOLpd3ErQkqO81SIiS6LfGfGoAOx3UV/MUWE=\"}}', '2026-10-22 11:47:34', '2026-10-22 11:47:34'),
('pre-key-108', '{\"private\":{\"type\":\"Buffer\",\"data\":\"WB6xDXwHDntw6C62ChE1+N17aa9pF6R/dEREJ1yr80Q=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"cbKZToG1FULgAQFy/JzR02gevL+eCx7YbZexpNkWg3M=\"}}', '2026-10-22 13:38:09', '2026-10-22 13:38:09'),
('pre-key-113', '{\"private\":{\"type\":\"Buffer\",\"data\":\"kG6E2eWCcGbEbCNSFlGqjxm60hOO1JkbDDIBkIQ+JXA=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"RjGm0HIwP6OfN/tPWxCiZ/zFTdf0dQfeZBXuTUXodhA=\"}}', '2026-10-22 14:17:49', '2026-10-22 14:17:49'),
('pre-key-114', '{\"private\":{\"type\":\"Buffer\",\"data\":\"CBFVkxk/FTC0nQovBrGfQ3phRYWKn2P69o7uZPCvP10=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"cZXWQ2UvZcLDmUT4zS0cSHFQFSN/OoqhDq+J9RDr4G4=\"}}', '2026-10-22 14:40:35', '2026-10-22 14:40:35'),
('pre-key-115', '{\"private\":{\"type\":\"Buffer\",\"data\":\"mN5wZJZzl9Hjw8FU0I7LuXsGV9lBBhKqXv2pVaV8GHQ=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"4otwSfYx3yA2jN5j9E7CsHU8HT3se4+GaLJU0qZVUio=\"}}', '2026-10-22 15:08:09', '2026-10-22 15:08:09'),
('pre-key-12', '{\"private\":{\"type\":\"Buffer\",\"data\":\"eIqC/9QT8bkIw7QDoSbtpeOdooXFSqkznyJCVXFlu10=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"Ycf0mbDx9PdEBPcecLgusK0ZTw0diEdeOVCBz8p+lEc=\"}}', '2026-10-22 11:03:25', '2026-10-22 11:03:27'),
('pre-key-120', '{\"private\":{\"type\":\"Buffer\",\"data\":\"8D0szVTxGLhcfVpm97Wo9z1cSvFiVj9BTnN4mEim6UA=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"Tn4x03NUMhPwZ39EuUB5nQuThrHrwsqWW4NLV63B6zM=\"}}', '2026-10-22 15:18:06', '2026-10-22 15:18:06'),
('pre-key-121', '{\"private\":{\"type\":\"Buffer\",\"data\":\"ECiRQs3+CKmO28QnNmXr92JNNPjEjXoCvwPPAhgST1Y=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"e4o652MY1WWSFbQkRHLXDvp3K6rdyrmsmJP4Uf63olU=\"}}', '2026-10-22 15:18:14', '2026-10-22 15:18:14'),
('pre-key-122', '{\"private\":{\"type\":\"Buffer\",\"data\":\"6M9RtaWTL+uOxtZFSWYliFZ3f3797DkesLsqiYIoD34=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"97f7od92BL3CXJw4gK9pGZwnfJA+DrCkm9qdLIenTl4=\"}}', '2026-10-22 15:18:19', '2026-10-22 15:18:19'),
('pre-key-123', '{\"private\":{\"type\":\"Buffer\",\"data\":\"+ABHDTRAxRd2/8kqnr+l4l4EyU+wDqbterUQGgx6/Fw=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"Z9ldLBg+hZft+6P9To0NLm/xk6CPWRO+znmscKdhvCA=\"}}', '2026-10-22 15:18:19', '2026-10-22 15:18:19'),
('pre-key-124', '{\"private\":{\"type\":\"Buffer\",\"data\":\"MMLzEfoBEiw/0C1FIXHGAV5Eep2HrlhNpkbCq0Ua6k0=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"tCCpVSxVISZ+ApWNqhaXGpDWrDug//ufBbuJz4znl2Y=\"}}', '2026-10-22 15:18:28', '2026-10-22 15:18:28'),
('pre-key-125', '{\"private\":{\"type\":\"Buffer\",\"data\":\"+InGOxu1pt4C9orsL9J35BwsrP6hojJiJS8hIgeTQ2Y=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"K7+yxCyoYOg/SRZZ/zMwSO4XsierzLSlURx5KxA4bV4=\"}}', '2026-10-22 15:18:28', '2026-10-22 15:18:28'),
('pre-key-126', '{\"private\":{\"type\":\"Buffer\",\"data\":\"QL/b69AoUmxcAvAtVGztgSQswfTaXTZgc+wW18cn7HU=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"Pd39QEKf6sYAozXsJ1m1Ui+hiNtEpSXhchrJHDErdRE=\"}}', '2026-10-22 15:18:33', '2026-10-22 15:18:33'),
('pre-key-127', '{\"private\":{\"type\":\"Buffer\",\"data\":\"YCL21UIafmmv4TDoLvSU1f4CTqeTfEupfvyZmdwZ5Fg=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"GBu039dAeQ+nROog989+Ks9gZBDWUeAwSc+jg+qHuns=\"}}', '2026-10-22 15:18:33', '2026-10-22 15:18:33'),
('pre-key-128', '{\"private\":{\"type\":\"Buffer\",\"data\":\"QHQZ3zE5u+LacaQJUx3wfhDvC+EkjNbvSMfe4np57UY=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"NFLO0SUTz/30Hc+ORR2i8WM6pXH9DLQodLZ3/daQ7ko=\"}}', '2026-10-22 15:18:33', '2026-10-22 15:18:33'),
('pre-key-129', '{\"private\":{\"type\":\"Buffer\",\"data\":\"0Ij28fu7L9/LoOIfvTVQR8xQN5KoU6+8yAZ+Gk4/U0c=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"fihOSKqOaxsk3W8LlbILyW63SUci3yu3FA1Gq6y2X1o=\"}}', '2026-10-22 15:18:41', '2026-10-22 15:18:41'),
('pre-key-13', '{\"private\":{\"type\":\"Buffer\",\"data\":\"EGzcBUYYY1hrag2hlBrEj5Jd3ZayOYF3Suwyg+xTX0A=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"dLUMTmji05Z4uR2ScbbRtLxntGCA7OL1r9M8iEfvOVk=\"}}', '2026-10-22 11:03:25', '2026-10-22 11:03:27'),
('pre-key-130', '{\"private\":{\"type\":\"Buffer\",\"data\":\"AHjRh2zoaTSiCX1lAYwipl58AgFu0qJNLwiy2dgq21M=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"z4Om6O0gLkEncEbQiq2oP+EvAS4MqGWOard64Lfeb2M=\"}}', '2026-10-22 15:18:46', '2026-10-22 15:18:46'),
('pre-key-131', '{\"private\":{\"type\":\"Buffer\",\"data\":\"wEGzIci40Bs5YKZIKWYo502u4u/f+xpSHsWT7kb5Q0M=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"aa0/ky3ar4XjwjQXKYw3GGSoHS0Mztkv4Onctu+iJFk=\"}}', '2026-10-22 15:18:46', '2026-10-22 15:18:46'),
('pre-key-132', '{\"private\":{\"type\":\"Buffer\",\"data\":\"ALGZ51+sjzRJ/WOKOUoDFEKBbQJoFY3dnUHG8rCt93E=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"noCup3kFSd9liXEoVb/8YSO+jRi8Z5nID0Rq2Mpf0AU=\"}}', '2026-10-22 15:18:46', '2026-10-22 15:18:46'),
('pre-key-133', '{\"private\":{\"type\":\"Buffer\",\"data\":\"wBxCFKTnXiubAzcsbW1Bm71lHW9dZxrye/UT7QTNtHI=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"GpwkiSVRVvpUTok9hvyurRBqDFtpFVU0LjxkLDlt5Wc=\"}}', '2026-10-22 15:18:47', '2026-10-22 15:18:47'),
('pre-key-134', '{\"private\":{\"type\":\"Buffer\",\"data\":\"8EhBiAiadHMtYKWNC494W6yo9GUvWhkbPRMSbu42i2U=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"eqaJePKM86RHB8xONwzxGd2jg/o7Jtsu72zI7BKxdCQ=\"}}', '2026-10-22 15:18:55', '2026-10-22 15:18:55'),
('pre-key-135', '{\"private\":{\"type\":\"Buffer\",\"data\":\"GCZzvn+iBt3Am4E5N3O625zDMi4sGv/xdSdw5CUaslQ=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"+xIIZbSI6wffOXMpiwBvG2/BGpTSB+zOQ+oJQ78pBg8=\"}}', '2026-10-22 15:18:59', '2026-10-22 15:18:59'),
('pre-key-136', '{\"private\":{\"type\":\"Buffer\",\"data\":\"cACBioqt7sl5OwXaXWgSlH5VnVBHpg8VSVV9Rb0WiUI=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"/zBPIDZw+ZztaAKOg8niaCFhGluPkZaqPRmLdQGUzTs=\"}}', '2026-10-22 15:19:00', '2026-10-22 15:19:00'),
('pre-key-137', '{\"private\":{\"type\":\"Buffer\",\"data\":\"oPBenoE/HIA7FVChkDnJsX5yoTDwWdWJp/QVLnwnl0s=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"XSk271NjHTP1STry8jMajVttQonL/CzUDPDdy9gT6is=\"}}', '2026-10-22 15:19:00', '2026-10-22 15:19:00'),
('pre-key-138', '{\"private\":{\"type\":\"Buffer\",\"data\":\"uJLBCr5TdOlHA9mcpc5y+WjetRayW7H7669Aiq+xq3w=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"K5bFUnaAzjXdOMka0zzNKtdUAwY//omTX0G8vVYrYF4=\"}}', '2026-10-22 15:19:00', '2026-10-22 15:19:00'),
('pre-key-139', '{\"private\":{\"type\":\"Buffer\",\"data\":\"kAXpCovDDAz6dC1bBZ7zXOotmKZqRustffZm+3GEa1g=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"+5k2kJpRJmO6CVFzo5GlIXY9+m2tPKXb8dB28y72iS0=\"}}', '2026-10-22 15:19:18', '2026-10-22 15:19:18'),
('pre-key-14', '{\"private\":{\"type\":\"Buffer\",\"data\":\"YC11QUdkOH7Umk8tfktxNpxIPDYHOJou3KHnd5mHLl0=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"Ze+fSdrQNv8Mkzh4LL0GMwof4yx8CullfbaTVZS5Ugk=\"}}', '2026-10-22 11:03:25', '2026-10-22 11:03:27'),
('pre-key-140', '{\"private\":{\"type\":\"Buffer\",\"data\":\"4CinDvkoUqvBAIx1SpMluZ/4wL9f2ah2pF45ivx4m0k=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"zNZ8PieREtVqF9k1RFDiU2RwabeSU0D5m83FqwRYmBI=\"}}', '2026-10-22 15:19:18', '2026-10-22 15:19:21'),
('pre-key-141', '{\"private\":{\"type\":\"Buffer\",\"data\":\"MNLJq0oE+t9TbTm7Jve0Rp0mODj76IDgJ2gReJpT4VA=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"y53ZhWwJtotLapQWGOpjUEigr1jteZvoTfFnpAAYdBE=\"}}', '2026-10-22 15:19:19', '2026-10-22 15:19:22'),
('pre-key-142', '{\"private\":{\"type\":\"Buffer\",\"data\":\"UELLx5fA4e+odSIQHxnyGSJNnr85s5gNtNXM2fLbins=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"LgtiviyUuDeUuUax6rCmJ9/bqHGIMWNyXXYc6vrf+VM=\"}}', '2026-10-22 15:19:32', '2026-10-22 15:19:32'),
('pre-key-143', '{\"private\":{\"type\":\"Buffer\",\"data\":\"6EpbAKBhVss8mzY7hswR/K//OPttEEbMZjrGYXcISFI=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"4Mb5h0pXhwB5d+ePKsFC/VMZnzxu8Tu3pijljdAMr0g=\"}}', '2026-10-22 15:19:32', '2026-10-22 15:19:35'),
('pre-key-144', '{\"private\":{\"type\":\"Buffer\",\"data\":\"+Ii77mjlyJXckXe1mtIjepIeJv9s9QIxRbXtmlic9Hk=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"Kn0HHlYzUWDmfxjNqnWonADLNs10xkkJlYC7auzW2WQ=\"}}', '2026-10-22 15:19:32', '2026-10-22 15:19:35'),
('pre-key-145', '{\"private\":{\"type\":\"Buffer\",\"data\":\"WABFw5FKrIPxj4CrDnr3mzvziJjrNlL8mJyBreHm8GA=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"QniU6kpOv+mQeDQsDMXV4tMCnNKh225M/KqMlxIkS34=\"}}', '2026-10-22 15:19:45', '2026-10-22 15:19:45'),
('pre-key-146', '{\"private\":{\"type\":\"Buffer\",\"data\":\"8Oa01nN3jUO1ah8guBcT//ubHPkMCxNj3KrAm6gLwFY=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"zddZty35RujDumUq185zA+2rlqjUbmAjkdnYErffChI=\"}}', '2026-10-22 15:19:46', '2026-10-22 15:19:49'),
('pre-key-147', '{\"private\":{\"type\":\"Buffer\",\"data\":\"4Ou7eAroSqe77MfS7kqHeo4W14KPGqwUSLltX7rm+Gk=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"0uodfCzQqqPT4zwUdRcaQPsG3KS/CiVpN+37t0MzNyM=\"}}', '2026-10-22 15:19:46', '2026-10-22 15:19:49'),
('pre-key-148', '{\"private\":{\"type\":\"Buffer\",\"data\":\"CICUBVkH1qvXmMNC/RnrMtxf1K1DGVyM2dffPR/yfEQ=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"zCCSTkX74Qb713B0I+fqtuU5J113CzmwKslQo9xh7Ec=\"}}', '2026-10-22 15:19:59', '2026-10-22 15:19:59'),
('pre-key-149', '{\"private\":{\"type\":\"Buffer\",\"data\":\"UI1o08jHJWJhSdt2An9JGN8sc7c2LMiwSChTod3kiHI=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"8vVlcj3wgq0drsewAqS82wtu/rpZoIPaz4W0ZUVi40k=\"}}', '2026-10-22 15:20:00', '2026-10-22 15:20:00'),
('pre-key-150', '{\"private\":{\"type\":\"Buffer\",\"data\":\"qJggVF4KoUfeUW6c5ykqIU8zko77yWGNpQUVvvRUNU0=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"F0nBDPQ88d1wdZmT3c6ooJJm4KJNUu9Qyqgwrps4sW4=\"}}', '2026-10-22 15:20:00', '2026-10-22 15:20:00'),
('pre-key-151', '{\"private\":{\"type\":\"Buffer\",\"data\":\"UALDml30tVUjeaVhiKVYqvm7ggoqSWBvPXPEHwZbEWA=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"ThN5wfO23u4fxC9bjHkfPbW2X11zl61ZvBV8A074QW8=\"}}', '2026-10-22 15:20:47', '2026-10-22 15:20:47'),
('pre-key-152', '{\"private\":{\"type\":\"Buffer\",\"data\":\"AAv2mhxC/9F5lzASsrRdFTqCC62oGo8e15c49579BXA=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"TDgM0lZxHUBKnUM9V+F3/doe3jPuUs+VYsPJR0tnAm0=\"}}', '2026-10-22 15:20:48', '2026-10-22 15:20:48'),
('pre-key-153', '{\"private\":{\"type\":\"Buffer\",\"data\":\"UDBw+7il4KV1A9nHQF01HBuJiXYN/RqIf7foK+VBWUs=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"A/m4dd2IS3oRRrDJ9BAfs1CK1Qj9PMgmFEKxBcB3x1k=\"}}', '2026-10-22 15:20:48', '2026-10-22 15:20:48'),
('pre-key-154', '{\"private\":{\"type\":\"Buffer\",\"data\":\"MCYGmClHd0HGM6rXk1PzbmjxEQ6Omsx5VHXVOuCgm2o=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"pnF4z7UNBtf+qJJ8aYCgTcRWqwMRSV8Q2nGx2lglAxk=\"}}', '2026-10-22 15:21:55', '2026-10-22 15:21:55'),
('pre-key-155', '{\"private\":{\"type\":\"Buffer\",\"data\":\"eHrxIUh/wB3zu2ohHuIwE+0dUYaEsePxGYYn8eapDUU=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"Y5Atmb4EkpjZzEmQD1Y4NtA8fn4+l6dfZbfOm1oWL0Y=\"}}', '2026-10-22 15:21:55', '2026-10-22 15:21:55'),
('pre-key-156', '{\"private\":{\"type\":\"Buffer\",\"data\":\"wPHq8aDe3m6yk8jnp57HD1b8iyRcypTexAH9eIp3w2E=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"NRKkki1IfdLEooKHBWH65973s6DRQGTjLO3ohsn8sQw=\"}}', '2026-10-22 15:21:56', '2026-10-22 15:21:56'),
('pre-key-157', '{\"private\":{\"type\":\"Buffer\",\"data\":\"yAR1/Vwi2kJ3z1Iaev3i7trJpDaI1juNu3ewN43RSmY=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"TOHBdePDKPZnX7yed0iQjL8MRwlVRc3VM7lfM6P+4FI=\"}}', '2026-10-22 15:21:56', '2026-10-22 15:21:56'),
('pre-key-158', '{\"private\":{\"type\":\"Buffer\",\"data\":\"UDylEKXjsnPr+tEw7ebQIbLpI5Rte1Y/lwxi7juCS2g=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"rhRwrHmDWRk0ju/tqS8eXzpMEN3q6GynTFsrLPEWIHc=\"}}', '2026-10-22 15:21:57', '2026-10-22 15:21:57'),
('pre-key-159', '{\"private\":{\"type\":\"Buffer\",\"data\":\"qHL0Bh4OA1M2duKiz96nYpDmUplWKSJXDcWt7BCYOWE=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"nRvgpbn4VL6geWxGGYUmN0QsUpLklwdeq6+8R7dv12Y=\"}}', '2026-10-22 15:21:59', '2026-10-22 15:21:59'),
('pre-key-16', '{\"private\":{\"type\":\"Buffer\",\"data\":\"YFVXdefCVhrtJhEplTwtO3DDQfFauUemt6iK8eMMzFc=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"3juo/w3661lyt0RBvKT9UNMuSvy4sgiuQC4iLUxS1QQ=\"}}', '2026-10-22 11:03:25', '2026-10-22 11:03:27'),
('pre-key-160', '{\"private\":{\"type\":\"Buffer\",\"data\":\"eGDNwqQBs4/aXyF1KUFpz5tOKH0M5zTJcNqY0akWiGI=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"57n6MVd1HOV2IOl14o9NlWaJDFnLwKczmREOrihxACM=\"}}', '2026-10-22 15:22:08', '2026-10-22 15:22:08'),
('pre-key-161', '{\"private\":{\"type\":\"Buffer\",\"data\":\"aF/dOkBHzbBOP1rXXoSL6R9w63ddmECXa0wPyhcrY2A=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"BksfXC3vebOmGD/f59L8JVPX3JaXWg8UXWckcp+YqBA=\"}}', '2026-10-22 15:22:08', '2026-10-22 15:22:08'),
('pre-key-162', '{\"private\":{\"type\":\"Buffer\",\"data\":\"iEFW2hfLahcgoT0h7y04sKrATqjp3r2VPWsY4fEy6mE=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"z/yHkLzySwxVUXOBmqzP566BBLI0Ds2FPinBFoH10GQ=\"}}', '2026-10-22 15:22:09', '2026-10-22 15:22:09'),
('pre-key-163', '{\"private\":{\"type\":\"Buffer\",\"data\":\"yFmwwO5hyeMzPMETbH7lPri/aSKKh4kSZobv77U11n8=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"moC7T+u8i7tF+J0L44C6MftNPi6f+eHVaA4P/8KYIDk=\"}}', '2026-10-22 15:22:09', '2026-10-22 15:22:09'),
('pre-key-164', '{\"private\":{\"type\":\"Buffer\",\"data\":\"mKo/hoHQeJ02unOcjWrg6+gV1+b3/PLA+lGEPeeRWWg=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"aMGRkQhHysuXsbsu3blrTttI/EW6xOSQE0wT+mkkfQk=\"}}', '2026-10-22 15:22:10', '2026-10-22 15:22:10'),
('pre-key-165', '{\"private\":{\"type\":\"Buffer\",\"data\":\"yKQ16Sxro/USwApG/O/SqCfhZGHKdWEnbKDF7FBj22E=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"KJDOug6Sh4g3IaHc+hQYkLTMm0D+gO1QHm4aA1ii/UM=\"}}', '2026-10-22 15:22:13', '2026-10-22 15:22:13'),
('pre-key-166', '{\"private\":{\"type\":\"Buffer\",\"data\":\"KK64zgBP5uCRFKS01PpGDDJ4RU4AQV6kR+ag5Mp0EGA=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"tYmyQbv9BRtKCLnByr+tQI+GoQmw21pBSeYvI22OrE0=\"}}', '2026-10-22 15:22:13', '2026-10-22 15:22:13'),
('pre-key-167', '{\"private\":{\"type\":\"Buffer\",\"data\":\"uKw3h6GVVWzVbuOujNbxQ/TGOHa0hWMz60qg+MbYTn0=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"kC/GxmFFp4R7bGIsh3MMt+dQqDnD0GCDohsczOXBTU0=\"}}', '2026-10-22 15:22:21', '2026-10-22 15:22:21'),
('pre-key-168', '{\"private\":{\"type\":\"Buffer\",\"data\":\"eCqBzj3er9rN6AJQZJhk6R/9DUj7mrwlvLbJ8kkj0WU=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"+WaqUzh62X2aIykCKF0g3XB3nUJo91lVKqbcJCfvpDU=\"}}', '2026-10-22 15:22:26', '2026-10-22 15:22:26'),
('pre-key-169', '{\"private\":{\"type\":\"Buffer\",\"data\":\"EIWcxkeKanbEARij4odEMNJ+4pbtPIzt4XwzaIKMqUw=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"SeEVeDq0kR33KS5CrXxFrH68XZ+IUebKwA6Ur6Gd7CQ=\"}}', '2026-10-22 15:22:26', '2026-10-22 15:22:26'),
('pre-key-17', '{\"private\":{\"type\":\"Buffer\",\"data\":\"eEQ1lGOb0HSJD64FSFV9iqa+cOgER06id6trUS8HQkM=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"qVG2189Fmlg/MQHMQmjShS6YnTikh/ZDWo0VGcj0tmQ=\"}}', '2026-10-22 11:03:25', '2026-10-22 11:03:27'),
('pre-key-170', '{\"private\":{\"type\":\"Buffer\",\"data\":\"YIe+R1YOzao4avYup2kmIOZvLLoyTfWB4CTP208GNlo=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"WU4suCz1vUr3EAJOJEXOhhsu8Ta7qnonpYhzgBAHKyw=\"}}', '2026-10-22 15:22:27', '2026-10-22 15:22:27'),
('pre-key-171', '{\"private\":{\"type\":\"Buffer\",\"data\":\"kBcip5ZtiPs5dDV0rjFAQGhznh6akyPkXzV6KABXn30=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"D0Mb+urR78cAIlgJP+OakebFiaXhqp36gDaBohEcjBc=\"}}', '2026-10-22 15:22:39', '2026-10-22 15:22:39'),
('pre-key-172', '{\"private\":{\"type\":\"Buffer\",\"data\":\"SDxLxjXDHNnoIPEJMsdGYXxoAqpKckJMv+XLoeJDNGc=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"Fc/3iFm2Az+VqMCvLGakW/JYY+u1TtqJQBeM504CqnI=\"}}', '2026-10-22 15:22:39', '2026-10-22 15:22:39'),
('pre-key-173', '{\"private\":{\"type\":\"Buffer\",\"data\":\"0NDQR5BkZ1XIOgfWNke/8S8UkqhtrNmCj+eEnVhZPE0=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"bFf6NvKdqVUStED5vBUaH4gYzwxFexeE+Jf2HEf0Kis=\"}}', '2026-10-22 15:22:39', '2026-10-22 15:22:39'),
('pre-key-174', '{\"private\":{\"type\":\"Buffer\",\"data\":\"4DwiMsRc5lu0VRxpODBUDjrFfNUy7UnK3cjvVyk3LHo=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"LxnBgsEcP5vCOvgV90SjqTbZAWzKzTtmaV8m+QQpsCo=\"}}', '2026-10-22 15:22:40', '2026-10-22 15:22:40'),
('pre-key-175', '{\"private\":{\"type\":\"Buffer\",\"data\":\"2Egi2XwbBNUvXadmgVGgDmKOdh0VQqCkV8BAgv/3Pm4=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"zq6AjQMxyMCR0iuQZ3JFa/8hYPpYBE17AuITd5psXQw=\"}}', '2026-10-22 15:22:52', '2026-10-22 15:22:53'),
('pre-key-176', '{\"private\":{\"type\":\"Buffer\",\"data\":\"eJUsuCRSnvpcsdf5c4oI/WXN6ORflUKxui9HNejh2Gc=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"rBLy4821NvpNTrFW0OWhICWec9SaLeFVUzBjkny/0hA=\"}}', '2026-10-22 15:22:53', '2026-10-22 15:22:53'),
('pre-key-177', '{\"private\":{\"type\":\"Buffer\",\"data\":\"oBN/X/LJFBumZcHhvv62ea0bZgRgzu6vDlVur98abUc=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"likoJMVYXuUDjIl44az856jL1vH5Qp/6nwy+fTuWIDg=\"}}', '2026-10-22 15:22:53', '2026-10-22 15:22:53'),
('pre-key-178', '{\"private\":{\"type\":\"Buffer\",\"data\":\"yDTy3aekH1p3HkMVQwvZKIlzzS+DePAT0+rU1FDx+kA=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"NoXlkZnbIV4DSi0sgNHk2MANwAw99nH3+kQswTTL6zo=\"}}', '2026-10-22 15:22:54', '2026-10-22 15:22:54'),
('pre-key-179', '{\"private\":{\"type\":\"Buffer\",\"data\":\"YKfabnDJ8zj3PEOuLWk+yF6NU3iDTtnOIYWpeG/iIEY=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"7KEpln0mdUZIH/JRgcOs6V4mW58yaeH3oYuexNL7+FA=\"}}', '2026-10-22 15:23:06', '2026-10-22 15:23:06'),
('pre-key-18', '{\"private\":{\"type\":\"Buffer\",\"data\":\"GM7Wwoms6p0AWznTN5dqTKPhT/gPiajh0sLlepfdfkc=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"RR2sgNBKrwiE5IfK4RnZXyFHEaD0NBcWYEzpKGoHsVA=\"}}', '2026-10-22 11:03:25', '2026-10-22 11:03:27'),
('pre-key-180', '{\"private\":{\"type\":\"Buffer\",\"data\":\"QIFMX27HPYTEabL3h4iNLOHKNLdLNHBaibBCKCiaFnw=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"J7qd9D3MmTjSaDcCsSdtxPvcwrl2ARXuserTAdD1qwY=\"}}', '2026-10-22 15:23:06', '2026-10-22 15:23:06'),
('pre-key-181', '{\"private\":{\"type\":\"Buffer\",\"data\":\"kMWm+DyvmMbfm3qHrK6QXGXVhK6xzpp/keDLIPQf2nE=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"1kmAcf4WUnFXIt86Ri0whhVpmR+46e4YcyUFRtqbfxo=\"}}', '2026-10-22 15:23:06', '2026-10-22 15:23:06'),
('pre-key-182', '{\"private\":{\"type\":\"Buffer\",\"data\":\"eOofErKKO2Pa3BO6OqPu2V/25SbmmtF2TJCimGmR9VU=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"wPqkLR21ytjmZW6rrVbaRpaBdx+0MlPSR4Rt8ymjCCg=\"}}', '2026-10-22 15:23:07', '2026-10-22 15:23:07'),
('pre-key-183', '{\"private\":{\"type\":\"Buffer\",\"data\":\"QLNUYmwfmLo35CNBS7H+SIqXg7sf4uC4RxAAZWjlVFU=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"Dj634oOa+niSHfm1yE2E2lx/v3uStW1JjJVOJOhBomM=\"}}', '2026-10-22 15:23:22', '2026-10-22 15:23:22'),
('pre-key-184', '{\"private\":{\"type\":\"Buffer\",\"data\":\"WJQmhyXxfrm2AiBJ/+Nz/tz61AdJElua1QROowSXXlI=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"jflfOJTdHnl9NAYEqug39wkm0jAeBwaU3floMY4b03Q=\"}}', '2026-10-22 15:23:22', '2026-10-22 15:23:35'),
('pre-key-185', '{\"private\":{\"type\":\"Buffer\",\"data\":\"8ON4RqjEULhiAxvnpkXY8Cy6sUv0Q7ojLtEZXfy0dm4=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"J9rbKK8+ZwwAKTNSZxAKSL1xRuiCNAooDCqVfIxJQSk=\"}}', '2026-10-22 15:23:23', '2026-10-22 15:23:48'),
('pre-key-186', '{\"private\":{\"type\":\"Buffer\",\"data\":\"gNpT2aZJGoPmQtp6lMCRTDnF214+7vLoPhB/rZYk8ls=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"fMb2f9ReMhzCVkrPv17KO2woPpKdzAP9rlvxelT3k3o=\"}}', '2026-10-22 15:23:35', '2026-10-22 15:24:02'),
('pre-key-187', '{\"private\":{\"type\":\"Buffer\",\"data\":\"KOlpfi801y6ScGXBHnlPle7V5JESs6yZJJyzl87CH2s=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"xKe/4HHSzJzYbOoBcVWXfH8+wDsd+b2FScBCBfsGQQ0=\"}}', '2026-10-22 15:24:02', '2026-10-22 15:24:14'),
('pre-key-188', '{\"private\":{\"type\":\"Buffer\",\"data\":\"ABlmC2rRbX/KYsP2BxoMmvq9djJ8pae9K6/wdDcgw3w=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"BqvMoCM00rYYNZC4N5zkyjWAF6iUgyioG33Ek6Lnckk=\"}}', '2026-10-22 15:24:15', '2026-10-22 15:24:15'),
('pre-key-189', '{\"private\":{\"type\":\"Buffer\",\"data\":\"MEPQAfQBIyWx39Eh9vllGoQQlsVvVZonAeLT/ivca2Y=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"EZe5TrPJ+aVPrb7Vy8oBDR4MF9/qCSp9l9il44mgNEk=\"}}', '2026-10-22 15:24:15', '2026-10-22 15:24:23'),
('pre-key-19', '{\"private\":{\"type\":\"Buffer\",\"data\":\"+Bfu86uUixl6nassJ3vWEg2sGqMDOyIKRC+cWhpb8GE=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"IN8TAR+Ojd6sAe6BLdhVCZuVsxMXHyMChRBy6jDVlAU=\"}}', '2026-10-22 11:03:25', '2026-10-22 11:03:27'),
('pre-key-190', '{\"private\":{\"type\":\"Buffer\",\"data\":\"kNszmk4VMooQSBhz9mhmd2ypa2FyvaJG9cvAb4TFvGg=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"L7Bb0ZVsuNtupAg5RQnCDE1QCwjDTPTH9+wXd0CCW30=\"}}', '2026-10-22 15:24:24', '2026-10-22 15:24:24'),
('pre-key-191', '{\"private\":{\"type\":\"Buffer\",\"data\":\"uFCV4bH2MTR8+tyf6STMXVocBQQUpX3UNw5jbmuotGQ=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"GV/+tFb+rBFWRjWaDAIdFmWn0LXjt48dok2wtmnKajs=\"}}', '2026-10-22 15:24:24', '2026-10-22 15:24:24'),
('pre-key-192', '{\"private\":{\"type\":\"Buffer\",\"data\":\"AEbUmaWDsDpI5T34aOBI7TZs5L7mHxuu/0a/D9ffLGs=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"/R1a5QSx+lysVtypWIr3ghPKwgiOHo9SZG4JVBSQgzc=\"}}', '2026-10-22 15:24:31', '2026-10-22 15:24:31'),
('pre-key-193', '{\"private\":{\"type\":\"Buffer\",\"data\":\"aJofP3D53SHedypUOyLK/Haf8nHzy8MCTt+qMDlplHo=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"Fi75+48diB8LBa2riEZaeI+nb9koCFVGaMnM6H5FomY=\"}}', '2026-10-22 15:24:41', '2026-10-22 15:24:41'),
('pre-key-194', '{\"private\":{\"type\":\"Buffer\",\"data\":\"IKNhc8Okl59pCCrEhd5H2BBhftZPSR6RhW9nHEwHVXs=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"7ydqH+nOJlyAJwJRyb9nZcxAKEqp3uz3NdTbxwyaA3M=\"}}', '2026-10-22 15:24:41', '2026-10-22 15:24:41'),
('pre-key-195', '{\"private\":{\"type\":\"Buffer\",\"data\":\"ULem1CFU4OvQ2HIAgfC30KO7uIckzNm0BLQcwtsRcU4=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"vtDalpr73F1F1RjX0u11GU7Uh7l4h6UjC90r5EXDGSI=\"}}', '2026-10-22 15:24:42', '2026-10-22 15:24:50'),
('pre-key-196', '{\"private\":{\"type\":\"Buffer\",\"data\":\"6Okvhy7Bn1mCk+off51upYII/689EbMRwctSLDjQKGc=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"e30ODMZ8fH8yUBqhD+N63HI+IyXB5kMQzO8wEAXwuGc=\"}}', '2026-10-22 15:24:50', '2026-10-22 15:24:50'),
('pre-key-197', '{\"private\":{\"type\":\"Buffer\",\"data\":\"2JVwZiSQpTEDxbGKN58TncKVfHFF+ymdCQP9jkqYf3A=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"lXgwDZJTJ+4oa2KHb7KpSVvqtQ5T15YZrzz1M8xFzRM=\"}}', '2026-10-22 15:24:51', '2026-10-22 15:24:51'),
('pre-key-198', '{\"private\":{\"type\":\"Buffer\",\"data\":\"AKoOMbavRidE3VuyFTZYvIKM5Dxj/g91OYzNgxlOQU0=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"wilVjcg5eaBqh4rsa5/j/yM16gW6P5mOJSPPNInW8n8=\"}}', '2026-10-22 15:24:58', '2026-10-22 15:24:58'),
('pre-key-199', '{\"private\":{\"type\":\"Buffer\",\"data\":\"4LjcNjrvEu1Sy26Wl5NAWfjnPJ8fLdtHSPtx2ezIIW8=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"UGmJ1Wm/nWbecbitBJRyMDgg0/p1uRemSRySIwdfRFU=\"}}', '2026-10-22 15:25:59', '2026-10-22 15:25:59'),
('pre-key-2', '{\"private\":{\"type\":\"Buffer\",\"data\":\"ENWMlfYeM4P1dAPquObg6CS9lmAK+yJcfFqjpnUOylk=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"gRwQurLRJtxynl7q1YimCdfi4J2/v2AebskJRDJPdgM=\"}}', '2026-10-22 11:03:25', '2026-10-22 11:03:26'),
('pre-key-20', '{\"private\":{\"type\":\"Buffer\",\"data\":\"oDoCoie/5lJEdRPt9u1ju5l9SEWMBf1llYIDjcD3r1U=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"7aM3hkMf2tzjpczo/+dbonIT78U7YERpGM3tFWL0Tk0=\"}}', '2026-10-22 11:03:25', '2026-10-22 11:03:27'),
('pre-key-200', '{\"private\":{\"type\":\"Buffer\",\"data\":\"6P09rndHnrpHlb1ap7zM5zZ2aUdf2AG+lbq6k6THc04=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"shrtSdhoGQZm56gBI8FhBvl1NLrmO2j5vu20ITCVGDI=\"}}', '2026-10-22 15:31:51', '2026-10-22 15:31:51');
INSERT INTO `whatsapp_sessions` (`key`, `value`, `created_at`, `updated_at`) VALUES
('pre-key-201', '{\"private\":{\"type\":\"Buffer\",\"data\":\"CAqztbZLsfTe75fvD0YNqHAzkYoINewqMyfb+oiBoFQ=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"ShWetQNt7thdad6beR+z7rjOD7TbPYtluzuFMfEp5Hs=\"}}', '2026-10-22 15:32:25', '2026-10-22 15:32:25'),
('pre-key-202', '{\"private\":{\"type\":\"Buffer\",\"data\":\"+N9qtA/rm/HRgaa9ifA4chbgDr8+HcKfw4gp4k3jgmI=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"3a5UGKv/AXlk6J6mysjlW73bJ6S2r8LkrL3J4QzbHw0=\"}}', '2026-10-22 15:32:30', '2026-10-22 15:32:30'),
('pre-key-203', '{\"private\":{\"type\":\"Buffer\",\"data\":\"2CtpA79fRzGF3MCaFM0ayBLIc77HofMUWp9mP8gbTlQ=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"1xnkpW6pEsiJW+U6swMr09hlzwKFJL39qVn0GZ99fWU=\"}}', '2026-10-22 15:32:38', '2026-10-22 15:32:38'),
('pre-key-204', '{\"private\":{\"type\":\"Buffer\",\"data\":\"YBLlaLfW/+/n7BGoR/QNbJoeoNGQtR/8WbuFP1+MJ08=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"9RngjKV/pMWev1YICjbfIpzQ719Ty+vOB6Mae1YFvzs=\"}}', '2026-10-22 15:32:43', '2026-10-22 15:32:43'),
('pre-key-205', '{\"private\":{\"type\":\"Buffer\",\"data\":\"OJ1gfGA14RZ8jWWW3BNQH6agB0u3An+lv5gSdKi+tE4=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"md660tiEwXiOZLHDryiSTV0n8D/rfTHxR29dLiKb1i8=\"}}', '2026-10-22 15:32:43', '2026-10-22 15:32:43'),
('pre-key-206', '{\"private\":{\"type\":\"Buffer\",\"data\":\"iGg6wPz9pevn2Laq5tJNrywKuE2Z6CRezlu9j/5PNHk=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"runRFUfkZdC+X9GIuHTCCS6EMiHKKZBrfbEreIoq+Qc=\"}}', '2026-10-22 15:32:50', '2026-10-22 15:32:50'),
('pre-key-207', '{\"private\":{\"type\":\"Buffer\",\"data\":\"+FpisWz5rKaQ+MXcR1uyeo+vwjUbBxOsl/UEf2i92UE=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"D6Gh7d97PaW3UCyShK5RiWuDorDZifTWlumJL8ewfj8=\"}}', '2026-10-22 15:32:51', '2026-10-22 15:32:51'),
('pre-key-208', '{\"private\":{\"type\":\"Buffer\",\"data\":\"+K/7ZeL/Qa8YmX5QO2D1h7qhSgHNM6he5T+xjVe0B3k=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"DByeW5Y2dg9vm21d0BFs6w6ez+uadcmn/rCHpJMInUs=\"}}', '2026-10-22 15:32:55', '2026-10-22 15:32:55'),
('pre-key-209', '{\"private\":{\"type\":\"Buffer\",\"data\":\"sPGEsjh//z0r3FdI4cwr7yO8c6VUqbPu9Rs6mDWMp1k=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"s5WC+7tb6DctNkyP5sxqZRhDIC7+Wqp210ELIRsqIQI=\"}}', '2026-10-22 15:32:56', '2026-10-22 15:32:56'),
('pre-key-21', '{\"private\":{\"type\":\"Buffer\",\"data\":\"4Bl4TUxtza1j0pV72u+/stBnrk8s3Lxri7Qb4Udp820=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"2HsaWeDRe1PRWlUYBhyCKF4w9EM7u+xN8QM3p+EM+hc=\"}}', '2026-10-22 11:03:25', '2026-10-22 11:03:27'),
('pre-key-210', '{\"private\":{\"type\":\"Buffer\",\"data\":\"qO7PEgnYHpDfJyxKbFDtUU3jRgolVtDLua37LmkWD04=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"Qt35lfMOpqzWYEcJBnLYLgpfsi8ZhW88COLYw7BTzCU=\"}}', '2026-10-22 15:32:56', '2026-10-22 15:32:56'),
('pre-key-211', '{\"private\":{\"type\":\"Buffer\",\"data\":\"MIIC83lyovy1mGwHl5XQpL3Mq+36RhBAiXAPlRpPv1o=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"d3zJaDC7iyj0mWc+RLYtX+zfPwhPJNhnNk/fJtyi3Q8=\"}}', '2026-10-22 15:33:04', '2026-10-22 15:33:04'),
('pre-key-212', '{\"private\":{\"type\":\"Buffer\",\"data\":\"eMREMIhIaIYubm7Q0//b8fdGdJv+7y698vIGcIZ+FEk=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"0ZBf4CI4LmI1G7YeB0sa9gQV8woLJ08fTimTguDG1ic=\"}}', '2026-10-22 15:33:04', '2026-10-22 15:33:04'),
('pre-key-213', '{\"private\":{\"type\":\"Buffer\",\"data\":\"ICB9auGVX0ri5oez2RldHYmVd0pcvqYsDatZgBrngWk=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"rxjqckEciWl2sXEGD4wvhNuDNF2s+/qfCbu1WOaJkXk=\"}}', '2026-10-22 15:33:05', '2026-10-22 15:33:05'),
('pre-key-214', '{\"private\":{\"type\":\"Buffer\",\"data\":\"sIBuiomN4bL+g7nGzc5XJRkyJNgB1sSgeQDEkEfOPFM=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"s0U4d3AiG2O3S3rflFcrgmhGHzKcdRlwWdJngNV2zHU=\"}}', '2026-10-22 15:33:08', '2026-10-22 15:33:08'),
('pre-key-215', '{\"private\":{\"type\":\"Buffer\",\"data\":\"UJtzzVXbiJQXZ9R5lPRb5BPU0mrkGrMmrQ+3pv7PqHU=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"U6D4gPxvZJ2NkWesPBwC7IdLULw/nav/zEfNpNmByX8=\"}}', '2026-10-22 15:33:09', '2026-10-22 15:33:09'),
('pre-key-216', '{\"private\":{\"type\":\"Buffer\",\"data\":\"ENc6ZabkID59KCfFWZvFedPREjYdW7+XrAVDvC/0Mng=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"ol2K5FEqRapUhunvrlvvybLkBjCvCVTk7hHKorbK7Hw=\"}}', '2026-10-22 15:33:09', '2026-10-22 15:33:09'),
('pre-key-217', '{\"private\":{\"type\":\"Buffer\",\"data\":\"cHioqslhJiAUCUR9xEDdFI0gxJ8rVKbcbOnSnqW5SU0=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"mkf5ksVFUDDf1ThFwUpK/idBPf/pzj9X8jqdul+QoGE=\"}}', '2026-10-22 15:33:09', '2026-10-22 15:33:09'),
('pre-key-218', '{\"private\":{\"type\":\"Buffer\",\"data\":\"aIppUYTEpTCR1AT2TajcPs+yPDrMW3Z7O9Ka543bM2s=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"qHR3/CSz3pyL2UUpRwhqjLT0ZAWAeVBaToIPSbNhRE8=\"}}', '2026-10-22 15:33:17', '2026-10-22 15:33:17'),
('pre-key-219', '{\"private\":{\"type\":\"Buffer\",\"data\":\"kNJ2a6GpBoj2RkZlv0m+Qk2x4lfHGADQ/bXdnwqz6UY=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"g3wKPsuogigNDVKBKGFTZtOQfGH8fBa4OCIBgtWySGg=\"}}', '2026-10-22 15:33:18', '2026-10-22 15:33:18'),
('pre-key-22', '{\"private\":{\"type\":\"Buffer\",\"data\":\"mFALCBt4t4V33zNCjJowUNKUlig+xq17JYNPYrRm1Vw=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"KUdMT/yTHH3x3y1h8CoAPAsaciM62DugKeM9/ihRHzw=\"}}', '2026-10-22 11:03:25', '2026-10-22 11:03:27'),
('pre-key-220', '{\"private\":{\"type\":\"Buffer\",\"data\":\"SKXlc3ZHk86v8Ic2QipdjxAZQU1fxrZUBgRKxTVBVFw=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"3xkYx2XxZHsngfu72ghNGd6Mui3q9wtcE+gDw7L8B24=\"}}', '2026-10-22 15:33:18', '2026-10-22 15:33:18'),
('pre-key-221', '{\"private\":{\"type\":\"Buffer\",\"data\":\"KKovJGAWdARb0Xc6J4H2t6T0anyq4ETJS1koXLkIEUw=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"myQJm2/HSTxYck0/fLDPAwUeLa5xK9Sw5MeWSgnefTE=\"}}', '2026-10-22 15:33:23', '2026-10-22 15:33:23'),
('pre-key-222', '{\"private\":{\"type\":\"Buffer\",\"data\":\"AKOQD6i7AFDUZhnFeh3DhZhqZP1RXABGks/FhBdQ5Us=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"i3LMhixPZX4JFL+UvCxtsbU4Wse1KBhNwdROF60Otxc=\"}}', '2026-10-22 15:33:24', '2026-10-22 15:33:24'),
('pre-key-223', '{\"private\":{\"type\":\"Buffer\",\"data\":\"yO6cTlGUQnaIu0+2+TQGHC9M1niYMWgOZOTp85UIIUc=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"WoMYyhfsmIaZ3gu1Q2pstb/ybYcFPT0pOMSdtULGJng=\"}}', '2026-10-22 15:33:24', '2026-10-22 15:33:24'),
('pre-key-224', '{\"private\":{\"type\":\"Buffer\",\"data\":\"YL3ofHI1DhyrMmCOxlBrskiEvvcCQq8bIreA/n9gQUs=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"+gbNgGouGsRQtrZ+/PoR5xLWOA2gGxsPo5VnjNt/WW8=\"}}', '2026-10-22 15:33:24', '2026-10-22 15:33:24'),
('pre-key-225', '{\"private\":{\"type\":\"Buffer\",\"data\":\"cPy/J4Ds4F3wDEs1PksMIZrjuFWHiv0ChvdKArSSblw=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"0bxtZx0ebQao4NhS09cK9ldyzHocCT02enGqoxdylT0=\"}}', '2026-10-22 15:33:42', '2026-10-22 15:33:42'),
('pre-key-226', '{\"private\":{\"type\":\"Buffer\",\"data\":\"eG+SSfpFL+ZfbgX98LZVO5kYd75/Own3ehaYVCqfwk8=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"ytMuqiTbjJ0kSadix3ftA5dA4hy0O+p0zsWiBThNgUM=\"}}', '2026-10-22 15:33:42', '2026-10-22 15:33:42'),
('pre-key-227', '{\"private\":{\"type\":\"Buffer\",\"data\":\"UNqxDasBQW5alh8SO1ZLVnDij6rcgMCDKKSr0Xb1BX4=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"RuqcF3exqCh5Wth9al5k3rgJP6MmtPKt++E+pllmV3c=\"}}', '2026-10-22 15:33:43', '2026-10-22 15:33:45'),
('pre-key-228', '{\"private\":{\"type\":\"Buffer\",\"data\":\"+INGB35ASPlzyqlb+aK1HBf+EeUk2bPZ6FFfT6wWiHI=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"yzI5oSCJbNAu0QW8C9xv2XLk+g+6NNk2RW0UomdiLWE=\"}}', '2026-10-22 15:33:43', '2026-10-22 15:33:46'),
('pre-key-229', '{\"private\":{\"type\":\"Buffer\",\"data\":\"WD3k6WVSoXd7rL0DXt7HhkWlzc3AjVM1wLYjxi/k2Go=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"3ySxTLHT4iZz++3YoffpiTKqkRVjAoBUiqfpxhLDk2Q=\"}}', '2026-10-22 15:33:46', '2026-10-22 15:33:46'),
('pre-key-23', '{\"private\":{\"type\":\"Buffer\",\"data\":\"0O2GpynbjkVposRBv+teLnkt6/IWOVhGzXlsgIlRv1Q=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"2PcdjNnhdC2HD9jd4Tk4eVvxulWfQPs1/rtC5YBNIHM=\"}}', '2026-10-22 11:03:25', '2026-10-22 11:03:27'),
('pre-key-230', '{\"private\":{\"type\":\"Buffer\",\"data\":\"+L5I3GqxT7YXlGkkGYwGxHA55/CgOOyi/jPB1c4xH3o=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"0a7apQfzIsFkb15SAjIJpfUhzOD9ns7QguXnK5WRPhY=\"}}', '2026-10-22 15:35:43', '2026-10-22 15:35:43'),
('pre-key-231', '{\"private\":{\"type\":\"Buffer\",\"data\":\"IJUJgYQEdRKv2D2XfFq6UG8dEkEFI7mRFnJZBTXSiWA=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"0FjOoCmYzDMa60EoB5xoQvsscRAzRvnmCeLmdKwxwG4=\"}}', '2026-10-22 15:35:48', '2026-10-22 15:35:48'),
('pre-key-232', '{\"private\":{\"type\":\"Buffer\",\"data\":\"sPSjLv2cTeZP9f/56QJFfaV0hVhTouU+tBzTznRWkUM=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"GxkMTgesIsFmbxivJZlEqMp/Jx0iCPWsfCrRrqRmwBM=\"}}', '2026-10-22 15:35:56', '2026-10-22 15:35:56'),
('pre-key-233', '{\"private\":{\"type\":\"Buffer\",\"data\":\"iCwtLLdAFRDNXFAlC7B5jyrxMGReMSNB/XeCtgjgzn0=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"cl0UI5r/6lwlWCOLCUs1UVan7TeskQ260idyELzo8yA=\"}}', '2026-10-22 15:35:57', '2026-10-22 15:35:57'),
('pre-key-234', '{\"private\":{\"type\":\"Buffer\",\"data\":\"EF+iHwE29pfqa9GosCbaWaUNiebMdfBPy97tqLprtWQ=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"ZJqJGNnFmlkEAFsEcjsken6i0yxdIWn7qFIofqL6DG4=\"}}', '2026-10-22 15:36:02', '2026-10-22 15:36:02'),
('pre-key-235', '{\"private\":{\"type\":\"Buffer\",\"data\":\"6AhzqLdVevX1ICgs9EMw2TrpS9QH5PLz5KPvMukUvFU=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"D9YBMLGrQQpHyHgnvrpXoUPlU+G6ns3GZ8DyLmKLB0o=\"}}', '2026-10-22 15:36:02', '2026-10-22 15:36:02'),
('pre-key-238', '{\"private\":{\"type\":\"Buffer\",\"data\":\"kNTRE4YL+KL6O/GReRg7oO2EyE7d2WxptgWCoMoQGWI=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"SL5VUkdXWQTlSrUteHL9ltEtBV3ZZh+hq73gHcNDhn4=\"}}', '2026-10-22 15:36:15', '2026-10-22 15:36:15'),
('pre-key-239', '{\"private\":{\"type\":\"Buffer\",\"data\":\"YJ2b22gP0K1di7mOOcPtIxirbSY6oayK2Y0SeXCzyko=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"CabU9FPmx7wcymy/zd/FKiiuq2wyYmxDL1/la0fkWjM=\"}}', '2026-10-22 15:37:06', '2026-10-22 15:37:12'),
('pre-key-24', '{\"private\":{\"type\":\"Buffer\",\"data\":\"4KnQdBCSi0qkU2G5L0nLbv8FiNizLHjDsx2Jy0aH6ks=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"ZQbbYQGKxjBh2TYtNw0Ucz2ws16a1WNCHYvtYk9oRBg=\"}}', '2026-10-22 11:03:25', '2026-10-22 11:03:27'),
('pre-key-240', '{\"private\":{\"type\":\"Buffer\",\"data\":\"YFtRC/PZacIrdpVTd2bTBBZoAJvvPVo+M4zav2rP9mY=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"Kx5O6EdpZWtOs84H0ctd9KaBAaxsMBX6pS0nueANzg8=\"}}', '2026-10-22 15:37:06', '2026-10-22 15:37:25'),
('pre-key-241', '{\"private\":{\"type\":\"Buffer\",\"data\":\"UElvoacwIi5tFICaWTdQ8UXRT+xATRywcj9ixYviv3E=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"BBuEw6wsWM7Z5V0i4DbZUkOh4SS38JrF86zIkf4BlUI=\"}}', '2026-10-22 15:37:07', '2026-10-22 15:37:37'),
('pre-key-242', '{\"private\":{\"type\":\"Buffer\",\"data\":\"2IVwLsAEHePxC9v1yzDbL2YCAhPjQaLfVbBAYBU+TlE=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"SjetlN0//XPKJx6drGsHcviPgyehWgBqD0YL9/j0BSA=\"}}', '2026-10-22 15:37:43', '2026-10-22 15:37:49'),
('pre-key-243', '{\"private\":{\"type\":\"Buffer\",\"data\":\"kF8ECCn2R5HylUcveeVvFNLV3zZxQsHmrSF/zqd7/2k=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"b4DRQK9riYr1Q0e6p+ZHDvoxPL6Ae3rqmyT5sg+A+zI=\"}}', '2026-10-22 15:37:43', '2026-10-22 15:37:50'),
('pre-key-244', '{\"private\":{\"type\":\"Buffer\",\"data\":\"GEBpH5WfVE6CAJ4CSBsdwnOhbpdl9R3F3fCjrShqcGY=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"g03B3v6E21Z3DctJnxbTnX6TkVaF7QBzhKCMERKE9As=\"}}', '2026-10-22 15:37:44', '2026-10-22 15:38:01'),
('pre-key-245', '{\"private\":{\"type\":\"Buffer\",\"data\":\"SPLkTkA8d02L0P7Q0NoVzfG5RFIde5FJioZbEEYv02k=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"HfUo2Sl2Q0CmP1zuT0ksOr+zAnN5pNrRaIbEUyczgSU=\"}}', '2026-10-22 15:37:56', '2026-10-22 15:38:02'),
('pre-key-246', '{\"private\":{\"type\":\"Buffer\",\"data\":\"CDp/sN3dgxUBSKPQrKJvMfD6iTIYU1xNHB5qIv7z5kI=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"0UGzEnPt5K8s8rH+Vrp23dR+h6MeaI3FsV+XrGWpijw=\"}}', '2026-10-22 15:38:02', '2026-10-22 15:38:02'),
('pre-key-247', '{\"private\":{\"type\":\"Buffer\",\"data\":\"IEbl78/paQUtVXY9cbEOb6BZ7a0kBXcIPEF65OXDyFg=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"DSHPCqGcVPMVRbk7iFIOh+MfhsydQZmuoM67lHaz0jo=\"}}', '2026-10-22 15:38:13', '2026-10-22 15:38:13'),
('pre-key-248', '{\"private\":{\"type\":\"Buffer\",\"data\":\"SGn7sKf8EaboRhNDNs+g674ncsCOOE1xhnMWSAD+MEI=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"L2kLuqdqKU610iwkmkTEt5Hbn4vRS10nLcJNIwxGx2E=\"}}', '2026-10-22 15:38:14', '2026-10-22 15:38:14'),
('pre-key-249', '{\"private\":{\"type\":\"Buffer\",\"data\":\"2AKKVkaS4CG9Z9S1zoqHXrO0XQfyf3oqoqLMX/3dVVA=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"lwq5o9bgZ3ghxTI0V8q0frpgrfGfUMhheuS06pjYI2w=\"}}', '2026-10-22 15:38:14', '2026-10-22 15:38:14'),
('pre-key-25', '{\"private\":{\"type\":\"Buffer\",\"data\":\"4MNGz2s8RWDeHX9QZyY7RtqgtTTcFto++7Zm5/wlxVg=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"ykok2AwRdCJf0N2rjKFwV5SpyWp/70TiUmmCvrvEwSw=\"}}', '2026-10-22 11:03:26', '2026-10-22 11:03:27'),
('pre-key-250', '{\"private\":{\"type\":\"Buffer\",\"data\":\"mH1lxnYoWB3WPHKVUaoSCq1istr3H9kCLG08uQ021HQ=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"nAr++IGQ0PMi9LqhU1FwgkvtYOe3yknTShABh5INPhw=\"}}', '2026-10-22 15:38:15', '2026-10-22 15:38:15'),
('pre-key-251', '{\"private\":{\"type\":\"Buffer\",\"data\":\"KM4w94H6HvlO4d5QK3wnyr00sgubzEjgNXDxVlbOr2o=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"cAIIXj421uc6wCabIJgMlZ0fqqLSuNgm/fRJSYekDVw=\"}}', '2026-10-22 15:38:19', '2026-10-22 15:38:25'),
('pre-key-252', '{\"private\":{\"type\":\"Buffer\",\"data\":\"KJcRRzVpEHmDOGKPFrvBPp0Bhw9AKE6YeGM4XbzP0Ug=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"xFadnk22v73YUZ7qbx9H4VRQfg8dbJ1/DKe/fumDqUY=\"}}', '2026-10-22 15:38:19', '2026-10-22 15:38:26'),
('pre-key-253', '{\"private\":{\"type\":\"Buffer\",\"data\":\"QMjJjcR1JfMGewC5RyKrlht6TkL4N+NEtXIOX7aQ2lY=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"FEO10IAE240XjYchz7Va6NpEpqFbp3wiOi1yg1x7iGE=\"}}', '2026-10-22 15:38:20', '2026-10-22 15:38:26'),
('pre-key-254', '{\"private\":{\"type\":\"Buffer\",\"data\":\"WKIoz9L1s4Uo97iGnzyl5Q8YbdeYHWAuFkBRoObUmkI=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"dvhNMf8+Fr+sKn216xrXkZRUs6kJH+VTDaAZSVSc2BM=\"}}', '2026-10-22 15:38:20', '2026-10-22 15:38:27'),
('pre-key-255', '{\"private\":{\"type\":\"Buffer\",\"data\":\"OD4sJq76HYDRd1L1Oioa9k4VqF7gONvajhOCuAvlyU4=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"q/I00TBBOoTxO2+5IrZf/Tl3+stVofgnptZ0uhT20gA=\"}}', '2026-10-22 15:38:31', '2026-10-22 15:38:50'),
('pre-key-256', '{\"private\":{\"type\":\"Buffer\",\"data\":\"OO/pE7VdXKwa/WR5dePEpuRy9/R4bvVoU3fEttZuLmU=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"XKbXO6zJJjL5cnUK4oTZsWv7iV+oqXrXngUR3Y7x5RU=\"}}', '2026-10-22 15:38:32', '2026-10-22 15:39:01'),
('pre-key-257', '{\"private\":{\"type\":\"Buffer\",\"data\":\"4GKRbfNYNyrQnHnexY6zmYuC8BaYoV//TOtpY52aqHI=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"NFIW9i5SMtflTSQ3/bLq21491g9Jn3dZkHqHeI6f8AU=\"}}', '2026-10-22 15:38:32', '2026-10-22 15:39:02'),
('pre-key-258', '{\"private\":{\"type\":\"Buffer\",\"data\":\"yN+WGoF8ORsoZgmdoARQwyzS3jOeh4b0VXzGDj/i1lQ=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"gAYwzjTKjmD29c7Jq6xjDxxaqhJrA31mlmgq4+BFMjk=\"}}', '2026-10-22 15:38:32', '2026-10-22 15:39:03'),
('pre-key-259', '{\"private\":{\"type\":\"Buffer\",\"data\":\"yP72lcP2Uop2Oojkh5ZhQLjlZvwhE0J01VKNkYWJZWA=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"j7sNyk3hikTbW7O5Ib49lsqwBnFvN0Yakti7wq5cAjY=\"}}', '2026-10-22 15:39:07', '2026-10-22 15:39:14'),
('pre-key-26', '{\"private\":{\"type\":\"Buffer\",\"data\":\"CAjKn/FPBqCSEe2kYeT/2JpOebWALwObQvDFEuH/RFQ=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"LxSGhbq5veAecfGLgEJwlKbqN7ItJAkKCBmqeUQYfSw=\"}}', '2026-10-22 11:03:26', '2026-10-22 11:03:27'),
('pre-key-260', '{\"private\":{\"type\":\"Buffer\",\"data\":\"SK4LRbG5Ra+LBIeNt4XvL1/XQ4VD2GvRm/Se1PXHgVE=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"4QtU1T3VzunZWd1ol+IZ6S9Aa4HnNDERLnKtdsdoeE0=\"}}', '2026-10-22 15:39:07', '2026-10-22 15:39:14'),
('pre-key-261', '{\"private\":{\"type\":\"Buffer\",\"data\":\"+KZ99LcWwMG+mXizoK1XB9ikve5g/bNWqgYwjFDWNEU=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"clyAUpFGx7CXuQxGEpvbY1QPaEP2ksKzVTVk+uitcCk=\"}}', '2026-10-22 15:39:08', '2026-10-22 15:39:15'),
('pre-key-262', '{\"private\":{\"type\":\"Buffer\",\"data\":\"EMy2kWgreK38nBgmTfaZIxkyl1nIHqfQkmBKlHeIUWw=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"rZGo7rY6PfCK1QqKSxnHJ68iO+K4WouiCqzGaosE32M=\"}}', '2026-10-22 15:39:16', '2026-10-22 15:39:16'),
('pre-key-263', '{\"private\":{\"type\":\"Buffer\",\"data\":\"YDy4QGcbK5LRdvvdE6sc4FmpBfD1VAfb1FshuqZznmA=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"x2XBtIodESRawt/uuDowCHloiB9Igb7sfnNBph0KKR8=\"}}', '2026-10-22 15:39:20', '2026-10-22 15:39:26'),
('pre-key-264', '{\"private\":{\"type\":\"Buffer\",\"data\":\"yGy7nXWsQ6XsQVcWFHCfewDtQywU8mDfXtX2HLKDpns=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"UiVqjEu4NglMaaUVS5A3Hv4yt+HALUIxHzAE+Q2EXxc=\"}}', '2026-10-22 15:39:21', '2026-10-22 15:39:27'),
('pre-key-265', '{\"private\":{\"type\":\"Buffer\",\"data\":\"eBQBpMa6wmylW5QPige+liEDkoHTCk37+P0ucfgDSFU=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"G//lCNtc3W6W5LY1bPFpqcgVu5V+AmlytlYjzrXr+lA=\"}}', '2026-10-22 15:39:21', '2026-10-22 15:39:27'),
('pre-key-266', '{\"private\":{\"type\":\"Buffer\",\"data\":\"YKDLxy7TtyOy17sCTx2sYmn8G4kQz/yS4DWqJTM+DGA=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"cRQomDfoKzbtMQ1sEskLKL++AkKNvGHYIBopox3HDhE=\"}}', '2026-10-22 15:39:28', '2026-10-22 15:39:28'),
('pre-key-267', '{\"private\":{\"type\":\"Buffer\",\"data\":\"iLDbHnICciZAtauY24Hr836LG2nZM6AaADlPJGs7zXk=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"ddoddszkq8BRFZYpawT1MQ5JXETDwQ/hKD93G3owW1U=\"}}', '2026-10-22 15:39:32', '2026-10-22 15:40:14'),
('pre-key-268', '{\"private\":{\"type\":\"Buffer\",\"data\":\"YCjxo9blz4T9mwFxA4V72G3QGy7a4KIwsCZhM9kPI14=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"dmspuutDsICosUWr2UAJLvaKsCesz2BzBbREc2BHojY=\"}}', '2026-10-22 15:40:20', '2026-10-22 15:40:27'),
('pre-key-269', '{\"private\":{\"type\":\"Buffer\",\"data\":\"SEiqsMjKwxg+2es1FwTBmd0ubstBXv9sWSakx0/bZl8=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"1BHV1e9zEeCHm41//LqIvQk2dqr9BzcjUZeB1QOojF8=\"}}', '2026-10-22 15:40:21', '2026-10-22 15:40:27'),
('pre-key-27', '{\"private\":{\"type\":\"Buffer\",\"data\":\"oDHpMwm0WVP8EzNUFMI7TVOhYLboe4fFQQhSF3s3Nk0=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"nn077Rht5TW1tdhrJeQTeAGqc29LKtaofifQhJGAohM=\"}}', '2026-10-22 11:03:26', '2026-10-22 11:03:27'),
('pre-key-270', '{\"private\":{\"type\":\"Buffer\",\"data\":\"CG1iv6G6BEPOLf71hs9r1eCVTJHj1qgwATn2JGedvlU=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"SGlWgc+ZWELtXdieCINJobcmx5FYVFqBmynSsqAxp1s=\"}}', '2026-10-22 15:40:33', '2026-10-22 15:40:40'),
('pre-key-271', '{\"private\":{\"type\":\"Buffer\",\"data\":\"KHUKQLdbGhAop3kUGfCray3gs0MyK80SSXs4ZufF4no=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"zhaLS/dyEwWJQS+lTMiTnTp8N3mpmvnxIE6lOvlI5zU=\"}}', '2026-10-22 15:40:33', '2026-10-22 15:40:51'),
('pre-key-272', '{\"private\":{\"type\":\"Buffer\",\"data\":\"gD9HVngQ/KztBdf/G4docBcLqXBwxeze2ZYlVTGFbVw=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"dyIzqqYWRavP3KPw8219svOHNFrvv+H6N95bFowrkAU=\"}}', '2026-10-22 15:40:33', '2026-10-22 15:40:52'),
('pre-key-273', '{\"private\":{\"type\":\"Buffer\",\"data\":\"mIfs8FUOL+Y4uqQWpQSQ7kcmdUALseu6NJTxIBZghXA=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"5JFrz5ozem5b1vIPPLAbMPIrQCdVVLBz1FQ01cyXCRM=\"}}', '2026-10-22 15:40:46', '2026-10-22 15:40:52'),
('pre-key-274', '{\"private\":{\"type\":\"Buffer\",\"data\":\"uLuMAu/lDGBx8g3coSZtyes3NaejhgJ1/8r2Bdxrvm4=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"eqnt8t8JRv76ooXorbQzZpzcWdodC0bRQHgbV20tF1E=\"}}', '2026-10-22 15:40:46', '2026-10-22 15:41:03'),
('pre-key-275', '{\"private\":{\"type\":\"Buffer\",\"data\":\"aNGCySMVu4V7Dm7pJHns0ggWfMIswYhK5djyBF7fOV0=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"ApXBvHIZhoSDBOAIQZPfZMQZIamF2ATf+yxibHX8Ryw=\"}}', '2026-10-22 15:40:58', '2026-10-22 15:41:04'),
('pre-key-276', '{\"private\":{\"type\":\"Buffer\",\"data\":\"OIAwOcu36+aPcH4wDE2TL2NQ8A7c9oEaLyeu3CnC8UY=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"D9DU5euXbCwsnNSTR7sfMNDb2q5xWqqMgenATX6A7Ak=\"}}', '2026-10-22 15:40:58', '2026-10-22 15:41:04'),
('pre-key-277', '{\"private\":{\"type\":\"Buffer\",\"data\":\"UOSfJgfOsRVCV6EpQpfnj2u6fiWC88oWGiMvbpdZM00=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"PQa7mZPnuDtxPOjtSSntWsVWRMaxmU/e2bSAjJETJHo=\"}}', '2026-10-22 15:40:58', '2026-10-22 15:41:15'),
('pre-key-278', '{\"private\":{\"type\":\"Buffer\",\"data\":\"eD8lZtNd0xNFSa1qCu5uoPC/kh0c/M9WeJZYL43Stkg=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"/XOG8kNszttWBcmAmq40NrJNNjqfzXik/gCVC8h03zg=\"}}', '2026-10-22 15:41:09', '2026-10-22 15:41:16'),
('pre-key-279', '{\"private\":{\"type\":\"Buffer\",\"data\":\"CPQ41I7IwGPNJnJLLsrfYd1puVLLWgooz82cXwQOsl4=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"zZ+by04iIdIAVTR4KqxosNRvRyd2VlQA47oo7WG1OH4=\"}}', '2026-10-22 15:41:10', '2026-10-22 15:41:16'),
('pre-key-28', '{\"private\":{\"type\":\"Buffer\",\"data\":\"GNaKii5tpybP8OXCRskMpjiOq/9voqBjIislHulJL30=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"0pvsOICMLRzehz9+KnQXtt7aaRbpMIxsPL9rAXzK9iE=\"}}', '2026-10-22 11:03:26', '2026-10-22 11:03:28'),
('pre-key-280', '{\"private\":{\"type\":\"Buffer\",\"data\":\"UMCiQ8ZnCXUbtzOWgCsYECrStdArdtKFPcUDOjOS/EQ=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"J0PI2zWoIdz3CHcJprPx8IscpAAska9fN/RxdSEIQHc=\"}}', '2026-10-22 15:41:10', '2026-10-22 15:41:28'),
('pre-key-281', '{\"private\":{\"type\":\"Buffer\",\"data\":\"6BN76WHxsAnkdRUNNVeXvdk/6ZiuFeDD+a246jiCvWM=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"RJG+HNIPyFmM0SWF9Y8yy+Df9kv3dVkch5jIHp9XN2c=\"}}', '2026-10-22 15:41:23', '2026-10-22 15:41:28'),
('pre-key-282', '{\"private\":{\"type\":\"Buffer\",\"data\":\"QNPuJcqsWzVZDEXsxBtbdDAY1BCaiPPR/lJcazVWel4=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"IjBmdksUhJNOWah6Gj8CW6lvxS2SO9RqE7Opbz+Z2yY=\"}}', '2026-10-22 15:41:23', '2026-10-22 15:41:29'),
('pre-key-283', '{\"private\":{\"type\":\"Buffer\",\"data\":\"WIlG0loPOjECGBoc9v8qpryEvOVV3AwNuf7aa74fhEI=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"Xc7G27Js5jcs69tvWB9nqPJ3hRidJw2Xr4UnQBOAwT4=\"}}', '2026-10-22 15:41:23', '2026-10-22 15:41:29'),
('pre-key-284', '{\"private\":{\"type\":\"Buffer\",\"data\":\"QLlHpgPvsPyKXdN2RTDO6V/BTxB2KEgqvfHoudsB/HM=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"poG/K2UAbhG6y2/WupSvkP0sPtyv8y5uX1dN34paKQg=\"}}', '2026-10-22 15:41:39', '2026-10-22 15:41:39'),
('pre-key-285', '{\"private\":{\"type\":\"Buffer\",\"data\":\"OI2+mQjS6gjeikHNt+ijnej7+v1sNnDkrZVqIQJ8FXk=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"+hBewR684qEcrdzE6dx+yxRIgciPnJ+k5PbxOysGg2U=\"}}', '2026-10-22 15:41:39', '2026-10-22 15:41:39'),
('pre-key-286', '{\"private\":{\"type\":\"Buffer\",\"data\":\"ADryxu48Ba7yW5Uh38Dh6BBWlBA8qF+V/Nsd3CopIWg=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"sQ+18XR57L4xOJSjNAgM6W35xL9D2vKKtIMH7riL1mE=\"}}', '2026-10-22 15:41:40', '2026-10-22 15:41:40'),
('pre-key-287', '{\"private\":{\"type\":\"Buffer\",\"data\":\"4GDqknzkTOIluo1GZJiqoGdzImhZBzeeROz3nDxmP0c=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"EQpY7QTrZsUfP87Ev0+KBgUuz/+RTNjiGAgB88lcGXk=\"}}', '2026-10-22 15:41:40', '2026-10-22 15:41:40'),
('pre-key-288', '{\"private\":{\"type\":\"Buffer\",\"data\":\"SNh+pMSz5QEFKQk3+vFMH1z+kaT7LSkOtvYeNFNh5U4=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"wjbGUNL6PGhrHHlZLvyvtAP1DmKhgCgPpdIwa12ULyE=\"}}', '2026-10-22 15:41:41', '2026-10-22 15:41:41'),
('pre-key-289', '{\"private\":{\"type\":\"Buffer\",\"data\":\"qJae6A1XATPlGuNWX37IM2eV8x5145FojxvLSWgyjmY=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"eV2el11xChULnr4AppeMTMhjCgc8ISJ+jHy1Gp2Yvhc=\"}}', '2026-10-22 15:41:44', '2026-10-22 15:41:44'),
('pre-key-29', '{\"private\":{\"type\":\"Buffer\",\"data\":\"AJ9Olk/87Ryj7MQzZvPBewgjKyHKmJSrGmJTDKHE0mE=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"jPURIWZaSjzOc34L+AF+IamA7Gmk9PV1N3zzjiESHCI=\"}}', '2026-10-22 11:03:26', '2026-10-22 11:03:28'),
('pre-key-290', '{\"private\":{\"type\":\"Buffer\",\"data\":\"sAHpkgMBGEChMqnEmcz/xUiNvaGwlRr6cSfxURqGeEM=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"KAe0p1MRuvL7aKcLMV6/ZwcxeNpANTXNPPkhDDjcK2s=\"}}', '2026-10-22 15:42:03', '2026-10-22 15:42:23'),
('pre-key-291', '{\"private\":{\"type\":\"Buffer\",\"data\":\"2O7nWupbbL7YlW4lvJjuohm/Eic9KSchjUAs9W4UaFE=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"3w8ofx+HBESBGzGEC20Rct10OxUYjapu1YsU3+zmuxA=\"}}', '2026-10-22 15:42:03', '2026-10-22 15:42:36'),
('pre-key-292', '{\"private\":{\"type\":\"Buffer\",\"data\":\"kMERWJcQP6rXDbueGIC/QoIKAgfGC+DY605jaTRWlWw=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"CsWZeOjQQHnvHM/Cvxe5LKWXSGQ/e+aqpnHYy2THhDk=\"}}', '2026-10-22 15:42:29', '2026-10-22 15:42:47'),
('pre-key-293', '{\"private\":{\"type\":\"Buffer\",\"data\":\"gCUbDG12gnT+JMZWcJ+Oe4/xrJonc9sVePm2kQlVfUU=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"T1vabL/UY+dNCAkHz0Gr5F2XF3dsTHU6XR7ydcOmaWc=\"}}', '2026-10-22 15:42:42', '2026-10-22 15:42:48');
INSERT INTO `whatsapp_sessions` (`key`, `value`, `created_at`, `updated_at`) VALUES
('pre-key-294', '{\"private\":{\"type\":\"Buffer\",\"data\":\"WIwQidYxPJrdPZY5jg+1RJckquYomuNAlUjtiLDXZ1c=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"w9gMKCee2JxeE5kYXalQaMzJJJ4mysh2aS27dgT+PWw=\"}}', '2026-10-22 15:42:42', '2026-10-22 15:43:00'),
('pre-key-295', '{\"private\":{\"type\":\"Buffer\",\"data\":\"sOncaOP3PLqKhCPqy0eJY/PMzNHhLwJI3JQYetC3jlo=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"llMfkDiDIz/Sba3BX+YNvMHQNgLiBgUtPC5q2a0Dmj8=\"}}', '2026-10-22 15:42:53', '2026-10-22 15:43:00'),
('pre-key-296', '{\"private\":{\"type\":\"Buffer\",\"data\":\"kJBkjx3+UILAsGVlObl6wvIlImwPAOhAP+pLrmqJB1I=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"ap7A2rZjsT/aJwLHOS3yyuYWVPA0VoXDb0ygnlwi9TE=\"}}', '2026-10-22 15:42:54', '2026-10-22 15:43:01'),
('pre-key-297', '{\"private\":{\"type\":\"Buffer\",\"data\":\"yCnKoYeajUWpHzx7snRehB56Sa3W+tgCquBDzte87ks=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"gisOCAHVwhrtqTMFdFYSngZ3xdHv8WojUoky9fo3jko=\"}}', '2026-10-22 15:42:54', '2026-10-22 15:43:13'),
('pre-key-298', '{\"private\":{\"type\":\"Buffer\",\"data\":\"+KX4OPucNufQUkLOi3potqS5gMKRgBPpuBi5cknxS2s=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"GsuFS04ehfG3EYCDRdN0a++rCgl56Yd77HXWsySx/jY=\"}}', '2026-10-22 15:43:06', '2026-10-22 15:43:13'),
('pre-key-299', '{\"private\":{\"type\":\"Buffer\",\"data\":\"ANjt/hLK6+nqvdyygD71iGhqJk/wCCK24HSQo/ouAWc=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"izyI70KO8jw5Yj9k9T/Asj+i/bh9SY3CJP4yvymm0VI=\"}}', '2026-10-22 15:43:07', '2026-10-22 15:43:14'),
('pre-key-3', '{\"private\":{\"type\":\"Buffer\",\"data\":\"AEajxf7EbGEPOezNVJ3y7dhx7vxubGQhP5JczP9G+0A=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"HsJG1dTOmETAFf3v4evBkBUK32VDcQ+ISAsdTS5sVUU=\"}}', '2026-10-22 11:03:25', '2026-10-22 11:03:26'),
('pre-key-30', '{\"private\":{\"type\":\"Buffer\",\"data\":\"SO/jvOurunbk6Fcr9DZSZ6fKGpIyaMr94OpfGGQ2928=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"LWhiqW+DlFJmzohS5SUpRIZJRetSwNeOZKo2+MOL2Us=\"}}', '2026-10-22 11:03:26', '2026-10-22 11:03:28'),
('pre-key-300', '{\"private\":{\"type\":\"Buffer\",\"data\":\"YLy5RitFY47O/90SRQ2ikZRLfrdcJhFnDyNa/I4+ZWk=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"U5r24ajNxzLjnCX3iXpViYn/fCnYAjXTWWuC8+xrPS0=\"}}', '2026-10-22 15:43:07', '2026-10-22 15:43:31'),
('pre-key-301', '{\"private\":{\"type\":\"Buffer\",\"data\":\"aHce+oggHILpUYvK8sUU3OELg3HBia4xdOsdu9/0YHs=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"XWizHY/EF6QGxmaL4fpxMIq/cEGjs/aQOlTfqbJnHys=\"}}', '2026-10-22 15:43:31', '2026-10-22 15:43:32'),
('pre-key-302', '{\"private\":{\"type\":\"Buffer\",\"data\":\"EIaRlcR3mfV4fcJ97LJN4llkDdydKO2G8GfYIxxQ12Q=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"2yUMpcsippix0pd/CIEI/j77wbYjPqgn7c3MPdUmChU=\"}}', '2026-10-22 15:43:32', '2026-10-22 15:43:32'),
('pre-key-303', '{\"private\":{\"type\":\"Buffer\",\"data\":\"6BaeVMZYv7SqshWjCbYR+uFAwaWdmUZXB2Sl77+lcWI=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"VyNQLXsUikTJt/FwRGA3QBi9U65+eZex3p/lZpEFpQs=\"}}', '2026-10-22 15:43:32', '2026-10-22 15:43:32'),
('pre-key-304', '{\"private\":{\"type\":\"Buffer\",\"data\":\"KL5jrB8QJP5r1f5/Y7cTXC9Upd1Ik3ejYfZYxyKMY2I=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"kE10l8k1JA1/cUYHyFtseUWYF5mCVQkcj+oz0obvEzM=\"}}', '2026-10-22 15:43:54', '2026-10-22 15:44:00'),
('pre-key-305', '{\"private\":{\"type\":\"Buffer\",\"data\":\"UEVCWWjdw85mY2SZ4e0yCCzhkPr3L6Hn5meHAG8CDVg=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"U4CXuYz/m9tE800t+gM8mVojtF+DfYST1xzllGD0els=\"}}', '2026-10-22 15:44:06', '2026-10-22 15:44:12'),
('pre-key-306', '{\"private\":{\"type\":\"Buffer\",\"data\":\"2KuL3lQ7eL8RCZ+CzbKIgsHQg/l7hW0UV5ORIDMcAUU=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"3KqYRrAhy1Z3FPw6+tc4iYo47xPyWfrpniIEkrS/ln4=\"}}', '2026-10-22 15:44:06', '2026-10-22 15:44:25'),
('pre-key-307', '{\"private\":{\"type\":\"Buffer\",\"data\":\"QDT7meR3R32rBFYhXNhhoxoSgR6ILgvYFIJGVVRJRH0=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"duB7ir7T5GCpbWcBn4+sa1smjP3oeOKDvjH2TQT2DDI=\"}}', '2026-10-22 15:44:31', '2026-10-22 15:44:31'),
('pre-key-308', '{\"private\":{\"type\":\"Buffer\",\"data\":\"6H3L+7SIU0plMboIh1JPLNyNlor6atKoivLP6EPJmVo=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"l8cOLXvqDz9Z+8R3tQV/BJNskfvGkk4wH24sTpAX2BU=\"}}', '2026-10-22 15:45:02', '2026-10-22 15:45:02'),
('pre-key-309', '{\"private\":{\"type\":\"Buffer\",\"data\":\"kEoeZhhePonftJapDaeHcbwc8vBa7RKZgK6iXt7gHVY=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"ijghb5wRO7sW5rTkF2nu3C3ODpPIBwFRYOFXwE81z1A=\"}}', '2026-10-22 15:45:08', '2026-10-22 15:45:15'),
('pre-key-310', '{\"private\":{\"type\":\"Buffer\",\"data\":\"uMyv+UF9v8I+oZDIUzG5OTV8enVHKOab3zAa26ezdlU=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"6B2KD7FcUjBxVPlM5W249qCUBZmNIuKxoIaKkrwt0hI=\"}}', '2026-10-22 15:45:14', '2026-10-22 15:45:26'),
('pre-key-311', '{\"private\":{\"type\":\"Buffer\",\"data\":\"kNQrN0hQDAOdoSpvdWyPU+leBxAJPWmij2SJgqVCBWM=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"72fFYH5RN6tfhwa658VexWxoUPbOwpBksUohd4pv5jQ=\"}}', '2026-10-22 15:45:32', '2026-10-22 15:45:38'),
('pre-key-312', '{\"private\":{\"type\":\"Buffer\",\"data\":\"wIgf6YIiqfOguJoLrpTQqVYf/pFrCEXXQcN4g3Gi6nA=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"QFa0HbvaPsy6lD6grAfotJVkK4HAdvvdbyKpgnSS23E=\"}}', '2026-10-22 15:45:32', '2026-10-22 15:45:38'),
('pre-key-313', '{\"private\":{\"type\":\"Buffer\",\"data\":\"cN0ZXBPgbGrdl95v9KnY++1pGkjAvn5GVh5RvknWGkY=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"xIMFdRN8qde6i7zasUYS5+pGNaH7yRQw3Zt6wrRNw2k=\"}}', '2026-10-22 15:45:45', '2026-10-22 15:45:52'),
('pre-key-314', '{\"private\":{\"type\":\"Buffer\",\"data\":\"6JVY/VuObWiXq/ikbDazUuU9uG6pNjAHrCR6asmyenU=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"BCJUJaNsBKSkrpxU4PxA1uxQybA1wiUNSpSDWSITbzc=\"}}', '2026-10-22 15:45:45', '2026-10-22 15:45:52'),
('pre-key-315', '{\"private\":{\"type\":\"Buffer\",\"data\":\"AH22zOFo7IPo4INgQlNX4gfSbLMOYvX+QGWcGSEGa1I=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"+peywgXlZQr+lwu4SDOHYXshL1yNt4DBzbn2nGIFrmA=\"}}', '2026-10-22 15:45:45', '2026-10-22 15:46:16'),
('pre-key-316', '{\"private\":{\"type\":\"Buffer\",\"data\":\"eMyqza5CaEZVdv0fPhrJOAi2VGnI9Q0VzK+w5SE8HFE=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"oaAQMSerC/LHhn1rSYxGx5Y/mzT6xSmgHh6idt/Domc=\"}}', '2026-10-22 15:45:58', '2026-10-22 15:46:29'),
('pre-key-317', '{\"private\":{\"type\":\"Buffer\",\"data\":\"iOdz+3hRyIeMr0yAXCO+XOIw8eXSHdMLcCYKzuhfLl8=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"ewEIb/2kuWU9jz0XrauaB3kokpt0z2LeHEatDKnr0Ho=\"}}', '2026-10-22 15:46:23', '2026-10-22 15:46:30'),
('pre-key-318', '{\"private\":{\"type\":\"Buffer\",\"data\":\"oIW8iSY9mvwAalm7kdXfCgrk7HNP5Dkm3XN1pkpuhVE=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"U45bsCS5obVfe4OMHHWpUctByxskPTRITOmLhdqGnSI=\"}}', '2026-10-22 15:46:40', '2026-10-22 15:46:41'),
('pre-key-319', '{\"private\":{\"type\":\"Buffer\",\"data\":\"cJ4warIWBTzqN0Qd7qSmVxUSS7xFsAtGh2wzvIEK70A=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"skpAuM1u/aBG7G2GMjdiHw9nFV59X3TUPljq59yXQho=\"}}', '2026-10-22 15:46:40', '2026-10-22 15:46:42'),
('pre-key-32', '{\"private\":{\"type\":\"Buffer\",\"data\":\"gJjM6Fo/3qygkKateucmnYkA8Z2iGvFzBFn04e70iUM=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"83jn04fqUkOE/m8IOVg7BznahFLawh8oOGAJSHzMUVU=\"}}', '2026-10-22 11:40:13', '2026-10-22 11:40:13'),
('pre-key-320', '{\"private\":{\"type\":\"Buffer\",\"data\":\"OHFf4OpEjHSvuJlWwO3QZK3KQCBXN3METbPPDggza2w=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"cUkeQOsHrYGjddhxgqPB8o4ZJ3kafLtvUXIZVspcmgI=\"}}', '2026-10-22 15:46:47', '2026-10-22 15:46:54'),
('pre-key-321', '{\"private\":{\"type\":\"Buffer\",\"data\":\"eKj8exKlQzfsQxuBssQ/9nm093pW3Z0fn7K9phndYXQ=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"jMuZmCrGwVzWxHegad9oPLhDvmuoW3RNTCMbD9TVizQ=\"}}', '2026-10-22 15:46:48', '2026-10-22 15:46:55'),
('pre-key-322', '{\"private\":{\"type\":\"Buffer\",\"data\":\"+MzbdH/yjBC71Z1JmRtKRcHX1n3plnSWCai+AQizRU0=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"nGh+hLG3A5sjJcbH2XRlZaXQ0TIcSIYIUAxHmD+6xH0=\"}}', '2026-10-22 15:46:55', '2026-10-22 15:46:55'),
('pre-key-323', '{\"private\":{\"type\":\"Buffer\",\"data\":\"APKW2fZu1yzWcjX4PQ5K4axiMO6Uc+x+QrKyg3OLa1w=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"9WS/gZyQaLdJx+Qj4VOupbUf/kAvmmcvILOalszAPSc=\"}}', '2026-10-22 15:46:56', '2026-10-22 15:46:56'),
('pre-key-324', '{\"private\":{\"type\":\"Buffer\",\"data\":\"gC73V5kOJgyDYh/0Gosq8qZ+G3uEtwH78KWqhPIMjlY=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"ZK4z0TOvCR3BoonEN/Hi4ZxhjZmkzR2XwIfZdx60oCg=\"}}', '2026-10-22 15:47:05', '2026-10-22 15:47:06'),
('pre-key-325', '{\"private\":{\"type\":\"Buffer\",\"data\":\"sPlI7tCqCDXnmO2DXuuflPI8F0679UQ7yq4GXAqaOHY=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"UftEd8GMImOGGt8OWjZSoFSXoM1dWl3FAF1Yxj4ifXU=\"}}', '2026-10-22 15:47:07', '2026-10-22 15:47:07'),
('pre-key-326', '{\"private\":{\"type\":\"Buffer\",\"data\":\"sLDMThIGXP7cuO0gzijNdCTpHLnnAV5J+X7UN7nd31Y=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"LVgEVAlJxmMSloFJ6N9TKVSUI7GrsudMUusn5o4FSyA=\"}}', '2026-10-22 15:47:07', '2026-10-22 15:47:07'),
('pre-key-327', '{\"private\":{\"type\":\"Buffer\",\"data\":\"+GdY2oJgtbr8GYtoxZ2oEIK8egNBi8WYebglIo0iyVs=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"TObXL8jKGr+jwQMOmYN8kCcQ9bQSW6Nx3mCqKIgSpS0=\"}}', '2026-10-22 15:47:12', '2026-10-22 15:47:18'),
('pre-key-328', '{\"private\":{\"type\":\"Buffer\",\"data\":\"IIP1aK+G6SDU/5qYItubAi163JHINkTPljCNadLycmY=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"vE/OeWaDlv0QVjV2Dy0kp9Y5+qyHFRxXMUoKhydX9mg=\"}}', '2026-10-22 15:47:12', '2026-10-22 15:47:19'),
('pre-key-329', '{\"private\":{\"type\":\"Buffer\",\"data\":\"SJqxba9T+PeMQtJIhjYfll3VeRbh9x2WeHZndbOm9V0=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"I76I/GNrBHdYyQWvrSD1mHyYXTOwI1470S8X/hiitik=\"}}', '2026-10-22 15:47:13', '2026-10-22 15:47:19'),
('pre-key-33', '{\"private\":{\"type\":\"Buffer\",\"data\":\"2AJxR9xMnGgzJ/7niA2894Zd2HHEhWMg0H/ttJ1LbH0=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"Ys5c6qa+hPuainSBAO2t/GkjbpGL5ymbokPnok0RPh8=\"}}', '2026-10-22 11:40:30', '2026-10-22 11:40:30'),
('pre-key-330', '{\"private\":{\"type\":\"Buffer\",\"data\":\"uD47jNPiITyvIXeCiM6fcnvw5yxUoEYi0sbYELVbekU=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"g1Rnq86GLhwmneEmdWDsdrbqHlTOYm8O+zdjYWytl0E=\"}}', '2026-10-22 15:47:20', '2026-10-22 15:47:20'),
('pre-key-331', '{\"private\":{\"type\":\"Buffer\",\"data\":\"4CMlfuaRS949aqQt6gAUr9C4DZKdDoi1NChtot5Q1VY=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"bayoQn6Zjqw0NNF7WJ9bN+f3iA6dSwggHjNuJYW/tWY=\"}}', '2026-10-22 15:47:20', '2026-10-22 15:47:20'),
('pre-key-332', '{\"private\":{\"type\":\"Buffer\",\"data\":\"oH3l+qHgl9OvHF4pywRKc492736lE76Eau4tIrgBaXs=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"45GLEwoSarg+495NtNTJdafGlFQR80t/iQp5Le5btlc=\"}}', '2026-10-22 15:47:25', '2026-10-22 15:47:31'),
('pre-key-333', '{\"private\":{\"type\":\"Buffer\",\"data\":\"sEm08tN/CqdWPMCjivjkXcLfvka7tXDjTXPm6tsP3Ew=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"QhVCwDFkHMN7x6In1sE+151/Te6L7H249lAYkWelnV0=\"}}', '2026-10-22 15:47:25', '2026-10-22 15:47:32'),
('pre-key-334', '{\"private\":{\"type\":\"Buffer\",\"data\":\"gPVYgyAQPsHNFJLBPsTCwt4HeJFJlT9sIFgTA/GisEg=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"8IY+zb8aH3ltcm/KlS1V60jncKkpvSE6P+QQ0L1+byo=\"}}', '2026-10-22 15:47:25', '2026-10-22 15:47:32'),
('pre-key-335', '{\"private\":{\"type\":\"Buffer\",\"data\":\"8CNTQE1YtOWc0xOL6ue5eq59tiZOIqXtLcRIgxG3vFU=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"OHoEoYCAzTPd3fx8v19iU5EjMsluNCKD3e/WEPbXRls=\"}}', '2026-10-22 15:47:26', '2026-10-22 15:47:33'),
('pre-key-336', '{\"private\":{\"type\":\"Buffer\",\"data\":\"UEk3gM6VAgqRe6GI060H30L4ZFJ5zFaUsffs25LYo2o=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"r9lsxvSWQc79OgfcSnuUA9V1Gg+GAnwiXcfH77ssAj8=\"}}', '2026-10-22 15:47:33', '2026-10-22 15:47:33'),
('pre-key-337', '{\"private\":{\"type\":\"Buffer\",\"data\":\"yJJb+uOaN+AhWskWdmbYCz1pdM7YhTJj/x9rswSkfnE=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"QuQSHmuVdtoPdUDWs8mRfOXdXXdLQR9adbViNTunrns=\"}}', '2026-10-22 15:47:40', '2026-10-22 15:47:40'),
('pre-key-338', '{\"private\":{\"type\":\"Buffer\",\"data\":\"6D0X71IqcAW5ZWJbh0/aLlt00f1AsMyqLyHewb+X1EU=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"V4QRHY7+CHjPo/mTWhKGXrqLfKkWQUCuYsFRsVat818=\"}}', '2026-10-22 15:47:41', '2026-10-22 15:47:41'),
('pre-key-339', '{\"private\":{\"type\":\"Buffer\",\"data\":\"WO3lZABFbv27sFHz6jgI/ODat8wLf5edBgounRIVSV8=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"pNEaB2krHOsPyDQ15xzqxOELsqAyMinBtIq5gCOJ80k=\"}}', '2026-10-22 15:47:41', '2026-10-22 15:47:41'),
('pre-key-34', '{\"private\":{\"type\":\"Buffer\",\"data\":\"4IEXqOLp6zjM1SuSJVQ3JBIJ9KcI8ohwnEm/y5AcW0k=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"XnRV3qlFIxVdgJbITbmRzOPGgbrPpkPn34cuH0B5VBg=\"}}', '2026-10-22 11:40:59', '2026-10-22 11:40:59'),
('pre-key-340', '{\"private\":{\"type\":\"Buffer\",\"data\":\"IPJshNupSwL09DA2v3JcGbfWslDXClfvNe/NTBaIL2w=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"8fyLGb2kNJej1KwB7wZf3WuLVvMuZECeLneHBMGTugE=\"}}', '2026-10-22 15:47:53', '2026-10-22 15:47:53'),
('pre-key-341', '{\"private\":{\"type\":\"Buffer\",\"data\":\"uGek/bUrrWC3LH1Zl8/RkbTPPF8iBdr6YHY0rqtA/3M=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"GMaMsN1Yt0BMLX3ubo+A7j3nrcdpLPEs1rambBGewzM=\"}}', '2026-10-22 15:47:53', '2026-10-22 15:47:53'),
('pre-key-342', '{\"private\":{\"type\":\"Buffer\",\"data\":\"IKU9usM7AEV+Xy1yQShRHtFYRcqukUIuUXAZDklJD2s=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"G7fcdBqUyr62FluxLP9rVCBgEwxz8YKWFrYWt6kJ10A=\"}}', '2026-10-22 15:48:06', '2026-10-22 15:48:06'),
('pre-key-343', '{\"private\":{\"type\":\"Buffer\",\"data\":\"UCoCXRiAxQPz21jDeQLpM0UP9iY5OV21FLsLX07auXI=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"EC/uVUmvBwlKo7ZDZIvdGzp/x8jEJ9XrNNlLJWiVV1c=\"}}', '2026-10-22 15:48:11', '2026-10-22 15:48:25'),
('pre-key-344', '{\"private\":{\"type\":\"Buffer\",\"data\":\"4Otaiq0Ls7LkRMiCxjrrYYCvdpagMVLZ7QyV511Sb00=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"dfKfoQAvCa3YvaEmfZHrIwJF/2q79PtXaLSzLTPsSFQ=\"}}', '2026-10-22 15:48:24', '2026-10-22 15:48:24'),
('pre-key-345', '{\"private\":{\"type\":\"Buffer\",\"data\":\"AEG+ADN+bcwRu3dBtXe/0rqDfWB2ZXGx/G1dA8tILGQ=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"bHecc3Vwx2/pXnV3cGUUSW9iSBFzbBQq9cFU5jpeKFE=\"}}', '2026-10-22 15:48:31', '2026-10-22 15:48:31'),
('pre-key-346', '{\"private\":{\"type\":\"Buffer\",\"data\":\"EKxmaopaDZL1ixeh+5+SFZJDyRyyAtlXS02YeRA0t0o=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"/pzGGY8EPsjmN4qH6GgrjDIK5cipfGeGIs4wHktTkA4=\"}}', '2026-10-22 15:48:37', '2026-10-22 15:48:43'),
('pre-key-347', '{\"private\":{\"type\":\"Buffer\",\"data\":\"UFx1MXQZ95F0H3lesIubAStrqNz9GXTfdDY5+Mnuj3E=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"IY8HWulfWCt35lzRLQqRvPKd0T4E8m3kd1brDAZ3lCs=\"}}', '2026-10-22 15:48:38', '2026-10-22 15:48:44'),
('pre-key-348', '{\"private\":{\"type\":\"Buffer\",\"data\":\"EHkrm30rZK9ayO5nIZkycXGdXN3xSBmhQTTQxhXb9mA=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"Vx7VK3BEkFkF42tb8zja0ikKkbj4PWb3BJ3DHdUVVkQ=\"}}', '2026-10-22 15:48:49', '2026-10-22 15:48:56'),
('pre-key-349', '{\"private\":{\"type\":\"Buffer\",\"data\":\"gO2GCv0PFDsYBe6r6e1h+UxQ4yESuVBuRkuskD4OC10=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"Dqw489wulbgg1SKFT1hjHvmoIHEwRxE2hoOTMb4jSlY=\"}}', '2026-10-22 15:48:49', '2026-10-22 15:48:56'),
('pre-key-35', '{\"private\":{\"type\":\"Buffer\",\"data\":\"IBlpomL3M9oafM87Nnsj7uJ6BcjjE6nwddYhvSFyoGM=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"/oArCljGTLZdJN4POOafmgdv0ZESCtdF6+5nRXayCBM=\"}}', '2026-10-22 11:41:08', '2026-10-22 11:41:08'),
('pre-key-350', '{\"private\":{\"type\":\"Buffer\",\"data\":\"ME4lBQfPAvCBNSOYnE3zwbDrat6cidsAeDLKWAhL1Ek=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"dmLqPk5PvVw4eEsbw5B+eGLa/hxjM4If+BRl4Om1OSI=\"}}', '2026-10-22 15:48:50', '2026-10-22 15:48:57'),
('pre-key-351', '{\"private\":{\"type\":\"Buffer\",\"data\":\"uDPPb7XOjKrsHG1/mkFjOysYvDmNCFe3ZyiQX9mYOXM=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"Ucih9mvOPZxFYMU8cALEZai4SlJkMeEOHquutJ5MulQ=\"}}', '2026-10-22 15:49:19', '2026-10-22 15:49:24'),
('pre-key-352', '{\"private\":{\"type\":\"Buffer\",\"data\":\"KJ8NHNt8KNuV2lDeNKskZffBCpMxzRlqtamIsZbUsF4=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"FoDX1uIPZrG9bGWFGGKEQw00EhyvR4qVDBQ4Cn47z14=\"}}', '2026-10-22 15:49:31', '2026-10-22 15:49:37'),
('pre-key-353', '{\"private\":{\"type\":\"Buffer\",\"data\":\"MDh70JavE5q7Kl3U6R2cPerNlx66v10Lq6sc5eMu+H0=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"QZxFOc65h3odYxRPjyswozWwIisRooju9JEnClzw3gU=\"}}', '2026-10-22 15:49:31', '2026-10-22 15:49:37'),
('pre-key-354', '{\"private\":{\"type\":\"Buffer\",\"data\":\"ML6SjThJIDTT8Q76kTyEqthNsiVgShegrfwTkyeCMF0=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"sa2DnAtRhUD4D+Ec3nj8a+upu9uWxntiBrLHTYw2z1M=\"}}', '2026-10-22 15:49:42', '2026-10-22 15:49:48'),
('pre-key-355', '{\"private\":{\"type\":\"Buffer\",\"data\":\"OK9Y3Hp61bjuW6gsmQqVREfJmutSL61RxgafKks+rEU=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"xjyiLE+XEq+uEP91oa2tW9hRxwM2CGWefJPLRhU4ukA=\"}}', '2026-10-22 15:49:43', '2026-10-22 15:49:49'),
('pre-key-356', '{\"private\":{\"type\":\"Buffer\",\"data\":\"UL6K/Sz3dYHWbFYQV2CtjbflvKf6zdppRBV4RgECYHY=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"03B0hmfWrFAdZS6JAMQjJKp/Zb+LoJLTwwVNhLspUVE=\"}}', '2026-10-22 15:49:43', '2026-10-22 15:49:49'),
('pre-key-357', '{\"private\":{\"type\":\"Buffer\",\"data\":\"6FPR+PjnKsFPQGp37Lk/VIybDhkTpPyKYNdUHDB1XkU=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"lmWI78knODvNRiha8bkEaC3+waWULev515B8Rg/s2T0=\"}}', '2026-10-22 15:51:17', '2026-10-22 15:51:17'),
('pre-key-358', '{\"private\":{\"type\":\"Buffer\",\"data\":\"yLOMrsiVMWo6HEYG9YPT5i05fTPT0hR1o1RKeoWqZk4=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"JG46ub1GDRJkBHwmL90TRIE2l9h+ed2CxTdyuoSeGTo=\"}}', '2026-10-22 15:51:23', '2026-10-22 15:51:29'),
('pre-key-359', '{\"private\":{\"type\":\"Buffer\",\"data\":\"qM7UxYp9OtpG6tnEgrWhfHm3IhDdwRvlJnbNKGv+8GQ=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"baBir7lDKEIUC4MivZUDAnpbNvRoUmgv/DOSGZtFzU8=\"}}', '2026-10-22 15:51:30', '2026-10-22 15:51:30'),
('pre-key-36', '{\"private\":{\"type\":\"Buffer\",\"data\":\"YEhurLnVdDp1Xrd2fFbKwkuwPQBy4Vn0Fvwzr1ijlV0=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"j6EZxDZ3iPxewArDicM2N3GD1oAp6rRLN4HyLUqcqBE=\"}}', '2026-10-22 11:41:23', '2026-10-22 11:41:23'),
('pre-key-360', '{\"private\":{\"type\":\"Buffer\",\"data\":\"sBRlbpS/oRQKnVDA2scAelSXv1nMng6KmZTjjzZ4V1A=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"ESq9PnMiOt5NJK4Gr2rURqP8t/KbnRv7Z55S4kfodlo=\"}}', '2026-10-22 15:51:35', '2026-10-22 15:51:41'),
('pre-key-361', '{\"private\":{\"type\":\"Buffer\",\"data\":\"kOtzqI0ckR1q9Qcozi0LZoNSaZTJWd3ERRNuTXedy2A=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"DFI0ZuM8MBYdBeLK15XxjavAbeymLiU6l5tZswvBS0A=\"}}', '2026-10-22 15:51:36', '2026-10-22 15:51:42'),
('pre-key-362', '{\"private\":{\"type\":\"Buffer\",\"data\":\"oGkVbms0in0LZ9YyoWJreC4jztq8kJsKvtEbC5krPnA=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"eMJb/1Una1yCJYU1LX59RkvBHkzCZHTPYGFNOTzULGo=\"}}', '2026-10-22 15:51:43', '2026-10-22 15:51:43'),
('pre-key-363', '{\"private\":{\"type\":\"Buffer\",\"data\":\"mBsstVjWg1XOLEjWNUKg+awR4nUwGvJR8zeetNNL6mM=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"jujG3BGlyeubx+tHAn3h7dVRA9k/055Qp0KNw0+mwnA=\"}}', '2026-10-22 15:51:48', '2026-10-22 15:51:54'),
('pre-key-364', '{\"private\":{\"type\":\"Buffer\",\"data\":\"yOtwD3jUXKN31vdZG0dhSmkx6lbuw6ILTNwMu6YjZF8=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"NbwzfwPux2BoA6J1BeBqX9WtNubDj8r4XuftQyNdMyM=\"}}', '2026-10-22 15:51:48', '2026-10-22 15:51:55'),
('pre-key-365', '{\"private\":{\"type\":\"Buffer\",\"data\":\"4JCHuJDGila0BLTp2hFDY4y+P+LhYUgSFVD5/GByGVc=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"XueOzVuVzT9cRfNHDBFCgm1aEHDbaK2GsWHJKBGCVz8=\"}}', '2026-10-22 15:51:49', '2026-10-22 15:52:00'),
('pre-key-366', '{\"private\":{\"type\":\"Buffer\",\"data\":\"4FX1K447jcm3od0QWhiWahT2C8jpK0r3Xytp+NFvm1c=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"OBN6SuKBmEoc+4sTYtD751uW/GAYzLIbESpqrFtDEE8=\"}}', '2026-10-22 15:52:01', '2026-10-22 15:52:01'),
('pre-key-367', '{\"private\":{\"type\":\"Buffer\",\"data\":\"+LdbXbGQFDpnZfcREy4uVXVUy+QoNG3WMGxT+TOvsGE=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"DJV5LEhIg4rmi+ChtyjP+urj0xfISc0JhEzvYVayXhU=\"}}', '2026-10-22 15:52:01', '2026-10-22 15:52:01'),
('pre-key-368', '{\"private\":{\"type\":\"Buffer\",\"data\":\"0CDbG5ZYtEv3tiIRYFCt6zIO0df5PYZmfRZqWGFdSE0=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"9xnFICbjc1AZNwnBoNrA51Rdix7CuJLu+8vXbuA4fho=\"}}', '2026-10-22 15:52:01', '2026-10-22 15:52:01'),
('pre-key-369', '{\"private\":{\"type\":\"Buffer\",\"data\":\"sP9UFmJD+aXsHwFEK3DIg+QxSQN8cD46aL478pS4Mks=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"JMm8QrfR2BR00yZGcZQpzyrl8uZfttSGD/GSNK8bc04=\"}}', '2026-10-22 15:53:56', '2026-10-22 15:53:56'),
('pre-key-37', '{\"private\":{\"type\":\"Buffer\",\"data\":\"2O+f442T2OkExa6jFJ/edAIgYjV9MfMT4kKGFNpxNEA=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"In/rQQfIf1c+4L5WBnW29hhgyMSpAKFAykYO150zHBk=\"}}', '2026-10-22 11:41:27', '2026-10-22 11:41:27'),
('pre-key-372', '{\"private\":{\"type\":\"Buffer\",\"data\":\"4JO76qjjLsgi+gdhhTpA0jAGJgsC3OBKDyoi5v64OWM=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"64JPbBesHUd97kyyLCqfdWFBcsjjlzlltBPYnwfij0g=\"}}', '2026-10-22 16:15:49', '2026-10-22 16:15:49'),
('pre-key-376', '{\"private\":{\"type\":\"Buffer\",\"data\":\"UDC/f1wLssM9cJ64kN/axKmyMiNSqO2SYa9m6vCYtV4=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"QrP3e51PYzJ0UsAbMlPmI11UzoXns//Wj+hlckh3NxY=\"}}', '2026-10-22 17:54:08', '2026-10-22 17:54:08'),
('pre-key-377', '{\"private\":{\"type\":\"Buffer\",\"data\":\"oEc84bQG7y9NHSaEkM2PYGehFIHeDof4WLIKlmfnJVs=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"xgcFxMN5fIGplE646lJVBjJ3LvGvmH7TQo7g2c45Lk4=\"}}', '2026-10-22 19:55:31', '2026-10-22 19:55:31'),
('pre-key-378', '{\"private\":{\"type\":\"Buffer\",\"data\":\"CPBKGL9ZBo1xIfM0Wjr7giDI3P4kEdPQriPaSWcWTF4=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"d4s9M6xn9NWG8xdF0MYhZis4Pv0NDuOjBgc7PtKzp2s=\"}}', '2026-10-22 21:01:18', '2026-10-22 21:01:34'),
('pre-key-379', '{\"private\":{\"type\":\"Buffer\",\"data\":\"wG2xeZL8Cb7NVY4Yj0Klbc4OvqpSKv9y3pmSyWlw224=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"QwyGegotHpxn9QZbRq82kYbiydwmTNeaRjWHGKAd6hs=\"}}', '2026-10-22 21:01:50', '2026-10-22 21:02:05'),
('pre-key-38', '{\"private\":{\"type\":\"Buffer\",\"data\":\"iM2aaYLfH8vFWeylAhuWj/kXI7i2zUft2Xzd7NrOQGY=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"COPJZeK2gZaGLTlnczP18v4T6KEFBlN3DgTTZ/PY7Hw=\"}}', '2026-10-22 11:41:36', '2026-10-22 11:41:36'),
('pre-key-380', '{\"private\":{\"type\":\"Buffer\",\"data\":\"QDg4xnc5s4mZ7ztnm1RaJrbSF2whiZGovpyt0jZRA1U=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"fx4eKdhSFbzQ4bgFIumK2OK7kEIFlpD7YPuP5TKDi1E=\"}}', '2026-10-22 21:02:22', '2026-10-22 21:02:38'),
('pre-key-381', '{\"private\":{\"type\":\"Buffer\",\"data\":\"8MW+a4Qeb6aUVl/RNaJRMbAcpzCetkYxRTsLAxpWXl8=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"muHzaHk8n6j2PMlamCf0pkUj7KhItuOkL75tutWvWCw=\"}}', '2026-10-22 21:02:54', '2026-10-22 21:03:10'),
('pre-key-382', '{\"private\":{\"type\":\"Buffer\",\"data\":\"8LAw3M/Jg1T93qf+jy+joQ4m8GLOKnJT6fIJ9RnUTkI=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"JDl9FWEL8hy1I2RptV9ya/jl+hcn3AFaL1P5TK8dNTM=\"}}', '2026-10-22 22:15:15', '2026-10-22 22:15:15'),
('pre-key-383', '{\"private\":{\"type\":\"Buffer\",\"data\":\"0PzsaR4gzWReQ6mvNdwMijAvDtbsuktFzpnZM4tnplU=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"2uXNsnsxWdr8CGjHi95R9bFS6WjOSBG6WN9CA6QFojs=\"}}', '2026-10-22 22:39:09', '2026-10-22 22:39:09'),
('pre-key-384', '{\"private\":{\"type\":\"Buffer\",\"data\":\"qBD1TOgVEA9SCScnnncznlWRY/pnX+eq2+czT83iN2U=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"jWcOXDrpVyKuz6PVk1iijS3Fo921HPbQ7Z7DKA2k+x8=\"}}', '2026-10-22 23:19:40', '2026-10-22 23:19:40'),
('pre-key-385', '{\"private\":{\"type\":\"Buffer\",\"data\":\"IARTeI0+DN1uLi0G54VaSxgIXe9BifMBV2s80tadjXw=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"KP/HWOE/IP8m8Uysl/JFHQ33HrObgvYxEp+ER6JFkWA=\"}}', '2026-10-22 23:29:16', '2026-10-22 23:29:16'),
('pre-key-386', '{\"private\":{\"type\":\"Buffer\",\"data\":\"mJI6omcdKH3D8lbWfGMlgyGKKsPfU8kpyyYtUz67ilM=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"/EtByEPGe0/FPw3LTzb2DcYDmP29uE1RoNIDJjfJHE8=\"}}', '2026-10-22 23:48:33', '2026-10-22 23:48:33'),
('pre-key-387', '{\"private\":{\"type\":\"Buffer\",\"data\":\"+HQ23Bms7O4wjxLWPc1/MlwdNhXyrMxWotAv7GKsSWg=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"aR8fSKC36783tsjcHkmPILX7wTId+ZhJurgfujb87Q0=\"}}', '2026-10-23 00:38:40', '2026-10-23 00:38:40'),
('pre-key-388', '{\"private\":{\"type\":\"Buffer\",\"data\":\"SB2dQPW4OxcQlBlq9CVDiw+3Zm5LaTFsttimYLVuWkY=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"q0mnAPCwT8a6nFc84jrMGv+vs3OmKh6To4b2oFUDmFo=\"}}', '2026-10-23 00:44:05', '2026-10-23 00:44:05'),
('pre-key-389', '{\"private\":{\"type\":\"Buffer\",\"data\":\"8KsucIJ9FB+zfhf0KLnIg/ulajTmu+nr/DnPNK9Mmmk=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"u9KTOSPueQnfcxbHtgFIKpsfjYmLNw6rC7Qoqg4aAA0=\"}}', '2026-10-23 00:52:40', '2026-10-23 00:52:40');
INSERT INTO `whatsapp_sessions` (`key`, `value`, `created_at`, `updated_at`) VALUES
('pre-key-39', '{\"private\":{\"type\":\"Buffer\",\"data\":\"AE3H0keROFDmfp0Vab3kmGlGZR0OcdegFXch1Q5g0Wc=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"IOWTIBcVTteRgei6xAL/CEejo8RzwuBK0JZ3OrZb0D4=\"}}', '2026-10-22 11:41:42', '2026-10-22 11:41:42'),
('pre-key-390', '{\"private\":{\"type\":\"Buffer\",\"data\":\"MCq7Vpf4T3f3yDsWuyNArfyqEi9hnUArdMNaLKQAjXM=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"SbPN2saQWdCRSUcCc1WHxdIwI13CZLNEW06FOIYnRTE=\"}}', '2026-10-23 01:42:49', '2026-10-23 01:42:49'),
('pre-key-391', '{\"private\":{\"type\":\"Buffer\",\"data\":\"SL9xd3vFLqGfWzX8iGeCU30Vjj7kfwiXCTGV8Ac9+ko=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"6TyWU+Us4m5CnFm7Md437VrluEUEmV2TemMpA8opyF8=\"}}', '2026-10-23 02:32:55', '2026-10-23 02:32:55'),
('pre-key-392', '{\"private\":{\"type\":\"Buffer\",\"data\":\"WIirpdDMWgGAKP/UeNUADUaCYWAdT0zqYWAR5RgQmV8=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"cKaUvdGdOGWvVXzJ2WgJxyN/XK898TK/qHy5QJZvBlg=\"}}', '2026-10-23 03:23:03', '2026-10-23 03:23:03'),
('pre-key-393', '{\"private\":{\"type\":\"Buffer\",\"data\":\"6O2XbQju5IrGPd1DxahU4A6pOFNrMv+3/RugZFGNIVc=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"D8a3/zFiDTHnYNqMs/E/d6Pc1O7zAbRPr2Rjy9jqWSo=\"}}', '2026-10-23 04:13:11', '2026-10-23 04:13:11'),
('pre-key-394', '{\"private\":{\"type\":\"Buffer\",\"data\":\"6H6siW7lJ7Wrrrm90hwX3yjQEPhOpTxoULSANJiNOWQ=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"1m2k7QNv/RGNiH1ejnOrL1ODMXggj+daiB6CHqLjtlI=\"}}', '2026-10-23 04:30:34', '2026-10-23 04:30:34'),
('pre-key-395', '{\"private\":{\"type\":\"Buffer\",\"data\":\"CHoeyvibVxMXO7PBsEndKhOhZC6CzGu7MOHXfIFeFGc=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"jGQzZoaVn0fgyMXxJaAoZU7PMsTmg11J61Y5HQILO0Y=\"}}', '2026-10-23 04:46:22', '2026-10-23 05:17:53'),
('pre-key-396', '{\"private\":{\"type\":\"Buffer\",\"data\":\"6FMJT+WE5b3NVU1RzjCZgNo4Abk/9jZ9KWKiJOr9z1Q=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"iqZTlQ++Yarcp0+SGH70iYiXwAnFhL20NZFDniSzFnY=\"}}', '2026-10-23 07:00:17', '2026-10-23 07:00:17'),
('pre-key-397', '{\"private\":{\"type\":\"Buffer\",\"data\":\"iGSdzH7MpwGCunIOkjb7YjOLgRdIPV6mCsYIbUiKwH4=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"pHN03H6n/SHojLdVPUCTaysOfnLvTsGQk1MnUFgQoTM=\"}}', '2026-10-23 07:35:27', '2026-10-23 07:35:27'),
('pre-key-398', '{\"private\":{\"type\":\"Buffer\",\"data\":\"uJri7x+VLybq8JpEUdGQCDT7A7CA68odcZpAaNe9R3A=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"kPuKBvkL9lSUaFdBkaO7wYgR3lR0Tviw266W8uxVEQA=\"}}', '2026-10-23 07:38:07', '2026-10-23 07:38:07'),
('pre-key-40', '{\"private\":{\"type\":\"Buffer\",\"data\":\"GDFJECErZzdMHf/kKx1hf8YYDBXodnyYPvo2DNTVbXk=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"XtunifZUl4bGbjwisdE3XNKEonlyNoH+liivJgQPATQ=\"}}', '2026-10-22 11:41:42', '2026-10-22 11:41:42'),
('pre-key-402', '{\"private\":{\"type\":\"Buffer\",\"data\":\"ODET9YZAf28kErbp+zhJPXr77IOBjxhQm1110ULaiEA=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"NCEN6PNBLFuSai/uyg343+aR1bKel0mhoRaM+1s8uAk=\"}}', '2026-10-23 08:58:15', '2026-10-23 08:58:15'),
('pre-key-403', '{\"private\":{\"type\":\"Buffer\",\"data\":\"eE62643iYHHR8Vihvq+/VIGM9ftWq7YClwSQoRxt7Gg=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"p5hWy6plW9MIr/MajqWANnuvwTyOV+hBr/E349y2Yxc=\"}}', '2026-10-23 08:58:26', '2026-10-23 08:58:26'),
('pre-key-404', '{\"private\":{\"type\":\"Buffer\",\"data\":\"0CmR4Ri5H/Gjc5xXxezbloVCChjQ8ZwrZketX31iTXY=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"KGoBxjm4WbBGQG4NVBZGJlEYQuB5lmjkUG1oS8e2wio=\"}}', '2026-10-23 09:00:07', '2026-10-23 09:00:07'),
('pre-key-405', '{\"private\":{\"type\":\"Buffer\",\"data\":\"qJe7uL5kCFo/G1A2fzYereutlhkxI+W+X/mN78hkwnQ=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"cLMWcAEFfFqP56EMjQUGlnmpYiXBljROC+aJ1/eGiC8=\"}}', '2026-10-23 09:35:44', '2026-10-23 09:35:44'),
('pre-key-408', '{\"private\":{\"type\":\"Buffer\",\"data\":\"WJyW5SOfWfUllCp494DL1HZaL5p/t8aVsb0sy2u5G2s=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"gSozCwLV4gPZESC8m282GzyW5f0sjyYjrTcAUF+a/2I=\"}}', '2026-10-23 09:52:36', '2026-10-23 09:52:36'),
('pre-key-409', '{\"private\":{\"type\":\"Buffer\",\"data\":\"cD5gvnQpmGjZtihe1QNvNCBxQTAPVyBTlu9mzBM/0XU=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"iY5+F2ppGP/Nut6UMvJKsQgEBYSSgcUCUA9jbldGWwk=\"}}', '2026-10-23 09:54:17', '2026-10-23 09:54:17'),
('pre-key-41', '{\"private\":{\"type\":\"Buffer\",\"data\":\"qPoLgHUzGxLBW1sMonkrOgxpApHXCxaiqDb7XAs9RE8=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"YBe4kyBcx+GRr3ElldLxQDuj0NPCUJjOpxrKNlVueEc=\"}}', '2026-10-22 11:41:50', '2026-10-22 11:41:50'),
('pre-key-410', '{\"private\":{\"type\":\"Buffer\",\"data\":\"CLL8b2Vz6DwoO9Mp5W7de7xgeUkf6bZof+alhQ8g6mg=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"vA4ATmbCts7i5Xoe7CqZoqASPWZNouuqRNY8DU+f3BU=\"}}', '2026-10-23 10:31:58', '2026-10-23 10:31:58'),
('pre-key-411', '{\"private\":{\"type\":\"Buffer\",\"data\":\"OI5WHfRTjcU0qxrbe5fGJUefHQ8hO1KIui8D7QmSc3g=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"wRX3HhwB5qlXVedD1CE2kPnwBjQEpNL3tp+MJO6iu0c=\"}}', '2026-10-23 11:22:01', '2026-10-23 11:22:01'),
('pre-key-412', '{\"private\":{\"type\":\"Buffer\",\"data\":\"gLvNOQu99Uao5kLdO9vUsB0HXluEuIVFpb3JToaxGmc=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"FR1n0t1EhzH5Ex1G/nayyDFYDQTtTyZW6jHhVwqFVRc=\"}}', '2026-10-23 11:22:07', '2026-10-23 11:22:07'),
('pre-key-416', '{\"private\":{\"type\":\"Buffer\",\"data\":\"eB9FDm7iiNY3fHQDvp+pXpOCYJuubzOgrImiqUQzP2Q=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"OnIuKINtsiYGDHsYtDA4A9KPwdwwvFIZyC552IPZChU=\"}}', '2026-10-23 11:22:08', '2026-10-23 11:22:08'),
('pre-key-417', '{\"private\":{\"type\":\"Buffer\",\"data\":\"+BQWR2TERFHhV2ygayN+j+yc32YinV4WonyV3ZnYsXY=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"rFmq3uDzPK4gquiFCWQ/b8AOWfyX1uZ57bQniSXucQg=\"}}', '2026-10-23 12:12:13', '2026-10-23 12:12:13'),
('pre-key-418', '{\"private\":{\"type\":\"Buffer\",\"data\":\"gIxltzpuIJynUo2nJ6lLZqaomxA57wD72hwi4s+3JGo=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"ymCnV9GcrMoE7cfcrku/svKVyEr6o9pKLMl7GNLbiw0=\"}}', '2026-10-23 13:02:20', '2026-10-23 13:02:20'),
('pre-key-419', '{\"private\":{\"type\":\"Buffer\",\"data\":\"SDxGo3paB5uBIbJ3/DuujcVMNSYdcCzBkXb7L0iMtGk=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"ZVvxDsjrpBo1NXsvfhmn1vy8knxQntg/NCpqLZgXNkA=\"}}', '2026-10-23 13:52:27', '2026-10-23 13:52:27'),
('pre-key-42', '{\"private\":{\"type\":\"Buffer\",\"data\":\"YGUCAUHmZUJZj5eH/mavTM5MU2DhIky1TzjTrfJIZlg=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"fSh1fVAgIGNQfa0CLPrqs/4pHm7enFg6+KDb3tmPMk8=\"}}', '2026-10-22 11:41:50', '2026-10-22 11:41:50'),
('pre-key-420', '{\"private\":{\"type\":\"Buffer\",\"data\":\"CDpRim7jP5tpgokv22CRXVTITleuHp/tJZgF3slvw3k=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"4iucNkmgqgduqKxnKr93BYc0yWNvNqfAFNfsJ1WaoGI=\"}}', '2026-10-23 14:42:34', '2026-10-23 14:42:34'),
('pre-key-421', '{\"private\":{\"type\":\"Buffer\",\"data\":\"aKo0Nan0BZnFOhUmdRe3jRORuysnhok5Eo/IZsQ3CVU=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"eCQU95NJqXBw97pLoUjWLywU7zigjC+jp4h5k95S1RE=\"}}', '2026-10-23 15:32:41', '2026-10-23 15:32:41'),
('pre-key-422', '{\"private\":{\"type\":\"Buffer\",\"data\":\"qPUwpI0FLdZKUVQTrhZvRGFhrMw+DgSJeqvbDlfL/EA=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"azszLmhFjeX0PLfg7C0Qh+2S6Yk5YPBHiCMQO6eCu10=\"}}', '2026-10-23 16:22:49', '2026-10-23 16:22:49'),
('pre-key-423', '{\"private\":{\"type\":\"Buffer\",\"data\":\"iOyzMRgkEprpx0NAdzegQw0Pxs4gV8dhrMe+CM8NT2E=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"7HHakgihaolbaieWnUZW/GVh47z8KFxnChiOF8Qf8kg=\"}}', '2026-10-23 17:12:56', '2026-10-23 17:12:56'),
('pre-key-424', '{\"private\":{\"type\":\"Buffer\",\"data\":\"wBOTphMDSZXRa6CB0o8swHS6kNgGVPacwQcZjCWJE08=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"QGQNLk6UUn01zff1S4tuNBP2LDDUjFWj9B14Hz7f9h0=\"}}', '2026-10-23 18:03:04', '2026-10-23 18:03:04'),
('pre-key-425', '{\"private\":{\"type\":\"Buffer\",\"data\":\"OO18nG1SPLiNMH4gnV6dnIulVl7ByBZ6Yg6RLRmW6FY=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"vvCqPOUCKBZUVv1Izo59PdgtF0Mi+RuKz7LY+QY3Fks=\"}}', '2026-10-23 18:41:03', '2026-10-23 18:41:03'),
('pre-key-426', '{\"private\":{\"type\":\"Buffer\",\"data\":\"EFdxBjJ6vU0jUZhwUOeNznJCgl5/NzEvw81BWRCdl1c=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"cmKaz1LyWFYkSVSArMe1UBGgFDziTAr9MKu8BkuNCig=\"}}', '2026-10-23 19:17:00', '2026-10-23 19:17:00'),
('pre-key-427', '{\"private\":{\"type\":\"Buffer\",\"data\":\"YMUFmVhbjEmRSjLXA/Z9RcXldQOeMQrYqxcf9B/9x30=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"2jQH11KdocKU9E2IZcPuKBX79wrtAxUIzHsi2NsqAh4=\"}}', '2026-10-23 20:03:57', '2026-10-23 20:03:57'),
('pre-key-43', '{\"private\":{\"type\":\"Buffer\",\"data\":\"uIw8DvlxLR4hqIJpFa/ulZDmM5TF36duODu/+DdL7ks=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"5u7sPXoqOytKefGnH8p8rOIxJ3SDh1C/P7EhZ47Rk1c=\"}}', '2026-10-22 11:41:55', '2026-10-22 11:41:55'),
('pre-key-44', '{\"private\":{\"type\":\"Buffer\",\"data\":\"0EbX5SuhhfQ0iXDYQPcU2qWNrQ8nSR0Ak39x9wA2NVc=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"+VQaEGVODRIYcmPcBraogAQZHiPnkxH/svAR+jpNzHQ=\"}}', '2026-10-22 11:41:55', '2026-10-22 11:41:55'),
('pre-key-45', '{\"private\":{\"type\":\"Buffer\",\"data\":\"GKGgPfVrA8uYG3qrcYkIvv0GxTnBfOQigHnv4blhcF0=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"OiL+8mhysJsVi49YL0x6ipXm9MLXRftMX7kIaqM+cjI=\"}}', '2026-10-22 11:42:03', '2026-10-22 11:42:03'),
('pre-key-46', '{\"private\":{\"type\":\"Buffer\",\"data\":\"oAtKvhdnWaQp/4AIHCAL2NEVXqPdJNasXa3jsKNnelE=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"hGKqa92sU/r4hMY81ER0MQ4LNqbRkTMhwBDoXqMAFWk=\"}}', '2026-10-22 11:42:03', '2026-10-22 11:42:03'),
('pre-key-47', '{\"private\":{\"type\":\"Buffer\",\"data\":\"6A7Zo4+LYe7lss6pQIokN+XVF07KWrPrYOaq7Ej8SlI=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"RfrdpwpMY/rBA1OBrCZfzRv2HkT42ddqkEFgXtLYIDw=\"}}', '2026-10-22 11:42:08', '2026-10-22 11:42:08'),
('pre-key-48', '{\"private\":{\"type\":\"Buffer\",\"data\":\"MCptZTBP3TtD7eDQrq/xRQ0kc6HYN/nEtx7RWNz8iF4=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"JMSa1igmaVa7/diYg3SwszLZEqxdz2qfLqMqpBn333o=\"}}', '2026-10-22 11:42:08', '2026-10-22 11:42:08'),
('pre-key-49', '{\"private\":{\"type\":\"Buffer\",\"data\":\"MIjheHal2mFkek/B8Sk5hptdpLuK5sPlobo/tUjYm3g=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"/h6vUaNXSYYC77yqfJ1FlOfyQfqtvrpF2LKZxZZ4Bw0=\"}}', '2026-10-22 11:42:42', '2026-10-22 11:42:42'),
('pre-key-5', '{\"private\":{\"type\":\"Buffer\",\"data\":\"8BBa+aMCjIZgCmwbNEGDbVdjH19wFeJyWl5MBiYdCWI=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"wqtM92lfOq/z2tkQM+XHysCF2ByS06oVbb4EdnFc4Sw=\"}}', '2026-10-22 11:03:25', '2026-10-22 11:03:27'),
('pre-key-50', '{\"private\":{\"type\":\"Buffer\",\"data\":\"OH7wM0fTMZTR5JdCgvrrVgP+KWCNEbeBcozwd+FeRHA=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"gtOAaoBO/WgRyRx/8MyXRNqZmjz1aidCSi/RZyE6TRE=\"}}', '2026-10-22 11:42:51', '2026-10-22 11:42:51'),
('pre-key-51', '{\"private\":{\"type\":\"Buffer\",\"data\":\"qOexMKHBTCrJSpiU19m7Y7RbQnrKXbkvCWbxQAUF41Q=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"pMLwZ9wwTJP1z2ttXo3U7kz7n78SavEfJfvdeWGOX0M=\"}}', '2026-10-22 11:42:55', '2026-10-22 11:42:55'),
('pre-key-52', '{\"private\":{\"type\":\"Buffer\",\"data\":\"ePvb4WKRvrweDE4XnoqIAyTaknSScJmL84EZarWl5n8=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"I9sbfonxEFe97nSW8Orb5Rh3NgHWmB4wX3/7+9ESdCs=\"}}', '2026-10-22 11:42:56', '2026-10-22 11:42:56'),
('pre-key-53', '{\"private\":{\"type\":\"Buffer\",\"data\":\"cH1Q4t9UcRt0nKg6QZRbQq4wqHYrbegb+fMeJHGZwV0=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"EChuE20ax4UOdxiEjgWr90wm++3BRfgO14R0kPEY9TU=\"}}', '2026-10-22 11:43:05', '2026-10-22 11:43:05'),
('pre-key-54', '{\"private\":{\"type\":\"Buffer\",\"data\":\"ODAWR3m6n5Gy2hDV2HqbYBEpATG4eDUPUP8iw5CWiXg=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"YPC4SCJHH7VkK+tWV0WHYvgoeYOSSjyvCC+/4MdG1m0=\"}}', '2026-10-22 11:43:05', '2026-10-22 11:43:05'),
('pre-key-55', '{\"private\":{\"type\":\"Buffer\",\"data\":\"wPeb1tmxRaTLlDPzLZqd+045w8uDZwA+KTTud/8t20Y=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"Sk0oaiwMjsZSEgFYk4f2wTOePXGorsmDTf3QNVV5hAQ=\"}}', '2026-10-22 11:43:40', '2026-10-22 11:43:40'),
('pre-key-56', '{\"private\":{\"type\":\"Buffer\",\"data\":\"WLqBn640HTd8yNG82GwyZUZFCNooG7VRuB/w1Bje3mM=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"/kkW9hofk2O1lGRaDfWSvcpPoAwi7qjBYs88Oo07SnA=\"}}', '2026-10-22 11:43:49', '2026-10-22 11:43:49'),
('pre-key-57', '{\"private\":{\"type\":\"Buffer\",\"data\":\"iEVw+Tpt/RQvFDZ3Miu16hT6LEVFntcSPAwyKPdolns=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"9oHeRqslfC7dCCU7jM/zrmoc7msceWFfLlm1EWEmRFA=\"}}', '2026-10-22 11:43:54', '2026-10-22 11:43:54'),
('pre-key-58', '{\"private\":{\"type\":\"Buffer\",\"data\":\"oDGSKR0bleAGjEcjj0WvBJXUyug9fYgmMrzoBPN9iEA=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"FWLhYjS/9nUFHBewSARk/yiUPuSF3hAIBcVdz9gr5zA=\"}}', '2026-10-22 11:44:00', '2026-10-22 11:44:02'),
('pre-key-59', '{\"private\":{\"type\":\"Buffer\",\"data\":\"0LZNAAQOmsFjuWVKDt2N5V90e73F8swzAZGSw72Ra1Y=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"2NMmllVU8cjA7zNRwlBwsrkqbdMbwLzd9WRAW1Wfwnc=\"}}', '2026-10-22 11:44:07', '2026-10-22 11:44:07'),
('pre-key-60', '{\"private\":{\"type\":\"Buffer\",\"data\":\"iOkHIOc0EdUvpdmSFDrlWSI+L99HgUCdXJPNFZS1J0w=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"Yumm2hALfEXT9wValG+HRfRAupvgZvFXd5FG2i/9808=\"}}', '2026-10-22 11:44:07', '2026-10-22 11:44:07'),
('pre-key-61', '{\"private\":{\"type\":\"Buffer\",\"data\":\"+P0/2+Mj1tEZSaJZRl3k6LpcfpYltEwFb2bKcqYvqnA=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"T+R+Eh1ipGaUiOfraM7ml+Nz04nWcUcFkA9RfylH5hQ=\"}}', '2026-10-22 11:44:07', '2026-10-22 11:44:07'),
('pre-key-62', '{\"private\":{\"type\":\"Buffer\",\"data\":\"qNSuy7PkVvSQbPy6vciWn/ViG3FgYmY2DieKIRmjN0g=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"NyXTgDdElrNAt9ie1L6ThAae5YOaokocU+JzOlJY+gw=\"}}', '2026-10-22 11:44:16', '2026-10-22 11:44:16'),
('pre-key-63', '{\"private\":{\"type\":\"Buffer\",\"data\":\"aNvApujFFIzdjUP5DGE+CCkTlHaMLLMKc3dm543KW1M=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"1gSdttTmt9PEVphetCpIlePDrQmNkMeButlKh4zrciE=\"}}', '2026-10-22 11:44:16', '2026-10-22 11:44:16'),
('pre-key-64', '{\"private\":{\"type\":\"Buffer\",\"data\":\"aCY0xxW4W9DRecznr7iLzzroNlsxJoEtsdEX29uHXUE=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"7RARV6iLTqLd3nGvZpZP4dwD3eyvclKazsyxfA5Pql0=\"}}', '2026-10-22 11:44:31', '2026-10-22 11:44:31'),
('pre-key-65', '{\"private\":{\"type\":\"Buffer\",\"data\":\"UJHH4s96tJfoAmEzUMzQrSxBpuqEhvpmLrnlHP6ikV4=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"pjmje1SY4QePWZZE3FhI5VPTszX69Onvo/weev3nPAU=\"}}', '2026-10-22 11:44:36', '2026-10-22 11:44:36'),
('pre-key-66', '{\"private\":{\"type\":\"Buffer\",\"data\":\"AB7pADmESWCzTrMFShrkUX4sUErbnf5MMoUriZrIzUw=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"5tKIR+r8rf93HFCscHzg9Ne/IGD/XVz26ULBWls5dxs=\"}}', '2026-10-22 11:44:45', '2026-10-22 11:44:45'),
('pre-key-67', '{\"private\":{\"type\":\"Buffer\",\"data\":\"wPtAmDrFPYh2Vewo1o+ygNSXN0dvs4Qt8IAixQ2vY3k=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"E7IIGjU8cIaP0f3HmTjL959tTrqgsKRZsuAd7sRwS1k=\"}}', '2026-10-22 11:44:49', '2026-10-22 11:44:49'),
('pre-key-68', '{\"private\":{\"type\":\"Buffer\",\"data\":\"gLXcOl2gQfvqWa8k5sI4bMXmV9/21VNQ7WbsNGGwIEg=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"vxaVKrwLKO9F6DhEU96QgeRoE9+XFs89TBp7bY2SciU=\"}}', '2026-10-22 11:44:49', '2026-10-22 11:44:49'),
('pre-key-69', '{\"private\":{\"type\":\"Buffer\",\"data\":\"IDyq8fNYJHiKIdppESv+9RdhgIseBE4jZwIJ72XMx34=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"18bacr/4KjW4NYXBGdUtCrK8cR5jOBxI0pncgEzY72g=\"}}', '2026-10-22 11:44:57', '2026-10-22 11:44:57'),
('pre-key-7', '{\"private\":{\"type\":\"Buffer\",\"data\":\"CDmbonL5EwXVeXJkSUFl3ju82GnOuT2xlo2jeKonpV0=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"T1H29E+7v0qmhn2cyr/XBXp/LiiYVloAkcT7jlvjRhE=\"}}', '2026-10-22 11:03:25', '2026-10-22 11:03:27'),
('pre-key-70', '{\"private\":{\"type\":\"Buffer\",\"data\":\"qFd127Tu1WBm51sBJdkfilzy/T9tEHJ4f6zlJzWLo00=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"F8V+AZD3wWCya4cC1SXlBYTqlz2pngX3mLfrh8iJfQ4=\"}}', '2026-10-22 11:44:57', '2026-10-22 11:44:57'),
('pre-key-71', '{\"private\":{\"type\":\"Buffer\",\"data\":\"QOvmWe/TyqY3C+6YosIvWsBgbyCeKgJBjDLhq2W1KHk=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"bmC9qPrfaopkNdV7zU7rYoK3nIsuEAFj18AMvQCSaTw=\"}}', '2026-10-22 11:45:03', '2026-10-22 11:45:03'),
('pre-key-72', '{\"private\":{\"type\":\"Buffer\",\"data\":\"8H6civVQrI75A01MFwvlQZPxJz+WB5CRm9E1Pwzs90g=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"8HSLG4FFBC9bErDUmQJV3lK+sBvHZLRd2p5rqY31SXk=\"}}', '2026-10-22 11:45:03', '2026-10-22 11:45:03'),
('pre-key-73', '{\"private\":{\"type\":\"Buffer\",\"data\":\"mAkB8J6zQT96RhbHeXf3dWm8cRcb1rCUlMCSOWzldVM=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"7r8OSx6tJ4gLnSdmI0R4Gu8vU1FF4S0157jDS9Ixxns=\"}}', '2026-10-22 11:45:03', '2026-10-22 11:45:03'),
('pre-key-74', '{\"private\":{\"type\":\"Buffer\",\"data\":\"gGTuhevcMnJUxgu79nkjx7Py1nLO0jpXzFwbMRabvmw=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"DfDziGkBzHys3ptNb3kJ9tMFNHRSF/FFfQi8cwE/JmY=\"}}', '2026-10-22 11:45:16', '2026-10-22 11:45:16'),
('pre-key-75', '{\"private\":{\"type\":\"Buffer\",\"data\":\"WPvVF6XDxOfxTbNr5WG+30VLKX37eko9+CS50Y/Dkl0=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"0AkHR+dJGc8/FgvBhUbjz+xe4f3zTwC8tKCPHnDuzE8=\"}}', '2026-10-22 11:45:17', '2026-10-22 11:45:17'),
('pre-key-76', '{\"private\":{\"type\":\"Buffer\",\"data\":\"wGmp2zLjmREh1XnhKYfgcuXxyGWZqq56veuf+b3TG3Y=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"NP21blY8/xZkJ5UItWN0DOPX991knXk2OwdC+7NOPws=\"}}', '2026-10-22 11:45:17', '2026-10-22 11:45:17'),
('pre-key-77', '{\"private\":{\"type\":\"Buffer\",\"data\":\"SLk3fpZ2U7Maww2cAi4rlKHEqz2HqCmMxj6QI9dJs3I=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"NdqieJE9rHva6rh+8t0I2aAyRWeWWNqe4iCQUz0AhXo=\"}}', '2026-10-22 11:45:30', '2026-10-22 11:45:30'),
('pre-key-78', '{\"private\":{\"type\":\"Buffer\",\"data\":\"GE7ecqOs7EqcVKIGjqoDDrNNmBmy1p64QTB1TfjnPls=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"Jgi6DBteMuv1L/+7mTC/3XOutcgBX1BwEJHa4pHMrg0=\"}}', '2026-10-22 11:45:30', '2026-10-22 11:45:30'),
('pre-key-79', '{\"private\":{\"type\":\"Buffer\",\"data\":\"WGrA8OSCDAosxFui8PF4y9fcnXTIZ331Xo3r8eUZNGM=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"JhEJ99CvUIWjZxux72FFCK2H0nmgEhb3VHh1VtsnrC8=\"}}', '2026-10-22 11:45:38', '2026-10-22 11:45:38'),
('pre-key-8', '{\"private\":{\"type\":\"Buffer\",\"data\":\"oKivh2V/0+56W6gd2OKwF/IvxxZW5WAQdu1VinIujFc=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"GAe3G8uDhDseM/YTVApuwzel+tCfsWn2t6lf+BNVz0U=\"}}', '2026-10-22 11:03:25', '2026-10-22 11:03:27'),
('pre-key-80', '{\"private\":{\"type\":\"Buffer\",\"data\":\"0BF/1Eg/O5BP5WYgBw5sS+dbr/uTv8eX2Tx0+DSDxVI=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"yWKMQTQ6Ze2j6cCIkpo16Hx/UUPGZMgBP7xfzGIlok8=\"}}', '2026-10-22 11:45:53', '2026-10-22 11:45:53'),
('pre-key-81', '{\"private\":{\"type\":\"Buffer\",\"data\":\"OPWjHeXSApK//xJeN33gXniNcm9lOhmQ2GHE7Co6vlA=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"9w7MqxnIdsaC9zX5EqBCr9ZE0wRwdTZho4Ewpoyc6z8=\"}}', '2026-10-22 11:45:58', '2026-10-22 11:45:58'),
('pre-key-82', '{\"private\":{\"type\":\"Buffer\",\"data\":\"qL5CuOI73ZaORpsQMjBtjzU+lZFXCqhdhi2YxMsp118=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"fIJYOK1TwdczHDJthk/e11A0qK3LqooSrmQAEui9B3M=\"}}', '2026-10-22 11:46:07', '2026-10-22 11:46:07'),
('pre-key-83', '{\"private\":{\"type\":\"Buffer\",\"data\":\"YPPo9FyTQGXlidTQvwjkQ7K7bEFnjhcO+W6z2SqotE8=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"Hvd0hV7Tmnh/LsBFlICk7BjkVzaY+iHB5YzERO8g5zc=\"}}', '2026-10-22 11:46:12', '2026-10-22 11:46:12'),
('pre-key-84', '{\"private\":{\"type\":\"Buffer\",\"data\":\"aHhYA0LEVkrPix4Xya2sVzt/d5JMiSFBuA6xbULcPGk=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"OQerLR1yf4tD8d1fWumwjY+X7siRgnGKSbSYVlsWSiI=\"}}', '2026-10-22 11:46:12', '2026-10-22 11:46:12'),
('pre-key-85', '{\"private\":{\"type\":\"Buffer\",\"data\":\"YFj5MK65R+oFN6lMOJfOHBMYKpCB7E17XCOaO0LstXU=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"BYdh5PPMGIO1fZX4uthWpfoTnFNe85UjBQvNALtZJXw=\"}}', '2026-10-22 11:46:20', '2026-10-22 11:46:20'),
('pre-key-86', '{\"private\":{\"type\":\"Buffer\",\"data\":\"0A6X+38VfqKxI34aQ8lRnhxXnB7oB5Vw0P5aogAlXmE=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"Ow5QKWCemA+CwUKU2o6RxjfCMA58KEltbWdbNf6WmVY=\"}}', '2026-10-22 11:46:21', '2026-10-22 11:46:21'),
('pre-key-87', '{\"private\":{\"type\":\"Buffer\",\"data\":\"0L9u61LxYoj9pscuxNqitXl3AmfroXBzekNE0gJmMFI=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"p1tY7jbRj+EAOmWVAxdFGM2+YOuNOueUlfSNTxtbaho=\"}}', '2026-10-22 11:46:25', '2026-10-22 11:46:25'),
('pre-key-88', '{\"private\":{\"type\":\"Buffer\",\"data\":\"aPpAofpmVSKfFW6/W/XPqLr5IMGgviX0FPwFCvKLhF0=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"K5pPDMEXKMjprYiW2XC1L0ZNU8Cp8uTNTLkeBNxhiic=\"}}', '2026-10-22 11:46:26', '2026-10-22 11:46:26'),
('pre-key-89', '{\"private\":{\"type\":\"Buffer\",\"data\":\"YHuWZnvac46V+9iivcjLk7dCNbck0AafxNGbwT16yHI=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"hMbbOkwT3Kvsbp5v7Hfb2fPN6CPEZZdMcwIImkq8vVY=\"}}', '2026-10-22 11:46:26', '2026-10-22 11:46:26'),
('pre-key-9', '{\"private\":{\"type\":\"Buffer\",\"data\":\"cHBn9WxFA/F+rrQSyfFvmyGG13ApIH2Raq/MzrOeKVw=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"F1yb/TbybwXaWfQADi01glVBZpW4UPlf0XdSIBQbFUo=\"}}', '2026-10-22 11:03:25', '2026-10-22 11:03:27'),
('pre-key-90', '{\"private\":{\"type\":\"Buffer\",\"data\":\"6J5VyMK6C4uok1qpjoVspXNj9Cm72N/TwEL+n5tmcFM=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"E7bG90LPIQWtLJ0C9+s5UyDdI2eweDFyJLripYZpC3E=\"}}', '2026-10-22 11:46:34', '2026-10-22 11:46:34'),
('pre-key-91', '{\"private\":{\"type\":\"Buffer\",\"data\":\"GJaPk+BbN3u4gW+TbDn2joGcEhHOFVBhgtq10eXeFU0=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"qg9l1TiqRrqGecf/bjUbXp3IXVWfOx9DdrQksyujjyY=\"}}', '2026-10-22 11:46:38', '2026-10-22 11:46:38'),
('pre-key-92', '{\"private\":{\"type\":\"Buffer\",\"data\":\"GPlzwozraGaAKocwyZioHvAShXpQX9t7RhMs8GnEzU4=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"0VlLTV3yWr4POpyloVHRBCcxNwM0Ej9JkcPlOPH6wD0=\"}}', '2026-10-22 11:46:39', '2026-10-22 11:46:39'),
('pre-key-93', '{\"private\":{\"type\":\"Buffer\",\"data\":\"GA+ba1vdjSEpUA44pbdAo8VYC+GNsKk9RVzJQ2CmnV0=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"FvKDg81hOnv7hhIVu++ryVbWSnIAw7ZY86xgwpzKg18=\"}}', '2026-10-22 11:46:39', '2026-10-22 11:46:39'),
('pre-key-94', '{\"private\":{\"type\":\"Buffer\",\"data\":\"IKC7SG74ctqUqG4dfCx1sqpeiRoZebPL+PN/XVU4eWs=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"tF0DKWlQBdpXloVcakoxxAQptKwZ6DvxlBNLiH/J11g=\"}}', '2026-10-22 11:46:52', '2026-10-22 11:46:52'),
('pre-key-95', '{\"private\":{\"type\":\"Buffer\",\"data\":\"8DmkXalEU6PNfkrKo8cZ3U7lpASn0MgiOxLpBqAqtW8=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"peBu3FGzjSVD4QosRzFH4KSnNXnw3OWbNs4G0h94nB4=\"}}', '2026-10-22 11:46:53', '2026-10-22 11:46:53'),
('pre-key-96', '{\"private\":{\"type\":\"Buffer\",\"data\":\"AKj83KBaX69XqYy+WmgfuPVefXYGZ+qPS5X8M5J9bmw=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"rhdWXLcXtDfeNMcGbFusLxBcovyr5qJx40wWIz3HDzM=\"}}', '2026-10-22 11:47:06', '2026-10-22 11:47:16'),
('pre-key-97', '{\"private\":{\"type\":\"Buffer\",\"data\":\"qBNDxCQMzOFmsOBYczFaDTXxkzA2K2vXof3g4brpQ3k=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"KUoVd39XTWuMmt+8BpU3SkgyQAMD1hGL8DEs/WairUY=\"}}', '2026-10-22 11:47:07', '2026-10-22 11:47:16'),
('pre-key-98', '{\"private\":{\"type\":\"Buffer\",\"data\":\"6Hm3QzDb3Dv9coye3G+6wz5zV5sa94PJSGpJX/toC1U=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"0nZ3Qum+PZ/69qWq+32G7EqXpbw3tZ2U+9KEAzgXx0o=\"}}', '2026-10-22 11:47:21', '2026-10-22 11:47:21'),
('pre-key-99', '{\"private\":{\"type\":\"Buffer\",\"data\":\"cLbUA3YrpTPKcJs4fhsVxnLe7m2TOvx+VrPx5yaKMUg=\"},\"public\":{\"type\":\"Buffer\",\"data\":\"kLIRbw4wprMf87oSQ6iG/P1tkCldownApFMFfbfCoE8=\"}}', '2026-10-22 11:47:29', '2026-10-22 11:47:29'),
('sender-key-status@broadcast::79418290610347::0', '{\"type\":\"Buffer\",\"data\":\"W3sic2VuZGVyS2V5SWQiOjgzMjY4Njc2Niwic2VuZGVyQ2hhaW5LZXkiOnsiaXRlcmF0aW9uIjoxLCJzZWVkIjp7InR5cGUiOiJCdWZmZXIiLCJkYXRhIjpbMCwxMDIsNDEsNjgsMjAyLDE2NiwxMzEsMTgyLDIxMCwyMjQsMTM1LDAsMTUsMjQ0LDIxMSwxOTcsMjA1LDI0MywyNDAsMjQ5LDEwNSwyMjUsMjIyLDI0NiwyMzcsMTAwLDE5NCwxODMsMjAzLDgxLDQ4LDExNV19fSwic2VuZGVyU2lnbmluZ0tleSI6eyJwdWJsaWMiOnsidHlwZSI6IkJ1ZmZlciIsImRhdGEiOls1LDIzNCwyMzAsMTc2LDE5NSwxMTAsODgsMzEsMTk5LDI2LDE3LDIxOCwyOSw5OCwxNDcsOCwxNSwxMzAsNzAsMTEyLDIzLDE1Nyw4NSwxNjIsNTQsMTAsNTksMjQ2LDI1MywxNjYsNTgsMjM1LDU4XX19LCJzZW5kZXJNZXNzYWdlS2V5cyI6W119XQ==\"}', '2026-10-23 07:00:43', '2026-10-23 07:00:43'),
('session-249070069219550.0', '{\"_sessions\":{\"BdTJMqKHsnQ2/xYq430AHcbE0Yv5+Nld6uySwmM+JZBo\":{\"registrationId\":1295218465,\"currentRatchet\":{\"ephemeralKeyPair\":{\"pubKey\":\"Ba4mlirIS6YI8sWo2IgWPGHt//Iqra45mDN+s3z/nM8n\",\"privKey\":\"6GLtVotzIpPeQR+FWiHXMhqnDrdxh05CqKto5frPAE4=\"},\"lastRemoteEphemeralKey\":\"BcIV5PLaUjNbG8XolpeWdlP1DTn/pPtkPTur1uI1WoA5\",\"previousCounter\":0,\"rootKey\":\"jTa3qClm9N6mcOa6cb4WNhDvV2eHB3Or08g4JO2TdCU=\"},\"indexInfo\":{\"baseKey\":\"BdTJMqKHsnQ2/xYq430AHcbE0Yv5+Nld6uySwmM+JZBo\",\"baseKeyType\":1,\"closed\":-1,\"used\":1792741574254,\"created\":1792741574254,\"remoteIdentityKey\":\"BeWhNI/5zM5CtHXYrUTynpdKeJk2l9SHA/+oFZqbSw4n\"},\"_chains\":{\"Ba4mlirIS6YI8sWo2IgWPGHt//Iqra45mDN+s3z/nM8n\":{\"chainKey\":{\"counter\":5,\"key\":\"xlgdJcqekJWBbXrE4tjGQ+rPmngH1crCTilLER7h3L0=\"},\"chainType\":1,\"messageKeys\":{}}},\"pendingPreKey\":{\"signedKeyId\":228064,\"baseKey\":\"BdTJMqKHsnQ2/xYq430AHcbE0Yv5+Nld6uySwmM+JZBo\",\"preKeyId\":998513}}},\"version\":\"v1\"}', '2026-10-23 07:46:14', '2026-10-23 11:29:21'),
('session-44925643173935.0', '{\"_sessions\":{\"BaURfkyyCfApS96XI4cBVwGVwuDbJ9eY0CerJ3SJpRQI\":{\"registrationId\":514790048,\"currentRatchet\":{\"ephemeralKeyPair\":{\"pubKey\":\"Bdj95LAfgeNfcNy6r5S5DItRU1uiODtnNMzBeKpFnRdn\",\"privKey\":\"6L0H4KmfWKEOaIMcj0dCOXzhRcnGRg1O0IwjrNAK/0A=\"},\"lastRemoteEphemeralKey\":\"BZmaI9VwjnDaiWGSJCfUJ40K1HKOxKbzBjVaKFhu6URY\",\"previousCounter\":0,\"rootKey\":\"AsAIKNP1yVJqf0+yKjE0g11frZuUbAuz+De9DuYo/HI=\"},\"indexInfo\":{\"baseKey\":\"BaURfkyyCfApS96XI4cBVwGVwuDbJ9eY0CerJ3SJpRQI\",\"baseKeyType\":2,\"closed\":1792748727984,\"used\":1792748722437,\"created\":1792748722437,\"remoteIdentityKey\":\"BZmabG1ne5tNP+BOQamy4EjEgGffH/+Q4I0+zdHPAtEt\"},\"_chains\":{\"BZmaI9VwjnDaiWGSJCfUJ40K1HKOxKbzBjVaKFhu6URY\":{\"chainKey\":{\"counter\":0,\"key\":\"MExwqXnMRIAkPhLc23hibcRgoNZEydFnih+/n2FMVDM=\"},\"chainType\":2,\"messageKeys\":{}},\"Bdj95LAfgeNfcNy6r5S5DItRU1uiODtnNMzBeKpFnRdn\":{\"chainKey\":{\"counter\":-1,\"key\":\"0nBc2pZrYvskfznMA+tZ385iAwPt4un9O3NOGq0ie64=\"},\"chainType\":1,\"messageKeys\":{}}}},\"BRriVkFQYmB5XgI96m2WlSI8d/cVpzUxkF1DMUotdPU5\":{\"registrationId\":514790048,\"currentRatchet\":{\"ephemeralKeyPair\":{\"pubKey\":\"BaygUGBMa1vQ02aCIAUGwwkklP5IS2aquVL7GfE94NAW\",\"privKey\":\"2OUYAgQB+/pPcecCuHm81TB0+CtDcXPCFAu3gCpqukY=\"},\"lastRemoteEphemeralKey\":\"BUCShweTnvbRk2yS/zfnRj/mPr4u16vVeOMy4GKljPNE\",\"previousCounter\":0,\"rootKey\":\"i/E+m6YecGwHHJMc4kSb88n98OGJEdlu7bd1aq7Qv/I=\"},\"indexInfo\":{\"baseKey\":\"BRriVkFQYmB5XgI96m2WlSI8d/cVpzUxkF1DMUotdPU5\",\"baseKeyType\":2,\"closed\":1792748736993,\"used\":1792748727987,\"created\":1792748727987,\"remoteIdentityKey\":\"BZmabG1ne5tNP+BOQamy4EjEgGffH/+Q4I0+zdHPAtEt\"},\"_chains\":{\"BUCShweTnvbRk2yS/zfnRj/mPr4u16vVeOMy4GKljPNE\":{\"chainKey\":{\"counter\":0,\"key\":\"sCvrq40r/NLzFSjLH37I4biYeGSmFWJ6dQ6GX4H2E7I=\"},\"chainType\":2,\"messageKeys\":{}},\"BaygUGBMa1vQ02aCIAUGwwkklP5IS2aquVL7GfE94NAW\":{\"chainKey\":{\"counter\":-1,\"key\":\"v5Sj9tk5HnJv+cq+qNhK932nt4ffgDOB1JR4KYa+N9I=\"},\"chainType\":1,\"messageKeys\":{}}}},\"BbFkTTz0OuHFY5P9ILL9opsRt0kg/QeW4dNZW8XG2mUH\":{\"registrationId\":514790048,\"currentRatchet\":{\"ephemeralKeyPair\":{\"pubKey\":\"BXDX1hwyv44ls9YGO/0MAaMITEKOJm/lCCwKdwYJ5ptD\",\"privKey\":\"kDn/lpqIBhpCCCIEUmxE0RaJ3XhXmx0CDt1aVvMqrXM=\"},\"lastRemoteEphemeralKey\":\"BWS4kozKC9JcEYrTWvjlIljprZyRhkr2PW6iCy9v4uM6\",\"previousCounter\":0,\"rootKey\":\"t0AK4q7kBXY6yXoaDR+Bv5uv9cGt1OfLgWPpzsl4HIU=\"},\"indexInfo\":{\"baseKey\":\"BbFkTTz0OuHFY5P9ILL9opsRt0kg/QeW4dNZW8XG2mUH\",\"baseKeyType\":1,\"closed\":1792748743381,\"used\":1792748736992,\"created\":1792748736992,\"remoteIdentityKey\":\"BZmabG1ne5tNP+BOQamy4EjEgGffH/+Q4I0+zdHPAtEt\"},\"_chains\":{\"BXDX1hwyv44ls9YGO/0MAaMITEKOJm/lCCwKdwYJ5ptD\":{\"chainKey\":{\"counter\":0,\"key\":\"FZpL8ozKkJYAahkHNOl4r/12u3BhxLOUWvhEAEX6nzc=\"},\"chainType\":1,\"messageKeys\":{}}},\"pendingPreKey\":{\"signedKeyId\":15594470,\"baseKey\":\"BbFkTTz0OuHFY5P9ILL9opsRt0kg/QeW4dNZW8XG2mUH\",\"preKeyId\":7655199}},\"BS9wlnRkGM9p55J3QcYaRiRwpzwNDuVzfOixMv6WTf47\":{\"registrationId\":514790048,\"currentRatchet\":{\"ephemeralKeyPair\":{\"pubKey\":\"BSSVuz5d1hwwvrj1YPokBa08e+K4h1Qfy0fla0+tDQ0c\",\"privKey\":\"sFfDGsKEmozbPUoJ9W4UkLFwTSj/UYoDz6EFY3yVbU0=\"},\"lastRemoteEphemeralKey\":\"BWS4kozKC9JcEYrTWvjlIljprZyRhkr2PW6iCy9v4uM6\",\"previousCounter\":0,\"rootKey\":\"V/8X7F1VAoOhpos1NejkbEntYEdNUCguE5nQVP+JUDA=\"},\"indexInfo\":{\"baseKey\":\"BS9wlnRkGM9p55J3QcYaRiRwpzwNDuVzfOixMv6WTf47\",\"baseKeyType\":1,\"closed\":-1,\"used\":1792748743380,\"created\":1792748743380,\"remoteIdentityKey\":\"BZmabG1ne5tNP+BOQamy4EjEgGffH/+Q4I0+zdHPAtEt\"},\"_chains\":{\"BSSVuz5d1hwwvrj1YPokBa08e+K4h1Qfy0fla0+tDQ0c\":{\"chainKey\":{\"counter\":0,\"key\":\"/VkV9UuJldrhysZJeWbAcGkLi6rfDhVFFfzhoMckoNM=\"},\"chainType\":1,\"messageKeys\":{}}},\"pendingPreKey\":{\"signedKeyId\":15594470,\"baseKey\":\"BS9wlnRkGM9p55J3QcYaRiRwpzwNDuVzfOixMv6WTf47\",\"preKeyId\":7655200}}},\"version\":\"v1\"}', '2026-10-23 09:45:22', '2026-10-23 09:45:43'),
('session-6281262364197.0', '{\"_sessions\":{\"BY7t0n1AJBUcrKhxkdGFTtiYEJyQmCJdM1kc3JTyAuYk\":{\"registrationId\":1295218465,\"currentRatchet\":{\"ephemeralKeyPair\":{\"pubKey\":\"BaskBfJGTsAEyuc2Bz13/qTYJqf/IsXrgj+DEgRwP3dd\",\"privKey\":\"kBk+3EfFNjf7+aTC1PqTTbbZrBmgFT3lLE4DEMR/HEg=\"},\"lastRemoteEphemeralKey\":\"BcIV5PLaUjNbG8XolpeWdlP1DTn/pPtkPTur1uI1WoA5\",\"previousCounter\":0,\"rootKey\":\"Gw9sfa8mTxBREoD3Bynu+j2KZpEE/g6+yaF6E9cTY7k=\"},\"indexInfo\":{\"baseKey\":\"BY7t0n1AJBUcrKhxkdGFTtiYEJyQmCJdM1kc3JTyAuYk\",\"baseKeyType\":1,\"closed\":-1,\"used\":1792684442637,\"created\":1792684442637,\"remoteIdentityKey\":\"BeWhNI/5zM5CtHXYrUTynpdKeJk2l9SHA/+oFZqbSw4n\"},\"_chains\":{\"BaskBfJGTsAEyuc2Bz13/qTYJqf/IsXrgj+DEgRwP3dd\":{\"chainKey\":{\"counter\":18541,\"key\":\"H5erJYAH95XVMAFbuQk/zgoMHctWi+TOTE/CTTAT1LU=\"},\"chainType\":1,\"messageKeys\":{}}},\"pendingPreKey\":{\"signedKeyId\":228064,\"baseKey\":\"BY7t0n1AJBUcrKhxkdGFTtiYEJyQmCJdM1kc3JTyAuYk\",\"preKeyId\":998509}}},\"version\":\"v1\"}', '2026-10-23 04:36:24', '2026-10-23 20:37:56'),
('session-6282183394549.0', '{\"_sessions\":{\"BZBmx3fuX5iPgqZP2JtyiXehtkF3CVJEg2D2RMXEGVgy\":{\"registrationId\":1082463293,\"currentRatchet\":{\"ephemeralKeyPair\":{\"pubKey\":\"BZmyHGQrtgiyup1JBJEYD/RXk/4/pB6LqZjERWmVpNxs\",\"privKey\":\"mJbdToVrXyDHcePs111xJrJwb7ZDMNHAExgDdnC8p2M=\"},\"lastRemoteEphemeralKey\":\"BWFfsCX1A9/3J0xW1Cer4kYDHqgY4NdX8QUbRhFWM+B4\",\"previousCounter\":0,\"rootKey\":\"5FT8ta7HFwgQTz+zlwkhuPY2moz6fBqCHLgtGFHqf8s=\"},\"indexInfo\":{\"baseKey\":\"BZBmx3fuX5iPgqZP2JtyiXehtkF3CVJEg2D2RMXEGVgy\",\"baseKeyType\":1,\"closed\":-1,\"used\":1792741213878,\"created\":1792741213878,\"remoteIdentityKey\":\"BUyHbSt6YzQGYM1Okz6IXtvagepP/Rkt2N6ZfcdIj90J\"},\"_chains\":{\"BZmyHGQrtgiyup1JBJEYD/RXk/4/pB6LqZjERWmVpNxs\":{\"chainKey\":{\"counter\":5,\"key\":\"oVIQz1xAMl6GrGnGfgbMOlsVGsiuWL4c67f0szmrC3A=\"},\"chainType\":1,\"messageKeys\":{}}},\"pendingPreKey\":{\"signedKeyId\":15903198,\"baseKey\":\"BZBmx3fuX5iPgqZP2JtyiXehtkF3CVJEg2D2RMXEGVgy\",\"preKeyId\":10781099}}},\"version\":\"v1\"}', '2026-10-23 07:40:13', '2026-10-23 11:29:19');
INSERT INTO `whatsapp_sessions` (`key`, `value`, `created_at`, `updated_at`) VALUES
('session-6282183394549.17', '{\"_sessions\":{\"BU/gdbhwgoq7KhBrYj35L3zvXFN+tYS9/Copr15FB2Nh\":{\"registrationId\":9554,\"currentRatchet\":{\"ephemeralKeyPair\":{\"pubKey\":\"BVpr7QnYMJFlGKhrB2d1QjxV34R16m111ebCmxj8cxQV\",\"privKey\":\"6DeEb77pD7pgDxawuoUaP+kO5GZcvJyRm0hJbYaSbmY=\"},\"lastRemoteEphemeralKey\":\"Bek0Gd/w6M74rhzrdhqzIlmHDOB/PWaVPl94K9HlwnNw\",\"previousCounter\":0,\"rootKey\":\"VtxkYIPM6PClF1Z1FpWy2MzczuhicNRcE/i2ZppKp0g=\"},\"indexInfo\":{\"baseKey\":\"BU/gdbhwgoq7KhBrYj35L3zvXFN+tYS9/Copr15FB2Nh\",\"baseKeyType\":1,\"closed\":-1,\"used\":1792741213886,\"created\":1792741213886,\"remoteIdentityKey\":\"BSiS5fIfvLBjQLvwBilDF+DayIRuEAQzfIddYMv1HrB5\"},\"_chains\":{\"BVpr7QnYMJFlGKhrB2d1QjxV34R16m111ebCmxj8cxQV\":{\"chainKey\":{\"counter\":5,\"key\":\"pLQqBwMRFQo4YU+5hiSL+UYTIwVA1YhEvo0fWyu3lRQ=\"},\"chainType\":1,\"messageKeys\":{}}},\"pendingPreKey\":{\"signedKeyId\":2,\"baseKey\":\"BU/gdbhwgoq7KhBrYj35L3zvXFN+tYS9/Copr15FB2Nh\",\"preKeyId\":1453}}},\"version\":\"v1\"}', '2026-10-23 07:40:14', '2026-10-23 11:29:19'),
('session-6282183394549.18', '{\"_sessions\":{\"BRAdD1/j+XtmvId12FVY/hJQ9cZqxlWxakGkZoW1ZQZT\":{\"registrationId\":1136114908,\"currentRatchet\":{\"ephemeralKeyPair\":{\"pubKey\":\"BffYBBwnnJWw67jZtjVxPUlpPKpBqN47FkYQxKzFly48\",\"privKey\":\"8HyxIGPSriyVbCiTEJnylSq6lUJz27rKjOqVR3fAGFc=\"},\"lastRemoteEphemeralKey\":\"BWBDdxI1z8+RRRgZC7KwQs61FtEMA2MlcQN4GRo85Hg9\",\"previousCounter\":0,\"rootKey\":\"ZWewnDh3cTiD5zT2DJnEZHH/sqv6X2T5QWPZ+suFFoo=\"},\"indexInfo\":{\"baseKey\":\"BRAdD1/j+XtmvId12FVY/hJQ9cZqxlWxakGkZoW1ZQZT\",\"baseKeyType\":1,\"closed\":-1,\"used\":1792741213884,\"created\":1792741213884,\"remoteIdentityKey\":\"BSnAV3ytSon1esY4Q2EwxbWAwtEwOuB33mBhgsCB+Idn\"},\"_chains\":{\"BffYBBwnnJWw67jZtjVxPUlpPKpBqN47FkYQxKzFly48\":{\"chainKey\":{\"counter\":5,\"key\":\"G7zWEuUIzWm90Noy14WYV1EHVuPCgbfDbwXnAsXssBM=\"},\"chainType\":1,\"messageKeys\":{}}},\"pendingPreKey\":{\"signedKeyId\":16301960,\"baseKey\":\"BRAdD1/j+XtmvId12FVY/hJQ9cZqxlWxakGkZoW1ZQZT\",\"preKeyId\":1650}}},\"version\":\"v1\"}', '2026-10-23 07:40:14', '2026-10-23 11:29:19'),
('session-6282183394549.19', '{\"_sessions\":{\"BYECkrMq8oOzOC2SZnKjz9uqJaB5otJqLKol5QGATtNI\":{\"registrationId\":583,\"currentRatchet\":{\"ephemeralKeyPair\":{\"pubKey\":\"BRuY/sK2NB8Zcx02N511FkPybIarlgGrrXivYwW5uXAJ\",\"privKey\":\"yC4XPNoXiSApJk2Ezt+JT+nf2Ew35XWmfLa2TV5SXnI=\"},\"lastRemoteEphemeralKey\":\"BUpX0bIoly/BFJc3dSuRj4qyghEcG9FixpW2KwOqQV4U\",\"previousCounter\":0,\"rootKey\":\"PY2hnI2LaCHR72/rBWvk1mceWWFgQwUNi3BUtgFxZXg=\"},\"indexInfo\":{\"baseKey\":\"BYECkrMq8oOzOC2SZnKjz9uqJaB5otJqLKol5QGATtNI\",\"baseKeyType\":1,\"closed\":-1,\"used\":1792741213881,\"created\":1792741213881,\"remoteIdentityKey\":\"BahUo/aRBOkHKJ/iYT0nNwMaFGINzclTZMHtqYbg7Wts\"},\"_chains\":{\"BRuY/sK2NB8Zcx02N511FkPybIarlgGrrXivYwW5uXAJ\":{\"chainKey\":{\"counter\":5,\"key\":\"ksI9uQSxvnFm0dLKpDxD5fU7vkcyvm+d7MOsX7T8NOM=\"},\"chainType\":1,\"messageKeys\":{}}},\"pendingPreKey\":{\"signedKeyId\":1,\"baseKey\":\"BYECkrMq8oOzOC2SZnKjz9uqJaB5otJqLKol5QGATtNI\",\"preKeyId\":710}}},\"version\":\"v1\"}', '2026-10-23 07:40:13', '2026-10-23 11:29:19'),
('session-6283183494835.0', '{\"_sessions\":{\"BZLsbqG16pLzBvnqF/8abtl3lLBJaplQpIfSB4N5HHco\":{\"registrationId\":514790048,\"currentRatchet\":{\"ephemeralKeyPair\":{\"pubKey\":\"BRLN1IRBAtcwO16dtRFW/iZfxHbBmzszzEiyz9QXVZ8h\",\"privKey\":\"oFN/MRtSMu7tGAPaWk3cp/CIMzaYtBe6n8AuRuMLKmM=\"},\"lastRemoteEphemeralKey\":\"BWS4kozKC9JcEYrTWvjlIljprZyRhkr2PW6iCy9v4uM6\",\"previousCounter\":0,\"rootKey\":\"1rVPVW/rRmmbYVZBzEOeDb/sh0PQP3XyNJpEcYtY1xQ=\"},\"indexInfo\":{\"baseKey\":\"BZLsbqG16pLzBvnqF/8abtl3lLBJaplQpIfSB4N5HHco\",\"baseKeyType\":1,\"closed\":-1,\"used\":1792743762212,\"created\":1792743762212,\"remoteIdentityKey\":\"BZmabG1ne5tNP+BOQamy4EjEgGffH/+Q4I0+zdHPAtEt\"},\"_chains\":{\"BRLN1IRBAtcwO16dtRFW/iZfxHbBmzszzEiyz9QXVZ8h\":{\"chainKey\":{\"counter\":2,\"key\":\"nrd3je8i/JmsMqza6f8c8YLSgZv2MtgVFbEc6TNADQs=\"},\"chainType\":1,\"messageKeys\":{}}},\"pendingPreKey\":{\"signedKeyId\":15594470,\"baseKey\":\"BZLsbqG16pLzBvnqF/8abtl3lLBJaplQpIfSB4N5HHco\",\"preKeyId\":7655198}}},\"version\":\"v1\"}', '2026-10-23 08:22:42', '2026-10-23 09:45:41'),
('session-70523983814871.0', '{\"_sessions\":{\"BbysE7mepfZHOzP6bpIbuJymyTRiiv6QCXQ9qhP/oVc4\":{\"registrationId\":1082463293,\"currentRatchet\":{\"ephemeralKeyPair\":{\"pubKey\":\"BfWJDRoBcwwLKY2DBgCGwCaS7vYeHXBtBT5s775RejVw\",\"privKey\":\"oCfqOWrZGAw7+JMUPNZoUgymmClbHu+qvph7qhl2uU4=\"},\"lastRemoteEphemeralKey\":\"BYfbRrqL4M/YthA5X22N4yhh9s3XS4H4UHR6Vm7iBeQB\",\"previousCounter\":0,\"rootKey\":\"faYgaAoFqqW+4J+Vjykamqy2rjZt4jw+31shxOTmgvk=\"},\"indexInfo\":{\"baseKey\":\"BbysE7mepfZHOzP6bpIbuJymyTRiiv6QCXQ9qhP/oVc4\",\"baseKeyType\":2,\"closed\":1792741205572,\"used\":1792741200015,\"created\":1792741200015,\"remoteIdentityKey\":\"BUyHbSt6YzQGYM1Okz6IXtvagepP/Rkt2N6ZfcdIj90J\"},\"_chains\":{\"BYfbRrqL4M/YthA5X22N4yhh9s3XS4H4UHR6Vm7iBeQB\":{\"chainKey\":{\"counter\":0,\"key\":\"n2uZ+MhhDqaQGMtdFgokKY4NEvrLTNRRUjCggz+SrZI=\"},\"chainType\":2,\"messageKeys\":{}},\"BfWJDRoBcwwLKY2DBgCGwCaS7vYeHXBtBT5s775RejVw\":{\"chainKey\":{\"counter\":-1,\"key\":\"RIp7zmOITeu0IKC3yYikltxBkic4DcktWP5Ef8vQx5o=\"},\"chainType\":1,\"messageKeys\":{}}}},\"BfTv+iau9J20UhC+PJcnbtxBUOWBqirWoMb/mAxMmVdS\":{\"registrationId\":1082463293,\"currentRatchet\":{\"ephemeralKeyPair\":{\"pubKey\":\"BTvF1vKGyh6e6wmjvKyXpG+2rxJT/+t30dK8pHdQXtlp\",\"privKey\":\"kBzIhouzFDEMGnksULeHdfRpIiauLGsiqpurexNYLF4=\"},\"lastRemoteEphemeralKey\":\"BcOiQSqXgW8FRv7nlYjM0EDdiUsnMvOxujt7c2g8LeNd\",\"previousCounter\":0,\"rootKey\":\"mbF81BoVKOBs5XsMMp2nUGAAnQBqCpD+y9vCJ5LDKm8=\"},\"indexInfo\":{\"baseKey\":\"BfTv+iau9J20UhC+PJcnbtxBUOWBqirWoMb/mAxMmVdS\",\"baseKeyType\":2,\"closed\":1792741560710,\"used\":1792741205575,\"created\":1792741205575,\"remoteIdentityKey\":\"BUyHbSt6YzQGYM1Okz6IXtvagepP/Rkt2N6ZfcdIj90J\"},\"_chains\":{\"BcOiQSqXgW8FRv7nlYjM0EDdiUsnMvOxujt7c2g8LeNd\":{\"chainKey\":{\"counter\":0,\"key\":\"sfe0Rw/5JXK5CQaYAwHhvHxuqUcMvX0YMlEvK1RQfaA=\"},\"chainType\":2,\"messageKeys\":{}},\"BTvF1vKGyh6e6wmjvKyXpG+2rxJT/+t30dK8pHdQXtlp\":{\"chainKey\":{\"counter\":-1,\"key\":\"yQ57A+7zrldAvxvinzsclv0879XRb0RzKhL64gembR0=\"},\"chainType\":1,\"messageKeys\":{}}}},\"BR3iftDhtiUrJ5zuG8naDbyhnn9BGtLIaPxHx40MdPpy\":{\"registrationId\":1082463293,\"currentRatchet\":{\"ephemeralKeyPair\":{\"pubKey\":\"BXllbzQeauY4ILklFuUm0n2/QAMecJ55t6AB6FX2t4kQ\",\"privKey\":\"QCMaCOKfvHHaLm06DAaEe8xLr026LF6LlARzbILZ03E=\"},\"lastRemoteEphemeralKey\":\"BTy4UTFK3QTqRUMCzkh1pqNrCT+xAdz0GT+NqiYrbRw+\",\"previousCounter\":0,\"rootKey\":\"w/02L+A+U7XTQibRG+pB8ENBYf8P3Delc2pjFJ+6DRE=\"},\"indexInfo\":{\"baseKey\":\"BR3iftDhtiUrJ5zuG8naDbyhnn9BGtLIaPxHx40MdPpy\",\"baseKeyType\":2,\"closed\":1792741573635,\"used\":1792741560712,\"created\":1792741560712,\"remoteIdentityKey\":\"BUyHbSt6YzQGYM1Okz6IXtvagepP/Rkt2N6ZfcdIj90J\"},\"_chains\":{\"BTy4UTFK3QTqRUMCzkh1pqNrCT+xAdz0GT+NqiYrbRw+\":{\"chainKey\":{\"counter\":2,\"key\":\"nWZHM9/eQpjMPQ4xkpBbbDmdfia+JFhE2bS1Y65j0pg=\"},\"chainType\":2,\"messageKeys\":{}},\"BXllbzQeauY4ILklFuUm0n2/QAMecJ55t6AB6FX2t4kQ\":{\"chainKey\":{\"counter\":-1,\"key\":\"0+njWDirqP7hzietCHOGILeXJdX3XVGrb60JybI+ej4=\"},\"chainType\":1,\"messageKeys\":{}}}},\"Bf86iUgdAvugaKkriGUNRgkfuKgq96RP8OJ6Nz6lkZxZ\":{\"registrationId\":1082463293,\"currentRatchet\":{\"ephemeralKeyPair\":{\"pubKey\":\"BbnCGLE7A8Y/LnGPDDQ9akyz4hQlnxuO2H9MR81v2Ns4\",\"privKey\":\"AGfm0XjJntb7IT1bHauVHnJ0KQk4H5haudY2qGy9NHk=\"},\"lastRemoteEphemeralKey\":\"BWFfsCX1A9/3J0xW1Cer4kYDHqgY4NdX8QUbRhFWM+B4\",\"previousCounter\":0,\"rootKey\":\"0PWCvmaSUv5B1dM9fqRAYOzjH/7mKGPmELLLYk8u9Z0=\"},\"indexInfo\":{\"baseKey\":\"Bf86iUgdAvugaKkriGUNRgkfuKgq96RP8OJ6Nz6lkZxZ\",\"baseKeyType\":1,\"closed\":1792743762425,\"used\":1792741573633,\"created\":1792741573633,\"remoteIdentityKey\":\"BUyHbSt6YzQGYM1Okz6IXtvagepP/Rkt2N6ZfcdIj90J\"},\"_chains\":{\"BbnCGLE7A8Y/LnGPDDQ9akyz4hQlnxuO2H9MR81v2Ns4\":{\"chainKey\":{\"counter\":0,\"key\":\"QLTJc5V9pPgo0pZhqwUcCnVUcbtrQbR4X2mC2rJbwi4=\"},\"chainType\":1,\"messageKeys\":{}}},\"pendingPreKey\":{\"signedKeyId\":15903198,\"baseKey\":\"Bf86iUgdAvugaKkriGUNRgkfuKgq96RP8OJ6Nz6lkZxZ\",\"preKeyId\":10781100}},\"BfeJm+kbbXEOuXmGloMBK/LDQzlUJwo0/8K1cw3BY2ZH\":{\"registrationId\":1082463293,\"currentRatchet\":{\"ephemeralKeyPair\":{\"pubKey\":\"BQEcpsY356ojpFDvaUiK0xdup+bHornB3AnktHu6YlFD\",\"privKey\":\"mH2a/kPgv8lSSgLTA2kF3CBkqQn2hsP3O7zeq5Syxlo=\"},\"lastRemoteEphemeralKey\":\"BWFfsCX1A9/3J0xW1Cer4kYDHqgY4NdX8QUbRhFWM+B4\",\"previousCounter\":0,\"rootKey\":\"RA18C9y9tlDwHmSWAsnB5Tg2MZEaHOX614m3PhHkcgk=\"},\"indexInfo\":{\"baseKey\":\"BfeJm+kbbXEOuXmGloMBK/LDQzlUJwo0/8K1cw3BY2ZH\",\"baseKeyType\":1,\"closed\":1792743768738,\"used\":1792743762424,\"created\":1792743762424,\"remoteIdentityKey\":\"BUyHbSt6YzQGYM1Okz6IXtvagepP/Rkt2N6ZfcdIj90J\"},\"_chains\":{\"BQEcpsY356ojpFDvaUiK0xdup+bHornB3AnktHu6YlFD\":{\"chainKey\":{\"counter\":0,\"key\":\"u+HQ3fnavqNdck3CLtgEjesa8JkQfjwunaXdzq49Zh0=\"},\"chainType\":1,\"messageKeys\":{}}},\"pendingPreKey\":{\"signedKeyId\":15903198,\"baseKey\":\"BfeJm+kbbXEOuXmGloMBK/LDQzlUJwo0/8K1cw3BY2ZH\",\"preKeyId\":10781101}},\"BRLGIW3O+xrcNBh8n1rcHzlp8mBN+wfzvGPKWws5f6MW\":{\"registrationId\":1082463293,\"currentRatchet\":{\"ephemeralKeyPair\":{\"pubKey\":\"BZpqQOnwCx2/XJHJzoLRKXwA5K8KhP33yKar3x6jSJEq\",\"privKey\":\"IAdHx5PSl0S59uvvWYPcNXGVlj5vVIJVDxshj9ZVIVQ=\"},\"lastRemoteEphemeralKey\":\"BWFfsCX1A9/3J0xW1Cer4kYDHqgY4NdX8QUbRhFWM+B4\",\"previousCounter\":0,\"rootKey\":\"6VbvNoUibgfzty3ah/W+vzHht0GwI0BTqW+Ne1PUCmM=\"},\"indexInfo\":{\"baseKey\":\"BRLGIW3O+xrcNBh8n1rcHzlp8mBN+wfzvGPKWws5f6MW\",\"baseKeyType\":1,\"closed\":1792754961480,\"used\":1792743768737,\"created\":1792743768737,\"remoteIdentityKey\":\"BUyHbSt6YzQGYM1Okz6IXtvagepP/Rkt2N6ZfcdIj90J\"},\"_chains\":{\"BZpqQOnwCx2/XJHJzoLRKXwA5K8KhP33yKar3x6jSJEq\":{\"chainKey\":{\"counter\":0,\"key\":\"Er4n08M+e/0lZ3QKm1BS6wKtcvKA3HqL0sHBoPsVwms=\"},\"chainType\":1,\"messageKeys\":{}}},\"pendingPreKey\":{\"signedKeyId\":15903198,\"baseKey\":\"BRLGIW3O+xrcNBh8n1rcHzlp8mBN+wfzvGPKWws5f6MW\",\"preKeyId\":10781102}},\"BXTCxXb0zFjG9m4cnvdXg5bLWFJykLvgDVKBJD3XijwF\":{\"registrationId\":1082463293,\"currentRatchet\":{\"ephemeralKeyPair\":{\"pubKey\":\"BS9keVkxI3aWpbYZWg3HYU1REniiKDdRaQHPwX9MvfZ0\",\"privKey\":\"yMeQSI0QZajTcaaDKMwSLU5oyGJ/psHz+oW+AxXyakE=\"},\"lastRemoteEphemeralKey\":\"BWFfsCX1A9/3J0xW1Cer4kYDHqgY4NdX8QUbRhFWM+B4\",\"previousCounter\":0,\"rootKey\":\"Q3KV7kUgzXKK9eXXTsefVcM4WWiXTPRj7agDQ2o85Ck=\"},\"indexInfo\":{\"baseKey\":\"BXTCxXb0zFjG9m4cnvdXg5bLWFJykLvgDVKBJD3XijwF\",\"baseKeyType\":1,\"closed\":-1,\"used\":1792754961439,\"created\":1792754961439,\"remoteIdentityKey\":\"BUyHbSt6YzQGYM1Okz6IXtvagepP/Rkt2N6ZfcdIj90J\"},\"_chains\":{\"BS9keVkxI3aWpbYZWg3HYU1REniiKDdRaQHPwX9MvfZ0\":{\"chainKey\":{\"counter\":0,\"key\":\"3TbFXxK06MyJr52H9Yjwui//+1Wp1QtHpauRikwwihA=\"},\"chainType\":1,\"messageKeys\":{}}},\"pendingPreKey\":{\"signedKeyId\":15903198,\"baseKey\":\"BXTCxXb0zFjG9m4cnvdXg5bLWFJykLvgDVKBJD3XijwF\",\"preKeyId\":10781104}}},\"version\":\"v1\"}', '2026-10-23 07:40:00', '2026-10-23 11:29:21'),
('session-70523983814871.17', '{\"_sessions\":{\"BTmNvRaA9wftC612qvG7ZVHUwiXLtZUdR6+brZO8TGdG\":{\"registrationId\":9554,\"currentRatchet\":{\"ephemeralKeyPair\":{\"pubKey\":\"BfGUBMBDLChINMegqSo4RQxG3WWBwGltwyKI4dxUvT8o\",\"privKey\":\"yNVuCWcu/S6o6NssPsrBoE9/hkUO6Me9dzKy2pAAamU=\"},\"lastRemoteEphemeralKey\":\"Bek0Gd/w6M74rhzrdhqzIlmHDOB/PWaVPl94K9HlwnNw\",\"previousCounter\":0,\"rootKey\":\"7ivsn0NpFY7lRdUaN1hdGVHXD0a27E9Z2poi/0Ppg4s=\"},\"indexInfo\":{\"baseKey\":\"BTmNvRaA9wftC612qvG7ZVHUwiXLtZUdR6+brZO8TGdG\",\"baseKeyType\":1,\"closed\":-1,\"used\":1792741574252,\"created\":1792741574252,\"remoteIdentityKey\":\"BSiS5fIfvLBjQLvwBilDF+DayIRuEAQzfIddYMv1HrB5\"},\"_chains\":{\"BfGUBMBDLChINMegqSo4RQxG3WWBwGltwyKI4dxUvT8o\":{\"chainKey\":{\"counter\":3,\"key\":\"uCih5hwaFqtvk8TiDFR1o1kzojeZp1REiM2o4DyVsok=\"},\"chainType\":1,\"messageKeys\":{}}},\"pendingPreKey\":{\"signedKeyId\":2,\"baseKey\":\"BTmNvRaA9wftC612qvG7ZVHUwiXLtZUdR6+brZO8TGdG\",\"preKeyId\":1454}}},\"version\":\"v1\"}', '2026-10-23 07:46:14', '2026-10-23 11:29:21'),
('session-70523983814871.18', '{\"_sessions\":{\"BfpdSwG/EgxlyxU7UoB0T7HTyd9WPkoQrU50IwGKHKIu\":{\"registrationId\":1136114908,\"currentRatchet\":{\"ephemeralKeyPair\":{\"pubKey\":\"BeCV7+d1TXGiI5a8NojseWjf4Q6/iqjdS+90XHn9nFYU\",\"privKey\":\"EFnxqx8N6+exrwO3C4kisOAP+WQdAETY5Szb+ye0mWY=\"},\"lastRemoteEphemeralKey\":\"BWBDdxI1z8+RRRgZC7KwQs61FtEMA2MlcQN4GRo85Hg9\",\"previousCounter\":0,\"rootKey\":\"bY6R85Gr+hvwhmVK5LO06iOGocFVb/qB+lBGLiBlvF0=\"},\"indexInfo\":{\"baseKey\":\"BfpdSwG/EgxlyxU7UoB0T7HTyd9WPkoQrU50IwGKHKIu\",\"baseKeyType\":1,\"closed\":1792754944007,\"used\":1792741574249,\"created\":1792741574249,\"remoteIdentityKey\":\"BSnAV3ytSon1esY4Q2EwxbWAwtEwOuB33mBhgsCB+Idn\"},\"_chains\":{\"BeCV7+d1TXGiI5a8NojseWjf4Q6/iqjdS+90XHn9nFYU\":{\"chainKey\":{\"counter\":2,\"key\":\"PRcH6qRsNea7YeCNPgQKmap6y8g376GP82wtClVahhw=\"},\"chainType\":1,\"messageKeys\":{}}},\"pendingPreKey\":{\"signedKeyId\":16301960,\"baseKey\":\"BfpdSwG/EgxlyxU7UoB0T7HTyd9WPkoQrU50IwGKHKIu\",\"preKeyId\":1651}},\"BXoecMwEd1cGpyyJLhxXnATOJJWkTUkGfAYw/5gUY9BT\":{\"registrationId\":1136114908,\"currentRatchet\":{\"ephemeralKeyPair\":{\"pubKey\":\"BcFvZ7Cl8h9rT4wa0NM/8VHRHL8RHxE/mxWrLjoQ+QFU\",\"privKey\":\"+Kqh1QD/r5DYXR+jZ4cIIu4QslaiHXcH9i+shl2XvHs=\"},\"lastRemoteEphemeralKey\":\"BcoTotL19Q8w6xRqy84bI8kCg/wtGNnzqfibNXaqMDIW\",\"previousCounter\":0,\"rootKey\":\"9zxp1Vhm0GX+zIi23mydxZciZDR3+3Vxdp6QlVBxJoM=\"},\"indexInfo\":{\"baseKey\":\"BXoecMwEd1cGpyyJLhxXnATOJJWkTUkGfAYw/5gUY9BT\",\"baseKeyType\":2,\"closed\":1792754944231,\"used\":1792754944009,\"created\":1792754944009,\"remoteIdentityKey\":\"BSnAV3ytSon1esY4Q2EwxbWAwtEwOuB33mBhgsCB+Idn\"},\"_chains\":{\"BcoTotL19Q8w6xRqy84bI8kCg/wtGNnzqfibNXaqMDIW\":{\"chainKey\":{\"counter\":0,\"key\":\"HnNFmi/UoaBD8adwTNaGVe4d/uwbXTWbfFJchJKU00A=\"},\"chainType\":2,\"messageKeys\":{}},\"BcFvZ7Cl8h9rT4wa0NM/8VHRHL8RHxE/mxWrLjoQ+QFU\":{\"chainKey\":{\"counter\":-1,\"key\":\"nUqYCawMtkfQgwlOKxXEOy63TiNaljvOmsOwvyzJXTs=\"},\"chainType\":1,\"messageKeys\":{}}}},\"BaRHYbN9nIUVfetnBZxDKjeSyWwmuR9gpDmrpDg1/5FB\":{\"registrationId\":1136114908,\"currentRatchet\":{\"ephemeralKeyPair\":{\"pubKey\":\"BdshxERJIKeRC9OmLh+nbCH+n26KAd9xHVD7nqV3Y8te\",\"privKey\":\"0JvpIbbfQi0TCNBYCK0gqVAO3BPBmMQZwKXFApDtcUc=\"},\"lastRemoteEphemeralKey\":\"BQfqM1ThAM4mlICltveLB8Ny3MaUjOuJ/mqdWE1knVRg\",\"previousCounter\":0,\"rootKey\":\"iuaFAMls+lKMR2jRnyBuqR+80nYbZmAWZYYGjf7Snf0=\"},\"indexInfo\":{\"baseKey\":\"BaRHYbN9nIUVfetnBZxDKjeSyWwmuR9gpDmrpDg1/5FB\",\"baseKeyType\":2,\"closed\":1792754944757,\"used\":1792754944233,\"created\":1792754944233,\"remoteIdentityKey\":\"BSnAV3ytSon1esY4Q2EwxbWAwtEwOuB33mBhgsCB+Idn\"},\"_chains\":{\"BQfqM1ThAM4mlICltveLB8Ny3MaUjOuJ/mqdWE1knVRg\":{\"chainKey\":{\"counter\":1,\"key\":\"0U3LM5I3GJwLmtoSfLkmAphMQQVqKobrA4k2c77cnUo=\"},\"chainType\":2,\"messageKeys\":{}},\"BdshxERJIKeRC9OmLh+nbCH+n26KAd9xHVD7nqV3Y8te\":{\"chainKey\":{\"counter\":-1,\"key\":\"xBfcJgJSvvpglfFQaaezxidsQcFGqhcDNE5MvidsO30=\"},\"chainType\":1,\"messageKeys\":{}}}},\"BVloahjxP7dT4q/9FB/Hb/D9TQAJWtEk9Wf/0RZpTVkO\":{\"registrationId\":1136114908,\"currentRatchet\":{\"ephemeralKeyPair\":{\"pubKey\":\"BcGL0Bz7ft1g/ZPO8z69wyojT73H7HS8jqGxZ017okRo\",\"privKey\":\"6HOHz7rHuVTsR3zR3j1hfYUha4wk6UwR5YsYsyqqE1g=\"},\"lastRemoteEphemeralKey\":\"BY8l4h18FVVCnQmOPsZgOnbw14iW2KM3r3oliJwAaAZ6\",\"previousCounter\":0,\"rootKey\":\"B1b3qzFPptFouuNUqcNcXHSDmZRrumT4VrlYamymf/8=\"},\"indexInfo\":{\"baseKey\":\"BVloahjxP7dT4q/9FB/Hb/D9TQAJWtEk9Wf/0RZpTVkO\",\"baseKeyType\":2,\"closed\":1792754944891,\"used\":1792754944760,\"created\":1792754944760,\"remoteIdentityKey\":\"BSnAV3ytSon1esY4Q2EwxbWAwtEwOuB33mBhgsCB+Idn\"},\"_chains\":{\"BY8l4h18FVVCnQmOPsZgOnbw14iW2KM3r3oliJwAaAZ6\":{\"chainKey\":{\"counter\":0,\"key\":\"OAI0s9YZ51zTmOMa3n5BeT2AFJ4jX7bblxVfBgN5vR8=\"},\"chainType\":2,\"messageKeys\":{}},\"BcGL0Bz7ft1g/ZPO8z69wyojT73H7HS8jqGxZ017okRo\":{\"chainKey\":{\"counter\":-1,\"key\":\"Bfu1kE69jQxqL+YVXgNlUpw6uGiAMns2ZSTs2i21q04=\"},\"chainType\":1,\"messageKeys\":{}}}},\"BW/zikSa7GJ9Qa/UWjR/7e18NSULNe1hbQpQUqo3vBxL\":{\"registrationId\":1136114908,\"currentRatchet\":{\"ephemeralKeyPair\":{\"pubKey\":\"BbSFoxIqU7hlw4Z3It9YLH3icJTSsUdVfk/Gxlf0pSso\",\"privKey\":\"8AqdmuBTV/z4No5Vidr//kiJ7xnRufEw/1SeWnJnVVo=\"},\"lastRemoteEphemeralKey\":\"BdKtmLOkmaUn+MuIpbSjT9iHomk8mhhzU++mw2tczYcw\",\"previousCounter\":0,\"rootKey\":\"qx1FSrRAH1KuG7vD+/1hR20EdoS+EYE9DR/IhiyyIFg=\"},\"indexInfo\":{\"baseKey\":\"BW/zikSa7GJ9Qa/UWjR/7e18NSULNe1hbQpQUqo3vBxL\",\"baseKeyType\":2,\"closed\":1792754962434,\"used\":1792754944892,\"created\":1792754944892,\"remoteIdentityKey\":\"BSnAV3ytSon1esY4Q2EwxbWAwtEwOuB33mBhgsCB+Idn\"},\"_chains\":{\"BdKtmLOkmaUn+MuIpbSjT9iHomk8mhhzU++mw2tczYcw\":{\"chainKey\":{\"counter\":2,\"key\":\"UI0ebyvcfY398xqwSGkh5bkSxzTj/UI7HErtVE5d5Qs=\"},\"chainType\":2,\"messageKeys\":{}},\"BbSFoxIqU7hlw4Z3It9YLH3icJTSsUdVfk/Gxlf0pSso\":{\"chainKey\":{\"counter\":0,\"key\":\"hT1XxIYJO+7fLhR+/dPcylEowam7LYrWYCGOEnaLwBw=\"},\"chainType\":1,\"messageKeys\":{}}}},\"BfyzezBXtna8rIi+iiLCq8saTRKaLNM4PBBSz2FCGqk1\":{\"registrationId\":1136114908,\"currentRatchet\":{\"ephemeralKeyPair\":{\"pubKey\":\"BUzOSrXF/EwpPxDzhRBwWTAp3MxpjRt4Yv05rYFzUKY5\",\"privKey\":\"8D9H/rk0tMNzvTc/HJr0V6JcHtnrD7F8WvC8axlxd2M=\"},\"lastRemoteEphemeralKey\":\"BWBDdxI1z8+RRRgZC7KwQs61FtEMA2MlcQN4GRo85Hg9\",\"previousCounter\":0,\"rootKey\":\"zc2IjYIIwX0KcZmR7meE7H2IL0dDtqYNgFChHG6BXPo=\"},\"indexInfo\":{\"baseKey\":\"BfyzezBXtna8rIi+iiLCq8saTRKaLNM4PBBSz2FCGqk1\",\"baseKeyType\":1,\"closed\":-1,\"used\":1792754962433,\"created\":1792754962433,\"remoteIdentityKey\":\"BSnAV3ytSon1esY4Q2EwxbWAwtEwOuB33mBhgsCB+Idn\"},\"_chains\":{\"BUzOSrXF/EwpPxDzhRBwWTAp3MxpjRt4Yv05rYFzUKY5\":{\"chainKey\":{\"counter\":0,\"key\":\"SOsNL77AiQBXgIaf/ErfWA9JIIJa/ZJrTJZV7QeTvDY=\"},\"chainType\":1,\"messageKeys\":{}}},\"pendingPreKey\":{\"signedKeyId\":16301960,\"baseKey\":\"BfyzezBXtna8rIi+iiLCq8saTRKaLNM4PBBSz2FCGqk1\",\"preKeyId\":1655}}},\"version\":\"v1\"}', '2026-10-23 07:46:14', '2026-10-23 11:29:22'),
('session-70523983814871.19', '{\"_sessions\":{\"BWvfrSijRRjw9polaJUyJtMNl70Y+sFEwmeesZ5K4Ix7\":{\"registrationId\":583,\"currentRatchet\":{\"ephemeralKeyPair\":{\"pubKey\":\"BcWkGxNX5t1Z2s5oId900Rti0d50MVgHedIMngr/22JS\",\"privKey\":\"MLuhb/1dXTpel4e7g85yaMAequNIcwXj53N6KBb48kg=\"},\"lastRemoteEphemeralKey\":\"BUpX0bIoly/BFJc3dSuRj4qyghEcG9FixpW2KwOqQV4U\",\"previousCounter\":0,\"rootKey\":\"pTDNiVhD/4sYTexZPkZmcluMI73dmtDIJNtbS4be7GQ=\"},\"indexInfo\":{\"baseKey\":\"BWvfrSijRRjw9polaJUyJtMNl70Y+sFEwmeesZ5K4Ix7\",\"baseKeyType\":1,\"closed\":-1,\"used\":1792741574246,\"created\":1792741574246,\"remoteIdentityKey\":\"BahUo/aRBOkHKJ/iYT0nNwMaFGINzclTZMHtqYbg7Wts\"},\"_chains\":{\"BcWkGxNX5t1Z2s5oId900Rti0d50MVgHedIMngr/22JS\":{\"chainKey\":{\"counter\":3,\"key\":\"koOKwcKMo9P/isEqUoeRppBhuPc+vLJ0PdUg9YniwAs=\"},\"chainType\":1,\"messageKeys\":{}}},\"pendingPreKey\":{\"signedKeyId\":1,\"baseKey\":\"BWvfrSijRRjw9polaJUyJtMNl70Y+sFEwmeesZ5K4Ix7\",\"preKeyId\":711}}},\"version\":\"v1\"}', '2026-10-23 07:46:14', '2026-10-23 11:29:22'),
('session-79418290610347.0', '{\"_sessions\":{\"BXsJ9XMhb+rmsgTvQ5TG3sisc+dqvGTPXbOupk2LoHQ0\":{\"registrationId\":2082144015,\"currentRatchet\":{\"ephemeralKeyPair\":{\"pubKey\":\"BRmtxbR7GBF/1B9MxbE5dy3cUeXgybhQG2gZuD3AeQxB\",\"privKey\":\"+JEB0HdlLLuqNhVvw/wGLUp67QqQoPkbKyePXPDQxGM=\"},\"lastRemoteEphemeralKey\":\"BYtNsjvsUxOlHePervNNiEtP+kyJl5tHSi5PKl2fIUMn\",\"previousCounter\":0,\"rootKey\":\"enFXOVEXsyHhx6UPGiBpIRMeD+kpfNGLeibYx/U2PbI=\"},\"indexInfo\":{\"baseKey\":\"BXsJ9XMhb+rmsgTvQ5TG3sisc+dqvGTPXbOupk2LoHQ0\",\"baseKeyType\":2,\"closed\":-1,\"used\":1792738843013,\"created\":1792738843013,\"remoteIdentityKey\":\"Bf9o8LKI1YMEy7pR68J+/hhb4RAQliKb0XXWPGxhpGVC\"},\"_chains\":{\"BYtNsjvsUxOlHePervNNiEtP+kyJl5tHSi5PKl2fIUMn\":{\"chainKey\":{\"counter\":0,\"key\":\"ECb/aeweCDmw9TG/ko+c64PG+Nix1cv/ticTablHnFQ=\"},\"chainType\":2,\"messageKeys\":{}},\"BRmtxbR7GBF/1B9MxbE5dy3cUeXgybhQG2gZuD3AeQxB\":{\"chainKey\":{\"counter\":-1,\"key\":\"pzBrmqvMV8FJ8X+KCWeQZMgpaQxbg7tsDRpyMQ3mGSw=\"},\"chainType\":1,\"messageKeys\":{}}}}},\"version\":\"v1\"}', '2026-10-23 07:00:43', '2026-10-23 07:00:43');
/*!40000 ALTER TABLE `whatsapp_sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wishlists`
--
DROP TABLE IF EXISTS `wishlists`;
CREATE TABLE `wishlists` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `barang_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `wishlists_user_id_foreign` (`user_id`),
  KEY `wishlists_barang_id_foreign` (`barang_id`),
  CONSTRAINT `wishlists_barang_id_foreign` FOREIGN KEY (`barang_id`) REFERENCES `barang` (`id`) ON DELETE CASCADE,
  CONSTRAINT `wishlists_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `wishlists`
--
LOCK TABLES `wishlists` WRITE;
/*!40000 ALTER TABLE `wishlists` DISABLE KEYS */;
INSERT INTO `wishlists` (`id`, `user_id`, `barang_id`, `created_at`, `updated_at`) VALUES
(12, 17, 10, '2026-10-03 13:26:18', '2026-10-03 13:26:18'),
(13, 21, 11, '2026-10-21 15:51:09', '2026-10-21 15:51:09');
/*!40000 ALTER TABLE `wishlists` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `withdrawals`
--
DROP TABLE IF EXISTS `withdrawals`;
CREATE TABLE `withdrawals` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `vendor_id` bigint unsigned NOT NULL,
  `nominal` decimal(15,2) NOT NULL,
  `metode` enum('Bank','E-Wallet') COLLATE utf8mb4_unicode_ci NOT NULL,
  `nama_bank_ewallet` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nomor_rekening` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nama_pemilik` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('pending','disetujui','ditolak') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `withdrawals_vendor_id_foreign` (`vendor_id`),
  CONSTRAINT `withdrawals_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `withdrawals`
--
LOCK TABLES `withdrawals` WRITE;
/*!40000 ALTER TABLE `withdrawals` DISABLE KEYS */;
/*!40000 ALTER TABLE `withdrawals` ENABLE KEYS */;
UNLOCK TABLES;

SET FOREIGN_KEY_CHECKS=1;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
