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
        $tenants = Tenant::with('domains')->latest()->paginate(15);
        return view('admin.tenants.index', compact('tenants'));
    }

    public function create()
    {
        return view('admin.tenants.index');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'                  => 'required|string|max:255',
            'domain'                => 'required|string|max:255|unique:domains,domain',
            'status'                => 'required|in:active,inactive,suspended',
            'plan'                  => 'nullable|in:basic,professional,enterprise',
            'business_type'         => 'nullable|string|max:100',
            'email'                 => 'nullable|email|max:255',
            'phone'                 => 'nullable|string|max:20',
            'address'               => 'nullable|string|max:500',
            'admin_name'            => 'required|string|max:255',
            'admin_email'           => 'required|email|max:255',
            'admin_password'        => 'required|string|min:8|confirmed',
        ]);

        $tenant = Tenant::create([
            'id'            => Str::slug($validated['name']) . '-' . Str::lower(Str::random(6)),
            'name'          => $validated['name'],
            'slug'          => Str::slug($validated['name']),
            'status'        => $validated['status'],
            'plan'          => $validated['plan'] ?? 'basic',
            'business_type' => $validated['business_type'] ?? null,
        ]);

        // Store extra fields in the data JSON column (stancl/tenancy)
        $tenant->email   = $validated['email']   ?? null;
        $tenant->phone   = $validated['phone']   ?? null;
        $tenant->address = $validated['address'] ?? null;
        $tenant->save();

        // Create domain (triggers TenantCreated → CreateDatabase + MigrateDatabase)
        $tenant->domains()->create(['domain' => $validated['domain']]);

        // Provision initial admin user inside the freshly created tenant database.
        tenancy()->initialize($tenant);

        try {
            $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);

            $user = User::create([
                'tenant_id' => $tenant->id,
                'name'      => $validated['admin_name'],
                'email'     => $validated['admin_email'],
                'password'  => Hash::make($validated['admin_password']),
                'is_active' => true,
            ]);

            $user->assignRole($adminRole);
        } finally {
            tenancy()->end();
        }

        return redirect()->route('admin.tenants.index')->with('success', 'Tenant created. Admin account provisioned for ' . $validated['admin_email'] . '.');
    }

    public function show(Tenant $tenant)
    {
        return view('admin.tenants.index', compact('tenant'));
    }

    public function edit(Tenant $tenant)
    {
        return view('admin.tenants.index', compact('tenant'));
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

        $tenant->update([
            'name'          => $validated['name'],
            'status'        => $validated['status'],
            'plan'          => $validated['plan'] ?? $tenant->plan,
            'business_type' => $validated['business_type'] ?? $tenant->business_type,
        ]);

        $tenant->email   = $validated['email']   ?? null;
        $tenant->phone   = $validated['phone']   ?? null;
        $tenant->address = $validated['address'] ?? null;
        $tenant->save();

        return redirect()->route('admin.tenants.index')->with('success', 'Tenant updated successfully.');
    }

    public function destroy(Tenant $tenant)
    {
        $tenant->delete();

        return redirect()->route('admin.tenants.index')->with('success', 'Tenant deleted successfully.');
    }
}
