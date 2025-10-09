<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\Cart;
use App\Models\GlobalVoucher;
use App\Models\ShippingRate;
use App\Models\ProductVoucher;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateOrderRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    use ApiResponse;

    /**
     * Display a listing of the user's orders.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            
            $orders = Order::with(['orderItems.productVariant.product'])
                ->where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->paginate($request->get('per_page', 15));

            return $this->successResponse('Orders retrieved successfully', [
                'data' => OrderResource::collection($orders->items()),
                'pagination' => [
                    'current_page' => $orders->currentPage(),
                    'last_page' => $orders->lastPage(),
                    'per_page' => $orders->perPage(),
                    'total' => $orders->total(),
                    'from' => $orders->firstItem(),
                    'to' => $orders->lastItem(),
                ]
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve orders', $e->getMessage(), 500);
        }
    }

    /**
     * Store a newly created order from cart.
     */
    public function store(StoreOrderRequest $request): JsonResponse
    {
        DB::beginTransaction();
        
        try {
            $user = Auth::user();
            $cart = Cart::with('cartItems.productVariant.product')->findOrFail($request->cart_id);
            
            // Verify cart belongs to user
            if ($cart->user_id !== $user->id) {
                throw ValidationException::withMessages(['cart_id' => 'Cart does not belong to authenticated user']);
            }

            if ($cart->cartItems->isEmpty()) {
                throw ValidationException::withMessages(['cart_id' => 'Cart is empty']);
            }

            // Get shipping rate
            $shippingRate = ShippingRate::findOrFail($request->shipping_rate_id);

            // Calculate total amount
            $totalAmount = $cart->cartItems->sum(function ($item) {
                return $item->quantity * $item->productVariant->price;
            });

            // Apply global vouchers
            $globalVoucherDiscount = 0;
            $globalVouchers = [];
            if ($request->has('global_voucher_codes')) {
                foreach ($request->global_voucher_codes as $code) {
                    $voucher = GlobalVoucher::where('code', $code)
                        ->where('status', 'active')
                        ->where('start_date', '<=', now())
                        ->where('end_date', '>=', now())
                        ->first();
                    
                    if ($voucher && $totalAmount >= $voucher->min_order_amount) {
                        $discount = $voucher->discount_amount ?: ($totalAmount * $voucher->discount_percent / 100);
                        if ($voucher->max_discount_amount) {
                            $discount = min($discount, $voucher->max_discount_amount);
                        }
                        $globalVoucherDiscount += $discount;
                        $globalVouchers[] = [
                            'voucher' => $voucher,
                            'discount' => $discount
                        ];
                    }
                }
            }

            // Apply product vouchers
            $productVoucherDiscount = 0;
            $productVouchers = [];
            if ($request->has('product_voucher_codes')) {
                foreach ($request->product_voucher_codes as $code) {
                    $voucher = ProductVoucher::where('code', $code)
                        ->where('status', 'active')
                        ->where('start_date', '<=', now())
                        ->where('end_date', '>=', now())
                        ->first();
                    
                    if ($voucher) {
                        // Check if cart contains the product
                        $cartItem = $cart->cartItems->first(function ($item) use ($voucher) {
                            return $item->productVariant->product_id === $voucher->product_id;
                        });
                        
                        if ($cartItem) {
                            $itemTotal = $cartItem->quantity * $cartItem->productVariant->price;
                            $discount = $voucher->discount_amount ?: ($itemTotal * $voucher->discount_percent / 100);
                            $productVoucherDiscount += $discount;
                            $productVouchers[] = [
                                'voucher' => $voucher,
                                'discount' => $discount,
                                'product_id' => $voucher->product_id
                            ];
                        }
                    }
                }
            }

            $finalAmount = $totalAmount - $globalVoucherDiscount - $productVoucherDiscount + $shippingRate->rate;

            // Create order
            $order = Order::create([
                'user_id' => $user->id,
                'cart_id' => $cart->id,
                'status' => 'pending',
                'payment_status' => 'pending',
                'payment_method' => $request->payment_method,
                'total_amount' => $finalAmount,
                'shipping_address' => $request->shipping_address,
                'courier_name' => $request->courier_name,
                'shipping_rate_id' => $shippingRate->id,
                'shipping_cost' => $shippingRate->rate,
            ]);

            // Create order items
            foreach ($cart->cartItems as $cartItem) {
                $order->orderItems()->create([
                    'product_variant_id' => $cartItem->product_variant_id,
                    'quantity' => $cartItem->quantity,
                    'price' => $cartItem->productVariant->price,
                ]);
            }

            // Attach global vouchers with pivot data
            foreach ($globalVouchers as $voucherData) {
                $order->globalVouchers()->attach($voucherData['voucher']->id, [
                    'voucher_code' => $voucherData['voucher']->code,
                    'discount_amount' => $voucherData['discount'],
                    'discount_percent' => $voucherData['voucher']->discount_percent,
                ]);
            }

            // Attach product vouchers with pivot data
            foreach ($productVouchers as $voucherData) {
                $order->productVouchers()->attach($voucherData['voucher']->id, [
                    'voucher_code' => $voucherData['voucher']->code,
                    'discount_amount' => $voucherData['discount'],
                    'discount_percent' => $voucherData['voucher']->discount_percent,
                    'product_id' => $voucherData['product_id'],
                ]);
            }

            // Clear cart after successful order
            $cart->cartItems()->delete();

            DB::commit();

            $order->load(['orderItems.productVariant.product', 'globalVouchers', 'productVouchers']);

            return $this->successResponse('Order created successfully', $order, 201);

        } catch (ValidationException $e) {
            DB::rollBack();
            return $this->validationErrorResponse('Validation failed', $e->errors());
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Failed to create order', $e->getMessage(), 500);
        }
    }

    /**
     * Display the specified order.
     */
    public function show(string $id): JsonResponse
    {
        try {
            $user = Auth::user();
            
            $order = Order::with(['orderItems.productVariant.product'])
                ->where('user_id', $user->id)
                ->findOrFail($id);

            return $this->successResponse('Order retrieved successfully', new OrderResource($order));
        } catch (\Exception $e) {
            return $this->errorResponse('Order not found', null, 404);
        }
    }

    /**
     * Update the specified order.
     */
    public function update(UpdateOrderRequest $request, string $id): JsonResponse
    {
        DB::beginTransaction();

        try {
            $user = Auth::user();
            
            $order = Order::where('user_id', $user->id)->findOrFail($id);

            // Only allow updates if order is still pending
            if ($order->status !== 'pending') {
                throw ValidationException::withMessages(['order' => 'Cannot update order that is not pending']);
            }

            $order->update($request->only(['shipping_address', 'courier_name']));

            DB::commit();

            return $this->successResponse('Order updated successfully', $order);

        } catch (ValidationException $e) {
            DB::rollBack();
            return $this->validationErrorResponse('Validation failed', $e->errors());
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Failed to update order', $e->getMessage(), 500);
        }
    }

    /**
     * Cancel the specified order.
     */
    public function destroy(string $id): JsonResponse
    {
        DB::beginTransaction();

        try {
            $user = Auth::user();
            
            $order = Order::where('user_id', $user->id)->findOrFail($id);

            // Only allow cancellation if order is pending or confirmed
            if (!in_array($order->status, ['pending', 'confirmed'])) {
                throw ValidationException::withMessages(['order' => 'Cannot cancel order with current status']);
            }

            $order->update(['status' => 'cancelled']);

            DB::commit();

            return $this->successResponse('Order cancelled successfully');

        } catch (ValidationException $e) {
            DB::rollBack();
            return $this->validationErrorResponse('Validation failed', $e->errors());
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Failed to cancel order', $e->getMessage(), 500);
        }
    }

    /**
     * Update order status (admin only).
     */
    public function updateStatus(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'status' => 'required|string|in:pending,confirmed,processing,shipped,delivered,cancelled',
            'tracking_number' => 'nullable|string',
        ]);

        DB::beginTransaction();

        try {
            $order = Order::findOrFail($id);

            $updateData = ['status' => $request->status];
            
            if ($request->has('tracking_number')) {
                $updateData['tracking_number'] = $request->tracking_number;
            }

            $order->update($updateData);

            DB::commit();

            return $this->successResponse('Order status updated successfully', $order);

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Failed to update order status', $e->getMessage(), 500);
        }
    }

    /**
     * Update payment status (webhook/admin).
     */
    public function updatePaymentStatus(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'payment_status' => 'required|string|in:pending,paid,failed,refunded',
            'xendit_invoice_id' => 'nullable|string',
            'xendit_payment_id' => 'nullable|string',
            'xendit_invoice_url' => 'nullable|url',
        ]);

        DB::beginTransaction();

        try {
            $order = Order::findOrFail($id);

            $order->update($request->only([
                'payment_status',
                'xendit_invoice_id',
                'xendit_payment_id',
                'xendit_invoice_url'
            ]));

            // Auto-confirm order if payment is successful
            if ($request->payment_status === 'paid' && $order->status === 'pending') {
                $order->update(['status' => 'confirmed']);
            }

            DB::commit();

            return $this->successResponse('Payment status updated successfully', $order);

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Failed to update payment status', $e->getMessage(), 500);
        }
    }
}
