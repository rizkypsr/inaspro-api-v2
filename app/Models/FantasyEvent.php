<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FantasyEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'location',
        'play_date',
        'base_fee',
        'status',
        'created_by',
    ];

    protected $casts = [
        'play_date' => 'datetime',
        'base_fee' => 'integer',
    ];

    /**
     * Get the user who created this fantasy event.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the teams for this fantasy event.
     */
    public function teams(): HasMany
    {
        return $this->hasMany(FantasyEventTeam::class);
    }

    /**
     * Get the shoes for this fantasy event.
     */
    public function shoes(): HasMany
    {
        return $this->hasMany(FantasyShoe::class);
    }

    /**
     * Get the registrations for this fantasy event.
     */
    public function registrations(): HasMany
    {
        return $this->hasMany(FantasyRegistration::class);
    }

    /**
     * Check if the current authenticated user is registered for this event.
     */
    public function isRegistered(?int $userId = null): bool
    {
        $userId = $userId ?? auth()->id();
        
        if (!$userId) {
            return false;
        }

        return $this->registrations()
            ->where('user_id', $userId)
            ->exists();
    }
}