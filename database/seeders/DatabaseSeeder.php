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
            ['phone' => '081234567890'],
            [
                'name' => 'Pemda',
                'password' => Hash::make('pemda123'),
                'role' => UserRole::Pemda->value,
                'is_active' => true,
            ]
        );

        UserDetail::updateOrCreate(
            ['user_id' => $pemda->id],
            []
        );
    }
}
