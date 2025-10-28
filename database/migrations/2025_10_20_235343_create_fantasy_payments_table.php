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
       Schema::create('fantasy_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fantasy_registration_id')->constrained('fantasy_registrations')->cascadeOnDelete();

            $table->decimal('amount', 12, 2);
            $table->string('method', 50)->default('manual'); // manual / midtrans / xendit
            $table->enum('status', ['waiting','confirmed','rejected','failed','refunded'])->default('waiting')->index();
            $table->string('transaction_id', 100)->nullable()->index();
            $table->text('evidence')->nullable(); // path, WA note, or admin note
            $table->timestamps();

            $table->index('fantasy_registration_id');
            $table->index(['fantasy_registration_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fantasy_payments');
    }
};
