<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Address extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'label',
        'full_name',
        'phone',
        'line_1',
        'line_2',
        'landmark',
        'city',
        'state',
        'pincode',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function getRecipientNameAttribute(): string
    {
        return (string) ($this->attributes['full_name'] ?? '');
    }

    public function getAddressLine1Attribute(): string
    {
        return (string) ($this->attributes['line_1'] ?? '');
    }

    public function getAddressLine2Attribute(): ?string
    {
        return $this->attributes['line_2'] ?? null;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    public function formatted(): string
    {
        return collect([
            $this->line_1,
            $this->line_2,
            $this->landmark,
            trim(collect([$this->city, $this->state])->filter()->implode(', ')),
            $this->pincode,
        ])->filter()->implode(', ');
    }

    public function shortLabel(): string
    {
        $tag = $this->label ? ucfirst($this->label) : 'Saved';
        $city = $this->city ?: ($this->line_1 ?: 'Address');
        $pin = $this->pincode ? " ({$this->pincode})" : '';
        return "{$tag}: {$city}{$pin}";
    }
}
