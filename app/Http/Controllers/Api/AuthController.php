<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Outlet;
use App\Models\Tenant;
use App\Models\User;
use App\Services\CashierService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

class AuthController extends Controller
{
    public function __construct(
        private CashierService $cashierService,
    ) {}

    /**
     * POST /api/auth/register
     *
     * Register a new Tenant, admin user, and default outlet for mobile apps.
     */
    public function register(Request $request): JsonResponse
    {
        $request->validate([
            'name'                  => ['required', 'string', 'max:255'],
            'business_name'         => ['nullable', 'string', 'max:255'],
            'email'                 => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password'              => ['required', 'string', 'confirmed', 'min:8'],
            'password_confirmation' => ['required', 'string'],
            'phone'                 => ['nullable', 'string', 'max:20'],
        ]);

        $businessName = $request->filled('business_name')
            ? trim((string) $request->business_name)
            : trim((string) $request->name) . "'s Business";

        $baseSlug = Str::slug($businessName) ?: 'business';

        [$tenant, $user, $outlet] = DB::transaction(function () use ($request, $businessName, $baseSlug) {
            // Reserve a unique slug inside the transaction to prevent race conditions
            $slug = $baseSlug;
            $counter = 1;
            while (Tenant::where('slug', $slug)->lockForUpdate()->exists()) {
                $slug = $baseSlug . '-' . $counter++;
            }

            $tenant = Tenant::create([
                'name'                   => $businessName,
                'slug'                   => $slug,
                'email'                  => $request->email,
                'phone'                  => $request->phone,
                'plan'                   => 'professional',
                'status'                 => 'active',
                'subscription_status'    => 'trial',
                'trial_ends_at'          => now()->addDays(14),
                'is_subscription_exempt' => false,
            ]);

            $adminRole = Role::firstOrCreate(['name' => 'tenant_admin', 'guard_name' => 'web']);

            $user = User::create([
                'tenant_id' => $tenant->id,
                'name'      => $request->name,
                'email'     => $request->email,
                'phone'     => $request->phone,
                'password'  => Hash::make($request->password),
                'is_active' => true,
            ]);

            $user->assignRole($adminRole);

            $outlet = Outlet::create([
                'tenant_id' => $tenant->id,
                'name'      => 'Main Outlet',
                'code'      => 'MAIN',
                'phone'     => $request->phone,
                'is_active' => true,
            ]);

            $user->outlets()->attach($outlet->id, ['is_default' => true]);

            return [$tenant, $user, $outlet];
        });

        event(new Registered($user));

        $token = $user->createToken('pos-token', $user->getPermissionsViaRoles()->pluck('name')->toArray())->plainTextToken;

        return response()->json([
            'message' => 'Tenant registered successfully.',
            'token'   => $token,
            'user'    => [
                'id'      => $user->id,
                'name'    => $user->name,
                'email'   => $user->email,
                'phone'   => $user->phone,
                'roles'   => $user->getRoleNames(),
                'has_pin' => $user->hasPin(),
            ],
            'tenant'  => [
                'id'                  => $tenant->id,
                'name'                => $tenant->name,
                'slug'                => $tenant->slug,
                'email'               => $tenant->email,
                'plan'                => $tenant->plan,
                'status'              => $tenant->status,
                'subscription_status' => $tenant->subscription_status,
                'trial_ends_at'       => $tenant->trial_ends_at?->toISOString(),
            ],
            'outlet'  => [
                'id'        => $outlet->id,
                'name'      => $outlet->name,
                'code'      => $outlet->code,
                'is_active' => $outlet->is_active,
            ],
        ], 201);
    }

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
        $request->user()->currentAccessToken()?->delete();

        return response()->json(['message' => 'Logged out successfully.']);
    }

    /**
     * PUT /api/auth/profile
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'name'  => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', 'unique:users,email,' . $user->id],
            'phone' => ['sometimes', 'nullable', 'string', 'max:20'],
        ]);

        $user->update($data);

        return response()->json([
            'id'    => $user->id,
            'name'  => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
        ]);
    }

    /**
     * PUT /api/auth/password
     */
    public function changePassword(Request $request): JsonResponse
    {
        $request->validate([
            'current_password' => ['required', 'string', 'current_password'],
            'password'         => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $request->user()->update([
            'password' => $request->password,
        ]);

        return response()->json(['message' => 'Password changed successfully.']);
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
