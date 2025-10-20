<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CategoryRequest;
use App\Http\Traits\ApiResponse;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CategoryController extends Controller
{
    use ApiResponse;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Category::query();

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
            $categories = $query->orderBy('created_at', 'desc')->get();

            return $this->successResponse(
                'Categories retrieved successfully',
                $categories
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Failed to retrieve categories',
                $e->getMessage(),
                500
            );
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CategoryRequest $request): JsonResponse
    {
        DB::beginTransaction();
        
        try {
            $category = Category::create($request->validated());

            DB::commit();

            return $this->successResponse(
                'Category created successfully',
                $category,
                201
            );
        } catch (\Exception $e) {
            DB::rollBack();
            
            return $this->errorResponse(
                'Failed to create category',
                $e->getMessage(),
                500
            );
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category): JsonResponse
    {
        try {
            $category->load('products');

            return $this->successResponse(
                'Category retrieved successfully',
                $category
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Failed to retrieve category',
                $e->getMessage(),
                500
            );
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CategoryRequest $request, Category $category): JsonResponse
    {
        DB::beginTransaction();
        
        try {
            $category->update($request->validated());

            DB::commit();

            return $this->successResponse(
                'Category updated successfully',
                $category
            );
        } catch (\Exception $e) {
            DB::rollBack();
            
            return $this->errorResponse(
                'Failed to update category',
                $e->getMessage(),
                500
            );
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category): JsonResponse
    {
        DB::beginTransaction();
        
        try {
            // Check if category has products
            if ($category->products()->count() > 0) {
                return $this->errorResponse(
                    'Cannot delete category',
                    'Category has associated products',
                    422
                );
            }

            $category->delete();

            DB::commit();

            return $this->successResponse(
                'Category deleted successfully'
            );
        } catch (\Exception $e) {
            DB::rollBack();
            
            return $this->errorResponse(
                'Failed to delete category',
                $e->getMessage(),
                500
            );
        }
    }
}
