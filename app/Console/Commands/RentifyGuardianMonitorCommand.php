<?php

namespace App\Console\Commands;

use App\Services\RentifyGuardianService;
use Illuminate\Console\Command;

class RentifyGuardianMonitorCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rentify:guardian-monitor 
                            {--notify : Paksa pengiriman notifikasi WhatsApp meskipun sistem normal}
                            {--diagnostics : Tampilkan rincian metrik diagnostik lengkap ke terminal}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Rentify Server Guardian: Monitoring otonom 24/7 mendeteksi status website, database Aiven, error logs, dan alert WhatsApp';

    /**
     * Execute the console command.
     */
    public function handle(RentifyGuardianService $guardian): int
    {
        $this->info("=================================================");
        $this->info("🛡️ RENTIFY AI SERVER GUARDIAN - 24/7 MONITORING");
        $this->info("=================================================");

        $forceNotify = (bool) $this->option('notify');
        $showDiag = (bool) $this->option('diagnostics');

        $this->line("Memulai pemeriksaan status sistem...");
        $result = $guardian->runAutonomousMonitoring($forceNotify);

        $statusColor = $result['is_healthy'] ? 'info' : 'error';
        $this->line("Waktu: <comment>{$result['timestamp']}</comment>");
        $this->line("Status Kesehatan: <{$statusColor}>" . ($result['is_healthy'] ? 'NORMAL & STABIL' : 'TERDETEKSI ANOMALI (' . $result['severity'] . ')') . "</{$statusColor}>");

        if (!empty($result['issues'])) {
            $this->warn("Daftar Isu:");
            foreach ($result['issues'] as $issue) {
                $this->line(" - <fg=red>{$issue}</>");
            }
        }

        if ($result['alert_sent']) {
            $this->warn("🚨 Alert WhatsApp darurat telah dikirimkan ke pemilik.");
        } elseif ($result['recovery_sent']) {
            $this->info("✅ Notifikasi pemulihan sistem berhasil dikirimkan ke pemilik.");
        } else {
            $this->line("Tidak ada alert yang perlu dikirim (sistem stabil atau dalam cooldown).");
        }

        if ($showDiag) {
            $this->newLine();
            $diag = $guardian->getLiveDiagnostics();

            $this->table(
                ['Komponen', 'Metrik', 'Status'],
                [
                    ['Website', "{$diag['website']['target_url']} (HTTP {$diag['website']['status_code']})", $diag['website']['is_ok'] ? 'OK (' . $diag['website']['latency_ms'] . 'ms)' : 'FAIL'],
                    ['Database', "Aiven MySQL ({$diag['database']['name']})", $diag['database']['is_ok'] ? 'OK (' . $diag['database']['latency_ms'] . 'ms)' : 'FAIL'],
                    ['Error Logs', "15m: {$diag['logs']['errors_last_15m']} | 1h: {$diag['logs']['errors_last_1h']} | 24h: {$diag['logs']['errors_last_24h']}", $diag['logs']['latest_error']],
                    ['RAM & Disk', "RAM: {$diag['resources']['memory_usage_mb']}MB | Disk: {$diag['resources']['disk_used_percent']}%", "Free {$diag['resources']['disk_free_gb']}GB"],
                    ['Firewall', "WAF & Auto-ban: " . ($diag['security']['firewall_enabled'] ? 'Active' : 'Off'), "Threats today: {$diag['security']['blocked_threats_today']}"],
                    ['Transaksi', "Pending: {$diag['transactions']['pending_payments_24h']} | Menunggu Konfirmasi: {$diag['transactions']['waiting_confirm']}", 'Data OK'],
                ]
            );
        }

        return $result['is_healthy'] ? Command::SUCCESS : Command::FAILURE;
    }
}
