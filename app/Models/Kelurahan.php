<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Models\Kecamatan;

class Kelurahan extends Model
{
    protected $table = 'kelurahan';
    protected $primaryKey = 'idkelurahan';
    protected $fillable = ['nama_kelurahan', 'idkecamatan'];

    public $timestamps = false;

    public function kecamatan()
    {
        return $this->belongsTo(Kecamatan::class, 'idkecamatan', 'idkecamatan');
    }
}
