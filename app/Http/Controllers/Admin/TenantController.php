<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class TenantController extends Controller
{
    public function index()
    {
        $tenants = Tenant::latest()->paginate(15);
        return view('admin.tenants.index', compact('tenants'));
    }

    public function create()
    {
        return view('admin.tenants.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'slug'           => 'required|string|max:255|unique:tenants,slug',
            'status'         => 'required|in:active,inactive,suspended',
            'plan'           => 'nullable|in:basic,professional,enterprise',
            'business_type'  => 'nullable|string|max:100',
            'email'          => 'nullable|email|max:255',
            'phone'          => 'nullable|string|max:20',
            'address'        => 'nullable|string|max:500',
            'admin_name'     => 'required|string|max:255',
            'admin_email'    => 'required|email|max:255|unique:users,email',
            'admin_password' => 'required|string|min:8|confirmed',
        ]);

        $tenant = Tenant::create([
            'name'          => $validated['name'],
            'slug'          => $validated['slug'],
            'status'        => $validated['status'],
            'plan'          => $validated['plan'] ?? 'basic',
            'business_type' => $validated['business_type'] ?? null,
            'email'         => $validated['email'] ?? null,
            'phone'         => $validated['phone'] ?? null,
            'address'       => $validated['address'] ?? null,
        ]);

        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);

        $user = User::create([
            'tenant_id' => $tenant->id,
            'name'      => $validated['admin_name'],
            'email'     => $validated['admin_email'],
            'password'  => Hash::make($validated['admin_password']),
            'is_active' => true,
        ]);

        $user->assignRole($adminRole);

        return redirect()->route('admin.tenants.index')
            ->with('success', "Tenant created. Admin account provisioned for {$validated['admin_email']}.");
    }

    public function show(Tenant $tenant)
    {
        $tenant->load(['users', 'outlets']);
        return view('admin.tenants.show', compact('tenant'));
    }

    public function edit(Tenant $tenant)
    {
        return view('admin.tenants.edit', compact('tenant'));
    }

    public function update(Request $request, Tenant $tenant)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'status'        => 'required|in:active,inactive,suspended',
            'plan'          => 'nullable|in:basic,professional,enterprise',
            'business_type' => 'nullable|string|max:100',
            'email'         => 'nullable|email|max:255',
            'phone'         => 'nullable|string|max:20',
            'address'       => 'nullable|string|max:500',
        ]);

        $tenant->update($validated);

        return redirect()->route('admin.tenants.index')
            ->with('success', 'Tenant updated successfully.');
    }

    public function destroy(Tenant $tenant)
    {
        $tenant->delete();

        return redirect()->route('admin.tenants.index')
            ->with('success', 'Tenant deleted successfully.');
    }
}
