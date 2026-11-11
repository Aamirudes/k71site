<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VendorSubdomainHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'vendor_id',
        'slug',
        'is_active_redirect',
        'released_at',
        'cooldown_until',
    ];

    protected $casts = [
        'is_active_redirect' => 'boolean',
        'released_at' => 'datetime',
        'cooldown_until' => 'datetime',
    ];

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }
}

