<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductRequest;
use App\Http\Traits\ApiResponse;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    use ApiResponse;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Product::with(['category', 'variants']);

            // Filter by category
            if ($request->has('category_id') && !empty($request->category_id)) {
                $query->where('category_id', $request->category_id);
            }

            // Filter by status
            if ($request->has('status') && !empty($request->status)) {
                $query->where('status', $request->status);
            }

            // Search functionality
            if ($request->has('search') && !empty($request->search)) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }

            // Pagination
            $perPage = $request->get('per_page', 15);
            $products = $query->orderBy('created_at', 'desc')->paginate($perPage);

            return $this->successResponse(
                'Products retrieved successfully',
                $products
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Failed to retrieve products',
                $e->getMessage(),
                500
            );
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ProductRequest $request): JsonResponse
    {
        DB::beginTransaction();
        
        try {
            $product = Product::create($request->validated());
            $product->load(['category', 'variants']);

            DB::commit();

            return $this->successResponse(
                'Product created successfully',
                $product,
                201
            );
        } catch (\Exception $e) {
            DB::rollBack();
            
            return $this->errorResponse(
                'Failed to create product',
                $e->getMessage(),
                500
            );
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product): JsonResponse
    {
        try {
            $product->load(['category', 'variants']);

            return $this->successResponse(
                'Product retrieved successfully',
                $product
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Failed to retrieve product',
                $e->getMessage(),
                500
            );
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ProductRequest $request, Product $product): JsonResponse
    {
        DB::beginTransaction();
        
        try {
            $product->update($request->validated());
            $product->load(['category', 'variants']);

            DB::commit();

            return $this->successResponse(
                'Product updated successfully',
                $product
            );
        } catch (\Exception $e) {
            DB::rollBack();
            
            return $this->errorResponse(
                'Failed to update product',
                $e->getMessage(),
                500
            );
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product): JsonResponse
    {
        DB::beginTransaction();
        
        try {
            // Check if product has variants
            if ($product->variants()->count() > 0) {
                return $this->errorResponse(
                    'Cannot delete product',
                    'Product has associated variants',
                    422
                );
            }

            $product->delete();

            DB::commit();

            return $this->successResponse(
                'Product deleted successfully'
            );
        } catch (\Exception $e) {
            DB::rollBack();
            
            return $this->errorResponse(
                'Failed to delete product',
                $e->getMessage(),
                500
            );
        }
    }
}
