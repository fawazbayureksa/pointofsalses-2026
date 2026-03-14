<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id');
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('payment_method'); // cash, card, qris, transfer, etc.
            $table->decimal('amount', 15, 2);
            $table->decimal('change_amount', 15, 2)->default(0);
            $table->string('status')->default('pending'); // pending, completed, failed, refunded
            $table->string('reference_number')->nullable();
            $table->json('metadata')->nullable(); // gateway response, transaction details
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index('tenant_id');
            $table->index('order_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
