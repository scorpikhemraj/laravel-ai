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
        Schema::create('posts', function (Blueprint $table) {
            $table->id();

            // Text types
            $table->string('title');
            $table->text('description')->nullable();

            // Date & Time
            $table->date('publish_date')->nullable();
            $table->dateTime('publish_datetime')->nullable();
            $table->time('publish_time')->nullable();

            // Numeric
            $table->integer('views')->default(0);
            $table->decimal('price', 8, 2)->nullable();

            // Boolean
            $table->boolean('is_active')->default(true);

            // Select & Multiselect
            $table->string('category')->nullable();
            $table->json('tags')->nullable();

            // Radio
            $table->string('status')->default('draft');

            // File
            $table->string('image')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
