<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->nullable()->index();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('outlet_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['adjustment', 'sale', 'return', 'transfer_in', 'transfer_out'])->default('adjustment');
            $table->decimal('quantity_before', 15, 3)->default(0);
            $table->decimal('quantity_change', 15, 3)->default(0);
            $table->decimal('quantity_after', 15, 3)->default(0);
            $table->string('reason')->nullable();
            $table->nullableMorphs('reference');   // reference_type / reference_id (e.g. Order)
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
