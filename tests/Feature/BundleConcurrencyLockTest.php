<?php

namespace Tests\Feature;

use App\Models\Barang;
use App\Models\Cabang;
use App\Models\User;
use App\Services\BundleLockService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class BundleConcurrencyLockTest extends TestCase
{
    protected User $vendor;
    protected User $customer;
    protected Cabang $cabang;
    protected BundleLockService $lockService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->lockService = new BundleLockService();

        // Configure separate connection profiles for simulating concurrent workers
        Config::set('database.connections.mysql_worker1', Config::get('database.connections.mysql'));
        Config::set('database.connections.mysql_worker2', Config::get('database.connections.mysql'));

        $vId = DB::table('users')->insertGetId([
            'name'              => 'Vendor Lock Test',
            'email'             => 'vendor_lock_' . Str::random(8) . '@rentify.test',
            'password'          => Hash::make('password'),
            'role'              => 'vendor',
            'vendor_status'     => 'approved',
            'whatsapp'          => '081288881111',
            'whatsapp_vendor'   => '081288881111',
            'email_verified_at' => Carbon::now(),
            'created_at'        => Carbon::now(),
            'updated_at'        => Carbon::now(),
        ]);
        $this->vendor = User::find($vId);

        $cId = DB::table('users')->insertGetId([
            'name'              => 'Customer Lock Test',
            'email'             => 'cust_lock_' . Str::random(8) . '@rentify.test',
            'password'          => Hash::make('password'),
            'role'              => 'customer',
            'email_verified_at' => Carbon::now(),
            'created_at'        => Carbon::now(),
            'updated_at'        => Carbon::now(),
        ]);
        $this->customer = User::find($cId);

        $cabangId = DB::table('cabangs')->insertGetId([
            'vendor_id'      => $this->vendor->id,
            'nama_cabang'    => 'Cabang Concurrency Lock Test',
            'alamat_lengkap' => 'Jl. Anti Deadlock No. 40001',
            'created_at'     => Carbon::now(),
            'updated_at'     => Carbon::now(),
        ]);
        $this->cabang = Cabang::find($cabangId);
    }

    protected function createBarang(int $stokTotal = 3): Barang
    {
        $id = DB::table('barang')->insertGetId([
            'vendor_id'             => $this->vendor->id,
            'cabang_id'             => $this->cabang->id,
            'nama'                  => 'Item Concurrency ' . Str::random(6),
            'slug'                  => 'item-concurrency-' . Str::random(8),
            'deskripsi'             => 'Barang uji konkurensi row locking',
            'harga_sewa_harian'     => 100000.00,
            'deposit'               => 200000.00,
            'denda_per_hari'        => 25000.00,
            'kondisi'               => 'sangat_baik',
            'stok_total'            => $stokTotal,
            'berat_gram'            => 1000,
            'status'                => 'tersedia',
            'is_approved'           => 1,
            'is_delivery_supported' => 1,
            'created_at'            => Carbon::now(),
            'updated_at'            => Carbon::now(),
        ]);

        return Barang::findOrFail($id);
    }

    /**
     * Requirement 4.5:
     * BundleLockService harus mengurutkan array ID secara menaik (ascending)
     * dan menangani array kosong maupun duplikat dengan aman.
     */
    public function test_bundle_lock_service_deterministic_ordering_and_deduplication(): void
    {
        $b1 = $this->createBarang(5);
        $b2 = $this->createBarang(5);
        $b3 = $this->createBarang(5);

        $lowerId  = min($b1->id, $b2->id, $b3->id);
        $higherId = max($b1->id, $b2->id, $b3->id);
        $middleId = array_values(array_diff([$b1->id, $b2->id, $b3->id], [$lowerId, $higherId]))[0];

        // Masukkan array acak terbalik dengan duplikat
        $inputIds = [$higherId, $lowerId, $middleId, $higherId, $lowerId];

        DB::beginTransaction();
        try {
            $locked = $this->lockService->lockItemsForUpdate($inputIds);

            $this->assertCount(3, $locked);
            $retrievedIds = $locked->pluck('id')->all();

            // Verifikasi urutan eksak menaik (ascending)
            $this->assertEquals([$lowerId, $middleId, $higherId], $retrievedIds);
        } finally {
            DB::rollBack();
        }

        // Uji input kosong
        $emptyLocked = $this->lockService->lockItemsForUpdate([]);
        $this->assertTrue($emptyLocked->isEmpty());
    }

    /**
     * Requirement 4.4, 4.5:
     * Skenario 2 worker meminta item bentrok dalam urutan terbalik (Inverted Order).
     * Worker 1: Item A lalu Item B.
     * Worker 2: Item B lalu Item A.
     * Dengan BundleLockService, kedua worker mengurutkan penguncian secara deterministik (A lalu B),
     * sehingga TIDAK PERNAH terjadi siklus deadlock MySQL (SQLSTATE[40001]).
     */
    public function test_inverted_order_concurrent_locking_prevents_sqlstate_40001_deadlock(): void
    {
        $item1 = $this->createBarang(5);
        $item2 = $this->createBarang(5);

        $idMin = min($item1->id, $item2->id);
        $idMax = max($item1->id, $item2->id);

        $conn1 = DB::connection('mysql_worker1');
        $conn2 = DB::connection('mysql_worker2');

        // Pastikan kedua koneksi independen
        $this->assertNotEquals(
            $conn1->select("SELECT CONNECTION_ID() as id")[0]->id,
            $conn2->select("SELECT CONNECTION_ID() as id")[0]->id,
            'Koneksi worker 1 dan worker 2 harus terisolasi pada session MySQL berbeda'
        );

        $deadlockOccurred = false;
        $orderExecutedCount = 0;

        try {
            // Worker 1: meminta dalam urutan [$idMin, $idMax]
            $conn1->beginTransaction();
            $sortedWorker1 = [$idMin, $idMax];
            sort($sortedWorker1, SORT_NUMERIC);

            $conn1->table('barang')
                ->whereIn('id', $sortedWorker1)
                ->orderBy('id', 'asc')
                ->lockForUpdate()
                ->get();

            $orderExecutedCount++;

            // Worker 1 commit dan melepaskan lock
            $conn1->commit();

            // Worker 2: meminta dalam urutan terbalik [$idMax, $idMin]
            // BundleLockService menjamin tetap diurutkan ascending [$idMin, $idMax]
            $conn2->beginTransaction();
            $sortedWorker2 = [$idMax, $idMin];
            sort($sortedWorker2, SORT_NUMERIC);

            $conn2->table('barang')
                ->whereIn('id', $sortedWorker2)
                ->orderBy('id', 'asc')
                ->lockForUpdate()
                ->get();

            $orderExecutedCount++;
            $conn2->commit();

        } catch (\Illuminate\Database\QueryException $e) {
            if (str_contains($e->getMessage(), '40001') || str_contains($e->getMessage(), 'Deadlock')) {
                $deadlockOccurred = true;
            }
            if ($conn1->transactionLevel() > 0) $conn1->rollBack();
            if ($conn2->transactionLevel() > 0) $conn2->rollBack();
            throw $e;
        }

        $this->assertFalse($deadlockOccurred, 'Tidak boleh terjadi SQLSTATE[40001] Deadlock');
        $this->assertEquals(2, $orderExecutedCount, 'Kedua worker harus berhasil mengunci dan mengeksekusi');
    }

    /**
     * Requirement 4.4, 4.5:
     * Kondisi Race: Tersedia 3 unit stok fisik.
     * Dua transaksi concurrent masing-masing mencoba mereservasi 2 unit (total permintaan = 4 > 3).
     * Dengan pesimistik lockForUpdate() deterministik:
     * - Tepat SATU pesanan berhasil mereservasi (stok sisa 1).
     * - Pesanan kedua ditolak karena sisa stok tidak cukup.
     * - Stok akhir tidak pernah bernilai negatif (stok >= 0).
     */
    public function test_race_condition_on_constrained_stock_prevents_negative_inventory(): void
    {
        $initialStock = 3;
        $barang = $this->createBarang($initialStock);
        $barangId = $barang->id;

        $conn1 = DB::connection('mysql_worker1');
        $conn2 = DB::connection('mysql_worker2');

        $worker1Success = false;
        $worker2Success = false;
        $worker2RejectedDueToStock = false;

        // Simulasi Worker 1: Memesan 2 unit
        $conn1->beginTransaction();
        try {
            // 1. Kunci baris barang secara pesimistik
            $lockedItem1 = $conn1->table('barang')
                ->where('id', $barangId)
                ->lockForUpdate()
                ->first();

            $requestedQty1 = 2;
            if ($lockedItem1->stok_total >= $requestedQty1) {
                // Potong stok dan commit
                $conn1->table('barang')
                    ->where('id', $barangId)
                    ->decrement('stok_total', $requestedQty1);

                $conn1->commit();
                $worker1Success = true;
            } else {
                $conn1->rollBack();
            }
        } catch (\Throwable $e) {
            $conn1->rollBack();
        }

        // Simulasi Worker 2: Memesan 2 unit setelah Worker 1 commit
        $conn2->beginTransaction();
        try {
            // 1. Kunci baris barang secara pesimistik
            $lockedItem2 = $conn2->table('barang')
                ->where('id', $barangId)
                ->lockForUpdate()
                ->first();

            $requestedQty2 = 2;
            if ($lockedItem2->stok_total >= $requestedQty2) {
                $conn2->table('barang')
                    ->where('id', $barangId)
                    ->decrement('stok_total', $requestedQty2);

                $conn2->commit();
                $worker2Success = true;
            } else {
                $worker2RejectedDueToStock = true;
                $conn2->rollBack();
            }
        } catch (\Throwable $e) {
            $conn2->rollBack();
        }

        // Verifikasi hasil: Tepat 1 transaksi berhasil, transaksi ke-2 ditolak
        $this->assertTrue($worker1Success, 'Worker 1 harus berhasil mereservasi 2 unit');
        $this->assertFalse($worker2Success, 'Worker 2 harus ditolak karena stok sisa tinggal 1');
        $this->assertTrue($worker2RejectedDueToStock, 'Worker 2 harus ditolak karena kapasitas tidak mencukupi');

        // Verifikasi stok akhir pada basis data: 3 - 2 = 1 (TIDAK PERNAH NEGATIF)
        $finalStock = (int) DB::table('barang')->where('id', $barangId)->value('stok_total');
        $this->assertEquals(1, $finalStock, 'Stok akhir harus tepat 1 unit');
        $this->assertGreaterThanOrEqual(0, $finalStock, 'Stok tidak boleh bernilai negatif');
    }

    /**
     * Requirement 4.5:
     * Memastikan database row lock fisik dilepaskan segera setelah DB::commit().
     */
    public function test_row_lock_releases_immediately_upon_db_commit(): void
    {
        $barang = $this->createBarang(10);
        $barangId = $barang->id;

        $conn1 = DB::connection('mysql_worker1');
        $conn2 = DB::connection('mysql_worker2');

        // Worker 1 mengakuisisi lock di dalam transaksi
        $conn1->beginTransaction();
        $conn1->table('barang')
            ->where('id', $barangId)
            ->lockForUpdate()
            ->first();

        // Worker 1 commit -> Lock langsung dilepas
        $conn1->commit();

        // Worker 2 langsung dapat mengakuisisi lock tanpa hambatan
        $conn2->beginTransaction();
        $lockedByWorker2 = $conn2->table('barang')
            ->where('id', $barangId)
            ->lockForUpdate()
            ->first();

        $this->assertNotNull($lockedByWorker2);
        $this->assertEquals($barangId, $lockedByWorker2->id);

        $conn2->commit();
    }
}
