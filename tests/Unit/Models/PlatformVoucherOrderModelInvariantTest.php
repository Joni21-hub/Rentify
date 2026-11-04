<?php

namespace Tests\Unit\Models;

use App\Models\CustomerVoucher;
use App\Models\OrderVoucherRedemption;
use App\Models\Penyewaan;
use App\Models\User;
use App\Models\Voucher;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PlatformVoucherOrderModelInvariantTest extends TestCase
{
    /**
     * Test Voucher model fillable and casts for TASK-256.
     */
    public function test_voucher_fillable_and_casts(): void
    {
        $voucher = new Voucher();

        // 1. Fillable attributes
        $fillable = $voucher->getFillable();
        $this->assertContains('min_quantity', $fillable);
        $this->assertContains('suspended_by_admin', $fillable);
        $this->assertContains('auto_exhausted', $fillable);

        // 2. Casts attributes
        $casts = $voucher->getCasts();
        $this->assertEquals('integer', $casts['min_quantity']);
        $this->assertEquals('boolean', $casts['suspended_by_admin']);
        $this->assertEquals('boolean', $casts['auto_exhausted']);

        // 3. Cast behavior in action
        $voucher->fill([
            'min_quantity'       => '3',
            'suspended_by_admin' => 1,
            'auto_exhausted'     => '0',
        ]);

        $this->assertSame(3, $voucher->min_quantity);
        $this->assertTrue($voucher->suspended_by_admin);
        $this->assertFalse($voucher->auto_exhausted);
    }

    /**
     * Test Voucher helper methods isPlatform and isBogo.
     */
    public function test_voucher_domain_helpers_is_platform_and_is_bogo(): void
    {
        // 1. isPlatform checks
        $platformVoucher1 = new Voucher([
            'funding_source' => 'platform',
            'tipe_voucher'   => 'toko',
        ]);
        $this->assertTrue($platformVoucher1->isPlatform());

        $platformVoucher2 = new Voucher([
            'funding_source' => 'vendor',
            'tipe_voucher'   => 'platform',
        ]);
        $this->assertTrue($platformVoucher2->isPlatform());

        $vendorVoucher = new Voucher([
            'funding_source' => 'vendor',
            'tipe_voucher'   => 'toko',
        ]);
        $this->assertFalse($vendorVoucher->isPlatform());

        // 2. isBogo checks
        $bogoVoucherByQty = new Voucher([
            'kode_voucher' => 'DISC50',
            'min_quantity' => 2,
        ]);
        $this->assertTrue($bogoVoucherByQty->isBogo());

        $bogoVoucherByCode = new Voucher([
            'kode_voucher' => 'BOGO2B01',
            'min_quantity' => 1,
        ]);
        $this->assertTrue($bogoVoucherByCode->isBogo());

        $normalVoucher = new Voucher([
            'kode_voucher' => 'REGULAR10',
            'min_quantity' => 1,
        ]);
        $this->assertFalse($normalVoucher->isBogo());
    }

    /**
     * Test Voucher scopePlatformActive filters.
     */
    public function test_voucher_scope_platform_active(): void
    {
        $uniq = uniqid();
        $today = Carbon::now()->toDateString();

        // 1. Eligible platform voucher
        $activeVoucher = Voucher::create([
            'kode_voucher'       => "PLTACT{$uniq}",
            'tipe_voucher'       => 'platform',
            'funding_source'     => 'platform',
            'tipe_diskon'        => 'nominal',
            'nilai_diskon'       => 10000,
            'minimal_belanja'    => 50000,
            'kuota_total'        => 100,
            'kuota_terpakai'     => 0,
            'tanggal_mulai'      => Carbon::now()->subDays(5)->toDateString(),
            'tanggal_selesai'    => Carbon::now()->addDays(5)->toDateString(),
            'is_active'          => true,
            'suspended_by_admin' => false,
            'auto_exhausted'     => false,
            'min_quantity'       => 1,
        ]);

        // 2. Suspended platform voucher
        $suspendedVoucher = Voucher::create([
            'kode_voucher'       => "PLTSUSP{$uniq}",
            'tipe_voucher'       => 'platform',
            'funding_source'     => 'platform',
            'tipe_diskon'        => 'nominal',
            'nilai_diskon'       => 10000,
            'minimal_belanja'    => 50000,
            'kuota_total'        => 100,
            'kuota_terpakai'     => 0,
            'tanggal_mulai'      => Carbon::now()->subDays(5)->toDateString(),
            'tanggal_selesai'    => Carbon::now()->addDays(5)->toDateString(),
            'is_active'          => true,
            'suspended_by_admin' => true,
            'auto_exhausted'     => false,
            'min_quantity'       => 1,
        ]);

        // 3. Auto-exhausted platform voucher
        $exhaustedVoucher = Voucher::create([
            'kode_voucher'       => "PLTEXH{$uniq}",
            'tipe_voucher'       => 'platform',
            'funding_source'     => 'platform',
            'tipe_diskon'        => 'nominal',
            'nilai_diskon'       => 10000,
            'minimal_belanja'    => 50000,
            'kuota_total'        => 100,
            'kuota_terpakai'     => 100,
            'tanggal_mulai'      => Carbon::now()->subDays(5)->toDateString(),
            'tanggal_selesai'    => Carbon::now()->addDays(5)->toDateString(),
            'is_active'          => true,
            'suspended_by_admin' => false,
            'auto_exhausted'     => true,
            'min_quantity'       => 1,
        ]);

        // 4. Expired platform voucher
        $expiredVoucher = Voucher::create([
            'kode_voucher'       => "PLTEXP{$uniq}",
            'tipe_voucher'       => 'platform',
            'funding_source'     => 'platform',
            'tipe_diskon'        => 'nominal',
            'nilai_diskon'       => 10000,
            'minimal_belanja'    => 50000,
            'kuota_total'        => 100,
            'kuota_terpakai'     => 0,
            'tanggal_mulai'      => Carbon::now()->subDays(10)->toDateString(),
            'tanggal_selesai'    => Carbon::now()->subDays(2)->toDateString(),
            'is_active'          => true,
            'suspended_by_admin' => false,
            'auto_exhausted'     => false,
            'min_quantity'       => 1,
        ]);

        try {
            $activeResults = Voucher::platformActive()->get();

            $this->assertTrue($activeResults->contains('id', $activeVoucher->id));
            $this->assertFalse($activeResults->contains('id', $suspendedVoucher->id));
            $this->assertFalse($activeResults->contains('id', $exhaustedVoucher->id));
            $this->assertFalse($activeResults->contains('id', $expiredVoucher->id));
        } finally {
            $activeVoucher->delete();
            $suspendedVoucher->delete();
            $exhaustedVoucher->delete();
            $expiredVoucher->delete();
        }
    }

    /**
     * Test Penyewaan model attributes and relationships for TASK-257.
     */
    public function test_penyewaan_attributes_and_relationships(): void
    {
        $penyewaan = new Penyewaan();

        // 1. Fillable checks
        $fillable = $penyewaan->getFillable();
        $this->assertContains('voucher_platform_id', $fillable);
        $this->assertContains('potongan_voucher_platform', $fillable);
        $this->assertContains('total_deposit', $fillable);

        // 2. Casts checks
        $casts = $penyewaan->getCasts();
        $this->assertEquals('decimal:2', $casts['potongan_voucher_platform']);
        $this->assertEquals('decimal:2', $casts['total_deposit']);

        // 3. Relationship definitions
        $this->assertTrue(method_exists($penyewaan, 'platformVoucher'));
        $this->assertTrue(method_exists($penyewaan, 'voucherRedemptions'));

        // 4. Persistence and relation invocation
        $customer = User::factory()->create();
        $voucher = Voucher::create([
            'kode_voucher'       => 'PLTREL' . uniqid(),
            'tipe_voucher'       => 'platform',
            'funding_source'     => 'platform',
            'tipe_diskon'        => 'nominal',
            'nilai_diskon'       => 15000,
            'minimal_belanja'    => 50000,
            'kuota_total'        => 50,
            'kuota_terpakai'     => 0,
            'tanggal_mulai'      => Carbon::now()->subDay(),
            'tanggal_selesai'    => Carbon::now()->addDays(10),
            'is_active'          => true,
            'suspended_by_admin' => false,
            'auto_exhausted'     => false,
            'min_quantity'       => 1,
        ]);

        $order = Penyewaan::create([
            'user_id'                   => $customer->id,
            'customer_name'             => $customer->name,
            'customer_whatsapp'         => '081234567890',
            'kode_booking'              => 'BK' . strtoupper(uniqid()),
            'payment_group_id'          => 'PG' . strtoupper(uniqid()),
            'payment_method'            => 'ONLINE',
            'metode_pengambilan'        => 'ambil',
            'tanggal_mulai'             => Carbon::now(),
            'tanggal_selesai'           => Carbon::now()->addDays(3),
            'total_biaya'               => 150000.00,
            'total_deposit'             => 50000.00,
            'voucher_platform_id'       => $voucher->id,
            'potongan_voucher_platform' => 15000.00,
            'status'                    => 'Menunggu Pembayaran',
        ]);

        $redemption = OrderVoucherRedemption::create([
            'payment_group_id' => $order->payment_group_id,
            'order_id'         => $order->id,
            'voucher_id'       => $voucher->id,
            'tipe_voucher'     => 'platform',
            'alokasi_diskon'   => 15000.00,
            'status'           => 'aktif',
        ]);

        try {
            $this->assertInstanceOf(Voucher::class, $order->platformVoucher);
            $this->assertEquals($voucher->id, $order->platformVoucher->id);

            $this->assertTrue($order->voucherRedemptions->contains('id', $redemption->id));
            $this->assertSame('50000.00', (string) $order->fresh()->total_deposit);
            $this->assertSame('15000.00', (string) $order->fresh()->potongan_voucher_platform);
        } finally {
            $redemption->delete();
            $order->delete();
            $voucher->delete();
            $customer->delete();
        }
    }

    /**
     * Test OrderVoucherRedemption and CustomerVoucher models for TASK-258.
     */
    public function test_order_voucher_redemption_and_customer_voucher(): void
    {
        $redemption = new OrderVoucherRedemption();

        // 1. Casts check on OrderVoucherRedemption
        $casts = $redemption->getCasts();
        $this->assertEquals('decimal:2', $casts['alokasi_diskon']);
        $this->assertEquals('string', $casts['status']);

        // 2. Relations on OrderVoucherRedemption
        $this->assertTrue(method_exists($redemption, 'order'));
        $this->assertTrue(method_exists($redemption, 'voucher'));
        $this->assertTrue(method_exists($redemption, 'customerVoucher'));

        // 3. CustomerVoucher relations check
        $cv = new CustomerVoucher();
        $this->assertTrue(method_exists($cv, 'user'));
        $this->assertTrue(method_exists($cv, 'voucher'));
        $this->assertTrue(method_exists($cv, 'redemptions'));

        // 4. Persistence and FK CASCADE / RESTRICT invariants
        $user = User::factory()->create();
        $voucher = Voucher::create([
            'kode_voucher'       => 'REDMPT' . uniqid(),
            'tipe_voucher'       => 'platform',
            'funding_source'     => 'platform',
            'tipe_diskon'        => 'nominal',
            'nilai_diskon'       => 20000,
            'minimal_belanja'    => 50000,
            'kuota_total'        => 50,
            'kuota_terpakai'     => 0,
            'tanggal_mulai'      => Carbon::now()->subDay(),
            'tanggal_selesai'    => Carbon::now()->addDays(10),
            'is_active'          => true,
            'suspended_by_admin' => false,
            'auto_exhausted'     => false,
            'min_quantity'       => 1,
        ]);

        $customerVoucher = CustomerVoucher::create([
            'user_id'    => $user->id,
            'voucher_id' => $voucher->id,
            'status'     => 'aktif',
            'claimed_at' => Carbon::now(),
        ]);

        $order = Penyewaan::create([
            'user_id'                   => $user->id,
            'customer_name'             => $user->name,
            'customer_whatsapp'         => '081234567890',
            'kode_booking'              => 'BK' . strtoupper(uniqid()),
            'payment_group_id'          => 'PG' . strtoupper(uniqid()),
            'payment_method'            => 'ONLINE',
            'metode_pengambilan'        => 'ambil',
            'tanggal_mulai'             => Carbon::now(),
            'tanggal_selesai'           => Carbon::now()->addDays(3),
            'total_biaya'               => 100000.00,
            'total_deposit'             => 0.00,
            'voucher_platform_id'       => $voucher->id,
            'potongan_voucher_platform' => 20000.00,
            'status'                    => 'Menunggu Pembayaran',
        ]);

        $persistedRedemption = OrderVoucherRedemption::create([
            'payment_group_id'    => $order->payment_group_id,
            'order_id'            => $order->id,
            'voucher_id'          => $voucher->id,
            'customer_voucher_id' => $customerVoucher->id,
            'tipe_voucher'        => 'platform',
            'alokasi_diskon'      => 20000.00,
            'status'              => 'aktif',
        ]);

        try {
            // Verify relationships resolve correctly
            $this->assertEquals($order->id, $persistedRedemption->order->id);
            $this->assertEquals($voucher->id, $persistedRedemption->voucher->id);
            $this->assertEquals($customerVoucher->id, $persistedRedemption->customerVoucher->id);
            $this->assertTrue($customerVoucher->redemptions->contains('id', $persistedRedemption->id));

            // Verify status cast to string
            $this->assertIsString($persistedRedemption->status);
            $this->assertEquals('aktif', $persistedRedemption->status);

            // Verify RESTRICT foreign key invariant: Deleting voucher directly MUST fail
            $restrictExceptionCaught = false;
            try {
                $voucher->delete();
            } catch (QueryException $e) {
                $restrictExceptionCaught = true;
            }
            $this->assertTrue($restrictExceptionCaught, 'Deleting a voucher with active redemptions must be restricted by foreign key');

            // Verify order deletion cascades to redemption
            $order->delete();
            $this->assertDatabaseMissing('order_voucher_redemptions', ['id' => $persistedRedemption->id]);

        } finally {
            OrderVoucherRedemption::where('voucher_id', $voucher->id)->delete();
            Penyewaan::where('id', $order->id)->delete();
            CustomerVoucher::where('id', $customerVoucher->id)->delete();
            Voucher::where('id', $voucher->id)->delete();
            User::where('id', $user->id)->delete();
        }
    }
}
