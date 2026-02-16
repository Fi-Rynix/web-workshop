<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kategori extends Model
{
    protected $table = 'kategori';
    protected $primaryKey = 'id_kategori';
    protected $fillable = ['nama_kategori'];

    public $timestamps = false;

    public function buku()
    {
        return $this->hasMany(Buku::class, 'id_kategori', 'id_kategori');
    }
}
