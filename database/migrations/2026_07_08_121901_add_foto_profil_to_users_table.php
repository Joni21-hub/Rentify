<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up()
{
    Schema::table('users', function (Blueprint $table) {
        $table->string('foto_profil')->nullable()->after('whatsapp_vendor');
    });
}
public function down()
{
    Schema::table('users', function (Blueprint $table) {
        $table->dropColumn('foto_profil');
    });
}
};
