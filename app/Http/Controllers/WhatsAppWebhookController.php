<?php

namespace App\Http\Controllers;

use App\Services\WhatsAppService;
use Illuminate\Http\Request;
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

        // Susun balasan formal dan manusiawi layaknya Admin Customer Service resmi
        $reply = $this->generateReply($message, $name);

        if (!empty($reply)) {
            WhatsAppService::send($sender, $reply);
        }

        return response()->json(['status' => true]);
    }

    /**
     * Susun balasan Customer Service Rentify (Formal & Manusiawi)
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
     * Balasan AI formal (jika API key tersedia) - bersikap 100% sebagai Admin CS manusia
     */
    protected function callGemini(string $message, string $name, string $apiKey): ?string
    {
        $model = env('GEMINI_MODEL', 'gemini-1.5-flash');
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";
        $webUrl = $this->getWebsiteUrl();

        $systemPrompt = <<<PROMPT
Anda adalah Admin Customer Service resmi dari platform persewaan online 'Rentify' (website: {$webUrl}).
Aturan penting:
1. Bersikaplah 100% sebagai admin manusia yang formal, ramah, sopan, dan solutif.
2. JANGAN PERNAH menyebut diri Anda sebagai AI, bot, robot, asisten virtual, atau sistem otomatis.
3. JANGAN menggunakan istilah teknis seperti webhook, server, database, radar, firewall, atau prompt.
4. Gunakan bahasa Indonesia baku, sopan, dan bersahabat. Sapa dengan "Kak" atau nama pengirim jika wajar.
5. Pendaftaran toko rental di Rentify 100% GRATIS tanpa biaya langganan bulanan. Tim admin Rentify siap membantu mendaftarkan dan mengunggah katalog produk mitra sampai tayang di website.
6. Transaksi penyewaan aman karena penyewa wajib melengkapi verifikasi identitas resmi (KTP).
7. Panjang balasan: Singkat, padat, dan nyaman dibaca di WhatsApp (1-2 paragraf pendek).
PROMPT;

        try {
            $response = Http::timeout(10)->post($url, [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $systemPrompt . "\n\nPesan dari pengguna: \"{$message}\""]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'temperature' => 0.5,
                    'maxOutputTokens' => 300,
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
     * Template balasan formal Customer Service manusia
     */
    protected function smartRuleReply(string $message, string $name): string
    {
        $lower = strtolower(trim($message));
        $webUrl = $this->getWebsiteUrl();

        // Tentukan sapaan waktu berdasarkan jam WIB
        $hour = (int) now()->timezone('Asia/Jakarta')->format('H');
        if ($hour >= 4 && $hour < 11) {
            $timeGreeting = 'Selamat pagi';
        } elseif ($hour >= 11 && $hour < 15) {
            $timeGreeting = 'Selamat siang';
        } elseif ($hour >= 15 && $hour < 18) {
            $timeGreeting = 'Selamat sore';
        } else {
            $timeGreeting = 'Selamat malam';
        }

        // Format panggilan sopan
        $salutation = 'Kak';
        if (!empty($name) && strtolower($name) !== 'kak' && strlen($name) <= 25 && !preg_match('/[0-9]{5,}/', $name)) {
            $salutation = "Kak {$name}";
        }

        // 1. Biaya / tarif / komisi / harga / gratis / bayar
        if (preg_match('/\b(biaya|bayar|tarif|harga|komisi|gratis|free|potongan|persen)\b/i', $lower)) {
            return "Halo {$salutation}, untuk bergabung dan membuka toko rental di Rentify 100% GRATIS Kak.\n\n"
                . "• Tidak ada biaya pendaftaran\n"
                . "• Tidak ada biaya langganan bulanan\n"
                . "• Toko Kakak langsung mendapatkan jangkauan calon penyewa baru melalui website {$webUrl}\n\n"
                . "Apakah Kakak berkenan untuk kami bantu buatkan akun tokonya sekarang?";
        }

        // 2. Minat / Konfirmasi bergabung (mau, boleh, minat, tertarik, siap, oke)
        if (preg_match('/\b(mau|boleh|minat|tertarik|oke|ok|siap|bisa|gas|ayok|ayo)\b/i', $lower)) {
            return "Baik {$salutation}, terima kasih banyak atas kesediaannya. 🙏\n\n"
                . "Untuk kami bantu proses pendaftaran dan penayangan produknya, boleh kami minta informasi berikut ya Kak:\n"
                . "1. Nama toko rental Kakak\n"
                . "2. Lokasi kota / domisili toko\n"
                . "3. Jenis barang yang disewakan (misal: kamera, alat camping, baju/kostum, alat pesta, dsb)\n\n"
                . "Jika ada foto produk atau daftar harga (pricelist), silakan langsung dikirimkan ke chat ini ya Kak. Nanti tim admin kami yang bantu inputkan semuanya sampai tayang di web Rentify.";
        }

        // 3. Pertanyaan cara daftar / gabung / menjadi mitra
        if (str_contains($lower, 'cara') || str_contains($lower, 'daftar') || str_contains($lower, 'gabung') || str_contains($lower, 'join') || str_contains($lower, 'mitra') || str_contains($lower, 'buka toko') || str_contains($lower, 'gimana')) {
            return "Halo {$salutation}, untuk bergabung dan membuka toko rental di Rentify caranya sangat mudah Kak:\n\n"
                . "1. Kakak bisa mendaftar mandiri secara gratis melalui website kami di:\n"
                . "👉 {$webUrl}/register\n\n"
                . "2. Atau jika Kakak ingin lebih praktis, cukup kirimkan nama toko, lokasi, dan daftar barang rental di chat ini. Tim admin kami yang akan bantu inputkan semuanya sampai katalog toko Kakak aktif dan siap disewa.\n\n"
                . "Apakah saat ini Kakak sudah ada daftar barang rental yang ingin dibantu tayangkan?";
        }

        // 4. Keamanan / jaminan / KTP / hilang / rusak / syarat
        if (str_contains($lower, 'aman') || str_contains($lower, 'keamanan') || str_contains($lower, 'jaminan') || str_contains($lower, 'hilang') || str_contains($lower, 'rusak') || str_contains($lower, 'ktp') || str_contains($lower, 'syarat') || str_contains($lower, 'resiko')) {
            return "Halo {$salutation}, keamanan mitra toko rental adalah prioritas utama kami di Rentify.\n\n"
                . "Sistem perlindungan kami mencakup:\n"
                . "• Verifikasi Identitas: Calon penyewa wajib memverifikasi KTP dan data kontak resmi sebelum bertransaksi.\n"
                . "• Surat Perjanjian & Invoice: Sistem otomatis menerbitkan bukti sewa dan surat perjanjian resmi untuk setiap pesanan.\n"
                . "• Kendali Pemilik: Kakak memiliki hak penuh untuk menyetujui atau menolak pesanan yang masuk.\n\n"
                . "Ada yang ingin Kakak ketahui lebih lanjut mengenai sistem keamanannya?";
        }

        // 5. Ingin sewa barang / tanya stok produk
        if (str_contains($lower, 'sewa') || str_contains($lower, 'rental') || str_contains($lower, 'pinjam') || str_contains($lower, 'cari') || str_contains($lower, 'butuh') || str_contains($lower, 'katalog')) {
            return "Halo {$salutation}, untuk melihat katalog barang yang siap disewa, Kakak bisa langsung mengunjungi website resmi kami di:\n"
                . "👉 {$webUrl}\n\n"
                . "Atau boleh diinfokan barang rental apa yang sedang Kakak butuhkan serta lokasi kotanya, agar bisa kami bantu carikan mitra rental terdekat?";
        }

        // 6. Lokasi / kantor
        if (str_contains($lower, 'lokasi') || str_contains($lower, 'alamat') || str_contains($lower, 'kantor') || str_contains($lower, 'dimana') || str_contains($lower, 'daerah')) {
            return "Halo {$salutation}, Rentify adalah platform persewaan online yang melayani berbagai kota di Indonesia melalui website {$webUrl}.\n\n"
                . "Kami menghubungkan pemilik usaha rental lokal dengan calon penyewa terdekat. Ada yang bisa kami bantu terkait kebutuhan rental di area Kakak?";
        }

        // 7. Salam / sapaan / tes (Halo, Hai, P, Tes, Selamat pagi/siang/sore/malam, Assalamualaikum)
        if (preg_match('/\b(halo|hai|hi|hei|p|ping|tes|test|assalamualaikum|pagi|siang|sore|malam|permisi)\b/i', $lower)) {
            return "{$timeGreeting} {$salutation}! Terima kasih sudah menghubungi Rentify. 🙏\n\n"
                . "Perkenalkan saya admin Customer Service Rentify. Ada yang bisa kami bantu seputar kebutuhan sewa barang atau pendaftaran toko rental Kakak?";
        }

        // 8. Default fallback umum (formal, sopan, manusiawi)
        return "{$timeGreeting} {$salutation}, terima kasih telah menghubungi Rentify. 🙏\n\n"
            . "Pesan Kakak sudah kami terima dengan baik. Ada yang bisa kami bantu terkait layanan persewaan barang atau pendaftaran toko rental di Rentify ({$webUrl})?";
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
