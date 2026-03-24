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
        $customers  = Customer::where('is_active', true)->orderBy('name')->get();
        $categories = Category::where('is_active', true)->orderBy('name')->get();
        $products   = Product::where('is_active', true)
            ->with(['category', 'outlets'])
            ->orderBy('name')
            ->get();

        return view('admin.pos.index', compact('outlets', 'customers', 'categories', 'products'));
    }
}
