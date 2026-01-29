<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            PemdaUserSeeder::class,
            PuskesmasKelurahanSeeder::class,
            KaderSeeder::class,
            // ScreeningSeeder::class, // Integrated into KaderSeeder
        ]);
    }
}
