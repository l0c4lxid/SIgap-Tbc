<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')->where('role', 'pasien')->delete();
    }

    public function down(): void
    {
        // No-op: patient users are intentionally removed.
    }
};
