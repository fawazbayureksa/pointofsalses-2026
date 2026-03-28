<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('customer_code')->nullable()->after('address');
            $table->enum('gender', ['male', 'female'])->nullable()->after('customer_code');
            $table->date('date_of_birth')->nullable()->after('gender');
            $table->text('notes')->nullable()->after('date_of_birth');
            $table->date('member_since')->nullable()->after('loyalty_points');
            $table->enum('membership_tier', ['regular', 'silver', 'gold', 'platinum'])->default('regular')->after('member_since');
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn(['customer_code', 'gender', 'date_of_birth', 'notes', 'member_since', 'membership_tier']);
        });
    }
};
