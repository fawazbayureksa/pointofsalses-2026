<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Outlet;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Payment;
use App\Services\OrderService;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function __construct(
        private OrderService $orderService,
        private PaymentService $paymentService,
    ) {}

    public function index(Request $request)
    {
        $query = Order::with(['customer', 'outlet'])->latest();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhereHas('customer', fn($q) => $q->where('name', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('outlet_id')) {
            $query->where('outlet_id', $request->outlet_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $orders    = $query->paginate(15)->withQueryString();
        $outlets   = Outlet::where('is_active', true)->get();
        $customers = Customer::orderBy('name')->get();
        $products  = Product::where('is_active', true)->get();

        return view('admin.orders.index', compact('orders', 'outlets', 'customers', 'products'));
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
            'outlet_id'               => 'required|exists:outlets,id',
            'customer_id'             => 'nullable|exists:customers,id',
            'notes'                   => 'nullable|string',
            'discount_amount'         => 'nullable|numeric|min:0',
            'discount_type'           => 'nullable|in:fixed,percentage',
            'payment_method'          => 'required|in:cash,card,qris,transfer',
            'amount_tendered'         => 'required|numeric|min:0',
            'items'                   => 'required|array|min:1',
            'items.*.product_id'      => 'required|exists:products,id',
            'items.*.quantity'        => 'required|numeric|min:0.001',
            'items.*.discount_amount' => 'nullable|numeric|min:0',
        ]);

        $order = $this->orderService->create([
            'outlet_id'       => $validated['outlet_id'],
            'customer_id'     => $validated['customer_id'] ?? null,
            'notes'           => $validated['notes'] ?? null,
            'discount_amount' => $validated['discount_amount'] ?? 0,
            'discount_type'   => $validated['discount_type'] ?? 'fixed',
            'items'           => $validated['items'],
        ], Auth::user()->id);

        $this->paymentService->pay($order, [
            'payment_method' => $validated['payment_method'],
            'amount'         => (float) $validated['amount_tendered'],
        ]);

        return redirect()->route('admin.orders.print', $order)->with('success', 'Order created and payment recorded.');
    }

    public function show(Order $order)
    {
        $order->load(['items.product', 'customer', 'outlet', 'payments']);

        if (request()->expectsJson()) {
            return response()->json([
                'id'             => $order->id,
                'order_number'   => $order->order_number ?? '#' . $order->id,
                'status'         => $order->status,
                'total_amount'   => $order->total_amount,
                'discount_amount' => $order->discount_amount,
                'tax_amount'     => $order->tax_amount,
                'notes'          => $order->notes,
                'created_at'     => $order->created_at?->format('M d, Y H:i'),
                'customer'       => $order->customer ? [
                    'name'  => $order->customer->name,
                    'email' => $order->customer->email,
                    'phone' => $order->customer->phone,
                ] : null,
                'outlet' => $order->outlet ? [
                    'name'    => $order->outlet->name,
                    'address' => $order->outlet->address,
                ] : null,
                'payment_method' => $order->payments->first()?->payment_method,
                'items' => $order->items->map(fn($item) => [
                    'product_name' => $item->product_name ?? optional($item->product)->name ?? '-',
                    'unit_price'   => $item->unit_price,
                    'quantity'     => $item->quantity,
                    'subtotal'     => $item->subtotal,
                ]),
            ]);
        }

        return redirect()->route('admin.orders.index');
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
        $qty      = (float) $validated['quantity'];

        $order->items()->create([
            'product_id'      => $product->id,
            'product_name'    => $product->name,
            'product_sku'     => $product->sku ?? '',
            'unit_price'      => $product->price,
            'cost_price'      => $product->cost_price,
            'quantity'        => $qty,
            'discount_amount' => 0,
            'tax_amount'      => 0,
            'subtotal'        => $product->price * $qty,
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
            'payment_method' => 'required|in:cash,card,qris,transfer',
            'amount'         => 'required|numeric|min:0',
        ]);

        $this->paymentService->pay($order, $validated);

        return redirect()->back()->with('success', 'Payment processed.');
    }

    public function refundPayment(Request $request, Order $order)
    {
        $payment = $order->payments()->where('status', 'completed')->latest()->firstOrFail();
        $this->paymentService->refund($payment, $request->reason ?? '');
        return redirect()->back()->with('success', 'Payment refunded.');
    }
}
