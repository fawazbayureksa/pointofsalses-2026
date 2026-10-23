<?php

namespace App\Services;

use App\Events\StockUpdated;
use App\Models\GoodsReceipt;
use App\Models\Outlet;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\SupplierInvoice;
use App\Models\SupplierPayment;
use App\Models\SupplierPriceHistory;
use App\Models\SupplierProduct;
use App\Models\SupplierReturn;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProcurementService
{
    /**
     * Default PO approval threshold (Rp 5,000,000) unless configured in settings.
     */
    public function approvalThreshold(): float
    {
        return (float) setting('po_approval_threshold', 5000000);
    }

    /**
     * Map or update a Supplier Product and record price history if price changed.
     */
    public function upsertSupplierProduct(array $data): SupplierProduct
    {
        return DB::transaction(function () use ($data) {
            $existing = SupplierProduct::where('supplier_id', $data['supplier_id'])
                ->where('product_id', $data['product_id'])
                ->first();

            $oldPrice = $existing ? (float) $existing->purchase_price : null;
            $newPrice = (float) ($data['purchase_price'] ?? 0);

            if (! empty($data['is_primary'])) {
                SupplierProduct::where('product_id', $data['product_id'])
                    ->where('supplier_id', '!=', $data['supplier_id'])
                    ->update(['is_primary' => false]);
            }

            $mapping = SupplierProduct::updateOrCreate(
                [
                    'supplier_id' => $data['supplier_id'],
                    'product_id' => $data['product_id'],
                ],
                [
                    'supplier_sku' => $data['supplier_sku'] ?? null,
                    'purchase_price' => $newPrice,
                    'is_primary' => (bool) ($data['is_primary'] ?? false),
                    'min_order_qty' => (float) ($data['min_order_qty'] ?? 1),
                    'lead_time_days' => (int) ($data['lead_time_days'] ?? 1),
                    'purchase_unit' => $data['purchase_unit'] ?? 'pcs',
                    'conversion_factor' => max(0.001, (float) ($data['conversion_factor'] ?? 1)),
                    'notes' => $data['notes'] ?? null,
                ]
            );

            if ($oldPrice === null || abs($oldPrice - $newPrice) > 0.001) {
                SupplierPriceHistory::create([
                    'supplier_id' => $data['supplier_id'],
                    'product_id' => $data['product_id'],
                    'old_price' => $oldPrice,
                    'new_price' => $newPrice,
                    'purchase_unit' => $mapping->purchase_unit,
                    'source' => 'catalog_mapping',
                    'changed_by' => Auth::id(),
                    'notes' => $oldPrice === null ? 'Initial supplier catalog price' : 'Catalog price updated',
                    'recorded_at' => now(),
                ]);
            }

            return $mapping;
        });
    }

    /**
     * Create a Purchase Order with items.
     */
    public function createPurchaseOrder(array $data): PurchaseOrder
    {
        return DB::transaction(function () use ($data) {
            $supplier = Supplier::findOrFail($data['supplier_id']);
            $tenantId = Auth::user()?->tenant_id ?? $supplier->tenant_id;

            $po = PurchaseOrder::create([
                'tenant_id' => $tenantId,
                'outlet_id' => $data['outlet_id'],
                'supplier_id' => $supplier->id,
                'po_number' => PurchaseOrder::generatePoNumber($tenantId),
                'order_date' => $data['order_date'] ?? now()->toDateString(),
                'expected_date' => $data['expected_date'] ?? now()->addDays(3)->toDateString(),
                'status' => PurchaseOrder::STATUS_DRAFT,
                'payment_terms' => (int) ($data['payment_terms'] ?? $supplier->default_payment_terms ?? 0),
                'discount_amount' => (float) ($data['discount_amount'] ?? 0),
                'tax_rate' => (float) ($data['tax_rate'] ?? setting('tax_rate', 11)),
                'shipping_cost' => (float) ($data['shipping_cost'] ?? 0),
                'notes' => $data['notes'] ?? null,
                'created_by' => Auth::id(),
            ]);

            foreach ($data['items'] as $itemData) {
                $product = Product::findOrFail($itemData['product_id']);
                $mapping = SupplierProduct::where('supplier_id', $supplier->id)
                    ->where('product_id', $product->id)
                    ->first();

                $qty = (float) $itemData['quantity_ordered'];
                $unitPrice = (float) $itemData['unit_price'];
                $itemDiscount = (float) ($itemData['discount_amount'] ?? 0);
                $purchaseUnit = $itemData['purchase_unit'] ?? ($mapping?->purchase_unit ?? ($product->unit ?? 'pcs'));
                $conversionFactor = max(0.001, (float) ($itemData['conversion_factor'] ?? ($mapping?->conversion_factor ?? 1)));
                $lineSubtotal = max(0, ($qty * $unitPrice) - $itemDiscount);

                $po->items()->create([
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product_sku' => $product->sku ?? $mapping?->supplier_sku,
                    'purchase_unit' => $purchaseUnit,
                    'conversion_factor' => $conversionFactor,
                    'quantity_ordered' => $qty,
                    'quantity_received' => 0,
                    'unit_price' => $unitPrice,
                    'discount_amount' => $itemDiscount,
                    'subtotal' => $lineSubtotal,
                    'notes' => $itemData['notes'] ?? null,
                ]);

                // Record price history if PO price differs from supplier mapping
                if ($mapping && abs(((float) $mapping->purchase_price) - $unitPrice) > 0.001) {
                    $oldPrice = (float) $mapping->purchase_price;
                    $mapping->update(['purchase_price' => $unitPrice]);

                    SupplierPriceHistory::create([
                        'tenant_id' => $tenantId,
                        'supplier_id' => $supplier->id,
                        'product_id' => $product->id,
                        'old_price' => $oldPrice,
                        'new_price' => $unitPrice,
                        'purchase_unit' => $purchaseUnit,
                        'source' => 'purchase_order',
                        'changed_by' => Auth::id(),
                        'notes' => "Updated via PO {$po->po_number}",
                        'recorded_at' => now(),
                    ]);
                }
            }

            $po->recalculateTotals();
            $po->refresh();

            // Determine workflow status based on submit_action and approval threshold
            $submitAction = $data['submit_action'] ?? 'draft';
            if ($submitAction === 'submit') {
                $threshold = $this->approvalThreshold();
                if ($threshold > 0 && (float) $po->total_amount >= $threshold && ! Auth::user()?->can('approve purchase orders')) {
                    $po->update(['status' => PurchaseOrder::STATUS_PENDING_APPROVAL]);
                } else {
                    $po->update([
                        'status' => PurchaseOrder::STATUS_APPROVED,
                        'approved_by' => Auth::id(),
                        'approved_at' => now(),
                    ]);
                }
            } elseif ($submitAction === 'pending_approval') {
                $po->update(['status' => PurchaseOrder::STATUS_PENDING_APPROVAL]);
            }

            return $po;
        });
    }

    /**
     * Receive Goods against a Purchase Order and update inventory stock ONLY for valid accepted quantity.
     */
    public function receiveGoods(PurchaseOrder $po, array $data): GoodsReceipt
    {
        if (! $po->canReceiveGoods()) {
            throw ValidationException::withMessages([
                'purchase_order_id' => "PO {$po->po_number} tidak dalam status yang dapat menerima barang (Status saat ini: {$po->status_label}).",
            ]);
        }

        return DB::transaction(function () use ($po, $data) {
            $tenantId = $po->tenant_id;
            $receivedDate = $data['received_date'] ?? now()->toDateString();
            $isLate = $po->expected_date && $receivedDate > $po->expected_date->toDateString();

            $gr = GoodsReceipt::create([
                'tenant_id' => $tenantId,
                'purchase_order_id' => $po->id,
                'outlet_id' => $po->outlet_id,
                'supplier_id' => $po->supplier_id,
                'gr_number' => GoodsReceipt::generateGrNumber($tenantId),
                'received_date' => $receivedDate,
                'supplier_delivery_note' => $data['supplier_delivery_note'] ?? null,
                'status' => 'completed',
                'is_late' => $isLate,
                'notes' => $data['notes'] ?? null,
                'received_by' => Auth::id(),
            ]);

            $hasAnyReceipt = false;

            foreach ($data['items'] as $itemInput) {
                $poItem = PurchaseOrderItem::where('purchase_order_id', $po->id)
                    ->where('id', $itemInput['purchase_order_item_id'])
                    ->firstOrFail();

                $qtyReceived = max(0, (float) ($itemInput['quantity_received'] ?? 0));
                $qtyDamaged = max(0, (float) ($itemInput['quantity_damaged'] ?? 0));

                if ($qtyReceived <= 0 && $qtyDamaged <= 0) {
                    continue;
                }

                $hasAnyReceipt = true;
                $conversionFactor = max(0.001, (float) $poItem->conversion_factor);
                $stockAdded = round($qtyReceived * $conversionFactor, 3);
                $unitCost = (float) $poItem->unit_price;

                $gr->items()->create([
                    'purchase_order_item_id' => $poItem->id,
                    'product_id' => $poItem->product_id,
                    'quantity_ordered' => $poItem->quantity_ordered,
                    'quantity_received' => $qtyReceived,
                    'quantity_damaged' => $qtyDamaged,
                    'conversion_factor' => $conversionFactor,
                    'stock_added' => $stockAdded,
                    'unit_cost' => $unitCost,
                    'batch_number' => $itemInput['batch_number'] ?? null,
                    'expiry_date' => ! empty($itemInput['expiry_date']) ? $itemInput['expiry_date'] : null,
                    'notes' => $itemInput['notes'] ?? null,
                ]);

                // Update PO item cumulative received quantity (only valid accepted goods)
                $newCumulativeReceived = ((float) $poItem->quantity_received) + $qtyReceived;
                $poItem->update(['quantity_received' => $newCumulativeReceived]);

                // Update Inventory Stock at the PO's Outlet ONLY for valid accepted goods ($stockAdded)
                $product = Product::find($poItem->product_id);
                if ($product && $stockAdded > 0) {
                    $pivot = $product->outlets()->wherePivot('outlet_id', $po->outlet_id)->first();
                    $before = $pivot ? (float) $pivot->pivot->stock : 0.0;
                    $after = $before + $stockAdded;

                    if ($pivot) {
                        $product->outlets()->updateExistingPivot($po->outlet_id, ['stock' => $after]);
                    } else {
                        $product->outlets()->attach($po->outlet_id, [
                            'stock' => $after,
                            'low_stock_threshold' => 5,
                        ]);
                    }

                    // Update product cost_price per stock unit
                    $costPerStockUnit = round($unitCost / $conversionFactor, 2);
                    if ($costPerStockUnit > 0) {
                        $product->update(['cost_price' => $costPerStockUnit]);
                    }

                    StockMovement::record(
                        product: $product,
                        outletId: $po->outlet_id,
                        type: 'goods_receipt',
                        before: $before,
                        change: $stockAdded,
                        after: $after,
                        reason: "Goods Receipt {$gr->gr_number} (PO {$po->po_number})",
                        reference: $gr,
                    );

                    event(new StockUpdated($product, $before, $after, 'goods_receipt'));
                }
            }

            if (! $hasAnyReceipt) {
                throw ValidationException::withMessages([
                    'items' => 'Minimal satu item harus memiliki kuantitas diterima (received) atau rusak (damaged) lebih dari 0.',
                ]);
            }

            // Re-evaluate PO status: received vs partially_received
            $po->load('items');
            $allReceived = $po->items->every(fn (PurchaseOrderItem $i) => (float) $i->quantity_received >= (float) $i->quantity_ordered);

            $po->update([
                'status' => $allReceived
                    ? PurchaseOrder::STATUS_RECEIVED
                    : PurchaseOrder::STATUS_PARTIALLY_RECEIVED,
            ]);

            return $gr;
        });
    }

    /**
     * Create a Supplier Invoice with Three-Way Matching check (PO vs GR vs Invoice).
     */
    public function createInvoice(array $data): SupplierInvoice
    {
        return DB::transaction(function () use ($data) {
            $supplier = Supplier::findOrFail($data['supplier_id']);
            $tenantId = Auth::user()?->tenant_id ?? $supplier->tenant_id;

            $po = ! empty($data['purchase_order_id']) ? PurchaseOrder::with('items')->find($data['purchase_order_id']) : null;
            $gr = ! empty($data['goods_receipt_id']) ? GoodsReceipt::with('items')->find($data['goods_receipt_id']) : null;

            $subtotal = (float) ($data['subtotal'] ?? 0);
            $discount = (float) ($data['discount_amount'] ?? 0);
            $taxAmount = (float) ($data['tax_amount'] ?? 0);
            $shippingCost = (float) ($data['shipping_cost'] ?? 0);
            $totalAmount = round(max(0, $subtotal - $discount) + $taxAmount + $shippingCost, 2);

            $paymentTerms = (int) ($data['payment_terms'] ?? ($po?->payment_terms ?? $supplier->default_payment_terms ?? 0));
            $invoiceDate = $data['invoice_date'] ?? now()->toDateString();
            $dueDate = ! empty($data['due_date'])
                ? $data['due_date']
                : Carbon::parse($invoiceDate)->addDays($paymentTerms)->toDateString();

            // Three-Way Matching evaluation
            $matchingStatus = 'manual';
            if ($gr) {
                $expectedGrSubtotal = (float) $gr->items->sum(fn ($i) => ((float) $i->quantity_received) * ((float) $i->unit_cost));
                $matchingStatus = abs($subtotal - $expectedGrSubtotal) <= 1.0 ? 'matched' : 'discrepancy';
            } elseif ($po) {
                $receivedPoSubtotal = (float) $po->items->sum(fn ($i) => ((float) $i->quantity_received) * ((float) $i->unit_price));
                $expectedBase = $receivedPoSubtotal > 0 ? $receivedPoSubtotal : (float) $po->subtotal;
                $matchingStatus = abs($subtotal - $expectedBase) <= 1.0 ? 'matched' : 'discrepancy';
            }

            $invoice = SupplierInvoice::create([
                'tenant_id' => $tenantId,
                'supplier_id' => $supplier->id,
                'purchase_order_id' => $po?->id,
                'goods_receipt_id' => $gr?->id,
                'invoice_number' => SupplierInvoice::generateInvoiceNumber($tenantId),
                'supplier_invoice_number' => $data['supplier_invoice_number'] ?? null,
                'invoice_date' => $invoiceDate,
                'due_date' => $dueDate,
                'payment_terms' => $paymentTerms,
                'subtotal' => $subtotal,
                'discount_amount' => $discount,
                'tax_amount' => $taxAmount,
                'shipping_cost' => $shippingCost,
                'total_amount' => $totalAmount,
                'amount_paid' => 0,
                'return_deduction' => 0,
                'outstanding_balance' => $totalAmount,
                'matching_status' => $matchingStatus,
                'status' => (Carbon::parse($dueDate)->isPast())
                    ? SupplierInvoice::STATUS_OVERDUE
                    : SupplierInvoice::STATUS_UNPAID,
                'notes' => $data['notes'] ?? null,
                'created_by' => Auth::id(),
            ]);

            return $invoice;
        });
    }

    /**
     * Record a Supplier Payment (full or partial) against a Supplier Invoice.
     */
    public function recordPayment(SupplierInvoice $invoice, array $data): SupplierPayment
    {
        if ($invoice->status === SupplierInvoice::STATUS_CANCELLED) {
            throw ValidationException::withMessages([
                'supplier_invoice_id' => 'Tidak dapat membayar invoice yang telah dibatalkan.',
            ]);
        }

        $amount = round((float) $data['amount'], 2);
        if ($amount <= 0) {
            throw ValidationException::withMessages([
                'amount' => 'Nominal pembayaran harus lebih besar dari 0.',
            ]);
        }

        if ($amount > ((float) $invoice->outstanding_balance + 0.01)) {
            throw ValidationException::withMessages([
                'amount' => 'Nominal pembayaran melebihi sisa tagihan (Rp '.number_format((float) $invoice->outstanding_balance, 0, ',', '.').').',
            ]);
        }

        return DB::transaction(function () use ($invoice, $data, $amount) {
            $tenantId = $invoice->tenant_id;

            $payment = SupplierPayment::create([
                'tenant_id' => $tenantId,
                'supplier_id' => $invoice->supplier_id,
                'supplier_invoice_id' => $invoice->id,
                'payment_number' => SupplierPayment::generatePaymentNumber($tenantId),
                'payment_date' => $data['payment_date'] ?? now()->toDateString(),
                'amount' => $amount,
                'payment_method' => $data['payment_method'] ?? 'bank_transfer',
                'reference_number' => $data['reference_number'] ?? null,
                'proof_file' => $data['proof_file'] ?? null,
                'notes' => $data['notes'] ?? null,
                'created_by' => Auth::id(),
            ]);

            $invoice->recalculateBalance();

            return $payment;
        });
    }

    /**
     * Record a Supplier Return (Retur Barang ke Supplier), optionally deducting stock and invoice payable.
     */
    public function createSupplierReturn(array $data): SupplierReturn
    {
        return DB::transaction(function () use ($data) {
            $supplier = Supplier::findOrFail($data['supplier_id']);
            $tenantId = Auth::user()?->tenant_id ?? $supplier->tenant_id;
            $outletId = (int) $data['outlet_id'];

            $return = SupplierReturn::create([
                'tenant_id' => $tenantId,
                'supplier_id' => $supplier->id,
                'outlet_id' => $outletId,
                'purchase_order_id' => $data['purchase_order_id'] ?? null,
                'goods_receipt_id' => $data['goods_receipt_id'] ?? null,
                'supplier_invoice_id' => $data['supplier_invoice_id'] ?? null,
                'return_number' => SupplierReturn::generateReturnNumber($tenantId),
                'return_date' => $data['return_date'] ?? now()->toDateString(),
                'total_refund_amount' => 0,
                'resolution_type' => $data['resolution_type'] ?? 'deduct_payable',
                'status' => 'completed',
                'reason' => $data['reason'] ?? null,
                'created_by' => Auth::id(),
            ]);

            $totalRefund = 0.0;

            foreach ($data['items'] as $itemData) {
                $qty = (float) ($itemData['quantity'] ?? 0);
                if ($qty <= 0) {
                    continue;
                }

                $unitCost = (float) ($itemData['unit_cost'] ?? 0);
                $subtotal = round($qty * $unitCost, 2);
                $deductStock = isset($itemData['deduct_stock']) ? (bool) $itemData['deduct_stock'] : true;
                $totalRefund += $subtotal;

                $return->items()->create([
                    'product_id' => $itemData['product_id'],
                    'quantity' => $qty,
                    'unit_cost' => $unitCost,
                    'subtotal' => $subtotal,
                    'deduct_stock' => $deductStock,
                    'condition_notes' => $itemData['condition_notes'] ?? null,
                ]);

                if ($deductStock) {
                    $product = Product::find($itemData['product_id']);
                    if ($product) {
                        $pivot = $product->outlets()->wherePivot('outlet_id', $outletId)->first();
                        $before = $pivot ? (float) $pivot->pivot->stock : 0.0;
                        $after = max(0.0, $before - $qty);

                        if ($pivot) {
                            $product->outlets()->updateExistingPivot($outletId, ['stock' => $after]);
                        }

                        StockMovement::record(
                            product: $product,
                            outletId: $outletId,
                            type: 'supplier_return',
                            before: $before,
                            change: -$qty,
                            after: $after,
                            reason: "Supplier Return {$return->return_number}".($return->reason ? ": {$return->reason}" : ''),
                            reference: $return,
                        );
                    }
                }
            }

            $return->update(['total_refund_amount' => $totalRefund]);

            if ($return->supplier_invoice_id) {
                $invoice = SupplierInvoice::find($return->supplier_invoice_id);
                $invoice?->recalculateBalance();
            }

            return $return;
        });
    }

    /**
     * Automatic Reorder Suggestions based on low stock threshold, primary/cheapest supplier, MOQ, and lead time.
     */
    public function getReorderSuggestions(?int $outletId = null): Collection
    {
        $outlets = $outletId
            ? Outlet::where('id', $outletId)->where('is_active', true)->get()
            : Outlet::where('is_active', true)->get();

        $suggestions = collect();

        $products = Product::where('track_stock', true)
            ->where('is_active', true)
            ->with(['outlets', 'supplierProducts.supplier'])
            ->get();

        foreach ($outlets as $outlet) {
            foreach ($products as $product) {
                $pivot = $product->outlets->firstWhere('id', $outlet->id);
                if (! $pivot) {
                    continue;
                }

                $currentStock = (float) $pivot->pivot->stock;
                $threshold = (float) $pivot->pivot->low_stock_threshold;

                if ($currentStock <= $threshold) {
                    // Find preferred supplier (primary first, otherwise lowest purchase_price)
                    $activeMappings = $product->supplierProducts
                        ->filter(fn ($sp) => $sp->supplier && $sp->supplier->is_active);

                    $preferredMapping = $activeMappings->firstWhere('is_primary', true)
                        ?? $activeMappings->sortBy('purchase_price')->first();

                    $deficit = max(1, ($threshold * 2) - $currentStock);
                    $conversionFactor = $preferredMapping ? max(1, (float) $preferredMapping->conversion_factor) : 1;
                    $moq = $preferredMapping ? max(1, (float) $preferredMapping->min_order_qty) : 1;
                    $neededInPurchaseUnit = ceil($deficit / $conversionFactor);
                    $suggestedOrderQty = max($moq, $neededInPurchaseUnit);
                    $unitPrice = $preferredMapping ? (float) $preferredMapping->purchase_price : (float) $product->cost_price;

                    $suggestions->push([
                        'outlet' => $outlet,
                        'product' => $product,
                        'current_stock' => $currentStock,
                        'low_stock_threshold' => $threshold,
                        'preferred_supplier' => $preferredMapping?->supplier,
                        'mapping' => $preferredMapping,
                        'suggested_qty' => $suggestedOrderQty,
                        'purchase_unit' => $preferredMapping?->purchase_unit ?? ($product->unit ?? 'pcs'),
                        'estimated_unit_price' => $unitPrice,
                        'estimated_total_cost' => round($suggestedOrderQty * $unitPrice, 2),
                        'lead_time_days' => $preferredMapping?->lead_time_days ?? 1,
                    ]);
                }
            }
        }

        return $suggestions;
    }

    /**
     * Compute Supplier Performance Score & KPI metrics for each supplier.
     */
    public function getSupplierPerformanceReport(): Collection
    {
        $suppliers = Supplier::with([
            'purchaseOrders',
            'goodsReceipts.items',
            'invoices',
            'supplierProducts',
        ])->get();

        return $suppliers->map(function (Supplier $supplier) {
            $pos = $supplier->purchaseOrders->where('status', '!=', PurchaseOrder::STATUS_CANCELLED);
            $grs = $supplier->goodsReceipts;

            $totalPurchaseValue = (float) $pos->sum('total_amount');
            $poFrequency = $pos->count();

            $totalReceipts = $grs->count();
            $onTimeReceipts = $grs->where('is_late', false)->count();
            $onTimeRate = $totalReceipts > 0 ? round(($onTimeReceipts / $totalReceipts) * 100, 1) : 100.0;

            $allGrItems = $grs->flatMap(fn ($gr) => $gr->items);
            $totalAccepted = (float) $allGrItems->sum('quantity_received');
            $totalDamaged = (float) $allGrItems->sum('quantity_damaged');
            $totalDelivered = $totalAccepted + $totalDamaged;

            $damageRate = $totalDelivered > 0 ? round(($totalDamaged / $totalDelivered) * 100, 1) : 0.0;
            $qualityRate = max(0.0, 100.0 - $damageRate);

            // Composite Performance Score (0 - 100): 50% Quality + 40% On-Time Delivery + 10% Activity
            $activityScore = min(100, $poFrequency > 0 ? 100 : 75);
            $performanceScore = round(($qualityRate * 0.50) + ($onTimeRate * 0.40) + ($activityScore * 0.10), 1);

            $grade = match (true) {
                $performanceScore >= 90 => 'A (Excellent)',
                $performanceScore >= 80 => 'B (Good)',
                $performanceScore >= 65 => 'C (Fair)',
                default => 'D (Needs Review)',
            };

            $outstandingPayables = (float) $supplier->invoices
                ->whereIn('status', ['unpaid', 'partially_paid', 'overdue'])
                ->sum('outstanding_balance');

            return [
                'supplier' => $supplier,
                'po_count' => $poFrequency,
                'total_purchase_value' => $totalPurchaseValue,
                'gr_count' => $totalReceipts,
                'on_time_rate' => $onTimeRate,
                'damage_rate' => $damageRate,
                'quality_rate' => $qualityRate,
                'performance_score' => $performanceScore,
                'grade' => $grade,
                'outstanding_payables' => $outstandingPayables,
                'mapped_products_count' => $supplier->supplierProducts->count(),
            ];
        })->sortByDesc('total_purchase_value')->values();
    }
}
