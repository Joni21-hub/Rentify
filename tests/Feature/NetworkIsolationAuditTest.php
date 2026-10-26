<?php

namespace Tests\Feature;

use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class NetworkIsolationAuditTest extends TestCase
{
    public function test_runtime_database_configurations_are_strictly_isolated_from_cloud(): void
    {
        $connections = config('database.connections');
        $this->assertIsArray($connections);

        foreach ($connections as $name => $config) {
            $host = strtolower((string)($config['host'] ?? ''));
            $url = strtolower((string)($config['url'] ?? ''));
            $port = (string)($config['port'] ?? '');

            $this->assertStringNotContainsString('aivencloud.com', $host, "Connection '{$name}' host must not point to Aiven cloud");
            $this->assertStringNotContainsString('aivencloud.com', $url, "Connection '{$name}' url must not point to Aiven cloud");
            $this->assertNotEquals('18655', $port, "Connection '{$name}' port must not be cloud port 18655");
        }
    }

    public function test_all_database_queries_flow_exclusively_to_local_mysql(): void
    {
        $interceptedQueries = [];

        Event::listen(QueryExecuted::class, function (QueryExecuted $event) use (&$interceptedQueries) {
            $host = $event->connection->getConfig('host');
            $port = $event->connection->getConfig('port');
            $database = $event->connection->getConfig('database');

            $interceptedQueries[] = [
                'sql' => $event->sql,
                'host' => $host,
                'port' => $port,
                'database' => $database,
            ];

            // Asersi instan pada setiap event query
            if (str_contains(strtolower((string)$host), 'aivencloud.com') || (string)$port === '18655') {
                $this->fail("Active query routed to remote cloud: host={$host}, port={$port}, sql={$event->sql}");
            }
        });

        // Eksekusi rentetan kueri aplikasi
        DB::table('users')->count();
        DB::table('barang')->count();
        DB::table('orders')->count();

        $this->assertNotEmpty($interceptedQueries);
        foreach ($interceptedQueries as $q) {
            $this->assertEquals('127.0.0.1', $q['host']);
            $this->assertEquals('3306', (string)$q['port']);
            $this->assertEquals('rentify_test', $q['database']);
        }
    }

    public function test_active_pdo_connection_dsn_and_server_info_are_local(): void
    {
        $pdo = DB::connection()->getPdo();
        $this->assertNotNull($pdo);

        $serverInfo = $pdo->getAttribute(\PDO::ATTR_SERVER_INFO);
        $this->assertNotEmpty($serverInfo);

        // Kueri server variable untuk host & port
        $status = DB::select("SHOW VARIABLES WHERE Variable_name IN ('hostname', 'port')");
        $vars = [];
        foreach ($status as $row) {
            $vars[$row->Variable_name] = $row->Value;
        }

        $this->assertEquals('3306', $vars['port'] ?? null);
    }

    public function test_http_prevent_stray_requests_guard_blocks_unmocked_cloud_calls(): void
    {
        Http::preventStrayRequests();

        // Verifikasi bahwa pemanggilan eksternal liar dicegat
        try {
            Http::get('https://mysql-rentify-rentify21.b.aivencloud.com');
            $this->fail('Stray HTTP request to cloud should have been intercepted and blocked.');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('Attempted request', $e->getMessage());
        }
    }
}
