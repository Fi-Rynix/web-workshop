<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $table = 'user';
    protected $primaryKey = 'iduser';
    public $timestamps = false;

    protected $fillable = ['nama', 'email', 'password', 'google_id', 'otp', 'otp_expire_at', 'status_verif', 'idrole'];

    public function pesanan()
    {
        return $this->hasMany(Pesanan::class, 'iduser', 'iduser');
    }

    /**
     * Relasi: User memiliki satu Vendor (jika role = vendor)
     */
    public function vendor()
    {
        return $this->hasOne(Vendor::class, 'iduser', 'iduser');
    }
}