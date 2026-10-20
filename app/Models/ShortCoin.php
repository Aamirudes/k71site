<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShortCoin extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'delta',
        'balance',
        'reason',
        'ref_id',
        'meta',
    ];

    protected $casts = [
        'delta' => 'integer',
        'balance' => 'integer',
        'ref_id' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
