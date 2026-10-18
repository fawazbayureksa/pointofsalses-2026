<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Outlet;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\SupplierProduct;
use App\Services\ProcurementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PurchaseOrderController extends Controller
{
    public function __construct(
        private ProcurementService $procurementService
    ) {}

    public function index(Request $request)
    {
        $query = PurchaseOrder::with(['supplier', 'outlet', 'creator', 'approver'])
            ->withCount(['items', 'goodsReceipts'])
            ->latest();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('po_number', 'like', "%{$search}%")
                    ->orWhereHas('supplier', fn ($sq) => $sq->where('name', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        if ($request->filled('outlet_id')) {
            $query->where('outlet_id', $request->outlet_id);
        }

        $purchaseOrders = $query->paginate(15)->withQueryString();
        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();
        $outlets = Outlet::where('is_active', true)->orderBy('name')->get();
        $products = Product::where('is_active', true)->orderBy('name')->get();
        $supplierMappings = SupplierProduct::all();
        $reorderSuggestions = $this->procurementService->getReorderSuggestions();

        return view('admin.purchase-orders.index', compact(
            'purchaseOrders',
            'suppliers',
            'outlets',
            'products',
            'supplierMappings',
            'reorderSuggestions'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'outlet_id' => 'required|exists:outlets,id',
            'supplier_id' => 'required|exists:suppliers,id',
            'order_date' => 'required|date',
            'expected_date' => 'nullable|date',
            'payment_terms' => 'nullable|integer|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'shipping_cost' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'submit_action' => 'nullable|in:draft,pending_approval,submit',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity_ordered' => 'required|numeric|min:0.001',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount_amount' => 'nullable|numeric|min:0',
            'items.*.purchase_unit' => 'nullable|string|max:50',
            'items.*.conversion_factor' => 'nullable|numeric|min:0.001',
        ]);

        $po = $this->procurementService->createPurchaseOrder($validated);

        return redirect()->route('admin.purchase-orders.show', $po)
            ->with('success', "Purchase Order {$po->po_number} berhasil dibuat dengan status {$po->status_label}.");
    }

    public function show(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load([
            'supplier',
            'outlet',
            'items.product',
            'goodsReceipts.items.product',
            'goodsReceipts.receiver',
            'invoices.payments',
            'creator',
            'approver',
        ]);

        return view('admin.purchase-orders.show', compact('purchaseOrder'));
    }

    public function submitApproval(PurchaseOrder $purchaseOrder)
    {
        if ($purchaseOrder->status !== PurchaseOrder::STATUS_DRAFT) {
            return redirect()->back()->with('error', 'Hanya PO berstatus Draft yang dapat diajukan.');
        }

        $purchaseOrder->update(['status' => PurchaseOrder::STATUS_PENDING_APPROVAL]);

        return redirect()->back()->with('success', "PO {$purchaseOrder->po_number} diajukan untuk persetujuan (Pending Approval).");
    }

    public function approve(PurchaseOrder $purchaseOrder)
    {
        if (! in_array($purchaseOrder->status, [PurchaseOrder::STATUS_DRAFT, PurchaseOrder::STATUS_PENDING_APPROVAL], true)) {
            return redirect()->back()->with('error', 'PO tidak dapat disetujui pada status saat ini.');
        }

        $purchaseOrder->update([
            'status' => PurchaseOrder::STATUS_APPROVED,
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        return redirect()->back()->with('success', "PO {$purchaseOrder->po_number} telah disetujui (Approved).");
    }

    public function markOrdered(PurchaseOrder $purchaseOrder)
    {
        if (! in_array($purchaseOrder->status, [PurchaseOrder::STATUS_APPROVED, PurchaseOrder::STATUS_DRAFT], true)) {
            return redirect()->back()->with('error', 'PO harus disetujui sebelum dikirim ke supplier.');
        }

        $purchaseOrder->update([
            'status' => PurchaseOrder::STATUS_ORDERED,
            'approved_by' => $purchaseOrder->approved_by ?? Auth::id(),
            'approved_at' => $purchaseOrder->approved_at ?? now(),
            'ordered_at' => now(),
        ]);

        return redirect()->back()->with('success', "PO {$purchaseOrder->po_number} ditandai telah dikirim ke supplier (Ordered).");
    }

    public function cancel(PurchaseOrder $purchaseOrder)
    {
        if (in_array($purchaseOrder->status, [PurchaseOrder::STATUS_RECEIVED, PurchaseOrder::STATUS_PARTIALLY_RECEIVED], true)) {
            return redirect()->back()->with('error', 'PO yang sudah menerima barang tidak dapat dibatalkan.');
        }

        $purchaseOrder->update(['status' => PurchaseOrder::STATUS_CANCELLED]);

        return redirect()->back()->with('success', "PO {$purchaseOrder->po_number} telah dibatalkan.");
    }

    public function print(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load(['supplier', 'outlet', 'items.product', 'creator', 'approver']);

        return view('admin.purchase-orders.print', compact('purchaseOrder'));
    }
}
