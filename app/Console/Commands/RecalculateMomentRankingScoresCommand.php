<?php

namespace App\Console\Commands;

use App\Models\Moment;
use Illuminate\Console\Command;

class RecalculateMomentRankingScoresCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'moments:recalculate-ranking-scores';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recalculate gravity ranking scores for active moments in chunks';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Memulai perhitungan ulang skor ranking gravitasi momen...');

        $processedCount = 0;

        Moment::where('status', 'active')->chunkById(200, function ($moments) use (&$processedCount) {
            foreach ($moments as $moment) {
                $moment->updateRankingScore();
                $processedCount++;
            }
        });

        $this->info("Berhasil memperbarui skor ranking gravitasi untuk {$processedCount} momen aktif.");

        return Command::SUCCESS;
    }
}
