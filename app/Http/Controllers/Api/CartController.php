<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CartRequest;
use App\Http\Requests\CartItemRequest;
use App\Http\Traits\ApiResponse;
use App\Models\Cart;
use App\Models\CartItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    use ApiResponse;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Cart::with(['user', 'cartItems.productVariant.product']);

            // Filter by user_id if provided
            if ($request->has('user_id')) {
                $query->where('user_id', $request->user_id);
            }

            // Filter by status if provided
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            // Search functionality
            if ($request->has('search')) {
                $search = $request->search;
                $query->whereHas('user', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            }

            // Pagination
            $perPage = $request->get('per_page', 15);
            $carts = $query->paginate($perPage);

            return $this->successResponse($carts, 'Carts retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve carts',  $e->getMessage(), 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(): JsonResponse
    {
        try {
            DB::beginTransaction();

            $userId = auth()->id();

            if (!$userId) {
                return $this->errorResponse('You can only create a cart for yourself', 'You must be authenticated to create a cart.', 403);
            }

            $cart = Cart::firstOrCreate(['user_id' => $userId, 'status' => 'active']);

            DB::commit();

            return $this->successResponse('Cart created successfully', $cart->load(['user', 'cartItems']), 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Failed to create cart', $e->getMessage(),500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        try {
            $cart = Cart::with(['user', 'cartItems.productVariant.product'])->findOrFail($id);

            return $this->successResponse($cart, 'Cart retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Cart not found', $e->getMessage(), 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CartRequest $request, string $id): JsonResponse
    {
        try {
            DB::beginTransaction();

            $cart = Cart::findOrFail($id);
            $cart->update($request->validated());

            DB::commit();

            return $this->successResponse('Cart updated successfully', $cart->load(['user', 'cartItems']));
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Failed to update cart', $e->getMessage(), 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            DB::beginTransaction();

            $cart = Cart::findOrFail($id);

            // Check if cart has items
            if ($cart->cartItems()->count() > 0) {
                return $this->errorResponse('Cannot delete cart with items. Please remove all items first.', 400);
            }

            $cart->delete();

            DB::commit();

            return $this->successResponse('Cart deleted successfully', null);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Failed to delete cart', $e->getMessage(), 500);
        }
    }

    // ========== CART ITEMS MANAGEMENT ==========

    /**
     * Display cart items for authenticated user. Creates cart if it doesn't exist.
     */
    public function getItems(Request $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $userId = auth()->id();

            // Get or create cart for the authenticated user
            $cart = Cart::firstOrCreate(['user_id' => $userId, 'status' => 'active']);

            $cartItems = CartItem::with(['productVariant.product'])
                ->where('cart_id', $cart->id)
                ->get()
                ->map(function ($item) {
                    $variantPrice = $item->productVariant->price ?? 0;
                    $quantity = $item->quantity;
                    $subtotal = $variantPrice * $quantity;
                    
                    return [
                        'id' => $item->id,
                        'cart_id' => $item->cart_id,
                        'product_variant_id' => $item->product_variant_id,
                        'variant_name' => $item->productVariant->variant_name ?? null,
                        'variant_image_url' => $item->productVariant->image_url ?? null,
                        'quantity' => $quantity,
                        'variant_price' => $variantPrice,
                        'subtotal' => $subtotal,
                        'product_name' => $item->productVariant->product->name ?? null,
                    ];
                });

            // Calculate total price for all items
            $totalPrice = $cartItems->sum('subtotal');

            $response = [
                'cart_id' => $cart->id,
                'items' => $cartItems,
                'total_price' => $totalPrice,
            ];

            DB::commit();

            return $this->successResponse('Cart items retrieved successfully', $response);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Failed to retrieve cart items', $e->getMessage(), 500);
        }
    }

    /**
     * Add item to cart.
     */
    public function addItem(CartItemRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $userId = auth()->id();

            // Get or create cart for the authenticated user
            $cart = Cart::firstOrCreate(['user_id' => $userId, 'status' => 'active']);

            // Check if item already exists in cart
            $existingItem = CartItem::where('cart_id', $cart->id)
                ->where('product_variant_id', $request->product_variant_id)
                ->first();

            if ($existingItem) {
                // Update quantity if item already exists
                $existingItem->quantity += $request->quantity;
                $existingItem->save();
                $cartItem = $existingItem;
            } else {
                // Create new cart item
                $cartItem = CartItem::create([
                    'cart_id' => $cart->id,
                    'product_variant_id' => $request->product_variant_id,
                    'quantity' => $request->quantity,
                ]);
            }

            DB::commit();

            return $this->successResponse('Item added to cart successfully', $cartItem->load(['productVariant.product']), 201); 
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Failed to add item to cart', $e->getMessage(), 500);
        }
    }

    /**
     * Update cart item quantity.
     */
    public function updateItem(CartItemRequest $request, string $itemId): JsonResponse
    {
        try {
            DB::beginTransaction();

            $userId = auth()->id();

            // Get cart for the authenticated user
            $cart = Cart::where('user_id', $userId)->where('status', 'active')->first();
            if (!$cart) {
                return $this->errorResponse('Cart not found', 404);
            }

            // Find cart item
            $cartItem = CartItem::where('cart_id', $cart->id)
                ->where('id', $itemId)
                ->first();

            if (!$cartItem) {
                return $this->errorResponse('Cart item not found', 404);
            }

            if ($request->quantity <= 0) {
                // Remove item if quantity is zero or less
                $cartItem->delete();
                DB::commit();
                return $this->successResponse('Item removed from cart successfully', null);
            }

            $cartItem->update([
                'quantity' => $request->quantity,
            ]);

            DB::commit();

            // Return simplified response matching getItems format
            $updatedItem = [
                'id' => $cartItem->id,
                'cart_id' => $cartItem->cart_id,
                'variant_name' => $cartItem->productVariant->name ?? null,
                'variant_image_url' => $cartItem->productVariant->image_url ?? null,
                'quantity' => $cartItem->quantity,
                'variant_price' => $cartItem->productVariant->price ?? 0,
                'product_name' => $cartItem->productVariant->product->name ?? null,
            ];

            return $this->successResponse('Cart item updated successfully', $updatedItem);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Failed to update cart item', $e->getMessage(), 500);
        }
    }

    /**
     * Remove item from cart.
     */
    public function removeItem(string $itemId): JsonResponse
    {
        try {
            DB::beginTransaction();

            $userId = auth()->id();

            // Get cart for the authenticated user
            $cart = Cart::where('user_id', $userId)->where('status', 'active')->first();
            if (!$cart) {
                return $this->errorResponse('Cart not found', 404);
            }

            $cartItem = CartItem::where('cart_id', $cart->id)
                ->where('id', $itemId)
                ->firstOrFail();

            $cartItem->delete();

            DB::commit();

            return $this->successResponse('Item removed from cart successfully', null);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Failed to remove item from cart', $e->getMessage(), 500);
        }
    }

    /**
     * Clear all items from cart.
     */
    public function clearItems(): JsonResponse
    {
        try {
            DB::beginTransaction();

            $userId = auth()->id();

            // Get cart for the authenticated user
            $cart = Cart::where('user_id', $userId)->where('status', 'active')->first();
            if (!$cart) {
                return $this->errorResponse('Cart not found', 404);
            }

            // Delete all cart items
            CartItem::where('cart_id', $cart->id)->delete();

            DB::commit();

            return $this->successResponse('Cart cleared successfully', null);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Failed to clear cart', $e->getMessage(), 500);
        }
    }
}
