<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Outlets — physical locations / branches belonging to the tenant.
     * Must be created FIRST because users, products, and orders FK to this table.
     */
    public function up(): void
    {
        Schema::create('outlets', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id');               // logical tenant reference (no cross-DB FK)

            $table->string('name');
            $table->string('code')->nullable()->unique(); // short branch code e.g. "BDG-01"
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('country', 2)->default('ID');
            $table->string('currency', 3)->default('IDR');
            $table->string('timezone')->default('Asia/Jakarta');
            $table->boolean('is_active')->default(true);
            $table->string('logo')->nullable();
            $table->json('settings')->nullable();      // outlet-level overrides

            $table->timestamps();
            $table->softDeletes();

            $table->index('tenant_id');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('outlets');
    }
};
