<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FantasyPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'fantasy_registration_id',
        'amount',
        'method',
        'status',
        'transaction_id',
        'evidence',
    ];

    protected $casts = [
        'amount' => 'integer',
    ];

    /**
     * Get the fantasy registration that owns this payment.
     */
    public function fantasyRegistration(): BelongsTo
    {
        return $this->belongsTo(FantasyRegistration::class);
    }

    /**
     * Check if payment is confirmed.
     */
    public function isConfirmed(): bool
    {
        return $this->status === 'confirmed';
    }

    /**
     * Check if payment is waiting.
     */
    public function isWaiting(): bool
    {
        return $this->status === 'waiting';
    }

    /**
     * Check if payment is rejected.
     */
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    /**
     * Check if payment is failed.
     */
    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * Check if payment is refunded.
     */
    public function isRefunded(): bool
    {
        return $this->status === 'refunded';
    }
}