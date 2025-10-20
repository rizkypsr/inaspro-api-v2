<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    /**
     * Display a listing of orders.
     */
    public function index(Request $request)
    {
        $query = Order::with(['user', 'orderItems.productVariant.product', 'shippingRate.province'])
            ->orderBy('created_at', 'desc');

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('uuid', 'like', "%{$search}%")
                  ->orWhere('tracking_number', 'like', "%{$search}%")
                  ->orWhere('courier_name', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%")
                               ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Payment status filter
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        // Date range filter
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        
        $allowedSortFields = ['created_at', 'status', 'payment_status', 'total_amount', 'uuid'];
        if (in_array($sortBy, $allowedSortFields)) {
            $query->orderBy($sortBy, $sortOrder);
        }

        $orders = $query->paginate(15)->withQueryString();

        return Inertia::render('admin/marketplace/orders', [
            'orders' => $orders,
            'filters' => $request->only(['search', 'status', 'payment_status', 'date_from', 'date_to', 'sort_by', 'sort_order']),
            'statusOptions' => [
                'pending' => 'Pending',
                'processing' => 'Processing',
                'shipped' => 'Shipped',
                'delivered' => 'Delivered',
                'cancelled' => 'Cancelled',
            ],
            'paymentStatusOptions' => [
                'pending' => 'Pending',
                'paid' => 'Paid',
                'failed' => 'Failed',
                'refunded' => 'Refunded',
            ],
        ]);
    }

    /**
     * Display the specified order.
     */
    public function show(Order $order)
    {
        $order->load([
            'user',
            'orderItems.productVariant.product.category',
            'orderItems.productVariant',
            'shippingRate.province',
            'globalVouchers',
            'productVouchers'
        ]);

        return Inertia::render('admin/marketplace/orders/show', [
            'order' => $order,
        ]);
    }

    /**
     * Show the form for editing the specified order.
     */
    public function edit(Order $order)
    {
        $order->load(['user', 'orderItems.productVariant.product', 'shippingRate.province']);

        return Inertia::render('admin/marketplace/orders/edit', [
            'order' => $order,
            'statusOptions' => [
                'pending' => 'Pending',
                'processing' => 'Processing',
                'shipped' => 'Shipped',
                'delivered' => 'Delivered',
                'cancelled' => 'Cancelled',
            ],
            'paymentStatusOptions' => [
                'pending' => 'Pending',
                'paid' => 'Paid',
                'failed' => 'Failed',
                'refunded' => 'Refunded',
            ],
        ]);
    }

    /**
     * Update the specified order.
     */
    public function update(Request $request, Order $order)
    {
        $validated = $request->validate([
            'status' => ['sometimes', Rule::in(['pending', 'processing', 'shipped', 'delivered', 'cancelled'])],
            'payment_status' => ['sometimes', Rule::in(['pending', 'paid', 'failed', 'refunded'])],
            'tracking_number' => ['sometimes', 'nullable', 'string', 'max:255'],
            'courier_name' => ['sometimes', 'nullable', 'string', 'max:255'],
        ], [
            'status.in' => 'The selected status is invalid.',
            'payment_status.in' => 'The selected payment status is invalid.',
            'tracking_number.max' => 'The tracking number may not be greater than 255 characters.',
            'courier_name.max' => 'The courier name may not be greater than 255 characters.',
        ]);

        $order->update($validated);

        return redirect()->route('orders.index')
            ->with('success', 'Order updated successfully.');
    }

    /**
     * Update tracking number for the specified order.
     */
    public function updateTracking(Request $request, Order $order)
    {
        $validated = $request->validate([
            'tracking_number' => ['required', 'string', 'max:255'],
            'courier_name' => ['sometimes', 'nullable', 'string', 'max:255'],
        ], [
            'tracking_number.required' => 'The tracking number is required.',
            'tracking_number.max' => 'The tracking number may not be greater than 255 characters.',
            'courier_name.max' => 'The courier name may not be greater than 255 characters.',
        ]);

        $order->update($validated);

        // Automatically update status to shipped if tracking number is provided
        if (in_array($order->status, ['pending', 'processing'])) {
            $order->update(['status' => 'shipped']);
        }

        return redirect()->back()
            ->with('success', 'Tracking number updated successfully.');
    }

    /**
     * Upload payment proof for the specified order.
     */
    public function uploadPaymentProof(Request $request, Order $order)
    {
        $validated = $request->validate([
            'payment_proof' => ['required', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
        ], [
            'payment_proof.required' => 'Payment proof image is required.',
            'payment_proof.image' => 'The file must be an image.',
            'payment_proof.mimes' => 'The image must be a file of type: jpeg, png, jpg, gif.',
            'payment_proof.max' => 'The image may not be greater than 2MB.',
        ]);

        // Delete old payment proof if exists
        if ($order->payment_proof) {
            Storage::disk('public')->delete($order->payment_proof);
        }

        // Store new payment proof
        $path = $request->file('payment_proof')->store('payment-proofs', 'public');

        $order->update([
            'payment_proof' => $path,
            'payment_status' => 'paid', // Automatically mark as paid when proof is uploaded
        ]);

        return redirect()->back()
            ->with('success', 'Payment proof uploaded successfully.');
    }

    /**
     * Remove payment proof from the specified order.
     */
    public function removePaymentProof(Order $order)
    {
        if ($order->payment_proof) {
            Storage::disk('public')->delete($order->payment_proof);
            $order->update([
                'payment_proof' => null,
                'payment_status' => 'pending',
            ]);
        }

        return redirect()->back()
            ->with('success', 'Payment proof removed successfully.');
    }

    /**
     * Bulk update orders.
     */
    public function bulkUpdate(Request $request)
    {
        $validated = $request->validate([
            'order_ids' => ['required', 'array', 'min:1'],
            'order_ids.*' => ['required', 'integer', 'exists:orders,id'],
            'action' => ['required', 'string', Rule::in(['update_status', 'update_payment_status'])],
            'status' => ['required_if:action,update_status', Rule::in(['pending', 'processing', 'shipped', 'delivered', 'cancelled'])],
            'payment_status' => ['required_if:action,update_payment_status', Rule::in(['pending', 'paid', 'failed', 'refunded'])],
        ], [
            'order_ids.required' => 'Please select at least one order.',
            'order_ids.min' => 'Please select at least one order.',
            'action.required' => 'Please select an action.',
            'action.in' => 'The selected action is invalid.',
            'status.required_if' => 'Status is required when updating order status.',
            'payment_status.required_if' => 'Payment status is required when updating payment status.',
        ]);

        $updateData = [];
        if ($validated['action'] === 'update_status') {
            $updateData['status'] = $validated['status'];
        } elseif ($validated['action'] === 'update_payment_status') {
            $updateData['payment_status'] = $validated['payment_status'];
        }

        $updatedCount = Order::whereIn('id', $validated['order_ids'])->update($updateData);

        return redirect()->back()
            ->with('success', "Successfully updated {$updatedCount} orders.");
    }
}