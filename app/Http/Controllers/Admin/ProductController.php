<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Outlet;
use App\Models\Product;
use App\Models\StockMovement;
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

    public function movements(Request $request)
    {
        $query = StockMovement::with(['product', 'outlet', 'user'])->latest();

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }
        if ($request->filled('outlet_id')) {
            $query->where('outlet_id', $request->outlet_id);
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $movements = $query->paginate(25)->withQueryString();
        $products  = Product::where('is_active', true)->orderBy('name')->get();
        $outlets   = Outlet::where('is_active', true)->orderBy('name')->get();

        return view('admin.inventory.movements', compact('movements', 'products', 'outlets'));
    }

    public function adjustStock(Request $request, Product $product)
    {
        $validated = $request->validate([
            'outlet_id' => 'required|exists:outlets,id',
            'quantity'  => 'required|numeric',
            'reason'    => 'nullable|string|max:255',
        ]);

        $outletId = (int) $validated['outlet_id'];
        $change   = (float) $validated['quantity'];

        $pivot  = $product->outlets()->wherePivot('outlet_id', $outletId)->first();
        $before = $pivot ? (float) $pivot->pivot->stock : 0.0;

        if ($pivot) {
            $after = max(0, $before + $change);
            $product->outlets()->updateExistingPivot($outletId, ['stock' => $after]);
        } else {
            $after = max(0, $change);
            $product->outlets()->attach($outletId, [
                'stock'               => $after,
                'low_stock_threshold' => 0,
            ]);
        }

        StockMovement::record(
            product: $product,
            outletId: $outletId,
            type: 'adjustment',
            before: $before,
            change: $after - $before,
            after: $after,
            reason: $validated['reason'] ?? null,
        );

        return redirect()->back()->with('success', 'Stock updated successfully.');
    }

    public function transferStock(Request $request)
    {
        $validated = $request->validate([
            'product_id'      => 'required|exists:products,id',
            'from_outlet_id'  => 'required|exists:outlets,id',
            'to_outlet_id'    => 'required|exists:outlets,id|different:from_outlet_id',
            'quantity'        => 'required|numeric|min:0.001',
        ]);

        $product  = Product::findOrFail($validated['product_id']);
        $qty      = (float) $validated['quantity'];
        $fromId   = (int) $validated['from_outlet_id'];
        $toId     = (int) $validated['to_outlet_id'];

        $fromPivot  = $product->outlets()->wherePivot('outlet_id', $fromId)->first();
        $fromBefore = $fromPivot ? (float) $fromPivot->pivot->stock : 0.0;

        if ($fromBefore < $qty) {
            return redirect()->back()->withErrors(['quantity' => 'Insufficient stock at source outlet.']);
        }

        $fromAfter = $fromBefore - $qty;
        $product->outlets()->updateExistingPivot($fromId, ['stock' => $fromAfter]);

        $toPivot  = $product->outlets()->wherePivot('outlet_id', $toId)->first();
        $toBefore = $toPivot ? (float) $toPivot->pivot->stock : 0.0;
        $toAfter  = $toBefore + $qty;

        if ($toPivot) {
            $product->outlets()->updateExistingPivot($toId, ['stock' => $toAfter]);
        } else {
            $product->outlets()->attach($toId, ['stock' => $toAfter, 'low_stock_threshold' => 0]);
        }

        StockMovement::record($product, $fromId, 'transfer_out', $fromBefore, -$qty, $fromAfter);
        StockMovement::record($product, $toId,   'transfer_in',  $toBefore,   $qty,  $toAfter);

        return redirect()->back()->with('success', 'Stock transferred successfully.');
    }
}
