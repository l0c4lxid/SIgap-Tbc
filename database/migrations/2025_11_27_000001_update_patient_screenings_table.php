<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('patient_screenings', function (Blueprint $table) {
            if (Schema::hasColumn('patient_screenings', 'patient_id')) {
                $table->dropForeign(['patient_id']);
                $table->dropColumn('patient_id');
            }

            if (!Schema::hasColumn('patient_screenings', 'patient_name')) {
                $table->string('patient_name')->nullable()->after('kader_id');
            }
            if (!Schema::hasColumn('patient_screenings', 'patient_nik')) {
                $table->string('patient_nik', 30)->nullable()->after('patient_name');
            }
            if (!Schema::hasColumn('patient_screenings', 'patient_phone')) {
                $table->string('patient_phone', 25)->nullable()->after('patient_nik');
            }
            if (!Schema::hasColumn('patient_screenings', 'patient_address')) {
                $table->string('patient_address')->nullable()->after('patient_phone');
            }
        });
    }

    public function down(): void
    {
        Schema::table('patient_screenings', function (Blueprint $table) {
            if (Schema::hasColumn('patient_screenings', 'patient_address')) {
                $table->dropColumn('patient_address');
            }
            if (Schema::hasColumn('patient_screenings', 'patient_phone')) {
                $table->dropColumn('patient_phone');
            }
            if (Schema::hasColumn('patient_screenings', 'patient_nik')) {
                $table->dropColumn('patient_nik');
            }
            if (Schema::hasColumn('patient_screenings', 'patient_name')) {
                $table->dropColumn('patient_name');
            }

            if (!Schema::hasColumn('patient_screenings', 'patient_id')) {
                $table->foreignId('patient_id')->nullable()->constrained('users')->cascadeOnDelete();
            }
        });
    }
};
