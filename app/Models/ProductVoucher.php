<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ProductVoucher extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'product_id',
        'code',
        'discount_amount',
        'discount_percent',
        'start_date',
        'end_date',
        'status',
    ];

    protected $casts = [
        'discount_amount' => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    /**
     * Get the product that owns the voucher.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the orders that have used this product voucher.
     */
    public function orders(): BelongsToMany
    {
        return $this->belongsToMany(Order::class, 'order_product_vouchers')
            ->withPivot(['voucher_code', 'discount_amount', 'discount_percent', 'product_id'])
            ->withTimestamps();
    }
}
