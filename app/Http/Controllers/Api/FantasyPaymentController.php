<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FantasyPayment;
use App\Models\FantasyRegistration;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FantasyPaymentController extends Controller
{
    /**
     * Display user's payments.
     */
    public function index(Request $request): JsonResponse
    {
        $query = FantasyPayment::whereHas('fantasyRegistration', function ($q) {
                $q->where('user_id', Auth::id());
            })
            ->with(['fantasyRegistration.fantasyEvent']);

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by payment method
        if ($request->has('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        $payments = $query->orderBy('created_at', 'desc')
                         ->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $payments,
        ]);
    }

    /**
     * Store a new payment.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'fantasy_registration_id' => 'required|exists:fantasy_registrations,id',
            'payment_method' => 'required|in:bank_transfer,e_wallet,cash',
            'amount' => 'required|numeric|min:0',
            'payment_proof' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            DB::beginTransaction();

            // Get registration and check ownership
            $registration = FantasyRegistration::where('id', $validated['fantasy_registration_id'])
                ->where('user_id', Auth::id())
                ->first();

            if (!$registration) {
                return response()->json([
                    'success' => false,
                    'message' => 'Registration not found or unauthorized',
                ], 404);
            }

            // Check if registration is still pending
            if ($registration->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment can only be made for pending registrations',
                ], 422);
            }

            // Check if amount matches registration fee
            if ($validated['amount'] != $registration->registration_fee) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment amount must match registration fee',
                ], 422);
            }

            // Check if payment already exists
            $existingPayment = FantasyPayment::where('fantasy_registration_id', $registration->id)
                ->whereIn('status', ['pending', 'approved'])
                ->first();

            if ($existingPayment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment already exists for this registration',
                ], 422);
            }

            $paymentData = [
                'payment_code' => 'PAY-' . strtoupper(Str::random(8)),
                'fantasy_registration_id' => $validated['fantasy_registration_id'],
                'payment_method' => $validated['payment_method'],
                'amount' => $validated['amount'],
                'status' => 'pending',
                'notes' => $validated['notes'] ?? null,
            ];

            // Handle payment proof upload
            if ($request->hasFile('payment_proof')) {
                $file = $request->file('payment_proof');
                $filename = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('fantasy/payments', $filename, 'public');
                $paymentData['payment_proof'] = $path;
            }

            $payment = FantasyPayment::create($paymentData);

            $payment->load('fantasyRegistration.fantasyEvent');

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Payment submitted successfully',
                'data' => $payment,
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit payment',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified payment.
     */
    public function show(FantasyPayment $fantasyPayment): JsonResponse
    {
        // Check if user owns this payment
        if ($fantasyPayment->fantasyRegistration->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $fantasyPayment->load([
            'fantasyRegistration.fantasyEvent',
            'fantasyRegistration.fantasyEventTeam',
        ]);

        return response()->json([
            'success' => true,
            'data' => $fantasyPayment,
        ]);
    }

    /**
     * Update payment proof.
     */
    public function updateProof(Request $request, FantasyPayment $fantasyPayment): JsonResponse
    {
        // Check if user owns this payment
        if ($fantasyPayment->fantasyRegistration->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        // Check if payment can be updated
        if ($fantasyPayment->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot update payment proof for non-pending payments',
            ], 422);
        }

        $validated = $request->validate([
            'payment_proof' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            // Delete old payment proof if exists
            if ($fantasyPayment->payment_proof) {
                Storage::disk('public')->delete($fantasyPayment->payment_proof);
            }

            // Upload new payment proof
            $file = $request->file('payment_proof');
            $filename = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('fantasy/payments', $filename, 'public');

            $fantasyPayment->update([
                'payment_proof' => $path,
                'notes' => $validated['notes'] ?? $fantasyPayment->notes,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Payment proof updated successfully',
                'data' => $fantasyPayment,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update payment proof',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get payment methods and bank accounts.
     */
    public function paymentMethods(): JsonResponse
    {
        $methods = [
            'bank_transfer' => [
                'name' => 'Bank Transfer',
                'accounts' => [
                    [
                        'bank' => 'BCA',
                        'account_number' => '1234567890',
                        'account_name' => 'INASPRO',
                    ],
                    [
                        'bank' => 'Mandiri',
                        'account_number' => '0987654321',
                        'account_name' => 'INASPRO',
                    ],
                ],
            ],
            'e_wallet' => [
                'name' => 'E-Wallet',
                'accounts' => [
                    [
                        'provider' => 'GoPay',
                        'number' => '081234567890',
                        'name' => 'INASPRO',
                    ],
                    [
                        'provider' => 'OVO',
                        'number' => '081234567890',
                        'name' => 'INASPRO',
                    ],
                ],
            ],
            'cash' => [
                'name' => 'Cash',
                'description' => 'Pay directly at the event location',
            ],
        ];

        return response()->json([
            'success' => true,
            'data' => $methods,
        ]);
    }

    /**
     * Get payment statistics for user.
     */
    public function statistics(): JsonResponse
    {
        $userId = Auth::id();

        $stats = [
            'total_payments' => FantasyPayment::whereHas('fantasyRegistration', function ($q) use ($userId) {
                $q->where('user_id', $userId);
            })->count(),
            
            'pending_payments' => FantasyPayment::whereHas('fantasyRegistration', function ($q) use ($userId) {
                $q->where('user_id', $userId);
            })->where('status', 'pending')->count(),
            
            'approved_payments' => FantasyPayment::whereHas('fantasyRegistration', function ($q) use ($userId) {
                $q->where('user_id', $userId);
            })->where('status', 'approved')->count(),
            
            'total_amount' => FantasyPayment::whereHas('fantasyRegistration', function ($q) use ($userId) {
                $q->where('user_id', $userId);
            })->where('status', 'approved')->sum('amount'),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }
}