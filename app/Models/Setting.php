<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'group',
        'key',
        'value',
        'type',
    ];

    /**
     * Retrieve a setting value by key with optional fallback.
     */
    public static function get(string $key, $default = null)
    {
        $setting = static::where('key', $key)->first();
        if (!$setting) {
            return $default;
        }

        return match ($setting->type) {
            'boolean' => filter_var($setting->value, FILTER_VALIDATE_BOOLEAN),
            'integer' => (int) $setting->value,
            'json' => json_decode($setting->value, true) ?: [],
            default => $setting->value,
        };
    }

    /**
     * Set or update a setting.
     */
    public static function set(string $key, $value, string $group = 'general', string $type = 'string'): void
    {
        $stringValue = match ($type) {
            'boolean' => $value ? '1' : '0',
            'json' => is_array($value) ? json_encode($value) : (string) $value,
            default => (string) $value,
        };

        static::updateOrCreate(
            ['key' => $key],
            [
                'group' => $group,
                'value' => $stringValue,
                'type' => $type,
            ]
        );
    }

    /**
     * Retrieve all settings in a specific group as key-value pairs.
     */
    public static function getGroup(string $group): array
    {
        $settings = static::where('group', $group)->get();
        $result = [];
        foreach ($settings as $setting) {
            $result[$setting->key] = match ($setting->type) {
                'boolean' => filter_var($setting->value, FILTER_VALIDATE_BOOLEAN),
                'integer' => (int) $setting->value,
                'json' => json_decode($setting->value, true) ?: [],
                default => $setting->value,
            };
        }
        return $result;
    }
}
