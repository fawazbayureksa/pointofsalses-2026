<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'can:view orders']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $orders = Order::query()
            ->when(tenancy()->initialized, function ($query) {
                // Tenant-aware filtering: only show orders for current tenant
                return $query->where('tenant_id', tenancy()->tenant->id);
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                return $query->where('status', $request->status);
            })
            ->when($request->filled('outlet_id'), function ($query) use ($request) {
                return $query->where('outlet_id', $request->outlet_id);
            })
            ->when($request->filled('customer_id'), function ($query) use ($request) {
                return $query->where('customer_id', $request->customer_id);
            })
            ->when($request->filled('date_from'), function ($query) use ($request) {
                return $query->whereDate('created_at', '>=', $request->date_from);
            })
            ->when($request->filled('date_to'), function ($query) use ($request) {
                return $query->whereDate('created_at', '<=', $request->date_to);
            })
            ->with(['customer', 'outlet', 'items.product'])
            ->latest()
            ->paginate(20);

        // Get data for filters
        $outlets = Outlet::query()
            ->when(tenancy()->initialized, function ($query) {
                return $query->where('tenant_id', tenancy()->tenant->id);
            })
            ->where('status', 'active')
            ->get();

        return view('admin.orders.index', compact('orders', 'outlets'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'outlet_id' => 'required|exists:outlets,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'payment_method' => 'required|in:cash,card,transfer,e-wallet',
            'status' => 'required|in:pending,completed,cancelled',
            'notes' => 'nullable|string',
        ]);

        // Validate outlet belongs to tenant
        if (tenancy()->initialized) {
            $outlet = Outlet::findOrFail($validated['outlet_id']);
            if ($outlet->tenant_id !== tenancy()->tenant->id) {
                abort(403, 'You can only create orders for your tenant outlets');
            }
        }

        $order = Order::create([
            'tenant_id' => tenancy()->initialized ? tenancy()->tenant->id : null,
            'customer_id' => $validated['customer_id'],
            'outlet_id' => $validated['outlet_id'],
            'payment_method' => $validated['payment_method'],
            'status' => $validated['status'],
            'notes' => $validated['notes'] ?? null,
            'total_amount' => 0,
        ]);

        // Create order items
        $totalAmount = 0;
        foreach ($validated['items'] as $item) {
            $product = Product::findOrFail($item['product_id']);

            // Validate product belongs to tenant
            if (tenancy()->initialized && $product->tenant_id !== tenancy()->tenant->id) {
                abort(403, 'Product does not belong to current tenant');
            }

            $orderItem = $order->items()->create([
                'product_id' => $product->id,
                'quantity' => $item['quantity'],
                'price' => $product->price,
                'subtotal' => $product->price * $item['quantity'],
            ]);

            $totalAmount += $orderItem->subtotal;

            // Update stock
            $product->decrement('stock', $item['quantity']);
        }

        $order->update(['total_amount' => $totalAmount]);

        // Log activity
        activity()
            ->performedOn($order)
            ->causedBy(auth()->user())
            ->log('created order');

        return redirect()
            ->route('admin.orders.index')
            ->with('success', 'Order created successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(Order $order)
    {
        // Tenant-aware check
        if (tenancy()->initialized && $order->tenant_id !== tenancy()->tenant->id) {
            abort(403, 'You do not have permission to view this order');
        }

        $order->load(['customer', 'outlet', 'items.product', 'payments']);

        return view('admin.orders.show', compact('order'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Order $order)
    {
        // Tenant-aware check
        if (tenancy()->initialized && $order->tenant_id !== tenancy()->tenant->id) {
            abort(403, 'You do not have permission to update this order');
        }

        $validated = $request->validate([
            'status' => 'required|in:pending,completed,cancelled,refunded',
            'notes' => 'nullable|string',
        ]);

        $order->update($validated);

        // Log activity
        activity()
            ->performedOn($order)
            ->causedBy(auth()->user())
            ->log('updated order status to '.$validated['status']);

        return redirect()
            ->route('admin.orders.index')
            ->with('success', 'Order updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Order $order)
    {
        // Tenant-aware check
        if (tenancy()->initialized && $order->tenant_id !== tenancy()->tenant->id) {
            abort(403, 'You do not have permission to delete this order');
        }

        // Restore stock
        foreach ($order->items as $item) {
            $item->product->increment('stock', $item->quantity);
        }

        $order->delete();

        // Log activity
        activity()
            ->performedOn($order)
            ->causedBy(auth()->user())
            ->log('deleted order');

        return redirect()
            ->route('admin.orders.index')
            ->when('success', 'Order deleted successfully');
    }
}
