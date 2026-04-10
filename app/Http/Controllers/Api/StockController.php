<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class StockController extends Controller
{
    /**
     * GET /api/stock/movements
     *
     * List stock movement history with optional filters.
     */
    public function movements(Request $request): JsonResponse
    {
        $request->validate([
            'product_id' => ['nullable', 'exists:products,id'],
            'outlet_id'  => ['nullable', 'exists:outlets,id'],
            'type'       => ['nullable', 'string', 'in:adjustment,sale,return,transfer_in,transfer_out'],
            'date_from'  => ['nullable', 'date'],
            'date_to'    => ['nullable', 'date'],
        ]);

        $tenantId = $request->user()->tenant_id;

        $movements = StockMovement::where('tenant_id', $tenantId)
            ->with(['product:id,name,sku', 'outlet:id,name', 'user:id,name'])
            ->when($request->product_id, fn($q, $id) => $q->where('product_id', $id))
            ->when($request->outlet_id, fn($q, $id) => $q->where('outlet_id', $id))
            ->when($request->type, fn($q, $t) => $q->where('type', $t))
            ->when($request->date_from, fn($q, $d) => $q->whereDate('created_at', '>=', $d))
            ->when($request->date_to, fn($q, $d) => $q->whereDate('created_at', '<=', $d))
            ->latest()
            ->paginate($request->per_page ?? 20);

        return response()->json($movements);
    }

    /**
     * POST /api/stock/adjust
     *
     * Manual stock adjustment for a product at a specific outlet.
     * Creates a StockMovement record of type 'adjustment'.
     */
    public function adjust(Request $request): JsonResponse
    {
        $data = $request->validate([
            'product_id'      => ['required', 'exists:products,id'],
            'outlet_id'       => ['required', 'exists:outlets,id'],
            'quantity_change' => ['required', 'numeric'],
            'reason'          => ['nullable', 'string', 'max:500'],
        ]);

        $product = Product::findOrFail($data['product_id']);

        $pivot = $product->outlets()->wherePivot('outlet_id', $data['outlet_id'])->first();

        if (! $pivot) {
            throw ValidationException::withMessages([
                'outlet_id' => 'This product is not assigned to the specified outlet.',
            ]);
        }

        $before = (float) $pivot->pivot->stock;
        $change = (float) $data['quantity_change'];
        $after  = max(0, $before + $change);

        $product->outlets()->updateExistingPivot($data['outlet_id'], ['stock' => $after]);

        $movement = StockMovement::record(
            $product,
            (int) $data['outlet_id'],
            'adjustment',
            $before,
            $change,
            $after,
            $data['reason'] ?? null,
        );

        return response()->json([
            'message'  => 'Stock adjusted successfully.',
            'movement' => $movement->load(['product:id,name,sku', 'outlet:id,name', 'user:id,name']),
        ], 201);
    }
}
