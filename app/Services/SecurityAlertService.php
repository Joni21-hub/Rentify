<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SecurityAlertService
{
    /**
     * Mengirim notifikasi keamanan otomatis ke WhatsApp Admin melalui WhatsApp Gateway
     * Dilengkapi mekanisme anti-flood cooldown agar HP Admin tidak ter-spam.
     *
     * @param string $threatType    Jenis ancaman (contoh: 'Brute Force Login', 'Path Scanning', 'SQL Injection')
     * @param string $description   Deskripsi rincian insiden
     * @param array  $context       Data pendukung: ip, url, target, action
     * @return bool
     */
    public static function notify(string $threatType, string $description, array $context = []): bool
    {
        $ip     = $context['ip'] ?? request()->ip() ?? 'UNKNOWN_IP';
        $url    = $context['url'] ?? request()->fullUrl() ?? '-';
        $target = $context['target'] ?? '-';
        $action = $context['action'] ?? 'Akses otomatis diblokir (403 Forbidden)';
        $agent  = $context['user_agent'] ?? request()->userAgent() ?? '-';

        // Selalu catat insiden ke log sistem Laravel
        Log::warning("[RENTIFY-SECURITY-ALERT] {$threatType} | IP: {$ip} | URL: {$url} | Action: {$action} | Details: {$description}");

        // Cek target nomor WhatsApp admin
        $adminPhone = config('security.admin_phone', env('ADMIN_SECURITY_PHONE', '083183494835'));
        if (empty($adminPhone)) {
            return false;
        }

        // Kunci cache untuk cooldown alert per IP dan per jenis ancaman
        $cooldownMinutes = (int) config('security.firewall.alert_cooldown_minutes', 10);
        $signature = md5($threatType . '_' . $ip);
        $cooldownKey = "sec_alert_cooldown_{$signature}";
        $counterKey  = "sec_alert_counter_{$signature}";

        // Jika masih dalam masa cooldown, simpan hitungan serangan dan jangan kirim WA berulang kali
        if (Cache::has($cooldownKey)) {
            try {
                Cache::increment($counterKey);
            } catch (\Throwable $e) {
                // Ignore cache increment errors
            }
            return false;
        }

        // Pasang cooldown ke cache
        try {
            Cache::put($cooldownKey, true, now()->addMinutes($cooldownMinutes));
        } catch (\Throwable $e) {
            // Ignore cache errors
        }

        // Ambil akumulasi serangan sebelumnya jika ada
        $previousAttempts = 0;
        try {
            $previousAttempts = (int) Cache::pull($counterKey, 0);
        } catch (\Throwable $e) {
            $previousAttempts = 0;
        }

        // Format waktu WIB
        $timeStr = now()->setTimezone('Asia/Jakarta')->format('d M Y, H:i:s') . ' WIB';

        // Potong User Agent jika terlalu panjang
        $shortAgent = strlen($agent) > 65 ? substr($agent, 0, 62) . '...' : $agent;

        // Susun pesan WhatsApp alert keamanan resmi
        $pesan = "*RENTIFY SECURITY ALERT*\n\n"
               . "• *Ancaman:* {$threatType}\n"
               . "• *Waktu:* {$timeStr}\n"
               . "• *IP:* `{$ip}`\n"
               . "• *Endpoint:* {$url}\n";

        if ($target !== '-') {
            $pesan .= "• *Target/Input:* {$target}\n";
        }

        $pesan .= "• *User-Agent:* {$shortAgent}\n"
               . "• *Tindakan Otomatis:* {$action}\n\n"
               . "• *Keterangan:* {$description}\n";

        if ($previousAttempts > 0) {
            $pesan .= "\n*Catatan:* Terdeteksi {$previousAttempts}x serangan berulang dari IP ini yang dicegat otomatis.\n";
        }

        $pesan .= "\n---\n_Sistem Pertahanan Otonom Rentify (rentify21.my.id)_";

        try {
            $res = WhatsAppService::send($adminPhone, $pesan);
            Log::info("[RENTIFY-SECURITY-ALERT] WhatsApp Alert terkirim ke {$adminPhone}", ['result' => $res]);
            return true;
        } catch (\Throwable $e) {
            Log::error("[RENTIFY-SECURITY-ALERT] Gagal mengirim alert ke WhatsApp: " . $e->getMessage());
            return false;
        }
    }
}
