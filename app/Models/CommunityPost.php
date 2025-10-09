<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CommunityPost extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'community_id',
        'admin_id',
        'caption',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the community that owns the post
     */
    public function community(): BelongsTo
    {
        return $this->belongsTo(Community::class);
    }

    /**
     * Get the admin who created the post
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    /**
     * Get the images for the post
     */
    public function images(): HasMany
    {
        return $this->hasMany(CommunityPostImage::class, 'post_id')->orderBy('position');
    }

    /**
     * Scope to get posts with images
     */
    public function scopeWithImages($query)
    {
        return $query->with('images');
    }

    /**
     * Scope to get posts for a specific community
     */
    public function scopeForCommunity($query, $communityId)
    {
        return $query->where('community_id', $communityId);
    }
}
