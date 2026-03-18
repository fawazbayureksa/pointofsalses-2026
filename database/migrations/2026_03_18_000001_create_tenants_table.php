<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('contact_name')->nullable();
            $table->string('business_type')->nullable();
            $table->string('logo')->nullable();
            $table->text('address')->nullable();
            $table->enum('plan', ['basic', 'professional', 'enterprise'])->default('basic');
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active');
            $table->timestamp('trial_ends_at')->nullable();
            $table->json('settings')->nullable(); // replaces separate settings table
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('plan');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};
