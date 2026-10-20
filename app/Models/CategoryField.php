<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CategoryField extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'vertical',
        'name',
        'label',
        'field_type',
        'options',
        'placeholder',
        'help_text',
        'default_value',
        'is_required',
        'sort_order',
    ];

    protected $casts = [
        'options' => 'array',
        'is_required' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Scope query to a specific vertical.
     */
    public function scopeForVertical(Builder $query, string $vertical): Builder
    {
        return $query->where('vertical', $vertical);
    }

    /**
     * Scope query to a specific category.
     */
    public function scopeForCategory(Builder $query, int $categoryId): Builder
    {
        return $query->where('category_id', $categoryId);
    }

    /**
     * Retrieve applicable fields for a given vertical and optional category.
     */
    public static function getFieldsFor(string $vertical, ?int $categoryId = null): Collection
    {
        return static::where(function (Builder $query) use ($vertical, $categoryId) {
            $query->where('vertical', $vertical);
            if ($categoryId) {
                $query->orWhere('category_id', $categoryId);
            }
        })
        ->orderBy('sort_order')
        ->get();
    }
}
