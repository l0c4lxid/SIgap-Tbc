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
        Schema::dropIfExists('family_members');
        Schema::dropIfExists('patient_treatments');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('family_members', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
        });

        Schema::create('patient_treatments', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
        });
    }
};
