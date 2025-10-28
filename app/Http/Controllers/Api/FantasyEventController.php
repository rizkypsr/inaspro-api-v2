<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\FantasyEventResource;
use App\Http\Resources\FantasyEventShowResource;
use App\Models\FantasyEvent;
use App\Models\FantasyEventTeam;
use App\Models\FantasyShoe;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class FantasyEventController extends Controller
{
    /**
     * Display a listing of fantasy events.
     */
    public function index(Request $request): JsonResponse
    {
        $query = FantasyEvent::query();

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->has('from_date')) {
            $query->where('play_date', '>=', $request->from_date);
        }

        if ($request->has('to_date')) {
            $query->where('play_date', '<=', $request->to_date);
        }

        // Search by title or location
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%");
            });
        }

        $events = $query->orderBy('play_date', 'desc')
                       ->get();

        return response()->json([
            'success' => true,
            'data' => FantasyEventResource::collection($events),
        ]);
    }

    /**
     * Store a newly created fantasy event.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'location' => 'required|string|max:255',
            'play_date' => 'required|date|after:now',
            'base_fee' => 'required|numeric|min:0',
            'status' => 'sometimes|in:draft,open,closed,finished',
        ]);

        try {
            DB::beginTransaction();

            $event = FantasyEvent::create([
                'title' => $validated['title'],
                'description' => $validated['description'],
                'location' => $validated['location'],
                'play_date' => $validated['play_date'],
                'base_fee' => $validated['base_fee'],
                'status' => $validated['status'] ?? 'draft',
                'created_by' => Auth::id(),
            ]);

            $event->load(['creator', 'teams.tshirtOptions', 'shoes.sizes']);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Fantasy event created successfully',
                'data' => $event,
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to create fantasy event',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified fantasy event.
     */
    public function show(FantasyEvent $fantasyEvent): JsonResponse
    {
        $fantasyEvent->load([
            'teams:id,fantasy_event_id,name,slot_limit'
        ]);

        return response()->json([
            'success' => true,
            'data' => new FantasyEventShowResource($fantasyEvent),
        ]);
    }

    /**
     * Update the specified fantasy event.
     */
    public function update(Request $request, FantasyEvent $fantasyEvent): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'location' => 'sometimes|string|max:255',
            'play_date' => 'sometimes|date|after:now',
            'base_fee' => 'sometimes|numeric|min:0',
            'status' => 'sometimes|in:draft,open,closed,finished',
        ]);

        try {
            $fantasyEvent->update($validated);
            $fantasyEvent->load(['creator', 'teams.tshirtOptions', 'shoes.sizes']);

            return response()->json([
                'success' => true,
                'message' => 'Fantasy event updated successfully',
                'data' => $fantasyEvent,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update fantasy event',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified fantasy event.
     */
    public function destroy(FantasyEvent $fantasyEvent): JsonResponse
    {
        try {
            // Check if there are any confirmed registrations
            $confirmedRegistrations = $fantasyEvent->registrations()
                ->where('status', 'confirmed')
                ->count();

            if ($confirmedRegistrations > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete event with confirmed registrations',
                ], 422);
            }

            $fantasyEvent->delete();

            return response()->json([
                'success' => true,
                'message' => 'Fantasy event deleted successfully',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete fantasy event',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get available teams for an event.
     */
    public function teams(FantasyEvent $fantasyEvent): JsonResponse
    {
        $teams = $fantasyEvent->teams()
            ->withCount('registrations')
            ->with('tshirtOptions')
            ->get()
            ->map(function ($team) {
                $team->available_slots = $team->slot_limit - $team->registrations_count;
                return $team;
            });

        return response()->json([
            'success' => true,
            'data' => $teams,
        ]);
    }

    /**
     * Get available shoes for an event.
     */
    public function shoes(FantasyEvent $fantasyEvent): JsonResponse
    {
        $shoes = $fantasyEvent->shoes()
            ->with(['sizes' => function ($query) {
                $query->selectRaw('*, (stock - reserved_stock) as available_stock');
            }])
            ->get();

        return response()->json([
            'success' => true,
            'data' => $shoes,
        ]);
    }
}