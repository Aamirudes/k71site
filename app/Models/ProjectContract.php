<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProjectContract extends Model
{
    use HasFactory;

    protected $fillable = [
        'contract_number',
        'project_id',
        'proposal_id',
        'buyer_id',
        'vendor_id',
        'agreed_amount',
        'payment_status',
        'status',
        'started_at',
        'completed_at',
        'buyer_rating',
        'buyer_review',
        'reviewed_at',
        'vendor_reply',
        'vendor_replied_at',
    ];

    protected $casts = [
        'agreed_amount' => 'integer',
        'buyer_rating' => 'integer',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'vendor_replied_at' => 'datetime',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(ServiceProject::class, 'project_id');
    }

    public function proposal(): BelongsTo
    {
        return $this->belongsTo(ProjectProposal::class, 'proposal_id');
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function milestones(): HasMany
    {
        return $this->hasMany(ProjectMilestone::class, 'contract_id')->orderBy('sort_order');
    }

    public function getFormattedAmountAttribute(): string
    {
        return '₹' . number_format($this->agreed_amount / 100, 2);
    }
}

