<?php

namespace App\Models;

use Database\Factories\SupplierFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    /** @use HasFactory<SupplierFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'contact',
        'address',
    ];

    /**
     * Options for the admin searchable supplier picker (Cash pinned first).
     *
     * @return array<int, array{value: string, text: string, search: string}>
     */
    public static function pickerOptions(): array
    {
        $names = array_values(array_unique(array_merge(
            ['Cash'],
            static::orderBy('name')->pluck('name')->all()
        )));

        return array_map(
            fn (string $name) => ['value' => $name, 'text' => $name, 'search' => 'supplier'],
            $names
        );
    }

    /**
     * @return HasMany<Purchase, $this>
     */
    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class);
    }
}
