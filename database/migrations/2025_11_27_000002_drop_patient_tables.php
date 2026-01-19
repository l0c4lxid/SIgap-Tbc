<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('family_members');
        Schema::dropIfExists('patient_treatments');
    }

    public function down(): void
    {
        // Intentionally left blank; patient tables removed by design.
    }
};
