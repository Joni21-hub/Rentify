<?php

namespace Tests\Feature;

use App\Models\Barang;
use App\Models\Cabang;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class ReleaseExpiredBundleReservationsCommandTest extends TestCase
{
    protected User $vendor;
    protected User $customer;
    protected Cabang $cabang;

    protected function setUp(): void
    {
        parent::setUp();

        $vId = DB::table('users')->insertGetId([
            'name'              => 'Vendor Release Test',
            'email'             => 'vendor_release_' . Str::random(8) . '@rentify.test',
            'password'          => Hash::make('password'),
            'role'              => 'vendor',
            'vendor_status'     => 'approved',
            'whatsapp'          => '081277771111',
            'whatsapp_vendor'   => '081277771111',
            'email_verified_at' => Carbon::now(),
            'created_at'        => Carbon::now(),
            'updated_at'        => Carbon::now(),
        ]);
        $this->vendor = User::find($vId);

        $cId = DB::table('users')->insertGetId([
            'name'              => 'Customer Release Test',
            'email'             => 'cust_release_' . Str::random(8) . '@rentify.test',
            'password'          => Hash::make('password'),
            'role'              => 'customer',
            'email_verified_at' => Carbon::now(),
            'created_at'        => Carbon::now(),
            'updated_at'        => Carbon::now(),
        ]);
        $this->customer = User::find($cId);

        $cabangId = DB::table('cabangs')->insertGetId([
            'vendor_id'      => $this->vendor->id,
            'nama_cabang'    => 'Cabang Release Test',
            'alamat_lengkap' => 'Jl. Expiration Release No. 15',
            'created_at'     => Carbon::now(),
            'updated_at'     => Carbon::now(),
        ]);
        $this->cabang = Cabang::find($cabangId);
    }

    protected function createBarang(int $stokTotal = 2): Barang
    {
        $id = DB::table('barang')->insertGetId([
            'vendor_id'             => $this->vendor->id,
            'cabang_id'             => $this->cabang->id,
            'nama'                  => 'Kamera Release Test ' . Str::random(5),
            'slug'                  => 'kamera-release-test-' . Str::random(8),
            'deskripsi'             => 'Barang uji pelepasan reservasi kedaluwarsa',
            'harga_sewa_harian'     => 120000.00,
            'deposit'               => 300000.00,
            'denda_per_hari'        => 30000.00,
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

    protected function createOrder(array $attributes = []): int
    {
        $now = Carbon::now();
        $start = $attributes['start_rent'] ?? $attributes['tanggal_mulai'] ?? $now;
        $end = $attributes['end_rent'] ?? $attributes['tanggal_selesai'] ?? $now->copy()->addDay();

        return DB::table('orders')->insertGetId(array_merge([
            'user_id'            => $this->customer->id,
            'vendor_id'          => $this->vendor->id,
            'customer_name'      => $this->customer->name,
            'customer_whatsapp'  => '081299994444',
            'kode_booking'       => 'RNT' . strtoupper(Str::random(8)),
            'metode_pengambilan' => 'Ambil Sendiri',
            'payment_method'     => 'QRIS',
            'status'             => 'Menunggu Pembayaran',
            'tanggal_mulai'      => Carbon::parse($start)->toDateString(),
            'tanggal_selesai'    => Carbon::parse($end)->toDateString(),
            'start_rent'         => Carbon::parse($start),
            'end_rent'           => Carbon::parse($end),
            'total_biaya'        => 120000.00,
            'created_at'         => $now,
            'updated_at'         => $now,
        ], $attributes));
    }

    /**
     * Requirement 4.8:
     * Command harus membatalkan pesanan kedaluwarsa ('Dibatalkan') dan melepaskan slot kalender
     * sehingga Barang::getAvailabilityData() langsung menyatakan barang tersedia kembali.
     */
    public function test_command_cancels_expired_unpaid_orders_and_releases_calendar_availability(): void
    {
        $barang = $this->createBarang(2); // Total stock = 2

        $targetDate = Carbon::now()->addDays(2)->format('Y-m-d');
        $startRent  = Carbon::parse($targetDate . ' 08:00:00');
        $endRent    = Carbon::parse($targetDate . ' 23:59:59');

        // Buat pesanan yang sudah melewati TTL 15 menit (payment_expired_at di masa lampau)
        $expiredOrderId = $this->createOrder([
            'status'             => 'Menunggu Pembayaran',
            'payment_expired_at' => Carbon::now()->subMinutes(5), // Kedaluwarsa 5 menit lalu
            'start_rent'         => $startRent,
            'end_rent'           => $endRent,
            'tanggal_mulai'      => $startRent->toDateString(),
            'tanggal_selesai'    => $endRent->toDateString(),
        ]);

        DB::table('order_items')->insert([
            'order_id'     => $expiredOrderId,
            'product_id'   => $barang->id,
            'product_name' => $barang->nama,
            'price'        => 120000.00,
            'quantity'     => 2, // Mengunci seluruh stok (2 unit)
            'item_type'    => 'barang',
            'created_at'   => Carbon::now(),
            'updated_at'   => Carbon::now(),
        ]);

        // SEBELUM COMMAND: Tanggal harus berstatus fully booked karena ditahan oleh pesanan pending
        $beforeRelease = $barang->getAvailabilityData(10, 1);
        $this->assertContains(
            $targetDate,
            $beforeRelease['fully_booked_dates'],
            'Sebelum release command dieksekusi, tanggal harus fully booked oleh pesanan pending'
        );

        // Jalankan console command pembersihan reservasi expired
        $this->artisan('rentify:release-expired-bundle-reservations')
            ->expectsOutputToContain('Berhasil melepaskan 1 reservasi sewa yang kedaluwarsa.')
            ->assertSuccessful();

        // Verifikasi status pesanan berubah menjadi 'Dibatalkan'
        $updatedStatus = DB::table('orders')->where('id', $expiredOrderId)->value('status');
        $this->assertEquals('Dibatalkan', $updatedStatus, 'Status pesanan kedaluwarsa harus diubah menjadi Dibatalkan');

        // SETELAH COMMAND: Stok dan slot tanggal harus langsung bebas di kalender ketersediaan
        $afterRelease = $barang->getAvailabilityData(10, 1);
        $this->assertNotContains(
            $targetDate,
            $afterRelease['fully_booked_dates'],
            'Setelah release command dieksekusi, tanggal harus langsung tersedia kembali'
        );
        $this->assertArrayNotHasKey(
            $targetDate,
            $afterRelease['booked_slots'],
            'Slot waktu harus sepenuhnya dibebaskan setelah pembatalan pesanan expired'
        );
    }

    /**
     * Requirement 4.8:
     * Command tidak boleh membatalkan pesanan yang belum melewati batas waktu (payment_expired_at > now()).
     */
    public function test_command_does_not_cancel_active_unexpired_orders(): void
    {
        $barang = $this->createBarang(2);

        $targetDate = Carbon::now()->addDays(3)->format('Y-m-d');
        $startRent  = Carbon::parse($targetDate . ' 08:00:00');
        $endRent    = Carbon::parse($targetDate . ' 23:59:59');

        // Pesanan masih memiliki sisa waktu 10 menit
        $activeOrderId = $this->createOrder([
            'status'             => 'Menunggu Pembayaran',
            'payment_expired_at' => Carbon::now()->addMinutes(10),
            'start_rent'         => $startRent,
            'end_rent'           => $endRent,
            'tanggal_mulai'      => $startRent->toDateString(),
            'tanggal_selesai'    => $endRent->toDateString(),
        ]);

        DB::table('order_items')->insert([
            'order_id'     => $activeOrderId,
            'product_id'   => $barang->id,
            'product_name' => $barang->nama,
            'price'        => 120000.00,
            'quantity'     => 2,
            'item_type'    => 'barang',
            'created_at'   => Carbon::now(),
            'updated_at'   => Carbon::now(),
        ]);

        $this->artisan('rentify:release-expired-bundle-reservations')
            ->expectsOutputToContain('Tidak ada reservasi sewa yang kedaluwarsa.')
            ->assertSuccessful();

        $status = DB::table('orders')->where('id', $activeOrderId)->value('status');
        $this->assertEquals('Menunggu Pembayaran', $status, 'Pesanan yang belum kedaluwarsa tidak boleh dibatalkan');
    }

    /**
     * Requirement 4.4, 4.8:
     * Memastikan reservasi komponen bundle dalam order_item_components ikut dibebaskan
     * ketika pesanan bundle kedaluwarsa dibatalkan.
     */
    public function test_command_cancels_expired_bundle_reservations_and_releases_components(): void
    {
        $komponen = $this->createBarang(1); // Stok komponen = 1 unit

        $targetDate = Carbon::now()->addDays(4)->format('Y-m-d');
        $startRent  = Carbon::parse($targetDate . ' 08:00:00');
        $endRent    = Carbon::parse($targetDate . ' 23:59:59');

        $expiredBundleOrderId = $this->createOrder([
            'status'             => 'Menunggu Pembayaran',
            'payment_expired_at' => Carbon::now()->subMinutes(2),
            'start_rent'         => $startRent,
            'end_rent'           => $endRent,
            'tanggal_mulai'      => $startRent->toDateString(),
            'tanggal_selesai'    => $endRent->toDateString(),
        ]);

        $bundleOrderItemId = DB::table('order_items')->insertGetId([
            'order_id'             => $expiredBundleOrderId,
            'product_id'           => null,
            'item_type'            => 'paket',
            'product_name'         => 'Bundle Fotografi Pro',
            'price'                => 450000.00,
            'quantity'             => 1,
            'subtotal_sewa_asli'   => 450000.00,
            'subtotal_sewa_markup' => 472500.00,
            'subtotal_sewa_bersih' => 472500.00,
            'created_at'           => Carbon::now(),
            'updated_at'           => Carbon::now(),
        ]);

        DB::table('order_item_components')->insert([
            'order_item_id'        => $bundleOrderItemId,
            'barang_id'            => $komponen->id,
            'quantity'             => 1, // Mengunci 1-satunya stok komponen
            'harga_komponen_asli'  => 120000.00,
            'subtotal_sewa_asli'   => 120000.00,
            'subtotal_sewa_markup' => 126000.00,
            'subtotal_sewa_bersih' => 126000.00,
            'status_komponen'      => 'disewa',
            'created_at'           => Carbon::now(),
            'updated_at'           => Carbon::now(),
        ]);

        // Sebelum command: komponen fully booked
        $availBefore = $komponen->getAvailabilityData(10, 1);
        $this->assertContains($targetDate, $availBefore['fully_booked_dates']);

        // Eksekusi pembersihan
        $this->artisan('rentify:release-expired-bundle-reservations')->assertSuccessful();

        // Sesudah command: komponen bebas
        $availAfter = $komponen->getAvailabilityData(10, 1);
        $this->assertNotContains($targetDate, $availAfter['fully_booked_dates']);
    }

    /**
     * Requirement 4.8:
     * Verifikasi bahwa command didaftarkan dalam penjadwalan Laravel (everyMinute).
     */
    public function test_command_is_registered_in_console_schedule(): void
    {
        $schedule = $this->app->make(Schedule::class);
        $events = new \Illuminate\Support\Collection($schedule->events());

        $hasRegisteredCommand = $events->contains(function ($event) {
            return str_contains($event->command ?? '', 'rentify:release-expired-bundle-reservations')
                && $event->expression === '* * * * *'; // everyMinute
        });

        $this->assertTrue(
            $hasRegisteredCommand,
            'Command rentify:release-expired-bundle-reservations harus terdaftar di scheduler setiap menit (* * * * *)'
        );
    }
}
