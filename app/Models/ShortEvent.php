<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShortEvent extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'short_id',
        'event_type',
        'user_id',
        'session_id',
        'device',
        'browser',
        'country',
        'referrer',
        'ip_hash',
        'watch_pct',
        'created_at',
    ];

    protected $casts = [
        'watch_pct' => 'integer',
        'created_at' => 'datetime',
    ];

    public function short(): BelongsTo
    {
        return $this->belongsTo(Short::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
