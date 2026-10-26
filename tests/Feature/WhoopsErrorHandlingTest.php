<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class WhoopsErrorHandlingTest extends TestCase
{
    public function test_detailed_error_page_renders_stack_trace_when_debug_enabled(): void
    {
        // Daftarkan rute fixture uji sementara secara dinamis (tanpa mencemari routes/web.php)
        Route::get('/_testing_fixture_exception', function () {
            throw new \RuntimeException('Controlled Test Exception For Whoops Verification');
        });

        config(['app.debug' => true, 'app.env' => 'local']);

        $response = $this->get('/_testing_fixture_exception');

        $response->assertStatus(500);
        $content = $response->getContent();

        // Asersi keberadaan pesan galat, nama kelas exception, dan indikator stack trace
        $this->assertStringContainsString('Controlled Test Exception For Whoops Verification', (string)$content);
        $this->assertStringContainsString('RuntimeException', (string)$content);
        $this->assertTrue(
            str_contains((string)$content, 'Whoops') ||
            str_contains((string)$content, 'stack') ||
            str_contains((string)$content, 'trace') ||
            str_contains((string)$content, 'vendor'),
            'Response must render detailed debug stack trace information'
        );
    }

    public function test_production_routes_file_remains_clean_of_debug_fixtures(): void
    {
        $webRoutesContent = file_get_contents(base_path('routes/web.php'));
        $this->assertStringNotContainsString('_testing_fixture_exception', $webRoutesContent);
        $this->assertStringNotContainsString('Controlled Test Exception', $webRoutesContent);
    }
}
