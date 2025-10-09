<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductVariantRequest;
use App\Http\Traits\ApiResponse;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProductVariantController extends Controller
{
    use ApiResponse;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $query = ProductVariant::with(['product']);

            // Filter by product_id
            if ($request->has('product_id')) {
                $query->where('product_id', $request->product_id);
            }

            // Filter by status
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            // Search functionality
            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('sku', 'like', "%{$search}%")
                      ->orWhere('variant_name', 'like', "%{$search}%");
                });
            }

            // Sorting
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // Pagination
            $perPage = $request->get('per_page', 15);
            $variants = $query->paginate($perPage);

            return $this->successResponse($variants, 'Product variants retrieved successfully');
        } catch (\Exception $e) {
            Log::error('Error retrieving product variants: ' . $e->getMessage());
            return $this->errorResponse('Failed to retrieve product variants', 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ProductVariantRequest $request)
    {
        try {
            DB::beginTransaction();

            $variant = ProductVariant::create($request->validated());

            DB::commit();

            return $this->successResponse($variant->load('product'), 'Product variant created successfully', 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating product variant: ' . $e->getMessage());
            return $this->errorResponse('Failed to create product variant', 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $variant = ProductVariant::with(['product', 'stockLogs.changedBy'])->findOrFail($id);

            return $this->successResponse($variant, 'Product variant retrieved successfully');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->errorResponse('Product variant not found', 404);
        } catch (\Exception $e) {
            Log::error('Error retrieving product variant: ' . $e->getMessage());
            return $this->errorResponse('Failed to retrieve product variant', 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ProductVariantRequest $request, string $id)
    {
        try {
            DB::beginTransaction();

            $variant = ProductVariant::findOrFail($id);
            $variant->update($request->validated());

            DB::commit();

            return $this->successResponse($variant->load('product'), 'Product variant updated successfully');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            return $this->errorResponse('Product variant not found', 404);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating product variant: ' . $e->getMessage());
            return $this->errorResponse('Failed to update product variant', 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            DB::beginTransaction();

            $variant = ProductVariant::findOrFail($id);

            // Check if variant has stock logs
            if ($variant->stockLogs()->exists()) {
                return $this->errorResponse('Cannot delete product variant with existing stock logs', 400);
            }

            $variant->delete();

            DB::commit();

            return $this->successResponse(null, 'Product variant deleted successfully');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            return $this->errorResponse('Product variant not found', 404);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting product variant: ' . $e->getMessage());
            return $this->errorResponse('Failed to delete product variant', 500);
        }
    }
}
