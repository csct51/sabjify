<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class CommaSeparatedArray implements CastsAttributes
{
    /**
     * @param  Model  $model
     * @param  mixed  $value
     * @param  array<string, mixed>  $attributes
     * @return array<int, string>
     */
    public function get($model, string $key, $value, array $attributes): array
    {
        if (blank($value)) {
            return [];
        }

        return collect(explode(',', (string) $value))
            ->map(fn (string $name) => trim($name))
            ->filter(fn (string $name) => $name !== '')
            ->values()
            ->all();
    }

    /**
     * @param  Model  $model
     * @param  mixed  $value
     * @param  array<string, mixed>  $attributes
     */
    public function set($model, string $key, $value, array $attributes): string
    {
        $names = is_array($value)
            ? $value
            : preg_split('/,+/', (string) $value, flags: PREG_SPLIT_NO_EMPTY);

        return collect($names ?? [])
            ->map(fn (string $name) => trim($name))
            ->filter(fn (string $name) => $name !== '')
            ->unique()
            ->sort()
            ->implode(',');
    }
}
