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
        Schema::create('fantasy_shoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fantasy_event_id')->constrained('fantasy_events')->cascadeOnDelete()->index();
            $table->string('name', 100);
            $table->string('image', 255);
            $table->unsignedInteger('price')->comment('price applied for this shoe (in smallest currency unit)'); 
            $table->timestamps();

            $table->index(['fantasy_event_id', 'price']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fantasy_shoes');
    }
};
