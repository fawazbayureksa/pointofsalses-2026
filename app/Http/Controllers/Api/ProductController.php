<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
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
        $outletId = $request->outlet_id;

        $products = Product::active()
            ->with(['category', 'outlets'])
            ->when(
                $request->search,
                fn($q, $s) =>
                $q->where(
                    fn($q) =>
                    $q->where('name', 'like', "%{$s}%")
                        ->orWhere('sku', 'like', "%{$s}%")
                        ->orWhere('barcode', 'like', "%{$s}%")
                )
            )
            ->when($request->category_id, fn($q, $c) => $q->where('category_id', $c))
            ->when(
                $request->category,
                fn($q, $name) =>
                $q->whereHas('category', fn($q) => $q->where('name', 'like', "%{$name}%"))
            )
            ->when(
                $outletId,
                fn($q, $id) =>
                $q->whereHas('outlets', fn($q) => $q->where('outlets.id', $id))
            )
            ->paginate($request->per_page ?? 20);

        $products->getCollection()->transform(function ($product) use ($outletId) {
            $stock = null;
            if ($outletId) {
                $pivot = $product->outlets->firstWhere('id', $outletId);
                $stock = $pivot ? (float) $pivot->pivot->stock : null;
            }

            return [
                'id'          => $product->id,
                'name'        => $product->name,
                'sku'         => $product->sku,
                'barcode'     => $product->barcode,
                'description' => $product->description,
                'price'       => $product->price,
                'cost_price'  => $product->cost_price,
                'unit'        => $product->unit,
                'image'       => $product->image ? \Illuminate\Support\Facades\Storage::url($product->image) : null,
                'category_id' => $product->category_id,
                'category'    => $product->category?->name,
                'track_stock' => $product->track_stock,
                'stock'       => $stock,
                'is_active'   => $product->is_active,
            ];
        });

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
     * GET /api/products/barcode/{barcode}
     *
     * Exact-match barcode lookup for scanner hardware.
     */
    public function findByBarcode(string $barcode): JsonResponse
    {
        $product = Product::active()
            ->where('barcode', $barcode)
            ->with(['category', 'outlets'])
            ->first();

        if (! $product) {
            return response()->json(['message' => 'Product not found.'], 404);
        }

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
            'category_id'         => ['nullable', 'exists:categories,id'],
            'category'            => ['nullable', 'string'],
            'price'               => ['required', 'numeric', 'min:0'],
            'cost_price'          => ['nullable', 'numeric', 'min:0'],
            'stock'               => ['nullable', 'numeric', 'min:0'],
            'low_stock_threshold' => ['nullable', 'numeric', 'min:0'],
            'unit'                => ['nullable', 'string'],
            'outlet_id'           => ['nullable', 'exists:outlets,id'],
            'track_stock'         => ['boolean'],
        ]);

        if (empty($data['category_id']) && !empty($data['category'])) {
            $cat = Category::where('name', $data['category'])->first();
            if ($cat) {
                $data['category_id'] = $cat->id;
            }
        }
        unset($data['category']);

        $product = Product::create($data);

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
            'category_id'         => ['sometimes', 'exists:categories,id'],
            'category'            => ['sometimes', 'string'],
            'is_active'           => ['sometimes', 'boolean'],
        ]);

        if (empty($data['category_id']) && !empty($data['category'])) {
            $cat = Category::where('name', $data['category'])->first();
            if ($cat) {
                $data['category_id'] = $cat->id;
            }
        }
        unset($data['category']);

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
