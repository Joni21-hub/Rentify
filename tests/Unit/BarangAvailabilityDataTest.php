<?php

namespace Tests\Unit;

use App\Models\Barang;
use App\Models\Cabang;
use App\Models\DetailPenyewaan;
use App\Models\OrderItemComponent;
use App\Models\PaketSewa;
use App\Models\Penyewaan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class BarangAvailabilityDataTest extends TestCase
{
    protected User $vendor;
    protected User $customer;
    protected Cabang $cabang;

    protected function setUp(): void
    {
        parent::setUp();

        $vId = DB::table('users')->insertGetId([
            'name'              => 'Vendor Avail Test',
            'email'             => 'vendor_avail_' . Str::random(8) . '@rentify.test',
            'password'          => Hash::make('password'),
            'role'              => 'vendor',
            'vendor_status'     => 'approved',
            'whatsapp'          => '081299992222',
            'whatsapp_vendor'   => '081299992222',
            'email_verified_at' => Carbon::now(),
            'created_at'        => Carbon::now(),
            'updated_at'        => Carbon::now(),
        ]);
        $this->vendor = User::find($vId);

        $cId = DB::table('users')->insertGetId([
            'name'              => 'Customer Avail Test',
            'email'             => 'cust_avail_' . Str::random(8) . '@rentify.test',
            'password'          => Hash::make('password'),
            'role'              => 'customer',
            'email_verified_at' => Carbon::now(),
            'created_at'        => Carbon::now(),
            'updated_at'        => Carbon::now(),
        ]);
        $this->customer = User::find($cId);

        $cabangId = DB::table('cabangs')->insertGetId([
            'vendor_id'      => $this->vendor->id,
            'nama_cabang'    => 'Cabang Avail Test',
            'alamat_lengkap' => 'Jl. Ketersediaan Multi Unit No. 10',
            'created_at'     => Carbon::now(),
            'updated_at'     => Carbon::now(),
        ]);
        $this->cabang = Cabang::find($cabangId);
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
            'customer_whatsapp'  => '081299993333',
            'kode_booking'       => 'RNT' . strtoupper(Str::random(8)),
            'metode_pengambilan' => 'Ambil Sendiri',
            'payment_method'     => 'QRIS',
            'status'             => 'Menunggu Pembayaran',
            'tanggal_mulai'      => Carbon::parse($start)->toDateString(),
            'tanggal_selesai'    => Carbon::parse($end)->toDateString(),
            'start_rent'         => Carbon::parse($start),
            'end_rent'           => Carbon::parse($end),
            'total_biaya'        => 150000.00,
            'created_at'         => $now,
            'updated_at'         => $now,
        ], $attributes));
    }

    protected function createBarang(int $stokTotal = 5): Barang
    {
        $id = DB::table('barang')->insertGetId([
            'vendor_id'             => $this->vendor->id,
            'cabang_id'             => $this->cabang->id,
            'nama'                  => 'Kamera DSLR Pro ' . Str::random(5),
            'slug'                  => 'kamera-dslr-pro-' . Str::random(8),
            'deskripsi'             => 'Kamera profesional multi-unit',
            'harga_sewa_harian'     => 150000.00,
            'deposit'               => 500000.00,
            'denda_per_hari'        => 50000.00,
            'kondisi'               => 'sangat_baik',
            'stok_total'            => $stokTotal,
            'berat_gram'            => 1200,
            'status'                => 'tersedia',
            'is_approved'           => 1,
            'is_delivery_supported' => 1,
            'created_at'            => Carbon::now(),
            'updated_at'            => Carbon::now(),
        ]);

        return Barang::findOrFail($id);
    }

    /**
     * Requirement 2.4, 4.2:
     * Barang tanpa pesanan aktif harus tersedia penuh untuk semua kuantitas <= stok_total.
     */
    public function test_default_availability_with_no_bookings_returns_empty_booked_slots(): void
    {
        $barang = $this->createBarang(5);

        $data = $barang->getAvailabilityData(30, 1);

        $this->assertIsArray($data);
        $this->assertArrayHasKey('fully_booked_dates', $data);
        $this->assertArrayHasKey('booked_slots', $data);
        $this->assertArrayHasKey('booked_info', $data);
        $this->assertEmpty($data['fully_booked_dates'], 'Tidak boleh ada tanggal fully booked bila belum ada pesanan');
        $this->assertEmpty($data['booked_slots'], 'Tidak boleh ada slot jam booked bila belum ada pesanan');
    }

    /**
     * Requirement 2.4, 4.2, 4.3:
     * Slot waktu harus ditandai tidak tersedia (booked) jika usedQty + requiredQty > stokTotal.
     */
    public function test_time_slots_flagged_unavailable_when_used_qty_plus_required_qty_exceeds_stok_total(): void
    {
        $barang = $this->createBarang(3); // Total stock = 3

        $targetDate = Carbon::now()->addDays(2)->format('Y-m-d');
        $startRent = Carbon::parse($targetDate . ' 08:00:00');
        $endRent   = Carbon::parse($targetDate . ' 23:59:59');

        // Buat pesanan aktif langsung yang menyewa 2 unit (usedQty = 2)
        $orderId = $this->createOrder([
            'status'             => 'Menunggu Pembayaran',
            'payment_expired_at' => Carbon::now()->addMinutes(15),
            'start_rent'         => $startRent,
            'end_rent'           => $endRent,
            'tanggal_mulai'      => $startRent->toDateString(),
            'tanggal_selesai'    => $endRent->toDateString(),
            'total_biaya'        => 300000.00,
        ]);

        DB::table('order_items')->insert([
            'order_id'     => $orderId,
            'product_id'   => $barang->id,
            'product_name' => $barang->nama,
            'price'        => 150000.00,
            'quantity'     => 2, // 2 unit dibooking
            'item_type'    => 'barang',
            'created_at'   => Carbon::now(),
            'updated_at'   => Carbon::now(),
        ]);

        // Skenario 1: Sewa 1 unit (usedQty 2 + requiredQty 1 = 3 <= stokTotal 3) -> MASIH TERSEDIA
        $availFor1 = $barang->getAvailabilityData(10, 1);
        $this->assertNotContains(
            $targetDate,
            $availFor1['fully_booked_dates'],
            'Target date seharusnya masih tersedia jika usedQty + requiredQty <= stokTotal'
        );
        $this->assertArrayNotHasKey(
            $targetDate,
            $availFor1['booked_slots'],
            'Tidak boleh ada booked slots untuk requiredQty = 1 saat kapasitas masih mencukupi'
        );

        // Skenario 2: Sewa 2 unit (usedQty 2 + requiredQty 2 = 4 > stokTotal 3) -> TIDAK TERSEDIA (BOOKED)
        $availFor2 = $barang->getAvailabilityData(10, 2);
        $this->assertContains(
            $targetDate,
            $availFor2['fully_booked_dates'],
            'Target date harus fully booked jika usedQty + requiredQty > stokTotal'
        );
        $this->assertArrayHasKey($targetDate, $availFor2['booked_slots']);
        $this->assertCount(
            count(Barang::OPERATIONAL_HOURS),
            $availFor2['booked_slots'][$targetDate],
            'Seluruh slot jam operasional harus ditandai penuh untuk requiredQty = 2'
        );
    }

    /**
     * Requirement 4.2, 4.3, 4.4:
     * Kuantitas yang dibooking dari pesanan langsung (order_items) dan pesanan bundle
     * (order_item_components) harus diakumulasikan secara tepat.
     */
    public function test_aggregates_direct_and_bundle_component_bookings_correctly(): void
    {
        $barang = $this->createBarang(5); // Total stock = 5

        $targetDate = Carbon::now()->addDays(3)->format('Y-m-d');
        $startRent = Carbon::parse($targetDate . ' 08:00:00');
        $endRent   = Carbon::parse($targetDate . ' 23:59:59');

        // 1. Pesanan Langsung menyewa 2 unit
        $directOrderId = $this->createOrder([
            'status'             => 'Disetujui',
            'start_rent'         => $startRent,
            'end_rent'           => $endRent,
            'tanggal_mulai'      => $startRent->toDateString(),
            'tanggal_selesai'    => $endRent->toDateString(),
            'total_biaya'        => 300000.00,
        ]);

        DB::table('order_items')->insert([
            'order_id'     => $directOrderId,
            'product_id'   => $barang->id,
            'product_name' => $barang->nama,
            'price'        => 150000.00,
            'quantity'     => 2, // Direct 2 units
            'item_type'    => 'barang',
            'created_at'   => Carbon::now(),
            'updated_at'   => Carbon::now(),
        ]);

        // 2. Pesanan Bundle menyewa 2 unit dari barang ini melalui order_item_components
        $bundleOrderId = $this->createOrder([
            'status'             => 'Sedang Disewa',
            'start_rent'         => $startRent,
            'end_rent'           => $endRent,
            'tanggal_mulai'      => $startRent->toDateString(),
            'tanggal_selesai'    => $endRent->toDateString(),
            'total_biaya'        => 500000.00,
        ]);

        $paketSewaId = DB::table('paket_sewa')->insertGetId([
            'vendor_id'                 => $this->vendor->id,
            'cabang_id'                 => $this->cabang->id,
            'nama'                      => 'Paket Fotografi Event',
            'slug'                      => 'paket-fotografi-' . Str::random(8),
            'deskripsi'                 => 'Paket sewa lengkap',
            'harga_sewa_harian_paket'   => 500000.00,
            'status'                    => 'tersedia',
            'is_delivery_supported'     => 1,
            'created_at'                => Carbon::now(),
            'updated_at'                => Carbon::now(),
        ]);

        $bundleOrderItemId = DB::table('order_items')->insertGetId([
            'order_id'             => $bundleOrderId,
            'product_id'           => null,
            'paket_sewa_id'        => $paketSewaId,
            'item_type'            => 'paket',
            'product_name'         => 'Paket Fotografi Event',
            'price'                => 500000.00,
            'quantity'             => 1,
            'subtotal_sewa_asli'   => 500000.00,
            'subtotal_sewa_markup' => 525000.00,
            'subtotal_sewa_bersih' => 525000.00,
            'created_at'           => Carbon::now(),
            'updated_at'           => Carbon::now(),
        ]);

        DB::table('order_item_components')->insert([
            'order_item_id'        => $bundleOrderItemId,
            'barang_id'            => $barang->id,
            'quantity'             => 2, // Bundle uses 2 units
            'harga_komponen_asli'  => 150000.00,
            'subtotal_sewa_asli'   => 300000.00,
            'subtotal_sewa_markup' => 315000.00,
            'subtotal_sewa_bersih' => 315000.00,
            'status_komponen'      => 'disewa',
            'created_at'           => Carbon::now(),
            'updated_at'           => Carbon::now(),
        ]);

        // Total usedQty = 2 (direct) + 2 (bundle) = 4 units used.
        // Remaining capacity = 5 - 4 = 1 unit.

        // Minta 1 unit: usedQty (4) + requiredQty (1) = 5 <= 5 -> Tersedia!
        $availReq1 = $barang->getAvailabilityData(10, 1);
        $this->assertNotContains(
            $targetDate,
            $availReq1['fully_booked_dates'],
            'Barang harus tersedia jika sisa 1 unit dan customer meminta 1 unit'
        );
        $this->assertEquals(4, $availReq1['booked_info'][$targetDate]['used']);
        $this->assertEquals(1, $availReq1['booked_info'][$targetDate]['remaining']);

        // Minta 2 unit: usedQty (4) + requiredQty (2) = 6 > 5 -> Tidak tersedia (Fully Booked)!
        $availReq2 = $barang->getAvailabilityData(10, 2);
        $this->assertContains(
            $targetDate,
            $availReq2['fully_booked_dates'],
            'Barang harus fully booked jika sisa 1 unit dan customer meminta 2 unit'
        );
    }

    /**
     * Requirement 4.3, 4.4:
     * Status non-aktif ('Selesai', 'Batal', 'Dibatalkan', 'Ditolak', 'CANCELLED', 'EXPIRED', 'Menunggu Ulasan')
     * harus dikeluarkan secara ketat dari kalkulasi inventory hold.
     */
    public function test_non_active_order_statuses_are_strictly_excluded_from_inventory_hold(): void
    {
        $barang = $this->createBarang(2); // Total stock = 2

        $targetDate = Carbon::now()->addDays(4)->format('Y-m-d');
        $startRent = Carbon::parse($targetDate . ' 08:00:00');
        $endRent   = Carbon::parse($targetDate . ' 23:59:59');

        $inactiveStatuses = [
            'Selesai',
            'Batal',
            'Dibatalkan',
            'Ditolak',
            'CANCELLED',
            'EXPIRED',
            'Menunggu Ulasan',
        ];

        foreach ($inactiveStatuses as $status) {
            $orderId = $this->createOrder([
                'status'          => $status,
                'start_rent'      => $startRent,
                'end_rent'        => $endRent,
                'tanggal_mulai'   => $startRent->toDateString(),
                'tanggal_selesai' => $endRent->toDateString(),
                'total_biaya'     => 300000.00,
            ]);

            DB::table('order_items')->insert([
                'order_id'     => $orderId,
                'product_id'   => $barang->id,
                'product_name' => $barang->nama,
                'price'        => 150000.00,
                'quantity'     => 2, // Menyewa seluruh stok (2 unit)
                'item_type'    => 'barang',
                'created_at'   => Carbon::now(),
                'updated_at'   => Carbon::now(),
            ]);
        }

        // Karena seluruh 7 pesanan berstatus non-aktif, stok harus 100% bebas
        $avail = $barang->getAvailabilityData(10, 1);
        $this->assertNotContains(
            $targetDate,
            $avail['fully_booked_dates'],
            'Status non-aktif tidak boleh menahan stok kalender'
        );
        $this->assertArrayNotHasKey(
            $targetDate,
            $avail['booked_slots'],
            'Slot tidak boleh berstatus booked jika seluruh pesanan non-aktif'
        );
    }

    /**
     * Requirement 4.4, 4.9:
     * Komponen bundle dengan status_komponen = 'dikembalikan' harus melepaskan stok fisik.
     */
    public function test_returned_bundle_component_status_releases_inventory_hold(): void
    {
        $barang = $this->createBarang(2); // Total stock = 2

        $targetDate = Carbon::now()->addDays(5)->format('Y-m-d');
        $startRent = Carbon::parse($targetDate . ' 08:00:00');
        $endRent   = Carbon::parse($targetDate . ' 23:59:59');

        $orderId = $this->createOrder([
            'status'          => 'Sedang Disewa',
            'start_rent'      => $startRent,
            'end_rent'        => $endRent,
            'tanggal_mulai'   => $startRent->toDateString(),
            'tanggal_selesai' => $endRent->toDateString(),
            'total_biaya'     => 300000.00,
        ]);

        $orderItemId = DB::table('order_items')->insertGetId([
            'order_id'             => $orderId,
            'product_id'           => null,
            'item_type'            => 'paket',
            'product_name'         => 'Paket Kamera Lensa',
            'price'                => 300000.00,
            'quantity'             => 1,
            'subtotal_sewa_asli'   => 300000.00,
            'subtotal_sewa_markup' => 315000.00,
            'subtotal_sewa_bersih' => 315000.00,
            'created_at'           => Carbon::now(),
            'updated_at'           => Carbon::now(),
        ]);

        // Komponen ditandai 'dikembalikan'
        DB::table('order_item_components')->insert([
            'order_item_id'        => $orderItemId,
            'barang_id'            => $barang->id,
            'quantity'             => 2,
            'harga_komponen_asli'  => 150000.00,
            'subtotal_sewa_asli'   => 300000.00,
            'subtotal_sewa_markup' => 315000.00,
            'subtotal_sewa_bersih' => 315000.00,
            'status_komponen'      => 'dikembalikan', // Released!
            'returned_at'          => Carbon::now(),
            'created_at'           => Carbon::now(),
            'updated_at'           => Carbon::now(),
        ]);

        $avail = $barang->getAvailabilityData(10, 1);
        $this->assertNotContains(
            $targetDate,
            $avail['fully_booked_dates'],
            'Komponen berstatus dikembalikan tidak boleh menahan stok'
        );
    }

    /**
     * Requirement 2.4, 4.2:
     * Jika requiredQty melebihi total stok fisik (requiredQty > stok_total), seluruh tanggal horizon
     * harus ditandai fully booked.
     */
    public function test_required_qty_exceeding_total_stock_flags_entire_horizon_unavailable(): void
    {
        $barang = $this->createBarang(2); // Total stock = 2

        $horizon = 7;
        $avail = $barang->getAvailabilityData($horizon, 5); // Request 5 units

        $this->assertNotEmpty($avail['fully_booked_dates']);
        $this->assertGreaterThanOrEqual($horizon, count($avail['fully_booked_dates']));

        $today = Carbon::now()->format('Y-m-d');
        $this->assertContains($today, $avail['fully_booked_dates']);
    }

    /**
     * Requirement 2.4, 4.2:
     * Helper methods getFullyBookedDates, getBookedSlots, and getBookedDatesInfo
     * harus meneruskan parameter requiredQty dengan benar.
     */
    public function test_helper_methods_propagate_required_qty_parameter(): void
    {
        $barang = $this->createBarang(2);

        $targetDate = Carbon::now()->addDays(1)->format('Y-m-d');
        $startRent = Carbon::parse($targetDate . ' 08:00:00');
        $endRent   = Carbon::parse($targetDate . ' 23:59:59');

        $orderId = $this->createOrder([
            'status'             => 'Menunggu Pembayaran',
            'payment_expired_at' => Carbon::now()->addMinutes(15),
            'start_rent'         => $startRent,
            'end_rent'           => $endRent,
            'tanggal_mulai'      => $startRent->toDateString(),
            'tanggal_selesai'    => $endRent->toDateString(),
            'total_biaya'        => 150000.00,
        ]);

        DB::table('order_items')->insert([
            'order_id'     => $orderId,
            'product_id'   => $barang->id,
            'product_name' => $barang->nama,
            'price'        => 150000.00,
            'quantity'     => 1, // 1 unit used, 1 remaining
            'item_type'    => 'barang',
            'created_at'   => Carbon::now(),
            'updated_at'   => Carbon::now(),
        ]);

        // requiredQty = 1: 1 + 1 = 2 <= 2 -> Available
        $this->assertNotContains($targetDate, $barang->getFullyBookedDates(10, 1));

        // requiredQty = 2: 1 + 2 = 3 > 2 -> Fully Booked
        $this->assertContains($targetDate, $barang->getFullyBookedDates(10, 2));

        $bookedSlots = $barang->getBookedSlots(10, 2);
        $this->assertArrayHasKey($targetDate, $bookedSlots);

        $bookedInfo = $barang->getBookedDatesInfo(10, 2);
        $this->assertArrayHasKey($targetDate, $bookedInfo);
        $this->assertTrue($bookedInfo[$targetDate]['is_full']);
    }
}
