<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Payment records — one order may have multiple partial payments.
     * FK: order_id (000006).
     */
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id');

            $table->foreignId('order_id')
                ->constrained('orders')
                ->cascadeOnDelete();

            // cash, card, qris, bank_transfer, voucher, etc.
            $table->string('payment_method');

            $table->decimal('amount', 15, 2);
            $table->decimal('change_amount', 15, 2)->default(0);

            // pending, completed, failed, refunded
            $table->string('status')->default('pending');

            $table->string('reference_number')->nullable(); // external transaction ID
            $table->json('metadata')->nullable();           // gateway response, etc.
            $table->text('notes')->nullable();

            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index('tenant_id');
            $table->index('order_id');
            $table->index(['tenant_id', 'status']);
            $table->index('reference_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
