<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'user_id',
        'order_id',
        'vendor_id',
        'review_type',
        'service_order_id',
        'project_contract_id',
        'used_transaction_id',
        'reviewed_user_id',
        'rating',
        'title',
        'body',
        'images',
        'is_verified_purchase',
        'is_approved',
        'vendor_reply',
        'vendor_replied_at',
    ];

    protected $casts = [
        'rating' => 'integer',
        'images' => 'array',
        'is_verified_purchase' => 'boolean',
        'is_approved' => 'boolean',
        'vendor_replied_at' => 'datetime',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class)->withoutGlobalScopes();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function serviceOrder(): BelongsTo
    {
        return $this->belongsTo(ServiceOrder::class, 'service_order_id');
    }

    public function projectContract(): BelongsTo
    {
        return $this->belongsTo(ProjectContract::class, 'project_contract_id');
    }

    public function usedTransaction(): BelongsTo
    {
        return $this->belongsTo(UsedTransaction::class, 'used_transaction_id');
    }

    public function reviewedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_user_id');
    }

    public function getCommentAttribute(): ?string
    {
        return $this->body;
    }

    public function setCommentAttribute($value): void
    {
        $this->attributes['body'] = $value;
    }

    public function reports(): MorphMany
    {
        return $this->morphMany(Report::class, 'reportable');
    }

    public function hasVendorReply(): bool
    {
        return !empty($this->vendor_reply);
    }

    public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
    }
}
