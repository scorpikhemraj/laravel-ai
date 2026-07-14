<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('dashboard_flowchart_edges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('widget_id')->constrained('dashboard_widgets')->onDelete('cascade');
            $table->string('source_node_key', 100);
            $table->string('target_node_key', 100);
            $table->string('label')->nullable();
            $table->string('edge_type', 100)->nullable();
            $table->json('style')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dashboard_flowchart_edges');
    }
};
