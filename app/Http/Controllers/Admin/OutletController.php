<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Outlet;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class OutletController extends Controller
{
    public function index()
    {
        $outlets = Outlet::latest()->paginate(15);
        $users = User::orderBy('name')->get();
        return view('admin.outlets.index', compact('outlets', 'users'));
    }

    public function create()
    {
        return redirect()->route('admin.outlets.index');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'       => 'required|string|max:255',
            'code'       => 'nullable|string|max:50',
            'phone'      => 'nullable|string|max:20',
            'email'      => 'nullable|email|max:255',
            'address'    => 'nullable|string',
            'city'       => 'nullable|string|max:100',
            'is_active'  => 'boolean',
            'manager_id' => 'nullable|exists:users,id',
        ]);

        // Auto-generate code if not provided
        if (empty($validated['code'])) {
            $base = strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $validated['name']), 0, 4));
            $validated['code'] = $base . '-' . strtoupper(Str::random(4));
        }

        unset($validated['manager_id']);
        $outlet = Outlet::create($validated);

        if ($request->filled('manager_id')) {
            $outlet->users()->attach($request->manager_id, ['is_default' => true]);
        }

        return redirect()->route('admin.outlets.index')->with('success', 'Outlet created successfully.');
    }

    public function show(Outlet $outlet)
    {
        $outlet->load(['users.roles', 'products']);
        return view('admin.outlets.show', compact('outlet'));
    }

    public function edit(Outlet $outlet)
    {
        return redirect()->route('admin.outlets.index');
    }

    public function update(Request $request, Outlet $outlet)
    {
        $validated = $request->validate([
            'name'      => 'required|string|max:255',
            'code'      => 'nullable|string|max:50',
            'phone'     => 'nullable|string|max:20',
            'email'     => 'nullable|email|max:255',
            'address'   => 'nullable|string',
            'city'      => 'nullable|string|max:100',
            'is_active' => 'boolean',
        ]);

        $outlet->update($validated);

        return redirect()->route('admin.outlets.index')->with('success', 'Outlet updated successfully.');
    }

    public function destroy(Outlet $outlet)
    {
        $outlet->delete();

        return redirect()->route('admin.outlets.index')->with('success', 'Outlet deleted successfully.');
    }
}
