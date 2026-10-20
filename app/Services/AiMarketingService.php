<?php

namespace App\Services;

use App\Models\AiMarketingPost;
use App\Models\Barang;
use App\Models\DetailPenyewaan;
use App\Models\Penyewaan;
use App\Models\Voucher;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;

class AiMarketingService
{
    protected ?string $apiKey;
    protected string $model;

    public function __construct()
    {
        $this->apiKey = config('rentify_ai.gemini.api_key') ?: (config('services.gemini.api_key') ?: env('GEMINI_API_KEY'));
        $this->model = config('rentify_ai.gemini.model') ?: (config('services.gemini.model') ?: env('GEMINI_MODEL', 'gemini-1.5-flash'));
    }

    /**
     * Cek apakah API Key Gemini telah dikonfigurasi
     */
    public function isAiEnabled(): bool
    {
        return !empty($this->apiKey);
    }

    /**
     * Panggil Google Gemini API
     */
    protected function callGemini(string $prompt, float $temperature = 0.7): ?string
    {
        if (empty($this->apiKey)) {
            return null;
        }

        try {
            $url = "https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent?key={$this->apiKey}";
            
            $response = Http::timeout(15)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                ])
                ->post($url, [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt]
                            ]
                        ]
                    ],
                    'generationConfig' => [
                        'temperature' => $temperature,
                        'maxOutputTokens' => 1500,
                    ]
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;
                return $text ? trim($text) : null;
            } else {
                Log::warning('Gemini API request failed: ' . $response->body());
                return null;
            }
        } catch (\Throwable $e) {
            Log::error('Gemini API Exception: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Generate Copywriting Iklan & Promosi Produk Rental
     */
    public function generateCopywriting(array $productData, string $platform = 'all', string $tone = 'fomo', ?string $customNote = null): array
    {
        $namaProduk = $productData['nama'] ?? 'Produk Rental';
        $kategori   = $productData['kategori'] ?? 'Umum';
        $hargaSewa  = isset($productData['harga_sewa']) ? (float)$productData['harga_sewa'] : 0;
        $formattedHarga = 'Rp ' . number_format($hargaSewa, 0, ',', '.');
        $deskripsi  = $productData['deskripsi'] ?? '';
        $diskon     = $productData['diskon'] ?? '';

        // Coba generate via Gemini jika API Key tersedia
        if ($this->isAiEnabled()) {
            $prompt = <<<PROMPT
Anda adalah AI Marketing Copywriter profesional untuk platform persewaan online 'Rentify'.
Buat copywriting promosi yang persuasif, menarik, dan siap dipublikasikan untuk barang rental berikut:
- Nama Produk: {$namaProduk}
- Kategori: {$kategori}
- Tarif Sewa: {$formattedHarga} / hari
- Deskripsi Singkat: {$deskripsi}
- Promo / Diskon Tambahan: {$diskon}
- Gaya Bahasa / Tone: {$tone} (pilihan: fomo = urgensi heboh, casual = santai & akrab, professional = terpercaya, solution = solusi hemat)
- Catatan Khusus: {$customNote}

Format balasan WAJIB dalam format JSON murni (tanpa pembuka/penutup markdown selain json) dengan key berikut:
{
  "whatsapp": "Teks pesan broadcast WhatsApp lengkap dengan emoji dan formatting bold *...*, call-to-action ke link rental",
  "instagram": "Caption Instagram menarik, dilengkapi hook awal, penjelasan fitur, ajakan booking, dan 8-12 hashtag relevan",
  "tiktok": "Konsep hook video 3 detik pertama + naskah singkat narasi TikTok/Reels untuk meyakinkan audiens agar menyewa daripada membeli",
  "banner": "Kalimat punchline pendek untuk banner promosi (Maks 10 kata) dan Sub-headline (Maks 15 kata)",
  "highlight_benefits": ["Poin keunggulan 1", "Poin keunggulan 2", "Poin keunggulan 3"]
}
PROMPT;

            $aiResponse = $this->callGemini($prompt);
            if ($aiResponse) {
                // Bersihkan kemungkinan markdown ```json ... ```
                $cleanJson = (string) preg_replace('/^```(?:json)?\s*|\s*```$/m', '', trim($aiResponse));
                $decoded = json_decode($cleanJson, true);
                if (is_array($decoded) && isset($decoded['whatsapp'], $decoded['instagram'])) {
                    $decoded['ai_source'] = 'Gemini AI (' . $this->model . ')';
                    return $decoded;
                }
            }
        }

        // Fallback ke Smart Heuristic Generator (tetap aktif jika tanpa API Key)
        return $this->generateFallbackCopywriting($namaProduk, $kategori, $formattedHarga, $deskripsi, $tone, $diskon, $customNote);
    }

    /**
     * Fallback Copywriting Berbasis Template Cerdas & Heuristik Bahasa Indonesia
     */
    protected function generateFallbackCopywriting(string $nama, string $kategori, string $harga, string $deskripsi, string $tone, string $diskon, ?string $customNote): array
    {
        $diskonText = $diskon ? "🔥 *SPESIAL PROMO:* {$diskon}!" : "✨ *HARGA TERBAIK:* Hanya {$harga}/hari!";
        
        switch ($tone) {
            case 'fomo':
                $waHook = "🚨 *SLOT TERBATAS! JANGAN SAMPAI KEHABISAN!* 🚨";
                $igHook = "⚠️ MAU PAKAI {$nama} TAPI MALAS BELI MAHAL? SEWA AJA DI RENTIFY! ⚠️";
                $ttHook = "Stop beli barang mahal yang cuma dipakai sekali! Tonton ini dulu!";
                $bannerTitle = "Sewa {$nama} Hemat Sekarang!";
                $bannerSub = "Unit terbatas, booking sebelum kehabisan slot minggu ini!";
                break;
            case 'casual':
                $waHook = "Halo guys! Mau agendain aktivitas seru tanpa bikin kantong bolong? 😉";
                $igHook = "Solusi anti boncos buat kamu yang butuh {$nama}! 🙌";
                $ttHook = "POV: Lu butuh {$nama} buat akhir pekan ini tapi budget mepet...";
                $bannerTitle = "Liburan & Acara Makin Seru";
                $bannerSub = "Sewa {$nama} gampang, siap pakai kapan aja!";
                break;
            case 'professional':
                $waHook = "Salam hangat dari Rentify. Tingkatkan produktivitas & kegiatan Anda dengan perlengkapan terbaik.";
                $igHook = "Kualitas terjamin & siap pakai: {$nama} kini tersedia untuk disewa.";
                $ttHook = "3 Alasan kenapa para profesional lebih memilih sewa {$nama} di Rentify.";
                $bannerTitle = "Rental Resmi {$nama}";
                $bannerSub = "Unit terawat, jaminan kualitas prima untuk kelancaran agenda Anda.";
                break;
            case 'solution':
            default:
                $waHook = "💡 *SOLUSI PINTAR:* Ngapain beli jutaan kalau bisa sewa hemat?";
                $igHook = "Kenapa harus beli kalau sewa {$nama} di Rentify jauh lebih praktis? 💡";
                $ttHook = "Kalkulasi cerdas: Beli vs Sewa {$nama}. Hasilnya bikin kaget!";
                $bannerTitle = "Hemat Cerdas Bersama Rentify";
                $bannerSub = "Nikmati fasilitas {$nama} premium mulai dari {$harga}/hari.";
                break;
        }

        // Tag kategori
        $katTag = '#' . Str::studly(Str::slug($kategori, ''));
        $prodTag = '#' . Str::studly(Str::slug($nama, ''));

        $whatsapp = "{$waHook}\n\n" .
                    "Lagi butuh *{$nama}* untuk kebutuhan Anda? Di Rentify, Anda bisa sewa dengan mudah, cepat, dan terpercaya!\n\n" .
                    "{$diskonText}\n" .
                    "📦 Tarif sewa hemat: *{$harga}* / hari\n" .
                    "✅ Kondisi unit bersih, prima, dan siap langsung digunakan.\n" .
                    ($customNote ? "📌 *Catatan:* {$customNote}\n" : "") .
                    "\n👉 *Amankan slot booking Anda sekarang:* Klik link toko kami di Rentify sebelum jadwal penuh!";

        $instagram = "{$igHook}\n\n" .
                     "Nggak perlu keluar budget besar untuk beli baru kalau cuma dipakai beberapa kali. Sewa *{$nama}* di Rentify jadi solusi paling hemat & anti ribet!\n\n" .
                     "💎 Tarif sewa bersahabat: *{$harga}/hari*\n" .
                     "👌 Unit terawat, bersih, dan melalui pengecekan berkala.\n" .
                     ($diskon ? "🎁 Promo aktif: {$diskon}\n" : "") .
                     "\nTap link di bio untuk langsung pesan & cek ketersediaan tanggal! 📲\n\n" .
                     "{$prodTag} {$katTag} #RentalHemat #RentifyIndonesia #SewaBarang #RentalMudah #SolusiHemat #SewaAja";

        $tiktok = "🎬 *Konsep Video:*\n" .
                  "1. [Detik 0-3] *Hook:* {$ttHook}\n" .
                  "2. [Detik 3-10] *Visual:* Tampilkan close-up {$nama} yang mulus dan siap pakai.\n" .
                  "3. [Detik 10-20] *Narasi:* Ceritakan kemudahan sewa di Rentify tanpa perlu deposit memberatkan, harga cuma {$harga}/hari.\n" .
                  "4. [Detik 20-30] *Call To Action:* 'Klik keranjang / link di profil buat sewa sekarang!'";

        return [
            'whatsapp' => $whatsapp,
            'instagram' => $instagram,
            'tiktok' => $tiktok,
            'banner' => [
                'headline' => $bannerTitle,
                'subheadline' => $bannerSub,
            ],
            'highlight_benefits' => [
                "Hemat biaya hingga 90% dibanding beli unit baru",
                "Unit bersih & terawat siap langsung dipakai",
                "Proses sewa & pengembalian mudah via Rentify",
            ],
            'ai_source' => 'Rentify Smart Heuristic Copywriter'
        ];
    }

    /**
     * Audit Performa Produk Vendor & Berikan Rekomendasi Auto-Promosi Cerdas
     */
    public function auditAndRecommendPromos(int $vendorId): array
    {
        $products = Barang::where('vendor_id', $vendorId)
            ->with(['kategori'])
            ->get();

        $activeVouchersCount = Voucher::where('vendor_id', $vendorId)
            ->where('is_active', 1)
            ->count();

        $recommendations = [];
        $stagnantProducts = [];
        $popularProducts = [];

        $now = Carbon::now();

        foreach ($products as $prod) {
            // Hitung frekuensi sewa melalui order_items
            $rentalCount = DetailPenyewaan::where('product_id', $prod->id)
                ->whereHas('penyewaan', function ($q) {
                    $q->whereIn('status', ['selesai', 'aktif', 'disetujui', 'dikirim', 'sedang_disewa']);
                })
                ->count();

            // Tanggal sewa terakhir
            $lastRental = DetailPenyewaan::where('product_id', $prod->id)
                ->latest()
                ->first();

            $daysSinceLastRental = $lastRental ? $now->diffInDays($lastRental->created_at) : 999;
            $daysSinceCreated = $now->diffInDays($prod->created_at);

            $stok = (int)$prod->stok_total;
            $harga = (float)$prod->harga_sewa_harian;

            $statusPerformance = 'normal';
            if ($rentalCount === 0 || ($daysSinceLastRental > 25 && $stok > 0)) {
                $statusPerformance = 'stagnant'; // Barang sepi / stok tertidur
                $stagnantProducts[] = $prod;
            } elseif ($rentalCount >= 5) {
                $statusPerformance = 'popular'; // Produk laris
                $popularProducts[] = $prod;
            }

            // Generate Rekomendasi Auto-Promosi jika barang butuh dorongan promosi
            if ($statusPerformance === 'stagnant' && $stok > 0) {
                // Tentukan rekomendasi diskon optimal
                $suggestedPersen = $harga > 200000 ? 15 : 20;
                $suggestedNominal = round(($harga * ($suggestedPersen / 100)), -3);
                $cleanSlug = strtoupper(substr((string) preg_replace('/[^A-Za-z0-9]/', '', (string)$prod->nama), 0, 6));
                $suggestedCode = 'HEMAT' . $cleanSlug . rand(10, 99);

                $recommendations[] = [
                    'barang_id'          => $prod->id,
                    'nama_barang'        => $prod->nama,
                    'kategori'           => $prod->kategori->nama ?? 'Umum',
                    'harga_sewa'         => $harga,
                    'stok'               => $stok,
                    'rental_count'       => $rentalCount,
                    'alasan_urgensi'     => $rentalCount === 0 
                        ? "Belum pernah disewa sejak diupload {$daysSinceCreated} hari lalu. Perlu diskon perkenalan!" 
                        : "Tidak ada pesanan baru dalam {$daysSinceLastRental} hari terakhir padahal stok tersedia {$stok} unit.",
                    'strategi_rekomendasi' => 'Flash Sale Diskon Toko',
                    'tipe_diskon'        => 'persen',
                    'nilai_diskon'       => $suggestedPersen,
                    'potongan_nominal'   => $suggestedNominal,
                    'minimal_belanja'    => $harga, // Min sewa 1 hari
                    'maksimal_diskon'    => $suggestedNominal * 2,
                    'kode_voucher'       => $suggestedCode,
                    'durasi_hari'        => 7,
                    'cta_punchline'      => "Gunakan voucher {$suggestedCode} untuk diskon {$suggestedPersen}% sewa {$prod->nama}!"
                ];
            }
        }

        return [
            'summary' => [
                'total_produk'           => $products->count(),
                'total_stagnant'         => count($stagnantProducts),
                'total_popular'          => count($popularProducts),
                'active_vouchers_count'  => $activeVouchersCount,
                'health_score'           => $products->count() > 0 
                    ? round(((count($popularProducts) + ($products->count() - count($stagnantProducts))) / ($products->count() * 1.5)) * 100) 
                    : 100,
            ],
            'recommendations' => array_slice($recommendations, 0, 6), // Top 6 rekomendasi utama
            'stagnant_count'  => count($stagnantProducts),
        ];
    }

    /**
     * Generate Kampanye Promosi Platform untuk Admin
     */
    public function generateAdminCampaign(string $theme, ?string $targetAudience = null, ?string $discountSuggestion = null): array
    {
        if ($this->isAiEnabled()) {
            $prompt = <<<PROMPT
Anda adalah AI Chief Marketing Officer untuk platform sewa-menyewa online 'Rentify'.
Buat konsep kampanye promosi platform terpadu untuk event: "{$theme}".
- Target Audiens: {$targetAudience}
- Usulan Diskon: {$discountSuggestion}

Format balasan WAJIB JSON murni dengan format:
{
  "judul_kampanye": "Nama event promo yang catchy",
  "slogan": "Slogan pendek yang berkesan",
  "banner_promo": {
     "judul_banner": "Judul singkat untuk banner utama aplikasi (Maks 7 kata)",
     "deskripsi_banner": "Sub-teks banner promosi (Maks 15 kata)"
  },
  "broadcast_notifikasi": "Pesan broadcast singkat untuk push notification / WhatsApp / Email ke customer",
  "strategi_eksekusi": [
     "Langkah 1",
     "Langkah 2",
     "Langkah 3"
  ]
}
PROMPT;

            $aiResponse = $this->callGemini($prompt);
            if ($aiResponse) {
                $cleanJson = (string) preg_replace('/^```(?:json)?\s*|\s*```$/m', '', trim($aiResponse));
                $decoded = json_decode($cleanJson, true);
                if (is_array($decoded) && isset($decoded['judul_kampanye'])) {
                    $decoded['ai_source'] = 'Gemini AI (' . $this->model . ')';
                    return $decoded;
                }
            }
        }

        // Fallback Heuristik
        return [
            'judul_kampanye' => 'Festival Sewa Seru Rentify: ' . $theme,
            'slogan' => 'Sewa Kebutuhan Apapun, Lebih Hemat & Praktis di Rentify!',
            'banner_promo' => [
                'judul_banner' => 'Mega Promo ' . $theme . ' Diskon Hingga 30%',
                'deskripsi_banner' => 'Nikmati tarif sewa terhemat minggu ini untuk semua kategori produk favoritmu!',
            ],
            'broadcast_notifikasi' => "🎉 Spesial {$theme}! Dapatkan diskon sewa istimewa untuk berbagai perlengkapan di Rentify. Pesan sekarang sebelum periode promo berakhir!",
            'strategi_eksekusi' => [
                'Pasang Banner Utama di Halaman Depan Aplikasi',
                'Kirim Notifikasi Broadcast ke Customer Aktif',
                'Ajak Mitra Vendor Menawarkan Voucher Diskon Tambahan'
            ],
            'ai_source' => 'Rentify Smart Heuristic Marketing'
        ];
    }

    /**
     * Generate Konten Video Reels/TikTok Harian (Sistem Rotasi 50:50 Customer vs Vendor)
     *
     * Aturan:
     * - Hari Ganjil (Odd Day): Target Customer (Penyewa) -> Edukasi sewa hemat daripada beli mahal
     * - Hari Genap (Even Day): Target Vendor (Pemilik Barang) -> Ajak monetisasi barang nganggur
     */
    public function generateDailyVideoContent(?Carbon $date = null, ?string $forceTarget = null): AiMarketingPost
    {
        $date = $date ? $date->copy() : Carbon::now();
        $dayNumber = (int) $date->format('j');
        $isOdd = ($dayNumber % 2 !== 0);
        $dayType = $isOdd ? 'ganjil' : 'genap';

        // Tentukan Target Audiens (50:50)
        if ($forceTarget && in_array($forceTarget, ['customer', 'vendor'])) {
            $targetAudience = $forceTarget;
        } else {
            $targetAudience = $isOdd ? 'customer' : 'vendor';
        }

        $brandName = config('rentify_ai.brand.name', 'Rentify');
        $webUrl = config('rentify_ai.brand.website_url', env('APP_URL', 'http://localhost:8000'));

        $generatedData = null;
        $aiSource = 'Smart Heuristic Engine';

        if ($targetAudience === 'customer') {
            // TARGET CUSTOMER (PENYEWA): Edukasi sewa barang sesekali/jarang dipakai
            $categories = [
                'Alat Camping & Outdoor' => 'Tenda doom, matras, kompor portable, nesting, carrier',
                'Fotografi & Kamera Acara' => 'Kamera mirrorless, lensa tele, gimbal stabilizer, mic wireless',
                'Pertukangan & Alat Rumah' => 'Bor listrik impact, tangga lipat teleskopik, mesin las mini, jet cleaner',
                'Perlengkapan Bayi & Anak' => 'Stroller travelling, baby car seat, sterilizer botol, mainan playhouse',
                'Alat Pesta & Acara' => 'Sound system portable, wireless mic, proyektor seminar, layar tripod',
                'Otomotif & Motor Harian' => 'Motor matic harian, helm touring, action cam',
                'Gaming & Hiburan' => 'PlayStation 5, Nintendo Switch, VR headset',
                'Musik & Band' => 'Gitar akustik elektrik, keyboard piano, amplifier portable',
            ];
            $catKeys = array_keys($categories);
            $selectedCategory = $catKeys[$dayNumber % count($catKeys)];
            $sampleItems = $categories[$selectedCategory];

            if ($this->isAiEnabled()) {
                $prompt = <<<PROMPT
Anda adalah Creative Director & Viral Marketing Strategist untuk '{$brandName}' ({$webUrl}).
Tugas Anda hari ini adalah membuat ide konten video pendek vertikal (Instagram Reels & TikTok, format 9:16) yang SANGAT RELATE dengan kehidupan sehari-hari orang Indonesia.

TUJUAN HARI INI: MENARIK CUSTOMER (PENYEWA).
Edukasi audiens yang butuh barang untuk keperluan sesekali/jarang dipakai (khususnya kategori: {$selectedCategory}, seperti {$sampleItems}) agar TIDAK PERLU BELI MAHAL. Cukup sewa murah, cepat, dan dekat lokasi mereka lewat web {$brandName}.

Kriteria Wajib:
1. Hook masalah 3 detik pertama harus menghentikan scrolling, menyentil pengeluaran boros atau dilema beli vs sewa (contoh: "Mau acara sehari masa harus beli baru?").
2. Naskah video pendek 15-30 detik: Terbagi rapi ke 4 adegan (Detik 0-3: Hook masalah relate, Detik 3-10: Pemborosan beli vs numpuk di gudang, Detik 10-20: Solusi mudah sewa di Rentify tanpa deposit berat, Detik 20-30: Call To Action ke web Rentify).
3. Teks di layar (On-screen text) yang eye-catching untuk video vertikal 9:16.
4. Caption persuasif yang mengajak penonton langsung buka web {$brandName} lengkap dengan 10+ hashtag viral relevan.
5. Visual Storyboard arahan warna, latar, dan ekspresi aktor format 9:16.

Balas WAJIB dalam JSON murni tanpa pembuka/penutup markdown lain:
{
  "tema": "Judul Konsep Video",
  "kategori_barang": "{$selectedCategory}",
  "hook_masalah": "Kalimat pembuka 3 detik pertama yang menusuk masalah audiens",
  "naskah_video": [
    {"detik": "0-3s", "adegan": "Hook Masalah", "visual": "Deskripsi visual vertikal 9:16", "voiceover": "Teks naskah yang diucapkan"},
    {"detik": "3-10s", "adegan": "Dilema Beli vs Numpuk", "visual": "Deskripsi visual ekspresi bingung/rugi beli", "voiceover": "Teks naskah narasi"},
    {"detik": "10-20s", "adegan": "Solusi Sewa di Rentify", "visual": "Tampilan aplikasi Rentify dan unit barang siap pakai", "voiceover": "Teks naskah solusi hemat"},
    {"detik": "20-30s", "adegan": "Call To Action", "visual": "Tampilan ajakan buka web Rentify", "voiceover": "Ajakan cek ketersediaan sekarang"}
  ],
  "teks_di_layar": [
    {"posisi": "Tengah Atas", "teks": "Teks hook besar di layar", "style": "Teks kuning/merah tebal"},
    {"posisi": "Bawah", "teks": "Perbandingan Biaya Beli vs Sewa", "style": "Badge kontras"},
    {"posisi": "Tengah", "teks": "Sewa di Rentify Mulai Rp XX.XXX/Hari", "style": "Highlight hijau cerah"},
    {"posisi": "Bawah", "teks": "Cek Katalog di rentify.id Sekarang!", "style": "Pill button CTA"}
  ],
  "caption": "Teks caption lengkap dengan hook, penjelasan solusi sewa, ajakan buka web Rentify, dan hashtag",
  "visual_prompt": "Arahan visual komposisi vertikal 9:16, warna tema utama, dan rekomendasi musik latar trending"
}
PROMPT;

                $aiRaw = $this->callGemini($prompt);
                if ($aiRaw) {
                    $cleanJson = (string) preg_replace('/^```(?:json)?\s*|\s*```$/m', '', trim($aiRaw));
                    $decoded = json_decode($cleanJson, true);
                    if (is_array($decoded) && isset($decoded['hook_masalah'], $decoded['naskah_video'])) {
                        $generatedData = $decoded;
                        $aiSource = 'Google Gemini (' . $this->model . ')';
                    }
                }
            }

            if (!$generatedData) {
                $generatedData = $this->getFallbackCustomerReels($selectedCategory, $sampleItems, $brandName, $webUrl);
            }

        } else {
            // TARGET VENDOR (PEMILIK BARANG / TOKO RENTAL): Ajak monetisasi barang nganggur
            $personas = [
                'Mahasiswa' => 'Punya laptop spek gaming, kamera mirrorless, Nintendo Switch, gitar elektrik di kosan yang nganggur pas kuliah.',
                'Ibu Rumah Tangga' => 'Punya stroller bayi mahal, sterilizer, mainan playhouse anak, atau set alat pesta yang disimpan di gudang.',
                'Bapak-bapak / Pekerja' => 'Punya bor impact, tangga teleskopik, tenda camping keluarga, sepeda gowes yang nganggur di garasi.',
                'Pemilik Usaha Rental Lokal' => 'Pemilik rental offline kamera/outdoor/audio yang ingin memperluas jangkauan ke ribuan customer online tanpa sewa toko baru.',
            ];
            $personaKeys = array_keys($personas);
            $selectedPersona = $personaKeys[$dayNumber % count($personaKeys)];
            $personaContext = $personas[$selectedPersona];

            if ($this->isAiEnabled()) {
                $prompt = <<<PROMPT
Anda adalah Chief Growth Officer & Social Media Strategist untuk platform '{$brandName}' ({$webUrl}).
Tugas Anda hari ini adalah membuat ide konten video pendek vertikal (Instagram Reels & TikTok, format 9:16) yang SANGAT PERSUASIF untuk MENGAJAK ORANG JADI VENDOR / MENYEWAKAN BARANGNYA DI RENTIFY.

TUJUAN HARI INI: MENARIK VENDOR (PEMILIK BARANG & TOKO RENTAL).
Target Audiens Hari Ini: {$selectedPersona} ({$personaContext}).
Edukasi audiens bahwa barang nganggur di rumah/gudang jangan cuma dibiarkan berdebu atau turun nilai harganya. Lebih baik disewakan di Rentify untuk jadi sumber cuan pasif (passive income) ratusan ribu hingga jutaan rupiah per bulan!

Kriteria Wajib:
1. Hook masalah 3 detik pertama: Menyentil barang nganggur berdebu dan potensi cuan yang hilang (contoh: "Kamera atau PS kamu nganggur di lemari? Sayang banget, harusnya bisa ngehasilin 2 juta per bulan!").
2. Naskah video pendek 15-30 detik: Terbagi 4 adegan (Detik 0-3: Hook barang nganggur, Detik 3-10: Hitung-hitungan potensi cuan pasif vs barang rusak karena gak dipakai, Detik 10-20: Kemudahan upload barang di Rentify & keamanan sistem, Detik 20-30: CTA ajak daftar vendor sekarang).
3. Teks di layar vertikal 9:16.
4. Caption persuasif mengajak daftar jadi vendor di web {$brandName} + hashtag relevan.
5. Visual Storyboard format 9:16.

Balas WAJIB dalam JSON murni:
{
  "tema": "Judul Konsep Video",
  "persona_vendor": "{$selectedPersona}",
  "hook_masalah": "Kalimat pembuka 3 detik pertama",
  "naskah_video": [
    {"detik": "0-3s", "adegan": "Hook Barang Berdebu", "visual": "Deskripsi visual vertikal 9:16", "voiceover": "Teks naskah narasi"},
    {"detik": "3-10s", "adegan": "Potensi Cuan Nganggur", "visual": "Deskripsi visual kalkulasi cuan", "voiceover": "Teks narasi perbandingan"},
    {"detik": "10-20s", "adegan": "Solusi Jadi Vendor di Rentify", "visual": "Tampilan dashboard vendor Rentify & transaksi masuk", "voiceover": "Kemudahan & keamanan sistem"},
    {"detik": "20-30s", "adegan": "Call To Action Vendor", "visual": "Ajakan buka web Rentify dan klik Mulai Sewakan", "voiceover": "Ajakan daftar sekarang"}
  ],
  "teks_di_layar": [
    {"posisi": "Tengah Atas", "teks": "Teks hook besar di layar", "style": "Teks oranye/kuning tebal"},
    {"posisi": "Bawah", "teks": "Daripada Nganggur Jadi Cuan Rp 1,5 Juta/Bulan", "style": "Badge cuan"},
    {"posisi": "Tengah", "teks": "Daftar Vendor Gratis Tanpa Modal Toko", "style": "Highlight hijau cerah"},
    {"posisi": "Bawah", "teks": "Buka Web rentify.id & Sewakan Barangmu!", "style": "Pill button CTA"}
  ],
  "caption": "Teks caption lengkap dengan hook ajak jadi vendor, edukasi cuan pasif, langkah daftar di web Rentify, dan hashtag",
  "visual_prompt": "Arahan visual komposisi vertikal 9:16, warna tema utama, dan rekomendasi audio"
}
PROMPT;

                $aiRaw = $this->callGemini($prompt);
                if ($aiRaw) {
                    $cleanJson = (string) preg_replace('/^```(?:json)?\s*|\s*```$/m', '', trim($aiRaw));
                    $decoded = json_decode($cleanJson, true);
                    if (is_array($decoded) && isset($decoded['hook_masalah'], $decoded['naskah_video'])) {
                        $generatedData = $decoded;
                        $aiSource = 'Google Gemini (' . $this->model . ')';
                    }
                }
            }

            if (!$generatedData) {
                $generatedData = $this->getFallbackVendorReels($selectedPersona, $personaContext, $brandName, $webUrl);
            }
        }

        // Simpan ke database ai_marketing_posts
        return AiMarketingPost::create([
            'tanggal'          => $date->toDateString(),
            'day_number'       => $dayNumber,
            'day_type'         => $dayType,
            'target_audience'  => $targetAudience,
            'tema'             => $generatedData['tema'] ?? ('Konten Harian Rentify - ' . ucfirst($targetAudience)),
            'kategori_barang'  => $generatedData['kategori_barang'] ?? null,
            'persona_vendor'   => $generatedData['persona_vendor'] ?? null,
            'hook_masalah'     => $generatedData['hook_masalah'],
            'naskah_video'     => $generatedData['naskah_video'],
            'teks_di_layar'    => $generatedData['teks_di_layar'],
            'caption'          => $generatedData['caption'],
            'visual_prompt'    => $generatedData['visual_prompt'] ?? 'Komposisi vertikal 9:16 bergaya modern reels',
            'ai_source'        => $aiSource,
            'status'           => 'ready',
        ]);
    }

    /**
     * Fallback Heuristik Cerdas untuk Konten Customer (Hari Ganjil)
     */
    protected function getFallbackCustomerReels(string $kategori, string $sampleItems, string $brandName, string $webUrl): array
    {
        $hooks = [
            'Alat Camping & Outdoor' => 'Mau camping sekali setahun, masa harus beli tenda 2 juta?',
            'Fotografi & Kamera Acara' => 'Ada acara wisuda & foto prewed sehari, masa beli kamera 20 juta?',
            'Pertukangan & Alat Rumah' => 'Mau renovasi ambalan rumah hari minggu, masa harus beli bor impact 800 ribu?',
            'Perlengkapan Bayi & Anak' => 'Anak cepet gede, beli stroller jutaan cuma kepake 4 bulan terus masuk kardus?',
            'Alat Pesta & Acara' => 'Mau bikin nobar & hajatan sehari, masa harus beli sound system dan proyektor baru?',
            'Gaming & Hiburan' => 'Mau seru-seruan main PS5 bareng temen weekend ini tapi belum punya konsolnya?',
            'Musik & Band' => 'Mau tampil akustikan akhir pekan ini tapi belum punya gitar bagus?',
            'Otomotif & Motor Harian' => 'Motor lagi diservis atau butuh kendaraan harian darurat tanpa ribet kredit?',
        ];

        $hook = $hooks[$kategori] ?? "Mau pakai {$kategori} sebentar, masa harus beli mahal-mahal?";

        return [
            'tema' => "Solusi Cerdas: Sewa {$kategori} Tanpa Perlu Beli Mahal!",
            'kategori_barang' => $kategori,
            'hook_masalah' => $hook,
            'naskah_video' => [
                [
                    'detik' => '0-3s',
                    'adegan' => 'Hook Masalah (Shocking Question)',
                    'visual' => "Close-up aktor menatap kamera dengan ekspresi bingung memegang struk harga atau melihat barang mahal online.",
                    'voiceover' => "{$hook} Jangan buang uang jutaan cuma buat barang yang dipakai sesekali!"
                ],
                [
                    'detik' => '3-10s',
                    'adegan' => 'Dilema Beli vs Numpuk di Gudang',
                    'visual' => "Potongan video barang menumpuk berdebu di pojok kamar atau gudang, diiringi teks kalkulasi harga beli vs jarang dipakai.",
                    'voiceover' => "Udah beli mahal, dipakai cuma 2 kali, ujung-ujungnya cuma berdebu dan menuh-menuhin gudang rumah."
                ],
                [
                    'detik' => '10-20s',
                    'adegan' => 'Solusi Sewa di Rentify',
                    'visual' => "Aktor tersenyum scroll web Rentify di smartphone, menampilkan katalog unit terdekat dengan harga sewa murah meriah.",
                    'voiceover' => "Untung sekarang ada {$brandName}! Kamu bisa sewa {$kategori} terbaik dengan harga mulai puluhan ribu aja per hari. Unit bersih, terawat, dan siap pakai!"
                ],
                [
                    'detik' => '20-30s',
                    'adegan' => 'Call To Action',
                    'visual' => "Tampilan halaman depan web {$brandName} di layar smartphone dengan tombol 'Sewa Sekarang' yang diklik.",
                    'voiceover' => "Ngapain beli mahal kalau bisa sewa hemat? Cek katalog terdekat dan booking sekarang juga di web {$brandName}!"
                ],
            ],
            'teks_di_layar' => [
                ['posisi' => 'Tengah Atas', 'teks' => $hook, 'style' => 'Teks merah tebal font bold sans-serif'],
                ['posisi' => 'Tengah', 'teks' => 'Beli: Rp 2.500.000 vs Sewa: Rp 45.000', 'style' => 'Kotak komparasi kontras'],
                ['posisi' => 'Bawah', 'teks' => 'Sewa Mudah & Dekat di Rentify', 'style' => 'Badge biru langit cerah'],
                ['posisi' => 'Bawah', 'teks' => '👉 Buka ' . parse_url($webUrl, PHP_URL_HOST) . ' Sekarang!', 'style' => 'Tombol pill CTA bergerak'],
            ],
            'caption' => "{$hook} 😱\n\n" .
                         "Banyak orang yang kejebak beli barang jutaan padahal cuma dipakai 1-2 kali setahun. Akhirnya? Menumpuk di gudang dan rusak dimakan waktu!\n\n" .
                         "Stop boros! Mulai sekarang, kebutuhan sesekali seperti {$kategori} ({$sampleItems}) cukup SEWA aja di Rentify. Tarif super hemat, barang berkualitas, dan langsung dekat dari lokasimu! ✨\n\n" .
                         "📲 Yuk cek katalog barang rental terdekat sekarang:\n" .
                         "🌐 {$webUrl}\n\n" .
                         "#Rentify #SewaAja #SolusiHemat #RentalBarang #SewaKamera #SewaCamping #SewaPerkakas #AntiBeliMahal #GayaHidupHemat #RentalDekatKamu",
            'visual_prompt' => "Format 9:16 vertikal. Visual terbagi dua bagian split-screen: atas menampilkan dilema beli mahal, bawah menampilkan solusi sewa praktis di Rentify dengan warna dominan Sky Blue & Emerald Green.",
        ];
    }

    /**
     * Fallback Heuristik Cerdas untuk Konten Vendor (Hari Genap)
     */
    protected function getFallbackVendorReels(string $persona, string $personaContext, string $brandName, string $webUrl): array
    {
        $hooks = [
            'Mahasiswa' => 'Punya kamera atau PS5 nganggur di kosan? Sayang banget, harusnya bisa hasilin 2 juta per bulan!',
            'Ibu Rumah Tangga' => 'Stroller anak & box bayi udah gak kepake dan menuhin kamar? Sewain di Rentify biar jadi uang belanja bulanan!',
            'Bapak-bapak / Pekerja' => 'Bor listrik, tangga lipat, atau tenda camping nganggur di garasi? Jadikan mesin cuan di Rentify!',
            'Pemilik Usaha Rental Lokal' => 'Punya rental kamera atau alat camping offline yang sepi penyewa? Gabung di Rentify dan raih ribuan pelanggan baru!',
        ];

        $hook = $hooks[$persona] ?? "Punya barang nganggur di rumah? Ubah jadi passive income jutaan rupiah di {$brandName}!";

        return [
            'tema' => "Ide Cuan {$persona}: Dari Barang Nganggur Jadi Passive Income Jutaan!",
            'persona_vendor' => $persona,
            'hook_masalah' => $hook,
            'naskah_video' => [
                [
                    'detik' => '0-3s',
                    'adegan' => 'Hook Barang Nganggur Berdebu',
                    'visual' => "Kamera menyorot barang-barang bagus di lemari/gudang yang tertutup debu, lalu aktor mengetuk-ngetuk barang itu dengan rasa gemas.",
                    'voiceover' => "{$hook} Tau gak kalau barang kamu ini banyak banget yang cari tiap hari?"
                ],
                [
                    'detik' => '3-10s',
                    'adegan' => 'Kalkulasi Potensi Cuan Pasif',
                    'visual' => "Grafis animasi uang mengalir masuk ke saldo smartphone saat barang disewa orang selama akhir pekan.",
                    'voiceover' => "Daripada didiemin harganya makin turun, mending disewakan! Sewa 2 hari tiap akhir pekan, sebulan bisa dapet 1 sampai 3 juta bersih!"
                ],
                [
                    'detik' => '10-20s',
                    'adegan' => 'Solusi Jadi Vendor di Rentify',
                    'visual' => "Tampilan mudah foto barang dan upload ke web Rentify via ponsel, langsung tayang ke calon penyewa terdekat.",
                    'voiceover' => "Di {$brandName}, daftar jadi vendor itu GRATIS! Gak perlu sewa ruko fisik. Ada sistem verifikasi aman, jaminan identitas, dan uang sewa langsung cair ke rekening kamu!"
                ],
                [
                    'detik' => '20-30s',
                    'adegan' => 'Call To Action Vendor',
                    'visual' => "Aktor menunjukkan ponsel dengan notifikasi 'Pesanan Sewa Masuk' dan mengacungkan jempol ke kamera.",
                    'voiceover' => "Mulai bisnis rentalmu sendiri hari ini tanpa modal! Buka web {$brandName} dan daftarkan barang nganggurmu sekarang!"
                ],
            ],
            'teks_di_layar' => [
                ['posisi' => 'Tengah Atas', 'teks' => $hook, 'style' => 'Teks oranye cerah font tebal'],
                ['posisi' => 'Tengah', 'teks' => 'Barang Nganggur = Cuan Rp 1.500.000+/Bulan', 'style' => 'Badge penghasilan hijau'],
                ['posisi' => 'Bawah', 'teks' => 'Daftar Vendor Gratis Tanpa Modal Ruko', 'style' => 'Badge putih border biru'],
                ['posisi' => 'Bawah', 'teks' => '🚀 Mulai Sewakan di rentify.id Sekarang!', 'style' => 'Pill CTA kontras'],
            ],
            'caption' => "{$hook} 💸✨\n\n" .
                         "Tau gak sih? Ribuan orang di sekitarmu lagi butuh barang yang kebetulan lagi nganggur di rumahmu!\n\n" .
                         "Daripada cuma menuh-menuhin ruangan dan berdebu, yuk daftarkan barangmu di {$brandName}. Mulai dari mahasiswa, ibu rumah tangga, sampai pemilik rental toko, semuanya bisa dapat penghasilan pasif rutin setiap bulan!\n\n" .
                         "✅ Gratis pendaftaran toko rental online\n" .
                         "✅ Sistem verifikasi penyewa aman\n" .
                         "✅ Uang sewa langsung masuk ke saldo & bisa dicairkan kapan saja\n\n" .
                         "🔗 Mulai sewakan barangmu sekarang di:\n" .
                         "🌐 {$webUrl}/vendor/register\n\n" .
                         "#RentifyVendor #CuanDariRumah #PassiveIncome #PeluangBisnis #IdeUsahaMahasiswa #BisnisRental #BarangNganggurJadiUang #SewaBarangOnline",
            'visual_prompt' => "Format 9:16 vertikal. Visual berenergi tinggi, menampilkan transisi dari barang berdebu di lemari menjadi notifikasi saldo cuan bertambah di aplikasi Rentify. Dominan warna Indigo & Gold/Yellow.",
        ];
    }

    /**
     * Integrasi Publikasi ke Instagram Graph API
     */
    public function publishToInstagram(AiMarketingPost $post): array
    {
        $token = config('rentify_ai.social.instagram.access_token');
        $accountId = config('rentify_ai.social.instagram.account_id');

        if (empty($token) || empty($accountId)) {
            // Mode Simulasi / Siap Pakai (Token belum diisi)
            $simulatedId = 'IG_' . strtoupper(Str::random(12));
            $post->update([
                'posted_to_instagram' => true,
                'instagram_post_id'   => $simulatedId,
                'posted_at'           => Carbon::now(),
                'log_info'            => ($post->log_info ? $post->log_info . "\n" : "") . "Instagram: Siap posting (Simulasi Sandbox - Token belum di-set di .env). ID: {$simulatedId}",
            ]);

            return [
                'success' => true,
                'mode'    => 'sandbox',
                'id'      => $simulatedId,
                'message' => 'Simulasi publish Instagram berhasil dicatat. Untuk posting live ke feed, isi INSTAGRAM_ACCESS_TOKEN di konfigurasi.'
            ];
        }

        try {
            // Panggil Instagram Graph API: /v19.0/{account-id}/media
            $mediaUrl = $post->visual_media_url ?: (config('rentify_ai.brand.website_url') . '/images/rentify-share-cover.jpg');
            
            $createMediaRes = Http::post("https://graph.facebook.com/v19.0/{$accountId}/media", [
                'image_url'    => $mediaUrl,
                'caption'      => $post->caption,
                'access_token' => $token,
            ]);

            if ($createMediaRes->successful()) {
                $creationId = $createMediaRes->json('id');

                // Publish container
                $publishRes = Http::post("https://graph.facebook.com/v19.0/{$accountId}/media_publish", [
                    'creation_id'  => $creationId,
                    'access_token' => $token,
                ]);

                if ($publishRes->successful()) {
                    $postId = $publishRes->json('id');
                    $post->update([
                        'posted_to_instagram' => true,
                        'instagram_post_id'   => $postId,
                        'posted_at'           => Carbon::now(),
                        'log_info'            => ($post->log_info ? $post->log_info . "\n" : "") . "Instagram Live Post ID: {$postId}",
                    ]);

                    return ['success' => true, 'id' => $postId, 'message' => 'Konten berhasil dipublikasikan ke Instagram!'];
                }
            }

            return ['success' => false, 'message' => 'Instagram API Error: ' . $createMediaRes->body()];
        } catch (\Throwable $e) {
            Log::error('Instagram Publish Exception: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Exception: ' . $e->getMessage()];
        }
    }

    /**
     * Integrasi Publikasi ke TikTok Content Posting API
     */
    public function publishToTikTok(AiMarketingPost $post): array
    {
        $accessToken = config('rentify_ai.social.tiktok.access_token');

        if (empty($accessToken)) {
            $simulatedId = 'TT_' . strtoupper(Str::random(12));
            $post->update([
                'posted_to_tiktok' => true,
                'tiktok_post_id'   => $simulatedId,
                'posted_at'        => Carbon::now(),
                'log_info'         => ($post->log_info ? $post->log_info . "\n" : "") . "TikTok: Siap posting (Simulasi Sandbox - Token belum di-set di .env). ID: {$simulatedId}",
            ]);

            return [
                'success' => true,
                'mode'    => 'sandbox',
                'id'      => $simulatedId,
                'message' => 'Simulasi publish TikTok berhasil dicatat. Untuk posting live, isi TIKTOK_ACCESS_TOKEN di konfigurasi.'
            ];
        }

        try {
            // TikTok Content Posting API v2: /v2/post/publish/creator_info/query/
            $response = Http::withToken($accessToken)
                ->post('https://open.tiktokapis.com/v2/post/publish/video/init/', [
                    'post_info' => [
                        'title' => Str::limit($post->hook_masalah, 90),
                        'privacy_level' => 'PUBLIC_TO_EVERYONE',
                        'disable_duet' => false,
                        'disable_stitch' => false,
                        'disable_comment' => false,
                    ],
                    'source_info' => [
                        'source' => 'PULL_FROM_URL',
                        'video_url' => $post->visual_media_url ?: (config('rentify_ai.brand.website_url') . '/videos/rentify-sample.mp4'),
                    ]
                ]);

            if ($response->successful()) {
                $publishId = $response->json('data.publish_id');
                $post->update([
                    'posted_to_tiktok' => true,
                    'tiktok_post_id'   => $publishId,
                    'posted_at'        => Carbon::now(),
                    'log_info'         => ($post->log_info ? $post->log_info . "\n" : "") . "TikTok Publish ID: {$publishId}",
                ]);

                return ['success' => true, 'id' => $publishId, 'message' => 'Konten berhasil diunggah ke TikTok Creator Queue!'];
            }

            return ['success' => false, 'message' => 'TikTok API Error: ' . $response->body()];
        } catch (\Throwable $e) {
            Log::error('TikTok Publish Exception: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Exception: ' . $e->getMessage()];
        }
    }
}
