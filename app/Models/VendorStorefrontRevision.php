<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VendorStorefrontRevision extends Model
{
    use HasFactory;

    protected $table = 'vendor_storefront_revisions';

    protected $fillable = [
        'vendor_id',
        'preset_key',
        'config_data',
        'summary',
        'created_by',
    ];

    protected $casts = [
        'config_data' => 'array',
    ];

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

