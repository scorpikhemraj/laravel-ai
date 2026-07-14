<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('dashboard_widget_conditions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('widget_id')->constrained('dashboard_widgets')->onDelete('cascade');
            $table->foreignId('data_source_id')->nullable()->constrained('dashboard_widget_data_sources')->onDelete('cascade');
            $table->string('field');
            $table->string('operator', 50);
            $table->json('value')->nullable();
            $table->string('value_type', 50)->default('static');
            $table->string('logical', 10)->default('AND');
            $table->integer('group_id')->nullable();
            $table->integer('order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dashboard_widget_conditions');
    }
};
