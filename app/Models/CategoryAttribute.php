<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CategoryAttribute extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'attribute_code',
        'label',
        'input_type',
        'is_variant_driver',
        'is_filterable',
        'is_required',
        'options_json',
        'sort_order',
    ];

    protected $casts = [
        'is_variant_driver' => 'boolean',
        'is_filterable' => 'boolean',
        'is_required' => 'boolean',
        'options_json' => 'array',
        'sort_order' => 'integer',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
