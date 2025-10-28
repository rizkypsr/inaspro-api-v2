<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FantasyEventTeam extends Model
{
    use HasFactory;

    protected $fillable = [
        'fantasy_event_id',
        'name',
        'slot_limit',
    ];

    protected $casts = [
        'slot_limit' => 'integer',
    ];

    /**
     * Get the fantasy event that owns this team.
     */
    public function fantasyEvent(): BelongsTo
    {
        return $this->belongsTo(FantasyEvent::class);
    }

    /**
     * Get the t-shirt options for this team.
     */
    public function tshirtOptions(): HasMany
    {
        return $this->hasMany(FantasyTshirtOption::class);
    }

    /**
     * Get the registrations for this team.
     */
    public function registrations(): HasMany
    {
        return $this->hasMany(FantasyRegistration::class);
    }
}