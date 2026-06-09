<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LokasiToko extends Model
{
    protected $table = 'lokasi_toko';
    protected $primaryKey = 'barcode';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = ['barcode', 'nama_toko', 'alamat', 'latitude', 'longitude', 'accuracy'];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'accuracy' => 'float',
    ];
}
