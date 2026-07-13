<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('vendor_earning', 15, 2)->default(0)->after('total_price');
            $table->decimal('rentify_fee', 15, 2)->default(0)->after('vendor_earning');
            $table->dateTime('completed_at')->nullable()->after('rentify_fee');
        });
    }
    public function down(): void {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['vendor_earning', 'rentify_fee', 'completed_at']);
        });
    }
};