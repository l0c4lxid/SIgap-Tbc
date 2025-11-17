<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use App\Models\UserDetail;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $pemda = User::firstOrCreate(
            ['email' => 'pemda@pemda.com'],
            [
                'name' => 'Pemda',
                'password' => Hash::make('pemda123'),
                'role' => UserRole::Pemda->value,
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        UserDetail::updateOrCreate(
            ['user_id' => $pemda->id],
            []
        );
    }
}
