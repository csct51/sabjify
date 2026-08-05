<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $key
 * @property string|null $value
 */
#[Fillable(['key', 'value'])]
class Setting extends Model
{
    /**
     * Get the value of a setting, falling back to a default.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $setting = static::query()->where('key', $key)->first();

        return $setting->value ?? $default;
    }

    /**
     * Get the value of a setting as an array, falling back to a default.
     *
     * @param  array<int, string>  $default
     * @return array<int, string>
     */
    public static function getArray(string $key, array $default = []): array
    {
        $value = static::get($key);

        if (is_string($value) && $value !== '') {
            return array_values(array_filter(array_map('trim', explode(',', $value))));
        }

        return $default;
    }
}
