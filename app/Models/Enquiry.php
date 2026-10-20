<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Enquiry extends Model
{
    use HasFactory;

    protected $fillable = [
        'enquiry_number',
        'user_id',
        'vendor_id',
        'category_id',
        'product_id',
        'title',
        'description',
        'quantity',
        'target_budget',
        'city',
        'pincode',
        'urgency',
        'buyer_name',
        'buyer_phone',
        'buyer_email',
        'status',
        'quotes_count',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function quotes(): HasMany
    {
        return $this->hasMany(Quote::class);
    }

    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    public function isQuoted(): bool
    {
        return $this->status === 'quoted';
    }

    public function isClosed(): bool
    {
        return in_array($this->status, ['closed', 'cancelled']);
    }

    public function getFormattedBudgetAttribute(): string
    {
        return $this->target_budget ? '₹' . number_format($this->target_budget / 100, 2) : 'Negotiable / Open';
    }

    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }
}
