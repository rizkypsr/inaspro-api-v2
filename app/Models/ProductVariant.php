<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

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

    /**
     * Accessor: return full absolute URL for image_url.
     */
    public function getImageUrlAttribute(?string $value): ?string
    {
        if (!$value) {
            return null;
        }

        // If already absolute (external or full URL), return as-is
        if (Str::startsWith($value, ['http://', 'https://'])) {
            return $value;
        }

        // Normalize to storage URL if it's a relative path
        $path = $value;
        if (!Str::startsWith($path, ['/storage', 'storage'])) {
            // Convert to public storage URL (e.g., /storage/product-variants/xxx.jpg)
            $path = Storage::disk('public')->url(ltrim($path, '/'));
        }

        // Convert to absolute URL using app URL configuration
        return URL::to($path);
    }
}
