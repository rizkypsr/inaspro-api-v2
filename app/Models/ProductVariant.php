<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductVariant extends Model
{
    use HasFactory;
    protected $fillable = [
        'sku',
        'product_id',
        'variant_name',
        'image_url',
        'price',
        'stock',
        'status',
    ];

    protected $casts = [
        'price' => 'integer',
        'stock' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the product that owns the variant.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the stock logs for the variant.
     */
    public function stockLogs(): HasMany
    {
        return $this->hasMany(ProductStockLog::class);
    }
}
