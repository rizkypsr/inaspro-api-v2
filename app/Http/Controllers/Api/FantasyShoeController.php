<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\FantasyShoeResource;
use App\Models\FantasyShoe;
use App\Models\FantasyEvent;
use App\Models\FantasyShoeSize;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class FantasyShoeController extends Controller
{
    /**
     * Display shoes for a specific event.
     */
    public function index(Request $request, FantasyEvent $fantasyEvent): JsonResponse
    {
        $query = $fantasyEvent->shoes()
            ->with(['sizes']);

        // Search by name
        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Filter by availability
        if ($request->has('available_only') && $request->available_only) {
            $query->whereHas('sizes', function ($q) {
                $q->whereRaw('stock > reserved_stock');
            });
        }

        $shoes = $query->orderBy('name')->get();

        return response()->json([
            'success' => true,
            'data' => FantasyShoeResource::collection($shoes),
        ]);
    }

    /**
     * Display the specified shoe.
     */
    public function show(FantasyEvent $fantasyEvent, FantasyShoe $fantasyShoe): JsonResponse
    {
        // Ensure shoe belongs to the event
        if ($fantasyShoe->fantasy_event_id !== $fantasyEvent->id) {
            return response()->json([
                'success' => false,
                'message' => 'Shoe not found in this event',
            ], 404);
        }

        $fantasyShoe->load(['sizes' => function ($query) {
            $query->selectRaw('*, (stock - reserved_stock) as available_stock')
                  ->withCount('registrationItems')
                  ->orderBy('size');
        }]);

        // Add statistics
        $fantasyShoe->statistics = [
            'total_sizes' => $fantasyShoe->sizes->count(),
            'available_sizes' => $fantasyShoe->sizes->where('available_stock', '>', 0)->count(),
            'total_stock' => $fantasyShoe->sizes->sum('stock'),
            'available_stock' => $fantasyShoe->sizes->sum('available_stock'),
            'reserved_stock' => $fantasyShoe->sizes->sum('reserved_stock'),
            'sold_items' => $fantasyShoe->sizes->sum('registration_items_count'),
        ];

        // Transform sizes to include availability
        $fantasyShoe->sizes->transform(function ($size) {
            $size->is_available = $size->available_stock > 0;
            return $size;
        });

        return response()->json([
            'success' => true,
            'data' => $fantasyShoe,
        ]);
    }

    /**
     * Get shoe sizes for a specific shoe.
     */
    public function sizes(FantasyEvent $fantasyEvent, FantasyShoe $fantasyShoe): JsonResponse
    {
        // Ensure shoe belongs to the event
        if ($fantasyShoe->fantasy_event_id !== $fantasyEvent->id) {
            return response()->json([
                'success' => false,
                'message' => 'Shoe not found in this event',
            ], 404);
        }

        $sizes = $fantasyShoe->sizes()
            ->selectRaw('*, (stock - reserved_stock) as available_stock')
            ->withCount('registrationItems')
            ->orderBy('size')
            ->get()
            ->map(function ($size) {
                return [
                    'id' => $size->id,
                    'size' => $size->size,
                    'stock' => $size->stock,
                    'reserved_stock' => $size->reserved_stock,
                    'available_stock' => $size->available_stock,
                    'sold_count' => $size->registration_items_count,
                    'is_available' => $size->available_stock > 0,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $sizes,
        ]);
    }

    /**
     * Get shoe availability summary.
     */
    public function availability(FantasyEvent $fantasyEvent): JsonResponse
    {
        $shoes = $fantasyEvent->shoes()
            ->with(['sizes' => function ($query) {
                $query->selectRaw('*, (stock - reserved_stock) as available_stock');
            }])
            ->get()
            ->map(function ($shoe) {
                $totalStock = $shoe->sizes->sum('stock');
                $availableStock = $shoe->sizes->sum('available_stock');
                
                return [
                    'id' => $shoe->id,
                    'name' => $shoe->name,
                    'price' => $shoe->price,
                    'total_stock' => $totalStock,
                    'available_stock' => $availableStock,
                    'reserved_stock' => $shoe->sizes->sum('reserved_stock'),
                    'is_available' => $availableStock > 0,
                    'sizes_count' => $shoe->sizes->count(),
                    'available_sizes_count' => $shoe->sizes->where('available_stock', '>', 0)->count(),
                ];
            });

        $summary = [
            'total_shoes' => $shoes->count(),
            'available_shoes' => $shoes->where('is_available', true)->count(),
            'out_of_stock_shoes' => $shoes->where('is_available', false)->count(),
            'total_stock' => $shoes->sum('total_stock'),
            'available_stock' => $shoes->sum('available_stock'),
            'reserved_stock' => $shoes->sum('reserved_stock'),
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'summary' => $summary,
                'shoes' => $shoes,
            ],
        ]);
    }

    /**
     * Check size availability.
     */
    public function checkAvailability(Request $request, FantasyEvent $fantasyEvent): JsonResponse
    {
        $validated = $request->validate([
            'shoe_size_ids' => 'required|array',
            'shoe_size_ids.*' => 'exists:fantasy_shoe_sizes,id',
        ]);

        $sizeIds = $validated['shoe_size_ids'];
        
        $sizes = FantasyShoeSize::whereIn('id', $sizeIds)
            ->whereHas('fantasyShoe', function ($query) use ($fantasyEvent) {
                $query->where('fantasy_event_id', $fantasyEvent->id);
            })
            ->selectRaw('*, (stock - reserved_stock) as available_stock')
            ->with('fantasyShoe:id,name,price')
            ->get()
            ->map(function ($size) {
                return [
                    'id' => $size->id,
                    'size' => $size->size,
                    'shoe_name' => $size->fantasyShoe->name,
                    'price' => $size->fantasyShoe->price,
                    'available_stock' => $size->available_stock,
                    'is_available' => $size->available_stock > 0,
                ];
            });

        $allAvailable = $sizes->every(function ($size) {
            return $size['is_available'];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'all_available' => $allAvailable,
                'sizes' => $sizes,
                'total_price' => $sizes->sum('price'),
            ],
        ]);
    }

    /**
     * Get popular shoe sizes.
     */
    public function popularSizes(FantasyEvent $fantasyEvent): JsonResponse
    {
        $popularSizes = FantasyShoeSize::whereHas('fantasyShoe', function ($query) use ($fantasyEvent) {
                $query->where('fantasy_event_id', $fantasyEvent->id);
            })
            ->withCount('registrationItems')
            ->with('fantasyShoe:id,name')
            ->orderBy('registration_items_count', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($size) {
                return [
                    'shoe_name' => $size->fantasyShoe->name,
                    'size' => $size->size,
                    'orders_count' => $size->registration_items_count,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $popularSizes,
        ]);
    }
}