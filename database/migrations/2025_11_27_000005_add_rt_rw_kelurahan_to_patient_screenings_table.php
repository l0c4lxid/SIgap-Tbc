<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('patient_screenings', function (Blueprint $table) {
            if (!Schema::hasColumn('patient_screenings', 'patient_address_rt')) {
                $table->string('patient_address_rt', 5)->nullable()->after('patient_address_domisili');
            }
            if (!Schema::hasColumn('patient_screenings', 'patient_address_rw')) {
                $table->string('patient_address_rw', 5)->nullable()->after('patient_address_rt');
            }
            if (!Schema::hasColumn('patient_screenings', 'patient_address_kelurahan')) {
                $table->string('patient_address_kelurahan', 100)->nullable()->after('patient_address_rw');
            }
        });
    }

    public function down(): void
    {
        Schema::table('patient_screenings', function (Blueprint $table) {
            $columns = [
                'patient_address_rt',
                'patient_address_rw',
                'patient_address_kelurahan',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('patient_screenings', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
