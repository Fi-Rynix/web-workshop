<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('laporan_kunjungan_sales', function (Blueprint $table) {
            $table->id('idlaporan');
            $table->integer('iduser');                // sales yang scan
            $table->string('barcode_toko', 8)->charset('utf8mb4')->collation('utf8mb4_0900_ai_ci');                // FK ke lokasi_toko
            $table->double('latitude_sales');
            $table->double('longitude_sales');
            $table->double('accuracy_sales');
            $table->double('jarak_aktual');                   // meter
            $table->double('threshold_efektif');              // meter
            $table->enum('status', ['DITERIMA', 'DITOLAK']);
            $table->timestamp('timestamp')->useCurrent();

            $table->index('iduser');
            $table->index('barcode_toko');
        });

        // Set charset/collation seluruh tabel agar match dengan parent table
        \Illuminate\Support\Facades\DB::statement('ALTER TABLE laporan_kunjungan_sales CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci');

        // Tambah FK setelah collation disesuaikan
        \Illuminate\Support\Facades\DB::statement('ALTER TABLE laporan_kunjungan_sales ADD CONSTRAINT fk_laporan_sales_user FOREIGN KEY (iduser) REFERENCES user(iduser) ON DELETE CASCADE');
        \Illuminate\Support\Facades\DB::statement('ALTER TABLE laporan_kunjungan_sales ADD CONSTRAINT fk_laporan_sales_toko FOREIGN KEY (barcode_toko) REFERENCES lokasi_toko(barcode) ON DELETE CASCADE');
    }

    public function down(): void
    {
        Schema::dropIfExists('laporan_kunjungan_sales');
    }
};
