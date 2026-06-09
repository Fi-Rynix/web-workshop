<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LaporanKunjunganSales extends Model
{
    protected $table = 'laporan_kunjungan_sales';
    protected $primaryKey = 'idlaporan';
    public $timestamps = false;

    protected $fillable = [
        'iduser',
        'barcode_toko',
        'latitude_sales',
        'longitude_sales',
        'accuracy_sales',
        'jarak_aktual',
        'threshold_efektif',
        'status',
        'timestamp',
    ];

    protected $casts = [
        'latitude_sales' => 'float',
        'longitude_sales' => 'float',
        'accuracy_sales' => 'float',
        'jarak_aktual' => 'float',
        'threshold_efektif' => 'float',
        'timestamp' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'iduser', 'iduser');
    }

    public function toko(): BelongsTo
    {
        return $this->belongsTo(LokasiToko::class, 'barcode_toko', 'barcode');
    }
}
