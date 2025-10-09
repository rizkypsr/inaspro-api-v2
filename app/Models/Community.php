<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Community extends Model
{
    use SoftDeletes, HasFactory;

    protected $fillable = [
        'name',
        'description',
        'profile_image_url',
        'is_private',
        'created_by',
    ];

    protected $casts = [
        'is_private' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the user who created the community
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get all members of the community
     */
    public function members(): HasMany
    {
        return $this->hasMany(CommunityMember::class);
    }

    /**
     * Get approved members only
     */
    public function approvedMembers(): HasMany
    {
        return $this->hasMany(CommunityMember::class)->where('status', 'approved');
    }

    /**
     * Get pending members (for private communities)
     */
    public function pendingMembers(): HasMany
    {
        return $this->hasMany(CommunityMember::class)->where('status', 'pending');
    }

    /**
     * Get community posts
     */
    public function posts(): HasMany
    {
        return $this->hasMany(CommunityPost::class);
    }

    /**
     * Get community admins
     */
    public function admins(): HasMany
    {
        return $this->hasMany(CommunityMember::class)->where('role', 'admin');
    }
}
