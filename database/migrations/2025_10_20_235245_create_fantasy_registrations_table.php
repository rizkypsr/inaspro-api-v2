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
        Schema::create('fantasy_registrations', function (Blueprint $table) {
            $table->id();
            $table->string('registration_code', 30)->unique()->index();
            $table->foreignId('fantasy_event_id')->constrained('fantasy_events')->cascadeOnDelete()->index();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete()->index();
            $table->foreignId('fantasy_event_team_id')->constrained('fantasy_event_teams')->cascadeOnDelete()->index();
            $table->decimal('registration_fee', 12, 2)->comment('snapshot of event base_fee at registration time');
            $table->enum('status', ['pending','confirmed','cancelled'])->default('pending')->index();
            $table->timestamps();

            // ensure a user can't register same event twice
            $table->unique(['fantasy_event_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fantasy_registrations');
    }
};
