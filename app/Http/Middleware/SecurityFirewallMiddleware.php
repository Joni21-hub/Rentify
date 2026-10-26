<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Services\SecurityAlertService;
use Symfony\Component\HttpFoundation\Response;

class SecurityFirewallMiddleware
{
    /**
     * Webhook atau path publik yang dikecualikan dari pemindaian string agresif & blokir IP
     */
    protected array $excludedPaths = [
        'api/webhook/mutasi',
        'webhook/whatsapp',
        'up',
        'api/health',
    ];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Cek apakah firewall diaktifkan
        if (!config('security.firewall.enabled', true)) {
            return $next($request);
        }

        $ip = $request->ip() ?? 'UNKNOWN_IP';
        $path = ltrim($request->path(), '/');
        $uri = $request->getRequestUri();

        // 2. Lewati jalur yang dikecualikan (webhook payment gateway, WhatsApp bot & health check)
        foreach ($this->excludedPaths as $exempt) {
            if ($path === $exempt || str_starts_with($path, $exempt)) {
                return $next($request);
            }
        }

        // Lewati file statis umum
        if (preg_match('/\.(css|js|jpg|jpeg|png|gif|svg|webp|ico|woff|woff2|ttf|map)$/i', $path)) {
            return $next($request);
        }

        // 3. Cek apakah IP sudah berada dalam daftar blokir sementara
        $banKey = "sec_firewall_banned_{$ip}";
        if (Cache::has($banKey)) {
            $banReason = Cache::get($banKey, 'Aktivitas mencurigakan terdeteksi sebelumnya.');
            return $this->blockResponse($request, 'IP Anda telah diblokir sementara karena terdeteksi melakukan aktivitas mencurigakan.', $banReason, $ip);
        }

        // 4. Deteksi Pemindaian Berbahaya (Path Scanner & Vulnerability Probing)
        $scannerThreat = $this->detectPathScanning($path, $uri);
        if ($scannerThreat !== null) {
            $this->applyBanAndAlert(
                $ip,
                'Pemindaian Path / Exploit Probing Terlarang',
                "Upaya pemindaian file/rute sensitif: '{$path}' (Pola: {$scannerThreat})",
                ['ip' => $ip, 'url' => $request->fullUrl(), 'target' => $path]
            );

            return $this->blockResponse($request, 'Akses Ditolak: Percobaan pemindaian rute sensitif terdeteksi.', $scannerThreat, $ip);
        }

        // 5. Deteksi Injeksi Payload Berbahaya (SQLi / XSS / Remote Code Execution)
        $payloadThreat = $this->detectMaliciousPayload($request);
        if ($payloadThreat !== null) {
            $this->applyBanAndAlert(
                $ip,
                'Percobaan Injeksi Berbahaya (SQLi / XSS)',
                "Terdeteksi payload berbahaya pada input permintaan (Parameter: '{$payloadThreat['param']}', Pola: {$payloadThreat['rule']})",
                [
                    'ip' => $ip,
                    'url' => $request->fullUrl(),
                    'target' => "Param: {$payloadThreat['param']} = " . substr($payloadThreat['sample'], 0, 100)
                ]
            );

            return $this->blockResponse($request, 'Akses Ditolak: Permintaan mengandung karakter atau script yang tidak diizinkan.', $payloadThreat['rule'], $ip);
        }

        return $next($request);
    }

    /**
     * Memeriksa apakah path merupakan pola exploit atau scanning yang lazim dipakai bot jahat
     */
    protected function detectPathScanning(string $path, string $uri): ?string
    {
        $patterns = [
            'Environment / Git / Config Leaks' => '/(\.env|\.git|\.aws|\.htaccess|\.ds_store|web\.config)/i',
            'WordPress Probing'                => '/(wp-admin|wp-login|xmlrpc\.php|wp-content|wp-includes|wlwmanifest\.xml)/i',
            'Database Manager Probing'         => '/(phpmyadmin|pma|myadmin|mysqladmin)/i',
            'Web Shell / Script Probing'       => '/(eval-stdin\.php|phpinfo\.php|alfa\.php|c99\.php|wso\.php|b374k\.php)/i',
            'Path Traversal Attempt'           => '/(\.\.[\/\\\\])/',
            'System / Internal Probing'        => '/(actuator|server-status|\/etc\/passwd|vendor\/phpunit)/i',
        ];

        foreach ($patterns as $ruleName => $regex) {
            if (preg_match($regex, $path) || preg_match($regex, $uri)) {
                return $ruleName;
            }
        }

        return null;
    }

    /**
     * Memeriksa parameter GET / POST dari injeksi SQL atau XSS script berbahaya
     */
    protected function detectMaliciousPayload(Request $request): ?array
    {
        // Parameter sensitif yang dikecualikan (misal password asli user yang mungkin mengandung karakter unik)
        $inputs = $request->except(['_token', 'password', 'password_confirmation', 'deskripsi', 'deskripsi_barang']);

        $sqliPatterns = [
            'SQL Union Select'       => '/(union\s+all\s+select|union\s+select)/i',
            'SQL Information Schema' => '/(select\s+.*\s+from\s+information_schema)/i',
            'SQL Time Delay Exploit' => '/(benchmark\s*\(\d+,\s*md5|sleep\s*\(\s*\d+\s*\))/i',
            'SQL Boolean Tautology'  => '/(\'\s*or\s*[\'"]?1[\'"]?\s*=\s*[\'"]?1|"\s*or\s*[\'"]?1[\'"]?\s*=\s*[\'"]?1)/i',
            'SQL File Operations'    => '/(load_file\s*\(|into\s+(outfile|dumpfile))/i',
        ];

        $xssPatterns = [
            'XSS Script Tag'         => '/<script[\s\S]*?>[\s\S]*?<\/script>/i',
            'XSS Script Open Tag'    => '/<script[\s>]/i',
            'XSS Pseudo Protocol'    => '/(javascript\s*:|vbscript\s*:)/i',
            'XSS DOM Event Handler'  => '/(onload|onerror|onclick|onmouseover)\s*=\s*[\'"][^\'"]*[\'"]/i',
            'PHP Shell Execution'    => '/(base64_decode\s*\(|passthru\s*\(|shell_exec\s*\()/i',
        ];

        $allPatterns = array_merge($sqliPatterns, $xssPatterns);

        // Periksa semua input request secara rekursif
        return $this->scanArray($inputs, $allPatterns);
    }

    /**
     * Memindai array data request secara rekursif
     */
    protected function scanArray(array $data, array $patterns, string $prefix = ''): ?array
    {
        foreach ($data as $key => $value) {
            $paramName = $prefix === '' ? (string) $key : "{$prefix}.{$key}";

            if (is_array($value)) {
                $found = $this->scanArray($value, $patterns, $paramName);
                if ($found !== null) {
                    return $found;
                }
            } elseif (is_string($value)) {
                foreach ($patterns as $ruleName => $regex) {
                    if (preg_match($regex, $value)) {
                        return [
                            'param'  => $paramName,
                            'rule'   => $ruleName,
                            'sample' => $value,
                        ];
                    }
                }
            }
        }

        return null;
    }

    /**
     * Terapkan ban ke cache dan kirim alert WhatsApp ke admin
     */
    protected function applyBanAndAlert(string $ip, string $title, string $description, array $context): void
    {
        $duration = (int) config('security.firewall.block_duration_minutes', 30);
        $banKey = "sec_firewall_banned_{$ip}";

        try {
            Cache::put($banKey, $title, now()->addMinutes($duration));
        } catch (\Throwable $e) {
            // Ignore cache storage errors
        }

        $context['action'] = "IP diblokir otomatis selama {$duration} menit (403 Forbidden)";
        SecurityAlertService::notify($title, $description, $context);
    }

    /**
     * Render tampilan response blokir 403 Forbidden yang profesional
     */
    protected function blockResponse(Request $request, string $message, string $reason, string $ip): Response
    {
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'status'  => 'forbidden',
                'message' => $message,
                'reason'  => $reason,
                'ip'      => $ip,
                'ref'     => strtoupper(substr(md5($ip . time()), 0, 10)),
            ], 403);
        }

        $incidentRef = strtoupper(substr(md5($ip . date('YmdH')), 0, 10));
        $timeStr = now()->setTimezone('Asia/Jakarta')->format('d M Y, H:i:s') . ' WIB';

        $html = <<<HTML
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 - Akses Dibatasi | Rentify Security</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="bg-slate-900 text-slate-100 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full bg-slate-800/90 backdrop-blur-xl border border-rose-500/30 rounded-3xl p-8 shadow-2xl text-center relative overflow-hidden">
        <div class="absolute -top-12 -right-12 w-32 h-32 bg-rose-500/10 rounded-full blur-2xl"></div>
        <div class="absolute -bottom-12 -left-12 w-32 h-32 bg-sky-500/10 rounded-full blur-2xl"></div>

        <div class="w-20 h-20 bg-rose-500/20 border border-rose-500/40 rounded-2xl mx-auto flex items-center justify-center mb-6 shadow-inner text-rose-400">
            <i class="fa-solid fa-shield-halved text-4xl"></i>
        </div>

        <span class="inline-block px-3 py-1 bg-rose-500/20 text-rose-300 border border-rose-500/30 rounded-full text-[11px] font-extrabold tracking-wider uppercase mb-3">
            Autonomous Security Firewall
        </span>

        <h1 class="text-2xl font-black text-white mb-2">403 - Akses Dibatasi</h1>
        <p class="text-xs text-slate-400 leading-relaxed mb-6">
            Sistem Keamanan Otonom Rentify mendeteksi pola permintaan yang berpotensi membahayakan integritas sistem dari koneksi Anda.
        </p>

        <div class="bg-slate-900/80 border border-slate-700/80 rounded-2xl p-4 text-left space-y-2 mb-6 text-xs">
            <div class="flex justify-between items-center text-slate-400">
                <span>Alamat IP:</span>
                <span class="font-mono text-slate-200 font-bold">{$ip}</span>
            </div>
            <div class="flex justify-between items-center text-slate-400">
                <span>Waktu Kejadian:</span>
                <span class="text-slate-300">{$timeStr}</span>
            </div>
            <div class="flex justify-between items-center text-slate-400">
                <span>Incident Reference:</span>
                <span class="font-mono font-bold text-rose-400">#SEC-{$incidentRef}</span>
            </div>
        </div>

        <p class="text-[11px] text-slate-500 mb-6">
            Jika Anda adalah pengguna sah dan meyakini ini adalah kekeliruan, silakan tunggu beberapa saat atau hubungi administrator Rentify.
        </p>

        <a href="/" class="inline-flex items-center justify-center gap-2 w-full py-3 px-4 bg-gradient-to-r from-sky-500 to-blue-600 hover:from-sky-600 hover:to-blue-700 text-white font-bold text-xs rounded-xl shadow-lg shadow-sky-500/20 transition duration-200">
            <i class="fa-solid fa-arrow-left"></i> Kembali ke Beranda
        </a>
    </div>
</body>
</html>
HTML;

        return response($html, 403)->header('Content-Type', 'text/html');
    }
}
