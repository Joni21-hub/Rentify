<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('wa:send {target} {message}', function ($target, $message) {
    $res = \App\Services\WhatsAppService::send($target, $message);
    $this->info(json_encode($res));
})->purpose('Send WhatsApp message via configured gateway');

Artisan::command('wa:reset-sessions', function () {
    $res = \App\Services\WhatsAppService::resetChatSessions();
    $this->info($res ? 'Reset successful' : 'Reset failed');
})->purpose('Reset WhatsApp chat sessions');

/*
|--------------------------------------------------------------------------
| AI Marketing Agent - Daily Reels/TikTok Content Generation Schedule
|--------------------------------------------------------------------------
|
| Menjalankan generator video marketing secara otomatis setiap hari.
| - Hari Ganjil: Menarik Customer (Penyewa)
| - Hari Genap: Menarik Vendor (Pemilik Barang)
|
*/
Schedule::command('rentify:generate-daily-content')
    ->dailyAt(config('rentify_ai.automation.schedule_time', '08:00'))
    ->name('rentify-ai-marketing-daily')
    ->withoutOverlapping();

