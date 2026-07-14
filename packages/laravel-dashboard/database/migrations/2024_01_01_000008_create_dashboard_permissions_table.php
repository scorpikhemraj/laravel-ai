<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('dashboard_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dashboard_id')->constrained('dashboards')->onDelete('cascade');
            $table->string('permissionable_type');
            $table->unsignedBigInteger('permissionable_id');
            $table->string('access_level', 20)->default('view'); // view, edit, admin
            $table->timestamps();

            $table->index(['permissionable_type', 'permissionable_id'], 'permissionable_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dashboard_permissions');
    }
};
