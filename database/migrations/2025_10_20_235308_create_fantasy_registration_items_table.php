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
       Schema::create('fantasy_registration_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fantasy_registration_id')->constrained('fantasy_registrations')->cascadeOnDelete();

            // one of these will be set: tshirt OR shoe_size
            $table->foreignId('fantasy_tshirt_option_id')->nullable()->constrained('fantasy_tshirt_options')->nullOnDelete();
            $table->foreignId('fantasy_shoe_size_id')->nullable()->constrained('fantasy_shoe_sizes')->nullOnDelete();

            // price snapshot for this item (shoe price; tshirt is 0 because included)
            $table->unsignedInteger('price')->default(0);

            $table->timestamps();

            $table->index('fantasy_registration_id');
            $table->index('fantasy_tshirt_option_id');
            $table->index('fantasy_shoe_size_id');
            // optional: ensure a registration doesn't have duplicate tshirt entries
            $table->unique(['fantasy_registration_id', 'fantasy_tshirt_option_id']);
            // optional: ensure a registration doesn't have duplicate shoe entries (by shoe_size)
            $table->unique(['fantasy_registration_id', 'fantasy_shoe_size_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fantasy_registration_items');
    }
};
