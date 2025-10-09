<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use App\Models\ProductVoucher;
use App\Models\Product;
use App\Http\Requests\StoreProductVoucherRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProductVoucherController extends Controller
{
    use ApiResponse;

    /**
     * Display a listing of product vouchers.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = ProductVoucher::with('product');

            // Filter by product
            if ($request->has('product_id')) {
                $query->where('product_id', $request->product_id);
            }

            // Filter by status
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            // Filter active vouchers only
            if ($request->boolean('active_only')) {
                $query->where('status', 'active')
                    ->where('start_date', '<=', now())
                    ->where('end_date', '>=', now());
            }

            $vouchers = $query->orderBy('created_at', 'desc')
                ->paginate($request->get('per_page', 15));

            return $this->successResponse('Product vouchers retrieved successfully', $vouchers);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve product vouchers', $e->getMessage(), 500);
        }
    }

    /**
     * Store a newly created product voucher.
     */
    public function store(StoreProductVoucherRequest $request): JsonResponse
    {
        DB::beginTransaction();

        try {
            $voucher = ProductVoucher::create($request->all());
            $voucher->load('product');

            DB::commit();

            return $this->successResponse('Product voucher created successfully', $voucher, 201);

        } catch (ValidationException $e) {
            DB::rollBack();
            return $this->validationErrorResponse('Validation failed', $e->errors());
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Failed to create product voucher', $e->getMessage(), 500);
        }
    }

    /**
     * Display the specified product voucher.
     */
    public function show(string $id): JsonResponse
    {
        try {
            $voucher = ProductVoucher::with('product')->findOrFail($id);

            return $this->successResponse('Product voucher retrieved successfully', $voucher);
        } catch (\Exception $e) {
            return $this->errorResponse('Product voucher not found', null, 404);
        }
    }

    /**
     * Update the specified product voucher.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'product_id' => 'sometimes|exists:products,id',
            'code' => 'sometimes|string|max:50|unique:product_vouchers,code,' . $id,
            'discount_amount' => 'nullable|numeric|min:0',
            'discount_percent' => 'nullable|numeric|min:0|max:100',
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date|after:start_date',
            'status' => 'sometimes|string|in:active,inactive',
        ]);

        DB::beginTransaction();

        try {
            $voucher = ProductVoucher::findOrFail($id);

            // Validate discount logic if being updated
            if ($request->has('discount_amount') || $request->has('discount_percent')) {
                $discountAmount = $request->get('discount_amount', $voucher->discount_amount);
                $discountPercent = $request->get('discount_percent', $voucher->discount_percent);

                if (!$discountAmount && !$discountPercent) {
                    throw ValidationException::withMessages([
                        'discount' => 'Either discount_amount or discount_percent must be provided'
                    ]);
                }

                if ($discountAmount && $discountPercent) {
                    throw ValidationException::withMessages([
                        'discount' => 'Cannot provide both discount_amount and discount_percent'
                    ]);
                }
            }

            $voucher->update($request->all());
            $voucher->load('product');

            DB::commit();

            return $this->successResponse('Product voucher updated successfully', $voucher);

        } catch (ValidationException $e) {
            DB::rollBack();
            return $this->validationErrorResponse('Validation failed', $e->errors());
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Failed to update product voucher', $e->getMessage(), 500);
        }
    }

    /**
     * Remove the specified product voucher (soft delete).
     */
    public function destroy(string $id): JsonResponse
    {
        DB::beginTransaction();

        try {
            $voucher = ProductVoucher::findOrFail($id);
            $voucher->delete();

            DB::commit();

            return $this->successResponse('Product voucher deleted successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Failed to delete product voucher', $e->getMessage(), 500);
        }
    }

    /**
     * Validate a product voucher code for a specific product.
     */
    public function validate(Request $request): JsonResponse
    {
        $request->validate([
            'code' => 'required|string',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        try {
            $voucher = ProductVoucher::where('code', $request->code)
                ->where('product_id', $request->product_id)
                ->where('status', 'active')
                ->where('start_date', '<=', now())
                ->where('end_date', '>=', now())
                ->with('product')
                ->first();

            if (!$voucher) {
                return $this->errorResponse('Invalid or expired voucher code for this product', null, 404);
            }

            // Get product price (assuming you have a price field or variant system)
            $product = $voucher->product;
            
            // For this example, let's assume we get price from product variants or a base price
            // You might need to adjust this based on your actual product structure
            $productPrice = $product->price ?? 0; // Adjust based on your product model
            $itemTotal = $productPrice * $request->quantity;

            // Calculate discount
            $discount = 0;
            if ($voucher->discount_amount) {
                $discount = $voucher->discount_amount;
            } elseif ($voucher->discount_percent) {
                $discount = $itemTotal * ($voucher->discount_percent / 100);
            }

            $responseData = [
                'voucher' => $voucher,
                'product' => $product,
                'original_amount' => $itemTotal,
                'discount_amount' => $discount,
                'final_amount' => $itemTotal - $discount,
            ];

            return $this->successResponse('Product voucher is valid', $responseData);

        } catch (\Exception $e) {
            return $this->errorResponse('Failed to validate product voucher', $e->getMessage(), 500);
        }
    }

    /**
     * Get voucher usage statistics for a specific product voucher.
     */
    public function usage(string $id): JsonResponse
    {
        try {
            $voucher = ProductVoucher::with([
                'product',
                'orders' => function ($query) {
                    $query->select('orders.id', 'orders.created_at', 'orders.total_amount')
                        ->withPivot(['discount_amount']);
                }
            ])->findOrFail($id);

            $usageStats = [
                'voucher' => $voucher,
                'product' => $voucher->product,
                'total_uses' => $voucher->orders->count(),
                'total_discount_given' => $voucher->orders->sum('pivot.discount_amount'),
                'recent_uses' => $voucher->orders->take(10),
            ];

            return $this->successResponse('Product voucher usage retrieved successfully', $usageStats);

        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve product voucher usage', $e->getMessage(), 500);
        }
    }

    /**
     * Get all vouchers for a specific product.
     */
    public function byProduct(string $productId): JsonResponse
    {
        try {
            $product = Product::findOrFail($productId);
            
            $vouchers = ProductVoucher::where('product_id', $productId)
                ->where('status', 'active')
                ->where('start_date', '<=', now())
                ->where('end_date', '>=', now())
                ->orderBy('created_at', 'desc')
                ->get();

            $responseData = [
                'product' => $product,
                'vouchers' => $vouchers,
                'voucher_count' => $vouchers->count(),
            ];

            return $this->successResponse('Product vouchers retrieved successfully', $responseData);

        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve product vouchers', $e->getMessage(), 500);
        }
    }
}
