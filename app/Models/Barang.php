<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Barang extends Model
{
    protected $table = 'barang';
    protected $primaryKey = 'idbarang';
    protected $fillable = ['nama_barang', 'harga', 'timestamp'];

        public function detailPenjualan()
        {
            return $this->hasMany(DetailPenjualan::class, 'idbarang', 'idbarang');
        }
}
