<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TvCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'status',
    ];

    protected $casts = [
        'status' => 'string',
    ];

    /**
     * Get the TVs for this category.
     */
    public function tvs(): HasMany
    {
        return $this->hasMany(Tv::class);
    }

    /**
     * Get active TVs for this category.
     */
    public function activeTvs(): HasMany
    {
        return $this->hasMany(Tv::class)->where('status', 'active');
    }

    /**
     * Scope a query to only include active categories.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}