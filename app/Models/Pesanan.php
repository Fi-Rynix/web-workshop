<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pesanan extends Model
{
    protected $table = 'pesanan';
    protected $primaryKey = 'idpesanan';
    public $timestamps = false;

    protected $fillable = ['order_id', 'nama', 'timestamp', 'total', 'metode_bayar', 'channel', 'status_bayar', 'customer_email', 'iduser'];

    protected $casts = [
        'timestamp' => 'datetime',
    ];


    public function detailPesanan(): HasMany
    {
        return $this->hasMany(DetailPesanan::class, 'idpesanan', 'idpesanan');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'iduser', 'iduser');
    }
}
