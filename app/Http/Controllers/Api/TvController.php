<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TvResource;
use App\Http\Traits\ApiResponse;
use App\Models\Tv;
use App\Models\TvCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TvController extends Controller
{
    use ApiResponse;

    /**
     * Display a listing of TVs with filtering (resource, no pagination).
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Tv::with('tvCategory');

            // Filter by category
            if ($request->has('category_id') && !empty($request->category_id)) {
                $query->where('tv_category_id', $request->category_id);
            }

            // Filter by status
            if ($request->has('status') && !empty($request->status)) {
                $query->where('status', $request->status);
            }

            // Search functionality
            if ($request->has('search') && !empty($request->search)) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                      ->orWhereHas('tvCategory', function ($categoryQuery) use ($search) {
                          $categoryQuery->where('name', 'like', "%{$search}%");
                      });
                });
            }

            // Sorting
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // No pagination, return full collection using resource
            $tvs = $query->get();

            return $this->successResponse('TVs retrieved successfully', TvResource::collection($tvs));
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve TVs', $e->getMessage(), 500);
        }
    }

    /**
     * Display the specified TV.
     */
    public function show(Tv $tv): JsonResponse
    {
        try {
            $tv->load('tvCategory');

            return $this->successResponse('TV retrieved successfully', $tv);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve TV', $e->getMessage(), 500);
        }
    }

    /**
     * Get TVs by category.
     */
    public function byCategory(TvCategory $category): JsonResponse
    {
        try {
            $tvs = $category->activeTvs()
                ->orderBy('created_at', 'desc')
                ->get();

            return $this->successResponse('TVs retrieved successfully', $tvs);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve TVs', $e->getMessage(), 500);
        }
    }

    /**
     * Get all TV categories with their active TVs.
     */
    public function categories(): JsonResponse
    {
        try {
            $categories = TvCategory::active()
                ->with(['activeTvs' => function ($query) {
                    $query->orderBy('created_at', 'desc');
                }])
                ->orderBy('name')
                ->get();

            return $this->successResponse('TV categories retrieved successfully', $categories);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve TV categories', $e->getMessage(), 500);
        }
    }
}