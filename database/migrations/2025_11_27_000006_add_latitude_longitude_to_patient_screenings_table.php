<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('patient_screenings', function (Blueprint $table) {
            if (!Schema::hasColumn('patient_screenings', 'latitude')) {
                $table->double('latitude')->nullable()->after('patient_height');
            }
            if (!Schema::hasColumn('patient_screenings', 'longitude')) {
                $table->double('longitude')->nullable()->after('latitude');
            }
        });
    }

    public function down(): void
    {
        Schema::table('patient_screenings', function (Blueprint $table) {
             if (Schema::hasColumn('patient_screenings', 'longitude')) {
                $table->dropColumn('longitude');
            }
            if (Schema::hasColumn('patient_screenings', 'latitude')) {
                $table->dropColumn('latitude');
            }
        });
    }
};
