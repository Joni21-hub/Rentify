<?php

namespace Tests\Feature;

use App\Models\Barang;
use App\Models\Kategori;
use App\Models\Pembayaran;
use App\Models\Penyewaan;
use App\Models\User;
use App\Services\StorageService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CustomerRentalTransactionTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        config(['filesystems.default' => 'public']);
    }

    public function test_customer_rental_order_creation_status_lifecycle_and_payment_proof(): void
    {
        // 1. Setup Customer & Vendor
        $customer = User::create([
            'name' => 'Customer Transaksi',
            'email' => 'cust_' . uniqid() . '@example.com',
            'password' => bcrypt('password123'),
            'role' => 'customer',
            'is_active' => 1,
        ]);
        $customer->markEmailAsVerified();

        $vendor = User::create([
            'name' => 'Vendor Transaksi',
            'email' => 'vendor_trx_' . uniqid() . '@example.com',
            'password' => bcrypt('password123'),
            'role' => 'vendor',
            'vendor_name' => 'Toko Rental Jaya',
            'is_active' => 1,
        ]);
        $vendor->markEmailAsVerified();

        $kategori = Kategori::create([
            'nama' => 'Elektronik Audio',
            'slug' => 'elektronik-audio-' . uniqid(),
            'is_active' => 1,
        ]);

        $barang = Barang::create([
            'vendor_id' => $vendor->id,
            'kategori_id' => $kategori->id,
            'nama' => 'Speaker Portable JBL',
            'slug' => 'speaker-portable-jbl-' . uniqid(),
            'deskripsi' => 'Speaker bluetooth jernih',
            'harga_sewa_harian' => 50000,
            'deposit' => 50000,
            'denda_per_hari' => 20000,
            'kondisi' => 'Baik',
            'stok_total' => 3,
            'status' => 'tersedia',
            'is_approved' => 1,
            'cover_photo' => 'barang/jbl.jpg',
            'is_delivery_supported' => 1,
            'alamat' => 'Jl. Sudirman No 10',
            'latitude' => '-6.2000',
            'longitude' => '106.8166',
        ]);

        // 2. Simulasikan Pembuatan Pesanan Sewa oleh Customer pada tabel 'orders'
        $kodeBooking = Penyewaan::generateKodeBooking();
        $order = Penyewaan::create([
            'user_id' => $customer->id,
            'vendor_id' => $vendor->id,
            'customer_name' => $customer->name,
            'customer_whatsapp' => '081234567890',
            'kode_booking' => $kodeBooking,
            'payment_method' => 'manual_transfer',
            'metode_pengambilan' => 'ambil_sendiri',
            'alamat_pengiriman' => 'Ambil di toko vendor',
            'total_biaya' => 100000, // 2 hari sewa
            'total_deposit' => 50000,
            'total_denda' => 0,
            'status' => 'menunggu_pembayaran',
            'tanggal_mulai' => now()->addDay()->toDateString(),
            'tanggal_selesai' => now()->addDays(3)->toDateString(),
        ]);

        $this->assertNotNull($order);
        $this->assertEquals('menunggu_pembayaran', $order->status);
        $this->assertEquals($customer->id, $order->user_id);

        // Verifikasi keberadaan di tabel orders
        $orderFromDb = DB::table('orders')->where('kode_booking', $kodeBooking)->first();
        $this->assertNotNull($orderFromDb);
        $this->assertEquals(100000, (float)$orderFromDb->total_biaya);

        // 3. Simulasikan Pengunggahan Bukti Pembayaran oleh Customer
        $fakeProof = UploadedFile::fake()->image('transfer_receipt.jpg', 600, 800);
        $proofPath = StorageService::storePublicFile($fakeProof, 'pembayaran');

        $pembayaran = Pembayaran::create([
            'order_id' => $order->id,
            'metode' => 'manual_transfer',
            'jumlah' => 100000,
            'bukti_pembayaran' => $proofPath,
            'status' => 'menunggu_konfirmasi',
            'paid_at' => now(),
        ]);

        $this->assertNotNull($pembayaran);
        $this->assertEquals($proofPath, $pembayaran->bukti_pembayaran);
        $this->assertFileExists(public_path('storage/' . $proofPath));
        $this->assertEquals(asset('storage/' . $proofPath), $pembayaran->bukti_pembayaran_url);

        // 4. Simulasikan Transisi Status Pesanan oleh Vendor (Konfirmasi Pembayaran -> Diproses -> Disewa -> Selesai)
        $order->status = 'dibayar';
        $order->paid_at = now();
        $order->save();
        $this->assertEquals('dibayar', $order->fresh()->status);

        $order->status = 'diproses';
        $order->save();
        $this->assertEquals('diproses', $order->fresh()->status);

        $order->status = 'disewa';
        $order->save();
        $this->assertEquals('disewa', $order->fresh()->status);

        $order->status = 'selesai';
        $order->tanggal_kembali_aktual = now()->toDateString();
        $order->save();
        $this->assertEquals('selesai', $order->fresh()->status);

        // Cleanup bukti pembayaran
        StorageService::deletePublicFile($proofPath);
    }
}
