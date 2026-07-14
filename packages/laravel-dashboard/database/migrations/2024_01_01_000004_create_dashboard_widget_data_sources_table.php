<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('dashboard_widget_data_sources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('widget_id')->constrained('dashboard_widgets')->onDelete('cascade');
            $table->string('module');
            $table->string('label')->nullable();
            $table->string('x_axis_field')->nullable();
            $table->string('x_axis_type')->nullable();
            $table->string('y_axis_field')->nullable();
            $table->string('y_axis_aggregate')->nullable();
            $table->string('y_axis_group_by')->nullable();
            $table->string('sort_field')->nullable();
            $table->string('sort_direction', 4)->default('asc');
            $table->integer('limit')->nullable();
            $table->string('date_range')->nullable();
            $table->date('date_from')->nullable();
            $table->date('date_to')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dashboard_widget_data_sources');
    }
};
