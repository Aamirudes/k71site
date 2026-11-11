<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ServiceProject extends Model
{
    use HasFactory;

    protected $fillable = [
        'buyer_id',
        'category_id',
        'project_number',
        'title',
        'slug',
        'description',
        'skills_json',
        'deliverables_json',
        'attachments_json',
        'budget_type',
        'budget_min',
        'budget_max',
        'expected_duration',
        'deadline_at',
        'work_mode',
        'city',
        'status',
        'proposals_count',
        'hired_vendor_id',
    ];

    protected $casts = [
        'skills_json' => 'array',
        'deliverables_json' => 'array',
        'attachments_json' => 'array',
        'budget_min' => 'integer',
        'budget_max' => 'integer',
        'proposals_count' => 'integer',
        'deadline_at' => 'date',
    ];

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function hiredVendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class, 'hired_vendor_id');
    }

    public function proposals(): HasMany
    {
        return $this->hasMany(ProjectProposal::class, 'project_id')->latest();
    }

    public function contract(): HasOne
    {
        return $this->hasOne(ProjectContract::class, 'project_id');
    }

    public function getFormattedBudgetAttribute(): string
    {
        $min = '₹' . number_format($this->budget_min / 100, 0);
        if ($this->budget_max && $this->budget_max > $this->budget_min) {
            return $min . ' – ₹' . number_format($this->budget_max / 100, 0);
        }
        return $min . ($this->budget_type === 'hourly' ? '/hr' : '');
    }

    public function scopeOpen($query)
    {
        return $query->whereIn('status', ['open', 'shortlisting']);
    }
}

