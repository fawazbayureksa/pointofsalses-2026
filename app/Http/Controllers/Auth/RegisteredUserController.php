<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
            'name'          => ['required', 'string', 'max:255'],
            'business_name' => ['nullable', 'string', 'max:255'],
            'email'         => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password'      => ['required', 'confirmed', 'min:8'],
        ]);

        $businessName = $request->filled('business_name')
            ? $request->business_name
            : $request->name . "'s Business";

        $baseSlug = Str::slug($businessName) ?: 'business';

        [$tenant, $user] = DB::transaction(function () use ($request, $businessName, $baseSlug) {
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
                'password'  => Hash::make($request->password),
                'is_active' => true,
            ]);

            $user->assignRole($adminRole);

            return [$tenant, $user];
        });

        $user->assignRole($adminRole);

        $user->assignRole($adminRole);

        event(new Registered($user));

        Auth::login($user);

        return redirect('/admin/dashboard');
    }
}
