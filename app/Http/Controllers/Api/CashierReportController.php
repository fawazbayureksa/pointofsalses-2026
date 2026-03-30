<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CashierShift;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CashierReportController extends Controller
{
    /**
     * GET /api/reports/sales-by-cashier
     *
     * Sales summary grouped by cashier for the given date range.
     */
    public function salesByCashier(Request $request): JsonResponse
    {
        $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to'   => ['nullable', 'date'],
            'outlet_id' => ['nullable', 'exists:outlets,id'],
        ]);

        $tenantId = $request->user()->tenant_id;

        $query = Order::where('tenant_id', $tenantId)
            ->where('status', 'completed');

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('outlet_id')) {
            $query->where('outlet_id', $request->outlet_id);
        }

        $report = $query->select([
            'user_id',
            DB::raw('MAX(cashier_name) as cashier_name'),
            DB::raw('COUNT(*) as total_transactions'),
            DB::raw('SUM(total_amount) as total_sales'),
            DB::raw('AVG(total_amount) as average_transaction'),
            DB::raw('SUM(discount_amount) as total_discounts'),
            DB::raw('SUM(tax_amount) as total_tax'),
        ])
            ->groupBy('user_id')
            ->get()
            ->map(function ($row) {
                return [
                    'cashier_id'          => $row->user_id,
                    'cashier_name'        => $row->cashier_name ?? 'Unknown',
                    'total_transactions'  => (int) $row->total_transactions,
                    'total_sales'         => round((float) $row->total_sales, 2),
                    'average_transaction' => round((float) $row->average_transaction, 2),
                    'total_discounts'     => round((float) $row->total_discounts, 2),
                    'total_tax'           => round((float) $row->total_tax, 2),
                ];
            });

        return response()->json(['report' => $report]);
    }

    /**
     * GET /api/reports/shift-summary
     *
     * Shift summary report with shift details and associated sales.
     */
    public function shiftSummary(Request $request): JsonResponse
    {
        $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to'   => ['nullable', 'date'],
            'user_id'   => ['nullable', 'exists:users,id'],
            'outlet_id' => ['nullable', 'exists:outlets,id'],
        ]);

        $tenantId = $request->user()->tenant_id;

        $query = CashierShift::where('tenant_id', $tenantId)
            ->with(['user:id,name', 'outlet:id,name']);

        if ($request->filled('date_from')) {
            $query->whereDate('started_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('started_at', '<=', $request->date_to);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('outlet_id')) {
            $query->where('outlet_id', $request->outlet_id);
        }

        $shifts = $query->latest('started_at')
            ->paginate($request->per_page ?? 20);

        $shifts->getCollection()->transform(function (CashierShift $shift) {
            $salesDuring = Order::where('user_id', $shift->user_id)
                ->where('outlet_id', $shift->outlet_id)
                ->where('status', 'completed')
                ->when($shift->started_at, fn($q) => $q->where('created_at', '>=', $shift->started_at))
                ->when($shift->ended_at, fn($q) => $q->where('created_at', '<=', $shift->ended_at))
                ->selectRaw('COUNT(*) as count, COALESCE(SUM(total_amount), 0) as total')
                ->first();

            return [
                'id'            => $shift->id,
                'cashier'       => $shift->user?->name,
                'outlet'        => $shift->outlet?->name,
                'started_at'    => $shift->started_at?->toIso8601String(),
                'ended_at'      => $shift->ended_at?->toIso8601String(),
                'starting_cash' => (float) $shift->starting_cash,
                'ending_cash'   => $shift->ending_cash !== null ? (float) $shift->ending_cash : null,
                'transactions'  => (int) ($salesDuring->count ?? 0),
                'total_sales'   => round((float) ($salesDuring->total ?? 0), 2),
                'notes'         => $shift->notes,
            ];
        });

        return response()->json($shifts);
    }
}
