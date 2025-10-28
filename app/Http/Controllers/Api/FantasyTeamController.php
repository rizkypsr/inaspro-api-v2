<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\FantasyTeamResource;
use App\Models\FantasyEventTeam;
use App\Models\FantasyEvent;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class FantasyTeamController extends Controller
{
    /**
     * Display teams for a specific event.
     */
    public function index(Request $request, FantasyEvent $fantasyEvent): JsonResponse
    {
        $query = $fantasyEvent->teams()
            ->withCount('registrations')
            ->with(['tshirtOptions']);

        // Search by name
        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Filter by availability
        if ($request->has('available_only') && $request->available_only) {
            $query->whereRaw('slot_limit > (SELECT COUNT(*) FROM fantasy_registrations WHERE fantasy_event_team_id = fantasy_event_teams.id)');
        }

        $teams = $query->orderBy('name')->get();

        return response()->json([
            'success' => true,
            'data' => FantasyTeamResource::collection($teams),
        ]);
    }

    /**
     * Display the specified team.
     */
    public function show(FantasyEvent $fantasyEvent, FantasyEventTeam $fantasyEventTeam): JsonResponse
    {
        // Ensure team belongs to the event
        if ($fantasyEventTeam->fantasy_event_id !== $fantasyEvent->id) {
            return response()->json([
                'success' => false,
                'message' => 'Team not found in this event',
            ], 404);
        }

        $fantasyEventTeam->load([
            'tshirtOptions',
            'registrations.user:id,name,email',
            'registrations.registrationItems.fantasyTshirtOption',
            'registrations.registrationItems.fantasyShoeSize.fantasyShoe',
        ]);

        // Add statistics
        $fantasyEventTeam->statistics = [
            'total_registrations' => $fantasyEventTeam->registrations->count(),
            'available_slots' => $fantasyEventTeam->slot_limit - $fantasyEventTeam->registrations->count(),
            'is_full' => ($fantasyEventTeam->slot_limit - $fantasyEventTeam->registrations->count()) <= 0,
            'tshirt_distribution' => $fantasyEventTeam->tshirtOptions->map(function ($option) {
                return [
                    'size' => $option->size,
                    'count' => $option->registrationItems->count(),
                ];
            }),
        ];

        return response()->json([
            'success' => true,
            'data' => $fantasyEventTeam,
        ]);
    }

    /**
     * Get team availability summary.
     */
    public function availability(FantasyEvent $fantasyEvent): JsonResponse
    {
        $teams = $fantasyEvent->teams()
            ->withCount('registrations')
            ->get()
            ->map(function ($team) {
                return [
                    'id' => $team->id,
                    'name' => $team->name,
                    'slot_limit' => $team->slot_limit,
                    'registered' => $team->registrations_count,
                    'available' => $team->slot_limit - $team->registrations_count,
                    'is_full' => ($team->slot_limit - $team->registrations_count) <= 0,
                ];
            });

        $summary = [
            'total_teams' => $teams->count(),
            'full_teams' => $teams->where('is_full', true)->count(),
            'available_teams' => $teams->where('is_full', false)->count(),
            'total_slots' => $teams->sum('slot_limit'),
            'occupied_slots' => $teams->sum('registered'),
            'available_slots' => $teams->sum('available'),
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'summary' => $summary,
                'teams' => $teams,
            ],
        ]);
    }

    /**
     * Get team members.
     */
    public function members(FantasyEvent $fantasyEvent, FantasyEventTeam $fantasyEventTeam): JsonResponse
    {
        // Ensure team belongs to the event
        if ($fantasyEventTeam->fantasy_event_id !== $fantasyEvent->id) {
            return response()->json([
                'success' => false,
                'message' => 'Team not found in this event',
            ], 404);
        }

        $members = $fantasyEventTeam->registrations()
            ->with([
                'user:id,name,email,phone',
                'registrationItems.fantasyTshirtOption',
                'registrationItems.fantasyShoeSize.fantasyShoe',
                'payments'
            ])
            ->where('status', 'confirmed')
            ->get()
            ->map(function ($registration) {
                return [
                    'registration_id' => $registration->id,
                    'registration_code' => $registration->registration_code,
                    'user' => $registration->user,
                    'items' => $registration->registrationItems->map(function ($item) {
                        if ($item->fantasyTshirtOption) {
                            return [
                                'type' => 'tshirt',
                                'size' => $item->fantasyTshirtOption->size,
                            ];
                        } elseif ($item->fantasyShoeSize) {
                            return [
                                'type' => 'shoe',
                                'shoe_name' => $item->fantasyShoeSize->fantasyShoe->name,
                                'size' => $item->fantasyShoeSize->size,
                            ];
                        }
                        return null;
                    })->filter(),
                    'payment_status' => $registration->payments->where('status', 'approved')->count() > 0 ? 'paid' : 'unpaid',
                    'registered_at' => $registration->created_at,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => [
                'team' => $fantasyEventTeam->only(['id', 'name', 'slot_limit']),
                'members' => $members,
                'statistics' => [
                    'total_members' => $members->count(),
                    'paid_members' => $members->where('payment_status', 'paid')->count(),
                    'unpaid_members' => $members->where('payment_status', 'unpaid')->count(),
                ],
            ],
        ]);
    }

    /**
     * Get T-shirt options for a team.
     */
    public function tshirtOptions(FantasyEvent $fantasyEvent, FantasyEventTeam $fantasyEventTeam): JsonResponse
    {
        // Ensure team belongs to the event
        if ($fantasyEventTeam->fantasy_event_id !== $fantasyEvent->id) {
            return response()->json([
                'success' => false,
                'message' => 'Team not found in this event',
            ], 404);
        }

        $options = $fantasyEventTeam->tshirtOptions()
            ->withCount('registrationItems')
            ->get()
            ->map(function ($option) {
                return [
                    'id' => $option->id,
                    'size' => $option->size,
                    'selected_count' => $option->registration_items_count,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $options,
        ]);
    }
}