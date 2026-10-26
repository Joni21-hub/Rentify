<?php

namespace App\Jobs;

use App\Models\Moment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessAiMomentTaggingJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $momentId;

    /**
     * Create a new job instance.
     */
    public function __construct(int $momentId)
    {
        $this->momentId = $momentId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $moment = Moment::find($this->momentId);
        if (!$moment) {
            Log::warning("ProcessAiMomentTaggingJob: Moment ID {$this->momentId} tidak ditemukan.");
            return;
        }

        Log::info("ProcessAiMomentTaggingJob: Memulai analisis AI untuk Moment ID {$this->momentId}.");
    }
}
