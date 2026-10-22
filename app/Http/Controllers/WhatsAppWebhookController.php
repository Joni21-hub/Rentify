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
     * Menerima pesan masuk dari WhatsApp via Webhook Fonnte (POST)
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
        $botDevice = (string) (preg_replace('/[^0-9]/', '', (string) config('services.whatsapp.bot_number', env('WHATSAPP_BOT_NUMBER', '081262364197'))) ?? '');
        $cleanSender = (string) (preg_replace('/[^0-9]/', '', (string) $sender) ?? '');
        if ($botDevice !== '' && str_ends_with($cleanSender, substr($botDevice, -9))) {
            return response()->json(['status' => true, 'note' => 'self_message_ignored']);
        }

        // Cek apakah pengirim adalah Admin / Bos dan mengirimkan perintah admin khusus
        $isBoss = $this->isBossAdmin((string) $sender, $message);
        $isAdminCommand = $isBoss && $this->isAdminCommand($message);

        if ($isAdminCommand) {
            $reply = $this->generateAdminReply($message);
        } else {
            // Susun balasan santai, ramah & responsif (Tim Rentify via Gemini AI)
            $reply = $this->generateReply($message, $name, (string) $sender);
        }

        if (!empty($reply)) {
            WhatsAppService::send($sender, $reply);
        }

        return response()->json(['status' => true]);
    }

    /**
     * Cek apakah pengirim adalah Bos Admin (Joni Utama / Super Admin)
     */
    protected function isBossAdmin(string $sender, string $message = ''): bool
    {
        $cleanSender = preg_replace('/[^0-9]/', '', $sender);

        // Nomor WhatsApp resmi Bos Joni Utama
        $knownBossNumbers = [
            '083183494835',    // Nomor HP Utama / Radar Keamanan Bos Joni
            '082183394549',    // Nomor HP Vendor / Bisnis Bos Joni
            '249070069219550', // WhatsApp LID Bos Joni (LID lama)
            '44925643173935',  // WhatsApp LID Bos Joni (LID aktif)
        ];

        // Ambil juga dari config/env security
        $adminPhone = config('security.admin_phone', env('ADMIN_SECURITY_PHONE', '083183494835'));
        if (!empty($adminPhone)) {
            $knownBossNumbers[] = $adminPhone;
        }

        // Cek kecocokan nomor
        if (!empty($cleanSender)) {
            foreach ($knownBossNumbers as $bossNum) {
                $cleanBoss = preg_replace('/[^0-9]/', '', $bossNum);
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

            // Cek ke tabel users untuk user dengan role admin atau nama Joni
            try {
                $adminUsers = \App\Models\User::where('role', 'admin')
                    ->orWhere('name', 'like', '%joni%')
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
                . "• 🗄️ *Database Cloud:* TERHUBUNG (Aiven MySQL aman)\n"
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
            'gemini-3.6-flash',
            'gemini-3.5-flash',
            'gemini-3.5-flash-lite',
        ])));

        $webUrl = $this->getWebsiteUrl();

        $systemPrompt = <<<PROMPT
Anda adalah perwakilan resmi dari "Tim Rentify", platform persewaan online di Indonesia (website resmi: {$webUrl}).

IDENTITAS & KARAKTER:
1. JANGAN PERNAH menyebut nama Anda "Asep" atau nama orang tertentu. Posisikan dan perkenalkan diri secara natural sebagai "Tim Rentify" (atau "Admin Rentify").
2. Anda adalah manusia asli: ramah, hangat, santai, asik diajak ngobrol, solutif, dan sangat responsif.
3. Selalu sapa pengguna dengan ramah, gunakan panggilan "Kak" atau nama pengirim secara wajar.
4. JANGAN PERNAH mengaku atau mengisyaratkan diri Anda sebagai AI, bot, robot, asisten virtual, program, atau model bahasa. Jika ditanya apakah Anda bot, tegaskan secara santai dan ramah bahwa Anda admin asli dari Tim Rentify yang sedang online dan siap bantu.
5. GAYA NGOBROL & FORMAT BALASAN (WAJIB DIPATUHI):
   - JANGAN PANJANG-PANJANG: Cukup 1 sampai 3 kalimat santai dan padat. Hindari balasan panjang bertele-tele atau formalitas kaku.
   - SLOW & SANTAI: Ngobrol santai layaknya chat WhatsApp manusia nyata (luwes, akrab, bersahabat, sopan).
   - TANYA KEBUTUHAN CUSTOMER: Secara proaktif tanyakan maunya apa dan butuhnya apa (misal: "Kakak lagi butuh atau cari sewa barang apa nih?", atau "Rencana mau cari alat apa buat di kota mana?").
   - RESPONSIF & TO THE POINT: Jawab langsung inti pertanyaannya, lalu berikan pertanyaan balik yang santai untuk memandu customer.
   - Jangan berikan penjelasan panjang jika belum ditanya secara spesifik.
6. JANGAN PERNAH mengeluarkan istilah teknis seperti webhook, server, database, radar, prompt, token, endpoint, API, JSON, dsb.

PENGETAHUAN LAYANAN RENTIFY:
- Rentify ({$webUrl}) adalah tempat sewa-menyewa online: kamera/fotografi, alat outdoor/camping, PlayStation, proyektor, baju/kostum pesta, sound system, dsb.
- UNTUK YANG INGIN SEWA BARANG: Tanya barang apa yang dicari dan daerah mana, arahkan atau bantu cekkan di katalog {$webUrl}.
- UNTUK YANG MAU BUKA TOKO RENTAL: 100% GRATIS tanpa biaya pendaftaran/bulanan. Bisa daftar sendiri di {$webUrl}/register atau Tim Rentify siap bantu inputkan barangnya sampai tayang.
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
                    $response = Http::timeout(5)->post($url, $payload);

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
                        if (in_array($status, [400, 401, 403, 429])) {
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

        // 1. Kebingungan / Permintaan Bantuan ("bisa bantu", "tolong", "bingung", "gak ngerti", "tidak paham", dsb)
        if (
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
            $reply = "Halo {$salutation}! Tentu bisa banget, Tim Rentify siap bantu. 😊\n\n"
                . "Boleh cerita Kak, lagi butuh atau mau sewa barang apa nih? Atau lagi mau info seputar buka toko rental di Rentify?";
        }

        // 2. Terima kasih / apresiasi
        elseif (preg_match('/\b(makasih|terima kasih|thanks|thank you|tengkyu|mksh|trims)\b/i', $lower)) {
            $reply = "Sama-sama {$salutation}! Senang bisa bantu. Kalau nanti ada butuh sewa apa-apa lagi seputar Rentify, kabari kami ya Kak. Have a nice day! 😊🙏";
        }

        // 3. Biaya / tarif / komisi / harga / gratis / bayar
        elseif (preg_match('/\b(biaya|bayar|tarif|harga|komisi|gratis|free|potongan|persen)\b/i', $lower)) {
            $reply = "Halo {$salutation}, untuk buka toko rental di Rentify 100% GRATIS ya Kak, tanpa biaya admin atau bulanan.\n\n"
                . "Kakak rencananya mau sewakan barang, atau lagi cari barang buat disewa nih?";
        }

        // 4. Konfirmasi bergabung / pendaftaran toko vendor (secara spesifik)
        elseif (
            preg_match('/\b(mau daftar|minat daftar|boleh daftar|siap daftar|gas daftar|mau gabung|minat gabung|bantu daftar|daftarin)\b/i', $lower) ||
            (preg_match('/\b(mau|boleh|minat|siap|setuju)\b/i', $lower) && preg_match('/\b(buka toko|toko rental|mitra|vendor|sewakan|daftar|gabung)\b/i', $lower))
        ) {
            $reply = "Siap {$salutation}, mantap! Boleh infokan nama toko, kota, dan barang apa aja yang mau disewakan ya Kak.\n\n"
                . "Kalau ada foto produk atau pricelist, langsung kirim ke chat ini aja, nanti Tim Rentify yang bantu buatkan tokonya sampai aktif. 👍";
        }

        // 5. Pertanyaan cara daftar / gabung / menjadi mitra
        elseif (str_contains($lower, 'cara') || str_contains($lower, 'daftar') || str_contains($lower, 'gabung') || str_contains($lower, 'join') || str_contains($lower, 'mitra') || str_contains($lower, 'buka toko')) {
            $reply = "Halo {$salutation}, caranya gampang banget! Kakak bisa daftar di {$webUrl}/register, atau kirim info toko dan produknya ke sini biar Tim Rentify bantu inputkan sampai beres.\n\n"
                . "Kakak rencana mau sewakan barang apa nih?";
        }

        // 6. Keamanan / jaminan / KTP / hilang / rusak / syarat
        elseif (str_contains($lower, 'aman') || str_contains($lower, 'keamanan') || str_contains($lower, 'jaminan') || str_contains($lower, 'hilang') || str_contains($lower, 'rusak') || str_contains($lower, 'ktp') || str_contains($lower, 'syarat') || str_contains($lower, 'resiko')) {
            $reply = "Tenang Kak, aman banget kok. Calon penyewa di Rentify wajib verifikasi KTP resmi, dan sistem otomatis buatkan invoice serta surat perjanjian sewa resmi. Kakak juga bebas setujui atau tolak pesanan sewa.\n\n"
                . "Ada yang mau dipastikan lagi Kak?";
        }

        // 7. Ingin sewa barang / tanya stok produk
        elseif (str_contains($lower, 'sewa') || str_contains($lower, 'rental') || str_contains($lower, 'pinjam') || str_contains($lower, 'cari') || str_contains($lower, 'butuh') || str_contains($lower, 'katalog')) {
            $reply = "Halo {$salutation}! Kakak lagi cari atau butuh sewa barang apa nih dan buat di daerah mana? Biar kami bantu carikan di katalog Rentify ({$webUrl}). 😊";
        }

        // 8. Lokasi / kantor
        elseif (str_contains($lower, 'lokasi') || str_contains($lower, 'alamat') || str_contains($lower, 'kantor') || str_contains($lower, 'dimana') || str_contains($lower, 'daerah')) {
            $reply = "Halo {$salutation}, Rentify berbasis online di {$webUrl} dan menghubungkan mitra rental dengan penyewa di berbagai kota.\n\n"
                . "Kakak lagi di daerah mana nih, dan lagi butuh apa?";
        }

        // 9. Salam / sapaan / tes
        elseif (preg_match('/\b(halo|hai|hi|hei|p|ping|tes|test|assalamualaikum|pagi|siang|sore|malam|permisi)\b/i', $lower)) {
            $reply = "Halo {$salutation}! Selamat datang di Rentify. 😊\n\n"
                . "Ada yang bisa Tim Rentify bantu? Lagi butuh atau cari sewa barang apa nih Kak?";
        }

        // 10. Default fallback umum (santai, ramah, solutif)
        else {
            $reply = "Halo {$salutation}! Salam dari Tim Rentify ({$webUrl}). 😊\n\n"
                . "Ada yang bisa kami bantu? Boleh cerita Kakak lagi butuh atau mau sewa apa hari ini?";
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
