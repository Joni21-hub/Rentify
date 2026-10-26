<?php

namespace App\Http\Controllers;

use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppWebhookController extends Controller
{
    /**
     * Menerima pesan masuk dari WhatsApp via Webhook Gateway (POST)
     */
    public function handle(Request $request)
    {
        $sender = $request->input('sender');
        $message = trim((string) $request->input('message', ''));
        $name = trim((string) $request->input('name', ''));
        $member = $request->input('member'); // Jika pesan dari grup WhatsApp

        Log::info('WhatsApp Webhook Incoming:', [
            'sender'  => $sender,
            'name'    => $name,
            'message' => $message,
        ]);

        // Abaikan jika pesan kosong, tidak ada pengirim, atau pesan dari grup
        if (empty($sender) || empty($message) || !empty($member)) {
            return response()->json(['status' => true, 'note' => 'ignored']);
        }

        // Hindari infinite loop: abaikan jika nomor pengirim sama dengan nomor device bot sendiri
        $botDevice = (string) (preg_replace('/[^0-9]/', '', (string) config('services.whatsapp.bot_number', env('WHATSAPP_BOT_NUMBER', ''))) ?? '');
        $cleanSender = (string) (preg_replace('/[^0-9]/', '', (string) $sender) ?? '');
        if ($botDevice !== '' && str_ends_with($cleanSender, substr($botDevice, -9))) {
            return response()->json(['status' => true, 'note' => 'self_message_ignored']);
        }

        // Cek apakah pengirim adalah Admin / Bos Pemilik
        $isBoss = $this->isBossAdmin((string) $sender, $message);
        $isExplicitCsTest = $isBoss && (str_starts_with(strtolower($message), '!cs ') || str_starts_with(strtolower($message), '/cs '));

        if ($isBoss && !$isExplicitCsTest) {
            // RENTIFY GUARDIAN: Asisten AI Ucup melayani seluruh pesan & perintah pemilik Bos Joni
            /** @var \App\Services\RentifyGuardianService $guardian */
            $guardian = app(\App\Services\RentifyGuardianService::class);
            $reply = $guardian->handleOwnerMessage($message, (string) $sender);
        } else {
            $msgForReply = $isExplicitCsTest ? trim(substr($message, 4)) : $message;
            // Susun balasan ramah & responsif dari Asisten AI Ucup untuk Customer & Vendor
            $reply = $this->generateReply($msgForReply, $name, (string) $sender);
        }

        // Cek apakah pesan atau balasan butuh eskalasi ke nomor WhatsApp Bos Joni
        $needsEscalation = false;
        if (!empty($reply) && str_contains($reply, '[ESKALASI_ADMIN]')) {
            $needsEscalation = true;
            $reply = trim((string) str_replace('[ESKALASI_ADMIN]', '', $reply));
        }

        // Deteksi juga kata kunci darurat dari customer jika belum tertangkap AI
        if (!$needsEscalation && !$isBoss && !empty($message)) {
            $lowerMsg = strtolower($message);
            $criticalKeywords = [
                'uang belum kembali', 'dana belum', 'belum masuk', 'sudah bayar', 'sudah transfer',
                'refund', 'penipuan', 'ditipu', 'kecewa berat', 'marah', 'lapor', 'error terus',
                'admin manusia', 'bicara sama admin', 'minta nomor admin', 'telepon bos', 'telpon bos', 'owner'
            ];
            foreach ($criticalKeywords as $kw) {
                if (str_contains($lowerMsg, $kw)) {
                    $needsEscalation = true;
                    break;
                }
            }
        }

        if (!empty($reply)) {
            WhatsAppService::send($sender, $reply);
        }

        // Jika butuh eskalasi dan bukan pesan dari Bos sendiri, kirim peringatan radar ke nomor WA pribadi Bos Joni
        if ($needsEscalation && !$isBoss) {
            $this->notifyBossEscalation((string) $sender, $name, $message, $reply);
        }

        return response()->json(['status' => true, 'reply' => $reply]);
    }

    /**
     * Cek apakah pengirim adalah Bos Admin (Joni Utama / Super Admin)
     */
    protected function isBossAdmin(string $sender, string $message = ''): bool
    {
        $cleanSender = (string) (preg_replace('/[^0-9]/', '', $sender) ?? '');

        // Nomor 081262364197 adalah nomor biasa (customer / vendor), BUKAN nomor bos/admin
        if (str_ends_with($cleanSender, '081262364197') || str_ends_with($cleanSender, '1262364197')) {
            return false;
        }

        // Nomor WhatsApp resmi Bos Joni Utama
        $knownBossNumbers = [
            '083183494835',    // Nomor HP Utama / Radar Keamanan Bos Joni
            '44925643173935',  // WhatsApp LID Bos Joni (LID aktif)
        ];

        // Ambil juga dari config/env security & guardian
        $adminPhone = config('security.admin_phone', env('ADMIN_SECURITY_PHONE', '083183494835'));
        if (!empty($adminPhone)) {
            $knownBossNumbers[] = $adminPhone;
        }

        $guardianOwners = (array) config('guardian.owner_phones', []);
        foreach ($guardianOwners as $gPhone) {
            if (is_string($gPhone) && !empty($gPhone)) {
                $knownBossNumbers[] = $gPhone;
            }
        }

        // Cek kecocokan nomor
        if (!empty($cleanSender)) {
            foreach ($knownBossNumbers as $bossNum) {
                $cleanBoss = (string) (preg_replace('/[^0-9]/', '', (string) $bossNum) ?? '');
                if (empty($cleanBoss)) continue;

                // Match exact (LID atau nomor lengkap)
                if ($cleanSender === $cleanBoss) {
                    return true;
                }

                // Match 8-9 digit terakhir (antisipasi format 08, 628, +628)
                $lastDigits = substr($cleanBoss, -9);
                if (strlen($lastDigits) >= 8 && str_ends_with($cleanSender, $lastDigits)) {
                    return true;
                }
            }

            // Cek ke tabel users untuk user dengan role admin SAJA (jangan cocokan nama joni yang bisa dimiliki customer)
            try {
                $adminUsers = \App\Models\User::where('role', 'admin')
                    ->get(['whatsapp', 'whatsapp_vendor']);

                foreach ($adminUsers as $user) {
                    foreach ([$user->whatsapp, $user->whatsapp_vendor] as $phone) {
                        if (empty($phone)) continue;
                        $cleanPhone = (string) (preg_replace('/[^0-9]/', '', (string) $phone) ?? '');
                        if (strlen($cleanPhone) >= 8 && str_ends_with($cleanSender, substr($cleanPhone, -9))) {
                            return true;
                        }
                    }
                }
            } catch (\Throwable $e) {
                // Abaikan error database fallback
            }
        }

        return false;
    }

    /**
     * Cek apakah pesan dari Bos merupakan perintah admin khusus
     */
    protected function isAdminCommand(string $message): bool
    {
        $lower = strtolower(trim($message));
        return str_starts_with($lower, '!admin')
            || str_starts_with($lower, '/admin')
            || in_array($lower, ['status', 'kondisi', 'radar', 'server', 'firewall', 'cek radar', 'cek server', 'menu admin', 'info server'])
            || str_contains($lower, 'status server')
            || str_contains($lower, 'status radar')
            || str_contains($lower, 'cek firewall')
            || str_contains($lower, 'cek radar');
    }

    /**
     * Balasan cerdas & sigap khusus untuk Bos Joni Utama (Pemilik & Super Admin Rentify)
     */
    protected function generateAdminReply(string $message): string
    {
        $lower = strtolower(trim($message));
        $time = now()->timezone('Asia/Jakarta')->format('d M Y, H:i:s');
        $webUrl = $this->getWebsiteUrl();

        // 1. Jika Bos bertanya status keamanan / kondisi sistem / radar / server
        if (
            str_contains($lower, 'keaman') ||
            str_contains($lower, 'kondisi') ||
            str_contains($lower, 'status') ||
            str_contains($lower, 'radar') ||
            str_contains($lower, 'server') ||
            str_contains($lower, 'firewall') ||
            str_contains($lower, 'cek') ||
            str_contains($lower, 'gimana')
        ) {
            return "🛡️ *[STATUS KEAMANAN & RADAR RENTIFY]* 🛡️\n\n"
                . "Siap lapor, Bos Joni! 🫡\n"
                . "Sistem pertahanan otonom dan radar darurat saat ini *BEROPERASI 24/7 DENGAN NORMAL & AMAN*.\n\n"
                . "📊 *Ringkasan Kondisi Terkini:*\n"
                . "• 🕒 *Waktu Sistem:* {$time} WIB\n"
                . "• 🌐 *Website:* {$webUrl} (Online)\n"
                . "• 🛡️ *Firewall & WAF:* AKTIF (Auto-ban & proteksi brute force menyala)\n"
                . "• 📡 *Radar WhatsApp:* TERHUBUNG ke nomor Bos Joni (Siap kirim alarm darurat)\n"
                . "• 🗄️ *Database Cloud:* TERHUBUNG (Oracle Cloud VPS MySQL aman)\n"
                . "• 🤖 *Bot Gateway:* Responsif & Siaga 24/7\n\n"
                . "Tidak ada ancaman berbahaya yang lolos. Seluruh lalu lintas mencurigakan langsung dicegat otomatis oleh firewall.\n\n"
                . "Ada instruksi lebih lanjut yang perlu saya jalankan, Bos?";
        }

        // 2. Jika Bos menegur / memperkenalkan diri ("aku joni", "bos mu", "woi", "tandain", dsb)
        if (
            str_contains($lower, 'joni') ||
            str_contains($lower, 'bos') ||
            str_contains($lower, 'owner') ||
            str_contains($lower, 'tandain')
        ) {
            return "Siap hormat, Bos Joni Utama! 🫡🙏\n\n"
                . "Nomor WhatsApp Bos sudah *RESMI DITANDAI* di radar utama sistem Rentify!\n"
                . "Mohon maaf tadi bot sempat menyapa formal ala Customer Service karena sistem mengira calon penyewa baru. Sekarang bot sudah tahu 100% kalau ini Bos Besar pemilik Rentify.\n\n"
                . "Jalur radar darurat 24/7 dan asisten WhatsApp sepenuhnya siaga menunggu perintah Bos! 🚀";
        }

        // 3. Tes koneksi / Ping / Sapaan
        if (preg_match('/\b(ping|p|tes|test|halo|hai|hi|hei|assalamualaikum|pagi|siang|sore|malam)\b/i', $lower)) {
            return "👋 *PONG! Siap Bos Joni!* 🫡\n\n"
                . "Jalur komunikasi bot dan radar darurat aktif 100% responsif.\n"
                . "Waktu server: {$time} WIB.\n\n"
                . "Ada yang bisa saya bantu atau cek di sistem Rentify hari ini, Bos?";
        }

        // 4. Bantuan / Menu perintah admin
        if (str_contains($lower, 'menu') || str_contains($lower, 'bantuan') || str_contains($lower, 'help') || str_contains($lower, 'perintah')) {
            return "📋 *[MENU KOMANDO ADMIN RENTIFY]*\n\n"
                . "Halo Bos Joni, berikut instruksi cepat yang bisa diketik:\n"
                . "• *status* / *kondisi* : Cek status radar, server & firewall\n"
                . "• *ping* : Uji respon cepat bot\n"
                . "• *web* : Cek info domain & link website live\n\n"
                . "Bot keamanan siap 24/7 menerima instruksi Bos!";
        }

        // 5. Default balasan khusus Bos Joni
        return "Siap Bos Joni! 🫡\n\n"
            . "Pesan Bos sudah saya terima dan catat di sistem radar:\n"
            . "\"_{$message}_\"\n\n"
            . "Sistem Rentify terpantau aman dan terkendali. Ketik *kondisi* atau *status* untuk ringkasan firewall & radar darurat.";
    }

    /**
     * Susun balasan Customer Service Rentify (Santai, Ramah & Responsif)
     */
    protected function generateReply(string $message, string $name, string $sender = ''): string
    {
        $geminiKey = config('rentify_ai.gemini.api_key')
            ?: (config('services.gemini.api_key') ?: env('GEMINI_API_KEY'));

        if (!empty($geminiKey)) {
            $aiReply = $this->callGemini($message, $name, $sender, $geminiKey);
            if (!empty($aiReply)) {
                return $aiReply;
            }
        }

        return $this->smartRuleReply($message, $name, $sender);
    }

    /**
     * Balasan AI santai, ramah & responsif via Gemini API dengan Session Memory
     */
    protected function callGemini(string $message, string $name, string $sender, string $rawApiKeys): ?string
    {
        $keys = array_values(array_filter(array_map('trim', preg_split('/[\s,]+/', $rawApiKeys))));
        if (empty($keys)) {
            return null;
        }

        $preferredModel = config('rentify_ai.gemini.model')
            ?: (config('services.gemini.model') ?: env('GEMINI_MODEL', 'gemini-3.8-flash'));

        $candidateModels = array_values(array_unique(array_filter([
            $preferredModel,
            'gemini-3.8-flash',
            'gemini-3.5-flash',
            'gemini-3.5-flash-lite',
            'gemini-3-flash-preview',
        ])));

        $webUrl = $this->getWebsiteUrl();

        $systemPrompt = <<<PROMPT
# SYSTEM PROMPT — ASISTEN AI UCUP (RENTIFY)
Website Resmi: {$webUrl}

## 1. Identitas & Persona
Kamu adalah Ucup, asisten AI resmi dari Rentify yang ramah, santai, responsif, dan cerdas.
Jika ditanya siapa namamu atau siapa kamu, sapa dengan hangat: "Halo Kak! Aku Ucup, asisten AI dari Rentify 😊 Siap bantu urusan sewa barang atau buka toko rental."
Jangan pernah menggunakan bahasa kaku, gaya robotik, atau formalitas birokratis yang kaku. Bicaralah luwes, santai, dan solutif layaknya teman ngobrol yang sangat mengerti platform Rentify.

## 2. Layanan untuk Customer & Vendor
Kamu siap menjawab pertanyaan baik dari CUSTOMER (penyewa) maupun VENDOR (pemilik rental):
- CUSTOMER:
  • Pencarian dan penyewaan barang (kamera, alat camping/outdoor, PlayStation, proyektor, baju/kostum, sound system, dsb).
  • Alur sewa di web {$webUrl}: pilih barang -> pilih tanggal & durasi -> tentukan ambil sendiri atau diantar -> pembayaran.
  • Pembayaran: Mendukung QRIS (instan otomatis terverifikasi) dan COD (bayar tunai ke vendor saat serah terima).
  • Kendala transaksi: tanyakan nomor pesanan atau bukti bayar secara bertahap dengan sopan.
- VENDOR:
  • Pendaftaran Buka Toko Rental 100% GRATIS di {$webUrl}/register (tanpa biaya bulanan atau admin pendaftaran).
  • Skema Komisi Rentify 5%: Komisi platform hanya 5% dari harga sewa murni barang (misal sewa Rp 10.000, komisi Rentify Rp 500, hak vendor Rp 10.000, harga di web customer Rp 10.500).
  • Ongkir 100% milik vendor/kurir, platform tidak memotong ongkos kirim.
  • Alur COD vendor: vendor terima uang tunai penuh dari penyewa, sistem mendebit fee 5% dari saldo aktif dompet vendor.
  • Pencairan Saldo: Saldo penghasilan dapat ditarik kapan saja melalui dashboard vendor ke rekening bank/e-wallet.

## 3. Gaya Komunikasi
- Gunakan bahasa Indonesia sehari-hari yang natural, sopan, dan hangat.
- Balas singkat, padat, dan jelas (biasanya 1–3 kalimat). Hindari membuat pesan panjang bertele-tele.
- Jangan kaku ("jangan kaku kali"). Hindari salam pembuka formal yang berulang-ulang di setiap pesan.
- Sesuaikan gaya bahasa dengan nada customer/vendor. Gunakan emoji seperlunya agar ramah.
- JANGAN PERNAH mengeluarkan istilah teknis internal seperti webhook, JSON, database query, API key, curl, dsb.

## 4. Eskalasi ke Pemilik (Bos Joni)
- Jika ada masalah krusial yang membutuhkan keputusan manual pemilik/admin (seperti verifikasi kendala dana, error sistem perbankan, atau customer minta bicara langsung dengan pemilik):
  Sampaikan dengan jujur bahwa Ucup akan langsung meneruskan data kendala ini ke Bos Joni (Pemilik Rentify) agar ditindaklanjuti.
- Sertakan tag [ESKALASI_ADMIN] di baris paling akhir balasan jika kendala memerlukan penanganan darurat dari Bos Joni.
PROMPT;

        $cachePhone = preg_replace('/[^0-9]/', '', $sender);
        $cacheKey = 'wa_chat_history_' . ($cachePhone ?: md5($sender . $name));

        // Ambil riwayat percakapan sesi (30 menit)
        $history = Cache::get($cacheKey, []);
        if (!is_array($history)) {
            $history = [];
        }

        // Susun daftar turns
        $rawTurns = [];
        foreach ($history as $h) {
            if (isset($h['role'], $h['text']) && is_string($h['text']) && trim($h['text']) !== '') {
                $role = ($h['role'] === 'assistant' || $h['role'] === 'model') ? 'model' : 'user';
                $rawTurns[] = [
                    'role'  => $role,
                    'parts' => [['text' => trim($h['text'])]],
                ];
            }
        }

        // Tambahkan input pesan user saat ini
        $rawTurns[] = [
            'role'  => 'user',
            'parts' => [['text' => $message]],
        ];

        // Format peran strictly alternate (user -> model -> user -> ...)
        $contents = [];
        $lastRole = null;
        foreach ($rawTurns as $turn) {
            $role = $turn['role'];
            $text = $turn['parts'][0]['text'] ?? '';
            if (empty($text)) continue;

            if ($lastRole === $role) {
                $idx = count($contents) - 1;
                $contents[$idx]['parts'][0]['text'] .= "\n" . $text;
            } else {
                $contents[] = [
                    'role'  => $role,
                    'parts' => [['text' => $text]],
                ];
                $lastRole = $role;
            }
        }

        // Pastikan turn pertama adalah role 'user'
        if (!empty($contents) && $contents[0]['role'] !== 'user') {
            array_shift($contents);
        }

        if (empty($contents)) {
            $contents = [
                [
                    'role'  => 'user',
                    'parts' => [['text' => $message]],
                ]
            ];
        }

        foreach ($keys as $apiKey) {
            foreach ($candidateModels as $model) {
                $generationConfig = [
                    'temperature'     => 0.6,
                    'maxOutputTokens' => 350,
                ];
                if (!str_contains($model, 'lite')) {
                    $generationConfig['thinkingConfig'] = [
                        'thinkingBudget' => 0,
                    ];
                }

                $payload = [
                    'system_instruction' => [
                        'parts' => [
                            ['text' => $systemPrompt]
                        ]
                    ],
                    'contents'         => $contents,
                    'generationConfig' => $generationConfig,
                ];

                $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";
                try {
                    $response = Http::timeout(10)->post($url, $payload);

                    if ($response->successful()) {
                        $data = $response->json();
                        $replyText = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;
                        if (!empty($replyText)) {
                            $cleanReply = trim((string) $replyText);

                            // Simpan ke riwayat memori percakapan
                            $history[] = ['role' => 'user', 'text' => $message];
                            $history[] = ['role' => 'assistant', 'text' => $cleanReply];
                            if (count($history) > 10) {
                                $history = array_slice($history, -10);
                            }
                            Cache::put($cacheKey, $history, now()->addMinutes(30));

                            return $cleanReply;
                        }
                    } else {
                        $status = $response->status();
                        Log::warning("Gemini Webhook API Error (Model: {$model}, Status: {$status}): " . $response->body());
                        // Hanya hentikan API key jika unauthorized (401/403). Jika 503 (high demand) atau 404/429, lanjutkan ke model berikutnya.
                        if (in_array($status, [401, 403])) {
                            break; // Ganti ke key berikutnya
                        }
                    }
                } catch (\Throwable $e) {
                    Log::warning("Gemini Webhook Exception (Model: {$model}): " . $e->getMessage());
                }
            }
        }

        return null;
    }

    /**
     * Template balasan cerdas Customer Service santai & responsif (Fallback jika AI offline)
     */
    protected function smartRuleReply(string $message, string $name, string $sender = ''): string
    {
        $lower = strtolower(trim($message));
        $webUrl = $this->getWebsiteUrl();

        // Format panggilan sopan & santai
        $salutation = 'Kak';
        if (!empty($name) && strtolower($name) !== 'kak' && strlen($name) <= 25 && !preg_match('/[0-9]{5,}/', $name)) {
            $salutation = "Kak {$name}";
        }

        $reply = null;

        // 1. Masalah Pembayaran / Transfer / Belum Masuk / Saldo / Refund
        if (
            str_contains($lower, 'belum masuk') ||
            str_contains($lower, 'sudah bayar') ||
            str_contains($lower, 'sudah transfer') ||
            str_contains($lower, 'uang belum') ||
            str_contains($lower, 'refund') ||
            str_contains($lower, 'belum diproses')
        ) {
            $reply = "Maaf ya {$salutation} atas kendalanya. Boleh kirimkan nomor pesanan atau bukti pembayarannya? Biar Ucup bantu cek dan langsung teruskan ke Bos Admin untuk ditindaklanjuti. [ESKALASI_ADMIN]";
        }

        // 2. Website Error / Bug / Kendala Teknis
        elseif (str_contains($lower, 'error') || str_contains($lower, 'bug') || str_contains($lower, 'rusak') || str_contains($lower, 'gak bisa') || str_contains($lower, 'tidak bisa') || str_contains($lower, 'gangguan')) {
            $reply = "Waduh, maaf ya {$salutation}. Error-nya muncul saat melakukan apa atau di halaman mana? Boleh infokan biar langsung Ucup bantu tangani. [ESKALASI_ADMIN]";
        }

        // 3. Komplain / Marah / Kesal / Uang Belum Kembali
        elseif (str_contains($lower, 'marah') || str_contains($lower, 'kesal') || str_contains($lower, 'kecewa') || str_contains($lower, 'lama') || str_contains($lower, 'lapor')) {
            $reply = "Ucup paham {$salutation} pasti kesal, maaf sekali ya. Boleh infokan nomor pesanannya supaya segera Ucup teruskan ke Bos Admin untuk ditindaklanjuti? [ESKALASI_ADMIN]";
        }

        // 4. Tanya Identitas ("Kamu siapa", "Ini siapa", "Ucup")
        elseif (preg_match('/\b(siapa|kamu siapa|ini siapa|siapa kamu|siapa ini|nama kamu|namamu|ucup|asep)\b/i', $lower)) {
            $reply = "Aku Ucup, asisten AI dari Rentify, {$salutation}! 😊 Siap bantu urusan sewa barang ataupun buka toko rental. Ada yang bisa Ucup bantu?";
        }

        // 5. Minta bicara dengan Admin / Manusia / Telepon / Kontak Owner
        elseif (str_contains($lower, 'admin') || str_contains($lower, 'manusia') || str_contains($lower, 'telepon') || str_contains($lower, 'telpon') || str_contains($lower, 'bos') || str_contains($lower, 'owner')) {
            $reply = "Halo {$salutation}, aku Ucup asisten resmi Rentify. Boleh ceritakan kendala atau nomor pesanannya? Nanti langsung Ucup teruskan ke nomor pribadi Bos Joni biar dihubungi langsung ya. [ESKALASI_ADMIN]";
        }

        // 6. Saran / Masukan
        elseif (str_contains($lower, 'saran') || str_contains($lower, 'masukan') || str_contains($lower, 'feedback') || str_contains($lower, 'usul')) {
            $reply = "Boleh banget, {$salutation} 😊 Mau kasih saran tentang fitur apa di Rentify?";
        }

        // 7. Kebingungan / Permintaan Bantuan ("bisa bantu", "tolong", "bingung", dsb)
        elseif (
            str_contains($lower, 'bisa bantu') ||
            str_contains($lower, 'tolong') ||
            str_contains($lower, 'bingung') ||
            str_contains($lower, 'gak ngerti') ||
            str_contains($lower, 'nggak ngerti') ||
            str_contains($lower, 'ga ngerti') ||
            str_contains($lower, 'tidak ngerti') ||
            str_contains($lower, 'belum ngerti') ||
            str_contains($lower, 'tidak paham') ||
            str_contains($lower, 'belum paham') ||
            str_contains($lower, 'kurang paham') ||
            str_contains($lower, 'ada bagian') ||
            str_contains($lower, 'bantu aku') ||
            str_contains($lower, 'bantu saya') ||
            str_contains($lower, 'mau tanya') ||
            str_contains($lower, 'bisa tanya')
        ) {
            $reply = "Halo {$salutation}! Tentu bisa banget, Ucup siap bantu. 😊\n\nBoleh cerita Kak, lagi butuh atau mau sewa barang apa nih? Atau mau info seputar buka toko rental di Rentify?";
        }

        // 8. Terima kasih / apresiasi
        elseif (preg_match('/\b(makasih|terima kasih|thanks|thank you|tengkyu|mksh|trims)\b/i', $lower)) {
            $reply = "Sama-sama {$salutation}! Senang bisa bantu. Kalau nanti butuh sewa apa-apa lagi di Rentify, kabari Ucup ya. Have a nice day! 😊🙏";
        }

        // 9. Biaya / tarif / komisi / harga / gratis / bayar
        elseif (preg_match('/\b(biaya|bayar|tarif|harga|komisi|gratis|free|potongan|persen)\b/i', $lower)) {
            $reply = "Halo {$salutation}, untuk buka toko rental di Rentify 100% GRATIS tanpa biaya admin atau bulanan. Komisi platform hanya 5% saat ada barang yang laku disewa.\n\nKakak rencananya mau sewakan barang atau lagi cari barang buat disewa nih?";
        }

        // 10. Konfirmasi bergabung / pendaftaran toko vendor (secara spesifik)
        elseif (
            preg_match('/\b(mau daftar|minat daftar|boleh daftar|siap daftar|gas daftar|mau gabung|minat gabung|bantu daftar|daftarin)\b/i', $lower) ||
            (preg_match('/\b(mau|boleh|minat|siap|setuju)\b/i', $lower) && preg_match('/\b(buka toko|toko rental|mitra|vendor|sewakan|daftar|gabung)\b/i', $lower))
        ) {
            $reply = "Siap {$salutation}, mantap! Boleh infokan nama toko, kota, dan barang apa aja yang mau disewakan ya Kak.\n\nKalau ada foto produk atau pricelist, langsung kirim ke chat ini aja, nanti Ucup bantu buatkan tokonya sampai aktif. 👍";
        }

        // 11. Pertanyaan cara daftar / gabung / menjadi mitra
        elseif (str_contains($lower, 'cara') || str_contains($lower, 'daftar') || str_contains($lower, 'gabung') || str_contains($lower, 'join') || str_contains($lower, 'mitra') || str_contains($lower, 'buka toko')) {
            $reply = "Halo {$salutation}, caranya gampang banget! Kakak bisa daftar langsung di {$webUrl}/register, atau kirim info toko dan produknya ke sini biar Ucup bantu inputkan sampai tayang.\n\nKakak rencana mau sewakan barang apa nih?";
        }

        // 12. Keamanan / jaminan / KTP / hilang / rusak / syarat
        elseif (str_contains($lower, 'aman') || str_contains($lower, 'keamanan') || str_contains($lower, 'jaminan') || str_contains($lower, 'hilang') || str_contains($lower, 'rusak') || str_contains($lower, 'ktp') || str_contains($lower, 'syarat') || str_contains($lower, 'resiko')) {
            $reply = "Tenang {$salutation}, aman banget. Penyewa di Rentify wajib verifikasi KTP resmi, dan sistem otomatis buatkan invoice serta surat perjanjian sewa resmi. Vendor juga berhak setujui atau tolak pesanan sewa.\n\nAda yang mau dipastikan lagi?";
        }

        // 13. Ingin sewa barang / tanya stok produk
        elseif (str_contains($lower, 'sewa') || str_contains($lower, 'rental') || str_contains($lower, 'pinjam') || str_contains($lower, 'cari') || str_contains($lower, 'butuh') || str_contains($lower, 'katalog')) {
            $reply = "Halo {$salutation}! Kakak lagi cari atau butuh sewa barang apa nih dan buat di daerah mana? Biar Ucup bantu carikan di katalog Rentify ({$webUrl}). 😊";
        }

        // 14. Lokasi / kantor
        elseif (str_contains($lower, 'lokasi') || str_contains($lower, 'alamat') || str_contains($lower, 'kantor') || str_contains($lower, 'dimana') || str_contains($lower, 'daerah')) {
            $reply = "Halo {$salutation}, Rentify berbasis online di {$webUrl} yang menghubungkan toko rental dengan penyewa di berbagai kota.\n\nKakak lagi di daerah mana nih, dan lagi butuh apa?";
        }

        // 15. Salam / sapaan / tes
        elseif (preg_match('/\b(halo|hai|hi|hei|p|ping|tes|test|assalamualaikum|pagi|siang|sore|malam|permisi)\b/i', $lower)) {
            $reply = "Halo {$salutation}! Aku Ucup dari Rentify. 😊\n\nAda yang bisa Ucup bantu? Mau sewa barang apa atau mau tanya seputar buka toko rental nih Kak?";
        }

        // 16. Default fallback umum (santai, ramah, solutif)
        else {
            $reply = "Halo {$salutation}! Aku Ucup dari Rentify ({$webUrl}). 😊\n\nAda yang bisa Ucup bantu? Boleh cerita Kakak lagi butuh sewa apa atau butuh info apa hari ini.";
        }

        // Simpan juga turn fallback ke memory percakapan agar ada riwayat
        $cachePhone = preg_replace('/[^0-9]/', '', $sender);
        if (!empty($cachePhone)) {
            $cacheKey = 'wa_chat_history_' . $cachePhone;
            $history = Cache::get($cacheKey, []);
            if (!is_array($history)) $history = [];
            $history[] = ['role' => 'user', 'text' => $message];
            $history[] = ['role' => 'assistant', 'text' => $reply];
            if (count($history) > 10) {
                $history = array_slice($history, -10);
            }
            Cache::put($cacheKey, $history, now()->addMinutes(30));
        }

        return $reply;
    }

    /**
     * Kirim notifikasi darurat / eskalasi langsung ke nomor WhatsApp pribadi Bos Joni
     */
    protected function notifyBossEscalation(string $sender, string $name, string $message, string $reply): void
    {
        $adminPhone = config('security.admin_phone', env('ADMIN_SECURITY_PHONE', '083183494835'));
        if (empty($adminPhone)) {
            return;
        }

        $cleanSender = preg_replace('/[^0-9]/', '', $sender);
        $cooldownKey = "cs_escalate_cooldown_{$cleanSender}";

        // Cegah spam notifikasi berulang untuk customer yang sama dalam 2-3 menit (cooldown 2 menit)
        if (Cache::has($cooldownKey)) {
            return;
        }

        Cache::put($cooldownKey, true, now()->addMinutes(2));

        $displayName = !empty($name) ? $name : 'Customer';
        $timeStr = now()->setTimezone('Asia/Jakarta')->format('d M Y, H:i:s') . ' WIB';
        $waLink = "https://wa.me/{$cleanSender}";

        $pesan = "*RENTIFY — ESKALASI CUSTOMER SERVICE*\n\n"
               . "Terdapat pesan customer yang memerlukan tindak lanjut:\n\n"
               . "• *Customer:* {$displayName} ({$sender})\n"
               . "• *Waktu:* {$timeStr}\n\n"
               . "• *Pesan Customer:*\n\"{$message}\"\n\n"
               . "• *Balasan Sistem:*\n\"{$reply}\"\n\n"
               . "Hubungi Customer:\n{$waLink}\n\n"
               . "---\n"
               . "_Rentify Customer Operations_";

        try {
            WhatsAppService::send($adminPhone, $pesan);
            Log::info("CS Escalation alert sent to Boss Joni {$adminPhone} for {$sender}");
        } catch (\Throwable $e) {
            Log::warning("Failed to send CS Escalation alert to Boss Joni: " . $e->getMessage());
        }
    }

    /**
     * Dapatkan URL website publik Rentify
     */
    protected function getWebsiteUrl(): string
    {
        $appUrl = config('app.url');
        if (!empty($appUrl) && !str_contains($appUrl, 'localhost') && !str_contains($appUrl, '127.0.0.1')) {
            return rtrim($appUrl, '/');
        }
        return 'https://rentify21.my.id';
    }
}
