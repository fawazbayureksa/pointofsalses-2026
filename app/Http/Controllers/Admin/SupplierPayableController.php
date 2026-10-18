<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GoodsReceipt;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\SupplierInvoice;
use App\Models\SupplierPayment;
use App\Models\SupplierPriceHistory;
use App\Services\ProcurementService;
use Illuminate\Http\Request;

class SupplierPayableController extends Controller
{
    public function __construct(
        private ProcurementService $procurementService
    ) {}

    /**
     * Supplier Invoices & Accounts Payable (AP)
     */
    public function invoices(Request $request)
    {
        $query = SupplierInvoice::with(['supplier', 'purchaseOrder', 'goodsReceipt', 'payments'])
            ->latest();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                    ->orWhere('supplier_invoice_number', 'like', "%{$search}%")
                    ->orWhereHas('supplier', fn ($sq) => $sq->where('name', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('status')) {
            if ($request->status === 'overdue') {
                $query->where('outstanding_balance', '>', 0)->whereDate('due_date', '<', now()->toDateString());
            } else {
                $query->where('status', $request->status);
            }
        }

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        $invoices = $query->paginate(15)->withQueryString();
        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();
        $purchaseOrders = PurchaseOrder::with(['supplier', 'items'])
            ->whereIn('status', [
                PurchaseOrder::STATUS_ORDERED,
                PurchaseOrder::STATUS_PARTIALLY_RECEIVED,
                PurchaseOrder::STATUS_RECEIVED,
            ])
            ->latest()
            ->get();
        $goodsReceipts = GoodsReceipt::with(['supplier', 'purchaseOrder', 'items'])->latest()->limit(50)->get();

        // Summary KPIs
        $totalPayable = (float) SupplierInvoice::whereIn('status', ['unpaid', 'partially_paid', 'overdue'])
            ->sum('outstanding_balance');
        $overduePayable = (float) SupplierInvoice::where('outstanding_balance', '>', 0)
            ->whereDate('due_date', '<', now()->toDateString())
            ->sum('outstanding_balance');
        $paidThisMonth = (float) SupplierPayment::whereMonth('payment_date', now()->month)
            ->whereYear('payment_date', now()->year)
            ->sum('amount');

        return view('admin.supplier-payables.invoices', compact(
            'invoices',
            'suppliers',
            'purchaseOrders',
            'goodsReceipts',
            'totalPayable',
            'overduePayable',
            'paidThisMonth'
        ));
    }

    public function storeInvoice(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'purchase_order_id' => 'nullable|exists:purchase_orders,id',
            'goods_receipt_id' => 'nullable|exists:goods_receipts,id',
            'supplier_invoice_number' => 'nullable|string|max:100',
            'invoice_date' => 'required|date',
            'due_date' => 'nullable|date',
            'payment_terms' => 'nullable|integer|min:0',
            'subtotal' => 'required|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'tax_amount' => 'nullable|numeric|min:0',
            'shipping_cost' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $invoice = $this->procurementService->createInvoice($validated);

        $matchMsg = $invoice->matching_status === 'discrepancy'
            ? ' (Perhatian: Terdeteksi selisih nominal Three-Way Matching dengan PO/Goods Receipt)'
            : '';

        return redirect()->route('admin.supplier-invoices.index')
            ->with('success', "Tagihan supplier {$invoice->invoice_number} berhasil dicatat.{$matchMsg}");
    }

    /**
     * Supplier Payments History & Recording
     */
    public function payments(Request $request)
    {
        $query = SupplierPayment::with(['supplier', 'invoice.purchaseOrder', 'creator'])->latest();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('payment_number', 'like', "%{$search}%")
                    ->orWhere('reference_number', 'like', "%{$search}%")
                    ->orWhereHas('supplier', fn ($sq) => $sq->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('invoice', fn ($iq) => $iq->where('invoice_number', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        $payments = $query->paginate(15)->withQueryString();
        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();
        $unpaidInvoices = SupplierInvoice::with('supplier')
            ->where('outstanding_balance', '>', 0)
            ->where('status', '!=', SupplierInvoice::STATUS_CANCELLED)
            ->orderBy('due_date')
            ->get();

        return view('admin.supplier-payables.payments', compact('payments', 'suppliers', 'unpaidInvoices'));
    }

    public function storePayment(Request $request)
    {
        $validated = $request->validate([
            'supplier_invoice_id' => 'required|exists:supplier_invoices,id',
            'payment_date' => 'required|date',
            'amount' => 'required|numeric|min:1',
            'payment_method' => 'required|in:bank_transfer,cash,giro,e_wallet',
            'reference_number' => 'nullable|string|max:100',
            'proof_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:4096',
            'notes' => 'nullable|string',
        ]);

        if ($request->hasFile('proof_file')) {
            $validated['proof_file'] = $request->file('proof_file')->store('supplier-payments', 'public');
        }

        $invoice = SupplierInvoice::findOrFail($validated['supplier_invoice_id']);
        $payment = $this->procurementService->recordPayment($invoice, $validated);

        return redirect()->back()
            ->with('success', "Pembayaran {$payment->payment_number} sebesar Rp ".number_format((float) $payment->amount, 0, ',', '.').' berhasil dicatat. Sisa tagihan: Rp '.number_format((float) $invoice->fresh()->outstanding_balance, 0, ',', '.'));
    }

    /**
     * Supplier Performance, Price Comparison & Procurement Reports
     */
    public function reports(Request $request)
    {
        $performanceRows = $this->procurementService->getSupplierPerformanceReport();
        $reorderSuggestions = $this->procurementService->getReorderSuggestions();

        // Products with multiple suppliers for price comparison
        $comparableProducts = Product::whereHas('supplierProducts')
            ->with(['supplierProducts.supplier'])
            ->get();

        $priceHistories = SupplierPriceHistory::with(['supplier', 'product', 'changedBy'])
            ->latest('recorded_at')
            ->limit(25)
            ->get();

        $totalProcurementSpend = (float) PurchaseOrder::where('status', '!=', PurchaseOrder::STATUS_CANCELLED)->sum('total_amount');
        $totalOutstandingDebt = (float) SupplierInvoice::whereIn('status', ['unpaid', 'partially_paid', 'overdue'])->sum('outstanding_balance');

        return view('admin.supplier-payables.reports', compact(
            'performanceRows',
            'reorderSuggestions',
            'comparableProducts',
            'priceHistories',
            'totalProcurementSpend',
            'totalOutstandingDebt'
        ));
    }
}
