<?php

namespace App\Http\Controllers;

use App\Models\FantasyEvent;
use App\Models\FantasyEventTeam;
use App\Models\FantasyTshirtOption;
use App\Models\FantasyShoe;
use App\Models\FantasyShoeSize;
use App\Models\FantasyRegistration;
use App\Models\FantasyRegistrationItem;
use App\Models\FantasyPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;
use Exception;

class FantasyController extends Controller
{
    /**
     * Display a listing of fantasy events.
     */
    public function index(Request $request): Response
    {
        $query = FantasyEvent::with(['creator', 'teams', 'shoes'])
            ->withCount(['teams', 'registrations'])
            ->orderBy('created_at', 'desc');

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%")
                  ->orWhereHas('creator', function ($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Date range filter
        if ($request->filled('date_from')) {
            $query->whereDate('play_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('play_date', '<=', $request->date_to);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $fantasyEvents = $query->paginate(15)->withQueryString();

        return Inertia::render('admin/fantasy/index', [
            'fantasyEvents' => $fantasyEvents,
            'filters' => $request->only(['search', 'status', 'date_from', 'date_to', 'sort_by', 'sort_order']),
        ]);
    }

    /**
     * Display the specified fantasy event with all related data.
     */
    public function show(FantasyEvent $fantasyEvent): Response
    {
        $fantasyEvent->load([
            'creator',
            'teams.tshirtOptions',
            'shoes.sizes',
            'registrations.user',
            'registrations.fantasyEventTeam',
            'registrations.registrationItems.fantasyTshirtOption',
            'registrations.registrationItems.fantasyShoeSize.fantasyShoe',
            'registrations.payments'
        ]);

        // Get teams with their t-shirt options
        $teams = FantasyEventTeam::where('fantasy_event_id', $fantasyEvent->id)
            ->with(['tshirtOptions'])
            ->withCount('registrations')
            ->get();

        // Get t-shirt options for this event
        $tshirtOptions = FantasyTshirtOption::whereHas('fantasyEventTeam', function ($query) use ($fantasyEvent) {
            $query->where('fantasy_event_id', $fantasyEvent->id);
        })->with('fantasyEventTeam')->get();

        // Get shoes for this event
        $shoes = FantasyShoe::where('fantasy_event_id', $fantasyEvent->id)
            ->with(['sizes'])
            ->get();

        // Get registrations for this event
        $registrations = FantasyRegistration::where('fantasy_event_id', $fantasyEvent->id)
            ->with([
                'user',
                'fantasyEventTeam',
                'registrationItems.fantasyTshirtOption',
                'registrationItems.fantasyShoeSize.fantasyShoe',
                'payments'
            ])
            ->orderBy('created_at', 'desc')
            ->get();

        // Get payments for this event
        $payments = FantasyPayment::whereHas('fantasyRegistration', function ($query) use ($fantasyEvent) {
            $query->where('fantasy_event_id', $fantasyEvent->id);
        })->with(['fantasyRegistration.user'])
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('admin/fantasy/show', [
            'fantasyEvent' => $fantasyEvent,
            'teams' => $teams,
            'tshirtOptions' => $tshirtOptions,
            'shoes' => $shoes,
            'registrations' => $registrations,
            'payments' => $payments,
        ]);
    }

    /**
     * Reserve shoe sizes atomically during registration flow
     */
    public function reserveShoeSize(Request $request)
    {
        $request->validate([
            'fantasy_event_id' => 'required|exists:fantasy_events,id',
            'fantasy_event_team_id' => 'required|exists:fantasy_event_teams,id',
            'user_id' => 'required|exists:users,id',
            'shoe_sizes' => 'required|array',
            'shoe_sizes.*.fantasy_shoe_size_id' => 'required|exists:fantasy_shoe_sizes,id',
            'shoe_sizes.*.quantity' => 'required|integer|min:1',
            'tshirt_options' => 'array',
            'tshirt_options.*.fantasy_tshirt_option_id' => 'required|exists:fantasy_tshirt_options,id',
            'tshirt_options.*.quantity' => 'required|integer|min:1',
        ]);

        try {
            return DB::transaction(function () use ($request) {
                // Create registration
                $registration = FantasyRegistration::create([
                    'fantasy_event_id' => $request->fantasy_event_id,
                    'fantasy_event_team_id' => $request->fantasy_event_team_id,
                    'user_id' => $request->user_id,
                    'status' => 'pending',
                    'registration_date' => now(),
                ]);

                // Process shoe size reservations
                foreach ($request->shoe_sizes as $shoeItem) {
                    $shoeSizeId = $shoeItem['fantasy_shoe_size_id'];
                    $quantity = $shoeItem['quantity'];

                    // Atomic check + increment with row locking
                    $shoeSize = DB::table('fantasy_shoe_sizes')
                        ->where('id', $shoeSizeId)
                        ->lockForUpdate()
                        ->first();

                    if (!$shoeSize) {
                        throw new Exception("Shoe size not found");
                    }

                    $availableStock = $shoeSize->stock - $shoeSize->reserved_stock;
                    if ($availableStock < $quantity) {
                        throw new Exception("Insufficient stock for shoe size. Available: {$availableStock}, Requested: {$quantity}");
                    }

                    // Increment reserved stock
                    DB::table('fantasy_shoe_sizes')
                        ->where('id', $shoeSizeId)
                        ->increment('reserved_stock', $quantity);

                    // Create registration item
                    FantasyRegistrationItem::create([
                        'fantasy_registration_id' => $registration->id,
                        'fantasy_shoe_size_id' => $shoeSizeId,
                        'quantity' => $quantity,
                        'type' => 'shoe',
                    ]);
                }

                // Process t-shirt options if provided
                if ($request->has('tshirt_options')) {
                    foreach ($request->tshirt_options as $tshirtItem) {
                        FantasyRegistrationItem::create([
                            'fantasy_registration_id' => $registration->id,
                            'fantasy_tshirt_option_id' => $tshirtItem['fantasy_tshirt_option_id'],
                            'quantity' => $tshirtItem['quantity'],
                            'type' => 'tshirt',
                        ]);
                    }
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Registration created successfully',
                    'registration' => $registration->load('registrationItems'),
                ]);
            });
        } catch (Exception $e) {
            Log::error('Registration reservation failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Confirm payment and finalize stock allocation
     */
    public function confirmPayment(Request $request, FantasyRegistration $registration)
    {
        $request->validate([
            'payment_id' => 'required|exists:fantasy_payments,id',
        ]);

        try {
            return DB::transaction(function () use ($request, $registration) {
                // Verify payment exists and belongs to this registration
                $payment = FantasyPayment::where('id', $request->payment_id)
                    ->where('fantasy_registration_id', $registration->id)
                    ->first();

                if (!$payment) {
                    throw new Exception('Payment not found for this registration');
                }

                if ($payment->status === 'confirmed') {
                    throw new Exception('Payment already confirmed');
                }

                // Update payment status
                $payment->update([
                    'status' => 'confirmed',
                    'confirmed_at' => now(),
                ]);

                // Process shoe items: decrement stock and reserved_stock
                $shoeItems = $registration->registrationItems()
                    ->where('type', 'shoe')
                    ->whereNotNull('fantasy_shoe_size_id')
                    ->get();

                foreach ($shoeItems as $item) {
                    $affected = DB::table('fantasy_shoe_sizes')
                        ->where('id', $item->fantasy_shoe_size_id)
                        ->where('reserved_stock', '>', 0)
                        ->where('stock', '>=', $item->quantity)
                        ->update([
                            'stock' => DB::raw('stock - ' . $item->quantity),
                            'reserved_stock' => DB::raw('reserved_stock - ' . $item->quantity),
                        ]);

                    if ($affected === 0) {
                        throw new Exception("Failed to confirm stock for shoe size ID: {$item->fantasy_shoe_size_id}");
                    }
                }

                // Update registration status
                $registration->update([
                    'status' => 'confirmed',
                    'confirmed_at' => now(),
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Payment confirmed and stock allocated successfully',
                    'registration' => $registration->fresh()->load(['registrationItems', 'payments']),
                ]);
            });
        } catch (Exception $e) {
            Log::error('Payment confirmation failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Cancel registration and release reserved stock
     */
    public function cancelRegistration(FantasyRegistration $registration)
    {
        try {
            return DB::transaction(function () use ($registration) {
                if ($registration->status === 'confirmed') {
                    throw new Exception('Cannot cancel confirmed registration');
                }

                if ($registration->status === 'cancelled') {
                    throw new Exception('Registration already cancelled');
                }

                // Release reserved stock for shoe items
                $shoeItems = $registration->registrationItems()
                    ->where('type', 'shoe')
                    ->whereNotNull('fantasy_shoe_size_id')
                    ->get();

                foreach ($shoeItems as $item) {
                    DB::table('fantasy_shoe_sizes')
                        ->where('id', $item->fantasy_shoe_size_id)
                        ->where('reserved_stock', '>=', $item->quantity)
                        ->decrement('reserved_stock', $item->quantity);
                }

                // Update registration status
                $registration->update([
                    'status' => 'cancelled',
                    'cancelled_at' => now(),
                ]);

                // Cancel any pending payments
                $registration->payments()
                    ->where('status', 'pending')
                    ->update([
                        'status' => 'cancelled',
                        'cancelled_at' => now(),
                    ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Registration cancelled and reserved stock released',
                    'registration' => $registration->fresh(),
                ]);
            });
        } catch (Exception $e) {
            Log::error('Registration cancellation failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Expire registrations and release reserved stock (for scheduled jobs)
     */
    public function expireRegistrations()
    {
        try {
            $expiredRegistrations = FantasyRegistration::where('status', 'pending')
                ->where('created_at', '<', now()->subHours(24)) // 24 hour expiry
                ->get();

            $expiredCount = 0;

            foreach ($expiredRegistrations as $registration) {
                DB::transaction(function () use ($registration) {
                    // Release reserved stock
                    $shoeItems = $registration->registrationItems()
                        ->where('type', 'shoe')
                        ->whereNotNull('fantasy_shoe_size_id')
                        ->get();

                    foreach ($shoeItems as $item) {
                        DB::table('fantasy_shoe_sizes')
                            ->where('id', $item->fantasy_shoe_size_id)
                            ->where('reserved_stock', '>=', $item->quantity)
                            ->decrement('reserved_stock', $item->quantity);
                    }

                    // Update registration status
                    $registration->update([
                        'status' => 'expired',
                        'expired_at' => now(),
                    ]);

                    // Cancel any pending payments
                    $registration->payments()
                        ->where('status', 'pending')
                        ->update([
                            'status' => 'expired',
                            'expired_at' => now(),
                        ]);
                });

                $expiredCount++;
            }

            Log::info("Expired {$expiredCount} registrations and released reserved stock");

            return response()->json([
                'success' => true,
                'message' => "Expired {$expiredCount} registrations",
                'expired_count' => $expiredCount,
            ]);
        } catch (Exception $e) {
            Log::error('Registration expiry failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Show the form for creating a new fantasy event.
     */
    public function create(): Response
    {
        return Inertia::render('admin/fantasy/create');
    }

    /**
     * Store a newly created fantasy event in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'location' => 'required|string|max:255',
            'play_date' => 'required|date|after:now',
            'base_fee' => 'required|numeric|min:0',
            'status' => 'required|in:draft,open,closed,finished',
            
            // Teams validation (minimum 2 teams required) with t-shirt sizes
            'teams' => 'required|array|min:2',
            'teams.*.name' => 'required|string|max:255',
            'teams.*.slot_limit' => 'required|integer|min:1',
            'teams.*.tshirt_sizes' => 'required|array|min:1',
            'teams.*.tshirt_sizes.*' => 'required|string|in:XS,S,M,L,XL,XXL',
            
            // Shoes validation (optional) - name, price, and image
            'shoes' => 'nullable|array',
            'shoes.*.name' => 'required|string|max:255',
            'shoes.*.price' => 'required|numeric|min:0',
            'shoes.*.image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'shoes.*.sizes' => 'required|array|min:1',
            'shoes.*.sizes.*.size' => 'required|string|max:10',
            'shoes.*.sizes.*.stock' => 'required|integer|min:0',
        ]);

        try {
            return DB::transaction(function () use ($request) {
                // Create the fantasy event
                $fantasyEvent = FantasyEvent::create([
                    'title' => $request->title,
                    'description' => $request->description,
                    'location' => $request->location,
                    'play_date' => $request->play_date,
                    'base_fee' => $request->base_fee,
                    'status' => $request->status,
                    'created_by' => auth()->id(),
                ]);

                // Create teams
                foreach ($request->teams as $teamData) {
                    $team = FantasyEventTeam::create([
                        'fantasy_event_id' => $fantasyEvent->id,
                        'name' => $teamData['name'],
                        'slot_limit' => $teamData['slot_limit'],
                    ]);

                    // Create t-shirt options for this team
                    foreach ($teamData['tshirt_sizes'] as $size) {
                        FantasyTshirtOption::create([
                            'fantasy_event_team_id' => $team->id,
                            'size' => $size,
                        ]);
                    }
                }

                // Create shoes (if provided)
                if ($request->has('shoes') && !empty($request->shoes)) {
                    foreach ($request->shoes as $shoeData) {
                        // Handle image upload
                        $imagePath = null;
                        if (isset($shoeData['image']) && $shoeData['image']) {
                            $imagePath = $shoeData['image']->store('fantasy/shoes', 'public');
                        }

                        $shoe = FantasyShoe::create([
                            'fantasy_event_id' => $fantasyEvent->id,
                            'name' => $shoeData['name'],
                            'price' => $shoeData['price'],
                            'image' => $imagePath,
                        ]);

                        // Create shoe sizes
                        foreach ($shoeData['sizes'] as $sizeData) {
                            FantasyShoeSize::create([
                                'fantasy_shoe_id' => $shoe->id,
                                'size' => $sizeData['size'],
                                'stock' => $sizeData['stock'],
                                'reserved_stock' => 0,
                            ]);
                        }
                    }
                }

                return redirect()->route('admin.fantasy.show', $fantasyEvent)
                    ->with('success', 'Fantasy event berhasil dibuat dengan lengkap!');
            });
        } catch (Exception $e) {
            Log::error('Failed to create fantasy event: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Gagal membuat fantasy event. Silakan coba lagi.');
        }
    }

    public function updateStatus(Request $request, FantasyEvent $fantasyEvent)
    {
        $request->validate([
            'status' => 'required|in:draft,open,closed,finished',
        ]);

        try {
            $fantasyEvent->update([
                'status' => $request->status,
            ]);

            return redirect()->back()->with('success', 'Fantasy event status updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to update fantasy event status.');
        }
    }

    /**
     * Update the specified t-shirt option.
     */
    public function updateTshirt(Request $request, FantasyTshirtOption $tshirtOption)
    {
        $request->validate([
            'size' => 'required|string|max:10',
        ]);

        try {
            $tshirtOption->update([
                'size' => $request->size,
            ]);

            return redirect()->back()->with('success', 'T-shirt option updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to update t-shirt option.');
        }
    }

    public function updateTeam(Request $request, FantasyEventTeam $team)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slot_limit' => 'required|integer|min:1',
        ]);

        try {
            $team->update([
                'name' => $request->name,
                'slot_limit' => $request->slot_limit,
            ]);

            return back()->with('success', 'Team berhasil diperbarui!');
        } catch (Exception $e) {
            Log::error('Failed to update team: ' . $e->getMessage());
            return back()->with('error', 'Gagal memperbarui team. Silakan coba lagi.');
        }
    }

    public function updatePaymentStatus(Request $request, FantasyPayment $payment)
    {
        $request->validate([
            'status' => 'required|in:pending,waiting,confirmed,rejected,failed,refunded',
        ]);

        try {
            DB::beginTransaction();

            $payment->update([
                'status' => $request->status,
            ]);

            // If payment is confirmed, update registration status to confirmed
            if ($request->status === 'confirmed') {
                $payment->fantasyRegistration()->update([
                    'status' => 'confirmed',
                ]);
            }

            DB::commit();

            return back()->with('success', 'Status pembayaran berhasil diperbarui!');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to update payment status: ' . $e->getMessage());
            return back()->with('error', 'Gagal memperbarui status pembayaran. Silakan coba lagi.');
        }
    }
}