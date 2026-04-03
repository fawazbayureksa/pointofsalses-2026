<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class RegisteredUserController extends Controller
{
    public function create()
    {
        return view('auth.register');
    }

    public function store(Request $request)
    {
        $request->validate([
            'business_name' => ['required', 'string', 'max:255'],
            'name'          => ['required', 'string', 'max:255'],
            'email'         => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password'      => ['required', 'confirmed', 'min:8'],
        ]);

        // Create the tenant with a 14-day free trial of the Professional plan.
        $baseSlug = Str::slug($request->business_name);
        $slug     = $baseSlug . '-' . Str::lower(Str::random(6));

        // Ensure uniqueness (retry on collision – extremely rare).
        while (\App\Models\Tenant::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . Str::lower(Str::random(6));
        }
        $tenant = Tenant::create([
            'name'          => $request->business_name,
            'slug'          => $slug,
            'email'         => $request->email,
            'plan'          => 'professional',
            'status'        => 'active',
            'trial_ends_at' => now()->addDays(14),
        ]);

        $adminRole = Role::firstOrCreate(['name' => 'tenant_admin', 'guard_name' => 'web']);

        $user = User::create([
            'tenant_id' => $tenant->id,
            'name'      => $request->name,
            'email'     => $request->email,
            'password'  => Hash::make($request->password),
            'is_active' => true,
        ]);

        $user->assignRole($adminRole);

        event(new Registered($user));

        Auth::login($user);

        return redirect('/admin/dashboard');
    }
}
