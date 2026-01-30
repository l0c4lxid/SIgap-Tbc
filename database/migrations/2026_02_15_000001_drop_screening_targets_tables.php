<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('screening_target_overrides');
        Schema::dropIfExists('screening_target_allocations');
        Schema::dropIfExists('screening_targets');
    }

    public function down(): void
    {
        // No-op: tables intentionally removed.
    }
};
