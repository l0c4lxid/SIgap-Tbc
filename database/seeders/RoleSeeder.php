<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class RoleSeeder extends Seeder
{
    public function run()
    {
        // Roles
        $roles = ['pasien', 'kader_tbc', 'puskesmas', 'kelurahan', 'pemda'];
        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role]);
        }

        // Example users
        // 1. pasien
        $user1 = User::firstOrCreate(
            ['email' => 'pasien@example.test'],
            ['name' => 'Demo Pasien', 'password' => Hash::make('password123')]
        );
        $user1->assignRole('pasien');

        // 2. kader_tbc
        $user2 = User::firstOrCreate(
            ['email' => 'kader@example.test'],
            ['name' => 'Demo Kader', 'password' => Hash::make('password123')]
        );
        $user2->assignRole('kader_tbc');

        // 3. puskesmas
        $user3 = User::firstOrCreate(
            ['email' => 'puskesmas@example.test'],
            ['name' => 'Demo Puskesmas', 'password' => Hash::make('password123')]
        );
        $user3->assignRole('puskesmas');

        // (Optional) mark emails verified for demo
        $user1->markEmailAsVerified();
        $user2->markEmailAsVerified();
        $user3->markEmailAsVerified();
    }
}
