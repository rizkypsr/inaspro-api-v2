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
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('shipping_rate_id')->nullable()->after('courier_name')->constrained('shipping_rates')->onDelete('set null');
            $table->decimal('shipping_cost', 10, 2)->nullable()->after('shipping_rate_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['shipping_rate_id']);
            $table->dropColumn(['shipping_rate_id', 'shipping_cost']);
        });
    }
};
