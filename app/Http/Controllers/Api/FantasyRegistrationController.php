<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\FantasyRegistrationDetailResource;
use App\Http\Resources\FantasyRegistrationResource;
use App\Http\Resources\FantasyRegistrationShowResource;
use App\Models\FantasyEvent;
use App\Models\FantasyEventTeam;
use App\Models\FantasyPayment;
use App\Models\FantasyRegistration;
use App\Models\FantasyRegistrationItem;
use App\Models\FantasyShoe;
use App\Models\FantasyShoeSize;
use App\Models\FantasyTshirtOption;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FantasyRegistrationController extends Controller
{
    /**
     * Display user's registrations.
     */
    public function index(Request $request): JsonResponse
    {
        $query = FantasyRegistration::where('user_id', Auth::id())
            ->with(['fantasyEvent:id,title,description,location,play_date,base_fee']);

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by event
        if ($request->has('event_id')) {
            $query->where('fantasy_event_id', $request->event_id);
        }

        $registrations = $query->orderBy('created_at', 'desc')
                             ->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => [
                'data' => FantasyRegistrationResource::collection($registrations->items()),
                'current_page' => $registrations->currentPage(),
                'last_page' => $registrations->lastPage(),
                'per_page' => $registrations->perPage(),
                'total' => $registrations->total(),
            ],
        ]);
    }

    /**
     * Store a new registration.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'fantasy_event_id' => 'required|exists:fantasy_events,id',
            'fantasy_event_team_id' => 'required|exists:fantasy_event_teams,id',
            'items' => 'required|array|min:1',
            'items.*.type' => 'required|in:tshirt,shoe',
            'items.*.tshirt_option_id' => 'required_if:items.*.type,tshirt|exists:fantasy_tshirt_options,id',
            'items.*.shoe_size_id' => 'required_if:items.*.type,shoe|exists:fantasy_shoe_sizes,id',
        ]);

        try {
            DB::beginTransaction();

            // Check if user already registered for this event
            $existingRegistration = FantasyRegistration::where('fantasy_event_id', $validated['fantasy_event_id'])
                ->where('user_id', Auth::id())
                ->first();

            if ($existingRegistration) {
                return response()->json([
                    'success' => false,
                    'message' => 'You have already registered for this event',
                ], 422);
            }

            // Get event and team
            $event = FantasyEvent::findOrFail($validated['fantasy_event_id']);
            $team = FantasyEventTeam::findOrFail($validated['fantasy_event_team_id']);

            // Check if event is open for registration
            if ($event->status !== 'open') {
                return response()->json([
                    'success' => false,
                    'message' => 'Event is not open for registration',
                ], 422);
            }

            // Check team slot availability
            $currentRegistrations = $team->registrations()->count();
            if ($currentRegistrations >= $team->slot_limit) {
                return response()->json([
                    'success' => false,
                    'message' => 'Team is full',
                ], 422);
            }

            // Calculate total fee
            $totalFee = $event->base_fee;
            $itemsData = [];

            foreach ($validated['items'] as $item) {
                if ($item['type'] === 'tshirt') {
                    $tshirtOption = FantasyTshirtOption::findOrFail($item['tshirt_option_id']);
                    // T-shirt is usually free, but we can add price logic here if needed
                    $itemsData[] = [
                        'type' => 'tshirt',
                        'tshirt_option_id' => $tshirtOption->id,
                        'price' => 0,
                    ];
                } elseif ($item['type'] === 'shoe') {
                    $shoeSize = FantasyShoeSize::with('fantasyShoe')->findOrFail($item['shoe_size_id']);
                    
                    // Check stock availability
                    if ($shoeSize->stock <= $shoeSize->reserved_stock) {
                        return response()->json([
                            'success' => false,
                            'message' => "Shoe size {$shoeSize->size} is out of stock",
                        ], 422);
                    }

                    $totalFee += $shoeSize->fantasyShoe->price;
                    $itemsData[] = [
                        'type' => 'shoe',
                        'shoe_size_id' => $shoeSize->id,
                        'price' => $shoeSize->fantasyShoe->price,
                    ];

                    // Reserve stock
                    $shoeSize->increment('reserved_stock');
                }
            }

            // Create registration
            $registration = FantasyRegistration::create([
                'registration_code' => 'REG-' . strtoupper(Str::random(8)),
                'fantasy_event_id' => $validated['fantasy_event_id'],
                'user_id' => Auth::id(),
                'fantasy_event_team_id' => $validated['fantasy_event_team_id'],
                'registration_fee' => $totalFee,
                'status' => 'pending',
            ]);

            // Create registration items
            foreach ($itemsData as $itemData) {
                FantasyRegistrationItem::create([
                    'fantasy_registration_id' => $registration->id,
                    'fantasy_tshirt_option_id' => $itemData['tshirt_option_id'] ?? null,
                    'fantasy_shoe_size_id' => $itemData['shoe_size_id'] ?? null,
                    'price' => $itemData['price'],
                ]);
            }

            // Create payment object with default values
            $payment = FantasyPayment::create([
                'fantasy_registration_id' => $registration->id,
                'amount' => $totalFee,
                'status' => 'waiting',
                'method' => 'manual',
            ]);

            $registration->load([
                'fantasyEvent',
                'fantasyEventTeam',
                'registrationItems.fantasyTshirtOption',
                'registrationItems.fantasyShoeSize.fantasyShoe',
                'payments'
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Registration created successfully',
                'data' => $registration,
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to create registration',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified registration.
     */
    public function show(FantasyRegistration $fantasyRegistration): JsonResponse
    {
        // Check if user owns this registration
        if ($fantasyRegistration->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $fantasyRegistration->load([
            'fantasyEvent:id,title,description,location,play_date,base_fee',
            'fantasyEvent.teams:id,fantasy_event_id,name,slot_limit'
        ]);

        return response()->json([
            'success' => true,
            'data' => new FantasyRegistrationShowResource($fantasyRegistration),
        ]);
    }

    /**
     * Cancel a registration.
     */
    public function cancel(FantasyRegistration $fantasyRegistration): JsonResponse
    {
        // Check if user owns this registration
        if ($fantasyRegistration->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        // Check if registration can be cancelled
        if ($fantasyRegistration->status === 'confirmed') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot cancel confirmed registration',
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Release reserved stock for shoes
            foreach ($fantasyRegistration->registrationItems as $item) {
                if ($item->fantasyShoeSize) {
                    $item->fantasyShoeSize->decrement('reserved_stock');
                }
            }

            // Update registration status
            $fantasyRegistration->update(['status' => 'cancelled']);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Registration cancelled successfully',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel registration',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get registration summary for an event.
     */
    public function summary(FantasyEvent $fantasyEvent): JsonResponse
    {
        $summary = [
            'event' => $fantasyEvent->only(['id', 'title', 'play_date', 'base_fee', 'status']),
            'teams' => $fantasyEvent->teams()
                ->withCount('registrations')
                ->get()
                ->map(function ($team) {
                    return [
                        'id' => $team->id,
                        'name' => $team->name,
                        'slot_limit' => $team->slot_limit,
                        'registered' => $team->registrations_count,
                        'available' => $team->slot_limit - $team->registrations_count,
                    ];
                }),
            'shoes' => $fantasyEvent->shoes()
                ->with(['sizes' => function ($query) {
                    $query->selectRaw('*, (stock - reserved_stock) as available_stock');
                }])
                ->get()
                ->map(function ($shoe) {
                    return [
                        'id' => $shoe->id,
                        'name' => $shoe->name,
                        'price' => $shoe->price,
                        'sizes' => $shoe->sizes->map(function ($size) {
                            return [
                                'id' => $size->id,
                                'size' => $size->size,
                                'available_stock' => $size->available_stock,
                            ];
                        }),
                    ];
                }),
        ];

        return response()->json([
            'success' => true,
            'data' => $summary,
        ]);
    }

    /**
     * Display user's fantasy registration history.
     */
    public function userHistory(Request $request): JsonResponse
    {
        $query = FantasyRegistration::where('user_id', Auth::id())
            ->with(['fantasyEvent:id,title']);

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by event
        if ($request->has('event_id')) {
            $query->where('fantasy_event_id', $request->event_id);
        }

        // Filter by date range
        if ($request->has('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->has('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $registrations = $query->orderBy('created_at', 'desc')->get();

        // Transform data to show only required fields
        $transformedData = $registrations->map(function ($registration) {
            return [
                'id' => $registration->id,
                'registration_code' => $registration->registration_code,
                'status' => $registration->status,
                'created_at' => $registration->created_at,
                'event_name' => $registration->fantasyEvent->title,
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'User fantasy registration history retrieved successfully',
            'data' => $transformedData,
        ]);
    }

    /**
     * Display detailed information about a specific user's fantasy registration.
     */
    public function userRegistrationDetail($id): JsonResponse
    {
        $registration = FantasyRegistration::where('user_id', Auth::id())
            ->where('id', $id)
            ->with([
                'fantasyEvent:id,title,location',
                'fantasyEventTeam:id,name',
                'registrationItems.fantasyTshirtOption:id,size,color',
                'registrationItems.fantasyShoeSize.fantasyShoe:id,brand,model,price',
                'registrationItems.fantasyShoeSize:id,fantasy_shoe_id,size',
            ])
            ->first();

        if (!$registration) {
            return response()->json([
                'success' => false,
                'message' => 'Fantasy registration not found or you do not have permission to view this registration',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Fantasy registration detail retrieved successfully',
            'data' => new FantasyRegistrationDetailResource($registration),
        ]);
    }
}