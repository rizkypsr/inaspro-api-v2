<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreProvinceRequest;
use App\Http\Requests\UpdateProvinceRequest;
use App\Http\Traits\ApiResponse;
use App\Models\Province;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Http\Controllers\Controller;

class ProvinceController extends Controller
{
    use ApiResponse;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Province::query();

            // Search functionality
            if ($request->has('search') && !empty($request->search)) {
                $search = $request->search;
                $query->where('name', 'like', "%{$search}%");
            }

            // Sorting
            $sortBy = $request->get('sort_by', 'name');
            $sortOrder = $request->get('sort_order', 'asc');
            $query->orderBy($sortBy, $sortOrder);

            // Pagination
            $perPage = $request->get('per_page', 15);
            $provinces = $query->paginate($perPage);

            return $this->successResponse('Provinces retrieved successfully', $provinces);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve provinces', $e->getMessage(), 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProvinceRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $province = Province::create($request->validated());

            DB::commit();

            return $this->successResponse('Province created successfully', $province, 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Failed to create province', $e->getMessage(), 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        try {
            $province = Province::with('shippingRates')->findOrFail($id);

            return $this->successResponse('Province retrieved successfully', $province);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->errorResponse('Province not found', null, 404);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve province', $e->getMessage(), 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProvinceRequest $request, string $id): JsonResponse
    {
        try {
            $province = Province::findOrFail($id);

            DB::beginTransaction();

            $province->update($request->validated());

            DB::commit();

            return $this->successResponse('Province updated successfully', $province);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            return $this->errorResponse('Province not found', null, 404);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Failed to update province', $e->getMessage(), 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $province = Province::findOrFail($id);

            // Check if province has shipping rates
            if ($province->shippingRates()->exists()) {
                return $this->errorResponse('Cannot delete province with existing shipping rates', null, 400);
            }

            DB::beginTransaction();

            $province->delete();

            DB::commit();

            return $this->successResponse('Province deleted successfully');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            return $this->errorResponse('Province not found', null, 404);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Failed to delete province', $e->getMessage(), 500);
        }
    }
}
