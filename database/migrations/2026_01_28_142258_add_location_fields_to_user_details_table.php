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
            $table->foreignId('kelurahan_user_id')->nullable()->constrained('users');
            $table->string('rw_code')->nullable();
            $table->string('rt_code')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_details', function (Blueprint $table) {
            $table->dropForeign(['kelurahan_user_id']);
            $table->dropColumn(['kelurahan_user_id', 'rw_code', 'rt_code']);
        });
    }
};
