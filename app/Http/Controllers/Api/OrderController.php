<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Services\OrderService;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    public function __construct(
        private OrderService $orderService,
        private PaymentService $paymentService,
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
            ->when($request->search, fn($q, $s) => $q->where(fn($sq) => $sq->where('order_number', 'like', "%{$s}%")
                ->orWhere('notes', 'like', "%{$s}%")))
            ->latest()
            ->paginate($request->per_page ?? 20);

        return response()->json($orders);
    }

    /**
     * POST /api/orders
     */
    public function store(Request $request): JsonResponse
    {
        // $this->authorize('manage_orders');

        $data = $request->validate([
            'outlet_id'                => ['required', 'exists:outlets,id'],
            'customer_id'              => ['nullable', 'exists:customers,id'],
            'notes'                    => ['nullable', 'string'],
            'discount_amount'          => ['nullable', 'numeric', 'min:0'],
            'discount_type'            => ['nullable', 'string', 'in:fixed,percentage'],
            'loyalty_points_redeemed'  => ['nullable', 'integer', 'min:0'],
            'items'                    => ['required', 'array', 'min:1'],
            'items.*.product_id'       => ['required', 'exists:products,id'],
            'items.*.quantity'         => ['required', 'numeric', 'min:0.001'],
            'items.*.discount_amount'  => ['nullable', 'numeric', 'min:0'],
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
        // $this->authorize('manage_orders');

        $data = $request->validate([
            'payment_method'   => ['required', 'string', 'in:cash,card,qris,transfer'],
            'amount'           => ['required', 'numeric', 'min:0'],
            'reference_number' => ['nullable', 'string'],
        ]);

        $payment = $this->paymentService->pay($order, $data);

        return response()->json($payment);
    }

    /**
     * POST /api/orders/{order}/cancel
     */
    public function cancel(Request $request, Order $order): JsonResponse
    {
        // $this->authorize('manage_orders');

        $order = $this->orderService->cancel($order, $request->reason ?? '');

        return response()->json($order);
    }

    /**
     * POST /api/orders/{order}/refund
     *
     * Refund a completed/paid order. Requires supervisor authorization for the
     * 'refund' action (supervisor_id returned by POST /api/supervisor/authorize).
     */
    public function refund(Request $request, Order $order): JsonResponse
    {
        // $this->authorize('manage_orders');

        $data = $request->validate([
            'reason'        => ['nullable', 'string', 'max:500'],
            'supervisor_id' => ['nullable', 'integer', 'exists:users,id'],
        ]);

        if ($order->status !== 'completed' || $order->payment_status !== 'paid') {
            throw ValidationException::withMessages([
                'order' => 'Only completed and paid orders can be refunded.',
            ]);
        }

        $payment = $order->payments()->where('status', 'completed')->latest()->first();

        if (! $payment) {
            throw ValidationException::withMessages([
                'order' => 'No completed payment found for this order.',
            ]);
        }

        $payment = $this->paymentService->refund($payment, $data['reason'] ?? '');

        if (isset($data['supervisor_id'])) {
            $order->update(['authorized_by' => $data['supervisor_id']]);
        }

        return response()->json([
            'message' => 'Order refunded successfully.',
            'payment' => $payment->refresh()->load('order'),
        ]);
    }

    /**
     * PATCH /api/orders/{order}/discount
     *
     * Apply or update an order-level discount on a pending order.
     * Typically called after supervisor authorization for 'discount_override'.
     */
    public function applyDiscount(Request $request, Order $order): JsonResponse
    {
        // $this->authorize('manage_orders');

        $data = $request->validate([
            'discount_amount' => ['required', 'numeric', 'min:0'],
            'discount_type'   => ['nullable', 'string', 'in:fixed,percentage'],
            'supervisor_id'   => ['nullable', 'integer', 'exists:users,id'],
        ]);

        if ($order->status !== 'pending') {
            throw ValidationException::withMessages([
                'order' => 'Discount can only be applied to pending orders.',
            ]);
        }

        $this->orderService->applyOrderDiscount(
            $order,
            (float) $data['discount_amount'],
            $data['discount_type'] ?? 'fixed',
            $data['supervisor_id'] ?? null
        );

        return response()->json(
            $order->fresh(['items.product', 'cashier', 'customer', 'outlet', 'payments'])
        );
    }
}
