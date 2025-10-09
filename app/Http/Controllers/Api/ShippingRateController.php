<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreShippingRateRequest;
use App\Http\Requests\UpdateShippingRateRequest;
use App\Http\Traits\ApiResponse;
use App\Models\ShippingRate;
use App\Models\Province;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Http\Controllers\Controller;

class ShippingRateController extends Controller
{
    use ApiResponse;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = ShippingRate::with('province');

            // Filter by province
            if ($request->has('province_id') && !empty($request->province_id)) {
                $query->where('province_id', $request->province_id);
            }

            // Filter by courier
            if ($request->has('courier') && !empty($request->courier)) {
                $query->where('courier', 'like', '%' . $request->courier . '%');
            }

            // Search functionality
            if ($request->has('search') && !empty($request->search)) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('courier', 'like', "%{$search}%")
                      ->orWhereHas('province', function ($provinceQuery) use ($search) {
                          $provinceQuery->where('name', 'like', "%{$search}%");
                      });
                });
            }

            // Sorting
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // Pagination
            $perPage = $request->get('per_page', 15);
            $shippingRates = $query->paginate($perPage);

            return $this->successResponse('Shipping rates retrieved successfully', $shippingRates);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve shipping rates', $e->getMessage(), 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreShippingRateRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $shippingRate = ShippingRate::create($request->validated());
            $shippingRate->load('province');

            DB::commit();

            return $this->successResponse('Shipping rate created successfully', $shippingRate, 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Failed to create shipping rate', $e->getMessage(), 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        try {
            $shippingRate = ShippingRate::with('province')->findOrFail($id);

            return $this->successResponse('Shipping rate retrieved successfully', $shippingRate);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->errorResponse('Shipping rate not found', null, 404);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve shipping rate', $e->getMessage(), 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateShippingRateRequest $request, string $id): JsonResponse
    {
        try {
            $shippingRate = ShippingRate::findOrFail($id);

            DB::beginTransaction();

            $shippingRate->update($request->validated());
            $shippingRate->load('province');

            DB::commit();

            return $this->successResponse('Shipping rate updated successfully', $shippingRate);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            return $this->errorResponse('Shipping rate not found', null, 404);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Failed to update shipping rate', $e->getMessage(), 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $shippingRate = ShippingRate::findOrFail($id);

            DB::beginTransaction();

            $shippingRate->delete();

            DB::commit();

            return $this->successResponse('Shipping rate deleted successfully');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            return $this->errorResponse('Shipping rate not found', null, 404);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Failed to delete shipping rate', $e->getMessage(), 500);
        }
    }
}
