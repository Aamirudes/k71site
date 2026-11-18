<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VendorThemeDraft extends Model
{
    protected $table = 'vendor_theme_drafts';

    protected $fillable = [
        'vendor_id',
        'theme_id',
        'theme_version',
        'config_data',
    ];

    protected $casts = [
        'config_data' => 'array',
    ];

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function theme(): BelongsTo
    {
        return $this->belongsTo(StorefrontTheme::class, 'theme_id', 'theme_id');
    }
}
