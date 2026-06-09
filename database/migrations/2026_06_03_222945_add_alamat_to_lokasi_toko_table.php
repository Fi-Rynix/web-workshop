<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lokasi_toko', function (Blueprint $table) {
            $table->string('alamat', 255)->nullable()->after('nama_toko');
        });
    }

    public function down(): void
    {
        Schema::table('lokasi_toko', function (Blueprint $table) {
            $table->dropColumn('alamat');
        });
    }
};
