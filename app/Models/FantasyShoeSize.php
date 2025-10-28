<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FantasyShoeSize extends Model
{
    use HasFactory;

    protected $fillable = [
        'fantasy_shoe_id',
        'size',
        'stock',
        'reserved_stock',
    ];

    protected $casts = [
        'stock' => 'integer',
        'reserved_stock' => 'integer',
    ];

    /**
     * Get the fantasy shoe that owns this shoe size.
     */
    public function fantasyShoe(): BelongsTo
    {
        return $this->belongsTo(FantasyShoe::class);
    }

    /**
     * Get the registration items for this shoe size.
     */
    public function registrationItems(): HasMany
    {
        return $this->hasMany(FantasyRegistrationItem::class);
    }

    /**
     * Get available stock (total stock minus reserved stock).
     */
    public function getAvailableStockAttribute(): int
    {
        return $this->stock - $this->reserved_stock;
    }
}