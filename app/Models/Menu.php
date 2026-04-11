<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Menu extends Model
{
    protected $table = 'menu';
    protected $primaryKey = 'idmenu';
    public $timestamps = false;

    protected $fillable = ['nama_menu', 'harga', 'path_gambar', 'idvendor'];


    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class, 'idvendor', 'idvendor');
    }


    public function detailPesanan(): HasMany
    {
        return $this->hasMany(DetailPesanan::class, 'idmenu', 'idmenu');
    }
}
