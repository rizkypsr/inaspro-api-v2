<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Tv extends Model
{
    use HasFactory;

    protected $fillable = [
        'tv_category_id',
        'title',
        'link',
        'image',
        'status',
    ];

    protected $casts = [
        'tv_category_id' => 'integer',
        'status' => 'string',
    ];

    /**
     * Get the category that owns this TV.
     */
    public function tvCategory(): BelongsTo
    {
        return $this->belongsTo(TvCategory::class);
    }

    /**
     * Scope a query to only include active TVs.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}