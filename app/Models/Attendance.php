<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    protected $table = 'attendances';
    protected $primaryKey = 'idattendance';
    public $timestamps = false;

    protected $fillable = [
        'nfc_card_id',
        'scanned_at',
        'device_info',
        'location',
        'notes',
        'raw_data',
    ];

    protected $casts = [
        'scanned_at' => 'datetime',
    ];

    public function nfcCard(): BelongsTo
    {
        return $this->belongsTo(NfcCard::class, 'nfc_card_id', 'idnfc');
    }
}