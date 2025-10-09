<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use App\Models\GlobalVoucher;
use App\Http\Requests\StoreGlobalVoucherRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class GlobalVoucherController extends Controller
{
    use ApiResponse;

    /**
     * Display a listing of global vouchers.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = GlobalVoucher::query();

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

            return $this->successResponse('Global vouchers retrieved successfully', $vouchers);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve global vouchers', $e->getMessage(), 500);
        }
    }

    /**
     * Store a newly created global voucher.
     */
    public function store(StoreGlobalVoucherRequest $request): JsonResponse
    {
        DB::beginTransaction();

        try {
            $voucher = GlobalVoucher::create($request->all());

            DB::commit();

            return $this->successResponse('Global voucher created successfully', $voucher, 201);

        } catch (ValidationException $e) {
            DB::rollBack();
            return $this->validationErrorResponse('Validation failed', $e->errors());
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Failed to create global voucher', $e->getMessage(), 500);
        }
    }

    /**
     * Display the specified global voucher.
     */
    public function show(string $id): JsonResponse
    {
        try {
            $voucher = GlobalVoucher::findOrFail($id);

            return $this->successResponse('Global voucher retrieved successfully', $voucher);
        } catch (\Exception $e) {
            return $this->errorResponse('Global voucher not found', null, 404);
        }
    }

    /**
     * Update the specified global voucher.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'code' => 'sometimes|string|max:50|unique:global_vouchers,code,' . $id,
            'discount_amount' => 'nullable|numeric|min:0',
            'discount_percent' => 'nullable|numeric|min:0|max:100',
            'min_order_amount' => 'nullable|numeric|min:0',
            'max_discount_amount' => 'nullable|numeric|min:0',
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date|after:start_date',
            'status' => 'sometimes|string|in:active,inactive',
        ]);

        DB::beginTransaction();

        try {
            $voucher = GlobalVoucher::findOrFail($id);

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

            DB::commit();

            return $this->successResponse('Global voucher updated successfully', $voucher);

        } catch (ValidationException $e) {
            DB::rollBack();
            return $this->validationErrorResponse('Validation failed', $e->errors());
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Failed to update global voucher', $e->getMessage(), 500);
        }
    }

    /**
     * Remove the specified global voucher (soft delete).
     */
    public function destroy(string $id): JsonResponse
    {
        DB::beginTransaction();

        try {
            $voucher = GlobalVoucher::findOrFail($id);
            $voucher->delete();

            DB::commit();

            return $this->successResponse('Global voucher deleted successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Failed to delete global voucher', $e->getMessage(), 500);
        }
    }

    /**
     * Validate a global voucher code.
     */
    public function validate(Request $request): JsonResponse
    {
        $request->validate([
            'code' => 'required|string',
            'order_amount' => 'required|numeric|min:0',
        ]);

        try {
            $voucher = GlobalVoucher::where('code', $request->code)
                ->where('status', 'active')
                ->where('start_date', '<=', now())
                ->where('end_date', '>=', now())
                ->first();

            if (!$voucher) {
                return $this->errorResponse('Invalid or expired voucher code', null, 404);
            }

            // Check minimum order amount
            if ($voucher->min_order_amount && $request->order_amount < $voucher->min_order_amount) {
                return $this->errorResponse(
                    "Minimum order amount of {$voucher->min_order_amount} required for this voucher",
                    null,
                    400
                );
            }

            // Calculate discount
            $discount = 0;
            if ($voucher->discount_amount) {
                $discount = $voucher->discount_amount;
            } elseif ($voucher->discount_percent) {
                $discount = $request->order_amount * ($voucher->discount_percent / 100);
                
                // Apply max discount limit
                if ($voucher->max_discount_amount) {
                    $discount = min($discount, $voucher->max_discount_amount);
                }
            }

            $responseData = [
                'voucher' => $voucher,
                'discount_amount' => $discount,
                'final_amount' => $request->order_amount - $discount,
            ];

            return $this->successResponse('Voucher is valid', $responseData);

        } catch (\Exception $e) {
            return $this->errorResponse('Failed to validate voucher', $e->getMessage(), 500);
        }
    }

    /**
     * Get voucher usage statistics.
     */
    public function usage(string $id): JsonResponse
    {
        try {
            $voucher = GlobalVoucher::with(['orders' => function ($query) {
                $query->select('orders.id', 'orders.created_at', 'orders.total_amount')
                    ->withPivot(['discount_amount']);
            }])->findOrFail($id);

            $usageStats = [
                'voucher' => $voucher,
                'total_uses' => $voucher->orders->count(),
                'total_discount_given' => $voucher->orders->sum('pivot.discount_amount'),
                'recent_uses' => $voucher->orders->take(10),
            ];

            return $this->successResponse('Voucher usage retrieved successfully', $usageStats);

        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve voucher usage', $e->getMessage(), 500);
        }
    }
}
