<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Penjualan extends Model
{
    protected $table = 'penjualan';
    protected $primaryKey = 'idpenjualan';
    protected $fillable = ['total', 'waktu'];
    public $timestamps = false;

    public function detailPenjualan()
    {
        return $this->hasMany(DetailPenjualan::class, 'idpenjualan', 'idpenjualan');
    }
}
