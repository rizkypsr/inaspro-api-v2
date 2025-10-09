<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductStockLogRequest;
use App\Http\Traits\ApiResponse;
use App\Models\ProductStockLog;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProductStockLogController extends Controller
{
    use ApiResponse;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $query = ProductStockLog::with(['productVariant.product', 'changedBy']);

            // Filter by product_variant_id
            if ($request->has('product_variant_id')) {
                $query->where('product_variant_id', $request->product_variant_id);
            }

            // Filter by change_type
            if ($request->has('change_type')) {
                $query->where('change_type', $request->change_type);
            }

            // Filter by date range
            if ($request->has('date_from')) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }

            if ($request->has('date_to')) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }

            // Sorting
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // Pagination
            $perPage = $request->get('per_page', 15);
            $stockLogs = $query->paginate($perPage);

            return $this->successResponse($stockLogs, 'Stock logs retrieved successfully');
        } catch (\Exception $e) {
            Log::error('Error retrieving stock logs: ' . $e->getMessage());
            return $this->errorResponse('Failed to retrieve stock logs', 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ProductStockLogRequest $request)
    {
        try {
            DB::beginTransaction();

            $data = $request->validated();
            $data['changed_by'] = Auth::id();

            // Update product variant stock
            $variant = ProductVariant::findOrFail($data['product_variant_id']);
            
            if ($data['change_type'] === 'in') {
                $variant->stock += $data['quantity'];
            } elseif ($data['change_type'] === 'out') {
                if ($variant->stock < $data['quantity']) {
                    return $this->errorResponse('Insufficient stock available', 400);
                }
                $variant->stock -= $data['quantity'];
            }

            $variant->save();

            // Create stock log
            $stockLog = ProductStockLog::create($data);

            DB::commit();

            return $this->successResponse($stockLog->load(['productVariant.product', 'changedBy']), 'Stock log created successfully', 201);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            return $this->errorResponse('Product variant not found', 404);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating stock log: ' . $e->getMessage());
            return $this->errorResponse('Failed to create stock log', 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $stockLog = ProductStockLog::with(['productVariant.product', 'changedBy'])->findOrFail($id);

            return $this->successResponse($stockLog, 'Stock log retrieved successfully');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->errorResponse('Stock log not found', 404);
        } catch (\Exception $e) {
            Log::error('Error retrieving stock log: ' . $e->getMessage());
            return $this->errorResponse('Failed to retrieve stock log', 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ProductStockLogRequest $request, string $id)
    {
        try {
            DB::beginTransaction();

            $stockLog = ProductStockLog::findOrFail($id);
            $oldQuantity = $stockLog->quantity;
            $oldChangeType = $stockLog->change_type;

            $data = $request->validated();
            $data['changed_by'] = Auth::id();

            // Revert old stock change
            $variant = ProductVariant::findOrFail($stockLog->product_variant_id);
            
            if ($oldChangeType === 'in') {
                $variant->stock -= $oldQuantity;
            } elseif ($oldChangeType === 'out') {
                $variant->stock += $oldQuantity;
            }

            // Apply new stock change
            if ($data['change_type'] === 'in') {
                $variant->stock += $data['quantity'];
            } elseif ($data['change_type'] === 'out') {
                if ($variant->stock < $data['quantity']) {
                    return $this->errorResponse('Insufficient stock available', 400);
                }
                $variant->stock -= $data['quantity'];
            }

            $variant->save();
            $stockLog->update($data);

            DB::commit();

            return $this->successResponse($stockLog->load(['productVariant.product', 'changedBy']), 'Stock log updated successfully');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            return $this->errorResponse('Stock log not found', 404);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating stock log: ' . $e->getMessage());
            return $this->errorResponse('Failed to update stock log', 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            DB::beginTransaction();

            $stockLog = ProductStockLog::findOrFail($id);

            // Revert stock change
            $variant = ProductVariant::findOrFail($stockLog->product_variant_id);
            
            if ($stockLog->change_type === 'in') {
                $variant->stock -= $stockLog->quantity;
            } elseif ($stockLog->change_type === 'out') {
                $variant->stock += $stockLog->quantity;
            }

            $variant->save();
            $stockLog->delete();

            DB::commit();

            return $this->successResponse(null, 'Stock log deleted successfully');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            return $this->errorResponse('Stock log not found', 404);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting stock log: ' . $e->getMessage());
            return $this->errorResponse('Failed to delete stock log', 500);
        }
    }
}
