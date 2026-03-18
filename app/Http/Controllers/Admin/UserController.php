<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Outlet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with(['roles', 'outlets'])->latest()->paginate(15);
        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        $roles   = Role::all();
        $outlets = Outlet::where('is_active', true)->get();
        return view('admin.users.index', compact('roles', 'outlets'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'      => 'required|string|max:255',
            'email'     => 'required|email|unique:users,email',
            'password'  => 'required|string|min:8|confirmed',
            'phone'     => 'nullable|string|max:20',
            'outlet_id' => 'nullable|exists:outlets,id',
            'is_active' => 'boolean',
            'role'      => 'nullable|string|exists:roles,name',
        ]);

        $user = User::create([
            'name'      => $validated['name'],
            'email'     => $validated['email'],
            'password'  => Hash::make($validated['password']),
            'phone'     => $validated['phone'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        if (!empty($validated['outlet_id'])) {
            $user->outlets()->attach($validated['outlet_id'], ['is_default' => true]);
        }

        if (!empty($validated['role'])) {
            $user->assignRole($validated['role']);
        }

        return redirect()->route('admin.users.index')->with('success', 'User created successfully.');
    }

    public function show(User $user)
    {
        return view('admin.users.index', compact('user'));
    }

    public function edit(User $user)
    {
        $roles   = Role::all();
        $outlets = Outlet::where('is_active', true)->get();
        return view('admin.users.index', compact('user', 'roles', 'outlets'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name'      => 'required|string|max:255',
            'email'     => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'phone'     => 'nullable|string|max:20',
            'outlet_id' => 'nullable|exists:outlets,id',
            'is_active' => 'boolean',
            'role'      => 'nullable|string|exists:roles,name',
        ]);

        $user->update([
            'name'      => $validated['name'],
            'email'     => $validated['email'],
            'phone'     => $validated['phone'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        if (!empty($validated['outlet_id'])) {
            $defaultOutlet = $user->outlets()->wherePivot('is_default', true)->first();
            if ($defaultOutlet) {
                $user->outlets()->updateExistingPivot($defaultOutlet->id, ['is_default' => false]);
            }
            $user->outlets()->syncWithoutDetaching([$validated['outlet_id'] => ['is_default' => true]]);
        }

        if (!empty($validated['role'])) {
            $user->syncRoles([$validated['role']]);
        }

        return redirect()->route('admin.users.index')->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
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
            'name'   => 'required|string|max:255',
            'email'  => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'phone'  => 'nullable|string|max:20',
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
            'password'         => 'required|string|min:8|confirmed',
        ]);

        Auth::user()->update(['password' => Hash::make($request->password)]);

        return redirect()->route('admin.profile')->with('success', 'Password updated successfully.');
    }
}
