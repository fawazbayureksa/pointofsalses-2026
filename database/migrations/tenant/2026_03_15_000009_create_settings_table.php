<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tenant key-value settings store.
     * Each tenant has its own copy due to multi-DB tenancy.
     */
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id');

            $table->string('key');
            $table->text('value')->nullable();

            // 'string' | 'integer' | 'boolean' | 'json' | 'float'
            $table->string('type')->default('string');

            // Whether this setting can be read by unauthenticated clients
            $table->boolean('is_public')->default(false);

            $table->string('group')->default('general'); // group for UI tab organization

            $table->timestamps();

            $table->unique(['tenant_id', 'key']);
            $table->index('tenant_id');
            $table->index(['tenant_id', 'group']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
