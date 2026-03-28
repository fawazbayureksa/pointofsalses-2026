<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->enum('subscription_status', ['trial', 'active', 'expired', 'cancelled'])
                  ->default('trial')
                  ->after('trial_ends_at');
            $table->timestamp('subscription_ends_at')->nullable()->after('subscription_status');
            $table->boolean('is_subscription_exempt')->default(false)->after('subscription_ends_at');

            $table->index('subscription_status');
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropIndex(['subscription_status']);
            $table->dropColumn(['subscription_status', 'subscription_ends_at', 'is_subscription_exempt']);
        });
    }
};
