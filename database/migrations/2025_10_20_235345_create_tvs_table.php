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
        Schema::create('tvs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tv_category_id')->constrained('tv_categories')->cascadeOnDelete();
            $table->string('title', 200);
            $table->text('link');
            $table->string('image', 255)->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
            
            $table->index('tv_category_id');
            $table->index(['tv_category_id', 'status']);
            $table->index(['status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tvs');
    }
};