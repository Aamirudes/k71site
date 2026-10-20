<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShortComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'short_id',
        'user_id',
        'parent_id',
        'content',
        'status',
        'likes',
    ];

    protected $casts = [
        'parent_id' => 'integer',
        'likes' => 'integer',
    ];

    public function short(): BelongsTo
    {
        return $this->belongsTo(Short::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function replies(): HasMany
    {
        return $this->hasMany(ShortComment::class, 'parent_id');
    }
}
