<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Part extends Model
{
    use HasFactory;

    protected $fillable = [
        'sku',
        'name',
        'description',
        'category',
        'brand',
        'cost_price',
        'selling_price',
        'quantity_in_stock',
        'reorder_level',
        'location',
        'supplier',
        'is_active',
    ];

    protected $casts = [
        'cost_price'        => 'decimal:2',
        'selling_price'     => 'decimal:2',
        'quantity_in_stock' => 'integer',
        'reorder_level'     => 'integer',
        'is_active'         => 'boolean',
    ];

    public function jobOrderParts(): HasMany
    {
        return $this->hasMany(JobOrderPart::class);
    }

    public function getIsLowStockAttribute(): bool
    {
        return $this->quantity_in_stock <= $this->reorder_level;
    }

    public function getMarginAttribute(): float
    {
        if ($this->cost_price == 0) {
            return 0;
        }

        return round((($this->selling_price - $this->cost_price) / $this->cost_price) * 100, 1);
    }
}
