<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /**
     * GET /api/products
     */
    public function index(Request $request): JsonResponse
    {
        $outletId = $request->outlet_id;
        Log::info('Product index requested', ['search' => $request->search, 'category_id' => $request->category_id, 'category' => $request->category, 'outlet_id' => $outletId]);

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
        $data = $product->toArray();
        $data['image'] = $product->image ? Storage::url($product->image) : null;
        Log::info('Showing product', ['id' => $product->id, 'data' => $data]);
        return response()->json($data);
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
        Log::info('Creating product with data: ' . json_encode($request->all()));
        try {
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
                'track_stock'         => ['string', 'in:true,false'],
                'image'               => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:2048'],
            ]);
        } catch (\Exception $e) {
            Log::error('Validation failed for product creation', ['errors' => $e->getMessage()]);
            return response()->json(['message' => 'Invalid data provided.', 'errors' => $e->getMessage()], 422);
        }

        // convert track_stock from string to boolean
        $data['track_stock'] = filter_var($data['track_stock'] ?? false, FILTER_VALIDATE_BOOLEAN);

        if (empty($data['category_id']) && !empty($data['category'])) {
            $cat = Category::where('name', $data['category'])->first();
            if ($cat) {
                $data['category_id'] = $cat->id;
            }
        }
        unset($data['category']);

        if (empty($data['sku'])) {
            $data['sku'] = 'PRD-' . strtoupper(substr(uniqid(), -6));
        }

        // Extract pivot fields – these belong to product_outlet, not products
        $outletId           = $data['outlet_id'] ?? null;
        $stock              = $data['stock'] ?? 0;
        $lowStockThreshold  = $data['low_stock_threshold'] ?? 0;
        unset($data['outlet_id'], $data['stock'], $data['low_stock_threshold']);

        try {
            if ($request->hasFile('image')) {
                $data['image'] = $request->file('image')->store('products', 'public');
            }

            $product = Product::create($data);

            if ($outletId) {
                $product->outlets()->syncWithoutDetaching([
                    $outletId => [
                        'stock'               => $stock,
                        'low_stock_threshold' => $lowStockThreshold,
                    ],
                ]);
            }

            $result = $product->toArray();
            $result['image'] = $product->image ? Storage::url($product->image) : null;
            $result['stock']               = $outletId ? (float) $stock : null;
            $result['low_stock_threshold'] = $outletId ? (float) $lowStockThreshold : null;
            $result['outlet_id']           = $outletId;

            return response()->json($result, 201);
        } catch (\Throwable $e) {
            if (!empty($data['image'])) {
                Storage::disk('public')->delete($data['image']);
            }
            Log::error('Product store failed', ['error' => $e->getMessage()]);

            return response()->json(['message' => 'Failed to create product.'], 500);
        }
    }

    /**
     * PUT /api/products/{product}
     */
    public function update(Request $request, Product $product): JsonResponse
    {
        // $this->authorize('manage_products');
        Log::info('Updating product', ['id' => $product->id, 'data' => $request->all()]);
        try {
            $data = $request->validate([
                'name'                => ['sometimes', 'string', 'max:255'],
                'price'               => ['sometimes', 'numeric', 'min:0'],
                'cost_price'          => ['sometimes', 'numeric', 'min:0'],
                'stock'               => ['sometimes', 'numeric', 'min:0'],
                'low_stock_threshold' => ['sometimes', 'numeric', 'min:0'],
                'outlet_id'           => [
                    'sometimes',
                    'exists:outlets,id',
                    \Illuminate\Validation\Rule::requiredIf(
                        $request->has('stock') || $request->has('low_stock_threshold')
                    ),
                ],
                'category_id'         => ['sometimes', 'exists:categories,id'],
                'category'            => ['sometimes', 'string'],
                'is_active'           => ['sometimes'],
                'image'               => ['sometimes', 'nullable', 'image', 'mimes:jpeg,png,webp', 'max:2048'],
            ]);
        } catch (\Exception $e) {
            Log::error('Validation failed for product update', ['id' => $product->id, 'errors' => $e->getMessage()]);
            return response()->json(['message' => 'Invalid data provided.', 'errors' => $e->getMessage()], 422);
        }

        if (empty($data['category_id']) && !empty($data['category'])) {
            $cat = Category::where('name', $data['category'])->first();
            if ($cat) {
                $data['category_id'] = $cat->id;
            }
        }
        unset($data['category']);

        // Extract pivot fields
        $outletId          = $data['outlet_id'] ?? null;
        $hasStock          = array_key_exists('stock', $data);
        $hasThreshold      = array_key_exists('low_stock_threshold', $data);
        $stock             = $data['stock'] ?? null;
        $lowStockThreshold = $data['low_stock_threshold'] ?? null;
        unset($data['outlet_id'], $data['stock'], $data['low_stock_threshold']);

        try {
            if ($request->hasFile('image')) {
                $oldImage = $product->image;
                $data['image'] = $request->file('image')->store('products', 'public');
            }

            if (isset($data['is_active'])) {
                $data['is_active'] = filter_var($data['is_active'], FILTER_VALIDATE_BOOLEAN);
            }

            $product->update($data);
            $product->refresh();

            if (isset($oldImage) && $oldImage) {
                Storage::disk('public')->delete($oldImage);
            }

            // Update pivot stock values when outlet is specified
            if ($outletId && ($hasStock || $hasThreshold)) {
                $pivotData = [];
                if ($hasStock) {
                    $pivotData['stock'] = $stock;
                }
                if ($hasThreshold) {
                    $pivotData['low_stock_threshold'] = $lowStockThreshold;
                }
                $product->outlets()->syncWithoutDetaching([$outletId => $pivotData]);
            }

            $result = $product->toArray();
            $result['image'] = $product->image ? Storage::url($product->image) : null;

            if ($outletId) {
                $pivot = $product->outlets()->where('outlets.id', $outletId)->first();
                $result['stock']               = $pivot ? (float) $pivot->pivot->stock : null;
                $result['low_stock_threshold'] = $pivot ? (float) $pivot->pivot->low_stock_threshold : null;
                $result['outlet_id']           = $outletId;
            }

            return response()->json($result);
        } catch (\Throwable $e) {
            if (!empty($data['image'])) {
                Storage::disk('public')->delete($data['image']);
            }
            Log::error('Product update failed', ['id' => $product->id, 'error' => $e->getMessage()]);

            return response()->json(['message' => 'Failed to update product.'], 500);
        }
    }

    /**
     * DELETE /api/products/{product}
     */
    public function destroy(Product $product): JsonResponse
    {
        // $this->authorize('manage_products');

        try {
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }

            $product->delete();

            return response()->json(null, 204);
        } catch (\Throwable $e) {
            Log::error('Product delete failed', ['id' => $product->id, 'error' => $e->getMessage()]);

            return response()->json(['message' => 'Failed to delete product.'], 500);
        }
    }

    /**
     * POST /api/products/{product}/image
     *
     * Dedicated endpoint for uploading or replacing a product image.
     * Accepts multipart/form-data with an `image` file field.
     */
    public function uploadImage(Request $request, Product $product): JsonResponse
    {
        // $this->authorize('manage_products');

        $request->validate([
            'image' => ['required', 'image', 'mimes:jpeg,png,webp', 'max:2048'],
        ]);

        try {
            $oldImage = $product->image;
            $path = $request->file('image')->store('products', 'public');

            $product->update(['image' => $path]);

            if ($oldImage) {
                Storage::disk('public')->delete($oldImage);
            }

            return response()->json([
                'image' => Storage::url($path),
            ]);
        } catch (\Throwable $e) {
            if (!empty($path)) {
                Storage::disk('public')->delete($path);
            }
            Log::error('Product image upload failed', ['id' => $product->id, 'error' => $e->getMessage()]);

            return response()->json(['message' => 'Failed to upload image.'], 500);
        }
    }
}
