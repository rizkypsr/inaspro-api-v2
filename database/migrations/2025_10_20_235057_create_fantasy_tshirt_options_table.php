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
        Schema::create('fantasy_tshirt_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fantasy_event_team_id')->constrained('fantasy_event_teams')->cascadeOnDelete()->index();
            $table->string('size', 10);
            $table->timestamps();

            $table->unique(['fantasy_event_team_id', 'size']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fantasy_tshirt_options');
    }
};
