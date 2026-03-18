<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('sku');
            $table->string('barcode')->nullable();
            $table->text('description')->nullable();
            $table->decimal('price', 15, 2);
            $table->decimal('cost_price', 15, 2)->nullable();
            $table->string('unit')->default('pcs');
            $table->boolean('is_active')->default(true);
            $table->boolean('track_stock')->default(true);
            $table->string('image')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['tenant_id', 'sku']);
        });

        Schema::create('product_outlet', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('outlet_id')->constrained()->cascadeOnDelete();
            $table->decimal('stock', 15, 3)->default(0);
            $table->decimal('low_stock_threshold', 15, 3)->default(0);
            $table->timestamps();

            $table->unique(['product_id', 'outlet_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_outlet');
        Schema::dropIfExists('products');
    }
};
