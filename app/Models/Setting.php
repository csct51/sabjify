<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

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

    /**
     * Resolve the store logo URL, or null when the default icon is used.
     */
    public static function logoUrl(): ?string
    {
        $type = static::get('logo_type');
        $value = static::get('logo_value');

        if (! is_string($value) || $value === '') {
            return null;
        }

        if ($type === 'url') {
            return $value;
        }

        if ($type === 'image' && Storage::disk('public')->exists($value)) {
            return '/storage/'.$value;
        }

        return null;
    }

    /**
     * Resolve the store logo as a base64 data URI for embedding in documents
     * such as PDF invoices, or null when no logo is configured.
     */
    public static function logoDataUri(): ?string
    {
        $type = static::get('logo_type');
        $value = static::get('logo_value');

        if (! is_string($value) || $value === '') {
            return null;
        }

        $content = match ($type) {
            'url' => @file_get_contents($value),
            'image' => Storage::disk('public')->exists($value) ? Storage::disk('public')->get($value) : null,
            default => null,
        };

        if (! is_string($content) || $content === '') {
            return null;
        }

        $mime = (new \finfo(FILEINFO_MIME_TYPE))->buffer($content) ?: 'image/png';

        return 'data:'.$mime.';base64,'.base64_encode($content);
    }
}
