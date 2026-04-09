<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Outlet;
use App\Models\Product;

class PosController extends Controller
{
    public function index()
    {
        $outlets    = Outlet::where('is_active', true)->get();
        $categories = Category::where('is_active', true)->orderBy('name')->get();
        $products   = Product::where('is_active', true)
            ->with(['category', 'outlets'])
            ->orderBy('name')
            ->get();

        $customersJson = Customer::where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(fn($c) => [
                'id'             => $c->id,
                'name'           => $c->name,
                'phone'          => $c->phone,
                'is_member'      => $c->is_member,
                'membership_tier' => $c->membership_tier ?? 'regular',
                'loyalty_points' => (int) $c->loyalty_points,
            ])
            ->values();

        return view('admin.pos.index', compact('outlets', 'customersJson', 'categories', 'products'));
    }
}
