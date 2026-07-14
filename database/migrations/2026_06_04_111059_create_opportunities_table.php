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
        Schema::create('opportunities', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->foreignId('lead_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('stage', ['prospecting', 'qualification', 'proposal', 'negotiation', 'closed_won', 'closed_lost'])->default('prospecting');
            $table->decimal('amount', 15, 2)->default(0.00);
            $table->integer('probability')->default(10);
            $table->enum('priority', ['low', 'medium', 'high'])->default('medium');
            $table->enum('type', ['new_business', 'existing_business'])->default('new_business');
            $table->string('lost_reason')->nullable();
            $table->timestamp('expected_close_date')->nullable();
            $table->timestamp('actual_close_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('opportunities');
    }
};
