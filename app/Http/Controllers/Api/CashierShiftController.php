<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CashierShift;
use App\Services\CashierService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CashierShiftController extends Controller
{
    public function __construct(
        private CashierService $cashierService,
    ) {}

    /**
     * POST /api/shifts/start
     *
     * Start a new cashier shift.
     */
    public function start(Request $request): JsonResponse
    {
        $request->validate([
            'outlet_id'     => ['required', 'exists:outlets,id'],
            'starting_cash' => ['nullable', 'numeric', 'min:0'],
            'notes'         => ['nullable', 'string', 'max:500'],
        ]);

        $shift = $this->cashierService->startShift(
            $request->user(),
            (int) $request->outlet_id,
            (float) ($request->starting_cash ?? 0),
            $request->notes
        );

        return response()->json([
            'message' => 'Shift started successfully.',
            'shift'   => $this->formatShift($shift),
        ], 201);
    }

    /**
     * POST /api/shifts/end
     *
     * End the current active shift.
     */
    public function end(Request $request): JsonResponse
    {
        $request->validate([
            'ending_cash' => ['nullable', 'numeric', 'min:0'],
            'notes'       => ['nullable', 'string', 'max:500'],
        ]);

        $shift = $this->cashierService->endShift(
            $request->user(),
            (float) ($request->ending_cash ?? 0),
            $request->notes
        );

        return response()->json([
            'message' => 'Shift ended successfully.',
            'shift'   => $this->formatShift($shift),
        ]);
    }

    /**
     * GET /api/shifts/current
     *
     * Get the current active shift for the authenticated cashier.
     */
    public function current(Request $request): JsonResponse
    {
        $shift = CashierShift::where('user_id', $request->user()->id)
            ->whereNull('ended_at')
            ->with(['outlet'])
            ->first();

        if (! $shift) {
            return response()->json(['shift' => null, 'message' => 'No active shift.']);
        }

        return response()->json(['shift' => $this->formatShift($shift)]);
    }

    /**
     * GET /api/shifts
     *
     * List shifts for the authenticated cashier (with optional filters).
     */
    public function index(Request $request): JsonResponse
    {
        $query = CashierShift::where('tenant_id', $request->user()->tenant_id)
            ->with(['user', 'outlet']);

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('outlet_id')) {
            $query->where('outlet_id', $request->outlet_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('started_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('started_at', '<=', $request->date_to);
        }

        if ($request->boolean('active_only')) {
            $query->whereNull('ended_at');
        }

        $shifts = $query->latest('started_at')
            ->paginate($request->per_page ?? 20);

        return response()->json($shifts);
    }

    private function formatShift(CashierShift $shift): array
    {
        $shift->loadMissing(['user', 'outlet']);

        return [
            'id'            => $shift->id,
            'user'          => [
                'id'   => $shift->user->id,
                'name' => $shift->user->name,
            ],
            'outlet'        => [
                'id'   => $shift->outlet->id,
                'name' => $shift->outlet->name,
            ],
            'started_at'    => $shift->started_at?->toIso8601String(),
            'ended_at'      => $shift->ended_at?->toIso8601String(),
            'starting_cash' => (float) $shift->starting_cash,
            'ending_cash'   => $shift->ending_cash !== null ? (float) $shift->ending_cash : null,
            'notes'         => $shift->notes,
            'is_active'     => $shift->isActive(),
        ];
    }
}
