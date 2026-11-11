<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductOptionGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'name',
        'selection_type',
        'is_required',
        'min_selections',
        'max_selections',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'min_selections' => 'integer',
        'max_selections' => 'integer',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class)->withoutGlobalScopes();
    }

    public function modifiers(): HasMany
    {
        return $this->hasMany(ProductModifier::class, 'option_group_id')->orderBy('sort_order')->orderBy('id');
    }

    public function effectiveMinSelections(): int
    {
        if ($this->selection_type === 'single') {
            return $this->is_required ? 1 : 0;
        }
        return $this->is_required ? max(1, (int) $this->min_selections) : (int) $this->min_selections;
    }

    public function effectiveMaxSelections(): ?int
    {
        if ($this->selection_type === 'single') {
            return 1;
        }
        return $this->max_selections !== null && (int) $this->max_selections > 0
            ? (int) $this->max_selections
            : null;
    }
}

