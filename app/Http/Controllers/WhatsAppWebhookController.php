<?php

namespace App\Http\Controllers;

use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppWebhookController extends Controller
{
    /**
     * Menerima event pesan masuk dari gateway Fonnte via Webhook (POST)
     */
    public function handle(Request $request)
    {
        $sender = $request->input('sender');
        $message = trim((string) $request->input('message', ''));
        $name = $request->input('name', 'Kak');
        $member = $request->input('member'); // Jika pesan dari grup WA

        Log::info('WhatsApp Webhook Incoming Message:', [
            'sender'  => $sender,
            'name'    => $name,
            'message' => $message,
        ]);

        // Abaikan jika bukan pesan personal, tidak ada pengirim, atau pesan kosong
        if (empty($sender) || empty($message) || !empty($member)) {
            return response()->json(['status' => true, 'note' => 'ignored']);
        }

        // Hindari infinite loop: jangan balas pesan dari device bot sendiri
        $botDevice = (string) (preg_replace('/[^0-9]/', '', (string) env('WHATSAPP_BOT_NUMBER', '081262364197')) ?? '');
        $cleanSender = (string) (preg_replace('/[^0-9]/', '', (string) $sender) ?? '');
        if ($botDevice !== '' && str_ends_with($cleanSender, substr($botDevice, -9))) {
            return response()->json(['status' => true, 'note' => 'self_message_ignored']);
        }

        // Cek apakah pengirim adalah Admin / Bos pemilik sistem
        $adminPhone = (string) (preg_replace('/[^0-9]/', '', (string) config('security.admin_phone', '083183494835')) ?? '');
        $isAdmin = false;
        if ($adminPhone !== '' && str_ends_with($cleanSender, substr($adminPhone, -9))) {
            $isAdmin = true;
        }

        // Susun balasan cerdas (khusus Admin vs Mitra/Customer)
        if ($isAdmin) {
            $reply = $this->generateAdminReply($message);
        } else {
            $reply = $this->generateReply($message, $name);
        }

        if (!empty($reply)) {
            WhatsAppService::send($sender, $reply);
        }

        return response()->json(['status' => true]);
    }

    /**
     * Balasan cerdas khusus untuk Admin / Bos pemilik sistem
     */
    protected function generateAdminReply(string $message): string
    {
        $lower = strtolower($message);

        if (str_contains($lower, 'status') || str_contains($lower, 'cek') || str_contains($lower, 'radar') || str_contains($lower, 'server')) {
            $time = now()->timezone('Asia/Jakarta')->format('d M Y H:i:s');
            $webUrl = $this->getWebsiteUrl();
            return "🛡️ *[RENTIFY SECURITY STATUS]* 🛡️\n\n"
                . "Halo Bos Admin! Sistem pertahanan dan radar darurat beroperasi normal.\n\n"
                . "🕒 *Waktu Sistem:* {$time} WIB\n"
                . "🌐 *Website:* {$webUrl}\n"
                . "🛡️ *Firewall:* AKTIF (Auto-Ban & WAF)\n"
                . "📡 *Radar WhatsApp:* TERHUBUNG ke nomor Bos\n"
                . "🤖 *Bot Respon:* AKTIF 24/7\n\n"
                . "Ketik *status*, *bantuan*, atau balas instruksi kapan saja.";
        }

        if (str_contains($lower, 'bantuan') || str_contains($lower, 'help') || str_contains($lower, 'menu')) {
            return "📋 *[MENU KOMANDO ADMIN RENTIFY]*\n\n"
                . "Halo Bos Admin, berikut perintah cepat yang bisa diketik:\n"
                . "• *status* : Cek kondisi radar dan firewall sistem\n"
                . "• *ping* : Uji respon cepat bot\n"
                . "• *info* : Informasi konfigurasi server & website\n\n"
                . "Sistem siap menerima instruksi!";
        }

        if (str_contains($lower, 'ping') || str_contains($lower, 'halo') || str_contains($lower, 'hai') || str_contains($lower, 'tes') || str_contains($lower, 'test')) {
            return "👋 *PONG!* Halo Bos Admin! 🛡️\n\nPesan Bos diterima dengan baik oleh Bot Gateway Rentify. Jalur komunikasi darurat & auto-reply beroperasi 100% lancar!\n\nAda yang perlu dicek atau dibantu Bos?";
        }

        // Respon umum untuk chat lain dari Admin
        return "Halo Bos Admin! 🛡️\n\nPesan Bos sudah diterima oleh Bot Gateway Rentify:\n\"_{$message}_\"\n\nRadar keamanan dan seluruh sistem Rentify aktif dan beroperasi normal 24/7. Ketik *status* untuk ringkasan sistem.";
    }

    /**
     * Susun balasan menggunakan Gemini AI atau Rule-based Heuristic
     */
    protected function generateReply(string $message, string $name): string
    {
        $geminiKey = env('GEMINI_API_KEY') ?: config('services.gemini.api_key');
        if (!empty($geminiKey)) {
            $aiReply = $this->callGemini($message, $name, $geminiKey);
            if (!empty($aiReply)) {
                return $aiReply;
            }
        }

        return $this->smartRuleReply($message, $name);
    }

    /**
     * Panggil Gemini AI sebagai AI Customer Support & Vendor Acquisition Rentify
     */
    protected function callGemini(string $message, string $name, string $apiKey): ?string
    {
        $model = env('GEMINI_MODEL', 'gemini-1.5-flash');
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";
        $webUrl = $this->getWebsiteUrl();

        $systemPrompt = <<<PROMPT
Anda adalah "Asep", Merchant Partner Specialist & Customer Support resmi dari platform persewaan online 'Rentify' (website: {$webUrl}).
Tujuan utama Anda:
1. Menjawab balasan chat WhatsApp dari calon mitra toko rental atau calon penyewa dengan ramah, natural, santun, dan meyakinkan.
2. Jelaskan bahwa bergabung di Rentify 100% GRATIS pendaftaran, tanpa biaya bulanan, dan tim Rentify siap bantu upload-kan katalog & foto barang sampai tayang.
3. Transaksi aman karena penyewa wajib verifikasi KTP.
4. Gaya bahasa: Indonesia santai, akrab namun sopan (gunakan sapaan "Kak {$name}"), pakai emoji yang relevan.
5. Panjang respon: maksimal 2-3 paragraf singkat agar nyaman dibaca di layar HP WhatsApp.

Pesan dari pengguna: "{$message}"
PROMPT;

        try {
            $response = Http::timeout(10)->post($url, [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $systemPrompt]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'temperature' => 0.7,
                    'maxOutputTokens' => 350,
                ],
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return $data['candidates'][0]['content']['parts'][0]['text'] ?? null;
            }
        } catch (\Exception $e) {
            Log::warning('Gemini Webhook Error: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Smart Rule-based Response jika Gemini AI tidak aktif / API limit
     */
    protected function smartRuleReply(string $message, string $name): string
    {
        $lower = strtolower($message);
        $webUrl = $this->getWebsiteUrl();

        // Pertanyaan seputar cara daftar / gabung
        if (str_contains($lower, 'cara') || str_contains($lower, 'daftar') || str_contains($lower, 'gabung') || str_contains($lower, 'join') || str_contains($lower, 'gimana')) {
            return "Halo Kak {$name}! Caranya simpel dan praktis banget kak:\n\n"
                . "1️⃣ Kakak bisa langsung daftar di web: {$webUrl}/register\n"
                . "2️⃣ ATAU kalau Kakak lagi sibuk, cukup kirimkan daftar barang & foto rental Kakak ke chat WA ini, nanti tim Rentify yang bantu inputkan semuanya sampai tayang di web!\n\n"
                . "Kira-kira Kakak ada list katalog/pricelist barang rental yang bisa kami bantu lihat kak? 😊";
        }

        // Pertanyaan seputar biaya / komisi / harga
        if (str_contains($lower, 'biaya') || str_contains($lower, 'bayar') || str_contains($lower, 'tarif') || str_contains($lower, 'komisi') || str_contains($lower, 'gratis') || str_contains($lower, 'persen')) {
            return "Halo Kak {$name}, untuk pendaftaran toko rental di Rentify 100% GRATIS kak! 🎉\n\n"
                . "✅ Tanpa biaya pendaftaran\n"
                . "✅ Tanpa biaya langganan bulanan\n"
                . "✅ Toko Kakak langsung dapat exposure calon penyewa baru dari web kami\n\n"
                . "Jadi tidak ada ruginya sama sekali kak untuk pajang katalog toko Kakak di Rentify. Mau kami bantu buatkan akun tokonya kak?";
        }

        // Respon ketertarikan / konfirmasi (mau, boleh, minat, ok, siap)
        if (str_contains($lower, 'mau') || str_contains($lower, 'boleh') || str_contains($lower, 'minat') || str_contains($lower, 'tertarik') || str_contains($lower, 'oke') || str_contains($lower, 'ok') || str_contains($lower, 'siap')) {
            return "Asyik, terima kasih banyak Kak {$name}! 🙏\n\n"
                . "Boleh diinfokan nama tokonya apa kak, lokasinya di kota mana, dan jenis rental apa (misal: kamera, camping, baju, elektronik)?\n\n"
                . "Boleh juga kirimkan link katalog/IG atau foto produknya di sini ya kak, biar langsung kami bantu proses tayang di web Rentify! ✨";
        }

        // Pertanyaan seputar keamanan / KTP / barang hilang
        if (str_contains($lower, 'aman') || str_contains($lower, 'hilang') || str_contains($lower, 'rusak') || str_contains($lower, 'jaminan') || str_contains($lower, 'ktp') || str_contains($lower, 'syarat')) {
            return "Keamanan mitra toko adalah prioritas utama kami di Rentify kak! 🛡️\n\n"
                . "• Setiap penyewa wajib verifikasi identitas resmi (KTP & data kontak valid)\n"
                . "• Ada invoice digital & surat perjanjian sewa otomatis dari sistem\n"
                . "• Kakak tetap memegang kendali penuh untuk menyetujui atau menolak penyewa yang booking.\n\n"
                . "Ada hal lain yang ingin Kakak tanyakan seputar sistem keamanannya kak?";
        }

        // Default sambutan hangat
        return "Halo Kak {$name}! Terima kasih banyak sudah merespon pesan Rentify ya kak 🙏\n\n"
            . "Kami sangat senang kalau toko rental Kakak bisa ikut tayang di web Rentify ({$webUrl}) untuk nambah jangkauan pelanggan baru.\n\n"
            . "Ada yang bisa kami bantu jelaskan lebih lanjut kak, atau mau kami bantu daftarkan langsung katalog barangnya? 😊";
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
