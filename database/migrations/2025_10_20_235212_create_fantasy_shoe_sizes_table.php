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
        Schema::create('fantasy_shoe_sizes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fantasy_shoe_id')->constrained('fantasy_shoes')->cascadeOnDelete()->index();
            $table->string('size', 10);
            $table->unsignedInteger('stock');
            $table->unsignedInteger('reserved_stock')->default(0);
            $table->timestamps();

            $table->unique(['fantasy_shoe_id', 'size']);
            $table->index(['fantasy_shoe_id', 'stock', 'reserved_stock']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fantasy_shoe_sizes');
    }
};
