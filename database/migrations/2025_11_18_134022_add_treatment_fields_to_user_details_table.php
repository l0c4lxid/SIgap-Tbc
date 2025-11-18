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
        Schema::table('user_details', function (Blueprint $table) {
            $table->enum('treatment_status', ['none', 'contacted', 'scheduled', 'in_treatment', 'recovered'])
                ->default('none')
                ->after('notes');
            $table->date('next_follow_up_at')->nullable()->after('treatment_status');
            $table->text('treatment_notes')->nullable()->after('next_follow_up_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_details', function (Blueprint $table) {
            $table->dropColumn(['treatment_status', 'next_follow_up_at', 'treatment_notes']);
        });
    }
};
