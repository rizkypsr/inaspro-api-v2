<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TvCategoryResource;
use App\Http\Traits\ApiResponse;
use App\Models\TvCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TvCategoryController extends Controller
{
    use ApiResponse;

    /**
     * Display a listing of TV categories.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = TvCategory::withCount('tvs')
                ->with(['tvs' => function ($q) {
                    $q->where('status', 'active')->orderBy('created_at', 'desc');
                }]);

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

            // Sorting
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // Pagination
            $perPage = $request->get('per_page', 15);
            $categories = $query->paginate($perPage);

            return $this->successResponse(
                'TV categories retrieved successfully',
                TvCategoryResource::collection($categories)
            );
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve TV categories', $e->getMessage(), 500);
        }
    }

    /**
     * Display the specified TV category.
     */
    public function show(TvCategory $tvCategory): JsonResponse
    {
        try {
            $tvCategory->loadCount('tvs');
            $tvCategory->load(['tvs' => function ($query) {
                $query->where('status', 'active')->orderBy('created_at', 'desc');
            }]);

            return $this->successResponse('TV category retrieved successfully', $tvCategory);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve TV category', $e->getMessage(), 500);
        }
    }

    /**
     * Get active TV categories for dropdown/select options.
     */
    public function active(): JsonResponse
    {
        try {
            $categories = TvCategory::active()
                ->orderBy('name')
                ->get(['id', 'name']);

            return $this->successResponse('Active TV categories retrieved successfully', $categories);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve active TV categories', $e->getMessage(), 500);
        }
    }
}