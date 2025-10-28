<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FantasyRegistrationItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'fantasy_registration_id',
        'fantasy_tshirt_option_id',
        'fantasy_shoe_size_id',
        'price',
    ];

    protected $casts = [
        'price' => 'integer',
    ];

    /**
     * Get the fantasy registration that owns this item.
     */
    public function fantasyRegistration(): BelongsTo
    {
        return $this->belongsTo(FantasyRegistration::class);
    }

    /**
     * Get the fantasy t-shirt option for this item.
     */
    public function fantasyTshirtOption(): BelongsTo
    {
        return $this->belongsTo(FantasyTshirtOption::class);
    }

    /**
     * Get the fantasy shoe size for this item.
     */
    public function fantasyShoeSize(): BelongsTo
    {
        return $this->belongsTo(FantasyShoeSize::class);
    }

    /**
     * Check if this item is a t-shirt.
     */
    public function isTshirt(): bool
    {
        return !is_null($this->fantasy_tshirt_option_id);
    }

    /**
     * Check if this item is a shoe.
     */
    public function isShoe(): bool
    {
        return !is_null($this->fantasy_shoe_size_id);
    }
}