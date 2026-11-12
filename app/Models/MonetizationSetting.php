<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MonetizationSetting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'type',
    ];

    public static function getValue(string $key, mixed $default = null): mixed
    {
        $row = static::where('key', $key)->first();
        if (!$row) {
            return $default;
        }
        return match ($row->type) {
            'boolean' => filter_var($row->value, FILTER_VALIDATE_BOOLEAN),
            'integer' => (int) $row->value,
            'json' => json_decode((string) $row->value, true) ?? $default,
            default => $row->value ?? $default,
        };
    }

    public static function setValue(string $key, mixed $value, string $type = 'string'): self
    {
        $encoded = match ($type) {
            'boolean' => $value ? '1' : '0',
            'integer' => (string) (int) $value,
            'json' => json_encode($value),
            default => (string) $value,
        };

        return static::updateOrCreate(
            ['key' => $key],
            ['value' => $encoded, 'type' => $type]
        );
    }
}
