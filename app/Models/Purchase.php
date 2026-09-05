<?php

namespace App\Models;

use Database\Factories\PurchaseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Purchase extends Model
{
    /** @use HasFactory<PurchaseFactory> */
    use HasFactory;

    protected $fillable = [
        'purchase_number',
        'supplier_id',
        'supplier_name',
        'purchase_date',
        'remark',
        'total_amount',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'purchase_date' => 'date',
            'total_amount' => 'integer',
        ];
    }

    /**
     * @return HasMany<PurchaseItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }

    /**
     * @return BelongsTo<Supplier, $this>
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * @return BelongsTo<Admin, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }
}
