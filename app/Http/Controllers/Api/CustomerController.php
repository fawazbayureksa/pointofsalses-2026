<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    /**
     * GET /api/customers
     */
    public function index(Request $request): JsonResponse
    {
        $query = Customer::active()
            ->when($request->search, fn($q, $s) => $q->where('name', 'like', "%{$s}%")
                ->orWhere('email', 'like', "%{$s}%")
                ->orWhere('phone', 'like', "%{$s}%"))
            ->latest();

        if ($request->boolean('all')) {
            return response()->json($query->get());
        }

        return response()->json($query->paginate($request->per_page ?? 20));
    }

    /**
     * GET /api/customers/{customer}
     */
    public function show(Customer $customer): JsonResponse
    {
        return response()->json($customer->load('orders'));
    }

    /**
     * POST /api/customers
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'             => ['required', 'string', 'max:255'],
            'email'            => ['nullable', 'email', 'max:255', 'unique:customers'],
            'phone'            => ['nullable', 'string', 'max:20'],
            'address'          => ['nullable', 'string'],
            'gender'           => ['nullable', 'string', 'in:male,female'],
            'date_of_birth'    => ['nullable', 'date'],
            'notes'            => ['nullable', 'string'],
            'membership_tier'  => ['nullable', 'string', 'in:regular,silver,gold,platinum'],
            'member_since'     => ['nullable', 'date'],
        ]);

        $customer = Customer::create($data);

        return response()->json($customer, 201);
    }

    /**
     * PUT /api/customers/{customer}
     */
    public function update(Request $request, Customer $customer): JsonResponse
    {
        $data = $request->validate([
            'name'             => ['sometimes', 'string', 'max:255'],
            'email'            => ['sometimes', 'nullable', 'email', 'max:255', 'unique:customers,email,' . $customer->id],
            'phone'            => ['sometimes', 'nullable', 'string', 'max:20'],
            'address'          => ['sometimes', 'nullable', 'string'],
            'gender'           => ['sometimes', 'nullable', 'string', 'in:male,female'],
            'date_of_birth'    => ['sometimes', 'nullable', 'date'],
            'notes'            => ['sometimes', 'nullable', 'string'],
            'membership_tier'  => ['sometimes', 'nullable', 'string', 'in:regular,silver,gold,platinum'],
            'member_since'     => ['sometimes', 'nullable', 'date'],
            'is_active'        => ['sometimes', 'boolean'],
        ]);

        $customer->update($data);

        return response()->json($customer->refresh());
    }

    /**
     * DELETE /api/customers/{customer}
     */
    public function destroy(Customer $customer): JsonResponse
    {
        $customer->delete();

        return response()->json(null, 204);
    }
}
