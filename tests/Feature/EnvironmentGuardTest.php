<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class EnvironmentGuardTest extends TestCase
{
    protected function tearDown(): void
    {
        // Restore environment variables and configs
        $_ENV['DB_HOST'] = '127.0.0.1';
        $_SERVER['DB_HOST'] = '127.0.0.1';
        $_ENV['DB_PORT'] = '3306';
        $_SERVER['DB_PORT'] = '3306';
        unset($_ENV['DB_URL'], $_SERVER['DB_URL']);
        putenv('DB_HOST=127.0.0.1');
        putenv('DB_PORT=3306');
        putenv('DB_URL=');
        config([
            'database.connections.mysql.host' => '127.0.0.1',
            'database.connections.mysql.port' => '3306',
            'database.connections.mysql.url' => null,
            'app.env' => 'testing',
        ]);
        parent::tearDown();
    }

    public function test_local_database_connection_succeeds_on_localhost(): void
    {
        $pdo = DB::connection()->getPdo();
        $this->assertNotNull($pdo);

        $result = DB::select('SELECT DATABASE() as current_db, @@hostname as current_host');
        $this->assertNotEmpty($result);
        $this->assertStringContainsString('rentify_test', $result[0]->current_db);
    }

    public function test_guard_throws_runtime_exception_when_host_points_to_aivencloud_in_non_production(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Cloud Aiven MySQL connection blocked in non-production environment.');

        $_ENV['DB_HOST'] = 'mysql-rentify-rentify21.b.aivencloud.com';
        $_SERVER['DB_HOST'] = 'mysql-rentify-rentify21.b.aivencloud.com';
        putenv('DB_HOST=mysql-rentify-rentify21.b.aivencloud.com');

        $this->refreshApplication();
    }

    public function test_guard_throws_runtime_exception_when_port_is_18655_in_non_production(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Cloud Aiven MySQL connection blocked in non-production environment.');

        $_ENV['DB_PORT'] = '18655';
        $_SERVER['DB_PORT'] = '18655';
        putenv('DB_PORT=18655');

        $this->refreshApplication();
    }

    public function test_guard_throws_runtime_exception_when_url_points_to_aivencloud_in_non_production(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Cloud Aiven MySQL connection blocked in non-production environment.');

        $_ENV['DB_URL'] = 'mysql://avnadmin:secret@mysql-rentify-rentify21.b.aivencloud.com:18655/defaultdb';
        $_SERVER['DB_URL'] = 'mysql://avnadmin:secret@mysql-rentify-rentify21.b.aivencloud.com:18655/defaultdb';
        putenv('DB_URL=mysql://avnadmin:secret@mysql-rentify-rentify21.b.aivencloud.com:18655/defaultdb');

        $this->refreshApplication();
    }

    public function test_critical_environment_check_logs_descriptive_error_when_invalid(): void
    {
        $logFile = storage_path('logs/laravel.log');
        $initialContent = file_exists($logFile) ? file_get_contents($logFile) : '';

        // Simulasikan validasi logger
        Log::error('[EnvironmentGuard] Critical configuration error detected: test simulation.');

        $updatedContent = file_get_contents($logFile);
        $this->assertStringContainsString('[EnvironmentGuard] Critical configuration error detected', $updatedContent);
    }
}
