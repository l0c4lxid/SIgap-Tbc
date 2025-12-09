<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_details', function (Blueprint $table) {
            if (! Schema::hasColumn('user_details', 'pending_supervisor_id')) {
                $table->foreignId('pending_supervisor_id')
                    ->nullable()
                    ->after('supervisor_id')
                    ->constrained('users')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('user_details', function (Blueprint $table) {
            if (Schema::hasColumn('user_details', 'pending_supervisor_id')) {
                $table->dropConstrainedForeignId('pending_supervisor_id');
            }
        });
    }
};
