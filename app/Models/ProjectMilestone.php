<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectMilestone extends Model
{
    use HasFactory;

    protected $fillable = [
        'contract_id',
        'title',
        'description',
        'amount',
        'due_at',
        'sort_order',
        'status',
        'deliverables_json',
        'submitted_notes',
        'revision_notes',
        'submitted_at',
        'approved_at',
    ];

    protected $casts = [
        'amount' => 'integer',
        'sort_order' => 'integer',
        'due_at' => 'date',
        'deliverables_json' => 'array',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    public function contract(): BelongsTo
    {
        return $this->belongsTo(ProjectContract::class, 'contract_id');
    }

    public function getFormattedAmountAttribute(): string
    {
        return '₹' . number_format($this->amount / 100, 2);
    }
}

