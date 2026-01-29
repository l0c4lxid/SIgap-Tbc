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
        Schema::create('screening_targets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('kelurahan_user_id')->constrained('users');
            $table->enum('period_type', ['monthly', 'custom']);
            $table->string('month')->nullable(); // YYYY-MM
            $table->date('date_from')->nullable();
            $table->date('date_to')->nullable();
            $table->integer('target_total');
            $table->integer('target_suspek')->nullable();
            $table->enum('allocation_mode', ['auto_even', 'manual'])->default('auto_even');
            $table->enum('status', ['active', 'archived'])->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['kelurahan_user_id', 'period_type', 'month', 'date_from', 'date_to', 'status'], 'target_unique_index');
        });

        Schema::create('screening_target_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('screening_target_id')->constrained()->cascadeOnDelete();
            $table->foreignId('kader_user_id')->constrained('users');
            $table->integer('allocated_total');
            $table->integer('allocated_suspek')->nullable();
            $table->timestamps();

            $table->unique(['screening_target_id', 'kader_user_id'], 'alloc_target_kader_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('screening_target_allocations');
        Schema::dropIfExists('screening_targets');
    }
};
