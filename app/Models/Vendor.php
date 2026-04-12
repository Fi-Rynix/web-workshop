<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vendor extends Model
{
    protected $table = 'vendor';
    protected $primaryKey = 'idvendor';
    public $timestamps = false;

    protected $fillable = ['nama_vendor', 'iduser'];

    /**
     * Relasi: Vendor memiliki banyak Menu
     */
    public function menus(): HasMany
    {
        return $this->hasMany(Menu::class, 'idvendor', 'idvendor');
    }

    /**
     * Relasi: Vendor dimiliki oleh satu User
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'iduser', 'iduser');
    }
}
