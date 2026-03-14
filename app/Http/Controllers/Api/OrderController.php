<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderService;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(
        private readonly OrderService $orderService,
        private readonly PaymentService $paymentService,
    ) {}

    /**
     * GET /api/orders
     */
    public function index(Request $request): JsonResponse
    {
        $orders = Order::with(['cashier', 'customer', 'outlet'])
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->when($request->outlet_id, fn($q, $id) => $q->where('outlet_id', $id))
            ->when($request->date_from, fn($q, $d) => $q->whereDate('created_at', '>=', $d))
            ->when($request->date_to, fn($q, $d) => $q->whereDate('created_at', '<=', $d))
            ->latest()
            ->paginate($request->per_page ?? 20);

        return response()->json($orders);
    }

    /**
     * POST /api/orders
     */
    public function store(Request $request): JsonResponse
    {
        $this->authorize('manage_orders');

        $data = $request->validate([
            'outlet_id'               => ['required', 'exists:outlets,id'],
            'customer_id'             => ['nullable', 'exists:customers,id'],
            'notes'                   => ['nullable', 'string'],
            'items'                   => ['required', 'array', 'min:1'],
            'items.*.product_id'      => ['required', 'exists:products,id'],
            'items.*.quantity'        => ['required', 'numeric', 'min:0.001'],
            'items.*.discount_amount' => ['nullable', 'numeric', 'min:0'],
        ]);

        $order = $this->orderService->create($data, $request->user()->id);

        return response()->json($order, 201);
    }

    /**
     * GET /api/orders/{order}
     */
    public function show(Order $order): JsonResponse
    {
        return response()->json(
            $order->load(['items.product', 'cashier', 'customer', 'outlet', 'payments'])
        );
    }

    /**
     * POST /api/orders/{order}/pay
     */
    public function pay(Request $request, Order $order): JsonResponse
    {
        $this->authorize('manage_orders');

        $data = $request->validate([
            'payment_method'   => ['required', 'string', 'in:cash,card,qris,transfer'],
            'amount'           => ['required', 'numeric', 'min:0'],
            'reference_number' => ['nullable', 'string'],
        ]);

        $payment = $this->paymentService->pay($order, $data);

        return response()->json($payment);
    }

    /**
     * DELETE /api/orders/{order}
     */
    public function cancel(Request $request, Order $order): JsonResponse
    {
        $this->authorize('manage_orders');

        $order = $this->orderService->cancel($order, $request->reason ?? '');

        return response()->json($order);
    }
}
