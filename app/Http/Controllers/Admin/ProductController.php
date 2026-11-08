<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Outlet;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProductController extends Controller
{
    public function index()
    {
        $categories = Category::where('is_active', true)->get();
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
            'name' => 'required|string|max:255',
            'sku' => 'nullable|string|max:100',
            'barcode' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'category_id' => 'nullable|exists:categories,id',
            'price' => 'required|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'stock' => 'nullable|numeric|min:0',
            'low_stock_threshold' => 'nullable|numeric|min:0',
            'unit' => 'nullable|string|max:20',
            'is_active' => 'boolean',
            'track_stock' => 'boolean',
            'outlet_id' => 'nullable|exists:outlets,id',
            'image' => 'nullable|image|max:2048',
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
                'stock' => $stock,
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
            'name' => 'required|string|max:255',
            'sku' => 'nullable|string|max:100',
            'barcode' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'category_id' => 'nullable|exists:categories,id',
            'price' => 'required|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'unit' => 'nullable|string|max:20',
            'is_active' => 'boolean',
            'track_stock' => 'boolean',
            'image' => 'nullable|image|max:2048',
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
        $products = Product::where('is_active', true)->orderBy('name')->get();
        $outlets = Outlet::where('is_active', true)->orderBy('name')->get();

        return view('admin.inventory.movements', compact('movements', 'products', 'outlets'));
    }

    public function adjustStock(Request $request, Product $product)
    {
        $validated = $request->validate([
            'outlet_id' => 'required|exists:outlets,id',
            'quantity' => 'required|numeric',
            'reason' => 'nullable|string|max:255',
        ]);

        $outletId = (int) $validated['outlet_id'];
        $change = (float) $validated['quantity'];

        $pivot = $product->outlets()->wherePivot('outlet_id', $outletId)->first();
        $before = $pivot ? (float) $pivot->pivot->stock : 0.0;

        if ($pivot) {
            $after = max(0, $before + $change);
            $product->outlets()->updateExistingPivot($outletId, ['stock' => $after]);
        } else {
            $after = max(0, $change);
            $product->outlets()->attach($outletId, [
                'stock' => $after,
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
            'product_id' => 'required|exists:products,id',
            'from_outlet_id' => 'required|exists:outlets,id',
            'to_outlet_id' => 'required|exists:outlets,id|different:from_outlet_id',
            'quantity' => 'required|numeric|min:0.001',
        ]);

        $product = Product::findOrFail($validated['product_id']);
        $qty = (float) $validated['quantity'];
        $fromId = (int) $validated['from_outlet_id'];
        $toId = (int) $validated['to_outlet_id'];

        $fromPivot = $product->outlets()->wherePivot('outlet_id', $fromId)->first();
        $fromBefore = $fromPivot ? (float) $fromPivot->pivot->stock : 0.0;

        if ($fromBefore < $qty) {
            return redirect()->back()->withErrors(['quantity' => 'Insufficient stock at source outlet.']);
        }

        $fromAfter = $fromBefore - $qty;
        $product->outlets()->updateExistingPivot($fromId, ['stock' => $fromAfter]);

        $toPivot = $product->outlets()->wherePivot('outlet_id', $toId)->first();
        $toBefore = $toPivot ? (float) $toPivot->pivot->stock : 0.0;
        $toAfter = $toBefore + $qty;

        if ($toPivot) {
            $product->outlets()->updateExistingPivot($toId, ['stock' => $toAfter]);
        } else {
            $product->outlets()->attach($toId, ['stock' => $toAfter, 'low_stock_threshold' => 0]);
        }

        StockMovement::record($product, $fromId, 'transfer_out', $fromBefore, -$qty, $fromAfter);
        StockMovement::record($product, $toId, 'transfer_in', $toBefore, $qty, $toAfter);

        return redirect()->back()->with('success', 'Stock transferred successfully.');
    }

    public function downloadTemplate(): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="products-import-template.csv"',
        ];

        $columns = [
            'name',
            'sku',
            'barcode',
            'category',
            'price',
            'cost_price',
            'unit',
            'is_active',
            'track_stock',
            'outlet',
            'stock',
            'low_stock_threshold',
            'description',
        ];

        $sampleRows = [
            [
                'Espresso',
                'PRD-001',
                '8991234567890',
                'Coffee',
                '25000',
                '12000',
                'cup',
                '1',
                '1',
                'Main Outlet',
                '100',
                '10',
                'Rich single shot espresso',
            ],
            [
                'Butter Croissant',
                'PRD-002',
                '8991234567891',
                'Food',
                '18000',
                '9000',
                'pcs',
                '1',
                '1',
                'Main Outlet',
                '50',
                '5',
                'Freshly baked butter croissant',
            ],
        ];

        return response()->stream(function () use ($columns, $sampleRows) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $columns);
            foreach ($sampleRows as $row) {
                fputcsv($handle, $row);
            }
            fclose($handle);
        }, 200, $headers);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:5120',
            'outlet_id' => 'nullable|exists:outlets,id',
        ]);

        $path = $request->file('file')->getRealPath();
        $handle = fopen($path, 'r');

        if ($handle === false) {
            return redirect()->route('admin.products.index')
                ->withErrors(['file' => 'Unable to read the uploaded CSV file.']);
        }

        $rawHeaders = fgetcsv($handle);
        if (! $rawHeaders) {
            fclose($handle);

            return redirect()->route('admin.products.index')
                ->withErrors(['file' => 'The uploaded CSV file is empty.']);
        }

        $headers = array_map(function ($header) {
            $clean = preg_replace('/^\xEF\xBB\xBF/', '', (string) $header);

            return Str::snake(strtolower(trim($clean)));
        }, $rawHeaders);

        $requiredColumns = ['name', 'sku', 'price'];
        $missingColumns = array_diff($requiredColumns, $headers);

        if (! empty($missingColumns)) {
            fclose($handle);

            return redirect()->route('admin.products.index')
                ->withErrors(['file' => 'Invalid CSV template. Missing required column(s): '.implode(', ', $missingColumns)]);
        }

        $rows = [];
        $lineNumber = 1;
        $errors = [];

        while (($row = fgetcsv($handle)) !== false) {
            $lineNumber++;

            if ($row === [null] || count(array_filter($row, fn ($v) => trim((string) $v) !== '')) === 0) {
                continue;
            }

            $data = [];
            foreach ($headers as $index => $key) {
                if ($key !== '') {
                    $data[$key] = isset($row[$index]) ? trim((string) $row[$index]) : '';
                }
            }

            if (($data['name'] ?? '') === '') {
                $errors[] = "Row {$lineNumber}: Product name is required.";

                continue;
            }

            if (($data['sku'] ?? '') === '') {
                $errors[] = "Row {$lineNumber}: SKU is required.";

                continue;
            }

            if (($data['price'] ?? '') === '' || ! is_numeric($data['price']) || (float) $data['price'] < 0) {
                $errors[] = "Row {$lineNumber}: Price must be a valid non-negative number.";

                continue;
            }

            if (($data['cost_price'] ?? '') !== '' && (! is_numeric($data['cost_price']) || (float) $data['cost_price'] < 0)) {
                $errors[] = "Row {$lineNumber}: Cost price must be a valid non-negative number.";

                continue;
            }

            if (($data['stock'] ?? '') !== '' && (! is_numeric($data['stock']) || (float) $data['stock'] < 0)) {
                $errors[] = "Row {$lineNumber}: Stock must be a valid non-negative number.";

                continue;
            }

            if (($data['low_stock_threshold'] ?? '') !== '' && (! is_numeric($data['low_stock_threshold']) || (float) $data['low_stock_threshold'] < 0)) {
                $errors[] = "Row {$lineNumber}: Low stock threshold must be a valid non-negative number.";

                continue;
            }

            $rows[] = [
                'line' => $lineNumber,
                'name' => $data['name'],
                'sku' => $data['sku'],
                'barcode' => ($data['barcode'] ?? '') !== '' ? $data['barcode'] : null,
                'category' => $data['category'] ?? ($data['category_name'] ?? ''),
                'price' => (float) $data['price'],
                'cost_price' => ($data['cost_price'] ?? '') !== '' ? (float) $data['cost_price'] : null,
                'unit' => ($data['unit'] ?? '') !== '' ? $data['unit'] : 'pcs',
                'is_active' => $this->parseBoolean($data['is_active'] ?? '1', true),
                'track_stock' => $this->parseBoolean($data['track_stock'] ?? '1', true),
                'outlet' => $data['outlet'] ?? ($data['outlet_name'] ?? ''),
                'stock' => ($data['stock'] ?? '') !== '' ? (float) $data['stock'] : null,
                'low_stock_threshold' => ($data['low_stock_threshold'] ?? '') !== '' ? (float) $data['low_stock_threshold'] : 0,
                'description' => ($data['description'] ?? '') !== '' ? $data['description'] : null,
            ];
        }

        fclose($handle);

        if (! empty($errors)) {
            return redirect()->route('admin.products.index')->withErrors($errors);
        }

        if (empty($rows)) {
            return redirect()->route('admin.products.index')
                ->withErrors(['file' => 'No valid product rows found in the CSV file.']);
        }

        $defaultOutlet = $request->filled('outlet_id') ? Outlet::find($request->input('outlet_id')) : null;
        $importedCount = 0;

        DB::transaction(function () use ($rows, $defaultOutlet, &$importedCount) {
            foreach ($rows as $item) {
                $categoryId = null;

                if ($item['category'] !== '') {
                    $categorySlug = Str::slug($item['category']);
                    $category = Category::where(function ($query) use ($item, $categorySlug) {
                        $query->where('name', $item['category'])
                            ->orWhere('slug', $categorySlug);
                    })->first();

                    if (! $category) {
                        $category = Category::create([
                            'name' => $item['category'],
                            'slug' => $categorySlug,
                            'is_active' => true,
                            'sort_order' => 0,
                        ]);
                    }

                    $categoryId = $category->id;
                }

                $product = Product::withTrashed()->where('sku', $item['sku'])->first();

                $attributes = [
                    'category_id' => $categoryId ?? $product?->category_id,
                    'name' => $item['name'],
                    'sku' => $item['sku'],
                    'barcode' => $item['barcode'],
                    'description' => $item['description'],
                    'price' => $item['price'],
                    'cost_price' => $item['cost_price'],
                    'unit' => $item['unit'],
                    'is_active' => $item['is_active'],
                    'track_stock' => $item['track_stock'],
                ];

                if ($product) {
                    if ($product->trashed()) {
                        $product->restore();
                    }
                    $product->update($attributes);
                } else {
                    $product = Product::create($attributes);
                }

                $outlet = null;
                if ($item['outlet'] !== '') {
                    $outlet = Outlet::where(function ($query) use ($item) {
                        $query->where('name', $item['outlet'])
                            ->orWhere('code', $item['outlet']);
                    })->first();
                } elseif ($defaultOutlet) {
                    $outlet = $defaultOutlet;
                }

                if ($outlet && $item['stock'] !== null) {
                    $product->outlets()->syncWithoutDetaching([
                        $outlet->id => [
                            'stock' => $item['stock'],
                            'low_stock_threshold' => $item['low_stock_threshold'],
                        ],
                    ]);
                }

                $importedCount++;
            }
        });

        return redirect()->route('admin.products.index')
            ->with('success', "Successfully imported {$importedCount} products.");
    }

    private function parseBoolean(string $value, bool $default = true): bool
    {
        $normalized = strtolower(trim($value));

        if ($normalized === '') {
            return $default;
        }

        return in_array($normalized, ['1', 'true', 'yes', 'active', 'y'], true);
    }
}
