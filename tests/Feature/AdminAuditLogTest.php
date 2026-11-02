<?php

namespace Tests\Feature;

use App\Models\AdminAuditLog;
use App\Models\Pembayaran;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminAuditLogTest extends TestCase
{
    protected User $admin;
    protected User $vendor;
    protected User $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::firstOrCreate(
            ['email' => 'admin_audit@rentify.id'],
            ['name' => 'Admin Audit Test', 'password' => bcrypt('password'), 'role' => 'admin']
        );

        $this->vendor = User::firstOrCreate(
            ['email' => 'vendor_audit@rentify.id'],
            ['name' => 'Vendor Audit Test', 'password' => bcrypt('password'), 'role' => 'vendor']
        );

        $this->customer = User::firstOrCreate(
            ['email' => 'cust_audit@rentify.id'],
            ['name' => 'Customer Audit Test', 'password' => bcrypt('password'), 'role' => 'customer']
        );
    }

    /**
     * Test bahwa aksi refund pembayaran oleh Admin tercatat dalam admin_audit_logs
     */
    public function test_refund_action_creates_audit_log()
    {
        // 1. Buat order dummy
        $orderId = DB::table('orders')->insertGetId([
            'user_id'            => $this->customer->id,
            'kode_booking'       => 'TEST-REF-' . uniqid(),
            'customer_name'      => 'Customer Test',
            'customer_whatsapp'  => '081234567890',
            'metode_pengambilan' => 'ambil',
            'tanggal_mulai'      => now(),
            'tanggal_selesai'    => now()->addDays(1),
            'payment_method'     => 'QRIS',
            'total_biaya'        => 50000.0,
            'total_price'        => 50000.0,
            'status'             => 'Menunggu Konfirmasi',
            'created_at'         => now(),
            'updated_at'         => now(),
        ]);

        // 2. Buat pembayaran
        $pembayaranId = DB::table('pembayarans')->insertGetId([
            'order_id'   => $orderId,
            'metode'     => 'QRIS',
            'jumlah'     => 50000.0,
            'status'     => 'Lunas',
            'paid_at'    => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 3. Admin mengeksekusi refund
        $response = $this->actingAs($this->admin)
            ->patch(route('admin.pembayaran.refund', $pembayaranId));

        $response->assertSessionHas('success');

        // 4. Verifikasi admin_audit_logs mencatat aksi refund
        $log = AdminAuditLog::where('action', 'refund_pembayaran')
            ->where('auditable_id', $pembayaranId)
            ->latest('id')
            ->first();

        $this->assertNotNull($log, 'Audit log refund_pembayaran harus tercatat!');
        $this->assertEquals($this->admin->id, $log->admin_id);
        $this->assertEquals('Lunas', $log->payload_before['status_sebelum'] ?? null);
        $this->assertEquals('Refunded', $log->payload_after['status_sesudah'] ?? null);
        $this->assertEquals(50000.0, (float) ($log->payload_after['nominal_refund'] ?? 0));
        $this->assertNotNull($log->ip_address);
    }

    /**
     * Test bahwa persetujuan pencairan dana penarikan vendor tercatat dalam admin_audit_logs
     */
    public function test_approve_withdrawal_creates_audit_log()
    {
        // Siapkan saldo vendor
        DB::table('saldos')->updateOrInsert(
            ['vendor_id' => $this->vendor->id],
            [
                'saldo_aktif'   => 100000.0,
                'saldo_ditahan' => 50000.0,
                'updated_at'    => now(),
            ]
        );

        // Buat penarikan pending
        $withdrawalId = DB::table('withdrawals')->insertGetId([
            'vendor_id'         => $this->vendor->id,
            'nominal'           => 50000.0,
            'metode'            => 'Bank',
            'nama_bank_ewallet' => 'BCA',
            'nomor_rekening'    => '1234567890',
            'nama_pemilik'      => 'Vendor Test',
            'status'            => 'pending',
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);

        // Admin approve penarikan
        $response = $this->actingAs($this->admin)
            ->post(route('admin.penarikan.approve', $withdrawalId));

        $response->assertSessionHas('success');

        // Verifikasi audit log tercatat
        $log = AdminAuditLog::where('action', 'pencairan_penarikan_vendor')
            ->where('auditable_id', $withdrawalId)
            ->latest('id')
            ->first();

        $this->assertNotNull($log, 'Audit log pencairan_penarikan_vendor harus tercatat!');
        $this->assertEquals($this->admin->id, $log->admin_id);
        $this->assertEquals('pending', $log->payload_before['status_penarikan'] ?? null);
        $this->assertEquals('disetujui', $log->payload_after['status_penarikan'] ?? null);
        $this->assertEquals(50000.0, (float) ($log->payload_before['saldo_ditahan_sebelum'] ?? 0));
        $this->assertEquals(0.0, (float) ($log->payload_after['saldo_ditahan_sesudah'] ?? 0));
    }

    /**
     * Test bahwa penolakan penarikan vendor tercatat dalam admin_audit_logs dengan pemulihan saldo
     */
    public function test_reject_withdrawal_creates_audit_log()
    {
        // Siapkan saldo vendor
        DB::table('saldos')->updateOrInsert(
            ['vendor_id' => $this->vendor->id],
            [
                'saldo_aktif'   => 50000.0,
                'saldo_ditahan' => 50000.0,
                'updated_at'    => now(),
            ]
        );

        $withdrawalId = DB::table('withdrawals')->insertGetId([
            'vendor_id'         => $this->vendor->id,
            'nominal'           => 50000.0,
            'metode'            => 'Bank',
            'nama_bank_ewallet' => 'BCA',
            'nomor_rekening'    => '1234567890',
            'nama_pemilik'      => 'Vendor Test',
            'status'            => 'pending',
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);

        // Admin reject penarikan
        $response = $this->actingAs($this->admin)
            ->post(route('admin.penarikan.reject', $withdrawalId));

        $response->assertSessionHas('success');

        // Verifikasi audit log tercatat
        $log = AdminAuditLog::where('action', 'penolakan_penarikan_vendor')
            ->where('auditable_id', $withdrawalId)
            ->latest('id')
            ->first();

        $this->assertNotNull($log, 'Audit log penolakan_penarikan_vendor harus tercatat!');
        $this->assertEquals('pending', $log->payload_before['status_penarikan'] ?? null);
        $this->assertEquals('ditolak', $log->payload_after['status_penarikan'] ?? null);
        $this->assertEquals(100000.0, (float) ($log->payload_after['saldo_aktif_sesudah'] ?? 0));
    }
}
