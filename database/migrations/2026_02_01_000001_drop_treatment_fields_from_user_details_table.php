<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_details', function (Blueprint $table) {
            if (Schema::hasColumn('user_details', 'treatment_status')) {
                $table->dropColumn('treatment_status');
            }
            if (Schema::hasColumn('user_details', 'next_follow_up_at')) {
                $table->dropColumn('next_follow_up_at');
            }
            if (Schema::hasColumn('user_details', 'treatment_notes')) {
                $table->dropColumn('treatment_notes');
            }
        });
    }

    public function down(): void
    {
        Schema::table('user_details', function (Blueprint $table) {
            if (! Schema::hasColumn('user_details', 'treatment_status')) {
                $table->string('treatment_status')->nullable();
            }
            if (! Schema::hasColumn('user_details', 'next_follow_up_at')) {
                $table->date('next_follow_up_at')->nullable();
            }
            if (! Schema::hasColumn('user_details', 'treatment_notes')) {
                $table->text('treatment_notes')->nullable();
            }
        });
    }
};
