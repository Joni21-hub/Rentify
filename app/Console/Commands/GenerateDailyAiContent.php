<?php

namespace App\Console\Commands;

use App\Models\AiMarketingPost;
use App\Services\AiMarketingService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GenerateDailyAiContent extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rentify:generate-daily-content 
                            {--force-target= : Force target audience (customer or vendor)} 
                            {--date= : Custom date (YYYY-MM-DD)}
                            {--platform=all : Target platform to publish (all, instagram, tiktok, facebook)}
                            {--publish : Auto-post to configured social media platforms}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'AI Marketing Agent: Otomatis generate ide video viral & naskah Reels/TikTok/FB harian untuk mempromosikan Rentify';

    /**
     * Execute the console command.
     */
    public function handle(AiMarketingService $aiService): int
    {
        $this->info("=================================================");
        $this->info("🤖 RENTIFY AI MARKETING AGENT - DAILY ROTATION");
        $this->info("=================================================");

        $dateStr = $this->option('date');
        $date = $dateStr ? Carbon::parse($dateStr) : Carbon::now();
        $dayNumber = (int) $date->format('j');
        $forceTarget = $this->option('force-target');

        $isOdd = ($dayNumber % 2 !== 0);
        $expectedTarget = $forceTarget ?: ($isOdd ? 'customer' : 'vendor');

        $this->line("📅 Tanggal: <comment>{$date->format('d M Y')}</comment> (Hari ke-{$dayNumber})");
        $this->line("🔄 Pola Hari: <comment>" . ($isOdd ? 'GANJIL (Odd Day)' : 'GENAP (Even Day)') . "</comment>");
        $this->line("🎯 Target Audiens: <info>" . strtoupper($expectedTarget) . " (" . ($expectedTarget === 'customer' ? 'Penyewa / Edukasi Hemat' : 'Vendor / Pemilik Barang') . ")</info>");

        $this->newLine();
        $this->comment("⏳ Sedang memproses AI Content Generator...");

        try {
            $post = $aiService->generateDailyVideoContent($date, $forceTarget);

            $this->info("✅ Berhasil membuat konten harian (ID #{$post->id})!");
            $this->newLine();

            $this->table(
                ['Parameter', 'Detail Output'],
                [
                    ['Tema', $post->tema],
                    ['Target', $post->target_audience === 'customer' ? 'Customer (Penyewa)' : 'Vendor (Pemilik Barang)'],
                    ['Kategori / Persona', $post->kategori_barang ?: $post->persona_vendor],
                    ['Hook Masalah', $post->hook_masalah],
                    ['AI Engine', $post->ai_source],
                    ['Durasi Naskah', '4 Adegan (15 - 30 Detik)'],
                    ['Format Visual', '9:16 Vertikal (Instagram Reels & TikTok)'],
                ]
            );

            // Tampilkan rincian naskah video & teks layar
            $this->newLine();
            $this->info("🎬 NASKAH LENGKAP VIDEO PENDEK (15 - 30 DETIK):");
            if (is_array($post->naskah_video)) {
                foreach ($post->naskah_video as $scene) {
                    $detik = $scene['detik'] ?? '';
                    $adegan = $scene['adegan'] ?? '';
                    $visual = $scene['visual'] ?? '';
                    $voice = $scene['voiceover'] ?? '';
                    $this->line("⏱️  <comment>[{$detik}] {$adegan}</comment>");
                    $this->line("👁️  Visual : {$visual}");
                    $this->line("🎙️  Voice  : \"{$voice}\"");
                    $this->line("-------------------------------------------------");
                }
            }

            $this->newLine();
            $this->info("📱 CAPTION & CALL TO ACTION (CTA):");
            $this->line($post->caption);

            // Auto-publish jika diminta via opsi command atau konfigurasi
            $platformOption = strtolower((string)$this->option('platform'));
            $shouldPublish = $this->option('publish') || config('rentify_ai.automation.auto_post_instagram') || config('rentify_ai.automation.auto_post_tiktok') || config('rentify_ai.automation.auto_post_facebook');

            if ($shouldPublish) {
                $this->newLine();
                $this->info("🚀 Menjalankan Auto-Post ke Media Sosial (Target: {$platformOption})...");

                if ($platformOption === 'all' || $platformOption === 'instagram') {
                    if (config('rentify_ai.automation.auto_post_instagram') || $this->option('publish')) {
                        $igRes = $aiService->publishToInstagram($post);
                        $this->line("📷 Instagram: " . ($igRes['success'] ? "<info>OK ({$igRes['id']})</info>" : "<error>{$igRes['message']}</error>"));
                    }
                }

                if ($platformOption === 'all' || $platformOption === 'tiktok') {
                    if (config('rentify_ai.automation.auto_post_tiktok') || $this->option('publish')) {
                        $ttRes = $aiService->publishToTikTok($post);
                        $this->line("🎵 TikTok   : " . ($ttRes['success'] ? "<info>OK ({$ttRes['id']})</info>" : "<error>{$ttRes['message']}</error>"));
                    }
                }

                if ($platformOption === 'all' || $platformOption === 'facebook') {
                    if (config('rentify_ai.automation.auto_post_facebook') || $this->option('publish')) {
                        $fbRes = $aiService->publishToFacebook($post);
                        $this->line("👥 Facebook : " . ($fbRes['success'] ? "<info>OK ({$fbRes['id']})</info>" : "<error>{$fbRes['message']}</error>"));
                    }
                }
            }

            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $this->error("❌ Gagal men-generate konten: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
