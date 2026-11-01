<?php

namespace App\Console\Commands;

use App\Services\SocialProofService;
use Illuminate\Console\Command;

class ReengageInactiveCustomersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rentify:customers:reengage-inactive {--days=30 : Ambang batas ketidakaktifan pelanggan dalam hari}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Jalankan re-engagement berkala untuk pelanggan tidak aktif (30/60 hari) dengan validasi opt-in marketing dan penekanan sengketa aktif.';

    /**
     * Execute the console command.
     */
    public function handle(SocialProofService $socialProofService): int
    {
        $days = (int) $this->option('days') ?: 30;

        $this->info("Memulai alur re-engagement pelanggan tidak aktif selama {$days} hari...");

        $result = $socialProofService->reengageInactiveCustomers($days);

        $this->line("Total kandidat diproses: {$result['processed_count']}");
        $this->line("Berhasil di-reengage: {$result['reengaged_count']}");
        $this->line("Ditekan karena opt-out marketing: {$result['suppressed_opt_out_count']}");
        $this->line("Ditekan karena sengketa aktif: {$result['suppressed_dispute_count']}");

        $this->info("Alur re-engagement selesai dengan sukses.");

        return Command::SUCCESS;
    }
}
