<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServiceOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'buyer_id',
        'vendor_id',
        'product_id',
        'gig_package_id',
        'package_tier',
        'package_title',
        'amount',
        'payment_status',
        'status',
        'delivery_days',
        'revisions_allowed',
        'revisions_used',
        'due_at',
        'requirements',
        'requirements_files',
        'deliveries',
        'revision_notes',
        'dispute_reason',
        'dispute_status',
        'buyer_rating',
        'buyer_review',
        'delivered_at',
        'completed_at',
    ];

    protected $casts = [
        'requirements_files' => 'array',
        'deliveries' => 'array',
        'due_at' => 'datetime',
        'delivered_at' => 'datetime',
        'completed_at' => 'datetime',
        'amount' => 'integer',
        'delivery_days' => 'integer',
        'revisions_allowed' => 'integer',
        'revisions_used' => 'integer',
        'buyer_rating' => 'integer',
    ];

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id')->withoutGlobalScopes();
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(ProductGigPackage::class, 'gig_package_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ServiceOrderMessage::class)->oldest();
    }

    public function getFormattedAmountAttribute(): string
    {
        return '₹' . number_format($this->amount / 100, 2);
    }

    public function getTierAttribute(): string
    {
        return $this->package_tier ?? 'basic';
    }

    public function getTotalPriceAttribute(): float
    {
        return $this->amount / 100;
    }

    public function getRequirementsTextAttribute(): ?string
    {
        return $this->requirements;
    }

    public function getStatusBadgeAttribute(): array
    {
        return match ($this->status) {
            'in_progress' => [
                'label' => 'In Progress',
                'bg' => 'bg-purple-100',
                'text' => 'text-purple-800',
                'border' => 'border-purple-200',
                'dot' => 'bg-purple-500 animate-pulse',
            ],
            'delivered' => [
                'label' => 'Delivered — Review Needed',
                'bg' => 'bg-amber-100',
                'text' => 'text-amber-800',
                'border' => 'border-amber-200',
                'dot' => 'bg-amber-500',
            ],
            'revision_requested' => [
                'label' => 'Revision In Progress',
                'bg' => 'bg-orange-100',
                'text' => 'text-orange-800',
                'border' => 'border-orange-200',
                'dot' => 'bg-orange-500 animate-pulse',
            ],
            'completed' => [
                'label' => 'Order Completed',
                'bg' => 'bg-emerald-100',
                'text' => 'text-emerald-800',
                'border' => 'border-emerald-200',
                'dot' => 'bg-emerald-500',
            ],
            'disputed' => [
                'label' => 'Under Dispute / Mediation',
                'bg' => 'bg-rose-100',
                'text' => 'text-rose-800',
                'border' => 'border-rose-200',
                'dot' => 'bg-rose-500',
            ],
            'cancelled' => [
                'label' => 'Cancelled',
                'bg' => 'bg-slate-100',
                'text' => 'text-slate-700',
                'border' => 'border-slate-200',
                'dot' => 'bg-slate-400',
            ],
            default => [
                'label' => ucfirst(str_replace('_', ' ', $this->status)),
                'bg' => 'bg-slate-100',
                'text' => 'text-slate-800',
                'border' => 'border-slate-200',
                'dot' => 'bg-slate-500',
            ],
        };
    }
}
