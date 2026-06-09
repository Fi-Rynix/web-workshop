<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Antrian extends Model
{
    protected $table = 'antrian';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'nama',
        'status',
        'called_at',
        'created_at',
    ];

    protected $casts = [
        'called_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    /**
     * Scope: hanya antrian yang masih waiting
     */
    public function scopeWaiting($query)
    {
        return $query->where('status', 'waiting');
    }

    /**
     * Scope: antrian yang dipanggil
     */
    public function scopeCalled($query)
    {
        return $query->where('status', 'called');
    }

    /**
     * Scope: antrian yang terlambat
     */
    public function scopeLate($query)
    {
        return $query->where('status', 'late');
    }

    /**
     * Scope: antrian yang sudah selesai
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }
}
