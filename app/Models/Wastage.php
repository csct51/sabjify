<?php

namespace App\Models;

use Database\Factories\WastageFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Wastage extends Model
{
    /** @use HasFactory<WastageFactory> */
    use HasFactory;

    protected $fillable = [
        'wastage_number',
        'wastage_date',
        'reason',
        'remark',
        'total_qty',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'wastage_date' => 'date',
            'total_qty' => 'decimal:3',
        ];
    }

    /**
     * @return HasMany<WastageItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(WastageItem::class);
    }

    /**
     * @return BelongsTo<Admin, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }
}
