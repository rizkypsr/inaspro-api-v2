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
        Schema::create('community_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('community_id')->constrained('communities')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->enum('role', ['admin', 'member'])->default('member');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->datetime('joined_at')->nullable()->comment('Only filled when status = approved');
            $table->timestamps();
            
            // Unique constraint to prevent duplicate memberships
            $table->unique(['community_id', 'user_id']);
            
            // Indexes for optimization
            $table->index(['community_id', 'status']);
            $table->index(['user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('community_members');
    }
};
