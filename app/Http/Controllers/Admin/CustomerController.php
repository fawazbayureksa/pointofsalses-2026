<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index()
    {
        $customers = Customer::latest()->paginate(15);

        return view('admin.customers.index', compact('customers'));
    }

    public function create()
    {
        return view('admin.customers.index');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'            => 'required|string|max:255',
            'email'           => 'nullable|email|max:255',
            'phone'           => 'nullable|string|max:20',
            'address'         => 'nullable|string',
            'customer_code'   => 'nullable|string|max:50',
            'gender'          => 'nullable|in:male,female',
            'date_of_birth'   => 'nullable|date',
            'notes'           => 'nullable|string',
            'status'          => 'nullable|in:active,inactive',
            'member_since'    => 'nullable|date',
            'membership_tier' => 'nullable|in:regular,silver,gold,platinum',
        ]);

        $validated['is_active'] = ($validated['status'] ?? 'active') === 'active';
        unset($validated['status']);

        Customer::create($validated);

        return redirect()->route('admin.customers.index')->with('success', 'Customer created successfully.');
    }

    public function show(Customer $customer)
    {
        $customer->load('orders');

        return view('admin.customers.show', compact('customer'));
    }

    public function edit(Customer $customer)
    {
        return view('admin.customers.index', compact('customer'));
    }

    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'name'            => 'required|string|max:255',
            'email'           => 'nullable|email|max:255',
            'phone'           => 'nullable|string|max:20',
            'address'         => 'nullable|string',
            'customer_code'   => 'nullable|string|max:50',
            'gender'          => 'nullable|in:male,female',
            'date_of_birth'   => 'nullable|date',
            'notes'           => 'nullable|string',
            'status'          => 'nullable|in:active,inactive',
            'member_since'    => 'nullable|date',
            'membership_tier' => 'nullable|in:regular,silver,gold,platinum',
        ]);

        $validated['is_active'] = ($validated['status'] ?? 'active') === 'active';
        unset($validated['status']);

        $customer->update($validated);

        return redirect()->route('admin.customers.index')->with('success', 'Customer updated successfully.');
    }

    public function enroll(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'member_since'    => 'required|date',
            'membership_tier' => 'required|in:regular,silver,gold,platinum',
        ]);

        $customer->update($validated);

        return redirect()->route('admin.customers.show', $customer)->with('success', 'Customer enrolled as member successfully.');
    }

    public function unenroll(Customer $customer)
    {
        $customer->update([
            'member_since'    => null,
            'membership_tier' => 'regular',
        ]);

        return redirect()->route('admin.customers.show', $customer)->with('success', 'Membership removed successfully.');
    }

    public function destroy(Customer $customer)
    {
        $customer->delete();

        return redirect()->route('admin.customers.index')->with('success', 'Customer deleted successfully.');
    }
}
