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
        Schema::table('screening_targets', function (Blueprint $table) {
            // Rename allocation_mode to distribution_mode if DB supports it or just drop/add
            // Since it's a new feature and likely no data yet, we can drop and recreate or just add column
            // We will drop the column and add new one to be clean with enum values
            $table->dropColumn('allocation_mode'); 
            $table->enum('distribution_mode', ['even', 'weighted_last_month', 'weighted_rwrt', 'manual'])->default('even')->after('target_suspek');
        });

        Schema::table('screening_target_allocations', function (Blueprint $table) {
            $table->json('weight_source')->nullable()->after('allocated_suspek');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('screening_target_allocations', function (Blueprint $table) {
            $table->dropColumn('weight_source');
        });

        Schema::table('screening_targets', function (Blueprint $table) {
            $table->dropColumn('distribution_mode');
            $table->enum('allocation_mode', ['auto_even', 'manual'])->default('auto_even');
        });
    }
};
