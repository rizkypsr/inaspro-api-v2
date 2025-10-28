<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FantasyShoe extends Model
{
    use HasFactory;

    protected $fillable = [
        'fantasy_event_id',
        'name',
        'price',
        'image',
    ];

    protected $casts = [
        'price' => 'integer',
    ];

    /**
     * Get the fantasy event that owns this shoe.
     */
    public function fantasyEvent(): BelongsTo
    {
        return $this->belongsTo(FantasyEvent::class);
    }

    /**
     * Get the shoe sizes for this shoe.
     */
    public function shoeSizes(): HasMany
    {
        return $this->hasMany(FantasyShoeSize::class);
    }

    /**
     * Get the shoe sizes for this shoe (alias for shoeSizes).
     */
    public function sizes(): HasMany
    {
        return $this->hasMany(FantasyShoeSize::class);
    }
}