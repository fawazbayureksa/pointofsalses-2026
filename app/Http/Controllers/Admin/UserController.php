<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Outlet;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $authUser = Auth::user();
        $isSuperAdmin = $authUser->hasRole('super_admin');

        $baseQuery = User::query();
        if (! $isSuperAdmin) {
            $baseQuery->where('tenant_id', $authUser->tenant_id);
        }

        // Summary statistics before search/filter
        $stats = [
            'total' => (clone $baseQuery)->count(),
            'active' => (clone $baseQuery)->where('is_active', true)->count(),
            'on_trial' => (clone $baseQuery)->whereHas('tenant', function ($q) {
                $q->where('subscription_status', 'trial')
                    ->whereNotNull('trial_ends_at')
                    ->where('trial_ends_at', '>', now());
            })->count(),
            'subscribed' => (clone $baseQuery)->whereHas('tenant', function ($q) {
                $q->where('subscription_status', 'active')
                    ->where(function ($q2) {
                        $q2->whereNull('subscription_ends_at')
                            ->orWhere('subscription_ends_at', '>', now());
                    });
            })->count(),
            'expired' => (clone $baseQuery)->whereHas('tenant', function ($q) {
                $q->where('is_subscription_exempt', false)
                    ->where(function ($q2) {
                        $q2->where('subscription_status', 'expired')
                            ->orWhere('subscription_status', 'cancelled')
                            ->orWhere(function ($q3) {
                                $q3->where('subscription_status', 'trial')
                                    ->where(function ($q4) {
                                        $q4->whereNull('trial_ends_at')
                                            ->orWhere('trial_ends_at', '<=', now());
                                    });
                            })
                            ->orWhere(function ($q3) {
                                $q3->where('subscription_status', 'active')
                                    ->whereNotNull('subscription_ends_at')
                                    ->where('subscription_ends_at', '<=', now());
                            });
                    });
            })->count(),
        ];

        $query = User::with(['roles', 'outlets', 'tenant'])->latest();

        if (! $isSuperAdmin) {
            $query->where('tenant_id', $authUser->tenant_id);
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhereHas('tenant', function ($tq) use ($search) {
                        $tq->where('name', 'like', "%{$search}%")
                            ->orWhere('slug', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('role')) {
            $query->whereHas('roles', fn ($q) => $q->where('name', $request->role));
        }

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        if ($request->filled('subscription_status')) {
            $subStatus = $request->subscription_status;
            if ($subStatus === 'trial_active') {
                $query->whereHas('tenant', function ($q) {
                    $q->where('subscription_status', 'trial')
                        ->whereNotNull('trial_ends_at')
                        ->where('trial_ends_at', '>', now());
                });
            } elseif ($subStatus === 'trial_expired') {
                $query->whereHas('tenant', function ($q) {
                    $q->where('subscription_status', 'trial')
                        ->where(function ($q2) {
                            $q2->whereNull('trial_ends_at')
                                ->orWhere('trial_ends_at', '<=', now());
                        });
                });
            } elseif ($subStatus === 'exempt') {
                $query->whereHas('tenant', fn ($q) => $q->where('is_subscription_exempt', true));
            } else {
                $query->whereHas('tenant', fn ($q) => $q->where('subscription_status', $subStatus));
            }
        }

        $users = $query->paginate(15)->withQueryString();
        $roles = Role::orderBy('name')->get();

        $outletsQuery = Outlet::where('is_active', true)->orderBy('name');
        if (! $isSuperAdmin) {
            $outletsQuery->where('tenant_id', $authUser->tenant_id);
        }
        $outlets = $outletsQuery->get();

        $tenants = $isSuperAdmin
            ? Tenant::orderBy('name')->get()
            : Tenant::where('id', $authUser->tenant_id)->get();

        return view('admin.users.index', compact('users', 'roles', 'outlets', 'tenants', 'stats'));
    }

    public function create()
    {
        return redirect()->route('admin.users.index');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'phone' => 'nullable|string|max:20',
            'outlet_id' => 'nullable|exists:outlets,id',
            'is_active' => 'nullable|boolean',
            'status' => 'nullable|in:active,inactive',
            'role' => 'nullable|string|exists:roles,name',
            'tenant_id' => 'nullable|exists:tenants,id',
            'business_name' => 'nullable|string|max:255',
            'plan' => 'nullable|in:basic,professional,enterprise',
            'subscription_status' => 'nullable|in:trial,active,expired,cancelled',
            'trial_ends_at' => 'nullable|date',
            'subscription_ends_at' => 'nullable|date',
            'is_subscription_exempt' => 'nullable|boolean',
            'avatar' => 'nullable|image|max:2048',
        ]);

        $authUser = Auth::user();
        $isSuperAdmin = $authUser->hasRole('super_admin');

        $isActive = isset($validated['is_active'])
            ? (bool) $validated['is_active']
            : (($validated['status'] ?? 'active') === 'active');

        $avatarPath = null;
        if ($request->hasFile('avatar')) {
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
        }

        DB::transaction(function () use ($request, $validated, $authUser, $isSuperAdmin, $isActive, $avatarPath) {
            $tenantId = $isSuperAdmin ? ($validated['tenant_id'] ?? null) : $authUser->tenant_id;
            $roleName = $validated['role'] ?? 'tenant_admin';

            if ($tenantId) {
                $tenant = Tenant::find($tenantId);
                if ($tenant && ($isSuperAdmin || $authUser->hasRole('tenant_admin'))) {
                    $tenantUpdates = [];
                    if ($request->filled('business_name')) {
                        $tenantUpdates['name'] = $validated['business_name'];
                    }
                    if ($request->filled('plan')) {
                        $tenantUpdates['plan'] = $validated['plan'];
                    }
                    if ($request->filled('subscription_status')) {
                        $tenantUpdates['subscription_status'] = $validated['subscription_status'];
                    }
                    if ($request->has('trial_ends_at') && $request->filled('trial_ends_at')) {
                        $tenantUpdates['trial_ends_at'] = $validated['trial_ends_at'];
                    }
                    if ($request->has('subscription_ends_at') && $request->filled('subscription_ends_at')) {
                        $tenantUpdates['subscription_ends_at'] = $validated['subscription_ends_at'];
                    }
                    if ($request->has('is_subscription_exempt')) {
                        $tenantUpdates['is_subscription_exempt'] = $request->boolean('is_subscription_exempt');
                    }
                    if (! empty($tenantUpdates)) {
                        $tenant->update($tenantUpdates);
                    }
                }
            } elseif ($roleName !== 'super_admin') {
                // Provision a new Tenant with trial/subscription settings (like RegisteredUserController)
                $businessName = ! empty($validated['business_name'])
                    ? $validated['business_name']
                    : $validated['name']."'s Business";

                $baseSlug = Str::slug($businessName) ?: 'business';
                $slug = $baseSlug;
                $counter = 1;
                while (Tenant::where('slug', $slug)->lockForUpdate()->exists()) {
                    $slug = $baseSlug.'-'.$counter++;
                }

                $subStatus = $validated['subscription_status'] ?? 'trial';
                $trialEndsAt = $request->filled('trial_ends_at')
                    ? $validated['trial_ends_at']
                    : ($subStatus === 'trial' ? now()->addDays(14) : null);

                $tenant = Tenant::create([
                    'name' => $businessName,
                    'slug' => $slug,
                    'email' => $validated['email'],
                    'phone' => $validated['phone'] ?? null,
                    'plan' => $validated['plan'] ?? 'professional',
                    'status' => 'active',
                    'subscription_status' => $subStatus,
                    'trial_ends_at' => $trialEndsAt,
                    'subscription_ends_at' => $validated['subscription_ends_at'] ?? null,
                    'is_subscription_exempt' => $request->boolean('is_subscription_exempt'),
                ]);

                $tenantId = $tenant->id;
            }

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'phone' => $validated['phone'] ?? null,
                'avatar' => $avatarPath,
                'is_active' => $isActive,
                'tenant_id' => $tenantId,
            ]);

            if (! empty($validated['outlet_id'])) {
                $user->outlets()->attach($validated['outlet_id'], ['is_default' => true]);
            }

            if (! empty($roleName)) {
                $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
                $user->assignRole($role);
            }
        });

        return redirect()->route('admin.users.index')->with('success', 'User created successfully.');
    }

    public function show(User $user)
    {
        return redirect()->route('admin.users.index');
    }

    public function edit(User $user)
    {
        return redirect()->route('admin.users.index');
    }

    public function update(Request $request, User $user)
    {
        $authUser = Auth::user();
        $isSuperAdmin = $authUser->hasRole('super_admin');

        if (! $isSuperAdmin && $user->tenant_id !== $authUser->tenant_id) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => 'nullable|string|max:20',
            'password' => 'nullable|string|min:8',
            'outlet_id' => 'nullable|exists:outlets,id',
            'is_active' => 'nullable|boolean',
            'role' => 'nullable|string|exists:roles,name',
            'business_name' => 'nullable|string|max:255',
            'plan' => 'nullable|in:basic,professional,enterprise',
            'subscription_status' => 'nullable|in:trial,active,expired,cancelled',
            'trial_ends_at' => 'nullable|date',
            'subscription_ends_at' => 'nullable|date',
            'is_subscription_exempt' => 'nullable|boolean',
            'avatar' => 'nullable|image|max:2048',
        ]);

        DB::transaction(function () use ($request, $validated, $user) {
            $userUpdates = [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'is_active' => isset($validated['is_active']) ? (bool) $validated['is_active'] : $user->is_active,
            ];

            if (! empty($validated['password'])) {
                $userUpdates['password'] = Hash::make($validated['password']);
            }

            if ($request->hasFile('avatar')) {
                $userUpdates['avatar'] = $request->file('avatar')->store('avatars', 'public');
            }

            $user->update($userUpdates);

            if ($request->has('outlet_id')) {
                if (! empty($validated['outlet_id'])) {
                    $defaultOutlet = $user->outlets()->wherePivot('is_default', true)->first();
                    if ($defaultOutlet && $defaultOutlet->id != $validated['outlet_id']) {
                        $user->outlets()->updateExistingPivot($defaultOutlet->id, ['is_default' => false]);
                    }
                    $user->outlets()->syncWithoutDetaching([$validated['outlet_id'] => ['is_default' => true]]);
                } else {
                    $user->outlets()->detach();
                }
            }

            if (! empty($validated['role'])) {
                $user->syncRoles([$validated['role']]);
            }

            // Update or provision Tenant subscription & trial expired dates
            $hasSubscriptionFields = $request->hasAny([
                'business_name',
                'plan',
                'subscription_status',
                'trial_ends_at',
                'subscription_ends_at',
                'is_subscription_exempt',
            ]);

            if ($hasSubscriptionFields) {
                $tenant = $user->tenant;

                if (! $tenant && ! $user->hasRole('super_admin')) {
                    $businessName = ! empty($validated['business_name'])
                        ? $validated['business_name']
                        : $user->name."'s Business";

                    $baseSlug = Str::slug($businessName) ?: 'business';
                    $slug = $baseSlug;
                    $counter = 1;
                    while (Tenant::where('slug', $slug)->lockForUpdate()->exists()) {
                        $slug = $baseSlug.'-'.$counter++;
                    }

                    $tenant = Tenant::create([
                        'name' => $businessName,
                        'slug' => $slug,
                        'email' => $user->email,
                        'phone' => $user->phone,
                        'plan' => $validated['plan'] ?? 'professional',
                        'status' => 'active',
                        'subscription_status' => $validated['subscription_status'] ?? 'trial',
                        'trial_ends_at' => $validated['trial_ends_at'] ?? now()->addDays(14),
                        'subscription_ends_at' => $validated['subscription_ends_at'] ?? null,
                        'is_subscription_exempt' => $request->boolean('is_subscription_exempt'),
                    ]);

                    $user->update(['tenant_id' => $tenant->id]);
                } elseif ($tenant) {
                    $tenantUpdates = [];

                    if ($request->filled('business_name')) {
                        $tenantUpdates['name'] = $validated['business_name'];
                    }
                    if ($request->filled('plan')) {
                        $tenantUpdates['plan'] = $validated['plan'];
                    }
                    if ($request->filled('subscription_status')) {
                        $tenantUpdates['subscription_status'] = $validated['subscription_status'];
                    }
                    if ($request->has('trial_ends_at')) {
                        $tenantUpdates['trial_ends_at'] = $validated['trial_ends_at'] ?: null;
                    }
                    if ($request->has('subscription_ends_at')) {
                        $tenantUpdates['subscription_ends_at'] = $validated['subscription_ends_at'] ?: null;
                    }
                    if ($request->has('is_subscription_exempt')) {
                        $tenantUpdates['is_subscription_exempt'] = $request->boolean('is_subscription_exempt');
                    }

                    if (! empty($tenantUpdates)) {
                        $tenant->update($tenantUpdates);
                    }
                }
            }
        });

        return redirect()->route('admin.users.index')->with('success', 'User and subscription details updated successfully.');
    }

    public function destroy(User $user)
    {
        if (! Auth::user()->hasRole('super_admin') && $user->tenant_id !== Auth::user()->tenant_id) {
            abort(403);
        }

        if ($user->id === Auth::id()) {
            return redirect()->route('admin.users.index')->withErrors(['error' => 'You cannot delete your own account.']);
        }

        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'User deleted successfully.');
    }

    public function profile()
    {
        $user = Auth::user();

        return view('admin.users.profile', compact('user'));
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => 'nullable|string|max:20',
            'avatar' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('avatar')) {
            $validated['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $user->update($validated);

        return redirect()->route('admin.profile')->with('success', 'Profile updated successfully.');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|current_password',
            'password' => 'required|string|min:8|confirmed',
        ]);

        Auth::user()->update(['password' => Hash::make($request->password)]);

        return redirect()->route('admin.profile')->with('success', 'Password updated successfully.');
    }
}
