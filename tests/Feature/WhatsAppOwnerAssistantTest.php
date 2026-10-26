<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\RentifyGuardianService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class WhatsAppOwnerAssistantTest extends TestCase
{
    use DatabaseTransactions;

    protected RentifyGuardianService $guardian;
    protected string $ownerPhone = '083183494835';
    protected int $orderId;
    protected User $vendor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->guardian = app(RentifyGuardianService::class);

        $this->vendor = User::create([
            'name'           => 'Vendor Joni Store',
            'email'          => 'vendor_wa_' . uniqid() . '@example.com',
            'password'       => bcrypt('secret'),
            'role'           => 'vendor',
            'vendor_name'    => 'Joni Audio Visual',
            'whatsapp'       => '082183394549',
            'is_active'      => 1,
        ]);

        DB::table('saldos')->updateOrInsert(
            ['vendor_id' => $this->vendor->id],
            ['saldo_aktif' => 150000, 'saldo_ditahan' => 0]
        );

        $this->orderId = DB::table('orders')->insertGetId([
            'user_id'            => $this->vendor->id,
            'vendor_id'          => $this->vendor->id,
            'kode_booking'       => 'RNT-TEST-58',
            'customer_name'      => 'Pelanggan Uji',
            'customer_whatsapp'  => '081299998888',
            'metode_pengambilan' => 'antar',
            'shipping_fee'       => 10000,
            'total_biaya'        => 115000,
            'total_price'        => 115000,
            'payment_method'     => 'QRIS',
            'status'             => 'Selesai',
            'tanggal_mulai'      => now(),
            'tanggal_selesai'    => now()->addDay(),
            'created_at'         => now(),
            'updated_at'         => now(),
        ]);
    }

    public function test_owner_audit_command(): void
    {
        $reply = $this->guardian->handleOwnerMessage('audit transaksi', $this->ownerPhone);

        $this->assertNotEmpty($reply);
        $this->assertStringContainsString('LAPORAN AUDIT TRANSAKSI RENTIFY', $reply);
        $this->assertStringContainsString('Hak Komisi Rentify (5%)', $reply);
        $this->assertStringContainsString('Rekap Saldo Vendor', $reply);
    }

    public function test_owner_financial_summary_command(): void
    {
        $reply = $this->guardian->handleOwnerMessage('cek keuangan dan omzet', $this->ownerPhone);

        $this->assertNotEmpty($reply);
        $this->assertStringContainsString('RINGKASAN KEUANGAN & OMZET RENTIFY', $reply);
        $this->assertStringContainsString('Pendapatan Bersih Rentify (5%)', $reply);
    }

    public function test_owner_latest_orders_command(): void
    {
        $reply = $this->guardian->handleOwnerMessage('cek order terbaru', $this->ownerPhone);

        $this->assertNotEmpty($reply);
        $this->assertStringContainsString('TRANSAKSI TERBARU RENTIFY', $reply);
    }

    public function test_owner_order_lookup_command(): void
    {
        $reply = $this->guardian->handleOwnerMessage("cek order #{$this->orderId}", $this->ownerPhone);

        $this->assertNotEmpty($reply);
        $this->assertStringContainsString("#INV-{$this->orderId}", $reply);
        $this->assertStringContainsString('Fee Rentify (5%)', $reply);
    }

    public function test_owner_vendors_overview_command(): void
    {
        $reply = $this->guardian->handleOwnerMessage('cek saldo vendor', $this->ownerPhone);

        $this->assertNotEmpty($reply);
        $this->assertStringContainsString('DAFTAR & SALDO TOKO VENDOR RENTIFY', $reply);
    }

    public function test_owner_business_rule_question(): void
    {
        $reply = $this->guardian->handleOwnerMessage('gimana rumus fee rentify dan alur COD?', $this->ownerPhone);

        $this->assertNotEmpty($reply);
        $this->assertStringContainsString('Fee Rentify (5%)', $reply);
        $this->assertStringContainsString('COD', $reply);
    }

    public function test_owner_help_and_greeting(): void
    {
        $reply = $this->guardian->handleOwnerMessage('halo kamu bisa apa', $this->ownerPhone);

        $this->assertNotEmpty($reply);
        $this->assertStringContainsString('BOS JONI', $reply);
        $this->assertStringContainsString('ASISTEN PRIBADI', $reply);
    }

    public function test_webhook_controller_routes_owner_to_personal_assistant(): void
    {
        $response = $this->postJson('/webhook/whatsapp', [
            'sender'  => $this->ownerPhone,
            'name'    => 'Bos Joni',
            'message' => 'audit',
        ]);

        $response->assertStatus(200);
        $this->assertTrue($response->json('status'));
        $this->assertStringContainsString('LAPORAN AUDIT TRANSAKSI RENTIFY', $response->json('reply'));
    }
}
