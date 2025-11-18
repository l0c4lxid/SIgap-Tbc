<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class RoleAndUserSeeder extends Seeder
{
    public function run()
    {
        $roles = ['pasien', 'kader_tbc', 'puskesmas', 'kelurahan', 'pemda'];

        foreach ($roles as $roleName) {
            Role::firstOrCreate(['name' => $roleName]);
        }

        // Create one user per role (simple demo users)
        $users = [
            ['name' => 'Pasien Demo', 'email' => 'pasien@example.test', 'role' => 'pasien'],
            ['name' => 'Kader Demo', 'email' => 'kader@example.test', 'role' => 'kader_tbc'],
            ['name' => 'Puskesmas Demo', 'email' => 'puskesmas@example.test', 'role' => 'puskesmas'],
            ['name' => 'Kelurahan Demo', 'email' => 'kelurahan@example.test', 'role' => 'kelurahan'],
            ['name' => 'Pemda Demo', 'email' => 'pemda@example.test', 'role' => 'pemda'],
        ];

        foreach ($users as $u) {
            $user = User::firstOrCreate(
                ['email' => $u['email']],
                ['name' => $u['name'], 'password' => Hash::make('P@ssw0rd123')]
            );
            $user->assignRole($u['role']);
        }
    }
}
