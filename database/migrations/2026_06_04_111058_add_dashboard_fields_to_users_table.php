<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'manager', 'sales_rep'])->default('sales_rep')->after('email');
            $table->decimal('target_revenue', 15, 2)->default(100000.00)->after('role');
            $table->string('department')->default('Sales')->after('target_revenue');
            $table->decimal('commission_rate', 4, 2)->default(0.10)->after('department');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'target_revenue', 'department', 'commission_rate']);
        });
    }
};
