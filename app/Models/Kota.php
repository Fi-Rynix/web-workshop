<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Models\Provinsi;

class Kota extends Model
{
    protected $table = 'kota';
    protected $primaryKey = 'idkota';
    protected $fillable = ['nama_kota', 'idprovinsi'];

    public $timestamps = false;

    public function provinsi()
    {
        return $this->belongsTo(Provinsi::class, 'idprovinsi', 'idprovinsi');
    }

    public function kecamatan()
    {
        return $this->hasMany(Kecamatan::class, 'idkota', 'idkota');
    }
}
