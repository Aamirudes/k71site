<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ThemeAuditLog extends Model
{
    protected $table = 'theme_audit_logs';

    protected $fillable = [
        'actor_user_id',
        'actor_role',
        'action',
        'vendor_id',
        'theme_id',
        'request_id',
        'entitlement_id',
        'before_state',
        'after_state',
        'notes',
        'ip_address',
    ];

    protected $casts = [
        'before_state' => 'array',
        'after_state' => 'array',
    ];

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function theme(): BelongsTo
    {
        return $this->belongsTo(StorefrontTheme::class, 'theme_id', 'theme_id');
    }
}
