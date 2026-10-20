<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShortShare extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'short_id',
        'platform',
        'user_id',
        'session_id',
        'created_at',
    ];

    protected $casts = [
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
