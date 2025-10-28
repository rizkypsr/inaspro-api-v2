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
        Schema::create('fantasy_event_teams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fantasy_event_id')->constrained()->cascadeOnDelete()->index();
            $table->string('name',100);
            $table->integer('slot_limit')->default(20);
            $table->timestamps();

            $table->index(['fantasy_event_id', 'slot_limit']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fantasy_event_teams');
    }
};
