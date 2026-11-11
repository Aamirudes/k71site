<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UsedSavedSearch extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'query',
        'city',
        'filters_json',
        'last_notified_at',
    ];

    protected $casts = [
        'filters_json' => 'array',
        'last_notified_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function buildSearchUrl(): string
    {
        $params = array_filter(array_merge(
            $this->filters_json ?? [],
            [
                'search' => $this->query,
                'city' => $this->city,
            ]
        ), fn($v) => $v !== null && $v !== '' && $v !== false);

        return route('used.index', $params);
    }
}

