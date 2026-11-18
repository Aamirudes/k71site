<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VendorThemeEntitlement extends Model
{
    protected $table = 'vendor_theme_entitlements';

    public const STATUS_GRANTED = 'granted';
    public const STATUS_REVOKED = 'revoked';

    protected $fillable = [
        'vendor_id',
        'theme_id',
        'status',
        'request_id',
        'granted_by',
        'granted_at',
        'revoked_by',
        'revoked_at',
        'revocation_reason',
        'notes',
    ];

    protected $casts = [
        'granted_at' => 'datetime',
        'revoked_at' => 'datetime',
    ];

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function theme(): BelongsTo
    {
        return $this->belongsTo(StorefrontTheme::class, 'theme_id', 'theme_id');
    }

    public function request(): BelongsTo
    {
        return $this->belongsTo(VendorThemeRequest::class, 'request_id');
    }

    public function grantor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'granted_by');
    }

    public function revoker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'revoked_by');
    }

    public function isGranted(): bool
    {
        return $this->status === self::STATUS_GRANTED;
    }

    public function isRevoked(): bool
    {
        return $this->status === self::STATUS_REVOKED;
    }
}
