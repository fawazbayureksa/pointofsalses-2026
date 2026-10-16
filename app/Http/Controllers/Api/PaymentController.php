<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    /**
     * GET /api/payments
     *
     * List payments with optional filters, scoped to the authenticated tenant.
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'order_id'       => ['nullable', 'exists:orders,id'],
            'payment_method' => ['nullable', 'string', 'in:cash,card,qris,transfer'],
            'status'         => ['nullable', 'string', 'in:pending,completed,failed,refunded'],
            'date_from'      => ['nullable', 'date'],
            'date_to'        => ['nullable', 'date'],
        ]);

        $tenantId = $request->user()->tenant_id;

        $payments = Payment::whereHas('order', fn($q) => $q->where('tenant_id', $tenantId))
            ->with(['order:id,order_number,status,total_amount,outlet_id,user_id'])
            ->when($request->order_id, fn($q, $id) => $q->where('order_id', $id))
            ->when($request->payment_method, fn($q, $m) => $q->where('payment_method', $m))
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->when($request->date_from, fn($q, $d) => $q->whereDate('paid_at', '>=', $d))
            ->when($request->date_to, fn($q, $d) => $q->whereDate('paid_at', '<=', $d))
            ->latest('paid_at')
            ->paginate($request->per_page ?? 20);

        return response()->json($payments);
    }

    /**
     * GET /api/payments/{payment}
     *
     * Get a single payment with full order details, scoped to the authenticated tenant.
     */
    public function show(Request $request, Payment $payment): JsonResponse
    {
        $tenantId = $request->user()->tenant_id;

        if ($payment->order->tenant_id !== $tenantId) {
            abort(403, 'This payment does not belong to your tenant.');
        }

        return response()->json(
            $payment->load(['order.items.product', 'order.cashier', 'order.customer', 'order.outlet'])
        );
    }
}
