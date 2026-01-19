<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('patient_screenings', function (Blueprint $table) {
            if (!Schema::hasColumn('patient_screenings', 'patient_is_wni')) {
                $table->boolean('patient_is_wni')->default(true)->after('kader_id');
            }
            if (!Schema::hasColumn('patient_screenings', 'patient_gender')) {
                $table->string('patient_gender', 20)->nullable()->after('patient_address');
            }
            if (!Schema::hasColumn('patient_screenings', 'patient_birth_place')) {
                $table->string('patient_birth_place')->nullable()->after('patient_gender');
            }
            if (!Schema::hasColumn('patient_screenings', 'patient_birth_date')) {
                $table->date('patient_birth_date')->nullable()->after('patient_birth_place');
            }
            if (!Schema::hasColumn('patient_screenings', 'patient_age')) {
                $table->unsignedSmallInteger('patient_age')->nullable()->after('patient_birth_date');
            }
            if (!Schema::hasColumn('patient_screenings', 'patient_address_ktp')) {
                $table->string('patient_address_ktp')->nullable()->after('patient_age');
            }
            if (!Schema::hasColumn('patient_screenings', 'patient_address_domisili')) {
                $table->string('patient_address_domisili')->nullable()->after('patient_address_ktp');
            }
            if (!Schema::hasColumn('patient_screenings', 'patient_weight')) {
                $table->decimal('patient_weight', 5, 2)->nullable()->after('patient_address_domisili');
            }
            if (!Schema::hasColumn('patient_screenings', 'patient_height')) {
                $table->decimal('patient_height', 5, 2)->nullable()->after('patient_weight');
            }
        });
    }

    public function down(): void
    {
        Schema::table('patient_screenings', function (Blueprint $table) {
            $columns = [
                'patient_is_wni',
                'patient_gender',
                'patient_birth_place',
                'patient_birth_date',
                'patient_age',
                'patient_address_ktp',
                'patient_address_domisili',
                'patient_weight',
                'patient_height',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('patient_screenings', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
