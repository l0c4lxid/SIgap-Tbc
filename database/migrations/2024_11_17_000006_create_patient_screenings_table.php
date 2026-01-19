<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patient_screenings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kader_id')->constrained('users')->cascadeOnDelete();
            $table->boolean('patient_is_wni')->default(true);
            $table->string('patient_name');
            $table->string('patient_nik', 30)->nullable();
            $table->string('patient_phone', 25)->nullable();
            $table->string('patient_address');
            $table->string('patient_gender', 20)->nullable();
            $table->string('patient_birth_place')->nullable();
            $table->date('patient_birth_date')->nullable();
            $table->unsignedSmallInteger('patient_age')->nullable();
            $table->string('patient_address_ktp')->nullable();
            $table->string('patient_address_domisili')->nullable();
            $table->string('patient_address_rt', 5)->nullable();
            $table->string('patient_address_rw', 5)->nullable();
            $table->string('patient_address_kelurahan', 100)->nullable();
            $table->decimal('patient_weight', 5, 2)->nullable();
            $table->decimal('patient_height', 5, 2)->nullable();
            $table->json('answers');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_screenings');
    }
};
