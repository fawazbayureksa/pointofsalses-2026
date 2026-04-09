<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('cashier_name')->nullable()->after('user_id');
            $table->unsignedBigInteger('authorized_by')->nullable()->after('notes');

            $table->foreign('authorized_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['authorized_by']);
            $table->dropColumn(['cashier_name', 'authorized_by']);
        });
    }
};
