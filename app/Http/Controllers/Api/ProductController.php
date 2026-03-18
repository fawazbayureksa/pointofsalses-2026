<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * GET /api/products
     */
    public function index(Request $request): JsonResponse
    {
        $query = Product::active()
            ->when($request->search, fn($q, $s) => $q->where('name', 'like', "%{$s}%")
                ->orWhere('sku', 'like', "%{$s}%")
                ->orWhere('barcode', 'like', "%{$s}%"))
            ->when($request->category, fn($q, $c) => $q->where('category', $c))
            ->when($request->outlet_id, fn($q, $id) => $q->where('outlet_id', $id));

        $products = $query->paginate($request->per_page ?? 20);

        return response()->json($products);
    }

    /**
     * GET /api/products/{product}
     */
    public function show(Product $product): JsonResponse
    {
        return response()->json($product);
    }

    /**
     * POST /api/products
     */
    public function store(Request $request): JsonResponse
    {
        // $this->authorize('manage_products');

        $data = $request->validate([
            'name'                => ['required', 'string', 'max:255'],
            'sku'                 => ['nullable', 'string', 'unique:products'],
            'barcode'             => ['nullable', 'string'],
            'description'         => ['nullable', 'string'],
            'category'            => ['nullable', 'string'],
            'price'               => ['required', 'numeric', 'min:0'],
            'cost_price'          => ['nullable', 'numeric', 'min:0'],
            'stock'               => ['nullable', 'numeric', 'min:0'],
            'low_stock_threshold' => ['nullable', 'numeric', 'min:0'],
            'unit'                => ['nullable', 'string'],
            'outlet_id'           => ['nullable', 'exists:outlets,id'],
            'track_stock'         => ['boolean'],
        ]);

        $product = Product::create(['tenant_id' => tenant('id'), ...$data]);

        return response()->json($product, 201);
    }

    /**
     * PUT /api/products/{product}
     */
    public function update(Request $request, Product $product): JsonResponse
    {
        // $this->authorize('manage_products');

        $data = $request->validate([
            'name'                => ['sometimes', 'string', 'max:255'],
            'price'               => ['sometimes', 'numeric', 'min:0'],
            'cost_price'          => ['sometimes', 'numeric', 'min:0'],
            'stock'               => ['sometimes', 'numeric', 'min:0'],
            'low_stock_threshold' => ['sometimes', 'numeric', 'min:0'],
            'category'            => ['sometimes', 'string'],
            'is_active'           => ['sometimes', 'boolean'],
        ]);

        $product->update($data);

        return response()->json($product->refresh());
    }

    /**
     * DELETE /api/products/{product}
     */
    public function destroy(Product $product): JsonResponse
    {
        // $this->authorize('manage_products');

        $product->delete();

        return response()->json(null, 204);
    }
}
