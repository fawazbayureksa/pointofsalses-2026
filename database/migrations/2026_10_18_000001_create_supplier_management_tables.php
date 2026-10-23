<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Suppliers (Global per Tenant)
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->nullable()->index();
            $table->string('code', 50);
            $table->string('name');
            $table->string('company_name')->nullable();
            $table->string('contact_person')->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->string('city', 100)->nullable();
            $table->string('npwp', 50)->nullable();
            $table->string('tax_info')->nullable();
            $table->string('bank_name', 100)->nullable();
            $table->string('bank_account_number', 100)->nullable();
            $table->string('bank_account_holder')->nullable();
            $table->integer('default_payment_terms')->default(0); // days: 0=COD, 7, 14, 30, etc.
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['tenant_id', 'code']);
        });

        // 2. Supplier Products Mapping (Global per Tenant)
        Schema::create('supplier_products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->nullable()->index();
            $table->foreignId('supplier_id')->constrained('suppliers')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('supplier_sku', 100)->nullable();
            $table->decimal('purchase_price', 15, 2)->default(0);
            $table->boolean('is_primary')->default(false);
            $table->decimal('min_order_qty', 12, 3)->default(1);
            $table->integer('lead_time_days')->default(1);
            $table->string('purchase_unit', 50)->default('pcs');
            $table->decimal('conversion_factor', 12, 3)->default(1); // 1 purchase_unit = X product stock units
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['supplier_id', 'product_id']);
        });

        // 3. Supplier Price Histories
        Schema::create('supplier_price_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->nullable()->index();
            $table->foreignId('supplier_id')->constrained('suppliers')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->decimal('old_price', 15, 2)->nullable();
            $table->decimal('new_price', 15, 2);
            $table->string('purchase_unit', 50)->default('pcs');
            $table->string('source', 50)->default('mapping'); // mapping, purchase_order
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('notes')->nullable();
            $table->timestamp('recorded_at')->useCurrent();
            $table->timestamps();
        });

        // 4. Purchase Orders (Per Outlet within Tenant)
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->nullable()->index();
            $table->foreignId('outlet_id')->constrained('outlets')->cascadeOnDelete();
            $table->foreignId('supplier_id')->constrained('suppliers')->restrictOnDelete();
            $table->string('po_number', 50);
            $table->date('order_date');
            $table->date('expected_date')->nullable();
            $table->string('status', 30)->default('draft');
            // draft, pending_approval, approved, ordered, partially_received, received, cancelled
            $table->integer('payment_terms')->default(0); // days
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('shipping_cost', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('ordered_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['tenant_id', 'po_number']);
        });

        // 5. Purchase Order Items
        Schema::create('purchase_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')->constrained('purchase_orders')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
            $table->string('product_name');
            $table->string('product_sku', 100)->nullable();
            $table->string('purchase_unit', 50)->default('pcs');
            $table->decimal('conversion_factor', 12, 3)->default(1);
            $table->decimal('quantity_ordered', 12, 3)->default(0);
            $table->decimal('quantity_received', 12, 3)->default(0);
            $table->decimal('unit_price', 15, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->string('notes')->nullable();
            $table->timestamps();
        });

        // 6. Goods Receipts (Per Outlet, references PO)
        Schema::create('goods_receipts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->nullable()->index();
            $table->foreignId('purchase_order_id')->constrained('purchase_orders')->cascadeOnDelete();
            $table->foreignId('outlet_id')->constrained('outlets')->cascadeOnDelete();
            $table->foreignId('supplier_id')->constrained('suppliers')->restrictOnDelete();
            $table->string('gr_number', 50);
            $table->date('received_date');
            $table->string('supplier_delivery_note', 100)->nullable(); // Surat Jalan
            $table->string('status', 30)->default('completed'); // draft, completed, cancelled
            $table->boolean('is_late')->default(false);
            $table->text('notes')->nullable();
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['tenant_id', 'gr_number']);
        });

        // 7. Goods Receipt Items
        Schema::create('goods_receipt_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('goods_receipt_id')->constrained('goods_receipts')->cascadeOnDelete();
            $table->foreignId('purchase_order_item_id')->nullable()->constrained('purchase_order_items')->nullOnDelete();
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
            $table->decimal('quantity_ordered', 12, 3)->default(0);
            $table->decimal('quantity_received', 12, 3)->default(0); // good/accepted qty
            $table->decimal('quantity_damaged', 12, 3)->default(0);  // damaged/rejected qty
            $table->decimal('conversion_factor', 12, 3)->default(1);
            $table->decimal('stock_added', 12, 3)->default(0);       // quantity_received * conversion_factor
            $table->decimal('unit_cost', 15, 2)->default(0);
            $table->string('batch_number', 100)->nullable();
            $table->date('expiry_date')->nullable();
            $table->string('notes')->nullable();
            $table->timestamps();
        });

        // 8. Supplier Invoices & Payables
        Schema::create('supplier_invoices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->nullable()->index();
            $table->foreignId('supplier_id')->constrained('suppliers')->restrictOnDelete();
            $table->foreignId('purchase_order_id')->nullable()->constrained('purchase_orders')->nullOnDelete();
            $table->foreignId('goods_receipt_id')->nullable()->constrained('goods_receipts')->nullOnDelete();
            $table->string('invoice_number', 100);
            $table->string('supplier_invoice_number', 100)->nullable();
            $table->date('invoice_date');
            $table->date('due_date');
            $table->integer('payment_terms')->default(0);
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('shipping_cost', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->decimal('amount_paid', 15, 2)->default(0);
            $table->decimal('return_deduction', 15, 2)->default(0);
            $table->decimal('outstanding_balance', 15, 2)->default(0);
            $table->string('matching_status', 30)->default('matched'); // matched, discrepancy, manual
            $table->string('status', 30)->default('unpaid'); // unpaid, partially_paid, paid, overdue, cancelled
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['tenant_id', 'invoice_number']);
        });

        // 9. Supplier Payments
        Schema::create('supplier_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->nullable()->index();
            $table->foreignId('supplier_id')->constrained('suppliers')->restrictOnDelete();
            $table->foreignId('supplier_invoice_id')->constrained('supplier_invoices')->cascadeOnDelete();
            $table->string('payment_number', 50);
            $table->date('payment_date');
            $table->decimal('amount', 15, 2);
            $table->string('payment_method', 50)->default('bank_transfer'); // cash, bank_transfer, giro, check, e_wallet
            $table->string('reference_number', 100)->nullable();
            $table->string('proof_file')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['tenant_id', 'payment_number']);
        });

        // 10. Supplier Returns
        Schema::create('supplier_returns', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->nullable()->index();
            $table->foreignId('supplier_id')->constrained('suppliers')->restrictOnDelete();
            $table->foreignId('outlet_id')->constrained('outlets')->cascadeOnDelete();
            $table->foreignId('purchase_order_id')->nullable()->constrained('purchase_orders')->nullOnDelete();
            $table->foreignId('goods_receipt_id')->nullable()->constrained('goods_receipts')->nullOnDelete();
            $table->foreignId('supplier_invoice_id')->nullable()->constrained('supplier_invoices')->nullOnDelete();
            $table->string('return_number', 50);
            $table->date('return_date');
            $table->decimal('total_refund_amount', 15, 2)->default(0);
            $table->string('resolution_type', 50)->default('deduct_payable'); // deduct_payable, replacement, cash_refund
            $table->string('status', 30)->default('completed'); // draft, completed, cancelled
            $table->text('reason')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['tenant_id', 'return_number']);
        });

        // 11. Supplier Return Items
        Schema::create('supplier_return_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_return_id')->constrained('supplier_returns')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
            $table->decimal('quantity', 12, 3)->default(0);
            $table->decimal('unit_cost', 15, 2)->default(0);
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->boolean('deduct_stock')->default(true);
            $table->string('condition_notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_return_items');
        Schema::dropIfExists('supplier_returns');
        Schema::dropIfExists('supplier_payments');
        Schema::dropIfExists('supplier_invoices');
        Schema::dropIfExists('goods_receipt_items');
        Schema::dropIfExists('goods_receipts');
        Schema::dropIfExists('purchase_order_items');
        Schema::dropIfExists('purchase_orders');
        Schema::dropIfExists('supplier_price_histories');
        Schema::dropIfExists('supplier_products');
        Schema::dropIfExists('suppliers');
    }
};
