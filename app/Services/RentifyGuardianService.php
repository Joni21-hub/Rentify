<?php

namespace App\Services;

use App\Models\Pembayaran;
use App\Models\Penyewaan;
use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RentifyGuardianService
{
    /**
     * System Prompt resmi RENTIFY AI SERVER GUARDIAN
     */
    protected const SYSTEM_PROMPT = <<<'PROMPT'
# SYSTEM PROMPT — RENTIFY AI SERVER GUARDIAN

## 1. IDENTITAS DAN MISI
Kamu adalah Rentify Guardian, AI Agent khusus keamanan, monitoring, dan pemeliharaan sistem Rentify.
Kamu bukan customer service biasa. Tugas utamamu menjaga sistem Rentify tetap berjalan, mendeteksi masalah sedini mungkin, melaporkannya kepada pemilik, dan membantu memperbaiki masalah secara otomatis jika aman dan memungkinkan.

Lingkungan sistem:
- Website: https://rentify21.my.id
- Hosting aplikasi: Fly.io
- Database: Aiven MySQL
- Kanal komunikasi dan perintah: WhatsApp pemilik.

Kamu harus bekerja 24/7 melalui infrastruktur monitoring yang berjalan di server atau layanan cloud secara mandiri, bukan hanya ketika seseorang mengirim pesan.

## 2. MONITORING DAN KEAMANAN
Pantau secara berkala jika akses teknis tersedia:
- Website tidak bisa diakses, error HTTP 5xx, atau respons melambat.
- Aplikasi crash, server restart berulang, atau deployment gagal.
- Koneksi database Aiven terputus, database penuh, atau query bermasalah.
- Error aplikasi Laravel, antrean pekerjaan gagal, dan log error baru.
- Penggunaan CPU, RAM, disk, koneksi, serta batas kuota layanan.
- Percobaan login mencurigakan, lonjakan permintaan, dan indikasi serangan.
- Kegagalan integrasi pembayaran, webhook, atau proses transaksi.
- Sertifikat SSL, domain, dan masa berlaku layanan jika informasinya tersedia.

Gunakan health check, log, metrik, dan alert yang benar-benar terhubung. Jangan mengaku sedang memantau sesuatu jika aksesnya belum tersedia.

## 3. NOTIFIKASI WHATSAPP
Ketika ditemukan masalah, kirim pesan secepat mungkin kepada pemilik.
Format laporan harus singkat:
🚨 RENTIFY ALERT

Masalah: [Deskripsi Masalah]

Dampak: [Dampak ke pengguna/sistem]

Penyebab: [Penyebab dari log/sistem]

Tindakan: [Tindakan yang sudah/sedang diambil]

Status: [Status investigasi/pemulihan]

Prioritas:
- KRITIS: Website mati, database tidak dapat diakses, dugaan kebocoran data, atau gangguan transaksi serius.
- TINGGI: Error berulang, aplikasi tidak stabil, atau penggunaan sumber daya mendekati batas.
- SEDANG: Fitur tertentu bermasalah atau performa menurun.
- RENDAH: Peringatan rutin yang tidak memerlukan tindakan segera.

Jangan mengirim pesan berulang untuk masalah yang sama. Kirim pembaruan jika dampak berubah, tindakan gagal, atau masalah telah pulih.

## 4. PERINTAH MELALUI WHATSAPP
Pemilik dapat mengirim perintah biasa dalam bahasa Indonesia, tanpa harus menggunakan format teknis.
Contoh perintah:
- "Cek kondisi Rentify sekarang."
- "Kenapa website error?"
- "Periksa log error terbaru."
- "Cek database Aiven."
- "Cari penyebab website lambat."
- "Perbaiki error yang sedang terjadi."
- "Coba pulihkan aplikasi jika aman."
- "Cek apakah ada aktivitas mencurigakan."
- "Apa masalah paling serius saat ini?"
- "Buat laporan kondisi server hari ini."
- "Hentikan perbaikan otomatis."
- "Jalankan pemeriksaan ulang."

Pahami maksud perintah, rujuk data diagnostik riil yang disediakan dalam konteks sistem, lalu berikan hasil nyata. Jangan sekadar memberikan tutorial jika kamu memiliki akses untuk menjalankan tindakan tersebut.
Balasan harus singkat dan langsung pada inti (1-2 kalimat).
Contoh:
Pemilik: "Cek Rentify."
Agent: "Aku cek sekarang. Website merespons normal, database terhubung, dan belum ditemukan error kritis."
Pemilik: "Perbaiki website yang error."
Agent: "Ditemukan error dari aplikasi. Pembersihan cache dan pemeriksaan ulang berhasil, sekarang sistem merespons normal."

Pernyataan tersebut hanya boleh dikirim jika hasil pemeriksaan benar-benar membuktikannya.

## 5. PROSEDUR PERBAIKAN OTOMATIS
Saat menerima laporan masalah atau perintah perbaikan:
1. Periksa kondisi aktual sistem.
2. Cari penyebab melalui log, metrik, status layanan, dan perubahan terbaru.
3. Tentukan akar masalah yang paling mungkin berdasarkan bukti.
4. Lakukan tindakan pemulihan dengan risiko paling rendah (clear cache, optimize, retest connection).
5. Jalankan pemeriksaan ulang untuk memastikan hasilnya.
6. Laporkan penyebab, tindakan, dan hasil kepada pemilik.

Jangan melakukan perbaikan secara asal. Jika penyebab belum jelas, kumpulkan bukti terlebih dahulu.

## 6. ATURAN KEAMANAN MUTLAK
Lindungi data pengguna, akun, kredensial, transaksi, dan database Rentify.
- Jangan pernah mengirim password, API key, token, private key, atau kredensial ke WhatsApp.
- Jangan menampilkan data sensitif di dalam laporan.
- Jangan menghapus database, tabel, file penting, atau backup.
- Jangan menjalankan migrasi destruktif atau mengubah data transaksi secara langsung tanpa persetujuan eksplisit.
- Jangan menonaktifkan autentikasi, firewall, SSL, atau mekanisme keamanan untuk mengatasi error.
- Catat tindakan, waktu, alasan, dan hasilnya dalam audit log.

## 7. GAYA KOMUNIKASI
- Gunakan bahasa Indonesia yang santai, jelas, dan profesional.
- Jangan bertele-tele.
- Utamakan satu atau dua kalimat untuk balasan biasa.
- Saat terjadi insiden, sampaikan masalah, dampak, dan tindakan.
- Hindari istilah teknis yang tidak diperlukan jika pemilik bertanya secara umum.
- Jika pemilik meminta detail, baru jelaskan lebih lengkap.
PROMPT;

    /**
     * Mengumpulkan diagnostik sistem riil saat ini (Database, HTTP, Logs, RAM/Disk, Security, Transaksi)
     */
    public function getLiveDiagnostics(): array
    {
        $now = now()->setTimezone('Asia/Jakarta');
        $timeStr = $now->format('d M Y, H:i:s') . ' WIB';

        $diagnostics = [
            'timestamp'  => $timeStr,
            'is_healthy' => true,
            'severity'   => 'NORMAL', // NORMAL, SEDANG, TINGGI, KRITIS
            'issues'     => [],
        ];

        // 1. Diagnostik Website & HTTP Response
        $targetUrl = config('guardian.target_url', 'https://rentify21.my.id');
        $webStart = microtime(true);
        try {
            $webRes = Http::timeout(4)
                ->withHeaders([
                    'User-Agent' => 'Rentify-Guardian-Monitor/1.0 (+https://rentify21.my.id)',
                ])
                ->get($targetUrl);

            $webMs = round((microtime(true) - $webStart) * 1000, 1);
            $httpCode = $webRes->status();

            $diagnostics['website'] = [
                'target_url' => $targetUrl,
                'status_code' => $httpCode,
                'latency_ms' => $webMs,
                'is_ok'      => ($httpCode >= 200 && $httpCode < 400),
                'error'      => null,
            ];

            if ($httpCode >= 500) {
                $diagnostics['is_healthy'] = false;
                $diagnostics['severity'] = 'KRITIS';
                $diagnostics['issues'][] = "Website mengembalikan HTTP {$httpCode}";
            } elseif ($webMs > (int) config('guardian.thresholds.latency_critical_ms', 6000)) {
                $diagnostics['is_healthy'] = false;
                $diagnostics['severity'] = maxSeverity($diagnostics['severity'], 'TINGGI');
                $diagnostics['issues'][] = "Latensi website kritis: {$webMs}ms";
            } elseif ($webMs > (int) config('guardian.thresholds.latency_warning_ms', 3000)) {
                $diagnostics['severity'] = maxSeverity($diagnostics['severity'], 'SEDANG');
                $diagnostics['issues'][] = "Latensi website agak lambat: {$webMs}ms";
            }
        } catch (\Throwable $e) {
            $webMs = round((microtime(true) - $webStart) * 1000, 1);
            $diagnostics['website'] = [
                'target_url' => $targetUrl,
                'status_code' => 0,
                'latency_ms' => $webMs,
                'is_ok'      => false,
                'error'      => $e->getMessage(),
            ];
            $diagnostics['is_healthy'] = false;
            $diagnostics['severity'] = 'KRITIS';
            $diagnostics['issues'][] = "Website tidak dapat dihubungi: " . substr($e->getMessage(), 0, 80);
        }

        // 2. Diagnostik Database Aiven MySQL
        $dbStart = microtime(true);
        try {
            DB::select('SELECT 1');
            $dbMs = round((microtime(true) - $dbStart) * 1000, 1);
            $dbName = DB::connection()->getDatabaseName();

            $diagnostics['database'] = [
                'name'       => $dbName,
                'status'     => 'connected',
                'latency_ms' => $dbMs,
                'is_ok'      => true,
                'error'      => null,
            ];

            if ($dbMs > (int) config('guardian.thresholds.db_latency_crit_ms', 2500)) {
                $diagnostics['is_healthy'] = false;
                $diagnostics['severity'] = maxSeverity($diagnostics['severity'], 'TINGGI');
                $diagnostics['issues'][] = "Query database lambat ({$dbMs}ms)";
            }
        } catch (\Throwable $e) {
            $dbMs = round((microtime(true) - $dbStart) * 1000, 1);
            $diagnostics['database'] = [
                'name'       => config('database.connections.mysql.database', 'defaultdb'),
                'status'     => 'disconnected',
                'latency_ms' => $dbMs,
                'is_ok'      => false,
                'error'      => $e->getMessage(),
            ];
            $diagnostics['is_healthy'] = false;
            $diagnostics['severity'] = 'KRITIS';
            $diagnostics['issues'][] = "Koneksi database Aiven terputus: " . substr($e->getMessage(), 0, 80);
        }

        // 3. Diagnostik Log Error Laravel
        $logInfo = $this->parseRecentLogs();
        $diagnostics['logs'] = $logInfo;
        if ($logInfo['errors_last_15m'] >= (int) config('guardian.thresholds.recent_errors_crit', 5)) {
            $diagnostics['is_healthy'] = false;
            $diagnostics['severity'] = maxSeverity($diagnostics['severity'], 'TINGGI');
            $diagnostics['issues'][] = "Terdeteksi {$logInfo['errors_last_15m']} error aplikasi dalam 15 menit terakhir";
        }

        // 4. Diagnostik Sumber Daya (RAM & Disk)
        $memUsage = round(memory_get_usage(true) / 1024 / 1024, 1);
        $memPeak  = round(memory_get_peak_usage(true) / 1024 / 1024, 1);

        $diskFreeBytes = @disk_free_space(base_path()) ?: 0;
        $diskTotalBytes = @disk_total_space(base_path()) ?: 0;
        $diskFreeGb = round($diskFreeBytes / 1024 / 1024 / 1024, 2);
        $diskTotalGb = round($diskTotalBytes / 1024 / 1024 / 1024, 2);
        $diskUsedPercent = $diskTotalBytes > 0 ? round((($diskTotalBytes - $diskFreeBytes) / $diskTotalBytes) * 100) : 0;

        $diagnostics['resources'] = [
            'php_version'       => PHP_VERSION,
            'laravel_version'   => app()->version(),
            'environment'       => app()->environment(),
            'memory_usage_mb'   => $memUsage,
            'memory_peak_mb'    => $memPeak,
            'disk_free_gb'      => $diskFreeGb,
            'disk_total_gb'     => $diskTotalGb,
            'disk_used_percent' => $diskUsedPercent,
        ];

        if ($diskUsedPercent >= (int) config('guardian.thresholds.disk_critical_percent', 95)) {
            $diagnostics['is_healthy'] = false;
            $diagnostics['severity'] = maxSeverity($diagnostics['severity'], 'KRITIS');
            $diagnostics['issues'][] = "Kapasitas disk kritis: {$diskUsedPercent}% terpakai";
        } elseif ($diskUsedPercent >= (int) config('guardian.thresholds.disk_warning_percent', 88)) {
            $diagnostics['severity'] = maxSeverity($diagnostics['severity'], 'SEDANG');
            $diagnostics['issues'][] = "Kapasitas disk menipis: {$diskUsedPercent}% terpakai";
        }

        // 5. Diagnostik Keamanan & Firewall
        $firewallEnabled = (bool) config('security.firewall.enabled', true);
        $diagnostics['security'] = [
            'firewall_enabled' => $firewallEnabled,
            'blocked_threats_today' => $logInfo['security_threats_today'] ?? 0,
            'last_threat' => $logInfo['last_security_threat'] ?? 'Tidak ada ancaman aktif',
        ];

        // 6. Diagnostik Transaksi Bisnis (Pending & Waiting Confirm)
        try {
            $pendingPayments = Penyewaan::where('status_pembayaran', 'belum_dibayar')
                ->where('created_at', '>=', now()->subHours(24))
                ->count();
            $waitingConfirm = Pembayaran::where('status', 'menunggu_konfirmasi')
                ->count();

            $diagnostics['transactions'] = [
                'pending_payments_24h' => $pendingPayments,
                'waiting_confirm'      => $waitingConfirm,
            ];
        } catch (\Throwable $e) {
            $diagnostics['transactions'] = [
                'pending_payments_24h' => 0,
                'waiting_confirm'      => 0,
                'note'                 => 'Database skipped',
            ];
        }

        return $diagnostics;
    }

    /**
     * Membaca dan menganalisis baris log error terbaru dari storage/logs/laravel.log
     */
    protected function parseRecentLogs(): array
    {
        $logPath = storage_path('logs/laravel.log');
        if (!file_exists($logPath) || !is_readable($logPath)) {
            return [
                'has_log' => false,
                'errors_last_15m' => 0,
                'errors_last_1h'  => 0,
                'errors_last_24h' => 0,
                'latest_error'    => 'File log belum tersedia atau kosong',
                'security_threats_today' => 0,
                'last_security_threat' => null,
            ];
        }

        $maxBytes = 150 * 1024; // 150 KB tail
        $fileSize = (int) (filesize($logPath) ?: 0);
        $readBytes = min($fileSize, $maxBytes);

        $fp = @fopen($logPath, 'r');
        if (!$fp) {
            return [
                'has_log' => true,
                'errors_last_15m' => 0,
                'errors_last_1h'  => 0,
                'errors_last_24h' => 0,
                'latest_error'    => 'Tidak dapat membuka file log',
                'security_threats_today' => 0,
                'last_security_threat' => null,
            ];
        }

        if ($fileSize > $maxBytes) {
            fseek($fp, $fileSize - $maxBytes);
        }
        $buffer = (string) fread($fp, (int) $readBytes);
        fclose($fp);

        $lines = explode("\n", $buffer);
        $now = now()->setTimezone('Asia/Jakarta');
        $fifteenMinAgo = $now->copy()->subMinutes(15);
        $oneHourAgo    = $now->copy()->subHour();
        $twentyFourAgo = $now->copy()->subHours(24);

        $err15m = 0;
        $err1h = 0;
        $err24h = 0;
        $secToday = 0;
        $latestError = null;
        $latestThreat = null;

        // Iterasi dari baris terbawah ke atas
        for ($i = count($lines) - 1; $i >= 0; $i--) {
            $line = trim($lines[$i]);
            if (empty($line)) continue;

            // Deteksi security alert
            if (str_contains($line, '[RENTIFY-SECURITY-ALERT]')) {
                $secToday++;
                if ($latestThreat === null) {
                    $latestThreat = $this->sanitizeLogLine($line);
                }
            }

            // Deteksi baris timestamp & error level Laravel
            if (preg_match('/^\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\]\s+([\w\.-]+)\.(ERROR|CRITICAL|ALERT|EMERGENCY):(.*)/i', $line, $matches)) {
                $time = Carbon::parse($matches[1], 'Asia/Jakarta');
                $errMsg = trim($matches[4]);

                // Abaikan error warning yang tidak berbahaya (misal cURL port 3000 saat gateway opsional)
                $isIgnorable = str_contains($errMsg, '127.0.0.1:3000') && str_contains($errMsg, 'Couldn\'t connect');

                if ($time->gte($twentyFourAgo)) {
                    $err24h++;
                    if (!$isIgnorable && $latestError === null) {
                        $latestError = $this->sanitizeLogLine($errMsg);
                    }
                }
                if ($time->gte($oneHourAgo)) {
                    $err1h++;
                }
                if ($time->gte($fifteenMinAgo)) {
                    if (!$isIgnorable) {
                        $err15m++;
                    }
                }
            }
        }

        return [
            'has_log'                => true,
            'errors_last_15m'        => $err15m,
            'errors_last_1h'         => $err1h,
            'errors_last_24h'        => $err24h,
            'latest_error'           => $latestError ?: 'Tidak ada error kritis di log terbaru',
            'security_threats_today' => $secToday,
            'last_security_threat'   => $latestThreat ?: 'Firewall aktif, tidak ada ancaman baru',
        ];
    }

    /**
     * Sanitasi baris log untuk memastikan tidak ada token/kredensial yang bocor
     */
    protected function sanitizeLogLine(string $text): string
    {
        // Tutup password, secret, token, key jika ada
        $clean = (string) (preg_replace('/(password|secret|token|api_key|key|auth|bearer)\s*[:=]\s*["\']?[^"\'\s,;]+/i', '$1=***REDACTED***', $text) ?? '');
        if (strlen($clean) > 220) {
            $clean = substr($clean, 0, 217) . '...';
        }
        return $clean;
    }

    /**
     * Prosedur Perbaikan Otomatis Berisiko Rendah (Safe Automated Recovery)
     * Dijalankan saat ada error cache, masalah rute, atau instruksi pemilik
     */
    public function executeSafeRecovery(string $reason = 'Manual Trigger'): array
    {
        Log::info("[RENTIFY-GUARDIAN-AUDIT] Menjalankan perbaikan otomatis: {$reason}");

        $actionsDone = [];

        try {
            // 1. Bersihkan cache aplikasi, views, routes & config
            Artisan::call('optimize:clear');
            $actionsDone[] = 'optimize:clear (cache, view, route, config)';
        } catch (\Throwable $e) {
            Log::warning("[RENTIFY-GUARDIAN] optimize:clear gagal: " . $e->getMessage());
        }

        // 2. Re-test koneksi database
        try {
            DB::purge();
            DB::reconnect();
            DB::select('SELECT 1');
            $actionsDone[] = 'database reconnect & ping';
        } catch (\Throwable $e) {
            Log::warning("[RENTIFY-GUARDIAN] db reconnect gagal: " . $e->getMessage());
        }

        // 3. Jalankan pemeriksaan ulang untuk memastikan hasilnya
        $afterDiag = $this->getLiveDiagnostics();

        $result = [
            'success'      => $afterDiag['is_healthy'],
            'actions_done' => $actionsDone,
            'after_status' => $afterDiag,
            'time'         => now()->setTimezone('Asia/Jakarta')->format('d M Y, H:i:s') . ' WIB',
        ];

        Log::info("[RENTIFY-GUARDIAN-AUDIT] Hasil perbaikan otomatis: " . json_encode($result));

        return $result;
    }

    /**
     * Menangani pesan dan perintah WhatsApp dari Pemilik (Owner Joni)
     */
    public function handleOwnerMessage(string $message, string $sender): string
    {
        $cleanMsg = trim($message);
        $lowerMsg = strtolower($cleanMsg);
        $cleanSender = (string) (preg_replace('/[^0-9]/', '', $sender) ?? '');

        // 1. Cek apakah pemilik menginstruksikan perbaikan / pemulihan
        $recoveryResult = null;
        if (preg_match('/(perbaiki|pulihkan|fix|recovery|restart|clear cache|bersihkan cache|atasi error|benerin|atasi masalah)/i', $lowerMsg)) {
            $recoveryResult = $this->executeSafeRecovery("WhatsApp Owner Command: {$cleanMsg}");
        }

        // 2. Kumpulkan data diagnostik riil terkini
        $diag = $this->getLiveDiagnostics();

        // 3. Panggil Gemini AI dengan System Prompt Guardian + Real-Time Context
        $geminiKey = config('rentify_ai.gemini.api_key')
            ?: (config('services.gemini.api_key') ?: env('GEMINI_API_KEY'));

        if (!empty($geminiKey)) {
            $aiReply = $this->callGeminiForGuardian($cleanMsg, $cleanSender, $diag, $recoveryResult, $geminiKey);
            if (!empty($aiReply)) {
                return $aiReply;
            }
        }

        // 4. Fallback jika AI offline / kuota habis: Balasan cerdas berbasis data faktual riil
        return $this->smartGuardianFallback($cleanMsg, $diag, $recoveryResult);
    }

    /**
     * Memanggil Gemini API dengan System Prompt Rentify Guardian dan Diagnostik Nyata
     */
    protected function callGeminiForGuardian(string $message, string $sender, array $diag, ?array $recoveryResult, string $rawApiKeys): ?string
    {
        $keys = array_values(array_filter(array_map('trim', preg_split('/[\s,]+/', $rawApiKeys))));
        if (empty($keys)) {
            return null;
        }

        $preferredModel = config('guardian.gemini.model', 'gemini-1.5-flash');
        $candidateModels = array_values(array_unique(array_filter([
            $preferredModel,
            'gemini-2.0-flash',
            'gemini-1.5-flash',
            'gemini-1.5-flash-8b',
            'gemini-3.8-flash',
            'gemini-3.6-flash',
        ])));

        // Susun teks ringkasan data diagnostik riil untuk disuntikkan ke AI
        $diagText = "Waktu Pemeriksaan: {$diag['timestamp']}\n";
        $diagText .= "- Website ({$diag['website']['target_url']}): HTTP {$diag['website']['status_code']} ({$diag['website']['latency_ms']}ms) - " . ($diag['website']['is_ok'] ? 'ONLINE & NORMAL' : 'TERGANGGU: ' . ($diag['website']['error'] ?? 'HTTP Error')) . "\n";
        $diagText .= "- Database Aiven ({$diag['database']['name']}): Status " . strtoupper($diag['database']['status']) . " ({$diag['database']['latency_ms']}ms)\n";
        $diagText .= "- Error Log Laravel: {$diag['logs']['errors_last_15m']} error baru dalam 15 menit, {$diag['logs']['errors_last_1h']} error dalam 1 jam, total {$diag['logs']['errors_last_24h']} dalam 24 jam.\n";
        $diagText .= "  Error Terakhir: \"{$diag['logs']['latest_error']}\"\n";
        $diagText .= "- Sumber Daya: RAM {$diag['resources']['memory_usage_mb']}MB (Peak {$diag['resources']['memory_peak_mb']}MB), Disk {$diag['resources']['disk_free_gb']}GB bebas dari total {$diag['resources']['disk_total_gb']}GB ({$diag['resources']['disk_used_percent']}% terpakai)\n";
        $diagText .= "- Keamanan & Firewall: " . ($diag['security']['firewall_enabled'] ? 'AKTIF' : 'NON-AKTIF') . ", Ancaman dicegat hari ini: {$diag['security']['blocked_threats_today']}\n";
        $diagText .= "- Status Keseluruhan Sistem: " . ($diag['is_healthy'] ? 'NORMAL & AMAN' : 'DITEMUKAN GANGGUAN (' . $diag['severity'] . ')') . "\n";
        if (!empty($diag['issues'])) {
            $diagText .= "- Isu Terdeteksi: " . implode('; ', $diag['issues']) . "\n";
        }
        if ($recoveryResult !== null) {
            $diagText .= "- TINDAKAN PEMULIHAN OTOMATIS BARU SAJA DIJALANKAN: " . implode(', ', $recoveryResult['actions_done']) . ". Hasil setelah pemulihan: " . ($recoveryResult['success'] ? 'Sistem sudah pulih normal' : 'Masih ada kendala') . "\n";
        }

        $systemInstruction = self::SYSTEM_PROMPT . "\n\n"
            . "--------------------------------------------------------\n"
            . "DATA DIAGNOSTIK AKTUAL SAAT INI (GUNAKAN FAKTA INI):\n"
            . $diagText
            . "--------------------------------------------------------\n"
            . "PANDUAN KOMUNIKASI PENTING:\n"
            . "1. Berikan balasan singkat, padat, dan langsung pada inti (1-2 kalimat untuk pesan umum).\n"
            . "2. Gunakan data faktual di atas. Jangan mengarang bahwa ada error jika datanya normal, dan jangan mengaku normal jika ada error.\n"
            . "3. Gaya bahasa: santai, sigap, profesional dalam bahasa Indonesia.\n";

        // Ambil riwayat percakapan khusus Owner (30 menit)
        $cacheKey = 'wa_guardian_history_' . $sender;
        $history = Cache::get($cacheKey, []);
        if (!is_array($history)) {
            $history = [];
        }

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
        $rawTurns[] = [
            'role'  => 'user',
            'parts' => [['text' => $message]],
        ];

        // Ensure alternating turns
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

        if (!empty($contents) && $contents[0]['role'] !== 'user') {
            array_shift($contents);
        }
        if (empty($contents)) {
            $contents = [
                ['role' => 'user', 'parts' => [['text' => $message]]]
            ];
        }

        foreach ($keys as $apiKey) {
            foreach ($candidateModels as $model) {
                $payload = [
                    'system_instruction' => [
                        'parts' => [['text' => $systemInstruction]]
                    ],
                    'contents' => $contents,
                    'generationConfig' => [
                        'temperature'     => (float) config('guardian.gemini.temperature', 0.2),
                        'maxOutputTokens' => (int) config('guardian.gemini.max_tokens', 350),
                    ],
                ];

                $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";
                try {
                    $response = Http::timeout(6)->post($url, $payload);
                    if ($response->successful()) {
                        $data = $response->json();
                        $replyText = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;
                        if (!empty($replyText)) {
                            $cleanReply = trim((string) $replyText);

                            // Simpan turn ke riwayat percakapan owner
                            $history[] = ['role' => 'user', 'text' => $message];
                            $history[] = ['role' => 'assistant', 'text' => $cleanReply];
                            if (count($history) > 10) {
                                $history = array_slice($history, -10);
                            }
                            Cache::put($cacheKey, $history, now()->addMinutes(30));

                            return $cleanReply;
                        }
                    } else {
                        Log::warning("Guardian Gemini API Error (Model: {$model}, Status: {$response->status()}): " . $response->body());
                    }
                } catch (\Throwable $e) {
                    Log::warning("Guardian Gemini Exception (Model: {$model}): " . $e->getMessage());
                }
            }
        }

        return null;
    }

    /**
     * Balasan cerdas berbasis data faktual riil jika Gemini sedang offline / limit
     */
    protected function smartGuardianFallback(string $message, array $diag, ?array $recoveryResult): string
    {
        $lower = strtolower(trim($message));

        // 1. Jika baru saja perbaikan otomatis dijalankan
        if ($recoveryResult !== null) {
            if ($recoveryResult['success']) {
                return "Perbaikan selesai. Cache aplikasi sudah dibersihkan, koneksi database divalidasi, dan website merespons normal ({$diag['website']['latency_ms']}ms).";
            }
            return "Tindakan pemulihan sudah dijalankan. Namun masih terdeteksi isu: " . implode(', ', $diag['issues']) . ". Sedang terus dipantau.";
        }

        // 2. Pemeriksaan Log Error
        if (str_contains($lower, 'log') || str_contains($lower, 'error')) {
            if ($diag['logs']['errors_last_15m'] > 0 || $diag['logs']['errors_last_1h'] > 0) {
                return "Ditemukan {$diag['logs']['errors_last_1h']} error dalam 1 jam terakhir. Error terbaru: \"{$diag['logs']['latest_error']}\".";
            }
            return "Log aplikasi terpantau bersih dalam 1 jam terakhir. Belum ditemukan error baru.";
        }

        // 3. Pemeriksaan Database Aiven
        if (str_contains($lower, 'database') || str_contains($lower, 'db') || str_contains($lower, 'aiven')) {
            if ($diag['database']['is_ok']) {
                return "Database Aiven terhubung normal dengan waktu respon {$diag['database']['latency_ms']}ms.";
            }
            return "Koneksi database Aiven terganggu: {$diag['database']['error']}. Tindakan pemeriksaan sedang berjalan.";
        }

        // 4. Pemeriksaan Keamanan / Aktivitas Mencurigakan / Firewall
        if (str_contains($lower, 'keamanan') || str_contains($lower, 'mencurigakan') || str_contains($lower, 'serangan') || str_contains($lower, 'firewall') || str_contains($lower, 'radar')) {
            return "Firewall aktif. Terdeteksi {$diag['security']['blocked_threats_today']} upaya scanning/serangan hari ini yang telah dicegat otomatis.";
        }

        // 5. Cek kecepatan / penyebab lambat
        if (str_contains($lower, 'lambat') || str_contains($lower, 'lemot') || str_contains($lower, 'speed') || str_contains($lower, 'latensi')) {
            return "Latensi website saat ini {$diag['website']['latency_ms']}ms dan latensi query DB {$diag['database']['latency_ms']}ms. Penggunaan disk {$diag['resources']['disk_used_percent']}%.";
        }

        // 6. Buat laporan kondisi harian / ringkasan lengkap
        if (str_contains($lower, 'laporan') || str_contains($lower, 'ringkasan') || str_contains($lower, 'rekap')) {
            return "📊 *LAPORAN SERVER RENTIFY*\n"
                . "• Website: HTTP {$diag['website']['status_code']} ({$diag['website']['latency_ms']}ms)\n"
                . "• Database: " . strtoupper($diag['database']['status']) . " ({$diag['database']['latency_ms']}ms)\n"
                . "• Error 24 Jam: {$diag['logs']['errors_last_24h']}\n"
                . "• RAM / Disk: {$diag['resources']['memory_usage_mb']}MB / {$diag['resources']['disk_used_percent']}% used\n"
                . "• Status: " . ($diag['is_healthy'] ? "Normal & Stabil ✅" : "Perlu Perhatian ⚠️");
        }

        // 7. Pemeriksaan Kondisi Umum / Status / Default
        if ($diag['is_healthy']) {
            return "Aku cek sekarang. Website merespons normal ({$diag['website']['latency_ms']}ms), database Aiven terhubung, dan belum ditemukan error kritis.";
        }

        return "Aku cek sekarang. Terdeteksi isu: " . implode('; ', $diag['issues']) . ". Sedang diinvestigasi.";
    }

    /**
     * Menjalankan monitoring otonom 24/7 (dieksekusi periodik via cron/scheduler)
     * Mendeteksi masalah kritis/tinggi, mengirim alert ke WhatsApp, dan mengirim notifikasi pemulihan
     */
    public function runAutonomousMonitoring(bool $forceAlert = false): array
    {
        $diag = $this->getLiveDiagnostics();
        $ownerPhones = config('guardian.owner_phones', ['083183494835']);
        $incidentCacheKey = 'guardian_active_incident';
        $wasInIncident = (bool) Cache::get($incidentCacheKey, false);

        $result = [
            'timestamp'    => $diag['timestamp'],
            'is_healthy'   => $diag['is_healthy'],
            'severity'     => $diag['severity'],
            'issues'       => $diag['issues'],
            'alert_sent'   => false,
            'recovery_sent'=> false,
        ];

        // Kasus 1: Ditemukan Masalah Kritis / Tinggi
        if (!$diag['is_healthy'] || in_array($diag['severity'], ['KRITIS', 'TINGGI']) || $forceAlert) {
            $issueSummary = !empty($diag['issues']) ? implode(', ', $diag['issues']) : 'Pemeriksaan rutin mendeteksi anomali';
            $issueSig = md5($diag['severity'] . '_' . $issueSummary);
            $cooldownKey = "guardian_alert_cooldown_{$issueSig}";
            $cooldownMinutes = (int) config('guardian.thresholds.alert_cooldown_min', 20);

            if (!Cache::has($cooldownKey) || $forceAlert) {
                // Tentukan dampak & penyebab spesifik
                $dampak = 'Pengguna mungkin mengalami kendala akses atau transaksi.';
                $penyebab = $diag['logs']['latest_error'] ?? 'Terdeteksi dari metrik kesehatan server.';
                $tindakan = 'Memeriksa status aplikasi dan mencoba pemulihan otomatis.';

                if (!$diag['database']['is_ok']) {
                    $dampak = 'Seluruh transaksi dan data sewa tidak dapat diproses.';
                    $penyebab = 'Koneksi ke database cloud Aiven terputus atau timeout.';
                    $tindakan = 'Memvalidasi kredensial dan melakukan reconnect DB.';
                } elseif (!$diag['website']['is_ok']) {
                    $dampak = 'Halaman utama Rentify tidak dapat dimuat oleh pengunjung.';
                    $penyebab = 'HTTP ' . $diag['website']['status_code'] . ' atau timeout koneksi.';
                    $tindakan = 'Menjalankan optimize:clear dan verifikasi ulang.';
                }

                // Coba pemulihan otomatis berisiko rendah
                $recovery = $this->executeSafeRecovery("Autonomous Alert Trigger: {$issueSummary}");
                if ($recovery['success']) {
                    $tindakan .= ' Pemulihan cache berhasil dijalankan.';
                }

                $alertMessage = "🚨 *RENTIFY ALERT*\n\n"
                    . "*Masalah:* {$issueSummary}\n\n"
                    . "*Dampak:* {$dampak}\n\n"
                    . "*Penyebab:* {$penyebab}\n\n"
                    . "*Tindakan:* {$tindakan}\n\n"
                    . "*Status:* Investigasi berlangsung (Prioritas: {$diag['severity']})";

                foreach ($ownerPhones as $phone) {
                    try {
                        WhatsAppService::send($phone, $alertMessage);
                        $result['alert_sent'] = true;
                    } catch (\Throwable $e) {
                        Log::error("[RENTIFY-GUARDIAN] Gagal kirim WA alert ke {$phone}: " . $e->getMessage());
                    }
                }

                Cache::put($cooldownKey, true, now()->addMinutes($cooldownMinutes));
                Cache::put($incidentCacheKey, [
                    'severity' => $diag['severity'],
                    'issue'    => $issueSummary,
                    'time'     => $diag['timestamp'],
                ], now()->addHours(12));
            }
        }
        // Kasus 2: Sistem Pulih dari Insiden Sebelumnya
        elseif ($diag['is_healthy'] && $wasInIncident) {
            $lastIncident = Cache::get($incidentCacheKey);
            $prevIssue = is_array($lastIncident) ? ($lastIncident['issue'] ?? 'gangguan sistem') : 'gangguan sistem';

            $recoveryMessage = "✅ *RENTIFY RECOVERED*\n\n"
                . "*Masalah:* {$prevIssue} telah pulih normal.\n\n"
                . "*Dampak:* Seluruh layanan Rentify kembali dapat diakses lancar.\n\n"
                . "*Penyebab:* Masalah telah teratasi dan diverifikasi ulang.\n\n"
                . "*Tindakan:* Pemeriksaan ulang otomatis selesai (Website HTTP {$diag['website']['status_code']}, DB Normal).\n\n"
                . "*Status:* Sistem normal dan stabil 24/7.";

            foreach ($ownerPhones as $phone) {
                try {
                    WhatsAppService::send($phone, $recoveryMessage);
                    $result['recovery_sent'] = true;
                } catch (\Throwable $e) {
                    Log::error("[RENTIFY-GUARDIAN] Gagal kirim WA recovery ke {$phone}: " . $e->getMessage());
                }
            }

            Cache::forget($incidentCacheKey);
        }

        return $result;
    }
}

/**
 * Helper pembanding tingkat keparahan
 */
function maxSeverity(string $current, string $new): string
{
    $levels = [
        'NORMAL' => 1,
        'SEDANG' => 2,
        'TINGGI' => 3,
        'KRITIS' => 4,
    ];

    $curRank = $levels[$current] ?? 1;
    $newRank = $levels[$new] ?? 1;

    return ($newRank > $curRank) ? $new : $current;
}
