<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ProjectProposal extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'vendor_id',
        'cover_letter',
        'bid_amount',
        'delivery_days',
        'pricing_type',
        'portfolio_project_ids_json',
        'proposed_milestones_json',
        'status',
    ];

    protected $casts = [
        'bid_amount' => 'integer',
        'delivery_days' => 'integer',
        'portfolio_project_ids_json' => 'array',
        'proposed_milestones_json' => 'array',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(ServiceProject::class, 'project_id');
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function contract(): HasOne
    {
        return $this->hasOne(ProjectContract::class, 'proposal_id');
    }

    public function attachedPortfolioProjects()
    {
        $ids = array_filter(array_map('intval', $this->portfolio_project_ids_json ?? []));
        if (empty($ids)) {
            return collect();
        }
        return PortfolioProject::where('vendor_id', $this->vendor_id)
            ->whereIn('id', $ids)
            ->get();
    }

    public function getFormattedBidAttribute(): string
    {
        return '₹' . number_format($this->bid_amount / 100, 0);
    }
}

