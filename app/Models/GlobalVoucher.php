<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class GlobalVoucher extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code',
        'discount_amount',
        'discount_percent',
        'min_order_amount',
        'max_discount_amount',
        'start_date',
        'end_date',
        'status',
    ];

    protected $casts = [
        'discount_amount' => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'min_order_amount' => 'decimal:2',
        'max_discount_amount' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    /**
     * Get the orders that have used this global voucher.
     */
    public function orders(): BelongsToMany
    {
        return $this->belongsToMany(Order::class, 'order_global_vouchers')
            ->withPivot(['voucher_code', 'discount_amount', 'discount_percent'])
            ->withTimestamps();
    }
}
