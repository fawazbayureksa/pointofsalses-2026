<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\CashierService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function __construct(
        private CashierService $cashierService,
    ) {}

    /**
     * POST /api/auth/login
     */
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        /** @var User $user */
        $user  = Auth::user();
        $token = $user->createToken('pos-token', $user->getPermissionsViaRoles()->pluck('name')->toArray())->plainTextToken;

        return response()->json([
            'token' => $token,
            'user'  => [
                'id'      => $user->id,
                'name'    => $user->name,
                'email'   => $user->email,
                'roles'   => $user->getRoleNames(),
                'has_pin' => $user->hasPin(),
            ],
        ]);
    }

    /**
     * POST /api/auth/login-pin
     *
     * Authenticate a cashier using email + PIN for fast access.
     */
    public function loginWithPin(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'pin'   => ['required', 'string', 'size:6'],
        ]);

        $result = $this->cashierService->authenticateWithPin(
            $request->email,
            $request->pin
        );

        return response()->json($result);
    }

    /**
     * POST /api/auth/switch-cashier
     *
     * Switch the active cashier on a shared device using PIN.
     */
    public function switchCashier(Request $request): JsonResponse
    {
        $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'pin'     => ['required', 'string', 'size:6'],
        ]);

        $result = $this->cashierService->switchCashier(
            (int) $request->user_id,
            $request->pin
        );

        return response()->json($result);
    }

    /**
     * GET /api/auth/cashiers
     *
     * List available cashiers for the current tenant (for cashier-switch UI).
     */
    public function listCashiers(Request $request): JsonResponse
    {
        $tenantId = $request->user()->tenant_id;

        $cashiers = User::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->whereNotNull('pin')
            ->select(['id', 'name', 'email', 'avatar'])
            ->get()
            ->map(fn(User $user) => [
                'id'     => $user->id,
                'name'   => $user->name,
                'avatar' => $user->avatar,
            ]);

        return response()->json(['cashiers' => $cashiers]);
    }

    /**
     * POST /api/auth/set-pin
     *
     * Set or update the authenticated user's PIN.
     */
    public function setPin(Request $request): JsonResponse
    {
        $request->validate([
            'pin'              => ['required', 'string', 'size:6', 'regex:/^\d{6}$/'],
            'pin_confirmation' => ['required', 'string', 'same:pin'],
        ]);

        $this->cashierService->setPin($request->user(), $request->pin);

        return response()->json(['message' => 'PIN set successfully.']);
    }

    /**
     * POST /api/auth/logout
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully.']);
    }

    /**
     * GET /api/auth/me
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user()->load('roles');

        return response()->json([
            'id'          => $user->id,
            'name'        => $user->name,
            'email'       => $user->email,
            'roles'       => $user->getRoleNames(),
            'permissions' => $user->getAllPermissions()->pluck('name'),
            'has_pin'     => $user->hasPin(),
        ]);
    }
}
