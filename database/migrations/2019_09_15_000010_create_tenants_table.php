<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTenantsTable extends Migration
{
    /**
     * Central tenants table.
     *
     * Columns declared here (other than id/data/timestamps) must also appear
     * in Tenant::getCustomColumns() so stancl/tenancy stores them on the row
     * rather than inside the `data` JSON blob.
     */
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->string('id')->primary();            // e.g. "acme-a3f9b2"

            // Core identity
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('email')->nullable();        // primary contact email
            $table->string('phone')->nullable();
            $table->string('contact_name')->nullable();

            // Business profile
            $table->string('business_type')->nullable(); // retail, restaurant, etc.
            $table->string('logo')->nullable();
            $table->text('address')->nullable();

            // Plan & lifecycle
            $table->string('plan')->default('basic');    // basic, professional, enterprise
            $table->string('status')->default('active'); // active, suspended, cancelled
            $table->timestamp('trial_ends_at')->nullable();

            $table->timestamps();
            $table->json('data')->nullable();

            $table->index('status');
            $table->index('plan');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
}
