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
        Schema::table("leads", function (Blueprint $table) {
            $table->index("created_at");
            $table->index("status");
            $table->index("source");
            $table->index("is_favorite");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table("leads", function (Blueprint $table) {
            $table->dropIndex(["leads_created_at_index"]);
            $table->dropIndex(["leads_status_index"]);
            $table->dropIndex(["leads_source_index"]);
            $table->dropIndex(["leads_is_favorite_index"]);
        });
    }
};
