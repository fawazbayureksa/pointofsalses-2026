<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\SupplierPriceHistory;
use App\Models\SupplierProduct;
use App\Services\ProcurementService;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function __construct(
        private ProcurementService $procurementService
    ) {}

    public function index(Request $request)
    {
        $query = Supplier::withCount(['supplierProducts', 'purchaseOrders', 'invoices'])->latest();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('company_name', 'like', "%{$search}%")
                    ->orWhere('contact_person', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $suppliers = $query->paginate(15)->withQueryString();

        return view('admin.suppliers.index', compact('suppliers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'nullable|string|max:50',
            'name' => 'required|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'npwp' => 'nullable|string|max:50',
            'tax_info' => 'nullable|string|max:255',
            'bank_name' => 'nullable|string|max:100',
            'bank_account_number' => 'nullable|string|max:100',
            'bank_account_holder' => 'nullable|string|max:255',
            'default_payment_terms' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
            'notes' => 'nullable|string',
        ]);

        $tenantId = $request->user()?->tenant_id;
        $validated['code'] = ! empty($validated['code'])
            ? $validated['code']
            : Supplier::generateCode($tenantId);
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['default_payment_terms'] = (int) ($validated['default_payment_terms'] ?? 0);

        Supplier::create($validated);

        return redirect()->route('admin.suppliers.index')
            ->with('success', 'Supplier berhasil ditambahkan.');
    }

    public function show(Supplier $supplier)
    {
        $supplier->load([
            'supplierProducts.product.category',
            'purchaseOrders' => fn ($q) => $q->with('outlet')->latest()->limit(15),
            'goodsReceipts' => fn ($q) => $q->with(['outlet', 'purchaseOrder'])->latest()->limit(15),
            'invoices' => fn ($q) => $q->latest()->limit(15),
            'payments' => fn ($q) => $q->with('invoice')->latest()->limit(15),
            'priceHistories' => fn ($q) => $q->with(['product', 'changedBy'])->latest()->limit(20),
        ]);

        $products = Product::where('is_active', true)->orderBy('name')->get();

        return view('admin.suppliers.show', compact('supplier', 'products'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'npwp' => 'nullable|string|max:50',
            'tax_info' => 'nullable|string|max:255',
            'bank_name' => 'nullable|string|max:100',
            'bank_account_number' => 'nullable|string|max:100',
            'bank_account_holder' => 'nullable|string|max:255',
            'default_payment_terms' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
            'notes' => 'nullable|string',
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $validated['default_payment_terms'] = (int) ($validated['default_payment_terms'] ?? 0);

        $supplier->update($validated);

        return redirect()->back()->with('success', 'Data supplier berhasil diperbarui.');
    }

    public function destroy(Supplier $supplier)
    {
        $supplier->delete();

        return redirect()->route('admin.suppliers.index')
            ->with('success', 'Supplier berhasil dihapus.');
    }

    /**
     * Supplier Products Mapping Catalog & Price Comparison
     */
    public function catalog(Request $request)
    {
        $query = SupplierProduct::with(['supplier', 'product.category'])->latest();

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('supplier_sku', 'like', "%{$search}%")
                    ->orWhereHas('product', fn ($pq) => $pq->where('name', 'like', "%{$search}%")->orWhere('sku', 'like', "%{$search}%"))
                    ->orWhereHas('supplier', fn ($sq) => $sq->where('name', 'like', "%{$search}%"));
            });
        }

        $mappings = $query->paginate(20)->withQueryString();
        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();
        $products = Product::where('is_active', true)->orderBy('name')->get();
        $priceHistories = SupplierPriceHistory::with(['supplier', 'product', 'changedBy'])
            ->latest('recorded_at')
            ->limit(15)
            ->get();

        return view('admin.suppliers.catalog', compact('mappings', 'suppliers', 'products', 'priceHistories'));
    }

    public function storeMapping(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'product_id' => 'required|exists:products,id',
            'supplier_sku' => 'nullable|string|max:100',
            'purchase_price' => 'required|numeric|min:0',
            'is_primary' => 'nullable|boolean',
            'min_order_qty' => 'nullable|numeric|min:0.001',
            'lead_time_days' => 'nullable|integer|min:0',
            'purchase_unit' => 'nullable|string|max:50',
            'conversion_factor' => 'nullable|numeric|min:0.001',
            'notes' => 'nullable|string',
        ]);

        $validated['is_primary'] = $request->boolean('is_primary');

        $this->procurementService->upsertSupplierProduct($validated);

        return redirect()->back()->with('success', 'Mapping produk supplier & harga beli berhasil disimpan.');
    }

    public function destroyMapping(SupplierProduct $mapping)
    {
        $mapping->delete();

        return redirect()->back()->with('success', 'Mapping produk supplier berhasil dihapus.');
    }
}
