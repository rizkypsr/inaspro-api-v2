<?php

namespace App\Http\Controllers;

use App\Models\ShippingRate;
use App\Models\Province;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LogisticsController extends Controller
{
    /**
     * Display a listing of the logistics.
     */
    public function index(Request $request): Response
    {
        $query = ShippingRate::with('province');

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

        // Filter by province
        if ($request->has('province_id') && !empty($request->province_id)) {
            $query->where('province_id', $request->province_id);
        }

        // Filter by courier
        if ($request->has('courier') && !empty($request->courier)) {
            $query->where('courier', 'like', '%' . $request->courier . '%');
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $shippingRates = $query->paginate(10)->withQueryString();
        $provinces = Province::orderBy('name')->get();

        return Inertia::render('admin/marketplace/logistics', [
            'shippingRates' => $shippingRates,
            'provinces' => $provinces,
            'filters' => $request->only(['search', 'province_id', 'courier', 'sort_by', 'sort_order']),
        ]);
    }

    /**
     * Show the form for creating a new logistics entry.
     */
    public function create(): Response
    {
        $provinces = Province::orderBy('name')->get();

        return Inertia::render('admin/marketplace/logistics/create', [
            'provinces' => $provinces,
        ]);
    }

    /**
     * Store a newly created logistics entry in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'province_id' => 'required|exists:provinces,id',
            'courier' => 'required|string|max:50',
            'rate' => 'required|numeric|min:0|max:999999999.99',
        ], [
            'province_id.required' => 'Province is required.',
            'province_id.exists' => 'Selected province does not exist.',
            'courier.required' => 'Courier name is required.',
            'courier.string' => 'Courier name must be a string.',
            'courier.max' => 'Courier name must not exceed 50 characters.',
            'rate.required' => 'Shipping rate is required.',
            'rate.numeric' => 'Shipping rate must be a number.',
            'rate.min' => 'Shipping rate must be at least 0.',
            'rate.max' => 'Shipping rate is too large.',
        ]);

        ShippingRate::create($validated);

        return redirect()->route('admin.marketplace.logistics.index')
            ->with('success', 'Shipping rate created successfully.');
    }

    /**
     * Display the specified logistics entry.
     */
    public function show(ShippingRate $shippingRate): Response
    {
        $shippingRate->load('province');

        return Inertia::render('admin/marketplace/logistics/show', [
            'shippingRate' => $shippingRate,
        ]);
    }

    /**
     * Show the form for editing the specified logistics entry.
     */
    public function edit(ShippingRate $shippingRate): Response
    {
        $shippingRate->load('province');
        $provinces = Province::orderBy('name')->get();

        return Inertia::render('admin/marketplace/logistics/edit', [
            'shippingRate' => $shippingRate,
            'provinces' => $provinces,
        ]);
    }

    /**
     * Update the specified logistics entry in storage.
     */
    public function update(Request $request, ShippingRate $shippingRate)
    {
        $validated = $request->validate([
            'province_id' => 'required|exists:provinces,id',
            'courier' => 'required|string|max:50',
            'rate' => 'required|numeric|min:0|max:999999999.99',
        ], [
            'province_id.required' => 'Province is required.',
            'province_id.exists' => 'Selected province does not exist.',
            'courier.required' => 'Courier name is required.',
            'courier.string' => 'Courier name must be a string.',
            'courier.max' => 'Courier name must not exceed 50 characters.',
            'rate.required' => 'Shipping rate is required.',
            'rate.numeric' => 'Shipping rate must be a number.',
            'rate.min' => 'Shipping rate must be at least 0.',
            'rate.max' => 'Shipping rate is too large.',
        ]);

        $shippingRate->update($validated);

        return redirect()->route('admin.marketplace.logistics.index')
            ->with('success', 'Shipping rate updated successfully.');
    }

    /**
     * Remove the specified logistics entry from storage.
     */
    public function destroy(ShippingRate $shippingRate)
    {
        $shippingRate->delete();

        return redirect()->route('admin.marketplace.logistics.index')
            ->with('success', 'Shipping rate deleted successfully.');
    }

    /**
     * Remove multiple logistics entries from storage.
     */
    public function bulkDestroy(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:shipping_rates,id',
        ]);

        ShippingRate::whereIn('id', $validated['ids'])->delete();

        return redirect()->route('admin.marketplace.logistics.index')
            ->with('success', 'Selected shipping rates deleted successfully.');
    }
}