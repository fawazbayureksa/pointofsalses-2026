<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Outlet;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with(['category', 'outlets'])->latest()->paginate(15);
        return view('admin.products.index', compact('products'));
    }

    public function create()
    {
        $outlets = Outlet::where('is_active', true)->get();
        return view('admin.products.index', compact('outlets'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'                => 'required|string|max:255',
            'sku'                 => 'nullable|string|max:100',
            'barcode'             => 'nullable|string|max:100',
            'description'         => 'nullable|string',
            'category'            => 'nullable|string|max:100',
            'price'               => 'required|numeric|min:0',
            'cost_price'          => 'nullable|numeric|min:0',
            'stock'               => 'nullable|numeric|min:0',
            'low_stock_threshold' => 'nullable|numeric|min:0',
            'unit'                => 'nullable|string|max:20',
            'is_active'           => 'boolean',
            'track_stock'         => 'boolean',
            'outlet_id'           => 'nullable|exists:outlets,id',
            'image'               => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('products', 'public');
        }

        Product::create($validated);

        return redirect()->route('admin.products.index')->with('success', 'Product created successfully.');
    }

    public function show(Product $product)
    {
        return view('admin.products.index', compact('product'));
    }

    public function edit(Product $product)
    {
        $outlets = Outlet::where('is_active', true)->get();
        return view('admin.products.index', compact('product', 'outlets'));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name'                => 'required|string|max:255',
            'sku'                 => 'nullable|string|max:100',
            'barcode'             => 'nullable|string|max:100',
            'description'         => 'nullable|string',
            'category'            => 'nullable|string|max:100',
            'price'               => 'required|numeric|min:0',
            'cost_price'          => 'nullable|numeric|min:0',
            'stock'               => 'nullable|numeric|min:0',
            'low_stock_threshold' => 'nullable|numeric|min:0',
            'unit'                => 'nullable|string|max:20',
            'is_active'           => 'boolean',
            'track_stock'         => 'boolean',
            'outlet_id'           => 'nullable|exists:outlets,id',
            'image'               => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('products', 'public');
        }

        $product->update($validated);

        return redirect()->route('admin.products.index')->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return redirect()->route('admin.products.index')->with('success', 'Product deleted successfully.');
    }

    public function stock()
    {
        $products = Product::with('outlets')->where('track_stock', true)->latest()->paginate(20);
        return view('admin.inventory.stock', compact('products'));
    }

    public function movements()
    {
        return view('admin.inventory.movements');
    }

    public function adjustStock(Request $request, Product $product)
    {
        $validated = $request->validate([
            'outlet_id' => 'required|exists:outlets,id',
            'quantity'  => 'required|numeric',
            'reason'    => 'nullable|string|max:255',
        ]);

        $product->outlets()->syncWithoutDetaching([
            $validated['outlet_id'] => ['stock' => \DB::raw("stock + {$validated['quantity']}")],
        ]);

        return redirect()->back()->with('success', 'Stock adjusted successfully.');
    }

    public function transferStock(Request $request)
    {
        $validated = $request->validate([
            'product_id'      => 'required|exists:products,id',
            'from_outlet_id'  => 'required|exists:outlets,id',
            'to_outlet_id'    => 'required|exists:outlets,id|different:from_outlet_id',
            'quantity'        => 'required|numeric|min:1',
        ]);

        return redirect()->back()->with('success', 'Stock transferred successfully.');
    }
}
