<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('dashboard_widgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dashboard_tab_id')->constrained('dashboard_tabs')->onDelete('cascade');
            $table->string('widget_type');
            $table->string('title')->nullable();
            $table->json('style_config')->nullable();
            $table->json('grid_position')->nullable();
            $table->integer('refresh_interval')->nullable();
            $table->integer('cache_ttl')->default(300);
            $table->boolean('is_visible')->default(true);
            $table->integer('order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dashboard_widgets');
    }
};
