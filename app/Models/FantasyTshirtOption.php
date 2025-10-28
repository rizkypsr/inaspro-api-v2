<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FantasyTshirtOption extends Model
{
    use HasFactory;

    protected $fillable = [
        'fantasy_event_team_id',
        'size',
    ];

    /**
     * Get the fantasy event team that owns this t-shirt option.
     */
    public function fantasyEventTeam(): BelongsTo
    {
        return $this->belongsTo(FantasyEventTeam::class);
    }

    /**
     * Get the registration items for this t-shirt option.
     */
    public function registrationItems(): HasMany
    {
        return $this->hasMany(FantasyRegistrationItem::class);
    }
}