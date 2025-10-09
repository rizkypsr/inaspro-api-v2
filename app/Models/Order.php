<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Order extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'cart_id',
        'uuid',
        'status',
        'payment_status',
        'payment_method',
        'xendit_invoice_id',
        'xendit_payment_id',
        'xendit_invoice_url',
        'total_amount',
        'shipping_address',
        'courier_name',
        'tracking_number',
        'shipping_rate_id',
        'shipping_cost',
    ];

    protected $casts = [
        'total_amount' => 'integer',
        'shipping_cost' => 'integer',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            if (empty($order->uuid)) {
                $order->uuid = (string) Str::uuid();
            }
        });
    }

    /**
     * Get the user that owns the order.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the cart associated with the order.
     */
    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    /**
     * Get the order items for the order.
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Get the global vouchers applied to this order.
     */
    public function globalVouchers(): BelongsToMany
    {
        return $this->belongsToMany(GlobalVoucher::class, 'order_global_vouchers')
            ->withPivot(['voucher_code', 'discount_amount', 'discount_percent'])
            ->withTimestamps();
    }

    /**
     * Get the product vouchers applied to this order.
     */
    public function productVouchers(): BelongsToMany
    {
        return $this->belongsToMany(ProductVoucher::class, 'order_product_vouchers')
            ->withPivot(['voucher_code', 'discount_amount', 'discount_percent', 'product_id'])
            ->withTimestamps();
    }

    /**
     * Get the shipping rate for the order.
     */
    public function shippingRate(): BelongsTo
    {
        return $this->belongsTo(ShippingRate::class);
    }
}
