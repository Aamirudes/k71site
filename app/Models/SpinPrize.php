<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SpinPrize extends Model
{
    use HasFactory;

    protected $fillable = [
        'label',
        'color',
        'type',
        'value',
        'coupon_id',
        'probability',
        'is_active',
    ];

    protected $casts = [
        'probability' => 'integer',
        'is_active' => 'boolean',
    ];

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }
}
