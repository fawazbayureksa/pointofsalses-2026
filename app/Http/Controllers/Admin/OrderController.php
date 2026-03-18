<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Outlet;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Payment;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::with(['customer', 'outlet'])->latest()->paginate(15);
        return view('admin.orders.index', compact('orders'));
    }

    public function create()
    {
        $outlets   = Outlet::where('is_active', true)->get();
        $customers = Customer::orderBy('name')->get();
        $products  = Product::where('is_active', true)->get();
        return view('admin.orders.index', compact('outlets', 'customers', 'products'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'outlet_id'   => 'required|exists:outlets,id',
            'customer_id' => 'nullable|exists:customers,id',
            'notes'       => 'nullable|string',
        ]);

        $order = Order::create(array_merge($validated, [
            'order_number' => 'ORD-' . strtoupper(uniqid()),
            'status'       => 'pending',
            'subtotal'     => 0,
            'tax_amount'   => 0,
            'total_amount' => 0,
        ]));

        return redirect()->route('admin.orders.index')->with('success', 'Order created successfully.');
    }

    public function show(Order $order)
    {
        $order->load(['items.product', 'customer', 'outlet', 'payment']);
        return view('admin.orders.index', compact('order'));
    }

    public function edit(Order $order)
    {
        return view('admin.orders.index', compact('order'));
    }

    public function update(Request $request, Order $order)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,processing,completed,cancelled',
            'notes'  => 'nullable|string',
        ]);

        $order->update($validated);

        return redirect()->route('admin.orders.index')->with('success', 'Order updated successfully.');
    }

    public function destroy(Order $order)
    {
        $order->delete();

        return redirect()->route('admin.orders.index')->with('success', 'Order deleted.');
    }

    public function print(Order $order)
    {
        $order->load(['items.product', 'customer', 'outlet']);
        return view('admin.orders.print', compact('order'));
    }

    public function addItem(Request $request, Order $order)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity'   => 'required|numeric|min:1',
        ]);

        $product = Product::findOrFail($validated['product_id']);

        $order->items()->create([
            'product_id'  => $product->id,
            'name'        => $product->name,
            'price'       => $product->price,
            'quantity'    => $validated['quantity'],
            'total'       => $product->price * $validated['quantity'],
        ]);

        $order->recalculateTotals();

        return redirect()->back()->with('success', 'Item added.');
    }

    public function removeItem(Request $request, Order $order, $item)
    {
        $order->items()->findOrFail($item)->delete();
        $order->recalculateTotals();

        return redirect()->back()->with('success', 'Item removed.');
    }

    public function payments()
    {
        $payments = Payment::with(['order.customer'])->latest()->paginate(15);
        return view('admin.orders.payments', compact('payments'));
    }

    public function processPayment(Request $request, Order $order)
    {
        $validated = $request->validate([
            'method' => 'required|string',
            'amount' => 'required|numeric|min:0',
        ]);

        Payment::create([
            'order_id'       => $order->id,
            'method'         => $validated['method'],
            'amount'         => $validated['amount'],
            'status'         => 'completed',
            'transaction_id' => 'TXN-' . strtoupper(uniqid()),
        ]);

        $order->update(['payment_status' => 'paid', 'status' => 'completed']);

        return redirect()->back()->with('success', 'Payment processed.');
    }

    public function refundPayment(Request $request, Order $order)
    {
        $order->update(['payment_status' => 'refunded', 'status' => 'cancelled']);

        return redirect()->back()->with('success', 'Payment refunded.');
    }
}
