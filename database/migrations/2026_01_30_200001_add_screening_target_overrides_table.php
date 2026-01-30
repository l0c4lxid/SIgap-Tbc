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
        Schema::create('screening_target_overrides', function (Blueprint $table) {
            $table->id();
            $table->foreignId('screening_target_id')->constrained()->cascadeOnDelete();
            $table->foreignId('kader_user_id')->constrained('users');
            $table->integer('allocated_total')->nullable();
            $table->integer('allocated_suspek')->nullable();
            $table->text('reason')->nullable();
            $table->foreignId('set_by')->nullable()->constrained('users');
            $table->timestamps();

            $table->unique(['screening_target_id', 'kader_user_id'], 'overrides_target_kader_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('screening_target_overrides');
    }
};
