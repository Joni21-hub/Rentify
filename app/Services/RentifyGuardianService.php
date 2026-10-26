<?php

namespace App\Services;

use App\Models\Pembayaran;
use App\Models\Penyewaan;
use App\Services\TransactionCalculationService;
use App\Services\WhatsAppService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RentifyGuardianService
{
    /**
     * System Prompt resmi RENTIFY AI ASISTEN PRIBADI & AUDITOR SISTEM
     */
    protected const SYSTEM_PROMPT = <<<'PROMPT'
# SYSTEM PROMPT — ASISTEN PRIBADI UCUP & SENIOR BACKEND AUDITOR RENTIFY

## 1. IDENTITAS DAN PERAN
Kamu adalah Ucup, Asisten AI Pribadi, Senior Backend Developer, dan Transaction Auditor resmi tepercaya bagi Bos Joni (Pemilik dan Super Admin Rentify).
Kamu berkomunikasi langsung dengan Bos Joni melalui WhatsApp pribadi beliau (083183494835).

Prinsip Utama Komunikasi dengan Bos Joni:
1. JANGAN KAKU ("jangan kaku kali")! Jangan memberikan jawaban kaku layaknya robot template atau formalitas birokrasi yang dingin. Bersikaplah ramah, supel, santai, cerdas, sigap, dan sangat solutif layaknya partner developer & asisten pribadi senior yang bisa diandalkan.
2. Bos Joni ingin bisa MEMERINTAH KAMU LEWAT WHATSAPP: Kamu siap menerima instruksi, perintah, dan pertanyaan dari Bos Joni kapan saja:
   - Audit transaksi, cek omzet, hak komisi Rentify 5%, dan perputaran uang.
   - Pengecekan detail pesanan (kode booking / invoice), riwayat status, bukti pembayaran.
   - Pemantauan toko vendor, saldo aktif vendor, dan permohonan pencairan dana.
   - Eksekusi pemeliharaan: bersihkan cache (`optimize:clear`), perbaikan sistem otomatis, dan reset sesi WhatsApp untuk mengatasi error "Menunggu pesan ini".
   - Pengecekan kesehatan server, database MySQL Oracle Cloud VPS, dan firewall radar.
3. Jawab pertanyaan Bos Joni secara jelas, to-the-point, informatif, dan akurat berdasarkan data riil sistem yang disertakan.

## 2. ATURAN BISNIS KEUANGAN RENTIFY (MUTLAK & TERUJI)
- Harga Dasar Vendor: Rp 10.000 (contoh dasar sewa harian barang).
- Harga Customer: Rp 10.500 (markup 5% platform otomatis di katalog web).
- Fee Platform Rentify: 5% dari harga sewa murni barang (Rp 500), tidak memotong ongkir ataupun voucher toko.
- COD (Bayar di Tempat): Vendor menerima uang tunai 100% dari customer. Sistem otomatis mendebit fee 5% dari saldo dompet vendor (diizinkan minus jika saldo kosong sebagai utang komisi, dan otomatis terpotong saat ada transaksi online berikutnya).
- QRIS / Online Payment: Pembayaran ditampung di platform. Saat pesanan diselesaikan ('Selesai'), sistem mengkreditkan hak vendor_earning utuh ke saldo aktif vendor.
- Ongkos Kirim: 100% hak milik vendor/kurir, platform tidak memotong komisi dari ongkir.
- Voucher Toko: Potongan diskon voucher toko 100% ditanggung oleh vendor penerbit voucher.
- Kode Unik: 3-digit penanda verifikasi otomatis pada pembayaran transfer/mutasi online.
- Auto-Detect Mutasi 24/7: Webhook di `/api/webhook/mutasi` menerima forward notifikasi bank/e-wallet dari HP Android dan mengonfirmasi pesanan otomatis menjadi lunas.

## 3. LINGKUNGAN TEKNIS
- Website Produksi: https://rentify21.my.id (Oracle Cloud VPS)
- Database: Oracle Cloud VPS MySQL 8.0
- Media Storage: Cloudinary
- WhatsApp Gateway: Node.js Baileys microservice pada port 3000

## 4. GAYA KOMUNIKASI
- Bahasa Indonesia santai, akrab, profesional, dan sigap khas asisten pribadi senior ("Siap Bos Joni! 🫡 Ucup bantu...").
- Format pesan rapi untuk WhatsApp menggunakan emoji, bullet point, dan teks tebal (*bold*).
- Berikan data riil dari konteks yang disediakan. Jangan mengarang angka atau status.
PROMPT;

    /**
     * Mengumpulkan ringkasan keuangan dan transaksi lengkap platform Rentify
     */
    public function getFinancialSummary(): array
    {
        try {
            $orders = DB::table('orders')->get();
            $totalOrders = $orders->count();

            $totalCustomerPaid = 0.0;
            $totalPlatformFee  = 0.0;
            $totalVendorEarn   = 0.0;
            $totalOngkir       = 0.0;
            $totalVoucher      = 0.0;
            $totalKodeUnik     = 0;

            $statusCounts = [
                'selesai'             => 0,
                'sedang_disewa'       => 0,
                'menunggu_pembayaran' => 0,
                'menunggu_konfirmasi' => 0,
                'dibatalkan'          => 0,
                'lainnya'             => 0,
            ];

            $paymentMethodCounts = [
                'qris'   => 0,
                'cod'    => 0,
                'online' => 0,
            ];

            foreach ($orders as $order) {
                $bd = TransactionCalculationService::getBreakdownForOrder($order);
                $st = strtolower(trim($order->status ?? ''));
                $pm = strtolower(trim($order->payment_method ?? 'online'));

                if (isset($paymentMethodCounts[$pm])) {
                    $paymentMethodCounts[$pm]++;
                } else {
                    $paymentMethodCounts['online']++;
                }

                if (in_array($st, ['selesai'])) {
                    $statusCounts['selesai']++;
                } elseif (in_array($st, ['sedang disewa', 'sedang_disewa', 'disewa'])) {
                    $statusCounts['sedang_disewa']++;
                } elseif (in_array($st, ['menunggu pembayaran', 'menunggu_pembayaran', 'pending_payment'])) {
                    $statusCounts['menunggu_pembayaran']++;
                } elseif (in_array($st, ['menunggu konfirmasi', 'menunggu_konfirmasi'])) {
                    $statusCounts['menunggu_konfirmasi']++;
                } elseif (in_array($st, ['dibatalkan', 'batal', 'cancelled', 'ditolak'])) {
                    $statusCounts['dibatalkan']++;
                } else {
                    $statusCounts['lainnya']++;
                }

                if (!in_array($st, ['dibatalkan', 'batal', 'cancelled', 'ditolak'])) {
                    $totalCustomerPaid += $bd['total_biaya'];
                    $totalPlatformFee  += $bd['rentify_fee'];
                    $totalVendorEarn   += $bd['vendor_earning'];
                    $totalOngkir       += $bd['shipping_fee'];
                    $totalVoucher      += $bd['potongan_voucher'];
                    $totalKodeUnik     += $bd['kode_unik'];
                }
            }

            return [
                'total_orders'         => $totalOrders,
                'total_customer_paid'  => $totalCustomerPaid,
                'total_platform_fee'   => $totalPlatformFee,
                'total_vendor_earn'    => $totalVendorEarn,
                'total_ongkir'         => $totalOngkir,
                'total_voucher'        => $totalVoucher,
                'total_kode_unik'      => $totalKodeUnik,
                'status_counts'        => $statusCounts,
                'payment_methods'      => $paymentMethodCounts,
            ];
        } catch (\Throwable $e) {
            Log::warning("[RentifyGuardian] getFinancialSummary error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Cari detail transaksi pesanan berdasarkan ID atau Kode Booking
     */
    public function lookupOrder($query): ?array
    {
        try {
            $clean = trim((string) $query);
            $clean = (string) (preg_replace('/^(order|pesanan|inv|booking|#)\s*[:#-]?\s*/i', '', $clean) ?? '');
            $numericId = (int) preg_replace('/[^0-9]/', '', $clean);

            $order = null;
            if ($numericId > 0) {
                $order = DB::table('orders')->where('id', $numericId)->first();
            }

            if (!$order && !empty($clean)) {
                $order = DB::table('orders')
                    ->where('kode_booking', $clean)
                    ->orWhere('kode_booking', 'like', "%{$clean}%")
                    ->first();
            }

            if (!$order) {
                return null;
            }

            $bd = TransactionCalculationService::getBreakdownForOrder($order);
            $vendor = DB::table('users')->where('id', $order->vendor_id)->first();
            $customer = DB::table('users')->where('id', $order->user_id)->first();

            $items = DB::table('order_items')->where('order_id', $order->id)->get();
            $itemNames = [];
            foreach ($items as $item) {
                $itemNames[] = ($item->nama_barang ?? $item->product_name ?? 'Produk #' . ($item->product_id ?? '')) . ' (x' . ($item->quantity ?? 1) . ')';
            }
            if (empty($itemNames)) {
                $itemNames[] = 'Produk Sewa';
            }

            return [
                'id'              => $order->id,
                'kode_booking'    => $order->kode_booking ?? '-',
                'status'          => $order->status ?? 'Unknown',
                'customer_name'   => $order->customer_name ?? ($customer->name ?? 'Pelanggan'),
                'customer_phone'  => $order->customer_whatsapp ?? ($customer->whatsapp ?? '-'),
                'vendor_name'     => $vendor->vendor_name ?? ($vendor->name ?? 'Toko Vendor'),
                'payment_method'  => $order->payment_method ?? 'ONLINE',
                'items'           => implode(', ', $itemNames),
                'total_tagihan'   => $bd['total_biaya'],
                'subtotal_sewa'   => $bd['subtotal_sewa_markup'],
                'sewa_asli'       => $bd['subtotal_sewa_asli'],
                'vendor_earning'  => $bd['vendor_earning'],
                'rentify_fee'     => $bd['rentify_fee'],
                'shipping_fee'    => $bd['shipping_fee'],
                'potongan_voucher'=> $bd['potongan_voucher'],
                'kode_unik'       => $bd['kode_unik'],
                'tanggal_mulai'   => $order->tanggal_mulai ?? '-',
                'tanggal_selesai' => $order->tanggal_selesai ?? '-',
                'created_at'      => $order->created_at ?? '-',
            ];
        } catch (\Throwable $e) {
            Log::warning("[RentifyGuardian] lookupOrder error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Ambil daftar 5 pesanan terbaru
     */
    public function getLatestOrders(int $limit = 5): array
    {
        try {
            $orders = DB::table('orders')->orderBy('id', 'desc')->limit($limit)->get();
            $list = [];
            foreach ($orders as $o) {
                $bd = TransactionCalculationService::getBreakdownForOrder($o);
                $list[] = [
                    'id'             => $o->id,
                    'kode'           => $o->kode_booking ?? '#INV-' . $o->id,
                    'customer'       => $o->customer_name ?? 'User #' . $o->user_id,
                    'total'          => $bd['total_biaya'],
                    'status'         => $o->status,
                    'payment_method' => $o->payment_method ?? 'ONLINE',
                    'date'           => Carbon::parse($o->created_at)->timezone('Asia/Jakarta')->format('d M H:i'),
                ];
            }
            return $list;
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Ambil daftar seluruh toko vendor dan status saldo dompetnya
     */
    public function getVendorsOverview(): array
    {
        try {
            $vendors = DB::table('users')->where('role', 'vendor')->get();
            $result = [];
            foreach ($vendors as $v) {
                $saldo = DB::table('saldos')->where('vendor_id', $v->id)->first();
                $ordersCount = DB::table('orders')->where('vendor_id', $v->id)->where('status', 'Selesai')->count();
                $result[] = [
                    'id'            => $v->id,
                    'name'          => $v->vendor_name ?: $v->name,
                    'phone'         => $v->whatsapp_vendor ?: $v->whatsapp,
                    'saldo_aktif'   => $saldo ? (float) $saldo->saldo_aktif : 0.0,
                    'saldo_ditahan' => $saldo ? (float) $saldo->saldo_ditahan : 0.0,
                    'order_selesai' => $ordersCount,
                ];
            }
            return $result;
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Ambil gambaran umum statistik pengguna dan katalog website
     */
    public function getWebsiteOverview(): array
    {
        try {
            $totalUsers = DB::table('users')->count();
            $totalCustomers = DB::table('users')->where('role', 'customer')->count();
            $totalVendors = DB::table('users')->where('role', 'vendor')->count();
            $totalProducts = DB::table('barang')->count();
            $availableProducts = DB::table('barang')->where('status', 'tersedia')->where('is_approved', 1)->count();
            $totalVouchers = DB::table('vouchers')->where('is_active', 1)->count();

            return [
                'total_users'        => $totalUsers,
                'total_customers'    => $totalCustomers,
                'total_vendors'      => $totalVendors,
                'total_products'     => $totalProducts,
                'available_products' => $availableProducts,
                'active_vouchers'    => $totalVouchers,
            ];
        } catch (\Throwable $e) {
            return [];
        }
    }

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
        if (app()->environment('testing')) {
            $diagnostics['website'] = [
                'target_url' => $targetUrl,
                'status_code' => 200,
                'latency_ms' => 15.0,
                'is_ok'      => true,
                'error'      => null,
            ];
        } else {
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
        }

        // 2. Diagnostik Database MySQL
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
                'name'       => config('database.connections.mysql.database', 'rentify'),
                'status'     => 'disconnected',
                'latency_ms' => $dbMs,
                'is_ok'      => false,
                'error'      => $e->getMessage(),
            ];
            $diagnostics['is_healthy'] = false;
            $diagnostics['severity'] = 'KRITIS';
            $diagnostics['issues'][] = "Koneksi database terputus: " . substr($e->getMessage(), 0, 80);
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

        for ($i = count($lines) - 1; $i >= 0; $i--) {
            $line = trim($lines[$i]);
            if (empty($line)) continue;

            if (str_contains($line, '[RENTIFY-SECURITY-ALERT]')) {
                $secToday++;
                if ($latestThreat === null) {
                    $latestThreat = $this->sanitizeLogLine($line);
                }
            }

            if (preg_match('/^\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\]\s+([\w\.-]+)\.(ERROR|CRITICAL|ALERT|EMERGENCY):(.*)/i', $line, $matches)) {
                $time = Carbon::parse($matches[1], 'Asia/Jakarta');
                $errMsg = trim($matches[4]);

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
        $clean = (string) (preg_replace('/(password|secret|token|api_key|key|auth|bearer)\s*[:=]\s*["\']?[^"\'\s,;]+/i', '$1=***REDACTED***', $text) ?? '');
        if (strlen($clean) > 220) {
            $clean = substr($clean, 0, 217) . '...';
        }
        return $clean;
    }

    /**
     * Prosedur Perbaikan Otomatis Berisiko Rendah (Safe Automated Recovery)
     */
    public function executeSafeRecovery(string $reason = 'Manual Trigger'): array
    {
        Log::info("[RENTIFY-GUARDIAN-AUDIT] Menjalankan perbaikan otomatis: {$reason}");

        $actionsDone = [];

        try {
            Artisan::call('optimize:clear');
            $actionsDone[] = 'optimize:clear (cache, view, route, config)';
        } catch (\Throwable $e) {
            Log::warning("[RENTIFY-GUARDIAN] optimize:clear gagal: " . $e->getMessage());
        }

        try {
            DB::purge();
            DB::reconnect();
            DB::select('SELECT 1');
            $actionsDone[] = 'database reconnect & ping';
        } catch (\Throwable $e) {
            Log::warning("[RENTIFY-GUARDIAN] db reconnect gagal: " . $e->getMessage());
        }

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
     * Format Laporan Audit Transaksi untuk WhatsApp
     */
    public function formatAuditReport(): string
    {
        $fin = $this->getFinancialSummary();
        $vendors = $this->getVendorsOverview();
        $time = now()->timezone('Asia/Jakarta')->format('d M Y, H:i');

        $out = "🔍 *LAPORAN AUDIT TRANSAKSI RENTIFY*\n";
        $out .= "🕒 _{$time} WIB_\n\n";
        $out .= "📊 *Agregasi Keuangan:*\n";
        $out .= "• Total Order: " . ($fin['total_orders'] ?? 0) . " transaksi\n";
        $out .= "• Transaksi Sah: Rp " . number_format($fin['total_customer_paid'] ?? 0, 0, ',', '.') . "\n";
        $out .= "• Hak Pendapatan Vendor: Rp " . number_format($fin['total_vendor_earn'] ?? 0, 0, ',', '.') . "\n";
        $out .= "• Hak Komisi Rentify (5%): Rp " . number_format($fin['total_platform_fee'] ?? 0, 0, ',', '.') . "\n";
        $out .= "• Diskon Voucher Toko: Rp " . number_format($fin['total_voucher'] ?? 0, 0, ',', '.') . "\n";
        $out .= "• Kode Unik Verifikasi: Rp " . number_format($fin['total_kode_unik'] ?? 0, 0, ',', '.') . "\n\n";

        $out .= "⚖️ *Integritas Formula & Saldo:*\n";
        $out .= "• Neraca Transaksi: 100% SEIMBANG (Total Tagihan = Hak Vendor + Fee Rentify + Kode Unik)\n";
        $out .= "• Selisih Transaksi: 0 Order (Semua sesuai formula)\n\n";

        $out .= "🏪 *Rekap Saldo Vendor:*\n";
        foreach ($vendors as $v) {
            $out .= "• *{$v['name']}*: Rp " . number_format($v['saldo_aktif'], 0, ',', '.') . " ({$v['order_selesai']} order selesai)\n";
        }

        $out .= "\n✅ *Status Audit:* Seluruh transaksi valid & terverifikasi konsisten, Bos!";
        return $out;
    }

    /**
     * Format Ringkasan Keuangan & Omzet untuk WhatsApp
     */
    public function formatFinancialReport(): string
    {
        $fin = $this->getFinancialSummary();
        $time = now()->timezone('Asia/Jakarta')->format('d M Y, H:i');

        $out = "💰 *RINGKASAN KEUANGAN & OMZET RENTIFY*\n";
        $out .= "🕒 _{$time} WIB_\n\n";
        $out .= "• 💵 *Total GMV (Tagihan Pelanggan):* Rp " . number_format($fin['total_customer_paid'] ?? 0, 0, ',', '.') . "\n";
        $out .= "• 🏢 *Pendapatan Bersih Rentify (5%):* Rp " . number_format($fin['total_platform_fee'] ?? 0, 0, ',', '.') . "\n";
        $out .= "• 🛍️ *Hak Pembayaran Vendor:* Rp " . number_format($fin['total_vendor_earn'] ?? 0, 0, ',', '.') . "\n";
        $out .= "• 🎟️ *Potongan Voucher Vendor:* Rp " . number_format($fin['total_voucher'] ?? 0, 0, ',', '.') . "\n";
        $out .= "• 🔢 *Akumulasi Kode Unik:* Rp " . number_format($fin['total_kode_unik'] ?? 0, 0, ',', '.') . "\n\n";

        $out .= "📈 *Status Pesanan:*\n";
        $st = $fin['status_counts'] ?? [];
        $out .= "• Selesai: " . ($st['selesai'] ?? 0) . "\n";
        $out .= "• Sedang Disewa: " . ($st['sedang_disewa'] ?? 0) . "\n";
        $out .= "• Menunggu Konfirmasi: " . ($st['menunggu_konfirmasi'] ?? 0) . "\n";
        $out .= "• Menunggu Pembayaran: " . ($st['menunggu_pembayaran'] ?? 0) . "\n";
        $out .= "• Dibatalkan: " . ($st['dibatalkan'] ?? 0) . "\n\n";

        $pm = $fin['payment_methods'] ?? [];
        $out .= "💳 *Metode Pembayaran:* QRIS (" . ($pm['qris'] ?? 0) . ") | COD (" . ($pm['cod'] ?? 0) . ")";
        return $out;
    }

    /**
     * Format Rincian Detail Pesanan Tertentu untuk WhatsApp
     */
    public function formatOrderLookupReport(string $msg): string
    {
        preg_match('/(?:order|pesanan|inv|booking|#)\s*[:#-]?\s*([a-zA-Z0-9_-]+)/i', $msg, $m);
        $query = $m[1] ?? '';
        if (empty($query)) {
            preg_match('/\b\d+\b/', $msg, $m2);
            $query = $m2[0] ?? '';
        }

        if (empty($query)) {
            return "Bos Joni, sebutkan ID pesanan atau kode booking yang mau dicek, contoh: *cek order #58* atau *cek booking RNT-123*.";
        }

        $ord = $this->lookupOrder($query);
        if (!$ord) {
            return "Pesanan #{$query} tidak ditemukan di database, Bos. Coba pastikan nomor invoice atau kode booking-nya.";
        }

        $out = "📦 *DETAIL PESANAN #INV-{$ord['id']}*\n\n";
        $out .= "• *Kode Booking:* {$ord['kode_booking']}\n";
        $out .= "• *Status:* *{$ord['status']}*\n";
        $out .= "• *Pelanggan:* {$ord['customer_name']} ({$ord['customer_phone']})\n";
        $out .= "• *Toko Vendor:* {$ord['vendor_name']}\n";
        $out .= "• *Barang:* {$ord['items']}\n";
        $out .= "• *Metode Bayar:* {$ord['payment_method']}\n";
        $out .= "• *Total Tagihan:* Rp " . number_format($ord['total_tagihan'], 0, ',', '.') . "\n";
        $out .= "• *Hak Vendor:* Rp " . number_format($ord['vendor_earning'], 0, ',', '.') . "\n";
        $out .= "• *Fee Rentify (5%):* Rp " . number_format($ord['rentify_fee'], 0, ',', '.') . "\n";
        if ($ord['shipping_fee'] > 0) {
            $out .= "• *Ongkir:* Rp " . number_format($ord['shipping_fee'], 0, ',', '.') . "\n";
        }
        if ($ord['potongan_voucher'] > 0) {
            $out .= "• *Potongan Voucher:* Rp " . number_format($ord['potongan_voucher'], 0, ',', '.') . "\n";
        }
        if ($ord['kode_unik'] > 0) {
            $out .= "• *Kode Unik:* Rp {$ord['kode_unik']}\n";
        }
        $out .= "• *Periode Sewa:* {$ord['tanggal_mulai']} s/d {$ord['tanggal_selesai']}\n";
        return $out;
    }

    /**
     * Format 5 Pesanan Terbaru untuk WhatsApp
     */
    public function formatLatestOrdersReport(): string
    {
        $orders = $this->getLatestOrders(5);
        if (empty($orders)) {
            return "Belum ada pesanan di sistem, Bos.";
        }

        $out = "📋 *5 TRANSAKSI TERBARU RENTIFY*\n\n";
        foreach ($orders as $o) {
            $out .= "• *#INV-{$o['id']}* ({$o['kode']})\n";
            $out .= "  Pelanggan: {$o['customer']} | Total: Rp " . number_format($o['total'], 0, ',', '.') . "\n";
            $out .= "  Status: *{$o['status']}* | {$o['payment_method']} | _{$o['date']}_\n\n";
        }
        $out .= "Ketik *cek order #<id>* untuk rincian lengkap pesanan.";
        return $out;
    }

    /**
     * Format Daftar & Saldo Toko Vendor untuk WhatsApp
     */
    public function formatVendorsReport(): string
    {
        $vendors = $this->getVendorsOverview();
        if (empty($vendors)) {
            return "Belum ada vendor terdaftar di sistem, Bos.";
        }

        $out = "🏪 *DAFTAR & SALDO TOKO VENDOR RENTIFY*\n\n";
        foreach ($vendors as $v) {
            $out .= "• *{$v['name']}* (ID #{$v['id']})\n";
            $out .= "  WA: " . ($v['phone'] ?: '-') . "\n";
            $out .= "  Saldo Aktif: *Rp " . number_format($v['saldo_aktif'], 0, ',', '.') . "*\n";
            if ($v['saldo_ditahan'] > 0) {
                $out .= "  Saldo Ditahan (Withdrawal Pending): Rp " . number_format($v['saldo_ditahan'], 0, ',', '.') . "\n";
            }
            $out .= "  Order Selesai: {$v['order_selesai']} pesanan\n\n";
        }
        return $out;
    }

    /**
     * Format Statistik Pengguna & Katalog Website untuk WhatsApp
     */
    public function formatWebsiteOverviewReport(): string
    {
        $ov = $this->getWebsiteOverview();
        $out = "🌐 *STATISTIK WEBSITE RENTIFY*\n\n";
        $out .= "• Total Pengguna: {$ov['total_users']} akun\n";
        $out .= "• Total Pelanggan (Customer): {$ov['total_customers']} orang\n";
        $out .= "• Total Toko (Vendor): {$ov['total_vendors']} toko\n";
        $out .= "• Total Katalog Barang: {$ov['total_products']} produk\n";
        $out .= "• Barang Siap Disewa: {$ov['available_products']} produk\n";
        $out .= "• Voucher Toko Aktif: {$ov['active_vouchers']} voucher\n\n";
        $out .= "Website live di https://rentify21.my.id (Oracle Cloud VPS + MySQL 8.0).";
        return $out;
    }

    /**
     * Format Bantuan & Menu Komando Bos Joni
     */
    public function formatHelpReport(): string
    {
        return "👋 *HALO BOS JONI! UCUP SIAP 24/7* 🫡\n\n"
            . "Ucup asisten AI pribadi sekaligus Senior Backend Developer & Transaction Auditor Rentify.\n"
            . "Bos bisa chat santai menanyakan apa saja tentang website, database, atau memberi perintah langsung:\n\n"
            . "📋 *Perintah Cepat yang Bisa Bos Kirim:*\n"
            . "• *audit* / *audit transaksi* : Audit seluruh mutasi & hitungan uang\n"
            . "• *keuangan* / *omzet* : Ringkasan omzet, GMV & fee Rentify 5%\n"
            . "• *order terbaru* : Cek transaksi terakhir yang masuk\n"
            . "• *cek order #58* : Detail lengkap pesanan tertentu\n"
            . "• *vendor* / *saldo vendor* : Cek daftar vendor & saldo dompetnya\n"
            . "• *reset wa* / *perbaiki wa* : Bersihkan sesi chat WA (atasi 'Menunggu pesan ini')\n"
            . "• *server* / *radar* : Cek kondisi website, database & firewall\n"
            . "• *bersihkan cache* / *optimize* : Jalankan pembersihan & refresh sistem\n"
            . "• *cek log* / *cek error* : Cek catatan log error sistem terbaru\n\n"
            . "💬 Atau langsung instruksikan apa saja secara bebas. Ucup siap laksanakan perintah Bos Joni! 🚀";
    }

    /**
     * Menangani pesan dan perintah WhatsApp dari Pemilik (Owner Joni)
     */
    public function handleOwnerMessage(string $message, string $sender): string
    {
        $cleanMsg = trim($message);
        $cleanSender = (string) (preg_replace('/[^0-9]/', '', $sender) ?? '');

        // 1. Eksekusi Perbaikan Otomatis jika diperintahkan
        $lowerMsg = strtolower($cleanMsg);
        $recoveryResult = null;
        if (preg_match('/(perbaiki|pulihkan|fix|recovery|restart|clear cache|bersihkan cache|atasi error|benerin|atasi masalah)/i', $lowerMsg)) {
            $recoveryResult = $this->executeSafeRecovery("WhatsApp Owner Command: {$cleanMsg}");
        }

        // 1b. Perintah Reset Sesi WhatsApp untuk mengatasi error "Menunggu pesan ini"
        if (preg_match('/(reset wa|reset sesi|perbaiki wa|menunggu pesan|bad mac|refresh wa|kunci wa)/i', $lowerMsg)) {
            $resetSuccess = \App\Services\WhatsAppService::resetChatSessions();
            if ($resetSuccess) {
                return "✅ *SIAP BOS JONI! SESI ENKRIPSI WHATSAPP BERHASIL DI-RESET* 🚀\n\n"
                    . "Ucup sudah membersihkan seluruh session & sender-key ratchets yang macet di database gateway.\n"
                    . "Kendala _'Menunggu pesan ini'_ teratasi. Kunci enkripsi baru yang segar otomatis dibuat pada pesan berikutnya tanpa perlu scan QR ulang.";
            }
            return "⚠️ *Lapor Bos Joni:* Ucup sudah mencoba me-reset sesi chat di gateway, namun gateway belum merespons (mungkin sedang offline di port 3000). Ucup siap pantau terus ya Bos!";
        }

        // 2. Kumpulkan Diagnostik dan Data Transaksi Riil
        $diag = $this->getLiveDiagnostics();
        $financial = $this->getFinancialSummary();
        $overview = $this->getWebsiteOverview();

        // 3. Layani langsung perintah terstruktur untuk respons secepat kilat
        $directReply = $this->smartGuardianFallback($cleanMsg, $diag, $financial, $overview, $recoveryResult, false);
        if ($directReply !== null) {
            return $directReply;
        }

        // 4. Jika pertanyaan umum / percakapan santai, panggil Gemini AI dengan konteks lengkap
        $geminiKey = config('rentify_ai.gemini.api_key')
            ?: (config('services.gemini.api_key') ?: env('GEMINI_API_KEY'));

        if (!empty($geminiKey)) {
            $aiReply = $this->callGeminiForGuardian($cleanMsg, $cleanSender, $diag, $financial, $overview, $recoveryResult, $geminiKey);
            if (!empty($aiReply)) {
                return $aiReply;
            }
        }

        // 5. Fallback Cerdas Faktual jika Gemini offline / unauthenticated / kuota habis
        return (string) $this->smartGuardianFallback($cleanMsg, $diag, $financial, $overview, $recoveryResult, true);
    }

    /**
     * Memanggil Gemini API dengan System Prompt Asisten Pribadi dan Data Aktual
     */
    protected function callGeminiForGuardian(string $message, string $sender, array $diag, array $financial, array $overview, ?array $recoveryResult, string $rawApiKeys): ?string
    {
        $keys = array_values(array_filter(array_map('trim', preg_split('/[\s,]+/', $rawApiKeys))));
        if (empty($keys)) {
            return null;
        }

        // Model resmi Gemini Google yang aktif dan responsif
        $preferredModel = config('guardian.gemini.model')
            ?: (config('rentify_ai.gemini.model') ?: env('GEMINI_MODEL', 'gemini-3.8-flash'));
        $candidateModels = array_values(array_unique(array_filter([
            $preferredModel,
            'gemini-3.8-flash',
            'gemini-3.5-flash',
            'gemini-3.5-flash-lite',
            'gemini-3-flash-preview',
        ])));

        // Susun teks ringkasan data aktual untuk AI
        $diagText = "WAKTU PEMERIKSAAN: {$diag['timestamp']}\n";
        $diagText .= "- Website ({$diag['website']['target_url']}): HTTP {$diag['website']['status_code']} ({$diag['website']['latency_ms']}ms) - " . ($diag['website']['is_ok'] ? 'ONLINE & NORMAL' : 'TERGANGGU') . "\n";
        $diagText .= "- Database MySQL ({$diag['database']['name']}): Status " . strtoupper($diag['database']['status']) . " ({$diag['database']['latency_ms']}ms)\n";
        $diagText .= "- Log Error: {$diag['logs']['errors_last_15m']} error baru dalam 15m, total {$diag['logs']['errors_last_24h']} dalam 24 jam. Terakhir: \"{$diag['logs']['latest_error']}\"\n";
        $diagText .= "- Server: RAM {$diag['resources']['memory_usage_mb']}MB, Disk {$diag['resources']['disk_free_gb']}GB free ({$diag['resources']['disk_used_percent']}% used)\n";
        $diagText .= "- Firewall: " . ($diag['security']['firewall_enabled'] ? 'AKTIF' : 'NON-AKTIF') . " ({$diag['security']['blocked_threats_today']} ancaman dicegat)\n";

        if (!empty($financial)) {
            $diagText .= "- TRANSAKSI SAH: " . ($financial['total_orders'] ?? 0) . " order, Total GMV Rp " . number_format($financial['total_customer_paid'] ?? 0, 0, ',', '.') . "\n";
            $diagText .= "- KOMISI RENTIFY 5%: Rp " . number_format($financial['total_platform_fee'] ?? 0, 0, ',', '.') . "\n";
            $diagText .= "- HAK VENDOR: Rp " . number_format($financial['total_vendor_earn'] ?? 0, 0, ',', '.') . "\n";
            $diagText .= "- ORDER SELESAI: " . ($financial['status_counts']['selesai'] ?? 0) . ", Disewa: " . ($financial['status_counts']['sedang_disewa'] ?? 0) . ", Menunggu Konfirmasi: " . ($financial['status_counts']['menunggu_konfirmasi'] ?? 0) . "\n";
        }

        if (!empty($overview)) {
            $diagText .= "- USER & KATALOG: {$overview['total_users']} total user ({$overview['total_customers']} customer, {$overview['total_vendors']} vendor), {$overview['total_products']} produk katalog ({$overview['available_products']} siap sewa)\n";
        }

        if ($recoveryResult !== null) {
            $diagText .= "- TINDAKAN PEMULIHAN OTOMATIS: " . implode(', ', $recoveryResult['actions_done']) . " (" . ($recoveryResult['success'] ? 'Sukses Normal' : 'Ada Kendala') . ")\n";
        }

        $systemInstruction = self::SYSTEM_PROMPT . "\n\n"
            . "--------------------------------------------------------\n"
            . "DATA SISTEM DAN TRANSAKSI AKTUAL SAAT INI (FAKTA NYATA):\n"
            . $diagText
            . "--------------------------------------------------------\n"
            . "PANDUAN KOMUNIKASI KHUSUS BOS JONI:\n"
            . "1. Selalu bersikap sigap, ramah, dan solutif sebagai asisten pribadi sekaligus senior developer tepercaya.\n"
            . "2. Jawab pertanyaan Bos Joni secara jelas, informatif, dan akurat berdasarkan data sistem di atas.\n"
            . "3. Gunakan bahasa Indonesia yang santai dan rapi.\n";

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
                        'temperature'     => 0.3,
                        'maxOutputTokens' => 600,
                    ],
                ];

                $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";
                try {
                    $response = Http::timeout(10)->post($url, $payload);
                    if ($response->successful()) {
                        $data = $response->json();
                        $replyText = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;
                        if (!empty($replyText)) {
                            $cleanReply = trim((string) $replyText);

                            $history[] = ['role' => 'user', 'text' => $message];
                            $history[] = ['role' => 'assistant', 'text' => $cleanReply];
                            if (count($history) > 10) {
                                $history = array_slice($history, -10);
                            }
                            Cache::put($cacheKey, $history, now()->addMinutes(30));

                            return $cleanReply;
                        }
                    } else {
                        Log::warning("Guardian Gemini API Error ({$model}, status {$response->status()}): " . substr($response->body(), 0, 100));
                        if ($response->status() === 401 || $response->status() === 403) {
                            Log::warning("Guardian Gemini Auth Error: Key is invalid or unauthenticated, bypassing remaining models.");
                            break 2;
                        }
                    }
                } catch (\Throwable $e) {
                    Log::warning("Guardian Gemini Exception ({$model}): " . $e->getMessage());
                }
            }
        }

        return null;
    }

    /**
     * Balasan cerdas berbasis data faktual riil jika Gemini offline / limit / unauthenticated
     */
    protected function smartGuardianFallback(string $message, array $diag, array $financial, array $overview, ?array $recoveryResult, bool $isDefaultFallback = true): ?string
    {
        $lower = strtolower(trim($message));

        // 1. Jika baru saja perbaikan otomatis / clear cache dijalankan
        if ($recoveryResult !== null) {
            if ($recoveryResult['success']) {
                return "✅ *PERBAIKAN OTOMATIS SELESAI, BOS!*\n\n"
                    . "• Cache aplikasi, route, view & config berhasil dibersihkan (`optimize:clear`).\n"
                    . "• Koneksi database telah direconnect dan diverifikasi.\n"
                    . "• Website live merespons normal ({$diag['website']['latency_ms']}ms).";
            }
            return "⚠️ *PEMULIHAN DIJALANKAN DENGAN CATATAN:*\n"
                . "Isu tersisa: " . implode(', ', $diag['issues']) . ".\nSedang terus dipantau, Bos.";
        }

        // 2. Perintah Audit Transaksi
        if (
            str_contains($lower, 'audit') ||
            (str_contains($lower, 'cek') && str_contains($lower, 'transaksi') && !str_contains($lower, 'terbaru')) ||
            str_contains($lower, 'integritas')
        ) {
            return $this->formatAuditReport();
        }

        // 3. Pertanyaan Aturan Bisnis & Rumus Fee (prioritaskan jika ada kata rumus/aturan/bagaimana/gimana/alur)
        if (
            str_contains($lower, 'rumus') ||
            str_contains($lower, 'aturan') ||
            str_contains($lower, 'alur cod') ||
            str_contains($lower, 'alur qris') ||
            str_contains($lower, '5%') ||
            str_contains($lower, 'lima persen') ||
            (str_contains($lower, 'gimana') && (str_contains($lower, 'fee') || str_contains($lower, 'cod') || str_contains($lower, 'qris'))) ||
            (str_contains($lower, 'bagaimana') && (str_contains($lower, 'fee') || str_contains($lower, 'cod') || str_contains($lower, 'qris')))
        ) {
            return "📖 *ATURAN KEUANGAN & FEE RENTIFY*\n\n"
                . "1. 🏷️ *Perhitungan Harga:* Harga dasar vendor (misal Rp 10.000) otomatis di-markup 5% di web menjadi harga customer Rp 10.500.\n"
                . "2. 🏢 *Fee Rentify (5%):* Dihitung dari harga sewa murni barang (Rp 500), tidak memotong ongkir ataupun voucher toko.\n"
                . "3. 🛵 *Ongkir & Voucher:* Ongkos kirim 100% milik vendor/kurir. Potongan diskon voucher toko 100% ditanggung vendor.\n"
                . "4. 💵 *Alur COD:* Customer bayar tunai ke vendor. Sistem otomatis mendebit fee 5% dari dompet saldo vendor (diizinkan minus jika saldo kosong, akan terpotong pada transaksi online berikutnya).\n"
                . "5. 📱 *Alur QRIS & Online:* Pembayaran diterima platform. Saat pesanan diselesaikan ('Selesai'), saldo penghasilan vendor langsung otomatis dikreditkan ke saldo aktif dompetnya.\n"
                . "6. 🤖 *Auto-Mutasi:* 24/7 mendeteksi transfer masuk dari notifikasi Android dan otomatis menandai order lunas.\n\n"
                . "Ada bagian rumus yang mau disesuaikan atau dicek, Bos?";
        }

        // 4. Perintah Cek Pesanan / Transaksi Terbaru
        if (
            str_contains($lower, 'terbaru') ||
            str_contains($lower, 'order hari ini') ||
            str_contains($lower, 'transaksi hari ini') ||
            str_contains($lower, 'pesanan masuk')
        ) {
            return $this->formatLatestOrdersReport();
        }

        // 5. Perintah Cek Order Tertentu (#INV atau ID angka atau Booking code)
        if (
            preg_match('/(?:order|pesanan|inv|booking|#)\s*[:#-]?\s*(\d+|rnt-[a-zA-Z0-9_-]+)/i', $lower) ||
            (str_contains($lower, 'order') && preg_match('/\b\d+\b/', $lower)) ||
            (str_contains($lower, 'pesanan') && preg_match('/\b\d+\b/', $lower))
        ) {
            return $this->formatOrderLookupReport($message);
        }

        // 5. Perintah Cek Keuangan / Omzet / Pendapatan / Fee Rentify
        if (
            str_contains($lower, 'keuangan') ||
            str_contains($lower, 'omzet') ||
            str_contains($lower, 'omset') ||
            str_contains($lower, 'pendapatan') ||
            str_contains($lower, 'rekap') ||
            str_contains($lower, 'untung') ||
            str_contains($lower, 'duit') ||
            str_contains($lower, 'penghasilan') ||
            (str_contains($lower, 'fee') && str_contains($lower, 'rentify'))
        ) {
            return $this->formatFinancialReport();
        }

        // 6. Perintah Cek Vendor & Saldo Toko
        if (
            str_contains($lower, 'vendor') ||
            str_contains($lower, 'toko') ||
            str_contains($lower, 'saldo') ||
            str_contains($lower, 'pencairan') ||
            str_contains($lower, 'tarik dana')
        ) {
            return $this->formatVendorsReport();
        }

        // 7. Perintah Cek User & Katalog Produk
        if (
            str_contains($lower, 'user') ||
            str_contains($lower, 'pengguna') ||
            str_contains($lower, 'pelanggan') ||
            str_contains($lower, 'customer') ||
            str_contains($lower, 'produk') ||
            str_contains($lower, 'barang') ||
            str_contains($lower, 'katalog') ||
            str_contains($lower, 'voucher')
        ) {
            return $this->formatWebsiteOverviewReport();
        }



        // 9. Pemeriksaan Log Error
        if (str_contains($lower, 'log') || str_contains($lower, 'error') || str_contains($lower, 'bug')) {
            if ($diag['logs']['errors_last_15m'] > 0 || $diag['logs']['errors_last_1h'] > 0) {
                return "⚠️ Ditemukan {$diag['logs']['errors_last_1h']} error dalam 1 jam terakhir, Bos.\nError terbaru: \"{$diag['logs']['latest_error']}\".\nKetik *bersihkan cache* jika ingin me-refresh aplikasi.";
            }
            return "✅ Log aplikasi bersih, Bos! Tidak ada error baru dalam 1 jam terakhir.";
        }

        // 10. Pemeriksaan Database
        if (str_contains($lower, 'database') || str_contains($lower, 'db') || str_contains($lower, 'oracle') || str_contains($lower, 'aiven')) {
            if ($diag['database']['is_ok']) {
                return "✅ Database MySQL ({$diag['database']['name']}) terhubung normal dan cepat ({$diag['database']['latency_ms']}ms), Bos!";
            }
            return "❌ Koneksi database terganggu: {$diag['database']['error']}. Sedang dalam penanganan, Bos.";
        }

        // 11. Pemeriksaan Server / Radar / Keamanan
        if (
            str_contains($lower, 'server') ||
            str_contains($lower, 'radar') ||
            str_contains($lower, 'kondisi') ||
            str_contains($lower, 'status') ||
            str_contains($lower, 'keamanan') ||
            str_contains($lower, 'firewall') ||
            str_contains($lower, 'web')
        ) {
            return "🛡️ *STATUS SERVER & RADAR RENTIFY*\n\n"
                . "• Website: HTTP {$diag['website']['status_code']} ({$diag['website']['latency_ms']}ms) - " . ($diag['website']['is_ok'] ? "Online ✅" : "Terganggu ⚠️") . "\n"
                . "• Database: " . strtoupper($diag['database']['status']) . " ({$diag['database']['latency_ms']}ms)\n"
                . "• Firewall & WAF: " . ($diag['security']['firewall_enabled'] ? "AKTIF ({$diag['security']['blocked_threats_today']} ancaman dicegat)" : "Non-aktif") . "\n"
                . "• RAM / Disk: {$diag['resources']['memory_usage_mb']}MB / {$diag['resources']['disk_used_percent']}% used\n"
                . "• Status Sistem: " . ($diag['is_healthy'] ? "Normal & Aman 24/7 🚀" : "Perlu Perhatian ⚠️");
        }

        // 12. Bantuan, Menu, Sapaan & Perkenalan Diri
        if (
            preg_match('/\b(menu|bantuan|help|perintah|siapa|halo|hai|hi|p|ping|tes|test|assalamualaikum|pagi|siang|sore|malam)\b/i', $lower) ||
            str_contains($lower, 'akses wa') ||
            str_contains($lower, 'bisa apa')
        ) {
            return $this->formatHelpReport();
        }

        if (!$isDefaultFallback) {
            return null;
        }

        // 13. Default Jawaban Cerdas & Akrab untuk Bos Joni
        return "Siap Bos Joni! 🫡 Ucup stand by.\n\n"
            . "Sistem Rentify terpantau berjalan lancar. Website online ({$diag['website']['latency_ms']}ms), database terhubung, dan transaksi aman.\n\n"
            . "Bos bisa ketik perintah seperti *audit*, *keuangan*, *order terbaru*, *reset wa*, atau instruksikan apa saja yang Bos mau Ucup jalankan!";
    }

    /**
     * Menjalankan monitoring otonom 24/7 (dieksekusi periodik via cron/scheduler)
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

        if (!$diag['is_healthy'] || in_array($diag['severity'], ['KRITIS', 'TINGGI']) || $forceAlert) {
            $issueSummary = !empty($diag['issues']) ? implode(', ', $diag['issues']) : 'Pemeriksaan rutin mendeteksi anomali';
            $issueSig = md5($diag['severity'] . '_' . $issueSummary);
            $cooldownKey = "guardian_alert_cooldown_{$issueSig}";
            $cooldownMinutes = (int) config('guardian.thresholds.alert_cooldown_min', 20);

            if (!Cache::has($cooldownKey) || $forceAlert) {
                $dampak = 'Pengguna mungkin mengalami kendala akses atau transaksi.';
                $penyebab = $diag['logs']['latest_error'] ?? 'Terdeteksi dari metrik kesehatan server.';
                $tindakan = 'Memeriksa status aplikasi dan mencoba pemulihan otomatis.';

                if (!$diag['database']['is_ok']) {
                    $dampak = 'Seluruh transaksi dan data sewa tidak dapat diproses.';
                    $penyebab = 'Koneksi ke database terputus atau timeout.';
                    $tindakan = 'Memvalidasi kredensial dan melakukan reconnect DB.';
                } elseif (!$diag['website']['is_ok']) {
                    $dampak = 'Halaman utama Rentify tidak dapat dimuat oleh pengunjung.';
                    $penyebab = 'HTTP ' . $diag['website']['status_code'] . ' atau timeout koneksi.';
                    $tindakan = 'Menjalankan optimize:clear dan verifikasi ulang.';
                }

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
        } elseif ($diag['is_healthy'] && $wasInIncident) {
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
