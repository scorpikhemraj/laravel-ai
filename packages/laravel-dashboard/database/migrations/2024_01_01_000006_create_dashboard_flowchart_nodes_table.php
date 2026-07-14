<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('dashboard_flowchart_nodes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('widget_id')->constrained('dashboard_widgets')->onDelete('cascade');
            $table->string('node_key', 100)->index();
            $table->string('node_type', 100);
            $table->text('label');
            $table->string('shape', 100)->nullable();
            $table->json('style')->nullable();
            $table->float('position_x');
            $table->float('position_y');
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dashboard_flowchart_nodes');
    }
};
