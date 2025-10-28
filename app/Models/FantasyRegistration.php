<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FantasyRegistration extends Model
{
    use HasFactory;

    protected $fillable = [
        'registration_code',
        'fantasy_event_id',
        'user_id',
        'fantasy_event_team_id',
        'registration_fee',
        'status',
    ];

    protected $casts = [
        'registration_fee' => 'integer',
    ];

    /**
     * Get the fantasy event that owns this registration.
     */
    public function fantasyEvent(): BelongsTo
    {
        return $this->belongsTo(FantasyEvent::class);
    }

    /**
     * Get the user that owns this registration.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the fantasy event team that owns this registration.
     */
    public function fantasyEventTeam(): BelongsTo
    {
        return $this->belongsTo(FantasyEventTeam::class);
    }

    /**
     * Get the registration items for this registration.
     */
    public function registrationItems(): HasMany
    {
        return $this->hasMany(FantasyRegistrationItem::class);
    }

    /**
     * Get the payments for this registration.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(FantasyPayment::class);
    }
}