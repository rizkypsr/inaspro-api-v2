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
        Schema::create('fantasy_events', function (Blueprint $table) {
            $table->id();
            $table->string('title',200);
            $table->text('description')->nullable();
            $table->string('location',150);
            $table->dateTime('play_date');
            $table->decimal('base_fee',12,2);
            $table->enum('status',['draft','open','closed','finished'])->default('open')->index();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fantasy_events');
    }
};
