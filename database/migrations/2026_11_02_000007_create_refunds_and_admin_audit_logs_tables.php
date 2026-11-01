<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Tabel refunds untuk refund presisi dan jejak audit finansial
        Schema::create('refunds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('restrict');
            $table->foreignId('penyewaan_id')->nullable()->constrained('orders')->onDelete('restrict');
            $table->foreignId('user_id')->constrained('users')->onDelete('restrict');
            $table->decimal('nominal_refund', 12, 2);
            $table->enum('tipe_refund', ['pembatalan_total', 'pembatalan_parsial', 'pengembalian_deposit']);
            $table->enum('metode_refund', ['manual_transfer'])->default('manual_transfer');
            $table->string('nama_bank', 100)->nullable();
            $table->string('nomor_rekening', 50)->nullable();
            $table->string('atas_nama', 150)->nullable();
            $table->enum('status', [
                'MENUNGGU_DATA_REKENING',
                'MENUNGGU_TRANSFER',
                'DITRANSFER',
                'DITOLAK'
            ])->default('MENUNGGU_DATA_REKENING');
            $table->string('bukti_transfer_refund', 255)->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->dateTime('processed_at')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();
        });

        // 2. Tabel admin_audit_logs untuk rekaman audit aksi administratif
        Schema::create('admin_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->constrained('users')->onDelete('cascade');
            $table->string('action', 100);
            $table->string('auditable_type', 100);
            $table->unsignedBigInteger('auditable_id');
            $table->json('payload_before')->nullable();
            $table->json('payload_after')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_audit_logs');
        Schema::dropIfExists('refunds');
    }
};
