<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Outlet;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    public function index()
    {
        $categories = \App\Models\Category::where('is_active', true)->get();
        $outlets = Outlet::where('is_active', true)->get();
        $products = Product::with(['category', 'outlets'])->latest()->paginate(15);
        return view('admin.products.index', compact('products', 'categories', 'outlets'));
    }

    public function create()
    {
        return redirect()->route('admin.products.index');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'                => 'required|string|max:255',
            'sku'                 => 'nullable|string|max:100',
            'barcode'             => 'nullable|string|max:100',
            'description'         => 'nullable|string',
            'category_id'         => 'nullable|exists:categories,id',
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

        $outletId = $validated['outlet_id'] ?? null;
        $stock = $validated['stock'] ?? 0;
        $threshold = $validated['low_stock_threshold'] ?? 0;

        $product = Product::create(Arr::except($validated, ['outlet_id', 'stock', 'low_stock_threshold']));

        if ($outletId) {
            $product->outlets()->attach($outletId, [
                'stock'               => $stock,
                'low_stock_threshold' => $threshold,
            ]);
        }

        return redirect()->route('admin.products.index')->with('success', 'Product created successfully.');
    }

    public function show(Product $product)
    {
        $product->load(['category', 'outlets']);
        return view('admin.products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        return redirect()->route('admin.products.index');
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name'                => 'required|string|max:255',
            'sku'                 => 'nullable|string|max:100',
            'barcode'             => 'nullable|string|max:100',
            'description'         => 'nullable|string',
            'category_id'         => 'nullable|exists:categories,id',
            'price'               => 'required|numeric|min:0',
            'cost_price'          => 'nullable|numeric|min:0',
            'unit'                => 'nullable|string|max:20',
            'is_active'           => 'boolean',
            'track_stock'         => 'boolean',
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
            $validated['outlet_id'] => ['stock' => DB::raw("stock + {$validated['quantity']}")],
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
