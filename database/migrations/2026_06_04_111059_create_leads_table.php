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
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('company')->nullable();
            $table->string('title')->nullable();
            $table->string('city')->nullable();
            $table->string('country')->nullable();
            $table->enum('status', ['new', 'contacted', 'qualified', 'lost'])->default('new');
            $table->enum('source', ['website', 'referral', 'social_media', 'cold_call', 'advertising'])->default('website');
            $table->decimal('value', 15, 2)->nullable();
            $table->string('address')->nullable();
            $table->string('state')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('industry')->nullable();
            $table->decimal('annual_revenue', 15, 2)->nullable();
            $table->integer('number_of_employees')->nullable();
            $table->string('website')->nullable();
            $table->string('linkedin_url')->nullable();
            $table->integer('lead_score')->default(0);
            $table->text('notes')->nullable();
            $table->timestamp('converted_at')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
