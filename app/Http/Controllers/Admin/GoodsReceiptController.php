<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GoodsReceipt;
use App\Models\Outlet;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\SupplierInvoice;
use App\Models\SupplierReturn;
use App\Services\ProcurementService;
use Illuminate\Http\Request;

class GoodsReceiptController extends Controller
{
    public function __construct(
        private ProcurementService $procurementService
    ) {}

    public function index(Request $request)
    {
        $query = GoodsReceipt::with(['purchaseOrder', 'supplier', 'outlet', 'receiver', 'items.product'])
            ->latest();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('gr_number', 'like', "%{$search}%")
                    ->orWhere('supplier_delivery_note', 'like', "%{$search}%")
                    ->orWhereHas('purchaseOrder', fn ($pq) => $pq->where('po_number', 'like', "%{$search}%"))
                    ->orWhereHas('supplier', fn ($sq) => $sq->where('name', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('outlet_id')) {
            $query->where('outlet_id', $request->outlet_id);
        }

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        $goodsReceipts = $query->paginate(15)->withQueryString();

        // Eligible POs for receiving goods
        $receivablePurchaseOrders = PurchaseOrder::with(['supplier', 'outlet', 'items.product'])
            ->whereIn('status', [
                PurchaseOrder::STATUS_APPROVED,
                PurchaseOrder::STATUS_ORDERED,
                PurchaseOrder::STATUS_PARTIALLY_RECEIVED,
            ])
            ->latest()
            ->get();

        $supplierReturns = SupplierReturn::with(['supplier', 'outlet', 'items.product', 'invoice'])
            ->latest()
            ->limit(15)
            ->get();

        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();
        $outlets = Outlet::where('is_active', true)->orderBy('name')->get();
        $products = Product::where('is_active', true)->orderBy('name')->get();
        $invoices = SupplierInvoice::whereIn('status', ['unpaid', 'partially_paid', 'overdue'])->latest()->get();

        return view('admin.goods-receipts.index', compact(
            'goodsReceipts',
            'receivablePurchaseOrders',
            'supplierReturns',
            'suppliers',
            'outlets',
            'products',
            'invoices'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'purchase_order_id' => 'required|exists:purchase_orders,id',
            'received_date' => 'required|date',
            'supplier_delivery_note' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.purchase_order_item_id' => 'required|exists:purchase_order_items,id',
            'items.*.quantity_received' => 'required|numeric|min:0',
            'items.*.quantity_damaged' => 'nullable|numeric|min:0',
            'items.*.batch_number' => 'nullable|string|max:100',
            'items.*.expiry_date' => 'nullable|date',
            'items.*.notes' => 'nullable|string|max:255',
        ]);

        $po = PurchaseOrder::findOrFail($validated['purchase_order_id']);
        $gr = $this->procurementService->receiveGoods($po, $validated);

        return redirect()->route('admin.goods-receipts.index')
            ->with('success', "Penerimaan barang {$gr->gr_number} berhasil dicatat dan stok inventori telah diperbarui sesuai barang valid yang diterima.");
    }

    public function storeReturn(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'outlet_id' => 'required|exists:outlets,id',
            'purchase_order_id' => 'nullable|exists:purchase_orders,id',
            'goods_receipt_id' => 'nullable|exists:goods_receipts,id',
            'supplier_invoice_id' => 'nullable|exists:supplier_invoices,id',
            'return_date' => 'required|date',
            'resolution_type' => 'required|in:deduct_payable,replacement,cash_refund',
            'reason' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.001',
            'items.*.unit_cost' => 'required|numeric|min:0',
            'items.*.deduct_stock' => 'nullable|boolean',
            'items.*.condition_notes' => 'nullable|string|max:255',
        ]);

        $return = $this->procurementService->createSupplierReturn($validated);

        return redirect()->route('admin.goods-receipts.index')
            ->with('success', "Retur supplier {$return->return_number} sebesar Rp ".number_format((float) $return->total_refund_amount, 0, ',', '.').' berhasil dicatat.');
    }
}
