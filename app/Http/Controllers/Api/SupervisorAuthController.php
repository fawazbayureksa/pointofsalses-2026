<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CashierService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SupervisorAuthController extends Controller
{
    public function __construct(
        private CashierService $cashierService,
    ) {}

    /**
     * POST /api/supervisor/authorize
     *
     * Verify a supervisor PIN for restricted actions (refund, void, discount override).
     * Returns the supervisor's info and a one-time authorization token.
     */
    public function authorize(Request $request): JsonResponse
    {
        $request->validate([
            'pin'    => ['required', 'string', 'size:6'],
            'action' => ['required', 'string', 'in:refund,void,discount_override'],
        ]);

        $supervisor = $this->cashierService->verifySupervisorPin(
            $request->pin,
            $request->user()->tenant_id
        );

        $permissionMap = [
            'refund'            => 'authorize refund',
            'void'              => 'authorize void',
            'discount_override' => 'manage orders',
        ];

        $requiredPermission = $permissionMap[$request->action] ?? null;

        if ($requiredPermission && ! $supervisor->can($requiredPermission)) {
            return response()->json([
                'message' => 'Supervisor does not have permission to authorize this action.',
            ], 403);
        }

        activity()
            ->causedBy($supervisor)
            ->withProperties([
                'action'       => $request->action,
                'requested_by' => $request->user()->id,
            ])
            ->log("Supervisor authorized {$request->action}");

        return response()->json([
            'authorized'    => true,
            'supervisor_id' => $supervisor->id,
            'supervisor'    => $supervisor->name,
            'action'        => $request->action,
        ]);
    }
}
