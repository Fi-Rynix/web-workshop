<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Models\Kota;
use App\Models\Kelurahan;

class Kecamatan extends Model
{
    protected $table = 'kecamatan';
    protected $primaryKey = 'idkecamatan';
    protected $fillable = ['nama_kecamatan', 'idkota'];

    public $timestamps = false;

    public function kota()
    {
        return $this->belongsTo(Kota::class, 'idkota', 'idkota');
    }

    public function kelurahan()
    {
        return $this->hasMany(Kelurahan::class, 'idkecamatan', 'idkecamatan');
    }
}
