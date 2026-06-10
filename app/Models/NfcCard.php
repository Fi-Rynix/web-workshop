<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NfcCard extends Model
{
    protected $table = 'nfc_cards';
    protected $primaryKey = 'idnfc';
    public $timestamps = false;

    protected $fillable = [
        'card_uid',
        'student_name',
        'student_nim',
        'is_active',
        'registered_at',
        'updated_at',
    ];

    protected $casts = [
        'is_active' => 'integer',
        'registered_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class, 'nfc_card_id', 'idnfc');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }
}